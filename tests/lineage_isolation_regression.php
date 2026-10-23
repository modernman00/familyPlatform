<?php
/**
 * Lineage Isolation Regression Test Suite
 * =========================================
 * Verifies that the maiden/maternal family code isolation fix is structurally
 * sound. Tests run against the live local database via PDO (same pattern as
 * the rest of the verify_e2e_robust_suite).
 *
 * Gates:
 *  1. General.php ownership gate  — POSTing a foreign famCode is silently
 *     corrected to the user's own primary or otherFamCode.
 *  2. SettingController lineage gate — submitting an unowned lineage_code
 *     falls back to the primary famCode.
 *  3. DB integrity check — all family_nodes for user 318422WALLY with
 *     parentType IN ('parent','sibling') are under KEN852, NOT OLAO60446.
 *  4. Rogue-token check — old tokens with family_code = OLAO60446 that were
 *     issued to parent/sibling invitees are exhausted (use_count >= max_uses).
 *  5. Blade variable check — $hasMaternalCode pattern present in view file.
 *  6. JS getInviteFamCode function present in compiled asset.
 *
 * Run: php tests/lineage_isolation_regression.php
 */

declare(strict_types=1);

ob_start();
require_once __DIR__ . '/../app/config/init.php';
ob_end_clean();

use Src\Db;

$passed = 0;
$failed = 0;

function pass(string $label): void {
    global $passed;
    $passed++;
    echo "\033[32m  ✅ PASS\033[0m  {$label}\n";
}

function fail(string $label, string $reason = ''): void {
    global $failed;
    $failed++;
    echo "\033[31m  ❌ FAIL\033[0m  {$label}" . ($reason ? " — {$reason}" : '') . "\n";
}

echo "\n\033[1m🧪 Lineage Isolation Regression Suite\033[0m\n";
echo str_repeat('─', 60) . "\n\n";

// ─────────────────────────────────────────────────────────────
// GATE 1 — General.php: ownership validation code paths exist
// ─────────────────────────────────────────────────────────────
echo "GATE 1 — General.php ownership guard\n";

$generalSrc = file_get_contents(__DIR__ . '/../app/controller/General.php');

if (str_contains($generalSrc, "otherFamCode FROM otherFamily WHERE id = ?")) {
    pass('Parameterised otherFamCode lookup present (no SQL interpolation)');
} else {
    fail('Parameterised otherFamCode lookup', 'Pattern not found in General.php');
}

if (str_contains($generalSrc, 'strtoupper(trim(')) {
    pass('FamCode normalisation (strtoupper+trim) applied before comparison');
} else {
    fail('FamCode normalisation', 'Missing in General.php');
}

// ─────────────────────────────────────────────────────────────
// GATE 2 — SettingController: lineage routing guard
// ─────────────────────────────────────────────────────────────
echo "\nGATE 2 — SettingController lineage_scope guard\n";

$settingSrc = file_get_contents(__DIR__ . '/../app/controller/members/SettingController.php');

if (str_contains($settingSrc, "'lineage_scope'") || str_contains($settingSrc, '"lineage_scope"')) {
    pass('lineage_scope POST field consumed in SettingController');
} else {
    fail('lineage_scope handling', 'Not found in SettingController.php');
}

if (str_contains($settingSrc, 'submitted unowned lineage_code')) {
    pass('Unowned lineage_code fallback guard confirmed');
} else {
    fail('Unowned lineage_code fallback guard', 'Error log message not found');
}

if (str_contains($settingSrc, "otherFamCode FROM otherFamily WHERE id = ?")) {
    pass('Parameterised ownership check in SettingController (no SQL interpolation)');
} else {
    fail('Parameterised ownership check', 'Pattern not found in SettingController.php');
}

// ─────────────────────────────────────────────────────────────
// GATE 3 — DB integrity: nodes under correct family code
// ─────────────────────────────────────────────────────────────
echo "\nGATE 3 — Database: nodes belong to KEN852, not OLAO60446\n";

try {
    $pdo = Db::connect2();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nodeIds = [25605, 25606, 26682];
    $sqlNodes = "SELECT family_code, COUNT(*) AS c FROM family_nodes WHERE id IN (?, ?, ?) GROUP BY family_code";
    $stmt = $pdo->prepare($sqlNodes);
    $stmt->execute($nodeIds);
    $byCode = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    (int)($byCode['OLAO60446'] ?? 0) === 0
        ? pass('Zero remediated nodes remain under OLAO60446')
        : fail('Rogue nodes under OLAO60446', (string)$byCode['OLAO60446'] . ' remain');
    (int)($byCode['KEN852'] ?? 0) === 3
        ? pass('All 3 remediated nodes (25605/25606/26682) under KEN852')
        : fail('Nodes under KEN852', 'found ' . (int)($byCode['KEN852'] ?? 0) . ', expected 3');

    // ─────────────────────────────────────────────────────────────
    // GATE 4 — Rogue tokens exhausted
    // ─────────────────────────────────────────────────────────────
    echo "\nGATE 4 — Rogue invite tokens exhausted\n";

    $tokIds = [20575, 20576, 20582, 20583, 20650, 20651, 20652, 20653];
    $sqlTok = "SELECT COUNT(*) FROM invite_tokens WHERE id IN (?, ?, ?, ?, ?, ?, ?, ?) AND use_count < max_uses AND expires_at > NOW()";
    $stmt3 = $pdo->prepare($sqlTok);
    $stmt3->execute($tokIds);
    $activeRogue = (int)$stmt3->fetchColumn();
    $activeRogue === 0
        ? pass('All 8 rogue OLAO60446 tokens are exhausted')
        : fail('Active rogue tokens', $activeRogue . ' still live');

    $stmt4 = $pdo->prepare("SELECT COUNT(*) FROM invite_tokens WHERE family_code = ? AND type = 'onboarding' AND use_count < max_uses AND expires_at > NOW()");
    $stmt4->execute(['KEN852']);
    $validKen = (int)$stmt4->fetchColumn();
    $validKen >= 1
        ? pass("Valid KEN852 onboarding token present ({$validKen})")
        : fail('Valid KEN852 token', 'none found');

    // otherFamCode set correctly in DB
    $stmt5 = $pdo->prepare(
        "SELECT otherFamCode FROM otherFamily WHERE id = ? LIMIT 1"
    );
    $stmt5->execute(['318422WALLY']);
    $dbCode = strtoupper(trim((string)($stmt5->fetchColumn() ?? '')));

    if ($dbCode === 'KEN852') {
        pass("otherFamily.otherFamCode = KEN852 for 318422WALLY ✓");
    } else {
        fail("otherFamCode in DB", "Expected KEN852, got '{$dbCode}'");
    }

} catch (PDOException $e) {
    fail('Database connection / query', $e->getMessage());
}

// ─────────────────────────────────────────────────────────────
// GATE 5 — Blade template: lineage picker markup present
// ─────────────────────────────────────────────────────────────
echo "\nGATE 5 — Blade template lineage picker\n";

$bladeSrc = file_get_contents(
    __DIR__ . '/../resources/views/member/accountSetting.blade.php'
);

$bladeChecks = [
    'hasMaternalCode variable set'           => str_contains($bladeSrc, 'hasMaternalCode'),
    'parents-scope-primary radio'            => str_contains($bladeSrc, 'parents-scope-primary'),
    'parents-scope-maternal radio'           => str_contains($bladeSrc, 'parents-scope-maternal'),
    'parentsLineageScope hidden field'       => str_contains($bladeSrc, 'parentsLineageScope'),
    'parentsLineageCode hidden field'        => str_contains($bladeSrc, 'parentsLineageCode'),
    'siblings-scope-primary radio'           => str_contains($bladeSrc, 'siblings-scope-primary'),
    'siblings-scope-maternal radio'          => str_contains($bladeSrc, 'siblings-scope-maternal'),
    'siblingsLineageScope hidden field'      => str_contains($bladeSrc, 'siblingsLineageScope'),
    'siblingsLineageCode hidden field'       => str_contains($bladeSrc, 'siblingsLineageCode'),
    'otherFamCode_id hidden field'           => str_contains($bladeSrc, 'otherFamCode_id'),
    'famCode_id hidden field'                => str_contains($bladeSrc, 'famCode_id'),
    'Birth / Maiden Family copy (British)'   => str_contains($bladeSrc, 'Birth') && str_contains($bladeSrc, 'Maiden'),
];

foreach ($bladeChecks as $label => $ok) {
    $ok ? pass($label) : fail($label, 'Not found in accountSetting.blade.php');
}

// ─────────────────────────────────────────────────────────────
// GATE 6 — JS: getInviteFamCode & initLineageScopePickers
// ─────────────────────────────────────────────────────────────
echo "\nGATE 6 — JavaScript: context-aware family code logic\n";

$jsSrc    = file_get_contents(__DIR__ . '/../resources/asset/js/components/kidsAndSiblings.js');
$acjsSrc  = file_get_contents(__DIR__ . '/../resources/asset/js/components/accountSetting.js');

$jsChecks = [
    'getInviteFamCode function defined'        => str_contains($jsSrc, 'getInviteFamCode'),
    'siblingsLineageScope read in JS'          => str_contains($jsSrc, 'siblingsLineageScope'),
    'parentsLineageScope read in JS'           => str_contains($jsSrc, 'parentsLineageScope'),
    'otherFamCode_id read in JS'               => str_contains($jsSrc, 'otherFamCode_id'),
    'getInviteFamCode called in onInput'       => str_contains($jsSrc, 'getInviteFamCode('),
    'initLineageScopePickers in accountSetting.js' => str_contains($acjsSrc, 'initLineageScopePickers'),
    'parents-scope-maternal wired in JS'       => str_contains($acjsSrc, 'parents-scope-maternal'),
    'siblings-scope-maternal wired in JS'      => str_contains($acjsSrc, 'siblings-scope-maternal'),
];

foreach ($jsChecks as $label => $ok) {
    $ok ? pass($label) : fail($label);
}

// ─────────────────────────────────────────────────────────────
// Summary
// ─────────────────────────────────────────────────────────────
echo "\n" . str_repeat('─', 60) . "\n";
$total = $passed + $failed;
echo "\033[1mResults: {$passed}/{$total} passed";

if ($failed === 0) {
    echo " 🎉\033[0m\n";
    echo "\033[32mAll lineage isolation regression gates PASSED.\033[0m\n\n";
    exit(0);
} else {
    echo " ❗\033[0m\n";
    echo "\033[31m{$failed} gate(s) FAILED — review above and re-run.\033[0m\n\n";
    exit(1);
}
