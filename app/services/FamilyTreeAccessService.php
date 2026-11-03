<?php
declare(strict_types=1);

namespace App\services;

use App\controller\BaseController;
use PDO;
use Src\Db;

/**
 * FamilyTreeAccessService
 *
 * Governs Family Tree (Organogram & Family Studio) visibility, strict same-family-code
 * isolation, granular per-person access control, and viewer audit tracking.
 *
 * Core Invariants:
 * 1. Users can see everyone who viewed their family tree (with view counts & timestamps).
 * 2. A family tree can ONLY be viewed by a family member sharing the same family code.
 * 3. Users can limit access to specific persons (via blocklist in 'family_all' mode,
 *    allowlist in 'specific_persons' mode, or full lock in 'only_me' mode).
 */
final class FamilyTreeAccessService
{
    public const MODE_FAMILY_ALL = 'family_all';
    public const MODE_SPECIFIC_PERSONS = 'specific_persons';
    public const MODE_ONLY_ME = 'only_me';

    private static bool $schemaEnsured = false;

    /**
     * Ensure the required database tables exist idempotently.
     */
    public static function ensureSchema(?PDO $pdo = null): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        try {
            $db = $pdo ?? Db::connect2();

            $db->exec("
                CREATE TABLE IF NOT EXISTS `family_tree_views` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `tree_owner_id` VARCHAR(100) NOT NULL,
                    `viewer_id` VARCHAR(100) NOT NULL,
                    `family_code` VARCHAR(100) NOT NULL,
                    `view_count` INT UNSIGNED NOT NULL DEFAULT 1,
                    `first_viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `last_viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_owner_viewer_code` (`tree_owner_id`, `viewer_id`, `family_code`),
                    KEY `idx_owner_last_viewed` (`tree_owner_id`, `last_viewed_at`),
                    KEY `idx_viewer_id` (`viewer_id`),
                    KEY `idx_family_code` (`family_code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS `family_tree_privacy_settings` (
                    `user_id` VARCHAR(100) NOT NULL,
                    `access_mode` VARCHAR(32) NOT NULL DEFAULT 'family_all',
                    `notify_on_view` TINYINT(1) NOT NULL DEFAULT 1,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`user_id`),
                    KEY `idx_access_mode` (`access_mode`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS `family_tree_access_rules` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `tree_owner_id` VARCHAR(100) NOT NULL,
                    `target_user_id` VARCHAR(100) NOT NULL,
                    `rule_type` VARCHAR(16) NOT NULL DEFAULT 'deny',
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_owner_target` (`tree_owner_id`, `target_user_id`),
                    KEY `idx_owner_rule` (`tree_owner_id`, `rule_type`),
                    KEY `idx_target_user` (`target_user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            self::$schemaEnsured = true;
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::ensureSchema] ' . $e->getMessage());
        }
    }

    /**
     * Normalize a family code (uppercase, trimmed, without leading '#').
     */
    public static function normalizeFamilyCode(string $code): string
    {
        return strtoupper(ltrim(trim($code), '#'));
    }

    /**
     * Check whether a given user belongs to the specified family code.
     * Enforces Requirement 2: Family tree can only be viewed by a family member sharing the same family code.
     */
    public static function userSharesFamilyCode(string $userId, string $familyCode, ?PDO $pdo = null): bool
    {
        $userId = trim($userId);
        $normCode = self::normalizeFamilyCode($familyCode);

        if ($userId === '' || $normCode === '') {
            return false;
        }

        $sessionId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? trim((string)$_SESSION['id']) : '';
        if ($sessionId !== '' && hash_equals($sessionId, $userId)) {
            if (BaseController::sessionSharesFamily($normCode)) {
                return true;
            }
        }

        try {
            $db = $pdo ?? Db::connect2();

            // 1. Check user_families table (approved memberships)
            $ufStmt = $db->prepare(
                "SELECT 1 FROM user_families
                 WHERE user_id = ?
                   AND status = 'approved'
                   AND UPPER(TRIM(REPLACE(family_code, '#', ''))) = ?
                 LIMIT 1"
            );
            $ufStmt->execute([$userId, $normCode]);
            if ((bool)$ufStmt->fetchColumn()) {
                return true;
            }

            // 2. Check personal table primary famCode
            $pStmt = $db->prepare(
                "SELECT 1 FROM personal
                 WHERE id = ?
                   AND UPPER(TRIM(REPLACE(famCode, '#', ''))) = ?
                 LIMIT 1"
            );
            $pStmt->execute([$userId, $normCode]);
            if ((bool)$pStmt->fetchColumn()) {
                return true;
            }

            // 3. Check active birth family code if applicable
            try {
                $bf = (new BirthFamilyCodeService($db))->status($userId);
                if (($bf['state'] ?? '') === 'active') {
                    $bfCode = self::normalizeFamilyCode((string)($bf['code'] ?? ''));
                    if ($bfCode !== '' && hash_equals($bfCode, $normCode)) {
                        return true;
                    }
                }
            } catch (\Throwable) {
            }

            return false;
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::userSharesFamilyCode] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve the best shared family code between $viewerId and $treeOwnerId when no explicit code was requested.
     *
     * @param string $viewerId
     * @param string $treeOwnerId
     * @param string $primaryCode
     * @param array<int, string> $candidateCodes
     * @param PDO|null $pdo
     * @return string
     */
    public static function resolveSharedFamilyCode(
        string $viewerId,
        string $treeOwnerId,
        string $primaryCode,
        array $candidateCodes = [],
        ?PDO $pdo = null
    ): string {
        $normPrimary = self::normalizeFamilyCode($primaryCode);
        if ($viewerId === '' || ($treeOwnerId !== '' && hash_equals($viewerId, $treeOwnerId))) {
            return $normPrimary;
        }

        if ($normPrimary !== '' && self::userSharesFamilyCode($viewerId, $normPrimary, $pdo)) {
            return $normPrimary;
        }

        foreach ($candidateCodes as $candidate) {
            $normCand = self::normalizeFamilyCode((string)$candidate);
            if ($normCand !== '' && self::userSharesFamilyCode($viewerId, $normCand, $pdo)) {
                return $normCand;
            }
        }

        return $normPrimary;
    }

    /**
     * Retrieve privacy settings for a tree owner.
     *
     * @return array{user_id: string, access_mode: string, notify_on_view: bool}
     */
    public static function getPrivacySettings(string $treeOwnerId, ?PDO $pdo = null): array
    {
        self::ensureSchema($pdo);
        $treeOwnerId = trim($treeOwnerId);

        $default = [
            'user_id' => $treeOwnerId,
            'access_mode' => self::MODE_FAMILY_ALL,
            'notify_on_view' => true,
        ];

        if ($treeOwnerId === '') {
            return $default;
        }

        try {
            $db = $pdo ?? Db::connect2();
            $stmt = $db->prepare(
                "SELECT access_mode, notify_on_view FROM family_tree_privacy_settings WHERE user_id = ? LIMIT 1"
            );
            $stmt->execute([$treeOwnerId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($row)) {
                $mode = (string)($row['access_mode'] ?? self::MODE_FAMILY_ALL);
                if (!in_array($mode, [self::MODE_FAMILY_ALL, self::MODE_SPECIFIC_PERSONS, self::MODE_ONLY_ME], true)) {
                    $mode = self::MODE_FAMILY_ALL;
                }
                return [
                    'user_id' => $treeOwnerId,
                    'access_mode' => $mode,
                    'notify_on_view' => (bool)((int)($row['notify_on_view'] ?? 1)),
                ];
            }
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::getPrivacySettings] ' . $e->getMessage());
        }

        return $default;
    }

    /**
     * Evaluate whether $viewerId is authorised to view $treeOwnerId's family tree under $familyCode.
     *
     * Enforces:
     * 1. Viewer must be signed in.
     * 2. Viewer must share the same family code ($familyCode) — external approved connections cannot view the tree.
     * 3. Owner's granular access mode ('family_all', 'specific_persons', 'only_me') and per-person rules ('allow', 'deny').
     *
     * @return array{allowed: bool, reason: string, code: string, access_mode: string}
     */
    public static function canViewFamilyTree(
        string $viewerId,
        string $treeOwnerId,
        string $familyCode,
        ?PDO $pdo = null
    ): array {
        self::ensureSchema($pdo);

        $viewerId = trim($viewerId);
        $treeOwnerId = trim($treeOwnerId);
        $normCode = self::normalizeFamilyCode($familyCode);

        if ($viewerId === '') {
            return [
                'allowed' => false,
                'reason' => 'Please sign in to view this family tree.',
                'code' => 'unauthenticated',
                'access_mode' => self::MODE_FAMILY_ALL,
            ];
        }

        if ($normCode === '') {
            return [
                'allowed' => false,
                'reason' => 'Invalid family code context.',
                'code' => 'invalid_family_code',
                'access_mode' => self::MODE_FAMILY_ALL,
            ];
        }

        $settings = self::getPrivacySettings($treeOwnerId, $pdo);
        $accessMode = $settings['access_mode'];

        // Owner viewing their own tree
        if ($treeOwnerId !== '' && hash_equals($viewerId, $treeOwnerId)) {
            if (!self::userSharesFamilyCode($viewerId, $normCode, $pdo)) {
                return [
                    'allowed' => false,
                    'reason' => 'You can only view family trees sharing your family code.',
                    'code' => 'different_family_code',
                    'access_mode' => $accessMode,
                ];
            }
            return [
                'allowed' => true,
                'reason' => 'Owner access granted.',
                'code' => 'owner',
                'access_mode' => $accessMode,
            ];
        }

        // Strict Requirement #2: Family tree can ONLY be viewed by a family member sharing the same family code
        if (!self::userSharesFamilyCode($viewerId, $normCode, $pdo)) {
            return [
                'allowed' => false,
                'reason' => "Family trees can only be viewed by family members sharing the same family code (#{$normCode}).",
                'code' => 'different_family_code',
                'access_mode' => $accessMode,
            ];
        }

        // Also ensure that if a target owner is specified, the target owner actually belongs to or holds $normCode
        if ($treeOwnerId !== '' && !self::userSharesFamilyCode($treeOwnerId, $normCode, $pdo)) {
            // Check if owner has any nodes in $normCode (e.g. in-law branch)
            try {
                $db = $pdo ?? Db::connect2();
                $nodeChk = $db->prepare("SELECT 1 FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
                $nodeChk->execute([$normCode, $treeOwnerId]);
                if (!(bool)$nodeChk->fetchColumn()) {
                    return [
                        'allowed' => false,
                        'reason' => "Family trees can only be viewed by family members sharing the same family code (#{$normCode}).",
                        'code' => 'different_family_code',
                        'access_mode' => $accessMode,
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // Requirement #3: Owner's granular per-person access restrictions
        if ($treeOwnerId !== '') {
            if ($accessMode === self::MODE_ONLY_ME) {
                return [
                    'allowed' => false,
                    'reason' => 'This family member has locked their family tree to private (Only Me).',
                    'code' => 'locked_only_me',
                    'access_mode' => $accessMode,
                ];
            }

            $ruleType = self::getRuleForPerson($treeOwnerId, $viewerId, $pdo);

            if ($ruleType === 'deny') {
                return [
                    'allowed' => false,
                    'reason' => 'The owner of this family tree has limited access to specific family members.',
                    'code' => 'restricted_person',
                    'access_mode' => $accessMode,
                ];
            }

            if ($accessMode === self::MODE_SPECIFIC_PERSONS && $ruleType !== 'allow') {
                return [
                    'allowed' => false,
                    'reason' => 'This family tree is restricted to specific selected family members only.',
                    'code' => 'not_in_allowlist',
                    'access_mode' => $accessMode,
                ];
            }
        }

        return [
            'allowed' => true,
            'reason' => 'Access granted to family member sharing the same family code.',
            'code' => 'allowed',
            'access_mode' => $accessMode,
        ];
    }

    /**
     * Get the specific rule ('allow', 'deny', or 'default') set by $treeOwnerId for $targetUserId.
     */
    public static function getRuleForPerson(string $treeOwnerId, string $targetUserId, ?PDO $pdo = null): string
    {
        self::ensureSchema($pdo);
        $treeOwnerId = trim($treeOwnerId);
        $targetUserId = trim($targetUserId);

        if ($treeOwnerId === '' || $targetUserId === '') {
            return 'default';
        }

        try {
            $db = $pdo ?? Db::connect2();
            $stmt = $db->prepare(
                "SELECT rule_type FROM family_tree_access_rules WHERE tree_owner_id = ? AND target_user_id = ? LIMIT 1"
            );
            $stmt->execute([$treeOwnerId, $targetUserId]);
            $rule = $stmt->fetchColumn();
            if (is_string($rule) && in_array($rule, ['allow', 'deny'], true)) {
                return $rule;
            }
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::getRuleForPerson] ' . $e->getMessage());
        }

        return 'default';
    }

    /**
     * Record a family tree view event.
     *
     * - When $viewerId views another relative's tree ($viewerId !== $treeOwnerId), logs a direct view for $treeOwnerId.
     * - Also logs shared-family-code tree views for co-members of $familyCode who allow $viewerId to view their tree,
     *   so family members sharing the same code see when relatives explore their shared family tree.
     */
    public static function recordTreeView(
        string $viewerId,
        string $treeOwnerId,
        string $familyCode,
        ?PDO $pdo = null
    ): void {
        self::ensureSchema($pdo);

        $viewerId = trim($viewerId);
        $treeOwnerId = trim($treeOwnerId);
        $normCode = self::normalizeFamilyCode($familyCode);

        if ($viewerId === '' || $normCode === '') {
            return;
        }

        try {
            $db = $pdo ?? Db::connect2();

            $upsertStmt = $db->prepare("
                INSERT INTO family_tree_views (tree_owner_id, viewer_id, family_code, view_count, first_viewed_at, last_viewed_at)
                VALUES (?, ?, ?, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE view_count = view_count + 1, last_viewed_at = NOW()
            ");

            // 1. Direct view on target tree owner (when viewing another family member's tree)
            if ($treeOwnerId !== '' && !hash_equals($viewerId, $treeOwnerId)) {
                $check = self::canViewFamilyTree($viewerId, $treeOwnerId, $normCode, $db);
                if ($check['allowed']) {
                    $upsertStmt->execute([$treeOwnerId, $viewerId, $normCode]);
                }
                return;
            }

            // 2. When a family member opens the shared family tree via their own root (/organogram),
            //    credit the view to co-members of the same family code who allow $viewerId.
            $coMembers = self::getSharedFamilyMemberIds($normCode, $viewerId, $db);
            foreach ($coMembers as $coMemberId) {
                if ($coMemberId !== '' && !hash_equals($viewerId, $coMemberId)) {
                    $check = self::canViewFamilyTree($viewerId, $coMemberId, $normCode, $db);
                    if ($check['allowed']) {
                        $upsertStmt->execute([$coMemberId, $viewerId, $normCode]);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::recordTreeView] ' . $e->getMessage());
        }
    }

    /**
     * Get distinct user IDs belonging to $familyCode (excluding $excludeUserId).
     *
     * @return array<int, string>
     */
    private static function getSharedFamilyMemberIds(string $familyCode, string $excludeUserId, PDO $db): array
    {
        $normCode = self::normalizeFamilyCode($familyCode);
        if ($normCode === '') {
            return [];
        }

        $ids = [];
        try {
            $stmt = $db->prepare("
                SELECT DISTINCT id AS user_id FROM personal
                WHERE UPPER(TRIM(REPLACE(famCode, '#', ''))) = ? AND id != ?
                UNION
                SELECT DISTINCT user_id FROM user_families
                WHERE UPPER(TRIM(REPLACE(family_code, '#', ''))) = ? AND status = 'approved' AND user_id != ?
                LIMIT 100
            ");
            $stmt->execute([$normCode, $excludeUserId, $normCode, $excludeUserId]);
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            foreach ($rows as $r) {
                $uid = trim((string)$r);
                if ($uid !== '' && !hash_equals($uid, $excludeUserId)) {
                    $ids[$uid] = $uid;
                }
            }
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::getSharedFamilyMemberIds] ' . $e->getMessage());
        }

        return array_values($ids);
    }

    /**
     * Retrieve the list of people who viewed $treeOwnerId's family tree.
     *
     * @return array{
     *   total_views: int,
     *   unique_viewers: int,
     *   access_mode: string,
     *   viewers: array<int, array<string, mixed>>
     * }
     */
    public static function getTreeViewers(
        string $treeOwnerId,
        ?string $familyCode = null,
        ?PDO $pdo = null
    ): array {
        self::ensureSchema($pdo);
        $treeOwnerId = trim($treeOwnerId);
        $settings = self::getPrivacySettings($treeOwnerId, $pdo);
        $accessMode = $settings['access_mode'];

        if ($treeOwnerId === '') {
            return [
                'total_views' => 0,
                'unique_viewers' => 0,
                'access_mode' => $accessMode,
                'viewers' => [],
            ];
        }

        try {
            $db = $pdo ?? Db::connect2();
            $normCode = $familyCode !== null && trim($familyCode) !== '' ? self::normalizeFamilyCode($familyCode) : '';

            if ($normCode !== '') {
                $stmt = $db->prepare("
                    SELECT ftv.viewer_id,
                           ftv.family_code,
                           ftv.view_count,
                           ftv.first_viewed_at,
                           ftv.last_viewed_at,
                           COALESCE(p.firstName, '') AS first_name,
                           COALESCE(p.lastName, '') AS last_name,
                           COALESCE(c.country, '') AS country,
                           COALESCE(pp.img, '') AS profile_img,
                           COALESCE(ftar.rule_type, 'default') AS rule_type
                    FROM family_tree_views ftv
                    LEFT JOIN personal p ON ftv.viewer_id = p.id
                    LEFT JOIN contact c ON ftv.viewer_id = c.id
                    LEFT JOIN profilePics pp ON ftv.viewer_id = pp.id
                    LEFT JOIN family_tree_access_rules ftar
                           ON ftar.tree_owner_id = ftv.tree_owner_id AND ftar.target_user_id = ftv.viewer_id
                    WHERE ftv.tree_owner_id = ?
                      AND ftv.viewer_id != ?
                      AND ftv.family_code = ?
                    ORDER BY ftv.last_viewed_at DESC
                    LIMIT 100
                ");
                $stmt->execute([$treeOwnerId, $treeOwnerId, $normCode]);
            } else {
                $stmt = $db->prepare("
                    SELECT ftv.viewer_id,
                           ftv.family_code,
                           ftv.view_count,
                           ftv.first_viewed_at,
                           ftv.last_viewed_at,
                           COALESCE(p.firstName, '') AS first_name,
                           COALESCE(p.lastName, '') AS last_name,
                           COALESCE(c.country, '') AS country,
                           COALESCE(pp.img, '') AS profile_img,
                           COALESCE(ftar.rule_type, 'default') AS rule_type
                    FROM family_tree_views ftv
                    LEFT JOIN personal p ON ftv.viewer_id = p.id
                    LEFT JOIN contact c ON ftv.viewer_id = c.id
                    LEFT JOIN profilePics pp ON ftv.viewer_id = pp.id
                    LEFT JOIN family_tree_access_rules ftar
                           ON ftar.tree_owner_id = ftv.tree_owner_id AND ftar.target_user_id = ftv.viewer_id
                    WHERE ftv.tree_owner_id = ?
                      AND ftv.viewer_id != ?
                    ORDER BY ftv.last_viewed_at DESC
                    LIMIT 100
                ");
                $stmt->execute([$treeOwnerId, $treeOwnerId]);
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $viewers = [];
            $totalViews = 0;
            $uniqueViewerIds = [];

            foreach ($rows as $row) {
                $vId = (string)($row['viewer_id'] ?? '');
                if ($vId === '') {
                    continue;
                }
                $count = max(1, (int)($row['view_count'] ?? 1));
                $totalViews += $count;
                $uniqueViewerIds[$vId] = true;

                $firstName = trim((string)($row['first_name'] ?? ''));
                $lastName = trim((string)($row['last_name'] ?? ''));
                $fullName = trim($firstName . ' ' . $lastName);
                if ($fullName === '') {
                    $fullName = 'Family Member';
                }

                $rawImg = trim((string)($row['profile_img'] ?? ''));
                if ($rawImg === '') {
                    $avatarUrl = '/resources/images/profile/avatarM.png';
                } elseif (str_starts_with($rawImg, '/') || str_starts_with($rawImg, 'http')) {
                    $avatarUrl = $rawImg;
                } else {
                    $avatarUrl = '/resources/images/profile/' . $rawImg;
                }

                $ruleType = (string)($row['rule_type'] ?? 'default');
                $isAllowed = self::evaluateIsAllowed($accessMode, $ruleType);

                $lastViewedAt = (string)($row['last_viewed_at'] ?? '');
                $viewers[] = [
                    'viewer_id' => $vId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'full_name' => $fullName,
                    'country' => trim((string)($row['country'] ?? '')),
                    'avatar_url' => $avatarUrl,
                    'family_code' => (string)($row['family_code'] ?? ''),
                    'view_count' => $count,
                    'first_viewed_at' => (string)($row['first_viewed_at'] ?? ''),
                    'last_viewed_at' => $lastViewedAt,
                    'last_viewed_human' => self::formatHumanTime($lastViewedAt),
                    'rule_type' => $ruleType,
                    'is_allowed' => $isAllowed,
                ];
            }

            return [
                'total_views' => $totalViews,
                'unique_viewers' => count($uniqueViewerIds),
                'access_mode' => $accessMode,
                'viewers' => $viewers,
            ];
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::getTreeViewers] ' . $e->getMessage());
            return [
                'total_views' => 0,
                'unique_viewers' => 0,
                'access_mode' => $accessMode,
                'viewers' => [],
            ];
        }
    }

    /**
     * Retrieve all family members sharing $treeOwnerId's family code(s) along with their
     * current access status and view counts, for the "Limit Access to Specific Persons" UI.
     *
     * @param string $treeOwnerId
     * @param string $primaryFamilyCode
     * @param array<int, string> $additionalCodes
     * @param PDO|null $pdo
     * @return array{
     *   access_mode: string,
     *   family_codes: array<int, string>,
     *   members: array<int, array<string, mixed>>,
     *   allowed_count: int,
     *   restricted_count: int
     * }
     */
    public static function getFamilyMembersForAccessControl(
        string $treeOwnerId,
        string $primaryFamilyCode,
        array $additionalCodes = [],
        ?PDO $pdo = null
    ): array {
        self::ensureSchema($pdo);
        $treeOwnerId = trim($treeOwnerId);
        $settings = self::getPrivacySettings($treeOwnerId, $pdo);
        $accessMode = $settings['access_mode'];

        $codes = [];
        foreach (array_merge([$primaryFamilyCode], $additionalCodes) as $c) {
            $nc = self::normalizeFamilyCode((string)$c);
            if ($nc !== '') {
                $codes[$nc] = $nc;
            }
        }
        $codeList = array_values($codes);

        if ($treeOwnerId === '' || empty($codeList)) {
            return [
                'access_mode' => $accessMode,
                'family_codes' => $codeList,
                'members' => [],
                'allowed_count' => 0,
                'restricted_count' => 0,
            ];
        }

        try {
            $db = $pdo ?? Db::connect2();

            // Gather all family members sharing any of $codeList
            $memberMap = [];
            foreach ($codeList as $fc) {
                $stmt = $db->prepare("
                    SELECT p.id, p.firstName, p.lastName, p.famCode, COALESCE(c.country, '') AS country, COALESCE(pp.img, '') AS profile_img
                    FROM personal p
                    LEFT JOIN contact c ON p.id = c.id
                    LEFT JOIN profilePics pp ON p.id = pp.id
                    WHERE p.id != ?
                      AND (
                        UPPER(TRIM(REPLACE(p.famCode, '#', ''))) = ?
                        OR EXISTS (
                            SELECT 1 FROM user_families uf
                            WHERE uf.user_id = p.id
                              AND uf.status = 'approved'
                              AND UPPER(TRIM(REPLACE(uf.family_code, '#', ''))) = ?
                        )
                      )
                    ORDER BY p.firstName ASC, p.lastName ASC
                    LIMIT 100
                ");
                $stmt->execute([$treeOwnerId, $fc, $fc]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($rows as $r) {
                    $uid = trim((string)($r['id'] ?? ''));
                    if ($uid === '' || hash_equals($uid, $treeOwnerId)) {
                        continue;
                    }
                    if (!isset($memberMap[$uid])) {
                        $memberMap[$uid] = [
                            'user_id' => $uid,
                            'first_name' => trim((string)($r['firstName'] ?? '')),
                            'last_name' => trim((string)($r['lastName'] ?? '')),
                            'profile_img' => trim((string)($r['profile_img'] ?? '')),
                            'country' => trim((string)($r['country'] ?? '')),
                            'shared_codes' => [$fc],
                        ];
                    } elseif (!in_array($fc, $memberMap[$uid]['shared_codes'], true)) {
                        $memberMap[$uid]['shared_codes'][] = $fc;
                    }
                }
            }

            // Fetch existing rules for $treeOwnerId
            $rulesMap = [];
            $rStmt = $db->prepare("SELECT target_user_id, rule_type FROM family_tree_access_rules WHERE tree_owner_id = ?");
            $rStmt->execute([$treeOwnerId]);
            foreach ($rStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $rr) {
                $rulesMap[(string)$rr['target_user_id']] = (string)$rr['rule_type'];
            }

            // Fetch view stats per viewer for $treeOwnerId
            $viewsMap = [];
            $vStmt = $db->prepare("
                SELECT viewer_id, SUM(view_count) AS total_views, MAX(last_viewed_at) AS last_viewed_at
                FROM family_tree_views
                WHERE tree_owner_id = ? AND viewer_id != ?
                GROUP BY viewer_id
            ");
            $vStmt->execute([$treeOwnerId, $treeOwnerId]);
            foreach ($vStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $vr) {
                $viewsMap[(string)$vr['viewer_id']] = [
                    'view_count' => (int)($vr['total_views'] ?? 0),
                    'last_viewed_at' => (string)($vr['last_viewed_at'] ?? ''),
                ];
            }

            $members = [];
            $allowedCount = 0;
            $restrictedCount = 0;

            foreach ($memberMap as $uid => $m) {
                $fullName = trim($m['first_name'] . ' ' . $m['last_name']);
                if ($fullName === '') {
                    $fullName = 'Family Member';
                }
                $rawImg = $m['profile_img'];
                if ($rawImg === '') {
                    $avatarUrl = '/resources/images/profile/avatarM.png';
                } elseif (str_starts_with($rawImg, '/') || str_starts_with($rawImg, 'http')) {
                    $avatarUrl = $rawImg;
                } else {
                    $avatarUrl = '/resources/images/profile/' . $rawImg;
                }

                $ruleType = $rulesMap[$uid] ?? 'default';
                $isAllowed = self::evaluateIsAllowed($accessMode, $ruleType);
                if ($isAllowed) {
                    $allowedCount++;
                } else {
                    $restrictedCount++;
                }

                $vInfo = $viewsMap[$uid] ?? ['view_count' => 0, 'last_viewed_at' => ''];
                $members[] = [
                    'user_id' => $uid,
                    'first_name' => $m['first_name'],
                    'last_name' => $m['last_name'],
                    'full_name' => $fullName,
                    'avatar_url' => $avatarUrl,
                    'country' => $m['country'],
                    'shared_codes' => $m['shared_codes'],
                    'rule_type' => $ruleType,
                    'is_allowed' => $isAllowed,
                    'view_count' => $vInfo['view_count'],
                    'last_viewed_at' => $vInfo['last_viewed_at'],
                    'last_viewed_human' => $vInfo['last_viewed_at'] !== '' ? self::formatHumanTime($vInfo['last_viewed_at']) : 'Never viewed',
                ];
            }

            return [
                'access_mode' => $accessMode,
                'family_codes' => $codeList,
                'members' => $members,
                'allowed_count' => $allowedCount,
                'restricted_count' => $restrictedCount,
            ];
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::getFamilyMembersForAccessControl] ' . $e->getMessage());
            return [
                'access_mode' => $accessMode,
                'family_codes' => $codeList,
                'members' => [],
                'allowed_count' => 0,
                'restricted_count' => 0,
            ];
        }
    }

    /**
     * Save bulk access control configuration for $treeOwnerId.
     *
     * @param string $treeOwnerId
     * @param string $accessMode 'family_all' | 'specific_persons' | 'only_me'
     * @param array<int, string> $allowedUserIds
     * @param array<int, string> $deniedUserIds
     * @param PDO|null $pdo
     * @return array{success: bool, access_mode: string, allowed_count: int, denied_count: int, message: string}
     */
    public static function saveAccessControlPolicy(
        string $treeOwnerId,
        string $accessMode,
        array $allowedUserIds = [],
        array $deniedUserIds = [],
        ?PDO $pdo = null
    ): array {
        self::ensureSchema($pdo);
        $treeOwnerId = trim($treeOwnerId);

        if ($treeOwnerId === '') {
            return [
                'success' => false,
                'access_mode' => self::MODE_FAMILY_ALL,
                'allowed_count' => 0,
                'denied_count' => 0,
                'message' => 'Authentication required.',
            ];
        }

        if (!in_array($accessMode, [self::MODE_FAMILY_ALL, self::MODE_SPECIFIC_PERSONS, self::MODE_ONLY_ME], true)) {
            $accessMode = self::MODE_FAMILY_ALL;
        }

        try {
            $db = $pdo ?? Db::connect2();
            $inTx = $db->inTransaction();
            if (!$inTx) {
                $db->beginTransaction();
            }

            $stmtMode = $db->prepare("
                INSERT INTO family_tree_privacy_settings (user_id, access_mode, notify_on_view, updated_at)
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE access_mode = VALUES(access_mode), updated_at = NOW()
            ");
            $stmtMode->execute([$treeOwnerId, $accessMode]);

            // Clear and replace explicit rules for this owner
            $delStmt = $db->prepare("DELETE FROM family_tree_access_rules WHERE tree_owner_id = ?");
            $delStmt->execute([$treeOwnerId]);

            $insStmt = $db->prepare("
                INSERT INTO family_tree_access_rules (tree_owner_id, target_user_id, rule_type, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE rule_type = VALUES(rule_type), updated_at = NOW()
            ");

            $allowedCount = 0;
            $deniedCount = 0;

            foreach ($allowedUserIds as $uid) {
                $targetId = trim((string)$uid);
                if ($targetId !== '' && !hash_equals($targetId, $treeOwnerId)) {
                    $insStmt->execute([$treeOwnerId, $targetId, 'allow']);
                    $allowedCount++;
                }
            }

            foreach ($deniedUserIds as $uid) {
                $targetId = trim((string)$uid);
                if ($targetId !== '' && !hash_equals($targetId, $treeOwnerId)) {
                    $insStmt->execute([$treeOwnerId, $targetId, 'deny']);
                    $deniedCount++;
                }
            }

            if (!$inTx && $db->inTransaction()) {
                $db->commit();
            }

            return [
                'success' => true,
                'access_mode' => $accessMode,
                'allowed_count' => $allowedCount,
                'denied_count' => $deniedCount,
                'message' => 'Family tree access settings updated successfully.',
            ];
        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[FamilyTreeAccessService::saveAccessControlPolicy] ' . $e->getMessage());
            return [
                'success' => false,
                'access_mode' => $accessMode,
                'allowed_count' => 0,
                'denied_count' => 0,
                'message' => 'Unable to save access settings. Please try again.',
            ];
        }
    }

    /**
     * Toggle or set access for a single family member ('allow', 'deny', or 'default').
     *
     * @return array{success: bool, target_user_id: string, rule_type: string, is_allowed: bool, access_mode: string, message: string}
     */
    public static function setPersonAccessRule(
        string $treeOwnerId,
        string $targetUserId,
        string $action,
        ?PDO $pdo = null
    ): array {
        self::ensureSchema($pdo);
        $treeOwnerId = trim($treeOwnerId);
        $targetUserId = trim($targetUserId);
        $action = strtolower(trim($action));

        $settings = self::getPrivacySettings($treeOwnerId, $pdo);
        $accessMode = $settings['access_mode'];

        if ($treeOwnerId === '' || $targetUserId === '' || hash_equals($treeOwnerId, $targetUserId)) {
            return [
                'success' => false,
                'target_user_id' => $targetUserId,
                'rule_type' => 'default',
                'is_allowed' => false,
                'access_mode' => $accessMode,
                'message' => 'Invalid target family member.',
            ];
        }

        try {
            $db = $pdo ?? Db::connect2();

            if ($action === 'restrict' || $action === 'deny' || $action === 'block') {
                $stmt = $db->prepare("
                    INSERT INTO family_tree_access_rules (tree_owner_id, target_user_id, rule_type, created_at, updated_at)
                    VALUES (?, ?, 'deny', NOW(), NOW())
                    ON DUPLICATE KEY UPDATE rule_type = 'deny', updated_at = NOW()
                ");
                $stmt->execute([$treeOwnerId, $targetUserId]);
                $ruleType = 'deny';
                $msg = 'Access limited for this family member.';
            } elseif ($action === 'allow' || $action === 'restore' || $action === 'grant') {
                if ($accessMode === self::MODE_SPECIFIC_PERSONS) {
                    $stmt = $db->prepare("
                        INSERT INTO family_tree_access_rules (tree_owner_id, target_user_id, rule_type, created_at, updated_at)
                        VALUES (?, ?, 'allow', NOW(), NOW())
                        ON DUPLICATE KEY UPDATE rule_type = 'allow', updated_at = NOW()
                    ");
                    $stmt->execute([$treeOwnerId, $targetUserId]);
                    $ruleType = 'allow';
                } else {
                    $stmt = $db->prepare("DELETE FROM family_tree_access_rules WHERE tree_owner_id = ? AND target_user_id = ?");
                    $stmt->execute([$treeOwnerId, $targetUserId]);
                    $ruleType = 'default';
                }
                $msg = 'Family tree access restored for this family member.';
            } else {
                $stmt = $db->prepare("DELETE FROM family_tree_access_rules WHERE tree_owner_id = ? AND target_user_id = ?");
                $stmt->execute([$treeOwnerId, $targetUserId]);
                $ruleType = 'default';
                $msg = 'Access reset to default family policy.';
            }

            $isAllowed = self::evaluateIsAllowed($accessMode, $ruleType);

            return [
                'success' => true,
                'target_user_id' => $targetUserId,
                'rule_type' => $ruleType,
                'is_allowed' => $isAllowed,
                'access_mode' => $accessMode,
                'message' => $msg,
            ];
        } catch (\Throwable $e) {
            error_log('[FamilyTreeAccessService::setPersonAccessRule] ' . $e->getMessage());
            return [
                'success' => false,
                'target_user_id' => $targetUserId,
                'rule_type' => 'default',
                'is_allowed' => false,
                'access_mode' => $accessMode,
                'message' => 'Failed to update person access rule.',
            ];
        }
    }

    /**
     * Evaluate whether a person with $ruleType is allowed under $accessMode.
     */
    public static function evaluateIsAllowed(string $accessMode, string $ruleType): bool
    {
        if ($accessMode === self::MODE_ONLY_ME) {
            return false;
        }
        if ($ruleType === 'deny') {
            return false;
        }
        if ($accessMode === self::MODE_SPECIFIC_PERSONS) {
            return $ruleType === 'allow';
        }
        return true;
    }

    /**
     * Human-friendly relative timestamp formatter (British English).
     */
    private static function formatHumanTime(string $datetime): string
    {
        if ($datetime === '') {
            return 'Recently';
        }
        $ts = strtotime($datetime);
        if ($ts === false) {
            return $datetime;
        }
        $diff = max(0, time() - $ts);
        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            $mins = (int)floor($diff / 60);
            return $mins . ($mins === 1 ? ' min ago' : ' mins ago');
        }
        if ($diff < 86400) {
            $hrs = (int)floor($diff / 3600);
            return $hrs . ($hrs === 1 ? ' hour ago' : ' hours ago');
        }
        if ($diff < 604800) {
            $days = (int)floor($diff / 86400);
            return $days . ($days === 1 ? ' day ago' : ' days ago');
        }
        return date('j M Y, H:i', $ts);
    }
}
