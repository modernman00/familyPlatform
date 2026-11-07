<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\CoverPhotoService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Exceptions\ValidationException;

/**
 * CoverPhotoService: photo-only gate in front of the shared-lib FileUploader
 * (which on its own also accepts pdf/doc/video), and active-cover lookup.
 * Uses an in-memory SQLite `images` table — no dev DB writes.
 */
final class CoverPhotoServiceTest extends TestCase
{
    private PDO $pdo;
    private string $fileName;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec(
            "CREATE TABLE images (no INTEGER PRIMARY KEY AUTOINCREMENT, id TEXT NOT NULL, img TEXT,
             where_from TEXT NOT NULL, deleted_at TEXT NULL,
             cover_pos_x INTEGER NULL, cover_pos_y INTEGER NULL)"
        );
        $this->fileName = 'phpunit_cover_' . bin2hex(random_bytes(4)) . '.jpg';
        $_FILES = [];
    }

    protected function tearDown(): void
    {
        @unlink(CoverPhotoService::UPLOAD_DIR . $this->fileName);
        $_FILES = [];
    }

    private function fakeUpload(string $name, int $size = 1024): void
    {
        $_FILES[CoverPhotoService::FORM_INPUT] = [
            'name' => $name, 'type' => 'application/octet-stream', 'tmp_name' => '/nonexistent',
            'error' => UPLOAD_ERR_OK, 'size' => $size,
        ];
    }

    public function test_rejects_non_photo_extensions(): void
    {
        foreach (['report.pdf', 'clip.mp4', 'shell.php', 'doc.docx', 'anim.gif'] as $name) {
            $this->fakeUpload($name);
            try {
                (new CoverPhotoService($this->pdo))->upload();
                $this->fail("$name should have been rejected");
            } catch (ValidationException $e) {
                $this->assertStringContainsString('JPG, PNG, WebP or HEIC', $e->getMessage());
            }
        }
    }

    public function test_rejects_files_over_10mb(): void
    {
        $this->fakeUpload('big.jpg', 10 * 1024 * 1024 + 1);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('10 MB');
        (new CoverPhotoService($this->pdo))->upload();
    }

    public function test_rejects_missing_or_multi_file_input(): void
    {
        $this->expectException(ValidationException::class);
        (new CoverPhotoService($this->pdo))->upload();
    }

    public function test_current_url_is_newest_live_cover_and_remove_clears_it(): void
    {
        if (!is_dir(CoverPhotoService::UPLOAD_DIR)) {
            $this->markTestSkipped('profile upload dir not present');
        }
        file_put_contents(CoverPhotoService::UPLOAD_DIR . $this->fileName, 'x');

        $ins = $this->pdo->prepare('INSERT INTO images (id, img, where_from) VALUES (?, ?, ?)');
        $ins->execute(['U1', 'missing_old_cover.jpg', 'cover']);
        $ins->execute(['U1', $this->fileName, 'cover']);
        $ins->execute(['U1', 'avatar.jpg', 'profile']);
        $ins->execute(['U2', $this->fileName, 'cover']);

        $svc = new CoverPhotoService($this->pdo);
        $this->assertSame('/resources/images/profile/' . $this->fileName, $svc->getCurrentUrl('U1'));
        $this->assertNull($svc->getCurrentUrl('U3'));
        $this->assertNull($svc->getCurrentUrl(''));

        // remove() uses MySQL NOW(); emulate the soft delete for SQLite
        $this->pdo->exec("UPDATE images SET deleted_at = '2026-11-06' WHERE id = 'U1' AND where_from = 'cover'");
        $this->assertNull($svc->getCurrentUrl('U1'));
        $this->assertNotNull($svc->getCurrentUrl('U2'), 'other members are unaffected');
    }

    public function test_position_defaults_to_centre_then_saves_clamped_for_owner_only(): void
    {
        if (!is_dir(CoverPhotoService::UPLOAD_DIR)) {
            $this->markTestSkipped('profile upload dir not present');
        }
        file_put_contents(CoverPhotoService::UPLOAD_DIR . $this->fileName, 'x');
        $ins = $this->pdo->prepare('INSERT INTO images (id, img, where_from) VALUES (?, ?, ?)');
        $ins->execute(['U1', $this->fileName, 'cover']);
        $ins->execute(['U2', $this->fileName, 'cover']);

        $svc = new CoverPhotoService($this->pdo);
        $this->assertSame(['x' => 50, 'y' => 50], array_intersect_key($svc->getCurrent('U1') ?? [], ['x' => 1, 'y' => 1]));

        $svc->setPosition('U1', '500', -40);
        $cur = $svc->getCurrent('U1');
        $this->assertSame(100, $cur['x'] ?? null);
        $this->assertSame(0, $cur['y'] ?? null);
        $this->assertSame(50, $svc->getCurrent('U2')['y'] ?? null, 'other members are unaffected');
    }

    public function test_position_rejects_non_numeric_and_missing_cover(): void
    {
        $svc = new CoverPhotoService($this->pdo);
        try {
            $svc->setPosition('U1', 'abc', 10);
            $this->fail('non-numeric accepted');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Invalid', $e->getMessage());
        }
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Add a cover photo');
        $svc->setPosition('NOBODY', 10, 10);
    }
}
