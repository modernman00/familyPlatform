<?php
declare(strict_types=1);

namespace App\dto;

use JsonSerializable;

/**
 * PostDto
 *
 * Immutable, strictly-typed Data Transfer Object representing a single Feed Post.
 * Guarantees standard engagement properties (user_has_liked, user_reaction, likes_count)
 * across all Blade views, Alpine components, and JSON API endpoints.
 */
final class PostDto implements JsonSerializable
{
    /**
     * @param int $postNo
     * @param string $id
     * @param string $fullName
     * @param string $postMessage
     * @param string $content
     * @param string $postFamCode
     * @param int $postLikes
     * @param int $likesCount
     * @param bool $userHasLiked
     * @param string|null $userReaction
     * @param string $dateCreated
     * @param string $postTime
     * @param string $postStatus
     * @param string|null $img
     * @param string|null $profileImg
     * @param array<string, mixed>|null $poll
     * @param array<int, mixed> $reactions
     * @param array<string, mixed> $rawAttributes
     */
    public function __construct(
        public readonly int $postNo,
        public readonly string $id,
        public readonly string $fullName,
        public readonly string $postMessage,
        public readonly string $content,
        public readonly string $postFamCode,
        public readonly int $postLikes,
        public readonly int $likesCount,
        public readonly bool $userHasLiked,
        public readonly ?string $userReaction,
        public readonly string $dateCreated,
        public readonly string $postTime,
        public readonly string $postStatus,
        public readonly ?string $img,
        public readonly ?string $profileImg,
        public readonly ?array $poll = null,
        public readonly array $reactions = [],
        public readonly array $rawAttributes = []
    ) {}

    /**
     * Factory from raw database row + current user context.
     *
     * @param array<string, mixed> $row
     * @param string|null $currentUserId
     * @return self
     */
    public static function fromDatabaseRow(array $row, ?string $currentUserId = null): self
    {
        $postNo = (int)($row['post_no'] ?? 0);
        $id = (string)($row['id'] ?? '');
        $fullName = (string)($row['fullName'] ?? ($row['fullname'] ?? 'Family Member'));
        $message = (string)($row['postMessage'] ?? ($row['content'] ?? ''));
        $famCode = (string)($row['postFamCode'] ?? ($row['famCode'] ?? ''));
        $postLikes = max(0, (int)($row['post_likes'] ?? ($row['likes_count'] ?? 0)));

        $userReaction = null;
        if (isset($row['user_reaction']) && is_string($row['user_reaction']) && trim($row['user_reaction']) !== '') {
            $userReaction = trim($row['user_reaction']);
        }

        $userHasLiked = ($userReaction !== null);

        $dateCreated = (string)($row['date_created'] ?? date('Y-m-d H:i:s'));
        $postTime = (string)($row['post_time'] ?? $dateCreated);
        $postStatus = (string)($row['post_status'] ?? 'published');

        $rawImg = isset($row['img']) && is_string($row['img']) ? $row['img'] : null;
        $profileImg = $rawImg;
        if ($profileImg !== null && !str_starts_with($profileImg, '/') && !str_starts_with($profileImg, 'http')) {
            $profileImg = "/resources/images/profile/{$profileImg}";
        }

        /** @var array<string, mixed>|null $poll */
        $poll = isset($row['poll']) && is_array($row['poll']) ? $row['poll'] : null;
        
        /** @var array<int, mixed> $reactions */
        $reactions = isset($row['reactions']) && is_array($row['reactions']) ? array_values($row['reactions']) : [];

        return new self(
            postNo: $postNo,
            id: $id,
            fullName: $fullName,
            postMessage: $message,
            content: $message,
            postFamCode: $famCode,
            postLikes: $postLikes,
            likesCount: $postLikes,
            userHasLiked: $userHasLiked,
            userReaction: $userReaction,
            dateCreated: $dateCreated,
            postTime: $postTime,
            postStatus: $postStatus,
            img: $rawImg,
            profileImg: $profileImg,
            poll: $poll,
            reactions: $reactions,
            rawAttributes: $row
        );
    }

    /**
     * Export to standard array format (100% backwards compatible with existing Blade templates & Alpine/Vue).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $arr = $this->rawAttributes;

        // Guaranteed standardized properties override any stale or missing raw values
        $arr['post_no'] = $this->postNo;
        $arr['id'] = $this->id;
        $arr['fullName'] = $this->fullName;
        $arr['postMessage'] = $this->postMessage;
        $arr['content'] = $this->content;
        $arr['postFamCode'] = $this->postFamCode;
        $arr['post_likes'] = $this->postLikes;
        $arr['likes_count'] = $this->likesCount;
        $arr['user_has_liked'] = $this->userHasLiked;
        $arr['user_reaction'] = $this->userReaction;
        $arr['date_created'] = $this->dateCreated;
        $arr['post_time'] = $this->postTime;
        $arr['post_status'] = $this->postStatus;
        $arr['img'] = $this->img;
        $arr['profileImg'] = $this->profileImg;
        $arr['poll'] = $this->poll;
        $arr['reactions'] = $this->reactions;

        return $arr;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
