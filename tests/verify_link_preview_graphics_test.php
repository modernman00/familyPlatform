<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Regression & Adversarial Verification Suite:
 * Full Graphics Link Sharing, Truncation Immunity, and SSRF Hardening
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\services\LinkPreviewService;
use Src\Db;

$passed = 0;
$failed = 0;

function runTest(string $name, callable $fn): void {
    global $passed, $failed;
    echo "[TEST " . ($passed + $failed + 1) . "] {$name}... ";
    try {
        $fn();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "🏆 LINK PREVIEW & GRAPHICS SHARING VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// ------------------------------------------------------------------------------
// TEST 1: Schema Integrity - postMessage is utf8mb4 TEXT
// ------------------------------------------------------------------------------
runTest("Database Schema: post.postMessage is TEXT and accepts 4-byte emojis and 300+ characters", function() {
    $pdo = Db::connect2();
    LinkPreviewService::ensureSchema($pdo);

    $stmt = $pdo->query("SHOW COLUMNS FROM post LIKE 'postMessage'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$col || !in_array(strtolower((string)$col['Type']), ['text', 'mediumtext', 'longtext'], true)) {
        throw new RuntimeException("post.postMessage column is not TEXT: " . var_export($col, true));
    }

    // Verify link_preview_cache table exists
    $stmt2 = $pdo->query("SHOW TABLES LIKE 'link_preview_cache'");
    if (!$stmt2->fetch()) {
        throw new RuntimeException("link_preview_cache table does not exist!");
    }
});

// ------------------------------------------------------------------------------
// TEST 2: Multi-line Sanitisation & Line-Break Preservation
// ------------------------------------------------------------------------------
runTest("Sanitisation: Preserves newlines, Unicode emojis, and URL query characters", function() {
    $raw = "🔥 Pull up to Afrobeats and Amapiano Halloween night! 🎉\n🗓️ Fri, Oct 30, 2026 · 11:00 PM\n📍 Toast, Falmouth\n👇https://mypartyplatform.com/e/afrobeats-and-amapiano-halloween-night?ref=family&tag=vip+pass";
    $cleaned = LinkPreviewService::sanitizePostMessage($raw);

    if (!str_contains($cleaned, "\n🗓️")) {
        throw new RuntimeException("Line breaks were stripped from caption!");
    }
    if (!str_contains($cleaned, "?ref=family&tag=vip+pass")) {
        throw new RuntimeException("URL query string was corrupted or stripped!");
    }
    if (!str_contains($cleaned, "https://mypartyplatform.com/e/afrobeats-and-amapiano-halloween-night")) {
        throw new RuntimeException("URL was not separated or preserved!");
    }
});

// ------------------------------------------------------------------------------
// TEST 3: Truncated PartyPlatform Event URL Auto-Recovery
// ------------------------------------------------------------------------------
runTest("Truncated URL Auto-Recovery: Resolves truncated /e/a to canonical event URL", function() {
    $caption = "🔥 Pull up to Afrobeats and Amapiano Halloween night! 🎉\n🗓️ Fri, Oct 30, 2026 · 11:00 PM 📍 Toast, 18 Church Street, Falmouth, TR11 3DR, United Kingdom👑 Hosted by DJ Banks · 🎟️ Free RSVP (RSVP Open)Claim your instant QR pass here 👇https://mypartyplatform.com/e/a";
    
    $extracted = LinkPreviewService::extractFirstPreviewableUrl($caption);
    if ($extracted !== 'https://mypartyplatform.com/e/a') {
        throw new RuntimeException("Failed to extract URL, got: " . var_export($extracted, true));
    }

    $recovered = LinkPreviewService::recoverTruncatedPartyPlatformUrl($extracted, $caption);
    if ($recovered !== 'https://mypartyplatform.com/e/afrobeats-and-amapiano-halloween-night') {
        throw new RuntimeException("Failed to recover truncated PartyPlatform URL: " . $recovered);
    }
});

// ------------------------------------------------------------------------------
// TEST 4: Adversarial SSRF Penetration Attack Gate (Dr. Viktor Kovács & Marcus)
// ------------------------------------------------------------------------------
runTest("Adversarial Security: Neutralises SSRF, Loopback, Private CIDR, and Cloud Metadata", function() {
    $forbidden = [
        'http://127.0.0.1/admin',
        'http://127.0.0.1:8080/metrics',
        'http://localhost/secret',
        'http://169.254.169.254/latest/meta-data/',
        'http://10.0.0.1/internal',
        'http://192.168.1.1/router',
        'file:///etc/passwd',
        'gopher://127.0.0.1:6379/_flushall',
        'ftp://example.com/file.txt',
        'javascript:alert(1)',
    ];

    foreach ($forbidden as $badUrl) {
        if (LinkPreviewService::isSafeUrl($badUrl)) {
            throw new RuntimeException("CRITICAL SSRF VULNERABILITY: isSafeUrl allowed: " . $badUrl);
        }
        $result = LinkPreviewService::fetchPreview($badUrl);
        if ($result !== null) {
            throw new RuntimeException("CRITICAL SSRF VULNERABILITY: fetchPreview did not reject: " . $badUrl);
        }
    }
});

// ------------------------------------------------------------------------------
// TEST 5: OpenGraph Unfurling & 24h Cache Verification
// ------------------------------------------------------------------------------
runTest("OpenGraph Fetch & Cache: Resolves 16:9 social card graphics and caches payload", function() {
    $url = 'https://mypartyplatform.com/e/afrobeats-and-amapiano-halloween-night';
    $preview = LinkPreviewService::fetchPreview($url);

    if ($preview === null) {
        throw new RuntimeException("Expected preview for PartyPlatform event, got null");
    }

    if (empty($preview['image']) || !str_contains($preview['image'], '/api/og/event/CYRGVPP')) {
        throw new RuntimeException("Expected 16:9 graphic image /api/og/event/CYRGVPP, got: " . ($preview['image'] ?? 'none'));
    }

    if (empty($preview['title']) || !str_contains($preview['title'], 'Afrobeats and Amapiano')) {
        throw new RuntimeException("Expected event title, got: " . ($preview['title'] ?? 'none'));
    }

    if (empty($preview['cta_label']) || $preview['cta_label'] !== 'View Event / RSVP →') {
        throw new RuntimeException("Expected PartyPlatform CTA label, got: " . ($preview['cta_label'] ?? 'none'));
    }

    // Verify cache hit in MySQL
    $pdo = Db::connect2();
    $hash = hash('sha256', $url);
    $stmt = $pdo->prepare("SELECT * FROM link_preview_cache WHERE url_hash = ?");
    $stmt->execute([$hash]);
    $cachedRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cachedRow) {
        throw new RuntimeException("Database cache row was not found for hash: " . $hash);
    }
});

// ------------------------------------------------------------------------------
// TEST 6: Feed Hydration & PostDto Contract
// ------------------------------------------------------------------------------
runTest("Feed Hydration: PostDto serialises link_preview and delivers full graphics card payload", function() {
    $row = [
        'post_no' => 999999,
        'id' => 'test-user-123',
        'fullName' => 'Test Organizer',
        'postMessage' => "Come to the party! https://mypartyplatform.com/e/afrobeats-and-amapiano-halloween-night",
        'postFamCode' => 'FAM999',
        'date_created' => date('Y-m-d H:i:s'),
        'post_time' => (string)time(),
        'post_status' => 'published',
        'post_likes' => 5,
    ];

    $dto = \App\dto\PostDto::fromDatabaseRow($row, 'test-user-123');
    $arr = $dto->toArray();

    if (!isset($arr['link_preview']) || !is_array($arr['link_preview'])) {
        throw new RuntimeException("PostDto did not serialize link_preview field");
    }

    if (empty($arr['link_preview']['image'])) {
        throw new RuntimeException("link_preview in PostDto is missing graphic image");
    }
});

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🎉 ALL {$passed} TESTS PASSED WITH 0 REGRESSIONS!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failed} TESTS FAILED out of " . ($passed + $failed) . "!\n";
    echo "=================================================================\n";
    exit(1);
}
