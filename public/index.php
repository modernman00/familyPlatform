<?php 

declare(strict_types =1);

require __DIR__ ."/../app/config/init.php";

require __DIR__ . "/../app/router/router.php";

return new App\router\RouteDispatch($router);