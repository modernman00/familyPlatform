"use strict";
import {  setupPasswordChange} from '@modernman00/shared-js-lib';

matchInput('password', 'confirm_password', 'changePasswordErr')

setupPasswordChange({
    formId: 'changePW',
  route: '/login/changePW',
  redirect: '/login'
});
