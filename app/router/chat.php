<?php

$router->map('GET', '/chat', 'App\controller\chat\ChatServer@startChat', 'chat');