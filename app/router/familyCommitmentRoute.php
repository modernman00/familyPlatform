<?php

declare(strict_types=1);

// Family Commitments & Recurring Targets
$router->map('GET', '/commitments', 'App\controller\members\FamilyCommitmentController@index', 'family_commitments_index');
$router->map('GET', '/member/commitments', 'App\controller\members\FamilyCommitmentController@index', 'family_commitments_member_alias');

$router->map('GET', '/api/commitments', 'App\controller\members\FamilyCommitmentController@listApi', 'api_commitments_list');
$router->map('POST', '/api/commitments', 'App\controller\members\FamilyCommitmentController@store', 'api_commitments_store');
$router->map('PUT|POST', '/api/commitments/[*:id]/update', 'App\controller\members\FamilyCommitmentController@update', 'api_commitments_update_post');
$router->map('PUT', '/api/commitments/[*:id]', 'App\controller\members\FamilyCommitmentController@update', 'api_commitments_update');
$router->map('DELETE', '/api/commitments/[*:id]', 'App\controller\members\FamilyCommitmentController@destroy', 'api_commitments_destroy');
$router->map('POST', '/api/commitments/[*:id]/delete', 'App\controller\members\FamilyCommitmentController@destroy', 'api_commitments_destroy_post');
$router->map('POST', '/api/commitments/[*:id]/checkin', 'App\controller\members\FamilyCommitmentController@checkin', 'api_commitments_checkin');
$router->map('POST', '/api/commitments/[*:id]/dismiss-reminder', 'App\controller\members\FamilyCommitmentController@dismissReminder', 'api_commitments_dismiss_reminder');
