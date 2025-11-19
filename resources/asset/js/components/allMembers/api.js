import axios from "axios";
import { id, showError } from "../global";
import { renderHtml } from "./html";
import { handleInput } from "./handleInput";
import { getMultipleApiData } from "@modernman00/shared-js-lib"

const config = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
};
const reqId = localStorage.getItem('requesterId');
const URL = process.env.MIX_APP_URL2;
const allMembersContainer = id('allMembers');
const noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";

export const renderMembers = (data, container, noMemberMessage, html) => {
    // container.innerHTML = "";
    if (data) {
        data.forEach(html);
    } else if (!data) {
        container.innerHTML = noMemberMessage;
    } else {
        data.forEach(html);
    }
};

const url1 = `${URL}allMembers/processApiData`; // data based on famCode and reqMgt accepted and approved
const url2 = `${URL}allMembers/allData`; // all the users data

const [famCodeData, allUsers] = await getMultipleApiData(url1, url2);

// const dataWithFamCode = filterMembersByFamCode(data);
renderMembers(famCodeData.message, allMembersContainer, noMemberHTML, renderHtml);
// Remove the "loader" class after rendering is complete
id('setLoader').classList.remove('loader');
id('searchFamily').addEventListener('input', () => handleInput(
    allUsers.message,
    famCodeData.message,
    renderMembers
)
);
