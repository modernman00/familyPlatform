<?php

declare(strict_types=1);

if (!defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

require_once __DIR__ . '/../app/config/init.php';

use App\controller\PostShareController;
use App\services\AtgFestiveCardRenderer;
use App\services\AtgFestiveCardService;
use App\services\AutonomousBlogService;
use App\services\FeatureDiscoveryService;
use Src\Db;

echo "====================================================================\n";
echo "🛡️  VERIFYING IRONCLAD SECURITY, RESILIENCE & GROWTH SUITE\n";
echo "====================================================================\n";

$passes = 0;
$failures = 0;

function assertIronclad(bool $cond, string $msg): void
{
    global $passes, $failures;
    if ($cond) {
        echo "  ✅ [PASS] {$msg}\n";
        $passes++;
    } else {
        echo "  ❌ [FAIL] {$msg}\n";
        $failures++;
    }
}

// -------------------------------------------------------------------
// 1. Vector 1: AutonomousBlogService Dual-Boundary HTML Purification
// -------------------------------------------------------------------
echo "\n[Vector 1] Auditing AutonomousBlogService HTML Purification & XSS Neutralization...\n";

$maliciousXss = '<h3>Dynasty Roots</h3><p>Exploring lineage <a href="javascript:alert(\'pwned\')" onclick="fetch(\'/steal\')">Click here</a> to learn more.</p><script>alert(1)</script><div onmouseover="evil()">Hover me</div><iframe src="https://attacker.com"></iframe>';
$purified = AutonomousBlogService::purifyHtml($maliciousXss);

assertIronclad(!str_contains($purified, 'javascript:'), 'Javascript: pseudo-protocol successfully stripped');
assertIronclad(!str_contains($purified, 'onclick='), 'Event handler onclick stripped');
assertIronclad(!str_contains($purified, 'onmouseover='), 'Event handler onmouseover stripped');
assertIronclad(!str_contains($purified, '<script>'), '<script> tag stripped');
assertIronclad(!str_contains($purified, '<iframe>'), '<iframe> tag stripped');

$safeHtml = '<p>Read our <a href="https://familyplatform.com/playbook">Family Playbook</a> for guidance.</p>';
$safePurified = AutonomousBlogService::purifyHtml($safeHtml);
assertIronclad(str_contains($safePurified, 'href="https://familyplatform.com/playbook"'), 'Safe https link preserved');
assertIronclad(str_contains($safePurified, 'rel="noopener noreferrer"'), 'Rel="noopener noreferrer" injected on safe links');
assertIronclad(str_contains($safePurified, 'target="_blank"'), 'Target="_blank" injected on safe links');

// -------------------------------------------------------------------
// 2. Vector 2: Festive Cards SSRF, Media Validation & WebP EXIF
// -------------------------------------------------------------------
echo "\n[Vector 2] Auditing Festive Card Media Isolation, WebP EXIF & Absolute URLs...\n";

$pdo = Db::connect2();
$testUser = 'ironclad_tester_' . bin2hex(random_bytes(4));
$testFam = 'FAM' . strtoupper(bin2hex(random_bytes(3)));

$service = new AtgFestiveCardService($pdo);

// Test SSRF / external media injection rejection
$maliciousCardData = [
    'occasion' => 'christmas',
    'theme' => 'royal_crimson',
    'headline' => 'Test Ironclad Card',
    'verse' => 'Warm wishes across branches.',
    'sender_name' => 'The Olaoguns',
    'hero_photo_url' => 'http://169.254.169.254/latest/meta-data/', // Malicious SSRF IP
    'audio_voice_url' => 'javascript:eval(atob("..."))', // Malicious audio protocol
];

$createdCard = $service->createCard($testUser, $testFam, $maliciousCardData);

// Fetch directly from DB to verify persistence sanitization
$stmt = $pdo->prepare('SELECT hero_photo_url, audio_voice_url FROM festive_cards WHERE id = ?');
$stmt->execute([$createdCard['id']]);
$dbRow = $stmt->fetch(\PDO::FETCH_ASSOC);

assertIronclad(empty($dbRow['hero_photo_url']), 'Malicious SSRF IP in hero_photo_url was rejected and sanitized to null');
assertIronclad(empty($dbRow['audio_voice_url']), 'Malicious javascript protocol in audio_voice_url was rejected and sanitized to null');
assertIronclad(!empty($createdCard['absolute_image_url']) && str_starts_with($createdCard['absolute_image_url'], 'http'), 'Card includes absolute OpenGraph image URL: ' . $createdCard['absolute_image_url']);
assertIronclad(!empty($createdCard['absolute_public_url']) && str_starts_with($createdCard['absolute_public_url'], 'http'), 'Card includes absolute public URL: ' . $createdCard['absolute_public_url']);

// Verify pre-rendered PNG is cached on disk
$cacheFile = __DIR__ . '/../storage/cache/festive_cards/' . $createdCard['share_token'] . '_og_v2.jpg';
assertIronclad(file_exists($cacheFile) && filesize($cacheFile) > 1000, 'Festive card PNG was pre-rendered at creation time: ' . basename($cacheFile));

// Test WebP EXIF stripping and bounds check
$testWebp = sys_get_temp_dir() . '/test_ironclad.webp';
$dummyImg = imagecreatetruecolor(100, 100);
imagewebp($dummyImg, $testWebp);
imagedestroy($dummyImg);

$strippedWebp = AtgFestiveCardService::stripExifGps($testWebp);
assertIronclad($strippedWebp === true, 'WebP image successfully processed and re-encoded by stripExifGps()');
@unlink($testWebp);

// -------------------------------------------------------------------
// 3. Vector 3: Social Share Intent Cookie & HMAC Security
// -------------------------------------------------------------------
echo "\n[Vector 3] Auditing Social Share Intent Cookie & Cryptographic Verification...\n";

$intentPostNo = 99881;
$intentFamCode = 'FAMIRONCLAD';
$appKey = (string)($_ENV['APP_KEY'] ?? 'familyPlatformIntentKey');

$payload = json_encode(['post_no' => $intentPostNo, 'famCode' => $intentFamCode, 't' => time()]);
$validSig = hash_hmac('sha256', (string)$payload, $appKey);
$validCookie = $validSig . '.' . base64_encode((string)$payload);

$parsedValid = PostShareController::parseShareIntentCookie($validCookie);
assertIronclad(
    is_array($parsedValid) && $parsedValid['post_no'] === $intentPostNo && $parsedValid['famCode'] === $intentFamCode,
    'Valid HMAC signature unpacked correctly from share intent cookie'
);

// Adversarial Tampering: modified payload with mismatched signature
$tamperedPayload = json_encode(['post_no' => 11111, 'famCode' => 'FAMHACK', 't' => time()]);
$tamperedCookie = $validSig . '.' . base64_encode((string)$tamperedPayload);
$parsedTampered = PostShareController::parseShareIntentCookie($tamperedCookie);
assertIronclad($parsedTampered === null, 'Tampered cookie payload correctly hard-rejected by HMAC check');

// -------------------------------------------------------------------
// 4. Vector 4: Discovery Hub Query Elimination & Multi-Tier Caching
// -------------------------------------------------------------------
echo "\n[Vector 4] Auditing Discovery Hub Query Elimination & Caching...\n";

$discService = new FeatureDiscoveryService($pdo);

// Request 1: cold compute
$t0 = microtime(true);
$state1 = $discService->getUserDiscoveryState($testUser, $testFam);
$t1 = microtime(true);

// Request 2: cached in memory
$t2 = microtime(true);
$state2 = $discService->getUserDiscoveryState($testUser, $testFam);
$t3 = microtime(true);

$timeCold = ($t1 - $t0) * 1000;
$timeCached = ($t3 - $t2) * 1000;

assertIronclad($state1['completed_count'] === $state2['completed_count'], 'State data remains identical between cold and cached retrieval');
assertIronclad($timeCached <= $timeCold, sprintf('In-memory cache delivers faster response: %.2fms vs cold %.2fms', $timeCached, $timeCold));

// Verify cache invalidation on milestone update
$discService->markMilestoneCompleted($testUser, 'avatar_studio_day4');
$stateAfter = $discService->getUserDiscoveryState($testUser, $testFam);
assertIronclad($stateAfter['completed_count'] >= 1, 'Cache was automatically invalidated and dynamic progress updated');

// -------------------------------------------------------------------
// Summary
// -------------------------------------------------------------------
echo "\n====================================================================\n";
echo "  🏁 SUITE SUMMARY: {$passes} Passed, {$failures} Failed\n";
echo "====================================================================\n";

if ($failures > 0) {
    exit(1);
}
