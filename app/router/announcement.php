<?php
declare(strict_types=1);

/** @var \AltoRouter $router */

// Member Family Announcements
$router->map('POST', '/api/announcement/store', 'App\controller\members\FamilyAnnouncementController@store', 'family_announcement_store');
$router->map('POST', '/api/announcement/delete', 'App\controller\members\FamilyAnnouncementController@delete', 'family_announcement_delete');
$router->map('GET', '/api/announcement/active', 'App\controller\members\FamilyAnnouncementController@getActive', 'family_announcement_active');
