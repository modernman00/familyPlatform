<?php
declare(strict_types=1);

/**
 * E2E Testing Routes
 * NOTE: This file should ONLY be included in non-production environments.
 */

if (!isset($router)) {
    return;
}

// Ensure the controller is whitelisted to bypass manager auth if needed
global $publicPaths, $publicControllers;
if (isset($publicControllers) && is_array($publicControllers)) {
    $publicControllers[] = 'E2ETestController';
}
if (isset($publicPaths) && is_array($publicPaths)) {
    $publicPaths[] = '/api/test/get-valid-family-code';
    $publicPaths[] = '/api/test/get-valid-family-code-with-inviter';
    $publicPaths[] = '/api/test/get-pending-approval-requests';
}

$router->map('GET', '/api/test/get-valid-family-code', 'App\controller\test\E2ETestController@getValidFamilyCode', 'test-valid-family-code');
$router->map('GET', '/api/test/get-valid-family-code-with-inviter', 'App\controller\test\E2ETestController@getValidFamilyCodeWithInviter', 'test-valid-family-code-inviter');
$router->map('GET', '/api/test/get-pending-approval-requests', 'App\controller\test\E2ETestController@getPendingApprovalRequests', 'test-pending-approvals');
$router->map('GET', '/api/test/get-approval-request/[i:id]', 'App\controller\test\E2ETestController@getApprovalRequest', 'test-get-approval-request');
