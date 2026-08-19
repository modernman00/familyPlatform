import axios from 'axios';
import Pusher from 'pusher-js';
import { format } from 'timeago.js';
import Swal from 'sweetalert2';

export function profileFeed() {
    return {
        posts: [],
        isLoading: true,
        errorMessage: '',
        lightboxOpen: false,
        lightboxImages: [],
        lightboxIndex: 0,
        currentUserId: localStorage.getItem('requesterId') || '',
        currentFamCode: localStorage.getItem('requesterFamCode') || '',
        commentInputs: {},
        activeCommentForms: {},
        activeReactions: {},
        activeReactionBars: {},
        activeCommentReactionBars: {},
        commentEmojiOpen: {},
        pusher: null,

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
        },

        async fetchPosts() {
            this.isLoading = true;
            this.errorMessage = '';
            try {
                const response = await axios.get('/post/getAllPostCommentByFamCode');
                const rawPosts = response?.data?.message?.message;
                
                if (Array.isArray(rawPosts)) {
                    this.posts = rawPosts.map(p => this.normalizePost(p));
                } else {
                    this.posts = [];
                }
            } catch (err) {
                console.error('Failed to load posts:', err);
                this.errorMessage = 'Unable to load family posts. Please refresh or try again later.';
            } finally {
                this.isLoading = false;
            }
        },

        normalizePost(p) {
            let profImg = '/public/avatar/avatarF.png';
            const rawImg = p?.profileImg || p?.img;
            if (rawImg) {
                profImg = (rawImg.startsWith('/') || rawImg.startsWith('http')) ? rawImg : `/public/img/profile/${rawImg}`;
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
            const img = c?.profileImg || c?.img;
            return {
                comment_no: c?.comment_no,
                post_no: c?.post_no,
                id: c?.id,
                fullName: c?.fullName || 'Family Member',
                profileImg: img ? `/public/img/profile/${img}` : '/public/img/profile/avatarM.png',
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
                .filter(k => k.startsWith('post_img') && p[k] !== null && p[k] !== '')
                .map(k => p[k]);
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
                    const post = this.posts.find(p => p.post_no === postNo);
                    if (post) {
                        const newComment = {
                            comment_no: Date.now(),
                            post_no: postNo,
                            id: this.currentUserId,
                            fullName: 'You',
                            profileImg: '/public/avatar/avatarF.png',
                            comment: commentText,
                            date_created: new Date().toISOString(),
                            reactions: {},
                            totalReactions: 0
                        };
                        post.comments.push(newComment);
                    }
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
            const post = this.posts.find(p => p.post_no === postNo);
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
            const post = this.posts.find(p => p.post_no === postNo);
            if (!post?.poll) return;

            try {
                const response = await axios.post('/api/poll/vote', {
                    post_no: postNo,
                    option_id: optionId
                });
                if (response?.data?.poll) {
                    post.poll = response.data.poll;
                }
            } catch (err) {
                console.error('Failed to vote on poll:', err);
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
                if (!key || !cluster) return;

                this.pusher = new Pusher(key, { cluster, encrypted: true });

                const postsChannel = this.pusher.subscribe('posts-channel');
                postsChannel.bind('new-post', (data) => {
                    if (Array.isArray(data)) {
                        data.forEach(item => {
                            if (!this.posts.some(p => p.post_no === item?.post_no)) {
                                this.posts.unshift(this.normalizePost(item));
                            }
                        });
                    }
                });

                const commentsChannel = this.pusher.subscribe('comments-channel');
                commentsChannel.bind('new-comment', (data) => {
                    if (Array.isArray(data)) {
                        data.forEach(item => {
                            const post = this.posts.find(p => p.post_no === item?.post_no);
                            if (post && !post.comments.some(c => c.comment_no === item?.comment_no)) {
                                post.comments.push(this.normalizeComment(item));
                            }
                        });
                    }
                });

                const likesChannel = this.pusher.subscribe('likes-channel');
                likesChannel.bind('like-event', (data) => {
                    if (Array.isArray(data)) {
                        data.forEach(item => {
                            const post = this.posts.find(p => p.post_no === item?.post_no);
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
