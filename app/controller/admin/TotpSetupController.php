<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\BaseController;
use Src\Auth\TotpService;
use Src\Update;
use Src\Utility;

/**
 * Admin TOTP Enrollment Controller
 *
 * GET  /admin/setup-2fa  → show QR code & manual key
 * POST /admin/setup-2fa  → verify entered code, then save secret to DB
 * POST /admin/disable-2fa → clear totp_secret and set totp_enabled = 0
 */
final class TotpSetupController extends BaseController
{
    /** @var array<string, mixed> */
    private array $jwt;

    public function __construct()
    {
        parent::__construct();
        $jwt = \Src\functionality\SignIn::verify();
        if (empty($jwt['id']) || $jwt['role'] !== 'admin') {
            throw new \Src\Exceptions\UnauthorisedException('Unauthorized access to administrative 2-FA setup.');
        }
        $this->jwt = $jwt;
    }

    /**
     * GET /admin/setup-2fa
     * Generate a fresh secret (stored in session), render QR + manual key.
     */
    public function show(): void
    {
        try {
            // Only generate a new secret if one isn't already pending in the session
            if (empty($_SESSION['totp_pending_secret'])) {
                $_SESSION['totp_pending_secret'] = TotpService::generateSecret(20);
            }

            $secret  = $_SESSION['totp_pending_secret'];
            $account = (string) ($this->jwt['email'] ?? $this->jwt['id'] ?? 'admin');
            $provisioningUri = TotpService::getProvisioningUri($account, $secret, 'FamilyPlatform Admin');
            $qrUri   = TotpService::getQrCodeDataUri($provisioningUri);

            parent::viewWithCsp('admin/totp_setup', [
                'secret' => $secret,
                'qrUri'  => $qrUri,
                'account' => $account,
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * POST /admin/setup-2fa
     * Verify the 6-digit code the admin just scanned, then persist to DB.
     */
    public function save(): void
    {
        try {
            \Src\CheckToken::tokenCheck();

            $code   = trim((string) ($_POST['totp_code'] ?? ''));
            $secret = (string) ($_SESSION['totp_pending_secret'] ?? '');

            if (empty($secret)) {
                Utility::msgException(400, 'Session expired — please reload the setup page.');
                return;
            }

            if (!TotpService::verifyCode($secret, $code)) {
                Utility::msgException(422, 'Code did not match. Wait for the next 30-second window and try again.');
                return;
            }

            $adminId = (int) ($this->jwt['id'] ?? 0);
            if ($adminId <= 0) {
                Utility::msgException(401, 'Cannot determine admin identity.');
                return;
            }

            // Persist secret and enable flag using the shared-lib Update pattern
            $upSecret = new Update('account');
            $upSecret->updateTable(
                column: 'totp_secret',
                columnAnswer: $secret,
                identifier: 'id',
                identifierAnswer: (string) $adminId
            );

            $upEnabled = new Update('account');
            $upEnabled->updateTable(
                column: 'totp_enabled',
                columnAnswer: '1',
                identifier: 'id',
                identifierAnswer: (string) $adminId
            );

            // Clear the pending secret from session
            unset($_SESSION['totp_pending_secret']);

            Utility::msgSuccess(200, '2-FA successfully enabled! Your next login will require a Google Authenticator code.');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * POST /admin/disable-2fa
     * Remove the stored TOTP secret and turn off enforcement for this account.
     */
    public function disable(): void
    {
        try {
            \Src\CheckToken::tokenCheck();

            $adminId = (int) ($this->jwt['id'] ?? 0);
            if ($adminId <= 0) {
                Utility::msgException(401, 'Cannot determine admin identity.');
                return;
            }

            $upSecret = new Update('account');
            $upSecret->updateTable(
                column: 'totp_secret',
                columnAnswer: '',
                identifier: 'id',
                identifierAnswer: (string) $adminId
            );

            $upEnabled = new Update('account');
            $upEnabled->updateTable(
                column: 'totp_enabled',
                columnAnswer: '0',
                identifier: 'id',
                identifierAnswer: (string) $adminId
            );

            unset($_SESSION['totp_pending_secret']);

            Utility::msgSuccess(200, '2-FA has been disabled for this account.');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
