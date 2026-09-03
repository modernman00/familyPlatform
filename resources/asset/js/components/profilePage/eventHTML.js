"use strict"
import { date2String, esc } from "../global"

export const eventHtml = (data) => {

    // sender_name + notification_* are user-authored event fields — escape (SEC-2).
    return `<p class='eventInfo'>
            <strong>RSVP: </strong> ${esc(data.sender_name)}</p>
            <p class='eventInfo'><strong>Event: </strong>${esc(data.notification_name)}</p>
            <p class='eventInfo'><strong>Date: </strong>${esc(date2String(data.notification_date))} </p>
            <p class='eventInfo'><strong>Type: </strong>${esc(data.notification_type)}</p>
            <p class='eventInfo'><strong>Description: </strong> ${esc(data.notification_content)}</p>
            <input type='hidden' name='event_no' id='event${esc(data.no)}' value='${esc(data.no)}'>


           <hr>`;

//                        <button 
//     type="button" 
//     id="coming${data.data.no}"
//     class="w3-button w3-tiny w3-theme-d2 w3-margin-bottom">
//       <em class="fa fa-comment"></em> 
//         Coming 
//     </button>
// 
}