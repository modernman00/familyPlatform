<?php

declare(strict_types=1);

namespace App\controller\members;

use App\model\AllMembersData;
use App\services\ActivityProximityService;
use Src\Ai\AiPrivacyGuard;
use Src\Ai\ResilientWebSearchService;
use Src\LoginUtility;

final class ActivityDiscoveryController
{
    /**
     * Discover live UK family activities grounded via ResilientWebSearchService.
     * GET /member/activities/discover?category={cat}&location={loc}&age_group={age}
     */
    public static function discover(): void
    {
        try {
            $userId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string)$_SESSION['id'] : '';
            if ($userId === '') {
                \msgException(401, 'Unauthorised access');
                return;
            }

            $category = is_string($_GET['category'] ?? null) ? trim((string)$_GET['category']) : 'general';
            $locationRaw = is_string($_GET['location'] ?? null) ? trim((string)$_GET['location']) : '';
            $ageGroupRaw = is_string($_GET['age_group'] ?? null) ? trim((string)$_GET['age_group']) : 'all-ages';

            // Default location to London or session preference if unsupplied
            $location = $locationRaw !== '' ? $locationRaw : 'London';
            $safeLocation = AiPrivacyGuard::scrubText($location);
            $cleanAge = AiPrivacyGuard::scrubText($ageGroupRaw);

            $searchService = new ResilientWebSearchService();

            $research = match (strtolower($category)) {
                'barbecue', 'bbq', 'picnic' => $searchService->groundedResearch(
                    query: "UK public parks designated barbecue areas rules facilities {$safeLocation}",
                    systemInstruction: "You are the FamilyPlatform Barbecue & Picnic Scout. Highlight 3 excellent public parks or open spaces around {$safeLocation} where families can have a barbecue or picnic. Explicitly mention local council rules on disposable BBQs, parking availability, and family amenities in British English.",
                    options: ['count' => 5, 'min_authority_tier' => 1]
                ),
                'museum', 'culture', 'exhibition' => $searchService->groundedResearch(
                    query: "UK free family museums exhibitions weekend activities {$safeLocation} {$cleanAge}",
                    systemInstruction: "You are the FamilyPlatform Museum & Culture Curator. Recommend 3 engaging, family-friendly museums or galleries near {$safeLocation} suitable for {$cleanAge}. Note whether entry is free, weekend workshops, and interactive exhibits in British English.",
                    options: ['count' => 5, 'min_authority_tier' => 1]
                ),
                'weekend_drive', 'drive', 'road_trip' => $searchService->groundedResearch(
                    query: "UK scenic weekend drives countryside routes family pubs viewpoints near {$safeLocation}",
                    systemInstruction: "You are the FamilyPlatform Scenic Drive Curator. Recommend 2 scenic countryside routes or day drives near {$safeLocation} featuring scenic lookouts, nature walks, and a family-friendly pub or café with parking in British English.",
                    options: ['count' => 5, 'min_authority_tier' => 1]
                ),
                'holiday', 'staycation', 'getaway' => $searchService->groundedResearch(
                    query: "UK family staycation weekend getaway cottage coastal breaks accessible from {$safeLocation}",
                    systemInstruction: "You are the FamilyPlatform Staycation & Holiday Scout. Suggest 3 brilliant family holiday getaways or weekend breaks within comfortable driving or train distance of {$safeLocation} suitable for multi-generational families in British English.",
                    options: ['count' => 5, 'min_authority_tier' => 1]
                ),
                default => $searchService->discoverFamilyWeekendOutings($safeLocation, $cleanAge)
            };

            // Parse structured cards from synthesis and citations
            $cards = self::parseActivityCards($research, $category, $safeLocation);

            $response = [
                'success' => $research['success'] ?? true,
                'category' => $category,
                'location' => $safeLocation,
                'synthesis' => $research['synthesis'] ?? '',
                'citations' => $research['citations'] ?? [],
                'cards' => $cards,
                'viral_share_prompt' => $research['viral_share_prompt'] ?? "👨‍👩‍👧‍👦 Family Vote for {$safeLocation}: Which outing should we do?",
                'provider' => $research['search_provider'] ?? 'Live Search',
                'cache_hit' => (bool)($research['cache_hit'] ?? false),
            ];

            \msgSuccess(200, $response);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Preview how many family members reside within the specified proximity radius.
     * POST /member/activities/preview-audience
     */
    public static function previewProximityAudience(): void
    {
        try {
            $userId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string)$_SESSION['id'] : '';
            if ($userId === '') {
                \msgException(401, 'Unauthorised access');
                return;
            }

            $cleanInput = LoginUtility::getSanitisedInputData($_POST);
            $famCode = is_string($_SESSION['famCode'] ?? null) ? (string)$_SESSION['famCode'] : '';
            $venueLocation = is_string($cleanInput['venue_location'] ?? null) ? trim((string)$cleanInput['venue_location']) : 'London';
            $radiusMiles = isset($cleanInput['radius_miles']) ? max(5, min(150, (int)$cleanInput['radius_miles'])) : 25;

            if ($famCode === '') {
                \msgSuccess(200, [
                    'in_proximity_count' => 0,
                    'total_family_count' => 0,
                    'radius_miles' => $radiusMiles,
                    'summary' => 'No registered family group found.',
                ]);
                return;
            }

            $allMembers = AllMembersData::AllMembersEmailByFamCode($famCode);
            $filtered = ActivityProximityService::filterMembersByProximity($allMembers, $venueLocation, $radiusMiles);

            $nearbyCount = count($filtered['nearby_members']);
            $totalCount = count($allMembers);

            // Generate privacy-safe summary names (first 3 names only)
            $sampleNames = [];
            foreach (array_slice($filtered['nearby_members'], 0, 3) as $m) {
                $first = trim((string)($m['firstName'] ?? ''));
                if ($first !== '') {
                    $sampleNames[] = $first;
                }
            }

            $summary = $nearbyCount > 0
                ? "{$nearbyCount} of {$totalCount} family members live within {$radiusMiles} miles of {$filtered['venue_coords']['name']}."
                : "No family members currently recorded within {$radiusMiles} miles of {$filtered['venue_coords']['name']}.";

            \msgSuccess(200, [
                'in_proximity_count' => $nearbyCount,
                'total_family_count' => $totalCount,
                'radius_miles' => $radiusMiles,
                'venue_resolved' => $filtered['venue_coords']['name'],
                'sample_names' => $sampleNames,
                'summary' => $summary,
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Parse structured suggestion cards from the synthesis text and citations.
     *
     * @param array<string, mixed> $research
     * @return list<array{title: string, description: string, type: string, location: string, source_url: ?string}>
     */
    private static function parseActivityCards(array $research, string $category, string $safeLocation): array
    {
        $cards = [];
        $citations = is_array($research['citations'] ?? null) ? $research['citations'] : [];
        $synthesis = is_string($research['synthesis'] ?? null) ? $research['synthesis'] : '';

        // Extract bullet points or numbered recommendations from synthesis
        $lines = preg_split('/(\r\n|\n|\r)/', $synthesis);
        if (is_array($lines)) {
            $currentTitle = '';
            $currentDesc = '';

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '') continue;

                // Match numbered items like "1. Richmond Park" or bullet "• Kew Gardens"
                if (preg_match('/^(?:\d+[\.\)]|[\*\•\-])\s*\*{0,2}([^:\*]+)\*{0,2}[:\-]?\s*(.*)$/u', $trimmed, $m)) {
                    if ($currentTitle !== '') {
                        $built = self::buildCard($currentTitle, $currentDesc, $category, $safeLocation, $citations);
                        if (!\App\services\ActivityAffiliateService::isBureaucraticOrSearchUrl($built['source_url'])) {
                            $cards[] = $built;
                        }
                    }
                    $currentTitle = trim($m[1]);
                    $currentDesc = trim($m[2]);
                } elseif ($currentTitle !== '') {
                    $currentDesc .= ' ' . $trimmed;
                }
            }

            if ($currentTitle !== '' && count($cards) < 3) {
                $built = self::buildCard($currentTitle, $currentDesc, $category, $safeLocation, $citations);
                if (!\App\services\ActivityAffiliateService::isBureaucraticOrSearchUrl($built['source_url'])) {
                    $cards[] = $built;
                }
            }
        }

        // If synthesis cards are sparse or were bureaucratic, pull from ActivityAffiliateService
        if (count($cards) < 3) {
            $enrichedOutings = \App\services\ActivityAffiliateService::curateAndEnrichOutings($research, $safeLocation, $category);
            foreach ($enrichedOutings as $o) {
                if (count($cards) >= 3) break;

                // Check duplicate
                $exists = false;
                foreach ($cards as $c) {
                    if (stripos($c['title'], $o['title']) !== false || stripos($o['title'], $c['title']) !== false) {
                        $exists = true;
                        break;
                    }
                }
                if ($exists) continue;

                $cards[] = [
                    'title' => $o['title'],
                    'description' => $o['snippet'],
                    'type' => self::mapCategoryToEventType($o['category']),
                    'location' => $o['location'],
                    'source_url' => $o['url'],
                    'price_guide' => $o['price_guide'],
                    'partner_name' => $o['partner_name'],
                    'affiliate_label' => $o['affiliate_label'],
                    'is_affiliate' => true,
                ];
            }
        }

        return array_slice($cards, 0, 3);
    }

    /**
     * @param array<int, array<string, mixed>> $citations
     * @return array{
     *     title: string,
     *     description: string,
     *     type: string,
     *     location: string,
     *     source_url: string|null,
     *     price_guide: string,
     *     partner_name: string,
     *     affiliate_label: string,
     *     is_affiliate: bool
     * }
     */
    private static function buildCard(string $title, string $desc, string $category, string $location, array $citations): array
    {
        $cleanTitle = trim(preg_replace('/^[\d\.\*\-\s]+/u', '', $title) ?? '');
        $cleanDesc = trim(preg_replace('/\s+/', ' ', $desc) ?? '');
        if (strlen($cleanDesc) > 220) {
            $cleanDesc = substr($cleanDesc, 0, 217) . '...';
        }

        $sourceUrl = null;
        if (!empty($citations)) {
            $first = (string)($citations[0]['url'] ?? '');
            if (!\App\services\ActivityAffiliateService::isBureaucraticOrSearchUrl($first)) {
                $sourceUrl = $first;
            }
        }

        $affiliate = \App\services\ActivityAffiliateService::buildAffiliateData($cleanTitle, $category, $location, $sourceUrl);

        return [
            'title' => $cleanTitle !== '' ? $cleanTitle : 'Family Outing',
            'description' => $cleanDesc !== '' ? $cleanDesc : "Great family activity in {$location}.",
            'type' => self::mapCategoryToEventType($category),
            'location' => $location,
            'source_url' => $affiliate['url'],
            'price_guide' => $affiliate['price_guide'],
            'partner_name' => $affiliate['partner_name'],
            'affiliate_label' => $affiliate['affiliate_label'],
            'is_affiliate' => true,
        ];
    }

    private static function mapCategoryToEventType(string $cat): string
    {
        return match (strtolower($cat)) {
            'barbecue', 'bbq', 'picnic' => 'Barbecue',
            'museum', 'culture' => 'Museum',
            'weekend_drive', 'drive' => 'Weekend_Drive',
            'holiday', 'staycation' => 'Holiday',
            default => 'Other'
        };
    }
}
