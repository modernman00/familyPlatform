import { id, hideElement, showElement } from '@modernman00/shared-js-lib';

const showMaidenName = () => {
  const gender = id('gender');
  const maritalStatus = id('maritalStatus');
  
  if (gender && maritalStatus && gender.value === "Female" && maritalStatus.value === "Yes") {
    showElement('maidenName_div');
  } else {
    hideElement('maidenName_div');
  }
};



export const showSpouse = () => {

  const maritalStatus = id('maritalStatus');
  
  if (maritalStatus && maritalStatus.value === "Married") {
    showElement('spouse');
  } else {
    hideElement('spouse');
  }
};


// Add event listeners
const maritalStatusEl = id('maritalStatus');
if (maritalStatusEl) {
    maritalStatusEl.addEventListener('change', showSpouse);
}

const genderEl = id('gender');
if (genderEl) {
    genderEl.addEventListener('change', showMaidenName);
}

// Hide elements by default
hideElement('spouse');
hideElement('maidenName_div');

// Other initializations
const registerNotifyDiv = id('register_notify_div');
if (registerNotifyDiv) registerNotifyDiv.style.display = "none";

// inject student after employment status value is student 

const injectStudent = () => {
  const empStatus = id('employmentStatus');
  if (empStatus) {
    empStatus.addEventListener('change', (e) => {
      const value = e.target.value;
      if(value === "Student") {
          const occ = id('occupation');
          if (occ) occ.value = 'Student';
      }
      
    });
  }
};

injectStudent();


