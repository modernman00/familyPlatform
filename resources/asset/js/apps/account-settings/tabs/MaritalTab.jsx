import { useEffect, useState } from 'preact/hooks';
import { postAccountForm, toastMessage } from '../api/accountSettingsApi';
import { CsrfFields, StatusAlert, SubmitButton } from '../components/SettingsControls';
import styles from '../AccountSettingsShell.module.css';

const SPOUSE_VISIBLE_STATUSES = [
    'Yes - Add Husband',
    'Yes - Add Wife',
    'Divorced',
    'Widowed',
];

export function MaritalTab({
    accountData,
    maxWeddingDate,
    csrfToken,
    bootstrap,
    onSavedAccountData,
    onDirtyChange,
}) {
    const initialWedding = accountData.wedding_date || accountData.marriage_date || '';
    const [maritalStatus, setMaritalStatus] = useState(accountData.maritalStatus || '');
    const [spouseName, setSpouseName] = useState(accountData.spouse_name || '');
    const [spouseEmail, setSpouseEmail] = useState(accountData.spouse_email || '');
    const [spouseMobile, setSpouseMobile] = useState(accountData.spouse_mobile || '');
    const [spouseGender, setSpouseGender] = useState(accountData.spouse_gender || '');
    const [maidenName, setMaidenName] = useState('');
    const [weddingDate, setWeddingDate] = useState(initialWedding);
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState({ type: 'info', message: '' });

    useEffect(() => {
        setMaritalStatus(accountData.maritalStatus || '');
        setSpouseName(accountData.spouse_name || '');
        setSpouseEmail(accountData.spouse_email || '');
        setSpouseMobile(accountData.spouse_mobile || '');
        setSpouseGender(accountData.spouse_gender || '');
        setWeddingDate(accountData.wedding_date || accountData.marriage_date || '');
    }, [
        accountData.maritalStatus,
        accountData.spouse_name,
        accountData.spouse_email,
        accountData.spouse_mobile,
        accountData.spouse_gender,
        accountData.wedding_date,
        accountData.marriage_date,
    ]);

    const isDirty =
        maritalStatus !== (accountData.maritalStatus || '') ||
        spouseName !== (accountData.spouse_name || '') ||
        spouseEmail !== (accountData.spouse_email || '') ||
        spouseMobile !== (accountData.spouse_mobile || '') ||
        spouseGender !== (accountData.spouse_gender || '') ||
        weddingDate !== (accountData.wedding_date || accountData.marriage_date || '') ||
        maidenName !== '';

    useEffect(() => {
        if (typeof onDirtyChange === 'function') {
            onDirtyChange('marital', isDirty);
        }
    }, [isDirty, onDirtyChange]);

    const showSpouse = SPOUSE_VISIBLE_STATUSES.includes(maritalStatus);
    const showMaiden = maritalStatus === 'Yes - Add Wife';
    const showFormerGender = maritalStatus === 'Divorced' || maritalStatus === 'Widowed';

    const handleSubmit = async (e) => {
        if (e && e.preventDefault) e.preventDefault();
        if (saving) return;

        setSaving(true);
        setStatus({ type: 'info', message: '' });

        try {
            const payload = {
                action: 'updateMarital',
                maritalStatus,
                spouse_name: spouseName.trim(),
                spouse_email: spouseEmail.trim(),
                spouse_mobile: spouseMobile.trim(),
                spouse_gender: showFormerGender ? spouseGender : '',
                maiden_name: showMaiden ? maidenName.trim() : '',
                wedding_date: weddingDate,
            };

            const res = await postAccountForm(payload, bootstrap);
            if (res.ok) {
                onSavedAccountData({
                    maritalStatus: payload.maritalStatus,
                    spouse_name: payload.spouse_name,
                    spouse_email: payload.spouse_email,
                    spouse_mobile: payload.spouse_mobile,
                    spouse_gender: payload.spouse_gender,
                    wedding_date: payload.wedding_date,
                    marriage_date: payload.wedding_date,
                });
                setMaidenName('');
                if (typeof onDirtyChange === 'function') {
                    onDirtyChange('marital', false);
                }
                setStatus({
                    type: 'success',
                    message: res.message || 'Marital status and spouse details successfully updated.',
                });
                toastMessage(
                    res.message || 'Marital status and spouse details successfully updated.',
                    'success'
                );
            } else {
                setStatus({ type: 'error', message: res.message || 'Could not save marital status.' });
            }
        } catch (err) {
            setStatus({
                type: 'error',
                message: (err && err.message) || 'Network error while saving marital status.',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <div>
            <div className={styles.sectionTitle}>Marital Status &amp; Spouse</div>
            <div className={styles.sectionSubtitle}>
                Configure your relationship status and partner connections.
            </div>
            <div className={styles.sectionDivider} />

            <StatusAlert
                id="maritalForm_notification"
                errorId="maritalForm_notification_error"
                message={status.message}
                type={status.type}
            />

            <form id="maritalForm" onSubmit={handleSubmit}>
                <input type="hidden" name="action" value="updateMarital" />
                <CsrfFields token={csrfToken} />

                <div className="mb-4">
                    <label htmlFor="maritalStatus" className={styles.formLabel}>
                        Marital Status
                    </label>
                    <select
                        className={`form-select ${styles.formSelect}`}
                        id="maritalStatus"
                        name="maritalStatus"
                        value={maritalStatus}
                        onChange={(e) => setMaritalStatus(e.currentTarget.value)}
                    >
                        <option value="" disabled>
                            Select status...
                        </option>
                        <option value="Single">Single</option>
                        <option value="Yes - Add Husband">Yes - Add Husband</option>
                        <option value="Yes - Add Wife">Yes - Add Wife</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>

                {showSpouse && (
                    <div id="spouse" className={`p-4 mb-4 ${styles.subcard}`}>
                        <h6
                            className="fw-bold mb-3 text-uppercase"
                            style={{ letterSpacing: '0.5px', fontSize: '0.9rem', color: 'var(--as-ink)' }}
                        >
                            <i className="bi bi-heart-fill text-danger me-2" aria-hidden="true" />
                            Spouse Details
                        </h6>
                        <div className="row g-3">
                            <div className="col-md-6">
                                <label className={styles.formLabel} htmlFor="spouse_name">
                                    Spouse&apos;s Name
                                </label>
                                <input
                                    type="text"
                                    className={`form-control ${styles.formInput}`}
                                    id="spouse_name"
                                    name="spouse_name"
                                    value={spouseName}
                                    placeholder="Enter full name"
                                    onInput={(e) => setSpouseName(e.currentTarget.value)}
                                />
                            </div>
                            <div className="col-md-6">
                                <label className={styles.formLabel} htmlFor="spouse_email">
                                    Spouse&apos;s Email
                                </label>
                                <input
                                    type="email"
                                    className={`form-control ${styles.formInput}`}
                                    id="spouse_email"
                                    name="spouse_email"
                                    value={spouseEmail}
                                    placeholder="Enter email"
                                    onInput={(e) => setSpouseEmail(e.currentTarget.value)}
                                />
                            </div>
                            <div className="col-md-6">
                                <label className={styles.formLabel} htmlFor="spouse_mobile">
                                    Spouse&apos;s Mobile
                                </label>
                                <input
                                    type="text"
                                    className={`form-control ${styles.formInput}`}
                                    id="spouse_mobile"
                                    name="spouse_mobile"
                                    value={spouseMobile}
                                    placeholder="Enter mobile number"
                                    onInput={(e) => setSpouseMobile(e.currentTarget.value)}
                                />
                            </div>

                            {showFormerGender && (
                                <div className="col-md-6" id="spouse_gender_div">
                                    <label className={styles.formLabel} htmlFor="spouse_gender">
                                        Former partner is your
                                    </label>
                                    <select
                                        className={`form-select ${styles.formSelect}`}
                                        id="spouse_gender"
                                        name="spouse_gender"
                                        value={spouseGender}
                                        onChange={(e) => setSpouseGender(e.currentTarget.value)}
                                    >
                                        <option value="">Select...</option>
                                        <option value="Male">Husband / Male partner</option>
                                        <option value="Female">Wife / Female partner</option>
                                    </select>
                                </div>
                            )}

                            {showMaiden && (
                                <div className="col-md-6" id="maiden_name_div">
                                    <label className={styles.formLabel} htmlFor="maiden_name">
                                        Maiden Name
                                    </label>
                                    <input
                                        type="text"
                                        className={`form-control ${styles.formInput}`}
                                        id="maiden_name"
                                        name="maiden_name"
                                        value={maidenName}
                                        placeholder="Enter maiden name"
                                        onInput={(e) => setMaidenName(e.currentTarget.value)}
                                    />
                                </div>
                            )}

                            <div className="col-md-6" id="wedding_date_div">
                                <label className={styles.formLabel} htmlFor="spouse_wedding_date">
                                    Wedding Date / Anniversary
                                </label>
                                <input
                                    type="date"
                                    className={`form-control ${styles.formInput}`}
                                    id="spouse_wedding_date"
                                    name="wedding_date"
                                    value={weddingDate}
                                    max={maxWeddingDate || undefined}
                                    onChange={(e) => setWeddingDate(e.currentTarget.value)}
                                />
                                <small className={styles.formHelp}>
                                    Powers automated anniversary cards and milestone countdowns.
                                </small>
                            </div>
                        </div>
                    </div>
                )}

                <SubmitButton
                    id="maritalBtn"
                    type="submit"
                    loading={saving}
                    className="w-100 mt-2"
                >
                    Save Marital Status
                </SubmitButton>
            </form>
        </div>
    );
}
