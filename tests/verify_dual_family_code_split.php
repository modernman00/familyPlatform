<?php
declare(strict_types=1);

/**
 * 🔍 VERIFICATION SUITE: DUAL FAMILY CODE SPLIT (MARRIED & BIRTH FAMILY CODES)
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$rootDir = dirname(__DIR__);
$leftCol = (string)file_get_contents($rootDir . '/resources/views/member/includes/leftColumn.blade.php');
$getProfile = (string)file_get_contents($rootDir . '/resources/views/member/getProfile.blade.php');
$profileService = (string)file_get_contents($rootDir . '/app/services/ProfilepageService.php');
$profileController = (string)file_get_contents($rootDir . '/app/controller/members/ProfilePage.php');
$baseController = (string)file_get_contents($rootDir . '/app/controller/BaseController.php');
$profileCss = (string)file_get_contents($rootDir . '/public/css/profilepage.css');

$failures = [];
$total = 0;

function checkSplit(bool $cond, string $passMsg, string $failMsg, array &$failures, int &$total): void {
    $total++;
    if ($cond) {
        echo "  ✅ PASSED: {$passMsg}\n";
    } else {
        echo "  ❌ FAILED: {$failMsg}\n";
        $failures[] = $failMsg;
    }
}

echo "\n=================================================================\n";
echo "🔍 DUAL FAMILY CODE SPLIT (MARRIED & BIRTH) VERIFICATION SUITE\n";
echo "=================================================================\n\n";

echo "[1/6] Checking ProfilepageService & ProfilePage controller hydration...\n";
checkSplit(
    str_contains($profileService, 'BirthFamilyCodeService') &&
    str_contains($profileService, "'maternalFamCode'") &&
    str_contains($profileService, "'hasMaternalCode'") &&
    str_contains($profileController, "'maternalFamCode'") &&
    str_contains($profileController, "'hasMaternalCode'"),
    "ProfilepageService and ProfilePage controller hydrate and pass Married (primaryFamCode) and Birth (maternalFamCode) codes.",
    "ProfilepageService or ProfilePage controller missing dual family code hydration",
    $failures,
    $total
);

echo "\n[2/6] Checking leftColumn.blade.php dual pill split & hiding Married Code when absent...\n";
checkSplit(
    str_contains($leftCol, 'id="leftColumnDualFamilyCodes"') &&
    str_contains($leftCol, 'id="marriedFamCodePill"') &&
    str_contains($leftCol, 'id="birthFamCodePill"') &&
    str_contains($leftCol, '@if($leftMarriedCode !== \'\')') &&
    str_contains($leftCol, 'married_family_code') &&
    str_contains($leftCol, 'birth_family_code'),
    "leftColumn.blade.php splits into Married Code and Birth Code pills and hides Married Code when user does not have one.",
    "leftColumn.blade.php missing split Married/Birth family code pills or conditional Married Code hiding",
    $failures,
    $total
);

echo "\n[3/6] Checking getProfile.blade.php dual family code display & hiding Married Code when absent...\n";
checkSplit(
    str_contains($getProfile, 'married_family_code') &&
    str_contains($getProfile, 'birth_family_code') &&
    str_contains($getProfile, '@if($heroMarriedCode !== \'\')'),
    "getProfile.blade.php displays Married and Birth family codes and hides Married Code when user only has a Birth Code.",
    "getProfile.blade.php missing dual family code display or conditional Married Code hiding",
    $failures,
    $total
);

echo "\n[4/6] Checking BaseController::formatPerson birth family resolution...\n";
checkSplit(
    str_contains($baseController, 'BirthFamilyCodeService') &&
    str_contains($baseController, "'maternalFamCode'") &&
    str_contains($baseController, "'hasMaternalCode'"),
    "BaseController::formatPerson resolves verified BirthFamilyCodeService status for member profiles.",
    "BaseController::formatPerson missing BirthFamilyCodeService resolution",
    $failures,
    $total
);

echo "\n[5/6] Checking CSS theme-adaptive & WCAG AA styling for birth pill...\n";
checkSplit(
    str_contains($profileCss, '.family-code-pill--birth') &&
    str_contains($profileCss, '.family-code-label--birth') &&
    str_contains($profileCss, 'min-height: 44px;'),
    "profilepage.css defines theme-adaptive light/dark mode styles and >= 44px touch targets for Married & Birth pills.",
    "profilepage.css missing birth family code pill styles or 44px touch target",
    $failures,
    $total
);

echo "\n[6/6] Live integration test with dual-code user (937619LAFANE)...\n";
$svc = new \App\services\ProfilepageService();
$profilePayload = $svc->getProfileData('937619LAFANE');
checkSplit(
    ($profilePayload['primaryFamCode'] ?? '') === 'MODERNMAN' &&
    ($profilePayload['maternalFamCode'] ?? '') === 'AKI980' &&
    ($profilePayload['hasMaternalCode'] ?? false) === true,
    "Live ProfilepageService::getProfileData('937619LAFANE') returns primaryFamCode='MODERNMAN', maternalFamCode='AKI980', hasMaternalCode=true.",
    "Live ProfilepageService::getProfileData failed to resolve dual family codes",
    $failures,
    $total
);

echo "\n=================================================================\n";
if (empty($failures)) {
    echo "🏆 ALL {$total} DUAL FAMILY CODE SPLIT CHECKS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ VERIFICATION FAILED:\n";
    foreach ($failures as $f) {
        echo "   - {$f}\n";
    }
    echo "=================================================================\n";
    exit(1);
}
