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
  { formId: 'profileForm', buttonId: 'profileBtn' },
  { formId: 'accountSettingForm', buttonId: 'button' }, // backward compatibility
  { formId: 'parentsForm', buttonId: 'parentsBtn' },
  { formId: 'childrenForm', buttonId: 'childrenBtn' },
  { formId: 'siblingsForm', buttonId: 'siblingsBtn' },
  { formId: 'maritalForm', buttonId: 'maritalBtn' },
  { formId: 'passwordForm', buttonId: 'passwordBtn' },
  { formId: 'preferencesForm', buttonId: 'preferencesBtn' },
  { formId: 'privacyForm', buttonId: 'privacyBtn' },
];

formsToRegister.forEach(({ formId, buttonId }) => {
  if (id(formId) && id(buttonId)) {
    update({
      formId,
      route: '/accountSetting',
      buttonId,
      redirect: '/accountSetting'
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

// URL Hash navigation support for deep-linking (e.g., /accountSetting#family-settings or #parents)
const handleHashNavigation = () => {
  const hash = window.location.hash;
  if (!hash) return;

  const hashMap = {
    '#profile': 'v-pills-profile-tab',
    '#parents': 'v-pills-parents-tab',
    '#family-settings': 'v-pills-parents-tab',
    '#children': 'v-pills-children-tab',
    '#siblings': 'v-pills-siblings-tab',
    '#marital': 'v-pills-marital-tab',
    '#marital-status': 'v-pills-marital-tab',
    '#password': 'v-pills-password-tab',
    '#preferences': 'v-pills-preferences-tab',
    '#privacy': 'v-pills-privacy-tab',
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
    }
  }
};

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

  // ---- GDPR Art. 17 — request account deletion --------------------------
  const delBtn = id('dataDeleteBtn');
  if (delBtn) {
    delBtn.addEventListener('click', async () => {
      const confirmed = window.Swal
        ? (await window.Swal.fire({
            icon: 'warning',
            title: 'Request account deletion?',
            html: 'Our team will permanently delete your account and personal data within 30 days. '
              + 'Content other family members rely on may be kept in anonymised form. This cannot be undone.',
            showCancelButton: true,
            confirmButtonText: 'Yes, request deletion',
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Cancel',
          })).isConfirmed
        : window.confirm('Request permanent deletion of your account? Our team will action it within 30 days.');
      if (!confirmed) return;

      delBtn.disabled = true;
      say('Sending your request…');
      try {
        const res = await fetch('/account/request-deletion', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': getCsrfToken(),
          'X-CSRF-TOKEN': getCsrfToken(),
          },
        });
        const body = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(body?.message || `Request failed (${res.status})`);
        say(body?.message || 'Your deletion request has been received.');
        window.notify?.(body?.message || 'Deletion request received', 'success');
      } catch (err) {
        showError(err);
        say('Sorry — we could not send your request. Please try again or email support.');
      } finally {
        delBtn.disabled = false;
      }
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

  isPushSubscribed().then((subbed) => paint(subbed));

  toggle.addEventListener('change', async () => {
    toggle.disabled = true;
    const wantOn = toggle.checked;
    try {
      if (wantOn) {
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


