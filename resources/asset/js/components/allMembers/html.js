import { format } from "timeago.js"
import { id, showError } from "../global"
import { toSentenceCase } from "../helper/general"



export const renderHtml = (el) => {

    const famCode = localStorage.getItem('requesterFamCode')
    const reqId = localStorage.getItem('requesterId')

    try {
        if (!el) {
            // Handle the case where 'el' is falsy, such as when data is not available.
            throw new Error('there is no data')
        }

        const theImg = `/public/img/profile/${el.img}`;
        const isUserInSameFamily = famCode == el.famCode || famCode == el.requesterCode;
        const statusButtonHTML = el.status && el.requester_id === reqId && el.status !== 'Approved' ?
            el.status :
            'Add to family';

        const disableButton = statusButtonHTML === "Request sent" ? "disabled" : "";

        const fatherName = toSentenceCase(el.fatherName);
        const motherName = toSentenceCase(el.motherName);
        // const spouse = toSentenceCase(el.spouseName);

        // Create the HTML content based on whether the user is in the same family or not.
        const html = `
        <div class="w3-col l3 m6 w3-margin-bottom w3-round-xlarge" id="${el.id}">
          
                <img src="${theImg}" style="width:100%; height:300px;" alt="${el.firstName}">
                      <ul class="w3-ul w3-border w3-center w3-hover-shadow">
    
                    <li class="w3-black w3-large w3-padding-16">${toSentenceCase(el.firstName)} ${toSentenceCase(el.lastName)}</li>

                    <li class="w3-padding-8 allMember_card_content">
                         <b>Country:</b> ${el.country} </li>

                        ${isUserInSameFamily ?
                        `    <li class="w3-padding-8"> <b>Father:</b> ${fatherName}</li>
                             <li class="w3-padding-8"> <b>Mother:</b> ${motherName}</li>
                             <li class="w3-padding-8"> <b>Spouse:</b> ${el.spouseName || 'none'}</li>
                             <li class="w3-padding-8"> <b>Mobile:</b> ${el.mobile} </li>
                             <li class="w3-padding-8"> <b>Date joined:</b> ${format(el.created_at)}</li>

                             <li class="w3-light-grey w3-padding-16">
                             <button class="w3-button w3-green w3-padding-small">
                                <a href="/allMembers/setProfile?id=${el.id}">
                                    See Profile
                                </a>
                                </button>
                                
                            </li>`

                    :   `<li class="w3-light-grey w3-padding-16">
                            <button type="button" data-user-id="addFamily${el.id}" class="w3-button w3-green w3-padding-large button" id="addFamily${el.id}" ${disableButton}>
                                ${statusButtonHTML}
                            </button>
                            
                        </li>`
            }
                    
     
          </ul>
        </div>`;

        // Insert the HTML content into the 'allMembers' element.
        id('allMembers').insertAdjacentHTML('beforeend', html);

    } catch (error) {

        showError(error);

    }


};