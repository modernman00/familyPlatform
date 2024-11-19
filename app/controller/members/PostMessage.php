<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\{PushNotificationClass};

use App\model\{
    Post,
    AllMembersData,
};

use React\EventLoop\Loop;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;



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
                id: $_SESSION['memberId'], 
                famCode: checkInput($_SESSION['famCode']));

            if (!$message) msgException(401, "no post msg");

            $count = count($message);

            $newCommentArray = [];

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


    // public static function getNewCommentInterim() {
    //     // Use WebSocket for broadcasting updates
    //     ignore_user_abort(true);
    //     set_time_limit(0);

    //     while (true) {
    //         try {
    //             $getUnpublishedComment = Post::getUnpublishedPost();

    //             if ($getUnpublishedComment) {
    //                 foreach ($getUnpublishedComment as $comment) {
    //                     $commentNo = $comment['post_no'];
    //                     $comment['img'] = AllMembersData::commentProfilePicByPostNo($commentNo);

    //                     self::broadcastUpdate(['type' => 'updateComment', 'data' => $comment]);

    //                     Post::updateCommentByStatusAsPublished($commentNo);
    //                 }
    //             } else {
    //                 exit();
    //             }
    //             sleep(5);
    //         } catch (\Throwable $th) {
    //             showSSEError($th);
    //             break;
    //         }
    //     }
    // }

    // public static function broadcastUpdate($updateData) {
    //     $loop = Loop::get();
    //     $connector = new Connector($loop);

    //     $connector('ws://'.URL.':8080')->then(function(WebSocket $conn) use ($updateData) {
    //         $conn->send(json_encode($updateData));
    //         $conn->close();
    //     }, function($e) {
    //         echo "Could not connect: {$e->getMessage()}\n";
    //     });

    //     $loop->run();
    // }



    public static function getNewComment()
    {
        // Broadcast to all clients
        ignore_user_abort(true);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        set_time_limit(0);

        while (true) {
            try {
                $getUnpublishedComment = Post::getUnpublishedComment();

                if ($getUnpublishedComment) {

                    foreach ($getUnpublishedComment as $comment) {
                        $commentNo = $comment['post_no'];

                        msgServerSent($comment, $commentNo, 'updateComment');

                        // Post::updateCommentByStatusAsPublished($commentNo);

                        // PushNotificationClass::sendPushNotificationToUser($results, "New Post", "A new post has been published by $postOriginName");            

                    }
                }
                if (connection_aborted()) break;

                // Sleep briefly to avoid CPU overload
                sleep(5); // 0.5 seconds
            } catch (\Throwable $th) {
                showSSEError($th);
                break;
            }
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

        $maxDuration = 300; // 5 minutes
        $startTime = time();

        while (time() - $startTime < $maxDuration) {

            try {

                $getUnpublishedPost = Post::getUnpublishedPost();

                if ($getUnpublishedPost) {

                    foreach ($getUnpublishedPost as $post) {
                        $postNo = $post['id'];

                        msgServerSent($post, $postNo, 'updatePost');

                        // Post::updatePostByStatusAsPublished($postNo);

                        // PushNotificationClass::sendPushNotificationToUser($results, "New Post", "A new post has been published by $postOriginName");            

                    }
                }
                if (connection_aborted()) break;

                // Sleep briefly to avoid CPU overload
                sleep(5); // 0.5 seconds

            } catch (\Throwable $th) {
                showSSEError($th);
                break;
            }
        }
    }

     public static function getNewPostPolling()
    {
       
        header('Content-Type: application/json');
  
            try {

                $getUnpublishedPost = Post::getUnpublishedPost();

                if ($getUnpublishedPost) {

                    foreach ($getUnpublishedPost as $post) {
                        $postNo = $post['id'];

                        msgSuccess(200, $post);


                        // Post::updatePostByStatusAsPublished($postNo);

                        // PushNotificationClass::sendPushNotificationToUser($results, "New Post", "A new post has been published by $postOriginName");            

                    }
                } 
             


            } catch (\Throwable $th) {
                showError($th);
        
            }
     
    }

     public static function getNewCommentPolling()
    {
       
        header('Content-Type: application/json');
  
            try {

                $getUnpublishedComment = Post::getUnpublishedComment();

                if ($getUnpublishedComment) {

                    foreach ($getUnpublishedComment as $comment) {
                        $commentNo = $comment['id'];

                        msgSuccess(200, $comment);


                        // Post::updatePostByStatusAsPublished($postNo);

                        // PushNotificationClass::sendPushNotificationToUser($results, "New Post", "A new post has been published by $postOriginName");            

                    }
                }
             


            } catch (\Throwable $th) {
                showError($th);
        
            }
     
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
