<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\classes\CountryCatalog;
use App\classes\I18n;
use App\classes\PhoneUtility;
use PHPUnit\Framework\TestCase;

/**
 * InternationalizationSecurityTest
 *
 * Adversarial Red Team verification (Dr. Viktor Kovács & Marcus Gate).
 * Asserts that internationalization and phone normalizers are impervious
 * to LFI/path traversal, parameter tampering, and malicious payloads.
 */
class InternationalizationSecurityTest extends TestCase
{
    public function testLfiAndPathTraversalInLocaleSwitchingNeutralized(): void
    {
        $maliciousLocales = [
            '../../etc/passwd',
            '..\\..\\windows\\system32',
            '%2e%2e%2f%2e%2e%2f',
            '../../../app/config/init.php',
            '<script>alert(1)</script>',
            "en' OR '1'='1",
            "en\0.php",
            '../../../etc/shadow',
        ];

        foreach ($maliciousLocales as $payload) {
            $result = I18n::setLocale($payload);
            $this->assertFalse($result, "Expected malicious locale '$payload' to be rejected by allowlist");
            $this->assertNotSame($payload, I18n::getLocale());
        }
    }

    public function testPhoneUtilityNeutralizesMaliciousPayloads(): void
    {
        $maliciousPhones = [
            "<script>alert('XSS')</script>" => '',
            "'+OR+1=1--" => '',
            "; DROP TABLE contact; --" => '',
            "07123456789<svg/onload=alert(1)>" => '+447123456789',
            "+234 803 123 4567; rm -rf /" => '+2348031234567',
        ];

        foreach ($maliciousPhones as $payload => $expected) {
            $normalized = PhoneUtility::toE164($payload, 'United Kingdom');
            $this->assertStringNotContainsString('<script>', $normalized);
            $this->assertStringNotContainsString('DROP', $normalized);
            $this->assertStringNotContainsString(';', $normalized);
            if ($expected !== '') {
                $this->assertSame($expected, $normalized);
            }
        }
    }

    public function testCountryCatalogResilienceAgainstMalformedInput(): void
    {
        $this->assertNull(CountryCatalog::find(''));
        $this->assertNull(CountryCatalog::find('   '));
        $this->assertNull(CountryCatalog::find("<script>alert(1)</script>"));
        $this->assertNull(CountryCatalog::find(str_repeat('A', 5000)));

        // Valid aliases still resolve cleanly
        $this->assertSame('GB', CountryCatalog::find('UK')['code'] ?? null);
        $this->assertSame('US', CountryCatalog::find('USA')['code'] ?? null);
        $this->assertSame('AE', CountryCatalog::find('UAE')['code'] ?? null);
    }

    public function testLocaleRouteDispatchSucceedsWithoutNamedParameterError(): void
    {
        // Assert LocaleController method accepts both 'locale' and 'lang' parameters
        $refMethod = new \ReflectionMethod(\App\controller\LocaleController::class, 'switch');
        $params = array_map(fn($p) => $p->getName(), $refMethod->getParameters());
        $this->assertContains('locale', $params);
        $this->assertContains('lang', $params);

        // Assert AltoRouter route exists and maps to LocaleController@switch
        $router = new \AltoRouter();
        require __DIR__ . '/../../app/router/router.php';
        $match = $router->match('/locale/fr', 'GET');
        $this->assertIsArray($match);
        $this->assertSame('App\controller\LocaleController@switch', $match['target']);
        $this->assertArrayHasKey('locale', $match['params']);
        $this->assertSame('fr', $match['params']['locale']);

        // Assert I18n sets and translates French and Spanish accurately
        $this->assertTrue(I18n::setLocale('fr'));
        $this->assertSame('fr', I18n::getLocale());
        $this->assertSame('Accueil', I18n::trans('nav.home'));

        $this->assertTrue(I18n::setLocale('es'));
        $this->assertSame('es', I18n::getLocale());
        $this->assertSame('Inicio', I18n::trans('nav.home'));

        I18n::setLocale('en');
    }
}
