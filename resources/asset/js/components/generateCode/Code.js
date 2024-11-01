"use strict";
import { id, showError } from '../global'
import { postFormData } from "../helper/http"

// block the setLoader div

id("setLoader").style.display = "none";



const LoginCode = (e) => {
    try {
       
        e.preventDefault();
        // just in case there was an earlier error notification - remove it
        const codeNotification = id('codeForm_notification');

        if (codeNotification.classList.contains('is-danger')) {
            codeNotification.classList.remove('is-danger');
        }

        id('setLoader').style.display = "block"

        // get the direct from the login script (getstorage)
        const location = localStorage.getItem('redirect')

        postFormData("/login/code", "codeForm", location)

    } catch (err) {
        showError(err)
    }
}

id('submit').addEventListener('click', LoginCode)