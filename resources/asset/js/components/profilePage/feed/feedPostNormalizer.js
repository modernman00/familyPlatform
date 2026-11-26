import { format } from "timeago.js";
import { parseVideoUrl, formatVideoDescriptor, extractVideoFromText, extractVideosFromText, cleanPostMessage } from "../videoParser";
import { getPostImageUrl, getProfileImageUrl, getPostImageVariantUrl, isGifMedia } from "../../helper/imageUrls";
import { isAllowedGifUrl } from "../gifCatalog";
import { triggerHeirloomBurst } from "../heirloomGlassReactions";

export const feedPostNormalizer = {
        normalizePost(p) {
            let profImg = '/public/avatar/avatarF.png';
            const rawImg = p?.img || p?.profileImg;
            if (rawImg) {
                profImg = (rawImg.startsWith('/') || rawImg.startsWith('http')) ? rawImg : `/resources/images/profile/${rawImg}`;
            }
            const cleanMsg = this.decodeHtml(p?.postMessage || '');
            let detectedVideos = (Array.isArray(p?.videos) && p.videos.length > 0)
                ? p.videos.map(v => formatVideoDescriptor(v, cleanMsg)).filter(Boolean)
                : [];
            const videoFilesFromSlots = this.extractVideosFromSlots(p);
            if (videoFilesFromSlots && videoFilesFromSlots.length > 0) {
                videoFilesFromSlots.forEach(videoFile => {
                    if (detectedVideos.length >= 6) return;
                    let slotUrl = null;
                    let matchThumb = null;
                    if (Array.isArray(p?.videos)) {
                        const match = p.videos.find(v => v && (v.originalUrl === videoFile || v.embedUrl === videoFile));
                        if (match && match.embedUrl) slotUrl = match.embedUrl;
                        if (match && match.thumbnailUrl) matchThumb = match.thumbnailUrl;
                    }
                    const desc = formatVideoDescriptor({
                        originalUrl: videoFile,
                        embedUrl: slotUrl || undefined,
                        thumbnailUrl: matchThumb || (videoFile.includes('googleusercontent.com') ? videoFile.replace(/=.*$/i, '=s1600-no') : null),
                    });
                    if (!desc) return;
                    const isDup = detectedVideos.some(ev =>
                        ev && (
                            (ev.videoId && desc.videoId && ev.videoId === desc.videoId) ||
                            ev.originalUrl === desc.originalUrl ||
                            ev.embedUrl === desc.embedUrl
                        )
                    );
                    if (!isDup) detectedVideos.push(desc);
                });
            }
            const textVideos = extractVideosFromText(cleanMsg, 6);
            if (textVideos && textVideos.length > 0) {
                textVideos.forEach(tv => {
                    if (detectedVideos.length >= 6 || !tv) return;
                    const isDup = detectedVideos.some(ev =>
                        ev && (
                            (ev.videoId && tv.videoId && ev.videoId === tv.videoId) ||
                            ev.originalUrl === tv.originalUrl ||
                            ev.embedUrl === tv.embedUrl
                        )
                    );
                    if (!isDup) detectedVideos.push(tv);
                });
            }
            const primaryVideo = detectedVideos.length > 0 ? detectedVideos[0] : null;
            const initialDisplay = p?.displayMessage 
                ? this.normalizeEmojiText(p.displayMessage) 
                : this.normalizeEmojiText(cleanPostMessage(cleanMsg, detectedVideos));

            const normalized = {
                post_no: p?.post_no,
                id: p?.id,
                fullName: p?.fullName || 'Family Member',
                profileImg: profImg,
                postFamCode: p?.postFamCode || '',
                date_created: p?.date_created || new Date().toISOString(),
                post_time: p?.post_time || p?.date_created || new Date().toISOString(),
                postMessage: cleanMsg,
                displayMessage: initialDisplay,
                video: primaryVideo,
                videos: detectedVideos,
                link_preview: p?.link_preview || null,
                post_likes: parseInt(p?.post_likes || 0, 10),
                images: this.extractImages(p),
                poll: p?.poll || null,
                two_truths: p?.two_truths || null,
                comparison: this.normalizeComparison(p?.comparison),
                reactions: Array.isArray(p?.reactions) ? p.reactions : [],
                user_reaction: p?.user_reaction || null,
                comments: Array.isArray(p?.comments) ? p.comments.map(c => this.normalizeComment(c)) : [],
                isLiked: false,
                post_tags: Array.isArray(p?.post_tags) ? p.post_tags : [],
                views_count: parseInt(p?.views_count || 0, 10),
                voice_capsule_url: p?.voice_capsule_url || null,
                is_official: Boolean(p?.is_official || p?.id === 'FAMILY_PLATFORM_OFFICIAL'),
                post_style: this.extractPostStyle(cleanMsg, p?.post_style, p),
                ar_emoji: p?.ar_emoji || (() => {
                    const m = cleanMsg.match(/:([a-z0-9_]+_3d):/i);
                    return m ? m[1] : null;
                })(),
                _isTranslated: false,
                _isTranslating: false,
                _translatedMessage: null,
                _origDisplayMessage: null,
                _detectedSource: '',
            };

            // Linked photo hydration (Google Photos, iCloud, direct image assets without local uploads)
            // Never promote OpenGraph preview image into post.images when the post is a video or multi-video album
            if (normalized.images.length === 0 && (!normalized.videos || normalized.videos.length === 0) && !normalized.video) {
                if (
                    normalized.link_preview
                    && (normalized.link_preview.is_photo_link || normalized.link_preview.is_google_photo)
                    && !normalized.link_preview.is_video
                    && (!normalized.link_preview.album_videos || normalized.link_preview.album_videos.length === 0)
                ) {
                    const photoAsset = normalized.link_preview.photo_url || normalized.link_preview.image;
                    if (photoAsset && !normalized.images.includes(photoAsset)) {
                        normalized.images.push(photoAsset);
                    }
                }
                const directImgRegex = /https?:\/\/[^\s<>"']+\.(?:jpe?g|png|webp|avif|gif)(?:\?[^\s<>"']*)?/gi;
                const directMatches = cleanMsg.match(directImgRegex);
                if (directMatches) {
                    directMatches.forEach(m => {
                        if (!normalized.images.includes(m) && normalized.images.length < 6) {
                            normalized.images.push(m);
                        }
                    });
                }
            }

            // Always guarantee displayMessage has all raw media links stripped
            normalized.displayMessage = this.getMediaCaption(normalized);

            // Progressive enhancement: lazy unfurl link preview(s) in parallel if server hasn't pre-hydrated all links
            if (normalized.postMessage) {
                const urlRe = /(https?:\/\/[^\s<>"'`]+)|(?:^|[\s(\[{"'])(www\.[a-z0-9-]+(?:\.[a-z0-9-]+)+(?:\/[^\s<>"'`]*)?)|(?:^|[\s(\[{"'])([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9-]{1,63})*\.(?:com|org|net|io|app|dev|co\.uk|org\.uk|uk|edu|gov|ai|me|info|biz|tv|co)(?:\/[^\s<>"'`]*)?)(?![a-z0-9@._-])/gi;
                const allUrlMatches = [];
                let urlM;
                while ((urlM = urlRe.exec(normalized.postMessage)) !== null) {
                    const rawU = urlM[1] ? urlM[1] : `https://${urlM[2] || urlM[3] || ''}`;
                    if (rawU && rawU !== 'https://') allUrlMatches.push(rawU);
                }
                const uniqueUrls = Array.from(new Set(allUrlMatches.map(u => u.replace(/[.,;!?)]+$/, '')))).filter(u => {
                    if (/\.(?:jpe?g|png|webp|avif|gif)(?:\?.*)?$/i.test(u)) return false;
                    if (parseVideoUrl(u)) return false;
                    return true;
                }).slice(0, 6);
                if (uniqueUrls.length > 1) {
                    this.lazyFetchAllLinkPreviews(normalized, uniqueUrls);
                } else if (uniqueUrls.length === 1 && !normalized.link_preview && !normalized.video) {
                    this.lazyFetchLinkPreview(normalized, uniqueUrls[0]);
                }
            }

            return normalized;
        },

        normalizeComment(c) {
            const img = c?.img || c?.profileImg;
            const timeVal = c?.post_time || c?.comment_time || c?.date_created || '';
            const rawComment = c?.comment || '';
            return {
                comment_no: c?.comment_no,
                post_no: c?.post_no,
                parent_comment_no: Number(c?.parent_comment_no) > 0 ? Number(c.parent_comment_no) : null,
                id: c?.id,
                fullName: c?.fullName || 'Family Member',
                profileImg: img ? ((img.startsWith('/') || img.startsWith('http')) ? img : `/resources/images/profile/${img}`) : '/public/avatar/avatarM.png',
                comment: this.normalizeEmojiText(this.decodeHtml(rawComment)),
                gif_url: isAllowedGifUrl(c?.gif_url) ? c.gif_url : '',
                date_created: c?.date_created || '',
                post_time: c?.post_time || '',
                comment_time: timeVal,
                reactions: c?.reactions?.counts ?? c?.reactions ?? {},
                totalReactions: c?.reactions?.counts?.totalReactions ?? c?.reactions?.totalReactions ?? 0,
                userReaction: (c?.user_reaction === 'likes' ? 'like' : (c?.user_reaction === 'shock' ? 'wow' : c?.user_reaction)) || null,
                views_count: parseInt(c?.views_count || 0, 10),
            };
        },

        normalizeComparison(c) {
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

            let deltaBadge = null;
            if (mode === 'echo') {
                deltaBadge = '🧬 Like Parent, Like Child';
            } else if (yearsDelta !== null && yearsDelta > 0) {
                deltaBadge = yearsDelta === 1 ? '⏳ 1 Year Apart' : `⏳ ${yearsDelta} Years Apart`;
            }

            const isMystery = Boolean(c.mystery_year) && y1 !== null;
            let mysteryOptions = [];
            if (isMystery && y1 !== null) {
                const nowYear = new Date().getFullYear();
                const decoyA = Math.max(1920, y1 - 4);
                const decoyB = Math.min(nowYear, y1 + 3 === y1 ? y1 - 2 : y1 + 3);
                const set = Array.from(new Set([decoyA, y1, decoyB]));
                while (set.length < 3) {
                    const extra = Math.max(1920, y1 - (set.length * 3));
                    if (!set.includes(extra)) set.push(extra);
                    else set.push(Math.min(nowYear, y1 + set.length));
                }
                mysteryOptions = set.sort((a, b) => a - b);
            }

            const rawAvatars = Array.isArray(c.chain_avatars) ? c.chain_avatars : [];
            const chainAvatars = rawAvatars.map(av => {
                const raw = av?.profileImg || '';
                const imgUrl = raw
                    ? ((raw.startsWith('/') || raw.startsWith('http')) ? raw : `/resources/images/profile/${raw}`)
                    : '/public/avatar/avatarM.png';
                return {
                    post_no: av?.post_no || null,
                    user_id: av?.user_id || '',
                    fullName: av?.fullName || 'Family Member',
                    profileImg: imgUrl,
                };
            });

            return {
                before_img: String(c.before_img),
                after_img: String(c.after_img),
                before_label: beforeLabel,
                after_label: afterLabel,
                parent_post_no: c.parent_post_no || null,
                answer_count: parseInt(c.answer_count || 0, 10) || 0,
                comparison_mode: mode,
                mystery_year: isMystery,
                true_year: y1,
                mystery_options: mysteryOptions,
                years_delta: yearsDelta,
                delta_badge: deltaBadge,
                chain_avatars: chainAvatars,
            };
        },

        celebrateThenNowGuess(event, isCorrect) {
            if (typeof window.triggerHaptic === 'function') {
                window.triggerHaptic(isCorrect ? 'impact' : 'selection');
            }
            if (isCorrect && event?.currentTarget) {
                try {
                    triggerHeirloomBurst(event.currentTarget, 'celebrate');
                } catch (_) {}
            }
        },

        isVideoAsset(url) {
            if (!url || typeof url !== 'string') return false;
            const clean = url.trim();
            if (/\.(mp4|webm|mov|ogg)(\?|#|$)/i.test(clean)) return true;
            if (clean.includes('googleusercontent.com') && /=(m18|m22|m37|dv)/i.test(clean)) return true;
            if (clean.includes('googlevideo.com')) return true;
            return false;
        },

        extractImages(p) {
            if (!p || typeof p !== 'object') return [];
            const collected = [];

            if (Array.isArray(p.images)) {
                p.images.forEach(img => {
                    if (img && typeof img === 'string') {
                        const trimmed = img.trim();
                        if (trimmed && !collected.includes(trimmed)) collected.push(trimmed);
                    }
                });
            }

            Object.keys(p).forEach(k => {
                if (k.startsWith('post_img') && p[k] !== null && p[k] !== '' && typeof p[k] === 'string') {
                    const val = p[k].trim();
                    if (val && !collected.includes(val)) {
                        collected.push(val);
                    }
                }
            });

            return collected.filter(img => img && img !== 'null' && img !== 'undefined' && img !== 'none' && !img.includes('no_image') && !this.isVideoAsset(img));
        },

        extractVideosFromSlots(p) {
            if (!p || typeof p !== 'object') return [];
            const collected = [];
            if (Array.isArray(p.videos)) {
                p.videos.forEach(v => {
                    const u = typeof v === 'string' ? v : (v?.embedUrl || v?.originalUrl || '');
                    if (u && !collected.includes(u.trim())) collected.push(u.trim());
                });
            }
            if (Array.isArray(p.images)) {
                p.images.forEach(img => {
                    if (this.isVideoAsset(img)) {
                        const clean = img.trim();
                        if (!collected.includes(clean)) collected.push(clean);
                    }
                });
            }
            Object.keys(p).forEach(k => {
                if (k.startsWith('post_img') && p[k] !== null && p[k] !== '' && typeof p[k] === 'string') {
                    const val = p[k].trim();
                    if (this.isVideoAsset(val) && !collected.includes(val)) {
                        collected.push(val);
                    }
                }
            });
            return collected.slice(0, 3);
        },

        extractVideoFromSlots(p) {
            const vids = this.extractVideosFromSlots(p);
            return vids.length > 0 ? vids[0] : null;
        },

        getPostImageUrl(img) {
            return getPostImageUrl(img);
        },

        getPostImageVariantUrl(img, variant = 'fallback', verifiedVariants = false) {
            return getPostImageVariantUrl(img, variant, verifiedVariants);
        },

        isGifMedia(img) {
            return isGifMedia(img);
        },

        getProfileImageUrl(img) {
            return getProfileImageUrl(img);
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            try {
                if (typeof dateStr === 'number' || (typeof dateStr === 'string' && /^\d+$/.test(dateStr.trim()))) {
                    return format(Number(dateStr));
                }
                return format(dateStr);
            } catch (e) {
                return dateStr;
            }
        },

        decodeHtml(str) {
            if (!str || typeof str !== 'string') return '';
            let decoded = str;
            // Decode multi-layer encoded entities (&amp;#... -> &#...)
            for (let i = 0; i < 3 && /&amp;(?:#\d+|#x[0-9a-f]+|[a-z]+);/i.test(decoded); i++) {
                decoded = decoded.replace(/&amp;(#\d+|#x[0-9a-f]+|[a-z]+);/gi, '&$1;');
            }
            return decoded
                .replace(/&(?:#?0*39|#x0*27|apos);/gi, "'")
                .replace(/&(?:#?0*34|#x0*22|quot);/gi, '"')
                .replace(/&(?:#?0*60|#x0*3c|lt);/gi, '<')
                .replace(/&(?:#?0*62|#x0*3e|gt);/gi, '>')
                .replace(/&(?:#?0*38|#x0*26|amp);/gi, '&');
        },

        escapeHtml(str) {
            if (!str) return '';
            // Decode any existing entities first so pre-encoded strings (e.g. legacy &#039;) never double-encode to &amp;#039;
            const clean = this.decodeHtml(String(str));
            return clean
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        normalizeEmojiText(str) {
            if (!str || typeof str !== 'string') return '';
            return str
                // Convert U+2764 (Heavy Black Heart) without \uFE0F to \u2764\uFE0F (Red Color Heart)
                .replace(/\u2764(?!\uFE0F)/g, '\u2764\uFE0F')
                // Convert U+2665 (Black Heart Suit) without \uFE0F to \u2764\uFE0F
                .replace(/\u2665(?!\uFE0F)/g, '\u2764\uFE0F')
                // Convert standalone ASCII heart '<3' to '❤️'
                .replace(/(^|\s)<3(?=\s|$|[.,!?])/g, '$1❤️');
        },

        getMediaCaption(post) {
            if (!post) return '';
            let text = (post.displayMessage !== undefined && post.displayMessage !== null)
                ? String(post.displayMessage)
                : String(post.postMessage || '');

            if (!text.trim()) return '';

            // Strip embedded 3D text stage style tokens
            text = text.replace(/\[style:[a-z0-9_]+\]\s*/gi, '');

            // Clean trailing 'Read the full story: <url>' when the 3D Family Chronicle CTA button is rendered
            text = text.replace(/👉\s*Read the full story:\s*(?:https?:\/\/[^\s<>"']+)?\/familyJournal\/\d+/gi, '');

            // Strip photo service links
            text = text.replace(/https?:\/\/(?:photos\.app\.goo\.gl|photos\.google\.com)\/[^\s<>"']+/gi, '');
            text = text.replace(/https?:\/\/(?:www\.)?(?:icloud\.com\/photos|i\.imgur\.com|imgur\.com|ibb\.co|postimg\.cc)\/[^\s<>"']+/gi, '');
            // Strip direct image URLs
            text = text.replace(/https?:\/\/[^\s<>"']+\.(?:jpe?g|png|webp|avif|gif)(?:\?[^\s<>"']*)?/gi, '');
            // Strip video URLs
            text = text.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)[a-zA-Z0-9_-]{11}/gi, '');
            text = text.replace(/(?:https?:\/\/)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/[^\/]*\/videos\/|album\/\d+\/video\/|video\/|)\d+(?:$|\/|\?)/gi, '');
            text = text.replace(/(?:https?:\/\/)?(?:iframe\.videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)\/[a-zA-Z0-9]{32}/gi, '');
            text = text.replace(/(?:https?:\/\/[^\s"'<>]+|\/resources\/(?:videos|images)\/[^\s"'<>]+)\.(?:mp4|webm|ogg|mov)(?:\?[^\s"'<>#]*)?(?:#t=[0-9.]+(?:,[0-9.]+)?)?/gi, '');

            // Strip original/canonical preview URLs if link preview exists
            if (post.link_preview) {
                const stripUrl = (u) => {
                    if (!u || typeof u !== 'string') return;
                    text = text.split(u).join('');
                    const trimmed = u.replace(/\/+$/, '');
                    if (trimmed && trimmed !== u) text = text.split(trimmed).join('');
                    const withSlash = (trimmed || u) + '/';
                    if (withSlash !== u) text = text.split(withSlash).join('');
                };
                stripUrl(post.link_preview.original_url);
                stripUrl(post.link_preview.url);
            }
            if (post.video && post.video.originalUrl) {
                text = text.split(post.video.originalUrl).join('');
            }
            if (Array.isArray(post.videos)) {
                post.videos.forEach(v => {
                    if (v && v.originalUrl) text = text.split(v.originalUrl).join('');
                });
            }

            return this.normalizeEmojiText(text.trim());
        },
};
