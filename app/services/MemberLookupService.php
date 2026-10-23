<?php
declare(strict_types=1);

namespace App\services;

use PDO;

/**
 * "Is this relative already a registered member?" lookup used while adding a
 * relative. Deliberately leaks as little as possible: no id, no surname, no
 * family code. The caller gets a masked first name and a short-lived signed
 * link token that only the same viewer can redeem.
 */
final class MemberLookupService
{
    private const TOKEN_TTL = 900; // 15 minutes

    private static function secret(): string
    {
        return (string)($_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: 'family-member-link-secret-key-2026');
    }

    public static function mask(string $first): string
    {
        $first = trim($first);
        if ($first === '') {
            return 'A member';
        }
        return mb_substr($first, 0, 2) . str_repeat('*', max(2, min(5, mb_strlen($first) - 2)));
    }

    public static function makeToken(string $viewerId, string $memberId, ?int $now = null): string
    {
        $exp = ($now ?? time()) + self::TOKEN_TTL;
        $payload = $viewerId . '|' . $memberId . '|' . $exp;
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=') . '.' . hash_hmac('sha256', $payload, self::secret());
    }

    /** @return string|null the member id when the token is valid, unexpired and was issued to this viewer */
    public static function verifyToken(string $viewerId, string $token, ?int $now = null): ?string
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }
        $payload = base64_decode(strtr($parts[0], '-_', '+/'), true);
        if ($payload === false || !hash_equals(hash_hmac('sha256', $payload, self::secret()), $parts[1])) {
            return null;
        }
        $bits = explode('|', $payload);
        if (count($bits) !== 3 || $bits[0] !== $viewerId || (int)$bits[2] < ($now ?? time())) {
            return null;
        }
        return $bits[1] !== '' ? $bits[1] : null;
    }

    /**
     * @return array{found: bool, masked?: string, same_family?: bool, already_on_tree?: bool, link_token?: string}
     */
    public static function lookup(PDO $db, string $viewerId, string $viewerFam, string $email, string $mobile): array
    {
        $email = strtolower(trim($email));
        $digits = substr((string)preg_replace('/\D+/', '', $mobile), -10);
        if ($viewerId === '' || (strlen($email) < 6 && strlen($digits) < 8)) {
            return ['found' => false];
        }

        $row = null;
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $db->prepare(
                'SELECT a.id, p.firstName, p.famCode FROM account a JOIN personal p ON p.id = a.id
                  WHERE LOWER(a.email) = ? AND a.id <> ? AND a.deleted_at IS NULL LIMIT 1'
            );
            $stmt->execute([$email, $viewerId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if ($row === null && strlen($digits) >= 8) {
            $stmt = $db->prepare(
                "SELECT p.id, p.firstName, p.famCode FROM contact c JOIN personal p ON p.id = c.id
                  WHERE RIGHT(REGEXP_REPLACE(c.mobile, '[^0-9]', ''), ?) = ? AND p.id <> ? AND c.deleted_at IS NULL LIMIT 1"
            );
            $stmt->execute([strlen($digits), $digits, $viewerId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if ($row === null) {
            return ['found' => false];
        }

        $memberId = (string)$row['id'];
        $same = strcasecmp((string)$row['famCode'], $viewerFam) === 0;
        $onTree = false;
        if ($same) {
            $chk = $db->prepare('SELECT 1 FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1');
            $chk->execute([$viewerFam, $memberId]);
            $onTree = (bool)$chk->fetchColumn();
        }

        return [
            'found' => true,
            'masked' => self::mask((string)$row['firstName']),
            'same_family' => $same,
            'already_on_tree' => $onTree,
            'link_token' => self::makeToken($viewerId, $memberId),
        ];
    }

    /**
     * Link a just-added relative to the registered member found by lookup().
     * Same family -> confirmation request to that member. Other family -> a
     * migration nudge (the existing, consent-based flow).
     *
     * @return array{ok: bool, message: string}
     */
    public static function link(PDO $db, string $fam, string $viewerId, string $viewerName, int $nodeId, string $token): array
    {
        $memberId = self::verifyToken($viewerId, $token);
        if ($memberId === null) {
            return ['ok' => false, 'message' => 'This link expired. Please type the email again.'];
        }
        $node = FamilyGraphService::getNode($db, $fam, $nodeId);
        if (!$node || !empty($node['user_id']) || (int)$node['is_placeholder'] === 1) {
            return ['ok' => false, 'message' => 'This relative can no longer be linked.'];
        }

        $mem = $db->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
        $mem->execute([$memberId]);
        $memFam = (string)$mem->fetchColumn();

        if (strcasecmp($memFam, $fam) === 0) {
            $made = FamilyRelationshipRequestService::createLinkRequest($db, $fam, $viewerId, $nodeId, $memberId);
            if ($made === []) {
                return ['ok' => true, 'message' => 'A confirmation request is already waiting for them.'];
            }
            FamilyRelationshipRequestService::notify($db, $fam, $viewerId, $viewerName, $nodeId, FamilyRelationshipRequestService::LINK_ACCOUNT, $made);
            return ['ok' => true, 'message' => 'Sent. They will be asked to confirm it is them.'];
        }

        $res = (new FamilyMigrationService($db))->createNudge($viewerId, $memberId, 'Linking you to the family tree');
        return ['ok' => (bool)$res['success'], 'message' => $res['success'] ? 'Invitation sent to join your family network.' : (string)$res['message']];
    }
}
