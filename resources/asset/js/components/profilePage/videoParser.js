/**
 * Video URL Parser & Embed Helper for Social Posts
 * Supports:
 * - YouTube (Standard, Shortened youtu.be, Shorts, Live, Unlisted, with or without '=' in query)
 * - Vimeo
 * - Cloudflare Stream
 * - TikTok (full video URLs)
 * - Dropbox shared video files
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

  // 3b. TikTok (tiktok.com/@user/video/ID) — embeddable player
  const tiktokMatch = trimmed.match(/^https?:\/\/(?:www\.)?tiktok\.com\/@[\w.-]+\/video\/(\d{8,25})/i);
  if (tiktokMatch) {
    return {
      type: 'tiktok',
      videoId: tiktokMatch[1],
      embedUrl: `https://www.tiktok.com/embed/v2/${tiktokMatch[1]}`,
      originalUrl: trimmed
    };
  }

  // 3c. Dropbox shared video files — force raw streaming (dl=0 returns an HTML page, raw=1 returns the file)
  const dropboxRegex = /^https?:\/\/(?:www\.)?dropbox\.com\/(?:s|scl\/fi)\/[^\s"'<>?#]+\.(?:mp4|webm|mov|ogg)(?:\?[^\s"'<>#]*)?$/i;
  if (dropboxRegex.test(trimmed)) {
    const dbUrl = new URL(trimmed);
    dbUrl.searchParams.delete('dl');
    dbUrl.searchParams.set('raw', '1');
    return {
      type: 'direct',
      videoId: null,
      embedUrl: dbUrl.toString(),
      originalUrl: trimmed,
      trimStart: null,
      trimEnd: null,
    };
  }

  // 4. Direct HTML5 Video (.mp4, .webm, .ogg, .mov) — supports absolute http(s) and sovereign /resources/videos/ paths with optional #t=start,end
  const directVideoRegex = /^(?:https?:\/\/[^\s"'<>]+|\/resources\/(?:videos|images)\/[^\s"'<>]+)\.(mp4|webm|ogg|mov)(?:\?[^\s"'<>#]*)?(?:#t=([0-9.]+)(?:,([0-9.]+))?)?$/i;
  const directMatch = trimmed.match(directVideoRegex);
  if (directMatch) {
    const trimStart = directMatch[2] !== undefined ? parseFloat(directMatch[2]) : null;
    const trimEnd = directMatch[3] !== undefined ? parseFloat(directMatch[3]) : null;
    return {
      type: 'direct',
      videoId: null,
      embedUrl: trimmed,
      originalUrl: trimmed,
      trimStart: Number.isFinite(trimStart) ? trimStart : null,
      trimEnd: Number.isFinite(trimEnd) ? trimEnd : null,
    };
  }

  // 5. Google Photos Video Stream (=m18, =m22, =m37, =dv) or Google Video Stream
  const gpVideoRegex = /^(https?:\/\/(?:lh[3-6]\.googleusercontent\.com|[^/]+\.googlevideo\.com)[^\s"'<>]+(?:=(?:m18|m22|m37|dv)|videoplayback[^\s"'<>]*))$/i;
  const gpMatch = trimmed.match(gpVideoRegex);
  if (gpMatch) {
    const poster = trimmed.includes('googleusercontent.com')
      ? trimmed.replace(/=.*$/i, '=s1600-no')
      : null;
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: poster,
      embedUrl: trimmed,
      originalUrl: trimmed,
      trimStart: null,
      trimEnd: null,
    };
  }

  return null;
}

/**
 * Extract first video URL from post message text if present (backwards-compatible)
 */
export function extractVideoFromText(text) {
  const vids = extractVideosFromText(text, 1);
  return vids.length > 0 ? vids[0] : null;
}

/**
 * Extract up to max video URLs from post message text
 */
export function extractVideosFromText(text, max = 3) {
  if (!text || typeof text !== 'string') return [];

  const urlRegex = /((?:https?:\/\/|\/resources\/(?:videos|images)\/)[^\s"'<>]+)/g;
  const matches = text.match(urlRegex);
  if (!matches) return [];

  const videos = [];
  const seenUrls = new Set();

  for (const url of matches) {
    if (videos.length >= max) break;
    if (seenUrls.has(url)) continue;
    const parsed = parseVideoUrl(url);
    if (parsed) {
      videos.push(parsed);
      seenUrls.add(url);
    }
  }

  return videos;
}

/**
 * Strip raw video URLs from post text if players are rendered,
 * leaving only user-written caption/comment text.
 */
export function cleanPostMessage(text, videoOrVideos) {
  if (!text || typeof text !== 'string') return '';
  let cleaned = text.replace(/\[style:[a-z0-9_]+\]\s*/gi, '');
  if (!videoOrVideos) return cleaned.trim();

  if (Array.isArray(videoOrVideos)) {
    videoOrVideos.forEach(v => {
      if (v?.originalUrl) {
        cleaned = cleaned.replace(v.originalUrl, '');
      }
    });
  } else if (videoOrVideos?.originalUrl) {
    cleaned = cleaned.replace(videoOrVideos.originalUrl, '');
  }

  return cleaned.trim();
}
