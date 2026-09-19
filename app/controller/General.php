<?php
declare(strict_types=1);

namespace App\controller;

use App\services\InviteTokenService;
use Exception;
use Src\CheckToken;
use Src\Limiter;
use Src\ToSendEmail;

final class General
{
    public function sendTextToMember(): void
    {
        $message = "it is well";
        $number = $_POST['number'] ?? '';
        if (function_exists('sendText') && !empty($number)) {
            sendText(message: $message, numbers: $number);
        }
    }

    public function sendEmailToMember(): void
    {
        try {
            // Auth gate: sender must be an authenticated user
            if (empty($_SESSION['manager_id']) && empty($_SESSION['id'])) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
                return;
            }

            // Anti-CSRF Gate
            CheckToken::tokenCheck();

            // Per-user rate limiting
            $userId = (string)($_SESSION['manager_id'] ?? $_SESSION['id'] ?? '');
            try {
                Limiter::limit('send_invite_' . $userId);
            } catch (\Src\Exceptions\TooManyRequestsException $e) {
                http_response_code(429);
                echo json_encode(['status' => 'error', 'message' => 'Too many invite messages sent. Please slow down.']);
                return;
            }

            $rawInput = file_get_contents("php://input");
            $data = $rawInput !== false ? json_decode($rawInput, true) : null;

            if (!is_array($data) || !isset($data['viewPath']) || !isset($data['data'])) {
                throw new Exception("Invalid payload");
            }

            // 1. Whitelist viewPath
            $allowedViewPaths = [
                'msg/contactNewMember'
            ];
            $viewPath = $data['viewPath'];
            if (!in_array($viewPath, $allowedViewPaths, true)) {
                throw new Exception("Unauthorized view path");
            }

            // 2. Validate payload data
            $payloadData = $data['data'];
            $recipientName = trim((string)($payloadData['name'] ?? ''));
            $yourName = trim((string)($payloadData['yourName'] ?? 'A family member'));
            $familyCode = trim((string)($data['familyCode'] ?? ($payloadData['familyCode'] ?? '')));

            if (empty($payloadData['email']) || !filter_var($payloadData['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid recipient email");
            }

            // 3. Generate opaque invite token if family code is available
            if ($familyCode !== '') {
                try {
                    $meta = [
                        'invited_by' => $userId,
                        'first_name' => $recipientName,
                        'email'      => (string)$payloadData['email'],
                    ];
                    $token = InviteTokenService::create($familyCode, $meta, 'family_invite');
                    $payloadData['invite_url'] = InviteTokenService::getRegisterUrl($token);
                } catch (\Throwable $tokenEx) {
                    error_log('[General] Invite token generation warning: ' . $tokenEx->getMessage());
                }
            }

            // 4. Generate subject on the server side
            $sanitizedYourName = htmlspecialchars($yourName);
            $subject = "{$sanitizedYourName} Wants You: Experience the Magic of your Family Network Today!";

            $array = [
                'viewPath' => $viewPath,
                'data' => $payloadData,
                'subject' => $subject,
                'familyCode' => $familyCode
            ];
            
            ToSendEmail::sendEmailGeneral($array, 'member');

            $sanitizedRecipientName = htmlspecialchars($recipientName);
            echo json_encode(['status' => 'success', 'message' => "Message sent to " . $sanitizedRecipientName]);

        } catch (\Throwable $err) {
            error_log((string)$err);
            echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
        }
    }

    public function sendTextMember(): void
    {
        try {
            // Auth gate
            if (empty($_SESSION['manager_id']) && empty($_SESSION['id'])) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
                return;
            }

            // Anti-CSRF Gate
            CheckToken::tokenCheck();

            $userId = (string)($_SESSION['manager_id'] ?? $_SESSION['id'] ?? '');
            try {
                Limiter::limit('send_sms_' . $userId);
            } catch (\Src\Exceptions\TooManyRequestsException $e) {
                http_response_code(429);
                echo json_encode(['status' => 'error', 'message' => 'Too many text messages sent. Please slow down.']);
                return;
            }

            $rawInput = file_get_contents("php://input");
            $data = $rawInput !== false ? json_decode($rawInput, true) : null;

            if (!is_array($data) || empty($data['mobile'])) {
                throw new Exception("Invalid payload");
            }

            if (function_exists('sendText')) {
                $msg = isset($data['data']['mobile']) ? (string)$data['data']['mobile'] : '';
                sendText(message: $msg, numbers: (string)$data['mobile']);
            }

            $recipientName = htmlspecialchars((string)($data['data']['name'] ?? ''));
            echo json_encode(['status' => 'success', 'message' => "Message sent to " . $recipientName]);
        } catch (\Throwable $err) {
            error_log((string)$err);
            echo json_encode(['status' => 'error', 'message' => 'Failed to send text message']);
        }
    }
}
