<?php

declare(strict_types=1);

use App\router\RouteDispatch as dispatcher;

require __DIR__ . "/app/config/init.php";

// Zero-trust: direct /admin access is never served; only ADMIN_SECRET_PATH
\Src\Admin\AdminPathGate::blockDirectAdmin();

require __DIR__ . "/app/router/router.php";

$getDispatcher = new dispatcher;
$getDispatcher->dispatch($router);

