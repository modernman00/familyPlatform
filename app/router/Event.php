<?php

$router->map('GET', '/sendEventReminder', 'App\controller\members\Event@sendReminder', 'Send_Event_Reminder');

$router->map('GET', '/member/getEventDataByNo', 'App\controller\members\Event@getEventDataByNoController', 'new event no');


