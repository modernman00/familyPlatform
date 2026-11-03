<?php

declare(strict_types=1);

/**
 * Weekly Activity Reel Curation & Approval Routes
 * Cluster: Social Media, Lifestyle & Platform Growth Sovereign Board / Enterprise Platform TAT
 */

$router->map('GET', '/api/weekly-reel/pending', 'App\controller\members\WeeklyReelApprovalController@getPending', 'api_weekly_reel_pending');
$router->map('POST', '/api/weekly-reel/approve', 'App\controller\members\WeeklyReelApprovalController@approve', 'api_weekly_reel_approve');
$router->map('POST', '/api/weekly-reel/dismiss', 'App\controller\members\WeeklyReelApprovalController@dismiss', 'api_weekly_reel_dismiss');
$router->map('POST', '/api/weekly-reel/generate', 'App\controller\members\WeeklyReelApprovalController@generate', 'api_weekly_reel_generate');
