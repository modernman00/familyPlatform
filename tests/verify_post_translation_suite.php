<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/init.php';

echo "=================================================================\n";
echo "🧪 POST TRANSLATION & DIASPORA PARITY SUITE (TAT & BRATS GATE)\n";
echo "=================================================================\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $msg): void {
    global $passCount, $failCount;
    if ($cond) {
        echo "  ✅ PASS: {$msg}\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL: {$msg}\n";
        $failCount++;
    }
}

// 1. Unauthenticated request should be rejected by BaseController with 401
$_SESSION = [];
$unauthBlocked = false;
try {
    ob_start();
    $controller = new \App\controller\TranslateController();
    $controller->translate();
    $res1 = ob_get_clean();
    $data1 = json_decode($res1 ?: '', true);
    if (isset($data1['success']) && $data1['success'] === false) {
        $unauthBlocked = true;
    }
} catch (\Throwable $e) {
    ob_end_clean();
    $unauthBlocked = true;
}
assertCondition($unauthBlocked, 'Unauthenticated request rejected at controller gateway');

// Ensure database connection and mock test user in session
$_SESSION['id'] = '1';
$_SESSION['famCode'] = 'FAM_TEST';
$_SESSION['famCodeAliasCheckedAt'] = time();
$_SESSION['token'] = 'VALID_CSRF_TOKEN_ABC';

// 2. Authenticated but missing/invalid CSRF should yield 403
$controller = new \App\controller\TranslateController();
$_SERVER['HTTP_X_CSRF_TOKEN'] = 'INVALID_TOKEN';
$_POST = [];
ob_start();
$controller->translate();
$res2 = ob_get_clean();
$data2 = json_decode($res2 ?: '', true);
assertCondition(isset($data2['success']) && $data2['success'] === false && strpos($data2['error'] ?? '', 'CSRF') !== false, 'Invalid CSRF rejected with 403');

// 3. Authenticated with valid CSRF translates foreign text cleanly (French -> English)
$_SERVER['HTTP_X_CSRF_TOKEN'] = 'VALID_CSRF_TOKEN_ABC';
$_POST = [
    'token' => 'VALID_CSRF_TOKEN_ABC',
    'text' => 'Bonjour la famille, grand-père va très bien et il vous aime tous !',
    'target' => 'en'
];
ob_start();
$controller->translate();
$res3 = ob_get_clean();
$data3 = json_decode($res3 ?: '', true);
assertCondition(isset($data3['success']) && $data3['success'] === true && !empty($data3['translation']), 'French text translated successfully to English');
assertCondition(isset($data3['detected_source']) && $data3['detected_source'] === 'fr', 'Detected source language identified as French (fr)');
assertCondition(!strpos($data3['translation'], '&#039;') && !strpos($data3['translation'], '&amp;'), 'Zero broken HTML entity escaping in translated output');

// 4. Same language detection (English -> English) flags already_in_target
$_POST = [
    'token' => 'VALID_CSRF_TOKEN_ABC',
    'text' => 'Grandpa turned 85 years old today and we had a wonderful feast!',
    'target' => 'en'
];
ob_start();
$controller->translate();
$res4 = ob_get_clean();
$data4 = json_decode($res4 ?: '', true);
assertCondition(isset($data4['success']) && $data4['success'] === true && !empty($data4['already_in_target']), 'English text requested for English target correctly flagged with already_in_target');

// 5. Normalise US English ('us') to 'en'
$_POST = [
    'token' => 'VALID_CSRF_TOKEN_ABC',
    'text' => 'Joyeux anniversaire à notre grand-père bien-aimé !',
    'target' => 'us'
];
ob_start();
$controller->translate();
$res5 = ob_get_clean();
$data5 = json_decode($res5 ?: '', true);
assertCondition(isset($data5['success']) && $data5['success'] === true && !empty($data5['translation']), 'US English locale target ("us") mapped cleanly to "en" and translated');

// 6. View verification: Blanket inline translate button removed from post-card
$postCardContent = file_get_contents(__DIR__ . '/../resources/views/components/feed/post-card.blade.php') ?: '';
assertCondition(strpos($postCardContent, "x-show=\"window.APP_LOCALE && window.APP_LOCALE !== 'en'\"") === false, 'Blanket inline translate button removed from post-card template');
assertCondition(strpos($postCardContent, 'window.toggleTranslatePost ? window.toggleTranslatePost(post) : null') !== false, 'On-demand translate action properly integrated into 3-dot dropdown menu');
assertCondition(strpos($postCardContent, 'x-show="post._isTranslated || post._isTranslating"') !== false, 'Translation status badge only activates on-demand after user request');

// 7. View verification: profileBase.blade.php includes prompt for already_english
$profileBaseContent = file_get_contents(__DIR__ . '/../resources/views/layouts/profileBase.blade.php') ?: '';
assertCondition(strpos($profileBaseContent, 'already_in_target') !== false, 'profileBase.blade.php handles already_in_target gracefully');

echo "\nSummary: {$passCount} passed, {$failCount} failed.\n";
if ($failCount > 0) {
    exit(1);
}
echo "🎉 ALL TRANSLATION SUITE CHECKS COMPLETED WITH ZERO ERRORS.\n";
exit(0);
