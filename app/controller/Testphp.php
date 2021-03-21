<?php

declare(strict_types=1);

namespace App\controller;

use App\classes\Select;
use DateInterval;
use DateTime;
use PDO;
use PDOException;

class Testphp extends Select
{
    public $time = 90;

    public function index()
    {

        //        //echo phpinfo();
        //     //     // const BR = ;
        //        // define("BR", "<br>");
        //         // php7
        //         // Null Coalescing Assignment Operator
        //         $number = null;
        //         echo $number ??= "Value not getting it";

        //         $number = [1, 2, 3, 3, 4, 5, 6];
        //         $num = [10, 20, 30, 30, 40, 50, 60];
        //         $name = ['olawale', 'james', 'emily'];

        //         $cBack = fn ($x) =>  $x * 3;

        //          \array_filter($number, $cBack);

        //         $timer = 4;

        //         array_map(function ($n) use ($timer) {
        //             return $n * $timer;
        //         }, $num);



        //         $money = fn ($s) => $timer * $s * 4;
        //         echo "<br>";
        //         echo "arrow function " . $money(4);
        //         echo "<br>";

        //         $square = array_map($cBack, $number);

        //         $point = strpos("wale", 'l');

        //         // php8

        //        // $even = array_filter(input: $number, callback: $cBack);

        //         echo $point;

        //         $combined = [...$number, ...$name, 23, "james"];

        //          printArr($combined);

        //         //printArr($square);

        //         $data = null;

        //         $checkData = $data ?? "Nothing";

        //         $checkData ??= "Nobody";

        //         //  echo $checkData;

        //         // echo phpinfo();
        //         // union type
        //         function foo(int | float $a, int | string $b = 20)
        //         {
        //             return "union type :" . $a * $b;
        //         };

        //         echo foo(5.2);

        //         function foo2(int | float $a, int | float $b = 20): int | float | string
        //         {
        //             if ($a % 2 === 0) {
        //                 $a /= 2;
        //             }

        //             return $a * $b;
        //         };

        //         $ten = 10.0;
        //         $olp = 7;

        //         echo BR;
        //         echo foo2($ten, $olp);


        //         function getInput(int | float ...$number)
        //         {
        //             return array_sum($number);
        //         }
        //         echo BR;
        //         echo getInput(12, 23, 44, 45);

        //         function getInput2(...$arg)
        //         {
        //             printArr($arg);
        //         }
        //         $age = [12, 14];
        //         $dob = ["ade", "son"];
        //         $arguments = [$age, $dob];
        //         echo BR;
        //         echo get_debug_type($arguments);

        //         echo getInput2($arguments[1]);

        //         // named arguments

        //         function namedArg($xi, $y)
        //         {
        //             return $xi / $y;
        //         }

        //         echo namedArg(y:5, xi: 10);

        //     //     //

        //         $_SESSION['NAME'] = "Olawale";
        //         $_SESSION['EMAIL'] = "wale@yahoo.com";

        //         $_SESSION['DATE'] ??= "15TH JULY";
        //         echo $_SESSION['DATE'];

        //         echo \str_contains('we are doing well', 'well');
        //         echo BR;
        //         echo str_start('wale', 'ola');
        //         echo BR;
        //         echo \str_starts_with('Olawale', 'Ol');

        //         // null safe operators
        //         echo BR;
        //         $about = new About();
        //         $check =  $about?->testing();

        //         print_r(\realpath_cache_size());

        //         for ($x = 0; $x < 4; $x++) {
        //             echo " this is an that loop $x" . BR;
        //         }

        //         echo "it is well" . srand() . BR;

        //         echo "<script>alert(it worked)</script>" . BR;


        //      //echo phpinfo();
        //     // var_dump(php_ini_loaded_file(), php_ini_scanned_files());

        //     // DATE AND TIME

        //         $raw = "22.11.2002";
        //         $start = DateTime::createFromFormat('d. m. Y', $raw);
        //         echo 'this is the time: ' . $start->format('d-m-y') . BR;

        //         $end = clone $start; // create a copy of the start
        //         $end->add(new DateInterval('P1M6D'));
        //         $diff = $end->diff($start);
        //         echo "Difference: {$diff->format('%m month, %d days (total: %a days')}." . BR;

        //         echo \strpos("we are fine", "f") . BR;
        //         echo strlen("olwale") . BR;
        //         echo \mb_substr("Adeniji", 1);

        //     //$email = \filter_var($_POST['email'], \FILTER_VALIDATE_EMAIL);

        // // using MATCH
        //         $email = "femi";

        //         $outcome = match($email) {
        //         'Olawale' => "The email has been found",
        //         null => "Nothing was found",
        //         default => "unknown email"
        //         };

        //             printMemory();
        //             echo $outcome;
        //             printMemory();

        // // CHECK PASSWORD HASH AND COST

        //             $time = 0.1;
        //             $cost = 11;
        // // do {
        // //     $cost++;
        // //     $start = microtime(true);
        // //     password_hash('heineken2', PASSWORD_DEFAULT, ['cost' => 5]);
        // //     $end = microtime(true);
        // // } while (($end - $start ) < $time);
        // //     echo "Cost found : $cost";


        //     // URL ENCODING
        //             $id = \urlencode("olawale");
        //             $set = "olaogun.de.com?user=$id";
        //             echo "<h1>this is the way</h1> " . $set;

        $this->getGenerator3();
    }


    // GENERATOR 

    public function getGenerator()
    {
        function getRange($max = 10)
        {
            $array = [];

            for ($i = 1; $i < $max; $i++) {
                $array[] = $i;
            }

            return $array;
        }

        foreach (getRange(10) as $range) {
            echo "Dataset {$range} <br>";
        }
    }

    public function getGenerator2()
    {
        function getRange2($max = 10)
        {
            for ($i = 1; $i < $max; $i++)
            {
                yield $i;
            }
                
        }

        foreach (getRange2(PHP_INT_MAX) as $range) {
            echo "Dataset2 {$range} <br>";
        }
    }
    // generator as a key value pair
    public function getGenerator3()
    {
        function getRange3($max = 10)
        {
            for ($i = 1; $i < $max; $i++) {
                $value = $i * mt_rand();
                yield $i => $value;
            }
        }

        foreach (getRange3(13) as $range => $value) {
            echo "Dataset {$range} has {$value} value<br>";
        }
    }
    // sending values to generator 

    public function getGenerator4()
    {
        function getRange4 ($max = 10) {
            for ($i = 1; $i < $max; $i++) {
                $injected = yield $i;

                if ($injected === 'stop') return;
            }
        }
    }

}
