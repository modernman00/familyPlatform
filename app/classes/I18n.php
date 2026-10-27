<?php

declare(strict_types=1);

namespace App\classes;

use Src\I18n as BaseI18n;

/**
 * Class I18n
 *
 * Extends the centralized modernman00/shared-lib I18n engine.
 * Preserves backwards compatibility for existing App\classes\I18n references
 * while inheriting cross-platform cascading localization.
 */
class I18n extends BaseI18n
{
    /**
     * Backwards-compatible alias constant for legacy code.
     */
    public const SUPPORTED_LOCALES = BaseI18n::DEFAULT_LOCALES;

    /**
     * Get the full loaded translation dictionary for a locale (defaults to active locale).
     *
     * @param string|null $locale
     * @return array<string, string>
     */
    public static function getDictionary(?string $locale = null): array
    {
        $loc = $locale !== null ? strtolower(trim($locale)) : self::getLocale();
        $clean = preg_replace('/[^a-z]/', '', $loc) ?: self::DEFAULT_LOCALE;

        $dict = [];

        // 1. Load shared-lib base dictionary
        $baseFile = dirname(__DIR__, 2) . '/vendor/modernman00/shared-lib/resources/lang/' . $clean . '.php';
        if (file_exists($baseFile)) {
            $data = require $baseFile;
            if (is_array($data)) {
                $dict = $data;
            }
        }

        // 2. Load host application dictionary
        $appPath = self::getAppDictionaryPath();
        if ($appPath !== null) {
            $appFile = $appPath . '/' . $clean . '.php';
            if (file_exists($appFile)) {
                $data = require $appFile;
                if (is_array($data)) {
                    $dict = array_merge($dict, $data);
                }
            }
        }

        return $dict;
    }

    /**
     * Alias for getDictionary to support client-side serialization.
     *
     * @param string|null $locale
     * @return array<string, string>
     */
    public static function transArray(?string $locale = null): array
    {
        return self::getDictionary($locale);
    }
}
