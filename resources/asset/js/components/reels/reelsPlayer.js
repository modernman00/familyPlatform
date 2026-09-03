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
        this.touchStartY = 0;
        this.touchEndY = 0;

        this.viewport = document.getElementById('reelsViewport');
        this.container = document.getElementById('reelsTheaterContainer');
        this.commentsDrawer = document.getElementById('reelCommentsDrawer');
        this.commentsList = document.getElementById('reelCommentsList');
        this.commentForm = document.getElementById('reelCommentForm');
        this.commentInput = document.getElementById('reelCommentInput');

        if (this.viewport || this.container) {
            this.init();
        }

        this.initCreateModal();
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
            if (['input', 'textarea'].includes(document.activeElement?.tagName?.toLowerCase())) return;

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
            }
        });

        // Touch Swipe Gestures
        if (this.viewport) {
            this.viewport.addEventListener('touchstart', (e) => {
                this.touchStartY = e.changedTouches[0].screenY;
            }, { passive: true });

            this.viewport.addEventListener('touchend', (e) => {
                this.touchEndY = e.changedTouches[0].screenY;
                this.handleSwipe();
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

    getMediaEmbed(videoUrl) {
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

        // Direct HTML5 Video File (MP4, WebM, MOV, server uploads)
        return `
        <video class="reel-video-element" id="reelActiveVideo" playsinline webkit-playsinline loop preload="auto" ${this.isMuted ? 'muted' : ''} autoplay>
            <source src="${trimmed}" type="video/mp4">
            <source src="${trimmed}" type="video/webm">
            Your browser does not support HTML5 video.
        </video>
        `;
    }

    renderCurrentReel() {
        const reel = this.reels[this.currentIndex];
        if (!reel || !this.viewport) return;

        // Close comments drawer when switching
        if (this.isCommentsOpen) this.toggleComments(false);

        // All reel.* string fields are user-authored — escape before innerHTML (SEC-2).
        const fullName = esc(`${reel.firstName || 'Family'} ${reel.lastName || 'Member'}`.trim());
        const rawAvatar = reel.profilePics ? (reel.profilePics.startsWith('/') ? reel.profilePics : `/resources/images/profile/${reel.profilePics}`) : '/resources/images/profile/avatarM.png';
        const avatarUrl = esc(rawAvatar);
        const isLiked = reel.user_reaction === 'like' || !!reel.user_reaction;
        const categoryLabel = esc(reel.category ? `#${String(reel.category).toUpperCase()}` : '#FAMILY');
        const mediaHtml = this.getMediaEmbed(reel.video_url);

        this.viewport.innerHTML = `
        <div class="reel-card-container" id="activeReelCard">
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
                <a href="/profilePage" class="reel-nav-pill-btn">
                    <i class="bi bi-chevron-left me-1"></i> Back
                </a>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="reel-nav-pill-btn" id="btnToggleMute">
                        <i class="bi ${this.isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}"></i>
                    </button>
                    <button type="button" class="reel-nav-pill-btn" data-bs-toggle="modal" data-bs-target="#createReelModal">
                        <i class="bi bi-camera-video-fill me-1 text-warning"></i> Create
                    </button>
                </div>
            </div>

            <!-- Floating Action Stack (Right) -->
            <div class="reel-action-stack">
                <!-- Like Button -->
                <div class="d-flex flex-column align-items-center">
                    <button class="reel-action-btn ${isLiked ? 'liked' : ''}" id="btnReelLike" title="Like Reel">
                        <i class="bi ${isLiked ? 'bi-heart-fill' : 'bi-heart'}"></i>
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
                    <a href="/organogram/${reel.user_id}" class="reel-action-btn" title="View on Family Tree" style="text-decoration:none;">
                        <i class="bi bi-diagram-3-fill text-warning"></i>
                    </a>
                    <span class="reel-action-count">Tree</span>
                </div>

                <!-- Share Link -->
                <div class="d-flex flex-column align-items-center">
                    <button class="reel-action-btn" id="btnReelShare" title="Copy Reel Link">
                        <i class="bi bi-share-fill"></i>
                    </button>
                    <span class="reel-action-count">Share</span>
                </div>
            </div>

            <!-- Bottom Metadata Overlay -->
            <div class="reel-bottom-meta">
                <div class="reel-author-row">
                    <img src="${avatarUrl}" alt="${fullName}" class="reel-author-avatar">
                    <div>
                        <div class="reel-author-name">${fullName}</div>
                        <span class="reel-tag-pill"><i class="bi bi-stars text-warning"></i> ${categoryLabel}</span>
                    </div>
                </div>

                <div class="reel-caption-text">${esc(reel.caption || '')}</div>

                <div class="reel-music-tag">
                    <i class="bi bi-music-note-beamed text-warning"></i>
                    <span>${esc(reel.music_title || 'Original Family Audio')} • ${reel.created_at ? format(reel.created_at) : 'Recent'}</span>
                </div>
            </div>
        </div>
        `;

        // Wire video play / pause & click triggers
        const video = document.getElementById('reelActiveVideo');
        if (video) {
            video.play().catch(() => {
                // Auto-play was prevented (browser policy), prompt click
                this.isMuted = true;
                video.muted = true;
                video.play().catch(console.error);
            });

            // Tap on video to toggle play/pause
            video.addEventListener('click', () => this.togglePlayPause());

            // Double click to heart
            let lastTap = 0;
            video.addEventListener('touchend', (e) => {
                const currentTime = new Date().getTime();
                const tapLength = currentTime - lastTap;
                if (tapLength < 300 && tapLength > 0) {
                    this.toggleLike();
                    e.preventDefault();
                }
                lastTap = currentTime;
            });
        }

        // Attach listeners for action buttons
        document.getElementById('btnToggleMute')?.addEventListener('click', () => this.toggleMute());
        document.getElementById('btnReelLike')?.addEventListener('click', () => this.toggleLike());
        document.getElementById('btnReelComments')?.addEventListener('click', () => this.toggleComments(true));
        document.getElementById('btnReelShare')?.addEventListener('click', () => this.shareReel(reel));
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

    async toggleLike() {
        const reel = this.reels[this.currentIndex];
        if (!reel) return;

        const likeBtn = document.getElementById('btnReelLike');
        const likeCount = document.getElementById('reelLikeCount');

        try {
            const res = await axios.post('/api/reels/react', {
                reel_id: reel.id,
                reaction_type: 'like'
            });

            if (res.data?.status === 'success') {
                const count = res.data.count;
                reel.likes_count = count;
                reel.user_reaction = res.data.reaction;

                if (likeCount) likeCount.textContent = count;
                if (likeBtn) {
                    if (res.data.action === 'added' || res.data.action === 'updated') {
                        likeBtn.classList.add('liked');
                        likeBtn.innerHTML = '<i class="bi bi-heart-fill"></i>';
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
                <img src="${esc(c.profilePics ? `/resources/images/profile/${c.profilePics}` : '/resources/images/profile/avatarM.png')}" class="reel-comment-avatar" alt="User">
                <div class="reel-comment-content">
                    <div class="reel-comment-user">${esc(`${c.firstName || 'Family'} ${c.lastName || 'Member'}`)}</div>
                    <div class="reel-comment-text">${esc(c.comment || '')}</div>
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

    shareReel(reel) {
        const shareUrl = `${window.location.origin}/reels?id=${reel.id}`;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(shareUrl).then(() => {
                this.showToast('Reel link copied to clipboard!', 'success');
            });
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
            alert(message);
        }
    }

    initCreateModal() {
        const fileInput = document.getElementById('reelVideoFileInput');
        const previewContainer = document.getElementById('reelVideoPreviewContainer');
        const previewVideo = document.getElementById('reelPreviewVideo');
        const urlInput = document.getElementById('reelVideoUrlInput');
        const uploadForm = document.getElementById('createReelForm');
        const submitBtn = document.getElementById('btnSubmitReel');
        const dropPrompt = document.getElementById('dropzonePrompt');
        const dropSelected = document.getElementById('dropzoneSelected');
        const dropFileName = document.getElementById('dropFileName');

        if (!uploadForm) return;

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
                    previewVideo.play().catch(console.error);
                }

                // Automatically capture video frame thumbnail using off-screen video and canvas
                const tempVideo = document.createElement('video');
                tempVideo.preload = 'metadata';
                tempVideo.src = fileObjUrl;
                tempVideo.muted = true;
                tempVideo.playsInline = true;

                tempVideo.onloadedmetadata = () => {
                    tempVideo.currentTime = Math.min(1.0, (tempVideo.duration || 2) / 2);
                };

                tempVideo.onseeked = () => {
                    try {
                        const canvas = document.createElement('canvas');
                        canvas.width = 360;
                        canvas.height = 640;
                        const ctx = canvas.getContext('2d');
                        if (ctx) {
                            ctx.drawImage(tempVideo, 0, 0, canvas.width, canvas.height);
                            const thumbData = canvas.toDataURL('image/jpeg', 0.85);
                            const thumbInput = document.getElementById('reelThumbnailDataInput');
                            if (thumbInput) thumbInput.value = thumbData;
                        }
                    } catch (e) {
                        console.warn('[Reels] Canvas thumbnail extraction skipped:', e);
                    }
                };
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
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    alert('Please choose a video file or enter a video URL before publishing.');
                }
                return;
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
                            confirmButtonColor: '#4f46e5'
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
