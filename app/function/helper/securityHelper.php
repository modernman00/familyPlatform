<?php

declare(strict_types=1);

/**
 * PWA-Compatible cache control for authenticated pages.
 * 
 * This function sets HTTP headers that:
 * 1. Allow Service Worker caching for offline PWA functionality
 * 2. Force revalidation on every page load to check authentication
 * 3. Prevent browser back/forward cache (bfcache) abuse
 * 
 * The key difference from traditional no-cache:
 * - Uses "private, must-revalidate" instead of "no-store"
 * - Allows Service Worker to cache but forces server validation
 * - Compatible with PWA offline functionality
 * 
 * @param bool $isPWA Set to true to enable PWA-compatible caching
 * @return void
 */
function preventPageCaching(bool $isPWA = true): void
{
    if ($isPWA) {
        // PWA-compatible: Allow SW caching but force revalidation
        header("Cache-Control: private, must-revalidate, max-age=0");
        header("Vary: Cookie"); // Important for authenticated content
    } else {
        // Traditional: Block all caching (breaks PWA offline)
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    // Always set Expires to prevent old browser caching
    header("Expires: 0");
}

/**
 * Set cache headers for static assets (PWA-friendly).
 * 
 * Use this for CSS, JS, images that should be cached by Service Worker.
 * 
 * @param int $maxAge Cache duration in seconds (default: 1 year)
 * @return void
 */
function allowAssetCaching(int $maxAge = 31536000): void
{
    header("Cache-Control: public, max-age={$maxAge}, immutable");
}

/**
 * Redirect with PWA-compatible cache prevention.
 * 
 * @param string $path The path to redirect to
 * @param int $statusCode HTTP status code
 * @return never
 */
function redirectWithNoCache(string $path, int $statusCode = 302): never
{
    preventPageCaching(true); // PWA-compatible by default
    http_response_code($statusCode);
    header("Location: " . url($path));
    exit();
}

/**
 * Check if the current request is from a Service Worker.
 * 
 * @return bool
 */
function isServiceWorkerRequest(): bool
{
    return isset($_SERVER['HTTP_SERVICE_WORKER']) ||
        (isset($_SERVER['HTTP_USER_AGENT']) &&
            strpos($_SERVER['HTTP_USER_AGENT'], 'ServiceWorker') !== false);
}

/**
 * Generate a session validation token for client-side checks.
 * 
 * This token can be embedded in the page and checked by JavaScript
 * to detect if the session is still valid when loading from cache.
 * 
 * @return string
 */
function generateSessionToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $sessionId = session_id();
    $userId = $_SESSION['id'] ?? $_SESSION['user_id'] ?? 'guest';
    $timestamp = time();

    // Create a hash that changes when session changes
    return hash('sha256', $sessionId . $userId . $timestamp);
}

/**
 * Clear browser history and redirect (PWA-compatible).
 * 
 * @param string $path The path to redirect to
 * @return never
 */
function clearHistoryAndRedirect(string $path): never
{
    preventPageCaching(true);

    // Output JavaScript to clear history and redirect
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        // Clear the current page from history
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '{$path}');
        }
        
        // If PWA, also clear Service Worker cache for this page
        if ('serviceWorker' in navigator && 'caches' in window) {
            caches.keys().then(function(cacheNames) {
                cacheNames.forEach(function(cacheName) {
                    if (cacheName.includes('auth') || cacheName.includes('dynamic')) {
                        caches.delete(cacheName);
                    }
                });
            });
        }
        
        // Redirect
        window.location.replace('{$path}');
    </script>
</head>
<body>
    <p>Redirecting...</p>
</body>
</html>
HTML;
    exit();
}

/**
 * Get PWA cache strategy for the current page type.
 *
 * Returns the appropriate caching strategy based on page type:
 * - 'network-first' for authenticated pages (always check server first)
 * - 'cache-first' for static assets
 * - 'stale-while-revalidate' for semi-dynamic content
 *
 * @param string $pageType 'authenticated', 'static', or 'dynamic'
 * @return string
 */
function getPWACacheStrategy(string $pageType = 'authenticated'): string
{
    return match ($pageType) {
        'authenticated' => 'network-first',
        'static' => 'cache-first',
        'dynamic' => 'stale-while-revalidate',
        default => 'network-first'
    };
}

/**
 * Flush the session and release its file lock for the rest of a read-only request.
 *
 * PHP's default (files) session handler holds an exclusive lock from
 * session_start() until the request finishes. The profile page fires a burst of
 * concurrent AJAX calls on load (feed, comments, friend requests, notifications,
 * memories…) that all carry the same session cookie, so that lock serialises
 * them one-behind-another on a small FPM worker pool — and because init.php sets
 * ignore_user_abort(true), a slow endpoint keeps holding the lock even after the
 * browser has already timed the request out, stalling everything queued behind it.
 *
 * Any handler that only *reads* $_SESSION can let go of the lock the moment it
 * has copied out what it needs. $_SESSION stays readable afterwards; only further
 * writes would fail to persist — so never call this before code that assigns to
 * $_SESSION.
 *
 * @return void
 */
function releaseSessionLock(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}
