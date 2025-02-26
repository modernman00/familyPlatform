<?php


declare(strict_types=1);

namespace App\controller\members;


use App\classes\{Db, AllFunctionalities, Pusher};
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
            $postNo = cleanSession($_GET['postNo']);
            $count = (int) cleanSession($_GET['count']);
            $count += 1;

            // Update both post_likes and likes_updated_at

            AllFunctionalities::updateWithTimestamp(
                table: 'post',
                likesColumn: 'post_likes',
                likesValue: $count,
                timestampColumn: 'likes_updated_at',
                whereColumn: 'post_no',
                whereValue: $postNo
            );

            msgSuccess(200, 'success');
        } catch (\Throwable $th) {

            returnErrorCode(505, $th);
        }
    }

    /**
     * Broadcasts the like count for a specific post to all clients.
     * 
     * @param int $postId
     * @param int $likeCount
     * @param string $likeCounterId
     */

  
    public static function getNewLikesPusher()
    {
        try {
            // Fetch updated like counts from the database
            $updatedLikes = Post::fetchUpdatedLikes();
  
            $response = [];

            if (is_array($updatedLikes) && !empty($updatedLikes)) {
                foreach ($updatedLikes as $postLikes) {
                    $postNo = $postLikes['post_no'];

                    // Data to broadcast
                    $data = [
                        'origin' => getenv("APP_URL2") ?: 'default_value', // Set a default value if not set
                        'likeCounter' => $postLikes['post_likes'],
                        'likeHtmlId' => "likeCounter$postNo",
                    ];

                    $response[] = $data;
                }

                try {
                    Pusher::broadcast('likes-channel', 'like-event', $response);
                } catch (\Exception $e) {
                    showError($e); // Handle broadcasting error
                }
            }
        } catch (\Throwable $th) {
            showError($th); // Handle general errors
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
