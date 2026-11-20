import { getHeirloomGlassSvg } from "../heirloomGlassReactions";

export const feedReactionsAndTouch = {
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
};
