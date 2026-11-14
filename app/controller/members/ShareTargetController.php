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
            $sharedPayload = [
                'files'     => $savedFiles,
                'caption'   => $caption,
                'timestamp' => time(),
            ];

            if (!empty($savedFiles) || $caption !== '') {
                $_SESSION['pwa_shared_target'] = $sharedPayload;
            }

            // Generate high-entropy single-use ticket for cross-site cookie-free Android navigations
            $ticket = 'skt_' . bin2hex(random_bytes(16));
            if (!empty($savedFiles) || $caption !== '') {
                self::storeTicket($ticket, $sharedPayload);
            }

            // Standard PRG pattern (Post-Redirect-Get) via 303 See Other
            // Direct to /profilePage with the ticket so top-level GET re-attaches SameSite=Lax session cookies
            $destination = '/profilePage?pwa_shared=1&ticket=' . urlencode($ticket);
            header('Location: ' . $destination, true, 303);
            if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
                exit;
            }

        } catch (\Throwable $th) {
            error_log('[ShareTargetController] Error processing PWA share target: ' . $th->getMessage());
            header('Location: /profilePage?pwa_shared_err=1', true, 303);
            if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
                exit;
            }
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

        $ticket = trim((string)($_GET['ticket'] ?? ''));
        $pending = null;

        if ($ticket !== '' && preg_match('/^skt_[a-f0-9]{32}$/', $ticket)) {
            $pending = self::consumeTicket($ticket);
            if ($pending !== null) {
                unset($_SESSION['pwa_shared_target']);
            }
        }

        if ($pending === null && isset($_SESSION['pwa_shared_target'])) {
            $pending = $_SESSION['pwa_shared_target'];
            unset($_SESSION['pwa_shared_target']);
        }

        echo json_encode([
            'status' => 'success',
            'data'   => $pending,
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Stores a shared media ticket payload with an expiration timestamp.
     *
     * @param array{files: array<int, string>, caption: string, timestamp: int} $payload
     */
    private static function storeTicket(string $ticket, array $payload): void
    {
        $dir = self::getTicketDir();
        $filePath = $dir . '/' . $ticket . '.json';
        $payload['expires'] = time() + 180; // 3 minutes validity window
        file_put_contents($filePath, json_encode($payload, JSON_UNESCAPED_SLASHES), LOCK_EX);
        self::purgeExpiredTickets();
    }

    /**
     * Consumes and deletes a single-use share ticket.
     *
     * @return array{files: array<int, string>, caption: string, timestamp: int}|null
     */
    private static function consumeTicket(string $ticket): ?array
    {
        $dir = self::getTicketDir();
        $filePath = $dir . '/' . $ticket . '.json';
        if (!is_file($filePath)) {
            return null;
        }

        $raw = file_get_contents($filePath);
        @unlink($filePath); // Atomic single-use consumption

        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        $expires = (int)($data['expires'] ?? 0);
        if ($expires < time()) {
            return null; // Expired ticket
        }

        return [
            'files'     => (array)($data['files'] ?? []),
            'caption'   => (string)($data['caption'] ?? ''),
            'timestamp' => (int)($data['timestamp'] ?? time()),
        ];
    }

    /**
     * Resolves and ensures the cache directory for share tickets.
     */
    private static function getTicketDir(): string
    {
        $base = realpath(__DIR__ . '/../../../storage/cache');
        if ($base === false) {
            $base = sys_get_temp_dir();
        }
        $dir = rtrim($base, '/') . '/share_tickets';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        return $dir;
    }

    /**
     * Purges expired ticket files older than 5 minutes.
     */
    private static function purgeExpiredTickets(): void
    {
        try {
            $dir = self::getTicketDir();
            $files = glob($dir . '/skt_*.json');
            if ($files === false) {
                return;
            }
            $now = time();
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file)) > 300) {
                    @unlink($file);
                }
            }
        } catch (\Throwable $e) {
            // Non-critical cache cleanup
        }
    }

    /**
     * Alias for test suite compatibility
     */
    public function getSharedPayload(): void
    {
        $this->getPendingShare();
    }
}
