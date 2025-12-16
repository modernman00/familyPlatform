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

    // Create the HTML content based on whether the user is in the same family or not. // LinkedIn-like card design
    const html = `
    <div class="member-card member_profile_${el.id}" id="${el.id}">

       <div class="card-cover">
            <img src="${el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image'}"  alt="Member-${el.firstName}"loading="lazy">
        </div>

        <div class="member-card-body">
            <h3 class="member-name">${toSentenceCase(el.firstName)} ${toSentenceCase(el.lastName)}</h3>
            <p class="member-location"><i class="bi bi-geo-alt-fill"></i> ${el.country}</p>
                <p class="member-location"><i class="bi bi-geo-alt-fill"></i> ${el.famCode}</p>
                  <p class="member-location"><i class="bi bi-geo-alt-fill"></i> ${el.email}</p>

  ${el.relationType !== 'other' ? `
    <div class="member-details">

         <p class="member-detail">  <i class="fa fa-link"></i><span>${el.relationType}</span></p>
          <p class="member-detail"> <i class="fa fa-calendar-alt"></i><span>Member since ${format(el.created_at)}</span></p>
    </div>

    <div class="member-interests">
      <button class="btn-profile primary-action" id="seeProfile${el.id}" tooltip="View Profile">
        <i class="fa fa-user"></i> View Profile
      </button>
         <button class="btn-profile" id="familyTree${el.id}" tooltip="View Tree">
        <i class="fa fa-sitemap"></i> Family Tree
      </button>
      <button class="btn-remove" id="removeProfile${el.id}" tooltip="Remove Connection">
        <i class="fa fa-user-times"></i> Remove
      </button>
    </div> 
  ` : `
    <div class="member-interests">
      <button class="btn-profile primary-action" 
              data-user-id="addFamily${el.id}" 
              id="addFamily${el.id}"
              ${disableButton}
              tooltip="${tooltip}">
        <i class="fa fa-user-plus"></i> ${statusButtonHTML}
      </button>
      <small>${tooltip}</small>
    </div>
  `}
</div>
    </div>
`;


    id('allMembers').insertAdjacentHTML('beforeend', html);

  } catch (error) {

    showError(error);

  }


};