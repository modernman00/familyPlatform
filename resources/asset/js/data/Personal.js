"use strict";
export const Personal = [
  {
    form: '3-col',
    label: 'Personal Information:',
    options: {
      label: ['First Name', 'Surname', 'Alias'],
      attribute: ['firstName', 'lastName', 'alias'],
      placeholder: ['Wale', 'Olaogun', 'Modernman'],
      type: ['text', 'text', 'text']
    }
  },
  {
    label: "Are you married?",
    attribute: 'maritalStatus',
    placeholder: null,
    type: 'radio',
    options: ["Yes", "No"],
    inputType: 'RADIO'
  },
  {
    form: '2-col',
    unique: 'spouse',
    label: "Spouse's Details",
    options: {
      label: ["Spouse's name", "Spouse's mobile"],
      attribute: ['spouseName', 'spouseMobile'],
      placeholder: ['Leave empty if you have no wife/husband', '23480364168089 or leave empty'],
      type: ['text', 'number']
    }
  },
  {
    form: '2-col',
    label: "Father's Details",
    options: {
      label: ["Father's name", "Father's mobile"],
      attribute: ['fatherName', 'fatherMobile'],
      placeholder: ['Toyin Olaogun', '23480364168089'],
      type: ['text', 'number']
    }
  },
  {
    form: '3-col',
    label: "Mother's Details",
    options: {
      label: ["Mother's name", "Mother's mobile", "Mother's maiden name"],
      attribute: ['motherName', 'motherMobile', 'motherMaiden'],
      placeholder: ['Toyin', '23480364168089', "surname before marriage"],
      type: ['text', 'number', 'text']
    }
  },
  {
    label: "Number of children",
    attribute: 'kids',
    placeholder: null,
    type: 'select',
    options: [0, 1, 2, 3, 4, 5, 6],
    inputType: 'SELECT'
  },
  {
    label: "Gender",
    attribute: 'gender',
    placeholder: null,
    type: 'select',
    options: ['Male', 'Female'],
    inputType: 'SELECT'
  },
  {
    label: "Number of siblings (Brothers/Sisters)",
    attribute: 'noSiblings',
    placeholder: null,
    type: 'select',
    options: [0, 1, 2, 3, 4, 5, 6],
    inputType: 'SELECT'
  },
  {
    label: "Please, upload your picture",
    attribute: 'profileImage',
    placeholder: null,
    type: 'file',
    inputType: 'FILE'

  },
  {
    form: '3-col',
    label: 'Date of Birth:',
    options: {
      label: ['Day', 'Month', 'Year'],
      attribute: ['day', 'month', 'year'],
      placeholder: ['15', 'July', '1982'],
      type: ['number', 'text', 'number']
    }
  }
  // {
  //   form: '3_col_select',
  //   label: 'Other details:',
  //   options: {
  //     label: ['Gender', 'Siblings', 'Children'],
  //     attribute: ['gender', 'noSiblings', 'kids'],
  //     placeholder: ['15', 'July', '1982'],
  //     selectOption1: ['male', 'female'],
  //     selectOption2: [1, 2, 3, 4, 5, 6],
  //     selectOption3: [1, 2, 3, 4, 5, 6],
  //   }
  // }


]

