<?php

$router->map('GET', '/accountSetting', 'App\controller\members\SettingController@index', 'accountSetting');

// Defensive redirect: map legacy /member/settings requests to /accountSetting#v-pills-familycode
$router->map('GET', '/member/settings', function() {
    $tab = trim((string)($_GET['tab'] ?? ''));
    $hash = ($tab === 'lineage' || $tab === 'familycode') ? '#v-pills-familycode' : '';
    header('Location: /accountSetting' . $hash, true, 302);
    exit;
}, 'memberSettingsRedirect');


$router->map('POST', '/accountSetting', 'App\controller\members\SettingController@post', 'accountSettingPost');

// GDPR Art. 15 / 20 — the member downloads their own personal data (JSON).
$router->map('POST', '/account/data-export', 'App\controller\members\DataPrivacyController@exportData', 'dataExport');

// GDPR Art. 17 — the member requests erasure (routed to ops for manual review).
$router->map('POST', '/account/request-deletion', 'App\controller\members\DataPrivacyController@requestDeletion', 'dataDeletionRequest');

// Selective Member Visibility & Content Restrictions
$router->map('GET', '/api/privacy/restrictions', 'App\controller\members\MemberRestrictionController@listDirectory', 'api_privacy_restrictions_list');
$router->map('POST', '/api/privacy/restrictions/update', 'App\controller\members\MemberRestrictionController@upsert', 'api_privacy_restrictions_upsert');
$router->map('POST', '/api/privacy/restrictions/remove', 'App\controller\members\MemberRestrictionController@remove', 'api_privacy_restrictions_remove');
