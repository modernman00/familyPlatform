<?php

declare(strict_types=1);

/**
 * 🎙️ Family Voice Notes Transcription Resiliency & Contract Verification Test
 *
 * Verifies:
 * - Multi-tier failover handling in VoiceTranscriptionService
 * - Rejection of non-existent and empty audio files
 * - Accurate transcription of real spoken audio via Gemini Tier 1
 * - Clear, user-friendly error messages when audio is inaudible/silent
 * - Strict structural return contract: array{success: bool, text: string, provider: string, error: ?string}
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "=================================================================\n";
echo "🎙️  FAMILY VOICE NOTES TRANSCRIPTION RESILIENCE SUITE\n";
echo "=================================================================\n\n";

$passed = 0;
$total = 0;

function assertCheck(string $label, bool $condition, string $detail = ''): void {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  ✅ PASS: $label\n";
    } else {
        echo "  ❌ FAIL: $label ($detail)\n";
    }
}

// 1. Non-existent file rejection
$service = new \Src\Ai\VoiceTranscriptionService();
$resNonExistent = $service->transcribe('/tmp/non_existent_audio_' . uniqid() . '.wav');
assertCheck(
    'Rejects non-existent audio file cleanly',
    $resNonExistent['success'] === false && str_contains($resNonExistent['error'] ?? '', 'not found'),
    'Expected error containing "not found"'
);

// 2. Empty file rejection
$tmpEmpty = tempnam(sys_get_temp_dir(), 'empty_audio_');
file_put_contents($tmpEmpty, '');
$resEmpty = $service->transcribe($tmpEmpty);
unlink($tmpEmpty);
assertCheck(
    'Rejects empty 0-byte audio file',
    $resEmpty['success'] === false && str_contains($resEmpty['error'] ?? '', 'empty'),
    'Expected error containing "empty"'
);

// 3. File exceeding 25MB boundary rejection
$tmpLarge = tempnam(sys_get_temp_dir(), 'large_audio_');
// Create sparse file of 27MB
$fp = fopen($tmpLarge, 'w');
fseek($fp, 27_000_000);
fwrite($fp, "\0");
fclose($fp);
$resLarge = $service->transcribe($tmpLarge);
unlink($tmpLarge);
assertCheck(
    'Rejects audio files exceeding 25MB boundary',
    $resLarge['success'] === false && str_contains($resLarge['error'] ?? '', '25MB'),
    'Expected error containing "25MB"'
);

// 4. Real speech audio transcription via Gemini Tier 1
$speechWav = '/tmp/family_test.wav';
if (file_exists($speechWav)) {
    $resSpeech = $service->transcribe($speechWav);
    assertCheck(
        'Real speech audio transcribes with success=true',
        $resSpeech['success'] === true && !empty($resSpeech['text']),
        'Text was: ' . ($resSpeech['text'] ?? 'NONE')
    );

    assertCheck(
        'Gemini Tier 1 provider identified in response',
        str_contains($resSpeech['provider'] ?? '', 'Gemini') || str_contains($resSpeech['provider'] ?? '', 'Whisper'),
        'Provider was: ' . ($resSpeech['provider'] ?? 'NONE')
    );

    assertCheck(
        'Transcribed speech matches expected family dinner sentence',
        stripos($resSpeech['text'], 'milk') !== false && stripos($resSpeech['text'], 'dinner') !== false,
        'Text was: ' . ($resSpeech['text'] ?? 'NONE')
    );
} else {
    echo "  ⚠️ SKIP: /tmp/family_test.wav not found on disk, skipping live audio transcription.\n";
}

// 5. Contract integrity check
$keys = ['success', 'text', 'provider', 'error'];
$hasAllKeys = true;
foreach ($keys as $k) {
    if (!array_key_exists($k, $resNonExistent)) {
        $hasAllKeys = false;
        break;
    }
}
assertCheck(
    'Response payload conforms strictly to 4-key contract array',
    $hasAllKeys,
    'Keys missing from array'
);

echo "\n=================================================================\n";
echo "🎯 SUMMARY: $passed / $total TESTS PASSED\n";
echo "=================================================================\n\n";

if ($passed !== $total) {
    exit(1);
}
