import { setupPasswordChange } from '@modernman00/shared-js-lib';

setupPasswordChange({
  formId: 'changePW',
  route: '/login/changePW',
  redirect: '/login'
});
