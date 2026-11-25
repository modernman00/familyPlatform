<?php

declare(strict_types=1);

namespace App\services;

use App\model\Reel;
use Src\Db;
use Src\Explainer\ExplainerStoryboard;
use Src\Explainer\SovereignVideoRenderer;
use PDO;
use Exception;
use DateTimeImmutable;

/**
 * WeeklyActivityVideoCuratorService
 *
 * Curates weekly activities across the family circle (photos, celebrations, milestones,
 * top conversations) and generates an emotional 30-60 second video montage ("Family Reel")
 * delivered to the user's feed for explicit approval before sharing.
 *
 * Conforms to TAT Governance Standards & Chamber 1 Social & Lifestyle Pod.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class WeeklyActivityVideoCuratorService
{
    /**
     * Retrieve any pending weekly reel draft for the given user and family.
     *
     * @param string $userId
     * @param string $famCode
     * @return array<string, mixed>|null
     */
    public static function getPendingReel(string $userId, string $famCode): ?array
    {
        $db = Db::connect2();
        $stmt = $db->prepare(
            'SELECT * FROM weekly_activity_reels 
             WHERE user_id = ? 
               AND (famCode = ? OR REPLACE(famCode, "#", "") = REPLACE(?, "#", ""))
               AND status = "draft_pending_approval"
             ORDER BY id DESC 
             LIMIT 1'
        );
        $stmt->execute([$userId, $famCode, $famCode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        /** @var array<string, mixed> $reel */
        $reel = $row;
        if (!empty($reel['storyboard_json']) && is_string($reel['storyboard_json'])) {
            $reel['storyboard'] = json_decode($reel['storyboard_json'], true) ?: [];
        } else {
            $reel['storyboard'] = [];
        }

        return $reel;
    }

    /**
     * Retrieve any pending weekly fun-facts reel draft for the given user and family,
     * or automatically curate one for the current 7-day window if none has been created yet.
     *
     * @param string $userId
     * @param string $famCode
     * @return array<string, mixed>|null
     */
    public static function getOrCurateWeeklyReel(string $userId, string $famCode): ?array
    {
        $cleanUser = trim($userId);
        $cleanFam = trim($famCode);
        if ($cleanUser === '' || $cleanFam === '') {
            return null;
        }

        try {
            $pending = self::getPendingReel($cleanUser, $cleanFam);
            if ($pending !== null) {
                return $pending;
            }

            // Check if a reel was already approved or dismissed by this user within the current 7-day window
            $db = Db::connect2();
            $stmtRecent = $db->prepare(
                'SELECT id FROM weekly_activity_reels
                 WHERE user_id = ?
                   AND (famCode = ? OR REPLACE(famCode, "#", "") = REPLACE(?, "#", ""))
                   AND status IN ("approved", "discarded")
                   AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 LIMIT 1'
            );
            $stmtRecent->execute([$cleanUser, $cleanFam, $cleanFam]);
            if ($stmtRecent->fetchColumn() !== false) {
                return null;
            }

            return self::curateWeeklyActivity($cleanUser, $cleanFam, false);
        } catch (\Throwable $e) {
            error_log('[WeeklyActivityVideoCuratorService::getOrCurateWeeklyReel] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Curate the week's family activities and generate/refresh a weekly reel draft.
     *
     * @param string $userId
     * @param string $famCode
     * @param bool $forceRegenerate
     * @return array<string, mixed>
     */
    public static function curateWeeklyActivity(string $userId, string $famCode, bool $forceRegenerate = false): array
    {
        $db = Db::connect2();

        // Check if an active draft already exists for this week unless forcing regeneration
        if (!$forceRegenerate) {
            $existing = self::getPendingReel($userId, $famCode);
            if ($existing !== null) {
                return $existing;
            }
        } else {
            // Supersede any previous unapproved draft for this user/family to prevent draft table bloat
            $stmtSupersede = $db->prepare(
                'UPDATE weekly_activity_reels
                 SET status = "discarded", updated_at = NOW()
                 WHERE user_id = ?
                   AND (famCode = ? OR REPLACE(famCode, "#", "") = REPLACE(?, "#", ""))
                   AND status = "draft_pending_approval"'
            );
            $stmtSupersede->execute([$userId, $famCode, $famCode]);
        }

        $now = new DateTimeImmutable();
        $weekStart = $now->modify('-7 days');
        $startDateStr = $weekStart->format('Y-m-d');
        $endDateStr = $now->format('Y-m-d');

        // 1. Fetch recent media and active posts from the family circle (bounded to 50, excluding system bots)
        $stmtPosts = $db->prepare(
            'SELECT post_no, id, fullName, postMessage, profileImg, post_likes, views_count,
                    post_img0, post_img1, post_img2, post_img3, post_img4, post_img5,
                    date_created
             FROM post
             WHERE (postFamCode = ? OR REPLACE(postFamCode, "#", "") = REPLACE(?, "#", ""))
               AND id NOT IN ("FAMILY_PLATFORM_OFFICIAL", "FAMILY_HISTORIAN")
               AND date_deleted IS NULL
               AND date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY post_likes DESC, views_count DESC, post_no DESC
             LIMIT 50'
        );
        $stmtPosts->execute([$famCode, $famCode]);
        $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $usedFallbackWindow = false;

        // Fallback to last 30 days if activity in the last 7 days was quiet
        if (count($posts) < 2) {
            $usedFallbackWindow = true;
            $stmtFallback = $db->prepare(
                'SELECT post_no, id, fullName, postMessage, profileImg, post_likes, views_count,
                        post_img0, post_img1, post_img2, post_img3, post_img4, post_img5,
                        date_created
                 FROM post
                 WHERE (postFamCode = ? OR REPLACE(postFamCode, "#", "") = REPLACE(?, "#", ""))
                   AND id NOT IN ("FAMILY_PLATFORM_OFFICIAL", "FAMILY_HISTORIAN")
                   AND date_deleted IS NULL
                   AND date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 ORDER BY post_likes DESC, views_count DESC, post_no DESC
                 LIMIT 50'
            );
            $stmtFallback->execute([$famCode, $famCode]);
            $posts = $stmtFallback->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        // 1b. Fetch comment counts per post using parameterised IN placeholders
        $commentCountsByPost = [];
        $postNos = array_values(array_filter(array_map(
            static fn(array $p): int => (int)($p['post_no'] ?? 0),
            $posts
        ), static fn(int $id): bool => $id > 0));

        if (!empty($postNos)) {
            $placeholders = implode(',', array_fill(0, count($postNos), '?'));
            $commentSql = sprintf(
                'SELECT post_no, COUNT(*) AS cnt FROM comment WHERE post_no IN (%s) AND date_deleted IS NULL GROUP BY post_no',
                $placeholders
            );
            $stmtComments = $db->prepare($commentSql);
            $stmtComments->execute($postNos);
            $commentRows = $stmtComments->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($commentRows as $cRow) {
                $commentCountsByPost[(int)($cRow['post_no'] ?? 0)] = (int)($cRow['cnt'] ?? 0);
            }
        }

        $funFacts = self::computeWeeklyFunFacts($posts, $commentCountsByPost, $weekStart, $now, $usedFallbackWindow);

        // 2. Fetch milestone dates (birthdays / celebrations) within the current window
        $stmtMilestones = $db->prepare(
            'SELECT id, firstName, lastName, day, month, year
             FROM personal
             WHERE (famCode = ? OR REPLACE(famCode, "#", "") = REPLACE(?, "#", ""))
               AND day IS NOT NULL AND day != "" AND month IS NOT NULL AND month != ""
             LIMIT 15'
        );
        $stmtMilestones->execute([$famCode, $famCode]);
        $members = $stmtMilestones->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $activeMilestones = [];
        $currentMonth = (int)$now->format('n');
        $currentDay = (int)$now->format('j');
        foreach ($members as $m) {
            $name = trim(($m['firstName'] ?? '') . ' ' . ($m['lastName'] ?? ''));
            $bDay = (int)($m['day'] ?? 0);
            $bMonthRaw = strtolower(trim((string)($m['month'] ?? '')));
            $bMonth = is_numeric($bMonthRaw) ? (int)$bMonthRaw : (int)date('n', (int)strtotime("1 {$bMonthRaw} 2000"));

            if ($bMonth > 0 && $bDay > 0) {
                // If within 5 days of today
                if ($bMonth === $currentMonth && abs($bDay - $currentDay) <= 5) {
                    $activeMilestones[] = [
                        'type' => 'birthday',
                        'title' => "{$name}'s Birthday",
                        'subtitle' => 'Celebrating another wonderful year together',
                        'image' => null
                    ];
                }
            }
        }

        // 3. Extract media scenes from posts
        $mediaScenes = [];
        $heroThumbnail = null;
        foreach ($posts as $post) {
            for ($i = 0; $i <= 5; $i++) {
                $imgKey = "post_img{$i}";
                if (!empty($post[$imgKey]) && is_string($post[$imgKey])) {
                    $imgUrl = trim($post[$imgKey]);
                    if ($imgUrl === '' || \App\classes\VideoParser::isVideoUrl($imgUrl)) {
                        continue;
                    }
                    if (!str_starts_with($imgUrl, '/') && !str_starts_with($imgUrl, 'http')) {
                        $imgUrl = '/resources/images/post/' . $imgUrl;
                    }
                    if ($heroThumbnail === null) {
                        $heroThumbnail = $imgUrl;
                    }
                    $snippet = !empty($post['postMessage']) ? mb_substr(strip_tags((string)$post['postMessage']), 0, 80) : 'Family moment';
                    $mediaScenes[] = [
                        'type' => 'photo',
                        'duration' => 5,
                        'image_url' => $imgUrl,
                        'author' => (string)($post['fullName'] ?? 'Family Member'),
                        'caption' => $snippet,
                        'likes' => (int)($post['post_likes'] ?? 0)
                    ];
                    if (count($mediaScenes) >= 6) {
                        break 2;
                    }
                }
            }
        }

        // If no user images were found, synthesise a high-resolution Fun Facts card via FamilyHistorianCardRenderer
        if (empty($mediaScenes)) {
            $mostLovedForCard = $funFacts['most_loved_post'] ?? null;
            $synthesisedCard = FamilyHistorianCardRenderer::renderAndSave(
                'activity_milestone',
                [
                    'total_posts' => max(1, (int)($funFacts['total_posts'] ?? 0)),
                    'peak_day' => (string)($funFacts['golden_day'] ?? 'Sunday'),
                    'top_author' => (string)($mostLovedForCard['author'] ?? ($funFacts['top_storyteller']['name'] ?? 'Family Circle')),
                    'top_score' => (int)($funFacts['total_likes'] ?? 0) + (int)($funFacts['total_comments'] ?? 0),
                    'top_excerpt' => (string)($mostLovedForCard['excerpt'] ?? 'Celebrating our weekly family fun facts, shared stories, and milestones together.'),
                ],
                $famCode,
                'weekly_funfacts_' . $startDateStr
            );
            $heroThumbnail = $synthesisedCard !== null
                ? '/resources/images/post/' . $synthesisedCard
                : '/images/logo/logo.png';
            $mediaScenes[] = [
                'type' => 'sanctuary',
                'duration' => 5,
                'image_url' => $heroThumbnail,
                'author' => 'Family Sanctuary',
                'caption' => 'Connecting hearts across generations',
                'likes' => count($posts)
            ];
        }

        // 4. Assemble the dynamic 30-60 second Fun Facts + Highlights storyboard
        $title = 'Our Family Fun Facts: ' . $weekStart->format('d M') . ' – ' . $now->format('d M');
        $caption = 'Curating our weekly family fun facts, favourite shared memories, and celebrations.';

        $storyboardScenes = [];

        // Scene 1: Kinetic Fun Facts Intro Slide
        $storyboardScenes[] = [
            'type' => 'fun_fact_intro',
            'duration' => 5,
            'badge' => 'WEEKLY FAMILY FUN FACTS  •  ' . strtoupper($weekStart->format('d M')) . ' – ' . strtoupper($now->format('d M')),
            'title' => 'Our Family Week in Numbers',
            'subtitle' => $funFacts['window_label'],
            'caption' => sprintf(
                '%d %s shared across %d family %s',
                $funFacts['total_posts'],
                $funFacts['total_posts'] === 1 ? 'moment' : 'moments',
                max(1, $funFacts['active_members']),
                max(1, $funFacts['active_members']) === 1 ? 'member' : 'members'
            ),
            'stat_value' => $funFacts['total_posts'],
            'stat_label' => 'new moments',
            'accent' => '#38bdf8',
        ];

        // Scene 2: Fun Fact #1 — Top Storyteller Leaderboard
        if (!empty($funFacts['top_storyteller'])) {
            $top = $funFacts['top_storyteller'];
            $storyboardScenes[] = [
                'type' => 'fun_fact_leaderboard',
                'duration' => 6,
                'badge' => 'FUN FACT #1  •  TOP STORYTELLER',
                'title' => $top['name'] . ' led the family feed!',
                'subtitle' => sprintf(
                    '%d%% of this %s’s posts came from %s (%d %s)',
                    $top['share_pct'],
                    $usedFallbackWindow ? 'month' : 'week',
                    $top['name'],
                    $top['posts'],
                    $top['posts'] === 1 ? 'post' : 'posts'
                ),
                'leaderboard' => $funFacts['leaderboard'],
                'accent' => '#0ea5e9',
            ];
        }

        // Scene 3: Fun Fact #2 — Golden Hour & Busiest Day
        $storyboardScenes[] = [
            'type' => 'fun_fact_golden_hour',
            'duration' => 6,
            'badge' => 'FUN FACT #2  •  GOLDEN HOUR',
            'title' => $funFacts['golden_day'] . ' around ' . $funFacts['golden_hour'] . ' was our liveliest hour',
            'subtitle' => sprintf(
                '%d %s captured alongside our favourite conversations.',
                $funFacts['total_photos'],
                $funFacts['total_photos'] === 1 ? 'photo' : 'photos'
            ),
            'daily_counts' => $funFacts['daily_counts'],
            'golden_day' => $funFacts['golden_day'],
            'accent' => '#f59e0b',
        ];

        // Scene 4: Fun Fact #3 — Most Loved Moment
        $mostLoved = $funFacts['most_loved_post'];
        $storyboardScenes[] = [
            'type' => 'fun_fact_most_loved',
            'duration' => 6,
            'badge' => 'FUN FACT #3  •  MOST LOVED MOMENT',
            'title' => sprintf(
                '%d hearts & %d comments kept the circle buzzing!',
                $funFacts['total_likes'],
                $funFacts['total_comments']
            ),
            'quote_author' => $mostLoved['author'] ?? 'Family Sanctuary',
            'quote_text' => $mostLoved['excerpt'] ?? 'Connecting hearts and stories across generations.',
            'quote_likes' => (int)($mostLoved['likes'] ?? $funFacts['total_likes']),
            'quote_comments' => (int)($mostLoved['comments'] ?? $funFacts['total_comments']),
            'accent' => '#f43f5e',
        ];

        // Scene 5..N: Top Photo Highlights (up to 3 photos so total stays ~30-45s)
        foreach (array_slice($mediaScenes, 0, 3) as $scene) {
            $storyboardScenes[] = $scene;
        }

        // Include any detected family milestones
        foreach (array_slice($activeMilestones, 0, 1) as $mStone) {
            $storyboardScenes[] = [
                'type' => 'milestone',
                'duration' => 4,
                'title' => $mStone['title'],
                'subtitle' => $mStone['subtitle'],
                'image_url' => $mStone['image'] ?? $heroThumbnail
            ];
        }

        // Final Scene: Outro Call to Celebrate
        $storyboardScenes[] = [
            'type' => 'outro',
            'duration' => 4,
            'badge' => 'READY FOR THE FAMILY FEED',
            'title' => 'One Family. Endless Love.',
            'subtitle' => 'Generated privately from your circle — ready for 1-tap approval',
            'accent' => '#10b981',
        ];

        // Compute total duration (clamped between 30 and 60 seconds)
        $totalDuration = 0;
        foreach ($storyboardScenes as $s) {
            $totalDuration += (int)$s['duration'];
        }
        $totalDuration = max(30, min(60, $totalDuration));

        $audioUrl = '/public/audio/reels/library/acoustic_warmth.wav';
        $musicTitle = 'Acoustic Warmth (Original Family Soundtrack)';

        $explainerManifests = self::buildExplainerManifests($title, $storyboardScenes);

        $storyboardJson = json_encode([
            'title' => $title,
            'duration_seconds' => $totalDuration,
            'music_title' => $musicTitle,
            'audio_url' => $audioUrl,
            'aspect_ratios' => ['16:9', '9:16'],
            'default_aspect_ratio' => '16:9',
            'fun_facts' => $funFacts,
            'explainer_manifests' => $explainerManifests,
            'scenes' => $storyboardScenes
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';

        // 5. Persist the draft into weekly_activity_reels
        $videoUrl = "/api/weekly-reel/canvas-player?week={$startDateStr}";
        $stmtInsert = $db->prepare(
            'INSERT INTO weekly_activity_reels (
                user_id, famCode, week_start_date, week_end_date, title, caption,
                duration_seconds, video_url, thumbnail_url, audio_url, music_title,
                storyboard_json, status, created_at
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "draft_pending_approval", NOW())'
        );
        $stmtInsert->execute([
            $userId,
            $famCode,
            $startDateStr,
            $endDateStr,
            $title,
            $caption,
            $totalDuration,
            $videoUrl,
            $heroThumbnail,
            $audioUrl,
            $musicTitle,
            $storyboardJson
        ]);

        $reelId = (int)$db->lastInsertId();

        return [
            'id' => $reelId,
            'user_id' => $userId,
            'famCode' => $famCode,
            'week_start_date' => $startDateStr,
            'week_end_date' => $endDateStr,
            'title' => $title,
            'caption' => $caption,
            'duration_seconds' => $totalDuration,
            'video_url' => $videoUrl,
            'thumbnail_url' => $heroThumbnail,
            'audio_url' => $audioUrl,
            'music_title' => $musicTitle,
            'storyboard_json' => $storyboardJson,
            'storyboard' => json_decode($storyboardJson, true) ?: [],
            'status' => 'draft_pending_approval'
        ];
    }

    /**
     * Approve the weekly reel draft and publish it directly to the family post feed.
     *
     * @param int $reelId
     * @param string $userId
     * @param string $famCode
     * @param string $userName
     * @param string|null $profileImg
     * @param string $aspectRatio '16:9' or '9:16'
     * @return array<string, mixed>
     */
    public static function approveAndPostReel(
        int $reelId,
        string $userId,
        string $famCode,
        string $userName,
        ?string $profileImg = null,
        string $aspectRatio = '9:16'
    ): array {
        $aspectRatio = in_array($aspectRatio, ['16:9', '9:16'], true) ? $aspectRatio : '9:16';
        $db = Db::connect2();

        try {
            $db->beginTransaction();

            // 1. Atomic Ownership, Tenancy & Single-Approval Verification (FOR UPDATE lock prevents race double-posts)
            $stmt = $db->prepare(
                'SELECT * FROM weekly_activity_reels 
                 WHERE id = ? 
                   AND user_id = ? 
                   AND (famCode = ? OR REPLACE(famCode, "#", "") = REPLACE(?, "#", ""))
                   AND status = "draft_pending_approval"
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmt->execute([$reelId, $userId, $famCode, $famCode]);
            /** @var array<string, mixed>|false $reel */
            $reel = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reel) {
                $db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Weekly reel not found, already approved, or access denied.'
                ];
            }

            $title = strip_tags((string)$reel['title']);
            $videoUrl = (string)$reel['video_url'];
            $storyboardDecoded = !empty($reel['storyboard_json']) && is_string($reel['storyboard_json'])
                ? (json_decode($reel['storyboard_json'], true) ?: [])
                : [];
            $facts = is_array($storyboardDecoded['fun_facts'] ?? null) ? $storyboardDecoded['fun_facts'] : [];

            // Synthesise a dedicated Fun Facts summary card as the fallback/OpenGraph thumbnail (never a random member photo)
            $postImg0 = '';
            $mostLovedCard = is_array($facts['most_loved_post'] ?? null) ? $facts['most_loved_post'] : [];
            $renderedCard = FamilyHistorianCardRenderer::renderAndSave(
                'activity_milestone',
                [
                    'total_posts' => max(1, (int)($facts['total_posts'] ?? 1)),
                    'peak_day' => (string)($facts['golden_day'] ?? 'Sunday'),
                    'top_author' => (string)($mostLovedCard['author'] ?? ($facts['top_storyteller']['name'] ?? $userName)),
                    'top_score' => (int)($facts['total_likes'] ?? 0) + (int)($facts['total_comments'] ?? 0),
                    'top_excerpt' => (string)($mostLovedCard['excerpt'] ?? 'Cherishing our favourite shared moments, laughter, and celebrations together.'),
                ],
                $famCode,
                'weekly_approved_' . (int)$reel['id']
            );
            if ($renderedCard !== null) {
                $postImg0 = $renderedCard;
            }
            $thumbnailUrl = $postImg0 !== '' ? '/resources/images/post/' . $postImg0 : '/images/logo/logo.png';

            $factLines = [];
            if (!empty($facts['total_posts'])) {
                $factLines[] = sprintf(
                    '• 📊 Family Activity: %d %s & %d %s shared across %d active family %s.',
                    (int)$facts['total_posts'],
                    ((int)$facts['total_posts'] === 1 ? 'moment' : 'moments'),
                    (int)($facts['total_photos'] ?? 0),
                    ((int)($facts['total_photos'] ?? 0) === 1 ? 'photo' : 'photos'),
                    max(1, (int)($facts['active_members'] ?? 1)),
                    (max(1, (int)($facts['active_members'] ?? 1)) === 1 ? 'member' : 'members')
                );
            }
            if (!empty($facts['top_storyteller']['name'])) {
                $factLines[] = sprintf(
                    '• 🏆 Top Storyteller: %s led the family feed (%d%% of posts).',
                    (string)$facts['top_storyteller']['name'],
                    (int)($facts['top_storyteller']['share_pct'] ?? 100)
                );
            }
            if (!empty($facts['golden_day']) && !empty($facts['golden_hour'])) {
                $factLines[] = sprintf(
                    '• ⏰ Golden Hour: %s around %s was our liveliest gathering time.',
                    (string)$facts['golden_day'],
                    (string)$facts['golden_hour']
                );
            }
            if (!empty($facts['most_loved_post']['author'])) {
                $factLines[] = sprintf(
                    '• ❤️ Most-Loved Moment: Shared by %s (%d reactions & %d comments).',
                    (string)$facts['most_loved_post']['author'],
                    (int)($facts['total_likes'] ?? 0),
                    (int)($facts['total_comments'] ?? 0)
                );
            }
            $factsBlock = !empty($factLines) ? ("\n\n" . implode("\n", $factLines)) : '';
            $postMessage = "🎥 {$title}{$factsBlock}\n\nOur weekly family fun facts & highlights have been published! Cherishing our favourite shared moments, laughter, and celebrations together. ❤️✨";
            $cleanUserName = mb_substr(trim(strip_tags($userName)), 0, 120) ?: 'Family Member';

            // 2. Insert into the main post feed
            $stmtPost = $db->prepare(
                'INSERT INTO post (
                    id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, post_img0, date_created
                 ) VALUES (?, ?, ?, ?, ?, ?, "published", ?, NOW())'
            );
            $stmtPost->execute([
                $userId,
                $cleanUserName,
                $profileImg ?? '',
                $postMessage,
                $famCode,
                (int)round(microtime(true) * 1000),
                $postImg0 !== '' ? $postImg0 : $thumbnailUrl
            ]);

            $postNo = (int)$db->lastInsertId();

            // Persist the user's chosen aspect ratio in storyboard_json for feed playback parity
            $storyboardDecoded['approved_aspect_ratio'] = $aspectRatio;
            $updatedStoryboardJson = json_encode($storyboardDecoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                ?: (string)$reel['storyboard_json'];

            // 3. Mark weekly reel draft as approved atomically inside transaction
            $stmtUpdate = $db->prepare(
                'UPDATE weekly_activity_reels 
                 SET status = "approved", post_no = ?, storyboard_json = ?, updated_at = NOW() 
                 WHERE id = ?'
            );
            $stmtUpdate->execute([$postNo, $updatedStoryboardJson, $reelId]);

            $db->commit();
        } catch (\Throwable $txEx) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('WeeklyActivityVideoCuratorService::approveAndPostReel transaction error: ' . $txEx->getMessage());
            return [
                'success' => false,
                'error' => 'Could not publish weekly reel at this time.'
            ];
        }

        // 4. Register in family_reels table for synchronous reels tray playback
        try {
            Reel::createReel([
                'user_id' => $userId,
                'famCode' => $famCode,
                'caption' => $title,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'aspect_ratio' => $aspectRatio,
                'category' => 'milestone',
                'music_title' => (string)($reel['music_title'] ?? 'Acoustic Warmth'),
                'audio_url' => (string)($reel['audio_url'] ?? ''),
                'video_volume' => 100,
                'music_volume' => 50,
                'audio_start_sec' => 0.0
            ]);
        } catch (\Throwable $e) {
            // Defensive logging; do not break feed post publishing if reels tray insert encounters a non-fatal constraint
            error_log('WeeklyActivityVideoCuratorService: Reel sync warning: ' . $e->getMessage());
        }

        return [
            'success' => true,
            'post_no' => $postNo,
            'message' => 'Weekly reel approved and published to the family feed successfully!'
        ];
    }

    /**
     * Dismiss the weekly reel draft without posting.
     *
     * @param int $reelId
     * @param string $userId
     * @param string $famCode
     * @return array<string, mixed>
     */
    public static function dismissReel(int $reelId, string $userId, string $famCode): array
    {
        $db = Db::connect2();

        $stmt = $db->prepare(
            'UPDATE weekly_activity_reels 
             SET status = "discarded", updated_at = NOW() 
             WHERE id = ? 
               AND user_id = ? 
               AND (famCode = ? OR REPLACE(famCode, "#", "") = REPLACE(?, "#", ""))'
        );
        $stmt->execute([$reelId, $userId, $famCode, $famCode]);

        return [
            'success' => true,
            'message' => 'Weekly reel dismissed.'
        ];
    }

    /**
     * Compute structured Weekly Fun Facts from family feed posts and comments.
     *
     * @param array<int, array<string, mixed>> $posts
     * @param array<int, int> $commentCountsByPost
     * @param DateTimeImmutable $weekStart
     * @param DateTimeImmutable $now
     * @param bool $usedFallbackWindow
     * @return array<string, mixed>
     */
    public static function computeWeeklyFunFacts(
        array $posts,
        array $commentCountsByPost,
        DateTimeImmutable $weekStart,
        DateTimeImmutable $now,
        bool $usedFallbackWindow = false
    ): array {
        $totalPosts = count($posts);
        $totalPhotos = 0;
        $totalLikes = 0;
        $totalComments = 0;
        $memberStats = [];
        $dailyCounts = [
            'Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0,
            'Fri' => 0, 'Sat' => 0, 'Sun' => 0,
        ];
        $hourCounts = array_fill(0, 24, 0);
        $mostLovedPost = null;
        $bestScore = -1;

        foreach ($posts as $post) {
            $postNo = (int)($post['post_no'] ?? 0);
            $authorId = (string)($post['id'] ?? 'unknown');
            $authorName = trim(strip_tags((string)($post['fullName'] ?? 'Family Member')));
            if ($authorName === '') {
                $authorName = 'Family Member';
            }

            $likes = max(0, (int)($post['post_likes'] ?? 0));
            $comments = max(0, (int)($commentCountsByPost[$postNo] ?? 0));
            $totalLikes += $likes;
            $totalComments += $comments;

            for ($i = 0; $i <= 5; $i++) {
                if (!empty($post["post_img{$i}"])) {
                    $totalPhotos++;
                }
            }

            if (!isset($memberStats[$authorId])) {
                $memberStats[$authorId] = [
                    'name' => $authorName,
                    'posts' => 0,
                    'likes' => 0,
                ];
            }
            $memberStats[$authorId]['posts']++;
            $memberStats[$authorId]['likes'] += $likes;

            $rawDate = (string)($post['date_created'] ?? '');
            $ts = $rawDate !== '' ? strtotime($rawDate) : false;
            if ($ts !== false) {
                $dayShort = date('D', $ts);
                if (isset($dailyCounts[$dayShort])) {
                    $dailyCounts[$dayShort]++;
                }
                $hour = (int)date('G', $ts);
                $hourCounts[$hour] = $hourCounts[$hour] + 1;
            }

            $engagementScore = $likes + ($comments * 2);
            if ($engagementScore > $bestScore) {
                $bestScore = $engagementScore;
                $cleanMsg = trim(preg_replace('/\s+/', ' ', strip_tags((string)($post['postMessage'] ?? ''))) ?? '');
                if ($cleanMsg === '') {
                    $cleanMsg = 'Shared a special family moment';
                }
                if (mb_strlen($cleanMsg) > 95) {
                    $cleanMsg = mb_substr($cleanMsg, 0, 92) . '...';
                }
                $mostLovedPost = [
                    'author' => $authorName,
                    'excerpt' => $cleanMsg,
                    'likes' => $likes,
                    'comments' => $comments,
                ];
            }
        }

        usort($memberStats, static function (array $a, array $b): int {
            if ($b['posts'] !== $a['posts']) {
                return $b['posts'] <=> $a['posts'];
            }
            return $b['likes'] <=> $a['likes'];
        });
        $leaderboard = array_slice($memberStats, 0, 3);

        $topStoryteller = null;
        if (!empty($leaderboard) && $totalPosts > 0) {
            $top = $leaderboard[0];
            $topStoryteller = [
                'name' => $top['name'],
                'posts' => $top['posts'],
                'share_pct' => (int)round(($top['posts'] / $totalPosts) * 100),
            ];
        }

        $dayNames = [
            'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday',
            'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday',
        ];
        $peakShortDay = 'Sun';
        $peakDayCount = -1;
        foreach ($dailyCounts as $d => $cnt) {
            if ($cnt > $peakDayCount) {
                $peakDayCount = $cnt;
                $peakShortDay = $d;
            }
        }
        if ($peakDayCount <= 0) {
            $peakShortDay = 'Sun';
        }
        $goldenDay = $dayNames[$peakShortDay];

        $peakHour = 19;
        $peakHourCount = -1;
        foreach ($hourCounts as $hr => $cnt) {
            if ($cnt > $peakHourCount) {
                $peakHourCount = $cnt;
                $peakHour = (int)$hr;
            }
        }
        if ($peakHourCount <= 0) {
            $peakHour = 19;
        }
        $goldenHour = date('g:00 A', mktime($peakHour, 0, 0, 1, 1, 2026) ?: 0);

        return [
            'window_label' => ($usedFallbackWindow ? 'Last 30 days' : 'This week') . ' • ' . $weekStart->format('d M') . ' – ' . $now->format('d M'),
            'total_posts' => $totalPosts,
            'total_photos' => $totalPhotos,
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments,
            'active_members' => count($memberStats),
            'leaderboard' => $leaderboard,
            'top_storyteller' => $topStoryteller,
            'daily_counts' => $dailyCounts,
            'golden_day' => $goldenDay,
            'golden_hour' => $goldenHour,
            'most_loved_post' => $mostLovedPost,
        ];
    }

    /**
     * Compile 60fps SovereignVideoRenderer client manifests for both 16:9 and 9:16.
     *
     * @param string $title
     * @param array<int, array<string, mixed>> $storyboardScenes
     * @return array<string, mixed>
     */
    private static function buildExplainerManifests(string $title, array $storyboardScenes): array
    {
        $manifests = [];
        try {
            $renderer = new SovereignVideoRenderer();
            $compiledScenes = [];
            $runningDuration = 0.0;

            foreach ($storyboardScenes as $idx => $s) {
                $dur = max(3.0, min(8.0, (float)($s['duration'] ?? 5.0)));
                if ($runningDuration + $dur > 58.0) {
                    break;
                }
                $runningDuration += $dur;
                $sceneTitle = preg_replace('/[^\p{L}\p{N}\s\-\.\,\:\!\&\']+/u', '', (string)($s['title'] ?? 'Family Highlight')) ?: 'Family Highlight';
                $narration = preg_replace('/[^\p{L}\p{N}\s\-\.\,\:\!\&\']+/u', '', (string)($s['subtitle'] ?? $s['caption'] ?? $sceneTitle)) ?: $sceneTitle;

                $compiledScenes[] = [
                    'id' => 'scene_' . ($idx + 1),
                    'duration' => $dur,
                    'title' => mb_substr($sceneTitle, 0, 80),
                    'narration' => mb_substr($narration, 0, 140),
                    'camera' => ['x' => 0.5, 'y' => 0.5, 'zoom' => 1.05],
                    'captions' => [
                        ['start' => 0.2, 'end' => max(1.0, $dur - 0.3), 'text' => mb_substr($narration, 0, 140)],
                    ],
                ];
            }

            if (empty($compiledScenes)) {
                return [];
            }

            $cleanTitle = preg_replace('/[^\p{L}\p{N}\s\-\.\,\:\!\&\']+/u', '', $title) ?: 'Family Fun Facts';
            foreach ([ExplainerStoryboard::ASPECT_16_9, ExplainerStoryboard::ASPECT_9_16] as $ratio) {
                $storyboard = ExplainerStoryboard::fromArray([
                    'title' => mb_substr($cleanTitle, 0, 80),
                    'app_name' => 'family',
                    'aspect_ratio' => $ratio,
                    'theme' => 'glassmorphic-dark',
                    'visual_format' => ExplainerStoryboard::FORMAT_MOTION_GRAPHICS,
                    'scenes' => $compiledScenes,
                ]);
                $manifest = $renderer->generateClientManifest($storyboard);
                $manifests[$ratio] = [
                    'dimensions' => $manifest['timeline']['dimensions'] ?? ($ratio === '9:16' ? ['width' => 1080, 'height' => 1920] : ['width' => 1920, 'height' => 1080]),
                    'fps' => $manifest['timeline']['fps'] ?? 60,
                    'total_frames' => $manifest['timeline']['total_frames'] ?? 0,
                    'total_duration' => $manifest['timeline']['total_duration'] ?? $runningDuration,
                    'subtitles_vtt' => $manifest['subtitles_vtt'] ?? '',
                ];
            }
        } catch (\Throwable $e) {
            error_log('WeeklyActivityVideoCuratorService::buildExplainerManifests fallback: ' . $e->getMessage());
        }

        return $manifests;
    }
}
