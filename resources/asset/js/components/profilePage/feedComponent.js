import axios from 'axios';
import Pusher from 'pusher-js';
import { format } from 'timeago.js';
import Swal from 'sweetalert2';
import { extractVideoFromText, cleanPostMessage } from './videoParser';
import { getCsrfToken } from '../global';

export function profileFeed(opts = {}) {
    return {
        posts: [],
        isLoading: true,
        errorMessage: '',
        lightboxOpen: false,
        lightboxImages: [],
        lightboxIndex: 0,
        currentUserId: opts.userId || localStorage.getItem('requesterId') || '',
        currentFamCode: opts.famCode || localStorage.getItem('requesterFamCode') || '',
        commentInputs: {},
        activeCommentForms: {},
        activeReactions: {},
        activeReactionBars: {},
        activeCommentReactionBars: {},
        commentEmojiOpen: {},
        pusher: null,
        editingCommentNo: null,
        editCommentText: '',
        csrfOptions: {
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN',
        },

        // Emoji map shared across post + comment reactions
        emojiMap: {
            like:  { icon: '👍', label: 'Like'  },
            love:  { icon: '❤️', label: 'Love'  },
            haha:  { icon: '😂', label: 'Haha'  },
            shock: { icon: '😮', label: 'Wow'   },
            sad:   { icon: '😢', label: 'Sad'   },
        },

        commentEmojiMap: {
            like:  '👍', love: '❤️', haha: '😄',
            wow:   '😮', sad:  '😢', angry: '😠',
        },

        async init() {
            // Register listeners before awaiting the initial fetch - a post created (or a
            // Pusher event received) while that first fetch is still in flight must not be
            // silently dropped because nothing was listening for it yet.
            this.initPusher();
            this.initEventListeners();
            await this.fetchPosts();
            this.scrollToHashPost();
        },

        // Jump to (and briefly highlight) the post referenced by a #postNNN link
        // (e.g. from an email/push notification). Posts render asynchronously, so the
        // browser's own hash-scroll-on-load never finds the element in time.
        scrollToHashPost() {
            const hash = window.location.hash;
            if (!hash || !hash.startsWith('#post')) return;

            this.$nextTick(() => {
                const target = document.getElementById(hash.slice(1));
                if (!target) return;
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.classList.add('highlighted-post');
                setTimeout(() => target.classList.remove('highlighted-post'), 2500);
            });
        },

        async fetchPosts() {
            this.isLoading = this.posts.length === 0;
            this.errorMessage = '';

            // 1. Instant offline cache hydration (Facebook standard)
            if (window.offlineSync && typeof window.offlineSync.getCachedFeed === 'function') {
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
                const response = await axios.get('/post/getAllPostCommentByFamCode');
                const rawPosts = response?.data?.message?.message;
                
                if (Array.isArray(rawPosts)) {
                    this.posts = rawPosts.map(p => this.normalizePost(p));
                    // Update offline store in background
                    if (window.offlineSync && typeof window.offlineSync.cacheFeed === 'function') {
                        window.offlineSync.cacheFeed(rawPosts).catch(() => {});
                    }
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

        normalizePost(p) {
            let profImg = '/public/avatar/avatarF.png';
            const rawImg = p?.img || p?.profileImg;
            if (rawImg) {
                profImg = (rawImg.startsWith('/') || rawImg.startsWith('http')) ? rawImg : `/resources/images/profile/${rawImg}`;
            }
            return {
                post_no: p?.post_no,
                id: p?.id,
                fullName: p?.fullName || 'Family Member',
                profileImg: profImg,
                postFamCode: p?.postFamCode || '',
                date_created: p?.date_created || new Date().toISOString(),
                post_time: p?.post_time || p?.date_created || new Date().toISOString(),
                postMessage: p?.postMessage || '',
                displayMessage: cleanPostMessage(p?.postMessage || '', extractVideoFromText(p?.postMessage)),
                video: extractVideoFromText(p?.postMessage),
                post_likes: parseInt(p?.post_likes || 0, 10),
                images: this.extractImages(p),
                poll: p?.poll || null,
                reactions: Array.isArray(p?.reactions) ? p.reactions : [],
                user_reaction: p?.user_reaction || null,
                comments: Array.isArray(p?.comments) ? p.comments.map(c => this.normalizeComment(c)) : [],
                isLiked: false,
            };
        },

        normalizeComment(c) {
            const img = c?.img || c?.profileImg;
            return {
                comment_no: c?.comment_no,
                post_no: c?.post_no,
                id: c?.id,
                fullName: c?.fullName || 'Family Member',
                profileImg: img ? `/resources/images/profile/${img}` : '/public/avatar/avatarM.png',
                comment: c?.comment || '',
                date_created: c?.date_created || '',
                comment_time: c?.comment_time || c?.date_created || '',
                reactions: c?.reactions?.counts ?? {},
                totalReactions: c?.reactions?.counts?.totalReactions ?? 0,
                userReaction: c?.user_reaction || null,
            };
        },

        extractImages(p) {
            if (!p || typeof p !== 'object') return [];
            return Object.keys(p)
                .filter(k => k.startsWith('post_img') && p[k] !== null && p[k] !== '' && typeof p[k] === 'string')
                .map(k => p[k].trim())
                .filter(img => img && img !== 'null' && img !== 'undefined' && img !== 'none' && !img.includes('no_image'));
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            try {
                return format(dateStr);
            } catch (e) {
                return dateStr;
            }
        },

        toggleCommentForm(postNo) {
            this.activeCommentForms[postNo] = !this.activeCommentForms[postNo];
        },

        async submitComment(postNo) {
            const commentText = (this.commentInputs[postNo] || '').trim();
            if (!commentText) return;

            const formData = new FormData();
            formData.append('post_no', postNo);
            formData.append('comment', commentText);

            try {
                const response = await axios.post('/postCommentProfile', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                if (response?.data?.status === 'success' || response?.status === 200) {
                    // Don't append an optimistic local copy here: the Pusher
                    // 'new-comment' handler (initPusher, below) already adds the
                    // real broadcast comment in real time. Since that one carries
                    // the real comment_no (this one only has a fake Date.now()
                    // placeholder), the dedup check never matches and both stayed
                    // on screen — one labeled "You", one with the real name.
                    this.commentInputs[postNo] = '';
                }
            } catch (err) {
                console.error('Failed to submit comment:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: err?.response?.data?.message || 'Failed to submit comment.',
                    confirmButtonColor: '#3085d6'
                });
            }
        },

        // ── Post edit/delete (author only) ──────────────────────────────────

        isOwnPost(post) {
            return String(post?.id) === String(this.currentUserId);
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
                cancelButtonColor: '#3085d6',
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
                    confirmButtonColor: '#3085d6'
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

            try {
                await axios.put(`/comment/${commentNo}`, { comment: text }, this.csrfOptions);
                for (const post of this.posts) {
                    const comment = post.comments.find(c => String(c.comment_no) === String(commentNo));
                    if (comment) {
                        comment.comment = text;
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
                    confirmButtonColor: '#3085d6'
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
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });
            if (!result.isConfirmed) return;

            try {
                await axios.delete(`/comment/${commentNo}`, this.csrfOptions);
                const post = this.posts.find(p => String(p.post_no) === String(postNo));
                if (post) {
                    post.comments = post.comments.filter(c => String(c.comment_no) !== String(commentNo));
                }
            } catch (err) {
                console.error('Failed to delete comment:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: err?.response?.data?.message || 'Failed to delete comment.',
                    confirmButtonColor: '#3085d6'
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
            this.reactToComment(postNo, commentNo, reactionType);
        },

        // ── Comment emoji picker for text input ─────────────────────────────
        toggleCommentEmoji(postNo) {
            this.commentEmojiOpen[postNo] = !this.commentEmojiOpen[postNo];
        },

        insertEmojiIntoComment(postNo, emoji) {
            const current = this.commentInputs[postNo] || '';
            this.commentInputs[postNo] = current + emoji;
            this.commentEmojiOpen[postNo] = false;
        },

        // ── React to a comment with an emoji ───────────────────────────────
        async reactToComment(postNo, commentNo, reactionType) {
            const post = this.posts.find(p => p.post_no === postNo);
            if (!post) return;
            const comment = post.comments.find(c => c.comment_no === commentNo);
            if (!comment) return;

            // Optimistic UI — update local state immediately
            const prevReactions = { ...comment.reactions };
            const prevTotal = comment.totalReactions;

            const isSame = comment.userReaction === reactionType;
            const wasReaction = comment.userReaction;

            if (isSame) {
                // Toggle off
                comment.reactions[reactionType] = Math.max(0, (comment.reactions[reactionType] || 1) - 1);
                comment.totalReactions = Math.max(0, comment.totalReactions - 1);
                comment.userReaction = null;
            } else {
                // Remove old reaction from counts if switching
                if (wasReaction && comment.reactions[wasReaction]) {
                    comment.reactions[wasReaction] = Math.max(0, comment.reactions[wasReaction] - 1);
                    comment.totalReactions = Math.max(0, comment.totalReactions - 1);
                }
                comment.reactions[reactionType] = (comment.reactions[reactionType] || 0) + 1;
                comment.totalReactions += 1;
                comment.userReaction = reactionType;
            }

            // Hide the reaction bar after clicking
            this.activeCommentReactionBars[commentNo] = false;

            try {
                const formData = new FormData();
                formData.append('comment_no', commentNo);
                formData.append('reaction', reactionType);

                const res = await axios.post('/api/reactions/add', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                // Sync server counts if returned
                if (res?.data?.message?.counts?.counts) {
                    const serverCounts = res.data.message.counts.counts;
                    comment.reactions = { ...serverCounts };
                    comment.totalReactions = serverCounts?.totalReactions ?? comment.totalReactions;
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
                .filter(([k, v]) => !['comment_no', 'total', 'totalReactions'].includes(k) && Number(v) > 0)
                .sort(([, a], [, b]) => Number(b) - Number(a))
                .slice(0, 3)
                .map(([label, count]) => ({ emoji: map[label] ?? '👍', count: Number(count) }));
        },

        // ── React to a post with an emoji (post-level reactions) ────────────
        async reactToPost(postNo, reactionType) {
            const post = this.posts.find(p => String(p.post_no) === String(postNo));
            if (!post) return;

            const previousReaction = post.user_reaction;
            post.user_reaction = (post.user_reaction === reactionType) ? null : reactionType;

            // Optimistic like counter bump
            if (previousReaction === null) post.post_likes += 1;
            else if (post.user_reaction === null) post.post_likes = Math.max(0, post.post_likes - 1);

            // Close the reaction bar
            this.activeReactionBars[postNo] = false;

            try {
                const formData = new FormData();
                formData.append('post_no', postNo);
                formData.append('reaction', reactionType);
                await axios.put('/profileCard/postLikes?postNo=' + postNo);
            } catch (err) {
                console.error('Failed to record post reaction:', err);
                post.user_reaction = previousReaction;
                // Revert like counter
                if (previousReaction === null) post.post_likes = Math.max(0, post.post_likes - 1);
                else if (post.user_reaction === null) post.post_likes += 1;
            }
        },


        async votePoll(postNo, optionId) {
            const post = this.posts.find(p => String(p.post_no) === String(postNo));
            if (!post?.poll || !Array.isArray(post.poll.options)) return;

            // Snapshot for rollback
            const snapshot = JSON.parse(JSON.stringify(post.poll));

            // ── Optimistic update ──────────────────────────────────────────
            const voted = Array.isArray(post.poll.user_voted_option_id)
                ? [...post.poll.user_voted_option_id]
                : [];
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
                const formData = new FormData();
                formData.append('post_no', postNo);
                formData.append('option_id', optionId);

                const response = await axios.post('/api/poll/vote', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
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
            }
        },

        initEventListeners() {
            window.addEventListener('post-created', (event) => {
                const newPostData = event?.detail;
                if (newPostData && typeof newPostData === 'object') {
                    const normalized = this.normalizePost(newPostData);
                    if (!this.posts.some(p => String(p.post_no) === String(normalized.post_no))) {
                        this.posts.unshift(normalized);
                    }
                } else {
                    this.fetchPosts();
                }
            });

            // Dispatched by allEvents.js after a successful post edit (the acting
            // user's own tab — other tabs get it via the update-post Pusher bind).
            window.addEventListener('post-updated', (event) => {
                const { postNo, postMessage } = event?.detail || {};
                const post = this.posts.find(p => String(p.post_no) === String(postNo));
                if (post) post.postMessage = postMessage;
            });
        },

        openLightbox(images, index) {
            this.lightboxImages = images;
            this.lightboxIndex = index;
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        },

        closeLightbox() {
            this.lightboxOpen = false;
            this.lightboxImages = [];
            this.lightboxIndex = 0;
            document.body.style.overflow = ''; // Restore background scrolling
        },

        nextLightboxImage() {
            if (this.lightboxImages.length > 0) {
                this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxImages.length;
            }
        },

        prevLightboxImage() {
            if (this.lightboxImages.length > 0) {
                this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxImages.length) % this.lightboxImages.length;
            }
        },

        initPusher() {
            try {
                const key = process.env.MIX_PUSHER_APP_KEY;
                const cluster = process.env.MIX_PUSHER_APP_CLUSTER;
                const famCode = (this.currentFamCode || '').replace(/[^A-Za-z0-9_-]/g, '');
                if (!key || !cluster || !famCode) return;

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
                    if (post) post.postMessage = data?.postMessage ?? post.postMessage;
                });

                channel.bind('delete-comment', (data) => {
                    const post = this.posts.find(p => String(p.post_no) === String(data?.postNo));
                    if (post) {
                        post.comments = post.comments.filter(c => String(c.comment_no) !== String(data?.commentNo));
                    }
                });
                channel.bind('update-comment', (data) => {
                    const post = this.posts.find(p => String(p.post_no) === String(data?.postNo));
                    const comment = post?.comments.find(c => String(c.comment_no) === String(data?.commentNo));
                    if (comment) comment.comment = data?.comment ?? comment.comment;
                });

                channel.bind('like-event', (data) => {
                    if (Array.isArray(data)) {
                        data.forEach(item => {
                            const post = this.posts.find(p => String(p.post_no) === String(item?.post_no));
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
