<?php

declare(strict_types=1);

namespace App\controller;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Src\CorsHandler;
use Src\LoggedOut;

class LogoutController extends BaseController
{
    public function signout(array $redirect): void
    {
        // printArr($redirect);
        $redirect = $redirect['redirect'] ?? '/'; // Default to 'home' if not provided
        try {
            CorsHandler::setHeaders();

            // Setup logger
            $logger = new Logger('logout');
            $logger->pushHandler(new StreamHandler(
                __DIR__ . $_ENV['LOGGER_PATH'],
                Level::Debug
            ));

            echo "You have been logged out successfully. Redirecting to $redirect";
            $logoutService = new LoggedOut($logger);
            echo "You have been logged out successfully. Redirecting to $redirect";
            $logoutService->logout($redirect);
        } catch (\Throwable $e) {
            // Optionally log error
            exit;
        }
    }
}
