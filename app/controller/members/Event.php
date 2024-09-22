<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\{
    AllFunctionalities,
    Insert,
    CheckToken,
    Select, PushNotificationClass
};
use App\model\{EmailData, AllMembersData};

class Event extends AllMembersData
{


    public static function submitEvent()
    {
        try {
            // SANITISE THE ENTRY
            $cleanData = getSanitisedInputData($_POST);

            $cleanData['id'] = checkInput($_SESSION['id']);

            // insert the data and return the last event no
            // if the data is successfully inserted then insert same data to the notification table
            CheckToken::tokenCheck(token: 'token');
            $lastInsertedId = Insert::submitFormDynamicLastId(table: 'events', field: $cleanData, lastIdCol: 'no');
            msgSuccess(200, $lastInsertedId);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public static function PostEventNotificationBar()
    {
        try {
            $cleanData = getSanitisedInputData(inputData: $_POST);
            $cleanData['id'] = checkInput(data: $_SESSION['id']);

            // GET THE SENDER'S NAME 

            $getSenderName = parent::getMemberName(id: $cleanData['id']);
            $sendName =  "{$getSenderName['fName']} {$getSenderName['lName']}";

            // insert to the notification table
            // Update the notification tab

            $cleanDataNotification = [
                'sender_id' => $cleanData['id'],
                'receiver_id' => $getSenderName['famCode'],
                'sender_name' => $sendName,
                'notification_name' => $cleanData['eventName'],
                'notification_date' => $cleanData['eventDate'],
                // 'notification_time' => strtotime(date('Y-m-d H:i:s')),
                'notification_type' => $cleanData['eventType'],
                'notification_content' => $cleanData['eventDescription']
            ];

            $lastInsertedId = Insert::submitFormDynamicLastId(table: 'notification', field: $cleanDataNotification, lastIdCol: 'no');

                  // Send push notification to the receiver about the new friend request

            msgSuccess(code: 200, msg: $lastInsertedId);
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    public static function GetEventNotificationBar()
    {
        try {

            $notificationNo = $_GET['notificationNo'];

            $query = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'notification', identifier1: 'no');

            $result = Select::selectFn2(query: $query, bind: [$notificationNo]);

            msgSuccess(code: 200, msg: $result);
        } catch (\Throwable $th) {
            showError(th: $th);
        }
    }

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

    public static function sendReminder(): void
    {
        try {

            // Get all members' details including email, famCode, and id
            $allMembers = self::getAllMembersCodeAndEmail();

            // show information of events within 7 days , 1 days and on the current date
            $allEventData = parent::getEventData();

            $notifyCustomer = new EmailData('admin');

            if (!defined('PASS')) {
                $notifyCustomer->getEmailData();
            }


            if ($allEventData) {
                // send other event reminders
                foreach ($allEventData as $eventData) {
                    // Get the famCode of the event
                    $eventFamCode = $eventData['famCode'];

                    // Filter members based on the event famCode
                    $membersToNotify = array_filter($allMembers, function ($member) use ($eventFamCode) {
                        return $member['famCode'] == $eventFamCode;
                    });

                    // Extract email addresses from the filtered members
                    $emailsToNotify = array_column($membersToNotify, 'email');

                     // Extract id from the filtered members
                    $idsToNotify = array_column($membersToNotify, 'id');

                   
                    self::checkEventDiffAndNotifyAll($eventData, $emailsToNotify, $idsToNotify);
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
        $emailPage = view('msg/events/event', compact('data'));
        $emailContent = ob_get_contents();
        ob_end_clean();


        sendBulkEmail($email, $subject, $emailContent);
    }
    /**
     * this function extends the event date on the due date 
     */

    private static function extendEventByFrequency($data)
    {

        if ($data['eventFrequency']) {

            $no = checkInput($data['no']) ?? null;

            $eventDate = checkInput($data['eventDate']);

            return match ($data['eventFrequency']) {
                "Annually" => self::processEventUpdate($eventDate, '1 year', $no),
                "Monthly" => self::processEventUpdate($eventDate, '1 month', $no),
                "Weekly" => self::processEventUpdate($eventDate, '1 week', $no)
            };
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
    public static function getEventData()
    {
        $allEventData = allMembersData::getEventData();

        \msgSuccess(201, $allEventData);
    }

    /**
     * @return void
     */
    public static function getEventDataByNoController()
    {
        $eventNo = $_GET['eventNo'];
        $allEventData = allMembersData::getEventDataByNo($eventNo);

        \msgSuccess(201, $allEventData);
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

        $diffEventAndTodayDate = dateDifference(date('Y-m-d'), $data['eventDate']);
        $eventDayFormatted = dateFormat($data['eventDate']);
        $subject = $data['eventName'];
        $eventHost = "{$data['firstName']} {$data['lastName']}";
        $eventDescription = $data['eventDescription'];
        $eventInformation = "Kindly be informed that this event is scheduled to be held on $eventDayFormatted. Please contact the $eventHost for more information. Event Description: $eventDescription";
        $url = getenv('MIX_APP_URL2') ."/member/ProfilePage";


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
