<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\CollateralKinshipService;
use App\services\FamilyGraphService;
use Tests\Support\OrganogramTestCase;

/**
 * Regression: a union between a person and their own mother (Laf123 tree) dragged the mother into the
 * child's row and overlapped the primary member.
 */
final class ParentChildUnionGuardTest extends OrganogramTestCase
{
    public function test_create_union_refuses_parent_child_pair(): void
    {
        $dad = $this->seedNode('Dad', 'X', 'Male', -1);
        $mum = $this->seedNode('Mum', 'X', 'Female', -1);
        $kid = $this->seedNode('Kid', 'X', 'Female', 0);
        $parents = FamilyGraphService::createUnion($this->pdo, $this->famCode, $dad, $mum);
        $this->pdo->prepare('INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, ?)')
            ->execute([$parents, $kid, 'biological']);

        $this->expectException(\DomainException::class);
        FamilyGraphService::createUnion($this->pdo, $this->famCode, $kid, $mum);
    }

    public function test_create_union_allows_unrelated_partners_and_single_parent_shell(): void
    {
        $a = $this->seedNode('A', 'X', 'Male');
        $b = $this->seedNode('B', 'X', 'Female');
        $this->assertGreaterThan(0, FamilyGraphService::createUnion($this->pdo, $this->famCode, $a, $b));
        $this->assertGreaterThan(0, FamilyGraphService::createUnion($this->pdo, $this->famCode, $a, $a));
    }

    public function test_drop_parent_child_unions_hides_corrupt_rows_only(): void
    {
        $unions = [
            ['id' => 1, 'partner_1_id' => 10, 'partner_2_id' => 11], // parents
            ['id' => 2, 'partner_1_id' => 12, 'partner_2_id' => 11], // child + own mother (corrupt)
            ['id' => 3, 'partner_1_id' => 12, 'partner_2_id' => 12], // single-parent shell
            ['id' => 4, 'partner_1_id' => 12, 'partner_2_id' => 13], // genuine spouse
        ];
        $children = [['union_id' => 1, 'child_id' => 12]];

        $kept = array_column(CollateralKinshipService::dropParentChildUnions($unions, $children), 'id');

        $this->assertSame([1, 3, 4], $kept);
    }
}
