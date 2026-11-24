<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class HomepageHeroVideoTest extends TestCase
{
    public function test_homepage_hero_video_and_mobile_poster_assets_and_markup_are_wired(): void
    {
        $root = dirname(__DIR__, 2);
        $videoPath = $root . '/public/media/family-hero.mp4';
        $posterPath = $root . '/public/media/family-hero-mobile.jpg';
        $bladePath = $root . '/resources/views/index.blade.php';

        $this->assertFileExists($videoPath, 'Homepage family heritage hero MP4 must exist at public/media/family-hero.mp4.');
        $this->assertGreaterThan(100000, (int) filesize($videoPath), 'Hero MP4 must be a genuine encoded video (>100 KB), not a stub.');
        $this->assertLessThan(4000000, (int) filesize($videoPath), 'Hero MP4 must remain under 4 MB for fast loading.');

        $this->assertFileExists($posterPath, 'Homepage mobile fallback poster must exist at public/media/family-hero-mobile.jpg.');
        $this->assertGreaterThan(10000, (int) filesize($posterPath), 'Hero mobile poster must be a valid JPEG.');

        $blade = (string) file_get_contents($bladePath);
        $this->assertStringContainsString('/public/media/family-hero.mp4', $blade);
        $this->assertStringContainsString('/public/media/family-hero-mobile.jpg', $blade);
        $this->assertStringContainsString('id="familyHeroVideo"', $blade);
        $this->assertStringContainsString('id="familyHeroVideoToggleBtn"', $blade);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $blade);
        $this->assertStringContainsString('Visualise your family history', $blade);
    }
}
