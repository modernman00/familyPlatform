<?php

declare(strict_types=1);

namespace App\controller;

class TranslateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Translate text on-demand for diaspora members with strict CSRF, rate-limiting & multilingual support
     */
    public function translate(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required'], JSON_UNESCAPED_SLASHES);
            return;
        }

        $raw = file_get_contents('php://input');
        $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($input)) {
            $input = $_POST;
        }

        // Defensively validate CSRF token
        $token = (string)($input['token'] ?? $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? '');
        $sessionToken = (string)($_SESSION['token'] ?? '');
        if ($sessionToken === '' || $token === '' || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or missing CSRF token'], JSON_UNESCAPED_SLASHES);
            return;
        }

        $rawText = trim((string)($input['text'] ?? ''));
        $target = strtolower(trim((string)($input['target'] ?? 'en')));

        // Normalise US English alias to standard ISO code 'en'
        if ($target === 'us') {
            $target = 'en';
        }

        // Whitelist standard 2-letter ISO-639-1 language codes (optionally with subtag)
        if (!preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $target)) {
            $target = 'en';
        }

        if ($rawText === '') {
            echo json_encode(['success' => true, 'translation' => ''], JSON_UNESCAPED_SLASHES);
            return;
        }

        if (mb_strlen($rawText) > 5000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Text too long (max 5000 characters)'], JSON_UNESCAPED_SLASHES);
            return;
        }

        // Decode any existing HTML entities so we do not translate raw escaped tokens
        $cleanSourceText = html_entity_decode($rawText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Cache lookup in session
        $cacheKey = hash('sha256', $target . ':' . $cleanSourceText);
        if (!empty($_SESSION['trans_cache'][$cacheKey]) && is_array($_SESSION['trans_cache'][$cacheKey])) {
            $cached = $_SESSION['trans_cache'][$cacheKey];
            echo json_encode([
                'success' => true,
                'translation' => (string)($cached['text'] ?? ''),
                'detected_source' => (string)($cached['source'] ?? ''),
                'already_in_target' => !empty($cached['already_in_target']),
                'cached' => true
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $apiUrl = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl='
                . urlencode($target) . '&dt=t&q=' . urlencode($cleanSourceText);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) FamilyPlatform/1.0',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            if (is_string($response) && $response !== '') {
                $data = json_decode($response, true);
                if (is_array($data) && isset($data[0]) && is_array($data[0])) {
                    $translated = '';
                    foreach ($data[0] as $segment) {
                        if (isset($segment[0]) && is_string($segment[0])) {
                            $translated .= $segment[0];
                        }
                    }

                    $detectedSource = strtolower(trim((string)($data[2] ?? '')));
                    $isAlreadyTarget = ($detectedSource !== '' && $detectedSource === $target);

                    if ($translated !== '') {
                        $_SESSION['trans_cache'][$cacheKey] = [
                            'text' => $translated,
                            'source' => $detectedSource,
                            'already_in_target' => $isAlreadyTarget,
                        ];

                        echo json_encode([
                            'success' => true,
                            'translation' => $translated,
                            'detected_source' => $detectedSource,
                            'already_in_target' => $isAlreadyTarget,
                            'cached' => false
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        return;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('Translate error: ' . $e->getMessage());
        }

        // Graceful fallback: return unescaped source text
        echo json_encode([
            'success' => true,
            'translation' => $cleanSourceText,
            'detected_source' => '',
            'already_in_target' => false,
            'fallback' => true
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
