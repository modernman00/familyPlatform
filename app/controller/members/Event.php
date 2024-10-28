<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\{
    AllFunctionalities,
    Insert,
    CheckToken,
    Select,
    PushNotificationClass
};
use App\model\{EmailData, AllMembersData};

class Event extends AllMembersData
{
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
            // SANITISE THE ENTRY
            $cleanData = getSanitisedInputData($_POST);
            $cleanData['id'] = checkInput(data: $_SESSION['id']);

            $cleanData['eventCode'] = self::generateFamCode();

            CheckToken::tokenCheck(token: 'token');

            $lastInsertedId = Insert::submitFormDynamicLastId(
                table: 'events',
                field: $cleanData,
                lastIdCol: 'no'
            );
            msgSuccess(code: 200, msg: $lastInsertedId);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private static function generateFamCode(): string
    {
        $getFamCode = parent::getMemberName(id: $_SESSION['id']);
        return $getFamCode['famCode'];
    }

    private static function getSenderName(): string
    {
        $getSenderName = parent::getMemberName(id: $_SESSION['id']);
        return $getSenderName['fName'] . " " . $getSenderName['lName'];
    }

    // Filter members based on the event famCode
    private static function filterMemberByFamCode(string $famCode): array
    {
        // Get all members' details including email, famCode, and id
        $members = self::getAllMembersCodeAndEmail();

        $membersToNotify = array_filter($members, function ($member) use ($famCode) {
            return $member['famCode'] == $famCode;
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
            $cleanData = getSanitisedInputData(inputData: $_POST);
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

            $lastInsertedId = Insert::submitFormDynamicLastId(
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
        header("Content-Type: text/event-stream");
        header("Cache-Control: no-cache");
        header("Connection: keep-alive");
        // Ensure the script runs indefinitely to support the long-running SSE connection
set_time_limit(0);
        try {

            $notificationNo = checkInput(data: $_GET['notificationNo']);
            $query = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'notification', identifier1: 'no');
            $result = Select::selectFn2(query: $query, bind: [$notificationNo]);

            // $member = self::filterMemberByFamCode(famCode: $result[0]['receiver_id']);

            // msgSuccess(code: 200, msg: [
            //     'eventData' => $result[0], 
            //     // 'member' => $member
            // ]);

            // real time update to the msgServerSent
            msgServerSent(
                data: $result[0],
                id: $notificationNo,
                event: 'newNotification'
            );
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

            $notificationNo = $_GET['notificationNo'];

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
                    $eventFamCode = $eventData['famCode'];

                    $membersToNotify = self::filterMemberByFamCode(famCode: $eventFamCode);

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

    private static function sendBulkNotification($data, $subject, $email)
    {

        // $allEmails = getAllEmails();

        // $notifyCustomer = new EmailData('admin');

        // if (!defined('PASS')) {
        //     $notifyCustomer->getEmailData();
        // }

        ob_start();
        $emailPage = view(path: 'msg/events/event', data: compact('data'));
        $emailContent = ob_get_contents();
        ob_end_clean();


        sendBulkEmail(emailAddresses: $email, subject: $subject, message: $emailContent);
    }
    /**
     * this function extends the event date on the due date 
     */

    private static function extendEventByFrequency($data)
    {

        if ($data['eventFrequency']) {

            $no = checkInput($data['no']) ?? null;

            $eventDate = checkInput($data['eventDate']);

            if ($data['eventFrequency'] != "One-off") {


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

    private static function processEventUpdate($dateEvent, string $extendBy, string $no)
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
        $eventNo = $_GET['eventNo'];
        $allEventData = allMembersData::getEventDataByNo($eventNo);

        $eventFamCode = self::generateFamCode();

        $filteredEventDataByFamCode = ($eventFamCode === $allEventData[0]['famCode']) ? $allEventData[0] : [];

        msgSuccess(
            code: 201,
            msg: $filteredEventDataByFamCode
        );
    }

    /**
     * Undocumented function
     *
     * @param array $data - the array must have the following keys: eventDate, eventName and firstName
     *
     * @return void
     */
    private static function checkEventDiffAndNotifyAll(array $data, array $email, array $id = null): void
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
        $url = getenv('MIX_APP_URL2') . "/member/ProfilePage";


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
