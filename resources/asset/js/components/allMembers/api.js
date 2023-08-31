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

const renderHtml = (el) => {
    if (el) {
        const theImg = `/img/profile/${el.img}`

        const approverObj = {
            approverFirstName: el.firstName,
            approverLastName: el.lastName,
            approverEmail: el.email,
            approverId: el.id
        }


        localStorage.setItem("approverDetails", JSON.stringify(approverObj))

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

                            ${famCode === el.famCode ?
                `  <br> <b>Father:</b>  ${el.fatherName}
                            <br> <b>Mother:</b> ${el.motherName}
                            <br> <b>Spouse:</b> ${el.spouseName && 'none'}
                            <br> <b>Email:</b>  ${el.email} 
                    
                            <br> <b>Mobile:</b>   ${el.mobile} 
                         
                            <br> <b>Date joined:</b> ${format(el.date_created)}
                            </p>

                              <div class="card-body">

                            <a href="/allMembers/setProfile?id=${el.id}" 
                            class="btn btn-primary card-link">
                               See Profile
                            </a> </div><div class="card-body">` :
                `<button type="button" class="btn btn-success" id="addFamily${el.id}">Add to family</button></div>`}       
                </div>
                
             
            </div>
        </div>`


        id('allMembers').insertAdjacentHTML('beforeend', html)

    } else {

        return `<p> Sorry, we could find the data</p>`
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
        let dataWithFamCode;
        if (famCode) {
            dataWithFamCode = response.data.filter(el => el.famCode == famCode)

            renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML);
        }

        // this is for the search input 
        // Define a function to handle input changes
        const handleInput = () => {
            // Get the value of the search input
            const searchInput = id('searchFamily');
            const inputVal = searchInput.value.trim().toLowerCase();

            allMembersContainer.innerHTML = "";

            // Clear the content if the input is empty
            if (inputVal === "") {

                // Render HTML for all members using forEach

                renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML);
            }

            // If there's an input value
            else {

                // Filter data based on input value checking first and last name
                let filteredData = response.data.filter(el =>
                    el.firstName.toLowerCase().includes(inputVal.toLowerCase()) || el.lastName.toLowerCase().includes(inputVal.toLowerCase())
                );
                if (filteredData.length === 0) {
                    allMembersContainer.innerHTML = "No matching name found.";
                } else {
                    // Render HTML for filtered members using map
                    filteredData.forEach(renderHtml)
                }

            }
        }

        // Attach input event listener to the search input
        id('searchFamily').addEventListener('input', handleInput);

    })
    // id('allMembers').classList.remove('loader')

    .catch(err => showError(err.message))