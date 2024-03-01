"use strict";
import FormHelper from '../FormHelper';
import { id, showError, qSel } from '../global'
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
    try {
        e.preventDefault();

        formData.error = [];

        formData.emailVal() // sanitise email

        formData.massValidate();

        if (formData.error.length == 0) {
            const loginNotification = id('loginNow_notification');

            if (loginNotification.classList.contains('is-danger')) {
                loginNotification.classList.remove('is-danger');
            }

            id('setLoader').style.display = "block"

            localStorage.setItem('redirect', '/member/ProfilePage')
                // Get the login URL from sessionStorage
            const loginURL = sessionStorage.getItem('loginURL1');

            postFormData("/lasu", "loginNow", "/admin/reviewApps")

        } else {
            alert('The form cannot be submitted. Please check the errors')

        }
        // } 
        // else {
        // 	alert('To continue, you need to agree to the our privacy policy')
        // }
    } catch (err) {
        showError(err)
    }
}

id('submit').addEventListener('click', LoginSubmission)
id("showPassword_id").addEventListener('click', () => showPassword('password_id'))