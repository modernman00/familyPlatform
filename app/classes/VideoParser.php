<?php
declare(strict_types=1);

namespace App\classes;

final class VideoParser
{
    /**
     * Parse a single URL string for supported video providers (YouTube, Vimeo, Direct Video).
     *
     * @param string|null $url
     * @return array{type: string, videoId: string|null, thumbnailUrl: string|null, originalUrl: string, trimStart?: float|null, trimEnd?: float|null}|null
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
                    'originalUrl' => $trimmed,
                ];
            }
        }

        // 3. Cloudflare Stream Matcher
        $cfStreamRegex = '/(?:https?:\/\/)?(?:iframe\.videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)\/([a-zA-Z0-9]{32})/i';
        if (preg_match($cfStreamRegex, $trimmed, $matches) === 1) {
            $cfUid = $matches[1];
            return [
                'type' => 'cloudflare',
                'videoId' => $cfUid,
                'thumbnailUrl' => "https://videodelivery.net/{$cfUid}/thumbnails/thumbnail.jpg",
                'originalUrl' => $trimmed,
            ];
        }

        // 4. Direct HTML5 Video (.mp4, .webm, .ogg, .mov) — supports absolute http(s) and sovereign /resources/videos/ paths with optional #t=start,end
        $directVideoRegex = '/^(?:https?:\/\/[^\s"\'<>]+|\/resources\/(?:videos|images)\/[^\s"\'<>]+)\.(mp4|webm|ogg|mov)(?:\?[^\s"\'<>#]*)?(?:#t=([0-9]+(?:\.[0-9]+)?)(?:,([0-9]+(?:\.[0-9]+)?))?)?$/i';
        if (preg_match($directVideoRegex, $trimmed, $directMatches) === 1) {
            $trimStart = isset($directMatches[2]) && $directMatches[2] !== '' ? (float)$directMatches[2] : null;
            $trimEnd = isset($directMatches[3]) && $directMatches[3] !== '' ? (float)$directMatches[3] : null;
            return [
                'type' => 'direct',
                'videoId' => null,
                'thumbnailUrl' => null,
                'originalUrl' => $trimmed,
                'trimStart' => $trimStart,
                'trimEnd' => $trimEnd,
            ];
        }

        return null;
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
}
