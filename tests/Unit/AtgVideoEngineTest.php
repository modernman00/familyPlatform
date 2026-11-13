<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\AtgVideoEngine;
use PHPUnit\Framework\TestCase;
use Src\Exceptions\ValidationException;

class AtgVideoEngineTest extends TestCase
{
    public function testAllTwelveModulesExecuteDeterministically(): void
    {
        $engine = new AtgVideoEngine();
        $uid = 'usr_family_42';

        $m1 = $engine->processModule('text_to_video', ['prompt' => 'Family reunion in Bath. Great joy.', 'duration_sec' => 8], $uid);
        $this->assertSame('text_to_video', $m1['module']);
        $this->assertCount(2, $m1['manifest']['scenes']);

        $m2 = $engine->processModule('image_to_video', ['width' => 1080, 'height' => 1920, 'duration_sec' => 4], $uid);
        $this->assertStringContainsString('zoompan=', $m2['filterGraph']);

        $m3 = $engine->processModule('ai_avatars', ['rms_samples' => [0.02, 0.4, 0.7]], $uid);
        $this->assertCount(3, $m3['timeline']);

        $m4 = $engine->processModule('auto_captions', ['words' => [['word' => 'Hello', 'startSec' => 0.0, 'endSec' => 0.5]]], $uid);
        $this->assertStringContainsString('Hello', $m4['srt']);

        $m5 = $engine->processModule('tts_voiceovers', ['text' => 'Good afternoon everyone', 'profile' => 'serena'], $uid);
        $this->assertSame('en-GB', $m5['descriptor']['accent']);

        $m6 = $engine->processModule('voice_cloning', ['gdpr_article9_consent' => true, 'pitch_ratio' => 1.05], $uid);
        $this->assertSame('gdpr_article_9_verified', $m6['consent']);

        $m7 = $engine->processModule('auto_cut_silence', ['pcm_samples' => array_fill(0, 8000, 0.001), 'sample_rate' => 8000], $uid);
        $this->assertCount(1, $m7['intervals']);

        $m8 = $engine->processModule('background_removal', ['hex_colour' => '00FF00'], $uid);
        $this->assertStringContainsString('chromakey=0x00FF00', $m8['filterGraph']);

        $m9 = $engine->processModule('subject_tracking', ['x' => 20, 'y' => 20, 'box_w' => 100, 'box_h' => 100, 'frame_w' => 1080, 'frame_h' => 1920], $uid);
        $this->assertStringContainsString('drawbox=', $m9['region']['filterGraph']);

        $m10 = $engine->processModule('video_upscaling', ['target_width' => 1920, 'target_height' => 1080], $uid);
        $this->assertStringContainsString('flags=lanczos', $m10['filterGraph']);

        $m11 = $engine->processModule('colour_matching', ['lut_preset' => 'heritage_gold'], $uid);
        $this->assertStringContainsString('colorbalance=', $m11['filterGraph']);

        $m12 = $engine->processModule('audio_noise_reduction', ['hum_hz' => 50], $uid);
        $this->assertStringContainsString('bandreject=f=50', $m12['filterGraph']);

        $m13 = $engine->processModule('nle_project', ['project' => ['aspectRatio' => '16:9', 'tracks' => [['id' => 'V1', 'type' => 'video'], ['id' => 'ADJ1', 'type' => 'adjustment']]]], $uid);
        $this->assertSame('16:9', $m13['project']['aspectRatio']);

        $m14 = $engine->processModule('atmospheric_overlay', ['overlay_id' => 'bonfire_flames'], $uid);
        $this->assertNotEmpty($m14['library']);
        $this->assertStringContainsString('hue=h=25', $m14['filterGraph']);

        $m15 = $engine->processModule('blend_mode', ['blend_mode' => 'add', 'opacity' => 0.85], $uid);
        $this->assertStringContainsString('all_mode=addition', $m15['filterGraph']);

        $m16 = $engine->processModule('feathered_mask', ['mask_type' => 'linear_gradient', 'feather' => 24], $uid);
        $this->assertStringContainsString('geq=', $m16['filterGraph']);

        $m17 = $engine->processModule('adjustment_layer', ['adjustments' => ['exposure' => 0.2, 'highlights' => 0.15, 'tint' => 0.1]], $uid);
        $this->assertStringContainsString('eq=', $m17['filterGraph']);

        $m18 = $engine->processModule('lighting_mood', ['strobe_frequency' => 4.0, 'flicker_intensity' => 0.3, 'glow_preset' => 'warm_glowing_tan', 'glow_intensity' => 0.45], $uid);
        $this->assertStringContainsString('gblur=', $m18['filterGraph']);
    }

    public function testVoiceCloningRejectsMissingGdprArticle9Consent(): void
    {
        $engine = new AtgVideoEngine();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('GDPR Article 9');
        $engine->processModule('voice_cloning', ['gdpr_article9_consent' => false], 'usr_family_42');
    }

    public function testUnsupportedModuleThrowsValidationException(): void
    {
        $engine = new AtgVideoEngine();
        $this->expectException(ValidationException::class);
        $engine->processModule('nonexistent_module', [], 'usr_family_42');
    }
}
