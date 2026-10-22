<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\model\ReviewAppsData;
use PHPUnit\Framework\TestCase;

final class ReviewAppsDataTest extends TestCase
{
    public function test_index_does_not_throw_unknown_named_parameter_error(): void
    {
        $model = new ReviewAppsData();
        $result = $model->index();

        $this->assertTrue(is_array($result) || is_bool($result) || is_null($result));
    }

    public function test_get_with_id_does_not_throw_unknown_named_parameter_error(): void
    {
        $model = new ReviewAppsData();
        $result = $model->getWithId('non_existent_test_id_99999');

        $this->assertTrue(is_array($result) || is_bool($result) || is_null($result));
    }

    public function test_admin_review_apps_blade_template_exists_with_matching_casing(): void
    {
        $templatePath = dirname(__DIR__, 2) . '/resources/views/admin/reviewApps.blade.php';
        $this->assertFileExists($templatePath, 'resources/views/admin/reviewApps.blade.php must exist on disk matching case');

        $viewDir = dirname(__DIR__, 2) . '/resources/views';
        $cacheDir = dirname(__DIR__, 2) . '/bootstrap/cache';
        $blade = new \eftec\bladeone\BladeOne($viewDir, $cacheDir, \eftec\bladeone\BladeOne::MODE_DEBUG);
        $compiled = $blade->run('admin.reviewApps', ['result' => [], 'no' => 1]);
        $this->assertIsString($compiled);
        $this->assertStringContainsString('Member Applications', $compiled);
    }
}
