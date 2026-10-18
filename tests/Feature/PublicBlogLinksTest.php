<?php
declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\model\BlogData;

final class PublicBlogLinksTest extends TestCase
{
    private string $viewsPath;
    private string $routerPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewsPath = dirname(__DIR__, 2) . '/resources/views';
        $this->routerPath = dirname(__DIR__, 2) . '/app/router';
    }

    public function testBlogRoutesAreMappedCorrectly(): void
    {
        $blogRoutes = file_get_contents($this->routerPath . '/blog.php');
        $this->assertNotFalse($blogRoutes);
        $this->assertStringContainsString("'/blog'", $blogRoutes);
        $this->assertStringContainsString("'/blogs'", $blogRoutes);
        $this->assertStringContainsString("'/blog/[*:slug]'", $blogRoutes);
        $this->assertStringContainsString("'/blogs/[*:slug]'", $blogRoutes);
    }

    public function testPublicNavbarIncludesBlogsLink(): void
    {
        $navContent = file_get_contents($this->viewsPath . '/includes/public_nav.blade.php');
        $this->assertNotFalse($navContent);
        $this->assertStringContainsString('href="/blogs"', $navContent);
        $this->assertStringContainsString('Blog', $navContent);
    }

    public function testBulmaNavbarIncludesBlogsLink(): void
    {
        $bulmaContent = file_get_contents($this->viewsPath . '/includes/bulmaNav.blade.php');
        $this->assertNotFalse($bulmaContent);
        $this->assertStringContainsString('href="/blogs"', $bulmaContent);
        $this->assertStringContainsString('Blog', $bulmaContent);
    }

    public function testPublicFooterIncludesBlogsLink(): void
    {
        $footerContent = file_get_contents($this->viewsPath . '/includes/public_footer.blade.php');
        $this->assertNotFalse($footerContent);
        $this->assertStringContainsString('href="/blogs"', $footerContent);
        $this->assertStringContainsString('Blog &amp; Stories', $footerContent);
    }

    public function testHomepageIncludesBlogPreviewSectionAndLink(): void
    {
        $homeContent = file_get_contents($this->viewsPath . '/index.blade.php');
        $this->assertNotFalse($homeContent);
        $this->assertStringContainsString('href="/blogs"', $homeContent);
        $this->assertStringContainsString('From Our Blog', $homeContent);
    }

    public function testAboutPageIncludesBlogLink(): void
    {
        $aboutContent = file_get_contents($this->viewsPath . '/about.blade.php');
        $this->assertNotFalse($aboutContent);
        $this->assertStringContainsString('href="/blogs"', $aboutContent);
    }

    public function testHowToUsePageIncludesBlogLink(): void
    {
        $howToUseContent = file_get_contents($this->viewsPath . '/howToUse.blade.php');
        $this->assertNotFalse($howToUseContent);
        $this->assertStringContainsString('href="/blogs"', $howToUseContent);
    }

    public function testLandingLayoutSchemaIncludesBlogsUrl(): void
    {
        $layoutContent = file_get_contents($this->viewsPath . '/layouts/landing_layout.blade.php');
        $this->assertNotFalse($layoutContent);
        $this->assertStringContainsString('https://myfamilyplatform.com/blogs', $layoutContent);
    }

    public function testBlogDataGetLatestPublishedBlogs(): void
    {
        $latest = BlogData::getLatestPublishedBlogs(3);
        $this->assertLessThanOrEqual(3, count($latest));
    }
}
