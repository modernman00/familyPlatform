<?php

declare(strict_types=1);

namespace App\services;

use App\classes\GeoSite;
use PDO;
use Src\Db;

/**
 * DailyFamilySparkService
 *
 * Unified Daily Family Spark engine: rotates 1-tap games/polls, heritage prompts,
 * and archive sparks at the top of the member feed, aggregating all family replies
 * into a single Living Spark Card.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class DailyFamilySparkService
{
    private const MAX_RESPONSES_RETURNED = 25;

    /**
     * Ensure the Daily Family Spark tables exist (idempotent DDL).
     */
    public static function ensureTablesExist(PDO $db): void
    {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS daily_family_sparks (
                id VARCHAR(64) NOT NULL PRIMARY KEY,
                famCode VARCHAR(64) NOT NULL,
                spark_type ENUM('game', 'heritage', 'archive', 'poll', 'member_question') NOT NULL DEFAULT 'poll',
                title VARCHAR(180) NOT NULL,
                prompt_text TEXT NOT NULL,
                options_json JSON NULL,
                source_ref VARCHAR(128) NULL,
                media_url VARCHAR(512) NULL,
                created_by_user_id VARCHAR(64) NULL,
                created_by_name VARCHAR(120) NULL,
                spark_date DATE NOT NULL,
                status ENUM('active', 'archived') NOT NULL DEFAULT 'active',
                last_activity_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_spark_fam_status_date (famCode, status, spark_date),
                INDEX idx_spark_fam_activity (famCode, last_activity_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS daily_family_spark_responses (
                id VARCHAR(64) NOT NULL PRIMARY KEY,
                spark_id VARCHAR(64) NOT NULL,
                famCode VARCHAR(64) NOT NULL,
                user_id VARCHAR(64) NOT NULL,
                author_name VARCHAR(120) NOT NULL,
                author_avatar VARCHAR(512) NULL,
                response_type ENUM('text', 'vote') NOT NULL DEFAULT 'text',
                body TEXT NULL,
                selected_option_index INT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_spark_resp_spark (spark_id, created_at),
                INDEX idx_spark_resp_user (spark_id, user_id, response_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private static function normaliseFamilyCode(string $famCode): string
    {
        return strtoupper(ltrim(trim($famCode), '#'));
    }

    /**
     * Retrieve the active Daily Family Spark for a family, seeding today's curated Spark if needed.
     *
     * @param string $famCode
     * @param string $userId
     * @param PDO|null $dbOverride
     * @return array<string, mixed>
     */
    public static function getActiveSpark(string $famCode, string $userId, ?PDO $dbOverride = null): array
    {
        $db = $dbOverride ?? Db::connect2();
        self::ensureTablesExist($db);
        $famCode = self::normaliseFamilyCode($famCode);
        $famCode = self::normaliseFamilyCode($famCode);

        $today = date('Y-m-d');

        // Look for an active spark created today or a member-created active spark
        $stmt = $db->prepare(
            "SELECT id, famCode, spark_type, title, prompt_text, options_json, source_ref,
                    media_url, created_by_user_id, created_by_name, spark_date, status,
                    last_activity_at, created_at
             FROM daily_family_sparks
             WHERE famCode = ? AND status = 'active'
             ORDER BY (spark_date = ?) DESC, last_activity_at DESC
             LIMIT 1"
        );
        $stmt->execute([$famCode, $today]);
        /** @var array<string, mixed>|false $spark */
        $spark = $stmt->fetch(PDO::FETCH_ASSOC);

        // Rotate to today's curated Spark if:
        // 1. No active spark exists, OR
        // 2. It's a system spark from a previous day, OR
        // 3. It's a member-created spark from a previous day that has been quiet for 48+ hours.
        $shouldRotate = !$spark;
        if ($spark && (string)$spark['spark_date'] < $today) {
            if (empty($spark['created_by_user_id'])) {
                $shouldRotate = true;
            } else {
                $lastActivityTs = strtotime((string)($spark['last_activity_at'] ?? $spark['created_at'] ?? ''));
                if ($lastActivityTs !== false && $lastActivityTs <= (time() - 48 * 3600)) {
                    $shouldRotate = true;
                }
            }
        }

        if ($shouldRotate) {
            $spark = self::seedCuratedSparkForToday($db, $famCode, $today);
        }

        return self::hydrateSpark($db, $spark, $userId);
    }

    /**
     * Record a vote or text reply inside the Living Spark Card.
     *
     * @param string $sparkId
     * @param string $famCode
     * @param string $userId
     * @param string $authorName
     * @param array<string, mixed> $payload
     * @param PDO|null $dbOverride
     * @return array<string, mixed>
     */
    public static function submitResponse(
        string $sparkId,
        string $famCode,
        string $userId,
        string $authorName,
        array $payload,
        ?PDO $dbOverride = null
    ): array {
        $db = $dbOverride ?? Db::connect2();
        self::ensureTablesExist($db);
        $famCode = self::normaliseFamilyCode($famCode);

        $sparkStmt = $db->prepare(
            "SELECT id, famCode, spark_type, title, prompt_text, options_json, source_ref,
                    media_url, created_by_user_id, created_by_name, spark_date, status,
                    last_activity_at, created_at
             FROM daily_family_sparks
             WHERE id = ? AND famCode = ? AND status = 'active'
             LIMIT 1"
        );
        $sparkStmt->execute([$sparkId, $famCode]);
        /** @var array<string, mixed>|false $spark */
        $spark = $sparkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$spark) {
            return [
                'status' => 'error',
                'http_code' => 404,
                'message' => 'This Family Spark could not be found or is no longer active.',
            ];
        }

        $responseType = trim((string)($payload['response_type'] ?? 'text'));
        if (!in_array($responseType, ['text', 'vote'], true)) {
            return [
                'status' => 'error',
                'http_code' => 422,
                'message' => 'Invalid response type.',
            ];
        }

        $cleanName = trim($authorName) !== '' ? mb_substr(trim($authorName), 0, 120) : 'Family Member';
        $avatar = self::resolveUserAvatar($db, $userId);
        $now = date('Y-m-d H:i:s');

        if ($responseType === 'vote') {
            $options = self::decodeOptions($spark['options_json'] ?? null);
            if ($options === []) {
                return [
                    'status' => 'error',
                    'http_code' => 422,
                    'message' => 'This Spark does not have poll options to vote on.',
                ];
            }

            $rawIndex = $payload['selected_option_index'] ?? null;
            $selectedIndex = -1;
            if (is_int($rawIndex)) {
                $selectedIndex = $rawIndex;
            } elseif (is_string($rawIndex) && ctype_digit(trim($rawIndex))) {
                $selectedIndex = (int)trim($rawIndex);
            }
            if ($selectedIndex < 0 || $selectedIndex >= count($options)) {
                return [
                    'status' => 'error',
                    'http_code' => 422,
                    'message' => 'Please choose a valid option.',
                ];
            }

            // Upsert single vote per user per Spark
            $existingVoteStmt = $db->prepare(
                "SELECT id FROM daily_family_spark_responses
                 WHERE spark_id = ? AND user_id = ? AND response_type = 'vote'
                 LIMIT 1"
            );
            $existingVoteStmt->execute([$sparkId, $userId]);
            $existingVoteId = $existingVoteStmt->fetchColumn();

            if ($existingVoteId) {
                $upd = $db->prepare(
                    "UPDATE daily_family_spark_responses
                     SET selected_option_index = ?, created_at = ?
                     WHERE id = ?"
                );
                $upd->execute([$selectedIndex, $now, (string)$existingVoteId]);
            } else {
                $respId = self::generateId('spr');
                $ins = $db->prepare(
                    "INSERT INTO daily_family_spark_responses
                     (id, spark_id, famCode, user_id, author_name, author_avatar, response_type, body, selected_option_index, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'vote', NULL, ?, ?)"
                );
                $ins->execute([$respId, $sparkId, $famCode, $userId, $cleanName, $avatar, $selectedIndex, $now]);
            }
        } else {
            $rawBody = trim(strip_tags((string)($payload['body'] ?? '')));
            if ($rawBody === '') {
                return [
                    'status' => 'error',
                    'http_code' => 422,
                    'message' => 'Please enter a short reply before posting.',
                ];
            }
            $cleanBody = mb_substr($rawBody, 0, 500);
            $respId = self::generateId('spr');
            $ins = $db->prepare(
                "INSERT INTO daily_family_spark_responses
                 (id, spark_id, famCode, user_id, author_name, author_avatar, response_type, body, selected_option_index, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'text', ?, NULL, ?)"
            );
            $ins->execute([$respId, $sparkId, $famCode, $userId, $cleanName, $avatar, $cleanBody, $now]);
        }

        // Bump Spark activity timestamp so the Living Spark Card reflects fresh momentum
        $bump = $db->prepare("UPDATE daily_family_sparks SET last_activity_at = ? WHERE id = ?");
        $bump->execute([$now, $sparkId]);

        // Best-effort non-blocking notification to Spark author (if member-created & not self)
        self::notifyAuthorOnReply($spark, $famCode, $userId, $cleanName);

        return [
            'status' => 'success',
            'http_code' => 200,
            'message' => $responseType === 'vote' ? 'Your vote has been recorded.' : 'Your reply has been added to the Spark.',
            'data' => self::hydrateSpark($db, $spark, $userId),
        ];
    }

    /**
     * Allow any family member to "Spark a Question" for their family hub.
     *
     * @param string $famCode
     * @param string $userId
     * @param string $authorName
     * @param string $title
     * @param string $promptText
     * @param array<int, string> $options
     * @param PDO|null $dbOverride
     * @return array<string, mixed>
     */
    public static function createMemberSpark(
        string $famCode,
        string $userId,
        string $authorName,
        string $title,
        string $promptText,
        array $options = [],
        ?PDO $dbOverride = null
    ): array {
        $db = $dbOverride ?? Db::connect2();
        self::ensureTablesExist($db);
        $famCode = self::normaliseFamilyCode($famCode);

        $cleanTitle = mb_substr(trim(strip_tags($title)), 0, 140);
        $cleanPrompt = mb_substr(trim(strip_tags($promptText)), 0, 500);

        if ($cleanTitle === '' || $cleanPrompt === '') {
            return [
                'status' => 'error',
                'http_code' => 422,
                'message' => 'Please provide both a title and a question for your family.',
            ];
        }

        $cleanOptions = [];
        foreach ($options as $opt) {
            $trimmed = mb_substr(trim(strip_tags((string)$opt)), 0, 80);
            if ($trimmed !== '') {
                $cleanOptions[] = $trimmed;
            }
            if (count($cleanOptions) >= 4) {
                break;
            }
        }

        $sparkType = count($cleanOptions) >= 2 ? 'poll' : 'member_question';
        $cleanName = trim($authorName) !== '' ? mb_substr(trim($authorName), 0, 120) : 'Family Member';
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $sparkId = self::generateId('spk');

        // Archive previous active sparks for this family so one Living Spark Card stays focused
        $archiveStmt = $db->prepare("UPDATE daily_family_sparks SET status = 'archived' WHERE famCode = ? AND status = 'active'");
        $archiveStmt->execute([$famCode]);

        $ins = $db->prepare(
            "INSERT INTO daily_family_sparks
             (id, famCode, spark_type, title, prompt_text, options_json, source_ref, media_url,
              created_by_user_id, created_by_name, spark_date, status, last_activity_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NULL, NULL, ?, ?, ?, 'active', ?, ?)"
        );
        $ins->execute([
            $sparkId,
            $famCode,
            $sparkType,
            $cleanTitle,
            $cleanPrompt,
            $cleanOptions !== [] ? json_encode($cleanOptions, JSON_UNESCAPED_UNICODE) : null,
            $userId,
            $cleanName,
            $today,
            $now,
            $now,
        ]);

        // Fetch and return hydrated spark
        $stmt = $db->prepare(
            "SELECT id, famCode, spark_type, title, prompt_text, options_json, source_ref,
                    media_url, created_by_user_id, created_by_name, spark_date, status,
                    last_activity_at, created_at
             FROM daily_family_sparks WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$sparkId]);
        /** @var array<string, mixed> $created */
        $created = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'http_code' => 200,
            'message' => 'Your Family Spark is now live at the top of the feed!',
            'data' => self::hydrateSpark($db, $created, $userId),
        ];
    }

    /**
     * Build a privacy-safe WhatsApp share URL and teaser message (zero PII / zero private media).
     *
     * @param array<string, mixed> $spark
     * @return array{whatsapp_url: string, teaser_text: string}
     */
    public static function buildPrivacySafeShareData(array $spark): array
    {
        $baseUrl = class_exists(GeoSite::class) ? GeoSite::baseUrl() : 'https://familyplatform.co.uk';
        $targetUrl = rtrim($baseUrl, '/') . '/member';
        $teaser = "A new Daily Family Spark is live in our Family Hub! Tap to cast your vote or share your answer: " . $targetUrl;

        return [
            'teaser_text' => $teaser,
            'whatsapp_url' => 'https://api.whatsapp.com/send?text=' . rawurlencode($teaser),
        ];
    }

    /**
     * Seed today's curated Spark following the weekly rhythm:
     * Mon/Thu/Sat -> Playful Poll/Game | Tue/Fri -> Heritage Prompt | Wed/Sun -> Archive Spark (with Heritage fallback).
     *
     * @return array<string, mixed>
     */
    private static function seedCuratedSparkForToday(PDO $db, string $famCode, string $today): array
    {
        $dayOfWeek = (int)date('N', strtotime($today) ?: time()); // 1 (Mon) .. 7 (Sun)
        $dayOfYear = (int)date('z', strtotime($today) ?: time());

        $chosen = null;

        if (in_array($dayOfWeek, [3, 7], true)) {
            $chosen = self::buildArchiveSparkCandidate($db, $famCode);
        }

        if ($chosen === null && in_array($dayOfWeek, [1, 4, 6], true)) {
            $polls = self::getCuratedPollBank();
            $chosen = $polls[$dayOfYear % count($polls)];
        }

        if ($chosen === null) {
            $heritage = self::getCuratedHeritageBank();
            $chosen = $heritage[$dayOfYear % count($heritage)];
        }

        // Archive previous system sparks
        $archiveStmt = $db->prepare("UPDATE daily_family_sparks SET status = 'archived' WHERE famCode = ? AND status = 'active'");
        $archiveStmt->execute([$famCode]);

        $sparkId = self::generateId('spk');
        $now = date('Y-m-d H:i:s');

        $ins = $db->prepare(
            "INSERT INTO daily_family_sparks
             (id, famCode, spark_type, title, prompt_text, options_json, source_ref, media_url,
              created_by_user_id, created_by_name, spark_date, status, last_activity_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, 'FamilyPlatform Spark', ?, 'active', ?, ?)"
        );
        $ins->execute([
            $sparkId,
            $famCode,
            $chosen['spark_type'],
            $chosen['title'],
            $chosen['prompt_text'],
            isset($chosen['options']) && is_array($chosen['options'])
                ? json_encode($chosen['options'], JSON_UNESCAPED_UNICODE)
                : null,
            $chosen['source_ref'] ?? null,
            $chosen['media_url'] ?? null,
            $today,
            $now,
            $now,
        ]);

        $stmt = $db->prepare(
            "SELECT id, famCode, spark_type, title, prompt_text, options_json, source_ref,
                    media_url, created_by_user_id, created_by_name, spark_date, status,
                    last_activity_at, created_at
             FROM daily_family_sparks WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$sparkId]);
        /** @var array<string, mixed> $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    /**
     * Attempt to surface an audience-safe nostalgia photo from the family's archive.
     *
     * @return array<string, mixed>|null
     */
    private static function buildArchiveSparkCandidate(PDO $db, string $famCode): ?array
    {
        try {
            $normCode = self::normaliseFamilyCode($famCode);
            $stmt = $db->prepare(
                "SELECT post_no, fullName, post_img0, date_created
                 FROM post
                 WHERE UPPER(REPLACE(COALESCE(postFamCode, ''), '#', '')) = ?
                   AND post_status = 'published'
                   AND date_deleted IS NULL
                   AND post_img0 IS NOT NULL
                   AND post_img0 != ''
                   AND (target_scope IS NULL OR target_scope = '' OR target_scope = 'family' OR target_scope = 'all')
                 ORDER BY RAND()
                 LIMIT 1"
            );
            $stmt->execute([$normCode]);
            /** @var array<string, mixed>|false $post */
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post || empty($post['post_img0'])) {
                return null;
            }

            $img = (string)$post['post_img0'];
            $mediaUrl = (str_starts_with($img, '/') || str_starts_with($img, 'http'))
                ? $img
                : '/resources/images/post/' . $img;

            $author = trim((string)($post['fullName'] ?? 'a family member'));

            return [
                'spark_type' => 'archive',
                'title' => 'Archive Flashback: Who & Where?',
                'prompt_text' => "Shared by {$author} — tell us the story behind this memory, or tag who you recognise!",
                'options' => null,
                'source_ref' => 'post_' . (string)$post['post_no'],
                'media_url' => $mediaUrl,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Hydrate a Spark row with aggregated poll tallies, user vote state, replies, and WhatsApp share metadata.
     *
     * @param array<string, mixed> $spark
     * @return array<string, mixed>
     */
    private static function hydrateSpark(PDO $db, array $spark, string $userId): array
    {
        $sparkId = (string)$spark['id'];
        $options = self::decodeOptions($spark['options_json'] ?? null);

        // Fetch vote tallies
        $voteCounts = array_fill(0, count($options), 0);
        $totalVotes = 0;
        $userVoteIndex = null;

        if ($options !== []) {
            $vStmt = $db->prepare(
                "SELECT user_id, selected_option_index
                 FROM daily_family_spark_responses
                 WHERE spark_id = ? AND response_type = 'vote'"
            );
            $vStmt->execute([$sparkId]);
            /** @var array<int, array<string, mixed>> $votes */
            $votes = $vStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($votes as $v) {
                $idx = (int)$v['selected_option_index'];
                if (isset($voteCounts[$idx])) {
                    $voteCounts[$idx]++;
                    $totalVotes++;
                }
                if ((string)$v['user_id'] === $userId) {
                    $userVoteIndex = $idx;
                }
            }
        }

        $hydratedOptions = [];
        foreach ($options as $idx => $label) {
            $count = $voteCounts[$idx] ?? 0;
            $pct = $totalVotes > 0 ? (int)round(($count / $totalVotes) * 100) : 0;
            $hydratedOptions[] = [
                'index' => $idx,
                'label' => $label,
                'votes' => $count,
                'percentage' => $pct,
            ];
        }

        // Fetch text replies (newest last for conversational reading, capped at MAX_RESPONSES_RETURNED)
        $rStmt = $db->prepare(
            "SELECT id, user_id, author_name, author_avatar, body, created_at
             FROM daily_family_spark_responses
             WHERE spark_id = ? AND response_type = 'text'
             ORDER BY created_at DESC
             LIMIT " . self::MAX_RESPONSES_RETURNED
        );
        $rStmt->execute([$sparkId]);
        /** @var array<int, array<string, mixed>> $replies */
        $replies = array_reverse($rStmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        $countStmt = $db->prepare(
            "SELECT COUNT(*) FROM daily_family_spark_responses WHERE spark_id = ? AND response_type = 'text'"
        );
        $countStmt->execute([$sparkId]);
        $totalReplies = (int)$countStmt->fetchColumn();

        $share = self::buildPrivacySafeShareData($spark);

        return [
            'id' => $sparkId,
            'famCode' => (string)$spark['famCode'],
            'spark_type' => (string)$spark['spark_type'],
            'badge_label' => self::badgeLabelForType((string)$spark['spark_type']),
            'title' => (string)$spark['title'],
            'prompt_text' => (string)$spark['prompt_text'],
            'media_url' => $spark['media_url'] ? (string)$spark['media_url'] : null,
            'created_by_user_id' => $spark['created_by_user_id'] ? (string)$spark['created_by_user_id'] : null,
            'created_by_name' => (string)($spark['created_by_name'] ?? 'FamilyPlatform Spark'),
            'spark_date' => (string)$spark['spark_date'],
            'options' => $hydratedOptions,
            'total_votes' => $totalVotes,
            'user_vote_index' => $userVoteIndex,
            'replies' => $replies,
            'total_replies' => $totalReplies,
            'whatsapp_url' => $share['whatsapp_url'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function decodeOptions(mixed $raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }
        return array_values(array_map(static fn ($v): string => (string)$v, $decoded));
    }

    private static function badgeLabelForType(string $type): string
    {
        return match ($type) {
            'game' => 'FAMILY GAME',
            'poll' => 'QUICK POLL',
            'heritage' => 'HERITAGE SPARK',
            'archive' => 'ARCHIVE FLASHBACK',
            'member_question' => 'FAMILY QUESTION',
            default => 'DAILY SPARK',
        };
    }

    private static function resolveUserAvatar(PDO $db, string $userId): string
    {
        try {
            $stmt = $db->prepare("SELECT img FROM profilePics WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $img = $stmt->fetchColumn();
            if (is_string($img) && trim($img) !== '') {
                return (str_starts_with($img, '/') || str_starts_with($img, 'http'))
                    ? $img
                    : '/resources/images/profile/' . $img;
            }
        } catch (\Throwable $e) {
            // Fallback to default avatar
        }
        return '/resources/images/profile/avatarM.png';
    }

    /**
     * Non-blocking notification when someone replies to a member-created Spark.
     *
     * @param array<string, mixed> $spark
     */
    private static function notifyAuthorOnReply(array $spark, string $famCode, string $senderId, string $senderName): void
    {
        $authorId = (string)($spark['created_by_user_id'] ?? '');
        if ($authorId === '' || $authorId === $senderId) {
            return;
        }
        if (!class_exists(NotificationOrchestrator::class)) {
            return;
        }
        try {
            NotificationOrchestrator::dispatch(
                userId: $authorId,
                category: 'FamilySpark',
                priority: 'normal',
                title: $senderName . ' replied to your Family Spark',
                body: 'Tap to view the latest reply in your Living Spark Card.',
                actionUrl: '/member',
                tag: 'family_spark_' . (string)$spark['id'],
                familyCode: $famCode,
                metadata: ['sender_id' => $senderId, 'spark_id' => (string)$spark['id']]
            );
        } catch (\Throwable $e) {
            // Non-blocking fallback: never fail a Spark reply if notification dispatch fails
        }
    }

    private static function generateId(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function getCuratedPollBank(): array
    {
        return [
            [
                'spark_type' => 'poll',
                'title' => 'Sunday Dinner Debate',
                'prompt_text' => 'When our family gathers for a big meal, which dish disappears from the table first?',
                'options' => ['Roast & Gravy', 'Jollof Rice', 'Homemade Dessert', 'Whatever Auntie Brings'],
                'source_ref' => 'curated_poll_1',
            ],
            [
                'spark_type' => 'poll',
                'title' => 'Family Timekeeping Check',
                'prompt_text' => 'Be honest—when an invitation says 2:00 PM sharp, when do most of us actually arrive?',
                'options' => ['1:50 PM (Early Bird)', '2:00 PM on the dot', '2:30 PM Fashionably Late', '3:00 PM+'],
                'source_ref' => 'curated_poll_2',
            ],
            [
                'spark_type' => 'poll',
                'title' => 'Next Family Reunion Destination',
                'prompt_text' => 'If we could organise a weekend getaway for everyone next year, what style wins?',
                'options' => ['Countryside Cottage', 'Seaside Retreat', 'City Heritage Tour', 'Backyard Garden Party'],
                'source_ref' => 'curated_poll_3',
            ],
            [
                'spark_type' => 'game',
                'title' => 'Family Superlative: The Storyteller',
                'prompt_text' => 'Who in the family can turn a 2-minute errand into a 20-minute gripping story? Drop their name in the replies!',
                'options' => ['Mum / Dad', 'Grandparents', 'Aunts / Uncles', 'One of the Cousins'],
                'source_ref' => 'curated_game_4',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function getCuratedHeritageBank(): array
    {
        return [
            [
                'spark_type' => 'heritage',
                'title' => 'First Job & Early Lessons',
                'prompt_text' => 'What was your very first job—and what did you spend your first paycheque on?',
                'options' => null,
                'source_ref' => 'curated_heritage_1',
            ],
            [
                'spark_type' => 'heritage',
                'title' => 'Family Recipe Vault',
                'prompt_text' => 'Which meal or snack instantly reminds you of growing up? Share the dish and who made it best.',
                'options' => null,
                'source_ref' => 'curated_heritage_2',
            ],
            [
                'spark_type' => 'heritage',
                'title' => 'Unforgettable Family Trip',
                'prompt_text' => 'What is the funniest or most memorable thing that ever happened on a family holiday or road trip?',
                'options' => null,
                'source_ref' => 'curated_heritage_3',
            ],
            [
                'spark_type' => 'heritage',
                'title' => 'Wisdom Worth Keeping',
                'prompt_text' => 'What is one piece of advice from an elder in our family that you still carry with you today?',
                'options' => null,
                'source_ref' => 'curated_heritage_4',
            ],
        ];
    }
}
