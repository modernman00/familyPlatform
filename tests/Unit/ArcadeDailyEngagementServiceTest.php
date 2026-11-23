<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\ArcadeDailyEngagementService;
use PDO;
use PHPUnit\Framework\TestCase;

final class ArcadeDailyEngagementServiceTest extends TestCase
{
    private function createSqlitePdo(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('CREATE TABLE personal (id TEXT PRIMARY KEY, firstName TEXT, famCode TEXT, deleted_at TEXT NULL)');
        $pdo->exec('CREATE TABLE user_push_subscriptions (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id TEXT, endpoint TEXT, failure_count INTEGER DEFAULT 0)');
        $pdo->exec('CREATE TABLE pushNotification (no INTEGER PRIMARY KEY AUTOINCREMENT, id TEXT, endpoint TEXT, deleted_at TEXT NULL)');
        $pdo->exec('CREATE TABLE family_arcade_scores (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id TEXT, fam_code TEXT, game_slug TEXT, puzzle_date TEXT, score INTEGER, created_at TEXT)');
        $pdo->exec('CREATE TABLE notification (no INTEGER PRIMARY KEY AUTOINCREMENT, sender_id TEXT, receiver_id TEXT, sender_name TEXT, notification_name TEXT, notification_date TEXT, notification_type TEXT, notification_content TEXT, action_url TEXT, notification_status TEXT)');
        $pdo->exec('CREATE TABLE notification_orchestration (id TEXT PRIMARY KEY, user_id TEXT, tag TEXT, created_at TEXT)');

        return $pdo;
    }

    public function testComposeIntelligentMessageSelectsStreakMomentumWhenUserHasActiveStreak(): void
    {
        $service = new ArcadeDailyEngagementService($this->createSqlitePdo());

        $msg = $service->composeIntelligentMessage([
            'user_id' => 'U100',
            'first_name' => 'Wale',
            'top_game' => 'kinzip',
            'streak_days' => 3,
            'next_win' => 4,
            'family_rival_name' => null,
            'family_rival_game' => null,
            'family_players_today' => 0,
            'today_date' => '2026-11-23',
        ]);

        $this->assertSame('streak_momentum', $msg['strategy']);
        $this->assertSame('kinzip', $msg['game']);
        $this->assertSame('/arcade?game=kinzip&source=daily_nudge', $msg['action_url']);
        $this->assertStringContainsString('Win #4', $msg['body']);
    }

    public function testComposeIntelligentMessageSelectsFamilySocialProofWhenRivalPlayedToday(): void
    {
        $service = new ArcadeDailyEngagementService($this->createSqlitePdo());

        $msg = $service->composeIntelligentMessage([
            'user_id' => 'U200',
            'first_name' => 'Tunde',
            'top_game' => 'kinzip',
            'streak_days' => 0,
            'next_win' => 1,
            'family_rival_name' => 'Femi',
            'family_rival_game' => 'kingrid',
            'family_players_today' => 2,
            'today_date' => '2026-11-23',
        ]);

        $this->assertSame('family_social_proof', $msg['strategy']);
        $this->assertSame('kingrid', $msg['game']);
        $this->assertStringContainsString('Femi', $msg['title']);
        $this->assertSame('/arcade?game=kingrid&source=daily_nudge', $msg['action_url']);
    }

    public function testComposeIntelligentMessageFallsBackToDailySpotlightForDormantUser(): void
    {
        $service = new ArcadeDailyEngagementService($this->createSqlitePdo());

        $msg = $service->composeIntelligentMessage([
            'user_id' => 'U300',
            'first_name' => 'Ada',
            'top_game' => 'kinzip',
            'streak_days' => 0,
            'next_win' => 1,
            'family_rival_name' => null,
            'family_rival_game' => null,
            'family_players_today' => 0,
            'today_date' => '2026-11-23',
        ]);

        $this->assertSame('daily_spotlight', $msg['strategy']);
        $this->assertStringContainsString('/arcade?game=', $msg['action_url']);
        $this->assertStringContainsString('source=daily_nudge', $msg['action_url']);
    }

    public function testDispatchDailyNudgesSuppressesAlreadyPlayedAndAlreadyNotifiedAndBackoff(): void
    {
        $pdo = $this->createSqlitePdo();
        $today = '2026-11-23';

        // U1: eligible, played yesterday & day before (2-day streak), not played today -> should send streak_momentum
        // U2: already played today -> suppressed_already_played
        // U3: already notified today -> suppressed_already_notified
        // U4: ignored 5+ nudges in last 14 days and sent within last 7 days -> suppressed_backoff
        $users = [
            ['U1', 'Wale', 'FAM1'],
            ['U2', 'Femi', 'FAM1'],
            ['U3', 'Sola', 'FAM1'],
            ['U4', 'Bola', 'FAM1'],
        ];
        $insUser = $pdo->prepare('INSERT INTO personal (id, firstName, famCode) VALUES (?, ?, ?)');
        $insSub = $pdo->prepare('INSERT INTO user_push_subscriptions (user_id, endpoint, failure_count) VALUES (?, ?, 0)');
        foreach ($users as [$id, $name, $fam]) {
            $insUser->execute([$id, $name, $fam]);
            $insSub->execute([$id, 'https://fcm.googleapis.com/fcm/send/' . $id]);
        }

        $insScore = $pdo->prepare('INSERT INTO family_arcade_scores (user_id, fam_code, game_slug, puzzle_date, score, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $insScore->execute(['U1', 'FAM1', 'kinzip', '2026-11-22', 950, '2026-11-22 09:00:00']);
        $insScore->execute(['U1', 'FAM1', 'kinzip', '2026-11-21', 910, '2026-11-21 09:00:00']);
        $insScore->execute(['U2', 'FAM1', 'kinzip', $today, 990, $today . ' 08:00:00']);

        $insNotif = $pdo->prepare('INSERT INTO notification (sender_id, receiver_id, sender_name, notification_name, notification_date, notification_type, notification_content, action_url, notification_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insNotif->execute(['system', 'U3', 'Family Platform', 'Play', $today . ' 07:00:00', 'arcade_daily_nudge', 'Play today', '/arcade', 'new']);

        for ($d = 1; $d <= 5; $d++) {
            $dt = date('Y-m-d', strtotime($today . " -{$d} days"));
            $insNotif->execute(['system', 'U4', 'Family Platform', 'Play', $dt . ' 08:00:00', 'arcade_daily_nudge', 'Play today', '/arcade', 'unread']);
        }

        $service = new ArcadeDailyEngagementService($pdo);
        $stats = $service->dispatchDailyNudges(true, 100, $today);

        $this->assertSame(4, $stats['eligible_users']);
        $this->assertSame(1, $stats['sent']);
        $this->assertSame(1, $stats['suppressed_already_played']);
        $this->assertSame(1, $stats['suppressed_already_notified']);
        $this->assertSame(1, $stats['suppressed_backoff']);
        $this->assertSame(0, $stats['errors']);
        $this->assertCount(1, $stats['samples']);
        $this->assertSame('U1', $stats['samples'][0]['user_id']);
        $this->assertSame('streak_momentum', $stats['samples'][0]['strategy']);
    }
}
