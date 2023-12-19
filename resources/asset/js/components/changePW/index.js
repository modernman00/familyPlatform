"use strict";
import { matchInput } from '../helper/general';
import { id, showError } from '../global';
import { postFormData } from '../helper/http';

matchInput('password_id', 'confirm_password_id', 'changePasswordErr')


const loaderElement = id('setLoader');

const submitChangePW = (e) => {

    try {
        e.preventDefault();

        const password = id('password_id').value;

        // Remove any previous error notifications
        const changePasswordNotificationElement = id('changePassword_notification');

        if (changePasswordNotificationElement.classList.contains('is-danger')) {
            changePasswordNotificationElement.classList.remove('is-danger');
        }

        id('error').innerHTML = '';
        if (password) {
            loaderElement.style.display = 'block';
            postFormData('/login/changePW', 'changePassword', '/login');
        }
    } catch (error) {
        showError(error)
    } finally {
        // Hide the loader element regardless of success or error
        loaderElement.style.display = 'none';
    }
}

// Add a click event listener to the element with id 'submit'
id('submit').addEventListener('click', submitChangePW)

const currentPs = id("password_id")
// const emailID = id("email_id")
const confirmPs = id("confirm_password_id")

currentPs.setAttribute('autocomplete', 'new-password')
confirmPs.setAttribute('autocomplete', 'new-password')
// emailID.setAttribute('type', 'hidden')
// emailLabel.style.display = "none"