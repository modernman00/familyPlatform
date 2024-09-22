<?php

declare(strict_types=1);

namespace App\controller\members;

use App\model\{
    Post
};

use App\classes\InnerJoin;

use Pusher\Pusher;
class PostMessage
{

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


    public function getComment(): void
    {
        try {
            $message = Post::getAllCommentProfilePics();
            msgSuccess(200, $message);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

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

    public function getPostNo(): void
    {
        try {

            $postNo = checkInput($_GET['postNo']);

            $message = Post::postByNo($postNo);
            $data = null;
            foreach ($message as $data);
            $posterName = $data['fullName'];

            $result = InnerJoin::joinAll2('personal', 'id', ['contact'], 'email');

            foreach ($result as $notifyCustomerData) {
                // integrate the name of the poster to the data array
                $notifyCustomerData['poster'] = $posterName;
              
                sendEmailAll($notifyCustomerData, "msg/customer/notifyNewPost", "$posterName has posted a new update");

            };

            msgSuccess(200, $data);

        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    public static function update2(): void
    {
        try {
            // session_destroy();
            // make session read-only
            session_write_close();
            // disable default disconnect checks
            ignore_user_abort(true);

            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            // header('Access-Control-Allow-Origin: *');

            // // ! deprecated at some point
            // $lastEventId = floatval(isset($_SERVER['HTTP_LAST_EVENT_ID']) ? $_SERVER['HTTP_LAST_EVENT_ID'] : 0);

            // if ($lastEventId == 0) {
            //     $lastEventId = floatval(isset($_GET['lastEventId']) ? $_GET['lastEventId'] : 0);
            // }

            $id = (int) $_SESSION['LAST_INSERT_ID_POST'];


            $messages = Post::postByNo($id);
            $message = null;
            foreach ($messages as $message);

            $idComment = (int) $_SESSION['LAST_INSERT_ID_COMMENT'];

            $messageComments = Post::commentByNo($idComment);



            msgServerSent($message, $id, "updatePost");
            msgServerSent($messageComments, $idComment, "updateComment");
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    public static function update(): void
    {
        try {

            // $option = array(
            //     'cluster' => 'eu',
            //     'useTLS' => true
            // );

            // $pusher = new Pusher(
            //     'd1f1e43f3d8afb028a1f',
            //     '67557ee07262c6a29970',
            //     '1218019',
            //     $option
            // );
       

           $id = isset($_SESSION['LAST_INSERT_ID_POST']) ? (int)$_SESSION['LAST_INSERT_ID_POST'] : null;


            $messages = Post::postByNo($id);
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
