"use strict";
import {  createCodeSubmitHandler } from '@modernman00/shared-js-lib';

const fromForgot = sessionStorage.getItem('fromForgot');
let redirectTo;
// Determine redirect target based on session flag


if(fromForgot){
  redirectTo = '/login/changePW';
}else {
  redirectTo = '/member/ProfilePage';
}

if (fromForgot) sessionStorage.removeItem('fromForgot');

createCodeSubmitHandler({
  formId: 'codeForm', 
  route: '/login/code', 
  redirect: redirectTo,
  theme: 'bulma'
});