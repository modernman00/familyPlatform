<?php

declare(strict_types=1);

namespace Tests\Support;

use App\controller\members\OrganogramEditorController;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Src\Db;

/**
 * Base for tests that exercise OrganogramEditorController against the real dev
 * database. The controller's methods are instance methods and its constructor
 * calls BaseController::__construct() (JWT verification), so tests build it via
 * newInstanceWithoutConstructor() and set $_SESSION['famCode'] by hand — the
 * same "authenticate the request state directly" trick FamilyRequestTestCase
 * uses for its static controller.
 *
 * Every family_nodes / family_unions / family_node_children row a test creates
 * is tracked and deleted in tearDown() so the suite never leaves fixture data
 * behind in a database also used for local dev/QA.
 */
abstract class OrganogramTestCase extends TestCase
{
    protected PDO $pdo;

    /** Family code every node/union in a single test shares. */
    protected string $famCode;

    /** @var list<int> family_nodes.id values to delete in tearDown() */
    private array $nodeIds = [];

    /** @var list<int> family_unions.id values to delete in tearDown() */
    private array $unionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->famCode = 'PHPUNIT_TREE_' . bin2hex(random_bytes(4));

        $_SESSION = ['famCode' => $this->famCode];
        $_POST = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_X_XSRF_TOKEN'] = '';
    }

    protected function tearDown(): void
    {
        // children link rows first (FK-free but conceptually dependent), then
        // unions, then the nodes themselves.
        $this->pdo->prepare(
            'DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code = ?)'
        )->execute([$this->famCode]);
        $this->pdo->prepare('DELETE FROM family_unions WHERE family_code = ?')->execute([$this->famCode]);
        $this->pdo->prepare('DELETE FROM family_nodes WHERE family_code = ?')->execute([$this->famCode]);

        parent::tearDown();
    }

    protected function controller(): OrganogramEditorController
    {
        return (new ReflectionClass(OrganogramEditorController::class))->newInstanceWithoutConstructor();
    }

    /** Inserts a bare node in this test's family and returns its id. */
    protected function seedNode(
        string $firstName = 'Base',
        string $lastName = 'Person',
        string $gender = 'Male',
        int $generationLevel = 0,
        ?string $userId = null,
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$this->famCode, $userId, $firstName, $lastName, $gender, $generationLevel]);
        $id = (int) $this->pdo->lastInsertId();
        $this->nodeIds[] = $id;
        return $id;
    }

    /** Inserts a union between two nodes in this test's family and returns its id. */
    protected function seedUnion(int $partner1Id, int $partner2Id, string $unionType = 'married'): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
             VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([$this->famCode, $partner1Id, $partner2Id, $unionType]);
        $id = (int) $this->pdo->lastInsertId();
        $this->unionIds[] = $id;
        return $id;
    }

    /** @return array<string, mixed>|null */
    protected function nodeById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM family_nodes WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> every node in this test's family */
    protected function familyNodes(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM family_nodes WHERE family_code = ? ORDER BY id');
        $stmt->execute([$this->famCode]);
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null the newest union in this test's family */
    protected function latestUnion(): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM family_unions WHERE family_code = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$this->famCode]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> child-link rows for a union */
    protected function childrenOfUnion(int $unionId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM family_node_children WHERE union_id = ? ORDER BY id');
        $stmt->execute([$unionId]);
        return $stmt->fetchAll();
    }

    /**
     * Runs $callback, captures everything it echoes and decodes it as JSON.
     *
     * @return array<string, mixed>
     */
    protected function captureJsonOutput(callable $callback): array
    {
        ob_start();
        try {
            $callback();
        } finally {
            $output = ob_get_clean();
        }

        $decoded = json_decode($output !== false ? $output : '', true);
        $this->assertIsArray($decoded, "Expected JSON output, got: " . var_export($output, true));

        return $decoded;
    }
}
