import styles from './StudioShell.module.css';

const FILTER_OPTIONS = [
    { id: 'none', label: 'Original' },
    { id: 'warm-heritage', label: 'Warm Heritage' },
    { id: 'sepia-archive', label: 'Sepia Archive' },
    { id: 'cinematic-noir', label: 'Cinematic Noir' },
    { id: 'crisp-daylight', label: 'Crisp Daylight' },
];

/**
 * Deterministic Web Audio motion SFX synthesiser (no speechSynthesis).
 */
export function synthesiseMotionSfx(preset = 'chime') {
    if (typeof window === 'undefined') return false;
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return false;
    try {
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = preset === 'whoosh' ? 'triangle' : 'sine';
        const now = ctx.currentTime;
        osc.frequency.setValueAtTime(preset === 'whoosh' ? 220 : 523.25, now);
        osc.frequency.exponentialRampToValueAtTime(preset === 'whoosh' ? 440 : 783.99, now + 0.18);
        gain.gain.setValueAtTime(0.08, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.22);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now);
        osc.stop(now + 0.23);
        return true;
    } catch {
        return false;
    }
}

export function InspectorPanel({
    filterPreset,
    onFilterChange,
    captionText,
    onCaptionChange,
    sfxPreset,
    onSfxChange,
    onOpenExport,
}) {
    return (
        <aside className={styles.panel} aria-label="Inspector Panel">
            <h2 className={styles.panelTitle}>Inspector</h2>

            <div className={styles.fieldGroup}>
                <label htmlFor="studio-filter-select" className={styles.label}>
                    Colour grade &amp; filter
                </label>
                <select
                    id="studio-filter-select"
                    className={styles.select}
                    value={filterPreset}
                    onChange={(e) => onFilterChange(e.target.value)}
                >
                    {FILTER_OPTIONS.map((opt) => (
                        <option key={opt.id} value={opt.id}>
                            {opt.label}
                        </option>
                    ))}
                </select>
            </div>

            <div className={styles.fieldGroup}>
                <label htmlFor="studio-caption-input" className={styles.label}>
                    On-screen caption overlay
                </label>
                <textarea
                    id="studio-caption-input"
                    rows={3}
                    maxLength={140}
                    className={styles.textarea}
                    placeholder="Add a caption for your family archive…"
                    value={captionText}
                    onInput={(e) => onCaptionChange(e.target.value)}
                    onChange={(e) => onCaptionChange(e.target.value)}
                />
            </div>

            <div className={styles.fieldGroup}>
                <label htmlFor="studio-sfx-select" className={styles.label}>
                    Web Audio motion SFX
                </label>
                <div style={{ display: 'flex', gap: '8px' }}>
                    <select
                        id="studio-sfx-select"
                        className={styles.select}
                        value={sfxPreset}
                        onChange={(e) => onSfxChange(e.target.value)}
                    >
                        <option value="chime">Heritage Chime</option>
                        <option value="whoosh">Reel Transition Whoosh</option>
                    </select>
                    <button
                        type="button"
                        className={styles.btn}
                        onClick={() => synthesiseMotionSfx(sfxPreset)}
                        aria-label="Preview Web Audio SFX"
                    >
                        <i className="bi bi-volume-up-fill" aria-hidden="true" />
                        <span>Test</span>
                    </button>
                </div>
            </div>

            <button
                type="button"
                className={`${styles.btn} ${styles.btnGold}`}
                onClick={onOpenExport}
            >
                <i className="bi bi-film" aria-hidden="true" />
                <span>Export &amp; Publish</span>
            </button>
        </aside>
    );
}
