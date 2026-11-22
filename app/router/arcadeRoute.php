<?php

declare(strict_types=1);

// Family Arcade Routes (KinZip, KinGrid, KinWord, KinPulse, Leagues, Live SSE Races)
$router->map('GET', '/arcade', 'App\controller\members\FamilyArcadeController@index', 'member_arcade');
$router->map('GET', '/api/arcade/puzzle', 'App\controller\members\FamilyArcadeController@getPuzzle', 'api_arcade_puzzle');
$router->map('POST', '/api/arcade/submit', 'App\controller\members\FamilyArcadeController@submitScore', 'api_arcade_submit');
$router->map('GET', '/api/arcade/leagues', 'App\controller\members\FamilyArcadeController@getLeagues', 'api_arcade_leagues');
$router->map('GET', '/api/arcade/daily-banner', 'App\controller\members\FamilyArcadeController@getDailyBanner', 'api_arcade_daily_banner');
$router->map('POST', '/api/arcade/race/create', 'App\controller\members\FamilyArcadeController@createRace', 'api_arcade_race_create');
$router->map('POST', '/api/arcade/race/progress', 'App\controller\members\FamilyArcadeController@updateRaceProgress', 'api_arcade_race_progress');
$router->map('POST', '/api/arcade/share-to-feed', 'App\controller\members\FamilyArcadeController@shareToFeed', 'api_arcade_share_to_feed');

// Public Open Graph "Beat My Score" Share Previews (Crawler-Safe)
$router->map('GET|HEAD', '/arcade/share/[:token]', 'App\controller\SharePreviewController@handleArcadeShareLink', 'arcade_share_view');
$router->map('GET|HEAD', '/arcade/card/[:token].jpg', 'App\controller\SharePreviewController@arcadeCardImage', 'arcade_share_card');
