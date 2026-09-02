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
    private static string $userC = '999903USERC';
    private static string $userD = '999904USERD';
    private static string $userE = '999905USERE';

    public static function setUpBeforeClass(): void
    {
        $pdo = Select::connect2();

        // Setup User A with mother's maiden name ADELEKETEST99
        $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (:id, 'Femi', 'TESTSURNAMEA', 'TESTCLANA')
            ON DUPLICATE KEY UPDATE firstName = 'Femi', lastName = 'TESTSURNAMEA', famCode = 'TESTCLANA'
        ")->execute([':id' => self::$userA]);

        $pdo->prepare("
            INSERT INTO otherFamily (id, maiden_name)
            VALUES (:id, 'ADELEKETEST99')
            ON DUPLICATE KEY UPDATE maiden_name = 'ADELEKETEST99'
        ")->execute([':id' => self::$userA]);

        // Setup User B whose surname is ADELEKETEST99
        $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (:id, 'Bisi', 'ADELEKETEST99', 'TESTCLANB')
            ON DUPLICATE KEY UPDATE firstName = 'Bisi', lastName = 'ADELEKETEST99', famCode = 'TESTCLANB'
        ")->execute([':id' => self::$userB]);

        // Setup User C (Mutual Friend 1)
        $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (:id, 'Dayo', 'Williams', 'WILLIAMS_FAM')
            ON DUPLICATE KEY UPDATE firstName = 'Dayo', lastName = 'Williams', famCode = 'WILLIAMS_FAM'
        ")->execute([':id' => self::$userC]);

        // Setup User E (Mutual Friend 2)
        $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (:id, 'Sola', 'Ogundele', 'OGUNDELE_FAM')
            ON DUPLICATE KEY UPDATE firstName = 'Sola', lastName = 'Ogundele', famCode = 'OGUNDELE_FAM'
        ")->execute([':id' => self::$userE]);

        // Setup User D (Candidate connected to User C and User E)
        $pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (:id, 'Kunle', 'Balogun', 'BALOGUN_FAM')
            ON DUPLICATE KEY UPDATE firstName = 'Kunle', lastName = 'Balogun', famCode = 'BALOGUN_FAM'
        ")->execute([':id' => self::$userD]);

        // Connect User A <-> User C (approved)
        $pdo->prepare("
            INSERT INTO requestMgt (requester_id, approver_id, status)
            VALUES (:u1, :u2, 'approved')
        ")->execute([':u1' => self::$userA, ':u2' => self::$userC]);

        // Connect User A <-> User E (approved)
        $pdo->prepare("
            INSERT INTO requestMgt (requester_id, approver_id, status)
            VALUES (:u1, :u2, 'approved')
        ")->execute([':u1' => self::$userA, ':u2' => self::$userE]);

        // Connect User C <-> User D (approved)
        $pdo->prepare("
            INSERT INTO requestMgt (requester_id, approver_id, status)
            VALUES (:u1, :u2, 'approved')
        ")->execute([':u1' => self::$userC, ':u2' => self::$userD]);

        // Connect User E <-> User D (approved)
        $pdo->prepare("
            INSERT INTO requestMgt (requester_id, approver_id, status)
            VALUES (:u1, :u2, 'approved')
        ")->execute([':u1' => self::$userE, ':u2' => self::$userD]);
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = Select::connect2();
        $ids = [self::$userA, self::$userB, self::$userC, self::$userD, self::$userE];
        $inClause = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("DELETE FROM requestMgt WHERE requester_id IN ({$inClause}) OR approver_id IN ({$inClause})");
        $stmt->execute(array_merge($ids, $ids));

        $stmt = $pdo->prepare("DELETE FROM kinship_dismissed WHERE user_id IN ({$inClause}) OR dismissed_user_id IN ({$inClause})");
        $stmt->execute(array_merge($ids, $ids));

        $stmt = $pdo->prepare("DELETE FROM otherFamily WHERE id IN ({$inClause})");
        $stmt->execute($ids);

        $stmt = $pdo->prepare("DELETE FROM personal WHERE id IN ({$inClause})");
        $stmt->execute($ids);
    }

    public function testCanDetectMaternalMaidenKinship(): void
    {
        $suggestions = KinshipEngineService::getSuggestedKin(self::$userA, 10);
        $this->assertNotEmpty($suggestions);

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

    public function testCanDetectMutualKinWithoutParameterCollision(): void
    {
        // When User A has kin (User C), Signal F and batchMutualKinCounts must execute cleanly without PDO HY093 error
        $suggestions = KinshipEngineService::getSuggestedKin(self::$userA, 10);
        $this->assertNotEmpty($suggestions);

        $matchedUserD = null;
        foreach ($suggestions as $s) {
            if ($s['user_id'] === self::$userD) {
                $matchedUserD = $s;
                break;
            }
        }

        $this->assertNotNull($matchedUserD, 'User D connected to mutual kin User C must be detected via Signal F');
        $this->assertStringContainsString('mutual family connection', implode(' ', $matchedUserD['reasons']));
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
