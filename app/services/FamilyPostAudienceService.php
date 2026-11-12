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
     * Resolve all held family codes for a user, categorised by lineage kind (household, paternal, maternal, birth, married).
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
     *     birth_fam_code: string,
     *     paternal_fam_code: string,
     *     maternal_fam_code: string,
     *     household_fam_code: string,
     *     is_married: bool,
     *     items: list<array{
     *         code: string,
     *         role: string,
     *         label: string,
     *         kind: string,
     *         icon: string,
     *         description: string
     *     }>
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
                'paternal_fam_code' => '',
                'maternal_fam_code' => '',
                'household_fam_code' => '',
                'is_married' => false,
                'items' => [],
            ];
        }

        $birthCodes = [];
        $marriedCodes = [];

        // 1. Personal table metadata (primary code, marital status, gender)
        $primaryCode = '';
        $maritalStatus = '';
        $gender = '';
        try {
            $pStmt = $this->pdo->prepare('SELECT famCode, marital_status, gender FROM personal WHERE id = ? LIMIT 1');
            $pStmt->execute([$userId]);
            $pRow = $pStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $rawPrimary = (string)($pRow['famCode'] ?? '');
            $maritalStatus = strtolower(trim((string)($pRow['marital_status'] ?? '')));
            $gender = strtolower(trim((string)($pRow['gender'] ?? '')));
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
        $activeBirthCode = '';
        try {
            $birthStatus = (new BirthFamilyCodeService($this->pdo))->status($userId);
            if (($birthStatus['state'] ?? '') === 'active' && !empty($birthStatus['code'])) {
                $activeBirthCode = self::normaliseCode((string)$birthStatus['code']);
                if ($activeBirthCode !== '' && !in_array($activeBirthCode, $birthCodes, true)) {
                    $birthCodes[] = $activeBirthCode;
                }
            }
        } catch (\Throwable $e) {
            error_log('[FamilyPostAudienceService::resolveUserAudiences birth] ' . $e->getMessage());
        }

        // 3. Check otherFamily stored code, spouse, and maiden name
        $ofCode = '';
        $maidenName = '';
        $spouseName = '';
        try {
            $ofStmt = $this->pdo->prepare('SELECT otherFamCode, maiden_name, spouse_name FROM otherFamily WHERE id = ? ORDER BY no ASC LIMIT 1');
            $ofStmt->execute([$userId]);
            $ofRow = $ofStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $rawOf = (string)($ofRow['otherFamCode'] ?? '');
            $maidenName = trim((string)($ofRow['maiden_name'] ?? ''));
            $spouseName = trim((string)($ofRow['spouse_name'] ?? ''));
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

        $isMarried = ($maritalStatus === 'married') || ($spouseName !== '') || ($maidenName !== '');
        $isFemale = ($gender === 'female') || ($maidenName !== '');

        // Lineage role resolution:
        $householdCode = '';
        $maidenCode = '';
        $paternalCode = '';
        $maternalCode = '';
        $spouseCode = '';

        if ($isMarried) {
            $householdCode = $primaryCode !== '' ? $primaryCode : ($marriedCodes[0] ?? '');
            if ($isFemale) {
                // For a married woman, her primary birth code is her Maiden / Birth Family
                $maidenCode = $ofCode !== '' ? $ofCode : ($activeBirthCode !== '' ? $activeBirthCode : ($birthCodes[0] ?? ''));
                // If she holds another distinct birth code, that represents maternal roots
                foreach ($birthCodes as $bc) {
                    if ($bc !== $maidenCode && $bc !== $householdCode) {
                        $maternalCode = $bc;
                        break;
                    }
                }
            } else {
                // For a married man, otherFamCode is his spouse's family code
                if ($ofCode !== '' && $ofCode !== $householdCode) {
                    $spouseCode = $ofCode;
                }
                foreach ($birthCodes as $bc) {
                    if ($bc !== $householdCode && $bc !== $spouseCode) {
                        $paternalCode = $bc;
                        break;
                    }
                }
            }
        } else {
            // Unmarried / Youth / Child:
            $paternalCode = $primaryCode !== '' ? $primaryCode : ($birthCodes[0] ?? '');
            if ($ofCode !== '' && $ofCode !== $paternalCode) {
                $maternalCode = $ofCode;
            } elseif (!empty($birthCodes)) {
                foreach ($birthCodes as $bc) {
                    if ($bc !== $paternalCode) {
                        $maternalCode = $bc;
                        break;
                    }
                }
            }
        }

        // Construct discrete audience items for post modal targeting
        $items = [];
        $seenCodes = [];

        if ($householdCode !== '') {
            $items[] = [
                'code' => $householdCode,
                'role' => 'household',
                'label' => 'Household / Marital Family (#' . $householdCode . ')',
                'kind' => 'married',
                'icon' => 'bi-house-heart-fill text-success',
                'description' => 'Visible only to your household relatives (#' . $householdCode . '). Ancestral roots & Connected Kin excluded.',
            ];
            $seenCodes[$householdCode] = true;
        }

        if ($maidenCode !== '' && empty($seenCodes[$maidenCode])) {
            $maidenLabel = $maidenName !== '' ? 'Birth / Maiden Family (' . $maidenName . ' - #' . $maidenCode . ')' : 'Birth / Maiden Family (#' . $maidenCode . ')';
            $items[] = [
                'code' => $maidenCode,
                'role' => 'maiden',
                'label' => $maidenLabel,
                'kind' => 'birth',
                'icon' => 'bi-flower1 text-danger',
                'description' => 'Visible only to your birth / maiden relatives (#' . $maidenCode . '). Marital household & Connected Kin excluded.',
            ];
            $seenCodes[$maidenCode] = true;
        }

        if ($paternalCode !== '' && empty($seenCodes[$paternalCode])) {
            $items[] = [
                'code' => $paternalCode,
                'role' => 'paternal',
                'label' => 'Paternal Roots (#' . $paternalCode . ')',
                'kind' => 'birth',
                'icon' => 'bi-diagram-3-fill text-danger',
                'description' => 'Visible only to your father\'s / paternal relatives (#' . $paternalCode . '). Maternal relatives & Connected Kin excluded.',
            ];
            $seenCodes[$paternalCode] = true;
        }

        if ($maternalCode !== '' && empty($seenCodes[$maternalCode])) {
            $items[] = [
                'code' => $maternalCode,
                'role' => 'maternal',
                'label' => 'Maternal Roots (#' . $maternalCode . ')',
                'kind' => 'birth',
                'icon' => 'bi-heart-pulse-fill text-purple',
                'description' => 'Visible only to your mother\'s / maternal relatives (#' . $maternalCode . '). Paternal relatives & Connected Kin excluded.',
            ];
            $seenCodes[$maternalCode] = true;
        }

        if ($spouseCode !== '' && empty($seenCodes[$spouseCode])) {
            $items[] = [
                'code' => $spouseCode,
                'role' => 'spouse_family',
                'label' => 'Spouse\'s Family (#' . $spouseCode . ')',
                'kind' => 'birth',
                'icon' => 'bi-heart-fill text-danger',
                'description' => 'Visible only to your spouse\'s relatives (#' . $spouseCode . '). Household & Connected Kin excluded.',
            ];
            $seenCodes[$spouseCode] = true;
        }

        // Append any remaining held family codes (e.g. from approved user_families)
        foreach ($allCodes as $code) {
            if (!empty($seenCodes[$code])) {
                continue;
            }
            $k = in_array($code, $birthCodes, true) ? 'birth' : 'primary';
            $items[] = [
                'code' => $code,
                'role' => 'family',
                'label' => 'Family Circle (#' . $code . ')',
                'kind' => $k === 'birth' ? 'birth' : 'married',
                'icon' => $k === 'birth' ? 'bi-tree text-warning' : 'bi-people text-primary',
                'description' => 'Visible only to members of #' . $code . '. Other branches & Connected Kin excluded.',
            ];
            $seenCodes[$code] = true;
        }

        $hasDualLineage = !empty($birthCodes) && !empty($marriedCodes);
        $canChoose = $hasDualLineage || count($allCodes) > 1 || count($items) > 1;

        return [
            'all' => $allCodes,
            'birth' => $birthCodes,
            'married' => $marriedCodes,
            'has_dual_lineage' => $hasDualLineage,
            'can_choose' => $canChoose,
            'default_scope' => $hasDualLineage ? 'all' : (!empty($birthCodes) ? 'birth' : 'married'),
            'primary_fam_code' => $primaryCode !== '' ? $primaryCode : ($marriedCodes[0] ?? ($allCodes[0] ?? '')),
            'birth_fam_code' => $birthCodes[0] ?? '',
            'paternal_fam_code' => $paternalCode,
            'maternal_fam_code' => $maternalCode,
            'household_fam_code' => $householdCode,
            'is_married' => $isMarried,
            'items' => $items,
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
        $lowerScope = strtolower($requestedScope);

        $targetCodes = [];
        $resolvedScope = 'all';

        // Check if user specified a valid held explicit code (only when not broadcasting to 'all')
        if ($lowerScope !== 'all' && $cleanExplicit !== '' && in_array($cleanExplicit, $audiences['all'], true)) {
            $targetCodes = [$cleanExplicit];
            $resolvedScope = in_array($cleanExplicit, $audiences['birth'], true) ? 'birth' : 'married';
        } else {
            if ($lowerScope === 'paternal' && !empty($audiences['paternal_fam_code'])) {
                $targetCodes = [$audiences['paternal_fam_code']];
                $resolvedScope = 'birth';
            } elseif ($lowerScope === 'maternal' && !empty($audiences['maternal_fam_code'])) {
                $targetCodes = [$audiences['maternal_fam_code']];
                $resolvedScope = 'birth';
            } elseif ($lowerScope === 'maiden') {
                $targetCodes = !empty($audiences['birth']) ? [$audiences['birth'][0]] : [];
                $resolvedScope = 'birth';
            } elseif (($lowerScope === 'household' || $lowerScope === 'married') && !empty($audiences['household_fam_code'])) {
                $targetCodes = [$audiences['household_fam_code']];
                $resolvedScope = 'married';
            } elseif ($lowerScope === 'birth') {
                $targetCodes = !empty($audiences['birth']) ? $audiences['birth'] : $audiences['all'];
                $resolvedScope = 'birth';
            } elseif ($lowerScope === 'married') {
                $targetCodes = !empty($audiences['married']) ? $audiences['married'] : $audiences['all'];
                $resolvedScope = 'married';
            } else {
                $targetCodes = !empty($audiences['all']) ? $audiences['all'] : [];
                $resolvedScope = 'all';
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
            $stmt = $this->pdo->prepare('SELECT id, postFamCode, target_scope, post_status, date_deleted FROM post WHERE post_no = ? LIMIT 1');
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

            // Selective member restriction (overrides post_tags, family code, and requestMgt)
            if (\App\services\MemberRestrictionService::isRestricted($this->pdo, $authorId, $userId, 'posts')) {
                return false;
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
                // If viewer holds at least one target family code, access is granted
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

                // OPTION A KINSHIP GATE: If target_scope is 'all', approved connected kin in requestMgt also have access
                $targetScope = (string)($post['target_scope'] ?? 'all');
                if ($targetScope === 'all') {
                    $rmTargetStmt = $this->pdo->prepare(
                        "SELECT 1 FROM requestMgt
                         WHERE ((requester_id = ? AND approver_id = ?) OR (approver_id = ? AND requester_id = ?))
                           AND LOWER(status) IN ('approved', 'accepted')
                         LIMIT 1"
                    );
                    $rmTargetStmt->execute([$userId, $authorId, $userId, $authorId]);
                    if ($rmTargetStmt->fetchColumn() !== false) {
                        return true;
                    }
                }

                // Explicitly targeted post (e.g. birth or married only, or unapproved contact): ACCESS DENIED!
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
