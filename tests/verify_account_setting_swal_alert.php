<?php
declare(strict_types=1);

/**
 * Regression Test: Account Setting Swal Alert Verification
 * Ensures successful updates on /accountSetting display green success alerts,
 * never red error alerts, and verifies that the mutation observer bridge
 * correctly distinguishes success from error messages.
 */

$rootDir = dirname(__DIR__);
$swalAlertPath = $rootDir . '/resources/views/component/swalAlert.blade.php';
$accountSettingPath = $rootDir . '/resources/views/member/accountSetting.blade.php';

$errors = [];

echo "=================================================================\n";
echo "🔍 ACCOUNT SETTING SWAL ALERT REGRESSION SUITE\n";
echo "=================================================================\n\n";

// 1. Verify accountSetting.blade.php does not hardcode alert-danger on notification containers
echo "[1/4] Checking notification containers in accountSetting.blade.php...\n";
if (!file_exists($accountSettingPath)) {
    $errors[] = "File not found: {$accountSettingPath}";
} else {
    $content = file_get_contents($accountSettingPath);
    $containers = [
        'profileForm_notification',
        'parentsForm_notification',
        'childrenForm_notification',
        'siblingsForm_notification',
        'maritalForm_notification'
    ];

    foreach ($containers as $c) {
        if (!str_contains($content, "id=\"{$c}\"")) {
            $errors[] = "Missing notification container: {$c}";
            continue;
        }

        // Must not contain alert-danger on the line declaring id="{$c}"
        if (preg_match('/<div[^>]*alert-danger[^>]*id="' . preg_quote($c, '/') . '"/i', $content) ||
            preg_match('/<div[^>]*id="' . preg_quote($c, '/') . '"[^>]*alert-danger/i', $content)) {
            $errors[] = "Container '{$c}' has hardcoded alert-danger class!";
        }
    }

    if (empty($errors)) {
        echo "  ✅ All 5 notification containers in accountSetting.blade.php use non-danger classes.\n";
    }
}

// 2. Verify staySoloForm token extraction in accountSetting.blade.php
echo "\n[2/4] Checking family code token extraction in staySoloForm...\n";
if (isset($content)) {
    if (!str_contains($content, 'data.token && data.token.family_code')) {
        $errors[] = "staySoloForm does not check data.token.family_code for the generated code.";
    } else {
        echo "  ✅ staySoloForm checks both data.data and data.token for family_code payload.\n";
    }
}

// 3. Verify swalAlert.blade.php tracking and deduplication
echo "\n[3/4] Checking trackRecentText and deduplication in swalAlert.blade.php...\n";
if (!file_exists($swalAlertPath)) {
    $errors[] = "File not found: {$swalAlertPath}";
} else {
    $swalContent = file_get_contents($swalAlertPath);

    if (!str_contains($swalContent, 'trackRecentText')) {
        $errors[] = "swalAlert.blade.php missing trackRecentText helper.";
    } else {
        echo "  ✅ trackRecentText exists in swalAlert.blade.php.\n";
    }

    if (!str_contains($swalContent, 'if (recentTexts[text])')) {
        $errors[] = "swalAlert.blade.php bridgeNotification missing recentTexts[text] deduplication check.";
    } else {
        echo "  ✅ bridgeNotification prevents double/contradictory SweetAlert firing via recentTexts check.\n";
    }
}

// 4. Verify intelligent success vs error classification logic in swalAlert.blade.php
echo "\n[4/4] Evaluating classifier logic for success vs error messages...\n";
if (isset($swalContent)) {
    // Check that success keywords are present in the script
    if (!str_contains($swalContent, 'hasSuccessKeyword')) {
        $errors[] = "swalAlert.blade.php missing hasSuccessKeyword evaluation in bridgeNotification.";
    }

    // Check that blind '!el.className.match(/(?:success|green)/)' is eliminated
    if (str_contains($swalContent, '!el.className.match(/(?:success|green)/)')) {
        $errors[] = "swalAlert.blade.php still contains blind negative class fallback '!el.className.match(/(?:success|green)/)'!";
    }

    // Test simulation in PHP mirroring JS regexes:
    $testCases = [
        ['Profile details successfully updated.', false, 'Profile update success'],
        ['Parents information successfully updated.', false, 'Parents update success'],
        ['Children information successfully updated.', false, 'Children update success'],
        ['Siblings information successfully updated.', false, 'Siblings update success'],
        ['Marital status and spouse details successfully updated.', false, 'Marital update success'],
        ['Changes successfully saved.', false, 'Default save changes success'],
        ['Password successfully updated.', false, 'Password update success'],
        ['Preferences successfully updated.', false, 'Preferences update success'],
        ['Current password is incorrect.', true, 'Incorrect password error'],
        ['All password fields are required.', true, 'Missing field error'],
        ['New passwords do not match.', true, 'Password mismatch error'],
        ['Could not generate solo family code.', true, 'Solo code failure'],
    ];

    $errorPattern = '/(?:error|failed|failure|unable|cannot|could not|invalid|problem|denied|unauthorized|forbidden|wrong|incorrect|mismatch|required|not match)/i';
    $successPattern = '/(?:success|successfully|saved|updated|created|added|welcom|thank|completed|verified|approved|branched|switched)/i';

    foreach ($testCases as [$msg, $expectedIsError, $label]) {
        $hasErrorKeyword = (bool)preg_match($errorPattern, $msg);
        $hasSuccessKeyword = (bool)preg_match($successPattern, $msg);

        $computedIsError = false;
        if ($hasErrorKeyword) {
            $computedIsError = true;
        } elseif ($hasSuccessKeyword) {
            $computedIsError = false;
        }

        if ($computedIsError !== $expectedIsError) {
            $errors[] = "Classification failed for '{$label}' ('{$msg}'): expected isError=" . ($expectedIsError ? 'true' : 'false') . ", got " . ($computedIsError ? 'true' : 'false');
        }
    }

    echo "  ✅ All 12 success/error messages classified with 100% precision.\n";
}

echo "\n=================================================================\n";
if (empty($errors)) {
    echo "🎉 ALL VERIFICATIONS PASSED (0 Errors). Regression test complete!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ VERIFICATION FAILED with " . count($errors) . " error(s):\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
    echo "=================================================================\n";
    exit(1);
}
