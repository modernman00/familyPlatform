import { useEffect, useMemo, useState } from 'preact/hooks';
import {
    removeMemberRestriction,
    toastMessage,
    upsertMemberRestriction,
} from '../api/accountSettingsApi';
import styles from '../AccountSettingsShell.module.css';

const SCOPE_OPTIONS = [
    { key: 'restrict_posts', label: 'Posts & Memories' },
    { key: 'restrict_comments', label: 'Comments & Replies' },
    { key: 'restrict_reels', label: 'Video Reels' },
    { key: 'restrict_events', label: 'Events & Calendar' },
    { key: 'restrict_journals', label: 'Family Journals' },
    { key: 'restrict_announcements', label: 'Announcements' },
    { key: 'restrict_capsules', label: 'Time Capsules' },
    { key: 'restrict_profile', label: 'Profile & Photos' },
];

const INITIAL_LIMIT = 5;

function readFallbackRestrictions() {
    if (typeof document === 'undefined') return [];
    try {
        const el = document.getElementById('restrictionDirectoryData');
        if (!el || !el.textContent) return [];
        const parsed = JSON.parse(el.textContent);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

export function MemberRestrictionsPanel({ initialMembers, bootstrap, onMembersChange }) {
    const [members, setMembers] = useState(() =>
        Array.isArray(initialMembers) && initialMembers.length > 0
            ? initialMembers
            : readFallbackRestrictions()
    );
    const [searchQuery, setSearchQuery] = useState('');
    const [filterMode, setFilterMode] = useState('all');
    const [showAllMembers, setShowAllMembers] = useState(false);
    const [expandedId, setExpandedId] = useState(null);
    const [savingId, setSavingId] = useState(null);
    const [errorMsg, setErrorMsg] = useState('');

    useEffect(() => {
        if (Array.isArray(initialMembers)) {
            setMembers(initialMembers);
        }
    }, [initialMembers]);

    const restrictedCount = useMemo(
        () => members.filter((m) => Boolean(m.is_restricted)).length,
        [members]
    );

    const filteredMembers = useMemo(() => {
        const rawQ = (searchQuery || '').trim();
        if (!rawQ) {
            return members.filter((m) => (filterMode === 'restricted' ? Boolean(m.is_restricted) : true));
        }
        const cleanQ = rawQ.replace(/^[#@]/, '').toLowerCase();
        const tokens = cleanQ.split(/\s+/).filter(Boolean);
        return members.filter((m) => {
            if (filterMode === 'restricted' && !m.is_restricted) return false;
            const name = String(m.fullName || '').toLowerCase();
            const code = String(m.famCode || '').toLowerCase();
            const note = String(m.note || '').toLowerCase();
            return tokens.every(
                (t) => name.includes(t) || code.includes(t) || (`#${code}`).includes(t) || note.includes(t)
            );
        });
    }, [members, searchQuery, filterMode]);

    const visibleMembers = useMemo(() => {
        if ((searchQuery || '').trim() !== '' || filterMode === 'restricted' || showAllMembers) {
            return filteredMembers;
        }
        return filteredMembers.slice(0, INITIAL_LIMIT);
    }, [filteredMembers, searchQuery, filterMode, showAllMembers]);

    const updateMemberLocal = (memberId, updater) => {
        setMembers((prev) => {
            const next = prev.map((m) => (String(m.id) === String(memberId) ? updater(m) : m));
            if (typeof onMembersChange === 'function') {
                onMembersChange(next);
            }
            return next;
        });
    };

    const persistMember = async (member, mode) => {
        setErrorMsg('');
        setSavingId(member.id);
        try {
            const data =
                mode === 'remove'
                    ? await removeMemberRestriction(member.id, bootstrap)
                    : await upsertMemberRestriction(member, bootstrap);

            const removed = mode === 'remove' || Boolean(data && data.restriction && data.restriction.removed);
            updateMemberLocal(member.id, (curr) => {
                if (removed) {
                    const cleared = { ...curr, is_restricted: false };
                    SCOPE_OPTIONS.forEach((s) => {
                        cleared[s.key] = false;
                    });
                    return cleared;
                }
                const updated = (data && data.restriction) || {};
                const next = { ...curr, is_restricted: Boolean(updated.active ?? true) };
                SCOPE_OPTIONS.forEach((s) => {
                    if (Object.prototype.hasOwnProperty.call(updated, s.key)) {
                        next[s.key] = Boolean(updated[s.key]);
                    }
                });
                return next;
            });

            toastMessage(removed ? 'Restriction removed' : 'Restriction saved silently', 'success');
        } catch (err) {
            setErrorMsg((err && err.message) || 'Unable to save restriction changes.');
        } finally {
            setSavingId(null);
        }
    };

    const handleQuickToggle = async (member) => {
        if (!member || !member.id || savingId === member.id) return;
        const willRestrict = !member.is_restricted;
        const draft = willRestrict
            ? {
                  ...member,
                  restrict_posts: true,
                  restrict_comments: true,
                  restrict_reels: true,
                  restrict_events: true,
                  restrict_journals: true,
                  restrict_announcements: true,
                  restrict_capsules: true,
                  restrict_profile: false,
              }
            : member;

        if (willRestrict) {
            updateMemberLocal(member.id, () => draft);
        }
        await persistMember(draft, willRestrict ? 'update' : 'remove');
    };

    return (
        <div id="memberRestrictionCard" className="mt-5 pt-4 border-top" style={{ borderColor: 'var(--as-border)' }}>
            <div className="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div style={{ flex: '1 1 280px' }}>
                    <h6 className="fw-bold mb-1" style={{ color: 'var(--as-ink)' }}>
                        <i className="bi bi-person-slash me-2" style={{ color: '#a31621' }} aria-hidden="true" />
                        Restricted Family Members
                    </h6>
                    <p className="small mb-0" style={{ color: 'var(--as-muted)', lineHeight: 1.5 }}>
                        Choose who cannot see your posts, comments, video reels, events, family journals, announcements, time capsules, or receive notifications and emails from you. Restricted members are never notified.
                    </p>
                </div>
                <span className={`badge rounded-pill px-3 py-2 fw-semibold ${styles.restrictionCountBadge}`}>
                    {restrictedCount} Restricted
                </span>
            </div>

            <div className="d-flex flex-wrap align-items-center gap-2 mb-3">
                <div className="flex-grow-1" style={{ minWidth: '220px' }}>
                    <label htmlFor="restrictMemberSearch" className="visually-hidden">
                        Search family members to restrict
                    </label>
                    <input
                        type="search"
                        id="restrictMemberSearch"
                        className={`form-control ${styles.formInput}`}
                        placeholder="Search by name or family code..."
                        value={searchQuery}
                        onInput={(e) => setSearchQuery(e.currentTarget.value.trimStart())}
                    />
                </div>
                <div className="d-flex align-items-center gap-2 overflow-x-auto">
                    <button
                        type="button"
                        className={`btn btn-sm rounded-pill px-3 ${styles.actionBtn} ${
                            filterMode === 'all' ? 'text-white' : 'btn-outline-secondary'
                        }`}
                        style={
                            filterMode === 'all'
                                ? { backgroundColor: '#a31621', borderColor: '#a31621', color: '#ffffff' }
                                : undefined
                        }
                        onClick={() => setFilterMode('all')}
                    >
                        All Members ({members.length})
                    </button>
                    <button
                        type="button"
                        className={`btn btn-sm rounded-pill px-3 ${styles.actionBtn} ${
                            filterMode === 'restricted' ? 'text-white' : 'btn-outline-secondary'
                        }`}
                        style={
                            filterMode === 'restricted'
                                ? { backgroundColor: '#a31621', borderColor: '#a31621', color: '#ffffff' }
                                : undefined
                        }
                        onClick={() => setFilterMode('restricted')}
                    >
                        Restricted ({restrictedCount})
                    </button>
                </div>
            </div>

            {errorMsg && (
                <div className="alert alert-warning small rounded-3 py-2 px-3 mb-3" role="alert">
                    {errorMsg}
                </div>
            )}

            {members.length === 0 ? (
                <div className={`p-4 text-center ${styles.subcard}`}>
                    <i className="bi bi-people fs-3 d-block mb-2" style={{ color: 'var(--as-muted)' }} aria-hidden="true" />
                    <div className="fw-semibold mb-1" style={{ color: 'var(--as-ink)' }}>
                        No connected family members found yet
                    </div>
                    <p className="small mb-3" style={{ color: 'var(--as-muted)' }}>
                        Once relatives join your family code or accept a connection request, you can manage their visibility here.
                    </p>
                    <a
                        href="/allMembers"
                        className={`btn btn-outline-secondary btn-sm rounded-pill px-4 ${styles.actionBtn}`}
                    >
                        Browse Family Directory
                    </a>
                </div>
            ) : filteredMembers.length === 0 ? (
                <div className={`p-4 text-center ${styles.subcard}`}>
                    <div className="fw-semibold mb-1" style={{ color: 'var(--as-ink)' }}>
                        No matching members
                    </div>
                    <p className="small mb-2" style={{ color: 'var(--as-muted)' }}>
                        Try clearing your search or switching back to All Members.
                    </p>
                    <button
                        type="button"
                        className={`btn btn-outline-secondary btn-sm rounded-pill px-3 ${styles.actionBtn}`}
                        onClick={() => {
                            setSearchQuery('');
                            setFilterMode('all');
                        }}
                    >
                        Reset Filter
                    </button>
                </div>
            ) : (
                <div className="d-flex flex-column gap-2">
                    {visibleMembers.map((member) => {
                        const isExpanded = expandedId === member.id;
                        const isSaving = savingId === member.id;

                        return (
                            <div
                                key={member.id}
                                className={`p-3 ${styles.restrictionMemberCard} ${
                                    member.is_restricted ? styles.restrictionMemberCardRestricted : ''
                                }`}
                            >
                                <div className="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div className="d-flex align-items-center gap-3" style={{ minWidth: 0, flex: '1 1 220px' }}>
                                        <img
                                            src={member.avatar || '/resources/images/profile/avatarM.png'}
                                            alt={member.fullName || 'Family Member'}
                                            className="rounded-circle flex-shrink-0"
                                            width="44"
                                            height="44"
                                            style={{ width: '44px', height: '44px', objectFit: 'cover' }}
                                            onError={(e) => {
                                                e.currentTarget.src = '/resources/images/profile/avatarM.png';
                                            }}
                                        />
                                        <div style={{ minWidth: 0 }}>
                                            <div
                                                className={`fw-bold ${styles.memberNameClamp}`}
                                                title={member.fullName || 'Family Member'}
                                            >
                                                {member.fullName || 'Family Member'}
                                            </div>
                                            <div className="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                {member.famCode && (
                                                    <span
                                                        className="badge border"
                                                        style={{
                                                            fontSize: '0.72rem',
                                                            backgroundColor: 'var(--as-surface)',
                                                            color: 'var(--as-muted)',
                                                            borderColor: 'var(--as-border)',
                                                        }}
                                                    >
                                                        #{member.famCode}
                                                    </span>
                                                )}
                                                {member.is_restricted && (
                                                    <span
                                                        className="badge rounded-pill"
                                                        style={{
                                                            backgroundColor: '#991b1b',
                                                            color: '#ffffff',
                                                            fontSize: '0.72rem',
                                                        }}
                                                    >
                                                        Restricted
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="d-flex flex-wrap align-items-center gap-2">
                                        <button
                                            type="button"
                                            className={`btn btn-sm btn-outline-secondary rounded-pill px-3 ${styles.actionBtn}`}
                                            aria-expanded={isExpanded}
                                            onClick={() => setExpandedId(isExpanded ? null : member.id)}
                                        >
                                            <i className="bi bi-sliders" aria-hidden="true" />
                                            <span>{isExpanded ? 'Hide Options' : 'Customise'}</span>
                                        </button>

                                        <button
                                            type="button"
                                            className={`btn btn-sm rounded-pill px-3 fw-semibold ${styles.actionBtn} ${
                                                member.is_restricted ? 'btn-outline-secondary' : 'text-white'
                                            }`}
                                            style={
                                                !member.is_restricted
                                                    ? {
                                                          backgroundColor: '#a31621',
                                                          borderColor: '#a31621',
                                                          color: '#ffffff',
                                                      }
                                                    : undefined
                                            }
                                            disabled={isSaving}
                                            onClick={() => handleQuickToggle(member)}
                                        >
                                            {isSaving && (
                                                <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                            )}
                                            <span>{member.is_restricted ? 'Unrestrict' : 'Restrict All'}</span>
                                        </button>
                                    </div>
                                </div>

                                {isExpanded && (
                                    <div className="mt-3 pt-3 border-top" style={{ borderColor: 'var(--as-border)' }}>
                                        <div className="row g-2 mb-3">
                                            {SCOPE_OPTIONS.map((scope) => (
                                                <div className="col-12 col-sm-6 col-md-4" key={scope.key}>
                                                    <label className={`d-flex align-items-center gap-2 p-2 rounded h-100 ${styles.restrictionScopeTile}`}>
                                                        <input
                                                            type="checkbox"
                                                            className="form-check-input mt-0"
                                                            style={{ width: '20px', height: '20px' }}
                                                            checked={Boolean(member[scope.key])}
                                                            onChange={(e) => {
                                                                const val = e.currentTarget.checked;
                                                                updateMemberLocal(member.id, (curr) => ({
                                                                    ...curr,
                                                                    [scope.key]: val,
                                                                }));
                                                            }}
                                                        />
                                                        <span className="small fw-medium">{scope.label}</span>
                                                    </label>
                                                </div>
                                            ))}
                                        </div>

                                        <div className="mb-3">
                                            <label className={styles.formLabel} htmlFor={`restrict_note_${member.id}`}>
                                                Private Note (never shared with this member)
                                            </label>
                                            <input
                                                id={`restrict_note_${member.id}`}
                                                type="text"
                                                className={`form-control form-control-sm ${styles.formInput}`}
                                                maxLength={255}
                                                placeholder="Optional reminder for yourself..."
                                                value={member.note || ''}
                                                onInput={(e) => {
                                                    const val = e.currentTarget.value;
                                                    updateMemberLocal(member.id, (curr) => ({ ...curr, note: val }));
                                                }}
                                            />
                                        </div>

                                        <div className="d-flex flex-wrap justify-content-end gap-2">
                                            <button
                                                type="button"
                                                className={styles.btnPrimaryBrand}
                                                disabled={isSaving}
                                                onClick={() => persistMember(member, 'update')}
                                            >
                                                {isSaving && (
                                                    <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                                )}
                                                <span>Save Preferences</span>
                                            </button>
                                        </div>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
            )}

            {!searchQuery.trim() && filterMode === 'all' && filteredMembers.length > INITIAL_LIMIT && (
                <div className="mt-3 text-center">
                    <button
                        type="button"
                        className={`btn btn-sm btn-outline-secondary rounded-pill px-4 ${styles.actionBtn}`}
                        onClick={() => setShowAllMembers((prev) => !prev)}
                    >
                        {showAllMembers ? 'Show fewer members' : `Show all (${filteredMembers.length}) members`}
                    </button>
                </div>
            )}
        </div>
    );
}
