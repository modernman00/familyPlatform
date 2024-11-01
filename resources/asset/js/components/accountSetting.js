"use strict";
import "./register/onChangeKidSibling"
import { id, showError, manipulateAttribute, showElement, hideElement } from "./global"
import FormHelper from './FormHelper';
import axios from "axios";
import { processKidsSiblings } from './kidsAndSiblings';
import { fetchEmailData } from './api/index';

const formInput = document.querySelectorAll('.accountSettingForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);

const options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
}


const process = (e) => {
  try {
    e.preventDefault();
    const notificationDiv = id('accountSettingForm_notification')
    const notificationMsg = id('accountSettingForm_notification_error')
    notificationMsg.innerHTML = "" // may not be needed
    formData.massValidate();
    // log(formData.error)
    if (formData.error.length <= 0) {
      // get the form data
      const eventForm = id('accountSettingForm');
      let eventFormEntries = new FormData(eventForm);
      // post the form data to the database and get the last posted event no
      axios.post("/accountSetting", eventFormEntries, options).then(response => {

        notificationDiv.style.display = "block" // unblock the notification
        notificationDiv.classList.add('is-success') // add the success class
        notificationMsg.innerHTML = response.data.message

      })
      // window.location.replace("/member/profilePage")
    } else {
      alert('The form cannot be submitted. Please check the errors')
      formData.clearError()
    }

  } catch (error) {
    showError(error)
  }

}

// show spouse once select is Yes
// Add event listeners
// Function to show spouse information based on marital status
const showSpouse = () => {
  // Get the value of the marital status dropdown
  const maritalStatus = id('maritalStatus_id').value;

  // Check marital status and show relevant elements
  if (maritalStatus === "Yes - Add Husband") {
    // Display spouse section if adding husband
    showElement('spouse');
    manipulateAttribute('spouseName_id', 'set', 'name', 'spouseName')
    manipulateAttribute('spouseEmail_id', 'set', 'name', 'spouseEmail')
    manipulateAttribute('spouseMobile_id', 'set', 'name', 'spouseMobile')

  } else if (maritalStatus === "Yes - Add Wife") {
    // Display spouse section if adding wife
    showElement('spouseMaidenName_div');
    showElement('spouse');
    manipulateAttribute('spouseName_id', 'set', 'name', 'spouseName')
    manipulateAttribute('spouseEmail_id', 'set', 'name', 'spouseEmail')
    manipulateAttribute('spouseMobile_id', 'set', 'name', 'spouseMobile')
    manipulateAttribute('spouseMaidenName_id', 'set', 'name', 'spouseMaidenName')
    // Display maiden name and spouse sections if adding wife

  } else {
    // Hide spouse section if marital status is not "Yes"
    hideElement('spouse');
  }
};

// Add event listener to marital status dropdown to trigger showSpouse function
id('maritalStatus_id').addEventListener('change', showSpouse);

// Hide spouse and maiden name elements by default
hideElement('spouse');
hideElement('spouseMaidenName_div');

// remove input name attritube by default
manipulateAttribute('spouseName_id', 'remove', 'name')
manipulateAttribute('spouseMaidenName_id', 'remove', 'name')
manipulateAttribute('spouseEmail_id', 'remove', 'name')
manipulateAttribute('spouseMobile_id', 'remove', 'name')


id('submit').addEventListener('click', process)
// GET ALL EMAILS 

// Call the fetchData function to initiate the request

// const emailData = []
const fName = id('fName').textContent
const lastName = id('lName').textContent
const famCode = localStorage.getItem('requesterFamCode')


fetchEmailData()
  .then(data => {
    // Do something with the fetched data
    const emailData = data;

    // SEND EMAIL TO THE KIDS AND processKidsSibling
    processKidsSiblings(emailData, fName, lastName, famCode)
  })
  .catch(error => {
    // Handle any errors that occurred during the request or processing
    console.error('Error:', error);
  });

// Call the getEmailData function somewhere in your code

