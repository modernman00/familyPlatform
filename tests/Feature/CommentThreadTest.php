<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\CommentThreadService;
use PDO;
use Src\Exceptions\ValidationException;
use Tests\Support\SocialFeedTestCase;

/**
 * One-level reply threading + reply/mention notifications, against the dev DB.
 */
final class CommentThreadTest extends SocialFeedTestCase
{
    /** @var list<string> */
    private array $notifySenders = [];

    protected function tearDown(): void
    {
        foreach ($this->notifySenders as $sender) {
            $this->pdo->prepare('DELETE FROM notification WHERE sender_id = ?')->execute([$sender]);
        }
        parent::tearDown();
    }

    public function test_reply_to_top_level_comment_hangs_under_it(): void
    {
        $post = $this->seedPost();
        $top = $this->seedComment($post, 'top', 'OTHER_1');

        $r = CommentThreadService::resolveParent($this->pdo, $top, (string) $post);

        $this->assertSame($top, $r['root']);
        $this->assertSame('OTHER_1', $r['parentAuthorId']);
    }

    public function test_reply_to_a_reply_is_flattened_to_the_top_level_comment(): void
    {
        $post = $this->seedPost();
        $top = $this->seedComment($post, 'top');
        $reply = $this->seedComment($post, 'reply', 'OTHER_2');
        $this->pdo->prepare('UPDATE comment SET parent_comment_no = ? WHERE comment_no = ?')->execute([$top, $reply]);

        $r = CommentThreadService::resolveParent($this->pdo, $reply, (string) $post);

        $this->assertSame($top, $r['root'], 'threads never nest deeper than one level');
        $this->assertSame('OTHER_2', $r['parentAuthorId'], 'the person replied to is still notified');
    }

    public function test_rejects_a_parent_on_a_different_post(): void
    {
        $postA = $this->seedPost();
        $postB = $this->seedPost();
        $onA = $this->seedComment($postA);

        $this->expectException(ValidationException::class);
        CommentThreadService::resolveParent($this->pdo, $onA, (string) $postB);
    }

    public function test_rejects_a_deleted_or_missing_parent(): void
    {
        $post = $this->seedPost();
        $gone = $this->seedComment($post);
        $this->pdo->prepare('UPDATE comment SET date_deleted = NOW() WHERE comment_no = ?')->execute([$gone]);

        try {
            CommentThreadService::resolveParent($this->pdo, $gone, (string) $post);
            $this->fail('deleted parent accepted');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(ValidationException::class);
        CommentThreadService::resolveParent($this->pdo, 2147483000, (string) $post);
    }

    public function test_notifies_parent_author_and_mentions_once_each_never_the_author(): void
    {
        $this->notifySenders[] = $this->authorId;

        $notified = CommentThreadService::notify(
            $this->pdo,
            $this->authorId,
            'Feed Tester',
            '999999',
            ['M_1', $this->authorId, 'P_1'],
            'P_1'
        );

        sort($notified);
        $this->assertSame(['M_1', 'P_1'], $notified);

        $rows = $this->pdo->prepare('SELECT receiver_id, notification_content FROM notification WHERE sender_id = ? ORDER BY receiver_id');
        $rows->execute([$this->authorId]);
        $byReceiver = array_column($rows->fetchAll(PDO::FETCH_ASSOC), 'notification_content', 'receiver_id');

        $this->assertSame('Feed Tester mentioned you in a comment', $byReceiver['P_1'], 'mention wins over reply for the same person');
        $this->assertSame('Feed Tester mentioned you in a comment', $byReceiver['M_1']);
        $this->assertCount(2, $byReceiver);
    }

    public function test_plain_reply_notifies_only_the_parent_author(): void
    {
        $this->notifySenders[] = $this->authorId;

        $notified = CommentThreadService::notify($this->pdo, $this->authorId, 'Feed Tester', '999999', [], 'P_9');

        $this->assertSame(['P_9'], $notified);
        $row = $this->pdo->prepare('SELECT notification_content FROM notification WHERE sender_id = ?');
        $row->execute([$this->authorId]);
        $this->assertSame('Feed Tester replied to your comment', $row->fetchColumn());
    }

    public function test_replying_to_yourself_notifies_nobody(): void
    {
        $this->assertSame([], CommentThreadService::notify($this->pdo, $this->authorId, 'Feed Tester', '1', [], $this->authorId));
    }

    /** Chaos: a database outage while notifying must not throw — the comment is already saved. */
    public function test_notify_survives_a_database_failure(): void
    {
        $down = new class extends PDO {
            public function __construct()
            {
            }

            public function prepare(string $query, array $options = []): \PDOStatement|false
            {
                throw new \PDOException('simulated outage');
            }
        };

        $this->assertSame([], CommentThreadService::notify($down, 'A', 'A B', '1', ['X'], 'Y'));
    }
}
