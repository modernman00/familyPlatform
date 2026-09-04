<?php
declare(strict_types=1);

namespace App\controller\members;

use App\classes\{PushNotificationClass, Pusher, VideoParser};

use App\model\{
    Post,
    AllMembersData,
};

use Src\functionality\SendEmailFunctionality as SendMail;
use Src\{CheckToken, Update};
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;
use Src\Utility;
use Src\functionality\SignIn;

final class PostMessage
{
    /**
     * Narrows a checkInput()-style result (which may come back as array|string|null)
     * down to the int|string identifier the model layer requires.
     */
    private static function asIdentifier(mixed $value): int|string
    {
        if (is_int($value) || is_string($value)) {
            return $value;
        }

        throw new ValidationException('Invalid identifier supplied.');
    }

    /**
     * Index
     *
     * Gets all the post on the profile page
     *
     * Sets the count of all the posts in the session variable
     *
     * @return void
     */
    public static function index()
    {
        try {
            $VerifyJWT = SignIn::verify('users');
            $id = \cleanSession((string)$VerifyJWT['id']);
            if ($id === null) {
                throw new ForbiddenException('Unable to verify user identity.');
            }
            $id = (string) $id;
            $famCodes = $_SESSION['famCodes'] ?? [\cleanSession((string)($_SESSION['famCode'] ?? ''))];

            // Get pagination parameters
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
            $offset = ($page - 1) * $limit;

            // 1. Get visible posts with pagination across multiple families via unified FeedRepository
            $feedRepo = new \App\repository\FeedRepository();
            $posts = $feedRepo->getFeedPostsAsArray(
                $id,
                $famCodes,
                $limit,
                $offset
            );

            // Get total count for pagination metadata
            $total = $feedRepo->countFeedPosts($id, $famCodes);
            $lastPage = (int)ceil($total / $limit);




            if (!$posts) {
                msgSuccess(
                    code: 200,
                    msg: [
                        'message' => [],
                        'pagination' => [
                            'total' => $total,
                            'per_page' => $limit,
                            'current_page' => $page,
                            'last_page' => $lastPage
                        ]
                    ]
                );
                return;
            }

            $count = count($posts);


            // 2. Loop through each post: MUST use the reference operator (&) to modify the original array.
            foreach ($posts as &$post) {

                // 3. Get all comments for THIS post
                $comments = AllMembersData::commentProfilePicByPostNo($post['post_no']);

                if (!$comments) {
                    continue; // No comments for this post, move to the next post
                }


                // 4. Loop through each comment: MUST use the reference operator (&) to modify the original array.
                foreach ($comments as &$comment) {
                    //  // 5. Get reactions for THIS comment
                    // 5. Get reactions for THIS comment
                    $commentNo = $comment['comment_no'];
                    $reactions = CommentReactionController::fetchReactions($commentNo, false);

                    // 6. Attach the reactions directly to their comment
                    // We use $comment (the reference) to attach reactions to this specific comment array.
                    $comment['reactions'] = $reactions ? $reactions : [];
                    // }                  
                }
                unset($comment); // Good practice to unset the inner reference

                // 7. Attach the comments (which now have reactions) to the CURRENT post array
                // We use $post (the reference) to modify the specific element in $posts.
                $post['comments'] = $comments;
            }

            // 8. Unset references to prevent bugs
            unset($post);

            $_SESSION['POST_COUNT'] = $count;
            msgSuccess(
                code: 200,
                msg: [
                    'message' => $posts,
                    'pagination' => [
                        'total' => $total,
                        'per_page' => $limit,
                        'current_page' => $page,
                        'last_page' => $lastPage
                    ]
                ]
            );
        } catch (\Throwable $th) {
            \showError($th);
        }
    }

    // update post and comment as published 
    public static function updatePostAsPublished($postNo): void
    {
        try {
            Post::updatePostByStatusAsPublished($postNo);
            msgSuccess(200, "post updated successfully");
        } catch (\Throwable $th) {
            \showError($th);
        }
    }

    public static function updateCommentAsPublished($commentNo): void
    {
        try {
            Post::updateCommentByStatusAsPublished($commentNo);

            msgSuccess(200, "comment updated successfully");
        } catch (\Throwable $th) {
            \showError($th);
        }
    }


    // build for Pusher library
    /**
     * @return array|null
     *
     * @psalm-return list{0?: mixed,...}|null
     */
    private static function fetchNewMsg(callable $fetchFunction, ?array $params = null)
    {
        try {
            $items = (isset($params)) ? $fetchFunction(...$params) : $fetchFunction();
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

    /**
     * Fetches unpublished posts for the current user's family code and broadcasts them via Pusher.
     * 
     * This function retrieves new posts that have not yet been published, based on the user's 
     * family code and member ID. The posts are then broadcasted to the 'posts-channel' with 
     * the event name 'new-post' using the Pusher library.
     * 
     * @return void
     */

    public static function getNewPostPusher()
    {
        $id = \cleanSession((string)$_SESSION['id']);
        $famCode = checkInput($_SESSION['famCode']);
        $newPost = self::fetchNewMsg(
            fetchFunction: [AllMembersData::class, 'getUnpublishedPostByFamCode'],
            params: [$famCode, $id]
        );

        if (!empty($newPost)) {
            foreach ($newPost as $post) {
                Post::updatePostByStatusAsPublished($post['post_no']);
            }
            
            try {
                Pusher::broadcastToFamily(is_string($famCode) ? $famCode : '', 'new-post', $newPost);
            } catch (\Throwable $th) {
                error_log("Pusher post broadcast failed: " . $th->getMessage());
            }
        }
    }

    /**
     * Fetches unpublished comments and broadcasts them via Pusher.
     * 
     * This function retrieves new comments that have not yet been published
     * and broadcasts them to the 'comments-channel' with the event name 
     * 'new-comment' using the Pusher library. Additionally, it sends a push notification
     * to all members with the same family code as the comment author.
     * 
     * @return void
     */
    public static function getNewCommentPusher()
    {
        $newComment = self::fetchNewMsg(fetchFunction: [Post::class, 'getUnpublishedComment']);

        if (!empty($newComment)) {
            foreach ($newComment as $comment) {
                Post::updateCommentByStatusAsPublished($comment['comment_no']);
            }

            // Authoritative family scope for the comment's post — drives both the
            // private Pusher channel and the downstream push notification.
            $data = Post::postByNo($newComment[0]['post_no']);
            $famCode = checkInput($data['postFamCode'] ?? '');
            $famCode = is_string($famCode) ? $famCode : '';

            try {
                Pusher::broadcastToFamily($famCode, 'new-comment', $newComment);
            } catch (\Throwable $th) {
                error_log("Pusher comment broadcast failed: " . $th->getMessage());
            }

            // send push notification to all members with the same family code
            $id = \checkInput($data['id']);

            $results = AllMembersData::AllMembersEmailByFamCode($famCode, $id);

            $url = self::buildPostUrl($newComment[0]['post_no']);

            self::notifyMembersByPushNotification(
                results: $results,
                postId: $newComment[0]['id'],
                postOriginName: $newComment[0]['fullName'],
                url: $url,
                notificationMsg: "{$newComment[0]['fullName']} posted a new comment"
            );
        }
    }


    // delete comment
    public static function deleteComment(int|string $commentNo): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'];
            $commentNo = self::asIdentifier(Utility::checkInput($commentNo));

            // select the id of the comment author
            $commentAuthorId = Post::commentByNo($commentNo)['id'];
            $commentPostNo = self::asIdentifier(Post::commentByNo($commentNo)['post_no']);

            // select the post (author + family scope for the Pusher channel)
            $post = Post::postByNo($commentPostNo);
            $postAuthorId = $post['id'] ?? null;

            // delete if the author of the comment or the author of the post
            if ($userId === $commentAuthorId || $userId === $postAuthorId) {

                (new Update('comment'))->updateTable(
                    column: 'date_deleted',
                    columnAnswer: date('Y-m-d H:i:s'),
                    identifier: 'comment_no',
                    identifierAnswer: $commentNo
                );

                // trigger pusher to update all the UI elements for the deleted comment
                Pusher::broadcastToFamily((string) ($post['postFamCode'] ?? ''), 'delete-comment', ['commentNo' => $commentNo, 'postNo' => $commentPostNo]);

                msgSuccess(200, "comment deleted successfully");
            } else {
                throw new ForbiddenException('You can only delete your own comments.');
            }
        } catch (\Throwable $th) {
            \showError($th);
        }
    }

    /**
     * Edits the text of a comment. Same ownership rule as deleteComment:
     * the comment's author, or the author of the post it's on.
     */
    public static function updateComment(int|string $commentNo): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'];
            $commentNo = self::asIdentifier(Utility::checkInput($commentNo));

            $commentAuthorId = Post::commentByNo($commentNo)['id'];
            $commentPostNo = self::asIdentifier(Post::commentByNo($commentNo)['post_no']);
            $post = Post::postByNo($commentPostNo);
            $postAuthorId = $post['id'] ?? null;

            if ($userId !== $commentAuthorId && $userId !== $postAuthorId) {
                throw new ForbiddenException('You can only edit your own comments.');
            }

            // PHP only populates $_POST for the literal POST verb — PUT bodies
            // have to be read manually, so the frontend sends JSON for edits.
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput !== false ? $rawInput : '', true) ?? [];
            $cleanComment = checkInput($input['comment'] ?? null) ?? '';

            (new Update('comment'))->updateTable(
                column: 'comment',
                columnAnswer: $cleanComment,
                identifier: 'comment_no',
                identifierAnswer: $commentNo
            );

            Pusher::broadcastToFamily((string) ($post['postFamCode'] ?? ''), 'update-comment', [
                'commentNo' => $commentNo,
                'postNo' => $commentPostNo,
                'comment' => $cleanComment,
            ]);

            msgSuccess(200, "comment updated successfully");
        } catch (\Throwable $th) {
            \showError($th);
        }
    }

    /**
     * Soft-deletes a post. Author-only.
     */
    public static function deletePost(int|string $postNo): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'];
            $postNo = self::asIdentifier(Utility::checkInput($postNo));
            $post = Post::postByNo($postNo);
            $postAuthorId = $post['id'] ?? null;

            if ($userId !== $postAuthorId) {
                throw new ForbiddenException('You can only delete your own posts.');
            }

            (new Update('post'))->updateTable(
                column: 'date_deleted',
                columnAnswer: date('Y-m-d H:i:s'),
                identifier: 'post_no',
                identifierAnswer: $postNo
            );

            Pusher::broadcastToFamily((string) ($post['postFamCode'] ?? ''), 'delete-post', ['postNo' => $postNo]);

            msgSuccess(200, "post deleted successfully");
        } catch (\Throwable $th) {
            \showError($th);
        }
    }

    /**
     * Edits a post's text. Text only — images/poll are unchanged.
     * Author-only.
     */
    public static function updatePost(int|string $postNo): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'];
            $postNo = self::asIdentifier(Utility::checkInput($postNo));
            $post = Post::postByNo($postNo);
            $postAuthorId = $post['id'] ?? null;

            if ($userId !== $postAuthorId) {
                throw new ForbiddenException('You can only edit your own posts.');
            }

            // PHP only populates $_POST for the literal POST verb — PUT bodies
            // have to be read manually, so the frontend sends JSON for edits.
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput !== false ? $rawInput : '', true) ?? [];
            $cleanMessage = checkInput($input['postMessage'] ?? null) ?? '';

            (new Update('post'))->updateTable(
                column: 'postMessage',
                columnAnswer: $cleanMessage,
                identifier: 'post_no',
                identifierAnswer: $postNo
            );

            Pusher::broadcastToFamily((string) ($post['postFamCode'] ?? ''), 'update-post', [
                'postNo' => $postNo,
                'postMessage' => $cleanMessage,
            ]);

            msgSuccess(200, "post updated successfully");
        } catch (\Throwable $th) {
            \showError($th);
        }
    }

    /**
     * Updates a comment's status as published.
     * 
     * @param int $commentNo The comment number of the comment to update.
     * 
     * @return mixed The result of the database query.
     * 
     * @throws \Throwable If an error occurs during the database query.
     */
    public static function updateCommentByStatusAsPublished($commentNo)
    {
        $commentNo = cleanSession((string)$commentNo);

        return Post::updateCommentByStatusAsPublished($commentNo);
    }

    /**
     * Sends an email to all members with the same family code as the post.
     * The email is a notification that a new post has been published.
     * 
     * @param string|int|null $postNoOverride The post number (optional, for backend calls)
     * @return void
     * @throws \Throwable
     */
    public static function getNewPostAndEmail($postNoOverride = null)
    {
        try {

            $rawPostNo = $postNoOverride ?? $_GET['newPostNo'] ?? $_GET['newCommentNo'] ?? '';
            $postNo = cleanSession((string) $rawPostNo);
            
            if (empty($postNo)) {
                if ($postNoOverride === null) {
                    msgSuccess(200, "Skipped");
                }
                return;
            }

            $data = Post::postByNo($postNo);
            
            if ($data) {
                $postOriginName = is_string($data['fullName'] ?? null) ? $data['fullName'] : '';
                $rawId = checkInput($data['id']);
                $id = is_string($rawId) ? $rawId : '';
                $famCode = checkInput($data['postFamCode']);
                $famCode = is_string($famCode) ? $famCode : '';

                $results = AllMembersData::AllMembersEmailByFamCode($famCode, $id);
                $url = self::buildPostUrl($postNo);
                $rawPostMessage = is_string($data['postMessage'] ?? null) ? (string) $data['postMessage'] : '';

                // Extract video details (thumbnail, type, videoId) if present
                $videoData = VideoParser::extractVideoFromText($rawPostMessage);
                $cleanPostContent = VideoParser::cleanPostMessage($rawPostMessage, $videoData);

                // Extract post image attachment if present
                $postImage = null;
                if (!empty($data['post_img']) && is_string($data['post_img'])) {
                    $appUrl = rtrim((string) (getenv('APP_URL') ?: (getenv('MIX_APP_URL') ?: '')), '/');
                    $postImage = $appUrl . '/resources/images/post/' . ltrim($data['post_img'], '/');
                }

                // Send notifications to members by email
                self::notifyMembersByEmail(
                    results: $results,
                    postId: $id,
                    postOriginName: $postOriginName,
                    url: $url,
                    postContent: $cleanPostContent,
                    video: $videoData,
                    postImage: $postImage
                );

                $pushMsg = !empty($videoData)
                    ? "$postOriginName shared a video"
                    : "$postOriginName posted a new update";

                self::notifyMembersByPushNotification(
                    results: $results,
                    postId: $id,
                    postOriginName: $postOriginName,
                    url: $url,
                    notificationMsg: $pushMsg
                );
            }
            if ($postNoOverride === null) {
                msgSuccess(200, "Success");
            }
        } catch (\Throwable $th) {
            // When called as a background step from ProfilePage->post() (i.e. with
            // $postNoOverride set), the post itself already saved successfully — a
            // notification failure here (e.g. email/SMTP) must never turn that into
            // a failed response for the user, so just log it instead of calling
            // showError(), which would emit its own HTTP status/body.
            if ($postNoOverride === null) {
                showError($th);
            } else {
                error_log((string) $th);
            }
        }
    }

    // Helper function to construct post URL
    private static function buildPostUrl(int|string $postNo): string
    {
        $getUrl = getenv('MIX_APP_URL');
        return "$getUrl/profilePage?#post$postNo";
    }

    /**
     * Helper function to notify members by email.
     *
     * @param array<int, array<string, mixed>> $results
     * @param array{type: string, videoId: string|null, thumbnailUrl: string|null, originalUrl: string}|null $video
     */
    private static function notifyMembersByEmail(
        array $results,
        string $postId,
        string $postOriginName,
        string $url,
        string $postContent,
        ?array $video = null,
        ?string $postImage = null
    ): void {
        $getPostProfilePics = self::getProfilePicsForPostAndComment($postId);

        foreach ($results as $memberData) {
            $memberData['postOriginName'] = $postOriginName;
            $memberData['url'] = $url;
            $memberData['img'] = $getPostProfilePics;

            $data = [
                'email' => is_string($memberData['email'] ?? null) ? $memberData['email'] : '',
                'authorName' => $postOriginName,
                'url' => $url,
                'img' => $getPostProfilePics,
                'firstName' => is_string($memberData['firstName'] ?? null) ? $memberData['firstName'] : '',
                'postContent' => $postContent,
                'video' => $video,
                'postImage' => $postImage,
            ];

            // Send email to all members
            SendMail::email(
                "msg/customer/notifyNewPost",
                "$postOriginName posted a new update",
                $data,
                'member'
            );
        }
    }

    /**
     * Send push notifications to all members with the same family code as the post author.
     * 
     * @param array $results The array of member data from the database query.
     * @param string $postId The post ID.
     * @param string $postOriginName The name of the post author.
     * @param string $url The URL of the post.
     * 
     * @return void
     */
    private static function notifyMembersByPushNotification(array $results, string $postId, string $postOriginName, string $url, string $notificationMsg): void
    {
        $getPostProfilePics = self::getProfilePicsForPostAndComment($postId);

        foreach ($results as $memberData) {
            $memberData['postOriginName'] = $postOriginName;
            $memberData['url'] = $url;
            $memberData['img'] = $getPostProfilePics;
            // Send push notification to all members WITH THE FAMILY CODE
            PushNotificationClass::sendPushNotification(
                userId: $memberData['id'],
                message: $notificationMsg,
                url: $url
            );
        }
    }

    // HELPER FUNCTION TO GET THE PROFILE PICS FOR COMMENTS AND POST 
    private static function getProfilePicsForPostAndComment(string $postId): string|null
    {
        $result = Post::getProfilePics($postId);
        return $result[0]["img"];
    }
}
