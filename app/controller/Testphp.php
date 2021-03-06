<?php

declare(strict_types=1);

namespace App\controller;

use App\controller\{Base, About};

class Testphp extends Base
{

    public function index()
    {
    //     // const BR = ;
       define("BR", "<br>");
        // php7
        $number = [1, 2, 3, 3, 4, 5, 6];
        $num = [10, 20, 30, 30, 40, 50, 60];
        $name = ['olawale', 'james', 'emily'];

        $cBack = fn ($x) => $x * 3;

        $even = \array_filter($number, $cBack);
        $timer = 4;

        $ten = array_map(function ($n) use ($timer) {
            return $n * $timer;
        }, $num);

        $money = fn ($s) => $timer * $s * 4;
        echo "<br>";
        echo "arrow function " . $money(4);
        echo "<br>";

        $square = array_map($cBack, $number);

        $point = strpos("wale", 'l');

        // php8

       // $even = array_filter(input: $number, callback: $cBack);

        echo $point;

        $combined = [...$number, ...$name, 23, "james"];

         printArr($combined);

        //printArr($square);

        $data = null;

        $checkData = $data ?? "Nothing";

        $checkData ??= "Nobody";

        //  echo $checkData;

        // echo phpinfo();
        // union type
        function foo(int|float $a, int|string $b = 20)
        {
            return "union type :" . $a * $b;
        };
        echo foo(5.2);

        function foo2(int|float $a, int|float $b = 20): int|float|string
        {
            if ($a % 2 === 0) {
                $a /= 2;
            }

            return $a * $b;
        };

        $ten = 10.0;
        $olp = 7;

        echo BR;
        echo foo2($ten, $olp);


        function getInput (int|float ...$number) {
           return array_sum($number);
        }
        echo BR;
        echo getInput(12, 23, 44, 45);

        function getInput2(...$arg) {
            printArr($arg);
        }
        $age = [12, 14];
        $dob = ["ade", "son"];
        $arguments = [$age, $dob];
        echo BR;
        echo get_debug_type($arguments);

        echo getInput2($arguments[1]);

        // named arguments

        function namedArg($xi, $y ) {
            return $xi / $y;
        }

        echo namedArg( y:5, xi: 10);

    //     // 

        $_SESSION['NAME'] = "Olawale";
        $_SESSION['EMAIL']= "wale@yahoo.com";

        $_SESSION['DATE'] ??= "15TH JULY";
        echo $_SESSION['DATE'];

        echo \str_contains('we are doing well', 'well');
        echo BR;
        echo str_start('wale', 'ola');
        echo BR;
        echo \str_starts_with('Olawale', 'Ol');

        // null safe operators
        echo BR;
        $about = new About;
        $check =  $about?->testing();

        print_r(\realpath_cache_size());

        for($x = 0; $x < 4; $x++) {
            echo " this is loop $x".BR;
        }


    // echo phpinfo();
    // var_dump(php_ini_loaded_file(), php_ini_scanned_files());
    //echo xdebug_info();

   


      
    }
}
