<?php
declare(strict_types=1);

namespace App\controller\members;


use App\classes\{AllFunctionalities, Pusher};
use Src\Db;
use App\model\Post;


final class PostLikeController extends Db
{

    /**
     * Toggles the post likes count and returns the new count.
     * 
     * The function is called by the AJAX request in the like button.
     * 
     * @return void
     */
    public static function postLikes()
    {
        try {
            $postNo = \cleanSession((string)($_GET['postNo'] ?? ''));
            $userId = \cleanSession((string)($_SESSION['id'] ?? ''));

            if (!$postNo || !$userId) {
                throw new \Exception("Invalid parameters for liking post");
            }

            $pdo = parent::connect2();
            
            // Check if user already liked
            $stmt = $pdo->prepare("SELECT id FROM post_reactions WHERE post_no = :postNo AND user_id = :userId AND reaction_type = 'like'");
            $stmt->execute(['postNo' => $postNo, 'userId' => $userId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                // User already liked, so unlike it (toggle)
                $pdo->prepare("DELETE FROM post_reactions WHERE id = :id")->execute(['id' => $existing['id']]);
                // Decrement count
                $pdo->prepare("UPDATE post SET post_likes = GREATEST(post_likes - 1, 0), likes_updated_at = NOW() WHERE post_no = :postNo")->execute(['postNo' => $postNo]);
            } else {
                // Insert new like
                $pdo->prepare("INSERT INTO post_reactions (post_no, user_id, reaction_type) VALUES (:postNo, :userId, 'like')")->execute(['postNo' => $postNo, 'userId' => $userId]);
                // Increment count
                $pdo->prepare("UPDATE post SET post_likes = post_likes + 1, likes_updated_at = NOW() WHERE post_no = :postNo")->execute(['postNo' => $postNo]);
            }

            self::getNewLikesPusher();

            msgSuccess(200, 'success');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Broadcasts the like count for a specific post to all clients.
     *
     * @param int $postId
     * @param int $likeCount
     * @param string $likeCounterId
     */
    public static function getNewLikesPusher(): void
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
        $token = checkInput($_GET['token']);
        $token = is_string($token) ? $token : '';
        setCookie(name: 'tokenJWT', value: $token, expires_or_options: time() + 3600, path: "/", domain: '', secure: true, httponly: true);
        msgSuccess(200, "message set");
    }
}
