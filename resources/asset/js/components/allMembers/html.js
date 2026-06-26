import { format } from "timeago.js"
import { id, showError, log } from "@shared"

const toSentenceCase = (str) => {
  if (str || typeof str == 'string')
    // {
    //     throw new Error('Invalid sentence for toSentenceCase function')
    // }
    return str
      .toLowerCase() // Convert the string to lowercase
      .split(' ')    // Split the string into words
      .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize the first letter of each word
      .join(' ');    // Join the words back into a string
}

export const renderHtml = (el) => {

  log(el)

  try {
    if (!el) {
      // Handle the case where 'el' is falsy, such as when data is not available.
      throw new Error('there is no data')
    }

    const theImg = `/resources/images/profile/${el.img}`;
    const status = el.status?.toLowerCase() || null;
    let statusButtonHTML = 'Connect';
    let tooltip = '';
    tooltip =
      status === 'rejected'
        ? 'You can send another request'
        : 'Send request';


    if (status === 'request sent') {
      statusButtonHTML = 'Request sent';
    }
    const disableButton = status === 'request sent' ? 'disabled' : '';

    // Create the HTML content based on whether the user is in the same family or not. // Refined card design
    const html = `
    <div class="member-card member_profile_${el.id}" id="${el.id}">
        <div class="card-cover"></div>
        
        <div class="avatar-wrapper">
             <img src="${el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image'}"  alt="Member-${el.firstName}" loading="lazy">
        </div>

        <div class="member-card-body">
            <h4 class="member-name">${toSentenceCase(el.firstName)} ${toSentenceCase(el.lastName)}</h4>
            
            <p class="member-location"><i class="bi bi-geo-alt"></i> ${el.country}</p>
            
            <div class="member-details">
                <div class="member-detail">
                    <i class="bi bi-hash"></i> 
                    <strong>${el.famCode}</strong>
                </div>
                <div class="member-detail">
                    <i class="bi bi-envelope"></i> 
                    <span class="text-truncate">${el.email}</span>
                </div>
                ${el.relationType !== 'other' ? `
                <div class="member-detail">
                    <i class="bi bi-link-45deg"></i> 
                    <span>${el.relationType}</span>
                </div>
                <div class="member-detail">
                    <i class="bi bi-calendar-check"></i> 
                    <span>Since ${format(el.created_at)}</span>
                </div>
                ` : ''}
            </div>

            <div class="member-interests">
                ${el.relationType !== 'other' ? `
                <button class="btn-profile" id="seeProfile${el.id}">
                    <i class="bi bi-person"></i> View Profile
                </button>
                <div class="d-flex gap-2">
                    <button class="btn-remove" style="color: var(--primary-color); border-color: var(--accent-color);" id="familyTree${el.id}">
                        <i class="bi bi-diagram-3"></i> Tree
                    </button>
                    <button class="btn-remove" id="removeProfile${el.id}">
                        <i class="bi bi-person-x"></i> Remove
                    </button>
                </div>
                ` : `
                <button class="btn-profile" 
                        data-user-id="addFamily${el.id}" 
                        id="addFamily${el.id}"
                        ${disableButton}>
                    <i class="bi bi-person-plus"></i> ${statusButtonHTML}
                </button>
                <small class="text-muted" style="font-size: 0.75rem; font-weight: 500;">${tooltip}</small>
                `}
            </div>
        </div>
    </div>
`;


    id('allMembers').insertAdjacentHTML('beforeend', html);

  } catch (error) {

    showError(error);

  }


};