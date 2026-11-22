import { useState, useEffect, useRef, useCallback, useMemo } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken, notifyToast } from '../shared/bootstrap';
import { IslandErrorBoundary } from '../shared/IslandErrorBoundary';
import { createHybridRealtimeClient } from '../../components/profilePage/sovereignRealtimeBridge';
import { parseVideoUrl } from '../../components/profilePage/videoParser';
import { PostCard } from './PostCard';
import { LightboxModal } from './LightboxModal';
import { VideoModal } from './VideoModal';
import { ViewersModal } from './ViewersModal';
import {
    normalizeComment,
    normalizePost,
    unwrapFeedResponse,
} from './feedUtils';
import styles from './FeedShell.module.css';

function getDeepLinkPostNo() {
    if (typeof window === 'undefined') return null;
    const hashMatch = window.location.hash.match(/^#post-?(\d+)$/);
    if (hashMatch) return hashMatch[1];
    const params = new URLSearchParams(window.location.search);
    const qPost = params.get('post');
    return qPost && /^\d+$/.test(qPost) ? qPost : null;
}

export function FeedShell({ bootstrap = {} }) {
    const [profileBootstrap, setProfileBootstrap] = useState(bootstrap);
    const [posts, setPosts] = useState(
        Array.isArray(bootstrap.initialPosts)
            ? bootstrap.initialPosts.map(normalizePost).filter(Boolean)
            : []
    );
    const [loading, setLoading] = useState(!Array.isArray(bootstrap.initialPosts));
    const [loadingMore, setLoadingMore] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(Boolean(bootstrap.initialHasMore));
    const [streamFilter, setStreamFilter] = useState('all'); // 'all' | 'tagged'
    const [hashtagFilter, setHashtagFilter] = useState('');
    const [trendingHashtags, setTrendingHashtags] = useState([]);
    const [pendingLivePosts, setPendingLivePosts] = useState([]);
    const [highlightedPostNo, setHighlightedPostNo] = useState(null);

    const [lightboxState, setLightboxState] = useState({ isOpen: false, images: [], index: 0, postNo: null });
    const [videoState, setVideoState] = useState({ isOpen: false, videos: [], index: 0 });
    const [viewersState, setViewersState] = useState({ isOpen: false, type: 'post', targetNo: null, initialCount: 0 });

    const shellRef = useRef(null);
    const sentinelRef = useRef(null);
    const hydratedPreviewPostNosRef = useRef(new Set());
    const viewedPostNosRef = useRef(new Set());
    const viewedCommentNosRef = useRef(new Set());
    const postsRef = useRef(posts);
    postsRef.current = posts;

    // Zero-reload profile edit sync
    useEffect(() => {
        const onProfileUpdated = (e) => {
            const detail = e?.detail || {};
            setProfileBootstrap((prev) => ({
                ...prev,
                firstName: detail.firstName !== undefined ? detail.firstName : prev.firstName,
                lastName: detail.lastName !== undefined ? detail.lastName : prev.lastName,
                avatar: detail.avatar || prev.avatar,
            }));
            const currentId = String(bootstrap?.userId || '');
            if (currentId && (detail.firstName || detail.lastName || detail.avatar)) {
                setPosts((prev) => prev.map((p) => {
                    if (String(p.id) !== currentId) return p;
                    const nextFirst = detail.firstName !== undefined ? detail.firstName : p.firstName;
                    const nextLast = detail.lastName !== undefined ? detail.lastName : p.lastName;
                    const nextFull = `${nextFirst || ''} ${nextLast || ''}`.trim() || p.fullName;
                    const nextImg = detail.avatar || p.profileImg;
                    return {
                        ...p,
                        firstName: nextFirst,
                        lastName: nextLast,
                        fullName: nextFull,
                        profileImg: nextImg,
                        profilePics: nextImg,
                    };
                }));
            }
        };
        window.addEventListener('fp:profile-updated', onProfileUpdated);
        return () => window.removeEventListener('fp:profile-updated', onProfileUpdated);
    }, [bootstrap?.userId]);

    // Bridge classic #postModal (allEvents.js) post-created & post-updated events into Preact state
    useEffect(() => {
        const onPostCreated = (e) => {
            const raw = e?.detail;
            if (!raw || typeof raw !== 'object') return;
            const hydrated = normalizePost({
                ...raw,
                firstName: raw.firstName || profileBootstrap?.firstName || 'Family',
                lastName: raw.lastName || profileBootstrap?.lastName || 'Member',
                fullName: raw.fullName || `${profileBootstrap?.firstName || ''} ${profileBootstrap?.lastName || ''}`.trim() || undefined,
                profileImg: raw.profileImg || raw.img || raw.profilePics || profileBootstrap?.avatar || undefined,
            });
            if (!hydrated || !hydrated.post_no) return;
            setPosts((prev) => {
                if (prev.some((p) => String(p.post_no) === String(hydrated.post_no))) {
                    return prev.map((p) => (String(p.post_no) === String(hydrated.post_no) ? hydrated : p));
                }
                return [hydrated, ...prev];
            });
        };

        const onPostUpdated = (e) => {
            const detail = e?.detail;
            const targetNo = detail?.postNo ?? detail?.post_no;
            if (!targetNo) return;
            setPosts((prev) => prev.map((p) => {
                if (String(p.post_no) !== String(targetNo)) return p;
                return normalizePost({
                    ...p,
                    postMessage: detail.postMessage !== undefined ? detail.postMessage : p.postMessage,
                    displayMessage: undefined,
                    post_style: detail.post_style !== undefined ? detail.post_style : p.post_style,
                    ar_emoji: detail.ar_emoji !== undefined ? detail.ar_emoji : p.ar_emoji,
                });
            }));
        };

        window.addEventListener('post-created', onPostCreated);
        window.addEventListener('post-updated', onPostUpdated);
        return () => {
            window.removeEventListener('post-created', onPostCreated);
            window.removeEventListener('post-updated', onPostUpdated);
        };
    }, [profileBootstrap]);

    const fetchFeed = useCallback(async ({ nextPage = 1, append = false, filter = streamFilter, tag = hashtagFilter, attempt = 0 } = {}) => {
        if (append) {
            setLoadingMore(true);
        } else {
            setLoading(true);
            setErrorMessage('');
        }

        try {
            let url = '';
            if (tag) {
                url = `/api/hashtags/stream?tag=${encodeURIComponent(tag)}`;
            } else {
                const limit = 15;
                const offset = (Math.max(1, nextPage) - 1) * limit;
                const params = new URLSearchParams({
                    limit: String(limit),
                    offset: String(offset),
                    page: String(nextPage),
                });
                if (filter === 'tagged') params.set('filter', 'tagged');
                url = `/post/getAllPostCommentByFamCode?${params.toString()}`;
            }

            const res = await apiFetch(url, { method: 'GET', timeoutMs: 60000 }, bootstrap);
            const { items, pagination } = unwrapFeedResponse(res);
            const normalized = items.map(normalizePost).filter(Boolean);

            setPosts((prev) => {
                if (!append) return normalized;
                const seen = new Set(prev.map((p) => String(p.post_no)));
                const extra = normalized.filter((p) => !seen.has(String(p.post_no)));
                return [...prev, ...extra];
            });
            setPage(nextPage);
            setHasMore(Boolean(pagination?.has_more));
        } catch (err) {
            if (!append && attempt === 0 && err?.status === 408) {
                return fetchFeed({ nextPage, append, filter, tag, attempt: 1 });
            }
            setErrorMessage(err?.message || 'Unable to load your family feed right now.');
        } finally {
            setLoading(false);
            setLoadingMore(false);
        }
    }, [bootstrap, streamFilter, hashtagFilter]);

    const loadTrendingHashtags = useCallback(async () => {
        try {
            const res = await apiFetch('/api/hashtags/trending?limit=10', { method: 'GET', timeoutMs: 12000 }, bootstrap);
            const inner = (res?.message && typeof res.message === 'object' && !Array.isArray(res.message)) ? res.message : res;
            const tags = Array.isArray(inner?.tags) ? inner.tags : (Array.isArray(res?.tags) ? res.tags : []);
            setTrendingHashtags(tags);
        } catch {
            // non-blocking
        }
    }, [bootstrap]);

    useEffect(() => {
        if (!Array.isArray(bootstrap.initialPosts)) {
            fetchFeed({ nextPage: 1, append: false });
        }
        loadTrendingHashtags();
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    // Lazy OpenGraph / Google Photos album hydration for posts without pre-hydrated link_preview
    useEffect(() => {
        if (loading || posts.length === 0) return;

        posts.forEach((post) => {
            const pKey = String(post?.post_no || '');
            if (!pKey || hydratedPreviewPostNosRef.current.has(pKey)) return;
            if (post.link_preview || (Array.isArray(post.videos) && post.videos.length > 0)) return;
            const rawMsg = String(post.postMessage || '');
            if (!rawMsg) return;

            const allMatches = rawMsg.match(/https?:\/\/[^\s<>"']+/gi) || [];
            const candidateUrls = Array.from(new Set(allMatches.map((u) => u.replace(/[.,;!?)]+$/, '')))).filter((u) => {
                if (/\.(?:jpe?g|png|webp|avif|gif)(?:\?.*)?$/i.test(u)) return false;
                if (parseVideoUrl(u)) return false;
                return true;
            }).slice(0, 6);

            if (candidateUrls.length === 0) return;
            hydratedPreviewPostNosRef.current.add(pKey);

            Promise.all(
                candidateUrls.map((u) =>
                    apiFetch(`/api/link-preview?url=${encodeURIComponent(u)}`, { method: 'GET', timeoutMs: 20000 }, bootstrap)
                        .then((res) => (res?.status === 'success' && res?.data ? res.data : (res?.message?.data || null)))
                        .catch(() => null)
                )
            ).then((results) => {
                const valid = results.filter(Boolean);
                if (valid.length === 0) return;

                setPosts((prev) => prev.map((cur) => {
                    if (String(cur.post_no) !== pKey) return cur;
                    const mergedAlbumVideos = [];
                    const mergedProxiedVideos = [];
                    const mergedAlbumImages = [];

                    valid.forEach((d) => {
                        if (Array.isArray(d.album_videos)) {
                            d.album_videos.forEach((vUrl, vIdx) => {
                                if (!vUrl || mergedAlbumVideos.length >= 6) return;
                                mergedAlbumVideos.push(vUrl);
                                const proxied = Array.isArray(d.proxied_album_videos) && d.proxied_album_videos[vIdx]
                                    ? d.proxied_album_videos[vIdx]
                                    : vUrl;
                                mergedProxiedVideos.push(proxied);
                            });
                        }
                        if (Array.isArray(d.album_images)) {
                            d.album_images.forEach((img) => {
                                if (img && !mergedAlbumImages.includes(img) && mergedAlbumImages.length < 6) {
                                    mergedAlbumImages.push(img);
                                }
                            });
                        }
                    });

                    const primaryPreview = {
                        ...valid[0],
                        ...(mergedAlbumVideos.length > 0 ? { album_videos: mergedAlbumVideos, proxied_album_videos: mergedProxiedVideos } : {}),
                        ...(mergedAlbumImages.length > 0 ? { album_images: mergedAlbumImages } : {}),
                    };

                    return normalizePost({
                        ...cur,
                        link_preview: cur.link_preview || primaryPreview,
                        displayMessage: undefined,
                    });
                }));
            }).catch(() => {});
        });
    }, [posts, loading, bootstrap]);

    // Deep-link post scrolling & highlighting
    useEffect(() => {
        const targetNo = getDeepLinkPostNo();
        if (!targetNo || loading) return;

        const scrollAndPulse = () => {
            const el = document.getElementById(`post${targetNo}`) || document.getElementById(`post-${targetNo}`);
            if (el) {
                el.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
                setHighlightedPostNo(String(targetNo));
                setTimeout(() => setHighlightedPostNo(null), 2600);
                return true;
            }
            return false;
        };

        if (scrollAndPulse()) return;

        let cancelled = false;
        apiFetch(`/api/posts/${encodeURIComponent(targetNo)}`, { method: 'GET' }, bootstrap)
            .then((res) => {
                if (cancelled) return;
                const rawItem = res?.message?.post || res?.message || res?.post;
                const item = rawItem && typeof rawItem === 'object' ? normalizePost(rawItem) : null;
                if (item && item.post_no) {
                    setPosts((prev) => {
                        if (prev.some((p) => String(p.post_no) === String(item.post_no))) return prev;
                        return [item, ...prev];
                    });
                    setTimeout(scrollAndPulse, 60);
                }
            })
            .catch(() => {});

        return () => { cancelled = true; };
    }, [loading]); // eslint-disable-line react-hooks/exhaustive-deps

    // Infinite scroll sentinel
    useEffect(() => {
        if (typeof IntersectionObserver === 'undefined' || !sentinelRef.current) return;
        const obs = new IntersectionObserver((entries) => {
            if (entries[0]?.isIntersecting && hasMore && !loading && !loadingMore && !hashtagFilter) {
                fetchFeed({ nextPage: page + 1, append: true });
            }
        }, { rootMargin: '250px' });
        obs.observe(sentinelRef.current);
        return () => obs.disconnect();
    }, [hasMore, loading, loadingMore, page, hashtagFilter, fetchFeed]);

    // Realtime bridge (Pusher + SSE failover)
    useEffect(() => {
        if (loading) return;
        const famCode = String(bootstrap?.famCode || '').replace(/[^A-Za-z0-9_-]/g, '');
        if (!famCode) return;

        const client = createHybridRealtimeClient({
            key: bootstrap?.pusherKey || '',
            cluster: bootstrap?.pusherCluster || 'eu',
            getCsrfToken: () => getCsrfToken(bootstrap),
        });

        const channel = client.subscribe(`private-family-${famCode}`);

        channel.bind('new-post', (raw) => {
            const items = Array.isArray(raw) ? raw : (Array.isArray(raw?.posts) ? raw.posts : [raw]);
            const incoming = items.map(normalizePost).filter((p) => p && p.post_no);
            if (incoming.length === 0) return;

            const currentNos = new Set((postsRef.current || []).map((p) => String(p.post_no)));
            const selfId = String(bootstrap?.userId || '');
            const ownUnseen = [];
            const othersUnseen = [];

            incoming.forEach((p) => {
                if (currentNos.has(String(p.post_no))) return;
                if (selfId && String(p.id) === selfId) {
                    ownUnseen.push(p);
                } else {
                    othersUnseen.push(p);
                }
            });

            if (ownUnseen.length > 0) {
                setPosts((prev) => {
                    const seen = new Set(prev.map((p) => String(p.post_no)));
                    const freshOwn = ownUnseen.filter((p) => !seen.has(String(p.post_no)));
                    return freshOwn.length > 0 ? [...freshOwn, ...prev] : prev;
                });
            }

            if (othersUnseen.length > 0) {
                setPendingLivePosts((prev) => {
                    const existingNos = new Set(prev.map((p) => String(p.post_no)));
                    const fresh = othersUnseen.filter((p) => !existingNos.has(String(p.post_no)));
                    return [...fresh, ...prev].slice(0, 20);
                });
            }
        });

        channel.bind('update-post', (data) => {
            if (!data?.postNo) return;
            setPosts((prev) =>
                prev.map((p) =>
                    String(p.post_no) === String(data.postNo)
                        ? normalizePost({
                            ...p,
                            postMessage: data.postMessage ?? p.postMessage,
                            displayMessage: undefined,
                            post_style: data.post_style ?? p.post_style,
                            ar_emoji: data.ar_emoji !== undefined ? data.ar_emoji : p.ar_emoji,
                        })
                        : p
                )
            );
        });

        channel.bind('delete-post', (data) => {
            if (!data?.postNo) return;
            setPosts((prev) => prev.filter((p) => String(p.post_no) !== String(data.postNo)));
        });

        channel.bind('new-comment', (raw) => {
            const items = Array.isArray(raw) ? raw : [raw];
            items.map(normalizeComment).filter(Boolean).forEach((c) => {
                setPosts((prev) =>
                    prev.map((p) => {
                        if (String(p.post_no) !== String(c.post_no)) return p;
                        if (p.comments.some((ex) => String(ex.comment_no) === String(c.comment_no))) return p;
                        return { ...p, comments: [...p.comments, c] };
                    })
                );
            });
        });

        return () => {
            client.disconnect?.();
        };
    }, [loading, bootstrap?.famCode, bootstrap?.pusherKey, bootstrap?.pusherCluster]);

    const flushPendingLivePosts = () => {
        if (pendingLivePosts.length === 0) return;
        setPosts((prev) => {
            const seen = new Set(prev.map((p) => String(p.post_no)));
            const fresh = pendingLivePosts.filter((p) => !seen.has(String(p.post_no)));
            return [...fresh, ...prev];
        });
        setPendingLivePosts([]);
    };

    const handleStreamChange = (nextFilter) => {
        setHashtagFilter('');
        setStreamFilter(nextFilter);
        fetchFeed({ nextPage: 1, append: false, filter: nextFilter, tag: '' });
    };

    const handleSelectHashtag = useCallback((tag) => {
        const clean = String(tag || '').replace(/^#/, '').trim();
        if (!clean) return;
        setHashtagFilter(clean);
        fetchFeed({ nextPage: 1, append: false, filter: streamFilter, tag: clean });
    }, [fetchFeed, streamFilter]);

    const handleClearHashtag = () => {
        setHashtagFilter('');
        fetchFeed({ nextPage: 1, append: false, filter: streamFilter, tag: '' });
        loadTrendingHashtags();
    };

    const handleReactPost = useCallback(async (postNo, reactionType) => {
        let snapshot = null;
        setPosts((prev) =>
            prev.map((p) => {
                if (String(p.post_no) !== String(postNo)) return p;
                snapshot = { user_reaction: p.user_reaction, post_likes: p.post_likes };
                const isToggleOff = p.user_reaction === reactionType;
                const nextReaction = isToggleOff ? null : reactionType;
                let nextLikes = Number(p.post_likes || 0);
                if (p.user_reaction === null && nextReaction !== null) nextLikes += 1;
                if (p.user_reaction !== null && nextReaction === null) nextLikes = Math.max(0, nextLikes - 1);
                return { ...p, user_reaction: nextReaction, post_likes: nextLikes };
            })
        );

        try {
            await apiFetch(
                `/profileCard/postLikes?postNo=${encodeURIComponent(postNo)}&reaction=${encodeURIComponent(reactionType)}`,
                { method: 'PUT' },
                bootstrap
            );
        } catch (err) {
            if (snapshot) {
                setPosts((prev) =>
                    prev.map((p) => (String(p.post_no) === String(postNo) ? { ...p, ...snapshot } : p))
                );
            }
            notifyToast(err?.message || 'Could not save reaction.', 'error');
        }
    }, [bootstrap]);

    const handleVotePoll = useCallback(async (postNo, optionId) => {
        let snapshot = null;
        setPosts((prev) =>
            prev.map((p) => {
                if (String(p.post_no) !== String(postNo) || !p.poll) return p;
                snapshot = JSON.parse(JSON.stringify(p.poll));
                const voted = Array.isArray(p.poll.user_voted_option_id)
                    ? p.poll.user_voted_option_id.map(String)
                    : (p.poll.user_voted_option_id ? [String(p.poll.user_voted_option_id)] : []);
                const already = voted.includes(String(optionId));
                const nextOptions = (p.poll.options || []).map((opt) => {
                    if (String(opt.option_id) !== String(optionId)) return opt;
                    const count = Math.max(0, Number(opt.vote_count || 0) + (already ? -1 : 1));
                    return { ...opt, vote_count: count };
                });
                const totalVotes = nextOptions.reduce((sum, o) => sum + Number(o.vote_count || 0), 0);
                const withPct = nextOptions.map((o) => ({
                    ...o,
                    percentage: totalVotes > 0 ? Math.round((Number(o.vote_count || 0) / totalVotes) * 100) : 0,
                }));
                const nextVoted = already
                    ? voted.filter((v) => v !== String(optionId))
                    : [...voted, String(optionId)];
                return {
                    ...p,
                    poll: {
                        ...p.poll,
                        total_votes: totalVotes,
                        options: withPct,
                        user_voted_option_id: nextVoted,
                    },
                };
            })
        );

        try {
            const fd = new FormData();
            fd.append('post_no', String(postNo));
            fd.append('option_id', String(optionId));
            await apiFetch('/api/poll/vote', { method: 'POST', body: fd }, bootstrap);
        } catch (err) {
            if (snapshot) {
                setPosts((prev) =>
                    prev.map((p) => (String(p.post_no) === String(postNo) ? { ...p, poll: snapshot } : p))
                );
            }
            notifyToast(err?.message || 'Could not record your vote.', 'error');
        }
    }, [bootstrap]);

    const handleMutateComments = useCallback((postNo, nextComments) => {
        setPosts((prev) =>
            prev.map((p) => (String(p.post_no) === String(postNo) ? { ...p, comments: nextComments } : p))
        );
    }, []);

    const handleUpdatePost = useCallback((postNo, patch) => {
        setPosts((prev) =>
            prev.map((p) => (String(p.post_no) === String(postNo) ? { ...p, ...patch } : p))
        );
    }, []);

    const handleDeletePost = useCallback((postNo) => {
        setPosts((prev) => prev.filter((p) => String(p.post_no) !== String(postNo)));
    }, []);

    const handleOpenLightbox = useCallback((images, index, p) => {
        setLightboxState({ isOpen: true, images, index, postNo: p.post_no });
    }, []);

    const handleOpenVideo = useCallback((videos, index) => {
        setVideoState({ isOpen: true, videos, index });
    }, []);

    const handleOpenPostViewers = useCallback((post) => {
        if (!post?.post_no) return;
        setViewersState({
            isOpen: true,
            type: 'post',
            targetNo: post.post_no,
            initialCount: Number(post.views_count ?? post.view_count ?? 0),
        });
    }, []);

    const handleOpenCommentViewers = useCallback((comment) => {
        if (!comment?.comment_no) return;
        setViewersState({
            isOpen: true,
            type: 'comment',
            targetNo: comment.comment_no,
            initialCount: Number(comment.views_count ?? comment.view_count ?? 0),
        });
    }, []);

    // Record post & comment views when cards/comments scroll into view
    useEffect(() => {
        if (loading || typeof IntersectionObserver === 'undefined') return undefined;

        const postObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const postNo = entry.target.getAttribute('data-post-no');
                postObserver.unobserve(entry.target);
                if (!postNo || viewedPostNosRef.current.has(postNo)) return;
                viewedPostNosRef.current.add(postNo);
                apiFetch('/api/posts/view', { method: 'POST', body: { post_no: Number(postNo) || postNo } }, bootstrap)
                    .then((res) => {
                        if (typeof res?.views_count === 'number') {
                            setPosts((prev) =>
                                prev.map((p) => (String(p.post_no) === String(postNo) ? { ...p, views_count: res.views_count, view_count: res.views_count } : p))
                            );
                        }
                    })
                    .catch(() => {});
            });
        }, { threshold: 0.35 });

        const commentObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const commentNo = entry.target.getAttribute('data-comment-no');
                commentObserver.unobserve(entry.target);
                if (!commentNo || String(commentNo).startsWith('temp_') || viewedCommentNosRef.current.has(commentNo)) return;
                viewedCommentNosRef.current.add(commentNo);
                apiFetch('/api/comments/view', { method: 'POST', body: { comment_no: Number(commentNo) || commentNo } }, bootstrap)
                    .then((res) => {
                        if (typeof res?.views_count === 'number') {
                            setPosts((prev) =>
                                prev.map((p) => {
                                    if (!Array.isArray(p.comments) || !p.comments.some((c) => String(c.comment_no) === String(commentNo))) {
                                        return p;
                                    }
                                    return {
                                        ...p,
                                        comments: p.comments.map((c) => (
                                            String(c.comment_no) === String(commentNo)
                                                ? { ...c, views_count: res.views_count, view_count: res.views_count }
                                                : c
                                        )),
                                    };
                                })
                            );
                        }
                    })
                    .catch(() => {});
            });
        }, { threshold: 0.3 });

        const postEls = document.querySelectorAll('.feed-post-card[data-post-no]');
        postEls.forEach((el) => {
            const postNo = el.getAttribute('data-post-no');
            if (postNo && !viewedPostNosRef.current.has(postNo)) postObserver.observe(el);
        });

        const commentEls = document.querySelectorAll('.feed-comment-item[data-comment-no]');
        commentEls.forEach((el) => {
            const cNo = el.getAttribute('data-comment-no');
            if (cNo && !viewedCommentNosRef.current.has(cNo)) commentObserver.observe(el);
        });

        return () => {
            postObserver.disconnect();
            commentObserver.disconnect();
        };
    }, [loading, posts.length, bootstrap]);

    const currentUserId = String(profileBootstrap?.userId || '');
    const taggedPostsCount = useMemo(
        () => posts.filter((p) => Array.isArray(p.post_tags) && p.post_tags.some((t) => String(t.user_id || t.id) === currentUserId)).length,
        [posts, currentUserId]
    );

    // Programmatic bridge for E2E & legacy integrations querying Alpine.$data(.feed-posts-container)
    useEffect(() => {
        const el = shellRef.current;
        if (!el) return undefined;
        const postsProxy = {
            get length() {
                return postsRef.current.length;
            },
            unshift(...rawItems) {
                const normalizedItems = rawItems
                    .map((item) => {
                        const norm = normalizePost(item);
                        if (norm && item && item.is_own_post === true) {
                            norm.is_own_post = true;
                        }
                        return norm;
                    })
                    .filter(Boolean);
                if (normalizedItems.length > 0) {
                    setPosts((prev) => [...normalizedItems, ...prev]);
                }
                return postsRef.current.length + normalizedItems.length;
            },
            push(...rawItems) {
                const normalizedItems = rawItems
                    .map((item) => {
                        const norm = normalizePost(item);
                        if (norm && item && item.is_own_post === true) {
                            norm.is_own_post = true;
                        }
                        return norm;
                    })
                    .filter(Boolean);
                if (normalizedItems.length > 0) {
                    setPosts((prev) => [...prev, ...normalizedItems]);
                }
                return postsRef.current.length + normalizedItems.length;
            },
        };
        const bridge = {
            currentUserId,
            loggedInUserId: currentUserId,
            posts: postsProxy,
            openLightbox: (images, index = 0, post = null) => {
                handleOpenLightbox(images, index, post);
            },
        };
        el._x_dataStack = [bridge];
        if (typeof window !== 'undefined') {
            window.__FP_FEED_CONTROLLER__ = bridge;
        }
        return () => {
            delete el._x_dataStack;
            if (typeof window !== 'undefined' && window.__FP_FEED_CONTROLLER__ === bridge) {
                delete window.__FP_FEED_CONTROLLER__;
            }
        };
    }, [currentUserId, handleOpenLightbox]);

    const activeLightboxPost = lightboxState.postNo
        ? posts.find((p) => String(p.post_no) === String(lightboxState.postNo)) || null
        : null;

    return (
        <div ref={shellRef} id="postIt" className="feed-posts-container" data-testid="feed-island-shell">
            {/* Error Alert */}
            {errorMessage && (
                <div className="alert alert-danger shadow-sm rounded-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2" role="alert">
                    <div>
                        <i className="bi bi-exclamation-triangle-fill me-2" aria-hidden="true" />
                        <span>{errorMessage}</span>
                    </div>
                    <button
                        type="button"
                        className="btn btn-sm btn-outline-danger rounded-pill px-3"
                        style={{ minHeight: '44px' }}
                        onClick={() => fetchFeed({ nextPage: 1, append: false })}
                    >
                        Retry
                    </button>
                </div>
            )}

            {/* Active Family Hashtag Filter Stream Banner */}
            {hashtagFilter && (
                <div
                    className="card border-0 shadow-sm mb-4 p-3"
                    style={{
                        borderRadius: '14px',
                        background: 'linear-gradient(135deg, #fdf5f5 0%, #f5dede 100%)',
                        borderLeft: '4px solid #a31621',
                    }}
                    role="status"
                >
                    <div className="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div className="d-flex align-items-center gap-2">
                            <span
                                className="badge rounded-pill text-white px-3 py-1 shadow-xs"
                                style={{ background: '#a31621', fontSize: '0.88rem', letterSpacing: '0.3px' }}
                            >
                                <i className="bi bi-hash me-1" aria-hidden="true" />
                                {hashtagFilter}
                            </span>
                            <span className="text-dark fw-bold small" style={{ fontSize: '0.9rem' }}>
                                Family Memory Stream
                            </span>
                        </div>
                        <button
                            type="button"
                            className="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1"
                            onClick={handleClearHashtag}
                            style={{ fontSize: '0.82rem', fontWeight: 600, minHeight: '44px' }}
                        >
                            <i className="bi bi-x-circle-fill" aria-hidden="true" /> Show All Posts
                        </button>
                    </div>
                </div>
            )}

            {/* Active Tagged With You Stream Banner */}
            {streamFilter === 'tagged' && !hashtagFilter && (
                <div
                    className="card border-0 shadow-sm mb-4 p-3"
                    style={{
                        borderRadius: '14px',
                        background: 'linear-gradient(135deg, #fdf5f5 0%, #f5dede 100%)',
                        borderLeft: '4px solid #a31621',
                    }}
                >
                    <div className="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div className="d-flex align-items-center gap-2">
                            <span
                                className="badge rounded-pill text-white px-3 py-1 shadow-xs"
                                style={{ background: '#a31621', fontSize: '0.88rem', letterSpacing: '0.3px' }}
                            >
                                <i className="bi bi-tag-fill me-1" aria-hidden="true" />
                                Tagged With You
                            </span>
                            <span className="text-dark fw-bold small" style={{ fontSize: '0.9rem' }}>
                                Your Living Heritage Scrapbook
                            </span>
                        </div>
                        <button
                            type="button"
                            className="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1"
                            onClick={() => handleStreamChange('all')}
                            style={{ fontSize: '0.82rem', fontWeight: 600, minHeight: '44px' }}
                        >
                            <i className="bi bi-arrow-left-circle-fill" aria-hidden="true" /> Show All Memories
                        </button>
                    </div>
                </div>
            )}

            {/* Feed Stream Selector Tabs (All Posts vs Tagged With You) */}
            <div className="feed-tabs-container mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div
                    className="btn-group p-1 rounded-pill bg-light border shadow-2xs"
                    role="tablist"
                    aria-label="Feed memory stream tabs"
                >
                    <button
                        type="button"
                        role="tab"
                        aria-selected={streamFilter === 'all' && !hashtagFilter}
                        className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold transition-all ${streamFilter === 'all' && !hashtagFilter ? 'btn-primary text-white shadow-xs' : 'text-muted border-0 bg-transparent'}`}
                        onClick={() => handleStreamChange('all')}
                        style={{ fontSize: '0.82rem', whiteSpace: 'nowrap', flexShrink: 0, minHeight: '44px' }}
                    >
                        <i className="bi bi-clock-history me-1" aria-hidden="true" />
                        All Family Memories
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={streamFilter === 'tagged' && !hashtagFilter}
                        className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold transition-all d-inline-flex align-items-center gap-1 ${streamFilter === 'tagged' && !hashtagFilter ? 'btn-primary text-white shadow-xs' : 'text-muted border-0 bg-transparent'}`}
                        onClick={() => handleStreamChange('tagged')}
                        style={{ fontSize: '0.82rem', whiteSpace: 'nowrap', flexShrink: 0, minHeight: '44px' }}
                    >
                        <i className="bi bi-tag-fill me-1" aria-hidden="true" />
                        <span>Tagged With You</span>
                        <span
                            className={`badge rounded-pill ${streamFilter === 'tagged' && !hashtagFilter ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark'}`}
                            style={{ fontSize: '0.68rem' }}
                        >
                            {taggedPostsCount}
                        </span>
                    </button>
                </div>
                {streamFilter === 'tagged' && (
                    <span className="text-muted" style={{ fontSize: '0.875rem' }}>
                        Showing memories featuring you
                    </span>
                )}
            </div>

            {/* Trending Family Hashtags Carousel */}
            {trendingHashtags.length > 0 && !hashtagFilter && (
                <div
                    className="trending-hashtags-tray card border-0 shadow-sm mb-4 p-3"
                    style={{ borderRadius: '14px', backgroundColor: 'var(--card-bg)' }}
                >
                    <div className="d-flex align-items-center justify-content-between mb-2">
                        <div className="d-flex align-items-center gap-2">
                            <i className="bi bi-tags-fill text-primary" style={{ fontSize: '1rem' }} aria-hidden="true" />
                            <span className="fw-bold small" style={{ fontSize: '0.88rem', color: 'var(--text-main, #0f172a)' }}>
                                Family Topics &amp; Milestones
                            </span>
                        </div>
                        <span className="text-muted" style={{ fontSize: '0.875rem' }}>Click to filter</span>
                    </div>
                    <div className="d-flex gap-2 flex-wrap align-items-center">
                        {trendingHashtags.map((item) => (
                            <button
                                key={item.tag}
                                type="button"
                                className="btn btn-sm family-hashtag-pill d-inline-flex align-items-center gap-1 shadow-xs"
                                style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                onClick={() => handleSelectHashtag(item.tag)}
                                title={`View family memories tagged #${item.tag}`}
                            >
                                <span className="fw-semibold">#{item.tag}</span>
                                <span className="badge rounded-pill bg-white text-primary px-1 py-0 ms-1" style={{ fontSize: '0.68rem' }}>
                                    {item.count}
                                </span>
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {/* Pending Live Posts Banner */}
            {pendingLivePosts.length > 0 && (
                <div className={styles.liveBanner} role="status">
                    <span className="fw-semibold">
                        {pendingLivePosts.length} new family {pendingLivePosts.length === 1 ? 'memory' : 'memories'} shared
                    </span>
                    <button type="button" className={styles.liveBannerBtn} onClick={flushPendingLivePosts}>
                        Show New Posts
                    </button>
                </div>
            )}

            {/* Classic Skeleton Loading State */}
            {loading && (
                <div aria-label="Loading family memories">
                    {[1, 2].map((n) => (
                        <div
                            key={n}
                            className="skeleton-post shadow-sm mb-4"
                            style={{
                                background: 'var(--card-bg, #ffffff)',
                                borderRadius: '16px',
                                padding: '20px',
                                textAlign: 'left',
                            }}
                        >
                            <div style={{ display: 'flex', alignItems: 'center', marginBottom: '15px' }}>
                                <div style={{ width: '48px', height: '48px', borderRadius: '50%', background: '#e2e8f0', marginRight: '15px' }} />
                                <div style={{ flexGrow: 1 }}>
                                    <div style={{ height: '12px', background: '#e2e8f0', borderRadius: '4px', width: '40%', marginBottom: '8px' }} />
                                    <div style={{ height: '10px', background: '#e2e8f0', borderRadius: '4px', width: '20%' }} />
                                </div>
                            </div>
                            <div style={{ height: '14px', background: '#e2e8f0', borderRadius: '4px', width: '100%', marginBottom: '10px' }} />
                            <div style={{ height: '14px', background: '#e2e8f0', borderRadius: '4px', width: '85%', marginBottom: '10px' }} />
                            <div style={{ height: '14px', background: '#e2e8f0', borderRadius: '4px', width: '60%' }} />
                        </div>
                    ))}
                </div>
            )}

            {/* Classic Empty State */}
            {!loading && !errorMessage && posts.length === 0 && (
                <div
                    className="card border-0 shadow-sm p-5 text-center mb-4"
                    style={{ borderRadius: '16px', background: 'var(--card-bg, #ffffff)' }}
                >
                    <div className="mb-3">
                        <i
                            className={`bi text-muted ${streamFilter === 'tagged' ? 'bi-tags-fill' : 'bi-chat-heart'}`}
                            style={{ fontSize: '3rem' }}
                            aria-hidden="true"
                        />
                    </div>
                    <h5 className="fw-bold" style={{ color: 'var(--text-main, #0f172a)' }}>
                        {hashtagFilter
                            ? `No memories tagged #${hashtagFilter} yet`
                            : (streamFilter === 'tagged' ? 'No tagged memories yet' : 'No family posts yet')}
                    </h5>
                    <p className="text-muted mb-3">
                        {hashtagFilter
                            ? 'Return to the full family stream or share the first memory with this hashtag.'
                            : (streamFilter === 'tagged'
                                ? 'When relatives tag you in stories or milestone photos, they will appear in this personal scrapbook.'
                                : 'Share the first memory or event with your family above!')}
                    </p>
                    {hashtagFilter && (
                        <div>
                            <button
                                type="button"
                                className="btn btn-primary rounded-pill px-4 fw-semibold"
                                style={{ minHeight: '44px' }}
                                onClick={handleClearHashtag}
                            >
                                Show All Posts
                            </button>
                        </div>
                    )}
                </div>
            )}

            {/* Posts List */}
            {!loading && posts.map((post) => (
                <IslandErrorBoundary
                    key={post.post_no}
                    name={`PostCard-${post.post_no}`}
                    fallbackTitle="This family memory could not be displayed."
                    compact
                >
                    <PostCard
                        post={post}
                        bootstrap={profileBootstrap}
                        isHighlighted={String(highlightedPostNo) === String(post.post_no)}
                        onReactPost={handleReactPost}
                        onVotePoll={handleVotePoll}
                        onMutateComments={handleMutateComments}
                        onUpdatePost={handleUpdatePost}
                        onDeletePost={handleDeletePost}
                        onSelectHashtag={handleSelectHashtag}
                        onOpenLightbox={handleOpenLightbox}
                        onOpenVideo={handleOpenVideo}
                        onOpenPostViewers={handleOpenPostViewers}
                        onOpenCommentViewers={handleOpenCommentViewers}
                    />
                </IslandErrorBoundary>
            ))}

            {/* Pagination / Infinite Scroll Sentinel */}
            {!loading && hasMore && !hashtagFilter && (
                <div ref={sentinelRef} className="d-flex justify-content-center py-3">
                    <button
                        type="button"
                        className="btn btn-outline-secondary rounded-pill px-4 fw-semibold"
                        style={{ minHeight: '44px' }}
                        disabled={loadingMore}
                        onClick={() => fetchFeed({ nextPage: page + 1, append: true })}
                    >
                        {loadingMore ? 'Loading more memories…' : 'Load More Memories'}
                    </button>
                </div>
            )}

            {/* Modals */}
            <LightboxModal
                isOpen={lightboxState.isOpen}
                images={lightboxState.images}
                initialIndex={lightboxState.index}
                post={activeLightboxPost}
                bootstrap={profileBootstrap}
                onClose={() => setLightboxState({ isOpen: false, images: [], index: 0, postNo: null })}
                onReactPost={handleReactPost}
                onMutateComments={handleMutateComments}
            />

            <VideoModal
                isOpen={videoState.isOpen}
                videos={videoState.videos}
                initialIndex={videoState.index}
                onClose={() => setVideoState({ isOpen: false, videos: [], index: 0 })}
            />

            <ViewersModal
                isOpen={viewersState.isOpen}
                type={viewersState.type}
                targetNo={viewersState.targetNo}
                initialCount={viewersState.initialCount}
                bootstrap={profileBootstrap}
                onClose={() => setViewersState({ isOpen: false, type: 'post', targetNo: null, initialCount: 0 })}
            />
        </div>
    );
}
