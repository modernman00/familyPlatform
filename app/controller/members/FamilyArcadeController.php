<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\FamilyArcadeService;
use PDO;
use Src\{CorsHandler, Db, LaravelHelper, Limiter};
use Src\Realtime\SovereignBroadcaster;
use Throwable;

/**
 * FamilyArcadeController
 *
 * Member-authenticated controller for FamilyPlatform's Family Arcade:
 * KinZip, KinGrid, KinWord, KinPulse, League Tables, Family Cup, Live SSE Races,
 * Victory Replays, and "Beat My Score" Feed Sharing.
 */
final class FamilyArcadeController extends BaseController
{
    /**
     * GET /arcade
     */
    public function index(): void
    {
        $userId = (string) ($_SESSION['id'] ?? '');
        if ($userId === '') {
            header('Location: /login');
            exit;
        }

        $famCode = (string) ($_SESSION['famCode'] ?? '');
        $firstName = (string) ($_SESSION['firstName'] ?? '');
        $lastName = (string) ($_SESSION['lastName'] ?? '');
        $fullName = trim("{$firstName} {$lastName}") ?: $userId;

        $db = Db::connect2();
        $dailyDate = date('Y-m-d');
        $dailyGame = FamilyArcadeService::getDailyFeaturedGame($dailyDate);
        $requestedGame = isset($_GET['game']) && is_string($_GET['game'])
            ? FamilyArcadeService::normaliseGame($_GET['game'])
            : $dailyGame;
        $challengeToken = isset($_GET['challenge']) && is_string($_GET['challenge'])
            ? preg_replace('/[^a-fA-F0-9]/', '', $_GET['challenge']) ?? ''
            : '';
        $raceToken = isset($_GET['race']) && is_string($_GET['race'])
            ? preg_replace('/[^a-fA-F0-9]/', '', $_GET['race']) ?? ''
            : '';

        $champions = FamilyArcadeService::getFamilyChampions($db, $famCode);

        $bootstrap = [
            'userId' => $userId,
            'fullName' => $fullName,
            'firstName' => $firstName ?: $fullName,
            'famCode' => $famCode,
            'csrfToken' => LaravelHelper::csrfToken(),
            'dailyDate' => $dailyDate,
            'dailyGame' => $dailyGame,
            'initialGame' => $requestedGame,
            'challengeToken' => $challengeToken,
            'raceToken' => $raceToken,
            'champions' => $champions,
        ];

        view('member/arcade', [
            'bootstrap' => $bootstrap,
            'creativeTab' => 'arcade',
            'champions' => $champions,
        ]);
    }

    /**
     * GET /api/arcade/puzzle
     */
    public function getPuzzle(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string) ($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            $famCode = (string) ($_SESSION['famCode'] ?? '');
            if ($famCode === '' || !BaseController::sessionSharesFamily($famCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            Limiter::limit($userId . ':arcade_puzzle');
            \releaseSessionLock();

            $game = FamilyArcadeService::normaliseGame((string) ($_GET['game'] ?? 'kinzip'));
            $difficulty = FamilyArcadeService::normaliseDifficulty((string) ($_GET['difficulty'] ?? 'medium'));
            $mode = (string) ($_GET['mode'] ?? 'daily');
            if (!in_array($mode, FamilyArcadeService::VALID_MODES, true)) {
                $mode = 'daily';
            }

            $dailyDate = date('Y-m-d');
            $seedKey = isset($_GET['seed']) && is_string($_GET['seed']) && trim($_GET['seed']) !== ''
                ? preg_replace('/[^a-zA-Z0-9_-]/', '', trim($_GET['seed'])) ?? $dailyDate
                : ($mode === 'daily' ? $dailyDate : 'practice-' . bin2hex(random_bytes(4)));

            $challengeToken = isset($_GET['challenge']) && is_string($_GET['challenge'])
                ? preg_replace('/[^a-fA-F0-9]/', '', $_GET['challenge']) ?? ''
                : '';

            $db = Db::connect2();
            $ghost = null;

            if ($challengeToken !== '') {
                $cStmt = $db->prepare(
                    "SELECT s.user_id, s.game_slug, s.seed_key, s.difficulty, s.score, s.elapsed_ms, s.replay_json,
                            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(p.firstName, ''), ' ', COALESCE(p.lastName, ''))), ''), s.user_id) AS full_name
                     FROM family_arcade_scores s
                     LEFT JOIN personal p ON p.id = s.user_id
                     WHERE s.challenge_token = ? AND s.fam_code = ?
                     LIMIT 1"
                );
                $cStmt->execute([$challengeToken, $famCode]);
                $cRow = $cStmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($cRow)) {
                    $game = FamilyArcadeService::normaliseGame((string) $cRow['game_slug']);
                    $seedKey = (string) $cRow['seed_key'];
                    $difficulty = FamilyArcadeService::normaliseDifficulty((string) $cRow['difficulty']);
                    $ghost = [
                        'userId' => (string) $cRow['user_id'],
                        'fullName' => trim((string) $cRow['full_name']),
                        'score' => (int) $cRow['score'],
                        'elapsedMs' => (int) $cRow['elapsed_ms'],
                        'replay' => json_decode((string) ($cRow['replay_json'] ?? '[]'), true) ?: [],
                    ];
                }
            }

            // If no explicit challenge ghost, fetch the current family #1 ghost for this seed
            if ($ghost === null) {
                $gStmt = $db->prepare(
                    "SELECT s.user_id, s.score, s.elapsed_ms, s.replay_json,
                            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(p.firstName, ''), ' ', COALESCE(p.lastName, ''))), ''), s.user_id) AS full_name
                     FROM family_arcade_scores s
                     LEFT JOIN personal p ON p.id = s.user_id
                     WHERE s.fam_code = ? AND s.game_slug = ? AND s.seed_key = ?
                     ORDER BY s.score DESC, s.elapsed_ms ASC
                     LIMIT 1"
                );
                $gStmt->execute([$famCode, $game, $seedKey]);
                $gRow = $gStmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($gRow)) {
                    $ghost = [
                        'userId' => (string) $gRow['user_id'],
                        'fullName' => trim((string) $gRow['full_name']),
                        'score' => (int) $gRow['score'],
                        'elapsedMs' => (int) $gRow['elapsed_ms'],
                        'replay' => json_decode((string) ($gRow['replay_json'] ?? '[]'), true) ?: [],
                    ];
                }
            }

            // Personal best for this seed
            $pbStmt = $db->prepare(
                'SELECT MAX(score) AS best_score, MIN(elapsed_ms) AS best_ms
                 FROM family_arcade_scores
                 WHERE user_id = ? AND fam_code = ? AND game_slug = ? AND seed_key = ?'
            );
            $pbStmt->execute([$userId, $famCode, $game, $seedKey]);
            $pb = $pbStmt->fetch(PDO::FETCH_ASSOC);

            $puzzle = FamilyArcadeService::generatePuzzle($game, $seedKey, $difficulty);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'puzzle' => $puzzle,
                    'mode' => $mode,
                    'ghost' => $ghost,
                    'personalBest' => [
                        'score' => (int) ($pb['best_score'] ?? 0),
                        'elapsedMs' => (int) ($pb['best_ms'] ?? 0),
                    ],
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to load puzzle: ' . $e->getMessage()]);
        }
    }

    /**
     * POST /api/arcade/submit
     */
    public function submitScore(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string) ($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            /** @var array<string, mixed>|null $input */
            $input = json_decode((string) file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }

            self::validateCsrf($input);
            Limiter::limit($userId . ':arcade_submit');

            $famCode = (string) ($_SESSION['famCode'] ?? '');
            if ($famCode === '' || !BaseController::sessionSharesFamily($famCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            $game = FamilyArcadeService::normaliseGame((string) ($input['game'] ?? 'kinzip'));
            $difficulty = FamilyArcadeService::normaliseDifficulty((string) ($input['difficulty'] ?? 'medium'));
            $mode = (string) ($input['mode'] ?? 'daily');
            if (!in_array($mode, FamilyArcadeService::VALID_MODES, true)) {
                $mode = 'daily';
            }
            $seedKey = trim((string) ($input['seedKey'] ?? date('Y-m-d')));
            if ($seedKey === '' || strlen($seedKey) > 64) {
                $seedKey = date('Y-m-d');
            }

            $elapsedMs = max(500, min(3600000, (int) ($input['elapsedMs'] ?? 30000)));
            $movesCount = max(0, min(10000, (int) ($input['movesCount'] ?? 0)));
            $hintsUsed = max(0, min(20, (int) ($input['hintsUsed'] ?? 0)));
            $mistakesCount = max(0, min(20, (int) ($input['mistakesCount'] ?? 0)));
            /** @var array<string, mixed> $submission */
            $submission = is_array($input['submission'] ?? null) ? $input['submission'] : [];
            $replay = is_array($input['replay'] ?? null) ? array_slice($input['replay'], 0, 400) : [];

            $verification = FamilyArcadeService::validateAndScore(
                $game,
                $seedKey,
                $difficulty,
                $submission,
                $elapsedMs,
                $hintsUsed,
                $mistakesCount
            );

            if (!$verification['valid']) {
                http_response_code(422);
                echo json_encode([
                    'status' => 'error',
                    'message' => $verification['reason'],
                ]);
                return;
            }

            $score = $verification['score'];
            $challengeToken = bin2hex(random_bytes(16));
            $replayJson = json_encode($replay, JSON_UNESCAPED_SLASHES) ?: '[]';
            $puzzleDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $seedKey) ? $seedKey : date('Y-m-d');

            $db = Db::connect2();
            $ins = $db->prepare(
                'INSERT INTO family_arcade_scores
                 (user_id, fam_code, game_slug, mode, puzzle_date, seed_key, difficulty, score, elapsed_ms, moves_count, hints_used, mistakes_count, replay_json, challenge_token)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $ins->execute([
                $userId,
                $famCode,
                $game,
                $mode,
                $puzzleDate,
                $seedKey,
                $difficulty,
                $score,
                $elapsedMs,
                $movesCount,
                $hintsUsed,
                $mistakesCount,
                $replayJson,
                $challengeToken,
            ]);

            $playerName = trim((string) (($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? ''))) ?: $userId;
            $badgeResult = FamilyArcadeService::updateBadgesAndNotify(
                $db,
                $famCode,
                $userId,
                $playerName,
                $game,
                $score,
                $challengeToken
            );

            // Update live race room if raceToken provided
            $raceToken = trim((string) ($input['raceToken'] ?? ''));
            if ($raceToken !== '') {
                $rStmt = $db->prepare(
                    "UPDATE family_arcade_races
                     SET status = 'completed',
                         winner_user_id = COALESCE(winner_user_id, ?),
                         host_progress = CASE WHEN host_user_id = ? THEN 100 ELSE host_progress END,
                         opponent_progress = CASE WHEN host_user_id <> ? THEN 100 ELSE opponent_progress END
                     WHERE race_token = ? AND fam_code = ?"
                );
                $rStmt->execute([$userId, $userId, $userId, $raceToken, $famCode]);
            }

            $leagues = FamilyArcadeService::getLeagueTables($db, $famCode, $userId, false, 'weekly');

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'score' => $score,
                    'elapsedMs' => $elapsedMs,
                    'challengeToken' => $challengeToken,
                    'shareUrl' => '/arcade/share/' . $challengeToken,
                    'isNewRecord' => $badgeResult['isNewRecord'],
                    'awardedBadges' => $badgeResult['awardedBadges'],
                    'leagues' => $leagues,
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Score submission failed: ' . $e->getMessage()]);
        }
    }

    /**
     * GET /api/arcade/leagues
     */
    public function getLeagues(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string) ($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            $famCode = (string) ($_SESSION['famCode'] ?? '');
            if ($famCode === '' || !BaseController::sessionSharesFamily($famCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
                return;
            }

            \releaseSessionLock();

            $period = ((string) ($_GET['period'] ?? 'weekly')) === 'all_time' ? 'all_time' : 'weekly';
            $includeConnected = ((string) ($_GET['connected'] ?? '0')) === '1';

            $db = Db::connect2();
            $leagues = FamilyArcadeService::getLeagueTables($db, $famCode, $userId, $includeConnected, $period);
            $champions = FamilyArcadeService::getFamilyChampions($db, $famCode);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'leagues' => $leagues,
                    'champions' => $champions,
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to load league tables.']);
        }
    }

    /**
     * GET /api/arcade/daily-banner
     */
    public function getDailyBanner(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string) ($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            $famCode = (string) ($_SESSION['famCode'] ?? '');
            if ($famCode === '' || !BaseController::sessionSharesFamily($famCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
                return;
            }

            \releaseSessionLock();

            $today = date('Y-m-d');
            $featured = FamilyArcadeService::getDailyFeaturedGame($today);
            $db = Db::connect2();

            $topStmt = $db->prepare(
                "SELECT s.user_id, s.score, s.elapsed_ms,
                        COALESCE(NULLIF(TRIM(CONCAT(COALESCE(p.firstName, ''), ' ', COALESCE(p.lastName, ''))), ''), s.user_id) AS full_name
                 FROM family_arcade_scores s
                 LEFT JOIN personal p ON p.id = s.user_id
                 WHERE s.fam_code = ? AND s.game_slug = ? AND s.puzzle_date = ?
                 ORDER BY s.score DESC, s.elapsed_ms ASC
                 LIMIT 1"
            );
            $topStmt->execute([$famCode, $featured, $today]);
            $leader = $topStmt->fetch(PDO::FETCH_ASSOC);

            $myStmt = $db->prepare(
                'SELECT MAX(score) FROM family_arcade_scores
                 WHERE user_id = ? AND fam_code = ? AND game_slug = ? AND puzzle_date = ?'
            );
            $myStmt->execute([$userId, $famCode, $featured, $today]);
            $myBest = (int) ($myStmt->fetchColumn() ?: 0);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'date' => $today,
                    'featuredGame' => $featured,
                    'myBestScore' => $myBest,
                    'leader' => is_array($leader) ? [
                        'userId' => (string) $leader['user_id'],
                        'fullName' => trim((string) $leader['full_name']),
                        'score' => (int) $leader['score'],
                        'elapsedMs' => (int) $leader['elapsed_ms'],
                    ] : null,
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to load daily arcade banner.']);
        }
    }

    /**
     * POST /api/arcade/race/create
     */
    public function createRace(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string) ($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            /** @var array<string, mixed>|null $input */
            $input = json_decode((string) file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }
            self::validateCsrf($input);

            $famCode = (string) ($_SESSION['famCode'] ?? '');
            if ($famCode === '' || !BaseController::sessionSharesFamily($famCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
                return;
            }

            $game = FamilyArcadeService::normaliseGame((string) ($input['game'] ?? 'kinzip'));
            $seedKey = trim((string) ($input['seedKey'] ?? date('Y-m-d')));
            $raceToken = bin2hex(random_bytes(12));

            $db = Db::connect2();
            $stmt = $db->prepare(
                "INSERT INTO family_arcade_races (race_token, fam_code, game_slug, seed_key, host_user_id, status)
                 VALUES (?, ?, ?, ?, ?, 'active')"
            );
            $stmt->execute([$raceToken, $famCode, $game, $seedKey, $userId]);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'raceToken' => $raceToken,
                    'raceUrl' => "/arcade?game={$game}&race={$raceToken}",
                ],
            ], JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create race room.']);
        }
    }

    /**
     * POST /api/arcade/race/progress
     */
    public function updateRaceProgress(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string) ($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            /** @var array<string, mixed>|null $input */
            $input = json_decode((string) file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }
            self::validateCsrf($input);

            $famCode = (string) ($_SESSION['famCode'] ?? '');
            $raceToken = preg_replace('/[^a-fA-F0-9]/', '', (string) ($input['raceToken'] ?? '')) ?? '';
            $progress = max(0, min(100, (int) ($input['progress'] ?? 0)));

            if ($raceToken === '' || $famCode === '') {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Missing race token.']);
                return;
            }

            $db = Db::connect2();
            $stmt = $db->prepare(
                'UPDATE family_arcade_races
                 SET host_progress = CASE WHEN host_user_id = ? THEN ? ELSE host_progress END,
                     opponent_user_id = CASE WHEN host_user_id <> ? AND opponent_user_id IS NULL THEN ? ELSE opponent_user_id END,
                     opponent_progress = CASE WHEN host_user_id <> ? THEN ? ELSE opponent_progress END
                 WHERE race_token = ? AND fam_code = ?'
            );
            $stmt->execute([$userId, $progress, $userId, $userId, $userId, $progress, $raceToken, $famCode]);

            $famSlug = preg_replace('/[^a-zA-Z0-9_-]/', '', $famCode) ?: 'default';
            $firstName = trim((string) ($_SESSION['firstName'] ?? ''));
            $lastName = trim((string) ($_SESSION['lastName'] ?? ''));
            $fullName = trim("{$firstName} {$lastName}") ?: $userId;
            if (class_exists(SovereignBroadcaster::class)) {
                (new SovereignBroadcaster($db))->trigger(
                    "private-family-{$famSlug}",
                    'arcade.race_progress',
                    [
                        'raceToken' => $raceToken,
                        'userId' => $userId,
                        'fullName' => $fullName,
                        'progress' => $progress,
                    ]
                );
            }

            http_response_code(200);
            echo json_encode(['status' => 'success']);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update race progress.']);
        }
    }

    /**
     * POST /api/arcade/share-to-feed
     */
    public function shareToFeed(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string) ($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            /** @var array<string, mixed>|null $input */
            $input = json_decode((string) file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }
            self::validateCsrf($input);

            $famCode = (string) ($_SESSION['famCode'] ?? '');
            if ($famCode === '' || !BaseController::sessionSharesFamily($famCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
                return;
            }

            $challengeToken = preg_replace('/[^a-fA-F0-9]/', '', (string) ($input['challengeToken'] ?? '')) ?? '';
            if ($challengeToken === '') {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Missing challenge token.']);
                return;
            }

            $db = Db::connect2();
            $sStmt = $db->prepare(
                'SELECT game_slug, score, elapsed_ms, difficulty FROM family_arcade_scores
                 WHERE challenge_token = ? AND user_id = ? AND fam_code = ? LIMIT 1'
            );
            $sStmt->execute([$challengeToken, $userId, $famCode]);
            $scoreRow = $sStmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($scoreRow)) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Challenge score not found.']);
                return;
            }

            $gameLabel = match ((string) $scoreRow['game_slug']) {
                'kingrid' => 'KinGrid',
                'kinword' => 'KinWord',
                'kinpulse' => 'KinPulse',
                default => 'KinZip',
            };
            $secs = round(((int) $scoreRow['elapsed_ms']) / 1000, 1);
            $fullName = trim((string) (($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? ''))) ?: $userId;
            $profileImg = (string) ($_SESSION['profilePics'] ?? '');
            $shareUrl = '/arcade/share/' . $challengeToken;

            $message = "🏆 I just scored {$scoreRow['score']} pts in {$gameLabel} ({$secs}s)! Think you can beat my time? Accept my Family Arcade challenge: {$shareUrl}";

            $ins = $db->prepare(
                "INSERT INTO post (id, fullName, postMessage, postFamCode, target_scope, profileImg, post_time, post_status)
                 VALUES (?, ?, ?, ?, 'all', ?, ?, 'new')"
            );
            $ins->execute([
                $userId,
                $fullName,
                $message,
                $famCode,
                $profileImg,
                date('Y-m-d H:i:s'),
            ]);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Shared to the Family Feed!',
                'postNo' => (int) $db->lastInsertId(),
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to share to feed.']);
        }
    }
}
