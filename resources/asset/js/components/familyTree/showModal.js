 import { id } from '@shared'

 
    const personModal = id('personModal');
      const modalBody = id('modalBody');
 
 // Displays detailed info about a person in the modal
export const  showPersonDetails = (personData) => {
  // destroy the modal if it's already open
  if (personModal.classList.contains('show')) {
    personModal.classList.remove('show');
  }

        // deconstruct the data object and assign it to variables
  const { img, relation, fullName, spouseName, occupation, country, email, isRegistered, familyCode, maritalStatus } = personData;

  const inviteMessage = encodeURIComponent(`Hey! I just started building our family tree and saved your spot. Our family code is ${familyCode}. Click here to join and see the rest of the family! ${window.location.origin}/register`);

  const claimSpotHtml = !isRegistered ? `
    <div class="claim-spot-section mt-4" style="background: var(--card-bg); padding: 20px; border-radius: 15px; text-align: center; border: 1px dashed var(--primary-color);">
      <h4 style="color: var(--primary-color); font-family: 'Playfair Display', serif; margin-bottom: 10px;">Claim This Spot</h4>
      <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 15px;">Send a magic invite to let them join the tree!</p>
      <div style="display: flex; gap: 10px; justify-content: center;">
        <a href="whatsapp://send?text=${inviteMessage}" class="btn" style="background: #25D366; color: white; border-radius: 20px; padding: 8px 20px; text-decoration: none;"><i class="bi bi-whatsapp"></i> WhatsApp</a>
        <a href="sms:?body=${inviteMessage}" class="btn" style="background: var(--primary-color); color: white; border-radius: 20px; padding: 8px 20px; text-decoration: none;"><i class="bi bi-chat-text"></i> SMS</a>
      </div>
    </div>
  ` : '';

  // Inject HTML into modal body with person's details
  modalBody.innerHTML = `
    <div class="person-detail">
      <img src="${img}" alt="${fullName}" class="person-image">
      <div class="person-info">
        <h2 class="person-name">${fullName}</h2>
        <div class="person-relation">${relation}</div>
      </div>
    </div>
    <div class="detail-grid">
      <div class="detail-item">
        <div class="detail-label">Relationship</div>
        <div class="detail-value">${relation || 'Family Member'}</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Marital Status</div>
        <div class="detail-value">${maritalStatus || 'N/A'}</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Occupation</div>
        <div class="detail-value">${occupation || 'Not specified'}</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Location</div>
        <div class="detail-value">${country || 'N/A'}</div>
      </div>

      ${spouseName ? `
        <div class="detail-item">
          <div class="detail-label">Spouse / Partner</div>
          <div class="detail-value">${spouseName}</div>
        </div>
      ` : ''}

      ${email ? `
        <div class="detail-item" style="grid-column: span 2;">
          <div class="detail-label">Email Address</div>
          <div class="detail-value">${email}</div>
        </div>
      ` : ''}
    </div>
    ${claimSpotHtml}
  `;


   // Show the modal
  personModal.style.display = 'flex';
}