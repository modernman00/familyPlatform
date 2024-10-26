import { id, showError } from "../global";
import { renderHtml } from "./html";

const reqId = localStorage.getItem('requesterId');

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

        if (filteredData.length === 0) {
            allMembersContainer.innerHTML = "No matching name found.";
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