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
    //     $table = ['personal', 'otherFamily', 'profilePics', 'contact', 'requestMgt'];
    //     $firstTable = array_shift($table);
    //     $memberData = parent::joinAll2(firstTable: $firstTable, para: 'id', table: $table, orderBy: 'date_created');

    //     if (!$memberData) {

    //         throw new Exception(self::ERR_MSG, 400);
    //     }
    //     return $memberData;
    // }


    /**
     * Retrieve all members of the given family code, including those who have been approved by the given requesterId
     * 
     * @param string $famCode The family code to retrieve members for
     * @param int $requesterId The requesterId to retrieve approved members for
     * @return array An array of all members of the given family code, including those who have been approved by the given requesterId
     * @throws PDOException If there is a problem with the database query
     */
    public function getAllMembers($famCode, $requesterId): array
    {
        try {
            $query = "
            SELECT 
                p.id, p.firstName, p.lastName, p.famCode, p.created_at,
                ofm.father_name, ofm.mother_name, ofm.spouse_name,
                pp.img, c.email, c.country, c.mobile,
                CASE 
                    WHEN p.famCode = :famCode1 THEN 'family'
                    WHEN p.id IN (
                        SELECT approver_id 
                        FROM requestMgt 
                        WHERE requester_id = :requesterId1 
                        AND status = 'Approved'
                    ) THEN 'approved_you'
                    WHEN p.id IN (
                        SELECT requester_id 
                        FROM requestMgt 
                        WHERE approver_id = :requesterId2 
                        AND status = 'Approved'
                    ) THEN 'you_approved'
                    ELSE 'other'
                END AS relationType
            FROM personal AS p
            INNER JOIN otherFamily AS ofm ON p.id = ofm.id
            INNER JOIN profilePics AS pp ON p.id = pp.id
            INNER JOIN contact AS c ON p.id = c.id
            WHERE 
                (
                    p.famCode = :famCode2
                    OR p.id IN (
                        SELECT approver_id 
                        FROM requestMgt 
                        WHERE requester_id = :requesterId3 
                        AND status = 'Approved'
                    )
                    OR p.id IN (
                        SELECT requester_id 
                        FROM requestMgt 
                        WHERE approver_id = :requesterId4 
                        AND status = 'Approved'
                    )
                )
                AND p.id != :Id
            ORDER BY 
                CASE 
                    WHEN p.famCode = :famCode3 THEN 1
                    WHEN p.id IN (
                        SELECT approver_id 
                        FROM requestMgt 
                        WHERE requester_id = :requesterId5 
                        AND status = 'Approved'
                    ) THEN 2
                    ELSE 3
                END,
                p.firstName
        ";

            $stmt = self::connect2()->prepare($query);

            // bind all parameters
            $stmt->bindValue(':famCode1', $famCode);
            $stmt->bindValue(':famCode2', $famCode);
            $stmt->bindValue(':famCode3', $famCode);

            $stmt->bindValue(':requesterId1', $requesterId);
            $stmt->bindValue(':requesterId2', $requesterId);
            $stmt->bindValue(':requesterId3', $requesterId);
            $stmt->bindValue(':requesterId4', $requesterId);
            $stmt->bindValue(':requesterId5', $requesterId);

            $stmt->bindValue(':Id', $requesterId);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }

    /**
     * Retrieve all users in the database, including their personal information, other family members, profile picture, and contact information.
     * 
     * @return array An array of all users in the database
     * @throws \Throwable If there is a problem with the database query
     */
    public function getAllUsersData($userId, $famCode): array
    {
        try {
            $query = "
            SELECT 
                p.id, p.firstName, p.lastName, p.famCode, p.created_at,
                ofm.father_name, ofm.mother_name, ofm.spouse_name,
                pp.img, c.email, c.country, c.mobile,
                rm.requester_id, rm.approver_id, rm.status, rm.requesterCode
            FROM personal AS p
            INNER JOIN otherFamily AS ofm ON p.id = ofm.id
            INNER JOIN profilePics AS pp ON p.id = pp.id
            INNER JOIN contact AS c ON p.id = c.id
            LEFT JOIN (
                SELECT requester_id, approver_id, status, requesterCode
                FROM requestMgt
                WHERE requester_id = :requester_id
            ) AS rm ON p.id = rm.approver_id
            WHERE p.id != :current_user && p.famCode != :famCode
        ";

            $stmt = self::connect2()->prepare($query);
            $stmt->bindValue(':requester_id', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':current_user', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':famCode', $famCode, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            showError($e);
            return [];
        }
    }

    public static function getVisiblePosts(string $userId, string $famCode, int $limit = 50, int $offset = 0): array
    {
        try {
            $query = " SELECT 
                    post.*
                FROM post
                WHERE post.postFamCode = :fam_code
                OR (
                    post.id IN (
                        SELECT approver_id 
                        FROM requestMgt
                        WHERE requester_id = :user_id1 
                        AND status = 'Approved'
                        UNION
                        SELECT requester_id 
                        FROM requestMgt
                        WHERE approver_id = :user_id2 
                        AND status = 'Approved'
                    )
                )
                ORDER BY post.date_created DESC
                LIMIT :limit OFFSET :offset
            ";


            $stmt = self::connect2()->prepare($query);
            $stmt->bindValue(':user_id1', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':user_id2', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':fam_code', $famCode, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            showError($e);
            return [];
        }
    }

    public static function countVisiblePosts(string $userId, string $famCode): int
    {
        try {
            $query = " SELECT 
                    COUNT(*)
                FROM post
                WHERE post.postFamCode = :fam_code
                OR (
                    post.id IN (
                        SELECT approver_id 
                        FROM requestMgt
                        WHERE requester_id = :user_id1 
                        AND status = 'Approved'
                        UNION
                        SELECT requester_id 
                        FROM requestMgt
                        WHERE approver_id = :user_id2 
                        AND status = 'Approved'
                    )
                )
            ";

            $stmt = self::connect2()->prepare($query);
            $stmt->bindValue(':user_id1', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':user_id2', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':fam_code', $famCode, PDO::PARAM_STR);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            showError($e);
            return 0;
        }
    }







    // this code picks all the famcode and those who accepted the request. 

    public static function postProfilePicByFamCode($famCode, $id): array
    {
        try {
            $query = "SELECT post.*, profilePics.img, profilePics.id, rm.requester_id, rm.approver_id,  rm.status, rm.requesterCode
              FROM post
              INNER JOIN profilePics ON post.id = profilePics.id
              LEFT JOIN (
                      SELECT requester_id, approver_id, status, requesterCode
                      FROM requestMgt
                      WHERE requester_id IS NOT NULL AND requester_id = :id
                  ) AS rm ON post.id = rm.approver_id
            WHERE (post.postFamCode = :famCode || post.id = rm.approver_id)
            ORDER BY post.date_created DESC";
            $result = parent::connect2()->prepare($query);
            $result->execute(['famCode' => $famCode, 'id' => $id]);

            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }

    public static function getUnpublishedPostByFamCode($famCode, $id): array
    {
        try {
            $query = "SELECT post.*, rm.requester_id, rm.approver_id,  rm.status, rm.requesterCode
              FROM post
              LEFT JOIN (
                      SELECT requester_id, approver_id, status, requesterCode
                      FROM requestMgt
                      WHERE requester_id IS NOT NULL AND requester_id = :id
                  ) AS rm ON post.id = rm.approver_id
            WHERE ((post.postFamCode = :famCode || post.id = rm.approver_id) AND post.post_status = 'new')
            ORDER BY post.date_created DESC";
            $result = parent::connect2()->prepare($query);
            $result->execute(['famCode' => $famCode, 'id' => $id]);

            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }

    /**
     * Retrieves a list of all members' emails and details associated with a given family code.
     *
     * This function executes a database query to fetch email addresses, family codes, 
     * first names, last names, and IDs of members whose family code matches the provided 
     * parameter. If an error occurs during the database operation, it logs the error and 
     * returns an empty array.
     *
     * @param string $famCode The family code used to filter members.
     * @return array An array containing the email, family code, first name, last name, and ID of each member.
     */
    public static function AllMembersEmailByFamCode($famCode): array
    {
        try {
            $query = "SELECT a.email, p.famCode, p.firstName, p.lastName, a.id 
                      FROM account a
                      INNER JOIN personal p ON a.id = p.id                    
                      WHERE (p.famCode = :famCode)";
            $result = parent::connect2()->prepare($query);
            $result->execute(['famCode' => $famCode]);
            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }

    public static function getAllMembersCodeAndEmail(): array
    {
        try {
            $query = "SELECT a.email, p.famCode AS famCode, a.id 
                      FROM account a
                      INNER JOIN personal p ON a.id = p.id";
            $statement = parent::connect2()->query($query);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }


    public function getAllMembersNoPics(): array
    {
        $table = ['personal', "contact", "profilePics", 'otherFamily'];
        $firstTable = array_shift($table);
        $memberData = parent::joinAll4(firstTable: $firstTable, para: 'id', table: $table, orderBy: 'date_created');

        if (!$memberData) {

            throw new Exception(self::ERR_MSG, 400);
        }


        return $memberData;
    }

    public static function getAllMembersNoPicsStatic(): array
    {
        $table = ['personal', "contact", "profilePics", 'otherFamily'];
        $firstTable = array_shift($table);
        $memberData = parent::joinAll4(firstTable: $firstTable, para: 'id', table: $table, orderBy: 'date_created');

        if (!$memberData) {

            throw new Exception(self::ERR_MSG, 400);
        }


        return $memberData;
    }

    public function getAllMembersById($id)
    {

        $table = ['personal', 'otherFamily', 'profilePics', 'post', 'contact'];

        $firstTable = array_shift($table);

        $memberData = $this->joinParam($firstTable, 'id', 'id', $table, $id);

        return $memberData ??= throw new Exception(self::ERR_MSG, 1);
    }

    // show information of events within 7 days , 1 days and on the current date
    public static function getAllEventData(): array
    {
        try {
            $query = "SELECT events.no, events.eventName, events.eventDate, events.eventType, events.eventFrequency, events.eventDescription, personal.firstName, personal.lastName, personal.famCode 
            FROM events 
            INNER JOIN personal ON events.id = personal.id 
            WHERE events.eventDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            OR events.eventDate = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            OR events.eventDate = CURDATE()
           
            ORDER BY events.eventDate ASC";
            $result = parent::connect2()->prepare($query);
            $result->execute();
            return $result->fetchAll();
        } catch (\Throwable $th) {
            showError($th);
            return [];
        }
    }

    // show information of events within 7 days , 1 days and on the current date
    public static function getEventDataByNo($eventNo): array
    {
        try {
            $query = "SELECT events.no, events.eventName, events.eventDate, events.eventType, events.eventFrequency,  events.eventDescription, personal.firstName, personal.lastName, personal.famCode
        FROM events 
        INNER JOIN personal ON events.id = personal.id 
        WHERE (events.no = :eventNo)
        AND (
            events.eventDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            OR events.eventDate = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            OR events.eventDate = CURDATE()
        )
        ORDER BY events.eventDate ASC";

            $conn = parent::connect2();
            $result = $conn->prepare($query);
            $result->execute(['eventNo' => $eventNo]);
            return $result->fetchAll();
        } catch (\Throwable $th) {
            showError($th);
            return [];
        }
    }

    public static function getEventDataByFamCode($famCode): array
    {
        try {
            $query = "SELECT events.no, events.eventName, events.eventDate, events.eventType, events.eventFrequency, events.eventDescription, personal.firstName, personal.lastName, personal.famCode
        FROM events 
        INNER JOIN personal ON events.id = personal.id 
        WHERE (personal.famCode = :famCode)
        AND (
            events.eventDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            OR events.eventDate = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            OR events.eventDate = CURDATE()
        )
        ORDER BY events.eventDate ASC";

            $conn = parent::connect2();
            $result = $conn->prepare($query);
            $result->execute(['famCode' => $famCode]);
            return $result->fetchAll();
        } catch (\Throwable $th) {
            showError($th);
            return [];
        }
    }


    public static function commentProfilePicByPostNo($postNo): array
    {
        try {
            $query = "SELECT comment.*, profilePics.img, profilePics.id 
              FROM comment
              INNER JOIN profilePics ON comment.id = profilePics.id
            WHERE (comment.post_no = :postNo)
            ORDER BY comment.date_created DESC";
            $result = parent::connect2()->prepare($query);
            $result->execute(['postNo' => $postNo]);

            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }







    public static function getFriendRequestData($id, $status)
    {
        try {
            $result = [];
            $select = Select::formAndMatchQuery(selection: "SELECT_AND", table: "requestMgt", identifier1: "approver_id", identifier2: "status");
            $getRequesterDataById1 = Select::selectFn2(query: $select, bind: [$id, $status]);
            foreach ($getRequesterDataById1 as $getRequesterDataById) {

                if ($getRequesterDataById['requester_id']) {
                    $custData = new SingleCustomerData();
                    $data = $custData->getCustomerData($getRequesterDataById['requester_id'], ['personal', 'contact', 'profilePics']);

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

        $column = ['firstName', 'lastName', 'famCode'];

        $query = Select::formAndMatchQuery(
            selection: "SELECT_COL_DYNAMICALLY_ID",
            colArray: $column,
            table: "personal",
            identifier1: "id"
        );

        $result = Select::selectFn2($query, [$id]);

        if (isset($result[0]['firstName'], $result[0]['lastName'])) {
            return [
                'fName' => $result[0]['firstName'],
                'lName' => $result[0]['lastName'],
                'famCode' => $result[0]['famCode']
            ];
        } else {
            // Handle the case where the query didn't return the expected result.
            msgException(401, "Member not found");
        }
    }

    public static function getFamCode($id): array
    {

        $query = Select::formAndMatchQuery(
            selection: "SELECT_COL_ID",
            table: "personal",
            identifier1: "id",
            column: 'famCode',
        );

        $result = Select::selectFn2($query, [$id]);

        // if (isset($result[0]['famCode'])) {
        // msgSuccess(200, $result[0]['famCode']);

        return [
            'famCode' => $result[0]['famCode']
        ];
        // } else {
        //     // Handle the case where the query didn't return the expected result.
        //     msgException(401, "Member not found");
        // }
    }
}
