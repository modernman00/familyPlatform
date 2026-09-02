"use strict";
import "./accountSettingHelpers/handleFamilyChangeBootstrap"
import { id, showError, manipulateAttribute, showElement, hideElement } from "./global"
import { loginSubmission, update } from '@modernman00/shared-js-lib'
import { processKidsSiblings } from './kidsAndSiblings';
import { fetchEmailData } from './api/index';

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

// GET ALL EMAILS
const fNameEl = id('fName');
const fName = fNameEl ? (fNameEl.textContent || '').trim() : '';
const famCode = localStorage.getItem('requesterFamCode');

fetchEmailData()
  .then(data => {
    const emailData = data;
    processKidsSiblings(emailData, fName, famCode);
  })
  .catch(error => {
    console.error('Error fetching email data:', error);
  });


