import { createAdminLoginHandler} from '@modernman00/shared-js-lib';


      // Get the login URL from sessionStorage
      const loginURL = sessionStorage.getItem('loginURL1');

      // Determine the redirect URL based on loginURL
      const redirect = loginURL === '/lasu' ? '/admin/reviewApps' : '/login/code';

      // Critically ensure the redirect storage is reset to ProfilePage for normal logins,
      // otherwise an aborted Forgot Password flow will falsely hijack the redirect.
      if (loginURL !== '/lasu') {
          localStorage.setItem('redirect', '/member/ProfilePage');
      }
createAdminLoginHandler({
  formId: 'login',
  buttonId: 'button', // Changed from 'none' to allow manual click handling/validation
  emailId: 'email',
  passwordId: 'password',
  showToggleId: 'showPassword',
  route: 'login',
  redirect: redirect,
  theme: 'bulma',
  recaptchaAction: 'LOGIN'
});
