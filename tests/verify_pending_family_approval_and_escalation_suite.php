<?php

declare(strict_types=1);

/**
 * Test Suite: verify_pending_family_approval_and_escalation_suite.php
 * Verifies:
 * 1. Registration with existing family code forces status='pending' and keeps personal.famCode null.
 * 2. Pending users can log in, but private family posts and feed are locked (holding state active).
 * 3. Approver nudge mechanism functions under 20 mins with strict 5-minute rate limit.
 * 4. 20-minute escalation broadcast activates when overdue, allowing any verified family member to approve.
 * 5. Full access (personal.famCode, tree node, feed access) unlocks upon approval.
 */

require_once __DIR__ . '/../app/config/init.php';

use App\service\FamilyCodeApprovalService;
use App\service\NotificationService;
use App\services\ProfilepageService;
use App\model\AllMembersData;
use Src\Db;

$pdo = Db::connect2();

echo "========================================================================\n";
echo "🔬 [TEST SUITE] PENDING FAMILY APPROVAL, NUDGE & 20-MIN ESCALATION\n";
echo "========================================================================\n";

$passCount = 0;
$testCount = 0;

function assertCondition(bool $condition, string $testName, string $details = ''): void {
    global $passCount, $testCount;
    $testCount++;
    if ($condition) {
        $passCount++;
        echo "  ✅ PASS: {$testName}\n";
    } else {
        echo "  ❌ FAIL: {$testName}\n";
        if ($details !== '') {
            echo "     Details: {$details}\n";
        }
    }
}

// -------------------------------------------------------------------------
// Setup Test Fixtures
// -------------------------------------------------------------------------
$rand = bin2hex(random_bytes(4));
$familyCode = "FAMTEST" . strtoupper($rand);
$approverId = "usr_approver_" . $rand;
$helperMemberId = "usr_helper_" . $rand;
$requesterId = "usr_requester_" . $rand;

echo "\n--- Setting up test environment with family code: #{$familyCode} ---\n";

// 1. Create family founder / approver
$pdo->prepare("INSERT INTO account (id, email, password, status, type) VALUES (?, ?, 'hashed_pass', 'active', 'member')")
    ->execute([$approverId, "approver_{$rand}@example.com"]);
$pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'ApproverFirst', 'ApproverLast', ?)")
    ->execute([$approverId, $familyCode]);
$pdo->prepare("INSERT INTO contact (id, email, country, mobile) VALUES (?, ?, 'United Kingdom', '07000000001')")
    ->execute([$approverId, "approver_{$rand}@example.com"]);
$pdo->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'admin')")
    ->execute([$approverId, $familyCode]);
$pdo->prepare("INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'primary', ?)")
    ->execute([$familyCode, $approverId]);

// 2. Create another verified family member (helper) who is NOT the nominated inviter
$pdo->prepare("INSERT INTO account (id, email, password, status, type) VALUES (?, ?, 'hashed_pass', 'active', 'member')")
    ->execute([$helperMemberId, "helper_{$rand}@example.com"]);
$pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'HelperFirst', 'HelperLast', ?)")
    ->execute([$helperMemberId, $familyCode]);
$pdo->prepare("INSERT INTO contact (id, email, country, mobile) VALUES (?, ?, 'United Kingdom', '07000000002')")
    ->execute([$helperMemberId, "helper_{$rand}@example.com"]);
$pdo->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member')")
    ->execute([$helperMemberId, $familyCode]);

// 3. Create requester account
$pdo->prepare("INSERT INTO account (id, email, password, status, type) VALUES (?, ?, 'hashed_pass', 'active', 'member')")
    ->execute([$requesterId, "requester_{$rand}@example.com"]);
$pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'NewRequester', 'FamilySeeker', NULL)")
    ->execute([$requesterId]);
$pdo->prepare("INSERT INTO contact (id, email, country, mobile) VALUES (?, ?, 'United Kingdom', '07000000003')")
    ->execute([$requesterId, "requester_{$rand}@example.com"]);

$approvalService = new FamilyCodeApprovalService($pdo);

// -------------------------------------------------------------------------
// Test 1: Family Code Existence Check & Registration Simulation
// -------------------------------------------------------------------------
echo "\n--- TEST 1: Family Code Existence & Pending Registration Gate ---\n";

$exists = $approvalService->familyCodeExists($familyCode);
assertCondition($exists === true, "Existing family code is correctly recognised by server");

// Simulate registration table generation for joining member
$regData = [
    'id' => $requesterId,
    'firstName' => 'NewRequester',
    'lastName' => 'FamilySeeker',
    'email' => "requester_{$rand}@example.com",
    'famCode' => $familyCode,
    'familyStatus' => 'pending' // Gated server-side because family code exists
];
$tables = \App\model\RegisterTableData::createRegisterTable($regData);

assertCondition($tables['personal']['famCode'] === null, "personal.famCode remains NULL during pending registration");
assertCondition(($tables['user_families']['status'] ?? '') === 'pending', "user_families is assigned pending status");
assertCondition(($tables['user_families']['role'] ?? '') === 'member', "user_families role is member (not admin)");

// Insert user_families record as pending
$pdo->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'pending', 'member')")
    ->execute([$requesterId, $familyCode]);

// Create approval request with nominated approver
$approvalData = $approvalService->createApprovalRequest(
    $requesterId,
    $familyCode,
    'ApproverFirst',
    'ApproverLast',
    "approver_{$rand}@example.com"
);
$requestId = (int)$approvalData['request_id'];
$approvalService->setApproverId($requestId, $approverId);

assertCondition($requestId > 0, "Approval request generated with ID: {$requestId}");

// -------------------------------------------------------------------------
// Test 2: Login Session Hydration & Feed Locking
// -------------------------------------------------------------------------
echo "\n--- TEST 2: Login Hydration & Privacy Wall Protection ---\n";

$memberFamCodes = AllMembersData::getFamCode($requesterId);
$activeCodes = $memberFamCodes['famCode'] ?? [];
assertCondition(empty($activeCodes), "AllMembersData::getFamCode does not return unapproved family codes for pending member");

// ProfilepageService profile retrieval for pending user
$profileService = new ProfilepageService();
$profileData = $profileService->getProfileData($requesterId);

assertCondition($profileData['isPendingApproval'] === true, "ProfilepageService flags user as isPendingApproval === true");
assertCondition(empty($profileData['posts']), "Private family posts are completely empty for pending user");
assertCondition(!empty($profileData['pendingApprovalData']), "pendingApprovalData is passed to view for holding card");
assertCondition(($profileData['pendingApprovalData']['family_code'] ?? '') === $familyCode, "Holding card targets correct family code");

// -------------------------------------------------------------------------
// Test 3: Nudge Mechanism Under 20 Minutes (Direct Approver Target)
// -------------------------------------------------------------------------
echo "\n--- TEST 3: Nudge Approver Under 20 Minutes ---\n";

$nudgeResult = $approvalService->nudgeApprover($requestId, $requesterId);
assertCondition($nudgeResult['success'] === true, "Initial nudge succeeds");
assertCondition(($nudgeResult['broadcasted'] ?? true) === false, "Nudge is targeted directly to inviter (not broadcasted yet)");

// Verify in-app notification delivered to nominated approver
$stmtNotif = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND notification_name = 'Approval Nudge'");
$stmtNotif->execute([$approverId]);
assertCondition(((int)$stmtNotif->fetchColumn()) > 0, "Durable notification recorded for nominated approver");

// Cooldown verification: second immediate nudge must be rejected
$rateLimitNudge = $approvalService->nudgeApprover($requestId, $requesterId);
assertCondition($rateLimitNudge['success'] === false, "Immediate subsequent nudge rejected by 5-minute cooldown timer");

// -------------------------------------------------------------------------
// Test 4: 20-Minute Escalation Broadcast
// -------------------------------------------------------------------------
echo "\n--- TEST 4: 20-Minute Family Escalation Broadcast ---\n";

// Check that initially, the helper member does NOT see the request in pending list
$initialHelperRequests = $approvalService->getPendingApprovalsWithDetails($helperMemberId, $familyCode);
assertCondition(empty($initialHelperRequests), "Non-nominated family member cannot see approval card before 20 minutes");

// Simulate passage of 25 minutes by setting created_at to 25 minutes ago and clearing nudge timestamp
$pdo->prepare("UPDATE family_approval_requests SET created_at = DATE_SUB(NOW(), INTERVAL 25 MINUTE), last_nudged_at = DATE_SUB(NOW(), INTERVAL 6 MINUTE) WHERE no = ?")
    ->execute([$requestId]);

// Run escalation check
$escalatedCount = $approvalService->escalateOverdueRequests($requestId);
assertCondition($escalatedCount >= 1, "escalateOverdueRequests identifies and escalates overdue request");

// Verify broadcast notification delivered to the other family member (helper)
$stmtBroadNotif = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND notification_name = 'Family Request Escalation'");
$stmtBroadNotif->execute([$helperMemberId]);
assertCondition(((int)$stmtBroadNotif->fetchColumn()) > 0, "Broadcast escalation notification delivered to all family members");

// Helper member should now see the request in their pending list!
$escalatedHelperRequests = $approvalService->getPendingApprovalsWithDetails($helperMemberId, $familyCode);
assertCondition(!empty($escalatedHelperRequests), "Overdue request is now visible to all family members for review");
assertCondition(!empty($escalatedHelperRequests[0]['is_escalated']), "Request is marked with is_escalated badge");

// -------------------------------------------------------------------------
// Test 5: Approval Execution by Escalated Family Member
// -------------------------------------------------------------------------
echo "\n--- TEST 5: Approval by Verified Family Member Unlocks Access ---\n";

// Helper member approves the request on behalf of the family
$approved = $approvalService->approveRequest($requestId, $helperMemberId);
assertCondition($approved === true, "Any verified family member can approve once escalated");

// Verify multi-table state transitions
$chkReq = $pdo->prepare("SELECT status, approver_id FROM family_approval_requests WHERE no = ?");
$chkReq->execute([$requestId]);
$reqRow = $chkReq->fetch(PDO::FETCH_ASSOC);
assertCondition(($reqRow['status'] ?? '') === 'approved', "family_approval_requests status transitioned to approved");
assertCondition(($reqRow['approver_id'] ?? '') === $helperMemberId, "family_approval_requests records effective approver ID");

$chkUf = $pdo->prepare("SELECT status, role FROM user_families WHERE user_id = ? AND family_code = ?");
$chkUf->execute([$requesterId, $familyCode]);
$ufRow = $chkUf->fetch(PDO::FETCH_ASSOC);
assertCondition(($ufRow['status'] ?? '') === 'approved', "user_families status transitioned to approved");

$chkPersonal = $pdo->prepare("SELECT famCode FROM personal WHERE id = ?");
$chkPersonal->execute([$requesterId]);
$personalCode = (string)$chkPersonal->fetchColumn();
assertCondition($personalCode === $familyCode, "personal.famCode populated with family code upon approval");

// Verify tree node claimed
$chkNode = $pdo->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND user_id = ?");
$chkNode->execute([$familyCode, $requesterId]);
assertCondition(((int)$chkNode->fetchColumn()) > 0, "Family tree node initialized/claimed upon approval");

// Verify post-approval profile loading
$approvedFamCodes = AllMembersData::getFamCode($requesterId);
assertCondition(in_array($familyCode, $approvedFamCodes['famCode'] ?? [], true), "AllMembersData::getFamCode now returns approved family code");

$unlockedProfile = $profileService->getProfileData($requesterId);
assertCondition($unlockedProfile['isPendingApproval'] === false, "User is no longer marked as isPendingApproval");

// -------------------------------------------------------------------------
// Teardown
// -------------------------------------------------------------------------
echo "\n--- Cleaning up test fixtures ---\n";
$pdo->prepare("DELETE FROM notification WHERE receiver_id IN (?, ?, ?) OR sender_id = ?")->execute([$approverId, $helperMemberId, $requesterId, $requesterId]);
$pdo->prepare("DELETE FROM family_approval_requests WHERE no = ?")->execute([$requestId]);
$pdo->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$familyCode]);
$pdo->prepare("DELETE FROM user_families WHERE family_code = ?")->execute([$familyCode]);
$pdo->prepare("DELETE FROM family_codes WHERE code = ?")->execute([$familyCode]);
$pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)")->execute([$approverId, $helperMemberId, $requesterId]);
$pdo->prepare("DELETE FROM contact WHERE id IN (?, ?, ?)")->execute([$approverId, $helperMemberId, $requesterId]);
$pdo->prepare("DELETE FROM account WHERE id IN (?, ?, ?)")->execute([$approverId, $helperMemberId, $requesterId]);

echo "\n========================================================================\n";
echo "📊 RESULTS: {$passCount}/{$testCount} tests passed.\n";
echo "========================================================================\n";

if ($passCount === $testCount) {
    exit(0);
} else {
    exit(1);
}
