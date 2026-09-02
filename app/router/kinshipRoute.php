<?php
declare(strict_types=1);

// Kinship Suggestion Routes
$router->map('GET', '/api/kinship/suggestions', 'App\controller\members\KinshipController@getSuggestions', 'kinship_suggestions_api');
$router->map('POST', '/api/kinship/dismiss', 'App\controller\members\KinshipController@dismiss', 'kinship_dismiss_api');

// Analytics Beacon Route
$router->map('POST', '/api/analytics/track', 'App\controller\members\AnalyticsController@track', 'analytics_track_api');
