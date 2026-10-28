<?php
declare(strict_types=1);

namespace App\controller\members;

use App\classes\{ AllFunctionalities, Insert, Select, PushNotificationClass, Pusher };
use App\model\{EmailData, AllMembersData};
use App\services\ActivityProximityService;
use App\services\NotificationOrchestrator;
use App\services\PartyPlatformBridgeService;
use Src\CheckToken;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;
use Src\functionality\SubmitPostData;
use Src\LoginUtility;
use Src\SelectFn;
use Src\SendEmail;
use Src\SubmitForm;
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
            $enableParty = !empty($_POST['enable_party_platform'])
                && in_array((string) $_POST['enable_party_platform'], ['1', 'true', 'on'], true);
            $famCodeRaw = self::generateFamCode();
            $famCodeStr = is_string($famCodeRaw) ? $famCodeRaw : '';
            $hostIdRaw = checkInput($_SESSION['id'] ?? '');
            $hostIdStr = is_string($hostIdRaw) ? $hostIdRaw : '';

            $retunLastId = SubmitPostData::submitToOneTablenImage(
                table: 'events',
                minMaxData: [
                    'data' => ['eventName',  'eventDescription'],
                    'min'  => [3,  0],
                    'max'  => [100, 500]
                ],
                removeKeys: ['submit', 'token', 'grecaptcharesponse', 'awnqylrds9sip', 'enable_party_platform'],
                newInput: [
                    'eventCode' => $famCodeRaw,
                    'id' => $hostIdRaw
                ],
                optionalFields: ['eventDescription']
            );

            if (!$retunLastId) {
                return;
            }

            if ($enableParty && $hostIdStr !== '' && $famCodeStr !== '') {
                try {
                    PartyPlatformBridgeService::provisionAndLinkEvent(
                        eventNo: (int) $retunLastId,
                        hostUserId: $hostIdStr,
                        familyCode: $famCodeStr
                    );
                } catch (\Throwable $bridgeErr) {
                    error_log('[Event::submitEvent] PartyPlatform auto-provision warning: ' . $bridgeErr->getMessage());
                }
            }

            msgSuccess(200, "Event submitted", $retunLastId);


        } catch (\Throwable $th) {
            showError($th);
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
     * @throws \Throwable
     */
    /**
     * Post event notification bar
     * This PHP function, PostEventNotificationBar, handles posting an event notification to a recipient. It:Sanitizes input data from $_POST and $_SESSION.
     * Retrieves the sender's name.
     * Prepares a notification array with sender and event details.
     * Inserts the notification into the 'notification' table.
     * Sends a success message with the last inserted ID.
     * In essence, it creates a new event notification and stores it in the database.
     * '/member/notification/event
     *
     * @return void
     */

    public static function PostEventNotificationBar(): void
    {
        try {
            $cleanData = LoginUtility::getSanitisedInputData($_POST);
            $cleanData['id'] = checkInput(data: $_SESSION['id']);
            $id = is_scalar($cleanData['id']) ? (string)$cleanData['id'] : '';

            // GET THE SENDER'S NAME 
            $senderName = self::getSenderName();
            $senderNameStr = is_string($senderName) && $senderName !== '' ? $senderName : 'A family member';
            $eventFamCode = self::generateFamCode();
            $eventFamCodeStr = is_string($eventFamCode) ? $eventFamCode : '';
            $eventName = is_string($cleanData['eventName'] ?? null) ? (string)$cleanData['eventName'] : 'Family Event';
            $eventDate = is_string($cleanData['eventDate'] ?? null) ? (string)$cleanData['eventDate'] : '';
            $eventType = is_string($cleanData['eventType'] ?? null) ? (string)$cleanData['eventType'] : 'Event';
            $eventDescription = is_string($cleanData['eventDescription'] ?? null) ? (string)$cleanData['eventDescription'] : '';

            // Insert to legacy notification table for backward compatibility
            $cleanDataNotification = [
                'sender_id' => $id,
                'receiver_id' => $eventFamCodeStr,
                'sender_name' => $senderNameStr,
                'notification_name' => $eventName,
                'notification_date' => $eventDate,
                'receiver' => 'everyone filtered',
                'notification_type' => $eventType,
                'notification_content' => $eventDescription,
                'action_url' => '/profilePage#eventHeader',
                'notification_status' => 'new'
            ];

            $lastInsertedId = SubmitForm::submitFormDynamicLastId(
                table: 'notification',
                field: $cleanDataNotification,
                lastIdCol: 'no'
            );

            // Multi-channel notification dispatch across the family network
            if ($eventFamCodeStr !== '') {
                $membersToNotify = self::filterMemberByFamCode($eventFamCodeStr);
                $formattedDate = $eventDate !== '' ? dateFormat($eventDate) : '';

                // Proximity Geofencing: Scope recipients if event is targeted to local members
                $proximityMode = (string)($cleanData['proximity_mode'] ?? 'all_family');
                $targetRadius = (int)($cleanData['target_radius_miles'] ?? 25);
                $eventLocation = (string)($cleanData['eventLocation'] ?? '');
                $isProximityScoped = ($proximityMode === 'proximity_only' && $eventLocation !== '');

                if ($isProximityScoped) {
                    $proximityResult = ActivityProximityService::filterMembersByProximity($membersToNotify, $eventLocation, $targetRadius);
                    $membersToNotify = $proximityResult['nearby_members'];
                }

                $notifTitle = $isProximityScoped
                    ? "📍 Nearby Activity ({$targetRadius}mi): {$eventName}"
                    : "🎉 New Event: {$eventName}";

                // 1. Dispatch via Unified NotificationOrchestrator (In-App real-time toast + WebPush OS banner + sync)
                foreach ($membersToNotify as $member) {
                    $targetUid = is_scalar($member['id'] ?? null) ? (string)$member['id'] : '';
                    if ($targetUid !== '') {
                        NotificationOrchestrator::dispatch(
                            userId: $targetUid,
                            category: 'social',
                            priority: 'medium',
                            title: $notifTitle,
                            body: "{$senderNameStr} added '{$eventName}' on {$formattedDate}" . ($isProximityScoped ? " near {$eventLocation}" : ""),
                            actionUrl: '/profilePage#eventHeader',
                            tag: 'event-' . ($lastInsertedId ?: 'new'),
                            familyCode: $eventFamCodeStr,
                            metadata: [
                                'sender_id' => $id,
                                'sender_name' => $senderNameStr,
                                'event_no' => $lastInsertedId,
                                'eventName' => $eventName,
                                'eventDate' => $eventDate,
                                'eventType' => $eventType,
                                'eventDescription' => $eventDescription,
                                'eventLocation' => $eventLocation,
                                'proximity_mode' => $proximityMode,
                                'target_radius_miles' => $targetRadius,
                            ]
                        );
                    }
                }

                // 2. Dispatch HTML Email Notification to family network
                $emailsToNotify = array_values(array_filter(array_map('strval', array_column($membersToNotify, 'email')), fn($e) => $e !== ''));
                if (!empty($emailsToNotify)) {
                    $emailPayload = [
                        'eventName' => $eventName,
                        'eventDate' => $eventDate,
                        'eventType' => $eventType,
                        'eventDescription' => $eventDescription,
                        'firstName' => $senderNameStr,
                        'lastName' => '',
                        'emailHTMLContent' => "{$senderNameStr} has added a new upcoming family event: {$eventName} scheduled for {$formattedDate}.",
                    ];
                    self::sendBulkNotification(
                        data: $emailPayload,
                        subject: "New Family Event: {$eventName}",
                        email: $emailsToNotify
                    );
                }
            }

            msgSuccess(code: 200, msg: $lastInsertedId);
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    /**
     * Get a single event notification by notificationNo.
     * /member/notification/event
     *
     * @return void
     */
    public static function GetEventNotificationBar()
    {
        try {
            $notificationNo = self::asString(checkInput(data: $_GET['notificationNo']));
            $allNotification = SelectFn::selectAllRowsById('notification', 'no', $notificationNo);

            $viewerId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';
            if ($viewerId !== '') {
                Pusher::broadcast(
                    theChannel: Pusher::userChannel($viewerId),
                    theEvent: 'new-notification',
                    theData: $allNotification
                );
            }

            $eventFamCode = self::generateFamCode();
            if (is_string($eventFamCode) && $eventFamCode !== '') {
                Pusher::broadcast(
                    theChannel: Pusher::familyChannel($eventFamCode),
                    theEvent: 'new-notification',
                    theData: $allNotification
                );
            }
        } catch (\Throwable $th) {
            showError(th: $th);
        }
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

    /**
     * Sends event reminders to all members whose family code matches the event's family code.
     *
     * This method retrieves all members' details and events occurring within the next 7 days,
     * 1 day, and on the current date. It filters members based on the family code of each event
     * and sends notifications to those members. If the event data is available, it iterates over
     * each event, filters the members by family code, and sends reminders to the corresponding
     * members via email.
     *
     * @return void
     * @throws \Throwable If any error occurs during the operation.
     */
    public static function sendReminder(): void
    {
        try {
            // show information of events within 7 days , 1 days and on the current date
            $allEventData = parent::getAllEventData();

            $notifyCustomer = new EmailData(
                sender: 'member'
            );

            if (!defined('PASS')) {
                $notifyCustomer->getEmailData();
            }

            if ($allEventData) {
                // send other event reminders
                foreach ($allEventData as $eventData) {
                // Get the famCode of the event
                $famCodeForEvent = $eventData['famCode'] ?? $eventData['eventCode'] ?? null;
                if (!$famCodeForEvent) continue;

                $membersToNotify = self::filterMemberByFamCode($famCodeForEvent);

                    // Extract email addresses from the filtered members
                    $emailsToNotify = array_column($membersToNotify, 'email');

                    // Extract id from the filtered members
                    $idsToNotify = array_column($membersToNotify, 'id');


                    self::checkEventDiffAndNotifyAll(data: $eventData, email: $emailsToNotify, id: $idsToNotify);
                }
            }
        } catch (\Throwable $th) {
            showError($th);
        }
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
                    "Weekly" => self::processEventUpdate(dateEvent: $eventDate, extendBy: '1 week', no: $no)
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
     * @return void
     */
    public static function getEventData(): void
    {
        $allEventData = allMembersData::getAllEventData();

        \msgSuccess(201, $allEventData);
    }

    /**
     * Retrieves all scheduled family events for the upcoming 3 months (for the interactive modal schedule).
     * GET /member/events/upcoming3Months
     */
    public static function getUpcoming3MonthsEvents(): void
    {
        try {
            $userId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string)$_SESSION['id'] : '';
            if ($userId === '') {
                \msgException(401, 'Unauthorised access');
                return;
            }

            $famCodeRaw = self::generateFamCode();
            $famCode = is_string($famCodeRaw) ? $famCodeRaw : '';
            $famCodes = $_SESSION['famCodes'] ?? ($famCode !== '' ? [$famCode] : []);

            $events = allMembersData::getUpcomingEventsForMonths($famCodes, 3);
            $enriched = [];

            foreach ($events as $event) {
                $rawDate = (string)($event['eventDate'] ?? '');
                $dateDiff = $rawDate !== '' ? dateDifferenceInt(date('Y-m-d'), $rawDate) : 0;
                if ($dateDiff < 0) {
                    $dateDifference = abs($dateDiff) === 1 ? 'Yesterday' : abs($dateDiff) . ' days ago';
                } else {
                    $getDateDiff = number2word($dateDiff);
                    if ($dateDiff === 0 || strtolower((string)$getDateDiff) === 'zero') {
                        $dateDifference = 'Today';
                    } elseif ($dateDiff === 1 || strtolower((string)$getDateDiff) === 'one') {
                        $dateDifference = 'Tomorrow';
                    } else {
                        $dateDifference = "in {$getDateDiff} Days";
                    }
                }

                $creatorName = trim(($event['firstName'] ?? '') . ' ' . ($event['lastName'] ?? ''));
                if ($creatorName === '') {
                    $creatorName = 'Family Member';
                }

                $creatorImg = !empty($event['creatorImg']) ? (string)$event['creatorImg'] : 'public/img/default.jpg';
                if (!str_starts_with($creatorImg, '/') && !str_starts_with($creatorImg, 'http')) {
                    $creatorImg = '/' . ltrim($creatorImg, '/');
                }

                $timestamp = strtotime($rawDate) ?: time();
                $monthYear = date('F Y', $timestamp);
                $monthKey = date('Y-m', $timestamp);
                $dayOfMonth = date('j', $timestamp);
                $dayName = date('D', $timestamp);

                $titleEncoded = urlencode((string)($event['eventName'] ?? 'Family Event'));
                $descEncoded = urlencode((string)($event['eventDescription'] ?? ''));
                $calDate = date('Ymd', $timestamp);
                $gCalUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$titleEncoded}&dates={$calDate}/{$calDate}&details={$descEncoded}";

                $enriched[] = [
                    'no' => $event['no'],
                    'id' => (string)$event['id'],
                    'eventName' => $event['eventName'],
                    'eventDate' => dateFormat($rawDate),
                    'eventDateRaw' => $rawDate,
                    'eventType' => $event['eventType'] ?? 'Gathering',
                    'eventDescription' => $event['eventDescription'] ?? '',
                    'eventFrequency' => $event['eventFrequency'] ?? '',
                    'dateDifference' => $dateDifference,
                    'daysUntil' => $dateDiff,
                    'dayOfMonth' => $dayOfMonth,
                    'dayName' => $dayName,
                    'monthYear' => $monthYear,
                    'monthKey' => $monthKey,
                    'creatorName' => $creatorName,
                    'creatorImg' => $creatorImg,
                    'isOwnEvent' => ($userId === (string)$event['id']),
                    'party_event_id' => $event['party_event_id'] ?? null,
                    'party_event_slug' => $event['party_event_slug'] ?? null,
                    'party_rsvp_url' => $event['party_rsvp_url'] ?? null,
                    'party_card_url' => $event['party_card_url'] ?? null,
                    'party_sync_status' => $event['party_sync_status'] ?? 'none',
                    'eventLocation' => $event['eventLocation'] ?? '',
                    'proximity_mode' => $event['proximity_mode'] ?? 'all_family',
                    'target_radius_miles' => (int)($event['target_radius_miles'] ?? 25),
                    'gCalUrl' => $gCalUrl,
                ];
            }

            \msgSuccess(200, $enriched);
        } catch (\Throwable $th) {
            showError($th);
        }
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
                'party_event_id' => $event['party_event_id'] ?? null,
                'party_rsvp_url' => $event['party_rsvp_url'] ?? null,
                'party_card_url' => $event['party_card_url'] ?? null,
            ]);

            msgSuccess(200, "event updated successfully");
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Gear 2/3 Progressive Escalation: Upgrades an existing FamilyPlatform event
     * to a full PartyPlatform Event Studio workspace and seeds the Family Tree
     * into Guest Builder prospects. Author & Family-scoped only.
     *
     * POST /member/profilePage/event/{eventNo}/upgrade-party
     */
    public static function upgradeToPartyPlatform(int|string $eventNo): void
    {
        try {
            CheckToken::tokenCheck();

            $eventNoStr = self::asString(checkInput($eventNo));
            $userId = self::asString(checkInput($_SESSION['id'] ?? ''));
            $sessionFamCode = is_scalar($_SESSION['famCode'] ?? null) ? (string) $_SESSION['famCode'] : '';

            $event = SelectFn::selectOneRow('events', 'no', $eventNoStr);

            if (
                !is_array($event)
                || $userId === ''
                || (string) ($event['id'] ?? '') !== $userId
                || ($sessionFamCode !== '' && (string) ($event['eventCode'] ?? '') !== '' && (string) $event['eventCode'] !== $sessionFamCode)
            ) {
                throw new ForbiddenException('You can only upgrade your own family events to PartyPlatform Studio.');
            }

            $familyCode = (string) ($event['eventCode'] ?? $sessionFamCode);
            $bridgeResult = PartyPlatformBridgeService::provisionAndLinkEvent(
                eventNo: (int) $eventNoStr,
                hostUserId: $userId,
                familyCode: $familyCode
            );

            Pusher::broadcastToFamily($familyCode, 'update-event', [
                'no' => $eventNoStr,
                'party_event_id' => $bridgeResult['party_event_id'],
                'party_event_slug' => $bridgeResult['party_event_slug'],
                'party_rsvp_url' => $bridgeResult['party_rsvp_url'],
                'party_card_url' => $bridgeResult['party_card_url'],
                'party_sync_status' => 'synced',
            ]);

            msgSuccess(200, 'Event linked to PartyPlatform Studio', $bridgeResult);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Gear 3 Single Sign-On (SSO) Handoff to PartyPlatform Event Studio.
     * Verifies strict event ownership, auto-provisions if not yet bridged,
     * and redirects the host into PartyPlatform with a signed 120s HMAC token.
     *
     * GET /member/profilePage/event/{eventNo}/party-sso
     */
    public static function partyStudioSso(int|string $eventNo): void
    {
        try {
            $eventNoStr = (string) $eventNo;
            if (empty($_SESSION['id'])) {
                if (\class_exists('\App\controller\BaseController')) {
                    \App\controller\BaseController::rehydrateSession();
                }
            }
            $userId = is_scalar($_SESSION['id'] ?? null) ? trim((string) $_SESSION['id']) : '';
            if ($userId === '') {
                header('Location: /login?redirect=' . urlencode('/member/profilePage/event/' . $eventNoStr . '/party-sso'));
                exit;
            }

            $sessionFamCode = is_scalar($_SESSION['famCode'] ?? null) ? (string) $_SESSION['famCode'] : '';

            $event = SelectFn::selectOneRow('events', 'no', $eventNoStr);

            if (!is_array($event) || empty($event['no'])) {
                header('Location: /profilePage');
                exit;
            }

            // If user is a logged-in family member but NOT the creator of the event,
            // direct them smoothly to the public RSVP page instead of throwing a 403 screen.
            if ((string) ($event['id'] ?? '') !== $userId) {
                if (!empty($event['party_rsvp_url'])) {
                    header('Location: ' . (string) $event['party_rsvp_url']);
                    exit;
                }
                header('Location: /profilePage');
                exit;
            }

            $hostAcct = SelectFn::selectOneRow('account', 'id', $userId);
            $hostContact = SelectFn::selectOneRow('contact', 'id', $userId);
            $hostEmail = is_array($hostAcct) && !empty($hostAcct['email'])
                ? strtolower(trim((string) $hostAcct['email']))
                : (is_array($hostContact) && !empty($hostContact['email'])
                    ? strtolower(trim((string) $hostContact['email']))
                    : strtolower(trim((string) ($_SESSION['email'] ?? $_SESSION['auth']['email'] ?? ''))));

            if (
                $hostEmail === ''
                || !filter_var($hostEmail, FILTER_VALIDATE_EMAIL)
                || str_ends_with($hostEmail, '.local')
            ) {
                if (function_exists('isTestEnv') && isTestEnv()) {
                    throw new ValidationException(
                        'Please add a valid email address in your Account Settings before launching PartyPlatform Studio.'
                    );
                }
                header('Location: /accountSetting?notice=email_required_for_party_studio');
                exit;
            }

            $familyCode = (string) ($event['eventCode'] ?? $sessionFamCode);
            $partyEventId = (string) ($event['party_event_id'] ?? '');

            if ($partyEventId === '') {
                $provisioned = PartyPlatformBridgeService::provisionAndLinkEvent(
                    eventNo: (int) $eventNoStr,
                    hostUserId: $userId,
                    familyCode: $familyCode
                );
                $partyEventId = $provisioned['party_event_id'];
            }

            $targetRaw = isset($_GET['target']) && is_scalar($_GET['target']) && trim((string) $_GET['target']) !== ''
                ? trim((string) $_GET['target'])
                : 'studio';
            $ssoUrl = PartyPlatformBridgeService::generateSsoHandoffUrl($partyEventId, $hostEmail, $targetRaw);

            if (function_exists('isTestEnv') && isTestEnv()) {
                $GLOBALS['__testRedirect'] = $ssoUrl;
                return;
            }

            header('Location: ' . $ssoUrl);
            exit;
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
    private static function checkEventDiffAndNotifyAll(array $data, array $email, ?array $id = null): void
    {

        $diffEventAndTodayDate = dateDifference(
            date1: date(format: 'Y-m-d'),
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
