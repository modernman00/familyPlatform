<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\{
    AllFunctionalities,
    Insert,
    CheckToken,
    Select
};
use App\model\EmailData;

use App\model\AllMembersData;

class Event extends AllMembersData
{
    // private const REDIRECT = "Location: /member/ProfilePage";

    public static function submitEvent()
    {
        try {
            // SANITISE THE ENTRY
            $cleanData = getSanitisedInputData($_POST);

            $cleanData['id'] = checkInput($_SESSION['id']);


            // insert the data and return the last event no
            // if the data is successfully inserted then insert same data to the notification table
            // CheckToken::tokenCheck('token');
            return Insert::submitFormDynamicLastId('events', $cleanData, 'no');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public static function PostEventNotificationBar()
    {
        try {
            $cleanData = getSanitisedInputData($_POST);
            $cleanData['id'] = checkInput($_SESSION['id']);

            // GET THE SENDER'S NAME 

            $getSenderName = parent::getMemberName($cleanData['id']);
            $sendName =  "{$getSenderName['fName']} {$getSenderName['lName']}";

            // insert to the notification table
            // Update the notification tab

            $cleanDataNotification = [
                'sender_id' => $cleanData['id'],
                'sender_name' => $sendName,
                'receiver' => 'Everyone',
                'notification_name' => $cleanData['eventName'],
                'notification_date' => $cleanData['eventDate'],
                'notification_type' => $cleanData['eventType'],
                'notification_content' => $cleanData['eventDescription']
            ];


            Insert::submitFormDynamicLastId('notification', $cleanDataNotification, 'no');
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    public static function GetEventNotificationBar()
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


            // show information of events within 7 days , 1 days and on the current date
            $eventData = parent::getEventData();

            $allEmails = getAllEmails();

            $notifyCustomer = new EmailData('admin');

            if (!defined('PASS')) {
                $notifyCustomer->getEmailData();
            }


            if ($eventData) {

                // send other event reminders
                foreach ($eventData as $allEventData) {


                    self::checkEventDiffAndNotifyAll($allEventData, $allEmails);
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
    private static function checkEventDiffAndNotifyAll(array $data, array $email)
    {

        $diffEventAndTodayDate = dateDifference(date('Y-m-d'), $data['eventDate']);
        $eventDayFormatted = dateFormat($data['eventDate']);



        $subject = "{$data['firstName']}'s {$data['eventType']}";

        switch ($diffEventAndTodayDate) {
            case "+7 days":
                $subject .= " is on $eventDayFormatted";
                $data['emailHTMLContent'] = "$subject in seven days";
                self::sendBulkNotification($data, $subject, $email);
                break;
            case "+1 days":
                $subject .= " is tomorrow, $eventDayFormatted";
                $data['emailHTMLContent'] = $subject;
                self::sendBulkNotification($data, $subject, $email);
                break;
            case "+0 days":
                $subject .= " is today, $eventDayFormatted";
                $data['emailHTMLContent'] = $subject;
                self::sendBulkNotification($data, $subject, $email);
                self::extendEventByFrequency($data);
                break;
            default:
                break;
        }
    }
}
