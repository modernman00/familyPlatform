<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\PushNotificationClass;
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
            $message = AllMembersData::postProfilePicByFamCode(checkInput($_SESSION['famCode']));

            if (!$message) msgException(401, "no post msg");



            $count = count($message);

            $newCommentArray = array();

            foreach ($message as $msg) {

                $commentData = AllMembersData::commentProfilePicByPostNo($msg['post_no']); // it works
                $newCommentArray[] = $commentData; // add the result to $newCommentArray instead of overwriting
            }

            $_SESSION['COUNT'] = $count;
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


    public static function getNewComment()
    {
               // Broadcast to all clients
        ignore_user_abort(true);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        set_time_limit(0);
        try {

            $commentNo =  cleanSession($_SESSION['LAST_INSERT_ID_COMMENT']);

            unset($_SESSION['LAST_INSERT_ID_COMMENT']);

            if ($commentNo) {

                $message = Post::commentByNo($commentNo);

                $data = $message[0];

                // GET THE COMMENT PROFILE PICS USING THE COMMENT ID
                if ($data) {

                    $data['img'] = self::getProfilePicsForPostAndComment($data['id']);

                    \msgServerSent($data, $commentNo, 'updateComment');
                }
            }
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    public static function getNewPost()
    {

       // Broadcast to all clients
        ignore_user_abort(true);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        set_time_limit(0);
        try {

            $postNo = cleanSession($_SESSION['LAST_INSERT_ID_POST']);

            unset($_SESSION['LAST_INSERT_ID_POST']);

            if ($postNo) {
                $message = Post::postByNo($postNo);
                $data = $message[0];

                if ($data) {

                    $data['img'] = self::getProfilePicsForPostAndComment($data['id']);


                    msgServerSent($data, $postNo, 'updatePost');
                }
            }
            // msgSuccess(200, $data);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }



    public static function getNewPostAndEmail()
    {
        try {

            $postNo = cleanSession($_GET['newCommentNo']);

            $message = Post::postByNo($postNo);
            $data = $message[0];

            $postOriginName = $data['fullName'];

            $results = AllMembersData::AllMembersEmailByFamCode(checkInput($data['postFamCode']));

            $url = self::buildPostUrl($postNo);

            if ($data) {
                // Send notifications to members
                self::notifyMembers(
                    results: $results,
                    id: $data['id'],
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
    private static function notifyMembers(array $results, string $id, string $postOriginName, string $url): void
    {
        $getPostProfilePics = self::getProfilePicsForPostAndComment($id);

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

            // Send push notification to all members
            PushNotificationClass::sendPushNotification(
                userId: $memberData['id'],
                message: "$postOriginName posted a new update",
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
