<?php
declare(strict_types=1);

$router->map('POST', '/api/engagement/react', 'App\controller\members\EngagementController@react', 'engagement_react');
$router->map('POST', '/api/engagement/vote', 'App\controller\members\EngagementController@vote', 'engagement_vote');
$router->map('POST', '/api/poll/vote', 'App\controller\members\EngagementController@vote', 'poll_vote');
$router->map('GET', '/api/engagement/memories', 'App\controller\members\EngagementController@fetchMemories', 'engagement_memories');
$router->map('GET', '/api/memories/today', 'App\controller\members\MemoryMilestoneController@getMemoriesAndMilestones', 'memories_today');
$router->map('POST', '/api/memories/share', 'App\controller\members\MemoryMilestoneController@shareMemory', 'memories_share');
$router->map('GET', '/api/onboarding/state', 'App\controller\members\OnboardingController@getOnboardingState', 'onboarding_state');
$router->map('POST', '/api/onboarding/step', 'App\controller\members\OnboardingController@completeStep', 'onboarding_step');
$router->map('POST', '/api/telemetry/event', 'App\controller\members\TelemetryController@recordEvent', 'telemetry_event');
$router->map('GET', '/api/telemetry/stats', 'App\controller\members\TelemetryController@getStats', 'telemetry_stats');
$router->map('GET', '/api/recommendation/get-link', 'App\controller\members\RecommendationController@getShareLink', 'recommendation_get_link');

