import { useEffect, useRef, useState } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import {
    captionStyle as captionStyleSignal,
    getCachedSubtitles,
    isCaptionsEnabled,
    setCachedSubtitles,
    setCaptionsEnabled,
    updateCaptionStyle,
    useSignalValue,
} from './mediaPlaybackStore';

const SIZE_MAP = {
    small: '0.8rem',
    medium: '0.95rem',
    large: '1.15rem',
};

const BG_MAP = {
    dark: 'rgba(15, 23, 42, 0.9)',
    translucent: 'rgba(15, 23, 42, 0.6)',
    none: 'transparent',
};

/**
 * Hook managing WebVTT subtitle fetching, caching, and active cue synchronisation.
 */
export function useVideoSubtitles(playSrc, videoRef, enabledOverride = null) {
    const globalCaptionsEnabled = useSignalValue(isCaptionsEnabled);
    const stylePrefs = useSignalValue(captionStyleSignal);
    const captionsEnabled = enabledOverride !== null ? enabledOverride : globalCaptionsEnabled;

    const [cues, setCues] = useState(() => {
        const cached = getCachedSubtitles(playSrc);
        return cached?.cues || [];
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [activeCueText, setActiveCueText] = useState('');
    const inFlightRef = useRef(null);

    const fetchSubtitles = async () => {
        if (!playSrc) return;
        const cached = getCachedSubtitles(playSrc);
        if (cached?.cues?.length) {
            setCues(cached.cues);
            setError(null);
            return;
        }
        if (inFlightRef.current === playSrc) return;
        inFlightRef.current = playSrc;
        setLoading(true);
        setError(null);
        try {
            const data = await apiFetch('/api/video/subtitles', {
                method: 'POST',
                body: { src: playSrc, lang: 'en-GB' },
                timeoutMs: 30000,
            });
            const nextCues = Array.isArray(data?.cues) ? data.cues : [];
            setCachedSubtitles(playSrc, { cues: nextCues, vtt: data?.vtt || '' });
            setCues(nextCues);
        } catch (err) {
            setError(err?.message || 'Unable to load captions.');
        } finally {
            inFlightRef.current = null;
            setLoading(false);
        }
    };

    useEffect(() => {
        const cached = getCachedSubtitles(playSrc);
        if (cached?.cues) {
            setCues(cached.cues);
        } else {
            setCues([]);
        }
        setActiveCueText('');
        setError(null);
    }, [playSrc]);

    useEffect(() => {
        if (captionsEnabled && playSrc && cues.length === 0 && !loading && !error) {
            fetchSubtitles();
        }
    }, [captionsEnabled, playSrc]);

    useEffect(() => {
        const video = videoRef?.current;
        if (!video || !captionsEnabled || cues.length === 0) {
            setActiveCueText('');
            return undefined;
        }

        const syncCue = () => {
            const t = Number(video.currentTime || 0);
            const match = cues.find((c) => t >= c.start && t <= c.end);
            setActiveCueText(match ? match.text : '');
        };

        syncCue();
        video.addEventListener('timeupdate', syncCue);
        video.addEventListener('seeked', syncCue);
        return () => {
            video.removeEventListener('timeupdate', syncCue);
            video.removeEventListener('seeked', syncCue);
        };
    }, [videoRef, captionsEnabled, cues]);

    return {
        captionsEnabled,
        stylePrefs,
        cues,
        loading,
        error,
        activeCueText,
        fetchSubtitles,
        enableAutoCaptions: () => {
            setCaptionsEnabled(true);
            fetchSubtitles();
        },
        disableCaptions: () => {
            setCaptionsEnabled(false);
        },
        updateStyle: updateCaptionStyle,
    };
}

/**
 * Floating CC toggle button for video stages (44x44px minimum touch target).
 */
export function VideoSubtitlesButton({
    captionsEnabled,
    loading,
    menuOpen,
    onToggleMenu,
    style = {},
}) {
    return (
        <button
            type="button"
            className="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
            data-testid="video-cc-btn"
            onClick={(e) => {
                e.stopPropagation();
                onToggleMenu?.();
            }}
            title={captionsEnabled ? 'Subtitles: On (English)' : 'Subtitles / Closed Captions'}
            aria-label={captionsEnabled ? 'Subtitles on, open subtitle menu' : 'Open subtitles menu'}
            aria-expanded={menuOpen ? 'true' : 'false'}
            style={{
                zIndex: 7,
                width: '44px',
                height: '44px',
                minWidth: '44px',
                minHeight: '44px',
                borderRadius: '9999px',
                background: captionsEnabled ? 'rgba(163, 22, 33, 0.92)' : 'rgba(15, 23, 42, 0.78)',
                backdropFilter: 'blur(8px)',
                border: captionsEnabled
                    ? '1px solid rgba(255, 255, 255, 0.65)'
                    : '1px solid rgba(255, 255, 255, 0.25)',
                color: '#ffffff',
                cursor: 'pointer',
                whiteSpace: 'nowrap',
                flexShrink: 0,
                ...style,
            }}
        >
            {loading ? (
                <span className="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true" />
            ) : (
                <i
                    className={`bi ${captionsEnabled ? 'bi-badge-cc-fill' : 'bi-badge-cc'}`}
                    style={{ fontSize: '1.05rem', color: '#ffffff' }}
                    aria-hidden="true"
                />
            )}
        </button>
    );
}

/**
 * Facebook-style dark-glass Subtitles popover menu (`Off`, `English (Auto-generated)`, `⚙ Options`).
 */
export function VideoSubtitlesPopover({
    menuOpen,
    onClose,
    captionsEnabled,
    loading,
    error,
    onSelectOff,
    onSelectAutoEn,
    captionStyle,
    onUpdateStyle,
    containerStyle = {},
}) {
    const [showOptions, setShowOptions] = useState(false);
    const popoverRef = useRef(null);

    useEffect(() => {
        if (!menuOpen) {
            setShowOptions(false);
            return undefined;
        }
        const onDocDown = (e) => {
            if (popoverRef.current && !popoverRef.current.contains(e.target)) {
                onClose?.();
            }
        };
        const onKey = (e) => {
            if (e.key === 'Escape') onClose?.();
        };
        document.addEventListener('pointerdown', onDocDown);
        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('pointerdown', onDocDown);
            document.removeEventListener('keydown', onKey);
        };
    }, [menuOpen, onClose]);

    if (!menuOpen) return null;

    return (
        <div
            ref={popoverRef}
            data-testid="video-cc-popover"
            role="dialog"
            aria-label="Subtitles menu"
            onClick={(e) => e.stopPropagation()}
            style={{
                position: 'absolute',
                top: '62px',
                right: '10px',
                zIndex: 20,
                width: 'min(272px, calc(100% - 20px))',
                background: 'rgba(17, 24, 39, 0.95)',
                backdropFilter: 'blur(14px)',
                border: '1px solid rgba(255, 255, 255, 0.16)',
                borderRadius: '12px',
                padding: '12px',
                color: '#ffffff',
                boxShadow: '0 16px 36px rgba(0, 0, 0, 0.45)',
                ...containerStyle,
            }}
        >
            <div className="d-flex align-items-center justify-content-between gap-2 pb-2 mb-2" style={{ borderBottom: '1px solid rgba(255, 255, 255, 0.12)' }}>
                {showOptions ? (
                    <button
                        type="button"
                        className="btn btn-sm d-inline-flex align-items-center gap-1 text-white p-1"
                        onClick={() => setShowOptions(false)}
                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0, background: 'transparent', border: 'none' }}
                    >
                        <i className="bi bi-arrow-left" aria-hidden="true" />
                        <span className="fw-semibold">Subtitles</span>
                    </button>
                ) : (
                    <span className="fw-bold" style={{ fontSize: '0.95rem', color: '#ffffff' }}>
                        Subtitles
                    </span>
                )}
                <button
                    type="button"
                    data-testid="video-cc-options-btn"
                    className="btn btn-sm d-inline-flex align-items-center gap-1 text-white"
                    onClick={() => setShowOptions((v) => !v)}
                    aria-label="Subtitle options"
                    style={{
                        minHeight: '44px',
                        padding: '0 10px',
                        borderRadius: '8px',
                        background: showOptions ? 'rgba(255, 255, 255, 0.16)' : 'transparent',
                        border: 'none',
                        color: '#ffffff',
                        fontSize: '0.85rem',
                        whiteSpace: 'nowrap',
                        flexShrink: 0,
                    }}
                >
                    <i className="bi bi-gear-fill" aria-hidden="true" />
                    <span>Options</span>
                </button>
            </div>

            {!showOptions ? (
                <div className="d-flex flex-column gap-2">
                    <button
                        type="button"
                        data-testid="video-cc-off"
                        className="btn w-100 d-flex align-items-center gap-2 text-start text-white"
                        onClick={() => {
                            onSelectOff?.();
                            onClose?.();
                        }}
                        style={{
                            minHeight: '44px',
                            padding: '8px 10px',
                            borderRadius: '8px',
                            background: !captionsEnabled ? 'rgba(255, 255, 255, 0.12)' : 'transparent',
                            border: 'none',
                            color: '#ffffff',
                            fontSize: '0.9rem',
                            whiteSpace: 'nowrap',
                            flexShrink: 0,
                        }}
                    >
                        <span style={{ width: '18px', display: 'inline-flex', justifyContent: 'center' }} aria-hidden="true">
                            {!captionsEnabled && <i className="bi bi-check-lg" />}
                        </span>
                        <span>Off</span>
                    </button>

                    <button
                        type="button"
                        data-testid="video-cc-auto-en"
                        className="btn w-100 d-flex align-items-center gap-2 text-start text-white"
                        onClick={() => {
                            onSelectAutoEn?.();
                            onClose?.();
                        }}
                        style={{
                            minHeight: '44px',
                            padding: '8px 10px',
                            borderRadius: '8px',
                            background: captionsEnabled ? 'rgba(255, 255, 255, 0.12)' : 'transparent',
                            border: 'none',
                            color: '#ffffff',
                            fontSize: '0.9rem',
                            whiteSpace: 'nowrap',
                            flexShrink: 0,
                        }}
                    >
                        <span style={{ width: '18px', display: 'inline-flex', justifyContent: 'center' }} aria-hidden="true">
                            {loading ? (
                                <span className="spinner-border spinner-border-sm text-light" />
                            ) : captionsEnabled ? (
                                <i className="bi bi-check-lg" />
                            ) : null}
                        </span>
                        <span>English (Auto-generated)</span>
                    </button>

                    {error && (
                        <div className="small px-2 py-1 rounded" style={{ color: '#fecaca', background: 'rgba(185, 28, 28, 0.25)' }}>
                            {error}
                        </div>
                    )}
                </div>
            ) : (
                <div className="d-flex flex-column gap-3" data-testid="video-cc-options-panel">
                    <div>
                        <div className="small fw-semibold mb-1" style={{ color: '#cbd5e1' }}>Text size</div>
                        <div className="d-flex gap-2">
                            {['small', 'medium', 'large'].map((sz) => (
                                <button
                                    key={sz}
                                    type="button"
                                    className="btn btn-sm flex-fill text-capitalize"
                                    onClick={() => onUpdateStyle?.({ size: sz })}
                                    style={{
                                        minHeight: '44px',
                                        borderRadius: '8px',
                                        background: captionStyle.size === sz ? '#a31621' : 'rgba(255, 255, 255, 0.1)',
                                        color: '#ffffff',
                                        border: '1px solid rgba(255, 255, 255, 0.16)',
                                        whiteSpace: 'nowrap',
                                        flexShrink: 0,
                                    }}
                                >
                                    {sz}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div>
                        <div className="small fw-semibold mb-1" style={{ color: '#cbd5e1' }}>Background</div>
                        <div className="d-flex gap-2">
                            {['dark', 'translucent', 'none'].map((bg) => (
                                <button
                                    key={bg}
                                    type="button"
                                    className="btn btn-sm flex-fill text-capitalize"
                                    onClick={() => onUpdateStyle?.({ background: bg })}
                                    style={{
                                        minHeight: '44px',
                                        borderRadius: '8px',
                                        background: captionStyle.background === bg ? '#a31621' : 'rgba(255, 255, 255, 0.1)',
                                        color: '#ffffff',
                                        border: '1px solid rgba(255, 255, 255, 0.16)',
                                        whiteSpace: 'nowrap',
                                        flexShrink: 0,
                                    }}
                                >
                                    {bg}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div>
                        <div className="small fw-semibold mb-1" style={{ color: '#cbd5e1' }}>Position</div>
                        <div className="d-flex gap-2">
                            {['bottom', 'top'].map((pos) => (
                                <button
                                    key={pos}
                                    type="button"
                                    className="btn btn-sm flex-fill text-capitalize"
                                    onClick={() => onUpdateStyle?.({ position: pos })}
                                    style={{
                                        minHeight: '44px',
                                        borderRadius: '8px',
                                        background: captionStyle.position === pos ? '#a31621' : 'rgba(255, 255, 255, 0.1)',
                                        color: '#ffffff',
                                        border: '1px solid rgba(255, 255, 255, 0.16)',
                                        whiteSpace: 'nowrap',
                                        flexShrink: 0,
                                    }}
                                >
                                    {pos}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

/**
 * High-contrast WCAG AA live caption overlay synced to video playback.
 */
export function VideoCaptionOverlay({ captionsEnabled, activeCueText, captionStyle, loading = false }) {
    if (!captionsEnabled) return null;
    const displayText = loading && !activeCueText ? 'Generating captions…' : activeCueText;
    if (!displayText) return null;

    const isTop = captionStyle?.position === 'top';
    const fontSize = SIZE_MAP[captionStyle?.size] || SIZE_MAP.medium;
    const background = BG_MAP[captionStyle?.background] || BG_MAP.dark;

    return (
        <div
            data-testid="video-cc-overlay"
            aria-live="polite"
            className="position-absolute start-50 translate-middle-x text-center px-3"
            style={{
                ...(isTop ? { top: '62px' } : { bottom: '56px' }),
                zIndex: 6,
                width: '92%',
                maxWidth: '580px',
                pointerEvents: 'none',
            }}
        >
            <span
                style={{
                    display: 'inline-block',
                    padding: '6px 12px',
                    borderRadius: '8px',
                    background,
                    color: '#ffffff',
                    fontSize,
                    fontWeight: 600,
                    lineHeight: 1.38,
                    textShadow: '0 1px 3px rgba(0, 0, 0, 0.9)',
                    boxDecorationBreak: 'clone',
                }}
            >
                {displayText}
            </span>
        </div>
    );
}
