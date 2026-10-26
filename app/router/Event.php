<?php

$router->map('GET', '/sendEventReminder', 'App\controller\members\Event@sendReminder', 'Send_Event_Reminder');

$router->map('GET', '/member/getEventDataByNo', 'App\controller\members\Event@getEventDataByNoController', 'new event no');

$router->map('POST', '/member/profilePage/event', 'App\controller\members\Event@submitEvent', 'PROCESS_EVENT');


$router->map('GET', '/member/sendReminders', 'App\controller\members\Event@sendReminder', 'SEND_REMINDER_EVENT');


$router->map('GET', '/member/getEventData', 'App\controller\members\Event@getEventData', 'getEventData');

$router->map('GET', '/member/getEventByNo', 'App\controller\members\Event@getEventByNo', 'getEvent_by_no');

// EDIT / DELETE AN EVENT (author only)
$router->map('PUT', '/member/profilePage/event/[i:eventNo]', 'App\controller\members\Event@updateEvent', 'update_event');

$router->map('DELETE', '/member/profilePage/event/[i:eventNo]', 'App\controller\members\Event@deleteEvent', 'delete_event');

// 3-GEAR PARTYPLATFORM STUDIO ESCALATION & SSO HANDOFF (author only)
$router->map('POST', '/member/profilePage/event/[i:eventNo]/upgrade-party', 'App\controller\members\Event@upgradeToPartyPlatform', 'upgrade_event_party');
$router->map('POST', '/member/ProfilePage/event/[i:eventNo]/upgrade-party', 'App\controller\members\Event@upgradeToPartyPlatform', 'upgrade_event_party_alt');

$router->map('GET', '/member/profilePage/event/[i:eventNo]/party-sso', 'App\controller\members\Event@partyStudioSso', 'event_party_sso');
$router->map('GET', '/member/ProfilePage/event/[i:eventNo]/party-sso', 'App\controller\members\Event@partyStudioSso', 'event_party_sso_alt');
$router->map('GET', '/member/profilePage/event/[i:eventNo]/party-sso/', 'App\controller\members\Event@partyStudioSso', 'event_party_sso_slash');
$router->map('GET', '/member/ProfilePage/event/[i:eventNo]/party-sso/', 'App\controller\members\Event@partyStudioSso', 'event_party_sso_alt_slash');

// 3-MONTH INTERACTIVE SCHEDULE MODAL ENDPOINT
$router->map('GET', '/member/events/upcoming3Months', 'App\controller\members\Event@getUpcoming3MonthsEvents', 'upcoming_events_3months');





