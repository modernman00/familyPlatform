/**
 * Video URL Parser & Embed Helper for Social Posts
 * Supports:
 * - YouTube (Standard, Shortened youtu.be, Shorts, Live, Unlisted, with or without '=' in query)
 * - Vimeo
 * - Cloudflare Stream
 * - Direct MP4 / WebM video streams
 */

'use strict';

export function parseVideoUrl(url) {
  if (!url || typeof url !== 'string') return null;

  const trimmed = url.trim();

  // 1. YouTube Matcher
  // Matches: youtube.com/watch?v=ID, youtube.com/watch?vID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID, youtube.com/live/ID
  const ytRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
  const ytMatch = trimmed.match(ytRegex);
  if (ytMatch && ytMatch[1]) {
    return {
      type: 'youtube',
      videoId: ytMatch[1],
      embedUrl: `https://www.youtube-nocookie.com/embed/${ytMatch[1]}?rel=0&modestbranding=1&autoplay=0`,
      originalUrl: trimmed
    };
  }

  // 2. Vimeo Matcher
  // Matches: vimeo.com/ID, player.vimeo.com/video/ID
  const vimeoRegex = /(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
  const vimeoMatch = trimmed.match(vimeoRegex);
  if (vimeoMatch && (vimeoMatch[3] || vimeoMatch[2] || vimeoMatch[1])) {
    const vimeoId = vimeoMatch[3] || vimeoMatch[2] || vimeoMatch[1];
    return {
      type: 'vimeo',
      videoId: vimeoId,
      embedUrl: `https://player.vimeo.com/video/${vimeoId}?dnt=1&title=0&byline=0&portrait=0`,
      originalUrl: trimmed
    };
  }

  // 3. Cloudflare Stream Matcher
  // Matches: iframe.videodelivery.net/UID, watch.cloudflarestream.com/UID, customer-*.cloudflarestream.com/UID
  const cfStreamRegex = /(?:https?:\/\/)?(?:iframe\.videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)\/([a-zA-Z0-9]{32})/;
  const cfMatch = trimmed.match(cfStreamRegex);
  if (cfMatch && cfMatch[1]) {
    return {
      type: 'cloudflare',
      videoId: cfMatch[1],
      embedUrl: `https://iframe.videodelivery.net/${cfMatch[1]}`,
      originalUrl: trimmed
    };
  }

  // 4. Direct HTML5 Video (.mp4, .webm, .ogg)
  const directVideoRegex = /^https?:\/\/.+\.(mp4|webm|ogg)(\?.*)?$/i;
  if (directVideoRegex.test(trimmed)) {
    return {
      type: 'direct',
      videoId: null,
      embedUrl: trimmed,
      originalUrl: trimmed
    };
  }

  return null;
}

/**
 * Extract video URL from post message text if present
 */
export function extractVideoFromText(text) {
  if (!text || typeof text !== 'string') return null;

  const urlRegex = /(https?:\/\/[^\s]+)/g;
  const matches = text.match(urlRegex);
  if (!matches) return null;

  for (const url of matches) {
    const parsed = parseVideoUrl(url);
    if (parsed) return parsed;
  }

  return null;
}

/**
 * Strip raw video URL from post text if a player is rendered,
 * leaving only user-written caption/comment text.
 */
export function cleanPostMessage(text, video) {
  if (!text || typeof text !== 'string') return '';
  if (!video || !video.originalUrl) return text;

  const cleaned = text.replace(video.originalUrl, '').trim();
  return cleaned;
}
