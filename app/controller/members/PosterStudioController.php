<?php

declare(strict_types=1);

namespace App\controller\members;

use App\services\ContentSafetyService;
use PDO;
use Src\CheckToken;
use Src\Db;
use Src\Media\UniversalPosterStudioController;

/**
 * PosterStudioController — FamilyPlatform Flyer & Poster Studio
 *
 * App-owned studio UI (navbar, Creative Studio tabs, family presets, family events,
 * feed posting) driving the shared AtgUniversalPosterEngine canvas renderer.
 * Built-in studio art is still served by the shared-lib controller (?asset=...).
 */
final class PosterStudioController
{
    /**
     * GET /poster-studio  (also /flyer-studio)
     */
    public function index(): void
    {
        if (!empty($_GET['asset']) && is_string($_GET['asset'])) {
            (new UniversalPosterStudioController())->serveStudioAsset($_GET['asset']);
            return;
        }

        $userId = $_SESSION['id'] ?? null;
        if (!$userId || !is_string($userId)) {
            header('Location: /login');
            exit;
        }
        $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';

        $pdo = Db::connect2();
        $stmt = $pdo->prepare('SELECT firstName, lastName FROM account WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $events = [];
        if ($famCode !== '') {
            try {
                $ev = $pdo->prepare(
                    'SELECT no, eventName, eventDate, eventType, eventTime, eventLocation, eventDescription, party_rsvp_url
                     FROM events
                     WHERE eventCode = ? AND deleted_at IS NULL AND eventDate >= CURDATE()
                     ORDER BY eventDate ASC LIMIT 12'
                );
                $ev->execute([$famCode]);
                $events = $ev->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log('[PosterStudio] events lookup failed: ' . $e->getMessage());
            }
        }

        $engineJs = '';
        foreach ([
            dirname(__DIR__, 3) . '/vendor/modernman00/shared-lib/helpers/universal-poster-engine.js',
            dirname(__DIR__, 3) . '/public/js/universal-poster-engine.js',
        ] as $path) {
            if (is_file($path)) {
                $engineJs = (string)file_get_contents($path);
                break;
            }
        }

        $appUrl = rtrim((string)($_ENV['APP_URL'] ?? (getenv('APP_URL') ?: 'https://familyplatform.com')), '/ ');

        view('member/posterStudio', [
            'lastName' => trim((string)($user['lastName'] ?? '')),
            'firstName' => trim((string)($user['firstName'] ?? '')),
            'famCode' => $famCode,
            'events' => $events,
            'engineJs' => $engineJs,
            'joinUrl' => $appUrl . '/register' . ($famCode !== '' ? '?famCode=' . rawurlencode(ltrim($famCode, '#')) . '&ref=poster' : '?ref=poster'),
        ]);
    }

    /**
     * POST /api/poster-studio/post-to-feed
     * Body: token, caption, poster (image file rendered client-side)
     */
    public function postToFeed(): void
    {
        header('Content-Type: application/json');
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'] ?? null;
            $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';
            if (!$userId || !is_string($userId) || $famCode === '') {
                $this->fail(401, 'Please sign in again to post to your family feed.');
                return;
            }

            $file = $_FILES['poster'] ?? null;
            if (!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
                $this->fail(422, 'The poster image did not upload. Please try again.');
                return;
            }
            if ((int)($file['size'] ?? 0) > 15 * 1024 * 1024) {
                $this->fail(422, 'That poster is too large to post.');
                return;
            }

            $info = @getimagesize((string)$file['tmp_name']);
            $mime = is_array($info) ? (string)$info['mime'] : '';
            $src = match ($mime) {
                'image/png' => @imagecreatefrompng((string)$file['tmp_name']),
                'image/jpeg' => @imagecreatefromjpeg((string)$file['tmp_name']),
                default => false,
            };
            if (!$src || imagesx($src) > 3000 || imagesy($src) > 3000) {
                $this->fail(422, 'That poster image could not be read.');
                return;
            }

            $caption = trim(strip_tags((string)($_POST['caption'] ?? '')));
            $caption = mb_substr(preg_replace("/\r\n?/", "\n", $caption) ?? '', 0, 500);
            if ($caption !== '' && class_exists(ContentSafetyService::class)) {
                ContentSafetyService::assertCleanPayload(['poster caption' => $caption], 'poster_feed');
            }

            // Re-encode (strips metadata, normalises format) into the feed's image directory
            $w = imagesx($src);
            $h = imagesy($src);
            $dst = imagecreatetruecolor($w, $h);
            imagefill($dst, 0, 0, (int)imagecolorallocate($dst, 255, 255, 255));
            imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
            imagedestroy($src);

            $dir = dirname(__DIR__, 3) . '/resources/images/post';
            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                $this->fail(500, 'Image storage is unavailable right now.');
                return;
            }
            $name = 'poster_' . bin2hex(random_bytes(10)) . '.jpg';
            imageinterlace($dst, true);
            $ok = imagejpeg($dst, $dir . '/' . $name, 90);
            imagedestroy($dst);
            if (!$ok) {
                $this->fail(500, 'We could not save the poster.');
                return;
            }

            $pdo = Db::connect2();
            $u = $pdo->prepare('SELECT firstName, lastName, (SELECT img FROM profilePics WHERE id = account.id LIMIT 1) AS profile_img FROM account WHERE id = ? LIMIT 1');
            $u->execute([$userId]);
            $author = $u->fetch(PDO::FETCH_ASSOC) ?: [];
            $fullName = trim(($author['firstName'] ?? '') . ' ' . ($author['lastName'] ?? '')) ?: 'Family member';

            $message = $caption !== '' ? $caption : '📣 New family poster';
            $postTime = function_exists('milliSeconds') ? (string)milliSeconds() : date('Y-m-d H:i:s');
            $ins = $pdo->prepare(
                'INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, post_img0, date_created)
                 VALUES (?, ?, ?, ?, ?, ?, "published", ?, NOW())'
            );
            $ins->execute([$userId, $fullName, (string)($author['profile_img'] ?? 'default.png'), $message, $famCode, $postTime, $name]);

            echo json_encode(['status' => 'success', 'post_no' => (int)$pdo->lastInsertId(), 'message' => 'Poster posted to your family feed.']);
        } catch (\Src\Exceptions\UnauthorisedException $e) {
            $this->fail(403, 'Your session has expired. Please refresh the page and try again.');
        } catch (\Src\Exceptions\ValidationException | \InvalidArgumentException $e) {
            $this->fail(422, $e->getMessage());
        } catch (\Throwable $e) {
            error_log('[PosterStudio::postToFeed] ' . $e->getMessage());
            $this->fail(500, 'We could not post the poster just now. Please try again.');
        }
    }

    private function fail(int $code, string $message): void
    {
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
    }
}
