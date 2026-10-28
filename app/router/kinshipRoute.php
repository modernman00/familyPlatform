<?php
declare(strict_types=1);

// Kinship Suggestion Routes
$router->map('GET', '/api/kinship/suggestions', 'App\controller\members\KinshipController@getSuggestions', 'kinship_suggestions_api');
$router->map('POST', '/api/kinship/dismiss', 'App\controller\members\KinshipController@dismiss', 'kinship_dismiss_api');
$router->map('GET', '/api/kinship/settings', 'App\controller\members\KinshipController@getSettings', 'kinship_settings_get_api');
$router->map('POST', '/api/kinship/settings', 'App\controller\members\KinshipController@updateSettings', 'kinship_settings_post_api');

// Kinship Auto-Matching & Position Claiming Routes
$router->map('GET', '/api/kinship/pending-matches', 'App\controller\members\KinshipController@getPendingMatches', 'kinship_pending_matches_api');
$router->map('POST', '/api/kinship/claim-match', 'App\controller\members\KinshipController@claimMatch', 'kinship_claim_match_api');
$router->map('POST', '/api/kinship/dismiss-match', 'App\controller\members\KinshipController@dismissMatch', 'kinship_dismiss_match_api');

// Analytics Beacon Route
$router->map('POST', '/api/analytics/track', 'App\controller\members\AnalyticsController@track', 'analytics_track_api');

