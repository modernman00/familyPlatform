import { setupPasswordChange } from '@modernman00/shared-js-lib';

// Defensive safeguard: ensure #setLoader exists for legacy shared-js-lib showLoader/clearLoader
if (typeof document !== 'undefined' && !document.getElementById('setLoader')) {
  const dummyLoader = document.createElement('div');
  dummyLoader.id = 'setLoader';
  dummyLoader.style.display = 'none';
  dummyLoader.className = 'd-none';
  dummyLoader.setAttribute('aria-hidden', 'true');
  document.body.appendChild(dummyLoader);
}

setupPasswordChange({
  formId: 'changePW',
  buttonId: 'button',
  passwordId: 'password',
  confirmId: 'confirm_password',
  showToggleId: 'showPassword',
  helpId: 'non_existent_help',
  route: '/changePW',
  redirect: '/login',
  optionalFields: ['token', 'showPassword', 'confirm_password']
});

