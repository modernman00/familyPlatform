<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\{PushNotificationClass, Pusher};

use App\model\{
    Post,
    AllMembersData,
};




class PostMessage
{

    /**
     * Index
     * 
     * Gets all the post on the profile page
     * 
     * Sets the count of all the posts in the session variable
     * 

     */
    public static function index()
    {

        try {
            // it works
            $message = AllMembersData::postProfilePicByFamCode(
                id: cleanSession($_SESSION['memberId']),
                famCode: checkInput($_SESSION['famCode'])
            );

            if (!$message) msgException(401, "no post msg");

            $count = count($message);

            $newCommentArray = [];

            foreach ($message as $msg) {

                $commentData = AllMembersData::commentProfilePicByPostNo($msg['post_no']); // it works
                $newCommentArray[] = $commentData; // add the result to $newCommentArray instead of overwriting
            }

            $_SESSION['POST_COUNT'] = $count;
            msgSuccess(
                code: 200,
                msg: [
                    'post' => $message,
                    'comment' => $newCommentArray
                ]
            );
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    // update post and comment as published 
    public static function updatePostAsPublished($postNo)
    {
        try {
            Post::updatePostByStatusAsPublished($postNo);
            msgSuccess(200, "post updated successfully");
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    public static function updateCommentAsPublished($commentNo)
    {
        try {
            Post::updateCommentByStatusAsPublished($commentNo);

            msgSuccess(200, "comment updated successfully");
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }


    // build for Pusher library
    private static function fetchNewMsg(callable $fetchFunction, array $params = null)
    {
        try {
            $items = (isset($params)) ? $fetchFunction(...$params) : $fetchFunction();
            $response = [];
            if ($items) {
                foreach ($items as $item) {
                    $item['origin'] = getenv("APP_URL2");
                    $response[] = $item;
                }
                return $response;
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Fetches unpublished posts for the current user's family code and broadcasts them via Pusher.
     * 
     * This function retrieves new posts that have not yet been published, based on the user's 
     * family code and member ID. The posts are then broadcasted to the 'posts-channel' with 
     * the event name 'new-post' using the Pusher library.
     * 
     * @return void
     */

    public static function getNewPostPusher()
    {
        $id = cleanSession($_SESSION['memberId']);
        $famCode = checkInput($_SESSION['famCode']);
        $newPost = self::fetchNewMsg(
            fetchFunction: [AllMembersData::class, 'getUnpublishedPostByFamCode'],
            params: [$famCode, $id]
        );

        Pusher::broadcast('posts-channel', 'new-post', $newPost);
    }

    /**
     * Fetches unpublished comments and broadcasts them via Pusher.
     * 
     * This function retrieves new comments that have not yet been published
     * and broadcasts them to the 'comments-channel' with the event name 
     * 'new-comment' using the Pusher library.
     * 
     * @return void
     */

    public static function getNewCommentPusher()
    {
        $newComment = self::fetchNewMsg(fetchFunction: [Post::class, 'getUnpublishedComment']);
        \printArr($newComment);

        Pusher::broadcast('comments-channel', 'new-comment', $newComment);
        // send push notification to all members with the same family code 
        $data = Post::postByNo($newComment[0]['post_no']);

        $results = AllMembersData::AllMembersEmailByFamCode(checkInput($data['postFamCode']));

        $url = self::buildPostUrl($newComment[0]['post_no']);

        self::notifyMembersByPushNotification(
            results: $results,
            postId: $newComment[0]['id'],
            postOriginName: $newComment[0]['fullName'],
            url: $url
        );
    }

    public static function updateCommentByStatusAsPublished($commentNo)
    {
        $commentNo = cleanSession($commentNo);

        return Post::updateCommentByStatusAsPublished($commentNo);
    }

    /**
     * Sends an email to all members with the same family code as the post.
     * The email is a notification that a new post has been published.
     * 
     * @param int $postNo The post number of the new post
     * @return void
     * @throws \Throwable
     */
    public static function getNewPostAndEmail()
    {
        try {

            $postNo = cleanSession($_GET['newCommentNo']);

            $data = Post::postByNo($postNo);

            $postOriginName = $data['fullName'];
            $id = \checkInput($data['id']);
            $famCode = checkInput($data['postFamCode']);

            $results = AllMembersData::AllMembersEmailByFamCode($famCode, $id);

            $url = self::buildPostUrl($postNo);

            if ($data) {
                // Send notifications to members by email
                self::notifyMembersByEmail(
                    results: $results,
                    postId: $data['id'],
                    postOriginName: $postOriginName,
                    url: $url
                );
                self::notifyMembersByPushNotification(
                    results: $results,
                    postId: $data['id'],
                    postOriginName: $postOriginName,
                    url: $url
                );
            }
            msgSuccess(200, "Success");
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    // Helper function to construct post URL
    private static function buildPostUrl($postNo): string
    {
        $getUrl = getenv('MIX_APP_URL');
        return "$getUrl/member/ProfilePage?#post$postNo";
    }

    // Helper function to notify members by email and push notifications
    private static function notifyMembersByEmail(array $results, string $postId, string $postOriginName, string $url): void
    {
        $getPostProfilePics = self::getProfilePicsForPostAndComment($postId);

        foreach ($results as $memberData) {
            $memberData['postOriginName'] = $postOriginName;
            $memberData['url'] = $url;
            $memberData['img'] = $getPostProfilePics;

            // Send email to all members
            sendEmailAll(
                data: $memberData,
                viewPath: "msg/customer/notifyNewPost",
                subject: "$postOriginName posted a new update"
            );
        }
    }

    /**
     * Send push notifications to all members with the same family code as the post author.
     * 
     * @param array $results The array of member data from the database query.
     * @param string $postId The post ID.
     * @param string $postOriginName The name of the post author.
     * @param string $url The URL of the post.
     * 
     * @return void
     */
    private static function notifyMembersByPushNotification(array $results, string $postId, string $postOriginName, string $url): void
    {
        $getPostProfilePics = self::getProfilePicsForPostAndComment($postId);

        foreach ($results as $memberData) {
            $memberData['postOriginName'] = $postOriginName;
            $memberData['url'] = $url;
            $memberData['img'] = $getPostProfilePics;
            // Send push notification to all members WITH THE FAMILY CODE
            PushNotificationClass::sendPushNotification(
                userId: $memberData['id'],
                message: "$postOriginName posted a new comment",
                url: $url
            );
        }
    }

    // HELPER FUNCTION TO GET THE PROFILE PICS FOR COMMENTS AND POST 
    private static function getProfilePicsForPostAndComment($postId): string|null
    {
        $result = Post::getProfilePics($postId);
        return $result[0]["img"];
    }
}
