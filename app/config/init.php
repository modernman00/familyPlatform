<?php

declare(strict_types=1);

namespace App\config;

// Massive Data Import Optimization
ini_set('max_execution_time', '900');
ini_set('memory_limit', '1024M');
ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');
ini_set('max_input_vars', '10000');
set_time_limit(900);
ignore_user_abort(true);

require_once __DIR__ . "/_env.php";

// Load environment variables FIRST so $_ENV['APP_ENV'] is available
require_once __DIR__ . '/_env.php';

$sessionExpire = isset($_ENV['SESSION_EXPIRE']) ? (int)$_ENV['SESSION_EXPIRE'] : 7200;
$cookieExpire = isset($_ENV['COOKIE_EXPIRE']) ? (int)$_ENV['COOKIE_EXPIRE'] : 7200;

ini_set('session.gc_maxlifetime', (string)$sessionExpire); // Dynamic expiration
session_set_cookie_params($sessionExpire);

$isProd = ($_ENV['APP_ENV'] ?? 'production') === 'production';
$isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true, // prevents XSS attacks from accessing the session cookie
        'cookie_secure' => $isHttps, // send cookie securely if HTTPS is active
        'cookie_samesite' => 'Lax', // prevents CSRF attacks
        'use_strict_mode' => true, // prevents session fixation attacks
    ]);
}

// Prevent browser caching to ensure latest state is always fetched
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


if (!isset($_SESSION['token']) || !is_string($_SESSION['token']) || !preg_match('/^[0-9a-f]{64}$/D', $_SESSION['token'])) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
}

if (!isset($_COOKIE['XSRF-TOKEN']) || $_COOKIE['XSRF-TOKEN'] !== $_SESSION['token']) {
    $token = $_SESSION['token'];
    // Must match the `domain` attribute viewBuilderWithCSP() (shared-lib helpers.php) uses when
    // it re-sets this same cookie during view rendering - setcookie() calls that differ only in
    // whether `domain` is present create two *separate* same-named cookies (RFC 6265 treats a
    // host-only cookie and a Domain-scoped one as distinct), so the browser ends up holding both
    // and axios's XSRF auto-header can pick the stale one, silently failing CSRF checks.
    $csrfCookieDomain = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_HOST) ?: '';
    setcookie('XSRF-TOKEN', $token, [
        'expires' => time() + $cookieExpire,
        'path' => '/',
        'domain' => $csrfCookieDomain,
        'samesite' => 'Lax',
        'secure' => $isHttps, // send cookie securely if HTTPS is active
        'httponly' => false,
    ]);
    // Manually populate $_COOKIE so it's available for the rest of this request
    $_COOKIE['XSRF-TOKEN'] = $token;
}


\Src\SecurityHandler::applyGlobalHeaders();

// The shared-lib's CSP::apply() (invoked later, during view rendering) sets a
// Content-Security-Policy that omits domains our own integrations need
// (GA4 beacon calls, Chart.js sourcemaps), and its 'extra directives' merge
// option isn't actually wired up by view2()/viewBuilderWithCSP(). We can't
// patch vendor/modernman00/shared-lib directly (changes there are lost on
// composer update and must go through the upstream package instead), so
// widen connect-src on the header right before it's actually sent — this
// runs after CSP::apply() has already set its nonce, and we preserve it.
if (php_sapi_name() !== 'cli') {
    header_register_callback(function () {
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Security-Policy:') === 0) {
                $value = trim(substr($header, strlen('Content-Security-Policy:')));
                if (strpos($value, 'google-analytics.com') === false) {
                    $value = preg_replace(
                        '/connect-src([^;]*)/',
                        'connect-src$1 https://*.google-analytics.com https://*.analytics.google.com https://cdn.jsdelivr.net',
                        $value,
                        1
                    );
                    header('Content-Security-Policy: ' . $value, true);
                }
                break;
            }
        }
    });
}





define("BR", "<br>");
define("URL", getenv("APP_URL"));

/**
 * You should use the mb_internal_encoding() function at the top of every PHP script you write (or at the top of your global include script), and the mb_http_output() function right after it if your script is outputting to a browser. Explicitly defining the encoding of your strings in every script will save you a lot of headaches down the road.
 * https://phptherightway.com/
 */
mb_internal_encoding();
mb_http_output();
date_default_timezone_set('Europe/London');

// Load environment (from .env or server environment variable)
$env = $_ENV['APP_ENV'] ?: 'production'; // Options: development, staging, production


// Configure error handling based on environment
switch ($env) {
    case 'development':
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL); // Show everything for debugging
        break;

    case 'staging':
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE); // Hide deprecated & notices
        ini_set('log_errors', '1');
        ini_set('error_log', __DIR__ . '/../../bootstrap/log/ini.log');
        break;

    case 'production':
    default:
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE); // Hide deprecated & notices
        ini_set('log_errors', '1');
        ini_set('error_log', __DIR__ . '/../../bootstrap/log/ini.log');
        break;
}
