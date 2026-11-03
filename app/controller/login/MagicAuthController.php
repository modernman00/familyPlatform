<?php

declare(strict_types=1);

namespace App\controller\login;

use App\services\MagicAuthService;
use Src\Limiter;
use Src\Utility;
use Throwable;

/**
 * Controller for handling 1-click magic authentication requests.
 *
 * Hardened against:
 * - Automated email security link scanners (Defender SafeLinks, Proofpoint, Mimecast)
 * - Social/messaging link preview bots (WhatsApp, iMessage, Slack, Telegram)
 * - Autonomous AI scraping agents & headless browser prefetchers
 * - Referer header token leakage & proxy caching
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class MagicAuthController
{
    /**
     * Consume a magic login token and redirect the authenticated user to their destination.
     */
    public function consume(): void
    {
        // Prevent token leakage via Referer header or intermediate proxy caches
        if (!headers_sent()) {
            header('Referrer-Policy: no-referrer');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('X-Robots-Tag: noindex, nofollow');
        }

        // 1. Do not consume single-use tokens on HEAD or browser prefetch/prerender probes
        if (self::isScannerOrPrefetchRequest()) {
            http_response_code(200);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><title>FamilyPlatform — Secure 1-Click Sign In</title></head><body><p>Secure FamilyPlatform authentication link ready.</p></body></html>';
            if (!defined('TESTING_ENV')) {
                exit;
            }
            return;
        }

        // 2. Rate-limit magic link consumption attempts per client IP against brute-force / AI agents
        try {
            $ip = Utility::getUserIpAddr();
            Limiter::limit('magic_auth:' . $ip, 'login');
        } catch (Throwable) {
            http_response_code(429);
            $this->redirectWithError('Too many authentication attempts. Please wait a few minutes and try again.');
            return;
        }

        $rawToken = (string)($_GET['token'] ?? $_POST['token'] ?? '');
        $requestedDest = isset($_GET['dest']) && is_string($_GET['dest'])
            ? $_GET['dest']
            : (isset($_POST['dest']) && is_string($_POST['dest']) ? $_POST['dest'] : null);

        if ($rawToken === '') {
            $this->redirectWithError('Invalid or missing authentication link.');
            return;
        }

        $result = MagicAuthService::consume($rawToken, $requestedDest);

        if (!$result['success']) {
            $errorCode = $result['error'] ?? 'invalid_token';
            $message = match ($errorCode) {
                'token_expired'            => 'Your secure 1-click link has expired. Please sign in with your email or request a new link.',
                'token_already_consumed'   => 'This secure link has already been used. Please sign in below.',
                'admin_escalation_blocked' => 'Administrator accounts must sign in using full credentials and two-factor authentication.',
                default                    => 'Invalid or expired secure link. Please sign in to access your family sanctuary.',
            };

            $this->redirectWithError($message);
            return;
        }

        $dest = $result['destination'];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['flash_success'] = 'Welcome back! You have been securely signed in.';
        }

        // Execute safe redirection
        header('Location: ' . $dest);
        if (!defined('TESTING_ENV')) {
            exit;
        }
    }

    /**
     * Detects non-interactive email link scanners, messaging preview crawlers,
     * browser prefetch/prerender probes, and headless AI agents so they cannot
     * prematurely burn a user's single-use magic token.
     */
    public static function isScannerOrPrefetchRequest(): bool
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($method === 'HEAD' || $method === 'OPTIONS') {
            return true;
        }

        // Browser prefetch / prerender headers
        $purposeHeaders = [
            $_SERVER['HTTP_SEC_PURPOSE'] ?? '',
            $_SERVER['HTTP_PURPOSE'] ?? '',
            $_SERVER['HTTP_X_PURPOSE'] ?? '',
            $_SERVER['HTTP_X_MOZ'] ?? '',
        ];
        foreach ($purposeHeaders as $val) {
            if (is_string($val) && preg_match('/prefetch|preview|prerender/i', $val)) {
                return true;
            }
        }

        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua !== '' && preg_match('/(WhatsApp|Slackbot|TelegramBot|Discordbot|Twitterbot|facebookexternalhit|Facebot|LinkedInBot|Proofpoint|Mimecast|SafeLinks|Barracuda|Googlebot|bingbot|YandexBot|Baiduspider|HeadlessChrome|PhantomJS|Scrapy|python-requests|aiohttp|Go-http-client)/i', $ua)) {
            return true;
        }

        return false;
    }

    /**
     * Redirects to the login screen with an accessible alert message.
     */
    private function redirectWithError(string $message): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['flash_error'] = $message;
        }

        header('Location: /login?magic_error=1');
        if (!defined('TESTING_ENV')) {
            exit;
        }
    }
}
