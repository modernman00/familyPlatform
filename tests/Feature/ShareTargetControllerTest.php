<?php

declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../Support/SocialFeedTestCase.php';

use App\controller\members\ShareTargetController;
use Tests\Support\SocialFeedTestCase;

class ShareTargetControllerTest extends SocialFeedTestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = realpath(__DIR__ . '/../../resources/images/post') . '/temp';
        if (!is_dir($this->tempDir)) {
            @mkdir($this->tempDir, 0755, true);
        }
        $cacheDir = realpath(__DIR__ . '/../../storage/cache') . '/share_tickets';
        if (is_dir($cacheDir)) {
            foreach (glob($cacheDir . '/*.json') ?: [] as $f) {
                @unlink($f);
            }
        }
        unset($_SESSION['pwa_shared_target']);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['pwa_shared_target'], $_POST['title'], $_POST['text'], $_POST['url'], $_FILES['media'], $_GET['ticket']);
        $cacheDir = realpath(__DIR__ . '/../../storage/cache') . '/share_tickets';
        if (is_dir($cacheDir)) {
            foreach (glob($cacheDir . '/*.json') ?: [] as $f) {
                @unlink($f);
            }
        }
        parent::tearDown();
    }

    public function test_share_target_receives_media_and_sets_session(): void
    {
        $tmpImage = tempnam(sys_get_temp_dir(), 'test_pwa_img_');
        $im = imagecreatetruecolor(50, 50);
        imagejpeg($im, $tmpImage);

        $_POST['text'] = 'Family vacation memories!';
        $_FILES['media'] = [
            'name'     => 'family_vacation.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => $tmpImage,
            'error'    => UPLOAD_ERR_OK,
            'size'     => (int) filesize($tmpImage),
        ];

        ob_start();
        (new ShareTargetController())->receive();
        ob_get_clean();

        $this->assertArrayHasKey('pwa_shared_target', $_SESSION);
        $this->assertSame('Family vacation memories!', $_SESSION['pwa_shared_target']['caption']);
        $this->assertNotEmpty($_SESSION['pwa_shared_target']['files']);

        $savedFileName = $_SESSION['pwa_shared_target']['files'][0];
        $destPath = $this->tempDir . '/' . $savedFileName;
        $this->assertFileExists($destPath);

        // Clean up
        if (file_exists($destPath)) {
            @unlink($destPath);
        }
        if (file_exists($tmpImage)) {
            @unlink($tmpImage);
        }
    }

    public function test_share_target_rejects_malicious_executable(): void
    {
        $tmpShell = tempnam(sys_get_temp_dir(), 'test_shell_');
        file_put_contents($tmpShell, '<?php phpinfo(); ?>');

        $_POST['text'] = 'Hacked';
        $_FILES['media'] = [
            'name'     => 'exploit.php',
            'type'     => 'application/x-php',
            'tmp_name' => $tmpShell,
            'error'    => UPLOAD_ERR_OK,
            'size'     => (int) filesize($tmpShell),
        ];

        ob_start();
        (new ShareTargetController())->receive();
        ob_get_clean();

        // Should not save any files
        $this->assertTrue(empty($_SESSION['pwa_shared_target']['files']));

        if (file_exists($tmpShell)) {
            @unlink($tmpShell);
        }
    }

    public function test_get_shared_payload_endpoint_returns_data_and_clears_session(): void
    {
        $_SESSION['pwa_shared_target'] = [
            'files'   => ['test_image.jpg'],
            'caption' => 'Sunday picnic',
            'time'    => time(),
        ];

        ob_start();
        (new ShareTargetController())->getSharedPayload();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $json = json_decode($output, true);
        $this->assertSame('success', $json['status']);
        $this->assertSame('Sunday picnic', $json['data']['caption']);
        $this->assertSame(['test_image.jpg'], $json['data']['files']);

        // Session should be cleared after retrieval to prevent duplicate composer triggers
        $this->assertArrayNotHasKey('pwa_shared_target', $_SESSION);
    }

    public function test_get_shared_payload_with_ticket_returns_staged_media_and_consumes_ticket(): void
    {
        $tmpImage = tempnam(sys_get_temp_dir(), 'test_ticket_img_');
        $im = imagecreatetruecolor(40, 40);
        imagejpeg($im, $tmpImage);

        $_POST['text'] = 'Android Share Ticket Test';
        $_FILES['media'] = [
            'name'     => 'ticket_test.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => $tmpImage,
            'error'    => UPLOAD_ERR_OK,
            'size'     => (int) filesize($tmpImage),
        ];

        // Clear session to simulate cross-site SameSite=Lax cookie-free POST
        unset($_SESSION['pwa_shared_target'], $_SESSION['id']);

        ob_start();
        (new ShareTargetController())->receive();
        ob_get_clean();

        // Locate created ticket file in storage/cache/share_tickets
        $cacheDir = realpath(__DIR__ . '/../../storage/cache') . '/share_tickets';
        $files = glob($cacheDir . '/skt_*.json');
        $this->assertNotEmpty($files, 'Ticket file should be created in cache');

        $latestTicketFile = end($files);
        $ticket = basename($latestTicketFile, '.json');

        $_GET['ticket'] = $ticket;
        $_SESSION['id'] = 'test_user_1';
        ob_start();
        (new ShareTargetController())->getPendingShare();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $json = json_decode($output, true);
        $this->assertSame('success', $json['status']);
        $this->assertSame('Android Share Ticket Test', $json['data']['caption']);
        $this->assertNotEmpty($json['data']['files']);

        // Ticket must be consumed and deleted (single-use)
        $this->assertFileDoesNotExist($latestTicketFile);

        // Subsequent query with same ticket must return null
        ob_start();
        (new ShareTargetController())->getPendingShare();
        $output2 = ob_get_clean();
        $json2 = json_decode($output2, true);
        $this->assertNull($json2['data']);

        unset($_GET['ticket']);
        if (file_exists($tmpImage)) {
            @unlink($tmpImage);
        }
    }
}
