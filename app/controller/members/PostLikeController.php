<?php


declare(strict_types=1);

namespace App\controller\members;


use App\classes\{Db, AllFunctionalities, EventEmitter};
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

    public static function getLikesInterim()
    {
        // Broadcast to all clients
        ignore_user_abort(true);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        set_time_limit(0);


        try {
            // Fetch updated like counts from the database
            $updatedLikes = Post::fetchUpdatedLikes();

            // Emit the event 
            EventEmitter::emit('likesUpdated', $updatedLikes);

            EventEmitter::on('likesUpdated', function ($updatedLikes) {
                foreach ($updatedLikes as $postLikes) {
                    $likeCount = $postLikes['post_likes'];
                    $postNo = $postLikes['post_no'];
                    $likeHTMLId = "likeCounter$postNo";
                    $data = ['likeCounter' => $likeCount, 'likeHtmlId' => $likeHTMLId,];

                    msgServerSent($data, $postNo, 'updateLike');
                }
            });


        } catch (\Throwable $th) {
            showSSEError($th);
        }

        // Keep the connection open 
        while (true) {
            usleep(500000); // 0.5 seconds to keep the connection alive }
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

        while (true) {
            try {
                // Fetch updated like counts from the database
                $updatedLikes = Post::fetchUpdatedLikes();

                if ($updatedLikes) {
                    foreach ($updatedLikes as $postLikes) {
                        $likeCount = $postLikes['post_likes'];
                        $postNo = $postLikes['post_no'];
                        $likeHTMLId = "likeCounter$postNo";

                        // Data to broadcast
                        $data = [
                            'likeCounter' => $likeCount,
                            'likeHtmlId' => $likeHTMLId,
                        ];

                        msgServerSent($data, $postNo, 'updateLike');
                    }
                } else {
                    exit();
                }
                  if (connection_aborted()) break;
                sleep(5);
            } catch (\Throwable $th) {
                showSSEError($th);
                break;
            }
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
