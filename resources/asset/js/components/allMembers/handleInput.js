import { id, showError,  showNotification } from "../global";
import { renderHtml } from "./html";
import { checkBox } from "@shared";
import axios from "axios";

const reqId = localStorage.getItem('requesterId');
const famCode = localStorage.getItem('requesterFamCode');

const allMembersContainer = id('allMembers');

const noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";


export const handleInput = (data, WithFamCode, renderMembers) => {
    const searchInput = id('searchFamily');
    const inputVal = searchInput.value.trim().toLowerCase();

    allMembersContainer.innerHTML = "";

    if (inputVal === "") {

        renderMembers(WithFamCode, allMembersContainer, noMemberHTML, renderHtml);

    } else {
        let filteredData = data.filter(el =>
            el.firstName.toLowerCase().includes(inputVal) || el.lastName.toLowerCase().includes(inputVal)
        );
        // if no match found, show a message with a checkbox to send a request to the new member to join the platform
        // the checkbox will show a form to enter the new member's name and email or mobile number
        // the form will have a submit button to send the request

        if (filteredData.length === 0) {

            allMembersContainer.innerHTML = `No matching name found-  Do you want us to send them a text/email to register to the platform</i>?</h4>${checkBox('newMemberRequest')} <br> 
            
            <input type="hidden" id="newMemberName" value="${inputVal}">

             <input type="type" id="newMemberRequestName" class="form-control" name="newMemberRequestName" data-yourName = "" placeholder="Enter their name" >

            <input type="type" id="newMemberRequestEmail" class="form-control" name="newMemberRequestEmail" data-yourName = "" placeholder="Enter their email address or mobile number" >

            <p id="loader" class="loader" style="display:none;"><p>
            <small id="newMemberRequest_help" class="form-text text-muted"></small>

            <button class="button is-primary" id="newMemberRequestBtn">Send Request</button>`;

             id('newMemberRequestEmail').style.display = 'none';
             id('newMemberRequestName').style.display = 'none';
             id('newMemberRequestBtn').style.display = 'none';

            id('newMemberRequestYes').addEventListener('click', () => {

                 id('newMemberRequestName').style.display = 'block';
                 id('newMemberRequestEmail').style.display = 'block';
                  id('newMemberRequestBtn').style.display = 'block';
            });

            id('newMemberRequestBtn').addEventListener('click', () => {
                const email = id('newMemberRequestEmail').value;
                const name = id('newMemberRequestName').value;
                const yourName = localStorage.getItem('yourName');
                const familyCode = localStorage.getItem('requesterFamCode');
                // check if email is an email or mobile number
                const mobileRegex = /^\+?[1-9]\d{1,14}$/; // Simple regex for international phone numbers
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Simple regex for email addresses
                const helpMsg = id('newMemberRequest_help');

                if (!emailRegex.test(email) && !mobileRegex.test(email)) {
                    helpMsg.innerHTML = 'Please enter a valid email address or mobile number.';
                
                    return;
                }

                if(!emailRegex.test(email) && mobileRegex.test(email)) {
                    // if it is a mobile number, ensure it starts with country code
                    if(!email.startsWith('+')) {
                        helpMsg.innerHTML = 'Please include the country code. E.g +2348036517179';
                 
                        return;
                    } else {
                        // send a text to the mobile number
                    }
                } else if (emailRegex.test(email)) {
                        if (name.length < 2) {
                            helpMsg.innerHTML = 'Please enter a valid name with at least 2 characters.';
                
                    return;
                }
            
                    // send an email to the email address
                       const postObj = {
                                mobile: "",
                                viewPath: "msg/contactNewMember",
                    
                                data: {
                                    email: email,
                                    mobile: "",
                                    name: name,
                                    familyCode: familyCode,
                                    yourName: yourName,
                                },
                    
                                subject: `${yourName} Wants You: Experience the Magic of your Family Network Today!`,
                            };

                              axios.post("/register/contactNewMember", postObj).then((response) => {

                                showNotification(`allMembers`, 'is-success', response.data.message);

                                helpMsg.innerHTML = "";

                            })
                                .catch((error) => {
                                    showNotification(`allMembers`, 'is-danger', error.message);
                              
                                });
                    
                }

            
                
            });



        } else {
            const uniqueItems = {};

            for (const item of filteredData) {
                if (!uniqueItems[item.id] || item.requester_id == reqId) {
                    uniqueItems[item.id] = item;
                }
            }

            const filteredDataByIdAndCurrentUser = Object.values(uniqueItems);
         
            filteredDataByIdAndCurrentUser.forEach(renderHtml);
        }
    }
};

