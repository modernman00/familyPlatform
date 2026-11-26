<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\PostShareController;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class PostShareCardRenditionTest extends TestCase
{
    private PDO $db;
    /** @var list<int> */
    private array $createdPostNos = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Db::connect2();
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdPostNos)) {
            $stmt = $this->db->prepare('DELETE FROM post WHERE post_no = ?');
            foreach ($this->createdPostNos as $pNo) {
                $stmt->execute([$pNo]);
            }
        }
        parent::tearDown();
    }

    private function createTestPost(string $authorId, string $message, ?string $img, bool $allowSharing): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, post_img0, media_meta, date_created) ' .
            'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $authorId,
            'Test Author',
            '',
            $message,
            'TESTFAM',
            '12:00',
            'active',
            $img ?? '',
            json_encode(['allow_sharing' => $allowSharing]),
        ]);
        $postNo = (int)$this->db->lastInsertId();
        $this->createdPostNos[] = $postNo;
        return $postNo;
    }

    public function test_post_with_image_renders_designed_card_under_300kb(): void
    {
        // SAM_0795.JPG exists in project resources
        $postNo = $this->createTestPost('author_img_test', 'A beautiful day with family', 'SAM_0795.JPG', true);

        $controller = new PostShareController();
        ob_start();
        $controller->cardImage($postNo);
        $cardJpg = (string)ob_get_clean();

        $this->assertGreaterThan(1000, strlen($cardJpg));
        $this->assertLessThan(307200, strlen($cardJpg), 'Card must be under 300KB for WhatsApp');
    }

    public function test_unshareable_post_serves_fanciful_backup_card_without_ugly_logo(): void
    {
        $postNo = $this->createTestPost('author_priv_test', 'Private secret memory', 'SAM_0795.JPG', false);

        $controller = new PostShareController();

        // 1. Crawler HTML check
        $_SERVER['HTTP_USER_AGENT'] = 'WhatsApp/2.21.12.21 i';
        $_GET['preview'] = '1';
        ob_start();
        $controller->handlePostLink($postNo);
        $html = (string)ob_get_clean();

        $this->assertStringContainsString('og-invite.jpg', $html);
        $this->assertStringNotContainsString('android-chrome-512x512.png', $html);
        $this->assertStringNotContainsString('Private secret memory', $html);

        // 2. Direct card image check fails safe to fanciful backup image
        ob_start();
        $controller->cardImage($postNo);
        $img = (string)ob_get_clean();

        $backupPath = dirname(__DIR__, 2) . '/public/img/og-invite.jpg';
        $this->assertSame(filesize($backupPath), strlen($img));
    }

    public function test_missing_post_card_falls_back_to_fanciful_backup_card(): void
    {
        $controller = new PostShareController();
        ob_start();
        $controller->cardImage(999999999);
        $img = (string)ob_get_clean();

        $backupPath = dirname(__DIR__, 2) . '/public/img/og-invite.jpg';
        $this->assertSame(filesize($backupPath), strlen($img));
    }

    public function test_author_with_restrictions_still_serves_designed_post_card_when_shareable(): void
    {
        // When author has restrictions on someone, their own shared post must still link to card.jpg
        $postNo = $this->createTestPost('318422WALLY', 'My Smart Beautiful Jummy', 'SAM_0795.JPG', true);

        $controller = new PostShareController();
        $_SERVER['HTTP_USER_AGENT'] = 'WhatsApp/2.21.12.21 i';
        $_GET['preview'] = '1';
        ob_start();
        $controller->handlePostLink($postNo);
        $html = (string)ob_get_clean();

        $this->assertStringContainsString('/post/' . $postNo . '/card.jpg', $html);
        $this->assertStringNotContainsString('android-chrome-512x512.png', $html);
        $this->assertStringContainsString('My Smart Beautiful Jummy', $html);
    }
}
