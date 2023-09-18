import axios from "axios";
import { id, log, } from "../global"
import { showError } from "../global"
// import axios from "axios";





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




document.onclick = (e) => {

    try {

        // put the target id into a variable
        const targetId = e.target.id;
        // check if the id includes addFamily
        if (targetId.includes('addFamily')) {

            // change the button HTML to request sent and disable the button so it cant be used again
            const changedBtnHTML = "Request sent"
            id(targetId).innerHTML = changedBtnHTML

            if (changedBtnHTML === "Request sent") {
                id(targetId).disabled = true;
            }

            // update the database (profile_pics - buttonHTML)


            const getApproverDetails = localStorage.getItem('approverDetails');
            const approverDetails = JSON.parse(getApproverDetails);



            // Retrieve the requester details JSON string from localStorage and parse it back to an object. it was set on the member/includes/personal.blade.php
            const getRequesterDetails = localStorage.getItem('profile');
            const requesterDetails = JSON.parse(getRequesterDetails);

            // submit the approver and requester ids to database - RequestTable

            const familyRequestMgt = {
                requester: requesterDetails,
                approver: approverDetails,
                emailPath: "msg/request",
                subject: `${requesterDetails['firstName']} ${requesterDetails['lastName']} sent you a family request`,
            }

            // send for server processing 
            axios
                .post("/members/familyRequestMgt", familyRequestMgt)

            .then((response) => {
                    // change the html of the button
                    log(response.data.message)

                })
                .catch((error) => {

                    showError(error);
                });



        }


    } catch (error) {

        log(error)

    }





}