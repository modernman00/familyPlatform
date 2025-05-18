<?php

declare(strict_types=1);

namespace App\config;

if (!session_start()) {
    session_start([
    'cookie_httponly' => true, // Prevent JavaScript access to session cookie
    'cookie_secure' => true, // Only send cookie over HTTPS
    'use_strict_mode' => true, // Prevent session fixation
    'cookie_samesite' => 'Strict', // Prevent CSRF
    ]);
}


use App\classes\ErrorHandler;

require_once __DIR__ . "/_env.php";



define("BR", "<br>");
define("URL", getenv("APP_URL2"));




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
