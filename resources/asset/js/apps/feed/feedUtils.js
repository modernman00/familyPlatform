/**
 * Pure utility helpers for the Profile Feed Preact Island.
 */
import {
    formatVideoDescriptor,
    extractVideosFromText,
    cleanPostMessage,
} from '../../components/profilePage/videoParser';
import {
    getPostImageUrl,
    getPostImageVariantUrl,
    isGifMedia,
} from '../../components/helper/imageUrls';

export { getPostImageUrl, getPostImageVariantUrl, isGifMedia };

export const TEXT_STAGE_CATALOG = [
    { slug: 'grand_announcement', label: 'Grand', badge: '📣 FAMILY ANNOUNCEMENT', themeClass: 'grand_announcement' },
    { slug: 'aurora_prism', label: 'Aurora', badge: '✨ MILESTONE MOMENT', themeClass: 'aurora_prism' },
    { slug: 'heirloom_velvet', label: 'Heirloom', badge: '📜 HERITAGE CHRONICLE', themeClass: 'heirloom_velvet' },
    { slug: 'dynasty_holo', label: 'Dynasty', badge: '🏰 OFFICIAL SANCTUARY', themeClass: 'dynasty_holo' },
    { slug: 'cyber_glass', label: 'Roundtable', badge: '💬 FAMILY ROUNDTABLE', themeClass: 'cyber_glass' },
    { slug: 'alert_pulse', label: 'Governance', badge: '🗳️ FAMILY GOVERNANCE', themeClass: 'alert_pulse' },
];

export function decodeHtml(str) {
    if (!str || typeof str !== 'string') return '';
    let decoded = str;
    for (let i = 0; i < 3 && /&amp;(?:#\d+|#x[0-9a-f]+|[a-z]+);/i.test(decoded); i += 1) {
        decoded = decoded.replace(/&amp;(#\d+|#x[0-9a-f]+|[a-z]+);/gi, '&$1;');
    }
    return decoded
        .replace(/&(?:#?0*39|#x0*27|apos);/gi, "'")
        .replace(/&(?:#?0*34|#x0*22|quot);/gi, '"')
        .replace(/&(?:#?0*60|#x0*3c|lt);/gi, '<')
        .replace(/&(?:#?0*62|#x0*3e|gt);/gi, '>')
        .replace(/&(?:#?0*38|#x0*26|amp);/gi, '&');
}

export function normalizeEmojiText(str) {
    if (!str || typeof str !== 'string') return '';
    return str
        .replace(/\u2764(?!\uFE0F)/g, '\u2764\uFE0F')
        .replace(/\u2665(?!\uFE0F)/g, '\u2764\uFE0F')
        .replace(/(^|\s)<3(?=\s|$|[.,!?])/g, '$1❤️');
}

export function stripStyleToken(text) {
    if (!text || typeof text !== 'string') return '';
    return text.replace(/\[style:[a-z0-9_]+\]\s*/gi, '').trim();
}

export function extractPostStyle(rawText, explicitStyle = null, postObj = null) {
    const allowed = new Set(TEXT_STAGE_CATALOG.map((s) => s.slug));
    if (explicitStyle && typeof explicitStyle === 'string') {
        const clean = explicitStyle.trim().toLowerCase();
        if (clean === 'none') return null;
        if (allowed.has(clean)) return clean;
    }
    if (!rawText || typeof rawText !== 'string') return null;
    const match = rawText.match(/\[style:([a-z0-9_]+)\]/i);
    if (match && match[1]) {
        const candidate = match[1].toLowerCase();
        if (candidate === 'none') return null;
        if (allowed.has(candidate)) return candidate;
    }

    const lower = rawText.toLowerCase();
    if (lower.includes('joined our family circle') || lower.includes('warm welcome to the family')) return 'grand_announcement';
    if (lower.includes('officially turned 18') || lower.includes('happy 18th birthday') || lower.includes('milestone celebration:')) return 'aurora_prism';
    if (lower.includes('family chronicle') || lower.includes('/familyjournal/') || lower.includes('[memory flashback') || lower.includes('on this day flashback')) return 'heirloom_velvet';
    if (lower.includes('vote to change our family code') || lower.includes('wants to change our family code')) return 'alert_pulse';
    if (lower.includes('welcome to your page') || postObj?.is_official || postObj?.id === 'FAMILY_PLATFORM_OFFICIAL') return 'dynasty_holo';
    if (lower.includes('family roundtable') || lower.includes('roundtable accord')) return 'cyber_glass';
    return null;
}

export function getPostStageMeta(post) {
    const slug = post?.post_style || extractPostStyle(post?.postMessage, post?.post_style, post);
    if (!slug) return null;
    const base = TEXT_STAGE_CATALOG.find((s) => s.slug === slug);
    if (!base) return null;

    const raw = String(post?.postMessage || '').toLowerCase();
    let customBadge = base.badge;
    if (raw.includes('joined our family circle') || raw.includes('warm welcome to the family')) {
        customBadge = '🎉 FAMILY WELCOME MILESTONE';
    } else if (raw.includes('officially turned 18') || raw.includes('happy 18th birthday')) {
        customBadge = '🎂 18TH BIRTHDAY GRADUATION';
    } else if (raw.includes('family chronicle') || raw.includes('/familyjournal/')) {
        customBadge = '📖 FAMILY CHRONICLE DECREE';
    } else if (raw.includes('[memory flashback')) {
        customBadge = '⏳ ON THIS DAY FLASHBACK';
    } else if (raw.includes('change our family code')) {
        customBadge = '🗳️ FAMILY CODE GOVERNANCE';
    } else if (raw.includes('welcome to your page')) {
        customBadge = '🏡 SANCTUARY WELCOME';
    } else if (post?.is_official || post?.id === 'FAMILY_PLATFORM_OFFICIAL') {
        customBadge = '✨ OFFICIAL PLATFORM BROADCAST';
    }
    return { ...base, badge: customBadge };
}

export function hasPollArena(post) {
    return Boolean(post?.poll && Array.isArray(post.poll.options) && post.poll.options.length > 0);
}

export function getPollArenaMeta(post) {
    const lower = String(post?.postMessage || '').toLowerCase();
    if (lower.includes('weekly family trivia')) {
        return {
            theme: 'trivia_gold',
            badge: '🏆 WEEKLY FAMILY TRIVIA ARENA',
            subtitle: 'Sunday Family Ritual • Cast your vote below & debate in the comments!',
        };
    }
    if (lower.includes('family game show')) {
        return {
            theme: 'gameshow_neon',
            badge: '🎮 FAMILY GAME SHOW ARENA',
            subtitle: 'Test how well you know our family stories & see who gets top marks!',
        };
    }
    if (lower.includes('weekend outings')) {
        return {
            theme: 'outings_emerald',
            badge: '🗺️ FAMILY WEEKEND OUTINGS VOTE',
            subtitle: 'Scouted live activities for our family • Tap your pick for this weekend!',
        };
    }
    if (lower.includes('change our family code')) {
        return {
            theme: 'alert_vote',
            badge: '🗳️ FAMILY CODE GOVERNANCE VOTE',
            subtitle: 'Cast your vote — the code updates automatically if Yes wins.',
        };
    }
    return {
        theme: 'cyber_poll',
        badge: '📊 FAMILY POLL',
        subtitle: 'Cast your vote to see live family results.',
    };
}

export function getPollCleanIntro(post) {
    const raw = stripStyleToken(post?.displayMessage || post?.postMessage || '');
    if (!raw) return '';
    const q = String(post?.poll?.question || '').trim();
    let cleaned = raw;
    if (q) cleaned = cleaned.split(q).join('').trim();
    cleaned = cleaned
        .replace(/🗳️?\s*cast your vote below!?/gi, '')
        .replace(/\n{3,}/g, '\n\n')
        .trim();
    return cleaned;
}

export function getAvatarUrl(entity) {
    const raw = entity?.profileImg || entity?.profilePics || entity?.avatar || entity?.img || entity?.user_avatar || '';
    if (!raw || typeof raw !== 'string') return '/public/avatar/avatarM.png';
    if (raw.startsWith('http://') || raw.startsWith('https://') || raw.startsWith('/')) {
        return raw;
    }
    return `/resources/images/profile/${raw}`;
}

export function getInitials(firstName = '', lastName = '') {
    const f = String(firstName || '').trim().charAt(0);
    const l = String(lastName || '').trim().charAt(0);
    return (f + l).toUpperCase() || 'FM';
}

export function formatRelativeTime(dateInput) {
    if (!dateInput) return 'Just now';
    const d = typeof dateInput === 'number' || /^\d+$/.test(String(dateInput).trim())
        ? new Date(Number(dateInput))
        : new Date(String(dateInput).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return String(dateInput);
    const diffSec = Math.floor((Date.now() - d.getTime()) / 1000);
    if (diffSec < 45) return 'Just now';
    if (diffSec < 3600) return `${Math.floor(diffSec / 60)}m ago`;
    if (diffSec < 86400) return `${Math.floor(diffSec / 3600)}h ago`;
    if (diffSec < 604800) return `${Math.floor(diffSec / 86400)}d ago`;
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

export function normalizeMediaUrl(item) {
    if (!item || typeof item !== 'string') return '';
    const trimmed = item.trim();
    if (!trimmed) return '';
    if (trimmed.startsWith('blob:')) return trimmed;
    return getPostImageUrl(trimmed) || trimmed;
}

export function isVideoPath(url) {
    if (!url || typeof url !== 'string') return false;
    const clean = url.trim();
    if (/\.(mp4|webm|mov|m4v|ogg)(\?|#|$)/i.test(clean)) return true;
    if (clean.includes('/resources/videos/') || clean.includes('/external-stream')) return true;
    if (clean.includes('googleusercontent.com') && /=(m18|m22|m37|dv)/i.test(clean)) return true;
    if (clean.includes('googlevideo.com')) return true;
    return false;
}

export function normalizeComparison(c) {
    if (!c || typeof c !== 'object' || !c.before_img || !c.after_img) return null;
    const beforeLabel = c.before_label || 'Then';
    const afterLabel = c.after_label || 'Now';
    const mode = c.comparison_mode === 'echo' ? 'echo' : 'classic';
    const m1 = String(beforeLabel).match(/\b(19\d{2}|20\d{2})\b/);
    const m2 = String(afterLabel).match(/\b(19\d{2}|20\d{2})\b/);
    const y1 = m1 ? parseInt(m1[1], 10) : null;
    const y2 = m2 ? parseInt(m2[1], 10) : null;
    const yearsDelta = (typeof c.years_delta === 'number' && !Number.isNaN(c.years_delta))
        ? c.years_delta
        : ((y1 !== null && y2 !== null) ? Math.abs(y2 - y1) : null);

    let deltaBadge = c.delta_badge || null;
    if (!deltaBadge) {
        if (mode === 'echo') {
            deltaBadge = '🧬 Like Parent, Like Child';
        } else if (yearsDelta !== null && yearsDelta > 0) {
            deltaBadge = yearsDelta === 1 ? '⏳ 1 Year Apart' : `⏳ ${yearsDelta} Years Apart`;
        }
    }

    return {
        ...c,
        before_img: String(c.before_img),
        after_img: String(c.after_img),
        before_label: beforeLabel,
        after_label: afterLabel,
        comparison_mode: mode,
        years_delta: yearsDelta,
        delta_badge: deltaBadge,
    };
}

export function isDirectVideoDescriptor(vDesc) {
    if (!vDesc) return false;
    if (typeof vDesc === 'string') return isVideoPath(vDesc);
    if (vDesc.renderMode === 'native_video') return true;
    if (vDesc.renderMode === 'inline_iframe') return false;
    if (vDesc.type === 'direct' || Boolean(vDesc.isDirect)) return true;
    const url = vDesc.videoSrc || vDesc.embedUrl || vDesc.originalUrl || '';
    return isVideoPath(url);
}

export function resolveVideoPlaybackSrc(vDesc) {
    if (!vDesc) return '';
    if (typeof vDesc === 'string') return vDesc;
    return String(vDesc.videoSrc || vDesc.embedUrl || vDesc.embedSrc || vDesc.originalUrl || '');
}

const VALID_RENDER_MODES = new Set(['image', 'native_video', 'inline_iframe', 'external_card']);

export function normalizeMediaItem(item, fallbackId = 'media-0') {
    if (!item || typeof item !== 'object') return null;
    const renderMode = VALID_RENDER_MODES.has(item.renderMode) ? item.renderMode : 'external_card';
    return {
        id: String(item.id || fallbackId),
        provider: String(item.provider || 'external'),
        renderMode,
        aspectRatio: typeof item.aspectRatio === 'string' && item.aspectRatio.trim() ? item.aspectRatio.trim() : null,
        posterUrl: typeof item.posterUrl === 'string' && item.posterUrl.trim() ? item.posterUrl.trim() : null,
        videoSrc: typeof item.videoSrc === 'string' && item.videoSrc.trim() ? item.videoSrc.trim() : null,
        hlsUrl: typeof item.hlsUrl === 'string' && item.hlsUrl.trim() ? item.hlsUrl.trim() : null,
        embedSrc: typeof item.embedSrc === 'string' && item.embedSrc.trim() ? item.embedSrc.trim() : null,
        originalUrl: typeof item.originalUrl === 'string' && item.originalUrl.trim() ? item.originalUrl.trim() : null,
        title: typeof item.title === 'string' && item.title.trim() ? item.title.trim() : null,
        durationSec: typeof item.durationSec === 'number' && Number.isFinite(item.durationSec) ? item.durationSec : null,
        focalX: typeof item.focalX === 'number' && Number.isFinite(item.focalX) ? item.focalX : null,
        focalY: typeof item.focalY === 'number' && Number.isFinite(item.focalY) ? item.focalY : null,
        isShort: Boolean(item.isShort),
    };
}

export function buildNormalizedMedia(raw, media = { images: [], videos: [] }, linkPreview = null) {
    if (Array.isArray(raw?.normalized_media) && raw.normalized_media.length > 0) {
        return raw.normalized_media
            .map((item, idx) => normalizeMediaItem(item, `post-${raw?.post_no ?? '0'}-media-${idx}`))
            .filter(Boolean);
    }

    const postKey = String(raw?.post_no ?? '0');
    const items = [];

    (media.images || []).forEach((imgUrl, idx) => {
        if (!imgUrl) return;
        items.push({
            id: `post-${postKey}-img-${idx}`,
            provider: 'image',
            renderMode: 'image',
            aspectRatio: null,
            posterUrl: imgUrl,
            videoSrc: null,
            hlsUrl: null,
            embedSrc: null,
            originalUrl: imgUrl,
            title: null,
            durationSec: null,
            focalX: null,
            focalY: null,
            isShort: false,
        });
    });

    (media.videos || []).forEach((vid, idx) => {
        if (!vid || typeof vid !== 'object') return;
        const provider = String(vid.type || 'direct');
        const isNative = isDirectVideoDescriptor(vid);
        const embedUrl = typeof vid.embedUrl === 'string' ? vid.embedUrl : null;
        const origUrl = typeof vid.originalUrl === 'string' ? vid.originalUrl : null;
        const renderMode = isNative
            ? 'native_video'
            : (embedUrl ? 'inline_iframe' : 'external_card');

        items.push({
            id: String(vid.videoId || `post-${postKey}-vid-${idx}`),
            provider,
            renderMode,
            aspectRatio: typeof vid.aspect_ratio === 'string' ? vid.aspect_ratio : null,
            posterUrl: typeof vid.thumbnailUrl === 'string' ? vid.thumbnailUrl : null,
            videoSrc: isNative ? (embedUrl || origUrl) : null,
            hlsUrl: typeof vid.hlsUrl === 'string' ? vid.hlsUrl : null,
            embedSrc: renderMode === 'inline_iframe' ? embedUrl : null,
            originalUrl: origUrl || embedUrl,
            title: typeof vid.hook === 'string' ? vid.hook : null,
            durationSec: Number.isFinite(Number(vid.duration_sec)) ? Number(vid.duration_sec) : null,
            focalX: typeof vid.focal_x === 'number' ? vid.focal_x : null,
            focalY: typeof vid.focal_y === 'number' ? vid.focal_y : null,
            isShort: Boolean(vid.isShort),
        });
    });

    if (items.length === 0 && linkPreview && (linkPreview.url || linkPreview.image || linkPreview.title)) {
        const frameBlocked = Boolean(linkPreview.frame_blocked);
        const lpEmbed = !frameBlocked && typeof linkPreview.embed_url === 'string' && linkPreview.embed_url.trim()
            ? linkPreview.embed_url.trim()
            : null;
        items.push({
            id: `post-${postKey}-lp-0`,
            provider: String(linkPreview.site_name || linkPreview.domain || 'external').toLowerCase(),
            renderMode: lpEmbed ? 'inline_iframe' : 'external_card',
            aspectRatio: typeof linkPreview.aspect_ratio === 'string' ? linkPreview.aspect_ratio : null,
            posterUrl: typeof linkPreview.image === 'string' ? linkPreview.image : null,
            videoSrc: null,
            hlsUrl: null,
            embedSrc: lpEmbed,
            originalUrl: typeof linkPreview.url === 'string' ? linkPreview.url : null,
            title: typeof linkPreview.title === 'string' ? linkPreview.title : null,
            durationSec: null,
            focalX: null,
            focalY: null,
            isShort: false,
        });
    }

    return items;
}

export function extractRawMedia(raw, cleanMsg = '') {
    const rawImageSlots = [];
    const pushImgCandidate = (val) => {
        if (!val || typeof val !== 'string') return;
        const trimmed = val.trim();
        if (!trimmed || trimmed === 'null' || trimmed === 'undefined' || trimmed === 'none' || trimmed.includes('no_image')) return;
        if (!rawImageSlots.includes(trimmed)) rawImageSlots.push(trimmed);
    };

    if (Array.isArray(raw?.images)) {
        raw.images.forEach(pushImgCandidate);
    }
    if (raw?.post_img) pushImgCandidate(raw.post_img);
    for (let i = 0; i <= 5; i += 1) {
        const slot = raw?.[`post_img${i}`];
        if (slot) pushImgCandidate(slot);
    }

    const images = rawImageSlots
        .filter((item) => !isVideoPath(item))
        .map((item) => normalizeMediaUrl(item))
        .filter(Boolean);

    const videos = [];
    const normalizeVideoDedupKey = (u) => {
        if (!u || typeof u !== 'string') return '';
        const trimmed = u.trim();
        if (trimmed.includes('googleusercontent.com')) {
            return trimmed.replace(/=[^/?#]+$/i, '');
        }
        return trimmed;
    };

    const pushVideoDesc = (candidate) => {
        if (!candidate) return;
        let desc = formatVideoDescriptor(candidate);
        if (!desc) return;

        if (typeof candidate === 'object') {
            const rawEmbed = typeof candidate.embedUrl === 'string' ? candidate.embedUrl : '';
            const rawOrig = typeof candidate.originalUrl === 'string' ? candidate.originalUrl : '';
            const derivedThumb = !candidate.thumbnailUrl && !desc.thumbnailUrl && rawOrig.includes('googleusercontent.com')
                ? rawOrig.replace(/=.*$/i, '=s1600-no')
                : null;
            desc = {
                ...desc,
                ...candidate,
                type: candidate.type || desc.type || 'direct',
                embedUrl: rawEmbed.startsWith('/resources/videos/') ? rawEmbed : (desc.embedUrl || rawEmbed || rawOrig),
                originalUrl: rawOrig || desc.originalUrl || rawEmbed,
                thumbnailUrl: candidate.thumbnailUrl || desc.thumbnailUrl || derivedThumb,
            };
        } else if (typeof desc === 'object' && !desc.thumbnailUrl && typeof desc.originalUrl === 'string' && desc.originalUrl.includes('googleusercontent.com')) {
            desc = {
                ...desc,
                thumbnailUrl: desc.originalUrl.replace(/=.*$/i, '=s1600-no'),
            };
        }

        const descOrigKey = normalizeVideoDedupKey(desc.originalUrl);
        const descEmbedKey = normalizeVideoDedupKey(desc.embedUrl);
        const existingIdx = videos.findIndex((ev) => {
            const evOrigKey = normalizeVideoDedupKey(ev.originalUrl);
            const evEmbedKey = normalizeVideoDedupKey(ev.embedUrl);
            return (
                (desc.videoId && ev.videoId && ev.videoId === desc.videoId) ||
                (descOrigKey && evOrigKey && descOrigKey === evOrigKey) ||
                (descEmbedKey && evEmbedKey && descEmbedKey === evEmbedKey) ||
                (descOrigKey && evEmbedKey && descOrigKey === evEmbedKey) ||
                (descEmbedKey && evOrigKey && descEmbedKey === evOrigKey)
            );
        });

        if (existingIdx !== -1) {
            const existing = videos[existingIdx];
            const incomingIsProxied = typeof desc.embedUrl === 'string' && desc.embedUrl.startsWith('/resources/videos/');
            const existingIsProxied = typeof existing.embedUrl === 'string' && existing.embedUrl.startsWith('/resources/videos/');
            if ((incomingIsProxied && !existingIsProxied) || (!existing.thumbnailUrl && desc.thumbnailUrl)) {
                videos[existingIdx] = {
                    ...existing,
                    ...desc,
                    embedUrl: incomingIsProxied ? desc.embedUrl : existing.embedUrl,
                    thumbnailUrl: existing.thumbnailUrl || desc.thumbnailUrl || null,
                };
            }
            return;
        }

        if (videos.length < 6) {
            videos.push(desc);
        }
    };

    if (Array.isArray(raw?.videos)) {
        raw.videos.forEach((v) => {
            if (v) pushVideoDesc(v);
        });
    }
    if (raw?.video) {
        pushVideoDesc(raw.video);
    }

    const lp = raw?.link_preview;
    if (lp && Array.isArray(lp.album_videos) && lp.album_videos.length > 0) {
        lp.album_videos.forEach((vUrl, vIdx) => {
            if (!vUrl || videos.length >= 6) return;
            const proxiedUrl = Array.isArray(lp.proxied_album_videos) && lp.proxied_album_videos[vIdx]
                ? lp.proxied_album_videos[vIdx]
                : vUrl;
            pushVideoDesc({
                type: 'direct',
                originalUrl: vUrl,
                embedUrl: proxiedUrl,
                thumbnailUrl: String(vUrl).includes('googleusercontent.com')
                    ? String(vUrl).replace(/=.*$/i, '=s1600-no')
                    : null,
            });
        });
    } else if (lp && lp.is_video && (lp.video_url || lp.url)) {
        const rawVidUrl = lp.video_url || lp.url;
        const proxiedUrl = lp.proxied_video_url || '';
        if (proxiedUrl && isVideoPath(proxiedUrl)) {
            pushVideoDesc({
                type: 'direct',
                originalUrl: rawVidUrl,
                embedUrl: proxiedUrl,
                thumbnailUrl: lp.image || lp.photo_url || null,
            });
        } else {
            const parsed = formatVideoDescriptor(rawVidUrl);
            if (parsed) {
                pushVideoDesc({
                    ...parsed,
                    thumbnailUrl: parsed.thumbnailUrl || lp.image || lp.photo_url || null,
                });
            }
        }
    }

    rawImageSlots.filter((item) => isVideoPath(item)).forEach((vUrl) => pushVideoDesc(vUrl));
    const hasSocialPreview = Boolean(lp && lp.is_social_video && !lp.is_video && !Array.isArray(lp.album_videos));
    if (cleanMsg && !hasSocialPreview) {
        const textVideos = extractVideosFromText(cleanMsg, 6);
        if (Array.isArray(textVideos)) {
            textVideos.forEach((tv) => pushVideoDesc(tv));
        }
    }

    // Linked photo hydration when there are no videos
    if (images.length === 0 && videos.length === 0) {
        if (lp && Array.isArray(lp.album_images) && lp.album_images.length > 0) {
            lp.album_images.forEach((img) => {
                if (img && !images.includes(img) && images.length < 6) images.push(img);
            });
        } else if (
            lp &&
            (lp.is_photo_link || lp.is_google_photo) &&
            !lp.is_video &&
            (!Array.isArray(lp.album_videos) || lp.album_videos.length === 0)
        ) {
            const photoAsset = lp.photo_url || lp.image;
            if (photoAsset) images.push(photoAsset);
        }
        const directImgRegex = /https?:\/\/[^\s<>"']+\.(?:jpe?g|png|webp|avif|gif)(?:\?[^\s<>"']*)?/gi;
        const directMatches = cleanMsg.match(directImgRegex);
        if (directMatches) {
            directMatches.forEach((m) => {
                if (!images.includes(m) && images.length < 6) images.push(m);
            });
        }
    }

    return { images, videos };
}

export function extractPostMedia(post) {
    return extractRawMedia(post, post?.postMessage || '');
}

export function getMediaCaption(post) {
    if (!post) return '';
    let text = (post.displayMessage !== undefined && post.displayMessage !== null)
        ? String(post.displayMessage)
        : String(post.postMessage || '');

    if (!text.trim()) return '';
    text = stripStyleToken(text);
    text = text.replace(/👉\s*Read the full story:\s*(?:https?:\/\/[^\s<>"']+)?\/familyJournal\/\d+/gi, '');
    text = text.replace(/https?:\/\/(?:photos\.app\.goo\.gl|photos\.google\.com)\/[^\s<>"']+/gi, '');
    text = text.replace(/https?:\/\/(?:www\.)?(?:icloud\.com\/photos|i\.imgur\.com|imgur\.com|ibb\.co|postimg\.cc)\/[^\s<>"']+/gi, '');
    text = text.replace(/https?:\/\/[^\s<>"']+\.(?:jpe?g|png|webp|avif|gif)(?:\?[^\s<>"']*)?/gi, '');
    text = cleanPostMessage(text, post.videos || post.video || []);

    if (post.link_preview && (post.link_preview.image || post.link_preview.title || post.link_preview.url)) {
        const stripUrl = (u) => {
            if (!u || typeof u !== 'string') return;
            text = text.split(u).join('');
            const trimmed = u.replace(/\/+$/, '');
            if (trimmed && trimmed !== u) text = text.split(trimmed).join('');
            const withSlash = `${trimmed || u}/`;
            if (withSlash !== u) text = text.split(withSlash).join('');
        };
        stripUrl(post.link_preview.original_url);
        stripUrl(post.link_preview.url);
    }

    return normalizeEmojiText(text.trim());
}

export function normalizeComment(raw) {
    if (!raw || typeof raw !== 'object') return null;
    const first = String(raw.firstName ?? raw.first_name ?? '').trim();
    const last = String(raw.lastName ?? raw.last_name ?? '').trim();
    const fullName = String(raw.fullName || `${first} ${last}`.trim() || 'Family Member').trim();
    const parts = fullName.split(/\s+/);
    const profileImg = getAvatarUrl(raw);

    let normalizedReactions = [];
    if (Array.isArray(raw.reactions)) {
        normalizedReactions = raw.reactions;
    } else if (raw.reactions && typeof raw.reactions === 'object') {
        const countsObj = raw.reactions.counts || raw.reactions;
        normalizedReactions = Object.entries(countsObj)
            .filter(([k, v]) => k !== 'totalReactions' && Number(v) > 0)
            .map(([label, count]) => ({ label, count: Number(count) }));
    }

    return {
        ...raw,
        comment_no: String(raw.comment_no ?? raw.id ?? `tmp_${Date.now()}`),
        post_no: String(raw.post_no ?? ''),
        id: String(raw.id ?? raw.user_id ?? ''),
        firstName: first || parts[0] || 'Family',
        lastName: last || parts.slice(1).join(' ') || '',
        fullName,
        profileImg,
        comment: normalizeEmojiText(decodeHtml(String(raw.comment ?? ''))),
        gif_url: raw.gif_url || '',
        parent_comment_no:
            raw.parent_comment_no !== null
            && raw.parent_comment_no !== undefined
            && String(raw.parent_comment_no).trim() !== ''
            && String(raw.parent_comment_no).trim() !== '0'
                ? String(raw.parent_comment_no).trim()
                : null,
        reactions: normalizedReactions,
        user_reaction: raw.user_reaction ?? raw.userReaction ?? null,
        date_created: raw.date_created ?? raw.created_at ?? '',
        post_time: raw.post_time ?? raw.comment_time ?? raw.date_created ?? '',
    };
}

export function normalizePost(raw) {
    if (!raw || typeof raw !== 'object') return null;
    const cleanMsg = decodeHtml(String(raw.postMessage ?? ''));
    const media = extractRawMedia(raw, cleanMsg);
    const comments = Array.isArray(raw.comments)
        ? raw.comments.map(normalizeComment).filter(Boolean)
        : [];

    const first = String(raw.firstName ?? raw.first_name ?? '').trim();
    const last = String(raw.lastName ?? raw.last_name ?? '').trim();
    const fullName = String(raw.fullName || `${first} ${last}`.trim() || 'Family Member').trim();
    const nameParts = fullName.split(/\s+/);
    const profileImg = getAvatarUrl(raw);
    const postTime = raw.post_time || raw.date_created || raw.created_at || '';

    const normalized = {
        ...raw,
        post_no: String(raw.post_no ?? ''),
        id: String(raw.id ?? raw.user_id ?? ''),
        firstName: first || nameParts[0] || 'Family',
        lastName: last || nameParts.slice(1).join(' ') || '',
        fullName,
        profileImg,
        postMessage: cleanMsg,
        post_style: extractPostStyle(cleanMsg, raw.post_style, raw),
        post_likes: Number(raw.post_likes ?? 0),
        user_reaction: raw.user_reaction ?? null,
        images: media.images,
        videos: media.videos,
        video: media.videos[0] || null,
        normalized_media: buildNormalizedMedia(raw, media, raw.link_preview || null),
        comparison: normalizeComparison(raw.comparison),
        comments,
        post_tags: Array.isArray(raw.post_tags) ? raw.post_tags : [],
        views_count: Number(raw.views_count ?? raw.view_count ?? 0),
        view_count: Number(raw.views_count ?? raw.view_count ?? 0),
        date_created: raw.date_created || postTime,
        post_time: postTime,
        is_official: Boolean(raw.is_official || raw.id === 'FAMILY_PLATFORM_OFFICIAL'),
        is_historian: Boolean(raw.is_historian || raw.id === 'FAMILY_HISTORIAN'),
    };

    normalized.displayMessage = getMediaCaption({
        ...normalized,
        displayMessage: raw.displayMessage ? decodeHtml(String(raw.displayMessage)) : undefined,
    });

    return normalized;
}

/**
 * Unwraps nested PHP msgSuccess() / controller responses across:
 *  - PostMessage::index: { status: 'success', message: { message: [...], pagination: {...} } }
 *  - HashtagController::stream: { status: 'success', message: { status: 'success', data: { items: [...] } } }
 *  - Flat payloads: { message: [...], pagination: {...} } or { data: { items: [...] } } or { posts: [...] }
 */
export function unwrapFeedResponse(res) {
    if (!res || typeof res !== 'object') {
        return { items: [], pagination: {} };
    }
    const inner = (res.message && typeof res.message === 'object' && !Array.isArray(res.message))
        ? res.message
        : res;

    let items = [];
    if (Array.isArray(inner.message)) {
        items = inner.message;
    } else if (Array.isArray(inner.data?.items)) {
        items = inner.data.items;
    } else if (Array.isArray(res.data?.items)) {
        items = res.data.items;
    } else if (Array.isArray(inner.posts)) {
        items = inner.posts;
    } else if (Array.isArray(res.message)) {
        items = res.message;
    } else if (Array.isArray(res.posts)) {
        items = res.posts;
    }

    const pagination = inner.pagination || res.pagination || {};
    return { items, pagination };
}

export function isOwnPost(post, currentUserId) {
    if (!post) return false;
    if (post.is_own_post === true) return true;
    if (!currentUserId) return false;
    return String(post.id) === String(currentUserId);
}

export function canEditComment(comment, currentUserId) {
    if (!comment || !currentUserId) return false;
    return String(comment.id) === String(currentUserId);
}

export function canModerateComment(post, comment, currentUserId) {
    if (!currentUserId) return false;
    return String(comment?.id) === String(currentUserId) || String(post?.id) === String(currentUserId);
}

/**
 * Checks whether a text string consists exclusively of living 3D emojis and/or Me-stickers.
 */
export function isEmojiOnlyMessage(text, arEmoji = null) {
    let msg = stripStyleToken(text || '');
    if (arEmoji && /^[a-z0-9_]+_3d$/i.test(arEmoji) && !msg.includes(`:${arEmoji}:`)) {
        msg = (msg ? `${msg} ` : '') + `:${arEmoji}:`;
    }
    if (!msg) return false;
    const stickerRe = /:(?:[a-z0-9_]+_3d|me_[a-z]+@[A-Za-z0-9_-]{1,64}):/gi;
    return stickerRe.test(msg) && msg.replace(stickerRe, '').trim() === '';
}

/**
 * Tokenizes post/comment text into plain text, markdown/relative links, #hashtags, @mentions,
 * 3D living family emojis (:id_3d:), Me-stickers (:me_reaction@uid:), and URLs for safe JSX rendering.
 */
export function tokenizeRichText(text, arEmoji = null) {
    let clean = stripStyleToken(text || '');
    if (arEmoji && /^[a-z0-9_]+_3d$/i.test(arEmoji) && !clean.includes(`:${arEmoji}:`)) {
        clean = (clean ? `${clean} ` : '') + `:${arEmoji}:`;
    }
    if (!clean) return [];
    const regex = /(:me_(?:hello|laugh|wow|love|nod|dance|point|clap)@[A-Za-z0-9_-]{1,64}:|:[a-z0-9_]+_3d:|\[[^\]\n]+\]\((?:https?:\/\/|\/)[^\s)]+\)|https?:\/\/[^\s<>"']+|(?:^|\s)\/[A-Za-z0-9_/?=&#.-]+|#[A-Za-z0-9_]+|@[A-Za-z0-9_.-]+(?:\s+[A-Za-z0-9_.-]+)?)/gi;
    const tokens = [];
    let lastIndex = 0;
    let match;
    while ((match = regex.exec(clean)) !== null) {
        if (match.index > lastIndex) {
            tokens.push({ type: 'text', value: clean.slice(lastIndex, match.index) });
        }
        const token = match[0];
        const meMatch = token.match(/^:me_(hello|laugh|wow|love|nod|dance|point|clap)@([A-Za-z0-9_-]{1,64}):$/i);
        const livingMatch = token.match(/^:([a-z0-9_]+_3d):$/i);
        if (meMatch) {
            tokens.push({
                type: 'me_sticker',
                reaction: meMatch[1].toLowerCase(),
                uid: meMatch[2],
                value: token,
            });
        } else if (livingMatch) {
            tokens.push({
                type: 'living_emoji',
                emojiId: livingMatch[1].toLowerCase(),
                value: token,
            });
        } else if (token.startsWith('[')) {
            const md = token.match(/^\[([^\]\n]+)\]\(((?:https?:\/\/|\/)[^\s)]+)\)$/);
            if (md) {
                tokens.push({ type: 'link', label: md[1], value: md[2] });
            } else {
                tokens.push({ type: 'text', value: token });
            }
        } else if (token.startsWith('http://') || token.startsWith('https://')) {
            tokens.push({ type: 'url', value: token });
        } else if (/^\s*\//.test(token)) {
            const leadingSpace = token.match(/^\s*/)[0];
            const path = token.slice(leadingSpace.length);
            if (leadingSpace) tokens.push({ type: 'text', value: leadingSpace });
            tokens.push({ type: 'link', label: path, value: path });
        } else if (token.startsWith('#')) {
            tokens.push({ type: 'hashtag', value: token, tag: token.slice(1) });
        } else if (token.startsWith('@')) {
            tokens.push({ type: 'mention', value: token });
        }
        lastIndex = regex.lastIndex;
    }
    if (lastIndex < clean.length) {
        tokens.push({ type: 'text', value: clean.slice(lastIndex) });
    }
    return tokens;
}
