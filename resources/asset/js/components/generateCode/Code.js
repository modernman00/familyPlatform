"use strict";
import { createCodeSubmitHandler, qSelAll, id } from '@modernman00/shared-js-lib';

// get the redirect from the login script (getstorage) or default to profile page
const location = localStorage.getItem('redirect') || '/member/ProfilePage';

// Initialize the primary handler
createCodeSubmitHandler({
  formId: 'code', 
  route: '/login/code', 
  redirect: location,
  theme: 'bootstrap',
  recaptchaAction: 'CODE'
});
