<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\GiftWishlistAffiliateService;
use PHPUnit\Framework\TestCase;

class GiftWishlistAffiliateServiceTest extends TestCase
{
    public function testRetailPartnersAreValidAndComprehensive(): void
    {
        $partners = GiftWishlistAffiliateService::getRetailPartners();
        $this->assertGreaterThanOrEqual(15, count($partners));

        foreach ($partners as $partner) {
            $this->assertNotEmpty($partner['id']);
            $this->assertNotEmpty($partner['name']);
            $this->assertNotEmpty($partner['category']);
            $this->assertStringStartsWith('https://', $partner['base_url']);
            $this->assertStringStartsWith('https://', $partner['search_template']);
            $this->assertStringContainsString('{query}', $partner['search_template']);
        }
    }

    public function testPartnerCategoriesAndCuratedIdeasAreValid(): void
    {
        $categories = GiftWishlistAffiliateService::getPartnerCategories();
        $this->assertNotEmpty($categories);
        $this->assertSame(array_values(array_unique($categories)), $categories);

        $ideas = GiftWishlistAffiliateService::getCuratedGiftIdeas();
        $this->assertGreaterThanOrEqual(6, count($ideas));
        foreach ($ideas as $idea) {
            $this->assertNotEmpty($idea['title']);
            $this->assertNotEmpty($idea['occasion']);
            $this->assertStringStartsWith('https://', $idea['url']);
        }
    }

    public function testBuildQuickShopLinksEncodesSafely(): void
    {
        $this->assertSame([], GiftWishlistAffiliateService::buildQuickShopLinksForItem('   '));

        $links = GiftWishlistAffiliateService::buildQuickShopLinksForItem('Sony Headphones & Case');
        $this->assertCount(3, $links);
        foreach ($links as $link) {
            $this->assertStringStartsWith('https://', $link['url']);
            $this->assertStringContainsString('Sony%20Headphones%20%26%20Case', $link['url']);
        }
    }
}
