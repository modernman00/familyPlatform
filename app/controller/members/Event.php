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
    private const REDIRECT = "Location: /member/ProfilePage";

    static function submitEvent()
    {
        try {
            // SANITISE THE ENTRY
            $cleanData = getSanitisedInputData($_POST);
            CheckToken::tokenCheck('token', self::REDIRECT);

            $cleanData['id'] = checkInput($_SESSION['id']);

            // insert the data and return the last event no

            return Insert::submitFormDynamicLastId('events', $cleanData, 'no');


        } catch (\Throwable $th) {
            showError($th);
        }
    }

    static function sendReminder()
    {
        try {
         
            $todayDate =  date('Y-m-d');
            $data = parent::getEventData();
            // printArr($data);

            self::getEventMonth($data);
            // printArr(self::getEventWeek($data));

            // echo count(self::getEventWeek($data)) . "total number of array";

            foreach ($data as $data) {

                $eventDate = $data['eventDate'];
                $firstName = $data['firstName'];
                $eventName = $data['eventName'];
                $eventDayFormatted = dateFormat($data['eventDate']);

                $diffEventAndTodayDate = dateDifference($todayDate, $eventDate);

                if ($diffEventAndTodayDate === "+7 days") {

                    $subject = "$firstName's $eventName is on $eventDayFormatted";

                    self::sendNotification($data, $subject);
                } elseif ($diffEventAndTodayDate === "+0 days") {

                    $subject = "$firstName's $eventName is today, $eventDayFormatted";

                    self::sendNotification($data, $subject);

                    self::extendEventByFrequency($data);
                }
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    // PRIVATE FUNCTION TO SEND EVENT NOTIFICATION
    private static function sendNotification($data, $subject)
    {

        $allEmails = getAllEmails();

        $view = 'msg/events/event';

        foreach ($allEmails as $email) {
            $data['email'] = $email;
            $emailWrapper = genEmailArray($view, $data, $subject);
            sendEmailWrapper($emailWrapper, "admin");
        }
    }


    private static function getEventMonth($data): array
    {
        foreach ($data as $data) {
            if (date('m') == date('m', strtotime($data['eventDate']))) {
                $eventWeek[] = $data['eventDate'];
            }
        }
        return $eventWeek;
    }

    /**
     * this function get the events of the week from Sunday
     */

    private static function getEventWeek($data): array
    {
        foreach ($data as $data) {
            if (date('l') == "Tuesday") {

                for ($i = 0; $i <= 7; $i++) {

                    $dateO = date('Y-m-d');

                    $date = modifyDate($dateO, "+ $i days", 'date_add')['date'];
                    echo $date;

                    $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'events', identifier1: "eventDate");

                    $result = Select::selectFn2(query: $query, bind: [$date]);

                    if (!empty($result)) {

                        foreach ($result as $key) {
                            $event[] = $key;
                        }
                    }
                }
                return $event;
            }
        }
    }

    /**
     * this function extends the event date on the due date 
     */

    private static function extendEventByFrequency($data)
    {
        $no = checkInput($data['no']);
        $eventDate = checkInput($data['eventDate']);

        return match ($data['eventFrequency']) {
            "Annually" => self::processEventUpdate($eventDate, '1 year', $no),
            "Monthly" => self::processEventUpdate($eventDate, '1 month', $no),
            "Weekly" => self::processEventUpdate($eventDate, '1 week', $no)
        };
    }

    // this is a sub function to extendEventByFrequency

    private static function processEventUpdate($dateEvent, string $extendBy, string $no)
    {
        $newEventDate = modifyDate($dateEvent, $extendBy, 'date_add')['date'];

        return AllFunctionalities::update2('events', 'eventDate', $newEventDate, 'no', $no);
    }

    static function getEventData()
    {
        $allEventData = allMembersData::getEventData();

        // $dateDiff = dateDifferenceInt(date('Y-m-d'), $allEventData['eventDate']);

        \msgSuccess(201, $allEventData);
    }

    static function getEventByNo()
    {

        try {

            $eventNo = checkInput($_GET['eventNo']);

            $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'events', identifier1: "no");

            $result = Select::selectFn2(query: $query, bind: [$eventNo]);
            $result[0]['firstName'] = $_SESSION['fName'];
            $result[0]['lastName'] = $_SESSION['lName'];

            foreach ($result as $key); // NEEDS INVESTIGATION WHY IT WORKS
            return \msgSuccess(201, $key);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }
}
