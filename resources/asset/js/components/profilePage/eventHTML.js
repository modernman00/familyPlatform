"use static"
import { date2String } from "../global"

export const eventHtml = (data) => {

    return ` <div class="event-card card mb-3">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <small class='eventInfo'>
            <strong><strong>RSVP: </strong> ${data.sender_name}</small><br>
                            <small class='eventInfo'><strong>Event: </strong>${data.notification_name}</small><br>
                            <small class='eventInfo'><strong>Type: </strong>${data.notification_type}</small><br>
                            <small class='eventInfo'><strong>Description: </strong> ${data.notification_content}</small><br>
                            <small class='eventInfo'><strong>Date: </strong>${date2String(data.notification_date)} </small><br>
        
         
            <input type='hidden' name='event_no' id='event${data.no}' value='${data.no}'>

             <div class="mt-2 rsvp-buttons d-flex">
                                        <button class="btn btn-sm btn-outline-primary">Going</button>
                                        <button class="btn btn-sm btn-outline-secondary">Maybe</button>
                                        <button class="btn btn-sm btn-outline-danger">Decline</button>
                                    </div>

               </div>
                            </div>
                        </div>
                    </div>
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