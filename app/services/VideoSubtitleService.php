<?php

declare(strict_types=1);

namespace App\services;

use Src\Exceptions\ValidationException;

/**
 * VideoSubtitleService
 *
 * Generates, caches, and parses WebVTT (CC) subtitle tracks for FamilyPlatform videos.
 * - Strictly confines local file access to `resources/videos/`, `public/uploads/`, and `public/storage/`.
 * - Extracts lightweight mono 16kHz audio via `ffmpeg` (`proc_open` array, 20s timeout).
 * - Uses Gemini Multimodal Audio (`GEMINI_API_KEY`) to produce timestamped British English WebVTT cues.
 * - Caches generated tracks in `storage/subtitles/` and provides deterministic fallbacks.
 */
final class VideoSubtitleService
{
    private const CACHE_SUBDIR = 'storage/subtitles';
    private const MAX_AUDIO_BYTES = 15_728_640; // 15 MB inline base64 cap
    private const FFMPEG_TIMEOUT_SEC = 20;
    private const GEMINI_CONNECT_TIMEOUT_SEC = 5;
    private const GEMINI_TIMEOUT_SEC = 25;

    /**
     * Generate or retrieve cached WebVTT subtitles for a video source URL or path.
     *
     * @return array{
     *     success: bool,
     *     cached: bool,
     *     fallback: bool,
     *     language: string,
     *     label: string,
     *     vtt: string,
     *     cues: list<array{start: float, end: float, text: string}>
     * }
     */
    public static function generateForVideo(string $rawSrc, string $lang = 'en-GB'): array
    {
        $normalizedSrc = self::normaliseSource($rawSrc);
        if ($normalizedSrc === '') {
            throw new ValidationException('A valid video source path or URL is required.');
        }

        $safeLang = preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $lang) === 1 ? $lang : 'en-GB';
        $cacheFile = self::resolveCachePath($normalizedSrc, $safeLang);

        if (is_file($cacheFile)) {
            $cachedVtt = file_get_contents($cacheFile);
            if (is_string($cachedVtt) && str_contains($cachedVtt, 'WEBVTT')) {
                $cues = self::parseVttCues($cachedVtt);
                if ($cues !== []) {
                    return [
                        'success' => true,
                        'cached' => true,
                        'fallback' => false,
                        'language' => $safeLang,
                        'label' => 'English (Auto-generated)',
                        'vtt' => $cachedVtt,
                        'cues' => $cues,
                    ];
                }
            }
        }

        $localPath = self::resolveSafeLocalVideoPath($normalizedSrc);
        if ($localPath !== null) {
            $generatedVtt = self::transcribeLocalVideoToVtt($localPath);
            if ($generatedVtt !== null) {
                $cues = self::parseVttCues($generatedVtt);
                if ($cues !== []) {
                    self::writeCache($cacheFile, $generatedVtt);
                    return [
                        'success' => true,
                        'cached' => false,
                        'fallback' => false,
                        'language' => $safeLang,
                        'label' => 'English (Auto-generated)',
                        'vtt' => $generatedVtt,
                        'cues' => $cues,
                    ];
                }
            }
        }

        // Deterministic fallback when video is remote-only, silent, or AI key is unavailable
        $duration = $localPath !== null ? self::probeDurationSeconds($localPath) : 8.0;
        $endSec = max(3.0, min($duration, 12.0));
        $fallbackText = $localPath === null
            ? '[Auto-captions unavailable for external stream]'
            : '[No spoken dialogue detected in this clip]';
        $fallbackVtt = "WEBVTT\n\n00:00:00.000 --> " . self::formatVttTimestamp($endSec) . "\n{$fallbackText}\n";

        return [
            'success' => true,
            'cached' => false,
            'fallback' => true,
            'language' => $safeLang,
            'label' => 'English (Auto-generated)',
            'vtt' => $fallbackVtt,
            'cues' => self::parseVttCues($fallbackVtt),
        ];
    }

    /**
     * Normalise and validate a video source string; rejects traversal, null bytes, and unsafe schemes.
     */
    public static function normaliseSource(string $rawSrc): string
    {
        $trimmed = trim($rawSrc);
        if ($trimmed === '' || str_contains($trimmed, "\0")) {
            return '';
        }

        // Strip URL fragment (#t=0,30)
        $hashPos = strpos($trimmed, '#');
        if ($hashPos !== false) {
            $trimmed = substr($trimmed, 0, $hashPos);
        }

        if (str_contains($trimmed, '..')) {
            return '';
        }

        // Convert same-origin absolute URL to relative path
        if (preg_match('#^https?://#i', $trimmed) === 1) {
            $parsed = parse_url($trimmed);
            if (!is_array($parsed) || empty($parsed['host'])) {
                return '';
            }
            $host = strtolower((string)$parsed['host']);
            $appUrl = (string)($_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? getenv('APP_URL') ?: '');
            $appHost = $appUrl !== '' ? strtolower((string)(parse_url($appUrl, PHP_URL_HOST) ?: '')) : '';

            // Reject private/loopback IPs unless matching configured APP_URL host
            if ($host !== $appHost && (
                $host === 'localhost'
                || $host === '127.0.0.1'
                || $host === '::1'
                || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
            )) {
                // Only reject if it's an IP literal in private/reserved range
                if (filter_var($host, FILTER_VALIDATE_IP) !== false || $host === 'localhost') {
                    if ($host !== $appHost) {
                        return '';
                    }
                }
            }

            if ($appHost !== '' && $host === $appHost && !empty($parsed['path'])) {
                return (string)$parsed['path'];
            }

            return $trimmed;
        }

        if (!str_starts_with($trimmed, '/')) {
            return '';
        }

        $pathOnly = parse_url($trimmed, PHP_URL_PATH);
        return is_string($pathOnly) ? $pathOnly : '';
    }

    /**
     * Resolve a local video path strictly inside allowed media directories.
     */
    public static function resolveSafeLocalVideoPath(string $normalizedSrc): ?string
    {
        if (preg_match('#^https?://#i', $normalizedSrc) === 1) {
            return null;
        }

        $projectRoot = dirname(__DIR__, 2);
        $allowedPrefixes = [
            '/resources/videos/',
            '/public/uploads/',
            '/public/storage/',
        ];

        $hasAllowedPrefix = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($normalizedSrc, $prefix)) {
                $hasAllowedPrefix = true;
                break;
            }
        }
        if (!$hasAllowedPrefix) {
            return null;
        }

        $candidate = $projectRoot . $normalizedSrc;
        $realPath = realpath($candidate);
        if ($realPath === false || !is_file($realPath)) {
            return null;
        }

        $allowedRoots = [
            realpath($projectRoot . '/resources/videos'),
            realpath($projectRoot . '/public/uploads'),
            realpath($projectRoot . '/public/storage'),
        ];

        foreach ($allowedRoots as $root) {
            if (is_string($root) && str_starts_with($realPath, $root . DIRECTORY_SEPARATOR)) {
                return $realPath;
            }
        }

        return null;
    }

    /**
     * Parse WebVTT string into sanitized cue objects.
     *
     * @return list<array{start: float, end: float, text: string}>
     */
    public static function parseVttCues(string $vtt): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($vtt));
        if ($normalized === '') {
            return [];
        }

        $blocks = preg_split("/\n{2,}/", $normalized) ?: [];
        $cues = [];

        foreach ($blocks as $block) {
            $lines = array_values(array_filter(
                array_map('trim', explode("\n", $block)),
                static fn(string $l): bool => $l !== ''
            ));
            if ($lines === []) {
                continue;
            }

            if (str_starts_with(strtoupper($lines[0]), 'WEBVTT') || str_starts_with(strtoupper($lines[0]), 'NOTE')) {
                continue;
            }

            $timeLineIdx = null;
            foreach ($lines as $idx => $line) {
                if (str_contains($line, '-->')) {
                    $timeLineIdx = $idx;
                    break;
                }
            }
            if ($timeLineIdx === null) {
                continue;
            }

            if (preg_match('/(\d{1,2}:)?\d{2}:\d{2}[.,]\d{3}\s*-->\s*(\d{1,2}:)?\d{2}:\d{2}[.,]\d{3}/', $lines[$timeLineIdx], $m) !== 1) {
                continue;
            }

            [$startRaw, $endRaw] = array_map('trim', explode('-->', $m[0], 2));
            $start = self::parseVttTimestamp($startRaw);
            $end = self::parseVttTimestamp($endRaw);
            if ($end <= $start) {
                continue;
            }

            $textLines = array_slice($lines, $timeLineIdx + 1);
            $plainText = trim(preg_replace('/\s+/', ' ', strip_tags(implode(' ', $textLines))) ?? '');
            if ($plainText === '') {
                continue;
            }

            $cues[] = [
                'start' => round($start, 3),
                'end' => round($end, 3),
                'text' => $plainText,
            ];
        }

        return $cues;
    }

    private static function transcribeLocalVideoToVtt(string $videoPath): ?string
    {
        $apiKey = trim((string)($_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: ''));
        if ($apiKey === '') {
            return null;
        }

        $ffmpeg = self::findBinary('ffmpeg');
        if ($ffmpeg === null) {
            return null;
        }

        $tmpBase = tempnam(sys_get_temp_dir(), 'fp_sub_');
        if ($tmpBase === false) {
            return null;
        }
        $audioPath = $tmpBase . '.mp3';
        @unlink($tmpBase);

        try {
            $cmd = [
                $ffmpeg,
                '-y',
                '-i',
                $videoPath,
                '-vn',
                '-ac',
                '1',
                '-ar',
                '16000',
                '-b:a',
                '32k',
                '-f',
                'mp3',
                $audioPath,
            ];

            $ok = self::runProcessWithTimeout($cmd, self::FFMPEG_TIMEOUT_SEC);
            if (!$ok || !is_file($audioPath)) {
                return null;
            }

            $size = filesize($audioPath);
            if ($size === false || $size === 0 || $size > self::MAX_AUDIO_BYTES) {
                return null;
            }

            $audioBytes = file_get_contents($audioPath);
            if ($audioBytes === false || $audioBytes === '') {
                return null;
            }

            return self::callGeminiForVtt($audioBytes, $apiKey);
        } finally {
            if (is_file($audioPath)) {
                @unlink($audioPath);
            }
        }
    }

    private static function callGeminiForVtt(string $audioBytes, string $apiKey): ?string
    {
        $model = trim((string)($_ENV['GEMINI_MODEL'] ?? $_SERVER['GEMINI_MODEL'] ?? getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash'));
        if ($model === '' || str_contains($model, '/')) {
            $model = 'gemini-2.5-flash';
        }

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            rawurlencode($model),
            rawurlencode($apiKey)
        );

        $prompt = "Transcribe the spoken dialogue in this audio into valid WebVTT (WEBVTT) subtitles using British English spelling.\n"
            . "Rules:\n"
            . "1. Output ONLY raw WebVTT starting with 'WEBVTT' on line 1 (no markdown code fences).\n"
            . "2. Use accurate timestamps in HH:MM:SS.mmm --> HH:MM:SS.mmm format.\n"
            . "3. Keep each cue concise (1-2 short lines, max ~42 chars per line).\n"
            . "4. If there is only music or silence with no speech, output:\nWEBVTT\n\n00:00:00.000 --> 00:00:04.000\n[Music / No spoken dialogue]";

        $payload = json_encode([
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    [
                        'inline_data' => [
                            'mime_type' => 'audio/mp3',
                            'data' => base64_encode($audioBytes),
                        ],
                    ],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 4096,
            ],
        ]);

        if ($payload === false) {
            return null;
        }

        $ch = curl_init($endpoint);
        if ($ch === false) {
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_CONNECTTIMEOUT => self::GEMINI_CONNECT_TIMEOUT_SEC,
            CURLOPT_TIMEOUT => self::GEMINI_TIMEOUT_SEC,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($response) || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return null;
        }

        $text = (string)($decoded['candidates'][0]['content']['parts'][0]['text'] ?? '');
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        // Strip accidental markdown fences
        $text = preg_replace('/^```(?:vtt|webvtt)?\s*/i', '', $text) ?? $text;
        $text = preg_replace('/\s*```$/', '', $text) ?? $text;
        $text = trim($text);

        if (!str_starts_with(strtoupper($text), 'WEBVTT')) {
            $text = "WEBVTT\n\n" . $text;
        }

        return $text;
    }

    private static function probeDurationSeconds(string $videoPath): float
    {
        $ffprobe = self::findBinary('ffprobe');
        if ($ffprobe === null) {
            return 8.0;
        }

        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = @proc_open([
            $ffprobe,
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $videoPath,
        ], $spec, $pipes);

        if (!is_resource($proc)) {
            return 8.0;
        }

        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        return is_string($out) && is_numeric(trim($out)) ? max(1.0, (float)trim($out)) : 8.0;
    }

    /**
     * @param list<string> $cmd
     */
    private static function runProcessWithTimeout(array $cmd, int $timeoutSeconds): bool
    {
        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = @proc_open($cmd, $spec, $pipes);
        if (!is_resource($proc)) {
            return false;
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $deadline = microtime(true) + $timeoutSeconds;
        while (true) {
            $status = proc_get_status($proc);
            if (!$status['running']) {
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($proc);
                return $status['exitcode'] === 0;
            }
            if (microtime(true) >= $deadline) {
                @proc_terminate($proc, 9);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($proc);
                return false;
            }
            usleep(50_000);
        }
    }

    private static function findBinary(string $name): ?string
    {
        foreach (["/opt/homebrew/bin/{$name}", "/usr/local/bin/{$name}", "/usr/bin/{$name}"] as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    private static function resolveCachePath(string $normalizedSrc, string $lang): string
    {
        $dir = dirname(__DIR__, 2) . '/' . self::CACHE_SUBDIR;
        $hash = hash('sha256', $normalizedSrc . '|' . $lang);
        return $dir . '/' . $hash . '.vtt';
    }

    private static function writeCache(string $cacheFile, string $vtt): void
    {
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        @file_put_contents($cacheFile, $vtt, LOCK_EX);
    }

    private static function parseVttTimestamp(string $ts): float
    {
        $clean = str_replace(',', '.', trim($ts));
        $parts = explode(':', $clean);
        if (count($parts) === 3) {
            return ((float)$parts[0] * 3600.0) + ((float)$parts[1] * 60.0) + (float)$parts[2];
        }
        if (count($parts) === 2) {
            return ((float)$parts[0] * 60.0) + (float)$parts[1];
        }
        return 0.0;
    }

    private static function formatVttTimestamp(float $seconds): string
    {
        $ms = (int)round(max(0.0, $seconds) * 1000);
        $hrs = intdiv($ms, 3_600_000);
        $ms %= 3_600_000;
        $mins = intdiv($ms, 60_000);
        $ms %= 60_000;
        $secs = intdiv($ms, 1000);
        $remMs = $ms % 1000;
        return sprintf('%02d:%02d:%02d.%03d', $hrs, $mins, $secs, $remMs);
    }
}
