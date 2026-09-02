<?php
declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\services\KinshipEngineService;
use Src\Select;

final class KinshipEngineTest extends TestCase
{
    private static string $userA = '999901USERA';
    private static string $userB = '999902USERB';

    public static function setUpBeforeClass(): void
    {
        $pdo = Select::connect2();

        // Setup User A with mother's maiden name ADELEKE
        $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (:id, 'Femi', 'Olaogun', 'OLAOGUN_CLAN')
            ON DUPLICATE KEY UPDATE firstName = 'Femi', lastName = 'Olaogun', famCode = 'OLAOGUN_CLAN'
        ")->execute([':id' => self::$userA]);

        $pdo->prepare("
            INSERT INTO otherFamily (id, maiden_name)
            VALUES (:id, 'ADELEKE')
            ON DUPLICATE KEY UPDATE maiden_name = 'ADELEKE'
        ")->execute([':id' => self::$userA]);

        // Setup User B whose surname is ADELEKE
        $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (:id, 'Bisi', 'ADELEKE', 'ADELEKE_FAM')
            ON DUPLICATE KEY UPDATE firstName = 'Bisi', lastName = 'ADELEKE', famCode = 'ADELEKE_FAM'
        ")->execute([':id' => self::$userB]);
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = Select::connect2();
        $pdo->prepare("DELETE FROM kinship_dismissed WHERE user_id = :u OR dismissed_user_id = :u2")->execute([':u' => self::$userA, ':u2' => self::$userB]);
        $pdo->prepare("DELETE FROM otherFamily WHERE id IN (:u1, :u2)")->execute([':u1' => self::$userA, ':u2' => self::$userB]);
        $pdo->prepare("DELETE FROM personal WHERE id IN (:u1, :u2)")->execute([':u1' => self::$userA, ':u2' => self::$userB]);
    }

    public function testCanDetectMaternalMaidenKinship(): void
    {
        $suggestions = KinshipEngineService::getSuggestedKin(self::$userA, 10);
        $this->assertIsArray($suggestions);

        $matchedUserB = null;
        foreach ($suggestions as $s) {
            if ($s['user_id'] === self::$userB) {
                $matchedUserB = $s;
                break;
            }
        }

        $this->assertNotNull($matchedUserB, 'User B with surname ADELEKE must be suggested to User A with mother maiden name ADELEKE');
        $this->assertGreaterThanOrEqual(65, $matchedUserB['confidence_score']);
        $this->assertStringContainsString('ADELEKE', $matchedUserB['primary_reason']);
    }

    public function testCanDismissSuggestedKin(): void
    {
        // Dismiss User B
        $dismissed = KinshipEngineService::dismissSuggestion(self::$userA, self::$userB);
        $this->assertTrue($dismissed);

        // Fetch suggestions again - User B must no longer appear
        $suggestionsAfter = KinshipEngineService::getSuggestedKin(self::$userA, 10);
        $found = false;
        foreach ($suggestionsAfter as $s) {
            if ($s['user_id'] === self::$userB) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Dismissed user must be excluded from future suggestions');
    }
}
