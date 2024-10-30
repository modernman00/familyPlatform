<?php


declare(strict_types=1);

namespace App\controller\members;


use App\classes\{Db, AllFunctionalities};
use App\model\Post;


class PostLikeController extends Db
{

    /**
     * Increments the post likes count and returns the new count.
     * 
     * The function is called by the AJAX request in the like button.
     * 
     * @return void
     */
    public static function postLikes()
    {
        try {
            $id = cleanSession($_GET['postId']);
            $count = (int) cleanSession($_GET['count']);
            $count += 1;
            AllFunctionalities::update2('post', 'post_likes', $count, 'post_no', $id);

            $_SESSION['POST_NO_FOR_LIKES'] =$id;
            $_SESSION['HTML_ID_FOR_LIKES'] =cleanSession($_GET['likeCounterId']);

            msgSuccess(200, 'success');
        } catch (\Throwable $th) {
            returnErrorCode(505, $th);
        }
    }

    public static function getLikes()
    {
          // Broadcast to all clients
        ignore_user_abort(true);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        set_time_limit(0);
        try {
            $id = cleanSession($_SESSION['POST_NO_FOR_LIKES']);
            unset($_SESSION['POST_NO_FOR_LIKES']);
            
            $message = $message = Post::postByNo($id);
                $result = $message[0];;
            
            $outcome = [
                'likeCounter' => $result['post_likes'],
                'likeHtmlId' => $_SESSION['HTML_ID_FOR_LIKES'],
                ];

        msgServerSent($outcome, $id, 'updateLike');
              
            
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }


    /**
     * Sets an HTTP cookie for JWT token.
     * 
     * Retrieves the token from the GET request and sets a secure, HTTP-only cookie with a one-hour expiry.
     * Responds with a success message upon completion.
     * 
     * @return void
     */
    public function setHeader()
    {
        $token = $_GET['token'];
        setCookie(name: 'tokenJWT', value: $token, expires_or_options: time() + 3600, path: "/", domain: '', secure: true, httponly: true);
        msgSuccess(200, "message set");
    }
}
