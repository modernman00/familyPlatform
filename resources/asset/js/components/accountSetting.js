"use strict";
import "./register/onChangeKidSibling"
import { id, showError } from "./global"
import FormHelper from './FormHelper';

import axios from "axios";

const formInput = document.querySelectorAll('.accountSettingForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);

const options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
}
const process = (e) => {
    try {
        e.preventDefault();
        const notificationDiv = id('accountSettingForm_notification')
        const notificationMsg = id('accountSettingForm_notification_error')
        notificationMsg.innerHTML = "" // may not be needed
        formData.massValidate();
        // log(formData.error)
        if (formData.error.length <= 0) {
            // get the form data
            const eventForm = id('accountSettingForm');
            let eventFormEntries = new FormData(eventForm);
            // post the form data to the database and get the last posted event no
            axios.post("/accountSetting", eventFormEntries, options).then(response => {
 
               
                notificationDiv.style.display = "block" // unblock the notification
                notificationDiv.classList.add('is-success') // add the success class
                notificationMsg.innerHTML = response.data.message

            })



            // window.location.replace("/member/profilePage")
        } else {
            alert('The form cannot be submitted. Please check the errors')
            formData.clearError()
        }

    } catch (error) {
        showError(error)
    }

}

id('submit').addEventListener('click', process)