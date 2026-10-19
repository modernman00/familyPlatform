<?php
declare(strict_types=1);

// Family Migration and Relative Discovery Nudge Routes
$router->map('POST', '/api/family-migration/nudge', 'App\controller\members\FamilyMigrationController@nudge', 'family_migration_nudge');
$router->map('POST', '/api/family-migration/request', 'App\controller\members\FamilyMigrationController@request', 'family_migration_request');
$router->map('POST', '/api/family-migration/accept-nudge', 'App\controller\members\FamilyMigrationController@acceptNudge', 'family_migration_accept_nudge');
$router->map('POST', '/api/family-migration/approve-request', 'App\controller\members\FamilyMigrationController@approveRequest', 'family_migration_approve_request');
$router->map('POST', '/api/family-migration/decline', 'App\controller\members\FamilyMigrationController@decline', 'family_migration_decline');
