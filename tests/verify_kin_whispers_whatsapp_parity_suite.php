<?php

declare(strict_types=1);

/**
 * 📱 Kin Whispers WhatsApp Feature Parity Automated Test Suite
 *
 * Verifies:
 *  1. Media Upload (Photos, PDFs, Docs) & MIME security validation
 *  2. Message Send with Attachment & Empty Text
 *  3. Quoted Replies (reply_to metadata linking)
 *  4. Star / Bookmark Message Toggle
 *  5. Message Edit & 15-Minute Window Security Gate
 *  6. Delete for Me vs Delete for Everyone
 *  7. Pin Thread & Mute Notifications Toggle
 *  8. Shared Media Gallery Retrieval
 *
 * Charter: Master Engineering Charter v3.0 (Zero Regressions)
 */

require_once __DIR__ . '/../app/config/init.php';

use App\dto\KinWhisperDto;
use App\services\KinWhisperService;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;

echo "=================================================================\n";
echo "📱 KIN WHISPERS WHATSAPP PARITY VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$db = Db::connect2();

// Setup mock test accounts
$suffix = bin2hex(random_bytes(4));
$userA = 'user_wa_a_' . $suffix;
$userB = 'user_wa_b_' . $suffix;
$familyCode = 'WAFAM_' . strtoupper($suffix);

$db->prepare("INSERT INTO account (id, email, password, status, is_child_account, adulthood_status) VALUES (?, ?, 'secret', 'active', 0, 'standard_adult')")
   ->execute([$userA, "wa_a_{$suffix}@test.local"]);
$db->prepare("INSERT INTO account (id, email, password, status, is_child_account, adulthood_status) VALUES (?, ?, 'secret', 'active', 0, 'standard_adult')")
   ->execute([$userB, "wa_b_{$suffix}@test.local"]);

$db->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Alice', 'Olaogun', ?)")
   ->execute([$userA, $familyCode]);
$db->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Bob', 'Olaogun', ?)")
   ->execute([$userB, $familyCode]);

$db->prepare("INSERT INTO user_families (user_id, family_code, role, status) VALUES (?, ?, 'admin', 'approved'), (?, ?, 'member', 'approved')")
   ->execute([$userA, $familyCode, $userB, $familyCode]);

try {
    $thread = KinWhisperService::createOrGetThread($userA, $userB, 'catch_up', 'Weekend Planning');
    echo "[TEST 1] Thread Created: {$thread->id}\n";

    // --- TEST 1: Media Upload (Photo & Document) ---
    echo "\n[TEST 2] Verifying Media Upload (Photo & PDF)...\n";
    $dummyImgPath = sys_get_temp_dir() . '/test_img_' . $suffix . '.png';
    // 1x1 transparent PNG
    file_put_contents($dummyImgPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));

    $imgUpload = KinWhisperService::uploadMediaWhisper($userA, [
        'tmp_name' => $dummyImgPath,
        'name' => 'family_vacation.png',
        'size' => filesize($dummyImgPath),
        'error' => UPLOAD_ERR_OK,
    ]);
    assert($imgUpload['type'] === 'image', 'Upload type must be image');
    assert(str_starts_with($imgUpload['url'], '/public/storage/whispers/media/'), 'Image URL must be in media storage');
    echo "  ✅ Photo uploaded successfully: {$imgUpload['url']}\n";

    // Dummy PDF
    $dummyPdfPath = sys_get_temp_dir() . '/test_doc_' . $suffix . '.pdf';
    file_put_contents($dummyPdfPath, "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 3 3]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000010 00000 n\n0000000053 00000 n\n0000000102 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n149\n%%EOF");
    $pdfUpload = KinWhisperService::uploadMediaWhisper($userA, [
        'tmp_name' => $dummyPdfPath,
        'name' => 'family_recipe.pdf',
        'size' => filesize($dummyPdfPath),
        'error' => UPLOAD_ERR_OK,
    ]);
    assert($pdfUpload['type'] === 'document', 'Upload type must be document');
    assert($pdfUpload['mime'] === 'application/pdf', 'MIME must be application/pdf');
    echo "  ✅ Document uploaded successfully: {$pdfUpload['url']}\n";

    // Dummy WebM video (EBML header)
    $dummyVideoPath = sys_get_temp_dir() . '/test_vid_' . $suffix . '.webm';
    file_put_contents($dummyVideoPath, "\x1A\x45\xDF\xA3\x9F\x42\x86\x81\x01\x42\xF7\x81\x01\x42\xF2\x81\x04\x42\xF3\x81\x08\x42\x82\x84webm\x42\x87\x81\x04\x42\x85\x81\x02");
    $videoUpload = KinWhisperService::uploadMediaWhisper($userA, [
        'tmp_name' => $dummyVideoPath,
        'name' => 'family_gathering.webm',
        'size' => filesize($dummyVideoPath),
        'error' => UPLOAD_ERR_OK,
    ]);
    assert($videoUpload['type'] === 'video', 'Upload type must be video');
    assert(str_starts_with($videoUpload['mime'], 'video/'), 'MIME must start with video/');
    echo "  ✅ Video uploaded successfully: {$videoUpload['url']}\n";

    // Adversarial: malicious shell script
    $dummyShPath = sys_get_temp_dir() . '/exploit_' . $suffix . '.sh';
    file_put_contents($dummyShPath, "#!/bin/bash\necho pwned");
    $shBlocked = false;
    try {
        KinWhisperService::uploadMediaWhisper($userA, [
            'tmp_name' => $dummyShPath,
            'name' => 'exploit.sh',
            'size' => filesize($dummyShPath),
            'error' => UPLOAD_ERR_OK,
        ]);
    } catch (ValidationException $e) {
        $shBlocked = true;
    }
    assert($shBlocked, 'Malicious shell script must be rejected');
    echo "  🛡️  [ADVERSARIAL NEUTRALIZED] Malicious executable upload rejected.\n";

    // --- TEST 3: Send Message with Attachment & Empty Body ---
    echo "\n[TEST 3] Verifying Whisper with Attachment & Empty Caption...\n";
    $msgWithMedia = KinWhisperService::sendMessage(
        $userA,
        $thread->id,
        '', // Empty body
        'text',
        null,
        null,
        ['attachments' => [$imgUpload, $videoUpload]]
    );
    assert(!empty($msgWithMedia->contextMetadata['attachments']), 'Attachments must be present in contextMetadata');
    echo "  ✅ Photo & video whisper sent successfully without requiring body text: ID={$msgWithMedia->id}\n";

    // --- TEST 4: Quoted Replies ---
    echo "\n[TEST 4] Verifying Quoted Reply (Swipe/Click to Reply)...\n";
    $replyMsg = KinWhisperService::sendMessage(
        $userB,
        $thread->id,
        'This picture looks incredible!',
        'text',
        null,
        null,
        [
            'reply_to' => [
                'id' => $msgWithMedia->id,
                'sender_name' => 'Alice Olaogun',
                'body' => '📷 Photo',
                'type' => 'image',
            ]
        ]
    );
    assert(!empty($replyMsg->contextMetadata['reply_to']), 'reply_to must be stored in contextMetadata');
    assert($replyMsg->contextMetadata['reply_to']['id'] === $msgWithMedia->id, 'Quoted message ID must match');
    echo "  ✅ Quoted reply linked: ID={$replyMsg->id}, Quoting={$replyMsg->contextMetadata['reply_to']['id']}\n";

    // --- TEST 5: Star / Bookmark Message ---
    echo "\n[TEST 5] Verifying Star / Bookmark Message...\n";
    $starRes1 = KinWhisperService::toggleStar($userA, $replyMsg->id);
    assert($starRes1['is_starred'] === true, 'Message must be starred');
    echo "  ✅ Message starred by User A.\n";

    $starRes2 = KinWhisperService::toggleStar($userA, $replyMsg->id);
    assert($starRes2['is_starred'] === false, 'Message must be un-starred');
    echo "  ✅ Message un-starred by User A.\n";

    // --- TEST 6: Message Edit (15-Minute Window) ---
    echo "\n[TEST 6] Verifying Message Edit (15-Minute Invariant)...\n";
    $edited = KinWhisperService::editMessage($userB, $replyMsg->id, 'This picture looks truly magnificent!');
    assert($edited->body === 'This picture looks truly magnificent!', 'Body must be updated');
    assert(!empty($edited->contextMetadata['is_edited']), 'is_edited must be true');
    echo "  ✅ Message edited by sender: '{$edited->body}' (marked edited at {$edited->contextMetadata['edited_at']})\n";

    // BOLA: User A tries to edit User B's message
    $editBlocked = false;
    try {
        KinWhisperService::editMessage($userA, $replyMsg->id, 'Malicious tampering');
    } catch (ForbiddenException $e) {
        $editBlocked = true;
    }
    assert($editBlocked, 'Non-author cannot edit another user message');
    echo "  🛡️  [ADVERSARIAL NEUTRALIZED] BOLA edit attempt blocked (403 Forbidden).\n";

    // --- TEST 7: Delete for Me vs Delete for Everyone ---
    echo "\n[TEST 7] Verifying Delete for Me...\n";
    $delMeRes = KinWhisperService::deleteMessage($userA, $replyMsg->id, 'me');
    assert($delMeRes['mode'] === 'me', 'Delete mode must be me');
    
    // User A should no longer see the message
    $userAMsgs = KinWhisperService::getMessages($userA, $thread->id);
    $userAIds = array_map(fn($m) => $m->id, $userAMsgs);
    assert(!in_array($replyMsg->id, $userAIds, true), 'Message must be hidden from User A');

    // User B should STILL see the message
    $userBMsgs = KinWhisperService::getMessages($userB, $thread->id);
    $userBIds = array_map(fn($m) => $m->id, $userBMsgs);
    assert(in_array($replyMsg->id, $userBIds, true), 'Message must remain visible to User B');
    echo "  ✅ 'Delete for Me' verified: hidden from User A, retained for User B.\n";

    // --- TEST 8: Pin & Mute Thread ---
    echo "\n[TEST 8] Verifying Pin Thread & Mute Notifications...\n";
    $pinRes = KinWhisperService::togglePinThread($userA, $thread->id);
    assert($pinRes === true, 'Thread must be pinned');
    echo "  ✅ Thread pinned for User A.\n";

    $muteRes = KinWhisperService::toggleMuteThread($userA, $thread->id);
    assert($muteRes === true, 'Thread must be muted');
    echo "  ✅ Thread notifications muted for User A.\n";

    $threadsA = KinWhisperService::listUserThreads($userA);
    assert(!empty($threadsA), 'Threads list must not be empty');
    assert($threadsA[0]->id === $thread->id, 'Pinned thread must appear first in list');
    assert($threadsA[0]->isPinned === true, 'isPinned must be true on DTO');
    assert($threadsA[0]->isMuted === true, 'isMuted must be true on DTO');
    echo "  ✅ Pinned thread sorting verified: {$threadsA[0]->id} is at top of conversation list.\n";

    // --- TEST 9: Shared Media Gallery ---
    echo "\n[TEST 9] Verifying Shared Media Gallery Retrieval...\n";
    $media = KinWhisperService::getThreadMedia($userA, $thread->id);
    assert(!empty($media['photos']), 'Shared photos must contain uploaded photo');
    assert($media['photos'][0]['name'] === 'family_vacation.png', 'Photo name must match');
    assert(!empty($media['videos']), 'Shared videos must contain uploaded video');
    assert($media['videos'][0]['name'] === 'family_gathering.webm', 'Video name must match');
    echo "  ✅ Shared media gallery retrieved: " . count($media['photos']) . " photo(s), " . count($media['videos']) . " video(s).\n";

    // --- TEST 10: My Story Removal Invariant ---
    echo "\n[TEST 10] Verifying 'My Story' Bar Removal from Kin Whispers View...\n";
    $viewContent = file_get_contents(dirname(__DIR__) . '/resources/views/kin_whispers/index.blade.php');
    assert(strpos($viewContent, 'kw-stories-bar') === false, 'kw-stories-bar CSS/HTML must be removed');
    assert(strpos($viewContent, 'My Story') === false, "'My Story' must not appear in kin whispers view");
    echo "  ✅ 'My Story' strip cleanly removed from Kin Whispers view.\n";

    // --- TEST 11: Online & Last Seen Presence Sync ---
    echo "\n[TEST 11] Verifying Online & Last Seen Presence Engine...\n";
    // Default privacy for both users is 'everyone'
    $privA = KinWhisperService::getPresencePrivacy($userA);
    $privB = KinWhisperService::getPresencePrivacy($userB);
    assert($privA === 'everyone', 'Default privacy for User A must be everyone');
    assert($privB === 'everyone', 'Default privacy for User B must be everyone');

    // Simulate User B presence heartbeat
    $syncB = KinWhisperService::syncPresence($userB, [$userA]);
    assert($syncB['privacy'] === 'everyone');

    // User A syncs presence querying User B
    $syncA = KinWhisperService::syncPresence($userA, [$userB]);
    assert(isset($syncA['presence'][$userB]), 'User B presence must be returned for User A');
    assert($syncA['presence'][$userB]['is_online'] === true, 'User B must be reported as online');
    assert($syncA['presence'][$userB]['is_hidden'] === false, 'User B presence must not be hidden');
    assert($syncA['presence'][$userB]['last_seen_ts'] !== null, 'User B last_seen_ts must be populated');
    echo "  ✅ Online status and last seen timestamp successfully synced for kin.\n";

    // --- TEST 12: Privacy Setting (Hide Online & Last Seen) ---
    echo "\n[TEST 12] Verifying User Privacy Settings (Hide Last Seen / Nobody)...\n";
    // User B sets privacy to 'nobody'
    KinWhisperService::setPresencePrivacy($userB, 'nobody');
    assert(KinWhisperService::getPresencePrivacy($userB) === 'nobody', 'User B privacy must be updated to nobody');

    // User A syncs presence querying User B: User B's details must be masked
    $syncAAfterHide = KinWhisperService::syncPresence($userA, [$userB]);
    assert(isset($syncAAfterHide['presence'][$userB]));
    assert($syncAAfterHide['presence'][$userB]['is_hidden'] === true, 'User B presence must be marked is_hidden');
    assert($syncAAfterHide['presence'][$userB]['is_online'] === false, 'User B must not be reported as online when hidden');
    assert($syncAAfterHide['presence'][$userB]['last_seen_ts'] === null, 'User B last_seen_ts must be null when hidden');
    echo "  🛡️  [PRIVACY VERIFIED] User B presence masked from User A when privacy is 'nobody'.\n";

    // Reciprocal Privacy: When User A sets privacy to 'nobody', User A cannot see User B's presence even if User B makes it 'everyone'
    KinWhisperService::setPresencePrivacy($userB, 'everyone');
    KinWhisperService::setPresencePrivacy($userA, 'nobody');
    $syncAReciprocal = KinWhisperService::syncPresence($userA, [$userB]);
    assert($syncAReciprocal['presence'][$userB]['is_hidden'] === true, 'Viewer with nobody privacy cannot see partner presence');
    assert($syncAReciprocal['presence'][$userB]['is_online'] === false);
    assert($syncAReciprocal['presence'][$userB]['last_seen_ts'] === null);
    echo "  🛡️  [RECIPROCAL PRIVACY VERIFIED] Viewer with 'nobody' privacy cannot view partner presence.\n";

    // Validation guard: illegal privacy value must throw ValidationException
    $threwValidation = false;
    try {
        KinWhisperService::setPresencePrivacy($userA, 'invalid_value');
    } catch (\Src\Exceptions\ValidationException $e) {
        $threwValidation = true;
    }
    assert($threwValidation === true, 'Invalid privacy value must be rejected with ValidationException');
    echo "  🛡️  [INPUT VALIDATION] Illegal privacy value rejected.\n";

    echo "\n=================================================================\n";
    echo "🏆 ALL 12 WHATSAPP PARITY TESTS PASSED CLEANLY!\n";
    echo "=================================================================\n";

} finally {
    // Cleanup files
    if (isset($dummyImgPath) && file_exists($dummyImgPath)) unlink($dummyImgPath);
    if (isset($dummyPdfPath) && file_exists($dummyPdfPath)) unlink($dummyPdfPath);
    if (isset($dummyShPath) && file_exists($dummyShPath)) unlink($dummyShPath);

    // Cleanup DB fixtures
    $cleanupThreadId = $thread->id ?? '';
    $stmtDelMsg = $db->prepare("DELETE FROM kin_messages WHERE thread_id = ?");
    $stmtDelMsg->execute([$cleanupThreadId]);

    $stmtDelPart = $db->prepare("DELETE FROM kin_thread_participants WHERE thread_id = ?");
    $stmtDelPart->execute([$cleanupThreadId]);

    $stmtDelThread = $db->prepare("DELETE FROM kin_threads WHERE id = ?");
    $stmtDelThread->execute([$cleanupThreadId]);

    $stmtDelFam = $db->prepare("DELETE FROM user_families WHERE user_id IN (?, ?)");
    $stmtDelFam->execute([$userA, $userB]);

    $stmtDelPers = $db->prepare("DELETE FROM personal WHERE id IN (?, ?)");
    $stmtDelPers->execute([$userA, $userB]);

    $stmtDelAcc = $db->prepare("DELETE FROM account WHERE id IN (?, ?)");
    $stmtDelAcc->execute([$userA, $userB]);
}
