import { format, render } from "timeago.js"
import { id, showError, qSel, log, msgException } from './global'
import { toSentenceCase } from "./helper/general"

// const timeAgo = (x) => format(x)
import axios from "axios"
// import { html } from './profilePage/html';


const postAgoNotification = (date) => {
    return `
  <div class="notification_timeago w3-left w3-opacity" datetime='${date}' title='${format(date)}'> ${format(date)}
  </div>`
}
// this is the notification htnl 
const notificationHTML = (data) => {

    // generate random numbers to make the notification unique

    let randomNumber = Math.floor(100 + Math.random() * 900);

    return `<a id = "notificationBar${data.sender_id}${randomNumber}" data-id="${data.sender_id}" class="w3-bar-item w3-button notification_real_time linkRequestCard w3-padding-16">

        ${postAgoNotification(data.created_at)}  - 
        <b> ${data.notification_type}</b> -
        ${data.notification_name} -
        ${data.notification_content} -
        ${toSentenceCase(data.sender_name)}
        <button type = "submit" class='w3-button-small w3-round w3-hover-grey w3-border-blue' data-id="${data.sender_id}" id="deleteNotification${data.sender_id}${randomNumber}"> delete</button>
  </a>

  `
}

// CLICK FUNCTION ON THE NOTIFICATION BAR THAT TAKES ONE TO THE FRIEND REQUEST CARD

export const increaseNotificationCount = () => {
    const currentNotificationCount = parseInt(
        sessionStorage.getItem('notificationCount')) + 1

    id('notification_count').innerHTML = currentNotificationCount
}

export const addToNotificationTab = (data) => {

    return qSel('.notification_tab').insertAdjacentHTML('afterbegin', notificationHTML(data));

}


const yourId = localStorage.getItem('requesterId');
const famCode = localStorage.getItem('requesterFamCode');
const notificationURL = `/member/notifications/id/${yourId}/${famCode}`;


// get all the notification and display them 
// they are already filtered by famCode and id 
// for the family request, connection is done by id
// for events -birthday etc, the connection is the famCode 
// so linked notification will be either where id matches or famcode matches

axios.get(notificationURL)
    .then(res => {

        // Extract the notifications from the response
        const data = res.data.message;

        if (data) {

            if (data.length > 0) {

                // Display the count of notifications
                id('notification_count').innerHTML = data.length;

                // Store the notification count in session storage
                sessionStorage.setItem('notificationCount', data.length);

                // Display each notification
                data.forEach(element => {
                    addToNotificationTab(element);
                });

                // Update the timing of notifications
                const updateNotificationTiming = document.querySelectorAll(".notification_timeago");
                render(updateNotificationTiming);
            } else {
                id('notification_count').innerHTML = 0;
            }

        }


    })
    .catch(error => {
        // Handle any errors that occur during the process
        showError(error);
    });















///member/notifications