<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;

/**
 * PartyPlatform Ecosystem Event & Kinship Bridge Service
 *
 * Bridges FamilyPlatform events with PartyPlatform's full event management
 * studio (RSVPs, Guest Builder, Seating, Menu, Gift Wishlist & Celebratory Cards).
 *
 * Implements RFC-0008:
 *  - Ego-centric Kinship -> GuestPriorityService category mapping
 *  - HMAC-SHA256 Server-to-Server (S2S) provisioning with 2.5s circuit breaker
 *  - Same-host / VPC database fallback for 100% zero-failure availability
 *  - 60-second single-use HMAC SSO handoff URL generation
 */
final class PartyPlatformBridgeService
{
    private const DEFAULT_BRIDGE_SECRET = 'modernman_ecosystem_bridge_hmac_secret_2026_v1';
    private const HTTP_TIMEOUT_MS       = 2500;

    /**
     * Event types that default to enabling PartyPlatform Studio in Gear 1.
     *
     * @var list<string>
     */
    public const AUTO_SUGGEST_PARTY_TYPES = [
        'Party',
        'Wedding',
        'Reunion',
        'House_warming',
        'House Warming',
        'Graduation',
    ];

    /**
     * Resolve the shared HMAC secret across FamilyPlatform and PartyPlatform.
     */
    public static function resolveBridgeSecret(): string
    {
        $secret = $_ENV['ECOSYSTEM_BRIDGE_SECRET'] ?? getenv('ECOSYSTEM_BRIDGE_SECRET') ?: '';
        if (is_string($secret) && trim($secret) !== '') {
            return trim($secret);
        }

        return self::DEFAULT_BRIDGE_SECRET;
    }

    /**
     * Resolve the base URL for PartyPlatform.
     */
    public static function resolvePartyBaseUrl(): string
    {
        $envUrl = $_ENV['PARTY_PLATFORM_URL'] ?? getenv('PARTY_PLATFORM_URL') ?: '';
        if (is_string($envUrl) && trim($envUrl) !== '') {
            return rtrim(trim($envUrl), '/');
        }

        $appUrl = (string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://olaogun.test');
        if (str_contains($appUrl, '.test') || str_contains($appUrl, 'localhost')) {
            return 'https://partyplatform.test';
        }

        return 'https://mypartyplatform.com';
    }

    /**
     *Compute HMAC-SHA256 signature for S2S payloads.
     */
    public static function signPayload(int $timestamp, string $rawBody, ?string $secret = null): string
    {
        $key = $secret ?? self::resolveBridgeSecret();
        return 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $rawBody, $key);
    }

    /**
     * Build a 120-second HMAC-signed SSO redirect URL for the event host.
     */
    public static function generateSsoHandoffUrl(string $partyEventId, string $hostEmail, string $target = 'studio'): string
    {
        $cleanEventId = trim($partyEventId);
        $cleanEmail   = strtolower(trim($hostEmail));
        $cleanTarget  = in_array($target, ['studio', 'dashboard'], true) ? $target : 'studio';
        $ts           = time();
        $nonce        = bin2hex(random_bytes(16));

        $canonical = "sso:{$cleanEventId}:{$cleanEmail}:{$ts}:{$nonce}:{$cleanTarget}";
        $sig       = hash_hmac('sha256', $canonical, self::resolveBridgeSecret());

        $baseUrl = self::resolvePartyBaseUrl();
        $query = http_build_query([
            'event_id' => $cleanEventId,
            'email'    => $cleanEmail,
            'ts'       => (string) $ts,
            'nonce'    => $nonce,
            'target'   => $cleanTarget,
            'sig'      => $sig,
        ]);

        return $baseUrl . '/api/v1/ecosystem/sso-handoff?' . $query;
    }

    /**
     * Public alias for generateSsoHandoffUrl to match ecosystem contract.
     */
    public static function generateStudioSsoUrl(string $partyEventId, string $hostEmail, string $target = 'studio'): string
    {
        return self::generateSsoHandoffUrl($partyEventId, $hostEmail, $target);
    }

    /**
     * Map a KinshipCalculatorService category/degree/relation to PartyPlatform's
     * GuestBuilderConfig category, invitation_reasons, always_invite, and closeness.
     *
     * @param string   $kinshipCategory
     * @param int|null $degree
     * @param string   $egoRelation
     * @return array{
     *     guest_category: string,
     *     invitation_reasons: list<string>,
     *     always_invite: int,
     *     closeness: int
     * }
     */
    public static function mapKinshipToGuestPriority(string $kinshipCategory, ?int $degree = null, string $egoRelation = ''): array
    {
        $catNorm = strtolower(trim($kinshipCategory));
        $relNorm = strtolower(trim($egoRelation !== '' ? $egoRelation : $kinshipCategory));

        // Former spouse is not auto-locked as current partner
        if ($catNorm === 'spouse' && !str_contains($relNorm, 'former') && !str_contains($relNorm, 'late')) {
            return [
                'guest_category'     => 'Close Family',
                'invitation_reasons' => ['immediate_family', 'important_to_occasion'],
                'always_invite'      => 1,
                'closeness'          => 5,
            ];
        }

        // Direct nuclear family: Parents, Children, Full/Half Siblings (degree <= 2)
        if (
            in_array($catNorm, ['parent', 'child', 'sibling'], true)
            || ($catNorm === 'direct' && $degree !== null && $degree <= 1)
            || preg_match('/\b(mother|father|mum|dad|son|daughter|brother|sister|sibling)\b/i', $relNorm)
        ) {
            return [
                'guest_category'     => 'Close Family',
                'invitation_reasons' => ['immediate_family', 'important_to_occasion'],
                'always_invite'      => 1,
                'closeness'          => 5,
            ];
        }

        // Close family circle: Grandparents, Grandchildren, Aunts, Uncles, Nieces, Nephews, First Cousins
        $isRemovedOrDistantCousin = (bool) preg_match('/\b(2nd|3rd|4th|second|third|fourth|removed|distant|in-law)\b/i', $relNorm);
        if (
            !$isRemovedOrDistantCousin && (
                in_array($catNorm, ['grandparent', 'grandchild', 'avuncular', 'nibling', 'cousin'], true)
                || ($degree !== null && $degree <= 4)
                || preg_match('/\b(grand|aunt|uncle|niece|nephew|first cousin|1st cousin|cousin)\b/i', $relNorm)
            )
        ) {
            return [
                'guest_category'     => 'Family',
                'invitation_reasons' => ['close_relative', 'long_standing'],
                'always_invite'      => 1,
                'closeness'          => 4,
            ];
        }

        // Extended Family / In-Laws / Collateral Kin
        return [
            'guest_category'     => 'Extended Family',
            'invitation_reasons' => ['extended_family'],
            'always_invite'      => 0,
            'closeness'          => 3,
        ];
    }

    /**
     * Collect ego-centric kinship prospects for the given host user & family code.
     * Combines `family_nodes` (scored via `KinshipCalculatorService`) with any
     * registered `personal`/`account` members in the same `famCode`.
     *
     * @return list<array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     mobile: string,
     *     guest_category: string,
     *     invitation_reasons: list<string>,
     *     always_invite: int,
     *     closeness: int,
     *     kinship_title: string
     * }>
     */
    public static function buildKinshipProspectsForHost(string $hostUserId, string $familyCode): array
    {
        $cleanUserId  = trim($hostUserId);
        $cleanFamCode = trim($familyCode);
        if ($cleanFamCode === '') {
            return [];
        }

        $db = Db::connect2();
        $prospects = [];
        $seenKeys  = [];

        try {
            // 1. Fetch family tree nodes with optional linked account/contact details
            $stmtNodes = $db->prepare(
                "SELECT fn.*,
                        COALESCE(NULLIF(p.firstName, ''), fn.first_name) AS resolved_first_name,
                        COALESCE(NULLIF(p.lastName, ''), fn.last_name) AS resolved_last_name,
                        COALESCE(NULLIF(a.email, ''), NULLIF(c.email, ''), fn.email) AS resolved_email,
                        COALESCE(NULLIF(c.mobile, ''), fn.mobile) AS resolved_mobile
                 FROM family_nodes fn
                 LEFT JOIN personal p ON fn.user_id = p.id
                 LEFT JOIN account a ON fn.user_id = a.id
                 LEFT JOIN contact c ON fn.user_id = c.id
                 WHERE fn.family_code = ? AND COALESCE(fn.is_erased, 0) = 0
                 ORDER BY fn.generation_level ASC, fn.id ASC"
            );
            $stmtNodes->execute([$cleanFamCode]);
            $nodes = $stmtNodes->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if ($nodes !== []) {
                $stmtUnions = $db->prepare('SELECT * FROM family_unions WHERE family_code = ?');
                $stmtUnions->execute([$cleanFamCode]);
                $unions = $stmtUnions->fetchAll(PDO::FETCH_ASSOC) ?: [];

                $stmtChildren = $db->prepare(
                    'SELECT fnc.* FROM family_node_children fnc
                     INNER JOIN family_unions fu ON fu.id = fnc.union_id
                     WHERE fu.family_code = ?'
                );
                $stmtChildren->execute([$cleanFamCode]);
                $children = $stmtChildren->fetchAll(PDO::FETCH_ASSOC) ?: [];

                $egoNodeId = 0;
                $formattedNodes = [];
                foreach ($nodes as $n) {
                    $nid = (int) ($n['id'] ?? 0);
                    if ($cleanUserId !== '' && (string) ($n['user_id'] ?? '') === $cleanUserId) {
                        $egoNodeId = $nid;
                    }
                    $formattedNodes[] = [
                        'id'                => $nid,
                        'user_id'           => $n['user_id'] ?? null,
                        'first_name'        => (string) ($n['resolved_first_name'] ?? $n['first_name'] ?? ''),
                        'last_name'         => (string) ($n['resolved_last_name'] ?? $n['last_name'] ?? ''),
                        'gender'            => (string) ($n['gender'] ?? 'Other'),
                        'generation_level'  => (int) ($n['generation_level'] ?? 0),
                        'is_deceased'       => !empty($n['is_deceased']),
                        'is_placeholder'    => !empty($n['is_placeholder']),
                        'placeholder_label' => $n['placeholder_label'] ?? null,
                        'email'             => (string) ($n['resolved_email'] ?? ''),
                        'mobile'            => (string) ($n['resolved_mobile'] ?? ''),
                    ];
                }

                if ($egoNodeId === 0 && isset($formattedNodes[0]['id'])) {
                    $egoNodeId = (int) $formattedNodes[0]['id'];
                }

                $formattedUnions = [];
                foreach ($unions as $u) {
                    $formattedUnions[] = [
                        'id'           => (int) ($u['id'] ?? 0),
                        'partner_1_id' => (int) ($u['partner_1_id'] ?? 0),
                        'partner_2_id' => (int) ($u['partner_2_id'] ?? 0),
                        'union_type'   => (string) ($u['union_type'] ?? 'married'),
                        'is_current'   => !empty($u['is_current']),
                    ];
                }

                $formattedChildren = [];
                foreach ($children as $c) {
                    $formattedChildren[] = [
                        'union_id'          => (int) ($c['union_id'] ?? 0),
                        'child_id'          => (int) ($c['child_id'] ?? 0),
                        'relationship_type' => (string) ($c['relationship_type'] ?? 'biological'),
                    ];
                }

                $decorated = KinshipCalculatorService::calculateEgoKinship([
                    'nodes'    => $formattedNodes,
                    'unions'   => $formattedUnions,
                    'children' => $formattedChildren,
                ], $egoNodeId);

                foreach ($decorated as $dNode) {
                    // Skip the host (Ego), deceased ancestors, and synthetic placeholders
                    if (!empty($dNode['is_ego']) || !empty($dNode['is_deceased']) || !empty($dNode['is_placeholder'])) {
                        continue;
                    }
                    if ($cleanUserId !== '' && (string) ($dNode['user_id'] ?? '') === $cleanUserId) {
                        continue;
                    }

                    $first = trim((string) ($dNode['first_name'] ?? ''));
                    $last  = trim((string) ($dNode['last_name'] ?? ''));
                    if ($first === '' || str_contains(strtolower($first), 'parent not added')) {
                        continue;
                    }

                    $email  = strtolower(trim((string) ($dNode['email'] ?? '')));
                    $mobile = trim((string) ($dNode['mobile'] ?? ''));
                    $dedupeKey = $email !== '' ? 'e:' . $email : 'n:' . mb_strtolower($first . '|' . $last);
                    if (isset($seenKeys[$dedupeKey])) {
                        continue;
                    }
                    $seenKeys[$dedupeKey] = true;

                    $kinCat = (string) ($dNode['kinship_category'] ?? 'extended');
                    $deg    = isset($dNode['consanguinity_degree']) && is_numeric($dNode['consanguinity_degree'])
                        ? (int) $dNode['consanguinity_degree']
                        : null;
                    $title  = (string) ($dNode['ego_relation'] ?? 'Relative');

                    $priority = self::mapKinshipToGuestPriority($kinCat, $deg, $title);

                    $prospects[] = [
                        'first_name'         => $first,
                        'last_name'          => $last,
                        'email'              => $email,
                        'mobile'             => $mobile,
                        'guest_category'     => $priority['guest_category'],
                        'invitation_reasons' => $priority['invitation_reasons'],
                        'always_invite'      => $priority['always_invite'],
                        'closeness'          => $priority['closeness'],
                        'kinship_title'      => $title,
                    ];
                }
            }

            // 2. Also include registered directory members in the same famCode not yet on the tree
            $stmtMembers = $db->prepare(
                "SELECT DISTINCT p.id, p.firstName, p.lastName, a.email, c.mobile
                 FROM personal p
                 INNER JOIN account a ON p.id = a.id
                 LEFT JOIN contact c ON p.id = c.id
                 LEFT JOIN user_families uf ON (p.id = uf.user_id AND uf.family_code = :fc1 AND uf.status = 'approved')
                 WHERE (p.famCode = :fc2 OR uf.family_code IS NOT NULL)
                   AND p.deleted_at IS NULL"
            );
            $stmtMembers->execute(['fc1' => $cleanFamCode, 'fc2' => $cleanFamCode]);
            $dirMembers = $stmtMembers->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($dirMembers as $m) {
                $mid = (string) ($m['id'] ?? '');
                if ($mid !== '' && $mid === $cleanUserId) {
                    continue;
                }
                $first = trim((string) ($m['firstName'] ?? ''));
                $last  = trim((string) ($m['lastName'] ?? ''));
                if ($first === '') {
                    continue;
                }
                $email  = strtolower(trim((string) ($m['email'] ?? '')));
                $mobile = trim((string) ($m['mobile'] ?? ''));
                $dedupeKey = $email !== '' ? 'e:' . $email : 'n:' . mb_strtolower($first . '|' . $last);
                if (isset($seenKeys[$dedupeKey])) {
                    continue;
                }
                $seenKeys[$dedupeKey] = true;

                $prospects[] = [
                    'first_name'         => $first,
                    'last_name'          => $last,
                    'email'              => $email,
                    'mobile'             => $mobile,
                    'guest_category'     => 'Family',
                    'invitation_reasons' => ['close_relative'],
                    'always_invite'      => 1,
                    'closeness'          => 4,
                    'kinship_title'      => 'Family Circle Member',
                ];
            }
        } catch (\Throwable $e) {
            error_log('[PartyPlatformBridgeService::buildKinshipProspectsForHost] ' . $e->getMessage());
        }

        return $prospects;
    }

    /**
     * Provision or sync a FamilyPlatform event to PartyPlatform and persist the
     * resulting PartyPlatform identifiers onto the `FamilyPlatform.events` row.
     *
     * @param int    $eventNo      The `events.no` primary key in FamilyPlatform
     * @param string $hostUserId   The authenticated host user ID (`$_SESSION['id']`)
     * @param string $familyCode   The family code (`$_SESSION['famCode']`)
     * @return array{
     *     synced: bool,
     *     party_event_id: string,
     *     party_event_slug: string,
     *     party_rsvp_url: string,
     *     party_card_url: string,
     *     party_studio_sso_url: string,
     *     seeded_prospects_count: int
     * }
     */
    public static function provisionAndLinkEvent(int $eventNo, string $hostUserId, string $familyCode): array
    {
        $db = Db::connect2();

        // 1. Fetch FamilyPlatform event row and verify caller ownership or family scope
        $stmtEv = $db->prepare(
            'SELECT * FROM events
             WHERE no = ?
               AND deleted_at IS NULL
               AND (id = ? OR REPLACE(UPPER(eventCode), \'#\', \'\') = REPLACE(UPPER(?), \'#\', \'\'))
             LIMIT 1'
        );
        $stmtEv->execute([$eventNo, $hostUserId, $familyCode]);
        $event = $stmtEv->fetch(PDO::FETCH_ASSOC);

        if (!is_array($event)) {
            throw new \RuntimeException('Event not found or access denied.');
        }

        // 2. Fetch Host Details (email, name, mobile)
        $stmtHost = $db->prepare(
            "SELECT p.firstName, p.lastName,
                    COALESCE(NULLIF(TRIM(a.email), ''), NULLIF(TRIM(c.email), '')) AS email,
                    c.mobile
             FROM personal p
             LEFT JOIN account a ON p.id = a.id
             LEFT JOIN contact c ON p.id = c.id
             WHERE p.id = ? LIMIT 1"
        );
        $stmtHost->execute([$hostUserId]);
        $hostRow = $stmtHost->fetch(PDO::FETCH_ASSOC) ?: [];

        $hostEmail = strtolower(trim((string) ($hostRow['email'] ?? $_SESSION['email'] ?? $_SESSION['auth']['email'] ?? '')));
        if (
            $hostEmail === ''
            || !filter_var($hostEmail, FILTER_VALIDATE_EMAIL)
            || str_ends_with($hostEmail, '.local')
        ) {
            throw new \Src\Exceptions\ValidationException(
                'Please add a valid email address in your Account Settings before launching PartyPlatform Studio.'
            );
        }

        // Idempotency fast path: if event is already synced, return existing bridge data
        if (!empty($event['party_event_id']) && ($event['party_sync_status'] ?? '') === 'synced') {
            return [
                'synced'                 => true,
                'party_event_id'         => (string)$event['party_event_id'],
                'party_event_slug'       => (string)($event['party_event_slug'] ?? ''),
                'party_rsvp_url'         => (string)($event['party_rsvp_url'] ?? ''),
                'party_card_url'         => (string)($event['party_card_url'] ?? ''),
                'party_studio_sso_url'   => '/member/profilePage/event/' . $eventNo . '/party-sso',
                'seeded_prospects_count' => 0,
            ];
        }
        $hostFirst = trim((string) ($hostRow['firstName'] ?? $_SESSION['fName'] ?? 'Family'));
        $hostLast  = trim((string) ($hostRow['lastName'] ?? $_SESSION['lName'] ?? 'Host'));
        $hostMob   = trim((string) ($hostRow['mobile'] ?? ''));

        // 3. Build Ego-Centric Kinship Prospects
        $prospects = self::buildKinshipProspectsForHost($hostUserId, $familyCode);

        $payload = [
            'host_email'              => $hostEmail,
            'host_first_name'         => $hostFirst,
            'host_last_name'          => $hostLast,
            'host_mobile'             => $hostMob,
            'family_code'             => $familyCode,
            'family_event_no'         => $eventNo,
            'event_name'              => (string) ($event['eventName'] ?? 'Family Gathering'),
            'event_date'              => (string) ($event['eventDate'] ?? date('Y-m-d')),
            'event_type'              => (string) ($event['eventType'] ?? 'Party'),
            'event_description'       => (string) ($event['eventDescription'] ?? ''),
            'existing_party_event_id' => (string) ($event['party_event_id'] ?? ''),
            'kinship_prospects'       => $prospects,
        ];

        $bridgeResult = self::dispatchToPartyPlatform($payload);

        if ($bridgeResult !== null && !empty($bridgeResult['party_event_id'])) {
            $partyEventId = (string) $bridgeResult['party_event_id'];
            $partySlug    = (string) ($bridgeResult['slug'] ?? '');
            $rsvpUrl      = (string) ($bridgeResult['rsvp_url'] ?? '');
            $cardUrl      = (string) ($bridgeResult['card_url'] ?? '');
            $seededCount  = (int) ($bridgeResult['seeded_prospects_count'] ?? 0);

            $upd = $db->prepare(
                'UPDATE events
                 SET party_event_id = :pid,
                     party_event_slug = :slug,
                     party_rsvp_url = :rsvp,
                     party_card_url = :card,
                     party_sync_status = :status,
                     updated_at = NOW()
                 WHERE no = :no'
            );
            $upd->execute([
                'pid'    => $partyEventId,
                'slug'   => $partySlug,
                'rsvp'   => $rsvpUrl,
                'card'   => $cardUrl,
                'status' => 'synced',
                'no'     => $eventNo,
            ]);

            return [
                'synced'                 => true,
                'party_event_id'         => $partyEventId,
                'party_event_slug'       => $partySlug,
                'party_rsvp_url'         => $rsvpUrl,
                'party_card_url'         => $cardUrl,
                'party_studio_sso_url'   => '/member/profilePage/event/' . $eventNo . '/party-sso',
                'seeded_prospects_count' => $seededCount,
            ];
        }

        // Circuit breaker fallback: mark pending without failing local event creation
        try {
            $updPending = $db->prepare("UPDATE events SET party_sync_status = 'pending' WHERE no = ?");
            $updPending->execute([$eventNo]);
        } catch (\Throwable) {
        }

        return [
            'synced'                 => false,
            'party_event_id'         => '',
            'party_event_slug'       => '',
            'party_rsvp_url'         => '',
            'party_card_url'         => '',
            'party_studio_sso_url'   => '',
            'seeded_prospects_count' => 0,
        ];
    }

    /**
     * Dispatch the provisioning payload via signed HTTP S2S call (with 2500ms timeout),
     * falling back to direct VPC database provisioning if local HTTP routing is unavailable.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>|null
     */
    private static function dispatchToPartyPlatform(array $payload): ?array
    {
        $rawBody = json_encode($payload);
        if (!is_string($rawBody)) {
            return null;
        }

        $ts  = time();
        $sig = self::signPayload($ts, $rawBody);
        $url = self::resolvePartyBaseUrl() . '/api/v1/ecosystem/provision-family-event';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch !== false) {
                $parsedHost = strtolower((string) parse_url($url, PHP_URL_HOST));
                $isLocalDev = $parsedHost === 'localhost'
                    || $parsedHost === '127.0.0.1'
                    || str_ends_with($parsedHost, '.test')
                    || str_ends_with($parsedHost, '.local');

                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $rawBody,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT_MS     => self::HTTP_TIMEOUT_MS,
                    CURLOPT_CONNECTTIMEOUT => 1,
                    CURLOPT_SSL_VERIFYPEER => !$isLocalDev,
                    CURLOPT_SSL_VERIFYHOST => $isLocalDev ? 0 : 2,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                        'X-Ecosystem-Timestamp: ' . $ts,
                        'X-Ecosystem-Signature: ' . $sig,
                    ],
                ]);
                $resp = curl_exec($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if (is_string($resp) && $httpCode >= 200 && $httpCode < 300) {
                    /** @var array<string, mixed>|null $decoded */
                    $decoded = json_decode($resp, true);
                    if (is_array($decoded)) {
                        $data = $decoded['data'] ?? $decoded['message'] ?? $decoded;
                        if (is_array($data) && !empty($data['party_event_id'])) {
                            return $data;
                        }
                    }
                }
            }
        }

        // Fallback: Direct VPC / Same-Host DB Bridge if HTTP server is unreachable
        return self::directVpcFallback($payload);
    }

    /**
     * Same-host / VPC deterministic fallback when local HTTP server is not listening.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>|null
     */
    private static function directVpcFallback(array $payload): ?array
    {
        try {
            $host = (string) ($_ENV['PARTY_DB_HOST'] ?? $_ENV['DB_HOST'] ?? '127.0.0.1');
            $port = (string) ($_ENV['PARTY_DB_PORT'] ?? $_ENV['DB_PORT'] ?? '3306');
            $dbName = (string) ($_ENV['PARTY_DB_NAME'] ?? 'partyplatform');
            $user = (string) ($_ENV['PARTY_DB_USER'] ?? $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? 'root');
            $pass = (string) ($_ENV['PARTY_DB_PASS'] ?? $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? '');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $partyPdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $hostEmail = strtolower(trim((string) ($payload['host_email'] ?? '')));
            $hostFirst = trim(strip_tags((string) ($payload['host_first_name'] ?? 'Family')));
            $hostLast  = trim(strip_tags((string) ($payload['host_last_name'] ?? 'Host')));
            $hostMob   = trim(strip_tags((string) ($payload['host_mobile'] ?? '')));
            $eventName = trim(strip_tags((string) ($payload['event_name'] ?? 'Family Gathering')));
            $eventDate = trim((string) ($payload['event_date'] ?? date('Y-m-d')));
            $eventType = trim(strip_tags((string) ($payload['event_type'] ?? 'Party')));
            $eventDesc = trim(strip_tags((string) ($payload['event_description'] ?? '')));
            $existingId = trim((string) ($payload['existing_party_event_id'] ?? ''));

            // 1. Resolve or create manager
            $stmtMgr = $partyPdo->prepare('SELECT * FROM account WHERE LOWER(TRIM(email)) = :email AND deleted_at IS NULL LIMIT 1');
            $stmtMgr->execute(['email' => $hostEmail]);
            $mgrRow = $stmtMgr->fetch(PDO::FETCH_ASSOC);

            if (is_array($mgrRow) && !empty($mgrRow['id'])) {
                $managerId = (string) $mgrRow['id'];
            } else {
                $managerId = self::randomCode(7);
                $insMgr = $partyPdo->prepare(
                    'INSERT INTO account (
                        id, email, email_verified_at, password, firstName, lastName, mobile,
                        provider, status, type, created_at
                    ) VALUES (
                        :id, :email, NOW(), :password, :first, :last, :mobile,
                        :provider, :status, :type, NOW()
                    )'
                );
                $insMgr->execute([
                    'id'       => $managerId,
                    'email'    => $hostEmail,
                    'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    'first'    => $hostFirst !== '' ? $hostFirst : 'Family',
                    'last'     => $hostLast !== '' ? $hostLast : 'Member',
                    'mobile'   => $hostMob !== '' ? $hostMob : '0000000000',
                    'provider' => 'familyplatform',
                    'status'   => 'active',
                    'type'     => 'user',
                ]);
            }

            // 2. Resolve or create event
            $eventRow = null;
            if ($existingId !== '') {
                $stmtEv = $partyPdo->prepare('SELECT * FROM events WHERE event_id = :eid LIMIT 1');
                $stmtEv->execute(['eid' => $existingId]);
                $found = $stmtEv->fetch(PDO::FETCH_ASSOC);
                if (is_array($found)) {
                    $eventRow = $found;
                }
            }

            if ($eventRow === null) {
                $eventCode = self::randomCode(7);
                $slug = self::uniquePartySlug($partyPdo, $eventName);
                $shortDesc = $eventDesc !== '' && mb_strlen($eventDesc) >= 10
                    ? mb_substr($eventDesc, 0, 250)
                    : mb_substr("Family {$eventType}: {$eventName} hosted by {$hostFirst} {$hostLast}.", 0, 250);
                $inviteToken = bin2hex(random_bytes(12));

                $insEv = $partyPdo->prepare(
                    'INSERT INTO events (
                        event_name, event_id, manager_id, manager_email, event_type,
                        start_date, visibility, event_visibility, is_active, status,
                        currency, timezone, slug, short_description, description,
                        invitation_direction, invitation_layout, invitation_palette,
                        show_attendee_count, show_attendee_names, attendee_count_threshold,
                        invite_token, invite_token_rotated_at, created_at
                    ) VALUES (
                        :event_name, :event_id, :manager_id, :manager_email, :event_type,
                        :start_date, :visibility, :event_visibility, 1, :status,
                        :currency, :timezone, :slug, :short_description, :description,
                        :inv_dir, :inv_layout, :inv_palette,
                        1, 1, 5,
                        :invite_token, NOW(), NOW()
                    )'
                );
                $insEv->execute([
                    'event_name'        => mb_substr($eventName, 0, 200),
                    'event_id'          => $eventCode,
                    'manager_id'        => $managerId,
                    'manager_email'     => mb_substr($hostEmail, 0, 80),
                    'event_type'        => $eventType !== '' ? $eventType : 'Party',
                    'start_date'        => $eventDate !== '' ? $eventDate : date('Y-m-d', strtotime('+14 days')),
                    'visibility'        => 'private',
                    'event_visibility'  => 'private',
                    'status'            => 'active',
                    'currency'          => 'GBP',
                    'timezone'          => 'Europe/London',
                    'slug'              => $slug,
                    'short_description' => $shortDesc,
                    'description'       => $eventDesc !== '' ? $eventDesc : $shortDesc,
                    'inv_dir'           => 'royal_luxe_iv',
                    'inv_layout'        => 'top_hero',
                    'inv_palette'       => 'royal_burgundy',
                    'invite_token'      => $inviteToken,
                ]);
            } else {
                $eventCode   = (string) $eventRow['event_id'];
                $slug        = (string) ($eventRow['slug'] ?? '');
                $inviteToken = (string) ($eventRow['invite_token'] ?? bin2hex(random_bytes(12)));
            }

            // 3. Seed prospects idempotently
            $prospects = is_array($payload['kinship_prospects'] ?? null) ? $payload['kinship_prospects'] : [];
            $stmtExisting = $partyPdo->prepare('SELECT first_name, email FROM guest_prospects WHERE event_id = :e');
            $stmtExisting->execute(['e' => $eventCode]);
            $seen = [];
            foreach ($stmtExisting->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $ef = mb_strtolower(trim((string) ($r['first_name'] ?? '')));
                $ee = mb_strtolower(trim((string) ($r['email'] ?? '')));
                $seen[$ef . '|' . $ee] = true;
                if ($ee !== '') {
                    $seen['e:' . $ee] = true;
                }
            }

            $stmtInsP = $partyPdo->prepare(
                'INSERT INTO guest_prospects (
                    event_id, first_name, last_name, email, mobile,
                    guest_category, guest_type, party_size, invitation_reasons,
                    closeness, contact_frequency, always_invite, always_invite_locked,
                    priority_score, priority_band, builder_status, notes, source
                ) VALUES (
                    :event_id, :first_name, :last_name, :email, :mobile,
                    :guest_category, :guest_type, 1, :invitation_reasons,
                    :closeness, 4, :always_invite, 0,
                    :priority_score, :priority_band, :builder_status, :notes, :source
                )'
            );

            $seededCount = 0;
            foreach (array_slice($prospects, 0, 500) as $raw) {
                if (!is_array($raw)) {
                    continue;
                }
                $first = trim(strip_tags((string) ($raw['first_name'] ?? '')));
                $last  = trim(strip_tags((string) ($raw['last_name'] ?? '')));
                if ($first === '') {
                    continue;
                }
                $emailRaw = strtolower(trim((string) ($raw['email'] ?? '')));
                $email = ($emailRaw !== '' && filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) ? $emailRaw : null;

                $idKey = mb_strtolower($first) . '|' . ($email ?? '');
                if (isset($seen[$idKey]) || ($email !== null && isset($seen['e:' . $email]))) {
                    continue;
                }

                $cat = trim((string) ($raw['guest_category'] ?? 'Family'));
                $always = !empty($raw['always_invite']) ? 1 : 0;
                $closeness = isset($raw['closeness']) && is_numeric($raw['closeness']) ? (int) $raw['closeness'] : 4;
                $score = $cat === 'Close Family' ? 88 : ($cat === 'Family' ? 76 : 58);
                $band  = $score >= 80 ? 'high' : 'consider';
                $title = trim(strip_tags((string) ($raw['kinship_title'] ?? 'Relative')));
                $reasons = is_array($raw['invitation_reasons'] ?? null) ? $raw['invitation_reasons'] : ['immediate_family'];
                $mob = trim(strip_tags((string) ($raw['mobile'] ?? '')));

                $stmtInsP->execute([
                    'event_id'           => $eventCode,
                    'first_name'         => mb_substr($first, 0, 100),
                    'last_name'          => $last !== '' ? mb_substr($last, 0, 100) : null,
                    'email'              => $email !== null ? mb_substr($email, 0, 120) : null,
                    'mobile'             => $mob !== '' ? mb_substr($mob, 0, 50) : null,
                    'guest_category'     => $cat,
                    'guest_type'         => 'adult',
                    'invitation_reasons' => json_encode(array_values($reasons), JSON_THROW_ON_ERROR),
                    'closeness'          => $closeness,
                    'always_invite'      => $always,
                    'priority_score'     => $score,
                    'priority_band'      => $band,
                    'builder_status'     => $always === 1 ? 'shortlisted' : 'potential',
                    'notes'              => mb_substr("Synced from FamilyPlatform Tree ({$title})", 0, 250),
                    'source'             => 'family_tree',
                ]);

                $seen[$idKey] = true;
                if ($email !== null) {
                    $seen['e:' . $email] = true;
                }
                $seededCount++;
            }

            $baseUrl = self::resolvePartyBaseUrl();

            return [
                'party_event_id'         => $eventCode,
                'slug'                   => $slug,
                'invite_token'           => $inviteToken,
                'rsvp_url'               => $baseUrl . '/e/' . rawurlencode($slug),
                'card_url'               => $baseUrl . '/celebrate/card?event_id=' . rawurlencode($eventCode),
                'studio_url'             => $baseUrl . '/guest-builder/' . rawurlencode($eventCode),
                'manager_id'             => $managerId,
                'seeded_prospects_count' => $seededCount,
            ];
        } catch (\Throwable $e) {
            error_log('[PartyPlatformBridgeService::directVpcFallback] ' . $e->getMessage());
            return null;
        }
    }

    private static function randomCode(int $len = 7): string
    {
        $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $bytes = random_bytes($len);
        $code = '';
        for ($i = 0; $i < $len; $i++) {
            $code .= $alphabet[ord($bytes[$i]) % strlen($alphabet)];
        }
        return $code;
    }

    private static function uniquePartySlug(PDO $db, string $eventName): string
    {
        $text = trim(mb_strtolower($eventName, 'UTF-8'));
        $text = (string) preg_replace("/['’]s\b/u", '', $text);
        $text = str_replace(["'", "’"], '', $text);
        $text = (string) preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = (string) preg_replace('~[^-\w]+~', '', $text);
        $base = trim($text, '-') ?: 'family-event';

        $slug = $base;
        $i = 2;
        $stmt = $db->prepare('SELECT 1 FROM events WHERE slug = :s LIMIT 1');
        while (true) {
            $stmt->execute(['s' => $slug]);
            if (!$stmt->fetchColumn()) {
                break;
            }
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
