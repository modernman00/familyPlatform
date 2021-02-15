"use strict";
export const Account = [

    {
    form: '3-col',
    label: 'Create an account:',
    unique: 'createAccount',
    options: {
      label: ['Password', 'Confirm password', 'Secret word'],
      attribute: ['password', 'confirm_password', 'secretWord'],
      placeholder: ['xxxx', 'xxxx', 'one time security code'],
      type: ['password', 'password', 'password']
    }
  }
]
