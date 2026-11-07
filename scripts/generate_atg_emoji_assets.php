<?php

declare(strict_types=1);

/**
 * Regenerate static living-emoji SVG assets in public/img/emoji/.
 *
 * The feed, composer and AR viewer load emojis as <img src="/img/emoji/{id}.svg">,
 * so every catalog emoji AND every skin-tone variant (e.g. family_wave_dark_3d)
 * needs a file on disk. Re-run after changing AtgEmojiService artwork:
 *
 *   php scripts/generate_atg_emoji_assets.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \App\services\AtgEmojiService();
$outDir = __DIR__ . '/../public/img/emoji';

if (!is_dir($outDir) && !mkdir($outDir, 0755, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

$written = 0;
foreach ($service->allEmojiAssetIds() as $id) {
    if (!preg_match('/^[a-z0-9_]+_3d$/', $id)) {
        continue;
    }
    $svg = $service->renderSvgEmoji($id, 320);
    file_put_contents($outDir . '/' . $id . '.svg', $svg);
    $written++;
}

echo "Wrote {$written} emoji SVG assets to public/img/emoji/\n";
