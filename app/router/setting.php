<?php

$router->map('GET', '/accountSetting', 'App\controller\members\SettingController@index', 'accountSetting');


$router->map('POST', '/accountSetting', 'App\controller\members\SettingController@post', 'accountSettingPost'); 
