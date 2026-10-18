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
  } else {
    hideElement('spouse');
    hideElement('maiden_name_div');
  }
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
            confirmButtonColor: '#0284c7',
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
            <div class="p-3 border rounded-3" style="background-color: #fff5f5; border-color: #fecaca !important; cursor: pointer;">
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
  if (!toggle) return;

  const DEFAULT_HINT = 'Get instant alerts on this device even when the app is closed.';
  const PUSH_OPT_OUT_KEY = 'fp_push_pref_disabled';

  const paint = (on, note) => {
    toggle.checked = on;
    if (label) label.textContent = on ? 'ON' : 'OFF';
    if (hint) hint.textContent = note || DEFAULT_HINT;
  };

  const state = getPushState();
  if (state === 'unsupported') {
    toggle.disabled = true;
    const pwa = window.pwaManager;
    if (pwa && pwa.isIOS && !pwa.isStandalone) {
      // iOS only delivers Web Push to an installed PWA (iOS 16.4+). Route the
      // user through the existing "Add to Home Screen" walkthrough (PUSH-3).
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
    paint(false, 'Notifications are blocked in your browser settings. Re-enable them there, then reload this page.');
    return;
  }

  isPushSubscribed().then((subbed) => {
    if (subbed) {
      localStorage.removeItem(PUSH_OPT_OUT_KEY);
      paint(true);
    } else if (localStorage.getItem(PUSH_OPT_OUT_KEY) === '1') {
      paint(false);
    } else {
      paint(true);
      if (state === 'granted') {
        enablePushNotifications().catch(() => {});
      }
    }
  });

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


