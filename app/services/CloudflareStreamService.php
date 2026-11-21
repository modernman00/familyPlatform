<?php
declare(strict_types=1);

namespace App\services;

class CloudflareStreamService
{
    public const MAX_PORTRAIT_DURATION = 90;
    public const MAX_LANDSCAPE_DURATION = 300;

    private string $accountId;
    private string $apiToken;
    private string $streamSubdomain;

    public function __construct()
    {
        $this->accountId = (string) ($_ENV['CLOUDFLARE_STREAM_ACCOUNT_ID'] ?? $_SERVER['CLOUDFLARE_STREAM_ACCOUNT_ID'] ?? getenv('CLOUDFLARE_STREAM_ACCOUNT_ID') ?: '');
        $this->apiToken = (string) ($_ENV['CLOUDFLARE_STREAM_API_TOKEN'] ?? $_SERVER['CLOUDFLARE_STREAM_API_TOKEN'] ?? getenv('CLOUDFLARE_STREAM_API_TOKEN') ?: '');
        $rawSubdomain = (string) ($_ENV['CLOUDFLARE_STREAM_SUBDOMAIN'] ?? $_SERVER['CLOUDFLARE_STREAM_SUBDOMAIN'] ?? getenv('CLOUDFLARE_STREAM_SUBDOMAIN') ?: '');
        $this->streamSubdomain = preg_replace('/[^a-zA-Z0-9.\-]/', '', $rawSubdomain) ?: '';
    }

    /**
     * Resolve orientation-aware maximum video duration in seconds.
     * Portrait / Reels: 90s | Landscape Cinema: 300s (5 mins)
     */
    public static function resolveMaxDurationSeconds(?string $orientation = null): int
    {
        $norm = strtolower(trim((string)$orientation));
        if ($norm === 'landscape' || $norm === 'cinema') {
            return self::MAX_LANDSCAPE_DURATION;
        }
        return self::MAX_PORTRAIT_DURATION;
    }

    /**
     * Build HLS ABR manifest URL (.m3u8) for a Cloudflare Stream UID.
     */
    public function buildHlsUrl(string $uid): string
    {
        $cleanUid = preg_replace('/[^a-zA-Z0-9_-]/', '', $uid) ?: '';
        if ($cleanUid === '') {
            return '';
        }
        $host = $this->streamSubdomain !== '' ? $this->streamSubdomain : 'videodelivery.net';
        return "https://{$host}/{$cleanUid}/manifest/video.m3u8";
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
     * @param int $maxDurationSeconds Maximum allowed video length (90s portrait / 300s landscape)
     * @param int $expirySeconds Expiration in seconds from now (0 = permanent / never expire)
     * @param string|null $orientation Optional orientation ('portrait' | 'landscape' | 'square')
     * @return array{success: bool, uploadUrl?: string, videoId?: string, streamUrl?: string, hlsUrl?: string, maxDuration?: int, error?: string}
     */
    public function createDirectUploadUrl(int $maxDurationSeconds = 90, int $expirySeconds = 0, ?string $orientation = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Cloudflare Stream is not yet configured. Please add CLOUDFLARE_STREAM_ACCOUNT_ID and CLOUDFLARE_STREAM_API_TOKEN to your .env file.'
            ];
        }

        $tierCap = self::resolveMaxDurationSeconds($orientation);
        $maxDurationSeconds = max(1, min($maxDurationSeconds, $tierCap));
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
                'streamUrl' => "https://iframe.videodelivery.net/{$uid}",
                'hlsUrl' => $this->buildHlsUrl($uid),
                'maxDuration' => $maxDurationSeconds,
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
     * Request a TUS 1.0.0 Resumable Direct Creator Upload URL from Cloudflare Stream.
     *
     * @param int $uploadLength Total file size in bytes
     * @param int $maxDurationSeconds Maximum allowed video length
     * @param int $expirySeconds Expiration in seconds from now (0 = permanent)
     * @param string|null $orientation Optional orientation ('portrait' | 'landscape' | 'square')
     * @return array{success: bool, tusSupported?: bool, uploadUrl?: string, videoId?: string, streamUrl?: string, hlsUrl?: string, maxDuration?: int, error?: string}
     */
    public function createTusUploadUrl(int $uploadLength, int $maxDurationSeconds = 90, int $expirySeconds = 0, ?string $orientation = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Cloudflare Stream is not configured for TUS resumable uploads.'
            ];
        }

        if ($uploadLength <= 0) {
            return ['success' => false, 'error' => 'Invalid upload length for TUS session.'];
        }

        $tierCap = self::resolveMaxDurationSeconds($orientation);
        $maxDurationSeconds = max(1, min($maxDurationSeconds, $tierCap));
        $endpoint = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream?direct_user=true";

        $metaParts = [
            'maxDurationSeconds ' . base64_encode((string)$maxDurationSeconds),
        ];
        if ($expirySeconds > 0) {
            $metaParts[] = 'expiry ' . base64_encode(date('c', time() + $expirySeconds));
        }

        $ch = curl_init($endpoint);
        if ($ch === false) {
            return ['success' => false, 'error' => 'Unable to initialise cURL for TUS upload'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 8, // David's Gate 3: 8s strict timeout
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiToken,
                'Tus-Resumable: 1.0.0',
                'Upload-Length: ' . $uploadLength,
                'Upload-Metadata: ' . implode(',', $metaParts),
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlError = curl_error($ch);
        if (\PHP_VERSION_ID < 80500) {
            @curl_close($ch);
        }

        if ($response === false || !empty($curlError)) {
            error_log("[CloudflareStream TUS] cURL Error: {$curlError}");
            return ['success' => false, 'error' => 'Network error creating TUS upload session.'];
        }

        $rawHeaders = substr((string)$response, 0, $headerSize);
        $location = '';
        $uid = '';
        foreach (preg_split("/\r\n|\n|\r/", $rawHeaders) ?: [] as $line) {
            if (stripos($line, 'location:') === 0) {
                $location = trim(substr($line, 9));
            } elseif (stripos($line, 'stream-media-id:') === 0) {
                $uid = trim(substr($line, 16));
            }
        }

        if ($httpCode >= 200 && $httpCode < 300 && $location !== '') {
            return [
                'success' => true,
                'tusSupported' => true,
                'uploadUrl' => $location,
                'videoId' => $uid,
                'streamUrl' => $uid !== '' ? "https://iframe.videodelivery.net/{$uid}" : '',
                'hlsUrl' => $uid !== '' ? $this->buildHlsUrl($uid) : '',
                'maxDuration' => $maxDurationSeconds,
            ];
        }

        return ['success' => false, 'error' => "Failed to create TUS upload session (HTTP {$httpCode})."];
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
