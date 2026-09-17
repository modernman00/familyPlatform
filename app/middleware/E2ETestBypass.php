<?php
declare(strict_types=1);

namespace App\middleware;

/**
 * E2E Test Bypass Middleware
 *
 * Disables rate limiting for Cypress E2E tests by defining TESTING_ENV when
 * the request carries the X-Cypress-Test header (added by Cypress test runner).
 * This prevents 429 errors during E2E test suites without affecting production.
 */
final class E2ETestBypass
{
    public static function handle(): void
    {
        $isCypressTest = isset($_SERVER['HTTP_X_CYPRESS_TEST']) ||
                         (isset($_SERVER['HTTP_USER_AGENT']) && str_contains($_SERVER['HTTP_USER_AGENT'], 'cypress'));

        if ($isCypressTest && !defined('TESTING_ENV')) {
            define('TESTING_ENV', true);
        }
    }
}
