<?php

namespace App\Controller;

use App\classes\Db;
use App\classes\tables\{
    Contact,
    Personal,
    Work,
    siblings,
    kids,
    Account,
    Interest,
    otherFamily,
    friendRequest,
    Notification
};

class Create extends Db
{
    public function index(): void
    {
        $table = [
            new Personal(),
            new Work(),
            new Contact(),
            new kids(),
            new siblings(),
            new Account(),
            new Interest(),
            new otherFamily()
        ];

        try {
            for ($i = 0; $i < count($table); $i++) {
                $table[$i]->index();
            }
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }

    public function createFriendRequest(): void {

        try {
            $fRequest = new friendRequest();
            $fRequest->index();
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    
    }

      public function notification(): void {

        try {
            Notification::index();
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    
    }
}


