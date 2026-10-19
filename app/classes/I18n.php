<?php

declare(strict_types=1);

namespace App\classes;

/**
 * I18n
 *
 * Lightweight, zero-npm internationalization and translation engine.
 * Provides dictionary-based translations for global diaspora members.
 *
 * Defaults to British English (en-GB) while allowing on-the-fly switching
 * to French (fr) and Spanish (es).
 */
class I18n
{
    /**
     * Supported locales with names and flags.
     *
     * @var array<string, array{name: string, native: string, flag: string}>
     */
    public const SUPPORTED_LOCALES = [
        'en' => ['name' => 'English (UK)', 'native' => 'English', 'flag' => '🇬🇧'],
        'fr' => ['name' => 'French', 'native' => 'Français', 'flag' => '🇫🇷'],
        'es' => ['name' => 'Spanish', 'native' => 'Español', 'flag' => '🇪🇸'],
    ];

    public const DEFAULT_LOCALE = 'en';

    /**
     * Active in-memory locale (overrides or falls back when session/cookie unavailable).
     */
    private static ?string $activeLocale = null;

    /**
     * In-memory cache of loaded translation dictionaries.
     *
     * @var array<string, array<string, string>>
     */
    private static array $dictionaries = [];

    /**
     * Get the active application locale.
     */
    public static function getLocale(): string
    {
        if (self::$activeLocale !== null && isset(self::SUPPORTED_LOCALES[self::$activeLocale])) {
            return self::$activeLocale;
        }

        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['app_locale'])) {
            $sessLocale = (string)$_SESSION['app_locale'];
            if (isset(self::SUPPORTED_LOCALES[$sessLocale])) {
                return $sessLocale;
            }
        }

        if (!empty($_COOKIE['app_locale'])) {
            $cookieLocale = (string)$_COOKIE['app_locale'];
            if (isset(self::SUPPORTED_LOCALES[$cookieLocale])) {
                return $cookieLocale;
            }
        }

        return self::DEFAULT_LOCALE;
    }

    /**
     * Set the active application locale with strict allowlist validation.
     */
    public static function setLocale(string $locale): bool
    {
        $clean = strtolower(trim($locale));
        if (!isset(self::SUPPORTED_LOCALES[$clean])) {
            return false;
        }

        self::$activeLocale = $clean;

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['app_locale'] = $clean;
        }

        // Set persistent cookie for 1 year
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
     * Get all supported locales.
     *
     * @return array<string, array{name: string, native: string, flag: string}>
     */
    public static function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    /**
     * Translate a given key into the current locale.
     *
     * @param string $key Dotted translation key (e.g. 'nav.home', 'auth.register')
     * @param array<string, string|int|float> $replace Placeholder replacements (e.g. ['name' => 'Wally'])
     * @param string|null $fallback Fallback text if key is missing in dictionary
     * @return string
     */
    public static function trans(string $key, array $replace = [], ?string $fallback = null): string
    {
        $locale = self::getLocale();
        self::loadDictionary($locale);

        $line = self::$dictionaries[$locale][$key] ?? null;

        // Fall back to English dictionary if not found in active locale
        if ($line === null && $locale !== self::DEFAULT_LOCALE) {
            self::loadDictionary(self::DEFAULT_LOCALE);
            $line = self::$dictionaries[self::DEFAULT_LOCALE][$key] ?? null;
        }

        // If still null, return explicit fallback or key
        if ($line === null) {
            $line = $fallback ?? $key;
        }

        // Perform variable interpolation: :name or {name}
        if (!empty($replace)) {
            foreach ($replace as $k => $v) {
                $line = str_replace([':' . $k, '{' . $k . '}'], (string)$v, $line);
            }
        }

        return $line;
    }

    /**
     * Load the translation dictionary for a specific locale.
     */
    private static function loadDictionary(string $locale): void
    {
        if (isset(self::$dictionaries[$locale])) {
            return;
        }

        $clean = preg_replace('/[^a-z]/', '', strtolower($locale));
        $filePath = __DIR__ . '/../../resources/lang/' . $clean . '.php';

        if (file_exists($filePath)) {
            $data = require $filePath;
            self::$dictionaries[$locale] = is_array($data) ? $data : [];
        } else {
            self::$dictionaries[$locale] = [];
        }
    }
}
