<?php

declare(strict_types=1);

/**
 * 🛡️ FAMILY ROUNDTABLE RECONCILIATION & COUNCIL SUITE
 *
 * Verifies:
 * 1. Dedicated Navigation: 'Family RoundTable' button directly below Family Journal in leftColumn.blade.php.
 * 2. Dedicated View: 'resources/views/member/familyRoundTable.blade.php' with Grand Council and 2-Minute Peacemaker.
 * 3. The Fun Place Independence: 'familyAiHub.blade.php' is cleanly separated with Fridge Chef as active default.
 * 4. Route Registration: All core RoundTable and council routes registered cleanly.
 * 5. Direct Dispute Lifecycle: Party A files against Party B -> Party B responds -> Reconciliatory accord sealed.
 * 6. Third-Party Peacemaker Mediation: Concerned family member initiates for feuding parties.
 * 7. Quick 2-Minute Peacemaker: Fast banter referee delivers reconciliatory wisdom.
 * 8. Zero-residual test cleanup.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\services\FamilyRoundTableService;
use Src\Db;

echo "=================================================================\n";
echo "🛡️  FAMILY ROUNDTABLE KINSHIP RECONCILIATION SUITE\n";
echo "=================================================================\n";

$roundTableViewPath = $rootDir . '/resources/views/member/familyRoundTable.blade.php';
$roundTableContent = (string)file_get_contents($roundTableViewPath);

$funPlaceViewPath = $rootDir . '/resources/views/member/familyAiHub.blade.php';
$funPlaceContent = (string)file_get_contents($funPlaceViewPath);

$leftColPath = $rootDir . '/resources/views/member/includes/leftColumn.blade.php';
$leftColContent = (string)file_get_contents($leftColPath);

// [TEST 1] Navigation Placement under Family Journal
echo "[TEST 1] Verifying 'Family RoundTable' button positioned under 'Family Journal'...\n";
if (!str_contains($leftColContent, 'Family RoundTable')) {
    echo "❌ FAILED: 'Family RoundTable' button missing from left column navigation.\n";
    exit(1);
}
if (!str_contains($leftColContent, '/family/roundtable')) {
    echo "❌ FAILED: '/family/roundtable' link missing from left column navigation.\n";
    exit(1);
}

// Confirm order: Family Journal appears before Family RoundTable in left column
$posJournal = strpos($leftColContent, 'Family Journal');
$posRoundTable = strpos($leftColContent, 'Family RoundTable');
if ($posJournal === false || $posRoundTable === false || $posRoundTable < $posJournal) {
    echo "❌ FAILED: 'Family RoundTable' button must be positioned under 'Family Journal'.\n";
    exit(1);
}
echo "  ✅ PASSED: 'Family RoundTable' button is positioned directly under 'Family Journal'.\n";

// [TEST 2] Dedicated View Verification
echo "[TEST 2] Verifying dedicated 'familyRoundTable.blade.php' view...\n";
if (!str_contains($roundTableContent, 'Family RoundTable')) {
    echo "❌ FAILED: 'Family RoundTable' title missing from dedicated view.\n";
    exit(1);
}
if (!str_contains($roundTableContent, 'The Grand Council')) {
    echo "❌ FAILED: 'The Grand Council' mode missing from dedicated view.\n";
    exit(1);
}
if (!str_contains($roundTableContent, 'Quick 2-Minute Peacemaker')) {
    echo "❌ FAILED: 'Quick 2-Minute Peacemaker' mode missing from dedicated view.\n";
    exit(1);
}
if (!str_contains($roundTableContent, 'id="complex-witness-select"') || str_contains($roundTableContent, 'witness-checkbox')) {
    echo "❌ FAILED: Named Eyewitnesses / Confidants must use a dropdown select ('complex-witness-select') instead of tickboxes.\n";
    exit(1);
}
echo "  ✅ PASSED: Dedicated Family RoundTable view template & Eyewitness Dropdown verified.\n";

// [TEST 3] The Fun Place Independence
echo "[TEST 3] Verifying 'The Fun Place' independence & separation...\n";
if (!str_contains($funPlaceContent, 'The Fun Place')) {
    echo "❌ FAILED: 'The Fun Place' headline missing from familyAiHub.blade.php.\n";
    exit(1);
}
if (!str_contains($funPlaceContent, 'id="tab-fridge" role="tabpanel"')) {
    echo "❌ FAILED: Fridge Chef panel missing from The Fun Place.\n";
    exit(1);
}
echo "  ✅ PASSED: The Fun Place operates independently with creative household tools.\n";

// [TEST 4] Route Registration in profile_page.php
echo "[TEST 4] Verifying RoundTable Routes in profile_page.php...\n";
$routerFile = $rootDir . '/app/router/profile_page.php';
$routerContent = (string)file_get_contents($routerFile);

$requiredRoutes = [
    '/family/roundtable',
    '/api/roundtable/file-dispute',
    '/api/roundtable/submit-statement',
    '/api/roundtable/adjudicate',
    '/api/roundtable/dispute/[*:id]',
    '/api/roundtable/arbitrate-instant',
];

foreach ($requiredRoutes as $rt) {
    if (!str_contains($routerContent, $rt)) {
        echo "❌ FAILED: Missing route {$rt} in profile_page.php.\n";
        exit(1);
    }
}
echo "  ✅ PASSED: All 6 core Family RoundTable routes registered cleanly.\n";

// [TEST 5] Direct Dispute Filing & Council Accord
echo "[TEST 5] Verifying Direct Dispute Filing & RoundTable Accord...\n";
$pdo = Db::connect2();
$famCode = 'ROUNDTABLE_TEST_FAM';
$delOld = $pdo->prepare("DELETE FROM personal WHERE famCode = ?");
$delOld->execute([$famCode]);

$ins = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
$ins->execute(['RTB_USER_1', 'Liam', 'Sterling', $famCode]);
$ins->execute(['RTB_USER_2', 'Maya', 'Sterling', $famCode]);
$ins->execute(['RTB_WITNESS_1', 'Uncle', 'Dave', $famCode]);

$service = new FamilyRoundTableService();

// Party A files against Party B
$fileA = $service->fileDispute(
    famCode: $famCode,
    initiatorId: 'RTB_USER_1',
    isThirdParty: false,
    partyAId: 'RTB_USER_1',
    partyBId: 'RTB_USER_2',
    witnessIds: ['RTB_WITNESS_1'],
    title: 'Dishes and Kitchen Cleanliness',
    category: 'household_chores',
    grievance: 'I washed up Monday and Tuesday, but Maya left dirty frying pans in the sink all Wednesday.'
);

if (!$fileA['success'] || empty($fileA['dispute_id'])) {
    echo "❌ FAILED: Direct dispute filing failed: " . ($fileA['error'] ?? '') . "\n";
    exit(1);
}
$disputeId1 = (string)$fileA['dispute_id'];

// Party B submits statement
$subB = $service->submitStatement($disputeId1, 'RTB_USER_2', 'I had two back-to-back client presentations and soaked the pans to wash them this morning.');
if (!$subB['success'] || empty($subB['is_ready'])) {
    echo "❌ FAILED: Party B statement submission failed or did not mark case as ready.\n";
    exit(1);
}

// Witness submits observation
$subW = $service->submitStatement($disputeId1, 'RTB_WITNESS_1', 'Both of them work hard; Maya was indeed on calls until late.');
if (!$subW['success']) {
    echo "❌ FAILED: Witness testimony submission failed.\n";
    exit(1);
}

// Adjudicate
$adj1 = $service->adjudicate($disputeId1);
if (!$adj1['success'] || empty($adj1['finding_of_fact']) || empty($adj1['reconciliatory_bridge'])) {
    echo "❌ FAILED: RoundTable adjudication failed: " . ($adj1['error'] ?? '') . "\n";
    exit(1);
}

$fullVerdictText = strtolower($adj1['finding_of_fact'] . ' ' . $adj1['reconciliatory_bridge'] . ' ' . $adj1['yommy_wisdom']);
if (!str_contains($fullVerdictText, 'reconcil') && !str_contains($fullVerdictText, 'harmony') && !str_contains($fullVerdictText, 'bond')) {
    echo "❌ FAILED: Accord missing reconciliatory language.\n";
    exit(1);
}
echo "  ✅ PASSED: Direct council hearing resolved with sealed RoundTable Accord.\n";

// [TEST 6] Third-Party Peacemaker Mediation Lifecycle
echo "[TEST 6] Verifying 3rd-Party Peacemaker Mediation Lifecycle...\n";
$ins->execute(['RTB_PEACEMAKER', 'Grandma', 'Elena', $famCode]);

// Grandma Elena initiates hearing on behalf of feuding Liam and Maya
$file3rd = $service->fileDispute(
    famCode: $famCode,
    initiatorId: 'RTB_PEACEMAKER',
    isThirdParty: true,
    partyAId: 'RTB_USER_1',
    partyBId: 'RTB_USER_2',
    witnessIds: ['RTB_WITNESS_1'],
    title: 'Shared Vehicle Fuel Disagreement',
    category: 'borrowed_property',
    grievance: 'Liam and Maya have been giving each other the silent treatment over fuel money for the family car.'
);

if (!$file3rd['success'] || empty($file3rd['dispute_id'])) {
    echo "❌ FAILED: 3rd-party peacemaker dispute filing failed: " . ($file3rd['error'] ?? '') . "\n";
    exit(1);
}
$disputeId2 = (string)$file3rd['dispute_id'];

// Both feuding parties submit statements
$service->submitStatement($disputeId2, 'RTB_USER_1', 'I filled up £40 of petrol last week and now it is nearly on empty.');
$service->submitStatement($disputeId2, 'RTB_USER_2', 'I drove Grandma to her doctor appointments all Thursday.');

// Adjudicate 3rd-party mediated dispute
$adj2 = $service->adjudicate($disputeId2);
if (!$adj2['success'] || empty($adj2['finding_of_fact'])) {
    echo "❌ FAILED: 3rd-party mediated adjudication failed: " . ($adj2['error'] ?? '') . "\n";
    exit(1);
}

$details = $service->getDisputeDetails($disputeId2, $famCode);
if (!$details || ($details['status'] ?? '') !== 'resolved' || empty($details['statements'])) {
    echo "❌ FAILED: getDisputeDetails failed or did not return resolved status.\n";
    exit(1);
}
echo "  ✅ PASSED: 3rd-party peacemaker dispute successfully mediated and sealed.\n";

// [TEST 7] Quick 2-Minute Peacemaker
echo "[TEST 7] Verifying Quick 2-Minute Peacemaker Arbitrator...\n";
$instant = $service->arbitrateInstant(
    partyAName: 'Leo',
    partyAClaim: 'Leo wants to watch the football match on the main TV.',
    partyBName: 'Sophie',
    partyBClaim: 'Sophie planned a movie night with her cousins.',
    context: 'tv_remote',
    isThirdParty: true,
    initiatorName: 'Dad'
);

if (!$instant['success'] || empty($instant['verdict_title']) || empty($instant['restorative_action_plan'])) {
    echo "❌ FAILED: Instant peacemaker failed: " . ($instant['error'] ?? '') . "\n";
    exit(1);
}
echo "  ✅ PASSED: Quick peacemaker delivered reconciliatory accord.\n";

// [TEST 8] Test Fixture Cleanup
echo "[TEST 8] Cleaning up test fixtures...\n";
$clean1 = $pdo->prepare("DELETE FROM family_disputes WHERE famCode = ?");
$clean1->execute([$famCode]);
$clean2 = $pdo->prepare("DELETE FROM family_dispute_statements WHERE dispute_id IN (?, ?)");
$clean2->execute([$disputeId1, $disputeId2]);
$clean3 = $pdo->prepare("DELETE FROM personal WHERE famCode = ?");
$clean3->execute([$famCode]);
echo "  ✅ PASSED: Cleaned up all test records.\n";

echo "=================================================================\n";
echo "🏆 ALL 8 FAMILY ROUNDTABLE VERIFICATION GATES PASSED CLEANLY!\n";
echo "=================================================================\n";
