"use strict";
export const Personal = [{
        form: '3-col',
        label: 'Personal Information:',
        unique: "personal",
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
        type: 'select',
        options: ["Yes", "No"],
        inputType: 'SELECT'
    },
    {
        form: '2-col',
        unique: 'spouse',
        label: "Spouse's Details",
        options: {
            label: ["Spouse's name", "Spouse's mobile", "Spouse Email"],
            attribute: ['spouseName', 'spouseMobile', 'spouseEmail'],
            placeholder: ["wife/husband's fullname", '23480364168089', 'spouse@gmail.com'],
            type: ['text', 'number', 'email']
        }
    },
    {
        form: '2-col',
        unique: 'father',
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
        unique: "mother",
        options: {
            label: ["Mother's name", "Mother's mobile"],
            attribute: ['motherName', 'motherMobile'],
            placeholder: ['Toyin', '23480364168089'],
            type: ['text', 'number']
        }
    },
    {
        label: "Number of children",
        attribute: 'kids',
        type: 'select',
        options: [0, 1, 2, 3, 4, 5, 6],
        inputType: 'SELECT'
    },
    {
        label: "Gender",
        attribute: 'gender',
        type: 'select',
        options: ['Male', 'Female'],
        inputType: 'SELECT'
    },
    {
        label: "Number of siblings (Brothers/Sisters)",
        attribute: 'noSiblings',
        type: 'select',
        options: [0, 1, 2, 3, 4, 5, 6],
        inputType: 'SELECT'
    },
    {
        label: "Please, upload your picture",
        attribute: 'profileImage',
        type: 'file',
        inputType: 'FILE'

    },
    {
        form: '3-col',
        label: 'Date of Birth:',
        unique: "birth",
        options: {
            label: ['Day', 'Month', 'Year'],
            attribute: ['day', 'month', 'year'],
            placeholder: ['15', 'July', '1982'],
            type: ['number', 'text', 'number']
        }
    }
]