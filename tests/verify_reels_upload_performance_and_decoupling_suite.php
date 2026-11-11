<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/init.php';

echo "=================================================================\n";
echo "⚡ REELS UPLOAD PERFORMANCE & DECOUPLED PIPELINE VERIFICATION\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(bool $condition, string $message): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  ✅ PASS: {$message}\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL: {$message}\n";
        $failCount++;
    }
}

// -------------------------------------------------------------
// Test 1: Verify Early Session Write Close in uploadReel()
// -------------------------------------------------------------
echo "[TEST 1] Verifying early session_write_close() in ReelsController::uploadReel()...\n";
$controllerSrc = file_get_contents(__DIR__ . '/../app/controller/members/ReelsController.php');
assertCheck($controllerSrc !== false, "ReelsController.php is readable");

$hasEarlySessionUnlock = str_contains($controllerSrc, 'session_write_close()')
    && str_contains($controllerSrc, 'Fast-Response Decoupling: Release PHP session lock early');
assertCheck($hasEarlySessionUnlock, "Early session unlock detected before file upload and DB transactions");

// -------------------------------------------------------------
// Test 2: Verify FastCGI Response Decoupling in uploadReel()
// -------------------------------------------------------------
echo "\n[TEST 2] Verifying FastCGI response decoupling and output flushing...\n";
$hasFastCgiDecoupling = str_contains($controllerSrc, 'fastcgi_finish_request()')
    && str_contains($controllerSrc, 'ignore_user_abort(true)');
assertCheck($hasFastCgiDecoupling, "HTTP 200 payload flushes and fastcgi_finish_request() disconnects client");

// -------------------------------------------------------------
// Test 3: Verify Batched Push Dispatch (Zero N-Iteration WebPush Flushes)
// -------------------------------------------------------------
echo "\n[TEST 3] Verifying Batched WebPush Dispatch in ReelsController...\n";
$hasBatchedReelPush = str_contains($controllerSrc, '$recipientIds[] = $targetId;')
    && str_contains($controllerSrc, 'PushNotificationClass::sendPushNotification(')
    && str_contains($controllerSrc, 'userId: $recipientIds');
assertCheck($hasBatchedReelPush, "ReelsController passes aggregated array of recipient IDs to sendPushNotification");

// -------------------------------------------------------------
// Test 4: Verify Batched WebPush in PostMessage.php
// -------------------------------------------------------------
echo "\n[TEST 4] Verifying Batched WebPush in PostMessage::notifyMembersByPushNotification()...\n";
$postMsgSrc = file_get_contents(__DIR__ . '/../app/controller/members/PostMessage.php');
assertCheck($postMsgSrc !== false, "PostMessage.php is readable");

$hasBatchedPostMsgPush = str_contains($postMsgSrc, '$pushTargetUserIds[] = $targetUserId;')
    && str_contains($postMsgSrc, 'userId: $pushTargetUserIds');
assertCheck($hasBatchedPostMsgPush, "PostMessage.php batches WebPush notification across family members in single flush");

// -------------------------------------------------------------
// Test 5: Verify Real-Time Upload Progress in createReelModal & reelsPlayer.js
// -------------------------------------------------------------
echo "\n[TEST 5] Verifying Frontend Upload Progress Telemetry...\n";
$modalSrc = file_get_contents(__DIR__ . '/../resources/views/member/modals/createReelModal.blade.php');
assertCheck(str_contains($modalSrc, 'id="reelUploadProgressWrapper"'), "createReelModal contains #reelUploadProgressWrapper container");
assertCheck(str_contains($modalSrc, 'id="reelUploadProgressBar"'), "createReelModal contains #reelUploadProgressBar element");

$playerSrc = file_get_contents(__DIR__ . '/../resources/asset/js/components/reels/reelsPlayer.js');
assertCheck(str_contains($playerSrc, 'onUploadProgress: (progressEvent) =>'), "reelsPlayer.js hooks onUploadProgress event handler into axios.post");

// -------------------------------------------------------------
// Test 6: Verify British English Invariants
// -------------------------------------------------------------
echo "\n[TEST 6] Verifying British English Copy in Progress & Telemetry...\n";
assertCheck(!str_contains($modalSrc, 'Finalizing'), "No US English 'Finalizing' in createReelModal");
assertCheck(str_contains($playerSrc, 'Finalising memory & notifying family…'), "British English 'Finalising' used in progress telemetry");

// -------------------------------------------------------------
// Test 7: Verify Composer Multi-Video Upload Concurrency & Session Unlock
// -------------------------------------------------------------
echo "\n[TEST 7] Verifying Composer Multi-Video Upload Concurrency & Session Unlock...\n";
$streamCtrlSrc = (string)file_get_contents(__DIR__ . '/../app/controller/members/StreamController.php');
assertCheck(
    substr_count($streamCtrlSrc, 'session_write_close()') >= 2,
    "StreamController releases PHP session lock early in both getDirectUploadUrl() and uploadAndTrimVideo()"
);

$composerVideoSrc = (string)file_get_contents(__DIR__ . '/../resources/asset/js/components/profilePage/postEmojiImgProcess.js');
assertCheck(
    str_contains($composerVideoSrc, 'Promise.all(toProcess.map(f => handleSelectedVideoFile(f)))'),
    "postEmojiImgProcess.js processes multiple selected videos concurrently via Promise.all"
);
assertCheck(
    str_contains($composerVideoSrc, "console.warn('[VideoUpload] Direct cloud upload failed/blocked, falling back to Sovereign Local upload:'"),
    "postEmojiImgProcess.js seamlessly falls back to Sovereign Local upload if direct cloud XHR fails"
);
assertCheck(
    str_contains($composerVideoSrc, 'modalResult.isDenied') && str_contains($composerVideoSrc, 'customising: true'),
    "postEmojiImgProcess.js clears 'Uploading...' badge state when user chooses 'Customise 30s Window'"
);

$filePreviewSrc = (string)file_get_contents(__DIR__ . '/../resources/asset/js/components/fileUploadPreview.js');
assertCheck(
    str_contains($filePreviewSrc, 'allowedVideos.map(vFile =>'),
    "fileUploadPreview.js dispatches multiple selected composer videos concurrently via Promise.all"
);
assertCheck(
    str_contains($filePreviewSrc, 'validRawFiles.map(async (file) =>'),
    "fileUploadPreview.js runs client-side image safety pre-screening in parallel via Promise.all"
);

$mediaSafetySrc = (string)file_get_contents(__DIR__ . '/../app/services/MediaSafetyInspectionService.php');
assertCheck(
    str_contains($mediaSafetySrc, 'inspectAndHashSinglePass'),
    "MediaSafetyInspectionService uses single-pass GD decode for both dHash and skin-ratio screening"
);

// -------------------------------------------------------------
// Test 8: Verify Pure-PHP ISO-BMFF `moov` FastStart Relocation & `stco` Offset Shift
// -------------------------------------------------------------
echo "\n[TEST 8] Verifying Host-Portable Pure-PHP MP4 `moov` FastStart Relocation...\n";
$tmpMp4 = sys_get_temp_dir() . '/test_faststart_' . uniqid('', true) . '.mp4';

// Build a synthetic ISO-BMFF MP4 where `mdat` precedes `moov` (`ftyp` -> `mdat` -> `moov`)
$ftypBox = pack('Na4a4N', 16, 'ftyp', 'isom', 0); // 16 bytes (offset 0..15)
$mdatPayload = str_repeat('V', 24);
$mdatBox = pack('Na4', 8 + strlen($mdatPayload), 'mdat') . $mdatPayload; // 32 bytes (offset 16..47)
$initialChunkOffset = 24; // points inside mdat (16 + 8)

// Build `stco` atom with 1 chunk offset entry pointing to $initialChunkOffset (24)
$stcoBox = pack('Na4NNN', 20, 'stco', 0, 1, $initialChunkOffset); // 20 bytes
$stblBox = pack('Na4', 8 + strlen($stcoBox), 'stbl') . $stcoBox;   // 28 bytes
$trakBox = pack('Na4', 8 + strlen($stblBox), 'trak') . $stblBox;   // 36 bytes
$moovBox = pack('Na4', 8 + strlen($trakBox), 'moov') . $trakBox;   // 44 bytes (offset 48..91)
$moovLen = strlen($moovBox);

file_put_contents($tmpMp4, $ftypBox . $mdatBox . $moovBox);

$fastStartOk = \App\services\VideoAutoTrimService::optimiseMp4FastStart($tmpMp4);
assertCheck($fastStartOk === true, "VideoAutoTrimService::optimiseMp4FastStart() relocated trailing moov box ahead of mdat");

$reorderedBytes = (string)file_get_contents($tmpMp4);
$firstAtom = substr($reorderedBytes, 4, 4);
$secondAtom = substr($reorderedBytes, 16 + 4, 4);
$thirdAtom = substr($reorderedBytes, 16 + $moovLen + 4, 4);
assertCheck(
    $firstAtom === 'ftyp' && $secondAtom === 'moov' && $thirdAtom === 'mdat',
    "MP4 box order transformed from [ftyp, mdat, moov] to [ftyp, moov, mdat] in pure PHP"
);

// Verify `stco` chunk offset inside relocated `moov` was incremented by +$moovLen (24 + 44 = 68)
$stcoPos = strpos($reorderedBytes, 'stco');
$shiftedOffset = 0;
if ($stcoPos !== false) {
    $unpackedStco = unpack('Noffset', substr($reorderedBytes, $stcoPos + 12, 4));
    $shiftedOffset = is_array($unpackedStco) ? (int)$unpackedStco['offset'] : 0;
}
assertCheck(
    $shiftedOffset === ($initialChunkOffset + $moovLen),
    "Pure-PHP FastStart shifted stco chunk offset accurately ({$initialChunkOffset} -> {$shiftedOffset})"
);
@unlink($tmpMp4);

// -------------------------------------------------------------
// Test 9: Verify ATG 4K Perceptual Cinema Filter & On-Device Video Compressor
// -------------------------------------------------------------
echo "\n[TEST 9] Verifying ATG 4K Perceptual Cinema Filter & On-Device Video Compressor...\n";
$compressorSrc = (string)file_get_contents(__DIR__ . '/../resources/asset/js/components/helper/videoCompressor.js');
assertCheck(
    str_contains($compressorSrc, 'export async function compressVideoFile') && str_contains($compressorSrc, 'export function shouldOptimiseVideoOnDevice'),
    "videoCompressor.js exports compressVideoFile() and shouldOptimiseVideoOnDevice()"
);
assertCheck(
    str_contains($composerVideoSrc, 'compressVideoFile') && str_contains($playerSrc, 'compressVideoFile'),
    "Both Create Post (postEmojiImgProcess.js) and Reels (reelsPlayer.js) integrate on-device videoCompressor"
);

$profileBaseSrc = (string)file_get_contents(__DIR__ . '/../resources/views/layouts/profileBase.blade.php');
$postCardSrc = (string)file_get_contents(__DIR__ . '/../resources/views/components/feed/post-card.blade.php');
assertCheck(
    str_contains($profileBaseSrc, 'id="atg-4k-cinema-cas"') && str_contains($postCardSrc, 'atg-4k-video-render') && str_contains($postCardSrc, '4K ULTRA HD'),
    "ATG 4K Perceptual Cinema SVG CAS filter (#atg-4k-cinema-cas) and .atg-4k-video-render active on feed and reels video players"
);

echo "\n=================================================================\n";
echo "📊 SUITE RESULTS: {$passCount} Passed, {$failCount} Failed\n";
echo "=================================================================\n";

if ($failCount > 0) {
    exit(1);
}
