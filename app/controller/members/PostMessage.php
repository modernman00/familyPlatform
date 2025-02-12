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

    // public static function getNewCommentSSE()
    // {
    //     // Broadcast to all clients
    //     ignore_user_abort(true);
    //     header('Content-Type: text/event-stream');
    //     header('Cache-Control: no-cache');
    //     header('Connection: keep-alive');
    //     set_time_limit(0);

    //     try {
    //         $noNewCommentCount = 0; // Counter for loop cycles without new posts 
    //         while (true) {

    //             $getUnpublishedComment = Post::getUnpublishedComment();


    //             if ($getUnpublishedComment) {

    //                 foreach ($getUnpublishedComment as $comment) {
    //                     $commentNo = $comment['post_no'];

    //                     msgServerSent(
    //                         data: $comment,
    //                         id: $commentNo,
    //                         event: 'updateComment'
    //                     );

    //                     // Post::updateCommentByStatusAsPublished($commentNo);

    //                     // PushNotificationClass::sendPushNotificationToUser($results, "New Post", "A new post has been published by $postOriginName");            

    //                 }
    //             } else {

    //                 $noNewCommentCount++;
    //                 if ($noNewCommentCount >= 10) {
    //                     break;
    //                 }
    //             }
    //             if (connection_aborted()) break;

    //             // Sleep briefly to avoid CPU overload
    //             sleep(5); // 0.5 seconds
    //         }
    //     } catch (\Throwable $th) {
    //         showSSEError($th);
    //     }
    // }

    // public static function getNewPostSSE()
    // {

    //     // Broadcast to all clients
    //     ignore_user_abort(true);
    //     header('Content-Type: text/event-stream');
    //     header('Cache-Control: no-cache');
    //     header('Connection: keep-alive');
    //     set_time_limit(0);

    //     $maxDuration = 300; // 5 minutes
    //     $startTime = time();
    //     $noNewPostsCount = 0; // Counter for loop cycles without new posts  
    //     $response = [];

    //     try {

    //         while (time() - $startTime < $maxDuration) {

    //             $getUnpublishedPost = Post::getUnpublishedPost();

    //             if ($getUnpublishedPost) {
    //                 $noNewPostsCount = 0;

    //                 foreach ($getUnpublishedPost as $post) {
    //                     $response = $post;
    //                     $postNo = $post['id'];

    //                     msgServerSent(
    //                         data: $response,
    //                         id: $postNo,
    //                         event: 'updatePost'
    //                     );

    //                     // Post::updatePostByStatusAsPublished($postNo);

    //                     // PushNotificationClass::sendPushNotificationToUser($results, "New Post", "A new post has been published by $postOriginName");            

    //                 }
    //             } else {
    //                 $noNewPostsCount++;
    //                 if ($noNewPostsCount >= 10) {
    //                     break;
    //                 }
    //             }
    //             if (connection_aborted()) break;

    //             // Sleep briefly to avoid CPU overload
    //             sleep(10); // 0.5 seconds
    //         }
    //     } catch (\Throwable $th) {
    //         showSSEError($th);
    //     }
    // }

    // public static function getNewPostPolling()
    // {
    //     $id = cleanSession($_SESSION['memberId']);
    //     $famCode = checkInput($_SESSION['famCode']);
    //     self::fetchAndRespond(
    //         fetchFunction: [AllMembersData::class, 'getUnpublishedPostByFamCode'],   
    //         params: [$famCode, $id]
    //     );
    // }

    // public static function getNewCommentPolling()
    // {
    //     self::fetchAndRespond( fetchFunction: [Post::class, 'getUnpublishedComment']);
    // }

    // private static function fetchAndRespond(callable $fetchFunction, array $params = null): void
    // {
    //     header('Content-Type: application/json');
    //     try {
    //         $items = (isset($params))? $fetchFunction(...$params) : $fetchFunction();
    //         $response = [];
    //         if ($items) {
    //             foreach ($items as $item) {
    //                 $item['origin'] = getenv("APP_URL2");
    //                 $response[] = $item;
    //             }
    //             msgSuccess(code: 200, msg: $response);
    //         }
    //     } catch (\Throwable $th) {
    //         showError($th);
    //     }
    // }

    // build for Pusher library
    private static function fetchNewMsg(callable $fetchFunction, array $params = null): array
    {
        try {
            $items = (isset($params))? $fetchFunction(...$params) : $fetchFunction();
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

    public static function getNewPostPusher()
    {
        $id = cleanSession($_SESSION['memberId']);
        $famCode = checkInput($_SESSION['famCode']);
        $newPost = self::fetchNewMsg(
            fetchFunction: [AllMembersData::class, 'getUnpublishedPostByFamCode'],   
            params: [$famCode, $id]
        );

        Pusher::broadcast('posts-channel', 'new-post' ,$newPost);
    }

    public static function getNewCommentPusher()
    {
        $newComment = self::fetchNewMsg(fetchFunction: [Post::class, 'getUnpublishedComment']);

        Pusher::broadcast('comments-channel', 'new-comment' ,$newComment);
    }

    public static function updateCommentByStatusAsPublished($commentNo)
    {
        $commentNo = cleanSession($commentNo);

        return Post::updateCommentByStatusAsPublished($commentNo);
    }

    public static function getNewPostAndEmail()
    {
        try {

            $postNo = cleanSession($_GET['newCommentNo']);

            $data = Post::postByNo($postNo);

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
