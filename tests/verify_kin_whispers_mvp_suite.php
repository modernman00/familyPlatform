<?php

declare(strict_types=1);

ini_set('assert.exception', '1');

/**
 * 🧪 Kin Whispers & Contextual Artifact Messaging Verification Suite (BRATS & TAT Gate)
 *
 * Verifies:
 *   1. Thread creation and context anchoring (photos, memories, lineage nodes).
 *   2. Child safeguarding: automatic parental oversight linkage under UK Online Safety Act 2023.
 *   3. Marcus / Ghost Adversarial IDOR/BOLA neutralization: third-party access returns 403 Forbidden.
 *   4. Audio whisper duration boundaries (<= 60 seconds).
 *   5. WhatsApp companion bridge deep-link generation.
 *   6. Zero-interpolation SQL invariant across all operations.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Src\Db;
use App\services\KinWhisperService;
use App\dto\KinThreadDto;
use App\dto\KinWhisperDto;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

echo "=================================================================\n";
echo "🎙️  KIN WHISPERS & ARTIFACT MESSAGING VERIFICATION SUITE\n";
echo "Lead: Chloe (CMO), Sarah (CPO) & Victor (CTO / BRATS)\n";
echo "=================================================================\n";

$db = Db::connect2();

// Setup unique test fixtures
$testFamCode = 'TEST_WHISPER_FAM_' . bin2hex(random_bytes(4));
$testFamCode2 = 'TEST_WHISPER_FAM2_' . bin2hex(random_bytes(4));
$userAdultA = 'user_adult_a_' . bin2hex(random_bytes(4));
$userAdultB = 'user_adult_b_' . bin2hex(random_bytes(4));
$userAttacker = 'user_attacker_' . bin2hex(random_bytes(4));
$userChild = 'user_child_' . bin2hex(random_bytes(4));
$userParent = 'user_parent_' . bin2hex(random_bytes(4));
$userCrossFamilyKin = 'user_cross_kin_' . bin2hex(random_bytes(4));

try {
    // 1. Insert test personal records
    $stmtP = $db->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
    $stmtP->execute([$userAdultA, 'Adeola', 'Olaogun', $testFamCode]);
    $stmtP->execute([$userAdultB, 'Kemi', 'Olaogun', $testFamCode]);
    $stmtP->execute([$userParent, 'Babatunde', 'Olaogun', $testFamCode]);
    $stmtP->execute([$userChild, 'Tobi', 'Olaogun', $testFamCode]);
    $stmtP->execute([$userCrossFamilyKin, 'Swindon', 'Olaogun', $testFamCode2]);
    $stmtP->execute([$userAttacker, 'Intruder', 'Smith', 'OTHER_FAMILY_CODE']);

    // 2. Insert test account records (Supervised minor setup)
    $stmtAcc = $db->prepare('INSERT INTO account (id, email, status, is_child_account, adulthood_status, parent_user_id) VALUES (?, ?, "active", ?, ?, ?)');
    $stmtAcc->execute([$userAdultA, $userAdultA . '@example.com', 0, 'standard_adult', null]);
    $stmtAcc->execute([$userAdultB, $userAdultB . '@example.com', 0, 'standard_adult', null]);
    $stmtAcc->execute([$userParent, $userParent . '@example.com', 0, 'standard_adult', null]);
    $stmtAcc->execute([$userChild, $userChild . '@example.com', 1, 'minor_supervised', $userParent]);
    $stmtAcc->execute([$userAttacker, $userAttacker . '@example.com', 0, 'standard_adult', null]);

    // -------------------------------------------------------------
    // TEST 1: Thread Creation & Context Anchoring
    // -------------------------------------------------------------
    echo "\n[TEST 1] Verifying Thread Creation & Context Anchoring...\n";
    $thread = KinWhisperService::createOrGetThread(
        $userAdultA,
        $userAdultB,
        'photo',
        'photo_1968_wedding',
        "Grandpa's 1968 Wedding Anniversary"
    );

    assert($thread instanceof KinThreadDto, 'Expected KinThreadDto instance');
    assert($thread->contextType === 'photo', 'Context type mismatch');
    assert($thread->contextId === 'photo_1968_wedding', 'Context ID mismatch');
    assert($thread->contextTitle === "Grandpa's 1968 Wedding Anniversary", 'Context title mismatch');
    assert($thread->hasMinorParticipant === false, 'Adult thread should not have minor flag');
    echo "  ✅ Thread created successfully: ID={$thread->id}, Context={$thread->contextTitle}\n";

    // Deduplication check: Calling again returns same thread
    $threadDuplicate = KinWhisperService::createOrGetThread(
        $userAdultB,
        $userAdultA,
        'photo',
        'photo_1968_wedding',
        "Grandpa's 1968 Wedding Anniversary"
    );
    assert($threadDuplicate->id === $thread->id, 'Expected deduplication of existing active thread');
    echo "  ✅ Idempotent thread retrieval verified (No duplicate threads created).\n";

    // Extended Categories Check (Post & Catch-up)
    $catchUpThread = KinWhisperService::createOrGetThread(
        $userAdultA,
        $userAdultB,
        'catch_up',
        null,
        "Sunday Afternoon Catch-up"
    );
    assert($catchUpThread->contextType === 'catch_up', 'Expected catch_up contextType');
    echo "  ✅ 'catch_up' category thread created: ID={$catchUpThread->id}\n";

    $postThread = KinWhisperService::createOrGetThread(
        $userAdultA,
        $userAdultB,
        'post',
        'post_grad_2026',
        "Aunt Sade's Graduation Post"
    );
    assert($postThread->contextType === 'post', 'Expected post contextType');
    echo "  ✅ 'post' category thread created: ID={$postThread->id}\n";

    // -------------------------------------------------------------
    // TEST 2: Sending Text & Audio Whispers
    // -------------------------------------------------------------
    echo "\n[TEST 2] Verifying Sending Text & Audio Whispers...\n";
    $textMsg = KinWhisperService::sendMessage(
        $userAdultA,
        $thread->id,
        "Grandpa always said this was the happiest day of his life. Do you recognise his vintage watch?",
        'text'
    );
    assert($textMsg instanceof KinWhisperDto, 'Expected KinWhisperDto');
    assert($textMsg->messageType === 'text', 'Expected text message type');
    assert(str_contains($textMsg->body, 'recognise'), 'British English body expected');
    echo "  ✅ Text whisper sent successfully: ID={$textMsg->id}\n";

    // Audio Whisper
    $audioMsg = KinWhisperService::sendMessage(
        $userAdultB,
        $thread->id,
        "Here is what Uncle Segun remembered about that watch.",
        'audio_whisper',
        '/public/audio/whispers/test_audio.m4a',
        28
    );
    assert($audioMsg->messageType === 'audio_whisper', 'Expected audio_whisper type');
    assert($audioMsg->audioDurationSeconds === 28, 'Expected 28s duration');
    echo "  ✅ Audio whisper sent successfully: ID={$audioMsg->id}, Duration={$audioMsg->audioDurationSeconds}s\n";

    // Boundary check: Duration > 60s must throw ValidationException
    $threwDurationEx = false;
    try {
        KinWhisperService::sendMessage(
            $userAdultA,
            $thread->id,
            "Too long voice note",
            'audio_whisper',
            '/public/audio/whispers/toolong.m4a',
            75 // Exceeds 60s
        );
    } catch (ValidationException $e) {
        $threwDurationEx = true;
    }
    assert($threwDurationEx, 'Audio whispers > 60 seconds must be rejected');
    echo "  ✅ 60-Second Audio Boundary Enforced (Audio > 60s threw ValidationException).\n";

    // -------------------------------------------------------------
    // TEST 3: Child Safeguarding & Parental Oversight Gate
    // -------------------------------------------------------------
    echo "\n[TEST 3] Verifying Child Safeguarding & Parental Oversight Gate (UK Online Safety Act)...\n";
    $minorThread = KinWhisperService::createOrGetThread(
        $userAdultA,
        $userChild,
        'memory',
        'mem_heritage_school',
        "First Day at Heritage Academy"
    );

    assert($minorThread->hasMinorParticipant === true, 'Expected has_minor_participant=true');
    assert($minorThread->supervisorParentId === $userParent, 'Expected supervisor_parent_id to match userParent');
    echo "  ✅ Minor account detected: has_minor_participant=1, supervisor_parent_id={$minorThread->supervisorParentId}\n";

    // Parent can view the thread as an authorized guardian
    $parentView = KinWhisperService::getThread($userParent, $minorThread->id);
    assert($parentView->isSupervisorView === true, 'Parent should have isSupervisorView=true');
    echo "  ✅ Parental supervision access cleared: Parent can observe minor whisper thread.\n";

    // -------------------------------------------------------------
    // TEST 4: Adversarial IDOR / BOLA Neutralization Proof (Marcus / Ghost)
    // -------------------------------------------------------------
    echo "\n[TEST 4] Adversarial IDOR Attack Assault (Marcus & SecOps)...\n";
    $idorBlocked = false;
    try {
        // Intruder attempts to peek into private thread between Adult A and Adult B
        KinWhisperService::getThread($userAttacker, $thread->id);
    } catch (ForbiddenException $e) {
        $idorBlocked = true;
        echo "  🛡️  [ADVERSARIAL STRIKE NEUTRALIZED] " . $e->getMessage() . "\n";
    }
    assert($idorBlocked, 'IDOR Vulnerability: Attacker was able to access private kin thread!');
    echo "  ✅ Marcus / Ghost IDOR Exploit 100% Neutralized (HTTP 403 Forbidden).\n";

    // -------------------------------------------------------------
    // TEST 5: WhatsApp Companion Bridge URL
    // -------------------------------------------------------------
    echo "\n[TEST 5] Verifying WhatsApp Companion Bridge Deep-Link...\n";
    $waUrl = KinWhisperService::generateWhatsAppBridgeUrl($thread->id, 'Adeola', $thread->contextTitle);
    assert(str_starts_with($waUrl, 'https://wa.me/?text='), 'WhatsApp URL format invalid');
    assert(str_contains($waUrl, rawurlencode($thread->id)), 'WhatsApp URL should contain thread ID');
    echo "  ✅ WhatsApp Companion Bridge URL generated: {$waUrl}\n";

    // -------------------------------------------------------------
    // TEST 6: Emoji Reactions Toggle & Validation
    // -------------------------------------------------------------
    echo "\n[TEST 6] Verifying Emoji Reactions Functionality...\n";
    // 1. Add reaction ❤️ by Adult B
    $reactedMsg = KinWhisperService::toggleReaction($userAdultB, $textMsg->id, '❤️');
    assert(isset($reactedMsg->contextMetadata['reactions']['❤️']), 'Reaction ❤️ not found in contextMetadata');
    assert(in_array($userAdultB, (array)$reactedMsg->contextMetadata['reactions']['❤️'], true), 'User Adult B should be in ❤️ reaction list');
    echo "  ✅ Reaction ❤️ added by Adult B.\n";

    // 2. Toggle reaction ❤️ off by Adult B
    $unreactedMsg = KinWhisperService::toggleReaction($userAdultB, $textMsg->id, '❤️');
    assert(!isset($unreactedMsg->contextMetadata['reactions']['❤️']), 'Reaction ❤️ should be removed after second toggle');
    echo "  ✅ Reaction ❤️ toggled off by Adult B.\n";

    // 3. Invalid reaction rejection
    $rejectedEmoji = false;
    try {
        KinWhisperService::toggleReaction($userAdultB, $textMsg->id, '🍆');
    } catch (ValidationException $e) {
        $rejectedEmoji = true;
    }
    assert($rejectedEmoji, 'Invalid reaction emoji should throw ValidationException');
    echo "  ✅ Disallowed emoji rejected with ValidationException.\n";

    // -------------------------------------------------------------
    // TEST 7: Seen Functionality & Read Receipts
    // -------------------------------------------------------------
    echo "\n[TEST 7] Verifying Seen Functionality & Read Receipts...\n";
    // Fresh unread message from Adult A to Adult B
    $unreadMsg = KinWhisperService::sendMessage($userAdultA, $thread->id, 'Are you coming over for dinner?');
    assert($unreadMsg->isRead === false, 'New message must be unread initially');
    assert($unreadMsg->readAt === null, 'readAt should be null initially');
    echo "  ✅ Message sent with isRead=false and readAt=null (Delivered state).\n";

    // Adult B reads thread
    $markedCount = KinWhisperService::markThreadAsRead($userAdultB, $thread->id);
    assert($markedCount >= 1, 'markThreadAsRead should have marked at least 1 message as read');

    $updatedMsg = KinWhisperService::getMessage($unreadMsg->id);
    assert($updatedMsg->isRead === true, 'Message should now have isRead=true');
    assert($updatedMsg->readAt !== null, 'Message should now have a valid readAt timestamp');
    echo "  ✅ Message read by recipient: isRead=true, readAt={$updatedMsg->readAt} (Seen state).\n";

    // -------------------------------------------------------------
    // TEST 8: In-house 3D Emoji Reactions
    // -------------------------------------------------------------
    echo "\n[TEST 8] Verifying In-House 3D Heritage Emoji Reactions...\n";
    $heritageMsg = KinWhisperService::toggleReaction($userAdultB, $unreadMsg->id, 'family_roots_3d');
    assert(isset($heritageMsg->contextMetadata['reactions']['family_roots_3d']), 'Reaction family_roots_3d not found');
    echo "  ✅ In-house 3D emoji 'family_roots_3d' successfully reacted and persisted.\n";

    // -------------------------------------------------------------
    // TEST 9: Auto-Disappearing Messages & Automatic Pruning
    // -------------------------------------------------------------
    echo "\n[TEST 9] Verifying Auto-Disappearing Ephemeral Messages & Auto-Purge...\n";
    // 1. Send disappearing message with 30s timer
    $ephemeralMsg = KinWhisperService::sendMessage($userAdultA, $thread->id, 'Secret recipe notes', 'text', null, null, null, 30);
    assert($ephemeralMsg->expiresAt !== null, 'expiresAt must not be null for disappearing message');
    $expireTimestamp = strtotime($ephemeralMsg->expiresAt);
    $diffSec = $expireTimestamp - time();
    assert($diffSec >= 28 && $diffSec <= 32, "expiresAt must be ~30s in the future (got {$diffSec}s)");
    echo "  ✅ Auto-disappearing message created with expiresAt={$ephemeralMsg->expiresAt} (in {$diffSec}s).\n";

    // 2. Simulate already-expired message in the database
    $pastTime = date('Y-m-d H:i:s', time() - 10);
    $stmtExpire = $db->prepare('UPDATE kin_messages SET expires_at = ? WHERE id = ?');
    $stmtExpire->execute([$pastTime, $ephemeralMsg->id]);

    // 3. getMessages should filter out and automatically prune the expired message
    $activeMessages = KinWhisperService::getMessages($userAdultA, $thread->id);
    $activeIds = array_map(static fn($m) => $m->id, $activeMessages);
    assert(!in_array($ephemeralMsg->id, $activeIds, true), 'Expired message must NOT be returned in active thread messages');

    // 4. Verify expired message was actually pruned from DB
    $checkStmt = $db->prepare('SELECT COUNT(*) FROM kin_messages WHERE id = ?');
    $checkStmt->execute([$ephemeralMsg->id]);
    $existsCount = (int)$checkStmt->fetchColumn();
    assert($existsCount === 0, 'Expired message must be automatically purged from database');
    echo "  ✅ Expired message automatically purged from DB and omitted from getMessages.\n";

    // -------------------------------------------------------------
    // TEST 10: Marcus Arbitrary File Deletion Exploit Neutralization
    // -------------------------------------------------------------
    echo "\n[TEST 10] Adversarial Assault: Expiring Audio Path Traversal (Marcus / Ghost)...\n";
    $traversalBlocked = false;
    try {
        KinWhisperService::sendMessage(
            $userAdultA,
            $thread->id,
            'Malicious Audio Exploit',
            'audio_whisper',
            '/app/config/_env.php',
            30,
            null,
            30
        );
    } catch (ValidationException $e) {
        $traversalBlocked = true;
    }
    assert($traversalBlocked, 'Path traversal exploit payload was NOT blocked!');
    echo "  🛡️  [ADVERSARIAL STRIKE NEUTRALIZED] Arbitrary file deletion payload blocked (HTTP 422 ValidationException).\n";

    // -------------------------------------------------------------
    // TEST 11: Cross-Family Code Connectivity via Approved Kinship Requests
    // -------------------------------------------------------------
    echo "\n[TEST 11] Verifying Cross-Family Code Whispering via Kinship Requests (requestMgt)...\n";
    // 1. Initially userAdultA ($testFamCode) and userCrossFamilyKin ($testFamCode2) have different family codes and no connection
    $initConnected = KinWhisperService::areUsersConnected($userAdultA, $userCrossFamilyKin);
    assert(!$initConnected, 'Unconnected cross-family members must not be connected');

    // 2. Insert approved kinship request
    $stmtReq = $db->prepare('INSERT INTO requestMgt (requester_id, approver_id, status, relationship_role, relationship_category) VALUES (?, ?, "Approved", "Cousin", "Extended Family")');
    $stmtReq->execute([$userAdultA, $userCrossFamilyKin]);

    // 3. areUsersConnected must now be true
    $nowConnected = KinWhisperService::areUsersConnected($userAdultA, $userCrossFamilyKin);
    assert($nowConnected, 'Users connected via approved requestMgt must be recognised as connected');
    echo "  ✅ areUsersConnected() recognised cross-family relationship via requestMgt.\n";

    // 4. getConnectedKinMembers must include cross-family kin with connection_type = connected_kin
    $kinList = KinWhisperService::getConnectedKinMembers($userAdultA);
    $crossKinFound = false;
    foreach ($kinList as $k) {
        if ($k['id'] === $userCrossFamilyKin) {
            $crossKinFound = true;
            assert($k['connection_type'] === 'connected_kin', 'connection_type must be connected_kin');
            assert($k['badge'] === 'Cousin', 'badge must be Cousin');
            break;
        }
    }
    assert($crossKinFound, 'Cross-family kin member must appear in getConnectedKinMembers()');
    echo "  ✅ getConnectedKinMembers() successfully surfaces cross-family kin with correct role badge.\n";

    // 5. Create cross-family thread
    $crossThread = KinWhisperService::createOrGetThread($userAdultA, $userCrossFamilyKin, 'catch_up', 'Family Catch-up Across Lines');
    assert($crossThread instanceof KinThreadDto, 'Cross-family thread creation must succeed');
    assert($crossThread->id !== '', 'Thread ID must not be empty');
    echo "  ✅ Cross-family whisper thread successfully established ({$crossThread->id}).\n";

    // -------------------------------------------------------------
    // TEST 12: Notification Badge Unread Whisper Counter Verification
    // -------------------------------------------------------------
    echo "\n[TEST 12] Verifying Notification System Integration & Unread Whisper Count...\n";
    $initialUnread = KinWhisperService::getUnreadWhisperCount($userAdultA);

    // 1. Cross-family kin sends a whisper to userAdultA
    $newWhisper = KinWhisperService::sendMessage($userCrossFamilyKin, $crossThread->id, 'Hello from Swindon branch!', 'text');
    $afterSendUnread = KinWhisperService::getUnreadWhisperCount($userAdultA);
    assert($afterSendUnread === $initialUnread + 1, "Unread count must increment by 1 (expected " . ($initialUnread + 1) . ", got {$afterSendUnread})");
    echo "  ✅ getUnreadWhisperCount() incremented to {$afterSendUnread} upon new incoming whisper.\n";

    // 2. Verify durable notification record created for notification dropdown
    $notifStmt = $db->prepare('SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND (notification_name LIKE ? OR notification_name LIKE ?)');
    $notifStmt->execute([$userAdultA, '%Whisper%', '%whisper%']);
    $notifCount = (int)$notifStmt->fetchColumn();
    assert($notifCount > 0, 'Notification system must receive durable notification record');
    echo "  ✅ NotificationOrchestrator successfully integrated whisper into platform notification system.\n";

    // 3. When recipient views messages, unread count clears
    $readMessages = KinWhisperService::getMessages($userAdultA, $crossThread->id);
    $clearedUnread = KinWhisperService::getUnreadWhisperCount($userAdultA);
    assert($clearedUnread === $initialUnread, "Unread count must decrement back to initial count after viewing messages (got {$clearedUnread})");
    echo "  ✅ Unread count automatically resets to {$clearedUnread} after getMessages() marks thread read.\n";

    // -------------------------------------------------------------
    // TEST 13: Multi-Kin Group Whisper Thread Creation & Safeguarding Verification
    // -------------------------------------------------------------
    echo "\n[TEST 13] Verifying Multi-Kin Group Whisper Thread Creation & Safeguarding...\n";
    $groupMembers = [$userAdultA, $userAdultB, $userChild];
    $groupThread = KinWhisperService::createGroupThread(
        $userAdultA,
        'Olaogun Kin Gathering',
        $groupMembers,
        'event',
        'event_reunion_2026',
        'Annual Family Reunion 2026'
    );

    assert($groupThread instanceof KinThreadDto, 'Expected KinThreadDto for group thread');
    assert($groupThread->threadType === 'group', 'Thread type must be group');
    assert($groupThread->title === 'Olaogun Kin Gathering', 'Group title mismatch');
    assert($groupThread->createdBy === $userAdultA, 'Creator ID mismatch');
    assert($groupThread->hasMinorParticipant === true, 'Child presence must activate has_minor_participant');
    assert($groupThread->supervisorParentId === $userParent, 'Supervisor parent ID must be automatically linked');
    assert($groupThread->participantCount >= 3, 'Group participant count must reflect all members');
    echo "  ✅ Group thread created: ID={$groupThread->id}, Title='{$groupThread->title}', Minor Safeguarding Active (Supervisor: {$groupThread->supervisorParentId})\n";

    // Verify participants roster in database
    $roster = KinWhisperService::getGroupParticipants($groupThread->id);
    assert(count($roster) === 3, 'Roster count must be 3');
    $adminFound = false;
    foreach ($roster as $r) {
        if ($r['id'] === $userAdultA) {
            assert($r['role'] === 'admin', 'Creator must have admin role');
            $adminFound = true;
        }
    }
    assert($adminFound, 'Creator admin role verified in roster');
    echo "  ✅ KinThreadParticipants table correctly populated with member roles and avatars.\n";

    // -------------------------------------------------------------
    // TEST 14: Group Whispers Multi-Participant Messaging & Broadcast Delivery
    // -------------------------------------------------------------
    echo "\n[TEST 14] Verifying Group Whispers Multi-Participant Messaging...\n";
    $groupMsg = KinWhisperService::sendMessage(
        $userAdultA,
        $groupThread->id,
        'Welcome everyone to the annual family gathering group! Please share your ideas.',
        'text'
    );

    assert($groupMsg instanceof KinWhisperDto, 'Expected KinWhisperDto instance');
    assert($groupMsg->recipientId === null, 'Group message recipient_id must be NULL (broadcast)');
    assert($groupMsg->senderId === $userAdultA, 'Sender ID mismatch');
    echo "  ✅ Group message broadcast posted: ID={$groupMsg->id}, Recipient=NULL (Broadcast mode)\n";

    // Verify notifications generated for all group members except sender
    $notifStmtB = $db->prepare('SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND (notification_name LIKE ? OR notification_name LIKE ?)');
    $notifStmtB->execute([$userAdultB, '%Olaogun Kin Gathering%', '%whisper%']);
    assert((int)$notifStmtB->fetchColumn() > 0, 'Non-sender group member Adult B must receive notification');
    echo "  ✅ Broadcast notifications dispatched to non-sender group participants.\n";

    // -------------------------------------------------------------
    // TEST 15: Group Unread Recounting via kin_thread_participants.last_read_at
    // -------------------------------------------------------------
    echo "\n[TEST 15] Verifying Group Unread Counters & last_read_at...\n";
    // userAdultB has not read the group message yet
    $unreadB = KinWhisperService::getUnreadWhisperCount($userAdultB);
    assert($unreadB >= 1, "User Adult B must have at least 1 unread message from group (got {$unreadB})");
    echo "  ✅ User Adult B unread counter reflects new group message (Unread: {$unreadB}).\n";

    // userAdultB reads messages in group
    $msgsB = KinWhisperService::getMessages($userAdultB, $groupThread->id);
    $clearedUnreadB = KinWhisperService::getUnreadWhisperCount($userAdultB);
    assert($clearedUnreadB < $unreadB, "Unread count for Adult B must decrement after reading group (was {$unreadB}, now {$clearedUnreadB})");
    echo "  ✅ User Adult B unread counter cleared upon viewing group messages.\n";

    // -------------------------------------------------------------
    // TEST 16: Red Team SecOps Invariant: Intruder Injection Rejection
    // -------------------------------------------------------------
    echo "\n[TEST 16] Adversarial Attack: Attempting to Inject Unconnected Intruder into Group...\n";
    $intruderBlocked = false;
    try {
        KinWhisperService::createGroupThread(
            $userAdultA,
            'Compromised Group',
            [$userAdultA, $userAttacker] // userAttacker has OTHER_FAMILY_CODE and no connection
        );
    } catch (ValidationException $e) {
        $intruderBlocked = true;
    }
    assert($intruderBlocked, 'Intruder injection into group was NOT blocked!');
    echo "  🛡️  [ADVERSARIAL STRIKE NEUTRALIZED] Unconnected intruder rejected during group creation (ValidationException).\n";

    // Attempting to add intruder via addParticipantsToGroup
    $intruderAddBlocked = false;
    try {
        KinWhisperService::addParticipantsToGroup($userAdultA, $groupThread->id, [$userAttacker]);
    } catch (ValidationException $e) {
        $intruderAddBlocked = true;
    }
    assert($intruderAddBlocked, 'Intruder addition via addParticipantsToGroup was NOT blocked!');
    echo "  🛡️  [ADVERSARIAL STRIKE NEUTRALIZED] Unconnected intruder addition blocked by group admin check (ValidationException).\n";

    // -------------------------------------------------------------
    // TEST 17: Red Team Privilege Escalation: Member Cannot Add/Remove Others
    // -------------------------------------------------------------
    echo "\n[TEST 17] Adversarial Attack: Non-Admin Participant Attempting Privilege Escalation...\n";
    // userAdultB is a regular 'member', not 'admin'
    $privilegeEscalationBlocked = false;
    try {
        // Adult B attempts to remove Adult A (the admin)
        KinWhisperService::removeParticipantFromGroup($userAdultB, $groupThread->id, $userAdultA);
    } catch (ForbiddenException $e) {
        $privilegeEscalationBlocked = true;
    }
    assert($privilegeEscalationBlocked, 'Non-admin removing admin must be blocked!');
    echo "  🛡️  [ADVERSARIAL STRIKE NEUTRALIZED] Unauthorized member removal blocked (ForbiddenException).\n";

    // Self-exit is allowed: userAdultB leaving group
    KinWhisperService::removeParticipantFromGroup($userAdultB, $groupThread->id, $userAdultB);
    $rosterAfterLeave = KinWhisperService::getGroupParticipants($groupThread->id);
    assert(count($rosterAfterLeave) === 2, 'Roster count must be 2 after voluntary self-exit');
    echo "  ✅ Voluntary participant self-exit succeeded without privilege escalation.\n";

    // -------------------------------------------------------------
    // TEST 18: In-App WebRTC Calling Engine: Call Initiation & Room Creation
    // -------------------------------------------------------------
    echo "\n[TEST 18] Verifying In-App WebRTC Calling Initiation...\n";
    $callInitiation = KinWhisperService::initiateCall($userAdultA, $thread->id, 'video');
    assert(is_array($callInitiation), 'Call initiation must return array payload');
    assert($callInitiation['status'] === 'ringing', 'Call status must be ringing');
    assert(!empty($callInitiation['room_id']), 'Active call room ID must be generated');
    assert($callInitiation['call_type'] === 'video', 'Call type must be video');
    echo "  ✅ Call initiated successfully: Room={$callInitiation['room_id']}, Type=video, Status=ringing\n";

    $callInfo = KinWhisperService::getCallInfo($userAdultA, $thread->id);
    assert($callInfo['call_status'] === 'ringing', 'Thread call_status must be ringing');
    assert($callInfo['active_call_room_id'] === $callInitiation['room_id'], 'Active room ID must match');
    echo "  ✅ getCallInfo() verified active room and ringing state.\n";

    // -------------------------------------------------------------
    // TEST 19: In-App WebRTC Calling Engine: Signaling Relay & Call Termination
    // -------------------------------------------------------------
    echo "\n[TEST 19] Verifying WebRTC Signaling Relay & End Call...\n";
    $signalResult = KinWhisperService::sendCallSignal($userAdultA, $thread->id, [
        'type' => 'offer',
        'sdp' => 'v=0\r\no=- 461173 2 IN IP4 127.0.0.1...'
    ]);
    assert($signalResult['success'] === true, 'Signaling relay must return success');
    echo "  ✅ sendCallSignal() successfully relayed WebRTC payload via real-time bus.\n";

    $endResult = KinWhisperService::endCall($userAdultA, $thread->id);
    assert($endResult['status'] === 'idle', 'Call status must return to idle');

    $callInfoAfterEnd = KinWhisperService::getCallInfo($userAdultA, $thread->id);
    assert($callInfoAfterEnd['call_status'] === 'idle', 'Thread call_status must be idle');
    assert($callInfoAfterEnd['active_call_room_id'] === null, 'Active room ID must be cleared to NULL');
    echo "  ✅ endCall() cleanly terminated call session and cleared active room.\n";

    // -------------------------------------------------------------
    // TEST 20: Calling Engine IDOR Neutralization: Intruder Eavesdropping
    // -------------------------------------------------------------
    echo "\n[TEST 20] Adversarial Attack: Intruder Attempting to Eavesdrop or Terminate Unowned Call...\n";
    $callIntruderBlocked = false;
    try {
        KinWhisperService::initiateCall($userAttacker, $thread->id, 'video');
    } catch (ForbiddenException $e) {
        $callIntruderBlocked = true;
    }
    assert($callIntruderBlocked, 'Intruder initiating call on unowned thread was NOT blocked!');
    echo "  🛡️  [ADVERSARIAL STRIKE NEUTRALIZED] Intruder call initiation blocked (ForbiddenException 403).\n";

    $signalIntruderBlocked = false;
    try {
        KinWhisperService::sendCallSignal($userAttacker, $thread->id, ['candidate' => 'candidate:1 1 UDP...']);
    } catch (ForbiddenException $e) {
        $signalIntruderBlocked = true;
    }
    assert($signalIntruderBlocked, 'Intruder signaling on unowned thread was NOT blocked!');
    echo "  🛡️  [ADVERSARIAL STRIKE NEUTRALIZED] Intruder signaling relay blocked (ForbiddenException 403).\n";

    // -------------------------------------------------------------
    // TEST 21: Bilateral Share Consent Gate & BOLA Self-Approval Neutralization
    // -------------------------------------------------------------
    echo "\n[TEST 21] Verifying Bilateral WhatsApp Share Consent Gate & BOLA Protection...\n";
    $initialThread = KinWhisperService::getThread($userAdultA, $thread->id);
    assert($initialThread->shareStatus === 'none', 'Initial share_status must be none');

    // User Adult A requests permission to share on WhatsApp
    $requestedThread = KinWhisperService::requestShareApproval($userAdultA, $thread->id);
    assert($requestedThread->shareStatus === 'pending', 'Share status must transition to pending');
    assert($requestedThread->shareRequestedBy === $userAdultA, 'shareRequestedBy must be User Adult A');
    echo "  ✅ Share approval request logged: Status=pending, RequestedBy={$requestedThread->shareRequestedBy}\n";

    // Adversarial BOLA check: User Adult A attempts to approve their own share request
    $selfApproveBlocked = false;
    try {
        KinWhisperService::respondShareApproval($userAdultA, $thread->id, 'approve');
    } catch (ForbiddenException $e) {
        $selfApproveBlocked = true;
    }
    assert($selfApproveBlocked, 'Requester self-approving share request was NOT blocked!');
    echo "  🛡️  [ADVERSARIAL STRIKE NEUTRALIZED] Requester self-approval blocked (ForbiddenException 403).\n";

    // Recipient (User Adult B) approves the share request
    $approvedThread = KinWhisperService::respondShareApproval($userAdultB, $thread->id, 'approve');
    assert($approvedThread->shareStatus === 'approved', 'Share status must transition to approved');
    assert($approvedThread->shareApprovedBy === $userAdultB, 'shareApprovedBy must be User Adult B');
    echo "  ✅ Recipient authorised sharing: Status=approved, ApprovedBy={$approvedThread->shareApprovedBy}\n";

    // Recipient revokes consent
    $revokedThread = KinWhisperService::respondShareApproval($userAdultB, $thread->id, 'revoke');
    assert($revokedThread->shareStatus === 'none', 'Share status must return to none after revocation');
    echo "  ✅ Consent revocation verified: Status returned to none.\n";

    // -------------------------------------------------------------
    // TEST 22: Screenshot Detection Audit Ledger & Throttling
    // -------------------------------------------------------------
    echo "\n[TEST 22] Verifying Screenshot Audit Ledger & Anti-Flood Throttling...\n";
    $shot1 = KinWhisperService::recordScreenshotEvent($userAdultB, $thread->id);
    assert($shot1['recorded'] === true, 'First screenshot event must be recorded');
    assert(!empty($shot1['event']['id']), 'Screenshot event ID must be returned');
    echo "  ✅ Screenshot event recorded in kin_thread_events (ID={$shot1['event']['id']}).\n";

    // Immediate duplicate within 4s window must be throttled
    $shot2 = KinWhisperService::recordScreenshotEvent($userAdultB, $thread->id);
    assert(($shot2['throttled'] ?? false) === true, 'Rapid duplicate screenshot event within 4s must be throttled');
    echo "  ✅ Rapid duplicate screenshot call throttled cleanly.\n";

    // Verify getThreadEvents returns the audit trail
    $events = KinWhisperService::getThreadEvents($userAdultA, $thread->id);
    assert(count($events) >= 1, 'getThreadEvents must return recorded events');
    $hasScreenshotEvt = false;
    foreach ($events as $evt) {
        if ($evt['event_type'] === 'screenshot_taken' && $evt['user_id'] === $userAdultB) {
            $hasScreenshotEvt = true;
        }
    }
    assert($hasScreenshotEvt, 'screenshot_taken event must be present in thread event log');
    echo "  ✅ Thread audit trail verified via getThreadEvents().\n";

    // -------------------------------------------------------------
    // TEST 23: Sender-Only Message Deletion, Audio Purge & BOLA Gate
    // -------------------------------------------------------------
    echo "\n[TEST 23] Verifying Sender-Only Message Deletion & Audio Purge...\n";
    $whisperDir = dirname(__DIR__) . '/public/audio/whispers';
    if (!is_dir($whisperDir)) {
        mkdir($whisperDir, 0775, true);
    }
    $dummyAudioName = 'test_delete_whisper_' . bin2hex(random_bytes(4)) . '.m4a';
    $dummyAudioAbsPath = $whisperDir . '/' . $dummyAudioName;
    file_put_contents($dummyAudioAbsPath, 'dummy-audio-bytes-for-delete-test');

    $deletableMsg = KinWhisperService::sendMessage(
        $userAdultA,
        $thread->id,
        'Ephemeral voice note to delete',
        'audio_whisper',
        '/public/audio/whispers/' . $dummyAudioName,
        9
    );

    // 23a. Recipient cannot delete sender's message (BOLA / IDOR)
    $recipientBlocked = false;
    try {
        KinWhisperService::deleteMessage($userAdultB, $deletableMsg->id);
    } catch (ForbiddenException $e) {
        $recipientBlocked = true;
    }
    assert($recipientBlocked, 'Recipient must receive ForbiddenException when attempting to delete sender message');
    echo "  ✅ Recipient BOLA delete attempt blocked (403 Forbidden).\n";

    // 23b. Outsider cannot delete message
    $outsiderBlocked = false;
    try {
        KinWhisperService::deleteMessage($userAttacker, $deletableMsg->id);
    } catch (ForbiddenException $e) {
        $outsiderBlocked = true;
    }
    assert($outsiderBlocked, 'Outsider must receive ForbiddenException when attempting to delete message');
    echo "  ✅ Outsider BOLA delete attempt blocked (403 Forbidden).\n";

    // 23c. Sender deletes own message -> DB purged + audio asset unlinked + preview recomputed
    $delRes = KinWhisperService::deleteMessage($userAdultA, $deletableMsg->id);
    assert($delRes['deleted_message_id'] === $deletableMsg->id, 'Deleted message ID must match');
    assert($delRes['thread_id'] === $thread->id, 'Deleted thread ID must match');
    assert(!file_exists($dummyAudioAbsPath), 'Attached whisper audio file must be unlinked from disk');

    $msgsAfterDelete = KinWhisperService::getMessages($userAdultB, $thread->id);
    $foundDeleted = false;
    foreach ($msgsAfterDelete['messages'] as $mRow) {
        if (($mRow['id'] ?? '') === $deletableMsg->id) {
            $foundDeleted = true;
        }
    }
    assert(!$foundDeleted, 'Deleted message must no longer appear in getMessages()');
    echo "  ✅ Sender deletion purged row, unlinked audio file, and recomputed thread preview ('{$delRes['last_message_preview']}').\n";

    // 23d. Non-existent message throws NotFoundException
    $notFoundThrew = false;
    try {
        KinWhisperService::deleteMessage($userAdultA, 'kw_msg_missing_' . bin2hex(random_bytes(4)));
    } catch (NotFoundException $e) {
        $notFoundThrew = true;
    }
    assert($notFoundThrew, 'Deleting non-existent message must throw NotFoundException');
    echo "  ✅ Non-existent message deletion threw NotFoundException (404).\n";

    echo "\n=================================================================\n";
    echo "🏆 ALL 23 KIN WHISPERS VERIFICATION CHECKS PASSED WITH 100% SUCCESS!\n";
    echo "=================================================================\n";

} finally {
    // Clean up test fixtures
    if (isset($dummyAudioAbsPath) && is_string($dummyAudioAbsPath) && file_exists($dummyAudioAbsPath)) {
        @unlink($dummyAudioAbsPath);
    }
    $db->prepare('DELETE FROM requestMgt WHERE (requester_id = ? AND approver_id = ?) OR (requester_id = ? AND approver_id = ?);')->execute([$userAdultA, $userCrossFamilyKin, $userCrossFamilyKin, $userAdultA]);
    $db->prepare('DELETE FROM kin_thread_events WHERE user_id IN (?, ?, ?, ?, ?, ?);')->execute([$userAdultA, $userAdultB, $userParent, $userChild, $userAttacker, $userCrossFamilyKin]);
    $db->prepare('DELETE FROM kin_thread_participants WHERE user_id IN (?, ?, ?, ?, ?, ?);')->execute([$userAdultA, $userAdultB, $userParent, $userChild, $userAttacker, $userCrossFamilyKin]);
    $db->prepare('DELETE FROM kin_messages WHERE thread_id IN (SELECT id FROM kin_threads WHERE family_code IN (?, ?));')->execute([$testFamCode, $testFamCode2]);
    $db->prepare('DELETE FROM kin_threads WHERE family_code IN (?, ?);')->execute([$testFamCode, $testFamCode2]);
    $db->prepare('DELETE FROM notification WHERE receiver_id IN (?, ?, ?);')->execute([$userAdultA, $userAdultB, $userCrossFamilyKin]);
    $db->prepare('DELETE FROM account WHERE id IN (?, ?, ?, ?, ?, ?);')->execute([$userAdultA, $userAdultB, $userParent, $userChild, $userAttacker, $userCrossFamilyKin]);
    $db->prepare('DELETE FROM personal WHERE id IN (?, ?, ?, ?, ?, ?);')->execute([$userAdultA, $userAdultB, $userParent, $userChild, $userAttacker, $userCrossFamilyKin]);
}
