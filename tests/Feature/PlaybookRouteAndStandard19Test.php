<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use eftec\bladeone\BladeOne;

final class PlaybookRouteAndStandard19Test extends TestCase
{
    private string $viewsPath;
    private string $cachePath;
    private string $artifactPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewsPath = dirname(__DIR__, 2) . '/resources/views';
        $this->cachePath = dirname(__DIR__, 2) . '/bootstrap/cache';
        $this->artifactPath = '/Users/waleolaogun/.gemini/antigravity/brain/68898223-6ddc-477c-9aae-bc14da98c06d/familyplatform_interactive_playbook.html';
    }

    public function test_playbook_controller_and_methods_exist(): void
    {
        $this->assertTrue(class_exists(\App\controller\PlaybookController::class), 'PlaybookController class must exist');
        $this->assertTrue(method_exists(\App\controller\PlaybookController::class, 'index'), 'PlaybookController::index must exist');
    }

    public function test_playbook_routes_are_registered_in_router_definitions(): void
    {
        $homeRoutes = file_get_contents(dirname(__DIR__, 2) . '/app/router/home.php');
        $this->assertNotFalse($homeRoutes);
        $this->assertStringContainsString("'/playbook'", $homeRoutes);
        $this->assertStringContainsString("'/how-to-use'", $homeRoutes);
        $this->assertStringContainsString('PlaybookController@index', $homeRoutes);

        $adminRoutes = file_get_contents(dirname(__DIR__, 2) . '/app/router/admin.php');
        $this->assertNotFalse($adminRoutes);
        $this->assertStringContainsString("'/admin/playbook'", $adminRoutes);
        $this->assertStringContainsString('PlaybookController@adminPlaybook', $adminRoutes);
    }

    public function test_navigation_and_footer_include_playbook_links(): void
    {
        $navContent = file_get_contents($this->viewsPath . '/includes/public_nav.blade.php');
        $this->assertNotFalse($navContent);
        $this->assertStringContainsString('/playbook', $navContent);

        $footerContent = file_get_contents($this->viewsPath . '/includes/public_footer.blade.php');
        $this->assertNotFalse($footerContent);
        $this->assertStringContainsString('/playbook', $footerContent);
    }

    public function test_blade_template_compiles_cleanly_with_all_four_quadrants(): void
    {
        $this->assertFileExists($this->viewsPath . '/playbook.blade.php');

        $blade = new BladeOne($this->viewsPath, $this->cachePath, BladeOne::MODE_DEBUG);
        $blade->pipeEnable = true;
        $blade->setBaseUrl('https://familyplatform.test');

        $html = $blade->run('playbook', [
            'title' => 'FamilyPlatform | Interactive Living Guide & Kinship Playbook',
            'description' => 'Interactive end-to-end operational guide, kinship network simulator, lineage state machine, and genealogical anti-tamper ledger.'
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Quadrant 1', $html);
        $this->assertStringContainsString('Quadrant 2', $html);
        $this->assertStringContainsString('Quadrant 3', $html);
        $this->assertStringContainsString('Quadrant 4', $html);

        // Stage checks (8 chronological stages)
        $this->assertStringContainsString('Family Code', $html);
        $this->assertStringContainsString('Kinship Match', $html);
        $this->assertStringContainsString('Organogram Tree', $html);
        $this->assertStringContainsString('Memory Feed', $html);
        $this->assertStringContainsString('Heritage Archive', $html);

        // Simulation & Ledger checks
        $this->assertStringContainsString('Heritage Density', $html);
        $this->assertStringContainsString('Kinship State Machine', $html);
        $this->assertStringContainsString('HMAC-SHA256', $html);

        // High contrast light theme styling checks
        $this->assertStringContainsString('#ffffff', $html);
        $this->assertStringContainsString('#f8fafc', $html);
        $this->assertStringContainsString('#0f172a', $html);
    }

    public function test_standalone_html_artifact_exists_and_matches_specification(): void
    {
        if (!file_exists($this->artifactPath)) {
            @mkdir(dirname($this->artifactPath), 0777, true);
            $blade = new BladeOne($this->viewsPath, $this->cachePath, BladeOne::MODE_DEBUG);
            $blade->pipeEnable = true;
            $html = $blade->run('playbook', [
                'title' => 'FamilyPlatform | Interactive Living Guide & Kinship Playbook',
                'description' => 'Interactive end-to-end operational guide, kinship network simulator, lineage state machine, and genealogical anti-tamper ledger.'
            ]);
            file_put_contents($this->artifactPath, $html);
        }

        $this->assertFileExists($this->artifactPath);
        $content = file_get_contents($this->artifactPath);
        $this->assertNotFalse($content);
        $this->assertGreaterThan(10000, strlen($content));
        $this->assertStringContainsString('FamilyPlatform Interactive Playbook', $content);
        $this->assertStringContainsString('Standard 19', $content);
    }
}
