'use strict';
import {
  id,
  showError,
  postFormData,
  showPassword,
  convertFormData,
} from '@modernman00/shared-js-lib';
import { Login } from '../dataToCheck';

// block the setLoader div

id('setLoader').style.display = 'none';

const formData = convertFormData('.loginNow');

(() => {
  //clear error from the form
  formData.clearError();

  // set the maxlength, check the length of the value, raise error
  formData.realTimeCheckLen(Login.maxLength.id, Login.maxLength.max);
})();

window.LoginSubmission = (token) => {
  // Clear any previous error messages
  formData.clearError();

  try {
    // Sanitize email
    formData.emailVal();

    // Validate and sanitize data
    formData.massValidate();

    if (formData.error.length === 0) {
      // the notification div and the content

      id('error').innerHTML = '';

      const loginNotification = id('loginNow_notification');

      if (loginNotification.classList.contains('is-danger')) {
        loginNotification.classList.remove('is-danger');
      }
      // Display the success information for 10 seconds
      id('setLoader').style.display = 'block';

      // Set the redirect URL in localStorage
      localStorage.setItem('redirect', '/member/ProfilePage');

      // Get the login URL from sessionStorage
      const loginURL = sessionStorage.getItem('loginURL1');

      // Determine the redirect URL based on loginURL
      const redirect = loginURL === '/lasu' ? '/admin/reviewApps' : '/login/code';

      // Submit the form data
      postFormData(loginURL, 'loginNow', redirect, 'bulma');
    } else {
      // Display an alert for form errors
      alert('The form cannot be submitted. Please check the errors');
    }
  } catch (err) {
    // Handle any unexpected errors
    showError(err);
  }
  // });
  // });
};

// id('button').addEventListener('click', LoginSubmission)

id('showPassword_id').addEventListener('click', () =>
  showPassword('password_id'),
);
