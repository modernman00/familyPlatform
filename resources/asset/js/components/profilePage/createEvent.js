"use strict";
import { id, log, showError } from "../global"
import FormHelper from '../FormHelper';
import { addToNotificationTab, increaseNotificationCount } from '../navbar'
import { eventHtml } from './eventHTML'
import axios from "axios";

const formInput = document.querySelectorAll('.eventModalForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const displayNone = () => id('id_event_modal').style.display = 'none'

id('cancelModal').addEventListener('click', displayNone)

/**
 * Filters events by family code (famCode) to ensure only relevant events are shown
 * @param {Object} event - The event data object
 * @returns {boolean} - Returns true if event is linked to the family code
 */


/**
 * Adds an event to the event list if it passes the family code filter.
 * 
 * @param {Object} data - The event data object to be checked and possibly added.
 * @returns {void}
 */
const checkEventAndAdd = (data) => {

    const appendEvent = eventHtml(data);
    return id('eventList').insertAdjacentHTML('afterbegin', appendEvent);
}

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
        id('error').innerHTML = ""
        formData.massValidate();

        if (formData.error.length > 0) {
            alert('The form cannot be submitted. Please check the errors');
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

        // Extract the eventNo and notificationNo from the responses
        const { message: eventNo } = eventResponse.data;
        const { message: notificationNo } = notificationResponse.data;

        // Use Promise.all to fetch event data and notification data in parallel
        const [eventDataResponse, notificationDataResponse] = await Promise.all([
            axios.get(`/member/getEventDataByNo?eventNo=${eventNo}`),
            axios.get(`/member/notification/event?notificationNo=${notificationNo}`)
        ]);

        const eventData = eventDataResponse.data.message;
        const notificationData = notificationDataResponse.data.message;

        // Check if event data exists, then add it to the UI
        if (eventData) checkEventAndAdd(eventData);

        // Add the notification to the notification tab and increase count
        if (notificationData?.[0]) {
            addToNotificationTab(notificationData[0]);
            increaseNotificationCount();
        }

        // close the modal
        displayNone();


    } catch (error) {
        showError(error)
    }

}



id('submitEventModal').addEventListener('click', process)





