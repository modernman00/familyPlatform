<?php

declare(strict_types=1);

namespace App\controller;

use App\services\FamilyArcadeService;
use App\services\ShareMetadataProtocol;
use PDO;
use Src\CorsHandler;
use Src\Db;
use Src\Limiter;

/**
 * PublicArcadeController
 *
 * Facebook-standard "Play-First, Sign-Up-After" public guest challenge controller.
 * Allows outsiders to play shared Family Arcade puzzles without logging in,
 * compares their verified score against the challenger's ghost, and tracks
 * full-funnel engagement in `family_arcade_engagements` with zero PII.
 */
final class PublicArcadeController
{
    private const VALID_EVENTS = ['view', 'start', 'how_to_play', 'complete', 'signup_click'];

    /**
     * GET /arcade/challenge/{token}
     */
    public function challenge(string $token = ''): void
    {
        $cleanToken = preg_replace('/[^a-fA-F0-9]/', '', trim($token)) ?? '';

        // Authenticated members go straight to their full Family Arcade workspace
        if (!empty($_SESSION['id'])) {
            $qs = $cleanToken !== '' ? ('?challenge=' . rawurlencode($cleanToken)) : '';
            redirect('/arcade' . $qs);
            return;
        }

        $today = date('Y-m-d');
        $requestedGame = isset($_GET['game']) ? FamilyArcadeService::normaliseGame((string) $_GET['game']) : '';
        $gameSlug = $requestedGame !== '' ? $requestedGame : FamilyArcadeService::getDailyFeaturedGame($today);
        $difficulty = isset($_GET['difficulty'])
            ? FamilyArcadeService::normaliseDifficulty((string) $_GET['difficulty'])
            : 'medium';
        $seedKey = $today;
        $challenger = null;

        $db = null;
        try {
            $db = Db::connect2();
            if ($cleanToken !== '') {
                $stmt = $db->prepare(
                    'SELECT s.game_slug, s.difficulty, s.seed_key, s.score, s.elapsed_ms, p.firstName
                     FROM family_arcade_scores s
                     LEFT JOIN personal p ON p.id = s.user_id
                     WHERE s.challenge_token = ?
                     LIMIT 1'
                );
                $stmt->execute([$cleanToken]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($row)) {
                    $gameSlug = FamilyArcadeService::normaliseGame((string) ($row['game_slug'] ?? $gameSlug));
                    $difficulty = FamilyArcadeService::normaliseDifficulty((string) ($row['difficulty'] ?? 'medium'));
                    $seedKey = (string) ($row['seed_key'] ?? $today);
                    $firstName = trim((string) ($row['firstName'] ?? 'A Family Challenger'));
                    if ($firstName === '') {
                        $firstName = 'A Family Challenger';
                    }
                    $challenger = [
                        'name' => $firstName,
                        'firstName' => $firstName,
                        'score' => (int) ($row['score'] ?? 0),
                        'elapsedMs' => (int) ($row['elapsed_ms'] ?? 0),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Fall back safely to daily puzzle
        }

        $puzzle = FamilyArcadeService::generatePuzzle($gameSlug, $seedKey, $difficulty);
        $parMs = match ($difficulty) {
            'easy' => 45000,
            'hard' => 95000,
            default => 65000,
        };
        $puzzle['parMs'] = $parMs;

        $visitorHash = self::computeVisitorHash();
        $refSource = self::detectReferrerSource();

        $playedToday = 0;
        if ($db instanceof PDO) {
            self::recordEvent($db, [
                'challenge_token' => $cleanToken,
                'game_slug' => $gameSlug,
                'event_type' => 'view',
                'visitor_hash' => $visitorHash,
                'score' => 0,
                'elapsed_ms' => 0,
                'beat_challenger' => 0,
                'referrer_source' => $refSource,
            ]);
            try {
                $cntStmt = $db->prepare(
                    'SELECT COUNT(DISTINCT visitor_hash)
                     FROM family_arcade_engagements
                     WHERE game_slug = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)'
                );
                $cntStmt->execute([$gameSlug]);
                $playedToday = (int) $cntStmt->fetchColumn();
            } catch (\Throwable $e) {
                $playedToday = 0;
            }
        }

        $gameName = match ($gameSlug) {
            'kingrid' => 'KinGrid',
            'kinword' => 'KinWord',
            'kinpulse' => 'KinPulse',
            'wordie' => 'Wordie',
            'cheeseboard' => 'Cheeseboard',
            'minesweeper' => 'Minesweeper',
            default => 'KinZip',
        };

        $baseUrl = ShareMetadataProtocol::baseUrl();
        $shareUrl = $cleanToken !== ''
            ? ($baseUrl . '/arcade/challenge/' . rawurlencode($cleanToken))
            : ($baseUrl . '/play/' . rawurlencode($gameSlug));
        $cardImg = $cleanToken !== ''
            ? ($baseUrl . '/arcade/card/' . rawurlencode($cleanToken) . '.jpg?v=1')
            : ($baseUrl . '/arcade/card/game-' . rawurlencode($gameSlug) . '.jpg?v=1');

        $title = $challenger
            ? "Beat {$challenger['firstName']}'s {$challenger['score']} pts in {$gameName}! — Family Arcade"
            : "Play {$gameName} Free — Family Arcade | FamilyPlatform";
        $description = $challenger
            ? "{$challenger['firstName']} finished {$gameName} in " . round($challenger['elapsedMs'] / 1000, 1) . "s. Tap to play the exact same puzzle right now — no sign-up needed!"
            : "Play today's {$gameName} puzzle free on FamilyPlatform — no sign-up required.";

        $meta = ShareMetadataProtocol::buildMeta([
            'badge' => strtoupper($gameName) . ' CHALLENGE',
            'title' => $title,
            'card_title' => $title,
            'description' => $description,
            'image' => $cardImg,
            'url' => $shareUrl,
            'type' => 'website',
        ]);

        $bootstrap = [
            'guestMode' => true,
            'initialGame' => $gameSlug,
            'initialDifficulty' => $difficulty,
            'dailyDate' => $today,
            'challengeToken' => $cleanToken,
            'challenger' => $challenger,
            'initialPuzzle' => $puzzle,
            'ghost' => $challenger ? [
                'fullName' => $challenger['firstName'],
                'elapsedMs' => $challenger['elapsedMs'],
                'score' => $challenger['score'],
            ] : null,
            'socialProofCount' => max(1, $playedToday),
            'shareUrl' => $shareUrl,
            'registerUrl' => '/register' . ($cleanToken !== '' ? ('?arcade_challenge=' . rawurlencode($cleanToken)) : '?ref=arcade_guest'),
            'referrerSource' => $refSource,
            'champions' => [],
        ];

        view('public/arcadeChallenge', [
            'meta' => $meta,
            'bootstrap' => $bootstrap,
            'gameName' => $gameName,
            'challenger' => $challenger,
            'cleanToken' => $cleanToken,
        ]);
    }

    /**
     * GET /play or GET /play/{game}
     */
    public function play(string $game = 'kinzip'): void
    {
        $_GET['game'] = FamilyArcadeService::normaliseGame($game);
        $this->challenge('');
    }

    /**
     * POST /api/arcade/public/track
     */
    public function trackEngagement(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $visitorHash = self::computeVisitorHash();
            Limiter::limit('pub_arc_' . $visitorHash);

            /** @var array<string, mixed>|null $input */
            $input = json_decode((string) file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }

            $eventType = (string) ($input['eventType'] ?? '');
            if (!in_array($eventType, self::VALID_EVENTS, true)) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Invalid engagement event type.']);
                return;
            }

            $gameSlug = FamilyArcadeService::normaliseGame((string) ($input['game'] ?? 'kinzip'));
            $difficulty = FamilyArcadeService::normaliseDifficulty((string) ($input['difficulty'] ?? 'medium'));
            $cleanToken = preg_replace('/[^a-fA-F0-9]/', '', trim((string) ($input['challengeToken'] ?? ''))) ?? '';
            $seedKey = trim((string) ($input['seedKey'] ?? date('Y-m-d')));
            if ($seedKey === '' || strlen($seedKey) > 64) {
                $seedKey = date('Y-m-d');
            }

            $refSource = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($input['referrerSource'] ?? self::detectReferrerSource())) ?? 'direct';
            if ($refSource === '') {
                $refSource = 'direct';
            }

            $elapsedMs = max(0, min(3600000, (int) ($input['elapsedMs'] ?? 0)));
            $verifiedScore = 0;
            $beatChallenger = false;
            $challengerScore = 0;
            $challengerElapsedMs = 0;

            $db = Db::connect2();

            if ($eventType === 'complete') {
                /** @var array<string, mixed> $submission */
                $submission = is_array($input['submission'] ?? null) ? $input['submission'] : [];
                $hintsUsed = max(0, min(20, (int) ($input['hintsUsed'] ?? 0)));
                $mistakesCount = max(0, min(20, (int) ($input['mistakesCount'] ?? 0)));

                $verification = FamilyArcadeService::validateAndScore(
                    $gameSlug,
                    $seedKey,
                    $difficulty,
                    $submission,
                    max(500, $elapsedMs),
                    $hintsUsed,
                    $mistakesCount
                );

                if ($verification['valid']) {
                    $verifiedScore = (int) $verification['score'];
                } else {
                    http_response_code(422);
                    echo json_encode([
                        'status' => 'error',
                        'message' => (string) $verification['reason'],
                    ]);
                    return;
                }

                if ($cleanToken !== '') {
                    $cStmt = $db->prepare('SELECT score, elapsed_ms FROM family_arcade_scores WHERE challenge_token = ? LIMIT 1');
                    $cStmt->execute([$cleanToken]);
                    $cRow = $cStmt->fetch(PDO::FETCH_ASSOC);
                    if (is_array($cRow)) {
                        $challengerScore = (int) ($cRow['score'] ?? 0);
                        $challengerElapsedMs = (int) ($cRow['elapsed_ms'] ?? 0);
                        $beatChallenger = $verifiedScore > $challengerScore
                            || ($verifiedScore === $challengerScore && $elapsedMs < $challengerElapsedMs);
                    }
                }
            }

            self::recordEvent($db, [
                'challenge_token' => $cleanToken,
                'game_slug' => $gameSlug,
                'event_type' => $eventType,
                'visitor_hash' => $visitorHash,
                'score' => $verifiedScore,
                'elapsed_ms' => $elapsedMs,
                'beat_challenger' => $beatChallenger ? 1 : 0,
                'referrer_source' => substr($refSource, 0, 64),
            ]);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'verifiedScore' => $verifiedScore,
                    'elapsedMs' => $elapsedMs,
                    'beatChallenger' => $beatChallenger,
                    'challengerScore' => $challengerScore,
                    'challengerElapsedMs' => $challengerElapsedMs,
                ],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not record engagement.']);
        }
    }

    /**
     * @param array<string, int|string> $row
     */
    private static function recordEvent(PDO $db, array $row): void
    {
        try {
            $stmt = $db->prepare(
                'INSERT INTO family_arcade_engagements
                 (challenge_token, game_slug, event_type, visitor_hash, score, elapsed_ms, beat_challenger, referrer_source)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                (string) ($row['challenge_token'] ?? ''),
                (string) ($row['game_slug'] ?? 'kinzip'),
                (string) ($row['event_type'] ?? 'view'),
                (string) ($row['visitor_hash'] ?? ''),
                (int) ($row['score'] ?? 0),
                (int) ($row['elapsed_ms'] ?? 0),
                (int) ($row['beat_challenger'] ?? 0),
                (string) ($row['referrer_source'] ?? 'direct'),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking analytics write
        }
    }

    private static function computeVisitorHash(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
        $salt = (string) ($_ENV['APP_KEY'] ?? $_ENV['JWT_SECRET'] ?? 'fp_arcade_salt_2026');
        return hash('sha256', $salt . '|' . $ip . '|' . $ua);
    }

    private static function detectReferrerSource(): string
    {
        $src = trim((string) ($_GET['src'] ?? $_GET['utm_source'] ?? ''));
        if ($src !== '') {
            return substr(preg_replace('/[^a-zA-Z0-9_-]/', '', $src) ?? 'direct', 0, 64) ?: 'direct';
        }
        $ref = strtolower((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        if (str_contains($ref, 'facebook.') || str_contains($ref, 'fb.')) return 'facebook';
        if (str_contains($ref, 'whatsapp.') || str_contains($ref, 'wa.me')) return 'whatsapp';
        if (str_contains($ref, 't.co') || str_contains($ref, 'twitter.') || str_contains($ref, 'x.com')) return 'x';
        if (str_contains($ref, 'instagram.')) return 'instagram';
        return 'direct';
    }
}
