"use strict";
import { createAdminLoginHandler, id } from '@modernman00/shared-js-lib';


const redirect = '/admin/reviewApps';



createAdminLoginHandler({
    formId: 'lasu',
    buttonId: 'button',
    emailId: 'email',
    passwordId: 'password',
    showToggleId: 'showPassword',
    route: 'lasu',
    redirect: redirect,
    theme: 'bulma',
    recaptchaAction: 'ADMIN_LOGIN'
});