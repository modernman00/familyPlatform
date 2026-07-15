import {  forgotSubmitHandler } from '@modernman00/shared-js-lib';

     localStorage.setItem('redirect', '/login/changePW')


forgotSubmitHandler({
  formId: 'forgot',
  route: '/login/forgot',
  redirect: '/login/code',
  recaptchaAction: 'FORGOT'
 
});


