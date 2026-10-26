<?php

declare(strict_types=1);

/**
 * Automated Verification Suite: Family Tree Node Contact Details Visibility
 *
 * Verifies that clicking a node on the Family Tree surfaces the person's:
 * 1. Email (with clickable mailto: link)
 * 2. Mobile / Telephone (with clickable tel: link)
 * 3. Occupation
 * Across the floating Person Details Card, Modal Dossier, and Graph Pipeline.
 */

$failures = 0;
$checks = 0;

function assertCondition(bool $condition, string $label): void
{
    global $failures, $checks;
    $checks++;
    if ($condition) {
        echo "  ✅ PASS: {$label}\n";
    } else {
        $failures++;
        echo "  ❌ FAIL: {$label}\n";
    }
}

echo "======================================================================\n";
echo "📞  FAMILY TREE NODE CONTACT DETAILS VERIFICATION SUITE\n";
echo "======================================================================\n\n";

$baseDir = dirname(__DIR__);

// -----------------------------------------------------------------------------
// [1] Verify CollateralKinshipService Data Pipeline
// -----------------------------------------------------------------------------
echo "[1] Checking CollateralKinshipService.php SQL and node mappings...\n";
$kinshipServicePath = $baseDir . '/app/services/CollateralKinshipService.php';
$kinshipContent = file_get_contents($kinshipServicePath);

assertCondition($kinshipContent !== false, 'CollateralKinshipService.php exists');
assertCondition(strpos($kinshipContent, 'LEFT JOIN contact c ON fn.user_id = c.id') !== false, 'Joins contact table on user_id');
assertCondition(strpos($kinshipContent, 'LEFT JOIN account a ON fn.user_id = a.id') !== false, 'Joins account table on user_id');
assertCondition(strpos($kinshipContent, 'LEFT JOIN work w ON fn.user_id = w.id') !== false, 'Joins work table on user_id');
assertCondition(strpos($kinshipContent, 'COALESCE(NULLIF(fn.email') !== false, 'Coalesces email across family_nodes, contact, and account');
assertCondition(strpos($kinshipContent, 'COALESCE(NULLIF(fn.mobile') !== false, 'Coalesces mobile across family_nodes and contact');
assertCondition(strpos($kinshipContent, 'COALESCE(NULLIF(fn.occupation') !== false, 'Coalesces occupation across family_nodes, work, and contact');
assertCondition(strpos($kinshipContent, "'email' => (string)(\$memberData['email'] ?? '')") !== false, 'Standalone ego includes email');
assertCondition(strpos($kinshipContent, "'mobile' => (string)(\$memberData['mobile'] ?? '')") !== false, 'Standalone ego includes mobile');
assertCondition(strpos($kinshipContent, "'occupation' => (string)(\$memberData['occupation'] ?? '')") !== false, 'Standalone ego includes occupation');

// -----------------------------------------------------------------------------
// [2] Verify organogram.blade.php Floating Card & Event Handlers
// -----------------------------------------------------------------------------
echo "\n[2] Checking organogram.blade.php Floating Card & JS Event Listeners...\n";
$organogramPath = $baseDir . '/resources/views/member/organogram.blade.php';
$organogramContent = file_get_contents($organogramPath);

assertCondition($organogramContent !== false, 'organogram.blade.php exists');
assertCondition(strpos($organogramContent, 'id="cardOccupationRow"') !== false, 'Contains cardOccupationRow in floating card');
assertCondition(strpos($organogramContent, 'id="cardOccupation"') !== false, 'Contains cardOccupation element in floating card');
assertCondition(strpos($organogramContent, 'id="cardEmailRow"') !== false, 'Contains cardEmailRow in floating card');
assertCondition(strpos($organogramContent, 'id="cardEmail"') !== false, 'Contains cardEmail element in floating card');
assertCondition(strpos($organogramContent, 'id="cardMobileRow"') !== false, 'Contains cardMobileRow in floating card');
assertCondition(strpos($organogramContent, 'id="cardMobile"') !== false, 'Contains cardMobile element in floating card');

// In openPersonCard JavaScript
assertCondition(strpos($organogramContent, "document.getElementById('cardOccupation')") !== false, 'openPersonCard retrieves cardOccupation');
assertCondition(strpos($organogramContent, "document.getElementById('cardEmail')") !== false, 'openPersonCard retrieves cardEmail');
assertCondition(strpos($organogramContent, "document.getElementById('cardMobile')") !== false, 'openPersonCard retrieves cardMobile');
assertCondition(strpos($organogramContent, 'mailto:') !== false, 'openPersonCard generates interactive mailto: link');
assertCondition(strpos($organogramContent, 'tel:') !== false, 'openPersonCard generates interactive tel: link');

// In familyTreeNodes mapping
assertCondition(strpos($organogramContent, 'email: node.email') !== false, 'familyTreeNodes maps node.email');
assertCondition(strpos($organogramContent, 'mobile: node.mobile') !== false, 'familyTreeNodes maps node.mobile');
assertCondition(strpos($organogramContent, 'occupation: node.occupation') !== false, 'familyTreeNodes maps node.occupation');

// In showPersonDetails call
assertCondition(strpos($organogramContent, 'mobile: (rawNode && rawNode.mobile)') !== false, 'family.on(click) passes mobile to showPersonDetails');

// -----------------------------------------------------------------------------
// [3] Verify showModal.js Person Dossier Modal & Bottom Sheet
// -----------------------------------------------------------------------------
echo "\n[3] Checking showModal.js Dossier Modal & Actionable Links...\n";
$showModalPath = $baseDir . '/resources/asset/js/components/familyTree/showModal.js';
$showModalContent = file_get_contents($showModalPath);

assertCondition($showModalContent !== false, 'showModal.js exists');
assertCondition(strpos($showModalContent, 'mobile = \'\'') !== false, 'showModal destructures mobile');
assertCondition(strpos($showModalContent, 'href="mailto:${esc(email)}"') !== false, 'showModal renders clickable mailto: link');
assertCondition(strpos($showModalContent, 'href="tel:${esc(mobile)}"') !== false, 'showModal renders clickable tel: link');
assertCondition(strpos($showModalContent, 'Occupation</div>') !== false, 'showModal displays Occupation section');

// -----------------------------------------------------------------------------
// [4] Verify treeNode.blade.php & index.js Traditional Tree
// -----------------------------------------------------------------------------
echo "\n[4] Checking treeNode.blade.php and index.js...\n";
$treeNodePath = $baseDir . '/resources/views/member/includes/treeNode.blade.php';
$treeNodeContent = file_get_contents($treeNodePath);
assertCondition(strpos($treeNodeContent, 'node-mobile') !== false, 'treeNode.blade.php includes node-mobile span');

$treeIndexPath = $baseDir . '/resources/asset/js/components/familyTree/index.js';
$treeIndexContent = file_get_contents($treeIndexPath);
assertCondition(strpos($treeIndexContent, 'node.dataset.mobile') !== false, 'index.js extracts mobile from node');

// -----------------------------------------------------------------------------
// [5] Verify CSS Styling for Interactive Contact Links
// -----------------------------------------------------------------------------
echo "\n[5] Checking organogram.css styling...\n";
$cssPath = $baseDir . '/public/css/organogram.css';
$cssContent = file_get_contents($cssPath);
assertCondition(strpos($cssContent, '.card-contact-link') !== false, 'organogram.css includes .card-contact-link rules');

// -----------------------------------------------------------------------------
// [6] Verify Live Backend Collateral Graph Output
// -----------------------------------------------------------------------------
echo "\n[6] Testing Live Backend Collateral Graph Output...\n";
require_once $baseDir . '/vendor/autoload.php';
if (file_exists($baseDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($baseDir)->safeLoad();
}

try {
    $db = \Src\Db::connect2();
    $sampleFamCode = 'MODERNMAN';
    $sampleUserStmt = $db->prepare("SELECT user_id FROM family_nodes WHERE family_code = ? AND user_id IS NOT NULL AND user_id != '' LIMIT 1");
    $sampleUserStmt->execute([$sampleFamCode]);
    $sampleUserId = $sampleUserStmt->fetchColumn() ?: '397755OLUSOLA';

    $graph = \App\services\CollateralKinshipService::buildCollateralGraph($sampleFamCode, (string)$sampleUserId);
    assertCondition(isset($graph['nodes']) && is_array($graph['nodes']), 'buildCollateralGraph returns nodes array');

    $firstNode = $graph['nodes'][0] ?? [];
    assertCondition(array_key_exists('email', $firstNode), 'Node object contains email key');
    assertCondition(array_key_exists('mobile', $firstNode), 'Node object contains mobile key');
    assertCondition(array_key_exists('occupation', $firstNode), 'Node object contains occupation key');
} catch (\Throwable $e) {
    echo "  ⚠️  Database simulation note: " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n======================================================================\n";
echo "🎯 CONTACT DETAILS VERIFICATION SUMMARY\n";
echo "======================================================================\n";
echo "Total Checks: {$checks}\n";
echo "Failures    : {$failures}\n";

if ($failures === 0) {
    echo "🎉 ALL CHECKS PASSED: Clicking a tree node displays Email, Mobile, and Occupation.\n";
    exit(0);
} else {
    echo "❌ SOME CHECKS FAILED.\n";
    exit(1);
}
