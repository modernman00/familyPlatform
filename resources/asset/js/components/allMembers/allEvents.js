import axios from 'axios';
import { id, showError, qSel, msgException, log } from '@shared';
import { deleteNotification } from '../global.js';
import { addToNotificationTab, increaseNotificationCount } from '../navbar';
import { friendRequestCard } from '../profilePage/htmlFolder/friendRequestCard';

// Attach a click event listener to the document
const reqId = localStorage.getItem('requesterId');
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
      const userId = targetId.replace('addFamily', '');

      // Fetch approver details for the user
      const approverDetails = await fetchApproverData(userId);

      const familyRequestData = {
        approver: approverDetails,
        emailPath: 'msg/request_premium',
      };

      // Send the family request data to the server for processing which returns the notification details for the approvers tab
      const result = await sendFamilyRequest(familyRequestData);

      if (result.data.status === 'success' && result.data.message === 'Request sent') {
        // Update the button's HTML and disable it
        updateButton(targetId, 'Request Sent');
      } else if (result.data.status === 'error' && result.data.message === 'Request already pending') {
        // Update the button's HTML and disable it
        updateButton(targetId, 'Request Pending');
      } else {
        // Update the button's HTML and disable it
        updateButton(targetId, 'Request Failed');
      }        


    } else if (targetId.includes('removeProfile')) {
      // Extract the user ID from the target ID
      const userId = targetId.replace('removeProfile', '');

      const url = `/allMembers/removeProfile/${userId}/${reqId}`;

      alert(url);

      // include a console to confirm if they truly want to delete the profile
      if (
        confirm(
          'You will no longer see the profile and associated posts. Are you sure you want to delete the profile?',
        )
      ) {
        const notificationHTML = qSel(`.member_profile_${userId}`);

        const response = await axios.delete(url);

        if (response.data.message === 'success') {
          // remove a html element with call member_profile
          notificationHTML.remove();
        } else {
          msgException(`Error deleting profile`);
        }
      }
    } else if (targetId.includes('seeProfile')) {

      // Extract the user ID from the target ID
      const userId = targetId.replace('seeProfile', '');

      // redirect to 'allMembers/setProfile/'+userId
      window.location.href = `/allMembers/seeProfile/${userId}`;

    } else if (targetId.includes('familyTree')) {

      // Extract the user ID from the target ID
      const userId = targetId.replace('familyTree', '');

      // redirect to 'allMembers/setProfile/'+userId
      window.location.href = `/organogram/${userId}`;
    }
    // else if (targetId.includes('deleteNotification')) {
    //   // Call the deleteNotification function to remove the notification
    //   deleteNotification(targetId);
    // }
    // Extract the user ID from the target ID
  } catch (error) {
    // Handle any errors that occur during execution
    showError(error);
  }
};

// Function to fetch approver data based on user ID
async function fetchApproverData(userId) {
  try {
    const result = await axios.get(
      `/members/familyRequestMgt/getApprover?id=${userId}`,
    );
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


// Function to send family request data to the server
function sendFamilyRequest(data) {
  try {
    return axios.post('/members/familyRequestMgt', data);
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
