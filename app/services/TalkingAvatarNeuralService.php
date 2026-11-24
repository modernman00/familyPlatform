<?php

declare(strict_types=1);

namespace App\services;

use InvalidArgumentException;
use Src\Media\PythonSidecarBridge;

/**
 * TalkingAvatarNeuralService
 *
 * Tier B In-House Neural Talking Avatar Pipeline:
 * Validates and stages portrait + script/voice inputs, enforces owner-scoped
 * job IDs (zero IDOR), and dispatches async rendering to the ATG Python GPU
 * Sidecar (Kokoro-82M TTS + LivePortrait/MuseTalk + FFmpeg H.264 MP4).
 */
final class TalkingAvatarNeuralService
{
    public const VOICES = [
        'bf_emma'  => 'British Female — Emma',
        'bm_george'=> 'British Male — George',
        'af_bella' => 'Warm Storyteller — Bella',
        'am_adam'  => 'Deep Narrator — Adam',
    ];

    public const EXPRESSIONS = [
        'natural' => 'Natural Speech & Blinks',
        'smile'   => 'Warm Smile',
        'laugh'   => 'Joyful Laugh',
        'wink'    => 'Cheeky Wink',
        'sing'    => 'Expressive Singing',
    ];

    private const MAX_IMAGE_BYTES = 5_242_880; // 5 MB
    private const MAX_AUDIO_BYTES = 8_388_608; // 8 MB
    private const MAX_SCRIPT_CHARS = 500;

    private string $jobsDir;
    private string $videosDir;

    public function __construct(?string $jobsDir = null, ?string $videosDir = null)
    {
        $root = dirname(__DIR__, 2);
        $this->jobsDir = $jobsDir ?? ($root . '/storage/talking_avatar_jobs');
        $this->videosDir = $videosDir ?? ($root . '/storage/talking_avatars');
    }

    /**
     * Queue a neural talking-avatar render job for the authenticated member.
     *
     * @param string $userId Authenticated member ID
     * @param array<string, mixed> $input Request payload
     * @return array{job_id: string, status: string, sidecar_online: bool, video_url: ?string, message: string}
     */
    public function enqueueRender(string $userId, array $input): array
    {
        if (!AvatarStickerService::isValidUserId($userId)) {
            throw new InvalidArgumentException('Invalid member identifier.');
        }

        $voice = (string)($input['voice_id'] ?? 'bf_emma');
        if (!array_key_exists($voice, self::VOICES)) {
            $voice = 'bf_emma';
        }

        $expression = (string)($input['expression_preset'] ?? 'natural');
        if (!array_key_exists($expression, self::EXPRESSIONS)) {
            $expression = 'natural';
        }

        $rawScript = trim((string)($input['script_text'] ?? ''));
        $script = mb_substr((string)(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $rawScript) ?? ''), 0, self::MAX_SCRIPT_CHARS);

        $imageDataUrl = trim((string)($input['image_data_url'] ?? ''));
        $audioDataUrl = trim((string)($input['audio_data_url'] ?? ''));

        if ($script === '' && $audioDataUrl === '') {
            throw new InvalidArgumentException('Please enter a script or record/upload a voice memo.');
        }

        $imageBytes = $this->decodeDataUrl($imageDataUrl, ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'], self::MAX_IMAGE_BYTES, 'portrait');

        $audioExt = null;
        $audioBytes = null;
        if ($audioDataUrl !== '') {
            [$audioBytes, $audioExt] = $this->decodeAudioDataUrl($audioDataUrl);
        }

        $this->ensureJobsDir();
        $jobId = bin2hex(random_bytes(16));
        $prefix = $userId . '_' . $jobId;

        $imagePath = $this->jobsDir . '/' . $prefix . '.' . $imageBytes['ext'];
        file_put_contents($imagePath, $imageBytes['bytes']);

        $audioPath = null;
        if ($audioBytes !== null) {
            $audioPath = $this->jobsDir . '/' . $prefix . '.' . $audioExt;
            file_put_contents($audioPath, $audioBytes);
        }

        $manifestPath = $this->manifestPath($userId, $jobId);
        $manifest = [
            'job_id'            => $jobId,
            'user_id'           => $userId,
            'status'            => 'queued',
            'voice_id'          => $voice,
            'expression_preset' => $expression,
            'script_text'       => $script,
            'image_path'        => $imagePath,
            'audio_path'        => $audioPath,
            'video_url'         => null,
            'error'             => null,
            'created_at'        => gmdate('c'),
        ];
        file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $sidecarRes = PythonSidecarBridge::dispatch('talking_avatar', [
            'job_id'            => $jobId,
            'user_id'           => $userId,
            'image_path'        => $imagePath,
            'audio_path'        => $audioPath,
            'script_text'       => $script,
            'voice_id'          => $voice,
            'expression_preset' => $expression,
            'manifest_path'     => $manifestPath,
        ]);

        if (is_array($sidecarRes) && in_array((string)($sidecarRes['status'] ?? ''), ['ok', 'queued', 'completed'], true)) {
            $videoUrl = isset($sidecarRes['video_url']) && is_string($sidecarRes['video_url']) ? $sidecarRes['video_url'] : null;
            $status = $videoUrl !== null ? 'completed' : 'queued';
            return [
                'job_id'         => $jobId,
                'status'         => $status,
                'sidecar_online' => true,
                'video_url'      => $videoUrl,
                'message'        => $status === 'completed'
                    ? 'Your HD neural talking video is ready!'
                    : 'Your HD neural talking video is rendering on the GPU worker…',
            ];
        }

        // On shared hosting (no sidecar daemon), remove staged media immediately to prevent disk bloat
        @unlink($imagePath);
        if ($audioPath !== null) {
            @unlink($audioPath);
        }

        return [
            'job_id'         => $jobId,
            'status'         => 'sidecar_offline',
            'sidecar_online' => false,
            'video_url'      => null,
            'message'        => 'Using instant in-browser ATG animation engine.',
        ];
    }

    /**
     * Owner-scoped job status lookup (returns null for foreign or non-existent jobs).
     *
     * @return array<string, mixed>|null
     */
    public function getJobStatus(string $userId, string $jobId): ?array
    {
        if (!AvatarStickerService::isValidUserId($userId) || !preg_match('/^[a-f0-9]{32}$/', $jobId)) {
            return null;
        }
        $path = $this->manifestPath($userId, $jobId);
        if (!is_file($path)) {
            return null;
        }
        $raw = (string)file_get_contents($path);
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || (string)($decoded['user_id'] ?? '') !== $userId) {
            return null;
        }
        return [
            'job_id'    => $jobId,
            'status'    => (string)($decoded['status'] ?? 'queued'),
            'video_url' => isset($decoded['video_url']) && is_string($decoded['video_url']) ? $decoded['video_url'] : null,
            'error'     => isset($decoded['error']) && is_string($decoded['error']) ? $decoded['error'] : null,
        ];
    }

    /**
     * Resolve an owner-scoped rendered MP4 path (returns null for foreign/invalid jobs).
     */
    public function getVideoFilePath(string $userId, string $jobId): ?string
    {
        if (!AvatarStickerService::isValidUserId($userId) || !preg_match('/^[a-f0-9]{32}$/', $jobId)) {
            return null;
        }
        $path = $this->videosDir . '/' . $userId . '_' . $jobId . '.mp4';
        return is_file($path) ? $path : null;
    }

    private function manifestPath(string $userId, string $jobId): string
    {
        return $this->jobsDir . '/' . $userId . '_' . $jobId . '.json';
    }

    private function ensureJobsDir(): void
    {
        if (!is_dir($this->jobsDir)) {
            mkdir($this->jobsDir, 0750, true);
        }
    }

    /**
     * @param array<string, string> $allowedMimes
     * @return array{bytes: string, ext: string}
     */
    private function decodeDataUrl(string $dataUrl, array $allowedMimes, int $maxBytes, string $label): array
    {
        if (!preg_match('#^data:([a-z0-9/+.-]+);base64,([A-Za-z0-9+/=\s]+)$#i', $dataUrl, $m)) {
            throw new InvalidArgumentException("Invalid {$label} data URL.");
        }
        $mime = strtolower($m[1]);
        if (!array_key_exists($mime, $allowedMimes)) {
            throw new InvalidArgumentException("Unsupported {$label} format.");
        }
        $bytes = base64_decode($m[2], true);
        if (!is_string($bytes) || $bytes === '' || strlen($bytes) > $maxBytes) {
            throw new InvalidArgumentException("Invalid or oversized {$label} payload.");
        }
        return ['bytes' => $bytes, 'ext' => $allowedMimes[$mime]];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function decodeAudioDataUrl(string $dataUrl): array
    {
        $res = $this->decodeDataUrl(
            $dataUrl,
            [
                'audio/wav'  => 'wav',
                'audio/x-wav'=> 'wav',
                'audio/mpeg' => 'mp3',
                'audio/mp3'  => 'mp3',
                'audio/webm' => 'webm',
                'audio/ogg'  => 'ogg',
                'audio/mp4'  => 'm4a',
            ],
            self::MAX_AUDIO_BYTES,
            'audio'
        );
        return [$res['bytes'], $res['ext']];
    }
}
