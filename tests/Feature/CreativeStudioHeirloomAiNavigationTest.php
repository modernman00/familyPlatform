<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class CreativeStudioHeirloomAiNavigationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function testHeirloomAiMovedUnderCreativeStudioInNavbar(): void
    {
        $navbarPath = $this->root . '/resources/views/layouts/navbar.blade.php';
        $navbarContent = (string)file_get_contents($navbarPath);

        // 1. Heirloom AI must NOT be a top-level nav item
        $this->assertStringNotContainsString(
            '<li class="nav-item livingHeirloomNav">',
            $navbarContent,
            'Heirloom AI should no longer be a standalone top-level navbar item'
        );

        // 2. Creative Studio nav trigger must be configured properly with no conflicting tooltips
        $this->assertStringContainsString('id="creativeStudioNavItem"', $navbarContent);
        $this->assertStringContainsString('id="creativeStudioNavTrigger"', $navbarContent);
        $this->assertStringContainsString('data-no-family-tooltip="true"', $navbarContent);

        // 3. Creative Studio dropdown must include Heirloom AI and all creative tools as interactive links
        $this->assertStringContainsString('/family/living-heirloom', $navbarContent);
        $this->assertStringContainsString('Heirloom AI', $navbarContent);
        $this->assertStringContainsString('/familyStudio', $navbarContent);
        $this->assertStringContainsString('/poster-studio', $navbarContent);
        $this->assertStringContainsString('/member/festive-cards', $navbarContent);
        $this->assertStringContainsString('/family-studio', $navbarContent);
        $this->assertStringContainsString('/arcade', $navbarContent);
        $this->assertStringContainsString('cs-dropdown-link', $navbarContent);
        $this->assertStringContainsString('cs-dropdown-hub-link', $navbarContent);
    }

    public function testNavbarCssHasPointerBridgeAndClickState(): void
    {
        $cssPath = $this->root . '/public/css/navbar.css';
        $cssContent = (string)file_get_contents($cssPath);

        // Hit bridge to prevent gap dismissal
        $this->assertStringContainsString('.creative-studio-rich-tooltip::after', $cssContent);
        $this->assertStringContainsString('pointer-events: auto', $cssContent);
        $this->assertStringContainsString('.creativeStudioNav.is-open .creative-studio-rich-tooltip', $cssContent);
        $this->assertStringContainsString('.cs-dropdown-link', $cssContent);
        $this->assertStringContainsString('min-height: 44px', $cssContent);
    }

    public function testFamilyTooltipIgnoresCreativeStudioNav(): void
    {
        $jsPath = $this->root . '/public/js/family-tooltip.js';
        $jsContent = (string)file_get_contents($jsPath);

        $this->assertStringContainsString('data-no-family-tooltip', $jsContent);
        $this->assertStringContainsString('.creativeStudioNav', $jsContent);
    }

    public function testCreativeStudioHubIncludesHeirloomAi(): void
    {
        $hubPath = $this->root . '/resources/views/member/creativeStudio.blade.php';
        $hubContent = (string)file_get_contents($hubPath);

        // Marquee tile
        $this->assertStringContainsString('/family/living-heirloom', $hubContent);
        $this->assertStringContainsString('Heirloom AI', $hubContent);

        // Spark pill
        $this->assertStringContainsString('Living Heirloom &amp; Age Progression', $hubContent);

        // Grid Card
        $this->assertStringContainsString('Heirloom AI Studio', $hubContent);
        $this->assertStringContainsString('Launch Heirloom AI', $hubContent);
    }

    public function testCreativeStudioTabsIncludesHeirloomAi(): void
    {
        $tabsPath = $this->root . '/resources/views/member/creativeStudioTabs.blade.php';
        $tabsContent = (string)file_get_contents($tabsPath);

        $this->assertStringContainsString('/family/living-heirloom', $tabsContent);
        $this->assertStringContainsString('Heirloom AI', $tabsContent);
    }

    public function testLivingHeirloomViewIncludesCreativeStudioTabs(): void
    {
        $viewPath = $this->root . '/resources/views/member/livingHeirloom.blade.php';
        $viewContent = (string)file_get_contents($viewPath);

        $this->assertStringContainsString("@include('member.creativeStudioTabs', ['creativeTab' => 'heirloom'])", $viewContent);
    }

    public function testHowToUsePlaybookDocumentsHeirloomAiUnderCreativeStudio(): void
    {
        $howToUsePath = $this->root . '/resources/views/howToUse.blade.php';
        $howToUseContent = (string)file_get_contents($howToUsePath);

        $this->assertStringContainsString('Creative Studio &rarr; Heirloom AI', $howToUseContent);
    }
}
