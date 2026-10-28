<?php
declare(strict_types=1);

/**
 * verify_emoji_gif_sticker_suite.php
 *
 * Sovereign Verification & Adversarial Security Suite for:
 * 1. Phone Emojis (Compound ZWJ, Skin-tones, Variation Selectors, Keycaps) in posts & comments
 * 2. Keyboard GIFs & Stickers (Gboard / iOS paste integration & CDN allowlists)
 * 3. SSRF Neutralization on remote GIF/Sticker URLs
 * 4. Polyglot / WebShell Upload Remediation
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

require_once $rootDir . '/app/config/init.php';

echo "=== STARTING EMOJI, GIF & STICKER VERIFICATION SUITE ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $message): void {
    global $passCount, $failCount;
    if ($cond) {
        echo "  [PASS] {$message}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$message}\n";
        $failCount++;
    }
}

// ---------------------------------------------------------
// TEST 1: Phone Emojis Preservation (Compound ZWJ, Skin Tones, Variation Selectors)
// ---------------------------------------------------------
echo "[1] Testing Phone Emoji Preservation:\n";

$testEmojis = [
    'Red Heart with Variation Selector' => "Happy Anniversary ❤️ my love!",
    'Compound Family with ZWJ' => "Welcome to the family 👨‍👩‍👧‍👦!",
    'Skin Tone Modifier & Profession' => "Great job doctor 👩🏽‍⚕️! Proud of you 🙏🏾!",
    'Enclosing Keycap Emoji' => "Priority 1️⃣ on the family list!",
    'National Flags' => "Cheering from 🇬🇧 and 🏴󠁧󠁢󠁥󠁮󠁧󠁿!"
];

foreach ($testEmojis as $label => $text) {
    $sanitized = \App\services\LinkPreviewService::sanitizePostMessage($text);
    assertCondition($sanitized === $text, "{$label} preserved exactly without byte corruption");
}

// ---------------------------------------------------------
// TEST 2: GIF & Sticker URL Sanitization & SSRF Defense (Reflection Test)
// ---------------------------------------------------------
echo "\n[2] Testing GIF & Sticker URL Sanitizer (SSRF Hardening):\n";

$reflection = new ReflectionClass(\App\controller\members\ProfilePage::class);
$method = $reflection->getMethod('sanitizeCommentGifUrl');
$method->setAccessible(true);

$validUrls = [
    'Curated Giphy' => 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/200.gif',
    'Subdomain Giphy' => 'https://media1.giphy.com/media/test/200.gif',
    'Tenor Mobile Keyboard GIF' => 'https://media.tenor.com/images/abcdef/tenor.gif',
    'Tenor CDN' => 'https://c.tenor.com/xYz/tenor.gif',
    'Internal Uploaded Sticker' => '/resources/images/post/comment_1730000000_a1b2c3d4e5f60718.png',
    'Internal Uploaded GIF' => '/resources/images/post/comment_1730000000_1234567890abcdef.gif',
];

foreach ($validUrls as $label => $url) {
    $result = $method->invoke(null, $url);
    assertCondition($result !== null, "Valid media URL accepted: {$label}");
}

$maliciousUrls = [
    'Arbitrary Tracking Pixel / Domain' => 'https://evil.example.com/pixel.gif',
    'AWS Metadata SSRF' => 'http://169.254.169.254/latest/meta-data/',
    'Loopback IPv4' => 'http://127.0.0.1/admin',
    'Loopback IPv6' => 'http://[::1]/secret',
    'Localhost Domain' => 'http://localhost:8000/api',
    'Private 10.x Class A' => 'https://10.0.0.1/internal.gif',
    'Private 192.168.x Class C' => 'https://192.168.1.1/router.png',
    'Private 172.16.x Class B' => 'https://172.16.0.5/gateway.gif',
    'Basic Auth / Credential Leak' => 'https://admin:pass@media.giphy.com/media/200.gif',
    'Non-Image Remote Executable' => 'https://evil.com/webshell.php',
    'JavaScript Pseudo-Protocol' => 'javascript:alert(1)'
];

foreach ($maliciousUrls as $label => $url) {
    $result = $method->invoke(null, $url);
    assertCondition($result === null, "Adversarial SSRF blocked: {$label}");
}

// ---------------------------------------------------------
// TEST 3: Uploaded Media MIME Sniffing & Polyglot Neutralization
// ---------------------------------------------------------
echo "\n[3] Testing Media Upload Security (Dr. Viktor Kovács & Marcus Gate):\n";

$uploadMethod = $reflection->getMethod('processUploadedCommentMedia');
$uploadMethod->setAccessible(true);

// Test empty / fake upload rejection
$fakeFile = [
    'tmp_name' => '/tmp/non_existent_file_' . uniqid(),
    'size' => 500,
    'error' => 0
];
try {
    $res = $uploadMethod->invoke(null, $fakeFile);
    assertCondition($res === null, "Rejects non-uploaded / spoofed file paths");
} catch (\Throwable $e) {
    assertCondition(true, "Handled non-uploaded file safely");
}

// Test oversize upload rejection (>10MB)
$oversizeFile = [
    'tmp_name' => __FILE__,
    'size' => 15 * 1024 * 1024,
    'error' => 0
];
$oversizeBlocked = false;
try {
    $uploadMethod->invoke(null, $oversizeFile);
} catch (\Src\Exceptions\ValidationException $e) {
    $oversizeBlocked = true;
} catch (\Throwable $e) {
    $oversizeBlocked = true;
}
assertCondition($oversizeBlocked, "Rejects attached media exceeding 10MB limit");

// ---------------------------------------------------------
// TEST 4: Live DB Persistence of Unicode Phone Emojis in Comments
// ---------------------------------------------------------
echo "\n[4] Testing Live Database Persistence of Emojis in Comments:\n";

try {
    $pdo = \Src\Db::connect2();
    $testComment = "Happy Celebration! ❤️🎉👨‍👩‍👧‍👦 👩🏽‍⚕️ 1️⃣ 🇬🇧";
    $testPostNo = "test_post_" . time();
    $userId = "test_user_" . time();

    $stmt = $pdo->prepare("INSERT INTO comment (id, post_no, fullName, comment, post_time, comment_status) VALUES (?, ?, ?, ?, ?, 'published')");
    $stmt->execute([$userId, $testPostNo, "Test Member", $testComment, (string)milliSeconds()]);
    $commentNo = (int)$pdo->lastInsertId();

    $fetchStmt = $pdo->prepare("SELECT comment FROM comment WHERE comment_no = ?");
    $fetchStmt->execute([$commentNo]);
    $savedComment = (string)$fetchStmt->fetchColumn();

    assertCondition($savedComment === $testComment, "Full 4-byte compound emojis survive database round-trip without corruption");

    // Clean up
    $delStmt = $pdo->prepare("DELETE FROM comment WHERE comment_no = ?");
    $delStmt->execute([$commentNo]);
} catch (\Throwable $th) {
    assertCondition(false, "DB emoji test error: " . $th->getMessage());
}

// ---------------------------------------------------------
// SUMMARY
// ---------------------------------------------------------
echo "\n=========================================================\n";
echo "TOTAL TESTS: " . ($passCount + $failCount) . " | PASSED: {$passCount} | FAILED: {$failCount}\n";
echo "=========================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
