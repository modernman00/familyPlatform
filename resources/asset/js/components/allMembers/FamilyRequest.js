import axios from "axios";
import { id, log, } from "../global"
import { showError } from "../global"
// import axios from "axios";

document.onclick = (e) => {

    try {

        // put the target id into a variable
        const targetId = e.target.id;
        // check if the id includes addFamily
        if (targetId.includes('addFamily')) {

            // change the button HTML to request sent and disable the button so it cant be used again
            // const changedBtnHTML = "Request sent"
            // id(targetId).innerHTML = changedBtnHTML

            // if (changedBtnHTML === "Request sent") {
            //     theTargetId.disabled = true;
            // }

            // update the database (profile_pics - buttonHTML)
            const theTargetId = id(targetId)

            const removedAddFriendFromId = targetId.replace("addFamily", "");

            // use the id of the button to get the approver details




            async function fetchApproverData() {
                try {
                    const result = await axios.get(`/members/familyRequestMgt/getApprover?id=${removedAddFriendFromId}`);
                    const approverDetails = {
                        approverFirstName: result.data.message.firstName,
                        approverLastName: result.data.message.lastName,
                        approverEmail: result.data.message.email,
                        approverId: result.data.message.id,
                        approverCode: result.data.message.famCode
                    };

                               // Retrieve the requester details JSON string from localStorage and parse it back to an object. it was set on the member/includes/personal.blade.php
            const getRequesterDetails = localStorage.getItem('profile');
            const requesterDetails = JSON.parse(getRequesterDetails);

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
                    theTargetId.innerHTML = response.data.message
                    theTargetId.disabled = true;
                    log(response.data.message)

                })
                .catch((error) => {

                    showError(error);
                });





                } catch (error) {
                    console.error(error);
                }
            }


        }


    } catch (error) {

        log(error)

    }





}