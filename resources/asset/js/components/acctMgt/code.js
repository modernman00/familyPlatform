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

console.log(redirectTo);

createCodeSubmitHandler({
  formId: 'code', 
  route: '/code', 
  redirect: redirectTo,
  theme: 'bulma'
});