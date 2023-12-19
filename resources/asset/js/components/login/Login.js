"use strict";
import FormHelper from '../FormHelper';
import { id, showError } from '../global'
import { Login } from "../dataToCheck";
import { postFormData } from "../helper/http"
import { showPassword } from "../helper/security"

// block the setLoader div

id("setLoader").style.display = "none";

const formInput = document.querySelectorAll('.loginNow');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);

(() => {
    //clear error from the form
    formData.clearError()

    // set the maxlength, check the length of the value, raise error
    formData.realTimeCheckLen(
        Login.maxLength.id,
        Login.maxLength.max
    );
})();



const LoginSubmission = (e) => {
    e.preventDefault();

    // Remove the danger class from the notification
    const loginNotification = id('loginNow_notification');
    // if (loginNotification.classList.contains('is-danger')) {
    //     loginNotification.classList.remove('is-danger');
    // }

    // Clear any previous error messages
    formData.clearError();

    try {
        // Sanitize email
        formData.emailVal();

        // Validate and sanitize data
        formData.massValidate();

        if (formData.error.length === 0) {
            // the notification div and the content

            id('error').innerHTML = ""

            const loginNotification = id('loginNow_notification');

            if (loginNotification.classList.contains('is-danger')) {
                loginNotification.classList.remove('is-danger');
            }
            // Display the success information for 10 seconds
            id('setLoader').style.display = "block";

            // Set the redirect URL in localStorage
            localStorage.setItem('redirect', '/member/ProfilePage');

            // Get the login URL from sessionStorage
            const loginURL = sessionStorage.getItem('loginURL1');

            // Determine the redirect URL based on loginURL
            const redirect = (loginURL === "/lasu") ? null : "/login/code";

            // Submit the form data
            postFormData(loginURL, "loginNow", redirect);

        } else {
            // Display an alert for form errors
            alert('The form cannot be submitted. Please check the errors');
        }
    } catch (err) {
        // Handle any unexpected errors
        showError(err);
    }
}


id('submit').addEventListener('click', LoginSubmission)

id("showPassword_id").addEventListener('click', () => showPassword('password_id'))