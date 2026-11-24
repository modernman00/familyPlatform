<?php

declare(strict_types=1);

use App\services\TalkingAvatarNeuralService;
use PHPUnit\Framework\TestCase;

final class test_talking_avatar_neural_service extends TestCase
{
    private string $tmpDir;
    private string $vidDir;

    protected function setUp(): void
    {
        $base = sys_get_temp_dir() . '/fp_talking_avatar_test_' . bin2hex(random_bytes(4));
        $this->tmpDir = $base . '/jobs';
        $this->vidDir = $base . '/videos';
        mkdir($this->tmpDir, 0750, true);
        mkdir($this->vidDir, 0750, true);
    }

    protected function tearDown(): void
    {
        $base = dirname($this->tmpDir);
        foreach ([$this->tmpDir, $this->vidDir] as $dir) {
            if (is_dir($dir)) {
                foreach (glob($dir . '/*') ?: [] as $f) {
                    @unlink($f);
                }
                @rmdir($dir);
            }
        }
        @rmdir($base);
    }

    public function testEnqueueStagesManifestAndEnforcesOwnerScopedStatus(): void
    {
        $svc = new TalkingAvatarNeuralService($this->tmpDir, $this->vidDir);
        // 1x1 transparent PNG
        $tinyPng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $res = $svc->enqueueRender('user_alpha', [
            'image_data_url'    => $tinyPng,
            'script_text'       => 'Hello family!',
            'voice_id'          => 'bf_emma',
            'expression_preset' => 'smile',
        ]);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $res['job_id']);
        $this->assertContains($res['status'], ['queued', 'sidecar_offline', 'completed']);
        $this->assertIsBool($res['sidecar_online']);

        $ownerStatus = $svc->getJobStatus('user_alpha', $res['job_id']);
        $this->assertIsArray($ownerStatus);
        $this->assertSame('queued', $ownerStatus['status']);

        // Cross-user IDOR attempt must return null
        $this->assertNull($svc->getJobStatus('user_attacker', $res['job_id']));
        // Path traversal / malformed job ID must return null
        $this->assertNull($svc->getJobStatus('user_alpha', '../../etc/passwd'));
    }

    public function testGetVideoFilePathIsStrictlyOwnerScoped(): void
    {
        $svc = new TalkingAvatarNeuralService($this->tmpDir, $this->vidDir);
        $jobId = bin2hex(random_bytes(16));
        $file = $this->vidDir . '/user_alpha_' . $jobId . '.mp4';
        file_put_contents($file, 'fake-mp4');

        $this->assertSame($file, $svc->getVideoFilePath('user_alpha', $jobId));
        $this->assertNull($svc->getVideoFilePath('user_attacker', $jobId));
        $this->assertNull($svc->getVideoFilePath('user_alpha', 'invalid_id'));
    }

    public function testRejectsMissingScriptAndAudio(): void
    {
        $svc = new TalkingAvatarNeuralService($this->tmpDir, $this->vidDir);
        $this->expectException(InvalidArgumentException::class);
        $svc->enqueueRender('user_alpha', [
            'image_data_url' => 'data:image/png;base64,iVBORw0KGgo=',
            'script_text'    => '   ',
        ]);
    }
}
