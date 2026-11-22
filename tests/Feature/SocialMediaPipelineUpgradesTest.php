<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\classes\VideoParser;
use App\controller\members\StreamController;
use App\dto\PostDto;
use App\services\LinkPreviewService;
use PDO;
use PHPUnit\Framework\TestCase;

final class SocialMediaPipelineUpgradesTest extends TestCase
{
    public function testPostDtoBuildsNormalizedMediaContract(): void
    {
        $row = [
            'post_no' => 101,
            'id' => 'usr_1',
            'fullName' => 'Ada Lovelace',
            'postMessage' => 'Watch this clip https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'post_img0' => 'photo1.jpg',
            'images' => ['/resources/images/posts/photo1.jpg'],
            'videos' => [
                [
                    'type' => 'direct',
                    'videoId' => null,
                    'embedUrl' => '/resources/videos/posts/clip.mp4',
                    'originalUrl' => '/resources/videos/posts/clip.mp4',
                    'thumbnailUrl' => '/resources/images/posts/thumb.jpg',
                    'aspect_ratio' => '9 / 16',
                    'duration_sec' => 42.5,
                    'hlsUrl' => 'https://videodelivery.net/abc123def45678901234567890123456/manifest/video.m3u8',
                ],
                [
                    'type' => 'youtube',
                    'videoId' => 'dQw4w9WgXcQ',
                    'embedUrl' => 'https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1',
                    'originalUrl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'thumbnailUrl' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
                ],
            ],
            'link_preview' => null,
        ];

        $dto = PostDto::fromDatabaseRow($row, 'usr_1')->toArray();

        $this->assertArrayHasKey('normalized_media', $dto);
        $this->assertCount(3, $dto['normalized_media']);

        $img = $dto['normalized_media'][0];
        $this->assertSame('image', $img['provider']);
        $this->assertSame('image', $img['renderMode']);
        $this->assertSame('/resources/images/posts/photo1.jpg', $img['posterUrl']);

        $native = $dto['normalized_media'][1];
        $this->assertSame('direct', $native['provider']);
        $this->assertSame('native_video', $native['renderMode']);
        $this->assertSame('/resources/videos/posts/clip.mp4', $native['videoSrc']);
        $this->assertSame('9 / 16', $native['aspectRatio']);
        $this->assertSame(42.5, $native['durationSec']);

        $iframe = $dto['normalized_media'][2];
        $this->assertSame('youtube', $iframe['provider']);
        $this->assertSame('inline_iframe', $iframe['renderMode']);
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1', $iframe['embedSrc']);
        $this->assertNull($iframe['videoSrc']);
    }

    public function testPostDtoDowngradesFrameBlockedLinkPreviewToExternalCard(): void
    {
        $row = [
            'post_no' => 102,
            'id' => 'usr_2',
            'fullName' => 'Grace Hopper',
            'postMessage' => 'Read this article https://example.com/post',
            'link_preview' => [
                'url' => 'https://example.com/post',
                'title' => 'Example Post',
                'image' => '/public/uploads/link_previews/abc.jpg',
                'site_name' => 'Example',
                'embed_url' => null,
                'frame_blocked' => true,
            ],
        ];

        $dto = PostDto::fromDatabaseRow($row, 'usr_2')->toArray();
        $this->assertCount(1, $dto['normalized_media']);
        $lpMedia = $dto['normalized_media'][0];
        $this->assertSame('external_card', $lpMedia['renderMode']);
        $this->assertNull($lpMedia['embedSrc']);
        $this->assertSame('/public/uploads/link_previews/abc.jpg', $lpMedia['posterUrl']);
    }

    public function testLinkPreviewServiceDetectsXFrameOptionsAndCspFrameAncestors(): void
    {
        $this->assertTrue(LinkPreviewService::isFramingBlocked(['x-frame-options' => 'DENY']));
        $this->assertTrue(LinkPreviewService::isFramingBlocked(['X-Frame-Options' => 'SAMEORIGIN']));
        $this->assertTrue(LinkPreviewService::isFramingBlocked(['content-security-policy' => "default-src 'self'; frame-ancestors 'none'"]));
        $this->assertTrue(LinkPreviewService::isFramingBlocked(['content-security-policy' => "frame-ancestors 'self' https://other.example.com"], 'myfamilyplatform.com'));
        $this->assertTrue(LinkPreviewService::isFramingBlocked(['content-security-policy' => "frame-ancestors https://notmyfamilyplatform.com"], 'myfamilyplatform.com'));
        $this->assertTrue(LinkPreviewService::isFramingBlocked(['content-security-policy' => "frame-ancestors *; frame-ancestors 'none'"]));

        $this->assertFalse(LinkPreviewService::isFramingBlocked(['content-security-policy' => 'frame-ancestors *']));
        $this->assertFalse(LinkPreviewService::isFramingBlocked(['content-security-policy' => 'frame-ancestors https://myfamilyplatform.com'], 'myfamilyplatform.com'));
        $this->assertFalse(LinkPreviewService::isFramingBlocked(['content-security-policy' => 'frame-ancestors https://*.myfamilyplatform.com'], 'app.myfamilyplatform.com'));
        $this->assertFalse(LinkPreviewService::isFramingBlocked([]));
    }

    public function testMirrorThumbnailRejectsSsrfAndPreservesLocalPaths(): void
    {
        $this->assertNull(LinkPreviewService::mirrorThumbnail('http://127.0.0.1/secret.jpg'));
        $this->assertNull(LinkPreviewService::mirrorThumbnail('http://169.254.169.254/latest/meta-data/'));
        $this->assertSame(
            '/public/uploads/link_previews/cached.jpg',
            LinkPreviewService::mirrorThumbnail('/public/uploads/link_previews/cached.jpg')
        );
    }

    public function testStreamWebhookRejectsInvalidAndExpiredSignatures(): void
    {
        $secret = 'test_webhook_secret_12345';
        $body = json_encode(['uid' => 'abcdef1234567890abcdef1234567890', 'readyToStream' => true]) ?: '{}';
        $now = 1700000000;

        // Valid signature
        $validSig = hash_hmac('sha256', $now . '.' . $body, $secret);
        $this->assertTrue(StreamController::verifyWebhookSignature($body, "time={$now},sig1={$validSig}", $secret, $now + 60));

        // Wrong signature
        $this->assertFalse(StreamController::verifyWebhookSignature($body, "time={$now},sig1=deadbeef", $secret, $now + 60));

        // Expired timestamp (>300s)
        $this->assertFalse(StreamController::verifyWebhookSignature($body, "time={$now},sig1={$validSig}", $secret, $now + 301));

        // Empty secret fails closed
        $this->assertFalse(StreamController::verifyWebhookSignature($body, "time={$now},sig1={$validSig}", '', $now + 60));
    }

    public function testStreamWebhookPersistsHlsDurationAndAspectRatioIntoMediaMeta(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE post (post_no INTEGER PRIMARY KEY, postMessage TEXT, post_img0 TEXT, post_img1 TEXT, post_img2 TEXT, post_img3 TEXT, post_img4 TEXT, post_img5 TEXT, media_meta TEXT)');

        $uid = 'abcdef1234567890abcdef1234567890';
        $stmt = $pdo->prepare('INSERT INTO post (post_no, postMessage, post_img0, post_img1, post_img2, post_img3, post_img4, post_img5, media_meta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([55, "Check this out https://iframe.videodelivery.net/{$uid}", '', '', '', '', '', '', null]);

        $secret = 'cf_stream_secret_xyz';
        $_ENV['CLOUDFLARE_STREAM_WEBHOOK_SECRET'] = $secret;
        $now = time();
        $payload = json_encode([
            'uid' => $uid,
            'readyToStream' => true,
            'status' => ['state' => 'ready'],
            'duration' => 78.4,
            'input' => ['width' => 1080, 'height' => 1920],
            'playback' => ['hls' => "https://videodelivery.net/{$uid}/manifest/video.m3u8"],
            'thumbnail' => '',
        ]) ?: '{}';
        $sig = hash_hmac('sha256', $now . '.' . $payload, $secret);

        $controller = new StreamController();
        ob_start();
        $controller->handleWebhook($payload, "time={$now},sig1={$sig}", $pdo);
        $output = (string)ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertSame(1, $decoded['updated_posts']);
        $this->assertSame('9 / 16', $decoded['aspect_ratio']);
        $this->assertSame(78.4, $decoded['duration_sec']);

        $checkStmt = $pdo->prepare('SELECT media_meta FROM post WHERE post_no = ?');
        $checkStmt->execute([55]);
        $savedMeta = VideoParser::normaliseMediaMeta($checkStmt->fetchColumn());
        $this->assertIsArray($savedMeta);
        $this->assertSame("https://videodelivery.net/{$uid}/manifest/video.m3u8", $savedMeta['hls_url']);
        $this->assertSame(78.4, $savedMeta['duration_sec']);
        $this->assertSame('9 / 16', $savedMeta['aspect_ratio']);

        unset($_ENV['CLOUDFLARE_STREAM_WEBHOOK_SECRET']);
    }
}
