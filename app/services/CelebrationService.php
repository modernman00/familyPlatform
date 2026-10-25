<?php

declare(strict_types=1);

namespace App\services;

use App\model\AllMembersData;
use DateTimeImmutable;
use PDO;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;

/**
 * Family Celebrations (RFC-0008): birthday and wedding-anniversary notices,
 * the private greeting from Family Platform, and private wishes.
 *
 * Who is told: everyone in the celebrant's families (primary famCode plus
 * approved user_families codes) except the celebrant. Age is shown only when
 * the person allows it; anniversaries only when a partner opted in.
 */
final class CelebrationService
{
    /** Wishes are accepted on the day and up to this many days late. */
    public const WISH_WINDOW_DAYS = 2;
    public const MAX_WISH_LENGTH = 500;
    /** How far back the "Your wishes" card looks. */
    private const RECEIVED_WISHES_DAYS = 14;

    private static bool $tablesReady = false;

    // ------------------------------------------------------------------ daily job

    /**
     * Sends the day-before notices for tomorrow's occasions, and the on-the-day
     * notices plus the celebrant greeting for today's. Each (occasion, kind, day)
     * is claimed in celebration_notice_log first, so reruns send nothing twice
     * and a failed claim sends nothing (it is retried by the next run).
     *
     * @param PDO|null $claimDb Connection for the idempotency claim (tests inject a failing one).
     * @return array{occasions: int, notices: int, greetings: int, skipped: int, failed: int}
     */
    public static function runDaily(?DateTimeImmutable $today = null, ?PDO $claimDb = null): array
    {
        $today = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0);
        $result = ['occasions' => 0, 'notices' => 0, 'greetings' => 0, 'skipped' => 0, 'failed' => 0];

        $db = Db::connect2();
        self::ensureTables($db);

        $tomorrow = $today->modify('+1 day');
        $batches = [
            'eve' => [...self::findBirthdays($db, [$tomorrow]), ...self::findAnniversaries($db, [$tomorrow])],
            'day' => [...self::findBirthdays($db, [$today]), ...self::findAnniversaries($db, [$today])],
        ];
        $result['occasions'] = count($batches['eve']) + count($batches['day']);
        if ($result['occasions'] === 0) {
            return $result;
        }

        try {
            $claimDb ??= $db;
            self::ensureNoticeLog($claimDb);
        } catch (\Throwable $th) {
            error_log('[CelebrationService] Notice log unavailable: ' . $th->getMessage());
            $result['failed'] = $result['occasions'];
            return $result;
        }

        foreach ($batches as $kind => $occasions) {
            foreach ($occasions as $occasion) {
                try {
                    if (!self::claimNotice($claimDb, $occasion['key'], $kind, $today->format('Y-m-d'))) {
                        $result['skipped']++;
                        continue;
                    }
                } catch (\Throwable $th) {
                    error_log("[CelebrationService] Claim failed for {$occasion['key']} ({$kind}): " . $th->getMessage());
                    $result['failed']++;
                    continue;
                }

                // The claim stays even if delivery fails: at most once per day.
                try {
                    [$title, $body] = self::familyNoticeText($occasion, $kind);
                    foreach (self::recipientsFor($occasion) as $recipientId) {
                        self::notify($recipientId, $title, $body, $kind === 'day' ? self::wishUrl($occasion) : '/profilePage', "{$occasion['key']}-{$kind}", $occasion);
                    }
                    $result['notices']++;

                    if ($kind === 'day') {
                        [$gTitle, $gBody] = self::greetingText($occasion);
                        foreach ($occasion['celebrant_ids'] as $celebrantId) {
                            self::notify($celebrantId, $gTitle, $gBody, '/profilePage#celebrationWishes', "{$occasion['key']}-greeting", $occasion);
                        }
                        $result['greetings']++;
                    }
                } catch (\Throwable $th) {
                    error_log("[CelebrationService] Delivery failed for {$occasion['key']} ({$kind}): " . $th->getMessage());
                    $result['failed']++;
                }
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------ what a member sees

    /**
     * Celebrations in the viewer's families from today back to WISH_WINDOW_DAYS
     * ago (the viewer's own excluded), with whether the viewer already wished.
     *
     * @return list<array{key: string, type: string, target_id: string, title: string, subtitle: string, date_label: string, already_wished: bool}>
     */
    public static function occasionsFor(string $viewerId, ?DateTimeImmutable $today = null): array
    {
        $db = Db::connect2();
        self::ensureTables($db);
        $today = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0);
        $viewerCodes = self::familyCodesOf($db, $viewerId);
        if ($viewerCodes === []) {
            return [];
        }

        $dates = self::wishWindow($today);
        $occasions = [...self::findBirthdays($db, $dates), ...self::findAnniversaries($db, $dates)];

        $wishedStmt = $db->prepare('SELECT 1 FROM celebration_wishes WHERE occasion_key = ? AND sender_id = ? LIMIT 1');
        $list = [];
        foreach ($occasions as $occasion) {
            if (in_array($viewerId, $occasion['celebrant_ids'], true)
                || array_intersect($viewerCodes, $occasion['family_codes']) === []) {
                continue;
            }
            $wishedStmt->execute([$occasion['key'], $viewerId]);
            $daysAgo = (int) $occasion['date']->diff($today)->format('%a');
            $list[] = [
                'key' => $occasion['key'],
                'type' => $occasion['type'],
                'target_id' => $occasion['target_id'],
                'title' => $occasion['type'] === 'birthday'
                    ? "{$occasion['names']}'s birthday"
                    : "{$occasion['names']}'s wedding anniversary",
                'subtitle' => self::subtitle($occasion),
                'date_label' => match ($daysAgo) {
                    0 => 'Today',
                    1 => 'Yesterday',
                    default => "{$daysAgo} days ago",
                },
                'days_ago' => $daysAgo,
                'already_wished' => $wishedStmt->fetchColumn() !== false,
            ];
        }

        usort($list, static fn(array $a, array $b): int => $a['days_ago'] <=> $b['days_ago']);
        return array_map(static function (array $o): array {
            unset($o['days_ago']);
            return $o;
        }, $list);
    }

    /**
     * Stores a private wish for the celebrant (both partners with accounts for an
     * anniversary) and notifies them the first time. Sending again replaces the
     * earlier message without a second notification.
     *
     * @throws ValidationException bad message/type, or wishing yourself (422)
     * @throws NotFoundException   no such celebration in the wish window (404)
     * @throws ForbiddenException  celebrant is not in any of the sender's families (403)
     * @return array{recipients: int}
     */
    public static function sendWish(string $senderId, string $type, string $targetId, string $message, ?DateTimeImmutable $today = null): array
    {
        // Plain text only; collapse the gaps stripped tags leave (line breaks are kept)
        $text = trim((string) preg_replace('/[ \t]{2,}/', ' ', strip_tags($message)));
        if ($text === '' || mb_strlen($text) > self::MAX_WISH_LENGTH) {
            throw new ValidationException('Please write a message of 1 to ' . self::MAX_WISH_LENGTH . ' characters.');
        }
        if (!in_array($type, ['birthday', 'anniversary'], true) || trim($targetId) === '') {
            throw new ValidationException('Unknown celebration.');
        }

        $db = Db::connect2();
        self::ensureTables($db);
        $today = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0);
        $dates = self::wishWindow($today);
        $candidates = $type === 'birthday'
            ? self::findBirthdays($db, $dates, $targetId)
            : (ctype_digit($targetId) ? self::findAnniversaries($db, $dates, (int) $targetId) : []);
        $occasion = $candidates[0] ?? null;
        if ($occasion === null) {
            throw new NotFoundException('There is no celebration to send wishes for right now.');
        }
        if (in_array($senderId, $occasion['celebrant_ids'], true)) {
            throw new ValidationException("You can't send wishes to yourself.");
        }
        if (array_intersect(self::familyCodesOf($db, $senderId), $occasion['family_codes']) === []) {
            throw new ForbiddenException('You can only send wishes to members of your family.');
        }

        $insert = $db->prepare('INSERT IGNORE INTO celebration_wishes (occasion_key, occasion_type, recipient_id, sender_id, message) VALUES (?, ?, ?, ?, ?)');
        $update = $db->prepare('UPDATE celebration_wishes SET message = ?, read_at = NULL WHERE occasion_key = ? AND recipient_id = ? AND sender_id = ?');
        $senderName = self::displayName($db, $senderId);
        $label = $type === 'birthday' ? 'birthday' : 'anniversary';
        $emoji = $type === 'birthday' ? '🎂' : '💞';

        foreach ($occasion['celebrant_ids'] as $recipientId) {
            $insert->execute([$occasion['key'], $type, $recipientId, $senderId, $text]);
            if ($insert->rowCount() === 1) {
                self::notify(
                    $recipientId,
                    "{$emoji} {$senderName} sent you {$label} wishes",
                    mb_strimwidth($text, 0, 140, '…'),
                    '/profilePage#celebrationWishes',
                    "{$occasion['key']}-wish-{$senderId}",
                    $occasion,
                    $senderId,
                    $senderName
                );
            } else {
                $update->execute([$text, $occasion['key'], $recipientId, $senderId]);
            }
        }

        return ['recipients' => count($occasion['celebrant_ids'])];
    }

    /**
     * Wishes the user received in the last RECEIVED_WISHES_DAYS days, newest
     * first. Reading them marks them read.
     *
     * @return list<array{occasion_type: string, sender_name: string, message: string, created_at: string, read: bool}>
     */
    public static function receivedWishes(string $userId, ?DateTimeImmutable $today = null, bool $markRead = true): array
    {
        $db = Db::connect2();
        self::ensureTables($db);
        $since = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0)->modify('-' . self::RECEIVED_WISHES_DAYS . ' days');

        $stmt = $db->prepare('
            SELECT w.id, w.occasion_type, w.sender_id, w.message, w.created_at, w.read_at
            FROM celebration_wishes w
            WHERE w.recipient_id = ? AND w.created_at >= ?
            ORDER BY w.created_at DESC, w.id DESC
        ');
        $stmt->execute([$userId, $since->format('Y-m-d H:i:s')]);

        $wishes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $wishes[] = [
                'occasion_type' => (string) ($row['occasion_type'] ?? ''),
                'sender_name' => self::displayName($db, (string) ($row['sender_id'] ?? '')),
                'message' => (string) ($row['message'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'read' => ($row['read_at'] ?? null) !== null,
            ];
        }

        if ($markRead && $wishes !== []) {
            $db->prepare('UPDATE celebration_wishes SET read_at = NOW() WHERE recipient_id = ? AND read_at IS NULL')->execute([$userId]);
        }
        return $wishes;
    }

    // ------------------------------------------------------------------ settings

    /** @return array{show_age: bool, announce_anniversary: bool} */
    public static function preferences(string $userId): array
    {
        $db = Db::connect2();
        self::ensureTables($db);
        $stmt = $db->prepare('SELECT show_age, announce_anniversary FROM celebration_prefs WHERE user_id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'show_age' => is_array($row) && (int) ($row['show_age'] ?? 0) === 1,
            'announce_anniversary' => is_array($row) && (int) ($row['announce_anniversary'] ?? 0) === 1,
        ];
    }

    /**
     * Saves the given preferences; a null argument leaves that preference as it is.
     * Must not be called inside an open transaction on the shared connection
     * (the first call in a process may create the table, which commits it).
     */
    public static function savePreferences(string $userId, ?bool $showAge, ?bool $announceAnniversary): void
    {
        if ($userId === '' || ($showAge === null && $announceAnniversary === null)) {
            return;
        }
        $db = Db::connect2();
        self::ensureTables($db);
        $db->prepare('INSERT IGNORE INTO celebration_prefs (user_id) VALUES (?)')->execute([$userId]);
        if ($showAge !== null) {
            $db->prepare('UPDATE celebration_prefs SET show_age = ? WHERE user_id = ?')->execute([$showAge ? 1 : 0, $userId]);
        }
        if ($announceAnniversary !== null) {
            $db->prepare('UPDATE celebration_prefs SET announce_anniversary = ? WHERE user_id = ?')->execute([$announceAnniversary ? 1 : 0, $userId]);
        }
    }

    /**
     * Sets (or, with '' / null, clears) the wedding date on the user's current
     * marriage in $famCode. Returns false when the date is invalid or in the
     * future, or the user has no current marriage in the tree. Safe inside a
     * transaction (no DDL).
     */
    public static function saveWeddingDate(string $userId, string $famCode, ?string $date): bool
    {
        $unionId = self::currentMarriageId(Db::connect2(), $userId, $famCode);
        if ($unionId === null) {
            return false;
        }

        $date = trim((string) $date);
        if ($date === '') {
            Db::connect2()->prepare('UPDATE family_unions SET marriage_date = NULL WHERE id = ?')->execute([$unionId]);
            return true;
        }

        if (!self::isValidWeddingDate($date)) {
            return false;
        }

        Db::connect2()->prepare('UPDATE family_unions SET marriage_date = ?, marriage_year = ? WHERE id = ?')
            ->execute([$date, (int) substr($date, 0, 4), $unionId]);
        return true;
    }

    /** A real calendar date in Y-m-d form, from 1900 up to today. */
    public static function isValidWeddingDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();
        return $parsed !== false
            && $parsed->format('Y-m-d') === $date
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $parsed <= new DateTimeImmutable('today')
            && (int) $parsed->format('Y') >= 1900;
    }

    public static function weddingDate(string $userId, string $famCode): ?string
    {
        $db = Db::connect2();
        $unionId = self::currentMarriageId($db, $userId, $famCode);
        if ($unionId === null) {
            return null;
        }
        $stmt = $db->prepare('SELECT marriage_date FROM family_unions WHERE id = ?');
        $stmt->execute([$unionId]);
        $value = $stmt->fetchColumn();
        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Creates the celebration tables if missing. Runs its DDL once per process;
     * CREATE TABLE commits any open transaction, so call it before starting one.
     */
    public static function ensureTables(PDO $db): void
    {
        if (self::$tablesReady) {
            return;
        }
        $db->exec('
            CREATE TABLE IF NOT EXISTS celebration_prefs (
                user_id VARCHAR(255) NOT NULL PRIMARY KEY,
                show_age TINYINT(1) NOT NULL DEFAULT 0,
                announce_anniversary TINYINT(1) NOT NULL DEFAULT 0,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
        $db->exec('
            CREATE TABLE IF NOT EXISTS celebration_wishes (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                occasion_key VARCHAR(120) NOT NULL,
                occasion_type VARCHAR(20) NOT NULL,
                recipient_id VARCHAR(255) NOT NULL,
                sender_id VARCHAR(255) NOT NULL,
                message VARCHAR(500) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                read_at DATETIME NULL,
                UNIQUE KEY uq_wish (occasion_key, sender_id, recipient_id),
                INDEX idx_wish_recipient (recipient_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
        self::$tablesReady = true;
    }

    // ------------------------------------------------------------------ internals

    /** Kept separate from ensureTables() so the claim store can be a different connection. */
    private static function ensureNoticeLog(PDO $db): void
    {
        $db->exec('
            CREATE TABLE IF NOT EXISTS celebration_notice_log (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                occasion_key VARCHAR(120) NOT NULL,
                notice_kind VARCHAR(10) NOT NULL,
                sent_on DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_celebration_notice (occasion_key, notice_kind, sent_on)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
    }

    private static function claimNotice(PDO $db, string $key, string $kind, string $sentOn): bool
    {
        $stmt = $db->prepare('INSERT IGNORE INTO celebration_notice_log (occasion_key, notice_kind, sent_on) VALUES (?, ?, ?)');
        if ($stmt === false) {
            throw new \RuntimeException('Could not prepare celebration claim.');
        }
        $stmt->execute([$key, $kind, $sentOn]);
        return $stmt->rowCount() === 1;
    }

    /**
     * The date a (day, month) is celebrated in $year: 29 February falls on 28
     * February in non-leap years. Null for impossible dates.
     */
    private static function celebrationDate(int $day, int $month, int $year): ?DateTimeImmutable
    {
        if ($month === 2 && $day === 29 && !checkdate(2, 29, $year)) {
            $day = 28;
        }
        if (!checkdate($month, $day, $year)) {
            return null;
        }
        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    /**
     * Day-of-month values that can land on any of $dates (adds 29 for a
     * non-leap 28 February) so the SQL can narrow rows before PHP checks months.
     *
     * @param list<DateTimeImmutable> $dates
     * @return list<int>
     */
    private static function candidateDays(array $dates): array
    {
        $days = [];
        foreach ($dates as $date) {
            $days[] = (int) $date->format('j');
            if ($date->format('m-d') === '02-28' && !checkdate(2, 29, (int) $date->format('Y'))) {
                $days[] = 29;
            }
        }
        return array_values(array_unique($days));
    }

    /** @return list<DateTimeImmutable> today back to WISH_WINDOW_DAYS ago */
    private static function wishWindow(DateTimeImmutable $today): array
    {
        $dates = [];
        for ($i = 0; $i <= self::WISH_WINDOW_DAYS; $i++) {
            $dates[] = $today->modify("-{$i} days");
        }
        return $dates;
    }

    /**
     * Live members whose birthday is celebrated on one of $dates.
     *
     * "Live" is decided by account.status alone: RFC-0006 found deleted_at
     * auto-stamped on live rows, and a genuine deletion always sets status to
     * erased / pending_deletion, so deleted_at is not trusted here.
     *
     * @param list<DateTimeImmutable> $dates
     * @return list<array{key: string, type: string, target_id: string, date: DateTimeImmutable, names: string, first_names: string, celebrant_ids: list<string>, family_codes: list<string>, age: ?int, years: ?int, family_code: string}>
     */
    private static function findBirthdays(PDO $db, array $dates, ?string $onlyUserId = null): array
    {
        $days = self::candidateDays($dates);
        if ($days === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($days), '?'));
        $sql = "SELECT p.id, p.firstName, p.lastName, p.day, p.month, p.year, p.famCode, COALESCE(cp.show_age, 0) AS show_age
                FROM personal p
                INNER JOIN account a ON a.id = p.id
                LEFT JOIN celebration_prefs cp ON cp.user_id = p.id
                WHERE COALESCE(a.status, '') NOT IN ('erased', 'pending_deletion')
                  AND p.day IN ($in)";
        $params = $days;
        if ($onlyUserId !== null) {
            $sql .= ' AND p.id = ?';
            $params[] = $onlyUserId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $found = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $userId = (string) ($row['id'] ?? '');
            $month = MemoryMilestoneService::parseMonthToNumber($row['month'] ?? null);
            $day = (int) ($row['day'] ?? 0);
            if ($userId === '' || $month === 0 || $day === 0 || isset($found[$userId])) {
                continue;
            }
            foreach ($dates as $date) {
                $celebrated = self::celebrationDate($day, $month, (int) $date->format('Y'));
                if ($celebrated === null || $celebrated->format('Y-m-d') !== $date->format('Y-m-d')) {
                    continue;
                }
                $birthYear = (int) ($row['year'] ?? 0);
                $firstName = trim((string) ($row['firstName'] ?? ''));
                $fullName = trim($firstName . ' ' . (string) ($row['lastName'] ?? ''));
                $found[$userId] = [
                    'key' => "birthday:{$userId}:" . $date->format('Y'),
                    'type' => 'birthday',
                    'target_id' => $userId,
                    'date' => $date,
                    'names' => $fullName !== '' ? $fullName : 'A family member',
                    'first_names' => $firstName !== '' ? $firstName : 'there',
                    'celebrant_ids' => [$userId],
                    'family_codes' => self::familyCodesOf($db, $userId),
                    'age' => ((int) ($row['show_age'] ?? 0) === 1 && $birthYear > 1900) ? (int) $date->format('Y') - $birthYear : null,
                    'years' => null,
                    'family_code' => (string) ($row['famCode'] ?? ''),
                ];
                break;
            }
        }
        return array_values($found);
    }

    /**
     * Current marriages, opted in by at least one partner, whose anniversary is
     * celebrated on one of $dates.
     *
     * @param list<DateTimeImmutable> $dates
     * @return list<array{key: string, type: string, target_id: string, date: DateTimeImmutable, names: string, first_names: string, celebrant_ids: list<string>, family_codes: list<string>, age: ?int, years: ?int, family_code: string}>
     */
    private static function findAnniversaries(PDO $db, array $dates, ?int $onlyUnionId = null): array
    {
        $days = self::candidateDays($dates);
        if ($days === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($days), '?'));
        $sql = "SELECT fu.id, fu.family_code, fu.marriage_date,
                       n1.user_id AS u1, n1.first_name AS f1, n2.user_id AS u2, n2.first_name AS f2
                FROM family_unions fu
                INNER JOIN family_nodes n1 ON n1.id = fu.partner_1_id
                INNER JOIN family_nodes n2 ON n2.id = fu.partner_2_id
                LEFT JOIN celebration_prefs cp1 ON cp1.user_id = n1.user_id
                LEFT JOIN celebration_prefs cp2 ON cp2.user_id = n2.user_id
                WHERE fu.union_type = 'married'
                  AND fu.is_current = 1
                  AND fu.marriage_date IS NOT NULL
                  AND fu.partner_1_id <> fu.partner_2_id
                  AND n1.is_deceased = 0 AND n2.is_deceased = 0
                  AND (COALESCE(cp1.announce_anniversary, 0) = 1 OR COALESCE(cp2.announce_anniversary, 0) = 1)
                  AND DAY(fu.marriage_date) IN ($in)";
        $params = $days;
        if ($onlyUnionId !== null) {
            $sql .= ' AND fu.id = ?';
            $params[] = $onlyUnionId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $found = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $wedding = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($row['marriage_date'] ?? ''));
            if ($wedding === false) {
                continue;
            }
            foreach ($dates as $date) {
                $years = (int) $date->format('Y') - (int) $wedding->format('Y');
                $celebrated = self::celebrationDate((int) $wedding->format('j'), (int) $wedding->format('n'), (int) $date->format('Y'));
                if ($years < 1 || $celebrated === null || $celebrated->format('Y-m-d') !== $date->format('Y-m-d')) {
                    continue;
                }
                $celebrants = self::liveAccounts($db, array_values(array_filter(
                    [(string) ($row['u1'] ?? ''), (string) ($row['u2'] ?? '')],
                    static fn(string $u): bool => $u !== ''
                )));
                if ($celebrants === []) {
                    break;
                }
                $codes = [(string) ($row['family_code'] ?? '')];
                foreach ($celebrants as $celebrantId) {
                    $codes = [...$codes, ...self::familyCodesOf($db, $celebrantId)];
                }
                $first1 = trim((string) ($row['f1'] ?? ''));
                $first2 = trim((string) ($row['f2'] ?? ''));
                $names = trim($first1 . ' & ' . $first2, ' &');
                $unionId = (string) ($row['id'] ?? '');
                $found[] = [
                    'key' => "anniversary:{$unionId}:" . $date->format('Y'),
                    'type' => 'anniversary',
                    'target_id' => $unionId,
                    'date' => $date,
                    'names' => $names !== '' ? $names : 'A family couple',
                    'first_names' => $names !== '' ? $names : 'you both',
                    'celebrant_ids' => $celebrants,
                    'family_codes' => array_values(array_unique(array_filter($codes, static fn(string $c): bool => $c !== ''))),
                    'age' => null,
                    'years' => $years,
                    'family_code' => (string) ($row['family_code'] ?? ''),
                ];
                break;
            }
        }
        return $found;
    }

    /**
     * @param list<string> $userIds
     * @return list<string> those with a live account and profile
     */
    private static function liveAccounts(PDO $db, array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $db->prepare("SELECT DISTINCT a.id FROM account a INNER JOIN personal p ON p.id = a.id
            WHERE a.id IN ($in) AND COALESCE(a.status, '') NOT IN ('erased', 'pending_deletion')");
        $stmt->execute($userIds);
        $live = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        return array_values(array_filter($userIds, static fn(string $u): bool => in_array($u, $live, true)));
    }

    /** @return list<string> the user's primary family plus approved joined families */
    private static function familyCodesOf(PDO $db, string $userId): array
    {
        if ($userId === '') {
            return [];
        }
        $codes = [];
        $stmt = $db->prepare('SELECT famCode FROM personal WHERE id = ? AND famCode IS NOT NULL AND famCode <> \'\'');
        $stmt->execute([$userId]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $code) {
            $codes[] = (string) $code;
        }
        $stmt = $db->prepare("SELECT family_code FROM user_families WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$userId]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $code) {
            $codes[] = (string) $code;
        }
        return array_values(array_unique(array_filter($codes, static fn(string $c): bool => $c !== '')));
    }

    /**
     * Everyone in the occasion's families except the celebrants.
     *
     * @param array{celebrant_ids: list<string>, family_codes: list<string>} $occasion
     * @return list<string>
     */
    private static function recipientsFor(array $occasion): array
    {
        $ids = [];
        foreach ($occasion['family_codes'] as $code) {
            foreach (AllMembersData::AllMembersEmailByFamCode($code, $occasion['celebrant_ids']) as $member) {
                $id = is_scalar($member['id'] ?? null) ? (string) $member['id'] : '';
                if ($id !== '' && !in_array($id, $occasion['celebrant_ids'], true)) {
                    $ids[$id] = true;
                }
            }
        }
        return array_map('strval', array_keys($ids));
    }

    private static function displayName(PDO $db, string $userId): string
    {
        $stmt = $db->prepare('SELECT firstName, lastName FROM personal WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = is_array($row) ? trim((string) ($row['firstName'] ?? '') . ' ' . (string) ($row['lastName'] ?? '')) : '';
        return $name !== '' ? $name : 'A family member';
    }

    private static function currentMarriageId(PDO $db, string $userId, string $famCode): ?int
    {
        if ($userId === '' || $famCode === '') {
            return null;
        }
        $stmt = $db->prepare("
            SELECT fu.id FROM family_unions fu
            INNER JOIN family_nodes n ON (n.id = fu.partner_1_id OR n.id = fu.partner_2_id)
            WHERE n.user_id = ? AND n.family_code = ?
              AND fu.partner_1_id <> fu.partner_2_id
              AND fu.is_current = 1 AND fu.union_type = 'married'
            ORDER BY fu.id DESC LIMIT 1
        ");
        $stmt->execute([$userId, $famCode]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }

    /** @param array{type: string, target_id: string} $occasion */
    private static function wishUrl(array $occasion): string
    {
        return '/profilePage#celebrate-' . $occasion['type'] . '-' . rawurlencode($occasion['target_id']);
    }

    /** @param array{type: string, age: ?int, years: ?int} $occasion */
    private static function subtitle(array $occasion): string
    {
        if ($occasion['type'] === 'birthday') {
            return $occasion['age'] !== null ? "Turns {$occasion['age']}" : 'Birthday';
        }
        return $occasion['years'] !== null ? "{$occasion['years']} years married" : 'Wedding anniversary';
    }

    /**
     * @param array{type: string, names: string, age: ?int, years: ?int} $occasion
     * @return array{0: string, 1: string} [title, body]
     */
    private static function familyNoticeText(array $occasion, string $kind): array
    {
        $names = $occasion['names'];
        if ($occasion['type'] === 'birthday') {
            $age = $occasion['age'];
            if ($kind === 'eve') {
                return [
                    $age !== null ? "🎂 {$names} turns {$age} tomorrow" : "🎂 {$names}'s birthday is tomorrow",
                    'Get ready to send your wishes tomorrow.',
                ];
            }
            return [
                $age !== null ? "🎂 {$names} turns {$age} today!" : "🎂 It's {$names}'s birthday today!",
                'Send your wishes 🎉',
            ];
        }

        $years = $occasion['years'];
        if ($kind === 'eve') {
            return [
                "💞 {$names}'s wedding anniversary is tomorrow",
                $years !== null ? "{$years} years together. Get ready to send your wishes." : 'Get ready to send your wishes.',
            ];
        }
        return [
            $years !== null ? "💞 {$names} have been married {$years} years today!" : "💞 It's {$names}'s wedding anniversary today!",
            'Send your wishes 🎉',
        ];
    }

    /**
     * @param array{type: string, first_names: string} $occasion
     * @return array{0: string, 1: string} [title, body]
     */
    private static function greetingText(array $occasion): array
    {
        if ($occasion['type'] === 'birthday') {
            return [
                "🎉 Happy birthday, {$occasion['first_names']}!",
                'Happy birthday from all of us at Family Platform. Wishes from your family will appear on your profile page.',
            ];
        }
        return [
            "💞 Happy anniversary, {$occasion['first_names']}!",
            'Happy anniversary from all of us at Family Platform. Wishes from your family will appear on your profile page.',
        ];
    }

    /** @param array{key: string, family_code: string} $occasion */
    private static function notify(
        string $userId,
        string $title,
        string $body,
        string $actionUrl,
        string $tag,
        array $occasion,
        string $senderId = 'system',
        string $senderName = 'Family Platform'
    ): void {
        NotificationOrchestrator::dispatch(
            userId: $userId,
            category: 'social',
            priority: 'medium',
            title: $title,
            body: $body,
            actionUrl: $actionUrl,
            tag: (string) preg_replace('/[^A-Za-z0-9_-]/', '-', $tag),
            familyCode: $occasion['family_code'] !== '' ? $occasion['family_code'] : null,
            metadata: [
                'sender_id' => $senderId,
                'sender_name' => $senderName,
                'occasion_key' => $occasion['key'],
            ]
        );
    }
}
