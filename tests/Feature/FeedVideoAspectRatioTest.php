<?php

namespace Tests\Feature;

use App\classes\VideoParser;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that all video aspect ratios (9:16, 4:5, 1:1, 16:9, 21:9)
 * are preserved without cropping in metadata normalisation and feed templates.
 */
class FeedVideoAspectRatioTest extends TestCase
{
    public function testNormaliseMediaMetaPreservesAllVideoAspectRatios(): void
    {
        $ratios = [
            '0.5625 / 1', // 9:16 portrait
            '0.8000 / 1', // 4:5 portrait
            '1.0000 / 1', // 1:1 square
            '1.7778 / 1', // 16:9 landscape
            '2.3333 / 1', // 21:9 ultrawide
            '9 / 16',
            '16 / 9',
            '1 / 1',
        ];

        foreach ($ratios as $ratio) {
            $normalised = VideoParser::normaliseMediaMeta([
                'aspect_ratio' => $ratio,
                'duration_sec' => 20.5,
            ]);

            $this->assertIsArray($normalised);
            $this->assertSame($ratio, $normalised['aspect_ratio']);

            $descriptor = VideoParser::mergeMediaMetaIntoDescriptor([
                'type' => 'direct',
                'embedUrl' => '/resources/videos/sample.mp4',
            ], $normalised);

            $this->assertSame($ratio, $descriptor['aspect_ratio']);
        }
    }

    public function testNormaliseMediaMetaRejectsCssInjectionInAspectRatio(): void
    {
        $malicious = VideoParser::normaliseMediaMeta([
            'aspect_ratio' => '9 / 16; background: url(javascript:alert(1))',
        ]);

        $this->assertNull($malicious);
    }

    public function testFeedPostCardUsesInstagramPortraitAndImmersiveLandscapeFraming(): void
    {
        $bladePath = __DIR__ . '/../../resources/views/components/feed/post-card.blade.php';
        $this->assertFileExists($bladePath);

        $blade = (string)file_get_contents($bladePath);

        // Single direct video defaults to 4 / 5 portrait feed preview (portraitExpanded: false) with cover
        $this->assertStringContainsString('portraitExpanded: false,', $blade);
        $this->assertStringContainsString("'4 / 5'", $blade);
        $this->assertStringContainsString("'16 / 10'", $blade);
        $this->assertStringContainsString("? 'cover' : 'contain'", $blade);
        $this->assertStringContainsString('const maxCap = (this.videoOrientation === \'landscape\') ? 300 : 180;', $blade);

        // Multi-video carousel uses object-fit: contain instead of cropping with cover
        $this->assertStringContainsString('object-fit: contain !important;', $blade);
    }
}
