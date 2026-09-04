"use strict";
export const dataToCheckRegister = {
    maxLength: {
        id: [
            'firstName', 'lastName', 'country', 'mobile', 'email',
        ],
        max: [15, 15, 30, 16, 45],
    },
    password: {
        pwd: 'password',
        pwd2: 'confirm_password'

    },
    familyCheck: {
        father: ["fatherYes", "fatherNo"],
        mother: ["motherYes", "motherNo"],
        spouse: ["spouseYes", "spouseNo"]
    }
};

export const Login = {

    maxLength: {
        id: ['email', 'password'],
        max: [35, 35]
    },

};