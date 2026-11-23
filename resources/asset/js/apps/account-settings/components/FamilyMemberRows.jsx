import { useEffect, useRef, useState } from 'preact/hooks';
import { checkEmailExists, sendMemberEmailInvite, toastMessage } from '../api/accountSettingsApi';
import styles from '../AccountSettingsShell.module.css';

export function createEmptyFamilyRow() {
    return {
        option: 'select',
        firstName: '',
        lastName: '',
        gender: '',
        otherParent: '',
        unionStatus: 'divorced',
        email: '',
    };
}

function EmailLookupStatus({
    elementId,
    email,
    fullName,
    yourFirstName,
    inviteFamCode,
    bootstrap,
}) {
    const cacheRef = useRef(new Map());
    const [status, setStatus] = useState({ state: 'idle', message: '' });
    const [sending, setSending] = useState(false);

    useEffect(() => {
        const cleanEmail = (email || '').toLowerCase().trim();
        if (!cleanEmail) {
            setStatus({ state: 'idle', message: '' });
            return undefined;
        }
        if (cleanEmail.length < 7 || !cleanEmail.includes('@')) {
            setStatus({ state: 'short', message: 'Email may be too short or incomplete' });
            return undefined;
        }

        let cancelled = false;
        const timer = setTimeout(async () => {
            try {
                let exists;
                if (cacheRef.current.has(cleanEmail)) {
                    exists = cacheRef.current.get(cleanEmail);
                } else {
                    exists = await checkEmailExists(cleanEmail);
                    cacheRef.current.set(cleanEmail, exists);
                }
                if (cancelled) return;
                setStatus({
                    state: exists ? 'exists' : 'missing',
                    message: exists
                        ? `Great news! ${fullName || 'This person'} is already registered on the platform.`
                        : `${fullName || 'This person'} is not on the platform yet. Would you like us to send an email invitation?`,
                });
            } catch {
                if (!cancelled) {
                    setStatus({ state: 'idle', message: '' });
                }
            }
        }, 250);

        return () => {
            cancelled = true;
            clearTimeout(timer);
        };
    }, [email, fullName]);

    const handleSendInvite = async () => {
        if (sending || !email) return;
        setSending(true);
        try {
            const res = await sendMemberEmailInvite(
                {
                    email: email.trim().toLowerCase(),
                    name: fullName || email.trim(),
                    yourName: yourFirstName,
                    familyCode: inviteFamCode,
                },
                bootstrap
            );
            const msg = (res && res.message) || 'Invitation sent!';
            setStatus({ state: 'sent', message: msg });
            toastMessage(msg, 'success');
        } catch (err) {
            const errMsg = (err && err.message) || 'Could not send invitation right now.';
            setStatus({ state: 'error', message: errMsg });
        } finally {
            setSending(false);
        }
    };

    if (status.state === 'idle') return null;

    return (
        <div
            id={`${elementId}_help`}
            className="mt-2 small fw-semibold"
            style={{
                color: status.state === 'exists' || status.state === 'sent' ? '#15803d' : '#b91c1c',
            }}
        >
            <div>{status.message}</div>
            {status.state === 'missing' && (
                <div className="d-flex flex-wrap gap-2 mt-2">
                    <button
                        type="button"
                        id={`${elementId}Yes`}
                        className={`btn btn-sm btn-outline-primary rounded-pill px-3 ${styles.actionBtn}`}
                        disabled={sending}
                        onClick={handleSendInvite}
                    >
                        {sending && <span className="spinner-border spinner-border-sm" aria-hidden="true" />}
                        <span>Send Invite</span>
                    </button>
                    <button
                        type="button"
                        id={`${elementId}No`}
                        className={`btn btn-sm btn-outline-secondary rounded-pill px-3 ${styles.actionBtn}`}
                        disabled={sending}
                        onClick={() => setStatus({ state: 'idle', message: '' })}
                    >
                        No, thanks
                    </button>
                </div>
            )}
        </div>
    );
}

export function FamilyMemberRows({
    kind,
    count,
    rows,
    onChangeRow,
    yourFirstName,
    inviteFamCode,
    bootstrap,
}) {
    if (!count || count <= 0) {
        return null;
    }

    const prefix = kind === 'children' ? 'children' : 'sibling';
    const labelText = kind === 'children' ? 'Child' : 'Sibling';
    const relationLabel = kind === 'children' ? 'With Spouse?' : 'Relationship';

    const visibleRows = Array.from({ length: count }, (_, idx) => ({
        no: idx + 1,
        data: rows[idx] || createEmptyFamilyRow(),
    }));

    return (
        <div id={`${prefix}_inputs`} className="mt-3">
            {visibleRows.map(({ no, data }) => {
                const showOtherParent =
                    kind === 'sibling'
                        ? data.option === 'Same_Father' || data.option === 'Same_Mother'
                        : data.option === 'With Former Partner';
                const showUnionStatus = kind === 'children' && data.option === 'With Former Partner';
                const emailId = `${prefix}_email${no}`;
                const fullName = `${data.firstName || ''} ${data.lastName || ''}`.trim();

                return (
                    <div
                        key={no}
                        className={`p-4 mb-3 wrapper-${prefix}-${no} ${styles.subcard}`}
                    >
                        <h6
                            className="fw-bold mb-3 text-uppercase"
                            style={{ fontSize: '0.88rem', letterSpacing: '0.04em', color: 'var(--as-ink)' }}
                        >
                            <i
                                className={`bi ${
                                    kind === 'children'
                                        ? 'bi-person-heart text-primary'
                                        : 'bi-person-lines-fill text-primary'
                                } me-2`}
                                aria-hidden="true"
                            />
                            #{no} {labelText} Information
                        </h6>

                        <div className="row g-3">
                            <div className="col-md-4">
                                <label className={styles.formLabel} htmlFor={`${prefix}_option${no}`}>
                                    {relationLabel}
                                </label>
                                <select
                                    className={`form-select ${styles.formSelect}`}
                                    name={`${prefix}_option${no}`}
                                    id={`${prefix}_option${no}`}
                                    value={data.option}
                                    onChange={(e) => onChangeRow(no - 1, 'option', e.currentTarget.value)}
                                >
                                    {kind === 'children' ? (
                                        <>
                                            <option value="select" disabled>
                                                Select status
                                            </option>
                                            <option value="With Spouse">With Current Spouse</option>
                                            <option value="With Former Partner">
                                                With Former Partner (divorced / separated / late)
                                            </option>
                                            <option value="Not With Spouse">Single / Other</option>
                                        </>
                                    ) : (
                                        <>
                                            <option value="select" disabled>
                                                Select type
                                            </option>
                                            <option value="Same_Mother_Father">Same Father &amp; Mother</option>
                                            <option value="Same_Father">Same Father Only</option>
                                            <option value="Same_Mother">Same Mother Only</option>
                                            <option value="Step">Step-sibling</option>
                                            <option value="Adopted">Adopted</option>
                                        </>
                                    )}
                                </select>
                            </div>

                            <div className="col-md-4">
                                <label className={styles.formLabel} htmlFor={`${prefix}_first_name${no}`}>
                                    First Name
                                </label>
                                <input
                                    type="text"
                                    className={`form-control ${styles.formInput}`}
                                    placeholder="e.g. John"
                                    name={`${prefix}_first_name${no}`}
                                    id={`${prefix}_first_name${no}`}
                                    value={data.firstName}
                                    onInput={(e) => onChangeRow(no - 1, 'firstName', e.currentTarget.value)}
                                    autoComplete="off"
                                />
                            </div>

                            <div className="col-md-4">
                                <label className={styles.formLabel} htmlFor={`${prefix}_last_name${no}`}>
                                    Last Name
                                </label>
                                <input
                                    type="text"
                                    className={`form-control ${styles.formInput}`}
                                    placeholder="e.g. Doe"
                                    name={`${prefix}_last_name${no}`}
                                    id={`${prefix}_last_name${no}`}
                                    value={data.lastName}
                                    onInput={(e) => onChangeRow(no - 1, 'lastName', e.currentTarget.value)}
                                    autoComplete="off"
                                />
                            </div>

                            <div className="col-md-4">
                                <label className={styles.formLabel} htmlFor={`${prefix}_gender${no}`}>
                                    Gender
                                </label>
                                <select
                                    className={`form-select ${styles.formSelect}`}
                                    name={`${prefix}_gender${no}`}
                                    id={`${prefix}_gender${no}`}
                                    value={data.gender}
                                    onChange={(e) => onChangeRow(no - 1, 'gender', e.currentTarget.value)}
                                >
                                    <option value="">Select gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>

                            {showOtherParent && (
                                <div className="col-md-4" data-family-other-parent={`${prefix}${no}`}>
                                    <label className={styles.formLabel} htmlFor={`${prefix}_other_parent${no}`}>
                                        {kind === 'children'
                                            ? "Former partner's name"
                                            : "Their other parent's name (optional)"}
                                    </label>
                                    <input
                                        type="text"
                                        className={`form-control ${styles.formInput}`}
                                        placeholder="Full name"
                                        name={`${prefix}_other_parent${no}`}
                                        id={`${prefix}_other_parent${no}`}
                                        value={data.otherParent}
                                        onInput={(e) => onChangeRow(no - 1, 'otherParent', e.currentTarget.value)}
                                        autoComplete="off"
                                    />
                                </div>
                            )}

                            {showUnionStatus && (
                                <div className="col-md-4" data-family-union-status={`${prefix}${no}`}>
                                    <label className={styles.formLabel} htmlFor={`children_union_status${no}`}>
                                        Relationship status
                                    </label>
                                    <select
                                        className={`form-select ${styles.formSelect}`}
                                        name={`children_union_status${no}`}
                                        id={`children_union_status${no}`}
                                        value={data.unionStatus}
                                        onChange={(e) => onChangeRow(no - 1, 'unionStatus', e.currentTarget.value)}
                                    >
                                        <option value="divorced">Divorced</option>
                                        <option value="separated">Separated</option>
                                        <option value="widowed">Widowed / Late partner</option>
                                        <option value="partner">Never married</option>
                                    </select>
                                </div>
                            )}

                            <div className="col-12">
                                <label className={styles.formLabel} htmlFor={emailId}>
                                    Email Address
                                </label>
                                <input
                                    type="email"
                                    className={`form-control ${styles.formInput}`}
                                    placeholder="e.g. john.doe@example.com"
                                    name={emailId}
                                    id={emailId}
                                    value={data.email}
                                    onInput={(e) => onChangeRow(no - 1, 'email', e.currentTarget.value)}
                                    autoComplete="off"
                                />
                                <EmailLookupStatus
                                    elementId={emailId}
                                    email={data.email}
                                    fullName={fullName}
                                    yourFirstName={yourFirstName}
                                    inviteFamCode={inviteFamCode}
                                    bootstrap={bootstrap}
                                />
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
