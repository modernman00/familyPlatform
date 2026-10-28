<?php

declare(strict_types=1);

namespace App\services;

/**
 * ActivityAffiliateService
 *
 * Curates grounded, high-value UK family activities, eliminates bureaucratic baseline search results
 * (e.g. gov.uk queries), and enriches outings with commercial affiliate partnership links (Viator,
 * GetYourGuide, Virgin Experience Days, Booking.com, National Trust).
 *
 * Compliant with UK Advertising Standards Authority (ASA) and CMA affiliate disclosure rules.
 */
final class ActivityAffiliateService
{
    private const AFFILIATE_PID = 'FAMILYPLATFORM';

    /**
     * Domains and URL patterns to strictly reject from user-facing family outings.
     *
     * @var list<string>
     */
    private const BANNED_URL_PATTERNS = [
        'gov.uk/search',
        'gov.uk',
        'google.com/search',
        'bing.com/search',
        'duckduckgo.com',
        'wikipedia.org/wiki/special:',
        'search.yahoo.com',
    ];

    /**
     * Premier UK family attractions and destinations by region and category.
     *
     * @var array<string, list<array{
     *     name: string,
     *     category: string,
     *     description: string,
     *     location: string,
     *     price_guide: string,
     *     partner: string,
     *     search_slug: string,
     *     highlight: string
     * }>>
     */
    private const REGIONAL_CURATED_VENUES = [
        'south_kensington' => [
            [
                'name' => 'Natural History Museum & Dinosaur Gallery',
                'category' => 'Museum',
                'description' => 'World-famous iconic dinosaur exhibits, earthquake simulator, Hintze Hall blue whale skeleton, and interactive science discovery zones.',
                'location' => 'Cromwell Road, South Kensington SW7 5BD',
                'price_guide' => 'Free Entry (Special Exhibits £15)',
                'partner' => 'GetYourGuide',
                'search_slug' => 'natural-history-museum-london',
                'highlight' => 'Free general admission, child-friendly discovery rooms & giant fossils.',
            ],
            [
                'name' => 'Science Museum & Wonderlab Interactive Gallery',
                'category' => 'Museum',
                'description' => 'Seven zones of hands-on interactive science phenomena, live experiments, flight simulators, and space exploration rockets.',
                'location' => 'Exhibition Road, South Kensington SW7 2DD',
                'price_guide' => 'Free Entry (Wonderlab Pass £12)',
                'partner' => 'Viator',
                'search_slug' => 'science-museum-wonderlab-london',
                'highlight' => 'Unmissable hands-on experiments, dry slides & Tesla lightning shows.',
            ],
            [
                'name' => 'Victoria and Albert (V&A) Museum Family Exploration Trails',
                'category' => 'Museum',
                'description' => 'The world’s premier museum of art, design and performance, featuring free interactive family exploration backpacks, trails, and garden courtyard fountains.',
                'location' => 'Cromwell Road, South Kensington SW7 2RL',
                'price_guide' => 'Free Admission',
                'partner' => 'Viator',
                'search_slug' => 'victoria-and-albert-museum-london',
                'highlight' => 'Free family activity backpacks and delightful Italianate garden courtyard.',
            ],
            [
                'name' => 'Hyde Park & Kensington Gardens Picnic & Serpentine Boating',
                'category' => 'Barbecue & Picnic',
                'description' => 'Sprawling royal parkland with lush picnic lawns, the Diana Memorial Playground with pirate ship, and Serpentine solar heritage pedal boats.',
                'location' => 'Kensington Gardens, London W2 / SW7',
                'price_guide' => 'Free Park Entry (Boating £12)',
                'partner' => 'Virgin Experience Days',
                'search_slug' => 'serpentine-boating-hydepark',
                'highlight' => 'Designated scenic picnic clearings and world-class pirate adventure playground.',
            ],
        ],
        'london' => [
            [
                'name' => 'Kew Royal Botanic Gardens & Treetop Walkway',
                'category' => 'Outdoor & Adventure',
                'description' => 'UNESCO World Heritage botanical paradise with soaring treetop walkway, Temperate House, Children’s Garden, and scenic family walking paths.',
                'location' => 'Kew, Richmond TW9 3AB',
                'price_guide' => 'From £19/adult (Under 4s Free)',
                'partner' => 'GetYourGuide',
                'search_slug' => 'kew-gardens-admission-ticket',
                'highlight' => 'Enormous botanical wonderland with dedicated 10,000sqm children’s play garden.',
            ],
            [
                'name' => 'Richmond Park Deer Walk & Isabella Plantation',
                'category' => 'Barbecue & Picnic',
                'description' => 'Historic 2,500-acre National Nature Reserve with roaming red and fallow deer herds, woodland picnic clearings, and vibrant evergreen azalea trails.',
                'location' => 'Richmond Park, London TW10 5HS',
                'price_guide' => 'Free Open Access',
                'partner' => 'National Trust',
                'search_slug' => 'richmond-park-family-trails',
                'highlight' => 'Spectacular wildlife viewing and expansive family picnic clearings.',
            ],
            [
                'name' => 'Tower of London & Crown Jewels Family Adventure',
                'category' => 'Museum',
                'description' => 'Step inside 1,000 years of royal British history, witness the dazzling Crown Jewels, meet the legendary Yeoman Warders (Beefeaters), and explore medieval battlements.',
                'location' => 'Tower Hill, London EC3N 4AB',
                'price_guide' => 'From £34.80 (Family Discounts)',
                'partner' => 'Viator',
                'search_slug' => 'tower-of-london-admission',
                'highlight' => 'Historic fortress, real armouries, and legendary ravens.',
            ],
            [
                'name' => 'ZSL London Zoo & Land of the Lions',
                'category' => 'Outdoor & Adventure',
                'description' => 'World’s oldest scientific zoo nestled in Regent’s Park, featuring Gorilla Kingdom, Penguin Beach, walkthrough butterfly biomes, and interactive feeding talks.',
                'location' => 'Regent’s Park, London NW1 4RY',
                'price_guide' => 'From £27/person',
                'partner' => 'GetYourGuide',
                'search_slug' => 'london-zoo-entry-ticket',
                'highlight' => 'Over 15,000 animals with immersive walkthrough habitats.',
            ],
        ],
        'birmingham' => [
            [
                'name' => 'Cadbury World Chocolate Experience',
                'category' => 'Outdoor & Adventure',
                'description' => 'Interactive 4D chocolate-making adventure, ride the Crunchie rollercoaster cinema, enjoy chocolate tasting zones, and explore African Adventure play area.',
                'location' => 'Bournville, Birmingham B30 1JR',
                'price_guide' => 'From £18.50/ticket',
                'partner' => 'Viator',
                'search_slug' => 'cadbury-world-birmingham-tickets',
                'highlight' => 'Sweet chocolate tasting and 4D sensory chocolate adventure cinema.',
            ],
            [
                'name' => 'Thinktank Birmingham Science Museum & MiniBrum',
                'category' => 'Museum',
                'description' => 'Award-winning science museum featuring MiniBrum child-sized city, Planetarium dome shows, historic steam locomotives, and interactive outdoor Science Garden.',
                'location' => 'Millennium Point, Birmingham B4 7XG',
                'price_guide' => 'From £14.50 (Under 3s Free)',
                'partner' => 'GetYourGuide',
                'search_slug' => 'thinktank-birmingham-science-museum',
                'highlight' => 'Dedicated MiniBrum child roleplay city and giant outdoor splash park.',
            ],
            [
                'name' => 'Warwick Castle Medieval Battlement Adventure',
                'category' => 'Holiday & Weekend Drive',
                'description' => 'Magnificent medieval castle with live jousting tournaments, Horrible Histories maze, Birds of Prey displays, and riverside woodland glamping staycations.',
                'location' => 'Warwick, Warwickshire CV34 6AU',
                'price_guide' => 'From £22/ticket',
                'partner' => 'Virgin Experience Days',
                'search_slug' => 'warwick-castle-family-pass',
                'highlight' => 'Live medieval jousting, dragon towers, and scenic riverside castle grounds.',
            ],
            [
                'name' => 'Lickey Hills Country Park & Viewpoint Barbecue Spots',
                'category' => 'Barbecue & Picnic',
                'description' => '524 acres of magnificent woodland, panoramic Beacon Hill viewpoints across Worcestershire, designated picnic clearings, and adventure playgrounds.',
                'location' => 'Warren Lane, Rednal, Birmingham B45 8ER',
                'price_guide' => 'Free Open Access',
                'partner' => 'National Trust',
                'search_slug' => 'lickey-hills-country-park',
                'highlight' => 'Spectacular elevated views, pine trails, and wide picnic meadows.',
            ],
        ],
        'manchester' => [
            [
                'name' => 'Science and Industry Museum & Revolution Manchester',
                'category' => 'Museum',
                'description' => 'Explore Manchester’s industrial heritage with live textile machinery demonstrations, interactive Experiment gallery, historic steam engines, and hands-on science.',
                'location' => 'Liverpool Road, Manchester M3 4FP',
                'price_guide' => 'Free Admission (Donations Welcome)',
                'partner' => 'GetYourGuide',
                'search_slug' => 'manchester-science-industry-museum',
                'highlight' => 'World’s oldest passenger railway station and interactive physics gallery.',
            ],
            [
                'name' => 'National Football Museum & Penalty Shootout Interactive',
                'category' => 'Museum',
                'description' => 'Six floors of football history, authentic Premier League and World Cup silverware, interactive penalty shootouts, and football commentary booths.',
                'location' => 'Urbis Building, Cathedral Gardens, Manchester M4 3BG',
                'price_guide' => 'From £14/adult (£8/child)',
                'partner' => 'Viator',
                'search_slug' => 'national-football-museum-manchester',
                'highlight' => 'Live interactive penalty shootouts and historic World Cup trophies.',
            ],
            [
                'name' => 'Peak District Country Drive & Mam Tor Ridge Walk',
                'category' => 'Weekend Drive',
                'description' => 'Short scenic countryside drive from Manchester through Castleton, Blue John cavern caves, Mam Tor ridge viewpoints, and family-friendly coaching inn pubs.',
                'location' => 'Castleton, Hope Valley, Peak District S33 8WN',
                'price_guide' => 'Free Countryside Access',
                'partner' => 'Virgin Experience Days',
                'search_slug' => 'peak-district-scenic-drive',
                'highlight' => 'Unrivalled 360-degree hill vistas and family cave explorations.',
            ],
            [
                'name' => 'Heaton Park Boating Lake & Animal Centre',
                'category' => 'Barbecue & Picnic',
                'description' => 'Europe’s largest municipal park spanning 600 acres with an 18th-century hall, farm animal centre, electric tramway, pedal boat hire, and barbecue-friendly lawns.',
                'location' => 'Middleton Road, Higher Blackley, Manchester M25 2SW',
                'price_guide' => 'Free Park Entry (Boats £10)',
                'partner' => 'National Trust',
                'search_slug' => 'heaton-park-manchester',
                'highlight' => 'Historic tram rides, farm animals, and expansive picnic grasslands.',
            ],
        ],
        'cotswolds' => [
            [
                'name' => 'Cotswolds Scenic Countryside Drive & Bourton-on-the-Water',
                'category' => 'Weekend Drive',
                'description' => 'Quintessential English countryside driving route through honey-coloured stone villages, shallow riverside picnics, and the famous Cotswold Motoring Museum.',
                'location' => 'Bournville / Bourton-on-the-Water, GL54 2BY',
                'price_guide' => 'Free Drive (Museum £7.50)',
                'partner' => 'Virgin Experience Days',
                'search_slug' => 'cotswolds-scenic-tour-day-trip',
                'highlight' => 'Charming low bridges, shallow clear rivers for paddling, and artisan bakeries.',
            ],
            [
                'name' => 'Cotswold Farm Park & Rare Breeds Conservation',
                'category' => 'Outdoor & Adventure',
                'description' => 'Adam Henson’s famous working farm featuring rare British livestock breeds, tractor trailer rides, adventure bounce pillows, and glamping pods.',
                'location' => 'Guiting Power, Cheltenham GL54 5FL',
                'price_guide' => 'From £16.50/person',
                'partner' => 'GetYourGuide',
                'search_slug' => 'cotswold-farm-park-tickets',
                'highlight' => 'Hands-on animal feeding, tractor safaris, and luxury glamping staycations.',
            ],
            [
                'name' => 'Cotswolds Family Holiday Cottage & Country Lodge Break',
                'category' => 'Holiday & Staycation',
                'description' => 'Cosy stone cottage retreat with wood-burning stoves, private hot tubs, enclosed gardens, and doorstep access to country walking trails.',
                'location' => 'Stow-on-the-Wold / Burford, Cotswolds',
                'price_guide' => 'From £135/night (Sleeps 6-8)',
                'partner' => 'Booking.com',
                'search_slug' => 'cotswolds-holiday-cottages-family',
                'highlight' => 'Multi-generational staycation cottage with private garden and log fires.',
            ],
        ],
        'general_uk' => [
            [
                'name' => 'National Trust Historic Estate & Country Woodland Picnic',
                'category' => 'Barbecue & Picnic',
                'description' => 'Protected historic parkland, ancient woodlands, natural playground trails, and designated family picnic grounds with visitor cafés and free parking for members.',
                'location' => 'Regional UK National Trust Parkland',
                'price_guide' => 'Free for Members (Day Pass from £12)',
                'partner' => 'National Trust',
                'search_slug' => 'national-trust-family-day-out',
                'highlight' => 'Pristine heritage gardens, outdoor play forts, and seasonal nature trails.',
            ],
            [
                'name' => 'UK Theme Park & Family Resort Adventure Day',
                'category' => 'Outdoor & Adventure',
                'description' => 'Adrenaline-packed family rollercoasters, themed immersive lands, water rides, live entertainment shows, and resort hotel packages.',
                'location' => 'UK Major Resort Theme Parks',
                'price_guide' => 'From £29/ticket (Advance Booking)',
                'partner' => 'GetYourGuide',
                'search_slug' => 'uk-theme-park-tickets',
                'highlight' => 'Thrill rides for older kids and gentle interactive rides for toddlers.',
            ],
            [
                'name' => 'Coastal Beach Cottage & Seaside Holiday Break',
                'category' => 'Holiday & Staycation',
                'description' => 'Self-catering coastal family retreat within walking distance of sandy beaches, coastal walking paths, fresh seafood shacks, and rock pooling.',
                'location' => 'UK Coastline (Cornwall, Dorset, Norfolk, Northumberland)',
                'price_guide' => 'From £120/night',
                'partner' => 'Booking.com',
                'search_slug' => 'uk-coastal-holiday-cottages-family',
                'highlight' => 'Sea views, sandcastle beaches, and pet-friendly enclosed gardens.',
            ],
        ],
    ];

    /**
     * Curate and enrich raw AI/search results into verified, high-converting commercial family outings.
     * Replaces bureaucratic gov.uk baseline links with verified attractions and affiliate partner URLs.
     *
     * @param array<string, mixed> $rawResult
     * @param string $region
     * @param string $interests
     * @return list<array{
     *     title: string,
     *     snippet: string,
     *     url: string,
     *     location: string,
     *     category: string,
     *     price_guide: string,
     *     partner_name: string,
     *     affiliate_label: string,
     *     is_affiliate: bool,
     *     disclosure: string
     * }>
     */
    public static function curateAndEnrichOutings(array $rawResult, string $region, string $interests): array
    {
        $normalisedRegion = self::normaliseRegionKey($region);
        $curatedSet = self::getCuratedVenuesForRegion($normalisedRegion, $interests);

        $outings = [];

        // 1. First, harvest any genuinely good non-bureaucratic citations from live search
        $citations = is_array($rawResult['citations'] ?? null) ? $rawResult['citations'] : [];
        foreach ($citations as $c) {
            $title = trim((string)($c['title'] ?? ''));
            $url = trim((string)($c['url'] ?? ''));
            $snippet = trim((string)($c['snippet'] ?? ''));

            if ($title === '' || self::isBureaucraticOrSearchUrl($url)) {
                continue;
            }

            // Decorate valid live citation with commercial monetization
            $affiliate = self::buildAffiliateData($title, $interests, $region, $url);
            $outings[] = [
                'title' => $title,
                'snippet' => $snippet !== '' ? $snippet : "Recommended local family attraction in {$region}.",
                'url' => $affiliate['url'],
                'location' => $region,
                'category' => self::detectCategory($title . ' ' . $snippet, $interests),
                'price_guide' => $affiliate['price_guide'],
                'partner_name' => $affiliate['partner_name'],
                'affiliate_label' => $affiliate['affiliate_label'],
                'is_affiliate' => true,
                'disclosure' => self::getAffiliateDisclosure(),
            ];

            if (count($outings) >= 2) {
                break;
            }
        }

        // 2. Pad or replace with curated, high-converting regional attractions
        foreach ($curatedSet as $venue) {
            if (count($outings) >= 4) {
                break;
            }

            // Check if title already present
            $alreadyExists = false;
            foreach ($outings as $existing) {
                if (stripos($existing['title'], $venue['name']) !== false || stripos($venue['name'], $existing['title']) !== false) {
                    $alreadyExists = true;
                    break;
                }
            }
            if ($alreadyExists) {
                continue;
            }

            $affiliate = self::buildAffiliateData(
                $venue['name'],
                $venue['category'],
                $venue['location'],
                null,
                $venue['partner'],
                $venue['search_slug']
            );

            $outings[] = [
                'title' => $venue['name'],
                'snippet' => $venue['description'] . ' ' . $venue['highlight'],
                'url' => $affiliate['url'],
                'location' => $venue['location'],
                'category' => $venue['category'],
                'price_guide' => $venue['price_guide'],
                'partner_name' => $affiliate['partner_name'],
                'affiliate_label' => $affiliate['affiliate_label'],
                'is_affiliate' => true,
                'disclosure' => self::getAffiliateDisclosure(),
            ];
        }

        // 3. Fallback guarantee: if somehow still empty, synthesize grounded regional activities
        if (empty($outings)) {
            $genericVenues = self::REGIONAL_CURATED_VENUES['general_uk'];
            foreach (array_slice($genericVenues, 0, 3) as $v) {
                $affiliate = self::buildAffiliateData($v['name'], $v['category'], $region, null, $v['partner'], $v['search_slug']);
                $outings[] = [
                    'title' => $v['name'] . " ({$region})",
                    'snippet' => $v['description'],
                    'url' => $affiliate['url'],
                    'location' => $region,
                    'category' => $v['category'],
                    'price_guide' => $v['price_guide'],
                    'partner_name' => $affiliate['partner_name'],
                    'affiliate_label' => $affiliate['affiliate_label'],
                    'is_affiliate' => true,
                    'disclosure' => self::getAffiliateDisclosure(),
                ];
            }
        }

        return array_slice($outings, 0, 4);
    }

    /**
     * Check if a URL is a generic government search query or search engine link.
     */
    public static function isBureaucraticOrSearchUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }

        $urlLower = strtolower($url);
        foreach (self::BANNED_URL_PATTERNS as $banned) {
            if (str_contains($urlLower, $banned)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Construct partner affiliate deep links with proper UTM and referral tags.
     *
     * @return array{
     *     url: string,
     *     partner_name: string,
     *     affiliate_label: string,
     *     price_guide: string
     * }
     */
    public static function buildAffiliateData(
        string $venueTitle,
        string $category,
        string $location,
        ?string $originalUrl = null,
        ?string $preferredPartner = null,
        ?string $searchSlug = null
    ): array {
        $catLower = strtolower($category);
        $cleanTitle = trim($venueTitle);
        $encodedQuery = rawurlencode("{$cleanTitle} {$location}");

        // Holiday / Staycation -> Booking.com
        if (str_contains($catLower, 'holiday') || str_contains($catLower, 'staycation') || str_contains($catLower, 'cottage')) {
            return [
                'url' => "https://www.booking.com/searchresults.html?ss=" . rawurlencode($location) . "&aid=" . self::AFFILIATE_PID . "&utm_source=familyplatform&utm_medium=affiliate",
                'partner_name' => 'Booking.com',
                'affiliate_label' => '🏡 Find Family Staycations & Lodges',
                'price_guide' => 'From £95/night',
            ];
        }

        // Weekend Drive / Experiences / Afternoon tea -> Virgin Experience Days
        if (str_contains($catLower, 'drive') || str_contains($catLower, 'experience') || str_contains($catLower, 'tea')) {
            return [
                'url' => "https://www.virginexperiencedays.co.uk/search?query=" . rawurlencode($cleanTitle) . "&aff=" . self::AFFILIATE_PID . "&utm_source=familyplatform",
                'partner_name' => 'Virgin Experience Days',
                'affiliate_label' => '🏎️ Explore Experience Days & Offers',
                'price_guide' => 'From £25/person',
            ];
        }

        // Barbecue / Picnic / Heritage Parkland -> National Trust or Days Out
        if (str_contains($catLower, 'barbecue') || str_contains($catLower, 'bbq') || str_contains($catLower, 'picnic') || str_contains($catLower, 'park')) {
            return [
                'url' => "https://www.nationaltrust.org.uk/search?query=" . rawurlencode($cleanTitle) . "&campaign=" . self::AFFILIATE_PID,
                'partner_name' => 'National Trust & Days Out',
                'affiliate_label' => '🌿 View Parkland & Reserve Passes',
                'price_guide' => 'Free Entry (Passes from £12)',
            ];
        }

        // Default: Viator / GetYourGuide for Museums & Major Family Attractions
        $partner = $preferredPartner ?? 'GetYourGuide';
        if ($partner === 'Viator') {
            $slug = $searchSlug ?? str_replace(' ', '-', strtolower($cleanTitle));
            return [
                'url' => "https://www.viator.com/searchResults/all?text=" . rawurlencode($cleanTitle . ' ' . $location) . "&pid=" . self::AFFILIATE_PID . "&mcid=42383&medium=api",
                'partner_name' => 'Viator (Tripadvisor)',
                'affiliate_label' => '🎟️ Book Tickets & Fast-Track Passes',
                'price_guide' => 'Free Entry or From £14/ticket',
            ];
        }

        return [
            'url' => "https://www.getyourguide.com/s/?q=" . rawurlencode($cleanTitle . ' ' . $location) . "&partner_id=" . self::AFFILIATE_PID . "&utm_medium=affiliate_service",
            'partner_name' => 'GetYourGuide',
            'affiliate_label' => '🎟️ Book Passes & Guided Experiences',
            'price_guide' => 'Free Entry or From £12/pass',
        ];
    }

    /**
     * Standard UK ASA / CMA transparent affiliate disclosure.
     */
    public static function getAffiliateDisclosure(): string
    {
        return 'Affiliate link: FamilyPlatform may receive a small commission when you book through our partner links, at no extra cost to your family.';
    }

    /**
     * Match a freeform city/postcode string to a regional key.
     */
    private static function normaliseRegionKey(string $region): string
    {
        $low = strtolower(trim($region));

        if (str_contains($low, 'south kensington') || str_contains($low, 'sw7') || str_contains($low, 'kensington')) {
            return 'south_kensington';
        }
        if (str_contains($low, 'london') || preg_match('/\b(e|ec|n|nw|se|sw|w|wc)[0-9]{1,2}\b/', $low)) {
            return 'london';
        }
        if (str_contains($low, 'birmingham') || str_contains($low, 'solihull') || str_contains($low, 'b1') || str_contains($low, 'b2') || str_contains($low, 'b3')) {
            return 'birmingham';
        }
        if (str_contains($low, 'manchester') || str_contains($low, 'salford') || str_contains($low, 'm1') || str_contains($low, 'm2')) {
            return 'manchester';
        }
        if (str_contains($low, 'cotswold') || str_contains($low, 'cheltenham') || str_contains($low, 'gloucester') || str_contains($low, 'bourton')) {
            return 'cotswolds';
        }

        return 'general_uk';
    }

    /**
     * Retrieve regional curated items, prioritizing matching category interests.
     *
     * @return list<array{
     *     name: string,
     *     category: string,
     *     description: string,
     *     location: string,
     *     price_guide: string,
     *     partner: string,
     *     search_slug: string,
     *     highlight: string
     * }>
     */
    private static function getCuratedVenuesForRegion(string $regionKey, string $interests): array
    {
        $venues = self::REGIONAL_CURATED_VENUES[$regionKey] ?? self::REGIONAL_CURATED_VENUES['general_uk'];
        $intLower = strtolower($interests);

        if ($intLower === '' || $intLower === 'all-ages') {
            return $venues;
        }

        // Sort matching category items to top
        usort($venues, function ($a, $b) use ($intLower) {
            $aMatches = (stripos($intLower, strtolower($a['category'])) !== false || stripos($intLower, strtolower($a['name'])) !== false);
            $bMatches = (stripos($intLower, strtolower($b['category'])) !== false || stripos($intLower, strtolower($b['name'])) !== false);

            if ($aMatches && !$bMatches) return -1;
            if (!$aMatches && $bMatches) return 1;
            return 0;
        });

        return $venues;
    }

    private static function detectCategory(string $text, string $defaultInterest): string
    {
        $t = strtolower($text . ' ' . $defaultInterest);
        if (str_contains($t, 'museum') || str_contains($t, 'exhibit') || str_contains($t, 'gallery') || str_contains($t, 'history')) {
            return 'Museum';
        }
        if (str_contains($t, 'barbecue') || str_contains($t, 'bbq') || str_contains($t, 'picnic')) {
            return 'Barbecue & Picnic';
        }
        if (str_contains($t, 'drive') || str_contains($t, 'scenic') || str_contains($t, 'route')) {
            return 'Weekend Drive';
        }
        if (str_contains($t, 'holiday') || str_contains($t, 'cottage') || str_contains($t, 'staycation')) {
            return 'Holiday & Staycation';
        }
        return 'Outdoor & Adventure';
    }
}
