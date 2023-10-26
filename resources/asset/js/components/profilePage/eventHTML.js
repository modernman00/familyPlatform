"use static"
import { date2String } from "../global"

export const eventHtml = (data) => {

    return `<p class='eventInfo'>
            <strong>RSVP: </strong> ${data.firstName} ${data.lastName}</p>
            <p class='eventInfo'><strong>Event: </strong>${data.eventName}</p>
            <p class='eventInfo'><strong>Date: </strong>${date2String(data.eventDate)}</p>
            <p class='eventInfo'><strong>Type: </strong>${data.eventType}</p>
            <p class='eventInfo'><strong>Description: </strong> ${data.eventDescription}</p>
            <input type='hidden' name='event_no' id='event${data.no}' value='${data.no}'>
            
           <hr>`;
}