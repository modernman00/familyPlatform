<?php

namespace App\controller;

use App\controller\Base;

class Testphp extends Base
{
    function index()
    {
        // php7
        $number = [1, 2, 3, 3, 4,5, 6];
        $num = [10, 20, 30, 30, 40,50, 60];
        $name = ['olawale', 'james', 'emily'];

        $cBack = fn($x)=>$x * 3;

        $even = \array_filter($number, $cBack);
        $timer = 4;

        $ten = array_map(function($n) use ($timer) {
            return $n*$timer;
        }, $num);

        $money = fn($s) => $timer * $s * 4;
        echo "<br>";
        echo $money(4); 
        echo "<br>";

        $square = array_map($cBack, $number);

        $point = strpos("wale", 'l');

        // php8

        // $even = array_filter(array: $number, func: $cBack);

        echo $point;

        $combined = [...$number, ...$name, 23, "james" ];

        printArr($combined);

        printArr($square);

        $data = null;

       $checkData = $data ?? "Nothing";

        $checkData ??= "Nobody";

        echo $checkData;


    }
}