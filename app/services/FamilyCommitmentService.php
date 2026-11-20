<?php

declare(strict_types=1);

namespace App\services;

use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;

/**
 * Family Commitments & Recurring Targets Service.
 *
 * Handles fixed day/time commitments, flexible weekly/monthly quotas,
 * tiered visibility (private / specific / family), shared check-ins,
 * gentle period resets, dynamic calendar projection, and exact-time reminders.
 */
final class FamilyCommitmentService
{
    private const VALID_DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    private const DAY_MAP = [
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
        'sun' => 7,
    ];

    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Db::connect2();
    }

    /**
     * List all active commitments visible to the given user, enriched with
     * gentle-reset period progress, streaks, next occurrence, and recent check-ins.
     *
     * @param list<string> $famCodes
     * @return list<array<string, mixed>>
     */
    public function listForUser(string $userId, array $famCodes, ?DateTimeImmutable $now = null): array
    {
        $rows = $this->fetchAccessibleCommitments($userId, $famCodes);
        $result = [];

        foreach ($rows as $row) {
            $result[] = $this->enrichCommitment($row, $userId, $now);
        }

        return $result;
    }

    /**
     * Get a single commitment by ID if accessible to the user.
     *
     * @param list<string> $famCodes
     * @return array<string, mixed>
     */
    public function getForUser(string $userId, array $famCodes, string $commitmentId, ?DateTimeImmutable $now = null): array
    {
        $row = $this->findById($commitmentId);
        if ($row === null) {
            throw new NotFoundException('Commitment not found.');
        }

        if (!$this->canView($row, $userId, $famCodes)) {
            throw new ForbiddenException('You do not have permission to view this commitment.');
        }

        return $this->enrichCommitment($row, $userId, $now);
    }

    /**
     * Create a new family commitment and register owner/participants.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createCommitment(string $userId, string $famCode, array $payload): array
    {
        $validated = $this->validatePayload($payload);
        $commitmentId = $this->generateId('cmt');

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO family_commitments (
                    id, owner_user_id, fam_code, title, target_statement,
                    schedule_mode, period_unit, target_count, days_of_week,
                    month_day, preferred_time, visibility, linked_member_id,
                    in_app_reminder, timezone, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
            );
            $stmt->execute([
                $commitmentId,
                $userId,
                $famCode,
                $validated['title'],
                $validated['target_statement'],
                $validated['schedule_mode'],
                $validated['period_unit'],
                $validated['target_count'],
                $validated['days_of_week_json'],
                $validated['month_day'],
                $validated['preferred_time'],
                $validated['visibility'],
                $validated['linked_member_id'],
                $validated['in_app_reminder'] ? 1 : 0,
                $validated['timezone'],
            ]);

            $this->syncParticipants($commitmentId, $userId, $validated['participant_ids'], $validated['linked_member_id']);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        $row = $this->findById($commitmentId);
        if ($row === null) {
            throw new \RuntimeException('Failed to load created commitment.');
        }

        return $this->enrichCommitment($row, $userId);
    }

    /**
     * Update an existing commitment (owner only).
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function updateCommitment(string $userId, string $commitmentId, array $payload): array
    {
        $existing = $this->findById($commitmentId);
        if ($existing === null) {
            throw new NotFoundException('Commitment not found.');
        }
        if ((string)$existing['owner_user_id'] !== $userId) {
            throw new ForbiddenException('Only the creator can edit this commitment.');
        }

        $validated = $this->validatePayload($payload);

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE family_commitments SET
                    title = ?,
                    target_statement = ?,
                    schedule_mode = ?,
                    period_unit = ?,
                    target_count = ?,
                    days_of_week = ?,
                    month_day = ?,
                    preferred_time = ?,
                    visibility = ?,
                    linked_member_id = ?,
                    in_app_reminder = ?,
                    timezone = ?
                 WHERE id = ? AND owner_user_id = ? AND deleted_at IS NULL'
            );
            $stmt->execute([
                $validated['title'],
                $validated['target_statement'],
                $validated['schedule_mode'],
                $validated['period_unit'],
                $validated['target_count'],
                $validated['days_of_week_json'],
                $validated['month_day'],
                $validated['preferred_time'],
                $validated['visibility'],
                $validated['linked_member_id'],
                $validated['in_app_reminder'] ? 1 : 0,
                $validated['timezone'],
                $commitmentId,
                $userId,
            ]);

            $this->syncParticipants($commitmentId, $userId, $validated['participant_ids'], $validated['linked_member_id']);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        $updated = $this->findById($commitmentId);
        if ($updated === null) {
            throw new NotFoundException('Commitment not found after update.');
        }

        return $this->enrichCommitment($updated, $userId);
    }

    /**
     * Soft-delete a commitment (owner only).
     */
    public function deleteCommitment(string $userId, string $commitmentId): void
    {
        $existing = $this->findById($commitmentId);
        if ($existing === null) {
            throw new NotFoundException('Commitment not found.');
        }
        if ((string)$existing['owner_user_id'] !== $userId) {
            throw new ForbiddenException('Only the creator can delete this commitment.');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE family_commitments SET is_active = 0, deleted_at = NOW() WHERE id = ? AND owner_user_id = ?'
        );
        $stmt->execute([$commitmentId, $userId]);
    }

    /**
     * Log a Rich Check-in for a commitment (shared completion for accessible members).
     * Requires a short reflection note (min 2 chars) or rating (1-5).
     *
     * @param list<string> $famCodes
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function logCheckin(
        string $userId,
        array $famCodes,
        string $commitmentId,
        array $payload,
        ?DateTimeImmutable $now = null
    ): array {
        $row = $this->findById($commitmentId);
        if ($row === null) {
            throw new NotFoundException('Commitment not found.');
        }
        if (!$this->canView($row, $userId, $famCodes)) {
            throw new ForbiddenException('You do not have permission to log a check-in for this commitment.');
        }

        $note = trim((string)($payload['reflection_note'] ?? ''));
        $ratingRaw = $payload['rating'] ?? null;
        $rating = ($ratingRaw !== null && $ratingRaw !== '') ? (int)$ratingRaw : null;

        if ($rating !== null && ($rating < 1 || $rating > 5)) {
            throw new ValidationException('Rating must be between 1 and 5.');
        }
        if (mb_strlen($note) < 2) {
            throw new ValidationException('Please add a brief reflection note (at least 2 characters) to complete your check-in.');
        }
        if (mb_strlen($note) > 1000) {
            throw new ValidationException('Reflection note must be 1,000 characters or fewer.');
        }

        $tz = $this->safeTimezone((string)($row['timezone'] ?? 'Europe/London'));
        $localNow = ($now ?? new DateTimeImmutable('now', $tz))->setTimezone($tz);
        $periodKey = $this->computePeriodKey((string)$row['period_unit'], $localNow);

        $slotKeyRaw = trim((string)($payload['slot_key'] ?? ''));
        $slotKey = $slotKeyRaw !== '' ? mb_substr($slotKeyRaw, 0, 32) : null;

        $checkinId = $this->generateId('chk');

        $stmt = $this->pdo->prepare(
            'INSERT INTO commitment_checkins (
                id, commitment_id, completed_by_user_id, period_key, slot_key, rating, reflection_note
            ) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $checkinId,
            $commitmentId,
            $userId,
            $periodKey,
            $slotKey,
            $rating,
            $note,
        ]);

        if ($slotKey !== null) {
            $upd = $this->pdo->prepare(
                "UPDATE commitment_reminder_logs SET status = 'done', acted_at = NOW()
                 WHERE commitment_id = ? AND slot_key = ? AND status = 'sent'"
            );
            $upd->execute([$commitmentId, $slotKey]);
        }

        return [
            'checkin_id' => $checkinId,
            'commitment' => $this->enrichCommitment($row, $userId, $now),
        ];
    }

    /**
     * Dismiss an exact-time in-app reminder for a specific slot.
     *
     * @param list<string> $famCodes
     */
    public function dismissReminder(string $userId, array $famCodes, string $commitmentId, string $slotKey): void
    {
        $row = $this->findById($commitmentId);
        if ($row === null) {
            throw new NotFoundException('Commitment not found.');
        }
        if (!$this->canView($row, $userId, $famCodes)) {
            throw new ForbiddenException('You do not have permission to dismiss this reminder.');
        }

        $stmt = $this->pdo->prepare(
            "UPDATE commitment_reminder_logs SET status = 'dismissed', acted_at = NOW()
             WHERE commitment_id = ? AND user_id = ? AND slot_key = ?"
        );
        $stmt->execute([$commitmentId, $userId, mb_substr(trim($slotKey), 0, 32)]);
    }

    /**
     * Dynamically project commitment occurrences onto the Family Calendar
     * for [startDate, endDate] (YYYY-MM-DD) without bloating the events table.
     *
     * @param list<string> $famCodes
     * @return list<array<string, mixed>>
     */
    public function projectCalendarOccurrences(
        string $userId,
        array $famCodes,
        string $startDate,
        string $endDate,
        ?DateTimeImmutable $now = null
    ): array {
        $rows = $this->fetchAccessibleCommitments($userId, $famCodes);
        $start = new DateTimeImmutable($startDate . ' 00:00:00');
        $end = new DateTimeImmutable($endDate . ' 23:59:59');
        $todayStr = ($now ?? new DateTimeImmutable('now'))->format('Y-m-d');

        $occurrences = [];

        foreach ($rows as $row) {
            $commitmentId = (string)$row['id'];
            $tz = $this->safeTimezone((string)($row['timezone'] ?? 'Europe/London'));
            $timeStr = (string)($row['preferred_time'] ?? '09:00');
            if ($timeStr === '') {
                $timeStr = '09:00';
            }

            $checkinsBySlot = $this->getCheckinSlotsForCommitment($commitmentId);

            if ((string)$row['schedule_mode'] === 'fixed') {
                $dates = $this->expandFixedDates($row, $start, $end);
                foreach ($dates as $dateObj) {
                    $dateYmd = $dateObj->format('Y-m-d');
                    $slotKey = $dateYmd . '_' . $timeStr;
                    $isCompleted = isset($checkinsBySlot[$slotKey]);

                    $occurrences[] = $this->formatCalendarItem(
                        $row,
                        $userId,
                        $dateYmd,
                        $timeStr,
                        $slotKey,
                        $isCompleted,
                        $todayStr
                    );
                }
            } else {
                // Flexible targets appear as an actionable period reminder on the upcoming Saturday (or today if current week)
                $cursor = $start;
                $seenPeriods = [];
                while ($cursor <= $end) {
                    $periodKey = $this->computePeriodKey((string)$row['period_unit'], $cursor);
                    if (!isset($seenPeriods[$periodKey])) {
                        $seenPeriods[$periodKey] = true;
                        $anchorDate = $cursor->format('Y-m-d');
                        if ($anchorDate < $todayStr) {
                            $anchorDate = $todayStr;
                        }
                        if ($anchorDate <= $endDate) {
                            $periodDone = $this->countCheckinsInPeriod($commitmentId, $periodKey);
                            $targetCount = (int)$row['target_count'];
                            $occurrences[] = $this->formatCalendarItem(
                                $row,
                                $userId,
                                $anchorDate,
                                $timeStr,
                                'flex_' . $periodKey,
                                $periodDone >= $targetCount,
                                $todayStr,
                                $periodDone,
                                $targetCount
                            );
                        }
                    }
                    $cursor = $cursor->modify('+7 days');
                }
            }
        }

        usort($occurrences, static function (array $a, array $b): int {
            return strcmp((string)$a['eventDateRaw'], (string)$b['eventDateRaw']);
        });

        return $occurrences;
    }

    /**
     * Dispatch exact-time in-app reminders for commitments whose preferred_time
     * matches the current minute (within a 2-minute tolerance window).
     * Idempotent via commitment_reminder_logs unique constraint.
     *
     * @return array{checked: int, dispatched: int}
     */
    public function dispatchDueReminders(?DateTimeImmutable $now = null): array
    {
        $utcNow = $now ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $stmt = $this->pdo->query(
            "SELECT * FROM family_commitments WHERE is_active = 1 AND deleted_at IS NULL AND in_app_reminder = 1 AND preferred_time IS NOT NULL AND preferred_time <> ''"
        );
        $commitments = $stmt !== false ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

        $dispatched = 0;

        foreach ($commitments as $row) {
            $tz = $this->safeTimezone((string)($row['timezone'] ?? 'Europe/London'));
            $localNow = $utcNow->setTimezone($tz);
            $preferredTime = (string)$row['preferred_time'];

            if (!$this->isDueNow($row, $localNow, $preferredTime)) {
                continue;
            }

            $slotKey = $localNow->format('Y-m-d') . '_' . $preferredTime;
            $recipients = $this->resolveReminderRecipients($row);

            foreach ($recipients as $recipientId) {
                if ($this->hasReminderLog((string)$row['id'], $recipientId, $slotKey)) {
                    continue;
                }

                $notifNo = $this->insertInAppReminderNotification($row, $recipientId, $slotKey);
                $logStmt = $this->pdo->prepare(
                    "INSERT IGNORE INTO commitment_reminder_logs (commitment_id, user_id, slot_key, status, notification_no)
                     VALUES (?, ?, ?, 'sent', ?)"
                );
                $logStmt->execute([(string)$row['id'], $recipientId, $slotKey, $notifNo]);
                if ($logStmt->rowCount() > 0) {
                    $dispatched++;
                }
            }
        }

        return [
            'checked' => count($commitments),
            'dispatched' => $dispatched,
        ];
    }

    /**
     * Fetch active ('sent') in-app reminders for the user.
     *
     * @return list<array<string, mixed>>
     */
    public function getDueInAppReminders(string $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT l.commitment_id, l.slot_key, l.sent_at, c.title, c.target_statement, c.preferred_time
             FROM commitment_reminder_logs l
             INNER JOIN family_commitments c ON c.id = l.commitment_id
             WHERE l.user_id = ? AND l.status = 'sent' AND c.is_active = 1 AND c.deleted_at IS NULL
             ORDER BY l.sent_at DESC
             LIMIT 10"
        );
        $stmt->execute([$userId]);
        return array_values($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    /**
     * @param list<string> $famCodes
     * @return list<array<string, mixed>>
     */
    private function fetchAccessibleCommitments(string $userId, array $famCodes): array
    {
        $cleanFamCodes = array_values(array_filter(array_unique(array_map('strval', $famCodes)), static fn (string $c): bool => $c !== ''));

        if ($cleanFamCodes === []) {
            $stmt = $this->pdo->prepare(
                'SELECT DISTINCT c.*
                 FROM family_commitments c
                 LEFT JOIN commitment_participants p ON p.commitment_id = c.id AND p.user_id = ?
                 WHERE c.is_active = 1 AND c.deleted_at IS NULL
                   AND (c.owner_user_id = ? OR p.user_id IS NOT NULL)
                 ORDER BY c.created_at DESC'
            );
            $stmt->execute([$userId, $userId]);
            return array_values($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        }

        $placeholders = implode(',', array_fill(0, count($cleanFamCodes), '?'));
        $sql = sprintf(
            "SELECT DISTINCT c.*
             FROM family_commitments c
             LEFT JOIN commitment_participants p ON p.commitment_id = c.id AND p.user_id = ?
             WHERE c.is_active = 1 AND c.deleted_at IS NULL
               AND (
                   c.owner_user_id = ?
                   OR p.user_id IS NOT NULL
                   OR (c.visibility = 'family' AND c.fam_code IN (%s))
               )
             ORDER BY c.created_at DESC",
            $placeholders
        );
        $params = array_merge([$userId, $userId], $cleanFamCodes);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return array_values($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findById(string $commitmentId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM family_commitments WHERE id = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([$commitmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed> $row
     * @param list<string> $famCodes
     */
    private function canView(array $row, string $userId, array $famCodes): bool
    {
        if ((string)$row['owner_user_id'] === $userId) {
            return true;
        }

        $vis = (string)($row['visibility'] ?? 'private');
        if ($vis === 'family' && in_array((string)$row['fam_code'], $famCodes, true)) {
            return true;
        }

        if ($vis === 'specific' || $vis === 'family') {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM commitment_participants WHERE commitment_id = ? AND user_id = ? LIMIT 1'
            );
            $stmt->execute([(string)$row['id'], $userId]);
            return $stmt->fetchColumn() !== false;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function enrichCommitment(array $row, string $userId, ?DateTimeImmutable $now = null): array
    {
        $commitmentId = (string)$row['id'];
        $tz = $this->safeTimezone((string)($row['timezone'] ?? 'Europe/London'));
        $localNow = ($now ?? new DateTimeImmutable('now', $tz))->setTimezone($tz);
        $periodUnit = (string)$row['period_unit'];
        $periodKey = $this->computePeriodKey($periodUnit, $localNow);
        $targetCount = max(1, (int)$row['target_count']);
        $completedThisPeriod = $this->countCheckinsInPeriod($commitmentId, $periodKey);

        $daysRaw = json_decode((string)($row['days_of_week'] ?? '[]'), true);
        $days = is_array($daysRaw) ? array_values(array_map('strval', $daysRaw)) : [];

        $nextOccurrence = $this->computeNextOccurrence($row, $days, $localNow);
        $recentCheckins = $this->fetchRecentCheckins($commitmentId, 5);
        $participants = $this->fetchParticipants($commitmentId);
        $streak = $this->computeStreak($commitmentId, $periodUnit, $targetCount, $localNow);

        return [
            'id' => $commitmentId,
            'owner_user_id' => (string)$row['owner_user_id'],
            'is_owner' => ((string)$row['owner_user_id'] === $userId),
            'fam_code' => (string)$row['fam_code'],
            'title' => (string)$row['title'],
            'target_statement' => (string)($row['target_statement'] ?? ''),
            'schedule_mode' => (string)$row['schedule_mode'],
            'period_unit' => $periodUnit,
            'target_count' => $targetCount,
            'days_of_week' => $days,
            'month_day' => $row['month_day'] !== null ? (int)$row['month_day'] : null,
            'preferred_time' => (string)($row['preferred_time'] ?? ''),
            'visibility' => (string)$row['visibility'],
            'linked_member_id' => $row['linked_member_id'] !== null ? (string)$row['linked_member_id'] : null,
            'in_app_reminder' => ((int)$row['in_app_reminder']) === 1,
            'timezone' => (string)($row['timezone'] ?? 'Europe/London'),
            'current_period_key' => $periodKey,
            'completed_this_period' => $completedThisPeriod,
            'progress_pct' => min(100, (int)round(($completedThisPeriod / $targetCount) * 100)),
            'period_met' => $completedThisPeriod >= $targetCount,
            'streak_periods' => $streak,
            'next_occurrence' => $nextOccurrence,
            'recent_checkins' => $recentCheckins,
            'participants' => $participants,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function validatePayload(array $payload): array
    {
        $title = trim((string)($payload['title'] ?? ''));
        if (mb_strlen($title) < 3 || mb_strlen($title) > 120) {
            throw new ValidationException('Commitment title must be between 3 and 120 characters.');
        }

        $targetStatement = trim((string)($payload['target_statement'] ?? ''));
        if (mb_strlen($targetStatement) > 500) {
            throw new ValidationException('Target statement must be 500 characters or fewer.');
        }

        $scheduleMode = (string)($payload['schedule_mode'] ?? 'fixed');
        if (!in_array($scheduleMode, ['fixed', 'flexible'], true)) {
            throw new ValidationException('Invalid schedule mode.');
        }

        $periodUnit = (string)($payload['period_unit'] ?? 'week');
        if (!in_array($periodUnit, ['week', 'month'], true)) {
            throw new ValidationException('Invalid period unit.');
        }

        $targetCount = (int)($payload['target_count'] ?? 1);
        if ($targetCount < 1 || $targetCount > 31) {
            throw new ValidationException('Target count must be between 1 and 31.');
        }

        $daysRaw = $payload['days_of_week'] ?? [];
        if (is_string($daysRaw)) {
            $decoded = json_decode($daysRaw, true);
            $daysRaw = is_array($decoded) ? $decoded : [];
        }
        $days = [];
        if (is_array($daysRaw)) {
            foreach ($daysRaw as $d) {
                $dClean = strtolower(trim((string)$d));
                if (in_array($dClean, self::VALID_DAYS, true) && !in_array($dClean, $days, true)) {
                    $days[] = $dClean;
                }
            }
        }

        $monthDay = isset($payload['month_day']) && $payload['month_day'] !== '' ? (int)$payload['month_day'] : null;
        if ($monthDay !== null && ($monthDay < 1 || $monthDay > 31)) {
            throw new ValidationException('Day of month must be between 1 and 31.');
        }

        $preferredTime = trim((string)($payload['preferred_time'] ?? ''));
        if ($scheduleMode === 'fixed') {
            if ($periodUnit === 'week' && $days === []) {
                throw new ValidationException('Please select at least one day of the week for a fixed weekly commitment.');
            }
            if ($periodUnit === 'month' && $monthDay === null) {
                throw new ValidationException('Please select a day of the month for a fixed monthly commitment.');
            }
            if ($preferredTime === '' || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $preferredTime)) {
                throw new ValidationException('Please provide a valid time (HH:MM) for a fixed commitment.');
            }
            if ($periodUnit === 'week' && count($days) > $targetCount) {
                $targetCount = count($days);
            }
        } elseif ($preferredTime !== '' && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $preferredTime)) {
            throw new ValidationException('Preferred time must be in HH:MM format.');
        }

        $visibility = (string)($payload['visibility'] ?? 'private');
        if (!in_array($visibility, ['private', 'specific', 'family'], true)) {
            throw new ValidationException('Invalid visibility option.');
        }

        $linkedMemberId = trim((string)($payload['linked_member_id'] ?? ''));
        $participantIdsRaw = $payload['participant_ids'] ?? [];
        if (is_string($participantIdsRaw)) {
            $decodedP = json_decode($participantIdsRaw, true);
            $participantIdsRaw = is_array($decodedP) ? $decodedP : [];
        }
        $participantIds = [];
        if (is_array($participantIdsRaw)) {
            foreach ($participantIdsRaw as $pid) {
                $pidStr = trim((string)$pid);
                if ($pidStr !== '' && !in_array($pidStr, $participantIds, true)) {
                    $participantIds[] = $pidStr;
                }
            }
        }

        $timezone = trim((string)($payload['timezone'] ?? 'Europe/London'));
        $this->safeTimezone($timezone);

        return [
            'title' => $title,
            'target_statement' => $targetStatement !== '' ? $targetStatement : null,
            'schedule_mode' => $scheduleMode,
            'period_unit' => $periodUnit,
            'target_count' => $targetCount,
            'days_of_week_json' => json_encode($days) ?: '[]',
            'month_day' => $monthDay,
            'preferred_time' => $preferredTime !== '' ? $preferredTime : null,
            'visibility' => $visibility,
            'linked_member_id' => $linkedMemberId !== '' ? $linkedMemberId : null,
            'participant_ids' => $participantIds,
            'in_app_reminder' => filter_var($payload['in_app_reminder'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'timezone' => $timezone !== '' ? $timezone : 'Europe/London',
        ];
    }

    /**
     * @param list<string> $participantIds
     */
    private function syncParticipants(string $commitmentId, string $ownerId, array $participantIds, ?string $linkedMemberId): void
    {
        $del = $this->pdo->prepare('DELETE FROM commitment_participants WHERE commitment_id = ?');
        $del->execute([$commitmentId]);

        $ins = $this->pdo->prepare(
            'INSERT IGNORE INTO commitment_participants (commitment_id, user_id, role) VALUES (?, ?, ?)'
        );
        $ins->execute([$commitmentId, $ownerId, 'owner']);

        $all = $participantIds;
        if ($linkedMemberId !== null && $linkedMemberId !== '' && !in_array($linkedMemberId, $all, true)) {
            $all[] = $linkedMemberId;
        }

        foreach ($all as $uid) {
            if ($uid !== $ownerId) {
                $ins->execute([$commitmentId, $uid, 'participant']);
            }
        }
    }

    private function countCheckinsInPeriod(string $commitmentId, string $periodKey): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM commitment_checkins WHERE commitment_id = ? AND period_key = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$commitmentId, $periodKey]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * @return array<string, bool>
     */
    private function getCheckinSlotsForCommitment(string $commitmentId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT slot_key FROM commitment_checkins WHERE commitment_id = ? AND slot_key IS NOT NULL AND deleted_at IS NULL'
        );
        $stmt->execute([$commitmentId]);
        $slots = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $s) {
            $slots[(string)$s] = true;
        }
        return $slots;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchRecentCheckins(string $commitmentId, int $limit): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.completed_by_user_id, c.period_key, c.slot_key, c.rating, c.reflection_note, c.completed_at,
                    per.firstName, per.lastName
             FROM commitment_checkins c
             LEFT JOIN personal per ON per.id = c.completed_by_user_id
             WHERE c.commitment_id = ? AND c.deleted_at IS NULL
             ORDER BY c.completed_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $commitmentId, PDO::PARAM_STR);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_values(array_map(static function (array $r): array {
            $name = trim(((string)($r['firstName'] ?? '')) . ' ' . ((string)($r['lastName'] ?? '')));
            return [
                'id' => (string)$r['id'],
                'completed_by_user_id' => (string)$r['completed_by_user_id'],
                'completed_by_name' => $name !== '' ? $name : 'Family Member',
                'period_key' => (string)$r['period_key'],
                'slot_key' => $r['slot_key'] !== null ? (string)$r['slot_key'] : null,
                'rating' => $r['rating'] !== null ? (int)$r['rating'] : null,
                'reflection_note' => (string)$r['reflection_note'],
                'completed_at' => (string)$r['completed_at'],
            ];
        }, $rows));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchParticipants(string $commitmentId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.user_id, p.role, per.firstName, per.lastName
             FROM commitment_participants p
             LEFT JOIN personal per ON per.id = p.user_id
             WHERE p.commitment_id = ?
             ORDER BY p.role ASC, per.firstName ASC'
        );
        $stmt->execute([$commitmentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_values(array_map(static function (array $r): array {
            $name = trim(((string)($r['firstName'] ?? '')) . ' ' . ((string)($r['lastName'] ?? '')));
            return [
                'user_id' => (string)$r['user_id'],
                'role' => (string)$r['role'],
                'name' => $name !== '' ? $name : 'Family Member',
            ];
        }, $rows));
    }

    private function computePeriodKey(string $periodUnit, DateTimeImmutable $dt): string
    {
        if ($periodUnit === 'month') {
            return $dt->format('Y-m');
        }
        return $dt->format('o-\WW');
    }

    private function computeStreak(string $commitmentId, string $periodUnit, int $targetCount, DateTimeImmutable $localNow): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT period_key, COUNT(*) AS cnt
             FROM commitment_checkins
             WHERE commitment_id = ? AND deleted_at IS NULL
             GROUP BY period_key'
        );
        $stmt->execute([$commitmentId]);
        $counts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $counts[(string)$r['period_key']] = (int)$r['cnt'];
        }

        $streak = 0;
        $currentKey = $this->computePeriodKey($periodUnit, $localNow);
        if (($counts[$currentKey] ?? 0) >= $targetCount) {
            $streak++;
        }

        $step = $periodUnit === 'month' ? '-1 month' : '-1 week';
        $cursor = $localNow->modify($step);

        for ($i = 0; $i < 52; $i++) {
            $k = $this->computePeriodKey($periodUnit, $cursor);
            if (($counts[$k] ?? 0) >= $targetCount) {
                $streak++;
                $cursor = $cursor->modify($step);
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * @param array<string, mixed> $row
     * @param list<string> $days
     * @return array<string, string>|null
     */
    private function computeNextOccurrence(array $row, array $days, DateTimeImmutable $localNow): ?array
    {
        if ((string)$row['schedule_mode'] === 'flexible') {
            return [
                'date' => $localNow->format('Y-m-d'),
                'time' => (string)($row['preferred_time'] ?? ''),
                'slot_key' => 'flex_' . $this->computePeriodKey((string)$row['period_unit'], $localNow),
                'label' => 'Flexible (' . ((string)$row['period_unit'] === 'month' ? 'this month' : 'this week') . ')',
            ];
        }

        $end = $localNow->modify('+35 days');
        $dates = $this->expandFixedDates($row, $localNow, $end, $days);
        if ($dates === []) {
            return null;
        }

        $timeStr = (string)($row['preferred_time'] ?? '09:00');
        foreach ($dates as $d) {
            $candidate = new DateTimeImmutable($d->format('Y-m-d') . ' ' . $timeStr . ':00', $localNow->getTimezone());
            if ($candidate >= $localNow->modify('-5 minutes')) {
                return [
                    'date' => $d->format('Y-m-d'),
                    'time' => $timeStr,
                    'slot_key' => $d->format('Y-m-d') . '_' . $timeStr,
                    'label' => $d->format('D j M') . ' at ' . $timeStr,
                ];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $row
     * @param list<string>|null $predecodedDays
     * @return list<DateTimeImmutable>
     */
    private function expandFixedDates(array $row, DateTimeImmutable $start, DateTimeImmutable $end, ?array $predecodedDays = null): array
    {
        $periodUnit = (string)$row['period_unit'];
        $dates = [];
        $cursor = new DateTimeImmutable($start->format('Y-m-d') . ' 00:00:00');
        $endDay = new DateTimeImmutable($end->format('Y-m-d') . ' 23:59:59');

        if ($periodUnit === 'month') {
            $monthDay = max(1, min(31, (int)($row['month_day'] ?? 1)));
            while ($cursor <= $endDay) {
                $daysInMonth = (int)$cursor->format('t');
                $targetDay = min($monthDay, $daysInMonth);
                $candidate = new DateTimeImmutable($cursor->format('Y-m-') . str_pad((string)$targetDay, 2, '0', STR_PAD_LEFT));
                if ($candidate >= $start->setTime(0, 0) && $candidate <= $endDay) {
                    $dates[] = $candidate;
                }
                $cursor = $cursor->modify('first day of next month');
            }
            return $dates;
        }

        $days = $predecodedDays;
        if ($days === null) {
            $decoded = json_decode((string)($row['days_of_week'] ?? '[]'), true);
            $days = is_array($decoded) ? $decoded : [];
        }
        $targetIsoDays = [];
        foreach ($days as $d) {
            $key = strtolower((string)$d);
            if (isset(self::DAY_MAP[$key])) {
                $targetIsoDays[self::DAY_MAP[$key]] = true;
            }
        }
        if ($targetIsoDays === []) {
            return [];
        }

        while ($cursor <= $endDay) {
            $isoDay = (int)$cursor->format('N');
            if (isset($targetIsoDays[$isoDay])) {
                $dates[] = $cursor;
            }
            $cursor = $cursor->modify('+1 day');
        }

        return $dates;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatCalendarItem(
        array $row,
        string $userId,
        string $dateYmd,
        string $timeStr,
        string $slotKey,
        bool $isCompleted,
        string $todayStr,
        ?int $periodDone = null,
        ?int $periodTarget = null
    ): array {
        $timestamp = strtotime($dateYmd) ?: time();
        $diffDays = (int)floor((strtotime($dateYmd) - strtotime($todayStr)) / 86400);
        if ($diffDays === 0) {
            $dateDifference = 'Today';
        } elseif ($diffDays === 1) {
            $dateDifference = 'Tomorrow';
        } elseif ($diffDays < 0) {
            $dateDifference = abs($diffDays) === 1 ? 'Yesterday' : abs($diffDays) . ' days ago';
        } else {
            $dateDifference = 'in ' . $diffDays . ' Days';
        }

        $desc = (string)($row['target_statement'] ?? '');
        if ($periodDone !== null && $periodTarget !== null) {
            $quotaText = "Progress: {$periodDone} / {$periodTarget} this " . (string)$row['period_unit'];
            $desc = $desc !== '' ? $desc . ' • ' . $quotaText : $quotaText;
        }

        return [
            'no' => 'cmt_' . (string)$row['id'] . '_' . $slotKey,
            'commitment_id' => (string)$row['id'],
            'slot_key' => $slotKey,
            'itemType' => 'commitment',
            'id' => (string)$row['owner_user_id'],
            'eventName' => (string)$row['title'],
            'eventDate' => date('D, j M Y', $timestamp),
            'eventDateRaw' => $dateYmd,
            'eventTime' => $timeStr,
            'eventType' => 'Commitment',
            'eventDescription' => $desc,
            'eventFrequency' => ((string)$row['schedule_mode'] === 'fixed' ? 'Fixed Commitment' : 'Flexible Target'),
            'visibility' => (string)$row['visibility'],
            'isCompleted' => $isCompleted,
            'dateDifference' => $dateDifference,
            'daysUntil' => $diffDays,
            'dayOfMonth' => date('j', $timestamp),
            'dayName' => date('D', $timestamp),
            'monthYear' => date('F Y', $timestamp),
            'monthKey' => date('Y-m', $timestamp),
            'creatorName' => 'Family Commitment',
            'creatorImg' => '/public/img/default.jpg',
            'isOwnEvent' => ((string)$row['owner_user_id'] === $userId),
            'eventLocation' => $timeStr !== '' ? 'Scheduled for ' . $timeStr : '',
            'gCalUrl' => '',
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function isDueNow(array $row, DateTimeImmutable $localNow, string $preferredTime): bool
    {
        if ((string)$row['schedule_mode'] === 'fixed') {
            if ((string)$row['period_unit'] === 'week') {
                $days = json_decode((string)($row['days_of_week'] ?? '[]'), true);
                if (!is_array($days)) {
                    return false;
                }
                $todayCode = strtolower($localNow->format('D'));
                if (!in_array($todayCode, $days, true)) {
                    return false;
                }
            } else {
                $monthDay = (int)($row['month_day'] ?? 1);
                $targetDay = min($monthDay, (int)$localNow->format('t'));
                if ((int)$localNow->format('j') !== $targetDay) {
                    return false;
                }
            }
        }

        $targetDt = DateTimeImmutable::createFromFormat('Y-m-d H:i', $localNow->format('Y-m-d') . ' ' . $preferredTime, $localNow->getTimezone());
        if ($targetDt === false) {
            return false;
        }

        $diff = abs($localNow->getTimestamp() - $targetDt->getTimestamp());
        return $diff <= 120;
    }

    /**
     * @param array<string, mixed> $row
     * @return list<string>
     */
    private function resolveReminderRecipients(array $row): array
    {
        $ownerId = (string)$row['owner_user_id'];
        $visibility = (string)($row['visibility'] ?? 'private');

        if ($visibility === 'private') {
            return [$ownerId];
        }

        $ids = [$ownerId];
        if ($visibility === 'specific') {
            $stmt = $this->pdo->prepare('SELECT user_id FROM commitment_participants WHERE commitment_id = ?');
            $stmt->execute([(string)$row['id']]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                $uidStr = (string)$uid;
                if ($uidStr !== '' && !in_array($uidStr, $ids, true)) {
                    $ids[] = $uidStr;
                }
            }
            return $ids;
        }

        $famCode = (string)($row['fam_code'] ?? '');
        if ($famCode !== '') {
            $stmt = $this->pdo->prepare('SELECT id FROM personal WHERE famCode = ? AND deleted_at IS NULL');
            $stmt->execute([$famCode]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                $uidStr = (string)$uid;
                if ($uidStr !== '' && !in_array($uidStr, $ids, true)) {
                    $ids[] = $uidStr;
                }
            }
        }

        return $ids;
    }

    private function hasReminderLog(string $commitmentId, string $userId, string $slotKey): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM commitment_reminder_logs WHERE commitment_id = ? AND user_id = ? AND slot_key = ? LIMIT 1'
        );
        $stmt->execute([$commitmentId, $userId, $slotKey]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertInAppReminderNotification(array $row, string $recipientId, string $slotKey): ?int
    {
        $commitmentId = (string)$row['id'];
        $title = (string)$row['title'];
        $timeStr = (string)($row['preferred_time'] ?? '');
        $content = $timeStr !== ''
            ? "Time for your commitment: {$title} ({$timeStr})"
            : "Time for your commitment: {$title}";

        $actionUrl = '/commitments?checkin=' . rawurlencode($commitmentId) . '&slot=' . rawurlencode($slotKey);

        $stmt = $this->pdo->prepare(
            "INSERT INTO notification (
                sender_id, receiver_id, sender_name, receiver, notification_name,
                notification_date, notification_type, notification_content, action_url, notification_status
            ) VALUES (?, ?, ?, ?, ?, ?, 'commitment_reminder', ?, ?, 'unread')"
        );
        $stmt->execute([
            (string)$row['owner_user_id'],
            $recipientId,
            'Family Commitments',
            $recipientId,
            $title,
            date('Y-m-d'),
            $content,
            $actionUrl,
        ]);

        $lastId = (int)$this->pdo->lastInsertId();
        return $lastId > 0 ? $lastId : null;
    }

    private function safeTimezone(string $tz): DateTimeZone
    {
        try {
            return new DateTimeZone($tz !== '' ? $tz : 'Europe/London');
        } catch (\Throwable $e) {
            return new DateTimeZone('Europe/London');
        }
    }

    private function generateId(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(10));
    }
}
