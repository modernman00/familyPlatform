import { useEffect, useMemo, useRef, useState } from 'preact/hooks';
import SovereignNleEngine from '@shared/SovereignNleEngine';
import { MediaBin } from './MediaBin';
import { PreviewStage } from './PreviewStage';
import { NleTimeline } from './NleTimeline';
import { InspectorPanel } from './InspectorPanel';
import { ExportModal } from './ExportModal';
import styles from './StudioShell.module.css';

const MODES = [
    { id: 'nle', label: 'Video NLE', icon: 'bi-scissors' },
    { id: 'poster', label: 'Poster Studio', icon: 'bi-card-image' },
    { id: 'reels', label: 'Reels Archive', icon: 'bi-camera-reels' },
];

export function StudioShell({ bootstrap = {} }) {
    const [mode, setMode] = useState(bootstrap.initialMode || 'nle');
    const [assets, setAssets] = useState(() => Array.isArray(bootstrap.initialAssets) ? bootstrap.initialAssets : []);
    const [clips, setClips] = useState([]);
    const [selectedClipId, setSelectedClipId] = useState(null);
    const [aspectRatio, setAspectRatio] = useState('9:16');
    const [isPlaying, setIsPlaying] = useState(false);
    const [playheadSec, setPlayheadSec] = useState(0);
    const [filterPreset, setFilterPreset] = useState('warm-heritage');
    const [captionText, setCaptionText] = useState('');
    const [sfxPreset, setSfxPreset] = useState('chime');
    const [isExportOpen, setIsExportOpen] = useState(false);

    const canvasRef = useRef(null);
    const createdUrlsRef = useRef([]);
    const nleEngineRef = useRef(null);

    const NleEngineCtor = typeof SovereignNleEngine === 'function'
        ? SovereignNleEngine
        : (typeof globalThis !== 'undefined' && typeof globalThis.SovereignNleEngine === 'function'
            ? globalThis.SovereignNleEngine
            : null);

    if (!nleEngineRef.current && NleEngineCtor) {
        nleEngineRef.current = new NleEngineCtor({ aspectRatio: '9:16' });
    }

    const totalDurationSec = useMemo(
        () => clips.reduce((acc, c) => acc + (Number(c.durationSec) || 5), 0),
        [clips]
    );

    const activeClip = useMemo(() => {
        if (clips.length === 0) return null;
        if (selectedClipId) {
            const found = clips.find((c) => c.id === selectedClipId);
            if (found) return found;
        }
        return clips[0];
    }, [clips, selectedClipId]);

    useEffect(() => {
        if (!isPlaying) return undefined;
        const maxDur = Math.max(1, totalDurationSec);
        const timer = setInterval(() => {
            setPlayheadSec((prev) => {
                const next = prev + 0.1;
                return next >= maxDur ? 0 : Number(next.toFixed(1));
            });
        }, 100);
        return () => clearInterval(timer);
    }, [isPlaying, totalDurationSec]);

    useEffect(() => {
        return () => {
            createdUrlsRef.current.forEach((url) => {
                try {
                    URL.revokeObjectURL(url);
                } catch {
                    // ignore
                }
            });
        };
    }, []);

    const handleUploadFiles = (files) => {
        const nextAssets = files.map((file, idx) => {
            const mime = String(file.type || '');
            const kind = mime.startsWith('video/')
                ? 'video'
                : mime.startsWith('audio/')
                    ? 'audio'
                    : 'image';
            const url = typeof URL !== 'undefined' && typeof URL.createObjectURL === 'function'
                ? URL.createObjectURL(file)
                : '';
            if (url) createdUrlsRef.current.push(url);
            return {
                id: `asset_${Date.now()}_${idx}_${Math.random().toString(36).slice(2, 7)}`,
                name: file.name || `Clip ${idx + 1}`,
                kind,
                url,
                file,
            };
        });
        setAssets((prev) => [...nextAssets, ...prev]);
    };

    const handleAddToTimeline = (asset) => {
        const track = asset.kind === 'audio' ? 'audio' : 'video';
        const engineClip = nleEngineRef.current
            ? nleEngineRef.current.addClip({
                track: track === 'audio' ? 'A1' : 'V1',
                type: asset.kind || 'video',
                title: asset.name,
                src: asset.url,
                durationSec: 5,
            })
            : null;
        const clip = {
            id: engineClip ? engineClip.id : `clip_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`,
            assetId: asset.id,
            name: asset.name,
            track,
            durationSec: 5,
            url: asset.url,
        };
        setClips((prev) => [...prev, clip]);
        setSelectedClipId(clip.id);
    };

    const handleAspectRatioChange = (nextRatio) => {
        setAspectRatio(nextRatio);
        if (nleEngineRef.current && typeof nleEngineRef.current.setAspectRatio === 'function') {
            try {
                nleEngineRef.current.setAspectRatio(nextRatio);
            } catch {
                // ignore unsupported ratio
            }
        }
    };

    const handleRemoveAsset = (assetId) => {
        setAssets((prev) => prev.filter((a) => a.id !== assetId));
        setClips((prev) => prev.filter((c) => c.assetId !== assetId));
    };

    const handleUpdateClip = (clipId, patch) => {
        if (patch.durationSec && nleEngineRef.current && typeof nleEngineRef.current.stretchClipDuration === 'function') {
            nleEngineRef.current.stretchClipDuration(clipId, patch.durationSec);
        }
        setClips((prev) => prev.map((c) => (c.id === clipId ? { ...c, ...patch } : c)));
    };

    const handleRemoveClip = (clipId) => {
        setClips((prev) => prev.filter((c) => c.id !== clipId));
        if (selectedClipId === clipId) setSelectedClipId(null);
    };

    return (
        <div className={styles.shell} data-island="creative-studio">
            <header className={styles.header}>
                <div className={styles.titleGroup}>
                    <span className={styles.eyebrow}>Sovereign Living Archives</span>
                    <h1 className={styles.title}>Family Creative Studio</h1>
                </div>

                <nav className={styles.modeTabs} aria-label="Studio modes">
                    {MODES.map((m) => (
                        <button
                            key={m.id}
                            type="button"
                            className={`${styles.btn} ${mode === m.id ? styles.btnActive : ''}`}
                            aria-pressed={mode === m.id}
                            onClick={() => setMode(m.id)}
                        >
                            <i className={`bi ${m.icon}`} aria-hidden="true" />
                            <span>{m.label}</span>
                        </button>
                    ))}
                    <a href="/director-mode" className={styles.btn} style={{ whiteSpace: 'nowrap', flexShrink: 0, minHeight: '44px' }}>
                        <i className="bi bi-camera-reels-fill" aria-hidden="true" />
                        <span>Director Mode</span>
                    </a>
                    <a href="/reels" className={styles.btn}>
                        <i className="bi bi-collection-play" aria-hidden="true" />
                        <span>View Reels</span>
                    </a>
                </nav>
            </header>

            <div className={styles.workspaceGrid}>
                <MediaBin
                    assets={assets}
                    onUploadFiles={handleUploadFiles}
                    onAddToTimeline={handleAddToTimeline}
                    onRemoveAsset={handleRemoveAsset}
                />

                <div style={{ display: 'flex', flexDirection: 'column', gap: '16px', minWidth: 0 }}>
                    <PreviewStage
                        mode={mode}
                        aspectRatio={aspectRatio}
                        onAspectRatioChange={handleAspectRatioChange}
                        isPlaying={isPlaying}
                        onTogglePlay={() => setIsPlaying((p) => !p)}
                        playheadSec={playheadSec}
                        totalDurationSec={totalDurationSec}
                        activeClip={activeClip}
                        filterPreset={filterPreset}
                        captionText={captionText}
                        familyCode={bootstrap.famCode}
                        canvasRef={canvasRef}
                    />
                    <NleTimeline
                        clips={clips}
                        selectedClipId={selectedClipId}
                        onSelectClip={setSelectedClipId}
                        onUpdateClip={handleUpdateClip}
                        onRemoveClip={handleRemoveClip}
                        playheadSec={playheadSec}
                        totalDurationSec={totalDurationSec}
                        onSeek={setPlayheadSec}
                    />
                </div>

                <InspectorPanel
                    filterPreset={filterPreset}
                    onFilterChange={setFilterPreset}
                    captionText={captionText}
                    onCaptionChange={setCaptionText}
                    sfxPreset={sfxPreset}
                    onSfxChange={setSfxPreset}
                    onOpenExport={() => setIsExportOpen(true)}
                />
            </div>

            <ExportModal
                isOpen={isExportOpen}
                onClose={() => setIsExportOpen(false)}
                bootstrap={bootstrap}
                clips={clips}
                captionText={captionText}
                aspectRatio={aspectRatio}
                canvasRef={canvasRef}
                nleEngineRef={nleEngineRef}
            />
        </div>
    );
}
