"use strict";
import FormHelper from "@modernman00/shared-js-lib/FormHelper";
import { registerHandler } from "@modernman00/shared-js-lib"
import { dataToCheckRegister } from "../dataToCheck"

const formInput = document.querySelectorAll('.register');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);

(() => {

    try {


        formData.clearError();
        // set the maxlength, check the length of the value, raise error
        formData.realTimeCheckLen(
            dataToCheckRegister.maxLength.id,
            dataToCheckRegister.maxLength.max
        );
        // check if password matches real time
        const pwdEl = document.getElementById(dataToCheckRegister.password.pwd);
        if (pwdEl) {
            formData.matchInput(
                dataToCheckRegister.password.pwd,
                dataToCheckRegister.password.pwd2,
            );
        }

        // formData.duplicate('firstName_id', 'alias_id')

    } catch (error) {
        console.log(error)
    }
})();

const formEl = document.getElementById('register');
if (formEl) {
    formEl.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById('btnSubmit') || document.getElementById('button');
        submitBtn?.click();
    });
}

const targetButtonId = document.getElementById('btnSubmit') ? 'btnSubmit' : 'button';

registerHandler({
    formId: 'register',
    buttonId: targetButtonId,
    route: '/register',
    redirect: '/login',
    recaptchaAction: 'SUBMIT',
    optionalFields: ['surname']
});

const submitBtn = document.getElementById('btnSubmit') || document.getElementById('button');
if (submitBtn) {
    submitBtn.setAttribute('data-ready', 'true');
}