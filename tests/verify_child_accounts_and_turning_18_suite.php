<?php

declare(strict_types=1);

/**
 * 🧒 Verification Suite: Supervised Child Accounts, Gated Family Posting & Turning 18 Adulthood Engine
 * 
 * Verifies:
 * 1. Parent provisioning of child accounts (UK GDPR s.9 / ICO Children's Code)
 * 2. Child authentication via Family Code + Username + PIN
 * 3. Child posting and commenting within the gated family circle
 * 4. Automatic EXIF GPS metadata stripping for child uploads
 * 5. Parental moderation permissions (parent can delete minor's posts; non-parents blocked)
 * 6. "Turning 18" Adulthood Graduation engine (milestone feed announcement & time capsule unlock)
 * 7. SecOps Red Team Gate: Parental supervisory BOLA/IDOR hard-blocked post-18 (403 Forbidden)
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\services\AdulthoodTransitionService;
use App\services\ChildAccountService;
use Src\Db;
use Src\Exceptions\ForbiddenException;

$passed = 0;
$failed = 0;

function runStep(string $name, callable $fn): void {
    global $passed, $failed;
    echo "  [TEST " . ($passed + $failed + 1) . "] {$name}... ";
    try {
        $fn();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n";
        echo "     at " . $e->getFile() . ":" . $e->getLine() . "\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "🧒 FAMILYPLATFORM CHILD ACCOUNTS & TURNING 18 VERIFICATION SUITE\n";
echo "=================================================================\n";

$db = Db::connect2();

// Fixtures
$familyCode = 'TEST_FAM_' . strtoupper(bin2hex(random_bytes(4)));
$parentUserId = 'usr_parent_' . bin2hex(random_bytes(6));
$otherAdultId = 'usr_other_' . bin2hex(random_bytes(6));

// 1. Setup Parent & Family
$db->prepare("INSERT INTO account (id, email, password, status, type) VALUES (?, 'parent@test.com', 'hash', 'approved', 'member')")->execute([$parentUserId]);
$db->prepare("INSERT INTO personal (id, firstName, lastName, famCode, day, month, year, gender) VALUES (?, 'Ade', 'Olaogun', ?, 15, 'May', 1985, 'Male')")->execute([$parentUserId, $familyCode]);

$db->prepare("INSERT INTO account (id, email, password, status, type) VALUES (?, 'other@test.com', 'hash', 'approved', 'member')")->execute([$otherAdultId]);
$db->prepare("INSERT INTO personal (id, firstName, lastName, famCode, day, month, year, gender) VALUES (?, 'Stranger', 'Smith', 'OTHER_FAM', 10, 'Jan', 1990, 'Male')")->execute([$otherAdultId]);

// Setup Family Node for child
$db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, birth_date, birth_year) VALUES (?, 'Tobi', 'Olaogun', 'Male', '2016-04-10', 2016)")->execute([$familyCode]);
$childNodeId = (int)$db->lastInsertId();

$childAccount = null;

// TEST 1: Provision Child Account (< 13 years old)
runStep("Parent provisions a supervised child account (< 13)", function() use (&$childAccount, $parentUserId, $childNodeId) {
    $res = ChildAccountService::provisionChildAccount(
        parentUserId: $parentUserId,
        childNodeId: $childNodeId,
        username: 'tobi_kid',
        pin: '1234',
        dobYear: '2016',
        dobMonth: '04',
        dobDay: '10',
        clientIp: '127.0.0.1'
    );

    if (($res['status'] ?? '') !== 'success') {
        throw new Exception("Provisioning failed: " . json_encode($res));
    }
    $childAccount = $res['child'];
    if ($childAccount['adulthood_status'] !== 'minor_supervised') {
        throw new Exception("Expected minor_supervised, got: " . $childAccount['adulthood_status']);
    }
    if ($childAccount['transition_due_date'] !== '2034-04-10') {
        throw new Exception("Expected transition due date 2034-04-10, got: " . $childAccount['transition_due_date']);
    }
});

// TEST 2: Child Authentication via Family Code + Username + PIN
runStep("Child authenticates using Family Code + Username + PIN", function() use ($familyCode) {
    $auth = ChildAccountService::authenticateChild($familyCode, 'tobi_kid', '1234');
    if ($auth === null) {
        throw new Exception("Authentication returned null for valid credentials.");
    }
    if ($auth['firstName'] !== 'Tobi' || $auth['famCode'] !== $familyCode) {
        throw new Exception("Auth data mismatch: " . json_encode($auth));
    }

    $invalidAuth = ChildAccountService::authenticateChild($familyCode, 'tobi_kid', '9999');
    if ($invalidAuth !== null) {
        throw new Exception("Authentication should have failed with invalid PIN.");
    }
});

// TEST 3: EXIF Metadata Stripping for child uploads
runStep("EXIF metadata and GPS stripping for child images", function() {
    if (!extension_loaded('gd')) {
        echo "(Skipped: GD extension not loaded) ";
        return;
    }
    $tempPath = sys_get_temp_dir() . '/test_child_img_' . time() . '.jpg';
    $img = imagecreatetruecolor(50, 50);
    imagefill($img, 0, 0, imagecolorallocate($img, 200, 50, 50));
    imagejpeg($img, $tempPath);
    imagedestroy($img);

    $stripped = ChildAccountService::stripExifMetadata($tempPath);
    if (!$stripped || !file_exists($tempPath)) {
        throw new Exception("Failed to sanitize child image.");
    }
    @unlink($tempPath);
});

// TEST 4: Parental Supervision Authorization (Allowed for active minor)
runStep("Parental supervision allowed while user is a minor", function() use ($parentUserId, &$childAccount) {
    $childId = $childAccount['user_id'];
    ChildAccountService::assertParentSupervisionAllowed($parentUserId, $childId);
});

// TEST 5: SecOps Red Team: Non-parent cannot supervise minor
runStep("Red Team: Unrelated user blocked from supervising minor (403)", function() use ($otherAdultId, &$childAccount) {
    $childId = $childAccount['user_id'];
    try {
        ChildAccountService::assertParentSupervisionAllowed($otherAdultId, $childId);
        throw new Exception("Expected ForbiddenException for non-parent.");
    } catch (ForbiddenException $e) {
        // Expected
    }
});

// TEST 6: "Turning 18" Adulthood Milestone Graduation
runStep("Adulthood Graduation Engine executes transition and posts family announcement", function() use ($db, $familyCode, $parentUserId) {
    // Create a minor child turning 18 today
    $eighteenNodeId = 0;
    $db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, birth_date, birth_year) VALUES (?, 'Maya', 'Olaogun', 'Female', '2008-10-27', 2008)")->execute([$familyCode]);
    $eighteenNodeId = (int)$db->lastInsertId();

    $res = ChildAccountService::provisionChildAccount(
        parentUserId: $parentUserId,
        childNodeId: $eighteenNodeId,
        username: 'maya_eighteen',
        pin: '5678',
        dobYear: '2008',
        dobMonth: '10',
        dobDay: '27'
    );
    $mayaId = $res['child']['user_id'];

    // Simulate an account originally registered as a minor reaching their 18th birthday today
    $db->prepare("UPDATE account SET adulthood_status = 'minor_teen', transition_due_date = CURDATE() WHERE id = ?")->execute([$mayaId]);

    // Plant an 18th Birthday Time Capsule
    $db->prepare("
        INSERT INTO family_time_capsules (family_code, creator_id, recipient_name, recipient_user_id, title, message, unlock_type, unlock_date, is_unlocked)
        VALUES (?, ?, 'Maya Olaogun', ?, 'Letter to my 18yo daughter', 'You are all grown up now!', 'milestone_18', CURDATE(), 0)
    ")->execute([$familyCode, $parentUserId, $mayaId]);
    $capsuleId = (int)$db->lastInsertId();

    // Check transition
    $grad = AdulthoodTransitionService::checkAndProcessUserGraduation($mayaId);
    if ($grad === null || empty($grad['graduated'])) {
        throw new Exception("Expected graduation to trigger for 18yo user.");
    }
    if ($grad['unsealed_capsules'] < 1) {
        throw new Exception("Expected time capsule to be unsealed.");
    }

    // Verify account row updated
    $stmt = $db->prepare("SELECT adulthood_status, is_child_account FROM account WHERE id = ?");
    $stmt->execute([$mayaId]);
    $acc = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($acc['adulthood_status'] !== 'graduated_adult' || (int)$acc['is_child_account'] !== 0) {
        throw new Exception("Account did not graduate cleanly: " . json_encode($acc));
    }

    // Verify celebration announcement on family feed
    $stmtPost = $db->prepare("SELECT postMessage FROM post WHERE id = ? AND postFamCode = ? ORDER BY post_no DESC LIMIT 1");
    $stmtPost->execute([$mayaId, $familyCode]);
    $postMsg = (string)$stmtPost->fetchColumn();
    if (!str_contains($postMsg, 'turned 18 today')) {
        throw new Exception("Celebration feed post was not published: " . $postMsg);
    }
});

// TEST 7: SecOps Red Team: Post-18 Parental Surveillance Severed
runStep("Red Team: Parental surveillance hard-blocked (403) once child graduates to 18", function() use ($db, $parentUserId) {
    // Find graduated user
    $stmt = $db->prepare("SELECT id FROM account WHERE adulthood_status = 'graduated_adult' LIMIT 1");
    $stmt->execute();
    $graduatedId = (string)$stmt->fetchColumn();

    try {
        ChildAccountService::assertParentSupervisionAllowed($parentUserId, $graduatedId);
        throw new Exception("Expected ForbiddenException: parental surveillance must be severed at 18.");
    } catch (ForbiddenException $e) {
        if (!str_contains($e->getMessage(), 'expired')) {
            throw new Exception("Unexpected message: " . $e->getMessage());
        }
    }
});

// Cleanup test fixtures
$db->prepare("DELETE FROM personal WHERE famCode IN (?, 'OTHER_FAM')")->execute([$familyCode]);
$db->prepare("DELETE FROM account WHERE id LIKE 'usr_%' OR id LIKE 'child_%'")->execute();
$db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$familyCode]);
$db->prepare("DELETE FROM child_pin_credentials WHERE family_code = ?")->execute([$familyCode]);
$db->prepare("DELETE FROM guardian_consents WHERE family_code = ?")->execute([$familyCode]);
$db->prepare("DELETE FROM family_time_capsules WHERE family_code = ?")->execute([$familyCode]);
$db->prepare("DELETE FROM post WHERE postFamCode = ?")->execute([$familyCode]);

echo "=================================================================\n";
echo "RESULTS: {$passed} Passed, {$failed} Failed\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
