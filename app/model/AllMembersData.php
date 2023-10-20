<?php

declare(strict_types=1);

namespace App\model;

use Exception;
use PDOException;
use PDO;

use App\classes\{
    InnerJoin,
    Select
};

class AllMembersData extends InnerJoin
{
    private const ERR_MSG = "Member Data Error";

    // public function getAllMembers(): array
    // {
    //     $table = ['personal', 'otherFamily', 'profile_pics', 'contact', 'requestMgt'];
    //     $firstTable = array_shift($table);
    //     $memberData = parent::joinAll2(firstTable: $firstTable, para: 'id', table: $table, orderBy: 'date_created');

    //     if (!$memberData) {

    //         throw new Exception(self::ERR_MSG, 400);
    //     }
    //     return $memberData;
    // }

    public function getAllMembers($id): array
    {
        try {
            $query = "SELECT p.id, p.firstName, p.lastName, p.famCode, p.created_at, ofm.fatherName, ofm.motherName, ofm.spouseName, pp.img, c.email, c.country, c.mobile,  rm.requester_id, rm.approver_id,  rm.status, rm.requesterCode
                FROM personal AS p
                  INNER JOIN otherFamily AS ofm ON p.id = ofm.id
                  INNER JOIN profile_pics AS pp ON p.id = pp.id
                  INNER JOIN contact AS c ON p.id = c.id
                   LEFT JOIN (
                      SELECT requester_id, approver_id, status, requesterCode
                      FROM requestMgt
                      WHERE requester_id IS NOT NULL AND requester_id = :id
                  ) AS rm ON p.id = rm.approver_id";

            $result = self::connect2()->prepare($query);
            $result->bindValue(':id', $id, PDO::PARAM_STR);
            $result->execute();

            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }



    public function getAllMembersNoPics(): array
    {
        $table = ['personal', "contact", "profile_pics", 'otherFamily'];
        $firstTable = array_shift($table);
        $memberData = parent::joinAll4(firstTable: $firstTable, para: 'id', table: $table, orderBy: 'date_created');

        if (!$memberData) {

            throw new Exception(self::ERR_MSG, 400);
        }


        return $memberData;
    }

    public static function getAllMembersNoPicsStatic(): array
    {
        $table = ['personal', "contact", "profile_pics", 'otherFamily'];
        $firstTable = array_shift($table);
        $memberData = parent::joinAll4(firstTable: $firstTable, para: 'id', table: $table, orderBy: 'date_created');

        if (!$memberData) {

            throw new Exception(self::ERR_MSG, 400);
        }


        return $memberData;
    }

    public function getAllMembersById($id)
    {

        $table = ['personal', 'otherFamily', 'profile_pics', 'post',  'contact'];

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

      // show information of events within 7 days , 1 days and on the current date
    public static function getEventDataByNo($eventNo)
    {
        try {
            $query = "SELECT events.no, events.eventName, events.eventDate, events.eventType, events.eventFrequency,events.eventGroup, events.eventDescription, personal.firstName, personal.lastName 
            FROM events 
            INNER JOIN personal ON events.id = personal.id 
            WHERE events.eventDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            OR events.eventDate = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            OR events.eventDate = CURDATE()
            AND events.no = $eventNo
            ORDER BY eventDate ASC";
            $result = parent::connect2()->prepare($query);
            $result->execute();
            return $result->fetchAll();
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public static function getFriendRequestData($id, $status)
    {
        try {
            $result = [];
            $select = Select::formAndMatchQuery(selection: "SELECT_AND", table: "requestMgt", identifier1: "approver_id", identifier2: "status");
            $getRequesterDataById = Select::selectFn2(query: $select, bind: [$id, $status]);
            foreach ($getRequesterDataById as $getRequesterDataById) {

                if ($getRequesterDataById['requester_id']) {
                    $custData = new SingleCustomerData();
                    $data = $custData->getCustomerData($getRequesterDataById['requester_id'], ['personal', 'contact', 'profile_pics']);

                    array_push($result, $data);
                } else {
                    $result = " No Requester Data";
                }
            }
            return $result;
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

    // Just get the member's name

    public static function getMemberName($id)
{
    $query = Select::formAndMatchQuery(selection: "SELECT_TWO_COLS_ID", table: "personal", identifier1: "id", column: "firstName", column2: "lastName");
    $result = Select::selectFn2($query, [$id]);
    
    if (isset($result[0]['firstName'], $result[0]['lastName'])) {
        return [
            'fName' => $result[0]['firstName'],
            'lName' => $result[0]['lastName']
        ];
    } else {
        // Handle the case where the query didn't return the expected result.
        msgException(401, "Member not found");
    }
}

}
