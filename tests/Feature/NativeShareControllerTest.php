<?php

declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../Support/SocialFeedTestCase.php';

use App\controller\members\NativeShareController;
use Tests\Support\SocialFeedTestCase;

class NativeShareControllerTest extends SocialFeedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed personal record for authorId and famCode
        $stmt = $this->pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
        $stmt->execute([$this->authorId, 'Adebisi', 'Johnson', $this->famCode]);
    }

    protected function tearDown(): void
    {
        // Clean up test personal row and post rows
        $stmt = $this->pdo->prepare("DELETE FROM personal WHERE id = ?");
        $stmt->execute([$this->authorId]);

        $stmt = $this->pdo->prepare("DELETE FROM post WHERE id = ?");
        $stmt->execute([$this->authorId]);

        parent::tearDown();
    }

    public function test_native_share_rejects_unauthenticated_request(): void
    {
        unset($_SESSION['id'], $_SESSION['famCode']);
        unset($_SERVER['HTTP_X_FAMILY_CODE'], $_POST['famCode']);

        ob_start();
        (new NativeShareController())->share();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertSame('error', $data['status']);
        $this->assertSame(401, http_response_code());
    }

    public function test_native_share_successfully_posts_photo_to_family_timeline(): void
    {
        $tmpImage = tempnam(sys_get_temp_dir(), 'test_fam_img_');
        $im = imagecreatetruecolor(60, 60);
        imagejpeg($im, $tmpImage);

        $_SESSION['id'] = $this->authorId;
        $_SESSION['famCode'] = $this->famCode;
        $_POST['caption'] = 'Family Sunday Roast! #SundayGathering #FamilyVibes';
        $_FILES['photo'] = [
            'name'     => 'family_photo.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => $tmpImage,
            'error'    => UPLOAD_ERR_OK,
            'size'     => (int) filesize($tmpImage),
        ];

        ob_start();
        (new NativeShareController())->share();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertSame('success', $data['status']);
        $this->assertSame(200, http_response_code());
        $this->assertGreaterThan(0, $data['post_no']);
        $this->assertSame($this->famCode, $data['fam_code']);

        // Verify post row created in DB
        $stmt = $this->pdo->prepare("SELECT * FROM post WHERE post_no = ?");
        $stmt->execute([$data['post_no']]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
        $this->assertSame($this->authorId, $row['id']);
        $this->assertSame('Family Sunday Roast! #SundayGathering #FamilyVibes', $row['postMessage']);
        $this->assertNotEmpty($row['post_img0']);

        unset($_POST['caption'], $_FILES['photo']);
        if (file_exists($tmpImage)) {
            unlink($tmpImage);
        }
    }

    public function test_native_share_rejects_malicious_script_file(): void
    {
        $tmpFake = tempnam(sys_get_temp_dir(), 'fake_fam_');
        file_put_contents($tmpFake, '<?php echo "injected shell"; ?>');

        $_SESSION['id'] = $this->authorId;
        $_SESSION['famCode'] = $this->famCode;
        $_FILES['photo'] = [
            'name'     => 'shell.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => $tmpFake,
            'error'    => UPLOAD_ERR_OK,
            'size'     => (int) filesize($tmpFake),
        ];

        ob_start();
        (new NativeShareController())->share();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertSame('error', $data['status']);
        $this->assertSame(422, http_response_code());

        unset($_FILES['photo']);
        if (file_exists($tmpFake)) {
            unlink($tmpFake);
        }
    }

    public function test_native_share_serves_shortcut_config(): void
    {
        $_GET['fam_code'] = $this->famCode;

        ob_start();
        (new NativeShareController())->shortcutConfig();
        $jsonOutput = ob_get_clean();

        $data = json_decode($jsonOutput, true);
        $this->assertIsArray($data);
        $this->assertSame('success', $data['status']);
        $this->assertSame($this->famCode, $data['fam_code']);
        $this->assertStringContainsString('/api/media/native-share', $data['upload_url']);

        $_GET['format'] = 'shortcut';
        ob_start();
        (new NativeShareController())->shortcutConfig();
        $plistOutput = ob_get_clean();

        $this->assertStringContainsString('<!DOCTYPE plist PUBLIC', $plistOutput);
        $this->assertStringContainsString('FamilyPlatform Native Share Shortcut', $plistOutput);

        unset($_GET['fam_code'], $_GET['format']);
    }
}
