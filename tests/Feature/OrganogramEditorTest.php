<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\OrganogramTestCase;

/**
 * Covers OrganogramEditorController's three tree-building actions against the
 * real dev database: addPartner (node + union), addChild (node + child link
 * off an existing union) and addParents (father + mother + union + links the
 * base node in as their child).
 *
 * Also pins the IDOR guards — every action refuses a base_node_id / union_id
 * that belongs to a different family_code — and the "first name required" /
 * "at least one parent" input rules.
 */
final class OrganogramEditorTest extends OrganogramTestCase
{
    // ---- addPartner -------------------------------------------------------

    public function test_add_partner_creates_node_and_union(): void
    {
        $baseId = $this->seedNode('Ada', 'Lovelace', 'Female', 2);

        $_POST = [
            'base_node_id' => (string) $baseId,
            'first_name' => 'Charles',
            'last_name' => 'Babbage',
            'gender' => 'Male',
            'is_current' => 'yes',
            'marriage_year' => '1835',
        ];

        $response = $this->captureJsonOutput(fn () => $this->controller()->addPartner());

        $this->assertSame('success', $response['status'] ?? null);
        $this->assertSame('Partner added successfully.', $response['message'] ?? null);

        $partner = null;
        foreach ($this->familyNodes() as $node) {
            if ($node['first_name'] === 'Charles' && $node['last_name'] === 'Babbage') {
                $partner = $node;
            }
        }
        $this->assertNotNull($partner, 'Expected the partner node to be inserted.');
        $this->assertSame((int) $partner['generation_level'], 2, 'Partner inherits the base node generation.');

        $union = $this->latestUnion();
        $this->assertNotNull($union);
        $this->assertSame((int) $union['partner_1_id'], $baseId);
        $this->assertSame((int) $union['partner_2_id'], (int) $partner['id']);
        $this->assertSame('married', $union['union_type']);
        $this->assertSame(1, (int) $union['is_current']);
        $this->assertSame(1835, (int) $union['marriage_year']);
    }

    public function test_add_partner_marks_union_divorced_when_a_divorce_year_is_given(): void
    {
        $baseId = $this->seedNode();

        $_POST = [
            'base_node_id' => (string) $baseId,
            'first_name' => 'Ex',
            'divorce_year' => '1990',
        ];

        $response = $this->captureJsonOutput(fn () => $this->controller()->addPartner());

        $this->assertSame('success', $response['status'] ?? null);
        $this->assertSame('divorced', $this->latestUnion()['union_type'] ?? null);
    }

    public function test_add_partner_rejects_a_base_node_from_another_family(): void
    {
        // A node that exists, but not under this request's family code.
        $stmt = $this->pdo->prepare(
            'INSERT INTO family_nodes (family_code, first_name, last_name, gender) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute(['PHPUNIT_OTHERFAM', 'Not', 'Yours', 'Male']);
        $foreignId = (int) $this->pdo->lastInsertId();

        try {
            $_POST = ['base_node_id' => (string) $foreignId, 'first_name' => 'Mallory'];

            $response = $this->captureJsonOutput(fn () => $this->controller()->addPartner());

            $this->assertSame('error', $response['status'] ?? null);
            $this->assertSame(403, (int) ($response['code'] ?? 0));
            $this->assertCount(0, $this->familyNodes(), 'No node should have been created for the attacker.');
        } finally {
            $this->pdo->prepare('DELETE FROM family_nodes WHERE id = ?')->execute([$foreignId]);
        }
    }

    public function test_add_partner_requires_a_first_name(): void
    {
        $baseId = $this->seedNode();

        $_POST = ['base_node_id' => (string) $baseId, 'first_name' => '   '];

        $response = $this->captureJsonOutput(fn () => $this->controller()->addPartner());

        $this->assertSame('error', $response['status'] ?? null);
        $this->assertStringContainsStringIgnoringCase('first name', (string) ($response['message'] ?? ''));
    }

    public function test_add_partner_rejects_a_non_post_request(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $response = $this->captureJsonOutput(fn () => $this->controller()->addPartner());

        $this->assertSame('error', $response['status'] ?? null);
        $this->assertSame(405, (int) ($response['code'] ?? 0));
    }

    // ---- addChild -------------------------------------------------------

    public function test_add_child_inserts_node_one_generation_below_the_union(): void
    {
        $fatherId = $this->seedNode('Homer', 'Simpson', 'Male', 3);
        $motherId = $this->seedNode('Marge', 'Simpson', 'Female', 3);
        $unionId = $this->seedUnion($fatherId, $motherId);

        $_POST = [
            'union_id' => (string) $unionId,
            'first_name' => 'Bart',
            'last_name' => 'Simpson',
            'gender' => 'Male',
        ];

        $response = $this->captureJsonOutput(fn () => $this->controller()->addChild());

        $this->assertSame('success', $response['status'] ?? null);
        $this->assertSame('Child added successfully.', $response['message'] ?? null);

        $child = null;
        foreach ($this->familyNodes() as $node) {
            if ($node['first_name'] === 'Bart') {
                $child = $node;
            }
        }
        $this->assertNotNull($child);
        $this->assertSame(4, (int) $child['generation_level'], 'Child sits one generation below its parents.');

        $links = $this->childrenOfUnion($unionId);
        $this->assertCount(1, $links);
        $this->assertSame((int) $links[0]['child_id'], (int) $child['id']);
        $this->assertSame('biological', $links[0]['relationship_type']);
    }

    public function test_add_child_rejects_an_invalid_union_id(): void
    {
        $_POST = ['union_id' => '0', 'first_name' => 'Nobody'];

        $response = $this->captureJsonOutput(fn () => $this->controller()->addChild());

        $this->assertSame('error', $response['status'] ?? null);
        $this->assertSame(400, (int) ($response['code'] ?? 0));
    }

    public function test_add_child_rejects_a_union_from_another_family(): void
    {
        // Union whose nodes live under a different family code.
        $stmt = $this->pdo->prepare(
            'INSERT INTO family_nodes (family_code, first_name, last_name, gender) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute(['PHPUNIT_OTHERFAM', 'Foreign', 'Dad', 'Male']);
        $p1 = (int) $this->pdo->lastInsertId();
        $stmt->execute(['PHPUNIT_OTHERFAM', 'Foreign', 'Mum', 'Female']);
        $p2 = (int) $this->pdo->lastInsertId();
        $this->pdo->prepare(
            'INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, ?, 1)'
        )->execute(['PHPUNIT_OTHERFAM', $p1, $p2, 'married']);
        $foreignUnionId = (int) $this->pdo->lastInsertId();

        try {
            $_POST = ['union_id' => (string) $foreignUnionId, 'first_name' => 'Mallory'];

            $response = $this->captureJsonOutput(fn () => $this->controller()->addChild());

            $this->assertSame('error', $response['status'] ?? null);
            $this->assertSame(403, (int) ($response['code'] ?? 0));
        } finally {
            $this->pdo->prepare('DELETE FROM family_unions WHERE id = ?')->execute([$foreignUnionId]);
            $this->pdo->prepare('DELETE FROM family_nodes WHERE id IN (?, ?)')->execute([$p1, $p2]);
        }
    }

    // ---- addParents ----------------------------------------------------

    public function test_add_parents_creates_father_mother_union_and_links_base_node(): void
    {
        $baseId = $this->seedNode('Kid', 'Person', 'Male', 5);

        $_POST = [
            'base_node_id' => (string) $baseId,
            'father_first_name' => 'Grand',
            'father_last_name' => 'Pa',
            'mother_first_name' => 'Grand',
            'mother_last_name' => 'Ma',
        ];

        $response = $this->captureJsonOutput(fn () => $this->controller()->addParents());

        $this->assertSame('success', $response['status'] ?? null);
        $this->assertSame('Parents added successfully.', $response['message'] ?? null);

        $nodes = $this->familyNodes();
        $names = array_map(static fn ($n) => $n['first_name'] . ' ' . $n['last_name'], $nodes);
        $this->assertContains('Grand Pa', $names);
        $this->assertContains('Grand Ma', $names);

        foreach ($nodes as $node) {
            if (in_array($node['first_name'] . ' ' . $node['last_name'], ['Grand Pa', 'Grand Ma'], true)) {
                $this->assertSame(4, (int) $node['generation_level'], 'Parents sit one generation above the base node.');
            }
        }

        $union = $this->latestUnion();
        $this->assertNotNull($union);
        $links = $this->childrenOfUnion((int) $union['id']);
        $this->assertCount(1, $links);
        $this->assertSame((int) $links[0]['child_id'], $baseId, 'The base node is linked in as the new union\'s child.');
    }

    public function test_add_parents_fills_in_an_unknown_partner_when_only_one_parent_is_given(): void
    {
        $baseId = $this->seedNode('Only', 'Child', 'Female', 1);

        $_POST = [
            'base_node_id' => (string) $baseId,
            'mother_first_name' => 'Solo',
            'mother_last_name' => 'Mum',
        ];

        $response = $this->captureJsonOutput(fn () => $this->controller()->addParents());

        $this->assertSame('success', $response['status'] ?? null);

        $names = array_map(
            static fn ($n) => $n['first_name'] . ' ' . $n['last_name'],
            $this->familyNodes(),
        );
        $this->assertContains('Solo Mum', $names);
        $this->assertContains('Unknown Father', $names, 'Missing father is backfilled so the union is structurally sound.');
    }

    public function test_add_parents_requires_at_least_one_parent_name(): void
    {
        $baseId = $this->seedNode();

        $_POST = ['base_node_id' => (string) $baseId];

        $response = $this->captureJsonOutput(fn () => $this->controller()->addParents());

        $this->assertSame('error', $response['status'] ?? null);
        $this->assertSame(400, (int) ($response['code'] ?? 0));
        $this->assertCount(1, $this->familyNodes(), 'Only the seeded base node should exist.');
    }
}
