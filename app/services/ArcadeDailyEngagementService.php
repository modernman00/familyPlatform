<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;
use Throwable;

/**
 * ArcadeDailyEngagementService
 *
 * Computes per-user intelligent daily engagement hooks (active streak/win momentum,
 * family rival social proof, or fresh daily puzzle spotlight) and dispatches
 * capped mobile push + in-app notifications via NotificationOrchestrator.
 */
final class ArcadeDailyEngagementService
{
    /** @var array<string, string> */
    private const GAME_LABELS = [
        'kinzip' => 'KinZip',
        'kingrid' => 'KinGrid',
        'kinword' => 'KinWord',
        'kinpulse' => 'KinPulse',
        'wordie' => 'KinWordie',
        'ayo' => 'Ayò Olópon',
        'chess' => 'Family Chess',
        'cheeseboard' => 'Cheeseboard',
        'minesweeper' => 'KinSweeper',
    ];

    /** @var array<int, string> */
    private const SPOTLIGHT_ROTATION = [
        'kinzip',
        'kingrid',
        'kinword',
        'kinpulse',
        'wordie',
        'ayo',
        'chess',
        'minesweeper',
    ];

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
    }

    /**
     * Run the daily arcade engagement dispatch across eligible users with push subscriptions.
     *
     * @return array{
     *   status: string,
     *   date: string,
     *   dry_run: bool,
     *   eligible_users: int,
     *   sent: int,
     *   suppressed_already_played: int,
     *   suppressed_already_notified: int,
     *   suppressed_backoff: int,
     *   errors: int,
     *   samples: array<int, array<string, mixed>>
     * }
     */
    public function dispatchDailyNudges(bool $dryRun = false, int $limit = 500, ?string $todayDate = null): array
    {
        $today = $todayDate ?? date('Y-m-d');
        $limit = max(1, min($limit, 5000));

        $candidates = $this->fetchPushEligibleUsers($limit);

        $stats = [
            'status' => 'ok',
            'date' => $today,
            'dry_run' => $dryRun,
            'eligible_users' => count($candidates),
            'sent' => 0,
            'suppressed_already_played' => 0,
            'suppressed_already_notified' => 0,
            'suppressed_backoff' => 0,
            'errors' => 0,
            'samples' => [],
        ];

        foreach ($candidates as $user) {
            $userId = (string) $user['id'];
            $famCode = (string) $user['famCode'];
            $firstName = trim((string) $user['firstName']);

            if ($userId === '') {
                continue;
            }

            try {
                if ($this->hasPlayedOnDate($userId, $today)) {
                    $stats['suppressed_already_played']++;
                    continue;
                }

                if ($this->hasReceivedNudgeOnDate($userId, $today)) {
                    $stats['suppressed_already_notified']++;
                    continue;
                }

                if ($this->shouldBackOff($userId, $today)) {
                    $stats['suppressed_backoff']++;
                    continue;
                }

                $context = $this->buildUserEngagementContext($userId, $famCode, $firstName, $today);
                $message = $this->composeIntelligentMessage($context);

                if (!$dryRun) {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'arcade',
                        priority: 'normal',
                        title: $message['title'],
                        body: $message['body'],
                        actionUrl: $message['action_url'],
                        tag: 'arcade_daily_nudge',
                        familyCode: $famCode !== '' ? $famCode : null,
                        metadata: [
                            'type' => 'arcade_daily_nudge',
                            'game' => $message['game'],
                            'strategy' => $message['strategy'],
                            'streak_days' => $context['streak_days'],
                            'next_win' => $context['next_win'],
                        ]
                    );
                }

                $stats['sent']++;
                if (count($stats['samples']) < 10) {
                    $stats['samples'][] = [
                        'user_id' => $userId,
                        'strategy' => $message['strategy'],
                        'game' => $message['game'],
                        'title' => $message['title'],
                        'body' => $message['body'],
                        'action_url' => $message['action_url'],
                    ];
                }
            } catch (Throwable $e) {
                $stats['errors']++;
                error_log('[ArcadeDailyEngagement] Failed for user ' . $userId . ': ' . $e->getMessage());
            }
        }

        return $stats;
    }

    /**
     * Compose a dynamic, LinkedIn-Games-style intelligent mobile notification payload.
     *
     * @param array{
     *   user_id: string,
     *   first_name: string,
     *   top_game: string,
     *   streak_days: int,
     *   next_win: int,
     *   family_rival_name: string|null,
     *   family_rival_game: string|null,
     *   family_players_today: int,
     *   today_date: string
     * } $context
     * @return array{title: string, body: string, action_url: string, game: string, strategy: string}
     */
    public function composeIntelligentMessage(array $context): array
    {
        $topGame = $this->resolveGameSlug($context['top_game']);
        $streakDays = max(0, (int) $context['streak_days']);
        $nextWin = max(1, (int) $context['next_win']);
        $rivalName = $context['family_rival_name'];
        $rivalGame = $context['family_rival_game'] !== null ? $this->resolveGameSlug($context['family_rival_game']) : null;
        $familyPlayersToday = max(0, (int) $context['family_players_today']);
        $userId = (string) $context['user_id'];
        $today = (string) $context['today_date'];

        // Strategy 1: Unstoppable Win / Active Streak Momentum (LinkedIn Zip style)
        if ($streakDays >= 2 || $nextWin >= 2) {
            $gameLabel = self::GAME_LABELS[$topGame] ?? 'KinZip';
            $variant = abs(crc32($userId . ':' . $today)) % 2;
            if ($variant === 0 || $streakDays < 2) {
                $title = "🏁 Play {$gameLabel}!";
                $body = "You're unstoppable. Win #{$nextWin} is waiting — let's go!";
            } else {
                $title = "🔥 {$streakDays}-Day {$gameLabel} Streak";
                $body = "Keep your streak alive! Win #{$nextWin} is waiting in the Family Arcade.";
            }

            return [
                'title' => $title,
                'body' => $body,
                'action_url' => "/arcade?game={$topGame}&source=daily_nudge",
                'game' => $topGame,
                'strategy' => 'streak_momentum',
            ];
        }

        // Strategy 2: Family Social Proof / Rival Overtake Challenge
        if ($familyPlayersToday > 0 && $rivalName !== null && $rivalName !== '' && $rivalGame !== null) {
            $gameLabel = self::GAME_LABELS[$rivalGame] ?? 'KinZip';
            $title = "🏆 {$rivalName} played {$gameLabel} today";
            $body = $familyPlayersToday > 1
                ? "{$rivalName} and " . ($familyPlayersToday - 1) . " other relatives are on today's board. Can you beat them?"
                : "{$rivalName} set today's {$gameLabel} pace. Jump in and claim the top spot!";

            return [
                'title' => $title,
                'body' => $body,
                'action_url' => "/arcade?game={$rivalGame}&source=daily_nudge",
                'game' => $rivalGame,
                'strategy' => 'family_social_proof',
            ];
        }

        // Strategy 3: Fresh Daily Spotlight Puzzle Re-engagement
        $dayIndex = (int) date('z', strtotime($today) ?: time());
        $userOffset = abs(crc32($userId)) % count(self::SPOTLIGHT_ROTATION);
        $spotlightSlug = self::SPOTLIGHT_ROTATION[($dayIndex + $userOffset) % count(self::SPOTLIGHT_ROTATION)];
        $spotlightLabel = self::GAME_LABELS[$spotlightSlug];

        return [
            'title' => "🧩 Today's {$spotlightLabel} is live!",
            'body' => "Take a 2-minute brain break and climb your Family Cup leaderboard.",
            'action_url' => "/arcade?game={$spotlightSlug}&source=daily_nudge",
            'game' => $spotlightSlug,
            'strategy' => 'daily_spotlight',
        ];
    }

    /**
     * Fetch active users who have at least one valid push subscription.
     *
     * @return array<int, array{id: string, firstName: string, famCode: string}>
     */
    private function fetchPushEligibleUsers(int $limit): array
    {
        $sql = '
            SELECT DISTINCT p.id, p.firstName, p.famCode
            FROM personal p
            WHERE (p.deleted_at IS NULL)
              AND (
                  EXISTS (
                      SELECT 1 FROM user_push_subscriptions ups
                      WHERE ups.user_id = p.id AND ups.failure_count < 5
                  )
                  OR EXISTS (
                      SELECT 1 FROM pushNotification pn
                      WHERE pn.id = p.id AND pn.deleted_at IS NULL
                  )
              )
            ORDER BY p.id ASC
            LIMIT ?
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        /** @var array<int, array{id: string, firstName: string, famCode: string}> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function hasPlayedOnDate(string $userId, string $date): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM family_arcade_scores WHERE user_id = ? AND puzzle_date = ? LIMIT 1'
        );
        $stmt->execute([$userId, $date]);
        return $stmt->fetchColumn() !== false;
    }

    private function hasReceivedNudgeOnDate(string $userId, string $date): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM notification
             WHERE receiver_id = ?
               AND notification_type = 'arcade_daily_nudge'
               AND DATE(notification_date) = ?
             LIMIT 1"
        );
        $stmt->execute([$userId, $date]);
        if ($stmt->fetchColumn() !== false) {
            return true;
        }

        $orchStmt = $this->db->prepare(
            "SELECT 1 FROM notification_orchestration
             WHERE user_id = ?
               AND tag = 'arcade_daily_nudge'
               AND DATE(created_at) = ?
             LIMIT 1"
        );
        $orchStmt->execute([$userId, $date]);
        return $orchStmt->fetchColumn() !== false;
    }

    /**
     * Behavioural back-off: if the user received 5+ unread arcade nudges in the last 14 days
     * without playing any game, throttle to once every 7 days.
     */
    private function shouldBackOff(string $userId, string $today): bool
    {
        $fourteenDaysAgo = date('Y-m-d', strtotime($today . ' -14 days') ?: time());
        $sevenDaysAgo = date('Y-m-d', strtotime($today . ' -7 days') ?: time());

        $playedStmt = $this->db->prepare(
            'SELECT 1 FROM family_arcade_scores WHERE user_id = ? AND puzzle_date >= ? LIMIT 1'
        );
        $playedStmt->execute([$userId, $fourteenDaysAgo]);
        if ($playedStmt->fetchColumn() !== false) {
            return false;
        }

        $ignoredStmt = $this->db->prepare(
            "SELECT COUNT(*) AS cnt, MAX(DATE(notification_date)) AS last_sent
             FROM notification
             WHERE receiver_id = ?
               AND notification_type = 'arcade_daily_nudge'
               AND notification_status IN ('new', 'unread')
               AND DATE(notification_date) >= ?"
        );
        $ignoredStmt->execute([$userId, $fourteenDaysAgo]);
        $row = $ignoredStmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $ignoredCount = (int) ($row['cnt'] ?? 0);
        $lastSent = (string) ($row['last_sent'] ?? '');

        return $ignoredCount >= 5 && $lastSent !== '' && $lastSent > $sevenDaysAgo;
    }

    /**
     * @return array{
     *   user_id: string,
     *   first_name: string,
     *   top_game: string,
     *   streak_days: int,
     *   next_win: int,
     *   family_rival_name: string|null,
     *   family_rival_game: string|null,
     *   family_players_today: int,
     *   today_date: string
     * }
     */
    private function buildUserEngagementContext(string $userId, string $famCode, string $firstName, string $today): array
    {
        // 1. User's favourite game & total wins
        $favStmt = $this->db->prepare(
            'SELECT game_slug, COUNT(DISTINCT puzzle_date) AS wins
             FROM family_arcade_scores
             WHERE user_id = ?
             GROUP BY game_slug
             ORDER BY wins DESC, MAX(created_at) DESC
             LIMIT 1'
        );
        $favStmt->execute([$userId]);
        $favRow = $favStmt->fetch(PDO::FETCH_ASSOC);

        $topGame = is_array($favRow) ? (string) ($favRow['game_slug'] ?? 'kinzip') : 'kinzip';
        $totalWinsOnTop = is_array($favRow) ? (int) ($favRow['wins'] ?? 0) : 0;

        // 2. Active consecutive-day streak ending yesterday (since today isn't played yet)
        $datesStmt = $this->db->prepare(
            'SELECT DISTINCT puzzle_date
             FROM family_arcade_scores
             WHERE user_id = ? AND puzzle_date < ?
             ORDER BY puzzle_date DESC
             LIMIT 30'
        );
        $datesStmt->execute([$userId, $today]);
        $dates = $datesStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $streakDays = 0;
        $expectedDate = date('Y-m-d', strtotime($today . ' -1 day') ?: time());
        foreach ($dates as $d) {
            if ((string) $d === $expectedDate) {
                $streakDays++;
                $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day') ?: time());
            } else {
                break;
            }
        }

        // 3. Family social proof for today
        $rivalName = null;
        $rivalGame = null;
        $familyPlayersToday = 0;

        if ($famCode !== '') {
            $famStmt = $this->db->prepare(
                'SELECT s.user_id, p.firstName, s.game_slug
                 FROM family_arcade_scores s
                 INNER JOIN personal p ON p.id = s.user_id
                 WHERE s.fam_code = ?
                   AND s.puzzle_date = ?
                   AND s.user_id <> ?
                 ORDER BY s.score DESC, s.created_at DESC
                 LIMIT 25'
            );
            $famStmt->execute([$famCode, $today, $userId]);
            $famRows = $famStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $uniquePlayers = [];
            foreach ($famRows as $r) {
                $uid = (string) ($r['user_id'] ?? '');
                if ($uid !== '') {
                    $uniquePlayers[$uid] = true;
                }
                if ($rivalName === null) {
                    $candidateName = trim((string) ($r['firstName'] ?? ''));
                    if ($candidateName !== '') {
                        $rivalName = $candidateName;
                        $rivalGame = (string) ($r['game_slug'] ?? 'kinzip');
                    }
                }
            }
            $familyPlayersToday = count($uniquePlayers);
        }

        return [
            'user_id' => $userId,
            'first_name' => $firstName,
            'top_game' => $topGame,
            'streak_days' => $streakDays,
            'next_win' => $totalWinsOnTop + 1,
            'family_rival_name' => $rivalName,
            'family_rival_game' => $rivalGame,
            'family_players_today' => $familyPlayersToday,
            'today_date' => $today,
        ];
    }

    private function resolveGameSlug(string $slug): string
    {
        $clean = strtolower(trim($slug));
        return isset(self::GAME_LABELS[$clean]) ? $clean : 'kinzip';
    }
}
