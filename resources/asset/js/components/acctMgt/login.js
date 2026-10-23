import { createAdminLoginHandler } from "@modernman00/shared-js-lib";

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

