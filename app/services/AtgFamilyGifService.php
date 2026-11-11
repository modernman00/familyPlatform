<?php

declare(strict_types=1);

namespace App\services;

use InvalidArgumentException;
use RuntimeException;

/**
 * AtgFamilyGifService — Sovereign Brand Animated GIF & Custom Dynasty Synthesizer Service
 *
 * Governed by: Master Engineering Charter (v3.0 Executive Edition) & TAT 2.0
 * Authored by: Advanced Technology Group (ATG Pods 3 & 4)
 * Compliant with: Universal British English, Inviolable Red Surface Rule, Zero External SaaS.
 */
final class AtgFamilyGifService
{
    /** Public web directory for official brand GIFs. */
    public const PUBLIC_WEB_DIR = '/images/brand/gifs';

    /**
     * Official curated brand GIF definitions.
     *
     * @var array<string, array{
     *     key: string,
     *     title: string,
     *     subtitle: string,
     *     category: string,
     *     category_label: string,
     *     emotion: string,
     *     filename: string,
     *     url: string,
     *     alt: string
     * }>
     */
    public const OFFICIAL_GIFS = [
        'atg_heritage_tree_bloom' => [
            'key'            => 'atg_heritage_tree_bloom',
            'title'          => 'Heritage Tree — Roots & Wings',
            'subtitle'       => 'Roots Deep • Love Strong',
            'category'       => 'family_brand',
            'category_label' => 'Family Roots',
            'emotion'        => 'Legacy & Connection',
            'filename'       => 'atg_heritage_tree_bloom.gif',
            'url'            => '/images/brand/gifs/atg_heritage_tree_bloom.gif',
            'alt'            => 'FamilyPlatform Heritage Tree pulsing with golden roots and deep love',
        ],
        'atg_kinship_pulse' => [
            'key'            => 'atg_kinship_pulse',
            'title'          => 'Generations Connected — Kinship Pulse',
            'subtitle'       => 'Family Forever & Always',
            'category'       => 'family_brand',
            'category_label' => 'Kinship',
            'emotion'        => 'Belonging',
            'filename'       => 'atg_kinship_pulse.gif',
            'url'            => '/images/brand/gifs/atg_kinship_pulse.gif',
            'alt'            => 'Kinship pulse connecting generational nodes in FamilyPlatform',
        ],
        'atg_family_reunion_toast' => [
            'key'            => 'atg_family_reunion_toast',
            'title'          => 'Family Reunion Toast — Reunited & Celebrating',
            'subtitle'       => 'Reunited & Celebrating Together',
            'category'       => 'family_brand',
            'category_label' => 'Celebration',
            'emotion'        => 'Joy & Reunion',
            'filename'       => 'atg_family_reunion_toast.gif',
            'url'            => '/images/brand/gifs/atg_family_reunion_toast.gif',
            'alt'            => 'Champagne flutes toasting with golden confetti at Family reunion',
        ],
        'atg_welcome_home_circle' => [
            'key'            => 'atg_welcome_home_circle',
            'title'          => 'Welcome Home — Family Circle',
            'subtitle'       => 'To Our Family Circle',
            'category'       => 'family_brand',
            'category_label' => 'Welcome',
            'emotion'        => 'Warmth & Inclusivity',
            'filename'       => 'atg_welcome_home_circle.gif',
            'url'            => '/images/brand/gifs/atg_welcome_home_circle.gif',
            'alt'            => 'Golden halo portal welcoming members to the Family circle',
        ],
        'atg_family_love_hugs' => [
            'key'            => 'atg_family_love_hugs',
            'title'          => 'Sending Family Love & Warm Hugs',
            'subtitle'       => 'Warmest Hugs Across Every Mile',
            'category'       => 'family_brand',
            'category_label' => 'Love & Hugs',
            'emotion'        => 'Affection & Care',
            'filename'       => 'atg_family_love_hugs.gif',
            'url'            => '/images/brand/gifs/atg_family_love_hugs.gif',
            'alt'            => 'Beating crimson and gold heart with radiant love ripples',
        ],
        'atg_proud_family_applause' => [
            'key'            => 'atg_proud_family_applause',
            'title'          => 'Proud Family Moments — Standing Ovation',
            'subtitle'       => 'Family Champion • Well Done!',
            'category'       => 'family_brand',
            'category_label' => 'Pride & Wins',
            'emotion'        => 'Pride & Celebration',
            'filename'       => 'atg_proud_family_applause.gif',
            'url'            => '/images/brand/gifs/atg_proud_family_applause.gif',
            'alt'            => 'Family champion laurel and fireworks celebrating achievements',
        ],
        'atg_legacy_vault_spark' => [
            'key'            => 'atg_legacy_vault_spark',
            'title'          => 'Family Legacy — Cherished Forever',
            'subtitle'       => 'Cherished Today • Preserved Forever',
            'category'       => 'family_brand',
            'category_label' => 'Heritage & Legacy',
            'emotion'        => 'Reverence & Continuity',
            'filename'       => 'atg_legacy_vault_spark.gif',
            'url'            => '/images/brand/gifs/atg_legacy_vault_spark.gif',
            'alt'            => 'Golden heirloom crest reflecting timeless family memories',
        ],
        'atg_sunday_family_banter' => [
            'key'            => 'atg_sunday_family_banter',
            'title'          => 'Sunday Banter — Laughter & Joy',
            'subtitle'       => 'Laughter That Keeps Us Together',
            'category'       => 'family_brand',
            'category_label' => 'Banter & Laughter',
            'emotion'        => 'Cheer & Humour',
            'filename'       => 'atg_sunday_family_banter.gif',
            'url'            => '/images/brand/gifs/atg_sunday_family_banter.gif',
            'alt'            => 'Joyous animated speech bubbles with heart and smiling expressions',
        ],
    ];

    /**
     * Absolute filesystem path to the project root.
     */
    public static function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Absolute filesystem directory for static brand GIFs.
     */
    public static function brandGifsDir(): string
    {
        $dir = self::projectRoot() . '/public/images/brand/gifs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Absolute filesystem directory for dynamically synthesized GIFs.
     */
    public static function cacheDir(): string
    {
        $dir = self::projectRoot() . '/storage/cache/gifs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Retrieve the full catalog of official FamilyPlatform brand GIFs.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getCatalog(): array
    {
        return self::OFFICIAL_GIFS;
    }

    /**
     * Check if a given key is an allowlisted official brand GIF.
     */
    public static function isOfficialGifKey(string $key): bool
    {
        return isset(self::OFFICIAL_GIFS[$key]);
    }

    /**
     * Retrieve raw GIF89a binary for an official brand GIF.
     * Caches to disk automatically for sub-millisecond future responses.
     */
    public static function getOfficialGifBinary(string $key, bool $forceRegenerate = false): string
    {
        if (!self::isOfficialGifKey($key)) {
            throw new InvalidArgumentException("Unknown brand GIF key: {$key}");
        }

        $filePath = self::brandGifsDir() . '/' . self::OFFICIAL_GIFS[$key]['filename'];

        if (!$forceRegenerate && file_exists($filePath) && filesize($filePath) > 100) {
            $contents = file_get_contents($filePath);
            if ($contents !== false) {
                return $contents;
            }
        }

        $binary = AtgFamilyGifRenderer::renderOfficial($key);

        // Atomic write to disk
        $tmpFile = $filePath . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (file_put_contents($tmpFile, $binary) !== false) {
            rename($tmpFile, $filePath);
        }

        return $binary;
    }

    /**
     * Retrieve raw GIF89a binary for a custom dynasty GIF with caching.
     */
    public static function getCustomGifBinary(
        string $familyName,
        string $caption = '',
        string $theme = 'crimson_gold'
    ): string {
        $cleanName = trim(strip_tags($familyName));
        $cleanCaption = trim(strip_tags($caption));
        $cacheHash = hash('sha256', "dynasty:{$cleanName}|{$cleanCaption}|{$theme}");
        $cacheFile = self::cacheDir() . "/{$cacheHash}.gif";

        if (file_exists($cacheFile) && filesize($cacheFile) > 100) {
            $contents = file_get_contents($cacheFile);
            if ($contents !== false) {
                return $contents;
            }
        }

        $binary = AtgFamilyGifRenderer::renderCustomDynasty($cleanName, $cleanCaption, $theme);

        $tmpFile = $cacheFile . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (file_put_contents($tmpFile, $binary) !== false) {
            rename($tmpFile, $cacheFile);
        }

        return $binary;
    }

    /**
     * Batch pre-generate all official brand GIFs onto disk.
     *
     * @return array<string, array{path: string, size: int, elapsed_ms: float}>
     */
    public static function generateAllOfficialGifs(bool $force = false): array
    {
        $results = [];

        foreach (array_keys(self::OFFICIAL_GIFS) as $key) {
            $t0 = microtime(true);
            $binary = self::getOfficialGifBinary($key, $force);
            $elapsedMs = round((microtime(true) - $t0) * 1000, 2);
            $path = self::brandGifsDir() . '/' . self::OFFICIAL_GIFS[$key]['filename'];

            $results[$key] = [
                'path'       => $path,
                'size'       => strlen($binary),
                'elapsed_ms' => $elapsedMs,
            ];
        }

        return $results;
    }

    /**
     * Export entries formatted for the feed and comment GIF picker (gifCatalog.js).
     *
     * @return array<int, array{id: string, cat: string, title: string, url: string, alt: string}>
     */
    public static function getForGifPicker(): array
    {
        $items = [];
        foreach (self::OFFICIAL_GIFS as $gif) {
            $items[] = [
                'id'    => $gif['key'],
                'cat'   => 'family_brand',
                'title' => $gif['title'],
                'url'   => $gif['url'],
                'alt'   => $gif['alt'],
            ];
        }
        return $items;
    }
}
