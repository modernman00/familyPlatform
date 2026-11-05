import { createAdminLoginHandler } from "@modernman00/shared-js-lib";

// Defensive safeguard: ensure #setLoader exists for legacy shared-js-lib showLoader/clearLoader
if (typeof document !== 'undefined' && !document.getElementById('setLoader')) {
  const dummyLoader = document.createElement('div');
  dummyLoader.id = 'setLoader';
  dummyLoader.style.display = 'none';
  dummyLoader.className = 'd-none';
  dummyLoader.setAttribute('aria-hidden', 'true');
  document.body.appendChild(dummyLoader);
}

try {
  sessionStorage.removeItem('fromForgot');
  localStorage.removeItem('redirect');
} catch (e) {}

const redirect = '/login/code';
localStorage.setItem('redirect', redirect);

createAdminLoginHandler({
  formId: 'login',
  route: '/login',
  redirect: redirect,
  recaptchaAction: 'LOGIN',
  optionalFields: ['rememberMe']
});

document.getElementById('button')?.setAttribute('data-ready', 'true');

