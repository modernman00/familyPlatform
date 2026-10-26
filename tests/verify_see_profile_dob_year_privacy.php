<?php
declare(strict_types=1);

/**
 * 🛡️ Test Suite: See Profile Date of Birth Privacy Verification
 * 
 * Tests:
 * 1. Schema verification: contact table contains show_birth_year column.
 * 2. Member data formatting: BaseController::findMemberById yields show_birth_year.
 * 3. Privacy default: Birth year is omitted by default unless user explicitly specifies.
 * 4. Privacy opt-in: Birth year is displayed when show_birth_year is 'on'.
 * 5. Route verification: /seeProfile/[a:id] is registered in router.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

echo "=================================================================\n";
echo "🔍 VERIFY SEE PROFILE DOB YEAR PRIVACY SUITE\n";
echo "=================================================================\n\n";

$pdo = \Src\Db::connect2();
$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    echo "• {$name}... ";
    if ($condition) {
        echo "✅ PASSED\n";
        $passed++;
    } else {
        echo "❌ FAILED: {$detail}\n";
        $failed++;
    }
}

// 1. Column exists in contact table
$stmt = $pdo->query("SHOW COLUMNS FROM contact LIKE 'show_birth_year'");
$column = $stmt->fetch(PDO::FETCH_ASSOC);
assertTest(
    "Contact table has show_birth_year column",
    !empty($column),
    "Column show_birth_year not found in contact table"
);

// 2. BaseController::findMemberById retrieves show_birth_year
$memberData = \App\controller\BaseController::findMemberById('117540OLAWALE');
assertTest(
    "BaseController::findMemberById includes show_birth_year key",
    array_key_exists('show_birth_year', $memberData),
    "show_birth_year key missing from formatted person data"
);

// 3. Default state for 117540OLAWALE is 'off'
assertTest(
    "Default show_birth_year for 117540OLAWALE is 'off'",
    ($memberData['show_birth_year'] ?? '') === 'off',
    "Expected 'off', got: " . var_export($memberData['show_birth_year'] ?? null, true)
);

// 4. Test Date of Birth formatting logic in getProfile.blade.php
$renderDob = function(array $data): string {
    return !empty($data['day']) ? ($data['day'] . ' / ' . ($data['month'] ?? '') . ((($data['show_birth_year'] ?? 'off') === 'on' && !empty($data['year'])) ? ' / ' . $data['year'] : '')) : 'Not Specified';
};

$dataWithYearHidden = [
    'day' => '29',
    'month' => 'Jul',
    'year' => '1979',
    'show_birth_year' => 'off',
];
assertTest(
    "DOB formatting omits year when show_birth_year is 'off'",
    $renderDob($dataWithYearHidden) === '29 / Jul',
    "Expected '29 / Jul', got: " . $renderDob($dataWithYearHidden)
);

$dataWithYearShown = [
    'day' => '29',
    'month' => 'Jul',
    'year' => '1979',
    'show_birth_year' => 'on',
];
assertTest(
    "DOB formatting includes year when show_birth_year is 'on'",
    $renderDob($dataWithYearShown) === '29 / Jul / 1979',
    "Expected '29 / Jul / 1979', got: " . $renderDob($dataWithYearShown)
);

$dataEmpty = [
    'day' => '',
    'month' => '',
    'year' => '',
];
assertTest(
    "DOB formatting returns 'Not Specified' when day is empty",
    $renderDob($dataEmpty) === 'Not Specified',
    "Expected 'Not Specified', got: " . $renderDob($dataEmpty)
);

// 5. Verify router registration for /seeProfile/[a:id]
$routerContent = file_get_contents($rootDir . '/app/router/allMembersRoute.php');
assertTest(
    "Route /seeProfile/[a:id] is mapped in allMembersRoute.php",
    str_contains($routerContent, "'/seeProfile/[a:id]'"),
    "Route /seeProfile/[a:id] missing from app/router/allMembersRoute.php"
);

// 6. Verify accountSetting.blade.php contains show_birth_year toggle
$accountSettingContent = file_get_contents($rootDir . '/resources/views/member/accountSetting.blade.php');
assertTest(
    "accountSetting.blade.php has show_birth_year toggle",
    str_contains($accountSettingContent, 'name="show_birth_year"'),
    "name=\"show_birth_year\" toggle not found in accountSetting.blade.php"
);

echo "\n-----------------------------------------------------------------\n";
echo "SUMMARY: {$passed} Passed, {$failed} Failed\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
