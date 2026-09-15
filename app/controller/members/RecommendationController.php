<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\FamilyRecommendationService;
use Src\CheckToken;
use Src\Db;
use Src\functionality\SignIn;
use Src\Utility;

final class RecommendationController
{
    /**
     * API endpoint returning the current user's personalized referral payload.
     *
     * GET /api/recommendation/get-link
     */
    public function getShareLink(): void
    {
        try {
            $payload = SignIn::verify('users');
            $userId = (string) $payload['id'];

            $db = Db::connect2();
            $stmt = $db->prepare('SELECT CONCAT(COALESCE(firstName, \'\'), \' \', COALESCE(lastName, \'\')) AS full_name FROM personal WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $userName = (string) ($stmt->fetchColumn() ?: 'A Family Member');

            $appUrl = getenv('APP_URL') ?: 'https://myfamilyplatform.com';
            $shareData = FamilyRecommendationService::getSharePayload($userId, trim($userName), (string)$appUrl);
            $stats = FamilyRecommendationService::getRecommendationStats($userId);

            Utility::msgSuccess(200, 'Recommendation link generated.', [
                'share' => $shareData,
                'stats' => $stats,
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * Public landing page for friends clicking a referral recommendation link.
     *
     * GET /join
     */
    public function joinLanding(): void
    {
        try {
            $rawRef = $_GET['ref'] ?? '';
            $refToken = is_string($rawRef) ? trim($rawRef) : '';

            $inviterName = null;
            $isValidRef = false;

            if ($refToken !== '') {
                $resolved = FamilyRecommendationService::verifyAndResolveReferralToken($refToken);
                if ($resolved !== null) {
                    $inviterName = $resolved['inviter_name'];
                    $isValidRef = true;
                    $_SESSION['pending_referral'] = [
                        'inviter_id'   => $resolved['inviter_id'],
                        'inviter_name' => $resolved['inviter_name'],
                        'token'        => $refToken,
                    ];
                }
            }

            BaseController::viewWithCsp('registration/joinRecommendation', [
                'inviterName' => $inviterName,
                'isValidRef'  => $isValidRef,
                'refToken'    => $refToken,
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
