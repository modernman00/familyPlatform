<?php

declare(strict_types=1);

namespace App\services;

/**
 * GiftWishlistAffiliateService
 *
 * Provides curated UK & international retail affiliate partners, occasion-matched
 * gift ideas, and partner quick-search links for Family Gift Wishlists.
 *
 * Compliant with UK Advertising Standards Authority (ASA) and CMA affiliate disclosure rules.
 */
final class GiftWishlistAffiliateService
{
    private const DEFAULT_PID = 'FAMILYPLATFORM';

    /**
     * Curated affiliate retail partners grouped by shopping category.
     *
     * @return list<array{
     *     id: string,
     *     name: string,
     *     category: string,
     *     tagline: string,
     *     badge: string,
     *     base_url: string,
     *     search_template: string,
     *     accent_color: string
     * }>
     */
    public static function getRetailPartners(): array
    {
        $amazonTag = rawurlencode((string) ($_ENV['AMAZON_AFFILIATE_TAG'] ?? 'familyplatf0e-21'));
        $awinRef = rawurlencode((string) ($_ENV['AWIN_AFFILIATE_ID'] ?? self::DEFAULT_PID));
        $pid = rawurlencode(self::DEFAULT_PID);

        return [
            [
                'id' => 'amazon-uk',
                'name' => 'Amazon UK',
                'category' => 'Everything & Tech',
                'tagline' => 'Fast Prime delivery on tech, books, toys & home.',
                'badge' => 'Top Pick',
                'base_url' => "https://www.amazon.co.uk/?tag={$amazonTag}",
                'search_template' => "https://www.amazon.co.uk/s?k={query}&tag={$amazonTag}",
                'accent_color' => '#ff9900',
            ],
            [
                'id' => 'john-lewis',
                'name' => 'John Lewis & Partners',
                'category' => 'Department Store',
                'tagline' => 'Quality homeware, electronics, fashion & nursery gifts.',
                'badge' => 'Trusted UK',
                'base_url' => "https://www.johnlewis.com/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.johnlewis.com/search?search-term={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#0f766e',
            ],
            [
                'id' => 'argos',
                'name' => 'Argos',
                'category' => 'Toys & Tech',
                'tagline' => 'Same-day click & collect for toys, gaming & gadgets.',
                'badge' => 'Same-Day',
                'base_url' => "https://www.argos.co.uk/?utm_source={$pid}&utm_medium=affiliate&awc={$awinRef}",
                'search_template' => "https://www.argos.co.uk/search/{query}/?utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#dc2626',
            ],
            [
                'id' => 'currys',
                'name' => 'Currys',
                'category' => 'Everything & Tech',
                'tagline' => 'Laptops, headphones, TVs & smart home tech.',
                'badge' => 'Tech',
                'base_url' => "https://www.currys.co.uk/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.currys.co.uk/search?q={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#7c3aed',
            ],
            [
                'id' => 'apple',
                'name' => 'Apple Store',
                'category' => 'Everything & Tech',
                'tagline' => 'iPad, AirPods, Apple Watch & official accessories.',
                'badge' => 'Official',
                'base_url' => "https://www.apple.com/uk/store?afid={$pid}",
                'search_template' => "https://www.apple.com/uk/search/{query}?afid={$pid}",
                'accent_color' => '#334155',
            ],
            [
                'id' => 'boots',
                'name' => 'Boots',
                'category' => 'Fashion & Beauty',
                'tagline' => 'Fragrance gift sets, skincare & wellness favourites.',
                'badge' => 'Beauty',
                'base_url' => "https://www.boots.com/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.boots.com/sitesearch?searchTerm={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#1d4ed8',
            ],
            [
                'id' => 'marks-spencer',
                'name' => 'Marks & Spencer',
                'category' => 'Food, Flowers & Cards',
                'tagline' => 'Luxury hampers, flowers, wine & classic clothing.',
                'badge' => 'Hampers',
                'base_url' => "https://www.marksandspencer.com/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.marksandspencer.com/MSFindItemsByKeyword?searchTerm={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#0f172a',
            ],
            [
                'id' => 'selfridges',
                'name' => 'Selfridges',
                'category' => 'Fashion & Beauty',
                'tagline' => 'Designer gifts, luxury beauty & special occasion treats.',
                'badge' => 'Luxury',
                'base_url' => "https://www.selfridges.com/GB/en/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.selfridges.com/GB/en/cat/?freeText={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#ca8a04',
            ],
            [
                'id' => 'notonthehighstreet',
                'name' => 'Not On The High Street',
                'category' => 'Personalised & Home',
                'tagline' => 'Thoughtful personalised gifts from small UK makers.',
                'badge' => 'Personalised',
                'base_url' => "https://www.notonthehighstreet.com/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.notonthehighstreet.com/search?term={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#0284c7',
            ],
            [
                'id' => 'etsy',
                'name' => 'Etsy UK',
                'category' => 'Personalised & Home',
                'tagline' => 'Handmade keepsakes, custom art & bespoke family gifts.',
                'badge' => 'Handmade',
                'base_url' => "https://www.etsy.com/uk/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.etsy.com/uk/search?q={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#ea580c',
            ],
            [
                'id' => 'lego',
                'name' => 'LEGO® Official',
                'category' => 'Toys & Tech',
                'tagline' => 'Creator, Technic, Star Wars & family building sets.',
                'badge' => 'All Ages',
                'base_url' => "https://www.lego.com/en-gb?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.lego.com/en-gb/search?q={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#b91c1c',
            ],
            [
                'id' => 'smyths-toys',
                'name' => 'Smyths Toys',
                'category' => 'Toys & Tech',
                'tagline' => 'Kids’ bikes, board games, dolls & outdoor play.',
                'badge' => 'Kids',
                'base_url' => "https://www.smythstoys.com/uk/en-gb?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.smythstoys.com/uk/en-gb/search/?text={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#2563eb',
            ],
            [
                'id' => 'waterstones',
                'name' => 'Waterstones',
                'category' => 'Books & Stationery',
                'tagline' => 'Bestselling books, signed editions, puzzles & games.',
                'badge' => 'Books',
                'base_url' => "https://www.waterstones.com/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.waterstones.com/books/search/term/{query}?utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#0369a1',
            ],
            [
                'id' => 'virgin-experience',
                'name' => 'Virgin Experience Days',
                'category' => 'Experiences',
                'tagline' => 'Afternoon teas, spa days, supercar drives & family days out.',
                'badge' => 'Experiences',
                'base_url' => "https://www.virginexperiencedays.co.uk/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.virginexperiencedays.co.uk/search?q={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#be123c',
            ],
            [
                'id' => 'hotel-chocolat',
                'name' => 'Hotel Chocolat',
                'category' => 'Food, Flowers & Cards',
                'tagline' => 'Velvetiser hot chocolate, luxury boxes & festive hampers.',
                'badge' => 'Treats',
                'base_url' => "https://www.hotelchocolat.com/uk/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.hotelchocolat.com/uk/search?q={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#78350f',
            ],
            [
                'id' => 'moonpig',
                'name' => 'Moonpig',
                'category' => 'Food, Flowers & Cards',
                'tagline' => 'Personalised cards, fresh bouquets & letterbox gifts.',
                'badge' => 'Cards & Flowers',
                'base_url' => "https://www.moonpig.com/uk/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.moonpig.com/uk/search/?q={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#db2777',
            ],
            [
                'id' => 'oliver-bonas',
                'name' => 'Oliver Bonas',
                'category' => 'Personalised & Home',
                'tagline' => 'Design-led homeware, jewellery & stylish accessories.',
                'badge' => 'Design',
                'base_url' => "https://www.oliverbonas.com/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.oliverbonas.com/catalogsearch/result/?q={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#0d9488',
            ],
            [
                'id' => 'asos',
                'name' => 'ASOS',
                'category' => 'Fashion & Beauty',
                'tagline' => 'Trainers, seasonal fashion, grooming & accessories.',
                'badge' => 'Fashion',
                'base_url' => "https://www.asos.com/?utm_source={$pid}&utm_medium=affiliate",
                'search_template' => "https://www.asos.com/search/?q={query}&utm_source={$pid}&utm_medium=affiliate",
                'accent_color' => '#1e293b',
            ],
        ];
    }

    /**
     * Return unique partner categories for filter pills.
     *
     * @return list<string>
     */
    public static function getPartnerCategories(): array
    {
        $categories = [];
        foreach (self::getRetailPartners() as $partner) {
            if (!in_array($partner['category'], $categories, true)) {
                $categories[] = $partner['category'];
            }
        }
        return $categories;
    }

    /**
     * Curated one-click gift ideas users can add straight to their wishlist.
     *
     * @return list<array{
     *     title: string,
     *     occasion: string,
     *     partner_name: string,
     *     approx_price: string,
     *     url: string
     * }>
     */
    public static function getCuratedGiftIdeas(): array
    {
        $amazonTag = rawurlencode((string) ($_ENV['AMAZON_AFFILIATE_TAG'] ?? 'familyplatf0e-21'));
        $pid = rawurlencode(self::DEFAULT_PID);

        return [
            [
                'title' => 'Sony WH-1000XM5 Noise Cancelling Headphones',
                'occasion' => 'Christmas',
                'partner_name' => 'Amazon UK',
                'approx_price' => '279.00',
                'url' => "https://www.amazon.co.uk/s?k=Sony+WH-1000XM5&tag={$amazonTag}",
            ],
            [
                'title' => 'Hotel Chocolat Velvetiser & Hot Chocolate Set',
                'occasion' => 'Christmas',
                'partner_name' => 'Hotel Chocolat',
                'approx_price' => '99.95',
                'url' => "https://www.hotelchocolat.com/uk/search?q=Velvetiser&utm_source={$pid}&utm_medium=affiliate",
            ],
            [
                'title' => 'John Lewis Luxury Cashmere Scarf',
                'occasion' => 'Birthday',
                'partner_name' => 'John Lewis & Partners',
                'approx_price' => '85.00',
                'url' => "https://www.johnlewis.com/search?search-term=cashmere+scarf&utm_source={$pid}&utm_medium=affiliate",
            ],
            [
                'title' => 'Virgin Experience Days Afternoon Tea for Two',
                'occasion' => 'Anniversary',
                'partner_name' => 'Virgin Experience Days',
                'approx_price' => '59.00',
                'url' => "https://www.virginexperiencedays.co.uk/search?q=afternoon+tea+for+two&utm_source={$pid}&utm_medium=affiliate",
            ],
            [
                'title' => 'LEGO® Botanicals Bouquet Building Set',
                'occasion' => 'Birthday',
                'partner_name' => 'LEGO® Official',
                'approx_price' => '54.99',
                'url' => "https://www.lego.com/en-gb/search?q=botanical+bouquet&utm_source={$pid}&utm_medium=affiliate",
            ],
            [
                'title' => 'Personalised Family Keepsake Print',
                'occasion' => 'Housewarming',
                'partner_name' => 'Not On The High Street',
                'approx_price' => '38.00',
                'url' => "https://www.notonthehighstreet.com/search?term=personalised+family+print&utm_source={$pid}&utm_medium=affiliate",
            ],
            [
                'title' => 'M&S Luxury Celebration Gift Hamper',
                'occasion' => 'Wedding',
                'partner_name' => 'Marks & Spencer',
                'approx_price' => '75.00',
                'url' => "https://www.marksandspencer.com/MSFindItemsByKeyword?searchTerm=gift+hamper&utm_source={$pid}&utm_medium=affiliate",
            ],
            [
                'title' => 'Soft Organic Cotton Baby Gift Box',
                'occasion' => 'Baby Shower',
                'partner_name' => 'John Lewis & Partners',
                'approx_price' => '45.00',
                'url' => "https://www.johnlewis.com/search?search-term=baby+gift+box&utm_source={$pid}&utm_medium=affiliate",
            ],
        ];
    }

    /**
     * Build quick-shop affiliate links for a specific wishlist item title.
     *
     * @param string $itemTitle
     * @return list<array{name: string, url: string}>
     */
    public static function buildQuickShopLinksForItem(string $itemTitle): array
    {
        $clean = trim($itemTitle);
        if ($clean === '') {
            return [];
        }

        $encoded = rawurlencode($clean);
        $amazonTag = rawurlencode((string) ($_ENV['AMAZON_AFFILIATE_TAG'] ?? 'familyplatf0e-21'));
        $pid = rawurlencode(self::DEFAULT_PID);

        return [
            [
                'name' => 'Amazon UK',
                'url' => "https://www.amazon.co.uk/s?k={$encoded}&tag={$amazonTag}",
            ],
            [
                'name' => 'John Lewis',
                'url' => "https://www.johnlewis.com/search?search-term={$encoded}&utm_source={$pid}&utm_medium=affiliate",
            ],
            [
                'name' => 'Argos',
                'url' => "https://www.argos.co.uk/search/{$encoded}/?utm_source={$pid}&utm_medium=affiliate",
            ],
        ];
    }
}
