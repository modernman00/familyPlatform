import axios from "axios";
import { id, showError } from "../global"
import { addToNotificationTab, increaseNotificationCount } from '../navbar'
import { friendRequestCard } from "../profilePage/htmlFolder/friendRequestCard";

// Attach a click event listener to the document

/**
 * Attach a click event listener to the document. When a button with the id `addFamily<userId>` is clicked, send a family request to the user identified by the userId and update the button's HTML and disable it.
 it returns the notification details for the approvers tab
 * 
 * @param {MouseEvent} e - The event object.
 */
document.onclick = async (e) => {
    try {
        // Get the target element's ID
        const targetId = e.target.id;

        // Check if the ID includes 'addFamily'
        if (targetId.includes('addFamily')) {
            // Extract the user ID from the target ID
            const userId = targetId.replace("addFamily", "");

            // Fetch approver details for the user
            const approverDetails = await fetchApproverData(userId);

            // Prepare family request data
            const requesterDetails = getLocalStorageProfile();

            const familyRequestData = {
                requester: requesterDetails,
                approver: approverDetails,
                emailPath: "msg/request",
                subject: `${requesterDetails.requesterFirstName} ${requesterDetails.requesterLastName} sent you a family request`,
            };


            // Send the family request data to the server for processing which returns the notification details for the approvers tab
            const response = await sendFamilyRequest(familyRequestData);


            // ADD TO THE NOTIFICATION TAB OF THE APPROVER if the famcode on local storage is the same as the approverFamCode
            const famCode = localStorage.getItem('requesterFamCode');
            if (famCode === approverDetails.approverCode) {
                addToNotificationTab(response.data.message);
                friendRequestCard(requesterDetails)
                increaseNotificationCount();
            }

            // Update the button's HTML and disable it
            updateButton(targetId, 'Request Sent');

        }
    } catch (error) {
        // Handle any errors that occur during execution
        showError(error);
    }
};

// Function to fetch approver data based on user ID
async function fetchApproverData(userId) {
    try {
        const result = await axios.get(`/members/familyRequestMgt/getApprover?id=${userId}`);
        const approverDetails = {
            approverFirstName: result.data.message.firstName,
            approverLastName: result.data.message.lastName,
            approverEmail: result.data.message.email,
            approverId: result.data.message.id,
            approverCode: result.data.message.famCode,
        };
        return approverDetails;
    } catch (error) {
        console.error(error);
        throw error;
    }
}

// Function to retrieve requester details from local storage
function getLocalStorageProfile() {
    const getRequesterDetails = localStorage.getItem('profile');
    return JSON.parse(getRequesterDetails);
}

// Function to send family request data to the server
async function sendFamilyRequest(data) {
    try {
        return await axios.post("/members/familyRequestMgt", data);
    } catch (error) {
        showError(error);
    }
}

// Function to update the button's HTML and disable it
function updateButton(targetId, newHTML) {
    const theTargetId = id(targetId);
    theTargetId.innerHTML = newHTML;
    theTargetId.disabled = true;
}

// ADD THE NEW EVENT TO THE NOTIFICATION TAB
