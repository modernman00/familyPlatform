import axios from 'axios';
import Swal from 'sweetalert2';
import { format } from 'timeago.js';
import { esc } from '../global';
import { FAMILY_GIFS, GIF_CATEGORIES } from '../profilePage/gifCatalog';
import { HEIRLOOM_REACTIONS, getHeirloomGlassSvg, triggerHeirloomBurst } from '../profilePage/heirloomGlassReactions';

const REEL_COMMENT_EMOJI_MAP = {
    love: '❤️',
    like: '👍',
    haha: '😂',
    shock: '😮',
    sad: '😢',
    celebrate: '✨'
};

const REEL_REACTION_LABELS = {
    love: 'Love',
    like: 'Like',
    likes: 'Like',
    haha: 'Haha',
    shock: 'Wow',
    wow: 'Wow',
    sad: 'Empathy',
    celebrate: 'Celebrate',
    bless: 'Celebrate',
    proud: 'Celebrate'
};

const REEL_REACTION_COLORS = {
    love: '#fb7185',
    like: '#fbbf24',
    likes: '#fbbf24',
    haha: '#fde047',
    shock: '#38bdf8',
    wow: '#38bdf8',
    sad: '#2dd4bf',
    celebrate: '#c084fc',
    bless: '#c084fc',
    proud: '#c084fc'
};

class FamilyReelsPlayer {
    constructor() {
        this.reels = [];
        this.currentIndex = 0;
        this.isMuted = false;
        this.isLoading = false;
        this.isCommentsOpen = false;
        this.fitMode = 'auto'; // 'auto' | 'cinema' | 'cover'
        this.viewedReelIds = new Set();
        this.touchStartY = 0;
        this.touchEndY = 0;
        this.lastWheelTime = 0;

        this.viewport = document.getElementById('reelsViewport');
        this.container = document.getElementById('reelsTheaterContainer');
        this.currentUserId = this.container?.dataset?.currentUserId || '';
        this.commentsDrawer = document.getElementById('reelCommentsDrawer');
        this.commentsList = document.getElementById('reelCommentsList');
        this.commentForm = document.getElementById('reelCommentForm');
        this.commentInput = document.getElementById('reelCommentInput');

        this.replyTarget = null;
        this.attachedGifUrl = null;
        this.selectedGifCat = 'all';

        this.gifPickerDrawer = document.getElementById('reelGifPickerDrawer');
        this.gifToggleBtn = document.getElementById('reelGifToggleBtn');
        this.closeGifPickerBtn = document.getElementById('closeReelGifPickerBtn');
        this.gifCategoriesContainer = document.getElementById('reelGifCategories');
        this.gifGridContainer = document.getElementById('reelGifGrid');
        this.commentAuxBar = document.getElementById('reelCommentAuxBar');
        this.replyBadge = document.getElementById('reelReplyBadge');
        this.replyTargetName = document.getElementById('reelReplyTargetName');
        this.cancelReplyBtn = document.getElementById('cancelReelReplyBtn');
        this.attachedGifContainer = document.getElementById('reelAttachedGifContainer');
        this.attachedGifImg = document.getElementById('reelAttachedGifImg');
        this.removeAttachedGifBtn = document.getElementById('removeReelAttachedGifBtn');
        this.commentSubmitBtn = document.getElementById('reelCommentSubmitBtn');
        this.commentSubmitIcon = document.getElementById('reelCommentSubmitIcon');
        this.commentSubmitSpinner = document.getElementById('reelCommentSubmitSpinner');

        if (this.viewport || this.container) {
            this.init();
        }

        this.initTrayCards();
        this.initCreateModal();
        this.initCommentControls();
    }

    /**
     * Render a video frame onto a 540x960 (9:16 HD) canvas WITHOUT stretching.
     * - 'cover': 9-argument drawImage center-crop (preserves 1:1 pixel aspect ratio).
     * - 'cinema': Blurred ambient 9:16 background + uncropped aspect-preserved foreground frame.
     */
    static renderVideoFrameToCanvas(videoEl, canvasEl, mode = 'auto') {
        if (!videoEl || !canvasEl) return null;
        const vw = videoEl.videoWidth || 0;
        const vh = videoEl.videoHeight || 0;
        if (vw <= 0 || vh <= 0) return null;

        const targetW = 540;
        const targetH = 960;
        canvasEl.width = targetW;
        canvasEl.height = targetH;

        const ctx = canvasEl.getContext('2d');
        if (!ctx) return null;

        const isLandscape = vw > vh;
        const effectiveMode = mode === 'auto' ? (isLandscape ? 'cinema' : 'cover') : mode;
        const targetRatio = targetW / targetH;
        const sourceRatio = vw / vh;

        // Helper for 9-arg center-crop draw
        const drawCenterCrop = () => {
            let sx = 0, sy = 0, sw = vw, sh = vh;
            if (sourceRatio > targetRatio) {
                sw = Math.round(vh * targetRatio);
                sx = Math.round((vw - sw) / 2);
            } else {
                sh = Math.round(vw / targetRatio);
                sy = Math.round((vh - sh) / 2);
            }
            ctx.drawImage(videoEl, sx, sy, sw, sh, 0, 0, targetW, targetH);
        };

        ctx.save();
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(0, 0, targetW, targetH);

        if (effectiveMode === 'cinema' && isLandscape) {
            // 1. Blurred ambient backdrop pass (center-cropped so background has zero stretch)
            try {
                ctx.filter = 'blur(22px) brightness(0.48)';
            } catch (_) {
                // Ignore if browser lacks ctx.filter
            }
            drawCenterCrop();
            ctx.filter = 'none';

            // Darken backdrop slightly for contrast
            ctx.fillStyle = 'rgba(15, 23, 42, 0.42)';
            ctx.fillRect(0, 0, targetW, targetH);

            // 2. Crisp uncropped foreground pass (100% aspect-ratio preserved)
            const drawW = targetW;
            const drawH = Math.round(targetW * (vh / vw));
            const drawY = Math.round((targetH - drawH) / 2);

            ctx.shadowColor = 'rgba(0, 0, 0, 0.55)';
            ctx.shadowBlur = 24;
            ctx.drawImage(videoEl, 0, 0, vw, vh, 0, drawY, drawW, drawH);
            ctx.shadowBlur = 0;
        } else {
            // Portrait or explicit 9:16 Fill: true center-crop (never stretches!)
            drawCenterCrop();
        }

        ctx.restore();
        return {
            dataUrl: canvasEl.toDataURL('image/jpeg', 0.88),
            aspectRatio: isLandscape ? '16:9' : '9:16',
            isLandscape
        };
    }

    /**
     * Enhance the Horizontal Reels Tray on /profilePage:
     * 1. Category filter pills
     * 2. True-aspect live video frame rendering (fixes legacy stretched thumbnails immediately)
     * 3. Background self-healing of stretched/missing server thumbnails
     * 4. Hover / touch silent video preview
     */
    initTrayCards() {
        const tray = document.getElementById('familyReelsTray') || document.querySelector('.reels-tray-wrapper');
        if (!tray) return;

        // 1. Category Filter Pills
        const filterBtns = tray.querySelectorAll('[data-reel-filter]');
        const cards = tray.querySelectorAll('.reel-fb-card[data-reel-id]');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const selectedCat = (btn.getAttribute('data-reel-filter') || 'all').toLowerCase();
                cards.forEach(card => {
                    const cardCat = (card.getAttribute('data-reel-category') || '').toLowerCase();
                    if (selectedCat === 'all' || cardCat === selectedCat) {
                        card.classList.remove('d-none');
                    } else {
                        card.classList.add('d-none');
                    }
                });
            });
        });

        // 2. True-Aspect Video Cover & Auto-Healing
        cards.forEach(card => {
            const video = card.querySelector('.reel-fb-video-thumb');
            const thumbImg = card.querySelector('.reel-fb-thumb');
            const ambientBg = card.querySelector('.reel-fb-ambient-bg');
            if (!video) return;

            const onVideoReady = () => {
                const vw = video.videoWidth || 0;
                const vh = video.videoHeight || 0;
                if (vw <= 0 || vh <= 0) return;

                const isLandscape = vw > vh;
                if (isLandscape) {
                    card.classList.add('is-landscape');
                } else {
                    card.classList.remove('is-landscape');
                }

                // Reveal the true-aspect video element and hide the legacy stretched static <img>
                video.classList.add('ready');
                if (thumbImg) {
                    thumbImg.style.opacity = '0';
                }

                // Generate a clean aspect-preserved canvas snapshot for ambient backdrop & self-healing
                try {
                    const offCanvas = document.createElement('canvas');
                    const rendered = FamilyReelsPlayer.renderVideoFrameToCanvas(video, offCanvas, 'auto');
                    if (rendered && rendered.dataUrl) {
                        if (ambientBg) {
                            ambientBg.style.backgroundImage = `url('${rendered.dataUrl}')`;
                        }
                        if (thumbImg) {
                            thumbImg.src = rendered.dataUrl;
                        }

                        // Self-heal server thumbnail once per session if owner & legacy thumb was 9:16 on a landscape video
                        const reelId = card.getAttribute('data-reel-id');
                        const isOwner = card.getAttribute('data-is-owner') === '1';
                        const savedAspect = card.getAttribute('data-aspect') || '9:16';
                        const healKey = `reel_thumb_healed_${reelId}`;

                        if (reelId && isOwner && (isLandscape && savedAspect === '9:16') && !sessionStorage.getItem(healKey)) {
                            sessionStorage.setItem(healKey, '1');
                            axios.post('/api/reels/thumbnail', {
                                reel_id: Number(reelId),
                                thumbnail_data: rendered.dataUrl,
                                aspect_ratio: rendered.aspectRatio
                            }).catch(() => {});
                        }
                    }
                } catch (_) {
                    // Cross-origin videos may taint canvas; native <video> element still displays un-stretched!
                }
            };

            if (video.readyState >= 2) {
                onVideoReady();
            } else {
                video.addEventListener('loadeddata', onVideoReady, { once: true });
                video.addEventListener('loadedmetadata', () => {
                    if (video.currentTime === 0) {
                        try { video.currentTime = 0.8; } catch (_) {}
                    }
                }, { once: true });
            }

            // Silent Hover Preview
            card.addEventListener('mouseenter', () => {
                video.play().catch(() => {});
            });
            card.addEventListener('mouseleave', () => {
                video.pause();
            });
        });
    }

    async init() {
        try {
            // Load initial reels from DOM data attribute or API
            const initialDataEl = document.getElementById('reelsInitialData');
            if (initialDataEl) {
                try {
                    this.reels = JSON.parse(initialDataEl.textContent || '[]');
                } catch (e) {
                    this.reels = [];
                }
            }

            if (!this.reels.length) {
                await this.fetchReels();
            }

            if (this.reels.length > 0) {
                const urlParams = new URLSearchParams(window.location.search);
                const targetId = urlParams.get('id');
                if (targetId) {
                    const targetIdx = this.reels.findIndex(r => r.id == targetId);
                    if (targetIdx !== -1) {
                        this.currentIndex = targetIdx;
                    }
                }
                this.renderCurrentReel();
            } else {
                this.renderEmptyState();
            }

            this.bindEvents();
        } catch (error) {
            console.error('[ReelsPlayer] Init failed:', error);
        }
    }

    async fetchReels() {
        try {
            const res = await axios.get('/api/reels/feed');
            if (res.data?.status === 'success') {
                this.reels = res.data.data || [];
            }
        } catch (error) {
            console.error('[ReelsPlayer] Failed to fetch reels:', error);
        }
    }

    bindEvents() {
        // Keyboard Controls
        window.addEventListener('keydown', (e) => {
            if (['input', 'textarea', 'select'].includes(document.activeElement?.tagName?.toLowerCase())) return;

            if (e.key === 'ArrowDown' || e.key === 'j') {
                e.preventDefault();
                this.nextReel();
            } else if (e.key === 'ArrowUp' || e.key === 'k') {
                e.preventDefault();
                this.prevReel();
            } else if (e.key === ' ' || e.code === 'Space') {
                e.preventDefault();
                this.togglePlayPause();
            } else if (e.key === 'm' || e.key === 'M') {
                this.toggleMute();
            } else if (e.key === 'l' || e.key === 'L') {
                this.spawnFloatingEmoji('❤️');
                this.toggleLike('like');
            } else if (e.key === 'f' || e.key === 'F') {
                this.toggleFitMode();
            }
        });

        // Touch Swipe & Mouse Wheel Gestures
        if (this.viewport) {
            this.viewport.addEventListener('touchstart', (e) => {
                this.touchStartY = e.changedTouches[0].screenY;
            }, { passive: true });

            this.viewport.addEventListener('touchend', (e) => {
                this.touchEndY = e.changedTouches[0].screenY;
                this.handleSwipe();
            }, { passive: true });

            this.viewport.addEventListener('wheel', (e) => {
                if (this.isCommentsOpen) return;
                const now = Date.now();
                if (now - this.lastWheelTime < 650) return;
                if (Math.abs(e.deltaY) > 35) {
                    this.lastWheelTime = now;
                    if (e.deltaY > 0) {
                        this.nextReel();
                    } else {
                        this.prevReel();
                    }
                }
            }, { passive: true });
        }
    }

    initCommentControls() {
        if (this.commentForm) {
            this.commentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitComment();
            });
        }

        if (this.gifToggleBtn && this.gifPickerDrawer) {
            this.gifToggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleGifPicker();
            });
        }

        if (this.closeGifPickerBtn && this.gifPickerDrawer) {
            this.closeGifPickerBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.gifPickerDrawer.classList.add('d-none');
            });
        }

        if (this.cancelReplyBtn) {
            this.cancelReplyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.cancelReply();
            });
        }

        if (this.removeAttachedGifBtn) {
            this.removeAttachedGifBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.removeAttachedGif();
            });
        }

        this.renderGifPicker();
    }

    handleSwipe() {
        const deltaY = this.touchStartY - this.touchEndY;
        if (deltaY > 50) {
            this.nextReel(); // Swiped Up -> Next Reel
        } else if (deltaY < -50) {
            this.prevReel(); // Swiped Down -> Prev Reel
        }
    }

    nextReel() {
        if (this.currentIndex < this.reels.length - 1) {
            this.currentIndex++;
            this.renderCurrentReel();
        } else {
            this.showToast('You have reached the end of family reels!', 'info');
        }
    }

    prevReel() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.renderCurrentReel();
        }
    }

    getMediaEmbed(videoUrl, aspectRatio = '9:16') {
        if (!videoUrl) {
            return '<div class="d-flex align-items-center justify-content-center h-100 text-white-50 small">No video source</div>';
        }

        const trimmed = videoUrl.trim();

        // YouTube Matcher (watch?v=, youtu.be/, shorts/, embed/)
        const ytRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;
        const ytMatch = trimmed.match(ytRegex);
        if (ytMatch && ytMatch[1]) {
            const videoId = ytMatch[1];
            return `
            <div class="reel-iframe-wrapper">
                <iframe 
                    id="reelActiveIframe"
                    class="reel-video-element"
                    src="https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1&mute=${this.isMuted ? 1 : 0}&loop=1&playlist=${videoId}&controls=1&modestbranding=1&rel=0&playsinline=1&enablejsapi=1"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen>
                </iframe>
            </div>
            `;
        }

        // Vimeo Matcher
        const vimeoRegex = /(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)/i;
        const vimeoMatch = trimmed.match(vimeoRegex);
        if (vimeoMatch && (vimeoMatch[3] || vimeoMatch[2])) {
            const vId = vimeoMatch[3] || vimeoMatch[2];
            return `
            <div class="reel-iframe-wrapper">
                <iframe 
                    id="reelActiveIframe"
                    class="reel-video-element"
                    src="https://player.vimeo.com/video/${vId}?autoplay=1&muted=${this.isMuted ? 1 : 0}&loop=1&title=0&byline=0&portrait=0"
                    frameborder="0"
                    allow="autoplay; fullscreen; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
            `;
        }

        const initialFitClass = (aspectRatio === '16:9' || aspectRatio === 'cinema') ? 'fit-cinema' : '';

        // Direct HTML5 Video File (MP4, WebM, MOV, server uploads) - no 'loop' attribute so 'ended' fires for continuous autoplay
        return `
        <video class="reel-video-element ${initialFitClass}" id="reelActiveVideo" playsinline webkit-playsinline preload="auto" ${this.isMuted ? 'muted' : ''} autoplay>
            <source src="${esc(trimmed)}" type="video/mp4">
            <source src="${esc(trimmed)}" type="video/webm">
            Your browser does not support HTML5 video.
        </video>
        `;
    }

    renderCurrentReel() {
        const reel = this.reels[this.currentIndex];
        if (!reel || !this.viewport) return;

        // Close comments drawer when switching
        if (this.isCommentsOpen) this.toggleComments(false);

        // Update URL query param silently so sharing/refreshing preserves active reel
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('id', String(reel.id));
            window.history.replaceState({}, '', url.toString());
        } catch (_) {}

        // All reel.* string fields are user-authored — escape before innerHTML (SEC-2).
        const fullName = esc(`${reel.firstName || 'Family'} ${reel.lastName || 'Member'}`.trim());
        const rawAvatar = reel.profilePics ? (reel.profilePics.startsWith('/') ? reel.profilePics : `/resources/images/profile/${reel.profilePics}`) : '/resources/images/profile/avatarM.png';
        const avatarUrl = esc(rawAvatar);
        const rawThumb = reel.thumbnail_url ? String(reel.thumbnail_url) : rawAvatar;
        const ambientThumbUrl = esc(rawThumb);
        const userReaction = reel.user_reaction || '';
        const isLiked = !!userReaction;
        const normalizedReelReaction = (userReaction === 'bless' || userReaction === 'proud') ? 'celebrate' : userReaction;
        const activeLikeContent = isLiked
            ? `<span class="d-inline-flex align-items-center justify-content-center heirloom-active-icon">${getHeirloomGlassSvg(normalizedReelReaction, 26)}</span>`
            : '<i class="bi bi-heart"></i>';
        const reelPickerItems = ['love', 'like', 'haha', 'shock', 'sad', 'celebrate'].map(type => {
            const meta = HEIRLOOM_REACTIONS[type] || HEIRLOOM_REACTIONS.love;
            return `<button type="button" class="reel-reaction-emoji-btn heirloom-reaction-btn ${normalizedReelReaction === type ? 'active' : ''}" data-reaction="${type}" title="${esc(meta.label)}" aria-label="${esc(meta.label)}" style="--heirloom-glow:${meta.glow};">
                <span class="d-inline-flex align-items-center justify-content-center">${getHeirloomGlassSvg(type, 30)}</span>
                <span class="heirloom-tooltip-pill">${esc(meta.label)}</span>
            </button>`;
        }).join('');
        const categoryLabel = esc(reel.category ? `#${String(reel.category).replace(/_/g, ' ').toUpperCase()}` : '#FAMILY');
        const mediaHtml = this.getMediaEmbed(reel.video_url, reel.aspect_ratio || '9:16');
        const isOwner = this.currentUserId && String(reel.user_id) === String(this.currentUserId);
        const totalReels = this.reels.length;

        // Author grouping for Instagram-style multi-segment progress & consecutive moments
        const currentAuthorId = String(reel.user_id || '');
        const authorReels = this.reels.filter(r => String(r.user_id || '') === currentAuthorId);
        const currentAuthorIndex = Math.max(0, authorReels.findIndex(r => String(r.id) === String(reel.id)));
        const authorReelsCount = authorReels.length;
        const posLabel = authorReelsCount > 1 
            ? `${currentAuthorIndex + 1} of ${authorReelsCount} moments`
            : `${this.currentIndex + 1} / ${totalReels}`;

        let segmentsHtml = '';
        if (authorReelsCount > 1) {
            segmentsHtml = `
            <div class="reel-progress-segments-container" id="reelProgressSegments" aria-label="Moments progress">
                ${authorReels.map((_, idx) => {
                    let fillClass = '';
                    if (idx < currentAuthorIndex) fillClass = 'is-past';
                    else if (idx > currentAuthorIndex) fillClass = 'is-future';
                    return `
                    <div class="reel-progress-segment-track" data-segment-idx="${idx}">
                        <div class="reel-progress-segment-fill ${fillClass}" id="segmentFill_${idx}"></div>
                    </div>`;
                }).join('')}
            </div>`;
        }

        this.viewport.innerHTML = `
        <div class="reel-card-container" id="activeReelCard">
            <!-- Instagram-Style Multi-Segment Progress Bars (Grouped Author Moments) -->
            ${segmentsHtml}

            <!-- Ambient Blurred Backdrop for Cinema Mode (Landscape 16:9 Videos) -->
            <div class="reel-ambient-backdrop" id="reelAmbientBackdrop" style="background-image: url('${ambientThumbUrl}');"></div>

            <!-- Video / Iframe Embed Media -->
            ${mediaHtml}

            <!-- Gradient Overlay -->
            <div class="reel-overlay"></div>

            <!-- Center Feedback Icon -->
            <div class="reel-center-feedback" id="reelCenterFeedback">
                <i class="bi bi-play-fill"></i>
            </div>

            <!-- Top Header Controls -->
            <div class="reel-top-controls">
                <div class="d-flex align-items-center gap-2">
                    <a href="/profilePage" class="reel-nav-pill-btn">
                        <i class="bi bi-chevron-left me-1"></i> Back
                    </a>
                    <span class="reel-nav-pill-btn" style="cursor: default; font-size: 0.74rem; padding: 5px 10px;" title="Reel position">
                        ${posLabel}
                    </span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="reel-nav-pill-btn" id="btnToggleFitMode" title="Toggle Cinema Fit / 9:16 Fill (F)">
                        <i class="bi bi-aspect-ratio"></i>
                    </button>
                    <button type="button" class="reel-nav-pill-btn" id="btnToggleMute" title="Mute / Unmute (M)">
                        <i class="bi ${this.isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}"></i>
                    </button>
                    ${isOwner ? `
                    <button type="button" class="reel-nav-pill-btn text-danger" id="btnDeleteReel" title="Delete Your Reel">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    ` : ''}
                    <button type="button" class="reel-nav-pill-btn" data-bs-toggle="modal" data-bs-target="#createReelModal">
                        <i class="bi bi-camera-video-fill me-1 text-warning"></i> Create
                    </button>
                </div>
            </div>

            <!-- Floating Action Stack (Right) -->
            <div class="reel-action-stack">
                <!-- Prev / Next Quick Chevrons (for Desktop & Elders) -->
                ${totalReels > 1 ? `
                <div class="d-flex flex-column align-items-center gap-1 mb-1">
                    <button type="button" class="reel-action-btn" id="btnPrevReel" style="width: 36px; height: 36px; font-size: 1rem;" title="Previous Reel (Up Arrow)" ${this.currentIndex === 0 ? 'disabled style="opacity:0.35;width:36px;height:36px;font-size:1rem;"' : ''}>
                        <i class="bi bi-chevron-up"></i>
                    </button>
                    <button type="button" class="reel-action-btn" id="btnNextReel" style="width: 36px; height: 36px; font-size: 1rem;" title="Next Reel (Down Arrow)" ${this.currentIndex >= totalReels - 1 ? 'disabled style="opacity:0.35;width:36px;height:36px;font-size:1rem;"' : ''}>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                ` : ''}

                <!-- Like & Celebratory Reaction Picker (Living Heirloom Glass 3D Dock) -->
                <div class="d-flex flex-column align-items-center position-relative" id="reelReactionWrapper">
                    <div class="reel-reaction-picker heirloom-glass-dock" id="reelReactionPicker">
                        ${reelPickerItems}
                    </div>
                    <button class="reel-action-btn ${isLiked ? 'liked' : ''}" id="btnReelLike" title="Tap to React • Hover or Hold for Living Heirloom Glass Reactions">
                        ${activeLikeContent}
                    </button>
                    <span class="reel-action-count" id="reelLikeCount">${reel.likes_count || 0}</span>
                </div>

                <!-- Comment Drawer Button -->
                <div class="d-flex flex-column align-items-center">
                    <button class="reel-action-btn" id="btnReelComments" title="Family Comments">
                        <i class="bi bi-chat-dots-fill"></i>
                    </button>
                    <span class="reel-action-count" id="reelCommentCount">${reel.comments_count || 0}</span>
                </div>

                <!-- Reel Views Indicator -->
                <div class="d-flex flex-column align-items-center" title="${reel.views_count || 0} family views">
                    <div class="reel-action-btn" style="cursor: default; pointer-events: none; opacity: 0.88;">
                        <i class="bi bi-eye-fill text-info"></i>
                    </div>
                    <span class="reel-action-count" id="reelActionViewCount">${reel.views_count || 0}</span>
                </div>

                <!-- Family Tree Node Jump -->
                <div class="d-flex flex-column align-items-center">
                    <a href="/organogram/${esc(String(reel.user_id || ''))}" class="reel-action-btn" title="View on Family Tree" style="text-decoration:none;">
                        <i class="bi bi-diagram-3-fill text-warning"></i>
                    </a>
                    <span class="reel-action-count">Tree</span>
                </div>

                <!-- Share Link -->
                <div class="d-flex flex-column align-items-center">
                    <button class="reel-action-btn" id="btnReelShare" title="Share Reel">
                        <i class="bi bi-share-fill"></i>
                    </button>
                    <span class="reel-action-count">Share</span>
                </div>
            </div>

            <!-- Bottom Metadata Overlay -->
            <div class="reel-bottom-meta">
                ${reel.chrono_sync_cluster_id ? `
                <div class="reel-chrono-sync-badge-container mb-2">
                    <button type="button" class="btn btn-sm btn-dark bg-opacity-75 text-white border border-warning rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnToggleChronoSync" title="Switch Multi-Angle Family Perspectives">
                        <i class="bi bi-camera-reels-fill text-warning"></i>
                        <span class="fw-bold text-truncate" style="max-width: 135px;">${esc(reel.chrono_sync_label || 'Perspective')}</span>
                        <span class="badge bg-warning text-dark rounded-pill px-1.5" style="font-size: 0.65rem;">Multi-Angle</span>
                        <i class="bi bi-chevron-down ms-0.5" style="font-size: 0.65rem;"></i>
                    </button>
                    <div class="reel-chrono-sync-dropdown d-none position-absolute bottom-100 start-0 mb-1 p-2 shadow-lg" id="reelChronoSyncDropdown" style="min-width: 220px; z-index: 25;">
                        <div class="text-white-50 small fw-bold px-1 mb-1" style="font-size: 0.68rem; text-transform: uppercase;">
                            <i class="bi bi-camera-video me-1 text-warning"></i> Family Event Angles
                        </div>
                        <div class="d-flex flex-column gap-1" id="reelChronoSyncList">
                            <div class="text-center py-2"><div class="spinner-border spinner-border-sm text-warning" role="status"></div></div>
                        </div>
                    </div>
                </div>
                ` : ''}

                <div class="reel-author-row">
                    <img src="${avatarUrl}" alt="${fullName}" class="reel-author-avatar" onerror="this.src='/resources/images/profile/avatarM.png'">
                    <div>
                        <div class="reel-author-name">${fullName}</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="reel-tag-pill"><i class="bi bi-stars text-warning"></i> ${categoryLabel}</span>
                            <span class="reel-tag-pill" id="reelViewsPill" title="Family views"><i class="bi bi-eye-fill text-info me-1"></i><span id="reelViewCount">${reel.views_count || 0}</span> views</span>
                        </div>
                    </div>
                </div>

                <div class="reel-caption-text">${esc(reel.caption || '')}</div>

                <div class="reel-music-tag d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-1.5 text-truncate" style="max-width: 72%;">
                        <div class="reel-audio-eq ${!this.isMuted ? 'playing' : ''}">
                            <span></span><span></span><span></span>
                        </div>
                        <i class="bi bi-music-note-beamed text-warning"></i>
                        <span class="text-truncate" title="${esc(reel.music_title || 'Original Family Audio')}">${esc(reel.music_title || 'Original Family Audio')} • ${reel.created_at ? format(reel.created_at) : 'Recent'}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-light btn-use-sound rounded-pill shadow-sm px-2.5 py-1 d-inline-flex align-items-center gap-1"
                            data-sound-title="${esc(reel.music_title || 'Original Family Audio')}"
                            data-sound-url="${esc(reel.audio_url || reel.video_url || '')}"
                            data-creator-name="${fullName}"
                            style="font-size: 0.72rem; min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;"
                            title="Create a Reel using this soundtrack">
                        <i class="bi bi-disc text-primary"></i>
                        <span class="fw-semibold">Use Sound</span>
                    </button>
                </div>
            </div>

            <!-- Interactive Video Progress Scrubber -->
            <div class="reel-progress-track" id="reelProgressTrack" title="Click to seek">
                <div class="reel-progress-fill" id="reelProgressFill"></div>
            </div>
        </div>
        `;

        // Record view count once per session for this reel
        this.recordViewCount(reel.id);

        // Auxiliary Audio Engine for Dual-Track Soundtrack Playback
        if (!this.auxAudio) {
            this.auxAudio = document.createElement('audio');
            this.auxAudio.id = 'reelAuxAudio';
            this.auxAudio.preload = 'auto';
            document.body.appendChild(this.auxAudio);
        }

        const audioUrl = reel.audio_url || null;
        const videoVolumeRatio = (typeof reel.video_volume !== 'undefined' ? Number(reel.video_volume) : 100) / 100;
        const musicVolumeRatio = (typeof reel.music_volume !== 'undefined' ? Number(reel.music_volume) : 40) / 100;
        const audioStartOffset = parseFloat(reel.audio_start_sec) || 0.0;

        if (audioUrl) {
            this.auxAudio.src = audioUrl;
            this.auxAudio.muted = this.isMuted;
            this.auxAudio.volume = Math.max(0, Math.min(1, musicVolumeRatio));
            try {
                this.auxAudio.currentTime = audioStartOffset;
            } catch (_) {}
            if (!this.isMuted) {
                this.auxAudio.play().catch(() => {});
            }
        } else {
            this.auxAudio.pause();
            this.auxAudio.removeAttribute('src');
        }

        // Wire video play / pause, smart aspect detection & progress bar
        const video = document.getElementById('reelActiveVideo');
        const progressTrack = document.getElementById('reelProgressTrack');
        const progressFill = document.getElementById('reelProgressFill');
        const ambientBackdrop = document.getElementById('reelAmbientBackdrop');

        if (video) {
            video.muted = this.isMuted;
            video.volume = Math.max(0, Math.min(1, videoVolumeRatio));

            video.addEventListener('play', () => {
                if (audioUrl && this.auxAudio) {
                    this.auxAudio.muted = this.isMuted;
                    this.auxAudio.play().catch(() => {});
                }
            });

            video.addEventListener('pause', () => {
                if (this.auxAudio) {
                    this.auxAudio.pause();
                }
            });

            video.addEventListener('seeking', () => {
                if (audioUrl && this.auxAudio) {
                    try {
                        this.auxAudio.currentTime = audioStartOffset + (video.currentTime % (this.auxAudio.duration || video.duration || 10));
                    } catch (_) {}
                }
            });

            video.addEventListener('ended', () => {
                if (audioUrl && this.auxAudio) {
                    try {
                        this.auxAudio.currentTime = audioStartOffset;
                        this.auxAudio.play().catch(() => {});
                    } catch (_) {}
                }
            });
            const detectAndApplyAspect = () => {
                const vw = video.videoWidth || 0;
                const vh = video.videoHeight || 0;
                if (vw <= 0 || vh <= 0) return;

                const isLandscape = vw > vh;
                if (this.fitMode === 'cinema' || (this.fitMode === 'auto' && isLandscape)) {
                    video.classList.add('fit-cinema');
                } else {
                    video.classList.remove('fit-cinema');
                }

                // Extract a clean un-stretched frame for the ambient backdrop and self-heal server thumb if needed
                try {
                    const offCanvas = document.createElement('canvas');
                    const rendered = FamilyReelsPlayer.renderVideoFrameToCanvas(video, offCanvas, 'auto');
                    if (rendered && rendered.dataUrl && ambientBackdrop) {
                        ambientBackdrop.style.backgroundImage = `url('${rendered.dataUrl}')`;
                        if (isOwner && isLandscape && (reel.aspect_ratio || '9:16') === '9:16') {
                            reel.aspect_ratio = '16:9';
                            axios.post('/api/reels/thumbnail', {
                                reel_id: Number(reel.id),
                                thumbnail_data: rendered.dataUrl,
                                aspect_ratio: '16:9'
                            }).catch(() => {});
                        }
                    }
                } catch (_) {}
            };

            if (video.readyState >= 1) {
                detectAndApplyAspect();
            } else {
                video.addEventListener('loadedmetadata', detectAndApplyAspect, { once: true });
                video.addEventListener('loadeddata', detectAndApplyAspect, { once: true });
            }

            video.play().catch(() => {
                this.isMuted = true;
                video.muted = true;
                video.play().catch(console.error);
            });

            // Progress Scrubber Update (Single + Multi-Segment Instagram Bars) & Continuous Autoplay Fallback
            let autoAdvanced = false;
            video.addEventListener('timeupdate', () => {
                if (video.duration > 0) {
                    const pct = Math.min(100, Math.max(0, (video.currentTime / video.duration) * 100));
                    if (progressFill) {
                        progressFill.style.width = `${pct}%`;
                    }
                    if (authorReelsCount > 1) {
                        const activeSegment = document.getElementById(`segmentFill_${currentAuthorIndex}`);
                        if (activeSegment) {
                            activeSegment.style.width = `${pct}%`;
                        }
                    }

                    // Autoplay threshold fallback if 'ended' event is delayed by edge mobile decoders
                    if (!autoAdvanced && video.currentTime >= video.duration - 0.25 && video.duration > 1.5) {
                        autoAdvanced = true;
                        if (this.currentIndex < this.reels.length - 1) {
                            this.nextReel();
                        } else {
                            this.showToast('You have reached the end of family reels!', 'info');
                        }
                    }
                }
            });

            // Autoplay next reel till the end of the entire playlist when current reel finishes
            video.addEventListener('ended', () => {
                if (!autoAdvanced) {
                    autoAdvanced = true;
                    if (this.currentIndex < this.reels.length - 1) {
                        this.nextReel();
                    } else {
                        this.showToast('You have reached the end of family reels!', 'info');
                    }
                }
            });

            if (progressTrack) {
                progressTrack.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (!video.duration) return;
                    const rect = progressTrack.getBoundingClientRect();
                    const ratio = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
                    video.currentTime = ratio * video.duration;
                });
            }

            // Tap navigation: Left 22% = Prev, Right 22% = Next, Center = Play/Pause
            video.addEventListener('click', (e) => {
                const rect = video.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const width = rect.width;

                if (clickX < width * 0.22) {
                    this.prevReel();
                } else if (clickX > width * 0.78) {
                    this.nextReel();
                } else {
                    this.togglePlayPause();
                }
            });

            // Desktop double-click to heart with pop animation
            video.addEventListener('dblclick', (e) => {
                if (this.viewport) {
                    const rect = this.viewport.getBoundingClientRect();
                    this.spawnDoubleTapHeart(e.clientX - rect.left, e.clientY - rect.top);
                } else {
                    this.spawnFloatingEmoji('❤️');
                }
                this.toggleLike('like');
            });

            // Mobile double-tap to heart with emoji burst
            let lastTap = 0;
            video.addEventListener('touchend', (e) => {
                const currentTime = new Date().getTime();
                const tapLength = currentTime - lastTap;
                if (tapLength < 300 && tapLength > 0) {
                    const touch = e.changedTouches && e.changedTouches[0];
                    if (touch && this.viewport) {
                        const rect = this.viewport.getBoundingClientRect();
                        this.spawnDoubleTapHeart(touch.clientX - rect.left, touch.clientY - rect.top);
                    } else {
                        this.spawnFloatingEmoji('❤️');
                    }
                    this.toggleLike('like');
                    e.preventDefault();
                }
                lastTap = currentTime;
            });
        }

        // Multi-Angle Chrono-Sync Angle Switcher
        const btnToggleChrono = document.getElementById('btnToggleChronoSync');
        const chronoDropdown = document.getElementById('reelChronoSyncDropdown');
        const chronoList = document.getElementById('reelChronoSyncList');

        if (btnToggleChrono && chronoDropdown && chronoList && reel.chrono_sync_cluster_id) {
            btnToggleChrono.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = chronoDropdown.classList.contains('d-none');
                chronoDropdown.classList.toggle('d-none', !isHidden);
                if (isHidden) {
                    chronoList.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-warning" role="status"></div></div>';
                    axios.get(`/api/reels/chrono-sync-angles?cluster_id=${encodeURIComponent(reel.chrono_sync_cluster_id)}&exclude_id=${reel.id}`)
                        .then(res => {
                            const angles = res.data?.data || [];
                            chronoList.innerHTML = '';

                            // Self (Active angle)
                            const selfItem = document.createElement('div');
                            selfItem.className = 'reel-chrono-sync-item active';
                            selfItem.innerHTML = `
                                <img src="${ambientThumbUrl}" class="reel-chrono-sync-thumb" alt="Thumb">
                                <div class="flex-grow-1 text-truncate">
                                    <div>${esc(reel.chrono_sync_label || 'Current Angle')} <span class="badge bg-warning text-dark ms-1" style="font-size:0.6rem;">Now Playing</span></div>
                                    <div class="text-white-50" style="font-size:0.68rem;">By ${fullName}</div>
                                </div>
                            `;
                            chronoList.appendChild(selfItem);

                            if (angles.length === 0) {
                                const emptyNote = document.createElement('div');
                                emptyNote.className = 'text-white-50 px-2 py-1 small';
                                emptyNote.style.fontSize = '0.72rem';
                                emptyNote.textContent = 'No other angles linked yet.';
                                chronoList.appendChild(emptyNote);
                            } else {
                                angles.forEach(ang => {
                                    const angItem = document.createElement('div');
                                    angItem.className = 'reel-chrono-sync-item';
                                    const angCreator = esc(`${ang.firstName || ''} ${ang.lastName || ''}`.trim() || 'Relative');
                                    const angThumb = ang.thumbnail_url ? esc(String(ang.thumbnail_url)) : avatarUrl;
                                    angItem.innerHTML = `
                                        <img src="${angThumb}" class="reel-chrono-sync-thumb" alt="Thumb">
                                        <div class="flex-grow-1 text-truncate">
                                            <div>${esc(ang.chrono_sync_label || 'Perspective')}</div>
                                            <div class="text-white-50" style="font-size:0.68rem;">By ${angCreator}</div>
                                        </div>
                                        <i class="bi bi-play-circle-fill text-warning ms-1"></i>
                                    `;
                                    angItem.addEventListener('click', (ev) => {
                                        ev.stopPropagation();
                                        this.switchAnglePerspective(ang);
                                    });
                                    chronoList.appendChild(angItem);
                                });
                            }
                        })
                        .catch(() => {
                            chronoList.innerHTML = '<div class="text-danger small px-2 py-1">Failed to load angles</div>';
                        });
                }
            });

            document.addEventListener('click', () => {
                chronoDropdown.classList.add('d-none');
            }, { once: false });
        }

        // Attach listeners for top & right action buttons
        document.getElementById('btnToggleFitMode')?.addEventListener('click', () => this.toggleFitMode());
        document.getElementById('btnToggleMute')?.addEventListener('click', () => this.toggleMute());
        document.getElementById('btnDeleteReel')?.addEventListener('click', () => this.deleteCurrentReel());
        document.getElementById('btnPrevReel')?.addEventListener('click', () => this.prevReel());
        document.getElementById('btnNextReel')?.addEventListener('click', () => this.nextReel());

        // Use This Sound Click Handler (Viral Sound Creation)
        const useSoundBtn = document.querySelector('.btn-use-sound');
        if (useSoundBtn) {
            useSoundBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const soundTitle = useSoundBtn.getAttribute('data-sound-title') || 'Original Family Audio';
                const soundUrl = useSoundBtn.getAttribute('data-sound-url') || '';
                const creatorName = useSoundBtn.getAttribute('data-creator-name') || 'Family';

                // Pre-populate creation modal
                const modal = document.getElementById('createReelModal');
                const titleInput = document.getElementById('reelMusicTitleInput');
                const audioUrlInput = document.getElementById('reelAudioUrlInput');
                const activeSoundLabel = document.getElementById('reelActiveSoundLabel');
                const dualControls = document.getElementById('reelDualTrackControls');

                let effectiveTitle = soundTitle;
                if (!reel.audio_url && soundTitle.includes('Original')) {
                    effectiveTitle = `Original Audio by ${creatorName}`;
                }

                if (titleInput) titleInput.value = effectiveTitle;
                if (audioUrlInput) audioUrlInput.value = soundUrl;
                if (activeSoundLabel) activeSoundLabel.textContent = `Using Sound: ${effectiveTitle}`;
                if (dualControls) dualControls.classList.remove('d-none');

                // Launch modal
                if (typeof bootstrap !== 'undefined' && modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                    bsModal.show();
                } else if (modal) {
                    modal.classList.add('show');
                    modal.style.display = 'block';
                }

                this.showToast(`Soundtrack "${effectiveTitle}" loaded! Now choose your video.`, 'success');
            });
        }

        // Reaction button + picker hover/long-press
        const likeBtn = document.getElementById('btnReelLike');
        const reactionWrapper = document.getElementById('reelReactionWrapper');
        const reactionPicker = document.getElementById('reelReactionPicker');

        if (likeBtn) {
            likeBtn.addEventListener('click', (e) => {
                const defaultReact = reel.user_reaction || 'love';
                this.spawnFloatingEmoji(defaultReact);
                triggerHeirloomBurst(defaultReact, e);
                this.toggleLike(defaultReact);
            });
        }
        if (reactionWrapper && reactionPicker) {
            reactionWrapper.addEventListener('mouseenter', () => reactionPicker.classList.add('open'));
            reactionWrapper.addEventListener('mouseleave', () => reactionPicker.classList.remove('open'));
            reactionPicker.querySelectorAll('[data-reaction]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const rType = btn.getAttribute('data-reaction') || 'love';
                    this.spawnFloatingEmoji(rType);
                    triggerHeirloomBurst(rType, e);
                    reactionPicker.classList.remove('open');
                    this.toggleLike(rType);
                });
            });
        }

        document.getElementById('btnReelComments')?.addEventListener('click', () => this.toggleComments(true));
        document.getElementById('btnReelShare')?.addEventListener('click', () => this.shareReel(reel));

        // Speculative pre-buffering (<180ms TTFF for subsequent reels)
        this.prebufferNextReel();
    }

    /**
     * Switch seamlessly between multi-angle perspectives of the same family event.
     */
    switchAnglePerspective(angleData) {
        const video = document.getElementById('reelActiveVideo');
        const savedTime = video && video.currentTime ? video.currentTime : 0;
        const currentReel = this.reels[this.currentIndex];

        if (currentReel && angleData.video_url) {
            currentReel.video_url = angleData.video_url;
            currentReel.thumbnail_url = angleData.thumbnail_url;
            currentReel.chrono_sync_label = angleData.chrono_sync_label;

            if (video) {
                video.src = angleData.video_url;
                video.currentTime = savedTime;
                video.play().catch(() => {});
            }

            const btnLabel = document.querySelector('#btnToggleChronoSync span.fw-bold');
            if (btnLabel) {
                btnLabel.textContent = angleData.chrono_sync_label || 'Perspective';
            }

            document.getElementById('reelChronoSyncDropdown')?.classList.add('d-none');
            this.showToast(`Switched perspective to ${angleData.chrono_sync_label || 'Angle'}`);
        }
    }

    /**
     * Spawn an animated 3D Living Heirloom Glass heart burst on double-tap or double-click.
     */
    spawnDoubleTapHeart(x, y) {
        if (!this.viewport) return;
        const burst = document.createElement('div');
        burst.className = 'reel-doubletap-burst';
        burst.style.left = `${Math.round(x)}px`;
        burst.style.top = `${Math.round(y)}px`;
        burst.innerHTML = getHeirloomGlassSvg('love', 68);
        this.viewport.appendChild(burst);
        const vpRect = this.viewport.getBoundingClientRect();
        triggerHeirloomBurst('love', { clientX: vpRect.left + x, clientY: vpRect.top + y });
        setTimeout(() => burst.remove(), 900);
    }

    /**
     * Meta-grade Speculative Pre-buffer (<180ms TTFF): Preloads metadata and initial video
     * chunks for the next reel in line so next-swipe transitions are instant.
     */
    prebufferNextReel() {
        const nextReel = this.reels[this.currentIndex + 1];
        if (!nextReel || !nextReel.video_url) return;

        const isDirect = /\.(mp4|webm|mov|mkv|ogv)(\?.*)?$/i.test(nextReel.video_url) || nextReel.video_url.startsWith('/resources/videos/reels/');
        if (!isDirect) return;

        if (!this.prebufferVideo) {
            this.prebufferVideo = document.createElement('video');
            this.prebufferVideo.preload = 'auto';
            this.prebufferVideo.muted = true;
            this.prebufferVideo.playsInline = true;
            this.prebufferVideo.style.display = 'none';
            document.body.appendChild(this.prebufferVideo);
        }

        if (this.prebufferVideo.src !== nextReel.video_url) {
            this.prebufferVideo.src = nextReel.video_url;
            this.prebufferVideo.load();
        }
    }

    toggleFitMode() {
        const video = document.getElementById('reelActiveVideo');
        if (!video) return;
        const isCurrentlyCinema = video.classList.contains('fit-cinema');
        if (isCurrentlyCinema) {
            video.classList.remove('fit-cinema');
            this.fitMode = 'cover';
            this.showToast('Switched to 9:16 Full-Screen Fill', 'info');
        } else {
            video.classList.add('fit-cinema');
            this.fitMode = 'cinema';
            this.showToast('Switched to Uncropped Cinema Fit', 'info');
        }
    }

    spawnFloatingEmoji(reactionType = 'love') {
        const card = document.getElementById('activeReelCard');
        if (!card) return;
        const el = document.createElement('div');
        el.className = 'reel-floating-emoji';
        const svg = getHeirloomGlassSvg(reactionType, 40);
        if (svg) {
            el.innerHTML = svg;
        } else {
            el.textContent = reactionType;
        }
        card.appendChild(el);
        setTimeout(() => el.remove(), 1150);
    }

    async recordViewCount(reelId) {
        if (!reelId || this.viewedReelIds.has(reelId)) return;
        this.viewedReelIds.add(reelId);
        try {
            const res = await axios.post('/api/reels/view', { reel_id: reelId });
            if (res.data?.status === 'success' && typeof res.data.views_count === 'number') {
                const reel = this.reels.find(r => r.id == reelId);
                if (reel) reel.views_count = res.data.views_count;
                const viewEl = document.getElementById('reelViewCount');
                if (viewEl) viewEl.textContent = String(res.data.views_count);
                const actionViewEl = document.getElementById('reelActionViewCount');
                if (actionViewEl) actionViewEl.textContent = String(res.data.views_count);
            }
        } catch (_) {}
    }

    async deleteCurrentReel() {
        const reel = this.reels[this.currentIndex];
        if (!reel) return;

        if (typeof Swal !== 'undefined') {
            const confirm = await Swal.fire({
                icon: 'warning',
                title: 'Delete Family Reel?',
                text: 'This reel and its comments will be permanently removed.',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Delete'
            });
            if (!confirm.isConfirmed) return;
        }

        try {
            const res = await axios.post('/api/reels/delete', { reel_id: reel.id });
            if (res.data?.status === 'success') {
                this.reels.splice(this.currentIndex, 1);
                this.showToast('Reel deleted.', 'success');
                if (this.reels.length === 0) {
                    this.renderEmptyState();
                } else {
                    if (this.currentIndex >= this.reels.length) {
                        this.currentIndex = this.reels.length - 1;
                    }
                    this.renderCurrentReel();
                }
            }
        } catch (error) {
            this.showToast(error.response?.data?.message || 'Could not delete reel.', 'error');
        }
    }

    renderEmptyState() {
        if (!this.viewport) return;
        this.viewport.innerHTML = `
        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center p-4 text-white">
            <div class="mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: rgba(255,255,255,0.1); font-size: 2rem;">
                <i class="bi bi-camera-reels text-warning"></i>
            </div>
            <h4 class="fw-bold mb-2">No Family Reels Yet</h4>
            <p class="text-white-50 small mb-4" style="max-width: 280px;">
                Be the first to record a family milestone, memory, or oral history video!
            </p>
            <button type="button" class="btn btn-primary fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#createReelModal" style="border-radius: 9999px;">
                <i class="bi bi-plus-circle-fill me-1"></i> Record First Reel
            </button>
        </div>
        `;
    }

    togglePlayPause() {
        const video = document.getElementById('reelActiveVideo');
        const feedback = document.getElementById('reelCenterFeedback');
        if (!video) return;

        if (video.paused) {
            video.play();
            if (this.auxAudio && this.auxAudio.src && !this.isMuted) {
                this.auxAudio.play().catch(() => {});
            }
            if (feedback) {
                feedback.innerHTML = '<i class="bi bi-play-fill"></i>';
                feedback.classList.add('show');
                setTimeout(() => feedback.classList.remove('show'), 300);
            }
        } else {
            video.pause();
            if (this.auxAudio) {
                this.auxAudio.pause();
            }
            if (feedback) {
                feedback.innerHTML = '<i class="bi bi-pause-fill"></i>';
                feedback.classList.add('show');
                setTimeout(() => feedback.classList.remove('show'), 300);
            }
        }
    }

    toggleMute() {
        this.isMuted = !this.isMuted;
        const video = document.getElementById('reelActiveVideo');
        const muteBtn = document.getElementById('btnToggleMute');
        const eq = document.querySelector('.reel-audio-eq');
        if (video) video.muted = this.isMuted;
        if (this.auxAudio) {
            this.auxAudio.muted = this.isMuted;
            if (!this.isMuted && video && !video.paused) {
                this.auxAudio.play().catch(() => {});
            }
        }
        if (muteBtn) {
            muteBtn.innerHTML = `<i class="bi ${this.isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}"></i>`;
        }
        if (eq) {
            eq.classList.toggle('playing', !this.isMuted);
        }
    }

    async toggleLike(reactionType = 'love') {
        const reel = this.reels[this.currentIndex];
        if (!reel) return;

        const likeBtn = document.getElementById('btnReelLike');
        const likeCount = document.getElementById('reelLikeCount');

        try {
            const res = await axios.post('/api/reels/react', {
                reel_id: reel.id,
                reaction_type: reactionType
            });

            if (res.data?.status === 'success') {
                const count = res.data.count;
                reel.likes_count = count;
                reel.user_reaction = res.data.reaction;

                if (likeCount) likeCount.textContent = count;
                if (likeBtn) {
                    if (res.data.action === 'added' || res.data.action === 'updated') {
                        likeBtn.classList.add('liked');
                        const normReact = (res.data.reaction === 'bless' || res.data.reaction === 'proud') ? 'celebrate' : (res.data.reaction || 'love');
                        likeBtn.innerHTML = `<span class="d-inline-flex align-items-center justify-content-center heirloom-active-icon">${getHeirloomGlassSvg(normReact, 26)}</span>`;
                    } else {
                        likeBtn.classList.remove('liked');
                        likeBtn.innerHTML = '<i class="bi bi-heart"></i>';
                    }
                }
            }
        } catch (error) {
            console.error('[ReelsPlayer] Reaction error:', error);
        }
    }

    async toggleComments(show = true) {
        this.isCommentsOpen = show;
        if (!this.commentsDrawer) return;

        if (show) {
            this.commentsDrawer.classList.add('open');
            await this.loadComments();
        } else {
            this.commentsDrawer.classList.remove('open');
        }
    }

    normalizeEmoji(str) {
        if (!str || typeof str !== 'string') return '';
        return str
            .replace(/\u2764(?!\uFE0F)/g, '\u2764\uFE0F')
            .replace(/\u2665(?!\uFE0F)/g, '\u2764\uFE0F')
            .replace(/(^|\s)<3(?=\s|$|[.,!?])/g, '$1❤️');
    }

    async loadComments() {
        const reel = this.reels[this.currentIndex];
        if (!reel || !this.commentsList) return;

        this.commentsList.innerHTML = '<div class="text-center py-4 text-white-50"><span class="spinner-border spinner-border-sm me-2"></span>Loading comments...</div>';

        try {
            const res = await axios.get(`/api/reels/comments?reel_id=${reel.id}`);
            const comments = res.data?.data || [];

            if (!comments.length) {
                this.commentsList.innerHTML = '<div class="text-center py-4 text-white-50 small">No comments yet. Leave the first family note!</div>';
                return;
            }

            // Organize 1-level threads: parents and replies
            const topLevel = [];
            const replyMap = new Map();
            comments.forEach(c => {
                if (c.parent_comment_no) {
                    const pid = Number(c.parent_comment_no);
                    if (!replyMap.has(pid)) replyMap.set(pid, []);
                    replyMap.get(pid).push(c);
                } else {
                    topLevel.push(c);
                }
            });

            let html = '';
            topLevel.forEach(parent => {
                html += `<div class="reel-comment-thread mb-3" data-root-id="${parent.comment_id}">`;
                html += this.renderCommentItem(parent, false, reel);
                const replies = replyMap.get(Number(parent.comment_id)) || [];
                if (replies.length > 0) {
                    html += `<div class="reel-comment-reply-container">`;
                    replies.forEach(rep => {
                        html += this.renderCommentItem(rep, true, reel);
                    });
                    html += `</div>`;
                }
                html += `</div>`;
            });

            this.commentsList.innerHTML = html;
            this.bindCommentEvents(reel);
        } catch (error) {
            this.commentsList.innerHTML = '<div class="text-center py-4 text-danger small">Failed to load comments.</div>';
        }
    }

    renderCommentItem(c, isReply, reel) {
        const isAuthor = this.currentUserId && String(c.user_id) === String(this.currentUserId);
        const isReelOwner = this.currentUserId && String(reel.user_id) === String(this.currentUserId);
        const canDelete = isAuthor || isReelOwner;
        const deleteTitle = isAuthor ? 'Delete your comment' : 'Delete comment from your reel';
        const authorName = `${c.firstName || 'Family'} ${c.lastName || 'Member'}`.trim();
        const avatarUrl = c.profilePics ? (c.profilePics.startsWith('/') ? c.profilePics : `/resources/images/profile/${c.profilePics}`) : '/resources/images/profile/avatarM.png';

        // Top reactions preview (Living Heirloom Glass 3D)
        const reactions = c.reactions || {};
        const topReactions = Object.entries(reactions)
            .filter(([k, v]) => !['total', 'comment_id'].includes(k) && Number(v) > 0)
            .sort(([, a], [, b]) => Number(b) - Number(a))
            .slice(0, 3)
            .map(([k, v]) => ({
                svg: getHeirloomGlassSvg(k, 20),
                count: v
            }));

        const activeReaction = c.user_reaction || '';
        const likeLabel = activeReaction ? (REEL_REACTION_LABELS[activeReaction] || (activeReaction.charAt(0).toUpperCase() + activeReaction.slice(1))) : 'Like';
        const likeSvg = activeReaction ? getHeirloomGlassSvg(activeReaction, 22) : '';
        const likeColor = activeReaction ? (REEL_REACTION_COLORS[activeReaction] || '#38bdf8') : '';

        return `
        <div class="reel-comment-bubble" data-comment-id="${c.comment_id}">
            <img src="${esc(avatarUrl)}" class="reel-comment-avatar" alt="${esc(authorName)}" onerror="this.src='/resources/images/profile/avatarM.png'">
            <div class="reel-comment-content flex-grow-1">
                <div class="d-flex align-items-center justify-content-between gap-1">
                    <div class="reel-comment-user">${esc(authorName)}</div>
                    ${canDelete ? `
                    <button type="button" class="btn btn-link text-danger p-0 delete-reel-comment-btn" data-comment-id="${c.comment_id}" title="${esc(deleteTitle)}" style="font-size: 0.82rem; text-decoration: none; opacity: 0.75; min-width: 32px; min-height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-trash3"></i>
                    </button>
                    ` : ''}
                </div>

                <div class="reel-comment-text-box" id="reelCommentTextBox_${c.comment_id}">
                    ${c.comment ? `<div class="reel-comment-text" style="font-variant-emoji: emoji;">${esc(this.normalizeEmoji(c.comment))}</div>` : ''}
                    ${c.gif_url ? `<img src="${esc(c.gif_url)}" alt="GIF" loading="lazy" class="reel-comment-gif-img">` : ''}
                </div>

                ${topReactions.length > 0 ? `
                <div class="reel-comment-reaction-pill d-inline-flex align-items-center gap-1.5" data-comment-id="${c.comment_id}">
                    ${topReactions.map(r => `<span class="d-inline-flex align-items-center gap-1">${r.svg} <span style="color:#94a3b8; font-size:0.7rem;">${r.count}</span></span>`).join(' ')}
                </div>
                ` : ''}

                <div class="reel-comment-meta-bar position-relative">
                    <span class="reel-comment-time">${c.created_at ? format(c.created_at) : 'Just now'}${c.updated_at ? ' <span class="text-white-50">(edited)</span>' : ''}</span>
                    
                    <!-- Like Button & Living Heirloom Glass Reaction Bar -->
                    <div class="position-relative d-inline-block">
                        <button type="button" class="reel-comment-action-btn reel-comment-react-toggle d-inline-flex align-items-center gap-1 ${activeReaction ? 'fw-bold' : ''}" style="${likeColor ? `color:${likeColor};` : ''}" data-comment-id="${c.comment_id}" data-active-reaction="${esc(activeReaction)}">
                            ${likeSvg ? `<span class="d-inline-flex align-items-center justify-content-center">${likeSvg}</span>` : ''}<span>${esc(likeLabel)}</span>
                        </button>
                        <div class="reel-comment-reaction-picker heirloom-glass-dock d-none" id="reactionPicker_${c.comment_id}">
                            ${Object.keys(REEL_COMMENT_EMOJI_MAP).map(type => {
                                const meta = HEIRLOOM_REACTIONS[type] || HEIRLOOM_REACTIONS.love;
                                return `
                                <button type="button" class="reel-comment-reaction-emoji-btn heirloom-reaction-btn" data-comment-id="${c.comment_id}" data-reaction-type="${type}" title="${esc(meta.label)}" style="--heirloom-glow:${meta.glow}; min-width:44px; min-height:44px; padding:4px;">
                                    <span class="d-inline-flex align-items-center justify-content-center">${getHeirloomGlassSvg(type, 34)}</span>
                                </button>
                                `;
                            }).join('')}
                        </div>
                    </div>

                    <!-- Reply Button -->
                    <button type="button" class="reel-comment-action-btn reel-comment-reply-btn" data-comment-id="${c.comment_id}" data-author-name="${esc(authorName)}">
                        Reply
                    </button>

                    <!-- Edit Button (Author only) -->
                    ${isAuthor && c.comment ? `
                    <button type="button" class="reel-comment-action-btn reel-comment-edit-btn" data-comment-id="${c.comment_id}" data-current-text="${esc(c.comment)}">
                        Edit
                    </button>
                    ` : ''}
                </div>
            </div>
        </div>
        `;
    }

    bindCommentEvents(reel) {
        if (!this.commentsList) return;

        // 1. Delete comment
        this.commentsList.querySelectorAll('.delete-reel-comment-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const commentId = btn.getAttribute('data-comment-id');
                if (commentId) {
                    this.deleteReelComment(commentId);
                }
            });
        });

        // 2. Reply to comment
        this.commentsList.querySelectorAll('.reel-comment-reply-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const commentId = btn.getAttribute('data-comment-id');
                const authorName = btn.getAttribute('data-author-name') || 'Family Member';
                if (commentId) {
                    this.startReply(commentId, authorName);
                }
            });
        });

        // 3. Edit comment
        this.commentsList.querySelectorAll('.reel-comment-edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const commentId = btn.getAttribute('data-comment-id');
                const currentText = btn.getAttribute('data-current-text') || '';
                if (commentId) {
                    this.startEdit(commentId, currentText);
                }
            });
        });

        // 4. Reaction toggle button (click toggles default 'like' or toggles picker)
        this.commentsList.querySelectorAll('.reel-comment-react-toggle').forEach(btn => {
            const commentId = btn.getAttribute('data-comment-id');
            const picker = document.getElementById(`reactionPicker_${commentId}`);

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const active = btn.getAttribute('data-active-reaction');
                if (!active) {
                    triggerHeirloomBurst('like', e);
                }
                // If already reacted, clicking the button toggles that same reaction off
                this.toggleCommentReaction(commentId, active || 'like');
            });

            // On mouseenter or long-press, show picker
            btn.addEventListener('mouseenter', () => {
                if (picker) picker.classList.remove('d-none');
            });

            const parentDiv = btn.parentElement;
            if (parentDiv) {
                parentDiv.addEventListener('mouseleave', () => {
                    if (picker) picker.classList.add('d-none');
                });
            }
        });

        // 5. Reaction emoji buttons inside picker
        this.commentsList.querySelectorAll('.reel-comment-reaction-emoji-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const commentId = btn.getAttribute('data-comment-id');
                const reactionType = btn.getAttribute('data-reaction-type') || 'like';
                const picker = document.getElementById(`reactionPicker_${commentId}`);
                if (picker) picker.classList.add('d-none');
                triggerHeirloomBurst(reactionType, e);
                if (commentId) {
                    this.toggleCommentReaction(commentId, reactionType);
                }
            });
        });
    }

    startReply(commentId, authorName) {
        this.replyTarget = { id: Number(commentId), name: authorName };
        if (this.replyTargetName) this.replyTargetName.textContent = `Replying to ${authorName}`;
        if (this.replyBadge) this.replyBadge.classList.remove('d-none');
        if (this.commentAuxBar) this.commentAuxBar.classList.remove('d-none');
        if (this.commentInput) {
            this.commentInput.placeholder = `Reply to ${authorName}...`;
            this.commentInput.focus();
        }
    }

    cancelReply() {
        this.replyTarget = null;
        if (this.replyBadge) this.replyBadge.classList.add('d-none');
        if (!this.attachedGifUrl && this.commentAuxBar) {
            this.commentAuxBar.classList.add('d-none');
        }
        if (this.commentInput) {
            this.commentInput.placeholder = 'Add a family note...';
        }
    }

    startEdit(commentId, currentText) {
        const box = document.getElementById(`reelCommentTextBox_${commentId}`);
        if (!box) return;

        box.innerHTML = `
        <div class="reel-comment-edit-box">
            <input type="text" class="form-control form-control-sm rounded-pill bg-dark text-white border-secondary mb-1 reel-edit-input" value="${esc(currentText)}" style="font-size: 0.82rem; padding: 4px 12px;">
            <div class="d-flex gap-2 justify-content-end align-items-center">
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-0 save-edit-btn" style="font-size: 0.75rem; min-height: 28px;">Save</button>
                <button type="button" class="btn btn-link btn-sm text-white-50 p-0 cancel-edit-btn" style="font-size: 0.75rem;">Cancel</button>
            </div>
        </div>
        `;

        const input = box.querySelector('.reel-edit-input');
        input?.focus();

        const saveBtn = box.querySelector('.save-edit-btn');
        const cancelBtn = box.querySelector('.cancel-edit-btn');

        saveBtn?.addEventListener('click', async () => {
            const newText = input?.value?.trim() || '';
            if (!newText) return;
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            await this.saveEdit(commentId, newText);
        });

        cancelBtn?.addEventListener('click', () => {
            this.loadComments();
        });
    }

    async saveEdit(commentId, newText) {
        try {
            const res = await axios.post('/api/reels/comment/update', {
                comment_id: Number(commentId),
                comment: newText
            });
            if (res.data?.status === 'success') {
                this.showToast('Comment updated.', 'success');
                await this.loadComments();
            }
        } catch (error) {
            this.showToast(error.response?.data?.message || 'Could not update comment.', 'error');
            await this.loadComments();
        }
    }

    async toggleCommentReaction(commentId, reactionType = 'like') {
        try {
            const res = await axios.post('/api/reels/comment/react', {
                comment_id: Number(commentId),
                reaction_type: reactionType
            });
            if (res.data?.status === 'success') {
                await this.loadComments();
            }
        } catch (error) {
            this.showToast(error.response?.data?.message || 'Could not react to comment.', 'error');
        }
    }

    renderGifPicker() {
        if (!this.gifCategoriesContainer || !this.gifGridContainer) return;

        // Render categories
        this.gifCategoriesContainer.innerHTML = GIF_CATEGORIES.map(cat => `
            <button type="button" class="btn btn-sm ${this.selectedGifCat === cat.key ? 'btn-primary' : 'btn-outline-secondary text-white-50'} rounded-pill py-0 px-2 fw-semibold reel-gif-cat-btn" data-cat="${cat.key}" style="font-size: 0.72rem; white-space: nowrap;">
                ${cat.label}
            </button>
        `).join('');

        this.gifCategoriesContainer.querySelectorAll('.reel-gif-cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.selectedGifCat = btn.getAttribute('data-cat') || 'all';
                this.renderGifPicker();
            });
        });

        // Render GIF grid
        const filtered = this.selectedGifCat === 'all'
            ? FAMILY_GIFS
            : FAMILY_GIFS.filter(g => g.cat === this.selectedGifCat);

        this.gifGridContainer.innerHTML = filtered.map(g => `
            <img src="${esc(g.url)}" alt="${esc(g.title)}" class="reel-gif-item" data-url="${esc(g.url)}" loading="lazy">
        `).join('');

        this.gifGridContainer.querySelectorAll('.reel-gif-item').forEach(img => {
            img.addEventListener('click', () => {
                const url = img.getAttribute('data-url');
                if (url) {
                    this.selectGif(url);
                }
            });
        });
    }

    toggleGifPicker() {
        if (!this.gifPickerDrawer) return;
        this.gifPickerDrawer.classList.toggle('d-none');
    }

    selectGif(url) {
        this.attachedGifUrl = url;
        if (this.attachedGifImg) this.attachedGifImg.src = url;
        if (this.attachedGifContainer) this.attachedGifContainer.classList.remove('d-none');
        if (this.commentAuxBar) this.commentAuxBar.classList.remove('d-none');
        if (this.gifPickerDrawer) this.gifPickerDrawer.classList.add('d-none');
        if (this.commentInput) this.commentInput.focus();
    }

    removeAttachedGif() {
        this.attachedGifUrl = null;
        if (this.attachedGifImg) this.attachedGifImg.src = '';
        if (this.attachedGifContainer) this.attachedGifContainer.classList.add('d-none');
        if (!this.replyTarget && this.commentAuxBar) {
            this.commentAuxBar.classList.add('d-none');
        }
    }

    async deleteReelComment(commentId) {
        const reel = this.reels[this.currentIndex];
        if (!reel || !commentId) return;

        if (typeof Swal !== 'undefined') {
            const confirm = await Swal.fire({
                icon: 'warning',
                title: 'Delete this comment?',
                text: 'This comment and any replies will be permanently removed.',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete'
            });
            if (!confirm.isConfirmed) return;
        }

        try {
            const res = await axios.post('/api/reels/comment/delete', {
                comment_id: Number(commentId)
            });

            if (res.data?.status === 'success') {
                this.showToast('Comment deleted.', 'success');
                reel.comments_count = Math.max(0, (reel.comments_count || 1) - 1);
                const countBadge = document.getElementById('reelCommentCount');
                if (countBadge) countBadge.textContent = reel.comments_count;

                await this.loadComments();
            }
        } catch (error) {
            this.showToast(error.response?.data?.message || 'Could not delete comment.', 'error');
        }
    }

    async submitComment() {
        const reel = this.reels[this.currentIndex];
        if (!reel || !this.commentInput) return;

        const text = this.commentInput.value.trim();
        const gif = this.attachedGifUrl;
        if (!text && !gif) return;

        // Button spinner activation
        if (this.commentSubmitBtn) this.commentSubmitBtn.disabled = true;
        if (this.commentSubmitIcon) this.commentSubmitIcon.classList.add('d-none');
        if (this.commentSubmitSpinner) this.commentSubmitSpinner.classList.remove('d-none');

        try {
            const payload = {
                reel_id: reel.id,
                comment: text
            };
            if (this.replyTarget?.id) {
                payload.parent_comment_no = this.replyTarget.id;
            }
            if (gif) {
                payload.gif_url = gif;
            }

            const res = await axios.post('/api/reels/comment', payload);

            if (res.data?.status === 'success') {
                this.commentInput.value = '';
                this.cancelReply();
                this.removeAttachedGif();
                if (this.gifPickerDrawer) this.gifPickerDrawer.classList.add('d-none');

                reel.comments_count = (reel.comments_count || 0) + 1;
                const countBadge = document.getElementById('reelCommentCount');
                if (countBadge) countBadge.textContent = reel.comments_count;

                await this.loadComments();
            }
        } catch (error) {
            this.showToast(error.response?.data?.message || 'Could not post comment. Please retry.', 'error');
        } finally {
            if (this.commentSubmitBtn) this.commentSubmitBtn.disabled = false;
            if (this.commentSubmitIcon) this.commentSubmitIcon.classList.remove('d-none');
            if (this.commentSubmitSpinner) this.commentSubmitSpinner.classList.add('d-none');
        }
    }

    async shareReel(reel) {
        if (!reel) return;
        const creatorName = (reel.firstName ? `${reel.firstName} ${reel.lastName || ''}` : 'Family').trim();
        const shareUrl = `${window.location.origin}/reels?id=${reel.id}`;
        const shareTitle = `Family Reel by ${creatorName}`;
        const shareText = reel.caption ? `"${reel.caption}" - Watch on Family Platform` : `Watch this family memory by ${creatorName} on Family Platform`;

        // 1. Try Native Mobile / Desktop Web Share API
        if (typeof navigator.share === 'function') {
            try {
                await navigator.share({
                    title: shareTitle,
                    text: shareText,
                    url: shareUrl
                });
                return;
            } catch (err) {
                if (err && (err.name === 'AbortError' || err.name === 'NotAllowedError')) {
                    return;
                }
                console.warn('[ReelsPlayer] Web Share failed, falling back to clipboard:', err);
            }
        }

        // 2. Fallback to Clipboard Copy
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            try {
                await navigator.clipboard.writeText(shareUrl);
                this.showToast('Private family link copied to clipboard!', 'success');
            } catch (err) {
                this.showToast(shareUrl, 'info');
            }
        } else {
            this.showToast(shareUrl, 'info');
        }
    }

    showToast(message, icon = 'info') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({ icon, title: message });
        } else {
            (window.swalToast || alert)(message, icon);
        }
    }

    initCreateModal() {
        const fileInput = document.getElementById('reelVideoFileInput');
        const previewContainer = document.getElementById('reelVideoPreviewContainer');
        const previewVideo = document.getElementById('reelPreviewVideo');
        const previewAmbientBg = document.getElementById('reelPreviewAmbientBg');
        const coverCanvas = document.getElementById('reelCoverPreviewCanvas');
        const orientationBadge = document.getElementById('reelDetectedOrientationBadge');
        const btnCinema = document.getElementById('btnReelFrameCinema');
        const btnCover = document.getElementById('btnReelFrameCover');
        const scrubber = document.getElementById('reelCoverTimeScrubber');
        const scrubberLabel = document.getElementById('reelScrubberTimeLabel');
        const scrubberWrapper = document.getElementById('reelScrubberWrapper');
        const thumbInput = document.getElementById('reelThumbnailDataInput');
        const aspectInput = document.getElementById('reelAspectRatioInput');
        const urlInput = document.getElementById('reelVideoUrlInput');
        const uploadForm = document.getElementById('createReelForm');
        const submitBtn = document.getElementById('btnSubmitReel');
        const dropPrompt = document.getElementById('dropzonePrompt');
        const dropSelected = document.getElementById('dropzoneSelected');
        const dropFileName = document.getElementById('dropzoneFileName') || document.getElementById('dropFileName');
        const dropzone = document.getElementById('reelDropzone');
        const externalPreviewWrapper = document.getElementById('reelPreviewExternalWrapper');
        const externalImg = document.getElementById('reelPreviewExternalImg');
        const externalIcon = document.getElementById('reelPreviewExternalIcon');
        const externalLabel = document.getElementById('reelPreviewExternalLabel');

        if (!uploadForm) return;
        if (uploadForm.dataset.initialized === 'true') return;
        uploadForm.dataset.initialized = 'true';

        let selectedFramingMode = 'auto'; // 'cinema' | 'cover' | 'auto'

        const updateCoverPreview = () => {
            if (!previewVideo || !previewVideo.videoWidth) return;
            const canvasTarget = coverCanvas || document.createElement('canvas');
            const result = FamilyReelsPlayer.renderVideoFrameToCanvas(previewVideo, canvasTarget, selectedFramingMode);
            if (result) {
                if (thumbInput) thumbInput.value = result.dataUrl;
                if (aspectInput) {
                    const effectiveAspect = (selectedFramingMode === 'cover') ? '9:16' : result.aspectRatio;
                    aspectInput.value = effectiveAspect;
                }
                if (previewAmbientBg) {
                    previewAmbientBg.style.backgroundImage = `url('${result.dataUrl}')`;
                }
            }
        };

        const applyModalFramingButtons = (mode) => {
            selectedFramingMode = mode;
            if (btnCinema && btnCover) {
                btnCinema.classList.toggle('active', mode === 'cinema');
                btnCover.classList.toggle('active', mode === 'cover');
            }
            if (previewVideo) {
                previewVideo.style.objectFit = (mode === 'cinema') ? 'contain' : 'cover';
            }
            updateCoverPreview();
        };

        btnCinema?.addEventListener('click', () => applyModalFramingButtons('cinema'));
        btnCover?.addEventListener('click', () => applyModalFramingButtons('cover'));

        // Multi-Angle Chrono-Sync Linking in Modal
        const toggleMultiAngle = document.getElementById('toggleMultiAngleReel');
        const multiAngleContainer = document.getElementById('multiAngleFieldsContainer');
        const linkReelSelect = document.getElementById('linkReelIdSelect');

        if (toggleMultiAngle && multiAngleContainer) {
            toggleMultiAngle.addEventListener('change', () => {
                const isOpen = toggleMultiAngle.checked;
                multiAngleContainer.classList.toggle('d-none', !isOpen);
                if (isOpen && linkReelSelect && linkReelSelect.options.length <= 1) {
                    axios.get('/api/reels/recent-clusters').then(res => {
                        const reels = res.data?.data;
                        if (Array.isArray(reels)) {
                            reels.forEach(r => {
                                const opt = document.createElement('option');
                                opt.value = r.id;
                                const creator = `${r.firstName || ''} ${r.lastName || ''}`.trim() || 'Family';
                                const cap = r.caption ? ` - "${r.caption.slice(0, 30)}..."` : '';
                                opt.textContent = `🎬 ${creator}${cap}`;
                                linkReelSelect.appendChild(opt);
                            });
                        }
                    }).catch(() => {});
                }
            });
        }

        if (scrubber && previewVideo) {
            scrubber.addEventListener('input', () => {
                const targetTime = parseFloat(scrubber.value) || 0;
                if (scrubberLabel) scrubberLabel.textContent = `${targetTime.toFixed(1)}s`;
                previewVideo.pause();
                try {
                    previewVideo.currentTime = targetTime;
                } catch (_) {}
            });
            previewVideo.addEventListener('seeked', () => {
                updateCoverPreview();
            });
        }

        // Direct Video Loader (Local file Blob or Direct MP4/WebM URL)
        const loadDirectVideoIntoPreview = (srcUrl, displayName) => {
            if (!previewContainer || !previewVideo) return;

            // Make sure external overlay is hidden and native video is active
            if (externalPreviewWrapper) externalPreviewWrapper.classList.add('d-none');
            previewVideo.classList.remove('d-none');
            if (scrubberWrapper) scrubberWrapper.classList.remove('d-none');

            previewVideo.muted = true;
            previewVideo.defaultMuted = true;
            previewVideo.playsInline = true;

            const onMetadataLoaded = () => {
                const vw = previewVideo.videoWidth || 360;
                const vh = previewVideo.videoHeight || 640;
                const isLandscape = vw > vh;
                const dur = previewVideo.duration && isFinite(previewVideo.duration) ? previewVideo.duration : 10;
                const defaultSeek = Math.min(0.8, dur / 2);

                if (scrubber) {
                    scrubber.max = String(Math.max(1, dur).toFixed(1));
                    scrubber.value = String(defaultSeek.toFixed(1));
                }
                if (scrubberLabel) {
                    scrubberLabel.textContent = `${defaultSeek.toFixed(1)}s`;
                }
                if (orientationBadge) {
                    orientationBadge.textContent = isLandscape
                        ? `Landscape (${vw}×${vh}) • Cinema Fit`
                        : `Portrait (${vw}×${vh}) • 9:16 Fill`;
                }

                applyModalFramingButtons(isLandscape ? 'cinema' : 'cover');
                try {
                    previewVideo.currentTime = defaultSeek;
                } catch (_) {}
                updateCoverPreview();
            };

            const onDataLoaded = () => {
                updateCoverPreview();
                previewVideo.play().catch(() => {});
            };

            // Remove existing one-time listeners
            if (previewVideo._onMeta) previewVideo.removeEventListener('loadedmetadata', previewVideo._onMeta);
            if (previewVideo._onData) previewVideo.removeEventListener('loadeddata', previewVideo._onData);

            previewVideo._onMeta = onMetadataLoaded;
            previewVideo._onData = onDataLoaded;

            previewVideo.addEventListener('loadedmetadata', onMetadataLoaded, { once: true });
            previewVideo.addEventListener('loadeddata', onDataLoaded, { once: true });

            previewVideo.src = srcUrl;
            previewContainer.classList.remove('d-none');
            previewVideo.load();

            // Self-trigger if metadata or data is already available
            if (previewVideo.readyState >= 1) {
                onMetadataLoaded();
            }
            if (previewVideo.readyState >= 2) {
                onDataLoaded();
            }
        };

        // External Video Stream Display (YouTube, Vimeo)
        const displayExternalVideoPreview = (type, thumbUrl, label, rawUrl) => {
            if (!previewContainer) return;

            if (previewVideo) {
                previewVideo.pause();
                previewVideo.classList.add('d-none');
            }

            previewContainer.classList.remove('d-none');

            if (externalPreviewWrapper) externalPreviewWrapper.classList.remove('d-none');
            if (externalImg) externalImg.src = thumbUrl;
            if (externalIcon) {
                externalIcon.className = (type === 'youtube') ? 'bi bi-youtube text-danger fs-2' : 'bi bi-vimeo text-info fs-2';
            }
            if (externalLabel) externalLabel.textContent = label;
            if (orientationBadge) {
                orientationBadge.textContent = `${type === 'youtube' ? 'YouTube' : 'Vimeo'} Stream • Cinema Fit`;
            }
            if (previewAmbientBg) {
                previewAmbientBg.style.backgroundImage = `url('${thumbUrl}')`;
            }
            if (scrubberWrapper) {
                scrubberWrapper.classList.add('d-none');
            }

            // Draw poster frame to tray cover canvas
            const posterImg = new Image();
            posterImg.crossOrigin = 'anonymous';
            posterImg.onload = () => {
                if (coverCanvas) {
                    const ctx = coverCanvas.getContext('2d');
                    if (ctx) {
                        const targetW = 540;
                        const targetH = 960;
                        coverCanvas.width = targetW;
                        coverCanvas.height = targetH;
                        ctx.fillStyle = '#0f172a';
                        ctx.fillRect(0, 0, targetW, targetH);

                        const sourceRatio = (posterImg.width || targetW) / (posterImg.height || targetH);
                        const targetRatio = targetW / targetH;
                        let sx = 0, sy = 0, sw = posterImg.width, sh = posterImg.height;
                        if (sourceRatio > targetRatio) {
                            sw = Math.round(posterImg.height * targetRatio);
                            sx = Math.round((posterImg.width - sw) / 2);
                        } else {
                            sh = Math.round(posterImg.width / targetRatio);
                            sy = Math.round((posterImg.height - sh) / 2);
                        }
                        ctx.drawImage(posterImg, sx, sy, sw, sh, 0, 0, targetW, targetH);

                        try {
                            const dataUrl = coverCanvas.toDataURL('image/jpeg', 0.88);
                            if (thumbInput) thumbInput.value = dataUrl;
                        } catch (_) {
                            // If cross-origin restrictions apply, server-side VideoParser automatically resolves thumbnailUrl
                        }
                    }
                }
            };
            posterImg.src = thumbUrl;

            if (aspectInput) aspectInput.value = '16:9';
            applyModalFramingButtons('cinema');
        };

        // File Selection Handler
        const handleFileSelected = (file) => {
            if (!file) return;

            if (file.size > 100 * 1024 * 1024) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'File Too Large', text: 'Video file size exceeds the 100MB limit.' });
                } else {
                    alert('Video file size exceeds 100MB limit.');
                }
                if (fileInput) fileInput.value = '';
                return;
            }

            // Clear URL input when a local file is picked
            if (urlInput) urlInput.value = '';

            if (dropPrompt && dropSelected && dropFileName) {
                dropPrompt.classList.add('d-none');
                dropSelected.classList.remove('d-none');
                dropFileName.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(1)} MB)`;
            }

            const fileObjUrl = URL.createObjectURL(file);
            loadDirectVideoIntoPreview(fileObjUrl, file.name);
        };

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                handleFileSelected(this.files?.[0]);
            });
        }

        // Drag and Drop Zone Enhancements
        if (dropzone && fileInput) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#a31621';
                    dropzone.style.backgroundColor = '#fff5f5';
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.backgroundColor = '#f8fafc';
                });
            });

            dropzone.addEventListener('drop', (e) => {
                const files = e.dataTransfer?.files;
                if (files && files.length > 0) {
                    fileInput.files = files;
                    handleFileSelected(files[0]);
                }
            });
        }

        // Video URL Input Handler (Paste or Type URL)
        const handleUrlChanged = () => {
            if (!urlInput) return;
            const rawUrl = urlInput.value.trim();

            if (!rawUrl) {
                // If URL was cleared and no file is selected, hide preview
                if (!fileInput || !fileInput.files || !fileInput.files.length) {
                    if (previewVideo) {
                        previewVideo.pause();
                        previewVideo.removeAttribute('src');
                    }
                    if (previewContainer) previewContainer.classList.add('d-none');
                    if (dropPrompt && dropSelected) {
                        dropPrompt.classList.remove('d-none');
                        dropSelected.classList.add('d-none');
                    }
                }
                return;
            }

            // Clear local file input when URL is supplied
            if (fileInput) fileInput.value = '';
            if (dropPrompt && dropSelected && dropFileName) {
                dropPrompt.classList.add('d-none');
                dropSelected.classList.remove('d-none');
                dropFileName.textContent = `External Video URL Linked`;
            }

            // 1. YouTube Matcher
            const ytRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;
            const ytMatch = rawUrl.match(ytRegex);
            if (ytMatch && ytMatch[1]) {
                const videoId = ytMatch[1];
                const thumbUrl = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
                displayExternalVideoPreview('youtube', thumbUrl, 'YouTube Video Stream', rawUrl);
                return;
            }

            // 2. Vimeo Matcher
            const vimeoRegex = /(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)/i;
            const vimeoMatch = rawUrl.match(vimeoRegex);
            if (vimeoMatch && (vimeoMatch[3] || vimeoMatch[2] || vimeoMatch[1])) {
                const vimeoId = vimeoMatch[3] || vimeoMatch[2] || vimeoMatch[1];
                const thumbUrl = `https://vumbnail.com/${vimeoId}.jpg`;
                displayExternalVideoPreview('vimeo', thumbUrl, 'Vimeo Video Stream', rawUrl);
                return;
            }

            // 3. Direct Video Stream (.mp4, .webm, .mov, Cloudflare, S3, or generic video stream)
            loadDirectVideoIntoPreview(rawUrl, 'Direct Video Link');
        };

        if (urlInput) {
            let debounceTimer = null;
            const triggerUrlCheck = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(handleUrlChanged, 180);
            };

            urlInput.addEventListener('input', triggerUrlCheck);
            urlInput.addEventListener('change', triggerUrlCheck);
            urlInput.addEventListener('paste', () => setTimeout(handleUrlChanged, 50));
        }

        // Soundtrack Studio Controls & Dual-Track Audio Balance
        const btnToggleAudioDrawer = document.getElementById('btnToggleAudioDrawer');
        const btnToggleAudioDrawerText = document.getElementById('btnToggleAudioDrawerText');
        const audioDrawer = document.getElementById('reelAudioDrawer');
        const tabCurated = document.getElementById('tabCuratedAudio');
        const tabCustom = document.getElementById('tabCustomAudio');
        const curatedList = document.getElementById('curatedAudioList');
        const customPanel = document.getElementById('customAudioPanel');
        const customAudioInput = document.getElementById('reelCustomAudioInput');
        const customAudioPrompt = document.getElementById('customAudioPrompt');
        const customAudioSelected = document.getElementById('customAudioSelected');
        const customAudioFileName = document.getElementById('customAudioFileName');
        const btnResetAudio = document.getElementById('btnResetToOriginalAudio');
        const dualControls = document.getElementById('reelDualTrackControls');
        const audioUrlInput = document.getElementById('reelAudioUrlInput');
        const musicTitleInput = document.getElementById('reelMusicTitleInput');
        const activeSoundLabel = document.getElementById('reelActiveSoundLabel');
        const videoVolumeInput = document.getElementById('reelVideoVolumeInput');
        const musicVolumeInput = document.getElementById('reelMusicVolumeInput');
        const audioStartSecInput = document.getElementById('reelAudioStartSecInput');
        const sliderVideoVol = document.getElementById('sliderVideoVolume');
        const sliderMusicVol = document.getElementById('sliderMusicVolume');
        const sliderAudioStart = document.getElementById('sliderAudioStartSec');
        const labelVideoVol = document.getElementById('labelVideoVolume');
        const labelMusicVol = document.getElementById('labelMusicVolume');
        const labelAudioStart = document.getElementById('labelAudioStartSec');

        let modalAudioPreview = null;
        let activePlayingTrackBtn = null;

        const loadCuratedTracks = async () => {
            if (!curatedList || curatedList.dataset.loaded === 'true') return;
            try {
                const res = await axios.get('/api/reels/audio-library');
                const tracks = res.data?.data || [];
                curatedList.innerHTML = '';
                curatedList.dataset.loaded = 'true';

                if (!tracks.length) {
                    curatedList.innerHTML = '<div class="text-muted small p-2">No library tracks found.</div>';
                    return;
                }

                tracks.forEach(track => {
                    const card = document.createElement('div');
                    card.className = 'd-flex align-items-center justify-content-between p-2 rounded-3 border bg-white shadow-xs';
                    card.style.transition = 'all 0.15s ease';
                    card.innerHTML = `
                        <div class="d-flex align-items-center gap-2 overflow-hidden" style="max-width: 65%;">
                            <button type="button" class="btn btn-sm btn-light rounded-circle d-flex align-items-center justify-content-center text-primary btn-play-sample"
                                    data-url="${track.url}" style="width: 36px; height: 36px; flex-shrink: 0;" title="Preview sample">
                                <i class="bi bi-play-fill fs-5"></i>
                            </button>
                            <div class="overflow-hidden">
                                <div class="fw-semibold text-dark small text-truncate" title="${track.title}">${track.title}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.68rem;">${track.genre} • ${track.duration}</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold btn-select-track"
                                data-url="${track.url}" data-title="${track.title}" style="font-size: 0.72rem; min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                            <i class="bi bi-check2 me-0.5"></i> Select
                        </button>
                    `;

                    // Sample preview handler
                    const playBtn = card.querySelector('.btn-play-sample');
                    playBtn?.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (!modalAudioPreview) {
                            modalAudioPreview = new Audio();
                        }

                        if (modalAudioPreview.src.includes(track.url) && !modalAudioPreview.paused) {
                            modalAudioPreview.pause();
                            playBtn.innerHTML = '<i class="bi bi-play-fill fs-5"></i>';
                            activePlayingTrackBtn = null;
                        } else {
                            if (activePlayingTrackBtn) {
                                activePlayingTrackBtn.innerHTML = '<i class="bi bi-play-fill fs-5"></i>';
                            }
                            modalAudioPreview.src = track.url;
                            modalAudioPreview.currentTime = 0;
                            modalAudioPreview.play().catch(() => {});
                            playBtn.innerHTML = '<i class="bi bi-pause-fill fs-5"></i>';
                            activePlayingTrackBtn = playBtn;

                            modalAudioPreview.onended = () => {
                                playBtn.innerHTML = '<i class="bi bi-play-fill fs-5"></i>';
                                activePlayingTrackBtn = null;
                            };
                        }
                    });

                    // Select track handler
                    const selectBtn = card.querySelector('.btn-select-track');
                    selectBtn?.addEventListener('click', () => {
                        if (modalAudioPreview) modalAudioPreview.pause();
                        if (activePlayingTrackBtn) activePlayingTrackBtn.innerHTML = '<i class="bi bi-play-fill fs-5"></i>';

                        if (audioUrlInput) audioUrlInput.value = track.url;
                        if (musicTitleInput) musicTitleInput.value = track.title;
                        if (activeSoundLabel) activeSoundLabel.textContent = `Selected: ${track.title}`;
                        if (dualControls) dualControls.classList.remove('d-none');
                        if (customAudioInput) customAudioInput.value = '';
                        if (customAudioPrompt && customAudioSelected) {
                            customAudioPrompt.classList.remove('d-none');
                            customAudioSelected.classList.add('d-none');
                        }
                        if (audioDrawer) audioDrawer.classList.add('d-none');
                        if (btnToggleAudioDrawerText) btnToggleAudioDrawerText.textContent = 'Change Music';
                    });

                    curatedList.appendChild(card);
                });
            } catch (err) {
                curatedList.innerHTML = '<div class="text-danger small p-2">Failed to load audio library.</div>';
            }
        };

        btnToggleAudioDrawer?.addEventListener('click', () => {
            if (!audioDrawer) return;
            const isClosed = audioDrawer.classList.contains('d-none');
            audioDrawer.classList.toggle('d-none', !isClosed);
            if (isClosed) {
                loadCuratedTracks();
            }
        });

        tabCurated?.addEventListener('click', () => {
            tabCurated.classList.add('active', 'btn-primary');
            tabCurated.classList.remove('btn-outline-secondary');
            tabCustom?.classList.remove('active', 'btn-primary');
            tabCustom?.classList.add('btn-outline-secondary');
            curatedList?.classList.remove('d-none');
            customPanel?.classList.add('d-none');
            loadCuratedTracks();
        });

        tabCustom?.addEventListener('click', () => {
            tabCustom.classList.add('active', 'btn-primary');
            tabCustom.classList.remove('btn-outline-secondary');
            tabCurated?.classList.remove('active', 'btn-primary');
            tabCurated?.classList.add('btn-outline-secondary');
            curatedList?.classList.add('d-none');
            customPanel?.classList.remove('d-none');
        });

        customAudioInput?.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) return;

            if (file.size > 20 * 1024 * 1024) {
                alert('Audio file size exceeds 20MB limit.');
                this.value = '';
                return;
            }

            if (customAudioPrompt && customAudioSelected && customAudioFileName) {
                customAudioPrompt.classList.add('d-none');
                customAudioSelected.classList.remove('d-none');
                customAudioFileName.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(1)} MB)`;
            }

            const cleanName = file.name.replace(/\.[^/.]+$/, "");
            if (musicTitleInput) musicTitleInput.value = cleanName;
            if (audioUrlInput) audioUrlInput.value = ''; // Will upload via audio_file
            if (activeSoundLabel) activeSoundLabel.textContent = `Custom Audio: ${cleanName}`;
            if (dualControls) dualControls.classList.remove('d-none');
            if (audioDrawer) audioDrawer.classList.add('d-none');
            if (btnToggleAudioDrawerText) btnToggleAudioDrawerText.textContent = 'Change Music';
        });

        btnResetAudio?.addEventListener('click', () => {
            if (modalAudioPreview) modalAudioPreview.pause();
            if (audioUrlInput) audioUrlInput.value = '';
            if (customAudioInput) customAudioInput.value = '';
            if (customAudioPrompt && customAudioSelected) {
                customAudioPrompt.classList.remove('d-none');
                customAudioSelected.classList.add('d-none');
            }
            if (musicTitleInput) musicTitleInput.value = 'Original Family Audio';
            if (activeSoundLabel) activeSoundLabel.textContent = 'Original Family Audio (Default)';
            if (dualControls) dualControls.classList.add('d-none');
            if (videoVolumeInput) videoVolumeInput.value = '100';
            if (musicVolumeInput) musicVolumeInput.value = '40';
            if (audioStartSecInput) audioStartSecInput.value = '0.0';
            if (sliderVideoVol) sliderVideoVol.value = '100';
            if (sliderMusicVol) sliderMusicVol.value = '40';
            if (sliderAudioStart) sliderAudioStart.value = '0.0';
            if (labelVideoVol) labelVideoVol.textContent = '100%';
            if (labelMusicVol) labelMusicVol.textContent = '40%';
            if (labelAudioStart) labelAudioStart.textContent = '0.0s';
            if (audioDrawer) audioDrawer.classList.add('d-none');
            if (btnToggleAudioDrawerText) btnToggleAudioDrawerText.textContent = 'Choose Music';
        });

        sliderVideoVol?.addEventListener('input', () => {
            const val = sliderVideoVol.value;
            if (labelVideoVol) labelVideoVol.textContent = `${val}%`;
            if (videoVolumeInput) videoVolumeInput.value = val;
            if (previewVideo) previewVideo.volume = Number(val) / 100;
        });

        sliderMusicVol?.addEventListener('input', () => {
            const val = sliderMusicVol.value;
            if (labelMusicVol) labelMusicVol.textContent = `${val}%`;
            if (musicVolumeInput) musicVolumeInput.value = val;
            if (modalAudioPreview) modalAudioPreview.volume = Number(val) / 100;
        });

        sliderAudioStart?.addEventListener('input', () => {
            const val = parseFloat(sliderAudioStart.value) || 0;
            if (labelAudioStart) labelAudioStart.textContent = `${val.toFixed(1)}s`;
            if (audioStartSecInput) audioStartSecInput.value = String(val.toFixed(1));
            if (modalAudioPreview) modalAudioPreview.currentTime = val;
        });

        // Clean up video & audio playback on modal hide
        const modalEl = document.getElementById('createReelModal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                if (previewVideo) previewVideo.pause();
                if (modalAudioPreview) modalAudioPreview.pause();
            });
        }

        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
            const hasUrl = urlInput && urlInput.value.trim().length > 0;

            if (!hasFile && !hasUrl) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Select a Video',
                        text: 'Please choose a video file or enter a video URL before publishing.',
                        confirmButtonColor: '#a31621'
                    });
                } else {
                    alert('Please choose a video file or enter a video URL before publishing.');
                }
                return;
            }

            // Ensure latest cover canvas frame is captured before creating FormData
            if (hasFile && previewVideo && previewVideo.videoWidth > 0) {
                updateCoverPreview();
            }

            const formData = new FormData(uploadForm);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publishing Reel...';
            }

            try {
                const res = await axios.post('/api/reels/upload', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                if (res.data?.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Reel Published! 🎉',
                            text: 'Your family reel is now live.',
                            confirmButtonColor: '#a31621'
                        });
                    }
                    window.location.href = `/reels?id=${res.data?.data?.id || ''}`;
                }
            } catch (error) {
                const errMsg = error.response?.data?.message || 'Failed to upload video reel.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Upload Failed', text: errMsg });
                } else {
                    alert(errMsg);
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-camera-video-fill me-1"></i> Publish Reel';
                }
            }
        });
    }
}

function initFamilyReels() {
    if (!window.familyReelsPlayerInstance) {
        window.familyReelsPlayerInstance = new FamilyReelsPlayer();
    } else {
        window.familyReelsPlayerInstance.initCreateModal();
        window.familyReelsPlayerInstance.initTrayCards();
    }
    return window.familyReelsPlayerInstance;
}

window.initFamilyReels = initFamilyReels;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFamilyReels);
} else {
    initFamilyReels();
}

export default FamilyReelsPlayer;

