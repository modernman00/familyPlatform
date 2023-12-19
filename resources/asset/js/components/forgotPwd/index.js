"use strict";
import { id, showError } from '../global'
import { postFormData } from "../helper/http"
import { emailVal } from "../helper/security"

// block the setLoader div

id("setLoader").style.display = "none";

const forgotPasswordSubmission = (e) => {
    try {
        e.preventDefault();

        const email = id('email_id').value

        // just in case there was an earlier error notification - remove it
        const forgotPasswordNotification = id('forgotPassword_notification');

        if (forgotPasswordNotification.classList.contains('is-danger')) {
            forgotPasswordNotification.classList.remove('is-danger');
        }

        id('error').innerHTML = ""

        if (!emailVal(email)) {
            id("setLoader").style.display = "block";

            localStorage.setItem('redirect', '/login/changePW')

            postFormData("/login/forgot", "forgotPassword", "/login/code")

        }
    } catch (error) {
        showError(error)
    }
}

id('submit').addEventListener('click', forgotPasswordSubmission)