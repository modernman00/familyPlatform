<?php
declare(strict_types=1);

namespace App\dto;

use JsonSerializable;

/**
 * KinThreadDto
 *
 * Immutable, strictly-typed Data Transfer Object representing a single Kin Conversation Thread.
 * Enforces UK Online Safety Act minor safeguarding parameters and defensive typing (David Gate 1).
 */
final class KinThreadDto implements JsonSerializable
{
    /**
     * @param array<array<string, mixed>> $participants
     */
    public function __construct(
        public readonly string $id,
        public readonly string $familyCode,
        public readonly string $threadType,
        public readonly ?string $title,
        public readonly ?string $createdBy,
        public readonly string $senderId,
        public readonly string $recipientId,
        public readonly string $senderName,
        public readonly string $senderAvatar,
        public readonly string $recipientName,
        public readonly string $recipientAvatar,
        public readonly string $contextType,
        public readonly ?string $contextId,
        public readonly ?string $contextTitle,
        public readonly bool $hasMinorParticipant,
        public readonly ?string $supervisorParentId,
        public readonly string $status,
        public readonly string $shareStatus,
        public readonly ?string $shareRequestedBy,
        public readonly ?string $shareRequestedAt,
        public readonly ?string $shareApprovedBy,
        public readonly ?string $shareApprovedAt,
        public readonly string $callStatus,
        public readonly ?string $activeCallRoomId,
        public readonly string $lastMessageAt,
        public readonly ?string $lastMessagePreview,
        public readonly int $unreadCount,
        public readonly bool $isSupervisorView,
        public readonly array $participants,
        public readonly int $participantCount,
        public readonly string $createdAt,
        public readonly bool $isPinned = false,
        public readonly bool $isMuted = false
    ) {}

    /**
     * Factory from associative database row.
     *
     * @param array<string, mixed> $row
     * @param string $currentUserId
     * @param array<array<string, mixed>> $participants
     * @return self
     */
    public static function fromArray(array $row, string $currentUserId, array $participants = []): self
    {
        $senderId = (string)($row['sender_id'] ?? '');
        $recipientId = (string)($row['recipient_id'] ?? '');
        $supervisorId = isset($row['supervisor_parent_id']) ? (string)$row['supervisor_parent_id'] : null;
        $isSupervisor = ($supervisorId !== null && $supervisorId === $currentUserId && $currentUserId !== $senderId && $currentUserId !== $recipientId);
        $threadType = (string)($row['thread_type'] ?? 'direct');
        $title = isset($row['title']) && trim((string)$row['title']) !== '' ? trim((string)$row['title']) : null;
        $createdBy = isset($row['created_by']) && $row['created_by'] !== '' ? (string)$row['created_by'] : ($senderId !== '' ? $senderId : null);
        $callStatus = (string)($row['call_status'] ?? 'idle');
        $activeCallRoomId = isset($row['active_call_room_id']) && $row['active_call_room_id'] !== '' ? (string)$row['active_call_room_id'] : null;

        $pCount = count($participants);
        if ($pCount === 0) {
            $pCount = $threadType === 'group' ? (int)($row['participant_count'] ?? 1) : 2;
        }

        return new self(
            id: (string)($row['id'] ?? ''),
            familyCode: (string)($row['family_code'] ?? ''),
            threadType: $threadType,
            title: $title,
            createdBy: $createdBy,
            senderId: $senderId,
            recipientId: $recipientId,
            senderName: (string)($row['sender_name'] ?? 'Family Kin'),
            senderAvatar: (string)($row['sender_avatar'] ?? '/public/images/default-avatar.png'),
            recipientName: (string)($row['recipient_name'] ?? 'Family Kin'),
            recipientAvatar: (string)($row['recipient_avatar'] ?? '/public/images/default-avatar.png'),
            contextType: (string)($row['context_type'] ?? 'general'),
            contextId: isset($row['context_id']) && $row['context_id'] !== '' ? (string)$row['context_id'] : null,
            contextTitle: isset($row['context_title']) && $row['context_title'] !== '' ? (string)$row['context_title'] : null,
            hasMinorParticipant: (bool)((int)($row['has_minor_participant'] ?? 0) === 1),
            supervisorParentId: $supervisorId,
            status: (string)($row['status'] ?? 'active'),
            shareStatus: (string)($row['share_status'] ?? 'none'),
            shareRequestedBy: isset($row['share_requested_by']) && $row['share_requested_by'] !== '' ? (string)$row['share_requested_by'] : null,
            shareRequestedAt: isset($row['share_requested_at']) ? (string)$row['share_requested_at'] : null,
            shareApprovedBy: isset($row['share_approved_by']) && $row['share_approved_by'] !== '' ? (string)$row['share_approved_by'] : null,
            shareApprovedAt: isset($row['share_approved_at']) ? (string)$row['share_approved_at'] : null,
            callStatus: $callStatus,
            activeCallRoomId: $activeCallRoomId,
            lastMessageAt: (string)($row['last_message_at'] ?? date('Y-m-d H:i:s')),
            lastMessagePreview: isset($row['last_message_preview']) ? (string)$row['last_message_preview'] : null,
            unreadCount: (int)($row['unread_count'] ?? 0),
            isSupervisorView: $isSupervisor,
            participants: $participants,
            participantCount: $pCount,
            createdAt: (string)($row['created_at'] ?? date('Y-m-d H:i:s')),
            isPinned: (bool)((int)($row['is_pinned'] ?? 0) === 1),
            isMuted: (bool)((int)($row['is_muted'] ?? 0) === 1)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'family_code' => $this->familyCode,
            'thread_type' => $this->threadType,
            'title' => $this->title,
            'created_by' => $this->createdBy,
            'sender_id' => $this->senderId,
            'recipient_id' => $this->recipientId,
            'sender_name' => $this->senderName,
            'sender_avatar' => $this->senderAvatar,
            'recipient_name' => $this->recipientName,
            'recipient_avatar' => $this->recipientAvatar,
            'context_type' => $this->contextType,
            'context_id' => $this->contextId,
            'context_title' => $this->contextTitle,
            'has_minor_participant' => $this->hasMinorParticipant,
            'supervisor_parent_id' => $this->supervisorParentId,
            'status' => $this->status,
            'share_status' => $this->shareStatus,
            'share_requested_by' => $this->shareRequestedBy,
            'share_requested_at' => $this->shareRequestedAt,
            'share_approved_by' => $this->shareApprovedBy,
            'share_approved_at' => $this->shareApprovedAt,
            'call_status' => $this->callStatus,
            'active_call_room_id' => $this->activeCallRoomId,
            'last_message_at' => $this->lastMessageAt,
            'last_message_preview' => $this->lastMessagePreview,
            'unread_count' => $this->unreadCount,
            'is_supervisor_view' => $this->isSupervisorView,
            'participants' => $this->participants,
            'participant_count' => $this->participantCount,
            'created_at' => $this->createdAt,
            'is_pinned' => $this->isPinned,
            'is_muted' => $this->isMuted,
        ];
    }
}
