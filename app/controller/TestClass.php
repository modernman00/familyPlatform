<?php

declare(strict_types=1);

namespace App\Controller;

use App\classes\{

    Db,

};


class TestClass extends Db
{

    public static function Organogram() {
        return view('test.familyTree');
    }






}
