<?php
declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\model\Reel;
use Src\Select;

final class ReelsTest extends TestCase
{
    private static string $testUserId = '999999TESTUSER';
    private static string $testFamCode = 'TESTFAM123';
    private static ?int $createdReelId = null;

    public static function setUpBeforeClass(): void
    {
        // Ensure test user exists in personal table
        $pdo = Select::connect2();
        $stmt = $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (:id, 'Test', 'ReelsUser', :famCode)
            ON DUPLICATE KEY UPDATE firstName = 'Test', lastName = 'ReelsUser', famCode = :famCode2
        ");
        $stmt->execute([
            ':id' => self::$testUserId,
            ':famCode' => self::$testFamCode,
            ':famCode2' => self::$testFamCode
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = Select::connect2();
        if (self::$createdReelId !== null) {
            $pdo->prepare("DELETE FROM family_reel_comments WHERE reel_id = :id")->execute([':id' => self::$createdReelId]);
            $pdo->prepare("DELETE FROM family_reel_reactions WHERE reel_id = :id")->execute([':id' => self::$createdReelId]);
            $pdo->prepare("DELETE FROM family_reels WHERE id = :id")->execute([':id' => self::$createdReelId]);
        }
        $pdo->prepare("DELETE FROM personal WHERE id = :id")->execute([':id' => self::$testUserId]);
    }

    public function testCanCreateReel(): void
    {
        $reelId = Reel::createReel([
            'user_id' => self::$testUserId,
            'famCode' => self::$testFamCode,
            'caption' => 'Automated PHPUnit Test Reel',
            'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
            'aspect_ratio' => '9:16',
            'category' => 'milestone',
            'music_title' => 'Test Track'
        ]);

        self::$createdReelId = $reelId;
        $this->assertIsInt($reelId);
        $this->assertGreaterThan(0, $reelId);
    }

    /**
     * @depends testCanCreateReel
     */
    public function testCanRetrieveReelById(): void
    {
        $this->assertNotNull(self::$createdReelId);
        $reel = Reel::getReelById(self::$createdReelId, self::$testUserId);

        $this->assertIsArray($reel);
        $this->assertSame(self::$testUserId, (string)$reel['user_id']);
        $this->assertSame('Automated PHPUnit Test Reel', $reel['caption']);
        $this->assertSame('Test Track', $reel['music_title']);
    }

    /**
     * @depends testCanCreateReel
     */
    public function testCanToggleReaction(): void
    {
        $this->assertNotNull(self::$createdReelId);

        // 1. Add Like
        $res1 = Reel::toggleReaction(self::$createdReelId, self::$testUserId, 'like');
        $this->assertSame('success', $res1['status']);
        $this->assertSame('added', $res1['action']);
        $this->assertSame(1, $res1['count']);

        // 2. Remove Like (Unlike)
        $res2 = Reel::toggleReaction(self::$createdReelId, self::$testUserId, 'like');
        $this->assertSame('success', $res2['status']);
        $this->assertSame('removed', $res2['action']);
        $this->assertSame(0, $res2['count']);
    }

    /**
     * @depends testCanCreateReel
     */
    public function testCanAddAndGetComments(): void
    {
        $this->assertNotNull(self::$createdReelId);

        $comment = Reel::addComment(self::$createdReelId, self::$testUserId, 'Great family milestone!');
        $this->assertIsArray($comment);
        $this->assertSame('Great family milestone!', $comment['comment']);

        $commentsList = Reel::getComments(self::$createdReelId);
        $this->assertIsArray($commentsList);
        $this->assertCount(1, $commentsList);
        $this->assertSame('Great family milestone!', $commentsList[0]['comment']);
    }

    /**
     * @depends testCanCreateReel
     */
    public function testCanFetchFeedForFamily(): void
    {
        $feed = Reel::getReelsFeed(self::$testUserId, self::$testFamCode, 10, 0);
        $this->assertIsArray($feed);
        $this->assertNotEmpty($feed);

        $found = false;
        foreach ($feed as $item) {
            if ((int)$item['id'] === self::$createdReelId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created reel must appear in the family feed');
    }
}
