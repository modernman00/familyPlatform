import { createAdminLoginHandler} from '@modernman00/shared-js-lib';

// Get the login URL from sessionStorage
const loginURL = sessionStorage.getItem('loginURL1');

// Determine the redirect URL based on loginURL
const redirect = loginURL === '/lasu' ? '/admin/reviewApps' : '/login/code';
    localStorage.setItem('redirect', '/login/code');

createAdminLoginHandler({
  formId: 'login',
  route: 'login',
  redirect: redirect,
  recaptchaAction: 'LOGIN',
  optionalFields: ['rememberMe']
});

