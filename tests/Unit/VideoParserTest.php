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
}
