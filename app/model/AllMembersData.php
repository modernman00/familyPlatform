<?php

namespace App\model;

use App\classes\Select;

class AllMembersData extends Select
{
    public function getAllMembers()
    {
        
        $memberData = $this->joinMany5('personal', 'otherFamily', 'profile_pics', 'contact', 'siblings', 'id');
        if(!$memberData) throw new \Exception("Member Data Error", 1);
    
        return $memberData;
        
    }
}