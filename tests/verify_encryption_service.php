<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\services\EncryptionService;

echo "======================================================================\n";
echo "🔐 VERIFYING FAMILY VAULT ENCRYPTION SERVICE (AES-256-GCM + AAD)\n";
echo "======================================================================\n";

$familyCode = "FAM_TEST_8921";
$otherFamilyCode = "FAM_ATTACKER_9999";
$sampleText = "<h1>Grandma's Secret Jollof Rice Recipe</h1><p>Passed down since 1954. Secret spice: 2 pinches of nutmeg.</p>";

// Test 1: Basic Encrypt & Decrypt
echo "[Test 1] Testing Basic Encrypt & Decrypt...\n";
$ciphertext = EncryptionService::encrypt($sampleText, $familyCode, 'journal');

if (!EncryptionService::isEncrypted($ciphertext)) {
    echo "❌ FAIL: Output is not marked as encrypted.\n";
    exit(1);
}

if ($ciphertext === $sampleText) {
    echo "❌ FAIL: Ciphertext matches plaintext.\n";
    exit(1);
}

$decrypted = EncryptionService::decrypt($ciphertext, $familyCode, 'journal');
if ($decrypted !== $sampleText) {
    echo "❌ FAIL: Decrypted text does not match original plaintext.\n";
    echo "Expected: " . $sampleText . "\n";
    echo "Got: " . $decrypted . "\n";
    exit(1);
}
echo "✅ PASS: Encrypt and decrypt round-trip succeeded.\n\n";

// Test 2: Cross-Family Isolation Attack (Dr. Kovács & Marcus Gate)
echo "[Test 2] Testing Cross-Family Isolation Attack (Tampering AAD / Key Context)...\n";
$attackerDecrypted = EncryptionService::decrypt($ciphertext, $otherFamilyCode, 'journal');
if ($attackerDecrypted === $sampleText) {
    echo "❌ FAIL: Attacker family code was able to decrypt foreign family vault data!\n";
    exit(1);
}
echo "✅ PASS: Cross-family decryption rejected (Authentication Verification Failed).\n\n";

// Test 3: Ciphertext Tampering & Bit-Flipping Resistance
echo "[Test 3] Testing Ciphertext Tamper Resistance (Bit-flipping payload)...\n";
$tamperedPayload = substr($ciphertext, 0, -5) . 'XYZ12';
$tamperedResult = EncryptionService::decrypt($tamperedPayload, $familyCode, 'journal');
if ($tamperedResult === $sampleText) {
    echo "❌ FAIL: Tampered ciphertext was decrypted without error!\n";
    exit(1);
}
echo "✅ PASS: Tampered ciphertext safely rejected by GCM authentication tag.\n\n";

// Test 4: Backward Compatibility with Legacy Plaintext Records
echo "[Test 4] Testing Legacy Plaintext Pass-Through...\n";
$legacyText = "<p>This is a legacy chronicle written before vault encryption.</p>";
$legacyResult = EncryptionService::decrypt($legacyText, $familyCode, 'journal');
if ($legacyResult !== $legacyText) {
    echo "❌ FAIL: Legacy plaintext was altered during pass-through.\n";
    exit(1);
}
echo "✅ PASS: Legacy unencrypted text passes through 100% transparently.\n\n";

echo "🎉 ALL ENCRYPTION SERVICE TESTS PASSED!\n";
exit(0);
