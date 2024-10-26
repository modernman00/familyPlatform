import {qSel} from '../../global'
const appUrl = process.env.MIX_APP_URL2;
const approverId = encodeURIComponent(localStorage.getItem('requesterId'))


export const friendRequestCard = (data) => {

  const imageUrl = `/public/img/profile/${encodeURIComponent(data.img ?? data.requesterProfileImg)}`;
  const firstName = encodeURIComponent(data.firstName ?? data.requesterFirstName);
  const lastName = encodeURIComponent(data.lastName ?? data.requesterLastName);
  const requestId = encodeURIComponent(data.id ?? data.requesterId);
  const requestCode = encodeURIComponent(data.famCode ?? data.requesterFamCode);
  

  const html = `<p id=${requestId}_linkRequestCard></p>


  <img src="${imageUrl}" alt="Avatar" style="width:50%"><br>

   <span>${firstName} ${lastName}</span>


    <div class="w3-row w3-opacity">
      <div class="w3-half">
        <a href="${appUrl}member/request?req=${requestId}&appr=${approverId}&dec=50&reqCode=${requestCode}&src=pp" style="text-decoration: none;">
          <button class="w3-button w3-block w3-green w3-section" title="Accept"><i class="fa fa-check"></i></button>
        </a>
      </div>
      
      <div class="w3-half">
        <a href="${appUrl}member/request?req=${requestId}&appr=${approverId}&dec=10" style="text-decoration: none;">
          <button class="w3-button w3-block w3-red w3-section" title="Decline"><i class="fa fa-remove"></i></button>
        </a>
      </div>
    </div>
  `;

  qSel('.requestFriendClass').insertAdjacentHTML('afterbegin', html);













}