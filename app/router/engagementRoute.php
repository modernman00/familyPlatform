<?php
declare(strict_types=1);

$router->map('POST', '/api/engagement/react', 'App\Controller\members\EngagementController@react', 'engagement_react');
$router->map('POST', '/api/engagement/vote', 'App\Controller\members\EngagementController@vote', 'engagement_vote');
$router->map('GET', '/api/engagement/memories', 'App\Controller\members\EngagementController@fetchMemories', 'engagement_memories');
