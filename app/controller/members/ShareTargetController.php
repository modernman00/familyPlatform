<?php

declare(strict_types=1);

namespace App\controller\members;

use Exception;

final class ShareTargetController
{
    /**
     * Supported MIME types for PWA gallery share target.
     *
     * @var array<string, string>
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
        'image/gif'       => 'gif',
        'image/heic'      => 'heic',
        'image/heif'      => 'heif',
        'video/mp4'       => 'mp4',
        'video/webm'      => 'webm',
        'video/quicktime' => 'mov',
    ];

    /**
     * Max upload size: 25MB.
     */
    private const MAX_FILE_SIZE = 25 * 1024 * 1024;

    /**
     * Receives direct OS Web Share Target POST (W3C Web Share Target API Level 2).
     * Bypasses internal CSRF because it originates from the native mobile OS share sheet.
     * Stashes validated media securely into session and redirects with HTTP 303 to /profilePage.
     */
    public function receive(): void
    {
        try {
            $title = trim((string)($_POST['title'] ?? ''));
            $text = trim((string)($_POST['text'] ?? ''));
            $url = trim((string)($_POST['url'] ?? ''));

            $parts = [];
            if ($text !== '') {
                $parts[] = $text;
            }
            if ($title !== '' && $title !== $text) {
                $parts[] = $title;
            }
            if ($url !== '') {
                $parts[] = $url;
            }
            $caption = implode("\n", $parts);

            $tempDir = realpath(__DIR__ . '/../../../resources/images/post') . '/temp';
            if (!is_dir($tempDir) && !mkdir($tempDir, 0755, true)) {
                $tempDir = sys_get_temp_dir();
            }

            $savedFiles = [];
            $filesPayload = $_FILES['media'] ?? $_FILES['photo'] ?? $_FILES['photos'] ?? $_FILES['file'] ?? null;

            if ($filesPayload && is_array($filesPayload['name'])) {
                $count = count($filesPayload['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($filesPayload['error'][$i] === UPLOAD_ERR_OK && $filesPayload['size'][$i] <= self::MAX_FILE_SIZE) {
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file($filesPayload['tmp_name'][$i]);
                        if (is_string($mime) && array_key_exists($mime, self::ALLOWED_MIME_TYPES)) {
                            $ext = self::ALLOWED_MIME_TYPES[$mime];
                            $safeName = 'pwa_share_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                            $dest = rtrim($tempDir, '/') . '/' . $safeName;
                            if (move_uploaded_file($filesPayload['tmp_name'][$i], $dest) || copy($filesPayload['tmp_name'][$i], $dest)) {
                                $savedFiles[] = $safeName;
                            }
                        }
                    }
                }
            } elseif ($filesPayload && !empty($filesPayload['tmp_name']) && $filesPayload['error'] === UPLOAD_ERR_OK) {
                if ($filesPayload['size'] <= self::MAX_FILE_SIZE) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($filesPayload['tmp_name']);
                    if (is_string($mime) && array_key_exists($mime, self::ALLOWED_MIME_TYPES)) {
                        $ext = self::ALLOWED_MIME_TYPES[$mime];
                        $safeName = 'pwa_share_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $dest = rtrim($tempDir, '/') . '/' . $safeName;
                        if (move_uploaded_file($filesPayload['tmp_name'], $dest) || copy($filesPayload['tmp_name'], $dest)) {
                            $savedFiles[] = $safeName;
                        }
                    }
                }
            }

            // Stash into session for client retrieval
            if (!empty($savedFiles) || $caption !== '') {
                $_SESSION['pwa_shared_target'] = [
                    'files'     => $savedFiles,
                    'caption'   => $caption,
                    'timestamp' => time(),
                ];
            }

            // Standard PRG pattern (Post-Redirect-Get) via 303 See Other
            $destination = !empty($_SESSION['id']) ? '/profilePage?pwa_shared=1' : '/login?pwa_shared=1';
            header('Location: ' . $destination, true, 303);
            exit;

        } catch (\Throwable $th) {
            error_log('[ShareTargetController] Error processing PWA share target: ' . $th->getMessage());
            header('Location: /profilePage?pwa_shared_err=1', true, 303);
            exit;
        }
    }

    /**
     * API endpoint to retrieve and consume any pending shared media for the authenticated user.
     */
    public function getPendingShare(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $pending = $_SESSION['pwa_shared_target'] ?? null;
        unset($_SESSION['pwa_shared_target']);

        echo json_encode([
            'status' => 'success',
            'data'   => $pending,
        ], JSON_UNESCAPED_SLASHES);
    }
}
