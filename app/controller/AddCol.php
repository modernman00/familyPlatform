<?php

namespace App\controller;

// use App\classes\Db;

use App\classes\AlterTable;

class AddCol
{

    public function addCol(): void
    {
        try {
            $add = new AlterTable('notification');
            $add->addNewCol('notification_date', 'TEXT', 'receiver');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function addColArr(): void
    {
        try {
            $cols = ['when'];
            $lastData = 'receiver';
            $add = new AlterTable('notification', $cols);
            $add->addNewColArr($lastData);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
