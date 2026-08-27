<?php

declare(strict_types=1);


use App\router\RouteDispatch as dispatcher;

require __DIR__ . "/app/config/init.php";

if (isset($_GET['token']) && $_GET['token'] === 'diagnose123') {
    require __DIR__ . '/diagnostics.php';
    exit;
}

require __DIR__ . "/app/router/router.php";

$getDispatcher = new dispatcher;
$getDispatcher->dispatch($router);
