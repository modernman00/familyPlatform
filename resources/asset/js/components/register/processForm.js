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
        formData.matchInput(
            dataToCheckRegister.password.pwd,
            dataToCheckRegister.password.pwd2,
        );

        // formData.duplicate('firstName_id', 'alias_id')

    } catch (error) {
        console.log(error)

    }
})();



registerHandler({
    formId:'register',
    route:'/register',
    buttonId: 'button',
    redirect: 'register/nextStep',
    recaptchaAction: 'SUBMIT',
    optionalFields: []
})