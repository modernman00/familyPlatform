import { format } from "timeago.js"
import { id, showError } from "@shared"

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

        const theImg = `/public/img/profile/${el.img}`;
        const areTheyLinked = famCode == el.famCode || famCode == el.requesterCode;
        const related = famCode == el.famCode
        const statusButtonHTML = el.status && el.requester_id === reqId && el.status !== 'Approved' ?
            el.status :
            'Connect';

        const disableButton = statusButtonHTML === "Request sent" ? "disabled" : "";

        const fatherName = toSentenceCase(el.father_name);
        const motherName = toSentenceCase(el.mother_name);
        const spouseName = toSentenceCase(el.spouseName);
        // const spouse = toSentenceCase(el.spouseName);

        // Create the HTML content based on whether the user is in the same family or not. // LinkedIn-like card design
        const html = `
    <div class="member-card member_profile_${el.id}" id="${el.id}">

       <div class="member-card-header">
            <img src="${el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image'}"  alt="Member-${el.firstName}" class="member-avatar">
        </div>

        <div class="member-card-body">
            <h3 class="member-name">${toSentenceCase(el.firstName)} ${toSentenceCase(el.lastName)}</h3>
            <p class="member-location">${el.country}</p>

  ${areTheyLinked ? `
    <div class="member-details">
      <p class="member-detail"><b>Father:</b> ${fatherName || 'Not specified'}</p>
      <p class="member-detail"><b>Mother:</b> ${motherName || 'Not specified'}</p>
      <p class="member-detail"><b>Spouse:</b> ${spouseName || 'Not specified'}</p>
      <p class="member-detail"><b>Mobile:</b> ${el.mobile || 'Not specified'}</p>
      <p class="member-detail"><b>Member since:</b> ${format(el.created_at)}</p>
    </div>

            <div class="member-stats">
                        <div class="stat">
                            <div class="stat-number">342</div>
                            <div class="stat-label">Posts</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">1.2K</div>
                            <div class="stat-label">Followers</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">256</div>
                            <div class="stat-label">Following</div>
                        </div>
                    </div>

    <div class="member-interests">
      <button class="btn btn-profile" id="seeProfile${el.id}">
        <i class="fa fa-user"></i> See Profile
      </button>
      <span class="btn btn-remove" id="removeProfile${el.id}">
        <i class="fa fa-times"></i> Remove
      </span>
    </div>
  ` : `
    <div class="member-actions">
      <button class="btn btn-primary btn-sm w-100" 
              data-user-id="addFamily${el.id}" 
              ${disableButton}
              onmouseover="pulseButton(this)" 
              onmouseout="resetButton(this)">
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