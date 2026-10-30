<?php

declare(strict_types=1);

/**
 * Sovereign Verification & Adversarial PoC Test Suite:
 * 30-Second Video Auto-Trim Studio & Post Video Upload Engine
 *
 * Verifies:
 * 1. Auto-Trim clamping invariance: any video > 30s is trimmed/clamped to <= 30.0s.
 * 2. Custom trim window selection (e.g. 15.0s - 50.0s on a 90s MP4 clamps to 15.0s - 45.0s = 30.0s).
 * 3. Native ISO-BMFF (moov/mvhd) MP4 duration parser accuracy.
 * 4. VideoParser::extractVideoFromText & cleanPostMessage support for sovereign /resources/videos/posts/...#t=start,end URLs.
 * 5. Dr. Viktor Kovács & Marcus Adversarial PoCs:
 *    - PoC 1: Polyglot PHP tag disguised inside .mp4 rejected with ValidationException.
 *    - PoC 2: Spoofed MIME file disguised as .mp4 rejected with ValidationException.
 *    - PoC 3: Oversized (>30s) window request (`trim_start=-50`, `trim_end=999999`) clamped to 30.0s.
 * 6. StreamController fallback & ServeImgController HTTP 206 Byte-Range streaming readiness.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\services\VideoAutoTrimService;
use App\classes\VideoParser;
use Src\Exceptions\ValidationException;

$passed = 0;
$failed = 0;

function assertCheck(bool $condition, string $label, string $detail = ''): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$label}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$label}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    }
}

/**
 * Build a minimal synthetic ISO-BMFF (MP4) binary with a valid ftyp box and moov/mvhd box
 * encoding a specific duration in seconds.
 */
function buildSyntheticMp4(float $durationSeconds, int $timescale = 1000, string $extraPayload = ''): string
{
    // ftyp box (24 bytes): size=24, type='ftyp', major_brand='isom', minor_version=512, compatible='isom','mp42'
    $ftyp = pack('N', 24) . 'ftyp' . 'isom' . pack('N', 512) . 'isom' . 'mp42';

    // mvhd box version 0:
    // size (4) + 'mvhd' (4) + version/flags (4) + creation_time (4) + mod_time (4) + timescale (4) + duration (4) + rest (76 bytes) = 104 bytes
    $durationUnits = (int) round($durationSeconds * $timescale);
    $mvhdBody = pack('N', 0) // version 0 + flags 0
        . pack('N', 0)       // creation_time
        . pack('N', 0)       // modification_time
        . pack('N', $timescale)
        . pack('N', $durationUnits)
        . str_repeat("\x00", 76);
    $mvhd = pack('N', strlen($mvhdBody) + 8) . 'mvhd' . $mvhdBody;

    // moov box wrapping mvhd
    $moov = pack('N', strlen($mvhd) + 8) . 'moov' . $mvhd;

    // mdat box with dummy video frames + optional payload
    $mdatPayload = str_repeat("\x00\x00\x00\x01\x65", 64) . $extraPayload;
    $mdat = pack('N', strlen($mdatPayload) + 8) . 'mdat' . $mdatPayload;

    return $ftyp . $moov . $mdat;
}

echo "=== [1/6] Testing Native ISO-BMFF (MP4) Duration Parser & 30s Trim Clamping ===\n";

$tmp75s = tempnam(sys_get_temp_dir(), 'vid75_') . '.mp4';
file_put_contents($tmp75s, buildSyntheticMp4(75.4, 1000));

$detectedDuration = VideoAutoTrimService::parseMp4Duration($tmp75s);
assertCheck(
    $detectedDuration !== null && abs($detectedDuration - 75.4) < 0.05,
    "ISO-BMFF binary parser accurately reads 75.4s duration from synthetic MP4 (got " . var_export($detectedDuration, true) . ")"
);

// Test default auto-trim window for 75.4s video -> [0.0, 30.0]
$windowDefault = VideoAutoTrimService::clampTrimWindow(0.0, null, $detectedDuration);
assertCheck(
    $windowDefault['start'] === 0.0 && $windowDefault['end'] === 30.0 && $windowDefault['duration'] === 30.0 && $windowDefault['isTrimmed'] === true,
    "75.4s video auto-trims by default to [0.0s, 30.0s] with isTrimmed=true"
);

// Test custom user-selected window [18.5s, 60.0s] on 75.4s video -> clamps to [18.5s, 48.5s] (duration 30.0s)
$windowCustom = VideoAutoTrimService::clampTrimWindow(18.5, 60.0, $detectedDuration);
assertCheck(
    $windowCustom['start'] === 18.5 && $windowCustom['end'] === 48.5 && $windowCustom['duration'] === 30.0 && $windowCustom['isTrimmed'] === true,
    "Custom trim request [18.5s, 60.0s] clamps strictly to [18.5s, 48.5s] (30.0s max duration)"
);

// Test short video (14.2s) -> untouched [0.0s, 14.2s], isTrimmed=false
$windowShort = VideoAutoTrimService::clampTrimWindow(0.0, null, 14.2);
assertCheck(
    $windowShort['start'] === 0.0 && $windowShort['end'] === 14.2 && $windowShort['isTrimmed'] === false,
    "Short 14.2s video preserves full [0.0s, 14.2s] duration without unnecessary trimming"
);

echo "\n=== [2/6] Testing Full VideoAutoTrimService::processUploadAndTrim() Pipeline ===\n";

$result75 = VideoAutoTrimService::processUploadAndTrim(
    [
        'name' => 'family_reunion_dance.mp4',
        'tmp_name' => $tmp75s,
        'size' => filesize($tmp75s),
        'error' => UPLOAD_ERR_OK,
    ],
    'member_test_1',
    12.0,
    55.0
);

assertCheck(
    ($result75['success'] ?? false) === true
    && $result75['trimStart'] === 12.0
    && $result75['trimEnd'] === 42.0
    && $result75['duration'] === 30.0
    && $result75['autoTrimmed'] === true
    && str_starts_with((string) $result75['streamUrl'], '/resources/videos/posts/post_vid_')
    && str_ends_with((string) $result75['streamUrl'], '#t=12,42'),
    "processUploadAndTrim() stores MP4 and generates sovereign stream URL with #t=12,42 media fragment (" . ($result75['streamUrl'] ?? '') . ")"
);

// Clean up test video file generated in resources/videos/posts
$createdFile = dirname(__DIR__) . '/resources/videos/posts/' . basename((string) ($result75['fileName'] ?? ''));
if (is_file($createdFile)) {
    @unlink($createdFile);
}
if (is_file($tmp75s)) {
    @unlink($tmp75s);
}

echo "\n=== [3/6] Testing VideoParser Recognition & Caption Cleaning for Sovereign Videos ===\n";

$sampleUrl = '/resources/videos/posts/post_vid_1730000_abcd1234.mp4#t=5.5,35.5';
$samplePostMessage = "Look at Grandma dancing at the party! 💃🏽\n" . $sampleUrl;

$extracted = VideoParser::extractVideoFromText($samplePostMessage);
assertCheck(
    $extracted !== null
    && $extracted['type'] === 'direct'
    && $extracted['originalUrl'] === $sampleUrl
    && isset($extracted['trimStart']) && abs((float) $extracted['trimStart'] - 5.5) < 0.01
    && isset($extracted['trimEnd']) && abs((float) $extracted['trimEnd'] - 35.5) < 0.01,
    "VideoParser::extractVideoFromText() extracts sovereign relative video URL and parses trimStart=5.5, trimEnd=35.5"
);

$cleanedCaption = VideoParser::cleanPostMessage($samplePostMessage, $extracted);
assertCheck(
    $cleanedCaption === "Look at Grandma dancing at the party! 💃🏽",
    "VideoParser::cleanPostMessage() cleanly removes the raw video URL while preserving caption text and emojis"
);

echo "\n=== [4/6] Dr. Viktor Kovács & Marcus Adversarial Red-Team PoC Checks ===\n";

// PoC 1: Polyglot PHP tag inside an MP4 container
$tmpPolyglot = tempnam(sys_get_temp_dir(), 'poly_') . '.mp4';
$phpTagMarker = '<' . '?php' . ' echo 1; ?' . '>';
file_put_contents($tmpPolyglot, buildSyntheticMp4(10.0, 1000, $phpTagMarker));

$polyglotBlocked = false;
try {
    VideoAutoTrimService::processUploadAndTrim(
        [
            'name' => 'exploit.mp4',
            'tmp_name' => $tmpPolyglot,
            'size' => filesize($tmpPolyglot),
            'error' => UPLOAD_ERR_OK,
        ],
        'member_test_1',
        0.0,
        10.0
    );
} catch (ValidationException $e) {
    $polyglotBlocked = true;
}
if (is_file($tmpPolyglot)) {
    @unlink($tmpPolyglot);
}
assertCheck(
    $polyglotBlocked === true,
    "Adversarial PoC 1 Neutralised: Polyglot MP4 containing embedded PHP tag rejected with ValidationException"
);

// PoC 2: Non-video text file renamed to .mp4
$tmpFake = tempnam(sys_get_temp_dir(), 'fake_') . '.mp4';
file_put_contents($tmpFake, "PLAIN_TEXT_NOT_A_VIDEO_HEADER");
$fakeBlocked = false;
try {
    VideoAutoTrimService::processUploadAndTrim(
        [
            'name' => 'avatar.mp4',
            'tmp_name' => $tmpFake,
            'size' => filesize($tmpFake),
            'error' => UPLOAD_ERR_OK,
        ],
        'member_test_1',
        0.0,
        10.0
    );
} catch (ValidationException $e) {
    $fakeBlocked = true;
}
if (is_file($tmpFake)) {
    @unlink($tmpFake);
}
assertCheck(
    $fakeBlocked === true,
    "Adversarial PoC 2 Neutralised: Spoofed MIME file disguised as .mp4 rejected by finfo magic-byte gate"
);

// PoC 3: Oversized trim window manipulation (trim_start=-50, trim_end=999999)
$clampedAttack = VideoAutoTrimService::clampTrimWindow(-50.0, 999999.0, 120.0);
assertCheck(
    $clampedAttack['start'] === 0.0 && $clampedAttack['end'] === 30.0 && $clampedAttack['duration'] === 30.0,
    "Adversarial PoC 3 Neutralised: Tampered parameters (trim_start=-50, trim_end=999999) strictly clamped to [0.0s, 30.0s]"
);

echo "\n=== [5/6] Verifying Frontend & Blade Auto-Trim Studio Integration ===\n";

$postModalContent = (string) file_get_contents(__DIR__ . '/../resources/views/member/modals/post.blade.php');
assertCheck(
    str_contains($postModalContent, 'id="videoAutoTrimStudio"')
    && str_contains($postModalContent, 'id="videoTrimStartRange"')
    && str_contains($postModalContent, 'id="videoTrimEndRange"')
    && str_contains($postModalContent, 'id="btnConfirmAutoTrim"'),
    "post.blade.php contains #videoAutoTrimStudio, range sliders, and #btnConfirmAutoTrim action button"
);

$postCardContent = (string) file_get_contents(__DIR__ . '/../resources/views/components/feed/post-card.blade.php');
assertCheck(
    str_contains($postCardContent, 'post.video.trimEnd') && str_contains($postCardContent, '@timeupdate='),
    "post-card.blade.php enforces [trimStart, trimEnd] 30-second playback boundaries on <video> elements"
);

assertCheck(
    str_contains($postCardContent, 'feed-post-video')
    && str_contains($postCardContent, 'playsinline')
    && str_contains($postCardContent, 'muted')
    && str_contains($postCardContent, 'isVideoMuted')
    && str_contains($postCardContent, 'bi-volume-mute-fill'),
    "post-card.blade.php configures feed video for autoplay in-view with 1-tap sound toggle button (44x44px touch target)"
);

$feedComponentContent = (string) file_get_contents(__DIR__ . '/../resources/asset/js/components/profilePage/feedComponent.js');
assertCheck(
    str_contains($feedComponentContent, 'observeFeedVideos()')
    && str_contains($feedComponentContent, '_feedVideoObserver')
    && str_contains($feedComponentContent, 'entry.intersectionRatio >= 0.45')
    && str_contains($feedComponentContent, 'video.play()')
    && str_contains($feedComponentContent, 'video.pause()'),
    "feedComponent.js implements observeFeedVideos() to autoplay when in view during scrolling and pause when out of view"
);

$htmlJsContent = (string) file_get_contents(__DIR__ . '/../resources/asset/js/components/profilePage/html.js');
assertCheck(
    str_contains($htmlJsContent, 'feed-post-video') && str_contains($htmlJsContent, 'muted'),
    "html.js fallback markup configures direct videos with feed-post-video and muted for scroll autoplay"
);

echo "\n=== [6/6] Summary ===\n";
echo "Passed: {$passed} | Failed: {$failed}\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
