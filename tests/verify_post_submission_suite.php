<?php

declare(strict_types=1);

/**
 * 📝 FAMILYPLATFORM POST SUBMISSION & ATTACHMENT SUITE
 * 
 * Verifies:
 * 1. Post creation with empty attached_gif_url ("") succeeds (Bug fix verification).
 * 2. Post creation with valid GIF URL succeeds and sets post_img0.
 * 3. Adversarial SSRF / internal IP GIF URLs are neutralized and not saved.
 * 4. Post creation with tagged_user_ids array succeeds without SQL column error.
 * 5. Post creation with shared_temp_images array succeeds without SQL column error.
 * 6. Empty post rejection (422 validation guard).
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/init.php';

use App\controller\members\ProfilePage;
use App\model\Post;
use Src\Db;

$pdo = Db::connect2();

echo "\n=================================================================\n";
echo "📝  FAMILYPLATFORM POST SUBMISSION VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "✅ PASS: {$name}\n";
        $passCount++;
    } else {
        echo "❌ FAIL: {$name} - {$details}\n";
        $failCount++;
    }
}

// 1. Setup Fixtures
$famCode = 'TEST_FAM_' . bin2hex(random_bytes(4));
$authorId = 'TEST_AUTHOR_' . bin2hex(random_bytes(4));
$token = 'test-token-' . bin2hex(random_bytes(8));

$createdPostNos = [];

try {
    // Insert author into personal table
    $stmtAuthor = $pdo->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, created_at)
        VALUES (?, 'Ajibike', 'Olaogun', ?, NOW())
    ");
    $stmtAuthor->execute([$authorId, $famCode]);

    $_SESSION['id'] = $authorId;
    $_SESSION['fName'] = 'Ajibike';
    $_SESSION['lName'] = 'Olaogun';
    $_SESSION['famCode'] = $famCode;
    $_SESSION['token'] = $token;

    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

    $profilePage = (new ReflectionClass(ProfilePage::class))->newInstanceWithoutConstructor();

    // -------------------------------------------------------------
    // TEST 1: Post creation with empty attached_gif_url ("")
    // (This was the exact regression reported by user)
    // -------------------------------------------------------------
    $_POST = [
        'token' => $token,
        'post_no' => '',
        'attached_gif_url' => '',
        'postMessage' => 'Testing post submission without GIF',
        'postFamCode' => $famCode,
    ];
    $_FILES = [];

    ob_start();
    $profilePage->post();
    $output = ob_get_clean();
    $res1 = json_decode((string)$output, true);

    $postNo1 = (int)($res1['message']['post_no'] ?? $res1['data']['post_no'] ?? $res1['message'] ?? 0);
    if ($postNo1 > 0) $createdPostNos[] = $postNo1;

    assertTest(
        'Post creation with empty attached_gif_url succeeds (Bug Fixed)',
        ($res1['status'] ?? '') === 'success' && $postNo1 > 0,
        "Failed with response: " . substr((string)$output, 0, 150)
    );

    // -------------------------------------------------------------
    // TEST 2: Post creation with valid attached_gif_url
    // -------------------------------------------------------------
    $validGif = 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/giphy.gif';
    $_POST = [
        'token' => $token,
        'post_no' => '',
        'attached_gif_url' => $validGif,
        'postMessage' => 'Celebrating with family GIF!',
        'postFamCode' => $famCode,
    ];

    ob_start();
    $profilePage->post();
    $output2 = ob_get_clean();
    $res2 = json_decode((string)$output2, true);

    $postNo2 = (int)($res2['message']['post_no'] ?? $res2['data']['post_no'] ?? $res2['message'] ?? 0);
    if ($postNo2 > 0) $createdPostNos[] = $postNo2;

    $postRow2 = $postNo2 > 0 ? Post::postByNo($postNo2) : null;
    $hasSavedGif = ($postRow2['post_img0'] ?? '') === $validGif;

    assertTest(
        'Post creation with valid attached_gif_url promotes GIF to post_img0',
        ($res2['status'] ?? '') === 'success' && $hasSavedGif,
        "Expected post_img0={$validGif}, got=" . ($postRow2['post_img0'] ?? 'null')
    );

    // -------------------------------------------------------------
    // TEST 3: Adversarial SSRF / Private IP GIF URL Neutralisation
    // -------------------------------------------------------------
    $ssrfPayload = 'http://169.254.169.254/latest/meta-data/';
    $_POST = [
        'token' => $token,
        'post_no' => '',
        'attached_gif_url' => $ssrfPayload,
        'postMessage' => 'Attacking internal metadata via GIF',
        'postFamCode' => $famCode,
    ];

    ob_start();
    $profilePage->post();
    $output3 = ob_get_clean();
    $res3 = json_decode((string)$output3, true);

    $postNo3 = (int)($res3['message']['post_no'] ?? $res3['data']['post_no'] ?? $res3['message'] ?? 0);
    if ($postNo3 > 0) $createdPostNos[] = $postNo3;

    $postRow3 = $postNo3 > 0 ? Post::postByNo($postNo3) : null;
    $ssrfBlocked = empty($postRow3['post_img0']);

    assertTest(
        'SSRF Cloud Metadata GIF URL is neutralized and NOT stored',
        $ssrfBlocked,
        "Malicious SSRF URL was saved to post_img0: " . ($postRow3['post_img0'] ?? '')
    );

    // -------------------------------------------------------------
    // TEST 4: Post creation with tagged_user_ids array
    // (Verifies auxiliary keys do not trigger SQL column error)
    // -------------------------------------------------------------
    $_POST = [
        'token' => $token,
        'post_no' => '',
        'attached_gif_url' => '',
        'postMessage' => 'Testing tags with family',
        'tagged_user_ids' => ['USER_TAG_1', 'USER_TAG_2'],
        'postFamCode' => $famCode,
    ];

    ob_start();
    $profilePage->post();
    $output4 = ob_get_clean();
    $res4 = json_decode((string)$output4, true);

    $postNo4 = (int)($res4['message']['post_no'] ?? $res4['data']['post_no'] ?? $res4['message'] ?? 0);
    if ($postNo4 > 0) $createdPostNos[] = $postNo4;

    assertTest(
        'Post creation with tagged_user_ids succeeds without SQL column error',
        ($res4['status'] ?? '') === 'success' && $postNo4 > 0,
        "Failed with: " . substr((string)$output4, 0, 150)
    );

    // -------------------------------------------------------------
    // TEST 5: Post creation with shared_temp_images array
    // (Verifies PWA Share Target keys do not trigger SQL column error)
    // -------------------------------------------------------------
    $_POST = [
        'token' => $token,
        'post_no' => '',
        'attached_gif_url' => '',
        'postMessage' => 'Testing PWA share target auxiliary keys',
        'shared_temp_images' => ['temp_fake_123.jpg'],
        'postFamCode' => $famCode,
    ];

    ob_start();
    $profilePage->post();
    $output5 = ob_get_clean();
    $res5 = json_decode((string)$output5, true);

    $postNo5 = (int)($res5['message']['post_no'] ?? $res5['data']['post_no'] ?? $res5['message'] ?? 0);
    if ($postNo5 > 0) $createdPostNos[] = $postNo5;

    assertTest(
        'Post creation with shared_temp_images succeeds without SQL column error',
        ($res5['status'] ?? '') === 'success' && $postNo5 > 0,
        "Failed with: " . substr((string)$output5, 0, 150)
    );

    // -------------------------------------------------------------
    // TEST 6: Empty post rejection
    // -------------------------------------------------------------
    $_POST = [
        'token' => $token,
        'post_no' => '',
        'attached_gif_url' => '',
        'postMessage' => '   ',
        'postFamCode' => $famCode,
    ];

    ob_start();
    $profilePage->post();
    $output6 = ob_get_clean();
    $res6 = json_decode((string)$output6, true);

    assertTest(
        'Empty post is rejected with 422 validation guard',
        ($res6['status'] ?? '') === 'error' && (int)($res6['code'] ?? 0) === 422,
        "Failed to reject empty post: " . substr((string)$output6, 0, 150)
    );

    // -------------------------------------------------------------
    // TEST 7: Video post with full composer modal fields (including then_now_voice_base64)
    // -------------------------------------------------------------
    $videoUrl = '/resources/videos/posts/post_vid_test_' . bin2hex(random_bytes(4)) . '.mov#t=0,4.75';
    $_POST = [
        'token' => $token,
        'post_no' => '',
        'attached_gif_url' => '',
        'postMessage' => $videoUrl,
        'postFamCode' => $famCode,
        'poll_question' => '',
        'poll_options' => ['', ''],
        'then_now' => '',
        'reply_to_post' => '',
        'comparison_mode' => 'classic',
        'mystery_year' => '0',
        'then_now_voice_base64' => '',
        'then_label' => '',
        'now_label' => '',
    ];

    ob_start();
    $profilePage->post();
    $output7 = ob_get_clean();
    $res7 = json_decode((string)$output7, true);

    $postNo7 = (int)($res7['message']['post_no'] ?? $res7['data']['post_no'] ?? $res7['message'] ?? 0);
    if ($postNo7 > 0) $createdPostNos[] = $postNo7;

    assertTest(
        'Post creation with video URL and empty then_now_voice_base64 succeeds',
        ($res7['status'] ?? '') === 'success' && $postNo7 > 0,
        "Failed with: " . substr((string)$output7, 0, 150)
    );

} finally {
    // 🧹 Cleanup test fixtures
    if (!empty($createdPostNos)) {
        $delStmt = $pdo->prepare("DELETE FROM post WHERE post_no = ?");
        foreach ($createdPostNos as $pNo) {
            $delStmt->execute([(int)$pNo]);
        }
    }
    $pdo->prepare("DELETE FROM personal WHERE id = ?")->execute([$authorId]);
}

echo "\n=================================================================\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "=================================================================\n\n";

exit($failCount === 0 ? 0 : 1);
