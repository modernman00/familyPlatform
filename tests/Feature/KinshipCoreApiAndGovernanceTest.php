<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\services\ApiKeyAuthService;
use App\services\KinshipCalculatorService;
use App\services\KinshipEntityResolver;
use Src\Db;

final class KinshipCoreApiAndGovernanceTest extends TestCase
{
    private static string $testFamCode = 'TEST_KINSHIP_EGO_01';

    protected function tearDown(): void
    {
        // Cleanup test data
        $db = Db::connect2();
        $db->prepare("DELETE FROM api_keys WHERE name LIKE 'TEST_%'")->execute();
        $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([self::$testFamCode]);
        $db->prepare("DELETE FROM family_unions WHERE family_code = ?")->execute([self::$testFamCode]);
    }

    /**
     * Test API Key Generation, Hashing, Masking, and Revocation
     */
    public function testApiKeyGovernanceLifecycle(): void
    {
        // 1. Generate Key
        $keyData = ApiKeyAuthService::generateKey(
            'TEST_PartyPlatform_Client',
            ['tree:read', 'tree:write'],
            ['TEST_FAM_001'],
            60,
            null,
            null,
            'admin_tester'
        );

        $this->assertNotEmpty($keyData['raw_key']);
        $this->assertStringStartsWith('fp_live_', $keyData['raw_key']);
        $this->assertEquals('TEST_PartyPlatform_Client', $keyData['name']);
        $this->assertEquals(['tree:read', 'tree:write'], $keyData['scopes']);

        // 2. Verify Key is Stored as SHA-256 Hash
        $db = Db::connect2();
        $stmt = $db->prepare("SELECT * FROM api_keys WHERE id = ?");
        $stmt->execute([$keyData['id']]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($row);
        $this->assertEquals(hash('sha256', $keyData['raw_key']), $row['key_hash']);
        $this->assertEquals(1, (int)$row['is_active']);

        // 3. Verify listKeys() returns key with masked prefix
        $list = ApiKeyAuthService::listKeys();
        $found = false;
        foreach ($list as $k) {
            if ((int)$k['id'] === $keyData['id']) {
                $found = true;
                $this->assertEquals($keyData['prefix'], $k['key_prefix']);
                break;
            }
        }
        $this->assertTrue($found, 'Newly provisioned key should appear in listKeys()');

        // 4. Revoke Key
        $revoked = ApiKeyAuthService::revokeKey($keyData['id']);
        $this->assertTrue($revoked);

        $stmt->execute([$keyData['id']]);
        $revokedRow = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(0, (int)$revokedRow['is_active']);
    }

    /**
     * Test Ego-Centric Kinship Calculator with Wally's Exact 4-Generation Structure
     */
    public function testEgoCentricKinshipCalculations(): void
    {
        // Simulated graph representing Wally's family tree
        $graph = [
            'nodes' => [
                // Grandparents
                ['id' => 10, 'first_name' => 'Pa John', 'last_name' => 'Mayungbe', 'gender' => 'Male'],
                ['id' => 11, 'first_name' => 'Dorcas', 'last_name' => 'Mayungbe', 'gender' => 'Female'],
                // Generation 2: Iyabo (Mother), Oluyomi (Father), Adesanya (Maternal Uncle)
                ['id' => 20, 'first_name' => 'Iyabo', 'last_name' => 'Olaogun', 'gender' => 'Female'],
                ['id' => 21, 'first_name' => 'Oluyomi', 'last_name' => 'Olaogun', 'gender' => 'Male'],
                ['id' => 22, 'first_name' => 'Adesanya', 'last_name' => 'Mayungbe', 'gender' => 'Male'],
                // Generation 3: Wale (Ego), Ajibike (Spouse), Femi (Brother), Sola (Brother), Temitope (Sister)
                ['id' => 30, 'first_name' => 'Wale', 'last_name' => 'Olaogun', 'gender' => 'Male'],
                ['id' => 31, 'first_name' => 'Ajibike', 'last_name' => 'Olaogun', 'gender' => 'Female'],
                ['id' => 32, 'first_name' => 'Femi', 'last_name' => 'Olaogun', 'gender' => 'Male'],
                ['id' => 33, 'first_name' => 'Sola', 'last_name' => 'Olaogun', 'gender' => 'Male'],
                ['id' => 34, 'first_name' => 'Temitope', 'last_name' => 'Olaogun', 'gender' => 'Female'],
                // Generation 4: Olajumoke (Daughter)
                ['id' => 40, 'first_name' => 'Olajumoke', 'last_name' => 'Olaogun', 'gender' => 'Female'],
            ],
            'unions' => [
                // Union 1: Pa John + Dorcas
                ['id' => 1, 'partner_1_id' => 10, 'partner_2_id' => 11, 'union_type' => 'married'],
                // Union 2: Oluyomi + Iyabo
                ['id' => 2, 'partner_1_id' => 21, 'partner_2_id' => 20, 'union_type' => 'married'],
                // Union 3: Wale + Ajibike
                ['id' => 3, 'partner_1_id' => 30, 'partner_2_id' => 31, 'union_type' => 'married'],
            ],
            'children' => [
                // Children of Pa John + Dorcas: Iyabo and Adesanya
                ['union_id' => 1, 'child_id' => 20, 'relationship_type' => 'biological'],
                ['union_id' => 1, 'child_id' => 22, 'relationship_type' => 'biological'],
                // Children of Oluyomi + Iyabo: Wale, Femi, Sola, Temitope
                ['union_id' => 2, 'child_id' => 30, 'relationship_type' => 'biological'],
                ['union_id' => 2, 'child_id' => 32, 'relationship_type' => 'biological'],
                ['union_id' => 2, 'child_id' => 33, 'relationship_type' => 'biological'],
                ['union_id' => 2, 'child_id' => 34, 'relationship_type' => 'biological'],
                // Child of Wale + Ajibike: Olajumoke
                ['union_id' => 3, 'child_id' => 40, 'relationship_type' => 'biological'],
            ]
        ];

        // Run Kinship Calculator anchored on Wale (node ID 30)
        $annotated = KinshipCalculatorService::calculateEgoKinship($graph, 30);

        // Assert Ego
        $this->assertEquals('You (Primary Member)', $annotated[30]['ego_relation']);
        $this->assertTrue($annotated[30]['is_ego']);

        // Assert Parents
        $this->assertEquals('Mother', $annotated[20]['ego_relation']);
        $this->assertEquals('Father', $annotated[21]['ego_relation']);

        // Assert Maternal Grandparents
        $this->assertEquals('Maternal Grandfather', $annotated[10]['ego_relation']);
        $this->assertEquals('Maternal Grandmother', $annotated[11]['ego_relation']);
        $this->assertEquals('maternal', $annotated[10]['ego_side']);
        $this->assertEquals('maternal', $annotated[11]['ego_side']);

        // Assert Maternal Uncle
        $this->assertEquals('Maternal Uncle', $annotated[22]['ego_relation']);
        $this->assertEquals('maternal', $annotated[22]['ego_side']);

        // Assert Siblings
        $this->assertEquals('Brother', $annotated[32]['ego_relation']);
        $this->assertEquals('Brother', $annotated[33]['ego_relation']);
        $this->assertEquals('Sister', $annotated[34]['ego_relation']);

        // Assert Spouse & Child
        $this->assertEquals('Wife', $annotated[31]['ego_relation']);
        $this->assertEquals('Daughter', $annotated[40]['ego_relation']);
    }

    /**
     * Test Entity Resolution Deduplication & Duplicate Healing
     */
    public function testEntityDeduplicationAndHealing(): void
    {
        $db = Db::connect2();

        // Create Parent Union
        $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
            VALUES (?, 'Father', 'Tester', 'Male', -1)
        ")->execute([self::$testFamCode]);
        $fatherId = (int)$db->lastInsertId();

        $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
            VALUES (?, 'Mother', 'Tester', 'Female', -1)
        ")->execute([self::$testFamCode]);
        $motherId = (int)$db->lastInsertId();

        $db->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, 'married', 1)
        ")->execute([self::$testFamCode, $fatherId, $motherId]);
        $unionId = (int)$db->lastInsertId();

        // 1. Add first child "Femi Tester"
        $res1 = KinshipEntityResolver::resolveOrCreateChild(
            self::$testFamCode,
            $unionId,
            'Femi',
            'Tester',
            'Male',
            'femi@test.com'
        );
        $this->assertTrue($res1['is_new']);

        // 2. Add second child with identical name and email
        $res2 = KinshipEntityResolver::resolveOrCreateChild(
            self::$testFamCode,
            $unionId,
            'FEMI',
            'Tester',
            'Male',
            'femi@test.com'
        );
        $this->assertFalse($res2['is_new'], 'Should detect existing child and avoid creating duplicate');
        $this->assertEquals($res1['node_id'], $res2['node_id']);

        // 3. Manually simulate an old duplicate row and test healDuplicateNodes()
        $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
            VALUES (?, 'femi', 'tester', 'Male', 0)
        ")->execute([self::$testFamCode]);
        $manualDupId = (int)$db->lastInsertId();

        $healResult = KinshipEntityResolver::healDuplicateNodes(self::$testFamCode);
        $this->assertGreaterThanOrEqual(1, $healResult['merged_count']);
        $this->assertContains($manualDupId, $healResult['removed_ids']);

        // Verify duplicate was removed
        $chk = $db->prepare("SELECT COUNT(*) FROM family_nodes WHERE id = ?");
        $chk->execute([$manualDupId]);
        $this->assertEquals(0, (int)$chk->fetchColumn());
    }
}
