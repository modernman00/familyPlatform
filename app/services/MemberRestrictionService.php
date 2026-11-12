<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;
use Throwable;

/**
 * MemberRestrictionService
 *
 * Enforces selective member-level visibility and notification restrictions.
 * If an owner restricts a target relative for one or more scopes, the restriction
 * applies silently across feed posts, comments, video reels, events, family journals,
 * announcements, time capsules, search results, Pusher broadcasts, emails, WebPush,
 * in-app bell alerts, and optional profile visibility.
 */
final class MemberRestrictionService
{
    /** @var array<string, string> */
    public const SCOPE_COLUMNS = [
        'posts' => 'restrict_posts',
        'comments' => 'restrict_comments',
        'reels' => 'restrict_reels',
        'events' => 'restrict_events',
        'journals' => 'restrict_journals',
        'announcements' => 'restrict_announcements',
        'capsules' => 'restrict_capsules',
        'profile' => 'restrict_profile',
    ];

    /**
     * Request-scoped cache: viewer_id => [owner_id => array<string, bool>]
     *
     * @var array<string, array<string, array<string, bool>>>
     */
    private static array $viewerCache = [];

    /**
     * Request-scoped cache: owner_id => [restricted_user_id => array<string, bool>]
     *
     * @var array<string, array<string, array<string, bool>>>
     */
    private static array $ownerCache = [];

    /**
     * Clear in-memory caches (essential between tests and CLI worker jobs).
     */
    public static function clearCache(): void
    {
        self::$viewerCache = [];
        self::$ownerCache = [];
    }

    /**
     * Normalise a scope name into its database column, or null for invalid scopes.
     */
    public static function resolveColumnForScope(string $scope): string
    {
        $clean = strtolower(trim($scope));
        return self::SCOPE_COLUMNS[$clean] ?? 'restrict_posts';
    }

    /**
     * Load and cache all restriction rules targeting $viewerId.
     *
     * @return array<string, array<string, bool>>
     */
    private static function loadForViewer(PDO $pdo, string $viewerId): array
    {
        if ($viewerId === '') {
            return [];
        }

        if (isset(self::$viewerCache[$viewerId])) {
            return self::$viewerCache[$viewerId];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT owner_id, restrict_posts, restrict_comments, restrict_reels,
                        restrict_events, restrict_journals, restrict_announcements,
                        restrict_capsules, restrict_profile
                 FROM user_member_restrictions
                 WHERE restricted_user_id = ?'
            );
            $stmt->execute([$viewerId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $map = [];
            foreach ($rows as $row) {
                $ownerId = (string)($row['owner_id'] ?? '');
                if ($ownerId === '') {
                    continue;
                }
                $map[$ownerId] = [
                    'posts' => (int)($row['restrict_posts'] ?? 0) === 1,
                    'comments' => (int)($row['restrict_comments'] ?? 0) === 1,
                    'reels' => (int)($row['restrict_reels'] ?? 0) === 1,
                    'events' => (int)($row['restrict_events'] ?? 0) === 1,
                    'journals' => (int)($row['restrict_journals'] ?? 0) === 1,
                    'announcements' => (int)($row['restrict_announcements'] ?? 0) === 1,
                    'capsules' => (int)($row['restrict_capsules'] ?? 0) === 1,
                    'profile' => (int)($row['restrict_profile'] ?? 0) === 1,
                ];
            }

            self::$viewerCache[$viewerId] = $map;
            return $map;
        } catch (Throwable $e) {
            // Gracefully degrade if lightweight unit tests run before schema migration
            if (str_contains($e->getMessage(), 'user_member_restrictions')) {
                return [];
            }
            error_log('[MemberRestrictionService::loadForViewer] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Load and cache all restriction rules created by $ownerId.
     *
     * @return array<string, array<string, bool>>
     */
    private static function loadForOwner(PDO $pdo, string $ownerId): array
    {
        if ($ownerId === '') {
            return [];
        }

        if (isset(self::$ownerCache[$ownerId])) {
            return self::$ownerCache[$ownerId];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT restricted_user_id, restrict_posts, restrict_comments, restrict_reels,
                        restrict_events, restrict_journals, restrict_announcements,
                        restrict_capsules, restrict_profile
                 FROM user_member_restrictions
                 WHERE owner_id = ?'
            );
            $stmt->execute([$ownerId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $map = [];
            foreach ($rows as $row) {
                $targetId = (string)($row['restricted_user_id'] ?? '');
                if ($targetId === '') {
                    continue;
                }
                $map[$targetId] = [
                    'posts' => (int)($row['restrict_posts'] ?? 0) === 1,
                    'comments' => (int)($row['restrict_comments'] ?? 0) === 1,
                    'reels' => (int)($row['restrict_reels'] ?? 0) === 1,
                    'events' => (int)($row['restrict_events'] ?? 0) === 1,
                    'journals' => (int)($row['restrict_journals'] ?? 0) === 1,
                    'announcements' => (int)($row['restrict_announcements'] ?? 0) === 1,
                    'capsules' => (int)($row['restrict_capsules'] ?? 0) === 1,
                    'profile' => (int)($row['restrict_profile'] ?? 0) === 1,
                ];
            }

            self::$ownerCache[$ownerId] = $map;
            return $map;
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'user_member_restrictions')) {
                return [];
            }
            error_log('[MemberRestrictionService::loadForOwner] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns true if $ownerId has restricted $viewerId for the given scope.
     */
    public static function isRestricted(PDO $pdo, string $ownerId, string $viewerId, string $scope = 'posts'): bool
    {
        if ($ownerId === '' || $viewerId === '' || hash_equals($ownerId, $viewerId)) {
            return false;
        }

        $cleanScope = strtolower(trim($scope));
        $map = self::loadForViewer($pdo, $viewerId);
        if (!isset($map[$ownerId])) {
            return false;
        }

        if ($cleanScope === 'any') {
            foreach ($map[$ownerId] as $enabled) {
                if ($enabled) {
                    return true;
                }
            }
            return false;
        }

        return (bool)($map[$ownerId][$cleanScope] ?? false);
    }

    /**
     * Returns all owner IDs who restricted $viewerId for $scope.
     *
     * @return list<string>
     */
    public static function getRestrictorIdsForViewer(PDO $pdo, string $viewerId, string $scope = 'posts'): array
    {
        if ($viewerId === '') {
            return [];
        }

        $cleanScope = strtolower(trim($scope));
        $map = self::loadForViewer($pdo, $viewerId);
        $ids = [];
        foreach ($map as $ownerId => $flags) {
            if ($ownerId !== $viewerId && !empty($flags[$cleanScope])) {
                $ids[] = (string)$ownerId;
            }
        }
        return $ids;
    }

    /**
     * Returns all restricted member IDs configured by $ownerId for $scope.
     *
     * @return list<string>
     */
    public static function getRestrictedUserIdsForOwner(PDO $pdo, string $ownerId, string $scope = 'posts'): array
    {
        if ($ownerId === '') {
            return [];
        }

        $cleanScope = strtolower(trim($scope));
        $map = self::loadForOwner($pdo, $ownerId);
        $ids = [];
        foreach ($map as $targetId => $flags) {
            if ($targetId !== $ownerId && !empty($flags[$cleanScope])) {
                $ids[] = (string)$targetId;
            }
        }
        return $ids;
    }

    /**
     * Whether $ownerId has at least one active restriction on $scope.
     * Used to switch Pusher fan-out and OG link preview redaction fail-closed.
     */
    public static function authorHasAnyRestriction(PDO $pdo, string $ownerId, string $scope = 'posts'): bool
    {
        return !empty(self::getRestrictedUserIdsForOwner($pdo, $ownerId, $scope));
    }

    /**
     * Build a parameterised NOT IN clause without SQL string variable interpolation.
     *
     * @param list<string> $excludedIds
     * @return array{sql: string, params: list<string>}
     */
    public static function buildNotInClause(string $columnExpression, array $excludedIds): array
    {
        $cleanIds = array_values(array_unique(array_filter(array_map('strval', $excludedIds), static fn(string $v): bool => $v !== '')));
        if (empty($cleanIds)) {
            return ['sql' => '', 'params' => []];
        }

        $placeholders = implode(', ', array_fill(0, count($cleanIds), '?'));
        return [
            'sql' => sprintf(' AND %s NOT IN (%s)', $columnExpression, $placeholders),
            'params' => $cleanIds,
        ];
    }

    /**
     * Filter an array of recipient member rows so restricted targets never receive alerts/emails.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    public static function filterRecipientRows(PDO $pdo, string $ownerId, array $rows, string $scope = 'posts', string $idKey = 'id'): array
    {
        if ($ownerId === '' || empty($rows)) {
            return $rows;
        }

        $restrictedIds = array_flip(self::getRestrictedUserIdsForOwner($pdo, $ownerId, $scope));
        if (empty($restrictedIds)) {
            return $rows;
        }

        return array_values(array_filter($rows, static function (array $row) use ($restrictedIds, $idKey): bool {
            $uid = (string)($row[$idKey] ?? '');
            return $uid === '' || !isset($restrictedIds[$uid]);
        }));
    }

    /**
     * Gather all distinct normalised family codes linked to $userId.
     *
     * @return list<string>
     */
    public static function getUserFamilyCodes(PDO $pdo, string $userId): array
    {
        if ($userId === '') {
            return [];
        }

        $codes = [];
        try {
            $stmt = $pdo->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $primary = strtoupper(ltrim(trim((string)($stmt->fetchColumn() ?: '')), '#'));
            if ($primary !== '') {
                $codes[$primary] = true;
            }
        } catch (Throwable) {
        }

        try {
            $stmtUf = $pdo->prepare('SELECT family_code FROM user_families WHERE user_id = ?');
            $stmtUf->execute([$userId]);
            foreach ($stmtUf->fetchAll(PDO::FETCH_COLUMN) as $fc) {
                $norm = strtoupper(ltrim(trim((string)$fc), '#'));
                if ($norm !== '') {
                    $codes[$norm] = true;
                }
            }
        } catch (Throwable) {
        }

        return array_keys($codes);
    }

    /**
     * Zero-trust boundary check: can $ownerId target $targetUserId in restrictions?
     * Must not be self, target must exist & be active, and they must share a family code
     * or have an approved/accepted kinship requestMgt link.
     */
    public static function canOwnerTargetUser(PDO $pdo, string $ownerId, string $targetUserId): bool
    {
        if ($ownerId === '' || $targetUserId === '' || hash_equals($ownerId, $targetUserId)) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT id, famCode FROM personal WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$targetUserId]);
        $targetRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($targetRow)) {
            return false;
        }

        $ownerCodes = self::getUserFamilyCodes($pdo, $ownerId);
        $targetCodes = self::getUserFamilyCodes($pdo, $targetUserId);
        if (!empty(array_intersect($ownerCodes, $targetCodes))) {
            return true;
        }

        $reqStmt = $pdo->prepare(
            "SELECT 1 FROM requestMgt
             WHERE LOWER(status) IN ('approved', 'accepted')
               AND ((approver_id = ? AND requester_id = ?) OR (approver_id = ? AND requester_id = ?))
             LIMIT 1"
        );
        $reqStmt->execute([$ownerId, $targetUserId, $targetUserId, $ownerId]);
        return (bool)$reqStmt->fetchColumn();
    }

    /**
     * Create, update, or clear a member restriction record for $ownerId.
     *
     * @param array<string, mixed> $flags
     * @return array<string, mixed>
     */
    public static function upsertRestriction(PDO $pdo, string $ownerId, string $targetUserId, array $flags, ?string $note = null): array
    {
        $ownerId = trim($ownerId);
        $targetUserId = trim($targetUserId);

        if ($ownerId === '' || $targetUserId === '') {
            throw new ValidationException('Both member identifiers are required.');
        }

        if (hash_equals($ownerId, $targetUserId)) {
            throw new ValidationException('You cannot restrict your own account.');
        }

        if (!self::canOwnerTargetUser($pdo, $ownerId, $targetUserId)) {
            throw new ForbiddenException('You can only restrict members within your family circles or approved connections.');
        }

        $normalised = [];
        $anyEnabled = false;
        foreach (self::SCOPE_COLUMNS as $scopeKey => $colName) {
            if (array_key_exists($scopeKey, $flags)) {
                $val = self::toBoolInt($flags[$scopeKey]);
            } elseif (array_key_exists($colName, $flags)) {
                $val = self::toBoolInt($flags[$colName]);
            } else {
                // Default presets: all content hidden, tree/profile preserved unless requested
                $val = $scopeKey === 'profile' ? 0 : 1;
            }
            $normalised[$colName] = $val;
            if ($val === 1) {
                $anyEnabled = true;
            }
        }

        if (!$anyEnabled) {
            self::removeRestriction($pdo, $ownerId, $targetUserId);
            return [
                'owner_id' => $ownerId,
                'restricted_user_id' => $targetUserId,
                'active' => false,
                'removed' => true,
            ];
        }

        $cleanNote = $note !== null ? mb_substr(trim($note), 0, 255) : null;
        if ($cleanNote === '') {
            $cleanNote = null;
        }

        $sql = 'INSERT INTO user_member_restrictions
                    (owner_id, restricted_user_id, restrict_posts, restrict_comments, restrict_reels,
                     restrict_events, restrict_journals, restrict_announcements, restrict_capsules,
                     restrict_profile, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    restrict_posts = VALUES(restrict_posts),
                    restrict_comments = VALUES(restrict_comments),
                    restrict_reels = VALUES(restrict_reels),
                    restrict_events = VALUES(restrict_events),
                    restrict_journals = VALUES(restrict_journals),
                    restrict_announcements = VALUES(restrict_announcements),
                    restrict_capsules = VALUES(restrict_capsules),
                    restrict_profile = VALUES(restrict_profile),
                    note = VALUES(note),
                    updated_at = CURRENT_TIMESTAMP';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $ownerId,
            $targetUserId,
            $normalised['restrict_posts'],
            $normalised['restrict_comments'],
            $normalised['restrict_reels'],
            $normalised['restrict_events'],
            $normalised['restrict_journals'],
            $normalised['restrict_announcements'],
            $normalised['restrict_capsules'],
            $normalised['restrict_profile'],
            $cleanNote,
        ]);

        unset(self::$viewerCache[$targetUserId], self::$ownerCache[$ownerId]);

        return [
            'owner_id' => $ownerId,
            'restricted_user_id' => $targetUserId,
            'active' => true,
            'restrict_posts' => $normalised['restrict_posts'] === 1,
            'restrict_comments' => $normalised['restrict_comments'] === 1,
            'restrict_reels' => $normalised['restrict_reels'] === 1,
            'restrict_events' => $normalised['restrict_events'] === 1,
            'restrict_journals' => $normalised['restrict_journals'] === 1,
            'restrict_announcements' => $normalised['restrict_announcements'] === 1,
            'restrict_capsules' => $normalised['restrict_capsules'] === 1,
            'restrict_profile' => $normalised['restrict_profile'] === 1,
            'note' => $cleanNote,
        ];
    }

    /**
     * Remove a member restriction completely for $ownerId.
     */
    public static function removeRestriction(PDO $pdo, string $ownerId, string $targetUserId): bool
    {
        $ownerId = trim($ownerId);
        $targetUserId = trim($targetUserId);
        if ($ownerId === '' || $targetUserId === '') {
            return false;
        }

        $stmt = $pdo->prepare('DELETE FROM user_member_restrictions WHERE owner_id = ? AND restricted_user_id = ?');
        $stmt->execute([$ownerId, $targetUserId]);

        unset(self::$viewerCache[$targetUserId], self::$ownerCache[$ownerId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * List all family & connected members eligible for restriction management by $ownerId,
     * along with any active restriction settings. Never includes the owner themselves.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getRestrictionDirectoryForOwner(PDO $pdo, string $ownerId): array
    {
        if ($ownerId === '') {
            return [];
        }

        $ownerCodes = self::getUserFamilyCodes($pdo, $ownerId);
        $familyCondSql = '';

        if (!empty($ownerCodes)) {
            $placeholdersA = implode(', ', array_fill(0, count($ownerCodes), '?'));
            $placeholdersB = implode(', ', array_fill(0, count($ownerCodes), '?'));
            $familyCondSql = sprintf(
                " OR UPPER(TRIM(REPLACE(COALESCE(p.famCode, ''), '#', ''))) IN (%s)
                  OR EXISTS (
                      SELECT 1 FROM user_families uf
                      WHERE uf.user_id = p.id
                        AND UPPER(TRIM(REPLACE(uf.family_code, '#', ''))) IN (%s)
                  )",
                $placeholdersA,
                $placeholdersB
            );
        }

        $sql = 'SELECT DISTINCT
                    p.id,
                    p.firstName,
                    p.lastName,
                    p.famCode,
                    pp.img AS profile_img,
                    umr.restrict_posts,
                    umr.restrict_comments,
                    umr.restrict_reels,
                    umr.restrict_events,
                    umr.restrict_journals,
                    umr.restrict_announcements,
                    umr.restrict_capsules,
                    umr.restrict_profile,
                    umr.note,
                    umr.updated_at AS restricted_updated_at
                FROM personal p
                LEFT JOIN profilePics pp ON pp.id = p.id
                LEFT JOIN requestMgt rm ON (
                    (rm.requester_id = ? AND rm.approver_id = p.id AND LOWER(rm.status) IN (\'approved\', \'accepted\'))
                    OR
                    (rm.approver_id = ? AND rm.requester_id = p.id AND LOWER(rm.status) IN (\'approved\', \'accepted\'))
                )
                LEFT JOIN user_member_restrictions umr ON umr.owner_id = ? AND umr.restricted_user_id = p.id
                WHERE p.id != ?
                  AND p.deleted_at IS NULL
                  AND (rm.status IS NOT NULL ' . $familyCondSql . ')
                ORDER BY (umr.restrict_posts IS NOT NULL) DESC, p.firstName ASC, p.lastName ASC';

        $stmt = $pdo->prepare($sql);
        // First 3 params are for rm join + umr join + p.id != ? -> total 4 before family codes
        $orderedParams = [$ownerId, $ownerId, $ownerId, $ownerId];
        if (!empty($ownerCodes)) {
            $orderedParams = array_merge($orderedParams, $ownerCodes, $ownerCodes);
        }
        $stmt->execute($orderedParams);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $seen = [];
        $result = [];
        foreach ($rows as $row) {
            $uid = (string)($row['id'] ?? '');
            if ($uid === '' || isset($seen[$uid])) {
                continue;
            }
            $seen[$uid] = true;

            $img = (string)($row['profile_img'] ?? '');
            $avatar = '/resources/images/profile/avatarM.png';
            if ($img !== '') {
                $avatar = (str_starts_with($img, '/') || str_starts_with($img, 'http'))
                    ? $img
                    : '/resources/images/profile/' . ltrim($img, '/');
            }

            $isRestricted = $row['restrict_posts'] !== null;
            $result[] = [
                'id' => $uid,
                'fullName' => trim((string)($row['firstName'] ?? '') . ' ' . (string)($row['lastName'] ?? '')),
                'famCode' => ltrim((string)($row['famCode'] ?? ''), '#'),
                'avatar' => $avatar,
                'is_restricted' => $isRestricted,
                'restrict_posts' => (int)($row['restrict_posts'] ?? 0) === 1,
                'restrict_comments' => (int)($row['restrict_comments'] ?? 0) === 1,
                'restrict_reels' => (int)($row['restrict_reels'] ?? 0) === 1,
                'restrict_events' => (int)($row['restrict_events'] ?? 0) === 1,
                'restrict_journals' => (int)($row['restrict_journals'] ?? 0) === 1,
                'restrict_announcements' => (int)($row['restrict_announcements'] ?? 0) === 1,
                'restrict_capsules' => (int)($row['restrict_capsules'] ?? 0) === 1,
                'restrict_profile' => (int)($row['restrict_profile'] ?? 0) === 1,
                'note' => (string)($row['note'] ?? ''),
                'updated_at' => (string)($row['restricted_updated_at'] ?? ''),
            ];
        }

        return $result;
    }

    /**
     * Search the restriction directory using the ATG SovereignSearchEngine (BM25 + 64-D Vector RRF)
     * from shared-lib.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function searchRestrictionDirectory(PDO $pdo, string $ownerId, string $query, int $limit = 30): array
    {
        $all = self::getRestrictionDirectoryForOwner($pdo, $ownerId);
        $cleanQuery = trim($query);
        if ($cleanQuery === '') {
            return $all;
        }

        // Strip leading # or @ for resilient family-code and handle matching
        $cleanQuery = ltrim($cleanQuery, '#@');

        if (class_exists('\\Src\\Search\\SovereignSearchEngine')) {
            $corpus = [];
            foreach ($all as $member) {
                $corpus[] = [
                    'id' => (string)($member['id'] ?? ''),
                    'title' => (string)($member['fullName'] ?? ''),
                    'body' => trim((string)($member['famCode'] ?? '') . ' ' . (string)($member['note'] ?? '')),
                    'category' => !empty($member['is_restricted']) ? 'Restricted' : 'Active',
                    'raw' => $member,
                ];
            }

            try {
                $vectorEngine = class_exists('\\Src\\Search\\VectorEngine') ? new \Src\Search\VectorEngine() : null;
                $engine = new \Src\Search\SovereignSearchEngine($vectorEngine);
                $ranked = $engine->searchCorpus($cleanQuery, $corpus, $limit);

                $results = [];
                foreach ($ranked as $item) {
                    $raw = $item['metadata']['raw'] ?? null;
                    if (is_array($raw)) {
                        $raw['search_score'] = (float)($item['rrf_score']);
                        $results[] = $raw;
                    }
                }
                return $results;
            } catch (\Throwable $e) {
                error_log('[MemberRestrictionService::searchRestrictionDirectory] SovereignSearchEngine error: ' . $e->getMessage());
            }
        }

        // Fallback token filtering
        $qLower = strtolower($cleanQuery);
        return array_values(array_filter($all, static function (array $m) use ($qLower): bool {
            $name = strtolower((string)($m['fullName'] ?? ''));
            $code = strtolower((string)($m['famCode'] ?? ''));
            $note = strtolower((string)($m['note'] ?? ''));
            return str_contains($name, $qLower) || str_contains($code, $qLower) || str_contains($note, $qLower);
        }));
    }

    private static function toBoolInt(mixed $value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        $str = strtolower(trim((string)$value));
        return in_array($str, ['1', 'true', 'on', 'yes'], true) ? 1 : 0;
    }
}
