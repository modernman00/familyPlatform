"use strict";
import { id, log } from "../global"
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
    if (!modal) return;

    // This page loads Bootstrap 5, which has no jQuery .modal() plugin — setting
    // style.display='none' directly skips Bootstrap's own hide lifecycle and leaves
    // the .modal-backdrop overlay + body's modal-open/overflow:hidden stuck behind,
    // which is what made the page look frozen after submitting.
    const instance = window.bootstrap?.Modal?.getInstance(modal);
    if (instance) {
        instance.hide();
    } else {
        const closeBtn = modal.querySelector('.btn-close, [data-bs-dismiss="modal"]');
        if (closeBtn) closeBtn.click();
    }
};

const cancelBtn = id('cancelModal');
if (cancelBtn) {
    cancelBtn.addEventListener('click', displayNone);
}

// Reset the modal out of "edit" mode however it closes (submit, cancel,
// backdrop click, ESC) — editEvent() in sidebarComponents.js is what puts it
// into edit mode by setting these same fields.
const createEventModalEl = id('createEventModal');
if (createEventModalEl) {
    createEventModalEl.addEventListener('hidden.bs.modal', () => {
        const editEventNo = id('editEventNo');
        const notice = id('editEventNotice');
        const title = id('createEventModalLabel');
        const submitBtnEl = id('submitEventModal');
        if (editEventNo) editEventNo.value = '';
        if (notice) notice.classList.add('d-none');
        if (title) title.textContent = 'Create Event';
        if (submitBtnEl) submitBtnEl.textContent = 'Create Event';
        const eventForm = id('eventModalForm');
        if (eventForm) eventForm.reset();
    });
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

// Mirrors rightColumn.blade.php's server-side "l jS \of F Y" format (e.g. "Wednesday 25th of August 2026")
const formatEventDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(`${dateStr}T00:00:00`);
    if (isNaN(date)) return dateStr;
    const day = date.getDate();
    const suffix = (day % 10 === 1 && day !== 11) ? 'st'
        : (day % 10 === 2 && day !== 12) ? 'nd'
        : (day % 10 === 3 && day !== 13) ? 'rd' : 'th';
    const weekday = date.toLocaleDateString('en-GB', { weekday: 'long' });
    const month = date.toLocaleDateString('en-GB', { month: 'long' });
    return `${weekday} ${day}${suffix} of ${month} ${date.getFullYear()}`;
};

// Mirrors rightColumn.blade.php's dateDifferenceInt()/number2word() -> "Today"/"Tomorrow"/"in N Days"
const dateDifferenceLabel = (dateStr) => {
    if (!dateStr) return '';
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const target = new Date(`${dateStr}T00:00:00`);
    if (isNaN(target)) return '';
    const diffDays = Math.round((target - today) / 86400000);
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    return `in ${diffDays} Days`;
};

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

        // editEvent() in sidebarComponents.js stamps this when reopening the modal
        // to edit an existing event instead of creating a new one.
        const editEventNo = id('editEventNo')?.value;
        if (editEventNo) {
            const payload = {
                eventName: eventFormEntries.get('eventName') || '',
                eventDate: eventFormEntries.get('eventDate') || '',
                eventType: eventFormEntries.get('eventType') || '',
                eventDescription: eventFormEntries.get('eventDescription') || '',
                eventFrequency: eventFormEntries.get('eventFrequency') || '',
            };

            await axios.put(`/member/profilePage/event/${editEventNo}`, payload, options);

            window.dispatchEvent(new CustomEvent('event-updated', {
                detail: {
                    no: editEventNo,
                    eventName: payload.eventName,
                    eventDate: formatEventDate(payload.eventDate),
                    eventType: payload.eventType,
                    dateDifference: dateDifferenceLabel(payload.eventDate),
                }
            }));

            displayNone();

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Event updated successfully',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }

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

        // The sidebar's upcomingEvents component only ever gets seeded once, from the
        // page's initial server render (see sidebarComponents.js) — nothing was telling
        // it about events created afterward, so they only showed up on a full reload.
        // Dispatch the new event so it can prepend it immediately, mirroring the shape
        // rightColumn.blade.php builds server-side ({no, eventName, eventDate, eventType, dateDifference}).
        const newEventNo = eventResponse?.data?.token;
        if (newEventNo) {
            window.dispatchEvent(new CustomEvent('event-created', {
                detail: {
                    no: newEventNo,
                    eventName: eventFormEntries.get('eventName') || '',
                    eventDate: formatEventDate(eventFormEntries.get('eventDate')),
                    eventType: eventFormEntries.get('eventType') || '',
                    dateDifference: dateDifferenceLabel(eventFormEntries.get('eventDate')),
                }
            }));
        }

        // close the modal
        displayNone();

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Event created successfully',
            showConfirmButton: false,
            timer: 3000
        });

    } catch (error) {
        // showError() writes into the modal's own #error <p> instead of firing a Swal
        // when that element exists (it does, in this modal), so real submission
        // failures were going unnoticed. Show a Swal directly instead, same as the
        // validation-error path above.
        const userMessage = error?.response?.data?.message || error?.message || 'There was an error creating your event. Please try again.';
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: userMessage,
            confirmButtonColor: '#3085d6'
        });
        log(error);
    }
};

const submitBtn = id('submitEventModal');
if (submitBtn) {
    submitBtn.addEventListener('click', process);
}





