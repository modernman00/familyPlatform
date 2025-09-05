import {  forgotSubmitHandler } from '@modernman00/shared-js-lib';

sessionStorage.setItem('fromForgot', 'true');

forgotSubmitHandler({
  formId: 'forgotPassword',
  route: '/login/forgot',
  redirect: '/login/code',
  theme: 'bulma'
 
});
