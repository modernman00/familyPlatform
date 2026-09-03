<?php

$router->map('GET', '/accountSetting', 'App\controller\members\SettingController@index', 'accountSetting');


$router->map('POST', '/accountSetting', 'App\controller\members\SettingController@post', 'accountSettingPost');

// GDPR Art. 15 / 20 — the member downloads their own personal data (JSON).
$router->map('POST', '/account/data-export', 'App\controller\members\DataPrivacyController@exportData', 'dataExport');

// GDPR Art. 17 — the member requests erasure (routed to ops for manual review).
$router->map('POST', '/account/request-deletion', 'App\controller\members\DataPrivacyController@requestDeletion', 'dataDeletionRequest');
