<?php
declare(strict_types=1);

namespace App\controller\members;

use App\classes\{ AllFunctionalities, Insert, Select, PushNotificationClass, Pusher };
use App\model\{EmailData, AllMembersData};
use Src\CheckToken;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;
use Src\functionality\SubmitPostData;
use Src\LoginUtility;
use Src\SelectFn;
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
            $retunLastId = SubmitPostData::submitToOneTablenImage(
                table: 'events',
                minMaxData: [
                    'data' => ['eventName',  'eventDescription'],
                    'min'  => [5,  5],
                    'max'  => [100, 500]
                ],
                removeKeys: ['submit', 'token', 'grecaptcharesponse', 'awnqylrds9sip'],
                newInput: [
                    'eventCode' => self::generateFamCode(),
                    'id' => checkInput($_SESSION['id'])
                ]

            );
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
        // Get all members' details including email, famCode, and id
        $members = self::getAllMembersCodeAndEmail();

        $membersToNotify = array_filter($members, function ($member) use ($eventFamCode) {
            // Check legacy famCode column or new user_families array structure
            $memberFamilies = isset($member['user_families']) ? array_column($member['user_families'], 'family_code') : [$member['famCode']];
            return in_array($eventFamCode, $memberFamilies) || $member['famCode'] == $eventFamCode;
        });

        return $membersToNotify;
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
            $cleanData = LoginUtility::getSanitisedInputData( $_POST);
            $cleanData['id'] = checkInput(data: $_SESSION['id']);
            $id = $cleanData['id'];

            // GET THE SENDER'S NAME 

            $senderName =  self::getSenderName();
            $eventFamCode = self::generateFamCode();
            $eventName = $cleanData['eventName'];


            // insert to the notification table
            // Update the notification tab

            $cleanDataNotification = [
                'sender_id' => $id,
                'receiver_id' => $eventFamCode,
                'sender_name' => $senderName,
                'notification_name' => $eventName,
                'notification_date' => $cleanData['eventDate'],
                'receiver' => 'everyone filtered',
                'notification_type' => $cleanData['eventType'],
                'notification_content' => $cleanData['eventDescription'],
                'notification_status' => 'new'
            ];

            $lastInsertedId = SubmitForm::submitFormDynamicLastId(
                table: 'notification',
                field: $cleanDataNotification,
                lastIdCol: 'no'
            );

            // // activate the email notification 
            //TODO i DON'T THINK i SHOULD NEED THIS AT ALL
            // self::sendReminder();

            // Send push notification to the receiver about the new event

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
            // $query = SelectFn::sel(selection: 'SELECT_ONE', table: 'notification', identifier1: 'no');
            // $result = SelectFn::selectFn2(query: $query, bind: [$notificationNo]);

            $allNotification = SelectFn::selectAllRowsById('notification', 'no', $notificationNo);

            // The notification bar is polled by its owner, so push it to that
            // viewer's own private channel — never a world-readable one.
            $viewerId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';
            if ($viewerId !== '') {
                Pusher::broadcast(
                    theChannel: Pusher::userChannel($viewerId),
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
                sender: 'admin'
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

        // $allEmails = getAllEmails();

        // $notifyCustomer = new EmailData('admin');

        // if (!defined('PASS')) {
        //     $notifyCustomer->getEmailData();
        // }

        ob_start();
        view(path: 'msg/events/event', data: compact('data'));
        $emailContent = ob_get_contents();
        ob_end_clean();


        sendBulkEmail(emailAddresses: $email, subject: $subject, message: $emailContent);
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
            $getDateDiff = number2word($dateDiff);
            $dateDifference = $getDateDiff === 'Zero' ? 'Today' : ($getDateDiff === 'One' ? 'Tomorrow' : "in $getDateDiff Days");

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
    }
}
