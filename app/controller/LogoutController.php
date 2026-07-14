<?php
declare(strict_types=1);

namespace App\controller;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Src\CorsHandler;
use Src\LoggedOut;

final class LogoutController extends BaseController
{
    public function signout(array $redirect = []): void
    {
        // printArr($redirect);
        $redirect = $redirect['redirect'] ?? '/login'; // Default to '/login' if not provided

        // PWA-compatible: Allow SW caching but force revalidation
        header("Cache-Control: private, must-revalidate, max-age=0");
        header("Vary: Cookie");
        header("Expires: 0");

        try {
            // **CRITICAL: Clear ALL authentication cookies BEFORE logout service**
            // This is the key fix for the 30-day cookie issue
            $this->clearAuthenticationCookies();

            // Setup logger
            $logger = new Logger('logout');
            $logger->pushHandler(new StreamHandler(
                __DIR__ . $_ENV['LOGGER_PATH'],
                Level::Debug
            ));

            // Clear session and cookies
            $logoutService = new LoggedOut($logger);
            $logoutService->logout($redirect);

            // The LoggedOut service already handles the redirect
            // But if we reach here, something went wrong, so redirect anyway
        } catch (\Throwable $e) {
            // Log the error
            error_log("Logout error: " . $e->getMessage());

            // Fallback: still try to redirect even if logout service fails
            header("Location: /login");
            exit();
        }
    }

    /**
     * Clear all authentication-related cookies.
     * This is critical for proper logout when cookies have long expiration times.
     */
    private function clearAuthenticationCookies(): void
    {
        $cookiesToClear = [
            'waleToken',                                    // Your JWT token
            $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'auth_token',   // Login token from env
            $_ENV['COOKIE_NAME_GENERAL'] ?? 'auth_forgot', // General auth cookie
            'tokenJWT',                                     // JWT token
            'XSRF-TOKEN',                                   // CSRF token
            session_name(),                                 // PHP session cookie
        ];

        // Get cookie parameters for proper clearing
        $params = session_get_cookie_params();
        $domain = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_HOST) ?: '';

        foreach ($cookiesToClear as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                // Clear cookie by setting expiration in the past
                setcookie(
                    $cookieName,
                    '',
                    time() - 3600, // 1 hour in the past
                    $params['path'] ?: '/',
                    $domain,
                    $params['secure'] ?? false,
                    $params['httponly'] ?? true
                );

                // Also unset from $_COOKIE superglobal
                unset($_COOKIE[$cookieName]);
            }
        }
    }
}
