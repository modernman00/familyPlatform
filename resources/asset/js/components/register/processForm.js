"use strict";
import FormHelper from '../FormHelper';
import { id, showError, showNotification, warningSign } from '../global';
import { dataToCheckRegister } from '../dataToCheck';
import { processFormDataAction } from './helper/notification';
import axios from "axios";

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
        showError(error)

    }
})();

const notificationDiv = id('register_notify_div')
const notificationMsg = id('register_notify_div_msg')

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

const processForm = async(e) => {
    try {
        e.preventDefault();

        notificationDiv.classList.remove('is-danger') // remove the danger class from the notification
        notificationMsg.innerHTML = "" // empty the error element

        if (id('checkbox').checked) {
            // window.location.hash = '#setLoader';
            id("setLoader").focus(); // focus on the loader element

            formData.clearError()
            formData.emailVal()
            formData.massValidate();

            // CHECK IF EMAIL HAS NOT BEEN REGISTERED ERROR IS NULL

            const emailError = id("email_error").innerHTML;

            if (formData.error.length <= 0 && emailError == "") {

                id("setLoader").classList.add('loader');

                await processFormData("/register", 'register');
                id("setLoader").classList.remove('loader');

                return;
            } else {

                alert(`The form cannot be submitted. Please check the errors`)
                notificationMsg.innerHTML = `${warningSign} Check the errors`
                notificationDiv.style.display = "block"
                notificationDiv.style.backgroundColor = "Red"
                notificationDiv.style.color = "White"
                //notificationMsg.scrollIntoView({ behavior: 'smooth', block: 'start' });
                notificationDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                //By using the js scrollIntoView method on the error element, the browser will automatically scroll to make the error message the focus point after the form is submitted.
            }

        } else {
            alert('To continue, you need to agree to the our privacy policy')

            const errorData = "To continue, you need to agree to the our privacy policy"

            showNotification('checkbox_error', 'is-danger', errorData)

        }

    } catch (error) {
        showError(error)
    }
}

id('submit').addEventListener('click', processForm)
// id('submit').addEventListener('click', processForm)