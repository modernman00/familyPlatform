import { useState } from 'preact/hooks';
import styles from './OrganogramShell.module.css';

function getInitials(name) {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '?';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
}

export function PersonNodeCard({
    node,
    isSelected,
    isReadOnly,
    onInspect,
    onAddBranch,
}) {
    const [imgFailed, setImgFailed] = useState(false);
    const fullName = node.fullName || node.name || `${node.first_name || ''} ${node.last_name || ''}`.trim() || 'Family Member';
    const roleLabel = node.relationLabel || node.relationship || node.role || 'Relative';
    const avatarUrl = node.avatar || node.profileImg || node.photo || '';

    return (
        <article
            className={`${styles.nodeCard} ${isSelected ? styles.nodeCardSelected : ''}`}
            data-node-id={String(node.id)}
        >
            <div className={styles.nodeTop}>
                {avatarUrl && !imgFailed ? (
                    <img
                        src={avatarUrl}
                        alt={fullName}
                        className={styles.avatarImg}
                        onError={() => setImgFailed(true)}
                    />
                ) : (
                    <div className={styles.avatarFallback} aria-hidden="true">
                        {getInitials(fullName)}
                    </div>
                )}

                <div style={{ minWidth: 0, flex: '1 1 auto' }}>
                    <div className={styles.nodeName} title={fullName}>
                        {fullName}
                    </div>
                    <div className={styles.nodeRole}>{roleLabel}</div>
                    {Array.isArray(node.arcadeBadges) && node.arcadeBadges.length > 0 && (
                        <div
                            className="badge rounded-pill mt-1 d-inline-flex align-items-center gap-1"
                            style={{ background: 'linear-gradient(135deg, #78350f, #b45309)', color: '#ffffff', fontSize: '0.68rem', fontWeight: 700 }}
                            title={`Family Arcade Crown: ${node.arcadeBadges[0].badgeTitle || node.arcadeBadges[0]}`}
                        >
                            <i className="bi bi-trophy-fill" style={{ color: '#fde68a' }} aria-hidden="true" />
                            <span>{node.arcadeBadges[0].badgeTitle || node.arcadeBadges[0]}</span>
                        </div>
                    )}
                </div>
            </div>

            <div className={styles.nodeActions}>
                <button
                    type="button"
                    className={styles.compactAction}
                    onClick={() => onInspect(node)}
                >
                    <i className="bi bi-person-lines-fill" aria-hidden="true" />
                    <span>View</span>
                </button>
                {!isReadOnly && (
                    <button
                        type="button"
                        className={styles.compactAction}
                        onClick={() => onAddBranch(node)}
                    >
                        <i className="bi bi-plus-circle" aria-hidden="true" />
                        <span>Branch</span>
                    </button>
                )}
            </div>
        </article>
    );
}
