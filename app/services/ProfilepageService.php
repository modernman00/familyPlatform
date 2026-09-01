<?php
declare(strict_types=1);

namespace App\services;

use App\model\SingleCustomerData;
use App\model\Post;
use App\model\AllMembersData as DataAll;
use Src\Exceptions\NotFoundException;

final class ProfilepageService
{
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

        return [
            'memberData' => $memberData,
            'famCode' => $famCode,
            'friendRequests' => DataAll::getFriendRequestData($userId, "Request sent"),
            'posts' => Post::getAllPostProfilePics(),
            'comments' => Post::getAllCommentProfilePics(),
            'events' => DataAll::getEventDataByFamCode($famCode),
            'post2Id' => Post::postLink2Id($userId),
            'pics' => Post::getAllPostPics($userId),
            'totalFamilyMembers' => count((new DataAll())->getAllMembers($userId)),
            'unclaimedMatch' => FamilyClaimService::findFuzzyUnclaimedMatches($famCode, $userId, $memberData),
        ];
    }
}