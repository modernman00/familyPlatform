import { useEffect, useState } from 'preact/hooks';
import {
    extractFamilyCode,
    postAccountForm,
    toastMessage,
} from '../api/accountSettingsApi';
import { CsrfFields } from '../components/SettingsControls';
import styles from '../AccountSettingsShell.module.css';

async function confirmDialog({ title, text, html, icon = 'question', confirmButtonText = 'Confirm', cancelButtonText = 'Cancel', confirmButtonColor = '#a31621' }) {
    if (typeof window !== 'undefined' && window.Swal) {
        const res = await window.Swal.fire({
            title,
            text,
            html,
            icon,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText,
            confirmButtonColor,
        });
        return Boolean(res.isConfirmed);
    }
    return typeof window !== 'undefined' ? window.confirm(text || title) : true;
}

async function copyText(text) {
    if (!text) return false;
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(text);
            return true;
        }
    } catch {
        // Fallback below
    }
    try {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        return true;
    } catch {
        return false;
    }
}

export function FamilyCodeTab({
    accountData,
    pendingFamilyRequest,
    birthFamily,
    codeRename,
    csrfToken,
    bootstrap,
    onRefreshState,
    onSavedAccountData,
}) {
    const [copiedCurrent, setCopiedCurrent] = useState(false);
    const [copiedBirth, setCopiedBirth] = useState(false);
    const [famAlert, setFamAlert] = useState('');

    // RFC-0005 rename vote state
    const [newFamilyCode, setNewFamilyCode] = useState('');
    const [renameAlert, setRenameAlert] = useState('');
    const [renameBusy, setRenameBusy] = useState(false);

    // RFC-0004 birth family state
    const defaultMaiden = accountData.maiden_name || accountData.mother_maiden || '';
    const [birthMaidenName, setBirthMaidenName] = useState(defaultMaiden);
    const [birthLinkCode, setBirthLinkCode] = useState('');
    const [birthInviterFirst, setBirthInviterFirst] = useState('');
    const [birthInviterLast, setBirthInviterLast] = useState('');
    const [birthInviterContact, setBirthInviterContact] = useState('');
    const [birthAlert, setBirthAlert] = useState('');
    const [birthBusy, setBirthBusy] = useState(false);

    // Solo / Join state
    const [soloSurname, setSoloSurname] = useState(accountData.lastName || '');
    const [soloBusy, setSoloBusy] = useState(false);
    const [targetFamCode, setTargetFamCode] = useState('');
    const [inviterFirstName, setInviterFirstName] = useState('');
    const [inviterLastName, setInviterLastName] = useState('');
    const [inviterContact, setInviterContact] = useState('');
    const [joinBusy, setJoinBusy] = useState(false);
    const [cancelReqBusy, setCancelReqBusy] = useState(false);

    useEffect(() => {
        if (!birthMaidenName && defaultMaiden) {
            setBirthMaidenName(defaultMaiden);
        }
    }, [defaultMaiden]);

    useEffect(() => {
        if (!soloSurname && accountData.lastName) {
            setSoloSurname(accountData.lastName);
        }
    }, [accountData.lastName]);

    const currentFamCode = accountData.famCode || 'NOT ASSIGNED';
    const hasPendingJoin = Boolean(
        pendingFamilyRequest && (pendingFamilyRequest.purpose || 'join') !== 'birth_link'
    );

    const bf = birthFamily || { state: 'none', code: '', origin: null, pending: null };
    const bfState = bf.state || 'none';

    const cr = codeRename || { is_creator: false, can_propose: false, code: '', open: null, cooldown: false };
    const crOpen = cr.open || null;

    const handleCopyCurrent = async () => {
        const code = (accountData.famCode || '').trim();
        if (!code || !(await copyText(code))) return;
        setCopiedCurrent(true);
        setTimeout(() => setCopiedCurrent(false), 2400);
        toastMessage(`Family Code copied: ${code}`, 'success');
    };

    const handleCopyBirth = async () => {
        const code = (bf.code || '').trim();
        if (!code || !(await copyText(code))) return;
        setCopiedBirth(true);
        setTimeout(() => setCopiedBirth(false), 1800);
        toastMessage(`Birth Family Code copied: #${code}`, 'success');
    };

    const handleProposeRename = async () => {
        if (renameBusy) return;
        setRenameAlert('');
        const code = newFamilyCode.trim();
        if (!code) {
            setRenameAlert('Please enter the new family code.');
            return;
        }
        setRenameBusy(true);
        try {
            const res = await postAccountForm(
                { action: 'proposeFamilyCodeRename', new_code: code },
                bootstrap
            );
            if (res.ok) {
                setNewFamilyCode('');
                toastMessage(res.message || 'Family code vote opened.', 'success');
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setRenameAlert(res.message || 'Could not start the family vote.');
            }
        } catch (err) {
            setRenameAlert((err && err.message) || 'Network error. Please try again.');
        } finally {
            setRenameBusy(false);
        }
    };

    const handleCancelRename = async () => {
        if (renameBusy) return;
        setRenameAlert('');
        setRenameBusy(true);
        try {
            const res = await postAccountForm({ action: 'cancelFamilyCodeRename' }, bootstrap);
            if (res.ok) {
                toastMessage(res.message || 'The family vote was cancelled.', 'info');
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setRenameAlert(res.message || 'Could not cancel the vote.');
            }
        } catch (err) {
            setRenameAlert((err && err.message) || 'Network error. Please try again.');
        } finally {
            setRenameBusy(false);
        }
    };

    const handleCreateBirthCode = async () => {
        if (birthBusy) return;
        setBirthAlert('');
        const maiden = birthMaidenName.trim();
        if (maiden.length < 2) {
            setBirthAlert('Enter your maiden (birth family) name first.');
            return;
        }
        setBirthBusy(true);
        try {
            const res = await postAccountForm(
                { action: 'createBirthFamilyCode', maiden_name: maiden },
                bootstrap
            );
            if (res.ok) {
                toastMessage(res.message || 'Birth family code created.', 'success');
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setBirthAlert(res.message || 'Could not create birth family code.');
            }
        } catch (err) {
            setBirthAlert((err && err.message) || 'Network error. Please try again.');
        } finally {
            setBirthBusy(false);
        }
    };

    const handleLinkBirthCode = async () => {
        if (birthBusy) return;
        setBirthAlert('');
        const cleanCode = birthLinkCode.trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (
            !cleanCode ||
            !birthInviterFirst.trim() ||
            !birthInviterLast.trim() ||
            !birthInviterContact.trim()
        ) {
            setBirthAlert(
                'Fill in the family code and the name and contact of a family member who can confirm you.'
            );
            return;
        }
        setBirthBusy(true);
        try {
            const res = await postAccountForm(
                {
                    action: 'requestBirthFamilyLink',
                    family_code: cleanCode,
                    inviter_first_name: birthInviterFirst.trim(),
                    inviter_last_name: birthInviterLast.trim(),
                    inviter_email_or_mobile: birthInviterContact.trim(),
                },
                bootstrap
            );
            if (res.ok) {
                setBirthLinkCode('');
                setBirthInviterFirst('');
                setBirthInviterLast('');
                setBirthInviterContact('');
                toastMessage(res.message || 'Birth family link request sent.', 'success');
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setBirthAlert(res.message || 'Could not send link request.');
            }
        } catch (err) {
            setBirthAlert((err && err.message) || 'Network error. Please try again.');
        } finally {
            setBirthBusy(false);
        }
    };

    const handleClearBirthCode = async () => {
        if (birthBusy) return;
        const ok = await confirmDialog({
            title: 'Unlink birth family?',
            text: 'You will leave this family tree. The tree and its members are not deleted.',
            icon: 'warning',
            confirmButtonText: 'Yes, unlink',
            confirmButtonColor: '#dc2626',
        });
        if (!ok) return;

        setBirthBusy(true);
        setBirthAlert('');
        try {
            const res = await postAccountForm(
                { action: 'updateSecondaryFamilyCode', otherFamCode: '' },
                bootstrap
            );
            if (res.ok) {
                toastMessage(res.message || 'Birth family unlinked.', 'info');
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setBirthAlert(res.message || 'Could not unlink birth family.');
            }
        } catch (err) {
            setBirthAlert((err && err.message) || 'Network error. Please try again.');
        } finally {
            setBirthBusy(false);
        }
    };

    const handleCancelBirthLink = async () => {
        if (birthBusy) return;
        const ok = await confirmDialog({
            title: 'Cancel request?',
            text: 'Cancel your pending request to link a birth family?',
            icon: 'warning',
            confirmButtonText: 'Yes, cancel',
            confirmButtonColor: '#dc2626',
        });
        if (!ok) return;

        setBirthBusy(true);
        setBirthAlert('');
        try {
            const res = await postAccountForm({ action: 'cancelFamilyRequest' }, bootstrap);
            if (res.ok) {
                toastMessage(res.message || 'Pending birth link request cancelled.', 'info');
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setBirthAlert(res.message || 'Could not cancel request.');
            }
        } catch (err) {
            setBirthAlert((err && err.message) || 'Network error. Please try again.');
        } finally {
            setBirthBusy(false);
        }
    };

    const handleStaySolo = async () => {
        if (soloBusy) return;
        const confirmed = await confirmDialog({
            title: 'Branch Out to Solo Family?',
            html: 'This will generate a brand-new unique Family Code for you.<br><br>You will become the family administrator of your own lineage with a fresh family tree. Are you sure you want to proceed?',
            icon: 'question',
            confirmButtonText: 'Yes, Branch Out',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#a31621',
        });
        if (!confirmed) return;

        setSoloBusy(true);
        setFamAlert('');
        try {
            const res = await postAccountForm(
                { action: 'staySoloCode', surname: soloSurname.trim() },
                bootstrap
            );
            if (res.ok) {
                const payload = res.data || {};
                const newCode =
                    (payload.data && payload.data.family_code) ||
                    (payload.token && payload.token.family_code) ||
                    extractFamilyCode(payload, 'Updated');
                onSavedAccountData({ famCode: newCode });
                if (typeof window !== 'undefined' && window.Swal) {
                    await window.Swal.fire({
                        icon: 'success',
                        title: 'Branched Out Successfully!',
                        html: `Your new solo Family Code is <strong>${newCode}</strong>.<br><br>Your profile has been transitioned to your new private family tree.`,
                        confirmButtonColor: '#a31621',
                    });
                } else {
                    toastMessage(`New solo Family Code: ${newCode}`, 'success');
                }
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setFamAlert(res.message || 'Could not generate solo family code.');
            }
        } catch (err) {
            setFamAlert((err && err.message) || 'Failed to process request. Please try again.');
        } finally {
            setSoloBusy(false);
        }
    };

    const handleRequestJoin = async () => {
        if (joinBusy) return;
        const cleanCode = targetFamCode.trim();
        const fName = inviterFirstName.trim();
        const lName = inviterLastName.trim();
        const contact = inviterContact.trim();

        if (!cleanCode || !fName || !lName || !contact) {
            if (typeof window !== 'undefined' && window.Swal) {
                window.Swal.fire({
                    icon: 'warning',
                    title: 'Missing Required Fields',
                    text: 'Please fill in target family code and all inviter details.',
                    confirmButtonColor: '#10b981',
                });
            } else {
                setFamAlert('Please fill in target family code and all inviter details.');
            }
            return;
        }

        setJoinBusy(true);
        setFamAlert('');
        try {
            const res = await postAccountForm(
                {
                    action: 'requestJoinFamily',
                    family_code: cleanCode,
                    inviter_first_name: fName,
                    inviter_last_name: lName,
                    inviter_email_or_mobile: contact,
                    id: accountData.id || '',
                },
                bootstrap
            );
            if (res.ok) {
                setTargetFamCode('');
                setInviterFirstName('');
                setInviterLastName('');
                setInviterContact('');
                if (typeof window !== 'undefined' && window.Swal) {
                    await window.Swal.fire({
                        icon: 'success',
                        title: 'Transfer Request Sent!',
                        html: `${res.message || 'Transfer request submitted successfully.'}<br><br><span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> Awaiting Inviter Approval</span>`,
                        confirmButtonColor: '#10b981',
                    });
                } else {
                    toastMessage(res.message || 'Transfer request sent!', 'success');
                }
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setFamAlert(res.message || 'Could not submit transfer request.');
            }
        } catch (err) {
            setFamAlert((err && err.message) || 'Failed to process request. Please try again.');
        } finally {
            setJoinBusy(false);
        }
    };

    const handleCancelPendingJoin = async () => {
        if (cancelReqBusy) return;
        const ok = await confirmDialog({
            title: 'Cancel Request?',
            text: 'Are you sure you want to cancel your pending family transfer request?',
            icon: 'question',
            confirmButtonText: 'Yes, Cancel Request',
            cancelButtonText: 'Keep Request',
            confirmButtonColor: '#dc2626',
        });
        if (!ok) return;

        setCancelReqBusy(true);
        try {
            const res = await postAccountForm({ action: 'cancelFamilyRequest' }, bootstrap);
            if (res.ok) {
                toastMessage('Transfer request cancelled.', 'info');
                if (typeof onRefreshState === 'function') await onRefreshState();
            } else {
                setFamAlert(res.message || 'Could not cancel request.');
            }
        } catch (err) {
            setFamAlert((err && err.message) || 'Could not cancel request.');
        } finally {
            setCancelReqBusy(false);
        }
    };

    const soloPreviewPrefix = ((accountData.lastName || 'OLA').slice(0, 3) || 'OLA').toUpperCase();

    return (
        <div>
            <div className={styles.sectionTitle}>Family Network &amp; Code Management</div>
            <div className={styles.sectionSubtitle}>
                Manage your active family code, branch out into your own private family space, or join another family network.
            </div>
            <div className={styles.sectionDivider} />

            {famAlert && (
                <div className="alert alert-warning shadow-sm border-0 rounded-3 mb-3" id="famCodeAlert" role="alert">
                    <div className="d-flex align-items-center">
                        <i className="bi bi-exclamation-octagon-fill fs-4 me-3" aria-hidden="true" />
                        <p id="famCodeAlertMessage" className="mb-0">
                            {famAlert}
                        </p>
                    </div>
                </div>
            )}

            {hasPendingJoin && (
                <div className={`p-3 mb-4 ${styles.pendingAlertBox}`} id="pendingRequestAlert">
                    <div className="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 className="fw-bold mb-1">
                                <i className="bi bi-hourglass-split me-1 text-warning" aria-hidden="true" /> Pending Family Transfer Request
                            </h6>
                            <p className="small mb-0">
                                You requested to join family code{' '}
                                <strong className="badge bg-warning text-dark">
                                    {pendingFamilyRequest.family_code}
                                </strong>
                                . Awaiting approval from{' '}
                                <strong>
                                    {pendingFamilyRequest.inviter_first_name} {pendingFamilyRequest.inviter_last_name}
                                </strong>
                                .
                            </p>
                        </div>
                        <button
                            type="button"
                            id="cancelRequestBtn"
                            className={`btn btn-sm btn-outline-danger rounded-pill px-3 ${styles.actionBtn}`}
                            disabled={cancelReqBusy}
                            onClick={handleCancelPendingJoin}
                        >
                            {cancelReqBusy ? (
                                <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                            ) : (
                                <i className="bi bi-x-circle" aria-hidden="true" />
                            )}
                            <span>Cancel Request</span>
                        </button>
                    </div>
                </div>
            )}

            {/* Current Family Code Card */}
            <div className={`card shadow-sm mb-4 ${styles.subcard}`}>
                <div className="card-body p-4">
                    <div className="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <span
                                className="text-uppercase fw-bold small"
                                style={{ letterSpacing: '0.05em', color: 'var(--as-muted)' }}
                            >
                                Your Current Family Code
                            </span>
                            <h3
                                className="fw-bolder mb-1 mt-1"
                                id="currentFamCodeText"
                                style={{ letterSpacing: '1px', color: 'var(--as-ink)' }}
                            >
                                <i className="bi bi-hash me-1" style={{ color: 'var(--as-muted)' }} aria-hidden="true" />
                                <span id="currentFamCodeValue">{currentFamCode}</span>
                            </h3>
                            {hasPendingJoin && (
                                <div className="mt-2 mb-2" id="currentFamCodePendingContainer">
                                    <span className="badge rounded-pill px-3 py-2 fw-semibold bg-warning text-dark">
                                        <i className="bi bi-hourglass-split me-1" aria-hidden="true" /> Transfer to{' '}
                                        <strong>#{pendingFamilyRequest.family_code}</strong> pending approval
                                    </span>
                                </div>
                            )}
                            <p className="small mb-0" style={{ color: 'var(--as-muted)' }}>
                                This code links your profile, personal memories, family tree, and shared feed with other verified family members.
                            </p>
                        </div>
                        <div>
                            <button
                                type="button"
                                id="copyCurrentFamCodeBtn"
                                className={`btn rounded-pill px-4 fw-semibold ${
                                    copiedCurrent ? 'btn-success' : 'btn-outline-primary'
                                } ${styles.actionBtn}`}
                                data-code={accountData.famCode || ''}
                                onClick={handleCopyCurrent}
                            >
                                <i className={`bi ${copiedCurrent ? 'bi-check2' : 'bi-clipboard'}`} aria-hidden="true" />
                                <span>{copiedCurrent ? 'Copied!' : 'Copy Code'}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Change Family Code by Family Vote (RFC-0005) */}
            <div className={`card shadow-sm mb-4 ${styles.subcard}`} id="codeRenameCard">
                <div className="card-body p-4">
                    <div className="d-flex align-items-center gap-2 mb-1">
                        <i className="bi bi-ui-checks-grid fs-4 text-primary" aria-hidden="true" />
                        <h5 className="fw-bold mb-0" style={{ color: 'var(--as-ink)' }}>
                            Change Our Family Code
                        </h5>
                    </div>
                    <p className="small mb-3" style={{ color: 'var(--as-muted)' }}>
                        The creator of the code can propose a new one. The whole family votes, and if more vote yes than no it changes for everyone automatically.
                    </p>
                    {renameAlert && (
                        <div id="codeRenameAlert" className="alert alert-warning small py-2 mb-3" role="alert">
                            {renameAlert}
                        </div>
                    )}

                    {crOpen ? (
                        <div className={`p-3 mb-2 ${styles.subcard}`} id="codeRenameOpen">
                            <div className="fw-semibold mb-1" style={{ color: 'var(--as-ink)' }}>
                                Vote open: #{crOpen.old_code} &rarr; #{crOpen.new_code}
                            </div>
                            <div className="small mb-3" style={{ color: 'var(--as-muted)' }}>
                                So far {crOpen.yes || 0} yes and {crOpen.no || 0} no from {crOpen.eligible || 0} members.
                                It passes if more members vote yes than no (at least one besides you must vote) when voting closes on{' '}
                                {crOpen.expires_formatted || crOpen.expires_at}, or straight away if over half the family says yes.
                            </div>
                            <div className="d-flex flex-wrap gap-2">
                                <a
                                    className={styles.btnPrimaryBrand}
                                    style={{ textDecoration: 'none' }}
                                    href={`/profilePage?post_no=${crOpen.post_no}#post${crOpen.post_no}`}
                                >
                                    <i className="bi bi-box-arrow-up-right" aria-hidden="true" />
                                    <span>Go to the vote</span>
                                </a>
                                {String(crOpen.requested_by || '') === String(accountData.id || '') && (
                                    <button
                                        type="button"
                                        id="btnCancelCodeRename"
                                        className={`btn btn-outline-danger rounded-pill px-4 fw-semibold ${styles.actionBtn}`}
                                        disabled={renameBusy}
                                        onClick={handleCancelRename}
                                    >
                                        {renameBusy && <span className="spinner-border spinner-border-sm" aria-hidden="true" />}
                                        <span>Cancel vote</span>
                                    </button>
                                )}
                            </div>
                        </div>
                    ) : cr.can_propose ? (
                        <form id="codeRenameForm" onSubmit={(e) => { e.preventDefault(); handleProposeRename(); }}>
                            <CsrfFields token={csrfToken} />
                            <label className={styles.formLabel} htmlFor="newFamilyCode">
                                New family code
                            </label>
                            <input
                                type="text"
                                id="newFamilyCode"
                                className={`form-control mb-1 ${styles.formInput}`}
                                maxLength={12}
                                minLength={4}
                                placeholder="e.g. ADEYEMI"
                                autoComplete="off"
                                style={{ textTransform: 'uppercase' }}
                                value={newFamilyCode}
                                onInput={(e) => setNewFamilyCode(e.currentTarget.value)}
                            />
                            <div className={`${styles.formHelp} mb-3`}>
                                4 to 12 letters or numbers. Your family is asked to vote first; nothing changes until the vote passes.
                            </div>
                            <button
                                type="button"
                                id="btnProposeCodeRename"
                                className={`${styles.btnPrimaryBrand} w-100`}
                                disabled={renameBusy}
                                onClick={handleProposeRename}
                            >
                                {renameBusy ? (
                                    <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                ) : (
                                    <i className="bi bi-send" aria-hidden="true" />
                                )}
                                <span>Ask my family to vote</span>
                            </button>
                        </form>
                    ) : cr.is_creator && cr.cooldown ? (
                        <div className="alert alert-secondary small mb-0">
                            The family code was changed recently. You can propose another change in 30 days.
                        </div>
                    ) : (
                        <div className="alert alert-secondary small mb-0">
                            Only the person who created this family code can propose changing it. If they do, you will be asked to vote.
                        </div>
                    )}
                </div>
            </div>

            {/* Birth / Maiden Family Code Card (RFC-0004) */}
            <div className={`card shadow-sm mb-4 ${styles.subcard}`} id="birthFamilyCard" data-state={bfState}>
                <div className="card-body p-4">
                    <div className="d-flex align-items-center gap-3 mb-3 flex-wrap">
                        <div className={styles.famIconCircleCrimson}>
                            <i className="bi bi-diagram-3-fill fs-4" aria-hidden="true" />
                        </div>
                        <div className="flex-grow-1">
                            <div className="d-flex align-items-center gap-2 flex-wrap">
                                <h5 className="fw-bold mb-0" style={{ color: 'var(--as-ink)' }}>
                                    Birth / Maiden Family Code
                                </h5>
                                <span
                                    className="badge rounded-pill px-3 py-1 fw-semibold"
                                    style={{ backgroundColor: '#fbefef', color: '#8a1019', fontSize: '0.75rem' }}
                                >
                                    Dual Lineage
                                </span>
                            </div>
                            <span className="small" style={{ color: 'var(--as-muted)' }}>
                                Keep your birth family connected without changing your household code.
                            </span>
                        </div>
                        <div>
                            {bfState === 'active' ? (
                                <span className="badge rounded-pill px-3 py-2 fw-semibold bg-success text-white" id="birthCodeStatusBadge">
                                    <i className="bi bi-check2-circle me-1" aria-hidden="true" />
                                    {bf.origin === 'created' ? 'Created by you' : 'Linked & approved'}
                                </span>
                            ) : bfState === 'pending' ? (
                                <span className="badge rounded-pill px-3 py-2 fw-semibold bg-warning text-dark" id="birthCodeStatusBadge">
                                    <i className="bi bi-hourglass-split me-1" aria-hidden="true" /> Awaiting approval
                                </span>
                            ) : bfState === 'unverified' ? (
                                <span className="badge rounded-pill px-3 py-2 fw-semibold bg-danger text-white" id="birthCodeStatusBadge">
                                    <i className="bi bi-exclamation-triangle me-1" aria-hidden="true" /> Unverified
                                </span>
                            ) : (
                                <span className="badge rounded-pill px-3 py-2 fw-semibold bg-secondary text-white" id="birthCodeStatusBadge">
                                    <i className="bi bi-dash-circle me-1" aria-hidden="true" /> Not set up
                                </span>
                            )}
                        </div>
                    </div>

                    {birthAlert && (
                        <div id="birthFamilyAlert" className="alert alert-warning small py-2 mb-3" role="alert">
                            {birthAlert}
                        </div>
                    )}

                    {bfState === 'active' ? (
                        <>
                            <div className={`p-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3 ${styles.famCodeHeroBox}`}>
                                <div>
                                    <span
                                        className="text-uppercase fw-bold small"
                                        style={{ letterSpacing: '0.05em', color: 'var(--as-muted)' }}
                                    >
                                        Your birth family code
                                    </span>
                                    <h3 className={`fw-bolder mb-0 mt-1 ${styles.famCodeHeroValue}`}>
                                        #<span id="birthCodeValue">{bf.code}</span>
                                    </h3>
                                </div>
                                <div className="d-flex gap-2 flex-wrap">
                                    <button
                                        type="button"
                                        id="btnCopyBirthCode"
                                        className={`btn rounded-pill px-4 fw-semibold ${
                                            copiedBirth ? 'btn-success' : 'btn-outline-primary'
                                        } ${styles.actionBtn}`}
                                        data-code={bf.code}
                                        onClick={handleCopyBirth}
                                    >
                                        <i className={`bi ${copiedBirth ? 'bi-check2' : 'bi-clipboard'}`} aria-hidden="true" />
                                        <span>{copiedBirth ? 'Copied' : 'Copy Code'}</span>
                                    </button>
                                    {bf.origin === 'linked' && (
                                        <button
                                            type="button"
                                            id="btnClearBirthCode"
                                            className={`btn btn-outline-secondary rounded-pill px-3 fw-semibold ${styles.actionBtn}`}
                                            title="Unlink this birth family"
                                            disabled={birthBusy}
                                            onClick={handleClearBirthCode}
                                        >
                                            <i className="bi bi-x-circle" aria-hidden="true" />
                                            <span>Unlink</span>
                                        </button>
                                    )}
                                </div>
                            </div>
                            <p className="small mb-0" style={{ color: 'var(--as-muted)' }}>
                                Use <strong>My Birth / Maiden Family</strong> in the <em>Parents</em> and <em>Siblings</em> tabs to invite your family into this tree. Your household tree (<strong>{accountData.famCode || 'Primary'}</strong>) stays unchanged.
                                {bf.origin === 'created' ? ' This code is permanent once created.' : ''}
                            </p>
                        </>
                    ) : bfState === 'pending' ? (
                        <div className={`p-3 d-flex align-items-center justify-content-between flex-wrap gap-2 ${styles.pendingAlertBox}`}>
                            <p className="small mb-0">
                                You asked to link to <strong>#{bf.code}</strong>. Waiting for{' '}
                                <strong>
                                    {(bf.pending && bf.pending.inviter_first_name) || ''}{' '}
                                    {(bf.pending && bf.pending.inviter_last_name) || ''}
                                </strong>{' '}
                                to confirm.
                            </p>
                            <button
                                type="button"
                                id="cancelBirthLinkBtn"
                                className={`btn btn-sm btn-outline-danger rounded-pill px-3 ${styles.actionBtn}`}
                                disabled={birthBusy}
                                onClick={handleCancelBirthLink}
                            >
                                <i className="bi bi-x-circle" aria-hidden="true" />
                                <span>Cancel Request</span>
                            </button>
                        </div>
                    ) : (
                        <>
                            {bfState === 'unverified' && (
                                <div className="alert alert-warning small mb-3" id="birthUnverifiedNotice">
                                    <i className="bi bi-shield-exclamation me-1" aria-hidden="true" />
                                    The code <strong>#{bf.code}</strong> was saved before family codes were verified, so it is not used for invitations or tree changes. Create your birth family or ask a member of that family to confirm you below.
                                </div>
                            )}

                            <div className="row g-4">
                                <div className="col-md-6">
                                    <div className={`h-100 p-3 ${styles.subcard}`}>
                                        <h6 className="fw-bold mb-1" style={{ color: 'var(--as-ink)' }}>
                                            <i className="bi bi-stars me-1 text-primary" aria-hidden="true" /> My birth family isn&apos;t on yet
                                        </h6>
                                        <p className="small" style={{ color: 'var(--as-muted)' }}>
                                            We&apos;ll create a new family code from your maiden name. Your parents and siblings can join with it.
                                        </p>
                                        <form id="createBirthCodeForm" onSubmit={(e) => { e.preventDefault(); handleCreateBirthCode(); }}>
                                            <CsrfFields token={csrfToken} />
                                            <label className={styles.formLabel} htmlFor="birthMaidenName">
                                                Maiden / birth family name
                                            </label>
                                            <input
                                                type="text"
                                                id="birthMaidenName"
                                                name="maiden_name"
                                                className={`form-control mb-3 ${styles.formInput}`}
                                                maxLength={60}
                                                placeholder="e.g. Adeyemi"
                                                value={birthMaidenName}
                                                onInput={(e) => setBirthMaidenName(e.currentTarget.value)}
                                                autoComplete="off"
                                            />
                                            <button
                                                type="button"
                                                id="btnCreateBirthCode"
                                                className={`${styles.btnPrimaryBrand} w-100`}
                                                disabled={birthBusy}
                                                onClick={handleCreateBirthCode}
                                            >
                                                {birthBusy ? (
                                                    <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                                ) : (
                                                    <i className="bi bi-plus-circle" aria-hidden="true" />
                                                )}
                                                <span>Create Birth Family Code</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div className="col-md-6">
                                    <div className={`h-100 p-3 ${styles.subcard}`}>
                                        <h6 className="fw-bold mb-1" style={{ color: 'var(--as-ink)' }}>
                                            <i className="bi bi-link-45deg me-1 text-success" aria-hidden="true" /> My family is already here
                                        </h6>
                                        <p className="small" style={{ color: 'var(--as-muted)' }}>
                                            Enter their code and a family member who can confirm you. Your household code won&apos;t change.
                                        </p>
                                        <form id="linkBirthCodeForm" onSubmit={(e) => { e.preventDefault(); handleLinkBirthCode(); }}>
                                            <CsrfFields token={csrfToken} />
                                            <div className="input-group mb-2">
                                                <span className="input-group-text fw-bold">#</span>
                                                <input
                                                    type="text"
                                                    id="birthLinkCode"
                                                    name="family_code"
                                                    className={`form-control text-uppercase fw-bold ${styles.formInput}`}
                                                    maxLength={12}
                                                    placeholder="e.g. ADE123"
                                                    aria-label="Birth family code"
                                                    value={birthLinkCode}
                                                    onInput={(e) => setBirthLinkCode(e.currentTarget.value)}
                                                    autoComplete="off"
                                                />
                                            </div>
                                            <div className="row g-2 mb-2">
                                                <div className="col-6">
                                                    <input
                                                        type="text"
                                                        id="birthInviterFirst"
                                                        name="inviter_first_name"
                                                        className={`form-control ${styles.formInput}`}
                                                        placeholder="Their first name"
                                                        aria-label="Inviter first name"
                                                        maxLength={100}
                                                        value={birthInviterFirst}
                                                        onInput={(e) => setBirthInviterFirst(e.currentTarget.value)}
                                                    />
                                                </div>
                                                <div className="col-6">
                                                    <input
                                                        type="text"
                                                        id="birthInviterLast"
                                                        name="inviter_last_name"
                                                        className={`form-control ${styles.formInput}`}
                                                        placeholder="Their last name"
                                                        aria-label="Inviter last name"
                                                        maxLength={100}
                                                        value={birthInviterLast}
                                                        onInput={(e) => setBirthInviterLast(e.currentTarget.value)}
                                                    />
                                                </div>
                                            </div>
                                            <input
                                                type="text"
                                                id="birthInviterContact"
                                                name="inviter_email_or_mobile"
                                                className={`form-control mb-3 ${styles.formInput}`}
                                                placeholder="Their email or mobile"
                                                aria-label="Inviter email or mobile"
                                                maxLength={100}
                                                value={birthInviterContact}
                                                onInput={(e) => setBirthInviterContact(e.currentTarget.value)}
                                            />
                                            <button
                                                type="button"
                                                id="btnRequestBirthLink"
                                                className={`btn btn-success w-100 rounded-pill fw-semibold ${styles.actionBtn}`}
                                                disabled={birthBusy}
                                                onClick={handleLinkBirthCode}
                                            >
                                                {birthBusy ? (
                                                    <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                                ) : (
                                                    <i className="bi bi-send" aria-hidden="true" />
                                                )}
                                                <span>Request to Link</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </div>

            {/* Branch Out Solo vs Join Another Family Network */}
            <div className="row g-4">
                <div className="col-md-6">
                    <div className={`card h-100 shadow-sm ${styles.subcard}`}>
                        <div className="card-body p-4 d-flex flex-column">
                            <div className="d-flex align-items-center gap-3 mb-3">
                                <div className={styles.famIconCircleCrimson}>
                                    <i className="bi bi-tree-fill fs-4" aria-hidden="true" />
                                </div>
                                <div>
                                    <h5 className="fw-bold mb-0" style={{ color: 'var(--as-ink)' }}>
                                        Start Your Own Family Space
                                    </h5>
                                    <span className="badge bg-secondary text-white">Branch Out / Solo</span>
                                </div>
                            </div>

                            <p className="small mb-4 flex-grow-1" style={{ color: 'var(--as-muted)' }}>
                                Want to establish an independent household tree or branch out on your own? Generating a new code provisions a fresh private family lineage where you are the administrator.
                            </p>

                            <form id="staySoloForm" onSubmit={(e) => { e.preventDefault(); handleStaySolo(); }}>
                                <CsrfFields token={csrfToken} />
                                <div className="mb-3">
                                    <label className={styles.formLabel} htmlFor="soloSurname">
                                        Family Surname (Optional)
                                    </label>
                                    <input
                                        type="text"
                                        name="surname"
                                        id="soloSurname"
                                        className={`form-control ${styles.formInput}`}
                                        placeholder={accountData.lastName || 'Surname'}
                                        value={soloSurname}
                                        onInput={(e) => setSoloSurname(e.currentTarget.value)}
                                    />
                                    <div className={styles.formHelp}>
                                        Will create your 6-character code (e.g. {soloPreviewPrefix}345).
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    id="btnStaySolo"
                                    className={`${styles.btnPrimaryBrand} w-100`}
                                    disabled={soloBusy}
                                    onClick={handleStaySolo}
                                >
                                    {soloBusy ? (
                                        <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                    ) : (
                                        <i className="bi bi-stars" aria-hidden="true" />
                                    )}
                                    <span>Generate New Solo Code (6-char)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div className="col-md-6">
                    <div className={`card h-100 shadow-sm ${styles.subcard}`}>
                        <div className="card-body p-4 d-flex flex-column">
                            <div className="d-flex align-items-center gap-3 mb-3">
                                <div className={styles.famIconCircleGreen}>
                                    <i className="bi bi-people-fill fs-4" aria-hidden="true" />
                                </div>
                                <div>
                                    <h5 className="fw-bold mb-0" style={{ color: 'var(--as-ink)' }}>
                                        Join Another Family Network
                                    </h5>
                                    <span className="badge bg-success text-white">Transfer Request</span>
                                </div>
                            </div>

                            <p className="small mb-3" style={{ color: 'var(--as-muted)' }}>
                                Moving to an existing family network? Enter the family code and the details of a family member who can verify your relationship.
                            </p>

                            <form id="joinFamilyForm" onSubmit={(e) => { e.preventDefault(); handleRequestJoin(); }}>
                                <CsrfFields token={csrfToken} />
                                <div className="mb-2">
                                    <label className={styles.formLabel} htmlFor="targetFamCode">
                                        Target Family Code *
                                    </label>
                                    <input
                                        type="text"
                                        name="family_code"
                                        id="targetFamCode"
                                        className={`form-control text-uppercase fw-bold ${styles.formInput}`}
                                        placeholder="e.g. OLA345"
                                        maxLength={10}
                                        value={targetFamCode}
                                        onInput={(e) => setTargetFamCode(e.currentTarget.value)}
                                        required
                                    />
                                </div>

                                <div className="row g-2 mb-2">
                                    <div className="col-6">
                                        <label className={styles.formLabel} htmlFor="inviterFirstName">
                                            Inviter First Name *
                                        </label>
                                        <input
                                            type="text"
                                            name="inviter_first_name"
                                            id="inviterFirstName"
                                            className={`form-control ${styles.formInput}`}
                                            placeholder="First Name"
                                            value={inviterFirstName}
                                            onInput={(e) => setInviterFirstName(e.currentTarget.value)}
                                            required
                                        />
                                    </div>
                                    <div className="col-6">
                                        <label className={styles.formLabel} htmlFor="inviterLastName">
                                            Inviter Last Name *
                                        </label>
                                        <input
                                            type="text"
                                            name="inviter_last_name"
                                            id="inviterLastName"
                                            className={`form-control ${styles.formInput}`}
                                            placeholder="Last Name"
                                            value={inviterLastName}
                                            onInput={(e) => setInviterLastName(e.currentTarget.value)}
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="mb-3">
                                    <label className={styles.formLabel} htmlFor="inviterContact">
                                        Inviter Email or Mobile *
                                    </label>
                                    <input
                                        type="text"
                                        name="inviter_email_or_mobile"
                                        id="inviterContact"
                                        className={`form-control ${styles.formInput}`}
                                        placeholder="email@example.com or phone"
                                        value={inviterContact}
                                        onInput={(e) => setInviterContact(e.currentTarget.value)}
                                        required
                                    />
                                </div>

                                <button
                                    type="button"
                                    id="btnRequestJoin"
                                    className={`btn btn-success w-100 fw-semibold rounded-3 shadow-sm ${styles.actionBtn}`}
                                    disabled={joinBusy}
                                    onClick={handleRequestJoin}
                                >
                                    {joinBusy ? (
                                        <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                    ) : (
                                        <i className="bi bi-send-check" aria-hidden="true" />
                                    )}
                                    <span>Send Transfer Request</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
