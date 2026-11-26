import { useState, useEffect, useRef } from 'preact/hooks';
import { isDirectVideoDescriptor, resolveVideoPlaybackSrc } from './feedUtils';
import {
    VideoCaptionOverlay,
    VideoSubtitlesPopover,
    useVideoSubtitles,
} from './VideoSubtitlesControl';
import styles from './FeedShell.module.css';

const SPEEDS = [1, 1.25, 1.5, 2];

export function VideoModal({
    isOpen,
    videos = [],
    initialIndex = 0,
    onClose,
}) {
    const [index, setIndex] = useState(initialIndex);
    const [playing, setPlaying] = useState(false);
    const [muted, setMuted] = useState(false);
    const [speedIdx, setSpeedIdx] = useState(0);
    const [currentTime, setCurrentTime] = useState(0);
    const [duration, setDuration] = useState(0);
    const [ccMenuOpen, setCcMenuOpen] = useState(false);
    const videoRef = useRef(null);

    useEffect(() => {
        setIndex(initialIndex);
        setCurrentTime(0);
        setPlaying(false);
        setCcMenuOpen(false);
    }, [initialIndex, isOpen]);

    useEffect(() => {
        if (!isOpen) return;
        const onKey = (e) => {
            if (e.key === 'Escape') onClose?.();
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [isOpen, onClose]);

    const rawItem = videos[index] || videos[0] || null;
    const currentUrl = rawItem ? resolveVideoPlaybackSrc(rawItem) : '';
    const fallbackOriginal = typeof rawItem === 'object' && rawItem ? (rawItem?.originalUrl || '') : '';
    const posterUrl = typeof rawItem === 'object' && rawItem ? (rawItem?.thumbnailUrl || undefined) : undefined;
    const isDirect = rawItem ? isDirectVideoDescriptor(rawItem) : false;
    const subtitles = useVideoSubtitles(isOpen && isDirect ? currentUrl : null, videoRef);

    if (!isOpen || videos.length === 0) return null;
    const parsedRatio = typeof rawItem?.aspect_ratio === 'string'
        ? rawItem.aspect_ratio.match(/^\s*(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)\s*$/)
        : null;
    const numericRatio = parsedRatio && parseFloat(parsedRatio[2]) > 0
        ? parseFloat(parsedRatio[1]) / parseFloat(parsedRatio[2])
        : null;
    const isFacebookIframe = !isDirect && (
        rawItem?.type === 'facebook'
        || (typeof currentUrl === 'string' && currentUrl.includes('facebook.com/plugins/video.php'))
    );
    const isPortraitIframe = !isDirect && Boolean(
        rawItem?.isShort
        || rawItem?.type === 'tiktok'
        || rawItem?.provider === 'tiktok'
        || (typeof rawItem?.originalUrl === 'string' && (rawItem.originalUrl.includes('/shorts/') || /\/reels?\/\d+/i.test(rawItem.originalUrl)))
        || (typeof currentUrl === 'string' && /\/reels?(?:\/|%2F)\d+/i.test(currentUrl))
        || /(?:^|[\s"'(])#(?:shorts?|reels?|tiktok|afv)\b/i.test(String(rawItem?.title || rawItem?.hook || rawItem?.hookText || ''))
        || (numericRatio !== null && numericRatio < 0.88)
    );
    const useFramedStage = isPortraitIframe || (isFacebookIframe && (numericRatio === null || numericRatio <= 1.15));

    const togglePlay = () => {
        const el = videoRef.current;
        if (!el) return;
        if (el.paused) {
            el.play().catch(() => {});
            setPlaying(true);
        } else {
            el.pause();
            setPlaying(false);
        }
    };

    const toggleMute = () => {
        const el = videoRef.current;
        if (!el) return;
        el.muted = !el.muted;
        setMuted(el.muted);
    };

    const cycleSpeed = () => {
        const nextIdx = (speedIdx + 1) % SPEEDS.length;
        setSpeedIdx(nextIdx);
        if (videoRef.current) {
            videoRef.current.playbackRate = SPEEDS[nextIdx];
        }
    };

    const formatSecs = (sec) => {
        const s = Math.max(0, Math.floor(sec || 0));
        const m = Math.floor(s / 60);
        const r = s % 60;
        return `${m}:${r < 10 ? '0' : ''}${r}`;
    };

    return (
        <div
            id="fullVideoModalRoot"
            className={styles.modalBackdrop}
            role="dialog"
            aria-modal="true"
            aria-label="Video player"
            onClick={(e) => {
                if (e.target === e.currentTarget) onClose?.();
            }}
        >
            <div className={styles.modalDialog}>
                <div className={styles.modalHeader}>
                    {videos.length > 1 ? (
                        <div className="d-flex align-items-center gap-1" style={{ height: '6px' }}>
                            {videos.map((_, idx) => (
                                <span
                                    key={idx}
                                    className="rounded-pill"
                                    style={{
                                        display: 'inline-block',
                                        height: '6px',
                                        width: index === idx ? '22px' : '6px',
                                        background: index === idx ? '#ffffff' : 'rgba(255, 255, 255, 0.4)',
                                        borderRadius: '9999px',
                                        transition: 'all 0.2s ease',
                                        cursor: 'pointer',
                                    }}
                                    onClick={() => setIndex(idx)}
                                />
                            ))}
                        </div>
                    ) : (
                        <span className="fw-bold">
                            Video {index + 1} of {videos.length}
                        </span>
                    )}
                    <button
                        type="button"
                        className={styles.btnBase}
                        onClick={onClose}
                        title="Close video player"
                        aria-label="Close video player"
                    >
                        Close
                    </button>
                </div>

                <div id="fullVideoModalStage" className={`${styles.modalBody} position-relative`}>
                    {isDirect ? (
                        <div className="position-relative w-100">
                            <video
                                ref={videoRef}
                                referrerPolicy="no-referrer"
                                src={currentUrl}
                                poster={posterUrl}
                                controls
                                playsInline
                                muted={muted}
                                style={{ width: '100%', maxHeight: '78vh', objectFit: 'contain', borderRadius: 10, background: '#000' }}
                                onLoadedMetadata={(e) => setDuration(e.currentTarget.duration || 0)}
                                onTimeUpdate={(e) => setCurrentTime(e.currentTarget.currentTime || 0)}
                                onPlay={() => setPlaying(true)}
                                onPause={() => setPlaying(false)}
                                onError={(e) => {
                                    const el = e.currentTarget;
                                    if (!el.dataset.retried && fallbackOriginal && el.src !== fallbackOriginal && fallbackOriginal.startsWith('http')) {
                                        el.dataset.retried = '1';
                                        el.src = fallbackOriginal;
                                        el.load();
                                    }
                                }}
                            />
                            <VideoCaptionOverlay
                                captionsEnabled={subtitles.captionsEnabled}
                                activeCueText={subtitles.activeCueText}
                                captionStyle={subtitles.stylePrefs}
                                loading={subtitles.loading}
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
                                containerStyle={{ top: 'auto', bottom: '16px', right: '16px' }}
                            />
                        </div>
                    ) : (
                        <div
                            className={useFramedStage ? 'w-100 mx-auto' : 'ratio ratio-16x9 w-100'}
                            style={useFramedStage ? {
                                aspectRatio: isPortraitIframe ? '9 / 16' : (rawItem?.aspect_ratio || '4 / 5'),
                                maxWidth: isPortraitIframe ? 'min(420px, 100%)' : 'min(520px, 100%)',
                                maxHeight: '78vh',
                            } : undefined}
                        >
                            <iframe
                                src={currentUrl}
                                title={`Video ${index + 1}`}
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                                allowFullScreen
                                style={useFramedStage ? { width: '100%', height: '100%', border: 'none', borderRadius: 10 } : undefined}
                            />
                        </div>
                    )}

                    <div className="d-flex align-items-center justify-content-center flex-wrap gap-2 w-100">
                        {isDirect && (
                            <>
                                <button type="button" className={styles.btnBase} onClick={togglePlay}>
                                    {playing ? 'Pause' : 'Play'}
                                </button>
                                <button type="button" className={styles.btnBase} onClick={toggleMute}>
                                    {muted ? 'Unmute' : 'Mute'}
                                </button>
                                <button type="button" className={styles.btnBase} onClick={cycleSpeed}>
                                    Speed {SPEEDS[speedIdx]}x
                                </button>
                                <button
                                    type="button"
                                    className={styles.btnBase}
                                    data-testid="modal-video-cc-btn"
                                    onClick={() => setCcMenuOpen((o) => !o)}
                                    aria-expanded={ccMenuOpen ? 'true' : 'false'}
                                    style={subtitles.captionsEnabled ? { background: '#a31621', color: '#ffffff', borderColor: '#ffffff' } : undefined}
                                >
                                    {subtitles.loading ? 'CC…' : (subtitles.captionsEnabled ? 'CC On' : 'CC')}
                                </button>
                                <span style={{ fontSize: '0.85rem', color: '#cbd5e1' }}>
                                    {formatSecs(currentTime)} / {formatSecs(duration)}
                                </span>
                            </>
                        )}
                        {videos.length > 1 && (
                            <>
                                <button
                                    type="button"
                                    className={styles.btnBase}
                                    onClick={() => setIndex((i) => (i - 1 + videos.length) % videos.length)}
                                    title="Previous video"
                                    aria-label="Previous video"
                                >
                                    Prev
                                </button>
                                <button
                                    type="button"
                                    className={styles.btnBase}
                                    onClick={() => setIndex((i) => (i + 1) % videos.length)}
                                    title="Next video"
                                    aria-label="Next video"
                                >
                                    Next
                                </button>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
