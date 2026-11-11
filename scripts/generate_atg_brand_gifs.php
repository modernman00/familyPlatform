<?php

declare(strict_types=1);

/**
 * Regenerate static FamilyPlatform Brand Animated GIFs in public/images/brand/gifs/.
 *
 * Authored by: Advanced Technology Group (ATG Pods 3 & 4)
 * Governed by: Master Engineering Charter (v3.0 Executive Edition) & TAT 2.0
 *
 * Usage:
 *   php scripts/generate_atg_brand_gifs.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\services\AtgFamilyGifService;

echo "=================================================================\n";
echo "🎨  ATG SOVEREIGN BRAND GIF ASSET GENERATOR (ATG PODS 3 & 4)\n";
echo "    App: FamilyPlatform | Output: public/images/brand/gifs/\n";
echo "=================================================================\n\n";

$t0 = microtime(true);
$results = AtgFamilyGifService::generateAllOfficialGifs(true);
$totalElapsed = round((microtime(true) - $t0) * 1000, 2);

$totalBytes = 0;
foreach ($results as $key => $meta) {
    $kb = round($meta['size'] / 1024, 1);
    $totalBytes += $meta['size'];
    echo sprintf("  ✅ [%-26s] -> %6.1f KB (%6.1f ms) [%s]\n", $key, $kb, $meta['elapsed_ms'], basename($meta['path']));
}

$totalKb = round($totalBytes / 1024, 1);
echo "\n-----------------------------------------------------------------\n";
echo sprintf("Summary: %d Official Brand GIFs synthesized (%s KB total) in %s ms.\n", count($results), number_format($totalKb, 1), number_format($totalElapsed, 1));
echo "=================================================================\n";
