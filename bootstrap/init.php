<?php
require __DIR__ ."/../app/config.php";
if(!session_start()){
    session_start();
}
date_default_timezone_set('Europe/London');
// SET ERROR HANDLER 
// set_error_handler([new \App\classes\ErrorHandler(__DIR__ . '/log'), 'handleErrors']); 
new \App\classes\ErrorHandler(__DIR__ . '/log'); 
require __DIR__ ."/../app/router/router.php";
restore_exception_handler();

