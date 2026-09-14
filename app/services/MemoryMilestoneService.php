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
                    'date_created' => (string)$row['date_created'],
                    'formatted_date' => $formattedDate,
                    'years_ago' => $yearsAgo,
                    'years_ago_label' => $yearsLabel,
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

                $milestones[] = [
                    'type' => 'birthday',
                    'title' => $name !== '' ? "{$name}'s Birthday" : "Family Birthday",
                    'subtitle' => $milestoneTag ?: ($age ? "Turning {$age}" : 'Birthday Celebration'),
                    'target_date' => $calc['event_date_str'],
                    'days_remaining' => $calc['days_remaining'],
                    'date_badge' => $calc['date_badge'],
                    'user_id' => (string)($bday['id'] ?? ''),
                    'profile_img' => (string)($bday['profile_img'] ?? ''),
                    'icon' => 'bi-gift-fill',
                    'color_class' => 'bg-warning text-dark',
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
                    $title = ($union['p1_name'] && $union['p2_name'])
                        ? "{$union['p1_name']} & {$union['p2_name']}'s Anniversary"
                        : "Wedding Anniversary";

                    $milestones[] = [
                        'type' => 'anniversary',
                        'title' => $title,
                        'subtitle' => $anniversaryTag ?: ($yearsMarried ? "{$yearsMarried} Years Together" : 'Wedding Anniversary'),
                        'target_date' => $calc['event_date_str'],
                        'days_remaining' => $calc['days_remaining'],
                        'date_badge' => $calc['date_badge'],
                        'user_id' => (string)($union['partner_1_id'] ?? ''),
                        'profile_img' => '',
                        'icon' => 'bi-heart-fill',
                        'color_class' => 'bg-danger text-white',
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

                $milestones[] = [
                    'type' => 'event',
                    'title' => (string)($ev['eventName'] ?? 'Family Gathering'),
                    'subtitle' => (string)($ev['eventType'] ?? 'Special Event'),
                    'target_date' => date('M j, Y', $evTime),
                    'days_remaining' => $diffDays,
                    'date_badge' => $badge,
                    'user_id' => (string)($ev['id'] ?? ''),
                    'profile_img' => '',
                    'icon' => 'bi-calendar-event-fill',
                    'color_class' => 'bg-primary text-white',
                ];
            }
        }

        // Sort milestones chronologically (days remaining ascending)
        usort($milestones, fn(array $a, array $b) => (int)$a['days_remaining'] <=> (int)$b['days_remaining']);

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

            $composedMessage = "✨ [Memory Flashback — {$timeHeader}]: ";
            if ($customNote !== '') {
                $composedMessage .= "{$customNote} \n\n— Original note: \"{$origPost->postMessage}\"";
            } else {
                $composedMessage .= "\"{$origPost->postMessage}\"";
            }

            // 4. Insert into post table
            $insQuery = "INSERT INTO post (id, fullName, postMessage, postFamCode, post_img0, post_time, post_status, date_created)
                         VALUES (?, ?, ?, ?, ?, ?, 'published', CURRENT_TIMESTAMP)";
            $insStmt = $this->db->prepare($insQuery);
            $primaryImg = is_string($origPost->rawAttributes['post_img0'] ?? null) ? $origPost->rawAttributes['post_img0'] : null;
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
     * Scans for daily milestone memories & anniversaries across all families
     * and dispatches native PWA push notifications.
     *
     * @return array{processed_families: int, notifications_sent: int}
     */
    public function dispatchDailyMilestonePushNotifications(): array
    {
        $processedFamilies = 0;
        $notificationsSent = 0;

        try {
            // Find all distinct active family codes
            $stmt = $this->db->query("SELECT DISTINCT famCode FROM personal WHERE famCode IS NOT NULL AND famCode != ''");
            $famCodes = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

            foreach ($famCodes as $code) {
                $codeStr = (string)$code;
                $processedFamilies++;

                // Get members of this family
                $membersStmt = $this->db->prepare("SELECT id, firstName FROM personal WHERE famCode = ?");
                $membersStmt->execute([$codeStr]);
                $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($members)) {
                    continue;
                }

                // Check for nostalgia memory today
                $memQuery = "SELECT post_no, postMessage, (YEAR(CURDATE()) - YEAR(date_created)) as years_ago 
                             FROM post 
                             WHERE postFamCode = ? 
                               AND post_status = 'published' 
                               AND date_deleted IS NULL 
                               AND MONTH(date_created) = MONTH(CURDATE()) 
                               AND DAY(date_created) = DAY(CURDATE()) 
                               AND YEAR(date_created) < YEAR(CURDATE()) 
                             LIMIT 1";
                $memStmt = $this->db->prepare($memQuery);
                $memStmt->execute([$codeStr]);
                $memory = $memStmt->fetch(PDO::FETCH_ASSOC);

                if ($memory) {
                    $years = (int)($memory['years_ago'] ?? 1);
                    $yearsText = $years === 1 ? '1 year ago' : "{$years} years ago";
                    $title = "✨ Family Memory Flashback";
                    $body = "You have a special memory from {$yearsText} today! Tap to view.";

                    foreach ($members as $m) {
                        $uid = (string)($m['id'] ?? '');
                        if ($uid !== '') {
                            $sent = PushNotificationClass::sendPushNotification(
                                userId: $uid,
                                message: $body,
                                url: '/profilePage',
                                title: $title,
                                tag: 'memory-flashback-' . date('Y-m-d')
                            );
                            if ($sent) {
                                $notificationsSent++;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \error_log('[MemoryMilestoneService::dispatchDailyMilestonePushNotifications] ' . $e->getMessage());
        }

        return [
            'processed_families' => $processedFamilies,
            'notifications_sent' => $notificationsSent,
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

            $inQuery = implode(',', array_fill(0, count($cleanedCodes), '?'));
            $query = "SELECT p.id, p.firstName, p.lastName, p.day, p.month, p.year, p.famCode, pp.img as profile_img
                      FROM personal p
                      LEFT JOIN profilePics pp ON p.id = pp.id
                      WHERE p.famCode IN ($inQuery)
                        AND p.day IS NOT NULL 
                        AND p.month IS NOT NULL";

            $stmt = $this->db->prepare($query);
            $stmt->execute($cleanedCodes);
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

            $inQuery = implode(',', array_fill(0, count($cleanedCodes), '?'));
            $query = "SELECT fu.*, 
                             CONCAT(COALESCE(fn1.first_name, ''), ' ', COALESCE(fn1.last_name, '')) as p1_name,
                             CONCAT(COALESCE(fn2.first_name, ''), ' ', COALESCE(fn2.last_name, '')) as p2_name
                      FROM family_unions fu
                      LEFT JOIN family_nodes fn1 ON fu.partner_1_id = fn1.id
                      LEFT JOIN family_nodes fn2 ON fu.partner_2_id = fn2.id
                      WHERE fu.family_code IN ($inQuery)
                        AND fu.is_current = 1";

            $stmt = $this->db->prepare($query);
            $stmt->execute($cleanedCodes);
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

            $inQuery = implode(',', array_fill(0, count($cleanedCodes), '?'));
            $query = "SELECT e.* 
                      FROM events e
                      WHERE (e.eventCode IN ($inQuery) OR e.id IN (
                          SELECT id FROM personal WHERE famCode IN ($inQuery)
                      ))
                      AND e.eventDate >= CURDATE()
                      AND e.eventDate <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                      ORDER BY e.eventDate ASC";

            $stmt = $this->db->prepare($query);
            $bindParams = array_merge($cleanedCodes, $cleanedCodes, [$daysAhead]);
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
     * @return array{days_remaining: int, target_age: int|null, date_badge: string, event_date_str: string}|null
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
            default => "Turning {$age}",
        };
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
}
