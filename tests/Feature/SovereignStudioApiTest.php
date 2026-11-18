<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\api\SovereignStudioApiController;
use PHPUnit\Framework\TestCase;
use Src\Media\SovereignVideoJobHandler;

final class SovereignStudioApiTest extends TestCase
{
    private ?string $prevSecret = null;
    private ?string $prevEnv = null;
    private ?string $prevAuth = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prevSecret = $_ENV['SOVEREIGN_STUDIO_LICENSE_SECRET'] ?? null;
        $this->prevEnv = $_ENV['APP_ENV'] ?? null;
        $this->prevAuth = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['SOVEREIGN_STUDIO_LICENSE_SECRET'] = 'test_sovereign_studio_secret_123456';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        SovereignStudioApiController::$testRawBody = null;
    }

    protected function tearDown(): void
    {
        SovereignStudioApiController::$testRawBody = null;
        if ($this->prevSecret !== null) {
            $_ENV['SOVEREIGN_STUDIO_LICENSE_SECRET'] = $this->prevSecret;
        } else {
            unset($_ENV['SOVEREIGN_STUDIO_LICENSE_SECRET']);
        }
        if ($this->prevEnv !== null) {
            $_ENV['APP_ENV'] = $this->prevEnv;
        } else {
            unset($_ENV['APP_ENV']);
        }
        if ($this->prevAuth !== null) {
            $_SERVER['HTTP_AUTHORIZATION'] = $this->prevAuth;
        } else {
            unset($_SERVER['HTTP_AUTHORIZATION']);
        }
        parent::tearDown();
    }

    /**
     * @runInSeparateProcess
     */
    public function test_execute_job_rejects_oversized_and_invalid_json_and_unauthenticated_requests(): void
    {
        $ctrl = new SovereignStudioApiController();

        // 1. Oversized payload (>256 KB) -> 413
        SovereignStudioApiController::$testRawBody = str_repeat('a', SovereignStudioApiController::MAX_REQUEST_BYTES + 10);
        ob_start();
        $ctrl->executeJob();
        $out413 = (string)ob_get_clean();
        $this->assertSame(413, http_response_code());
        $this->assertStringContainsString('256 KB ceiling', $out413);

        // 2. Malformed JSON -> 400
        SovereignStudioApiController::$testRawBody = '{bad-json';
        ob_start();
        $ctrl->executeJob();
        $out400 = (string)ob_get_clean();
        $this->assertSame(400, http_response_code());
        $this->assertStringContainsString('Invalid JSON', $out400);

        // 3. Missing/invalid license token -> 401
        SovereignStudioApiController::$testRawBody = json_encode(['action' => 'storyboard', 'prompt' => 'Test']);
        unset($_SERVER['HTTP_AUTHORIZATION']);
        ob_start();
        $ctrl->executeJob();
        $out401 = (string)ob_get_clean();
        $this->assertSame(401, http_response_code());
        $this->assertNotEmpty($out401);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_sdk_manifest_enforces_token_and_entitlement_tier(): void
    {
        $ctrl = new SovereignStudioApiController();

        // 1. Missing bearer token -> 401
        unset($_SERVER['HTTP_AUTHORIZATION']);
        ob_start();
        $ctrl->sdkManifest();
        ob_get_clean();
        $this->assertSame(401, http_response_code());

        // 2. Starter tier lacks allowWhitelabelDownload -> 403
        $starterToken = SovereignVideoJobHandler::issueLicenseToken(
            licenseeOrg: 'Starter Family Org',
            tier: 'starter',
            allowedDomains: ['*'],
            ttlDays: 7,
            signingSecret: 'test_sovereign_studio_secret_123456'
        );
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $starterToken;
        ob_start();
        $ctrl->sdkManifest();
        $out403 = (string)ob_get_clean();
        $this->assertSame(403, http_response_code());
        $this->assertStringContainsString('ENTITLEMENT VIOLATION', $out403);

        // 3. Enterprise whitelabel tier -> 200
        $entToken = SovereignVideoJobHandler::issueLicenseToken(
            licenseeOrg: 'Enterprise Family Org',
            tier: 'enterprise_whitelabel',
            allowedDomains: ['*'],
            ttlDays: 7,
            signingSecret: 'test_sovereign_studio_secret_123456'
        );
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $entToken;
        ob_start();
        $ctrl->sdkManifest();
        $out200 = (string)ob_get_clean();
        $this->assertSame(200, http_response_code());
        $decoded = json_decode($out200, true);
        $this->assertIsArray($decoded);
        $this->assertSame(200, $decoded['status']);
        $this->assertArrayHasKey('manifest', $decoded);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_openapi_endpoint_returns_valid_specification(): void
    {
        $ctrl = new SovereignStudioApiController();
        ob_start();
        $ctrl->openApi();
        $out = (string)ob_get_clean();
        $this->assertSame(200, http_response_code());
        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('openapi', $decoded);
    }
}
