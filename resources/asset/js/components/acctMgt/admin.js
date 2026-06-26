"use strict";
import { createAdminLoginHandler, id } from '@modernman00/shared-js-lib';

id("setLoader").style.display = "none";


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