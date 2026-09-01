<?php

declare(strict_types=1);

namespace Tests\Support;

use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Src\Db;

/**
 * Base for the social-feed controllers (ProfilePage post/comment,
 * PostLikeController, CommentReactionController), run against the real dev
 * database. Controllers with an auth-checking constructor are built via
 * newInstanceWithoutConstructor(); the request identity they read out of
 * $_SESSION is set by hand in setUp()/each test.
 *
 * Fixtures are namespaced by a per-test author id and family code and swept
 * in tearDown() so nothing leaks into the shared dev DB.
 */
abstract class SocialFeedTestCase extends TestCase
{
    protected PDO $pdo;
    protected string $authorId;
    protected string $famCode;

    /** @var list<int> post_no values created via seedPost() */
    private array $postNos = [];
    /** @var list<int> comment_no values created via seedComment() */
    private array $commentNos = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->authorId = 'PU_' . bin2hex(random_bytes(6));
        $this->famCode = 'PHPUNIT_FEED_' . bin2hex(random_bytes(4));

        $_SESSION = [
            'id' => $this->authorId,
            'famCode' => $this->famCode,
            'fName' => 'Feed',
            'lName' => 'Tester',
        ];
        $_POST = [];
        $_GET = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_X_XSRF_TOKEN'] = '';
    }

    protected function tearDown(): void
    {
        foreach ($this->commentNos as $no) {
            $this->pdo->prepare('DELETE FROM comment_reactions WHERE comment_no = ?')->execute([$no]);
            $this->pdo->prepare('DELETE FROM reaction_counts WHERE comment_no = ?')->execute([$no]);
            $this->pdo->prepare('DELETE FROM comment WHERE comment_no = ?')->execute([$no]);
        }
        foreach ($this->postNos as $no) {
            $this->pdo->prepare('DELETE FROM post_reactions WHERE post_no = ?')->execute([$no]);
            $this->pdo->prepare('DELETE FROM comment WHERE post_no = ?')->execute([(string) $no]);
            $this->pdo->prepare('DELETE FROM post WHERE post_no = ?')->execute([$no]);
        }
        parent::tearDown();
    }

    /** @param class-string $class */
    protected function makeWithoutConstructor(string $class): object
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    protected function seedPost(string $message = 'hello world', ?string $famCode = null, ?string $authorId = null): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_likes, post_time, post_status)
             VALUES (?, ?, ?, ?, 0, ?, 'published')"
        );
        $stmt->execute([
            $authorId ?? $this->authorId,
            'Feed Tester',
            $message,
            $famCode ?? $this->famCode,
            (string) (int) (microtime(true) * 1000),
        ]);
        $no = (int) $this->pdo->lastInsertId();
        $this->postNos[] = $no;
        return $no;
    }

    protected function seedComment(int $postNo, string $text = 'nice one', ?string $authorId = null): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO comment (id, post_no, fullName, comment, post_time, comment_status)
             VALUES (?, ?, ?, ?, ?, 'published')"
        );
        $stmt->execute([
            $authorId ?? $this->authorId,
            (string) $postNo,
            'Feed Tester',
            $text,
            (string) (int) (microtime(true) * 1000),
        ]);
        $no = (int) $this->pdo->lastInsertId();
        $this->commentNos[] = $no;
        return $no;
    }

    protected function postReactionCount(int $postNo, string $userId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM post_reactions WHERE post_no = ? AND user_id = ? AND reaction_type = 'like'"
        );
        $stmt->execute([$postNo, $userId]);
        return (int) $stmt->fetchColumn();
    }

    protected function postLikes(int $postNo): int
    {
        $stmt = $this->pdo->prepare('SELECT post_likes FROM post WHERE post_no = ?');
        $stmt->execute([$postNo]);
        return (int) $stmt->fetchColumn();
    }

    protected function commentReactionCount(int $commentNo): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM comment_reactions WHERE comment_no = ?');
        $stmt->execute([$commentNo]);
        return (int) $stmt->fetchColumn();
    }

    /** Raw echoed output of $callback (tolerates non-JSON noise from real Pusher/SMTP calls). */
    protected function captureOutput(callable $callback): string
    {
        ob_start();
        try {
            $callback();
        } finally {
            $output = ob_get_clean();
        }
        return $output === false ? '' : $output;
    }

    /**
     * Decodes the LAST top-level JSON object echoed by $callback. The feed
     * controllers occasionally emit a stray error blob from a best-effort
     * Pusher/broadcast call before their real response; the meaningful
     * payload is always the final one.
     *
     * @return array<string, mixed>
     */
    protected function captureLastJson(callable $callback): array
    {
        $output = trim($this->captureOutput($callback));
        $this->assertNotSame('', $output, 'Expected some output.');

        // Split concatenated top-level JSON objects: "}{"  ->  "}\n{"
        $chunks = preg_split('/(?<=})\s*(?={)/', $output) ?: [$output];
        $last = (string) end($chunks);

        $decoded = json_decode($last, true);
        $this->assertIsArray($decoded, "Expected JSON, got: $output");
        return $decoded;
    }
}
