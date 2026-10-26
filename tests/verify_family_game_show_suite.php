<?php
declare(strict_types=1);

/**
 * 🎮 FAMILY GAME SHOW VERIFICATION SUITE
 * 
 * Verifies:
 * 1. UI Branding: All occurrences of 'Dynasty Trivia Clash' & 'Dynasty Trivia' replaced with 'Family Game Show'.
 * 2. AI Reference Neutralisation: Zero references to AI or 'Llama-3' in the trivia/game show interface.
 * 3. Inclusive Guidance: Accessible story guide and 'Try an Example' mechanism for older/non-technical users.
 * 4. Automated Feed Posting: Game Show generation automatically inserts a published post into `post` and attaches an interactive poll into `post_polls`.
 * 5. Full test cleanup.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\controller\members\FamilyAiHubController;
use Src\Db;

echo "=================================================================\n";
echo "🎮 FAMILY GAME SHOW & FEED POSTING VERIFICATION SUITE\n";
echo "=================================================================\n";

$viewPath = $rootDir . '/resources/views/member/familyAiHub.blade.php';
$viewContent = file_get_contents($viewPath);

// [TEST 1] View Template Rebranding
echo "[TEST 1] Verifying Rebranding to 'Family Game Show' in Blade view...\n";
if (strpos($viewContent, 'Family Game Show') === false) {
    echo "❌ FAILED: 'Family Game Show' not found in view template.\n";
    exit(1);
}
if (stripos($viewContent, 'Dynasty Trivia') !== false) {
    echo "❌ FAILED: Found residual 'Dynasty Trivia' in view template.\n";
    exit(1);
}
if (stripos($viewContent, 'Trivia Clash') !== false) {
    echo "❌ FAILED: Found residual 'Trivia Clash' in view template.\n";
    exit(1);
}
echo "  ✅ PASSED: Rebranding verified. All residual Dynasty Trivia Clash strings eliminated.\n";

// [TEST 2] AI Reference Neutralisation
echo "[TEST 2] Verifying Neutralisation of AI references in Trivia/Game Show view...\n";
if (stripos($viewContent, 'The AI uses these memories') !== false) {
    echo "❌ FAILED: Found 'The AI uses these memories' in view template.\n";
    exit(1);
}
if (stripos($viewContent, 'Llama-3') !== false) {
    echo "❌ FAILED: Found 'Llama-3' in view template.\n";
    exit(1);
}
echo "  ✅ PASSED: AI references successfully neutralised.\n";

// [TEST 3] Inclusive Story Guidance for Older / Less Tech-Educated Users
echo "[TEST 3] Verifying Inclusive Story Guidance & Example Populator...\n";
if (strpos($viewContent, 'Easy Guide for Family Stories') === false) {
    echo "❌ FAILED: Guidance box for older users missing from view template.\n";
    exit(1);
}
if (strpos($viewContent, 'insertExampleTriviaFacts') === false) {
    echo "❌ FAILED: Example populator function missing from view template.\n";
    exit(1);
}
if (strpos($viewContent, 'trivia-posted-alert') === false) {
    echo "❌ FAILED: Post confirmation banner missing from view template.\n";
    exit(1);
}
echo "  ✅ PASSED: Inclusive story guidance and 1-tap example populator verified.\n";

// [TEST 4] Functional Generation & Automatic Feed Posting
echo "[TEST 4] Testing Family Game Show Generation & Automated Feed Posting...\n";

$db = Db::connect2();
$testUserId = '9999GAMESHOWUSER';
$testFamCode = 'FAMGAMESHOWTEST99';

// Clean existing fixtures
$db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);
$db->prepare("DELETE FROM post WHERE id = ? OR postFamCode = ?")->execute([$testUserId, $testFamCode]);

$insUser = $db->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
$insUser->execute([$testUserId, 'Funmi', 'Adeleke', $testFamCode]);

$_SESSION['id'] = $testUserId;
$_SESSION['famCode'] = $testFamCode;
$_SESSION['fullName'] = 'Funmi Adeleke';

// Mock POST payload
$payload = [
    'family_name' => 'The Adeleke Family',
    'category' => 'family_folklore',
    'family_facts' => [
        'Grandpa moved to London in 1968',
        'Uncle Tunde burnt the Christmas turkey in 2012',
        'Auntie Simi claims she invented Nigerian fried rice'
    ],
    'question_count' => 3
];

// Capture API output
ob_start();
$_POST = $payload;
$controller = new FamilyAiHubController();
$controller->generateTrivia();
$output = ob_get_clean();

$response = json_decode($output, true);

if (!is_array($response) || empty($response['success'])) {
    echo "❌ FAILED: generateTrivia did not return success. Output: {$output}\n";
    exit(1);
}

if (empty($response['posted']) || empty($response['post_no']) || empty($response['post_url'])) {
    echo "❌ FAILED: Response does not indicate post was published to feed.\n";
    exit(1);
}

$postNo = (int)$response['post_no'];

// Verify record in database
$stmtPost = $db->prepare("SELECT * FROM post WHERE post_no = ? AND postFamCode = ?");
$stmtPost->execute([$postNo, $testFamCode]);
$postRow = $stmtPost->fetch(\PDO::FETCH_ASSOC);

if (!$postRow) {
    echo "❌ FAILED: Post #{$postNo} not found in database for family {$testFamCode}.\n";
    exit(1);
}

if ($postRow['post_status'] !== 'published') {
    echo "❌ FAILED: Post #{$postNo} status is '{$postRow['post_status']}', expected 'published'.\n";
    exit(1);
}

// Verify poll in post_polls
$stmtPoll = $db->prepare("SELECT * FROM post_polls WHERE post_no = ?");
$stmtPoll->execute([$postNo]);
$pollRow = $stmtPoll->fetch(\PDO::FETCH_ASSOC);

if (!$pollRow) {
    echo "❌ FAILED: Interactive poll not created for post #{$postNo}.\n";
    exit(1);
}

$pollId = (int)$pollRow['poll_id'];
$stmtOpts = $db->prepare("SELECT COUNT(*) FROM post_poll_options WHERE poll_id = ?");
$stmtOpts->execute([$pollId]);
$optionCount = (int)$stmtOpts->fetchColumn();

if ($optionCount < 2) {
    echo "❌ FAILED: Poll options count ({$optionCount}) is less than 2.\n";
    exit(1);
}

echo "  ✅ PASSED: Game Show successfully generated and posted to feed (Post #{$postNo}, Poll #{$pollId} with {$optionCount} options).\n";

// Cleanup test fixtures
$db->prepare("DELETE FROM post_poll_options WHERE poll_id = ?")->execute([$pollId]);
$db->prepare("DELETE FROM post_polls WHERE poll_id = ?")->execute([$pollId]);
$db->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
$db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);

echo "=================================================================\n";
echo "🏆 ALL 4 FAMILY GAME SHOW VERIFICATION TESTS PASSED CLEANLY!\n";
echo "=================================================================\n";
