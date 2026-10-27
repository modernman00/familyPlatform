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
        $this->assertSame('Home', trans('nav.home'));
        $this->assertSame('Custom Fallback', __('missing.key', 'Custom Fallback'));
        $this->assertSame('Custom Fallback', trans('missing.key', [], 'Custom Fallback'));

        // Test replacement
        $this->assertSame('Home', __('nav.home', 'Home', ['any' => 'thing']));
        $this->assertSame('Home', trans('nav.home', ['any' => 'thing']));
    }

    public function testGetDictionaryAndClientSerialization(): void
    {
        $dictEn = I18n::getDictionary('en');
        $this->assertIsArray($dictEn);
        $this->assertArrayHasKey('nav.home', $dictEn);
        $this->assertSame('Home', $dictEn['nav.home']);
        $this->assertSame('My Page', $dictEn['nav.my_page']);

        $dictFr = I18n::getDictionary('fr');
        $this->assertIsArray($dictFr);
        $this->assertSame('Accueil', $dictFr['nav.home']);
        $this->assertSame('Ma Page', $dictFr['nav.my_page']);
        $this->assertSame('Annuaire', $dictFr['nav.directory']);

        $dictEs = I18n::getDictionary('es');
        $this->assertIsArray($dictEs);
        $this->assertSame('Inicio', $dictEs['nav.home']);
        $this->assertSame('Mi Página', $dictEs['nav.my_page']);
        $this->assertSame('Directorio', $dictEs['nav.directory']);

        // Test transArray alias
        $this->assertSame($dictFr, I18n::transArray('fr'));
    }

    public function testNavigationAndAuthTranslationsComprehensive(): void
    {
        I18n::setLocale('fr');
        $this->assertSame('Ma Page', __('nav.my_page'));
        $this->assertSame('Studio', __('nav.studio'));
        $this->assertSame('Journal', __('nav.journal'));
        $this->assertSame('Reels', __('nav.reels'));
        $this->assertSame('Annuaire', __('nav.directory'));
        $this->assertSame('Inviter des proches', __('nav.invite_others'));
        $this->assertSame('Notifications', __('nav.notifications'));
        $this->assertSame('Paramètres', __('nav.settings'));
        $this->assertSame('Déconnexion', __('nav.logout'));

        I18n::setLocale('es');
        $this->assertSame('Mi Página', __('nav.my_page'));
        $this->assertSame('Estudio', __('nav.studio'));
        $this->assertSame('Diario', __('nav.journal'));
        $this->assertSame('Reels', __('nav.reels'));
        $this->assertSame('Directorio', __('nav.directory'));
        $this->assertSame('Invitar a otros', __('nav.invite_others'));
        $this->assertSame('Notificaciones', __('nav.notifications'));
        $this->assertSame('Configuración', __('nav.settings'));
        $this->assertSame('Cerrar Sesión', __('nav.logout'));

        // Reset to en
        I18n::setLocale('en');
    }

    public function testFeedAndHeritageContentTranslations(): void
    {
        // English defaults
        I18n::setLocale('en');
        $this->assertSame('Share a moment with your family...', __('feed.share_prompt'));
        $this->assertSame('Then & Now', __('feed.then_now'));
        $this->assertSame('Family Dynasty Hub', __('heritage.dynasty_hub'));
        $this->assertSame('All Family Memories', __('feed.all_memories'));
        $this->assertSame('Tagged With You', __('feed.tagged_with_you'));
        $this->assertSame('Like', __('feed.like'));
        $this->assertSame('Comment', __('feed.comment'));
        $this->assertSame('Share', __('feed.share'));
        $this->assertSame('VERIFIED', __('profile.verified'));
        $this->assertSame('Upcoming Events', __('events.upcoming'));
        $this->assertSame('Suggested Kin & In-Laws', __('kinship.suggested_kin'));

        // French
        I18n::setLocale('fr');
        $this->assertSame('Partagez un moment avec votre famille...', __('feed.share_prompt'));
        $this->assertSame('Avant & Maintenant', __('feed.then_now'));
        $this->assertSame('Centre de Dynastie Familiale', __('heritage.dynasty_hub'));
        $this->assertSame('Tous les souvenirs de famille', __('feed.all_memories'));
        $this->assertSame('Identifié(e) avec vous', __('feed.tagged_with_you'));
        $this->assertSame("J'aime", __('feed.like'));
        $this->assertSame('Commenter', __('feed.comment'));
        $this->assertSame('Partager', __('feed.share'));
        $this->assertSame('VÉRIFIÉ', __('profile.verified'));
        $this->assertSame('Événements à venir', __('events.upcoming'));
        $this->assertSame('Proches suggérés & belle-famille', __('kinship.suggested_kin'));

        // Spanish
        I18n::setLocale('es');
        $this->assertSame('Comparte un momento con tu familia...', __('feed.share_prompt'));
        $this->assertSame('Antes & Ahora', __('feed.then_now'));
        $this->assertSame('Centro de Dinastía Familiar', __('heritage.dynasty_hub'));
        $this->assertSame('Todos los recuerdos familiares', __('feed.all_memories'));
        $this->assertSame('Etiquetado contigo', __('feed.tagged_with_you'));
        $this->assertSame('Me gusta', __('feed.like'));
        $this->assertSame('Comentar', __('feed.comment'));
        $this->assertSame('Compartir', __('feed.share'));
        $this->assertSame('VERIFICADO', __('profile.verified'));
        $this->assertSame('Próximos Eventos', __('events.upcoming'));
        $this->assertSame('Parientes sugeridos y familia política', __('kinship.suggested_kin'));

        // Reset
        I18n::setLocale('en');
    }
}
