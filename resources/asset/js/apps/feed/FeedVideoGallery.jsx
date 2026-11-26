import { useEffect, useRef, useState } from 'preact/hooks';
import { isDirectVideoDescriptor, isVideoPath, resolveVideoPlaybackSrc } from './feedUtils';
import { attachWebGL4kCas } from './AtgWebGL4kRenderer';
import {
    VideoCaptionOverlay,
    VideoSubtitlesButton,
    VideoSubtitlesPopover,
    useVideoSubtitles,
} from './VideoSubtitlesControl';
import {
    activePostId,
    isGlobalMuted,
    registerMediaPlayer,
    requestActivePlayer,
    toggleGlobalMute,
    useSignalValue,
} from './mediaPlaybackStore';

function safeAspectRatio(raw) {
    if (typeof raw !== 'string') return undefined;
    const trimmed = raw.trim();
    return /^\d+(?:\.\d+)?\s*\/\s*\d+(?:\.\d+)?$/.test(trimmed) ? trimmed : undefined;
}

function parseAspectRatioValue(raw) {
    const safe = safeAspectRatio(raw);
    if (!safe) return null;
    const parts = safe.split('/');
    const w = parseFloat(parts[0]);
    const h = parseFloat(parts[1]);
    if (!Number.isFinite(w) || !Number.isFinite(h) || h <= 0) return null;
    return w / h;
}

function IframePosterFacade({ vid, providerLabel, onActivate }) {
    const thumb = vid?.posterUrl || vid?.thumbnailUrl || null;
    return (
        <div
            data-testid="iframe-poster-facade"
            className="w-100 h-100 position-relative d-flex align-items-center justify-content-center overflow-hidden"
            style={{
                background: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
                minHeight: '220px',
            }}
        >
            {thumb && (
                <img
                    src={thumb}
                    alt={vid?.title || vid?.hook || `${providerLabel} video preview`}
                    referrerPolicy="no-referrer"
                    loading="lazy"
                    className="position-absolute top-0 start-0 w-100 h-100"
                    style={{ objectFit: 'cover', opacity: 0.72 }}
                />
            )}
            <span
                className="badge rounded-pill position-absolute"
                style={{
                    top: '12px',
                    left: '12px',
                    zIndex: 2,
                    background: 'rgba(15, 23, 42, 0.82)',
                    color: '#ffffff',
                    fontSize: '0.75rem',
                    padding: '6px 10px',
                }}
            >
                {providerLabel}
            </span>
            <button
                type="button"
                onClick={onActivate}
                aria-label={`Play ${providerLabel} video`}
                className="btn position-relative d-inline-flex align-items-center justify-content-center rounded-circle shadow"
                style={{
                    zIndex: 3,
                    width: '56px',
                    height: '56px',
                    minWidth: '44px',
                    minHeight: '44px',
                    background: 'rgba(163, 22, 33, 0.92)',
                    border: '2px solid rgba(255, 255, 255, 0.85)',
                    color: '#ffffff',
                    cursor: 'pointer',
                    whiteSpace: 'nowrap',
                    flexShrink: 0,
                }}
            >
                <i className="bi bi-play-fill" style={{ fontSize: '1.5rem', marginLeft: '2px' }} aria-hidden="true" />
            </button>
        </div>
    );
}

function SingleDirectVideo({ vid, allVideos, onOpenVideo, playerId }) {
    const videoRef = useRef(null);
    const canvasRef = useRef(null);
    const containerRef = useRef(null);
    const [webglActive, setWebglActive] = useState(true);
    const globalMuted = useSignalValue(isGlobalMuted);
    const currentActiveId = useSignalValue(activePostId);
    const [userActivatedIframe, setUserActivatedIframe] = useState(false);
    const [isPlaying, setIsPlaying] = useState(false);
    const [isBuffering, setIsBuffering] = useState(false);
    const [streamFailed, setStreamFailed] = useState(false);
    const [ccMenuOpen, setCcMenuOpen] = useState(false);
    const [detectedRatio, setDetectedRatio] = useState(() => safeAspectRatio(vid?.aspectRatio || vid?.aspect_ratio));

    const isDirect = isDirectVideoDescriptor(vid);
    const playSrc = resolveVideoPlaybackSrc(vid);
    const subtitles = useVideoSubtitles(isDirect ? playSrc : null, videoRef);
    const fallbackOriginal = typeof vid === 'object' ? (vid?.originalUrl || '') : '';
    const thumbUrl = typeof vid === 'object' ? (vid?.posterUrl || vid?.thumbnailUrl || null) : null;
    const durationSec = typeof vid === 'object' && Number.isFinite(Number(vid?.durationSec ?? vid?.duration_sec))
        ? Math.round(Number(vid.durationSec ?? vid.duration_sec))
        : 0;
    const providerRaw = String(vid?.provider || vid?.type || 'video').toLowerCase();
    const providerLabel = providerRaw === 'youtube' ? 'YouTube'
        : providerRaw === 'facebook' ? 'Facebook'
        : providerRaw === 'vimeo' ? 'Vimeo'
        : providerRaw === 'cloudflare' ? 'Cloudflare Stream'
        : providerRaw === 'tiktok' ? 'TikTok'
        : 'Video';
    const hasObserver = typeof IntersectionObserver !== 'undefined';
    const isPlayerActive = currentActiveId === playerId;
    const isIframeMounted = !hasObserver || isPlayerActive || userActivatedIframe;

    const numericRatio = parseAspectRatioValue(detectedRatio);
    const isShortHint = Boolean(
        vid?.isShort
        || (typeof vid?.originalUrl === 'string' && (vid.originalUrl.includes('/shorts/') || /\/reel\/\d+/i.test(vid.originalUrl)))
        || (typeof vid?.embedUrl === 'string' && /\/reel(?:\/|%2F)\d+/i.test(vid.embedUrl))
    );
    const isFacebookEmbed = !isDirect && (
        vid?.type === 'facebook'
        || (typeof playSrc === 'string' && playSrc.includes('facebook.com/plugins/video.php'))
    );
    const isPortrait = numericRatio !== null ? numericRatio < 0.88 : isShortHint;
    const isLandscape = numericRatio !== null ? numericRatio > 1.15 : !isShortHint;

    const feedAspectRatio = isPortrait
        ? '4 / 5'
        : (isLandscape
            ? (numericRatio !== null && numericRatio <= 1.6 ? detectedRatio : '16 / 10')
            : (detectedRatio || '1 / 1'));
    const feedMaxHeight = isPortrait ? '880px' : (isLandscape ? 'min(680px, 75vh)' : 'min(720px, 78vh)');
    const rawFocalX = vid?.focalX ?? vid?.focal_x;
    const rawFocalY = vid?.focalY ?? vid?.focal_y;
    const focalPos = (typeof rawFocalX === 'number' && typeof rawFocalY === 'number')
        ? `${rawFocalX}% ${rawFocalY}%`
        : (isPortrait ? '50% 28%' : '50% 50%');

    const getTrimBounds = (el) => {
        const maxCap = isLandscape ? 300 : 180;
        const rawStart = (typeof vid?.trimStart === 'number' && vid.trimStart > 0) ? vid.trimStart : 0;
        const rawEnd = (typeof vid?.trimEnd === 'number' && vid.trimEnd > rawStart) ? vid.trimEnd : (rawStart + maxCap);
        const rawDur = (el && el.duration && !Number.isNaN(el.duration) && el.duration > 0) ? el.duration : rawEnd;
        const end = Math.min(rawDur, rawEnd, rawStart + maxCap);
        return { start: rawStart, end };
    };

    useEffect(() => {
        const v = videoRef.current;
        if (v) {
            v.muted = globalMuted;
            v.defaultMuted = globalMuted;
            if (globalMuted) {
                v.setAttribute('muted', '');
            } else {
                v.removeAttribute('muted');
            }
        }
    }, [globalMuted]);

    useEffect(() => {
        if (!isPlayerActive && userActivatedIframe) {
            setUserActivatedIframe(false);
        }
    }, [isPlayerActive, userActivatedIframe]);

    useEffect(() => {
        const video = videoRef.current;
        const container = containerRef.current;
        if (!container) return;

        if (video) {
            video.muted = isGlobalMuted.peek();
            video.defaultMuted = isGlobalMuted.peek();
            video.playsInline = true;
            if (video.muted) {
                video.setAttribute('muted', '');
            }
            video.setAttribute('playsinline', '');
            video.setAttribute('webkit-playsinline', '');

            if (vid?.hlsUrl && window.__FPFeedVideoCoordinator && typeof window.__FPFeedVideoCoordinator.attachHls === 'function') {
                window.__FPFeedVideoCoordinator.attachHls(video, vid.hlsUrl, vid.embedUrl);
            }
        }

        const activatePlayer = () => {
            const v = videoRef.current;
            if (!v || v._userManuallyPaused) return;
            const mutedNow = isGlobalMuted.peek();
            v.muted = mutedNow;
            v.defaultMuted = mutedNow;
            if (mutedNow) v.setAttribute('muted', '');
            v._autoPlaying = true;
            const playPromise = v.play();
            if (playPromise && typeof playPromise.then === 'function') {
                playPromise.then(() => {
                    setIsPlaying(true);
                }).catch(() => {
                    v._autoPlaying = false;
                });
            }
        };

        const deactivatePlayer = () => {
            const v = videoRef.current;
            if (v && !v.paused) {
                v._autoPlaying = false;
                // Pause only — preserve buffered media in <video> element
                v.pause();
                setIsPlaying(false);
            }
        };

        const unregisterStore = registerMediaPlayer(playerId, container, {
            onActivate: activatePlayer,
            onDeactivate: deactivatePlayer,
        });

        let unregisterLegacy = null;
        if (window.__FPFeedVideoCoordinator && typeof window.__FPFeedVideoCoordinator.register === 'function') {
            unregisterLegacy = window.__FPFeedVideoCoordinator.register(container, {
                getVideo: () => videoRef.current,
                play: activatePlayer,
                pause: deactivatePlayer,
            });
        }

        return () => {
            unregisterStore();
            if (typeof unregisterLegacy === 'function') unregisterLegacy();
        };
    }, [playSrc, vid?.hlsUrl, playerId]);

    useEffect(() => {
        const video = videoRef.current;
        const canvas = canvasRef.current;
        if (!isDirect || !video || !canvas || !webglActive) return undefined;
        const detach = attachWebGL4kCas(video, canvas, {
            onFallback: () => setWebglActive(false),
        });
        if (!detach) {
            setWebglActive(false);
            return undefined;
        }
        return () => detach();
    }, [isDirect, playSrc, webglActive]);

    const handleSingleError = (e) => {
        setIsBuffering(false);
        const el = e?.currentTarget;
        if (!el) return;
        if (
            !el.dataset.retried
            && vid?.provider !== 'instagram'
            && vid?.provider !== 'facebook'
            && fallbackOriginal
            && el.src !== fallbackOriginal
            && fallbackOriginal.startsWith('http')
            && isVideoPath(fallbackOriginal)
        ) {
            el.dataset.retried = '1';
            el.src = fallbackOriginal;
            el.load();
            return;
        }
        if (vid?.provider === 'instagram' || vid?.sourceUrl) {
            setStreamFailed(true);
        }
    };

    const togglePlay = () => {
        const el = videoRef.current;
        if (!el) return;
        if (el.paused) {
            el._userManuallyPaused = false;
            if (activePostId.peek() !== playerId) {
                requestActivePlayer(playerId);
            }
            const p = el.play();
            if (p && typeof p.then === 'function') {
                p.then(() => setIsPlaying(true)).catch(() => {});
            } else {
                setIsPlaying(true);
            }
        } else {
            el._userManuallyPaused = true;
            el._autoPlaying = false;
            el.pause();
            setIsPlaying(false);
        }
    };

    const toggleMute = (e) => {
        e.stopPropagation();
        const nextMuted = toggleGlobalMute();
        const el = videoRef.current;
        if (el) {
            el.muted = nextMuted;
        }
    };

    const isSocialProvider = vid?.provider === 'instagram' || vid?.provider === 'facebook';
    const socialSourceUrl = isSocialProvider
        ? (vid?.sourceUrl || (typeof fallbackOriginal === 'string' && (fallbackOriginal.includes('instagram.com') || fallbackOriginal.includes('facebook.com')) ? fallbackOriginal : ''))
        : '';
    const socialPlatformLabel = vid?.provider === 'facebook' ? 'Facebook' : 'Instagram';
    const socialFallbackTestId = vid?.provider === 'facebook' ? 'facebook-video-fallback-card' : 'instagram-video-fallback-card';

    return (
        <div ref={containerRef} className="video-embed-container feed-video-breakout mb-3 position-relative">
            {streamFailed && socialSourceUrl ? (
                <a
                    href={socialSourceUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    data-testid={socialFallbackTestId}
                    className="link-preview-card text-decoration-none rounded-3 overflow-hidden shadow-sm d-block"
                >
                    {thumbUrl && (
                        <div className="link-preview-card__media link-preview-card__media--portrait">
                            <img
                                src={thumbUrl}
                                alt={vid?.sourceTitle || vid?.hook || `${socialPlatformLabel} Reel`}
                                className="w-100 h-100 object-fit-cover"
                                style={{ objectPosition: '50% 24%' }}
                                loading="lazy"
                                referrerPolicy="no-referrer"
                            />
                            <span className="link-preview-card__platform-pill">{socialPlatformLabel}</span>
                            {!String(thumbUrl).includes('stp=cmp1_') && (
                                <span className="link-preview-card__play-badge" aria-hidden="true">
                                    <i className="bi bi-play-fill" />
                                </span>
                            )}
                        </div>
                    )}
                    <div className="p-3 link-preview-card__body d-flex align-items-center justify-content-between gap-2">
                        <div className="min-w-0">
                            <h6 className="fw-bold mb-0 link-preview-card__title">
                                {vid?.sourceTitle || vid?.hook || `${socialPlatformLabel} Reel`}
                            </h6>
                        </div>
                        <span className="badge rounded-pill px-3 py-2 link-preview-card__cta d-inline-flex align-items-center">
                            {`Watch on ${socialPlatformLabel} →`}
                        </span>
                    </div>
                </a>
            ) : isDirect ? (
                <div className="position-relative feed-media-aura-container">
                    {thumbUrl && (
                        <div className="feed-media-aura-backdrop" aria-hidden="true" style={{ zIndex: 1, inset: 0 }}>
                            <img src={thumbUrl} referrerPolicy="no-referrer" alt="" loading="lazy" />
                        </div>
                    )}
                    <div
                        className="video-rendition-frame position-relative overflow-hidden vse-skeleton-surface"
                        style={{
                            backgroundColor: '#0b0f19',
                            width: '100%',
                            maxWidth: '100%',
                            maxHeight: feedMaxHeight,
                            aspectRatio: feedAspectRatio,
                            borderRadius: '12px',
                            zIndex: 2,
                        }}
                    >
                        {socialSourceUrl && durationSec === 0 && (
                            <a
                                href={socialSourceUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                onClick={(e) => e.stopPropagation()}
                                className="position-absolute d-inline-flex align-items-center gap-1.5 text-decoration-none"
                                title={`Open original Reel on ${socialPlatformLabel}`}
                                aria-label={`Open original Reel on ${socialPlatformLabel}`}
                                style={{
                                    top: '10px',
                                    left: '10px',
                                    zIndex: 6,
                                    minHeight: '44px',
                                    minWidth: '44px',
                                    padding: '0 14px',
                                    borderRadius: '9999px',
                                    background: 'rgba(15, 23, 42, 0.82)',
                                    backdropFilter: 'blur(8px)',
                                    border: '1px solid rgba(255, 255, 255, 0.22)',
                                    color: '#ffffff',
                                    fontSize: '0.75rem',
                                    fontWeight: 700,
                                    whiteSpace: 'nowrap',
                                    flexShrink: 0,
                                }}
                            >
                                <i className="bi bi-instagram" aria-hidden="true" />
                                <span>Instagram</span>
                                <i className="bi bi-box-arrow-up-right" style={{ fontSize: '0.68rem', opacity: 0.85 }} aria-hidden="true" />
                            </a>
                        )}
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
                        <VideoSubtitlesButton
                            captionsEnabled={subtitles.captionsEnabled}
                            loading={subtitles.loading}
                            menuOpen={ccMenuOpen}
                            onToggleMenu={() => setCcMenuOpen((o) => !o)}
                            style={{ top: '10px', right: '112px' }}
                        />
                        <VideoSubtitlesPopover
                            menuOpen={ccMenuOpen}
                            onClose={() => setCcMenuOpen(false)}
                            captionsEnabled={subtitles.captionsEnabled}
                            loading={subtitles.loading}
                            error={subtitles.error}
                            onSelectOff={subtitles.disableCaptions}
                            onSelectAutoEn={subtitles.enableAutoCaptions}
                            captionStyle={subtitles.stylePrefs}
                            onUpdateStyle={subtitles.updateStyle}
                        />
                        <VideoCaptionOverlay
                            captionsEnabled={subtitles.captionsEnabled}
                            activeCueText={subtitles.activeCueText}
                            captionStyle={subtitles.stylePrefs}
                            loading={subtitles.loading}
                        />
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
                            title={globalMuted ? 'Unmute video' : 'Mute video'}
                            aria-label={globalMuted ? 'Unmute video' : 'Mute video'}
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
                            <i className={`bi ${globalMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}`} style={{ fontSize: '0.9rem', color: '#ffffff' }} aria-hidden="true" />
                        </button>
                        <video
                            ref={videoRef}
                            referrerPolicy="no-referrer"
                            src={playSrc}
                            poster={thumbUrl || undefined}
                            controls
                            playsInline
                            muted={globalMuted}
                            preload="metadata"
                            onLoadedMetadata={(e) => {
                                const el = e.currentTarget;
                                if (!detectedRatio && el.videoWidth > 0 && el.videoHeight > 0) {
                                    setDetectedRatio(`${(el.videoWidth / el.videoHeight).toFixed(4)} / 1`);
                                }
                                const bounds = getTrimBounds(el);
                                if (bounds.start > 0 && el.currentTime < bounds.start) {
                                    el.currentTime = bounds.start;
                                }
                            }}
                            onTimeUpdate={(e) => {
                                const el = e.currentTarget;
                                const bounds = getTrimBounds(el);
                                if (bounds.end > bounds.start && el.currentTime >= bounds.end) {
                                    el.currentTime = bounds.start;
                                }
                            }}
                            onWaiting={() => setIsBuffering(true)}
                            onCanPlay={() => setIsBuffering(false)}
                            onPlaying={() => {
                                setIsBuffering(false);
                                setIsPlaying(true);
                                if (activePostId.peek() !== playerId) {
                                    requestActivePlayer(playerId);
                                }
                            }}
                            onPause={() => {
                                setIsBuffering(false);
                                setIsPlaying(false);
                            }}
                            onError={handleSingleError}
                            className="w-100 h-100 d-block atg-4k-video-render"
                            style={{ width: '100%', height: '100%', objectFit: 'cover', objectPosition: focalPos, backgroundColor: '#0b0f19', cursor: 'pointer' }}
                        />
                        {webglActive && (
                            <canvas
                                ref={canvasRef}
                                aria-hidden="true"
                                className="position-absolute top-0 start-0 w-100 h-100"
                                style={{
                                    objectFit: 'cover',
                                    objectPosition: focalPos,
                                    pointerEvents: 'none',
                                    zIndex: 2,
                                    clipPath: 'inset(0 0 84px 0)',
                                }}
                            />
                        )}
                    </div>
                </div>
            ) : isFacebookEmbed ? (
                <div
                    className="video-rendition-frame position-relative overflow-hidden d-flex align-items-center justify-content-center"
                    style={{
                        backgroundColor: '#0b0f19',
                        width: '100%',
                        maxWidth: '100%',
                        borderRadius: '12px',
                    }}
                >
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
                    <div
                        className="facebook-embed-stage w-100"
                        style={{
                            maxWidth: (isShortHint || (numericRatio !== null && numericRatio < 0.88))
                                ? 'min(420px, 100%)'
                                : (numericRatio !== null && numericRatio > 1.15 ? '100%' : 'min(520px, 100%)'),
                            aspectRatio: (isShortHint || (numericRatio !== null && numericRatio < 0.88))
                                ? (detectedRatio || '9 / 16')
                                : (detectedRatio || '4 / 5'),
                            maxHeight: (isShortHint || (numericRatio !== null && numericRatio < 0.88))
                                ? 'min(740px, 80vh)'
                                : 'min(680px, 78vh)',
                            margin: '0 auto',
                        }}
                    >
                        {isIframeMounted ? (
                            <iframe
                                src={playSrc}
                                title={vid?.title || 'Family video'}
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowFullScreen
                                loading="lazy"
                                style={{ width: '100%', height: '100%', border: 'none', display: 'block' }}
                            />
                        ) : (
                            <IframePosterFacade
                                vid={vid}
                                providerLabel={providerLabel}
                                onActivate={() => {
                                    setUserActivatedIframe(true);
                                    requestActivePlayer(playerId);
                                }}
                            />
                        )}
                    </div>
                </div>
            ) : (
                <div
                    className="video-rendition-frame position-relative overflow-hidden d-flex align-items-center justify-content-center"
                    style={{
                        backgroundColor: '#0b0f19',
                        width: '100%',
                        maxWidth: '100%',
                        borderRadius: '12px',
                    }}
                >
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
                    <div
                        className="iframe-embed-stage w-100"
                        style={{
                            maxWidth: isPortrait ? 'min(460px, 100%)' : '100%',
                            aspectRatio: isPortrait ? (detectedRatio || '9 / 16') : (detectedRatio || '16 / 9'),
                            maxHeight: isPortrait ? 'min(820px, 84vh)' : 'min(720px, 80vh)',
                            margin: '0 auto',
                        }}
                    >
                        {isIframeMounted ? (
                            <iframe
                                src={playSrc}
                                title={vid?.title || 'Family video'}
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowFullScreen
                                loading="lazy"
                                style={{ width: '100%', height: '100%', border: 'none', display: 'block' }}
                            />
                        ) : (
                            <IframePosterFacade
                                vid={vid}
                                providerLabel={providerLabel}
                                onActivate={() => {
                                    setUserActivatedIframe(true);
                                    requestActivePlayer(playerId);
                                }}
                            />
                        )}
                    </div>
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
    playerId,
}) {
    const videoRef = useRef(null);
    const [hasFrame, setHasFrame] = useState(false);
    const [isPlaying, setIsPlaying] = useState(false);
    const [isBuffering, setIsBuffering] = useState(false);
    const [ccMenuOpen, setCcMenuOpen] = useState(false);
    const globalMuted = useSignalValue(isGlobalMuted);
    const currentActiveId = useSignalValue(activePostId);
    const [userActivatedIframe, setUserActivatedIframe] = useState(false);
    const [progressPct, setProgressPct] = useState(0);
    const isPortraitHint = Boolean(
        vid?.isShort
        || (typeof vid?.originalUrl === 'string' && (vid.originalUrl.includes('/shorts/') || /\/reel\/\d+/i.test(vid.originalUrl)))
        || (parseAspectRatioValue(vid?.aspectRatio || vid?.aspect_ratio) !== null && parseAspectRatioValue(vid?.aspectRatio || vid?.aspect_ratio) < 0.88)
    );
    const [isLandscape, setIsLandscape] = useState(vid?.type && vid.type !== 'direct' && !isPortraitHint && vid?.type !== 'facebook');

    const isDirect = isDirectVideoDescriptor(vid);
    const playSrc = resolveVideoPlaybackSrc(vid);
    const subtitles = useVideoSubtitles(isDirect ? playSrc : null, videoRef);
    const fallbackOriginal = typeof vid === 'object' ? (vid?.originalUrl || '') : '';
    const thumbUrl = typeof vid === 'object' ? (vid?.posterUrl || vid?.thumbnailUrl || null) : null;
    const hasObserver = typeof IntersectionObserver !== 'undefined';
    const isPlayerActive = currentActiveId === playerId;
    const isIframeMounted = !hasObserver || isPlayerActive || userActivatedIframe;
    const providerRaw = String(vid?.provider || vid?.type || 'video').toLowerCase();
    const providerLabel = providerRaw === 'youtube' ? 'YouTube'
        : providerRaw === 'facebook' ? 'Facebook'
        : providerRaw === 'vimeo' ? 'Vimeo'
        : providerRaw === 'cloudflare' ? 'Cloudflare Stream'
        : providerRaw === 'tiktok' ? 'TikTok'
        : 'Video';

    useEffect(() => {
        const v = videoRef.current;
        if (v) {
            v.muted = globalMuted;
            v.defaultMuted = globalMuted;
            if (globalMuted) {
                v.setAttribute('muted', '');
            } else {
                v.removeAttribute('muted');
            }
        }
    }, [globalMuted]);

    useEffect(() => {
        if (!isPlayerActive && userActivatedIframe) {
            setUserActivatedIframe(false);
        }
    }, [isPlayerActive, userActivatedIframe]);

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
            const p = el.play();
            if (p && typeof p.then === 'function') {
                p.then(() => {
                    setIsPlaying(true);
                    setIsBuffering(false);
                }).catch(() => {
                    setIsBuffering(false);
                });
            } else {
                setIsPlaying(true);
                setIsBuffering(false);
            }
        } else {
            el.pause();
            setIsPlaying(false);
            setIsBuffering(false);
        }
    };

    const toggleMute = (e) => {
        e.stopPropagation();
        const nextMuted = toggleGlobalMute();
        const el = videoRef.current;
        if (el) {
            el.muted = nextMuted;
        }
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
                    <VideoSubtitlesButton
                        captionsEnabled={subtitles.captionsEnabled}
                        loading={subtitles.loading}
                        menuOpen={ccMenuOpen}
                        onToggleMenu={() => setCcMenuOpen((o) => !o)}
                        style={{ top: '8px', right: '112px' }}
                    />
                    <VideoSubtitlesPopover
                        menuOpen={ccMenuOpen}
                        onClose={() => setCcMenuOpen(false)}
                        captionsEnabled={subtitles.captionsEnabled}
                        loading={subtitles.loading}
                        error={subtitles.error}
                        onSelectOff={subtitles.disableCaptions}
                        onSelectAutoEn={subtitles.enableAutoCaptions}
                        captionStyle={subtitles.stylePrefs}
                        onUpdateStyle={subtitles.updateStyle}
                    />
                    <button
                        type="button"
                        className="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                        onClick={toggleMute}
                        title={globalMuted ? 'Unmute video' : 'Mute video'}
                        aria-label={globalMuted ? 'Unmute video' : 'Mute video'}
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
                            className={`bi ${globalMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'}`}
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
                            muted={globalMuted}
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
                        <VideoCaptionOverlay
                            captionsEnabled={subtitles.captionsEnabled}
                            activeCueText={subtitles.activeCueText}
                            captionStyle={subtitles.stylePrefs}
                            loading={subtitles.loading}
                        />
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
                    {isIframeMounted ? (
                        <iframe
                            src={playSrc}
                            title={vid?.title || `Family video ${idx + 1}`}
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowFullScreen
                            loading="lazy"
                            style={{ width: '100%', height: '100%', border: 'none' }}
                        />
                    ) : (
                        <IframePosterFacade
                            vid={vid}
                            providerLabel={providerLabel}
                            onActivate={() => {
                                setUserActivatedIframe(true);
                                requestActivePlayer(playerId);
                            }}
                        />
                    )}
                </div>
            )}
        </div>
    );
}

function MultiVideoGallery({ effectiveVideos, playerId, onOpenVideo }) {
    const trackRef = useRef(null);
    const galleryContainerRef = useRef(null);
    const [activeIdx, setActiveIdx] = useState(0);

    useEffect(() => {
        const container = galleryContainerRef.current;
        if (!container) return;

        const activateGallery = () => {
            const cards = trackRef.current?.querySelectorAll('.multi-video-card');
            const v = cards?.[activeIdx]?.querySelector('video');
            if (!v || v._userManuallyPaused) return;
            const mutedNow = isGlobalMuted.peek();
            v.muted = mutedNow;
            v.defaultMuted = mutedNow;
            if (mutedNow) v.setAttribute('muted', '');
            v._autoPlaying = true;
            const p = v.play();
            if (p && typeof p.catch === 'function') {
                p.catch(() => { v._autoPlaying = false; });
            }
        };

        const deactivateGallery = () => {
            const cards = trackRef.current?.querySelectorAll('.multi-video-card');
            cards?.forEach((c) => {
                const v = c.querySelector('video');
                if (v && !v.paused) {
                    v._autoPlaying = false;
                    v.pause();
                }
            });
        };

        const unregisterStore = registerMediaPlayer(playerId, container, {
            onActivate: activateGallery,
            onDeactivate: deactivateGallery,
        });

        let unregister = null;
        if (window.__FPFeedVideoCoordinator && typeof window.__FPFeedVideoCoordinator.register === 'function') {
            unregister = window.__FPFeedVideoCoordinator.register(container, {
                getVideo: () => {
                    const cards = trackRef.current?.querySelectorAll('.multi-video-card');
                    return cards?.[activeIdx]?.querySelector('video') || null;
                },
                play: activateGallery,
                pause: deactivateGallery,
            });
        }
        return () => {
            unregisterStore();
            if (typeof unregister === 'function') unregister();
        };
    }, [activeIdx, playerId]);

    const scrollToCard = (targetIdx) => {
        const bounded = Math.max(0, Math.min(effectiveVideos.length - 1, targetIdx));
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
                    {effectiveVideos.map((_, vIdx) => (
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

            {activeIdx < effectiveVideos.length - 1 && (
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
                {effectiveVideos.map((vid, vIdx) => (
                    <MultiVideoCard
                        key={`${resolveVideoPlaybackSrc(vid)}_${vIdx}`}
                        vid={vid}
                        idx={vIdx}
                        totalCount={effectiveVideos.length}
                        onOpenVideo={onOpenVideo}
                        allVideos={effectiveVideos}
                        trackRef={trackRef}
                        playerId={playerId}
                    />
                ))}
            </div>
        </div>
    );
}

export function FeedVideoGallery({ videos = [], postNo = null, normalizedMedia = null, onOpenVideo }) {
    const mediaVideos = Array.isArray(normalizedMedia)
        ? normalizedMedia.filter((m) => m && (m.renderMode === 'native_video' || m.renderMode === 'inline_iframe'))
        : [];
    const effectiveVideos = Array.isArray(videos) && videos.length > 0 ? videos : mediaVideos;

    if (!Array.isArray(effectiveVideos) || effectiveVideos.length === 0) {
        return null;
    }

    const playerId = postNo ? `post-${postNo}` : `vid-${resolveVideoPlaybackSrc(effectiveVideos[0])}`;

    if (effectiveVideos.length === 1) {
        return <SingleDirectVideo vid={effectiveVideos[0]} allVideos={effectiveVideos} onOpenVideo={onOpenVideo} playerId={playerId} />;
    }

    return <MultiVideoGallery effectiveVideos={effectiveVideos} playerId={playerId} onOpenVideo={onOpenVideo} />;
}
