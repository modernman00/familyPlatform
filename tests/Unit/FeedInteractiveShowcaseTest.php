<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class FeedInteractiveShowcaseTest extends TestCase
{
    private string $rootDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootDir = dirname(__DIR__, 2);
    }

    public function testFeedInteractiveShowcaseComponentContainsSixGamesAndSixAppFeatures(): void
    {
        $showcaseFile = $this->rootDir . '/resources/asset/js/apps/feed/FeedInteractiveShowcase.jsx';
        self::assertFileExists($showcaseFile);

        $content = (string) file_get_contents($showcaseFile);

        // Verify 6 playable games
        self::assertStringContainsString("id: 'game_ayo'", $content);
        self::assertStringContainsString("id: 'game_wordie'", $content);
        self::assertStringContainsString("id: 'game_kinpulse'", $content);
        self::assertStringContainsString("id: 'game_kinzip'", $content);
        self::assertStringContainsString("id: 'game_cheeseboard'", $content);
        self::assertStringContainsString("id: 'game_chess'", $content);

        // Verify 6 interactive app functionalities
        self::assertStringContainsString("id: 'feat_kinship_mentions'", $content);
        self::assertStringContainsString("id: 'feat_dynasty_tree'", $content);
        self::assertStringContainsString("id: 'feat_secret_card'", $content);
        self::assertStringContainsString("id: 'feat_heirloom_ai'", $content);
        self::assertStringContainsString("id: 'feat_roundtable'", $content);
        self::assertStringContainsString("id: 'feat_director_mode'", $content);
    }

    public function testFeedShellWeavesInteractiveShowcasesIntoPostStream(): void
    {
        $feedShellFile = $this->rootDir . '/resources/asset/js/apps/feed/FeedShell.jsx';
        self::assertFileExists($feedShellFile);

        $content = (string) file_get_contents($feedShellFile);
        self::assertStringContainsString('FeedInteractiveShowcase', $content);
        self::assertStringContainsString('shouldShowInterleavedShowcase', $content);
    }

    public function testDailyArcadeBannerIncludesInteractiveTryLiveMoveControl(): void
    {
        $bannerFile = $this->rootDir . '/resources/views/member/includes/dailyArcadeBanner.blade.php';
        self::assertFileExists($bannerFile);

        $content = (string) file_get_contents($bannerFile);
        self::assertStringContainsString('playInteractiveMove()', $content);
        self::assertStringContainsString('Try Live Move', $content);
    }

    public function testWhatsNewAndLivingGuidesDocumentPlayableFeedShowcases(): void
    {
        $whatsNew = require $this->rootDir . '/resources/data/whats_new.php';
        self::assertIsArray($whatsNew);
        self::assertSame('2026_11_w19', $whatsNew['version_key'] ?? '');
        self::assertCount(4, $whatsNew['items'] ?? []);

        $howToUse = (string) file_get_contents($this->rootDir . '/resources/views/howToUse.blade.php');
        self::assertStringContainsString('playbook-feed-interactive-showcase', $howToUse);

        $playbook = (string) file_get_contents($this->rootDir . '/resources/views/playbook.blade.php');
        self::assertStringContainsString('Interactive Feed Arcade &amp; App Spotlights', $playbook);
    }
}
