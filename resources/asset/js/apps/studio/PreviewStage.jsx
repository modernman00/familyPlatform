import { useEffect, useRef } from 'preact/hooks';
import styles from './StudioShell.module.css';

const AtgUniversalPosterEngine = typeof globalThis !== 'undefined'
    ? globalThis.AtgUniversalPosterEngine
    : null;

const ASPECT_DIMENSIONS = {
    '9:16': { width: 360, height: 640, css: '9 / 16' },
    '1:1': { width: 480, height: 480, css: '1 / 1' },
    '16:9': { width: 640, height: 360, css: '16 / 9' },
};

const FILTER_MAP = {
    none: 'none',
    'warm-heritage': 'sepia(0.18) saturate(1.18) contrast(1.04)',
    'sepia-archive': 'sepia(0.75) contrast(1.08)',
    'cinematic-noir': 'grayscale(1) contrast(1.22)',
    'crisp-daylight': 'brightness(1.06) contrast(1.08) saturate(1.12)',
};

export function PreviewStage({
    mode,
    aspectRatio,
    onAspectRatioChange,
    isPlaying,
    onTogglePlay,
    playheadSec,
    totalDurationSec,
    activeClip,
    filterPreset,
    captionText,
    familyCode,
    canvasRef: externalCanvasRef,
}) {
    const internalCanvasRef = useRef(null);
    const posterEngineRef = useRef(null);
    const canvasRef = externalCanvasRef || internalCanvasRef;
    const dims = ASPECT_DIMENSIONS[aspectRatio] || ASPECT_DIMENSIONS['9:16'];

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas || typeof canvas.getContext !== 'function') return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const headline = activeClip ? activeClip.name : `Family Circle #${familyCode || 'HERITAGE'}`;

        if (mode === 'poster' && typeof AtgUniversalPosterEngine === 'function') {
            try {
                const ratioKey = aspectRatio === '1:1' ? 'feed' : 'story';
                if (!posterEngineRef.current) {
                    posterEngineRef.current = new AtgUniversalPosterEngine({
                        canvas,
                        aspectRatio: ratioKey,
                        theme: 'family_heirloom_gold',
                        strictContent: true,
                        data: {
                            badge: `FAMILY CIRCLE #${familyCode || 'HERITAGE'}`,
                            title: headline.slice(0, 28),
                            subtitle: captionText || 'Sovereign Family Heritage Poster',
                        },
                    });
                } else {
                    posterEngineRef.current.canvas = canvas;
                    posterEngineRef.current.ctx = ctx;
                    posterEngineRef.current.setAspectRatio(ratioKey);
                    posterEngineRef.current.setData({
                        strictContent: true,
                        badge: `FAMILY CIRCLE #${familyCode || 'HERITAGE'}`,
                        title: headline.slice(0, 28),
                        subtitle: captionText || 'Sovereign Family Heritage Poster',
                    });
                }
                if (typeof posterEngineRef.current.render === 'function') {
                    posterEngineRef.current.render();
                    return;
                }
            } catch {
                // Fall back to standard 2D frame below
            }
        }

        ctx.save();
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
        grad.addColorStop(0, '#140408');
        grad.addColorStop(0.55, '#2b0912');
        grad.addColorStop(1, '#0b0f19');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.strokeStyle = 'rgba(255, 215, 0, 0.35)';
        ctx.lineWidth = 2;
        ctx.strokeRect(12, 12, canvas.width - 24, canvas.height - 24);

        ctx.fillStyle = '#ffd700';
        ctx.font = 'bold 14px Montserrat, sans-serif';
        ctx.textAlign = 'center';
        const modeLabel = mode === 'poster' ? 'POSTER STUDIO' : mode === 'reels' ? 'FAMILY REELS' : 'LIVING ARCHIVES NLE';
        ctx.fillText(modeLabel, canvas.width / 2, 46);

        ctx.fillStyle = '#f8fafc';
        ctx.font = '600 18px Montserrat, sans-serif';
        ctx.fillText(headline.slice(0, 32), canvas.width / 2, canvas.height / 2);

        ctx.fillStyle = '#cbd5e1';
        ctx.font = '13px Montserrat, sans-serif';
        ctx.fillText(
            `${playheadSec.toFixed(1)}s / ${Math.max(1, totalDurationSec).toFixed(1)}s · ${filterPreset}`,
            canvas.width / 2,
            canvas.height / 2 + 28
        );
        ctx.restore();
    }, [mode, aspectRatio, playheadSec, totalDurationSec, activeClip, filterPreset, captionText, familyCode, canvasRef]);

    return (
        <section className={styles.panel} aria-label="Preview Stage">
            <div className={styles.toolbarRow}>
                <h2 className={styles.panelTitle}>Preview Stage</h2>
                <div style={{ display: 'flex', gap: '8px', overflowX: 'auto' }}>
                    {Object.keys(ASPECT_DIMENSIONS).map((ratio) => (
                        <button
                            key={ratio}
                            type="button"
                            className={`${styles.btn} ${aspectRatio === ratio ? styles.btnActive : ''}`}
                            aria-pressed={aspectRatio === ratio}
                            onClick={() => onAspectRatioChange(ratio)}
                        >
                            <span>{ratio}</span>
                        </button>
                    ))}
                </div>
            </div>

            <div className={styles.stageFrame} style={{ aspectRatio: dims.css }}>
                <canvas
                    ref={canvasRef}
                    width={dims.width}
                    height={dims.height}
                    className={styles.stageCanvas}
                    style={{ filter: FILTER_MAP[filterPreset] || 'none' }}
                    aria-label="Studio video and poster canvas preview"
                />
                {captionText && (
                    <div className={styles.captionPreview} role="status">
                        {captionText}
                    </div>
                )}
            </div>

            <div className={styles.toolbarRow}>
                <button
                    type="button"
                    className={`${styles.btn} ${styles.btnPrimary}`}
                    onClick={onTogglePlay}
                >
                    <i className={`bi ${isPlaying ? 'bi-pause-fill' : 'bi-play-fill'}`} aria-hidden="true" />
                    <span>{isPlaying ? 'Pause' : 'Play preview'}</span>
                </button>
                <span style={{ fontSize: '0.875rem', fontWeight: 600, color: '#cbd5e1' }}>
                    {playheadSec.toFixed(1)}s / {Math.max(1, totalDurationSec).toFixed(1)}s
                </span>
            </div>
        </section>
    );
}
