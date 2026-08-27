<?php
declare(strict_types=1);

$router->map('POST', '/api/engagement/react', 'App\controller\members\EngagementController@react', 'engagement_react');
$router->map('POST', '/api/engagement/vote', 'App\controller\members\EngagementController@vote', 'engagement_vote');
$router->map('POST', '/api/poll/vote', 'App\controller\members\EngagementController@vote', 'poll_vote');
$router->map('GET', '/api/engagement/memories', 'App\controller\members\EngagementController@fetchMemories', 'engagement_memories');
