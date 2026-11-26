<?php
declare(strict_types=1);

namespace App\services;

/**
 * Lightweight short-TTL read-through micro-cache for expensive, non-critical
 * profile page widgets (APCu primary with sys_get_temp_dir JSON fallback).
 * Automatically bypassed under CLI so PHPUnit/cron always observe live DB state.
 */
final class ProfileMicroCache
{
    private const CACHE_PREFIX = 'fp_pmc_v1_';

    /**
     * @template T
     * @param string $key
     * @param int $ttlSeconds
     * @param callable(): T $loader
     * @return T
     */
    public static function remember(string $key, int $ttlSeconds, callable $loader): mixed
    {
        if (PHP_SAPI === 'cli' || $ttlSeconds <= 0) {
            return $loader();
        }

        $safeKey = self::CACHE_PREFIX . preg_replace('/[^A-Za-z0-9_.-]/', '_', $key);

        if (function_exists('apcu_enabled') && apcu_enabled()) {
            $hit = false;
            $cached = apcu_fetch($safeKey, $hit);
            if ($hit) {
                return $cached;
            }
            $value = $loader();
            apcu_store($safeKey, $value, $ttlSeconds);
            return $value;
        }

        $file = sys_get_temp_dir() . '/' . $safeKey . '.json';
        if (is_file($file)) {
            $mtime = @filemtime($file);
            if ($mtime !== false && (time() - $mtime) < $ttlSeconds) {
                $raw = @file_get_contents($file);
                if (is_string($raw) && $raw !== '') {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded) && array_key_exists('v', $decoded)) {
                        return $decoded['v'];
                    }
                }
            }
        }

        $value = $loader();
        $encoded = json_encode(['v' => $value]);
        if (is_string($encoded)) {
            @file_put_contents($file, $encoded, LOCK_EX);
        }

        return $value;
    }

    public static function forget(string $key): void
    {
        $safeKey = self::CACHE_PREFIX . preg_replace('/[^A-Za-z0-9_.-]/', '_', $key);
        if (function_exists('apcu_enabled') && apcu_enabled()) {
            apcu_delete($safeKey);
        }
        $file = sys_get_temp_dir() . '/' . $safeKey . '.json';
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
