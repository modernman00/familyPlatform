<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Src\EcosystemPromoService;

final class EcosystemPromoBannerTest extends TestCase
{
    public function testPromoDeckRotatesAcrossAllEligibleSisterAppsExcludingFamilyPlatform(): void
    {
        $service = new EcosystemPromoService('test_secret_2026');
        $deck = $service->getPromoDeckForContext('familyplatform', 'family_feed');

        $this->assertGreaterThanOrEqual(8, count($deck));

        $targetApps = array_column($deck, 'target_app');
        $this->assertNotContains('familyplatform', $targetApps);
        $this->assertContains('partyplatform', $targetApps);
        $this->assertContains('idecide', $targetApps);
        $this->assertContains('iaccount', $targetApps);
        $this->assertContains('scoretenant', $targetApps);
        $this->assertContains('execmind', $targetApps);
        $this->assertContains('loaneasyfinance', $targetApps);
        $this->assertContains('bcp', $targetApps);
    }

    public function testSuccessiveFamilyFeedImpressionsRotateWithoutBeingLockedToPartyPlatform(): void
    {
        $service = new EcosystemPromoService('test_secret_2026');
        $seenApps = [];

        for ($i = 0; $i < 5; $i++) {
            $promo = $service->getPromoForContext('familyplatform', 'family_feed', [
                'mock_day_of_year' => 120,
                'mock_session_offset' => $i,
            ]);
            $this->assertNotNull($promo);
            $this->assertNotSame('familyplatform', $promo['target_app']);
            $seenApps[] = $promo['target_app'];
        }

        $this->assertGreaterThanOrEqual(4, count(array_unique($seenApps)));
    }

    public function testPartyPlatformCatalogEntryUsesOfficialLogoAndMarketableCopy(): void
    {
        $catalog = EcosystemPromoService::getCatalog();
        $this->assertArrayHasKey('partyplatform', $catalog);

        $party = $catalog['partyplatform'];
        $this->assertSame('/public/images/ecosystem/partyplatform.png', $party['logo_url']);
        $this->assertStringContainsString('pp_star_gold', (string) $party['svg_icon']);
        $this->assertSame('Host Show-Stopping Parties Guests Talk About All Year.', $party['headline']);
        $this->assertIsArray($party['value_pills']);
        $this->assertCount(4, $party['value_pills']);
    }
}
