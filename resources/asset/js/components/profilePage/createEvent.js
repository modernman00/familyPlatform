"use strict";
import { id, showError , log, date2String} from "../global"
import FormHelper from '../FormHelper';

import axios from "axios";

const formInput = document.querySelectorAll('.eventModalForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const displayNone = () => id('id_event_modal').style.display = 'none'
id('cancelModal').addEventListener('click', displayNone)


const eventHtml = (data) => {

    return `<p class='eventInfo'>
                    <strong>RSVP: </strong> ${data.firstName} ${data.lastName}</p>
            <p class='eventInfo'><strong>Event: </strong>${data.eventName}</p>
            <p class='eventInfo'><strong>Date: </strong>${date2String(data.eventDate)}</p>
            <p class='eventInfo'><strong>Type: </strong>${data.eventType}</p>
            <p class='eventInfo'><strong>Description: </strong> ${data.eventDescription}</p>
            <input type='hidden' name='event_no' id='event${data.no}' value='${data.no}'>
            
           <hr>`;
}

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
                
                     if(res.data.message) {

                          // add new event real time
                    checkEventAndAdd(res.data.message)
                     }        
                })
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





