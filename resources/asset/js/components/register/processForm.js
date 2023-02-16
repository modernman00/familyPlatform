"use strict";
import FormHelper from '../FormHelper';
import { id, log, showError, showNotification } from '../global';
import { dataToCheckRegister } from '../dataToCheck';
import axios from "axios";

const formInput = document.querySelectorAll('.register');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


(() => {

    formData.clearError();

    // set the maxlength, check the length of the value, raise error

    formData.realTimeCheckLen(
        dataToCheckRegister.maxLength.id,
        dataToCheckRegister.maxLength.max
    );

    // check if password matches real time
    formData.matchInput(dataToCheckRegister.password.pwd,
        dataToCheckRegister.password.pwd2,
    );

    formData.duplicate('firstName_id', 'alias_id')

})();

const processFormDataAction = (addClass, serverResponse) => {
    // display the success information for 10sec
    id('register_notify_div').style.display = "block" // unblock the notification
    id('register_notify_div').classList.add(addClass) // add the success class
    id('register_notify_div_msg').innerHTML = serverResponse.message // error element
    id('loader').classList.remove('loader') // remove loader
}


const processFormData = async(url, formElement) => {
    const form = id(formElement)
    let formEntries = new FormData(form)
    formEntries.delete('submit')
    formEntries.delete('checkbox_id')

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }

    await axios.post(url, formEntries, options).then(response => {

        // get the api message and output it to the form
        processFormDataAction('is-success', response.data)

        setTimeout(() => {
            //window.location.replace(redirect)
            window.location.replace('register/nextStep')
        }, 5000)

        // it clears all the contents
        //  formData.clearHtml();
    }).catch(error => {
        processFormDataAction('is-danger', error.response.data)

    })

}

const processForm = (e) => {
    try {
        e.preventDefault();

        id('register_notify_div').classList.remove('is-danger') // remove the danger class from the notification
        id('register_notify_div_msg').innerHTML = "" // empty the error element

        if (id('checkbox').checked) {
            // window.location.hash = '#setLoader';
            id("setLoader").focus(); // focus on the loader element

            formData.clearError()
            formData.emailVal()
            formData.massValidate();

            if (formData.error.length <= 0) {

                id('loader').classList.add('loader')
                return processFormData("/register", 'register')
            } else {

                alert('The form cannot be submitted. Please check the errors')
                    // console.log(formData.error)

            }

        } else {
            alert('To continue, you need to agree to the our privacy policy')

            const errorData = "To continue, you need to agree to the our privacy policy"

            showNotification('checkbox_error', 'is-danger', errorData)

        }

    } catch (event) {
        showError(event)
    }
}

id('submit').addEventListener('click', processForm)