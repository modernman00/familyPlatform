<?php

declare(strict_types=1);

namespace App\controller\members;

use App\services\DataExportService;
use Src\CheckToken;
use Src\Db;
use Src\Utility;
use Src\functionality\SendEmailFunctionality;

/**
 * Member-facing data-rights endpoints (GDPR Art. 15 / 20).
 * Erasure (Art. 17) is deliberately not here yet — it needs a retention-policy
 * decision (what survives for other family members / legal hold). Tracked in
 * implementation_plan.md as GDPR-1.
 */
final class DataPrivacyController
{
    /**
     * Streams the caller's own personal data as a JSON download.
     */
    public function exportData(): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = isset($_SESSION['id']) ? (string) $_SESSION['id'] : '';
            if ($userId === '') {
                Utility::msgException(401, 'Unauthorized');
                return;
            }

            $stmt = Db::connect2()->prepare('SELECT email FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $email = (string) ($stmt->fetchColumn() ?: '');
            if ($email === '') {
                Utility::msgException(404, 'Account not found');
                return;
            }

            $payload = (new DataExportService($userId, $email))->collect();
            $json = json_encode(
                $payload,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            ) ?: '{}';

            $filename = 'familyplatform-data-' . preg_replace('/[^A-Za-z0-9_-]/', '', $userId)
                . '-' . gmdate('Y-m-d') . '.json';

            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . \strlen($json));
            header('Cache-Control: no-store');
            echo $json;
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * Records or executes a GDPR Art. 17 erasure request:
     * - Option A (mode === 'immediate'): Instantly wipes/anonymises the account via DataErasureService.
     * - Option B (mode === 'schedule', default): Schedules account for 30-day deletion in DB with grace period.
     */
    public function requestDeletion(): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = isset($_SESSION['id']) ? (string) $_SESSION['id'] : '';
            if ($userId === '') {
                Utility::msgException(401, 'Unauthorized');
                return;
            }

            $stmt = Db::connect2()->prepare('SELECT email FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $memberEmail = (string) ($stmt->fetchColumn() ?: '');
            if ($memberEmail === '') {
                Utility::msgException(404, 'Account not found');
                return;
            }

            \Src\Limiter::limit('deletion:' . $userId);

            $rawInput = file_get_contents('php://input') ?: '';
            $json = json_decode($rawInput, true);
            $mode = is_array($json) && isset($json['mode']) ? (string) $json['mode'] : (string) ($_POST['mode'] ?? 'schedule');

            if ($mode === 'immediate') {
                // Option A: Immediate Permanent Erasure via DataErasureService
                $service = new \App\services\DataErasureService($userId);
                $eraseResult = $service->erase('self');

                if (!$eraseResult['success']) {
                    Utility::msgException(500, 'Erasure failed: ' . $eraseResult['message']);
                    return;
                }

                error_log("[GDPR] Art.17 immediate erasure completed — account_id={$userId} email={$memberEmail} at " . gmdate('c'));

                // Invalidate active session and authentication cookies
                $_SESSION = [];
                $sessName = \session_name();
                if (\ini_get('session.use_cookies') && is_string($sessName)) {
                    $params = \session_get_cookie_params();
                    \setcookie($sessName, '', time() - 42000,
                        $params['path'], $params['domain'],
                        $params['secure'], $params['httponly']
                    );
                }
                if (\session_status() === PHP_SESSION_ACTIVE) {
                    \session_destroy();
                }

                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                Utility::msgSuccess(
                    200,
                    'Your account and personal data have been permanently erased.',
                    [
                        'mode' => 'immediate',
                        'redirect' => '/login',
                    ]
                );
                return;
            }

            // Option B: 30-Day Scheduled Deletion (Grace Period)
            $updateStmt = Db::connect2()->prepare("
                UPDATE account
                SET status = 'pending_deletion',
                    deleted_at = NOW(),
                    token_version = token_version + 1
                WHERE id = ?
            ");
            $updateStmt->execute([$userId]);

            // Durable audit log
            try {
                $auditStmt = Db::connect2()->prepare("
                    INSERT INTO audit_logs (email, user_id, status, ip_address, user_agent, created_at)
                    VALUES (?, ?, 'GDPR_DELETION_SCHEDULED_30_DAYS', ?, ?, NOW())
                ");
                $auditStmt->execute([
                    $memberEmail,
                    $userId,
                    Utility::getUserIpAddr(),
                    substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 245),
                ]);
            } catch (\Throwable $e) {
                error_log('[GDPR] audit_logs insertion failed: ' . $e->getMessage());
            }

            error_log("[GDPR] Art.17 erasure request scheduled (30 days) — account_id={$userId} email={$memberEmail} at " . gmdate('c'));

            $opsEmail = (string) ($_ENV['ADMIN_EMAIL'] ?? getenv('ADMIN_EMAIL') ?: '');
            $opsEmail = str_contains($opsEmail, '${') ? '' : $opsEmail;
            if ($opsEmail !== '') {
                try {
                    SendEmailFunctionality::email(
                        'msg/dataDeletionRequest',
                        'GDPR — account deletion request (30-day scheduled)',
                        [
                            'email'         => $opsEmail,
                            'name'          => 'Data Protection',
                            'account_id'    => $userId,
                            'member_email'  => $memberEmail,
                            'requested_at'  => gmdate('c'),
                        ],
                        'admin'
                    );
                } catch (\Throwable $mailErr) {
                    error_log('[GDPR] deletion-request ops email failed: ' . $mailErr->getMessage());
                }
            }

            // Securely invalidate active session so user is logged out
            $_SESSION = [];
            $sessName = \session_name();
            if (\ini_get('session.use_cookies') && is_string($sessName)) {
                $params = \session_get_cookie_params();
                \setcookie($sessName, '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }
            if (\session_status() === PHP_SESSION_ACTIVE) {
                \session_destroy();
            }

            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            Utility::msgSuccess(
                200,
                'We have received your deletion request. Your account has been scheduled for permanent deletion in 30 days and you have been securely logged out.',
                [
                    'mode' => 'schedule',
                    'redirect' => '/login',
                ]
            );
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
