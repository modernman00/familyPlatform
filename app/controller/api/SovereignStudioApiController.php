<?php

declare(strict_types=1);

namespace App\controller\api;

use App\controller\BaseController;
use Src\AuditLogger;
use Src\Exceptions\ValidationException;
use Src\Limiter;
use Src\Media\SovereignVideoJobHandler;
use Throwable;

/**
 * SovereignStudioApiController
 *
 * Headless Licensing REST API v1 for external licensees & partners:
 * - POST /api/v1/sovereign-studio/job
 * - GET  /api/v1/sovereign-studio/sdk-manifest
 * - GET  /api/v1/sovereign-studio/openapi.json
 */
final class SovereignStudioApiController extends BaseController
{
    public const MAX_REQUEST_BYTES = 262144; // 256 KB JSON payload ceiling

    public static ?string $testRawBody = null;

    public function __construct()
    {
        // Headless API controller: authenticates via SovereignVideoJobHandler::verifyLicenseToken()
    }

    public function executeJob(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $clientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        try {
            Limiter::limit($clientIp, 'sovereign_studio_api');
            $secret = $this->resolveSigningSecret();
            $raw = self::$testRawBody ?? (file_get_contents('php://input', false, null, 0, self::MAX_REQUEST_BYTES + 1) ?: '{}');
            if (strlen($raw) > self::MAX_REQUEST_BYTES) {
                http_response_code(413);
                echo json_encode(['status' => 413, 'error' => 'Payload exceeds 256 KB ceiling.']);
                return;
            }
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                http_response_code(400);
                echo json_encode(['status' => 400, 'error' => 'Invalid JSON request payload.']);
                return;
            }

            if (empty($decoded['license_token'])) {
                $bearer = $this->extractBearerToken();
                if ($bearer !== '') {
                    $decoded['license_token'] = $bearer;
                }
            }
            if (empty($decoded['origin_domain']) && !empty($_SERVER['HTTP_ORIGIN'])) {
                $host = parse_url((string) $_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
                if (is_string($host) && $host !== '') {
                    $decoded['origin_domain'] = $host;
                }
            }

            $res = SovereignVideoJobHandler::handleApiJobRequest($decoded, $secret);
            AuditLogger::log('sovereign_studio_api_job', [
                'licensee' => $res['licensee'],
                'tier'     => $res['tier'],
                'action'   => $res['action'],
                'job_id'   => $res['jobId'],
                'ip'       => $clientIp,
            ]);
            http_response_code(200);
            echo json_encode($res, JSON_UNESCAPED_SLASHES);
        } catch (ValidationException $e) {
            $msg = $e->getMessage();
            $code = $this->resolveValidationStatusCode($msg);
            AuditLogger::log('sovereign_studio_api_denied', ['status' => $code, 'reason' => $msg, 'ip' => $clientIp]);
            http_response_code($code);
            echo json_encode(['status' => $code, 'error' => $msg]);
        } catch (Throwable $e) {
            error_log('[SovereignStudioApiController::executeJob] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 500, 'error' => 'Unable to process Sovereign Studio API job.']);
        }
    }

    public function sdkManifest(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $clientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        try {
            Limiter::limit($clientIp, 'sovereign_studio_api');
            $secret = $this->resolveSigningSecret();
            $token = $this->extractBearerToken();
            $origin = !empty($_SERVER['HTTP_ORIGIN']) ? (string) parse_url((string) $_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) : null;
            $license = SovereignVideoJobHandler::verifyLicenseToken($token, $secret, $origin ?: null);
            if (!($license['capabilities']['allowWhitelabelDownload'] ?? false)) {
                $msg = 'ENTITLEMENT VIOLATION - White-label SDK download requires Growth or Enterprise tier.';
                AuditLogger::log('sovereign_studio_api_denied', ['status' => 403, 'reason' => $msg, 'ip' => $clientIp]);
                http_response_code(403);
                echo json_encode(['status' => 403, 'error' => $msg]);
                return;
            }
            $manifest = SovereignVideoJobHandler::buildDownloadableSdkManifest($license['org'], $license['tier']);
            AuditLogger::log('sovereign_studio_sdk_manifest', ['licensee' => $license['org'], 'tier' => $license['tier'], 'ip' => $clientIp]);
            http_response_code(200);
            echo json_encode(['status' => 200, 'manifest' => $manifest], JSON_UNESCAPED_SLASHES);
        } catch (ValidationException $e) {
            $msg = $e->getMessage();
            $code = $this->resolveValidationStatusCode($msg);
            AuditLogger::log('sovereign_studio_api_denied', ['status' => $code, 'reason' => $msg, 'ip' => $clientIp]);
            http_response_code($code);
            echo json_encode(['status' => $code, 'error' => $msg]);
        } catch (Throwable $e) {
            error_log('[SovereignStudioApiController::sdkManifest] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 500, 'error' => 'Unable to build SDK manifest.']);
        }
    }

    public function openApi(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $baseUrl = rtrim((string) ($_ENV['APP_URL'] ?? 'https://myfamilyplatform.com'), '/');
        http_response_code(200);
        echo json_encode(SovereignVideoJobHandler::getOpenApiSpecification($baseUrl), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function extractBearerToken(): string
    {
        $header = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
        if (preg_match('/^\s*Bearer\s+(.+)\s*$/i', $header, $m) === 1) {
            return trim($m[1]);
        }
        return '';
    }

    private function resolveValidationStatusCode(string $msg): int
    {
        if (str_contains($msg, 'ENTITLEMENT')) {
            return 403;
        }
        if (
            str_contains($msg, 'SECURITY')
            || str_contains($msg, 'token')
            || str_contains($msg, 'Origin')
            || str_contains($msg, 'signature')
        ) {
            return 401;
        }
        return 422;
    }

    /**
     * @throws ValidationException
     */
    private function resolveSigningSecret(): string
    {
        $secret = trim((string) ($_ENV['SOVEREIGN_STUDIO_LICENSE_SECRET'] ?? ''));
        if ($secret === '' && strtolower((string) ($_ENV['APP_ENV'] ?? 'local')) !== 'production') {
            $secret = trim((string) ($_ENV['APP_KEY'] ?? ''));
        }
        if ($secret === '') {
            throw new ValidationException('SECURITY VIOLATION - SOVEREIGN_STUDIO_LICENSE_SECRET is not configured.');
        }
        return $secret;
    }
}
