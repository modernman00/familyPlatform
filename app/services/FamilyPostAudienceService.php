<?php
declare(strict_types=1);

namespace App\services;

use App\service\FamilyCodeApprovalService;
use PDO;
use Src\Db;

/**
 * FamilyPostAudienceService
 *
 * Manages post audience targeting across dual lineages (Birth vs. Married / Household family codes).
 * Allows members to publish posts to All, Birth only, or Married only, with strict IDOR protections.
 */
class FamilyPostAudienceService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Db::connect2();
    }

    /**
     * Normalise a family code (strip # and whitespace, uppercase).
     */
    public static function normaliseCode(string $code): string
    {
        return strtoupper(trim(preg_replace('/[\s#]+/', '', $code) ?? ''));
    }

    /**
     * Resolve all held family codes for a user, categorised by lineage kind (birth vs. married/primary).
     *
     * @param string $userId
     * @return array{
     *     all: list<string>,
     *     birth: list<string>,
     *     married: list<string>,
     *     has_dual_lineage: bool,
     *     can_choose: bool,
     *     default_scope: string,
     *     primary_fam_code: string,
     *     birth_fam_code: string
     * }
     */
    public function resolveUserAudiences(string $userId): array
    {
        $userId = trim($userId);
        if ($userId === '') {
            return [
                'all' => [],
                'birth' => [],
                'married' => [],
                'has_dual_lineage' => false,
                'can_choose' => false,
                'default_scope' => 'all',
                'primary_fam_code' => '',
                'birth_fam_code' => '',
            ];
        }

        $birthCodes = [];
        $marriedCodes = [];

        // 1. Primary family code from personal table
        $primaryCode = '';
        try {
            $pStmt = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
            $pStmt->execute([$userId]);
            $rawPrimary = (string)($pStmt->fetchColumn() ?: '');
            $primaryCode = self::normaliseCode($rawPrimary);
            if ($primaryCode !== '') {
                // Check registry kind
                $kindStmt = $this->pdo->prepare('SELECT kind FROM family_codes WHERE code = ? LIMIT 1');
                $kindStmt->execute([$primaryCode]);
                $kind = (string)($kindStmt->fetchColumn() ?: 'primary');
                if ($kind === 'birth') {
                    $birthCodes[] = $primaryCode;
                } else {
                    $marriedCodes[] = $primaryCode;
                }
            }
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::resolveUserAudiences personal] ' . $e->getMessage());
        }

        // 2. Verified active birth family code
        try {
            $birthStatus = (new BirthFamilyCodeService($this->pdo))->status($userId);
            if (($birthStatus['state'] ?? '') === 'active' && !empty($birthStatus['code'])) {
                $bCode = self::normaliseCode((string)$birthStatus['code']);
                if ($bCode !== '') {
                    $birthCodes[] = $bCode;
                }
            }
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::resolveUserAudiences birth] ' . $e->getMessage());
        }

        // 3. Fallback: check otherFamily stored code if not caught above
        try {
            $ofStmt = $this->pdo->prepare('SELECT otherFamCode FROM otherFamily WHERE id = ? ORDER BY no ASC LIMIT 1');
            $ofStmt->execute([$userId]);
            $rawOf = (string)($ofStmt->fetchColumn() ?: '');
            $ofCode = self::normaliseCode($rawOf);
            if ($ofCode !== '' && !in_array($ofCode, $birthCodes, true)) {
                $birthCodes[] = $ofCode;
            }
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::resolveUserAudiences otherFamily] ' . $e->getMessage());
        }

        // 4. Approved memberships in user_families table
        try {
            $ufStmt = $this->pdo->prepare(
                "SELECT uf.family_code, fc.kind
                 FROM user_families uf
                 LEFT JOIN family_codes fc ON fc.code COLLATE utf8mb4_unicode_ci = UPPER(TRIM(REPLACE(uf.family_code, '#', ''))) COLLATE utf8mb4_unicode_ci
                 WHERE uf.user_id = ? AND uf.status = 'approved'"
            );
            $ufStmt->execute([$userId]);
            $ufRows = $ufStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($ufRows as $row) {
                $c = self::normaliseCode((string)($row['family_code'] ?? ''));
                if ($c === '') {
                    continue;
                }
                $k = (string)($row['kind'] ?? 'primary');
                if ($k === 'birth') {
                    if (!in_array($c, $birthCodes, true)) {
                        $birthCodes[] = $c;
                    }
                } else {
                    if (!in_array($c, $marriedCodes, true)) {
                        $marriedCodes[] = $c;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::resolveUserAudiences user_families] ' . $e->getMessage());
        }

        $birthCodes = array_values(array_unique(array_filter($birthCodes)));
        $marriedCodes = array_values(array_unique(array_filter($marriedCodes)));
        $allCodes = array_values(array_unique(array_merge($marriedCodes, $birthCodes)));

        $hasDualLineage = !empty($birthCodes) && !empty($marriedCodes);
        $canChoose = $hasDualLineage || count($allCodes) > 1;

        return [
            'all' => $allCodes,
            'birth' => $birthCodes,
            'married' => $marriedCodes,
            'has_dual_lineage' => $hasDualLineage,
            'can_choose' => $canChoose,
            'default_scope' => $hasDualLineage ? 'all' : (!empty($birthCodes) ? 'birth' : 'married'),
            'primary_fam_code' => $primaryCode !== '' ? $primaryCode : ($marriedCodes[0] ?? ($allCodes[0] ?? '')),
            'birth_fam_code' => $birthCodes[0] ?? '',
        ];
    }

    /**
     * Assign and persist post audience targets.
     * Enforces IDOR security by verifying the user holds the targeted codes.
     *
     * @param int $postNo
     * @param string $userId
     * @param string $requestedScope 'all' | 'birth' | 'married'
     * @param string|null $explicitFamCode Specific family code if selected
     * @return array{
     *     post_no: int,
     *     target_scope: string,
     *     target_codes: list<string>
     * }
     */
    public function assignPostTargets(int $postNo, string $userId, string $requestedScope = 'all', ?string $explicitFamCode = null): array
    {
        $audiences = $this->resolveUserAudiences($userId);
        $cleanExplicit = $explicitFamCode !== null ? self::normaliseCode($explicitFamCode) : '';

        $resolvedScope = in_array(strtolower($requestedScope), ['all', 'birth', 'married'], true)
            ? strtolower($requestedScope)
            : 'all';

        $targetCodes = [];

        // Check if user specified a valid held explicit code
        if ($cleanExplicit !== '' && in_array($cleanExplicit, $audiences['all'], true)) {
            $targetCodes = [$cleanExplicit];
            $resolvedScope = in_array($cleanExplicit, $audiences['birth'], true) ? 'birth' : 'married';
        } else {
            if ($resolvedScope === 'birth') {
                $targetCodes = !empty($audiences['birth']) ? $audiences['birth'] : $audiences['all'];
            } elseif ($resolvedScope === 'married') {
                $targetCodes = !empty($audiences['married']) ? $audiences['married'] : $audiences['all'];
            } else {
                $targetCodes = !empty($audiences['all']) ? $audiences['all'] : [];
            }
        }

        // Defensive fallback: if no codes found (rare), use session code if available
        if (empty($targetCodes) && !empty($_SESSION['famCode'])) {
            $sessCode = self::normaliseCode((string)$_SESSION['famCode']);
            if ($sessCode !== '') {
                $targetCodes = [$sessCode];
            }
        }

        $targetCodes = array_values(array_unique(array_filter($targetCodes)));
        $storedFamCode = implode(',', $targetCodes);

        // 1. Update post table
        try {
            $stmt = $this->pdo->prepare('UPDATE post SET target_scope = ?, postFamCode = ? WHERE post_no = ?');
            $stmt->execute([$resolvedScope, $storedFamCode, $postNo]);
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::assignPostTargets update post] ' . $e->getMessage());
        }

        // 2. Clear existing targets and insert new relational target rows
        try {
            $delStmt = $this->pdo->prepare('DELETE FROM post_family_targets WHERE post_no = ?');
            $delStmt->execute([$postNo]);

            if (!empty($targetCodes)) {
                $insStmt = $this->pdo->prepare(
                    'INSERT IGNORE INTO post_family_targets (post_no, family_code, lineage_kind) VALUES (?, ?, ?)'
                );
                foreach ($targetCodes as $code) {
                    $kind = in_array($code, $audiences['birth'], true) ? 'birth' : 'primary';
                    $insStmt->execute([$postNo, $code, $kind]);
                }
            }
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::assignPostTargets insert targets] ' . $e->getMessage());
        }

        return [
            'post_no' => $postNo,
            'target_scope' => $resolvedScope,
            'target_codes' => $targetCodes,
        ];
    }

    /**
     * Retrieve all family codes this post targets.
     *
     * @param int $postNo
     * @return list<string>
     */
    public function getPostTargetCodes(int $postNo): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT family_code FROM post_family_targets WHERE post_no = ?');
            $stmt->execute([$postNo]);
            $codes = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            if (!empty($codes)) {
                return array_values(array_unique(array_map('strval', $codes)));
            }

            // Fallback to post.postFamCode
            $pStmt = $this->pdo->prepare('SELECT postFamCode FROM post WHERE post_no = ? LIMIT 1');
            $pStmt->execute([$postNo]);
            $raw = (string)($pStmt->fetchColumn() ?: '');
            if ($raw !== '') {
                $exploded = array_map([self::class, 'normaliseCode'], explode(',', $raw));
                return array_values(array_unique(array_filter($exploded)));
            }
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::getPostTargetCodes] ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Check whether a user is permitted to view, comment on, or react to a specific post.
     * Enforces target audience isolation between Birth and Married lineages.
     *
     * @param int $postNo
     * @param string $userId
     * @return bool
     */
    public function canUserAccessPost(int $postNo, string $userId): bool
    {
        $userId = trim($userId);
        if ($postNo <= 0 || $userId === '') {
            return false;
        }

        try {
            // 1. Fetch post metadata
            $stmt = $this->pdo->prepare('SELECT id, postFamCode, post_status, date_deleted FROM post WHERE post_no = ? LIMIT 1');
            $stmt->execute([$postNo]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$post || !empty($post['date_deleted'])) {
                return false;
            }

            $authorId = (string)($post['id'] ?? '');

            // Author always has access to their own post
            if ($authorId === $userId) {
                return true;
            }

            // Platform official account broadcast is universally accessible
            if ($authorId === \App\services\FamilyPlatformAccountService::getAccountId()) {
                return true;
            }

            // User tagged in post has access
            $tagStmt = $this->pdo->prepare('SELECT 1 FROM post_tags WHERE post_no = ? AND user_id = ? LIMIT 1');
            $tagStmt->execute([$postNo, $userId]);
            if ($tagStmt->fetchColumn() !== false) {
                return true;
            }

            // 4. Check whether this post has explicit lineage targets in post_family_targets
            $targetCountStmt = $this->pdo->prepare('SELECT COUNT(*) FROM post_family_targets WHERE post_no = ?');
            $targetCountStmt->execute([$postNo]);
            $hasExplicitTargets = ((int)$targetCountStmt->fetchColumn()) > 0;

            // Resolve all family codes held by the viewer
            $viewerAudiences = $this->resolveUserAudiences($userId);
            $viewerCodes = $viewerAudiences['all'];
            if (!empty($_SESSION['famCode'])) {
                $sessCode = self::normaliseCode((string)$_SESSION['famCode']);
                if ($sessCode !== '' && !in_array($sessCode, $viewerCodes, true)) {
                    $viewerCodes[] = $sessCode;
                }
            }

            if ($hasExplicitTargets) {
                // STRICT LINEAGE GATE: Post was explicitly targeted. Viewer MUST hold at least one target family code.
                if (!empty($viewerCodes)) {
                    $inPlaceholders = implode(',', array_fill(0, count($viewerCodes), '?'));
                    $targetStmt = $this->pdo->prepare(
                        "SELECT 1 FROM post_family_targets WHERE post_no = ? AND family_code IN ($inPlaceholders) LIMIT 1"
                    );
                    $targetStmt->execute(array_merge([$postNo], $viewerCodes));
                    if ($targetStmt->fetchColumn() !== false) {
                        return true;
                    }
                }
                // Explicitly targeted post, and viewer holds no matching target code: ACCESS DENIED!
                return false;
            }

            // 5. Legacy post fallback (no records in post_family_targets)
            if (!empty($viewerCodes)) {
                $rawPostFam = (string)($post['postFamCode'] ?? '');
                if ($rawPostFam !== '') {
                    $postFamCodes = array_map([self::class, 'normaliseCode'], explode(',', $rawPostFam));
                    foreach ($viewerCodes as $vc) {
                        if (in_array($vc, $postFamCodes, true)) {
                            return true;
                        }
                    }
                }
            }

            // Check connected family members via requestMgt for legacy posts
            $rmStmt = $this->pdo->prepare(
                "SELECT 1 FROM requestMgt
                 WHERE (requester_id = ? AND approver_id = ?)
                    OR (approver_id = ? AND requester_id = ?)
                 LIMIT 1"
            );
            $rmStmt->execute([$userId, $authorId, $userId, $authorId]);
            if ($rmStmt->fetchColumn() !== false) {
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::canUserAccessPost] ' . $e->getMessage());
            return false;
        }
    }
}
