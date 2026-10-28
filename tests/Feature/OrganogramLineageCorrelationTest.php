<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\Organogram;
use App\services\KinshipEntityResolver;
use App\services\LineageQuestService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Src\Db;
use PDO;

final class OrganogramLineageCorrelationTest extends TestCase
{
    private PDO $pdo;
    private string $testFamCode;
    private string $rootUserId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();

        $this->testFamCode = 'CORR_TEST_' . bin2hex(random_bytes(4));
        $this->rootUserId = 'corr_user_' . bin2hex(random_bytes(4));

        // Seed personal record
        $stmt = $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender, day, month, year, kids)
            VALUES (?, 'Laf', 'OLAOGUN', ?, 'Male', 1, 'Jan', 1985, 1)
        ");
        $stmt->execute([$this->rootUserId, $this->testFamCode]);
    }

    protected function tearDown(): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM personal WHERE famCode = ?");
        $stmt->execute([$this->testFamCode]);

        $stmt = $this->pdo->prepare("
            DELETE FROM family_node_children 
            WHERE child_id IN (SELECT id FROM family_nodes WHERE family_code = ?)
               OR union_id IN (SELECT id FROM family_unions WHERE family_code = ?)
        ");
        $stmt->execute([$this->testFamCode, $this->testFamCode]);

        $stmt = $this->pdo->prepare("DELETE FROM family_unions WHERE family_code = ?");
        $stmt->execute([$this->testFamCode]);

        $stmt = $this->pdo->prepare("DELETE FROM family_nodes WHERE family_code = ?");
        $stmt->execute([$this->testFamCode]);

        parent::tearDown();
    }

    public function testKinshipEntityResolverSanitizesDuplicateSurnames(): void
    {
        // Insert node with duplicated surname in first_name
        $stmt = $this->pdo->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, created_at, updated_at)
            VALUES (?, NULL, 'OLUYOMI OLAOGUN', 'OLAOGUN', 'Male', 1, NOW(), NOW())
        ");
        $stmt->execute([$this->testFamCode]);
        $nodeId = (int)$this->pdo->lastInsertId();

        KinshipEntityResolver::healDuplicateNodes($this->testFamCode);

        $stmt = $this->pdo->prepare("SELECT first_name, last_name FROM family_nodes WHERE id = ?");
        $stmt->execute([$nodeId]);
        $node = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($node);
        $this->assertEquals('OLUYOMI', $node['first_name']);
        $this->assertEquals('OLAOGUN', $node['last_name']);
    }

    public function testOrganogramHealsOrphanedGen0Nodes(): void
    {
        // 1. Create parents (Gen -1)
        $stmt = $this->pdo->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio, created_at, updated_at)
            VALUES (?, NULL, 'Oluyomi', 'Olaogun', 'Male', -1, 'Father', NOW(), NOW())
        ");
        $stmt->execute([$this->testFamCode]);
        $fatherId = (int)$this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio, created_at, updated_at)
            VALUES (?, NULL, 'Iyabo', 'Olaogun', 'Female', -1, 'Mother', NOW(), NOW())
        ");
        $stmt->execute([$this->testFamCode]);
        $motherId = (int)$this->pdo->lastInsertId();

        // Ancestral union
        $stmt = $this->pdo->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current, created_at)
            VALUES (?, ?, ?, 'MARRIED', 1, NOW())
        ");
        $stmt->execute([$this->testFamCode, $fatherId, $motherId]);
        $ancestralUnionId = (int)$this->pdo->lastInsertId();

        // 2. Create orphaned Gen 0 node (Laf)
        $stmt = $this->pdo->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, created_at, updated_at)
            VALUES (?, ?, 'Laf', 'Olaogun', 'Male', 0, NOW(), NOW())
        ");
        $stmt->execute([$this->testFamCode, $this->rootUserId]);
        $lafNodeId = (int)$this->pdo->lastInsertId();

        // Call repairOrphanedFamilyGraph via reflection
        $organogram = new Organogram();
        $ref = new ReflectionClass(Organogram::class);
        $repairMethod = $ref->getMethod('repairOrphanedFamilyGraph');
        $repairMethod->setAccessible(true);
        $repairMethod->invoke($organogram, $this->testFamCode, $this->rootUserId);

        // Verify Laf is now registered as child in ancestralUnionId
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM family_node_children 
            WHERE union_id = ? AND child_id = ?
        ");
        $stmt->execute([$ancestralUnionId, $lafNodeId]);
        $count = (int)$stmt->fetchColumn();

        $this->assertEquals(1, $count, 'Orphaned Gen 0 Laf node must be healed and linked to ancestral parents union');
    }

    public function testLineageQuestServiceFormatsCleanNames(): void
    {
        // Insert nodes with duplicated surname
        $stmt = $this->pdo->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, created_at, updated_at)
            VALUES (?, NULL, 'Oluyomi Olaogun', 'Olaogun', 'Male', 1, NOW(), NOW())
        ");
        $stmt->execute([$this->testFamCode]);

        $result = LineageQuestService::getFamilyQuests($this->testFamCode, $this->rootUserId);
        $this->assertIsArray($result);
        $quests = $result['quests'] ?? [];
        $this->assertIsArray($quests);

        foreach ($quests as $quest) {
            $title = $quest['title'] ?? '';
            $desc = $quest['description'] ?? '';
            $this->assertStringNotContainsString('Olaogun Olaogun', $title);
            $this->assertStringNotContainsString('OLAOGUN OLAOGUN', $title);
            $this->assertStringNotContainsString('Olaogun Olaogun', $desc);
        }
    }
}
