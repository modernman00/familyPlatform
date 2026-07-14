<?php
declare(strict_types=1);

$router->map('POST', '/api/family/approve', 'App\controller\members\ApprovalController@approve', 'family_approve');
$router->map('POST', '/api/family/deny', 'App\controller\members\ApprovalController@deny', 'family_deny');
