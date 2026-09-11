<?php
declare(strict_types=1);

/**
 * E2E Testing Routes
 * NOTE: This file is only included from router.php when APP_ENV is non-prod,
 * where $router is the AltoRouter instance created in router.php.
 */

$router->map('GET', '/api/test/get-valid-family-code', 'App\controller\test\E2ETestController@getValidFamilyCode', 'test-valid-family-code');
$router->map('GET', '/api/test/get-valid-family-code-with-inviter', 'App\controller\test\E2ETestController@getValidFamilyCodeWithInviter', 'test-valid-family-code-inviter');
$router->map('GET', '/api/test/get-pending-approval-requests', 'App\controller\test\E2ETestController@getPendingApprovalRequests', 'test-pending-approvals');
$router->map('GET', '/api/test/get-approval-request/[i:id]', 'App\controller\test\E2ETestController@getApprovalRequest', 'test-get-approval-request');
$router->map('POST', '/tests/clear-rate-limit', 'App\controller\test\E2ETestController@clearRateLimit', 'test-clear-rate-limit');
$router->map('POST', '/tests/seed-test-user', 'App\controller\test\E2ETestController@seedTestUser', 'test-seed-user');
