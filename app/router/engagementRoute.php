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

// Sunday Family Trivia & Guess Who Engine
$router->map('GET', '/api/trivia/current', 'App\controller\members\FamilyTriviaController@getCurrentTrivia', 'trivia_current');
$router->map('POST', '/api/trivia/auto-generate', 'App\controller\members\FamilyTriviaController@autoGenerate', 'trivia_auto_generate');

// Digital Family Time Capsule Vault
$router->map('GET', '/api/capsules', 'App\controller\members\TimeCapsuleController@listCapsules', 'capsules_list');
$router->map('POST', '/api/capsules/create', 'App\controller\members\TimeCapsuleController@createCapsule', 'capsules_create');


// Family Celebrations: birthdays & anniversaries, private wishes (RFC-0008)
$router->map('GET', '/api/celebrations', 'App\controller\members\CelebrationController@index', 'celebrations_list');
$router->map('POST', '/api/celebrations/wish', 'App\controller\members\CelebrationController@wish', 'celebrations_wish');
