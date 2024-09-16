"use strict";
import { id, showError } from "../global"
import FormHelper from '../FormHelper';
import { addToNotificationTab, increaseNotificationCount } from '../navbar'
import { eventHtml } from './eventHTML'
import axios from "axios";

const formInput = document.querySelectorAll('.eventModalForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const displayNone = () => id('id_event_modal').style.display = 'none'
id('cancelModal').addEventListener('click', displayNone)


const checkEventAndAdd = (data) => {

    const appendEvent = eventHtml(data);
    return id('eventList').insertAdjacentHTML('afterbegin', appendEvent);

}

const options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
}

const process = (e) => {
    try {
        e.preventDefault();
        // id('eventModalForm_notification').classList.remove('w3-red') // remove the danger class from the notification - may not be needed
        id('error').innerHTML = "" // may not be needed
        formData.massValidate();
        // log(formData.error)
        if (formData.error.length <= 0) {
            // get the form data
            const eventForm = id('eventModalForm');
            let eventFormEntries = new FormData(eventForm);

            // post the form data to the database and get the last posted event no

            axios.post("/member/profilePage/event", eventFormEntries, options).then(response => {

                // use the event no to get the last event from the database

                axios.get(`/member/getEventDataByNo?eventNo=${response.data.message}`)

                    .then(res => {

                        if (res.data.message) {

                            // add new event real time
                            checkEventAndAdd(res.data.message[0])
                        }
                    })

                // POST THE EVENT TO NOTIFICATION

                axios.post('/member/notification/event', eventFormEntries, options).then(result => {

                    // GET THE POST EVENTS AND ADD THEM TO THE NOTIFICATION

                    axios.get(`/member/notification/event?notificationNo=${result.data.message}`).then(result2 => {

                        addToNotificationTab(result2.data.message[0])

                        increaseNotificationCount()

                    })

                })

                // now trigger push notifications subscription
                const registration = navigator.serviceWorker.ready;
                const subscription = registration.pushManager.getSubscription();

                if (!subscription) {
                    const newSubscription =registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: process.env.MIX_VAPID_PUBLIC_KEY
                    });
                     console.log('New Push Subscription:', newSubscription);
                } else {
                    console.log('Already subscribed for push notifications:', subscription);
                }


            })

            displayNone();

            // window.location.replace("/member/profilePage")
        } else {
            alert('The form cannot be submitted. Please check the errors')
            formData.clearError()
        }

    } catch (error) {
        showError(error)
    }

}

id('submitEventModal').addEventListener('click', process)





