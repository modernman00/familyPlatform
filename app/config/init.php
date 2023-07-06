<?php

declare(strict_types=1);

namespace App\config;

if (!session_start()) {
    session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true
    ]);
}

use Tracy\Debugger;  // https://packagist.org/packages/tracy/tracy

use App\classes\ErrorHandler;

require_once __DIR__ . "/_env.php";

Debugger::enable();
Debugger::$strictMode = true;
Debugger::$logDirectory = __DIR__ . '/../../bootstrap/log';

Debugger::$email = 'waledevtest@gmail.com';

// Debugger::enable(Debugger::Development);



define("BR", "<br>");


/**
 * You should use the mb_internal_encoding() function at the top of every PHP script you write (or at the top of your global include script), and the mb_http_output() function right after it if your script is outputting to a browser. Explicitly defining the encoding of your strings in every script will save you a lot of headaches down the road.
 * https://phptherightway.com/
 */
mb_internal_encoding();
mb_http_output();


date_default_timezone_set('Europe/London');
// SET ERROR HANDLER
// set_error_handler([new \App\classes\ErrorHandler(__DIR__ . '/log'), 'handleErrors']);
$errorHandler = new ErrorHandler();
$errorHandler->outputError(__DIR__ . '/../../bootstrap/log');

// require_once __DIR__ . "/../router/router.php";
restore_exception_handler();
