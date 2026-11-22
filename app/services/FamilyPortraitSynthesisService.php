<?php

declare(strict_types=1);

namespace App\services;

use GdImage;
use InvalidArgumentException;
use Throwable;

/**
 * Sovereign Family Portrait Synthesis Service
 *
 * Powers:
 *  1. Living Heirloom Re-Emotion Keyframes (Warm Smile, Nostalgic Nod, Joyful Laugh, Serene)
 *  2. Future Age Progression Engine (+10, +20, +30, +40 years)
 *
 * Multi-tier resilient architecture:
 *  - Tier 1: Google Gemini Native Image-to-Image (gemini-2.5-flash-image / gemini-3.1-flash-image)
 *  - Tier 2: Sovereign Deterministic PHP GD Engine (zero-SaaS offline/test guarantee)
 */
final class FamilyPortraitSynthesisService
{
    public const MAX_RAW_BYTES = 4 * 1024 * 1024; // 4 MB
    public const TARGET_WIDTH = 512;
    public const TARGET_HEIGHT = 576;

    /**
     * Supported re-emotion presets for Living Heirloom portraits.
     *
     * @var array<string, array{label: string, prompt: string, warmth: float}>
     */
    public const EMOTIONS = [
        'warm_smile' => [
            'label'  => 'Warm Smile',
            'prompt' => 'Gently transform the facial expression of the exact person in this portrait into a warm, affectionate, natural smile with soft crinkled eyes, relaxed cheeks, and a loving gaze directed at the viewer.',
            'warmth' => 1.08,
        ],
        'joyful_laugh' => [
            'label'  => 'Joyful Laugh',
            'prompt' => 'Transform the facial expression of the exact person in this portrait into a genuine, joyful mid-laugh with lifted cheekbones, bright eyes, and natural teeth-showing warmth.',
            'warmth' => 1.12,
        ],
        'nostalgic_reflection' => [
            'label'  => 'Nostalgic Reflection',
            'prompt' => 'Gently adjust the expression of the exact person in this portrait to a serene, wise, softly reflective expression with a subtle knowing smile and calm, reassuring eyes.',
            'warmth' => 1.04,
        ],
        'loving_blessing' => [
            'label'  => 'Loving Blessing',
            'prompt' => 'Transform the expression of the exact person in this portrait into a proud, tender, encouraging expression as if offering a heartfelt blessing to family.',
            'warmth' => 1.10,
        ],
    ];

    /**
     * Generate an age-progressed portrait (+10, +20, +30, +40 years).
     *
     * @param array<string, mixed> $options
     * @return array{
     *     success: bool,
     *     image_data_url: string,
     *     tier: string,
     *     current_age: int,
     *     target_age: int,
     *     years_delta: int,
     *     summary: string
     * }
     */
    public function generateAgeProgression(
        string $rawImageBytes,
        int $currentAge,
        int $yearsDelta,
        string $gender = 'Unspecified',
        array $options = []
    ): array {
        $yearsDelta = max(5, min(60, $yearsDelta));
        $currentAge = max(1, min(95, $currentAge));
        $targetAge = min(105, $currentAge + $yearsDelta);

        $normalisedJpeg = $this->normaliseToJpeg($rawImageBytes);
        $skipRemote = (bool)($options['force_local'] ?? false);

        if (!$skipRemote) {
            $morphology = $this->analyseFacialMorphology($normalisedJpeg);
            $morphologyClause = $morphology !== '' ? " Extracted facial morphology to lock: {$morphology}." : '';

            $prompt = sprintf(
                'Create an ultra-realistic, studio-grade photograph showing how the exact person in this reference photo will naturally look %d years in the future at approximately age %d.%s '
                . 'STRICT IDENTITY LOCK: Preserve their exact craniofacial bone structure, inter-ocular distance, eye colour and shape, nose bridge and tip geometry, lip proportions, ethnicity, skin undertone, and unmistakable personal likeness. '
                . 'PHOTOREALISTIC AGEING: Model authentic chronological ageing for age %d — realistic pore-level skin texture, subtle volume loss at temples and cheeks, natural periorbital crow\'s feet, deepening nasolabial folds, mature neck/jawline definition, and natural silvering/greying at the temples and hair matching their natural hair texture. '
                . 'PHOTOGRAPHIC REALISM: Natural 85mm portrait lens depth of field, authentic skin subsurface scattering, realistic lighting matching the input photo. Do NOT make it look painterly, airbrushed, plastic, or cartoonish. Do NOT add text or watermarks. Gender context: %s.',
                $yearsDelta,
                $targetAge,
                $morphologyClause,
                $targetAge,
                $gender !== '' ? $gender : 'Preserve from photo'
            );

            $geminiJpeg = $this->callGeminiImageEdit($normalisedJpeg, $prompt);
            if ($geminiJpeg !== null) {
                return [
                    'success'        => true,
                    'image_data_url' => 'data:image/jpeg;base64,' . base64_encode($geminiJpeg),
                    'tier'           => 'neural_gemini',
                    'current_age'    => $currentAge,
                    'target_age'     => $targetAge,
                    'years_delta'    => $yearsDelta,
                    'summary'        => "Neural age progression (+{$yearsDelta} years, target age ~{$targetAge}) preserving craniofacial identity.",
                ];
            }
        }

        // Tier 2: Sovereign Deterministic PHP GD Ageing Fallback
        $agedJpeg = $this->applyDeterministicAgeing($normalisedJpeg, $yearsDelta, $targetAge);

        return [
            'success'        => true,
            'image_data_url' => 'data:image/jpeg;base64,' . base64_encode($agedJpeg),
            'tier'           => 'sovereign_gd',
            'current_age'    => $currentAge,
            'target_age'     => $targetAge,
            'years_delta'    => $yearsDelta,
            'summary'        => "Sovereign local age progression (+{$yearsDelta} years, target age ~{$targetAge}) with temple silvering and dermal micro-contrast.",
        ];
    }

    /**
     * Generate a re-emotion portrait keyframe for an ancestor or family member.
     *
     * @param array<string, mixed> $options
     * @return array{success: bool, image_data_url: string, tier: string, emotion: string, label: string}
     */
    public function generateReEmotionPortrait(string $rawImageBytes, string $emotion, array $options = []): array
    {
        $preset = self::EMOTIONS[$emotion] ?? self::EMOTIONS['warm_smile'];
        $emotionKey = isset(self::EMOTIONS[$emotion]) ? $emotion : 'warm_smile';
        $normalisedJpeg = $this->normaliseToJpeg($rawImageBytes);
        $skipRemote = (bool)($options['force_local'] ?? false);

        if (!$skipRemote) {
            $prompt = $preset['prompt']
                . ' CRITICAL INVARIANTS: Keep the exact same person, facial geometry, age, era attire, lighting, and camera crop so it transitions seamlessly in a portrait animator. Do not add text or watermarks.';
            $geminiJpeg = $this->callGeminiImageEdit($normalisedJpeg, $prompt);
            if ($geminiJpeg !== null) {
                return [
                    'success'        => true,
                    'image_data_url' => 'data:image/jpeg;base64,' . base64_encode($geminiJpeg),
                    'tier'           => 'neural_gemini',
                    'emotion'        => $emotionKey,
                    'label'          => $preset['label'],
                ];
            }
        }

        // Tier 2: Sovereign Deterministic PHP GD Warmth & Vitality Enhancement
        $enhancedJpeg = $this->applyDeterministicReEmotion($normalisedJpeg, $preset['warmth']);

        return [
            'success'        => true,
            'image_data_url' => 'data:image/jpeg;base64,' . base64_encode($enhancedJpeg),
            'tier'           => 'sovereign_gd',
            'emotion'        => $emotionKey,
            'label'          => $preset['label'],
        ];
    }

    /**
     * Safely resolve image bytes from a data URI, local upload path, or allowlisted HTTPS URL.
     * Prevents SSRF and path traversal.
     */
    public function resolveSafeImageBytes(string $source): string
    {
        $source = trim($source);
        if ($source === '') {
            throw new InvalidArgumentException('Image source is required.');
        }

        // 1. Base64 Data URI
        if (str_starts_with($source, 'data:image/')) {
            $commaPos = strpos($source, ',');
            if ($commaPos === false) {
                throw new InvalidArgumentException('Malformed image data URI.');
            }
            $b64 = substr($source, $commaPos + 1);
            $decoded = base64_decode($b64, true);
            if (!is_string($decoded) || $decoded === '' || strlen($decoded) > self::MAX_RAW_BYTES) {
                throw new InvalidArgumentException('Invalid or oversized base64 image payload.');
            }
            return $decoded;
        }

        // 2. Local project path (must resolve inside project root)
        $projectRoot = realpath(dirname(__DIR__, 2));
        if ($projectRoot !== false && str_starts_with($source, '/') && !str_starts_with($source, '//')) {
            $cleanPath = strtok($source, '?') ?: $source;
            $candidate = realpath($projectRoot . $cleanPath);
            if ($candidate !== false && str_starts_with($candidate, $projectRoot . DIRECTORY_SEPARATOR) && is_file($candidate)) {
                $bytes = @file_get_contents($candidate);
                if (is_string($bytes) && $bytes !== '' && strlen($bytes) <= self::MAX_RAW_BYTES) {
                    return $bytes;
                }
            }
        }

        // 3. Allowlisted HTTPS remote URL (R2_PUBLIC_DOMAIN or APP_URL only — zero arbitrary SSRF)
        if (preg_match('#^https://#i', $source)) {
            $host = strtolower((string)(parse_url($source, PHP_URL_HOST) ?? ''));
            $allowedHosts = [];
            foreach (['R2_PUBLIC_DOMAIN', 'APP_URL', 'APP_URL2'] as $envKey) {
                $val = trim((string)($_ENV[$envKey] ?? getenv($envKey) ?: ''));
                if ($val !== '') {
                    $parsedHost = parse_url(str_contains($val, '://') ? $val : 'https://' . $val, PHP_URL_HOST);
                    if (is_string($parsedHost) && $parsedHost !== '') {
                        $allowedHosts[] = strtolower($parsedHost);
                    }
                }
            }
            if ($host !== '' && in_array($host, $allowedHosts, true)) {
                $ch = curl_init($source);
                if ($ch !== false) {
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT        => 12,
                        CURLOPT_CONNECTTIMEOUT => 5,
                        CURLOPT_FOLLOWLOCATION => false,
                        CURLOPT_MAXFILESIZE    => self::MAX_RAW_BYTES,
                    ]);
                    $bytes = curl_exec($ch);
                    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if (is_string($bytes) && $code >= 200 && $code < 300 && $bytes !== '') {
                        return $bytes;
                    }
                }
            }
        }

        throw new InvalidArgumentException('Portrait source could not be safely loaded.');
    }

    /**
     * Normalise any supported raster image bytes (JPEG/PNG/WEBP/GIF) to a clean 512x576 JPEG.
     */
    public function normaliseToJpeg(string $rawBytes): string
    {
        if (strlen($rawBytes) > self::MAX_RAW_BYTES) {
            throw new InvalidArgumentException('Image exceeds maximum 4 MB size limit.');
        }
        $src = @imagecreatefromstring($rawBytes);
        if (!$src instanceof GdImage) {
            throw new InvalidArgumentException('Unsupported or corrupted image format.');
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        if ($srcW < 16 || $srcH < 16) {
            imagedestroy($src);
            throw new InvalidArgumentException('Image dimensions are too small.');
        }

        $dst = imagecreatetruecolor(self::TARGET_WIDTH, self::TARGET_HEIGHT);
        if (!$dst instanceof GdImage) {
            imagedestroy($src);
            throw new InvalidArgumentException('Unable to allocate portrait canvas.');
        }

        // Cover-crop centred
        $srcAspect = $srcW / $srcH;
        $dstAspect = self::TARGET_WIDTH / self::TARGET_HEIGHT;
        if ($srcAspect > $dstAspect) {
            $cropH = $srcH;
            $cropW = (int)round($srcH * $dstAspect);
            $cropX = (int)round(($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            $cropW = $srcW;
            $cropH = (int)round($srcW / $dstAspect);
            $cropX = 0;
            $cropY = (int)round(($srcH - $cropH) / 2);
        }

        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, self::TARGET_WIDTH, self::TARGET_HEIGHT, $cropW, $cropH);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 90);
        $jpeg = (string)ob_get_clean();
        imagedestroy($dst);

        return $jpeg;
    }

    /**
     * Call Google Gemini Native Image-to-Image API (single or multi-image).
     *
     * @param array<int, string> $extraJpegBytes
     */
    public function callGeminiImageEdit(string $primaryJpegBytes, string $prompt, array $extraJpegBytes = []): ?string
    {
        $apiKey = trim((string)($_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: $_ENV['GOOGLE_AI_STUDIO_API_KEY'] ?? getenv('GOOGLE_AI_STUDIO_API_KEY') ?: ''));
        if ($apiKey === '') {
            return null;
        }

        $parts = [
            ['text' => $prompt],
            [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data'     => base64_encode($primaryJpegBytes),
                ],
            ],
        ];
        foreach ($extraJpegBytes as $extraBytes) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data'     => base64_encode($extraBytes),
                ],
            ];
        }

        $payload = json_encode([
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => $parts,
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['IMAGE'],
            ],
        ], JSON_UNESCAPED_SLASHES);

        if (!is_string($payload)) {
            return null;
        }

        foreach (['gemini-2.5-flash-image', 'gemini-3.1-flash-image'] as $model) {
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent';
            $ch = curl_init($endpoint);
            if ($ch === false) {
                continue;
            }
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 6,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'x-goog-api-key: ' . $apiKey,
                ],
                CURLOPT_POSTFIELDS     => $payload,
            ]);
            $body = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!is_string($body) || $code < 200 || $code >= 300 || $body === '') {
                continue;
            }

            $decoded = json_decode($body, true);
            $candParts = $decoded['candidates'][0]['content']['parts'] ?? null;
            if (!is_array($candParts)) {
                continue;
            }
            foreach ($candParts as $part) {
                if (!is_array($part)) {
                    continue;
                }
                $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
                $b64 = is_array($inline) ? ($inline['data'] ?? null) : null;
                if (is_string($b64) && $b64 !== '') {
                    $imgBytes = base64_decode($b64, true);
                    if (is_string($imgBytes) && $imgBytes !== '') {
                        try {
                            return $this->normaliseToJpeg($imgBytes);
                        } catch (Throwable) {
                            continue;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Step 1 Vision Analysis: Extract craniofacial morphology or hereditary traits from 1..N portraits
     * using Gemini Vision text model to ground subsequent image synthesis.
     *
     * @param array<int, string> $jpegList
     */
    public function extractVisionMorphology(array $jpegList, string $instruction): string
    {
        $apiKey = trim((string)($_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: $_ENV['GOOGLE_AI_STUDIO_API_KEY'] ?? getenv('GOOGLE_AI_STUDIO_API_KEY') ?: ''));
        if ($apiKey === '' || empty($jpegList)) {
            return '';
        }

        $parts = [['text' => $instruction]];
        foreach (array_slice($jpegList, 0, 4) as $bytes) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data'     => base64_encode($bytes),
                ],
            ];
        }

        $payload = json_encode([
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 260],
        ], JSON_UNESCAPED_SLASHES);

        if (!is_string($payload)) {
            return '';
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
        $ch = curl_init($endpoint);
        if ($ch === false) {
            return '';
        }
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => $payload,
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($body) || $code < 200 || $code >= 300) {
            return '';
        }
        $decoded = json_decode($body, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return is_string($text) ? trim(preg_replace('/\s+/', ' ', $text) ?? '') : '';
    }

    private function analyseFacialMorphology(string $jpegBytes): string
    {
        return $this->extractVisionMorphology(
            [$jpegBytes],
            'Concisely describe in 2 sentences the subject\'s permanent craniofacial structure (face shape, cheekbones, nose bridge, eye shape, brow ridge, jawline, skin undertone, hair texture) for identity-locked age progression. No preamble.'
        );
    }

    /**
     * Tier 2 Sovereign PHP GD Age Progression (deterministic offline fallback).
     */
    private function applyDeterministicAgeing(string $jpegBytes, int $yearsDelta, int $targetAge): string
    {
        $img = @imagecreatefromstring($jpegBytes);
        if (!$img instanceof GdImage) {
            return $jpegBytes;
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $ageFactor = min(1.0, max(0.15, ($yearsDelta / 45.0) + ($targetAge > 60 ? 0.25 : 0.0)));

        // Subtle dermal micro-contrast & maturity tone
        imagefilter($img, IMG_FILTER_CONTRAST, (int)round(-10 * $ageFactor));

        // Silvering at upper hair/temple region + warm parchment maturity
        $silverStrength = $ageFactor * 0.45;
        for ($y = 0; $y < $h; $y++) {
            $ny = $y / $h;
            for ($x = 0; $x < $w; $x++) {
                $nx = $x / $w;
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Hair/temple silvering in upper 32% or outer temple edges
                $inHairBand = ($ny < 0.30) || ($ny < 0.48 && ($nx < 0.24 || $nx > 0.76));
                if ($inHairBand) {
                    $lum = (int)round(0.299 * $r + 0.587 * $g + 0.114 * $b);
                    if ($lum < 145) {
                        $silverTarget = min(225, $lum + (int)round(95 * $silverStrength));
                        $r = (int)round($r * (1 - $silverStrength) + $silverTarget * $silverStrength);
                        $g = (int)round($g * (1 - $silverStrength) + $silverTarget * $silverStrength);
                        $b = (int)round($b * (1 - $silverStrength) + ($silverTarget + 4) * $silverStrength);
                    }
                }

                // Subtle maturity warmth & contrast
                $r = min(255, max(0, (int)round($r * (1.0 + 0.03 * $ageFactor))));
                $b = min(255, max(0, (int)round($b * (1.0 - 0.05 * $ageFactor))));
                imagesetpixel($img, $x, $y, ($r << 16) | ($g << 8) | $b);
            }
        }

        ob_start();
        imagejpeg($img, null, 90);
        $out = (string)ob_get_clean();
        imagedestroy($img);

        return $out;
    }

    /**
     * Tier 2 Sovereign PHP GD Re-Emotion warmth & vitality enhancement.
     */
    private function applyDeterministicReEmotion(string $jpegBytes, float $warmth): string
    {
        $img = @imagecreatefromstring($jpegBytes);
        if (!$img instanceof GdImage) {
            return $jpegBytes;
        }
        imagefilter($img, IMG_FILTER_BRIGHTNESS, 8);
        imagefilter($img, IMG_FILTER_COLORIZE, (int)round(($warmth - 1.0) * 140), (int)round(($warmth - 1.0) * 70), 0, 0);

        ob_start();
        imagejpeg($img, null, 90);
        $out = (string)ob_get_clean();
        imagedestroy($img);

        return $out;
    }
}
