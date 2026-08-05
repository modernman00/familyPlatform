<?php
namespace App\controller;
use Src\ToSendEmail;

use Exception;


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
            $recipientName = $payloadData['name'] ?? '';
            $yourName = $payloadData['yourName'] ?? 'A family member';
            $familyCode = $data['familyCode'] ?? ($payloadData['familyCode'] ?? '');

            if (empty($payloadData['email']) || !filter_var($payloadData['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid recipient email");
            }

            // 3. Generate subject on the server side
            $sanitizedYourName = htmlspecialchars(trim((string)$yourName));
            $subject = "{$sanitizedYourName} Wants You: Experience the Magic of your Family Network Today!";

            $array = [
                'viewPath' => $viewPath,
                'data' => $payloadData,
                'subject' => $subject,
                'familyCode' => $familyCode
            ];
            
            ToSendEmail::sendEmailGeneral($array, 'member');

            $sanitizedRecipientName = htmlspecialchars(trim((string)$recipientName));
            echo json_encode(['status' => 'success', 'message' => "Message sent to " . $sanitizedRecipientName]);

        } catch (\Throwable $err) {
            error_log((string)$err);
            echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
        }
    }

    public function sendTextMember(): void
    {
        try {
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
