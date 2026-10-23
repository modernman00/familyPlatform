<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\FamilyGraphService as G;
use App\services\FamilyRelationshipRequestService as R;
use Tests\Support\OrganogramTestCase;

/**
 * Confirmation requests for half/step siblings and former partners, plus the
 * Studio child form's "who is the other parent?" picker (union_id = new).
 */
final class FamilyRelationshipRequestTest extends OrganogramTestCase
{
    private int $dad;
    private int $mum;
    private int $ego;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dad = $this->seedNode('Dad', 'Oguns', 'Male', -1, 'dad-' . $this->famCode);
        $this->mum = $this->seedNode('Mum', 'Oguns', 'Female', -1, 'mum-' . $this->famCode);
        $this->ego = $this->seedNode('Ego', 'Oguns', 'Male', 0, 'ego-' . $this->famCode);
        $u = $this->seedUnion($this->dad, $this->mum);
        $this->pdo->prepare('INSERT INTO family_node_children (union_id, child_id) VALUES (?, ?)')->execute([$u, $this->ego]);
    }

    protected function tearDown(): void
    {
        $this->pdo->prepare('DELETE FROM family_relationship_requests WHERE family_code = ?')->execute([$this->famCode]);
        parent::tearDown();
    }

    private function halfSibling(): array
    {
        $ctx = null;
        $id = G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Eniola', 'parentage' => 'Same_Mother'], $ctx);
        return [$id, $ctx];
    }

    public function testHalfSiblingAsksTheSharedParentNotTheRequester(): void
    {
        [$id, $ctx] = $this->halfSibling();
        $made = R::createFor($this->pdo, $this->famCode, 'ego-' . $this->famCode, $id, R::HALF_SIBLING, $ctx['union_id'], $ctx['shared_parent_id']);
        $this->assertCount(1, $made);
        $this->assertSame('mum-' . $this->famCode, $made[0]['approver_id']);
        $this->assertSame([], R::createFor($this->pdo, $this->famCode, 'ego-' . $this->famCode, $id, R::HALF_SIBLING, $ctx['union_id'], $ctx['shared_parent_id']), 'duplicate request must not be created');
    }

    public function testNobodyToAskMeansNoRequest(): void
    {
        $this->pdo->prepare('UPDATE family_nodes SET user_id = NULL WHERE id = ?')->execute([$this->mum]);
        [$id, $ctx] = $this->halfSibling();
        $this->assertSame([], R::createFor($this->pdo, $this->famCode, 'ego-' . $this->famCode, $id, R::HALF_SIBLING, $ctx['union_id'], $ctx['shared_parent_id']));
    }

    public function testFullSiblingNeedsNoConfirmation(): void
    {
        $this->assertNull(R::kindForParentage(G::FULL));
        $this->assertSame(R::HALF_SIBLING, R::kindForParentage(G::SAME_FATHER));
        $this->assertSame(R::STEP_SIBLING, R::kindForParentage(G::STEP));
    }

    public function testOnlyTheAddressedApproverCanAnswer(): void
    {
        [$id, $ctx] = $this->halfSibling();
        $made = R::createFor($this->pdo, $this->famCode, 'ego-' . $this->famCode, $id, R::HALF_SIBLING, $ctx['union_id'], $ctx['shared_parent_id']);
        $res = R::resolve($this->pdo, $made[0]['id'], 'someone-else', false);
        $this->assertFalse($res['ok']);
        $this->assertNotNull($this->nodeById($id), 'a stranger must not be able to remove the sibling');
    }

    public function testApproveKeepsTheSibling(): void
    {
        [$id, $ctx] = $this->halfSibling();
        $made = R::createFor($this->pdo, $this->famCode, 'ego-' . $this->famCode, $id, R::HALF_SIBLING, $ctx['union_id'], $ctx['shared_parent_id']);
        $res = R::resolve($this->pdo, $made[0]['id'], 'mum-' . $this->famCode, true);
        $this->assertSame('approved', $res['status']);
        $this->assertNotNull($this->nodeById($id));
        $this->assertFalse(R::resolve($this->pdo, $made[0]['id'], 'mum-' . $this->famCode, false)['ok'], 'already answered');
    }

    public function testDeclineRemovesTheSiblingButNothingElse(): void
    {
        [$id, $ctx] = $this->halfSibling();
        $made = R::createFor($this->pdo, $this->famCode, 'ego-' . $this->famCode, $id, R::HALF_SIBLING, $ctx['union_id'], $ctx['shared_parent_id']);
        $res = R::resolve($this->pdo, $made[0]['id'], 'mum-' . $this->famCode, false);
        $this->assertSame('declined', $res['status']);
        $this->assertNull($this->nodeById($id));
        $this->assertNotNull($this->nodeById($this->ego));
        $this->assertNotNull($this->nodeById($this->mum));
    }

    public function testDeclinedFormerPartnerWithChildrenIsNeverOrphaned(): void
    {
        $stmtNull = $this->pdo->prepare('UPDATE family_nodes SET user_id = NULL WHERE id = ?');
        $stmtNull->execute([$this->ego]);
        $p = G::upsertPartner($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Tola', 'status' => 'divorced']);
        $stmtUser = $this->pdo->prepare('UPDATE family_nodes SET user_id = ? WHERE id = ?');
        $stmtUser->execute(['tola-' . $this->famCode, $p['partner_id']]);
        $kid = G::upsertChild($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Kemi', 'with' => 'former', 'other_parent_name' => 'Tola']);
        $made = R::createFor($this->pdo, $this->famCode, 'ego-' . $this->famCode, $p['partner_id'], R::FORMER_PARTNER, $p['union_id']);
        $this->assertSame('tola-' . $this->famCode, $made[0]['approver_id']);
        R::resolve($this->pdo, $made[0]['id'], 'tola-' . $this->famCode, false);
        $this->assertNotNull($this->nodeById($p['partner_id']));
        $this->assertNotNull($this->nodeById($kid));
        $this->assertCount(1, $this->childrenOfUnion($p['union_id']));
    }

    public function testPendingListIsScopedToTheApprover(): void
    {
        [$id, $ctx] = $this->halfSibling();
        R::createFor($this->pdo, $this->famCode, 'ego-' . $this->famCode, $id, R::HALF_SIBLING, $ctx['union_id'], $ctx['shared_parent_id']);
        $this->assertCount(1, R::pendingFor($this->pdo, 'mum-' . $this->famCode));
        $this->assertCount(0, R::pendingFor($this->pdo, 'dad-' . $this->famCode));
    }

    // ---- Studio child form: who is the other parent? ----------------------

    public function testStudioChildForExistingFormerPartnerUnion(): void
    {
        $cur = G::upsertPartner($this->pdo, $this->famCode, $this->ego, ['first_name' => 'DEDE', 'status' => 'married']);
        $ex = G::upsertPartner($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Tola', 'status' => 'divorced']);
        $_POST = ['base_node_id' => (string)$this->ego, 'union_id' => (string)$ex['union_id'], 'first_name' => 'Kemi', 'gender' => 'Female'];
        $resp = $this->captureJsonOutput(fn () => $this->controller()->addChild());
        $this->assertSame('success', $resp['status'] ?? null);
        $this->assertCount(1, $this->childrenOfUnion($ex['union_id']));
        $this->assertCount(0, $this->childrenOfUnion($cur['union_id']));
    }

    public function testStudioChildWithNewNamedFormerPartner(): void
    {
        $_POST = ['base_node_id' => (string)$this->ego, 'union_id' => 'new', 'new_partner_name' => 'Ngozi Eze', 'new_partner_status' => 'divorced', 'first_name' => 'Chi', 'gender' => 'Female'];
        $resp = $this->captureJsonOutput(fn () => $this->controller()->addChild());
        $this->assertSame('success', $resp['status'] ?? null);
        $unions = G::unionsOf($this->pdo, $this->famCode, $this->ego);
        $this->assertCount(1, $unions);
        $this->assertSame('divorced', $unions[0]['union_type']);
        $this->assertSame(0, (int)$unions[0]['is_current']);
        $this->assertCount(1, $this->childrenOfUnion((int)$unions[0]['id']));
    }

    public function testStudioChildWithNewUnnamedPartnerGetsPlaceholder(): void
    {
        $_POST = ['base_node_id' => (string)$this->ego, 'union_id' => 'new', 'new_partner_name' => '', 'new_partner_status' => 'partner', 'first_name' => 'Chi'];
        $resp = $this->captureJsonOutput(fn () => $this->controller()->addChild());
        $this->assertSame('success', $resp['status'] ?? null);
        $ph = $this->pdo->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ? AND is_placeholder = 1');
        $ph->execute([$this->famCode]);
        $this->assertSame(1, (int)$ph->fetchColumn());
    }

    public function testStudioChildNewPartnerRejectsForeignBaseNode(): void
    {
        $_POST = ['base_node_id' => '999999999', 'union_id' => 'new', 'first_name' => 'Evil'];
        $resp = $this->captureJsonOutput(fn () => $this->controller()->addChild());
        $this->assertSame(403, (int)($resp['code'] ?? 0));
    }

    public function testStudioChildGarbageUnionIdIsRejected(): void
    {
        $_POST = ['union_id' => "1' OR 1=1 --", 'first_name' => 'Evil'];
        $resp = $this->captureJsonOutput(fn () => $this->controller()->addChild());
        $this->assertSame(400, (int)($resp['code'] ?? 0));
    }

    // ---- Regression: tree/studio 500 for members whose profile gender is NULL ----

    public function testClaimNodeSurvivesNullProfileGender(): void
    {
        $id = \App\services\FamilyClaimService::claimOrInitializeNode(
            $this->famCode,
            'nogender-' . $this->famCode,
            ['firstName' => 'No', 'lastName' => 'Gender', 'gender' => null, 'email' => '', 'mobile' => '']
        );
        $this->assertGreaterThan(0, $id);
        $this->assertSame('Other', $this->nodeById($id)['gender']);
    }

    // ---- Regression: relatives "vanished" after refresh / tree went blank ----

    public function testHealDuplicatesNeverMergesPlaceholdersOrOtherGenerations(): void
    {
        $ctx = null;
        G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => 'HalfM', 'parentage' => 'Same_Mother'], $ctx);
        G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => 'HalfD', 'parentage' => 'Same_Father'], $ctx);
        // grandfather and grandson share a name but are different people
        $this->seedNode('Tunde', 'Oguns', 'Male', -2);
        $this->seedNode('Tunde', 'Oguns', 'Male', 1);

        $before = count($this->familyNodes());
        $res = \App\services\KinshipEntityResolver::healDuplicateNodes($this->famCode);
        $this->assertSame(0, $res['merged_count']);
        $this->assertCount($before, $this->familyNodes());
        // half-sibling still reads as half, not as a full sibling
        $half = $this->pdo->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND first_name = 'HalfM'");
        $half->execute([$this->famCode]);
        $this->assertSame('half', G::siblingKind($this->pdo, $this->famCode, $this->ego, (int)$half->fetchColumn())['type']);
    }

    public function testOrganogramPageDeclaresLayoutUnionsInScopeBeforeUse(): void
    {
        $blade = (string)file_get_contents(__DIR__ . '/../../resources/views/member/organogram.blade.php');
        $declared = strpos($blade, 'let layoutUnions');
        $used = strpos($blade, 'unions: layoutUnions');
        $this->assertNotFalse($declared);
        $this->assertNotFalse($used);
        $this->assertLessThan($used, $declared, 'layoutUnions must be declared in the outer scope (a block-scoped const crashed the tree)');
        $this->assertStringNotContainsString('const layoutUnions', $blade);
    }
}
