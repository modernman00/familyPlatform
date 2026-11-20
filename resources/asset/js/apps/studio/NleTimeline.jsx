import styles from './StudioShell.module.css';

export function NleTimeline({
    clips,
    selectedClipId,
    onSelectClip,
    onUpdateClip,
    onRemoveClip,
    playheadSec,
    totalDurationSec,
    onSeek,
}) {
    const safeDuration = Math.max(1, totalDurationSec);

    return (
        <section className={styles.panel} aria-label="NLE Timeline">
            <div className={styles.panelHeader}>
                <h2 className={styles.panelTitle}>Timeline</h2>
                <span style={{ fontSize: '0.75rem', color: '#cbd5e1', fontWeight: 600 }}>
                    {clips.length} {clips.length === 1 ? 'clip' : 'clips'} · {safeDuration.toFixed(1)}s
                </span>
            </div>

            <div className={styles.fieldGroup}>
                <label htmlFor="studio-playhead-scrubber" className={styles.label}>
                    Playhead ({playheadSec.toFixed(1)}s)
                </label>
                <input
                    id="studio-playhead-scrubber"
                    type="range"
                    min="0"
                    max={safeDuration}
                    step="0.1"
                    value={Math.min(playheadSec, safeDuration)}
                    onInput={(e) => onSeek(parseFloat(e.target.value) || 0)}
                    onChange={(e) => onSeek(parseFloat(e.target.value) || 0)}
                    style={{ width: '100%', minHeight: '44px', accentColor: '#ffd700' }}
                />
            </div>

            <div className={styles.timelineTrack}>
                {clips.length === 0 ? (
                    <div className={styles.emptyState}>
                        <span>No clips on the timeline yet. Select &ldquo;Use&rdquo; on any asset in the Media Bin.</span>
                    </div>
                ) : (
                    clips.map((clip, idx) => {
                        const isSelected = clip.id === selectedClipId;
                        return (
                            <div
                                key={clip.id}
                                className={`${styles.clipRow} ${isSelected ? styles.clipRowSelected : ''}`}
                            >
                                <button
                                    type="button"
                                    className={`${styles.btn} ${isSelected ? styles.btnGold : ''}`}
                                    onClick={() => onSelectClip(clip.id)}
                                >
                                    <span>#{idx + 1} · {clip.track.toUpperCase()}</span>
                                </button>
                                <span
                                    style={{
                                        fontSize: '0.8125rem',
                                        fontWeight: 600,
                                        color: '#f8fafc',
                                        flex: '1 1 120px',
                                        minWidth: 0,
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
                                        whiteSpace: 'nowrap',
                                    }}
                                    title={clip.name}
                                >
                                    {clip.name}
                                </span>
                                <label className={styles.label} style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                    <span>Dur (s)</span>
                                    <input
                                        type="number"
                                        min="1"
                                        max="120"
                                        step="0.5"
                                        value={clip.durationSec}
                                        aria-label={`Duration in seconds for ${clip.name}`}
                                        className={styles.input}
                                        style={{ width: '80px' }}
                                        onChange={(e) =>
                                            onUpdateClip(clip.id, {
                                                durationSec: Math.max(1, Math.min(120, parseFloat(e.target.value) || 5)),
                                            })
                                        }
                                    />
                                </label>
                                <button
                                    type="button"
                                    className={styles.btn}
                                    aria-label={`Remove ${clip.name} from timeline`}
                                    onClick={() => onRemoveClip(clip.id)}
                                >
                                    <i className="bi bi-x-lg" aria-hidden="true" />
                                </button>
                            </div>
                        );
                    })
                )}
            </div>
        </section>
    );
}
