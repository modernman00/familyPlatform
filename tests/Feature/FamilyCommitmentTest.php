<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\FamilyCommitmentService;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;

final class FamilyCommitmentTest extends TestCase
{
    private PDO $pdo;
    private FamilyCommitmentService $service;
    private string $ownerId;
    private string $partnerId;
    private string $outsiderId;
    private string $famCode;
    private string $otherFamCode;

    /** @var list<string> */
    private array $createdCommitmentIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (file_exists(dirname(__DIR__, 2) . '/.env')) {
            \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
        }
        $this->pdo = Db::connect2();
        $this->service = new FamilyCommitmentService($this->pdo);

        $suffix = bin2hex(random_bytes(4));
        $this->ownerId = 'test_owner_' . $suffix;
        $this->partnerId = 'test_partner_' . $suffix;
        $this->outsiderId = 'test_outsider_' . $suffix;
        $this->famCode = 'FAMTEST_' . $suffix;
        $this->otherFamCode = 'FAMOTHER_' . $suffix;

        $insPersonal = $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)'
        );
        $insPersonal->execute([$this->ownerId, 'Wale', 'Olaogun', $this->famCode]);
        $insPersonal->execute([$this->partnerId, 'Ajibike', 'Olaogun', $this->famCode]);
    }

    protected function tearDown(): void
    {
        if ($this->createdCommitmentIds !== []) {
            $placeholders = implode(',', array_fill(0, count($this->createdCommitmentIds), '?'));
            $delLogs = $this->pdo->prepare(sprintf('DELETE FROM commitment_reminder_logs WHERE commitment_id IN (%s)', $placeholders));
            $delLogs->execute($this->createdCommitmentIds);

            $delChk = $this->pdo->prepare(sprintf('DELETE FROM commitment_checkins WHERE commitment_id IN (%s)', $placeholders));
            $delChk->execute($this->createdCommitmentIds);

            $delPart = $this->pdo->prepare(sprintf('DELETE FROM commitment_participants WHERE commitment_id IN (%s)', $placeholders));
            $delPart->execute($this->createdCommitmentIds);

            $delCmt = $this->pdo->prepare(sprintf('DELETE FROM family_commitments WHERE id IN (%s)', $placeholders));
            $delCmt->execute($this->createdCommitmentIds);
        }

        $delNotif = $this->pdo->prepare('DELETE FROM notification WHERE sender_id = ? OR receiver_id IN (?, ?, ?)');
        $delNotif->execute([$this->ownerId, $this->ownerId, $this->partnerId, $this->outsiderId]);

        $delPersonal = $this->pdo->prepare('DELETE FROM personal WHERE id IN (?, ?, ?)');
        $delPersonal->execute([$this->ownerId, $this->partnerId, $this->outsiderId]);

        parent::tearDown();
    }

    public function testCreateFixedAndFlexibleCommitmentsAndProjectCalendarWithTieredPrivacy(): void
    {
        $callDad = $this->service->createCommitment($this->ownerId, $this->famCode, [
            'title' => 'Call Dad twice a week',
            'target_statement' => 'Catch up with Dad on health and family news',
            'schedule_mode' => 'fixed',
            'period_unit' => 'week',
            'target_count' => 2,
            'days_of_week' => ['wed', 'sun'],
            'preferred_time' => '19:00',
            'visibility' => 'private',
            'in_app_reminder' => true,
            'timezone' => 'Europe/London',
        ]);
        $this->createdCommitmentIds[] = (string)$callDad['id'];

        $walkKids = $this->service->createCommitment($this->ownerId, $this->famCode, [
            'title' => 'Go for a walk with the kids',
            'target_statement' => 'Outdoor walk together 3 times a week',
            'schedule_mode' => 'flexible',
            'period_unit' => 'week',
            'target_count' => 3,
            'visibility' => 'family',
            'in_app_reminder' => false,
            'timezone' => 'Europe/London',
        ]);
        $this->createdCommitmentIds[] = (string)$walkKids['id'];

        $dinnerWife = $this->service->createCommitment($this->ownerId, $this->famCode, [
            'title' => 'Dinner with wife',
            'target_statement' => 'Dedicated Friday date night',
            'schedule_mode' => 'fixed',
            'period_unit' => 'week',
            'target_count' => 1,
            'days_of_week' => ['fri'],
            'preferred_time' => '20:00',
            'visibility' => 'specific',
            'participant_ids' => [$this->partnerId],
            'in_app_reminder' => true,
            'timezone' => 'Europe/London',
        ]);
        $this->createdCommitmentIds[] = (string)$dinnerWife['id'];

        $start = '2026-11-14';
        $end = '2026-12-14';

        // Owner sees all 3 commitments projected on calendar
        $ownerCalendar = $this->service->projectCalendarOccurrences($this->ownerId, [$this->famCode], $start, $end);
        $ownerTitles = array_unique(array_column($ownerCalendar, 'eventName'));
        self::assertContains('Call Dad twice a week', $ownerTitles);
        self::assertContains('Go for a walk with the kids', $ownerTitles);
        self::assertContains('Dinner with wife', $ownerTitles);

        // Partner sees family + specific, NEVER private "Call Dad"
        $partnerCalendar = $this->service->projectCalendarOccurrences($this->partnerId, [$this->famCode], $start, $end);
        $partnerTitles = array_unique(array_column($partnerCalendar, 'eventName'));
        self::assertNotContains('Call Dad twice a week', $partnerTitles);
        self::assertContains('Go for a walk with the kids', $partnerTitles);
        self::assertContains('Dinner with wife', $partnerTitles);

        // Outsider sees none of them
        $outsiderCalendar = $this->service->projectCalendarOccurrences($this->outsiderId, [$this->otherFamCode], $start, $end);
        $outsiderIds = array_column($outsiderCalendar, 'commitment_id');
        self::assertNotContains($callDad['id'], $outsiderIds);
        self::assertNotContains($walkKids['id'], $outsiderIds);
        self::assertNotContains($dinnerWife['id'], $outsiderIds);
    }

    public function testRichCheckinRequiresReflectionNoteAndSupportsSharedCompletionAndGentleReset(): void
    {
        $dinner = $this->service->createCommitment($this->ownerId, $this->famCode, [
            'title' => 'Dinner with wife',
            'schedule_mode' => 'fixed',
            'period_unit' => 'week',
            'target_count' => 1,
            'days_of_week' => ['fri'],
            'preferred_time' => '20:00',
            'visibility' => 'specific',
            'participant_ids' => [$this->partnerId],
        ]);
        $commitmentId = (string)$dinner['id'];
        $this->createdCommitmentIds[] = $commitmentId;

        // Missing reflection note must be rejected
        $this->expectException(ValidationException::class);
        $this->service->logCheckin($this->partnerId, [$this->famCode], $commitmentId, [
            'reflection_note' => '',
            'rating' => 5,
        ]);
    }

    public function testSharedCompletionGentleResetAndExactTimeIdempotentReminders(): void
    {
        $tz = new DateTimeZone('Europe/London');
        $wednesdayAt19 = new DateTimeImmutable('2026-11-18 19:00:30', $tz);
        $lastWeek = $wednesdayAt19->modify('-7 days');

        $callDad = $this->service->createCommitment($this->ownerId, $this->famCode, [
            'title' => 'Call Dad twice a week',
            'schedule_mode' => 'fixed',
            'period_unit' => 'week',
            'target_count' => 2,
            'days_of_week' => ['wed', 'sun'],
            'preferred_time' => '19:00',
            'visibility' => 'private',
            'in_app_reminder' => true,
            'timezone' => 'Europe/London',
        ]);
        $commitmentId = (string)$callDad['id'];
        $this->createdCommitmentIds[] = $commitmentId;

        // Log 1 check-in last week (does NOT carry over as overdue into current week)
        $this->service->logCheckin($this->ownerId, [$this->famCode], $commitmentId, [
            'reflection_note' => 'Spoke to Dad last Wednesday',
            'rating' => 5,
        ], $lastWeek);

        $currentWeekState = $this->service->getForUser($this->ownerId, [$this->famCode], $commitmentId, $wednesdayAt19);
        self::assertSame(0, $currentWeekState['completed_this_period']);

        // Exact-time reminder dispatch is idempotent
        $firstRun = $this->service->dispatchDueReminders($wednesdayAt19);
        self::assertGreaterThanOrEqual(1, $firstRun['dispatched']);

        $secondRun = $this->service->dispatchDueReminders($wednesdayAt19);
        self::assertSame(0, $secondRun['dispatched']);

        $due = $this->service->getDueInAppReminders($this->ownerId);
        $dueIds = array_column($due, 'commitment_id');
        self::assertContains($commitmentId, $dueIds);

        // Logging check-in with slot_key marks reminder done
        $slotKey = '2026-11-18_19:00';
        $res = $this->service->logCheckin($this->ownerId, [$this->famCode], $commitmentId, [
            'slot_key' => $slotKey,
            'reflection_note' => 'Great catch-up call with Dad',
            'rating' => 5,
        ], $wednesdayAt19);

        self::assertSame(1, $res['commitment']['completed_this_period']);
        self::assertSame('Wale Olaogun', $res['commitment']['recent_checkins'][0]['completed_by_name'] ?? '');
    }

    public function testAdversarialIdorProtectionBlocksUnauthorizedAccess(): void
    {
        $privateCmt = $this->service->createCommitment($this->ownerId, $this->famCode, [
            'title' => 'Private Commitment',
            'schedule_mode' => 'fixed',
            'period_unit' => 'week',
            'target_count' => 1,
            'days_of_week' => ['mon'],
            'preferred_time' => '09:00',
            'visibility' => 'private',
        ]);
        $commitmentId = (string)$privateCmt['id'];
        $this->createdCommitmentIds[] = $commitmentId;

        $this->expectException(ForbiddenException::class);
        $this->service->getForUser($this->outsiderId, [$this->otherFamCode], $commitmentId);
    }
}
