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

export function normaliseDropboxStreamUrl(url) {
  if (!url || typeof url !== 'string') return url;
  const trimmed = url.trim();
  const dropboxRegex = /^https?:\/\/(?:www\.|dl\.)?dropbox(?:usercontent)?\.com\/(?:s|scl\/fi)\/[^\s"'<>?#]+\.(?:mp4|webm|mov|ogg|m4v)(?:\?[^\s"'<>#]*)?$/i;
  if (!dropboxRegex.test(trimmed)) return trimmed;
  try {
    const dbUrl = new URL(trimmed);
    dbUrl.searchParams.delete('dl');
    dbUrl.searchParams.set('raw', '1');
    return dbUrl.toString();
  } catch (_) {
    return trimmed;
  }
}

export function parseVideoUrl(url) {
  if (!url || typeof url !== 'string') return null;

  const trimmed = url.trim();
  if (!trimmed) return null;

  // 0. Already-proxied sovereign stream URL (/resources/videos/external-stream?url=... or /member/stream-video?url=...)
  if (trimmed.startsWith('/resources/videos/external-stream?url=') || trimmed.startsWith('/member/stream-video?url=')) {
    const rawQuery = trimmed.split('?url=')[1] || '';
    const rawParam = rawQuery.split('&')[0] || '';
    let decoded = rawParam;
    try {
      decoded = decodeURIComponent(rawParam);
    } catch (_) {}
    const poster = decoded.includes('googleusercontent.com')
      ? decoded.replace(/=.*$/i, '=s1600-no')
      : null;
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: poster,
      embedUrl: trimmed.startsWith('/resources/videos/external-stream') ? trimmed : (decoded || trimmed),
      originalUrl: decoded || trimmed,
      trimStart: null,
      trimEnd: null,
    };
  }

  // 1. YouTube Matcher
  // Matches: youtube.com/watch?v=ID, youtube.com/watch?vID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID, youtube.com/live/ID
  const ytRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
  const ytMatch = trimmed.match(ytRegex);
  if (ytMatch && ytMatch[1]) {
    return {
      type: 'youtube',
      videoId: ytMatch[1],
      thumbnailUrl: `https://i.ytimg.com/vi/${ytMatch[1]}/hqdefault.jpg`,
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
      thumbnailUrl: null,
      embedUrl: `https://player.vimeo.com/video/${vimeoId}?dnt=1&title=0&byline=0&portrait=0`,
      originalUrl: trimmed
    };
  }

  // 3. Cloudflare Stream Matcher
  // Matches: iframe.videodelivery.net/UID, videodelivery.net/UID, watch.cloudflarestream.com/UID, customer-*.cloudflarestream.com/UID
  const cfStreamRegex = /(?:https?:\/\/)?(?:iframe\.videodelivery\.net|videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)\/([a-zA-Z0-9]{32})/;
  const cfMatch = trimmed.match(cfStreamRegex);
  if (cfMatch && cfMatch[1]) {
    return {
      type: 'cloudflare',
      videoId: cfMatch[1],
      thumbnailUrl: `https://videodelivery.net/${cfMatch[1]}/thumbnails/thumbnail.jpg`,
      embedUrl: `https://iframe.videodelivery.net/${cfMatch[1]}`,
      hlsUrl: `https://videodelivery.net/${cfMatch[1]}/manifest/video.m3u8`,
      originalUrl: trimmed
    };
  }

  // 3b. TikTok (tiktok.com/@user/video/ID or tiktok.com/embed/v2/ID) — embeddable player
  const tiktokMatch = trimmed.match(/^https?:\/\/(?:www\.)?tiktok\.com\/(?:@[\w.-]+\/video\/|embed\/v2\/)(\d{8,25})/i);
  if (tiktokMatch) {
    return {
      type: 'tiktok',
      videoId: tiktokMatch[1],
      thumbnailUrl: null,
      embedUrl: `https://www.tiktok.com/embed/v2/${tiktokMatch[1]}`,
      originalUrl: trimmed
    };
  }

  // 3c. Dropbox shared video files — force raw streaming (dl=0 returns an HTML page, raw=1 returns the file)
  const dropboxRegex = /^https?:\/\/(?:www\.|dl\.)?dropbox(?:usercontent)?\.com\/(?:s|scl\/fi)\/[^\s"'<>?#]+\.(?:mp4|webm|mov|ogg|m4v)(?:\?[^\s"'<>#]*)?$/i;
  if (dropboxRegex.test(trimmed)) {
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: normaliseDropboxStreamUrl(trimmed),
      originalUrl: trimmed,
      trimStart: null,
      trimEnd: null,
    };
  }

  // 4. Direct HTML5 Video (.mp4, .webm, .ogg, .mov, .m4v, .m3u8) — supports absolute http(s), sovereign /resources/videos/ or /public/uploads/, and bare filenames
  const directVideoRegex = /^(?:https?:\/\/[^\s"'<>]+|\/(?:resources\/(?:videos|images)|public\/uploads)\/[^\s"'<>]+|[a-zA-Z0-9_-]+\.(?:mp4|webm|ogg|mov|m4v|m3u8))(?:\.(mp4|webm|ogg|mov|m4v|m3u8))?(?:\?[^\s"'<>#]*)?(?:#t=([0-9.]+)(?:,([0-9.]+))?)?$/i;
  if (/\.(mp4|webm|ogg|mov|m4v|m3u8)(?:\?[^\s"'<>#]*)?(?:#t=[0-9.]+(?:,[0-9.]+)?)?$/i.test(trimmed)) {
    const directMatch = trimmed.match(directVideoRegex);
    const isHls = /\.m3u8(?:\?|#|$)/i.test(trimmed);
    const trimStart = directMatch && directMatch[2] !== undefined ? parseFloat(directMatch[2]) : null;
    const trimEnd = directMatch && directMatch[3] !== undefined ? parseFloat(directMatch[3]) : null;
    const resolvedEmbed = (trimmed.startsWith('/') || trimmed.startsWith('http'))
      ? trimmed
      : `/resources/videos/posts/${trimmed}`;
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: resolvedEmbed,
      hlsUrl: isHls ? resolvedEmbed : null,
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

  // 6. Facebook Video / Reel / Watch (in-platform iframe via official Facebook video plugin)
  // Note: /share/r/ and /share/v/ URLs are excluded so they render as Rich Link Previews rather than "Video unavailable"
  const fbVideoRegex = /^https?:\/\/(?:www\.|m\.|web\.)?(?:facebook\.com\/(?:[^\/\s]+\/videos\/\d+|watch\/?\?v=\d+|reel\/\d+|video\.php\?v=\d+)|fb\.watch\/[^\s"'<>]+|fb\.com\/(?:watch|reel|[^\/\s]+\/videos)\/[^\s"'<>]+)/i;
  if (fbVideoRegex.test(trimmed)) {
    const isFbReel = /(?:facebook\.com|fb\.com)\/reel\/\d+/i.test(trimmed);
    return {
      type: 'facebook',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(trimmed)}&show_text=false&autoplay=0`,
      originalUrl: trimmed,
      ...(isFbReel ? { isShort: true, aspect_ratio: '9 / 16' } : {}),
    };
  }

  // 7. Google Drive Video (drive.google.com/file/d/{id}/... or drive.google.com/open?id={id})
  const gdMatch = trimmed.match(/^https?:\/\/drive\.google\.com\/(?:file\/d\/([a-zA-Z0-9_-]{10,})|open\?id=([a-zA-Z0-9_-]{10,}))/i);
  if (gdMatch && (gdMatch[1] || gdMatch[2])) {
    const gdId = gdMatch[1] || gdMatch[2];
    return {
      type: 'googledrive',
      videoId: gdId,
      thumbnailUrl: null,
      embedUrl: `https://drive.google.com/file/d/${gdId}/preview`,
      originalUrl: trimmed,
    };
  }

  // 8. OneDrive Video / Embed (onedrive.live.com/embed, onedrive.live.com/redir, 1drv.ms/v/)
  if (/^https?:\/\/(?:onedrive\.live\.com\/(?:embed|redir|\?)|1drv\.ms\/v\/)[^\s"'<>]+/i.test(trimmed)) {
    const odEmbed = /^https?:\/\/onedrive\.live\.com\/(?:redir|view\.aspx)?\?(.+)$/i.test(trimmed)
      ? `https://onedrive.live.com/embed?${trimmed.replace(/^https?:\/\/onedrive\.live\.com\/(?:redir|view\.aspx)?\?/i, '')}`
      : trimmed;
    return {
      type: 'onedrive',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: odEmbed,
      originalUrl: trimmed,
    };
  }

  return null;
}

/**
 * Normalize a raw video URL or existing video descriptor object into a canonical video descriptor.
 */
export function formatVideoDescriptor(videoOrUrl) {
  if (!videoOrUrl) return null;
  if (typeof videoOrUrl === 'string') {
    const parsed = parseVideoUrl(videoOrUrl);
    if (parsed) return parsed;
    const resolved = (videoOrUrl.startsWith('/') || videoOrUrl.startsWith('http'))
      ? videoOrUrl
      : `/resources/videos/posts/${videoOrUrl}`;
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: resolved,
      originalUrl: videoOrUrl,
      trimStart: null,
      trimEnd: null,
    };
  }
  if (typeof videoOrUrl === 'object') {
    const candidateUrl = videoOrUrl.originalUrl || videoOrUrl.embedUrl || '';
    const parsed = parseVideoUrl(candidateUrl);
    const hasProxiedEmbed = typeof videoOrUrl.embedUrl === 'string' && videoOrUrl.embedUrl.startsWith('/resources/videos/');
    if (parsed) {
      return {
        ...parsed,
        embedUrl: hasProxiedEmbed
          ? videoOrUrl.embedUrl
          : parsed.embedUrl,
        thumbnailUrl: videoOrUrl.thumbnailUrl || parsed.thumbnailUrl || null,
        trimStart: videoOrUrl.trimStart ?? parsed.trimStart ?? null,
        trimEnd: videoOrUrl.trimEnd ?? parsed.trimEnd ?? null,
        hook: videoOrUrl.hook || null,
      };
    }
    return videoOrUrl;
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
export function extractVideosFromText(text, max = 6) {
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
