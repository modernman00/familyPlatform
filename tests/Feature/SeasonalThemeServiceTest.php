<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\SeasonalThemeService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SeasonalThemeServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SeasonalThemeService::clearCache();
    }

    protected function tearDown(): void
    {
        unset($_GET['seasonal_preview']);
        SeasonalThemeService::clearCache();
        parent::tearDown();
    }

    public function testStandardThemeOnNonFestivalDate(): void
    {
        $res = SeasonalThemeService::resolve(new DateTimeImmutable('2026-08-12 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_STANDARD, $res['mode']);
        $this->assertFalse($res['is_active']);
        $this->assertSame('none', $res['particle_mode']);
    }

    public function testChristmasWindowStartsNovember20AndRunsThroughDecember30(): void
    {
        $nov19 = SeasonalThemeService::resolve(new DateTimeImmutable('2026-11-19 23:59:59'));
        $this->assertSame(SeasonalThemeService::MODE_STANDARD, $nov19['mode']);

        $nov20 = SeasonalThemeService::resolve(new DateTimeImmutable('2026-11-20 00:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_CHRISTMAS, $nov20['mode']);
        $this->assertTrue($nov20['is_active']);
        $this->assertSame('snow', $nov20['particle_mode']);

        $dec25 = SeasonalThemeService::resolve(new DateTimeImmutable('2026-12-25 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_CHRISTMAS, $dec25['mode']);
        $this->assertSame('Merry Christmas', $dec25['badge_text']);

        $dec30 = SeasonalThemeService::resolve(new DateTimeImmutable('2026-12-30 23:59:59'));
        $this->assertSame(SeasonalThemeService::MODE_CHRISTMAS, $dec30['mode']);
    }

    public function testNewYearWindowRunsDecember31ThroughJanuary2(): void
    {
        $dec31 = SeasonalThemeService::resolve(new DateTimeImmutable('2026-12-31 00:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_NEW_YEAR, $dec31['mode']);
        $this->assertSame('New Year’s Eve', $dec31['badge_text']);

        $jan1 = SeasonalThemeService::resolve(new DateTimeImmutable('2027-01-01 09:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_NEW_YEAR, $jan1['mode']);
        $this->assertSame('Happy New Year 2027', $jan1['badge_text']);

        $jan3 = SeasonalThemeService::resolve(new DateTimeImmutable('2027-01-03 00:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_STANDARD, $jan3['mode']);
    }

    public function testMajorFestivalsActivateInTheir2To3DayWindows(): void
    {
        // Lunar New Year 2026 (Feb 17 -> Feb 16..18)
        $lny = SeasonalThemeService::resolve(new DateTimeImmutable('2026-02-17 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_LUNAR_NEW_YEAR, $lny['mode']);
        $this->assertSame('lantern', $lny['particle_mode']);

        // Valentine's Day (Feb 13..14)
        $val = SeasonalThemeService::resolve(new DateTimeImmutable('2026-02-14 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_VALENTINES, $val['mode']);
        $this->assertSame('hearts', $val['particle_mode']);

        // UK Mothering Sunday 2026 (Easter is Apr 5 -> Mothering Sunday is Mar 15 -> Mar 14..15)
        $mom = SeasonalThemeService::resolve(new DateTimeImmutable('2026-03-15 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_MOTHERS_DAY, $mom['mode']);

        // Eid al-Fitr 2026 (Mar 20 -> Mar 20..22)
        $eidFitr = SeasonalThemeService::resolve(new DateTimeImmutable('2026-03-20 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_EID_FITR, $eidFitr['mode']);
        $this->assertSame('crescent', $eidFitr['particle_mode']);

        // Easter 2026 (Apr 5 -> Apr 4..6)
        $easter = SeasonalThemeService::resolve(new DateTimeImmutable('2026-04-05 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_EASTER, $easter['mode']);
        $this->assertSame('petals', $easter['particle_mode']);

        // Eid al-Adha 2026 (May 27 -> May 27..29)
        $eidAdha = SeasonalThemeService::resolve(new DateTimeImmutable('2026-05-28 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_EID_ADHA, $eidAdha['mode']);

        // Father's Day 2026 (3rd Sunday of June -> Jun 21 -> Jun 20..21)
        $dad = SeasonalThemeService::resolve(new DateTimeImmutable('2026-06-21 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_FATHERS_DAY, $dad['mode']);

        // Diwali 2026 (Nov 8 -> Nov 7..9)
        $diwali = SeasonalThemeService::resolve(new DateTimeImmutable('2026-11-08 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_DIWALI, $diwali['mode']);
        $this->assertSame('diya', $diwali['particle_mode']);
    }

    public function testPrecedenceAndDualEidOccurrenceIn2033(): void
    {
        // 2033-12-23 is inside Christmas window AND Eid al-Fitr; Christmas takes precedence
        $dec23 = SeasonalThemeService::resolve(new DateTimeImmutable('2033-12-23 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_CHRISTMAS, $dec23['mode']);

        // 2033-01-02 is New Year (takes precedence over Eid al-Fitr Jan 2), while 2033-01-03 is Eid al-Fitr
        $jan2 = SeasonalThemeService::resolve(new DateTimeImmutable('2033-01-02 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_NEW_YEAR, $jan2['mode']);
        $jan3 = SeasonalThemeService::resolve(new DateTimeImmutable('2033-01-03 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_EID_FITR, $jan3['mode']);
    }

    public function testQueryPreviewIsBlockedInProductionAndAllowedInLocal(): void
    {
        $origEnv = $_ENV['APP_ENV'] ?? null;
        $_GET['seasonal_preview'] = 'diwali';

        $_ENV['APP_ENV'] = 'production';
        $prodRes = SeasonalThemeService::resolve(new DateTimeImmutable('2026-08-12 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_STANDARD, $prodRes['mode']);
        $this->assertFalse($prodRes['is_preview']);

        $_ENV['APP_ENV'] = 'local';
        $localRes = SeasonalThemeService::resolve(new DateTimeImmutable('2026-08-12 12:00:00'));
        $this->assertSame(SeasonalThemeService::MODE_DIWALI, $localRes['mode']);
        $this->assertTrue($localRes['is_preview']);

        if ($origEnv === null) {
            unset($_ENV['APP_ENV']);
        } else {
            $_ENV['APP_ENV'] = $origEnv;
        }
    }
}
