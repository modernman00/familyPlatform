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
        const areTheyLinked = famCode == el.famCode || famCode == el.requesterCode;
        const related = famCode == el.famCode
        const statusButtonHTML = el.status && el.requester_id === reqId && el.status !== 'Approved' ?
            el.status :
            'Connect';

        const disableButton = statusButtonHTML === "Request sent" ? "disabled" : "";

        const fatherName = toSentenceCase(el.fatherName);
        const motherName = toSentenceCase(el.motherName);
        // const spouse = toSentenceCase(el.spouseName);

        // Create the HTML content based on whether the user is in the same family or not. // LinkedIn-like card design
        const html = `
    <div class="w3-col l3 m6 w3-margin-bottom member_profile_${el.id}" id="${el.id}" style="width: 270px;">
        <div class="w3-card w3-white w3-round-large member-card" style="height: 450px;">
    
            <div class="w3-display-container profile-image-container" style="height: 250px; overflow: hidden;">
                <img src="${el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image'}" 
                     class="w3-image profile-image w3-hover-opacity" 
                     style="width: 100%; height: 100%; object-fit: cover;" 
                     alt="${el.firstName}">
                <div class="w3-display-bottomleft w3-container w3-padding w3-text-white" style="text-shadow: 1px 1px 3px #000;">
                    <h3><b>${toSentenceCase(el.firstName)} ${toSentenceCase(el.lastName)}</b></h3>
                </div>
            </div>
    
            <div class="w3-container w3-padding" style="flex-grow: 1; overflow: hidden;">
                <p class="w3-text-gray"><i class="fa fa-map-marker"></i> ${el.country}</p>
                
                ${areTheyLinked ? `
                    <div class="w3-small w3-text-gray" style="max-height: 150px; overflow: hidden;">
                        <p><b>Father:</b> ${fatherName || 'Not specified'}</p>
                        <p><b>Mother:</b> ${motherName || 'Not specified'}</p>
                        <p><b>Spouse:</b> ${el.spouseName || 'Not specified'}</p>
                        <p><b>Mobile:</b> ${el.mobile || 'Not specified'}</p>
                        <p><b>Member since:</b> ${format(el.created_at)}</p>
                    </div>
                ` : ''}
                
                <div class="w3-row w3-margin-top">
                    ${areTheyLinked ? `
                        <div class="w3-col s6 w3-padding-small">
                            <button class="w3-button w3-blue w3-round-large w3-block" id="seeProfile${el.id}">
                                <i class="fa fa-user"></i> Profile
                            </button>
                        </div>
                        ${related ? '' : `
                        <div class="w3-col s6 w3-padding-small">
                            <button class="w3-button w3-light-gray w3-round-large w3-block" id="removeProfile${el.id}">
                                <i class="fa fa-times"></i> Remove
                            </button>
                        </div>
                        `}
                    ` : `
                        <div class="w3-col s12 w3-padding-small">
                            <button type="button" data-user-id="addFamily${el.id}" 
                                class="w3-button w3-blue w3-round-large" 
                                id="addFamily${el.id}" ${disableButton} onmouseover="pulseButton(this)" 
                                onmouseout="resetButton(this)">
                                <i class="fa fa-user-plus"></i> ${statusButtonHTML}
                            </button>
                        </div>
                    `}
                </div>
            </div>
        </div>
        </div>`;


        id('allMembers').insertAdjacentHTML('beforeend', html);

    } catch (error) {

        showError(error);

    }


};