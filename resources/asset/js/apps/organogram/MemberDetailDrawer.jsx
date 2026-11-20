import { useEffect, useState } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { notifyToast } from '../shared/bootstrap';
import styles from './OrganogramShell.module.css';

export function MemberDetailDrawer({
    node,
    bootstrap,
    isReadOnly,
    onClose,
    onOpenBranchEditor,
    onNodeDeleted,
}) {
    const [dossier, setDossier] = useState(null);
    const [loading, setLoading] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [errorMsg, setErrorMsg] = useState('');

    useEffect(() => {
        if (!node) return undefined;
        let cancelled = false;
        setLoading(true);
        setErrorMsg('');
        setDossier(null);

        apiFetch(`/member/organogram/node/${encodeURIComponent(String(node.id))}`, {}, bootstrap)
            .then((res) => {
                if (!cancelled) setDossier(res && (res.node || res.data || res));
            })
            .catch((err) => {
                if (!cancelled) setErrorMsg(err && err.message ? err.message : 'Unable to load relative details.');
            })
            .finally(() => {
                if (!cancelled) setLoading(false);
            });

        const onKeyDown = (e) => {
            if (e.key === 'Escape') onClose();
        };
        window.addEventListener('keydown', onKeyDown);
        return () => {
            cancelled = true;
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [node, bootstrap, onClose]);

    if (!node) return null;

    const displayName = (dossier && (dossier.fullName || dossier.name))
        || node.fullName
        || node.name
        || 'Family Member';

    const handleDelete = async () => {
        setDeleting(true);
        setErrorMsg('');
        try {
            const fd = new FormData();
            fd.append('node_id', String(node.id));
            await apiFetch('/member/organogram/editor/delete', { method: 'POST', body: fd }, bootstrap);
            notifyToast('Relative removed from tree.', 'success');
            onNodeDeleted(String(node.id));
            onClose();
        } catch (err) {
            setErrorMsg(err && err.message ? err.message : 'Unable to remove relative.');
        } finally {
            setDeleting(false);
        }
    };

    return (
        <div
            className={styles.drawerBackdrop}
            role="dialog"
            aria-modal="true"
            aria-labelledby="drawer-member-title"
            onClick={(e) => {
                if (e.target === e.currentTarget) onClose();
            }}
        >
            <aside className={styles.drawerPanel}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '8px' }}>
                    <h2 id="drawer-member-title" className={styles.title} style={{ fontSize: '1.25rem' }}>
                        {displayName}
                    </h2>
                    <button
                        type="button"
                        className={styles.btn}
                        aria-label="Close member details"
                        onClick={onClose}
                    >
                        <i className="bi bi-x-lg" aria-hidden="true" />
                    </button>
                </div>

                <div className={styles.metaRow}>
                    <span className={styles.badge}>
                        {node.relationLabel || node.relationship || 'Relative'}
                    </span>
                    {node.gender && <span className={styles.badge}>{node.gender}</span>}
                </div>

                {loading && (
                    <div className={styles.badge} role="status">
                        Loading relative dossier…
                    </div>
                )}

                {errorMsg && (
                    <div role="alert" style={{ color: '#fecaca', background: '#7f1d1d', padding: '10px 12px', borderRadius: '10px', fontSize: '0.875rem' }}>
                        {errorMsg}
                    </div>
                )}

                {dossier && (
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', fontSize: '0.875rem' }}>
                        {dossier.bio && <p style={{ margin: 0 }}>{dossier.bio}</p>}
                        {dossier.location && (
                            <div>
                                <strong>Location:</strong> {dossier.location}
                            </div>
                        )}
                        {dossier.birth_year && (
                            <div>
                                <strong>Birth year:</strong> {dossier.birth_year}
                            </div>
                        )}
                    </div>
                )}

                {!isReadOnly && (
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', marginTop: 'auto' }}>
                        <button
                            type="button"
                            className={`${styles.btn} ${styles.btnPrimary}`}
                            onClick={() => onOpenBranchEditor(node)}
                        >
                            <i className="bi bi-person-plus-fill" aria-hidden="true" />
                            <span>Add branch to {displayName}</span>
                        </button>
                        <button
                            type="button"
                            className={styles.btn}
                            data-spin="true"
                            aria-busy={deleting ? 'true' : undefined}
                            disabled={deleting}
                            onClick={handleDelete}
                        >
                            {deleting ? (
                                <span className="st-spinner-border" role="status" aria-hidden="true" />
                            ) : (
                                <i className="bi bi-trash" aria-hidden="true" />
                            )}
                            <span>{deleting ? 'Removing…' : 'Remove from tree'}</span>
                        </button>
                    </div>
                )}
            </aside>
        </div>
    );
}
