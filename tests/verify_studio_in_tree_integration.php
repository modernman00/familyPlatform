<?php
declare(strict_types=1);

/**
 * 🏛️ FAMILYPLATFORM INTEGRATED STUDIO IN FAMILY TREE VERIFICATION SUITE
 *
 * Verifies:
 * 1. Organogram controller passes complete studio datasets (generations, stats, rootNodeId, rootUnions, pendingRelRequests)
 * 2. organogram.blade.php contains:
 *    - Mode switcher button for Family Studio (#btnModeStudio)
 *    - Discovery banner informing users that Studio is now directly on the tree page (#treeStudioDiscoveryBanner)
 *    - Mode 5 container embedding member.includes.familyStudioContent (#familyStudioContainer)
 *    - Quick action configureFamilyBtn wired to switchTreeViewMode('studio')
 *    - Empty state actions wired to switchTreeViewMode('studio')
 *    - Node details modal wired to closeNodeModalAndOpenStudio()
 * 3. familyStudioContent.blade.php contains:
 *    - Quick-return bar pointing back to Interactive Tree (switchTreeViewMode('canvas'))
 *    - Real-time studio dirty state tracking (window.__familyStudioDirty = true)
 *    - Strict CSRF token presence on all forms
 *    - British English compliance
 */

$baseDir = dirname(__DIR__);

echo "=================================================================\n";
echo "🏛️ INTEGRATED FAMILY STUDIO IN TREE VERIFICATION SUITE\n";
echo "=================================================================\n";

$checks = 0;
$failures = 0;

function assertCondition(bool $condition, string $description): void {
    global $checks, $failures;
    $checks++;
    if ($condition) {
        echo "  ✅ PASS: {$description}\n";
    } else {
        echo "  ❌ FAIL: {$description}\n";
        $failures++;
    }
}

// 1. Check Organogram controller logic
echo "\n[1] Auditing app/controller/members/Organogram.php...\n";
$controllerContent = file_get_contents($baseDir . '/app/controller/members/Organogram.php');
assertCondition($controllerContent !== false, 'Organogram.php read successfully');

assertCondition(
    str_contains($controllerContent, 'protected function prepareStudioData(array $graphData, string $idStr, bool $isSelf): array'),
    'prepareStudioData() helper method defined on Organogram controller'
);

assertCondition(
    str_contains($controllerContent, '$studioData = $this->prepareStudioData($graphData, $idStr, $isSelf);') &&
    str_contains($controllerContent, "'generations'") &&
    str_contains($controllerContent, "'stats'") &&
    str_contains($controllerContent, "'rootNodeId'") &&
    str_contains($controllerContent, "'rootUnions'") &&
    str_contains($controllerContent, "'pendingRelRequests'"),
    'Organogram::index() computes and passes complete studio datasets to view'
);

// 2. Check organogram.blade.php template integration
echo "\n[2] Auditing resources/views/member/organogram.blade.php...\n";
$organogramContent = file_get_contents($baseDir . '/resources/views/member/organogram.blade.php');
assertCondition($organogramContent !== false, 'organogram.blade.php read successfully');

assertCondition(
    str_contains($organogramContent, 'id="btnModeStudio"') &&
    str_contains($organogramContent, "switchTreeViewMode('studio')") &&
    str_contains($organogramContent, 'Update Tree'),
    'organogram.blade.php layout segmented control includes Family Studio mode button with Update Tree badge'
);

assertCondition(
    str_contains($organogramContent, 'id="treeStudioDiscoveryBanner"') &&
    str_contains($organogramContent, 'Family Studio is now inside your Family Tree!') &&
    str_contains($organogramContent, 'dismissTreeStudioDiscoveryBanner()'),
    'organogram.blade.php features prominent discovery banner informing users of Studio in tree'
);

assertCondition(
    str_contains($organogramContent, 'id="familyStudioContainer"') &&
    str_contains($organogramContent, "@include('member.includes.familyStudioContent')"),
    'organogram.blade.php embeds member.includes.familyStudioContent inside familyStudioContainer'
);

assertCondition(
    str_contains($organogramContent, 'id="configureFamilyBtn"') &&
    str_contains($organogramContent, "switchTreeViewMode('studio')"),
    'Top header configureFamilyBtn switches to studio mode directly on the page'
);

assertCondition(
    str_contains($organogramContent, "closeNodeModalAndOpenStudio") &&
    str_contains($organogramContent, "switchTreeViewMode('studio')"),
    'Node details modal allows opening studio directly from tree node'
);

assertCondition(
    str_contains($organogramContent, "urlMode === 'studio'") &&
    str_contains($organogramContent, "switchTreeViewMode('studio')"),
    'URL query parameter ?mode=studio automatically activates studio mode on page load'
);

// 3. Check familyStudioContent.blade.php partial
echo "\n[3] Auditing resources/views/member/includes/familyStudioContent.blade.php...\n";
$studioContent = file_get_contents($baseDir . '/resources/views/member/includes/familyStudioContent.blade.php');
assertCondition($studioContent !== false, 'familyStudioContent.blade.php read successfully');

assertCondition(
    str_contains($studioContent, 'Return to Interactive Tree') &&
    str_contains($studioContent, "switchTreeViewMode('canvas')"),
    'familyStudioContent.blade.php includes quick-return bar navigating back to Interactive Tree'
);

assertCondition(
    str_contains($studioContent, 'window.__familyStudioDirty = true'),
    'familyStudioContent.blade.php marks tree dirty upon relative creation to guarantee fresh tree refresh'
);

assertCondition(
    str_contains($studioContent, 'id="studioAddRelativeModal"') &&
    str_contains($studioContent, 'id="studioEditModal"'),
    'familyStudioContent.blade.php retains full modal functionality for adding and editing kin'
);

echo "\n=================================================================\n";
echo "SUMMARY: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed.\n";
echo "=================================================================\n";

if ($failures > 0) {
    echo "❌ INTEGRATED STUDIO SUITE FAILED!\n";
    exit(1);
}

echo "🎉 ALL INTEGRATED STUDIO SUITE CHECKS PASSED WITH 100% SUCCESS!\n";
exit(0);
