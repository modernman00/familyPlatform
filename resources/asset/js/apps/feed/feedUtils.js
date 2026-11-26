/**
 * Pure utility helpers for the Profile Feed Preact Island.
 */
import {
    formatVideoDescriptor,
    extractVideosFromText,
    cleanPostMessage,
    parseVideoUrl,
} from '../../components/profilePage/videoParser';
import {
    getPostImageUrl,
    getPostImageVariantUrl,
    isGifMedia,
} from '../../components/helper/imageUrls';

export { getPostImageUrl, getPostImageVariantUrl, isGifMedia };

export const TEXT_STAGE_CATALOG = [
    {
        slug: 'starlight_bokeh',
        label: 'Starlight',
        badge: null,
        category: 'scenic',
        themeClass: 'starlight_bokeh',
        swatch: 'radial-gradient(circle at 30% 30%, #38bdf8 0%, #0369a1 45%, #020817 100%)',
    },
    {
        slug: 'golden_ember',
        label: 'Fireflies',
        badge: null,
        category: 'scenic',
        themeClass: 'golden_ember',
        swatch: 'radial-gradient(circle at 30% 30%, #fbbf24 0%, #b45309 48%, #140802 100%)',
    },
    {
        slug: 'sunset_horizon',
        label: 'Sunset',
        badge: null,
        category: 'scenic',
        themeClass: 'sunset_horizon',
        swatch: 'linear-gradient(135deg, #312e81 0%, #be185d 52%, #c2410c 100%)',
    },
    {
        slug: 'emerald_canopy',
        label: 'Forest',
        badge: null,
        category: 'scenic',
        themeClass: 'emerald_canopy',
        swatch: 'radial-gradient(circle at 32% 28%, #34d399 0%, #047857 48%, #021810 100%)',
    },
    {
        slug: 'ocean_biolum',
        label: 'Deep Ocean',
        badge: null,
        category: 'scenic',
        themeClass: 'ocean_biolum',
        swatch: 'radial-gradient(circle at 32% 30%, #22d3ee 0%, #0e7490 48%, #020c1b 100%)',
    },
    {
        slug: 'rose_stardust',
        label: 'Rose Velvet',
        badge: null,
        category: 'scenic',
        themeClass: 'rose_stardust',
        swatch: 'radial-gradient(circle at 30% 28%, #f472b6 0%, #9d174d 48%, #230514 100%)',
    },
    {
        slug: 'northern_sky',
        label: 'Aurora Sky',
        badge: null,
        category: 'scenic',
        themeClass: 'northern_sky',
        swatch: 'linear-gradient(135deg, #020617 0%, #059669 50%, #6366f1 100%)',
    },
    {
        slug: 'royal_parchment',
        label: 'Royal Noir',
        badge: null,
        category: 'scenic',
        themeClass: 'royal_parchment',
        swatch: 'linear-gradient(135deg, #09090b 0%, #292524 60%, #d97706 100%)',
    },
    {
        slug: 'xmas_snowfall',
        label: 'Snowfall',
        badge: null,
        category: 'scenic',
        themeClass: 'xmas_snowfall',
        swatch: 'radial-gradient(circle at 30% 28%, #e0f2fe 0%, #0284c7 45%, #06283d 100%)',
    },
    {
        slug: 'xmas_hearth',
        label: 'Cosy Hearth',
        badge: null,
        category: 'scenic',
        themeClass: 'xmas_hearth',
        swatch: 'radial-gradient(circle at 30% 30%, #fbbf24 0%, #b91c1c 48%, #3b0712 100%)',
    },
    {
        slug: 'xmas_lights',
        label: 'Fairy Lights',
        badge: null,
        category: 'scenic',
        themeClass: 'xmas_lights',
        swatch: 'linear-gradient(135deg, #064e3b 0%, #b91c1c 50%, #f59e0b 100%)',
    },
    {
        slug: 'xmas_winter_gold',
        label: 'Winter Gold',
        badge: null,
        category: 'scenic',
        themeClass: 'xmas_winter_gold',
        swatch: 'radial-gradient(circle at 30% 28%, #fef08a 0%, #ca8a04 45%, #052e16 100%)',
    },
    {
        slug: 'solar_galaxy',
        label: 'Solar Galaxy',
        badge: null,
        category: 'scenic',
        themeClass: 'solar_galaxy',
        swatch: 'radial-gradient(circle at 35% 35%, #fde047 0%, #ea580c 26%, #4338ca 62%, #02030d 100%)',
    },
    {
        slug: 'andromeda_nebula',
        label: 'Andromeda',
        badge: null,
        category: 'scenic',
        themeClass: 'andromeda_nebula',
        swatch: 'radial-gradient(circle at 35% 35%, #f0abfc 0%, #9333ea 35%, #1e1b4b 72%, #050214 100%)',
    },
    {
        slug: 'binary_eclipse',
        label: 'Binary Eclipse',
        badge: null,
        category: 'scenic',
        themeClass: 'binary_eclipse',
        swatch: 'radial-gradient(circle at 30% 35%, #fde047 0%, #f97316 28%, #0284c7 58%, #030712 100%)',
    },
    {
        slug: 'supernova_crown',
        label: 'Supernova',
        badge: null,
        category: 'scenic',
        themeClass: 'supernova_crown',
        swatch: 'radial-gradient(circle at 38% 35%, #fef08a 0%, #10b981 32%, #0f172a 68%, #020617 100%)',
    },
    {
        slug: 'cosmic_rings',
        label: 'Cosmic Rings',
        badge: null,
        category: 'scenic',
        themeClass: 'cosmic_rings',
        swatch: 'radial-gradient(circle at 35% 35%, #fed7aa 0%, #e11d48 34%, #312e81 68%, #030312 100%)',
    },
    {
        slug: 'ocean_waves',
        label: 'Ocean Waves',
        badge: null,
        category: 'scenic',
        themeClass: 'ocean_waves',
        swatch: 'linear-gradient(180deg, #0c4a6e 0%, #0284c7 45%, #0369a1 72%, #082f49 100%)',
    },
    {
        slug: 'moving_sunset',
        label: 'Golden Sunset',
        badge: null,
        category: 'scenic',
        themeClass: 'moving_sunset',
        swatch: 'linear-gradient(180deg, #311042 0%, #be185d 45%, #f97316 78%, #1e1b4b 100%)',
    },
    {
        slug: 'family_gathering',
        label: 'Family Fireside',
        badge: null,
        category: 'scenic',
        themeClass: 'family_gathering',
        swatch: 'radial-gradient(circle at 50% 72%, #fbbf24 0%, #b45309 36%, #451a03 72%, #1c0a00 100%)',
    },
    {
        slug: 'grand_announcement',
        label: 'Grand',
        badge: '📣 FAMILY ANNOUNCEMENT',
        category: 'stage',
        themeClass: 'grand_announcement',
        swatch: 'linear-gradient(135deg, #b45309 0%, #f59e0b 100%)',
    },
    {
        slug: 'aurora_prism',
        label: 'Prism',
        badge: '✨ MILESTONE MOMENT',
        category: 'stage',
        themeClass: 'aurora_prism',
        swatch: 'linear-gradient(135deg, #7c3aed 0%, #ec4899 100%)',
    },
    {
        slug: 'heirloom_velvet',
        label: 'Heirloom',
        badge: '📜 HERITAGE CHRONICLE',
        category: 'stage',
        themeClass: 'heirloom_velvet',
        swatch: 'linear-gradient(135deg, #4c0519 0%, #9f1239 100%)',
    },
    {
        slug: 'dynasty_holo',
        label: 'Dynasty',
        badge: '🏰 OFFICIAL SANCTUARY',
        category: 'stage',
        themeClass: 'dynasty_holo',
        swatch: 'linear-gradient(135deg, #065f46 0%, #10b981 100%)',
    },
    {
        slug: 'cyber_glass',
        label: 'Roundtable',
        badge: '💬 FAMILY ROUNDTABLE',
        category: 'stage',
        themeClass: 'cyber_glass',
        swatch: 'linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%)',
    },
    {
        slug: 'alert_pulse',
        label: 'Governance',
        badge: '🗳️ FAMILY GOVERNANCE',
        category: 'stage',
        themeClass: 'alert_pulse',
        swatch: 'linear-gradient(135deg, #991b1b 0%, #e11d48 100%)',
    },
];

const STAGE_KEYWORD_RULES = [
    {
        slug: 'xmas_snowfall',
        reason: 'Winter & Snow',
        pattern: /\b(snow|snowfall|snowman|frost|winter|blizzard)\b/i,
    },
    {
        slug: 'xmas_lights',
        reason: 'Festive Lights',
        pattern: /\b(fairy lights?|xmas lights?|christmas lights?|christmas tree|xmas tree)\b/i,
    },
    {
        slug: 'xmas_winter_gold',
        reason: 'Festive Gold',
        pattern: /\b(new year|champagne|winter gold|countdown)\b/i,
    },
    {
        slug: 'xmas_hearth',
        reason: 'Christmas',
        pattern: /\b(xmas|christmas|santa|festive|reindeer|mistletoe|stocking|yuletide|jingle)\b/i,
    },
    {
        slug: 'aurora_prism',
        reason: 'Party & Celebration',
        pattern: /\b(party|parties|birthday|bday|celebrat(?:e|ing|ion)|cheers|disco|bash|fiesta|toast)\b/i,
    },
    {
        slug: 'rose_stardust',
        reason: 'Love & Anniversary',
        pattern: /\b(anniversary|wedding|valentine|romance|love you|forever)\b/i,
    },
    {
        slug: 'ocean_waves',
        reason: 'Ocean & Coast',
        pattern: /\b(ocean|waves?|sea|beach|surf|tide|coast|island|seaside)\b/i,
    },
    {
        slug: 'moving_sunset',
        reason: 'Sunset & Dusk',
        pattern: /\b(sunset|sunrise|golden hour|dusk|twilight|horizon)\b/i,
    },
    {
        slug: 'family_gathering',
        reason: 'Family Gathering',
        pattern: /\b(fireside|dinner|sunday roast|talking together|catching up|family time|gathering|chatting|storytime)\b/i,
    },
    {
        slug: 'andromeda_nebula',
        reason: 'Nebula',
        pattern: /\b(andromeda|nebula|pulsar|stardust)\b/i,
    },
    {
        slug: 'binary_eclipse',
        reason: 'Eclipse',
        pattern: /\b(eclipse|binary star|twin suns?)\b/i,
    },
    {
        slug: 'supernova_crown',
        reason: 'Supernova',
        pattern: /\b(supernova|comet|meteor|shooting star)\b/i,
    },
    {
        slug: 'cosmic_rings',
        reason: 'Cosmic Rings',
        pattern: /\b(saturn|rings of|celestial)\b/i,
    },
    {
        slug: 'solar_galaxy',
        reason: 'Universe & Space',
        pattern: /\b(solar|galaxy|galaxies|planet|planets|cosmos|universe|space|astronomy|stargazing)\b/i,
    },
    {
        slug: 'emerald_canopy',
        reason: 'Nature & Forest',
        pattern: /\b(forest|woods|nature|garden|picnic|hike|hiking|springtime)\b/i,
    },
    {
        slug: 'northern_sky',
        reason: 'Aurora Sky',
        pattern: /\b(aurora|northern lights|night sky|midnight)\b/i,
    },
    {
        slug: 'dynasty_holo',
        reason: 'Family Heritage',
        pattern: /\b(family tree|lineage|ancestry|heritage|dynasty|generations)\b/i,
    },
    {
        slug: 'alert_pulse',
        reason: 'Important Alert',
        pattern: /\b(urgent|emergency|important notice|alert)\b/i,
    },
    {
        slug: 'grand_announcement',
        reason: 'Announcement',
        pattern: /\b(announce(?:ment)?|congratulations|congrats|welcome|reunion|milestone)\b/i,
    },
];

/**
 * Suggest a matching scenic/3D backdrop theme for a text-only post based on keywords.
 * Returns null if no keyword matches or text is too short.
 */
export function suggestTextStageForMessage(text) {
    if (!text || typeof text !== 'string') return null;
    const trimmed = text.trim();
    if (trimmed.length < 4) return null;
    for (const rule of STAGE_KEYWORD_RULES) {
        if (rule.pattern.test(trimmed)) {
            const meta = TEXT_STAGE_CATALOG.find((item) => item.slug === rule.slug);
            if (meta) {
                return { ...meta, reason: rule.reason };
            }
        }
    }
    return null;
}

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
    let d;
    if (typeof dateInput === 'number' || /^\d+$/.test(String(dateInput).trim())) {
        const rawNum = Number(dateInput);
        // Normalise 10-digit Unix timestamps (seconds) to milliseconds to avoid 1970 dates
        const ms = rawNum > 0 && rawNum < 1e12 ? rawNum * 1000 : rawNum;
        d = new Date(ms);
    } else {
        d = new Date(String(dateInput).replace(' ', 'T'));
    }
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
    if (parseVideoUrl(clean) !== null) return true;
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
    if (typeof vDesc.type === 'string' && vDesc.type !== '' && vDesc.type !== 'direct') return false;
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

    const isNonImageShareUrl = (item) => typeof item === 'string'
        && /^https?:\/\/(?:www\.|m\.|web\.)?facebook\.com\/share\/[a-z]+\/[^\s"'<>]+/i.test(item.trim());

    const images = rawImageSlots
        .filter((item) => !isVideoPath(item) && !isNonImageShareUrl(item))
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
        const isIg = Boolean(
            lp.is_instagram
            || String(lp.domain || lp.url || '').toLowerCase().includes('instagram.com')
            || String(lp.domain || lp.url || '').toLowerCase().includes('instagr.am')
        );
        const isFb = Boolean(
            lp.is_facebook
            || String(lp.domain || lp.url || '').toLowerCase().includes('facebook.com')
            || String(lp.domain || lp.url || '').toLowerCase().includes('fb.watch')
            || String(lp.domain || lp.url || '').toLowerCase().includes('fb.com')
        );
        const lpRatio = typeof lp.aspect_ratio === 'string' && lp.aspect_ratio.trim()
            ? lp.aspect_ratio.trim()
            : ((isIg || isFb) ? '9 / 16' : null);
        if (proxiedUrl && isVideoPath(proxiedUrl)) {
            pushVideoDesc({
                type: 'direct',
                provider: isIg ? 'instagram' : (isFb ? 'facebook' : 'direct'),
                videoId: lp.ig_shortcode
                    ? `ig_${lp.ig_shortcode}`
                    : (lp.fb_video_id ? `fb_${lp.fb_video_id}` : undefined),
                originalUrl: lp.url || rawVidUrl,
                embedUrl: proxiedUrl,
                thumbnailUrl: lp.image || lp.photo_url || null,
                posterUrl: lp.image || lp.photo_url || null,
                aspect_ratio: lpRatio || undefined,
                isShort: Boolean(isIg || isFb || (lpRatio && /^9\s*\/\s*16/.test(lpRatio))),
                sourceUrl: lp.url || null,
                sourceTitle: lp.title || null,
                sourceDescription: lp.description || null,
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

    let allowSharing = true;
    if (raw.allow_sharing !== undefined && raw.allow_sharing !== null) {
        allowSharing = !(raw.allow_sharing === false || raw.allow_sharing === 0 || raw.allow_sharing === '0' || raw.allow_sharing === 'false' || raw.allow_sharing === 'off');
    } else if (raw.media_meta) {
        try {
            const parsedMeta = typeof raw.media_meta === 'string' ? JSON.parse(raw.media_meta) : raw.media_meta;
            if (parsedMeta && parsedMeta.allow_sharing !== undefined) {
                allowSharing = !(parsedMeta.allow_sharing === false || parsedMeta.allow_sharing === 0 || parsedMeta.allow_sharing === '0' || parsedMeta.allow_sharing === 'false' || parsedMeta.allow_sharing === 'off');
            }
        } catch {
            // default true
        }
    }

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
        allow_sharing: allowSharing,
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
 * Extracts previewable HTTP/HTTPS URLs from post text, normalising scheme-less
 * domains (e.g. www.idecideapp.com or idecideapp.com) with https://.
 */
export function extractPreviewableUrls(text, max = 6) {
    if (!text || typeof text !== 'string') return [];
    const pattern = /(https?:\/\/[^\s<>"'`]+)|(?:^|[\s(\[{"'])(www\.[a-z0-9-]+(?:\.[a-z0-9-]+)+(?:\/[^\s<>"'`]*)?)|(?:^|[\s(\[{"'])([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9-]{1,63})*\.(?:com|org|net|io|app|dev|co\.uk|org\.uk|uk|edu|gov|ai|me|info|biz|tv|co)(?:\/[^\s<>"'`]*)?)(?![a-z0-9@._-])/gi;
    const urls = [];
    const seen = new Set();
    let match;
    while ((match = pattern.exec(text)) !== null) {
        const raw = match[1] ? match[1] : `https://${match[2] || match[3] || ''}`;
        const cleaned = raw.replace(/:[a-z0-9_]+_3d:.*$/i, '').replace(/[.,;:!?)]+$/, '');
        if (cleaned && cleaned !== 'https://' && !seen.has(cleaned)) {
            seen.add(cleaned);
            urls.push(cleaned);
            if (urls.length >= max) break;
        }
    }
    return urls;
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
    const regex = /(:me_(?:hello|laugh|wow|love|nod|dance|point|clap)@[A-Za-z0-9_-]{1,64}:|:[a-z0-9_]+_3d:|\[[^\]\n]+\]\((?:https?:\/\/|\/)[^\s)]+\)|https?:\/\/[^\s<>"']+|(?<![@/\w.-])www\.[a-z0-9-]+(?:\.[a-z0-9-]+)+(?:\/[^\s<>"'`]*)?|(?<![@/\w.-])[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9-]{1,63})*\.(?:com|org|net|io|app|dev|co\.uk|org\.uk|uk|edu|gov|ai|me|info|biz|tv|co)(?:\/[^\s<>"'`]*)?(?![a-z0-9@._-])|(?:^|\s)\/[A-Za-z0-9_/?=&#.-]+|#[A-Za-z0-9_]+|@[A-Za-z\u00C0-\u024F0-9_.'-]+(?:[ \t]+[A-Za-z\u00C0-\u024F0-9_.'-]+)?)/gi;
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
        } else if (/^(?:www\.[a-z0-9-]+(?:\.[a-z0-9-]+)+|[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9-]{1,63})*\.(?:com|org|net|io|app|dev|co\.uk|org\.uk|uk|edu|gov|ai|me|info|biz|tv|co))(?:\/[^\s<>"'`]*)?$/i.test(token)) {
            tokens.push({ type: 'url', value: `https://${token}`, label: token });
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

export const ARCADE_GAME_PROFILES = {
    kinzip: {
        slug: 'kinzip',
        name: 'KinZip',
        tag: 'Connect the Dots',
        icon: 'bi-bezier2',
        accent: '#818cf8',
        accentDeep: '#4338ca',
        glow: 'rgba(129, 140, 248, 0.34)',
    },
    kingrid: {
        slug: 'kingrid',
        name: 'KinGrid',
        tag: 'Number Puzzle',
        icon: 'bi-grid-3x3-gap-fill',
        accent: '#38bdf8',
        accentDeep: '#0284c7',
        glow: 'rgba(56, 189, 248, 0.34)',
    },
    kinword: {
        slug: 'kinword',
        name: 'KinWord',
        tag: 'Word Groups',
        icon: 'bi-collection-fill',
        accent: '#fbbf24',
        accentDeep: '#b45309',
        glow: 'rgba(251, 191, 36, 0.30)',
    },
    kinpulse: {
        slug: 'kinpulse',
        name: 'KinPulse',
        tag: 'Memory Pattern',
        icon: 'bi-soundwave',
        accent: '#f472b6',
        accentDeep: '#be185d',
        glow: 'rgba(244, 114, 182, 0.34)',
    },
    wordie: {
        slug: 'wordie',
        name: 'Wordie',
        tag: '5-Letter Word',
        icon: 'bi-alphabet-uppercase',
        accent: '#34d399',
        accentDeep: '#059669',
        glow: 'rgba(52, 211, 153, 0.32)',
    },
    cheeseboard: {
        slug: 'cheeseboard',
        name: 'Cheeseboard',
        tag: 'Queens Territory',
        icon: 'bi-award-fill',
        accent: '#c084fc',
        accentDeep: '#7e22ce',
        glow: 'rgba(192, 132, 252, 0.34)',
    },
    chess: {
        slug: 'chess',
        name: 'Family Chess',
        tag: 'Classic 8x8 Strategy',
        icon: 'bi-shield-shaded',
        accent: '#fbbf24',
        accentDeep: '#4338ca',
        glow: 'rgba(251, 191, 36, 0.30)',
    },
    ayo: {
        slug: 'ayo',
        name: 'Ayò Olópon',
        tag: 'Heritage Strategy',
        icon: 'bi-gem',
        accent: '#f59e0b',
        accentDeep: '#92400e',
        glow: 'rgba(245, 158, 11, 0.32)',
    },
    minesweeper: {
        slug: 'minesweeper',
        name: 'Minesweeper',
        tag: 'Safe Path Logic',
        icon: 'bi-bullseye',
        accent: '#f87171',
        accentDeep: '#b91c1c',
        glow: 'rgba(248, 113, 113, 0.32)',
    },
};

const ARCADE_GAME_MATCHERS = [
    { key: 'kingrid', re: /\bkingrid\b/i },
    { key: 'kinword', re: /\bkinword\b/i },
    { key: 'kinpulse', re: /\bkinpulse\b/i },
    { key: 'wordie', re: /\bwordie\b/i },
    { key: 'cheeseboard', re: /\bcheeseboard(?:\s*\(queens\))?/i },
    { key: 'chess', re: /\bfamily\s+chess\b/i },
    { key: 'ayo', re: /ay[oò]\s+ol[oó]pon/i },
    { key: 'minesweeper', re: /\bminesweeper\b/i },
    { key: 'kinzip', re: /\bkinzip\b/i },
];

/**
 * Extracts structured Family Arcade Challenge metadata from a feed post.
 * Recognises both standard share-to-feed posts and pasted /arcade/share/<token> links.
 */
export function getArcadeChallengeMeta(post) {
    const raw = decodeHtml(stripStyleToken(String(post?.postMessage || '')));
    if (!raw) return null;

    const urlMatch = raw.match(/(?:https?:\/\/[^\s<>"']+)?\/arcade\/share\/([a-f0-9]{16,64})/i);
    if (!urlMatch || !urlMatch[1]) return null;

    const token = urlMatch[1];
    const shareUrl = `/arcade/share/${encodeURIComponent(token)}`;
    const cardImageUrl = `/arcade/card/${encodeURIComponent(token)}.jpg?v=3`;

    let gameProfile = {
        slug: 'arcade',
        name: 'Family Arcade',
        tag: 'Family Challenge',
        icon: 'bi-controller',
        accent: '#38bdf8',
        accentDeep: '#1d4ed8',
        glow: 'rgba(56, 189, 248, 0.32)',
    };
    for (const matcher of ARCADE_GAME_MATCHERS) {
        if (matcher.re.test(raw)) {
            gameProfile = ARCADE_GAME_PROFILES[matcher.key];
            break;
        }
    }

    const scoreMatch = raw.match(/scored\s+([\d,]+)\s*pts?\b/i);
    const scoreNum = scoreMatch ? Number.parseInt(scoreMatch[1].replace(/,/g, ''), 10) : null;
    const scoreFormatted = Number.isFinite(scoreNum) ? scoreNum.toLocaleString('en-GB') : null;

    const timeMatch = raw.match(/\((\d+(?:\.\d+)?s)\)/i);
    const timeLabel = timeMatch ? timeMatch[1] : null;

    let promptText = 'Think you can beat my time? Tap below to race my score.';
    if (/think you can beat my time\?/i.test(raw)) {
        promptText = 'Think you can beat my time? Step up to the board.';
    }

    return {
        token,
        shareUrl,
        cardImageUrl,
        scoreFormatted,
        timeLabel,
        game: gameProfile,
        promptText,
    };
}
