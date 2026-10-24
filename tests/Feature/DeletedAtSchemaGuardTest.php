<?php

declare(strict_types=1);

namespace Tests\Feature;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Src\Db;

/**
 * RFC-0006 guard: `deleted_at` on the member-identity tables must only change when something deliberately
 * deletes a row. An `ON UPDATE CURRENT_TIMESTAMP` here makes every unrelated UPDATE look like a deletion.
 */
final class DeletedAtSchemaGuardTest extends TestCase
{
    private PDO $pdo;

    /** @var list<string> */
    private array $ids = [];

    protected function setUp(): void
    {
        $this->pdo = Db::connect2();
    }

    protected function tearDown(): void
    {
        foreach ($this->ids as $id) {
            $this->pdo->prepare('DELETE FROM contact WHERE id = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM personal WHERE id = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM account WHERE id = ?')->execute([$id]);
        }
    }

    /** @return array<string, array{string}> */
    public static function tables(): array
    {
        return ['account' => ['account'], 'personal' => ['personal'], 'contact' => ['contact']];
    }

    #[DataProvider('tables')]
    public function testDeletedAtIsNotAutoStamped(string $table): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT EXTRA, COLUMN_DEFAULT FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, 'deleted_at']);
        $col = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($col, "{$table}.deleted_at must exist");
        $this->assertStringNotContainsStringIgnoringCase('on update', (string)$col['EXTRA'], "{$table}.deleted_at must not auto-update (RFC-0006)");
        $this->assertNull($col['COLUMN_DEFAULT'], "{$table}.deleted_at must default to NULL");
    }

    public function testAnUnrelatedUpdateLeavesALiveMemberUndeleted(): void
    {
        $id = 'test-da-' . bin2hex(random_bytes(5));
        $this->ids[] = $id;

        $this->pdo->prepare('INSERT INTO account (id, email, password, status) VALUES (?, ?, ?, "approved")')
            ->execute([$id, $id . '@test.com', password_hash('x', PASSWORD_BCRYPT)]);
        $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, "A", "B", "TDA1234")')->execute([$id]);
        $this->pdo->prepare('INSERT INTO contact (id, email, mobile, country) VALUES (?, ?, "07700000000", "UK")')
            ->execute([$id, $id . '@test.com']);

        // Ordinary profile / account / contact edits.
        $this->pdo->prepare("UPDATE account SET status = 'active' WHERE id = ?")->execute([$id]);
        $this->pdo->prepare("UPDATE personal SET firstName = 'Changed' WHERE id = ?")->execute([$id]);
        $this->pdo->prepare("UPDATE contact SET mobile = '07711111111' WHERE id = ?")->execute([$id]);

        $queries = [
            'account' => 'SELECT deleted_at FROM account WHERE id = ?',
            'personal' => 'SELECT deleted_at FROM personal WHERE id = ?',
            'contact' => 'SELECT deleted_at FROM contact WHERE id = ?',
        ];
        foreach ($queries as $table => $sql) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $this->assertNull($stmt->fetchColumn(), "{$table}.deleted_at was stamped by an unrelated UPDATE");
        }
    }

    public function testNoLiveAccountCarriesADeletionStamp(): void
    {
        $count = (int)$this->pdo->query(
            "SELECT COUNT(*) FROM account WHERE deleted_at IS NOT NULL AND status NOT IN ('erased', 'pending_deletion')"
        )->fetchColumn();

        $this->assertSame(0, $count, 'a live account has a deleted_at: the auto-stamp is back or data was not cleaned');
    }
}
