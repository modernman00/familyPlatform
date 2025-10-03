import { format, render } from "timeago.js"
import { id, showError, qSel, msgException, log } from '@shared'
import { toSentenceCase } from "./helper/general"

// const timeAgo = (x) => format(x)
import axios from "axios"
import { qSelAll } from "@modernman00/shared-js-lib";
// import { html } from './profilePage/html';


// Update notification badge
function updateNotificationBadge(change) {
    const badge = document.querySelector('.notification-badge');
    let count = parseInt(badge.textContent);
    count += change;
    if (count <= 0) {
        badge.style.display = 'none';
    } else {
        badge.textContent = count;
        badge.style.display = 'flex';
    }
}


const postAgoNotification = (date) => {
    return `
  <div class="notification_timeago w3-left w3-opacity" datetime='${date}' title='${format(date)}'> ${format(date)}
  </div>`
}
// this is the notification htnl 
const notificationHTML = (data) => {

    // Map notification types to icon classes
    // Map type → { icon, colour }
const iconMap = {
    friend_request: { icon: "fa-solid fa-user-plus", color: "text-primary" },       // Blue
    like: { icon: "fa-solid fa-thumbs-up", color: "text-success" },                // Green
    comment: { icon: "fa-solid fa-comment-dots", color: "text-info" },             // Cyan
    Anniversary: { icon: "fa-solid fa-cake-candles", color: "text-warning" },      // Gold
    Birthday: { icon: "fa-solid fa-cake-candles", color: "text-warning" },         // Gold
    Wedding: { icon: "fa-solid fa-heart", color: "text-warning" },                 // Gold
    new_post: { icon: "fa-solid fa-file-lines", color: "text-purple" },            // Custom purple
    House_Warming: { icon: "fa-solid fa-house", color: "text-orange" },            // Orange
    Reunion: { icon: "fa-solid fa-people-group", color: "text-pink" },             // Pink
    Party: { icon: "fa-solid fa-champagne-glasses", color: "text-danger" },        // Red
    Meeting: { icon: "fa-solid fa-handshake", color: "text-teal" },                // Teal
    default: { icon: "fa-solid fa-bell", color: "text-secondary" }                 // Grey
};

    const { icon, color } = iconMap[data.notification_type] || iconMap.default

    const readOrUnread = (data.notification_status === 'new') ? 'unread' : 'read'
    const { sender_id, notification_name, notification_content, created_at, no } = data


    // generate random numbers to make the notification unique

    let randomNumber = Math.floor(100 + Math.random() * 900);

    return `<a id = "notificationBar${sender_id}${randomNumber}" href="#linkNotification${no}"  class="list-group-item list-group-item-action d-flex align-items-start notification_real_time ${readOrUnread} notification-item linkRequestCard">

    
            <div class="notification-icon ${color}">
                <i class="${icon}"></i></div>
            <div class="notification-text">
                <strong>${notification_name}</strong><br>
                <small>${notification_content}</small>
                <div class="notification-time"> ${postAgoNotification(created_at)} </div>
            </div>
            <button class="notification-delete btn btn-sm btn-outline-secondary btn-light" 
                 " 
                    data-no="${no}"
                    data-is="${sender_id}"
                    type="submit"
                    id="deleteNotification${sender_id}${randomNumber}"
                    aria-label="Delete notification">
               <i class="fa-solid fa-trash"></i>
            </button>
 

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


// delete a notification 

// delete notification 




// document.addEventListener('click', async (e) => {
//     const id = e.target.id;
//        log(id)
//     // if (!id.includes('deleteNotification')) return;

//     // const deleteBtn = id(id);
//     // const sender_id = deleteBtn.getAttribute('data-id');

//     // const url = `/removeNotification/${yourId}/${famCode}/${sender_id}`
//     // const response = axios.put(url)

//     // if (response.data.message === "Notification marked as read") {

//     //     // remove a html element with notificationBar after 2 mins 
//     //     qSel(`#${deleteBtn.id}`).closest('.notification_real_time')?.remove();

//     //     // reduce the notification count as you have deleted the notification

//     //     const newValues = parseInt(sessionStorage.getItem('notificationCount') - 1)
//     //     id('notification_count').innerHTML = newValues;
//     // } else {
//     //     msgException("Error removing notification" + " " + response.data.message);
//     // }
// })


const notificationBtn = id('notificationBtn');
const notificationDropdown = id('notificationDropdown');
const markAllReadBtn = id('markAllRead');
const notificationCount = id('notification_count');

// Toggle dropdown visibility
notificationBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    notificationDropdown.classList.toggle('show');
});

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
        notificationDropdown.classList.remove('show');
    }
});

// Prevent dropdown from closing when clicking inside it
notificationDropdown.addEventListener('click', function (e) {
    e.stopPropagation();
});

// Mark all as read functionality
markAllReadBtn.addEventListener('click', function () {
    const unreadItems = document.querySelectorAll('.notification-item.unread');
    unreadItems.forEach(item => {
        item.classList.remove('unread');
    });

    // Update notification count
    notificationCount.textContent = '0';
    notificationCount.style.display = 'none';
});

/* run once, after the dropdown HTML is in the page */
const initDeleteOnce = () => {
    const tab = document.getElementById('notification_tab'); // static parent
    if (!tab) return;

    tab.addEventListener('click', e => {
        const btn = e.target.closest('button[id*="deleteNotification"]');
        if (!btn) return;                   // not a delete button → ignore

        e.stopPropagation();                // keep dropdown open
        const bannerId = btn.id.replace('deleteNotification', 'notificationBar');
        const no = btn.getAttribute('data-no');
    

        const url = `/removeNotification/${no}`;


        axios.put(url)
            .then(response => {
                if (response.data.message === 'Notification marked as read') {
                    // remove a html element with notificationBar after 2 mins
                    document.getElementById(bannerId)?.remove();

                    // reduce the notification count as you have deleted the notification
                    const newValues = parseInt(sessionStorage.getItem('notificationCount') - 1);
                    sessionStorage.setItem('notificationCount', newValues);
                    id('notification_count').innerHTML = newValues;
                } else {
                    msgException('Error removing notification' + ' ' + response.data.message);
                }
                // your counter routine
            });
    })
}

/* safe entry point */
document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', initDeleteOnce)
    : initDeleteOnce();














///member/notifications