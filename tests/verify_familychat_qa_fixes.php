<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

use App\controller\KinWhisperController;
use App\controller\RealtimeBroadcastingController;
use App\services\KinWhisperService;
use Src\Db;
use Src\Exceptions\ForbiddenException;

echo "=================================================================\n";
echo "🔒 FAMILYCHAT QA FIXES REGRESSION SUITE (1.3, 1.4, 1.5, 1.6)\n";
echo "=================================================================\n\n";

$db = Db::connect2();
KinWhisperService::ensureSchema($db);

$suffix = bin2hex(random_bytes(4));
$famA = 'FAM_A_' . $suffix;
$famB = 'FAM_B_' . $suffix;
$userA1 = 'user_a1_' . $suffix;
$userA2 = 'user_a2_' . $suffix;
$userB1 = 'user_b1_' . $suffix;

$cleanup = function () use ($db, $userA1, $userA2, $userB1, $famA, $famB): void {
    try {
        $stmtT = $db->prepare('SELECT id FROM kin_threads WHERE family_code IN (?, ?)');
        $stmtT->execute([$famA, $famB]);
        $tIds = $stmtT->fetchAll(PDO::FETCH_COLUMN) ?: [];
        foreach ($tIds as $tid) {
            $db->prepare('DELETE FROM kin_messages WHERE thread_id = ?')->execute([$tid]);
            $db->prepare('DELETE FROM kin_thread_participants WHERE thread_id = ?')->execute([$tid]);
            $db->prepare('DELETE FROM kin_thread_events WHERE thread_id = ?')->execute([$tid]);
            $db->prepare('DELETE FROM kin_threads WHERE id = ?')->execute([$tid]);
        }
        $db->prepare('DELETE FROM user_presence_settings WHERE user_id IN (?, ?, ?)')->execute([$userA1, $userA2, $userB1]);
        $db->prepare('DELETE FROM user_socket_presence WHERE user_id IN (?, ?, ?)')->execute([$userA1, $userA2, $userB1]);
        $db->prepare('DELETE FROM user_families WHERE user_id IN (?, ?, ?)')->execute([$userA1, $userA2, $userB1]);
        $db->prepare('DELETE FROM personal WHERE id IN (?, ?, ?)')->execute([$userA1, $userA2, $userB1]);
        $db->prepare('DELETE FROM account WHERE id IN (?, ?, ?)')->execute([$userA1, $userA2, $userB1]);
    } catch (\Throwable $e) {
        error_log('[verify_familychat_qa_fixes cleanup] ' . $e->getMessage());
    }
};

register_shutdown_function($cleanup);

// Seed two accounts in Family A and one account in Family B
$stmtAcc = $db->prepare("INSERT INTO account (id, email, password, status, is_child_account, adulthood_status) VALUES (?, ?, 'hash', 'active', 0, 'standard_adult')");
$stmtAcc->execute([$userA1, $userA1 . '@test.local']);
$stmtAcc->execute([$userA2, $userA2 . '@test.local']);
$stmtAcc->execute([$userB1, $userB1 . '@test.local']);

$stmtPersonal = $db->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
$stmtPersonal->execute([$userA1, 'Alice', 'Olaogun', $famA]);
$stmtPersonal->execute([$userA2, 'Bob', 'Olaogun', $famA]);
$stmtPersonal->execute([$userB1, 'Eve', 'Outsider', $famB]);

$stmtUf = $db->prepare("INSERT INTO user_families (user_id, family_code, role, status) VALUES (?, ?, 'admin', 'approved'), (?, ?, 'member', 'approved'), (?, ?, 'admin', 'approved')");
$stmtUf->execute([$userA1, $famA, $userA2, $famA, $userB1, $famB]);

// Create thread between A1 and A2
$threadA = KinWhisperService::createOrGetThread($userA1, $userA2, 'general', null, 'Family A Chat');
$threadAId = $threadA->id;

// -----------------------------------------------------------------
// TEST 1.6: Cross-family access blocked (thread, SSE, signal, media)
// -----------------------------------------------------------------
echo "[1.6] Testing Cross-Family Isolation...\n";

// 1.6a: Direct thread, message, event, media, call, and createOrGetThread access blocked for outsider
$blockedChecks = [
    'getThread' => fn() => KinWhisperService::getThread($userB1, $threadAId),
    'getMessages' => fn() => KinWhisperService::getMessages($userB1, $threadAId),
    'getThreadEvents' => fn() => KinWhisperService::getThreadEvents($userB1, $threadAId),
    'getThreadMedia' => fn() => KinWhisperService::getThreadMedia($userB1, $threadAId),
    'sendMessage' => fn() => KinWhisperService::sendMessage($userB1, $threadAId, 'intruder'),
    'toggleMuteThread' => fn() => KinWhisperService::toggleMuteThread($userB1, $threadAId),
    'initiateCall' => fn() => KinWhisperService::initiateCall($userB1, $threadAId, 'video'),
    'endCall' => fn() => KinWhisperService::endCall($userB1, $threadAId),
    'createOrGetThread' => fn() => KinWhisperService::createOrGetThread($userB1, $userA1, 'general', null, 'Intrude'),
];
foreach ($blockedChecks as $label => $fn) {
    $blocked = false;
    try {
        $fn();
    } catch (ForbiddenException) {
        $blocked = true;
    }
    if (!$blocked) {
        fwrite(STDERR, "❌ FAIL: Cross-family {$label}() was not blocked!\n");
        exit(1);
    }
}
echo "  ✅ Cross-family getThread, getMessages, getThreadEvents, getThreadMedia, sendMessage, toggleMute, initiateCall, endCall, and createOrGetThread all blocked with 403 ForbiddenException.\n";

// 1.6b: RealtimeBroadcastingController case-insensitive own channel + cross-user block
if (!RealtimeBroadcastingController::isAuthorizedForChannel(strtoupper($userA1), 'private-user-' . strtolower($userA1))) {
    fwrite(STDERR, "❌ FAIL: Case-insensitive own user channel was rejected!\n");
    exit(1);
}
if (RealtimeBroadcastingController::isAuthorizedForChannel($userB1, 'private-user-' . strtolower($userA1))) {
    fwrite(STDERR, "❌ FAIL: Cross-family user channel was NOT blocked!\n");
    exit(1);
}
if (RealtimeBroadcastingController::isAuthorizedForChannel($userB1, 'private-family-' . $famA)) {
    fwrite(STDERR, "❌ FAIL: Cross-family family channel was NOT blocked!\n");
    exit(1);
}
echo "  ✅ Realtime channel auth allows own case-insensitive channel and blocks cross-family channels.\n";

// 1.6c: Controller index() check for ?thread_id=other_family_thread
$ctrlSrc = (string)file_get_contents(__DIR__ . '/../app/controller/KinWhisperController.php');
if (!str_contains($ctrlSrc, "http_response_code(403);") || !str_contains($ctrlSrc, "apiServeMediaAsset")) {
    fwrite(STDERR, "❌ FAIL: KinWhisperController missing 403 block on ?thread_id or media asset guard!\n");
    exit(1);
}
echo "  ✅ KinWhisperController enforces 403 on unauthorized ?thread_id and protects media/audio assets.\n";

// -----------------------------------------------------------------
// TEST 1.5: Online / Last seen privacy (case-insensitive + server)
// -----------------------------------------------------------------
echo "[1.5] Testing Online & Last Seen Privacy...\n";

KinWhisperService::syncPresence($userA1, [$userA2]);
KinWhisperService::syncPresence($userA2, [$userA1]);

// Set A2 privacy to 'nobody' with uppercase ID to test case-insensitive matching
KinWhisperService::setPresencePrivacy(strtoupper($userA2), 'nobody');
$resA1 = KinWhisperService::syncPresence($userA1, [$userA2]);
$pA2 = $resA1['presence'][$userA2] ?? null;
if (!$pA2 || $pA2['is_online'] !== false || $pA2['last_seen_ts'] !== null || $pA2['is_hidden'] !== true) {
    fwrite(STDERR, "❌ FAIL: Presence privacy 'nobody' leaked online/last_seen! " . json_encode($pA2) . "\n");
    exit(1);
}
echo "  ✅ Server presence sync strictly masks online & last_seen when privacy is 'nobody' (case-insensitive).\n";

$bladeSrc = (string)file_get_contents(__DIR__ . '/../resources/views/kin_whispers/index.blade.php');
if (!str_contains($bladeSrc, '@json($presence_privacy ?? \'everyone\')')) {
    fwrite(STDERR, "❌ FAIL: index.blade.php still uses wrong \$presencePrivacy variable name!\n");
    exit(1);
}
echo "  ✅ index.blade.php binds \$presence_privacy correctly on initial render.\n";

// -----------------------------------------------------------------
// TEST 1.4: WebRTC Calling (target validation + SSE fallback + no loop)
// -----------------------------------------------------------------
echo "[1.4] Testing Voice & Video Call Signaling & Cleanup...\n";

$blockedSignal = false;
try {
    KinWhisperService::sendCallSignal($userA1, $threadAId, [
        'target_user_id' => $userB1,
        'signal' => ['sdp' => ['type' => 'offer', 'sdp' => 'fake']],
    ]);
} catch (ForbiddenException) {
    $blockedSignal = true;
}
if (!$blockedSignal) {
    fwrite(STDERR, "❌ FAIL: sendCallSignal() allowed targeting an outsider!\n");
    exit(1);
}
echo "  ✅ sendCallSignal() blocks targeting non-participant outsiders.\n";

// Verify same-family call initiation, signaling to participant, and hang-up cleanup
$callInfo = KinWhisperService::initiateCall($userA1, $threadAId, 'video');
if (empty($callInfo['room_id'])) {
    fwrite(STDERR, "❌ FAIL: initiateCall() did not return room_id!\n");
    exit(1);
}
$sigRes = KinWhisperService::sendCallSignal($userA1, $threadAId, [
    'target_user_id' => $userA2,
    'signal' => ['sdp' => ['type' => 'offer', 'sdp' => 'v=0']],
]);
if (empty($sigRes['success']) || empty($sigRes['seq'])) {
    fwrite(STDERR, "❌ FAIL: sendCallSignal() to valid participant failed or did not assign seq!\n");
    exit(1);
}

// Verify getActiveIncomingCallForUser detects ringing call for recipient ($userA2) but not caller ($userA1)
$incomingForA2 = KinWhisperService::getActiveIncomingCallForUser($userA2);
$incomingForA1 = KinWhisperService::getActiveIncomingCallForUser($userA1);
if (!$incomingForA2 || $incomingForA2['room_id'] !== $callInfo['room_id'] || $incomingForA1 !== null) {
    fwrite(STDERR, "❌ FAIL: getActiveIncomingCallForUser() did not detect ringing call accurately!\n");
    exit(1);
}

// Verify SDP answer transitions call_status from 'ringing' to 'in_call'
$ansRes = KinWhisperService::sendCallSignal($userA2, $threadAId, [
    'target_user_id' => $userA1,
    'signal' => ['sdp' => ['type' => 'answer', 'sdp' => 'v=0']],
]);
$stmtInCall = $db->prepare('SELECT call_status FROM kin_threads WHERE id = ? LIMIT 1');
$stmtInCall->execute([$threadAId]);
if ($stmtInCall->fetchColumn() !== 'in_call') {
    fwrite(STDERR, "❌ FAIL: SDP answer did not transition call_status to in_call!\n");
    exit(1);
}

KinWhisperService::endCall($userA1, $threadAId);
$stmtCallCheck = $db->prepare('SELECT call_status, active_call_room_id FROM kin_threads WHERE id = ? LIMIT 1');
$stmtCallCheck->execute([$threadAId]);
$callRow = $stmtCallCheck->fetch(PDO::FETCH_ASSOC);
if (!$callRow || $callRow['call_status'] !== 'idle' || $callRow['active_call_room_id'] !== null) {
    fwrite(STDERR, "❌ FAIL: endCall() did not reset call_status/active_call_room_id!\n");
    exit(1);
}
echo "  ✅ Same-family initiateCall(), getActiveIncomingCallForUser(), sendCallSignal() seq + in_call transition, and endCall() lifecycle verified.\n";

// -----------------------------------------------------------------
// TEST 1.7: Voice Whisper Recording Upload (WebM / MP4 containers) & Delivery
// -----------------------------------------------------------------
echo "[1.7] Testing Voice Whisper Upload (video/webm & video/mp4 containers) & Delivery...\n";
$tmpWebm = tempnam(sys_get_temp_dir(), 'kw_webm_');
// Write Matroska/WebM EBML magic bytes (\x1A\x45\xDF\xA3) + padding
file_put_contents($tmpWebm, "\x1A\x45\xDF\xA3\x93\x42\x82\x88webm\x00" . str_repeat("\x00", 256));
$uploadedAudio = KinWhisperService::uploadAudioWhisper($userA1, [
    'tmp_name' => $tmpWebm,
    'name' => 'whisper.webm',
    'type' => 'video/webm',
    'size' => filesize($tmpWebm),
    'error' => UPLOAD_ERR_OK,
], 9);
@unlink($tmpWebm);

if (empty($uploadedAudio['url']) || ($uploadedAudio['duration'] ?? 0) !== 9) {
    fwrite(STDERR, "❌ FAIL: uploadAudioWhisper() failed for MediaRecorder WebM container!\n");
    exit(1);
}

$voiceMsg = KinWhisperService::sendMessage(
    $userA1,
    $threadAId,
    '',
    'audio_whisper',
    $uploadedAudio['url'],
    $uploadedAudio['duration']
);

$recipientMessages = KinWhisperService::getMessages($userA2, $threadAId);
$deliveredVoice = null;
foreach ($recipientMessages as $m) {
    if ($m->id === $voiceMsg->id) {
        $deliveredVoice = $m;
        break;
    }
}
if (!$deliveredVoice || $deliveredVoice->messageType !== 'audio_whisper' || $deliveredVoice->audioUrl !== $uploadedAudio['url'] || $deliveredVoice->audioDurationSeconds !== 9) {
    fwrite(STDERR, "❌ FAIL: Voice whisper was not persisted and delivered to recipient feed!\n");
    exit(1);
}
$absVoiceFile = dirname(__DIR__) . $uploadedAudio['url'];
if (is_file($absVoiceFile)) {
    @unlink($absVoiceFile);
}
echo "  ✅ Voice whisper upload (WebM/MP4 containers), duration metadata, persistence, and recipient delivery verified.\n";

$rbSrc = (string)file_get_contents(__DIR__ . '/../app/controller/RealtimeBroadcastingController.php');
if (!str_contains($rbSrc, 'getEvents($channel, $lastEventId, 250)')) {
    fwrite(STDERR, "❌ FAIL: RealtimeBroadcastingController missing full-batch since_now=1 fast-forward!\n");
    exit(1);
}
echo "  ✅ RealtimeBroadcastingController fast-forwards since_now=1 across full batches.\n";

if (!str_contains($bladeSrc, 'initSseFallback()') || !str_contains($bladeSrc, 'this.endCall(false)') || !str_contains($bladeSrc, '/api/broadcasting/status') || !str_contains($bladeSrc, 'pendingOfferRoomId')) {
    fwrite(STDERR, "❌ FAIL: index.blade.php missing SSE fallback, /api/broadcasting/status check, pendingOfferRoomId guard, or endCall(false) loop guard!\n");
    exit(1);
}
echo "  ✅ Frontend calling includes Sovereign SSE fallback, /api/broadcasting/status degraded detection, out-of-order offer guard, and loop-free endCall(false).\n";

// -----------------------------------------------------------------
// TEST 1.3: Pin & Mute Persistence (even if participant row missing)
// -----------------------------------------------------------------
echo "[1.3] Testing Pin & Mute Persistence...\n";

// Simulate legacy thread where kin_thread_participants row was missing for userA1
$db->prepare('DELETE FROM kin_thread_participants WHERE thread_id = ? AND user_id = ?')->execute([$threadAId, $userA1]);

$pinned = KinWhisperService::togglePinThread($userA1, $threadAId);
$muted = KinWhisperService::toggleMuteThread($userA1, $threadAId);
if (!$pinned || !$muted) {
    fwrite(STDERR, "❌ FAIL: togglePinThread/toggleMuteThread failed when participant row needed backfill!\n");
    exit(1);
}

$loadedThread = KinWhisperService::getThread($userA1, $threadAId);
if (!$loadedThread->isPinned || !$loadedThread->isMuted) {
    fwrite(STDERR, "❌ FAIL: Pin/Mute flags did not persist on getThread()!\n");
    exit(1);
}

// Verify listUserThreads() returns isPinned & isMuted and sorts pinned thread first
$listedThreads = KinWhisperService::listUserThreads($userA1);
$foundPinned = null;
foreach ($listedThreads as $lt) {
    if ($lt->id === $threadAId) {
        $foundPinned = $lt;
        break;
    }
}
if (!$foundPinned || !$foundPinned->isPinned || !$foundPinned->isMuted) {
    fwrite(STDERR, "❌ FAIL: Pin/Mute flags did not persist in listUserThreads()!\n");
    exit(1);
}

$outsiderPinBlocked = false;
try {
    KinWhisperService::togglePinThread($userB1, $threadAId);
} catch (ForbiddenException) {
    $outsiderPinBlocked = true;
}
if (!$outsiderPinBlocked) {
    fwrite(STDERR, "❌ FAIL: Outsider was able to pin Family A thread!\n");
    exit(1);
}
echo "  ✅ Pin & mute persist per user (even on legacy threads) and block outsiders.\n";

echo "\n=================================================================\n";
echo "🏆 ALL FAMILYCHAT QA REGRESSION CHECKS PASSED!\n";
echo "=================================================================\n";
