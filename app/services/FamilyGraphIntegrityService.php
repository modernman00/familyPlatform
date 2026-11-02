<?php

declare(strict_types=1);

namespace App\services;

use PDO;

/**
 * Structural integrity rules for the family P-graph (family_nodes / family_unions / family_node_children).
 *
 * Root cause this service exists to prevent (production incident, Nov 2026):
 * a member who joined a family was auto-attached as a CHILD of the grandparents' union (generation -2)
 * instead of their parents' union (generation -1), so their own brother rendered as
 * "Maternal Parent's Sibling" and their brother as a "Maternal Uncle".
 *
 * Invariants enforced here:
 *  1. A generation-0 node may only be auto-attached to a union whose partners sit at generation -1.
 *  2. The union must be identified by SURNAME EVIDENCE, never by "the union with the most children".
 *  3. Ambiguity (0 or >1 matching unions) never results in a guess; the node is left unattached.
 */
final class FamilyGraphIntegrityService
{
    /**
     * ATG Governance Invariant: Zero-Silent-Graft Rule.
     * Automated background attachment of unlinked members to any parent union is strictly decommissioned.
     *
     * @return int Always 0 under the Zero-Silent-Graft invariant
     */
    public static function attachOrphanedGen0(PDO $db, string $familyCode, string $rootUserId): int
    {
        return 0;
    }

    /**
     * Get unplaced members in a family who have not yet been connected to any parent or partner union.
     * These members appear in the Unplaced Members Tray / Staging Dock on the canvas.
     *
     * @return list<array<string, mixed>>
     */
    public static function getUnplacedMembers(PDO $db, string $familyCode): array
    {
        $stmt = $db->prepare('
            SELECT fn.id, fn.user_id, fn.first_name, fn.last_name, fn.gender, fn.avatar_url, fn.bio, fn.created_at
            FROM family_nodes fn
            LEFT JOIN family_node_children fnc ON fnc.child_id = fn.id
            LEFT JOIN family_unions fu ON (fu.partner_1_id = fn.id OR fu.partner_2_id = fn.id) AND fu.family_code = fn.family_code
            WHERE fn.family_code = ?
              AND fn.is_placeholder = 0
              AND fnc.id IS NULL
              AND fu.id IS NULL
            ORDER BY fn.id ASC
        ');
        $stmt->execute([$familyCode]);
        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * Pure selection rule (unit-testable): exactly one candidate union whose partner surname equals the
     * member's surname. Returns null when there is no evidence or the evidence is ambiguous.
     *
     * @param list<array<string, mixed>> $candidates
     * @return array<string, mixed>|null
     */
    public static function pickUnionForSurname(array $candidates, string $surname, int $nodeId = 0): ?array
    {
        $needle = self::norm($surname);
        if ($needle === '') {
            return null;
        }

        $matches = [];
        foreach ($candidates as $c) {
            if ($nodeId > 0 && ((int)$c['partner_1_id'] === $nodeId || (int)$c['partner_2_id'] === $nodeId)) {
                continue;
            }
            if (self::norm((string)($c['p1_last'] ?? '')) === $needle || self::norm((string)($c['p2_last'] ?? '')) === $needle) {
                $matches[] = $c;
            }
        }

        return count($matches) === 1 ? $matches[0] : null;
    }

    /**
     * Unions whose partners are exactly one generation above generation-0 members (generation -1).
     * Grandparent unions (generation -2 and above) are deliberately excluded.
     *
     * @return list<array<string, mixed>>
     */
    public static function parentUnionCandidates(PDO $db, string $familyCode): array
    {
        $stmt = $db->prepare('
            SELECT fu.id, fu.partner_1_id, fu.partner_2_id,
                   p1.last_name AS p1_last, p2.last_name AS p2_last
            FROM family_unions fu
            LEFT JOIN family_nodes p1 ON p1.id = fu.partner_1_id
            LEFT JOIN family_nodes p2 ON p2.id = fu.partner_2_id
            WHERE fu.family_code = ?
              AND (p1.id IS NOT NULL OR p2.id IS NOT NULL)
              AND COALESCE(p1.generation_level, -1) = -1
              AND COALESCE(p2.generation_level, -1) = -1
            ORDER BY fu.id ASC
        ');
        $stmt->execute([$familyCode]);

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * Detect registered members (user_id set, generation 0) hanging off a union of generation -2 or older.
     * Used by the regression suite and safe to call from diagnostics; read-only.
     *
     * @return list<array<string, mixed>>
     */
    public static function findMisplacedMembers(PDO $db, ?string $familyCode = null): array
    {
        $sql = '
            SELECT n.id AS node_id, n.first_name, n.last_name, n.family_code, c.id AS link_id, c.union_id
            FROM family_node_children c
            JOIN family_unions u ON u.id = c.union_id
            JOIN family_nodes n ON n.id = c.child_id
            LEFT JOIN family_nodes p1 ON p1.id = u.partner_1_id
            LEFT JOIN family_nodes p2 ON p2.id = u.partner_2_id
            WHERE n.generation_level = 0
              AND n.user_id IS NOT NULL
              AND (p1.generation_level <= -2 OR p2.generation_level <= -2)';
        $params = [];
        if ($familyCode !== null) {
            $sql .= ' AND n.family_code = ?';
            $params[] = $familyCode;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    private static function norm(string $s): string
    {
        return strtolower(trim($s));
    }
}
