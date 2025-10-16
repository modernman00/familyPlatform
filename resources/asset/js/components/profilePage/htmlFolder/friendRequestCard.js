import { qSel } from '@shared'
const appUrl = process.env.MIX_APP_URL2;
const approverId = encodeURIComponent(localStorage.getItem('requesterId'))


export const friendRequestCard = (data) => {

  const imageUrl = `/resources/images/profile/${encodeURIComponent(data.img ?? data.requesterProfileImg)}`;
  const firstName = encodeURIComponent(data.firstName ?? data.requesterFirstName);
  const lastName = encodeURIComponent(data.lastName ?? data.requesterLastName);
  const requestId = encodeURIComponent(data.id ?? data.requesterId);
  const requestCode = encodeURIComponent(data.famCode ?? data.requesterFamCode);
  const mutualFriends = '2 mutual friends';


  const html = `<p id=${requestId}_linkRequestCard></p>

    <div class="d-flex align-items-center mb-3 friend-request-card">
      <img src="${imageUrl}" alt="Avatar" class="avatar me-3><br>

        <div class="flex-grow-1">
          <h6 class="mb-0">${firstName} ${lastName}</h6>
          <small class="text-muted">${mutualFriends}</small>
        </div>
    </div>

    <div class="friend-request-actions mb-3">

              <a href="${appUrl}member/request/${requestId}/${approverId}/50/${requestCode}/pp" class="btn btn-sm btn-primary" title="confirm">Confirm</a>


              <a href="${appUrl}member/request/${requestId}/${approverId}/10" class="btn btn-sm btn-outline-secondary" title="Decline">Decline</a>

    </div>
  `;

  qSel('.requestFriendClass').insertAdjacentHTML('afterbegin', html);













}