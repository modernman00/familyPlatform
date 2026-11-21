<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\classes\VideoParser;
use App\controller\members\StreamController;
use App\services\CloudflareStreamService;
use App\services\VideoAutoTrimService;
use PHPUnit\Framework\TestCase;

final class MediaPipelinePhase1And2Test extends TestCase
{
    public function testOrientationAwareDurationTiers(): void
    {
        $this->assertSame(180, CloudflareStreamService::resolveMaxDurationSeconds('portrait'));
        $this->assertSame(180, CloudflareStreamService::resolveMaxDurationSeconds('reel'));
        $this->assertSame(180, CloudflareStreamService::resolveMaxDurationSeconds('square'));
        $this->assertSame(180, CloudflareStreamService::resolveMaxDurationSeconds(null));
        $this->assertSame(300, CloudflareStreamService::resolveMaxDurationSeconds('landscape'));
        $this->assertSame(300, CloudflareStreamService::resolveMaxDurationSeconds('cinema'));

        // Default 3-arg clampTrimWindow preserves 30s backward compatibility
        $defaultWin = VideoAutoTrimService::clampTrimWindow(0.0, null, 120.0);
        $this->assertSame(30.0, $defaultWin['duration']);
        $this->assertTrue($defaultWin['isTrimmed']);

        // Portrait 180s tier
        $portraitWin = VideoAutoTrimService::clampTrimWindow(0.0, 210.0, 240.0, 180.0);
        $this->assertSame(180.0, $portraitWin['duration']);
        $this->assertTrue($portraitWin['isTrimmed']);

        // Landscape 300s tier
        $landscapeWin = VideoAutoTrimService::clampTrimWindow(0.0, 240.0, 240.0, 300.0);
        $this->assertSame(240.0, $landscapeWin['duration']);
        $this->assertFalse($landscapeWin['isTrimmed']);

        // Landscape > 300s clamps to 300s
        $landscapeClamped = VideoAutoTrimService::clampTrimWindow(10.0, 500.0, 400.0, 300.0);
        $this->assertSame(300.0, $landscapeClamped['duration']);
        $this->assertSame(310.0, $landscapeClamped['end']);
        $this->assertTrue($landscapeClamped['isTrimmed']);
    }

    public function testCloudflareHlsUrlAndGracefulFallback(): void
    {
        $service = new CloudflareStreamService();
        $hls = $service->buildHlsUrl('0123456789abcdef0123456789abcdef');
        $this->assertStringEndsWith('/0123456789abcdef0123456789abcdef/manifest/video.m3u8', $hls);
        $this->assertStringStartsWith('https://', $hls);

        if (!$service->isConfigured()) {
            $direct = $service->createDirectUploadUrl(90, 0, 'portrait');
            $this->assertFalse($direct['success']);
            $tus = $service->createTusUploadUrl(1024 * 1024, 300, 0, 'landscape');
            $this->assertFalse($tus['success']);
        }
    }

    public function testVideoParserHlsAndMediaMeta(): void
    {
        $parsedM3u8 = VideoParser::parseVideoUrl('/resources/videos/posts/sample_master.m3u8');
        $this->assertNotNull($parsedM3u8);
        $this->assertSame('direct', $parsedM3u8['type']);
        $this->assertSame('/resources/videos/posts/sample_master.m3u8', $parsedM3u8['hlsUrl'] ?? null);

        $cfUid = 'abcdef0123456789abcdef0123456789';
        $parsedCf = VideoParser::parseVideoUrl("https://iframe.videodelivery.net/{$cfUid}");
        $this->assertNotNull($parsedCf);
        $this->assertSame('cloudflare', $parsedCf['type']);
        $this->assertStringContainsString("{$cfUid}/manifest/video.m3u8", (string)($parsedCf['hlsUrl'] ?? ''));

        // Normalise media_meta with clamping & XSS/SSRF filtering
        $cleanMeta = VideoParser::normaliseMediaMeta(json_encode([
            'focal_x' => 150,
            'focal_y' => -10,
            'aspect_ratio' => '4 / 5',
            'duration_sec' => 84.567,
            'hls_url' => 'javascript:alert(1)',
        ]));
        $this->assertIsArray($cleanMeta);
        $this->assertSame(100.0, $cleanMeta['focal_x']);
        $this->assertSame(0.0, $cleanMeta['focal_y']);
        $this->assertSame('4 / 5', $cleanMeta['aspect_ratio']);
        $this->assertSame(84.57, $cleanMeta['duration_sec']);
        $this->assertArrayNotHasKey('hls_url', $cleanMeta);

        $validMeta = VideoParser::normaliseMediaMeta([
            'focal_x' => 48.5,
            'focal_y' => 26.2,
            'hls_url' => 'https://videodelivery.net/abcdef0123456789abcdef0123456789/manifest/video.m3u8',
        ]);
        $this->assertIsArray($validMeta);
        $merged = VideoParser::mergeMediaMetaIntoDescriptor(['type' => 'direct', 'embedUrl' => '/a.mp4'], $validMeta);
        $this->assertSame(48.5, $merged['focal_x']);
        $this->assertSame(26.2, $merged['focal_y']);
        $this->assertSame('https://videodelivery.net/abcdef0123456789abcdef0123456789/manifest/video.m3u8', $merged['hlsUrl']);
    }

    public function testStreamControllerGetDirectUploadUrlEnforcesCsrfCheck(): void
    {
        $reflection = new \ReflectionMethod(StreamController::class, 'getDirectUploadUrl');
        $fileName = $reflection->getFileName();
        $this->assertIsString($fileName);
        $source = (string)file_get_contents($fileName);
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();
        $lines = array_slice(explode("\n", $source), $startLine - 1, $endLine - $startLine + 1);
        $methodBody = implode("\n", $lines);

        $this->assertStringContainsString('\Src\CheckToken::tokenCheck()', $methodBody);
        $this->assertStringContainsString('UnauthorisedException', $methodBody);
    }

    public function testStreamControllerRejectsInvalidCsrfWith403Not500(): void
    {
        $_SESSION['id'] = 'user_test_csrf_check';
        $_SESSION['token'] = 'valid_session_csrf_token_123';
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'wrong_csrf_token';
        unset($_POST['token']);

        $controller = new StreamController();
        ob_start();
        $controller->getDirectUploadUrl();
        $output = (string)ob_get_clean();

        $this->assertSame(403, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success'] ?? true);

        unset($_SESSION['id'], $_SESSION['token'], $_SERVER['HTTP_X_CSRF_TOKEN']);
        http_response_code(200);
    }
}
