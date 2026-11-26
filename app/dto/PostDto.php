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
    public const array ALLOWED_TEXT_STYLES = [
        'starlight_bokeh',
        'golden_ember',
        'sunset_horizon',
        'emerald_canopy',
        'ocean_biolum',
        'rose_stardust',
        'northern_sky',
        'royal_parchment',
        'xmas_snowfall',
        'xmas_hearth',
        'xmas_lights',
        'xmas_winter_gold',
        'solar_galaxy',
        'andromeda_nebula',
        'binary_eclipse',
        'supernova_crown',
        'cosmic_rings',
        'ocean_waves',
        'moving_sunset',
        'family_gathering',
        'cyber_glass',
        'alert_pulse',
        'grand_announcement',
        'dynasty_holo',
        'aurora_prism',
        'heirloom_velvet',
    ];

    /**
     * Normalize and validate a 3D text stage style slug against the strict allowlist.
     */
    public static function cleanPostStyle(mixed $raw): ?string
    {
        if (!is_string($raw)) {
            return null;
        }
        $slug = strtolower(trim($raw));
        if ($slug === '' || $slug === 'none' || $slug === 'plain') {
            return null;
        }
        return in_array($slug, self::ALLOWED_TEXT_STYLES, true) ? $slug : null;
    }

    /**
     * Extract a `[style:<slug>]` token from raw postMessage if present.
     */
    public static function extractStyleFromMessage(string $message): ?string
    {
        if (preg_match('/\[style:([a-z0-9_]+)\]/i', $message, $m)) {
            return self::cleanPostStyle($m[1]);
        }
        return null;
    }

    /**
     * Strip any `[style:<slug>]` token from visible message text.
     */
    public static function stripStyleToken(string $message): string
    {
        if (!str_contains($message, '[style:')) {
            return $message;
        }
        return trim((string)preg_replace('/\s*\[style:[a-z0-9_]+\]\s*/i', ' ', $message));
    }

    /**
     * Stamp or replace the `[style:<slug>]` token inside a message string for persistence.
     */
    public static function embedStyleToken(string $message, ?string $styleSlug): string
    {
        $cleanMsg = self::stripStyleToken($message);
        $validStyle = self::cleanPostStyle($styleSlug);
        if ($validStyle === null) {
            return $cleanMsg;
        }
        return $cleanMsg !== '' ? $cleanMsg . ' [style:' . $validStyle . ']' : '[style:' . $validStyle . ']';
    }
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
     * @param list<array<string, mixed>> $normalizedMedia
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
        public readonly string $targetScope = 'all',
        public readonly ?string $postStyle = null,
        public readonly bool $isHistorianPost = false,
        public readonly array $normalizedMedia = [],
        public readonly bool $allowSharing = true
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
        if ($fullName === '' || $fullName === 'Family Member' || ($id !== '' && strcasecmp($fullName, $id) === 0)) {
            if (!empty($row['personalFullName']) && is_string($row['personalFullName'])) {
                $fullName = trim($row['personalFullName']);
            }
        }
        if ($fullName === '' || ($id !== '' && strcasecmp($fullName, $id) === 0)) {
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
        if ($linkPreview === null && !$alreadyHydratedLinkPreview && \App\services\LinkPreviewService::containsPotentialUrl((string)$message)) {
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
                    } elseif (\App\classes\VideoParser::isNonImageShareUrl($colVal)) {
                        if ($linkPreview === null) {
                            $linkPreview = \App\services\LinkPreviewService::fetchPreview($colVal);
                        }
                    } else {
                        $existingExternalImages[] = $colVal;
                    }
                }
            }
        }

        $rowVideos = isset($row['videos']) && is_array($row['videos']) ? $row['videos'] : [];
        $hasPreExtractedMedia = !empty($rowVideos) || !empty($row['images']) || !empty($existingExternalImages) || !empty($existingExternalVideos);
        $hasExternalLinkInMessage = !empty($message) && (str_contains($message, 'http://') || str_contains($message, 'https://'));
        $hasLocalVideoInMessage = !empty($message) && str_contains($message, '/resources/videos/');

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
                $rowVideos = array_map(function ($v) use ($hook, $message) {
                    $desc = \App\classes\VideoParser::formatVideoDescriptor((string)$v, $hook, $message);
                    if ($desc['type'] === 'direct' && preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|googlevideo\.com)/#i', $desc['originalUrl'])) {
                        $desc['embedUrl'] = \App\services\LinkPreviewService::getProxiedVideoUrl($desc['originalUrl']);
                    }
                    $desc['hookText'] = $hook;
                    $desc['aspectMode'] = 'reel';
                    return $desc;
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

            $urlsToStripSocial = [];
            // Promote verified Instagram or Facebook Reel/video streams into native in-feed video descriptors
            $isPromotableSocialVideo = empty($rowVideos)
                && is_array($linkPreview)
                && (!empty($linkPreview['is_instagram']) || !empty($linkPreview['is_facebook']))
                && !empty($linkPreview['is_video'])
                && !empty($linkPreview['proxied_video_url']);
            if ($isPromotableSocialVideo) {
                $socialHook = self::deriveHookFromMessage($message);
                $isIgSocial = !empty($linkPreview['is_instagram']);
                $igShortcode = isset($linkPreview['ig_shortcode']) && is_string($linkPreview['ig_shortcode'])
                    ? trim($linkPreview['ig_shortcode'])
                    : '';
                $fbVideoId = isset($linkPreview['fb_video_id']) && is_string($linkPreview['fb_video_id'])
                    ? trim($linkPreview['fb_video_id'])
                    : '';
                $socialDesc = [
                    'type' => 'direct',
                    'provider' => $isIgSocial ? 'instagram' : 'facebook',
                    'videoId' => $isIgSocial
                        ? ($igShortcode !== '' ? ('ig_' . $igShortcode) : null)
                        : ($fbVideoId !== '' ? ('fb_' . $fbVideoId) : null),
                    'thumbnailUrl' => isset($linkPreview['image']) && is_string($linkPreview['image']) ? $linkPreview['image'] : null,
                    'posterUrl' => isset($linkPreview['image']) && is_string($linkPreview['image']) ? $linkPreview['image'] : null,
                    'embedUrl' => (string)$linkPreview['proxied_video_url'],
                    'originalUrl' => (string)($linkPreview['url'] ?? ''),
                    'hlsUrl' => null,
                    'trimStart' => null,
                    'trimEnd' => null,
                    'hook' => $socialHook ?? (isset($linkPreview['title']) && is_string($linkPreview['title']) ? $linkPreview['title'] : null),
                    'hookText' => $socialHook,
                    'aspectMode' => 'reel',
                    'aspect_ratio' => isset($linkPreview['aspect_ratio']) && is_string($linkPreview['aspect_ratio']) ? $linkPreview['aspect_ratio'] : '9 / 16',
                    'isShort' => true,
                    'sourceLabel' => $isIgSocial ? 'Instagram' : 'Facebook',
                    'sourceUrl' => (string)($linkPreview['url'] ?? ''),
                    'sourceTitle' => isset($linkPreview['title']) && is_string($linkPreview['title']) ? $linkPreview['title'] : null,
                ];
                $rowVideos = [$socialDesc];
                $row['videos'] = $rowVideos;
                $row['video'] = $socialDesc;
                $urlsToStripSocial = array_values(array_filter([
                    isset($linkPreview['original_url']) && is_string($linkPreview['original_url']) ? $linkPreview['original_url'] : '',
                    isset($linkPreview['url']) && is_string($linkPreview['url']) ? $linkPreview['url'] : '',
                ]));
            }

            if ($urlsToStripSocial !== []) {
                $baseCleaned = !empty($extractedAssets['cleaned_message']) ? $extractedAssets['cleaned_message'] : $message;
                $row['displayMessage'] = \App\services\LinkPreviewService::cleanPhotoLinkFromMessage($baseCleaned, $urlsToStripSocial);
            } elseif (!empty($extractedAssets['cleaned_message'])) {
                $row['displayMessage'] = $extractedAssets['cleaned_message'];
            } elseif (!empty($finalImages) || !empty($finalVideos)) {
                $row['displayMessage'] = \App\services\LinkPreviewService::cleanPhotoLinkFromMessage(
                    $message,
                    array_merge($finalImages, $finalVideos)
                );
            }
        } elseif ((!empty($existingExternalVideos) || $hasExternalLinkInMessage || $hasLocalVideoInMessage) && empty($rowVideos)) {
            $allVideoUrls = $existingExternalVideos;
            if ($hasExternalLinkInMessage || $hasLocalVideoInMessage) {
                foreach (\App\classes\VideoParser::extractAllVideosFromText($message, 6) as $parsedVid) {
                    if (!in_array($parsedVid['originalUrl'], $allVideoUrls, true) && count($allVideoUrls) < 6) {
                        $allVideoUrls[] = $parsedVid['originalUrl'];
                    }
                }
            }
            if (!empty($allVideoUrls)) {
                $hook = self::deriveHookFromMessage($message ?? ($row['postMessage'] ?? ''));
                $rowVideos = array_map(function ($v) use ($hook, $message) {
                    $desc = \App\classes\VideoParser::formatVideoDescriptor((string)$v, $hook, $message);
                    if ($desc['type'] === 'direct' && preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|googlevideo\.com)/#i', $desc['originalUrl'])) {
                        $desc['embedUrl'] = \App\services\LinkPreviewService::getProxiedVideoUrl($desc['originalUrl']);
                    }
                    $desc['hookText'] = $hook;
                    $desc['aspectMode'] = 'reel';
                    return $desc;
                }, $allVideoUrls);
                $row['videos'] = $rowVideos;
                $row['video'] = $rowVideos[0];
            }
        }

        // Merge persisted media_meta (focal_x, focal_y, aspect_ratio, duration_sec, hls_url) into video descriptors
        $parsedMediaMeta = \App\classes\VideoParser::normaliseMediaMeta($row['media_meta'] ?? null);
        if ($parsedMediaMeta !== null) {
            $row['media_meta'] = $parsedMediaMeta;
            if (!empty($row['videos']) && is_array($row['videos'])) {
                $row['videos'] = array_map(
                    static fn($v) => is_array($v) ? \App\classes\VideoParser::mergeMediaMetaIntoDescriptor($v, $parsedMediaMeta) : $v,
                    $row['videos']
                );
                $row['video'] = $row['videos'][0] ?? ($row['video'] ?? null);
            } elseif (!empty($row['video']) && is_array($row['video'])) {
                $row['video'] = \App\classes\VideoParser::mergeMediaMetaIntoDescriptor($row['video'], $parsedMediaMeta);
            }
        }

        // Hydrate images array from post_img0..5 slots if not already hydrated, filtering out video streams
        if (empty($row['images'])) {
            $slotImages = [];
            for ($i = 0; $i < 6; $i++) {
                $colVal = trim((string)($row['post_img' . $i] ?? ''));
                if (
                    $colVal !== ''
                    && $colVal !== 'null'
                    && $colVal !== 'undefined'
                    && !\App\classes\VideoParser::isVideoUrl($colVal)
                    && !\App\classes\VideoParser::isNonImageShareUrl($colVal)
                ) {
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
        $postStyle = self::cleanPostStyle($row['post_style'] ?? null) ?? self::extractStyleFromMessage($message);
        if ($postStyle !== null && str_contains($message, '[style:')) {
            $row['displayMessage'] = self::stripStyleToken(isset($row['displayMessage']) && is_string($row['displayMessage']) ? $row['displayMessage'] : $message);
        }
        $isOfficialPost = ($id === \App\services\FamilyPlatformAccountService::getAccountId()) || !empty($row['is_official']);
        if ($isOfficialPost && (empty($profileImg) || str_contains($profileImg, 'avatarM') || str_contains($profileImg, 'avatarF') || str_contains($profileImg, 'default-avatar'))) {
            $profileImg = '/images/logo/logo.png';
        }
        $isHistorianPost = ($id === \App\services\FamilyHistorianService::HISTORIAN_ID) || !empty($row['is_historian']);
        if ($isHistorianPost) {
            $profileImg = \App\services\FamilyHistorianService::HISTORIAN_AVATAR;
        }

        /** @var list<string> $slotImagesForNorm */
        $slotImagesForNorm = isset($row['images']) && is_array($row['images']) ? array_values(array_filter($row['images'], 'is_string')) : [];
        $normalizedMedia = self::buildNormalizedMedia($postNo, $slotImagesForNorm, $rowVideos, $linkPreview);

        $allowSharing = true;
        if (array_key_exists('allow_sharing', $row)) {
            $rawShareFlag = $row['allow_sharing'];
            $allowSharing = !($rawShareFlag === false || $rawShareFlag === 0 || $rawShareFlag === '0' || $rawShareFlag === 'false' || $rawShareFlag === 'off');
        } elseif (is_array($parsedMediaMeta) && array_key_exists('allow_sharing', $parsedMediaMeta)) {
            $allowSharing = (bool)$parsedMediaMeta['allow_sharing'];
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
            targetScope: (string)($row['target_scope'] ?? 'all'),
            postStyle: $postStyle,
            isHistorianPost: $isHistorianPost,
            normalizedMedia: $normalizedMedia,
            allowSharing: $allowSharing
        );
    }

    /**
     * Build unified NormalizedMedia contract items for images, videos, and link previews.
     *
     * @param int $postNo
     * @param list<string> $images
     * @param array<int, mixed> $videos
     * @param array<string, mixed>|null $linkPreview
     * @return list<array<string, mixed>>
     */
    public static function buildNormalizedMedia(int $postNo, array $images, array $videos, ?array $linkPreview): array
    {
        $items = [];

        foreach ($images as $idx => $imgUrl) {
            $cleanUrl = trim($imgUrl);
            if ($cleanUrl === '') {
                continue;
            }
            $items[] = [
                'id' => "post-{$postNo}-img-{$idx}",
                'provider' => 'image',
                'renderMode' => 'image',
                'aspectRatio' => null,
                'posterUrl' => $cleanUrl,
                'videoSrc' => null,
                'hlsUrl' => null,
                'embedSrc' => null,
                'originalUrl' => $cleanUrl,
                'title' => null,
                'durationSec' => null,
                'focalX' => null,
                'focalY' => null,
                'isShort' => false,
            ];
        }

        foreach ($videos as $idx => $vid) {
            if (!is_array($vid)) {
                continue;
            }
            $provider = (string)($vid['type'] ?? 'direct');
            $embedUrl = isset($vid['embedUrl']) && is_string($vid['embedUrl']) ? $vid['embedUrl'] : null;
            $origUrl = isset($vid['originalUrl']) && is_string($vid['originalUrl']) ? $vid['originalUrl'] : null;
            $hlsUrl = isset($vid['hlsUrl']) && is_string($vid['hlsUrl']) ? $vid['hlsUrl'] : null;
            $isNative = $provider === 'direct'
                || (!empty($vid['isDirect']))
                || ($embedUrl !== null && (str_contains($embedUrl, '/resources/videos/') || (bool)preg_match('/\.(mp4|webm|mov|m4v|ogg)(\?|#|$)/i', $embedUrl)));

            $renderMode = $isNative
                ? 'native_video'
                : ($embedUrl !== null && $embedUrl !== '' ? 'inline_iframe' : 'external_card');

            $items[] = [
                'id' => (string)($vid['videoId'] ?? "post-{$postNo}-vid-{$idx}"),
                'provider' => $provider,
                'renderMode' => $renderMode,
                'aspectRatio' => isset($vid['aspect_ratio']) && is_string($vid['aspect_ratio']) ? $vid['aspect_ratio'] : null,
                'posterUrl' => isset($vid['thumbnailUrl']) && is_string($vid['thumbnailUrl']) ? $vid['thumbnailUrl'] : null,
                'videoSrc' => $isNative ? ($embedUrl ?: $origUrl) : null,
                'hlsUrl' => $hlsUrl,
                'embedSrc' => $renderMode === 'inline_iframe' ? $embedUrl : null,
                'originalUrl' => $origUrl ?: $embedUrl,
                'title' => isset($vid['hook']) && is_string($vid['hook']) ? $vid['hook'] : null,
                'durationSec' => isset($vid['duration_sec']) && is_numeric($vid['duration_sec']) ? (float)$vid['duration_sec'] : null,
                'focalX' => isset($vid['focal_x']) && is_numeric($vid['focal_x']) ? (float)$vid['focal_x'] : null,
                'focalY' => isset($vid['focal_y']) && is_numeric($vid['focal_y']) ? (float)$vid['focal_y'] : null,
                'isShort' => !empty($vid['isShort']),
            ];
        }

        if (empty($items) && is_array($linkPreview) && (!empty($linkPreview['url']) || !empty($linkPreview['image']) || !empty($linkPreview['title']))) {
            $frameBlocked = !empty($linkPreview['frame_blocked']);
            $lpEmbed = !$frameBlocked && !empty($linkPreview['embed_url']) && is_string($linkPreview['embed_url'])
                ? $linkPreview['embed_url']
                : null;
            $renderMode = $lpEmbed !== null ? 'inline_iframe' : 'external_card';
            $lpUrl = isset($linkPreview['url']) && is_string($linkPreview['url']) ? $linkPreview['url'] : null;

            $items[] = [
                'id' => "post-{$postNo}-lp-0",
                'provider' => strtolower((string)($linkPreview['site_name'] ?? $linkPreview['domain'] ?? 'external')),
                'renderMode' => $renderMode,
                'aspectRatio' => isset($linkPreview['aspect_ratio']) && is_string($linkPreview['aspect_ratio']) ? $linkPreview['aspect_ratio'] : null,
                'posterUrl' => isset($linkPreview['image']) && is_string($linkPreview['image']) ? $linkPreview['image'] : null,
                'videoSrc' => null,
                'hlsUrl' => null,
                'embedSrc' => $lpEmbed,
                'originalUrl' => $lpUrl,
                'title' => isset($linkPreview['title']) && is_string($linkPreview['title']) ? $linkPreview['title'] : null,
                'durationSec' => null,
                'focalX' => null,
                'focalY' => null,
                'isShort' => false,
            ];
        }

        return $items;
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
        $arr['is_historian'] = $this->isHistorianPost;
        $arr['ar_emoji'] = $this->arEmoji;
        $arr['post_style'] = $this->postStyle;
        $arr['videos'] = $this->videos;
        $arr['video'] = $this->video;
        $arr['normalized_media'] = $this->normalizedMedia;
        $arr['allow_sharing'] = $this->allowSharing;

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
