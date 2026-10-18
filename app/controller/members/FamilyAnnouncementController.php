<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\FamilyAnnouncement;
use Src\CheckToken;
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
    }

    /**
     * Broadcast a new family announcement
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        try {
            CheckToken::tokenCheck();

            $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;

            $title = trim((string)($input['title'] ?? ''));
            $message = trim((string)($input['message'] ?? ''));
            $priority = (string)($input['priority'] ?? 'important');
            $type = (string)($input['type'] ?? 'general');
            $expiresAt = !empty($input['expires_at']) ? (string)$input['expires_at'] : null;

            if (empty($title) || empty($message)) {
                throw new ValidationException("Title and announcement message are required.");
            }

            if (!in_array($priority, ['normal', 'important', 'urgent'], true)) {
                $priority = 'important';
            }

            if (!in_array($type, ['general', 'reunion', 'milestone', 'alert', 'celebration'], true)) {
                $type = 'general';
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
            CheckToken::tokenCheck();

            $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
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
