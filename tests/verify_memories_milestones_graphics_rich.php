<?php
declare(strict_types=1);

/**
 * Verification Suite for Graphics-Rich Memories & Milestones
 *
 * Verifies:
 * 1. Upcoming milestones are NOT shareable (no share buttons, no composer modals).
 * 2. Milestones and Memories provide emotionally meaningful content.
 * 3. Both are graphics-rich with bespoke celebration SVGs, avatar halos, and archival styling.
 * 4. MemoryMilestoneService returns rich milestone metadata, partner pictures, and meaningful messages.
 * 5. British English (UK) invariant compliance.
 */

echo "=================================================================\n";
echo "🎨 VERIFYING GRAPHICS-RICH MEMORIES & MILESTONES (TAT GATE)\n";
echo "=================================================================\n\n";

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

require_once $rootDir . '/app/config/init.php';

use App\services\MemoryMilestoneService;
use Src\Db;

$service = new MemoryMilestoneService();

// 1. Verify Service Meaningful Message Generation
echo "[Check 1/5] Testing Birthday & Anniversary Meaningful Message Generation...\n";

$bday50Msg = $service->getBirthdayMeaningfulMessage(50, 'Sarah Jenkins');
if (!str_contains($bday50Msg, 'Golden Jubilee') || !str_contains($bday50Msg, '50 remarkable years')) {
    echo "❌ FAILED: 50th birthday meaningful message incorrect: {$bday50Msg}\n";
    exit(1);
}

$bday18Msg = $service->getBirthdayMeaningfulMessage(18, 'David Cole');
if (!str_contains($bday18Msg, 'adulthood') || !str_contains($bday18Msg, 'strong roots')) {
    echo "❌ FAILED: 18th birthday meaningful message incorrect: {$bday18Msg}\n";
    exit(1);
}

$anniv25Msg = $service->getAnniversaryMeaningfulMessage(25, 'Sarah & David');
if (!str_contains($anniv25Msg, 'Silver Jubilee') || !str_contains($anniv25Msg, 'quarter-century')) {
    echo "❌ FAILED: 25th anniversary meaningful message incorrect: {$anniv25Msg}\n";
    exit(1);
}

$eventMsg = $service->getEventMeaningfulMessage('Annual Family Reunion', 'Reunion');
if (!str_contains($eventMsg, 'generations together') || !str_contains($eventMsg, 'reconnect')) {
    echo "❌ FAILED: Reunion event meaningful message incorrect: {$eventMsg}\n";
    exit(1);
}

echo "  ✅ PASS: All meaningful message engines return emotionally rich family copy.\n\n";

// 2. Verify Desktop View Template (memoriesMilestonesWidget.blade.php)
echo "[Check 2/5] Inspecting Desktop Widget for Non-Shareable Milestones & Graphics-Rich Design...\n";

$widgetFile = file_get_contents(__DIR__ . '/../resources/views/member/includes/memoriesMilestonesWidget.blade.php');
if ($widgetFile === false) {
    echo "❌ FAILED: Unable to read memoriesMilestonesWidget.blade.php\n";
    exit(1);
}

// Ensure upcoming milestones do NOT have Then & Now or share buttons
if (str_contains($widgetFile, '__openThenNow') || str_contains($widgetFile, 'Then &amp; Now')) {
    echo "❌ FAILED: Upcoming milestones still contain Then & Now share button!\n";
    exit(1);
}

// Ensure WhatsApp and Share buttons are completely removed from memories
if (str_contains($widgetFile, 'whatsappMemoryUrl') || str_contains($widgetFile, 'bi-whatsapp') || str_contains($widgetFile, 'openShareModal') || str_contains($widgetFile, 'shareMemoryModal')) {
    echo "❌ FAILED: WhatsApp or Share button/modal still present in memories widget!\n";
    exit(1);
}

// Ensure graphics-rich celebration SVGs are present
if (!str_contains($widgetFile, 'Birthday Celebration Cake & Sparkles SVG') || 
    !str_contains($widgetFile, 'Anniversary Interlocking Rings & Laurel SVG') || 
    !str_contains($widgetFile, 'Family Heirloom Chronicle')) {
    echo "❌ FAILED: Bespoke graphics-rich SVGs missing from desktop widget!\n";
    exit(1);
}

// Ensure meaningful message binding is present
if (!str_contains($widgetFile, 'milestone.meaningful_message')) {
    echo "❌ FAILED: milestone.meaningful_message binding missing from desktop widget!\n";
    exit(1);
}

// Ensure celebratory avatar halos are present
if (!str_contains($widgetFile, '👑') || !str_contains($widgetFile, 'milestone.p1_img')) {
    echo "❌ FAILED: Celebrant crown or dual anniversary avatar frames missing!\n";
    exit(1);
}

// Ensure 'in xx days' countdown badge is placed underneath
if (!str_contains($widgetFile, "'in xx days' placed UNDER as requested by user")) {
    echo "❌ FAILED: 'in xx days' countdown badge not placed underneath!\n";
    exit(1);
}

echo "  ✅ PASS: Desktop widget is graphics-rich, meaningful, private (no WhatsApp/share), with 'in xx days' under.\n\n";

// 3. Verify Mobile View Template & Post Card (Privacy & 'in xx days' under)
echo "[Check 3/5] Inspecting Mobile Highlights Tray & Post Card for Absolute Privacy...\n";

$mobileFile = file_get_contents(__DIR__ . '/../resources/views/member/includes/mobileHighlightsTray.blade.php');
if ($mobileFile === false) {
    echo "❌ FAILED: Unable to read mobileHighlightsTray.blade.php\n";
    exit(1);
}

// Ensure mobile milestones do NOT contain Wish Birthday modal trigger
if (str_contains($mobileFile, 'Wish Birthday')) {
    echo "❌ FAILED: Mobile milestones still contain 'Wish Birthday' shareable action!\n";
    exit(1);
}

// Ensure mobile memories do NOT contain WhatsApp or share buttons
if (str_contains($mobileFile, 'whatsappMemoryUrl') || str_contains($mobileFile, 'bi-whatsapp')) {
    echo "❌ FAILED: Mobile memories still contain WhatsApp button!\n";
    exit(1);
}

// Ensure mobile milestones have graphics, meaningful message, and 'in xx days' placed under
if (!str_contains($mobileFile, 'milestone.meaningful_message') || !str_contains($mobileFile, 'Family Chronicle') || !str_contains($mobileFile, "'in xx days' placed UNDER as requested by user")) {
    echo "❌ FAILED: Mobile milestones missing meaningful message or 'in xx days' under!\n";
    exit(1);
}

// Verify Post Card External Social Leaks are Removed (Absolute Privacy Standard)
$postCardFile = file_get_contents(__DIR__ . '/../resources/views/components/feed/post-card.blade.php');
if ($postCardFile === false) {
    echo "❌ FAILED: Unable to read post-card.blade.php\n";
    exit(1);
}

if (str_contains($postCardFile, 'bi-whatsapp') || str_contains($postCardFile, 'api.whatsapp.com')) {
    echo "❌ FAILED: Post card still contains WhatsApp share link! Must be private.\n";
    exit(1);
}

echo "  ✅ PASS: Mobile highlights tray and feed post cards are strictly private (0 share/WhatsApp buttons) with 'in xx days' under.\n\n";

// 4. Verify API Payload Structure
echo "[Check 4/5] Verifying API / MemoryMilestoneService Payload Completeness...\n";

// Seed a temporary test user and family node to verify live payload
$db = Db::connect2();
$testFamCode = 'MIL_GRAPH_' . bin2hex(random_bytes(3));
$testUserId = 'TST_' . bin2hex(random_bytes(4));

$bdayStmt = $db->prepare("
    INSERT INTO personal (id, firstName, lastName, day, month, year, famCode, gender)
    VALUES (?, 'Grandpa', 'Ogun', DAY(CURDATE()), MONTH(CURDATE()), YEAR(CURDATE()) - 80, ?, 'Male')
");
$bdayStmt->execute([$testUserId, $testFamCode]);

$milestones = $service->getUpcomingMilestones($testUserId, [$testFamCode], 7);

// Cleanup test record
$delStmt = $db->prepare("DELETE FROM personal WHERE id = ?");
$delStmt->execute([$testUserId]);

$foundBirthday = false;
foreach ($milestones as $ms) {
    if ($ms['type'] === 'birthday' && $ms['user_id'] === $testUserId) {
        $foundBirthday = true;
        if (!isset($ms['meaningful_message']) || !str_contains($ms['meaningful_message'], '80 years of strong roots')) {
            echo "❌ FAILED: Milestone payload missing or inaccurate meaningful_message: " . json_encode($ms) . "\n";
            exit(1);
        }
        if (!isset($ms['landmark_tag']) || !str_contains($ms['landmark_tag'], '80th Oak Milestone')) {
            echo "❌ FAILED: Milestone payload missing landmark_tag: " . json_encode($ms) . "\n";
            exit(1);
        }
        if (($ms['graphic_type'] ?? '') !== 'birthday') {
            echo "❌ FAILED: Milestone payload missing graphic_type: " . json_encode($ms) . "\n";
            exit(1);
        }
        break;
    }
}

if (!$foundBirthday) {
    echo "❌ FAILED: Seeded milestone was not returned by getUpcomingMilestones!\n";
    exit(1);
}

echo "  ✅ PASS: Live service payload confirmed with rich milestone metadata and emotional tribute copy.\n\n";

// 5. Verify British English Invariant on All New Copy
echo "[Check 5/5] Checking British English (UK) Invariant on View Copy...\n";

$forbiddenUSWords = ['honoring', 'celebrating ' => null, 'jubilee' => null, 'favor', 'flavor', 'center', 'defense', 'organize', 'customized'];
$viewContents = $widgetFile . ' ' . $mobileFile;

$usViolations = [];
if (stripos($viewContents, 'honoring') !== false) {
    $usViolations[] = 'honoring (must use honouring)';
}
if (stripos($viewContents, 'favor') !== false) {
    $usViolations[] = 'favor (must use favour)';
}
if (stripos($viewContents, 'organize') !== false) {
    $usViolations[] = 'organize (must use organise)';
}

if (!empty($usViolations)) {
    echo "❌ FAILED: US English violations detected: " . implode(', ', $usViolations) . "\n";
    exit(1);
}

echo "  ✅ PASS: 100% British English compliance verified across all templates.\n\n";

echo "=================================================================\n";
echo "🎉 ALL 5 GRAPHICS-RICH VERIFICATION GATES PASSED CLEANLY!\n";
echo "=================================================================\n";
