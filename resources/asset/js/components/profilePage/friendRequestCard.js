
import axios from 'axios'
import { log, showError } from "../global"
const approverId = encodeURIComponent(localStorage.getItem('requesterId')) // means that the user currently working on the UI
import { friendRequestCard } from './htmlFolder/friendRequestCard';


//NOTE - this code worked well 25/10/24
/**
 * Fetch friend requests by approver ID and render each request.
 */
const fetchFriendRequests = async () => {
  try{

  const response = await axios.get(`/getFriendRequestById?id=${approverId}`)

      if (response.data.message) {
        response.data.message.forEach(request => waitForRequestFriendClass(request));
      }

  } catch(error) {showError(error)} ;
}

// Wait for .requestFriendClass to appear in the DOM
const waitForRequestFriendClass = (data) => {
  const observer = new MutationObserver((mutations, obs) => {
    const requestContainer = document.querySelector('.requestFriendClass');
    if (requestContainer) {
       friendRequestCard(data);
      obs.disconnect(); // Stop observing once .requestFriendClass is found
    } else{
      log('there is no requestFriendClass')
    }
  });

  // Observe the entire body for changes in child elements
  observer.observe(document.body, { childList: true, subtree: true });
};


// TODO: Maybe a future enhancement to show count of friend request 
// const countFriendRequest = (friend) => {
//   return friend.length > 1 ? `<p><b>Friend Requests</b></p><br></br>` : `<p><b>Friend Request</b></p><br>`;
// }


// Fetch and render friend requests on page load

   fetchFriendRequests();








