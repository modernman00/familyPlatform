<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\FamilyRecommendationService;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class RecommendationControllerTest extends TestCase
{
    private static string $inviterId = '777101INVITER';
    private static string $inviteeId = '777102INVITEE';
    private static string $inviterCode = 'INV777';
    private static string $inviteeCode = 'NWN888';

    public static function setUpBeforeClass(): void
    {
        $pdo = Db::connect2();

        // Create inviter user
        $stmt = $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (?, 'Kemi', 'Adeyemi', ?)
            ON DUPLICATE KEY UPDATE firstName = 'Kemi', lastName = 'Adeyemi', famCode = ?
        ");
        $stmt->execute([self::$inviterId, self::$inviterCode, self::$inviterCode]);

        // Clean up any stale recommendation rows
        $pdo->prepare("DELETE FROM platform_recommendations WHERE inviter_user_id = ? OR invitee_user_id = ?")
            ->execute([self::$inviterId, self::$inviteeId]);
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = Db::connect2();
        $pdo->prepare("DELETE FROM platform_recommendations WHERE inviter_user_id = ? OR invitee_user_id = ?")
            ->execute([self::$inviterId, self::$inviteeId]);
        $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?)")
            ->execute([self::$inviterId, self::$inviteeId]);
    }

    public function test_can_record_recommendation_and_fetch_stats(): void
    {
        $token = FamilyRecommendationService::generateSignedReferralToken(self::$inviterId, 'Kemi Adeyemi');

        // Initial count should be 0
        $statsInitial = FamilyRecommendationService::getRecommendationStats(self::$inviterId);
        $this->assertSame(0, $statsInitial['total_spawned']);

        // Record successful recommendation
        $recorded = FamilyRecommendationService::recordSuccessfulRecommendation(
            self::$inviterId,
            self::$inviteeId,
            self::$inviteeCode,
            $token
        );
        $this->assertTrue($recorded, 'Recommendation record must succeed in database');

        // Stats should reflect 1 spawned network
        $statsAfter = FamilyRecommendationService::getRecommendationStats(self::$inviterId);
        $this->assertSame(1, $statsAfter['total_spawned']);
    }

    public function test_isolated_family_networks_preserve_walled_sanctuary(): void
    {
        // Assert inviter code and invitee code are separate (No tree crossover)
        $this->assertNotEquals(self::$inviterCode, self::$inviteeCode);
        
        $pdo = Db::connect2();
        $stmt = $pdo->prepare('SELECT new_family_code FROM platform_recommendations WHERE inviter_user_id = ? LIMIT 1');
        $stmt->execute([self::$inviterId]);
        $spawnedCode = $stmt->fetchColumn();

        $this->assertSame(self::$inviteeCode, $spawnedCode);
    }
}
