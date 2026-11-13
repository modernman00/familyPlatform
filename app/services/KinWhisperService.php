<?php

declare(strict_types=1);

namespace App\services;

use App\dto\KinThreadDto;
use App\dto\KinWhisperDto;
use PDO;
use Src\Db;
use Src\Exceptions\BadRequestException;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;

/**
 * 🎙️ KinWhisperService
 *
 * Sovereign Service for Direct Kin Messaging & Asynchronous Kin Whispers (Option B).
 * Anchors private messages to family artifacts (photos, tree nodes, memories, events).
 *
 * Compliance: UK Online Safety Act 2023, UK GDPR (DPA 2018 s.9), ICO Children's Code.
 * Enforces mandatory parental oversight for minor accounts and zero-interpolation SQL.
 */
final class KinWhisperService
{
    public const int MAX_MESSAGE_LENGTH = 2000;
    public const int MAX_AUDIO_DURATION_SECONDS = 60;
    public const int MAX_AUDIO_FILE_SIZE = 2097152; // 2MB
    public const int MAX_MEDIA_FILE_SIZE = 15728640; // 15MB

    public const array ALLOWED_MEDIA_MIMES = [
        // Images
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/heic',
        // Documents
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        // Video
        'video/mp4', 'video/webm', 'video/quicktime', 'video/x-m4v', 'video/ogg'
    ];

    public const array ALLOWED_CONTEXT_TYPES = [
        'catch_up',
        'post',
        'photo',
        'memory',
        'family_node',
        'event',
        'milestone',
        'recipe',
        'prayer',
        'advice',
        'general',
    ];

    public const array ALLOWED_AUDIO_MIMES = [
        'audio/webm',
        'audio/mp4',
        'audio/mpeg',
        'audio/ogg',
        'audio/wav',
        'audio/x-m4a',
    ];

    public const array ALLOWED_REACTIONS = [
        '❤️', '🙏', '🥰', '🎉', '👏', '😂', '👍', '🎂', '🕊️', '😮',
        'family_roots_3d', 'ancestor_blessing_3d', 'family_hug_3d', 'ancestor_crown_3d', 'kinship_pride_3d'
    ];

    public const array ALLOWED_DISAPPEAR_SECONDS = [
        30,     // 30 seconds
        60,     // 1 minute
        1800,   // 30 minutes
        3600,   // 1 hour
        86400,  // 24 hours
    ];

    /** @var array<string, string> */
    private static array $avatarCache = [];

    /** @var int */
    private static int $lastPruneTimestamp = 0;

    /** @var bool */
    private static bool $schemaEnsured = false;

    /**
     * Defensive self-healing schema assurance.
     * Guarantees is_pinned and is_muted columns exist on kin_thread_participants.
     */
    public static function ensureSchema(?PDO $pdo = null): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        try {
            $db = $pdo ?? Db::connect2();
            $colPinned = $db->query("SHOW COLUMNS FROM `kin_thread_participants` LIKE 'is_pinned'");
            if (!$colPinned || !$colPinned->fetch()) {
                $db->exec("ALTER TABLE `kin_thread_participants` ADD COLUMN `is_pinned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `deleted_at`");
            }

            $colMuted = $db->query("SHOW COLUMNS FROM `kin_thread_participants` LIKE 'is_muted'");
            if (!$colMuted || !$colMuted->fetch()) {
                $db->exec("ALTER TABLE `kin_thread_participants` ADD COLUMN `is_muted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_pinned`");
            }

            self::$schemaEnsured = true;
        } catch (\Throwable $e) {
            error_log('[KinWhisperService::ensureSchema] ' . $e->getMessage());
        }
    }

    /**
     * Resolve a profile image filename or URL to a valid, publicly served asset URL.
     *
     * @param string|null $img
     * @return string
     */
    public static function resolveAvatarUrl(?string $img): string
    {
        $fallback = '/resources/images/profile/avatarM.png';
        if ($img === null) {
            return $fallback;
        }

        $img = trim($img);
        if (isset(self::$avatarCache[$img])) {
            return self::$avatarCache[$img];
        }

        if (
            $img === ''
            || str_contains($img, 'avatarM')
            || str_contains($img, 'avatarF')
            || str_contains($img, 'default-avatar')
        ) {
            $res = str_contains($img, 'avatarF')
                ? '/resources/images/profile/avatarF.png'
                : $fallback;
            return self::$avatarCache[$img] = $res;
        }

        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return self::$avatarCache[$img] = $img;
        }

        $basename = basename($img);
        if ($basename === '' || $basename === '.' || $basename === '..') {
            return self::$avatarCache[$img] = $fallback;
        }

        $baseDir = dirname(__DIR__, 2);
        if (is_file($baseDir . '/resources/images/profile/' . $basename)) {
            return self::$avatarCache[$img] = '/resources/images/profile/' . $basename;
        }
        if (is_file($baseDir . '/public/img/profile/' . $basename)) {
            return self::$avatarCache[$img] = '/public/img/profile/' . $basename;
        }

        return self::$avatarCache[$img] = $fallback;
    }

    /**
     * Create or retrieve an existing whisper thread between two kin members.
     *
     * @param string $currentUserId Authenticated sender ID
     * @param string $recipientId Target kin member ID
     * @param string $contextType Context entity type (e.g. 'family_node', 'photo', 'post')
     * @param string|null $contextId Context entity ID
     * @param string|null $contextTitle Human-readable context title (e.g. "Grandpa's 1968 Wedding")
     * @return KinThreadDto
     */
    public static function createOrGetThread(
        string $currentUserId,
        string $recipientId,
        string $contextType = 'general',
        ?string $contextId = null,
        ?string $contextTitle = null
    ): KinThreadDto {
        $currentUserId = trim($currentUserId);
        $recipientId = trim($recipientId);

        if ($currentUserId === '' || $recipientId === '') {
            throw new BadRequestException('Sender and recipient IDs must be provided.');
        }

        if ($currentUserId === $recipientId) {
            throw new ValidationException('You cannot initiate a private whisper thread with yourself.');
        }

        $contextType = strtolower(trim($contextType));
        if (!in_array($contextType, self::ALLOWED_CONTEXT_TYPES, true)) {
            $contextType = 'general';
        }

        $db = Db::connect2();

        // 1. Verify connection: user must share a family code OR have an approved requestMgt connection
        if (!self::areUsersConnected($currentUserId, $recipientId)) {
            throw new ForbiddenException('You can only send private kin whispers to members in your family network or connected via kinship requests.');
        }

        $senderCodes = self::getUserFamilyCodes($currentUserId);
        $recipientCodes = self::getUserFamilyCodes($recipientId);
        $commonCodes = array_intersect($senderCodes, $recipientCodes);

        if (!empty($commonCodes)) {
            $sharedFamilyCode = (string)reset($commonCodes);
        } else {
            // Connected via requestMgt: use primary family code of recipient or sender
            $sharedFamilyCode = !empty($recipientCodes) ? (string)reset($recipientCodes) : (!empty($senderCodes) ? (string)reset($senderCodes) : 'CONNECTED_KIN');
        }

        // 2. Child Safeguarding Gate (UK Online Safety Act 2023)
        $hasMinor = false;
        $supervisorParentId = null;

        $stmtChildSender = $db->prepare('SELECT is_child_account, adulthood_status, parent_user_id FROM account WHERE id = ? LIMIT 1');
        $stmtChildSender->execute([$currentUserId]);
        $senderAcc = $stmtChildSender->fetch(PDO::FETCH_ASSOC);

        $stmtChildRecip = $db->prepare('SELECT is_child_account, adulthood_status, parent_user_id FROM account WHERE id = ? LIMIT 1');
        $stmtChildRecip->execute([$recipientId]);
        $recipAcc = $stmtChildRecip->fetch(PDO::FETCH_ASSOC);

        $senderIsMinor = false;
        if ($senderAcc) {
            $senderIsMinor = ((int)($senderAcc['is_child_account'] ?? 0) === 1 || in_array((string)($senderAcc['adulthood_status'] ?? ''), ['minor_supervised', 'minor_teen'], true));
        }

        $recipIsMinor = false;
        if ($recipAcc) {
            $recipIsMinor = ((int)($recipAcc['is_child_account'] ?? 0) === 1 || in_array((string)($recipAcc['adulthood_status'] ?? ''), ['minor_supervised', 'minor_teen'], true));
        }

        if ($senderIsMinor || $recipIsMinor) {
            $hasMinor = true;
            if ($recipIsMinor && !empty($recipAcc['parent_user_id'])) {
                $supervisorParentId = (string)$recipAcc['parent_user_id'];
            } elseif ($senderIsMinor && !empty($senderAcc['parent_user_id'])) {
                $supervisorParentId = (string)$senderAcc['parent_user_id'];
            }
        }

        // 3. Search for existing active thread between these users
        $stmtSearch = $db->prepare(
            'SELECT * FROM kin_threads 
             WHERE ((sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?))
               AND context_type = ? 
               AND (context_id = ? OR (context_id IS NULL AND ? IS NULL))
               AND status = "active"
             LIMIT 1'
        );
        $stmtSearch->execute([
            $currentUserId, $recipientId,
            $recipientId, $currentUserId,
            $contextType,
            $contextId, $contextId
        ]);
        $existing = $stmtSearch->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return self::hydrateThreadDto($existing, $currentUserId);
        }

        // 4. Create new thread
        $threadId = 'th_' . bin2hex(random_bytes(16));
        $cleanTitle = $contextTitle !== null && trim($contextTitle) !== '' ? trim(strip_tags($contextTitle)) : null;

        $stmtInsert = $db->prepare(
            'INSERT INTO kin_threads (
                id, family_code, sender_id, recipient_id, context_type, context_id, context_title,
                has_minor_participant, supervisor_parent_id, status, last_message_at, created_at
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "active", NOW(), NOW())'
        );
        $stmtInsert->execute([
            $threadId,
            $sharedFamilyCode,
            $currentUserId,
            $recipientId,
            $contextType,
            $contextId,
            $cleanTitle,
            $hasMinor ? 1 : 0,
            $supervisorParentId
        ]);

        // Insert into kin_thread_participants for both sender and recipient
        $stmtPart = $db->prepare('INSERT IGNORE INTO kin_thread_participants (id, thread_id, user_id, role, joined_at) VALUES (?, ?, ?, "admin", NOW()), (?, ?, ?, "member", NOW())');
        $stmtPart->execute([
            'part_' . bin2hex(random_bytes(16)), $threadId, $currentUserId,
            'part_' . bin2hex(random_bytes(16)), $threadId, $recipientId,
        ]);

        return self::getThread($currentUserId, $threadId);
    }

    /**
     * Create a new multi-kin group whisper thread.
     *
     * @param string $creatorId Authenticated creator user ID
     * @param string $title Custom group title
     * @param array<string> $participantIds List of user IDs to include in group
     * @param string $contextType Artifact category
     * @param string|null $contextTitle Optional context title
     * @param string|null $contextId Optional context ID
     * @return KinThreadDto
     */
    public static function createGroupThread(
        string $creatorId,
        string $title,
        array $participantIds,
        string $contextType = 'general',
        ?string $contextTitle = null,
        ?string $contextId = null
    ): KinThreadDto {
        $creatorId = trim($creatorId);
        $title = trim(strip_tags($title));
        if ($creatorId === '') {
            throw new BadRequestException('Creator ID is required.');
        }

        if ($title === '') {
            $title = 'Family Whisper Circle';
        }

        if (mb_strlen($title) > 100) {
            $title = mb_substr($title, 0, 100);
        }

        // Deduplicate and ensure creator is included
        $cleanedIds = array_values(array_unique(array_filter(
            array_map('trim', $participantIds),
            fn($id) => $id !== ''
        )));

        if (!in_array($creatorId, $cleanedIds, true)) {
            $cleanedIds[] = $creatorId;
        }

        if (count($cleanedIds) < 2) {
            throw new ValidationException('A group whisper must contain at least 2 family members.');
        }

        if (count($cleanedIds) > 50) {
            throw new ValidationException('A group whisper cannot exceed 50 family members.');
        }

        $db = Db::connect2();

        // SecOps Red Team Invariant: Verify every non-creator member is connected to creator
        foreach ($cleanedIds as $memberId) {
            if ($memberId === $creatorId) {
                continue;
            }
            if (!self::areUsersConnected($creatorId, $memberId)) {
                throw new ValidationException(sprintf('User %s is not connected to your family network.', $memberId));
            }
        }

        // Child safeguarding inspection across all participants
        $hasMinor = false;
        $supervisorParentId = null;

        $placeholders = implode(',', array_fill(0, count($cleanedIds), '?'));
        $stmtAccounts = $db->prepare("SELECT id, is_child_account, adulthood_status, parent_user_id FROM account WHERE id IN ({$placeholders})");
        $stmtAccounts->execute($cleanedIds);
        $accountRows = $stmtAccounts->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($accountRows as $acc) {
            $isMinor = ((int)($acc['is_child_account'] ?? 0) === 1 || in_array((string)($acc['adulthood_status'] ?? ''), ['minor_supervised', 'minor_teen'], true));
            if ($isMinor) {
                $hasMinor = true;
                if (!empty($acc['parent_user_id']) && $supervisorParentId === null) {
                    $supervisorParentId = (string)$acc['parent_user_id'];
                }
            }
        }

        $creatorCodes = self::getUserFamilyCodes($creatorId);
        $primaryCode = !empty($creatorCodes) ? $creatorCodes[0] : (string)($_SESSION['famCode'] ?? 'FAMILY');

        $threadId = 'grp_' . bin2hex(random_bytes(16));
        $cleanContextTitle = $contextTitle !== null && trim($contextTitle) !== '' ? trim(strip_tags($contextTitle)) : null;

        $stmtInsert = $db->prepare(
            'INSERT INTO kin_threads (
                id, family_code, thread_type, title, created_by, sender_id, recipient_id,
                context_type, context_id, context_title, has_minor_participant,
                supervisor_parent_id, status, last_message_at, created_at
             ) VALUES (?, ?, "group", ?, ?, ?, NULL, ?, ?, ?, ?, ?, "active", NOW(), NOW())'
        );
        $stmtInsert->execute([
            $threadId,
            $primaryCode,
            $title,
            $creatorId,
            $creatorId,
            $contextType,
            $contextId,
            $cleanContextTitle,
            $hasMinor ? 1 : 0,
            $supervisorParentId
        ]);

        // Insert all participants (Creator starts with NOW(), members start with NULL until viewed)
        $stmtPart = $db->prepare('INSERT INTO kin_thread_participants (id, thread_id, user_id, role, joined_at, last_read_at) VALUES (?, ?, ?, ?, NOW(), ?)');
        foreach ($cleanedIds as $mId) {
            $isAdmin = ($mId === $creatorId);
            $role = $isAdmin ? 'admin' : 'member';
            $pId = 'part_' . bin2hex(random_bytes(16));
            $initialRead = $isAdmin ? date('Y-m-d H:i:s') : null;
            $stmtPart->execute([$pId, $threadId, $mId, $role, $initialRead]);
        }

        // Fetch creator name for system welcome note and notifications
        $stmtName = $db->prepare('SELECT CONCAT(COALESCE(firstName, ""), " ", COALESCE(lastName, "")) AS full_name FROM personal WHERE id = ? LIMIT 1');
        $stmtName->execute([$creatorId]);
        $creatorName = trim((string)($stmtName->fetchColumn() ?: 'A family member'));

        // Insert introductory system message
        $sysMsgId = 'msg_' . bin2hex(random_bytes(16));
        $welcomeBody = sprintf('%s created the group "%s".', $creatorName, $title);
        $stmtSysMsg = $db->prepare(
            'INSERT INTO kin_messages (
                id, thread_id, sender_id, recipient_id, message_type, body, is_read, created_at
             ) VALUES (?, ?, ?, NULL, "text", ?, 1, NOW())'
        );
        $stmtSysMsg->execute([$sysMsgId, $threadId, $creatorId, $welcomeBody]);

        // Broadcast notifications to non-creator participants
        $actionUrl = '/kin-whispers?thread_id=' . urlencode($threadId);
        foreach ($cleanedIds as $mId) {
            if ($mId === $creatorId) {
                continue;
            }
            NotificationOrchestrator::dispatch(
                userId: $mId,
                category: 'kin_whisper',
                priority: 'high',
                title: '👥 Added to ' . $title,
                body: sprintf('%s added you to the "%s" whisper group.', $creatorName, $title),
                actionUrl: $actionUrl,
                tag: 'group_' . $threadId,
                familyCode: $primaryCode,
                metadata: [
                    'thread_id' => $threadId,
                    'is_group' => true,
                    'creator_id' => $creatorId,
                ]
            );
        }

        // Batch multi-channel Pusher broadcast in 1 single HTTP request
        $memberChannels = [];
        foreach ($cleanedIds as $mId) {
            if ($mId !== $creatorId) {
                $memberChannels[] = 'private-user-' . strtolower((string)$mId);
            }
        }
        if (!empty($memberChannels)) {
            \App\classes\Pusher::trigger($memberChannels, 'kin-whisper', [
                'thread_id' => $threadId,
                'is_group' => true,
                'title' => $title,
            ]);
        }

        return self::getThread($creatorId, $threadId);
    }

    /**
     * Add new participants to an existing group whisper.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @param array<string> $newParticipantIds
     * @return KinThreadDto
     */
    public static function addParticipantsToGroup(string $currentUserId, string $threadId, array $newParticipantIds): KinThreadDto
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);

        $db = Db::connect2();
        $stmt = $db->prepare('SELECT * FROM kin_threads WHERE id = ? AND thread_type = "group" LIMIT 1');
        $stmt->execute([$threadId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Group thread not found.');
        }

        // Verify current user is admin (or creator)
        $stmtRole = $db->prepare('SELECT role FROM kin_thread_participants WHERE thread_id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmtRole->execute([$threadId, $currentUserId]);
        $role = $stmtRole->fetchColumn();

        if ($role !== 'admin' && (string)($thread['created_by'] ?? '') !== $currentUserId) {
            throw new ForbiddenException('Only group administrators can add new participants.');
        }

        $cleanNewIds = array_values(array_unique(array_filter(
            array_map('trim', $newParticipantIds),
            fn($id) => $id !== ''
        )));

        if (empty($cleanNewIds)) {
            return self::getThread($currentUserId, $threadId);
        }

        // Verify each new participant is connected to the group admin
        foreach ($cleanNewIds as $newId) {
            if (!self::areUsersConnected($currentUserId, $newId)) {
                throw new ValidationException(sprintf('User %s is not connected to your family network.', $newId));
            }
        }

        $stmtAdd = $db->prepare('INSERT INTO kin_thread_participants (id, thread_id, user_id, role, joined_at, last_read_at) VALUES (?, ?, ?, "member", NOW(), NULL) ON DUPLICATE KEY UPDATE deleted_at = NULL');

        foreach ($cleanNewIds as $newId) {
            $pId = 'part_' . bin2hex(random_bytes(16));
            $stmtAdd->execute([$pId, $threadId, $newId]);
        }

        return self::getThread($currentUserId, $threadId);
    }

    /**
     * Remove a participant from a group whisper (or leave group).
     *
     * @param string $currentUserId
     * @param string $threadId
     * @param string $targetUserId
     */
    public static function removeParticipantFromGroup(string $currentUserId, string $threadId, string $targetUserId): void
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);
        $targetUserId = trim($targetUserId);

        $db = Db::connect2();
        $stmt = $db->prepare('SELECT * FROM kin_threads WHERE id = ? AND thread_type = "group" LIMIT 1');
        $stmt->execute([$threadId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Group thread not found.');
        }

        $isSelf = ($currentUserId === $targetUserId);

        if (!$isSelf) {
            $stmtRole = $db->prepare('SELECT role FROM kin_thread_participants WHERE thread_id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
            $stmtRole->execute([$threadId, $currentUserId]);
            $role = $stmtRole->fetchColumn();

            if ($role !== 'admin' && (string)($thread['created_by'] ?? '') !== $currentUserId) {
                throw new ForbiddenException('Only group administrators can remove members from this group.');
            }
        }

        $stmtDel = $db->prepare('UPDATE kin_thread_participants SET deleted_at = NOW() WHERE thread_id = ? AND user_id = ?');
        $stmtDel->execute([$threadId, $targetUserId]);
    }

    /**
     * Retrieve all active participants in a thread.
     *
     * @param string $threadId
     * @param string|null $viewerUserId Optional viewer ID to mask unshared family codes
     * @return array<array<string, mixed>>
     */
    public static function getGroupParticipants(string $threadId, ?string $viewerUserId = null): array
    {
        $db = Db::connect2();
        $stmt = $db->prepare(
            'SELECT p.user_id, p.role, p.last_read_at, p.joined_at,
                    CONCAT(COALESCE(per.firstName, ""), " ", COALESCE(per.lastName, "")) AS name,
                    COALESCE(pic.img, "/avatar/avatarM.jpeg") AS avatar,
                    c.mobile,
                    per.famCode
             FROM kin_thread_participants p
             JOIN personal per ON p.user_id = per.id
             LEFT JOIN profilePics pic ON per.id = pic.id
             LEFT JOIN contact c ON per.id = c.id
             WHERE p.thread_id = ? AND p.deleted_at IS NULL
             ORDER BY (p.role = "admin") DESC, per.firstName ASC'
        );
        $stmt->execute([$threadId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $results = [];
        foreach ($rows as $r) {
            $rawFamCode = (string)($r['famCode'] ?? '');
            $canSeeCode = $viewerUserId === null || $viewerUserId === ''
                ? true
                : ((string)$r['user_id'] === $viewerUserId
                    || \App\controller\BaseController::viewerCanSeeFamilyCode($rawFamCode, $viewerUserId));
            $results[] = [
                'id' => (string)$r['user_id'],
                'name' => trim((string)$r['name']),
                'avatar' => self::resolveAvatarUrl($r['avatar'] ?? null),
                'role' => (string)$r['role'],
                'mobile' => !empty($r['mobile']) ? (string)$r['mobile'] : null,
                'fam_code' => $canSeeCode ? $rawFamCode : '',
            ];
        }

        return $results;
    }

    /**
     * Send a whisper (text note, audio dedication, or memory prompt) within a thread.
     *
     * @param string $currentUserId Authenticated sender ID
     * @param string $threadId Target thread ID
     * @param string $body Message text content
     * @param string $messageType 'text' | 'audio_whisper' | 'memory_prompt'
     * @param string|null $audioUrl Optional URL to audio recording
     * @param int|null $audioDuration Optional duration in seconds (<= 60)
     * @param array<string, mixed>|null $metadata Contextual metadata
     * @param int|null $disappearSeconds Auto-disappear duration in seconds (30, 60, 1800, 3600, 86400)
     * @param bool $deferNotifications Defer external push/socket dispatch for asynchronous execution
     * @return KinWhisperDto
     */
    public static function sendMessage(
        string $currentUserId,
        string $threadId,
        string $body,
        string $messageType = 'text',
        ?string $audioUrl = null,
        ?int $audioDuration = null,
        ?array $metadata = null,
        ?int $disappearSeconds = null,
        bool $deferNotifications = false
    ): KinWhisperDto {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);
        $cleanBody = trim(strip_tags($body));

        if ($threadId === '') {
            throw new BadRequestException('A valid thread ID is required.');
        }

        $db = Db::connect2();

        // 1. Fetch thread and assert authorization
        $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmtThread->execute([$threadId]);
        $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        $isGroup = ((string)($thread['thread_type'] ?? 'direct')) === 'group';

        if ($isGroup) {
            $recipientId = null;
            $stmtPartCheck = $db->prepare('SELECT 1 FROM kin_thread_participants WHERE thread_id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
            $stmtPartCheck->execute([$threadId, $currentUserId]);
            if (!$stmtPartCheck->fetchColumn()) {
                throw new ForbiddenException('You are not an active participant of this group whisper.');
            }
        } else {
            // Direct thread recipient
            $recipientId = ($currentUserId === (string)$thread['sender_id'])
                ? (string)$thread['recipient_id']
                : (string)$thread['sender_id'];

            // If supervisor is viewing, they cannot post on behalf of the child directly
            if ($currentUserId !== (string)$thread['sender_id'] && $currentUserId !== (string)$thread['recipient_id']) {
                throw new ForbiddenException('Supervising guardians have read-only audit permissions on minor kin threads.');
            }
        }

        // 2. Validate content
        $hasAttachments = !empty($metadata['attachments']) && is_array($metadata['attachments']);
        if ($cleanBody === '' && empty($audioUrl) && !$hasAttachments) {
            throw new ValidationException('Whisper cannot be empty. Please provide either a message, a voice dedication, or a media attachment.');
        }

        if (mb_strlen($cleanBody) > self::MAX_MESSAGE_LENGTH) {
            throw new ValidationException(sprintf('Message exceeds the maximum permitted length of %d characters.', self::MAX_MESSAGE_LENGTH));
        }

        if (!empty($audioUrl)) {
            if (!preg_match('#^/public/audio/whispers/[a-zA-Z0-9_-]+\.(webm|mp3|m4a|ogg|wav)$#', $audioUrl)) {
                throw new ValidationException('Invalid audio whisper source URL. Path traversal detected.');
            }
        }

        if ($messageType === 'audio_whisper') {
            if (empty($audioUrl)) {
                throw new ValidationException('An audio URL must be provided for audio whispers.');
            }
            if ($audioDuration !== null && ($audioDuration < 1 || $audioDuration > self::MAX_AUDIO_DURATION_SECONDS)) {
                throw new ValidationException(sprintf('Audio whispers must be between 1 and %d seconds.', self::MAX_AUDIO_DURATION_SECONDS));
            }
        } else {
            $messageType = 'text';
        }

        if ($cleanBody !== '' && empty($metadata['link_preview'])) {
            try {
                if (LinkPreviewService::extractFirstPreviewableUrl($cleanBody) !== null) {
                    $preview = LinkPreviewService::getPreviewForPostText($cleanBody);
                    if ($preview !== null && (!empty($preview['title']) || !empty($preview['image']) || !empty($preview['domain']))) {
                        $metadata = $metadata ?? [];
                        $metadata['link_preview'] = $preview;
                    }
                }
            } catch (\Throwable $e) {
                error_log('[KinWhisperService::sendMessage link_preview] ' . $e->getMessage());
            }
        }

        $msgId = 'msg_' . bin2hex(random_bytes(16));
        $metaJson = $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        $expiresAt = null;
        if ($disappearSeconds !== null && in_array($disappearSeconds, self::ALLOWED_DISAPPEAR_SECONDS, true)) {
            $expiresAt = date('Y-m-d H:i:s', time() + $disappearSeconds);
        }

        // 3. Insert message
        $stmtMsg = $db->prepare(
            'INSERT INTO kin_messages (
                id, thread_id, sender_id, recipient_id, message_type, body, audio_url,
                audio_duration_seconds, context_metadata, is_read, parent_viewed, expires_at, created_at
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, NOW())'
        );
        $stmtMsg->execute([
            $msgId,
            $threadId,
            $currentUserId,
            $recipientId,
            $messageType,
            $cleanBody,
            $audioUrl,
            $audioDuration,
            $metaJson,
            $expiresAt
        ]);

        // 4. Update thread last_message_at and sender's last_read_at
        $stmtUp = $db->prepare('UPDATE kin_threads SET last_message_at = NOW(), status = "active" WHERE id = ?');
        $stmtUp->execute([$threadId]);

        $stmtPartRead = $db->prepare('UPDATE kin_thread_participants SET last_read_at = NOW() WHERE thread_id = ? AND user_id = ?');
        $stmtPartRead->execute([$threadId, $currentUserId]);

        // 5. Multi-Channel Notifications (Synchronous or Deferred)
        if (!$deferNotifications) {
            self::dispatchMessageNotifications($currentUserId, $thread, $msgId, $cleanBody, $messageType);
        }

        return self::getMessage($msgId);
    }

    /**
     * Dispatch multi-channel notifications and push alerts for a message asynchronously or post-response.
     *
     * @param string $currentUserId Authenticated sender ID
     * @param string|array<string, mixed> $threadOrId Target thread row or ID
     * @param string $msgId Created message ID
     * @param string $cleanBody Message text preview
     * @param string $messageType 'text' | 'audio_whisper' | 'memory_prompt'
     * @param string|null $senderName Optional resolved sender display name
     */
    public static function dispatchMessageNotifications(
        string $currentUserId,
        string|array $threadOrId,
        string $msgId,
        string $cleanBody,
        string $messageType = 'text',
        ?string $senderName = null
    ): void {
        try {
            $db = Db::connect2();

            $thread = is_array($threadOrId) ? $threadOrId : null;
            $threadId = is_array($threadOrId) ? (string)($threadOrId['id'] ?? '') : (string)$threadOrId;

            if ($thread === null) {
                $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
                $stmtThread->execute([$threadId]);
                $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);
            }

            if (!$thread) {
                return;
            }

            if ($senderName === null || $senderName === '') {
                $stmtSenderName = $db->prepare('SELECT CONCAT(COALESCE(firstName, ""), " ", COALESCE(lastName, "")) AS full_name FROM personal WHERE id = ? LIMIT 1');
                $stmtSenderName->execute([$currentUserId]);
                $senderName = trim((string)($stmtSenderName->fetchColumn() ?: 'A family member'));
            }

            $isGroup = ((string)($thread['thread_type'] ?? 'direct')) === 'group';
            $actionUrl = '/kin-whispers?thread_id=' . urlencode($threadId);

            if ($isGroup) {
                $groupTitle = !empty($thread['title']) ? (string)$thread['title'] : 'Family Group';
                $notifBody = ($messageType === 'audio_whisper')
                    ? sprintf('%s shared an audio whisper in "%s".', $senderName, $groupTitle)
                    : sprintf('%s (%s): "%s"', $senderName, $groupTitle, mb_substr($cleanBody, 0, 80));

                $stmtGroupParts = $db->prepare('SELECT user_id FROM kin_thread_participants WHERE thread_id = ? AND user_id != ? AND deleted_at IS NULL');
                $stmtGroupParts->execute([$threadId, $currentUserId]);
                $recipientIds = $stmtGroupParts->fetchAll(PDO::FETCH_COLUMN) ?: [];

                foreach ($recipientIds as $recipId) {
                    $targetId = (string)$recipId;
                    NotificationOrchestrator::dispatch(
                        userId: $targetId,
                        category: 'kin_whisper',
                        priority: 'high',
                        title: '👥 ' . $groupTitle,
                        body: $notifBody,
                        actionUrl: $actionUrl,
                        tag: 'whisper_' . $threadId,
                        familyCode: (string)$thread['family_code'],
                        metadata: [
                            'sender_id' => $currentUserId,
                            'sender_name' => $senderName,
                            'thread_id' => $threadId,
                            'message_id' => $msgId,
                            'is_group' => true,
                        ]
                    );
                }

                // Batch multi-channel broadcast to all group members in 1 single network call
                $targetChannels = array_values(array_map(fn($id) => 'private-user-' . strtolower((string)$id), $recipientIds));
                if (!empty($targetChannels)) {
                    \App\classes\Pusher::trigger($targetChannels, 'kin-whisper', [
                        'thread_id' => $threadId,
                        'message_id' => $msgId,
                        'is_group' => true,
                    ]);
                }
            } else {
                $recipientId = ($currentUserId === (string)$thread['sender_id'])
                    ? (string)$thread['recipient_id']
                    : (string)$thread['sender_id'];

                $notifBody = ($messageType === 'audio_whisper')
                    ? sprintf('%s left a new audio whisper for you.', $senderName)
                    : sprintf('%s: "%s"', $senderName, mb_substr($cleanBody, 0, 80));

                if (!empty($recipientId)) {
                    NotificationOrchestrator::dispatch(
                        userId: $recipientId,
                        category: 'kin_whisper',
                        priority: 'high',
                        title: '🎙️ New Kin Whisper',
                        body: $notifBody,
                        actionUrl: $actionUrl,
                        tag: 'whisper_' . $threadId,
                        familyCode: (string)$thread['family_code'],
                        metadata: [
                            'sender_id' => $currentUserId,
                            'sender_name' => $senderName,
                            'thread_id' => $threadId,
                            'message_id' => $msgId,
                            'message_type' => $messageType,
                        ]
                    );

                    \App\classes\Pusher::trigger('private-user-' . strtolower($recipientId), 'kin-whisper', [
                        'thread_id' => $threadId,
                        'message_id' => $msgId,
                    ]);
                }
            }

            // Child Safeguarding Alert (Parent Guardian Oversight)
            $hasMinor = (int)($thread['has_minor_participant'] ?? 0) === 1;
            $supervisorId = (string)($thread['supervisor_parent_id'] ?? '');

            if ($hasMinor && $supervisorId !== '' && $supervisorId !== $currentUserId && (!isset($recipientId) || $supervisorId !== $recipientId)) {
                NotificationOrchestrator::dispatch(
                    userId: $supervisorId,
                    category: 'child_supervision',
                    priority: 'normal',
                    title: '🛡️ Minor Kin Whisper Supervision',
                    body: sprintf('New kin communication recorded involving %s and your supervised ward.', $senderName),
                    actionUrl: $actionUrl,
                    tag: 'supervise_' . $threadId,
                    familyCode: (string)$thread['family_code'],
                    metadata: [
                        'thread_id' => $threadId,
                        'is_supervision' => true,
                        'sender_id' => $currentUserId,
                    ]
                );
            }
        } catch (\Throwable $e) {
            error_log('[KinWhisperService::dispatchMessageNotifications] ' . $e->getMessage());
        }
    }

    /**
     * Retrieve a single message by ID.
     *
     * @param string $messageId
     * @return KinWhisperDto
     */
    public static function getMessage(string $messageId): KinWhisperDto
    {
        $db = Db::connect2();
        $stmt = $db->prepare(
            'SELECT m.*, 
                    CONCAT(COALESCE(p.firstName, ""), " ", COALESCE(p.lastName, "")) AS sender_name,
                    COALESCE(pic.img, "/public/images/default-avatar.png") AS sender_avatar
             FROM kin_messages m
             LEFT JOIN personal p ON m.sender_id = p.id
             LEFT JOIN profilePics pic ON m.sender_id = pic.id
             WHERE m.id = ? LIMIT 1'
        );
        $stmt->execute([$messageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Message not found.');
        }

        $row['sender_avatar'] = self::resolveAvatarUrl($row['sender_avatar'] ?? null);

        return KinWhisperDto::fromArray($row);
    }

    /**
     * Retrieve a thread by ID with security authorization check.
     *
     * @param string $currentUserId Authenticated user ID
     * @param string $threadId Thread identifier
     * @return KinThreadDto
     */
    public static function getThread(string $currentUserId, string $threadId): KinThreadDto
    {
        $db = Db::connect2();
        $stmt = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmt->execute([$threadId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        // Mark incoming messages as read for this user
        $stmtRead = $db->prepare('UPDATE kin_messages SET is_read = 1, read_at = NOW() WHERE thread_id = ? AND recipient_id = ? AND is_read = 0');
        $stmtRead->execute([$threadId, $currentUserId]);

        $stmtPartRead = $db->prepare('UPDATE kin_thread_participants SET last_read_at = NOW() WHERE thread_id = ? AND user_id = ?');
        $stmtPartRead->execute([$threadId, $currentUserId]);

        // If current user is supervising parent, mark parent_viewed
        if ((string)($thread['supervisor_parent_id'] ?? '') === $currentUserId) {
            $stmtPar = $db->prepare('UPDATE kin_messages SET parent_viewed = 1, parent_viewed_at = NOW() WHERE thread_id = ? AND parent_viewed = 0');
            $stmtPar->execute([$threadId]);
        }

        return self::hydrateThreadDto($thread, $currentUserId);
    }

    /**
     * Retrieve paginated messages for a thread.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @param int $limit Max items to return
     * @param int $offset Pagination offset
     * @return array<KinWhisperDto>
     */
    public static function getMessages(string $currentUserId, string $threadId, int $limit = 50, int $offset = 0): array
    {
        $db = Db::connect2();
        $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmtThread->execute([$threadId]);
        $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        // Mark incoming messages as read when messages are viewed
        $stmtRead = $db->prepare('UPDATE kin_messages SET is_read = 1, read_at = NOW() WHERE thread_id = ? AND recipient_id = ? AND is_read = 0');
        $stmtRead->execute([$threadId, $currentUserId]);

        $stmtPartRead = $db->prepare('UPDATE kin_thread_participants SET last_read_at = NOW() WHERE thread_id = ? AND user_id = ?');
        $stmtPartRead->execute([$threadId, $currentUserId]);

        if ((string)($thread['supervisor_parent_id'] ?? '') === $currentUserId) {
            $stmtPar = $db->prepare('UPDATE kin_messages SET parent_viewed = 1, parent_viewed_at = NOW() WHERE thread_id = ? AND parent_viewed = 0');
            $stmtPar->execute([$threadId]);
        }

        // Ephemeral Pruning: delete expired whispers and associated audio assets strictly within safe boundaries
        self::pruneExpiredWhispers();

        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);

        $stmtMsgs = $db->prepare(
            'SELECT m.*, 
                    CONCAT(COALESCE(p.firstName, ""), " ", COALESCE(p.lastName, "")) AS sender_name,
                    COALESCE(pic.img, "/public/images/default-avatar.png") AS sender_avatar
             FROM kin_messages m
             LEFT JOIN personal p ON m.sender_id = p.id
             LEFT JOIN profilePics pic ON m.sender_id = pic.id
             WHERE m.thread_id = ? AND (m.expires_at IS NULL OR m.expires_at > NOW())
             ORDER BY m.created_at ASC
             LIMIT ? OFFSET ?'
        );
        $stmtMsgs->bindValue(1, $threadId, PDO::PARAM_STR);
        $stmtMsgs->bindValue(2, $limit, PDO::PARAM_INT);
        $stmtMsgs->bindValue(3, $offset, PDO::PARAM_INT);
        $stmtMsgs->execute();

        $rows = $stmtMsgs->fetchAll(PDO::FETCH_ASSOC);
        $results = [];
        foreach ($rows as $row) {
            if (!empty($row['context_metadata'])) {
                $meta = is_string($row['context_metadata']) ? json_decode($row['context_metadata'], true) : $row['context_metadata'];
                if (is_array($meta) && !empty($meta['deleted_for_users']) && in_array($currentUserId, (array)$meta['deleted_for_users'], true)) {
                    continue;
                }
            }
            $row['sender_avatar'] = self::resolveAvatarUrl($row['sender_avatar'] ?? null);
            $results[] = KinWhisperDto::fromArray($row);
        }

        return $results;
    }

    /**
     * Toggle an emoji reaction on a message.
     *
     * @param string $currentUserId
     * @param string $messageId
     * @param string $emoji
     * @return KinWhisperDto
     */
    public static function toggleReaction(string $currentUserId, string $messageId, string $emoji): KinWhisperDto
    {
        $currentUserId = trim($currentUserId);
        $messageId = trim($messageId);
        $emoji = trim($emoji);

        if ($currentUserId === '' || $messageId === '') {
            throw new BadRequestException('User ID and message ID are required.');
        }

        if (!in_array($emoji, self::ALLOWED_REACTIONS, true)) {
            throw new ValidationException('Invalid reaction emoji.');
        }

        $db = Db::connect2();

        $stmt = $db->prepare('SELECT m.*, t.sender_id AS thread_sender_id, t.recipient_id AS thread_recipient_id, t.supervisor_parent_id, t.has_minor_participant FROM kin_messages m JOIN kin_threads t ON m.thread_id = t.id WHERE m.id = ? LIMIT 1');
        $stmt->execute([$messageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Message not found.');
        }

        self::assertCanAccessThread($currentUserId, [
            'sender_id' => (string)$row['thread_sender_id'],
            'recipient_id' => (string)$row['thread_recipient_id'],
            'supervisor_parent_id' => isset($row['supervisor_parent_id']) ? (string)$row['supervisor_parent_id'] : null,
            'has_minor_participant' => (int)($row['has_minor_participant'] ?? 0),
        ]);

        $meta = [];
        if (!empty($row['context_metadata'])) {
            if (is_array($row['context_metadata'])) {
                $meta = $row['context_metadata'];
            } elseif (is_string($row['context_metadata'])) {
                $decoded = json_decode($row['context_metadata'], true);
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            }
        }

        /** @var array<string, list<string>> $reactions */
        $reactions = isset($meta['reactions']) && is_array($meta['reactions']) ? $meta['reactions'] : [];

        $userList = isset($reactions[$emoji]) ? $reactions[$emoji] : [];
        if (in_array($currentUserId, $userList, true)) {
            $userList = array_values(array_filter($userList, fn($uid) => $uid !== $currentUserId));
        } else {
            $userList[] = $currentUserId;
        }

        if (empty($userList)) {
            unset($reactions[$emoji]);
        } else {
            $reactions[$emoji] = $userList;
        }

        $meta['reactions'] = $reactions;
        $metaJson = json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $stmtUp = $db->prepare('UPDATE kin_messages SET context_metadata = ? WHERE id = ?');
        $stmtUp->execute([$metaJson, $messageId]);

        return self::getMessage($messageId);
    }

    /**
     * Delete a whisper message.
     * Supports mode 'everyone' (sender only, purges row and unlinks audio) or 'me' (hides for current user).
     *
     * @param string $currentUserId
     * @param string $messageId
     * @param string $mode 'everyone' | 'me'
     * @return array<string, mixed>
     * @throws BadRequestException|NotFoundException|ForbiddenException
     */
    public static function deleteMessage(string $currentUserId, string $messageId, string $mode = 'everyone'): array
    {
        $currentUserId = trim($currentUserId);
        $messageId = trim($messageId);
        $mode = in_array($mode, ['everyone', 'me'], true) ? $mode : 'everyone';

        if ($currentUserId === '' || $messageId === '') {
            throw new BadRequestException('User ID and message ID are required.');
        }

        $db = Db::connect2();

        $stmt = $db->prepare(
            'SELECT m.*, t.thread_type, t.sender_id AS thread_sender_id, t.recipient_id AS thread_recipient_id,
                    t.supervisor_parent_id, t.has_minor_participant
             FROM kin_messages m
             JOIN kin_threads t ON m.thread_id = t.id
             WHERE m.id = ? LIMIT 1'
        );
        $stmt->execute([$messageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Message not found.');
        }

        self::assertCanAccessThread($currentUserId, [
            'id' => (string)$row['thread_id'],
            'thread_type' => (string)($row['thread_type'] ?? 'direct'),
            'sender_id' => (string)$row['thread_sender_id'],
            'recipient_id' => (string)$row['thread_recipient_id'],
            'supervisor_parent_id' => isset($row['supervisor_parent_id']) ? (string)$row['supervisor_parent_id'] : null,
            'has_minor_participant' => (int)($row['has_minor_participant'] ?? 0),
        ]);

        $threadId = (string)$row['thread_id'];
        $audioUrl = isset($row['audio_url']) ? (string)$row['audio_url'] : null;

        if ($mode === 'me') {
            $meta = [];
            if (!empty($row['context_metadata'])) {
                $decoded = is_string($row['context_metadata']) ? json_decode($row['context_metadata'], true) : $row['context_metadata'];
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            }
            $delUsers = isset($meta['deleted_for_users']) && is_array($meta['deleted_for_users']) ? $meta['deleted_for_users'] : [];
            if (!in_array($currentUserId, $delUsers, true)) {
                $delUsers[] = $currentUserId;
            }
            $meta['deleted_for_users'] = $delUsers;
            $metaJson = json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $stmtUp = $db->prepare('UPDATE kin_messages SET context_metadata = ? WHERE id = ?');
            $stmtUp->execute([$metaJson, $messageId]);

            return [
                'deleted_message_id' => $messageId,
                'thread_id' => $threadId,
                'mode' => 'me'
            ];
        }

        // BOLA Prevention: Strictly enforce sender-only message deletion for everyone
        if ((string)$row['sender_id'] !== $currentUserId) {
            throw new ForbiddenException('You can only delete your own messages.');
        }

        $db->beginTransaction();
        try {
            $stmtDel = $db->prepare('DELETE FROM kin_messages WHERE id = ? AND sender_id = ? LIMIT 1');
            $stmtDel->execute([$messageId, $currentUserId]);

            $stmtLatest = $db->prepare(
                'SELECT COALESCE(NULLIF(body, ""), "Voice whisper") AS preview, created_at
                 FROM kin_messages
                 WHERE thread_id = ? AND (expires_at IS NULL OR expires_at > NOW())
                 ORDER BY created_at DESC
                 LIMIT 1'
            );
            $stmtLatest->execute([$threadId]);
            $latest = $stmtLatest->fetch(PDO::FETCH_ASSOC);

            $lastPreview = $latest ? (string)$latest['preview'] : 'No messages yet';
            $lastAt = $latest ? (string)$latest['created_at'] : null;

            if ($lastAt !== null) {
                $stmtThreadUp = $db->prepare('UPDATE kin_threads SET last_message_at = ? WHERE id = ?');
                $stmtThreadUp->execute([$lastAt, $threadId]);
            }

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }

        self::unlinkAudioAsset($audioUrl);

        // Broadcast deletion over real-time Pusher channels cleanly (non-blocking)
        try {
            $isGroup = ($row['thread_type'] ?? 'direct') === 'group';
            if ($isGroup) {
                $stmtParts = $db->prepare('SELECT user_id FROM kin_thread_participants WHERE thread_id = ? AND deleted_at IS NULL');
                $stmtParts->execute([$threadId]);
                $allParts = $stmtParts->fetchAll(PDO::FETCH_COLUMN) ?: [];
            } else {
                $allParts = array_filter([(string)$row['thread_sender_id'], (string)$row['thread_recipient_id']]);
            }

            if ((int)($row['has_minor_participant'] ?? 0) === 1 && !empty($row['supervisor_parent_id'])) {
                $allParts[] = (string)$row['supervisor_parent_id'];
            }

            $allParts = array_values(array_unique(array_filter(array_map('strval', $allParts))));
            $channels = array_map(fn(string $id): string => 'private-user-' . strtolower($id), $allParts);

            if (!empty($channels)) {
                \App\classes\Pusher::trigger($channels, 'kin-whisper-deleted', [
                    'thread_id' => $threadId,
                    'message_id' => $messageId,
                    'deleted_by' => $currentUserId,
                    'last_message_preview' => $lastPreview,
                    'last_message_at' => $lastAt,
                ]);
            }
        } catch (\Throwable $e) {
            error_log('[KinWhisperService::deleteMessage Pusher] ' . $e->getMessage());
        }

        return [
            'deleted_message_id' => $messageId,
            'thread_id' => $threadId,
            'last_message_preview' => $lastPreview,
            'last_message_at' => $lastAt,
        ];
    }

    /**
     * Explicitly mark all unread messages in a thread as seen/read.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return int Number of messages marked as read
     */
    public static function markThreadAsRead(string $currentUserId, string $threadId): int
    {
        $db = Db::connect2();
        $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmtThread->execute([$threadId]);
        $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        $stmtRead = $db->prepare('UPDATE kin_messages SET is_read = 1, read_at = NOW() WHERE thread_id = ? AND recipient_id = ? AND is_read = 0');
        $stmtRead->execute([$threadId, $currentUserId]);
        $count = (int)$stmtRead->rowCount();

        $stmtPartRead = $db->prepare('UPDATE kin_thread_participants SET last_read_at = NOW() WHERE thread_id = ? AND user_id = ?');
        $stmtPartRead->execute([$threadId, $currentUserId]);

        if ((string)($thread['supervisor_parent_id'] ?? '') === $currentUserId) {
            $stmtPar = $db->prepare('UPDATE kin_messages SET parent_viewed = 1, parent_viewed_at = NOW() WHERE thread_id = ? AND parent_viewed = 0');
            $stmtPar->execute([$threadId]);
        }

        return $count;
    }

    /**
     * List all active whisper threads for a user (as participant or supervising parent).
     *
     * @param string $currentUserId
     * @return array<KinThreadDto>
     */
    public static function listUserThreads(string $currentUserId): array
    {
        // Opportunistic cleanup of expired whispers
        self::pruneExpiredWhispers();

        $db = Db::connect2();
        self::ensureSchema($db);

        try {
            $stmt = $db->prepare(
                'SELECT t.*,
                        COALESCE((SELECT is_pinned FROM kin_thread_participants WHERE thread_id = t.id AND user_id = ? AND deleted_at IS NULL LIMIT 1), 0) AS is_pinned,
                        COALESCE((SELECT is_muted FROM kin_thread_participants WHERE thread_id = t.id AND user_id = ? AND deleted_at IS NULL LIMIT 1), 0) AS is_muted,
                        (SELECT COALESCE(NULLIF(body, ""), "Voice whisper") FROM kin_messages WHERE thread_id = t.id AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC LIMIT 1) AS last_message_preview,
                        CASE 
                            WHEN t.thread_type = "group" THEN (
                                SELECT COUNT(*) 
                                FROM kin_messages m 
                                JOIN kin_thread_participants p ON m.thread_id = p.thread_id AND p.user_id = ? AND p.deleted_at IS NULL
                                WHERE m.thread_id = t.id 
                                  AND m.recipient_id IS NULL 
                                  AND m.sender_id != ? 
                                  AND (p.last_read_at IS NULL OR m.created_at > p.last_read_at)
                                  AND (m.expires_at IS NULL OR m.expires_at > NOW())
                            )
                            ELSE (
                                SELECT COUNT(*) 
                                FROM kin_messages 
                                WHERE thread_id = t.id 
                                  AND recipient_id = ? 
                                  AND is_read = 0 
                                  AND (expires_at IS NULL OR expires_at > NOW())
                            )
                        END AS unread_count
                 FROM kin_threads t
                 WHERE (
                     EXISTS (SELECT 1 FROM kin_thread_participants p WHERE p.thread_id = t.id AND p.user_id = ? AND p.deleted_at IS NULL)
                     OR t.sender_id = ? 
                     OR t.recipient_id = ? 
                     OR (t.has_minor_participant = 1 AND t.supervisor_parent_id = ?)
                 )
                   AND t.status != "blocked"
                 ORDER BY is_pinned DESC, t.last_message_at DESC
                 LIMIT 100'
            );
            $stmt->execute([
                $currentUserId, $currentUserId,
                $currentUserId, $currentUserId, $currentUserId,
                $currentUserId, $currentUserId, $currentUserId, $currentUserId
            ]);
        } catch (\PDOException) {
            // Defensive fallback if is_pinned or is_muted columns cannot be queried or forward reference occurs
            $stmt = $db->prepare(
                'SELECT t.*,
                        0 AS is_pinned,
                        0 AS is_muted,
                        (SELECT COALESCE(NULLIF(body, ""), "Voice whisper") FROM kin_messages WHERE thread_id = t.id AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC LIMIT 1) AS last_message_preview,
                        CASE 
                            WHEN t.thread_type = "group" THEN (
                                SELECT COUNT(*) 
                                FROM kin_messages m 
                                JOIN kin_thread_participants p ON m.thread_id = p.thread_id AND p.user_id = ? AND p.deleted_at IS NULL
                                WHERE m.thread_id = t.id 
                                  AND m.recipient_id IS NULL 
                                  AND m.sender_id != ? 
                                  AND (p.last_read_at IS NULL OR m.created_at > p.last_read_at)
                                  AND (m.expires_at IS NULL OR m.expires_at > NOW())
                            )
                            ELSE (
                                SELECT COUNT(*) 
                                FROM kin_messages 
                                WHERE thread_id = t.id 
                                  AND recipient_id = ? 
                                  AND is_read = 0 
                                  AND (expires_at IS NULL OR expires_at > NOW())
                            )
                        END AS unread_count
                 FROM kin_threads t
                 WHERE (
                     EXISTS (SELECT 1 FROM kin_thread_participants p WHERE p.thread_id = t.id AND p.user_id = ? AND p.deleted_at IS NULL)
                     OR t.sender_id = ? 
                     OR t.recipient_id = ? 
                     OR (t.has_minor_participant = 1 AND t.supervisor_parent_id = ?)
                 )
                   AND t.status != "blocked"
                 ORDER BY t.last_message_at DESC
                 LIMIT 100'
            );
            $stmt->execute([
                $currentUserId, $currentUserId, $currentUserId,
                $currentUserId, $currentUserId, $currentUserId, $currentUserId
            ]);
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return [];
        }

        // Batch-resolve user profiles to eliminate N+1 queries
        $neededUserIds = [];
        foreach ($rows as $r) {
            $sId = (string)($r['sender_id'] ?? '');
            $recId = (string)($r['recipient_id'] ?? '');
            if ($sId !== '') {
                $neededUserIds[$sId] = true;
            }
            if ($recId !== '') {
                $neededUserIds[$recId] = true;
            }
        }

        $userProfileMap = [];
        if (!empty($neededUserIds)) {
            $uIds = array_keys($neededUserIds);
            $inClause = implode(',', array_fill(0, count($uIds), '?'));
            $stmtUsers = $db->prepare("SELECT p.id, CONCAT(COALESCE(p.firstName, ''), ' ', COALESCE(p.lastName, '')) AS full_name, COALESCE(pic.img, '/avatar/avatarM.jpeg') AS avatar FROM personal p LEFT JOIN profilePics pic ON p.id = pic.id WHERE p.id IN ({$inClause})");
            $stmtUsers->execute($uIds);
            foreach ($stmtUsers->fetchAll(PDO::FETCH_ASSOC) as $uRow) {
                $userProfileMap[(string)$uRow['id']] = [
                    'name' => trim((string)$uRow['full_name']),
                    'avatar' => self::resolveAvatarUrl($uRow['avatar'] ?? null),
                ];
            }
        }

        $results = [];
        foreach ($rows as $row) {
            $results[] = self::hydrateThreadDto($row, $currentUserId, $userProfileMap);
        }

        return $results;
    }

    /**
     * Securely unlink an audio whisper asset if it resides strictly within public/audio/whispers.
     */
    private static function unlinkAudioAsset(?string $audioUrl): void
    {
        if ($audioUrl === null || trim($audioUrl) === '') {
            return;
        }
        $baseDir = dirname(__DIR__, 2);
        $whisperBase = realpath($baseDir . '/public/audio/whispers') ?: ($baseDir . '/public/audio/whispers');
        $realFPath = realpath($baseDir . '/' . ltrim(trim($audioUrl), '/'));
        if ($realFPath && str_starts_with($realFPath, (string)$whisperBase . DIRECTORY_SEPARATOR) && is_file($realFPath)) {
            @unlink($realFPath);
        }
    }

    /**
     * Purge expired ephemeral whispers and unlinks associated audio assets securely.
     */
    public static function pruneExpiredWhispers(bool $force = false): int
    {
        $now = time();
        if (!$force && (self::$lastPruneTimestamp > 0) && ($now - self::$lastPruneTimestamp < 60)) {
            return 0;
        }
        self::$lastPruneTimestamp = $now;

        $db = Db::connect2();
        $stmtExp = $db->query('SELECT audio_url FROM kin_messages WHERE expires_at IS NOT NULL AND expires_at <= NOW() AND audio_url IS NOT NULL');
        if ($stmtExp) {
            foreach ($stmtExp->fetchAll(PDO::FETCH_COLUMN) as $expUrl) {
                self::unlinkAudioAsset((string)$expUrl);
            }
        }
        return (int)$db->exec('DELETE FROM kin_messages WHERE expires_at IS NOT NULL AND expires_at <= NOW()');
    }

    /**
     * Get all active family codes for a user (primary + approved in user_families).
     *
     * @param string $userId
     * @return array<string>
     */
    public static function getUserFamilyCodes(string $userId): array
    {
        $userId = trim($userId);
        if ($userId === '') {
            return [];
        }

        $db = Db::connect2();
        $codes = [];

        $stmt = $db->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $primary = strtoupper(ltrim(trim((string)($stmt->fetchColumn() ?: '')), '#'));
        if ($primary !== '') {
            $codes[$primary] = true;
        }

        try {
            $stmtUf = $db->prepare('SELECT family_code FROM user_families WHERE user_id = ? AND status = "approved"');
            $stmtUf->execute([$userId]);
            foreach ($stmtUf->fetchAll(PDO::FETCH_COLUMN) as $fc) {
                $norm = strtoupper(ltrim(trim((string)$fc), '#'));
                if ($norm !== '') {
                    $codes[$norm] = true;
                }
            }
        } catch (\Throwable) {}

        return array_keys($codes);
    }

    /**
     * Determine if two users are legally connected either via family code or approved kinship request.
     *
     * @param string $userIdA
     * @param string $userIdB
     * @return bool
     */
    public static function areUsersConnected(string $userIdA, string $userIdB): bool
    {
        $userIdA = trim($userIdA);
        $userIdB = trim($userIdB);

        if ($userIdA === '' || $userIdB === '' || $userIdA === $userIdB) {
            return false;
        }

        $db = Db::connect2();

        // 1. Check family code intersection
        $codesA = self::getUserFamilyCodes($userIdA);
        $codesB = self::getUserFamilyCodes($userIdB);
        if (!empty(array_intersect($codesA, $codesB))) {
            return true;
        }

        // 2. Check approved connection in requestMgt
        $stmtReq = $db->prepare(
            'SELECT 1 FROM requestMgt
             WHERE LOWER(status) IN ("approved", "accepted")
               AND ((approver_id = ? AND requester_id = ?) OR (approver_id = ? AND requester_id = ?))
             LIMIT 1'
        );
        $stmtReq->execute([$userIdA, $userIdB, $userIdB, $userIdA]);

        return (bool)$stmtReq->fetchColumn();
    }

    /**
     * Retrieve all eligible kin members a user can whisper to (via family codes or approved requests).
     *
     * @param string $currentUserId
     * @return array<array<string, string>>
     */
    public static function getConnectedKinMembers(string $currentUserId): array
    {
        $currentUserId = trim($currentUserId);
        if ($currentUserId === '') {
            return [];
        }

        $db = Db::connect2();
        $codes = self::getUserFamilyCodes($currentUserId);

        $params = [$currentUserId, $currentUserId, $currentUserId];
        $famCondition = '1=0';

        if (!empty($codes)) {
            $placeholders = implode(',', array_fill(0, count($codes), '?'));
            $famCondition = "(
                UPPER(TRIM(REPLACE(p.famCode, '#', ''))) IN ({$placeholders})
                OR EXISTS (
                    SELECT 1 FROM user_families uf 
                    WHERE uf.user_id = p.id 
                      AND UPPER(TRIM(REPLACE(uf.family_code, '#', ''))) IN ({$placeholders})
                      AND uf.status = 'approved'
                )
            )";
            $params = array_merge($params, $codes, $codes);
        }

        $sql = "
            SELECT DISTINCT
                p.id,
                p.firstName,
                p.lastName,
                CONCAT(COALESCE(p.firstName, ''), ' ', COALESCE(p.lastName, '')) AS name,
                p.famCode,
                COALESCE(pic.img, '/avatar/avatarM.jpeg') AS avatar,
                rm.relationship_role,
                rm.relationship_category,
                CASE 
                    WHEN rm.no IS NOT NULL THEN 'connected_kin'
                    ELSE 'family_code'
                END AS connection_type
            FROM personal p
            LEFT JOIN profilePics pic ON p.id = pic.id
            LEFT JOIN requestMgt rm ON (
                ((rm.requester_id = p.id AND rm.approver_id = ?) OR (rm.approver_id = p.id AND rm.requester_id = ?))
                AND LOWER(rm.status) IN ('approved', 'accepted')
            )
            WHERE p.deleted_at IS NULL
              AND p.id != ?
              AND ({$famCondition} OR rm.no IS NOT NULL)
            ORDER BY p.firstName ASC, p.lastName ASC
            LIMIT 300
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $results = [];
        $codesSet = array_flip($codes);
        $isSuper = \App\controller\BaseController::isSuperAdmin($currentUserId);

        foreach ($rows as $row) {
            $memberId = (string)$row['id'];
            $rawFamCode = (string)($row['famCode'] ?? '');
            $normCode = strtoupper(ltrim(trim($rawFamCode), '#'));
            $canSeeCode = $isSuper || ($normCode !== '' && isset($codesSet[$normCode]));
            $isReq = ((string)$row['connection_type']) === 'connected_kin';
            $badge = $canSeeCode && $rawFamCode !== '' ? '#' . ltrim($rawFamCode, '#') : 'Connected Kin';
            $desc = $canSeeCode && $rawFamCode !== '' ? ('Family Code: ' . $badge) : 'Connected Kin Network';

            if ($isReq) {
                $role = trim((string)($row['relationship_role'] ?? ''));
                $cat = trim((string)($row['relationship_category'] ?? ''));
                $badge = $role !== '' ? $role : ($cat !== '' ? $cat : 'Connected Kin');
                $desc = $canSeeCode && $rawFamCode !== ''
                    ? ('Connected via Request • #' . ltrim($rawFamCode, '#'))
                    : 'Connected via Kinship Request';
            }

            $results[] = [
                'id' => $memberId,
                'name' => trim((string)$row['name']),
                'avatar' => self::resolveAvatarUrl($row['avatar'] ?? null),
                'famCode' => $canSeeCode ? $rawFamCode : '',
                'connection_type' => (string)$row['connection_type'],
                'badge' => $badge,
                'description' => $desc,
            ];
        }

        return $results;
    }

    /**
     * Get the total unread whisper count for a user.
     *
     * @param string $userId
     * @return int
     */
    public static function getUnreadWhisperCount(string $userId): int
    {
        $userId = trim($userId);
        if ($userId === '') {
            return 0;
        }

        $db = Db::connect2();

        // 1. Direct unread messages
        $stmtDirect = $db->prepare(
            'SELECT COUNT(*) FROM kin_messages
             WHERE recipient_id = ?
               AND is_read = 0
               AND (expires_at IS NULL OR expires_at > NOW())'
        );
        $stmtDirect->execute([$userId]);
        $directCount = (int)$stmtDirect->fetchColumn();

        // 2. Group unread messages
        $stmtGroup = $db->prepare(
            'SELECT COUNT(*) FROM kin_messages m
             JOIN kin_thread_participants p ON m.thread_id = p.thread_id AND p.user_id = ? AND p.deleted_at IS NULL
             WHERE m.recipient_id IS NULL
               AND m.sender_id != ?
               AND (p.last_read_at IS NULL OR m.created_at > p.last_read_at)
               AND (m.expires_at IS NULL OR m.expires_at > NOW())'
        );
        $stmtGroup->execute([$userId, $userId]);
        $groupCount = (int)$stmtGroup->fetchColumn();

        return $directCount + $groupCount;
    }

    /**
     * Store an audio whisper voice file safely.
     *
     * @param string $currentUserId
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file
     * @return array{url: string, duration: int}
     */
    public static function uploadAudioWhisper(string $currentUserId, array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new ValidationException('Failed to upload audio file. Please retry.');
        }

        if ($file['size'] > self::MAX_AUDIO_FILE_SIZE) {
            throw new ValidationException('Audio file exceeds the maximum permitted size of 2MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file((string)$file['tmp_name']);

        if (!in_array($mime, self::ALLOWED_AUDIO_MIMES, true) && !str_starts_with($mime, 'audio/')) {
            throw new ValidationException('Invalid audio file format. Only webm, mp3, mp4, ogg, or wav files are allowed.');
        }

        $targetDir = dirname(__DIR__, 2) . '/public/audio/whispers';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $extension = 'webm';
        if (str_contains($mime, 'mpeg') || str_contains($mime, 'mp3')) {
            $extension = 'mp3';
        } elseif (str_contains($mime, 'mp4') || str_contains($mime, 'm4a')) {
            $extension = 'm4a';
        } elseif (str_contains($mime, 'ogg')) {
            $extension = 'ogg';
        } elseif (str_contains($mime, 'wav')) {
            $extension = 'wav';
        }

        $filename = sprintf('whisper_%s_%s.%s', bin2hex(random_bytes(8)), date('Ymd_His'), $extension);
        $destination = $targetDir . '/' . $filename;

        if (!move_uploaded_file((string)$file['tmp_name'], $destination)) {
            throw new ValidationException('Could not save uploaded audio.');
        }

        $publicUrl = '/public/audio/whispers/' . $filename;

        return [
            'url' => $publicUrl,
            'duration' => 30, // Default duration estimation
        ];
    }

    /**
     * Upload a rich media attachment (Photo, Video, Document) for a whisper.
     * Enforces UK Online Safety Act content restrictions, MIME inspection, and 10MB limits.
     *
     * @param string $currentUserId
     * @param array<string, mixed> $file
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public static function uploadMediaWhisper(string $currentUserId, array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new ValidationException('Failed to upload media file. Please retry.');
        }

        if ((int)($file['size'] ?? 0) > self::MAX_MEDIA_FILE_SIZE) {
            throw new ValidationException('Uploaded file exceeds the maximum permitted size of 15MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file((string)$file['tmp_name']);

        if (!in_array($mime, self::ALLOWED_MEDIA_MIMES, true)) {
            throw new ValidationException('Unsupported file format. Please upload an image (JPG, PNG, WebP), document (PDF, Word), or video (MP4, WebM, MOV).');
        }

        $targetDir = dirname(__DIR__, 2) . '/public/storage/whispers/media';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $type = 'document';
        $ext = 'bin';
        if (str_starts_with($mime, 'image/')) {
            $type = 'image';
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                'image/heic' => 'heic',
                default => 'jpg'
            };
        } elseif (str_starts_with($mime, 'video/')) {
            $type = 'video';
            $ext = match ($mime) {
                'video/mp4' => 'mp4',
                'video/webm' => 'webm',
                'video/quicktime' => 'mov',
                'video/x-m4v' => 'm4v',
                'video/ogg' => 'ogv',
                default => 'mp4'
            };
        } elseif ($mime === 'application/pdf') {
            $ext = 'pdf';
        } elseif (str_contains($mime, 'word')) {
            $ext = 'docx';
        } elseif ($mime === 'text/plain') {
            $ext = 'txt';
        }

        $filename = sprintf('media_%s_%s.%s', bin2hex(random_bytes(8)), date('Ymd_His'), $ext);
        $destination = $targetDir . '/' . $filename;

        $isHttpUpload = is_uploaded_file((string)$file['tmp_name']);
        $moved = $isHttpUpload
            ? move_uploaded_file((string)$file['tmp_name'], $destination)
            : copy((string)$file['tmp_name'], $destination);

        if (!$moved) {
            throw new ValidationException('Could not save uploaded media.');
        }

        $origName = basename(strip_tags((string)($file['name'] ?? 'attachment.' . $ext)));
        return [
            'url' => '/public/storage/whispers/media/' . $filename,
            'name' => $origName,
            'type' => $type,
            'size' => (int)$file['size'],
            'mime' => $mime,
        ];
    }

    /**
     * Toggle bookmark/star on a whisper message.
     *
     * @param string $currentUserId
     * @param string $messageId
     * @return array<string, mixed>
     */
    public static function toggleStar(string $currentUserId, string $messageId): array
    {
        $currentUserId = trim($currentUserId);
        $messageId = trim($messageId);

        if ($currentUserId === '' || $messageId === '') {
            throw new BadRequestException('User ID and message ID are required.');
        }

        $db = Db::connect2();
        $stmt = $db->prepare('SELECT m.*, t.sender_id AS thread_sender_id, t.recipient_id AS thread_recipient_id, t.supervisor_parent_id, t.has_minor_participant FROM kin_messages m JOIN kin_threads t ON m.thread_id = t.id WHERE m.id = ? LIMIT 1');
        $stmt->execute([$messageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Message not found.');
        }

        self::assertCanAccessThread($currentUserId, [
            'sender_id' => (string)$row['thread_sender_id'],
            'recipient_id' => (string)$row['thread_recipient_id'],
            'supervisor_parent_id' => isset($row['supervisor_parent_id']) ? (string)$row['supervisor_parent_id'] : null,
            'has_minor_participant' => (int)($row['has_minor_participant'] ?? 0),
        ]);

        $meta = [];
        if (!empty($row['context_metadata'])) {
            $decoded = is_string($row['context_metadata']) ? json_decode($row['context_metadata'], true) : $row['context_metadata'];
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        /** @var array<string> $starredBy */
        $starredBy = isset($meta['starred_by']) && is_array($meta['starred_by']) ? $meta['starred_by'] : [];
        $isStarred = false;

        if (in_array($currentUserId, $starredBy, true)) {
            $starredBy = array_values(array_diff($starredBy, [$currentUserId]));
            $isStarred = false;
        } else {
            $starredBy[] = $currentUserId;
            $isStarred = true;
        }

        $meta['starred_by'] = $starredBy;
        $metaJson = json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $stmtUp = $db->prepare('UPDATE kin_messages SET context_metadata = ? WHERE id = ?');
        $stmtUp->execute([$metaJson, $messageId]);

        return [
            'message_id' => $messageId,
            'is_starred' => $isStarred,
        ];
    }

    /**
     * Edit a whisper message (WhatsApp 15-minute window invariant).
     *
     * @param string $currentUserId
     * @param string $messageId
     * @param string $newBody
     * @return KinWhisperDto
     */
    public static function editMessage(string $currentUserId, string $messageId, string $newBody): KinWhisperDto
    {
        $currentUserId = trim($currentUserId);
        $messageId = trim($messageId);
        $cleanBody = trim(strip_tags($newBody));

        if ($currentUserId === '' || $messageId === '') {
            throw new BadRequestException('User ID and message ID are required.');
        }

        if ($cleanBody === '') {
            throw new ValidationException('Edited whisper cannot be empty.');
        }

        if (mb_strlen($cleanBody) > self::MAX_MESSAGE_LENGTH) {
            throw new ValidationException(sprintf('Message exceeds %d characters.', self::MAX_MESSAGE_LENGTH));
        }

        $db = Db::connect2();
        $stmt = $db->prepare('SELECT m.*, t.sender_id AS thread_sender_id, t.recipient_id AS thread_recipient_id, t.supervisor_parent_id, t.has_minor_participant FROM kin_messages m JOIN kin_threads t ON m.thread_id = t.id WHERE m.id = ? LIMIT 1');
        $stmt->execute([$messageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Message not found.');
        }

        if ((string)$row['sender_id'] !== $currentUserId) {
            throw new ForbiddenException('You can only edit your own messages.');
        }

        // WhatsApp 15-minute edit window check:
        $createdTs = strtotime((string)$row['created_at']);
        if ($createdTs < (time() - 900)) {
            throw new ValidationException('Whispers can only be edited within 15 minutes of sending.');
        }

        $meta = [];
        if (!empty($row['context_metadata'])) {
            $decoded = is_string($row['context_metadata']) ? json_decode($row['context_metadata'], true) : $row['context_metadata'];
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        $meta['is_edited'] = true;
        $meta['edited_at'] = date('Y-m-d H:i:s');
        try {
            if (LinkPreviewService::extractFirstPreviewableUrl($cleanBody) !== null) {
                $preview = LinkPreviewService::getPreviewForPostText($cleanBody);
                if ($preview !== null && (!empty($preview['title']) || !empty($preview['image']) || !empty($preview['domain']))) {
                    $meta['link_preview'] = $preview;
                } else {
                    unset($meta['link_preview']);
                }
            } else {
                unset($meta['link_preview']);
            }
        } catch (\Throwable $e) {
            error_log('[KinWhisperService::editMessage link_preview] ' . $e->getMessage());
        }
        $metaJson = json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $stmtUp = $db->prepare('UPDATE kin_messages SET body = ?, context_metadata = ? WHERE id = ? AND sender_id = ?');
        $stmtUp->execute([$cleanBody, $metaJson, $messageId, $currentUserId]);

        return self::getMessage($messageId);
    }

    /**
     * Toggle pinned state for a thread.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return bool
     */
    public static function togglePinThread(string $currentUserId, string $threadId): bool
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);

        $db = Db::connect2();
        self::ensureSchema($db);
        $stmt = $db->prepare('SELECT is_pinned FROM kin_thread_participants WHERE thread_id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$threadId, $currentUserId]);
        $val = $stmt->fetchColumn();

        if ($val === false) {
            throw new ForbiddenException('You are not an active participant in this thread.');
        }

        $newVal = ((int)$val === 1) ? 0 : 1;
        $stmtUp = $db->prepare('UPDATE kin_thread_participants SET is_pinned = ? WHERE thread_id = ? AND user_id = ?');
        $stmtUp->execute([$newVal, $threadId, $currentUserId]);

        return $newVal === 1;
    }

    /**
     * Toggle muted state for a thread.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return bool
     */
    public static function toggleMuteThread(string $currentUserId, string $threadId): bool
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);

        $db = Db::connect2();
        self::ensureSchema($db);
        $stmt = $db->prepare('SELECT is_muted FROM kin_thread_participants WHERE thread_id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$threadId, $currentUserId]);
        $val = $stmt->fetchColumn();

        if ($val === false) {
            throw new ForbiddenException('You are not an active participant in this thread.');
        }

        $newVal = ((int)$val === 1) ? 0 : 1;
        $stmtUp = $db->prepare('UPDATE kin_thread_participants SET is_muted = ? WHERE thread_id = ? AND user_id = ?');
        $stmtUp->execute([$newVal, $threadId, $currentUserId]);

        return $newVal === 1;
    }

    /**
     * Retrieve all shared media (photos, documents, audio) for a thread.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function getThreadMedia(string $currentUserId, string $threadId): array
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);

        $db = Db::connect2();
        $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmtThread->execute([$threadId]);
        $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        $stmt = $db->prepare('SELECT id, sender_id, message_type, body, audio_url, audio_duration_seconds, context_metadata, created_at FROM kin_messages WHERE thread_id = ? AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC LIMIT 200');
        $stmt->execute([$threadId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $media = ['photos' => [], 'videos' => [], 'documents' => [], 'docs' => [], 'audio' => []];
        foreach ($rows as $row) {
            $meta = [];
            if (!empty($row['context_metadata'])) {
                $decoded = is_string($row['context_metadata']) ? json_decode($row['context_metadata'], true) : $row['context_metadata'];
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            }
            if (!empty($meta['attachments']) && is_array($meta['attachments'])) {
                foreach ($meta['attachments'] as $att) {
                    $type = $att['type'] ?? 'image';
                    $mime = strtolower((string)($att['mime'] ?? $att['mime_type'] ?? ''));
                    $url = strtolower((string)($att['url'] ?? ''));
                    $item = array_merge($att, ['message_id' => $row['id'], 'created_at' => $row['created_at']]);
                    if ($type === 'image' || str_starts_with($mime, 'image/') || preg_match('/\.(jpe?g|png|gif|webp|heic)$/i', $url)) {
                        $media['photos'][] = $item;
                    } elseif ($type === 'video' || str_starts_with($mime, 'video/') || preg_match('/\.(mp4|webm|mov|m4v|ogv)$/i', $url)) {
                        $media['videos'][] = $item;
                    } else {
                        $media['documents'][] = $item;
                        $media['docs'][] = $item;
                    }
                }
            }
            if (!empty($row['audio_url'])) {
                $media['audio'][] = [
                    'message_id' => $row['id'],
                    'url' => $row['audio_url'],
                    'duration' => $row['audio_duration_seconds'],
                    'created_at' => $row['created_at'],
                ];
            }
        }

        return $media;
    }

    /**
     * Generate a WhatsApp Companion Bridge URL.
     * Deep-links a private memory invitation back to FamilyPlatform.
     *
     * @param string $threadId
     * @param string $senderName
     * @param string|null $contextTitle
     * @return string
     */
    public static function generateWhatsAppBridgeUrl(
        string $threadId,
        string $senderName,
        ?string $contextTitle = null
    ): string {
        $baseUrl = (string)($_ENV['APP_URL'] ?? 'https://familyplatform.test');
        $whisperUrl = rtrim($baseUrl, '/') . '/kin-whispers?thread_id=' . urlencode($threadId);

        $topic = !empty($contextTitle) ? sprintf('regarding "%s"', $contextTitle) : 'for you';
        $messageText = sprintf(
            "Hello! I left a private Kin Whisper on FamilyPlatform %s. Click here to listen or reply: %s",
            $topic,
            $whisperUrl
        );

        return 'https://wa.me/?text=' . rawurlencode($messageText);
    }

    /**
     * Assert that the current user has legal authorization to access the thread.
     * Neutralizes Marcus's IDOR exploit vector.
     *
     * @param string $currentUserId
     * @param array<string, mixed> $thread
     * @throws ForbiddenException
     */
    public static function assertCanAccessThread(string $currentUserId, array $thread): void
    {
        $senderId = (string)($thread['sender_id'] ?? '');
        $recipientId = (string)($thread['recipient_id'] ?? '');
        $hasMinor = (int)($thread['has_minor_participant'] ?? 0) === 1;
        $supervisorId = (string)($thread['supervisor_parent_id'] ?? '');

        $isParticipant = ($currentUserId === $senderId || ($recipientId !== '' && $currentUserId === $recipientId));
        $isSupervisor = ($hasMinor && $supervisorId !== '' && $currentUserId === $supervisorId);

        if (!$isParticipant && !$isSupervisor) {
            if (!empty($thread['id'])) {
                $db = Db::connect2();
                $stmtCheck = $db->prepare('SELECT 1 FROM kin_thread_participants WHERE thread_id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
                $stmtCheck->execute([(string)$thread['id'], $currentUserId]);
                if ($stmtCheck->fetchColumn()) {
                    return;
                }
            }
            throw new ForbiddenException('Access denied: You are neither a participant nor an authorized supervising guardian of this whisper thread.');
        }
    }

    /**
     * Initiate a Voice or Video Call for a thread.
     * Sets active call room and broadcasts incoming call event via Pusher.
     *
     * @param string $currentUserId Caller ID
     * @param string $threadId Thread ID
     * @param string $callType 'video' | 'audio'
     * @return array<string, mixed>
     */
    public static function initiateCall(string $currentUserId, string $threadId, string $callType = 'video'): array
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);
        $callType = in_array($callType, ['audio', 'video'], true) ? $callType : 'video';

        $db = Db::connect2();
        $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmtThread->execute([$threadId]);
        $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        // Fetch caller details
        $stmtCaller = $db->prepare(
            'SELECT CONCAT(COALESCE(p.firstName, ""), " ", COALESCE(p.lastName, "")) AS full_name,
                    COALESCE(pic.img, "/avatar/avatarM.jpeg") AS avatar
             FROM personal p
             LEFT JOIN profilePics pic ON p.id = pic.id
             WHERE p.id = ? LIMIT 1'
        );
        $stmtCaller->execute([$currentUserId]);
        $callerData = $stmtCaller->fetch(PDO::FETCH_ASSOC) ?: [];
        $callerName = trim((string)($callerData['full_name'] ?? 'Family Kin'));
        $callerAvatar = self::resolveAvatarUrl($callerData['avatar'] ?? null);

        $roomId = 'room_' . bin2hex(random_bytes(12));

        $stmtUp = $db->prepare('UPDATE kin_threads SET call_status = "ringing", active_call_room_id = ? WHERE id = ?');
        $stmtUp->execute([$roomId, $threadId]);

        $isGroup = ((string)($thread['thread_type'] ?? 'direct')) === 'group';
        $threadTitle = $isGroup ? (string)($thread['title'] ?? 'Family Group') : $callerName;

        $callPayload = [
            'thread_id' => $threadId,
            'room_id' => $roomId,
            'call_type' => $callType,
            'caller_id' => $currentUserId,
            'caller_name' => $callerName,
            'caller_avatar' => $callerAvatar,
            'is_group' => $isGroup,
            'group_title' => $threadTitle,
            'status' => 'ringing',
            'timestamp' => time(),
        ];

        if ($isGroup) {
            $stmtParts = $db->prepare('SELECT user_id FROM kin_thread_participants WHERE thread_id = ? AND user_id != ? AND deleted_at IS NULL');
            $stmtParts->execute([$threadId, $currentUserId]);
            $recipientIds = $stmtParts->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $channels = array_values(array_map(fn($id) => 'private-user-' . strtolower((string)$id), $recipientIds));
            if (!empty($channels)) {
                \App\classes\Pusher::trigger($channels, 'kin-call-incoming', $callPayload);
            }
        } else {
            $recipientId = ($currentUserId === (string)$thread['sender_id'])
                ? (string)$thread['recipient_id']
                : (string)$thread['sender_id'];
            if ($recipientId !== '') {
                \App\classes\Pusher::trigger('private-user-' . strtolower($recipientId), 'kin-call-incoming', $callPayload);
            }
        }

        return $callPayload;
    }

    /**
     * Relay a WebRTC signaling payload (offer, answer, ICE candidate) to call peers.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @param array<string, mixed> $signalData
     * @return array{success: bool}
     */
    public static function sendCallSignal(string $currentUserId, string $threadId, array $signalData): array
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);

        $db = Db::connect2();
        $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmtThread->execute([$threadId]);
        $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        $targetUserId = isset($signalData['target_user_id']) ? trim((string)$signalData['target_user_id']) : null;
        $isGroup = ((string)($thread['thread_type'] ?? 'direct')) === 'group';

        $relayPayload = [
            'thread_id' => $threadId,
            'sender_id' => $currentUserId,
            'room_id' => (string)($thread['active_call_room_id'] ?? ''),
            'signal' => $signalData['signal'] ?? $signalData,
        ];

        if ($targetUserId !== null && $targetUserId !== '') {
            \App\classes\Pusher::trigger('private-user-' . strtolower($targetUserId), 'kin-call-signal', $relayPayload);
        } elseif ($isGroup) {
            $stmtParts = $db->prepare('SELECT user_id FROM kin_thread_participants WHERE thread_id = ? AND user_id != ? AND deleted_at IS NULL');
            $stmtParts->execute([$threadId, $currentUserId]);
            $recipientIds = $stmtParts->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $channels = array_values(array_map(fn($id) => 'private-user-' . strtolower((string)$id), $recipientIds));
            if (!empty($channels)) {
                \App\classes\Pusher::trigger($channels, 'kin-call-signal', $relayPayload);
            }
        } else {
            $recipientId = ($currentUserId === (string)$thread['sender_id'])
                ? (string)$thread['recipient_id']
                : (string)$thread['sender_id'];
            if ($recipientId !== '') {
                \App\classes\Pusher::trigger('private-user-' . strtolower($recipientId), 'kin-call-signal', $relayPayload);
            }
        }

        return ['success' => true];
    }

    /**
     * Terminate an active or ringing call.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return array<string, mixed>
     */
    public static function endCall(string $currentUserId, string $threadId): array
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);

        $db = Db::connect2();
        $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmtThread->execute([$threadId]);
        $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        $stmtUp = $db->prepare('UPDATE kin_threads SET call_status = "idle", active_call_room_id = NULL WHERE id = ?');
        $stmtUp->execute([$threadId]);

        $endPayload = [
            'thread_id' => $threadId,
            'ended_by' => $currentUserId,
            'status' => 'idle',
            'timestamp' => time(),
        ];

        $isGroup = ((string)($thread['thread_type'] ?? 'direct')) === 'group';

        if ($isGroup) {
            $stmtParts = $db->prepare('SELECT user_id FROM kin_thread_participants WHERE thread_id = ? AND deleted_at IS NULL');
            $stmtParts->execute([$threadId]);
            $allParts = $stmtParts->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $channels = array_values(array_map(fn($id) => 'private-user-' . strtolower((string)$id), $allParts));
            if (!empty($channels)) {
                \App\classes\Pusher::trigger($channels, 'kin-call-ended', $endPayload);
            }
        } else {
            $channels = [];
            $senderId = (string)$thread['sender_id'];
            $recipientId = (string)$thread['recipient_id'];
            if ($senderId !== '') {
                $channels[] = 'private-user-' . strtolower($senderId);
            }
            if ($recipientId !== '') {
                $channels[] = 'private-user-' . strtolower($recipientId);
            }
            if (!empty($channels)) {
                \App\classes\Pusher::trigger($channels, 'kin-call-ended', $endPayload);
            }
        }

        return $endPayload;
    }

    /**
     * Get active call info for a thread.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return array<string, mixed>
     */
    public static function getCallInfo(string $currentUserId, string $threadId): array
    {
        $currentUserId = trim($currentUserId);
        $threadId = trim($threadId);

        $db = Db::connect2();
        $stmtThread = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmtThread->execute([$threadId]);
        $thread = $stmtThread->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new NotFoundException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        return [
            'thread_id' => $threadId,
            'call_status' => (string)($thread['call_status'] ?? 'idle'),
            'active_call_room_id' => !empty($thread['active_call_room_id']) ? (string)$thread['active_call_room_id'] : null,
            'thread_type' => (string)($thread['thread_type'] ?? 'direct'),
            'title' => (string)($thread['title'] ?? ''),
        ];
    }

    /**
     * Request approval from recipient(s) to share thread/whisper on WhatsApp.
     * Enforces bilateral consent gate.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return KinThreadDto
     * @throws ForbiddenException|ValidationException
     */
    public static function requestShareApproval(string $currentUserId, string $threadId): KinThreadDto
    {
        $db = Db::connect2();
        $stmt = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmt->execute([$threadId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new ValidationException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        // Update thread share status to pending
        $stmtUp = $db->prepare('UPDATE kin_threads SET share_status = ?, share_requested_by = ?, share_requested_at = NOW() WHERE id = ?');
        $stmtUp->execute(['pending', $currentUserId, $threadId]);

        // Get requester name
        $stmtUser = $db->prepare('SELECT CONCAT(COALESCE(firstName, ""), " ", COALESCE(lastName, "")) FROM personal WHERE id = ? LIMIT 1');
        $stmtUser->execute([$currentUserId]);
        $userName = trim((string)$stmtUser->fetchColumn()) ?: 'A family member';

        // Insert audit event
        $eventId = 'evt_' . bin2hex(random_bytes(16));
        $stmtEvt = $db->prepare('INSERT INTO kin_thread_events (id, thread_id, user_id, user_name, event_type, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmtEvt->execute([$eventId, $threadId, $currentUserId, $userName, 'share_requested', json_encode(['requester_id' => $currentUserId])]);

        // Broadcast to other participants
        $isGroup = ($thread['thread_type'] ?? 'direct') === 'group';
        if ($isGroup) {
            $stmtParts = $db->prepare('SELECT user_id FROM kin_thread_participants WHERE thread_id = ? AND user_id != ? AND deleted_at IS NULL');
            $stmtParts->execute([$threadId, $currentUserId]);
            $recipientIds = $stmtParts->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } else {
            $otherId = ($currentUserId === (string)$thread['sender_id']) ? (string)$thread['recipient_id'] : (string)$thread['sender_id'];
            $recipientIds = $otherId !== '' ? [$otherId] : [];
        }

        $channels = array_values(array_map(fn($id) => 'private-user-' . strtolower((string)$id), $recipientIds));
        if (!empty($channels)) {
            \App\classes\Pusher::trigger($channels, 'kin-share-status', [
                'thread_id' => $threadId,
                'status' => 'pending',
                'share_status' => 'pending',
                'actor_id' => $currentUserId,
                'actor_name' => $userName,
                'requested_by' => $currentUserId,
                'requester_name' => $userName,
            ]);
        }

        // Send in-app notification to recipients
        $actionUrl = '/kin-whispers?thread_id=' . urlencode($threadId);
        foreach ($recipientIds as $recId) {
            NotificationOrchestrator::dispatch(
                userId: (string)$recId,
                category: 'kin_whisper',
                priority: 'high',
                title: '🔒 Sharing Consent Request',
                body: "{$userName} has requested your permission to share this whisper on WhatsApp.",
                actionUrl: $actionUrl,
                tag: 'share_req_' . $threadId,
                familyCode: (string)($thread['family_code'] ?? '')
            );
        }

        return self::getThread($currentUserId, $threadId);
    }

    /**
     * Respond to a share request (approve, decline, or revoke).
     *
     * @param string $currentUserId
     * @param string $threadId
     * @param string $action 'approve' | 'decline' | 'revoke'
     * @return KinThreadDto
     * @throws ForbiddenException|ValidationException
     */
    public static function respondShareApproval(string $currentUserId, string $threadId, string $action): KinThreadDto
    {
        if (!in_array($action, ['approve', 'decline', 'revoke'], true)) {
            throw new ValidationException('Invalid share action.');
        }

        $db = Db::connect2();
        $stmt = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmt->execute([$threadId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new ValidationException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        // BOLA Prevention: Requester cannot self-approve their own request!
        if ($action === 'approve' && (string)($thread['share_requested_by'] ?? '') === $currentUserId) {
            throw new ForbiddenException('You cannot approve your own share request. The other recipient must authorise sharing.');
        }

        // Get responder name
        $stmtUser = $db->prepare('SELECT CONCAT(COALESCE(firstName, ""), " ", COALESCE(lastName, "")) FROM personal WHERE id = ? LIMIT 1');
        $stmtUser->execute([$currentUserId]);
        $userName = trim((string)$stmtUser->fetchColumn()) ?: 'A family member';

        $newStatus = match ($action) {
            'approve' => 'approved',
            'decline' => 'declined',
            'revoke' => 'none',
        };

        $approvedBy = $action === 'approve' ? $currentUserId : null;

        if ($action === 'revoke') {
            $stmtUp = $db->prepare('UPDATE kin_threads SET share_status = ?, share_requested_by = NULL, share_requested_at = NULL, share_approved_by = NULL, share_approved_at = NULL WHERE id = ?');
            $stmtUp->execute([$newStatus, $threadId]);
        } elseif ($action === 'approve') {
            $stmtUp = $db->prepare('UPDATE kin_threads SET share_status = ?, share_approved_by = ?, share_approved_at = NOW() WHERE id = ?');
            $stmtUp->execute([$newStatus, $approvedBy, $threadId]);
        } else {
            $stmtUp = $db->prepare('UPDATE kin_threads SET share_status = ? WHERE id = ?');
            $stmtUp->execute([$newStatus, $threadId]);
        }

        // Insert audit event
        $eventId = 'evt_' . bin2hex(random_bytes(16));
        $evtType = 'share_' . ($action === 'approve' ? 'approved' : ($action === 'decline' ? 'declined' : 'revoked'));
        $stmtEvt = $db->prepare('INSERT INTO kin_thread_events (id, thread_id, user_id, user_name, event_type, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmtEvt->execute([$eventId, $threadId, $currentUserId, $userName, $evtType, json_encode(['action' => $action, 'responder_id' => $currentUserId])]);

        // Broadcast to all thread participants
        $isGroup = ($thread['thread_type'] ?? 'direct') === 'group';
        if ($isGroup) {
            $stmtParts = $db->prepare('SELECT user_id FROM kin_thread_participants WHERE thread_id = ? AND deleted_at IS NULL');
            $stmtParts->execute([$threadId]);
            $allParts = $stmtParts->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } else {
            $allParts = array_filter([(string)$thread['sender_id'], (string)$thread['recipient_id']]);
        }

        $channels = array_values(array_map(fn($id) => 'private-user-' . strtolower((string)$id), $allParts));
        if (!empty($channels)) {
            \App\classes\Pusher::trigger($channels, 'kin-share-status', [
                'thread_id' => $threadId,
                'status' => $newStatus,
                'share_status' => $newStatus,
                'actor_id' => $currentUserId,
                'actor_name' => $userName,
                'responder_id' => $currentUserId,
                'responder_name' => $userName,
                'action' => $action,
            ]);
        }

        return self::getThread($currentUserId, $threadId);
    }

    /**
     * Record a screenshot detection event with immutable audit logging and real-time alerts.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return array<string, mixed>
     * @throws ForbiddenException|ValidationException
     */
    public static function recordScreenshotEvent(string $currentUserId, string $threadId): array
    {
        $db = Db::connect2();
        $stmt = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmt->execute([$threadId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new ValidationException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        // Throttle check: 1 screenshot event per 4 seconds per user to prevent log spam
        $stmtThrottle = $db->prepare('SELECT 1 FROM kin_thread_events WHERE thread_id = ? AND user_id = ? AND event_type = ? AND created_at > (NOW() - INTERVAL 4 SECOND) LIMIT 1');
        $stmtThrottle->execute([$threadId, $currentUserId, 'screenshot_taken']);
        if ($stmtThrottle->fetch()) {
            return ['success' => true, 'recorded' => false, 'throttled' => true];
        }

        // Get user name
        $stmtUser = $db->prepare('SELECT CONCAT(COALESCE(firstName, ""), " ", COALESCE(lastName, "")) FROM personal WHERE id = ? LIMIT 1');
        $stmtUser->execute([$currentUserId]);
        $userName = trim((string)$stmtUser->fetchColumn()) ?: 'A family member';

        // Insert audit log
        $eventId = 'evt_' . bin2hex(random_bytes(16));
        $now = date('Y-m-d H:i:s');
        $stmtEvt = $db->prepare('INSERT INTO kin_thread_events (id, thread_id, user_id, user_name, event_type, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmtEvt->execute([$eventId, $threadId, $currentUserId, $userName, 'screenshot_taken', json_encode(['source' => 'web_browser']), $now]);

        $eventPayload = [
            'id' => $eventId,
            'event_id' => $eventId,
            'thread_id' => $threadId,
            'user_id' => $currentUserId,
            'actor_id' => $currentUserId,
            'user_name' => $userName,
            'actor_name' => $userName,
            'event_type' => 'screenshot_taken',
            'created_at' => $now,
            'timestamp' => $now,
        ];

        // Broadcast real-time alert to all participants
        $isGroup = ($thread['thread_type'] ?? 'direct') === 'group';
        if ($isGroup) {
            $stmtParts = $db->prepare('SELECT user_id FROM kin_thread_participants WHERE thread_id = ? AND deleted_at IS NULL');
            $stmtParts->execute([$threadId]);
            $allParts = $stmtParts->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } else {
            $allParts = array_filter([(string)$thread['sender_id'], (string)$thread['recipient_id']]);
        }

        $channels = array_values(array_map(fn($id) => 'private-user-' . strtolower((string)$id), $allParts));
        if (!empty($channels)) {
            \App\classes\Pusher::trigger($channels, 'kin-screenshot-detected', $eventPayload);
        }

        $actionUrl = '/kin-whispers?thread_id=' . urlencode($threadId);
        // Safeguarding notice for parents if minor involved
        if ((int)($thread['has_minor_participant'] ?? 0) === 1 && !empty($thread['supervisor_parent_id'])) {
            NotificationOrchestrator::dispatch(
                userId: (string)$thread['supervisor_parent_id'],
                category: 'child_supervision',
                priority: 'high',
                title: '📸 Safeguarding: Screenshot Captured',
                body: "{$userName} took a screenshot of a supervised family whisper.",
                actionUrl: $actionUrl,
                tag: 'screenshot_sup_' . $threadId,
                familyCode: (string)($thread['family_code'] ?? '')
            );
        }

        // Notify other participants
        $otherParticipants = array_diff($allParts, [$currentUserId]);
        foreach ($otherParticipants as $otherId) {
            NotificationOrchestrator::dispatch(
                userId: (string)$otherId,
                category: 'kin_whisper',
                priority: 'high',
                title: '📸 Screenshot Captured',
                body: "{$userName} took a screenshot in your whisper conversation.",
                actionUrl: $actionUrl,
                tag: 'screenshot_' . $threadId,
                familyCode: (string)($thread['family_code'] ?? '')
            );
        }

        return ['success' => true, 'recorded' => true, 'event' => $eventPayload];
    }

    /**
     * Fetch recent audit events for a thread.
     *
     * @param string $currentUserId
     * @param string $threadId
     * @return array<int, array<string, mixed>>
     * @throws ForbiddenException|ValidationException
     */
    public static function getThreadEvents(string $currentUserId, string $threadId): array
    {
        $db = Db::connect2();
        $stmt = $db->prepare('SELECT * FROM kin_threads WHERE id = ? LIMIT 1');
        $stmt->execute([$threadId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            throw new ValidationException('Whisper thread not found.');
        }

        self::assertCanAccessThread($currentUserId, $thread);

        $stmtEvents = $db->prepare('SELECT id, thread_id, user_id, user_name, event_type, metadata, created_at FROM kin_thread_events WHERE thread_id = ? ORDER BY created_at ASC LIMIT 100');
        $stmtEvents->execute([$threadId]);
        return $stmtEvents->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Hydrate a thread database row into a rich KinThreadDto.
     *
     * @param array<string, mixed> $row
     * @param string $currentUserId
     * @param array<string, array{name: string, avatar: string}>|null $userProfileMap Optional pre-fetched user profile cache
     * @return KinThreadDto
     */
    private static function hydrateThreadDto(array $row, string $currentUserId, ?array $userProfileMap = null): KinThreadDto
    {
        $db = Db::connect2();
        $threadId = (string)($row['id'] ?? '');
        $threadType = (string)($row['thread_type'] ?? 'direct');
        $senderId = (string)($row['sender_id'] ?? '');
        $recipientId = (string)($row['recipient_id'] ?? '');

        $participants = [];
        if ($threadType === 'group' && $threadId !== '') {
            $participants = self::getGroupParticipants($threadId, $currentUserId);
        }

        // Fetch sender
        if ($userProfileMap !== null && isset($userProfileMap[$senderId])) {
            $row['sender_name'] = $userProfileMap[$senderId]['name'] !== '' ? $userProfileMap[$senderId]['name'] : 'Family Kin';
            $row['sender_avatar'] = $userProfileMap[$senderId]['avatar'];
        } else {
            $stmtS = $db->prepare('SELECT CONCAT(COALESCE(p.firstName, ""), " ", COALESCE(p.lastName, "")) AS full_name, COALESCE(pic.img, "/avatar/avatarM.jpeg") AS avatar FROM personal p LEFT JOIN profilePics pic ON p.id = pic.id WHERE p.id = ? LIMIT 1');
            $stmtS->execute([$senderId]);
            $sData = $stmtS->fetch(PDO::FETCH_ASSOC);
            $row['sender_name'] = !empty($sData['full_name']) ? trim($sData['full_name']) : 'Family Kin';
            $row['sender_avatar'] = self::resolveAvatarUrl($sData['avatar'] ?? null);
        }

        // Fetch recipient if direct
        if ($recipientId !== '') {
            if ($userProfileMap !== null && isset($userProfileMap[$recipientId])) {
                $row['recipient_name'] = $userProfileMap[$recipientId]['name'] !== '' ? $userProfileMap[$recipientId]['name'] : 'Family Kin';
                $row['recipient_avatar'] = $userProfileMap[$recipientId]['avatar'];
            } else {
                $stmtR = $db->prepare('SELECT CONCAT(COALESCE(p.firstName, ""), " ", COALESCE(p.lastName, "")) AS full_name, COALESCE(pic.img, "/avatar/avatarM.jpeg") AS avatar FROM personal p LEFT JOIN profilePics pic ON p.id = pic.id WHERE p.id = ? LIMIT 1');
                $stmtR->execute([$recipientId]);
                $rData = $stmtR->fetch(PDO::FETCH_ASSOC);
                $row['recipient_name'] = !empty($rData['full_name']) ? trim($rData['full_name']) : 'Family Kin';
                $row['recipient_avatar'] = self::resolveAvatarUrl($rData['avatar'] ?? null);
            }
        } else {
            $row['recipient_name'] = $threadType === 'group' ? (string)($row['title'] ?? 'Family Group') : 'Family Kin';
            $row['recipient_avatar'] = self::resolveAvatarUrl(null);
        }

        if (!isset($row['unread_count'])) {
            if ($threadType === 'group') {
                $stmtUnread = $db->prepare(
                    'SELECT COUNT(*) FROM kin_messages m
                     JOIN kin_thread_participants p ON m.thread_id = p.thread_id AND p.user_id = ? AND p.deleted_at IS NULL
                     WHERE m.thread_id = ? AND m.sender_id != ? AND m.recipient_id IS NULL
                       AND (p.last_read_at IS NULL OR m.created_at > p.last_read_at)
                       AND (m.expires_at IS NULL OR m.expires_at > NOW())'
                );
                $stmtUnread->execute([$currentUserId, $threadId, $currentUserId]);
                $row['unread_count'] = (int)$stmtUnread->fetchColumn();
            } else {
                $stmtUnread = $db->prepare('SELECT COUNT(*) FROM kin_messages WHERE thread_id = ? AND recipient_id = ? AND is_read = 0 AND (expires_at IS NULL OR expires_at > NOW())');
                $stmtUnread->execute([$threadId, $currentUserId]);
                $row['unread_count'] = (int)$stmtUnread->fetchColumn();
            }
        }

        if (!isset($row['is_pinned']) || !isset($row['is_muted'])) {
            try {
                $stmtPartPrefs = $db->prepare('SELECT is_pinned, is_muted FROM kin_thread_participants WHERE thread_id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
                $stmtPartPrefs->execute([$threadId, $currentUserId]);
                $pRow = $stmtPartPrefs->fetch(PDO::FETCH_ASSOC);
                $row['is_pinned'] = (int)($pRow['is_pinned'] ?? 0);
                $row['is_muted'] = (int)($pRow['is_muted'] ?? 0);
            } catch (\Throwable) {
                $row['is_pinned'] = 0;
                $row['is_muted'] = 0;
            }
        }

        return KinThreadDto::fromArray($row, $currentUserId, $participants);
    }

    /**
     * Read a user's "Online & Last seen" privacy preference ('everyone' | 'nobody').
     */
    public static function getPresencePrivacy(string $userId): string
    {
        try {
            $stmt = Db::connect2()->prepare('SELECT last_seen_privacy FROM user_presence_settings WHERE user_id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $value = $stmt->fetchColumn();

            return $value === 'nobody' ? 'nobody' : 'everyone';
        } catch (\Throwable) {
            return 'everyone';
        }
    }

    /**
     * Persist a user's presence privacy preference (strict allowlist).
     *
     * @throws ValidationException
     */
    public static function setPresencePrivacy(string $userId, string $privacy): string
    {
        if (!in_array($privacy, ['everyone', 'nobody'], true)) {
            throw new ValidationException('Presence privacy must be "everyone" or "nobody".');
        }
        $stmt = Db::connect2()->prepare(
            'INSERT INTO user_presence_settings (user_id, last_seen_privacy) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE last_seen_privacy = VALUES(last_seen_privacy)'
        );
        $stmt->execute([$userId, $privacy]);

        return $privacy;
    }

    /**
     * Record a heartbeat for the viewer and return privacy-filtered presence for the requested kin.
     *
     * Privacy rules (WhatsApp-style, reciprocal):
     *  - A user who hides their presence is never reported as online and exposes no timestamp.
     *  - A viewer who hides their own presence cannot see anyone else's.
     *  - Only kin who share a thread with the viewer can be queried (no enumeration).
     *
     * @param array<int, mixed> $userIds
     * @return array{privacy: string, presence: array<string, array{is_online: bool, last_seen_ts: ?int, is_hidden: bool}>}
     */
    public static function syncPresence(string $viewerId, array $userIds): array
    {
        $db = Db::connect2();

        $beat = $db->prepare(
            'INSERT INTO user_socket_presence (user_id, channel_name, is_active, last_heartbeat)
             VALUES (?, ?, 1, NOW())
             ON DUPLICATE KEY UPDATE is_active = 1, last_heartbeat = NOW()'
        );
        $beat->execute([$viewerId, 'kin-whispers-web']);

        $myPrivacy = self::getPresencePrivacy($viewerId);

        $ids = [];
        foreach ($userIds as $candidate) {
            $candidate = trim((string)$candidate);
            if ($candidate !== '' && strlen($candidate) <= 191 && $candidate !== $viewerId) {
                $ids[$candidate] = true;
            }
        }
        $ids = array_slice(array_keys($ids), 0, 100);
        if ($ids === []) {
            return ['privacy' => $myPrivacy, 'presence' => []];
        }

        $marks = implode(',', array_fill(0, count($ids), '?'));

        // 1. Restrict to kin who share a thread with the viewer.
        $allowedSql = sprintf(
            'SELECT DISTINCT u FROM (
                SELECT recipient_id AS u FROM kin_threads WHERE sender_id = ? AND recipient_id IS NOT NULL
                UNION SELECT sender_id AS u FROM kin_threads WHERE recipient_id = ?
                UNION SELECT p2.user_id AS u FROM kin_thread_participants p1
                      JOIN kin_thread_participants p2 ON p1.thread_id = p2.thread_id
                      WHERE p1.user_id = ? AND p1.deleted_at IS NULL AND p2.deleted_at IS NULL
             ) x WHERE u IN (%s)',
            $marks
        );
        $stmtAllowed = $db->prepare($allowedSql);
        $stmtAllowed->execute(array_merge([$viewerId, $viewerId, $viewerId], $ids));
        $allowed = array_map('strval', $stmtAllowed->fetchAll(PDO::FETCH_COLUMN));
        if ($allowed === []) {
            return ['privacy' => $myPrivacy, 'presence' => []];
        }
        $marks = implode(',', array_fill(0, count($allowed), '?'));

        // 2. Their privacy preferences.
        $hidden = [];
        try {
            $stmtPriv = $db->prepare(sprintf('SELECT user_id FROM user_presence_settings WHERE last_seen_privacy = \'nobody\' AND user_id IN (%s)', $marks));
            $stmtPriv->execute($allowed);
            foreach ($stmtPriv->fetchAll(PDO::FETCH_COLUMN) as $hid) {
                $hidden[(string)$hid] = true;
            }
        } catch (\Throwable) {
            $hidden = [];
        }

        // 3. Heartbeats (only read for users we may disclose).
        $beats = [];
        $stmtBeat = $db->prepare(sprintf(
            'SELECT user_id,
                    MAX(CASE WHEN is_active = 1 AND last_heartbeat >= DATE_SUB(NOW(), INTERVAL 60 SECOND) THEN 1 ELSE 0 END) AS online,
                    UNIX_TIMESTAMP(MAX(last_heartbeat)) AS ts
             FROM user_socket_presence WHERE user_id IN (%s) GROUP BY user_id',
            $marks
        ));
        $stmtBeat->execute($allowed);
        foreach ($stmtBeat->fetchAll(PDO::FETCH_ASSOC) as $b) {
            $beats[(string)$b['user_id']] = $b;
        }

        $presence = [];
        foreach ($allowed as $uid) {
            if ($myPrivacy === 'nobody' || isset($hidden[$uid])) {
                $presence[$uid] = ['is_online' => false, 'last_seen_ts' => null, 'is_hidden' => true];
                continue;
            }
            $b = $beats[$uid] ?? null;
            $presence[$uid] = [
                'is_online'    => $b !== null && (int)$b['online'] === 1,
                'last_seen_ts' => $b !== null && $b['ts'] !== null ? (int)$b['ts'] : null,
                'is_hidden'    => false,
            ];
        }

        return ['privacy' => $myPrivacy, 'presence' => $presence];
    }
}
