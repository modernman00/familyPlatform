import { format } from "timeago.js"
import { id, showError } from "../global"

export const renderHtml = (el) => {

const famCode = localStorage.getItem('requesterFamCode')
const reqId = localStorage.getItem('requesterId')

try {
    if (!el) {
        // Handle the case where 'el' is falsy, such as when data is not available.
        throw new Error('there is no data')
    }

    const theImg = `/img/profile/${el.img}`;
    const isUserInSameFamily = famCode == el.famCode || famCode == el.requesterCode;
    const statusButtonHTML = el.status && el.requester_id === reqId && el.status !== 'Approved'
        ? el.status
        : 'Add to family';

   const disableButton =  statusButtonHTML === "Request sent"? "disabled" : "";

    // Create the HTML content based on whether the user is in the same family or not.
    const html = `
        <div class="col-sm-3 mb-3" id="${el.id}">
            <div class="card">
                <img src="${theImg}" 
                    class="card-img-top allMember_profileImg" 
                    width="200" height="300" alt="profile img">
    
                <div class="card-body">
                    <h5 class="card-title">${el.firstName} ${el.lastName}</h5>
                    <p class="card-text allMember_card_content">
                        <br> <b>Country:</b> ${el.country} 

                        ${isUserInSameFamily ?
                            `<br> <b>Father:</b> ${el.fatherName}
                             <br> <b>Mother:</b> ${el.motherName}
                             <br> <b>Spouse:</b> ${el.spouseName || 'none'}
                             <br> <b>Email:</b> ${el.email} 
                             <br> <b>famCode:</b> ${el.famCode} 
                             <br> <b>Mobile:</b> ${el.mobile} 
                             <br> <b>Date joined:</b> ${format(el.created_at)}</p>
                             <div class="card-body">
                                <a href="/allMembers/setProfile?id=${el.id}" class="btn btn-primary card-link">
                                    See Profile
                                </a>
                            </div>
                            <div class="card-body">`
                        :
                            `<button type="button" data-user-id="addFamily${el.id}" class="btn btn-success button" id="addFamily${el.id}" ${disableButton}>
                                ${statusButtonHTML}
                            </button>
                            
                            </div>`
                        }
                    </div>
                </div>
            </div>
        </div>`;

    // Insert the HTML content into the 'allMembers' element.
    id('allMembers').insertAdjacentHTML('beforeend', html);
  
} catch (error) {

  showError(error);
  
}

  
};
