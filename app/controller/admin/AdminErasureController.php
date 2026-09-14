<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\BaseController;
use App\services\DataErasureService;
use Src\CheckToken;
use Src\Db;
use Src\Utility;

final class AdminErasureController
{
    public function __construct()
    {
        $verifyJWT = \Src\functionality\SignIn::verify();
        if (empty($verifyJWT['id'])) {
            throw new \Src\Exceptions\UnauthorisedException("Unauthorized access to administrative area.");
        }
    }

    /**
     * Admin view listing accounts with status 'erased' or pending deletion requests.
     */
    public function index(): void
    {
        $db = Db::connect2();
        $stmt = $db->query("
            SELECT id, email, status, created_at, deleted_at
            FROM account
            WHERE status = 'erased' OR email LIKE 'erased-%@anonymised.local'
            ORDER BY id DESC
            LIMIT 50
        ");
        $erasedAccounts = ($stmt instanceof \PDOStatement) ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

        BaseController::viewWithCsp('admin/erasure', [
            'erasedAccounts' => $erasedAccounts,
        ]);
    }

    /**
     * Executes GDPR Art. 17 erasure on a specific user ID.
     */
    public function process(): void
    {
        try {
            CheckToken::tokenCheck();

            $adminId = isset($_SESSION['id']) ? (string) $_SESSION['id'] : 'admin';
            $targetUserId = isset($_POST['target_user_id']) ? trim((string) $_POST['target_user_id']) : '';

            if ($targetUserId === '') {
                Utility::msgException(400, 'Missing target_user_id parameter.');
                return;
            }

            $service = new DataErasureService($targetUserId);
            $result = $service->erase($adminId);

            if (!$result['success']) {
                Utility::msgException(500, $result['message']);
                return;
            }

            Utility::msgSuccess(200, $result['message']);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
