<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\PostMessage;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class PostDeleteCsrfResilienceTest extends TestCase
{
    private PDO $pdo;
    private string $famCode = '';
    private string $authorId = '';
    private string $intruderId = '';
    /** @var list<int> */
    private array $createdPostNos = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $suffix = bin2hex(random_bytes(4));
        $this->famCode = 'FAM_DEL_' . strtoupper($suffix);
        $this->authorId = 'u_author_' . $suffix;
        $this->intruderId = 'u_intruder_' . $suffix;

        $stmt = $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
        $stmt->execute([$this->authorId, 'Ada', 'Olaogun', $this->famCode]);
        $stmt->execute([$this->intruderId, 'Mallory', 'Intruder', $this->famCode]);

        $this->resetGlobals();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdPostNos as $postNo) {
            $delComments = $this->pdo->prepare('DELETE FROM comment WHERE post_no = ?');
            $delComments->execute([(string) $postNo]);
            $delPost = $this->pdo->prepare('DELETE FROM post WHERE post_no = ?');
            $delPost->execute([$postNo]);
        }

        $delPersonal = $this->pdo->prepare('DELETE FROM personal WHERE id IN (?, ?)');
        $delPersonal->execute([$this->authorId, $this->intruderId]);

        $this->resetGlobals();
        parent::tearDown();
    }

    private function resetGlobals(): void
    {
        unset(
            $_SERVER['HTTP_X_CSRF_TOKEN'],
            $_SERVER['HTTP_X_XSRF_TOKEN'],
            $_SERVER['HTTP_CSRF_TOKEN'],
            $_COOKIE['XSRF-TOKEN']
        );
        $_POST = [];
        $_SESSION = [];
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    }

    private function createFixturePost(string $message): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO post (id, postFamCode, postMessage, fullName) VALUES (?, ?, ?, ?)');
        $stmt->execute([$this->authorId, $this->famCode, $message, 'Ada Olaogun']);
        $postNo = (int) $this->pdo->lastInsertId();
        $this->createdPostNos[] = $postNo;
        return $postNo;
    }

    private function isPostSoftDeleted(int $postNo): bool
    {
        $stmt = $this->pdo->prepare('SELECT date_deleted FROM post WHERE post_no = ? LIMIT 1');
        $stmt->execute([$postNo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) && !empty($row['date_deleted']);
    }

    public function test_author_deletes_post_after_session_token_eviction_when_valid_xsrf_cookie_and_header_match(): void
    {
        $postNo = $this->createFixturePost('Family memory after idle session');
        $validHexToken = bin2hex(random_bytes(32));

        // Simulate PHP session garbage collection evicting $_SESSION['token'] while session is rehydrated
        $_SESSION['id'] = $this->authorId;
        $_SESSION['famCode'] = $this->famCode;
        $_SESSION['token'] = '';
        $_COOKIE['XSRF-TOKEN'] = $validHexToken;
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $validHexToken;

        http_response_code(200);
        ob_start();
        PostMessage::deletePost($postNo);
        $rawOut = (string) ob_get_clean();

        $this->assertTrue($this->isPostSoftDeleted($postNo), 'Expected post to be soft-deleted. Output: ' . $rawOut);
        $this->assertSame($validHexToken, $_SESSION['token']);
    }

    public function test_cookie_only_csrf_attack_without_explicit_header_or_body_is_neutralised(): void
    {
        $postNo = $this->createFixturePost('Protected family memory');
        $validHexToken = bin2hex(random_bytes(32));

        $_SESSION['id'] = $this->authorId;
        $_SESSION['famCode'] = $this->famCode;
        $_SESSION['token'] = '';
        $_COOKIE['XSRF-TOKEN'] = $validHexToken;
        // Attacker relies solely on browser sending Cookie: XSRF-TOKEN without X-CSRF-TOKEN header/body

        http_response_code(200);
        ob_start();
        PostMessage::deletePost($postNo);
        $rawOut = (string) ob_get_clean();
        $decoded = json_decode($rawOut, true);

        $this->assertSame(401, http_response_code());
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status'] ?? null);
        $this->assertFalse($this->isPostSoftDeleted($postNo));
    }

    public function test_tampered_csrf_token_and_non_author_idor_are_rejected(): void
    {
        $postNo = $this->createFixturePost('Author-only heirloom post');
        $validHexToken = bin2hex(random_bytes(32));

        // 1. Tampered CSRF token -> 401
        $_SESSION['id'] = $this->authorId;
        $_SESSION['famCode'] = $this->famCode;
        $_SESSION['token'] = $validHexToken;
        $_SERVER['HTTP_X_CSRF_TOKEN'] = str_repeat('f', 64);

        http_response_code(200);
        ob_start();
        PostMessage::deletePost($postNo);
        $tamperedOut = (string) ob_get_clean();

        $this->assertSame(401, http_response_code(), 'Expected 401 for tampered CSRF: ' . $tamperedOut);
        $this->assertFalse($this->isPostSoftDeleted($postNo));

        // 2. Non-author IDOR with valid CSRF -> 403
        $_SESSION['id'] = $this->intruderId;
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $validHexToken;

        http_response_code(200);
        ob_start();
        PostMessage::deletePost($postNo);
        $idorOut = (string) ob_get_clean();

        $this->assertSame(403, http_response_code(), 'Expected 403 for IDOR delete: ' . $idorOut);
        $this->assertFalse($this->isPostSoftDeleted($postNo));
    }
}
