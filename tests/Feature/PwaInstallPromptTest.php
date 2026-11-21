<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class PwaInstallPromptTest extends TestCase
{
    private string $rootDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootDir = dirname(__DIR__, 2);
    }

    public function testPwaInstallPartialExistsWithAccessibleStylesAndNonce(): void
    {
        $partialPath = $this->rootDir . '/resources/views/partials/pwa-install.blade.php';
        $this->assertFileExists($partialPath);

        $contents = (string) file_get_contents($partialPath);
        $this->assertStringContainsString('#pwa-install-banner', $contents);
        $this->assertStringContainsString('#pwa-ios-overlay', $contents);
        $this->assertStringContainsString('min-height: 44px', $contents);
        $this->assertStringContainsString('min-width: 44px', $contents);
        $this->assertStringContainsString('white-space: nowrap', $contents);
        $this->assertStringContainsString('csp_nonce($nonce ?? null)', $contents);
        $this->assertStringContainsString('fp-has-bottom-nav', $contents);
    }

    public function testHomepageAndProfileLayoutsIncludePwaInstallPartialAndIndexBundle(): void
    {
        $landingLayout = (string) file_get_contents($this->rootDir . '/resources/views/layouts/landing_layout.blade.php');
        $profileLayout = (string) file_get_contents($this->rootDir . '/resources/views/layouts/profileBase.blade.php');

        $this->assertStringContainsString("@include('partials.pwa-install')", $landingLayout);
        $this->assertStringContainsString('/public/js/index.js', $landingLayout);

        $this->assertStringContainsString("@include('partials.pwa-install')", $profileLayout);
        $this->assertStringContainsString('/public/js/index.js', $profileLayout);
    }

    public function testPwaManagerHasDefensiveStandaloneDetectionAndCompiledBundle(): void
    {
        $source = (string) file_get_contents($this->rootDir . '/resources/asset/js/components/pwa/pwaManager.js');
        $this->assertStringContainsString('detectStandalone()', $source);
        $this->assertStringContainsString('isInstalled()', $source);
        $this->assertStringContainsString('display-mode: standalone', $source);
        $this->assertStringContainsString('window.navigator.standalone', $source);
        $this->assertStringContainsString('fp_pwa_installed', $source);
        $this->assertStringContainsString('fp_pwa_dismissed_session', $source);
        $this->assertStringContainsString('Add to Home Screen', $source);
        $this->assertStringContainsString('Not now', $source);

        $compiled = (string) file_get_contents($this->rootDir . '/public/js/index.js');
        $this->assertStringContainsString('fp_pwa_installed', $compiled);
        $this->assertStringContainsString('fp_pwa_dismissed_session', $compiled);
    }

    public function testHowToUseDocumentsOneTapAppInstallation(): void
    {
        $howToUse = (string) file_get_contents($this->rootDir . '/resources/views/howToUse.blade.php');
        $this->assertStringContainsString('One-Tap App Installation', $howToUse);
    }
}
