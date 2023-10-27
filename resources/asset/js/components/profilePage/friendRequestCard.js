
import axios from 'axios'
import { log, qSel } from "../global"
const appUrl = process.env.MIX_APP_URL2;
const approver_id = sessionStorage.getItem('idSetFromHttp')

axios.get(`/getFriendRequestById?id=${approver_id}`)
  .then(results => {

      if (results.data.message) {
        results.data.message.forEach(request => {
          qSel('.requestFriendClass').insertAdjacentHTML('afterbegin', htmlFriendRequest(request));
        })
      }
})


// const countFriendRequest = (friend) => {
//   return friend.length > 1 ? `<p><b>Friend Requests</b></p><br></br>` : `<p><b>Friend Request</b></p><br>`;
// }

const imgFriendRequest = (data) => {
  return `<img src="/img/profile/${data.img}" alt="Avatar" style="width:50%"><br>`
}



const buttonFriendRequest = (data) => {
  return `<div class="w3-row w3-opacity" >
            <div class="w3-half">
              <a href=${appUrl}member/request?req=${data.id}&appr=${approver_id}&dec=50&reqCode=${data.famCode}&src=pp  style="text-decoration: none;"> 
              
                <button class="w3-button w3-block w3-green w3-section" title="Accept"><i class="fa fa-check"></i>
              
                </button>
              
              </a>

            </div>

            <div class="w3-half">
                  <a href=${appUrl}member/request?req=${data.id}&appr=${approver_id}&dec=10>
                  <button class="w3-button w3-block w3-red w3-section" title="Decline"><i class="fa fa-remove"></i></button>
              
                  </a>
             
            </div>
             
         </div>`
}

const name = (data) => {
  return `<span>${data.firstName} ${data.lastName}</span>`
}

const htmlFriendRequest = (data) => {
  return `
    <p id=${data.id}_linkRequestCard></p>
    ${imgFriendRequest(data)}
    ${name(data)}
    ${buttonFriendRequest(data)}
  `;
}




