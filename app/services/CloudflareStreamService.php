<?php
declare(strict_types=1);

namespace App\services;

class CloudflareStreamService
{
    private string $accountId;
    private string $apiToken;

    public function __construct()
    {
        $this->accountId = (string) ($_ENV['CLOUDFLARE_STREAM_ACCOUNT_ID'] ?? $_SERVER['CLOUDFLARE_STREAM_ACCOUNT_ID'] ?? getenv('CLOUDFLARE_STREAM_ACCOUNT_ID') ?: '');
        $this->apiToken = (string) ($_ENV['CLOUDFLARE_STREAM_API_TOKEN'] ?? $_SERVER['CLOUDFLARE_STREAM_API_TOKEN'] ?? getenv('CLOUDFLARE_STREAM_API_TOKEN') ?: '');
    }

    /**
     * Check if Cloudflare Stream credentials are configured and structurally valid
     */
    public function isConfigured(): bool
    {
        return !empty($this->accountId)
            && !empty($this->apiToken)
            && (bool)preg_match('/^[a-zA-Z0-9_\-]{16,64}$/', $this->accountId);
    }

    /**
     * Resolve allowed CORS origins for Cloudflare Stream direct uploads
     *
     * @return list<string>
     */
    private function getAllowedOrigins(): array
    {
        $rawAppUrl = (string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com');
        $host = parse_url($rawAppUrl, PHP_URL_HOST);
        $origins = [];
        if (is_string($host) && $host !== '') {
            $origins[] = $host;
            if (!str_starts_with($host, 'www.') && $host !== 'localhost' && $host !== '127.0.0.1') {
                $origins[] = 'www.' . $host;
            }
        }
        $env = strtolower(trim((string)($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production'), "'\""));
        if (in_array($env, ['local', 'development', 'dev', 'testing'], true)) {
            $origins[] = 'localhost';
            $origins[] = '127.0.0.1';
        }
        return !empty($origins) ? array_values(array_unique($origins)) : ['myfamilyplatform.com'];
    }

    /**
     * Request a Direct Creator Upload URL from Cloudflare Stream
     *
     * @param int $maxDurationSeconds Maximum allowed video length (default 30s)
     * @param int $expirySeconds Expiration in seconds from now (0 = permanent / never expire)
     * @return array{success: bool, uploadUrl?: string, videoId?: string, streamUrl?: string, error?: string}
     */
    public function createDirectUploadUrl(int $maxDurationSeconds = 30, int $expirySeconds = 0): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Cloudflare Stream is not yet configured. Please add CLOUDFLARE_STREAM_ACCOUNT_ID and CLOUDFLARE_STREAM_API_TOKEN to your .env file.'
            ];
        }

        $maxDurationSeconds = max(1, min($maxDurationSeconds, 30));
        $endpoint = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/direct_upload";

        $payloadData = [
            'maxDurationSeconds' => $maxDurationSeconds,
            'requireSignedURLs' => false,
            'allowedOrigins' => $this->getAllowedOrigins(),
        ];

        // If expirySeconds > 0, set expiration date. If 0 (permanent), omit expiry.
        if ($expirySeconds > 0) {
            $payloadData['expiry'] = date('c', time() + $expirySeconds);
        }

        $payload = json_encode($payloadData) ?: '{}';

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
        if (\PHP_VERSION_ID < 80500) {
            @curl_close($ch);
        }

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
        if (!empty($data['messages'][0]['message'])) {
            $errorMsg .= ' (' . $data['messages'][0]['message'] . ')';
        }
        error_log("[CloudflareStream] API Error ({$httpCode}): {$errorMsg}");

        if ($httpCode === 401 || $httpCode === 403 || str_contains(strtolower($errorMsg), 'authoriz') || str_contains(strtolower($errorMsg), 'authoris')) {
            $this->notifyAdminOfStreamFailure($errorMsg, $httpCode);
        }

        return ['success' => false, 'error' => $errorMsg];
    }

    /**
     * Notify platform administrator via shared-lib email when Cloudflare credentials fail
     */
    private function notifyAdminOfStreamFailure(string $errorMessage, int $httpCode): void
    {
        // Never send external email alerts in local test environment unless explicitly enabled
        if (function_exists('isTestEnv') && \isTestEnv()) {
            return;
        }

        // Throttled to at most once per 24 hours to prevent inbox spamming
        $lockFile = sys_get_temp_dir() . '/cf_stream_auth_alert.lock';
        if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 86400) {
            return;
        }
        @touch($lockFile);

        try {
            $adminEmail = (string)($_ENV['ADMIN_EMAIL'] ?? getenv('ADMIN_EMAIL') ?: 'general@myfamilyplatform.com');
            if (empty($adminEmail)) {
                return;
            }

            $subject = '🚨 [CRITICAL ALERT] Cloudflare Stream API Token Authorisation Failure';
            $data = [
                'email' => $adminEmail,
                'name' => 'Wally',
                'isFunctional' => true,
                'error' => $errorMessage,
                'httpCode' => $httpCode,
                'accountId' => $this->accountId,
                'time' => date('Y-m-d H:i:s T'),
            ];

            if (class_exists('\Src\ToSendEmail')) {
                \Src\ToSendEmail::sendEmailGeneral([
                    'viewPath' => 'msg.admin.cloudflareStreamAlert',
                    'data' => $data,
                    'subject' => $subject,
                ], 'admin');
            }
        } catch (\Throwable $e) {
            error_log('[CloudflareStream Alert Error] ' . $e->getMessage());
        }
    }
}
