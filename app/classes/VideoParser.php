<?php
declare(strict_types=1);

namespace App\classes;

final class VideoParser
{
    /**
     * Parse a single URL string for supported video providers (YouTube, Vimeo, Cloudflare, TikTok, Dropbox, Direct Video).
     *
     * @param string|null $url
     * @return array{type: string, videoId: string|null, thumbnailUrl: string|null, embedUrl: string, originalUrl: string, hlsUrl?: string|null, trimStart?: float|null, trimEnd?: float|null, isShort?: bool, aspect_ratio?: string}|null
     */
    public static function parseVideoUrl(?string $url): ?array
    {
        if ($url === null) {
            return null;
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        // 1. YouTube Matcher
        // Matches: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID, youtube.com/live/ID
        $ytRegex = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i';
        if (preg_match($ytRegex, $trimmed, $matches) === 1) {
            $videoId = $matches[1];
            return [
                'type' => 'youtube',
                'videoId' => $videoId,
                'thumbnailUrl' => "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
                'embedUrl' => "https://www.youtube.com/embed/{$videoId}?autoplay=1&rel=0&playsinline=1",
                'originalUrl' => $trimmed,
            ];
        }

        // 2. Vimeo Matcher
        $vimeoRegex = '/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/i';
        if (preg_match($vimeoRegex, $trimmed, $matches) === 1) {
            $vimeoId = !empty($matches[3]) ? $matches[3] : (!empty($matches[2]) ? $matches[2] : $matches[1]);
            if ($vimeoId !== '') {
                return [
                    'type' => 'vimeo',
                    'videoId' => $vimeoId,
                    'thumbnailUrl' => "https://vumbnail.com/{$vimeoId}.jpg",
                    'embedUrl' => "https://player.vimeo.com/video/{$vimeoId}?autoplay=1",
                    'originalUrl' => $trimmed,
                ];
            }
        }

        // 3. Cloudflare Stream Matcher (supports iframe, watch, customer subdomain, or .m3u8 manifest)
        $cfStreamRegex = '/(?:https?:\/\/)?(?:iframe\.videodelivery\.net|videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)\/([a-zA-Z0-9]{32})/i';
        if (preg_match($cfStreamRegex, $trimmed, $matches) === 1) {
            $cfUid = $matches[1];
            $cfSubdomain = preg_replace('/[^a-zA-Z0-9.\-]/', '', (string)($_ENV['CLOUDFLARE_STREAM_SUBDOMAIN'] ?? getenv('CLOUDFLARE_STREAM_SUBDOMAIN') ?: '')) ?: '';
            $hlsHost = $cfSubdomain !== '' ? $cfSubdomain : 'videodelivery.net';
            return [
                'type' => 'cloudflare',
                'videoId' => $cfUid,
                'thumbnailUrl' => "https://{$hlsHost}/{$cfUid}/thumbnails/thumbnail.jpg",
                'embedUrl' => "https://iframe.videodelivery.net/{$cfUid}?preload=true&autoplay=true&muted=true&loop=true",
                'hlsUrl' => "https://{$hlsHost}/{$cfUid}/manifest/video.m3u8",
                'originalUrl' => $trimmed,
            ];
        }

        // 3b. TikTok (tiktok.com/@user/video/ID or tiktok.com/embed/v2/ID)
        if (preg_match('/^https?:\/\/(?:www\.|m\.)?tiktok\.com\/(?:@[\w.-]+\/video|embed\/v2|v)\/(\d{8,25})(?:[\/?#].*)?$/i', $trimmed, $ttMatches) === 1) {
            $ttId = $ttMatches[1];
            return [
                'type' => 'tiktok',
                'videoId' => $ttId,
                'thumbnailUrl' => null,
                'embedUrl' => "https://www.tiktok.com/embed/v2/{$ttId}",
                'originalUrl' => $trimmed,
            ];
        }

        // 3c. Dropbox shared video files — streamed as direct video via raw=1
        $dropboxRegex = '/^https?:\/\/(?:www\.|dl\.)?dropbox(?:usercontent)?\.com\/(?:s|scl\/fi)\/[^\s"\'<>?#]+\.(?:mp4|webm|mov|ogg|m4v)(?:\?[^\s"\'<>#]*)?$/i';
        if (preg_match($dropboxRegex, $trimmed) === 1) {
            $rawStream = self::normaliseDropboxStreamUrl($trimmed);
            return [
                'type' => 'direct',
                'videoId' => null,
                'thumbnailUrl' => null,
                'embedUrl' => $rawStream,
                'originalUrl' => $rawStream,
                'trimStart' => null,
                'trimEnd' => null,
            ];
        }

        // 3d. Already-proxied /resources/videos/external-stream?(url|ig)=... or legacy /member/stream-video?url=...
        if (preg_match('/^\/(?:resources\/videos\/external-stream|member\/stream-video)\?(?:url=([^\s"\'<>&#]+)|ig=([A-Za-z0-9_-]{5,24}))/i', $trimmed, $proxyMatches) === 1) {
            $igCode = $proxyMatches[2] ?? '';
            if ($igCode !== '') {
                return [
                    'type' => 'direct',
                    'videoId' => 'ig_' . $igCode,
                    'thumbnailUrl' => null,
                    'embedUrl' => $trimmed,
                    'originalUrl' => 'https://www.instagram.com/reel/' . $igCode . '/',
                    'isShort' => true,
                    'aspect_ratio' => '9 / 16',
                    'trimStart' => null,
                    'trimEnd' => null,
                ];
            }
            $decodedInner = rawurldecode($proxyMatches[1]);
            $thumb = (stripos($decodedInner, 'googleusercontent.com') !== false)
                ? (string)preg_replace('/=[^\/=?#]+$/i', '=s1600-no', $decodedInner)
                : null;
            $embedUrl = str_starts_with($trimmed, '/resources/videos/external-stream')
                ? $trimmed
                : \App\services\LinkPreviewService::getProxiedVideoUrl($decodedInner);
            return [
                'type' => 'direct',
                'videoId' => null,
                'thumbnailUrl' => $thumb,
                'embedUrl' => $embedUrl,
                'originalUrl' => $decodedInner,
                'trimStart' => null,
                'trimEnd' => null,
            ];
        }

        // 4. Direct HTML5 Video (.mp4, .webm, .ogg, .mov, .m4v, .m3u8) — supports absolute http(s), sovereign /resources/ or /public/uploads/ paths, or bare post_video filenames with optional #t=start,end
        $directVideoRegex = '/^(?:https?:\/\/[^\s"\'<>]+|\/(?:resources\/(?:videos|images)|public\/uploads)\/[^\s"\'<>]+|[a-zA-Z0-9_\-]+)\.(mp4|webm|ogg|mov|m4v|m3u8)(?:\?[^\s"\'<>#]*)?(?:#t=([0-9]+(?:\.[0-9]+)?)(?:,([0-9]+(?:\.[0-9]+)?))?)?$/i';
        if (preg_match($directVideoRegex, $trimmed, $directMatches) === 1) {
            $ext = strtolower($directMatches[1]);
            $trimStart = isset($directMatches[2]) && is_numeric($directMatches[2]) ? (float)$directMatches[2] : null;
            $trimEnd = isset($directMatches[3]) && is_numeric($directMatches[3]) ? (float)$directMatches[3] : null;
            $normalisedUrl = $trimmed;
            if (!str_starts_with($normalisedUrl, 'http://') && !str_starts_with($normalisedUrl, 'https://') && !str_starts_with($normalisedUrl, '/')) {
                $normalisedUrl = '/resources/videos/posts/' . $normalisedUrl;
            }
            return [
                'type' => 'direct',
                'videoId' => null,
                'thumbnailUrl' => null,
                'embedUrl' => $normalisedUrl,
                'hlsUrl' => $ext === 'm3u8' ? $normalisedUrl : null,
                'originalUrl' => $normalisedUrl,
                'trimStart' => $trimStart,
                'trimEnd' => $trimEnd,
            ];
        }

        // 5. Google Photos direct video stream (=m22, =m18, =m37, =dv or googlevideo.com)
        $gpVideoRegex = '/^(?:https?:\/\/[^\s"\'<>]*(?:googleusercontent\.com\/[^\s"\'<>]*=(?:m18|m22|m37|dv)|googlevideo\.com\/videoplayback[^\s"\'<>]*))$/i';
        if (preg_match($gpVideoRegex, $trimmed) === 1) {
            $thumb = (stripos($trimmed, 'googleusercontent.com') !== false)
                ? (string)preg_replace('/=[^\/=?#]+$/i', '=s1600-no', $trimmed)
                : null;
            return [
                'type' => 'direct',
                'videoId' => null,
                'thumbnailUrl' => $thumb,
                'embedUrl' => \App\services\LinkPreviewService::getProxiedVideoUrl($trimmed),
                'originalUrl' => $trimmed,
                'trimStart' => null,
                'trimEnd' => null,
            ];
        }

        // 6. Facebook Video / Reel / Watch (in-platform iframe via official Facebook video plugin)
        // Note: Opaque /share/r/ and /share/v/ links are excluded because Facebook's plugins/video.php
        // cannot play share-redirect URLs and renders "Video unavailable"; they render via Rich Link Preview instead.
        $fbVideoRegex = '/^https?:\/\/(?:www\.|m\.|web\.)?(?:facebook\.com\/(?:[^\/\s]+\/videos\/\d+|watch\/?\?v=\d+|reel\/\d+|video\.php\?v=\d+)|fb\.watch\/[^\s"\'<>]+|fb\.com\/(?:watch|reel|[^\/\s]+\/videos)\/[^\s"\'<>]+)/i';
        if (preg_match($fbVideoRegex, $trimmed) === 1) {
            $isFbReel = preg_match('/(?:facebook\.com|fb\.com)\/reel\/\d+/i', $trimmed) === 1;
            $desc = [
                'type' => 'facebook',
                'videoId' => null,
                'thumbnailUrl' => null,
                'embedUrl' => 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($trimmed) . '&show_text=false&autoplay=0',
                'originalUrl' => $trimmed,
            ];
            if ($isFbReel) {
                $desc['isShort'] = true;
                $desc['aspect_ratio'] = '9 / 16';
            }
            return $desc;
        }

        // 7. Google Drive Video (drive.google.com/file/d/{id}/... or drive.google.com/open?id={id})
        if (preg_match('/^https?:\/\/drive\.google\.com\/(?:file\/d\/([a-zA-Z0-9_-]{10,})|open\?id=([a-zA-Z0-9_-]{10,}))/i', $trimmed, $gdMatches) === 1) {
            $gdId = $gdMatches[1] !== '' ? $gdMatches[1] : $gdMatches[2];
            return [
                'type' => 'googledrive',
                'videoId' => $gdId,
                'thumbnailUrl' => null,
                'embedUrl' => 'https://drive.google.com/file/d/' . $gdId . '/preview',
                'originalUrl' => $trimmed,
            ];
        }

        // 8. OneDrive Video / Embed (onedrive.live.com/embed, onedrive.live.com/redir, 1drv.ms/v/)
        if (preg_match('/^https?:\/\/(?:onedrive\.live\.com\/(?:embed|redir|\?)|1drv\.ms\/v\/)[^\s"\'<>]+/i', $trimmed) === 1) {
            $odEmbed = self::normaliseOneDriveEmbedUrl($trimmed);
            return [
                'type' => 'onedrive',
                'videoId' => null,
                'thumbnailUrl' => null,
                'embedUrl' => $odEmbed,
                'originalUrl' => $trimmed,
            ];
        }

        return null;
    }

    /**
     * Normalise OneDrive share URLs into an embeddable player URL where possible.
     */
    public static function normaliseOneDriveEmbedUrl(string $url): string
    {
        $trimmed = trim($url);
        if (preg_match('#^https?://onedrive\.live\.com/embed\b#i', $trimmed) === 1) {
            return $trimmed;
        }
        if (preg_match('#^https?://onedrive\.live\.com/(?:redir|view\.aspx)?\?(.+)$#i', $trimmed, $m) === 1) {
            return 'https://onedrive.live.com/embed?' . $m[1];
        }
        return $trimmed;
    }

    /**
     * Rewrite Dropbox shared URLs so HTML5 <video> receives a raw byte stream instead of an HTML viewer.
     */
    public static function normaliseDropboxStreamUrl(string $url): string
    {
        $trimmed = trim($url);
        if (stripos($trimmed, 'dropbox.com') === false) {
            return $trimmed;
        }
        if (preg_match('/([?&])dl=[01]/i', $trimmed) === 1) {
            return (string)preg_replace('/([?&])dl=[01]/i', '$1raw=1', $trimmed);
        }
        if (stripos($trimmed, 'raw=1') === false) {
            return $trimmed . (str_contains($trimmed, '?') ? '&raw=1' : '?raw=1');
        }
        return $trimmed;
    }

    /**
     * Format any video URL or file path into a normalized frontend-ready video descriptor array.
     *
     * @param string $url
     * @param string|null $hookText
     * @return array{type: string, videoId: string|null, thumbnailUrl: string|null, embedUrl: string, hlsUrl: string|null, originalUrl: string, trimStart: float|null, trimEnd: float|null, hook: string|null}
     */
    public static function formatVideoDescriptor(string $url, ?string $hookText = null): array
    {
        $trimmed = trim($url);
        $hook = ($hookText !== null && trim($hookText) !== '') ? trim($hookText) : null;
        $parsed = self::parseVideoUrl($trimmed);

        if ($parsed !== null) {
            $type = $parsed['type'];
            $vidId = $parsed['videoId'];
            $origUrl = $parsed['originalUrl'];
            $thumb = $parsed['thumbnailUrl'];
            $trimStart = $parsed['trimStart'] ?? null;
            $trimEnd = $parsed['trimEnd'] ?? null;

            if ($type === 'youtube' && $vidId !== null) {
                $embedUrl = "https://www.youtube.com/embed/{$vidId}?autoplay=1&rel=0&playsinline=1";
            } elseif ($type === 'vimeo' && $vidId !== null) {
                $embedUrl = "https://player.vimeo.com/video/{$vidId}?autoplay=1";
            } elseif ($type === 'cloudflare' && $vidId !== null) {
                $embedUrl = "https://iframe.videodelivery.net/{$vidId}?preload=true&autoplay=true&muted=true&loop=true";
            } elseif ($type === 'tiktok' && $vidId !== null) {
                $embedUrl = "https://www.tiktok.com/embed/v2/{$vidId}";
            } elseif (in_array($type, ['facebook', 'googledrive', 'onedrive'], true)) {
                $embedUrl = $parsed['embedUrl'];
            } else {
                $isExternalGoogleStream = str_starts_with($origUrl, 'http')
                    && (stripos($origUrl, 'googleusercontent.com') !== false || stripos($origUrl, 'googlevideo.com') !== false);
                if ($isExternalGoogleStream) {
                    $embedUrl = \App\services\LinkPreviewService::getProxiedVideoUrl($origUrl);
                    if ($thumb === null && stripos($origUrl, 'googleusercontent.com') !== false) {
                        $thumb = (string)preg_replace('/=[^\/=?#]+$/i', '=s1600-no', $origUrl);
                    }
                } elseif (stripos($origUrl, 'dropbox.com') !== false) {
                    $embedUrl = self::normaliseDropboxStreamUrl($origUrl);
                } else {
                    $embedUrl = $origUrl;
                }
            }

            return [
                'type' => $type,
                'videoId' => $vidId,
                'thumbnailUrl' => $thumb,
                'embedUrl' => $embedUrl,
                'hlsUrl' => $parsed['hlsUrl'] ?? null,
                'originalUrl' => $origUrl,
                'trimStart' => $trimStart,
                'trimEnd' => $trimEnd,
                'hook' => $hook,
            ];
        }

        // Fallback for unrecognized external/local stream URLs
        $fallbackUrl = $trimmed;
        if (!str_starts_with($fallbackUrl, 'http://') && !str_starts_with($fallbackUrl, 'https://') && !str_starts_with($fallbackUrl, '/')) {
            $fallbackUrl = '/resources/videos/posts/' . $fallbackUrl;
        }
        $isExternalStream = str_starts_with($fallbackUrl, 'http')
            && (stripos($fallbackUrl, 'googleusercontent.com') !== false || stripos($fallbackUrl, 'googlevideo.com') !== false);
        $thumb = $isExternalStream ? (string)preg_replace('/=[^\/=?#]+$/i', '=s1600-no', $fallbackUrl) : null;
        $embedUrl = $isExternalStream
            ? \App\services\LinkPreviewService::getProxiedVideoUrl($fallbackUrl)
            : (stripos($fallbackUrl, 'dropbox.com') !== false ? self::normaliseDropboxStreamUrl($fallbackUrl) : $fallbackUrl);

        return [
            'type' => 'direct',
            'videoId' => null,
            'thumbnailUrl' => $thumb,
            'embedUrl' => $embedUrl,
            'hlsUrl' => null,
            'originalUrl' => $fallbackUrl,
            'trimStart' => null,
            'trimEnd' => null,
            'hook' => $hook,
        ];
    }

    /**
     * Extract all supported videos found within a text block (up to $max).
     *
     * @param string|null $text
     * @param int $max
     * @return list<array{type: string, videoId: string|null, thumbnailUrl: string|null, embedUrl: string, originalUrl: string, trimStart: float|null, trimEnd: float|null, hook: string|null}>
     */
    public static function extractAllVideosFromText(?string $text, int $max = 6): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $results = [];
        $seen = [];
        $urlRegex = '/((?:https?:\/\/|\/resources\/(?:videos|images)\/)[^\s"\'<>]+)/i';
        if (preg_match_all($urlRegex, $text, $matches) > 0 && !empty($matches[0])) {
            /** @var list<string> $matchedUrls */
            $matchedUrls = $matches[0];
            foreach ($matchedUrls as $rawUrl) {
                $cleanUrl = rtrim($rawUrl, '.,;:!?)');
                if (isset($seen[$cleanUrl])) {
                    continue;
                }
                if (self::parseVideoUrl($cleanUrl) !== null) {
                    $seen[$cleanUrl] = true;
                    $results[] = self::formatVideoDescriptor($cleanUrl);
                    if (count($results) >= $max) {
                        break;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Check if a given URL is a supported video URL or direct video stream.
     *
     * @param string|null $url
     * @return bool
     */
    public static function isVideoUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }
        $trimmed = trim($url);
        if (preg_match('/(\.(mp4|webm|ogg|mov)(\?.*)?$)|(googleusercontent\.com\/[^\s"\'<>]*=(?:m18|m22|m37|dv))|(googlevideo\.com\/videoplayback)/i', $trimmed) === 1) {
            return true;
        }
        return self::parseVideoUrl($trimmed) !== null;
    }

    /**
     * Check if a given URL is an opaque social share page URL (e.g. facebook.com/share/r/...)
     * that must never be rendered as a raw <img> tag even if previously persisted in post_img0..5.
     */
    public static function isNonImageShareUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }
        return preg_match('/^https?:\/\/(?:www\.|m\.|web\.)?facebook\.com\/share\/[a-z]+\/[^\s"\'<>]+/i', trim($url)) === 1;
    }

    /**
     * Extract the first supported video found within a text block.
     *
     * @param string|null $text
     * @return array{type: string, videoId: string|null, thumbnailUrl: string|null, originalUrl: string, trimStart?: float|null, trimEnd?: float|null}|null
     */
    public static function extractVideoFromText(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $urlRegex = '/((?:https?:\/\/|\/resources\/(?:videos|images)\/)[^\s"\'<>]+)/i';
        if (preg_match_all($urlRegex, $text, $matches) > 0 && !empty($matches[0])) {
            /** @var list<string> $matchedUrls */
            $matchedUrls = $matches[0];
            foreach ($matchedUrls as $url) {
                $parsed = self::parseVideoUrl($url);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        }

        return null;
    }

    /**
     * Clean raw video URL from text message, leaving only user-written caption.
     *
     * @param string|null $text
     * @param array{type: string, videoId: string|null, thumbnailUrl: string|null, originalUrl: string, trimStart?: float|null, trimEnd?: float|null}|null $video
     * @return string
     */
    public static function cleanPostMessage(?string $text, ?array $video): string
    {
        if ($text === null) {
            return '';
        }

        if ($video === null || empty($video['originalUrl'])) {
            return trim($text);
        }

        $cleaned = str_replace($video['originalUrl'], '', $text);
        return trim($cleaned);
    }

    /**
     * Validate and normalise client-submitted or DB-stored media_meta JSON payload.
     *
     * @param mixed $rawMeta
     * @return array{focal_x?: float, focal_y?: float, aspect_ratio?: string, duration_sec?: float, hls_url?: string}|null
     */
    public static function normaliseMediaMeta(mixed $rawMeta): ?array
    {
        if (is_string($rawMeta)) {
            $decoded = json_decode($rawMeta, true);
            $rawMeta = is_array($decoded) ? $decoded : null;
        }
        if (!is_array($rawMeta) || empty($rawMeta)) {
            return null;
        }

        $clean = [];
        if (isset($rawMeta['focal_x']) && is_numeric($rawMeta['focal_x'])) {
            $clean['focal_x'] = round(max(0.0, min(100.0, (float)$rawMeta['focal_x'])), 2);
        }
        if (isset($rawMeta['focal_y']) && is_numeric($rawMeta['focal_y'])) {
            $clean['focal_y'] = round(max(0.0, min(100.0, (float)$rawMeta['focal_y'])), 2);
        }
        if (isset($rawMeta['aspect_ratio']) && is_string($rawMeta['aspect_ratio'])) {
            $ar = trim($rawMeta['aspect_ratio']);
            if (preg_match('/^\d+(?:\.\d+)?\s*\/\s*\d+(?:\.\d+)?$/', $ar) === 1) {
                $clean['aspect_ratio'] = $ar;
            }
        }
        if (isset($rawMeta['duration_sec']) && is_numeric($rawMeta['duration_sec'])) {
            $dur = (float)$rawMeta['duration_sec'];
            if ($dur > 0.0 && $dur <= 36000.0) {
                $clean['duration_sec'] = round($dur, 2);
            }
        }
        if (isset($rawMeta['hls_url']) && is_string($rawMeta['hls_url'])) {
            $hls = trim($rawMeta['hls_url']);
            if (
                $hls !== ''
                && (str_starts_with($hls, 'https://') || str_starts_with($hls, '/resources/videos/'))
                && str_contains(strtolower($hls), '.m3u8')
                && !preg_match('/[\s"\'<>]/', $hls)
            ) {
                $clean['hls_url'] = $hls;
            }
        }
        if (isset($rawMeta['poster_url']) && is_string($rawMeta['poster_url'])) {
            $poster = trim($rawMeta['poster_url']);
            if (
                $poster !== ''
                && (str_starts_with($poster, 'https://') || str_starts_with($poster, '/public/uploads/') || str_starts_with($poster, '/resources/'))
                && !preg_match('/[\s"\'<>]/', $poster)
            ) {
                $clean['poster_url'] = $poster;
            }
        }

        return !empty($clean) ? $clean : null;
    }

    /**
     * Format pixel dimensions into a simplified "W / H" aspect ratio string.
     */
    public static function formatAspectRatio(int $width, int $height): ?string
    {
        if ($width <= 0 || $height <= 0) {
            return null;
        }
        $a = $width;
        $b = $height;
        while ($b !== 0) {
            $t = $a % $b;
            $a = $b;
            $b = $t;
        }
        $gcd = max(1, $a);
        return (int)($width / $gcd) . ' / ' . (int)($height / $gcd);
    }

    /**
     * Merge persisted media_meta fields into a formatted video descriptor.
     *
     * @param array<string, mixed> $descriptor
     * @param array<string, mixed>|null $mediaMeta
     * @return array<string, mixed>
     */
    public static function mergeMediaMetaIntoDescriptor(array $descriptor, ?array $mediaMeta): array
    {
        if (empty($mediaMeta)) {
            return $descriptor;
        }
        if (isset($mediaMeta['focal_x'])) {
            $descriptor['focal_x'] = $mediaMeta['focal_x'];
        }
        if (isset($mediaMeta['focal_y'])) {
            $descriptor['focal_y'] = $mediaMeta['focal_y'];
        }
        if (isset($mediaMeta['aspect_ratio'])) {
            $descriptor['aspect_ratio'] = $mediaMeta['aspect_ratio'];
        }
        if (isset($mediaMeta['duration_sec'])) {
            $descriptor['duration_sec'] = $mediaMeta['duration_sec'];
        }
        if (!empty($mediaMeta['hls_url'])) {
            $descriptor['hlsUrl'] = $mediaMeta['hls_url'];
        }
        if (!empty($mediaMeta['poster_url'])) {
            $descriptor['thumbnailUrl'] = $mediaMeta['poster_url'];
        }
        return $descriptor;
    }
}
