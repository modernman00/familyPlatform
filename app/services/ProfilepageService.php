<?php
declare(strict_types=1);

namespace App\services;

use App\model\SingleCustomerData;
use App\model\Post;
use App\model\AllMembersData as DataAll;
use App\repository\FeedRepository;
use Src\Exceptions\NotFoundException;

final class ProfilepageService
{
    private FeedRepository $feedRepo;

    public function __construct(?FeedRepository $feedRepo = null)
    {
        $this->feedRepo = $feedRepo ?? new FeedRepository();
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfileData(string $userId): array
    {
        $setData = new SingleCustomerData();

        $tables = ['personal', 'contact', 'otherFamily', 'post', 'profilePics'];
        $memberData = $setData->getCustomerData($userId, $tables);

        if (!$memberData || !is_array($memberData)) {
            throw new NotFoundException("User not found");
        }

        $famCodeClean = checkInput($memberData['famCode']);
        $famCode = is_string($famCodeClean) ? $famCodeClean : '';
        $famCodes = $_SESSION['famCodes'] ?? ($famCode !== '' ? [$famCode] : []);

        $pendingFamilyRequests = [];
        try {
            $pendingFamilyRequests = (new \App\service\FamilyCodeApprovalService(\Src\Db::connect2()))
                ->getPendingApprovalsWithDetails($userId, $famCode);
        } catch (\Throwable $e) {
            error_log('[ProfilepageService] Failed to load pending family requests: ' . $e->getMessage());
        }

        $pendingMigrations = [
            'incomingNudges' => [],
            'incomingRequests' => [],
            'totalPendingCount' => 0
        ];
        try {
            $pendingMigrations = (new \App\services\FamilyMigrationService(\Src\Db::connect2()))
                ->getPendingMigrationsForUser($userId, $famCode);
        } catch (\Throwable $e) {
            error_log('[ProfilepageService] Failed to load pending migrations: ' . $e->getMessage());
        }

        $allFamilyMembers = (new DataAll())->getAllMembers($userId);
        $familyRoster = array_values(array_map(static fn(array $m): array => [
            'id' => (string)($m['id'] ?? ''),
            'firstName' => (string)($m['firstName'] ?? ''),
            'lastName' => (string)($m['lastName'] ?? ''),
            'img' => (string)($m['img'] ?? ''),
        ], $allFamilyMembers));

        return [
            'memberData' => $memberData,
            'famCode' => $famCode,
            'friendRequests' => DataAll::getFriendRequestData($userId, "Request sent"),
            'posts' => $this->feedRepo->getFeedPostsAsArray($userId, (array)$famCodes, 50, 0),
            'comments' => Post::getAllCommentProfilePics(),
            'events' => DataAll::getEventDataByFamCode((array)$famCodes),
            'post2Id' => Post::postLink2Id($userId),
            'pics' => Post::getAllPostPics($userId),
            'totalFamilyMembers' => count($allFamilyMembers),
            'familyRoster' => $familyRoster,
            'unclaimedMatch' => FamilyClaimService::findFuzzyUnclaimedMatches($famCode, $userId, $memberData),
            'pendingFamilyRequests' => $pendingFamilyRequests,
            'pendingMigrations' => $pendingMigrations,
        ];
    }
}