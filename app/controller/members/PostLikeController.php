<?php
declare(strict_types=1);

namespace App\controller\members;


use App\classes\{AllFunctionalities, Pusher, PushNotificationClass};
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
            $postNo = \cleanSession((string)($_GET['postNo'] ?? $_POST['post_no'] ?? ''));
            $userId = \cleanSession((string)($_SESSION['id'] ?? ''));
            $reactionType = \cleanSession((string)($_GET['reaction'] ?? $_POST['reaction'] ?? 'love'));
            if (!in_array($reactionType, ['like', 'love', 'haha', 'shock', 'sad', 'celebrate'], true)) {
                $reactionType = 'love';
            }

            if (!$postNo || !$userId) {
                msgException(400, "Invalid parameters for liking post");
            }

            $pdo = parent::connect2();
            
            // Check if user already reacted
            $stmt = $pdo->prepare("SELECT id, reaction_type FROM post_reactions WHERE post_no = :postNo AND user_id = :userId");
            $stmt->execute(['postNo' => $postNo, 'userId' => $userId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['reaction_type'] === $reactionType) {
                    // User already gave this reaction, so toggle it off
                    $delStmt = $pdo->prepare("DELETE FROM post_reactions WHERE id = :id");
                    $delStmt->execute(['id' => $existing['id']]);
                    // Decrement count
                    $updStmt = $pdo->prepare("UPDATE post SET post_likes = GREATEST(post_likes - 1, 0), likes_updated_at = NOW() WHERE post_no = :postNo");
                    $updStmt->execute(['postNo' => $postNo]);
                } else {
                    // User switched reaction (e.g. from like to sad, or vice versa)
                    $updReactStmt = $pdo->prepare("UPDATE post_reactions SET reaction_type = :reactionType WHERE id = :id");
                    $updReactStmt->execute(['reactionType' => $reactionType, 'id' => $existing['id']]);
                }
            } else {
                // Insert new reaction
                $insStmt = $pdo->prepare("INSERT INTO post_reactions (post_no, user_id, reaction_type) VALUES (:postNo, :userId, :reactionType)");
                $insStmt->execute(['postNo' => $postNo, 'userId' => $userId, 'reactionType' => $reactionType]);
                // Increment count
                $updStmt = $pdo->prepare("UPDATE post SET post_likes = post_likes + 1, likes_updated_at = NOW() WHERE post_no = :postNo");
                $updStmt->execute(['postNo' => $postNo]);

                // Notify post author about the new reaction
                try {
                    $postRow = Post::postByNo($postNo);
                    $postAuthorId = (string)($postRow['id'] ?? '');
                    if ($postAuthorId !== '' && $postAuthorId !== $userId) {
                        $likerName = trim(($_SESSION['fName'] ?? '') . ' ' . ($_SESSION['lName'] ?? ''));
                        if ($likerName === '') {
                            $likerName = 'A family member';
                        }
                        $postUrl = '/profilePage?post_no=' . (int)$postNo . '#post' . (int)$postNo;
                        $notifMsg = "{$likerName} reacted to your post";

                        $nStmt = $pdo->prepare("
                            INSERT INTO notification 
                            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                            VALUES (?, ?, ?, 'Post Reaction', 'Post', ?, ?, 'new', NOW())
                        ");
                        $nStmt->execute([
                            $userId,
                            $postAuthorId,
                            $likerName,
                            $notifMsg,
                            $postUrl
                        ]);

                        $reactionEmoji = match($reactionType) {
                            'like' => '👍',
                            'love' => '❤️',
                            'haha' => '😂',
                            'shock' => '😮',
                            'sad' => '😢',
                            'celebrate' => '✨',
                            default => '❤️'
                        };

                        PushNotificationClass::sendPushNotification(
                            userId: $postAuthorId,
                            message: $notifMsg,
                            url: $postUrl,
                            title: $reactionEmoji . ' Post Reaction',
                            tag: 'post-like-' . $postNo
                        );
                    }
                } catch (\Throwable $likeNotifEx) {
                    error_log('[PostLikeController notif warning] ' . $likeNotifEx->getMessage());
                }
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

            // Group the broadcast by the post's family so each like-count update
            // only reaches that family's private channel.
            $byFamily = [];

            if (is_array($updatedLikes) && !empty($updatedLikes)) {
                foreach ($updatedLikes as $postLikes) {
                    $postNo = $postLikes['post_no'];
                    $famCode = (string) ($postLikes['postFamCode'] ?? $postLikes['famCode'] ?? '');
                    if ($famCode === '') {
                        continue;
                    }

                    $byFamily[$famCode][] = [
                        'post_no' => $postNo,
                        'origin' => getenv("APP_URL2") ?: 'default_value', // Set a default value if not set
                        'likeCounter' => $postLikes['post_likes'],
                        'likeHtmlId' => "likeCounter$postNo",
                    ];
                }

                foreach ($byFamily as $famCode => $response) {
                    try {
                        Pusher::broadcastToFamily((string) $famCode, 'like-event', $response);
                    } catch (\Exception $e) {
                        showError($e); // Handle broadcasting error
                    }
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
