<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\PushNotificationClass;
use App\model\{
    Post, AllMembersData,
};

use App\classes\InnerJoin;


class PostMessage
{

    /**
     * Index
     * 
     * Gets all the post on the profile page
     * 
     * Sets the count of all the posts in the session variable
     * 
     * @return void
     */
    public function index(): void
    {
        try {

            $newPost = new Post();
            $message = $newPost->getAllPostProfilePics();
            $count = count($message);

            $_SESSION['COUNT'] = $count;
            msgSuccess(200, $message);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }


    /**
     * Gets all the comments on the profile page
     * 
     * @return void
     */
    public function getComment(): void
    {
        try {
            $message = Post::getAllCommentProfilePics();
            msgSuccess(200, $message);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    /**
     * Get comment and commenter's img by commentNo
     * 
     * Gets a comment by the comment number
     * 
     * @return void
     */
    public function getCommentNo(): void
    {
        try {

            $commentNo = checkInput($_GET['commentNo']);

            $message = Post::commentByNo($commentNo);

            $data = null;
            foreach ($message as $data);

            // GET THE COMMENT PROFILE PICS USING THE COMMENT ID

            $profilePics = Post::getProfilePics($data['id']);

            $data['profileImg'] = $profilePics[0]['img'];

            msgSuccess(200, $data);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    /**
     * Get post by postNo
     * 
     * Gets a post by the post number
     * 
     * @return void
     */
    public function getPostByNo(): void
    {
        try {

            $postNo = checkInput(data: $_GET['postNo']);

            $message = Post::postByNo(postNo: $postNo);
            $data = null;
            foreach ($message as $data);
            msgSuccess(code: 200, msg: $data);
        } catch (\Throwable $th) {
            showErrorExp(th: $th);
        }
    }

    /**
     * getPostNo
     *
     * It retrieves the post with the given postNo and sends an email to all customers
     * with the name of the user who posted
     *
     * @return void
     */
    public function getPostNo(): void
    {
        try {

            $postNo = checkInput($_GET['postNo']);

            $message = Post::postByNo($postNo);
            $data = null;
            foreach ($message as $data);
            $postOriginName = $data['fullName'];

            $results = AllMembersData::AllMembersEmailByFamCode(checkInput($_SESSION['famCode']));

            $getUrl = getenv('MIX_APP_URL2');
            // clicking the url will takes you to the post in question
            $url = "$getUrl/member/ProfilePage?#$postNo";

            foreach ($results as $result) {
                // integrate the name of the poster to the data array
                $result['postOriginName'] = $postOriginName;
                $result['url'] = $url;

                sendEmailAll(
                    data: $result, 
                    viewPath: "msg/customer/notifyNewPost", 
                    subject: "$postOriginName posted a new update"
                );

                PushNotificationClass::sendPushNotification(
                    userId: $result['id'],
                    message: "$postOriginName posted a new update",
                    url: $url
                );
            };

            msgSuccess(200, $data);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    /**
     * Updates the post and comment data to the client
     *
     * It gets the last post id and last comment id from the session and
     * then sends the data to the client using the msgServerSent function
     *
     * @throws \Throwable
     * @return void
     */
    public static function update2(): void
    {
        try {
              // Make session read-only to prevent locks
            session_write_close();
            // disable default disconnect checks
            ignore_user_abort(true);

             // Set headers for Server-Sent Events (SSE)
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header("Connection: keep-alive");
            set_time_limit(0);
            // header('Access-Control-Allow-Origin: *');

            // Loop for continuous streaming
        while (true) {
            if($_SESSION['LAST_INSERT_ID_POST']) {
                $id = (int)$_SESSION['LAST_INSERT_ID_POST'];
            
            // Retrieve post data by ID
            $messages = Post::postByNo($id);

            if (empty($messages)) {
                msgException(401, "No post message");
            }

            // Extract the first message
            $message = $messages[0] ?? null;

            msgServerSent(
                data: $message, 
                id: $id, 
                event: "updatePost"
            );

              // Clear post session variable after processing
                    unset($_SESSION['LAST_INSERT_ID_POST']);
                
            }

        // Check if there is a comment ID in the session
            if (isset($_SESSION['LAST_INSERT_ID_COMMENT'])) {
                $idComment = (int) $_SESSION['LAST_INSERT_ID_COMMENT'];

                $idComment = (int) $_SESSION['LAST_INSERT_ID_COMMENT'];

                $messageComments = Post::commentByNo($idComment);

                msgServerSent(
                    data: $messageComments, 
                    id: $idComment, 
                    event: "updateComment"
                );
     
                unset($_SESSION['LAST_INSERT_ID_COMMENT']);
            }
            
            // Flush the output buffer to ensure data is sent to the client
            ob_flush();
            flush();

            // Delay before checking again to prevent high server load
            sleep(5); // Adjust the delay as needed
        }
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    /**
     * Sends a server-sent event to the client with the latest post data
     *
     * It retrieves the last post and comment id from the session and
     * then sends the data to the client using the msgServerSent function
     *
     * @throws \Throwable
     * @return void
     */
    public static function update(): void
    {
        session_write_close();
        // disable default disconnect checks
        ignore_user_abort(true);
        header("Content-Type: text/event-stream");
        header("Cache-Control: no-cache");
        header("Connection: keep-alive");
        set_time_limit(0);
        try {

            $id = isset($_SESSION['LAST_INSERT_ID_POST']) ? (int)$_SESSION['LAST_INSERT_ID_POST'] : msgException(401, "Error Processing ID request");

            $messages = Post::postByNo($id);

            if (empty($messages)) {
                msgException(401, "Error Processing ID request");
            }

            $message = null;
            foreach ($messages as $message);

            $idComment = (int) $_SESSION['LAST_INSERT_ID_COMMENT'];

            $messageComments = Post::commentByNo($idComment);

            // $pusher->trigger('my-channel', 'updatePost', $message);

            msgServerSent($message, $id, "updatePost");
            msgServerSent($messageComments, $idComment, "updateComment");
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }
}
