<?php
declare(strict_types=1);

/**
 * 🎨 FAMILY FLYER STUDIO REGRESSION GATE
 *
 * Since Nov 2026 the Family Hub "Flyer Studio" tab embeds the shared-lib
 * Universal Poster Engine (/poster-studio) instead of the legacy in-page studio.
 *
 * Verifies:
 * 1. The Flyer Studio tab pane exists and embeds /poster-studio?app=familyplatform.
 * 2. The engine is lazy-mounted (data-src, set on first shown.bs.tab) so the Hub stays fast.
 * 3. A full-screen escape hatch to the standalone engine is present.
 * 4. The legacy in-page studio (presets, AR room, canvas renderer) is fully removed — one editor only.
 * 5. The /poster-studio and render API routes are registered.
 * 6. Universal British English (UK) compliance and zero 'Masterpiece' buzzwords.
 */

echo "======================================================================\n";
echo "  🎨 FAMILY FLYER STUDIO (UNIVERSAL POSTER ENGINE) REGRESSION GATE   \n";
echo "======================================================================\n";

$root = dirname(__DIR__);
$bladePath = $root . '/resources/views/member/familyAiHub.blade.php';
$routerPath = $root . '/app/router/router.php';
foreach ([$bladePath, $routerPath] as $path) {
    if (!file_exists($path)) {
        echo "❌ FAIL: required file not found at {$path}\n";
        exit(1);
    }
}

$content = (string) file_get_contents($bladePath);
$router = (string) file_get_contents($routerPath);
$failures = [];

// 1. Embedded engine
if (!str_contains($content, 'id="tab-flyer" role="tabpanel"')) {
    $failures[] = "Flyer Studio tab pane (#tab-flyer) is missing.";
}
if (!preg_match('/<iframe[^>]*id="flyerEngineFrame"[^>]*data-src="\/poster-studio\?app=familyplatform"/s', $content)) {
    $failures[] = "Flyer Studio must embed the Universal Poster Engine via data-src=\"/poster-studio?app=familyplatform\".";
}
if (!preg_match('/<iframe[^>]*id="flyerEngineFrame"[^>]*title="[^"]+"/s', $content)) {
    $failures[] = "Embedded engine iframe must carry an accessible title.";
}

// 2. Lazy mount
if (!preg_match("/tabBtn\.addEventListener\('shown\.bs\.tab',\s*mount\)/", $content)) {
    $failures[] = "Engine must be lazy-mounted when the Flyer Studio tab is first shown.";
}

// 3. Full-screen escape hatch
if (!preg_match('/<a[^>]*href="\/poster-studio\?app=familyplatform"[^>]*target="_blank"/s', $content)) {
    $failures[] = "A 'Full screen' link to the standalone engine is missing.";
}

// 4. Legacy studio removed (single editor, no duplicate entry points)
foreach (['applyBirthdayPreset', 'renderFullPosterCanvas', 'arRoomModal', 'launchArRoomView', 'updateFamFlyerPreview', 'flyerControlsColumn'] as $legacy) {
    if (str_contains($content, $legacy)) {
        $failures[] = "Legacy in-page studio artefact '{$legacy}' still present; the Hub must host only the Universal Poster Engine.";
    }
}

// 5. Routes
if (!str_contains($router, "'/poster-studio'") || !str_contains($router, 'UniversalPosterStudioController')) {
    $failures[] = "Route GET /poster-studio → UniversalPosterStudioController is not registered.";
}
if (!str_contains($router, "'/api/poster-studio/render'")) {
    $failures[] = "Route /api/poster-studio/render is not registered.";
}

// 6. British English & buzzwords
if (preg_match('/\b(customize|customized|customizing|customizer)\b/i', $content)) {
    $failures[] = "US English spelling 'customize' found; must use British English 'customise'.";
}
if (preg_match('/>\s*Center\s*</i', $content)) {
    $failures[] = "US English 'Center' found in user-facing copy; must use 'Centre'.";
}
if (preg_match('/World-Class\s+Masterpiece|Masterpiece\s+Poster/i', $content)) {
    $failures[] = "'Masterpiece' buzzword copy found in view template.";
}

if (!empty($failures)) {
    echo "❌ SUITE FAILED with " . count($failures) . " issue(s):\n";
    foreach ($failures as $f) {
        echo "   - {$f}\n";
    }
    exit(1);
}

echo "✅ [PASS 1/6] Flyer Studio tab embeds the Universal Poster Engine (/poster-studio?app=familyplatform)\n";
echo "✅ [PASS 2/6] Engine is lazy-mounted on first tab open\n";
echo "✅ [PASS 3/6] Full-screen link to the standalone engine present\n";
echo "✅ [PASS 4/6] Legacy in-page studio fully removed (single editor)\n";
echo "✅ [PASS 5/6] /poster-studio and render API routes registered\n";
echo "✅ [PASS 6/6] British English invariant & zero buzzword copy verified\n";
echo "\n🎉 ALL FLYER STUDIO CHECKS PASSED WITH EXIT CODE 0!\n";
exit(0);
