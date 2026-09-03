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
}
include_once __DIR__ . "/register.php";
include_once __DIR__ . "/post.php";
include_once __DIR__ . "/comment.php";
include_once __DIR__ . "/login.php";
include_once __DIR__ . "/realtime.php";
include_once __DIR__ . "/admin.php";
include_once __DIR__ . "/profile_page.php";
include_once __DIR__ . "/allMembersRoute.php";
include_once __DIR__ . "/chat.php";
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
include_once __DIR__ . "/cron.php";
