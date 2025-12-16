import { qSel, log } from '@shared'
const appUrl = process.env.MIX_APP_URL2;
const approverId = localStorage.getItem('requesterId')

export const friendRequestCard = (data) => {

  const imageUrl = data.img ? `/resources/images/profile/${data.profilePics}` : data.requesterProfileImg;
  const firstName = encodeURIComponent(data.firstName ?? data.requesterFirstName);
  const lastName = encodeURIComponent(data.lastName ?? data.requesterLastName);
  const requestId = encodeURIComponent(data.id ?? data.requesterId);
  const requestCode = encodeURIComponent(data.famCode ?? data.requesterFamCode);
  const gender = data.gender ?? "";
  const occupation = data.occupation ?? "";
  const country = data.country ?? ""
  const mutualFriends = '';


  /* New modern colorful card design */
  const html = `<p id=${requestId}_linkRequestCard></p>
    <div class="card border-0 shadow-sm mb-4 friend-request-card" style="border-radius: 12px; overflow: hidden; transition: box-shadow 0.3s ease;">
      <div class="friend-request-cover" style="height: 70px; background: linear-gradient(135deg, var(--primary-color), #00c6ff);"></div>
      <div class="card-body p-3 pt-0 position-relative">
        <div class="d-flex justify-content-between align-items-end mb-2" style="margin-top: -35px;">
           <a href="/allMembers/seeProfile/${requestId}" class="position-relative">
            <img src="${imageUrl}" alt="${firstName}" class="avatar rounded-circle border border-4 border-white shadow-sm" style="width: 70px; height: 70px; object-fit: cover; background-color: white;">
             <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1" style="width: 12px; height: 12px;"></span>
          </a>
        </div>

        <div class="mb-3">
           <h6 class="mb-0 fw-bold text-truncate" style="font-size: 1.1rem;">
              <a href="/allMembers/seeProfile/${requestId}" class="text-dark text-decoration-none">${firstName} ${lastName}</a>
            </h6>
            ${occupation ? `<div class="small text-muted text-truncate fw-medium">${occupation}</div>` : ''}
             <div class="d-flex align-items-center mt-1 text-muted small">
                ${country ? `<span class="me-2"><i class="bi bi-geo-alt-fill me-1 text-primary"></i>${country}</span>` : ''}
                <span>${mutualFriends ? mutualFriends : 'New to interactions'}</span>
            </div>
        </div>

        
        <div class="d-flex gap-2 friend-request-actions">
          <a href="${appUrl}member/request/${requestId}/${approverId}/50/${requestCode}/pp" class="btn btn-primary btn-sm flex-grow-1 border-0 fw-medium shadow-sm" style="border-radius: 20px; padding: 6px 0; background: linear-gradient(to right, var(--primary-color), #00c6ff);">
            Confirm
          </a>
          <a href="${appUrl}member/request/${requestId}/${approverId}/10" class="btn btn-light btn-sm flex-grow-1 border-0 fw-medium text-secondary" style="border-radius: 20px; padding: 6px 0; background-color: #f0f2f5;">
            Decline
          </a>
        </div>
      </div>
    </div>
  `;

  qSel('.requestFriendClass').insertAdjacentHTML('afterbegin', html);













}