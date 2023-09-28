import axios from "axios";
import { id, log, qSel, showError } from "../global";
import { loaderIcon } from "../helper/general";
import { renderHtml } from "./html";

const config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
};

const famCode = localStorage.getItem('requesterFamCode');
const reqId = localStorage.getItem('requesterId');
const URL = process.env.MIX_APP_URL2;
const allMembersContainer = id('allMembers');
const noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";

const renderMembers = (data, container, noMemberMessage) => {
  container.innerHTML = "";
  if (data) {
    data.forEach(renderHtml);
  } else {
    container.innerHTML = noMemberMessage;
  }
};

const filterMembersByFamCode = (data, famCode) => {
  return data.filter(el => el.id !== reqId && (el.famCode === famCode || el.requesterCode === famCode));
};

const handleInput = (data, WithFamCode) => {
  const searchInput = id('searchFamily');
  const inputVal = searchInput.value.trim().toLowerCase();
  allMembersContainer.innerHTML = "";

  if (inputVal === "") {
    renderMembers(WithFamCode, allMembersContainer, noMemberHTML);
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
      log(filteredDataByIdAndCurrentUser);
      filteredDataByIdAndCurrentUser.forEach(renderHtml);
    }
  }
};

axios.get(`${URL}allMembers/processApiData?id=${reqId}`, config)
  .then(function (response) {
    loaderIcon();
    id('allMembers').classList.add('loader');
    id('allMembers').innerHTML = "";

    if (!response.data) {
      throw Error('There is no data');
    }

    if (!famCode) {
      throw Error('There is no famCode');
    }

    const data = response.data;
   
    const dataWithFamCode = filterMembersByFamCode(data, famCode);

    renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML);

    id('searchFamily').addEventListener('input', ()=>handleInput(data, dataWithFamCode));
  })
  .catch(err => showError(err.message));
