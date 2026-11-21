import { useEffect, useRef, useState } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { notifyToast } from '../shared/bootstrap';
import styles from './StudioShell.module.css';

export function ExportModal({
    isOpen,
    onClose,
    bootstrap,
    clips,
    captionText,
    aspectRatio,
    canvasRef,
    nleEngineRef,
}) {
    const [title, setTitle] = useState('');
    const [privacy, setPrivacy] = useState('family');
    const [progress, setProgress] = useState(0);
    const [isRendering, setIsRendering] = useState(false);
    const [isPublishing, setIsPublishing] = useState(false);
    const [errorMsg, setErrorMsg] = useState('');
    const [exportedBlobUrl, setExportedBlobUrl] = useState('');
    const [exportedBlob, setExportedBlob] = useState(null);
    const studioClientRef = useRef(null);

    const VideoStudioClientCtor = typeof globalThis !== 'undefined' && typeof globalThis.SovereignVideoStudioClient === 'function'
        ? globalThis.SovereignVideoStudioClient
        : null;

    if (!studioClientRef.current && VideoStudioClientCtor) {
        studioClientRef.current = new VideoStudioClientCtor({
            eventId: bootstrap && bootstrap.famCode ? String(bootstrap.famCode) : '',
            csrfToken: bootstrap && bootstrap.csrfToken ? String(bootstrap.csrfToken) : '',
        });
    }

    useEffect(() => {
        if (!isOpen) return undefined;
        const onKeyDown = (e) => {
            if (e.key === 'Escape') onClose();
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    const handleRender = async () => {
        setErrorMsg('');
        setIsRendering(true);
        setProgress(15);

        try {
            const canvas = canvasRef && canvasRef.current;
            if (canvas && typeof canvas.toBlob === 'function') {
                setProgress(60);
                const blob = await new Promise((resolve) => {
                    canvas.toBlob((b) => resolve(b), 'image/png');
                });
                if (blob) {
                    const url = URL.createObjectURL(blob);
                    setExportedBlob(blob);
                    setExportedBlobUrl(url);
                }
            }
            setProgress(100);
            notifyToast('Archive render ready to download or publish.', 'success');
        } catch (err) {
            setErrorMsg(err && err.message ? err.message : 'Unable to render archive.');
        } finally {
            setIsRendering(false);
        }
    };

    const handlePublish = async () => {
        setErrorMsg('');
        setIsPublishing(true);
        try {
            const fd = new FormData();
            fd.append('title', title.trim() || 'Family Living Archive');
            fd.append('caption', captionText || '');
            fd.append('privacy', privacy);
            fd.append('aspect_ratio', aspectRatio);
            fd.append('clip_count', String(clips.length));
            if (nleEngineRef && nleEngineRef.current && typeof nleEngineRef.current.exportProjectJson === 'function') {
                fd.append('nle_project_json', nleEngineRef.current.exportProjectJson());
            } else {
                fd.append('nle_project_json', JSON.stringify({
                    schemaVersion: '2.0.0',
                    aspectRatio,
                    captionText: captionText || '',
                    clips: clips.map((c) => ({
                        id: c.id,
                        track: c.track || 'video',
                        title: c.name,
                        src: c.url,
                        durationSec: Number(c.durationSec) || 5,
                    })),
                }));
            }
            if (exportedBlob) {
                fd.append('media', exportedBlob, 'family-archive.png');
            }

            await apiFetch('/api/reels/upload', { method: 'POST', body: fd }, bootstrap);
            notifyToast('Published to Family Reels!', 'success');
            onClose();
        } catch (err) {
            setErrorMsg(err && err.message ? err.message : 'Could not publish reel. Please try again.');
        } finally {
            setIsPublishing(false);
        }
    };

    return (
        <div
            className={styles.modalBackdrop}
            role="dialog"
            aria-modal="true"
            aria-labelledby="export-modal-title"
            onClick={(e) => {
                if (e.target === e.currentTarget) onClose();
            }}
        >
            <div className={styles.modalCard}>
                <div className={styles.panelHeader}>
                    <h2 id="export-modal-title" className={styles.panelTitle}>
                        Export &amp; Publish Archive
                    </h2>
                    <button
                        type="button"
                        className={styles.btn}
                        aria-label="Close export modal"
                        onClick={onClose}
                    >
                        <i className="bi bi-x-lg" aria-hidden="true" />
                    </button>
                </div>

                <div className={styles.fieldGroup}>
                    <label htmlFor="export-reel-title" className={styles.label}>
                        Archive title
                    </label>
                    <input
                        id="export-reel-title"
                        type="text"
                        maxLength={120}
                        className={styles.input}
                        placeholder="e.g. Olaogun Family Reunion Highlights"
                        value={title}
                        onInput={(e) => setTitle(e.target.value)}
                        onChange={(e) => setTitle(e.target.value)}
                    />
                </div>

                <div className={styles.fieldGroup}>
                    <label htmlFor="export-reel-privacy" className={styles.label}>
                        Visibility
                    </label>
                    <select
                        id="export-reel-privacy"
                        className={styles.select}
                        value={privacy}
                        onChange={(e) => setPrivacy(e.target.value)}
                    >
                        <option value="family">Family Circle Only (#{bootstrap.famCode || 'PRIVATE'})</option>
                        <option value="private">Private Vault (Only Me)</option>
                    </select>
                </div>

                {progress > 0 && (
                    <div className={styles.progressBar} aria-label={`Render progress ${progress}%`}>
                        <div className={styles.progressFill} style={{ width: `${progress}%` }} />
                    </div>
                )}

                {errorMsg && (
                    <div role="alert" style={{ color: '#fecaca', background: '#7f1d1d', padding: '10px 12px', borderRadius: '10px', fontSize: '0.875rem' }}>
                        {errorMsg}
                    </div>
                )}

                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', justifyContent: 'flex-end' }}>
                    <button
                        type="button"
                        className={styles.btn}
                        data-spin="true"
                        aria-busy={isRendering ? 'true' : undefined}
                        disabled={isRendering}
                        onClick={handleRender}
                    >
                        {isRendering && <span className="st-spinner-border" role="status" aria-hidden="true" />}
                        <span>{isRendering ? 'Rendering…' : 'Render preview'}</span>
                    </button>
                    {exportedBlobUrl && (
                        <a
                            href={exportedBlobUrl}
                            download="family-archive.png"
                            className={`${styles.btn} ${styles.btnGold}`}
                        >
                            <span>Download</span>
                        </a>
                    )}
                    <button
                        type="button"
                        className={`${styles.btn} ${styles.btnPrimary}`}
                        data-spin="true"
                        aria-busy={isPublishing ? 'true' : undefined}
                        disabled={isPublishing}
                        onClick={handlePublish}
                    >
                        {isPublishing && <span className="st-spinner-border" role="status" aria-hidden="true" />}
                        <span>{isPublishing ? 'Publishing…' : 'Publish to Family Reels'}</span>
                    </button>
                </div>
            </div>
        </div>
    );
}
