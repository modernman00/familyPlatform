<?php

declare(strict_types=1);

namespace App\services;

use App\model\AllMembersData;
use App\repository\FeedRepository;
use PDO;
use Src\Db;
use Throwable;

/**
 * KinshipHonorificService
 *
 * Projects viewer-relative cultural kinship honorifics (e.g. Dad, Mum, Grandpa,
 * Grandma, Great-Grandpa, Great-Grandma, Uncle, Aunty) onto @mentions and post
 * tags at display time without mutating canonical stored text.
 *
 * Core Invariants:
 * 1. Canonical storage remains plain "@First Last" in posts and comments.
 * 2. Only elder & marital-elder relationships receive honorific prefixes;
 *    same-generation kin (siblings, cousins, spouses), younger kin, and self-views
 *    remain plain full names.
 * 3. Respects each viewer's personal `contact.kinship_honorifics` preference
 *    ('on' by default, 'off' for full names only).
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class KinshipHonorificService
{
    /** @var array<int, bool> */
    private static array $ensuredConnections = [];

    /** @var array<string, string> */
    private static array $viewerPreferenceCache = [];

    /**
     * Ensure `contact.kinship_honorifics` exists idempotently once per connection.
     */
    public static function ensureSchema(?PDO $pdo = null): void
    {
        try {
            $db = $pdo ?? Db::connect2();
            $connId = spl_object_id($db);
            if (isset(self::$ensuredConnections[$connId])) {
                return;
            }
            self::$ensuredConnections[$connId] = true;

            $chk = $db->query("SHOW COLUMNS FROM `contact` LIKE 'kinship_honorifics'");
            if ($chk && !$chk->fetch()) {
                $db->exec(
                    "ALTER TABLE `contact` ADD COLUMN `kinship_honorifics` VARCHAR(10) NOT NULL DEFAULT 'on' AFTER `sms_notifications`"
                );
            }
        } catch (Throwable $e) {
            error_log('[KinshipHonorificService::ensureSchema] ' . $e->getMessage());
        }
    }

    /**
     * Clear in-process viewer preference cache (used after Settings updates & in tests).
     */
    public static function clearPreferenceCache(?string $viewerId = null): void
    {
        if ($viewerId === null) {
            self::$viewerPreferenceCache = [];
            return;
        }
        unset(self::$viewerPreferenceCache[trim($viewerId)]);
    }

    /**
     * Normalize a switch preference value strictly to 'on' or 'off' (default 'on').
     */
    public static function normalisePreference(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return 'on';
        }
        $clean = strtolower(trim((string)$raw));
        if (in_array($clean, ['off', '0', 'false', 'no'], true)) {
            return 'off';
        }
        return 'on';
    }

    /**
     * Retrieve the viewer's saved `kinship_honorifics` preference ('on' default | 'off').
     */
    public static function getViewerPreference(string $viewerId, ?PDO $pdo = null): string
    {
        $viewerId = trim($viewerId);
        if ($viewerId === '') {
            return 'on';
        }
        if (isset(self::$viewerPreferenceCache[$viewerId])) {
            return self::$viewerPreferenceCache[$viewerId];
        }

        self::ensureSchema($pdo);

        try {
            $db = $pdo ?? Db::connect2();
            $stmt = $db->prepare('SELECT kinship_honorifics FROM contact WHERE id = ? LIMIT 1');
            $stmt->execute([$viewerId]);
            $val = $stmt->fetchColumn();
            $pref = ($val === false || $val === null || trim((string)$val) === '')
                ? 'on'
                : self::normalisePreference($val);
            self::$viewerPreferenceCache[$viewerId] = $pref;
            return $pref;
        } catch (Throwable $e) {
            error_log('[KinshipHonorificService::getViewerPreference] ' . $e->getMessage());
            return 'on';
        }
    }

    /**
     * Check whether respectful kinship honorifics are active for $viewerId.
     */
    public static function isEnabledForViewer(string $viewerId, ?PDO $pdo = null): bool
    {
        return self::getViewerPreference($viewerId, $pdo) === 'on';
    }

    /**
     * Map a genealogical ego-centric relationship title (e.g. "Paternal Grandfather",
     * "Maternal Aunt", "Father-in-law", "Stepmother") into a warm British/Commonwealth
     * conversational honorific, or null when no elder prefix applies.
     */
    public static function mapRelationToHonorific(
        string $egoRelation,
        string $viewerId = '',
        string $targetId = ''
    ): ?string {
        $viewerId = trim($viewerId);
        $targetId = trim($targetId);
        if ($viewerId !== '' && $targetId !== '' && hash_equals($viewerId, $targetId)) {
            return null;
        }

        $rel = trim($egoRelation);
        if ($rel === '') {
            return null;
        }

        // Strip technical Maternal / Paternal lineage qualifiers
        $stripped = preg_replace('/^(?:maternal|paternal)\s+/iu', '', $rel) ?? $rel;
        $lower = strtolower(trim($stripped));

        if ($lower === '' || str_starts_with($lower, 'former ') || str_starts_with($lower, 'late ')) {
            return null;
        }

        // Step-parent dynamic labels from KinshipCalculatorService (e.g. "Eniola's Father" / "Eniola's Mother")
        if (preg_match('/^.+(?:\'|\x{2019})s\s+father$/iu', $stripped) === 1) {
            return 'Dad';
        }
        if (preg_match('/^.+(?:\'|\x{2019})s\s+mother$/iu', $stripped) === 1) {
            return 'Mum';
        }

        // Direct parents, step-parents, and parents-in-law
        if (in_array($lower, ['father', 'stepfather', 'father-in-law'], true)) {
            return 'Dad';
        }
        if (in_array($lower, ['mother', 'stepmother', 'mother-in-law'], true)) {
            return 'Mum';
        }

        // Grandparents & grandparents-in-law
        if (in_array($lower, ['grandfather', 'grandfather-in-law'], true)) {
            return 'Grandpa';
        }
        if (in_array($lower, ['grandmother', 'grandmother-in-law'], true)) {
            return 'Grandma';
        }

        // Great-grandparents & great-grandparents-in-law
        if (in_array($lower, ['great-grandfather', 'great-grandfather-in-law'], true)) {
            return 'Great-Grandpa';
        }
        if (in_array($lower, ['great-grandmother', 'great-grandmother-in-law'], true)) {
            return 'Great-Grandma';
        }

        // Deeper direct ancestors (Great-Great-Grandfather, 3rd Great-Grandmother, etc.)
        if (in_array($lower, ['great-great-grandfather', '2nd great-grandfather'], true)) {
            return 'Great-Great-Grandpa';
        }
        if (in_array($lower, ['great-great-grandmother', '2nd great-grandmother'], true)) {
            return 'Great-Great-Grandma';
        }
        if (preg_match('/^(\d+(?:st|nd|rd|th))\s+great-grandfather$/iu', $stripped, $m) === 1) {
            return $m[1] . ' Great-Grandpa';
        }
        if (preg_match('/^(\d+(?:st|nd|rd|th))\s+great-grandmother$/iu', $stripped, $m) === 1) {
            return $m[1] . ' Great-Grandma';
        }

        // Uncles & Aunts (blood and marital)
        if (in_array($lower, ['uncle', 'uncle-in-law'], true)) {
            return 'Uncle';
        }
        if (in_array($lower, ['aunt', 'aunty', 'auntie', 'aunt-in-law'], true)) {
            return 'Aunty';
        }
        if (in_array($lower, ['great-uncle', 'great-uncle-in-law'], true)) {
            return 'Great-Uncle';
        }
        if (in_array($lower, ['great-aunt', 'great-aunty', 'great-aunt-in-law'], true)) {
            return 'Great-Aunty';
        }

        // Same-generation kin (siblings, cousins, spouses, siblings-in-law), younger kin,
        // and unplaced/gender-unknown fallbacks stay plain full names.
        return null;
    }

    /**
     * Combine an honorific prefix with a relative's formatted full name without double-prefixing.
     */
    public static function formatDisplayName(string $fullName, ?string $honorific): string
    {
        $cleanName = trim(preg_replace('/\s+/u', ' ', $fullName) ?? $fullName);
        if ($cleanName === '' || $honorific === null || trim($honorific) === '') {
            return $cleanName;
        }

        $cleanHon = trim($honorific);
        if (
            strcasecmp($cleanName, $cleanHon) === 0
            || strncasecmp($cleanName, $cleanHon . ' ', strlen($cleanHon) + 1) === 0
        ) {
            return $cleanName;
        }

        return $cleanHon . ' ' . $cleanName;
    }

    /**
     * Normalize a full name into a deterministic lowercase lookup key ("oluyomi olaogun").
     */
    public static function normaliseLookupKey(string $fullName): string
    {
        $collapsed = trim(preg_replace('/\s+/u', ' ', $fullName) ?? $fullName);
        return mb_strtolower($collapsed, 'UTF-8');
    }

    /**
     * Batch-resolve kinship map for a viewer with 120s per-viewer micro-caching.
     *
     * @param list<string> $targetIds
     * @return array<string, string>
     */
    public static function batchResolveForViewer(string $viewerId, array $targetIds, ?PDO $pdo = null): array
    {
        $viewerId = trim($viewerId);
        $cleanIds = array_values(array_unique(array_filter(
            array_map(static fn($id): string => trim((string)$id), $targetIds),
            static fn(string $id): bool => $id !== ''
        )));

        if ($viewerId === '' || $cleanIds === []) {
            return [];
        }

        $sorted = $cleanIds;
        sort($sorted);
        $cacheKey = 'kin_hon_map_v1_' . $viewerId . '_' . md5(implode(',', $sorted));

        return ProfileMicroCache::remember(
            $cacheKey,
            120,
            static function () use ($viewerId, $cleanIds, $pdo): array {
                $repo = new FeedRepository($pdo ?? Db::connect2());
                return $repo->batchResolveKinship($viewerId, $cleanIds);
            }
        );
    }

    /**
     * Enrich a family roster array with viewer-relative `kinship`, `honorific`, and `displayFullName`.
     *
     * @param array<int, array<string, mixed>> $roster
     * @return array<int, array<string, mixed>>
     */
    public static function enrichRosterForViewer(string $viewerId, array $roster, ?PDO $pdo = null): array
    {
        if ($roster === []) {
            return [];
        }

        $viewerId = trim($viewerId);
        $enabled = self::isEnabledForViewer($viewerId, $pdo);

        $targetIds = [];
        foreach ($roster as $row) {
            $id = trim((string)($row['id'] ?? ($row['user_id'] ?? '')));
            if ($id !== '') {
                $targetIds[] = $id;
            }
        }

        $kinshipMap = $viewerId !== '' ? self::batchResolveForViewer($viewerId, $targetIds, $pdo) : [];

        $enriched = [];
        foreach ($roster as $row) {
            $id = trim((string)($row['id'] ?? ($row['user_id'] ?? '')));
            $first = trim((string)($row['firstName'] ?? ($row['first_name'] ?? '')));
            $last = trim((string)($row['lastName'] ?? ($row['last_name'] ?? '')));
            $rawFull = trim((string)($row['fullName'] ?? ($row['full_name'] ?? '')));
            $formatted = NameFormatterService::formatFirstAndLast($first, $last);
            $fullName = $rawFull !== '' ? NameFormatterService::formatName($rawFull) : $formatted['full_name'];
            if ($fullName === '' || $fullName === 'Relative') {
                $fullName = trim($first . ' ' . $last);
            }

            $kinship = $id !== '' ? ($kinshipMap[$id] ?? 'Family Member') : 'Family Member';
            $honorific = ($enabled && $id !== '')
                ? self::mapRelationToHonorific($kinship, $viewerId, $id)
                : null;

            $row['fullName'] = $fullName;
            $row['kinship'] = $kinship;
            $row['honorific'] = $honorific;
            $row['displayFullName'] = self::formatDisplayName($fullName, $honorific);
            $enriched[] = $row;
        }

        return $enriched;
    }

    /**
     * Build a lowercase-name-keyed lookup directory of authorised family & connected-kin
     * members for client-side @mention rendering on Reels, Daily Spark, and Feed views.
     *
     * Marks `ambiguous: true` if two distinct relatives share the exact same full name.
     *
     * @return array<string, array{
     *     id: string,
     *     fullName: string,
     *     kinship: string,
     *     honorific: string|null,
     *     displayFullName: string,
     *     ambiguous?: bool
     * }>
     */
    public static function buildViewerMentionDirectory(
        string $viewerId,
        string $famCode = '',
        ?PDO $pdo = null
    ): array {
        $viewerId = trim($viewerId);
        if ($viewerId === '') {
            return [];
        }

        try {
            $db = $pdo ?? Db::connect2();
            $members = (new AllMembersData())->getAllMembers($viewerId);
            if ($members === [] && $famCode !== '') {
                $members = AllMembersData::AllMembersEmailByFamCode($famCode, $viewerId);
            }

            // Include the viewer themselves so self-mentions resolve cleanly to plain name + profile link
            $selfStmt = $db->prepare('SELECT id, firstName, lastName FROM personal WHERE id = ? LIMIT 1');
            $selfStmt->execute([$viewerId]);
            $selfRow = $selfStmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($selfRow)) {
                $members[] = $selfRow;
            }

            $enriched = self::enrichRosterForViewer($viewerId, $members, $db);
            return self::indexEnrichedRosterByName($enriched);
        } catch (Throwable $e) {
            error_log('[KinshipHonorificService::buildViewerMentionDirectory] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Index an enriched roster array by normalised lowercase full name.
     *
     * @param array<int, array<string, mixed>> $enrichedRoster
     * @return array<string, array{
     *     id: string,
     *     fullName: string,
     *     kinship: string,
     *     honorific: string|null,
     *     displayFullName: string,
     *     ambiguous?: bool
     * }>
     */
    public static function indexEnrichedRosterByName(array $enrichedRoster): array
    {
        /**
         * @var array<string, array{
         *     id: string,
         *     fullName: string,
         *     kinship: string,
         *     honorific: string|null,
         *     displayFullName: string,
         *     ambiguous?: bool
         * }> $directory
         */
        $directory = [];
        foreach ($enrichedRoster as $member) {
            $id = trim((string)($member['id'] ?? ($member['user_id'] ?? '')));
            $fullName = trim((string)($member['fullName'] ?? ''));
            if ($id === '' || $fullName === '') {
                continue;
            }

            $key = self::normaliseLookupKey($fullName);
            if ($key === '') {
                continue;
            }

            if (isset($directory[$key]) && $directory[$key]['id'] !== $id) {
                $existing = $directory[$key];
                $directory[$key] = [
                    'id' => $existing['id'],
                    'fullName' => $fullName,
                    'kinship' => $existing['kinship'],
                    'honorific' => null,
                    'displayFullName' => $fullName,
                    'ambiguous' => true,
                ];
                continue;
            }

            $kinship = (string)($member['kinship'] ?? 'Family Member');
            $honorific = isset($member['honorific']) && is_string($member['honorific']) && $member['honorific'] !== ''
                ? $member['honorific']
                : null;
            $displayFullName = (string)($member['displayFullName'] ?? self::formatDisplayName($fullName, $honorific));

            $directory[$key] = [
                'id' => $id,
                'fullName' => $fullName,
                'kinship' => $kinship,
                'honorific' => $honorific,
                'displayFullName' => $displayFullName,
            ];
        }

        return $directory;
    }
}
