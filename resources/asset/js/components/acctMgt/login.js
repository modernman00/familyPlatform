'use strict';
import { createAdminLoginHandler, log} from '@shared';


// Set the redirect URL in localStorage
localStorage.setItem('redirect', '/profilePage');

// Determine the redirect URL based on loginURL
const redirect = '/code';

// alert('redirect ' + redirect)

createAdminLoginHandler({
  formId: 'login',
  route: 'login',
  redirect: redirect,
  theme: 'bulma'
});
