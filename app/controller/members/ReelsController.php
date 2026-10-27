<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\Reel;
use App\model\AllMembersData;
use App\classes\PushNotificationClass;
use App\classes\VideoParser;
use App\services\AnalyticsService;
use Src\Db;
use Src\functionality\SignIn;
use Exception;

final class ReelsController extends BaseController
{
    /**
     * Render the Full-Screen Immersive Family Reels View
     */
    public function index(): void
    {
        try {
            $targetReelId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $targetReel = null;
            if ($targetReelId > 0) {
                $targetReel = Reel::getReelById($targetReelId);
            }

            $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');

            $ogMeta = null;
            if ($targetReel) {
                $creatorName = trim(($targetReel['firstName'] ?? 'Family') . ' ' . ($targetReel['lastName'] ?? ''));
                $title = "Family Reel by {$creatorName}";
                $description = !empty($targetReel['caption']) ? (string)$targetReel['caption'] : "Watch this family memory by {$creatorName} on Family Platform.";
                $rawThumb = !empty($targetReel['thumbnail_url']) ? (string)$targetReel['thumbnail_url'] : '';

                if (str_starts_with($rawThumb, 'http://') || str_starts_with($rawThumb, 'https://')) {
                    $image = $rawThumb;
                } elseif (!empty($rawThumb)) {
                    $image = $baseUrl . '/' . ltrim($rawThumb, '/');
                } elseif (!empty($targetReel['profilePics'])) {
                    $image = $baseUrl . '/resources/images/profile/' . basename((string)$targetReel['profilePics']);
                } else {
                    $image = $baseUrl . '/public/img/favicon/android-chrome-512x512.png';
                }

                $ogMeta = [
                    'title' => $title,
                    'description' => $description,
                    'image' => $image,
                    'url' => $baseUrl . '/reels?id=' . $targetReel['id'],
                ];
            }

            // Detect social bot/crawler scrapers for WhatsApp, iMessage, Facebook, Twitter, etc.
            $userAgent = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
            $isBot = false;
            $botSignatures = [
                'whatsapp', 'facebookexternalhit', 'facebot', 'twitterbot',
                'applebot', 'slackbot', 'telegrambot', 'skypeuripreview',
                'linkedinbot', 'discordbot', 'pinterest', 'googlebot', 'bingbot'
            ];
            foreach ($botSignatures as $sig) {
                if (str_contains($userAgent, $sig)) {
                    $isBot = true;
                    break;
                }
            }

            // If crawler bot, serve instant OG HTML markup without requiring login session
            if ($isBot && $ogMeta) {
                header('Content-Type: text/html; charset=utf-8');
                $escTitle = htmlspecialchars($ogMeta['title'], ENT_QUOTES, 'UTF-8');
                $escDesc = htmlspecialchars($ogMeta['description'], ENT_QUOTES, 'UTF-8');
                $escImg = htmlspecialchars($ogMeta['image'], ENT_QUOTES, 'UTF-8');
                $escUrl = htmlspecialchars($ogMeta['url'], ENT_QUOTES, 'UTF-8');

                echo "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n" .
                     "    <meta charset=\"UTF-8\">\n" .
                     "    <title>{$escTitle}</title>\n" .
                     "    <meta name=\"description\" content=\"{$escDesc}\">\n" .
                     "    <meta property=\"og:type\" content=\"video.other\">\n" .
                     "    <meta property=\"og:site_name\" content=\"Family Platform\">\n" .
                     "    <meta property=\"og:title\" content=\"{$escTitle}\">\n" .
                     "    <meta property=\"og:description\" content=\"{$escDesc}\">\n" .
                     "    <meta property=\"og:image\" content=\"{$escImg}\">\n" .
                     "    <meta property=\"og:image:secure_url\" content=\"{$escImg}\">\n" .
                     "    <meta property=\"og:image:type\" content=\"image/jpeg\">\n" .
                     "    <meta property=\"og:image:width\" content=\"720\">\n" .
                     "    <meta property=\"og:image:height\" content=\"1280\">\n" .
                     "    <meta property=\"og:url\" content=\"{$escUrl}\">\n" .
                     "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n" .
                     "    <meta name=\"twitter:title\" content=\"{$escTitle}\">\n" .
                     "    <meta name=\"twitter:description\" content=\"{$escDesc}\">\n" .
                     "    <meta name=\"twitter:image\" content=\"{$escImg}\">\n" .
                     "</head>\n<body>\n" .
                     "    <h1>{$escTitle}</h1>\n" .
                     "    <p>{$escDesc}</p>\n" .
                     "    <img src=\"{$escImg}\" alt=\"Reel Thumbnail\">\n" .
                     "</body>\n</html>";
                exit;
            }

            if (empty($_SESSION['id'])) {
                SignIn::verify();
            }

            $userId = (string)($_SESSION['id'] ?? '');
            $famCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($famCode) && !empty($userId)) {
                $pStmt = Db::connect2()->prepare("SELECT famCode FROM personal WHERE id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $famCode = trim((string)$pStmt->fetchColumn());
                if (!empty($famCode)) {
                    $_SESSION['famCode'] = $famCode;
                }
            }
            if (empty($famCode) && !empty($userId)) {
                $pStmt = Db::connect2()->prepare("SELECT famCode FROM personal WHERE id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $famCode = trim((string)$pStmt->fetchColumn());
                if (!empty($famCode)) {
                    $_SESSION['famCode'] = $famCode;
                }
            }

            $data = $this->membersData();
            $initialReels = Reel::getReelsFeed($userId, $famCode, 30, 0);

            if ($targetReel && !empty($targetReel['id'])) {
                $alreadyInFeed = false;
                foreach ($initialReels as $idx => $r) {
                    if ((int)$r['id'] === (int)$targetReel['id']) {
                        $alreadyInFeed = true;
                        if ($idx > 0) {
                            array_splice($initialReels, $idx, 1);
                            array_unshift($initialReels, $targetReel);
                        }
                        break;
                    }
                }
                if (!$alreadyInFeed && self::canAccessReel($targetReel, $userId)) {
                    array_unshift($initialReels, $targetReel);
                }
            }

            // Track reel theater view
            AnalyticsService::track($userId, 'reel_theater_view', null, ['count' => count($initialReels)]);

            view('member/reels', [
                'data' => $data,
                'initialReels' => $initialReels,
                'totalReels' => count($initialReels),
                'ogMeta' => $ogMeta
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * API: Get JSON Feed of Family Reels (Infinite Scroll)
     */
    public function getFeed(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $famCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($famCode) && !empty($userId)) {
                $pStmt = Db::connect2()->prepare("SELECT famCode FROM personal WHERE id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $famCode = trim((string)$pStmt->fetchColumn());
                if (!empty($famCode)) {
                    $_SESSION['famCode'] = $famCode;
                }
            }

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $reels = Reel::getReelsFeed($userId, $famCode, $limit, $offset);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload, never HTML-rendered.
            echo json_encode([
                'status' => 'success',
                'data' => $reels,
                'count' => count($reels)
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Upload & Publish a Family Reel
     */
    public function uploadReel(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $famCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($famCode) && !empty($userId)) {
                $pStmt = Db::connect2()->prepare("SELECT famCode FROM personal WHERE id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $famCode = trim((string)$pStmt->fetchColumn());
                if (!empty($famCode)) {
                    $_SESSION['famCode'] = $famCode;
                }
            }

            if (empty($userId)) {
                throw new Exception('Unauthorized user session', 401);
            }

            if (empty($famCode)) {
                $pStmt = Db::connect2()->prepare("SELECT famCode FROM personal WHERE id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $famCode = trim((string)$pStmt->fetchColumn());
                if (!empty($famCode)) {
                    $_SESSION['famCode'] = $famCode;
                }
            }

            if (empty($famCode)) {
                throw new Exception('Unauthorized user session: no family code found', 401);
            }

            $caption = isset($_POST['caption']) ? trim((string)$_POST['caption']) : '';
            $category = isset($_POST['category']) ? trim((string)$_POST['category']) : 'milestone';
            if ($category === 'custom') {
                $custom = strtolower(trim((string)($_POST['custom_category'] ?? '')));
                $custom = trim((string)preg_replace('/[^a-z0-9]+/', '_', $custom), '_');
                $category = $custom !== '' ? substr($custom, 0, 30) : 'milestone';
            }
            $musicTitle = isset($_POST['music_title']) ? trim((string)$_POST['music_title']) : 'Original Family Audio';
            $videoUrlInput = isset($_POST['video_url']) ? trim((string)$_POST['video_url']) : '';
            $videoPath = '';
            $thumbnailPath = null;

            // Handle direct video file upload
            if (!empty($_FILES['video_file']['name'])) {
                $file = $_FILES['video_file'];
                // MIME => extension. The stored extension is derived from the
                // *sniffed* type only — never from the client-supplied filename
                // or $_FILES['type'] (both attacker-controlled), so an uploaded
                // .php can't land in the webroot with a .php name.
                $allowedMimes = [
                    'video/mp4'          => 'mp4',
                    'video/webm'         => 'webm',
                    'video/quicktime'    => 'mov',
                    'video/x-matroska'   => 'mkv',
                    'video/ogg'          => 'ogv',
                ];

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = $finfo !== false ? finfo_file($finfo, $file['tmp_name']) : false;
                if ($finfo !== false) {
                    finfo_close($finfo);
                }

                if (!is_string($mimeType) || !isset($allowedMimes[$mimeType])) {
                    throw new Exception('Invalid video format. Supported formats: MP4, WebM, QuickTime (MOV).', 400);
                }

                if ($file['size'] > 100 * 1024 * 1024) { // 100MB limit
                    throw new Exception('Video file exceeds maximum allowed size (100MB).', 400);
                }

                $ext = $allowedMimes[$mimeType];
                $uniqueName = 'reel_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetDir = __DIR__ . '/../../../resources/videos/reels/';

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                $targetPath = $targetDir . $uniqueName;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception('Failed to save uploaded video on server.', 500);
                }

                $videoPath = '/resources/videos/reels/' . $uniqueName;
            } elseif (!empty($videoUrlInput)) {
                // Parse video URL (YouTube, Vimeo, Cloudflare Stream, or direct video)
                $videoData = VideoParser::parseVideoUrl($videoUrlInput);
                if (!$videoData) {
                    $videoPath = $videoUrlInput;
                } else {
                    $videoPath = $videoData['originalUrl'];
                    $thumbnailPath = $videoData['thumbnailUrl'];
                }
            } else {
                throw new Exception('Please provide a video file or a supported video URL.', 400);
            }

            // Check for client-generated canvas video thumbnail (Base64 JPEG)
            $thumbnailData = isset($_POST['thumbnail_data']) ? (string)$_POST['thumbnail_data'] : '';
            if (!empty($thumbnailData) && str_contains($thumbnailData, 'base64,')) {
                $rawBase64 = explode('base64,', $thumbnailData)[1];
                $decodedImg = base64_decode($rawBase64, true);
                // Confirm the bytes really are a raster image before writing.
                $imgInfo = is_string($decodedImg) ? @getimagesizefromstring($decodedImg) : false;
                $okThumb = is_array($imgInfo)
                    && in_array($imgInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
                    && strlen((string) $decodedImg) <= 5 * 1024 * 1024;
                if ($okThumb) {
                    $thumbDir = __DIR__ . '/../../../resources/images/reels/thumbs/';
                    if (!is_dir($thumbDir)) {
                        mkdir($thumbDir, 0755, true);
                    }
                    $thumbName = 'thumb_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.jpg';
                    file_put_contents($thumbDir . $thumbName, $decodedImg);
                    $thumbnailPath = '/resources/images/reels/thumbs/' . $thumbName;
                }
            }

            // Multi-Angle Chrono-Sync Resolution (Tenant Isolated)
            $clusterId = null;
            $rawCluster = isset($_POST['chrono_sync_cluster_id']) ? trim((string)$_POST['chrono_sync_cluster_id']) : '';
            $linkReelId = isset($_POST['link_reel_id']) ? (int)$_POST['link_reel_id'] : 0;
            $isMultiAngle = !empty($_POST['is_multi_angle']);

            if (!empty($rawCluster) && preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $rawCluster)) {
                $clusterId = $rawCluster;
            } elseif ($linkReelId > 0) {
                // Link to an existing reel strictly within the same family tenant
                $db = Db::connect2();
                $linkStmt = $db->prepare("SELECT id, chrono_sync_cluster_id FROM family_reels WHERE id = ? AND famCode = ? LIMIT 1");
                $linkStmt->execute([$linkReelId, $famCode]);
                $linkedReel = $linkStmt->fetch(\PDO::FETCH_ASSOC);
                if ($linkedReel) {
                    if (!empty($linkedReel['chrono_sync_cluster_id'])) {
                        $clusterId = (string)$linkedReel['chrono_sync_cluster_id'];
                    } else {
                        // Generate a new shared cluster ID and backfill the linked parent
                        $clusterId = 'cluster_' . time() . '_' . bin2hex(random_bytes(4));
                        $updLinked = $db->prepare("UPDATE family_reels SET chrono_sync_cluster_id = ? WHERE id = ?");
                        $updLinked->execute([$clusterId, $linkReelId]);
                    }
                }
            } elseif ($isMultiAngle) {
                $clusterId = 'cluster_' . time() . '_' . bin2hex(random_bytes(4));
            }

            $rawAngleLabel = isset($_POST['chrono_sync_label']) ? trim((string)$_POST['chrono_sync_label']) : '';
            $chronoSyncLabel = $rawAngleLabel !== '' ? mb_substr($rawAngleLabel, 0, 60) : ($clusterId ? 'Perspective' : null);

            $rawTimeBridge = isset($_POST['time_bridge_node_id']) ? (int)$_POST['time_bridge_node_id'] : 0;
            $timeBridgeNodeId = $rawTimeBridge > 0 ? $rawTimeBridge : null;

            // Audio soundtrack & dual-track volume handling
            $audioUrl = null;
            $videoVolume = isset($_POST['video_volume']) ? max(0, min(100, (int)$_POST['video_volume'])) : 100;
            $musicVolume = isset($_POST['music_volume']) ? max(0, min(100, (int)$_POST['music_volume'])) : 40;
            $audioStartSec = isset($_POST['audio_start_sec']) ? max(0.0, min(3600.0, (float)$_POST['audio_start_sec'])) : 0.0;

            // 1. Direct custom audio file upload (MP3, WAV, OGG, M4A, AAC)
            if (!empty($_FILES['audio_file']['name'])) {
                $audioFile = $_FILES['audio_file'];
                $allowedAudioMimes = [
                    'audio/mpeg'       => 'mp3',
                    'audio/mp3'        => 'mp3',
                    'audio/wav'        => 'wav',
                    'audio/x-wav'      => 'wav',
                    'audio/ogg'        => 'ogg',
                    'audio/mp4'        => 'm4a',
                    'audio/x-m4a'      => 'm4a',
                    'audio/aac'        => 'aac',
                ];

                $aFinfo = finfo_open(FILEINFO_MIME_TYPE);
                $aMimeType = $aFinfo !== false ? finfo_file($aFinfo, $audioFile['tmp_name']) : false;
                if ($aFinfo !== false) {
                    finfo_close($aFinfo);
                }

                if (!is_string($aMimeType) || !isset($allowedAudioMimes[$aMimeType])) {
                    throw new Exception('Invalid audio format. Supported formats: MP3, WAV, OGG, M4A, AAC.', 400);
                }

                if ($audioFile['size'] > 20 * 1024 * 1024) { // 20MB limit
                    throw new Exception('Audio file exceeds maximum allowed size (20MB).', 400);
                }

                $aExt = $allowedAudioMimes[$aMimeType];
                $uniqueAudioName = 'audio_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $aExt;
                $audioTargetDir = __DIR__ . '/../../../public/audio/reels/custom/';

                if (!is_dir($audioTargetDir)) {
                    mkdir($audioTargetDir, 0755, true);
                }

                $audioTargetPath = $audioTargetDir . $uniqueAudioName;
                if (!move_uploaded_file($audioFile['tmp_name'], $audioTargetPath)) {
                    throw new Exception('Failed to save uploaded audio file on server.', 500);
                }

                $audioUrl = '/audio/reels/custom/' . $uniqueAudioName;
                if ($musicTitle === 'Original Family Audio' || $musicTitle === 'Original Audio') {
                    $origAudioName = pathinfo((string)$audioFile['name'], PATHINFO_FILENAME);
                    $musicTitle = !empty($origAudioName) ? mb_substr(trim($origAudioName), 0, 80) : 'Custom Family Soundtrack';
                }
            } elseif (!empty($_POST['audio_url'])) {
                // Curated library track OR "Use This Sound" track
                $candidateAudioUrl = trim((string)$_POST['audio_url']);
                // Strict validation: must be internal relative path starting with /audio/reels/ or /resources/videos/reels/
                if (preg_match('#^/(audio/reels/|resources/videos/reels/)[a-zA-Z0-9_.-]+(\.[a-zA-Z0-9]{3,4})?$#', $candidateAudioUrl) && !str_contains($candidateAudioUrl, '..')) {
                    $audioUrl = $candidateAudioUrl;
                }
            }

            // Create Reel Record
            $rawAspect = isset($_POST['aspect_ratio']) ? trim((string)$_POST['aspect_ratio']) : '9:16';
            $allowedAspects = ['9:16', '16:9', '1:1', '4:5', 'cinema'];
            $safeAspect = in_array($rawAspect, $allowedAspects, true) ? $rawAspect : '9:16';

            $reelId = Reel::createReel([
                'user_id' => $userId,
                'famCode' => $famCode,
                'caption' => $caption,
                'video_url' => $videoPath,
                'thumbnail_url' => $thumbnailPath,
                'aspect_ratio' => $safeAspect,
                'category' => $category,
                'music_title' => $musicTitle,
                'audio_url' => $audioUrl,
                'video_volume' => $videoVolume,
                'music_volume' => $musicVolume,
                'audio_start_sec' => $audioStartSec,
                'chrono_sync_cluster_id' => $clusterId,
                'chrono_sync_label' => $chronoSyncLabel,
                'time_bridge_node_id' => $timeBridgeNodeId,
            ]);

            $createdReel = Reel::getReelById($reelId, $userId);

            // Synchronise hashtags for family reel
            try {
                \App\services\HashtagService::syncHashtags(
                    $famCode,
                    'reel',
                    (string)$reelId,
                    $userId,
                    $caption
                );
            } catch (\Throwable $th) {
                error_log('[ReelsController::uploadReel hashtags] ' . $th->getMessage());
            }

            // Track reel creation
            AnalyticsService::track($userId, 'reel_create', (string)$reelId, [
                'category' => $category,
                'has_upload' => !empty($_FILES['video_file']['name']),
            ]);

            // Notify family members about the new Family Reel
            try {
                $creatorName = trim(($createdReel['firstName'] ?? '') . ' ' . ($createdReel['lastName'] ?? ''));
                if ($creatorName === '') {
                    $creatorName = trim(($_SESSION['fName'] ?? '') . ' ' . ($_SESSION['lName'] ?? ''));
                }
                if ($creatorName === '') {
                    $creatorName = 'A family member';
                }

                $members = AllMembersData::AllMembersEmailByFamCode($famCode, $userId);
                $reelUrl = '/reels?id=' . (int)$reelId;
                $captionSnippet = $caption !== '' ? mb_substr($caption, 0, 70) : 'Watch the latest family memory!';
                $notifMsg = "{$creatorName} shared a new Family Reel: \"{$captionSnippet}\"";

                $db = Db::connect2();
                $stmt = $db->prepare("
                    INSERT INTO notification 
                    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                    VALUES (?, ?, ?, ?, 'Reel', ?, ?, 'new', NOW())
                ");

                foreach ($members as $member) {
                    $targetId = (string)($member['id'] ?? '');
                    if ($targetId === '' || $targetId === $userId) {
                        continue;
                    }

                    try {
                        $stmt->execute([
                            $userId,
                            $targetId,
                            $creatorName,
                            "🎬 New Reel from {$creatorName}",
                            $notifMsg,
                            $reelUrl
                        ]);
                    } catch (\Throwable) {}

                    try {
                        PushNotificationClass::sendPushNotification(
                            userId: $targetId,
                            message: $notifMsg,
                            url: $reelUrl,
                            title: '🎬 New Family Reel',
                            tag: 'reel-' . $reelId
                        );
                    } catch (\Throwable) {}
                }

                // Notify mentioned members in reel caption
                try {
                    $captionMentioned = \App\services\CommentThreadService::extractMentionedIds($caption, $members, $userId);
                    if (!empty($captionMentioned)) {
                        $cMentionTitle = '🎬 Tagged in a Family Reel';
                        $cMentionUrl = '/reels?id=' . (int)$reelId;
                        $cMentionMsg = "{$creatorName} tagged you in a Family Reel: \"{$captionSnippet}\"";
                        $cmStmt = $db->prepare("
                            INSERT INTO notification 
                            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                            VALUES (?, ?, ?, 'Reel Tag', 'Reel', ?, ?, 'new', NOW())
                        ");
                        foreach ($captionMentioned as $cId) {
                            try {
                                $cmStmt->execute([
                                    $userId,
                                    $cId,
                                    $creatorName,
                                    $cMentionMsg,
                                    $cMentionUrl
                                ]);
                                PushNotificationClass::sendPushNotification(
                                    userId: $cId,
                                    message: $cMentionMsg,
                                    url: $cMentionUrl,
                                    title: $cMentionTitle,
                                    tag: 'reel-tag-' . $reelId
                                );
                            } catch (\Throwable) {}
                        }
                    }
                } catch (\Throwable $capEx) {
                    error_log('[ReelsController::uploadReel caption mentions warning] ' . $capEx->getMessage());
                }
            } catch (\Throwable $notifEx) {
                error_log('[ReelsController::uploadReel notification error] ' . $notifEx->getMessage());
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'message' => 'Family Reel published successfully!',
                'data' => $createdReel
            ]);
        } catch (\Throwable $e) {
            http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Determine whether the current authenticated user has permission to view,
     * react, or comment on a family reel.
     *
     * Grants access if:
     * 1. The user is the author/creator of the reel.
     * 2. The user holds Super Admin privileges.
     * 3. The user's active session or registered families share the reel's explicit famCode.
     * 4. The user and the reel author share the author's primary famCode.
     * 5. The user and the reel author share ANY approved family in personal or user_families.
     * 6. The user and the reel author are connected through approved kinship (requestMgt).
     *
     * @param array<string, mixed> $targetReel
     * @param string $userId
     * @return bool
     */
    public static function canAccessReel(array $targetReel, string $userId): bool
    {
        if ($userId === '') {
            return false;
        }

        $authorId = (string)($targetReel['user_id'] ?? '');
        if ($authorId !== '' && hash_equals($userId, $authorId)) {
            return true;
        }

        if (BaseController::isSuperAdmin($userId)) {
            return true;
        }

        $reelFamCode = trim((string)($targetReel['famCode'] ?? ''));

        // 1. Reel's own family code matches current user session/membership
        if ($reelFamCode !== '' && BaseController::sessionSharesFamily($reelFamCode)) {
            return true;
        }

        // 2. Reel author's primary family code matches current user session/membership
        $authorPrimaryFam = trim((string)($targetReel['authorFamCode'] ?? ''));
        if ($authorPrimaryFam === '' && $authorId !== '') {
            try {
                $stmt = Db::connect2()->prepare("SELECT famCode FROM personal WHERE id = ? LIMIT 1");
                $stmt->execute([$authorId]);
                $authorPrimaryFam = trim((string)$stmt->fetchColumn());
            } catch (\Throwable $e) {
                $authorPrimaryFam = '';
            }
        }
        if ($authorPrimaryFam !== '' && BaseController::sessionSharesFamily($authorPrimaryFam)) {
            return true;
        }

        if ($authorId === '') {
            return $reelFamCode === '';
        }

        // 3. User and author share ANY family code across personal or user_families
        try {
            $sharedStmt = Db::connect2()->prepare("
                SELECT 1 FROM (
                    SELECT UPPER(TRIM(REPLACE(famCode, '#', ''))) AS code FROM personal WHERE id = ? AND famCode IS NOT NULL AND famCode != ''
                    UNION
                    SELECT UPPER(TRIM(REPLACE(family_code, '#', ''))) AS code FROM user_families WHERE user_id = ? AND family_code IS NOT NULL AND family_code != ''
                ) viewer_fams
                INNER JOIN (
                    SELECT UPPER(TRIM(REPLACE(famCode, '#', ''))) AS code FROM personal WHERE id = ? AND famCode IS NOT NULL AND famCode != ''
                    UNION
                    SELECT UPPER(TRIM(REPLACE(family_code, '#', ''))) AS code FROM user_families WHERE user_id = ? AND family_code IS NOT NULL AND family_code != ''
                ) author_fams ON viewer_fams.code = author_fams.code
                LIMIT 1
            ");
            $sharedStmt->execute([$userId, $userId, $authorId, $authorId]);
            if ((bool)$sharedStmt->fetchColumn()) {
                return true;
            }
        } catch (\Throwable $e) {
            error_log('[canAccessReel] shared family check failed: ' . $e->getMessage());
        }

        // 4. Approved Kinship Circle link (requestMgt)
        try {
            $kinStmt = Db::connect2()->prepare("
                SELECT 1 FROM requestMgt
                WHERE LOWER(status) IN ('approved','accepted')
                  AND ((approver_id = ? AND requester_id = ?) OR (approver_id = ? AND requester_id = ?))
                LIMIT 1
            ");
            $kinStmt->execute([$userId, $authorId, $authorId, $userId]);
            if ((bool)$kinStmt->fetchColumn()) {
                return true;
            }
        } catch (\Throwable $e) {
            error_log('[canAccessReel] kinship check failed: ' . $e->getMessage());
        }

        // 5. Fallback session check
        if (BaseController::sessionCanViewMember($authorId, $reelFamCode !== '' ? $reelFamCode : $authorPrimaryFam)) {
            return true;
        }

        return false;
    }

    /**
     * API: Toggle Reaction on Reel (Like, Love, Celebrate)
     */
    public function toggleReaction(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $reelId = (int)($payload['reel_id'] ?? 0);
            $type = (string)($payload['reaction_type'] ?? 'like');

            if (!$reelId || empty($userId)) {
                throw new Exception('Invalid reel ID or user session', 400);
            }

            $targetReel = Reel::getReelById($reelId, $userId);
            if (!$targetReel) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Reel not found']);
                return;
            }
            if (!self::canAccessReel($targetReel, $userId)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family reel']);
                return;
            }

            $result = Reel::toggleReaction($reelId, $userId, $type);

            // Notify reel owner on added reaction
            if ($result['action'] === 'added') {
                try {
                    $targetReel = Reel::getReelById($reelId, $userId);
                    $ownerId = (string)($targetReel['user_id'] ?? '');
                    if ($ownerId !== '' && $ownerId !== $userId) {
                        $reactorName = trim(($_SESSION['fName'] ?? '') . ' ' . ($_SESSION['lName'] ?? ''));
                        if ($reactorName === '') {
                            $reactorName = 'A family member';
                        }
                        $reelUrl = '/reels?id=' . $reelId;
                        $notifMsg = "{$reactorName} reacted with {$type} to your Reel";

                        $db = Db::connect2();
                        $stmt = $db->prepare("
                            INSERT INTO notification 
                            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                            VALUES (?, ?, ?, 'Reel Reaction', 'Reel', ?, ?, 'new', NOW())
                        ");
                        $stmt->execute([
                            $userId,
                            $ownerId,
                            $reactorName,
                            $notifMsg,
                            $reelUrl
                        ]);

                        PushNotificationClass::sendPushNotification(
                            userId: $ownerId,
                            message: $notifMsg,
                            url: $reelUrl,
                            title: '🎬 New Reaction on your Reel',
                            tag: 'reel-reaction-' . $reelId
                        );
                    }
                } catch (\Throwable $notifEx) {
                    error_log('[ReelsController::toggleReaction notif warning] ' . $notifEx->getMessage());
                }
            }
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Get Reel Comments
     */
    public function getComments(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $reelId = isset($_GET['reel_id']) ? (int)$_GET['reel_id'] : 0;
            if (!$reelId) {
                throw new Exception('Invalid reel ID', 400);
            }

            $userId = (string)($_SESSION['id'] ?? '');
            $targetReel = Reel::getReelById($reelId, $userId);
            if (!$targetReel) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Reel not found']);
                return;
            }
            if (!self::canAccessReel($targetReel, $userId)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family reel']);
                return;
            }

            $comments = Reel::getComments($reelId, $userId);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Add Reel Comment (supports 1-level threaded reply & curated GIFs)
     */
    public function addComment(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $famCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($famCode) && !empty($userId)) {
                $pStmt = Db::connect2()->prepare("SELECT famCode FROM personal WHERE id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $famCode = trim((string)$pStmt->fetchColumn());
                if (!empty($famCode)) {
                    $_SESSION['famCode'] = $famCode;
                }
            }
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $reelId = (int)($payload['reel_id'] ?? 0);
            $commentText = trim((string)($payload['comment'] ?? ''));
            $parentCommentNo = isset($payload['parent_comment_no']) && (int)$payload['parent_comment_no'] > 0 ? (int)$payload['parent_comment_no'] : null;
            $gifUrl = isset($payload['gif_url']) ? trim((string)$payload['gif_url']) : null;

            if (!$reelId || empty($userId) || ($commentText === '' && empty($gifUrl))) {
                throw new Exception('Comment content or GIF, user session and reel ID are required', 400);
            }

            $targetReelCheck = Reel::getReelById($reelId, $userId);
            if (!$targetReelCheck) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Reel not found']);
                return;
            }
            if (!self::canAccessReel($targetReelCheck, $userId)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family reel']);
                return;
            }

            // Cap comment spam per user
            try {
                \Src\Limiter::limit($userId . ':reel_comment', 'post');
            } catch (\Throwable $limitEx) {
                // If limiter table not initialized, allow comment through gracefully
            }

            $newComment = Reel::addComment($reelId, $userId, $commentText, $parentCommentNo, $gifUrl);

            // Notify reel owner about the new comment
            try {
                $targetReel = Reel::getReelById($reelId, $userId);
                $ownerId = (string)($targetReel['user_id'] ?? '');

                $commenterName = trim(($newComment['firstName'] ?? '') . ' ' . ($newComment['lastName'] ?? ''));
                if ($commenterName === '') {
                    $commenterName = trim(($_SESSION['fName'] ?? '') . ' ' . ($_SESSION['lName'] ?? ''));
                }
                if ($commenterName === '') {
                    $commenterName = 'A family member';
                }

                $snippet = mb_substr($commentText, 0, 70);
                $reelUrl = '/reels?id=' . $reelId;
                $db = Db::connect2();

                if ($ownerId !== '' && $ownerId !== $userId) {
                    $notifMsg = "{$commenterName} commented: \"{$snippet}\"";

                    $stmt = $db->prepare("
                        INSERT INTO notification 
                        (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                        VALUES (?, ?, ?, 'New Reel Comment', 'Reel', ?, ?, 'new', NOW())
                    ");
                    $stmt->execute([
                        $userId,
                        $ownerId,
                        $commenterName,
                        $notifMsg,
                        $reelUrl
                    ]);

                    PushNotificationClass::sendPushNotification(
                        userId: $ownerId,
                        message: $notifMsg,
                        url: $reelUrl,
                        title: '🎬 New Reel Comment',
                        tag: 'reel-comment-' . $reelId
                    );
                }

                // If this was a reply to another comment, notify the parent comment's author
                if ($parentCommentNo !== null) {
                    $pStmt = $db->prepare("SELECT user_id FROM family_reel_comments WHERE id = ? LIMIT 1");
                    $pStmt->execute([$parentCommentNo]);
                    $parentAuthorId = (string)$pStmt->fetchColumn();
                    if ($parentAuthorId !== '' && $parentAuthorId !== $userId && $parentAuthorId !== $ownerId) {
                        $replyMsg = "{$commenterName} replied to your comment on a Reel: \"{$snippet}\"";
                        $rStmt = $db->prepare("
                            INSERT INTO notification 
                            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                            VALUES (?, ?, ?, 'Reel Comment Reply', 'Reel', ?, ?, 'new', NOW())
                        ");
                        $rStmt->execute([
                            $userId,
                            $parentAuthorId,
                            $commenterName,
                            $replyMsg,
                            $reelUrl
                        ]);

                        PushNotificationClass::sendPushNotification(
                            userId: $parentAuthorId,
                            message: $replyMsg,
                            url: $reelUrl,
                            title: '💬 Reply to your comment',
                            tag: 'reel-comment-reply-' . $reelId
                        );
                    }
                }

                // Notify mentioned members in reel comment
                try {
                    $members = AllMembersData::AllMembersEmailByFamCode($famCode, $userId);
                    $mentionedIds = \App\services\CommentThreadService::extractMentionedIds($commentText, $members, $userId);
                    if (!empty($mentionedIds)) {
                        $mTitle = '💬 Mentioned in a Reel Comment';
                        $mUrl = '/reels?id=' . $reelId;
                        $mMsg = "{$commenterName} mentioned you in a comment on a Reel";
                        $db = Db::connect2();
                        $mStmt = $db->prepare("
                            INSERT INTO notification 
                            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                            VALUES (?, ?, ?, 'Reel Mention', 'Reel', ?, ?, 'new', NOW())
                        ");
                        foreach ($mentionedIds as $mId) {
                            if ($mId === $ownerId) {
                                continue; // Already notified as reel owner
                            }
                            try {
                                $mStmt->execute([
                                    $userId,
                                    $mId,
                                    $commenterName,
                                    $mMsg,
                                    $mUrl
                                ]);
                                PushNotificationClass::sendPushNotification(
                                    userId: $mId,
                                    message: $mMsg,
                                    url: $mUrl,
                                    title: $mTitle,
                                    tag: 'reel-mention-' . $reelId
                                );
                            } catch (\Throwable) {}
                        }
                    }
                } catch (\Throwable $menEx) {
                    error_log('[ReelsController::addComment mentions error] ' . $menEx->getMessage());
                }
            } catch (\Throwable $notifEx) {
                error_log('[ReelsController::addComment notif warning] ' . $notifEx->getMessage());
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'message' => 'Comment added',
                'data' => $newComment
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Update Reel Comment (Author-only, IDOR guarded)
     */
    public function updateComment(int|string $commentId = 0): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $commId = (int)$commentId;
            if (!$commId && isset($payload['comment_id'])) {
                $commId = (int)$payload['comment_id'];
            }
            if (!$commId && isset($_GET['comment_id'])) {
                $commId = (int)$_GET['comment_id'];
            }

            $commentText = trim((string)($payload['comment'] ?? ''));

            if (!$commId || empty($userId) || empty($commentText)) {
                throw new Exception('Invalid comment ID or empty comment text', 400);
            }

            $result = Reel::updateComment($commId, $userId, $commentText);
            if (!$result['success']) {
                $errCode = in_array((int)$result['code'], [400, 401, 403, 404, 500], true) ? (int)$result['code'] : 400;
                throw new Exception($result['message'], $errCode);
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Throwable $e) {
            $code = in_array((int)$e->getCode(), [400, 401, 403, 404, 500], true) ? (int)$e->getCode() : 400;
            http_response_code($code);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Toggle Reaction on Reel Comment (Like, Love, Haha, Wow, Sad, Angry)
     */
    public function toggleCommentReaction(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $commentId = (int)($payload['comment_id'] ?? 0);
            $type = (string)($payload['reaction_type'] ?? 'like');

            if (!$commentId || empty($userId)) {
                throw new Exception('Invalid comment ID or user session', 400);
            }

            // Cap reaction spam per user
            try {
                \Src\Limiter::limit($userId . ':reel_comment_reaction', 'comment_reactions');
            } catch (\Throwable $limitEx) {
                // If limiter table not initialized, allow reaction gracefully
            }

            $result = Reel::toggleCommentReaction($commentId, $userId, $type);

            // Notify comment author on added reaction
            if ($result['action'] === 'added') {
                try {
                    $targetAuthorId = (string)$result['commentAuthorId'];
                    if ($targetAuthorId !== '' && $targetAuthorId !== $userId) {
                        $reactorName = trim(($_SESSION['fName'] ?? '') . ' ' . ($_SESSION['lName'] ?? ''));
                        if ($reactorName === '') {
                            $reactorName = 'A family member';
                        }
                        $reelId = (int)$result['reelId'];
                        $reelUrl = '/reels?id=' . $reelId;
                        $notifMsg = "{$reactorName} reacted with {$type} to your comment";

                        $db = Db::connect2();
                        $stmt = $db->prepare("
                            INSERT INTO notification 
                            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                            VALUES (?, ?, ?, 'Reel Comment Reaction', 'Reel', ?, ?, 'new', NOW())
                        ");
                        $stmt->execute([
                            $userId,
                            $targetAuthorId,
                            $reactorName,
                            $notifMsg,
                            $reelUrl
                        ]);

                        PushNotificationClass::sendPushNotification(
                            userId: $targetAuthorId,
                            message: $notifMsg,
                            url: $reelUrl,
                            title: '💬 New Reaction on your comment',
                            tag: 'reel-comment-reaction-' . $commentId
                        );
                    }
                } catch (\Throwable $notifEx) {
                    error_log('[ReelsController::toggleCommentReaction notif warning] ' . $notifEx->getMessage());
                }
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Delete Reel Comment (Author of comment OR account holder of reel)
     */
    public function deleteComment(int|string $commentId = 0): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;
            $commId = (int)$commentId;
            if (!$commId && isset($payload['comment_id'])) {
                $commId = (int)$payload['comment_id'];
            }
            if (!$commId && isset($_GET['comment_id'])) {
                $commId = (int)$_GET['comment_id'];
            }

            if (!$commId || empty($userId)) {
                throw new Exception('Invalid comment ID or user session', 400);
            }

            $result = Reel::deleteComment($commId, $userId);
            if (!$result['success']) {
                $errCode = in_array((int)$result['code'], [400, 401, 403, 404, 500], true) ? (int)$result['code'] : 400;
                throw new Exception($result['message'], $errCode);
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'message' => $result['message']
            ]);
        } catch (\Throwable $e) {
            $code = in_array((int)$e->getCode(), [400, 401, 403, 404, 500], true) ? (int)$e->getCode() : 400;
            http_response_code($code);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Delete Reel (IDOR protected)
     */
    public function deleteReel(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;
            $reelId = (int)($payload['reel_id'] ?? 0);

            if (!$reelId || empty($userId)) {
                throw new Exception('Invalid reel ID or user session', 400);
            }

            $deleted = Reel::deleteReel($reelId, $userId);
            if (!$deleted) {
                throw new Exception('You are not authorized to delete this reel or it does not exist.', 403);
            }

            // Clean up family hashtag links for deleted reel
            try {
                \App\services\HashtagService::deleteLinksForTarget('reel', (string)$reelId);
            } catch (\Throwable $th) {
                error_log('[ReelsController::deleteReel hashtags] ' . $th->getMessage());
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Reel deleted successfully.'
            ]);
        } catch (\Throwable $e) {
            http_response_code($e->getCode() === 403 ? 403 : 400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Record Reel View (increments views_count)
     */
    public function recordView(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;
            $reelId = (int)($payload['reel_id'] ?? 0);

            if ($reelId <= 0 || empty($userId)) {
                throw new Exception('Invalid reel ID or user session', 400);
            }

            Reel::incrementViews($reelId);
            $updated = Reel::getReelById($reelId, $userId);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode([
                'status' => 'success',
                'views_count' => (int)($updated['views_count'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Update/Auto-Heal Reel Thumbnail & Aspect Ratio (IDOR Protected)
     */
    public function updateThumbnail(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $reelId = (int)($payload['reel_id'] ?? 0);
            $thumbnailData = isset($payload['thumbnail_data']) ? (string)$payload['thumbnail_data'] : '';
            $aspectRatio = isset($payload['aspect_ratio']) ? trim((string)$payload['aspect_ratio']) : '9:16';

            if ($reelId <= 0 || empty($userId)) {
                throw new Exception('Invalid reel ID or unauthorized session', 401);
            }

            $existingReel = Reel::getReelById($reelId, $userId);
            if (!is_array($existingReel) || (string)($existingReel['user_id'] ?? '') !== $userId) {
                throw new Exception('Forbidden: You do not own this reel or it does not exist.', 403);
            }

            if (empty($thumbnailData) || !str_contains($thumbnailData, 'base64,')) {
                throw new Exception('Valid Base64 image data is required', 400);
            }

            $parts = explode('base64,', $thumbnailData);
            $rawBase64 = $parts[1] ?? '';
            $decodedImg = base64_decode($rawBase64, true);
            $imgInfo = is_string($decodedImg) ? @getimagesizefromstring($decodedImg) : false;

            $okThumb = is_array($imgInfo)
                && in_array($imgInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
                && strlen((string)$decodedImg) <= 5 * 1024 * 1024;

            if (!$okThumb) {
                throw new Exception('Invalid raster image payload', 400);
            }

            $thumbDir = __DIR__ . '/../../../resources/images/reels/thumbs/';
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }
            $thumbName = 'thumb_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.jpg';
            file_put_contents($thumbDir . $thumbName, $decodedImg);
            $thumbnailPath = '/resources/images/reels/thumbs/' . $thumbName;

            $updated = Reel::updateThumbnailAndAspect($reelId, $userId, $thumbnailPath, $aspectRatio);
            if (!$updated) {
                throw new Exception('Forbidden: You do not own this reel or it does not exist.', 403);
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode([
                'status' => 'success',
                'thumbnail_url' => $thumbnailPath,
                'aspect_ratio' => $aspectRatio,
            ]);
        } catch (\Throwable $e) {
            $code = (int)$e->getCode();
            http_response_code(in_array($code, [400, 401, 403, 404], true) ? $code : 400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Fetch peer synchronized angles for a multi-angle reel cluster.
     * Enforces tenant-isolation strictly against the authenticated user's famCode.
     */
    public function getChronoSyncAngles(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $famCode = (string)($_SESSION['famCode'] ?? '');
            $clusterId = isset($_GET['cluster_id']) ? trim((string)$_GET['cluster_id']) : '';
            $excludeId = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;

            if ($famCode === '' || $clusterId === '') {
                echo json_encode([
                    'status' => 'success',
                    'data' => [],
                    'count' => 0
                ]);
                return;
            }

            $angles = Reel::getChronoSyncAngles($clusterId, $famCode, $excludeId);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode([
                'status' => 'success',
                'data' => $angles,
                'count' => count($angles)
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Fetch active family reels from the last 7 days for multi-angle linking in the modal.
     */
    public function getRecentClusters(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $famCode = (string)($_SESSION['famCode'] ?? '');
            if ($famCode === '') {
                echo json_encode(['status' => 'success', 'data' => []]);
                return;
            }

            $reels = Reel::getRecentActiveReelsForCluster($famCode, 15);

            echo json_encode([
                'status' => 'success',
                'data' => $reels
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Get Curated Audio Library tracks for Reels creation
     */
    public function getAudioLibrary(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $tracks = Reel::getAudioLibrary();
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'data' => $tracks
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Get Reels by Sound / Soundtrack Title (Viral "Use This Sound")
     */
    public function getReelsBySound(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $famCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($famCode)) {
                throw new Exception('Unauthorized family tenant', 401);
            }

            $sound = isset($_GET['sound']) ? trim((string)$_GET['sound']) : '';
            if ($sound === '') {
                throw new Exception('Soundtrack title parameter is required.', 400);
            }

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 24;
            $reels = Reel::getReelsBySound($sound, $famCode, $limit);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'sound' => $sound,
                'count' => count($reels),
                'data' => $reels
            ]);
        } catch (\Throwable $e) {
            http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
