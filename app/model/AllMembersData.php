<?php
declare(strict_types =1);

namespace App\model;
use Exception;

use App\classes\InnerJoin;
class AllMembersData extends InnerJoin
{
    private const ERR_MSG = "Member Data Error";

    public function getAllMembers()
    {
        $table = ['personal','otherFamily','profile_pics', 'contact'];
        $firstTable = array_shift($table);
        $memberData = parent::joinAll2(firstTable:$firstTable, para:'id', table:$table, orderBy:'date_created');

        if(!$memberData) {
             http_response_code(400);
        echo http_response_code();
       throw new Exception(self::ERR_MSG, 1);
        }
    
       
       return $memberData;
    }

     public function getAllMembersById($id)
    {
        
          $table = ['personal','otherFamily', 'profile_pics', 'interest', 'post', 'images', 'contact'];
        $firstTable = array_shift($table);
       
        $memberData = $this->joinParam($firstTable, 'id', 'id', $table, $id );

        return $memberData??= throw new Exception(self::ERR_MSG, 1);
        
    }

    static function getMembers($table, $orderBy) : array
    {
        $firstTable = array_shift($table);
        $memberData = parent::joinAll2($firstTable, 'id', $table, $orderBy);
        return $memberData??= throw new Exception(self::ERR_MSG, 1);
    }

}