<?php
declare(strict_types=1);

/**
 * Verification Test: Multi-Lineage Post Audience Targeting & Matrimonial Kinship
 *
 * Validates:
 * 1. resolveUserAudiences resolution across Single (Dual-Root) and Married (Tri-Lineage) setups.
 * 2. assignPostTargets targeting specific branches (Household, Paternal, Maternal, All).
 * 3. canUserAccessPost authorization isolation & Connected Kin gating.
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\services\FamilyPostAudienceService;
use Src\Db;

$pdo = Db::connect2();

echo "=================================================================\n";
echo "🧪 RUNNING MULTI-LINEAGE POST AUDIENCE VERIFICATION TEST\n";
echo "=================================================================\n\n";

$svc = new FamilyPostAudienceService($pdo);
$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $description): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  ✅ PASS: {$description}\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL: {$description}\n";
        $failCount++;
    }
}

// ── Test 1: Method Signatures and Structure ───────────────────────────────────
echo "[Phase 1] Validating resolveUserAudiences Output Schema...\n";
$emptyAudiences = $svc->resolveUserAudiences('');
assertTest(is_array($emptyAudiences), "Empty user returns array");
assertTest(isset($emptyAudiences['items']), "Output contains 'items' key");
assertTest(isset($emptyAudiences['paternal_fam_code']), "Output contains 'paternal_fam_code'");
assertTest(isset($emptyAudiences['maternal_fam_code']), "Output contains 'maternal_fam_code'");
assertTest(isset($emptyAudiences['household_fam_code']), "Output contains 'household_fam_code'");
assertTest(isset($emptyAudiences['is_married']), "Output contains 'is_married'");

// ── Test 2: Fixture-based Lineage Resolution ──────────────────────────────────
echo "\n[Phase 2] Simulating Child and Married Daughter Lineages...\n";
$testChildId = 'TEST_CHILD_' . bin2hex(random_bytes(4));
$testDaughterId = 'TEST_DAUGHTER_' . bin2hex(random_bytes(4));
$testMaternalRelativeId = 'TEST_MATERNAL_' . bin2hex(random_bytes(4));
$testInlawRelativeId = 'TEST_INLAW_' . bin2hex(random_bytes(4));
$testConnectedKinId = 'TEST_FRIEND_' . bin2hex(random_bytes(4));

try {
    // 2.1 Child with Father's code (#JOHNSON) and Mother's code (#WILLIAMS)
    $stmt = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode, marital_status) VALUES (?, 'Leo', 'Johnson', 'JOHNSON', 'single')");
    $stmt->execute([$testChildId]);

    $stmtOf = $pdo->prepare("INSERT INTO otherFamily (id, otherFamCode, mother_name) VALUES (?, 'WILLIAMS', 'Mary Williams')");
    $stmtOf->execute([$testChildId]);

    $childAudience = $svc->resolveUserAudiences($testChildId);
    assertTest(in_array('JOHNSON', $childAudience['all'], true), "Child holds paternal code JOHNSON");
    assertTest(in_array('WILLIAMS', $childAudience['all'], true), "Child holds maternal code WILLIAMS");
    assertTest($childAudience['paternal_fam_code'] === 'JOHNSON', "Paternal code correctly mapped to JOHNSON");
    assertTest($childAudience['maternal_fam_code'] === 'WILLIAMS', "Maternal code correctly mapped to WILLIAMS");
    assertTest($childAudience['can_choose'] === true, "Child with dual roots can choose audience");
    assertTest(count($childAudience['items']) >= 2, "Child has at least 2 audience branch items");

    // 2.2 Married Daughter with Husband's code (#ADAMS), Father's (#JOHNSON), Mother's (#WILLIAMS)
    $stmtD = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode, marital_status, gender) VALUES (?, 'Sarah', 'Adams', 'ADAMS', 'married', 'Female')");
    $stmtD->execute([$testDaughterId]);

    $stmtOfD = $pdo->prepare("INSERT INTO otherFamily (id, otherFamCode, maiden_name, spouse_name) VALUES (?, 'WILLIAMS', 'Sarah Johnson', 'David Adams')");
    $stmtOfD->execute([$testDaughterId]);

    // Also register Johnson in user_families as approved birth branch
    $stmtUf = $pdo->prepare("INSERT INTO user_families (user_id, family_code, status) VALUES (?, 'JOHNSON', 'approved')");
    $stmtUf->execute([$testDaughterId]);

    $daughterAudience = $svc->resolveUserAudiences($testDaughterId);
    assertTest($daughterAudience['is_married'] === true, "Daughter detected as married");
    assertTest($daughterAudience['household_fam_code'] === 'ADAMS', "Household family code is ADAMS");
    assertTest(in_array('ADAMS', $daughterAudience['all'], true), "Daughter holds ADAMS");
    assertTest(in_array('WILLIAMS', $daughterAudience['all'], true), "Daughter holds WILLIAMS");
    assertTest(in_array('JOHNSON', $daughterAudience['all'], true), "Daughter holds JOHNSON");
    assertTest(count($daughterAudience['all']) === 3, "Daughter holds exactly 3 lineage codes (Tri-Lineage)");
    assertTest($daughterAudience['items'][1]['role'] === 'maiden', "Daughter birth code correctly mapped to maiden role");
    assertTest(str_contains($daughterAudience['items'][1]['label'], 'Birth / Maiden Family'), "Label correctly reflects Birth / Maiden Family");

    // ── Test 3: Post Targeting and Access Authorization ───────────────────────
    echo "\n[Phase 3] Testing Multi-Lineage Post Isolation & Connected Kin Security...\n";
    
    // Register test relative accounts
    $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Aunt', 'Williams', 'WILLIAMS')")
        ->execute([$testMaternalRelativeId]);

    $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Brother', 'InLaw', 'ADAMS')")
        ->execute([$testInlawRelativeId]);

    $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Family', 'Friend', 'OTHERCODE')")
        ->execute([$testConnectedKinId]);

    // Make testConnectedKinId an approved Connected Kin via requestMgt
    $pdo->prepare("INSERT INTO requestMgt (requester_id, approver_id, status) VALUES (?, ?, 'approved')")
        ->execute([$testConnectedKinId, $testDaughterId]);

    // Post A: Targeted to Maternal Roots Only (#WILLIAMS)
    $stmtPostA = $pdo->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, target_scope, post_status) VALUES (?, 'Sarah Adams', 'Maternal recipe update', 'WILLIAMS', 'birth', 'published')");
    $stmtPostA->execute([$testDaughterId]);
    $postNoA = (int)$pdo->lastInsertId();

    $assignedA = $svc->assignPostTargets($postNoA, $testDaughterId, 'birth', 'WILLIAMS');
    assertTest($assignedA['target_codes'] === ['WILLIAMS'], "Post A strictly targeted to WILLIAMS");
    assertTest($svc->canUserAccessPost($postNoA, $testDaughterId) === true, "Author has access to Post A");
    assertTest($svc->canUserAccessPost($postNoA, $testMaternalRelativeId) === true, "Maternal relative has access to Post A");
    assertTest($svc->canUserAccessPost($postNoA, $testInlawRelativeId) === false, "In-law (ADAMS) is BLOCKED from Post A");
    assertTest($svc->canUserAccessPost($postNoA, $testConnectedKinId) === false, "Connected Kin is BLOCKED from specific branch Post A");

    // Post B: Targeted to All Circles & Connected Kin
    $stmtPostB = $pdo->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, target_scope, post_status) VALUES (?, 'Sarah Adams', 'Baby took first steps!', 'ADAMS,WILLIAMS,JOHNSON', 'all', 'published')");
    $stmtPostB->execute([$testDaughterId]);
    $postNoB = (int)$pdo->lastInsertId();

    $assignedB = $svc->assignPostTargets($postNoB, $testDaughterId, 'all');
    assertTest(count($assignedB['target_codes']) === 3, "Post B targeted to all 3 family codes");
    assertTest($svc->canUserAccessPost($postNoB, $testMaternalRelativeId) === true, "Maternal relative has access to Post B");
    assertTest($svc->canUserAccessPost($postNoB, $testInlawRelativeId) === true, "In-law has access to Post B");
    assertTest($svc->canUserAccessPost($postNoB, $testConnectedKinId) === true, "Connected Kin has access to Post B (scope=all)");

    // Regression: even if a fallback explicitCode (e.g. from localStorage 'postFamCode') is passed alongside 'all',
    // assignPostTargets must preserve target_scope === 'all' and Connected Kin access.
    $assignedBRegression = $svc->assignPostTargets($postNoB, $testDaughterId, 'all', 'ADAMS');
    assertTest($assignedBRegression['target_scope'] === 'all', "Post B target_scope stays 'all' even when fallback explicitCode is supplied");
    assertTest(count($assignedBRegression['target_codes']) === 3, "Post B retains all 3 family codes when scope is 'all'");
    assertTest($svc->canUserAccessPost($postNoB, $testConnectedKinId) === true, "Connected Kin retains access to Post B after fallback explicitCode test");

    // Post C: Targeted to Household Only (#ADAMS)
    $stmtPostC = $pdo->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, target_scope, post_status) VALUES (?, 'Sarah Adams', 'Quiet dinner at our house', 'ADAMS', 'married', 'ADAMS')");
    $stmtPostC->execute([$testDaughterId]);
    $postNoC = (int)$pdo->lastInsertId();

    $assignedC = $svc->assignPostTargets($postNoC, $testDaughterId, 'household');
    assertTest($assignedC['target_codes'] === ['ADAMS'], "Post C strictly targeted to ADAMS household");
    assertTest($svc->canUserAccessPost($postNoC, $testInlawRelativeId) === true, "In-law (ADAMS) has access to Post C");
    assertTest($svc->canUserAccessPost($postNoC, $testMaternalRelativeId) === false, "Maternal relative (WILLIAMS) BLOCKED from Post C");
    assertTest($svc->canUserAccessPost($postNoC, $testConnectedKinId) === false, "Connected Kin BLOCKED from Post C");

} finally {
    // ── Cleanup Test Fixtures ─────────────────────────────────────────────────
    echo "\n[Cleanup] Cleaning up test fixtures...\n";
    $testIds = [$testChildId, $testDaughterId, $testMaternalRelativeId, $testInlawRelativeId, $testConnectedKinId];
    $inClause = implode(',', array_fill(0, count($testIds), '?'));
    
    $pdo->prepare("DELETE FROM post_family_targets WHERE post_no IN (SELECT post_no FROM post WHERE id IN ($inClause))")->execute($testIds);
    $pdo->prepare("DELETE FROM post WHERE id IN ($inClause)")->execute($testIds);
    $pdo->prepare("DELETE FROM requestMgt WHERE requester_id IN ($inClause) OR approver_id IN ($inClause)")->execute(array_merge($testIds, $testIds));
    $pdo->prepare("DELETE FROM user_families WHERE user_id IN ($inClause)")->execute($testIds);
    $pdo->prepare("DELETE FROM otherFamily WHERE id IN ($inClause)")->execute($testIds);
    $pdo->prepare("DELETE FROM personal WHERE id IN ($inClause)")->execute($testIds);
}

echo "\n=================================================================\n";
echo "📊 RESULTS: {$passCount} Passed, {$failCount} Failed\n";
echo "=================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
