<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\KinWhisperController;
use App\controller\members\ShareTargetController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

final class RedTeamAuditRegressionTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $origSession = [];

    /** @var array<string, mixed> */
    private array $origServer = [];

    /** @var array<string, mixed> */
    private array $origPost = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->origSession = $_SESSION ?? [];
        $this->origServer = $_SERVER;
        $this->origPost = $_POST;

        $_SESSION = [
            'id' => 'rt_user_' . bin2hex(random_bytes(4)),
            'token' => 'valid_csrf_token_' . bin2hex(random_bytes(8)),
        ];
        unset(
            $_SERVER['HTTP_X_CSRF_TOKEN'],
            $_SERVER['HTTP_X_XSRF_TOKEN'],
            $_SERVER['HTTP_CSRF_TOKEN']
        );
        $_POST = [];
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->origSession;
        $_SERVER = $this->origServer;
        $_POST = $this->origPost;
        http_response_code(200);
        parent::tearDown();
    }

    public function testKinWhisperStateChangingEndpointsRejectMissingCsrfWith403(): void
    {
        $ctrl = new KinWhisperController();
        $methods = [
            ['apiCreateThread', []],
            ['apiSendMessage', []],
            ['apiUploadAudio', []],
            ['apiMarkThreadRead', ['thr_test']],
            ['apiShareRequest', [['id' => 'thr_test']]],
            ['apiShareRespond', [['id' => 'thr_test']]],
            ['apiRecordScreenshot', [['id' => 'thr_test']]],
            ['apiCreateGroup', []],
            ['apiAddGroupParticipants', ['thr_test']],
            ['apiRemoveGroupParticipant', ['thr_test', 'u_target']],
            ['apiInitiateCall', ['thr_test']],
            ['apiSendCallSignal', ['thr_test']],
            ['apiEndCall', ['thr_test']],
        ];

        foreach ($methods as [$methodName, $args]) {
            http_response_code(200);
            ob_start();
            $ctrl->{$methodName}(...$args);
            $output = (string)ob_get_clean();
            $decoded = json_decode($output, true);

            $this->assertSame(
                403,
                http_response_code(),
                sprintf('Expected 403 CSRF rejection for KinWhisperController::%s, got %d (%s)', $methodName, http_response_code(), $output)
            );
            $this->assertIsArray($decoded);
            $this->assertFalse($decoded['success'] ?? true);
        }
    }

    public function testShareTargetPendingRequiresAuthentication401(): void
    {
        $_SESSION = [];
        $ctrl = new ShareTargetController();

        ob_start();
        $ctrl->getPendingShare();
        $output = (string)ob_get_clean();
        $decoded = json_decode($output, true);

        $this->assertSame(401, http_response_code());
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status'] ?? null);
    }

    public function testKinWhisperAssetOwnershipGuardRejectsForeignMediaUrl(): void
    {
        $ctrl = new KinWhisperController();
        $ref = new ReflectionMethod($ctrl, 'assertWhisperAssetOwnedOrInThread');

        $this->expectException(\Src\Exceptions\ForbiddenException::class);
        $ref->invoke($ctrl, '/public/storage/whispers/media/foreign_secret_file.jpg', '', []);
    }

    public function testKinWhisper500ExceptionDoesNotLeakInternalDetails(): void
    {
        $ctrl = new KinWhisperController();
        $ref = new ReflectionMethod($ctrl, 'handleJsonException');

        ob_start();
        $ref->invoke($ctrl, new RuntimeException('SQLSTATE[42S02]: sensitive table leak'));
        $output = (string)ob_get_clean();
        $decoded = json_decode($output, true);

        $this->assertSame(500, http_response_code());
        $this->assertIsArray($decoded);
        $this->assertStringNotContainsString('SQLSTATE', (string)($decoded['error'] ?? ''));
        $this->assertSame('An unexpected error occurred. Please try again.', $decoded['error'] ?? null);
    }
}
