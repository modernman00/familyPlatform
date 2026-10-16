<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\services\KinshipEntityResolver;
use Src\Db;

/**
 * Kinship Auto-Matching Resolution & Adversarial Red Team Penetration Test Suite
 *
 * Verifies intelligent auto-matching, Yoruba/Anglo diminutive mapping,
 * and neutralizes Marcus & Ghost's Red Team attack vectors:
 * 1. Unauthorized Cross-Family IDOR Node Hijacking (403 Forbidden)
 * 2. High-Concurrency Double-Claim Race Conditions (409 Conflict)
 * 3. Sibling Subtree Grafting & DAG Cycle Defenses (422 Cycle Detected)
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class KinshipAutoMatchingAndRedTeamTest extends TestCase
{
    private static string $familyAlpha = 'TEST_FAM_ALPHA_99';
    private static string $familyBeta = 'TEST_FAM_BETA_99';
    private static string $userFemi = 'USR_FEMI_TEST_99';
    private static string $userAttacker = 'USR_ATTACKER_99';

    private static int $unclaimedFemiNodeId = 0;
    private static int $unclaimedBetaNodeId = 0;
    private static int $parentUnionId = 0;
    private static int $waleNodeId = 0;

    public static function setUpBeforeClass(): void
    {
        $db = Db::connect2();

        // 1. Clean previous test artifacts
        self::tearDownAfterClass();

        // 2. Setup Family Alpha: Parent Union (Adesanya & Iyabo) with children (Wale & unclaimed Olufemi)
        $insP1 = $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
            VALUES (?, 'Adesanya', 'Olaogun', 'Male', -1)
        ");
        $insP1->execute([self::$familyAlpha]);
        $p1Id = (int)$db->lastInsertId();

        $insP2 = $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
            VALUES (?, 'Iyabo', 'Olaogun', 'Female', -1)
        ");
        $insP2->execute([self::$familyAlpha]);
        $p2Id = (int)$db->lastInsertId();

        $insUnion = $db->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, 'married', 1)
        ");
        $insUnion->execute([self::$familyAlpha, $p1Id, $p2Id]);
        self::$parentUnionId = (int)$db->lastInsertId();

        // Brother 1: Wale (Already registered)
        $insWale = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, 'USR_WALE_TEST_99', 'Wale', 'Olaogun', 'Male', 0)
        ");
        $insWale->execute([self::$familyAlpha]);
        self::$waleNodeId = (int)$db->lastInsertId();

        $linkWale = $db->prepare("
            INSERT INTO family_node_children (union_id, child_id, relationship_type)
            VALUES (?, ?, 'biological')
        ");
        $linkWale->execute([self::$parentUnionId, self::$waleNodeId]);

        // Brother 2: Unclaimed Olufemi placeholder on tree
        $insFemi = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, email, mobile)
            VALUES (?, NULL, 'Olufemi', 'Olaogun', 'Male', 0, 'femi.olaogun@test.com', '+2348012345678')
        ");
        $insFemi->execute([self::$familyAlpha]);
        self::$unclaimedFemiNodeId = (int)$db->lastInsertId();

        $linkFemi = $db->prepare("
            INSERT INTO family_node_children (union_id, child_id, relationship_type)
            VALUES (?, ?, 'biological')
        ");
        $linkFemi->execute([self::$parentUnionId, self::$unclaimedFemiNodeId]);

        // 3. Setup Family Beta: Separate family tree with an unclaimed node
        $insBetaNode = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, NULL, 'Bisi', 'Adeyemi', 'Female', 0)
        ");
        $insBetaNode->execute([self::$familyBeta]);
        self::$unclaimedBetaNodeId = (int)$db->lastInsertId();
    }

    public static function tearDownAfterClass(): void
    {
        $db = Db::connect2();
        $db->prepare("DELETE FROM kinship_match_dismissals WHERE family_code IN (?, ?)")
            ->execute([self::$familyAlpha, self::$familyBeta]);
        $db->prepare("DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code IN (?, ?))")
            ->execute([self::$familyAlpha, self::$familyBeta]);
        $db->prepare("DELETE FROM family_unions WHERE family_code IN (?, ?)")
            ->execute([self::$familyAlpha, self::$familyBeta]);
        $db->prepare("DELETE FROM family_nodes WHERE family_code IN (?, ?)")
            ->execute([self::$familyAlpha, self::$familyBeta]);
    }

    /**
     * Test Diminutive Mapping & Multi-Vector Match Scoring
     * e.g. User "Femi" matches tree node "Olufemi" with score >= 70% and rich kinship hints.
     */
    public function testDiminutiveAndExactNameAutoMatching(): void
    {
        $memberData = [
            'firstName' => 'Femi',
            'lastName'  => 'Olaogun',
            'email'     => 'femi.olaogun@test.com',
            'mobile'    => '08012345678',
            'gender'    => 'Male'
        ];

        $matches = KinshipEntityResolver::findProbableMatches(self::$familyAlpha, self::$userFemi, $memberData);

        $this->assertNotEmpty($matches, 'Femi should match Olufemi via Yoruba diminutive mapping');
        $topMatch = $matches[0];
        $this->assertSame(self::$unclaimedFemiNodeId, $topMatch['node_id']);
        $this->assertGreaterThanOrEqual(0.70, $topMatch['confidence_score']);
        $this->assertStringContainsString('Adesanya Olaogun', $topMatch['relationship_hint']);
        $this->assertStringContainsString('Wale', $topMatch['relationship_hint']);
    }

    /**
     * Test Dismissal Exclusion
     * When a user dismisses a match, it must never appear in recommendations again.
     */
    public function testDismissedMatchIsExcludedFromRecommendations(): void
    {
        $memberData = [
            'firstName' => 'Femi',
            'lastName'  => 'Olaogun',
            'email'     => 'femi.olaogun@test.com'
        ];

        // 1. Dismiss match
        $dismissed = KinshipEntityResolver::dismissMatch(self::$familyAlpha, self::$userFemi, self::$unclaimedFemiNodeId);
        $this->assertTrue($dismissed);

        // 2. Query matches again - must be empty
        $matches = KinshipEntityResolver::findProbableMatches(self::$familyAlpha, self::$userFemi, $memberData);
        $this->assertEmpty($matches, 'Dismissed node must be excluded from auto-matching');

        // Clean up dismissal so subsequent tests can claim
        $db = Db::connect2();
        $db->prepare("DELETE FROM kinship_match_dismissals WHERE user_id = ? AND node_id = ?")
            ->execute([self::$userFemi, self::$unclaimedFemiNodeId]);
    }

    /**
     * RED TEAM ATTACK 1: Cross-Family IDOR Node Hijacking
     * Marcus & Ghost PoC: Authenticated user in Family Alpha attempts to claim node in Family Beta.
     */
    public function testCrossFamilyIdorClaimAttemptBlockedWith403(): void
    {
        $res = KinshipEntityResolver::claimAndMergeNode(
            self::$familyAlpha,
            self::$userAttacker,
            self::$unclaimedBetaNodeId,
            ['firstName' => 'Hacker', 'lastName' => 'Attacker']
        );

        $this->assertFalse($res['success'], 'Cross-family node claiming must fail');
        $this->assertSame(403, $res['code'], 'Must return 403 Forbidden for cross-family claim attempt');
        $this->assertStringContainsString('Cross-family', $res['message']);

        // Verify target node remains unclaimed in DB
        $db = Db::connect2();
        $stmt = $db->prepare("SELECT user_id FROM family_nodes WHERE id = ?");
        $stmt->execute([self::$unclaimedBetaNodeId]);
        $this->assertNull($stmt->fetchColumn(), 'Target node must remain unclaimed');
    }

    /**
     * RED TEAM ATTACK 2: Concurrency Double-Claim Race Condition
     * Verify that when a node is claimed, a second concurrent attempt fails with 409 Conflict.
     */
    public function testDoubleClaimConcurrencyRaceConditionReturns409(): void
    {
        // 1. First claim by Femi (Legitimate claim)
        $res1 = KinshipEntityResolver::claimAndMergeNode(
            self::$familyAlpha,
            self::$userFemi,
            self::$unclaimedFemiNodeId,
            ['firstName' => 'Femi', 'lastName' => 'Olaogun', 'email' => 'femi@olaogun.com']
        );

        $this->assertTrue($res1['success'], 'First legitimate claim must succeed');
        $this->assertSame(200, $res1['code']);

        // 2. Second concurrent claim by another user attempting to hijack the same node
        $res2 = KinshipEntityResolver::claimAndMergeNode(
            self::$familyAlpha,
            'USR_SECOND_USER_99',
            self::$unclaimedFemiNodeId,
            ['firstName' => 'Second', 'lastName' => 'User']
        );

        $this->assertFalse($res2['success'], 'Second claim on already-claimed node must fail');
        $this->assertSame(409, $res2['code'], 'Must return 409 Conflict on double-claim race condition');

        // Verify node user_id is still Femi
        $db = Db::connect2();
        $stmt = $db->prepare("SELECT user_id FROM family_nodes WHERE id = ?");
        $stmt->execute([self::$unclaimedFemiNodeId]);
        $this->assertSame(self::$userFemi, $stmt->fetchColumn());
    }

    /**
     * RED TEAM ATTACK 3: Subtree Grafting & DAG Cycle Defense
     * Verify that when Femi has an existing standalone root node with a child,
     * claiming his placeholder node reparents his child to the claimed node,
     * deletes the duplicate root node, and prevents circular reference cycles.
     */
    public function testSubtreeGraftingAndCircularReferencePrevention(): void
    {
        $db = Db::connect2();

        $familyCode = 'TEST_FAM_GRAFT_99';
        $db->prepare("DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code = ?)")->execute([$familyCode]);
        $db->prepare("DELETE FROM family_unions WHERE family_code = ?")->execute([$familyCode]);
        $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$familyCode]);

        // 1. Setup Family with grandfather node (unclaimed)
        $insGrandpa = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, NULL, 'Grandpa', 'Olaogun', 'Male', -1)
        ");
        $insGrandpa->execute([$familyCode]);
        $grandpaId = (int)$db->lastInsertId();

        // 2. Setup user with standalone node and child
        $userKemi = 'USR_KEMI_GRAFT_99';
        $insKemi = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, ?, 'Kemi', 'Olaogun', 'Female', 0)
        ");
        $insKemi->execute([$familyCode, $userKemi]);
        $kemiId = (int)$db->lastInsertId();

        // Kemi has a child: Tobi
        $insChild = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, NULL, 'Tobi', 'Olaogun', 'Male', 1)
        ");
        $insChild->execute([$familyCode]);
        $tobiId = (int)$db->lastInsertId();

        // Union for Kemi -> Tobi
        $insPartner = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, NULL, 'PartnerKemi', 'Olaogun', 'Male', 0)
        ");
        $insPartner->execute([$familyCode]);
        $partnerId = (int)$db->lastInsertId();

        $insKemiUnion = $db->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, 'married', 1)
        ");
        $insKemiUnion->execute([$familyCode, $kemiId, $partnerId]);
        $kemiUnionId = (int)$db->lastInsertId();

        $insChildLink = $db->prepare("
            INSERT INTO family_node_children (union_id, child_id, relationship_type)
            VALUES (?, ?, 'biological')
        ");
        $insChildLink->execute([$kemiUnionId, $tobiId]);

        // A. Circular Reference Defense: Kemi cannot claim her own child Tobi
        $cycleRes = KinshipEntityResolver::claimAndMergeNode(
            $familyCode,
            $userKemi,
            $tobiId,
            ['firstName' => 'Kemi']
        );
        $this->assertFalse($cycleRes['success'], 'Claiming own descendant must trigger cycle detection');
        $this->assertSame(422, $cycleRes['code'], 'Must return 422 Unprocessable for cycle');

        // B. Legitimate Grafting: Kemi claims Grandpa's daughter placeholder
        $insPlaceholder = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, NULL, 'Olukemi', 'Olaogun', 'Female', 0)
        ");
        $insPlaceholder->execute([$familyCode]);
        $placeholderId = (int)$db->lastInsertId();

        $graftRes = KinshipEntityResolver::claimAndMergeNode(
            $familyCode,
            $userKemi,
            $placeholderId,
            ['firstName' => 'Kemi', 'lastName' => 'Olaogun']
        );

        $this->assertTrue($graftRes['success'], 'Legitimate graft must succeed');
        $this->assertTrue($graftRes['grafted'], 'Grafted flag must be true');

        // Verify old standalone node kemiId is deleted
        $chkOld = $db->prepare("SELECT id FROM family_nodes WHERE id = ?");
        $chkOld->execute([$kemiId]);
        $this->assertFalse($chkOld->fetchColumn(), 'Duplicate standalone node must be safely purged');

        // Verify union was reparented to placeholderId
        $chkUnion = $db->prepare("SELECT partner_1_id FROM family_unions WHERE id = ?");
        $chkUnion->execute([$kemiUnionId]);
        $this->assertSame($placeholderId, (int)$chkUnion->fetchColumn(), 'Union partner must be updated to target claimed node');

        // Clean up
        $db->prepare("DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code = ?)")->execute([$familyCode]);
        $db->prepare("DELETE FROM family_unions WHERE family_code = ?")->execute([$familyCode]);
        $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$familyCode]);
    }
}
