import { id } from '../global';

export const hideElement = (elementId) => {
  id(elementId).style.display = "none";
};

const showElement = (elementId) => {
  id(elementId).style.display = "block";
};


const showMaidenName = () => {
  const gender = id('gender_id');
  const maritalStatus = id('maritalStatus_id');
  
  if (gender.value === "Female" && maritalStatus.value === "Yes") {
    showElement('maidenName_div');
  } else {
    hideElement('maidenName_div');
  }
};

export const showSpouse = () => {

  const maritalStatus = id('maritalStatus_id').value;
  
  if (maritalStatus === "Married") {
    showElement('spouse');
  } else {
    hideElement('spouse');
  }
};


// Add event listeners



id('maritalStatus_id').addEventListener('change', showSpouse);
id('gender_id').addEventListener('change', showMaidenName);

// Hide elements by default
hideElement('spouse');
hideElement('maidenName_div');

// Other initializations
id('register_notify_div').style.display = "none";

// inject student after employment status value is student 

const injectStudent = () => {
  id('employmentStatus_id').addEventListener('change', (e) => {
    const value = e.target.value;
    if(value === "Student") {
        id('occupation_id').value = 'Student';
    }
    
  });
};

injectStudent();

