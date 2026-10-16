<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\services\CollateralKinshipService;
use Src\Db;
use PDO;

final class CollateralKinshipServiceTest extends TestCase
{
    private string $testFamCode = 'TEST_COLLATERAL_CHARTER_FAM';
    private string $egoUserId = 'test-ego-wally-100';
    private string $cousinUserId = 'test-cousin-tobi-200';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupDatabase();
        $this->seedCollateralDynasty();
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();
        parent::tearDown();
    }

    private function cleanupDatabase(): void
    {
        $db = Db::connect2();
        $db->prepare("DELETE FROM personal WHERE id IN (?, ?)")->execute([$this->egoUserId, $this->cousinUserId]);
        $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$this->testFamCode]);
        $db->prepare("DELETE FROM family_unions WHERE family_code = ?")->execute([$this->testFamCode]);
    }

    /**
     * Seed a 6-tier multi-generational collateral dynasty
     */
    private function seedCollateralDynasty(): void
    {
        $db = Db::connect2();

        // 1. Personal accounts
        $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender)
            VALUES (?, 'Wally', 'Olaogun', ?, 'Male')
        ")->execute([$this->egoUserId, $this->testFamCode]);

        $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender)
            VALUES (?, 'Tobi', 'Olaogun', ?, 'Male')
        ")->execute([$this->cousinUserId, $this->testFamCode]);

        // Helper to insert a node
        $insertNode = function (string $first, string $last, string $gender, ?string $userId = null) use ($db): int {
            $stmt = $db->prepare("
                INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, avatar_url)
                VALUES (?, ?, ?, ?, ?, '/resources/images/profile/avatarM.png')
            ");
            $stmt->execute([$this->testFamCode, $userId, $first, $last, $gender]);
            return (int)$db->lastInsertId();
        };

        // Helper to insert a union
        $insertUnion = function (int $p1, int $p2) use ($db): int {
            $stmt = $db->prepare("
                INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
                VALUES (?, ?, ?, 'married', 1)
            ");
            $stmt->execute([$this->testFamCode, $p1, $p2]);
            return (int)$db->lastInsertId();
        };

        // Helper to link child
        $linkChild = function (int $uId, int $cId) use ($db): void {
            $stmt = $db->prepare("
                INSERT INTO family_node_children (union_id, child_id, relationship_type)
                VALUES (?, ?, 'biological')
            ");
            $stmt->execute([$uId, $cId]);
        };

        // ----------------------------------------------------
        // TIER -3: Great-Grandparents & Great-Grand-Sibling
        // ----------------------------------------------------
        // Paternal Great-Grandfather & Great-Grandmother
        $ggf = $insertNode('Abraham', 'Olaogun', 'Male');
        $ggm = $insertNode('Sarah', 'Olaogun', 'Female');
        $uGg = $insertUnion($ggf, $ggm);

        // Great-Grandfather's Brother (Great-Grand-Uncle) & Wife
        $ggUncle = $insertNode('Enoch', 'Olaogun', 'Male');
        $ggAunt = $insertNode('Esther', 'Olaogun', 'Female');
        $uGgUncle = $insertUnion($ggUncle, $ggAunt);

        // Parent of Great-Grandparents to link Enoch and Abraham as brothers
        $ancestorRootF = $insertNode('Patriarch', 'Olaogun', 'Male');
        $ancestorRootM = $insertNode('Matriarch', 'Olaogun', 'Female');
        $uAncestor = $insertUnion($ancestorRootF, $ancestorRootM);
        $linkChild($uAncestor, $ggf);
        $linkChild($uAncestor, $ggUncle);

        // ----------------------------------------------------
        // TIER -2: Grandparents & Grand-Uncle
        // ----------------------------------------------------
        // Grandfather (Son of Abraham & Sarah) & Grandmother
        $gf = $insertNode('Oluyomi', 'Olaogun', 'Male');
        $gm = $insertNode('Iyabo', 'Olaogun', 'Female');
        $linkChild($uGg, $gf);

        // Grandfather's Brother (Great-Uncle) & Wife
        $gUncle = $insertNode('Samuel', 'Olaogun', 'Male');
        $gAunt = $insertNode('Grace', 'Olaogun', 'Female');
        $linkChild($uGg, $gUncle);
        $uGUncle = $insertUnion($gUncle, $gAunt);

        $uGp = $insertUnion($gf, $gm);

        // ----------------------------------------------------
        // TIER -1: Parents & Uncles/Aunts (Father's siblings)
        // ----------------------------------------------------
        // Father (Son of Oluyomi & Iyabo) & Mother
        $father = $insertNode('Adewale', 'Olaogun', 'Male');
        $mother = $insertNode('Olayinka', 'Olaogun', 'Female');
        $linkChild($uGp, $father);
        $uParents = $insertUnion($father, $mother);

        // Father's Brother (Uncle) & Aunt (Uncle's Wife)
        $uncle = $insertNode('Babatunde', 'Olaogun', 'Male');
        $aunt = $insertNode('Funke', 'Olaogun', 'Female');
        $linkChild($uGp, $uncle);
        $uUncle = $insertUnion($uncle, $aunt);

        // ----------------------------------------------------
        // TIER 0: Ego, Siblings, and First Cousins
        // ----------------------------------------------------
        // Ego (Wally) & Spouse
        $ego = $insertNode('Wally', 'Olaogun', 'Male', $this->egoUserId);
        $egoSpouse = $insertNode('Ajibike', 'Olaogun', 'Female');
        $linkChild($uParents, $ego);
        $uEgo = $insertUnion($ego, $egoSpouse);

        // Ego's Sister & Brother-in-law
        $sister = $insertNode('Temitope', 'Olaogun', 'Female');
        $broInLaw = $insertNode('Kayode', 'Adebayo', 'Male');
        $linkChild($uParents, $sister);
        $uSister = $insertUnion($sister, $broInLaw);

        // First Cousin (Uncle Babatunde's Son - Tobi) & Wife
        $cousin = $insertNode('Tobi', 'Olaogun', 'Male', $this->cousinUserId);
        $cousinWife = $insertNode('Folashade', 'Olaogun', 'Female');
        $linkChild($uUncle, $cousin);
        $uCousin = $insertUnion($cousin, $cousinWife);

        // ----------------------------------------------------
        // TIER +1: Children of Ego & Nieces/Nephews
        // ----------------------------------------------------
        // Child of Ego & Child's Partner
        $child = $insertNode('Jumoke', 'Olaogun', 'Female');
        $sonInLaw = $insertNode('David', 'Johnson', 'Male');
        $linkChild($uEgo, $child);
        $uChild = $insertUnion($child, $sonInLaw);

        // Niece (Sister Temitope's Child)
        $niece = $insertNode('Simisola', 'Adebayo', 'Female');
        $linkChild($uSister, $niece);

        // ----------------------------------------------------
        // TIER +2: Grandchildren
        // ----------------------------------------------------
        // Granddaughter (Jumoke's Daughter) & Spouse
        $granddaughter = $insertNode('Amara', 'Johnson', 'Female');
        $granddaughterSpouse = $insertNode('Lucas', 'Brown', 'Male');
        $linkChild($uChild, $granddaughter);
        $insertUnion($granddaughter, $granddaughterSpouse);
    }

    /**
     * Test 1: Full multi-generational collateral graph from Ego's vantage
     */
    public function testEgoCollateralGraphIncludesGreatGrandparentsDownToGrandchildren(): void
    {
        $graph = CollateralKinshipService::buildCollateralGraph($this->testFamCode, $this->egoUserId);

        $this->assertTrue($graph['has_built_tree'], 'Tree must be flagged as built');
        $this->assertNotEmpty($graph['nodes'], 'Nodes must not be empty');

        $nodeNames = array_column($graph['nodes'], 'full_name');
        $nodeMap = [];
        foreach ($graph['nodes'] as $n) {
            $nodeMap[$n['full_name']] = $n;
        }

        // 1. Great-Grandparents & Great-Grand-Uncle
        $this->assertContains('Abraham Olaogun', $nodeNames, 'Great-Grandfather must be present');
        $this->assertContains('Sarah Olaogun', $nodeNames, 'Great-Grandmother must be present');
        $this->assertContains('Enoch Olaogun', $nodeNames, 'Great-Grand-Uncle must be present');
        $this->assertSame(-3, $nodeMap['Abraham Olaogun']['generation_level']);

        // 2. Grandparents & Great-Uncle
        $this->assertContains('Oluyomi Olaogun', $nodeNames, 'Grandfather must be present');
        $this->assertContains('Iyabo Olaogun', $nodeNames, 'Grandmother must be present');
        $this->assertContains('Samuel Olaogun', $nodeNames, 'Great-Uncle must be present');
        $this->assertSame(-2, $nodeMap['Oluyomi Olaogun']['generation_level']);

        // 3. Parents, Uncles, Aunts
        $this->assertContains('Adewale Olaogun', $nodeNames, 'Father must be present');
        $this->assertContains('Olayinka Olaogun', $nodeNames, 'Mother must be present');
        $this->assertContains('Babatunde Olaogun', $nodeNames, 'Uncle must be present');
        $this->assertContains('Funke Olaogun', $nodeNames, 'Aunt must be present');
        $this->assertSame(-1, $nodeMap['Adewale Olaogun']['generation_level']);

        // 4. Ego, Siblings, First Cousins & In-laws
        $this->assertContains('Wally Olaogun', $nodeNames, 'Ego must be present');
        $this->assertContains('Ajibike Olaogun', $nodeNames, 'Ego spouse must be present');
        $this->assertContains('Temitope Olaogun', $nodeNames, 'Sister must be present');
        $this->assertContains('Kayode Adebayo', $nodeNames, 'Brother-in-law must be present');
        $this->assertContains('Tobi Olaogun', $nodeNames, 'First Cousin must be present');
        $this->assertContains('Folashade Olaogun', $nodeNames, 'Cousin spouse must be present');
        $this->assertSame(0, $nodeMap['Wally Olaogun']['generation_level']);
        $this->assertSame(0, $nodeMap['Tobi Olaogun']['generation_level']);

        // 5. Children & Nieces
        $this->assertContains('Jumoke Olaogun', $nodeNames, 'Child must be present');
        $this->assertContains('David Johnson', $nodeNames, 'Son-in-law must be present');
        $this->assertContains('Simisola Adebayo', $nodeNames, 'Niece must be present');
        $this->assertSame(1, $nodeMap['Jumoke Olaogun']['generation_level']);

        // 6. Grandchildren & Spouses
        $this->assertContains('Amara Johnson', $nodeNames, 'Granddaughter must be present');
        $this->assertContains('Lucas Brown', $nodeNames, 'Granddaughter spouse must be present');
        $this->assertSame(2, $nodeMap['Amara Johnson']['generation_level']);

        // Kinship relationship titles assertion
        $this->assertSame('You (Primary Member)', $nodeMap['Wally Olaogun']['ego_relation']);
        $this->assertStringContainsString('Father', $nodeMap['Adewale Olaogun']['ego_relation']);
        $this->assertStringContainsString('Mother', $nodeMap['Olayinka Olaogun']['ego_relation']);
        $this->assertStringContainsString('Grandfather', $nodeMap['Oluyomi Olaogun']['ego_relation']);
        $this->assertStringContainsString('Sister', $nodeMap['Temitope Olaogun']['ego_relation']);
        $this->assertStringContainsString('Daughter', $nodeMap['Jumoke Olaogun']['ego_relation']);
        $this->assertStringContainsString('Granddaughter', $nodeMap['Amara Johnson']['ego_relation']);
    }

    /**
     * Test 2: Universal Vantage Parity (When First Cousin Tobi views the tree)
     */
    public function testUniversalVantageParityWhenFirstCousinViewsTheTree(): void
    {
        $graph = CollateralKinshipService::buildCollateralGraph($this->testFamCode, $this->cousinUserId);

        $this->assertTrue($graph['has_built_tree']);
        $nodeMap = [];
        foreach ($graph['nodes'] as $n) {
            $nodeMap[$n['full_name']] = $n;
        }

        // Tobi is now Ego (Gen 0)
        $this->assertSame(0, $nodeMap['Tobi Olaogun']['generation_level']);
        $this->assertSame('You (Primary Member)', $nodeMap['Tobi Olaogun']['ego_relation']);

        // Tobi's Father is Babatunde (Gen -1)
        $this->assertSame(-1, $nodeMap['Babatunde Olaogun']['generation_level']);
        $this->assertStringContainsString('Father', $nodeMap['Babatunde Olaogun']['ego_relation']);

        // Wally is now Tobi's First Cousin (Gen 0)
        $this->assertSame(0, $nodeMap['Wally Olaogun']['generation_level']);
        $this->assertStringContainsString('Cousin', $nodeMap['Wally Olaogun']['ego_relation']);

        // Oluyomi is still Grandfather (Gen -2)
        $this->assertSame(-2, $nodeMap['Oluyomi Olaogun']['generation_level']);
        $this->assertStringContainsString('Grandfather', $nodeMap['Oluyomi Olaogun']['ego_relation']);

        // Abraham is still Great-Grandfather (Gen -3)
        $this->assertSame(-3, $nodeMap['Abraham Olaogun']['generation_level']);
        $this->assertStringContainsString('Great-Grandfather', $nodeMap['Abraham Olaogun']['ego_relation']);
    }

    /**
     * Test 3: Deep 7-Generation Dynasty with Paternal & Maternal Parity, Spouses, and Collaterals
     */
    public function testSevenGenerationDynastyWithPaternalMaternalParityAndSpouses(): void
    {
        $db = Db::connect2();
        $famCode = 'TEST_7GEN_PARITY_FAM';
        $wallyId = 'test-wally-7gen-root';

        // Clean any leftovers
        $db->prepare("DELETE FROM personal WHERE id = ?")->execute([$wallyId]);
        $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$famCode]);
        $db->prepare("DELETE FROM family_unions WHERE family_code = ?")->execute([$famCode]);

        $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender)
            VALUES (?, 'Wale', 'Olaogun', ?, 'Male')
        ")->execute([$wallyId, $famCode]);

        $insertNode = function (string $first, string $last, string $gender, ?string $userId = null) use ($db, $famCode): int {
            $stmt = $db->prepare("
                INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, avatar_url)
                VALUES (?, ?, ?, ?, ?, '/resources/images/profile/avatarM.png')
            ");
            $stmt->execute([$famCode, $userId, $first, $last, $gender]);
            return (int)$db->lastInsertId();
        };

        $insertUnion = function (int $p1, int $p2) use ($db, $famCode): int {
            $stmt = $db->prepare("
                INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
                VALUES (?, ?, ?, 'married', 1)
            ");
            $stmt->execute([$famCode, $p1, $p2]);
            return (int)$db->lastInsertId();
        };

        $linkChild = function (int $uId, int $cId) use ($db): void {
            $stmt = $db->prepare("
                INSERT INTO family_node_children (union_id, child_id, relationship_type)
                VALUES (?, ?, 'biological')
            ");
            $stmt->execute([$uId, $cId]);
        };

        // Ego (Tier 0)
        $wally = $insertNode('Wale', 'Olaogun', 'Male', $wallyId);
        $ajibike = $insertNode('Ajibike', 'Olaogun', 'Female');
        $uWally = $insertUnion($wally, $ajibike);

        // Ego Siblings
        $femi = $insertNode('Femi', 'Olaogun', 'Male');
        $sola = $insertNode('Sola', 'Olaogun', 'Male');
        $temitope = $insertNode('Temitope', 'Olaogun', 'Female');

        // Sibling child (Niece, Tier +1) & Sibling grandchild (Grand-niece, Tier +2)
        $femiWife = $insertNode('FemiWife', 'Olaogun', 'Female');
        $uFemi = $insertUnion($femi, $femiWife);
        $niece = $insertNode('Bisi', 'Olaogun', 'Female');
        $linkChild($uFemi, $niece);

        $nieceHusband = $insertNode('NieceHusband', 'Olaogun', 'Male');
        $uNiece = $insertUnion($niece, $nieceHusband);
        $grandNiece = $insertNode('Tade', 'Olaogun', 'Female');
        $linkChild($uNiece, $grandNiece);

        // Parents (Tier -1)
        $oluyomi = $insertNode('Oluyomi', 'Olaogun', 'Male');
        $iyabo = $insertNode('Iyabo', 'Olaogun', 'Female');
        $uParents = $insertUnion($oluyomi, $iyabo);
        $linkChild($uParents, $wally);
        $linkChild($uParents, $femi);
        $linkChild($uParents, $sola);
        $linkChild($uParents, $temitope);

        // Paternal Grandparents (Tier -2)
        $patGf = $insertNode('PaOluyomiFather', 'Olaogun', 'Male');
        $patGm = $insertNode('MaOluyomiMother', 'Olaogun', 'Female');
        $uPatGp = $insertUnion($patGf, $patGm);
        $linkChild($uPatGp, $oluyomi);
        // Oluyomi's Sibling (Paternal Uncle)
        $patUncle = $insertNode('Kehinde', 'Olaogun', 'Male');
        $linkChild($uPatGp, $patUncle);

        // Maternal Grandparents (Tier -2)
        $matGf = $insertNode('PaJohn', 'Mayungbe', 'Male');
        $matGm = $insertNode('Ondeyin', 'Mayungbe', 'Female');
        $uMatGp = $insertUnion($matGf, $matGm);
        $linkChild($uMatGp, $iyabo);
        // Iyabo's Sibling (Maternal Uncle)
        $matUncle = $insertNode('Adesanya', 'Mayungbe', 'Male');
        $linkChild($uMatGp, $matUncle);

        // Ancestors up to Tier -4 (Great-Great-Grandparents)
        $patGgf = $insertNode('PatGgf', 'Olaogun', 'Male');
        $patGgm = $insertNode('PatGgm', 'Olaogun', 'Female');
        $uPatGgp = $insertUnion($patGgf, $patGgm);
        $linkChild($uPatGgp, $patGf);

        $patGggf = $insertNode('PatGggf', 'Olaogun', 'Male');
        $patGggm = $insertNode('PatGggm', 'Olaogun', 'Female');
        $uPatGggp = $insertUnion($patGggf, $patGggm);
        $linkChild($uPatGggp, $patGgf);

        // Descendants down to Tier +3 (Great-Grandchild)
        $child1 = $insertNode('Olutobi', 'Olaogun', 'Male');
        $linkChild($uWally, $child1);

        $child1Spouse = $insertNode('Child1Spouse', 'Olaogun', 'Female');
        $uChild1 = $insertUnion($child1, $child1Spouse);
        $grandChild1 = $insertNode('Grandchild1', 'Olaogun', 'Male');
        $linkChild($uChild1, $grandChild1);

        $gc1Spouse = $insertNode('Gc1Spouse', 'Olaogun', 'Female');
        $uGc1 = $insertUnion($grandChild1, $gc1Spouse);
        $greatGrandChild1 = $insertNode('GreatGrandchild1', 'Olaogun', 'Female');
        $linkChild($uGc1, $greatGrandChild1);

        // Build Graph
        $graph = CollateralKinshipService::buildCollateralGraph($famCode, $wallyId);

        $this->assertTrue($graph['has_built_tree']);
        $nodeMap = [];
        foreach ($graph['nodes'] as $n) {
            $nodeMap[$n['full_name']] = $n;
        }

        // Verify Paternal & Maternal Parity
        $this->assertArrayHasKey('PaOluyomiFather Olaogun', $nodeMap, 'Paternal Grandfather must be present');
        $this->assertArrayHasKey('Kehinde Olaogun', $nodeMap, 'Paternal Uncle must be present');
        $this->assertArrayHasKey('PaJohn Mayungbe', $nodeMap, 'Maternal Grandfather must be present');
        $this->assertArrayHasKey('Adesanya Mayungbe', $nodeMap, 'Maternal Uncle must be present');

        // Verify generation levels
        $this->assertSame(-4, $nodeMap['PatGggf Olaogun']['generation_level']);
        $this->assertSame(-3, $nodeMap['PatGgf Olaogun']['generation_level']);
        $this->assertSame(-2, $nodeMap['PaOluyomiFather Olaogun']['generation_level']);
        $this->assertSame(-2, $nodeMap['PaJohn Mayungbe']['generation_level']);
        $this->assertSame(-1, $nodeMap['Oluyomi Olaogun']['generation_level']);
        $this->assertSame(-1, $nodeMap['Iyabo Olaogun']['generation_level']);
        $this->assertSame(0, $nodeMap['Wale Olaogun']['generation_level']);
        $this->assertSame(1, $nodeMap['Olutobi Olaogun']['generation_level']);
        $this->assertSame(2, $nodeMap['Grandchild1 Olaogun']['generation_level']);
        $this->assertSame(3, $nodeMap['GreatGrandchild1 Olaogun']['generation_level']);

        // Verify Sibling descendants
        $this->assertArrayHasKey('Bisi Olaogun', $nodeMap, 'Niece must be present');
        $this->assertSame(1, $nodeMap['Bisi Olaogun']['generation_level']);
        $this->assertArrayHasKey('Tade Olaogun', $nodeMap, 'Grand-Niece must be present');
        $this->assertSame(2, $nodeMap['Tade Olaogun']['generation_level']);

        // Verify Titles
        $this->assertStringContainsString('Uncle', $nodeMap['Kehinde Olaogun']['ego_relation']);
        $this->assertStringContainsString('Uncle', $nodeMap['Adesanya Mayungbe']['ego_relation']);
        $this->assertStringContainsString('Granddaughter', $nodeMap['GreatGrandchild1 Olaogun']['ego_relation']);

        // Clean up
        $db->prepare("DELETE FROM personal WHERE id = ?")->execute([$wallyId]);
        $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$famCode]);
        $db->prepare("DELETE FROM family_unions WHERE family_code = ?")->execute([$famCode]);
    }
}
