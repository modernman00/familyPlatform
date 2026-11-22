<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\FamilyArcadeService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class FamilyArcadeTest extends TestCase
{
    private PDO $db;
    private string $testFamCode = 'TESTARCADE_FAM';
    private string $userA = 'arcade_test_user_a';
    private string $userB = 'arcade_test_user_b';

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Db::connect2();
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    private function cleanupTestData(): void
    {
        $s1 = $this->db->prepare('DELETE FROM family_arcade_scores WHERE fam_code = ?');
        $s1->execute([$this->testFamCode]);

        $s2 = $this->db->prepare('DELETE FROM family_arcade_badges WHERE fam_code = ?');
        $s2->execute([$this->testFamCode]);

        $s3 = $this->db->prepare('DELETE FROM family_arcade_races WHERE fam_code = ?');
        $s3->execute([$this->testFamCode]);

        $s4 = $this->db->prepare("DELETE FROM notification WHERE sender_id IN (?, ?) AND notification_type = 'arcade_overtake'");
        $s4->execute([$this->userA, $this->userB]);
    }

    public function testDeterministicPuzzleGenerationAcrossAllFourGames(): void
    {
        foreach (FamilyArcadeService::VALID_GAMES as $game) {
            $p1 = FamilyArcadeService::generatePuzzle($game, '2026-11-22', 'medium');
            $p2 = FamilyArcadeService::generatePuzzle($game, '2026-11-22', 'medium');
            $this->assertSame($p1, $p2, "Puzzle {$game} must be deterministic for the same seed.");
            $this->assertSame($game, $p1['game']);
        }
    }

    public function testKinZipServerAuthoritativeValidationRejectsCheatsAndAcceptsSolution(): void
    {
        $ref = new \ReflectionMethod(FamilyArcadeService::class, 'generateKinZipPuzzle');
        /** @var array<string, mixed> $fullPuzzle */
        $fullPuzzle = $ref->invoke(null, '2026-11-22', 'easy', true);
        $solutionPath = $fullPuzzle['solutionPath'];

        // Valid solution passes
        $ok = FamilyArcadeService::validateAndScore(
            'kinzip',
            '2026-11-22',
            'easy',
            ['path' => $solutionPath],
            25000,
            0,
            0
        );
        $this->assertTrue($ok['valid']);
        $this->assertGreaterThanOrEqual(500, $ok['score']);

        // Incomplete path rejected
        $badShort = FamilyArcadeService::validateAndScore(
            'kinzip',
            '2026-11-22',
            'easy',
            ['path' => array_slice($solutionPath, 0, 10)],
            12000,
            0,
            0
        );
        $this->assertFalse($badShort['valid']);
        $this->assertSame(0, $badShort['score']);

        // Reversed path (violates dot 1..K order) rejected
        $badReversed = FamilyArcadeService::validateAndScore(
            'kinzip',
            '2026-11-22',
            'easy',
            ['path' => array_reverse($solutionPath)],
            12000,
            0,
            0
        );
        $this->assertFalse($badReversed['valid']);
    }

    public function testKinGridServerAuthoritativeValidation(): void
    {
        $ref = new \ReflectionMethod(FamilyArcadeService::class, 'generateKinGridPuzzle');
        /** @var array<string, mixed> $fullPuzzle */
        $fullPuzzle = $ref->invoke(null, '2026-11-22', 'easy', true);
        $solutionGrid = $fullPuzzle['solutionGrid'];

        $ok = FamilyArcadeService::validateAndScore(
            'kingrid',
            '2026-11-22',
            'easy',
            ['grid' => $solutionGrid],
            30000,
            0,
            0
        );
        $this->assertTrue($ok['valid']);

        // Corrupt a cell to create duplicate in row
        $badGrid = $solutionGrid;
        $badGrid[0][0] = $badGrid[0][1];
        $bad = FamilyArcadeService::validateAndScore(
            'kingrid',
            '2026-11-22',
            'easy',
            ['grid' => $badGrid],
            30000,
            0,
            0
        );
        $this->assertFalse($bad['valid']);
    }

    public function testKinWordAndKinPulseServerValidation(): void
    {
        $refWord = new \ReflectionMethod(FamilyArcadeService::class, 'generateKinWordPuzzle');
        /** @var array<string, mixed> $wordPuzzle */
        $wordPuzzle = $refWord->invoke(null, '2026-11-22', 'medium', true);
        $groups = array_map(static fn (array $c): array => $c['words'], $wordPuzzle['categories']);

        $okWord = FamilyArcadeService::validateAndScore('kinword', '2026-11-22', 'medium', ['groups' => $groups], 40000, 0, 1);
        $this->assertTrue($okWord['valid']);

        $badWord = FamilyArcadeService::validateAndScore('kinword', '2026-11-22', 'medium', ['groups' => $groups], 40000, 0, 5);
        $this->assertFalse($badWord['valid']);

        $pulsePuzzle = FamilyArcadeService::generatePuzzle('kinpulse', '2026-11-22', 'easy');
        $okPulse = FamilyArcadeService::validateAndScore(
            'kinpulse',
            '2026-11-22',
            'easy',
            ['sequence' => $pulsePuzzle['sequence'], 'roundsCleared' => $pulsePuzzle['maxRounds']],
            18000,
            0,
            0
        );
        $this->assertTrue($okPulse['valid']);

        // Wordie validation test
        $wordiePuzzle = FamilyArcadeService::generatePuzzle('wordie', '2026-11-22', 'medium');
        $this->assertSame('wordie', $wordiePuzzle['game']);
        $this->assertSame(5, $wordiePuzzle['wordLength']);
        $this->assertArrayNotHasKey('targetWord', $wordiePuzzle);

        // Internal solve check
        $wordieInternal = (new \ReflectionClass(FamilyArcadeService::class))
            ->getMethod('generateWordiePuzzle')
            ->invoke(null, '2026-11-22', 'medium', true);
        $target = $wordieInternal['targetWord'];
        $okWordie = FamilyArcadeService::validateAndScore('wordie', '2026-11-22', 'medium', ['guesses' => ['CRANE', $target]], 25000, 0, 0);
        $this->assertTrue($okWordie['valid']);
        $failWordie = FamilyArcadeService::validateAndScore('wordie', '2026-11-22', 'medium', ['guesses' => ['WRONG']], 25000, 0, 0);
        $this->assertFalse($failWordie['valid']);

        // Cheeseboard validation test
        $cheeseInternal = (new \ReflectionClass(FamilyArcadeService::class))
            ->getMethod('generateCheeseboardPuzzle')
            ->invoke(null, '2026-11-22', 'easy', true);
        $this->assertCount(6, $cheeseInternal['solutionQueens']);
        $okCheese = FamilyArcadeService::validateAndScore('cheeseboard', '2026-11-22', 'easy', ['queens' => $cheeseInternal['solutionQueens']], 30000, 0, 0);
        $this->assertTrue($okCheese['valid']);

        // Minesweeper validation test
        $mineInternal = (new \ReflectionClass(FamilyArcadeService::class))
            ->getMethod('generateMinesweeperPuzzle')
            ->invoke(null, '2026-11-22', 'medium', true);
        $safeCells = [];
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                if (!isset($mineInternal['mineSet']["{$r},{$c}"])) {
                    $safeCells[] = ['r' => $r, 'c' => $c];
                }
            }
        }
        $this->assertSame(54, count($safeCells));
        $okMine = FamilyArcadeService::validateAndScore('minesweeper', '2026-11-22', 'medium', ['uncovered' => $safeCells], 40000, 0, 0);
        $this->assertTrue($okMine['valid']);
    }

    public function testLeagueTablesBadgesAndOvertakeAlert(): void
    {
        $ins = $this->db->prepare(
            'INSERT INTO family_arcade_scores
             (user_id, fam_code, game_slug, mode, puzzle_date, seed_key, difficulty, score, elapsed_ms, moves_count, hints_used, mistakes_count, replay_json, challenge_token)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        // User A sets initial record
        $ins->execute([$this->userA, $this->testFamCode, 'kinzip', 'daily', '2026-11-22', '2026-11-22', 'medium', 900, 40000, 36, 0, 0, '[]', 'tok_a_' . bin2hex(random_bytes(4))]);
        $resA = FamilyArcadeService::updateBadgesAndNotify($this->db, $this->testFamCode, $this->userA, 'Player A', 'kinzip', 900, 'tok_a');
        $this->assertTrue($resA['isNewRecord']);
        $this->assertContains('KinZip Grandmaster', $resA['awardedBadges']);

        // User B overtakes User A
        $ins->execute([$this->userB, $this->testFamCode, 'kinzip', 'daily', '2026-11-22', '2026-11-22', 'medium', 1250, 22000, 36, 0, 0, '[]', 'tok_b_' . bin2hex(random_bytes(4))]);
        $resB = FamilyArcadeService::updateBadgesAndNotify($this->db, $this->testFamCode, $this->userB, 'Player B', 'kinzip', 1250, 'tok_b');
        $this->assertTrue($resB['isNewRecord']);
        $this->assertSame($this->userA, $resB['overtookUserId']);

        // Verify League Table ordering & Family Cup
        $leagues = FamilyArcadeService::getLeagueTables($this->db, $this->testFamCode, $this->userB, false, 'weekly');
        $this->assertNotEmpty($leagues['games']['kinzip']);
        $this->assertSame($this->userB, $leagues['games']['kinzip'][0]['userId']);
        $this->assertSame($this->userB, $leagues['familyCup'][0]['userId']);

        // Verify badge lookup for Feed / Profile / Family Tree
        $badgeMap = FamilyArcadeService::getFamilyBadgesForUsers($this->db, $this->testFamCode, [$this->userB]);
        $this->assertArrayHasKey($this->userB, $badgeMap);
        $titles = array_column($badgeMap[$this->userB], 'badgeTitle');
        $this->assertContains('KinZip Grandmaster', $titles);
        $this->assertContains('Family Cup Champion', $titles);
    }
}
