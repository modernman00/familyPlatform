import { useEffect, useState } from 'preact/hooks';
import { postAccountForm, toastMessage } from '../api/accountSettingsApi';
import { CsrfFields, StatusAlert, SubmitButton } from '../components/SettingsControls';
import styles from '../AccountSettingsShell.module.css';

function PasswordField({ id, name, label, value, onInput, autoComplete }) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="mb-4">
            <label className={styles.formLabel} htmlFor={id}>
                {label}
            </label>
            <div className={styles.passwordWrap}>
                <input
                    id={id}
                    type={visible ? 'text' : 'password'}
                    name={name}
                    value={value}
                    autoComplete={autoComplete}
                    onInput={(e) => onInput(e.currentTarget.value)}
                    className={`form-control ${styles.formInput} ${styles.passwordInput}`}
                />
                <button
                    type="button"
                    className={styles.passwordEyeBtn}
                    data-no-family-tooltip="true"
                    onClick={() => setVisible((prev) => !prev)}
                    aria-label={visible ? `Hide ${label.toLowerCase()}` : `Show ${label.toLowerCase()}`}
                    aria-pressed={visible}
                >
                    <i className={`bi ${visible ? 'bi-eye-slash' : 'bi-eye'}`} aria-hidden="true" />
                </button>
            </div>
        </div>
    );
}

export function PasswordTab({ csrfToken, bootstrap, onDirtyChange }) {
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState({ type: 'info', message: '' });

    const isDirty = Boolean(currentPassword || newPassword || confirmPassword);

    useEffect(() => {
        if (typeof onDirtyChange === 'function') {
            onDirtyChange('password', isDirty);
        }
    }, [isDirty, onDirtyChange]);

    const handleSubmit = async (e) => {
        if (e && e.preventDefault) e.preventDefault();
        if (saving) return;

        if (!currentPassword || !newPassword || !confirmPassword) {
            setStatus({ type: 'error', message: 'All password fields are required.' });
            return;
        }
        if (newPassword.length < 8) {
            setStatus({ type: 'error', message: 'Password must be at least 8 characters.' });
            return;
        }
        if (newPassword !== confirmPassword) {
            setStatus({ type: 'error', message: 'New passwords do not match.' });
            return;
        }

        setSaving(true);
        setStatus({ type: 'info', message: '' });

        try {
            const res = await postAccountForm(
                {
                    action: 'updatePassword',
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword,
                },
                bootstrap
            );

            if (res.ok) {
                setCurrentPassword('');
                setNewPassword('');
                setConfirmPassword('');
                if (typeof onDirtyChange === 'function') {
                    onDirtyChange('password', false);
                }
                setStatus({ type: 'success', message: res.message || 'Password successfully updated.' });
                toastMessage(res.message || 'Password successfully updated.', 'success');
            } else {
                setStatus({ type: 'error', message: res.message || 'Could not update password.' });
            }
        } catch (err) {
            setStatus({
                type: 'error',
                message: (err && err.message) || 'Could not update password. Please verify your current password.',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <div>
            <div className={styles.sectionTitle}>Change Password</div>
            <div className={styles.sectionSubtitle}>
                Ensure your account is using a long, random password to stay secure.
            </div>
            <div className={styles.sectionDivider} />

            <StatusAlert
                id="passwordForm_notification"
                errorId="passwordForm_notification_error"
                message={status.message}
                type={status.type}
            />

            <form id="passwordForm" onSubmit={handleSubmit}>
                <input type="hidden" name="action" value="updatePassword" />
                <CsrfFields token={csrfToken} />

                <PasswordField
                    id="current_password_input"
                    name="current_password"
                    label="Current Password"
                    value={currentPassword}
                    onInput={setCurrentPassword}
                    autoComplete="current-password"
                />

                <PasswordField
                    id="new_password_input"
                    name="new_password"
                    label="New Password"
                    value={newPassword}
                    onInput={setNewPassword}
                    autoComplete="new-password"
                />

                <PasswordField
                    id="confirm_password_input"
                    name="confirm_password"
                    label="Confirm New Password"
                    value={confirmPassword}
                    onInput={setConfirmPassword}
                    autoComplete="new-password"
                />

                <SubmitButton
                    id="passwordBtn"
                    type="submit"
                    loading={saving}
                    className="w-100 mt-2"
                >
                    Save Password
                </SubmitButton>
            </form>
        </div>
    );
}
