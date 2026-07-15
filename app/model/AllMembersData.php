<?php

declare(strict_types=1);

namespace App\model;

use Exception;
use PDOException;
use PDO;

use Src\{
    InnerJoin,
    Select
};

class AllMembersData extends InnerJoin
{
    private const string ERR_MSG = "Member Data Error";

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
     * @param array|null|string $id
     */
    public function getAllMembers(array|string|null $id): array
    {
        try {
            $query = "SELECT p.id, p.firstName, p.lastName, p.famCode, p.created_at, ofm.fatherName, ofm.motherName, ofm.spouseName, pp.img, c.email, c.country, c.mobile,  rm.requester_id, rm.approver_id,  rm.status, rm.requesterCode
                FROM personal AS p
                  INNER JOIN otherFamily AS ofm ON p.id = ofm.id
                  INNER JOIN profilePics AS pp ON p.id = pp.id
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



    // this code picks all the famcode and those who accepted the request. 

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
     * Gets visible posts for multiple family codes, implementing the Unified Feed.
     */
    public static function getVisiblePosts(string $id, array $famCodes, int $limit, int $offset): array
    {
        try {
            if (empty($famCodes)) {
                return [];
            }
            $inQuery = implode(',', array_fill(0, count($famCodes), '?'));
            
            $query = "SELECT post.*, pp.img, rm.requester_id, rm.approver_id, rm.status, rm.requesterCode
              FROM post
              INNER JOIN profilePics pp ON post.id = pp.id
              LEFT JOIN (
                      SELECT requester_id, approver_id, status, requesterCode
                      FROM requestMgt
                      WHERE requester_id IS NOT NULL AND requester_id = ?
                  ) AS rm ON post.id = rm.approver_id
            WHERE (post.postFamCode IN ($inQuery) OR post.id = rm.approver_id) 
            AND post.post_status = 'published'
            ORDER BY post.date_created DESC
            LIMIT ? OFFSET ?";
            
            $params = [$id];
            $params = array_merge($params, $famCodes);
            $params[] = $limit;
            $params[] = $offset;

            $result = parent::connect2()->prepare($query);
            foreach ($params as $key => $val) {
                if (is_int($val)) {
                    $result->bindValue($key + 1, $val, PDO::PARAM_INT);
                } else {
                    $result->bindValue($key + 1, $val, PDO::PARAM_STR);
                }
            }
            
            $result->execute();
            $posts = $result->fetchAll(PDO::FETCH_ASSOC);

            // Fetch attached polls and reactions (Engagement Integration)
            if (!empty($posts)) {
                $postIds = array_column($posts, 'post_no');
                
                $polls = PollData::getPollsForPosts($postIds, $id);
                $reactions = ReactionData::getReactionsForPosts($postIds);
                $userReactions = ReactionData::getUserReactionsForPosts($id, $postIds);

                // Re-map into posts array
                foreach ($posts as &$post) {
                    $pNo = $post['post_no'];
                    $post['poll'] = $polls[$pNo] ?? null;
                    
                    $post['reactions'] = array_filter($reactions, function($r) use ($pNo) {
                        return $r['post_no'] == $pNo;
                    });
                    
                    $post['user_reaction'] = null;
                    foreach ($userReactions as $ur) {
                        if ($ur['post_no'] == $pNo) {
                            $post['user_reaction'] = $ur['reaction_type'];
                            break;
                        }
                    }
                }
            }

            return $posts;
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }

    /**
     * Gets posts from exactly one year ago today
     */
    public static function getMemories(string $id, array $famCodes): array
    {
        try {
            if (empty($famCodes)) return [];
            
            $inQuery = implode(',', array_fill(0, count($famCodes), '?'));
            
            // Query for posts created exactly 1 year ago (matching month and day)
            // Use range scan instead of DATE() function for index optimization
            $query = "SELECT post.*, pp.img, rm.requester_id, rm.approver_id, rm.status, rm.requesterCode
              FROM post
              INNER JOIN profilePics pp ON post.id = pp.id
              LEFT JOIN (
                      SELECT requester_id, approver_id, status, requesterCode
                      FROM requestMgt
                      WHERE requester_id IS NOT NULL AND requester_id = ?
                  ) AS rm ON post.id = rm.approver_id
            WHERE (post.postFamCode IN ($inQuery) OR post.id = rm.approver_id) 
            AND post.post_status = 'published'
            AND post.date_created >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
            AND post.date_created < DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), INTERVAL 1 DAY)
            ORDER BY post.date_created DESC";
            
            $params = [$id];
            $params = array_merge($params, $famCodes);

            $result = parent::connect2()->prepare($query);
            foreach ($params as $key => $val) {
                $result->bindValue($key + 1, $val, PDO::PARAM_STR);
            }
            
            $result->execute();
            $posts = $result->fetchAll(PDO::FETCH_ASSOC);

            // Fetch attached polls and reactions
            if (!empty($posts)) {
                $postIds = array_column($posts, 'post_no');
                
                $polls = PollData::getPollsForPosts($postIds, $id);
                $reactions = ReactionData::getReactionsForPosts($postIds);
                $userReactions = ReactionData::getUserReactionsForPosts($id, $postIds);

                foreach ($posts as &$post) {
                    $pNo = $post['post_no'];
                    $post['poll'] = $polls[$pNo] ?? null;
                    $post['reactions'] = array_filter($reactions, function($r) use ($pNo) { return $r['post_no'] == $pNo; });
                    $post['user_reaction'] = null;
                    foreach ($userReactions as $ur) {
                        if ($ur['post_no'] == $pNo) {
                            $post['user_reaction'] = $ur['reaction_type'];
                            break;
                        }
                    }
                }
            }

            return $posts;
        } catch (PDOException $e) {
            showError($e);
            return [];
        }
    }

    /**
     * Counts visible posts for multiple family codes.
     */
    public static function countVisiblePosts(string $id, array $famCodes): int
    {
        try {
            if (empty($famCodes)) {
                return 0;
            }
            $inQuery = implode(',', array_fill(0, count($famCodes), '?'));
            
            $query = "SELECT COUNT(*) as total
              FROM post
              LEFT JOIN (
                      SELECT requester_id, approver_id, status, requesterCode
                      FROM requestMgt
                      WHERE requester_id IS NOT NULL AND requester_id = ?
                  ) AS rm ON post.id = rm.approver_id
            WHERE (post.postFamCode IN ($inQuery) OR post.id = rm.approver_id) 
            AND post.post_status = 'published'";
            
            $params = [$id];
            $params = array_merge($params, $famCodes);

            $result = parent::connect2()->prepare($query);
            foreach ($params as $key => $val) {
                $result->bindValue($key + 1, $val, PDO::PARAM_STR);
            }
            $result->execute();
            return (int) $result->fetchColumn();
        } catch (PDOException $e) {
            showError($e);
            return 0;
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
     * @param array|null|string $id
     *
     * @return array An array containing the email, family code, first name, last name, and ID of each member.
     */
    public static function AllMembersEmailByFamCode($famCode, array|string|null $id): array
    {
        try {
            $query = "SELECT a.email, p.famCode, p.firstName, p.lastName, a.id 
                      FROM account a
                      INNER JOIN personal p ON a.id = p.id                    
                      WHERE (p.famCode = :famCode)";
            $result = parent::connect2()->prepare($query);
            $result->execute(['famCode' => $famCode, 'id' => $id]);
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
        $memberData = parent::joinAll2(para: 'id', table: $table, orderBy: 'date_created');

        if (!$memberData) {

            throw new Exception(self::ERR_MSG, 400);
        }


        return $memberData;
    }

    public function getAllMembersById(array|string|null $id): array|bool
    {

        $table = ['personal', 'otherFamily', 'profilePics', 'post',  'contact'];

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
    /**
     * @param array|null|string $eventNo
     */
    public static function getEventDataByNo(array|string|null $eventNo): array
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

    /**
     * @param array|null|string $famCode
     */
    public static function getEventDataByFamCode(array|string|null $famCode): array
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







    /**
     * @psalm-param 'Request sent' $status
     *
     * @return array[]|null|string
     *
     * @psalm-return ' No Requester Data'|list{0?: array,...}|null
     */
    public static function getFriendRequestData(string $id, string $status)
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

    // Just get the member's name

    public static function getFamCode($id): array
    {
        try {
            $query = "SELECT family_code FROM user_families WHERE user_id = :id AND status = 'approved'";
            $statement = parent::connect2()->prepare($query);
            $statement->execute(['id' => $id]);
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            
            $famCodes = array_column($results, 'family_code');
            
            // Fallback to legacy personal table if no rows exist in user_families (for transition safety)
            if (empty($famCodes)) {
                $legacyQuery = "SELECT famCode FROM personal WHERE id = :id";
                $legacyStatement = parent::connect2()->prepare($legacyQuery);
                $legacyStatement->execute(['id' => $id]);
                $legacyResult = $legacyStatement->fetch(PDO::FETCH_ASSOC);
                if ($legacyResult) {
                    $famCodes[] = $legacyResult['famCode'];
                }
            }

            return [
                'famCode' => $famCodes // Return array of codes instead of single string
            ];
        } catch (PDOException $e) {
            showError($e);
            return ['famCode' => []];
        }
    }
}
