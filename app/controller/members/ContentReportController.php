<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use PDO;
use Src\CheckToken;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;
use Src\Limiter;
use Throwable;

/**
 * ContentReportController — Member-facing Community Flagging & Abuse Reporting
 *
 * Allows family members to flag and report posts or comments violating community safety,
 * hate speech policies, or respectful family discourse standards.
 */
final class ContentReportController extends BaseController
{
    private const ALLOWED_REASONS = [
        'hate_speech' => 'Hate speech or abusive slurs',
        'harassment' => 'Harassment, bullying, or threats',
        'inappropriate' => 'Inappropriate or offensive content',
        'spam' => 'Spam or misleading information',
        'other' => 'Other violation of family values',
    ];

    /**
     * Submit a report against a post or comment.
     * POST /member/content/report
     */
    public function submitReport(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            CheckToken::tokenCheck();

            $reporterId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? trim((string)$_SESSION['id']) : '';
            if ($reporterId === '') {
                throw new ForbiddenException('Authentication required to submit reports.');
            }

            $userFamCode = isset($_SESSION['famCode']) && is_scalar($_SESSION['famCode']) ? trim((string)$_SESSION['famCode']) : '';

            // Cap report spam (max 6 reports per 5 minutes per user)
            Limiter::limit($reporterId . ':report_content', 'post');

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput !== false && $rawInput !== '' ? $rawInput : '{}', true) ?: $_POST;

            $targetType = strtolower(trim((string)($input['target_type'] ?? 'post')));
            if (!in_array($targetType, ['post', 'comment'], true)) {
                throw new ValidationException('Invalid content target type. Must be post or comment.');
            }

            $targetId = (int)($input['target_id'] ?? 0);
            if ($targetId <= 0) {
                throw new ValidationException('A valid content identifier is required.');
            }

            $reason = strtolower(trim((string)($input['reason'] ?? 'hate_speech')));
            if (!isset(self::ALLOWED_REASONS[$reason])) {
                $reason = 'other';
            }

            $details = trim((string)($input['details'] ?? ''));
            $detailsClean = mb_substr(strip_tags($details), 0, 500);

            $pdo = Db::connect2();

            // Locate target content & author
            if ($targetType === 'post') {
                $stmt = $pdo->prepare('SELECT post_no, id AS author_id, postFamCode, postMessage FROM post WHERE post_no = ? AND (date_deleted IS NULL OR date_deleted = "") LIMIT 1');
                $stmt->execute([$targetId]);
                $contentRow = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$contentRow) {
                    throw new NotFoundException('The requested post could not be found or has already been removed.');
                }

                $contentFamCode = (string)($contentRow['postFamCode'] ?? '');
                $targetAuthorId = (string)($contentRow['author_id'] ?? '');
                $targetContent = (string)($contentRow['postMessage'] ?? '');
            } else {
                $stmt = $pdo->prepare('SELECT c.comment_no, c.id AS author_id, c.comment, p.postFamCode FROM comment c INNER JOIN post p ON c.post_no = p.post_no WHERE c.comment_no = ? AND (c.date_deleted IS NULL OR c.date_deleted = "") LIMIT 1');
                $stmt->execute([$targetId]);
                $contentRow = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$contentRow) {
                    throw new NotFoundException('The requested comment could not be found or has already been removed.');
                }

                $contentFamCode = (string)($contentRow['postFamCode'] ?? '');
                $targetAuthorId = (string)($contentRow['author_id'] ?? '');
                $targetContent = (string)($contentRow['comment'] ?? '');
            }

            // Prevent self-reporting
            if ($targetAuthorId === $reporterId) {
                throw new ValidationException('You cannot report your own content. You can edit or delete it directly.');
            }

            // Verify reporter belongs to the family
            $famCodeToUse = $contentFamCode !== '' ? $contentFamCode : $userFamCode;
            if ($famCodeToUse !== '' && !BaseController::sessionSharesFamily($famCodeToUse)) {
                throw new ForbiddenException('Access denied: content belongs to another family network.');
            }

            // Prevent duplicate pending reports from the same user for the same item
            $dupCheck = $pdo->prepare('SELECT id FROM content_reports WHERE reporter_id = ? AND target_type = ? AND target_id = ? AND status = "pending" LIMIT 1');
            $dupCheck->execute([$reporterId, $targetType, $targetId]);
            if ($dupCheck->fetch()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'status' => 'already_reported',
                    'message' => 'You have already reported this item. Our moderation team is actively reviewing it.',
                ]);
                return;
            }

            $reportUuid = 'rep_' . bin2hex(random_bytes(12));

            $insStmt = $pdo->prepare(
                'INSERT INTO content_reports (
                    report_uuid, fam_code, reporter_id, target_type, target_id, target_author_id,
                    target_content, reason, details, status, created_at
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW())'
            );

            $insStmt->execute([
                $reportUuid,
                $famCodeToUse,
                $reporterId,
                $targetType,
                $targetId,
                $targetAuthorId,
                $targetContent,
                $reason,
                $detailsClean !== '' ? $detailsClean : null,
            ]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'status' => 'reported',
                'message' => 'Thank you for helping keep our family community safe. Your report has been submitted to administrators for review.',
            ]);
        } catch (ValidationException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (ForbiddenException $e) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (NotFoundException $e) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Throwable $e) {
            error_log('[ContentReportController::submitReport] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to submit report at this time. Please try again.']);
        }
    }
}
