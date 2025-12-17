"use strict";
import "./accountSettingHelpers/handleFamilyChangeBootstrap"
import { id, showError, manipulateAttribute, showElement, hideElement } from "./global"
import { loginSubmission, update } from '@modernman00/shared-js-lib'
import { processKidsSiblings } from './kidsAndSiblings';
import { fetchEmailData } from './api/index';


update({
  formId: 'accountSettingForm',
  route: '/accountSetting',
  buttonId: 'button',
  redirect: '/accountSetting'
})


// Hide spouse and maiden name elements by default
hideElement('spouse');
hideElement('maiden_name_div');

// Function to show spouse information based on marital status
const showSpouse = () => {
  // Get the value of the marital status dropdown
  const maritalStatus = id('maritalStatus').value;

  // Check marital status and show relevant elements
  if (maritalStatus === "Yes - Add Husband") {
    // Display spouse section if adding husband
    showElement('spouse');

  } else if (maritalStatus === "Yes - Add Wife") {
    // Display spouse section if adding wife
    showElement('maiden_name_div');
    showElement('spouse');

  } else {
    // Hide spouse section if marital status is not "Yes"
    hideElement('spouse');
  }
};

// Add event listener to marital status dropdown to trigger showSpouse function
id('maritalStatus').addEventListener('change', showSpouse);


// GET ALL EMAILS 
// Call the fetchData function to initiate the request
// const emailData = []
const fName = id('fName').textContent
const famCode = localStorage.getItem('requesterFamCode')

fetchEmailData()
  .then(data => {
    // Do something with the fetched data
    const emailData = data;

    // SEND EMAIL TO THE KIDS AND processKidsSibling
    processKidsSiblings(emailData, fName, famCode)
  })
  .catch(error => {
    // Handle any errors that occurred during the request or processing
    console.error('Error:', error);
  });

// Call the getEmailData function somewhere in your code

