<?php

namespace App\controller;

use App\classes\Db;

use App\classes\tables\{
    Contact,
    Personal,
    Work,
    siblings,
    kids,
    Account,
    Interest
    
};
class Create extends Db
{
    public function index()
    {
        $table = [
            new Personal,
            new Work,
            new Contact,
            new kids,
            new siblings,
            new Account,
            new Interest
        ];
        
        try {
            for ($i = 0; $i < count($table); $i++) {
                $table[$i]->index();
            }
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }

  
}
