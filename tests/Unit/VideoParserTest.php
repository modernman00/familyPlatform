<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\classes\VideoParser;
use PHPUnit\Framework\TestCase;

final class VideoParserTest extends TestCase
{
    public function test_parses_standard_youtube_url(): void
    {
        $url = 'https://www.youtube.com/watch?v=rW9MbYQrTUI&list=RDrW9MbYQrTUI&start_radio=1';
        $result = VideoParser::parseVideoUrl($url);

        $this->assertNotNull($result);
        $this->assertSame('youtube', $result['type']);
        $this->assertSame('rW9MbYQrTUI', $result['videoId']);
        $this->assertSame('https://img.youtube.com/vi/rW9MbYQrTUI/hqdefault.jpg', $result['thumbnailUrl']);
    }

    public function test_parses_short_youtube_url(): void
    {
        $url = 'https://youtu.be/dQw4w9WgXcQ';
        $result = VideoParser::parseVideoUrl($url);

        $this->assertNotNull($result);
        $this->assertSame('youtube', $result['type']);
        $this->assertSame('dQw4w9WgXcQ', $result['videoId']);
        $this->assertSame('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $result['thumbnailUrl']);
    }

    public function test_parses_youtube_shorts_url(): void
    {
        $url = 'https://www.youtube.com/shorts/abc123XYZ_-';
        $result = VideoParser::parseVideoUrl($url);

        $this->assertNotNull($result);
        $this->assertSame('youtube', $result['type']);
        $this->assertSame('abc123XYZ_-', $result['videoId']);
    }

    public function test_parses_vimeo_url(): void
    {
        $url = 'https://vimeo.com/76979871';
        $result = VideoParser::parseVideoUrl($url);

        $this->assertNotNull($result);
        $this->assertSame('vimeo', $result['type']);
        $this->assertSame('76979871', $result['videoId']);
        $this->assertSame('https://vumbnail.com/76979871.jpg', $result['thumbnailUrl']);
    }

    public function test_extracts_video_from_mixed_post_text(): void
    {
        $text = "Hey family! Check out this awesome song: https://www.youtube.com/watch?v=rW9MbYQrTUI Let me know what you think!";
        $video = VideoParser::extractVideoFromText($text);

        $this->assertNotNull($video);
        $this->assertSame('youtube', $video['type']);
        $this->assertSame('rW9MbYQrTUI', $video['videoId']);

        $cleanedText = VideoParser::cleanPostMessage($text, $video);
        $this->assertSame("Hey family! Check out this awesome song:  Let me know what you think!", $cleanedText);
    }

    public function test_clean_post_message_when_only_video_url_is_posted(): void
    {
        $text = 'https://www.youtube.com/watch?v=rW9MbYQrTUI';
        $video = VideoParser::extractVideoFromText($text);

        $this->assertNotNull($video);
        $cleanedText = VideoParser::cleanPostMessage($text, $video);
        $this->assertSame('', $cleanedText);
    }

    public function test_returns_null_for_non_video_urls(): void
    {
        $result = VideoParser::parseVideoUrl('https://example.com/article/123');
        $this->assertNull($result);
    }

    public function test_parses_tiktok_video_url(): void
    {
        $url = 'https://www.tiktok.com/@familycreator/video/7345678901234567890';
        $result = VideoParser::parseVideoUrl($url);

        $this->assertNotNull($result);
        $this->assertSame('tiktok', $result['type']);
        $this->assertSame('7345678901234567890', $result['videoId']);
        $this->assertSame('https://www.tiktok.com/embed/v2/7345678901234567890', $result['embedUrl']);
    }

    public function test_normalises_dropbox_shared_video_link_to_raw_stream(): void
    {
        $url = 'https://www.dropbox.com/scl/fi/abc123xyz/family_reunion.mp4?rlkey=secret123&dl=0';
        $result = VideoParser::parseVideoUrl($url);

        $this->assertNotNull($result);
        $this->assertSame('direct', $result['type']);
        $this->assertStringContainsString('raw=1', $result['embedUrl']);
        $this->assertStringNotContainsString('dl=0', $result['embedUrl']);
    }

    public function test_extracts_multiple_videos_from_post_text_with_accurate_descriptors(): void
    {
        $text = "Watch these 3 clips!\nhttps://youtu.be/dQw4w9WgXcQ\nhttps://vimeo.com/76979871\nhttps://www.dropbox.com/s/xyz987/clip3.mp4?dl=0";
        $videos = VideoParser::extractAllVideosFromText($text, 6);

        $this->assertCount(3, $videos);
        $this->assertSame('youtube', $videos[0]['type']);
        $this->assertSame('dQw4w9WgXcQ', $videos[0]['videoId']);
        $this->assertSame('vimeo', $videos[1]['type']);
        $this->assertSame('76979871', $videos[1]['videoId']);
        $this->assertSame('direct', $videos[2]['type']);
        $this->assertStringContainsString('raw=1', $videos[2]['embedUrl']);
    }

    public function test_google_photos_video_stream_uses_signed_external_stream_proxy(): void
    {
        $gpUrl = 'https://lh3.googleusercontent.com/pw/AP1GczNX-pHBA6IkZXN9JNkeUvR7LH9FhNn5X7nki7zi5SkLQ8SOUiSZWpdEzHBuDSMpMQuNrxxtQefDLsZj7g_xGoKlrv6Tnhk4MHhl0W58mQAL2y49Q5Ee=m22';
        $result = VideoParser::parseVideoUrl($gpUrl);

        $this->assertNotNull($result);
        $this->assertSame('direct', $result['type']);
        $this->assertStringStartsWith('/resources/videos/external-stream?url=', $result['embedUrl']);
        $this->assertStringContainsString('&sig=', $result['embedUrl']);
        $this->assertSame($gpUrl, $result['originalUrl']);
        $this->assertStringEndsWith('=s1600-no', (string)$result['thumbnailUrl']);

        $reParsed = VideoParser::parseVideoUrl($result['embedUrl']);
        $this->assertNotNull($reParsed);
        $this->assertSame($result['embedUrl'], $reParsed['embedUrl']);
    }
}
