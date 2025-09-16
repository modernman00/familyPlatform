import {  forgotSubmitHandler } from '@modernman00/shared-js-lib';

sessionStorage.setItem('fromForgot', 'true');


forgotSubmitHandler({
  formId: 'forgot',
  route: '/forgot',
  redirect: '/code',
  theme: 'bulma'
 
});
