<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\FamilyGraphService as G;
use Tests\Support\OrganogramTestCase;

/**
 * RFC-0001: half-siblings, former partners, placeholder parents.
 * Fixture mirrors "Developer Oguns": Eniola (same mum), Posi (same dad), coco (full), wife DEDE.
 */
final class FamilyGraphHalfSiblingTest extends OrganogramTestCase
{
    private int $ego;
    private int $mainUnion;
    private int $dad;
    private int $mum;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dad = $this->seedNode('GRAND', 'OGUNS', 'Male', -1);
        $this->mum = $this->seedNode('GRADMUM', 'OGUNS', 'Female', -1);
        $this->ego = $this->seedNode('developer', 'oguns', 'Male', 0, 'u-' . $this->famCode);
        $this->mainUnion = $this->seedUnion($this->dad, $this->mum);
        $this->pdo->prepare('INSERT INTO family_node_children (union_id, child_id) VALUES (?, ?)')->execute([$this->mainUnion, $this->ego]);
    }

    private function unionOfChild(int $child): array
    {
        $s = $this->pdo->prepare('SELECT u.* FROM family_node_children c JOIN family_unions u ON u.id = c.union_id WHERE c.child_id = ?');
        $s->execute([$child]);
        return $s->fetchAll();
    }

    public function testFullSiblingStaysInMainUnion(): void
    {
        $id = G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => 'coco', 'last_name' => 'oguns', 'parentage' => 'Same_Mother_Father']);
        $u = $this->unionOfChild($id);
        $this->assertCount(1, $u);
        $this->assertSame($this->mainUnion, (int)$u[0]['id']);
        $this->assertSame('full', G::siblingKind($this->pdo, $this->famCode, $this->ego, $id)['type']);
    }

    public function testSameMotherOnlyHangsFromMotherAndPlaceholderFather(): void
    {
        $id = G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Eniola', 'last_name' => 'Jedi', 'gender' => 'Female', 'parentage' => 'Same_Mother']);
        $u = $this->unionOfChild($id);
        $this->assertCount(1, $u);
        $this->assertNotSame($this->mainUnion, (int)$u[0]['id']);
        $this->assertContains($this->mum, [(int)$u[0]['partner_1_id'], (int)$u[0]['partner_2_id']]);
        $this->assertNotContains($this->dad, [(int)$u[0]['partner_1_id'], (int)$u[0]['partner_2_id']]);
        $kind = G::siblingKind($this->pdo, $this->famCode, $this->ego, $id);
        $this->assertSame('half', $kind['type']);
        $this->assertSame([$this->mum], $kind['shared_parents']);
    }

    public function testSameFatherOnlyHangsFromFather(): void
    {
        $id = G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Posi', 'last_name' => 'Oguns', 'parentage' => 'Same_Father', 'other_parent_name' => 'Ngozi Eze']);
        $kind = G::siblingKind($this->pdo, $this->famCode, $this->ego, $id);
        $this->assertSame('half', $kind['type']);
        $this->assertSame([$this->dad], $kind['shared_parents']);
        $other = $this->pdo->prepare("SELECT first_name FROM family_nodes WHERE family_code = ? AND first_name = 'Ngozi'");
        $other->execute([$this->famCode]);
        $this->assertNotFalse($other->fetchColumn());
    }

    public function testResaveIsIdempotentAndRehomesOnParentageChange(): void
    {
        $d = ['first_name' => 'Eniola', 'last_name' => 'Jedi', 'parentage' => 'Same_Mother_Father'];
        $id1 = G::upsertSibling($this->pdo, $this->famCode, $this->ego, $d);
        $d['parentage'] = 'Same_Mother';
        $id2 = G::upsertSibling($this->pdo, $this->famCode, $this->ego, $d);
        $id3 = G::upsertSibling($this->pdo, $this->famCode, $this->ego, $d);
        $this->assertSame($id1, $id2);
        $this->assertSame($id1, $id3);
        $this->assertCount(1, $this->unionOfChild($id1), 'a sibling must hang from exactly one union');
        $this->assertSame('half', G::siblingKind($this->pdo, $this->famCode, $this->ego, $id1)['type']);
        $count = $this->pdo->prepare("SELECT COUNT(*) FROM family_nodes WHERE family_code = ? AND first_name = 'Eniola'");
        $count->execute([$this->famCode]);
        $this->assertSame(1, (int)$count->fetchColumn());
    }

    public function testUnnamedHalfSiblingsShareOnePlaceholderUnion(): void
    {
        $a = G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => 'A', 'parentage' => 'Same_Mother']);
        $b = G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => 'B', 'parentage' => 'Same_Mother']);
        $this->assertSame((int)$this->unionOfChild($a)[0]['id'], (int)$this->unionOfChild($b)[0]['id']);
    }

    public function testEgoWithNoParentsGetsPlaceholdersSoHalfSiblingStillHangs(): void
    {
        $solo = $this->seedNode('Solo', 'Person', 'Male', 0);
        $id = G::upsertSibling($this->pdo, $this->famCode, $solo, ['first_name' => 'Half', 'parentage' => 'Same_Father']);
        $this->assertSame('half', G::siblingKind($this->pdo, $this->famCode, $solo, $id)['type']);
        $ph = $this->pdo->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ? AND is_placeholder = 1');
        $ph->execute([$this->famCode]);
        $this->assertGreaterThanOrEqual(2, (int)$ph->fetchColumn());
    }

    public function testEmptyNameIsRejectedWithoutWrites(): void
    {
        $before = count($this->familyNodes());
        $this->assertSame(0, G::upsertSibling($this->pdo, $this->famCode, $this->ego, ['first_name' => '  ']));
        $this->assertCount($before, $this->familyNodes());
    }

    public function testForeignFamilyNodeCannotBeUsedAsBase(): void
    {
        $this->assertSame(0, G::upsertSibling($this->pdo, 'SOME_OTHER_FAMILY', $this->ego, ['first_name' => 'Evil']));
    }

    public function testFormerPartnerWithSharedChildAndCurrentSpouseCoexist(): void
    {
        $cur = G::upsertPartner($this->pdo, $this->famCode, $this->ego, ['first_name' => 'DEDE', 'last_name' => 'Oguns', 'gender' => 'Female', 'status' => 'married']);
        $kid = G::upsertChild($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Kemi', 'with' => 'former', 'other_parent_name' => 'Tola Ade', 'union_status' => 'divorced']);
        $kid2 = G::upsertChild($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Dayo', 'with' => 'spouse']);

        $unions = G::unionsOf($this->pdo, $this->famCode, $this->ego);
        $this->assertCount(2, $unions);
        $byType = array_column($unions, null, 'union_type');
        $this->assertSame(0, (int)$byType['divorced']['is_current']);
        $this->assertSame(1, (int)$byType['married']['is_current']);
        $this->assertSame((int)$byType['divorced']['id'], (int)$this->unionOfChild($kid)[0]['id']);
        $this->assertSame($cur['union_id'], (int)$this->unionOfChild($kid2)[0]['id']);
        // children of different mothers are half-siblings of each other
        $this->assertSame('half', G::siblingKind($this->pdo, $this->famCode, $kid, $kid2)['type']);
    }

    public function testResavingSpouseDoesNotStackDuplicateUnions(): void
    {
        G::upsertPartner($this->pdo, $this->famCode, $this->ego, ['first_name' => 'DEDE', 'status' => 'married']);
        G::upsertPartner($this->pdo, $this->famCode, $this->ego, ['first_name' => 'DEDE', 'status' => 'married']);
        G::upsertPartner($this->pdo, $this->famCode, $this->ego, ['first_name' => 'Dede', 'last_name' => 'Oguns', 'status' => 'married']);
        $this->assertCount(1, G::unionsOf($this->pdo, $this->famCode, $this->ego));
    }

    public function testParentsDivorcedKeepsBothParentsOnMainUnion(): void
    {
        G::setParentsStatus($this->pdo, $this->famCode, $this->ego, 'divorced', 1999);
        $u = $this->unionOfChild($this->ego)[0];
        $this->assertSame('divorced', $u['union_type']);
        $this->assertSame(0, (int)$u['is_current']);
        $this->assertSame(1999, (int)$u['divorce_year']);
    }

    public function testInvalidEnumsFallBackSafely(): void
    {
        $this->assertSame('partner', G::normaliseUnionType('other'));
        $this->assertSame('full', G::normaliseParentage("'; DROP TABLE x;--"));
    }
}
