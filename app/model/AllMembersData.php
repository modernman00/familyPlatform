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
     * Retrieve all members of the given family code, including those who have been
     * approved by / with the given requesterId.
     */
    public function getAllMembers(string $famCode, string $requesterId): array
    {
        try {
            $sql = "
            SELECT 
                p.id,
                p.firstName,
                p.lastName,
                p.famCode,
                p.created_at,
                ofm.father_name,
                ofm.mother_name,
                ofm.spouse_name,
                pp.img,
                c.email,
                c.country,
                c.mobile,
                CASE 
                    WHEN p.famCode = :famCode_case THEN 'family'
                    WHEN rm.requester_id = :req_case_approved_you
                         AND rm.approver_id = p.id
                         AND rm.status = 'approved'
                         THEN 'approved_you'
                    WHEN rm.approver_id = :req_case_you_approved
                         AND rm.requester_id = p.id
                         AND rm.status = 'approved'
                         THEN 'you_approved'
                    ELSE 'other'
                END AS relationType
            FROM personal AS p
            LEFT JOIN otherFamily AS ofm ON p.id = ofm.id
            INNER JOIN profilePics AS pp   ON p.id = pp.id
            INNER JOIN contact AS c        ON p.id = c.id
            LEFT JOIN requestMgt AS rm
                ON (
                    (rm.requester_id = :req_join_outgoing AND rm.approver_id = p.id)
                    OR
                    (rm.approver_id = :req_join_incoming AND rm.requester_id = p.id)
                )
                AND rm.status = 'approved'
            WHERE 
                p.id != :req_where_not_me
                AND (
                    p.famCode = :famCode_where_family
                    OR rm.status = 'approved'
                )
            ORDER BY 
                CASE 
                    WHEN p.famCode = :famCode_order_family THEN 1
                    WHEN rm.requester_id = :req_order_outgoing
                         AND rm.approver_id = p.id
                         AND rm.status = 'approved'
                         THEN 2
                    WHEN rm.approver_id = :req_order_incoming
                         AND rm.requester_id = p.id
                         AND rm.status = 'approved'
                         THEN 3
                    ELSE 4
                END,
                p.firstName
        ";

            $stmt = self::connect2()->prepare($sql);

            // famCode params
            $stmt->bindValue(':famCode_case', $famCode, PDO::PARAM_STR);
            $stmt->bindValue(':famCode_where_family', $famCode, PDO::PARAM_STR);
            $stmt->bindValue(':famCode_order_family', $famCode, PDO::PARAM_STR);

            // requesterId params
            $stmt->bindValue(':req_case_approved_you', $requesterId, PDO::PARAM_INT);
            $stmt->bindValue(':req_case_you_approved', $requesterId, PDO::PARAM_INT);

            $stmt->bindValue(':req_join_outgoing', $requesterId, PDO::PARAM_INT);
            $stmt->bindValue(':req_join_incoming', $requesterId, PDO::PARAM_INT);

            $stmt->bindValue(':req_where_not_me', $requesterId, PDO::PARAM_INT);

            $stmt->bindValue(':req_order_outgoing', $requesterId, PDO::PARAM_INT);
            $stmt->bindValue(':req_order_incoming', $requesterId, PDO::PARAM_INT);

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Remove duplicate users by ID
            $result2 = $this->uniqueById($result);

            return $result2;
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }

    /**
     * Remove duplicate rows from a result set based on the "id" field.
     *
     * This function scans the array returned from a database query and ensures
     * that each unique "id" appears only once in the final result. When duplicates
     * exist (for example, due to multiple JOIN matches in requestMgt), the first
     * occurrence of each id is kept and subsequent duplicates are ignored.
     *
     * @param array $rows The array of rows fetched from the database.
     * @return array A new array containing only one entry per unique "id".
     */
    private function uniqueById(array $rows): array
    {
        $unique = [];

        foreach ($rows as $row) {
            $id = $row['id'];

            // Keep the first occurrence of each ID
            if (!isset($unique[$id])) {
                $unique[$id] = $row;
            }

            // If you prefer to overwrite with the last occurrence:
            // $unique[$id] = $row;
        }

        return array_values($unique);
    }



    /**
     * Retrieve all users in the database, including their personal information, other family members, profile picture, and contact information.
     * 
     * @return array An array of all users in the database
     * @throws \Throwable If there is a problem with the database query
     */
    public function getAllUsersData(string $userId, string $famCode): array
    {
        try {
            $sql = "
            SELECT 
                p.id,
                p.firstName,
                p.lastName,
                p.famCode,
                p.created_at,
                ofm.father_name,
                ofm.mother_name,
                ofm.spouse_name,
                pp.img,
                c.email,
                c.country,
                c.mobile,
                rm.requester_id,
                rm.approver_id,
                rm.status,
                rm.requesterCode
            FROM personal AS p
            LEFT JOIN otherFamily AS ofm ON p.id = ofm.id
            INNER JOIN profilePics AS pp   ON p.id = pp.id
            INNER JOIN contact AS c        ON p.id = c.id
            LEFT JOIN requestMgt AS rm
                ON (
                    (rm.requester_id = :userId_join_outgoing AND rm.approver_id = p.id)
                    OR
                    (rm.approver_id = :userId_join_incoming AND rm.requester_id = p.id)
                )
            WHERE 
                p.id != :userId_where_not_me
                AND p.famCode != :famCode
        ";

            $stmt = self::connect2()->prepare($sql);
            // each placeholder is unique & bound exactly once
            $stmt->bindValue(':userId_join_outgoing', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':userId_join_incoming', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':userId_where_not_me', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':famCode', $famCode, PDO::PARAM_STR);


            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Remove duplicate users by ID
            $result2 = $this->uniqueById($result);

            return $result2;
        } catch (\Throwable $e) {
            showError($e);
            return [];
        }
    }


    public function searchMembers(
        int $requesterId,
        string $famCode,
        string $term,
        int $limit = 30,
        int $offset = 0
    ): array {
        // if search term is empty, just return empty and let controller / frontend
        // fall back to processApiData()
        if ($term === '') {
            return [];
        }

        try {
            $like = "%$term%";

            $sql = "
            SELECT 
                p.id,
                p.firstName,
                p.lastName,
                p.famCode,
                p.created_at,
                ofm.father_name,
                ofm.mother_name,
                ofm.spouse_name,
                pp.img,
                c.email,
                c.country,
                c.mobile,
                CASE 
                    WHEN p.famCode = :famCode_case THEN 'family'
                    WHEN rm.requester_id = :req_case_approved_you
                         AND rm.approver_id = p.id
                         AND rm.status = 'approved'
                         THEN 'approved_you'
                    WHEN rm.approver_id = :req_case_you_approved
                         AND rm.requester_id = p.id
                         AND rm.status = 'approved'
                         THEN 'you_approved'
                    ELSE 'other'
                END AS relationType,
                rm.requester_id,
                rm.approver_id,
                rm.status,
                rm.requesterCode
            FROM personal AS p
            LEFT JOIN otherFamily AS ofm ON p.id = ofm.id
            INNER JOIN profilePics AS pp   ON p.id = pp.id
            INNER JOIN contact AS c        ON p.id = c.id
            LEFT JOIN requestMgt AS rm
                ON (
                    (rm.requester_id = :req_join_outgoing AND rm.approver_id = p.id)
                    OR
                    (rm.approver_id = :req_join_incoming AND rm.requester_id = p.id)
                )
            WHERE
                p.id != :req_where_not_me
                AND (
                    p.firstName LIKE :search_firstName
                    OR p.lastName LIKE :search_lastName
                    OR c.email LIKE :search_email
                    OR c.mobile LIKE :search_mobile
                    OR p.famCode LIKE :search_famCode
                    OR c.country LIKE :search_country
                )
            ORDER BY
                CASE 
                    WHEN p.famCode = :famCode_order_family THEN 1
                    WHEN rm.requester_id = :req_order_outgoing
                         AND rm.approver_id = p.id
                         AND rm.status = 'approved'
                         THEN 2
                    WHEN rm.approver_id = :req_order_incoming
                         AND rm.requester_id = p.id
                         AND rm.status = 'approved'
                         THEN 3
                    ELSE 4
                END,
                p.firstName
            LIMIT :limit_rows
            OFFSET :offset_rows
        ";

            $stmt = self::connect2()->prepare($sql);

            // --- famCode placeholders ---
            $stmt->bindValue(':famCode_case', $famCode, PDO::PARAM_STR);
            $stmt->bindValue(':famCode_order_family', $famCode, PDO::PARAM_STR);

            // --- requesterId placeholders used in CASE (relationType) ---
            $stmt->bindValue(':req_case_approved_you', $requesterId, PDO::PARAM_INT);
            $stmt->bindValue(':req_case_you_approved', $requesterId, PDO::PARAM_INT);

            // --- requesterId placeholders used in JOIN ---
            $stmt->bindValue(':req_join_outgoing', $requesterId, PDO::PARAM_INT);
            $stmt->bindValue(':req_join_incoming', $requesterId, PDO::PARAM_INT);

            // --- requesterId placeholder used in WHERE p.id != ---
            $stmt->bindValue(':req_where_not_me', $requesterId, PDO::PARAM_INT);

            // --- requesterId placeholders used in ORDER BY ---
            $stmt->bindValue(':req_order_outgoing', $requesterId, PDO::PARAM_INT);
            $stmt->bindValue(':req_order_incoming', $requesterId, PDO::PARAM_INT);

            // --- search term placeholders (all the same LIKE value) ---
            $stmt->bindValue(':search_firstName', $like, PDO::PARAM_STR);
            $stmt->bindValue(':search_lastName', $like, PDO::PARAM_STR);
            $stmt->bindValue(':search_email', $like, PDO::PARAM_STR);
            $stmt->bindValue(':search_mobile', $like, PDO::PARAM_STR);
            $stmt->bindValue(':search_famCode', $like, PDO::PARAM_STR);
            $stmt->bindValue(':search_country', $like, PDO::PARAM_STR);

            // --- pagination ---
            $stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset_rows', $offset, PDO::PARAM_INT);

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Remove duplicate users by ID
            $result2 = $this->uniqueById($result);

            return $result2;
        } catch (PDOException $e) {
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
                        AND status = 'approved'
                        UNION
                        SELECT requester_id 
                        FROM requestMgt
                        WHERE approver_id = :user_id2 
                        AND status = 'approved'
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
                        AND status = 'approved'
                        UNION
                        SELECT requester_id 
                        FROM requestMgt
                        WHERE approver_id = :user_id2 
                        AND status = 'approved'
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
