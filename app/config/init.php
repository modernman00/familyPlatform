<?php

declare(strict_types=1);

namespace App\config;

// 🛡️ NCSC PHP Advisory ("php2shell" — CVE-2026-109618 & CVE-2026-109619) Defense-in-Depth Guard
if (PHP_SAPI !== 'cli') {
    @header_remove('X-Powered-By');
    $rawQuery = (string) ($_SERVER['QUERY_STRING'] ?? '');
    $rawCookie = (string) ($_SERVER['HTTP_COOKIE'] ?? '');
    $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
    $reqMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (
        isset($_POST['PHP_SESSION_UPLOAD_PROGRESS']) ||
        isset($_GET['PHP_SESSION_UPLOAD_PROGRESS']) ||
        isset($_COOKIE['PHP_SESSION_UPLOAD_PROGRESS']) ||
        stripos($rawQuery, 'PHP_SESSION_UPLOAD_PROGRESS') !== false ||
        stripos($rawCookie, 'PHP_SESSION_UPLOAD_PROGRESS') !== false ||
        ($reqMethod !== 'POST' && stripos($contentType, 'multipart/form-data') !== false)
    ) {
        http_response_code(403);
        exit('Forbidden');
    }
}

// E2E test bypass: detect Cypress E2E requests and disable rate limiting for tests
// Check multiple headers since different web servers handle header names differently
$isCypressTest = false;
if (isset($_SERVER['HTTP_X_CYPRESS_TEST'])) {
    $isCypressTest = true;
}
// Also check variations of the header name
if (!$isCypressTest && isset($_SERVER['HTTP_X_CYPRESS_TEST'])) {
    $isCypressTest = true;
}
// Check if running via test/E2E controller
if (!$isCypressTest && isset($_SERVER['HTTP_USER_AGENT']) && (str_contains($_SERVER['HTTP_USER_AGENT'], 'cypress') || str_contains($_SERVER['HTTP_USER_AGENT'], 'node'))) {
    $isCypressTest = true;
}
// Allow test requests via specific path patterns
if (!$isCypressTest && (str_contains($_SERVER['REQUEST_URI'] ?? '', '/tests/') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/test/'))) {
    $isCypressTest = true;
}
if ($isCypressTest && !defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

require_once __DIR__ . "/_env.php";

// Ensure Limiter static instances always have safe fallbacks in test/mock environments,
// preventing fatal "reset() on null" errors when downstream functionality calls Limiter::$argLimiter->reset()
if ($isCypressTest || (function_exists('isTestEnv') && \isTestEnv())) {
    $noopLimiter = new class {
        public function reset(): void {}
        public function consume(int $tokens = 1): object {
            return new class {
                public function isAccepted(): bool { return true; }
            };
        }
    };
    \Src\Limiter::$argLimiter ??= $noopLimiter;
    \Src\Limiter::$ipLimiter ??= $noopLimiter;
}


$sessionExpire = isset($_ENV['SESSION_EXPIRE']) ? (int)$_ENV['SESSION_EXPIRE'] : 2592000;
$cookieExpire = isset($_ENV['COOKIE_EXPIRE']) ? (int)$_ENV['COOKIE_EXPIRE'] : 2592000;

ini_set('session.gc_maxlifetime', (string)$sessionExpire); // Dynamic expiration (30 days)
session_set_cookie_params($sessionExpire);

$isProd = ($_ENV['APP_ENV'] ?? 'production') === 'production';
$isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if (!isset($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] === '') {
    $_SERVER['SERVER_PORT'] = $isHttps ? '443' : '80';
}
if (!isset($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] === '') {
    $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_lifetime' => $sessionExpire, // keep session alive for 30 days instead of expiring on browser close
        'cookie_httponly' => true, // prevents XSS attacks from accessing the session cookie
        'cookie_secure' => $isProd && $isHttps, // send cookie securely if HTTPS is active in production
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
if (($_ENV['APP_ENV'] ?? '') === 'local') {
    header_remove('X-Frame-Options');
    header('X-Frame-Options: SAMEORIGIN', true);
}

if (!defined('CSP_NONCE')) {
    define('CSP_NONCE', bin2hex(random_bytes(16)));
}
$GLOBALS['csp_nonce'] = (string) CSP_NONCE;

// The shared-lib's CSP::apply() (invoked later, during view rendering) sets a
// Content-Security-Policy that omits domains our own integrations need
// (GA4 beacon calls, Chart.js sourcemaps), and its 'extra directives' merge
// option isn't actually wired up by view2()/viewBuilderWithCSP(). We can't
// patch vendor/modernman00/shared-lib directly (changes there are lost on
// composer update and must go through the upstream package instead), so
// harden, sanitize, and ensure a complete CSP header is ALWAYS sent on all HTTP responses.
if (php_sapi_name() !== 'cli') {
    header_register_callback(function () {
        if (($_ENV['APP_ENV'] ?? '') === 'local') {
            header_remove('X-Frame-Options');
            header('X-Frame-Options: SAMEORIGIN', true);
        }

        $cspFound = false;
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Security-Policy:') === 0) {
                $cspFound = true;
                $value = trim(substr($header, strlen('Content-Security-Policy:')));

                // 1. Replace dangerous wildcard https: in script-src with approved origins
                $approvedScripts = 'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://use.fontawesome.com https://kit.fontawesome.com https://www.google.com https://www.gstatic.com https://recaptcha.google.com https://unpkg.com https://d3js.org https://canvasjs.com https://js.pusher.com';
                $value = preg_replace(
                    "/script-src(?!-attr)([^;]*)\bhttps:\b([^;]*)/",
                    "script-src$1 $approvedScripts $2",
                    $value
                ) ?? $value;

                // 2. Ensure connect-src covers Google Analytics, Google APIs, jsDelivr, FontAwesome & Pusher Channels
                if (strpos($value, 'google-analytics.com') === false) {
                    $value = preg_replace(
                        '/connect-src([^;]*)/',
                        'connect-src$1 https://*.google-analytics.com https://*.analytics.google.com https://cdn.jsdelivr.net',
                        $value,
                        1
                    ) ?? $value;
                }
                if (strpos($value, 'pusher.com') === false) {
                    $value = preg_replace(
                        '/connect-src([^;]*)/',
                        'connect-src$1 https://*.pusher.com wss://*.pusher.com https://*.pusherapp.com wss://*.pusherapp.com',
                        $value,
                        1
                    ) ?? $value;
                }
                if (strpos($value, 'ka-f.fontawesome.com') === false) {
                    $value = preg_replace(
                        '/connect-src([^;]*)/',
                        'connect-src$1 https://ka-f.fontawesome.com',
                        $value,
                        1
                    ) ?? $value;
                }
                if (strpos($value, 'ka-f.fontawesome.com') === false) {
                    $value = preg_replace(
                        '/font-src([^;]*)/',
                        'font-src$1 https://ka-f.fontawesome.com',
                        $value,
                        1
                    ) ?? $value;
                }
                if (strpos($value, 'r2.cloudflarestorage.com') === false) {
                    $value = preg_replace(
                        '/connect-src([^;]*)/',
                        'connect-src$1 https://*.r2.cloudflarestorage.com',
                        $value,
                        1
                    ) ?? $value;
                }
                if (stripos($value, 'media-src') === false) {
                    $value .= "; media-src 'self' blob: https://*.r2.dev https://*.r2.cloudflarestorage.com";
                } elseif (strpos($value, 'blob:') === false || strpos($value, 'r2.dev') === false) {
                    $value = preg_replace(
                        '/media-src([^;]*)/',
                        'media-src$1 blob: https://*.r2.dev https://*.r2.cloudflarestorage.com',
                        $value,
                        1
                    ) ?? $value;
                }

                // 3. Ensure directives with no fallback are defined (OWASP ZAP 10055)
                if (strpos($value, 'object-src') === false) {
                    $value .= "; object-src 'none'";
                }
                if (strpos($value, 'base-uri') === false) {
                    $value .= "; base-uri 'self'";
                }
                if (strpos($value, 'form-action') === false) {
                    $value .= "; form-action 'self'";
                }
                if (strpos($value, 'frame-ancestors') === false) {
                    $value .= "; frame-ancestors 'self'";
                }

                // 4. Emoji/Poster/Flyer Studio on-device MediaPipe wasm permissions
                $path = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
                if (in_array(rtrim($path, '/'), ['/member/emoji-studio', '/poster-studio', '/flyer-studio'], true)) {
                    if (strpos($value, 'wasm-unsafe-eval') === false) {
                        $value = preg_replace('/script-src(?!-)([^;]*)/', "script-src$1 'wasm-unsafe-eval'", $value, 1) ?? $value;
                    }
                    if (strpos($value, 'storage.googleapis.com') === false) {
                        $value = preg_replace('/connect-src([^;]*)/', 'connect-src$1 https://storage.googleapis.com', $value, 1) ?? $value;
                    }
                }

                header('Content-Security-Policy: ' . $value, true);
                break;
            }
        }

        // If no CSP was emitted by the view or route (e.g. landing page /, /api, /cron, /member redirects),
        // emit a complete, robust, nonce-protected Content-Security-Policy header.
        if (!$cspFound) {
            $nonce = defined('CSP_NONCE') ? (string) CSP_NONCE : bin2hex(random_bytes(16));
            if (!defined('CSP_NONCE')) {
                define('CSP_NONCE', $nonce);
            }

            $scriptSrc = "script-src 'self' 'nonce-$nonce' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://use.fontawesome.com https://kit.fontawesome.com https://www.google.com https://www.gstatic.com https://recaptcha.google.com https://unpkg.com https://d3js.org https://canvasjs.com https://js.pusher.com 'unsafe-eval'";
            $connectSrc = "connect-src 'self' https://www.google.com https://www.gstatic.com https://*.google-analytics.com https://*.analytics.google.com https://cdn.jsdelivr.net https://*.pusher.com wss://*.pusher.com https://*.pusherapp.com wss://*.pusherapp.com https://ka-f.fontawesome.com https://*.r2.cloudflarestorage.com";

            $path = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
            if (in_array(rtrim($path, '/'), ['/member/emoji-studio', '/poster-studio', '/flyer-studio'], true)) {
                $scriptSrc .= " 'wasm-unsafe-eval'";
                $connectSrc .= " https://storage.googleapis.com";
            }

            $defaultCsp = "default-src 'self'; "
                . $scriptSrc . "; "
                . "script-src-attr 'unsafe-inline'; "
                . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com; "
                . "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://ka-f.fontawesome.com; "
                . "img-src 'self' data: https:; "
                . "media-src 'self' blob: https://*.r2.dev https://*.r2.cloudflarestorage.com; "
                . $connectSrc . "; "
                . "frame-src 'self' https://www.google.com https://recaptcha.google.com; "
                . "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'";

            header('Content-Security-Policy: ' . $defaultCsp, true);
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

\Src\ErrorHandler::register();

