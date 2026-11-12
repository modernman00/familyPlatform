<?php
declare(strict_types =1);

$router = new AltoRouter;

// SEC-4 — schema-mutating helper routes (/createTable, /alterTable, …) and the
// unauthenticated cache-reset route must never be routable in production. Gate
// them behind a non-prod APP_ENV so a stray request can't reach them at all.
$isNonProd = in_array((string) ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: ''), ['local', 'development', 'testing'], true);

include_once __DIR__ ."/home.php";
if ($isNonProd) {
    include_once __DIR__ ."/dbTable.php";
    include_once __DIR__ ."/test-e2e.php";
}
include_once __DIR__ . "/register.php";
include_once __DIR__ . "/post.php";
include_once __DIR__ . "/comment.php";
include_once __DIR__ . "/login.php";
include_once __DIR__ . "/realtime.php";
include_once __DIR__ . "/admin.php";
include_once __DIR__ . "/profile_page.php";
include_once __DIR__ . "/allMembersRoute.php";
include_once __DIR__ . "/Event.php";
include_once __DIR__ . "/notification.php";
include_once __DIR__ . "/friendRequest.php";
include_once __DIR__ . "/pushNotification.php";
include_once __DIR__ . "/organogram.php";
include_once __DIR__ . "/setting.php";
include_once __DIR__ . "/images.php";
include_once __DIR__ . "/approvalRoute.php";
include_once __DIR__ . "/engagementRoute.php";
include_once __DIR__ . "/blog.php";
include_once __DIR__ . "/reelsRoute.php";
include_once __DIR__ . "/kinshipRoute.php";
include_once __DIR__ . "/invite.php";
include_once __DIR__ . "/journal.php";
include_once __DIR__ . "/announcement.php";
include_once __DIR__ . "/familyMigrationRoute.php";
include_once __DIR__ . "/childAccountRoute.php";
include_once __DIR__ . "/omniSearchRoute.php";
include_once __DIR__ . "/weeklyReelRoute.php";
include_once __DIR__ . "/emojiRoute.php";
include_once __DIR__ . "/atgGifRoute.php";
include_once __DIR__ . "/kinWhisperRoute.php";

// Internationalization & Diaspora Language Switching
$router->map('GET|POST|HEAD', '/locale/[a:locale]', 'App\controller\LocaleController@switch', 'LOCALE_SWITCH');
$router->map('POST', '/api/translate', 'App\controller\TranslateController@translate', 'API_TRANSLATE');
// SEC-4 — /checking was an unauthenticated `while (true)` SSE loop that pins an
// FPM worker forever; superseded by Pusher. Route removed.

// SEC-4 — existence check for the kid/sibling invite flow. Now session-gated and
// scoped to a single ?email= lookup (was: unauthenticated dump of every member
// email address).
$router->map('GET', '/getEmails', 'App\controller\Index@getEmails', 'GET_EMAILS');

if ($isNonProd) {
    // Cache-reset — dev only. Resets OPcache/APCu; no auth, so non-prod only.
    // (AltoRouter treats a literal '?' in the path, so the old '/clearcache/?'
    // pattern never matched a real URL.)
    $router->map('GET|POST', '/clearcache', 'App\controller\ClearCache@clear', 'clear-cache');
}
// ATG Sovereign Poster & Flyer Studio (Family Reunions, Anniversaries, Naming & Milestones)
// App-owned studio UI (navbar, family presets, events, feed posting) over the shared poster engine
$router->map('GET', '/poster-studio', 'App\controller\members\PosterStudioController@index', 'atg_poster_studio');
$router->map('GET', '/flyer-studio', 'App\controller\members\PosterStudioController@index', 'atg_flyer_studio');
// Built-in studio art (backdrops, florals): static, non-sensitive, served without session work
$router->map('GET', '/poster-studio-asset/[*:file]', function (string $file) {
    (new \Src\Media\UniversalPosterStudioController())->serveStudioAsset($file);
}, 'poster_studio_asset');
$router->map('POST', '/api/poster-studio/post-to-feed', 'App\controller\members\PosterStudioController@postToFeed', 'api_poster_studio_post_feed');
$router->map('GET|POST', '/api/poster-studio/render', function () {
    (new \Src\Media\UniversalPosterStudioController())->renderApiPoster();
}, 'api_atg_poster_studio_render');

include_once __DIR__ . "/cron.php";
