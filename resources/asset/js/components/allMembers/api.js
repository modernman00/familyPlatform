import axios from "axios";
import { id, showError } from "../global";
import { renderHtml } from "./html";
import filterMembersByFamCode from "./filterMembersByFamCode";
import { handleInput } from "./handleInput";

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


axios.get(`${URL}allMembers/processApiData?id=${reqId}`, config)
    .then(function (response) {

        id('allMembers').innerHTML = "";

        if (!response.data) {
            throw Error('There is no data');
        }

        const data = response.data;

        console.log(data);

        const dataWithFamCode = filterMembersByFamCode(data);

        renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML, renderHtml);

        // Remove the "loader" class after rendering is complete
        id('setLoader').classList.remove('loader');

        id('searchFamily').addEventListener('input', () => handleInput(
            data,
            dataWithFamCode,
            renderMembers
        )

        );


    })
    .catch(err => showError(err.message));