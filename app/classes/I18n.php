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
     * Active in-memory locale for this request.
     */
    private static ?string $currentLocale = null;

    /**
     * Session key indicating an explicit diaspora locale was chosen by the user.
     */
    public const EXPLICIT_LOCALE_SESSION_KEY = 'explicit_locale_chosen';

    /**
     * Validate whether a locale code is in the supported allowlist.
     */
    public static function isValidLocale(string $locale): bool
    {
        $clean = strtolower(trim($locale));
        $supported = parent::getSupportedLocales();
        return isset($supported[$clean]);
    }

    /**
     * Get the active application locale.
     * Guarantees British English ('en') is the strict default unless explicitly controlled by locale.
     *
     * @return string
     */
    public static function getLocale(): string
    {
        if (self::$currentLocale !== null && self::isValidLocale(self::$currentLocale)) {
            return self::$currentLocale;
        }

        // 1. Explicit query parameter override (?locale=fr or ?lang=es)
        $queryLocale = $_GET['locale'] ?? ($_GET['lang'] ?? null);
        if (is_string($queryLocale) && self::isValidLocale($queryLocale)) {
            $clean = strtolower(trim($queryLocale));
            self::setLocale($clean);
            return self::$currentLocale = $clean;
        }

        // 2. Explicit session preference
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['app_locale'])) {
            $sessLocale = (string)$_SESSION['app_locale'];
            // Only honour session locale if it is the default English or the user has explicitly chosen a different language
            if (self::isValidLocale($sessLocale)) {
                if ($sessLocale === self::DEFAULT_LOCALE || (!empty($_SESSION[self::EXPLICIT_LOCALE_SESSION_KEY]) && $_SESSION[self::EXPLICIT_LOCALE_SESSION_KEY] === true)) {
                    return self::$currentLocale = $sessLocale;
                }
                // Otherwise ignore and fall back to default English
            }
        }

        // 3. Persistent cookie — only honor if marked as explicitly chosen or if English
        if (!empty($_COOKIE['app_locale'])) {
            $cookieLocale = (string)$_COOKIE['app_locale'];
            if (self::isValidLocale($cookieLocale)) {
                if ($cookieLocale === self::DEFAULT_LOCALE) {
                    return self::$currentLocale = self::DEFAULT_LOCALE;
                }
                // For non-English locales: only adopt from cookie if session confirms explicit choice
                if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION[self::EXPLICIT_LOCALE_SESSION_KEY])) {
                    return self::$currentLocale = $cookieLocale;
                }
            }
        }

        // 4. Default invariant: British English ('en')
        return self::$currentLocale = self::DEFAULT_LOCALE;
    }

    /**
     * Set the active application locale with strict allowlist validation.
     *
     * @param string $locale
     * @return bool
     */
    public static function setLocale(string $locale): bool
    {
        $clean = strtolower(trim($locale));
        if (!self::isValidLocale($clean)) {
            return false;
        }

        self::$currentLocale = $clean;
        parent::setLocale($clean);

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['app_locale'] = $clean;
            $_SESSION[self::EXPLICIT_LOCALE_SESSION_KEY] = ($clean !== self::DEFAULT_LOCALE);
        }

        if (!headers_sent()) {
            setcookie('app_locale', $clean, [
                'expires' => time() + 31536000,
                'path' => '/',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }

        return true;
    }

    /**
     * Reset active locale to default British English ('en') and purge non-English cookies.
     */
    public static function resetToDefault(): void
    {
        self::$currentLocale = self::DEFAULT_LOCALE;
        parent::reset();
        parent::setLocale(self::DEFAULT_LOCALE);
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION['app_locale']);
            unset($_SESSION[self::EXPLICIT_LOCALE_SESSION_KEY]);
        }
        if (!headers_sent()) {
            setcookie('app_locale', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Reset in-memory state (useful for test isolation).
     */
    public static function reset(): void
    {
        self::$currentLocale = null;
        parent::reset();
    }

    /**
     * Check if a translation key exists in either active locale or default fallback.
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        $locale = self::getLocale();
        $dict = self::getDictionary($locale);
        if (isset($dict[$key])) {
            return true;
        }
        if ($locale !== self::DEFAULT_LOCALE) {
            $defaultDict = self::getDictionary(self::DEFAULT_LOCALE);
            return isset($defaultDict[$key]);
        }
        return false;
    }

    /**
     * Translate a given key into the current locale.
     *
     * @param string $key
     * @param array<string, string|int|float> $replace
     * @param string|null $fallback
     * @return string
     */
    public static function trans(string $key, array $replace = [], ?string $fallback = null): string
    {
        $locale = self::getLocale();
        $dict = self::getDictionary($locale);
        $line = $dict[$key] ?? null;

        if ($line === null && $locale !== self::DEFAULT_LOCALE) {
            $defaultDict = self::getDictionary(self::DEFAULT_LOCALE);
            $line = $defaultDict[$key] ?? null;
        }

        if ($line === null) {
            $line = $fallback ?? $key;
        }

        if (!empty($replace)) {
            foreach ($replace as $k => $v) {
                $line = str_replace([':' . $k, '{' . $k . '}'], (string)$v, $line);
            }
        }

        return $line;
    }

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
