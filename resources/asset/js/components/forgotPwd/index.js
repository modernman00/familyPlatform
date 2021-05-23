"use strict";
import { id, showError} from '../global'
import { postFormData } from "../helper/http"
import { emailVal } from "../helper/security"


// block the setLoader div

id("setLoader").style.display = "none";

const forgotPasswordSubmission = (e) => {
    try {
        e.preventDefault();
        const email = id('email_id').value

        // just in case there was an earlier error notification - remove it
        id('forgotPassword_notification').classList.remove('is-danger')

        id('error').innerHTML = ""

        if (!emailVal(email)) {
            id("setLoader").style.display = "block";

            id('loader').classList.add('loader')
            
            postFormData("/login/forgot", "forgotPassword")

            window.location.replace("/login/code")
        }
    } catch (error) {
        showError(err)
    }
}

    id('submit').addEventListener('click', forgotPasswordSubmission)