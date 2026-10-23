<?php
declare(strict_types=1);

namespace App\services;

use PDO;

/**
 * Single writer for relationship edits that touch the family graph
 * (family_nodes / family_unions / family_node_children).
 *
 * Model: a child hangs from a *union* (a pair of partner nodes). Half-siblings
 * are children of two different unions that share exactly one partner; former
 * partners are unions with is_current = 0 and a union_type of divorced /
 * separated / widowed. A missing parent is a placeholder node
 * (family_nodes.is_placeholder = 1) so half-siblings always hang from a real,
 * known parent.
 *
 * All methods are transaction-neutral: callers own begin/commit.
 * All lookups are scoped to the caller's family code and the base node's own
 * relatives (never family-wide name matching).
 */
final class FamilyGraphService
{
    public const FULL = 'full';
    public const SAME_MOTHER = 'same_mother';
    public const SAME_FATHER = 'same_father';
    public const STEP = 'step';
    public const ADOPTED = 'adopted';

    private const UNION_TYPES = ['married', 'divorced', 'separated', 'partner', 'widowed', 'annulled'];

    private const AVATAR = ['Male' => '/resources/images/profile/avatarM.png', 'Female' => '/resources/images/profile/avatarF.png', 'Other' => '/resources/images/profile/avatarM.png'];

    /** Maps the raw form / API value onto one of the parentage constants. */
    public static function normaliseParentage(?string $raw): string
    {
        return match (strtolower(trim((string)$raw))) {
            'same_mother', 'same mother only', 'mother', 'maternal' => self::SAME_MOTHER,
            'same_father', 'same father only', 'father', 'paternal' => self::SAME_FATHER,
            'step', 'step_sibling' => self::STEP,
            'adopted', 'adoptive' => self::ADOPTED,
            default => self::FULL,
        };
    }

    public static function normaliseUnionType(?string $raw): string
    {
        $v = strtolower(trim((string)$raw));
        return in_array($v, self::UNION_TYPES, true) ? $v : 'partner';
    }

    public static function normaliseGender(?string $raw, string $default = 'Other'): string
    {
        return match (strtolower(trim((string)$raw))) {
            'male', 'm', 'man', 'boy' => 'Male',
            'female', 'f', 'woman', 'girl' => 'Female',
            'other' => 'Other',
            default => $default,
        };
    }

    /**
     * Split "First Last" into [first, last].
     *
     * @return array{0: string, 1: string}
     */
    public static function splitName(string $full, string $fallbackLast = ''): array
    {
        $parts = preg_split('/\s+/', trim($full), 2);
        if ($parts === false) {
            return [trim($full), $fallbackLast];
        }
        return [$parts[0], $parts[1] ?? $fallbackLast];
    }

    // ------------------------------------------------------------------
    // Node helpers
    // ------------------------------------------------------------------

    /**
     * @param array<string,mixed> $f
     */
    public static function createNode(PDO $db, string $fam, array $f): int
    {
        $gender = self::normaliseGender($f['gender'] ?? null);
        $stmt = $db->prepare(
            'INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile, is_placeholder)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $fam,
            (string)($f['first_name'] ?? ''),
            (string)($f['last_name'] ?? ''),
            $gender,
            (int)($f['generation_level'] ?? 0),
            (string)($f['avatar_url'] ?? self::AVATAR[$gender]),
            (string)($f['bio'] ?? ''),
            self::nullIfEmpty($f['email'] ?? null),
            self::nullIfEmpty($f['mobile'] ?? null),
            !empty($f['is_placeholder']) ? 1 : 0,
        ]);
        return (int)$db->lastInsertId();
    }

    /** @return array<string,mixed>|null */
    public static function getNode(PDO $db, string $fam, int $id): ?array
    {
        $stmt = $db->prepare('SELECT * FROM family_nodes WHERE id = ? AND family_code = ? LIMIT 1');
        $stmt->execute([$id, $fam]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private static function placeholder(PDO $db, string $fam, string $gender, int $gen, string $label): int
    {
        return self::createNode($db, $fam, [
            'first_name' => $label,
            'gender' => $gender,
            'generation_level' => $gen,
            'bio' => $label,
            'is_placeholder' => 1,
        ]);
    }

    private static function nullIfEmpty(mixed $v): ?string
    {
        $s = trim((string)($v ?? ''));
        return $s === '' ? null : $s;
    }

    /**
     * @param array<string, mixed> $node
     */
    private static function sameName(array $node, string $first, string $last): bool
    {
        return strcasecmp(trim((string)$node['first_name']), trim($first)) === 0
            && strcasecmp(trim((string)$node['last_name']), trim($last)) === 0;
    }

    // ------------------------------------------------------------------
    // Union helpers
    // ------------------------------------------------------------------

    /** @return list<array<string,mixed>> every union the node is a partner in, oldest first */
    public static function unionsOf(PDO $db, string $fam, int $nodeId): array
    {
        $stmt = $db->prepare(
            'SELECT * FROM family_unions WHERE family_code = ? AND (partner_1_id = ? OR partner_2_id = ?) ORDER BY id ASC'
        );
        $stmt->execute([$fam, $nodeId, $nodeId]);
        return array_values($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function createUnion(PDO $db, string $fam, int $p1, int $p2, string $type = 'married', ?int $marriageYear = null, ?int $divorceYear = null, ?bool $isCurrent = null): int
    {
        $type = self::normaliseUnionType($type);
        $isCurrent = ($isCurrent ?? ($type === 'married')) ? 1 : 0;
        $stmt = $db->prepare(
            'INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, marriage_year, divorce_year, is_current)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$fam, $p1, $p2, $type, $marriageYear, $divorceYear, $isCurrent]);
        return (int)$db->lastInsertId();
    }

    private static function linkChild(PDO $db, int $unionId, int $childId, string $relationship = 'biological'): void
    {
        $chk = $db->prepare('SELECT id FROM family_node_children WHERE union_id = ? AND child_id = ? LIMIT 1');
        $chk->execute([$unionId, $childId]);
        if ($chk->fetchColumn()) {
            return;
        }
        $db->prepare('INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, ?)')
            ->execute([$unionId, $childId, $relationship]);
    }

    /**
     * @param array<string, mixed> $union
     */
    private static function otherPartnerId(array $union, int $nodeId): int
    {
        return (int)$union['partner_1_id'] === $nodeId ? (int)$union['partner_2_id'] : (int)$union['partner_1_id'];
    }

    /**
     * The union the base node was born into (their "main" parents). Creates
     * placeholder parents when the node has none yet.
     *
     * @return array{union_id:int, father_id:int, mother_id:int}
     */
    public static function ensureParentUnion(PDO $db, string $fam, int $baseId): array
    {
        $stmt = $db->prepare(
            'SELECT fnc.union_id FROM family_node_children fnc
               JOIN family_unions u ON u.id = fnc.union_id
              WHERE fnc.child_id = ? AND u.family_code = ?
              ORDER BY (u.partner_1_id <> u.partner_2_id) DESC, fnc.id ASC LIMIT 1'
        );
        $stmt->execute([$baseId, $fam]);
        $unionId = (int)$stmt->fetchColumn();

        $base = self::getNode($db, $fam, $baseId);
        $gen = (int)($base['generation_level'] ?? 0);

        if ($unionId <= 0) {
            $fId = self::placeholder($db, $fam, 'Male', $gen - 1, 'Unknown Father');
            $mId = self::placeholder($db, $fam, 'Female', $gen - 1, 'Unknown Mother');
            $unionId = self::createUnion($db, $fam, $fId, $mId, 'married');
            self::linkChild($db, $unionId, $baseId);
            return ['union_id' => $unionId, 'father_id' => $fId, 'mother_id' => $mId];
        }

        $u = $db->prepare('SELECT * FROM family_unions WHERE id = ?');
        $u->execute([$unionId]);
        $union = $u->fetch(PDO::FETCH_ASSOC) ?: [];
        $a = self::getNode($db, $fam, (int)($union['partner_1_id'] ?? 0));
        $b = self::getNode($db, $fam, (int)($union['partner_2_id'] ?? 0));

        $father = $a;
        $mother = $b;
        if (($a['gender'] ?? '') === 'Female' && ($b['gender'] ?? '') !== 'Female') {
            [$father, $mother] = [$b, $a];
        }
        // A single-parent union (p1 == p2) leaves the other side missing; fill it
        // with a placeholder so half-sibling routing always has both parent slots.
        if ((int)($union['partner_1_id'] ?? 0) === (int)($union['partner_2_id'] ?? 0) && $a) {
            if (($a['gender'] ?? '') === 'Female') {
                $father = null;
            } else {
                $mother = null;
            }
        }

        return [
            'union_id' => $unionId,
            'father_id' => (int)($father['id'] ?? 0),
            'mother_id' => (int)($mother['id'] ?? 0),
        ];
    }

    /**
     * Find (or create) the union that links $parentId to an "other parent" so a
     * half-sibling can hang from it. Reuses a union whose other partner is
     * the named person, or an existing placeholder partner when no name given.
     */
    private static function halfUnionFor(PDO $db, string $fam, int $parentId, int $mainUnionId, string $otherFirst, string $otherLast, int $parentGen): int
    {
        $parent = self::getNode($db, $fam, $parentId);
        $otherGender = ($parent['gender'] ?? 'Male') === 'Female' ? 'Male' : 'Female';
        $named = trim("$otherFirst $otherLast") !== '';

        foreach (self::unionsOf($db, $fam, $parentId) as $u) {
            if ((int)$u['id'] === $mainUnionId) {
                continue;
            }
            $otherId = self::otherPartnerId($u, $parentId);
            $other = self::getNode($db, $fam, $otherId);
            if (!$other) {
                continue;
            }
            if ($named && self::sameName($other, $otherFirst, $otherLast)) {
                return (int)$u['id'];
            }
            if (!$named && (int)$other['is_placeholder'] === 1) {
                return (int)$u['id'];
            }
        }

        $otherId = $named
            ? self::createNode($db, $fam, ['first_name' => $otherFirst, 'last_name' => $otherLast, 'gender' => $otherGender, 'generation_level' => $parentGen, 'bio' => 'Partner'])
            : self::placeholder($db, $fam, $otherGender, $parentGen, $otherGender === 'Female' ? 'Unknown Mother' : 'Unknown Father');

        return self::createUnion($db, $fam, $parentId, $otherId, 'partner');
    }

    // ------------------------------------------------------------------
    // Siblings
    // ------------------------------------------------------------------

    /**
     * Create or re-home a sibling. Idempotent: an existing sibling of the same
     * name (among the base node's own relatives) is moved to the correct union
     * rather than duplicated.
     *
     * @param array<string, mixed> $d
     * @param array<string, mixed>|null $ctx out: union_id, parentage, shared_parent_id
     * @return int sibling node id (0 if the data had no name)
     */
    public static function upsertSibling(PDO $db, string $fam, int $baseId, array $d, ?array &$ctx = null): int
    {
        $first = trim((string)($d['first_name'] ?? ''));
        $last = trim((string)($d['last_name'] ?? ''));
        if ($first === '') {
            return 0;
        }
        $parentage = self::normaliseParentage($d['parentage'] ?? null);
        $base = self::getNode($db, $fam, $baseId);
        if (!$base) {
            return 0;
        }
        $gen = (int)$base['generation_level'];

        $main = self::ensureParentUnion($db, $fam, $baseId);
        [$otherFirst, $otherLast] = self::splitName((string)($d['other_parent_name'] ?? ''));

        $targetUnion = $main['union_id'];
        $sharedParentId = 0;
        $relationship = $parentage === self::ADOPTED ? 'adopted' : ($parentage === self::STEP ? 'step' : 'biological');

        if ($parentage === self::SAME_MOTHER || $parentage === self::SAME_FATHER) {
            $parentId = $parentage === self::SAME_MOTHER ? $main['mother_id'] : $main['father_id'];
            if ($parentId <= 0) {
                // The shared parent isn't on record yet: create the placeholder slot.
                $parentId = self::placeholder(
                    $db, $fam,
                    $parentage === self::SAME_MOTHER ? 'Female' : 'Male', $gen - 1,
                    $parentage === self::SAME_MOTHER ? 'Unknown Mother' : 'Unknown Father'
                );
            }
            $targetUnion = self::halfUnionFor($db, $fam, $parentId, $main['union_id'], $otherFirst, $otherLast, $gen - 1);
            $sharedParentId = $parentId;
        }

        // Existing sibling (child of any union that includes one of the base's parents)?
        $parentIds = array_values(array_filter([$main['father_id'], $main['mother_id']]));
        $sibId = self::findExistingSibling($db, $fam, $baseId, $parentIds, $first, $last);

        $gender = self::normaliseGender($d['gender'] ?? null, 'Other');
        if ($sibId > 0) {
            $db->prepare(
                "UPDATE family_nodes SET gender = IF(? = 'Other', gender, ?), email = COALESCE(NULLIF(?, ''), email) WHERE id = ?"
            )->execute([$gender, $gender, (string)($d['email'] ?? ''), $sibId]);
        } else {
            $sibId = self::createNode($db, $fam, [
                'first_name' => $first,
                'last_name' => $last !== '' ? $last : (string)$base['last_name'],
                'gender' => $gender,
                'generation_level' => $gen,
                'bio' => 'Sibling',
                'email' => $d['email'] ?? null,
                'mobile' => $d['mobile'] ?? null,
            ]);
        }

        // Re-home: drop links to the base's other parent unions, keep only the target.
        $unionIds = self::unionIdsContaining($db, $fam, $parentIds);
        $unionIds = array_values(array_diff($unionIds, [$targetUnion]));
        if ($unionIds) {
            $in = implode(',', array_fill(0, count($unionIds), '?'));
            $db->prepare("DELETE FROM family_node_children WHERE child_id = ? AND union_id IN ($in)")
                ->execute([$sibId, ...$unionIds]);
        }
        self::linkChild($db, $targetUnion, $sibId, $relationship);

        $ctx = ['union_id' => $targetUnion, 'parentage' => $parentage, 'shared_parent_id' => $sharedParentId];
        return $sibId;
    }

    /**
     * @param list<int> $parentIds
     * @return list<int>
     */
    private static function unionIdsContaining(PDO $db, string $fam, array $parentIds): array
    {
        if (!$parentIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($parentIds), '?'));
        $stmt = $db->prepare(
            "SELECT id FROM family_unions WHERE family_code = ? AND (partner_1_id IN ($in) OR partner_2_id IN ($in))"
        );
        $stmt->execute([$fam, ...$parentIds, ...$parentIds]);
        return array_values(array_map(intval(...), $stmt->fetchAll(PDO::FETCH_COLUMN)));
    }

    /**
     * @param list<int> $parentIds
     */
    private static function findExistingSibling(PDO $db, string $fam, int $baseId, array $parentIds, string $first, string $last): int
    {
        $unionIds = self::unionIdsContaining($db, $fam, $parentIds);
        if (!$unionIds) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($unionIds), '?'));
        $stmt = $db->prepare(
            "SELECT n.* FROM family_node_children fnc JOIN family_nodes n ON n.id = fnc.child_id
              WHERE fnc.union_id IN ($in) AND n.id <> ? AND n.family_code = ?"
        );
        $stmt->execute([...$unionIds, $baseId, $fam]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
            if (strcasecmp(trim((string)$n['first_name']), $first) === 0
                && ($last === '' || strcasecmp(trim((string)$n['last_name']), $last) === 0)) {
                return (int)$n['id'];
            }
        }
        return 0;
    }

    // ------------------------------------------------------------------
    // Parents
    // ------------------------------------------------------------------

    /**
     * Set the relationship status of the base node's parents (together,
     * divorced, separated, widowed ...) on their main union.
     */
    public static function setParentsStatus(PDO $db, string $fam, int $baseId, string $status, ?int $endYear = null): void
    {
        $type = self::normaliseUnionType($status);
        $main = self::ensureParentUnion($db, $fam, $baseId);
        $isCurrent = $type === 'married' || $type === 'partner' ? 1 : 0;
        $db->prepare('UPDATE family_unions SET union_type = ?, is_current = ?, divorce_year = COALESCE(?, divorce_year) WHERE id = ? AND family_code = ?')
            ->execute([$type, $isCurrent, $endYear, $main['union_id'], $fam]);
    }

    // ------------------------------------------------------------------
    // Partners & children
    // ------------------------------------------------------------------

    /**
     * Create or update a partner union for the base node.
     * Current partner: reuses the node's current union (fills a placeholder
     * partner or renames) instead of stacking duplicates on every save.
     * Former partner: matched by name among the node's non-current unions.
     *
     * @param array<string, mixed> $d
     * @return array{union_id:int, partner_id:int}
     */
    public static function upsertPartner(PDO $db, string $fam, int $baseId, array $d): array
    {
        $base = self::getNode($db, $fam, $baseId);
        $first = trim((string)($d['first_name'] ?? ''));
        $last = trim((string)($d['last_name'] ?? ''));
        if (!$base || $first === '') {
            return ['union_id' => 0, 'partner_id' => 0];
        }
        $type = self::normaliseUnionType($d['status'] ?? 'married');
        $current = in_array($type, ['married', 'partner'], true);
        $default = ($base['gender'] ?? 'Male') === 'Male' ? 'Female' : 'Male';
        $gender = self::normaliseGender($d['gender'] ?? null, $default);
        $gen = (int)$base['generation_level'];

        $match = null;
        foreach (self::unionsOf($db, $fam, $baseId) as $u) {
            if ((int)$u['partner_1_id'] === (int)$u['partner_2_id']) {
                continue; // single-parent shell, not a partnership
            }
            $other = self::getNode($db, $fam, self::otherPartnerId($u, $baseId));
            if (!$other) {
                continue;
            }
            $named = self::sameName($other, $first, $last) || strcasecmp(trim((string)$other['first_name']), $first) === 0;
            if ($named || ($current && (int)$u['is_current'] === 1 && (int)$other['is_placeholder'] === 1)) {
                $match = [$u, $other];
                break;
            }
            if ($current && (int)$u['is_current'] === 1 && $match === null) {
                $match = [$u, $other]; // replace the existing current partner's name
                // keep scanning for an exact name match first
            }
        }

        if ($match) {
            [$u, $other] = $match;
            $db->prepare(
                "UPDATE family_nodes SET first_name = ?, last_name = ?, is_placeholder = 0,
                        email = COALESCE(NULLIF(?, ''), email), mobile = COALESCE(NULLIF(?, ''), mobile) WHERE id = ?"
            )->execute([$first, $last, (string)($d['email'] ?? ''), (string)($d['mobile'] ?? ''), (int)$other['id']]);
            $db->prepare('UPDATE family_unions SET union_type = ?, is_current = ? WHERE id = ?')
                ->execute([$type, $current ? 1 : 0, (int)$u['id']]);
            return ['union_id' => (int)$u['id'], 'partner_id' => (int)$other['id']];
        }

        $partnerId = self::createNode($db, $fam, [
            'first_name' => $first, 'last_name' => $last, 'gender' => $gender, 'generation_level' => $gen,
            'bio' => 'Partner/Spouse', 'email' => $d['email'] ?? null, 'mobile' => $d['mobile'] ?? null,
        ]);
        $unionId = self::createUnion($db, $fam, $baseId, $partnerId, $type, null, null, $current);
        return ['union_id' => $unionId, 'partner_id' => $partnerId];
    }

    /**
     * Always create a NEW partnership for the node (named partner or unnamed
     * placeholder) and return the union id. Used when a child belongs to
     * someone who is not yet in the tree.
     */
    public static function createPartnerUnion(PDO $db, string $fam, int $baseId, string $partnerName, string $status): int
    {
        $base = self::getNode($db, $fam, $baseId);
        if (!$base) {
            return 0;
        }
        $type = self::normaliseUnionType($status);
        $current = in_array($type, ['married', 'partner'], true) && $status === 'married';
        $gen = (int)$base['generation_level'];
        $gender = ($base['gender'] ?? 'Male') === 'Male' ? 'Female' : 'Male';
        [$first, $last] = self::splitName($partnerName);
        $partnerId = $first !== ''
            ? self::createNode($db, $fam, ['first_name' => $first, 'last_name' => $last, 'gender' => $gender, 'generation_level' => $gen, 'bio' => 'Partner/Spouse'])
            : self::placeholder($db, $fam, $gender, $gen, $type === 'married' ? 'Unknown Spouse' : 'Unknown Partner');
        return self::createUnion($db, $fam, $baseId, $partnerId, $type, null, null, $current);
    }

    /**
     * Create or update a child and link it to the right union.
     *
     * $with: 'spouse'  -> current union (placeholder partner if none)
     *        'former'  -> union with the named former partner (status given)
     *        'other'   -> single-parent / unknown partner union
     *
     * @param array<string, mixed> $d
     * @param array<string, mixed>|null $ctx out: union_id, partner_id
     */
    public static function upsertChild(PDO $db, string $fam, int $baseId, array $d, ?array &$ctx = null): int
    {
        $first = trim((string)($d['first_name'] ?? ''));
        $last = trim((string)($d['last_name'] ?? ''));
        $base = self::getNode($db, $fam, $baseId);
        if (!$base || $first === '') {
            return 0;
        }
        $gen = (int)$base['generation_level'];
        $with = strtolower((string)($d['with'] ?? 'other'));
        [$opFirst, $opLast] = self::splitName((string)($d['other_parent_name'] ?? ''));
        $partnerGender = ($base['gender'] ?? 'Male') === 'Male' ? 'Female' : 'Male';
        $unionId = 0;

        if ($with === 'spouse') {
            foreach (self::unionsOf($db, $fam, $baseId) as $u) {
                if ((int)$u['is_current'] === 1 && (int)$u['partner_1_id'] !== (int)$u['partner_2_id']) {
                    $unionId = (int)$u['id'];
                    break;
                }
            }
            if ($unionId === 0) {
                $pid = self::placeholder($db, $fam, $partnerGender, $gen, 'Unknown Spouse');
                $unionId = self::createUnion($db, $fam, $baseId, $pid, 'married');
            }
        } elseif ($with === 'former') {
            $status = self::normaliseUnionType($d['union_status'] ?? 'divorced');
            if ($status === 'married') {
                $status = 'divorced';
            }
            $named = trim("$opFirst $opLast") !== '';
            foreach (self::unionsOf($db, $fam, $baseId) as $u) {
                if ((int)$u['is_current'] === 1 || (int)$u['partner_1_id'] === (int)$u['partner_2_id']) {
                    continue;
                }
                $other = self::getNode($db, $fam, self::otherPartnerId($u, $baseId));
                if ($other && (($named && self::sameName($other, $opFirst, $opLast)) || (!$named && (int)$other['is_placeholder'] === 1))) {
                    $unionId = (int)$u['id'];
                    break;
                }
            }
            if ($unionId === 0) {
                $pid = $named
                    ? self::createNode($db, $fam, ['first_name' => $opFirst, 'last_name' => $opLast, 'gender' => $partnerGender, 'generation_level' => $gen, 'bio' => 'Former Partner'])
                    : self::placeholder($db, $fam, $partnerGender, $gen, 'Former Partner');
                $unionId = self::createUnion($db, $fam, $baseId, $pid, $status);
            }
        } else {
            foreach (self::unionsOf($db, $fam, $baseId) as $u) {
                $other = self::getNode($db, $fam, self::otherPartnerId($u, $baseId));
                if ($other && (int)$other['is_placeholder'] === 1 && (int)$u['is_current'] === 0 && $other['bio'] === 'Unknown Partner') {
                    $unionId = (int)$u['id'];
                    break;
                }
            }
            if ($unionId === 0) {
                $pid = self::placeholder($db, $fam, $partnerGender, $gen, 'Unknown Partner');
                $unionId = self::createUnion($db, $fam, $baseId, $pid, 'partner');
            }
        }

        // Match an existing child of this node by name (idempotent re-save).
        $childId = 0;
        $stmt = $db->prepare(
            'SELECT n.* FROM family_node_children fnc
               JOIN family_unions u ON u.id = fnc.union_id
               JOIN family_nodes n ON n.id = fnc.child_id
              WHERE u.family_code = ? AND (u.partner_1_id = ? OR u.partner_2_id = ?)'
        );
        $stmt->execute([$fam, $baseId, $baseId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
            if (strcasecmp(trim((string)$n['first_name']), $first) === 0
                && ($last === '' || strcasecmp(trim((string)$n['last_name']), $last) === 0)) {
                $childId = (int)$n['id'];
                break;
            }
        }

        $gender = self::normaliseGender($d['gender'] ?? null, 'Other');
        if ($childId > 0) {
            $db->prepare("UPDATE family_nodes SET gender = IF(? = 'Other', gender, ?), email = COALESCE(NULLIF(?, ''), email) WHERE id = ?")
                ->execute([$gender, $gender, (string)($d['email'] ?? ''), $childId]);
            // Re-home to the chosen union (only among this node's own unions).
            $own = array_map(static fn(array $u): int => (int)$u['id'], self::unionsOf($db, $fam, $baseId));
            $others = array_values(array_diff($own, [$unionId]));
            if ($others) {
                $in = implode(',', array_fill(0, count($others), '?'));
                $db->prepare("DELETE FROM family_node_children WHERE child_id = ? AND union_id IN ($in)")->execute([$childId, ...$others]);
            }
        } else {
            $childId = self::createNode($db, $fam, [
                'first_name' => $first,
                'last_name' => $last !== '' ? $last : (string)$base['last_name'],
                'gender' => $gender,
                'generation_level' => $gen + 1,
                'bio' => 'Child',
                'email' => $d['email'] ?? null,
            ]);
        }
        self::linkChild($db, $unionId, $childId);

        $uRow = $db->prepare('SELECT * FROM family_unions WHERE id = ?');
        $uRow->execute([$unionId]);
        $union = $uRow->fetch(PDO::FETCH_ASSOC) ?: [];
        $ctx = ['union_id' => $unionId, 'partner_id' => $union ? self::otherPartnerId($union, $baseId) : 0, 'with' => $with];
        return $childId;
    }

    // ------------------------------------------------------------------
    // Derived relationship (used by renderer / labels / tests)
    // ------------------------------------------------------------------

    /**
     * @return array{type:string, shared_parents:list<int>}  type: full|half|step|none
     */
    public static function siblingKind(PDO $db, string $fam, int $aId, int $bId): array
    {
        $parents = static function (int $id) use ($db, $fam): array {
            $stmt = $db->prepare(
                'SELECT u.partner_1_id, u.partner_2_id, fnc.relationship_type FROM family_node_children fnc
                   JOIN family_unions u ON u.id = fnc.union_id
                  WHERE fnc.child_id = ? AND u.family_code = ?'
            );
            $stmt->execute([$id, $fam]);
            $out = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                foreach ([(int)$r['partner_1_id'], (int)$r['partner_2_id']] as $p) {
                    $out[$p] = $r['relationship_type'];
                }
            }
            return $out;
        };
        $pa = $parents($aId);
        $pb = $parents($bId);
        $shared = array_values(array_intersect(array_keys($pa), array_keys($pb)));
        $real = array_values(array_filter($shared, static function (int $pid) use ($db, $fam): bool {
            $n = self::getNode($db, $fam, $pid);
            return $n !== null && (int)$n['is_placeholder'] === 0;
        }));
        $count = count($shared);
        return [
            'type' => $count >= 2 ? 'full' : ($count === 1 ? 'half' : 'none'),
            'shared_parents' => $real,
        ];
    }
}
