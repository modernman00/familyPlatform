"use strict";
import { createAdminLoginHandler } from '@modernman00/shared-js-lib';

id("setLoader").style.display = "none";

createAdminLoginHandler({
    formId: 'lasu',
    route: 'lasu',
    redirect: '/admin/reviewApps',
    theme: 'bulma'
});