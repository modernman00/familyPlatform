<?php

use AltoRouter;

/**
 * Family Code Approval API Routes
 * Handles verification and approval workflow for existing family code registrations
 */

$router = new AltoRouter();

// Check if family code exists
$router->map('POST', '/api/family-code/check', 'App\controller\auth\FamilyCodeApprovalController@checkFamilyCode', 'FAMILY_CODE_CHECK');

// Verify inviter details
$router->map('POST', '/api/family-code/verify-inviter', 'App\controller\auth\FamilyCodeApprovalController@verifyInviter', 'FAMILY_CODE_VERIFY_INVITER');

// Complete registration with approval request
$router->map('POST', '/api/family-code/complete-registration', 'App\controller\auth\FamilyCodeApprovalController@completeRegistration', 'FAMILY_CODE_COMPLETE_REGISTRATION');

// Approve a family code request
$router->map('POST', '/api/family-code/approve/:requestId', 'App\controller\auth\FamilyCodeApprovalController@approveRequest', 'FAMILY_CODE_APPROVE');

// Deny a family code request
$router->map('POST', '/api/family-code/deny/:requestId', 'App\controller\auth\FamilyCodeApprovalController@denyRequest', 'FAMILY_CODE_DENY');

return $router;
