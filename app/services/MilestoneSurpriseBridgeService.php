<?php

declare(strict_types=1);

namespace App\services;

use InvalidArgumentException;
use PDO;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;

/**
 * Coordinates cross-app Surprise Celebration Cards & Goodwill Video Tributes
 * between FamilyPlatform Milestones (Birthdays & Wedding Anniversaries) and
 * PartyPlatform Standalone (Non-RSVP) Celebration Workspaces.
 *
 * Security & Privacy Invariants:
 * 1. Celebrants (and both partners on an anniversary) are strictly blocked (403)
 *    and never receive surprise CTA metadata.
 * 2. Cross-family IDOR attempts are strictly blocked (403).
 * 3. First relative clicking authenticates/registers on PartyPlatform to claim
 *    and auto-provision ONE unified standalone event (Group Card + Goodwill Video).
 * 4. Every subsequent relative is automatically routed straight to the shared
 *    card-signing or goodwill-video recording screen without account creation.
 */
final class MilestoneSurpriseBridgeService
{
    private const S2S_TIMEOUT_MS = 2000;

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
        self::ensureSchema($this->db);
    }

    /**
     * Idempotently ensure `milestone_party_links` exists.
     */
    public static function ensureSchema(?PDO $db = null): void
    {
        $pdo = $db ?? Db::connect2();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS `milestone_party_links` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `milestone_key` VARCHAR(190) NOT NULL,
                `family_code` VARCHAR(64) NOT NULL,
                `milestone_type` VARCHAR(32) NOT NULL,
                `entity_id` VARCHAR(128) NOT NULL,
                `celebration_year` INT UNSIGNED NOT NULL,
                `party_event_id` VARCHAR(20) NOT NULL,
                `party_manager_id` VARCHAR(64) NOT NULL DEFAULT \'\',
                `claimed_by_user_id` VARCHAR(64) NOT NULL DEFAULT \'\',
                `card_guest_url` VARCHAR(255) NOT NULL,
                `goodwill_guest_url` VARCHAR(255) NOT NULL,
                `card_manage_url` VARCHAR(255) NOT NULL DEFAULT \'\',
                `goodwill_manage_url` VARCHAR(255) NOT NULL DEFAULT \'\',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_milestone_key` (`milestone_key`),
                UNIQUE KEY `uq_family_milestone_year` (`family_code`, `milestone_type`, `entity_id`, `celebration_year`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Normalise family code without leading '#'.
     */
    public static function normalizeFamilyCode(string $familyCode): string
    {
        return strtoupper(str_replace('#', '', trim($familyCode)));
    }

    /**
     * Deterministic milestone key shared across FamilyPlatform and PartyPlatform.
     * Keyed by canonical milestone_type + entity_id + celebration_year so dual-lineage
     * relatives (birth famCode + married familyCode2) always share one unified celebration.
     */
    public static function buildMilestoneKey(
        string $familyCode,
        string $milestoneType,
        string $entityId,
        int $celebrationYear
    ): string {
        $normType = strtolower(trim($milestoneType));
        $normEnt  = trim($entityId);

        return "MILESTONE:{$normType}:{$normEnt}:{$celebrationYear}";
    }

    /**
     * Compute HMAC-SHA256 claim signature matching PartyPlatform's StandaloneMilestoneClaimController.
     *
     * @param array<string, string> $params
     */
    public static function signClaimParams(array $params, ?string $secret = null): string
    {
        $key = $secret ?? PartyPlatformBridgeService::resolveBridgeSecret();
        $orderedKeys = [
            'milestone_key',
            'milestone_type',
            'entity_id',
            'family_code',
            'celebration_year',
            'title',
            'celebrant_name',
            'event_date',
            'feature',
            'initiator_user_id',
            'callback_url',
            'ts',
        ];
        $parts = ['milestone_claim_v1'];
        foreach ($orderedKeys as $k) {
            $parts[] = $k . '=' . ($params[$k] ?? '');
        }

        return hash_hmac('sha256', implode('|', $parts), $key);
    }

    /**
     * Verify incoming S2S callback signature from PartyPlatform.
     */
    public static function verifyEcosystemSignature(string $rawBody, ?string $timestampHeader, ?string $signatureHeader): bool
    {
        if ($timestampHeader === null || $signatureHeader === null || trim($timestampHeader) === '' || trim($signatureHeader) === '') {
            return false;
        }
        if (!ctype_digit(trim($timestampHeader))) {
            return false;
        }
        $ts = (int) trim($timestampHeader);
        if (abs(time() - $ts) > 300) {
            return false;
        }
        $expected = PartyPlatformBridgeService::signPayload($ts, $rawBody);

        return hash_equals($expected, trim($signatureHeader));
    }

    /**
     * Batch-fetch existing milestone links by key to avoid N+1 queries on feed load.
     *
     * @param array<int, string> $milestoneKeys
     * @return array<string, array<string, mixed>>
     */
    public function findLinksForKeys(array $milestoneKeys): array
    {
        $cleanKeys = array_values(array_unique(array_filter(array_map('trim', $milestoneKeys), static fn (string $k): bool => $k !== '')));
        if ($cleanKeys === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cleanKeys), '?'));
        $sql = sprintf('SELECT * FROM milestone_party_links WHERE milestone_key IN (%s)', $placeholders);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($cleanKeys);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $map = [];
        foreach ($rows as $row) {
            $k = (string) ($row['milestone_key'] ?? '');
            if ($k !== '') {
                $map[$k] = $row;
            }
        }

        return $map;
    }

    /**
     * Validate and persist a PartyPlatform S2S milestone claim callback.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function recordCallbackPayload(array $payload): array
    {
        $milestoneKey   = trim((string) ($payload['milestone_key'] ?? ''));
        $familyCode     = self::normalizeFamilyCode((string) ($payload['family_code'] ?? ''));
        $milestoneType  = strtolower(trim((string) ($payload['milestone_type'] ?? '')));
        $entityId       = trim((string) ($payload['entity_id'] ?? ''));
        $year           = (int) ($payload['celebration_year'] ?? 0);
        $partyEventId   = trim((string) ($payload['party_event_id'] ?? ''));
        $partyManagerId = trim((string) ($payload['party_manager_id'] ?? ''));
        $claimedBy      = trim((string) ($payload['claimed_by_user_id'] ?? ''));
        $cardGuestUrl   = trim((string) ($payload['card_guest_url'] ?? ''));
        $goodwillUrl    = trim((string) ($payload['goodwill_guest_url'] ?? ''));
        $cardManageUrl  = trim((string) ($payload['card_manage_url'] ?? ''));
        $gwManageUrl    = trim((string) ($payload['goodwill_manage_url'] ?? ''));

        if (
            $milestoneKey === ''
            || $familyCode === ''
            || $entityId === ''
            || $year < 2020
            || $partyEventId === ''
            || !in_array($milestoneType, ['birthday', 'anniversary'], true)
        ) {
            throw new InvalidArgumentException('Missing or invalid milestone binding fields.');
        }

        // Strict relative-path validation to prevent open-redirect or XSS payload injection
        if (
            !str_starts_with($cardGuestUrl, '/card/')
            || str_starts_with($cardGuestUrl, '//')
            || preg_match('/[\r\n\0]/', $cardGuestUrl)
            || !str_starts_with($goodwillUrl, '/goodwill/video')
            || str_starts_with($goodwillUrl, '//')
            || preg_match('/[\r\n\0]/', $goodwillUrl)
        ) {
            throw new InvalidArgumentException('Invalid PartyPlatform contributor relative URLs.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO milestone_party_links (
                milestone_key,
                family_code,
                milestone_type,
                entity_id,
                celebration_year,
                party_event_id,
                party_manager_id,
                claimed_by_user_id,
                card_guest_url,
                goodwill_guest_url,
                card_manage_url,
                goodwill_manage_url
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            mb_substr($milestoneKey, 0, 190),
            mb_substr($familyCode, 0, 64),
            $milestoneType,
            mb_substr($entityId, 0, 128),
            $year,
            mb_substr($partyEventId, 0, 20),
            mb_substr($partyManagerId, 0, 64),
            mb_substr($claimedBy, 0, 64),
            mb_substr($cardGuestUrl, 0, 255),
            mb_substr($goodwillUrl, 0, 255),
            mb_substr($cardManageUrl, 0, 255),
            mb_substr($gwManageUrl, 0, 255),
        ]);

        $sel = $this->db->prepare('SELECT * FROM milestone_party_links WHERE milestone_key = ? LIMIT 1');
        $sel->execute([$milestoneKey]);
        $saved = $sel->fetch(PDO::FETCH_ASSOC);

        return is_array($saved) ? $saved : [];
    }

    /**
     * Authorise viewer, enforce celebrant/partner surprise shield, and return the
     * target PartyPlatform redirect URL (either direct contributor link if claimed,
     * or signed standalone claim handoff if unclaimed).
     *
     * @param array<int, string> $viewerFamCodes
     */
    public function resolveLaunchRedirectUrl(
        string $viewerUserId,
        array $viewerFamCodes,
        string $milestoneType,
        string $entityId,
        string $feature
    ): string {
        $cleanViewerId = trim($viewerUserId);
        $cleanType     = strtolower(trim($milestoneType));
        $cleanEntityId = trim($entityId);
        $rawFeature    = strtolower(trim($feature));
        $cleanFeature  = in_array($rawFeature, ['card', 'goodwill', 'manage_cards', 'manage_videos'], true)
            ? $rawFeature
            : 'card';

        if ($cleanViewerId === '') {
            throw new ForbiddenException('Authentication is required.');
        }
        if (!in_array($cleanType, ['birthday', 'anniversary'], true) || $cleanEntityId === '') {
            throw new InvalidArgumentException('Invalid milestone parameters.');
        }

        $viewerAllowedCodes = $this->resolveUserFamilyCodes($cleanViewerId, $viewerFamCodes);
        if ($viewerAllowedCodes === []) {
            throw new ForbiddenException('No active family membership found.');
        }

        $resolved = $cleanType === 'birthday'
            ? $this->authorizeAndResolveBirthday($cleanViewerId, $viewerAllowedCodes, $cleanEntityId)
            : $this->authorizeAndResolveAnniversary($cleanViewerId, $viewerAllowedCodes, $cleanEntityId);

        $milestoneKey = self::buildMilestoneKey(
            $resolved['family_code'],
            $cleanType,
            $cleanEntityId,
            $resolved['celebration_year']
        );

        $partyBaseUrl = PartyPlatformBridgeService::resolvePartyBaseUrl();

        // 1. Check local FamilyPlatform link table first
        $links = $this->findLinksForKeys([$milestoneKey]);
        $link  = $links[$milestoneKey] ?? null;

        // 2. If not yet recorded locally, check PartyPlatform ledger (handles race / in-flight webhook)
        if ($link === null) {
            $link = $this->syncFromPartyPlatformIfClaimed($milestoneKey, $resolved, $cleanType, $cleanEntityId);
        }

        // 2b. Organiser-only host management / reveal actions
        if (in_array($cleanFeature, ['manage_cards', 'manage_videos'], true)) {
            if ($link === null) {
                throw new NotFoundException('This milestone celebration has not been started yet.');
            }
            $claimedBy = trim((string) ($link['claimed_by_user_id'] ?? ''));
            if ($claimedBy === '' || $claimedBy !== $cleanViewerId) {
                throw new ForbiddenException('Only the relative who started this surprise celebration can open the organiser studio.');
            }
            $partyEventId = trim((string) ($link['party_event_id'] ?? ''));
            $manageRel = $cleanFeature === 'manage_videos'
                ? ((string) ($link['goodwill_manage_url'] ?? '') ?: '/manager/goodwill-videos?event_id=' . rawurlencode($partyEventId))
                : ((string) ($link['card_manage_url'] ?? '') ?: '/manager/celebratory-cards?event_id=' . rawurlencode($partyEventId));

            if (!str_starts_with($manageRel, '/') || str_starts_with($manageRel, '//')) {
                throw new InvalidArgumentException('Invalid management destination.');
            }
            return $partyBaseUrl . $manageRel;
        }

        // 3. Already claimed! Route subsequent relatives straight to the shared card or goodwill video screen
        if ($link !== null) {
            $relPath = $cleanFeature === 'goodwill'
                ? (string) ($link['goodwill_guest_url'] ?? '')
                : (string) ($link['card_guest_url'] ?? '');

            if ($relPath !== '' && str_starts_with($relPath, '/')) {
                return $partyBaseUrl . $relPath;
            }
        }

        // 4. Unclaimed: first relative clicks -> redirect to PartyPlatform standalone auth & claim flow
        $fpBaseUrl = rtrim((string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
        $claimParams = [
            'milestone_key'     => $milestoneKey,
            'milestone_type'    => $cleanType,
            'entity_id'         => $cleanEntityId,
            'family_code'       => $resolved['family_code'],
            'celebration_year'  => (string) $resolved['celebration_year'],
            'title'             => $resolved['title'],
            'celebrant_name'    => $resolved['celebrant_name'],
            'event_date'        => $resolved['event_date'],
            'feature'           => $cleanFeature,
            'initiator_user_id' => $cleanViewerId,
            'callback_url'      => $fpBaseUrl . '/api/v1/ecosystem/milestone-link-callback',
            'ts'                => (string) time(),
        ];
        $claimParams['sig'] = self::signClaimParams($claimParams);

        return $partyBaseUrl . '/standalone/family-milestone-claim?' . http_build_query($claimParams);
    }

    /**
     * Resolve all normalised family codes the viewer is authorised to access.
     *
     * @param array<int, string> $sessionFamCodes
     * @return list<string>
     */
    public function resolveUserFamilyCodes(string $userId, array $sessionFamCodes = []): array
    {
        $codes = [];
        foreach ($sessionFamCodes as $c) {
            $norm = self::normalizeFamilyCode((string) $c);
            if ($norm !== '') {
                $codes[$norm] = true;
            }
        }

        $stmt = $this->db->prepare('SELECT famCode, familyCode2 FROM personal WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) {
            foreach (['famCode', 'familyCode2'] as $col) {
                $norm = self::normalizeFamilyCode((string) ($row[$col] ?? ''));
                if ($norm !== '') {
                    $codes[$norm] = true;
                }
            }
        }

        try {
            $ufStmt = $this->db->prepare("SELECT family_code FROM user_families WHERE user_id = ? AND status = 'approved'");
            $ufStmt->execute([$userId]);
            foreach ($ufStmt->fetchAll(PDO::FETCH_COLUMN) as $fc) {
                $norm = self::normalizeFamilyCode((string) $fc);
                if ($norm !== '') {
                    $codes[$norm] = true;
                }
            }
        } catch (\Throwable) {
        }

        return array_keys($codes);
    }

    /**
     * @param list<string> $viewerCodes
     * @return array{family_code: string, celebration_year: int, event_date: string, title: string, celebrant_name: string}
     */
    private function authorizeAndResolveBirthday(string $viewerUserId, array $viewerCodes, string $targetUserId): array
    {
        if ($viewerUserId === $targetUserId) {
            throw new ForbiddenException('Surprise birthday cards and goodwill videos are hidden from the celebrant.');
        }

        $stmt = $this->db->prepare(
            'SELECT id, firstName, lastName, day, month, famCode, familyCode2
             FROM personal
             WHERE id = ? AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute([$targetUserId]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($person)) {
            throw new NotFoundException('Birthday celebrant not found.');
        }

        $targetCodes = $this->resolveUserFamilyCodes($targetUserId, [
            (string) ($person['famCode'] ?? ''),
            (string) ($person['familyCode2'] ?? ''),
        ]);

        $shared = array_values(array_intersect($viewerCodes, $targetCodes));
        if ($shared === []) {
            throw new ForbiddenException('Access denied: this milestone belongs to another family circle.');
        }

        $canonicalFamCode = self::normalizeFamilyCode((string) ($person['famCode'] ?? ''));
        if ($canonicalFamCode === '') {
            $canonicalFamCode = $shared[0];
        }

        $fullName = trim(((string) ($person['firstName'] ?? '')) . ' ' . ((string) ($person['lastName'] ?? '')));
        if ($fullName === '') {
            $fullName = 'Family Member';
        }

        [$year, $isoDate] = $this->computeNextOccurrence((int) ($person['day'] ?? 1), $this->parseMonth((string) ($person['month'] ?? '1')));

        return [
            'family_code'      => $canonicalFamCode,
            'celebration_year' => $year,
            'event_date'       => $isoDate,
            'title'            => "{$fullName}'s Birthday",
            'celebrant_name'   => $fullName,
        ];
    }

    /**
     * @param list<string> $viewerCodes
     * @return array{family_code: string, celebration_year: int, event_date: string, title: string, celebrant_name: string}
     */
    private function authorizeAndResolveAnniversary(string $viewerUserId, array $viewerCodes, string $unionId): array
    {
        if (!ctype_digit($unionId)) {
            throw new InvalidArgumentException('Invalid union identifier.');
        }

        $stmt = $this->db->prepare(
            "SELECT fu.*,
                    COALESCE(fn1.user_id, '') AS p1_user_id,
                    COALESCE(fn2.user_id, '') AS p2_user_id,
                    COALESCE(fn1.family_code, '') AS p1_family_code,
                    COALESCE(fn2.family_code, '') AS p2_family_code,
                    TRIM(CONCAT(COALESCE(fn1.first_name, ''), ' ', COALESCE(fn1.last_name, ''))) AS p1_name,
                    TRIM(CONCAT(COALESCE(fn2.first_name, ''), ' ', COALESCE(fn2.last_name, ''))) AS p2_name
             FROM family_unions fu
             LEFT JOIN family_nodes fn1 ON fu.partner_1_id = fn1.id
             LEFT JOIN family_nodes fn2 ON fu.partner_2_id = fn2.id
             WHERE fu.id = ? AND fu.is_current = 1
             LIMIT 1"
        );
        $stmt->execute([(int) $unionId]);
        $union = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($union)) {
            throw new NotFoundException('Wedding anniversary milestone not found.');
        }

        $p1Uid = trim((string) ($union['p1_user_id'] ?? ''));
        $p2Uid = trim((string) ($union['p2_user_id'] ?? ''));

        if (($p1Uid !== '' && $viewerUserId === $p1Uid) || ($p2Uid !== '' && $viewerUserId === $p2Uid)) {
            throw new ForbiddenException('Surprise anniversary cards and goodwill videos are hidden from the celebrating couple.');
        }

        $unionCodes = [];
        foreach (['family_code', 'p1_family_code', 'p2_family_code'] as $col) {
            $norm = self::normalizeFamilyCode((string) ($union[$col] ?? ''));
            if ($norm !== '') {
                $unionCodes[$norm] = true;
            }
        }
        if ($p1Uid !== '') {
            foreach ($this->resolveUserFamilyCodes($p1Uid) as $c) {
                $unionCodes[$c] = true;
            }
        }
        if ($p2Uid !== '') {
            foreach ($this->resolveUserFamilyCodes($p2Uid) as $c) {
                $unionCodes[$c] = true;
            }
        }

        $shared = array_values(array_intersect($viewerCodes, array_keys($unionCodes)));
        if ($shared === []) {
            throw new ForbiddenException('Access denied: this anniversary belongs to another family circle.');
        }

        $canonicalFamCode = self::normalizeFamilyCode((string) ($union['family_code'] ?? ''));
        if ($canonicalFamCode === '') {
            $canonicalFamCode = $shared[0];
        }

        $p1Name = trim((string) ($union['p1_name'] ?? ''));
        $p2Name = trim((string) ($union['p2_name'] ?? ''));
        $coupleName = ($p1Name !== '' && $p2Name !== '')
            ? "{$p1Name} & {$p2Name}"
            : ($p1Name !== '' ? $p1Name : ($p2Name !== '' ? $p2Name : 'Family Couple'));

        $mDate = trim((string) ($union['marriage_date'] ?? ''));
        $mTime = $mDate !== '' ? strtotime($mDate) : false;
        $day   = $mTime !== false ? (int) date('j', $mTime) : 1;
        $month = $mTime !== false ? (int) date('n', $mTime) : 1;

        [$year, $isoDate] = $this->computeNextOccurrence($day, $month);

        return [
            'family_code'      => $canonicalFamCode,
            'celebration_year' => $year,
            'event_date'       => $isoDate,
            'title'            => "{$coupleName}'s Anniversary",
            'celebrant_name'   => $coupleName,
        ];
    }

    /**
     * Check PartyPlatform (via S2S HTTP or same-host VPC DB) if a milestone was claimed,
     * and cache locally into `milestone_party_links` if found.
     *
     * @param array{family_code: string, celebration_year: int, event_date: string, title: string, celebrant_name: string} $resolved
     * @return array<string, mixed>|null
     */
    private function syncFromPartyPlatformIfClaimed(
        string $milestoneKey,
        array $resolved,
        string $milestoneType,
        string $entityId
    ): ?array {
        $url = PartyPlatformBridgeService::resolvePartyBaseUrl()
            . '/api/v1/ecosystem/milestone-claim-status?milestone_key=' . rawurlencode($milestoneKey);

        if (function_exists('curl_init')) {
            $ts  = time();
            $sig = PartyPlatformBridgeService::signPayload($ts, $milestoneKey);
            $ch  = curl_init($url);
            if ($ch !== false) {
                $parsedHost = strtolower((string) parse_url($url, PHP_URL_HOST));
                $isLocalDev = $parsedHost === 'localhost'
                    || $parsedHost === '127.0.0.1'
                    || str_ends_with($parsedHost, '.test')
                    || str_ends_with($parsedHost, '.local');

                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT_MS     => self::S2S_TIMEOUT_MS,
                    CURLOPT_CONNECTTIMEOUT => 1,
                    CURLOPT_SSL_VERIFYPEER => !$isLocalDev,
                    CURLOPT_SSL_VERIFYHOST => $isLocalDev ? 0 : 2,
                    CURLOPT_HTTPHEADER     => [
                        'Accept: application/json',
                        'X-Ecosystem-Timestamp: ' . $ts,
                        'X-Ecosystem-Signature: ' . $sig,
                    ],
                ]);
                $resp     = curl_exec($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if (is_string($resp) && $httpCode === 200) {
                    $decoded = json_decode($resp, true);
                    $data    = is_array($decoded) ? ($decoded['data'] ?? null) : null;
                    if (is_array($data) && !empty($data['claimed']) && !empty($data['party_event_id'])) {
                        return $this->recordCallbackPayload([
                            'milestone_key'       => $milestoneKey,
                            'family_code'         => $resolved['family_code'],
                            'milestone_type'      => $milestoneType,
                            'entity_id'           => $entityId,
                            'celebration_year'    => $resolved['celebration_year'],
                            'party_event_id'      => (string) $data['party_event_id'],
                            'party_manager_id'    => (string) ($data['manager_id'] ?? ''),
                            'claimed_by_user_id'  => '',
                            'card_guest_url'      => (string) ($data['card_guest_url'] ?? ''),
                            'goodwill_guest_url'  => (string) ($data['goodwill_guest_url'] ?? ''),
                            'card_manage_url'     => (string) ($data['card_manage_url'] ?? ''),
                            'goodwill_manage_url' => (string) ($data['goodwill_manage_url'] ?? ''),
                        ]);
                    }
                }
            }
        }

        // Fallback: Same-host VPC check on PartyPlatform's milestone_claims_ledger
        try {
            $host   = (string) ($_ENV['PARTY_DB_HOST'] ?? $_ENV['DB_HOST'] ?? '127.0.0.1');
            $port   = (string) ($_ENV['PARTY_DB_PORT'] ?? $_ENV['DB_PORT'] ?? '3306');
            $dbName = (string) ($_ENV['PARTY_DB_NAME'] ?? 'partyplatform');
            $user   = (string) ($_ENV['PARTY_DB_USER'] ?? $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? 'root');
            $pass   = (string) ($_ENV['PARTY_DB_PASS'] ?? $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? '');

            $partyPdo = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $stmt = $partyPdo->prepare('SELECT * FROM milestone_claims_ledger WHERE milestone_key = ? LIMIT 1');
            $stmt->execute([$milestoneKey]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($row) && !empty($row['event_id'])) {
                return $this->recordCallbackPayload([
                    'milestone_key'       => $milestoneKey,
                    'family_code'         => $resolved['family_code'],
                    'milestone_type'      => $milestoneType,
                    'entity_id'           => $entityId,
                    'celebration_year'    => $resolved['celebration_year'],
                    'party_event_id'      => (string) $row['event_id'],
                    'party_manager_id'    => (string) ($row['manager_id'] ?? ''),
                    'claimed_by_user_id'  => '',
                    'card_guest_url'      => (string) ($row['card_guest_url'] ?? ''),
                    'goodwill_guest_url'  => (string) ($row['goodwill_guest_url'] ?? ''),
                    'card_manage_url'     => (string) ($row['card_manage_url'] ?? ''),
                    'goodwill_manage_url' => (string) ($row['goodwill_manage_url'] ?? ''),
                ]);
            }
        } catch (\Throwable) {
        }

        return null;
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function computeNextOccurrence(int $day, int $month): array
    {
        $today   = new \DateTimeImmutable('today');
        $curYear = (int) $today->format('Y');
        $safeM   = ($month >= 1 && $month <= 12) ? $month : 1;
        $safeD   = ($day >= 1 && $day <= 31) ? $day : 1;

        if (!checkdate($safeM, $safeD, $curYear)) {
            $safeD = 28;
        }
        $candidate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $curYear, $safeM, $safeD));
        if ($candidate < $today) {
            $nextYear = $curYear + 1;
            if (!checkdate($safeM, $safeD, $nextYear)) {
                $safeD = 28;
            }
            $candidate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $nextYear, $safeM, $safeD));
        }

        return [(int) $candidate->format('Y'), $candidate->format('Y-m-d')];
    }

    private function parseMonth(string $month): int
    {
        if (is_numeric($month)) {
            $m = (int) $month;
            return ($m >= 1 && $m <= 12) ? $m : 1;
        }
        $map = [
            'january' => 1, 'jan' => 1, 'february' => 2, 'feb' => 2,
            'march' => 3, 'mar' => 3, 'april' => 4, 'apr' => 4,
            'may' => 5, 'june' => 6, 'jun' => 6, 'july' => 7, 'jul' => 7,
            'august' => 8, 'aug' => 8, 'september' => 9, 'sep' => 9, 'sept' => 9,
            'october' => 10, 'oct' => 10, 'november' => 11, 'nov' => 11,
            'december' => 12, 'dec' => 12,
        ];
        return $map[strtolower(trim($month))] ?? 1;
    }
}
