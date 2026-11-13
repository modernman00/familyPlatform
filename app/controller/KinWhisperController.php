<?php

declare(strict_types=1);

namespace App\controller;

use App\services\KinWhisperService;
use Src\Exceptions\BadRequestException;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\UnauthorisedException;
use Src\Exceptions\ValidationException;
use Src\Utility;

/**
 * 🎙️ KinWhisperController
 *
 * Controller handling interactive view rendering and secure API endpoints
 * for Direct Kin Messaging & Asynchronous Kin Whispers (Option B).
 */
final class KinWhisperController extends BaseController
{
    /**
     * Render the main Kin Whispers & Artifact DMs view.
     */
    public function index(): void
    {
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            header('Location: /login');
            exit;
        }

        try {
            $threads = KinWhisperService::listUserThreads($currentUserId);
            $selectedThreadId = isset($_GET['thread_id']) ? trim((string)$_GET['thread_id']) : '';

            $selectedThread = null;
            $messages = [];
            if ($selectedThreadId !== '') {
                try {
                    $selectedThread = KinWhisperService::getThread($currentUserId, $selectedThreadId);
                    $messages = KinWhisperService::getMessages($currentUserId, $selectedThreadId);
                } catch (\Throwable $e) {
                    // Thread may not be found or access denied; fall back gracefully
                    $selectedThread = null;
                    $messages = [];
                }
            } elseif (!empty($threads)) {
                $selectedThread = $threads[0];
                $messages = KinWhisperService::getMessages($currentUserId, $selectedThread->id);
            }

            $kin = KinWhisperService::getConnectedKinMembers($currentUserId);

            Utility::view('kin_whispers/index', [
                'page_title' => 'Kin Whispers & Artifact Messages — FamilyPlatform',
                'current_user_id' => $currentUserId,
                'threads' => $threads,
                'selected_thread' => $selectedThread,
                'messages' => $messages,
                'kin' => $kin,
                'presence_privacy' => KinWhisperService::getPresencePrivacy($currentUserId),
                'server_now_iso' => gmdate('Y-m-d\TH:i:s\Z'),
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * API: Get total unread whisper count for current authenticated user.
     */
    public function apiUnreadCount(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            echo json_encode(['success' => true, 'unread_count' => 0]);
            return;
        }

        try {
            $count = KinWhisperService::getUnreadWhisperCount($currentUserId);
            echo json_encode([
                'success' => true,
                'unread_count' => $count,
            ]);
        } catch (\Throwable $th) {
            error_log('[KinWhisperController::apiUnreadCount] ' . $th->getMessage());
            echo json_encode([
                'success' => true,
                'unread_count' => 0,
            ]);
        }
    }

    /**
     * API: List all threads for current authenticated user.
     */
    public function apiListThreads(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        try {
            $threads = KinWhisperService::listUserThreads($currentUserId);
            echo json_encode(['success' => true, 'threads' => $threads]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * Helper to reliably extract an ID from string or array route parameters or $_GET.
     *
     * @param mixed $param
     * @param string $key
     * @return string
     */
    private function resolveParamId(mixed $param, string $key = 'id'): string
    {
        if (is_string($param) && $param !== '') {
            return trim($param);
        }
        if (is_array($param) && isset($param[$key])) {
            return trim((string)$param[$key]);
        }
        return trim((string)($_GET[$key] ?? ''));
    }

    /**
     * API: Get single thread details and message history.
     *
     * @param mixed $params Route parameters or string thread ID
     */
    public function apiGetThread(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        if ($threadId === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Thread ID is required.']);
            return;
        }

        try {
            $thread = KinWhisperService::getThread($currentUserId, $threadId);
            $messages = KinWhisperService::getMessages($currentUserId, $threadId);
            echo json_encode([
                'success' => true,
                'thread' => $thread,
                'messages' => $messages,
                'server_time' => gmdate('Y-m-d\TH:i:s\Z'),
            ]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Create or retrieve a whisper thread.
     */
    public function apiCreateThread(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        if (\App\services\AnalyticsService::isRateLimited($currentUserId, 'kin_whisper_thread_create', 20, 60)) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many threads created. Please wait a moment.']);
            return;
        }

        $input = $this->parseJsonInput();
        $recipientId = trim((string)($input['recipient_id'] ?? ''));
        $contextType = trim((string)($input['context_type'] ?? 'general'));
        $contextId = isset($input['context_id']) && $input['context_id'] !== '' ? trim((string)$input['context_id']) : null;
        $contextTitle = isset($input['context_title']) && $input['context_title'] !== '' ? trim((string)$input['context_title']) : null;

        try {
            $thread = KinWhisperService::createOrGetThread(
                $currentUserId,
                $recipientId,
                $contextType,
                $contextId,
                $contextTitle
            );
            echo json_encode(['success' => true, 'thread' => $thread]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Send a whisper in a thread.
     */
    public function apiSendMessage(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        if (\App\services\AnalyticsService::isRateLimited($currentUserId, 'kin_whisper_send', 45, 60)) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many whispers sent. Please pause for a moment.']);
            return;
        }

        $input = $this->parseJsonInput();
        $threadId = trim((string)($input['thread_id'] ?? ''));
        $body = trim((string)($input['body'] ?? ''));
        $messageType = trim((string)($input['message_type'] ?? 'text'));
        $audioUrl = isset($input['audio_url']) && $input['audio_url'] !== '' ? trim((string)$input['audio_url']) : null;
        $audioDuration = isset($input['audio_duration']) ? (int)$input['audio_duration'] : null;
        $disappearSeconds = isset($input['disappear_seconds']) ? (int)$input['disappear_seconds'] : null;

        $metadata = isset($input['metadata']) && is_array($input['metadata']) ? $input['metadata'] : [];
        if (isset($input['attachments']) && is_array($input['attachments'])) {
            $metadata['attachments'] = $input['attachments'];
        }
        if (isset($input['reply_to']) && is_array($input['reply_to'])) {
            $metadata['reply_to'] = $input['reply_to'];
        }
        $metaArg = !empty($metadata) ? $metadata : null;

        try {
            $message = KinWhisperService::sendMessage(
                $currentUserId,
                $threadId,
                $body,
                $messageType,
                $audioUrl,
                $audioDuration,
                $metaArg,
                $disappearSeconds,
                deferNotifications: true
            );
            $response = json_encode(['success' => true, 'message' => $message, 'server_time' => gmdate('Y-m-d\TH:i:s\Z')]);
            header('Content-Length: ' . strlen((string)$response));
            echo $response;
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            KinWhisperService::dispatchMessageNotifications(
                $currentUserId,
                $threadId,
                $message->id,
                $body,
                $messageType
            );
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Upload an audio voice whisper.
     */
    public function apiUploadAudio(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        if (\App\services\AnalyticsService::isRateLimited($currentUserId, 'kin_whisper_audio_upload', 10, 60)) {
            http_response_code(429);
            echo json_encode(['error' => 'Audio upload rate limit reached. Please wait a moment before recording again.']);
            return;
        }

        if (empty($_FILES['audio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No audio file received.']);
            return;
        }

        try {
            /** @var array{name: string, type: string, tmp_name: string, error: int, size: int} $file */
            $file = $_FILES['audio'];
            $result = KinWhisperService::uploadAudioWhisper($currentUserId, $file);
            echo json_encode(['success' => true, 'audio' => $result]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Upload a rich media attachment (Photo, Video, Document).
     */
    public function apiUploadMedia(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }

        if (\App\services\AnalyticsService::isRateLimited($currentUserId, 'kin_whisper_media_upload', 25, 60)) {
            http_response_code(429);
            echo json_encode(['error' => 'Media upload rate limit reached. Please wait a moment before uploading again.']);
            return;
        }

        try {
            self::validateCsrf();
            session_write_close();

            $file = $_FILES['media'] ?? ($_FILES['file'] ?? null);
            if (!$file || !is_array($file)) {
                http_response_code(400);
                echo json_encode(['error' => 'No media file provided for upload.']);
                return;
            }

            $result = KinWhisperService::uploadMediaWhisper($currentUserId, $file);
            echo json_encode(['success' => true, 'attachment' => $result]);
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Toggle an emoji reaction on a message.
     *
     * @param mixed $params Route parameters or string message ID
     */
    public function apiToggleReaction(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        try {
            $input = $this->parseJsonInput();
            self::validateCsrf($input);
            session_write_close();

            $messageId = $this->resolveParamId($params, 'id');
            $emoji = trim((string)($input['emoji'] ?? ''));

            $message = KinWhisperService::toggleReaction($currentUserId, $messageId, $emoji);
            echo json_encode(['success' => true, 'message' => $message, 'server_time' => gmdate('Y-m-d\TH:i:s\Z')]);
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Toggle star/bookmark on a whisper message.
     *
     * @param mixed $params
     */
    public function apiToggleStar(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }

        try {
            $input = $this->parseJsonInput();
            self::validateCsrf($input);
            session_write_close();

            $messageId = $this->resolveParamId($params, 'id');
            $result = KinWhisperService::toggleStar($currentUserId, $messageId);
            echo json_encode(array_merge(['success' => true], $result));
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Edit a whisper message within 15 minutes of sending.
     *
     * @param mixed $params
     */
    public function apiEditMessage(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }

        try {
            $input = $this->parseJsonInput();
            self::validateCsrf($input);
            session_write_close();

            $messageId = $this->resolveParamId($params, 'id');
            $body = trim((string)($input['body'] ?? ''));
            $message = KinWhisperService::editMessage($currentUserId, $messageId, $body);
            echo json_encode(['success' => true, 'message' => $message]);
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Delete a whisper message (for everyone or for current user).
     *
     * @param mixed $params Route parameters or string message ID
     */
    public function apiDeleteMessage(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }

        try {
            $input = $this->parseJsonInput();
            self::validateCsrf($input);
            session_write_close();

            $messageId = $this->resolveParamId($params, 'id');
            $mode = (string)($_GET['mode'] ?? ($input['mode'] ?? 'everyone'));
            $result = KinWhisperService::deleteMessage($currentUserId, $messageId, $mode);
            echo json_encode(['success' => true] + $result + ['server_time' => gmdate('Y-m-d\TH:i:s\Z')]);
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Mark all unread messages in a thread as seen/read.
     *
     * @param mixed $params Route parameters or string thread ID
     */
    public function apiMarkThreadRead(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        try {
            $count = KinWhisperService::markThreadAsRead($currentUserId, $threadId);
            echo json_encode(['success' => true, 'marked_count' => $count]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Generate WhatsApp bridge URL for thread.
     *
     * @param mixed $params Route parameters or string thread ID
     */
    public function apiWhatsAppBridge(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        if (\App\services\AnalyticsService::isRateLimited($currentUserId, 'kin_whisper_audio_upload', 10, 60)) {
            http_response_code(429);
            echo json_encode(['error' => 'Audio upload rate limit reached. Please wait a moment before recording again.']);
            return;
        }

        $threadId = $this->resolveParamId($params, 'id');
        try {
            $thread = KinWhisperService::getThread($currentUserId, $threadId);
            if ($thread->shareStatus !== 'approved') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Sharing blocked: Recipient has not approved sharing this whisper outside the platform.',
                    'share_status' => $thread->shareStatus,
                    'share_requested_by' => $thread->shareRequestedBy,
                ]);
                return;
            }
            $url = KinWhisperService::generateWhatsAppBridgeUrl(
                $thread->id,
                $thread->senderName,
                $thread->contextTitle
            );
            echo json_encode(['success' => true, 'whatsapp_url' => $url]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Request recipient approval to share whisper externally on WhatsApp.
     *
     * @param array<string, mixed> $params
     */
    public function apiShareRequest(array $params = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        try {
            $thread = KinWhisperService::requestShareApproval($currentUserId, $threadId);
            echo json_encode([
                'success' => true,
                'message' => 'Share approval requested from recipient.',
                'thread' => $thread,
            ]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Respond to a share request (approve, decline, or revoke).
     *
     * @param array<string, mixed> $params
     */
    public function apiShareRespond(array $params = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        $payload = $this->parseJsonInput();
        $action = (string)($payload['action'] ?? '');

        try {
            $thread = KinWhisperService::respondShareApproval($currentUserId, $threadId, $action);
            echo json_encode([
                'success' => true,
                'message' => 'Share response recorded.',
                'thread' => $thread,
            ]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Record a screenshot detection event with immutable audit logging.
     *
     * @param array<string, mixed> $params
     */
    public function apiRecordScreenshot(array $params = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        try {
            $res = KinWhisperService::recordScreenshotEvent($currentUserId, $threadId);
            echo json_encode($res);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Fetch recent audit events for a thread.
     *
     * @param array<string, mixed> $params
     */
    public function apiGetThreadEvents(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        try {
            $events = KinWhisperService::getThreadEvents($currentUserId, $threadId);
            echo json_encode(['success' => true, 'events' => $events]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Retrieve shared media, documents, and audio for a thread.
     *
     * @param mixed $params
     */
    public function apiGetThreadMedia(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        try {
            $media = KinWhisperService::getThreadMedia($currentUserId, $threadId);
            echo json_encode(['success' => true, 'media' => $media]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Toggle pin status of a thread.
     *
     * @param mixed $params
     */
    public function apiTogglePin(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        try {
            $input = $this->parseJsonInput();
            self::validateCsrf($input);
            session_write_close();

            $threadId = $this->resolveParamId($params, 'id');
            $isPinned = KinWhisperService::togglePinThread($currentUserId, $threadId);
            echo json_encode(['success' => true, 'is_pinned' => $isPinned]);
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Toggle mute notifications of a thread.
     *
     * @param mixed $params
     */
    public function apiToggleMute(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }

        try {
            $input = $this->parseJsonInput();
            self::validateCsrf($input);
            session_write_close();

            $threadId = $this->resolveParamId($params, 'id');
            $isMuted = KinWhisperService::toggleMuteThread($currentUserId, $threadId);
            echo json_encode(['success' => true, 'is_muted' => $isMuted]);
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Heartbeat + privacy-filtered presence lookup for kin.
     */
    public function apiPresenceSync(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }

        try {
            $input = $this->parseJsonInput();
            self::validateCsrf($input);
            session_write_close();

            $ids = is_array($input['user_ids'] ?? null) ? $input['user_ids'] : [];
            echo json_encode(['success' => true] + KinWhisperService::syncPresence($currentUserId, $ids));
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Update the current user's "Online & Last seen" privacy.
     */
    public function apiSetPresencePrivacy(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }

        try {
            $input = $this->parseJsonInput();
            self::validateCsrf($input);
            session_write_close();

            $privacy = KinWhisperService::setPresencePrivacy($currentUserId, (string)($input['last_seen_privacy'] ?? ''));
            echo json_encode(['success' => true, 'last_seen_privacy' => $privacy]);
        } catch (\Throwable $th) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Create a multi-kin group whisper.
     */
    public function apiCreateGroup(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        if (\App\services\AnalyticsService::isRateLimited($currentUserId, 'kin_whisper_group_create', 15, 60)) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many groups created. Please wait a moment.']);
            return;
        }

        $input = $this->parseJsonInput();
        $title = trim((string)($input['title'] ?? ''));
        /** @var array<string> $participantIds */
        $participantIds = isset($input['participant_ids']) && is_array($input['participant_ids']) ? $input['participant_ids'] : [];
        $contextType = trim((string)($input['context_type'] ?? 'general'));
        $contextId = isset($input['context_id']) && $input['context_id'] !== '' ? trim((string)$input['context_id']) : null;
        $contextTitle = isset($input['context_title']) && $input['context_title'] !== '' ? trim((string)$input['context_title']) : null;

        try {
            $thread = KinWhisperService::createGroupThread(
                $currentUserId,
                $title,
                $participantIds,
                $contextType,
                $contextId,
                $contextTitle
            );
            echo json_encode(['success' => true, 'thread' => $thread]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Add participants to an existing group whisper.
     *
     * @param mixed $params Route parameters or string thread ID
     */
    public function apiAddGroupParticipants(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        $input = $this->parseJsonInput();
        /** @var array<string> $participantIds */
        $participantIds = isset($input['participant_ids']) && is_array($input['participant_ids']) ? $input['participant_ids'] : [];

        try {
            $thread = KinWhisperService::addParticipantsToGroup($currentUserId, $threadId, $participantIds);
            echo json_encode(['success' => true, 'thread' => $thread]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Remove participant from a group whisper (or leave group).
     *
     * @param mixed $params Route parameters or string thread ID
     * @param mixed $userIdParam Optional second route parameter for user ID
     */
    public function apiRemoveGroupParticipant(mixed $params = null, mixed $userIdParam = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        $targetUserId = is_string($userIdParam) && $userIdParam !== ''
            ? trim($userIdParam)
            : (is_array($params) ? trim((string)($params['userId'] ?? '')) : trim((string)($_GET['userId'] ?? '')));

        try {
            KinWhisperService::removeParticipantFromGroup($currentUserId, $threadId, $targetUserId);
            echo json_encode(['success' => true, 'message' => 'Participant removed successfully.']);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Initiate in-app video or voice call.
     *
     * @param mixed $params Route parameters or string thread ID
     */
    public function apiInitiateCall(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        $input = $this->parseJsonInput();
        $callType = trim((string)($input['call_type'] ?? 'video'));

        try {
            $call = KinWhisperService::initiateCall($currentUserId, $threadId, $callType);
            echo json_encode(['success' => true, 'call' => $call]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Relay WebRTC call signaling message.
     *
     * @param mixed $params Route parameters or string thread ID
     */
    public function apiSendCallSignal(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');
        $input = $this->parseJsonInput();

        try {
            $result = KinWhisperService::sendCallSignal($currentUserId, $threadId, $input);
            echo json_encode($result);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Terminate an active or ringing call.
     *
     * @param mixed $params Route parameters or string thread ID
     */
    public function apiEndCall(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');

        try {
            $result = KinWhisperService::endCall($currentUserId, $threadId);
            echo json_encode(['success' => true, 'call' => $result]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * API: Get active call info for a thread.
     *
     * @param mixed $params Route parameters or string thread ID
     */
    public function apiGetCallInfo(mixed $params = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $currentUserId = (string)($_SESSION['id'] ?? '');
        if ($currentUserId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Please sign in.']);
            return;
        }
        session_write_close();

        $threadId = $this->resolveParamId($params, 'id');

        try {
            $info = KinWhisperService::getCallInfo($currentUserId, $threadId);
            echo json_encode(['success' => true, 'call' => $info]);
        } catch (\Throwable $th) {
            $this->handleJsonException($th);
        }
    }

    /**
     * Parse incoming JSON body or fall back to $_POST.
     *
     * @return array<string, mixed>
     */
    private function parseJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $_POST;
    }

    /**
     * Standardised JSON exception handler.
     */
    private function handleJsonException(\Throwable $th): void
    {
        $code = 500;
        if ($th instanceof ValidationException || $th instanceof BadRequestException) {
            $code = 400;
        } elseif ($th instanceof ForbiddenException || $th instanceof UnauthorisedException) {
            $code = 403;
        } elseif ($th instanceof NotFoundException) {
            $code = 404;
        }

        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $th->getMessage(),
        ]);
    }
}
