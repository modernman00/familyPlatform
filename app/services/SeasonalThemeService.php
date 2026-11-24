<?php

declare(strict_types=1);

namespace App\services;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * SeasonalThemeService — Multi-Festival Dynamic Date-Driven Design Resolver
 *
 * Automatically activates major family & cultural festivals:
 *  - New Year ("new_year"): 31 December – 2 January
 *  - Christmas ("christmas"): 20 November – 30 December
 *  - Lunar New Year ("lunar_new_year"): 3-day window (Eve, Day 1, Day 2; 2026–2035)
 *  - Valentine’s Day ("valentines"): 13–14 February (2-day window)
 *  - Mother’s Day / UK Mothering Sunday ("mothers_day"): 2-day weekend (Sat–Sun, 3 weeks before Easter)
 *  - Easter ("easter"): 3-day window (Easter Saturday – Easter Monday via Gregorian Computus)
 *  - Father’s Day ("fathers_day"): 2-day weekend (Sat–Sun of 3rd Sunday in June)
 *  - Eid al-Fitr ("eid_fitr"): 3-day celebration (2026–2035 table)
 *  - Eid al-Adha ("eid_adha"): 3-day celebration (2026–2035 table)
 *  - Diwali ("diwali"): 3-day Festival of Lights (Eve, Main Day, +1; 2026–2035 table)
 *  - Standard ("standard"): All other dates
 *
 * Pure, stateless, zero-DB, zero-SaaS, and strictly typed for PHPStan Level 8.
 */
final class SeasonalThemeService
{
    public const MODE_STANDARD = 'standard';
    public const MODE_CHRISTMAS = 'christmas';
    public const MODE_NEW_YEAR = 'new_year';
    public const MODE_LUNAR_NEW_YEAR = 'lunar_new_year';
    public const MODE_VALENTINES = 'valentines';
    public const MODE_MOTHERS_DAY = 'mothers_day';
    public const MODE_EASTER = 'easter';
    public const MODE_FATHERS_DAY = 'fathers_day';
    public const MODE_EID_FITR = 'eid_fitr';
    public const MODE_EID_ADHA = 'eid_adha';
    public const MODE_DIWALI = 'diwali';

    private const ALLOWED_MODES = [
        self::MODE_STANDARD,
        self::MODE_CHRISTMAS,
        self::MODE_NEW_YEAR,
        self::MODE_LUNAR_NEW_YEAR,
        self::MODE_VALENTINES,
        self::MODE_MOTHERS_DAY,
        self::MODE_EASTER,
        self::MODE_FATHERS_DAY,
        self::MODE_EID_FITR,
        self::MODE_EID_ADHA,
        self::MODE_DIWALI,
    ];

    /** @var array<int, list<string>> Lunar New Year Day 1 (2026–2035) */
    private const LUNAR_NEW_YEAR_DATES = [
        2026 => ['2026-02-17'],
        2027 => ['2027-02-06'],
        2028 => ['2028-01-26'],
        2029 => ['2029-02-13'],
        2030 => ['2030-02-03'],
        2031 => ['2031-01-23'],
        2032 => ['2032-02-11'],
        2033 => ['2033-01-31'],
        2034 => ['2034-02-19'],
        2035 => ['2035-02-08'],
    ];

    /** @var array<int, list<string>> Eid al-Fitr Day 1 (2026–2035, includes dual-occurrence years 2033–2034) */
    private const EID_FITR_DATES = [
        2026 => ['2026-03-20'],
        2027 => ['2027-03-09'],
        2028 => ['2028-02-27'],
        2029 => ['2029-02-14'],
        2030 => ['2030-02-04'],
        2031 => ['2031-01-24'],
        2032 => ['2032-01-14'],
        2033 => ['2033-01-02', '2033-12-23'],
        2034 => ['2034-12-12'],
        2035 => ['2035-12-01'],
    ];

    /** @var array<int, list<string>> Eid al-Adha Day 1 (2026–2035) */
    private const EID_ADHA_DATES = [
        2026 => ['2026-05-27'],
        2027 => ['2027-05-16'],
        2028 => ['2028-05-05'],
        2029 => ['2029-04-24'],
        2030 => ['2030-04-13'],
        2031 => ['2031-04-02'],
        2032 => ['2032-03-22'],
        2033 => ['2033-03-11'],
        2034 => ['2034-03-01'],
        2035 => ['2035-02-18'],
    ];

    /** @var array<int, list<string>> Diwali Main Day (2026–2035) */
    private const DIWALI_DATES = [
        2026 => ['2026-11-08'],
        2027 => ['2027-10-29'],
        2028 => ['2028-10-17'],
        2029 => ['2029-11-05'],
        2030 => ['2030-10-26'],
        2031 => ['2031-11-14'],
        2032 => ['2032-11-02'],
        2033 => ['2033-10-22'],
        2034 => ['2034-11-10'],
        2035 => ['2035-10-30'],
    ];

    /** @var array<string, mixed>|null */
    private static ?array $cachedCurrent = null;

    /**
     * Resolve the active seasonal theme configuration for the given instant.
     *
     * @return array{
     *   mode: string,
     *   is_active: bool,
     *   is_preview: bool,
     *   label: string,
     *   badge_text: string,
     *   sub_greeting: string,
     *   icon: string,
     *   ends_at: string|null,
     *   ends_label: string|null,
     *   days_remaining: int,
     *   accent_primary: string,
     *   accent_secondary: string,
     *   accent_gold: string,
     *   css_class: string,
     *   data_attr: string,
     *   particle_mode: string
     * }
     */
    public static function resolve(?DateTimeInterface $now = null, ?string $preview = null): array
    {
        $tz = new DateTimeZone(date_default_timezone_get() ?: 'Europe/London');
        $date = $now !== null
            ? DateTimeImmutable::createFromInterface($now)->setTimezone($tz)
            : new DateTimeImmutable('now', $tz);

        $isLocal = (($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: '') === 'local');
        $resolvedPreview = null;

        if ($preview !== null && in_array($preview, self::ALLOWED_MODES, true)) {
            $resolvedPreview = $preview;
        } elseif ($isLocal && isset($_GET['seasonal_preview']) && is_string($_GET['seasonal_preview'])) {
            $candidate = strtolower(trim($_GET['seasonal_preview']));
            if (in_array($candidate, self::ALLOWED_MODES, true)) {
                $resolvedPreview = $candidate;
            }
        }

        $todayStr = $date->format('Y-m-d');
        $month = (int) $date->format('n');
        $day = (int) $date->format('j');
        $year = (int) $date->format('Y');

        if ($resolvedPreview !== null) {
            return self::buildPayload($resolvedPreview, $date, true, $tz);
        }

        // Strict precedence order:
        // 1. New Year (31 Dec – 2 Jan)
        if (($month === 12 && $day === 31) || ($month === 1 && $day <= 2)) {
            return self::buildPayload(self::MODE_NEW_YEAR, $date, false, $tz);
        }

        // 2. Christmas (20 Nov – 30 Dec)
        if (($month === 11 && $day >= 20) || $month === 12) {
            return self::buildPayload(self::MODE_CHRISTMAS, $date, false, $tz);
        }

        // 3. Lunar New Year (3-day window: Eve, Day 1, Day 2)
        if (self::matchesOffsetWindow($todayStr, self::LUNAR_NEW_YEAR_DATES, $year, -1, 1, $tz)) {
            return self::buildPayload(self::MODE_LUNAR_NEW_YEAR, $date, false, $tz);
        }

        // 4. Eid al-Fitr (3-day window: Day 1, Day 2, Day 3)
        if (self::matchesOffsetWindow($todayStr, self::EID_FITR_DATES, $year, 0, 2, $tz)) {
            return self::buildPayload(self::MODE_EID_FITR, $date, false, $tz);
        }

        // 5. Eid al-Adha (3-day window: Day 1, Day 2, Day 3)
        if (self::matchesOffsetWindow($todayStr, self::EID_ADHA_DATES, $year, 0, 2, $tz)) {
            return self::buildPayload(self::MODE_EID_ADHA, $date, false, $tz);
        }

        // 6. Diwali (3-day window: Eve, Main Day, +1)
        if (self::matchesOffsetWindow($todayStr, self::DIWALI_DATES, $year, -1, 1, $tz)) {
            return self::buildPayload(self::MODE_DIWALI, $date, false, $tz);
        }

        // 7. Easter (3-day window: Easter Saturday – Easter Monday)
        $easterSunday = self::computeEasterSunday($year, $tz);
        $easterSat = $easterSunday->modify('-1 day')->format('Y-m-d');
        $easterMon = $easterSunday->modify('+1 day')->format('Y-m-d');
        if ($todayStr >= $easterSat && $todayStr <= $easterMon) {
            return self::buildPayload(self::MODE_EASTER, $date, false, $tz);
        }

        // 8. Valentine's Day (13–14 Feb)
        if ($month === 2 && ($day === 13 || $day === 14)) {
            return self::buildPayload(self::MODE_VALENTINES, $date, false, $tz);
        }

        // 9. UK Mothering Sunday (2-day window: Sat–Sun, 3 weeks before Easter)
        $motheringSun = $easterSunday->modify('-21 days');
        $motheringSat = $motheringSun->modify('-1 day')->format('Y-m-d');
        if ($todayStr >= $motheringSat && $todayStr <= $motheringSun->format('Y-m-d')) {
            return self::buildPayload(self::MODE_MOTHERS_DAY, $date, false, $tz);
        }

        // 10. Father's Day (2-day window: Sat–Sun of 3rd Sunday in June)
        $fathersSun = self::computeFathersDay($year, $tz);
        $fathersSat = $fathersSun->modify('-1 day')->format('Y-m-d');
        if ($todayStr >= $fathersSat && $todayStr <= $fathersSun->format('Y-m-d')) {
            return self::buildPayload(self::MODE_FATHERS_DAY, $date, false, $tz);
        }

        return self::buildPayload(self::MODE_STANDARD, $date, false, $tz);
    }

    /**
     * Per-request memoized resolver for Blade layouts and partials.
     *
     * @return array<string, mixed>
     */
    public static function current(): array
    {
        if (self::$cachedCurrent === null) {
            self::$cachedCurrent = self::resolve();
        }
        return self::$cachedCurrent;
    }

    public static function clearCache(): void
    {
        self::$cachedCurrent = null;
    }

    /**
     * @param array<int, list<string>> $table
     */
    private static function matchesOffsetWindow(
        string $todayStr,
        array $table,
        int $year,
        int $startOffsetDays,
        int $endOffsetDays,
        DateTimeZone $tz
    ): bool {
        foreach ([$year - 1, $year, $year + 1] as $checkYear) {
            foreach ($table[$checkYear] ?? [] as $anchorStr) {
                $anchor = new DateTimeImmutable($anchorStr . ' 00:00:00', $tz);
                $winStart = $anchor->modify(sprintf('%+d days', $startOffsetDays))->format('Y-m-d');
                $winEnd = $anchor->modify(sprintf('%+d days', $endOffsetDays))->format('Y-m-d');
                if ($todayStr >= $winStart && $todayStr <= $winEnd) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Pure PHP Anonymous Gregorian Easter Computus.
     */
    private static function computeEasterSunday(int $year, DateTimeZone $tz): DateTimeImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return new DateTimeImmutable(sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day), $tz);
    }

    private static function computeFathersDay(int $year, DateTimeZone $tz): DateTimeImmutable
    {
        $june1 = new DateTimeImmutable(sprintf('%04d-06-01 00:00:00', $year), $tz);
        $dow = (int) $june1->format('w'); // 0 = Sunday
        $firstSundayOffset = $dow === 0 ? 0 : (7 - $dow);
        return $june1->modify(sprintf('+%d days', $firstSundayOffset + 14));
    }

    /**
     * @return array{
     *   mode: string,
     *   is_active: bool,
     *   is_preview: bool,
     *   label: string,
     *   badge_text: string,
     *   sub_greeting: string,
     *   icon: string,
     *   ends_at: string|null,
     *   ends_label: string|null,
     *   days_remaining: int,
     *   accent_primary: string,
     *   accent_secondary: string,
     *   accent_gold: string,
     *   css_class: string,
     *   data_attr: string,
     *   particle_mode: string
     * }
     */
    private static function buildPayload(
        string $mode,
        DateTimeImmutable $date,
        bool $isPreview,
        DateTimeZone $tz
    ): array {
        $month = (int) $date->format('n');
        $day = (int) $date->format('j');
        $year = (int) $date->format('Y');

        if ($mode === self::MODE_CHRISTMAS) {
            $endDate = new DateTimeImmutable(sprintf('%04d-12-30 23:59:59', $year), $tz);
            $daysRemaining = self::calcDaysRemaining($date, $endDate);

            if ($month === 12 && ($day === 25 || $day === 26)) {
                $badgeText = 'Merry Christmas';
                $subGreeting = 'Warm Christmas wishes across your family circle';
            } elseif ($month === 12 && $day === 24) {
                $badgeText = 'Christmas Eve';
                $subGreeting = 'Cosy festive wishes as Christmas Eve arrives';
            } elseif ($month === 12 && $day >= 27) {
                $badgeText = 'Festive Season';
                $subGreeting = 'Warm festive wishes across your family circle';
            } else {
                $badgeText = 'Season’s Greetings';
                $subGreeting = 'Warm festive wishes across your family circle';
            }

            return [
                'mode' => self::MODE_CHRISTMAS,
                'is_active' => true,
                'is_preview' => $isPreview,
                'label' => 'Festive Christmas',
                'badge_text' => $badgeText,
                'sub_greeting' => $subGreeting,
                'icon' => 'bi-snow2',
                'ends_at' => $endDate->format('Y-m-d'),
                'ends_label' => '30 Dec',
                'days_remaining' => $daysRemaining,
                'accent_primary' => '#991b1b',
                'accent_secondary' => '#14532d',
                'accent_gold' => '#d4af37',
                'css_class' => 'seasonal-theme-christmas',
                'data_attr' => self::MODE_CHRISTMAS,
                'particle_mode' => 'snow',
            ];
        }

        if ($mode === self::MODE_NEW_YEAR) {
            $targetYear = $month >= 11 ? $year + 1 : $year;
            $endDate = new DateTimeImmutable(sprintf('%04d-01-02 23:59:59', $targetYear), $tz);
            $daysRemaining = self::calcDaysRemaining($date, $endDate);

            if ($month === 12 && $day === 31) {
                $badgeText = 'New Year’s Eve';
                $subGreeting = sprintf('Counting down to %d with your family circle', $targetYear);
            } else {
                $badgeText = sprintf('Happy New Year %d', $targetYear);
                $subGreeting = 'Wishing your family health, joy & prosperity';
            }

            return [
                'mode' => self::MODE_NEW_YEAR,
                'is_active' => true,
                'is_preview' => $isPreview,
                'label' => 'New Year Celebration',
                'badge_text' => $badgeText,
                'sub_greeting' => $subGreeting,
                'icon' => 'bi-stars',
                'ends_at' => $endDate->format('Y-m-d'),
                'ends_label' => '2 Jan',
                'days_remaining' => $daysRemaining,
                'accent_primary' => '#1e1b4b',
                'accent_secondary' => '#b45309',
                'accent_gold' => '#fbbf24',
                'css_class' => 'seasonal-theme-new-year',
                'data_attr' => self::MODE_NEW_YEAR,
                'particle_mode' => 'sparkle',
            ];
        }

        $catalog = [
            self::MODE_LUNAR_NEW_YEAR => [
                'label' => 'Lunar New Year',
                'badge_text' => 'Happy Lunar New Year',
                'sub_greeting' => 'Wishing your family good fortune, harmony & joy',
                'icon' => 'bi-brightness-high-fill',
                'accent_primary' => '#b91c1c',
                'accent_secondary' => '#b45309',
                'accent_gold' => '#fbbf24',
                'css_class' => 'seasonal-theme-lunar-new-year',
                'particle_mode' => 'lantern',
                'days' => 3,
            ],
            self::MODE_VALENTINES => [
                'label' => 'Valentine’s Day',
                'badge_text' => 'Happy Valentine’s Day',
                'sub_greeting' => 'Celebrating love, devotion & cherished family bonds',
                'icon' => 'bi-heart-fill',
                'accent_primary' => '#be123c',
                'accent_secondary' => '#9f1239',
                'accent_gold' => '#fda4af',
                'css_class' => 'seasonal-theme-valentines',
                'particle_mode' => 'hearts',
                'days' => 2,
            ],
            self::MODE_MOTHERS_DAY => [
                'label' => 'Mother’s Day',
                'badge_text' => 'Happy Mother’s Day',
                'sub_greeting' => 'Honouring mothers, grandmothers & matriarchs today',
                'icon' => 'bi-flower1',
                'accent_primary' => '#9d174d',
                'accent_secondary' => '#7e22ce',
                'accent_gold' => '#f9a8d4',
                'css_class' => 'seasonal-theme-mothers-day',
                'particle_mode' => 'hearts',
                'days' => 2,
            ],
            self::MODE_EASTER => [
                'label' => 'Happy Easter',
                'badge_text' => 'Happy Easter',
                'sub_greeting' => 'Wishing your family renewal, hope & springtime warmth',
                'icon' => 'bi-sun-fill',
                'accent_primary' => '#6d28d9',
                'accent_secondary' => '#15803d',
                'accent_gold' => '#fbbf24',
                'css_class' => 'seasonal-theme-easter',
                'particle_mode' => 'petals',
                'days' => 3,
            ],
            self::MODE_FATHERS_DAY => [
                'label' => 'Father’s Day',
                'badge_text' => 'Happy Father’s Day',
                'sub_greeting' => 'Celebrating fathers, grandfathers & family mentors',
                'icon' => 'bi-award-fill',
                'accent_primary' => '#1e3a8a',
                'accent_secondary' => '#0f766e',
                'accent_gold' => '#fbbf24',
                'css_class' => 'seasonal-theme-fathers-day',
                'particle_mode' => 'petals',
                'days' => 2,
            ],
            self::MODE_EID_FITR => [
                'label' => 'Eid al-Fitr',
                'badge_text' => 'Eid Mubarak',
                'sub_greeting' => 'Wishing your family peace, blessings & joyful gatherings',
                'icon' => 'bi-moon-stars-fill',
                'accent_primary' => '#065f46',
                'accent_secondary' => '#047857',
                'accent_gold' => '#fbbf24',
                'css_class' => 'seasonal-theme-eid',
                'particle_mode' => 'crescent',
                'days' => 3,
            ],
            self::MODE_EID_ADHA => [
                'label' => 'Eid al-Adha',
                'badge_text' => 'Eid al-Adha Mubarak',
                'sub_greeting' => 'Warm wishes of gratitude, generosity & family unity',
                'icon' => 'bi-moon-stars-fill',
                'accent_primary' => '#0f766e',
                'accent_secondary' => '#065f46',
                'accent_gold' => '#fbbf24',
                'css_class' => 'seasonal-theme-eid',
                'particle_mode' => 'crescent',
                'days' => 3,
            ],
            self::MODE_DIWALI => [
                'label' => 'Diwali',
                'badge_text' => 'Happy Diwali',
                'sub_greeting' => 'May the Festival of Lights brighten your family home',
                'icon' => 'bi-lamp-fill',
                'accent_primary' => '#7c2d12',
                'accent_secondary' => '#581c87',
                'accent_gold' => '#fbbf24',
                'css_class' => 'seasonal-theme-diwali',
                'particle_mode' => 'diya',
                'days' => 3,
            ],
        ];

        if (isset($catalog[$mode])) {
            $meta = $catalog[$mode];
            return [
                'mode' => $mode,
                'is_active' => true,
                'is_preview' => $isPreview,
                'label' => $meta['label'],
                'badge_text' => $meta['badge_text'],
                'sub_greeting' => $meta['sub_greeting'],
                'icon' => $meta['icon'],
                'ends_at' => $date->format('Y-m-d'),
                'ends_label' => null,
                'days_remaining' => $meta['days'],
                'accent_primary' => $meta['accent_primary'],
                'accent_secondary' => $meta['accent_secondary'],
                'accent_gold' => $meta['accent_gold'],
                'css_class' => $meta['css_class'],
                'data_attr' => $mode,
                'particle_mode' => $meta['particle_mode'],
            ];
        }

        return [
            'mode' => self::MODE_STANDARD,
            'is_active' => false,
            'is_preview' => $isPreview,
            'label' => 'Standard',
            'badge_text' => '',
            'sub_greeting' => '',
            'icon' => '',
            'ends_at' => null,
            'ends_label' => null,
            'days_remaining' => 0,
            'accent_primary' => '#a31621',
            'accent_secondary' => '#8a1019',
            'accent_gold' => '#f59e0b',
            'css_class' => '',
            'data_attr' => self::MODE_STANDARD,
            'particle_mode' => 'none',
        ];
    }

    private static function calcDaysRemaining(DateTimeImmutable $date, DateTimeImmutable $endDate): int
    {
        $todayStart = $date->setTime(0, 0, 0);
        $endStart = $endDate->setTime(0, 0, 0);
        return $todayStart <= $endStart
            ? ((int) $todayStart->diff($endStart)->days) + 1
            : 1;
    }
}
