<?php

declare(strict_types=1);

namespace App\classes;

/**
 * PhoneUtility
 *
 * Provides standardized international E.164 phone normalization, validation,
 * and display formatting.
 *
 * Implements resilient parsing across global phone formats while preserving
 * backwards compatibility for existing UK local formats.
 */
class PhoneUtility
{
    /**
     * Normalize a phone number to standard E.164 format (+[country code][number]).
     *
     * @param string $phone Raw phone string (e.g. "07123 456789", "+234 803 123 4567", "(555) 012-3456")
     * @param string|null $country Optional country name or ISO code to resolve missing prefix
     * @return string Sanitized E.164 string or cleanly stripped numeric string
     */
    public static function toE164(string $phone, ?string $country = null): string
    {
        $raw = trim(strip_tags($phone));
        if ($raw === '') {
            return '';
        }

        // Handle 00 international exit code replacement: 0044... -> +44...
        if (str_starts_with($raw, '00')) {
            $raw = '+' . substr($raw, 2);
        }

        $hasPlus = str_starts_with($raw, '+');

        // Strip everything except digits
        $digitsOnly = preg_replace('/[^\d]/', '', $raw) ?? '';
        if ($digitsOnly === '') {
            return '';
        }

        // If the number originally started with +, return +digits
        if ($hasPlus) {
            return '+' . $digitsOnly;
        }

        // If country is specified, try resolving dial code
        if ($country !== null && trim($country) !== '') {
            $dialCode = CountryCatalog::getDialCode($country);
            if ($dialCode !== '') {
                $cleanDialDigits = preg_replace('/[^\d]/', '', $dialCode) ?? '';

                // If user entered number already starting with the dial code digits (e.g. 2348031234567 or 447123456789)
                if (str_starts_with($digitsOnly, $cleanDialDigits) && strlen($digitsOnly) > strlen($cleanDialDigits) + 6) {
                    return '+' . $digitsOnly;
                }

                // If user entered number with national trunk prefix '0' (e.g. 08031234567 in Nigeria or 07123456789 in UK)
                if (str_starts_with($digitsOnly, '0')) {
                    $withoutZero = substr($digitsOnly, 1);
                    return '+' . $cleanDialDigits . $withoutZero;
                }

                // If user entered local number without trunk 0 (e.g. 8031234567)
                return '+' . $cleanDialDigits . $digitsOnly;
            }
        }

        // Default heuristic: If 11 digits starting with 07 (Standard UK mobile), assume UK +44
        if (strlen($digitsOnly) === 11 && str_starts_with($digitsOnly, '07')) {
            return '+44' . substr($digitsOnly, 1);
        }

        // If no country dial code could be determined, return clean digits with + if >= 10 digits
        return (strlen($digitsOnly) >= 10) ? '+' . $digitsOnly : $digitsOnly;
    }

    /**
     * Format an E.164 or legacy phone number cleanly for human display.
     *
     * @param string|null $phone
     * @return string
     */
    public static function formatForDisplay(?string $phone): string
    {
        if ($phone === null || trim($phone) === '') {
            return '';
        }

        $normalized = self::toE164($phone);
        if ($normalized === '') {
            return $phone;
        }

        // If E.164 UK: +44 7123 456789
        if (str_starts_with($normalized, '+44') && strlen($normalized) === 13) {
            return '+44 ' . substr($normalized, 3, 4) . ' ' . substr($normalized, 7);
        }

        // If E.164 US/Canada: +1 (555) 123-4567
        if (str_starts_with($normalized, '+1') && strlen($normalized) === 12) {
            return '+1 (' . substr($normalized, 2, 3) . ') ' . substr($normalized, 5, 3) . '-' . substr($normalized, 8);
        }

        // If E.164 Nigeria: +234 803 123 4567
        if (str_starts_with($normalized, '+234') && strlen($normalized) === 14) {
            return '+234 ' . substr($normalized, 4, 3) . ' ' . substr($normalized, 7, 3) . ' ' . substr($normalized, 10);
        }

        return $normalized;
    }
}
