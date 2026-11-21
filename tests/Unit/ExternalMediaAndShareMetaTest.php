<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\classes\VideoParser;
use App\controller\PostShareController;
use App\services\LinkPreviewService;
use PHPUnit\Framework\TestCase;

final class ExternalMediaAndShareMetaTest extends TestCase
{
    public function testYoutubePlaysInPlatformViaEmbedUrl(): void
    {
        $parsed = VideoParser::parseVideoUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $this->assertNotNull($parsed);
        $this->assertSame('youtube', $parsed['type']);
        $this->assertSame('dQw4w9WgXcQ', $parsed['videoId']);
        $this->assertStringContainsString('youtube.com/embed/dQw4w9WgXcQ', $parsed['embedUrl']);
    }

    public function testGooglePhotosDirectVideoProxiesInPlatform(): void
    {
        $url = 'https://lh3.googleusercontent.com/pw/AP1GczTestAsset123456=m22';
        $parsed = VideoParser::parseVideoUrl($url);
        $this->assertNotNull($parsed);
        $this->assertSame('direct', $parsed['type']);
        $this->assertStringStartsWith('/resources/videos/external-stream?url=', $parsed['embedUrl']);
    }

    public function testFacebookVideoAndReelPlayInPlatform(): void
    {
        $fbWatch = VideoParser::parseVideoUrl('https://fb.watch/abc123XYZ/');
        $this->assertNotNull($fbWatch);
        $this->assertSame('facebook', $fbWatch['type']);
        $this->assertStringStartsWith('https://www.facebook.com/plugins/video.php?href=', $fbWatch['embedUrl']);

        $fbReel = VideoParser::parseVideoUrl('https://www.facebook.com/reel/987654321012345');
        $this->assertNotNull($fbReel);
        $this->assertSame('facebook', $fbReel['type']);
        $this->assertStringContainsString('plugins/video.php', $fbReel['embedUrl']);

        // Opaque /share/r/ and /share/v/ links must NOT be sent to plugins/video.php (renders "Video unavailable")
        $this->assertNull(VideoParser::parseVideoUrl('https://www.facebook.com/share/r/1AGvntUbdm/'));
        $this->assertNull(VideoParser::parseVideoUrl('https://www.facebook.com/share/v/1FRX2SDq6t/'));
        $this->assertTrue(VideoParser::isNonImageShareUrl('https://www.facebook.com/share/r/1AGvntUbdm/'));
        $this->assertFalse(VideoParser::isNonImageShareUrl('https://example.com/photo.jpg'));
    }

    public function testOneDriveVideoNormalisesToEmbedUrl(): void
    {
        $odRedir = VideoParser::parseVideoUrl('https://onedrive.live.com/redir?resid=ABC123!456&authkey=!XYZ');
        $this->assertNotNull($odRedir);
        $this->assertSame('onedrive', $odRedir['type']);
        $this->assertSame('https://onedrive.live.com/embed?resid=ABC123!456&authkey=!XYZ', $odRedir['embedUrl']);

        $odShort = VideoParser::parseVideoUrl('https://1drv.ms/v/s!AtTestOneDriveVideo123');
        $this->assertNotNull($odShort);
        $this->assertSame('onedrive', $odShort['type']);
    }

    public function testGoogleDriveVideoNormalisesToPreviewEmbed(): void
    {
        $gd = VideoParser::parseVideoUrl('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUvWxYz/view?usp=sharing');
        $this->assertNotNull($gd);
        $this->assertSame('googledrive', $gd['type']);
        $this->assertSame('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUvWxYz/preview', $gd['embedUrl']);
    }

    public function testCleanPhotoLinkStripsPromotedExternalVideoUrls(): void
    {
        $msg = "Look at this family clip!\nhttps://fb.watch/abc123XYZ/\nhttps://1drv.ms/v/s!AtTestOneDriveVideo123";
        $cleaned = LinkPreviewService::cleanPhotoLinkFromMessage($msg, []);
        $this->assertSame('Look at this family clip!', $cleaned);
    }

    public function testSocialCrawlerDetectionForWhatsAppAndFacebook(): void
    {
        $this->assertTrue(PostShareController::isSocialCrawler('WhatsApp/2.23.20.0 A'));
        $this->assertTrue(PostShareController::isSocialCrawler('facebookexternalhit/1.1'));
        $this->assertFalse(PostShareController::isSocialCrawler('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'));
    }
}
