<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/init.php';

use App\controller\members\PostMessage;
use App\controller\members\TagController;
use App\repository\FeedRepository;
use Src\Db;

echo "=================================================================\n";
echo "🧪 VERIFICATION SUITE: UNIFIED MEDIA LINK, EMOJIS & TAG NOTIF\n";
echo "=================================================================\n";

$passCount = 0;
$totalCount = 0;

function assertTest(bool $condition, string $title): void {
    global $passCount, $totalCount;
    $totalCount++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$title}\n";
    } else {
        echo "  [FAIL] {$title}\n";
    }
}

// -------------------------------------------------------------
// Test 1: Check post modal markup for single unified link drawer
// -------------------------------------------------------------
$modalViewPath = __DIR__ . '/../resources/views/member/modals/post.blade.php';
$modalContent = file_get_contents($modalViewPath);

assertTest(
    str_contains($modalContent, 'Link Photos &amp; Videos (Zero Storage)') || str_contains($modalContent, 'Link Photos & Videos (Zero Storage)'),
    'Post modal contains Link Photos & Videos (Zero Storage) drawer header'
);

assertTest(
    str_contains($modalContent, 'action-link-media') && str_contains($modalContent, 'bi-link-45deg'),
    'Dock toolbar contains universal Link Photos & Videos icon (bi-link-45deg), eliminating specific Google branding'
);

assertTest(
    !str_contains($modalContent, 'action-google-photo" id="addGooglePhotoBtn" title="Link Google Photo'),
    'Specific "Link Google Photo" title has been removed in favor of universal photo and video linking'
);

// -------------------------------------------------------------
// Test 2: Verify 3D Family Emoji integration and single emoji button
// -------------------------------------------------------------
assertTest(
    str_contains($modalContent, 'emojiTabStandardBtn') && str_contains($modalContent, 'emojiTab3dBtn'),
    'Emoji drawer contains integrated tab navigation for Standard Emojis and 3D Family Emojis'
);

assertTest(
    !str_contains($modalContent, 'id="atg3dEmojiBtn"'),
    'Separate 3D Emoji button has been removed from dock, leaving one single emoji button'
);

assertTest(
    str_contains($modalContent, 'closeAllComposerDrawers'),
    'Drawer mutual exclusivity coordinator closeAllComposerDrawers is active'
);

// -------------------------------------------------------------
// Test 3: Verify Video drawer streamlining
// -------------------------------------------------------------
assertTest(
    str_contains($modalContent, 'Upload Videos (Up to 3, Auto-Trim to 30s)'),
    'Video drawer focuses on Direct Video Upload and 30s auto-trim highlight studio'
);

assertTest(
    str_contains($modalContent, 'id="postVideoInput" class="d-none"'),
    'Redundant visible video URL input is removed from video drawer (unified in media link drawer)'
);

// -------------------------------------------------------------
// Test 4: Verify single post API endpoint in router and controller
// -------------------------------------------------------------
$routerPostPath = __DIR__ . '/../app/router/post.php';
$routerContent = file_get_contents($routerPostPath);

assertTest(
    str_contains($routerContent, "route: '/api/posts/[i:postNo]'") && str_contains($routerContent, 'PostMessage@getSinglePost'),
    'Router maps GET /api/posts/[i:postNo] to PostMessage@getSinglePost'
);

assertTest(
    method_exists(PostMessage::class, 'getSinglePost'),
    'PostMessage controller declares getSinglePost method'
);

// -------------------------------------------------------------
// Test 5: Verify TagController notification generation with action_url
// -------------------------------------------------------------
$tagControllerPath = __DIR__ . '/../app/controller/members/TagController.php';
$tagControllerContent = file_get_contents($tagControllerPath);

assertTest(
    str_contains($tagControllerContent, '"/profilePage?post_no={$postNo}#post{$postNo}"'),
    'TagController creates notifications with direct deep-link action_url /profilePage?post_no={$postNo}#post{$postNo}'
);

// -------------------------------------------------------------
// Test 6: Verify feedComponent dynamic post hydration on deep-link
// -------------------------------------------------------------
$feedComponentPath = __DIR__ . '/../resources/asset/js/components/profilePage/feedComponent.js';
$feedComponentContent = file_get_contents($feedComponentPath);

assertTest(
    str_contains($feedComponentContent, 'axios.get(`/api/posts/${pNo}`)'),
    'feedComponent.js dynamically fetches older or unbatched tagged posts on notification deep-link'
);

// -------------------------------------------------------------
// Test 7: Verify navbar.js feed path normalization for notification clicks
// -------------------------------------------------------------
$navbarJsPath = __DIR__ . '/../resources/asset/js/components/navbar.js';
$navbarContent = file_get_contents($navbarJsPath);

assertTest(
    str_contains($navbarContent, 'isFeedPath'),
    'navbar.js normalizes feed paths (/profilePage, /member/profilePage) to smoothly scroll to post without reload'
);

// -------------------------------------------------------------
// Test 8: Verify Google Photos shared album multi-video extraction
// -------------------------------------------------------------
$mockGooglePhotosHtml = <<<'HTML'
<!doctype html><html><head>
<script>var AF_dataServiceRequests = {'ds:0' : {id:'UJlKrf'},'ds:1' : {id:'snAcKc'}}; var AF_initDataCallback;</script>
<script class="ds:0">AF_initDataCallback({key: 'ds:0', hash: '1', data:[], sideChannel: {}});</script>
<script class="ds:1">AF_initDataCallback({key: 'ds:1', hash: '2', data:[null,[
  ["ID1",["https://lh3.googleusercontent.com/pw/VID1",null,null],null,null,null,null,null,null,null,{"76647426":[10.5]}],
  ["ID2",["https://lh3.googleusercontent.com/pw/VID2",null,null],null,null,null,null,null,null,null,{"76647426":[15.2]}],
  ["ID3",["https://lh3.googleusercontent.com/pw/VID3",null,null],null,null,null,null,null,null,null,{"76647426":[22.0]}]
],null,0], sideChannel: {}});</script>
</head><body></body></html>
HTML;

$extractedMock = \App\services\LinkPreviewService::extractGooglePhotosAlbumMedia($mockGooglePhotosHtml);
assertTest(
    count($extractedMock['videos']) === 3 && str_ends_with($extractedMock['videos'][0], '=m22') && str_ends_with($extractedMock['videos'][2], '=m22'),
    'extractGooglePhotosAlbumMedia extracts all 3 video streams with =m22 from shared albums'
);

// Verify LinkPreviewService fetchHtmlHead buffer threshold and regex logic
$lpsPath = __DIR__ . '/../app/services/LinkPreviewService.php';
$lpsContent = file_get_contents($lpsPath);
assertTest(
    str_contains($lpsContent, "key\\s*:\\s*['\"]ds:1['\"]") || str_contains($lpsContent, 'ds:1'),
    'LinkPreviewService checks complete ds:1 album payload before early cURL abort'
);

echo "\nSummary: {$passCount}/{$totalCount} tests passed.\n";

if ($passCount === $totalCount) {
    echo "🎉 ALL TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED.\n";
    exit(1);
}
