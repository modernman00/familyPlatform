<?php

namespace App\router\register;

$router->map('GET', '/register', 'App\controller\register\Register@index', 'Register');

$router->map('GET', '/createFamilyCode', 'App\controller\register\Register@createFamilyCode', 'createFamilyCode');

$router->map('POST', '/createFamilyCode', 'App\controller\register\Register@postFamilyCode', 'postFamilyCode');

// $router->map('POST', '/familycode', 'App\controller\register\Register@familyCode', 'family_code');

$router->map('GET', '/register/nextStep', 'App\controller\register\Register@nextStep', 'Register next step');

$router->map('POST', '/register', 'App\controller\register\Register@processForm', 'submit Register');



$router->map('POST', '/register/contactNewMember', 'App\controller\General@sendEmailToMember', 'contact other members');
