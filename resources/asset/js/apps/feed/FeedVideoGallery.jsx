import { useEffect, useRef, useState } from 'preact/hooks';
import { isDirectVideoDescriptor, resolveVideoPlaybackSrc } from './feedUtils';

function safeAspectRatio(raw) {
    if (typeof raw !== 'string') return undefined;
    const trimmed = raw.trim();
    return /^\d+(?:\.\d+)?\s*\/\s*\d+(?:\.\d+)?$/.test(trimmed) ? trimmed : undefined;
}

function SingleDirectVideo({ vid, allVideos, onOpenVideo }) {
    const videoRef = useRef(null);
    const containerRef = useRef(null);
    const [isMuted, setIsMuted] = useState(true);
    const [isPlaying, setIsPlaying] = useState(false);
    const [isBuffering, setIsBuffering] = useState(false);
    const [detectedRatio, setDetectedRatio] = useState(() => safeAspectRatio(vid?.aspect_ratio));

    const isDirect = isDirectVideoDescriptor(vid);
    const playSrc = resolveVideoPlaybackSrc(vid);
    const fallbackOriginal = typeof vid === 'object' ? (vid?.originalUrl || '') : '';
    const thumbUrl = typeof vid === 'object' ? (vid?.thumbnailUrl || null) : null;
    const durationSec = typeof vid === 'object' && Number.isFinite(Number(vid?.duration_sec)) ? Math.round(Number(vid.duration_sec)) : 0;

    useEffect(() => {
        const video = videoRef.current;
        const container = containerRef.current;
        if (!video || !container) return;

        video.muted = true;
        video.defaultMuted = true;
        video.setAttribute('muted', '');
        video.setAttribute('playsinline', '');
        video.setAttribute('webkit-playsinline', '');

        if (vid?.hlsUrl && window.__FPFeedVideoCoordinator && typeof window.__FPFeedVideoCoordinator.attachHls === 'function') {
            window.__FPFeedVideoCoordinator.attachHls(video, vid.hlsUrl, vid.embedUrl);
        }

        let unregister = null;
        if (window.__FPFeedVideoCoordinator && typeof window.__FPFeedVideoCoordinator.register === 'function') {
            unregister = window.__FPFeedVideoCoordinator.register(container, {
                getVideo: () => videoRef.current,
                play: () => {
                    const v = videoRef.current;
                    if (!v || v._userManuallyPaused) return;
                    v.muted = true;
                    v.defaultMuted = true;
                    v.setAttribute('muted', '');
                    v._autoPlaying = true;
                    v.play().then(() => {
                        setIsPlaying(true);
                    }).catch(() => {
                        v._autoPlaying = false;
                    });
                },
                pause: () => {
                    const v = videoRef.current;
                    if (v && !v.paused) {
                        v._autoPlaying = false;
                        v.pause();
                        setIsPlaying(false);
                    }
                }
            });
        } else if ('IntersectionObserver' in window) {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    const v = videoRef.current;
                    if (!v) return;
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.3) {
                        if (!v._userManuallyPaused && v.paused) {
                            v.muted = true;
                            v.defaultMuted = true;
                            v.setAttribute('muted', '');
                            v._autoPlaying = true;
                            v.play().then(() => setIsPlaying(true)).catch(() => { v._autoPlaying = false; });
                        }
                    } else if (entry.intersectionRatio < 0.15) {
                        if (!v.paused) {
                            v._autoPlaying = false;
                            v.pause();
                            setIsPlaying(false);
                        }
                    }
                });
            }, { threshold: [0, 0.15, 0.3, 0.6] });
            obs.observe(container);
            unregister = () => obs.disconnect();
        }

        return () => {
            if (typeof unregister === 'function') unregister();
        };
    }, [playSrc, vid?.hlsUrl]);

    const handleSingleError = (e) => {
        const el = e?.currentTarget;
        if (!el) return;
        if (!el.dataset.retried && fallbackOriginal && el.src !== fallbackOriginal && fallbackOriginal.startsWith('http')) {
            el.dataset.retried = '1';
            el.src = fallbackOriginal;
            el.load();
        }
    };

    const togglePlay = () => {
        const el = videoRef.current;
        if (!el) return;
        if (el.paused) {
            el._userManuallyPaused = false;
            el.play().then(() => setIsPlaying(true)).catch(() => {});
        } else {
            el._userManuallyPaused = true;
            el._autoPlaying = false;
            el.pause();
            setIsPlaying(false);
        }
    };

    const toggleMute = (e) => {
        e.stopPropagation();
        const el = videoRef.current;
        if (!el) return;
        el.muted = !el.muted;
        setIsMuted(el.muted);
    };

    return (
        <div ref={containerRef} className="video-embed-container feed-video-breakout mb-3 px-1 position-relative">
            {isDirect ? (
                <div className="position-relative feed-media-aura-container">
                    {thumbUrl && (
                        <div className="feed-media-aura-backdrop" aria-hidden="true" style={{ zIndex: 1, inset: 0 }}>
                            <img src={thumbUrl} referrerPolicy="no-referrer" alt="" loading="lazy" />
                        </div>
                    )}
                    <div
                        className="position-relative overflow-hidden rounded-3 vse-skeleton-surface"
                        style={{
                            backgroundColor: '#0b0f19',
                            maxHeight: '560px',
                            aspectRatio: detectedRatio || undefined,
                            margin: '0 auto',
                            zIndex: 2,
                        }}
                    >
                        {durationSec > 0 && (
                            <span
                                className="badge rounded-pill position-absolute d-inline-flex align-items-center gap-1"
                                style={{
                                    top: '12px',
                                    left: '12px',
                                    zIndex: 6,
                                    background: 'rgba(15, 23, 42, 0.78)',
                                    backdropFilter: 'blur(8px)',
                                    border: '1px solid rgba(255, 255, 255, 0.22)',
                                    color: '#ffffff',
                                    fontSize: '0.72rem',
                                    padding: '5px 10px',
                                }}
                            >
                                <i className="bi bi-stars" style={{ color: '#fde047' }} aria-hidden="true" />
                                <span>{Math.floor(durationSec / 60)}:{String(durationSec % 60).padStart(2, '0')}</span>
                            </span>
                        )}
                        {isBuffering && (
                            <div className="position-absolute top-50 start-50 translate-middle" style={{ zIndex: 5, pointerEvents: 'none' }}>
                                <div className="spinner-border text-light" role="status" style={{ width: '2.25rem', height: '2.25rem', borderWidth: '2.5px' }}>
                                    <span className="visually-hidden">Loading video…</span>
                                </div>
                            </div>
                        )}
                        <button
                            type="button"
                            className="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                            onClick={() => onOpenVideo?.(allVideos, 0)}
                            title="Play in full video mode"
                            aria-label="Play in full video mode"
                            style={{
                                top: '10px',
                                right: '60px',
                                zIndex: 6,
                                width: '44px',
                                height: '44px',
                                minWidth: '44px',
                                minHeight: '44px',
                                borderRadius: '9999px',
                                background: 'rgba(15, 23, 42, 0.78)',
                                backdropFilter: 'blur(8px)',
                                border: '1px solid rgba(255, 255, 255, 0.25)',
                                color: '#ffffff',
                                cursor: 'pointer',
                                whiteSpace: 'nowrap',
                                flexShrink: 0,
                            }}
                        >
                            <i className="bi bi-arrows-fullscreen" style={{ fontSize: '0.9rem' }} aria-hidden="true" />
                        </button>
                        <button
                            type="button"
                            className="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                            onClick={toggleMute}
                            title={isMuted ? 'Unmute video' : 'Mute video'}
                            aria-label={isMuted ? 'Unmute video' : 'Mute video'}
                            style={{
                                top: '10px',
                                right: '10px',
                                zIndex: 6,
                                width: '44px',
                                height: '44px',
                                minWidth: '44px',
                                minHeight: '44px',
                                borderRadius: '9999px',
                                background: 'rgba(15, 23, 42, 0.78)',
                                backdropFilter: 'blur(8px)',
                                border: '1px solid rgba(255, 255, 255, 0.25)',
                                color: '#ffffff',
                                cursor: 'pointer',
                                whiteSpace: 'nowrap',
                                flexShrink: 0,
                            }}
                        >
                            <i className={`bi ${isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}`} style={{ fontSize: '0.9rem', color: '#ffffff' }} aria-hidden="true" />
                        </button>
                        <video
                            ref={videoRef}
                            referrerPolicy="no-referrer"
                            src={playSrc}
                            poster={thumbUrl || undefined}
                            controls
                            playsInline
                            muted={isMuted}
                            preload="metadata"
                            onLoadedMetadata={(e) => {
                                const el = e.currentTarget;
                                if (!detectedRatio && el.videoWidth > 0 && el.videoHeight > 0) {
                                    setDetectedRatio(`${(el.videoWidth / el.videoHeight).toFixed(4)} / 1`);
                                }
                            }}
                            onWaiting={() => setIsBuffering(true)}
                            onCanPlay={() => setIsBuffering(false)}
                            onPlaying={() => {
                                setIsBuffering(false);
                                setIsPlaying(true);
                            }}
                            onPause={() => {
                                setIsBuffering(false);
                                setIsPlaying(false);
                            }}
                            onError={handleSingleError}
                            onClick={togglePlay}
                            className="w-100 h-100 d-block atg-4k-video-render"
                            style={{ maxHeight: '560px', objectFit: 'contain', backgroundColor: '#0b0f19', cursor: 'pointer' }}
                        />
                    </div>
                </div>
            ) : (
                <div className="position-relative rounded-3 overflow-hidden" style={{ backgroundColor: '#0b0f19' }}>
                    <button
                        type="button"
                        className="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                        onClick={() => onOpenVideo?.(allVideos, 0)}
                        title="Play in full video mode"
                        aria-label="Play in full video mode"
                        style={{
                            top: '10px',
                            right: '10px',
                            zIndex: 6,
                            width: '44px',
                            height: '44px',
                            minWidth: '44px',
                            minHeight: '44px',
                            borderRadius: '9999px',
                            background: 'rgba(15, 23, 42, 0.78)',
                            backdropFilter: 'blur(8px)',
                            border: '1px solid rgba(255, 255, 255, 0.25)',
                            color: '#ffffff',
                            cursor: 'pointer',
                            whiteSpace: 'nowrap',
                            flexShrink: 0,
                        }}
                    >
                        <i className="bi bi-arrows-fullscreen" style={{ fontSize: '0.9rem' }} aria-hidden="true" />
                    </button>
                    <iframe
                        src={playSrc}
                        title={vid?.title || 'Family video'}
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowFullScreen
                        loading="lazy"
                        style={{ width: '100%', height: '360px', border: 'none' }}
                    />
                </div>
            )}
        </div>
    );
}

function MultiVideoCard({
    vid,
    idx,
    totalCount,
    onOpenVideo,
    allVideos,
    trackRef,
}) {
    const videoRef = useRef(null);
    const [hasFrame, setHasFrame] = useState(false);
    const [isPlaying, setIsPlaying] = useState(false);
    const [isBuffering, setIsBuffering] = useState(false);
    const [isMuted, setIsMuted] = useState(true);
    const [progressPct, setProgressPct] = useState(0);
    const [isLandscape, setIsLandscape] = useState(vid?.type && vid.type !== 'direct');

    const isDirect = isDirectVideoDescriptor(vid);
    const playSrc = resolveVideoPlaybackSrc(vid);
    const fallbackOriginal = typeof vid === 'object' ? (vid?.originalUrl || '') : '';
    const thumbUrl = typeof vid === 'object' ? (vid?.thumbnailUrl || null) : null;

    const cardWidth = isLandscape
        ? 'clamp(300px, 82%, 520px)'
        : (totalCount === 2 ? 'clamp(240px, 48.5%, 360px)' : 'clamp(210px, 44%, 300px)');

    const handleVideoError = (e) => {
        setIsBuffering(false);
        const el = e?.currentTarget;
        if (!el) return;
        if (!el.dataset.retried && fallbackOriginal && el.src !== fallbackOriginal && fallbackOriginal.startsWith('http')) {
            el.dataset.retried = '1';
            el.src = fallbackOriginal;
            el.load();
        }
    };

    const handleDetectDimensions = (el) => {
        if (!el || !el.videoWidth || !el.videoHeight) return;
        setIsLandscape(el.videoWidth / el.videoHeight > 1.15);
    };

    const togglePlay = () => {
        const el = videoRef.current;
        if (!el) return;
        if (el.paused) {
            if (trackRef?.current) {
                trackRef.current.querySelectorAll('video').forEach((other) => {
                    if (other !== el && !other.paused) other.pause();
                });
            }
            el.play().then(() => {
                setIsPlaying(true);
                setIsBuffering(false);
            }).catch(() => {
                setIsBuffering(false);
            });
        } else {
            el.pause();
            setIsPlaying(false);
            setIsBuffering(false);
        }
    };

    const toggleMute = (e) => {
        e.stopPropagation();
        const el = videoRef.current;
        if (!el) return;
        el.muted = !el.muted;
        setIsMuted(el.muted);
    };

    const handleTimeUpdate = () => {
        const el = videoRef.current;
        if (!el || !el.duration) return;
        setProgressPct(Math.min(100, Math.max(0, (el.currentTime / el.duration) * 100)));
    };

    return (
        <div
            className="multi-video-card flex-shrink-0 position-relative rounded-3 overflow-hidden shadow-sm border border-light-subtle d-flex flex-column h-100"
            style={{
                scrollSnapAlign: 'start',
                flex: '0 0 auto',
                width: cardWidth,
                height: '100%',
                background: '#0b0f19',
                transition: 'width 0.25s cubic-bezier(0.16, 1, 0.3, 1)',
            }}
        >
            {/* Full Video Mode Button Top-Right */}
            <button
                type="button"
                className="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                onClick={(e) => {
                    e.stopPropagation();
                    onOpenVideo?.(allVideos, idx);
                }}
                title="Play in full video mode"
                aria-label="Play in full video mode"
                style={{
                    top: '8px',
                    right: isDirect ? '60px' : '8px',
                    zIndex: 6,
                    width: '44px',
                    height: '44px',
                    minWidth: '44px',
                    minHeight: '44px',
                    borderRadius: '9999px',
                    background: 'rgba(15, 23, 42, 0.78)',
                    backdropFilter: 'blur(8px)',
                    border: '1px solid rgba(255, 255, 255, 0.22)',
                    color: '#ffffff',
                    cursor: 'pointer',
                    whiteSpace: 'nowrap',
                    flexShrink: 0,
                }}
            >
                <i className="bi bi-arrows-fullscreen" style={{ fontSize: '0.85rem', color: '#ffffff' }} aria-hidden="true" />
            </button>

            {isDirect ? (
                <>
                    <button
                        type="button"
                        className="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                        onClick={toggleMute}
                        title={isMuted ? 'Unmute video' : 'Mute video'}
                        aria-label={isMuted ? 'Unmute video' : 'Mute video'}
                        style={{
                            top: '8px',
                            right: '8px',
                            zIndex: 6,
                            width: '44px',
                            height: '44px',
                            minWidth: '44px',
                            minHeight: '44px',
                            borderRadius: '9999px',
                            background: 'rgba(15, 23, 42, 0.78)',
                            backdropFilter: 'blur(8px)',
                            border: '1px solid rgba(255, 255, 255, 0.22)',
                            color: '#ffffff',
                            cursor: 'pointer',
                            whiteSpace: 'nowrap',
                            flexShrink: 0,
                        }}
                    >
                        <i
                            className={`bi ${isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}`}
                            style={{ fontSize: '0.9rem', color: '#ffffff' }}
                            aria-hidden="true"
                        />
                    </button>

                    <div className="position-relative w-100 h-100 flex-grow-1 overflow-hidden" style={{ minHeight: '320px', background: '#0b0f19' }}>
                        {!hasFrame && thumbUrl && (
                            <img
                                src={thumbUrl}
                                referrerPolicy="no-referrer"
                                className="position-absolute top-0 start-0 w-100 h-100"
                                style={{
                                    zIndex: 3,
                                    objectFit: 'contain',
                                    width: '100%',
                                    height: '100%',
                                    position: 'absolute',
                                    inset: 0,
                                    pointerEvents: 'none',
                                }}
                                loading="lazy"
                                alt={`Video ${idx + 1} preview`}
                                onError={(e) => { e.currentTarget.style.display = 'none'; }}
                            />
                        )}

                        <video
                            ref={videoRef}
                            referrerPolicy="no-referrer"
                            className="w-100 h-100 position-absolute top-0 start-0 atg-4k-video-render"
                            src={playSrc}
                            poster={thumbUrl || undefined}
                            playsInline
                            muted={isMuted}
                            preload="metadata"
                            onLoadedMetadata={(e) => handleDetectDimensions(e.currentTarget)}
                            onLoadedData={(e) => {
                                setHasFrame(true);
                                setIsBuffering(false);
                                handleDetectDimensions(e.currentTarget);
                            }}
                            onPlaying={() => {
                                setHasFrame(true);
                                setIsPlaying(true);
                                setIsBuffering(false);
                            }}
                            onPause={() => {
                                setIsPlaying(false);
                                setIsBuffering(false);
                            }}
                            onWaiting={() => setIsBuffering(true)}
                            onCanPlay={() => setIsBuffering(false)}
                            onTimeUpdate={handleTimeUpdate}
                            onError={handleVideoError}
                            onClick={(e) => {
                                e.stopPropagation();
                                togglePlay();
                            }}
                            onDblClick={(e) => {
                                e.stopPropagation();
                                onOpenVideo?.(allVideos, idx);
                            }}
                            style={{
                                objectFit: 'contain',
                                width: '100%',
                                height: '100%',
                                position: 'absolute',
                                inset: 0,
                                zIndex: 2,
                                cursor: 'pointer',
                                borderRadius: '12px',
                                background: '#0b0f19',
                            }}
                        />

                        {/* Subtle Play Indicator when paused and poster is shown */}
                        {!isPlaying && !isBuffering && (
                            <div
                                className="position-absolute top-50 start-50 translate-middle d-inline-flex align-items-center justify-content-center rounded-circle"
                                style={{
                                    zIndex: 4,
                                    width: '52px',
                                    height: '52px',
                                    background: 'rgba(15, 23, 42, 0.65)',
                                    color: '#ffffff',
                                    pointerEvents: 'none',
                                    border: '1px solid rgba(255, 255, 255, 0.35)',
                                }}
                            >
                                <i className="bi bi-play-fill" style={{ fontSize: '1.5rem', marginLeft: '2px' }} aria-hidden="true" />
                            </div>
                        )}

                        {isBuffering && (
                            <div className="position-absolute top-50 start-50 translate-middle" style={{ zIndex: 5, pointerEvents: 'none' }}>
                                <div className="spinner-border text-light" role="status" style={{ width: '2rem', height: '2rem', borderWidth: '2.5px' }}>
                                    <span className="visually-hidden">Loading video…</span>
                                </div>
                            </div>
                        )}

                        <div className="position-absolute bottom-0 start-0 w-100" style={{ height: '4px', background: 'rgba(255, 255, 255, 0.2)', zIndex: 5 }}>
                            <div className="h-100" style={{ width: `${progressPct}%`, background: '#ffffff', transition: 'width 0.1s linear' }} />
                        </div>
                    </div>
                </>
            ) : (
                <div className="w-100 h-100 position-relative flex-grow-1" style={{ background: '#0b0f19' }}>
                    <iframe
                        src={playSrc}
                        title={vid?.title || `Family video ${idx + 1}`}
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowFullScreen
                        loading="lazy"
                        style={{ width: '100%', height: '100%', border: 'none' }}
                    />
                </div>
            )}
        </div>
    );
}

export function FeedVideoGallery({ videos = [], onOpenVideo }) {
    const trackRef = useRef(null);
    const [activeIdx, setActiveIdx] = useState(0);

    if (!Array.isArray(videos) || videos.length === 0) {
        return null;
    }

    if (videos.length === 1) {
        return <SingleDirectVideo vid={videos[0]} allVideos={videos} onOpenVideo={onOpenVideo} />;
    }

    const galleryContainerRef = useRef(null);

    useEffect(() => {
        const container = galleryContainerRef.current;
        if (!container) return;

        let unregister = null;
        if (window.__FPFeedVideoCoordinator && typeof window.__FPFeedVideoCoordinator.register === 'function') {
            unregister = window.__FPFeedVideoCoordinator.register(container, {
                getVideo: () => {
                    const cards = trackRef.current?.querySelectorAll('.multi-video-card');
                    return cards?.[activeIdx]?.querySelector('video') || null;
                },
                play: () => {
                    const cards = trackRef.current?.querySelectorAll('.multi-video-card');
                    const v = cards?.[activeIdx]?.querySelector('video');
                    if (!v || v._userManuallyPaused) return;
                    v.muted = true;
                    v.defaultMuted = true;
                    v.setAttribute('muted', '');
                    v._autoPlaying = true;
                    v.play().catch(() => { v._autoPlaying = false; });
                },
                pause: () => {
                    const cards = trackRef.current?.querySelectorAll('.multi-video-card');
                    cards?.forEach(c => {
                        const v = c.querySelector('video');
                        if (v && !v.paused) {
                            v._autoPlaying = false;
                            v.pause();
                        }
                    });
                }
            });
        }
        return () => {
            if (typeof unregister === 'function') unregister();
        };
    }, [activeIdx]);

    const scrollToCard = (targetIdx) => {
        const bounded = Math.max(0, Math.min(videos.length - 1, targetIdx));
        setActiveIdx(bounded);
        const track = trackRef.current;
        if (!track) return;
        const card = track.children[bounded];
        if (card && typeof card.scrollIntoView === 'function') {
            card.scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
        }
    };

    const handleTrackScroll = () => {
        const track = trackRef.current;
        if (!track || !track.children.length) return;
        const left = track.scrollLeft;
        let closest = 0;
        let minDist = Infinity;
        Array.from(track.children).forEach((child, i) => {
            const dist = Math.abs(child.offsetLeft - left);
            if (dist < minDist) {
                minDist = dist;
                closest = i;
            }
        });
        setActiveIdx(closest);
    };

    return (
        <div ref={galleryContainerRef} className="video-embed-container multi-video-horizontal-container position-relative mb-3 px-1">
            {/* Subtle Pagination Dots */}
            <div className="d-flex align-items-center justify-content-end mb-1 px-1">
                <div className="d-flex align-items-center gap-1">
                    {videos.map((_, vIdx) => (
                        <span
                            key={`dot-${vIdx}`}
                            onClick={() => scrollToCard(vIdx)}
                            className="rounded-pill"
                            style={activeIdx === vIdx
                                ? { width: '18px', height: '6px', background: '#a31621', cursor: 'pointer', display: 'inline-block' }
                                : { width: '6px', height: '6px', background: 'rgba(15, 23, 42, 0.25)', cursor: 'pointer', display: 'inline-block' }}
                        />
                    ))}
                </div>
            </div>

            {activeIdx > 0 && (
                <button
                    type="button"
                    className="btn position-absolute start-0 top-50 translate-middle-y shadow d-inline-flex align-items-center justify-content-center p-0 rounded-circle"
                    style={{
                        zIndex: 20,
                        width: '44px',
                        height: '44px',
                        minWidth: '44px',
                        minHeight: '44px',
                        background: 'rgba(15, 23, 42, 0.82)',
                        backdropFilter: 'blur(8px)',
                        color: '#ffffff',
                        border: '1px solid rgba(255, 255, 255, 0.25)',
                        marginLeft: '6px',
                    }}
                    onClick={() => scrollToCard(activeIdx - 1)}
                    aria-label="Previous video"
                >
                    <i className="bi bi-chevron-left" style={{ fontSize: '1.1rem' }} aria-hidden="true" />
                </button>
            )}

            {activeIdx < videos.length - 1 && (
                <button
                    type="button"
                    className="btn position-absolute end-0 top-50 translate-middle-y shadow d-inline-flex align-items-center justify-content-center p-0 rounded-circle"
                    style={{
                        zIndex: 20,
                        width: '44px',
                        height: '44px',
                        minWidth: '44px',
                        minHeight: '44px',
                        background: 'rgba(15, 23, 42, 0.82)',
                        backdropFilter: 'blur(8px)',
                        color: '#ffffff',
                        border: '1px solid rgba(255, 255, 255, 0.25)',
                        marginRight: '6px',
                    }}
                    onClick={() => scrollToCard(activeIdx + 1)}
                    aria-label="Next video"
                >
                    <i className="bi bi-chevron-right" style={{ fontSize: '1.1rem' }} aria-hidden="true" />
                </button>
            )}

            <div
                ref={trackRef}
                className="multi-video-scroll-track d-flex gap-2 overflow-x-auto pb-2"
                onScroll={handleTrackScroll}
                style={{
                    scrollSnapType: 'x mandatory',
                    scrollbarWidth: 'thin',
                    WebkitOverflowScrolling: 'touch',
                    scrollPadding: '0 4px',
                    height: 'clamp(340px, 50vh, 460px)',
                    maxWidth: '100%',
                }}
            >
                {videos.map((vid, vIdx) => (
                    <MultiVideoCard
                        key={`${resolveVideoPlaybackSrc(vid)}_${vIdx}`}
                        vid={vid}
                        idx={vIdx}
                        totalCount={videos.length}
                        onOpenVideo={onOpenVideo}
                        allVideos={videos}
                        trackRef={trackRef}
                    />
                ))}
            </div>
        </div>
    );
}
