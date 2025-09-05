'use strict';
import { createAdminLoginHandler } from '@modernman00/shared-js-lib';

// Set the redirect URL in localStorage
localStorage.setItem('redirect', '/member/ProfilePage');

alert('login')

// Determine the redirect URL based on loginURL
const redirect = '/login/code';

createAdminLoginHandler({
  formId: 'login',
  route: 'login',
  redirect: redirect,
  theme: 'bulma'
});
