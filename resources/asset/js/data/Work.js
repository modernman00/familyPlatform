import { type } from "jquery";

 export const Work = [
      {
        label : 'Job title?',
        attribute: 'jobTitle',
        placeholder: 'What is your job title',
        type: 'text',
      },
      {
        label : 'Occupation?',
        attribute: 'occupation',
        placeholder: 'Accountant, Housewife, Business man etc',
        type: 'text',
      },
       {
        label : "Employer's name?",
        attribute: 'employerName',
        placeholder: 'Guinness, Showal, Coca Cola',
        type: 'text',
      },
      {
        label : "Work email",
        attribute: 'workEmail',
        placeholder: 'enter your work email',
        type: 'text',
      }
      {
        label : "Years with employer",
        attribute: 'employers',
        placeholder: null,
        type : 'select',
        options: [0, 1, 2, 3, 4, 5, 6]
      },
]
