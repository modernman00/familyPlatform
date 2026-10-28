<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\ActivityAffiliateService;
use PHPUnit\Framework\TestCase;

final class ActivityAffiliateServiceTest extends TestCase
{
    public function testBureaucraticUrlsAreDetectedAndRejected(): void
    {
        $this->assertTrue(ActivityAffiliateService::isBureaucraticOrSearchUrl('https://www.gov.uk/search/all?keywords=museums'));
        $this->assertTrue(ActivityAffiliateService::isBureaucraticOrSearchUrl('https://gov.uk/family-activities'));
        $this->assertTrue(ActivityAffiliateService::isBureaucraticOrSearchUrl('https://www.google.com/search?q=test'));
        $this->assertTrue(ActivityAffiliateService::isBureaucraticOrSearchUrl('https://duckduckgo.com/?q=london'));
        $this->assertTrue(ActivityAffiliateService::isBureaucraticOrSearchUrl('https://en.wikipedia.org/wiki/Special:Search'));

        // Legitimate attractions
        $this->assertFalse(ActivityAffiliateService::isBureaucraticOrSearchUrl('https://www.nhm.ac.uk/visit.html'));
        $this->assertFalse(ActivityAffiliateService::isBureaucraticOrSearchUrl('https://www.sciencemuseum.org.uk/'));
        $this->assertFalse(ActivityAffiliateService::isBureaucraticOrSearchUrl(null));
        $this->assertFalse(ActivityAffiliateService::isBureaucraticOrSearchUrl(''));
    }

    public function testCurateAndEnrichOutingsReplacesGovUkWithRealVenuesAndAffiliateLinks(): void
    {
        // Mock raw result containing bureaucratic search baseline
        $rawMock = [
            'success' => true,
            'synthesis' => 'UK family outings baseline research.',
            'citations' => [
                [
                    'title' => 'GOV.UK & UK Official Public Reference Baseline',
                    'snippet' => 'Authoritative UK public guidance and verified baseline reference.',
                    'url' => 'https://www.gov.uk/search/all?keywords=UK+family+weekend+days+out+South+Kensington',
                ],
            ],
        ];

        $outings = ActivityAffiliateService::curateAndEnrichOutings(
            $rawMock,
            'South Kensington London SW7',
            'science museums, child-friendly exhibitions'
        );

        $this->assertNotEmpty($outings);
        $this->assertLessThanOrEqual(4, count($outings));

        foreach ($outings as $o) {
            // Must NOT contain gov.uk
            $this->assertStringNotContainsString('gov.uk', $o['url']);
            $this->assertStringNotContainsString('Official Public Reference', $o['title']);

            // Must contain affiliate monetization
            $this->assertTrue($o['is_affiliate']);
            $this->assertNotEmpty($o['url']);
            $this->assertStringContainsString('FAMILYPLATFORM', $o['url']);
            $this->assertNotEmpty($o['price_guide']);
            $this->assertNotEmpty($o['partner_name']);
            $this->assertNotEmpty($o['affiliate_label']);
            $this->assertNotEmpty($o['disclosure']);
            $this->assertStringContainsString('FamilyPlatform', $o['disclosure']);
        }

        // Should contain authentic South Kensington attractions like Science Museum or Natural History Museum
        $titles = array_column($outings, 'title');
        $allTitles = implode(' ', $titles);
        $this->assertTrue(
            str_contains($allTitles, 'Science Museum') || str_contains($allTitles, 'Natural History') || str_contains($allTitles, 'V&A')
        );
    }

    public function testBuildAffiliateDataGeneratesCorrectPartnerNetworks(): void
    {
        // 1. Holiday Staycation -> Booking.com
        $holiday = ActivityAffiliateService::buildAffiliateData('Cotswold Farm Cottage', 'Holiday & Staycation', 'Cotswolds');
        $this->assertSame('Booking.com', $holiday['partner_name']);
        $this->assertStringContainsString('booking.com', $holiday['url']);
        $this->assertStringContainsString('aid=FAMILYPLATFORM', $holiday['url']);

        // 2. Weekend Drive -> Virgin Experience Days
        $drive = ActivityAffiliateService::buildAffiliateData('Scenic Country Road', 'Weekend Drive', 'Peak District');
        $this->assertSame('Virgin Experience Days', $drive['partner_name']);
        $this->assertStringContainsString('virginexperiencedays.co.uk', $drive['url']);
        $this->assertStringContainsString('aff=FAMILYPLATFORM', $drive['url']);

        // 3. Barbecue & Picnic -> National Trust
        $bbq = ActivityAffiliateService::buildAffiliateData('Richmond Park Deer Lawns', 'Barbecue & Picnic', 'Richmond London');
        $this->assertSame('National Trust & Days Out', $bbq['partner_name']);
        $this->assertStringContainsString('nationaltrust.org.uk', $bbq['url']);
        $this->assertStringContainsString('campaign=FAMILYPLATFORM', $bbq['url']);

        // 4. Museum & Attractions -> GetYourGuide or Viator
        $museum = ActivityAffiliateService::buildAffiliateData('Natural History Museum Dinosaur Hall', 'Museum', 'London');
        $this->assertSame('GetYourGuide', $museum['partner_name']);
        $this->assertStringContainsString('getyourguide.com', $museum['url']);
        $this->assertStringContainsString('partner_id=FAMILYPLATFORM', $museum['url']);
    }

    public function testAffiliateDisclosureAdheresToBritishEnglishAndAsaRules(): void
    {
        $disclosure = ActivityAffiliateService::getAffiliateDisclosure();
        $this->assertStringContainsString('commission', $disclosure);
        $this->assertStringContainsString('partner links', $disclosure);
        $this->assertStringContainsString('FamilyPlatform', $disclosure);
    }
}
