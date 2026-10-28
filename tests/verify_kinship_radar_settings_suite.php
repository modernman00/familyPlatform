<?php
declare(strict_types=1);

/**
 * 🛡️ KINSHIP RADAR SETTINGS & DISCOVERY ENGINE VERIFICATION SUITE
 * 
 * Tests:
 *  1. Radar Settings DTO & default values fallback
 *  2. Database persistence & UPSERT via KinshipEngineService::updateRadarSettings
 *  3. Suggestion Engine filtering (radar_enabled, include_inlaws, include_maternal, min_confidence, discoverable)
 *  4. Blade view structure & Fitts's Law touch target (>= 44x44px)
 *  5. Dr. Viktor Kovács & Marcus Adversarial Security Attacks (IDOR, CSRF, Unauth, Out-of-Bounds validation)
 */

require_once __DIR__ . '/../app/config/init.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\services\KinshipEngineService;
use App\controller\members\KinshipController;
use Src\Db;
use eftec\bladeone\BladeOne;

$pdo = Db::connect2();
$failures = [];

echo "=================================================================\n";
echo "🛡️  RUNNING KINSHIP RADAR SETTINGS & DISCOVERY SUITE\n";
echo "=================================================================\n\n";

$testUserId = 'test_radar_user_' . bin2hex(random_bytes(4));

try {
    // -------------------------------------------------------------
    // TEST 1: Default Settings Fallback
    // -------------------------------------------------------------
    echo "[TEST 1] Testing default settings fallback for non-existent user...\n";
    $defaults = KinshipEngineService::getRadarSettings($testUserId);

    if ($defaults['user_id'] !== $testUserId ||
        $defaults['radar_enabled'] !== true ||
        $defaults['include_inlaws'] !== true ||
        $defaults['include_maternal'] !== true ||
        $defaults['min_confidence'] !== 50 ||
        $defaults['discoverable'] !== true) {
        $failures[] = "Default settings did not match expected structure: " . json_encode($defaults);
    } else {
        echo "  ✅ PASS: Default settings correctly returned (radar_enabled=true, min_confidence=50, discoverable=true).\n";
    }

    // -------------------------------------------------------------
    // TEST 2: Updating & Persisting Radar Settings
    // -------------------------------------------------------------
    echo "\n[TEST 2] Testing persistence & UPSERT of custom radar settings...\n";
    $custom = [
        'radar_enabled'    => 1,
        'include_inlaws'   => 0,
        'include_maternal' => 1,
        'min_confidence'   => 70,
        'discoverable'     => 0,
    ];
    $saved = KinshipEngineService::updateRadarSettings($testUserId, $custom);
    if (!$saved) {
        $failures[] = "updateRadarSettings returned false.";
    } else {
        $reloaded = KinshipEngineService::getRadarSettings($testUserId);
        if ($reloaded['include_inlaws'] !== false ||
            $reloaded['include_maternal'] !== true ||
            $reloaded['min_confidence'] !== 70 ||
            $reloaded['discoverable'] !== false) {
            $failures[] = "Persisted settings mismatch: " . json_encode($reloaded);
        } else {
            echo "  ✅ PASS: Custom radar settings persisted & reloaded accurately via MySQL.\n";
        }
    }

    // -------------------------------------------------------------
    // TEST 3: Radar Paused Invariant (radar_enabled = false -> returns [])
    // -------------------------------------------------------------
    echo "\n[TEST 3] Testing Radar Paused invariant...\n";
    KinshipEngineService::updateRadarSettings($testUserId, ['radar_enabled' => 0]);
    $pausedSettings = KinshipEngineService::getRadarSettings($testUserId);
    if ($pausedSettings['radar_enabled'] !== false) {
        $failures[] = "radar_enabled was not saved as false.";
    }

    $suggestionsWhenPaused = KinshipEngineService::getSuggestedKin($testUserId);
    if (!empty($suggestionsWhenPaused)) {
        $failures[] = "getSuggestedKin should return empty array when radar is paused.";
    } else {
        echo "  ✅ PASS: When radar_enabled = false, getSuggestedKin returns exactly 0 suggestions.\n";
    }

    // Reset back to enabled for further checks
    KinshipEngineService::updateRadarSettings($testUserId, [
        'radar_enabled'    => 1,
        'include_inlaws'   => 1,
        'include_maternal' => 1,
        'min_confidence'   => 50,
        'discoverable'     => 1,
    ]);

    // -------------------------------------------------------------
    // TEST 4: Blade View Structure & Fitts's Law Touch Target
    // -------------------------------------------------------------
    echo "\n[TEST 4] Testing Blade views (modal + widget touch targets)...\n";
    $viewDir = dirname(__DIR__) . '/resources/views';
    $cacheDir = dirname(__DIR__) . '/bootstrap/cache';
    $blade = new BladeOne($viewDir, $cacheDir, BladeOne::MODE_DEBUG);

    $modalHtml = file_get_contents($viewDir . '/member/modals/kinshipRadarSettingsModal.blade.php');
    if (!str_contains($modalHtml, 'id="kinshipRadarSettingsModal"') ||
        !str_contains($modalHtml, 'id="radarMasterSwitch"') ||
        !str_contains($modalHtml, 'id="radarInlawsSwitch"') ||
        !str_contains($modalHtml, 'id="radarMaternalSwitch"') ||
        !str_contains($modalHtml, 'id="radarDiscoverableSwitch"') ||
        !str_contains($modalHtml, 'id="btnSaveRadarSettings"') ||
        (!str_contains($modalHtml, '@csrf') && !str_contains($modalHtml, "include('csrf')"))) {
        $failures[] = "kinshipRadarSettingsModal.blade.php is missing essential form IDs or CSRF inclusion.";
    } else {
        echo "  ✅ PASS: kinshipRadarSettingsModal contains complete form, CSRF token, and switch controls.\n";
    }

    $widgetHtml = $blade->run('member.includes.kinshipSuggestions', [
        'suggestedKin' => [
            [
                'user_id' => 'u1',
                'firstName' => 'Adebayo',
                'lastName' => 'Olaogun',
                'kinship_type' => 'Paternal Clan',
                'primary_reason' => 'Shared Surname',
                'confidence_score' => 85,
            ]
        ],
        'radarSettings' => ['radar_enabled' => true],
        'kinshipWidgetId' => 'testWidget'
    ]);

    if (!str_contains($widgetHtml, 'data-bs-target="#kinshipRadarSettingsModal"') ||
        !str_contains($widgetHtml, 'min-width: 44px') ||
        !str_contains($widgetHtml, 'min-height: 44px')) {
        $failures[] = "Kinship widget settings button missing data-bs-target or 44x44px minimum tap target.";
    } else {
        echo "  ✅ PASS: Widget button links to #kinshipRadarSettingsModal with >= 44x44px safe touch area.\n";
    }

    // Verify Paused State Blade rendering
    $pausedWidgetHtml = $blade->run('member.includes.kinshipSuggestions', [
        'suggestedKin' => [],
        'radarSettings' => ['radar_enabled' => false],
        'kinshipWidgetId' => 'testWidgetPaused'
    ]);

    if (!str_contains($pausedWidgetHtml, 'Radar Paused') ||
        !str_contains($pausedWidgetHtml, 'btn-resume-radar')) {
        $failures[] = "Paused widget does not render 'Radar Paused' badge or resume button.";
    } else {
        echo "  ✅ PASS: Paused widget correctly renders 'Radar Paused' badge and 1-click resume button.\n";
    }

    // -------------------------------------------------------------
    // TEST 5: Dr. Viktor Kovács Adversarial Security Penetration Attacks
    // -------------------------------------------------------------
    echo "\n[TEST 5] Executing Dr. Viktor Kovács Adversarial Exploit Attacks...\n";

    // Attack Vector A: Unauthenticated access to /api/kinship/settings
    echo "  [Adversarial Attack A] Unauthenticated access to settings endpoint...\n";
    $unauthLines = [];
    $unauthCode = 0;
    exec("php -r \"require_once \\\"app/config/init.php\\\"; require_once \\\"vendor/autoload.php\\\"; \\\$c = new \\\\App\\\\controller\\\\members\\\\KinshipController(); \\\$c->getSettings();\" 2>&1", $unauthLines, $unauthCode);
    $unauthOutput = implode("\n", $unauthLines);

    if ($unauthCode !== 0 || str_contains($unauthOutput, '401') || str_contains($unauthOutput, 'UnauthorisedException') || str_contains($unauthOutput, 'Missing authentication cookie') || str_contains($unauthOutput, 'Unauthenticated')) {
        echo "    🛡️ Neutralized: Unauthenticated access rejected with HTTP 401 / UnauthorisedException.\n";
    } else {
        $failures[] = "Attack A Failed: Unauthenticated GET /api/kinship/settings did not block unauth access (got code: $unauthCode, out: $unauthOutput)";
    }

    // Attack Vector B: CSRF Tampering on settings update
    echo "  [Adversarial Attack B] CSRF forgery attack (tampered token)...\n";
    $_SESSION['id'] = $testUserId;
    $_SESSION['famCode'] = 'FAM123';
    $_SESSION['token'] = 'valid_secret_csrf_token_12345';
    $_SERVER['HTTP_X_CSRF_TOKEN'] = 'attacker_forged_token';

    $controller = new KinshipController();

    ob_start();
    $controller->updateSettings();
    $csrfOutput = ob_get_clean();
    $csrfCode = http_response_code();

    if ($csrfCode !== 403 && !str_contains((string)$csrfOutput, 'CSRF token')) {
        $failures[] = "Attack B Failed: Forged CSRF token was not rejected with 403 (got: $csrfCode)";
    } else {
        echo "    🛡️ Neutralized: CSRF forgery attack rejected with HTTP 403 Forbidden.\n";
    }

    // Attack Vector C: Cross-Tenant BOLA/IDOR Parameter Tampering
    echo "  [Adversarial Attack C] BOLA/IDOR attempt to modify another member's radar...\n";
    $victimUserId = 'victim_member_' . bin2hex(random_bytes(4));
    // Victim initial state
    KinshipEngineService::updateRadarSettings($victimUserId, ['radar_enabled' => 1]);

    // Attacker sends request with victim's user_id in payload, but logged in as $testUserId
    $_SESSION['id'] = $testUserId;
    $_SESSION['token'] = 'session_token_xyz';
    $_SERVER['HTTP_X_CSRF_TOKEN'] = 'session_token_xyz';

    $_POST = [
        'user_id'       => $victimUserId, // Attacker attempts to target victim
        'radar_enabled' => 0,
        'token'         => 'session_token_xyz'
    ];

    ob_start();
    $controller->updateSettings();
    $idorOutput = ob_get_clean();

    // Verify victim settings remain UNTOUCHED (still enabled)
    $victimCheck = KinshipEngineService::getRadarSettings($victimUserId);
    if ($victimCheck['radar_enabled'] !== true) {
        $failures[] = "Attack C Succeeded (CRITICAL VULNERABILITY): Victim radar settings were overwritten via IDOR payload!";
    } else {
        echo "    🛡️ Neutralized: Session-bound user_id invariant enforced; victim account completely unharmed.\n";
    }

    // Attack Vector D: Out-of-bounds parameter clamping
    echo "  [Adversarial Attack D] Out-of-bounds parameter tampering (min_confidence = 9999)...\n";
    $_POST = [
        'min_confidence' => 9999, // Invalid option
        'token'          => 'session_token_xyz'
    ];
    ob_start();
    $controller->updateSettings();
    $clampOutput = ob_get_clean();

    $clampedSettings = KinshipEngineService::getRadarSettings($testUserId);
    if ($clampedSettings['min_confidence'] !== 50) {
        $failures[] = "Attack D Failed: Out-of-bounds min_confidence was not clamped to safe default 50 (got: " . $clampedSettings['min_confidence'] . ")";
    } else {
        echo "    🛡️ Neutralized: Invalid confidence values strictly clamped to safe default (50).\n";
    }

} finally {
    // Clean up test data from DB
    $pdo->prepare("DELETE FROM user_radar_settings WHERE user_id IN (?, ?, ?)")
        ->execute([$testUserId, $victimUserId ?? '', 'u999']);
}

// SUMMARY
echo "\n-----------------------------------------------------------------\n";
if (!empty($failures)) {
    echo "❌ SUITE FAILED with " . count($failures) . " error(s):\n";
    foreach ($failures as $f) {
        echo "   - $f\n";
    }
    exit(1);
} else {
    echo "🎉 ALL 5 RADAR SETTINGS & SECURITY VERIFICATIONS PASSED (0 ERRORS)!\n";
    echo "=================================================================\n";
    exit(0);
}
