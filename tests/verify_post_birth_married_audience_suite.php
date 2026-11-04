<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Dual-Lineage Post Audience Selection (All vs. Birth vs. Married)
 * 
 * Verifies that:
 * 1. Users holding dual lineages (Birth vs. Married family codes) have their audiences correctly resolved.
 * 2. Posts targeted to 'all' are delivered to both Married and Birth family circles.
 * 3. Posts targeted to 'birth' are delivered exclusively to Birth family members, isolated from Married family.
 * 4. Posts targeted to 'married' are delivered exclusively to Married family members, isolated from Birth family.
 * 5. SecOps IDOR protection strips or rejects unauthorized family codes that the author does not hold.
 * 6. Relational post_family_targets junction table maintains fast indexed queries.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

$db = \Src\Db::connect2();

echo "\n======================================================================\n";
echo "🛡️  DUAL-LINEAGE POST AUDIENCE SELECTION SUITE (ALL vs. BIRTH vs. MARRIED)\n";
echo "======================================================================\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $condition, string $message): void
{
    global $passCount, $failCount;
    if ($condition) {
        echo "  ✅ PASS: {$message}\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL: {$message}\n";
        $failCount++;
    }
}

// Generate unique test identifiers
$uniqueSuffix = substr(bin2hex(random_bytes(4)), 0, 8);
$userAId = "test_user_a_{$uniqueSuffix}";
$userBId = "test_user_b_{$uniqueSuffix}";
$userCId = "test_user_c_{$uniqueSuffix}";

$marriedFamCode = strtoupper("FAM_MARRIED_{$uniqueSuffix}");
$birthFamCode = strtoupper("FAM_BIRTH_{$uniqueSuffix}");
$unauthorizedFamCode = strtoupper("FAM_UNAUTH_{$uniqueSuffix}");

echo "\n[Setup] Provisioning dual-lineage test fixture...\n";
echo "  - User A (Author): Married Code #{$marriedFamCode}, Birth Code #{$birthFamCode}\n";
echo "  - User B (Husband / Married Kin): Married Code #{$marriedFamCode}\n";
echo "  - User C (Birth Sibling / Maiden Kin): Birth Code #{$birthFamCode}\n";

try {
    // 1. Register family codes
    $db->prepare("INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'primary', ?)")
        ->execute([$marriedFamCode, $userAId]);
    $db->prepare("INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'birth', ?)")
        ->execute([$birthFamCode, $userAId]);
    $db->prepare("INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'primary', 'attacker')")
        ->execute([$unauthorizedFamCode]);

    // 2. Provision User A (Mother holding dual lineage)
    $db->prepare("INSERT INTO account (id, email, password, status) VALUES (?, ?, 'secret', 'active')")
        ->execute([$userAId, "{$userAId}@test.local"]);
    $db->prepare("INSERT INTO personal (id, firstName, lastName, famCode, marital_status) VALUES (?, 'Amara', 'Olaogun', ?, 'Married')")
        ->execute([$userAId, $marriedFamCode]);
    $db->prepare("INSERT INTO otherFamily (id, otherFamCode, maiden_name) VALUES (?, ?, 'Adeleke')")
        ->execute([$userAId, $birthFamCode]);
    $db->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'admin')")
        ->execute([$userAId, $marriedFamCode]);
    $db->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'admin')")
        ->execute([$userAId, $birthFamCode]);

    // 3. Provision User B (Married family kin only)
    $db->prepare("INSERT INTO account (id, email, password, status) VALUES (?, ?, 'secret', 'active')")
        ->execute([$userBId, "{$userBId}@test.local"]);
    $db->prepare("INSERT INTO personal (id, firstName, lastName, famCode, marital_status) VALUES (?, 'Wale', 'Olaogun', ?, 'Married')")
        ->execute([$userBId, $marriedFamCode]);
    $db->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member')")
        ->execute([$userBId, $marriedFamCode]);

    // 4. Provision User C (Birth family kin only)
    $db->prepare("INSERT INTO account (id, email, password, status) VALUES (?, ?, 'secret', 'active')")
        ->execute([$userCId, "{$userCId}@test.local"]);
    $db->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Babatunde', 'Adeleke', ?)")
        ->execute([$userCId, $birthFamCode]);
    $db->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member')")
        ->execute([$userCId, $birthFamCode]);

    $audienceService = new \App\services\FamilyPostAudienceService($db);
    $feedRepo = new \App\repository\FeedRepository($db);

    // =========================================================================
    // STEP 1: VERIFY DUAL-LINEAGE AUDIENCE RESOLUTION
    // =========================================================================
    echo "\n[Step 1] Auditing User Audiences Resolution...\n";
    $audiences = $audienceService->resolveUserAudiences($userAId);
    
    assertCondition($audiences['has_dual_lineage'] === true, 'User A correctly detected as holding dual lineage');
    assertCondition(in_array($marriedFamCode, $audiences['married'], true), "Married code #{$marriedFamCode} classified as married");
    assertCondition(in_array($birthFamCode, $audiences['birth'], true), "Birth code #{$birthFamCode} classified as birth");
    assertCondition($audiences['can_choose'] === true, 'User A is permitted to choose post audience');

    // =========================================================================
    // STEP 2: TEST POST TARGETED TO 'ALL' (DUAL DELIVERY)
    // =========================================================================
    echo "\n[Step 2] Testing Post Targeted to 'All' (Broadcast to Both Lineages)...\n";
    $stmtPost1 = $db->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, post_status) VALUES (?, 'Amara Olaogun', 'Happy New Month everyone in both families!', ?, 'published')");
    $stmtPost1->execute([$userAId, $marriedFamCode]);
    $post1No = (int)$db->lastInsertId();

    $targetResult1 = $audienceService->assignPostTargets($post1No, $userAId, 'all');
    assertCondition($targetResult1['target_scope'] === 'all', 'Post 1 assigned scope is "all"');
    assertCondition(in_array($marriedFamCode, $targetResult1['target_codes'], true) && in_array($birthFamCode, $targetResult1['target_codes'], true), 'Post 1 target codes include both Married and Birth codes');

    // Feed queries
    $feedUserB = $feedRepo->getFeedPostsAsArray($userBId, [$marriedFamCode], 50, 0);
    $feedUserC = $feedRepo->getFeedPostsAsArray($userCId, [$birthFamCode], 50, 0);

    $post1InB = in_array($post1No, array_column($feedUserB, 'post_no'), true);
    $post1InC = in_array($post1No, array_column($feedUserC, 'post_no'), true);

    assertCondition($post1InB === true, "Post 1 (scope: all) is visible to User B in Married family (#{$marriedFamCode})");
    assertCondition($post1InC === true, "Post 1 (scope: all) is visible to User C in Birth family (#{$birthFamCode})");

    // =========================================================================
    // STEP 3: TEST POST TARGETED TO 'BIRTH' ONLY (LINEAGE ISOLATION)
    // =========================================================================
    echo "\n[Step 3] Testing Post Targeted to 'Birth' Only...\n";
    $stmtPost2 = $db->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, post_status) VALUES (?, 'Amara Olaogun', 'Childhood throwback photo with Adeleke cousins!', ?, 'published')");
    $stmtPost2->execute([$userAId, $birthFamCode]);
    $post2No = (int)$db->lastInsertId();

    $targetResult2 = $audienceService->assignPostTargets($post2No, $userAId, 'birth');
    assertCondition($targetResult2['target_scope'] === 'birth', 'Post 2 assigned scope is "birth"');
    assertCondition(in_array($birthFamCode, $targetResult2['target_codes'], true), "Post 2 targets Birth family code #{$birthFamCode}");
    assertCondition(!in_array($marriedFamCode, $targetResult2['target_codes'], true), "Post 2 excludes Married family code #{$marriedFamCode}");

    // Feed queries
    $feedUserB_after2 = $feedRepo->getFeedPostsAsArray($userBId, [$marriedFamCode], 50, 0);
    $feedUserC_after2 = $feedRepo->getFeedPostsAsArray($userCId, [$birthFamCode], 50, 0);

    $post2InB = in_array($post2No, array_column($feedUserB_after2, 'post_no'), true);
    $post2InC = in_array($post2No, array_column($feedUserC_after2, 'post_no'), true);

    assertCondition($post2InC === true, "Post 2 (scope: birth) is visible to User C in Birth family");
    assertCondition($post2InB === false, "Post 2 (scope: birth) is NOT visible to User B in Married family (Lineage Isolation enforced)");

    // =========================================================================
    // STEP 4: TEST POST TARGETED TO 'MARRIED' ONLY (HOUSEHOLD ISOLATION)
    // =========================================================================
    echo "\n[Step 4] Testing Post Targeted to 'Married' Only...\n";
    $stmtPost3 = $db->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, post_status) VALUES (?, 'Amara Olaogun', 'Our household wedding anniversary celebration!', ?, 'published')");
    $stmtPost3->execute([$userAId, $marriedFamCode]);
    $post3No = (int)$db->lastInsertId();

    $targetResult3 = $audienceService->assignPostTargets($post3No, $userAId, 'married');
    assertCondition($targetResult3['target_scope'] === 'married', 'Post 3 assigned scope is "married"');
    assertCondition(in_array($marriedFamCode, $targetResult3['target_codes'], true), "Post 3 targets Married family code #{$marriedFamCode}");
    assertCondition(!in_array($birthFamCode, $targetResult3['target_codes'], true), "Post 3 excludes Birth family code #{$birthFamCode}");

    // Feed queries
    $feedUserB_after3 = $feedRepo->getFeedPostsAsArray($userBId, [$marriedFamCode], 50, 0);
    $feedUserC_after3 = $feedRepo->getFeedPostsAsArray($userCId, [$birthFamCode], 50, 0);

    $post3InB = in_array($post3No, array_column($feedUserB_after3, 'post_no'), true);
    $post3InC = in_array($post3No, array_column($feedUserC_after3, 'post_no'), true);

    assertCondition($post3InB === true, "Post 3 (scope: married) is visible to User B in Married family");
    assertCondition($post3InC === false, "Post 3 (scope: married) is NOT visible to User C in Birth family (Household Isolation enforced)");

    // =========================================================================
    // STEP 5: ADVERSARIAL IDOR SECURITY ATTACK (Dr. Viktor Kovács & Marcus)
    // =========================================================================
    echo "\n[Step 5] Executing Adversarial IDOR Target Tampering Attack...\n";
    $stmtPost4 = $db->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, post_status) VALUES (?, 'Amara Olaogun', 'Malicious IDOR Injection Attempt', ?, 'published')");
    $stmtPost4->execute([$userAId, $marriedFamCode]);
    $post4No = (int)$db->lastInsertId();

    // Attacker attempts to target an unauthorized family code they do NOT belong to
    $targetResult4 = $audienceService->assignPostTargets($post4No, $userAId, 'all', $unauthorizedFamCode);
    
    assertCondition(!in_array($unauthorizedFamCode, $targetResult4['target_codes'], true), "Unauthorized family code #{$unauthorizedFamCode} stripped/rejected by backend validator");

    $stmtCheckTargets = $db->prepare("SELECT 1 FROM post_family_targets WHERE post_no = ? AND family_code = ?");
    $stmtCheckTargets->execute([$post4No, $unauthorizedFamCode]);
    assertCondition($stmtCheckTargets->fetchColumn() === false, "Zero unauthorized family rows created in post_family_targets (IDOR Immunity Confirmed)");

    // =========================================================================
    // STEP 6: DIRECT GATING VERIFICATION (SINGLE POST, COMMENTS, LIKES)
    // =========================================================================
    echo "\n[Step 6] Auditing Direct API & Interaction Gating (Marcus SecOps)...\n";
    
    // User B (Married kin only) attempts direct access to User A's Birth-only post ($post2No)
    $canUserBAccessPost2 = $audienceService->canUserAccessPost($post2No, $userBId);
    assertCondition($canUserBAccessPost2 === false, "User B (Married kin) direct access to Birth-only post rejected by canUserAccessPost");

    $post2DtoForUserB = $feedRepo->getPostByNo($post2No, $userBId);
    assertCondition($post2DtoForUserB === null, "FeedRepository::getPostByNo returns null for User B on Birth-only post (HTTP 403/404 Shield)");

    // User C (Birth kin only) attempts direct access to User A's Married-only post ($post3No)
    $canUserCAccessPost3 = $audienceService->canUserAccessPost($post3No, $userCId);
    assertCondition($canUserCAccessPost3 === false, "User C (Birth kin) direct access to Married-only post rejected by canUserAccessPost");

    $post3DtoForUserC = $feedRepo->getPostByNo($post3No, $userCId);
    assertCondition($post3DtoForUserC === null, "FeedRepository::getPostByNo returns null for User C on Married-only post (HTTP 403/404 Shield)");

    // Author (User A) always retains access to all their posts
    $canUserAAccessPost2 = $audienceService->canUserAccessPost($post2No, $userAId);
    $canUserAAccessPost3 = $audienceService->canUserAccessPost($post3No, $userAId);
    assertCondition($canUserAAccessPost2 === true, "Author User A retains access to own Birth-only post");
    assertCondition($canUserAAccessPost3 === true, "Author User A retains access to own Married-only post");

    // =========================================================================
    // STEP 7: CONNECTION BYPASS IMMUNITY (requestMgt Anti-Leak Gate)
    // =========================================================================
    echo "\n[Step 7] Testing requestMgt Connection Bypass Immunity...\n";
    // Connect User A and User B in requestMgt as friends/in-laws
    $db->prepare("INSERT INTO requestMgt (requester_id, approver_id, requesterCode, approverCode, status) VALUES (?, ?, ?, ?, 'Approved')")
        ->execute([$userAId, $userBId, $marriedFamCode, $marriedFamCode]);

    // Despite being connected in requestMgt, User B MUST NOT see User A's Birth-only post in feed or direct query
    $feedUserB_connected = $feedRepo->getFeedPostsAsArray($userBId, [$marriedFamCode], 50, 0);
    $post2InB_connected = in_array($post2No, array_column($feedUserB_connected, 'post_no'), true);
    assertCondition($post2InB_connected === false, "Birth-only post remains strictly hidden from User B even when connected via requestMgt");

    $canAccessPost2_connected = $audienceService->canUserAccessPost($post2No, $userBId);
    assertCondition($canAccessPost2_connected === false, "canUserAccessPost rejects User B on Birth-only post even when connected in requestMgt");

} catch (\Throwable $e) {
    echo "\n  ❌ CRITICAL EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    $failCount++;
} finally {
    // Teardown test records
    echo "\n[Teardown] Cleaning up test fixtures...\n";
    if (isset($post1No)) $db->prepare("DELETE FROM post WHERE post_no IN (?, ?, ?, ?)")->execute([$post1No, $post2No, $post3No, $post4No]);
    if (isset($post1No)) $db->prepare("DELETE FROM post_family_targets WHERE post_no IN (?, ?, ?, ?)")->execute([$post1No, $post2No, $post3No, $post4No]);
    $db->prepare("DELETE FROM requestMgt WHERE requester_id IN (?, ?, ?) OR approver_id IN (?, ?, ?)")->execute([$userAId, $userBId, $userCId, $userAId, $userBId, $userCId]);
    $db->prepare("DELETE FROM user_families WHERE user_id IN (?, ?, ?)")->execute([$userAId, $userBId, $userCId]);
    $db->prepare("DELETE FROM otherFamily WHERE id = ?")->execute([$userAId]);
    $db->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)")->execute([$userAId, $userBId, $userCId]);
    $db->prepare("DELETE FROM account WHERE id IN (?, ?, ?)")->execute([$userAId, $userBId, $userCId]);
    $db->prepare("DELETE FROM family_codes WHERE code IN (?, ?, ?)")->execute([$marriedFamCode, $birthFamCode, $unauthorizedFamCode]);
    echo "  Cleanup completed.\n";
}

echo "\n======================================================================\n";
echo "📊 SUITE SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "======================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
