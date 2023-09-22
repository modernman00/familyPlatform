import axios from "axios";
import { id, log, showError, } from "../global"
import { format } from "timeago.js"
import { loaderIcon } from "../helper/general"

const config = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
}

const famCode = localStorage.getItem('requesterFamCode')
const reqId = localStorage.getItem('requesterId')

const renderHtml = (el) => {
    if (el) {

        const theImg = `/img/profile/${el.img}`

        // only show this button if the famcode and el.familyCode do not match

        id('allMembers').classList.remove('loader')
        // const img = (el.img) 

        const html = `
        <div class="col-sm-3 mb-3" id=${el.id}>
            <div class="card">
                <img src="${theImg}" 
                    class="card-img-top allMember_profileImg" 
                    width="200" height="300" alt="profile img">
    
                <div class="card-body">
                            <h5 class='card-title'>${el.firstName} ${el.lastName}</h5>
                            <p class="card-text allMember_card_content">
                             <br> <b>Country:</b>  ${el.country} 
                             <br> <b>ref:</b>  ${el.id}
                             <br> <b>Current User:</b>  ${reqId}
                             <br> <b>requester_id:</b>  ${el.requester_id}
                             <br> <b>famCode:</b>  ${el.famCode}
                             <br> <b>Email:</b>  ${el.email} 
                             <br> <b>Status :</b>  ${el.status}   

                            ${famCode == el.famCode || famCode == el.requesterCode ?
                `<br> <b>Father:</b>  ${el.fatherName}
                                    <br> <b>Mother:</b> ${el.motherName}
                                    <br> <b>Spouse:</b> ${el.spouseName && 'none'}
                                    <br> <b>Email:</b>  ${el.email} 
                                    <br> <b>famCode:</b>  ${el.famCode} 
                                    <br> <b>Mobile:</b>   ${el.mobile} 
                                    <br> <b>Date joined:</b> ${format(el.created_at)}
                                    </p>
                                    <div class="card-body">
                                    <a href="/allMembers/setProfile?id=${el.id}" 
                                    class="btn btn-primary card-link">
                                    See Profile
                                    </a> </div><div class="card-body">`
                : `<button type="button" class="btn btn-success" id="addFamily${el.id}">
                                        ${el.status && el.requester_id === reqId && el.status !== 'Approved'  ? el.status : `Add to family`}
                                        </button></div>`}       
                                        </div>
            </div>
        </div>`


        id('allMembers').insertAdjacentHTML('beforeend', html)

    } else {

        return `<p> Sorry, we couldn't find the data</p>`
    }

}

const URL = process.env.MIX_APP_URL2
const allMembersContainer = id('allMembers');
const noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";
const renderMembers = (data, container, noMemberMessage) => {
    container.innerHTML = ""; // Clear container content

    if (data) {
        data.forEach(renderHtml); // Render data if available
    } else {
        container.innerHTML = noMemberMessage; // Display no member message
    }
};

/** it will only show all members with the same code but will search the only ecosystem */

axios.get(URL + 'allMembers/processApiData', config)
    .then(function (response) {
        loaderIcon()
        // add loader
        id('allMembers').classList.add('loader')
        id('allMembers').innerHTML = ""
        // check if the family code is set and if so, filter the data
        // let dataWithFamCode;
        if (!response.data) throw Error(' there is no data')
        if (!famCode) throw Error(' there is no famCode')

        const filterMembersByFamCode = (data, famCode) => {
            return data.filter(el => el.id !== reqId && el.famCode === famCode || el.requesterCode === famCode);
        }

        const data = response.data;
        //  log(data)
        const dataWithFamCode = filterMembersByFamCode(data, famCode);

        renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML);

        // this is for the search input 
        // Define a function to handle input changes
        const handleInput = () => {
            // Get the value of the search input
            const searchInput = id('searchFamily');
            const inputVal = searchInput.value.trim().toLowerCase();

            allMembersContainer.innerHTML = "";

            // Clear the content if the input is empty
            if (inputVal === "") {

                // Render HTML for all members with the same famcode using forEach

                renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML);
            }
            else {// If there's an input value

                // Filter data based on input value checking first and last name
                let filteredData = data.filter(el =>
                    el.firstName.toLowerCase().includes(inputVal.toLowerCase()) || el.lastName.toLowerCase().includes(inputVal.toLowerCase())
                );
                if (filteredData.length === 0) {
                    allMembersContainer.innerHTML = "No matching name found.";
                } else {

                    // log(filteredData);

                    // Create an object to store unique items based on id and prioritize "reqId" if there's a duplicate.
                    const uniqueItems = {};

                    for (const item of filteredData) {
                        if (!uniqueItems[item.id] || item.requester_id == reqId) {
                            uniqueItems[item.id] = item;
                        }
                    }

                    // Convert the object back to an array.
                    const filteredDataByIdAndCurrentUser = Object.values(uniqueItems);

                    log(filteredDataByIdAndCurrentUser);

                    filteredDataByIdAndCurrentUser.forEach(renderHtml)
                }

            }
        }

        // Attach input event listener to the search input
        id('searchFamily').addEventListener('input', handleInput);

    })
    // id('allMembers').classList.remove('loader')

    .catch(err => showError(err.message))