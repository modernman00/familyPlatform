import {  forgotSubmitHandler } from '@modernman00/shared-js-lib';

localStorage.setItem('redirect', '/login/changePW');
sessionStorage.setItem('fromForgot', 'true');

forgotSubmitHandler({
  formId: 'forgot',
  route: '/login/forgot',
  redirect: '/login/code',
  theme: 'bootstrap',
  recaptchaAction: 'FORGOT'
});
