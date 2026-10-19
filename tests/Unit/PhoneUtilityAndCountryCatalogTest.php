<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\classes\CountryCatalog;
use App\classes\PhoneUtility;
use PHPUnit\Framework\TestCase;

class PhoneUtilityAndCountryCatalogTest extends TestCase
{
    public function testCountryCatalogRetrievesKeyCountries(): void
    {
        $uk = CountryCatalog::find('United Kingdom');
        $this->assertNotNull($uk);
        $this->assertSame('GB', $uk['code']);
        $this->assertSame('+44', $uk['dial_code']);
        $this->assertSame('🇬🇧', $uk['flag']);

        $ng = CountryCatalog::find('Nigeria');
        $this->assertNotNull($ng);
        $this->assertSame('NG', $ng['code']);
        $this->assertSame('+234', $ng['dial_code']);
        $this->assertSame('🇳🇬', $ng['flag']);

        $us = CountryCatalog::find('US');
        $this->assertNotNull($us);
        $this->assertSame('US', $us['code']);
        $this->assertSame('+1', $us['dial_code']);
        $this->assertSame('🇺🇸', $us['flag']);

        // Check fallback
        $this->assertSame('🌐', CountryCatalog::getFlag('Atlantis'));
        $this->assertSame('', CountryCatalog::getDialCode('Atlantis'));
    }

    public function testPhoneUtilityNormalizesE164Correctly(): void
    {
        // UK with trunk 0
        $this->assertSame('+447123456789', PhoneUtility::toE164('07123 456789', 'United Kingdom'));

        // Nigeria with trunk 0
        $this->assertSame('+2348031234567', PhoneUtility::toE164('0803 123 4567', 'Nigeria'));

        // US with parentheses and dashes
        $this->assertSame('+15551234567', PhoneUtility::toE164('(555) 123-4567', 'United States'));

        // Already with plus
        $this->assertSame('+447123456789', PhoneUtility::toE164('+44 7123 456 789'));

        // 00 international exit code
        $this->assertSame('+447123456789', PhoneUtility::toE164('0044 7123 456 789'));

        // Display formatting
        $this->assertSame('+44 7123 456789', PhoneUtility::formatForDisplay('+447123456789'));
        $this->assertSame('+1 (555) 123-4567', PhoneUtility::formatForDisplay('+15551234567'));
        $this->assertSame('+234 803 123 4567', PhoneUtility::formatForDisplay('+2348031234567'));
    }
}
