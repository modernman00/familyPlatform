<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\FamilyAnnouncement;
use App\model\AllMembersData;
use App\classes\PushNotificationClass;
use Src\CheckToken;
use Src\Db;
use Src\SelectFn;
use Src\functionality\SignIn;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;

final class FamilyAnnouncementController extends BaseController
{
    private string $userId;
    private string $famCode;

    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }

        $this->userId = (string)($_SESSION['id'] ?? '');
        if (empty($this->userId)) {
            throw new ForbiddenException("Authentication required to manage announcements.");
        }

        $famCode = (string)($_SESSION['famCode'] ?? '');
        if (empty($famCode)) {
            $userRow = SelectFn::selectOneRow('personal', 'id', $this->userId);
            $famCode = (string)($userRow['famCode'] ?? '');
            $_SESSION['famCode'] = $famCode;
        }
        $this->famCode = $famCode;
        if (empty($this->famCode)) {
            throw new ForbiddenException("Active family group membership is required to access family announcements.");
        }
    }

    /**
     * Broadcast a new family announcement
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
            self::validateCsrf(is_array($input) ? $input : null);

            $title = trim((string)($input['title'] ?? ''));
            $message = trim((string)($input['message'] ?? ''));
            $priority = (string)($input['priority'] ?? 'important');
            $type = (string)($input['type'] ?? 'general');
            $rawExpiresAt = !empty($input['expires_at']) ? trim((string)$input['expires_at']) : null;

            if (empty($title) || empty($message)) {
                throw new ValidationException("Title and announcement message are required.");
            }

            if (mb_strlen($title) < 3 || mb_strlen($title) > 255) {
                throw new ValidationException("Announcement title must be between 3 and 255 characters.");
            }

            if (mb_strlen($message) < 5 || mb_strlen($message) > 5000) {
                throw new ValidationException("Announcement message must be between 5 and 5,000 characters.");
            }

            if (!in_array($priority, ['normal', 'important', 'urgent'], true)) {
                $priority = 'important';
            }

            if (!in_array($type, ['general', 'reunion', 'milestone', 'alert', 'celebration'], true)) {
                $type = 'general';
            }

            $expiresAt = null;
            if ($rawExpiresAt !== null) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawExpiresAt)) {
                    throw new ValidationException("Expiration date must follow YYYY-MM-DD format.");
                }
                $expiresAt = $rawExpiresAt;
            }

            $announcementData = [
                'family_code' => $this->famCode,
                'author_id'   => $this->userId,
                'title'       => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                'message'     => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
                'priority'    => $priority,
                'type'        => $type,
                'is_pinned'   => 1,
                'expires_at'  => $expiresAt
            ];

            $newId = FamilyAnnouncement::createAnnouncement($announcementData);

            // Dispatch in-app and push notifications to all family members
            try {
                $authorName = trim(($_SESSION['fName'] ?? '') . ' ' . ($_SESSION['lName'] ?? ''));
                if ($authorName === '') {
                    $userRow = SelectFn::selectOneRow('personal', 'id', $this->userId);
                    $authorName = trim(($userRow['firstName'] ?? '') . ' ' . ($userRow['lastName'] ?? ''));
                }
                if ($authorName === '') {
                    $authorName = 'Family Leader';
                }

                $members = AllMembersData::AllMembersEmailByFamCode($this->famCode, $this->userId);
                $actionUrl = '/profilePage#familyAnnouncementsCard';
                $snippet = mb_substr($message, 0, 80);
                $notifTitle = "📢 Announcement: " . htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
                $notifMsg = "{$authorName}: \"{$snippet}\"";

                $db = Db::connect2();
                $stmt = $db->prepare("
                    INSERT INTO notification 
                    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                    VALUES (?, ?, ?, ?, 'Announcement', ?, ?, 'new', NOW())
                ");

                foreach ($members as $member) {
                    $targetId = (string)($member['id'] ?? '');
                    if ($targetId === '' || $targetId === $this->userId) {
                        continue;
                    }

                    try {
                        $stmt->execute([
                            $this->userId,
                            $targetId,
                            $authorName,
                            $notifTitle,
                            $notifMsg,
                            $actionUrl
                        ]);
                    } catch (\Throwable) {}

                    try {
                        PushNotificationClass::sendPushNotification(
                            userId: $targetId,
                            message: $notifMsg,
                            url: $actionUrl,
                            title: $notifTitle,
                            tag: 'announcement-' . $newId
                        );
                    } catch (\Throwable) {}
                }
            } catch (\Throwable $notifEx) {
                error_log('[FamilyAnnouncementController::store notif warning] ' . $notifEx->getMessage());
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response with json_encode and application/json header
            echo json_encode([
                'status'  => 'success',
                'message' => 'Announcement broadcasted to your family!',
                'id'      => $newId
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Remove / Dismiss a family announcement
     */
    public function delete(): void
    {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
            self::validateCsrf(is_array($input) ? $input : null);

            $id = isset($input['id']) ? (int)$input['id'] : 0;

            if ($id <= 0) {
                throw new ValidationException("Invalid announcement ID.");
            }

            $isAdmin = ($_SESSION['type'] ?? '') === 'admin';
            $success = FamilyAnnouncement::deleteAnnouncement($id, $this->famCode, $this->userId, $isAdmin);

            if (!$success) {
                throw new ForbiddenException("Unable to delete announcement. Authorization failed.");
            }

            echo json_encode([
                'status'  => 'success',
                'message' => 'Announcement removed.'
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Get active family announcements JSON
     */
    public function getActive(): void
    {
        header('Content-Type: application/json');
        try {
            $announcements = FamilyAnnouncement::getActiveAnnouncements($this->famCode, 5);
            echo json_encode([
                'status' => 'success',
                'data'   => $announcements
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}
