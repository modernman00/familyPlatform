<?php
declare(strict_types=1);

$router->map('POST', '/api/family/approve', 'App\controller\members\ApprovalController@approve', 'family_approve');
$router->map('POST', '/api/family/deny', 'App\controller\members\ApprovalController@deny', 'family_deny');

// Family Code Approval Flow
$router->map('POST', '/api/family-code/check', 'App\controller\auth\FamilyCodeApprovalController@checkFamilyCode', 'family_code_check');
$router->map('POST', '/api/family-code/verify-inviter', 'App\controller\auth\FamilyCodeApprovalController@verifyInviter', 'family_code_verify');
$router->map('POST', '/api/family-code/complete-registration', 'App\controller\auth\FamilyCodeApprovalController@completeRegistration', 'family_code_complete');
$router->map('POST', '/api/family-code/approve/:id', 'App\controller\auth\FamilyCodeApprovalController@approveRequest', 'family_code_approve');
$router->map('POST', '/api/family-code/deny/:id', 'App\controller\auth\FamilyCodeApprovalController@denyRequest', 'family_code_deny');
