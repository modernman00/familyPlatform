"use strict";
export const Contact = [

   {
      form : 'Contact Information',
      label: 'Home Address',
      attribute: 'address',
      placeholder: 'Please, enter your first line of address',
      type: 'text',
      inputType: 'NORMAL_INPUT'
   },

   {
      form: '2-col',
      unique: 'region',
      label: "Area Code and Region",
      options: {
         label: ["Postcode /zip code/area code", "Region / State / District"],
         attribute: ['postcode', 'region'],
         placeholder: ['SN2 3BF / 234', 'London / Lagos state / New York'],
         type: ['text', 'text']
      }
   },
    {
      form: '2-col',
      unique: "contact",
      label: "How to reach you",
      options: {
         label: ["Email", "Mobile Number"],
         attribute: ['email', 'mobile'],
         placeholder: ['toyin@yahoo.com', 'include the area code - 234 or 1 or 44'],
         type: ['email', 'number']
      }
   },
   {
      label: 'Country',
      attribute: 'country',
      type: 'select',
      options: ['Nigeria', 'UK', 'Canada', 'Europe', 'USA', 'China', 'Asia', 'Latin America'],
      inputType: 'SELECT'
   },
]

