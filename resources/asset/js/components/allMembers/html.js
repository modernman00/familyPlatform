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
  const famCode = localStorage.getItem('requesterFamCode')
  const reqId = localStorage.getItem('requesterId')

  try {
    if (!el) {
      // Handle the case where 'el' is falsy, such as when data is not available.
      throw new Error('there is no data')
    }
    log(el)

    const theImg = `/resources/images/profile/${el.img}`;
    const statusButtonHTML = el.status !== null ?
      el.status :
      'Connect';
      const relationshipType = (el.relationship)? el.relationship: 'Immediate Family';

    const disableButton = statusButtonHTML === "Request sent" ? "disabled" : "";

    const fatherName = toSentenceCase(el.father_name);
    const motherName = toSentenceCase(el.mother_name);
    const spouseName = toSentenceCase(el.spouse_name);
    // const spouse = toSentenceCase(el.spouseName);

    // Create the HTML content based on whether the user is in the same family or not. // LinkedIn-like card design
    const html = `
    <div class="member-card member_profile_${el.id}" id="${el.id}">

       <div class="card-cover">
            <img src="${el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image'}"  alt="Member-${el.firstName}" class= "card-img-top">
        </div>

        <div class="member-card-body">
            <h3 class="member-name">${toSentenceCase(el.firstName)} ${toSentenceCase(el.lastName)}</h3>
            <p class="member-location">${el.country}</p>

  ${el.relationType ? `
    <div class="member-details text-secondary mb-3 pb-3 border-bottom border-secondary">

         <p class="member-detail small mb-1">  <i class="fa fa-link me-2 text-danger"></i><b>Relationship Type:</b> ${el.relationType}</p>
          <p class="member-detail small mb-1"> <i class="fa fa-calendar-alt me-2 text-info"></i><b>Member since:</b> ${format(el.created_at)}</p>
    </div>

    <div class="member-interests">
      <button class="btn btn-sm btn-profile" id="seeProfile${el.id}" tooltip="View ${toSentenceCase(el.firstName)}'s Profile">
        <i class="fa fa-user"></i>Profile
      </button>
         <button class="btn btn-sm btn-profile" id="familyTree${el.id}" tooltip="View ${toSentenceCase(el.firstName)}'s Family Tree">
        <i class="fa fa-tree"></i> Family Tree
      </button>
      <span class="btn btn-sm btn-remove" id="removeProfile${el.id}" tooltip="Remove ${toSentenceCase(el.firstName)} from your family">
        <i class="fa fa-times"></i> 
      </span>
    </div>
  ` : `
    <div class="member-actions">
      <button class="btn btn-primary btn-sm w-100" 
              data-user-id="addFamily${el.id}" 
              ${disableButton}
              tooltip="Send a connection request to ${toSentenceCase(el.firstName)}">
        <i class="fa fa-user-plus"></i> ${statusButtonHTML}
      </button>
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