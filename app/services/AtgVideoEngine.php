<?php

declare(strict_types=1);

namespace App\services;

use Src\Exceptions\ValidationException;
use Src\Media\SovereignVideoJobHandler;

/**
 * AtgVideoEngine
 *
 * Sovereign Kinship Video Studio service for FamilyPlatform.
 * Delegates deterministic FFmpeg/PHP fallbacks, GDPR Article 9 consent gates,
 * and Marcus & Ghost adversarial sanitisation to Src\Media\SovereignVideoJobHandler.
 */
class AtgVideoEngine
{
    /** @var array<int, string> */
    public const SUPPORTED_MODULES = [
        'text_to_video',
        'image_to_video',
        'ai_avatars',
        'auto_captions',
        'tts_voiceovers',
        'voice_cloning',
        'auto_cut_silence',
        'background_removal',
        'subject_tracking',
        'video_upscaling',
        'colour_matching',
        'audio_noise_reduction',
        'atmospheric_overlay',
        'blend_mode',
        'feathered_mask',
        'adjustment_layer',
        'lighting_mood',
        'nle_project',
    ];

    /**
     * Dispatch a deterministic server fallback / manifest computation for any of the 12 studio modules.
     *
     * @param string $module
     * @param array<string, mixed> $payload
     * @param string $userId
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function processModule(string $module, array $payload, string $userId): array
    {
        if (!in_array($module, self::SUPPORTED_MODULES, true)) {
            throw new ValidationException('Unsupported Sovereign Studio module requested.');
        }

        if (trim($userId) === '') {
            throw new ValidationException('Authenticated user ID is required.');
        }

        return match ($module) {
            'text_to_video' => [
                'module'   => $module,
                'manifest' => SovereignVideoJobHandler::buildStoryboardManifest(
                    (string) ($payload['prompt'] ?? ''),
                    (string) ($payload['template'] ?? 'heritage_gold'),
                    (float) ($payload['duration_sec'] ?? 15.0)
                ),
            ],
            'image_to_video' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildParallaxFilterGraph(
                    (int) ($payload['width'] ?? 1080),
                    (int) ($payload['height'] ?? 1920),
                    (float) ($payload['duration_sec'] ?? 4.0),
                    (string) ($payload['motion'] ?? 'zoom_in')
                ),
            ],
            'ai_avatars' => [
                'module'   => $module,
                'timeline' => SovereignVideoJobHandler::buildAvatarVisemeTimeline(
                    array_map(static fn (mixed $v): float => (float) $v, (array) ($payload['rms_samples'] ?? [0.05, 0.35, 0.55, 0.15])),
                    (float) ($payload['interval_sec'] ?? 0.05)
                ),
            ],
            'auto_captions' => [
                'module' => $module,
                'srt'    => SovereignVideoJobHandler::buildKaraokeSrt(
                    $this->normaliseWordArray((array) ($payload['words'] ?? [])),
                    (int) ($payload['words_per_line'] ?? 5)
                ),
            ],
            'tts_voiceovers' => [
                'module'     => $module,
                'descriptor' => SovereignVideoJobHandler::buildBritishTtsDescriptor(
                    (string) ($payload['text'] ?? ''),
                    (string) ($payload['profile'] ?? 'oliver'),
                    (float) ($payload['rate'] ?? 1.0),
                    (float) ($payload['pitch_semitones'] ?? 0.0)
                ),
            ],
            'voice_cloning' => [
                'module'      => $module,
                'consent'     => 'gdpr_article_9_verified',
                'filterGraph' => SovereignVideoJobHandler::buildVoiceDubbingFilterGraph(
                    (bool) ($payload['gdpr_article9_consent'] ?? false),
                    $userId,
                    (float) ($payload['pitch_ratio'] ?? 1.0),
                    (float) ($payload['formant_shift_hz'] ?? 0.0)
                ),
            ],
            'auto_cut_silence' => [
                'module'    => $module,
                'intervals' => SovereignVideoJobHandler::detectSilencePcm(
                    array_map(static fn (mixed $v): float => (float) $v, (array) ($payload['pcm_samples'] ?? [])),
                    (int) ($payload['sample_rate'] ?? 44100),
                    (float) ($payload['threshold_rms'] ?? 0.02),
                    (float) ($payload['min_silence_sec'] ?? 0.4)
                ),
            ],
            'background_removal' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildChromaKeyFilterGraph(
                    (string) ($payload['hex_colour'] ?? '00FF00'),
                    (float) ($payload['similarity'] ?? 0.15),
                    (float) ($payload['blend'] ?? 0.08)
                ),
            ],
            'subject_tracking' => [
                'module' => $module,
                'region' => SovereignVideoJobHandler::validateTrackingRegion(
                    (int) ($payload['x'] ?? 0),
                    (int) ($payload['y'] ?? 0),
                    (int) ($payload['box_w'] ?? 160),
                    (int) ($payload['box_h'] ?? 160),
                    (int) ($payload['frame_w'] ?? 1080),
                    (int) ($payload['frame_h'] ?? 1920),
                    (string) ($payload['mode'] ?? 'callout')
                ),
            ],
            'video_upscaling' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildLanczosUpscaleFilterGraph(
                    (int) ($payload['target_width'] ?? 1920),
                    (int) ($payload['target_height'] ?? 1080),
                    (float) ($payload['sharpen'] ?? 0.8)
                ),
            ],
            'colour_matching' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildColourMatchFilterGraph(
                    (string) ($payload['lut_preset'] ?? 'heritage_gold'),
                    (float) ($payload['skin_protection'] ?? 0.85)
                ),
            ],
            'audio_noise_reduction' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildNoiseReductionFilterGraph(
                    (int) ($payload['hum_hz'] ?? 50),
                    (float) ($payload['noise_floor_db'] ?? -28.0)
                ),
            ],
            'atmospheric_overlay' => [
                'module'      => $module,
                'library'     => SovereignVideoJobHandler::getAtmosphericOverlayLibrary(),
                'filterGraph' => SovereignVideoJobHandler::buildAtmosphericOverlayFilterGraph(
                    (string) ($payload['overlay_id'] ?? 'bonfire_flames'),
                    (int) ($payload['width'] ?? 1280),
                    (int) ($payload['height'] ?? 720)
                ),
            ],
            'blend_mode' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildBlendModeFilterGraph(
                    (string) ($payload['blend_mode'] ?? 'screen'),
                    (float) ($payload['opacity'] ?? 1.0)
                ),
            ],
            'feathered_mask' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildMaskFilterGraph(
                    (string) ($payload['mask_type'] ?? 'linear_gradient'),
                    (float) ($payload['feather'] ?? 24.0),
                    (bool) ($payload['invert'] ?? false),
                    (int) ($payload['width'] ?? 1280),
                    (int) ($payload['height'] ?? 720)
                ),
            ],
            'adjustment_layer' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildAdjustmentFilterGraph(
                    is_array($payload['adjustments'] ?? null) ? $payload['adjustments'] : []
                ),
            ],
            'lighting_mood' => [
                'module'      => $module,
                'filterGraph' => SovereignVideoJobHandler::buildLightingMoodFilterGraph(
                    (float) ($payload['strobe_frequency'] ?? 0.0),
                    (float) ($payload['flicker_intensity'] ?? 0.0),
                    (string) ($payload['glow_preset'] ?? 'warm_glowing_tan'),
                    (float) ($payload['glow_intensity'] ?? 0.35)
                ),
            ],
            'nle_project' => [
                'module'  => $module,
                'project' => SovereignVideoJobHandler::sanitiseNleProjectPayload(
                    is_array($payload['project'] ?? null) ? $payload['project'] : []
                ),
            ],
        };
    }

    /**
     * @param array<int, mixed> $rawWords
     * @return array<int, array{word: string, startSec: float, endSec: float}>
     */
    private function normaliseWordArray(array $rawWords): array
    {
        $out = [];
        foreach ($rawWords as $item) {
            if (!is_array($item)) {
                continue;
            }
            $out[] = [
                'word'     => (string) ($item['word'] ?? ''),
                'startSec' => (float) ($item['startSec'] ?? 0.0),
                'endSec'   => (float) ($item['endSec'] ?? 0.5),
            ];
        }
        return $out;
    }
}
