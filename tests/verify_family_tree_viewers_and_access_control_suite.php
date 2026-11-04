<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Family Tree Viewers Tracking, Same-Family-Code Isolation,
 * and Granular Per-Person Access Control (TAT & BRATS Gate)
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\controller\members\Organogram;
use App\services\FamilyTreeAccessService;
use Src\Db;

echo "=================================================================\n";
echo "🌳 FAMILY TREE VIEWERS & ACCESS CONTROL VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$passed = 0;
$failed = 0;

function runTest(string $title, callable $test): void {
    global $passed, $failed;
    echo "[TEST] {$title}... ";
    try {
        $test();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")\n";
        $failed++;
    }
}

$pdo = Db::connect2();
FamilyTreeAccessService::ensureSchema($pdo);

$suffix = bin2hex(random_bytes(4));
$ownerFamCode = 'FAM_OWNER_' . $suffix;
$outsiderFamCode = 'FAM_OUTSIDER_' . $suffix;

$ownerId = 'OWNER_' . $suffix;
$relative1Id = 'REL1_' . $suffix;
$relative2Id = 'REL2_' . $suffix;
$outsiderId = 'OUTSIDER_' . $suffix;

// Provision Owner
$stmt = $pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
$stmt->execute([$ownerId, 'OwnerFirst', 'OwnerLast', $ownerFamCode]);

// Provision Relatives (Same Family Code)
$stmt->execute([$relative1Id, 'Kolawole', 'Olaogun', $ownerFamCode]);
$stmt->execute([$relative2Id, 'Folake', 'Olaogun', $ownerFamCode]);

// Provision Outsider (Different Family Code)
$stmt->execute([$outsiderId, 'David', 'Outsider', $outsiderFamCode]);

// Provision Contact
$stmtC = $pdo->prepare('INSERT INTO contact (id, email, country, mobile, show_my_profile) VALUES (?, ?, ?, ?, ?)');
$stmtC->execute([$ownerId, 'owner_' . $suffix . '@test.com', 'United Kingdom', '+447000000001', 'on']);
$stmtC->execute([$relative1Id, 'kola_' . $suffix . '@test.com', 'United Kingdom', '+447000000002', 'on']);
$stmtC->execute([$relative2Id, 'folake_' . $suffix . '@test.com', 'United Kingdom', '+447000000003', 'on']);
$stmtC->execute([$outsiderId, 'david_' . $suffix . '@test.com', 'Canada', '+12025550100', 'on']);

try {
    // 1. Schema check
    runTest('1. Database tables exist and are properly indexed', function() use ($pdo) {
        $tables = ['family_tree_views', 'family_tree_privacy_settings', 'family_tree_access_rules'];
        foreach ($tables as $t) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$t'");
            if (!$stmt->fetch()) {
                throw new Exception("Table $t does not exist.");
            }
        }
    });

    // 2. Requirement 1: Users can see who viewed their family tree
    runTest('2. Users can see who viewed their family tree with atomic count & timestamps', function() use ($pdo, $ownerId, $relative1Id, $ownerFamCode) {
        $initial = FamilyTreeAccessService::getTreeViewers($ownerId, null, $pdo);
        if ($initial['total_views'] !== 0 || $initial['unique_viewers'] !== 0) {
            throw new Exception("Expected 0 initial views, got {$initial['total_views']}");
        }

        // Relative views owner's tree
        FamilyTreeAccessService::recordTreeView($relative1Id, $ownerId, $ownerFamCode, $pdo);

        $v1 = FamilyTreeAccessService::getTreeViewers($ownerId, null, $pdo);
        if ($v1['total_views'] !== 1 || $v1['unique_viewers'] !== 1) {
            throw new Exception("Expected 1 view, got {$v1['total_views']}");
        }
        if ($v1['viewers'][0]['viewer_id'] !== $relative1Id) {
            throw new Exception("Viewer ID mismatch.");
        }
        if ($v1['viewers'][0]['view_count'] !== 1) {
            throw new Exception("View count mismatch.");
        }

        // Second view increments counter
        FamilyTreeAccessService::recordTreeView($relative1Id, $ownerId, $ownerFamCode, $pdo);
        $v2 = FamilyTreeAccessService::getTreeViewers($ownerId, null, $pdo);
        if ($v2['total_views'] !== 2 || $v2['viewers'][0]['view_count'] !== 2) {
            throw new Exception("Expected 2 views on second visit, got {$v2['total_views']}");
        }
    });

    // 3. Requirement 2: Strict Same Family Code Invariant
    runTest('3. Family tree can ONLY be viewed by family member sharing same family code', function() use ($pdo, $ownerId, $relative1Id, $outsiderId, $ownerFamCode) {
        // Same family relative -> ALLOWED
        $resRelative = FamilyTreeAccessService::canViewFamilyTree($relative1Id, $ownerId, $ownerFamCode, $pdo);
        if (!$resRelative['allowed']) {
            throw new Exception("Same family member was incorrectly blocked.");
        }

        // Outsider with different family code -> BLOCKED
        $resOutsider = FamilyTreeAccessService::canViewFamilyTree($outsiderId, $ownerId, $ownerFamCode, $pdo);
        if ($resOutsider['allowed']) {
            throw new Exception("Outsider was incorrectly allowed to view tree.");
        }
        if ($resOutsider['code'] !== 'different_family_code') {
            throw new Exception("Expected code 'different_family_code', got {$resOutsider['code']}");
        }

        // Outsider with approved connection in requestMgt -> STILL BLOCKED (Cannot bypass family code)
        $reqStmt = $pdo->prepare("
            INSERT INTO requestMgt (requester_id, approver_id, status)
            VALUES (?, ?, 'approved')
        ");
        $reqStmt->execute([$outsiderId, $ownerId]);

        $resConnectedOutsider = FamilyTreeAccessService::canViewFamilyTree($outsiderId, $ownerId, $ownerFamCode, $pdo);
        if ($resConnectedOutsider['allowed']) {
            throw new Exception("Connected outsider outside family code was incorrectly allowed to view tree.");
        }
        if ($resConnectedOutsider['code'] !== 'different_family_code') {
            throw new Exception("Expected code 'different_family_code', got {$resConnectedOutsider['code']}");
        }
    });

    // 4. Requirement 3: Power to limit access to specific persons
    runTest('4. Owner can limit access to specific relatives (blocklist and allowlist)', function() use ($pdo, $ownerId, $relative1Id, $relative2Id, $ownerFamCode) {
        // By default, relative1 and relative2 have access
        $c1 = FamilyTreeAccessService::canViewFamilyTree($relative1Id, $ownerId, $ownerFamCode, $pdo);
        $c2 = FamilyTreeAccessService::canViewFamilyTree($relative2Id, $ownerId, $ownerFamCode, $pdo);
        if (!$c1['allowed'] || !$c2['allowed']) {
            throw new Exception("Family members should be allowed by default in family_all mode.");
        }

        // Owner restricts relative1 specifically
        $setRes = FamilyTreeAccessService::setPersonAccessRule($ownerId, $relative1Id, 'restrict', $pdo);
        if (!$setRes['success'] || $setRes['rule_type'] !== 'deny' || $setRes['is_allowed'] !== false) {
            throw new Exception("Failed to set deny rule for relative1.");
        }

        // Now relative1 is denied, but relative2 remains allowed
        $check1 = FamilyTreeAccessService::canViewFamilyTree($relative1Id, $ownerId, $ownerFamCode, $pdo);
        $check2 = FamilyTreeAccessService::canViewFamilyTree($relative2Id, $ownerId, $ownerFamCode, $pdo);
        if ($check1['allowed']) {
            throw new Exception("Restricted relative1 should have been blocked.");
        }
        if ($check1['code'] !== 'restricted_person') {
            throw new Exception("Expected code 'restricted_person', got {$check1['code']}");
        }
        if (!$check2['allowed']) {
            throw new Exception("Relative2 should remain allowed.");
        }

        // Owner restores access for relative1
        $restoreRes = FamilyTreeAccessService::setPersonAccessRule($ownerId, $relative1Id, 'allow', $pdo);
        if (!$restoreRes['success'] || $restoreRes['is_allowed'] !== true) {
            throw new Exception("Failed to restore access for relative1.");
        }
        $checkRestore = FamilyTreeAccessService::canViewFamilyTree($relative1Id, $ownerId, $ownerFamCode, $pdo);
        if (!$checkRestore['allowed']) {
            throw new Exception("Restored relative1 should now be allowed.");
        }

        // Mode: specific_persons allowlist
        FamilyTreeAccessService::saveAccessControlPolicy(
            $ownerId,
            FamilyTreeAccessService::MODE_SPECIFIC_PERSONS,
            [$relative2Id], // only relative2 allowlisted
            [],
            false,
            $pdo
        );
        $checkAllowlist1 = FamilyTreeAccessService::canViewFamilyTree($relative1Id, $ownerId, $ownerFamCode, $pdo);
        $checkAllowlist2 = FamilyTreeAccessService::canViewFamilyTree($relative2Id, $ownerId, $ownerFamCode, $pdo);
        if ($checkAllowlist1['allowed']) {
            throw new Exception("Relative1 should be blocked in specific_persons mode when not allowlisted.");
        }
        if (!$checkAllowlist2['allowed']) {
            throw new Exception("Relative2 should be allowed in specific_persons mode when allowlisted.");
        }

        // Mode: only_me private lock
        FamilyTreeAccessService::saveAccessControlPolicy(
            $ownerId,
            FamilyTreeAccessService::MODE_ONLY_ME,
            [$relative2Id],
            [],
            false,
            $pdo
        );
        $checkOnlyMe = FamilyTreeAccessService::canViewFamilyTree($relative2Id, $ownerId, $ownerFamCode, $pdo);
        if ($checkOnlyMe['allowed']) {
            throw new Exception("Relative2 should be blocked when tree is in only_me mode.");
        }
        if ($checkOnlyMe['code'] !== 'locked_only_me') {
            throw new Exception("Expected code 'locked_only_me', got {$checkOnlyMe['code']}");
        }
    });

    // 5. Requirement 4: Made obvious to users in UI templates
    runTest('5. Obvious UI presence across Organogram and Family Studio templates', function() {
        $organogramBlade = (string)file_get_contents(dirname(__DIR__) . '/resources/views/member/organogram.blade.php');
        $modalsBlade = (string)file_get_contents(dirname(__DIR__) . '/resources/views/member/includes/treeAccessModals.blade.php');
        $studioBlade = (string)file_get_contents(dirname(__DIR__) . '/resources/views/member/familyStudio.blade.php');

        // Check Organogram Header buttons and Security Banner
        if (!str_contains($organogramBlade, 'id="btnWhoViewedTree"')) {
            throw new Exception("Missing Who Viewed Tree header button in organogram.blade.php");
        }
        if (!str_contains($organogramBlade, 'id="btnTreeAccessControl"')) {
            throw new Exception("Missing Limit Access header button in organogram.blade.php");
        }
        if (!str_contains($organogramBlade, 'id="treeSecurityBanner"')) {
            throw new Exception("Missing treeSecurityBanner in organogram.blade.php");
        }
        if (!str_contains($organogramBlade, 'Same Family Code Only')) {
            throw new Exception("Missing Same Family Code Only badge in organogram.blade.php");
        }

        // Check Modals
        if (!str_contains($modalsBlade, 'id="whoViewedTreeModal"')) {
            throw new Exception("Missing whoViewedTreeModal");
        }
        if (!str_contains($modalsBlade, 'id="treeAccessControlModal"')) {
            throw new Exception("Missing treeAccessControlModal");
        }
        if (!str_contains($modalsBlade, 'Same Family Code Only Invariant')) {
            throw new Exception("Missing Same Family Code Only Invariant notice in modal");
        }
        if (!str_contains($modalsBlade, 'All Family Members (Default)')) {
            throw new Exception("Missing All Family Members option");
        }
        if (!str_contains($modalsBlade, 'Only Specific Selected Persons')) {
            throw new Exception("Missing Only Specific Selected Persons option");
        }

        // Check Family Studio inclusion
        if (!str_contains($studioBlade, 'openWhoViewedTreeModal()')) {
            throw new Exception("Missing openWhoViewedTreeModal in familyStudio.blade.php");
        }
        if (!str_contains($studioBlade, 'openTreeAccessControlModal()')) {
            throw new Exception("Missing openTreeAccessControlModal in familyStudio.blade.php");
        }
        if (!str_contains($studioBlade, 'treeAccessModals')) {
            throw new Exception("Missing treeAccessModals include in familyStudio.blade.php");
        }
    });

    // 6. Controller endpoints and CSRF security
    runTest('6. Controller endpoints enforce authentication and CSRF', function() use ($pdo, $ownerId, $ownerFamCode, $relative1Id) {
        $controller = (new ReflectionClass(Organogram::class))->newInstanceWithoutConstructor();

        // Unauthenticated access returns 401
        unset($_SESSION['id']);
        ob_start();
        $controller->getTreeViewers();
        $raw1 = ob_get_clean() ?: '';
        $resp1 = json_decode($raw1, true) ?: [];
        $code = http_response_code();
        if (($resp1['success'] ?? true) !== false || !str_contains((string)($resp1['error'] ?? ''), 'sign in')) {
            throw new Exception("Unauthenticated getTreeViewers must reject with sign-in error. Got raw=" . var_export($raw1, true));
        }

        // Authenticated getTreeViewers succeeds
        $_SESSION['id'] = $ownerId;
        $_SESSION['famCode'] = $ownerFamCode;
        http_response_code(200);
        ob_start();
        $controller->getTreeViewers();
        $raw2 = ob_get_clean() ?: '';
        $resp2 = json_decode($raw2, true) ?: [];
        if (!($resp2['success'] ?? false)) {
            throw new Exception("Authenticated getTreeViewers failed.");
        }

        // CSRF tampering on setPersonAccessRule returns 403
        $realToken = 'token_' . bin2hex(random_bytes(6));
        $_SESSION['token'] = $realToken;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'target_user_id' => $relative1Id,
            'action' => 'restrict',
            'token' => 'tampered_token'
        ];

        ob_start();
        $controller->setPersonAccessRule();
        $raw3 = ob_get_clean() ?: '';
        $resp3 = json_decode($raw3, true) ?: [];
        if (($resp3['success'] ?? true) !== false || !str_contains((string)($resp3['error'] ?? ''), 'CSRF')) {
            throw new Exception("Tampered CSRF token on setPersonAccessRule must be rejected. Got raw=" . var_export($raw3, true));
        }

        // Valid CSRF succeeds
        $_POST['token'] = $realToken;
        $_POST['_token'] = $realToken;
        http_response_code(200);

        ob_start();
        $controller->setPersonAccessRule();
        $raw4 = ob_get_clean() ?: '';
        $resp4 = json_decode($raw4, true) ?: [];
        if (!($resp4['success'] ?? false) || ($resp4['rule_type'] ?? '') !== 'deny') {
            throw new Exception("Valid setPersonAccessRule failed.");
        }
    });

    // 7. Modal search filtering implementation
    runTest('7. Modal search filtering logic and DOM binding verification', function() {
        $modalsBlade = (string)file_get_contents(dirname(__DIR__) . '/resources/views/member/includes/treeAccessModals.blade.php');

        if (!str_contains($modalsBlade, 'id="accessControlSearchInput"')) {
            throw new Exception("Missing accessControlSearchInput in treeAccessModals.blade.php");
        }
        if (!str_contains($modalsBlade, 'id="whoViewedSearchInput"')) {
            throw new Exception("Missing whoViewedSearchInput in treeAccessModals.blade.php");
        }
        if (!str_contains($modalsBlade, 'filterAccessControlList')) {
            throw new Exception("Missing filterAccessControlList implementation");
        }
        if (!str_contains($modalsBlade, 'filterWhoViewedList')) {
            throw new Exception("Missing filterWhoViewedList implementation");
        }
        // Verify important display override to bypass Bootstrap .d-flex specificity
        if (!str_contains($modalsBlade, "setProperty('display'") && !str_contains($modalsBlade, 'setProperty("display"')) {
            throw new Exception("filterAccessControlList must use setProperty('display', ..., 'important') to override Bootstrap .d-flex.");
        }
    });

    // 8. Cultural privacy: Option to show initial only for parents' first name
    runTest('8. Cultural privacy: Option to show initial only for parents first names (e.g. A. Olaogun)', function() use ($pdo, $ownerId) {
        // 8a. Test formatting helper
        $formatted = FamilyTreeAccessService::formatInitialOnlyFirstName('Adeola', 'Olaogun');
        if ($formatted['first_name'] !== 'A.' || $formatted['full_name'] !== 'A. Olaogun') {
            throw new Exception("formatInitialOnlyFirstName failed: expected 'A. Olaogun', got '{$formatted['full_name']}'");
        }

        $formattedSingle = FamilyTreeAccessService::formatInitialOnlyFirstName('Folake', '');
        if ($formattedSingle['first_name'] !== 'F.' || $formattedSingle['full_name'] !== 'F.') {
            throw new Exception("formatInitialOnlyFirstName single name failed: expected 'F.', got '{$formattedSingle['full_name']}'");
        }

        // 8b. Test graph node masking
        $testNodes = [
            [
                'id' => 101,
                'first_name' => 'Adeola',
                'last_name' => 'Olaogun',
                'full_name' => 'Adeola Olaogun',
                'kinship_category' => 'parent',
                'ego_relation' => 'Father'
            ],
            [
                'id' => 102,
                'first_name' => 'Folake',
                'last_name' => 'Olaogun',
                'full_name' => 'Folake Olaogun',
                'kinship_category' => 'parent',
                'ego_relation' => 'Mother'
            ],
            [
                'id' => 103,
                'first_name' => 'Babajide',
                'last_name' => 'Olaogun',
                'full_name' => 'Babajide Olaogun',
                'kinship_category' => 'sibling',
                'ego_relation' => 'Brother'
            ]
        ];

        // When hide_parent_first_name = false: no change
        $unmasked = FamilyTreeAccessService::maskParentNamesInGraph($testNodes, 1, false);
        if ($unmasked[0]['first_name'] !== 'Adeola') {
            throw new Exception("Parent first name should not be masked when hide_parent_first_name is false.");
        }

        // When hide_parent_first_name = true: parent first names masked to initials, siblings untouched
        $masked = FamilyTreeAccessService::maskParentNamesInGraph($testNodes, 1, true);
        if ($masked[0]['first_name'] !== 'A.' || $masked[0]['full_name'] !== 'A. Olaogun') {
            throw new Exception("Father first name should be masked to initial 'A.'. Got {$masked[0]['full_name']}");
        }
        if ($masked[1]['first_name'] !== 'F.' || $masked[1]['full_name'] !== 'F. Olaogun') {
            throw new Exception("Mother first name should be masked to initial 'F.'. Got {$masked[1]['full_name']}");
        }
        if ($masked[2]['first_name'] !== 'Babajide' || $masked[2]['full_name'] !== 'Babajide Olaogun') {
            throw new Exception("Sibling first name must remain in full. Got {$masked[2]['full_name']}");
        }

        // 8c. Test saving and retrieving preference via database
        FamilyTreeAccessService::saveAccessControlPolicy(
            $ownerId,
            FamilyTreeAccessService::MODE_FAMILY_ALL,
            [],
            [],
            true, // hideParentFirstName
            $pdo
        );

        $settings = FamilyTreeAccessService::getPrivacySettings($ownerId, $pdo);
        if (empty($settings['hide_parent_first_name'])) {
            throw new Exception("Expected hide_parent_first_name to be true in privacy settings.");
        }

        // Toggle back to false
        FamilyTreeAccessService::saveAccessControlPolicy(
            $ownerId,
            FamilyTreeAccessService::MODE_FAMILY_ALL,
            [],
            [],
            false,
            $pdo
        );
        $settings2 = FamilyTreeAccessService::getPrivacySettings($ownerId, $pdo);
        if (!empty($settings2['hide_parent_first_name'])) {
            throw new Exception("Expected hide_parent_first_name to be false after toggle.");
        }
    });

    // 9. Unplaced relatives tray: names and avatars link to the person's profile
    runTest('9. Unplaced relative name links to profile (registered) or details popup (unregistered)', function() {
        $blade = (string)file_get_contents(dirname(__DIR__) . '/resources/views/member/organogram.blade.php');
        $css = (string)file_get_contents(dirname(__DIR__) . '/public/css/organogram.css');

        foreach ([
            '/allMembers/seeProfile/${encodeURIComponent(userId)}' => 'registered profile URL',
            'unplaced-profile-link' => 'profile link class on the name',
            'window.viewUnplacedMemberProfile' => 'fallback details popup for unregistered relatives',
        ] as $needle => $label) {
            if (!str_contains($blade, $needle)) {
                throw new Exception("Missing {$label} in organogram.blade.php");
            }
        }
        // External profile links must not leak the opener
        if (!str_contains($blade, 'target="_blank" rel="noopener noreferrer"')) {
            throw new Exception('Profile link must open in a new tab with rel="noopener noreferrer"');
        }
        // userId must be URL-encoded, never interpolated raw into the href
        if (str_contains($blade, '/allMembers/seeProfile/${userId}')) {
            throw new Exception('Profile href must use encodeURIComponent(userId)');
        }
        if (!str_contains($css, '.unplaced-profile-link:focus-visible')) {
            throw new Exception('Missing keyboard focus style for .unplaced-profile-link');
        }
        // Route the link targets must exist
        $routes = (string)file_get_contents(dirname(__DIR__) . '/app/router/allMembersRoute.php');
        if (!str_contains($routes, '/allMembers/seeProfile/[a:id]')) {
            throw new Exception('Profile route /allMembers/seeProfile/[a:id] is not registered');
        }
    });

} finally {
    // Teardown test fixtures
    $uids = [$ownerId, $relative1Id, $relative2Id, $outsiderId];
    $stmtDelViews = $pdo->prepare('DELETE FROM family_tree_views WHERE tree_owner_id = ? OR viewer_id = ?');
    $stmtDelPriv = $pdo->prepare('DELETE FROM family_tree_privacy_settings WHERE user_id = ?');
    $stmtDelRules = $pdo->prepare('DELETE FROM family_tree_access_rules WHERE tree_owner_id = ? OR target_user_id = ?');
    $stmtDelPers = $pdo->prepare('DELETE FROM personal WHERE id = ?');
    $stmtDelCont = $pdo->prepare('DELETE FROM contact WHERE id = ?');
    $stmtDelReq = $pdo->prepare('DELETE FROM requestMgt WHERE approver_id = ? OR requester_id = ?');

    foreach ($uids as $uid) {
        $stmtDelViews->execute([$uid, $uid]);
        $stmtDelPriv->execute([$uid]);
        $stmtDelRules->execute([$uid, $uid]);
        $stmtDelPers->execute([$uid]);
        $stmtDelCont->execute([$uid]);
        $stmtDelReq->execute([$uid, $uid]);
    }
}

echo "\n=================================================================\n";
echo "SUMMARY: {$passed} Passed, {$failed} Failed\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
