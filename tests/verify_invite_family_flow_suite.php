<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\controller\InviteController;
use Src\Db;

echo "=================================================================\n";
echo "🌳 VERIFY INVITE FAMILY & OPENGRAPH RESOLUTION SUITE\n";
echo "=================================================================\n\n";

$pdo = Db::connect2();
$userId = '937619LAFANE';

// Simulate authenticated session for user 937619LAFANE
$_SESSION['id'] = $userId;
$_SESSION['famCode'] = 'MODERNMAN';
$_SESSION['token'] = 'test_token_123';
$_POST['token'] = 'test_token_123';

// 1. Verify Surname Resolution for AKI980
echo "[TEST 1] Resolving family surname for Birth Code #AKI980...\n";
$surnameAki980 = \App\controller\register\Register::getFamilySurnameByCode('AKI980');
if ($surnameAki980 === 'AKINDURO') {
    echo "  ✅ PASSED: #AKI980 resolved correctly to 'AKINDURO'.\n";
} else {
    echo "  ❌ FAILED: Expected 'AKINDURO', got " . var_export($surnameAki980, true) . "\n";
    exit(1);
}

// 2. Verify Surname Resolution for Marital Code #MODERNMAN
echo "[TEST 2] Resolving family surname for Marital Code #MODERNMAN...\n";
$surnameModernman = \App\controller\register\Register::getFamilySurnameByCode('MODERNMAN');
if (!empty($surnameModernman)) {
    echo "  ✅ PASSED: #MODERNMAN resolved to '{$surnameModernman}'.\n";
} else {
    echo "  ❌ FAILED: #MODERNMAN resolution returned empty.\n";
    exit(1);
}

// 3. Test InviteController generation without email for #AKI980
echo "[TEST 3] Generating WhatsApp invite for #AKI980 (no email)...\n";
$_POST = [
    'token' => 'test_token_123',
    'first_name' => 'Femi',
    'last_name' => 'Akinduro',
    'family_code' => 'AKI980',
    'lineage_scope' => 'maternal',
    'relationship' => 'Sibling',
    'type' => 'organogram'
];

ob_start();
$controller = new InviteController();
$controller->generate();
$outputRaw = ob_get_clean();
$output = json_decode((string)$outputRaw, true);
$payload = $output['message'] ?? $output;

if (!is_array($output) || ($output['status'] ?? '') !== 'success') {
    echo "  ❌ FAILED: Invalid controller response: " . $outputRaw . "\n";
    exit(1);
}

if (!empty($payload['invite_url']) && !empty($payload['whatsapp_share_url']) && empty($payload['email_sent'])) {
    echo "  ✅ PASSED: Invite URL and WhatsApp link generated successfully without email.\n";
    echo "     URL: " . $payload['invite_url'] . "\n";
    echo "     Tree Label: " . ($payload['tree_label'] ?? '') . "\n";
} else {
    echo "  ❌ FAILED: Incomplete payload: " . print_r($output, true) . "\n";
    exit(1);
}

// 4. Test InviteController generation with email for #AKI980
echo "[TEST 4] Generating invite for #AKI980 with email supplied...\n";
$_POST = [
    'token' => 'test_token_123',
    'first_name' => 'Dupe',
    'last_name' => 'Akinduro',
    'email' => 'dupe.akinduro@example.com',
    'family_code' => 'AKI980',
    'lineage_scope' => 'maternal',
    'relationship' => 'Parent',
    'type' => 'organogram'
];

ob_start();
$controller->generate();
$outputRaw2 = ob_get_clean();
$output2 = json_decode((string)$outputRaw2, true);
$payload2 = $output2['message'] ?? $output2;

if (!is_array($output2) || ($output2['status'] ?? '') !== 'success') {
    echo "  ❌ FAILED: Invalid controller response with email: " . $outputRaw2 . "\n";
    exit(1);
}

if (!empty($payload2['email_sent']) && ($payload2['email_recipient'] ?? '') === 'dupe.akinduro@example.com') {
    echo "  ✅ PASSED: Email dispatched and confirmed for dupe.akinduro@example.com.\n";
} else {
    echo "  ❌ FAILED: Email dispatch was not recorded as true: " . print_r($output2, true) . "\n";
    exit(1);
}

// 5. Test Live Registration Route OpenGraph output
echo "[TEST 5] Testing Live Registration Route OpenGraph Meta Tags for #AKI980...\n";
$html = (string)file_get_contents('https://olaogun.test/register?famCode=AKI980&name=ode%20lop', false, stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]));

if (str_contains($html, 'content="Join the AKINDURO Family on FamilyPlatform"') &&
    str_contains($html, 'content="You\'ve been invited to connect with the AKINDURO family on FamilyPlatform.')) {
    echo "  ✅ PASSED: OpenGraph meta tags render 'Join the AKINDURO Family' and match invitation copy.\n";
} else {
    echo "  ❌ FAILED: OpenGraph tags do not match expected AKINDURO copy.\n";
    exit(1);
}

echo "\n=================================================================\n";
echo "🎉 ALL 5 INVITE FAMILY TESTS PASSED (100% SUCCESS)!\n";
echo "=================================================================\n";
