import { useRef, useState } from 'preact/hooks';
import styles from './StudioShell.module.css';

export function MediaBin({ assets, onUploadFiles, onAddToTimeline, onRemoveAsset }) {
    const fileInputRef = useRef(null);
    const [failedIds, setFailedIds] = useState({});
    const [isDragging, setIsDragging] = useState(false);

    const handleFiles = (fileList) => {
        if (!fileList || fileList.length === 0) return;
        onUploadFiles(Array.from(fileList));
    };

    const markFailed = (id) => {
        setFailedIds((prev) => ({ ...prev, [id]: true }));
    };

    return (
        <section className={styles.panel} aria-label="Media Bin">
            <div className={styles.panelHeader}>
                <h2 className={styles.panelTitle}>Media Bin</h2>
                <button
                    type="button"
                    className={`${styles.btn} ${styles.btnGold}`}
                    onClick={() => fileInputRef.current && fileInputRef.current.click()}
                >
                    <i className="bi bi-plus-lg" aria-hidden="true" />
                    <span>Add media</span>
                </button>
            </div>

            <input
                ref={fileInputRef}
                type="file"
                accept="image/*,video/*,audio/*"
                multiple
                className={styles.srOnly}
                aria-label="Choose photos, videos or audio files"
                onChange={(e) => {
                    handleFiles(e.target.files);
                    e.target.value = '';
                }}
            />

            <div
                onDragOver={(e) => {
                    e.preventDefault();
                    setIsDragging(true);
                }}
                onDragLeave={() => setIsDragging(false)}
                onDrop={(e) => {
                    e.preventDefault();
                    setIsDragging(false);
                    handleFiles(e.dataTransfer && e.dataTransfer.files);
                }}
                style={isDragging ? { borderColor: '#ffd700', background: 'rgba(255,215,0,0.06)' } : undefined}
                className={styles.emptyState}
            >
                <i className="bi bi-cloud-arrow-up" aria-hidden="true" style={{ fontSize: '1.5rem', color: '#ffd700' }} />
                <span>
                    {assets.length === 0
                        ? 'Drop family photos, clips or audio here to start editing.'
                        : 'Drop more media here or use Add media.'}
                </span>
                {assets.length === 0 && (
                    <button
                        type="button"
                        className={`${styles.btn} ${styles.btnPrimary}`}
                        onClick={() => fileInputRef.current && fileInputRef.current.click()}
                    >
                        <span>Upload family media</span>
                    </button>
                )}
            </div>

            {assets.length > 0 && (
                <div className={styles.assetList}>
                    {assets.map((asset) => {
                        const isBroken = Boolean(failedIds[asset.id]) || !asset.url;
                        return (
                            <article key={asset.id} className={styles.assetCard}>
                                <div className={styles.assetThumbWrap}>
                                    {asset.kind === 'image' && !isBroken ? (
                                        <img
                                            src={asset.url}
                                            alt={asset.name}
                                            className={styles.assetThumb}
                                            onError={() => markFailed(asset.id)}
                                        />
                                    ) : (
                                        <div className={styles.assetFallback} role="img" aria-label={`${asset.name} preview`}>
                                            <i
                                                className={`bi ${asset.kind === 'video' ? 'bi-film' : asset.kind === 'audio' ? 'bi-music-note-beamed' : 'bi-image'}`}
                                                aria-hidden="true"
                                                style={{ fontSize: '1.5rem', color: '#ffd700' }}
                                            />
                                            <span>{isBroken && asset.kind === 'image' ? 'Preview unavailable' : asset.kind.toUpperCase()}</span>
                                        </div>
                                    )}
                                </div>
                                <div className={styles.assetMeta}>
                                    <span className={styles.assetName} title={asset.name}>
                                        {asset.name}
                                    </span>
                                    <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                                        <button
                                            type="button"
                                            className={`${styles.btn} ${styles.btnPrimary}`}
                                            style={{ flex: '1 1 auto' }}
                                            onClick={() => onAddToTimeline(asset)}
                                        >
                                            <span>Use</span>
                                        </button>
                                        <button
                                            type="button"
                                            className={styles.btn}
                                            aria-label={`Remove ${asset.name}`}
                                            onClick={() => onRemoveAsset(asset.id)}
                                        >
                                            <i className="bi bi-trash" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                            </article>
                        );
                    })}
                </div>
            )}
        </section>
    );
}
