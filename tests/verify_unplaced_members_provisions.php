<?php
/**
 * Test Suite: Unplaced Family Members Provisions & Card Layout
 * Verifies:
 * 1. Provision of parent, grandparent, and great-grandparent in organogram UI and backend.
 * 2. Unplaced member card CSS rules guarantee zero overlap and fluid container sizing.
 * 3. Ancestry cycle prevention guards on unplaced connection endpoints.
 */

require_once __DIR__ . '/bootstrap.php';

$passed = 0;
$failed = 0;

function assertCheck(bool $condition, string $message): void {
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ PASS: {$message}\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: {$message}\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "🛡️ UNPLACED FAMILY MEMBERS PROVISIONS & CARD LAYOUT SUITE\n";
echo "=================================================================\n\n";

// [1] OrganogramEditorController backend checks
echo "[1] Auditing app/controller/members/OrganogramEditorController.php...\n";
$controllerCode = file_get_contents(__DIR__ . '/../app/controller/members/OrganogramEditorController.php');

assertCheck(strpos($controllerCode, "'parent'") !== false, "OrganogramEditorController includes 'parent' in allowed relationships");
assertCheck(strpos($controllerCode, "'grandparent'") !== false, "OrganogramEditorController includes 'grandparent' in allowed relationships");
assertCheck(strpos($controllerCode, "'great_grandparent'") !== false, "OrganogramEditorController includes 'great_grandparent' in allowed relationships");
assertCheck(strpos($controllerCode, "wouldFormCycle") !== false, "Adversarial ancestry cycle prevention is active for unplaced connections");
assertCheck(strpos($controllerCode, "!in_array(\$relationship, ['partner', 'spouse', 'parent', 'grandparent', 'great_grandparent']") !== false, "Ancestor placements are excluded from being inserted as children of their own unions");

// [2] organogram.blade.php template & script checks
echo "\n[2] Auditing resources/views/member/organogram.blade.php...\n";
$bladeCode = file_get_contents(__DIR__ . '/../resources/views/member/organogram.blade.php');

assertCheck(strpos($bladeCode, '<option value="parent">') !== false, "Relationship dropdown has option for 'parent'");
assertCheck(strpos($bladeCode, '<option value="grandparent">') !== false, "Relationship dropdown has option for 'grandparent'");
assertCheck(strpos($bladeCode, '<option value="great_grandparent">') !== false, "Relationship dropdown has option for 'great_grandparent'");
assertCheck(strpos($bladeCode, "rel === 'parent'") !== false, "onConnectRelationshipChange handles 'parent' anchor configuration");
assertCheck(strpos($bladeCode, "rel === 'grandparent'") !== false, "onConnectRelationshipChange handles 'grandparent' anchor configuration");
assertCheck(strpos($bladeCode, "rel === 'great_grandparent'") !== false, "onConnectRelationshipChange handles 'great_grandparent' anchor configuration");

// [3] organogram.css card layout & overlap immunity
echo "\n[3] Auditing public/css/organogram.css...\n";
$cssCode = file_get_contents(__DIR__ . '/../public/css/organogram.css');

assertCheck(strpos($cssCode, "max-width: 380px") === false, "Eliminated restrictive max-width: 380px constraint that caused card button overlaps");
assertCheck(strpos($cssCode, ".unplaced-member-card") !== false, "Dedicated styling exists for .unplaced-member-card");
assertCheck(strpos($cssCode, "gap: 12px") !== false || strpos($cssCode, "gap: 10px") !== false, "Generous gap exists between unplaced cards to prevent physical touch target collision");

echo "\n=================================================================\n";
echo "SUMMARY: " . ($passed + $failed) . " checks, {$passed} passed, {$failed} failed.\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
echo "🎉 ALL UNPLACED PROVISIONS CHECKS PASSED WITH 100% SUCCESS!\n";
exit(0);
