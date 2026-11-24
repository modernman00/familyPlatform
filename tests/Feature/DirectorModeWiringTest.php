<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\DirectorModeController;
use PHPUnit\Framework\TestCase;

final class DirectorModeWiringTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
        $GLOBALS['__TEST_NO_EXIT__'] = true;
        $_SESSION = [];
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['__TEST_NO_EXIT__'], $_SERVER['HTTP_X_CSRF_TOKEN']);
        $_SESSION = [];
    }

    /** @return array<string, mixed> */
    private function callEndpoint(string $method): array
    {
        ob_start();
        try {
            $ctrl = new DirectorModeController();
            if ($method === 'render') {
                $ctrl->render();
            } else {
                $ctrl->plan();
            }
        } finally {
            $out = (string) ob_get_clean();
        }
        $decoded = json_decode($out, true);

        return is_array($decoded) ? $decoded : ['raw' => $out];
    }

    public function testPlanAndRenderRejectMissingOrForgedCsrf(): void
    {
        $_SESSION['token'] = 'real-token';
        $this->assertSame(403, $this->callEndpoint('plan')['status']);
        $this->assertSame(403, $this->callEndpoint('render')['status']);

        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'forged';
        $this->assertSame(403, $this->callEndpoint('plan')['status']);
        $this->assertSame(403, $this->callEndpoint('render')['status']);
    }

    public function testPlanAndRenderRejectUnauthenticatedSession(): void
    {
        $_SESSION['token'] = 'real-token';
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'real-token';
        $this->assertSame(403, $this->callEndpoint('plan')['status']);
        $this->assertSame(403, $this->callEndpoint('render')['status']);
    }

    public function testRoutesNavigationAndLivingGuidesAreWired(): void
    {
        $routes = (string) file_get_contents($this->root . '/app/router/reelsRoute.php');
        $this->assertStringContainsString('/director-mode', $routes);
        $this->assertStringContainsString('/api/director-mode/plan', $routes);
        $this->assertStringContainsString('/api/director-mode/render', $routes);

        $dispatch = (string) file_get_contents($this->root . '/app/router/RouteDispatch.php');
        $this->assertStringNotContainsString('DirectorModeController', $dispatch);

        $tabs = (string) file_get_contents($this->root . '/resources/views/member/creativeStudioTabs.blade.php');
        $this->assertStringContainsString('href="/director-mode"', $tabs);

        $familyStudio = (string) file_get_contents($this->root . '/resources/views/member/family_studio.blade.php');
        $this->assertStringContainsString('href="/director-mode"', $familyStudio);

        $studioShell = (string) file_get_contents($this->root . '/resources/asset/js/apps/studio/StudioShell.jsx');
        $this->assertStringContainsString('href="/director-mode"', $studioShell);

        $howTo = (string) file_get_contents($this->root . '/resources/views/howToUse.blade.php');
        $this->assertStringContainsString('/director-mode', $howTo);

        $playbook = (string) file_get_contents($this->root . '/resources/views/playbook.blade.php');
        $this->assertStringContainsString('Director Mode', $playbook);

        $this->assertSame(
            md5_file($this->root . '/node_modules/@modernman00/shared-js-lib/SovereignDirectorMode.js'),
            md5_file($this->root . '/public/js/SovereignDirectorMode.js')
        );
    }
}
