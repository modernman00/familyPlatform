<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;

/**
 * High-Performance Caching and Decoupling Engine for Living Heritage Family Trees
 * Part of Phase 2 Living Heritage Tree Acceleration (TAT Mandate: Olutobi, David & Victor)
 */
final class FamilyTreeCacheService
{
    private const CACHE_TTL_SECONDS = 3600; // 1 hour TTL

    /**
     * Resolve the filesystem cache directory for family trees.
     */
    public static function getCacheDir(): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/cache/family_trees';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Build a sanitized cache file path for a given family code and user ID.
     */
    private static function getCacheFilePath(string $familyCode, string $userId): string
    {
        $safeFamily = preg_replace('/[^A-Za-z0-9_-]/', '', $familyCode);
        $safeUser = preg_replace('/[^A-Za-z0-9_-]/', '', $userId);
        return self::getCacheDir() . "/tree_{$safeFamily}_{$safeUser}.json";
    }

    /**
     * Check if a family has at least one graph node already initialized.
     * Used as a fast guard to bypass synchronous legacy imports and graph healing on GET.
     */
    public static function isFamilyInitialized(string $familyCode): bool
    {
        $clean = trim($familyCode);
        if ($clean === '') {
            return false;
        }

        try {
            $stmt = Db::connect2()->prepare("SELECT 1 FROM family_nodes WHERE family_code = ? LIMIT 1");
            $stmt->execute([$clean]);
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Retrieve cached 6-generation graph data if valid and fresh.
     *
     * @return array<string, mixed>|null
     */
    public static function get(string $familyCode, string $userId): ?array
    {
        $file = self::getCacheFilePath($familyCode, $userId);
        if (!is_file($file)) {
            return null;
        }

        $mtime = @filemtime($file);
        if ($mtime === false || (time() - $mtime) > self::CACHE_TTL_SECONDS) {
            @unlink($file);
            return null;
        }

        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        return (is_array($data) && !empty($data['nodes'])) ? $data : null;
    }

    /**
     * Atomically cache the 6-generation graph data.
     *
     * @param array<string, mixed> $graphData
     */
    public static function put(string $familyCode, string $userId, array $graphData): void
    {
        if (empty($graphData['nodes'])) {
            return;
        }

        $file = self::getCacheFilePath($familyCode, $userId);
        $tmp = $file . '.' . bin2hex(random_bytes(6)) . '.tmp';

        $payload = json_encode($graphData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return;
        }

        if (@file_put_contents($tmp, $payload, LOCK_EX) !== false) {
            @rename($tmp, $file);
        } else {
            @unlink($tmp);
        }
    }

    /**
     * Invalidate all cached tree graphs for a given family code upon any tree mutation.
     */
    public static function invalidateFamily(string $familyCode): void
    {
        $safeFamily = preg_replace('/[^A-Za-z0-9_-]/', '', $familyCode);
        if ($safeFamily === '') {
            return;
        }

        $dir = self::getCacheDir();
        $files = glob("{$dir}/tree_{$safeFamily}_*.json");
        if (is_array($files)) {
            foreach ($files as $f) {
                if (is_file($f)) {
                    @unlink($f);
                }
            }
        }
    }
}
