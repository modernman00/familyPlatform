"use strict";
import { id, log, showError } from '../global'
import { postFormData } from "../helper/http"


// block the setLoader div

id("setLoader").style.display = "none";

const LoginCode = (e) => {
    try {
        e.preventDefault();
        // the notification div and the content
        id('codeForm_notification').classList.remove('is-danger')
        id('error').innerHTML = ""
            // show notification
        id('setLoader').style.display = "block"
            // add the notification div
        id('loader').classList.add('loader')

        // get the direct from the login script (getstorage)
        const location = localStorage.getItem('redirect')

        log(location)

        postFormData("/login/code", "codeForm", location)

      

    } catch (err) {
        showError(err)
    }
}

id('submit').addEventListener('click', LoginCode)