<?php

require __DIR__ ."/../app/config.php";

if(!session_start()){
    session_start();
}

date_default_timezone_set('Europe/London');

// SET ERROR HANDLER 

require __DIR__ ."/../app/router/router.php";

