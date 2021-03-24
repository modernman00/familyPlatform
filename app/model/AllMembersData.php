<?php
declare(strict_types =1);

namespace App\model;

use App\classes\innerJoin;

class AllMembersData extends innerJoin
{
    public function getAllMembers()
    {
        $firstTable = "personal";
        $table = ['personal', 'otherFamily', 'profile_pics', 'contact', 'siblings'];
        $memberData = $this->joinAll($firstTable, 'id', $table);
        return $memberData??= throw new \Exception("Member Data Error", 1);
        
    }
}