<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$user = [
    'id' => '117540OLAWALE',
    'email' => 'woguns@ymail.com',
    'role' => 'member',
    'firstName' => 'Olawale',
    'lastName' => 'Olaogun',
    'famCode' => 'MODERNMAN'
];

$jwt = Src\JwtHandler::jwtEncodeData($user);
$tokenName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'auth_token';

setcookie(
    $tokenName,
    $jwt,
    time() + 3600,
    '/',
    '',
    false,
    false
);

header('Location: /profilePage');
exit;
