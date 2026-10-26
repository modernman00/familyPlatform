import axios from 'axios';
import Swal from 'sweetalert2';
import { format } from 'timeago.js';
import { esc } from '../global';

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

        if (this.viewport || this.container) {
            this.init();
        }

        this.initTrayCards();
        this.initCreateModal();
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

        // Comment Form Submit
        if (this.commentForm) {
            this.commentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitComment();
            });
        }
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

        // Direct HTML5 Video File (MP4, WebM, MOV, server uploads)
        return `
        <video class="reel-video-element ${initialFitClass}" id="reelActiveVideo" playsinline webkit-playsinline loop preload="auto" ${this.isMuted ? 'muted' : ''} autoplay>
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
        const reactionIconMap = {
            like: 'bi-heart-fill',
            love: 'bi-emoji-heart-eyes-fill',
            celebrate: 'bi-stars',
            bless: 'bi-sun-fill',
            proud: 'bi-trophy-fill'
        };
        const activeLikeIcon = isLiked ? (reactionIconMap[userReaction] || 'bi-heart-fill') : 'bi-heart';
        const categoryLabel = esc(reel.category ? `#${String(reel.category).replace(/_/g, ' ').toUpperCase()}` : '#FAMILY');
        const mediaHtml = this.getMediaEmbed(reel.video_url, reel.aspect_ratio || '9:16');
        const isOwner = this.currentUserId && String(reel.user_id) === String(this.currentUserId);
        const totalReels = this.reels.length;
        const posLabel = `${this.currentIndex + 1} / ${totalReels}`;

        this.viewport.innerHTML = `
        <div class="reel-card-container" id="activeReelCard">
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

                <!-- Like & Celebratory Reaction Picker -->
                <div class="d-flex flex-column align-items-center position-relative" id="reelReactionWrapper">
                    <div class="reel-reaction-picker" id="reelReactionPicker">
                        <button type="button" class="reel-reaction-emoji-btn" data-reaction="like" title="Love">❤️</button>
                        <button type="button" class="reel-reaction-emoji-btn" data-reaction="celebrate" title="Celebrate Milestone">🎉</button>
                        <button type="button" class="reel-reaction-emoji-btn" data-reaction="bless" title="Family Blessing">🙏</button>
                        <button type="button" class="reel-reaction-emoji-btn" data-reaction="proud" title="So Proud">👏</button>
                    </div>
                    <button class="reel-action-btn ${isLiked ? 'liked' : ''}" id="btnReelLike" title="Tap to Like • Hold for Reactions">
                        <i class="bi ${activeLikeIcon}"></i>
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
                <div class="reel-author-row">
                    <img src="${avatarUrl}" alt="${fullName}" class="reel-author-avatar" onerror="this.src='/resources/images/profile/avatarM.png'">
                    <div>
                        <div class="reel-author-name">${fullName}</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="reel-tag-pill"><i class="bi bi-stars text-warning"></i> ${categoryLabel}</span>
                            <span class="reel-tag-pill" id="reelViewsPill"><i class="bi bi-eye-fill text-info"></i> <span id="reelViewCount">${reel.views_count || 0}</span></span>
                        </div>
                    </div>
                </div>

                <div class="reel-caption-text">${esc(reel.caption || '')}</div>

                <div class="reel-music-tag">
                    <i class="bi bi-music-note-beamed text-warning"></i>
                    <span>${esc(reel.music_title || 'Original Family Audio')} • ${reel.created_at ? format(reel.created_at) : 'Recent'}</span>
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

        // Wire video play / pause, smart aspect detection & progress bar
        const video = document.getElementById('reelActiveVideo');
        const progressTrack = document.getElementById('reelProgressTrack');
        const progressFill = document.getElementById('reelProgressFill');
        const ambientBackdrop = document.getElementById('reelAmbientBackdrop');

        if (video) {
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

            // Progress Scrubber Update
            video.addEventListener('timeupdate', () => {
                if (progressFill && video.duration > 0) {
                    const pct = Math.min(100, Math.max(0, (video.currentTime / video.duration) * 100));
                    progressFill.style.width = `${pct}%`;
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

            // Tap on video to toggle play/pause
            video.addEventListener('click', () => this.togglePlayPause());

            // Double click / tap to heart with emoji burst
            let lastTap = 0;
            video.addEventListener('touchend', (e) => {
                const currentTime = new Date().getTime();
                const tapLength = currentTime - lastTap;
                if (tapLength < 300 && tapLength > 0) {
                    this.spawnFloatingEmoji('❤️');
                    this.toggleLike('like');
                    e.preventDefault();
                }
                lastTap = currentTime;
            });
        }

        // Attach listeners for top & right action buttons
        document.getElementById('btnToggleFitMode')?.addEventListener('click', () => this.toggleFitMode());
        document.getElementById('btnToggleMute')?.addEventListener('click', () => this.toggleMute());
        document.getElementById('btnDeleteReel')?.addEventListener('click', () => this.deleteCurrentReel());
        document.getElementById('btnPrevReel')?.addEventListener('click', () => this.prevReel());
        document.getElementById('btnNextReel')?.addEventListener('click', () => this.nextReel());

        // Reaction button + picker hover/long-press
        const likeBtn = document.getElementById('btnReelLike');
        const reactionWrapper = document.getElementById('reelReactionWrapper');
        const reactionPicker = document.getElementById('reelReactionPicker');

        if (likeBtn) {
            likeBtn.addEventListener('click', () => {
                this.spawnFloatingEmoji('❤️');
                this.toggleLike('like');
            });
        }
        if (reactionWrapper && reactionPicker) {
            reactionWrapper.addEventListener('mouseenter', () => reactionPicker.classList.add('open'));
            reactionWrapper.addEventListener('mouseleave', () => reactionPicker.classList.remove('open'));
            reactionPicker.querySelectorAll('[data-reaction]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const rType = btn.getAttribute('data-reaction') || 'like';
                    const emojiChar = btn.textContent || '🎉';
                    this.spawnFloatingEmoji(emojiChar);
                    reactionPicker.classList.remove('open');
                    this.toggleLike(rType);
                });
            });
        }

        document.getElementById('btnReelComments')?.addEventListener('click', () => this.toggleComments(true));
        document.getElementById('btnReelShare')?.addEventListener('click', () => this.shareReel(reel));
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

    spawnFloatingEmoji(emoji = '❤️') {
        const card = document.getElementById('activeReelCard');
        if (!card) return;
        const el = document.createElement('div');
        el.className = 'reel-floating-emoji';
        el.textContent = emoji;
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
            if (feedback) {
                feedback.innerHTML = '<i class="bi bi-play-fill"></i>';
                feedback.classList.add('show');
                setTimeout(() => feedback.classList.remove('show'), 300);
            }
        } else {
            video.pause();
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
        if (video) video.muted = this.isMuted;
        if (muteBtn) {
            muteBtn.innerHTML = `<i class="bi ${this.isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}"></i>`;
        }
    }

    async toggleLike(reactionType = 'like') {
        const reel = this.reels[this.currentIndex];
        if (!reel) return;

        const likeBtn = document.getElementById('btnReelLike');
        const likeCount = document.getElementById('reelLikeCount');
        const reactionIconMap = {
            like: 'bi-heart-fill',
            love: 'bi-emoji-heart-eyes-fill',
            celebrate: 'bi-stars',
            bless: 'bi-sun-fill',
            proud: 'bi-trophy-fill'
        };

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
                        const iconCls = reactionIconMap[res.data.reaction] || 'bi-heart-fill';
                        likeBtn.innerHTML = `<i class="bi ${iconCls}"></i>`;
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

            this.commentsList.innerHTML = comments.map(c => `
            <div class="reel-comment-bubble">
                <img src="${esc(c.profilePics ? (c.profilePics.startsWith('/') ? c.profilePics : `/resources/images/profile/${c.profilePics}`) : '/resources/images/profile/avatarM.png')}" class="reel-comment-avatar" alt="User" onerror="this.src='/resources/images/profile/avatarM.png'">
                <div class="reel-comment-content">
                    <div class="reel-comment-user">${esc(`${c.firstName || 'Family'} ${c.lastName || 'Member'}`)}</div>
                    <div class="reel-comment-text" style="font-variant-emoji: emoji;">${esc(this.normalizeEmoji(c.comment || ''))}</div>
                    <span class="reel-comment-time">${c.created_at ? format(c.created_at) : 'Just now'}</span>
                </div>
            </div>
            `).join('');
        } catch (error) {
            this.commentsList.innerHTML = '<div class="text-center py-4 text-danger small">Failed to load comments.</div>';
        }
    }

    async submitComment() {
        const reel = this.reels[this.currentIndex];
        if (!reel || !this.commentInput) return;

        const text = this.commentInput.value.trim();
        if (!text) return;

        try {
            const res = await axios.post('/api/reels/comment', {
                reel_id: reel.id,
                comment: text
            });

            if (res.data?.status === 'success') {
                this.commentInput.value = '';
                reel.comments_count = (reel.comments_count || 0) + 1;
                const countBadge = document.getElementById('reelCommentCount');
                if (countBadge) countBadge.textContent = reel.comments_count;

                await this.loadComments();
            }
        } catch (error) {
            this.showToast('Could not post comment. Please retry.', 'error');
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
        const thumbInput = document.getElementById('reelThumbnailDataInput');
        const aspectInput = document.getElementById('reelAspectRatioInput');
        const urlInput = document.getElementById('reelVideoUrlInput');
        const uploadForm = document.getElementById('createReelForm');
        const submitBtn = document.getElementById('btnSubmitReel');
        const dropPrompt = document.getElementById('dropzonePrompt');
        const dropSelected = document.getElementById('dropzoneSelected');
        const dropFileName = document.getElementById('dropzoneFileName') || document.getElementById('dropFileName');

        if (!uploadForm) return;

        let selectedFramingMode = 'auto'; // 'cinema' | 'cover' | 'auto'

        const updateCoverPreview = () => {
            if (!previewVideo || ! previewVideo.videoWidth) return;
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

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const file = this.files?.[0];
                if (!file) return;

                if (file.size > 100 * 1024 * 1024) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'File Too Large', text: 'Video file size exceeds the 100MB limit.' });
                    } else {
                        alert('Video file size exceeds 100MB limit.');
                    }
                    fileInput.value = '';
                    return;
                }

                if (dropPrompt && dropSelected && dropFileName) {
                    dropPrompt.classList.add('d-none');
                    dropSelected.classList.remove('d-none');
                    dropFileName.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(1)} MB)`;
                }

                const fileObjUrl = URL.createObjectURL(file);

                if (previewContainer && previewVideo) {
                    previewVideo.src = fileObjUrl;
                    previewContainer.classList.remove('d-none');

                    previewVideo.onloadedmetadata = () => {
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
                    };

                    previewVideo.onloadeddata = () => {
                        updateCoverPreview();
                        previewVideo.play().catch(() => {});
                    };
                }
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
    new FamilyReelsPlayer();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFamilyReels);
} else {
    initFamilyReels();
}

