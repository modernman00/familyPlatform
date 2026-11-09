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
$router->map('GET', '/api/discovery/journey', 'App\controller\members\OnboardingController@getDiscoveryJourney', 'discovery_journey');
$router->map('POST', '/api/discovery/fast-forward', 'App\controller\members\OnboardingController@fastForwardFeature', 'discovery_fast_forward');
$router->map('POST', '/api/telemetry/event', 'App\controller\members\TelemetryController@recordEvent', 'telemetry_event');
$router->map('GET', '/api/telemetry/stats', 'App\controller\members\TelemetryController@getStats', 'telemetry_stats');
$router->map('GET', '/api/recommendation/get-link', 'App\controller\members\RecommendationController@getShareLink', 'recommendation_get_link');

// Sunday Family Trivia & Guess Who Engine
$router->map('GET', '/member/trivia', 'App\controller\members\FamilyTriviaController@redirectToTriviaPost', 'member_trivia_redirect');
$router->map('GET', '/api/trivia/current', 'App\controller\members\FamilyTriviaController@getCurrentTrivia', 'trivia_current');
$router->map('POST', '/api/trivia/auto-generate', 'App\controller\members\FamilyTriviaController@autoGenerate', 'trivia_auto_generate');

// Digital Family Time Capsule Vault
$router->map('GET', '/api/capsules', 'App\controller\members\TimeCapsuleController@listCapsules', 'capsules_list');
$router->map('POST', '/api/capsules/create', 'App\controller\members\TimeCapsuleController@createCapsule', 'capsules_create');

// Two Truths & a Lie (Heritage Edition) Game Ritual
$router->map('GET', '/api/games/two-truths/active', 'App\controller\members\TwoTruthsGameController@getActive', 'games_two_truths_active');
$router->map('POST', '/api/games/two-truths/vote', 'App\controller\members\TwoTruthsGameController@vote', 'games_two_truths_vote');
$router->map('POST', '/api/games/two-truths/create', 'App\controller\members\TwoTruthsGameController@create', 'games_two_truths_create');
$router->map('POST', '/api/games/two-truths/reveal', 'App\controller\members\TwoTruthsGameController@reveal', 'games_two_truths_reveal');

// RFC-0019: Festivity & Celebration Greeting Cards Studio (ATG)
$router->map('GET', '/creative-studio', 'App\controller\members\FestiveCardController@hub', 'member_creative_studio');
$router->map('GET', '/member/festive-cards', 'App\controller\members\FestiveCardController@index', 'member_festive_cards');
$router->map('POST', '/api/festive-cards/create', 'App\controller\members\FestiveCardController@create', 'api_festive_cards_create');
$router->map('POST', '/api/festive-cards/post-to-feed', 'App\controller\members\FestiveCardController@postToFeed', 'api_festive_cards_post_to_feed');
$router->map('GET', '/greetings/f/[:token]', 'App\controller\members\FestiveCardController@publicView', 'festive_card_public_view');
$router->map('GET', '/greetings/i/[:token]', 'App\controller\members\FestiveCardController@renderCardImage', 'festive_card_render_image');



