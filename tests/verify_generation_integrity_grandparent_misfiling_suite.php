<?php

declare(strict_types=1);

/**
 * 🛡️ Generation-Integrity Regression Suite (production incident, Nov 2026)
 *
 * Reproduces the EXACT production topology that rendered a member's own brothers as
 * "Maternal Parent's Sibling" / "Maternal Uncle":
 *
 *   Pa John + Ondeyin   (gen -2, union G)  -> children: Iyabo + 5 Mayungbe siblings  (6 children)
 *   Oluyomi + Iyabo     (gen -1, union P)  -> children: Wally, Temitope, Femi          (3 children)
 *   Olusola, Taiwo      (gen  0, registered members, surname Olaogun, no parent link)
 *
 * The retired heuristic ("union with most children") selects G, so a member opening their own
 * Studio was filed as a CHILD of the grandparents. This suite proves the defect, proves the fix,
 * and pins the invariants. Runs against real MySQL tables (no mocks).
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
echo "🧬 GENERATION-INTEGRITY REGRESSION SUITE (Grandparent Mis-Filing)\n";
echo "=================================================================\n\n";

$db = Db::connect2();
$fam = 'TEST_GENINT_' . strtoupper(bin2hex(random_bytes(3)));
$famAmbig = 'TEST_GENAMB_' . strtoupper(bin2hex(random_bytes(3)));
$famWife = 'TEST_GENWIFE_' . strtoupper(bin2hex(random_bytes(3)));
$uidOlusola = 'T_OLUSOLA_' . bin2hex(random_bytes(3));
$uidTaiwo = 'T_TAIWO_' . bin2hex(random_bytes(3));

$node = function (string $f, string $first, string $last, string $gender, int $gen, ?string $bio, ?string $uid = null) use ($db): int {
    $s = $db->prepare('INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $s->execute([$f, $uid, $first, $last, $gender, $gen, $bio]);
    return (int)$db->lastInsertId();
};
$union = function (string $f, int $p1, int $p2) use ($db): int {
    $s = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
    $s->execute([$f, $p1, $p2]);
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
    // ---- Production topology -------------------------------------------------
    $paJohn = $node($fam, 'Pa John Adekoya', 'Mayungbe', 'Male', -2, 'Father');
    $ondeyin = $node($fam, 'Ondeyin', 'Dorcas Adekoya', 'Female', -2, 'Mother');
    $unionG = $union($fam, $paJohn, $ondeyin);

    $oluyomi = $node($fam, 'Oluyomi', 'Olaogun', 'Male', -1, 'Father');
    $iyabo = $node($fam, 'IYABO', 'OLAOGUN', 'Female', -1, 'Mother');
    $unionP = $union($fam, $oluyomi, $iyabo);

    $link($unionG, $iyabo);
    $uncles = [];
    foreach (['Adesanya', 'Labomehin', 'Adesegun', 'Modupe', 'Adeola'] as $nm) {
        $id = $node($fam, $nm, 'Mayungbe', 'Male', -1, 'Sibling');
        $link($unionG, $id);
        $uncles[$nm] = $id;
    }

    $wally = $node($fam, 'Wally', 'Olaogun', 'Other', 0, 'Primary Member', 'T_WALLY_' . bin2hex(random_bytes(2)));
    $temitope = $node($fam, 'Temitope', 'Olaogun', 'Female', 0, 'Sibling', 'T_TEMI_' . bin2hex(random_bytes(2)));
    $femi = $node($fam, 'FEMI', 'OLAOGUN', 'Male', 0, 'Sibling', 'T_FEMI_' . bin2hex(random_bytes(2)));
    foreach ([$wally, $temitope, $femi] as $k) {
        $link($unionP, $k);
    }

    // Registered members who joined later: gen 0, no parent link yet
    $olusola = $node($fam, 'Olusola', 'Olaogun', 'Other', 0, 'Primary Member', $uidOlusola);
    $taiwo = $node($fam, 'Taiwo', 'Olaogun', 'Male', 0, 'Primary Member', $uidTaiwo);

    // TEST 1: prove the retired heuristic picks the grandparents' union (documents the defect)
    runStep('DEFECT PROOF: retired "most children" heuristic selects the GRANDPARENTS union', function () use ($db, $fam, $unionG, $unionP) {
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
        expect($picked === $unionG, "Expected the old heuristic to pick grandparents union {$unionG} (the bug); got {$picked}");
        expect($picked !== $unionP, 'Old heuristic unexpectedly picked the parents union');
    });

    // TEST 2: candidates exclude grandparent unions
    runStep('Parent-union candidates contain ONLY generation -1 unions (grandparents excluded)', function () use ($db, $fam, $unionG, $unionP) {
        $ids = array_map(fn($r) => (int)$r['id'], FamilyGraphIntegrityService::parentUnionCandidates($db, $fam));
        expect($ids === [$unionP], 'Candidates expected [' . $unionP . '], got [' . implode(',', $ids) . ']');
        expect(!in_array($unionG, $ids, true), 'Grandparent union leaked into candidates');
    });

    // TEST 3: Olusola opens his Studio (isRoot) -> attaches to PARENTS union
    runStep('Olusola (root user) attaches to Oluyomi & Iyabo, NOT to the grandparents', function () use ($db, $fam, $uidOlusola, $olusola, $unionP, $unionG, $parentOf) {
        $n = FamilyGraphIntegrityService::attachOrphanedGen0($db, $fam, $uidOlusola);
        $p = $parentOf($olusola);
        expect($p === [$unionP], 'Olusola parent unions expected [' . $unionP . '], got [' . implode(',', $p) . ']');
        expect(!in_array($unionG, $p, true), 'Olusola was filed under the grandparents union');
        expect($n >= 1, 'Expected at least one attachment');
    });

    // TEST 4: Taiwo the same
    runStep('Taiwo (root user) attaches to Oluyomi & Iyabo, NOT to the grandparents', function () use ($db, $fam, $uidTaiwo, $taiwo, $unionP, $unionG, $parentOf) {
        FamilyGraphIntegrityService::attachOrphanedGen0($db, $fam, $uidTaiwo);
        $p = $parentOf($taiwo);
        expect($p === [$unionP], 'Taiwo parent unions expected [' . $unionP . '], got [' . implode(',', $p) . ']');
    });

    // TEST 5: Ego = Wally -> brothers are siblings, never uncles
    runStep("Wally's view: Olusola and Taiwo are SIBLINGS (direct), never 'Parent's Sibling' / 'Uncle'", function () use ($graph, $fam, $wally, $olusola, $taiwo) {
        $a = KinshipCalculatorService::calculateEgoKinship($graph($fam), $wally);
        foreach ([$olusola => 'Olusola', $taiwo => 'Taiwo'] as $id => $nm) {
            $cat = $a[$id]['kinship_category'];
            $rel = (string)$a[$id]['ego_relation'];
            expect($cat === 'sibling', "{$nm} category expected 'sibling', got '{$cat}' ({$rel})");
            expect($a[$id]['ego_side'] === 'direct', "{$nm} side expected 'direct', got '{$a[$id]['ego_side']}'");
            expect(!str_contains($rel, 'Uncle') && !str_contains($rel, "Parent's Sibling") && !str_contains($rel, 'Maternal'), "{$nm} still mislabelled: {$rel}");
        }
    });

    // TEST 6: Ego = Olusola -> parents, grandparents and genuine maternal uncles all correct
    runStep("Olusola's view: Iyabo = Mother, Pa John = Maternal Grandfather, Adesanya = Maternal Uncle, Wally = sibling", function () use ($graph, $fam, $olusola, $iyabo, $paJohn, $uncles, $wally) {
        $a = KinshipCalculatorService::calculateEgoKinship($graph($fam), $olusola);
        expect($a[$iyabo]['ego_relation'] === 'Mother', 'Iyabo expected Mother, got ' . $a[$iyabo]['ego_relation']);
        expect($a[$paJohn]['ego_relation'] === 'Maternal Grandfather', 'Pa John expected Maternal Grandfather, got ' . $a[$paJohn]['ego_relation']);
        expect($a[$uncles['Adesanya']]['ego_relation'] === 'Maternal Uncle', 'Adesanya expected Maternal Uncle, got ' . $a[$uncles['Adesanya']]['ego_relation']);
        expect($a[$wally]['kinship_category'] === 'sibling', 'Wally expected sibling, got ' . $a[$wally]['kinship_category']);
    });

    // TEST 7: idempotency
    runStep('Re-running the heal is idempotent (no duplicate links, zero new attachments)', function () use ($db, $fam, $uidOlusola, $olusola, $parentOf) {
        $n = FamilyGraphIntegrityService::attachOrphanedGen0($db, $fam, $uidOlusola);
        expect($n === 0, "Expected 0 new attachments, got {$n}");
        expect(count($parentOf($olusola)) === 1, 'Olusola acquired a duplicate parent link');
    });

    // TEST 8: wife/in-law with a different surname is NEVER silently filed as a child
    runStep('Registered in-law (different surname, root user) is NOT attached as a child of any union', function () use ($db, $node, $union, $link, $parentOf, $famWife) {
        $f = $node($famWife, 'Baba', 'Adebayo', 'Male', -1, 'Father');
        $m = $node($famWife, 'Mama', 'Adebayo', 'Female', -1, 'Mother');
        $u = $union($famWife, $f, $m);
        $kid = $node($famWife, 'Kunle', 'Adebayo', 'Male', 0, 'Sibling');
        $link($u, $kid);
        $uid = 'T_WIFE_' . bin2hex(random_bytes(3));
        $wife = $node($famWife, 'Ngozi', 'Okafor', 'Female', 0, 'Primary Member', $uid);
        FamilyGraphIntegrityService::attachOrphanedGen0($db, $famWife, $uid);
        expect($parentOf($wife) === [], 'In-law with non-matching surname was wrongly attached');
    });

    // TEST 9: ambiguity never results in a guess
    runStep('Two generation -1 unions with the same surname => ambiguous => NO guess', function () use ($db, $node, $union, $parentOf, $famAmbig) {
        $a1 = $node($famAmbig, 'A1', 'Bello', 'Male', -1, 'Father');
        $a2 = $node($famAmbig, 'A2', 'Bello', 'Female', -1, 'Mother');
        $b1 = $node($famAmbig, 'B1', 'Bello', 'Male', -1, 'Father');
        $b2 = $node($famAmbig, 'B2', 'Bello', 'Female', -1, 'Mother');
        $union($famAmbig, $a1, $a2);
        $union($famAmbig, $b1, $b2);
        $uid = 'T_AMB_' . bin2hex(random_bytes(3));
        $kid = $node($famAmbig, 'Tobi', 'Bello', 'Male', 0, 'Primary Member', $uid);
        FamilyGraphIntegrityService::attachOrphanedGen0($db, $famAmbig, $uid);
        expect($parentOf($kid) === [], 'Ambiguous surname evidence resulted in a guess');
    });

    // TEST 10: pure selector unit checks
    runStep('pickUnionForSurname: case/whitespace-insensitive, null on empty/none/ambiguous', function () {
        $c = [['id' => 1, 'partner_1_id' => 1, 'partner_2_id' => 2, 'p1_last' => ' OLAOGUN ', 'p2_last' => 'olaogun']];
        expect(FamilyGraphIntegrityService::pickUnionForSurname($c, 'Olaogun') !== null, 'case/space match failed');
        expect(FamilyGraphIntegrityService::pickUnionForSurname($c, '') === null, 'empty surname must not match');
        expect(FamilyGraphIntegrityService::pickUnionForSurname($c, 'Other') === null, 'non-matching surname must not match');
        expect(FamilyGraphIntegrityService::pickUnionForSurname($c, 'Olaogun', 1) === null, 'a partner must not be filed under its own union');
    });

    // TEST 11: the data-repair migration moves already-corrupted rows, leaves unprovable ones, is idempotent
    runStep('Repair migration relocates mis-filed members, skips unprovable ones, and is idempotent', function () use ($db, $fam, $olusola, $taiwo, $unionG, $unionP, $parentOf, $node, $link, $rootDir) {
        // Re-create the production corruption for both members
        $db->prepare('DELETE FROM family_node_children WHERE child_id IN (?, ?)')->execute([$olusola, $taiwo]);
        $link($unionG, $olusola);
        $link($unionG, $taiwo);
        // A registered member with a surname that matches NO generation -1 union must stay untouched
        $stranger = $node($fam, 'Chidi', 'Nwosu', 'Male', 0, 'Primary Member', 'T_STR_' . bin2hex(random_bytes(3)));
        $link($unionG, $stranger);

        expect(count(FamilyGraphIntegrityService::findMisplacedMembers($db, $fam)) === 3, 'Fixture should contain 3 misplaced members');

        $sql = (string)file_get_contents($rootDir . '/migrations/2026_11_02_repair_members_misfiled_under_grandparent_union.sql');
        $stmtSql = trim(preg_replace('/^\s*--.*$/m', '', $sql) ?? '');
        $db->exec($stmtSql);

        expect($parentOf($olusola) === [$unionP], 'Olusola not relocated to parents union');
        expect($parentOf($taiwo) === [$unionP], 'Taiwo not relocated to parents union');
        expect($parentOf($stranger) === [$unionG], 'Unprovable member must be left untouched');

        $db->exec($stmtSql);
        expect($parentOf($olusola) === [$unionP] && $parentOf($taiwo) === [$unionP], 'Second run changed data (not idempotent)');
        expect(count(FamilyGraphIntegrityService::findMisplacedMembers($db, $fam)) === 1, 'Only the unprovable stranger should remain flagged');
    });

    // TEST 12: invariant sweep on the fixture's healed members
    runStep('INVARIANT: no healed registered member hangs off a generation <= -2 union', function () use ($db, $fam, $olusola, $taiwo) {
        $bad = array_filter(
            FamilyGraphIntegrityService::findMisplacedMembers($db, $fam),
            fn($r) => in_array((int)$r['node_id'], [$olusola, $taiwo], true)
        );
        expect($bad === [], 'Healed members still misplaced');
    });
} finally {
    foreach ([$fam, $famAmbig, $famWife] as $f) {
        $db->prepare('DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code = ?)')->execute([$f]);
        $db->prepare('DELETE FROM family_unions WHERE family_code = ?')->execute([$f]);
        $db->prepare('DELETE FROM family_nodes WHERE family_code = ?')->execute([$f]);
    }
}

echo "\n=================================================================\n";
echo "🎉 TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "=================================================================\n";

exit($failed > 0 ? 1 : 0);
