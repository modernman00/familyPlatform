<?php

namespace App\controller;

// use App\classes\Db;

use App\classes\AlterTable;

class AddCol
{

    public function addCol(): void
    {
        try {
            $cols = ['alias', 'spouse'];
            $lastData = 'lastName';
            $add = new AlterTable('personal', $cols);
            $add->addNewColArr($lastData);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
