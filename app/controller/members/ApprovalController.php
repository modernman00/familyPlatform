<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use Src\CheckToken;
use Src\Select;
use Src\UpdateFn;
use Src\SubmitForm;
use Src\functionality\middleware\GetRequestData;

final class ApprovalController extends BaseController
{
    /**
     * Approves a pending family join request
     */
    public static function approve(): void
    {
        try {
            CheckToken::tokenCheck();
            $input = GetRequestData::getRequestData();
            
            $userId = $input['user_id'] ?? null;
            $familyCode = $input['family_code'] ?? null;

            if (!$userId || !$familyCode) {
                msgException(400, "Missing user ID or family code.");
            }

            $db = self::connect2();
            $db->beginTransaction();

            try {
                // Update the user_families status to approved
                $stmt = $db->prepare("UPDATE user_families SET status = 'approved' WHERE user_id = ? AND family_code = ? AND status = 'pending'");
                $stmt->execute([$userId, $familyCode]);
                
                if ($stmt->rowCount() === 0) {
                    throw new \Exception("Request not found or already processed.");
                }

                // Add notification logic here to alert other pending reviewers that it's been approved
                // ... (Could insert into notification table)

                $db->commit();
                msgSuccess(200, "Application successfully approved.");
            } catch (\Exception $e) {
                $db->rollBack();
                msgException(500, "Failed to approve: " . $e->getMessage());
            }

        } catch (\Throwable $e) {
            showError($e);
        }
    }

    /**
     * Denies a pending family join request
     */
    public static function deny(): void
    {
        try {
            CheckToken::tokenCheck();
            $input = GetRequestData::getRequestData();
            
            $userId = $input['user_id'] ?? null;
            $familyCode = $input['family_code'] ?? null;

            if (!$userId || !$familyCode) {
                msgException(400, "Missing user ID or family code.");
            }

            $db = self::connect2();
            $db->beginTransaction();

            try {
                // Delete or update the record
                $stmt = $db->prepare("DELETE FROM user_families WHERE user_id = ? AND family_code = ? AND status = 'pending'");
                $stmt->execute([$userId, $familyCode]);
                
                if ($stmt->rowCount() === 0) {
                    throw new \Exception("Request not found or already processed.");
                }

                $db->commit();
                msgSuccess(200, "Application denied.");
            } catch (\Exception $e) {
                $db->rollBack();
                msgException(500, "Failed to deny: " . $e->getMessage());
            }

        } catch (\Throwable $e) {
            showError($e);
        }
    }
}
