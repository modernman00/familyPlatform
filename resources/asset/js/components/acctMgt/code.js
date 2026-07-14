"use strict";
import {  createCodeSubmitHandler } from '@modernman00/shared-js-lib';

const fromForgot = sessionStorage.getItem('fromForgot');
let redirectTo;
// Determine redirect target based on session flag


if(fromForgot){
  redirectTo = '/changePW';
}else {
  redirectTo = '/profilePage';
}

if (fromForgot) sessionStorage.removeItem('fromForgot');

createCodeSubmitHandler({
  formId: 'code', 
  route: '/login/code', 
  buttonId: 'button',
  redirect: redirectTo,
  theme: 'bulma',
  lengthLimitArray: {
    id: ['code'], 
    max: [6]
  },
  recaptchaAction: 'LOGIN_CODE'

});