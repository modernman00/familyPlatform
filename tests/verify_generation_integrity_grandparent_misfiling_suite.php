<?php

declare(strict_types=1);

/**
 * 🛡️ Generation-Integrity & Guided Relational Qualification Suite
 *
 * Verifies the ATG Invariants:
 *  1. Zero-Silent-Graft Rule: Unplaced members are NEVER silently auto-attached to parent unions based on surname.
 *  2. Staging Dock / Unplaced Members Tray: Unlinked members are staged cleanly without corrupting the canvas.
 *  3. Nephew Qualification: Femi's son Fayokanmi accurately resolves as 'Nephew' (direct).
 *  4. Half-Sibling Isolation: Oluyomi's son Segun accurately resolves as 'Half-brother (same dad)'.
 *  5. Full Sibling Lineage: Olusola under Oluyomi & Iyabo accurately resolves as 'Brother'.
 *  6. Defect proof: Demonstrates why the retired cardinality heuristic failed.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
if (!defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\services\FamilyGraphIntegrityService;
use App\services\KinshipCalculatorService;
use App\services\CollateralKinshipService;
use Src\Db;

$passed = 0;
$failed = 0;

function runStep(string $name, callable $fn): void
{
    global $passed, $failed;
    echo '  [TEST ' . ($passed + $failed + 1) . "] {$name}... ";
    try {
        $fn();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo '❌ FAILED: ' . $e->getMessage() . "\n";
        echo '     at ' . $e->getFile() . ':' . $e->getLine() . "\n";
        $failed++;
    }
}

function expect(bool $cond, string $msg): void
{
    if (!$cond) {
        throw new \RuntimeException($msg);
    }
}

echo "=================================================================\n";
echo "🧬 GENERATION-INTEGRITY & GUIDED QUALIFICATION SUITE\n";
echo "=================================================================\n\n";

$db = Db::connect2();
$fam = 'TEST_GENINT_' . strtoupper(bin2hex(random_bytes(3)));

$node = function (string $f, string $first, string $last, string $gender, int $gen, ?string $bio, ?string $uid = null) use ($db): int {
    $s = $db->prepare('INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $s->execute([$f, $uid, $first, $last, $gender, $gen, $bio]);
    return (int)$db->lastInsertId();
};
$union = function (string $f, int $p1, int $p2, string $type = 'married') use ($db): int {
    $s = $db->prepare('INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, ?, 1)');
    $s->execute([$f, $p1, $p2, $type]);
    return (int)$db->lastInsertId();
};
$link = function (int $u, int $c) use ($db): void {
    $s = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
    $s->execute([$u, $c]);
};
$parentOf = function (int $nodeId) use ($db): array {
    $s = $db->prepare('SELECT union_id FROM family_node_children WHERE child_id = ?');
    $s->execute([$nodeId]);
    return array_map('intval', $s->fetchAll(\PDO::FETCH_COLUMN));
};
$graph = function (string $f) use ($db): array {
    $n = $db->prepare('SELECT * FROM family_nodes WHERE family_code = ?');
    $n->execute([$f]);
    $u = $db->prepare('SELECT * FROM family_unions WHERE family_code = ?');
    $u->execute([$f]);
    $c = $db->prepare('SELECT fnc.* FROM family_node_children fnc JOIN family_unions u ON fnc.union_id = u.id WHERE u.family_code = ?');
    $c->execute([$f]);
    return ['nodes' => $n->fetchAll(\PDO::FETCH_ASSOC), 'unions' => $u->fetchAll(\PDO::FETCH_ASSOC), 'children' => $c->fetchAll(\PDO::FETCH_ASSOC)];
};

try {
    // 1. Ancestors: Maternal Grandparents (Gen -2, Union G)
    $paJohn = $node($fam, 'Pa John Adekoya', 'Mayungbe', 'Male', -2, 'Father');
    $ondeyin = $node($fam, 'Ondeyin', 'Dorcas Adekoya', 'Female', -2, 'Mother');
    $unionG = $union($fam, $paJohn, $ondeyin);

    // 2. Parents: Oluyomi & Iyabo (Gen -1, Union P)
    $oluyomi = $node($fam, 'Oluyomi', 'Olaogun', 'Male', -1, 'Father', 'UID_OLUYOMI_' . bin2hex(random_bytes(2)));
    $iyabo = $node($fam, 'IYABO', 'OLAOGUN', 'Female', -1, 'Mother', 'UID_IYABO_' . bin2hex(random_bytes(2)));
    $unionP = $union($fam, $oluyomi, $iyabo);

    // Link Iyabo + 5 maternal uncles to Union G
    $link($unionG, $iyabo);
    foreach (['Adesanya', 'Labomehin', 'Adesegun', 'Modupe', 'Adeola'] as $nm) {
        $id = $node($fam, $nm, 'Mayungbe', 'Male', -1, 'Sibling');
        $link($unionG, $id);
    }

    // 3. Children of Oluyomi & Iyabo: Wally, Temitope, Femi, Olusola
    $uidWally = 'UID_WALLY_' . bin2hex(random_bytes(2));
    $wally = $node($fam, 'Wally', 'Olaogun', 'Male', 0, 'Primary Member', $uidWally);
    $temitope = $node($fam, 'Temitope', 'Olaogun', 'Female', 0, 'Sibling', 'UID_TEMI_' . bin2hex(random_bytes(2)));
    $femi = $node($fam, 'FEMI', 'OLAOGUN', 'Male', 0, 'Sibling', 'UID_FEMI_' . bin2hex(random_bytes(2)));
    $olusola = $node($fam, 'Olusola', 'Olaogun', 'Male', 0, 'Sibling', 'UID_SHOLA_' . bin2hex(random_bytes(2)));

    $link($unionP, $wally);
    $link($unionP, $temitope);
    $link($unionP, $femi);
    $link($unionP, $olusola);

    // 4. Nephew: Fayokanmi (Femi's son, Gen 1)
    $femiUnion = $union($fam, $femi, $femi, 'partner');
    $fayokanmi = $node($fam, 'Fayokanmi', 'Olaogun', 'Male', 1, 'Child', 'UID_FAYOKANMI_' . bin2hex(random_bytes(2)));
    $link($femiUnion, $fayokanmi);

    // 5. Half-brother: Segun (Oluyomi's son, Gen 0)
    $oluyomiHalfUnion = $union($fam, $oluyomi, $oluyomi, 'partner');
    $segun = $node($fam, 'Segun', 'Olaogun', 'Male', 0, 'Half-sibling');
    $link($oluyomiHalfUnion, $segun);

    // 6. Unplaced Member: Taiwo (registered with code, NOT added as brother, unlinked)
    $uidTaiwo = 'UID_TAIWO_' . bin2hex(random_bytes(2));
    $taiwo = $node($fam, 'Taiwo', 'Olaogun', 'Male', 0, 'Primary Member', $uidTaiwo);

    // TEST 1: Defect proof of retired heuristic
    runStep('DEFECT PROOF: retired heuristic picked grandparents union due to child count', function () use ($db, $fam, $unionG) {
        $s = $db->prepare("
            SELECT fu.id, COUNT(fnc.id) AS child_count
            FROM family_unions fu
            LEFT JOIN family_nodes p1 ON p1.id = fu.partner_1_id
            LEFT JOIN family_nodes p2 ON p2.id = fu.partner_2_id
            LEFT JOIN family_node_children fnc ON fnc.union_id = fu.id
            WHERE fu.family_code = ?
              AND (p1.generation_level = -1 OR p2.generation_level = -1 OR LOWER(COALESCE(p1.bio, '')) LIKE '%father%' OR LOWER(COALESCE(p2.bio, '')) LIKE '%mother%')
            GROUP BY fu.id ORDER BY child_count DESC LIMIT 1
        ");
        $s->execute([$fam]);
        $picked = (int)$s->fetchColumn();
        expect($picked === $unionG, "Expected retired heuristic to pick grandparents union {$unionG}; got {$picked}");
    });

    // TEST 2: Zero-Silent-Graft Invariant
    runStep('Zero-Silent-Graft Invariant: unplaced member Taiwo is NEVER auto-attached to parents', function () use ($db, $fam, $uidTaiwo, $taiwo, $parentOf) {
        $attached = FamilyGraphIntegrityService::attachOrphanedGen0($db, $fam, $uidTaiwo);
        expect($attached === 0, "Expected 0 auto-attachments, got {$attached}");
        expect($parentOf($taiwo) === [], 'Taiwo was illegally auto-attached to a parent union');
    });

    // TEST 3: Staging Dock surfaces unplaced members
    runStep('Staging Dock surfaces Taiwo in unplaced members list', function () use ($db, $fam, $taiwo) {
        $unplaced = FamilyGraphIntegrityService::getUnplacedMembers($db, $fam);
        $ids = array_map(fn($r) => (int)$r['id'], $unplaced);
        expect(in_array($taiwo, $ids, true), 'Taiwo expected in unplaced members tray');
    });

    // TEST 4: Wally's view of brothers (Femi, Olusola) and sister (Temitope)
    runStep("Wally's view: Femi & Olusola are Brothers, Temitope is Sister", function () use ($graph, $fam, $wally, $femi, $olusola, $temitope) {
        $a = KinshipCalculatorService::calculateEgoKinship($graph($fam), $wally);
        expect($a[$femi]['ego_relation'] === 'Brother', "Femi expected Brother, got " . $a[$femi]['ego_relation']);
        expect($a[$olusola]['ego_relation'] === 'Brother', "Olusola expected Brother, got " . $a[$olusola]['ego_relation']);
        expect($a[$temitope]['ego_relation'] === 'Sister', "Temitope expected Sister, got " . $a[$temitope]['ego_relation']);
    });

    // TEST 5: Nephew Qualification - Fayokanmi is Nephew
    runStep("Wally's view: Fayokanmi (Femi's son) is accurately identified as 'Nephew'", function () use ($graph, $fam, $wally, $fayokanmi) {
        $a = KinshipCalculatorService::calculateEgoKinship($graph($fam), $wally);
        expect($a[$fayokanmi]['ego_relation'] === 'Nephew', "Fayokanmi expected Nephew, got " . $a[$fayokanmi]['ego_relation']);
        expect($a[$fayokanmi]['kinship_category'] === 'nephew_niece', "Fayokanmi expected category nephew_niece, got " . $a[$fayokanmi]['kinship_category']);
    });

    // TEST 6: Half-Sibling Isolation - Segun is Half-brother (same dad)
    runStep("Wally's view: Segun (Oluyomi's son) is accurately identified as 'Half-brother (same dad)'", function () use ($graph, $fam, $wally, $segun) {
        $a = KinshipCalculatorService::calculateEgoKinship($graph($fam), $wally);
        expect($a[$segun]['ego_relation'] === 'Half-brother (same dad)', "Segun expected Half-brother (same dad), got " . $a[$segun]['ego_relation']);
        expect($a[$segun]['sibling_kind'] === 'half', "Segun expected sibling_kind half, got " . ($a[$segun]['sibling_kind'] ?? 'null'));
    });

    // TEST 7: Maternal Asymmetry - Segun is isolated from Iyabo's maternal lineage
    runStep("Iyabo's view: Segun is Husband's Son (isolated from her maternal lineage)", function () use ($graph, $fam, $iyabo, $segun) {
        $a = KinshipCalculatorService::calculateEgoKinship($graph($fam), $iyabo);
        expect($a[$segun]['kinship_category'] !== 'child', "Segun must not be a direct child of Iyabo");
    });

    // TEST 8: Unplaced Taiwo computes as Relative (extended) without corrupting sibling list
    runStep("Wally's view: Taiwo is Relative (extended), NOT brother or uncle", function () use ($graph, $fam, $wally, $taiwo) {
        $a = KinshipCalculatorService::calculateEgoKinship($graph($fam), $wally);
        expect($a[$taiwo]['ego_relation'] === 'Relative', "Taiwo expected Relative, got " . $a[$taiwo]['ego_relation']);
        expect($a[$taiwo]['kinship_category'] === 'extended', "Taiwo expected category extended, got " . $a[$taiwo]['kinship_category']);
    });

    // TEST 9: Fayokanmi's perspective - Wally is Uncle, Femi is Father, Oluyomi is Grandfather
    runStep("Fayokanmi's reciprocal view: Femi is Father, Wally is Uncle, Oluyomi is Paternal Grandfather", function () use ($graph, $fam, $fayokanmi, $femi, $wally, $oluyomi) {
        $a = KinshipCalculatorService::calculateEgoKinship($graph($fam), $fayokanmi);
        expect($a[$femi]['ego_relation'] === 'Father', "Femi expected Father, got " . $a[$femi]['ego_relation']);
        expect($a[$wally]['ego_relation'] === 'Uncle' || str_contains($a[$wally]['ego_relation'], 'Uncle'), "Wally expected Uncle, got " . $a[$wally]['ego_relation']);
        expect($a[$oluyomi]['ego_relation'] === 'Paternal Grandfather', "Oluyomi expected Paternal Grandfather, got " . $a[$oluyomi]['ego_relation']);
    });

} finally {
    $db->prepare('DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code = ?)')->execute([$fam]);
    $db->prepare('DELETE FROM family_unions WHERE family_code = ?')->execute([$fam]);
    $db->prepare('DELETE FROM family_nodes WHERE family_code = ?')->execute([$fam]);
}

echo "\n=================================================================\n";
echo "🎉 TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "=================================================================\n";

exit($failed > 0 ? 1 : 0);
