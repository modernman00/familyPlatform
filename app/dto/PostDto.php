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
     * @param array<string, mixed>|null $linkPreview
     * @param array<int, mixed> $postTags
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
        public readonly ?array $twoTruths = null,
        public readonly array $reactions = [],
        public readonly ?array $linkPreview = null,
        public readonly array $postTags = [],
        public readonly array $rawAttributes = [],
        public readonly int $viewsCount = 0,
        public readonly ?string $voiceCapsuleUrl = null,
        public readonly bool $isOfficialPost = false,
        public readonly ?string $arEmoji = null,
        public readonly array $videos = [],
        public readonly ?array $video = null,
        public readonly string $targetScope = 'all'
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
        $fullName = (string)($row['fullName'] ?? ($row['fullname'] ?? ''));
        if ($fullName === '' || $fullName === 'Family Member') {
            if (!empty($row['personalFullName']) && is_string($row['personalFullName'])) {
                $fullName = trim($row['personalFullName']);
            }
        }
        if ($fullName === '') {
            $fullName = 'Family Member';
        }
        $message = (string)($row['postMessage'] ?? ($row['content'] ?? ''));
        if ($message !== '') {
            $message = (string)preg_replace('/&amp;(#\d+|#x[0-9a-f]+|[a-z]+);/iu', '&$1;', $message);
            $message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $message = (string)preg_replace('/&(?:#?0*39|#x0*27|apos);/iu', "'", $message);
        }
        $famCode = (string)($row['postFamCode'] ?? ($row['famCode'] ?? ''));
        $postLikes = max(0, (int)($row['post_likes'] ?? ($row['likes_count'] ?? 0)));

        $userReaction = null;
        if (isset($row['user_reaction']) && is_string($row['user_reaction']) && trim($row['user_reaction']) !== '') {
            $userReaction = trim($row['user_reaction']);
        }

        $userHasLiked = ($userReaction !== null) || ($currentUserId !== null && !empty($row['user_has_liked']));

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

        /** @var array<string, mixed>|null $twoTruths */
        $twoTruths = isset($row['two_truths']) && is_array($row['two_truths']) ? $row['two_truths'] : null;
        
        /** @var array<int, mixed> $reactions */
        $reactions = isset($row['reactions']) && is_array($row['reactions']) ? array_values($row['reactions']) : [];

        /** @var array<string, mixed>|null $linkPreview */
        $linkPreview = isset($row['link_preview']) && is_array($row['link_preview']) ? $row['link_preview'] : null;
        $alreadyHydratedLinkPreview = array_key_exists('link_preview', $row);
        if ($linkPreview === null && !$alreadyHydratedLinkPreview && !empty($message) && (str_contains($message, 'http://') || str_contains($message, 'https://'))) {
            $linkPreview = \App\services\LinkPreviewService::getPreviewForPostText($message);
        }

        // Linked Media Promotion (e.g. Google Photos, direct image/video assets, multi-item support)
        $hasLocalUpload = false;
        $existingExternalImages = [];
        $existingExternalVideos = [];
        for ($i = 0; $i < 6; $i++) {
            $colVal = trim((string)($row['post_img' . $i] ?? ''));
            if ($colVal !== '' && $colVal !== 'null' && $colVal !== 'undefined') {
                if (!str_starts_with($colVal, 'http://') && !str_starts_with($colVal, 'https://')) {
                    $hasLocalUpload = true;
                } else {
                    if (\App\classes\VideoParser::isVideoUrl($colVal)) {
                        $existingExternalVideos[] = $colVal;
                    } else {
                        $existingExternalImages[] = $colVal;
                    }
                }
            }
        }

        $rowVideos = isset($row['videos']) && is_array($row['videos']) ? $row['videos'] : [];
        $hasPreExtractedMedia = !empty($rowVideos) || !empty($row['images']) || !empty($existingExternalImages) || !empty($existingExternalVideos);
        $hasExternalLinkInMessage = !empty($message) && (str_contains($message, 'http://') || str_contains($message, 'https://'));

        if (!$hasLocalUpload && !$hasPreExtractedMedia && $hasExternalLinkInMessage) {
            $extractedAssets = \App\services\LinkPreviewService::extractPhotoAssetsFromText($message, 6);
            $finalImages = !empty($extractedAssets['images']) ? $extractedAssets['images'] : $existingExternalImages;
            $finalVideos = !empty($extractedAssets['videos']) ? $extractedAssets['videos'] : $existingExternalVideos;

            if (!empty($finalImages)) {
                $row['images'] = $finalImages;
                foreach ($finalImages as $slotIdx => $imgUrl) {
                    $row['post_img' . $slotIdx] = $imgUrl;
                }
            }

            if (!empty($finalVideos)) {
                $hook = self::deriveHookFromMessage($message ?? ($row['postMessage'] ?? ''));
                $rowVideos = array_map(function ($v) use ($hook) {
                    $thumb = null;
                    if (preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|ggpht\.com)/#i', $v)) {
                        $thumb = preg_replace('/=.*$/i', '=s1600-no', $v);
                    }
                    return [
                        'type' => 'direct',
                        'videoId' => null,
                        'thumbnailUrl' => $thumb,
                        'embedUrl' => \App\services\LinkPreviewService::getProxiedVideoUrl($v),
                        'originalUrl' => $v,
                        'trimStart' => null,
                        'trimEnd' => null,
                        'hookText' => $hook,
                        'aspectMode' => 'reel',
                    ];
                }, $finalVideos);
                $row['videos'] = $rowVideos;
                $row['video'] = $rowVideos[0];
            }

            if (!empty($finalImages) || !empty($finalVideos)) {
                if (!empty($linkPreview['is_google_photo']) || !empty($linkPreview['is_photo_link'])) {
                    $linkPreview = null;
                }
            } elseif ($linkPreview === null && ($extractedAssets['preview'] ?? null) !== null) {
                $linkPreview = $extractedAssets['preview'];
            }

            if (!empty($extractedAssets['cleaned_message'])) {
                $row['displayMessage'] = $extractedAssets['cleaned_message'];
            } elseif (!empty($finalImages) || !empty($finalVideos)) {
                $row['displayMessage'] = \App\services\LinkPreviewService::cleanPhotoLinkFromMessage(
                    $message,
                    array_merge($finalImages, $finalVideos)
                );
            }
        } elseif (!empty($existingExternalVideos) && empty($rowVideos)) {
            $hook = self::deriveHookFromMessage($message ?? ($row['postMessage'] ?? ''));
            $rowVideos = array_map(function ($v) use ($hook) {
                $thumb = null;
                if (preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|ggpht\.com)/#i', $v)) {
                    $thumb = preg_replace('/=.*$/i', '=s1600-no', $v);
                }
                return [
                    'type' => 'direct',
                    'videoId' => null,
                    'thumbnailUrl' => $thumb,
                    'embedUrl' => \App\services\LinkPreviewService::getProxiedVideoUrl($v),
                    'originalUrl' => $v,
                    'trimStart' => null,
                    'trimEnd' => null,
                    'hookText' => $hook,
                    'aspectMode' => 'reel',
                ];
            }, $existingExternalVideos);
            $row['videos'] = $rowVideos;
            $row['video'] = $rowVideos[0];
        }

        // Hydrate images array from post_img0..5 slots if not already hydrated, filtering out video streams
        if (empty($row['images'])) {
            $slotImages = [];
            for ($i = 0; $i < 6; $i++) {
                $colVal = trim((string)($row['post_img' . $i] ?? ''));
                if ($colVal !== '' && $colVal !== 'null' && $colVal !== 'undefined' && !\App\classes\VideoParser::isVideoUrl($colVal)) {
                    $slotImages[] = $colVal;
                }
            }
            if (!empty($slotImages)) {
                $row['images'] = $slotImages;
            }
        }

        /** @var array<int, mixed> $postTags */
        $postTags = isset($row['post_tags']) && is_array($row['post_tags']) ? array_values($row['post_tags']) : [];

        $viewsCount = max(0, (int)($row['views_count'] ?? 0));
        $voiceCapsuleUrl = !empty($row['voice_capsule_url']) ? (string)$row['voice_capsule_url'] : null;
        $arEmoji = !empty($row['ar_emoji']) ? (string)$row['ar_emoji'] : null;
        if ($arEmoji === null && preg_match('/:([a-z0-9_]+_3d):/i', $message, $m)) {
            $arEmoji = $m[1];
        }
        $isOfficialPost = ($id === \App\services\FamilyPlatformAccountService::getAccountId()) || !empty($row['is_official']);
        if ($isOfficialPost && (empty($profileImg) || str_contains($profileImg, 'avatarM') || str_contains($profileImg, 'avatarF'))) {
            $profileImg = '/images/logo/logo.png';
        }

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
            twoTruths: $twoTruths,
            reactions: $reactions,
            linkPreview: $linkPreview,
            postTags: $postTags,
            rawAttributes: $row,
            viewsCount: $viewsCount,
            voiceCapsuleUrl: $voiceCapsuleUrl,
            isOfficialPost: $isOfficialPost,
            arEmoji: $arEmoji,
            videos: $rowVideos,
            video: $rowVideos[0] ?? null,
            targetScope: (string)($row['target_scope'] ?? 'all')
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
        $arr['two_truths'] = $this->twoTruths;
        $arr['reactions'] = $this->reactions;
        $arr['link_preview'] = $this->linkPreview;
        $arr['post_tags'] = $this->postTags;
        $arr['views_count'] = $this->viewsCount;
        $arr['target_scope'] = $this->targetScope;
        $arr['voice_capsule_url'] = $this->voiceCapsuleUrl;
        $arr['is_official'] = $this->isOfficialPost;
        $arr['ar_emoji'] = $this->arEmoji;
        $arr['videos'] = $this->videos;
        $arr['video'] = $this->video;

        if (isset($this->rawAttributes['images'])) {
            $arr['images'] = $this->rawAttributes['images'];
        }
        for ($i = 0; $i < 6; $i++) {
            if (isset($this->rawAttributes['post_img' . $i])) {
                $arr['post_img' . $i] = $this->rawAttributes['post_img' . $i];
            }
        }
        if (isset($this->rawAttributes['displayMessage'])) {
            $arr['displayMessage'] = $this->rawAttributes['displayMessage'];
            $arr['cleanMessage'] = $this->rawAttributes['displayMessage'];
        }

        return $arr;
    }

    /**
     * Intelligently derives a punchy viral/story title hook from post message content.
     * Respects British English conventions and bounds length to prevent DOM overflow.
     */
    public static function deriveHookFromMessage(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }
        $raw = \App\services\EmojiPlainText::humanize($raw);
        $clean = preg_replace('#(?:https?://|/resources/(?:videos|images)/)[^\s"\'<>]+#i', '', $raw);
        $clean = preg_replace('/#[a-zA-Z0-9_]+/', '', (string)$clean);
        $clean = trim((string)$clean);
        if ($clean === '') {
            return null;
        }
        // Extract first sentence or paragraph
        $parts = preg_split('/[\r\n]+|[.!?]\s+/', $clean);
        if (!is_array($parts) || empty($parts)) {
            return null;
        }
        $candidate = trim((string)$parts[0]);
        if ($candidate === '') {
            return null;
        }
        if (mb_strlen($candidate) <= 80) {
            return $candidate;
        }
        $sub = mb_substr($candidate, 0, 75);
        $lastSpace = mb_strrpos($sub, ' ');
        return ($lastSpace !== false && $lastSpace > 25 ? mb_substr($sub, 0, $lastSpace) : $sub) . '...';
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
