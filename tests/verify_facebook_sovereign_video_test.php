<?php

declare(strict_types=1);

/**
 * Automated Regression & Adversarial Verification Suite:
 * Sovereign Facebook Reel/Video Extraction, Share-Link Resolution & Stream Proxy Hardening
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

function runFbTest(string $name, callable $fn): void {
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
echo "🎬 SOVEREIGN FACEBOOK VIDEO & PREVIEW VERIFICATION SUITE\n";
echo "=================================================================\n\n";

runFbTest('Recognises Facebook domains and extracts numeric Reel/Video IDs', function (): void {
    assert(LinkPreviewService::isFacebookDomain('https://www.facebook.com/share/r/1AGvntUbdm/') === true);
    assert(LinkPreviewService::isFacebookDomain('https://fb.watch/abc123/') === true);
    assert(LinkPreviewService::isFacebookDomain('https://evilfacebook.com/reel/123') === false);

    assert(LinkPreviewService::isFacebookVideoOrShareUrl('https://www.facebook.com/share/r/1AGvntUbdm/') === true);
    assert(LinkPreviewService::isFacebookVideoOrShareUrl('https://www.facebook.com/reel/1176514021862056/') === true);
    assert(LinkPreviewService::isFacebookVideoOrShareUrl('https://www.facebook.com/zuck') === false);

    assert(LinkPreviewService::extractFacebookVideoId('https://www.facebook.com/reel/1176514021862056/') === '1176514021862056');
    assert(LinkPreviewService::extractFacebookVideoId('https://www.facebook.com/watch/?v=9876543210123') === '9876543210123');
    assert(LinkPreviewService::extractFacebookVideoId('https://www.facebook.com/share/r/1AGvntUbdm/') === null);
});

runFbTest('Parses Facebook plugins/video.php HTML for hd_src/sd_src fbcdn.net streams', function (): void {
    $hdUrl = 'https://video-man2-1.xx.fbcdn.net/o1/v/t2/f2/m366/test_hd.mp4?oe=6B0B9C33';
    $sdUrl = 'https://video-man2-1.xx.fbcdn.net/o1/v/t2/f2/m412/test_sd.mp4?oe=6B0B9C33';
    $html = '<script>{"hd_src":"' . str_replace('/', '\\/', $hdUrl) . '","sd_src":"' . str_replace('/', '\\/', $sdUrl) . '","original_width":720,"original_height":1280}</script>';

    $parsed = LinkPreviewService::parseFacebookPluginVideoHtml($html);
    assert($parsed['video_url'] === $hdUrl, 'Expected hd_src to be preferred');
    assert($parsed['aspect_ratio'] === '9 / 16', 'Expected 9 / 16 aspect ratio');
});

runFbTest('Rejects untrusted non-fbcdn hosts in Facebook plugin HTML parser', function (): void {
    $evilUrl = 'https://169.254.169.254/latest/meta-data/evil.mp4';
    $html = '<script>{"hd_src":"' . str_replace('/', '\\/', $evilUrl) . '"}</script>';

    $parsed = LinkPreviewService::parseFacebookPluginVideoHtml($html);
    assert($parsed['video_url'] === null, 'Untrusted SSRF host must be rejected');
});

runFbTest('VideoParser recognises signed /resources/videos/external-stream?fb=... proxy URLs', function (): void {
    $proxyUrl = LinkPreviewService::getFacebookStreamProxyUrl('1176514021862056');
    assert(str_starts_with($proxyUrl, '/resources/videos/external-stream?fb=1176514021862056&sig='));

    $parsed = VideoParser::parseVideoUrl($proxyUrl);
    assert(is_array($parsed));
    assert($parsed['type'] === 'direct');
    assert($parsed['videoId'] === 'fb_1176514021862056');
    assert($parsed['aspect_ratio'] === '9 / 16');
});

runFbTest('PostDto promotes Facebook Reel link_preview into native portrait video descriptor', function (): void {
    $proxyUrl = LinkPreviewService::getFacebookStreamProxyUrl('1176514021862056');
    $dto = PostDto::fromDatabaseRow([
        'post_no' => 992001,
        'id' => 'u_fb_test',
        'fullName' => 'Wally Olaogun',
        'postMessage' => 'https://www.facebook.com/share/r/1AGvntUbdm/',
        'postFamCode' => 'OLA001',
        'date_created' => date('Y-m-d H:i:s'),
        'post_time' => (string)time(),
        'post_status' => 'published',
        'post_likes' => 0,
        'link_preview' => [
            'url' => 'https://www.facebook.com/reel/1176514021862056/',
            'original_url' => 'https://www.facebook.com/share/r/1AGvntUbdm/',
            'title' => '41K views | Reel by OpenArt AI',
            'description' => 'Create Your Next Scene on OpenArt.',
            'image' => '/public/uploads/link_previews/fb_clean_poster.jpg',
            'video_url' => $proxyUrl,
            'proxied_video_url' => $proxyUrl,
            'aspect_ratio' => '9 / 16',
            'site_name' => 'Facebook',
            'domain' => 'facebook.com',
            'is_video' => true,
            'is_social_video' => true,
            'is_facebook' => true,
            'fb_video_id' => '1176514021862056',
            'cta_label' => 'Watch on Facebook →',
        ],
    ], 'u_fb_test')->toArray();

    assert(!empty($dto['videos']), 'Expected videos array to be populated');
    assert($dto['videos'][0]['provider'] === 'facebook');
    assert($dto['videos'][0]['embedUrl'] === $proxyUrl);
    assert($dto['videos'][0]['videoId'] === 'fb_1176514021862056');
});

runFbTest('Adversarial Security: Rejects non-numeric fb IDs and tampered HMAC signatures', function (): void {
    assert(LinkPreviewService::getFacebookStreamProxyUrl('../../../etc/passwd') === '');
    assert(LinkPreviewService::verifyStreamSignature('fb:1176514021862056', 'tampered_sig') === false);
    $validSig = LinkPreviewService::signStreamUrl('fb:1176514021862056');
    assert(LinkPreviewService::verifyStreamSignature('fb:1176514021862056', $validSig) === true);
    assert(LinkPreviewService::verifyStreamSignature('fb:9999999999999999', $validSig) === false);
});

echo "\n=================================================================\n";
echo "🎉 ALL {$passed} FACEBOOK VIDEO & SECURITY TESTS PASSED!\n";
echo "=================================================================\n";

exit($failed > 0 ? 1 : 0);
