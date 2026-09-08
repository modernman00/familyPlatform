<?php
declare(strict_types=1);

/**
 * E2E Testing Routes
 * NOTE: This file is only included from router.php when APP_ENV is non-prod.
 *
 * @var \AltoRouter $router
 */

$router->map('GET', '/api/test/get-valid-family-code', 'App\controller\test\E2ETestController@getValidFamilyCode', 'test-valid-family-code');
$router->map('GET', '/api/test/get-valid-family-code-with-inviter', 'App\controller\test\E2ETestController@getValidFamilyCodeWithInviter', 'test-valid-family-code-inviter');
$router->map('GET', '/api/test/get-pending-approval-requests', 'App\controller\test\E2ETestController@getPendingApprovalRequests', 'test-pending-approvals');
$router->map('GET', '/api/test/get-approval-request/[i:id]', 'App\controller\test\E2ETestController@getApprovalRequest', 'test-get-approval-request');
