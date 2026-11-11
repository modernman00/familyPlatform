<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\BaseController;
use App\services\NotificationOrchestrator;
use App\services\PerceptualHashService;
use App\classes\Pusher;
use PDO;
use Src\CheckToken;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;
use Src\Utility;
use Throwable;

/**
 * AdminModerationController — Super Admin Content Moderation Command Console
 *
 * Allows Super Administrators to review community reports, inspect flagged posts/comments,
 * delete abusive content from the live platform, and issue official warnings to offenders.
 */
final class AdminModerationController extends BaseController
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        parent::__construct();
        $this->db = $db ?? Db::connect2();
    }

    private function getAuthenticatedAdminId(): int
    {
        $userId = $_SESSION['user_id'] ?? ($_SESSION['account_id'] ?? null);
        if (!$userId && \class_exists('\Src\functionality\SignIn')) {
            try {
                $verifyJWT = \Src\functionality\SignIn::verify();
                if (!empty($verifyJWT['id'])) {
                    $userId = (int)$verifyJWT['id'];
                }
            } catch (Throwable $e) {}
        }

        $isSuperAdmin = ($_SESSION['auth']['type'] ?? '') === 'super_admin' && !empty($_SESSION['auth']['2fa_passed']);
        if (!$userId || !$isSuperAdmin) {
            $adminPrefix = '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');
            redirect($adminPrefix . '/login');
            exit;
        }
        return (int)$userId;
    }

    /**
     * Moderation Dashboard View
     * GET /admin/moderation
     */
    public function index(): void
    {
        $adminId = $this->getAuthenticatedAdminId();
        $adminPrefix = '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');

        $statusFilter = strtolower(trim((string)($_GET['status'] ?? 'pending')));
        if (!in_array($statusFilter, ['pending', 'actioned', 'dismissed', 'all'], true)) {
            $statusFilter = 'pending';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        try {
            // Count badges
            $cntStmt = $this->db->query(
                "SELECT 
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'actioned' THEN 1 END) as actioned_count,
                    COUNT(CASE WHEN status = 'dismissed' THEN 1 END) as dismissed_count,
                    COUNT(*) as total_count
                 FROM content_reports"
            );
            $counts = ($cntStmt instanceof \PDOStatement ? $cntStmt->fetch(PDO::FETCH_ASSOC) : false) ?: [
                'pending_count' => 0,
                'actioned_count' => 0,
                'dismissed_count' => 0,
                'total_count' => 0,
            ];

            $whereSql = $statusFilter === 'all' ? '1=1' : 'cr.status = :status';
            $sql = "
                SELECT 
                    cr.*,
                    p_rep.firstName as reporter_first_name,
                    p_rep.lastName as reporter_last_name,
                    p_off.firstName as author_first_name,
                    p_off.lastName as author_last_name
                FROM content_reports cr
                LEFT JOIN personal p_rep ON cr.reporter_id = p_rep.id
                LEFT JOIN personal p_off ON cr.target_author_id = p_off.id
                WHERE {$whereSql}
                ORDER BY cr.id DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->db->prepare($sql);
            if ($statusFilter !== 'all') {
                $stmt->bindValue(':status', $statusFilter, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $totalItems = (int)($statusFilter === 'all' ? $counts['total_count'] : $counts[$statusFilter . '_count']);
            $totalPages = max(1, (int)ceil($totalItems / $limit));

            parent::viewWithCsp('admin/moderation', [
                'adminPrefix' => $adminPrefix,
                'adminId' => $adminId,
                'reports' => $reports,
                'statusFilter' => $statusFilter,
                'counts' => $counts,
                'currentPage' => $page,
                'totalPages' => $totalPages,
            ]);
        } catch (Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * Super Admin Moderation Action Endpoint
     * POST /admin/moderation/action
     */
    public function takeAction(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $adminId = $this->getAuthenticatedAdminId();

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput !== false && $rawInput !== '' ? $rawInput : '{}', true) ?: $_POST;

            $reportId = (int)($input['report_id'] ?? 0);
            $action = strtolower(trim((string)($input['action'] ?? '')));
            $adminNote = trim((string)($input['admin_note'] ?? ''));

            if ($reportId <= 0) {
                throw new ValidationException('Valid report ID is required.');
            }

            $allowedActions = ['delete_and_warn', 'delete_only', 'warn_only', 'dismiss'];
            if (!in_array($action, $allowedActions, true)) {
                throw new ValidationException('Invalid moderation action specified.');
            }

            // Fetch report
            $stmt = $this->db->prepare('SELECT * FROM content_reports WHERE id = ? LIMIT 1');
            $stmt->execute([$reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$report) {
                throw new NotFoundException('Report record not found.');
            }

            $targetType = (string)$report['target_type'];
            $targetId = (int)$report['target_id'];
            $targetAuthorId = (string)$report['target_author_id'];
            $targetFamCode = (string)$report['fam_code'];
            $targetContent = (string)$report['target_content'];

            // 1. Process Deletion if requested
            if (in_array($action, ['delete_and_warn', 'delete_only'], true)) {
                if ($targetType === 'post') {
                    // Soft-delete post
                    $delStmt = $this->db->prepare('UPDATE post SET date_deleted = NOW() WHERE post_no = ?');
                    $delStmt->execute([$targetId]);

                    // Cascade delete comments
                    $delCommStmt = $this->db->prepare('UPDATE comment SET date_deleted = NOW() WHERE post_no = ? AND (date_deleted IS NULL OR date_deleted = "")');
                    $delCommStmt->execute([$targetId]);

                    // Realtime broadcast to remove post from member feeds
                    try {
                        Pusher::broadcastToFamily($targetFamCode, 'delete-post', ['postNo' => $targetId]);
                    } catch (Throwable $pe) {
                        error_log('[AdminModerationController delete-post pusher] ' . $pe->getMessage());
                    }

                    // Active Learning: Fingerprint any attached images into media_safety_hashes so they are blocked from future re-uploads
                    try {
                        $imgStmt = $this->db->prepare('SELECT post_img0, post_img1, post_img2, post_img3, post_img4, post_img5 FROM post WHERE post_no = ? LIMIT 1');
                        $imgStmt->execute([$targetId]);
                        $imgRow = $imgStmt->fetch(PDO::FETCH_ASSOC);
                        if ($imgRow) {
                            $baseDir = dirname(__DIR__, 3) . '/resources/images/post/';
                            for ($i = 0; $i < 6; $i++) {
                                $imgName = $imgRow['post_img' . $i] ?? '';
                                if (!empty($imgName) && !str_starts_with((string)$imgName, 'http')) {
                                    $fullImgPath = $baseDir . $imgName;
                                    if (is_file($fullImgPath)) {
                                        PerceptualHashService::blockMediaFile($fullImgPath, 'pornography', (string)$adminId, (string)$imgName);
                                    }
                                }
                            }
                        }
                    } catch (Throwable $phe) {
                        error_log('[AdminModerationController blockMediaFile] ' . $phe->getMessage());
                    }
                } else {
                    // Soft-delete comment
                    $delStmt = $this->db->prepare('UPDATE comment SET date_deleted = NOW() WHERE comment_no = ?');
                    $delStmt->execute([$targetId]);

                    // Realtime broadcast
                    try {
                        Pusher::broadcastToFamily($targetFamCode, 'delete-comment', ['commentNo' => $targetId]);
                    } catch (Throwable $pe) {
                        error_log('[AdminModerationController delete-comment pusher] ' . $pe->getMessage());
                    }
                }
            }

            // 2. Process Official Warning to the Offending User
            if (in_array($action, ['delete_and_warn', 'warn_only'], true) && $targetAuthorId !== '') {
                $itemLabel = $targetType === 'post' ? 'post' : 'comment';
                $snippet = mb_substr(strip_tags($targetContent), 0, 80);
                $warningBody = "A {$itemLabel} you published was reviewed and removed by Platform Safety after being flagged by community members: \"{$snippet}...\".\n\nFamilyPlatform is a dedicated sanctuary for love, family connection, and mutual respect. Abusive language, hate speech, and harassment are strictly prohibited. Continued violations will result in account suspension.";

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $targetAuthorId,
                        category: 'moderation_warning',
                        priority: 'urgent',
                        title: '⚠️ Community Safety Notice: Content Warning',
                        body: $warningBody,
                        actionUrl: '/profilePage',
                        tag: 'safety_warning',
                        familyCode: $targetFamCode !== '' ? $targetFamCode : null
                    );
                } catch (Throwable $ne) {
                    error_log('[AdminModerationController send warning] ' . $ne->getMessage());
                }
            }

            // 3. Update Report Record
            $newStatus = $action === 'dismiss' ? 'dismissed' : 'actioned';
            $updStmt = $this->db->prepare(
                'UPDATE content_reports 
                 SET status = ?, action_taken = ?, admin_note = ?, reviewed_by = ?, reviewed_at = NOW() 
                 WHERE id = ?'
            );
            $updStmt->execute([
                $newStatus,
                $action,
                $adminNote !== '' ? $adminNote : null,
                (string)$adminId,
                $reportId,
            ]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'status' => $newStatus,
                'action' => $action,
                'message' => 'Moderation action successfully completed.',
            ]);
        } catch (ValidationException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (NotFoundException $e) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (ForbiddenException $e) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Throwable $e) {
            error_log('[AdminModerationController::takeAction] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to execute moderation action. Please check server logs.']);
        }
    }
}
