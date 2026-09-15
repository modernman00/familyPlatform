<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use Src\Utility;

/**
 * Service managing user-led network recommendations & viral family spawning.
 *
 * Facilitates inviting extended relatives, in-laws, and friends to create their
 * OWN independent, private family networks with unique family codes, reinforcing
 * the "Walled Family Sanctuary" USP (Zero tree crossover/pollution).
 */
final class FamilyRecommendationService
{
    private const TOKEN_TTL_SECONDS = 2592000; // 30 Days

    /**
     * Resolves the application secret key used for HMAC signature generation.
     */
    public static function getSecretKey(): string
    {
        $secret = getenv('JWT_SECRET') ?: getenv('APP_SECRET') ?: getenv('APP_KEY');
        if (is_string($secret) && trim($secret) !== '') {
            return trim($secret);
        }
        return 'family-platform-referral-sanctuary-secret-v1';
    }

    /**
     * Generates a cryptographically signed referral token.
     */
    public static function generateSignedReferralToken(string $userId, string $userName): string
    {
        $payload = [
            'uid'  => trim($userId),
            'name' => trim($userName),
            'iat'  => time(),
            'exp'  => time() + self::TOKEN_TTL_SECONDS,
            'rnd'  => bin2hex(random_bytes(6)),
        ];

        $json = (string) json_encode($payload, JSON_UNESCAPED_SLASHES);
        $encodedPayload = self::base64UrlEncode($json);
        $signature = hash_hmac('sha256', $encodedPayload, self::getSecretKey());

        return $encodedPayload . '.' . $signature;
    }

    /**
     * Verifies and resolves a signed referral token.
     *
     * @return array{inviter_id: string, inviter_name: string, is_valid: bool, created_at: int}|null
     */
    public static function verifyAndResolveReferralToken(string $token): ?array
    {
        $token = trim($token);
        if ($token === '' || !str_contains($token, '.')) {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $providedSignature] = $parts;

        $expectedSignature = hash_hmac('sha256', $encodedPayload, self::getSecretKey());
        if (!hash_equals($expectedSignature, $providedSignature)) {
            return null;
        }

        $decodedJson = self::base64UrlDecode($encodedPayload);
        if ($decodedJson === '') {
            return null;
        }

        $payload = json_decode($decodedJson, true);
        if (!is_array($payload) || empty($payload['uid']) || empty($payload['iat']) || empty($payload['exp'])) {
            return null;
        }

        $exp = (int) $payload['exp'];
        if (time() > $exp) {
            return null; // Expired token
        }

        return [
            'inviter_id'   => (string) $payload['uid'],
            'inviter_name' => (string) ($payload['name'] ?? 'A Family Member'),
            'is_valid'     => true,
            'created_at'   => (int) $payload['iat'],
        ];
    }

    /**
     * Formats the personalized referral share payload with the "Walled Family Sanctuary" USP message.
     *
     * @return array{share_url: string, whatsapp_url: string, message: string, token: string}
     */
    public static function getSharePayload(string $userId, string $userName, string $baseUrl = ''): array
    {
        if ($baseUrl === '') {
            $baseUrl = getenv('APP_URL') ?: 'https://myfamilyplatform.com';
        }
        $baseUrl = rtrim((string) $baseUrl, '/');

        $token = self::generateSignedReferralToken($userId, $userName);
        $shareUrl = "{$baseUrl}/join?ref={$token}";

        $firstName = explode(' ', trim($userName))[0] ?: 'A family member';

        // WhatsApp rich-preview strategy: share the URL only so WhatsApp's link
        // scraper picks up the og:image / og:title from the /join page and renders
        // the branded 1200×630 card. Encoding a full text message suppresses the preview.
        $whatsappUrl = 'https://api.whatsapp.com/send?text=' . rawurlencode($shareUrl);

        // SMS / clipboard fallback: personalised text with the link appended
        $message = "🔒 *Private Family Sanctuary*\n\n" .
            "Hey! {$firstName} uses FamilyPlatform to preserve private family trees, milestones, and memories away from public social media.\n\n" .
            "Start your OWN private family network here — 100% walled and private (only connected kins and approved family can see through the wall):\n" .
            "👉 {$shareUrl}";

        return [
            'share_url'    => $shareUrl,
            'whatsapp_url' => $whatsappUrl,
            'message'      => $message,
            'token'        => $token,
        ];
    }

    /**
     * Records a completed referral when an invited friend registers and creates their new family network.
     */
    public static function recordSuccessfulRecommendation(
        string $inviterId,
        string $newUserId,
        string $newFamilyCode,
        string $token
    ): bool {
        try {
            $db = Db::connect2();

            // Fetch inviter name if available
            $stmtInviter = $db->prepare('SELECT CONCAT(COALESCE(firstName, \'\'), \' \', COALESCE(lastName, \'\')) AS full_name FROM personal WHERE id = ? LIMIT 1');
            $stmtInviter->execute([$inviterId]);
            $inviterName = (string) ($stmtInviter->fetchColumn() ?: 'Family Member');

            $stmt = $db->prepare('
                INSERT INTO platform_recommendations 
                    (inviter_user_id, inviter_name, invitee_user_id, new_family_code, status, referral_token, created_at, completed_at)
                VALUES 
                    (?, ?, ?, ?, \'completed\', ?, NOW(), NOW())
            ');
            return $stmt->execute([
                $inviterId,
                trim($inviterName),
                $newUserId,
                $newFamilyCode,
                $token,
            ]);
        } catch (\Throwable $e) {
            error_log('[FamilyRecommendationService] Failed to record recommendation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves referral statistics for a given user.
     *
     * @return array{total_spawned: int}
     */
    public static function getRecommendationStats(string $userId): array
    {
        try {
            $db = Db::connect2();
            $stmt = $db->prepare('SELECT COUNT(*) FROM platform_recommendations WHERE inviter_user_id = ? AND status = \'completed\'');
            $stmt->execute([$userId]);
            $count = (int) ($stmt->fetchColumn() ?: 0);
            return ['total_spawned' => $count];
        } catch (\Throwable $e) {
            return ['total_spawned' => 0];
        }
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        return $decoded !== false ? $decoded : '';
    }
}
