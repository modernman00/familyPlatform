<?php

$router->map('GET', '/register', 'App\controller\register\Register@index', 'Register');

$router->map('POST', '/register', 'App\controller\register\Register@processForm', 'submit Register');
