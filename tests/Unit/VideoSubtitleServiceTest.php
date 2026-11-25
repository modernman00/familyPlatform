<?php

declare(strict_types=1);

use App\services\VideoSubtitleService;
use PHPUnit\Framework\TestCase;
use Src\Exceptions\ValidationException;

final class VideoSubtitleServiceTest extends TestCase
{
    public function testNormaliseSourceRejectsTraversalAndUnsafeSchemes(): void
    {
        $this->assertSame('', VideoSubtitleService::normaliseSource(''));
        $this->assertSame('', VideoSubtitleService::normaliseSource('../../../etc/passwd'));
        $this->assertSame('', VideoSubtitleService::normaliseSource('/resources/videos/posts/../../.env'));
        $this->assertSame('', VideoSubtitleService::normaliseSource('file:///etc/passwd'));
        $this->assertSame('', VideoSubtitleService::normaliseSource('http://127.0.0.1/secret.mp4'));
        $this->assertSame('', VideoSubtitleService::normaliseSource('http://localhost/secret.mp4'));
        $this->assertSame('/resources/videos/posts/sample.mp4', VideoSubtitleService::normaliseSource('/resources/videos/posts/sample.mp4#t=0,30'));
    }

    public function testResolveSafeLocalVideoPathRejectsNonMediaDirectories(): void
    {
        $this->assertNull(VideoSubtitleService::resolveSafeLocalVideoPath('/app/controller/members/StreamController.php'));
        $this->assertNull(VideoSubtitleService::resolveSafeLocalVideoPath('/.env'));
        $this->assertNull(VideoSubtitleService::resolveSafeLocalVideoPath('https://cdn.example.com/video.mp4'));
    }

    public function testParseVttCuesSanitizesHtmlAndParsesTimestamps(): void
    {
        $vtt = "WEBVTT\n\n00:00:01.000 --> 00:00:04.500\n<script>alert(1)</script>Hello <b>family</b>!\n\n00:00:05.000 --> 00:00:08.250\nWelcome home.";
        $cues = VideoSubtitleService::parseVttCues($vtt);

        $this->assertCount(2, $cues);
        $this->assertSame(1.0, $cues[0]['start']);
        $this->assertSame(4.5, $cues[0]['end']);
        $this->assertSame('alert(1)Hello family!', $cues[0]['text']);
        $this->assertSame(5.0, $cues[1]['start']);
        $this->assertSame(8.25, $cues[1]['end']);
        $this->assertSame('Welcome home.', $cues[1]['text']);
    }

    public function testGenerateForVideoThrowsOnEmptySource(): void
    {
        $this->expectException(ValidationException::class);
        VideoSubtitleService::generateForVideo('   ');
    }

    public function testGenerateForVideoReturnsDeterministicFallbackForExternalStream(): void
    {
        $result = VideoSubtitleService::generateForVideo('https://cdn.example.com/family-clip.mp4', 'en-GB');
        $this->assertTrue($result['success']);
        $this->assertTrue($result['fallback']);
        $this->assertSame('en-GB', $result['language']);
        $this->assertSame('English (Auto-generated)', $result['label']);
        $this->assertStringContainsString('WEBVTT', $result['vtt']);
        $this->assertNotEmpty($result['cues']);
    }
}
