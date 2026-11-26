<?php
declare(strict_types=1);

namespace App\services;

use App\classes\PushNotificationClass;
use App\classes\Pusher;
use App\model\AllMembersData;
use App\model\Post;
use App\repository\FeedRepository;
use PDO;
use PDOException;
use Src\Db;

/**
 * MemoryMilestoneService
 *
 * Flagship nostalgia and milestone prompt engine for FamilyPlatform.
 * Surfaces "On This Day" flashback memories, automated birthday/anniversary
 * recognition, 1-click memory resharing, and daily WebPush notifications.
 */
class MemoryMilestoneService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
        self::ensureTablesExist($this->db);
    }

    /**
     * Ensures support tables for milestone feed tracking exist.
     */
    public static function ensureTablesExist(PDO $db): void
    {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `family_milestone_feed_runs` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `family_code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
                `milestone_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
                `target_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
                `interval_days` int NOT NULL,
                `event_date` date NOT NULL,
                `post_no` int DEFAULT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_fam_milestone_run` (`family_code`, `milestone_type`, `target_id`, `interval_days`, `event_date`),
                INDEX `idx_fam_date` (`family_code`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    /**
     * Retrieves nostalgic posts published on today's month & day in past years.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @return array<int, array<string, mixed>>
     */
    public function getNostalgiaMemories(string $userId, array $famCodes): array
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            $hasFamCodes = !empty($cleanedCodes);
            $inQuery = $hasFamCodes ? implode(',', array_fill(0, count($cleanedCodes), '?')) : "''";

            $query = "SELECT post.*, pp.img as authorProfileImg,
                             (YEAR(CURDATE()) - YEAR(post.date_created)) as years_ago,
                             rm.requester_id, rm.approver_id, rm.status
                      FROM post
                      LEFT JOIN profilePics pp ON post.id = pp.id
                      LEFT JOIN (
                          SELECT requester_id, approver_id, status
                          FROM requestMgt
                          WHERE requester_id IS NOT NULL AND requester_id = ?
                      ) AS rm ON post.id = rm.approver_id
                      WHERE (";

            if ($hasFamCodes) {
                $query .= "post.postFamCode IN ($inQuery) OR ";
            }

            $query .= "post.id = rm.approver_id OR post.id = ?)
                       AND post.post_status = 'published'
                       AND post.date_deleted IS NULL
                       AND MONTH(post.date_created) = MONTH(CURDATE())
                       AND DAY(post.date_created) = DAY(CURDATE())
                       AND YEAR(post.date_created) < YEAR(CURDATE())
                      ORDER BY post.date_created DESC";

            $params = [$userId];
            if ($hasFamCodes) {
                $params = array_merge($params, $cleanedCodes);
            }
            $params[] = $userId;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key + 1, $val, PDO::PARAM_STR);
            }

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return [];
            }

            $memories = [];
            foreach ($rows as $row) {
                $yearsAgo = max(1, (int)($row['years_ago'] ?? 1));
                $yearsLabel = $yearsAgo === 1 ? '1 year ago today' : "{$yearsAgo} years ago today";

                $images = [];
                for ($i = 0; $i <= 5; $i++) {
                    $imgCol = 'post_img' . $i;
                    if (!empty($row[$imgCol]) && is_string($row[$imgCol])) {
                        $images[] = $row[$imgCol];
                    }
                }

                $createdTime = strtotime((string)$row['date_created']);
                $formattedDate = ($createdTime !== false) ? date('F j, Y', $createdTime) : (string)$row['date_created'];

                $memories[] = [
                    'post_no' => (int)$row['post_no'],
                    'id' => (string)$row['id'],
                    'fullName' => (string)($row['fullName'] ?? 'Family Member'),
                    'profileImg' => (string)($row['authorProfileImg'] ?? $row['profileImg'] ?? ''),
                    'postMessage' => (string)($row['postMessage'] ?? ''),
                    'images' => $images,
                    'primary_image' => $images[0] ?? null,
                    'has_image' => !empty($images),
                    'date_created' => (string)$row['date_created'],
                    'formatted_date' => $formattedDate,
                    'years_ago' => $yearsAgo,
                    'years_ago_label' => $yearsLabel,
                    'year_taken' => (int)date('Y') - $yearsAgo,
                    'meaningful_prompt' => "A treasured moment from our family journey {$yearsLabel}. Reflect on how much our family has grown.",
                    'postFamCode' => (string)($row['postFamCode'] ?? ''),
                    'likes' => (int)($row['post_likes'] ?? 0),
                ];
            }

            return $memories;
        } catch (PDOException $e) {
            \error_log('[MemoryMilestoneService::getNostalgiaMemories] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Collects all upcoming family milestones (birthdays, wedding anniversaries, events)
     * occurring today or in the next $daysAhead days.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @param int $daysAhead
     * @return array<int, array<string, mixed>>
     */
    public function getUpcomingMilestones(string $userId, array $famCodes, int $daysAhead = 7): array
    {
        $milestones = [];
        $today = new \DateTimeImmutable('today');
        $cleanViewerId = trim($userId);
        $fallbackFamCode = MilestoneSurpriseBridgeService::normalizeFamilyCode((string)($famCodes[0] ?? ''));

        // 1. Collect birthdays from `personal` table
        $birthdays = $this->fetchPersonalBirthdays($famCodes);
        foreach ($birthdays as $bday) {
            $day = (int)($bday['day'] ?? 0);
            $monthNum = $this->parseMonthToNumber($bday['month'] ?? null);
            if ($day <= 0 || $monthNum <= 0) {
                continue;
            }

            $calc = $this->calculateMilestoneCountdown($day, $monthNum, (int)($bday['year'] ?? 0), $today, $daysAhead);
            if ($calc !== null) {
                $name = trim(($bday['firstName'] ?? '') . ' ' . ($bday['lastName'] ?? ''));
                $age = $calc['target_age'];
                $milestoneTag = $this->getBirthdayMilestoneTag($age);
                $bdayUserId = trim((string)($bday['id'] ?? ''));
                $bdayFamCode = MilestoneSurpriseBridgeService::normalizeFamilyCode((string)($bday['famCode'] ?? ''));
                if ($bdayFamCode === '') {
                    $bdayFamCode = $fallbackFamCode;
                }
                $targetYear = $calc['target_year'];
                $isSubject = ($cleanViewerId === '' || $bdayUserId === '' || $cleanViewerId === $bdayUserId);
                $mKey = ($bdayFamCode !== '' && $bdayUserId !== '')
                    ? MilestoneSurpriseBridgeService::buildMilestoneKey($bdayFamCode, 'birthday', $bdayUserId, $targetYear)
                    : '';

                $milestones[] = [
                    'type' => 'birthday',
                    'title' => $name !== '' ? "{$name}'s Birthday" : "Family Birthday",
                    'subtitle' => $milestoneTag ?: 'Birthday Celebration',
                    'first_name' => trim((string)($bday['firstName'] ?? '')),
                    'full_name' => $name !== '' ? $name : 'Family Member',
                    'age' => $age,
                    'landmark_tag' => $milestoneTag,
                    'meaningful_message' => $this->getBirthdayMeaningfulMessage($age, $name),
                    'target_date' => $calc['event_date_str'],
                    'iso_date' => $calc['iso_date'],
                    'days_remaining' => $calc['days_remaining'],
                    'date_badge' => $calc['date_badge'],
                    'user_id' => $bdayUserId,
                    'profile_img' => (string)($bday['profile_img'] ?? ''),
                    'icon' => 'bi-gift-fill',
                    'color_class' => 'bg-warning text-dark',
                    'graphic_type' => 'birthday',
                    'accent_color' => '#b8202c',
                    '_milestone_key' => $mKey,
                    '_entity_id' => $bdayUserId,
                    '_is_subject' => $isSubject,
                ];
            }
        }

        // 2. Collect wedding anniversaries from `family_unions` table
        $unions = $this->fetchFamilyUnions($famCodes);
        foreach ($unions as $union) {
            $mDateStr = (string)($union['marriage_date'] ?? '');
            if ($mDateStr === '' && empty($union['marriage_year'])) {
                continue;
            }

            $mMonth = 0;
            $mDay = 0;
            $mYear = (int)($union['marriage_year'] ?? 0);

            if ($mDateStr !== '') {
                $mTime = strtotime($mDateStr);
                if ($mTime !== false) {
                    $mMonth = (int)date('n', $mTime);
                    $mDay = (int)date('j', $mTime);
                    if ($mYear === 0) {
                        $mYear = (int)date('Y', $mTime);
                    }
                }
            }

            if ($mDay > 0) {
                $calc = $this->calculateMilestoneCountdown($mDay, $mMonth, $mYear, $today, $daysAhead);
                if ($calc !== null) {
                    $yearsMarried = $calc['target_age'];
                    $anniversaryTag = $this->getAnniversaryMilestoneTag($yearsMarried);
                    $p1Clean = trim((string)($union['p1_name'] ?? ''));
                    $p2Clean = trim((string)($union['p2_name'] ?? ''));
                    $coupleFullName = ($p1Clean !== '' && $p2Clean !== '')
                        ? "{$p1Clean} & {$p2Clean}"
                        : ($p1Clean !== '' ? $p1Clean : ($p2Clean !== '' ? $p2Clean : 'Wedding Anniversary'));
                    $title = ($p1Clean !== '' && $p2Clean !== '')
                        ? "{$p1Clean} & {$p2Clean}'s Anniversary"
                        : "Wedding Anniversary";

                    $unionId = trim((string)($union['id'] ?? ''));
                    $p1Uid = trim((string)($union['p1_user_id'] ?? ''));
                    $p2Uid = trim((string)($union['p2_user_id'] ?? ''));
                    $unionFamCode = MilestoneSurpriseBridgeService::normalizeFamilyCode((string)($union['family_code'] ?? ''));
                    if ($unionFamCode === '') {
                        $unionFamCode = $fallbackFamCode;
                    }
                    $targetYear = $calc['target_year'];
                    $isSubject = (
                        $cleanViewerId === ''
                        || $unionId === ''
                        || ($p1Uid !== '' && $cleanViewerId === $p1Uid)
                        || ($p2Uid !== '' && $cleanViewerId === $p2Uid)
                    );
                    $mKey = ($unionFamCode !== '' && $unionId !== '')
                        ? MilestoneSurpriseBridgeService::buildMilestoneKey($unionFamCode, 'anniversary', $unionId, $targetYear)
                        : '';

                    $milestones[] = [
                        'type' => 'anniversary',
                        'title' => $title,
                        'full_name' => $coupleFullName,
                        'subtitle' => $anniversaryTag ?: ($yearsMarried ? "{$yearsMarried} Years Together" : 'Wedding Anniversary'),
                        'years_married' => $yearsMarried,
                        'anniversary_tag' => $anniversaryTag,
                        'meaningful_message' => $this->getAnniversaryMeaningfulMessage($yearsMarried, $title),
                        'p1_name' => $p1Clean,
                        'p2_name' => $p2Clean,
                        'p1_img' => (string)($union['p1_img'] ?? ''),
                        'p2_img' => (string)($union['p2_img'] ?? ''),
                        'target_date' => $calc['event_date_str'],
                        'iso_date' => $calc['iso_date'],
                        'days_remaining' => $calc['days_remaining'],
                        'date_badge' => $calc['date_badge'],
                        'user_id' => $p1Uid !== '' ? $p1Uid : (string)($union['partner_1_id'] ?? ''),
                        'profile_img' => (string)($union['p1_img'] ?? ''),
                        'icon' => 'bi-heart-fill',
                        'color_class' => 'bg-danger text-white',
                        'graphic_type' => 'anniversary',
                        'accent_color' => '#ec4899',
                        '_milestone_key' => $mKey,
                        '_entity_id' => $unionId,
                        '_is_subject' => $isSubject,
                    ];
                }
            }
        }

        // 3. Collect family calendar events from `events` table
        $events = $this->fetchUpcomingEvents($famCodes, $daysAhead);
        foreach ($events as $ev) {
            $evDate = (string)($ev['eventDate'] ?? '');
            if ($evDate === '') {
                continue;
            }

            $evTime = strtotime($evDate);
            if ($evTime === false) {
                continue;
            }

            $diffDays = (int)floor(($evTime - $today->getTimestamp()) / 86400);
            if ($diffDays >= 0 && $diffDays <= $daysAhead) {
                $badge = match ($diffDays) {
                    0 => 'Today! 🎉',
                    1 => 'Tomorrow',
                    default => "in {$diffDays} days",
                };

                $evName = (string)($ev['eventName'] ?? 'Family Gathering');
                $evType = (string)($ev['eventType'] ?? 'Special Event');

                $milestones[] = [
                    'type' => 'event',
                    'title' => $evName,
                    'full_name' => $evName,
                    'subtitle' => $evType,
                    'meaningful_message' => $this->getEventMeaningfulMessage($evName, $evType),
                    'target_date' => date('M j, Y', $evTime),
                    'days_remaining' => $diffDays,
                    'date_badge' => $badge,
                    'user_id' => (string)($ev['id'] ?? ''),
                    'profile_img' => '',
                    'icon' => 'bi-calendar-event-fill',
                    'color_class' => 'bg-primary text-white',
                    'graphic_type' => 'event',
                    'accent_color' => '#0d9488',
                    '_milestone_key' => '',
                    '_entity_id' => '',
                    '_is_subject' => true,
                ];
            }
        }

        // Sort milestones chronologically (days remaining ascending)
        usort($milestones, fn(array $a, array $b) => (int)$a['days_remaining'] <=> (int)$b['days_remaining']);

        // 4. Batch-enrich surprise celebration links while strictly shielding celebrants & couples
        $candidateKeys = [];
        foreach ($milestones as $msItem) {
            if (empty($msItem['_is_subject']) && !empty($msItem['_milestone_key'])) {
                $candidateKeys[] = $msItem['_milestone_key'];
            }
        }

        $linkedMap = [];
        if ($candidateKeys !== []) {
            try {
                $bridge = new MilestoneSurpriseBridgeService($this->db);
                $linkedMap = $bridge->findLinksForKeys($candidateKeys);
            } catch (\Throwable $bridgeErr) {
                \error_log('[MemoryMilestoneService::getUpcomingMilestones] bridge lookup warning: ' . $bridgeErr->getMessage());
            }
        }

        foreach ($milestones as &$msItem) {
            $isSubj = $msItem['_is_subject'];
            $mKey   = $msItem['_milestone_key'];
            $mEntId = $msItem['_entity_id'];
            $mType  = $msItem['type'];

            if (!$isSubj && $mEntId !== '' && in_array($mType, ['birthday', 'anniversary'], true)) {
                $linkedRow = ($mKey !== '' && isset($linkedMap[$mKey])) ? $linkedMap[$mKey] : null;
                $claimedBy = is_array($linkedRow) ? trim((string)($linkedRow['claimed_by_user_id'] ?? '')) : '';
                $isOrganiser = ($cleanViewerId !== '' && $claimedBy !== '' && $claimedBy === $cleanViewerId);

                $msItem['can_contribute_surprise'] = true;
                $msItem['surprise_claimed'] = ($linkedRow !== null);
                $msItem['is_surprise_organiser'] = $isOrganiser;
                $msItem['sign_card_url'] = '/member/milestones/surprise-launch?' . http_build_query([
                    'type' => $mType,
                    'entity_id' => $mEntId,
                    'feature' => 'card',
                ]);
                $msItem['record_video_url'] = '/member/milestones/surprise-launch?' . http_build_query([
                    'type' => $mType,
                    'entity_id' => $mEntId,
                    'feature' => 'goodwill',
                ]);
                $msItem['manage_cards_url'] = $isOrganiser ? ('/member/milestones/surprise-launch?' . http_build_query([
                    'type' => $mType,
                    'entity_id' => $mEntId,
                    'feature' => 'manage_cards',
                ])) : '';
                $msItem['manage_videos_url'] = $isOrganiser ? ('/member/milestones/surprise-launch?' . http_build_query([
                    'type' => $mType,
                    'entity_id' => $mEntId,
                    'feature' => 'manage_videos',
                ])) : '';
            } else {
                $msItem['can_contribute_surprise'] = false;
                $msItem['surprise_claimed'] = false;
                $msItem['is_surprise_organiser'] = false;
                $msItem['sign_card_url'] = '';
                $msItem['record_video_url'] = '';
                $msItem['manage_cards_url'] = '';
                $msItem['manage_videos_url'] = '';
            }

            if (function_exists('optimiseLocalImageUrl')) {
                foreach (['profile_img', 'p1_img', 'p2_img'] as $imgKey) {
                    if (!empty($msItem[$imgKey])) {
                        $rawImg = (string) $msItem[$imgKey];
                        $fullImg = (str_starts_with($rawImg, '/') || str_starts_with($rawImg, 'http'))
                            ? $rawImg
                            : '/resources/images/profile/' . $rawImg;
                        $msItem[$imgKey] = optimiseLocalImageUrl($fullImg, 160);
                    }
                }
            }

            unset($msItem['_milestone_key'], $msItem['_entity_id'], $msItem['_is_subject']);
        }
        unset($msItem);

        return $milestones;
    }

    /**
     * Reshares a past nostalgia memory as a new post to the family feed.
     *
     * @param string $userId
     * @param int $postNo
     * @param string $reflectionMessage
     * @param string $famCode
     * @return array<string, mixed>
     */
    public function shareMemoryToFeed(string $userId, int $postNo, string $reflectionMessage, string $famCode): array
    {
        try {
            // 1. Fetch original memory post
            $feedRepo = new FeedRepository($this->db);
            $origPost = $feedRepo->getPostByNo($postNo, $userId);

            if (!$origPost) {
                return ['status' => 'error', 'message' => 'Original memory post not found'];
            }

            // 2. Fetch current user personal details
            $userStmt = $this->db->prepare("SELECT firstName, lastName FROM personal WHERE id = ? LIMIT 1");
            $userStmt->execute([$userId]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            $fullName = trim(($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Family Member';
            }

            // 3. Construct formatted reflection post message
            $createdTime = strtotime($origPost->dateCreated);
            $createdYear = ($createdTime !== false) ? (int)date('Y', $createdTime) : (int)date('Y');
            $yearsAgo = max(1, (int)date('Y') - $createdYear);
            $timeHeader = $yearsAgo === 1 ? '1 year ago' : "{$yearsAgo} years ago";
            $customNote = trim($reflectionMessage);

            $primaryImg = is_string($origPost->rawAttributes['post_img0'] ?? null) ? $origPost->rawAttributes['post_img0'] : null;

            $composedMessage = "✨ [Memory Flashback — {$timeHeader}]: ";
            if ($customNote !== '') {
                $composedMessage .= "{$customNote} \n\n— Original note: \"{$origPost->postMessage}\"";
            } else {
                $composedMessage .= "\"{$origPost->postMessage}\"";
            }
            if ($primaryImg === null || $primaryImg === '') {
                $composedMessage .= ' [style:heirloom_velvet]';
            }

            // 4. Insert into post table
            $insQuery = "INSERT INTO post (id, fullName, postMessage, postFamCode, post_img0, post_time, post_status, date_created)
                         VALUES (?, ?, ?, ?, ?, ?, 'published', CURRENT_TIMESTAMP)";
            $insStmt = $this->db->prepare($insQuery);
            $insStmt->execute([
                $userId,
                $fullName,
                $composedMessage,
                $famCode,
                $primaryImg,
                date('g:i a'),
            ]);

            $newPostNo = (int)$this->db->lastInsertId();

            // Broadcast via Pusher
            try {
                Pusher::broadcastToFamily($famCode, 'new-post', [[
                    'post_no' => $newPostNo,
                    'id' => $userId,
                    'fullName' => $fullName,
                    'postMessage' => $composedMessage,
                    'postFamCode' => $famCode,
                    'post_img0' => $primaryImg,
                    'date_created' => date('Y-m-d H:i:s'),
                ]]);
            } catch (\Throwable $th) {
                \error_log('[MemoryMilestoneService::shareMemoryToFeed] Pusher broadcast failed: ' . $th->getMessage());
            }

            return [
                'status' => 'success',
                'message' => 'Memory shared to family feed successfully',
                'new_post_no' => $newPostNo,
            ];
        } catch (\Throwable $e) {
            \error_log('[MemoryMilestoneService::shareMemoryToFeed] ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Scans and dispatches today's nostalgia memory flashback and milestone alerts
     * for a specific user and their family codes into both the in-app notification system
     * and the WebPush PWA notification system with deep links directly to the card.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @return array{memories_dispatched: int, milestones_dispatched: int}
     */
    public function dispatchDailyMemoriesAndMilestonesForUser(string $userId, array $famCodes): array
    {
        $memoriesDispatched = 0;
        $milestonesDispatched = 0;

        if ($userId === '') {
            return [
                'memories_dispatched' => 0,
                'milestones_dispatched' => 0,
            ];
        }

        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            $todayDate = date('Y-m-d');

            // 1. Process Nostalgia Flashback Memories for Today
            $memories = $this->getNostalgiaMemories($userId, $cleanedCodes);
            if (!empty($memories)) {
                $primaryMemory = $memories[0];
                $memoryTag = 'memory-flashback-' . $todayDate;

                // Idempotent de-duplication: check if already dispatched for today
                $stmt = $this->db->prepare("
                    SELECT 1 FROM notification_orchestration 
                    WHERE user_id = ? AND tag = ? AND DATE(created_at) = CURDATE()
                    LIMIT 1
                ");
                $stmt->execute([$userId, $memoryTag]);
                $alreadyDispatched = (bool)$stmt->fetchColumn();

                if (!$alreadyDispatched) {
                    $stmt2 = $this->db->prepare("
                        SELECT 1 FROM notification 
                        WHERE receiver_id = ? 
                          AND notification_type = 'Memories' 
                          AND (created_at >= CURDATE() OR notification_date LIKE ?)
                        LIMIT 1
                    ");
                    $stmt2->execute([$userId, $todayDate . '%']);
                    $alreadyDispatched = (bool)$stmt2->fetchColumn();
                }

                if (!$alreadyDispatched) {
                    $yearsLabel = !empty($primaryMemory['years_ago_label']) ? (string)$primaryMemory['years_ago_label'] : 'a past year';
                    $title = '✨ On This Day Flashback';
                    $body = "You have a cherished family memory from {$yearsLabel}! Tap to view the flashback card.";
                    $actionUrl = '/profilePage#memoriesCard';
                    $famCode = (string)($primaryMemory['postFamCode'] ?: ($cleanedCodes[0] ?? ''));

                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'social',
                        priority: 'medium',
                        title: $title,
                        body: $body,
                        actionUrl: $actionUrl,
                        tag: $memoryTag,
                        familyCode: $famCode !== '' ? $famCode : null,
                        metadata: [
                            'type' => 'memories',
                            'post_no' => (int)$primaryMemory['post_no'],
                            'sender_id' => 'system',
                            'sender_name' => 'Family Memories',
                        ]
                    );

                    $memoriesDispatched++;
                }
            }

            // 2. Process Milestones (Birthdays, Anniversaries, Events)
            // Dispatches advance notice at 7 days ahead (or within 7-5 days catch-up window),
            // 1 day ahead (or 2-1 days), and on the day itself (0 days).
            $milestones = $this->getUpcomingMilestones($userId, $cleanedCodes, 7);
            foreach ($milestones as $ms) {
                $daysRemaining = (int)($ms['days_remaining'] ?? -1);
                if ($daysRemaining < 0 || $daysRemaining > 7) {
                    continue;
                }

                // Map to canonical interval buckets: 0d (today), 1d (tomorrow/within 2d), 7d (advance notice 3-7d)
                $intervalLabel = match (true) {
                    $daysRemaining === 0 => '0d',
                    $daysRemaining <= 2 => '1d',
                    default => '7d',
                };

                $msType = (string)($ms['type'] ?? 'milestone');
                $targetId = (string)($ms['user_id'] ?? 'item');
                $targetDateStr = (string)($ms['target_date'] ?? $todayDate);
                $targetTime = strtotime($targetDateStr);
                $eventDateIso = ($targetTime !== false) ? date('Y-m-d', $targetTime) : $todayDate;
                $milestoneTag = 'milestone-' . $msType . '-' . $targetId . '-' . $intervalLabel . '-' . $eventDateIso;

                $stmt = $this->db->prepare("
                    SELECT 1 FROM notification_orchestration 
                    WHERE user_id = ? AND tag = ?
                    LIMIT 1
                ");
                $stmt->execute([$userId, $milestoneTag]);
                $alreadyDispatched = (bool)$stmt->fetchColumn();

                if (!$alreadyDispatched) {
                    $subtitle = !empty($ms['subtitle']) ? (string)$ms['subtitle'] : 'Celebration';
                    $msTitle = (string)($ms['title'] ?? 'Special Occasion');

                    $title = match ($daysRemaining) {
                        0 => match ($msType) {
                            'birthday' => '🎂 ' . $msTitle,
                            'anniversary' => '💍 ' . $msTitle,
                            default => '🎉 ' . $msTitle,
                        },
                        1 => match ($msType) {
                            'birthday' => '🎂 Tomorrow: ' . $msTitle,
                            'anniversary' => '💍 Tomorrow: ' . $msTitle,
                            default => '🎉 Tomorrow: ' . $msTitle,
                        },
                        default => match ($msType) {
                            'birthday' => '🎂 In ' . $daysRemaining . ' Days: ' . $msTitle,
                            'anniversary' => '💍 In ' . $daysRemaining . ' Days: ' . $msTitle,
                            default => '🎉 In ' . $daysRemaining . ' Days: ' . $msTitle,
                        },
                    };

                    $cardCreateUrl = $msType === 'anniversary'
                        ? $this->buildAnniversaryCardCreateUrl($msTitle, $subtitle)
                        : null;

                    $body = match ($daysRemaining) {
                        0 => match ($msType) {
                            'birthday' => "Today is {$msTitle} ({$subtitle})! Celebrate this cherished milestone with our family.",
                            'anniversary' => "Happy Anniversary! {$msTitle} is today ({$subtitle}). Create a celebratory card or share a warm memory.",
                            default => "Family event milestone today: {$msTitle}. Tap to view event details.",
                        },
                        1 => match ($msType) {
                            'birthday' => "Tomorrow is {$msTitle} ({$subtitle})! Get ready to celebrate and send your warmest wishes.",
                            'anniversary' => "Tomorrow is {$msTitle} ({$subtitle}). Send your congratulations and celebrate their enduring bond.",
                            default => "Upcoming event tomorrow: {$msTitle}. Don't forget to get ready!",
                        },
                        default => match ($msType) {
                            'birthday' => "Upcoming birthday in {$daysRemaining} days ({$ms['target_date']}): {$msTitle} ({$subtitle})! Time to prepare a card, gift, or celebration.",
                            'anniversary' => "Upcoming anniversary in {$daysRemaining} days ({$ms['target_date']}): {$msTitle} ({$subtitle}). Mark your calendar!",
                            default => "Upcoming family event in {$daysRemaining} days: {$msTitle}. Tap to view event details.",
                        },
                    };

                    $actionUrl = '/profilePage#memoriesCard';
                    $famCode = (string)($cleanedCodes[0] ?? '');

                    $metadata = [
                        'type' => 'milestones',
                        'milestone_type' => $msType,
                        'interval_days' => $daysRemaining,
                        'target_date' => $eventDateIso,
                        'sender_id' => 'system',
                        'sender_name' => 'Family Milestones',
                    ];
                    if ($cardCreateUrl !== null) {
                        $metadata['card_create_url'] = $cardCreateUrl;
                    }

                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'social',
                        priority: 'medium',
                        title: $title,
                        body: $body,
                        actionUrl: $actionUrl,
                        tag: $milestoneTag,
                        familyCode: $famCode !== '' ? $famCode : null,
                        metadata: $metadata
                    );

                    $milestonesDispatched++;
                }
            }
        } catch (\Throwable $e) {
            \error_log('[MemoryMilestoneService::dispatchDailyMemoriesAndMilestonesForUser] ' . $e->getMessage());
        }

        return [
            'memories_dispatched' => $memoriesDispatched,
            'milestones_dispatched' => $milestonesDispatched,
        ];
    }

    /**
     * Publishes automated milestone timeline posts into the family feed
     * for birthdays, wedding anniversaries, and calendar events at key intervals (7 days, 1 day, 0 days).
     *
     * @param string $famCode
     * @param int $daysAhead
     * @return array{published_count: int, posts: array<int, array<string, mixed>>}
     */
    public function dispatchMilestoneFeedPostsForFamily(string $famCode, int $daysAhead = 7): array
    {
        $normCode = strtoupper(ltrim(trim($famCode), '#'));
        if ($normCode === '') {
            return ['published_count' => 0, 'posts' => []];
        }

        self::ensureTablesExist($this->db);

        $publishedCount = 0;
        $publishedPosts = [];
        $today = new \DateTimeImmutable('today');
        $milestones = $this->getUpcomingMilestones('system', [$normCode], $daysAhead);

        foreach ($milestones as $ms) {
            $daysRemaining = (int)($ms['days_remaining'] ?? -1);
            if ($daysRemaining < 0 || $daysRemaining > 7) {
                continue;
            }

            // Map to canonical interval buckets: 0d (today), 1d (tomorrow/within 2d), 7d (advance notice 3-7d)
            $intervalLabel = match (true) {
                $daysRemaining === 0 => '0d',
                $daysRemaining <= 2 => '1d',
                default => '7d',
            };
            $canonicalIntervalDays = match ($intervalLabel) {
                '0d' => 0,
                '1d' => 1,
                default => 7,
            };

            $msType = (string)($ms['type'] ?? 'milestone');
            $targetId = (string)($ms['user_id'] ?? 'event');
            $targetDateStr = (string)($ms['target_date'] ?? $today->format('Y-m-d'));
            $targetTime = strtotime($targetDateStr);
            $eventDateIso = ($targetTime !== false) ? date('Y-m-d', $targetTime) : $today->format('Y-m-d');
            $title = (string)($ms['title'] ?? 'Family Milestone');
            $subtitle = !empty($ms['subtitle']) ? (string)$ms['subtitle'] : 'Celebration';

            // Check if already published for this family, milestone, target, interval, and date
            $chkStmt = $this->db->prepare(
                "SELECT 1 FROM family_milestone_feed_runs
                 WHERE family_code = ? AND milestone_type = ? AND target_id = ? AND interval_days = ? AND event_date = ?
                 LIMIT 1"
            );
            $chkStmt->execute([$normCode, $msType, $targetId, $canonicalIntervalDays, $eventDateIso]);
            if ($chkStmt->fetchColumn() !== false) {
                continue;
            }

            $message = match ($daysRemaining) {
                0 => match ($msType) {
                    'birthday' => "🎂 Happy Birthday! Today we celebrate {$title}!\n\nJoin the whole family in showering love, warm memories, and best wishes in the comments below! ✨",
                    'anniversary' => "💍 Happy Wedding Anniversary! Today we honour {$title} ({$subtitle})!\n\nMay your love and companionship continue to shine as an inspiration for our whole family dynasty. Leave your congratulations below! 🥂",
                    default => "🎉 Family Gathering Today: {$title} ({$subtitle})!\n\nLooking forward to creating wonderful memories together today. See you all there!",
                },
                1 => match ($msType) {
                    'birthday' => "🎂 Tomorrow: {$title}!\n\nOur family is getting ready to celebrate tomorrow. Get your birthday wishes and celebration messages ready!",
                    'anniversary' => "💍 Tomorrow: {$title} ({$subtitle})!\n\nHonouring another wonderful year of unity and love in our family tree.",
                    default => "🎉 Upcoming Tomorrow: {$title} ({$subtitle})!\n\nOur family gathering is just 1 day away. We can't wait to see everyone!",
                },
                default => match ($msType) {
                    'birthday' => "🎂 Upcoming Birthday in {$daysRemaining} Days ({$ms['target_date']}): {$title}!\n\nMark your calendar as we count down to this special family milestone. Share your favourite memories and warm wishes!",
                    'anniversary' => "💍 Upcoming Anniversary in {$daysRemaining} Days ({$ms['target_date']}): {$title} ({$subtitle})!\n\nCounting down to another cherished milestone in our family tree. Save the date!",
                    default => "🎉 Upcoming Family Event in {$daysRemaining} Days ({$ms['target_date']}): {$title} ({$subtitle})!\n\nSave the date as we look forward to coming together as a family.",
                },
            };

            // Synthesise high-prestige milestone celebration card with celebrant avatar
            $avatarFilename = $this->resolveMemberAvatar($ms, $normCode);
            $cardTitle = $this->formatMilestoneCardTitle($ms, $normCode);
            $isBirthdayEvent = $msType === 'birthday'
                || stripos($subtitle, 'birthday') !== false
                || stripos($title, 'birthday') !== false;
            $rawMeaningful = trim((string)($ms['meaningful_message'] ?? ''));
            $cardMeaningfulMessage = $rawMeaningful !== ''
                ? $rawMeaningful
                : ($isBirthdayEvent
                    ? ($this->getBirthdayMeaningfulMessage(isset($ms['age']) && is_numeric($ms['age']) ? (int)$ms['age'] : null, $cardTitle)
                        ?: 'Another wonderful year of laughter, growth, and shared memories that enrich our family legacy.')
                    : 'Honouring this cherished milestone in our family.');

            $cardData = [
                'title' => $cardTitle,
                'subtitle' => $subtitle,
                'days_remaining' => $daysRemaining,
                'target_date' => (string)($ms['target_date'] ?? $targetDateStr),
                'avatar' => $avatarFilename,
                'meaningful_message' => $cardMeaningfulMessage,
            ];
            $periodKey = $eventDateIso . '_' . $intervalLabel . '_' . substr(hash('sha256', $targetId . $title . '_v2'), 0, 8);
            $cardImage = FamilyHistorianCardRenderer::renderAndSave('milestone_spotlight', $cardData, $normCode, $periodKey);

            try {
                $this->db->beginTransaction();

                $timeMs = function_exists('milliSeconds') ? \milliSeconds() : (string)(int)(microtime(true) * 1000);
                $insPost = $this->db->prepare(
                    "INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, post_img0, target_scope, post_time, post_status, date_created)
                     VALUES (?, ?, ?, ?, ?, ?, 'all', ?, 'published', NOW())"
                );
                $insPost->execute([
                    \App\services\FamilyHistorianService::HISTORIAN_ID,
                    \App\services\FamilyHistorianService::HISTORIAN_NAME,
                    \App\services\FamilyHistorianService::HISTORIAN_AVATAR,
                    $message,
                    $normCode,
                    $cardImage,
                    $timeMs,
                ]);
                $postNo = (int)$this->db->lastInsertId();

                $insRun = $this->db->prepare(
                    "INSERT INTO family_milestone_feed_runs (family_code, milestone_type, target_id, interval_days, event_date, post_no, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW())"
                );
                $insRun->execute([
                    $normCode,
                    $msType,
                    $targetId,
                    $canonicalIntervalDays,
                    $eventDateIso,
                    $postNo,
                ]);

                $this->db->commit();

                // Broadcast Pusher update
                try {
                    Pusher::broadcastToFamily($normCode, 'new-post', [[
                        'post_no' => $postNo,
                        'id' => \App\services\FamilyHistorianService::HISTORIAN_ID,
                        'fullName' => \App\services\FamilyHistorianService::HISTORIAN_NAME,
                        'profileImg' => \App\services\FamilyHistorianService::HISTORIAN_AVATAR,
                        'postMessage' => $message,
                        'postFamCode' => $normCode,
                        'post_img0' => $cardImage,
                        'date_created' => date('Y-m-d H:i:s'),
                    ]]);
                } catch (\Throwable $pe) {
                    \error_log('[dispatchMilestoneFeedPostsForFamily] Pusher broadcast: ' . $pe->getMessage());
                }

                $publishedCount++;
                $publishedPosts[] = [
                    'post_no' => $postNo,
                    'milestone_type' => $msType,
                    'title' => $title,
                    'interval_days' => $daysRemaining,
                    'post_img0' => $cardImage,
                ];
            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                \error_log('[dispatchMilestoneFeedPostsForFamily] ' . $e->getMessage());
            }
        }

        return [
            'published_count' => $publishedCount,
            'posts' => $publishedPosts,
        ];
    }

    /**
     * Resolves an avatar image filename for a milestone celebrant.
     * Checks:
     * 1. Direct profile_img on milestone data if raster (JPG, PNG, WEBP) or sister PNG if SVG.
     * 2. For calendar events, matches celebrant name in family_nodes first before falling back to event creator user_id.
     * 3. User profilePics table entry by target user_id.
     * 4. Fallback empty string (renderer will draw monogram crest).
     *
     * @param array<string, mixed> $ms
     * @param string $famCode
     * @return string
     */
    public function resolveMemberAvatar(array $ms, string $famCode): string
    {
        $root = dirname(__DIR__, 2);
        $msType = (string)($ms['type'] ?? '');

        // 1. Direct profile_img from milestone
        $rawAvatar = (string)($ms['profile_img'] ?? '');
        if ($rawAvatar !== '') {
            $resolved = $this->sanitizeAndCheckAvatarFile($rawAvatar, $root);
            if ($resolved !== '') {
                return $resolved;
            }
        }

        // Helper closure to match celebrant name in family_nodes
        $lookupNodeByTitle = function () use ($ms, $famCode, $root): string {
            $title = (string)($ms['title'] ?? '');
            $words = preg_split('/[\s\-_]+/', trim($title)) ?: [];
            $searchTerms = [];
            foreach ($words as $word) {
                $w = trim($word, " \t\n\r\0\x0B'’");
                if (mb_strlen($w) >= 3 && !in_array(strtolower($w), ['birthday', 'anniversary', 'wedding', 'celebration', 'party', 'gathering', 'event', 'advance', 'test'], true)) {
                    $searchTerms[] = $w;
                }
            }

            if (!empty($searchTerms)) {
                $firstName = $searchTerms[0];
                $nodeStmt = $this->db->prepare("
                    SELECT avatar_url FROM family_nodes
                    WHERE family_code = ?
                      AND CHAR_LENGTH(TRIM(first_name)) >= 3
                      AND (first_name LIKE ? OR ? LIKE CONCAT('%', TRIM(first_name), '%'))
                    ORDER BY (avatar_url IS NOT NULL AND avatar_url != '') DESC, CHAR_LENGTH(first_name) DESC
                    LIMIT 1
                ");
                $nodeStmt->execute([$famCode, '%' . $firstName . '%', $firstName]);
                $nodeAvatar = (string)$nodeStmt->fetchColumn();
                if ($nodeAvatar !== '') {
                    $resolved = $this->sanitizeAndCheckAvatarFile($nodeAvatar, $root);
                    if ($resolved !== '') {
                        return $resolved;
                    }
                }
            }
            return '';
        };

        // For calendar events, user_id is the event creator—check the celebrant name in family_nodes first
        if ($msType === 'event') {
            $fromNode = $lookupNodeByTitle();
            if ($fromNode !== '') {
                return $fromNode;
            }
        }

        // Check profilePics by target user_id
        $userId = (string)($ms['user_id'] ?? '');
        if ($userId !== '') {
            $stmt = $this->db->prepare("SELECT img FROM profilePics WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $userPic = (string)$stmt->fetchColumn();
            if ($userPic !== '') {
                $resolved = $this->sanitizeAndCheckAvatarFile($userPic, $root);
                if ($resolved !== '') {
                    return $resolved;
                }
            }
        }

        return $lookupNodeByTitle();
    }

    /**
     * Formats a clean, high-prestige display headline for the milestone card.
     * E.g. "Temitope birthday" in #MODERNMAN resolves surname from family_nodes -> "Temitope Olaogun".
     *
     * @param array<string, mixed> $ms
     * @param string $famCode
     * @return string
     */
    private function formatMilestoneCardTitle(array $ms, string $famCode): string
    {
        $rawTitle = trim((string)($ms['title'] ?? 'Family Milestone'));
        if ($rawTitle === '') {
            return 'Family Milestone';
        }

        // If title ends with " birthday" (e.g. "Temitope birthday"), extract the given name
        if (preg_match('/^([a-zA-Z\-’\']+)\s+birthday$/i', $rawTitle, $m)) {
            $givenName = ucfirst(strtolower(trim($m[1])));
            $nodeStmt = $this->db->prepare("
                SELECT last_name FROM family_nodes
                WHERE family_code = ?
                  AND CHAR_LENGTH(TRIM(first_name)) >= 3
                  AND (first_name LIKE ? OR ? LIKE CONCAT('%', TRIM(first_name), '%'))
                  AND last_name IS NOT NULL AND TRIM(last_name) != ''
                LIMIT 1
            ");
            $nodeStmt->execute([$famCode, '%' . $givenName . '%', $givenName]);
            $lastName = trim((string)$nodeStmt->fetchColumn());
            if ($lastName !== '') {
                return $givenName . ' ' . ucwords(strtolower($lastName));
            }
            return $givenName . "'s Birthday";
        }

        return ucwords($rawTitle);
    }

    /**
     * Sanitizes and checks if an avatar file exists in profile images or uploads.
     */
    private function sanitizeAndCheckAvatarFile(string $filename, string $root): string
    {
        $clean = basename(trim($filename));
        if ($clean === '') {
            return '';
        }

        // If SVG, check for corresponding PNG raster fallback
        if (str_ends_with(strtolower($clean), '.svg')) {
            $pngCandidate = substr($clean, 0, -4) . '.png';
            if (is_file($root . '/resources/images/profile/' . $pngCandidate)) {
                return $pngCandidate;
            }
            if (is_file($root . '/public/uploads/' . $pngCandidate)) {
                return $pngCandidate;
            }
            return '';
        }

        // Check in resources/images/profile/
        if (is_file($root . '/resources/images/profile/' . $clean)) {
            return $clean;
        }

        // Check in public/uploads/
        if (is_file($root . '/public/uploads/' . $clean)) {
            return $clean;
        }

        return '';
    }

    /**
     * Scans for daily milestone memories & anniversaries across all families
     * and dispatches native PWA push notifications and in-app notifications,
     * plus family timeline feed posts.
     *
     * @return array{processed_families: int, notifications_sent: int, feed_posts_created: int}
     */
    public function dispatchDailyMilestonePushNotifications(): array
    {
        $processedFamilies = 0;
        $notificationsSent = 0;
        $feedPostsCreated = 0;

        try {
            self::ensureTablesExist($this->db);

            // 1. Dispatch timeline announcement feed posts for active family codes that have upcoming milestones
            $stmtFeedCodes = $this->db->query("
                SELECT DISTINCT UPPER(REPLACE(code, '#', '')) as clean_code FROM (
                    SELECT eventCode as code FROM events 
                    WHERE deleted_at IS NULL 
                      AND eventDate >= CURDATE() 
                      AND eventDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
                      AND eventCode IS NOT NULL AND eventCode != ''
                    UNION
                    SELECT famCode as code FROM personal 
                    WHERE deleted_at IS NULL 
                      AND day IS NOT NULL 
                      AND month IS NOT NULL 
                      AND famCode IS NOT NULL AND famCode != ''
                    UNION
                    SELECT family_code as code FROM family_unions 
                    WHERE is_current = 1 
                      AND family_code IS NOT NULL AND family_code != ''
                ) t WHERE code != ''
            ");
            $feedTargetCodes = $stmtFeedCodes ? $stmtFeedCodes->fetchAll(PDO::FETCH_COLUMN) : [];

            foreach ($feedTargetCodes as $code) {
                $codeStr = trim((string)$code);
                if ($codeStr === '') {
                    continue;
                }
                $processedFamilies++;
                $feedRes = $this->dispatchMilestoneFeedPostsForFamily($codeStr, 7);
                $feedPostsCreated += $feedRes['published_count'];
            }

            // 2. Dispatch notifications per active user across all their associated family codes
            $userStmt = $this->db->query("
                SELECT p.id,
                       GROUP_CONCAT(DISTINCT fc.code SEPARATOR ',') as codes
                FROM personal p
                LEFT JOIN account a ON a.id = p.id
                JOIN (
                    SELECT id, UPPER(REPLACE(famCode, '#', '')) as code FROM personal WHERE famCode IS NOT NULL AND famCode != '' AND deleted_at IS NULL
                    UNION
                    SELECT id, UPPER(REPLACE(familyCode2, '#', '')) as code FROM personal WHERE familyCode2 IS NOT NULL AND familyCode2 != '' AND deleted_at IS NULL
                    UNION
                    SELECT user_id as id, UPPER(REPLACE(family_code, '#', '')) as code FROM user_families WHERE status = 'approved' AND family_code IS NOT NULL AND family_code != ''
                ) fc ON fc.id = p.id
                WHERE p.deleted_at IS NULL
                  AND (a.id IS NULL OR a.deleted_at IS NULL)
                  AND p.id NOT IN ('FAMILY_PLATFORM_OFFICIAL', 'FAMILY_HISTORIAN')
                GROUP BY p.id
            ");
            $users = $userStmt ? $userStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            foreach ($users as $u) {
                $uid = (string)($u['id'] ?? '');
                $rawCodes = (string)($u['codes'] ?? '');
                if ($uid === '' || $rawCodes === '') {
                    continue;
                }
                $userCodes = array_values(array_filter(explode(',', $rawCodes)));
                if (!empty($userCodes)) {
                    $res = $this->dispatchDailyMemoriesAndMilestonesForUser($uid, $userCodes);
                    $notificationsSent += ($res['memories_dispatched'] + $res['milestones_dispatched']);
                }
            }
        } catch (\Throwable $e) {
            \error_log('[MemoryMilestoneService::dispatchDailyMilestonePushNotifications] ' . $e->getMessage());
        }

        return [
            'processed_families' => $processedFamilies,
            'notifications_sent' => $notificationsSent,
            'feed_posts_created' => $feedPostsCreated,
        ];
    }

    /**
     * Helper: Fetch birthdays in family codes.
     *
     * @param array<string> $famCodes
     * @return array<int, array<string, mixed>>
     */
    private function fetchPersonalBirthdays(array $famCodes): array
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            if (empty($cleanedCodes)) {
                return [];
            }

            $inClauses = [];
            $params = [];
            foreach ($cleanedCodes as $code) {
                $norm = strtoupper(str_replace('#', '', trim((string)$code)));
                $inClauses[] = "(
                    UPPER(REPLACE(COALESCE(p.famCode, ''), '#', '')) = ?
                    OR UPPER(REPLACE(COALESCE(p.familyCode2, ''), '#', '')) = ?
                    OR EXISTS (
                        SELECT 1 FROM user_families uf
                        WHERE uf.user_id = p.id
                          AND uf.status = 'approved'
                          AND UPPER(REPLACE(uf.family_code, '#', '')) = ?
                    )
                )";
                $params[] = $norm;
                $params[] = $norm;
                $params[] = $norm;
            }

            $whereClause = implode(' OR ', $inClauses);
            $sqlTemplate = "SELECT DISTINCT p.id, p.firstName, p.lastName, p.day, p.month, p.year, p.famCode, pp.img as profile_img
                      FROM personal p
                      LEFT JOIN profilePics pp ON p.id = pp.id
                      LEFT JOIN account a ON a.id = p.id
                      WHERE (%s)
                        AND p.deleted_at IS NULL
                        AND (a.id IS NULL OR a.deleted_at IS NULL)
                        AND p.id NOT IN ('FAMILY_PLATFORM_OFFICIAL', 'FAMILY_HISTORIAN')
                        AND p.day IS NOT NULL 
                        AND p.month IS NOT NULL";

            $stmt = $this->db->prepare(sprintf($sqlTemplate, $whereClause));
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            \error_log('[fetchPersonalBirthdays] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper: Fetch wedding unions in family codes.
     *
     * @param array<string> $famCodes
     * @return array<int, array<string, mixed>>
     */
    private function fetchFamilyUnions(array $famCodes): array
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            if (empty($cleanedCodes)) {
                return [];
            }

            $inClauses = [];
            $params = [];
            foreach ($cleanedCodes as $code) {
                $norm = strtoupper(str_replace('#', '', trim((string)$code)));
                $inClauses[] = "(
                    UPPER(REPLACE(COALESCE(fu.family_code, ''), '#', '')) = ?
                    OR EXISTS (
                        SELECT 1 FROM family_nodes fn1_sub
                        WHERE fn1_sub.id = fu.partner_1_id
                          AND (
                              UPPER(REPLACE(COALESCE(fn1_sub.family_code, ''), '#', '')) = ?
                              OR fn1_sub.user_id IN (
                                  SELECT p.id FROM personal p
                                  WHERE UPPER(REPLACE(COALESCE(p.famCode, ''), '#', '')) = ?
                                     OR UPPER(REPLACE(COALESCE(p.familyCode2, ''), '#', '')) = ?
                                     OR EXISTS (SELECT 1 FROM user_families uf WHERE uf.user_id = p.id AND uf.status = 'approved' AND UPPER(REPLACE(uf.family_code, '#', '')) = ?)
                              )
                          )
                    )
                )";
                $params[] = $norm;
                $params[] = $norm;
                $params[] = $norm;
                $params[] = $norm;
                $params[] = $norm;
            }

            $whereClause = implode(' OR ', $inClauses);
            $sqlTemplate = "SELECT fu.*, 
                             COALESCE(fn1.user_id, '') as p1_user_id,
                             COALESCE(fn2.user_id, '') as p2_user_id,
                             TRIM(CONCAT(COALESCE(fn1.first_name, ''), ' ', COALESCE(fn1.last_name, ''))) as p1_name,
                             TRIM(CONCAT(COALESCE(fn2.first_name, ''), ' ', COALESCE(fn2.last_name, ''))) as p2_name,
                             COALESCE(pp1.img, fn1.avatar_url, '') as p1_img,
                             COALESCE(pp2.img, fn2.avatar_url, '') as p2_img
                      FROM family_unions fu
                      LEFT JOIN family_nodes fn1 ON fu.partner_1_id = fn1.id
                      LEFT JOIN family_nodes fn2 ON fu.partner_2_id = fn2.id
                      LEFT JOIN profilePics pp1 ON fn1.user_id = pp1.id
                      LEFT JOIN profilePics pp2 ON fn2.user_id = pp2.id
                      WHERE (%s)
                        AND fu.is_current = 1";

            $stmt = $this->db->prepare(sprintf($sqlTemplate, $whereClause));
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            \error_log('[fetchFamilyUnions] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper: Fetch upcoming events.
     *
     * @param array<string> $famCodes
     * @param int $daysAhead
     * @return array<int, array<string, mixed>>
     */
    private function fetchUpcomingEvents(array $famCodes, int $daysAhead): array
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            if (empty($cleanedCodes)) {
                return [];
            }

            $inClauses = [];
            $params = [];
            foreach ($cleanedCodes as $code) {
                $norm = strtoupper(str_replace('#', '', trim((string)$code)));
                $inClauses[] = "(
                    UPPER(REPLACE(COALESCE(e.eventCode, ''), '#', '')) = ?
                    OR e.id IN (
                        SELECT p.id FROM personal p
                        WHERE (
                            UPPER(REPLACE(COALESCE(p.famCode, ''), '#', '')) = ?
                            OR UPPER(REPLACE(COALESCE(p.familyCode2, ''), '#', '')) = ?
                            OR EXISTS (
                                SELECT 1 FROM user_families uf
                                WHERE uf.user_id = p.id
                                  AND uf.status = 'approved'
                                  AND UPPER(REPLACE(uf.family_code, '#', '')) = ?
                            )
                        )
                    )
                )";
                $params[] = $norm;
                $params[] = $norm;
                $params[] = $norm;
                $params[] = $norm;
            }

            $whereClause = implode(' OR ', $inClauses);
            $sqlTemplate = "SELECT e.* 
                      FROM events e
                      WHERE (%s)
                        AND (e.deleted_at IS NULL)
                        AND e.eventDate >= CURDATE()
                        AND e.eventDate <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                      ORDER BY e.eventDate ASC";

            $stmt = $this->db->prepare(sprintf($sqlTemplate, $whereClause));
            $bindParams = array_merge($params, [$daysAhead]);
            $stmt->execute($bindParams);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            \error_log('[fetchUpcomingEvents] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Parses diverse month representations (numeric string, full name, short name) to integer 1-12.
     */
    private function parseMonthToNumber(mixed $month): int
    {
        if ($month === null || $month === '') {
            return 0;
        }

        if (is_numeric($month)) {
            $num = (int)$month;
            return ($num >= 1 && $num <= 12) ? $num : 0;
        }

        $str = strtolower(trim((string)$month));
        $months = [
            'january' => 1, 'jan' => 1,
            'february' => 2, 'feb' => 2,
            'march' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'may' => 5,
            'june' => 6, 'jun' => 6,
            'july' => 7, 'jul' => 7,
            'august' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'october' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'december' => 12, 'dec' => 12,
        ];

        return $months[$str] ?? 0;
    }

    /**
     * Calculates days until next occurrence and target milestone age/anniversary.
     *
     * @return array{days_remaining: int, target_age: int|null, date_badge: string, event_date_str: string, target_year: int, iso_date: string}|null
     */
    private function calculateMilestoneCountdown(
        int $day,
        int $month,
        int $year,
        \DateTimeImmutable $today,
        int $daysAhead
    ): ?array {
        $curYear = (int)$today->format('Y');

        // Check if date is valid for this year (e.g. Feb 29 leap years)
        if (!checkdate($month, $day, $curYear)) {
            $day = 28;
        }

        $thisYearDate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $curYear, $month, $day));
        
        // If date has already passed this year, look at next year
        if ($thisYearDate < $today) {
            $targetDate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $curYear + 1, $month, $day));
        } else {
            $targetDate = $thisYearDate;
        }

        $diffDays = (int)$today->diff($targetDate)->days;
        if ($diffDays > $daysAhead) {
            return null;
        }

        $targetAge = ($year > 0) ? ((int)$targetDate->format('Y') - $year) : null;

        $badge = match ($diffDays) {
            0 => 'Today! 🎉',
            1 => 'Tomorrow',
            default => "in {$diffDays} days",
        };

        return [
            'days_remaining' => $diffDays,
            'target_age' => $targetAge,
            'date_badge' => $badge,
            'event_date_str' => $targetDate->format('M j, Y'),
            'target_year' => (int)$targetDate->format('Y'),
            'iso_date' => $targetDate->format('Y-m-d'),
        ];
    }

    /**
     * Returns special landmark milestone tag for birthdays (e.g. 50th Golden Jubilee, 80th).
     */
    private function getBirthdayMilestoneTag(?int $age): string
    {
        if ($age === null || $age <= 0) {
            return '';
        }

        return match ($age) {
            1 => '1st Birthday Milestone 🎂',
            18 => '18th Milestone Birthday 🌟',
            21 => '21st Key Birthday 🗝️',
            30 => '30th Milestone 🎈',
            40 => '40th Ruby Celebration 🌺',
            50 => '50th Golden Jubilee 👑',
            60 => '60th Diamond Jubilee 💎',
            70 => '70th Platinum Jubilee 🕊️',
            80 => '80th Oak Milestone 🌳',
            90 => '90th Longevity Milestone 🌿',
            100 => '100th Centenary Milestone 🏆',
            default => '',
        };
    }

    /**
     * Builds a pre-filled Festive Cards Studio URL for an anniversary milestone.
     */
    public function buildAnniversaryCardCreateUrl(string $title, string $subtitle = ''): string
    {
        $recipient = preg_replace("/'s\s+Anniversary$/i", '', trim($title)) ?? trim($title);
        $headline = $subtitle !== '' && stripos($subtitle, 'Celebration') === false
            ? "Happy {$subtitle}"
            : 'Happy Wedding Anniversary';

        $params = [
            'occasion' => 'wedding',
            'headline' => $headline,
        ];
        if ($recipient !== '' && strcasecmp($recipient, 'Wedding Anniversary') !== 0) {
            $params['recipient'] = $recipient;
        }

        return '/member/festive-cards?' . http_build_query($params);
    }

    /**
     * Returns traditional anniversary gemstone/material designations.
     */
    private function getAnniversaryMilestoneTag(?int $years): string
    {
        if ($years === null || $years <= 0) {
            return '';
        }

        return match ($years) {
            1 => '1st Paper Anniversary 📜',
            5 => '5th Wood Anniversary 🪵',
            10 => '10th Tin Anniversary 🔔',
            15 => '15th Crystal Anniversary 🔮',
            20 => '20th China Anniversary ☕',
            25 => '25th Silver Jubilee 🥈',
            30 => '30th Pearl Anniversary 🦪',
            40 => '40th Ruby Anniversary 🌹',
            50 => '50th Golden Jubilee 👑',
            60 => '60th Diamond Anniversary 💎',
            default => "{$years} Years of Marriage",
        };
    }

    /**
     * Crafts an emotionally rich, meaningful message for landmark jubilee birthdays only.
     * Generic non-landmark 'Another wonderful year...' copy is intentionally omitted.
     */
    public function getBirthdayMeaningfulMessage(?int $age, string $name): string
    {
        if ($age === null || $age <= 0) {
            return '';
        }

        return match ($age) {
            1 => "A miraculous first year! Celebrating 365 days of baby steps, sweet giggles, and boundless family love.",
            18 => "Stepping into adulthood with strong roots to stay grounded and vibrant wings to reach their greatest dreams.",
            21 => "A milestone key birthday! Unlocking new independence, adventures, and lifelong family pride.",
            30 => "Three incredible decades of character, laughter, and accomplishment. Embracing a shining new chapter.",
            40 => "A radiant ruby milestone! Flourishing in wisdom, purpose, and cherished family achievements.",
            50 => "Golden Jubilee: 50 remarkable years of grace, resilience, and inspiring leadership for our whole family.",
            60 => "Diamond celebration: 60 years of guiding our family with enduring wisdom, kindness, and devotion.",
            70 => "Platinum Jubilee: 70 years of living family history, treasured memories, and generational blessings.",
            80 => "Oak milestone: 80 years of strong roots, protective branches, and a lifetime of unconditional love.",
            90 => "A revered milestone of 90 years of longevity, ancestral stories, and generational triumph.",
            100 => "Centenary celebration: A majestic century of living history, wisdom, and family nobility.",
            default => '',
        };
    }

    /**
     * Crafts an emotionally rich, meaningful message for a wedding anniversary.
     */
    public function getAnniversaryMeaningfulMessage(?int $years, string $title): string
    {
        if ($years === null || $years <= 0) {
            return "Honouring a beautiful union and celebrating the enduring love and companionship that inspires our whole family.";
        }

        return match ($years) {
            1 => "1st Paper Anniversary: One wonderful year of weaving shared dreams and writing the first chapter of forever.",
            5 => "5th Wood Anniversary: Five years of deeply rooted devotion, growing stronger through every passing season.",
            10 => "10th Tin Anniversary: A full decade of unbreakable unity, playful laughter, and shared family victories.",
            15 => "15th Crystal Anniversary: Fifteen luminous years of clarity, deep affection, and sparkling shared milestones.",
            20 => "20th China Anniversary: Two decades of gentle care, mutual respect, and building a welcoming family home.",
            25 => "25th Silver Jubilee: A quarter-century of enduring love and partnership that serves as an inspiring beacon for all of us.",
            30 => "30th Pearl Anniversary: Thirty years of rare, luminous love cultivated through patience, devotion, and shared triumph.",
            40 => "40th Ruby Anniversary: Four decades of steadfast passion, unbreakable partnership, and generational warmth.",
            50 => "50th Golden Jubilee: Half a century of golden devotion, founding an enduring dynasty of love and honour.",
            60 => "60th Diamond Anniversary: An extraordinary diamond milestone of invincible, lifelong love and family legacy.",
            default => "Celebrating {$years} years of devoted marriage and the inspiring partnership that keeps our family united.",
        };
    }

    /**
     * Crafts a meaningful message for an upcoming family event or gathering.
     */
    public function getEventMeaningfulMessage(string $name, string $type): string
    {
        $cleanType = strtolower(trim($type));
        $cleanName = strtolower(trim($name));

        if (str_contains($cleanType, 'reunion') || str_contains($cleanName, 'reunion')) {
            return "Bringing generations together under one roof to reconnect, share laughter, and honour our shared roots.";
        }
        if (str_contains($cleanType, 'heritage') || str_contains($cleanName, 'heritage')) {
            return "Honouring our ancestors, celebrating family folklore, and passing down traditions to the next generation.";
        }
        if (str_contains($cleanType, 'ritual') || str_contains($cleanType, 'weekly')) {
            return "A sacred weekly rhythm to pause, break bread together, and keep our family circle close.";
        }
        return "A special family gathering to celebrate our heritage, create lifelong memories, and strengthen our generational bond.";
    }
}
