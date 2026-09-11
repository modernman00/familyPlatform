<?php
declare(strict_types=1);

namespace Tests\Feature;

use eftec\bladeone\BladeOne;
use PHPUnit\Framework\TestCase;

final class BladeCompilationTest extends TestCase
{
    private BladeOne $blade;
    private string $viewsDir;
    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viewsDir = dirname(__DIR__, 2) . '/resources/views';
        $this->cacheDir = dirname(__DIR__, 2) . '/bootstrap/cache';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        $this->blade = new BladeOne($this->viewsDir, $this->cacheDir, BladeOne::MODE_DEBUG);
        $this->blade->pipeEnable = true;
    }

    public function test_all_blade_templates_compile_cleanly(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->viewsDir, \FilesystemIterator::SKIP_DOTS)
        );

        $compiledCount = 0;
        $errors = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $relativePath = str_replace([$this->viewsDir . '/', '.blade.php'], '', $file->getPathname());
            $viewName = str_replace('/', '.', $relativePath);

            try {
                $compiledCode = $this->blade->compile($viewName);
                $this->assertNotEmpty($compiledCode, "Compiled code should not be empty for view: {$viewName}");

                // Validate compiled PHP syntax using token_get_all
                $tokens = @token_get_all("<?php\n" . $compiledCode);
                $this->assertNotEmpty($tokens, "Token stream should not be empty for view: {$viewName}");
                $compiledCount++;
            } catch (\Throwable $e) {
                $errors[] = "View [{$viewName}]: " . $e->getMessage() . " in " . $file->getPathname();
            }
        }

        $this->assertEmpty($errors, "Blade templates failed compilation:\n" . implode("\n", $errors));
        $this->assertGreaterThan(10, $compiledCount, "Should compile at least 10 blade views");
    }

    public function test_no_blade_views_use_clashing_alpine_error_directive(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->viewsDir, \FilesystemIterator::SKIP_DOTS)
        );

        $clashes = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $content = (string)file_get_contents($file->getPathname());
            // Match @error=" or @error = " which is an Alpine event handler mistakenly written as Blade directive
            if (preg_match('/@error\s*=\s*["\']/', $content)) {
                $clashes[] = $file->getPathname();
            }
        }

        $this->assertEmpty($clashes, "Found Alpine @error shorthand colliding with Blade directive in:\n" . implode("\n", $clashes));
    }
}
