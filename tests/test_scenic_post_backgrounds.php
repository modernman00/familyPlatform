<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/init.php';

use App\dto\PostDto;
use Src\Db;

echo "=== STARTING SCENIC POST BACKGROUNDS & ADVERSARIAL SECURITY SUITE ===\n";

$scenicStyles = [
    'starlight_bokeh',
    'golden_ember',
    'sunset_horizon',
    'emerald_canopy',
    'ocean_biolum',
    'rose_stardust',
    'northern_sky',
    'royal_parchment',
];

// 1. Verify allowlist acceptance & round-trip token embedding/stripping
$quote = 'Stay silent even when triggered. You will win 99% of your battles through silence and observation.';
foreach ($scenicStyles as $slug) {
    assert(PostDto::cleanPostStyle($slug) === $slug, "Expected {$slug} to be accepted by PostDto::cleanPostStyle");
    assert(PostDto::cleanPostStyle('  ' . strtoupper($slug) . '  ') === $slug, "Expected case-insensitive trim normalization for {$slug}");

    $embedded = PostDto::embedStyleToken($quote, $slug);
    assert(str_contains($embedded, "[style:{$slug}]"), "Expected embedded token for {$slug}");
    assert(PostDto::extractStyleFromMessage($embedded) === $slug, "Expected extractStyleFromMessage to recover {$slug}");
    assert(PostDto::stripStyleToken($embedded) === $quote, "Expected stripStyleToken to return clean quote");
}
echo "✓ All 8 scenic backdrop slugs round-trip cleanly through PostDto\n";

// 2. Adversarial XSS / Class-Injection / SQLi Payload Neutralisation
$maliciousPayloads = [
    'starlight_bokeh" onclick="alert(1)',
    '<script>alert(1)</script>',
    'starlight_bokeh; background:url(javascript:alert(1))',
    "' OR 1=1 --",
    '../../../etc/passwd',
    'unknown_custom_theme',
    '',
    'none',
    'plain',
    ['array_injection'],
];

foreach ($maliciousPayloads as $payload) {
    assert(PostDto::cleanPostStyle($payload) === null, 'Adversarial post_style payload must resolve to null');
}

$injectedMsg = PostDto::embedStyleToken($quote, 'starlight_bokeh" onmouseover="alert(1)');
assert($injectedMsg === $quote, 'Malicious style slug must never be stamped into postMessage');
echo "✓ Adversarial XSS, CSS-injection, and type-juggling payloads neutralised\n";

// 3. Database Persistence & PostDto::fromRow Hydration Parity (Parameterised PDO)
$db = Db::connect2();
$testUserId = 'test_scenic_author_' . time();
$testFamCode = 'FAM_SCENIC_' . substr(md5((string)time()), 0, 6);
$storedMessage = PostDto::embedStyleToken($quote, 'starlight_bokeh');

$stmtInsert = $db->prepare(
    'INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, date_created)
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
);
$stmtInsert->execute([
    $testUserId,
    'Wally Test',
    'avatarM.png',
    $storedMessage,
    $testFamCode,
    (string)time(),
    'published',
]);
$postNo = (int)$db->lastInsertId();
assert($postNo > 0, 'Expected inserted post_no > 0');

try {
    $stmtFetch = $db->prepare('SELECT * FROM post WHERE post_no = ?');
    $stmtFetch->execute([$postNo]);
    $row = $stmtFetch->fetch(PDO::FETCH_ASSOC);
    assert(is_array($row), 'Expected persisted post row');

    $dto = PostDto::fromDatabaseRow($row, $testUserId);
    $serialized = $dto->jsonSerialize();
    assert($serialized['post_style'] === 'starlight_bokeh', 'Expected hydrated DTO post_style === starlight_bokeh');
    echo "✓ Persisted post #{$postNo} hydrates post_style=starlight_bokeh cleanly via PostDto::fromDatabaseRow\n";
} finally {
    $stmtClean = $db->prepare('DELETE FROM post WHERE post_no = ?');
    $stmtClean->execute([$postNo]);
}

// 4. Verify whats_new.php schema parity
$whatsNew = require __DIR__ . '/../resources/data/whats_new.php';
assert(is_array($whatsNew) && ($whatsNew['version_key'] ?? '') === '2026_11_w6', 'Expected bumped whats_new version_key 2026_11_w6');
assert(count($whatsNew['items'] ?? []) === 4, 'Expected exact 4-item whats_new format');
echo "✓ whats_new.php 4-item schema & version_key parity verified\n";

echo "=== ALL SCENIC POST BACKGROUND TESTS PASSED ===\n";
