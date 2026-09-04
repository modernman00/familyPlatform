import { id } from "@modernman00/shared-js-lib";
import { esc } from '../global';

const personModal = id('personModal');
const modalBody = id('modalBody');
const closeModal = id('closeModal');

/**
 * Open the Person Details Modal for a selected person
 * @param {Object} personData
 */
export const showPersonDetails = async (personData) => {
  const { 
    fullName = 'Family Member', 
    email = '', 
    img = '/resources/images/profile/avatarM.png', 
    relation = 'Relative', 
    maritalStatus = '', 
    spouseName = '', 
    occupation = '', 
    country = '', 
    personId = null, 
    nodeId = null,
    isRegistered = false, 
    familyCode = '', 
    isDeceased = false, 
    bio = '' 
  } = personData || {};

  const targetModal = id('personModal');
  const targetModalBody = id('modalBody');

  if (!targetModal || !targetModalBody) return;

  // Clean duplicate repeated words in full name (e.g. "AJIBIKE OLAOGUN OLAOGUN" -> "Ajibike Olaogun")
  let cleanName = (fullName || 'Family Member').trim().replace(/\s+/g, ' ');
  const parts = cleanName.split(' ');
  if (parts.length >= 2) {
    const deduped = [];
    for (let i = 0; i < parts.length; i++) {
      if (i > 0 && parts[i].toLowerCase() === parts[i - 1].toLowerCase()) {
        continue;
      }
      deduped.push(parts[i]);
    }
    cleanName = deduped.map(w => {
      if (w === w.toUpperCase() && w.length > 1) {
        return w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
      }
      return w;
    }).join(' ');
  }

  const inviteLink = `${window.location.origin}/register?famCode=${encodeURIComponent(familyCode)}&name=${encodeURIComponent(cleanName)}`;
  const inviteMessage = encodeURIComponent(
    `🌳 *Family Tree Invitation* 🌳\n\n` +
    `Hey *${cleanName}*! You've been added to our family tree on FamilyPlatform.\n\n` +
    `Click below to claim your spot, explore our lineage, and connect with the family:\n` +
    `👉 ${inviteLink}`
  );

  const claimSpotHtml = !isRegistered ? `
    <div class="claim-spot-section mt-4" style="background: var(--gold-light); padding: 18px; border-radius: 18px; text-align: center; border: 1px dashed var(--gold-accent);">
      <h4 style="color: var(--primary-color); font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Invite Relative to Claim This Spot</h4>
      <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">Send a personal invitation so they can join and share family memories.</p>
      <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
        <a href="https://api.whatsapp.com/send?text=${inviteMessage}" target="_blank" rel="noopener noreferrer" class="btn" style="background: #25D366; color: white; border-radius: 20px; padding: 8px 18px; font-size: 0.86rem; text-decoration: none; font-weight: 600;">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
        <a href="sms:?body=${inviteMessage}" class="btn" style="background: var(--primary-color); color: white; border-radius: 20px; padding: 8px 18px; font-size: 0.86rem; text-decoration: none; font-weight: 600;">
          <i class="bi bi-chat-text"></i> SMS
        </a>
      </div>
    </div>
  ` : '';

  // Inject content into modal body — every field below is user-controlled
  // family-member data, so escape it before it touches innerHTML (SEC-2).
  targetModalBody.innerHTML = `
    <div class="person-detail">
      <img src="${esc(img)}" alt="${esc(fullName)}" class="person-image">
      <div class="person-info">
        <h2 class="person-name">${esc(fullName)}</h2>
        <div class="person-relation">${esc(relation || 'Relative')}</div>
        ${isDeceased ? '<span class="badge bg-secondary ms-2" style="font-size: 0.75rem;"><i class="bi bi-flower1"></i> Deceased</span>' : ''}
      </div>
    </div>

    <div class="detail-grid">
      <div class="detail-item">
        <div class="detail-label">Relationship</div>
        <div class="detail-value">${esc(relation || 'Family Member')}</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Marital Status</div>
        <div class="detail-value">${esc(maritalStatus || 'N/A')}</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Occupation</div>
        <div class="detail-value">${esc(occupation || 'Not specified')}</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Location / Origin</div>
        <div class="detail-value">${esc(country || 'N/A')}</div>
      </div>

      ${spouseName ? `
        <div class="detail-item" style="grid-column: span 2;">
          <div class="detail-label">Spouse / Partner</div>
          <div class="detail-value" style="display: flex; align-items: center; justify-content: space-between;">
            <span>${esc(spouseName)}</span>
            <span class="badge" style="background: var(--gold-light); color: var(--primary-dark); font-size: 0.75rem;"><i class="bi bi-heart-fill text-danger"></i> Partner</span>
          </div>
        </div>
      ` : ''}

      ${email ? `
        <div class="detail-item" style="grid-column: span 2;">
          <div class="detail-label">Email Address</div>
          <div class="detail-value">${esc(email)}</div>
        </div>
      ` : ''}
    </div>

    ${bio ? `
      <div class="detail-item mt-3" style="width: 100%;">
        <div class="detail-label">Notes & Biography</div>
        <div class="detail-value" style="font-size: 0.9rem; font-weight: 500;">${esc(bio)}</div>
      </div>
    ` : ''}

    <div id="extraUnionsSection"></div>

    ${claimSpotHtml}

    <div class="modal-actions mt-4" style="display: flex; gap: 10px; justify-content: flex-end;">
      ${personId ? `
        <a href="/allMembers/seeProfile/${encodeURIComponent(personId)}" class="btn" style="background: var(--primary-color); color: white; border-radius: 14px; padding: 8px 20px; font-weight: 600; text-decoration: none;">
          <i class="bi bi-person-fill"></i> View Profile
        </a>
      ` : ''}
      <button type="button" class="btn" id="modalCloseActionBtn" style="background: #e2e8f0; color: #334155; border-radius: 14px; padding: 8px 18px; font-weight: 600;">
        Close
      </button>
    </div>
  `;

  // Attach close handlers
  const closeActionBtn = id('modalCloseActionBtn');
  closeActionBtn?.addEventListener('click', () => {
    targetModal.style.display = 'none';
  });

  // Display modal
  targetModal.style.display = 'flex';
};

// Global modal close handlers
const initModalClosers = () => {
  const modal = id('personModal');
  const closeBtn = id('closeModal');

  closeBtn?.addEventListener('click', () => {
    if (modal) modal.style.display = 'none';
  });

  window.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
};

initModalClosers();
window.showPersonDetails = showPersonDetails;