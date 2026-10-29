import axios from 'axios';
import Pusher from 'pusher-js';
import { format } from 'timeago.js';
import Swal from 'sweetalert2';
import { extractVideoFromText, cleanPostMessage } from './videoParser';
import { getCsrfToken } from '../global';
import { getPostImageUrl, getProfileImageUrl, getPostImageVariantUrl } from '../helper/imageUrls';
import { FAMILY_GIFS, GIF_CATEGORIES, isAllowedGifUrl } from './gifCatalog';
import { getPreferredSkinTone, setPreferredSkinTone, SKIN_TONES } from '../emojiPicker';
import { HEIRLOOM_REACTIONS, getHeirloomGlassSvg, triggerHeirloomBurst } from './heirloomGlassReactions';

export function profileFeed(opts = {}) {
    return {
        posts: [],
        isLoading: true,
        errorMessage: '',
        lightboxOpen: false,
        lightboxImages: [],
        lightboxIndex: 0,
        lightboxLoading: false,
        lightboxError: false,
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
        csrfOptions: {
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN',
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
                        this.posts = cachedPosts.map(p => this.normalizePost(p));
                        this.isLoading = false;
                    }
                } catch (cacheErr) {
                    console.warn('[Feed] Offline cache read:', cacheErr);
                }
            }

            // 2. Fetch fresh updates from server
            try {
                const url = filter ? `/post/getAllPostCommentByFamCode?filter=${encodeURIComponent(filter)}` : '/post/getAllPostCommentByFamCode';
                const response = await axios.get(url);
                const rawPosts = response?.data?.message?.message;
                
                if (Array.isArray(rawPosts)) {
                    this.posts = rawPosts.map(p => this.normalizePost(p));
                    // Update offline store in background if main stream
                    if (!filter && window.offlineSync && typeof window.offlineSync.cacheFeed === 'function') {
                        window.offlineSync.cacheFeed(rawPosts).catch(() => {});
                    }
                    this.scrollToHashPost();
                    this.observePostViews();
                    this.observeCommentViews();
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

        async setStreamFilter(filterType) {
            this.activeStreamFilter = filterType;
            this.isLoading = true;
            await this.fetchPosts({ filter: filterType === 'tagged' ? 'tagged' : '' });
        },

        normalizePost(p) {
            let profImg = '/public/avatar/avatarF.png';
            const rawImg = p?.img || p?.profileImg;
            if (rawImg) {
                profImg = (rawImg.startsWith('/') || rawImg.startsWith('http')) ? rawImg : `/resources/images/profile/${rawImg}`;
            }
            const normalized = {
                post_no: p?.post_no,
                id: p?.id,
                fullName: p?.fullName || 'Family Member',
                profileImg: profImg,
                postFamCode: p?.postFamCode || '',
                date_created: p?.date_created || new Date().toISOString(),
                post_time: p?.post_time || p?.date_created || new Date().toISOString(),
                postMessage: p?.postMessage || '',
                displayMessage: this.normalizeEmojiText(cleanPostMessage(p?.postMessage || '', extractVideoFromText(p?.postMessage))),
                video: extractVideoFromText(p?.postMessage),
                link_preview: p?.link_preview || null,
                post_likes: parseInt(p?.post_likes || 0, 10),
                images: this.extractImages(p),
                poll: p?.poll || null,
                comparison: this.normalizeComparison(p?.comparison),
                reactions: Array.isArray(p?.reactions) ? p.reactions : [],
                user_reaction: p?.user_reaction || null,
                comments: Array.isArray(p?.comments) ? p.comments.map(c => this.normalizeComment(c)) : [],
                isLiked: false,
                post_tags: Array.isArray(p?.post_tags) ? p.post_tags : [],
                views_count: parseInt(p?.views_count || 0, 10),
                voice_capsule_url: p?.voice_capsule_url || null,
            };

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
                comment: this.normalizeEmojiText(rawComment),
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
            return {
                before_img: String(c.before_img),
                after_img: String(c.after_img),
                before_label: c.before_label || 'Then',
                after_label: c.after_label || 'Now',
                parent_post_no: c.parent_post_no || null,
                answer_count: parseInt(c.answer_count || 0, 10) || 0,
            };
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

            return collected.filter(img => img && img !== 'null' && img !== 'undefined' && img !== 'none' && !img.includes('no_image'));
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

        escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
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

        renderHashtags(message) {
            if (!message) return '';
            let safeText = this.escapeHtml(message);

            // Linkify URLs into secure clickable links
            safeText = safeText.replace(/(https?:\/\/[^\s<>"']+)/gi, (match) => {
                const cleanHref = match.replace(/[.,;:!?)]+$/, '');
                return `<a href="${cleanHref}" target="_blank" rel="noopener noreferrer" class="text-primary fw-medium text-decoration-underline" style="word-break: break-all;" onclick="event.stopPropagation();">${cleanHref}</a>`;
            });

            // Highlight @First Last mentions as chips
            safeText = safeText.replace(/(?<![\w@])@([A-Z\p{Lu}][\p{L}'-]+(?:\s+[A-Z\p{Lu}][\p{L}'-]+)+)(?![\w])/gu, (match, name) => {
                return `<span class="mention-chip" title="Mentioned: ${name}"><i class="bi bi-at"></i>${name}</span>`;
            });

            // Hashtag pills
            return safeText.replace(/#([a-zA-Z0-9_]{2,40})/g, (match, tag) => {
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
                    // `post` may be the raw object from normalizePost(); assigning to it
                    // bypasses Alpine's proxy, so write to the reactive copy in this.posts.
                    const target = this.posts?.find(p => p?.post_no === post.post_no) || post;
                    target.link_preview = res.data.data;
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
            const commentText = (this.commentInputs[postNo] || '').trim();
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

            postNoInput.value = post.post_no;
            textarea.value = post.postMessage || '';
            if (notice) notice.classList.remove('d-none');

            const modalTitle = document.getElementById('postModalLabel');
            if (modalTitle) modalTitle.textContent = 'Edit Post';

            const modalEl = document.getElementById('postModal');
            const instance = window.bootstrap?.Modal?.getOrCreateInstance(modalEl);
            instance?.show();
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
            this.editCommentText = comment.comment;
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
        // since hover/mouseenter never fires on touch devices ────────────
        startPostLongPress(postNo) {
            this._postLongPressFired = false;
            clearTimeout(this._postLongPressTimer);
            this._postLongPressTimer = setTimeout(() => {
                this._postLongPressFired = true;
                this.activeReactionBars[postNo] = true;
                if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
            }, 450);
        },

        cancelPostLongPress() {
            clearTimeout(this._postLongPressTimer);
        },

        // Plain tap likes the post; a long-press that already opened the
        // bar suppresses the tap so it doesn't also toggle the like.
        onPostLikeClick(postNo, reactionType) {
            if (this._postLongPressFired) {
                this._postLongPressFired = false;
                return;
            }
            this.reactToPost(postNo, reactionType);
        },

        startCommentLongPress(commentNo) {
            this._commentLongPressFired = false;
            clearTimeout(this._commentLongPressTimer);
            this._commentLongPressTimer = setTimeout(() => {
                this._commentLongPressFired = true;
                this.activeCommentReactionBars[commentNo] = true;
                if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
            }, 450);
        },

        cancelCommentLongPress() {
            clearTimeout(this._commentLongPressTimer);
        },

        onCommentLikeClick(postNo, commentNo, reactionType) {
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
            this.reactToComment(postNo, commentNo, targetType);
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

        // ── React to a comment with an emoji ───────────────────────────────
        async reactToComment(postNo, commentNo, reactionType) {
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
                triggerHeirloomBurst(cleanType, window.event || null);
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
                    emoji: map[label] ?? '👍',
                    count: Number(count)
                }));
        },

        // ── React to a post with an emoji (post-level reactions) ────────────
        async reactToPost(postNo, reactionType) {
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
                triggerHeirloomBurst(post.user_reaction, window.event || null);
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

        initEventListeners() {
            window.addEventListener('post-created', (event) => {
                const newPostData = event?.detail;
                if (newPostData && typeof newPostData === 'object') {
                    const normalized = this.normalizePost(newPostData);
                    const existing = this.posts.find(p => String(p.post_no) === String(normalized.post_no));
                    if (!existing) {
                        this.posts.unshift(normalized);
                    } else if (normalized.comparison && !existing.comparison) {
                        // The realtime broadcast can land first, without the Then & Now pair.
                        existing.comparison = normalized.comparison;
                    }
                } else {
                    this.fetchPosts();
                }
            });

            // Dispatched by allEvents.js after a successful post edit (the acting
            // user's own tab — other tabs get it via the update-post Pusher bind).
            window.addEventListener('post-updated', (event) => {
                const { postNo, postMessage, link_preview } = event?.detail || {};
                const post = this.posts.find(p => String(p.post_no) === String(postNo));
                if (post) {
                    post.postMessage = postMessage;
                    post.displayMessage = this.normalizeEmojiText(cleanPostMessage(postMessage || '', extractVideoFromText(postMessage)));
                    post.video = extractVideoFromText(postMessage);
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
                    this.openLightbox(images, idx);
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

        openLightbox(images, index = 0) {
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
            document.body.style.overflow = ''; // Restore background scrolling
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
                const userSlug = (this.currentUserId || localStorage.getItem('requesterId') || '').toLowerCase().replace(/[^a-z0-9_-]/g, '-');
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
                        data.forEach(item => {
                            if (!this.posts.some(p => String(p.post_no) === String(item?.post_no))) {
                                this.posts.unshift(this.normalizePost(item));
                            }
                        });
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
                        post.postMessage = data?.postMessage ?? post.postMessage;
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
