import { useEffect, useState } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { notifyToast } from '../shared/bootstrap';
import styles from './OrganogramShell.module.css';

const RELATION_ENDPOINTS = {
    partner: '/member/organogram/editor/partner',
    child: '/member/organogram/editor/child',
    parents: '/member/organogram/editor/parents',
    sibling: '/member/organogram/editor/sibling',
    'uncle-aunt': '/member/organogram/editor/uncle-aunt',
    'in-law': '/member/organogram/editor/in-law',
};

export function BranchEditorModal({
    isOpen,
    anchorNode,
    bootstrap,
    onClose,
    onSaved,
}) {
    const [relation, setRelation] = useState('child');
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    const [gender, setGender] = useState('unknown');
    const [submitting, setSubmitting] = useState(false);
    const [errorMsg, setErrorMsg] = useState('');

    useEffect(() => {
        if (!isOpen) return undefined;
        setErrorMsg('');
        const onKeyDown = (e) => {
            if (e.key === 'Escape') onClose();
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    const handleSubmit = async (e) => {
        e.preventDefault();
        const trimmedFirst = firstName.trim();
        const trimmedLast = lastName.trim();

        if (!trimmedFirst) {
            setErrorMsg('Please enter a first name.');
            return;
        }

        setSubmitting(true);
        setErrorMsg('');

        try {
            const endpoint = RELATION_ENDPOINTS[relation] || RELATION_ENDPOINTS.child;
            const fd = new FormData();
            fd.append('first_name', trimmedFirst);
            fd.append('last_name', trimmedLast || bootstrap.primarySurname || '');
            fd.append('gender', gender);
            if (anchorNode && anchorNode.id) {
                fd.append('anchor_node_id', String(anchorNode.id));
            }
            if (bootstrap.familyCode) {
                fd.append('family_code', String(bootstrap.familyCode));
            }

            const result = await apiFetch(endpoint, { method: 'POST', body: fd }, bootstrap);
            notifyToast('Relative added to your family tree.', 'success');
            onSaved({
                id: (result && (result.node_id || result.id)) || `local_${Date.now()}`,
                fullName: `${trimmedFirst} ${trimmedLast}`.trim(),
                first_name: trimmedFirst,
                last_name: trimmedLast,
                gender,
                relationLabel: relation,
                generation: relation === 'parents' ? -1 : relation === 'child' ? 1 : 0,
                lineage: relation === 'partner' || relation === 'in-law' ? 'married' : 'birth',
            });
            setFirstName('');
            setLastName('');
            onClose();
        } catch (err) {
            setErrorMsg(err && err.message ? err.message : 'Unable to save relative.');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div
            className={styles.modalBackdrop}
            role="dialog"
            aria-modal="true"
            aria-labelledby="branch-modal-title"
            onClick={(e) => {
                if (e.target === e.currentTarget) onClose();
            }}
        >
            <form className={styles.modalCard} onSubmit={handleSubmit}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '8px' }}>
                    <h2 id="branch-modal-title" className={styles.title} style={{ fontSize: '1.25rem' }}>
                        Add Relative
                    </h2>
                    <button
                        type="button"
                        className={styles.btn}
                        aria-label="Close add relative modal"
                        onClick={onClose}
                    >
                        <i className="bi bi-x-lg" aria-hidden="true" />
                    </button>
                </div>

                {anchorNode && (
                    <div className={styles.badge}>
                        Connecting to: {anchorNode.fullName || anchorNode.name}
                    </div>
                )}

                <div className={styles.fieldGroup}>
                    <label htmlFor="branch-relation-select" className={styles.label}>
                        Relationship
                    </label>
                    <select
                        id="branch-relation-select"
                        className={styles.select}
                        value={relation}
                        onChange={(e) => setRelation(e.target.value)}
                    >
                        <option value="child">Child</option>
                        <option value="partner">Spouse / Partner</option>
                        <option value="parents">Parent</option>
                        <option value="sibling">Sibling</option>
                        <option value="uncle-aunt">Uncle / Aunt</option>
                        <option value="in-law">In-Law</option>
                    </select>
                </div>

                <div className={styles.fieldGroup}>
                    <label htmlFor="branch-first-name" className={styles.label}>
                        First name
                    </label>
                    <input
                        id="branch-first-name"
                        type="text"
                        required
                        maxLength={80}
                        className={styles.input}
                        value={firstName}
                        onInput={(e) => setFirstName(e.target.value)}
                        onChange={(e) => setFirstName(e.target.value)}
                    />
                </div>

                <div className={styles.fieldGroup}>
                    <label htmlFor="branch-last-name" className={styles.label}>
                        Surname
                    </label>
                    <input
                        id="branch-last-name"
                        type="text"
                        maxLength={80}
                        className={styles.input}
                        value={lastName}
                        onInput={(e) => setLastName(e.target.value)}
                        onChange={(e) => setLastName(e.target.value)}
                    />
                </div>

                <div className={styles.fieldGroup}>
                    <label htmlFor="branch-gender" className={styles.label}>
                        Gender
                    </label>
                    <select
                        id="branch-gender"
                        className={styles.select}
                        value={gender}
                        onChange={(e) => setGender(e.target.value)}
                    >
                        <option value="unknown">Prefer not to say / Unknown</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                    </select>
                </div>

                {errorMsg && (
                    <div role="alert" style={{ color: '#fecaca', background: '#7f1d1d', padding: '10px 12px', borderRadius: '10px', fontSize: '0.875rem' }}>
                        {errorMsg}
                    </div>
                )}

                <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                    <button type="button" className={styles.btn} onClick={onClose}>
                        <span>Cancel</span>
                    </button>
                    <button
                        type="submit"
                        className={`${styles.btn} ${styles.btnPrimary}`}
                        data-spin="true"
                        aria-busy={submitting ? 'true' : undefined}
                        disabled={submitting}
                    >
                        {submitting && <span className="st-spinner-border" role="status" aria-hidden="true" />}
                        <span>{submitting ? 'Saving…' : 'Save relative'}</span>
                    </button>
                </div>
            </form>
        </div>
    );
}
