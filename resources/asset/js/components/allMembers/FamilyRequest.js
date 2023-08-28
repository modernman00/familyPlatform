import axios from "axios";
import { id, log, } from "../global"
import { showError } from "../global"




  document.onclick = (e) => {

    log(e.target.id)

    const ApproverId = e.target.id;

    id(ApproverId).innerHTML = "Request sent"
    // the request sent should also go to the database and should form the basis of the innerHTML OF THE BUTTON 

    /**
     * Get the requester's detail (firstName and Surname, profileImg)
     * Limit requester to people who already have upload their profile pics
     * Get the approver's details (firstName and Surname, email)
     * send notification to the requester and approvers homepage
     *    build a notification button 
     *    build a pop to show the notification 
     *    update the notification for new request, new post, events 
     *     send an email to the approver to approve the request 
     */

    



  }

  
