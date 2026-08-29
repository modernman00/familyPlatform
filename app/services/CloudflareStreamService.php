<?php
declare(strict_types=1);

namespace App\services;

class CloudflareStreamService
{
    private string $accountId;
    private string $apiToken;

    public function __construct()
    {
        $this->accountId = (string) (getenv('CLOUDFLARE_STREAM_ACCOUNT_ID') ?: '');
        $this->apiToken = (string) (getenv('CLOUDFLARE_STREAM_API_TOKEN') ?: '');
    }

    /**
     * Check if Cloudflare Stream credentials are configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->accountId) && !empty($this->apiToken);
    }

    /**
     * Request a Direct Creator Upload URL from Cloudflare Stream
     *
     * @param int $maxDurationSeconds Maximum allowed video length (default 30s)
     * @return array{success: bool, uploadUrl?: string, videoId?: string, streamUrl?: string, error?: string}
     */
    public function createDirectUploadUrl(int $maxDurationSeconds = 30): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Cloudflare Stream is not yet configured. Please add CLOUDFLARE_STREAM_ACCOUNT_ID and CLOUDFLARE_STREAM_API_TOKEN to your .env file.'
            ];
        }

        $maxDurationSeconds = max(1, min($maxDurationSeconds, 30));
        $endpoint = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/direct_upload";

        $payload = json_encode([
            'maxDurationSeconds' => $maxDurationSeconds,
            'expiry' => date('c', time() + 3600), // 1 hour upload expiry
            'requireSignedURLs' => false,
            'allowedOrigins' => ['*'],
        ]);

        $ch = curl_init($endpoint);
        if ($ch === false) {
            return ['success' => false, 'error' => 'Unable to initialize cURL'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8, // David's Gate 3: 8s strict timeout
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiToken,
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($curlError)) {
            error_log("[CloudflareStream] cURL Error: {$curlError}");
            return ['success' => false, 'error' => 'Network error connecting to video streaming provider.'];
        }

        $data = json_decode((string) $response, true);

        if ($httpCode >= 200 && $httpCode < 300 && !empty($data['success']) && !empty($data['result']['uploadURL'])) {
            $uid = (string) ($data['result']['uid'] ?? '');
            return [
                'success' => true,
                'uploadUrl' => (string) $data['result']['uploadURL'],
                'videoId' => $uid,
                'streamUrl' => "https://iframe.videodelivery.net/{$uid}"
            ];
        }

        $errorMsg = $data['errors'][0]['message'] ?? 'Failed to generate video upload URL';
        error_log("[CloudflareStream] API Error ({$httpCode}): {$errorMsg}");
        return ['success' => false, 'error' => $errorMsg];
    }
}
