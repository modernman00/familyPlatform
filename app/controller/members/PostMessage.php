<?php

declare(strict_types=1);

namespace App\controller\members;

use App\model\{
    SingleCustomerData,
    Post
};

use App\classes\{
    Select
};

use Pusher\Pusher;
class PostMessage
{

    function index()
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


    function getComment()
    {
        try {
            $message = Post::getAllCommentProfilePics();
            msgSuccess(200, $message);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    function getCommentNo()
    {
        try {

            $commentNo = checkInput($_GET['commentNo']);

            $message = Post::commentByNo($commentNo);

            foreach ($message as $data);

            // GET THE COMMENT PROFILE PICS USING THE COMMENT ID

            $profilePics = Post::getProfilePics($data['id']);

            $data['profileImg'] = $profilePics[0]['img'];




            msgSuccess(200, $data);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    function getPostNo()
    {
        try {

            $postNo = checkInput($_GET['postNo']);

            $message = Post::postByNo($postNo);
            foreach ($message as $data);
            msgSuccess(200, $data);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    static function update2()
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
            foreach ($messages as $message);

            $idComment = (int) $_SESSION['LAST_INSERT_ID_COMMENT'];

            $messageComments = Post::commentByNo($idComment);



            msgServerSent($message, $id, "updatePost");
            msgServerSent($messageComments, $idComment, "updateComment");
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    static function update()
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


            $id = (int) $_SESSION['LAST_INSERT_ID_POST'];


            $messages = Post::postByNo($id);
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
