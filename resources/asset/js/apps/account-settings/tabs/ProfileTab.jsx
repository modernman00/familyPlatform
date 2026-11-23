import { useEffect, useState } from 'preact/hooks';
import { postAccountForm, toastMessage } from '../api/accountSettingsApi';
import { CsrfFields, StatusAlert, SubmitButton } from '../components/SettingsControls';
import styles from '../AccountSettingsShell.module.css';

export function ProfileTab({
    accountData,
    csrfToken,
    bootstrap,
    onSavedAccountData,
    onDirtyChange,
}) {
    const [mobile, setMobile] = useState(accountData.mobile || '');
    const [country, setCountry] = useState(accountData.country || '');
    const [employmentStatus, setEmploymentStatus] = useState(accountData.employmentStatus || '');
    const [occupation, setOccupation] = useState(accountData.occupation || '');
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState({ type: 'info', message: '' });

    useEffect(() => {
        setMobile(accountData.mobile || '');
        setCountry(accountData.country || '');
        setEmploymentStatus(accountData.employmentStatus || '');
        setOccupation(accountData.occupation || '');
    }, [
        accountData.mobile,
        accountData.country,
        accountData.employmentStatus,
        accountData.occupation,
    ]);

    const isDirty =
        mobile !== (accountData.mobile || '') ||
        country !== (accountData.country || '') ||
        employmentStatus !== (accountData.employmentStatus || '') ||
        occupation !== (accountData.occupation || '');

    useEffect(() => {
        if (typeof onDirtyChange === 'function') {
            onDirtyChange('profile', isDirty);
        }
    }, [isDirty, onDirtyChange]);

    const handleSubmit = async (e) => {
        if (e && e.preventDefault) e.preventDefault();
        if (saving) return;

        setSaving(true);
        setStatus({ type: 'info', message: '' });

        try {
            const res = await postAccountForm(
                {
                    action: 'updateProfile',
                    email: accountData.email || '',
                    mobile: mobile.trim(),
                    country: country.trim(),
                    employmentStatus,
                    occupation: occupation.trim(),
                },
                bootstrap
            );

            if (res.ok) {
                const updatedFields = {
                    mobile: mobile.trim(),
                    country: country.trim(),
                    employmentStatus,
                    occupation: occupation.trim(),
                };
                onSavedAccountData(updatedFields);
                if (typeof onDirtyChange === 'function') {
                    onDirtyChange('profile', false);
                }
                setStatus({ type: 'success', message: res.message || 'Profile details successfully updated.' });
                toastMessage(res.message || 'Profile details successfully updated.', 'success');
            } else {
                setStatus({ type: 'error', message: res.message || 'Could not update profile details.' });
            }
        } catch (err) {
            setStatus({
                type: 'error',
                message: (err && err.message) || 'Network error while updating profile details.',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <div>
            <div className={styles.sectionTitle}>Profile &amp; Work Information</div>
            <div className={styles.sectionSubtitle}>Update your personal contact and professional details.</div>
            <div className={styles.sectionDivider} />

            <StatusAlert
                id="profileForm_notification"
                errorId="profileForm_notification_error"
                message={status.message}
                type={status.type}
            />

            <form
                className="accountSettingForm"
                id="profileForm"
                encType="multipart/form-data"
                onSubmit={handleSubmit}
            >
                <input type="hidden" name="action" value="updateProfile" />
                <CsrfFields token={csrfToken} />

                <div className="row g-3 mb-3">
                    <div className="col-md-6">
                        <label className={styles.formLabel} htmlFor="profile_first_name">
                            First Name
                        </label>
                        <input
                            type="text"
                            id="profile_first_name"
                            className={`form-control ${styles.formInput}`}
                            value={accountData.firstName || ''}
                            readOnly
                        />
                    </div>
                    <div className="col-md-6">
                        <label className={styles.formLabel} htmlFor="profile_last_name">
                            Last Name
                        </label>
                        <input
                            type="text"
                            id="profile_last_name"
                            className={`form-control ${styles.formInput}`}
                            value={accountData.lastName || ''}
                            readOnly
                        />
                    </div>
                </div>

                <div className="row g-3 mb-3">
                    <div className="col-md-6">
                        <label className={styles.formLabel} htmlFor="email">
                            Email Address (Read-only)
                        </label>
                        <input
                            type="email"
                            className={`form-control ${styles.formInput}`}
                            id="email"
                            name="email"
                            value={accountData.email || ''}
                            readOnly
                        />
                    </div>
                    <div className="col-md-6">
                        <label className={styles.formLabel} htmlFor="mobile">
                            Mobile Number
                        </label>
                        <input
                            type="text"
                            className={`form-control ${styles.formInput}`}
                            id="mobile"
                            name="mobile"
                            value={mobile}
                            placeholder="e.g. +44 7700 900077"
                            onInput={(e) => setMobile(e.currentTarget.value)}
                        />
                    </div>
                </div>

                <div className="row g-3 mb-4">
                    <div className="col-12">
                        <label className={styles.formLabel} htmlFor="country">
                            Country
                        </label>
                        <input
                            type="text"
                            className={`form-control ${styles.formInput}`}
                            id="country"
                            name="country"
                            value={country}
                            placeholder="Enter country of residence"
                            onInput={(e) => setCountry(e.currentTarget.value)}
                        />
                    </div>
                </div>

                <div className="pt-4 mt-2 border-top" style={{ borderColor: 'var(--as-border)' }}>
                    <div className={styles.sectionTitle} style={{ fontSize: '1.1rem' }}>
                        Work Information
                    </div>
                    <div className={styles.sectionSubtitle}>Update your professional details.</div>
                </div>

                <div className="row g-3 mb-4">
                    <div className="col-md-6">
                        <label className={styles.formLabel} htmlFor="employmentStatus">
                            Employment Status
                        </label>
                        <select
                            className={`form-select ${styles.formSelect}`}
                            id="employmentStatus"
                            name="employmentStatus"
                            value={employmentStatus}
                            onChange={(e) => setEmploymentStatus(e.currentTarget.value)}
                        >
                            <option value="" disabled>
                                Select status...
                            </option>
                            <option value="Employed">Employed</option>
                            <option value="Self-Employed">Self-Employed</option>
                            <option value="Unemployed">Unemployed</option>
                            <option value="Student">Student</option>
                            <option value="Retired">Retired</option>
                        </select>
                    </div>
                    <div className="col-md-6">
                        <label className={styles.formLabel} htmlFor="occupation">
                            Occupation
                        </label>
                        <input
                            type="text"
                            className={`form-control ${styles.formInput}`}
                            id="occupation"
                            name="occupation"
                            value={occupation}
                            placeholder="e.g. Software Engineer, Doctor"
                            onInput={(e) => setOccupation(e.currentTarget.value)}
                        />
                    </div>
                </div>

                <SubmitButton
                    id="profileBtn"
                    type="submit"
                    loading={saving}
                    className="w-100 mt-2"
                >
                    Save Profile Details
                </SubmitButton>
            </form>
        </div>
    );
}
