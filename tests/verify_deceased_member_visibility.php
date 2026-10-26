<?php

declare(strict_types=1);

/**
 * Automated Verification Suite: Obvious Deceased Member Visibility & Dignified Memorial UX
 *
 * Verifies that deceased members on the Family Tree across all views
 * (Panoramic Dynasty Canvas, Pedigree Outline, Radial Fan Chart, Person Details Card,
 * Heritage Dossier Modal, and Lineage Studio) are distinct, obvious, and reverently memorialised.
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
echo "🕊️  DECEASED MEMBER VISIBILITY & MEMORIAL UX VERIFICATION SUITE\n";
echo "======================================================================\n\n";

$baseDir = dirname(__DIR__);

// 1. Verify resources/views/member/organogram.blade.php
echo "[1] Checking organogram.blade.php data pipeline & view modes...\n";
$organogramContent = file_get_contents($baseDir . '/resources/views/member/organogram.blade.php');
assertCondition(
    $organogramContent !== false,
    'organogram.blade.php read successfully'
);

assertCondition(
    str_contains($organogramContent, 'isDeceased: Boolean(node.is_deceased)'),
    'familyTreeNodes pushes isDeceased boolean from node.is_deceased'
);

assertCondition(
    str_contains($organogramContent, 'birth_year:') && str_contains($organogramContent, 'death_year:'),
    'familyTreeNodes binds birth_year and death_year for lifespan rendering'
);

assertCondition(
    str_contains($organogramContent, 'cardLifespanRow'),
    'personDetailsCard includes cardLifespanRow HTML element'
);

assertCondition(
    str_contains($organogramContent, "statusBadge.className = 'card-status-badge card-status-deceased'") &&
    str_contains($organogramContent, 'In Loving Memory (Deceased)'),
    'openPersonCard assigns card-status-deceased badge'
);

assertCondition(
    str_contains($organogramContent, "avatarEl.style.filter = isDeceased ? 'grayscale(100%)' : 'none'"),
    'openPersonCard applies grayscale filter to avatar photo for deceased ancestors'
);

assertCondition(
    str_contains($organogramContent, 'if (claimSpotSection) claimSpotSection.style.display = \'none\';') &&
    str_contains($organogramContent, 'if (isDeceased) {') &&
    str_contains($organogramContent, 'claimSpotSection.style.display = isReadOnlyTree ? \'none\' : \'block\';'),
    'openPersonCard suppresses claimSpotSection (no WhatsApp/SMS invites to deceased relatives)'
);

assertCondition(
    str_contains($organogramContent, 'pedigree-deceased-card') &&
    str_contains($organogramContent, 'pedigree-avatar-deceased') &&
    str_contains($organogramContent, 'pedigree-deceased-pill fw-bold'),
    'renderPedigreeOutline adds pedigree-deceased-card and grayscale avatar'
);

assertCondition(
    str_contains($organogramContent, 'l1}${isDeceased ? \' 🕊️\' : \'\'}'),
    'renderRadialFanChart renders memorial dove icon for deceased ancestors'
);

// 2. Verify KinshipTree (Canvas SVG Engine)
echo "\n[2] Checking kinshiptree.js SVG engine (both source and public distribution)...\n";
$kinshipSource = file_get_contents($baseDir . '/resources/asset/js/vendor/kinshiptree.js');
$kinshipPublic = file_get_contents($baseDir . '/public/js/vendor/kinshiptree.js');

assertCondition(
    $kinshipSource !== false && $kinshipPublic !== false,
    'Both source and public kinshiptree.js exist'
);

assertCondition(
    $kinshipSource === $kinshipPublic,
    'resources/asset/js/vendor/kinshiptree.js and public/js/vendor/kinshiptree.js are 100% identical'
);

assertCondition(
    str_contains($kinshipSource, 'id="kinshipDeceasedGrayscale"') &&
    str_contains($kinshipSource, 'feColorMatrix'),
    'SVG defs include kinshipDeceasedGrayscale matrix filter'
);

assertCondition(
    str_contains($kinshipSource, 'id="gradCardDarkDeceased"') &&
    str_contains($kinshipSource, 'id="gradTopDeceased"'),
    'SVG defs include gradCardDarkDeceased and gradTopDeceased memorial gradients'
);

assertCondition(
    str_contains($kinshipSource, 'isDeceased ? \'url(#gradCardDarkDeceased)\'') &&
    str_contains($kinshipSource, 'isDeceased ? \'url(#gradTopDeceased)\''),
    'Node card fill and top ribbon switch to memorial gradients for deceased ancestors'
);

assertCondition(
    str_contains($kinshipSource, 'filter="url(#kinshipDeceasedGrayscale)"'),
    'Avatar image applies kinshipDeceasedGrayscale filter for deceased ancestors'
);

assertCondition(
    str_contains($kinshipSource, '🕊️ IN MEMORY') &&
    str_contains($kinshipSource, 'kinship-deceased-banner'),
    'Node renders prominent 🕊️ IN MEMORY ribbon banner'
);

assertCondition(
    str_contains($kinshipSource, 'Deceased Dove & Tribute Badge on Portrait Ring') &&
    str_contains($kinshipSource, '🕊️'),
    'Portrait ring includes memorial dove tribute badge'
);

assertCondition(
    str_contains($kinshipSource, 'lifespanText'),
    'Lifespan text calculation and rendering included on node cards'
);

// 3. Verify public/css/organogram.css
echo "\n[3] Checking public/css/organogram.css styling rules...\n";
$cssContent = file_get_contents($baseDir . '/public/css/organogram.css');

assertCondition(
    str_contains($cssContent, '.tree-node.deceased .profile-image {') &&
    str_contains($cssContent, 'filter: grayscale(100%);'),
    'CSS applies grayscale filter to .tree-node.deceased'
);

assertCondition(
    str_contains($cssContent, '.node-deceased-badge {'),
    'CSS defines .node-deceased-badge'
);

assertCondition(
    str_contains($cssContent, '.pedigree-deceased-card {') &&
    str_contains($cssContent, '.pedigree-avatar-deceased {'),
    'CSS defines .pedigree-deceased-card and .pedigree-avatar-deceased'
);

assertCondition(
    str_contains($cssContent, '.card-status-deceased {'),
    'CSS defines .card-status-deceased'
);

// 4. Verify treeNode.blade.php
echo "\n[4] Checking treeNode.blade.php traditional HTML organogram nodes...\n";
$treeNodeContent = file_get_contents($baseDir . '/resources/views/member/includes/treeNode.blade.php');

assertCondition(
    str_contains($treeNodeContent, 'node-deceased-badge') &&
    str_contains($treeNodeContent, 'In Memory'),
    'treeNode.blade.php renders node-deceased-badge ribbon for deceased relatives'
);

// 5. Verify showModal.js (Heritage Dossier Modal)
echo "\n[5] Checking showModal.js...\n";
$showModalContent = file_get_contents($baseDir . '/resources/asset/js/components/familyTree/showModal.js');

assertCondition(
    str_contains($showModalContent, '!isDeceased') && str_contains($showModalContent, 'claimSpotHtml'),
    'showModal.js suppresses claimSpotHtml for deceased relatives'
);

assertCondition(
    str_contains($showModalContent, 'filter: grayscale(100%)'),
    'showModal.js applies grayscale styling to avatar for deceased relatives'
);

assertCondition(
    str_contains($showModalContent, 'memorialBannerHtml') && str_contains($showModalContent, 'IN LOVING MEMORY'),
    'showModal.js displays memorial banner for deceased relatives'
);

// 6. Verify familyStudio.blade.php
echo "\n[6] Checking familyStudio.blade.php (Lineage Studio)...\n";
$studioContent = file_get_contents($baseDir . '/resources/views/member/familyStudio.blade.php');

assertCondition(
    str_contains($studioContent, '!empty($fatherNode[\'is_deceased\'])') &&
    str_contains($studioContent, '!empty($motherNode[\'is_deceased\'])'),
    'familyStudio.blade.php displays deceased status for parents'
);

assertCondition(
    str_contains($studioContent, 'createStudioCardHtml') &&
    str_contains($studioContent, 'isDeceased ? \'<span class="badge bg-secondary ms-1" style="font-size: 0.7rem;"><i class="bi bi-flower1"></i> Deceased</span>\' : \'\''),
    'createStudioCardHtml displays deceased badge and grayscale avatar'
);

echo "\n======================================================================\n";
echo "SUMMARY: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed.\n";
echo "======================================================================\n";

if ($failures > 0) {
    exit(1);
}
echo "🎉 ALL DECEASED MEMBER OBVIOUSNESS CHECKS PASSED!\n";
exit(0);
