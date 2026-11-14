<?php
declare(strict_types=1);

/**
 * Portfolio-Wide Synchroniser for the 10-Stage Autonomous Software Engine (v5.0)
 * Commissioned by: Wally (CEO) | Executed by: Jumoke Olaogun & TAT
 *
 * Propagates across all 8 portfolio applications + shared-lib & shared-js-lib:
 *   1. AGENTS.md (v5.0 — 10-Stage Autonomous Engine)
 *   2. CLAUDE.md (v5.0 — Claude Code & Agent Operations Guide)
 *   3. docs/governance/PROMPT_CHEATSHEET.md (v5.0)
 *   4. scripts/dom_audit.js (Stage 8 Browser DOM & Contrast Auditor)
 *   5. scripts/autonomous_gate.sh (Stage 7 & 9 Fast Iterative Finisher Gate)
 */

$sitesDir = '/Users/waleolaogun/Sites';
$sourceApp = 'familyPlatform';
$apps = [
    'bcp',
    'execmindapp',
    'familyPlatform',
    'iaccountapp',
    'idecideapp',
    'loaneasyfinance',
    'partyplatform',
    'scoretenant',
    'shared-lib',
    'shared-js-lib',
];

$filesToSync = [
    'AGENTS.md' => 0644,
    'CLAUDE.md' => 0644,
    'docs/governance/PROMPT_CHEATSHEET.md' => 0644,
    'scripts/dom_audit.js' => 0644,
    'scripts/autonomous_gate.sh' => 0755,
];

$sourceBase = "{$sitesDir}/{$sourceApp}";
$syncedApps = 0;

foreach ($apps as $app) {
    $targetBase = "{$sitesDir}/{$app}";
    if (!is_dir($targetBase)) {
        echo "⚠️  Skipping missing directory: {$targetBase}\n";
        continue;
    }

    foreach ($filesToSync as $relPath => $chmod) {
        $srcFile = "{$sourceBase}/{$relPath}";
        $dstFile = "{$targetBase}/{$relPath}";

        $dstDir = dirname($dstFile);
        if (!is_dir($dstDir)) {
            mkdir($dstDir, 0755, true);
        }

        if ($srcFile !== $dstFile) {
            copy($srcFile, $dstFile);
        }
        chmod($dstFile, $chmod);
    }

    $syncedApps++;
    echo "✅ Synced 10-Stage Autonomous Engine (v5.0: AGENTS.md + CLAUDE.md + scripts) -> {$app}\n";
}

echo "\n🎯 Portfolio Sync Complete: {$syncedApps}/" . count($apps) . " repositories updated.\n";
