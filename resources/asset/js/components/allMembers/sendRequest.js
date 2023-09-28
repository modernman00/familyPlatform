import axios from "axios";
import { id, log, } from "../global"
import { showError } from "../global"
// import axios from "axios";

// Attach a click event listener to the document
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

            // Send the family request data to the server for processing
            const response = await sendFamilyRequest(familyRequestData);

            // Update the button's HTML and disable it
            updateButton(targetId, response.data.message);

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
