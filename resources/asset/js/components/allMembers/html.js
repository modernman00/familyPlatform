import { format } from "timeago.js"
import { id, showError } from "@shared"
import { esc } from "../global"

const toSentenceCase = (str) => {
  if (!str || typeof str !== 'string') return '';
  return str
    .toLowerCase()
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

export const renderHtml = (el) => {
  try {
    if (!el) {
      throw new Error('there is no data');
    }

    const theImg = el.img ? `/resources/images/profile/${el.img}` : '/resources/images/profile/avatarM.png';
    const status = el.status?.toLowerCase() || null;
    let statusButtonHTML = 'Connect';
    let tooltip = (status === 'rejected') ? 'You can send another request' : 'Send connection request';

    if (status === 'request sent') {
      statusButtonHTML = 'Request Sent';
    }
    const disableButton = (status === 'request sent') ? 'disabled' : '';

    // Determine Relation Category & Human-Friendly Status Chip
    const relType = el.relationType || 'other';
    let roleChipHtml = '';
    let categoryKey = 'explore';

    if (relType === 'family') {
      categoryKey = 'family';
      roleChipHtml = `<span class="role-chip role-chip-family"><i class="bi bi-house-heart-fill"></i> Same Family</span>`;
    } else if (relType === 'approved_you' || relType === 'you_approved') {
      categoryKey = 'connected';
      roleChipHtml = `<span class="role-chip role-chip-connected"><i class="bi bi-check2-circle"></i> Connected Kin</span>`;
    } else {
      categoryKey = 'explore';
      roleChipHtml = `<span class="role-chip role-chip-directory"><i class="bi bi-globe2"></i> Directory</span>`;
    }

    // el.* fields are user-authored — escape every one before it hits innerHTML (SEC-2).
    const idSafe = esc(el.id);
    const firstName = toSentenceCase(el.firstName || '');
    const lastName = toSentenceCase(el.lastName || '');
    const fullNameRaw = `${firstName} ${lastName}`.trim();
    const fullName = esc(fullNameRaw);
    const location = esc(el.country ? toSentenceCase(el.country) : 'Location not set');
    const famCode = esc(el.famCode ? String(el.famCode).toUpperCase() : 'CODE');
    const emailSafe = esc(el.email || 'Email not provided');
    const imgSafe = esc(theImg);

    const html = `
    <div class="member-card member_profile_${idSafe}" id="${idSafe}" data-category="${categoryKey}" data-search="${esc(fullNameRaw.toLowerCase() + ' ' + (el.email || ''))}">
        <div class="card-cover"></div>

        <div class="avatar-wrapper">
             <img src="${imgSafe}" alt="${fullName}" loading="lazy">
             <span class="avatar-online-dot"></span>
        </div>

        <div class="member-card-body">
            ${roleChipHtml}
            <h4 class="member-name text-truncate" title="${fullName}">${fullName}</h4>

            <span class="member-location-chip">
                <i class="bi bi-geo-alt-fill text-danger"></i> ${location}
            </span>

            <div class="member-details">
                <div class="member-detail">
                    <i class="bi bi-hash"></i>
                    <span class="fw-bold">${famCode}</span>
                </div>
                <div class="member-detail">
                    <i class="bi bi-envelope"></i>
                    <span class="text-truncate">${emailSafe}</span>
                </div>
                ${relType !== 'other' ? `
                <div class="member-detail">
                    <i class="bi bi-calendar-check"></i> 
                    <span>Connected ${format(el.created_at)}</span>
                </div>
                ` : ''}
            </div>

            <div class="member-interests">
                ${relType !== 'other' ? `
                <button class="btn-stitch-primary" id="seeProfile${idSafe}">
                    <i class="bi bi-person-badge-fill" style="pointer-events: none;"></i> View Profile
                </button>
                <div class="d-flex gap-2">
                    <button class="btn-stitch-tonal flex-grow-1" id="familyTree${idSafe}" title="View in Family Tree">
                        <i class="bi bi-diagram-3-fill" style="pointer-events: none;"></i> Tree
                    </button>
                    <button class="btn-stitch-danger flex-grow-1" id="removeProfile${idSafe}" title="Remove Connection">
                        <i class="bi bi-person-dash-fill" style="pointer-events: none;"></i> Remove
                    </button>
                </div>
                ` : `
                <button class="btn-stitch-connect" 
                        data-user-id="addFamily${idSafe}" 
                        id="addFamily${idSafe}"
                        ${disableButton}>
                    <i class="bi bi-person-plus-fill" style="pointer-events: none;"></i> ${statusButtonHTML}
                </button>
                <small class="text-muted text-center" style="font-size: 0.75rem; font-weight: 500;">${tooltip}</small>
                `}
            </div>
        </div>
    </div>
    `;

    const container = id('allMembers');
    if (container) {
      container.insertAdjacentHTML('beforeend', html);
    }
  } catch (error) {
    showError(error);
  }
};
