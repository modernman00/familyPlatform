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
     * Records a GDPR Art. 17 erasure request and routes it to ops.
     *
     * Deliberately not an automatic wipe — erasure has to respect content other
     * family members depend on and any legal hold, which is a manual review.
     * This gives members the required channel; the processing side (and an
     * automated pipeline) is tracked as GDPR-1.
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

            // Durable backstop — this line is the record of the request even if
            // the notification email below fails.
            error_log("[GDPR] Art.17 erasure request — account_id={$userId} email={$memberEmail} at " . gmdate('c'));

            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            Utility::msgSuccess(
                200,
                'We have received your deletion request. Our team will action it within 30 days and email you at ' . $memberEmail . ' when it is done.'
            );

            // Do not make the member wait for an operational notification email.
            // Local PHP servers cannot flush and continue safely, so leave the
            // notification to the durable log until a worker-backed mail queue
            // is configured.
            if (!function_exists('fastcgi_finish_request')) {
                return;
            }
            fastcgi_finish_request();

            $opsEmail = (string) ($_ENV['ADMIN_EMAIL'] ?? getenv('ADMIN_EMAIL') ?: '');
            $opsEmail = str_contains($opsEmail, '${') ? '' : $opsEmail; // guard against an unresolved .env var
            if ($opsEmail !== '') {
                try {
                    SendEmailFunctionality::email(
                        'msg/dataDeletionRequest',
                        'GDPR — account deletion request',
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
                    // The user's request still stands — it's in the error log above.
                    error_log('[GDPR] deletion-request ops email failed: ' . $mailErr->getMessage());
                }
            }
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
