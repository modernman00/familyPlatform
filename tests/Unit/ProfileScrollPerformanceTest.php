<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\repository\FeedRepository;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ProfileScrollPerformanceTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function testGetFeedPostsPageDefaultsToNonBlockingPreviewCacheLookup(): void
    {
        $ref = new ReflectionMethod(FeedRepository::class, 'getFeedPostsPage');
        $params = $ref->getParameters();
        $found = null;
        foreach ($params as $param) {
            if ($param->getName() === 'allowNetworkPreviewFetch') {
                $found = $param;
                break;
            }
        }

        $this->assertNotNull($found, 'FeedRepository::getFeedPostsPage must accept $allowNetworkPreviewFetch');
        $this->assertTrue($found->isDefaultValueAvailable());
        $this->assertFalse(
            $found->getDefaultValue(),
            'Paginated feed slices must never block mid-scroll on live outbound cURL link unfurling'
        );
    }

    public function testProfileLayoutEliminatesSoftwareSvgConvolveFilterAndScrollSpy(): void
    {
        $layout = (string)file_get_contents($this->root . '/resources/views/layouts/profileBase.blade.php');
        $this->assertStringNotContainsString(
            '<feConvolveMatrix',
            $layout,
            'CPU-bound SVG feConvolveMatrix filter causes mid-scroll raster stalls when media enters viewport'
        );
        $this->assertStringNotContainsString(
            'url(#atg-cas-4k-sharpen)',
            $layout,
            'CSS must not reference SVG convolution filter url(#atg-cas-4k-sharpen)'
        );
        $this->assertStringNotContainsString(
            'data-spy="scroll"',
            $layout,
            'Unused Bootstrap ScrollSpy on <body> adds scroll evaluation overhead'
        );
    }

    public function testProfilePageCssEliminatesBackgroundAttachmentFixedAndOptimisesMobileLayers(): void
    {
        $css = (string)file_get_contents($this->root . '/public/css/profilepage.css');
        $this->assertStringNotContainsString(
            'background-attachment: fixed',
            $css,
            'background-attachment: fixed disables GPU tile-scrolling and forces full-viewport repaints on every frame'
        );
        $this->assertStringContainsString('content-visibility: auto', $css);
        $this->assertStringContainsString('overscroll-behavior-y: contain', $css);
        $this->assertStringContainsString('touch-action: pan-y', $css);
    }

    public function testMobileBundleSplittingProactivePrefetchAndModalContainment(): void
    {
        $standardsCss = (string)file_get_contents($this->root . '/public/css/standards-v21.css');
        $this->assertStringContainsString('.vse-skeleton-surface:not(.is-media-loaded)', $standardsCss);
        $this->assertStringContainsString('.modal:not(.show)', $standardsCss);
        $this->assertStringContainsString('content-visibility: hidden', $standardsCss);

        $videoGallery = (string)file_get_contents($this->root . '/resources/asset/js/apps/feed/FeedVideoGallery.jsx');
        $this->assertStringContainsString('!isDirect || !isPlayerActive || !isPlaying', $videoGallery);

        $feedShell = (string)file_get_contents($this->root . '/resources/asset/js/apps/feed/FeedShell.jsx');
        $this->assertStringContainsString("rootMargin: '1400px 0px'", $feedShell);
        $this->assertStringContainsString('prefetchedPagesRef', $feedShell);
        $this->assertStringContainsString('isFirstPost={postIdx === 0}', $feedShell);

        $postCard = (string)file_get_contents($this->root . '/resources/asset/js/apps/feed/PostCard.jsx');
        $this->assertStringContainsString("fetchPriority={isFirstPost ? 'high' : 'auto'}", $postCard);
        $this->assertStringContainsString('LazyWeeklyFunFactsPlayer', $postCard);

        $commentThread = (string)file_get_contents($this->root . '/resources/asset/js/apps/feed/CommentThread.jsx');
        $this->assertStringContainsString('LazyEmojiGifPicker', $commentThread);
    }
}
