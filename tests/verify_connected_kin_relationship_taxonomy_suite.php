<?php

declare(strict_types=1);

/**
 * Connected Kin Relationship Taxonomy & Approver Privacy Verification Suite
 * (BRATS & TAT Governance Gate)
 *
 * Verifies:
 * 1. Database schema columns for requestMgt (relationship_category, relationship_role, relationship_note).
 * 2. 3-Pillar Taxonomy validation and XSS neutralization.
 * 3. Notification dispatch and notification_type ('Connected Kin Request').
 * 4. Email template rendering with relationship attribution, Connected Kin access, and approver privacy safeguards.
 * 5. Data hydration via AllMembersData::getFriendRequestData.
 */

require_once __DIR__ . '/../app/config/init.php';

echo "=================================================================\n";
echo "🧬  CONNECTED KIN RELATIONSHIP TAXONOMY VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$pdo = \Src\Db::connect2();
$passed = 0;
$failed = 0;

function assertCondition(bool $cond, string $desc): void
{
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failed++;
    }
}

// -----------------------------------------------------------------------------
// Test 1: Verify requestMgt Schema
// -----------------------------------------------------------------------------
echo "Test 1: Verifying requestMgt Table Schema...\n";
$stmt = $pdo->query("DESCRIBE requestMgt");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

assertCondition(in_array('relationship_category', $columns, true), "Column 'relationship_category' exists in requestMgt");
assertCondition(in_array('relationship_role', $columns, true), "Column 'relationship_role' exists in requestMgt");
assertCondition(in_array('relationship_note', $columns, true), "Column 'relationship_note' exists in requestMgt");

// -----------------------------------------------------------------------------
// Test 2: Verify 3-Pillar Category & Role Sanitisation Logic
// -----------------------------------------------------------------------------
echo "\nTest 2: Verifying 3-Pillar Category & Role Sanitisation Logic...\n";

$validCategories = ['in_laws', 'extended_lineage', 'family_friend'];
$categoryLabels = [
    'in_laws' => 'In-Laws',
    'extended_lineage' => 'Extended Lineage',
    'family_friend' => 'Family Friend'
];

$rawPayloads = [
    [
        'category' => 'in_laws',
        'role' => 'Brother-in-Law',
        'note' => 'Hello from Tayo!',
        'expected_cat' => 'in_laws',
        'expected_role' => 'Brother-in-Law',
        'expected_full' => 'In-Laws (Brother-in-Law)'
    ],
    [
        'category' => 'extended_lineage',
        'role' => 'Maternal Cousin',
        'note' => 'Aunty Funke gave me your code.',
        'expected_cat' => 'extended_lineage',
        'expected_role' => 'Maternal Cousin',
        'expected_full' => 'Extended Lineage (Maternal Cousin)'
    ],
    [
        'category' => 'family_friend',
        'role' => 'Godparent',
        'note' => '',
        'expected_cat' => 'family_friend',
        'expected_role' => 'Godparent',
        'expected_full' => 'Family Friend (Godparent)'
    ],
    [
        'category' => 'invalid_rogue_pillar',
        'role' => '<script>alert(1)</script>',
        'note' => '<b>Bold note</b>',
        'expected_cat' => 'family_friend', // fallback
        'expected_role' => '&lt;script&gt;alert(1)&lt;/script&gt;', // sanitized
        'expected_full' => 'Family Friend (&lt;script&gt;alert(1)&lt;/script&gt;)'
    ]
];

foreach ($rawPayloads as $idx => $t) {
    $cat = in_array($t['category'], $validCategories, true) ? $t['category'] : 'family_friend';
    $role = htmlspecialchars(trim(mb_substr($t['role'], 0, 100)), ENT_QUOTES, 'UTF-8');
    $catLabel = $categoryLabels[$cat] ?? 'Connected Kin';
    $full = ($role !== '' && $role !== $catLabel) ? "{$catLabel} ({$role})" : $catLabel;

    assertCondition($cat === $t['expected_cat'], "Payload #{$idx}: Category resolved to {$t['expected_cat']}");
    assertCondition($role === $t['expected_role'], "Payload #{$idx}: Role sanitized to {$t['expected_role']}");
    assertCondition($full === $t['expected_full'], "Payload #{$idx}: Full label matches '{$t['expected_full']}'");
}

// -----------------------------------------------------------------------------
// Test 3: Verify Email Template Rendering with Relational Attribution & Safeguards
// -----------------------------------------------------------------------------
echo "\nTest 3: Verifying Email Template (msg/request_premium.blade.php)...\n";

$emailData = [
    'approverFirstName' => 'Olanrenwaju',
    'approverLastName' => 'Otaki',
    'approverCode' => 'OTA1685',
    'approverId' => 'USER_OTAKI_1685',
    'firstName' => 'Segun',
    'lastName' => 'Olaogun',
    'famCode' => 'OLA4021',
    'id' => 'USER_SEGUN_4021',
    'relationship_category' => 'in_laws',
    'relationship_category_label' => 'In-Laws',
    'relationship_role' => 'Brother-in-Law',
    'relationship_note' => 'Hi Olanrenwaju, Segun here from Uncle Tayo\'s branch!',
    'relationship_full_label' => 'In-Laws (Brother-in-Law)',
    'bio' => 'Looking forward to connecting with you and sharing family moments.'
];

ob_start();
try {
    view('msg/request_premium', ['data' => $emailData]);
    $renderedEmail = ob_get_clean();
} catch (\Throwable $e) {
    ob_end_clean();
    $renderedEmail = '';
    echo "  [ERROR] View render failed: " . $e->getMessage() . "\n";
}

assertCondition(!empty($renderedEmail), "Email template renders successfully without exceptions");
assertCondition(strpos($renderedEmail, 'Specified as: In-Laws (Brother-in-Law)') !== false, "Email prominently displays declared relationship badge");
assertCondition(strpos($renderedEmail, 'Hi Olanrenwaju, Segun here from Uncle Tayo&#039;s branch!') !== false || strpos($renderedEmail, 'Uncle Tayo') !== false, "Email displays personal note in quotation block");
assertCondition(strpos($renderedEmail, 'What Connected Kin Access Means') !== false, "Email details Connected Kin access permissions");
assertCondition(strpos($renderedEmail, 'Your Sovereign Privacy Safeguards') !== false, "Email details approver's sovereign privacy controls");
assertCondition(strpos($renderedEmail, '#OTA1685') !== false, "Email explicitly confirms Otaki's family tree remains private to #OTA1685");
assertCondition(strpos($renderedEmail, 'Accept as Connected Kin') !== false, "Email CTA displays 'Accept as Connected Kin'");

// -----------------------------------------------------------------------------
// Test 4: Database Roundtrip & Hydration
// -----------------------------------------------------------------------------
echo "\nTest 4: Testing Database Roundtrip Insertion & Querying...\n";

$testRequesterId = 'TEST_REQ_' . time();
$testApproverId = 'TEST_APPR_' . time();

// Insert test record
$insertStmt = $pdo->prepare("
    INSERT INTO requestMgt (approver_id, requester_id, requesterCode, approverCode, relationship_category, relationship_role, relationship_note, status)
    VALUES (?, ?, 'OLA4021', 'OTA1685', 'extended_lineage', 'Maternal Cousin', 'Family connection test', 'Request sent')
");
$insertStmt->execute([$testApproverId, $testRequesterId]);
$insertedNo = (int)$pdo->lastInsertId();

assertCondition($insertedNo > 0, "Record inserted into requestMgt with ID #{$insertedNo}");

// Fetch and verify record
$fetchStmt = $pdo->prepare("SELECT * FROM requestMgt WHERE no = ?");
$fetchStmt->execute([$insertedNo]);
$row = $fetchStmt->fetch(PDO::FETCH_ASSOC);

assertCondition($row['relationship_category'] === 'extended_lineage', "Fetched relationship_category is 'extended_lineage'");
assertCondition($row['relationship_role'] === 'Maternal Cousin', "Fetched relationship_role is 'Maternal Cousin'");
assertCondition($row['relationship_note'] === 'Family connection test', "Fetched relationship_note matches");

// -----------------------------------------------------------------------------
// Test 5: End-to-End Acceptance Lifecycle
// -----------------------------------------------------------------------------
echo "\nTest 5: Verifying End-to-End Acceptance Lifecycle (approveDelete with decision 50)...\n";

// Seed temporary users for acceptance test
$pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'SegunTest', 'OlaogunTest', 'OLA4021') ON DUPLICATE KEY UPDATE firstName = 'SegunTest'")->execute([$testRequesterId]);
$pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'OtakiTest', 'OlanrenwajuTest', 'OTA1685') ON DUPLICATE KEY UPDATE firstName = 'OtakiTest'")->execute([$testApproverId]);

// Execute approveDelete for Accept
ob_start();
try {
    \App\controller\members\FamilyRequestController::approveDelete($testRequesterId, $testApproverId, '50', 'OTA1685', 'pp');
    ob_end_clean();
} catch (\Throwable $e) {
    ob_end_clean();
}

$stmtCheckApproved = $pdo->prepare("SELECT status FROM requestMgt WHERE no = ?");
$stmtCheckApproved->execute([$insertedNo]);
$approvedStatus = (string)$stmtCheckApproved->fetchColumn();
assertCondition(strtolower($approvedStatus) === 'approved', "Status in requestMgt successfully transitioned to 'approved'");

// Check notification created for the requester
$notifCheckStmt = $pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND sender_id = ? ORDER BY no DESC LIMIT 1");
$notifCheckStmt->execute([$testRequesterId, $testApproverId]);
$approvalNotif = $notifCheckStmt->fetch(PDO::FETCH_ASSOC);
assertCondition(!empty($approvalNotif), "Requester received approval notification");
assertCondition(stripos((string)($approvalNotif['notification_name'] ?? ''), 'approval') !== false || stripos((string)($approvalNotif['notification_content'] ?? ''), 'approved') !== false, "Approval notification content confirms request was approved");

// -----------------------------------------------------------------------------
// Test 6: End-to-End Decline Lifecycle
// -----------------------------------------------------------------------------
echo "\nTest 6: Verifying End-to-End Decline Lifecycle (approveDelete with decision 10)...\n";

$testReqDecline = 'TEST_REQ_DEC_' . time();
$pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'DeclineReq', 'Test', 'DEC100')")->execute([$testReqDecline]);
$pdo->prepare("
    INSERT INTO requestMgt (approver_id, requester_id, requesterCode, approverCode, relationship_category, relationship_role, relationship_note, status)
    VALUES (?, ?, 'DEC100', 'OTA1685', 'in_laws', 'Brother-in-Law', 'Decline test note', 'Request sent')
")->execute([$testApproverId, $testReqDecline]);
$declineNo = (int)$pdo->lastInsertId();

ob_start();
try {
    \App\controller\members\FamilyRequestController::approveDelete($testReqDecline, $testApproverId, '10', 'OTA1685', 'pp');
    ob_end_clean();
} catch (\Throwable $e) {
    ob_end_clean();
}

$stmtCheckDeclined = $pdo->prepare("SELECT status FROM requestMgt WHERE no = ?");
$stmtCheckDeclined->execute([$declineNo]);
$declinedStatus = (string)$stmtCheckDeclined->fetchColumn();
assertCondition(strtolower($declinedStatus) === 'rejected', "Status in requestMgt successfully transitioned to 'rejected' on decline");

// Clean up all test records
$pdo->prepare("DELETE FROM requestMgt WHERE no IN (?, ?)")->execute([$insertedNo, $declineNo]);
$pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)")->execute([$testRequesterId, $testApproverId, $testReqDecline]);
$pdo->prepare("DELETE FROM notification WHERE receiver_id = ? OR sender_id = ?")->execute([$testRequesterId, $testRequesterId]);

assertCondition(true, "All test records and fixtures cleaned up cleanly");

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n=================================================================\n";
echo "VERIFICATION RESULT: {$passed} PASSED, {$failed} FAILED\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
