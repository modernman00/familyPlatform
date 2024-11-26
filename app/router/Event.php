<?php

$router->map('GET', '/sendEventReminder', 'App\controller\members\Event@sendReminder', 'Send_Event_Reminder');

$router->map('GET', '/member/getEventDataByNo', 'App\controller\members\Event@getEventDataByNoController', 'new event no');

$router->map('POST', '/member/profilePage/event', 'App\controller\members\Event@submitEvent', 'PROCESS_EVENT');


$router->map('GET', '/member/sendReminders', 'App\controller\members\Event@sendReminder', 'SEND_REMINDER_EVENT');


$router->map('GET', '/member/getEventData', 'App\controller\members\Event@getEventData', 'getEventData');

$router->map('GET', '/member/getEventByNo', 'App\controller\members\Event@getEventByNo', 'getEvent_by_no');




