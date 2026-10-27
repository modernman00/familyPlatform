"use strict";
import "./accountSettingHelpers/handleFamilyChangeBootstrap"
import { id, showError, manipulateAttribute, showElement, hideElement, getCsrfToken } from "./global"
import { loginSubmission, update } from '@modernman00/shared-js-lib'
import { processKidsSiblings } from './kidsAndSiblings';
import {
  enablePushNotifications,
  disablePushNotifications,
  getPushState,
  isPushSubscribed,
} from './profilePage/registerPushNotification';

// Register update handlers for all account & family forms
const formsToRegister = [
  { formId: 'profileForm', buttonId: 'profileBtn', redirect: '/accountSetting#v-pills-profile' },
  { formId: 'accountSettingForm', buttonId: 'button', redirect: '/accountSetting' }, // backward compatibility
  { formId: 'parentsForm', buttonId: 'parentsBtn', redirect: '/accountSetting#v-pills-parents' },
  { formId: 'childrenForm', buttonId: 'childrenBtn', redirect: '/accountSetting#v-pills-children' },
  { formId: 'siblingsForm', buttonId: 'siblingsBtn', redirect: '/accountSetting#v-pills-siblings' },
  { formId: 'maritalForm', buttonId: 'maritalBtn', redirect: '/accountSetting#v-pills-marital' },
  { formId: 'passwordForm', buttonId: 'passwordBtn', redirect: '/accountSetting#v-pills-password' },
  { formId: 'preferencesForm', buttonId: 'preferencesBtn', redirect: '/accountSetting#v-pills-preferences' },
  { formId: 'privacyForm', buttonId: 'privacyBtn', redirect: '/accountSetting#v-pills-privacy' },
];

formsToRegister.forEach(({ formId, buttonId, redirect }) => {
  if (id(formId) && id(buttonId)) {
    update({
      formId,
      route: '/accountSetting',
      buttonId,
      redirect: redirect || '/accountSetting'
    });
  }
});

// Function to show/hide spouse information based on marital status
const showSpouse = () => {
  const maritalEl = id('maritalStatus');
  if (!maritalEl) return;
  const maritalStatus = maritalEl.value;

  if (maritalStatus === "Yes - Add Husband") {
    showElement('spouse');
    hideElement('maiden_name_div');
  } else if (maritalStatus === "Yes - Add Wife") {
    showElement('maiden_name_div');
    showElement('spouse');
  } else if (maritalStatus === "Divorced" || maritalStatus === "Widowed") {
    // Former partner: same details, plus their gender (cannot be inferred)
    showElement('spouse');
    hideElement('maiden_name_div');
  } else {
    hideElement('spouse');
    hideElement('maiden_name_div');
  }
  const gDiv = id('spouse_gender_div');
  if (gDiv) gDiv.style.display = (maritalStatus === "Divorced" || maritalStatus === "Widowed") ? '' : 'none';
};

const maritalSelect = id('maritalStatus');
if (maritalSelect) {
  maritalSelect.addEventListener('change', showSpouse);
  // Initial check on load
  showSpouse();
}

// URL Hash navigation and direct Family Connections tab scroll support
const scrollToFormPane = (tabId) => {
  const formMap = {
    'v-pills-parents-tab': 'v-pills-parents',
    'v-pills-children-tab': 'v-pills-children',
    'v-pills-siblings-tab': 'v-pills-siblings',
    'v-pills-marital-tab': 'v-pills-marital',
    'v-pills-familycode-tab': 'v-pills-familycode',
  };
  const targetPaneId = formMap[tabId];
  if (!targetPaneId) return;
  
  setTimeout(() => {
    const pane = id(targetPaneId);
    if (pane) {
      pane.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }, 100);
};

const handleHashNavigation = () => {
  const hash = window.location.hash;
  if (!hash) return;

  const hashMap = {
    '#profile': 'v-pills-profile-tab',
    '#v-pills-profile': 'v-pills-profile-tab',
    '#parents': 'v-pills-parents-tab',
    '#v-pills-parents': 'v-pills-parents-tab',
    '#family-settings': 'v-pills-parents-tab',
    '#children': 'v-pills-children-tab',
    '#v-pills-children': 'v-pills-children-tab',
    '#siblings': 'v-pills-siblings-tab',
    '#v-pills-siblings': 'v-pills-siblings-tab',
    '#marital': 'v-pills-marital-tab',
    '#v-pills-marital': 'v-pills-marital-tab',
    '#marital-status': 'v-pills-marital-tab',
    '#password': 'v-pills-password-tab',
    '#v-pills-password': 'v-pills-password-tab',
    '#preferences': 'v-pills-preferences-tab',
    '#v-pills-preferences': 'v-pills-preferences-tab',
    '#privacy': 'v-pills-privacy-tab',
    '#v-pills-privacy': 'v-pills-privacy-tab',
    '#familycode': 'v-pills-familycode-tab',
    '#v-pills-familycode': 'v-pills-familycode-tab',
    '#family-code': 'v-pills-familycode-tab',
    '#family-network': 'v-pills-familycode-tab',
  };

  const targetTabId = hashMap[hash.toLowerCase()];
  if (targetTabId) {
    const tabBtn = id(targetTabId);
    if (tabBtn) {
      if (typeof window !== 'undefined' && window.bootstrap?.Tab) {
        const tab = new window.bootstrap.Tab(tabBtn);
        tab.show();
      } else {
        tabBtn.click();
      }
      scrollToFormPane(targetTabId);
    }
  }
};

// Bind click listeners on Family Connections tabs for instant smooth scrolling directly to form below
['v-pills-parents-tab', 'v-pills-children-tab', 'v-pills-siblings-tab', 'v-pills-marital-tab', 'v-pills-familycode-tab'].forEach((btnId) => {
  const btn = id(btnId);
  if (btn) {
    btn.addEventListener('click', () => scrollToFormPane(btnId));
  }
});

window.addEventListener('DOMContentLoaded', handleHashNavigation);
window.addEventListener('hashchange', handleHashNavigation);

// ── Lineage Scope Radio Buttons ────────────────────────────────────────────
// Wires the "Marital Tree / Birth Family" radio buttons in the Parents and
// Siblings tabs to the hidden `lineage_scope` and `lineage_code` form fields.
// When the user switches to "maternal", the sibling/parent invite emails will
// use their otherFamCode instead of the primary marital family code.
(function initLineageScopePickers() {
  const primaryCode = (id('famCode_id')?.value || '').trim();
  const maternalCode = (id('otherFamCode_id')?.value || '').trim();

  const TABS = [
    {
      primaryRadioId:  'parents-scope-primary',
      maternalRadioId: 'parents-scope-maternal',
      scopeFieldId:    'parentsLineageScope',
      codeFieldId:     'parentsLineageCode',
      primaryLabelId:  'parents-scope-primary-label',
      maternalLabelId: 'parents-scope-maternal-label',
      hintId:          'parents-lineage-hint',
      saveBtnId:       'parentsBtn',
      entityLabel:     'Parents',
    },
    {
      primaryRadioId:  'siblings-scope-primary',
      maternalRadioId: 'siblings-scope-maternal',
      scopeFieldId:    'siblingsLineageScope',
      codeFieldId:     'siblingsLineageCode',
      primaryLabelId:  'siblings-scope-primary-label',
      maternalLabelId: 'siblings-scope-maternal-label',
      hintId:          'siblings-lineage-hint',
      saveBtnId:       'siblingsBtn',
      entityLabel:     'Siblings',
    },
  ];

  TABS.forEach(({ primaryRadioId, maternalRadioId, scopeFieldId, codeFieldId, primaryLabelId, maternalLabelId, hintId, saveBtnId, entityLabel }) => {
    const primaryRadio  = id(primaryRadioId);
    const maternalRadio = id(maternalRadioId);
    const scopeField    = id(scopeFieldId);
    const codeField     = id(codeFieldId);
    const primaryLabel  = id(primaryLabelId);
    const maternalLabel = id(maternalLabelId);
    const hintEl        = id(hintId);
    const saveBtn       = id(saveBtnId);

    if (!primaryRadio || !maternalRadio || !scopeField) return;

    const applyStyles = (selected) => {
      if (primaryLabel)  { primaryLabel.style.borderColor  = selected === 'primary'  ? '#a31621' : ''; primaryLabel.style.background  = selected === 'primary'  ? '#fbefef' : ''; }
      if (maternalLabel) { maternalLabel.style.borderColor = selected === 'maternal' ? '#16a34a' : ''; maternalLabel.style.background = selected === 'maternal' ? '#f0fdf4' : ''; }

      const activeCode = selected === 'maternal' ? (codeField?.value || maternalCode) : primaryCode;
      const codeBadge = activeCode ? ` (#${activeCode})` : '';

      if (hintEl) {
        if (selected === 'maternal') {
          hintEl.innerHTML = `${entityLabel} will be added to your <strong>Birth / Maiden Family${codeBadge}</strong>, and any invitation sent will use your birth family code.`;
        } else {
          hintEl.innerHTML = `${entityLabel} will be added to your <strong>Marital / Household Tree${codeBadge}</strong>, and any invitation sent will use your marital family code.`;
        }
      }

      if (saveBtn) {
        saveBtn.textContent = selected === 'maternal'
          ? `Save ${entityLabel} to Birth Family${codeBadge}`
          : `Save ${entityLabel} to Marital Tree${codeBadge}`;
      }
    };

    const onPrimary = () => {
      scopeField.value = 'primary';
      applyStyles('primary');
    };
    const onMaternal = () => {
      scopeField.value = 'maternal';
      // Ensure lineage_code is populated (may already be set server-side)
      if (codeField && !codeField.value) {
        const otherEl = id('otherFamCode_id');
        codeField.value = otherEl?.value ?? '';
      }
      applyStyles('maternal');
    };

    primaryRadio.addEventListener('change',  onPrimary);
    maternalRadio.addEventListener('change', onMaternal);

    // Apply initial visual state
    const initialScope = maternalRadio.checked ? 'maternal' : 'primary';
    scopeField.value = initialScope;
    applyStyles(initialScope);
  });
})();

// Kid / sibling email helper — checks each address against the server on demand
// (no bulk email list is pulled to the browser any more, SEC-4).
const fNameEl = id('fName');
const fName = fNameEl ? (fNameEl.textContent || '').trim() : '';
const famCode = localStorage.getItem('requesterFamCode');

processKidsSiblings(fName, famCode);

// ---- GDPR Art. 15 — "Download my data" ----------------------------------
(function initDataExport() {
  const btn = id('dataExportBtn');
  const status = id('dataExportStatus');
  if (!btn) return;

  const say = (msg) => {
    if (!status) return;
    status.textContent = msg;
    status.style.display = msg ? 'block' : 'none';
  };

  btn.addEventListener('click', async () => {
    btn.disabled = true;
    say('Preparing your data…');
    try {
      const res = await fetch('/account/data-export', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': getCsrfToken(),
          'X-CSRF-TOKEN': getCsrfToken(),
        },
      });
      if (!res.ok) throw new Error(`Export failed (${res.status})`);

      const blob = await res.blob();
      const cd = res.headers.get('Content-Disposition') || '';
      const name = /filename="?([^"]+)"?/.exec(cd)?.[1] || 'familyplatform-data.json';

      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = name;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
      say('Download started. Check your downloads folder.');
    } catch (err) {
      showError(err);
      say('Sorry — we could not prepare your data. Please try again.');
    } finally {
      btn.disabled = false;
    }
  });

  // ---- GDPR Art. 17 — Account deletion options (Immediate vs 30-Day Grace Period) ----
  const delBtn = id('dataDeleteBtn');
  if (delBtn) {
    const executeDeletion = async (mode) => {
      delBtn.disabled = true;
      say('Processing your deletion request…');
      try {
        const res = await fetch('/account/request-deletion', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': getCsrfToken(),
            'X-CSRF-TOKEN': getCsrfToken(),
          },
          body: JSON.stringify({ mode }),
        });
        const body = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(body?.message || `Request failed (${res.status})`);

        say(body?.message || 'Your deletion request has been processed.');

        if (window.Swal) {
          await window.Swal.fire({
            icon: 'success',
            title: mode === 'immediate' ? 'Account Permanently Erased' : 'Deletion Scheduled',
            text: body?.message || 'Your account deletion request has been processed.',
            confirmButtonColor: '#a31621',
            confirmButtonText: 'Return to Sign In',
          });
        }

        window.location.href = body?.redirect || '/login';
      } catch (err) {
        showError(err);
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error',
            title: 'Deletion Request Failed',
            text: err.message || 'Sorry — we could not process your deletion request. Please try again or contact support.',
          });
        }
        say('Sorry — we could not process your request. Please try again or email support.');
      } finally {
        delBtn.disabled = false;
      }
    };

    delBtn.addEventListener('click', async () => {
      if (!window.Swal) {
        const choice = window.confirm(
          'Do you want to permanently erase your account immediately (Click OK)? Or cancel (Click Cancel)?'
        );
        if (choice) executeDeletion('immediate');
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
        title: isImmediate ? 'Confirm Immediate Permanent Erasure?' : 'Confirm 30-Day Scheduled Deletion?',
        html: isImmediate
          ? 'Your personal data, media, and credentials will be permanently erased immediately. You will not be able to recover or sign into this account.'
          : 'Your account will be deactivated and scheduled for deletion in 30 days. You will be signed out now. You may sign back in at any point within 30 days to cancel this request.',
        showCancelButton: true,
        confirmButtonText: isImmediate ? 'Yes, erase my account now' : 'Yes, schedule deletion',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Keep My Account',
      });

      if (!confirmResult.isConfirmed) return;

      executeDeletion(selectedOption);
    });
  }
})();

// ---- Browser / mobile push notification toggle (PUSH-1) ------------------
(function initPushToggle() {
  const toggle = id('pushPrefToggle');
  const label = id('pushPrefLabel');
  const hint = id('pushPrefHint');
  const deviceBadge = id('pushDeviceBadge');
  const testBtn = id('sendTestNotifBtn');
  const resyncBtn = id('resyncDeviceBtn');
  const testFeedback = id('testNotifFeedback');
  if (!toggle) return;

  const DEFAULT_HINT = 'Get instant alerts on this device even when the app is closed.';
  const PUSH_OPT_OUT_KEY = 'fp_push_pref_disabled';

  const updateBadge = (status, text) => {
    if (!deviceBadge) return;
    deviceBadge.className = 'badge border ';
    if (status === 'active') {
      deviceBadge.className += 'bg-success text-white';
      deviceBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i> ' + (text || 'Device Active');
    } else if (status === 'warning') {
      deviceBadge.className += 'bg-warning text-dark';
      deviceBadge.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> ' + (text || 'Needs Activation');
    } else if (status === 'error') {
      deviceBadge.className += 'bg-danger text-white';
      deviceBadge.innerHTML = '<i class="bi bi-x-circle me-1"></i> ' + (text || 'Blocked by Browser');
    } else {
      deviceBadge.className += 'bg-light text-secondary';
      deviceBadge.innerHTML = text || 'Checking...';
    }
  };

  const paint = (on, note) => {
    toggle.checked = on;
    if (label) label.textContent = on ? 'ON' : 'OFF';
    if (hint) hint.textContent = note || DEFAULT_HINT;
  };

  const state = getPushState();
  if (state === 'unsupported') {
    toggle.disabled = true;
    updateBadge('error', 'Push Unsupported');
    const pwa = window.pwaManager;
    if (pwa && pwa.isIOS && !pwa.isStandalone) {
      if (hint) {
        hint.innerHTML =
          'On iPhone, notifications work once the app is on your Home Screen. ' +
          '<a href="#" id="pushIosInstall" style="font-weight:600;text-decoration:underline;">Show me how</a>.';
        const link = id('pushIosInstall');
        if (link) {
          link.addEventListener('click', (e) => {
            e.preventDefault();
            pwa.showIOSOverlay?.();
          });
        }
      }
    } else {
      paint(false, 'This browser does not support push notifications.');
    }
    return;
  }
  if (state === 'denied') {
    toggle.disabled = true;
    updateBadge('error', 'Blocked by Browser');
    paint(false, 'Notifications are blocked in your browser settings. Re-enable them there, then reload this page.');
    return;
  }

  isPushSubscribed().then((subbed) => {
    if (subbed) {
      localStorage.removeItem(PUSH_OPT_OUT_KEY);
      paint(true);
      updateBadge('active', 'Device Subscribed');
    } else if (localStorage.getItem(PUSH_OPT_OUT_KEY) === '1') {
      paint(false);
      updateBadge('warning', 'Turned Off');
    } else {
      if (state === 'granted') {
        updateBadge('warning', 'Connecting...');
        enablePushNotifications().then((res) => {
          if (res.ok) {
            paint(true);
            updateBadge('active', 'Device Subscribed');
          } else {
            paint(false);
            updateBadge('warning', 'Subscription Pending');
          }
        }).catch(() => {
          paint(false);
          updateBadge('warning', 'Subscription Pending');
        });
      } else {
        paint(false);
        updateBadge('warning', 'Not Activated');
      }
    }
  });

  // Attach Test Notification Trigger
  if (testBtn) {
    testBtn.addEventListener('click', async () => {
      const origHtml = testBtn.innerHTML;
      testBtn.disabled = true;
      testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Sending Test...';
      if (testFeedback) {
        testFeedback.style.display = 'block';
        testFeedback.className = 'small mt-2 text-muted';
        testFeedback.textContent = 'Transmitting live test alert to backend cascade...';
      }

      try {
        const res = await fetch('/api/notifications/test', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-XSRF-TOKEN': getCsrfToken()
          },
          body: JSON.stringify({ token: getCsrfToken() })
        });
        const data = await res.json();

        if (res.ok && data.status === 'success') {
          const msg = data.data?.message || data.message || 'Test notification sent!';
          if (testFeedback) {
            testFeedback.className = 'small mt-2 text-success fw-medium';
            testFeedback.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + msg;
          }
          if (window.Swal) {
            window.Swal.fire({
              icon: 'success',
              title: 'Test Alert Sent! 🎉',
              text: msg,
              confirmButtonColor: '#a31621'
            });
          }
        } else {
          throw new Error(data.message || 'Could not dispatch test notification');
        }
      } catch (err) {
        if (testFeedback) {
          testFeedback.className = 'small mt-2 text-danger fw-medium';
          testFeedback.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> ' + (err.message || 'Test dispatch failed');
        }
      } finally {
        testBtn.disabled = false;
        testBtn.innerHTML = origHtml;
      }
    });
  }

  // Attach Re-sync Device Trigger
  if (resyncBtn) {
    resyncBtn.addEventListener('click', async () => {
      const origHtml = resyncBtn.innerHTML;
      resyncBtn.disabled = true;
      resyncBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Re-syncing...';

      try {
        const res = await enablePushNotifications();
        if (res.ok) {
          paint(true, 'Push notifications are on for this device.');
          updateBadge('active', 'Device Subscribed');
          if (testFeedback) {
            testFeedback.style.display = 'block';
            testFeedback.className = 'small mt-2 text-success';
            testFeedback.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Device endpoint successfully registered with server.';
          }
        } else {
          updateBadge('warning', 'Re-sync Skipped');
        }
      } catch (err) {
        showError(err);
      } finally {
        resyncBtn.disabled = false;
        resyncBtn.innerHTML = origHtml;
      }
    });
  }

  toggle.addEventListener('change', async () => {
    toggle.disabled = true;
    const wantOn = toggle.checked;
    try {
      if (wantOn) {
        localStorage.removeItem(PUSH_OPT_OUT_KEY);
        const res = await enablePushNotifications();
        if (res.ok) {
          paint(true, 'Push notifications are on for this device.');
          window.notify?.('Push notifications enabled', 'success');
        } else if (res.reason === 'denied') {
          paint(false, 'You dismissed the browser prompt. Allow notifications to turn this on.');
        } else {
          paint(false, 'Could not enable notifications. Please try again.');
        }
      } else {
        localStorage.setItem(PUSH_OPT_OUT_KEY, '1');
        await disablePushNotifications();
        paint(false, 'Push notifications are off for this device.');
        window.notify?.('Push notifications turned off', 'success');
      }
    } catch (err) {
      showError(err);
      paint(!wantOn);
    } finally {
      toggle.disabled = false;
    }
  });
})();


