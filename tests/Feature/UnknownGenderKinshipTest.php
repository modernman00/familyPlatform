<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\KinshipCalculatorService;
use Tests\Support\OrganogramTestCase;

/**
 * Regression: unknown gender ('Other'/blank) must never render as Brother/Wife/etc.
 * (Wally shown as a gendered sibling, Bolatito shown as "Brother").
 */
final class UnknownGenderKinshipTest extends OrganogramTestCase
{
    /** @return array<string, string> node id => ego_relation */
    private function titlesFor(string $egoGender, string $spouseGender, string $sibGender): array
    {
        $ego = $this->seedNode('Ajibike', 'Olaogun', $egoGender);
        $spouse = $this->seedNode('Wally', 'Olaogun', $spouseGender);
        $sib = $this->seedNode('Bolatito', 'Isibor', $sibGender);
        $mum = $this->seedNode('Mum', 'Akinduro', 'Female');
        $dad = $this->seedNode('Dad', 'Akinduro', 'Male');

        $pdo = $this->pdo;
        $union = static function (int $a, int $b, string $type) use ($pdo, $ego): int {
            $pdo->prepare('INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, ?, 1)')
                ->execute([$_SESSION['famCode'], $a, $b, $type]);
            return (int) $pdo->lastInsertId();
        };
        $marriage = $union($ego, $spouse, 'married');
        $parents = $union($dad, $mum, 'married');
        $link = $pdo->prepare('INSERT INTO family_node_children (union_id, child_id) VALUES (?, ?)');
        $link->execute([$parents, $ego]);
        $link->execute([$parents, $sib]);

        $graph = [
            'nodes' => $this->familyNodes(),
            'unions' => $pdo->query("SELECT * FROM family_unions WHERE family_code = " . $pdo->quote($this->famCode))->fetchAll(\PDO::FETCH_ASSOC),
            'children' => $pdo->query("SELECT c.* FROM family_node_children c JOIN family_unions u ON u.id = c.union_id WHERE u.family_code = " . $pdo->quote($this->famCode))->fetchAll(\PDO::FETCH_ASSOC),
        ];
        $out = KinshipCalculatorService::calculateEgoKinship($graph, $ego);

        return [
            'spouse' => (string) ($out[$spouse]['ego_relation'] ?? ''),
            'sibling' => (string) ($out[$sib]['ego_relation'] ?? ''),
            'marriage' => (string) $marriage,
        ];
    }

    public function test_known_genders_keep_gendered_titles(): void
    {
        $t = $this->titlesFor('Female', 'Male', 'Female');
        $this->assertSame('Husband', $t['spouse']);
        $this->assertSame('Sister', $t['sibling']);
    }

    public function test_unknown_gender_gets_neutral_titles_never_a_guess(): void
    {
        $t = $this->titlesFor('Female', 'Other', 'Other');
        $this->assertSame('Spouse', $t['spouse']);
        $this->assertSame('Sibling', $t['sibling']);
    }

    public function test_neutralise_title_handles_compounds_and_prefixes(): void
    {
        $this->assertSame('Sibling-in-law', KinshipCalculatorService::neutraliseTitle('Sister-in-law'));
        $this->assertSame('Paternal Grandparent', KinshipCalculatorService::neutraliseTitle('Paternal Grandfather'));
        $this->assertSame('Great-Grandparent', KinshipCalculatorService::neutraliseTitle('Great-Grandmother'));
    }

    public function test_has_known_gender(): void
    {
        $this->assertTrue(KinshipCalculatorService::hasKnownGender('Male'));
        $this->assertTrue(KinshipCalculatorService::hasKnownGender(' female '));
        foreach (['Other', '', 'Unknown', null] as $v) {
            $this->assertFalse(KinshipCalculatorService::hasKnownGender($v));
        }
    }

    // ---- setGender endpoint ------------------------------------------------

    public function test_set_gender_updates_unknown_node(): void
    {
        $id = $this->seedNode('Bolatito', 'Isibor', 'Other');
        $_POST = ['node_id' => (string) $id, 'gender' => 'Female'];

        $res = $this->captureJsonOutput(fn () => $this->controller()->setGender());

        $this->assertSame('success', $res['status'] ?? null);
        $this->assertSame('Female', $this->nodeById($id)['gender']);
    }

    public function test_set_gender_refuses_to_override_known_gender(): void
    {
        $id = $this->seedNode('Wally', 'Olaogun', 'Male');
        $_POST = ['node_id' => (string) $id, 'gender' => 'Female'];

        $res = $this->captureJsonOutput(fn () => $this->controller()->setGender());

        $this->assertSame(409, (int) ($res['code'] ?? 0));
        $this->assertSame('Male', $this->nodeById($id)['gender']);
    }

    public function test_set_gender_rejects_invalid_value(): void
    {
        $id = $this->seedNode('X', 'Y', 'Other');
        $_POST = ['node_id' => (string) $id, 'gender' => 'Banana'];

        $res = $this->captureJsonOutput(fn () => $this->controller()->setGender());

        $this->assertSame(400, (int) ($res['code'] ?? 0));
        $this->assertSame('Other', $this->nodeById($id)['gender']);
    }

    public function test_set_gender_blocks_cross_family_idor(): void
    {
        $this->pdo->prepare('INSERT INTO family_nodes (family_code, first_name, last_name, gender) VALUES (?, ?, ?, ?)')
            ->execute(['PHPUNIT_OTHERFAM', 'Not', 'Yours', 'Other']);
        $foreignId = (int) $this->pdo->lastInsertId();

        try {
            $_POST = ['node_id' => (string) $foreignId, 'gender' => 'Male'];
            $res = $this->captureJsonOutput(fn () => $this->controller()->setGender());

            $this->assertSame(403, (int) ($res['code'] ?? 0));
            $g = $this->pdo->prepare('SELECT gender FROM family_nodes WHERE id = ?');
            $g->execute([$foreignId]);
            $this->assertSame('Other', $g->fetchColumn());
        } finally {
            $this->pdo->prepare('DELETE FROM family_nodes WHERE id = ?')->execute([$foreignId]);
        }
    }
}
