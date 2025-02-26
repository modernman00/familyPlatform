"use static"
import { date2String } from "../global"

export const eventHtml = (data) => {

    return `<p class='eventInfo'>
            <strong>RSVP: </strong> ${data.sender_name}</p>
            <p class='eventInfo'><strong>Event: </strong>${data.notification_name}</p>
            <p class='eventInfo'><strong>Date: </strong>${date2String(data.notification_date)} </p>
            <p class='eventInfo'><strong>Type: </strong>${data.notification_type}</p>
            <p class='eventInfo'><strong>Description: </strong> ${data.notification_content}</p>
            <input type='hidden' name='event_no' id='event${data.no}' value='${data.no}'>

            
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