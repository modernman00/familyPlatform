<?php

declare(strict_types=1);

namespace App\controller;

class TranslateController extends BaseController
{
    private const ALLOWED_TARGETS = ['en', 'fr', 'es'];

    public function __construct()
    {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Translate text on-demand for diaspora members
     */
    public function translate(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $raw = file_get_contents('php://input');
        $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($input)) {
            $input = $_POST;
        }

        $rawText = trim((string)($input['text'] ?? ''));
        $target = strtolower(trim((string)($input['target'] ?? 'en')));

        if ($rawText === '') {
            echo json_encode(['success' => true, 'translation' => '']);
            return;
        }

        if (mb_strlen($rawText) > 5000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Text too long (max 5000 characters)']);
            return;
        }

        if (!in_array($target, self::ALLOWED_TARGETS, true)) {
            $target = 'en';
        }

        // Sanitize text for defensive output & XSS prevention
        $safeText = htmlspecialchars($rawText, ENT_QUOTES, 'UTF-8');

        // Cache lookup in session
        $cacheKey = hash('sha256', $target . ':' . $rawText);
        if (!empty($_SESSION['trans_cache'][$cacheKey]) && is_string($_SESSION['trans_cache'][$cacheKey])) {
            $cachedVal = htmlspecialchars((string)$_SESSION['trans_cache'][$cacheKey], ENT_QUOTES, 'UTF-8');
            echo json_encode([
                'success' => true,
                'translation' => $cachedVal,
                'cached' => true
            ]);
            return;
        }

        try {
            $apiUrl = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl='
                . urlencode($target) . '&dt=t&q=' . urlencode($rawText);

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
                        if (isset($segment[0])) {
                            $translated .= (string)$segment[0];
                        }
                    }
                    if ($translated !== '') {
                        $_SESSION['trans_cache'][$cacheKey] = $translated;
                        $safeTranslated = htmlspecialchars($translated, ENT_QUOTES, 'UTF-8');
                        echo json_encode([
                            'success' => true,
                            'translation' => $safeTranslated,
                            'cached' => false
                        ]);
                        return;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('Translate error: ' . $e->getMessage());
        }

        // Graceful fallback: return sanitized original text
        echo json_encode([
            'success' => true,
            'translation' => $safeText,
            'fallback' => true
        ]);
    }
}
