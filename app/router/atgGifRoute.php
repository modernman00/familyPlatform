<?php

declare(strict_types=1);

/**
 * ATG Family Brand GIF Studio & Synthesizer Routes
 * Developed by Advanced Technology Group (ATG Pods 3 & 4)
 * Governed by: Master Engineering Charter (v3.0 Executive Edition) & TAT 2.0
 */

$router->map('GET', '/member/gif-studio', 'App\controller\members\AtgGifController@studio', 'atg_gif_studio');
$router->map('GET', '/member/gifStudio', 'App\controller\members\AtgGifController@studio', 'atg_gif_studio_alias');
$router->map('GET', '/api/atg/gif/catalog', 'App\controller\members\AtgGifController@catalog', 'api_atg_gif_catalog');
$router->map('GET|HEAD', '/api/atg/gif/view/[*:key]', 'App\controller\members\AtgGifController@viewGif', 'api_atg_gif_view');
$router->map('GET|HEAD', '/api/atg/gif/custom', 'App\controller\members\AtgGifController@viewCustom', 'api_atg_gif_custom');
$router->map('POST', '/api/atg/gif/synthesize', 'App\controller\members\AtgGifController@synthesize', 'api_atg_gif_synthesize');
