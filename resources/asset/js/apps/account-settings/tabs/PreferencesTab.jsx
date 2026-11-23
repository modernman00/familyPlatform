import { useEffect, useState } from 'preact/hooks';
import {
    enablePushNotifications,
    disablePushNotifications,
    getPushState,
    isPushSubscribed,
} from '../../../components/profilePage/registerPushNotification';
import {
    postAccountForm,
    sendTestPushNotification,
    toastMessage,
} from '../api/accountSettingsApi';
import {
    CsrfFields,
    SettingsSwitch,
    StatusAlert,
    SubmitButton,
} from '../components/SettingsControls';
import styles from '../AccountSettingsShell.module.css';

const PUSH_OPT_OUT_KEY = 'fp_push_pref_disabled';

function extractPushMsg(val, fallback) {
    if (typeof val === 'string' && val.trim() !== '') return val.trim();
    if (val && typeof val === 'object') {
        if (typeof val.message === 'string' && val.message.trim() !== '') return val.message.trim();
        if (typeof val.body === 'string' && val.body.trim() !== '') return val.body.trim();
        if (typeof val.content === 'string' && val.content.trim() !== '') return val.content.trim();
    }
    return fallback;
}

export function PreferencesTab({
    accountData,
    csrfToken,
    bootstrap,
    onSavedAccountData,
    onDirtyChange,
}) {
    const [emailNotifs, setEmailNotifs] = useState((accountData.email_notifications || 'on') === 'on');
    const [postEmailNotifs, setPostEmailNotifs] = useState((accountData.post_email_notifications || 'off') === 'on');
    const [smsNotifs, setSmsNotifs] = useState((accountData.sms_notifications || 'on') === 'on');
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState({ type: 'info', message: '' });

    // Push state
    const [pushOn, setPushOn] = useState(false);
    const [pushBusy, setPushBusy] = useState(false);
    const [pushDisabled, setPushDisabled] = useState(false);
    const [pushLabel, setPushLabel] = useState('CHECKING...');
    const [pushHintMode, setPushHintMode] = useState('default'); // default | ios | denied | unsupported | text
    const [pushHintText, setPushHintText] = useState('Get instant alerts on this device even when the app is closed.');
    const [deviceBadge, setDeviceBadge] = useState({ status: 'checking', text: 'Checking Status...' });
    const [testBusy, setTestBusy] = useState(false);
    const [resyncBusy, setResyncBusy] = useState(false);
    const [testFeedback, setTestFeedback] = useState(null);

    // PWA card state
    const [pwaState, setPwaState] = useState({ visible: false, installed: false });

    // Tooltip state
    const [tooltipMode, setTooltipMode] = useState('smart');
    const [tooltipResetDone, setTooltipResetDone] = useState(false);

    useEffect(() => {
        setEmailNotifs((accountData.email_notifications || 'on') === 'on');
        setPostEmailNotifs((accountData.post_email_notifications || 'off') === 'on');
        setSmsNotifs((accountData.sms_notifications || 'on') === 'on');
    }, [
        accountData.email_notifications,
        accountData.post_email_notifications,
        accountData.sms_notifications,
    ]);

    const isDirty =
        emailNotifs !== ((accountData.email_notifications || 'on') === 'on') ||
        postEmailNotifs !== ((accountData.post_email_notifications || 'off') === 'on') ||
        smsNotifs !== ((accountData.sms_notifications || 'on') === 'on');

    useEffect(() => {
        if (typeof onDirtyChange === 'function') {
            onDirtyChange('preferences', isDirty);
        }
    }, [isDirty, onDirtyChange]);

    // Initialise push diagnostics and PWA card state
    useEffect(() => {
        let mounted = true;
        const initPush = async () => {
            const state = getPushState();
            if (state === 'unsupported') {
                if (!mounted) return;
                setPushDisabled(true);
                setPushOn(false);
                setPushLabel('OFF');
                setDeviceBadge({ status: 'error', text: 'Push Unsupported' });
                const pwa = typeof window !== 'undefined' ? window.pwaManager : null;
                if (pwa && pwa.isIOS && !pwa.isStandalone) {
                    setPushHintMode('ios');
                } else {
                    setPushHintMode('unsupported');
                    setPushHintText('This browser does not support push notifications.');
                }
                return;
            }

            if (state === 'denied') {
                if (!mounted) return;
                setPushDisabled(true);
                setPushOn(false);
                setPushLabel('OFF');
                setDeviceBadge({ status: 'error', text: 'Blocked by Browser' });
                setPushHintMode('denied');
                return;
            }

            try {
                const subbed = await isPushSubscribed();
                if (!mounted) return;
                if (subbed) {
                    try { localStorage.removeItem(PUSH_OPT_OUT_KEY); } catch {}
                    setPushOn(true);
                    setPushLabel('ON');
                    setDeviceBadge({ status: 'active', text: 'Device Subscribed' });
                } else if (typeof localStorage !== 'undefined' && localStorage.getItem(PUSH_OPT_OUT_KEY) === '1') {
                    setPushOn(false);
                    setPushLabel('OFF');
                    setDeviceBadge({ status: 'warning', text: 'Turned Off' });
                } else if (state === 'granted') {
                    setPushOn(false);
                    setPushLabel('OFF');
                    setDeviceBadge({ status: 'warning', text: 'Ready — Click Re-sync' });
                } else {
                    setPushOn(false);
                    setPushLabel('OFF');
                    setDeviceBadge({ status: 'warning', text: 'Not Activated' });
                }
            } catch {
                if (!mounted) return;
                setPushOn(false);
                setPushLabel('OFF');
                setDeviceBadge({ status: 'warning', text: 'Not Activated' });
            }
        };

        initPush();

        const syncPwaCard = () => {
            if (typeof window === 'undefined' || !window.pwaManager) {
                setPwaState({ visible: false, installed: false });
                return;
            }
            const installed = Boolean(
                typeof window.pwaManager.isInstalled === 'function' && window.pwaManager.isInstalled()
            );
            setPwaState({ visible: true, installed });
        };

        syncPwaCard();

        const onAppInstalled = () => setPwaState({ visible: true, installed: true });
        if (typeof window !== 'undefined') {
            window.addEventListener('appinstalled', onAppInstalled);
            if (window.FamilyTooltip && typeof window.FamilyTooltip.getMode === 'function') {
                setTooltipMode(window.FamilyTooltip.getMode() || 'smart');
            }
        }

        return () => {
            mounted = false;
            if (typeof window !== 'undefined') {
                window.removeEventListener('appinstalled', onAppInstalled);
            }
        };
    }, []);

    const handleSubmit = async (e) => {
        if (e && e.preventDefault) e.preventDefault();
        if (saving) return;

        setSaving(true);
        setStatus({ type: 'info', message: '' });

        try {
            const payload = {
                action: 'updatePreferences',
                email_notifications: emailNotifs ? 'on' : 'off',
                post_email_notifications: postEmailNotifs ? 'on' : 'off',
                sms_notifications: smsNotifs ? 'on' : 'off',
            };
            const res = await postAccountForm(payload, bootstrap);
            if (res.ok) {
                onSavedAccountData({
                    email_notifications: payload.email_notifications,
                    post_email_notifications: payload.post_email_notifications,
                    sms_notifications: payload.sms_notifications,
                });
                if (typeof onDirtyChange === 'function') {
                    onDirtyChange('preferences', false);
                }
                setStatus({ type: 'success', message: res.message || 'Preferences successfully updated.' });
                toastMessage(res.message || 'Preferences successfully updated.', 'success');
            } else {
                setStatus({ type: 'error', message: res.message || 'Could not save preferences.' });
            }
        } catch (err) {
            setStatus({
                type: 'error',
                message: (err && err.message) || 'Network error while saving preferences.',
            });
        } finally {
            setSaving(false);
        }
    };

    const handlePushToggle = async (wantOn) => {
        if (pushBusy || pushDisabled) return;
        setPushBusy(true);
        try {
            if (wantOn) {
                try { localStorage.removeItem(PUSH_OPT_OUT_KEY); } catch {}
                const res = await enablePushNotifications();
                if (res && res.ok) {
                    setPushOn(true);
                    setPushLabel('ON');
                    setPushHintMode('text');
                    setPushHintText('Push notifications are on for this device.');
                    setDeviceBadge({ status: 'active', text: 'Device Subscribed' });
                    toastMessage('Push notifications enabled', 'success');
                } else {
                    setPushOn(false);
                    setPushLabel('OFF');
                    setPushHintMode('text');
                    setPushHintText(
                        res && res.reason === 'denied'
                            ? 'Notifications are blocked in your browser. Allow permissions in your address bar.'
                            : 'Could not enable notifications. Check your browser settings.'
                    );
                    if (typeof window !== 'undefined' && typeof window.showPushPermissionRecovery === 'function') {
                        window.showPushPermissionRecovery();
                    }
                }
            } else {
                try { localStorage.setItem(PUSH_OPT_OUT_KEY, '1'); } catch {}
                await disablePushNotifications();
                setPushOn(false);
                setPushLabel('OFF');
                setPushHintMode('text');
                setPushHintText('Push notifications are off for this device.');
                setDeviceBadge({ status: 'warning', text: 'Turned Off' });
                toastMessage('Push notifications turned off', 'success');
            }
        } catch {
            setPushOn(!wantOn);
        } finally {
            setPushBusy(false);
        }
    };

    const handleSendTestNotif = async () => {
        if (testBusy) return;
        setTestBusy(true);
        setTestFeedback({
            tone: 'muted',
            text: 'Transmitting live test alert to backend cascade...',
        });

        try {
            const data = await sendTestPushNotification(bootstrap);
            const rawMsg = data.message || (data.data && data.data.message);
            const msg = extractPushMsg(
                rawMsg,
                data.endpoints_registered
                    ? `Test notification dispatched to ${data.endpoints_registered} registered device(s).`
                    : 'Test alert sent successfully!'
            );
            setTestFeedback({ tone: 'success', text: msg });
            if (typeof window !== 'undefined' && window.Swal) {
                const hasBrowserBlock = typeof Notification !== 'undefined' && Notification.permission === 'denied';
                const alertText = hasBrowserBlock
                    ? `${msg}\n\n(Note: Notifications are currently blocked in this browser. To see OS banners on this device, allow notifications in your browser address bar.)`
                    : msg;
                window.Swal.fire({
                    icon: 'success',
                    title: 'Test Alert Sent!',
                    text: alertText,
                    confirmButtonColor: '#a31621',
                });
            }
        } catch (err) {
            setTestFeedback({
                tone: 'error',
                text: (err && err.message) || 'Test dispatch failed.',
            });
        } finally {
            setTestBusy(false);
        }
    };

    const handleResyncDevice = async () => {
        if (resyncBusy) return;
        setResyncBusy(true);
        try {
            const res = await enablePushNotifications();
            if (res && res.ok) {
                setPushOn(true);
                setPushLabel('ON');
                setPushHintMode('text');
                setPushHintText('Push notifications are on for this device.');
                setDeviceBadge({ status: 'active', text: 'Device Subscribed' });
                setTestFeedback({
                    tone: 'success',
                    text: 'Device endpoint successfully registered with server.',
                });
            } else if (
                (res && res.reason === 'denied') ||
                (typeof Notification !== 'undefined' && Notification.permission === 'denied')
            ) {
                setDeviceBadge({ status: 'error', text: 'Blocked by Browser' });
                setTestFeedback({
                    tone: 'error',
                    text: 'Notifications are blocked in your browser settings. Tap the lock icon in your address bar to allow notifications, then reload.',
                });
                if (typeof window !== 'undefined' && typeof window.showPushPermissionRecovery === 'function') {
                    window.showPushPermissionRecovery();
                }
            } else {
                setDeviceBadge({ status: 'warning', text: 'Re-sync Skipped' });
            }
        } finally {
            setResyncBusy(false);
        }
    };

    const handleTooltipModeChange = (mode) => {
        setTooltipMode(mode);
        if (typeof window !== 'undefined' && window.FamilyTooltip && typeof window.FamilyTooltip.setMode === 'function') {
            window.FamilyTooltip.setMode(mode);
        }
        toastMessage(`Tooltip mode updated to ${mode.toUpperCase()}`, 'success');
    };

    const handleResetTooltipMastery = () => {
        if (typeof window !== 'undefined' && window.FamilyTooltip && typeof window.FamilyTooltip.resetMastery === 'function') {
            window.FamilyTooltip.resetMastery();
        }
        setTooltipResetDone(true);
        setTimeout(() => setTooltipResetDone(false), 2200);
        toastMessage('Learned icon memory reset. All tooltips will show again.', 'info');
    };

    const badgeClass =
        deviceBadge.status === 'active'
            ? 'badge bg-success text-white'
            : deviceBadge.status === 'warning'
            ? 'badge bg-warning text-dark'
            : deviceBadge.status === 'error'
            ? 'badge bg-danger text-white'
            : 'badge bg-light text-secondary border';

    return (
        <div>
            <div className={styles.sectionTitle}>Notification Preferences</div>
            <div className={styles.sectionSubtitle}>Choose which notifications you want to receive.</div>
            <div className={styles.sectionDivider} />

            <StatusAlert
                id="preferencesForm_notification"
                errorId="preferencesForm_notification_error"
                message={status.message}
                type={status.type}
            />

            <form id="preferencesForm" onSubmit={handleSubmit}>
                <input type="hidden" name="action" value="updatePreferences" />
                <CsrfFields token={csrfToken} />

                <SettingsSwitch
                    id="pref_email_notifications"
                    name="email_notifications"
                    label="Email Notifications"
                    description="Receive alerts about events, digests, and account activities via email."
                    checked={emailNotifs}
                    onChange={setEmailNotifs}
                />

                <SettingsSwitch
                    id="pref_post_email_notifications"
                    name="post_email_notifications"
                    label="New Post Email Alerts"
                    description="Opt in to receive an immediate email for every new family post (off by default — weekly digests and push alerts remain active)."
                    checked={postEmailNotifs}
                    onChange={setPostEmailNotifs}
                />

                <SettingsSwitch
                    id="pref_sms_notifications"
                    name="sms_notifications"
                    label="SMS Notifications"
                    description="Receive critical alerts directly to your phone."
                    checked={smsNotifs}
                    onChange={setSmsNotifs}
                />

                <div className={styles.switchRow}>
                    <div className={styles.switchInfo}>
                        <h6>Browser &amp; Mobile Notifications</h6>
                        <p id="pushPrefHint">
                            {pushHintMode === 'ios' ? (
                                <>
                                    On iPhone, notifications work once the app is on your Home Screen.{' '}
                                    <a
                                        href="#install-ios"
                                        id="pushIosInstall"
                                        style={{ fontWeight: 600, textDecoration: 'underline', color: 'inherit' }}
                                        onClick={(e) => {
                                            e.preventDefault();
                                            if (window.pwaManager && window.pwaManager.showIOSOverlay) {
                                                window.pwaManager.showIOSOverlay();
                                            }
                                        }}
                                    >
                                        Show me how
                                    </a>
                                    .
                                </>
                            ) : pushHintMode === 'denied' ? (
                                <>
                                    Notifications are blocked in your browser settings.{' '}
                                    <a
                                        href="#push-recovery"
                                        id="showPushRecoveryLink"
                                        style={{ fontWeight: 600, textDecoration: 'underline', color: '#a31621' }}
                                        onClick={(e) => {
                                            e.preventDefault();
                                            if (typeof window.showPushPermissionRecovery === 'function') {
                                                window.showPushPermissionRecovery();
                                            }
                                        }}
                                    >
                                        How to re-enable
                                    </a>
                                </>
                            ) : (
                                pushHintText
                            )}
                        </p>
                    </div>
                    <div className={styles.toggleGroup}>
                        <span className={styles.toggleLabel} id="pushPrefLabel">
                            {pushLabel}
                        </span>
                        <label className={styles.switchControl}>
                            <input
                                type="checkbox"
                                id="pushPrefToggle"
                                data-standalone="1"
                                checked={pushOn}
                                disabled={pushBusy || pushDisabled}
                                onChange={(e) => handlePushToggle(e.currentTarget.checked)}
                                aria-label="Browser and mobile push notifications"
                            />
                            <span className={styles.switchSlider} />
                        </label>
                    </div>
                </div>

                {/* Self-Serve Notification Diagnostics & Device Test Card */}
                <div className={`p-3 mt-3 ${styles.subcard}`} id="pushDiagnosticsCard">
                    <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <span className="fw-bold" style={{ fontSize: '0.9rem', color: 'var(--as-ink)' }}>
                            <i className="bi bi-shield-check text-success me-2" aria-hidden="true" />
                            Device Delivery &amp; Push Diagnostics
                        </span>
                        <span id="pushDeviceBadge" className={badgeClass}>
                            {deviceBadge.text}
                        </span>
                    </div>
                    <p className="small mb-3" style={{ color: 'var(--as-muted)', lineHeight: 1.45 }}>
                        Verify whether this device is actively registered to receive lockscreen and in-app milestone alerts.
                    </p>
                    <div className="d-flex flex-wrap gap-2">
                        <button
                            type="button"
                            id="sendTestNotifBtn"
                            className={`btn btn-sm btn-outline-danger rounded-pill px-3 ${styles.actionBtn}`}
                            disabled={testBusy}
                            onClick={handleSendTestNotif}
                        >
                            {testBusy ? (
                                <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                            ) : (
                                <i className="bi bi-broadcast" aria-hidden="true" />
                            )}
                            <span>{testBusy ? 'Sending Test...' : 'Send Test Alert to This Device'}</span>
                        </button>
                        <button
                            type="button"
                            id="resyncDeviceBtn"
                            className={`btn btn-sm btn-outline-secondary rounded-pill px-3 ${styles.actionBtn}`}
                            disabled={resyncBusy}
                            onClick={handleResyncDevice}
                        >
                            {resyncBusy ? (
                                <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                            ) : (
                                <i className="bi bi-arrow-clockwise" aria-hidden="true" />
                            )}
                            <span>{resyncBusy ? 'Re-syncing...' : 'Re-sync Device Endpoint'}</span>
                        </button>
                    </div>
                    {testFeedback && (
                        <div
                            id="testNotifFeedback"
                            className={`small mt-2 fw-medium ${
                                testFeedback.tone === 'success'
                                    ? 'text-success'
                                    : testFeedback.tone === 'error'
                                    ? 'text-danger'
                                    : ''
                            }`}
                            style={testFeedback.tone === 'muted' ? { color: 'var(--as-muted)' } : undefined}
                        >
                            {testFeedback.text}
                        </div>
                    )}
                </div>

                {/* Ambient PWA Installation Card */}
                {pwaState.visible && (
                    <div className={`p-3 mt-3 ${styles.subcard}`} id="pwaAccountSettingCard">
                        <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <span className="fw-bold" style={{ fontSize: '0.9rem', color: 'var(--as-ink)' }}>
                                <i className="bi bi-phone text-primary me-2" aria-hidden="true" />
                                FamilyPlatform App Experience
                            </span>
                            <span
                                id="pwaInstallStateBadge"
                                className={
                                    pwaState.installed
                                        ? 'badge bg-success text-white'
                                        : 'badge bg-primary text-white'
                                }
                            >
                                {pwaState.installed ? 'App Installed' : 'PWA Ready'}
                            </span>
                        </div>
                        <p className="small mb-3" style={{ color: 'var(--as-muted)', lineHeight: 1.45 }} id="pwaAccountSettingHint">
                            {pwaState.installed
                                ? 'You are currently running FamilyPlatform in native installed app mode. Automatic background updates and offline storage are active.'
                                : 'Install FamilyPlatform directly to your home screen or desktop for 1-tap fast access, instant milestone notifications, and offline viewing.'}
                        </p>
                        {!pwaState.installed && (
                            <div className="d-flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    id="pwaAccountSettingInstallBtn"
                                    className={`btn btn-sm btn-primary pwa-ambient-install-trigger rounded-pill px-3 fw-semibold ${styles.actionBtn}`}
                                    onClick={() => {
                                        if (window.pwaManager && typeof window.pwaManager.triggerAppInstall === 'function') {
                                            window.pwaManager.triggerAppInstall();
                                        }
                                    }}
                                >
                                    <i className="bi bi-download" aria-hidden="true" />
                                    <span>Install App on This Device</span>
                                </button>
                            </div>
                        )}
                    </div>
                )}

                {/* Icon & Button Tooltip Frequency & Guidance Control */}
                <div className={`${styles.switchRow} mt-3 flex-wrap`}>
                    <div className={styles.switchInfo} style={{ flex: '1 1 260px' }}>
                        <h6>
                            <i className="bi bi-info-circle-fill text-primary me-2" aria-hidden="true" />
                            Icon &amp; Button Explanatory Tooltips
                        </h6>
                        <p>Manage how frequently guidance tooltips appear when hovering over icons and buttons across the platform.</p>
                    </div>
                    <div className="d-flex align-items-center gap-2">
                        <label htmlFor="tooltipModeSelect" className="visually-hidden">
                            Tooltip display frequency
                        </label>
                        <select
                            id="tooltipModeSelect"
                            className={`form-select form-select-sm ${styles.formSelect}`}
                            style={{ minWidth: '215px' }}
                            value={tooltipMode}
                            onChange={(e) => handleTooltipModeChange(e.currentTarget.value)}
                        >
                            <option value="smart">Smart (Hides Once Learned)</option>
                            <option value="always">Always Show (Guided Mode)</option>
                            <option value="off">Off (Minimal UI)</option>
                        </select>
                    </div>
                </div>

                <div className="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 mb-4">
                    <small style={{ color: 'var(--as-muted)', maxWidth: '460px', lineHeight: 1.4 }}>
                        In <strong>Smart</strong> mode, tooltips appear only if you hesitate for &gt;450ms, and disappear once you click or view them 3 times.
                    </small>
                    <button
                        type="button"
                        id="resetTooltipMasteryBtn"
                        className={`btn btn-sm rounded-pill px-3 ${
                            tooltipResetDone ? 'btn-success' : 'btn-outline-secondary'
                        } ${styles.actionBtn}`}
                        title="Reset your learned icons to view all tooltips again"
                        onClick={handleResetTooltipMastery}
                    >
                        <i
                            className={`bi ${tooltipResetDone ? 'bi-check2' : 'bi-arrow-counterclockwise'}`}
                            aria-hidden="true"
                        />
                        <span>{tooltipResetDone ? 'Reset Complete!' : 'Reset Learned Icons'}</span>
                    </button>
                </div>

                <SubmitButton
                    id="preferencesBtn"
                    type="submit"
                    loading={saving}
                    className="w-100 mt-2"
                >
                    Save Preferences
                </SubmitButton>
            </form>
        </div>
    );
}
