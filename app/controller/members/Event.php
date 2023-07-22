<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\{
    AllFunctionalities,
    Insert,
    Select,
    CheckToken
};

use App\model\AllMembersData;

class Event extends AllMembersData
{
    // private const REDIRECT = "Location: /member/ProfilePage";

    public static function submitEvent()
    {
        try {
            // SANITISE THE ENTRY
            $cleanData = getSanitisedInputData($_POST);
            CheckToken::tokenCheck('token');

            $cleanData['id'] = checkInput($_SESSION['id']);

            // insert the data and return the last event no

            return Insert::submitFormDynamicLastId('events', $cleanData, 'no');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public static function sendReminder(): void
    {
        try {


            $allEvents = parent::getEventData();

           
            $birthday = parent::getBirthdayMonth();
       

            // working on the birthday event 


            foreach ($birthday as $birthDateData) {

                // Format the birthday date from 'day' and 'month' data
                $birthdayFormatted = date("Y-m-d", strtotime("{$birthDateData['day']} {$birthDateData['month']}"));

                // Prepare the data for the notification
                $notificationData = [
                    'firstName' => $birthDateData['firstName'],
                    'eventName' => 'birthday',
                    'eventDate' => $birthdayFormatted
                ];

                // Check the event difference and notify

                self::checkEventDiffAndNotifyAll($notificationData);
            }


            // dump(self::getEventMonth($allEvents));

            foreach ($allEvents as $allEventData) {

                /**
                 * it checks the difference between the date today and the event date to see if the event is in 14 days, 7 days or same day
                 */


                self::checkEventDiffAndNotifyAll($allEventData);
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    // PRIVATE FUNCTION TO SEND EVENT NOTIFICATION
    private static function sendNotification($data, string $subject): void
    {

        $allEmails = getAllEmails();

        $view = 'msg/events/event';

        foreach ($allEmails as $email) {
            $data['email'] = $email;
            $emailWrapper = genEmailArray($view, $data, $subject);
            sendEmailWrapper($emailWrapper, "admin");
        }
    }


    // private static function getEventMonth($data): array
    // {
    //     $eventWeek = null;
    //     foreach ($data as $data) {
    //         if (date('m') == date('m', strtotime($data['eventDate']))) {
    //             $eventWeek[] = $data['eventDate'];
    //         }
    //     }
    //     return $eventWeek;
    // }

    /**
     * this function get the events of the week from Sunday
     */

    // private static function getEventWeek($data): array
    // {
    //     $event = null;
    //     foreach ($data as $data) {
    //         if (date('l') == "Tuesday") {

    //             for ($i = 0; $i <= 7; $i++) {

    //                 $dateO = date('Y-m-d');

    //                 $date = modifyDate($dateO, "+ $i days", 'date_add')['date'];
    //                 echo $date;

    //                 $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'events', identifier1: "eventDate");

    //                 $result = Select::selectFn2(query: $query, bind: [$date]);

    //                 if (!empty($result)) {

    //                     foreach ($result as $key) {
    //                         $event[] = $key;
    //                     }
    //                 }
    //             }
    //             return $event;
    //         }
    //     }
    // }

    /**
     * this function extends the event date on the due date 
     */

    private static function extendEventByFrequency($data)
    {

        if ($data['eventName'] !== "birthday") {

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

        // $dateDiff = dateDifferenceInt(date('Y-m-d'), $allEventData['eventDate']);

        \msgSuccess(201, $allEventData);
    }

    // public static function getEventByNo()
    // {

    //     try {

    //         $eventNo = checkInput($_GET['eventNo']);

    //         $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'events', identifier1: "no");

    //         $result = Select::selectFn2(query: $query, bind: [$eventNo]);
    //         $result[0]['firstName'] = $_SESSION['fName'];
    //         $result[0]['lastName'] = $_SESSION['lName'];

    //         $key = null;
    //         foreach ($result as $key); // NEEDS INVESTIGATION WHY IT WORKS
    //         return \msgSuccess(201, $key);
    //     } catch (\Throwable $th) {
    //         showErrorExp($th);
    //     }
    // }

    /**
     * Undocumented function
     *
     * @param array $data - the array must have the following keys: eventDate, eventName and firstName
     *
     * @return void
     */
    private static function checkEventDiffAndNotifyAll(array $data)
    {
        $todayDate = date('Y-m-d');
        $eventDayFormatted = dateFormat($data['eventDate']);
        $diffEventAndTodayDate = dateDifference($todayDate, $data['eventDate']);
        $data['eventDateFormatted'] = $eventDayFormatted;
      

        $subject = "{$data['firstName']}'s {$data['eventName']}";

        switch ($diffEventAndTodayDate) {
            case "+14 days":
                $subject .= " is on $eventDayFormatted";
                // this should be sending bulk email 
                self::sendNotification($data, $subject);
                self::extendEventByFrequency($data);
                break;
            case "+7 days":
                $subject .= " is on $eventDayFormatted";
                self::sendNotification($data, $subject);
                break;
            case "+1 days":
                $subject .= " is tomorrow, $eventDayFormatted";
                self::sendNotification($data, $subject);
                break;
            case "+0 days":
                $subject .= " is today, $eventDayFormatted";
                self::sendNotification($data, $subject);
                self::extendEventByFrequency($data);
                break;
            default:
                break;
        }
    }
}
