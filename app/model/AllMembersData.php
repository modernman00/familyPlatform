<?php
declare(strict_types =1);

namespace App\model;

use App\classes\innerJoin;

class AllMembersData extends innerJoin
{
    public function getAllMembers()
    {
       
        $table = ['personal','otherFamily', 'profile_pics',  'contact', 'siblings'];
        $firstTable = array_shift($table);

        $memberData = $this->joinAll($firstTable, 'id', $table);
        return $memberData??= throw new \Exception("Member Data Error", 1);
        
    }

     public function getAllMembersById($id)
    {
        
          $table = ['personal','otherFamily', 'profile_pics', 'interest', 'post', 'images', 'contact', 'siblings'];;
        $firstTable = array_shift($table);
       
        $memberData = $this->joinParam($firstTable, 'id', 'id', $table, $id );
        return $memberData??= throw new \Exception("Member Data Error", 1);
        
    }
}