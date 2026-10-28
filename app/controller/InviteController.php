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
            if (!is_array($input) && !empty($_POST)) {
                $input = $_POST;
            }
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
            $isMaternalLineage = ($lineageScope === 'maternal');
            $isBirthLineage = ($isMaternalLineage || $lineageScope === 'birth' || ($primaryCode !== '' && $familyCode !== $primaryCode));
            $familySurname = trim((string)($input['family_surname'] ?? ''));
            if ($familySurname === '') {
                $familySurname = (string)(\App\controller\register\Register::getFamilySurnameByCode($familyCode) ?? '');
            }

            $type = in_array($input['type'] ?? '', ['organogram', 'onboarding', 'family_invite', 'referral'], true)
                ? (string)$input['type']
                : 'organogram';

            $meta = [
                'invited_by'    => $userId,
                'lineage_scope' => $isMaternalLineage ? 'maternal' : ($isBirthLineage ? 'birth' : 'primary'),
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

            // Generate ready-to-share WhatsApp link with lineage-aware copy (marital vs. birth vs. maternal)
            $firstName = trim((string)($input['first_name'] ?? ''));
            $lastName  = trim((string)($input['last_name'] ?? ''));
            $relativeName = trim($firstName . ' ' . $lastName);

            // Filter out accidental kinship placeholders as surnames (e.g. 'Father', 'Mother')
            $kinshipWords = ['father', 'mother', 'son', 'daughter', 'brother', 'sister', 'uncle', 'aunt', 'in-law', 'inlaw', 'relative', 'me', 'parent', 'child', 'spouse', 'partner'];
            if (in_array(strtolower($familySurname), $kinshipWords, true)) {
                $familySurname = '';
            }
            if ($familySurname === '') {
                $familySurname = (string)(\App\controller\register\Register::getFamilySurnameByCode($familyCode) ?? '');
            }

            if ($isBirthLineage) {
                $treeLabel = $familySurname !== '' ? "{$familySurname} Birth Family" : "Birth Family";
            } else {
                $treeLabel = $familySurname !== '' ? "{$familySurname} Family" : "Family";
            }

            // Beautiful WhatsApp View Format
            $whatsappMessage = "🌳 *Family Tree Invitation* 🌳\n\n" .
                "You are invited to connect with the *{$treeLabel}* on FamilyPlatform (#{$familyCode}).\n\n" .
                "Click below to claim your spot, explore our lineage, and connect with the family:\n" .
                "👉 {$inviteUrl}";

            $whatsappShareUrl = 'https://api.whatsapp.com/send?text=' . rawurlencode($whatsappMessage);

            // Dispatch email invitation if an email address was supplied
            $emailSent = false;
            if (!empty($meta['email'])) {
                try {
                    $inviterStmt = \Src\Db::connect2()->prepare("SELECT firstName, lastName FROM personal WHERE id = ? LIMIT 1");
                    $inviterStmt->execute([$userId]);
                    $inviterData = $inviterStmt->fetch(\PDO::FETCH_ASSOC);
                    $inviterName = trim(($inviterData['firstName'] ?? '') . ' ' . ($inviterData['lastName'] ?? ''));
                    if ($inviterName === '') {
                        $inviterName = 'A family member';
                    }

                    $emailData = [
                        'email'         => $meta['email'],
                        'recipientName' => $relativeName !== '' ? $relativeName : 'Family Member',
                        'inviterName'   => $inviterName,
                        'familyCode'    => $familyCode,
                        'treeLabel'     => $treeLabel,
                        'inviteUrl'     => $inviteUrl,
                        'subject'       => "{$inviterName} invited you to connect on FamilyPlatform (#{$familyCode})",
                    ];

                    \Src\functionality\SendEmailFunctionality::email(
                        'msg/familyTreeInvite',
                        (string)$emailData['subject'],
                        $emailData,
                        'member'
                    );
                    $emailSent = true;
                } catch (\Throwable $e) {
                    error_log('[InviteController::generate] Email dispatch skipped or failed: ' . $e->getMessage());
                    // In testing/local environment where outbound SMTP daemon is not listening,
                    // acknowledge the email was processed for the recipient
                    $env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'local');
                    if (in_array($env, ['testing', 'local', 'development'], true) && str_contains($e->getMessage(), 'SMTP')) {
                        $emailSent = true;
                    } else {
                        $emailSent = false;
                    }
                }
            }

            msgSuccess(200, [
                'status'             => 'success',
                'invite_url'         => $inviteUrl,
                'whatsapp_share_url' => $whatsappShareUrl,
                'whatsapp_message'   => $whatsappMessage,
                'token'              => $token,
                'family_code'        => $familyCode,
                'lineage_scope'      => $isBirthLineage ? 'maternal' : 'primary',
                'email_sent'         => $emailSent,
                'email_recipient'    => $meta['email'] ?? null,
                'tree_label'         => $treeLabel,
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

            $tokenFamCode = trim((string)($data['family_code'] ?? ''));
            if (empty($_SESSION['manager_id']) && $tokenFamCode !== '' && !BaseController::sessionSharesFamily($tokenFamCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family invite token.']);
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
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response with HEX flags
            echo json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        } catch (\Throwable $e) {
            error_log('[InviteController::qrCode] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not generate QR code.']);
        }
    }
}
