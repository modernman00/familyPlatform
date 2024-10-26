import { format, render } from "timeago.js"
import { id, showError, qSel, log } from './global'
import { toSentenceCase } from "./helper/general"

// const timeAgo = (x) => format(x)
import axios from "axios"


const postAgoNotification = (date) => {
        return `
  <div class="notification_timeago w3-left w3-opacity" datetime='${date}' title='${format(date)}'> ${format(date)}
  </div>`
    }
    // this is the notification htnl 
const notificationHTML = (data) => {


    return `<a data-id="${data.sender_id}" class="w3-bar-item w3-button notification_real_time linkRequestCard">
  

  ${postAgoNotification(data.created_at)}  - 
  <b> ${data.notification_type}</b> -
  ${data.notification_name} -
  ${data.notification_content} -
  ${toSentenceCase(data.sender_name)}

  
  </a>`
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


const reqId = localStorage.getItem('requesterId');
const famCode = localStorage.getItem('requesterFamCode');
const notificationURL = `/member/notifications/id?notificationId=${reqId}&famCode=${famCode}`;



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


// ONCE THE NOTIFICATION BAR IS CLICKED, IT SHOULD TAKE YOU TO BE FRIEND REQUEST CARD

// Add a click event listener to elements with the "linkRequestCard" class
document.addEventListener('click', (e) => {

    if (e.target.classList.contains('linkRequestCard')) {
        const friendRequestSection = id(`${e.target.getAttribute('data-id')}_linkRequestCard`);
        if (friendRequestSection) {
            friendRequestSection.scrollIntoView({ behavior: "smooth" });
        }
    }



});








///member/notifications