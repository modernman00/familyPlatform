<?php

declare(strict_types=1);

namespace App\services;

use App\model\Reel;
use Src\Db;
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

        // 1. Fetch recent media and active posts from the family circle
        $stmtPosts = $db->prepare(
            'SELECT post_no, id, fullName, postMessage, profileImg, post_likes, views_count,
                    post_img0, post_img1, post_img2, post_img3, post_img4, post_img5,
                    date_created
             FROM post
             WHERE (postFamCode = ? OR REPLACE(postFamCode, "#", "") = REPLACE(?, "#", ""))
               AND date_deleted IS NULL
               AND date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY post_likes DESC, views_count DESC, post_no DESC
             LIMIT 20'
        );
        $stmtPosts->execute([$famCode, $famCode]);
        $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Fallback to last 30 days if activity in the last 7 days was quiet
        if (count($posts) < 2) {
            $stmtFallback = $db->prepare(
                'SELECT post_no, id, fullName, postMessage, profileImg, post_likes, views_count,
                        post_img0, post_img1, post_img2, post_img3, post_img4, post_img5,
                        date_created
                 FROM post
                 WHERE (postFamCode = ? OR REPLACE(postFamCode, "#", "") = REPLACE(?, "#", ""))
                   AND date_deleted IS NULL
                   AND date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 ORDER BY post_likes DESC, views_count DESC, post_no DESC
                 LIMIT 20'
            );
            $stmtFallback->execute([$famCode, $famCode]);
            $posts = $stmtFallback->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

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
                    $imgUrl = $post[$imgKey];
                    if (!str_starts_with($imgUrl, '/') && !str_starts_with($imgUrl, 'http')) {
                        $imgUrl = '/' . $imgUrl;
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

        // If no user images were found, provide fallback atmospheric sanctuary slides
        if (empty($mediaScenes)) {
            $heroThumbnail = '/public/img/family_placeholder.png';
            $mediaScenes[] = [
                'type' => 'sanctuary',
                'duration' => 5,
                'image_url' => '/public/img/family_placeholder.png',
                'author' => 'Family Sanctuary',
                'caption' => 'Connecting hearts across generations',
                'likes' => count($posts)
            ];
        }

        // 4. Assemble the dynamic 30-60 second storyboard (ATG Pod 1 / Pod 3 Standard)
        $title = 'Our Family Highlights: ' . $weekStart->format('d M') . ' – ' . $now->format('d M');
        $caption = 'Curating our favourite shared memories, celebrations, and moments from this past week.';

        $storyboardScenes = [];

        // Scene 1: Kinetic Intro Slide
        $storyboardScenes[] = [
            'type' => 'intro',
            'duration' => 4,
            'title' => 'Weekly Highlights',
            'subtitle' => $weekStart->format('d M') . ' – ' . $now->format('d M'),
            'caption' => 'Celebrating the moments that bring our family closer'
        ];

        // Scene 2..N: Photo Highlights & Milestones
        foreach ($mediaScenes as $scene) {
            $storyboardScenes[] = $scene;
        }

        // Include any detected family milestones
        foreach (array_slice($activeMilestones, 0, 2) as $mStone) {
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
            'title' => 'One Family. Endless Love.',
            'subtitle' => 'Approved and shared with our family circle'
        ];

        // Compute total duration (clamped between 30 and 60 seconds)
        $totalDuration = 0;
        foreach ($storyboardScenes as $s) {
            $totalDuration += (int)($s['duration'] ?? 4);
        }
        $totalDuration = max(30, min(60, $totalDuration));

        $audioUrl = '/public/audio/reels/library/acoustic_warmth.wav';
        $musicTitle = 'Acoustic Warmth (Original Family Soundtrack)';

        $storyboardJson = json_encode([
            'title' => $title,
            'duration_seconds' => $totalDuration,
            'music_title' => $musicTitle,
            'audio_url' => $audioUrl,
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
     * @return array<string, mixed>
     */
    public static function approveAndPostReel(
        int $reelId,
        string $userId,
        string $famCode,
        string $userName,
        ?string $profileImg = null
    ): array {
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
            $thumbnailUrl = !empty($reel['thumbnail_url']) ? (string)$reel['thumbnail_url'] : '/public/img/family_placeholder.png';
            $postMessage = "🎥 {$title}\n\nOur weekly highlights have been approved! Cherishing our favourite shared moments, laughter, and celebrations together. ❤️✨";
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
                time(),
                $thumbnailUrl
            ]);

            $postNo = (int)$db->lastInsertId();

            // 3. Mark weekly reel draft as approved atomically inside transaction
            $stmtUpdate = $db->prepare(
                'UPDATE weekly_activity_reels 
                 SET status = "approved", post_no = ?, updated_at = NOW() 
                 WHERE id = ?'
            );
            $stmtUpdate->execute([$postNo, $reelId]);

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
                'aspect_ratio' => '9:16',
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
}
