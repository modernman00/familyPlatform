import { id } from '../global'

export const showPassword = (inputId) => {
    const y = id(inputId);
    if (y.type === "password") {
        y.type = "text";
    } else {
        y.type = "password";
    }
}

/**
 * 
 * @param {* } email 
 * @returns 1 if there is an error
 */
 export const emailVal= (email)=> {
        const emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
        let error;
        let msg = `<li style=color:'red';> Please enter a valid email</li>`
        if (email.match(emailExp) === null) {
            id('email_error').innerHTML = msg
            id('email_error').style.color = "red"
            error = 1;
            return error
        }
    }