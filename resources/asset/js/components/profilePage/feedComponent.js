import axios from 'axios';
import Pusher from 'pusher-js';
import { format } from 'timeago.js';
import Swal from 'sweetalert2';
import { extractVideoFromText, extractVideosFromText, cleanPostMessage } from './videoParser';
import { getCsrfToken } from '../global';
import { getPostImageUrl, getProfileImageUrl, getPostImageVariantUrl } from '../helper/imageUrls';
import { FAMILY_GIFS, GIF_CATEGORIES, isAllowedGifUrl } from './gifCatalog';
import { getPreferredSkinTone, setPreferredSkinTone, SKIN_TONES } from '../emojiPicker';
import { HEIRLOOM_REACTIONS, getHeirloomGlassSvg, triggerHeirloomBurst } from './heirloomGlassReactions';

export function profileFeed(opts = {}) {
    return {
        posts: [],
        feedPageSize: 10,
        feedHasMore: false,
        isLoadingMore: false,
        _serverLoaded: 0,
        isLoading: true,
        errorMessage: '',
        lightboxOpen: false,
        lightboxImages: [],
        lightboxIndex: 0,
        lightboxLoading: false,
        lightboxError: false,
        lightboxPost: null,
        mediaReactionTrayOpen: false,
        mediaCommentDrawerOpen: false,
        mediaCommentInput: '',
        mediaCommentSubmitting: false,
        mediaReactionsMap: (() => {
            try {
                return JSON.parse(localStorage.getItem('fp_media_reactions_v1') || '{}') || {};
            } catch (e) {
                return {};
            }
        })(),
        videoModalOpen: false,
        videoModalVideos: [],
        videoModalIndex: 0,
        videoModalMuted: false,
        videoModalPlaying: true,
        videoModalProgress: 0,
        videoModalCurrentTime: '0:00',
        videoModalDuration: '0:00',
        videoModalRemainingTime: '-0:00',
        videoModalSpeed: 1,
        videoModalHasFrame: false,
        videoModalIsPortrait: true,
        videoModalPost: null,
        videoModalFullscreen: false,
        touchStartX: 0,
        touchStartY: 0,
        heartBursts: {},
        lastTapTime: {},
        tapTimeout: null,
        lightboxDragY: 0,
        lightboxDragging: false,
        currentUserId: opts.userId || localStorage.getItem('requesterId') || '',
        currentFamCode: opts.famCode || localStorage.getItem('requesterFamCode') || '',
        commentInputs: {},
        activeCommentForms: {},
        activeReactions: {},
        activeReactionBars: {},
        activeCommentReactionBars: {},
        commentEmojiOpen: {},
        commentEmojiTab: 'standard',
        commentGifOpen: {},
        commentGifs: {},
        commentFiles: {},
        commentGifCategory: 'all',
        gifCategories: GIF_CATEGORIES,
        familyRoster: Array.isArray(opts.familyRoster) ? opts.familyRoster : [],
        mentionOpen: {},
        mentionMatches: {},
        mentionIndex: {},
        threadExpanded: {},
        pusher: null,
        editingCommentNo: null,
        // postNo -> { comment_no, fullName } of the comment being replied to
        replyingTo: {},
        editCommentText: '',
        activeStreamFilter: 'all',
        activeHashtagFilter: null,
        trendingHashtags: [],
        isHashtagFilterActive: false,
        votingPolls: {},
        votingTwoTruths: {},
        viewedPostNos: new Set(),
        viewedCommentNos: new Set(),
        _postObserver: null,
        _commentObserver: null,
        isViewersModalOpen: false,
        viewersLoading: false,
        viewersList: [],
        viewersType: 'post',
        viewersTargetNo: null,
        viewersCount: 0,
        viewersSearchQuery: '',
        activeSpotlightPostNo: null,
        playingVoicePostNo: null,
        activeAudioInstance: null,
        convertingStagePosts: {},
        stagePickerOpen: {},
        textStageCatalog: [
            {
                slug: 'dynasty_holo',
                shortLabel: 'Dynasty Tree',
                label: '🌳 Dynasty Tree',
                badge: '🌳 DYNASTY LINEAGE CALL',
                icon: 'bi-diagram-3-fill',
                accent: '#34d399',
            },
            {
                slug: 'cyber_glass',
                shortLabel: '3D Holo',
                label: '🔮 3D Holo Glass',
                badge: '🔮 3D HOLOGRAM STAGE',
                icon: 'bi-layers-half',
                accent: '#38bdf8',
            },
            {
                slug: 'alert_pulse',
                shortLabel: 'Alert Trigger',
                label: '🚨 Alert Trigger',
                badge: '🚨 FAMILY ALERT TRIGGER',
                icon: 'bi-broadcast-pin',
                accent: '#fce79a',
            },
            {
                slug: 'grand_announcement',
                shortLabel: 'Announcement',
                label: '📣 Announcement',
                badge: '📣 OFFICIAL BROADCAST',
                icon: 'bi-megaphone-fill',
                accent: '#fbbf24',
            },
            {
                slug: 'aurora_prism',
                shortLabel: 'Aurora Prism',
                label: '🌌 Aurora Prism',
                badge: '🌌 FUTURISTIC PRISM',
                icon: 'bi-stars',
                accent: '#e879f9',
            },
            {
                slug: 'heirloom_velvet',
                shortLabel: 'Royal Velvet',
                label: '🏛️ Royal Velvet',
                badge: '🏛️ HEIRLOOM DECREE',
                icon: 'bi-award-fill',
                accent: '#fde68a',
            },
        ],
        csrfOptions: {
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN',
        },

        extractPostStyle(rawText, explicitStyle = null) {
            const allowed = new Set(this.textStageCatalog.map(s => s.slug));
            if (explicitStyle && typeof explicitStyle === 'string') {
                const clean = explicitStyle.trim().toLowerCase();
                if (allowed.has(clean)) return clean;
            }
            if (!rawText || typeof rawText !== 'string') return null;
            const m = rawText.match(/\[style:([a-z0-9_]+)\]/i);
            if (m && m[1]) {
                const candidate = m[1].toLowerCase();
                if (allowed.has(candidate)) return candidate;
            }
            return null;
        },

        stripStyleToken(text) {
            if (!text || typeof text !== 'string') return '';
            return text.replace(/\[style:[a-z0-9_]+\]\s*/gi, '').trim();
        },

        getPostStageMeta(post) {
            const slug = post?.post_style || this.extractPostStyle(post?.postMessage, post?.post_style);
            if (!slug) return null;
            return this.textStageCatalog.find(s => s.slug === slug) || null;
        },

        hasFamilyTreeCall(post) {
            const text = String(post?.displayMessage || post?.postMessage || '').toLowerCase();
            return (
                text.includes('family tree')
                || text.includes('adding your branch')
                || text.includes('add your branch')
                || text.includes('organogram')
                || post?.post_style === 'dynasty_holo'
            );
        },

        toggleStagePicker(postNo) {
            if (!postNo) return;
            this.stagePickerOpen[postNo] = !this.stagePickerOpen[postNo];
        },

        async setPostStage(post, styleSlug, ev = null) {
            if (!post || !post.post_no || this.convertingStagePosts[post.post_no]) return;
            const prevStyle = post.post_style || null;
            const prevMsg = post.postMessage || '';
            const cleanSlug = (!styleSlug || styleSlug === 'none') ? null : String(styleSlug).toLowerCase();

            // Optimistic UI update
            post.post_style = cleanSlug;
            const stripped = this.stripStyleToken(prevMsg);
            post.postMessage = cleanSlug ? `[style:${cleanSlug}] ${stripped}` : stripped;
            post.displayMessage = this.getMediaCaption(post);
            this.convertingStagePosts[post.post_no] = true;

            if (typeof window.triggerHaptic === 'function') {
                window.triggerHaptic('impact');
            }

            try {
                const res = await axios.put(
                    `/post/${post.post_no}`,
                    { post_style: cleanSlug || 'none' },
                    this.csrfOptions
                );
                const payload = (res?.data?.message && typeof res.data.message === 'object')
                    ? res.data.message
                    : res?.data;
                if (payload?.post_style !== undefined) {
                    post.post_style = payload.post_style || null;
                }
                if (payload?.postMessage) {
                    post.postMessage = this.decodeHtml(payload.postMessage);
                    post.displayMessage = this.getMediaCaption(post);
                }
                const meta = this.getPostStageMeta(post);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: meta ? `Converted to ${meta.label}` : 'Reverted to standard text post',
                    showConfirmButton: false,
                    timer: 2200
                });
            } catch (err) {
                console.error('Failed to convert post stage:', err);
                post.post_style = prevStyle;
                post.postMessage = prevMsg;
                post.displayMessage = this.getMediaCaption(post);
                Swal.fire({
                    icon: 'error',
                    title: 'Stage Conversion Failed',
                    text: err?.response?.data?.message || 'Unable to update post design right now.',
                    confirmButtonColor: '#a31621'
                });
            } finally {
                this.convertingStagePosts[post.post_no] = false;
            }
        },

        async cyclePostStage(post, ev = null) {
            if (!post || !post.post_no) return;
            const order = this.hasFamilyTreeCall(post)
                ? ['dynasty_holo', 'cyber_glass', 'alert_pulse', 'grand_announcement', 'aurora_prism', 'heirloom_velvet']
                : ['cyber_glass', 'alert_pulse', 'grand_announcement', 'dynasty_holo', 'aurora_prism', 'heirloom_velvet'];
            const current = post.post_style || null;
            const idx = current ? order.indexOf(current) : -1;
            const nextSlug = order[(idx + 1) % order.length];
            this.stagePickerOpen[post.post_no] = true;
            await this.setPostStage(post, nextSlug, ev);
        },

        // Living Heirloom Glass 3D Emoji Map
        emojiMap: {
            love:      { icon: '❤️', label: 'Love',      glow: 'rgba(225, 29, 72, 0.45)',  tone: '#e11d48' },
            like:      { icon: '👍', label: 'Like',      glow: 'rgba(245, 158, 11, 0.45)',  tone: '#f59e0b' },
            likes:     { icon: '👍', label: 'Like',      glow: 'rgba(245, 158, 11, 0.45)',  tone: '#f59e0b' },
            haha:      { icon: '😂', label: 'Haha',      glow: 'rgba(234, 179, 8, 0.45)',   tone: '#eab308' },
            shock:     { icon: '😮', label: 'Wow',       glow: 'rgba(2, 132, 199, 0.45)',   tone: '#0284c7' },
            wow:       { icon: '😮', label: 'Wow',       glow: 'rgba(2, 132, 199, 0.45)',   tone: '#0284c7' },
            sad:       { icon: '😢', label: 'Empathy',   glow: 'rgba(13, 148, 136, 0.45)',  tone: '#0d9488' },
            celebrate: { icon: '✨', label: 'Celebrate', glow: 'rgba(147, 51, 234, 0.5)',   tone: '#9333ea' },
        },

        getHeirloomGlassSvg(type, size = 32) {
            return getHeirloomGlassSvg(type, size);
        },

        formatReactionLabel(type) {
            if (!type) return 'Like';
            const norm = String(type).toLowerCase();
            const map = {
                like: 'Like',
                likes: 'Like',
                love: 'Love',
                haha: 'Haha',
                shock: 'Wow',
                wow: 'Wow',
                sad: 'Empathy',
                celebrate: 'Celebrate'
            };
            return map[norm] || (type.charAt(0).toUpperCase() + type.slice(1));
        },

        getReactionColor(type) {
            if (!type) return '';
            const norm = String(type).toLowerCase();
            const map = {
                like: '#f59e0b',
                likes: '#f59e0b',
                love: '#e11d48',
                haha: '#eab308',
                shock: '#0284c7',
                wow: '#0284c7',
                sad: '#0d9488',
                celebrate: '#9333ea'
            };
            return map[norm] || '';
        },

        // Race-conscious diversity state & curated comment palette
        commentSkinTone: getPreferredSkinTone(),
        skinTones: SKIN_TONES,

        setCommentSkinTone(tone) {
            this.commentSkinTone = tone;
            setPreferredSkinTone(tone);
        },

        commentEmojiItems: [
            { base: '👍', skins: ['👍', '👍🏻', '👍🏼', '👍🏽', '👍🏾', '👍🏿'] },
            { base: '👋', skins: ['👋', '👋🏻', '👋🏼', '👋🏽', '👋🏾', '👋🏿'] },
            { base: '👏', skins: ['👏', '👏🏻', '👏🏼', '👏🏽', '👏🏾', '👏🏿'] },
            { base: '🙏', skins: ['🙏', '🙏🏻', '🙏🏼', '🙏🏽', '🙏🏾', '🙏🏿'] },
            { base: '🙌', skins: ['🙌', '🙌🏻', '🙌🏼', '🙌🏽', '🙌🏾', '🙌🏿'] },
            { base: '💪', skins: ['💪', '💪🏻', '💪🏼', '💪🏽', '💪🏾', '💪🏿'] },
            { base: '🤝', skins: ['🤝', '🤝🏻', '🤝🏼', '🤝🏽', '🤝🏾', '🤝🏿'] },
            { base: '🫶', skins: ['🫶', '🫶🏻', '🫶🏼', '🫶🏽', '🫶🏾', '🫶🏿'] },
            { base: '✌️', skins: ['✌️', '✌🏻', '✌🏼', '✌🏽', '✌🏾', '✌🏿'] },
            '🫡',
            '❤️', '💖', '🥰', '😍', '😂', '🤣',
            '😊', '🥹', '🥳', '😎', '🤗', '😮',
            '😢', '😭', '🔥', '💯', '🎉', '✨',
            '🎂', '🎈', '🥂', '💐', '🌟', '👑',
            '🍰', '☕', '🍕', '🏡', '🌸', '🍀',
            '🤔', '😅', '😡', '👀', '🏆', '🎁',
        ],

        get commentEmojiPalette() {
            const tone = this.commentSkinTone || 0;
            return this.commentEmojiItems.map(item => {
                if (typeof item === 'string') return item;
                return (item.skins && item.skins[tone]) ? item.skins[tone] : item.base;
            });
        },

        commentEmojiMap: {
            like:      '👍',
            likes:     '👍',
            love:      '❤️',
            haha:      '😄',
            wow:       '😮',
            shock:     '😮',
            sad:       '😢',
            angry:     '😠',
            celebrate: '✨',
        },

        async init() {
            if (typeof window !== 'undefined') {
                window.__getHeirloomGlassSvg = getHeirloomGlassSvg;
                window.__HEIRLOOM_REACTIONS = HEIRLOOM_REACTIONS;
            }

            // Register listeners before awaiting the initial fetch - a post created (or a
            // Pusher event received) while that first fetch is still in flight must not be
            // silently dropped because nothing was listening for it yet.
            this.initPusher();
            this.initEventListeners();
            this.loadFamilyRoster();
            await this.fetchPosts();
            await this.loadTrendingHashtags();
            this.scrollToHashPost();
            this.observePostViews();
            this.observeCommentViews();
            this.observeFeedVideos();

            // Set up MutationObserver on feed container so newly added posts or infinite scroll batches
            // automatically have their video autoplay and view observers attached
            if (typeof MutationObserver !== 'undefined') {
                const postContainer = document.getElementById('postIt');
                if (postContainer) {
                    this._feedMutationObserver = new MutationObserver(() => {
                        this.observeFeedVideos();
                        this.observePostViews();
                        this.observeCommentViews();
                    });
                    this._feedMutationObserver.observe(postContainer, { childList: true, subtree: true });
                }
            }

            // Expose globally for dynamic injections
            window.observeFeedVideos = () => this.observeFeedVideos();

            // Expose filter helper globally for post pills
            window.filterByFamilyHashtag = (tag) => {
                this.filterByHashtag(tag);
            };

            // Deep-link listener for in-page hash changes
            window.addEventListener('hashchange', () => this.scrollToHashPost());
        },

        // Jump to (and briefly highlight) the post or comment referenced by a #postNNN or #commentNNN link
        // or ?post_no=NNN query param (e.g. from an email/push notification). Posts and comments render
        // asynchronously, so browser native hash scroll never finds the element in time.
        scrollToHashPost() {
            let targetId = '';
            let isComment = false;

            // 1. Check window.location.hash
            const rawHash = (window.location.hash || '').replace(/^[#?]+/, '');
            if (rawHash) {
                if (rawHash.startsWith('post-') || rawHash.startsWith('post')) {
                    const num = rawHash.replace(/\D/g, '');
                    if (num) targetId = 'post' + num;
                } else if (rawHash.startsWith('comment-') || rawHash.startsWith('comment')) {
                    const num = rawHash.replace(/\D/g, '');
                    if (num) {
                        targetId = 'comment' + num;
                        isComment = true;
                    }
                }
            }

            // 2. Fallback to query parameters (?post_no=NNN, ?comment_no=NNN)
            if (!targetId && window.location.search) {
                try {
                    const params = new URLSearchParams(window.location.search);
                    const commentNo = params.get('comment_no') || params.get('comment');
                    const postNo = params.get('post_no') || params.get('post');
                    if (commentNo && /^\d+$/.test(commentNo)) {
                        targetId = 'comment' + commentNo;
                        isComment = true;
                    } else if (postNo && /^\d+$/.test(postNo)) {
                        targetId = 'post' + postNo;
                    }
                } catch (e) {
                    console.warn('[Feed] Search param parse error:', e);
                }
            }

            if (!targetId) return;

            // 3. If targeting a comment, ensure its thread is expanded if collapsed
            if (isComment) {
                const cNo = parseInt(targetId.replace('comment', ''), 10);
                if (cNo && Array.isArray(this.posts)) {
                    for (const p of this.posts) {
                        if (Array.isArray(p.comments)) {
                            const found = p.comments.find(c => parseInt(c.comment_no, 10) === cNo);
                            if (found) {
                                const rootNo = found.parent_comment_no ? String(found.parent_comment_no) : String(found.comment_no);
                                this.threadExpanded[rootNo] = true;
                                break;
                            }
                        }
                    }
                }
            }

            // 4. Polling loop with timeout up to 3 seconds (30 attempts @ 100ms)
            let attempts = 0;
            const maxAttempts = 30;
            const interval = setInterval(() => {
                attempts++;
                const target = document.getElementById(targetId)
                    || document.querySelector(`[data-id="${targetId}"]`)
                    || (isComment ? document.querySelector(`[data-comment-no="${targetId.replace('comment', '')}"]`) : null);

                if (target) {
                    clearInterval(interval);
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const highlightClass = isComment ? 'highlighted-comment' : 'highlighted-post';
                    target.classList.add(highlightClass);
                    setTimeout(() => target.classList.remove(highlightClass), 2500);
                } else if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    // 5. Fallback: If targeted post was not in initial batch (e.g. older tagged post from notification), fetch dynamically
                    if (!isComment && targetId.startsWith('post')) {
                        const pNo = parseInt(targetId.replace('post', ''), 10);
                        if (pNo > 0 && !this.posts.some(p => parseInt(p?.post_no, 10) === pNo)) {
                            axios.get(`/api/posts/${pNo}`).then(res => {
                                if (res?.data?.status === 'success' && res?.data?.data) {
                                    const postData = this.normalizePost(res.data.data);
                                    this.posts.unshift(postData);
                                    setTimeout(() => {
                                        const el = document.getElementById(targetId) || document.querySelector(`[data-post-no="${pNo}"]`);
                                        if (el) {
                                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                            el.classList.add('highlighted-post');
                                            setTimeout(() => el.classList.remove('highlighted-post'), 2500);
                                        }
                                    }, 120);
                                }
                            }).catch(() => {});
                        }
                    }
                }
            }, 100);
        },

        async fetchPosts(options = {}) {
            const filter = options.filter || (this.activeStreamFilter === 'tagged' ? 'tagged' : '');
            this.isLoading = this.posts.length === 0;
            this.errorMessage = '';

            // 1. Instant offline cache hydration (only if on default 'all' stream)
            if (!filter && window.offlineSync && typeof window.offlineSync.getCachedFeed === 'function') {
                try {
                    const cachedPosts = await window.offlineSync.getCachedFeed();
                    if (Array.isArray(cachedPosts) && cachedPosts.length > 0 && this.posts.length === 0) {
                        // Only hydrate the first page from cache; the rest streams in on scroll.
                        this.posts = cachedPosts.slice(0, this.feedPageSize).map(p => this.normalizePost(p));
                        this.isLoading = false;
                    }
                } catch (cacheErr) {
                    console.warn('[Feed] Offline cache read:', cacheErr);
                }
            }

            // 2. Fetch fresh updates from server
            try {
                // Refreshes keep the depth already scrolled (min one page, max 50) so the list doesn't collapse.
                const depth = Math.min(50, Math.max(this.feedPageSize, this._serverLoaded || 0));
                const params = new URLSearchParams({ limit: String(depth), offset: '0' });
                if (filter) params.set('filter', filter);
                const response = await axios.get(`/post/getAllPostCommentByFamCode?${params.toString()}`);
                const rawPosts = response?.data?.message?.message;
                
                if (Array.isArray(rawPosts)) {
                    this._serverLoaded = rawPosts.length;
                    this.feedHasMore = !!response?.data?.message?.pagination?.has_more;
                    this.posts = rawPosts.map(p => this.normalizePost(p));
                    // Update offline store in background if main stream
                    if (!filter && window.offlineSync && typeof window.offlineSync.cacheFeed === 'function') {
                        window.offlineSync.cacheFeed(rawPosts).catch(() => {});
                    }
                    this.scrollToHashPost();
                    this.observePostViews();
                    this.observeCommentViews();
                    this.observeFeedVideos();
                } else if (this.posts.length === 0) {
                    this.posts = [];
                }
            } catch (err) {
                console.error('Failed to load posts from network:', err);
                if (this.posts.length === 0) {
                    this.errorMessage = 'Unable to load family posts. Please check your network connection.';
                }
            } finally {
                this.isLoading = false;
            }
        },

        async loadMorePosts() {
            if (this.isLoadingMore || !this.feedHasMore || this.isLoading || this.posts.length === 0) return;
            this.isLoadingMore = true;
            try {
                const filter = this.activeStreamFilter === 'tagged' ? 'tagged' : '';
                const params = new URLSearchParams({ limit: String(this.feedPageSize), offset: String(this._serverLoaded) });
                if (filter) params.set('filter', filter);
                const response = await axios.get(`/post/getAllPostCommentByFamCode?${params.toString()}`);
                const raw = response?.data?.message?.message;
                if (Array.isArray(raw)) {
                    const seen = new Set(this.posts.map(p => String(p.post_no)));
                    const fresh = raw.filter(p => !seen.has(String(p.post_no))).map(p => this.normalizePost(p));
                    this.posts = this.posts.concat(fresh);
                    this._serverLoaded += raw.length;
                    this.feedHasMore = raw.length > 0 && !!response?.data?.message?.pagination?.has_more;
                    this.$nextTick(() => {
                        this.observePostViews();
                        this.observeCommentViews();
                        this.observeFeedVideos();
                    });
                } else {
                    this.feedHasMore = false;
                }
            } catch (err) {
                console.warn('[Feed] Load more failed:', err);
            } finally {
                this.isLoadingMore = false;
            }
        },

        async setStreamFilter(filterType) {
            this.activeStreamFilter = filterType;
            this._serverLoaded = 0;
            this.feedHasMore = false;
            this.isLoading = true;
            await this.fetchPosts({ filter: filterType === 'tagged' ? 'tagged' : '' });
        },

        normalizePost(p) {
            let profImg = '/public/avatar/avatarF.png';
            const rawImg = p?.img || p?.profileImg;
            if (rawImg) {
                profImg = (rawImg.startsWith('/') || rawImg.startsWith('http')) ? rawImg : `/resources/images/profile/${rawImg}`;
            }
            const cleanMsg = this.decodeHtml(p?.postMessage || '');
            let detectedVideos = (Array.isArray(p?.videos) && p.videos.length > 0)
                ? p.videos
                : extractVideosFromText(cleanMsg, 3);
            if (!detectedVideos || detectedVideos.length === 0) {
                const videoFilesFromSlots = this.extractVideosFromSlots(p);
                if (videoFilesFromSlots && videoFilesFromSlots.length > 0) {
                    detectedVideos = videoFilesFromSlots.map(videoFile => {
                        let slotUrl = null;
                        let matchThumb = null;
                        if (Array.isArray(p?.videos)) {
                            const match = p.videos.find(v => v && (v.originalUrl === videoFile || v.embedUrl === videoFile));
                            if (match && match.embedUrl) {
                                slotUrl = match.embedUrl;
                            }
                            if (match && match.thumbnailUrl) {
                                matchThumb = match.thumbnailUrl;
                            }
                        }
                        if (!slotUrl) {
                            slotUrl = (videoFile.startsWith('/') || videoFile.startsWith('http'))
                                ? videoFile
                                : `/resources/videos/posts/${videoFile}`;
                        }
                        const thumb = matchThumb
                            || (videoFile.includes('googleusercontent.com') ? videoFile.replace(/=.*$/i, '=s1600-no') : null);
                        return {
                            type: 'direct',
                            videoId: null,
                            thumbnailUrl: thumb,
                            embedUrl: slotUrl,
                            originalUrl: videoFile,
                            trimStart: null,
                            trimEnd: null,
                        };
                    });
                }
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
                post_style: this.extractPostStyle(cleanMsg, p?.post_style),
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

            // Progressive enhancement: lazy unfurl link preview if server hasn't pre-hydrated
            if (!normalized.link_preview && normalized.postMessage && !normalized.video) {
                const urlMatch = normalized.postMessage.match(/(https?:\/\/[^\s<>"']+)/i);
                if (urlMatch && urlMatch[1]) {
                    this.lazyFetchLinkPreview(normalized, urlMatch[1]);
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

        getPostImageVariantUrl(img, variant = 'fallback') {
            return getPostImageVariantUrl(img, variant);
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
                if (post.link_preview.original_url) text = text.split(post.link_preview.original_url).join('');
                if (post.link_preview.url) text = text.split(post.link_preview.url).join('');
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

        observePostViews() {
            if (typeof IntersectionObserver === 'undefined') return;
            if (!this._postObserver) {
                this._postObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const postNo = parseInt(entry.target.getAttribute('data-post-no') || '0', 10);
                            if (postNo > 0) {
                                this.recordPostView(postNo);
                                this._postObserver.unobserve(entry.target);
                            }
                        }
                    });
                }, { threshold: 0.4 });
            }
            setTimeout(() => {
                const postEls = document.querySelectorAll('.feed-post-card[data-post-no]');
                postEls.forEach(el => {
                    const pNo = parseInt(el.getAttribute('data-post-no') || '0', 10);
                    if (pNo > 0 && !this.viewedPostNos.has(pNo)) {
                        this._postObserver.observe(el);
                    }
                });
            }, 300);
        },

        async recordPostView(postNo) {
            if (!postNo || this.viewedPostNos.has(postNo)) return;
            this.viewedPostNos.add(postNo);
            try {
                const csrfToken = getCsrfToken();
                const headers = { 'Content-Type': 'application/json' };
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                    headers['X-XSRF-TOKEN'] = csrfToken;
                }
                const res = await axios.post('/api/posts/view', { post_no: postNo }, { headers });
                if (res.data?.status === 'success' && typeof res.data.views_count === 'number') {
                    const post = this.posts.find(p => p.post_no === postNo);
                    if (post) {
                        post.views_count = res.data.views_count;
                    }
                }
            } catch (err) {
                // Silently ignore view counter increments
            }
        },

        observeCommentViews() {
            if (typeof IntersectionObserver === 'undefined') return;
            if (!this.viewedCommentNos) {
                this.viewedCommentNos = new Set();
            }
            if (!this._commentObserver) {
                this._commentObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const cNo = parseInt(entry.target.getAttribute('data-comment-no') || '0', 10);
                            if (cNo > 0) {
                                this.recordCommentView(cNo);
                                this._commentObserver.unobserve(entry.target);
                            }
                        }
                    });
                }, { threshold: 0.3 });
            }
            setTimeout(() => {
                const commentEls = document.querySelectorAll('.feed-comment-item[data-comment-no]');
                commentEls.forEach(el => {
                    const cNo = parseInt(el.getAttribute('data-comment-no') || '0', 10);
                    if (cNo > 0 && !this.viewedCommentNos.has(cNo)) {
                        this._commentObserver.observe(el);
                    }
                });
            }, 300);
        },

        async recordCommentView(commentNo) {
            if (!commentNo || this.viewedCommentNos.has(commentNo)) return;
            this.viewedCommentNos.add(commentNo);
            try {
                const csrfToken = getCsrfToken();
                const headers = { 'Content-Type': 'application/json' };
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                    headers['X-XSRF-TOKEN'] = csrfToken;
                }
                const res = await axios.post('/api/comments/view', { comment_no: commentNo }, { headers });
                if (res.data?.status === 'success' && typeof res.data.views_count === 'number') {
                    for (const post of this.posts) {
                        if (Array.isArray(post.comments)) {
                            const c = post.comments.find(item => item.comment_no === commentNo);
                            if (c) {
                                c.views_count = res.data.views_count;
                                break;
                            }
                        }
                    }
                }
            } catch (err) {
                // Silently ignore view counter increments
            }
        },

        observeFeedVideos() {
            if (typeof IntersectionObserver === 'undefined') return;

            if (!this._feedVideoObserver) {
                this._activePlayingFeedVideo = null;

                this._feedVideoObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        const video = entry.target;
                        if (!video || !(video instanceof HTMLVideoElement)) return;

                        if (entry.isIntersecting && entry.intersectionRatio >= 0.45) {
                            // Autoplay video when in view during scrolling
                            if (!video._userManuallyPaused) {
                                if (this._activePlayingFeedVideo && this._activePlayingFeedVideo !== video && !this._activePlayingFeedVideo.paused) {
                                    this._activePlayingFeedVideo.pause();
                                }
                                this._activePlayingFeedVideo = video;

                                if (!video.muted) {
                                    video.muted = true;
                                }
                                video._autoPlaying = true;
                                const playPromise = video.play();
                                if (playPromise !== undefined) {
                                    playPromise.catch(() => {
                                        if (!video.muted) {
                                            video.muted = true;
                                            video.play().catch(() => {});
                                        }
                                    });
                                }
                            }
                        } else if (!entry.isIntersecting || entry.intersectionRatio < 0.2) {
                            // Stop playing video when scrolled out of view
                            if (!video.paused) {
                                video._autoPlaying = false;
                                video.pause();
                            }
                            if (this._activePlayingFeedVideo === video) {
                                this._activePlayingFeedVideo = null;
                            }
                            // Reset user pause lock when scrolled completely away so it can autoplay next time
                            video._userManuallyPaused = false;
                        }
                    });
                }, {
                    threshold: [0.0, 0.2, 0.45, 0.75]
                });
            }

            if (!this._feedMultiVideoObserver) {
                this._feedMultiVideoObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        const container = entry.target;
                        if (!container) return;

                        if (entry.isIntersecting && entry.intersectionRatio >= 0.35) {
                            // Container in view: autoplay the currently visible card in the horizontal track
                            const track = container.querySelector('.multi-video-scroll-track');
                            if (!track) return;
                            const cards = Array.from(track.querySelectorAll('.multi-video-card'));
                            if (!cards.length) return;
                            const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
                            let activeCard = cards[0];
                            if (maxScroll > 0 && track.scrollLeft >= maxScroll - 16) {
                                activeCard = cards[cards.length - 1];
                            } else {
                                let minDiff = Infinity;
                                cards.forEach(c => {
                                    const diff = Math.abs(track.scrollLeft - c.offsetLeft);
                                    if (diff < minDiff) {
                                        minDiff = diff;
                                        activeCard = c;
                                    }
                                });
                            }
                            const targetVideo = activeCard ? activeCard.querySelector('video') : null;
                            if (targetVideo && !targetVideo._userManuallyPaused) {
                                cards.forEach(c => {
                                    const v = c.querySelector('video');
                                    if (v && v !== targetVideo && !v.paused) v.pause();
                                });
                                if (this._activePlayingFeedVideo && this._activePlayingFeedVideo !== targetVideo && !this._activePlayingFeedVideo.paused) {
                                    this._activePlayingFeedVideo.pause();
                                }
                                this._activePlayingFeedVideo = targetVideo;
                                targetVideo.muted = true;
                                targetVideo._autoPlaying = true;
                                targetVideo.play().catch(() => {});
                            }
                        } else if (!entry.isIntersecting || entry.intersectionRatio < 0.15) {
                            // Scrolled away: pause all videos in this multi-video container
                            const track = container.querySelector('.multi-video-scroll-track');
                            if (track) {
                                track.querySelectorAll('video').forEach(v => {
                                    if (!v.paused) {
                                        v._autoPlaying = false;
                                        v.pause();
                                    }
                                    v._userManuallyPaused = false;
                                });
                            }
                        }
                    });
                }, {
                    threshold: [0.0, 0.15, 0.35, 0.7]
                });
            }

            setTimeout(() => {
                // Observe multi-video containers for sequential autoplay
                const multiContainers = document.querySelectorAll('.multi-video-horizontal-container');
                multiContainers.forEach(mc => {
                    if (!mc.dataset.multiFeedObserved) {
                        mc.dataset.multiFeedObserved = 'true';
                        this._feedMultiVideoObserver.observe(mc);
                    }
                });

                // Observe single videos (excluding those inside multi-video cards)
                const videoEls = document.querySelectorAll('video.feed-post-video:not(.multi-video-card video), .video-embed-container:not(.multi-video-horizontal-container) video');
                videoEls.forEach(video => {
                    if (!video.dataset.feedObserved) {
                        video.dataset.feedObserved = 'true';
                        video.playsInline = true;
                        video.setAttribute('playsinline', '');
                        video.setAttribute('webkit-playsinline', '');

                        video.addEventListener('pause', () => {
                            if (!video._autoPlaying) {
                                video._userManuallyPaused = true;
                            }
                            video._autoPlaying = false;
                        });

                        video.addEventListener('play', () => {
                            video._userManuallyPaused = false;
                        });

                        this._feedVideoObserver.observe(video);
                    }
                });
            }, 300);
        },

        async openPostViewersModal(post) {
            if (!post || !post.post_no) return;
            this.viewersType = 'post';
            this.viewersTargetNo = post.post_no;
            this.viewersCount = post.views_count || 0;
            this.viewersList = [];
            this.viewersSearchQuery = '';
            this.viewersLoading = true;
            this.isViewersModalOpen = true;

            try {
                const res = await axios.get(`/api/posts/${post.post_no}/viewers`);
                if (res.data?.status === 'success') {
                    this.viewersList = Array.isArray(res.data.viewers) ? res.data.viewers : [];
                    this.viewersCount = typeof res.data.views_count === 'number' ? res.data.views_count : this.viewersList.length;
                }
            } catch (err) {
                console.error('[openPostViewersModal] Failed to fetch viewers:', err);
            } finally {
                this.viewersLoading = false;
            }
        },

        async openCommentViewersModal(comment) {
            if (!comment || !comment.comment_no) return;
            this.viewersType = 'comment';
            this.viewersTargetNo = comment.comment_no;
            this.viewersCount = comment.views_count || 0;
            this.viewersList = [];
            this.viewersSearchQuery = '';
            this.viewersLoading = true;
            this.isViewersModalOpen = true;

            try {
                const res = await axios.get(`/api/comments/${comment.comment_no}/viewers`);
                if (res.data?.status === 'success') {
                    this.viewersList = Array.isArray(res.data.viewers) ? res.data.viewers : [];
                    this.viewersCount = typeof res.data.views_count === 'number' ? res.data.views_count : this.viewersList.length;
                }
            } catch (err) {
                console.error('[openCommentViewersModal] Failed to fetch viewers:', err);
            } finally {
                this.viewersLoading = false;
            }
        },

        closeViewersModal() {
            this.isViewersModalOpen = false;
            this.viewersList = [];
            this.viewersSearchQuery = '';
        },

        getFilteredViewers() {
            if (!this.viewersSearchQuery || !this.viewersSearchQuery.trim()) {
                return this.viewersList;
            }
            const q = this.viewersSearchQuery.toLowerCase().trim();
            return this.viewersList.filter(v => {
                const fullName = `${v.firstName || ''} ${v.lastName || ''}`.toLowerCase();
                return fullName.includes(q);
            });
        },

        getViewerAvatar(viewer) {
            const img = viewer?.profile_img || viewer?.img;
            if (!img) return '/public/avatar/avatarM.png';
            if (img.startsWith('/') || img.startsWith('http')) return img;
            return `/resources/images/profile/${img}`;
        },

        formatViewerTime(dateStr) {
            if (!dateStr) return 'Recently';
            try {
                const d = new Date(dateStr.replace(/-/g, '/'));
                if (isNaN(d.getTime())) return 'Recently';
                return format(d);
            } catch (e) {
                return 'Recently';
            }
        },

        async loadFamilyRoster() {
            if (this.familyRoster && this.familyRoster.length > 0) return;
            try {
                const res = await axios.get('/allMembers/processApiData');
                const members = res?.data?.data || res?.data?.message || [];
                if (Array.isArray(members)) {
                    this.familyRoster = members.map(m => ({
                        id: String(m.id || ''),
                        firstName: String(m.firstName || '').trim(),
                        lastName: String(m.lastName || '').trim(),
                        img: m.img || ''
                    }));
                }
            } catch (err) {
                // Non-blocking
            }
        },

        renderHashtags(message, arEmoji = null) {
            message = message || '';
            // Attached 3D emoji sits inline at the end of the caption, like a typed emoji.
            if (arEmoji && /^[a-z0-9_]+_3d$/i.test(arEmoji) && !message.includes(':' + arEmoji + ':')) {
                message = (message.trim() ? message.replace(/\s+$/, '') + ' ' : '') + ':' + arEmoji + ':';
            }
            if (!message) return '';
            let safeText = this.escapeHtml(message);
            // A caption made only of living emojis renders them sticker-sized so they stand out.
            const stickerRe = /:(?:[a-z0-9_]+_3d|me_[a-z]+@[A-Za-z0-9_-]{1,64}):/gi;
            const emojiOnly = /:(?:[a-z0-9_]+_3d|me_[a-z]+@[A-Za-z0-9_-]{1,64}):/i.test(message) && message.replace(stickerRe, '').trim() === '';
            const liveEmojiClass = emojiOnly ? 'atg-emoji-container atg-live-emoji atg-live-emoji--jumbo' : 'atg-emoji-container atg-live-emoji';

            // Render 3D family emojis (:family_roots_3d:, :family_clap_dark_3d:, etc.).
            // Any :<id>_3d: token is accepted only if it is a known catalog id (incl. tone variants),
            // so new catalog emojis render automatically without touching this regex.
            // Me-stickers (RFC-0011): :me_<reaction>@<userId>: → the author's avatar doing that reaction.
            // The server strips tokens naming anyone but the author, and serves the image only to family.
            const stickerClass = emojiOnly ? 'atg-me-sticker atg-me-sticker--jumbo' : 'atg-me-sticker';
            safeText = safeText.replace(/:me_(hello|laugh|wow|love|nod|dance|point|clap)@([A-Za-z0-9_-]{1,64}):/g, (match, reaction, uid) => {
                return `<span class="${stickerClass}" role="button" tabindex="0" data-testid="me-sticker" onclick="event.stopPropagation(); window.AtgEmojiEngine && window.AtgEmojiEngine.tapSticker(this, '${reaction}')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}"><img src="/api/emoji/avatar-sticker/${uid}/${reaction}?v=live2" loading="lazy" width="64" height="64" alt="Avatar sticker: ${reaction}" title="Tap to replay" onerror="this.closest('.atg-me-sticker').remove()"></span>`;
            });

            const knownIds = this.livingEmojiIdSet();
            safeText = safeText.replace(/:([a-z0-9_]+_3d):/gi, (match, rawId) => {
                const emojiId = rawId.toLowerCase();
                if (knownIds && !knownIds.has(emojiId)) return match;
                return `<span class="${liveEmojiClass}" role="button" tabindex="0" onclick="event.stopPropagation(); window.AtgEmojiEngine && window.AtgEmojiEngine.tapLive(this, '${emojiId}')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}"><img src="/public/img/emoji/${emojiId}.svg?v=20261107a" onerror="this.src='/img/emoji/${emojiId}.svg?v=20261107a'" class="atg-3d-emoji" loading="lazy" width="26" height="26" alt="${emojiId}" title="Tap to play · tap again for 3D / AR"></span>`;
            });

            // Linkify URLs into secure clickable links
            safeText = safeText.replace(/(https?:\/\/[^\s<>"']+)/gi, (match) => {
                const cleanHref = match.replace(/[.,;:!?)]+$/, '');
                return `<a href="${cleanHref}" target="_blank" rel="noopener noreferrer" class="text-primary fw-medium text-decoration-underline" style="word-break: break-all;" onclick="event.stopPropagation();">${cleanHref}</a>`;
            });

            // Highlight @First Last mentions as chips
            safeText = safeText.replace(/(?<![\w@])@([A-Z\p{Lu}][\p{L}'-]+(?:\s+[A-Z\p{Lu}][\p{L}'-]+)+)(?![\w])/gu, (match, name) => {
                return `<span class="mention-chip" title="Mentioned: ${name}"><i class="bi bi-at"></i>${name}</span>`;
            });

            // Hashtag pills (require word boundary/whitespace or start, never preceded by & or inside an entity, must start with a letter)
            return safeText.replace(/(?<![&\w])#([a-zA-Z][a-zA-Z0-9_]{1,39})(?!;)/gu, (match, tag) => {
                const cleanTag = tag.toLowerCase();
                return `<span class="badge family-tag-pill" role="button" tabindex="0" onclick="window.filterByFamilyHashtag('${cleanTag}')" title="Filter family memories by #${cleanTag}"><i class="bi bi-hash me-0.5"></i>${tag}</span>`;
            });
        },

        renderCommentText(comment) {
            return this.renderHashtags(comment);
        },

        async lazyFetchLinkPreview(post, url) {
            if (!post || !url) return;
            try {
                const res = await axios.get(`/api/link-preview?url=${encodeURIComponent(url)}`);
                if (res?.data?.status === 'success' && res?.data?.data) {
                    const d = res.data.data;
                    // `post` may be the raw object from normalizePost(); assigning to it
                    // bypasses Alpine's proxy, so write to the reactive copy in this.posts.
                    const target = this.posts?.find(p => p?.post_no === post.post_no) || post;
                    target.link_preview = d;

                    // If linked album contains multiple photos or videos, promote to native multi-media carousel
                    if (Array.isArray(d.album_images) && d.album_images.length > 0) {
                        target.images = d.album_images;
                    } else if (
                        (!target.images || target.images.length === 0)
                        && (!target.videos || target.videos.length === 0)
                        && !target.video
                        && (d.is_photo_link || d.is_google_photo)
                        && (d.photo_url || d.image)
                        && !d.is_video
                        && (!d.album_videos || d.album_videos.length === 0)
                    ) {
                        const photoAsset = d.photo_url || d.image;
                        target.images = [photoAsset];
                    }

                    if (Array.isArray(d.album_videos) && d.album_videos.length > 0) {
                        target.videos = d.album_videos.map(vUrl => ({
                            type: 'direct',
                            videoId: null,
                            thumbnailUrl: vUrl.replace(/=.*$/i, '=s1600-no'),
                            embedUrl: `/member/stream-video?url=${encodeURIComponent(vUrl)}`,
                            originalUrl: vUrl,
                            trimStart: null,
                            trimEnd: null
                        }));
                        target.video = target.videos[0];
                    }

                    if (d.is_photo_link || d.is_google_photo || d.album_images?.length || d.album_videos?.length) {
                        const origUrl = d.original_url || url || '';
                        const canonUrl = d.url || '';
                        let cleanText = target.displayMessage || target.postMessage || '';
                        if (origUrl) cleanText = cleanText.split(origUrl).join('');
                        if (canonUrl) cleanText = cleanText.split(canonUrl).join('');
                        cleanText = cleanText.replace(/https?:\/\/(?:photos\.app\.goo\.gl|photos\.google\.com)\/[^\s<>"']+/gi, '');
                        target.displayMessage = this.normalizeEmojiText(cleanText.trim());
                    }
                }
            } catch (_) {
                // Silently ignore if unresolvable
            }
        },

        async loadTrendingHashtags() {
            try {
                const res = await axios.get('/api/hashtags/trending?limit=10');
                if (res?.data?.status === 'success' && Array.isArray(res.data.tags)) {
                    this.trendingHashtags = res.data.tags;
                }
            } catch (e) {
                console.warn('[Feed] Trending hashtags load failed:', e);
            }
        },

        async filterByHashtag(tag) {
            if (!tag) return;
            const cleanTag = tag.toLowerCase().replace(/^#/, '');
            this.isLoading = true;
            this.activeHashtagFilter = cleanTag;
            this.isHashtagFilterActive = true;

            try {
                const res = await axios.get(`/api/hashtags/stream?tag=${encodeURIComponent(cleanTag)}`);
                const streamData = res?.data?.data;
                if (streamData && Array.isArray(streamData.items)) {
                    this.posts = streamData.items.map(p => this.normalizePost(p));
                } else {
                    this.posts = [];
                }
            } catch (err) {
                console.error('[Feed] Failed to filter by hashtag:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Memory Stream Error',
                    text: 'Could not load memories for this hashtag.',
                    confirmButtonColor: '#a31621'
                });
            } finally {
                this.isLoading = false;
                const feedElem = document.getElementById('postIt');
                if (feedElem) {
                    feedElem.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        },

        async clearHashtagFilter() {
            this.activeHashtagFilter = null;
            this.isHashtagFilterActive = false;
            await this.fetchPosts();
            await this.loadTrendingHashtags();
        },

        toggleCommentForm(postNo) {
            this.activeCommentForms[postNo] = !this.activeCommentForms[postNo];
            if (this.activeCommentForms[postNo]) {
                setTimeout(() => this.observeCommentViews(), 200);
            }
        },

        // ── Threaded replies (one level: a reply to a reply hangs under the top-level comment) ──

        /**
         * Top-level comments in feed order, each followed by its replies.
         * If a root comment has > 2 replies and the thread is not expanded, only the first 2
         * replies are shown, followed by an interactive expansion toggle.
         */
        threadedComments(post) {
            const all = Array.isArray(post?.comments) ? post.comments : [];
            const known = new Set(all.map(c => String(c?.comment_no)));
            const repliesOf = {};
            const roots = [];
            for (const c of all) {
                const parent = c?.parent_comment_no ? String(c.parent_comment_no) : '';
                if (parent && known.has(parent)) {
                    (repliesOf[parent] = repliesOf[parent] || []).push(c);
                } else {
                    roots.push(c);
                }
            }

            const result = [];
            for (const root of roots) {
                result.push(root);
                const rootNo = String(root.comment_no);
                const replies = repliesOf[rootNo] || [];
                if (replies.length <= 2 || this.threadExpanded[rootNo]) {
                    result.push(...replies);
                } else {
                    result.push(...replies.slice(0, 2));
                }
            }
            return result;
        },

        getThreadRoot(post, comment) {
            if (!comment) return null;
            const parent = comment.parent_comment_no ? String(comment.parent_comment_no) : '';
            if (!parent) return comment;
            const all = Array.isArray(post?.comments) ? post.comments : [];
            return all.find(c => String(c?.comment_no) === parent) || comment;
        },

        getRepliesForRoot(post, rootComment) {
            const rootNo = String(rootComment?.comment_no || '');
            if (!rootNo) return [];
            const all = Array.isArray(post?.comments) ? post.comments : [];
            return all.filter(c => String(c?.parent_comment_no || '') === rootNo);
        },

        getTotalReplyCount(post, rootComment) {
            return this.getRepliesForRoot(post, rootComment).length;
        },

        getHiddenReplyCount(post, rootComment) {
            const rootNo = String(rootComment?.comment_no || '');
            if (!rootNo || this.threadExpanded[rootNo]) return 0;
            const total = this.getTotalReplyCount(post, rootComment);
            return total > 2 ? total - 2 : 0;
        },

        toggleThread(rootCommentNo) {
            const key = String(rootCommentNo);
            this.threadExpanded[key] = !this.threadExpanded[key];
            if (this.threadExpanded[key]) {
                setTimeout(() => this.observeCommentViews(), 200);
            }
        },

        isLastVisibleInThread(post, comment) {
            if (!comment) return false;
            const root = this.getThreadRoot(post, comment);
            if (!root) return false;
            const rootNo = String(root.comment_no);
            const replies = this.getRepliesForRoot(post, root);
            if (replies.length === 0) {
                return String(comment.comment_no) === rootNo;
            }
            const isExp = !!this.threadExpanded[rootNo];
            const visibleReplies = (replies.length <= 2 || isExp) ? replies : replies.slice(0, 2);
            const lastItem = visibleReplies[visibleReplies.length - 1];
            return String(comment.comment_no) === String(lastItem?.comment_no);
        },

        isReply(post, comment) {
            const parent = comment?.parent_comment_no;
            return !!parent && (post?.comments || []).some(c => String(c?.comment_no) === String(parent));
        },

        startReply(post, comment) {
            const postNo = post?.post_no;
            if (!postNo || !comment?.comment_no) return;
            this.replyingTo[postNo] = { comment_no: comment.comment_no, fullName: comment.fullName || 'Family Member' };
            this.activeCommentForms[postNo] = true;
            const root = this.getThreadRoot(post, comment);
            if (root?.comment_no) {
                this.threadExpanded[String(root.comment_no)] = true;
            }
            // Pre-fill an @mention of the person being answered (server resolves it to a notification).
            if (!(this.commentInputs[postNo] || '').trim() && String(comment.id) !== String(this.currentUserId)) {
                this.commentInputs[postNo] = `@${comment.fullName} `;
            }
            this.$nextTick(() => {
                const el = document.querySelector(`input[data-comment-input="${postNo}"]`);
                if (el) {
                    el.focus();
                    el.setSelectionRange(el.value.length, el.value.length);
                }
            });
        },

        cancelReply(postNo) {
            this.replyingTo[postNo] = null;
        },

        handleCommentInput(postNo, event) {
            const input = event.target;
            const text = input.value || '';
            const cursorPos = input.selectionEnd ?? text.length;
            const textBeforeCursor = text.slice(0, cursorPos);

            const match = textBeforeCursor.match(/(?:^|\s)@([^\s@]*)$/);
            if (!match) {
                this.mentionOpen[postNo] = false;
                this.mentionMatches[postNo] = [];
                return;
            }

            const query = (match[1] || '').toLowerCase();
            const currentUserId = String(this.currentUserId || '');

            const matches = (this.familyRoster || []).filter(m => {
                const first = (m.firstName || '').trim();
                const last = (m.lastName || '').trim();
                if (!first && !last) return false;
                if (String(m.id) === currentUserId) return false;
                const full = `${first} ${last}`.toLowerCase();
                if (!query) return true;
                return full.includes(query) || first.toLowerCase().startsWith(query) || last.toLowerCase().startsWith(query);
            }).slice(0, 6);

            this.mentionMatches[postNo] = matches;
            this.mentionIndex[postNo] = 0;
            this.mentionOpen[postNo] = matches.length > 0;
        },

        handleCommentKeydown(postNo, event) {
            if (!this.mentionOpen[postNo] || !(this.mentionMatches[postNo] || []).length) return;

            const matches = this.mentionMatches[postNo];
            let idx = this.mentionIndex[postNo] || 0;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.mentionIndex[postNo] = (idx + 1) % matches.length;
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.mentionIndex[postNo] = (idx - 1 + matches.length) % matches.length;
            } else if (event.key === 'Enter' || event.key === 'Tab') {
                if (matches[idx]) {
                    event.preventDefault();
                    this.selectMention(postNo, matches[idx]);
                }
            } else if (event.key === 'Escape') {
                this.mentionOpen[postNo] = false;
            }
        },

        selectMention(postNo, member) {
            if (!member) return;
            const text = this.commentInputs[postNo] || '';
            const replaced = text.replace(/(?:^|\s)@([^\s@]*)$/, (match) => {
                const prefix = match.startsWith(' ') ? ' ' : '';
                return `${prefix}@${member.firstName} ${member.lastName} `;
            });
            this.commentInputs[postNo] = replaced;
            this.mentionOpen[postNo] = false;
            this.mentionMatches[postNo] = [];

            this.$nextTick(() => {
                const el = document.querySelector(`input[data-comment-input="${postNo}"]`);
                if (el) {
                    el.focus();
                    el.setSelectionRange(el.value.length, el.value.length);
                }
            });
        },

        async submitComment(postNo, ev = null) {
            const typedText = (this.commentInputs[postNo] || '').trim();
            const stickerToken = this.commentStickers[postNo]?.token || '';
            const commentText = [typedText, stickerToken].filter(Boolean).join(' ');
            const rawGif = this.commentGifs[postNo] || '';
            const attachedFile = this.commentFiles ? this.commentFiles[postNo] : null;
            const isBlob = typeof rawGif === 'string' && rawGif.startsWith('blob:');
            const gifUrl = (!isBlob && isAllowedGifUrl(rawGif)) ? rawGif : '';
            if (!commentText && !gifUrl && !attachedFile) return;

            const formData = new FormData();
            formData.append('post_no', postNo);
            formData.append('comment', commentText);
            if (attachedFile) {
                formData.append('comment_media', attachedFile);
            } else if (gifUrl) {
                formData.append('gif_url', gifUrl);
            }
            const replyTo = this.replyingTo[postNo]?.comment_no;
            if (replyTo) formData.append('parent_comment_no', String(replyTo));

            try {
                const response = await axios.post('/postCommentProfile', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                if (response?.data?.status === 'success' || response?.status === 200) {
                    const respData = response?.data?.message;
                    const commentNo = (respData && typeof respData === 'object' && respData.comment_no)
                        ? respData.comment_no
                        : (typeof respData === 'number' || (typeof respData === 'string' && /^\d+$/.test(respData)))
                            ? parseInt(respData, 10)
                            : Date.now();

                    const post = this.posts.find(p => String(p.post_no) === String(postNo));
                    if (post) {
                        if (!Array.isArray(post.comments)) {
                            post.comments = [];
                        }
                        if (!post.comments.some(c => String(c.comment_no) === String(commentNo))) {
                            const newComment = this.normalizeComment({
                                comment_no: commentNo,
                                post_no: postNo,
                                parent_comment_no: (respData && typeof respData === 'object' && respData.parent_comment_no) || null,
                                id: this.currentUserId,
                                fullName: (respData && typeof respData === 'object' && respData.fullName) || 'You',
                                profileImg: (respData && typeof respData === 'object' && respData.profileImg) || '',
                                comment: commentText,
                                gif_url: (respData && typeof respData === 'object' && respData.gif_url) || (isBlob ? '' : gifUrl),
                                post_time: (respData && typeof respData === 'object' && respData.post_time) || Date.now().toString(),
                                comment_time: (respData && typeof respData === 'object' && respData.comment_time) || Date.now().toString(),
                                date_created: (respData && typeof respData === 'object' && respData.date_created) || new Date().toISOString(),
                                reactions: {},
                                user_reaction: null
                            });
                            post.comments.push(newComment);
                        }
                    }

                    if (replyTo) {
                        const root = this.getThreadRoot(post, { comment_no: replyTo, parent_comment_no: replyTo });
                        if (root?.comment_no) {
                            this.threadExpanded[String(root.comment_no)] = true;
                        }
                    }

                    this.commentInputs[postNo] = '';
                    this.clearCommentSticker(postNo);
                    this.clearCommentGif(postNo);
                    this.commentGifOpen[postNo] = false;
                    this.mentionOpen[postNo] = false;
                    this.mentionMatches[postNo] = [];
                    this.cancelReply(postNo);
                }
            } catch (err) {
                console.error('Failed to submit comment:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: err?.response?.data?.message || 'Failed to submit comment.',
                    confirmButtonColor: '#a31621'
                });
            } finally {
                // The global submit spinner locks the send button for 15s unless told otherwise,
                // which would block an immediate follow-up reply.
                const spinner = window.FamilyPlatformSpinner;
                if (ev?.target && spinner?.findSubmitButton) {
                    spinner.stop(spinner.findSubmitButton(ev.target, ev.submitter));
                }
            }
        },

        // ── Post edit/delete (author only) ──────────────────────────────────

        isOwnPost(post) {
            return String(post?.id) === String(this.currentUserId);
        },

        isCurrentUser(userId) {
            return String(userId) === String(this.currentUserId || '');
        },

        isUserTaggedInPost(post) {
            if (!post || !Array.isArray(post.post_tags) || !this.currentUserId) return false;
            return post.post_tags.some(t => String(t.user_id) === String(this.currentUserId));
        },

        getSortedTagsForViewer(post) {
            if (!post || !Array.isArray(post.post_tags)) return [];
            const uid = String(this.currentUserId || '');
            const hasViewer = post.post_tags.some(t => String(t.user_id) === uid);
            if (!hasViewer) return post.post_tags;
            return [...post.post_tags].sort((a, b) => {
                if (String(a.user_id) === uid) return -1;
                if (String(b.user_id) === uid) return 1;
                return 0;
            });
        },

        get taggedPostsCount() {
            if (!Array.isArray(this.posts)) return 0;
            return this.posts.filter(p => this.isUserTaggedInPost(p)).length;
        },

        async untagSelfFromPost(post) {
            if (!post || !post.post_no || !this.currentUserId) return;
            const confirm = await Swal.fire({
                title: 'Remove Tag?',
                text: 'Do you want to remove your tag from this family post?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#a31621',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Remove Tag'
            });
            if (!confirm.isConfirmed) return;

            try {
                await axios.delete(`/api/posts/${post.post_no}/tags/${this.currentUserId}`, this.csrfOptions);
                post.post_tags = (post.post_tags || []).filter(t => String(t.user_id) !== String(this.currentUserId));
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Tag removed from post',
                    showConfirmButton: false,
                    timer: 2500
                });
            } catch (err) {
                console.error('Failed to remove tag:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Action Failed',
                    text: 'Unable to remove tag. Please try again.',
                    confirmButtonColor: '#a31621'
                });
            }
        },

        // Opens the existing "Create Post" modal in edit mode: prefills the text,
        // stamps a hidden post_no the modal's own submit handler (allEvents.js)
        // checks to decide between POST (create) and PUT (update).
        editPost(post) {
            const postNoInput = document.getElementById('editPostNo');
            const textarea = document.getElementById('postMessage');
            const notice = document.getElementById('editPostNotice');
            if (!postNoInput || !textarea) return;

            const currentStyle = post?.post_style || this.extractPostStyle(post?.postMessage, post?.post_style) || '';
            const styleInput = document.getElementById('composerPostStyleInput');
            if (styleInput) {
                styleInput.value = currentStyle;
            }
            if (typeof window.__setComposerTextStage === 'function') {
                window.__setComposerTextStage(currentStyle);
            }

            postNoInput.value = post.post_no;
            textarea.value = this.stripStyleToken(this.decodeHtml(post.postMessage || ''));
            if (notice) notice.classList.remove('d-none');

            const modalTitle = document.getElementById('postModalLabel');
            if (modalTitle) modalTitle.textContent = 'Edit Post';

            const modalEl = document.getElementById('postModal');
            const instance = window.bootstrap?.Modal?.getOrCreateInstance(modalEl);
            instance?.show();
        },

        // Reposts one of the user's own earlier posts, with an optional reflection.
        async repostPost(post) {
            const result = await Swal.fire({
                title: 'Repost this memory?',
                text: 'It will appear again at the top of your family feed.',
                input: 'textarea',
                inputPlaceholder: 'Add your thoughts or reflection (optional)',
                showCancelButton: true,
                confirmButtonColor: '#a31621',
                confirmButtonText: 'Repost',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (note) => {
                    try {
                        const res = await axios.post(
                            `/post/${post.post_no}/repost`,
                            { reflection_message: (note || '').trim() },
                            this.csrfOptions
                        );
                        return res.data;
                    } catch (err) {
                        Swal.showValidationMessage(err?.response?.data?.message || 'Unable to repost. Please try again.');
                        return false;
                    }
                }
            });
            if (!result.isConfirmed || !result.value) return;
            Swal.fire({ icon: 'success', title: 'Reposted', timer: 1500, showConfirmButton: false });
        },

        async deletePost(postNo) {
            const result = await Swal.fire({
                title: 'Delete this post?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#a31621',
                confirmButtonText: 'Yes, delete it!'
            });
            if (!result.isConfirmed) return;

            try {
                await axios.delete(`/post/${postNo}`, this.csrfOptions);
                this.posts = this.posts.filter(p => String(p.post_no) !== String(postNo));
            } catch (err) {
                console.error('Failed to delete post:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: err?.response?.data?.message || 'Failed to delete post.',
                    confirmButtonColor: '#a31621'
                });
            }
        },

        // ── Comment edit/delete (comment author or post author) ─────────────

        canEditComment(comment) {
            return String(comment?.id) === String(this.currentUserId);
        },

        canModerateComment(post, comment) {
            return this.canEditComment(comment) || String(post?.id) === String(this.currentUserId);
        },

        startEditComment(comment) {
            this.editingCommentNo = comment.comment_no;
            this.editCommentText = this.decodeHtml(comment.comment || '');
        },

        cancelEditComment() {
            this.editingCommentNo = null;
            this.editCommentText = '';
        },

        async saveCommentEdit(commentNo) {
            const text = (this.editCommentText || '').trim();
            if (!text) return;
            const normalizedText = this.normalizeEmojiText(text);

            try {
                await axios.put(`/comment/${commentNo}`, { comment: normalizedText }, this.csrfOptions);
                for (const post of this.posts) {
                    const comment = post.comments.find(c => String(c.comment_no) === String(commentNo));
                    if (comment) {
                        comment.comment = normalizedText;
                        break;
                    }
                }
                this.cancelEditComment();
            } catch (err) {
                console.error('Failed to update comment:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: err?.response?.data?.message || 'Failed to update comment.',
                    confirmButtonColor: '#a31621'
                });
            }
        },

        async deleteComment(postNo, commentNo) {
            const result = await Swal.fire({
                title: 'Delete this comment?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#a31621',
                confirmButtonText: 'Yes, delete it!'
            });
            if (!result.isConfirmed) return;

            try {
                await axios.delete(`/comment/${commentNo}`, this.csrfOptions);
                const post = this.posts.find(p => String(p.post_no) === String(postNo));
                if (post) {
                    post.comments = post.comments.filter(c => String(c.comment_no) !== String(commentNo) && String(c.parent_comment_no || '') !== String(commentNo));
                }
            } catch (err) {
                console.error('Failed to delete comment:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: err?.response?.data?.message || 'Failed to delete comment.',
                    confirmButtonColor: '#a31621'
                });
            }
        },

        async likePost(postNo) {
            const post = this.posts.find(p => p.post_no === postNo);
            if (!post) return;

            post.isLiked = !post.isLiked;
            post.post_likes += post.isLiked ? 1 : -1;

            try {
                await axios.put('/profileCard/postLikes?postNo=' + postNo);
            } catch (err) {
                console.error('Failed to like post:', err);
                // revert on error
                post.isLiked = !post.isLiked;
                post.post_likes += post.isLiked ? 1 : -1;
            }
        },

        // ── Post-level: toggle the floating emoji reaction bar ─────────────
        togglePostReactionBar(postNo, show) {
            // Use a small delay on hide to allow the user to move into the bar
            if (show) {
                clearTimeout(this._postBarTimer);
                this.activeReactionBars[postNo] = true;
            } else {
                this._postBarTimer = setTimeout(() => {
                    this.activeReactionBars[postNo] = false;
                }, 350);
            }
        },

        keepPostReactionBar(postNo, keep) {
            if (keep) {
                clearTimeout(this._postBarTimer);
                this.activeReactionBars[postNo] = true;
            } else {
                this.activeReactionBars[postNo] = false;
            }
        },

        // ── Comment-level: toggle the floating emoji reaction bar ───────────
        toggleCommentReactionBar(commentNo, show) {
            if (show) {
                clearTimeout(this._commentBarTimer);
                this.activeCommentReactionBars[commentNo] = true;
            } else {
                this._commentBarTimer = setTimeout(() => {
                    this.activeCommentReactionBars[commentNo] = false;
                }, 350);
            }
        },

        keepCommentReactionBar(commentNo, keep) {
            if (keep) {
                clearTimeout(this._commentBarTimer);
                this.activeCommentReactionBars[commentNo] = true;
            } else {
                this.activeCommentReactionBars[commentNo] = false;
            }
        },

        // ── Touch support: long-press reveals the reaction bar on mobile,
        // with touch-scrubbing across gems (iOS/Facebook parity) ─────────
        startPostLongPress(postNo, ev) {
            this._postLongPressFired = false;
            this._postActiveHoveredReaction = null;
            if (ev?.touches?.[0]) {
                this._postTouchStartX = ev.touches[0].clientX;
                this._postTouchStartY = ev.touches[0].clientY;
            }
            clearTimeout(this._postLongPressTimer);
            this._postLongPressTimer = setTimeout(() => {
                this._postLongPressFired = true;
                this.activeReactionBars[postNo] = true;
                if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
            }, 350);
        },

        handlePostTouchMove(postNo, ev) {
            if (!ev?.touches?.[0]) return;
            const touch = ev.touches[0];
            const isBarOpen = !!this.activeReactionBars[postNo];

            if (!isBarOpen) {
                const dx = Math.abs(touch.clientX - (this._postTouchStartX || touch.clientX));
                const dy = Math.abs(touch.clientY - (this._postTouchStartY || touch.clientY));
                if (dx > 10 || dy > 10) {
                    this.cancelPostLongPress();
                }
                return;
            }

            if (ev.cancelable) ev.preventDefault();

            const targetEl = document.elementFromPoint(touch.clientX, touch.clientY);
            const btn = targetEl?.closest?.('.heirloom-reaction-btn');
            const dock = document.querySelector(`.reaction-bar-float[data-post-bar="${postNo}"]`) ||
                         targetEl?.closest?.('.reaction-bar-float');

            if (btn && btn.dataset?.reactionType) {
                const reactionType = btn.dataset.reactionType;
                if (this._postActiveHoveredReaction !== reactionType) {
                    this._postActiveHoveredReaction = reactionType;
                    if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
                }
                if (dock) {
                    dock.querySelectorAll('.heirloom-reaction-btn').forEach(b => {
                        b.classList.toggle('touch-active', b === btn);
                    });
                }
            } else {
                this._postActiveHoveredReaction = null;
                if (dock) {
                    dock.querySelectorAll('.heirloom-reaction-btn').forEach(b => b.classList.remove('touch-active'));
                }
            }
        },

        endPostTouch(postNo, ev) {
            this.cancelPostLongPress();
            const dock = document.querySelector(`.reaction-bar-float[data-post-bar="${postNo}"]`);
            if (dock) {
                dock.querySelectorAll('.heirloom-reaction-btn').forEach(b => b.classList.remove('touch-active'));
            }

            if (this._postLongPressFired && this._postActiveHoveredReaction) {
                const chosen = this._postActiveHoveredReaction;
                this._postActiveHoveredReaction = null;
                this._postLongPressFired = false;
                this.reactToPost(postNo, chosen, ev);
            }
        },

        cancelPostLongPress() {
            clearTimeout(this._postLongPressTimer);
        },

        // Plain tap likes the post; a long-press that already opened the
        // bar suppresses the tap so it doesn't also toggle the like.
        onPostLikeClick(postNo, reactionType, ev = null) {
            if (this._postLongPressFired) {
                this._postLongPressFired = false;
                return;
            }
            this.reactToPost(postNo, reactionType, ev);
        },

        startCommentLongPress(commentNo, ev) {
            this._commentLongPressFired = false;
            this._commentActiveHoveredReaction = null;
            if (ev?.touches?.[0]) {
                this._commentTouchStartX = ev.touches[0].clientX;
                this._commentTouchStartY = ev.touches[0].clientY;
            }
            clearTimeout(this._commentLongPressTimer);
            this._commentLongPressTimer = setTimeout(() => {
                this._commentLongPressFired = true;
                this.activeCommentReactionBars[commentNo] = true;
                if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
            }, 350);
        },

        handleCommentTouchMove(commentNo, ev) {
            if (!ev?.touches?.[0]) return;
            const touch = ev.touches[0];
            const isBarOpen = !!this.activeCommentReactionBars[commentNo];

            if (!isBarOpen) {
                const dx = Math.abs(touch.clientX - (this._commentTouchStartX || touch.clientX));
                const dy = Math.abs(touch.clientY - (this._commentTouchStartY || touch.clientY));
                if (dx > 10 || dy > 10) {
                    this.cancelCommentLongPress();
                }
                return;
            }

            if (ev.cancelable) ev.preventDefault();

            const targetEl = document.elementFromPoint(touch.clientX, touch.clientY);
            const btn = targetEl?.closest?.('.heirloom-reaction-btn');
            const dock = document.querySelector(`.comment-reaction-float-bar[data-comment-bar="${commentNo}"]`) ||
                         targetEl?.closest?.('.comment-reaction-float-bar');

            if (btn && btn.dataset?.reactionType) {
                const reactionType = btn.dataset.reactionType;
                if (this._commentActiveHoveredReaction !== reactionType) {
                    this._commentActiveHoveredReaction = reactionType;
                    if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
                }
                if (dock) {
                    dock.querySelectorAll('.heirloom-reaction-btn').forEach(b => {
                        b.classList.toggle('touch-active', b === btn);
                    });
                }
            } else {
                this._commentActiveHoveredReaction = null;
                if (dock) {
                    dock.querySelectorAll('.heirloom-reaction-btn').forEach(b => b.classList.remove('touch-active'));
                }
            }
        },

        endCommentTouch(postNo, commentNo, ev) {
            this.cancelCommentLongPress();
            const dock = document.querySelector(`.comment-reaction-float-bar[data-comment-bar="${commentNo}"]`);
            if (dock) {
                dock.querySelectorAll('.heirloom-reaction-btn').forEach(b => b.classList.remove('touch-active'));
            }

            if (this._commentLongPressFired && this._commentActiveHoveredReaction) {
                const chosen = this._commentActiveHoveredReaction;
                this._commentActiveHoveredReaction = null;
                this._commentLongPressFired = false;
                this.reactToComment(postNo, commentNo, chosen, ev);
            }
        },

        cancelCommentLongPress() {
            clearTimeout(this._commentLongPressTimer);
        },

        onCommentLikeClick(postNo, commentNo, reactionType, ev = null) {
            if (this._commentLongPressFired) {
                this._commentLongPressFired = false;
                return;
            }
            // Marcus Adversarial Hardening: 300ms rapid-tap debounce per comment
            const now = Date.now();
            this._commentReactDebounce = this._commentReactDebounce || {};
            if (now - (this._commentReactDebounce[commentNo] || 0) < 300) {
                return;
            }
            this._commentReactDebounce[commentNo] = now;
            if (typeof window.triggerHaptic === 'function') window.triggerHaptic('impact');

            const post = this.posts.find(p => String(p.post_no) === String(postNo));
            const comment = post?.comments?.find(c => String(c.comment_no) === String(commentNo));
            // If already reacted, toggle off with current reaction; if unreacted, default to 'like' (Topaz Thumb)
            const targetType = comment?.userReaction || 'like';
            this.reactToComment(postNo, commentNo, targetType, ev);
        },

        // ── Comment emoji picker for text input ─────────────────────────────
        toggleCommentEmoji(postNo) {
            this.commentGifOpen[postNo] = false;
            this.commentEmojiOpen[postNo] = !this.commentEmojiOpen[postNo];
        },

        // ── Comment GIF picker (same curated catalog as the post composer) ──
        toggleCommentGif(postNo) {
            this.commentEmojiOpen[postNo] = false;
            this.commentGifOpen[postNo] = !this.commentGifOpen[postNo];
        },

        filteredCommentGifs() {
            return FAMILY_GIFS.filter(g => this.commentGifCategory === 'all' || g.cat === this.commentGifCategory);
        },

        selectCommentGif(postNo, url) {
            if (!isAllowedGifUrl(url)) return;
            this.commentGifs[postNo] = url;
            this.commentGifOpen[postNo] = false;
        },

        clearCommentGif(postNo) {
            const current = this.commentGifs[postNo];
            if (current && typeof current === 'string' && current.startsWith('blob:')) {
                try { URL.revokeObjectURL(current); } catch (_) {}
            }
            this.commentGifs[postNo] = '';
            if (this.commentFiles) {
                this.commentFiles[postNo] = null;
            }
        },

        handleCommentPaste(postNo, ev) {
            const clipboard = ev.clipboardData || window.clipboardData;
            if (!clipboard) return;

            // 1. Binary image / sticker / GIF from mobile keyboard (Gboard, iOS, Memoji)
            const items = clipboard.items;
            if (items && items.length) {
                for (let i = 0; i < items.length; i++) {
                    const item = items[i];
                    if (item.kind === 'file' && item.type.startsWith('image/')) {
                        const blob = item.getAsFile();
                        if (blob) {
                            ev.preventDefault();
                            const ext = item.type.split('/')[1] || 'png';
                            const file = new File([blob], `sticker_${Date.now()}.${ext}`, { type: item.type });
                            if (!this.commentFiles) this.commentFiles = {};
                            this.commentFiles[postNo] = file;
                            const objectUrl = URL.createObjectURL(blob);
                            this.commentGifs[postNo] = objectUrl;
                            this.commentGifOpen[postNo] = false;
                            return;
                        }
                    }
                }
            }

            // 2. GIF / image URL pasted as plain text
            const text = clipboard.getData('text/plain');
            if (text && isAllowedGifUrl(text.trim())) {
                const url = text.trim();
                if (/\.(gif|webp)(\?|$)/i.test(url) || /giphy\.com|tenor\.com/.test(url)) {
                    ev.preventDefault();
                    this.commentGifs[postNo] = url;
                    this.commentGifOpen[postNo] = false;
                }
            }
        },

        insertEmojiIntoComment(postNo, emoji) {
            const current = this.commentInputs[postNo] || '';
            this.commentInputs[postNo] = current + emoji;
        },

        // ── Living (3D) emojis: catalog from window.ATG_LIVING_EMOJIS (profileBase) ──
        livingEmojiIdSet() {
            if (this._livingIdSet) return this._livingIdSet;
            const cfg = window.ATG_LIVING_EMOJIS;
            if (!cfg || !Array.isArray(cfg.catalog)) return null; // unknown: accept any well-formed token
            const set = new Set();
            const toneable = new Set(cfg.toneable || []);
            cfg.catalog.forEach(e => {
                set.add(e.id);
                if (toneable.has(e.id)) {
                    (cfg.tones || []).forEach(t => set.add(e.id.replace(/_3d$/, `_${t}_3d`)));
                }
            });
            this._livingIdSet = set;
            return set;
        },

        get commentLivingPalette() {
            const cfg = window.ATG_LIVING_EMOJIS;
            if (!cfg || !Array.isArray(cfg.catalog)) return [];
            // SKIN_TONES index 1..5 maps onto the living emoji tone keys in order.
            const toneKey = (cfg.tones || [])[(this.commentSkinTone || 0) - 1] || null;
            const toneable = new Set(cfg.toneable || []);
            return cfg.catalog.map(e => ({
                id: (toneKey && toneable.has(e.id)) ? e.id.replace(/_3d$/, `_${toneKey}_3d`) : e.id,
                title: e.title,
            }));
        },

        // ── Me-stickers in comments (RFC-0011) ──
        meStickers: null,        // null = not loaded; { available, stickers[] } once fetched
        meStickersError: false,

        openMeStickerTab() {
            this.commentEmojiTab = 'me';
            if (this.meStickers || !window.AtgEmojiEngine || !window.AtgEmojiEngine.getMeStickers) return;
            this.meStickersError = false;
            window.AtgEmojiEngine.getMeStickers()
                .then(d => { this.meStickers = d || { available: false }; })
                .catch(() => { this.meStickersError = true; });
        },

        // A chosen sticker shows as a preview chip in the comment field (like a GIF), never
        // as its raw `:me_…@id:` token; the token is appended to the text on submit.
        commentStickers: {},

        insertMeStickerIntoComment(postNo, token, url = '', label = '') {
            if (!/^:me_[a-z]+@[A-Za-z0-9_-]{1,64}:$/.test(token)) return;
            this.commentStickers[postNo] = { token, url: String(url || ''), label: String(label || '') };
            this.commentEmojiOpen[postNo] = false;
        },

        clearCommentSticker(postNo) {
            delete this.commentStickers[postNo];
        },

        insertLivingEmojiIntoComment(postNo, emojiId) {
            if (!/^[a-z0-9_]+_3d$/.test(emojiId)) return;
            const current = this.commentInputs[postNo] || '';
            const sep = current && !/\s$/.test(current) ? ' ' : '';
            this.commentInputs[postNo] = `${current}${sep}:${emojiId}: `;
            this.commentEmojiOpen[postNo] = false;
        },

        // ── React to a comment with an emoji ───────────────────────────────
        async reactToComment(postNo, commentNo, reactionType, ev = null) {
            const post = this.posts.find(p => String(p.post_no) === String(postNo));
            if (!post) return;
            const comment = post.comments.find(c => String(c.comment_no) === String(commentNo));
            if (!comment) return;

            // Normalize type
            let cleanType = String(reactionType || 'like').toLowerCase();
            if (cleanType === 'likes') cleanType = 'like';
            if (cleanType === 'shock') cleanType = 'wow';

            // Optimistic UI — update local state immediately
            const prevReactions = { ...(comment.reactions || {}) };
            const prevTotal = Number(comment.totalReactions || 0);

            const currentReaction = comment.userReaction === 'shock' ? 'wow' : (comment.userReaction === 'likes' ? 'like' : comment.userReaction);
            const isSame = currentReaction === cleanType;
            const wasReaction = comment.userReaction;

            const targetKey = cleanType === 'like' ? 'likes' : cleanType;
            const wasKey = (currentReaction === 'like' || currentReaction === 'likes') ? 'likes' : currentReaction;

            const nextReactions = { ...(comment.reactions || {}) };
            if (isSame) {
                // Toggle off
                nextReactions[targetKey] = Math.max(0, Number(nextReactions[targetKey] || 1) - 1);
                comment.totalReactions = Math.max(0, prevTotal - 1);
                comment.userReaction = null;
            } else {
                // Remove old reaction from counts if switching
                if (currentReaction && wasKey && Number(nextReactions[wasKey]) > 0) {
                    nextReactions[wasKey] = Math.max(0, Number(nextReactions[wasKey]) - 1);
                    comment.totalReactions = Math.max(0, prevTotal - 1);
                }
                nextReactions[targetKey] = Number(nextReactions[targetKey] || 0) + 1;
                comment.totalReactions = Number(comment.totalReactions || 0) + 1;
                comment.userReaction = cleanType;
                triggerHeirloomBurst(cleanType, ev || (typeof window !== 'undefined' ? window.event : null));
            }
            comment.reactions = nextReactions;

            // Hide the reaction bar after clicking
            this.activeCommentReactionBars[commentNo] = false;

            try {
                const token = getCsrfToken();
                const formData = new FormData();
                formData.append('comment_no', commentNo);
                formData.append('reaction', cleanType);
                if (token) {
                    formData.append('token', token);
                }

                const res = await axios.post('/api/reactions/add', formData, {
                    headers: {
                        ...(token ? { 'X-XSRF-TOKEN': token, 'X-CSRF-TOKEN': token } : {}),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Sync server counts if returned
                const serverCounts = res?.data?.message?.counts?.counts || res?.data?.message?.counts;
                if (serverCounts && typeof serverCounts === 'object') {
                    comment.reactions = { ...serverCounts };
                    comment.totalReactions = Number(serverCounts?.totalReactions ?? comment.totalReactions);
                }
                const serverAction = res?.data?.message?.action;
                if (serverAction === 'removed') {
                    comment.userReaction = null;
                } else if (serverAction === 'added' || serverAction === 'updated') {
                    comment.userReaction = cleanType;
                }
            } catch (err) {
                // Revert optimistic update on failure
                comment.reactions = prevReactions;
                comment.totalReactions = prevTotal;
                comment.userReaction = wasReaction;
                console.error('Comment reaction failed:', err);
            }
        },

        // ── Get top 3 comment reactions for display ─────────────────────────
        getTopCommentReactions(reactions) {
            if (!reactions || typeof reactions !== 'object') return [];
            const map = this.commentEmojiMap;
            return Object.entries(reactions)
                .filter(([k, v]) => !['comment_no', 'total', 'totalReactions'].includes(k) && Number(v) > 0 && !(k === 'like' && reactions.likes !== undefined))
                .sort(([, a], [, b]) => Number(b) - Number(a))
                .slice(0, 3)
                .map(([label, count]) => ({
                    label: label,
                    type: label,
                    emoji: map[label] ?? '👍',
                    count: Number(count)
                }));
        },

        // ── React to a post with an emoji (post-level reactions) ────────────
        async reactToPost(postNo, reactionType, ev = null) {
            const post = this.posts.find(p => String(p.post_no) === String(postNo));
            if (!post) return;

            // Marcus Adversarial Hardening: 300ms rapid-tap debounce per post
            const now = Date.now();
            this._postReactDebounce = this._postReactDebounce || {};
            if (now - (this._postReactDebounce[postNo] || 0) < 300) {
                return;
            }
            this._postReactDebounce[postNo] = now;
            if (typeof window.triggerHaptic === 'function') window.triggerHaptic('impact');

            const previousReaction = post.user_reaction;
            post.user_reaction = (post.user_reaction === reactionType) ? null : reactionType;

            if (post.user_reaction) {
                triggerHeirloomBurst(post.user_reaction, ev || (typeof window !== 'undefined' ? window.event : null));
            }

            // Optimistic like counter bump
            if (previousReaction === null) post.post_likes += 1;
            else if (post.user_reaction === null) post.post_likes = Math.max(0, post.post_likes - 1);

            // Close the reaction bar
            this.activeReactionBars[postNo] = false;

            try {
                const formData = new FormData();
                formData.append('post_no', postNo);
                formData.append('reaction', reactionType);
                await axios.put('/profileCard/postLikes?postNo=' + encodeURIComponent(postNo) + '&reaction=' + encodeURIComponent(reactionType));
            } catch (err) {
                console.error('Failed to record post reaction:', err);
                post.user_reaction = previousReaction;
                // Revert like counter
                if (previousReaction === null) post.post_likes = Math.max(0, post.post_likes - 1);
                else if (post.user_reaction === null) post.post_likes += 1;
            }
        },


        isOptionVoted(post, optionId) {
            if (!post?.poll?.user_voted_option_id) return false;
            const list = Array.isArray(post.poll.user_voted_option_id)
                ? post.poll.user_voted_option_id
                : [post.poll.user_voted_option_id];
            return list.some(v => String(v) === String(optionId));
        },

        isVotingPoll(postNo) {
            return !!this.votingPolls[postNo];
        },

        async votePoll(postNo, optionId) {
            const post = this.posts.find(p => String(p.post_no) === String(postNo));
            if (!post?.poll || !Array.isArray(post.poll.options)) return;
            if (this.votingPolls[postNo]) return;

            this.votingPolls[postNo] = true;

            // Snapshot for rollback
            const snapshot = JSON.parse(JSON.stringify(post.poll));

            // ── Optimistic update ──────────────────────────────────────────
            const voted = Array.isArray(post.poll.user_voted_option_id)
                ? [...post.poll.user_voted_option_id]
                : (post.poll.user_voted_option_id ? [post.poll.user_voted_option_id] : []);
            const already = voted.some(v => String(v) === String(optionId));

            post.poll.options = post.poll.options.map(opt => {
                if (String(opt.option_id) === String(optionId)) {
                    const count = Number(opt.vote_count || 0) + (already ? -1 : 1);
                    return { ...opt, vote_count: Math.max(0, count) };
                }
                return { ...opt };
            });

            post.poll.user_voted_option_id = already
                ? voted.filter(v => String(v) !== String(optionId))
                : [...voted, optionId];

            const total = post.poll.options.reduce((sum, o) => sum + Number(o.vote_count || 0), 0);
            post.poll.total_votes = total;
            post.poll.options = post.poll.options.map(o => ({
                ...o,
                percentage: total > 0 ? Math.round((Number(o.vote_count || 0) / total) * 100) : 0,
            }));

            // ── Persist ────────────────────────────────────────────────────
            try {
                const token = getCsrfToken();
                const formData = new FormData();
                formData.append('post_no', postNo);
                formData.append('option_id', optionId);
                if (token) {
                    formData.append('token', token);
                }

                const response = await axios.post('/api/poll/vote', formData, {
                    headers: {
                        ...(token ? { 'X-XSRF-TOKEN': token } : {}),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                // Prefer authoritative server state when returned
                if (response?.data?.poll) {
                    post.poll = response.data.poll;
                } else if (response?.data?.status !== 'success') {
                    throw new Error(response?.data?.message || 'Vote was not recorded');
                }
            } catch (err) {
                console.error('Failed to vote on poll:', err);
                post.poll = snapshot; // rollback
            } finally {
                this.votingPolls[postNo] = false;
            }
        },

        // ── Two Truths & a Lie (play directly from the feed) ──────────────
        isVotingTwoTruths(postNo) {
            return !!this.votingTwoTruths[postNo];
        },

        async voteTwoTruths(post, guess) {
            const game = post?.two_truths;
            if (!game || game.has_voted || game.is_author || game.status !== 'active') return;
            if (this.votingTwoTruths[post.post_no]) return;

            this.votingTwoTruths[post.post_no] = true;
            const snapshot = JSON.parse(JSON.stringify(game));
            // Optimistic lock-in so the tap feels instant
            game.has_voted = true;
            game.user_vote = { guessed_lie_index: guess };

            try {
                const token = getCsrfToken();
                const response = await axios.post('/api/games/two-truths/vote', {
                    game_id: game.id,
                    guess,
                    token,
                    _token: token,
                }, {
                    headers: {
                        ...(token ? { 'X-CSRF-TOKEN': token, 'X-XSRF-TOKEN': token } : {}),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = response?.data || {};
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Guess was not recorded');
                }
                game.total_votes = data.total_votes ?? (Number(game.total_votes || 0) + 1);
                game.vote_percentages = data.vote_percentages || null;
                game.vote_counts = data.vote_counts || null;
                game.just_voted = true;
                this.pulseHaptic?.();
            } catch (err) {
                const msg = err?.response?.data?.message || err.message || 'Could not record your guess.';
                Object.keys(game).forEach(k => delete game[k]);
                Object.assign(game, snapshot);
                game.error_message = msg;
                console.error('Failed to vote on Two Truths:', err);
            } finally {
                this.votingTwoTruths[post.post_no] = false;
            }
        },

        twoTruthsPercent(game, idx) {
            return Number(game?.vote_percentages?.[idx] ?? 0);
        },

        initEventListeners() {
            window.addEventListener('post-created', (event) => {
                const newPostData = event?.detail;
                if (newPostData && typeof newPostData === 'object') {
                    // Reset active stream filters so the user immediately sees their newly posted post
                    if (this.activeStreamFilter && this.activeStreamFilter !== 'all') {
                        this.activeStreamFilter = 'all';
                    }
                    if (this.activeHashtag) {
                        this.activeHashtag = '';
                    }

                    const normalized = this.normalizePost(newPostData);
                    const existing = this.posts.find(p => String(p.post_no) === String(normalized.post_no));
                    if (!existing) {
                        this.posts.unshift(normalized);
                        this.observeFeedVideos();
                    } else if (normalized.comparison && !existing.comparison) {
                        // The realtime broadcast can land first, without the Then & Now pair.
                        existing.comparison = normalized.comparison;
                    }

                    // Smoothly scroll and navigate directly to the newly posted post in the feed
                    const postNo = normalized.post_no;
                    if (postNo) {
                        this.$nextTick(() => {
                            let attempts = 0;
                            const interval = setInterval(() => {
                                attempts++;
                                const target = document.getElementById('post' + postNo);
                                if (target) {
                                    clearInterval(interval);
                                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    target.classList.add('highlighted-post');
                                    setTimeout(() => target.classList.remove('highlighted-post'), 2500);
                                } else if (attempts >= 25) {
                                    clearInterval(interval);
                                    window.scrollTo({ top: 0, behavior: 'smooth' });
                                }
                            }, 50);
                        });
                    }
                } else {
                    this.fetchPosts().then(() => {
                        this.scrollToHashPost();
                    });
                }
            });

            // Dispatched by allEvents.js after a successful post edit (the acting
            // user's own tab — other tabs get it via the update-post Pusher bind).
            window.addEventListener('post-updated', (event) => {
                const { postNo, postMessage, post_style, link_preview } = event?.detail || {};
                const post = this.posts.find(p => String(p.post_no) === String(postNo));
                if (post) {
                    const effectiveStyle = post_style !== undefined
                        ? this.extractPostStyle(postMessage, post_style === 'none' ? null : post_style)
                        : this.extractPostStyle(postMessage, post.post_style);
                    post.post_style = effectiveStyle;
                    const stripped = this.stripStyleToken(postMessage ?? post.postMessage ?? '');
                    post.postMessage = effectiveStyle ? `[style:${effectiveStyle}] ${stripped}` : stripped;
                    post.videos = extractVideosFromText(post.postMessage || '', 3);
                    post.video = post.videos.length > 0 ? post.videos[0] : null;
                    post.displayMessage = this.normalizeEmojiText(cleanPostMessage(post.postMessage || '', post.videos));
                    this.observeFeedVideos();
                    if (link_preview !== undefined) {
                        post.link_preview = link_preview;
                    } else if (post.postMessage && !post.video) {
                        const urlMatch = post.postMessage.match(/(https?:\/\/[^\s<>"']+)/i);
                        if (urlMatch && urlMatch[1]) {
                            this.lazyFetchLinkPreview(post, urlMatch[1]);
                        }
                    }
                }
            });
        },

        triggerHeartBurst(post, idx = 0) {
            if (!post || !post.post_no) return;
            const key = `${post.post_no}_${idx}`;
            this.heartBursts = { ...this.heartBursts, [key]: true };

            // Trigger generational celebration haptic vibration (Instagram / World-Class standard)
            if (typeof window.triggerHaptic === 'function') {
                window.triggerHaptic('impact');
            } else if (typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function') {
                try {
                    navigator.vibrate([20, 40, 20]);
                } catch (_) {}
            }

            // Automatically like with 'love' reaction if not already reacted
            if (!post.user_reaction) {
                this.onPostLikeClick(post.post_no, 'love');
            }

            setTimeout(() => {
                const updated = { ...this.heartBursts };
                delete updated[key];
                this.heartBursts = updated;
            }, 900);
        },

        handleImageTap(post, images, idx = 0, event) {
            const now = Date.now();
            const postKey = String(post?.post_no || '0');
            const lastTap = this.lastTapTime[postKey] || 0;
            const timeDiff = now - lastTap;

            if (timeDiff > 0 && timeDiff < 320) {
                // Double tap confirmed! Cancel pending single tap lightbox open
                if (this.tapTimeout) {
                    clearTimeout(this.tapTimeout);
                    this.tapTimeout = null;
                }
                this.lastTapTime[postKey] = 0;
                this.triggerHeartBurst(post, idx);
            } else {
                // First tap: buffer slightly to distinguish between single tap & double tap
                this.lastTapTime[postKey] = now;
                if (this.tapTimeout) clearTimeout(this.tapTimeout);
                this.tapTimeout = setTimeout(() => {
                    this.openLightbox(images, idx, post);
                    this.tapTimeout = null;
                }, 260);
            }
        },

        toggleLineageSpotlight(post) {
            if (!post || !post.post_no) return;
            this.activeSpotlightPostNo = (this.activeSpotlightPostNo === post.post_no) ? null : post.post_no;
        },

        togglePlayVoiceCapsule(post) {
            if (!post || !post.post_no) return;
            const url = post.voice_capsule_url || post.voice_url;
            if (!url) return;

            if (this.playingVoicePostNo === post.post_no) {
                if (this.activeAudioInstance) {
                    this.activeAudioInstance.pause();
                    this.activeAudioInstance = null;
                }
                this.playingVoicePostNo = null;
            } else {
                if (this.activeAudioInstance) {
                    this.activeAudioInstance.pause();
                    this.activeAudioInstance = null;
                }
                const audio = new Audio(url);
                audio.onended = () => {
                    this.playingVoicePostNo = null;
                    this.activeAudioInstance = null;
                };
                audio.onerror = () => {
                    this.playingVoicePostNo = null;
                    this.activeAudioInstance = null;
                };
                audio.play().then(() => {
                    this.playingVoicePostNo = post.post_no;
                    this.activeAudioInstance = audio;
                }).catch((e) => {
                    console.warn('[VoiceCapsule] playback failed:', e);
                    this.playingVoicePostNo = null;
                    this.activeAudioInstance = null;
                });
            }
        },

        openLightbox(images, index = 0, postObj = null) {
            let imgList = [];
            if (Array.isArray(images)) {
                imgList = Array.from(images).filter(img => img && typeof img === 'string');
            } else if (typeof images === 'string' && images.trim()) {
                imgList = images.split(',').map(s => s.trim()).filter(Boolean);
            } else if (images && typeof images === 'object') {
                imgList = Object.values(images).filter(img => img && typeof img === 'string');
            }

            if (imgList.length === 0) return;

            const safeIndex = (index >= 0 && index < imgList.length) ? index : 0;
            this.lightboxImages = imgList;
            this.lightboxIndex = safeIndex;
            this.lightboxLoading = true;
            this.lightboxError = false;
            this.lightboxDragY = 0;
            this.lightboxDragging = false;
            this.lightboxPost = postObj || null;
            this.mediaReactionTrayOpen = false;
            this.mediaCommentDrawerOpen = false;
            this.mediaCommentInput = '';
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        },

        closeLightbox() {
            this.lightboxOpen = false;
            this.lightboxImages = [];
            this.lightboxIndex = 0;
            this.lightboxLoading = false;
            this.lightboxError = false;
            this.lightboxDragY = 0;
            this.lightboxDragging = false;
            this.lightboxPost = null;
            this.mediaReactionTrayOpen = false;
            this.mediaCommentDrawerOpen = false;
            this.mediaCommentInput = '';
            document.body.style.overflow = ''; // Restore background scrolling
        },

        getMediaItemKey(post, mediaType, idx) {
            if (!post || !post.post_no) return '';
            return `${post.post_no}_${mediaType}_${idx || 0}`;
        },

        getMediaReaction(post, mediaType, idx) {
            if (!post || !post.post_no) return null;
            const key = this.getMediaItemKey(post, mediaType, idx);
            if (key && this.mediaReactionsMap && this.mediaReactionsMap[key]) {
                return this.mediaReactionsMap[key];
            }
            return post.user_reaction || null;
        },

        reactToMediaItem(post, mediaType, idx, reactionType, ev = null) {
            if (!post || !post.post_no) return;
            const key = this.getMediaItemKey(post, mediaType, idx);
            const current = this.mediaReactionsMap[key] || null;
            const nextReaction = (current === reactionType) ? null : reactionType;
            this.mediaReactionsMap = {
                ...this.mediaReactionsMap,
                [key]: nextReaction
            };
            try {
                localStorage.setItem('fp_media_reactions_v1', JSON.stringify(this.mediaReactionsMap));
            } catch (e) {}
            this.mediaReactionTrayOpen = false;
            this.reactToPost(post.post_no, reactionType, ev);
        },

        insertEmojiIntoMediaComment(emoji) {
            this.mediaCommentInput = (this.mediaCommentInput || '') + emoji;
        },

        async submitMediaComment(post, mediaType, idx, total) {
            if (!post || !post.post_no || this.mediaCommentSubmitting) return;
            const text = (this.mediaCommentInput || '').trim();
            if (!text) return;

            this.mediaCommentSubmitting = true;
            try {
                const label = (total > 1)
                    ? `[${mediaType === 'video' ? 'Video' : 'Photo'} ${(idx || 0) + 1}] `
                    : '';
                this.commentInputs[post.post_no] = label + text;
                await this.submitComment(post.post_no);
                this.mediaCommentInput = '';
            } finally {
                this.mediaCommentSubmitting = false;
            }
        },

        toggleMediaBookmark(post) {
            if (!post || !post.post_no) return;
            if (typeof window.__saveMemory === 'function') {
                window.__saveMemory(post.post_no);
            } else {
                post._saved = !post._saved;
            }
        },

        shareMediaItem(post, mediaType, idx) {
            const shareUrl = `${window.location.origin}/profilePage?post_no=${encodeURIComponent(post?.post_no || '')}#post${encodeURIComponent(post?.post_no || '')}`;
            if (navigator.share) {
                navigator.share({
                    title: post?.fullName ? `${post.fullName}'s Family Memory` : 'Family Memory',
                    url: shareUrl
                }).catch(() => {});
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shareUrl).then(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Link copied to clipboard!',
                        showConfirmButton: false,
                        timer: 1800
                    });
                }).catch(() => {});
            }
        },

        openVideoModal(videos, index = 0, initialMuted = false, postObj = null) {
            let list = [];
            if (Array.isArray(videos)) {
                list = videos.filter(v => v && (v.embedUrl || typeof v === 'string'));
            } else if (videos && typeof videos === 'object') {
                list = [videos];
            } else if (typeof videos === 'string') {
                list = [{ type: 'direct', embedUrl: videos, originalUrl: videos }];
            }

            if (list.length === 0) return;

            // Pause any background feed videos before launching full-screen mode
            document.querySelectorAll('video').forEach(v => {
                if (v.id !== 'fullVideoModalPlayer' && !v.paused) {
                    try { v.pause(); } catch (e) {}
                }
            });

            // Normalize entries
            list = list.map(v => {
                if (typeof v === 'string') {
                    return {
                        type: 'direct',
                        videoId: null,
                        embedUrl: (v.startsWith('/') || v.startsWith('http')) ? v : `/resources/videos/posts/${v}`,
                        originalUrl: v
                    };
                }
                return v;
            });

            const safeIndex = (index >= 0 && index < list.length) ? index : 0;
            this.videoModalVideos = list;
            this.videoModalIndex = safeIndex;
            this.videoModalMuted = !!initialMuted;
            this.videoModalPlaying = true;
            this.videoModalProgress = 0;
            this.videoModalCurrentTime = '0:00';
            this.videoModalDuration = '0:00';
            this.videoModalRemainingTime = '-0:00';
            this.videoModalSpeed = 1;
            this.videoModalHasFrame = false;
            this.videoModalIsPortrait = true;
            this.videoModalPost = postObj || null;
            this.mediaReactionTrayOpen = false;
            this.mediaCommentDrawerOpen = false;
            this.mediaCommentInput = '';
            this.videoModalOpen = true;
            document.body.style.overflow = 'hidden';

            this.$nextTick(() => {
                this.syncVideoModalPlayback();
                setTimeout(() => {
                    if (this.videoModalOpen) this.syncVideoModalPlayback();
                }, 60);
            });
        },

        closeVideoModal() {
            this.pauseVideoModalPlayback();
            this.videoModalOpen = false;
            this.videoModalVideos = [];
            this.videoModalIndex = 0;
            this.videoModalPlaying = false;
            this.videoModalHasFrame = false;
            this.videoModalPost = null;
            this.mediaReactionTrayOpen = false;
            this.mediaCommentDrawerOpen = false;
            this.mediaCommentInput = '';
            document.body.style.overflow = '';
            if (document.fullscreenElement || document.webkitFullscreenElement) {
                try {
                    if (document.exitFullscreen) document.exitFullscreen();
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                } catch (e) {}
            }
        },

        getVideoModalElement() {
            const direct = document.getElementById('fullVideoModalPlayer');
            if (direct) return direct;
            const container = document.getElementById('fullVideoModalStage');
            return container ? container.querySelector('video') : null;
        },

        onVideoModalMetadata(vEl) {
            if (!vEl) return;
            if (vEl.videoWidth && vEl.videoHeight) {
                this.videoModalIsPortrait = (vEl.videoHeight >= vEl.videoWidth * 0.95);
            }
            if (vEl.duration && !isNaN(vEl.duration)) {
                this.videoModalDuration = this.formatVideoTime(vEl.duration);
                const rem = Math.max(0, vEl.duration - (vEl.currentTime || 0));
                this.videoModalRemainingTime = '-' + this.formatVideoTime(rem);
            }
        },

        syncVideoModalPlayback() {
            const vEl = this.getVideoModalElement();
            if (!vEl) return;

            vEl.muted = this.videoModalMuted;
            vEl.playbackRate = this.videoModalSpeed || 1;
            const activeVid = this.videoModalVideos[this.videoModalIndex];
            if (vEl.paused) {
                if (activeVid && typeof activeVid.trimStart === 'number' && activeVid.trimStart > 0 && vEl.currentTime < activeVid.trimStart) {
                    try { vEl.currentTime = activeVid.trimStart; } catch (e) {}
                }
                const attemptPlay = () => {
                    const playPromise = vEl.play();
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            this.videoModalPlaying = true;
                            this.videoModalHasFrame = true;
                        }).catch(() => {
                            // Browser blocked unmuted playback -> fallback to muted autoplay immediately
                            vEl.muted = true;
                            this.videoModalMuted = true;
                            vEl.play().then(() => {
                                this.videoModalPlaying = true;
                                this.videoModalHasFrame = true;
                            }).catch(() => {
                                this.videoModalPlaying = false;
                            });
                        });
                    }
                };
                if (vEl.readyState >= 2) {
                    attemptPlay();
                } else {
                    vEl.addEventListener('loadeddata', () => attemptPlay(), { once: true });
                    attemptPlay();
                }
            }
        },

        pauseVideoModalPlayback() {
            const vEl = this.getVideoModalElement();
            if (vEl) {
                try { vEl.pause(); } catch (e) {}
            }
        },

        nextVideoModal() {
            if (this.videoModalVideos.length > 1) {
                this.pauseVideoModalPlayback();
                this.videoModalHasFrame = false;
                this.videoModalIndex = (this.videoModalIndex + 1) % this.videoModalVideos.length;
                this.videoModalProgress = 0;
                this.$nextTick(() => {
                    this.syncVideoModalPlayback();
                });
            }
        },

        prevVideoModal() {
            if (this.videoModalVideos.length > 1) {
                this.pauseVideoModalPlayback();
                this.videoModalHasFrame = false;
                this.videoModalIndex = (this.videoModalIndex - 1 + this.videoModalVideos.length) % this.videoModalVideos.length;
                this.videoModalProgress = 0;
                this.$nextTick(() => {
                    this.syncVideoModalPlayback();
                });
            }
        },

        toggleVideoModalPlay() {
            const vEl = this.getVideoModalElement();
            if (vEl) {
                if (vEl.paused) {
                    vEl.play().then(() => {
                        this.videoModalPlaying = true;
                        this.videoModalHasFrame = true;
                    }).catch(() => {});
                } else {
                    vEl.pause();
                    this.videoModalPlaying = false;
                }
            }
        },

        toggleVideoModalMute() {
            const vEl = this.getVideoModalElement();
            if (vEl) {
                vEl.muted = !vEl.muted;
                this.videoModalMuted = vEl.muted;
            } else {
                this.videoModalMuted = !this.videoModalMuted;
            }
        },

        toggleVideoModalSpeed() {
            const speeds = [1, 1.25, 1.5, 2];
            const idx = speeds.indexOf(this.videoModalSpeed);
            this.videoModalSpeed = speeds[(idx + 1) % speeds.length];
            const vEl = this.getVideoModalElement();
            if (vEl) {
                vEl.playbackRate = this.videoModalSpeed;
            }
        },

        seekVideoModal(event) {
            const track = event.currentTarget;
            const vEl = this.getVideoModalElement();
            if (!track || !vEl || !vEl.duration) return;
            const rect = track.getBoundingClientRect();
            const clickX = Math.max(0, Math.min(event.clientX - rect.left, rect.width));
            const ratio = clickX / rect.width;
            vEl.currentTime = ratio * vEl.duration;
        },

        onVideoModalTimeUpdate(vEl) {
            if (!vEl || !vEl.duration) return;
            this.videoModalProgress = Math.min(100, Math.max(0, (vEl.currentTime / vEl.duration) * 100));
            this.videoModalCurrentTime = this.formatVideoTime(vEl.currentTime);
            this.videoModalDuration = this.formatVideoTime(vEl.duration);
            const remaining = Math.max(0, vEl.duration - vEl.currentTime);
            this.videoModalRemainingTime = '-' + this.formatVideoTime(remaining);

            const activeVid = this.videoModalVideos[this.videoModalIndex];
            if (activeVid && typeof activeVid.trimEnd === 'number' && vEl.currentTime >= activeVid.trimEnd) {
                this.onVideoModalEnded();
            }
        },

        onVideoModalEnded() {
            if (this.videoModalVideos && this.videoModalVideos.length > 1) {
                if (this.videoModalIndex < this.videoModalVideos.length - 1) {
                    this.nextVideoModal();
                } else {
                    // Loop back to the first video
                    this.pauseVideoModalPlayback();
                    this.videoModalHasFrame = false;
                    this.videoModalIndex = 0;
                    this.videoModalProgress = 0;
                    this.$nextTick(() => {
                        this.syncVideoModalPlayback();
                    });
                }
            } else {
                // Single video: loop playback
                const vEl = this.getVideoModalElement();
                if (vEl) {
                    const activeVid = this.videoModalVideos[this.videoModalIndex];
                    vEl.currentTime = (activeVid && typeof activeVid.trimStart === 'number') ? activeVid.trimStart : 0;
                    vEl.play().catch(() => {});
                }
            }
        },

        formatVideoTime(seconds) {
            if (isNaN(seconds) || seconds < 0) return '0:00';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${s < 10 ? '0' : ''}${s}`;
        },

        toggleNativeFullscreen() {
            const el = document.getElementById('fullVideoModalRoot') || document.documentElement;
            const vEl = this.getVideoModalElement();
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                if (el.requestFullscreen) {
                    el.requestFullscreen().catch(() => {});
                    this.videoModalFullscreen = true;
                } else if (el.webkitRequestFullscreen) {
                    el.webkitRequestFullscreen();
                    this.videoModalFullscreen = true;
                } else if (vEl && vEl.webkitEnterFullscreen) {
                    vEl.webkitEnterFullscreen();
                    this.videoModalFullscreen = true;
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                this.videoModalFullscreen = false;
            }
        },

        nextLightboxImage() {
            if (this.lightboxImages.length > 1) {
                this.lightboxLoading = true;
                this.lightboxError = false;
                this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxImages.length;
            }
        },

        prevLightboxImage() {
            if (this.lightboxImages.length > 1) {
                this.lightboxLoading = true;
                this.lightboxError = false;
                this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxImages.length) % this.lightboxImages.length;
            }
        },

        onLightboxTouchStart(e) {
            if (e.touches && e.touches.length === 1) {
                this.touchStartX = e.touches[0].clientX;
                this.touchStartY = e.touches[0].clientY;
                this.lightboxDragY = 0;
                this.lightboxDragging = false;
            }
        },

        onLightboxTouchMove(e) {
            if (!this.touchStartY || !e.touches || e.touches.length === 0) return;
            const diffX = e.touches[0].clientX - this.touchStartX;
            const diffY = e.touches[0].clientY - this.touchStartY;

            // When vertical swipe movement is initiated
            if (Math.abs(diffY) > Math.abs(diffX) && Math.abs(diffY) > 8) {
                this.lightboxDragging = true;
                this.lightboxDragY = diffY;
                if (e.cancelable) e.preventDefault();
            }
        },

        onLightboxTouchEnd(e) {
            if (!this.touchStartX || !e.changedTouches || e.changedTouches.length === 0) {
                this.resetLightboxDrag();
                return;
            }
            const diffX = e.changedTouches[0].clientX - this.touchStartX;
            const diffY = e.changedTouches[0].clientY - this.touchStartY;
            this.touchStartX = 0;
            this.touchStartY = 0;

            // Vertical drag-to-dismiss threshold (≥ 80px dismisses lightbox with native velocity feel)
            if (this.lightboxDragging && Math.abs(diffY) >= 80) {
                this.closeLightbox();
                return;
            }

            // Snap back smoothly if below threshold
            this.resetLightboxDrag();

            // Horizontal swipe (> 50px threshold for photo gallery cycling)
            if (!this.lightboxDragging && Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                if (diffX < 0) {
                    this.nextLightboxImage();
                } else {
                    this.prevLightboxImage();
                }
            }
        },

        onLightboxTouchCancel() {
            this.resetLightboxDrag();
        },

        resetLightboxDrag() {
            this.lightboxDragging = false;
            this.lightboxDragY = 0;
        },

        getLightboxOverlayStyle() {
            if (!this.lightboxDragging || this.lightboxDragY === 0) return '';
            const progress = Math.min(Math.abs(this.lightboxDragY) / 260, 0.75);
            const alpha = Math.max(0.18, 0.94 * (1 - progress));
            const blurPx = Math.max(2, Math.round(14 * (1 - progress)));
            return `background-color: rgba(0, 0, 0, ${alpha}) !important; backdrop-filter: blur(${blurPx}px) !important;`;
        },

        getLightboxImageStyle() {
            if (!this.lightboxDragging || this.lightboxDragY === 0) return '';
            const scale = Math.max(0.72, 1 - Math.abs(this.lightboxDragY) / 850);
            return `transform: translateY(${this.lightboxDragY}px) scale(${scale}); transition: none;`;
        },

        initPusher() {
            try {
                const key = document.querySelector('meta[name="pusher-key"]')?.getAttribute('content')
                    || (typeof window !== 'undefined' && window.PUSHER_APP_KEY)
                    || process.env.MIX_PUSHER_APP_KEY;
                const cluster = document.querySelector('meta[name="pusher-cluster"]')?.getAttribute('content')
                    || (typeof window !== 'undefined' && window.PUSHER_APP_CLUSTER)
                    || process.env.MIX_PUSHER_APP_CLUSTER
                    || 'eu';
                const famCode = (this.currentFamCode || '').replace(/[^A-Za-z0-9_-]/g, '');
                if (!key || !cluster || !famCode) {
                    return;
                }

                // One private, per-family channel. The server (Pusher::authoriseChannel)
                // only signs the subscription if this session belongs to <famCode>,
                // so another family cannot read this feed even with the public key.
                this.pusher = new Pusher(key, {
                    cluster,
                    forceTLS: true,
                    channelAuthorization: {
                        endpoint: '/pusher/auth',
                        headersProvider: () => ({
                            'X-CSRF-Token': getCsrfToken(),
                            'X-XSRF-TOKEN': getCsrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                        }),
                    },
                });

                const channel = this.pusher.subscribe(`private-family-${famCode}`);

                // Real-time notifications: subscribe to user-specific private channel & family channel
                const rawUserId = this.currentUserId
                    || document.querySelector('meta[name="user-id"]')?.getAttribute('content')
                    || localStorage.getItem('requesterId')
                    || '';
                const userSlug = String(rawUserId).replace(/[^A-Za-z0-9_-]/g, '');
                if (userSlug) {
                    try {
                        const userChannel = this.pusher.subscribe(`private-user-${userSlug}`);
                        userChannel.bind('new-notification', (data) => {
                            window.dispatchEvent(new CustomEvent('pwa:notification-received', { detail: data }));
                        });
                    } catch (e) {
                        console.warn('[feedComponent] userChannel subscribe error:', e);
                    }
                }

                channel.bind('new-notification', (data) => {
                    window.dispatchEvent(new CustomEvent('pwa:notification-received', { detail: data }));
                });

                channel.bind('new-post', (data) => {
                    if (Array.isArray(data)) {
                        let needsRefetch = false;
                        data.forEach(item => {
                            // Game state is viewer-specific (has_voted / is_author), so
                            // re-hydrate from the server instead of trusting the broadcast.
                            if (String(item?.postMessage || '').includes('Two Truths & a Lie')) {
                                needsRefetch = true;
                                return;
                            }
                            if (!this.posts.some(p => String(p.post_no) === String(item?.post_no))) {
                                this.posts.unshift(this.normalizePost(item));
                            }
                        });
                        this.observeFeedVideos();
                        if (needsRefetch) this.fetchPosts();
                    }
                });

                channel.bind('two-truths-voted', (data) => {
                    const post = this.posts.find(p => p.two_truths && p.two_truths.id === data?.game_id);
                    if (post) {
                        post.two_truths.total_votes = data.total_votes;
                        if (post.two_truths.has_voted || post.two_truths.is_author) {
                            post.two_truths.vote_percentages = data.vote_percentages || post.two_truths.vote_percentages;
                        }
                    }
                });

                channel.bind('new-comment', (data) => {
                    if (Array.isArray(data)) {
                        data.forEach(item => {
                            const post = this.posts.find(p => String(p.post_no) === String(item?.post_no));
                            if (post && !post.comments.some(c => String(c.comment_no) === String(item?.comment_no))) {
                                post.comments.push(this.normalizeComment(item));
                            }
                        });
                    }
                });

                // Edit/delete broadcasts (PostMessage::deletePost/updatePost/
                // updateComment/deleteComment) send a single flat object, not an
                // array like new-post/new-comment do.
                channel.bind('delete-post', (data) => {
                    this.posts = this.posts.filter(p => String(p.post_no) !== String(data?.postNo));
                });
                channel.bind('update-post', (data) => {
                    const post = this.posts.find(p => String(p.post_no) === String(data?.postNo));
                    if (post) {
                        post.postMessage = this.decodeHtml(data?.postMessage ?? post.postMessage);
                        post.post_style = this.extractPostStyle(post.postMessage, data?.post_style);
                        post.displayMessage = this.normalizeEmojiText(cleanPostMessage(post.postMessage || '', extractVideoFromText(post.postMessage)));
                        post.video = extractVideoFromText(post.postMessage);
                        if (data?.link_preview !== undefined) {
                            post.link_preview = data.link_preview;
                        } else if (post.postMessage && !post.video) {
                            const urlMatch = post.postMessage.match(/(https?:\/\/[^\s<>"']+)/i);
                            if (urlMatch && urlMatch[1]) {
                                this.lazyFetchLinkPreview(post, urlMatch[1]);
                            }
                        }
                    }
                });

                channel.bind('delete-comment', (data) => {
                    const post = this.posts.find(p => String(p.post_no) === String(data?.postNo));
                    if (post) {
                        post.comments = post.comments.filter(c => String(c.comment_no) !== String(data?.commentNo) && String(c.parent_comment_no || '') !== String(data?.commentNo));
                    }
                });
                channel.bind('update-comment', (data) => {
                    const post = this.posts.find(p => String(p.post_no) === String(data?.postNo));
                    const comment = post?.comments.find(c => String(c.comment_no) === String(data?.commentNo));
                    if (comment) comment.comment = data?.comment ?? comment.comment;
                });

                channel.bind('comment-reaction', (data) => {
                    if (!data) return;
                    const postNo = data.postNo ?? data.post_no;
                    const commentNo = data.commentNo ?? data.comment_no;
                    if (!commentNo) return;

                    let post = postNo ? this.posts.find(p => String(p.post_no) === String(postNo)) : null;
                    if (!post) {
                        post = this.posts.find(p => Array.isArray(p.comments) && p.comments.some(c => String(c.comment_no) === String(commentNo)));
                    }
                    if (post && Array.isArray(post.comments)) {
                        const comment = post.comments.find(c => String(c.comment_no) === String(commentNo));
                        if (comment) {
                            if (data.reactions && typeof data.reactions === 'object') {
                                comment.reactions = { ...data.reactions };
                            }
                            if (data.totalReactions !== undefined) {
                                comment.totalReactions = parseInt(data.totalReactions, 10);
                            }
                            // Cross-tab / cross-device sync if triggered by current user
                            if (data.userId && String(data.userId) === String(this.currentUserId)) {
                                if (data.action === 'removed') {
                                    comment.userReaction = null;
                                } else if (data.reactionType) {
                                    comment.userReaction = data.reactionType;
                                }
                            }
                        }
                    }
                });

                channel.bind('like-event', (data) => {
                    if (Array.isArray(data)) {
                        data.forEach(item => {
                            const postNo = item?.post_no ?? (item?.likeHtmlId ? String(item.likeHtmlId).replace('likeCounter', '') : null);
                            const post = this.posts.find(p => String(p.post_no) === String(postNo));
                            if (post && item?.likeCounter !== undefined) {
                                post.post_likes = parseInt(item.likeCounter, 10);
                            }
                        });
                    }
                });
            } catch (e) {
                console.warn('Pusher initialization skipped:', e);
            }
        }
    };
}

if (typeof window !== 'undefined') {
    window.getMediaCaption = function(post) {
        if (!post) return '';
        let text = (post.displayMessage !== undefined && post.displayMessage !== null)
            ? String(post.displayMessage)
            : String(post.postMessage || '');

        if (!text.trim()) return '';

        text = text.replace(/\[style:[a-z0-9_]+\]\s*/gi, '');
        text = text.replace(/https?:\/\/(?:photos\.app\.goo\.gl|photos\.google\.com)\/[^\s<>"']+/gi, '');
        text = text.replace(/https?:\/\/(?:www\.)?(?:icloud\.com\/photos|i\.imgur\.com|imgur\.com|ibb\.co|postimg\.cc)\/[^\s<>"']+/gi, '');
        text = text.replace(/https?:\/\/[^\s<>"']+\.(?:jpe?g|png|webp|avif|gif)(?:\?[^\s<>"']*)?/gi, '');
        text = text.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)[a-zA-Z0-9_-]{11}/gi, '');
        text = text.replace(/(?:https?:\/\/)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/[^\/]*\/videos\/|album\/\d+\/video\/|video\/|)\d+(?:$|\/|\?)/gi, '');
        text = text.replace(/(?:https?:\/\/)?(?:iframe\.videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)\/[a-zA-Z0-9]{32}/gi, '');
        text = text.replace(/(?:https?:\/\/[^\s"'<>]+|\/resources\/(?:videos|images)\/[^\s"'<>]+)\.(?:mp4|webm|ogg|mov)(?:\?[^\s"'<>#]*)?(?:#t=[0-9.]+(?:,[0-9.]+)?)?/gi, '');

        if (post.link_preview) {
            if (post.link_preview.original_url) text = text.split(post.link_preview.original_url).join('');
            if (post.link_preview.url) text = text.split(post.link_preview.url).join('');
        }
        if (post.video && post.video.originalUrl) {
            text = text.split(post.video.originalUrl).join('');
        }
        if (Array.isArray(post.videos)) {
            post.videos.forEach(v => {
                if (v && v.originalUrl) text = text.split(v.originalUrl).join('');
            });
        }

        return text.trim();
    };
}
