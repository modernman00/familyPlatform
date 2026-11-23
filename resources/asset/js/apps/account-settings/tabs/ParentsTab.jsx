import { useEffect, useState } from 'preact/hooks';
import { postAccountForm, toastMessage } from '../api/accountSettingsApi';
import {
    CsrfFields,
    LineageScopePicker,
    StatusAlert,
    SubmitButton,
} from '../components/SettingsControls';
import styles from '../AccountSettingsShell.module.css';

export function ParentsTab({
    accountData,
    csrfToken,
    bootstrap,
    onSavedAccountData,
    onDirtyChange,
}) {
    const hasMaternalCode = Boolean(accountData.hasMaternalCode && accountData.otherFamCode);

    const [lineageScope, setLineageScope] = useState(hasMaternalCode ? 'maternal' : 'primary');
    const [fatherFirstName, setFatherFirstName] = useState(accountData.father_first_name || '');
    const [fatherLastName, setFatherLastName] = useState(accountData.father_last_name || '');
    const [fatherEmail, setFatherEmail] = useState(accountData.father_email || '');
    const [fatherMobile, setFatherMobile] = useState(accountData.father_mobile || '');
    const [motherFirstName, setMotherFirstName] = useState(accountData.mother_first_name || '');
    const [motherLastName, setMotherLastName] = useState(accountData.mother_last_name || '');
    const [motherMaiden, setMotherMaiden] = useState(accountData.maiden_name || accountData.mother_maiden || '');
    const [motherEmail, setMotherEmail] = useState(accountData.mother_email || '');
    const [motherMobile, setMotherMobile] = useState(accountData.mother_mobile || '');
    const [parentsStatus, setParentsStatus] = useState(accountData.parents_status || '');
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState({ type: 'info', message: '' });

    useEffect(() => {
        setLineageScope(accountData.hasMaternalCode && accountData.otherFamCode ? 'maternal' : 'primary');
        setFatherFirstName(accountData.father_first_name || '');
        setFatherLastName(accountData.father_last_name || '');
        setFatherEmail(accountData.father_email || '');
        setFatherMobile(accountData.father_mobile || '');
        setMotherFirstName(accountData.mother_first_name || '');
        setMotherLastName(accountData.mother_last_name || '');
        setMotherMaiden(accountData.maiden_name || accountData.mother_maiden || '');
        setMotherEmail(accountData.mother_email || '');
        setMotherMobile(accountData.mother_mobile || '');
        setParentsStatus(accountData.parents_status || '');
    }, [
        accountData.hasMaternalCode,
        accountData.otherFamCode,
        accountData.father_first_name,
        accountData.father_last_name,
        accountData.father_email,
        accountData.father_mobile,
        accountData.mother_first_name,
        accountData.mother_last_name,
        accountData.maiden_name,
        accountData.mother_maiden,
        accountData.mother_email,
        accountData.mother_mobile,
        accountData.parents_status,
    ]);

    const isDirty =
        fatherFirstName !== (accountData.father_first_name || '') ||
        fatherLastName !== (accountData.father_last_name || '') ||
        fatherEmail !== (accountData.father_email || '') ||
        fatherMobile !== (accountData.father_mobile || '') ||
        motherFirstName !== (accountData.mother_first_name || '') ||
        motherLastName !== (accountData.mother_last_name || '') ||
        motherMaiden !== (accountData.maiden_name || accountData.mother_maiden || '') ||
        motherEmail !== (accountData.mother_email || '') ||
        motherMobile !== (accountData.mother_mobile || '') ||
        parentsStatus !== (accountData.parents_status || '');

    useEffect(() => {
        if (typeof onDirtyChange === 'function') {
            onDirtyChange('parents', isDirty);
        }
    }, [isDirty, onDirtyChange]);

    const lineageCode = lineageScope === 'maternal' ? accountData.otherFamCode || '' : accountData.famCode || '';

    const handleSubmit = async (e) => {
        if (e && e.preventDefault) e.preventDefault();
        if (saving) return;

        setSaving(true);
        setStatus({ type: 'info', message: '' });

        try {
            const payload = {
                action: 'updateParents',
                lineage_scope: lineageScope,
                lineage_code: lineageCode,
                father_first_name: fatherFirstName.trim(),
                father_last_name: fatherLastName.trim(),
                father_email: fatherEmail.trim(),
                father_mobile: fatherMobile.trim(),
                mother_first_name: motherFirstName.trim(),
                mother_last_name: motherLastName.trim(),
                maiden_name: motherMaiden.trim(),
                mother_email: motherEmail.trim(),
                mother_mobile: motherMobile.trim(),
                parents_status: parentsStatus,
            };

            const res = await postAccountForm(payload, bootstrap);
            if (res.ok) {
                onSavedAccountData({
                    father_first_name: payload.father_first_name,
                    father_last_name: payload.father_last_name,
                    father_email: payload.father_email,
                    father_mobile: payload.father_mobile,
                    mother_first_name: payload.mother_first_name,
                    mother_last_name: payload.mother_last_name,
                    maiden_name: payload.maiden_name,
                    mother_maiden: payload.maiden_name,
                    mother_email: payload.mother_email,
                    mother_mobile: payload.mother_mobile,
                    parents_status: payload.parents_status,
                });
                if (typeof onDirtyChange === 'function') {
                    onDirtyChange('parents', false);
                }
                setStatus({ type: 'success', message: res.message || 'Parents information successfully updated.' });
                toastMessage(res.message || 'Parents information successfully updated.', 'success');
            } else {
                setStatus({ type: 'error', message: res.message || 'Could not save parents information.' });
            }
        } catch (err) {
            setStatus({
                type: 'error',
                message: (err && err.message) || 'Network error while saving parents information.',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <div>
            <div className={styles.sectionTitle} id="family-settings">
                Parents Information
            </div>
            <div className={styles.sectionSubtitle}>
                Configure your father and mother details for your family tree connections.
            </div>
            <div className={styles.sectionDivider} />

            <div className={`${styles.lineageBanner} d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3`}>
                <div className="d-flex align-items-center gap-3">
                    <i className="bi bi-stars fs-3" style={{ color: '#a31621' }} aria-hidden="true" />
                    <div>
                        <div className="fw-bold" style={{ color: 'var(--as-ink)' }}>
                            Dedicated Family Lineage Studio
                        </div>
                        <div className="small" style={{ color: 'var(--as-muted)' }}>
                            Use our interactive studio to manage generations, photos, and live tree connections.
                        </div>
                    </div>
                </div>
                <a
                    href="/familyStudio"
                    className={styles.btnPrimaryBrand}
                    style={{ textDecoration: 'none' }}
                >
                    <span>Open Tree Studio</span>
                    <i className="bi bi-arrow-right" aria-hidden="true" />
                </a>
            </div>

            {hasMaternalCode && (
                <LineageScopePicker
                    prefix="parents"
                    primaryId="parents-scope-primary"
                    maternalId="parents-scope-maternal"
                    famCode={accountData.famCode}
                    otherFamCode={accountData.otherFamCode}
                    scope={lineageScope}
                    onChange={setLineageScope}
                    subjectLabel="parents"
                />
            )}

            <StatusAlert
                id="parentsForm_notification"
                errorId="parentsForm_notification_error"
                message={status.message}
                type={status.type}
            />

            <form id="parentsForm" onSubmit={handleSubmit}>
                <input type="hidden" name="action" value="updateParents" />
                <CsrfFields token={csrfToken} />
                <input type="hidden" name="lineage_scope" id="parentsLineageScope" value={lineageScope} />
                <input type="hidden" name="lineage_code" id="parentsLineageCode" value={lineageCode} />
                <input type="hidden" id="otherFamCode_id" value={accountData.otherFamCode || ''} />
                <input type="hidden" id="famCode_id" value={accountData.famCode || ''} />

                {/* Father's Card */}
                <div className={`p-4 mb-4 ${styles.subcard}`}>
                    <h6
                        className="fw-bold mb-3 text-uppercase"
                        style={{ letterSpacing: '0.5px', fontSize: '0.9rem', color: 'var(--as-ink)' }}
                    >
                        <i className="bi bi-person-fill text-primary me-2" aria-hidden="true" />
                        Father&apos;s Details
                    </h6>
                    <div className="row g-3">
                        <div className="col-md-6">
                            <label className={styles.formLabel} htmlFor="father_first_name">
                                Father&apos;s First Name
                            </label>
                            <input
                                type="text"
                                className={`form-control ${styles.formInput}`}
                                id="father_first_name"
                                name="father_first_name"
                                value={fatherFirstName}
                                placeholder="Enter first name"
                                onInput={(e) => setFatherFirstName(e.currentTarget.value)}
                            />
                        </div>
                        <div className="col-md-6">
                            <label className={styles.formLabel} htmlFor="father_last_name">
                                Father&apos;s Last Name
                            </label>
                            <input
                                type="text"
                                className={`form-control ${styles.formInput}`}
                                id="father_last_name"
                                name="father_last_name"
                                value={fatherLastName}
                                placeholder="Enter last name"
                                onInput={(e) => setFatherLastName(e.currentTarget.value)}
                            />
                        </div>
                        <div className="col-md-6">
                            <label className={styles.formLabel} htmlFor="father_email">
                                Father&apos;s Email
                            </label>
                            <input
                                type="email"
                                className={`form-control ${styles.formInput}`}
                                id="father_email"
                                name="father_email"
                                value={fatherEmail}
                                placeholder="father@example.com"
                                onInput={(e) => setFatherEmail(e.currentTarget.value)}
                            />
                        </div>
                        <div className="col-md-6">
                            <label className={styles.formLabel} htmlFor="father_mobile">
                                Father&apos;s Mobile
                            </label>
                            <input
                                type="text"
                                className={`form-control ${styles.formInput}`}
                                id="father_mobile"
                                name="father_mobile"
                                value={fatherMobile}
                                placeholder="e.g. +44..."
                                onInput={(e) => setFatherMobile(e.currentTarget.value)}
                            />
                        </div>
                    </div>
                </div>

                {/* Mother's Card */}
                <div className={`p-4 mb-4 ${styles.subcard}`}>
                    <h6
                        className="fw-bold mb-3 text-uppercase"
                        style={{ letterSpacing: '0.5px', fontSize: '0.9rem', color: 'var(--as-ink)' }}
                    >
                        <i className="bi bi-person-fill text-danger me-2" aria-hidden="true" />
                        Mother&apos;s Details
                    </h6>
                    <div className="row g-3">
                        <div className="col-md-4">
                            <label className={styles.formLabel} htmlFor="mother_first_name">
                                Mother&apos;s First Name
                            </label>
                            <input
                                type="text"
                                className={`form-control ${styles.formInput}`}
                                id="mother_first_name"
                                name="mother_first_name"
                                value={motherFirstName}
                                placeholder="Enter first name"
                                onInput={(e) => setMotherFirstName(e.currentTarget.value)}
                            />
                        </div>
                        <div className="col-md-4">
                            <label className={styles.formLabel} htmlFor="mother_last_name">
                                Mother&apos;s Last Name
                            </label>
                            <input
                                type="text"
                                className={`form-control ${styles.formInput}`}
                                id="mother_last_name"
                                name="mother_last_name"
                                value={motherLastName}
                                placeholder="Enter last name"
                                onInput={(e) => setMotherLastName(e.currentTarget.value)}
                            />
                        </div>
                        <div className="col-md-4">
                            <label className={styles.formLabel} htmlFor="mother_maiden">
                                Maiden Name
                            </label>
                            <input
                                type="text"
                                className={`form-control ${styles.formInput}`}
                                id="mother_maiden"
                                name="maiden_name"
                                value={motherMaiden}
                                placeholder="Maiden name"
                                onInput={(e) => setMotherMaiden(e.currentTarget.value)}
                            />
                        </div>
                        <div className="col-md-6">
                            <label className={styles.formLabel} htmlFor="mother_email">
                                Mother&apos;s Email
                            </label>
                            <input
                                type="email"
                                className={`form-control ${styles.formInput}`}
                                id="mother_email"
                                name="mother_email"
                                value={motherEmail}
                                placeholder="mother@example.com"
                                onInput={(e) => setMotherEmail(e.currentTarget.value)}
                            />
                        </div>
                        <div className="col-md-6">
                            <label className={styles.formLabel} htmlFor="mother_mobile">
                                Mother&apos;s Mobile
                            </label>
                            <input
                                type="text"
                                className={`form-control ${styles.formInput}`}
                                id="mother_mobile"
                                name="mother_mobile"
                                value={motherMobile}
                                placeholder="e.g. +44..."
                                onInput={(e) => setMotherMobile(e.currentTarget.value)}
                            />
                        </div>
                    </div>
                </div>

                <div className="mb-4">
                    <label htmlFor="parents_status" className={styles.formLabel}>
                        Are your parents still together?
                    </label>
                    <select
                        className={`form-select ${styles.formSelect}`}
                        id="parents_status"
                        name="parents_status"
                        value={parentsStatus}
                        onChange={(e) => setParentsStatus(e.currentTarget.value)}
                    >
                        <option value="">Prefer not to say</option>
                        <option value="married">Yes - together</option>
                        <option value="divorced">Divorced</option>
                        <option value="separated">Separated</option>
                        <option value="widowed">One of them has passed away</option>
                    </select>
                    <div className={styles.formHelp}>
                        If you have half-siblings, add them under <strong>Siblings</strong> and choose &ldquo;Same Mother Only&rdquo; or &ldquo;Same Father Only&rdquo;.
                    </div>
                </div>

                <SubmitButton
                    id="parentsBtn"
                    type="submit"
                    loading={saving}
                    className="w-100 mt-2"
                >
                    Save Parents Details
                </SubmitButton>
            </form>
        </div>
    );
}
