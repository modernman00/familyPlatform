<?php
declare(strict_types=1);

namespace App\dto;

use JsonSerializable;

/**
 * KinWhisperDto
 *
 * Immutable, strictly-typed Data Transfer Object representing a single Kin Whisper / Message.
 * Enforces British English formatting and defensive typing (David Gate 1).
 */
final class KinWhisperDto implements JsonSerializable
{
    /**
     * @param array<string, mixed>|null $contextMetadata
     */
    public function __construct(
        public readonly string $id,
        public readonly string $threadId,
        public readonly string $senderId,
        public readonly string $senderName,
        public readonly string $senderAvatar,
        public readonly ?string $recipientId,
        public readonly string $messageType,
        public readonly string $body,
        public readonly ?string $audioUrl,
        public readonly ?int $audioDurationSeconds,
        public readonly ?array $contextMetadata,
        public readonly bool $isRead,
        public readonly ?string $readAt,
        public readonly bool $parentViewed,
        public readonly ?string $expiresAt,
        public readonly string $createdAt
    ) {}

    /**
     * Factory from associative database row.
     *
     * @param array<string, mixed> $row
     * @return self
     */
    public static function fromArray(array $row): self
    {
        $meta = null;
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

        return new self(
            id: (string)($row['id'] ?? ''),
            threadId: (string)($row['thread_id'] ?? ''),
            senderId: (string)($row['sender_id'] ?? ''),
            senderName: (string)($row['sender_name'] ?? 'Family Kin'),
            senderAvatar: (string)($row['sender_avatar'] ?? '/public/images/default-avatar.png'),
            recipientId: isset($row['recipient_id']) && $row['recipient_id'] !== '' ? (string)$row['recipient_id'] : null,
            messageType: (string)($row['message_type'] ?? 'text'),
            body: (string)($row['body'] ?? ''),
            audioUrl: isset($row['audio_url']) && $row['audio_url'] !== '' ? (string)$row['audio_url'] : null,
            audioDurationSeconds: isset($row['audio_duration_seconds']) ? (int)$row['audio_duration_seconds'] : null,
            contextMetadata: $meta,
            isRead: (bool)((int)($row['is_read'] ?? 0) === 1),
            readAt: isset($row['read_at']) ? (string)$row['read_at'] : null,
            parentViewed: (bool)((int)($row['parent_viewed'] ?? 0) === 1),
            expiresAt: isset($row['expires_at']) && $row['expires_at'] !== '' ? (string)$row['expires_at'] : null,
            createdAt: (string)($row['created_at'] ?? date('Y-m-d H:i:s'))
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'thread_id' => $this->threadId,
            'sender_id' => $this->senderId,
            'sender_name' => $this->senderName,
            'sender_avatar' => $this->senderAvatar,
            'recipient_id' => $this->recipientId,
            'message_type' => $this->messageType,
            'body' => $this->body,
            'audio_url' => $this->audioUrl,
            'audio_duration_seconds' => $this->audioDurationSeconds,
            'context_metadata' => $this->contextMetadata,
            'is_read' => $this->isRead,
            'read_at' => $this->readAt,
            'parent_viewed' => $this->parentViewed,
            'expires_at' => $this->expiresAt,
            'expires_at_iso' => $this->expiresAt !== null ? gmdate('Y-m-d\TH:i:s\Z', (int)strtotime($this->expiresAt)) : null,
            'expires_in_seconds' => $this->expiresAt !== null ? max(0, (int)strtotime($this->expiresAt) - time()) : null,
            'created_at' => $this->createdAt,
            'created_at_iso' => gmdate('Y-m-d\TH:i:s\Z', (int)strtotime($this->createdAt)),
        ];
    }
}
