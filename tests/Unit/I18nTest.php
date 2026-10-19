<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\classes\I18n;
use PHPUnit\Framework\TestCase;

class I18nTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    public function testDefaultLocaleIsBritishEnglish(): void
    {
        unset($_SESSION['app_locale']);
        unset($_COOKIE['app_locale']);

        $this->assertSame('en', I18n::getLocale());
        $this->assertSame('Home', I18n::trans('nav.home'));
        $this->assertSame('Family Tree', I18n::trans('nav.tree'));
    }

    public function testLocaleSwitchingWithStrictAllowlist(): void
    {
        $this->assertTrue(I18n::setLocale('fr'));
        $this->assertSame('fr', I18n::getLocale());
        $this->assertSame('Accueil', I18n::trans('nav.home'));
        $this->assertSame('Arbre Généalogique', I18n::trans('nav.tree'));

        $this->assertTrue(I18n::setLocale('es'));
        $this->assertSame('es', I18n::getLocale());
        $this->assertSame('Inicio', I18n::trans('nav.home'));
        $this->assertSame('Árbol Genealógico', I18n::trans('nav.tree'));

        // Rejection of malicious / unknown locale
        $this->assertFalse(I18n::setLocale('../../etc/passwd'));
        $this->assertFalse(I18n::setLocale('invalid'));

        // Reset to default
        I18n::setLocale('en');
    }

    public function testGlobalHelperFunction(): void
    {
        I18n::setLocale('en');
        $this->assertSame('Home', __('nav.home'));
        $this->assertSame('Custom Fallback', __('missing.key', 'Custom Fallback'));
    }
}
