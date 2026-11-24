<?php

declare(strict_types=1);

/**
 * Automated Regression & Adversarial Verification Suite:
 * Sovereign Instagram Reel/Video Extraction, Token-Expiry Shield & Stream Proxy Hardening
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\classes\VideoParser;
use App\dto\PostDto;
use App\services\LinkPreviewService;

$passed = 0;
$failed = 0;

function runIgTest(string $name, callable $fn): void {
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
echo "🎬 SOVEREIGN INSTAGRAM VIDEO & PREVIEW VERIFICATION SUITE\n";
echo "=================================================================\n\n";

runIgTest('User-Agent strips WhatsApp token strictly on Instagram domains', function (): void {
    $igUa = LinkPreviewService::crawlerUserAgentForUrl('https://www.instagram.com/reel/DUNGKnBCSdC/');
    $igShortUa = LinkPreviewService::crawlerUserAgentForUrl('https://instagr.am/p/CmUv48DLvxd/');
    $otherUa = LinkPreviewService::crawlerUserAgentForUrl('https://www.bbc.co.uk/news/uk-12345');

    if (str_contains($igUa, 'WhatsApp') || str_contains($igShortUa, 'WhatsApp')) {
        throw new RuntimeException('Instagram UA must NOT contain WhatsApp token.');
    }
    if (!str_contains($igUa, 'facebookexternalhit/1.1')) {
        throw new RuntimeException('Instagram UA must preserve facebookexternalhit/1.1.');
    }
    if (!str_contains($otherUa, 'WhatsApp')) {
        throw new RuntimeException('Non-Instagram UA must keep WhatsApp token.');
    }
});

runIgTest('Extracts validated Instagram shortcodes across /reel/, /reels/, /p/, /tv/, and /user/reel/', function (): void {
    $cases = [
        'https://www.instagram.com/reel/DUNGKnBCSdC/?igsh=abc' => 'DUNGKnBCSdC',
        'https://instagram.com/reels/DUNGKnBCSdC/' => 'DUNGKnBCSdC',
        'https://www.instagram.com/justinflom/reel/DUNGKnBCSdC/' => 'DUNGKnBCSdC',
        'https://www.instagram.com/p/CmUv48DLvxd/' => 'CmUv48DLvxd',
        'https://www.instagram.com/tv/C1234567890/' => 'C1234567890',
        'https://www.instagram.com/explore/' => null,
        'https://example.com/reel/DUNGKnBCSdC/' => null,
    ];
    foreach ($cases as $url => $expected) {
        $actual = LinkPreviewService::extractInstagramShortcode($url);
        if ($actual !== $expected) {
            throw new RuntimeException("Expected " . var_export($expected, true) . " for {$url}, got " . var_export($actual, true));
        }
    }
});

runIgTest('Parses PolarisEmbedSimple contextJSON for video_url, watermark-free poster, dimensions, and caption', function (): void {
    $contextPayload = json_encode([
        'gql_data' => [
            'shortcode_media' => [
                'is_video' => true,
                'video_url' => 'https://scontent-man2-1.cdninstagram.com/o1/v/t2/f2/m86/sample.mp4?oe=7FFFFFFF',
                'display_url' => 'https://scontent-man2-1.cdninstagram.com/v/t51.71878-15/cover_e15_tt6.jpg',
                'dimensions' => ['width' => 720, 'height' => 1280],
                'owner' => ['username' => 'djbanks', 'full_name' => 'DJ Banks'],
                'edge_media_to_caption' => [
                    'edges' => [
                        ['node' => ['text' => 'Afrobeats & Amapiano live set in Falmouth!']],
                    ],
                ],
            ],
        ],
    ]);
    $html = '<script>require("PolarisEmbedSimple").init([],[{"contextJSON":' . json_encode($contextPayload) . '}]);</script>';
    $parsed = LinkPreviewService::parseInstagramEmbedHtml($html);

    if (!str_contains((string)$parsed['video_url'], 'sample.mp4')) {
        throw new RuntimeException('Failed to extract video_url from contextJSON.');
    }
    if (!str_contains((string)$parsed['image_url'], 'cover_e15_tt6.jpg')) {
        throw new RuntimeException('Failed to extract display_url from contextJSON.');
    }
    if ($parsed['aspect_ratio'] !== '9 / 16') {
        throw new RuntimeException('Expected aspect_ratio 9 / 16, got: ' . var_export($parsed['aspect_ratio'], true));
    }
    if ($parsed['username'] !== 'djbanks' || $parsed['full_name'] !== 'DJ Banks') {
        throw new RuntimeException('Owner metadata mismatch.');
    }
    if ($parsed['caption'] !== 'Afrobeats & Amapiano live set in Falmouth!') {
        throw new RuntimeException('Caption mismatch.');
    }
});

runIgTest('Detects expired vs active Instagram CDN oe= hex timestamps', function (): void {
    $expired = 'https://scontent-man2-1.cdninstagram.com/v/video.mp4?oe=' . dechex(time() - 3600);
    $fresh = 'https://scontent-man2-1.cdninstagram.com/v/video.mp4?oe=' . dechex(time() + 86400);
    if (!LinkPreviewService::isInstagramCdnUrlExpired($expired)) {
        throw new RuntimeException('Expired oe= URL was not detected.');
    }
    if (LinkPreviewService::isInstagramCdnUrlExpired($fresh)) {
        throw new RuntimeException('Fresh oe= URL was falsely flagged as expired.');
    }
});

runIgTest('PostDto promotes Instagram Reel link_preview into native portrait video descriptor', function (): void {
    $sig = LinkPreviewService::signStreamUrl('ig:DUNGKnBCSdC');
    $proxyUrl = '/resources/videos/external-stream?ig=DUNGKnBCSdC&sig=' . $sig;
    $row = [
        'post_no' => 888001,
        'id' => 'u_ig_test',
        'fullName' => 'Wally Olaogun',
        'postMessage' => 'https://www.instagram.com/reel/DUNGKnBCSdC/',
        'postFamCode' => 'OLA001',
        'date_created' => date('Y-m-d H:i:s'),
        'post_time' => (string)time(),
        'post_status' => 'published',
        'post_likes' => 0,
        'link_preview' => [
            'url' => 'https://www.instagram.com/reel/DUNGKnBCSdC/',
            'original_url' => 'https://www.instagram.com/reel/DUNGKnBCSdC/',
            'title' => 'DJ Banks (@djbanks) on Instagram Reels',
            'description' => 'Afrobeats & Amapiano live set!',
            'image' => '/public/uploads/link_previews/ig_clean.jpg',
            'video_url' => $proxyUrl,
            'proxied_video_url' => $proxyUrl,
            'aspect_ratio' => '9 / 16',
            'is_video' => true,
            'is_social_video' => true,
            'is_instagram' => true,
            'ig_shortcode' => 'DUNGKnBCSdC',
            'site_name' => 'Instagram',
            'domain' => 'instagram.com',
        ],
    ];

    $dto = PostDto::fromDatabaseRow($row, 'u_ig_test');
    $arr = $dto->toArray();

    if (empty($arr['videos']) || count($arr['videos']) !== 1) {
        throw new RuntimeException('Instagram Reel was not promoted into PostDto videos array.');
    }
    $vid = $arr['videos'][0];
    if (($vid['provider'] ?? '') !== 'instagram' || ($vid['embedUrl'] ?? '') !== $proxyUrl || empty($vid['isShort'])) {
        throw new RuntimeException('Promoted Instagram video descriptor malformed: ' . json_encode($vid));
    }
    if (trim((string)($arr['displayMessage'] ?? '')) !== '') {
        throw new RuntimeException('Raw Instagram URL was not stripped from displayMessage: ' . $arr['displayMessage']);
    }

    $parsedProxy = VideoParser::parseVideoUrl($proxyUrl);
    if (!$parsedProxy || ($parsedProxy['videoId'] ?? '') !== 'ig_DUNGKnBCSdC') {
        throw new RuntimeException('VideoParser failed to parse Instagram proxy URL.');
    }
});

runIgTest('Adversarial Security: Neutralises untrusted CDN hosts, SSRF, and tampered HMAC signatures', function (): void {
    $evilUrls = [
        'http://scontent-man2-1.cdninstagram.com/video.mp4',
        'https://evil.example.com/video.mp4',
        'https://cdninstagram.com.evil.com/video.mp4',
        'https://127.0.0.1/video.mp4',
        'https://169.254.169.254/latest/meta-data/',
    ];
    foreach ($evilUrls as $bad) {
        if (LinkPreviewService::isInstagramCdnUrl($bad)) {
            throw new RuntimeException("SECURITY VIOLATION: isInstagramCdnUrl allowed {$bad}");
        }
    }

    $validSig = LinkPreviewService::signStreamUrl('ig:DUNGKnBCSdC');
    if (!LinkPreviewService::verifyStreamSignature('ig:DUNGKnBCSdC', $validSig)) {
        throw new RuntimeException('Valid Instagram stream signature rejected.');
    }
    if (LinkPreviewService::verifyStreamSignature('ig:OTHER_CODE', $validSig)) {
        throw new RuntimeException('SECURITY VIOLATION: Tampered shortcode accepted valid signature.');
    }
    if (LinkPreviewService::verifyStreamSignature('ig:DUNGKnBCSdC', str_repeat('0', strlen($validSig)))) {
        throw new RuntimeException('SECURITY VIOLATION: Forged signature accepted.');
    }
});

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🎉 ALL {$passed} INSTAGRAM VIDEO & SECURITY TESTS PASSED!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failed} TESTS FAILED out of " . ($passed + $failed) . "!\n";
    echo "=================================================================\n";
    exit(1);
}
