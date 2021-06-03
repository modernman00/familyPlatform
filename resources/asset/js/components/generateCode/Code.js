"use strict";
import { id, log, showError } from '../global'
import { postFormData } from "../helper/http"


// block the setLoader div

id("setLoader").style.display = "none";


const LoginCode = (e) => {
    try {
        e.preventDefault();
        id('codeForm_notification').classList.remove('is-danger')
        id('error').innerHTML = ""
        id('setLoader').style.display = "block"
        id('loader').classList.add('loader')

        // get the direct from the login script (getstorage)
        const location = localStorage.getItem('redirect')

        postFormData("/login/code", "codeForm", location)

        localStorage.removeItem('redirect')

    } catch (err) {
        showError(err)
    }
}

id('submit').addEventListener('click', LoginCode)
