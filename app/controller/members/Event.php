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

            Insert::submitForm2('events', $cleanData);

            msgSuccess(200, "event successfully created");

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    static function sendReminder()
    {
        try {
            $todayDate =  date('Y-m-d');
            $data = parent::getEventData();
            //printArr($data);

            self::getEventMonth($data);
            echo BR;
           // printArr(self::getEventWeek($data));

            echo BR;
           // echo count(self::getEventWeek($data)) . "total number of array";

            foreach ($data as $data) {

                $eventDate = $data['eventDate'];
                $firstName = $data['firstName'];
                $eventName = $data['eventName'];
                $eventDayFormatted = dateFormat($data['eventDate']);

                $diffEventAndTodayDate = dateDifference($todayDate, $eventDate);

                if ($diffEventAndTodayDate === "+7 days") {

                    $subject = "$firstName's $eventName is on the $eventDayFormatted";

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

        $dateDiff = dateDifferenceInt(date('Y-m-d'), $allEventData['eventDate']);
      

        foreach ($allEventData as $event) {
            if ($dateDiff > 0 && $dateDiff <= 30) {
                $eventFirstName = $event['firstName'];
                $eventLastName = $event['LastName'];
                $eventName = $event['eventName'];
                $eventDate = dateFormat($event['eventDate']);
                $eventFrequency = $event['eventFrequency'];
                $eventType = $event['eventType'];
                $eventDescription = $event['eventDescription'];
                $eventNo2Word = \number2word($dateDiff);

        return "<p class='eventInfo'><strong>RSVP: </strong> $eventFirstName $eventLastName</p>
            <p class='eventInfo'><strong>Event: </strong>$eventName</p>
            <p class='eventInfo'><strong>Date: </strong>$eventDate <em style='color: rgba(11, 11, 201, 0.631)'> In $eventNo2Word Days</em> </p>
            <p class='eventInfo'><strong>Type: </strong>$eventType</p>
            <p class='eventInfo'><strong>Description: </strong> $eventDescription</p>
            <p class='eventInfo'><strong>Frequency: </strong> $eventFrequency</p>
           <hr>";
            }

            

        }
    }

}
