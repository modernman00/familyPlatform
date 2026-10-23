<?php
declare(strict_types=1);

namespace App\services;

use PDO;

/**
 * Confirmation requests for sensitive relationships (half/step siblings and
 * former partners). The relative is shown in the tree straight away; the
 * people on the other side (the shared parent, the relative themself, or the
 * former partner) are asked to confirm. Declining removes the link again.
 */
final class FamilyRelationshipRequestService
{
    public const HALF_SIBLING = 'half_sibling';
    public const STEP_SIBLING = 'step_sibling';
    public const FORMER_PARTNER = 'former_partner';
    public const LINK_ACCOUNT = 'link_account';

    private const LABELS = [
        self::HALF_SIBLING => 'half-sibling',
        self::STEP_SIBLING => 'step-sibling',
        self::FORMER_PARTNER => 'former partner',
        self::LINK_ACCOUNT => 'member of your family (is this you?)',
    ];

    /** Maps a sibling parentage to a request kind (null = no confirmation needed). */
    public static function kindForParentage(string $parentage): ?string
    {
        return match ($parentage) {
            FamilyGraphService::SAME_MOTHER, FamilyGraphService::SAME_FATHER => self::HALF_SIBLING,
            FamilyGraphService::STEP => self::STEP_SIBLING,
            default => null,
        };
    }

    /**
     * Work out who should confirm and store one pending request per person.
     * Candidates: the relative's own account (claimed node or matching e-mail)
     * and, for half-siblings, the shared parent's account.
     *
     * @return list<array{id:int, approver_id:string}> newly created requests
     */
    public static function createFor(
        PDO $db,
        string $fam,
        string $requesterId,
        int $nodeId,
        string $kind,
        ?int $unionId = null,
        int $sharedParentNodeId = 0
    ): array {
        if (!isset(self::LABELS[$kind]) || $nodeId <= 0 || $requesterId === '') {
            return [];
        }
        $node = FamilyGraphService::getNode($db, $fam, $nodeId);
        if (!$node || (int)$node['is_placeholder'] === 1) {
            return [];
        }

        $candidates = [];
        if (!empty($node['user_id'])) {
            $candidates[] = (string)$node['user_id'];
        }
        $email = strtolower(trim((string)($node['email'] ?? '')));
        if ($email !== '') {
            $acc = $db->prepare('SELECT id FROM account WHERE LOWER(email) = ? LIMIT 1');
            $acc->execute([$email]);
            $accId = $acc->fetchColumn();
            if ($accId !== false && $accId !== null && $accId !== '') {
                $candidates[] = (string)$accId;
            }
        }
        if ($sharedParentNodeId > 0) {
            $parent = FamilyGraphService::getNode($db, $fam, $sharedParentNodeId);
            if ($parent && !empty($parent['user_id']) && (int)$parent['is_placeholder'] === 0) {
                $candidates[] = (string)$parent['user_id'];
            }
        }

        $created = [];
        $ins = $db->prepare(
            'INSERT IGNORE INTO family_relationship_requests (family_code, requester_id, approver_id, node_id, union_id, kind)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach (array_values(array_unique($candidates)) as $approverId) {
            if ($approverId === $requesterId) {
                continue;
            }
            $ins->execute([$fam, $requesterId, $approverId, $nodeId, $unionId, $kind]);
            $id = (int)$db->lastInsertId();
            if ($id > 0 && $ins->rowCount() > 0) {
                $created[] = ['id' => $id, 'approver_id' => $approverId];
            }
        }
        return $created;
    }

    /**
     * Ask a registered member to confirm that a tree card is them.
     *
     * @return list<array{id:int, approver_id:string}>
     */
    public static function createLinkRequest(PDO $db, string $fam, string $requesterId, int $nodeId, string $memberId): array
    {
        if ($memberId === '' || $memberId === $requesterId) {
            return [];
        }
        $ins = $db->prepare(
            "INSERT IGNORE INTO family_relationship_requests (family_code, requester_id, approver_id, node_id, kind)
             VALUES (?, ?, ?, ?, 'link_account')"
        );
        $ins->execute([$fam, $requesterId, $memberId, $nodeId]);
        $id = (int)$db->lastInsertId();
        return ($id > 0 && $ins->rowCount() > 0) ? [['id' => $id, 'approver_id' => $memberId]] : [];
    }

    /**
     * Notify approvers (in-app + push cascade). Call AFTER the transaction commits.
     *
     * @param list<array{id:int, approver_id:string}> $requests
     */
    public static function notify(PDO $db, string $fam, string $requesterId, string $requesterName, int $nodeId, string $kind, array $requests): void
    {
        $node = FamilyGraphService::getNode($db, $fam, $nodeId);
        $relName = trim(((string)($node['first_name'] ?? '')) . ' ' . ((string)($node['last_name'] ?? '')));
        $label = self::LABELS[$kind] ?? 'relative';
        foreach ($requests as $r) {
            try {
                NotificationOrchestrator::dispatch(
                    userId: $r['approver_id'],
                    category: 'family',
                    priority: 'high',
                    title: '🌳 Please confirm a family connection',
                    body: "{$requesterName} added {$relName} as a {$label}. Is this correct?",
                    actionUrl: '/familyStudio#relationshipRequests',
                    tag: 'relreq-' . $r['id'],
                    familyCode: $fam,
                    metadata: ['request_id' => $r['id'], 'sender_id' => $requesterId, 'kind' => $kind]
                );
            } catch (\Throwable $e) {
                error_log('[FamilyRelationshipRequestService] notify failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Pending requests waiting for this member.
     *
     * @return list<array<string, mixed>>
     */
    public static function pendingFor(PDO $db, string $approverId): array
    {
        $stmt = $db->prepare(
            "SELECT r.id, r.kind, r.created_at, r.requester_id,
                    TRIM(CONCAT(COALESCE(n.first_name,''), ' ', COALESCE(n.last_name,''))) AS relative_name,
                    TRIM(CONCAT(COALESCE(p.firstName,''), ' ', COALESCE(p.lastName,''))) AS requester_name
               FROM family_relationship_requests r
               JOIN family_nodes n ON n.id = r.node_id
               LEFT JOIN personal p ON p.id = r.requester_id
              WHERE r.approver_id = ? AND r.status = 'pending'
              ORDER BY r.id DESC LIMIT 50"
        );
        $stmt->execute([$approverId]);
        return array_values($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Confirm or decline. Only the addressed approver may resolve a pending request.
     *
     * @return array{ok:bool, status:string, message:string}
     */
    public static function resolve(PDO $db, int $requestId, string $approverId, bool $approve): array
    {
        $stmt = $db->prepare("SELECT * FROM family_relationship_requests WHERE id = ? AND approver_id = ? AND status = 'pending' LIMIT 1");
        $stmt->execute([$requestId, $approverId]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$req) {
            return ['ok' => false, 'status' => 'not_found', 'message' => 'Request not found or already answered.'];
        }

        if ($approve && (string)$req['kind'] === self::LINK_ACCOUNT) {
            $m = $db->prepare('SELECT firstName, lastName FROM personal WHERE id = ? LIMIT 1');
            $m->execute([$approverId]);
            $mr = $m->fetch(PDO::FETCH_ASSOC) ?: [];
            $claim = KinshipEntityResolver::claimAndMergeNode((string)$req['family_code'], $approverId, (int)$req['node_id'], [
                'firstName' => (string)($mr['firstName'] ?? ''),
                'lastName' => (string)($mr['lastName'] ?? ''),
            ]);
            if (!$claim['success'] && (int)$claim['code'] !== 409) {
                return ['ok' => false, 'status' => 'claim_failed', 'message' => (string)$claim['message']];
            }
        }

        $db->beginTransaction();
        try {
            $db->prepare('UPDATE family_relationship_requests SET status = ?, resolved_at = NOW() WHERE id = ?')
                ->execute([$approve ? 'approved' : 'declined', $requestId]);

            if (!$approve) {
                self::unlink($db, (string)$req['family_code'], (int)$req['node_id'], $req['union_id'] !== null ? (int)$req['union_id'] : null, (string)$req['kind']);
                // A decline answers every other pending request for the same relative+kind
                $db->prepare("UPDATE family_relationship_requests SET status = 'declined', resolved_at = NOW()
                               WHERE node_id = ? AND kind = ? AND status = 'pending'")
                    ->execute([(int)$req['node_id'], (string)$req['kind']]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        try {
            NotificationOrchestrator::dispatch(
                userId: (string)$req['requester_id'],
                category: 'family',
                priority: 'medium',
                title: $approve ? '✅ Connection confirmed' : 'Connection declined',
                body: $approve ? 'A family member confirmed the relationship you added.' : 'A family member declined a connection you added.',
                actionUrl: '/familyStudio',
                tag: 'relreq-done-' . $requestId,
                familyCode: (string)$req['family_code'],
                metadata: ['request_id' => $requestId]
            );
        } catch (\Throwable $e) {
            error_log('[FamilyRelationshipRequestService] requester notify failed: ' . $e->getMessage());
        }

        return ['ok' => true, 'status' => $approve ? 'approved' : 'declined', 'message' => $approve ? 'Confirmed. Thank you!' : 'Declined. The relationship was removed.'];
    }

    /** Remove the declined relationship again (only the single link, never other family data). */
    private static function unlink(PDO $db, string $fam, int $nodeId, ?int $unionId, string $kind): void
    {
        if ($kind === self::LINK_ACCOUNT) {
            return; // "No, that's not me" only declines the link, the relative stays on the tree
        }
        if ($kind === self::FORMER_PARTNER) {
            if ($unionId === null) {
                return;
            }
            $kids = $db->prepare('SELECT COUNT(*) FROM family_node_children WHERE union_id = ?');
            $kids->execute([$unionId]);
            // Never orphan children: a partnership with children stays, the stamp is simply lifted.
            if ((int)$kids->fetchColumn() === 0) {
                $db->prepare('DELETE FROM family_unions WHERE id = ? AND family_code = ?')->execute([$unionId, $fam]);
                $db->prepare('DELETE FROM family_nodes WHERE id = ? AND family_code = ? AND user_id IS NULL')->execute([$nodeId, $fam]);
            }
            return;
        }

        // Siblings: drop the single child link, and the node itself if it is an unclaimed shell with no other links.
        if ($unionId !== null) {
            $db->prepare('DELETE FROM family_node_children WHERE union_id = ? AND child_id = ?')->execute([$unionId, $nodeId]);
        }
        $other = $db->prepare(
            'SELECT (SELECT COUNT(*) FROM family_node_children WHERE child_id = ?)
                  + (SELECT COUNT(*) FROM family_unions WHERE partner_1_id = ? OR partner_2_id = ?)'
        );
        $other->execute([$nodeId, $nodeId, $nodeId]);
        if ((int)$other->fetchColumn() === 0) {
            $db->prepare('DELETE FROM family_nodes WHERE id = ? AND family_code = ? AND user_id IS NULL')->execute([$nodeId, $fam]);
        }
    }
}
