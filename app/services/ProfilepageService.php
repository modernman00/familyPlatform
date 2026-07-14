<?php
declare(strict_types=1);

namespace App\Services;

use App\model\SingleCustomerData;
use App\model\Post;
use App\model\AllMembersData as DataAll;
use Src\Exceptions\NotFoundException;

class ProfilepageService
{
    public function getProfileData($userId): array
    {
        $setData = new SingleCustomerData();

        $tables = ['personal', 'contact', 'otherFamily', 'post', 'profilePics'];
        $memberData = $setData->getCustomerData($userId, $tables);

        if (!$memberData) {
            throw new NotFoundException("User not found");
        }

        $famCode = checkInput($memberData['famCode']);

        return [
            'memberData' => $memberData,
            'famCode' => $famCode,
            'friendRequests' => DataAll::getFriendRequestData($userId, "Request sent"),
            'posts' => Post::getAllPostProfilePics(),
            'comments' => Post::getAllCommentProfilePics(),
            'events' => DataAll::getEventDataByFamCode($famCode),
            'post2Id' => Post::postLink2Id($userId),
            'pics' => Post::getAllPostPics($userId),
        ];
    }
}