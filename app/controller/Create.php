<?php

namespace App\controller;;

use App\classes\tables\{
    Contact,
    Personal,
    Work,
};

use App\controller\Base;

class Create
{
    public function index()
    {
        $table = [
            new Personal,
            new Work,
            new Contact
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
