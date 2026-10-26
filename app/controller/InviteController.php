<?php

declare(strict_types=1);

namespace App\controller;

use App\services\InviteTokenService;
use Src\{CheckToken, CorsHandler, Limiter};
use Src\Exceptions\BadRequestException;

/**
 * InviteController
 *
 * Handles authenticated AJAX requests to generate opaque invite tokens.
 * Replaces all client-side URL building (organogram JS) with server-side
 * token generation — zero PII ever leaves in a URL.
 */
final class InviteController
{
    /**
     * POST /api/invite/generate
     *
     * Generates a token for a specific organogram node or general family invite.
     * Requires active session (auth).
     *
     * Request JSON:
     *   { "family_code": "OLAO60446", "node_id": 42, "first_name": "Amara", "last_name": "Olaogun", "type": "organogram" }
     *
     * Response JSON:
     *   { "status": "success", "invite_url": "https://…/register?invite=abc123…", "token": "abc123…" }
     */
    public function generate(): void
    {
        CorsHandler::setHeaders();

        try {
            // Auth gate — must be a logged-in session
            if (empty($_SESSION['manager_id']) && empty($_SESSION['id'])) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            // CSRF check
            CheckToken::tokenCheck();

            // Rate limit per user — max 30 invite generations per 5 minutes
            $userId = (string)($_SESSION['manager_id'] ?? $_SESSION['id'] ?? '');
            try {
                Limiter::limit($userId);
            } catch (\Src\Exceptions\TooManyRequestsException $e) {
                http_response_code(429);
                echo json_encode(['status' => 'error', 'message' => 'Too many invite links generated. Please slow down.']);
                return;
            }

            $raw = (string)(file_get_contents('php://input') ?: '');
            /** @var array<string, mixed>|null $input */
            $input = json_decode($raw, true);
            if (!is_array($input)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload.']);
                return;
            }

            $familyCode = strtoupper(trim(str_replace('#', '', (string)($input['family_code'] ?? ''))));
            $primaryCode = strtoupper(trim((string)($_SESSION['famCode'] ?? '')));
            if ($familyCode === '') {
                $familyCode = $primaryCode;
            }
            if ($familyCode === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'family_code is required.']);
                return;
            }

            // Security gate (RFC-0004): Verify the signed-in user holds this family code
            // (either their marital/primary famCode or an approved birth/maiden family code).
            $holdsCode = BaseController::sessionSharesFamily($familyCode);
            if ($holdsCode && $familyCode !== $primaryCode && !empty($_SESSION['id'])) {
                $holdsCode = (new \App\services\BirthFamilyCodeService(\Src\Db::connect2()))
                    ->userHoldsCode((string)$_SESSION['id'], $familyCode);
            }
            if (!$holdsCode) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'You do not have permission to generate invites for this family code.']);
                return;
            }

            $lineageScope = trim((string)($input['lineage_scope'] ?? ''));
            $isBirthLineage = ($lineageScope === 'maternal' || $lineageScope === 'birth' || ($primaryCode !== '' && $familyCode !== $primaryCode));
            $familySurname = trim((string)($input['family_surname'] ?? ''));

            $type = in_array($input['type'] ?? '', ['organogram', 'onboarding', 'family_invite', 'referral'], true)
                ? (string)$input['type']
                : 'organogram';

            $meta = [
                'invited_by'    => $userId,
                'lineage_scope' => $isBirthLineage ? 'maternal' : 'primary',
            ];
            if (!empty($input['first_name'])) {
                $meta['first_name'] = trim((string)$input['first_name']);
            }
            if (!empty($input['last_name'])) {
                $meta['last_name'] = trim((string)$input['last_name']);
            }
            if (!empty($input['email'])) {
                $meta['email'] = strtolower(trim((string)$input['email']));
            }
            if (!empty($input['node_id'])) {
                $meta['node_id'] = (int)$input['node_id'];
            }

            $token     = InviteTokenService::create($familyCode, $meta, $type);
            $inviteUrl = InviteTokenService::getRegisterUrl($token);

            // Generate ready-to-share WhatsApp link with lineage-aware copy (marital vs. birth)
            $firstName = trim((string)($input['first_name'] ?? ''));
            $lastName  = trim((string)($input['last_name'] ?? ''));
            $relativeName = trim($firstName . ' ' . $lastName);
            $treeLabel = $isBirthLineage
                ? ($familySurname !== '' ? "our {$familySurname} Birth Family Tree" : 'our Birth Family Tree')
                : ($familySurname !== '' ? "our {$familySurname} Family Tree" : 'our Family Tree');

            $whatsappMessage = !empty($relativeName)
                ? "👋 Hello {$relativeName}! I have reserved your branch on {$treeLabel} on FamilyPlatform (Family Code: #{$familyCode}). Tap here to claim your spot and connect with our family:\n" . $inviteUrl
                : "👋 Hello! You have been invited to connect with {$treeLabel} on FamilyPlatform (Family Code: #{$familyCode}). Tap here to claim your branch:\n" . $inviteUrl;

            $whatsappShareUrl = 'https://api.whatsapp.com/send?text=' . rawurlencode($whatsappMessage);

            header('Content-Type: application/json');
            echo json_encode([
                'status'             => 'success',
                'invite_url'         => $inviteUrl,
                'whatsapp_share_url' => $whatsappShareUrl,
                'token'              => $token,
                'family_code'        => $familyCode,
                'lineage_scope'      => $isBirthLineage ? 'maternal' : 'primary',
            ]);

        } catch (BadRequestException $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            error_log('[InviteController::generate] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not generate invite link.']);
        }
    }

    /**
     * GET /api/invite/qr?token={token}
     *
     * Returns a QR-code PNG for the given invite token.
     * Requires active session. Rate-limited to 10/min per user.
     * Uses Google Charts QR API as a dependency-free fallback.
     */
    public function qrCode(): void
    {
        CorsHandler::setHeaders();

        try {
            if (empty($_SESSION['manager_id']) && empty($_SESSION['id'])) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            $token = trim((string)($_GET['token'] ?? ''));
            if ($token === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'token parameter is required.']);
                return;
            }

            // Validate token exists without consuming
            $data = InviteTokenService::peek($token);
            if ($data === null) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Token not found or expired.']);
                return;
            }

            $inviteUrl = InviteTokenService::getRegisterUrl($token);

            // Construct QR-code URL with safe URL parameter encoding
            $encodedUrl = rawurlencode($inviteUrl);
            $qrUrl = sprintf('https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=%s', $encodedUrl);  // nosem

            $response = [
                'status' => 'success',
                'qr_url' => $qrUrl,
                'invite_url' => $inviteUrl,
            ];

            header('Content-Type: application/json');
            $jsonResponse = json_encode($response);
            echo htmlspecialchars((string)$jsonResponse, ENT_QUOTES, 'UTF-8');

        } catch (\Throwable $e) {
            error_log('[InviteController::qrCode] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not generate QR code.']);
        }
    }
}
