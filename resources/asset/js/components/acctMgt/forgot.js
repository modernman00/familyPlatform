import { forgotSubmitHandler } from '@modernman00/shared-js-lib';

sessionStorage.setItem('fromForgot', 'true');


forgotSubmitHandler({
  formId: 'forgot',
  route: '/login/forgot',
  redirect: '/login/code',
  theme: 'bulma'
 
});
