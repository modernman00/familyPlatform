<?php

$router->map('GET', '/register', 'App\controller\Register@index', 'Register');

$router->map('POST', '/register', 'App\controller\Register@processForm', 'submit Register');
