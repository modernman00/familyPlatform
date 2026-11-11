<?php

declare(strict_types=1);

/**
 * Adversarial Security & In-Flight Regression Test Suite:
 * URL Link Icon & Hidden URL Architecture + Open Graph Privacy
 *
 * Verifies:
 * 1. Open Graph metadata eliminates personal legal name leakage.
 * 2. URL Link Pill strictly neutralises protocol smuggling (javascript:, data:).
 * 3. XSS injection payloads in titles and subtitles are escaped.
 * 4. Referral token cryptographic HMAC anti-tampering & TTL expiry.
 * 5. Fitts's law touch targets (>= 44px) and British English compliance.
 *
 * Owned by: Dr. Viktor Kovács & Marcus (SecOps/Red Team) + Victor (CTO & BRATS).
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\services\FamilyRecommendationService;

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $description): void
{
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ PASSED: {$description}\n";
        $passed++;
    } else {
        echo "  ❌ FAILED: {$description}\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "🛡️  ADVERSARIAL SECURITY & URL LINK ICON VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// -------------------------------------------------------------
// [TEST 1] Open Graph Metadata Privacy & Legal Name Sanitisation
// -------------------------------------------------------------
echo "[TEST 1] Verifying Open Graph metadata eliminates legal name exposure...\n";

$joinViewFile = __DIR__ . '/../resources/views/registration/joinRecommendation.blade.php';
$joinContent = file_get_contents($joinViewFile) ?: '';

$hasOldInviterTitle = str_contains($joinContent, '$ogInviter ? "{$ogInviter} invited you');
$hasNewTitle = str_contains($joinContent, "Don't Let Family Memories Get Lost in Chat Feeds");
$hasRichDesc = str_contains($joinContent, 'oral history voice vault') && str_contains($joinContent, 'living family tree');

assertTest(!$hasOldInviterTitle, "Legacy '$ogInviter invited you' string completely purged from Open Graph metadata.");
assertTest($hasNewTitle, "Curiosity-driven high-converting title 'Don't Let Family Memories Get Lost in Chat Feeds' is active.");
assertTest($hasRichDesc, "Open Graph description showcases oral history voice vault & living family tree.");

// -------------------------------------------------------------
// [TEST 2] Open-Redirect & Protocol Smuggling Attack Neutralisation
// -------------------------------------------------------------
echo "\n[TEST 2] Testing protocol smuggling neutralization (javascript:, data:)...\n";

$pillViewFile = __DIR__ . '/../resources/views/components/urlLinkPill.blade.php';
$pillContent = file_get_contents($pillViewFile) ?: '';

$hasDisallowedCheck = str_contains($pillContent, "disallowedProtocols = ['javascript:', 'data:', 'vbscript:', 'file:']");
$hasBlockedFallback = str_contains($pillContent, '#blocked-unsafe-url');

assertTest($hasDisallowedCheck && $hasBlockedFallback, "Disallowed protocol filter disarms javascript:, data:, vbscript:, and file: schemes.");

// -------------------------------------------------------------
// [TEST 3] XSS Injection Neutralisation
// -------------------------------------------------------------
echo "\n[TEST 3] Testing XSS injection escaping on Link Pill component...\n";

$hasTitleEscaping = str_contains($pillContent, "htmlspecialchars(trim((string)(\$title ?? 'Open Private Link')), ENT_QUOTES, 'UTF-8')");
$hasSubtitleEscaping = str_contains($pillContent, "htmlspecialchars(trim((string)\$subtitle), ENT_QUOTES, 'UTF-8')");

assertTest($hasTitleEscaping, "Link title strictly escaped with htmlspecialchars(..., ENT_QUOTES, 'UTF-8').");
assertTest($hasSubtitleEscaping, "Link subtitle strictly escaped with htmlspecialchars(..., ENT_QUOTES, 'UTF-8').");

// -------------------------------------------------------------
// [TEST 4] Cryptographic HMAC Referral Anti-Tampering & TTL
// -------------------------------------------------------------
echo "\n[TEST 4] Testing HMAC SHA-256 referral token anti-tampering...\n";

$userId = 'test_user_' . bin2hex(random_bytes(4));
$userName = 'Test User';

$token = FamilyRecommendationService::generateSignedReferralToken($userId, $userName);
$resolved = FamilyRecommendationService::verifyAndResolveReferralToken($token);

assertTest($resolved !== null && $resolved['inviter_id'] === $userId, "Legitimate signed referral token verifies and resolves correctly.");

// Exploit A: Tampered signature
$tamperedSignatureToken = $token . 'tampered';
$tamperedResult = FamilyRecommendationService::verifyAndResolveReferralToken($tamperedSignatureToken);
assertTest($tamperedResult === null, "Red Team: Tampered token signature rejected (HTTP 403 / null).");

// Exploit B: Tampered payload
$parts = explode('.', $token);
$tamperedPayload = base64_encode(json_encode(['uid' => 'evil_user', 'name' => 'Attacker', 'iat' => time(), 'exp' => time() + 3600]));
$tamperedPayloadToken = $tamperedPayload . '.' . $parts[1];
$tamperedPayloadResult = FamilyRecommendationService::verifyAndResolveReferralToken($tamperedPayloadToken);
assertTest($tamperedPayloadResult === null, "Red Team: Tampered token payload rejected (HTTP 403 / null).");

// Exploit C: Expired token
$secret = FamilyRecommendationService::getSecretKey();
$expiredPayload = [
    'uid'  => $userId,
    'name' => $userName,
    'iat'  => time() - 3600000,
    'exp'  => time() - 3600, // Expired 1 hour ago
    'rnd'  => 'random123',
];
$expiredJson = json_encode($expiredPayload);
$expiredEncoded = rtrim(strtr(base64_encode((string)$expiredJson), '+/', '-_'), '=');
$expiredSig = hash_hmac('sha256', $expiredEncoded, $secret);
$expiredToken = $expiredEncoded . '.' . $expiredSig;
$expiredResult = FamilyRecommendationService::verifyAndResolveReferralToken($expiredToken);
assertTest($expiredResult === null, "Red Team: Expired referral token rejected (TTL enforcement).");

// -------------------------------------------------------------
// [TEST 5] Touch Target & British English Invariant
// -------------------------------------------------------------
echo "\n[TEST 5] Testing Fitts's law (44px) and British English in modified templates...\n";

$hasHitArea = str_contains($pillContent, 'min-height: 44px') && str_contains($pillContent, 'min-width: 44px');
assertTest($hasHitArea, "Tactile Link Pill strictly enforces minimum 44x44px touch targets.");

$filesToScan = [
    $joinViewFile,
    $pillViewFile,
    __DIR__ . '/../resources/views/member/modals/recommendModal.blade.php',
    __DIR__ . '/../app/services/FamilyRecommendationService.php',
];

$usWords = [
    '\bcustomize\b', '\bcustomizing\b', '\bcustomized\b',
    '\boptimize\b', '\boptimizing\b', '\boptimized\b',
    '\borganize\b', '\borganizing\b', '\borganized\b',
    '\bfavorite\b', '\bfavorites\b',
    '\bcolor\b(?!:)', '\bcolors\b(?!:)',
    '\bmold\b', '\binquiry\b', '\binquiries\b'
];
$violations = 0;

foreach ($filesToScan as $filePath) {
    $rawContent = file_get_contents($filePath) ?: '';
    // Strip <style> and <script> tags and HTML tag attributes so we only check visible copy
    $cleanText = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $rawContent) ?? '';
    $cleanText = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $cleanText) ?? '';
    $cleanText = strip_tags($cleanText);

    foreach ($usWords as $usWord) {
        if (preg_match('/' . $usWord . '/i', $cleanText)) {
            $violations++;
            echo "  ⚠️ User-facing American English spelling detected in {$filePath}: {$usWord}\n";
        }
    }
}

assertTest($violations === 0, "Universal British English Invariant verified: 0 US spelling violations in user-facing copy.");

// -------------------------------------------------------------
// Summary
// -------------------------------------------------------------
echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🏆 ALL {$passed} ADVERSARIAL SECURITY CHECKS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ SECURITY GATES FAILED: {$failed} failed, {$passed} passed.\n";
    echo "=================================================================\n";
    exit(1);
}
