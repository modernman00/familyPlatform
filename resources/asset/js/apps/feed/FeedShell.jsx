import { useState, useEffect, useRef, useCallback, useMemo } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken, notifyToast } from '../shared/bootstrap';
import { IslandErrorBoundary } from '../shared/IslandErrorBoundary';
import { createHybridRealtimeClient } from '../../components/profilePage/sovereignRealtimeBridge';
import { parseVideoUrl } from '../../components/profilePage/videoParser';
import { PostCard } from './PostCard';
import { LightboxModal } from './LightboxModal';
import { VideoModal } from './VideoModal';
import { WhatsNewBanner } from './WhatsNewBanner';
import { FeedInteractiveShowcase, SHOWCASE_ITEMS } from './FeedInteractiveShowcase';
import { createLazyModal } from '../shared/lazyIslandModal';

const LazyViewersModal = createLazyModal(
    () => import(/* webpackChunkName: "feed-modals" */ './ViewersModal'),
    'ViewersModal'
);
const LazySharePortalModal = createLazyModal(
    () => import(/* webpackChunkName: "feed-modals" */ './SharePortalModal'),
    'SharePortalModal'
);
import {
    extractPreviewableUrls,
    getAvatarUrl,
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
    const qPost = params.get('post_no') || params.get('post');
    return qPost && /^\d+$/.test(qPost) ? qPost : null;
}

const SWR_CACHE_TTL_MS = 24 * 60 * 60 * 1000;

function getSwrCacheKey(bootstrap) {
    const uid = String(bootstrap?.userId || '').trim();
    const fam = String(bootstrap?.famCode || '').trim();
    if (!uid || !fam) return null;
    return `fp_feed_swr_v1_${uid}_${fam}`;
}

function readSwrFeedCache(bootstrap) {
    if (typeof window === 'undefined' || !window.localStorage) return null;
    const key = getSwrCacheKey(bootstrap);
    if (!key) return null;
    try {
        const raw = window.localStorage.getItem(key);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        if (!parsed || !Array.isArray(parsed.posts) || parsed.posts.length === 0) return null;
        if (typeof parsed.ts === 'number' && Date.now() - parsed.ts > SWR_CACHE_TTL_MS) return null;
        return parsed;
    } catch {
        return null;
    }
}

function writeSwrFeedCache(bootstrap, { posts, hasMore, nextCursor }) {
    if (typeof window === 'undefined' || !window.localStorage) return;
    const key = getSwrCacheKey(bootstrap);
    if (!key || !Array.isArray(posts)) return;
    try {
        window.localStorage.setItem(key, JSON.stringify({
            ts: Date.now(),
            posts: posts.slice(0, 10),
            hasMore: Boolean(hasMore),
            nextCursor: nextCursor || null,
        }));
    } catch {
        // Ignore storage quota or private-mode errors
    }
}

export function FeedShell({ bootstrap = {} }) {
    const hasBootstrapPosts = Array.isArray(bootstrap.initialPosts);
    const swrSeed = useMemo(
        () => (hasBootstrapPosts ? null : readSwrFeedCache(bootstrap)),
        // eslint-disable-next-line react-hooks/exhaustive-deps
        []
    );

    const [profileBootstrap, setProfileBootstrap] = useState(bootstrap);
    const [posts, setPosts] = useState(() => {
        if (hasBootstrapPosts) {
            return bootstrap.initialPosts.map(normalizePost).filter(Boolean);
        }
        if (swrSeed?.posts) {
            return swrSeed.posts.map(normalizePost).filter(Boolean);
        }
        return [];
    });
    const [loading, setLoading] = useState(!hasBootstrapPosts && !swrSeed);
    const [loadingMore, setLoadingMore] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(
        hasBootstrapPosts ? Boolean(bootstrap.initialHasMore) : Boolean(swrSeed?.hasMore)
    );
    const [nextCursor, setNextCursor] = useState(
        hasBootstrapPosts ? (bootstrap.initialNextCursor || null) : (swrSeed?.nextCursor || null)
    );
    const [streamFilter, setStreamFilter] = useState('all'); // 'all' | 'tagged'
    const [hashtagFilter, setHashtagFilter] = useState('');
    const [personFilter, setPersonFilter] = useState(null); // { id, name, img } | null
    const [trendingHashtags, setTrendingHashtags] = useState([]);
    const [showTopicsTray, setShowTopicsTray] = useState(false);
    const [showPeopleTray, setShowPeopleTray] = useState(false);
    const [peopleSearch, setPeopleSearch] = useState('');
    const [brokenPersonAvatars, setBrokenPersonAvatars] = useState({});
    const [pendingLivePosts, setPendingLivePosts] = useState([]);
    const [highlightedPostNo, setHighlightedPostNo] = useState(null);

    const [lightboxState, setLightboxState] = useState({ isOpen: false, images: [], index: 0, postNo: null });
    const [videoState, setVideoState] = useState({ isOpen: false, videos: [], index: 0 });
    const [viewersState, setViewersState] = useState({ isOpen: false, type: 'post', targetNo: null, initialCount: 0 });
    const [sharePortalPost, setSharePortalPost] = useState(null);

    const shellRef = useRef(null);
    const sentinelRef = useRef(null);
    const hydratedPreviewPostNosRef = useRef(new Set());
    const viewedPostNosRef = useRef(new Set());
    const viewedCommentNosRef = useRef(new Set());
    const prefetchedPagesRef = useRef(new Map());
    const inflightPrefetchRef = useRef(new Map());
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
                    allow_sharing: detail.allow_sharing !== undefined ? detail.allow_sharing : p.allow_sharing,
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

    const buildFeedPageUrl = useCallback(({ nextPage = 1, append = false, cursor = null, filter = streamFilter, tag = hashtagFilter, personId = personFilter?.id || '' } = {}) => {
        if (tag) {
            return `/api/hashtags/stream?tag=${encodeURIComponent(tag)}`;
        }
        const limit = 15;
        const offset = (Math.max(1, nextPage) - 1) * limit;
        const params = new URLSearchParams({
            limit: String(limit),
            page: String(nextPage),
        });
        if (append && cursor) {
            params.set('cursor', String(cursor));
        } else {
            params.set('offset', String(offset));
        }
        if (filter === 'tagged') params.set('filter', 'tagged');
        if (personId) params.set('person_id', String(personId));
        return `/post/getAllPostCommentByFamCode?${params.toString()}`;
    }, [streamFilter, hashtagFilter, personFilter]);

    const fetchAndNormalizeSlice = useCallback(async (url) => {
        if (prefetchedPagesRef.current.has(url)) {
            const cached = prefetchedPagesRef.current.get(url);
            prefetchedPagesRef.current.delete(url);
            return cached;
        }
        if (inflightPrefetchRef.current.has(url)) {
            return inflightPrefetchRef.current.get(url);
        }
        const promise = apiFetch(url, { method: 'GET', timeoutMs: 60000 }, bootstrap)
            .then((res) => {
                const { items, pagination } = unwrapFeedResponse(res);
                const normalized = items.map(normalizePost).filter(Boolean);
                return { normalized, pagination };
            })
            .finally(() => {
                inflightPrefetchRef.current.delete(url);
            });
        inflightPrefetchRef.current.set(url, promise);
        return promise;
    }, [bootstrap]);

    const fetchFeed = useCallback(async ({ nextPage = 1, append = false, cursor = null, background = false, filter = streamFilter, tag = hashtagFilter, personId = personFilter?.id || '', attempt = 0 } = {}) => {
        const url = buildFeedPageUrl({ nextPage, append, cursor, filter, tag, personId });

        // Fast-path: if next cursor slice was already pre-fetched into memory during idle/touch,
        // commit synchronously without flashing loadingMore spinner or waiting on 4G/5G RTT.
        if (append && prefetchedPagesRef.current.has(url)) {
            const { normalized, pagination } = prefetchedPagesRef.current.get(url);
            prefetchedPagesRef.current.delete(url);
            setPosts((prev) => {
                const seen = new Set(prev.map((p) => String(p.post_no)));
                const extra = normalized.filter((p) => !seen.has(String(p.post_no)));
                return extra.length > 0 ? [...prev, ...extra] : prev;
            });
            setPage(nextPage);
            setHasMore(Boolean(pagination?.has_more));
            setNextCursor(pagination?.next_cursor || null);
            return;
        }

        if (append) {
            setLoadingMore(true);
        } else if (!background) {
            prefetchedPagesRef.current.clear();
            setLoading(true);
            setErrorMessage('');
        }

        try {
            const { normalized, pagination } = await fetchAndNormalizeSlice(url);

            setPosts((prev) => {
                if (!append) return normalized;
                const seen = new Set(prev.map((p) => String(p.post_no)));
                const extra = normalized.filter((p) => !seen.has(String(p.post_no)));
                return [...prev, ...extra];
            });
            setPage(nextPage);
            const resolvedHasMore = Boolean(pagination?.has_more);
            const resolvedCursor = pagination?.next_cursor || null;
            setHasMore(resolvedHasMore);
            setNextCursor(resolvedCursor);

            if (!append && !tag && !personId && filter === 'all') {
                writeSwrFeedCache(bootstrap, {
                    posts: normalized,
                    hasMore: resolvedHasMore,
                    nextCursor: resolvedCursor,
                });
            }
        } catch (err) {
            if (!append && attempt === 0 && err?.status === 408) {
                return fetchFeed({ nextPage, append, cursor, background, filter, tag, personId, attempt: 1 });
            }
            if (!background) {
                setErrorMessage(err?.message || 'Unable to load your family feed right now.');
            }
        } finally {
            setLoading(false);
            setLoadingMore(false);
        }
    }, [bootstrap, buildFeedPageUrl, fetchAndNormalizeSlice, streamFilter, hashtagFilter, personFilter]);

    // Proactive Next-Cursor Lookahead Prefetch: warms Page N+1 into memory on idle or first scroll/touch
    // so mobile thumb-scrolling appends the next batch in 0ms with zero network stall.
    useEffect(() => {
        if (typeof window === 'undefined' || loading || loadingMore || !hasMore || !nextCursor || hashtagFilter) {
            return undefined;
        }
        // Skip background lookahead inside synthetic unit-test environments lacking IntersectionObserver/matchMedia
        if (typeof IntersectionObserver === 'undefined' || typeof window.matchMedia !== 'function') {
            return undefined;
        }
        const nextUrl = buildFeedPageUrl({
            nextPage: page + 1,
            append: true,
            cursor: nextCursor,
            filter: streamFilter,
            tag: '',
            personId: personFilter?.id || '',
        });
        if (prefetchedPagesRef.current.has(nextUrl) || inflightPrefetchRef.current.has(nextUrl)) {
            return undefined;
        }

        let cancelled = false;
        let idleHandle = null;
        let timerHandle = null;

        const triggerPrefetch = () => {
            if (cancelled || prefetchedPagesRef.current.has(nextUrl) || inflightPrefetchRef.current.has(nextUrl)) return;
            fetchAndNormalizeSlice(nextUrl)
                .then((slice) => {
                    if (!cancelled && slice) {
                        if (prefetchedPagesRef.current.size >= 4) {
                            const oldest = prefetchedPagesRef.current.keys().next().value;
                            prefetchedPagesRef.current.delete(oldest);
                        }
                        prefetchedPagesRef.current.set(nextUrl, slice);
                    }
                })
                .catch(() => {});
        };

        // Trigger immediately on first user touch/scroll gesture OR quietly during browser idle window
        const onFirstScrollGesture = () => {
            window.removeEventListener('touchstart', onFirstScrollGesture);
            window.removeEventListener('scroll', onFirstScrollGesture);
            triggerPrefetch();
        };
        window.addEventListener('touchstart', onFirstScrollGesture, { once: true, passive: true });
        window.addEventListener('scroll', onFirstScrollGesture, { once: true, passive: true });

        if (typeof window.requestIdleCallback === 'function') {
            idleHandle = window.requestIdleCallback(triggerPrefetch, { timeout: 1400 });
        } else {
            timerHandle = setTimeout(triggerPrefetch, 750);
        }

        return () => {
            cancelled = true;
            window.removeEventListener('touchstart', onFirstScrollGesture);
            window.removeEventListener('scroll', onFirstScrollGesture);
            if (idleHandle !== null && typeof window.cancelIdleCallback === 'function') {
                window.cancelIdleCallback(idleHandle);
            }
            if (timerHandle !== null) clearTimeout(timerHandle);
        };
    }, [hasMore, nextCursor, page, loading, loadingMore, hashtagFilter, streamFilter, personFilter, buildFeedPageUrl, fetchAndNormalizeSlice]);

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
        if (Array.isArray(bootstrap.initialPosts)) {
            writeSwrFeedCache(bootstrap, {
                posts: bootstrap.initialPosts,
                hasMore: Boolean(bootstrap.initialHasMore),
                nextCursor: bootstrap.initialNextCursor || null,
            });
        } else {
            fetchFeed({ nextPage: 1, append: false, background: Boolean(swrSeed) });
        }
        loadTrendingHashtags();
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    // Allow non-Preact islands/cards (e.g. Weekly Fun Facts approval) to refresh the feed without reloading the page
    useEffect(() => {
        const onFeedRefresh = () => {
            fetchFeed({ nextPage: 1, append: false });
        };
        window.addEventListener('fp:feed-refresh', onFeedRefresh);
        return () => window.removeEventListener('fp:feed-refresh', onFeedRefresh);
    }, [fetchFeed]);

    // Lazy OpenGraph / Google Photos album hydration for posts without pre-hydrated link_preview
    useEffect(() => {
        if (loading || posts.length === 0) return;

        posts.forEach((post) => {
            const pKey = String(post?.post_no || '');
            if (!pKey || hydratedPreviewPostNosRef.current.has(pKey)) return;
            if (post.link_preview || (Array.isArray(post.videos) && post.videos.length > 0)) return;
            const rawMsg = String(post.postMessage || '');
            if (!rawMsg) return;

            const allMatches = extractPreviewableUrls(rawMsg, 6);
            const candidateUrls = allMatches.filter((u) => {
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

    // Deep-link post scrolling & highlighting (initial load + live notification clicks)
    useEffect(() => {
        if (loading) return;
        let cancelled = false;
        let fetchTimer = null;

        const runDeepLinkScroll = () => {
            const targetNo = getDeepLinkPostNo();
            if (!targetNo) return;

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
                        fetchTimer = setTimeout(scrollAndPulse, 60);
                    }
                })
                .catch(() => {});
        };

        runDeepLinkScroll();
        window.addEventListener('feed:scroll-to-target', runDeepLinkScroll);
        window.addEventListener('hashchange', runDeepLinkScroll);

        return () => {
            cancelled = true;
            if (fetchTimer) clearTimeout(fetchTimer);
            window.removeEventListener('feed:scroll-to-target', runDeepLinkScroll);
            window.removeEventListener('hashchange', runDeepLinkScroll);
        };
    }, [loading]); // eslint-disable-line react-hooks/exhaustive-deps

    // Infinite scroll sentinel (1400px ahead-of-viewport prefetch window to avoid mid-scroll stalls)
    useEffect(() => {
        if (typeof IntersectionObserver === 'undefined' || !sentinelRef.current) return;
        const obs = new IntersectionObserver((entries) => {
            if (entries[0]?.isIntersecting && hasMore && !loading && !loadingMore && !hashtagFilter) {
                fetchFeed({ nextPage: page + 1, append: true, cursor: nextCursor });
            }
        }, { rootMargin: '1400px 0px' });
        obs.observe(sentinelRef.current);
        return () => obs.disconnect();
    }, [hasMore, loading, loadingMore, page, nextCursor, hashtagFilter, fetchFeed]);

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
                            allow_sharing: data.allow_sharing !== undefined ? data.allow_sharing : p.allow_sharing,
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
        setPersonFilter(null);
        setShowPeopleTray(false);
        setStreamFilter(nextFilter);
        fetchFeed({ nextPage: 1, append: false, filter: nextFilter, tag: '', personId: '' });
    };

    const handleSelectHashtag = useCallback((tag) => {
        const clean = String(tag || '').replace(/^#/, '').trim();
        if (!clean) return;
        setPersonFilter(null);
        setShowPeopleTray(false);
        setHashtagFilter(clean);
        fetchFeed({ nextPage: 1, append: false, filter: streamFilter, tag: clean, personId: '' });
    }, [fetchFeed, streamFilter]);

    const handleClearHashtag = () => {
        setHashtagFilter('');
        fetchFeed({ nextPage: 1, append: false, filter: streamFilter, tag: '', personId: personFilter?.id || '' });
        loadTrendingHashtags();
    };

    const handleSelectPerson = useCallback((person) => {
        if (!person || !person.id) return;
        if (personFilter && String(personFilter.id) === String(person.id)) {
            setPersonFilter(null);
            setShowPeopleTray(false);
            setPeopleSearch('');
            fetchFeed({ nextPage: 1, append: false, filter: streamFilter, tag: '', personId: '' });
            return;
        }
        setHashtagFilter('');
        setShowTopicsTray(false);
        setShowPeopleTray(false);
        setPeopleSearch('');
        setPersonFilter(person);
        fetchFeed({ nextPage: 1, append: false, filter: streamFilter, tag: '', personId: String(person.id) });
    }, [fetchFeed, personFilter, streamFilter]);

    const handleClearPerson = useCallback(() => {
        setPersonFilter(null);
        setPeopleSearch('');
        fetchFeed({ nextPage: 1, append: false, filter: streamFilter, tag: hashtagFilter, personId: '' });
    }, [fetchFeed, streamFilter, hashtagFilter]);

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

    const handleOpenLightbox = useCallback((images, index, p, startAnimated = false) => {
        setLightboxState({ isOpen: true, images, index, postNo: p?.post_no ?? null, startAnimated: Boolean(startAnimated) });
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

    const handleOpenSharePortal = useCallback((post) => {
        if (!post?.post_no) return;
        setSharePortalPost(post);
    }, []);

    // Record post & comment views when cards/comments scroll into view (idle-debounced so active scrolling stays 60fps)
    useEffect(() => {
        if (loading || typeof IntersectionObserver === 'undefined') return undefined;

        const pendingPostViews = new Set();
        const pendingCommentViews = new Set();
        let flushTimer = null;

        const scheduleIdleFlush = () => {
            if (flushTimer !== null) return;
            flushTimer = setTimeout(() => {
                flushTimer = null;
                const postBatch = Array.from(pendingPostViews);
                const commentBatch = Array.from(pendingCommentViews);
                pendingPostViews.clear();
                pendingCommentViews.clear();

                postBatch.forEach((postNo) => {
                    apiFetch('/api/posts/view', { method: 'POST', body: { post_no: Number(postNo) || postNo } }, bootstrap)
                        .then((res) => {
                            if (typeof res?.views_count === 'number') {
                                setPosts((prev) => {
                                    const targetIdx = prev.findIndex((p) => String(p.post_no) === String(postNo));
                                    if (targetIdx === -1) return prev;
                                    const cur = prev[targetIdx];
                                    if (Number(cur.views_count ?? cur.view_count ?? 0) === res.views_count) return prev;
                                    const next = prev.slice();
                                    next[targetIdx] = { ...cur, views_count: res.views_count, view_count: res.views_count };
                                    return next;
                                });
                            }
                        })
                        .catch(() => {});
                });

                commentBatch.forEach((commentNo) => {
                    apiFetch('/api/comments/view', { method: 'POST', body: { comment_no: Number(commentNo) || commentNo } }, bootstrap)
                        .then((res) => {
                            if (typeof res?.views_count === 'number') {
                                setPosts((prev) => {
                                    const pIdx = prev.findIndex((p) => Array.isArray(p.comments) && p.comments.some((c) => String(c.comment_no) === String(commentNo)));
                                    if (pIdx === -1) return prev;
                                    const parentPost = prev[pIdx];
                                    const cIdx = parentPost.comments.findIndex((c) => String(c.comment_no) === String(commentNo));
                                    if (cIdx === -1) return prev;
                                    const curComment = parentPost.comments[cIdx];
                                    if (Number(curComment.views_count ?? curComment.view_count ?? 0) === res.views_count) return prev;
                                    const nextComments = parentPost.comments.slice();
                                    nextComments[cIdx] = { ...curComment, views_count: res.views_count, view_count: res.views_count };
                                    const next = prev.slice();
                                    next[pIdx] = { ...parentPost, comments: nextComments };
                                    return next;
                                });
                            }
                        })
                        .catch(() => {});
                });
            }, 350);
        };

        const postObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const postNo = entry.target.getAttribute('data-post-no');
                postObserver.unobserve(entry.target);
                if (!postNo || viewedPostNosRef.current.has(postNo)) return;
                viewedPostNosRef.current.add(postNo);
                pendingPostViews.add(postNo);
            });
            if (pendingPostViews.size > 0) scheduleIdleFlush();
        }, { threshold: 0.35 });

        const commentObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const commentNo = entry.target.getAttribute('data-comment-no');
                commentObserver.unobserve(entry.target);
                if (!commentNo || String(commentNo).startsWith('temp_') || viewedCommentNosRef.current.has(commentNo)) return;
                viewedCommentNosRef.current.add(commentNo);
                pendingCommentViews.add(commentNo);
            });
            if (pendingCommentViews.size > 0) scheduleIdleFlush();
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
            if (flushTimer !== null) clearTimeout(flushTimer);
            postObserver.disconnect();
            commentObserver.disconnect();
        };
    }, [loading, posts.length, bootstrap]);

    const currentUserId = String(profileBootstrap?.userId || '');
    const taggedPostsCount = useMemo(
        () => posts.filter((p) => Array.isArray(p.post_tags) && p.post_tags.some((t) => String(t.user_id || t.id) === currentUserId)).length,
        [posts, currentUserId]
    );

    const availablePeople = useMemo(() => {
        const map = new Map();
        const addPerson = (rawId, rawName, rawImg) => {
            const id = String(rawId || '').trim();
            if (!id) return;
            const name = String(rawName || '').trim() || 'Family Member';
            const trimmedImg = String(rawImg || '').trim();
            const img = trimmedImg ? getAvatarUrl({ img: trimmedImg }) : '';
            const existing = map.get(id);
            if (!existing) {
                map.set(id, { id, name, img, count: 0 });
            } else {
                if ((!existing.name || existing.name === 'Family Member') && name !== 'Family Member') {
                    existing.name = name;
                }
                if (!existing.img && img) {
                    existing.img = img;
                }
            }
        };

        if (Array.isArray(profileBootstrap?.familyRoster)) {
            profileBootstrap.familyRoster.forEach((m) => {
                const full = `${m?.firstName || ''} ${m?.lastName || ''}`.trim() || m?.fullName || '';
                addPerson(m?.id, full, m?.img || m?.profilePics || '');
            });
        }

        if (currentUserId) {
            const selfFull = `${profileBootstrap?.firstName || ''} ${profileBootstrap?.lastName || ''}`.trim();
            addPerson(currentUserId, selfFull, profileBootstrap?.avatar || '');
        }

        posts.forEach((p) => {
            const seenInPost = new Set();
            const authorId = String(p?.id || '').trim();
            if (authorId) {
                const authorName = p.fullName || `${p.firstName || ''} ${p.lastName || ''}`.trim();
                addPerson(authorId, authorName, p.profileImg || p.profilePics || '');
                seenInPost.add(authorId);
            }
            if (Array.isArray(p?.post_tags)) {
                p.post_tags.forEach((t) => {
                    const tagId = String(t?.user_id || t?.id || '').trim();
                    if (!tagId) return;
                    const tagName = t.fullName || `${t.firstName || ''} ${t.lastName || ''}`.trim();
                    addPerson(tagId, tagName, t.img || t.profilePics || '');
                    seenInPost.add(tagId);
                });
            }
            seenInPost.forEach((pid) => {
                const entry = map.get(pid);
                if (entry) entry.count += 1;
            });
        });

        return Array.from(map.values()).sort((a, b) => {
            if (b.count !== a.count) return b.count - a.count;
            return a.name.localeCompare(b.name);
        });
    }, [profileBootstrap?.familyRoster, profileBootstrap?.firstName, profileBootstrap?.lastName, profileBootstrap?.avatar, currentUserId, posts]);

    const filteredPeople = useMemo(() => {
        const q = peopleSearch.trim().toLowerCase();
        if (!q) return availablePeople;
        return availablePeople.filter((p) => p.name.toLowerCase().includes(q));
    }, [availablePeople, peopleSearch]);

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
            openSharePortal: (post) => {
                handleOpenSharePortal(post);
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
            {/* What's New Banner (Preact-native, hydrated from resources/data/whats_new.php) */}
            <WhatsNewBanner whatsNew={bootstrap?.whatsNew} />

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
                            <span className="fw-bold small" style={{ fontSize: '0.9rem', color: '#0f172a' }}>
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
            {streamFilter === 'tagged' && !hashtagFilter && !personFilter && (
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
                            <span className="fw-bold small" style={{ fontSize: '0.9rem', color: '#0f172a' }}>
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

            {/* Active Person Filter Stream Banner */}
            {personFilter && !hashtagFilter && (
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
                                className="badge rounded-pill text-white px-3 py-1 shadow-xs d-inline-flex align-items-center gap-1"
                                style={{ background: '#a31621', fontSize: '0.88rem', letterSpacing: '0.3px', maxWidth: '240px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}
                                title={personFilter.name}
                            >
                                <i className="bi bi-person-fill" aria-hidden="true" />
                                <span>{personFilter.name}</span>
                            </span>
                            <span className="fw-bold small" style={{ fontSize: '0.9rem', color: '#0f172a' }}>
                                Memories by or featuring {personFilter.name}
                            </span>
                        </div>
                        <button
                            type="button"
                            className="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1"
                            onClick={handleClearPerson}
                            style={{ fontSize: '0.82rem', fontWeight: 600, minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                        >
                            <i className="bi bi-x-circle-fill" aria-hidden="true" /> Show All Posts
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
                        aria-selected={streamFilter === 'all' && !hashtagFilter && !personFilter}
                        className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold transition-all ${streamFilter === 'all' && !hashtagFilter && !personFilter ? 'btn-primary text-white shadow-xs' : 'text-muted border-0 bg-transparent'}`}
                        onClick={() => handleStreamChange('all')}
                        style={{ fontSize: '0.82rem', whiteSpace: 'nowrap', flexShrink: 0, minHeight: '44px' }}
                    >
                        <i className="bi bi-clock-history me-1" aria-hidden="true" />
                        All Family Memories
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={streamFilter === 'tagged' && !hashtagFilter && !personFilter}
                        className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold transition-all d-inline-flex align-items-center gap-1 ${streamFilter === 'tagged' && !hashtagFilter && !personFilter ? 'btn-primary text-white shadow-xs' : 'text-muted border-0 bg-transparent'}`}
                        onClick={() => handleStreamChange('tagged')}
                        style={{ fontSize: '0.82rem', whiteSpace: 'nowrap', flexShrink: 0, minHeight: '44px' }}
                    >
                        <i className="bi bi-tag-fill me-1" aria-hidden="true" />
                        <span>Tagged With You</span>
                        <span
                            className={`badge rounded-pill ${streamFilter === 'tagged' && !hashtagFilter && !personFilter ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark'}`}
                            style={{ fontSize: '0.68rem' }}
                        >
                            {taggedPostsCount}
                        </span>
                    </button>
                </div>
                <div className="d-flex align-items-center gap-2 ms-auto flex-wrap">
                    {streamFilter === 'tagged' && !personFilter && (
                        <span className="text-muted" style={{ fontSize: '0.875rem' }}>
                            Showing memories featuring you
                        </span>
                    )}
                    {availablePeople.length > 0 && !hashtagFilter && (
                        <button
                            type="button"
                            className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-2 transition-all ${showPeopleTray || personFilter ? 'btn-primary text-white shadow-xs' : 'border shadow-2xs'}`}
                            onClick={() => {
                                setShowTopicsTray(false);
                                setShowPeopleTray((prev) => !prev);
                            }}
                            aria-expanded={showPeopleTray}
                            aria-controls="family-people-tray"
                            title={showPeopleTray ? 'Hide Family People Filter' : 'Filter posts by family member'}
                            style={{
                                fontSize: '0.82rem',
                                whiteSpace: 'nowrap',
                                flexShrink: 0,
                                minHeight: '44px',
                                ...(showPeopleTray || personFilter
                                    ? {}
                                    : {
                                        backgroundColor: 'var(--card-bg, #ffffff)',
                                        color: 'var(--text-main, #0f172a)',
                                    }),
                            }}
                        >
                            <i className="bi bi-people-fill" aria-hidden="true" />
                            <span>{personFilter ? personFilter.name : 'People'}</span>
                            <span
                                className={`badge rounded-pill ${showPeopleTray || personFilter ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25'}`}
                                style={{
                                    fontSize: '0.68rem',
                                    color: (showPeopleTray || personFilter) ? undefined : 'var(--text-main, #0f172a)',
                                }}
                            >
                                {availablePeople.length}
                            </span>
                            <i className={`bi ${showPeopleTray ? 'bi-chevron-up' : 'bi-chevron-down'}`} aria-hidden="true" />
                        </button>
                    )}
                    {trendingHashtags.length > 0 && !hashtagFilter && (
                        <button
                            type="button"
                            className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-2 transition-all ${showTopicsTray ? 'btn-primary text-white shadow-xs' : 'border shadow-2xs'}`}
                            onClick={() => {
                                setShowPeopleTray(false);
                                setShowTopicsTray((prev) => !prev);
                            }}
                            aria-expanded={showTopicsTray}
                            aria-controls="family-topics-tray"
                            title={showTopicsTray ? 'Hide Family Topics & Milestones' : 'Show Family Topics & Milestones'}
                            style={{
                                fontSize: '0.82rem',
                                whiteSpace: 'nowrap',
                                flexShrink: 0,
                                minHeight: '44px',
                                ...(showTopicsTray
                                    ? {}
                                    : {
                                        backgroundColor: 'var(--card-bg, #ffffff)',
                                        color: 'var(--text-main, #0f172a)',
                                    }),
                            }}
                        >
                            <i className="bi bi-tags-fill" aria-hidden="true" />
                            <span>Topics</span>
                            <span
                                className={`badge rounded-pill ${showTopicsTray ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25'}`}
                                style={{
                                    fontSize: '0.68rem',
                                    color: showTopicsTray ? undefined : 'var(--text-main, #0f172a)',
                                }}
                            >
                                {trendingHashtags.length}
                            </span>
                            <i className={`bi ${showTopicsTray ? 'bi-chevron-up' : 'bi-chevron-down'}`} aria-hidden="true" />
                        </button>
                    )}
                </div>
            </div>

            {/* Inline Family People Filter Tray (hidden by default to save vertical space) */}
            {showPeopleTray && availablePeople.length > 0 && !hashtagFilter && (
                <div
                    id="family-people-tray"
                    className="card border-0 shadow-sm mb-4 p-3"
                    style={{ borderRadius: '14px', backgroundColor: 'var(--card-bg, #ffffff)' }}
                >
                    <div className="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
                        <div className="d-flex align-items-center gap-2">
                            <i className="bi bi-people-fill text-primary" style={{ fontSize: '1rem' }} aria-hidden="true" />
                            <span className="fw-bold small" style={{ fontSize: '0.88rem', color: 'var(--text-main, #0f172a)' }}>
                                Filter by Person
                            </span>
                        </div>
                        <div className="d-flex align-items-center gap-2">
                            {personFilter && (
                                <button
                                    type="button"
                                    className="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                    onClick={() => {
                                        setShowPeopleTray(false);
                                        handleClearPerson();
                                    }}
                                    style={{ fontSize: '0.78rem', minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                >
                                    Everyone
                                </button>
                            )}
                            <button
                                type="button"
                                className="btn btn-sm btn-link text-muted text-decoration-none p-0 px-2"
                                onClick={() => setShowPeopleTray(false)}
                                aria-label="Hide Filter by Person"
                                style={{ fontSize: '0.82rem', minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                            >
                                Hide
                            </button>
                        </div>
                    </div>
                    {availablePeople.length > 6 && (
                        <div className="mb-2">
                            <input
                                type="search"
                                className="form-control form-control-sm rounded-pill px-3"
                                placeholder="Find a family member…"
                                aria-label="Find a family member"
                                value={peopleSearch}
                                onInput={(e) => setPeopleSearch(e.currentTarget.value)}
                                style={{ minHeight: '44px', fontSize: '0.875rem' }}
                            />
                        </div>
                    )}
                    <div className="d-flex gap-2 flex-wrap align-items-center">
                        {filteredPeople.length === 0 ? (
                            <span className="text-muted small py-2">No family members match &ldquo;{peopleSearch}&rdquo;.</span>
                        ) : (
                            filteredPeople.map((person) => {
                                const isActive = personFilter && String(personFilter.id) === String(person.id);
                                const initials = person.name
                                    .split(/\s+/)
                                    .filter(Boolean)
                                    .slice(0, 2)
                                    .map((part) => part[0]?.toUpperCase() || '')
                                    .join('') || '?';
                                return (
                                    <button
                                        key={person.id}
                                        type="button"
                                        className={`btn btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2 shadow-2xs transition-all ${isActive ? 'btn-primary text-white' : 'border'}`}
                                        style={{
                                            minHeight: '44px',
                                            whiteSpace: 'nowrap',
                                            flexShrink: 0,
                                            ...(isActive
                                                ? {}
                                                : {
                                                    backgroundColor: 'var(--card-bg, #ffffff)',
                                                    color: 'var(--text-main, #0f172a)',
                                                }),
                                        }}
                                        onClick={() => handleSelectPerson(person)}
                                        title={`View family memories by or featuring ${person.name}`}
                                        aria-pressed={Boolean(isActive)}
                                    >
                                        {person.img && !brokenPersonAvatars[person.id] ? (
                                            <img
                                                src={person.img}
                                                alt=""
                                                width="24"
                                                height="24"
                                                className="rounded-circle object-fit-cover flex-shrink-0"
                                                style={{ width: '24px', height: '24px' }}
                                                onError={() => setBrokenPersonAvatars((prev) => ({ ...prev, [person.id]: true }))}
                                            />
                                        ) : (
                                            <span
                                                className="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                style={{
                                                    width: '24px',
                                                    height: '24px',
                                                    fontSize: '0.68rem',
                                                    backgroundColor: isActive ? 'rgba(255,255,255,0.22)' : '#e2e8f0',
                                                    color: isActive ? '#ffffff' : '#0f172a',
                                                }}
                                            >
                                                {initials}
                                            </span>
                                        )}
                                        <span
                                            className="fw-semibold"
                                            style={{ maxWidth: '180px', overflow: 'hidden', textOverflow: 'ellipsis' }}
                                        >
                                            {person.name}
                                        </span>
                                        {person.count > 0 && (
                                            <span
                                                className={`badge rounded-pill px-1 py-0 ${isActive ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25'}`}
                                                style={{
                                                    fontSize: '0.68rem',
                                                    color: isActive ? undefined : 'var(--text-main, #0f172a)',
                                                }}
                                            >
                                                {person.count}
                                            </span>
                                        )}
                                    </button>
                                );
                            })
                        )}
                    </div>
                </div>
            )}

            {/* Trending Family Hashtags Carousel (hidden by default to save vertical space) */}
            {showTopicsTray && trendingHashtags.length > 0 && !hashtagFilter && (
                <div
                    id="family-topics-tray"
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
                        <button
                            type="button"
                            className="btn btn-sm btn-link text-muted text-decoration-none p-0 px-2"
                            onClick={() => setShowTopicsTray(false)}
                            aria-label="Hide Family Topics & Milestones"
                            style={{ fontSize: '0.82rem', minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                        >
                            Hide
                        </button>
                    </div>
                    <div className="d-flex gap-2 flex-wrap align-items-center">
                        {trendingHashtags.map((item) => (
                            <button
                                key={item.tag}
                                type="button"
                                className="btn btn-sm family-hashtag-pill d-inline-flex align-items-center gap-1 shadow-xs"
                                style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                onClick={() => {
                                    setShowTopicsTray(false);
                                    handleSelectHashtag(item.tag);
                                }}
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
                            : (personFilter
                                ? `No memories featuring ${personFilter.name} yet`
                                : (streamFilter === 'tagged' ? 'No tagged memories yet' : 'No family posts yet'))}
                    </h5>
                    <p className="text-muted mb-3">
                        {hashtagFilter
                            ? 'Return to the full family stream or share the first memory with this hashtag.'
                            : (personFilter
                                ? `Return to the full family stream or share a memory with ${personFilter.name}.`
                                : (streamFilter === 'tagged'
                                    ? 'When relatives tag you in stories or milestone photos, they will appear in this personal scrapbook.'
                                    : 'Share the first memory or event with your family above!'))}
                    </p>
                    {(hashtagFilter || personFilter) && (
                        <div>
                            <button
                                type="button"
                                className="btn btn-primary rounded-pill px-4 fw-semibold"
                                style={{ minHeight: '44px' }}
                                onClick={hashtagFilter ? handleClearHashtag : handleClearPerson}
                            >
                                Show All Posts
                            </button>
                        </div>
                    )}
                </div>
            )}

            {/* Posts List + Interleaved Live-Playable Games & App Functionality Adverts */}
            {!loading && posts.map((post, postIdx) => {
                // Weave a live interactive game or app-feature advert after the 1st post and every 2 posts thereafter
                const shouldShowInterleavedShowcase = postIdx === 0 || (postIdx + 1) % 2 === 0;
                const showcaseSlot = postIdx === 0 ? 0 : Math.floor((postIdx + 1) / 2);
                const slotCategory = showcaseSlot % 2 === 0 ? 'game' : 'feature';

                return (
                    <div key={post.post_no} data-feed-item-wrapper={post.post_no}>
                        <IslandErrorBoundary
                            name={`PostCard-${post.post_no}`}
                            fallbackTitle="This family memory could not be displayed."
                            compact
                        >
                            <PostCard
                                post={post}
                                bootstrap={profileBootstrap}
                                isFirstPost={postIdx === 0}
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
                                onOpenSharePortal={handleOpenSharePortal}
                            />
                        </IslandErrorBoundary>

                        {shouldShowInterleavedShowcase && (
                            <FeedInteractiveShowcase
                                slotIndex={showcaseSlot % SHOWCASE_ITEMS.length}
                                initialCategory={slotCategory}
                            />
                        )}
                    </div>
                );
            })}

            {/* Evergreen Interactive Showcase Deck when feed has 0 posts so discovery never stops */}
            {!loading && posts.length === 0 && (
                <FeedInteractiveShowcase slotIndex={0} initialCategory="all" />
            )}

            {/* Pagination / Infinite Scroll Sentinel */}
            {!loading && hasMore && !hashtagFilter && (
                <div ref={sentinelRef} className="d-flex justify-content-center py-3">
                    <button
                        type="button"
                        className="btn btn-outline-secondary rounded-pill px-4 fw-semibold"
                        style={{ minHeight: '44px' }}
                        disabled={loadingMore}
                        onClick={() => fetchFeed({ nextPage: page + 1, append: true, cursor: nextCursor })}
                    >
                        {loadingMore ? 'Loading more memories…' : 'Load More Memories'}
                    </button>
                </div>
            )}

            {/* Modals — mounted on demand so closed modals incur zero hook overhead */}
            {lightboxState.isOpen && (
                <LightboxModal
                    isOpen={lightboxState.isOpen}
                    images={lightboxState.images}
                    initialIndex={lightboxState.index}
                    initialAnimate={Boolean(lightboxState.startAnimated)}
                    post={activeLightboxPost}
                    bootstrap={profileBootstrap}
                    onClose={() => setLightboxState({ isOpen: false, images: [], index: 0, postNo: null })}
                    onReactPost={handleReactPost}
                    onMutateComments={handleMutateComments}
                />
            )}

            {videoState.isOpen && (
                <VideoModal
                    isOpen={videoState.isOpen}
                    videos={videoState.videos}
                    initialIndex={videoState.index}
                    onClose={() => setVideoState({ isOpen: false, videos: [], index: 0 })}
                />
            )}

            <LazyViewersModal
                isOpen={viewersState.isOpen}
                type={viewersState.type}
                targetNo={viewersState.targetNo}
                initialCount={viewersState.initialCount}
                bootstrap={profileBootstrap}
                onClose={() => setViewersState({ isOpen: false, type: 'post', targetNo: null, initialCount: 0 })}
            />

            <LazySharePortalModal
                isOpen={Boolean(sharePortalPost)}
                post={sharePortalPost}
                bootstrap={profileBootstrap}
                onUpdatePost={(postNo, patch) => {
                    handleUpdatePost(postNo, patch);
                    setSharePortalPost((prev) => (prev && String(prev.post_no) === String(postNo) ? { ...prev, ...patch } : prev));
                }}
                onClose={() => setSharePortalPost(null)}
            />
        </div>
    );
}
