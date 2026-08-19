import { setupPasswordChange } from '@modernman00/shared-js-lib';

setupPasswordChange({
  formId: 'changePW',
  buttonId: 'button',
  passwordId: 'password',
  confirmId: 'confirm_password',
  showToggleId: 'showPassword',
  helpId: 'non_existent_help',
  route: '/changePW',
  redirect: 'login',
  optionalFields: ['token', 'showPassword', 'confirm_password']
});

