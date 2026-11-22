import { useState, useEffect, useMemo } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { Portal } from '../shared/Portal';
import { getAvatarUrl, getInitials, formatRelativeTime } from './feedUtils';
import styles from './FeedShell.module.css';

export function ViewersModal({
    isOpen,
    type = 'post',
    targetNo = null,
    initialCount = 0,
    bootstrap = {},
    onClose,
}) {
    const [loading, setLoading] = useState(false);
    const [viewers, setViewers] = useState([]);
    const [count, setCount] = useState(initialCount);
    const [query, setQuery] = useState('');
    const [error, setError] = useState('');

    useEffect(() => {
        if (!isOpen || !targetNo) return;
        let cancelled = false;
        setLoading(true);
        setError('');
        setQuery('');
        setCount(initialCount);

        const endpoint = type === 'comment'
            ? `/api/comments/${encodeURIComponent(targetNo)}/viewers`
            : `/api/posts/${encodeURIComponent(targetNo)}/viewers`;

        apiFetch(endpoint, { method: 'GET' }, bootstrap)
            .then((res) => {
                if (cancelled) return;
                const list = Array.isArray(res?.viewers) ? res.viewers : [];
                setViewers(list);
                setCount(typeof res?.views_count === 'number' ? res.views_count : list.length);
            })
            .catch((err) => {
                if (!cancelled) {
                    setError(err?.message || 'Could not load viewers.');
                }
            })
            .finally(() => {
                if (!cancelled) setLoading(false);
            });

        return () => {
            cancelled = true;
        };
    }, [isOpen, type, targetNo, initialCount, bootstrap]);

    useEffect(() => {
        if (!isOpen) return undefined;
        const onKey = (e) => {
            if (e.key === 'Escape') onClose?.();
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [isOpen, onClose]);

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) return viewers;
        return viewers.filter((v) => {
            const name = `${v.firstName || ''} ${v.lastName || ''} ${v.fullName || ''}`.toLowerCase();
            return name.includes(q);
        });
    }, [viewers, query]);

    if (!isOpen) return null;

    return (
        <Portal>
        <div
            className={styles.modalBackdrop}
            role="dialog"
            aria-modal="true"
            aria-label={type === 'comment' ? 'Comment viewers' : 'Post viewers'}
            data-testid="viewers-modal"
            onClick={(e) => {
                if (e.target === e.currentTarget) onClose?.();
            }}
        >
            <div className={styles.modalDialog} style={{ maxWidth: 460, background: 'var(--card-bg, #ffffff)', color: 'var(--text-main, #0f172a)' }}>
                <div className={styles.modalHeader} style={{ borderBottom: '1px solid var(--border-color, #e2e8f0)' }}>
                    <div className="d-flex align-items-center gap-2">
                        <i className="bi bi-eye-fill text-primary" aria-hidden="true" />
                        <span className="fw-bold">
                            {count} {count === 1 ? 'Family View' : 'Family Views'}
                        </span>
                    </div>
                    <button
                        type="button"
                        className={styles.btnBase}
                        style={{ minWidth: '44px', minHeight: '44px' }}
                        onClick={onClose}
                        aria-label="Close viewers"
                    >
                        Close
                    </button>
                </div>

                <div className={styles.modalBody} style={{ alignItems: 'stretch', maxHeight: '65vh', overflowY: 'auto' }}>
                    <input
                        type="search"
                        className="form-control form-control-sm mb-2"
                        style={{ minHeight: '44px' }}
                        placeholder="Search family members…"
                        aria-label="Search viewers"
                        value={query}
                        onInput={(e) => setQuery(e.target.value)}
                    />

                    {loading && <div className="text-muted small py-3 text-center">Loading family viewers…</div>}
                    {!loading && error && <div className="text-danger small py-2">{error}</div>}
                    {!loading && !error && filtered.length === 0 && (
                        <div className="text-muted small py-3 text-center">
                            {query.trim() ? 'No matching family members found.' : 'No recorded views yet.'}
                        </div>
                    )}

                    {!loading && !error && filtered.length > 0 && (
                        <div className="d-flex flex-column gap-2">
                            {filtered.map((v, idx) => {
                                const name = v.fullName || `${v.firstName || ''} ${v.lastName || ''}`.trim() || 'Family Member';
                                return (
                                    <div key={v.id || v.user_id || idx} className="d-flex align-items-center justify-content-between gap-2 p-2 rounded-3 border">
                                        <div className="d-flex align-items-center gap-2 min-w-0">
                                            {v.profileImg || v.img || v.profilePics ? (
                                                <img
                                                    src={getAvatarUrl(v)}
                                                    alt={name}
                                                    width="36"
                                                    height="36"
                                                    className="rounded-circle flex-shrink-0"
                                                    style={{ objectFit: 'cover' }}
                                                />
                                            ) : (
                                                <div
                                                    className="rounded-circle flex-shrink-0 d-inline-flex align-items-center justify-content-center fw-bold text-white"
                                                    style={{ width: 36, height: 36, background: '#a31621', fontSize: '0.75rem' }}
                                                >
                                                    {getInitials(v.firstName, v.lastName)}
                                                </div>
                                            )}
                                            <span className="fw-semibold text-truncate">{name}</span>
                                        </div>
                                        {v.viewed_at && (
                                            <small className="text-muted flex-shrink-0">{formatRelativeTime(v.viewed_at)}</small>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>
        </div>
        </Portal>
    );
}
