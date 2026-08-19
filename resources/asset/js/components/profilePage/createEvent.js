"use strict";
import { id, log, showError } from "../global"
import FormHelper from '../FormHelper';
import { addToNotificationTab, increaseNotificationCount } from '../navbar'
import { eventHtml } from './eventHTML'
import axios from "axios";
import Pusher from 'pusher-js';
import Swal from 'sweetalert2';


const formInput = document.querySelectorAll('.eventModalForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const displayNone = () => {
    const modal = id('id_event_modal') || id('createEventModal');
    if (modal) {
        modal.style.display = 'none';
        if (window.jQuery && window.jQuery(modal).modal) {
            window.jQuery(modal).modal('hide');
        }
    }
};

const cancelBtn = id('cancelModal');
if (cancelBtn) {
    cancelBtn.addEventListener('click', displayNone);
}

/**
 * Filters events by family code (famCode) to ensure only relevant events are shown
 * @param {Object} event - The event data object
 * @returns {boolean} - Returns true if event is linked to the family code
 */



const options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
}

/**
 * @function process
 * @description Processes the event modal form data to create a new event
 * @param {Event} e - The event object
 * @example
 * const eventForm = id('eventModalForm');
 * eventForm.addEventListener('submit', process);
 */
const process = async (e) => {
    try {
        e.preventDefault();
        const errEl = id('error');
        if (errEl) errEl.innerHTML = "";

        const formInput = document.querySelectorAll('.eventModalForm');
        const formInputArr = Array.from(formInput);
        const formData = new FormHelper(formInputArr);
        formData.massValidate();

        if (formData.error && formData.error.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'The form cannot be submitted. Please check the errors',
                confirmButtonColor: '#3085d6'
            });
            formData.clearError();
            return;
        }

        // get the form data
        const eventForm = id('eventModalForm');
        let eventFormEntries = new FormData(eventForm);

        // POST data to create the event and notification in parallel
        const [eventResponse, notificationResponse] = await Promise.all([
            axios.post("/member/profilePage/event", eventFormEntries, options),
            axios.post('/member/notification/event', eventFormEntries, options)
        ]);

        // Extract and get notificationNo from the responses
        const { message: notificationNo } = notificationResponse.data || {};

        // update all members of similar famcode on their UIs using Pusher
        if (notificationNo) {
            axios.get(`/member/notification/event?notificationNo=${notificationNo}`);
        }

        // close the modal
        displayNone();

    } catch (error) {
        showError(error);
    }
};

const submitBtn = id('submitEventModal');
if (submitBtn) {
    submitBtn.addEventListener('click', process);
}





