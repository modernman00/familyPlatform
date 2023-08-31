<?php

declare(strict_types=1);

namespace App\model;

use Exception;

use App\classes\{
    InnerJoin,
    Select
};

class AllMembersData extends InnerJoin
{
    private const ERR_MSG = "Member Data Error";

    public function getAllMembers(): array
    {
        $table = ['personal', 'otherFamily', 'profile_pics', 'contact'];
        $firstTable = array_shift($table);
        $memberData = parent::joinAll2(firstTable: $firstTable, para: 'id', table: $table, orderBy: 'date_created');

        if (!$memberData) {

            throw new Exception(self::ERR_MSG, 400);
        }

            // dump($memberData);
        return $memberData;
    }

  


    public function getAllMembersNoPics(): array
    {
        $table = ['personal', "contact", "profile_pics", 'otherFamily'];
        $firstTable = array_shift($table);
        $memberData = parent::joinAll4(firstTable: $firstTable, para: 'id', table: $table, orderBy: 'date_created');

        if (!$memberData) {
            // http_response_code(400);
            // echo http_response_code();
            throw new Exception(self::ERR_MSG, 400);
        }


        return $memberData;
    }

    public function getAllMembersById($id)
    {

        $table = ['personal', 'otherFamily', 'profile_pics', 'post',  'contact'];
        //  $table = ['personal', 'otherFamily', 'profile_pics', 'post', 'contact'];
        $firstTable = array_shift($table);

        $memberData = $this->joinParam($firstTable, 'id', 'id', $table, $id);

        return $memberData ??= throw new Exception(self::ERR_MSG, 1);
    }

// show information of events within 7 days , 1 days and on the current date
    public static function getEventData()
    {
        try {
            $query = "SELECT events.no, events.eventName, events.eventDate, events.eventType, events.eventFrequency,events.eventGroup, events.eventDescription, personal.firstName, personal.lastName 
            FROM events 
            INNER JOIN personal ON events.id = personal.id 
            WHERE events.eventDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            OR events.eventDate = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            OR events.eventDate = CURDATE()
            ORDER BY eventDate ASC";
            $result = parent::connect2()->prepare($query);
            $result->execute();
            return $result->fetchAll();
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public static function getMembers($table, $orderBy): array
    {
        $firstTable = array_shift($table);
        $memberData = parent::joinAll2($firstTable, 'id', $table, $orderBy);
        return $memberData ??= throw new Exception(self::ERR_MSG, 1);
    }

    public static function getBirthdayMonth()
    {
        try {

            $select = Select::formAndMatchQuery(selection: "SELECT_COL_DYNAMICALLY", colArray: ['day', 'month', 'firstName'], table: "personal");
            return Select::selectFn2(query: $select);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
