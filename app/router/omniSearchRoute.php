<?php

declare(strict_types=1);

/** @var \AltoRouter $router */

// 🔍 Omni-Search & Universal Navigation Routes
$router->map('GET', '/api/omni-search', 'App\controller\members\OmniSearchController@search', 'api_omni_search');
$router->map('GET', '/api/omni-search/catalog', 'App\controller\members\OmniSearchController@catalog', 'api_omni_search_catalog');
