"use strict";
import { createAdminLoginHandler, id } from '@modernman00/shared-js-lib';


const redirect = '/admin/dashboard';
const currentPath = (typeof window !== 'undefined' && window.location.pathname) 
    ? window.location.pathname.replace(/^\//, '') 
    : 'lasu';

createAdminLoginHandler({
    formId: 'lasu',
    buttonId: 'button',
    emailId: 'email',
    passwordId: 'password',
    showToggleId: 'showPassword',
    route: currentPath,
    redirect: redirect,
    theme: 'bulma',
    recaptchaAction: 'ADMIN_LOGIN'
});