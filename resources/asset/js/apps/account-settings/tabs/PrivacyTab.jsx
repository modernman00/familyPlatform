import { useEffect, useState } from 'preact/hooks';
import {
    exportPersonalData,
    postAccountForm,
    registerPasskeyDevice,
    requestAccountDeletion,
    revokePasskeyDevice,
    toastMessage,
} from '../api/accountSettingsApi';
import {
    CsrfFields,
    SettingsSwitch,
    StatusAlert,
    SubmitButton,
} from '../components/SettingsControls';
import { MemberRestrictionsPanel } from './MemberRestrictionsPanel';
import styles from '../AccountSettingsShell.module.css';

export function PrivacyTab({
    accountData,
    passkeys,
    restrictionDirectory,
    csrfToken,
    bootstrap,
    onSavedAccountData,
    onRestrictionsChange,
    onRefreshState,
    onJumpToTarget,
    onDirtyChange,
}) {
    const [twoFactor, setTwoFactor] = useState((accountData.two_factor_auth || 'off') === 'on');
    const [profileVisibility, setProfileVisibility] = useState(accountData.profile_visibility || 'Public');
    const [showMyProfile, setShowMyProfile] = useState((accountData.show_my_profile || 'on') === 'on');
    const [showBirthYear, setShowBirthYear] = useState((accountData.show_birth_year || 'off') === 'on');
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState({ type: 'info', message: '' });

    // Passkey state
    const [registeringPasskey, setRegisteringPasskey] = useState(false);
    const [revokingId, setRevokingId] = useState(null);
    const [passkeyMsg, setPasskeyMsg] = useState(null);

    // GDPR state
    const [exporting, setExporting] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [gdprStatus, setGdprStatus] = useState('');

    useEffect(() => {
        setTwoFactor((accountData.two_factor_auth || 'off') === 'on');
        setProfileVisibility(accountData.profile_visibility || 'Public');
        setShowMyProfile((accountData.show_my_profile || 'on') === 'on');
        setShowBirthYear((accountData.show_birth_year || 'off') === 'on');
    }, [
        accountData.two_factor_auth,
        accountData.profile_visibility,
        accountData.show_my_profile,
        accountData.show_birth_year,
    ]);

    const isDirty =
        twoFactor !== ((accountData.two_factor_auth || 'off') === 'on') ||
        profileVisibility !== (accountData.profile_visibility || 'Public') ||
        showMyProfile !== ((accountData.show_my_profile || 'on') === 'on') ||
        showBirthYear !== ((accountData.show_birth_year || 'off') === 'on');

    useEffect(() => {
        if (typeof onDirtyChange === 'function') {
            onDirtyChange('privacy', isDirty);
        }
    }, [isDirty, onDirtyChange]);

    const handleVisibilityChange = (val) => {
        setProfileVisibility(val);
        if (val === 'Public') {
            setShowMyProfile(true);
        } else if (val === 'Private') {
            setShowMyProfile(false);
        }
    };

    const handleSubmit = async (e) => {
        if (e && e.preventDefault) e.preventDefault();
        if (saving) return;

        setSaving(true);
        setStatus({ type: 'info', message: '' });

        try {
            const payload = {
                action: 'updatePrivacy',
                two_factor_auth: twoFactor ? 'on' : 'off',
                profile_visibility: profileVisibility,
                show_my_profile: showMyProfile ? 'on' : 'off',
                show_birth_year: showBirthYear ? 'on' : 'off',
            };
            const res = await postAccountForm(payload, bootstrap);
            if (res.ok) {
                onSavedAccountData({
                    two_factor_auth: payload.two_factor_auth,
                    profile_visibility: payload.profile_visibility,
                    show_my_profile: payload.show_my_profile,
                    show_birth_year: payload.show_birth_year,
                });
                if (typeof onDirtyChange === 'function') {
                    onDirtyChange('privacy', false);
                }
                setStatus({ type: 'success', message: res.message || 'Privacy settings successfully updated.' });
                toastMessage(res.message || 'Privacy settings successfully updated.', 'success');
            } else {
                setStatus({ type: 'error', message: res.message || 'Could not update privacy settings.' });
            }
        } catch (err) {
            setStatus({
                type: 'error',
                message: (err && err.message) || 'Network error while saving privacy settings.',
            });
        } finally {
            setSaving(false);
        }
    };

    const handleRegisterPasskey = async () => {
        if (registeringPasskey) return;
        setRegisteringPasskey(true);
        setPasskeyMsg(null);
        try {
            await registerPasskeyDevice(bootstrap);
            setPasskeyMsg({ ok: true, text: 'Biometric device registered successfully!' });
            toastMessage('Biometric device registered successfully!', 'success');
            if (typeof onRefreshState === 'function') {
                await onRefreshState();
            }
        } catch (err) {
            const msg =
                err && err.name === 'NotAllowedError'
                    ? 'Biometric prompt was cancelled or timed out.'
                    : (err && err.message) || 'Could not register biometric device.';
            setPasskeyMsg({ ok: false, text: msg });
        } finally {
            setRegisteringPasskey(false);
        }
    };

    const handleRevokePasskey = async (pk) => {
        if (revokingId) return;
        let confirmed = true;
        if (typeof window !== 'undefined' && window.Swal) {
            const result = await window.Swal.fire({
                title: 'Revoke Biometric Device?',
                text: `Remove passkey for ${pk.device_name || 'Biometric Device'}? You can register it again anytime.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Revoke',
                cancelButtonText: 'Keep Device',
                confirmButtonColor: '#a31621',
            });
            confirmed = Boolean(result.isConfirmed);
        } else if (typeof window !== 'undefined') {
            confirmed = window.confirm('Are you sure you want to revoke this biometric device?');
        }
        if (!confirmed) return;

        setRevokingId(pk.id || pk.credential_id);
        try {
            await revokePasskeyDevice(pk, bootstrap);
            toastMessage('Biometric device revoked.', 'success');
            if (typeof onRefreshState === 'function') {
                await onRefreshState();
            }
        } catch (err) {
            const errMsg = (err && err.message) || 'Failed to revoke biometric device.';
            setPasskeyMsg({ ok: false, text: errMsg });
        } finally {
            setRevokingId(null);
        }
    };

    const handleDataExport = async () => {
        if (exporting) return;
        setExporting(true);
        setGdprStatus('Preparing your data...');
        try {
            const { blob, filename } = await exportPersonalData(bootstrap);
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
            setGdprStatus('Download started. Check your downloads folder.');
        } catch {
            setGdprStatus('Sorry — we could not prepare your data. Please try again.');
        } finally {
            setExporting(false);
        }
    };

    const executeDeletion = async (mode) => {
        setDeleting(true);
        setGdprStatus('Processing your deletion request...');
        try {
            const body = await requestAccountDeletion(mode, bootstrap);
            const msg = (body && body.message) || 'Your deletion request has been processed.';
            setGdprStatus(msg);

            if (typeof window !== 'undefined' && window.Swal) {
                await window.Swal.fire({
                    icon: 'success',
                    title: mode === 'immediate' ? 'Account Permanently Erased' : 'Deletion Scheduled',
                    text: msg,
                    confirmButtonColor: '#a31621',
                    confirmButtonText: 'Return to Sign In',
                });
            }
            if (typeof window !== 'undefined') {
                window.location.href = (body && body.redirect) || '/login';
            }
        } catch (err) {
            const errMsg =
                (err && err.message) || 'Sorry — we could not process your request. Please try again.';
            setGdprStatus(errMsg);
            if (typeof window !== 'undefined' && window.Swal) {
                window.Swal.fire({
                    icon: 'error',
                    title: 'Deletion Request Failed',
                    text: errMsg,
                    confirmButtonColor: '#a31621',
                });
            }
        } finally {
            setDeleting(false);
        }
    };

    const handleDataDelete = async () => {
        if (deleting) return;
        if (typeof window === 'undefined' || !window.Swal) {
            const choice = typeof window !== 'undefined'
                ? window.confirm(
                      'Do you want to permanently erase your account immediately (Click OK)? Or cancel (Click Cancel)?'
                  )
                : false;
            if (choice) await executeDeletion('immediate');
            return;
        }

        const { value: selectedOption } = await window.Swal.fire({
            icon: 'warning',
            title: 'Delete Your Account',
            html: `
              <div class="text-start" style="font-size: 0.95rem;">
                <p class="text-muted mb-3">Under GDPR Article 17, please select your preferred deletion method:</p>
                <div class="p-3 mb-3 border rounded-3" style="background-color: #f8fafc; cursor: pointer;">
                  <label class="d-flex align-items-start gap-2" style="cursor: pointer;">
                    <input class="form-check-input mt-1" type="radio" name="deletionMode" id="delModeSchedule" value="schedule" checked style="cursor: pointer;">
                    <div>
                      <strong class="d-block text-dark">Option B: 30-Day Scheduled Deletion (Grace Period)</strong>
                      <span class="small text-muted d-block mt-1">
                        Your account is scheduled for deletion in 30 days and you will be signed out. You can cancel anytime within 30 days simply by signing back in.
                      </span>
                    </div>
                  </label>
                </div>
                <div class="p-3 border rounded-3" style="background-color: #fdf5f5; border-color: #fecaca !important; cursor: pointer;">
                  <label class="d-flex align-items-start gap-2" style="cursor: pointer;">
                    <input class="form-check-input mt-1" type="radio" name="deletionMode" id="delModeImmediate" value="immediate" style="cursor: pointer;">
                    <div>
                      <strong class="d-block text-danger">Option A: Delete Immediately (Permanent Erasure)</strong>
                      <span class="small text-danger-emphasis d-block mt-1">
                        Instantly purges and anonymises your profile, credentials, and uploaded media. Shared family trees are preserved in anonymised form. <strong>This cannot be undone.</strong>
                      </span>
                    </div>
                  </label>
                </div>
              </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Continue',
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const checked = document.querySelector('input[name="deletionMode"]:checked');
                return checked ? checked.value : 'schedule';
            },
        });

        if (!selectedOption) return;

        const isImmediate = selectedOption === 'immediate';
        const confirmResult = await window.Swal.fire({
            icon: 'warning',
            title: isImmediate
                ? 'Confirm Immediate Permanent Erasure?'
                : 'Confirm 30-Day Scheduled Deletion?',
            html: isImmediate
                ? 'Your personal data, media, and credentials will be permanently erased immediately. You will not be able to recover or sign into this account.'
                : 'Your account will be deactivated and scheduled for deletion in 30 days. You will be signed out now. You may sign back in at any point within 30 days to cancel this request.',
            showCancelButton: true,
            confirmButtonText: isImmediate ? 'Yes, erase my account now' : 'Yes, schedule deletion',
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Keep My Account',
        });

        if (confirmResult.isConfirmed) {
            await executeDeletion(selectedOption);
        }
    };

    const pkList = Array.isArray(passkeys) ? passkeys : [];

    return (
        <div>
            <div className={styles.sectionTitle}>Privacy &amp; Security</div>
            <div className={styles.sectionSubtitle}>Control your data sharing and security layers.</div>
            <div className={styles.sectionDivider} />

            <StatusAlert
                id="privacyForm_notification"
                errorId="privacyForm_notification_error"
                message={status.message}
                type={status.type}
            />

            <form id="privacyForm" onSubmit={handleSubmit}>
                <input type="hidden" name="action" value="updatePrivacy" />
                <CsrfFields token={csrfToken} />

                <SettingsSwitch
                    id="privacy_two_factor_auth"
                    name="two_factor_auth"
                    label="Two-Factor Authentication (2FA)"
                    description="Require a 6-digit verification code sent to your email when signing in."
                    checked={twoFactor}
                    onChange={setTwoFactor}
                />

                <div className="mb-4 mt-4">
                    <label className={styles.formLabel} htmlFor="profileVisibilitySelect">
                        Profile Visibility
                    </label>
                    <select
                        className={`form-select ${styles.formSelect}`}
                        name="profile_visibility"
                        id="profileVisibilitySelect"
                        value={profileVisibility}
                        onChange={(e) => handleVisibilityChange(e.currentTarget.value)}
                    >
                        <option value="Public">Public</option>
                        <option value="Family Only">Family Only</option>
                        <option value="Private">Private</option>
                    </select>
                    <div className={styles.formHelp}>
                        Want to hide updates from specific relatives instead? Customise per-member visibility silently in{' '}
                        <a
                            href="#memberRestrictionCard"
                            className="fw-semibold text-decoration-underline"
                            style={{ color: 'inherit' }}
                            onClick={(e) => {
                                e.preventDefault();
                                if (typeof onJumpToTarget === 'function') {
                                    onJumpToTarget('memberRestrictionCard');
                                }
                            }}
                        >
                            Restricted Family Members
                        </a>{' '}
                        below.
                    </div>
                </div>

                <SettingsSwitch
                    id="privacy_show_my_profile"
                    name="show_my_profile"
                    label="Show My Profile"
                    description="Allow family members and other verified users to discover your profile in member directory search."
                    checked={showMyProfile}
                    onChange={setShowMyProfile}
                />

                <SettingsSwitch
                    id="privacy_show_birth_year"
                    name="show_birth_year"
                    label="Show Year of Birth"
                    description="Display your birth year under Date of Birth on your profile (e.g. 29 / Jul / 1979). When turned off, only the day and month are visible (e.g. 29 / Jul)."
                    checked={showBirthYear}
                    onChange={setShowBirthYear}
                />

                <SubmitButton
                    id="privacyBtn"
                    type="submit"
                    loading={saving}
                    className="w-100 mt-3"
                >
                    Save Privacy Settings
                </SubmitButton>
            </form>

            {/* Biometric Passkeys Card */}
            <div className="mt-5 pt-4 border-top" style={{ borderColor: 'var(--as-border)' }}>
                <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h6 className="fw-bold mb-1" style={{ color: 'var(--as-ink)' }}>
                            <i className="bi bi-fingerprint text-primary me-2" aria-hidden="true" />
                            Biometric Passkeys
                        </h6>
                        <p className="small mb-0" style={{ color: 'var(--as-muted)' }}>
                            Log in securely using Face ID, Touch ID, or Windows Hello without passwords.
                        </p>
                    </div>
                    <button
                        type="button"
                        id="btn-register-passkey"
                        className={styles.btnPrimaryBrand}
                        disabled={registeringPasskey}
                        onClick={handleRegisterPasskey}
                    >
                        {registeringPasskey ? (
                            <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                        ) : (
                            <i className="bi bi-plus-circle" aria-hidden="true" />
                        )}
                        <span>{registeringPasskey ? 'Authorising...' : 'Register This Device'}</span>
                    </button>
                </div>

                {passkeyMsg && (
                    <div
                        id="passkey-status-msg"
                        className={`alert small mb-3 ${passkeyMsg.ok ? 'alert-success' : 'alert-warning'}`}
                        role="alert"
                    >
                        {passkeyMsg.text}
                    </div>
                )}

                {pkList.length > 0 ? (
                    <div className="d-flex flex-column gap-2 mt-3">
                        {pkList.map((pk) => {
                            const keyId = pk.id || pk.credential_id;
                            const isRevoking = revokingId === keyId;
                            return (
                                <div
                                    key={keyId}
                                    className={`d-flex justify-content-between align-items-center p-3 flex-wrap gap-2 ${styles.subcard}`}
                                >
                                    <div className="d-flex align-items-center gap-3">
                                        <div
                                            className="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                                            style={{ width: '40px', height: '40px', flexShrink: 0 }}
                                        >
                                            <i className="bi bi-laptop" aria-hidden="true" />
                                        </div>
                                        <div>
                                            <div className="fw-bold small" style={{ color: 'var(--as-ink)' }}>
                                                {pk.device_name || 'Biometric Device'}
                                            </div>
                                            <div style={{ fontSize: '0.76rem', color: 'var(--as-muted)' }}>
                                                Added {pk.created_formatted || pk.created_at || 'Recently'}
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        className={`btn btn-outline-danger btn-sm rounded-pill px-3 btn-revoke-passkey ${styles.actionBtn}`}
                                        data-id={pk.credential_id}
                                        disabled={isRevoking}
                                        onClick={() => handleRevokePasskey(pk)}
                                    >
                                        {isRevoking && (
                                            <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                        )}
                                        <span>{isRevoking ? 'Revoking...' : 'Revoke'}</span>
                                    </button>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className={`p-3 text-center small ${styles.subcard}`} style={{ color: 'var(--as-muted)' }}>
                        No biometric devices registered yet. Click &ldquo;Register This Device&rdquo; above to enable passkey sign in.
                    </div>
                )}
            </div>

            {/* GDPR Export & Erasure */}
            <div className={`${styles.sectionDivider} mt-4`} />
            <div className={styles.switchInfo}>
                <h6>Your data &amp; account deletion (GDPR)</h6>
                <p>
                    Download a copy of your personal data (Article 15) or manage account deletion (Article 17) with options for immediate permanent erasure or a 30-day scheduled grace period.
                </p>
            </div>
            <div className="d-flex flex-wrap gap-2 mt-3">
                <button
                    type="button"
                    id="dataExportBtn"
                    className={`btn btn-outline-secondary btn-sm rounded-pill px-3 ${styles.actionBtn}`}
                    disabled={exporting}
                    onClick={handleDataExport}
                >
                    {exporting ? (
                        <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                    ) : (
                        <i className="bi bi-download" aria-hidden="true" />
                    )}
                    <span>Download my data</span>
                </button>
                <button
                    type="button"
                    id="dataDeleteBtn"
                    className={`btn btn-outline-danger btn-sm rounded-pill px-3 ${styles.actionBtn}`}
                    disabled={deleting}
                    onClick={handleDataDelete}
                >
                    {deleting ? (
                        <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                    ) : (
                        <i className="bi bi-trash3" aria-hidden="true" />
                    )}
                    <span>Delete account</span>
                </button>
            </div>
            {gdprStatus && (
                <p id="dataExportStatus" className="small mt-2 mb-0" style={{ color: 'var(--as-muted)' }}>
                    {gdprStatus}
                </p>
            )}

            <MemberRestrictionsPanel
                initialMembers={restrictionDirectory}
                bootstrap={bootstrap}
                onMembersChange={onRestrictionsChange}
            />
        </div>
    );
}
