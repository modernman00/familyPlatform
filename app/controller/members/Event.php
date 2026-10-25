<?php
declare(strict_types=1);

namespace App\controller\members;

use App\classes\{ AllFunctionalities, Insert, Select, PushNotificationClass, Pusher };
use App\model\{EmailData, AllMembersData};
use App\services\NotificationOrchestrator;
use Src\CheckToken;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;
use Src\functionality\SubmitPostData;
use Src\SelectFn;
use Src\SendEmail;
use Src\UpdateFn;

final class Event extends AllMembersData
{
    /**
     * Narrows a checkInput()-style result (which may come back as array|string|null)
     * down to the plain string identifier the model/db layer requires.
     */
    private static function asString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        throw new ValidationException('Invalid value supplied.');
    }
    /**
     * @throws \Throwable
     */
    /**
     * Submit event form to the database
     * /member/profilePage/event
     *
     * @return void
     */

    public static function submitEvent()
    {
        try {
            CheckToken::tokenCheck();
            $retunLastId = SubmitPostData::submitToOneTablenImage(
                table: 'events',
                minMaxData: [
                    'data' => ['eventName',  'eventDescription'],
                    'min'  => [3,  0],
                    'max'  => [100, 500]
                ],
                removeKeys: ['submit', 'token', 'grecaptcharesponse', 'awnqylrds9sip'],
                newInput: [
                    'eventCode' => self::generateFamCode(),
                    'id' => checkInput($_SESSION['id'])
                ],
                optionalFields: ['eventDescription']
            );

            if (!$retunLastId) {
                return;
            }

            msgSuccess(200, "Event submitted", $retunLastId);
        } catch (\Throwable $th) {
            showError($th);
            return;
        }

        // Notify only once the event is saved. The response has already been
        // sent, so a slow mail/push provider never delays the creator, and a
        // delivery failure can never turn a saved event into an error.
        self::releaseClient();
        try {
            self::notifyFamilyOfNewEvent((string) $retunLastId);
        } catch (\Throwable $th) {
            error_log('[Event] New-event notification failed for event ' . $retunLastId . ': ' . $th->getMessage());
        }
    }

    /**
     * Finish the HTTP response so the browser is not kept waiting while the
     * family is notified (PHP-FPM and LiteSpeed both expose a finish call).
     */
    private static function releaseClient(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        }
    }

    /**
     * Tells everyone in the event's family (except its creator) about a newly
     * saved event: in-app row + real-time toast + WebPush via the orchestrator,
     * and one bulk email. Reads the saved row, so only persisted, validated
     * values are ever sent.
     */
    private static function notifyFamilyOfNewEvent(string $eventNo): void
    {
        $event = SelectFn::selectOneRow('events', 'no', $eventNo);
        if (!is_array($event) || $event === []) {
            return;
        }

        $creatorId = is_scalar($event['id'] ?? null) ? (string) $event['id'] : '';
        $famCode = is_scalar($event['eventCode'] ?? null) ? (string) $event['eventCode'] : '';
        if ($famCode === '' || $creatorId === '') {
            return;
        }

        $senderName = self::getSenderName();
        $senderNameStr = is_string($senderName) && trim($senderName) !== '' ? $senderName : 'A family member';
        $eventName = is_scalar($event['eventName'] ?? null) ? (string) $event['eventName'] : 'Family Event';
        $eventDate = is_scalar($event['eventDate'] ?? null) ? (string) $event['eventDate'] : '';
        $eventType = is_scalar($event['eventType'] ?? null) ? (string) $event['eventType'] : 'Event';
        $eventDescription = is_scalar($event['eventDescription'] ?? null) ? (string) $event['eventDescription'] : '';
        $formattedDate = $eventDate !== '' ? dateFormat($eventDate) : '';

        $membersToNotify = self::AllMembersEmailByFamCode($famCode, $creatorId);

        foreach ($membersToNotify as $member) {
            $targetUid = is_scalar($member['id'] ?? null) ? (string) $member['id'] : '';
            if ($targetUid === '') {
                continue;
            }
            NotificationOrchestrator::dispatch(
                userId: $targetUid,
                category: 'social',
                priority: 'medium',
                title: "🎉 New Event: {$eventName}",
                body: "{$senderNameStr} added '{$eventName}' on {$formattedDate}",
                actionUrl: '/profilePage#eventHeader',
                tag: 'event-' . $eventNo,
                familyCode: $famCode,
                metadata: [
                    'sender_id' => $creatorId,
                    'sender_name' => $senderNameStr,
                    'event_no' => $eventNo,
                    'eventName' => $eventName,
                    'eventDate' => $eventDate,
                    'eventType' => $eventType,
                    'eventDescription' => $eventDescription,
                ]
            );
        }

        $emailsToNotify = array_values(array_filter(
            array_map(static fn(mixed $e): string => is_scalar($e) ? (string) $e : '', array_column($membersToNotify, 'email')),
            static fn(string $e): bool => $e !== ''
        ));
        if ($emailsToNotify !== []) {
            self::sendBulkNotification(
                data: [
                    'eventName' => $eventName,
                    'eventDate' => $eventDate,
                    'eventType' => $eventType,
                    'eventDescription' => $eventDescription,
                    'firstName' => $senderNameStr,
                    'lastName' => '',
                    'emailHTMLContent' => "{$senderNameStr} has added a new upcoming family event: {$eventName} scheduled for {$formattedDate}.",
                ],
                subject: "New Family Event: {$eventName}",
                email: $emailsToNotify
            );
        }
    }

    private static function generateFamCode(): array|string|null
    {
        return checkInput($_SESSION['famCode']);
    }

    private static function getSenderName(): array|string|null
    {
        return checkInput($_SESSION['fName'] . ' ' . $_SESSION['lName']);
    }

    // Filter members based on the event famCode
    private static function filterMemberByFamCode(string $eventFamCode): array
    {
        if ($eventFamCode === '') {
            return [];
        }

        return self::AllMembersEmailByFamCode($eventFamCode);
    }

    /**
     * @deprecated since RFC-0007. submitEvent() now notifies the family once the
     * event is saved. Kept for one release because a service-worker-cached
     * createEvent.js still awaits this POST; a 404 would show the user an error
     * for an event that did save. It sends nothing and returns a null message,
     * so the old client also skips its follow-up GET broadcast.
     * POST /member/notification/event
     */
    public static function PostEventNotificationBar(): void
    {
        try {
            CheckToken::tokenCheck();
            msgSuccess(code: 200, msg: null);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * @deprecated since RFC-0007. It used to load any notification row by number
     * and broadcast it to the caller's channels (a cross-family leak). The
     * orchestrator now delivers real-time toasts itself. Kept as a no-op for
     * cached clients.
     * GET /member/notification/event
     */
    public static function GetEventNotificationBar(): void
    {
        msgSuccess(code: 200, msg: null);
    }

    /**
     * Retrieves a notification by its notification number.
     *
     * This function fetches a single notification from the 'notification' table 
     * using the provided notification number from the GET request. 
     * Upon successful retrieval, it returns the notification data and 
     * sends a success message. In case of an exception, it handles the error appropriately.
     *
     * @return void
     */
    public static function GetAllNotificationBar()
    {
        try {

            $notificationNo = checkInput($_GET['notificationNo']);

            $query = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'notification', identifier1: 'no');

            $result = Select::selectFn2($query, [$notificationNo]);

            msgSuccess(200, $result);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /** Days-before-event => reminder kind recorded in event_reminder_log. */
    private const REMINDER_KINDS = [7 => 'week', 1 => 'day', 0 => 'today'];

    /**
     * Sends the 7-day, 1-day and on-the-day reminders for every live event to
     * the members of the event's family. Run once a day by
     * cron/event_reminder_cron.php.
     *
     * Each (event, kind, day) is claimed in event_reminder_log before anything
     * is sent, so a rerun or an overlapping run sends nothing twice. If the
     * claim cannot be written, that reminder is skipped (and retried by the
     * next run) rather than risk a duplicate.
     *
     * @param \DateTimeImmutable|null $today Reference day (tests pin it); defaults to today.
     * @param \PDO|null $claimDb Connection used for the idempotency claim (tests inject a failing one).
     * @return array{events: int, sent: int, skipped: int, failed: int}
     */
    public static function runReminders(?\DateTimeImmutable $today = null, ?\PDO $claimDb = null): array
    {
        $today ??= new \DateTimeImmutable('today');
        $todayStr = $today->format('Y-m-d');
        $result = ['events' => 0, 'sent' => 0, 'skipped' => 0, 'failed' => 0];

        $dates = [];
        foreach (array_keys(self::REMINDER_KINDS) as $daysAhead) {
            $dates[] = $today->modify("+{$daysAhead} days")->format('Y-m-d');
        }

        $allEventData = parent::getAllEventData($dates);
        $result['events'] = count($allEventData);
        if ($allEventData === []) {
            return $result;
        }

        try {
            $claimDb ??= \Src\Db::connect2();
            self::ensureReminderLogTable($claimDb);
        } catch (\Throwable $th) {
            error_log('[Event] Reminder claim store unavailable: ' . $th->getMessage());
            $result['failed'] = $result['events'];
            return $result;
        }

        foreach ($allEventData as $eventData) {
            $eventNo = is_scalar($eventData['no'] ?? null) ? (string) $eventData['no'] : '';
            $famCode = is_scalar($eventData['famCode'] ?? null) ? (string) $eventData['famCode'] : '';
            $eventDate = is_scalar($eventData['eventDate'] ?? null) ? (string) $eventData['eventDate'] : '';
            if ($eventNo === '' || $famCode === '' || $eventDate === '') {
                $result['skipped']++;
                continue;
            }

            $daysAhead = (int) $today->diff(new \DateTimeImmutable($eventDate))->format('%r%a');
            $kind = self::REMINDER_KINDS[$daysAhead] ?? null;
            if ($kind === null) {
                $result['skipped']++;
                continue;
            }

            try {
                if (!self::claimReminder($claimDb, $eventNo, $kind, $todayStr)) {
                    $result['skipped']++;
                    continue;
                }
            } catch (\Throwable $th) {
                error_log("[Event] Reminder claim failed for event {$eventNo} ({$kind}): " . $th->getMessage());
                $result['failed']++;
                continue;
            }

            // One event's delivery failure must not stop the others. The claim
            // stays, so a failed send is not retried today (at most once).
            try {
                $membersToNotify = self::filterMemberByFamCode($famCode);
                self::checkEventDiffAndNotifyAll(
                    data: $eventData,
                    email: array_column($membersToNotify, 'email'),
                    id: array_column($membersToNotify, 'id'),
                    today: $todayStr
                );
                $result['sent']++;
            } catch (\Throwable $th) {
                error_log("[Event] Reminder delivery failed for event {$eventNo} ({$kind}): " . $th->getMessage());
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * @deprecated since RFC-0007; the cron script calls runReminders(). Kept so
     * any other caller still gets the same behaviour, now with a JSON summary.
     */
    public static function sendReminder(): void
    {
        try {
            msgSuccess(200, self::runReminders());
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private static function ensureReminderLogTable(\PDO $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS event_reminder_log (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_no INT NOT NULL,
                reminder_kind VARCHAR(10) NOT NULL,
                sent_on DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_event_reminder (event_no, reminder_kind, sent_on)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /**
     * Atomically claims one reminder. Returns true only for the single run that
     * inserted the row; every other run sees the UNIQUE key and gets false.
     */
    private static function claimReminder(\PDO $db, string $eventNo, string $kind, string $sentOn): bool
    {
        $stmt = $db->prepare('INSERT IGNORE INTO event_reminder_log (event_no, reminder_kind, sent_on) VALUES (?, ?, ?)');
        if ($stmt === false) {
            throw new \RuntimeException('Could not prepare reminder claim.');
        }
        $stmt->execute([$eventNo, $kind, $sentOn]);
        return $stmt->rowCount() === 1;
    }

    private static function sendBulkNotification(array $data, string $subject, array $email): void
    {
        $validEmails = array_values(array_filter(
            array_map(static fn(mixed $e): string => is_scalar($e) ? trim((string) $e) : '', $email),
            static fn(string $addr): bool => $addr !== '' && filter_var($addr, FILTER_VALIDATE_EMAIL) !== false
        ));

        if ($validEmails === []) {
            return;
        }

        if (!defined('PASS')) {
            $notifyCustomer = new EmailData('member');
            $notifyCustomer->getEmailData();
        }

        ob_start();
        view(path: 'msg/events/event', data: compact('data'));
        $emailContent = (string) ob_get_clean();

        try {
            SendEmail::sendBulkEmail(
                emailAddresses: $validEmails,
                subject: $subject,
                message: $emailContent
            );
        } catch (\Throwable $e) {
            error_log('[Event] Bulk email notification failed: ' . $e->getMessage());
        }
    }
    /**
     * this function extends the event date on the due date
     *
     * @param array<string, mixed> $data
     */
    private static function extendEventByFrequency(array $data)
    {

        if ($data['eventFrequency']) {

            $no = checkInput($data['no']) ?? null;

            $eventDate = checkInput($data['eventDate']);

            if ($data['eventFrequency'] != "One-off" && is_string($no)) {


                return match ($data['eventFrequency']) {
                    "Annually" => self::processEventUpdate(dateEvent: $eventDate, extendBy: '1 year', no: $no),
                    "Monthly" => self::processEventUpdate(dateEvent: $eventDate, extendBy: '1 month', no: $no),
                    "Weekly" => self::processEventUpdate(dateEvent: $eventDate, extendBy: '1 week', no: $no),
                    default => false,
                };
            }
        } else {
            return false;
        }
    }

    // this is a sub function to extendEventByFrequency

    /**
     * @param array|null|string $dateEvent
     */
    private static function processEventUpdate(array|string|null $dateEvent, string $extendBy, string $no)
    {
        $newEventDate = modifyDate($dateEvent, $extendBy, 'date_add')['date'];

        return AllFunctionalities::update2('events', 'eventDate', $newEventDate, 'no', $no);
    }

    /**
     * Upcoming events for the caller's own families only. (It used to return
     * every family's events for the next 7 days to any logged-in member.)
     * GET /member/getEventData
     */
    public static function getEventData(): void
    {
        $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';
        $famCodes = $_SESSION['famCodes'] ?? ($famCode !== '' ? [$famCode] : []);
        $allEventData = allMembersData::getEventDataByFamCode((array) $famCodes);

        \msgSuccess(201, $allEventData);
    }

    /**
     * @return void
     * /member/getEventDataByNo?eventNo={eventNo}
     */
    public static function getEventDataByNoController()
    {
        $eventNo = checkInput($_GET['eventNo']);
        $allEventData = allMembersData::getEventDataByNo($eventNo);

        $eventFamCode = self::generateFamCode();

        $filteredEventDataByFamCode = ($eventFamCode === $allEventData[0]['famCode']) ? $allEventData[0] : [];

        msgSuccess(
            code: 201,
            msg: $filteredEventDataByFamCode
        );
    }

    /**
     * Soft-deletes an event. Author-only.
     * DELETE /member/profilePage/event/{eventNo}
     */
    public static function deleteEvent(int|string $eventNo): void
    {
        try {
            CheckToken::tokenCheck();

            $eventNo = self::asString(checkInput($eventNo));
            $userId = checkInput($_SESSION['id']);

            // Not date-window scoped (unlike getEventDataByNo, which is for the
            // "upcoming events" widget) — we need the row regardless of date.
            $event = SelectFn::selectOneRow('events', 'no', $eventNo);

            if (!$event || $userId !== $event['id']) {
                throw new ForbiddenException('You can only delete your own events.');
            }

            AllFunctionalities::update2('events', 'deleted_at', date('Y-m-d H:i:s'), 'no', $eventNo);

            Pusher::broadcastToFamily((string) ($event['eventCode'] ?? $_SESSION['famCode'] ?? ''), 'delete-event', ['no' => $eventNo]);

            msgSuccess(200, "event deleted successfully");
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Edits an event's fields. Author-only.
     * PUT /member/profilePage/event/{eventNo}
     */
    public static function updateEvent(int|string $eventNo): void
    {
        try {
            CheckToken::tokenCheck();

            $eventNo = self::asString(checkInput($eventNo));
            $userId = checkInput($_SESSION['id']);

            $event = SelectFn::selectOneRow('events', 'no', $eventNo);

            if (!$event || $userId !== $event['id']) {
                throw new ForbiddenException('You can only edit your own events.');
            }

            // PHP only populates $_POST for the literal POST verb — PUT bodies
            // have to be read manually, so the frontend sends JSON for edits.
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput !== false ? $rawInput : '', true) ?? [];

            $data = [
                'eventName' => checkInput($input['eventName'] ?? null),
                'eventDate' => checkInput($input['eventDate'] ?? null),
                'eventType' => checkInput($input['eventType'] ?? null),
                'eventDescription' => checkInput($input['eventDescription'] ?? null),
                'eventFrequency' => checkInput($input['eventFrequency'] ?? null),
            ];

            UpdateFn::updateMultiple('events', array_merge($data, ['no' => $eventNo]), 'no');

            // Mirror rightColumn.blade.php's @php enrichment so the broadcast
            // carries the same shape the sidebar already renders.
            $eventDateForDiff = is_string($data['eventDate']) ? $data['eventDate'] : '';
            $dateDiff = dateDifferenceInt(date('Y-m-d'), $eventDateForDiff);
            if ($dateDiff < 0) {
                $dateDifference = abs($dateDiff) === 1 ? 'Yesterday' : abs($dateDiff) . ' days ago';
            } else {
                $getDateDiff = number2word($dateDiff);
                $dateDifference = ($dateDiff === 0 || strtolower($getDateDiff) === 'zero') ? 'Today' : (($dateDiff === 1 || strtolower($getDateDiff) === 'one') ? 'Tomorrow' : "in $getDateDiff Days");
            }

            Pusher::broadcastToFamily((string) ($event['eventCode'] ?? $_SESSION['famCode'] ?? ''), 'update-event', [
                'no' => $eventNo,
                'eventName' => $data['eventName'],
                'eventDate' => dateFormat($data['eventDate']),
                'eventType' => $data['eventType'],
                'dateDifference' => $dateDifference,
            ]);

            msgSuccess(200, "event updated successfully");
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Undocumented function
     *
     * @param array $data - the array must have the following keys: eventDate, eventName and firstName
     *
     * @return void
     */
    private static function checkEventDiffAndNotifyAll(array $data, array $email, ?array $id = null, ?string $today = null): void
    {

        $diffEventAndTodayDate = dateDifference(
            date1: $today ?? date(format: 'Y-m-d'),
            date2: $data['eventDate']
        );
        $eventDayFormatted = dateFormat($data['eventDate']);
        $subject = $data['eventName'];
        $eventHost = "{$data['firstName']} {$data['lastName']}";
        $eventDescription = $data['eventDescription'];
        $eventInformation = "Kindly be informed that this event is scheduled to be held on $eventDayFormatted. Please contact the $eventHost for more information. Event Description: $eventDescription";
        $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: (getenv('MIX_APP_URL2') ?: 'https://familyplatform.test')), '/');
        $url = $baseUrl . "/member/ProfilePage";


        switch ($diffEventAndTodayDate) {
            case "+7 days":
                $subject .= " is on $eventDayFormatted";
                $data['emailHTMLContent'] = "$subject in seven days. $eventInformation ";
                PushNotificationClass::sendPushNotification(userId: $id, message: $subject, url: $url); // this is to push notification for the PWA application 

                self::sendBulkNotification(data: $data, subject: $subject, email: $email);
                break;
            case "+1 days":
                $subject .= " is tomorrow, $eventDayFormatted";
                $data['emailHTMLContent'] = "$subject. $eventInformation";
                PushNotificationClass::sendPushNotification(userId: $id, message: $subject, url: $url);
                self::sendBulkNotification(data: $data, subject: $subject, email: $email);
                break;
            case "+0 days":
                $subject .= " is today, $eventDayFormatted";
                $data['emailHTMLContent'] = "$subject. $eventInformation";
                PushNotificationClass::sendPushNotification(userId: $id, message: $subject, url: $url);
                self::sendBulkNotification(data: $data, subject: $subject, email: $email);
                self::extendEventByFrequency(data: $data);
                break;
            default:
                break;
        }

        if (in_array($diffEventAndTodayDate, ['+7 days', '+1 days', '+0 days'], true) && !empty($id)) {
            try {
                $db = \Src\Db::connect2();
                $stmt = $db->prepare("
                    INSERT INTO notification 
                    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                    VALUES (?, ?, ?, ?, 'Event', ?, '/profilePage#eventHeader', 'new', NOW())
                ");
                $recipientList = $id;
                $senderId = is_scalar($data['id'] ?? null) ? (string)$data['id'] : 'system';
                $senderName = trim($eventHost) !== '' ? $eventHost : 'Family Platform';
                foreach ($recipientList as $recId) {
                    if (is_scalar($recId) && (string)$recId !== '') {
                        $stmt->execute([
                            $senderId,
                            (string)$recId,
                            $senderName,
                            $subject,
                            $eventInformation
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                error_log('[Event] In-app notification write failed: ' . $e->getMessage());
            }
        }
    }
}
