<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\CommentReactionController;
use App\controller\members\PostLikeController;
use App\controller\members\ProfilePage;
use Tests\Support\SocialFeedTestCase;

/**
 * Backend coverage for the social feed:
 *  - ProfilePage::post()            — the "empty post" validation guard
 *  - PostLikeController::postLikes() — the like/unlike toggle against post_reactions + post.post_likes
 *  - CommentReactionController::addReaction() — its input / ownership guards
 *
 * The happy path of ProfilePage::post() (image upload, poll creation, email +
 * Pusher fan-out) and the "new reaction" branch of addReaction() are left to
 * the Cypress E2E suite / follow-up work — the latter currently writes a
 * non-existent `reacted_at` column, tracked separately.
 */
final class SocialFeedTest extends SocialFeedTestCase
{
    // ---- ProfilePage::post -------------------------------------------------

    public function test_post_rejects_an_empty_submission(): void
    {
        $_POST = ['postMessage' => '   '];

        $controller = $this->makeWithoutConstructor(ProfilePage::class);
        $response = $this->captureLastJson(fn () => $controller->post());

        $this->assertSame('error', $response['status'] ?? null);
        $this->assertSame(422, (int) ($response['code'] ?? 0));
        $this->assertStringContainsStringIgnoringCase('add some text', (string) ($response['message'] ?? ''));
    }

    // ---- PostLikeController::postLikes ----------------------------------

    public function test_liking_a_post_adds_a_reaction_row_and_increments_the_counter(): void
    {
        $postNo = $this->seedPost('like me');

        $_GET['postNo'] = (string) $postNo;

        $output = $this->captureOutput(fn () => PostLikeController::postLikes());

        $this->assertStringContainsString('success', $output);
        $this->assertSame(1, $this->postReactionCount($postNo, $this->authorId));
        $this->assertSame(1, $this->postLikes($postNo));
    }

    public function test_liking_a_post_twice_toggles_the_like_off(): void
    {
        $postNo = $this->seedPost('toggle me');
        $_GET['postNo'] = (string) $postNo;

        $this->captureOutput(fn () => PostLikeController::postLikes()); // like
        $this->captureOutput(fn () => PostLikeController::postLikes()); // unlike

        $this->assertSame(0, $this->postReactionCount($postNo, $this->authorId));
        $this->assertSame(0, $this->postLikes($postNo), 'Counter returns to zero and never goes negative.');
    }

    public function test_post_like_rejects_a_request_with_no_post_number(): void
    {
        $_GET = [];

        $response = $this->captureLastJson(fn () => PostLikeController::postLikes());

        $this->assertSame('error', $response['status'] ?? null);
    }

    // ---- CommentReactionController::addReaction ------------------------

    public function test_add_reaction_rejects_missing_parameters(): void
    {
        $_POST = ['reaction' => 'like']; // no comment_no

        $response = $this->captureLastJson(fn () => CommentReactionController::addReaction());

        $this->assertSame('error', $response['status'] ?? null);
        $this->assertSame('Missing required parameters.', $response['message'] ?? null);
    }

    public function test_add_reaction_rejects_an_unknown_reaction_type(): void
    {
        $_POST = ['comment_no' => '123', 'reaction' => 'thumbsdown'];

        $response = $this->captureLastJson(fn () => CommentReactionController::addReaction());

        $this->assertSame('error', $response['status'] ?? null);
        $this->assertSame('Invalid reaction type.', $response['message'] ?? null);
    }

    public function test_add_reaction_refuses_a_comment_from_another_family(): void
    {
        $foreignPost = $this->seedPost('theirs', famCode: 'PHPUNIT_OTHERFAM_' . bin2hex(random_bytes(3)));
        $foreignComment = $this->seedComment($foreignPost, 'their comment');

        $_POST = ['comment_no' => (string) $foreignComment, 'reaction' => 'like'];

        $response = $this->captureLastJson(fn () => CommentReactionController::addReaction());

        $this->assertSame('error', $response['status'] ?? null);
        $this->assertSame('Comment not found in your family.', $response['message'] ?? null);
        $this->assertSame(0, $this->commentReactionCount($foreignComment));
    }

    public function test_add_reaction_inserts_a_new_reaction_on_a_family_comment(): void
    {
        $postNo = $this->seedPost('ours');
        $commentNo = $this->seedComment($postNo, 'our comment');

        $_POST = ['comment_no' => (string) $commentNo, 'reaction' => 'love'];
        $response = $this->captureLastJson(fn () => CommentReactionController::addReaction());

        $this->assertSame(1, $this->commentReactionCount($commentNo));
        $this->assertSame('love', $this->reactionLabel($commentNo, $this->authorId));
        $this->assertSame('success', $response['status'] ?? null);
        $this->assertSame('added', $response['message']['action'] ?? null);
        $this->assertSame(1, $response['message']['counts']['counts']['totalReactions'] ?? null);
    }

    public function test_add_reaction_replaces_an_existing_reaction_with_a_different_one(): void
    {
        $postNo = $this->seedPost('ours');
        $commentNo = $this->seedComment($postNo, 'our comment');

        $_POST = ['comment_no' => (string) $commentNo, 'reaction' => 'like'];
        $res1 = $this->captureLastJson(fn () => CommentReactionController::addReaction());
        $this->assertSame('added', $res1['message']['action'] ?? null);

        $_POST = ['comment_no' => (string) $commentNo, 'reaction' => 'angry'];
        $res2 = $this->captureLastJson(fn () => CommentReactionController::addReaction());
        $this->assertSame('updated', $res2['message']['action'] ?? null);

        $this->assertSame(1, $this->commentReactionCount($commentNo), 'Still one row — the reaction was updated, not duplicated.');
        $this->assertSame('angry', $this->reactionLabel($commentNo, $this->authorId));
    }

    public function test_add_reaction_toggles_the_same_reaction_off(): void
    {
        $postNo = $this->seedPost('ours');
        $commentNo = $this->seedComment($postNo, 'our comment');

        $_POST = ['comment_no' => (string) $commentNo, 'reaction' => 'like'];
        $res1 = $this->captureLastJson(fn () => CommentReactionController::addReaction()); // react
        $this->assertSame(1, $this->commentReactionCount($commentNo));
        $this->assertSame('added', $res1['message']['action'] ?? null);

        $res2 = $this->captureLastJson(fn () => CommentReactionController::addReaction()); // same emoji again → un-react
        $this->assertSame('removed', $res2['message']['action'] ?? null);

        $this->assertSame(0, $this->commentReactionCount($commentNo), 'Re-sending the same reaction removes it.');
    }

    public function test_post_comment_returns_full_comment_data_with_epoch_post_time(): void
    {
        $postNo = $this->seedPost('Testing comment submission payload');
        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
        $_POST = [
            'post_no' => (string) $postNo,
            'comment' => 'Instant comment from family member',
        ];

        $controller = $this->makeWithoutConstructor(ProfilePage::class);
        $response = $this->captureLastJson(fn () => $controller->postComment());

        $this->assertSame('success', $response['status'] ?? null);
        $payload = $response['message'] ?? null;
        $this->assertIsArray($payload);
        $this->assertGreaterThan(0, $payload['comment_no'] ?? 0);
        $this->assertSame((string) $postNo, $payload['post_no'] ?? null);
        $this->assertSame($this->authorId, $payload['id'] ?? null);
        $this->assertSame('Feed Tester', $payload['fullName'] ?? null);
        $this->assertSame('Instant comment from family member', $payload['comment'] ?? null);

        // Verify post_time is a valid numeric string of epoch milliseconds (at least 13 digits)
        $this->assertNotEmpty($payload['post_time'] ?? null);
        $this->assertMatchesRegularExpression('/^\d{13,}$/', (string) $payload['post_time']);
        $this->assertSame($payload['post_time'], $payload['comment_time']);

        // Verify the comment row is persisted in the database
        $stmt = $this->pdo->prepare("SELECT comment_no, post_no, post_time, comment FROM comment WHERE comment_no = ?");
        $stmt->execute([(int) $payload['comment_no']]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
        $this->assertSame((string) $postNo, (string) $row['post_no']);
        $this->assertSame($payload['post_time'], (string) $row['post_time']);

        // Clean up
        $delStmt = $this->pdo->prepare("DELETE FROM comment WHERE comment_no = ?");
        $delStmt->execute([(int) $payload['comment_no']]);
    }

    public function test_post_comment_reply_is_stored_under_the_top_level_comment(): void
    {
        $postNo = $this->seedPost('Reply threading');
        $top = $this->seedComment($postNo, 'top level', 'OTHER_PARENT');
        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
        $_POST = ['post_no' => (string) $postNo, 'comment' => 'a reply', 'parent_comment_no' => (string) $top];

        $controller = $this->makeWithoutConstructor(ProfilePage::class);
        $response = $this->captureLastJson(fn () => $controller->postComment());

        $payload = $response['message'] ?? null;
        $this->assertSame('success', $response['status'] ?? null);
        $this->assertSame($top, $payload['parent_comment_no'] ?? null);

        $stmt = $this->pdo->prepare('SELECT parent_comment_no FROM comment WHERE comment_no = ?');
        $stmt->execute([(int) $payload['comment_no']]);
        $this->assertSame($top, (int) $stmt->fetchColumn());

        $this->pdo->prepare('DELETE FROM notification WHERE sender_id = ?')->execute([$this->authorId]);
        $commentNo = (int) $payload['comment_no'];
        $this->pdo->prepare('DELETE FROM comment WHERE comment_no = ?')->execute([$commentNo]);
    }

    public function test_post_comment_reply_to_a_comment_on_another_post_is_rejected_and_saves_nothing(): void
    {
        $postA = $this->seedPost('A');
        $postB = $this->seedPost('B');
        $onA = $this->seedComment($postA);
        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
        $_POST = ['post_no' => (string) $postB, 'comment' => 'cross-post reply', 'parent_comment_no' => (string) $onA];

        $controller = $this->makeWithoutConstructor(ProfilePage::class);
        $response = $this->captureLastJson(fn () => $controller->postComment());

        $this->assertNotSame('success', $response['status'] ?? null);
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM comment WHERE post_no = ?');
        $count->execute([(string) $postB]);
        $this->assertSame(0, (int) $count->fetchColumn(), 'a rejected reply must not leave an orphan comment');
    }
}
