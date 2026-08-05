"use strict";
import { id, showError, showNotification, checkBox } from "@modernman00/shared-js-lib";
import axios from "axios";
import { processKidsSiblings } from "../kidsAndSiblings"
import { log } from "@modernman00/shared-js-lib";

let fName = id("firstName").value 
let lName = id("lastName").value;

const checkContactExists = async (mobileValue, emailValue) => {
    try {
        const payload = {};
        if (mobileValue) payload.mobile = mobileValue;
        if (emailValue) payload.email = emailValue;
        
        const response = await axios.post('/register/checkContact', payload);
        return response?.data?.exists || false;
    } catch (error) {
        showError(error);
        return false;
    }
};

const setInput = async (name, value) => {
    const sex = name === "father" ? "him" : "her";
    const genId = id(`${name}Mobile_error`);
    genId.style.display = "block";

    const exists = await checkContactExists(value, null);

    genId.innerHTML = exists ?
        `Great news that your ${name} is already on the platform` :
        `<h4><i>Your ${name} is not on the platform. Do you want us to send ${sex} a text/email to register to the platform</i>?</h4>${checkBox(name)}`;

    if (!exists) {
        function processRadio() {
            const postObj = {
                mobile: value,
                viewPath: "msg/contactNewMember",

                data: {
                    email: id(`${name}_email`)?.value || "",
                    mobile: id(`${name}_mobile`)?.value || "",
                    name: id(`${name}_name`)?.value || "",
                    familyCode: id(`famCode`)?.value || id('familyCode')?.value || "",
                    yourName: `${fName} ${lName}`,
                },

                subject: `${fName} ${lName} Wants You: Experience the Magic of your Family Network Today!`,
            };

            axios
                .post("/register/contactNewMember", postObj)
                .then((response) => {
                    showNotification(`${name}Mobile_help`, 'is-success', response?.data?.message || "Success");
                    genId.innerHTML = "";
                })
                .catch((error) => {
                    showNotification(`${name}Mobile_error`, 'is-danger', error?.message || "An error occurred");
                    showError(error);
                });
        }
        
        // Wait for DOM update
        setTimeout(() => {
            id(`${name}Yes`)?.addEventListener("click", processRadio);
            id(`${name}No`)?.addEventListener(
                "click",
                () => (genId.style.display = "none")
            );
        }, 100);
    }
};

const mobileFilter = async (event, name) => {
    try {
        const value = event.target.value;

        if (!value) {
            return;
        }

        if (value.length >= 11) {
            await setInput(name, value);
        }
    } catch (error) {
        showError(error);
    }
};

const fatherMobile = (event) => {
    const setName = "father";
    mobileFilter(event, setName);
};

const motherMobile = (event) => {
    const setName = "mother";
    mobileFilter(event, setName);
};

const spouseMobile = (event) => {
    const setName = "spouse";
    mobileFilter(event, setName);
};

// Add event listeners with error handling
id("father_mobile")?.addEventListener("keyup", (event) => {
    try {
        fatherMobile(event);
    } catch (error) {
        showError(error)
    }
});

id("mother_mobile")?.addEventListener("keyup", (event) => {
    try {
        motherMobile(event);
    } catch (error) {
        showError(error)
    }
});

id("spouse_mobile")?.addEventListener("keyup", (event) => {
    try {
        spouseMobile(event);
    } catch (error) {
        showError(error)
    }
});


// processKidsSiblings needs array of emails, but we removed eager fetch
// We will just pass an empty array to it for now.
processKidsSiblings([], fName);

// Use a simple debounce for email check
let emailTimeout;
const checkPersonalEmail = (e) => {
    clearTimeout(emailTimeout);
    emailTimeout = setTimeout(async () => {
        const email = e.target.value;
        if (!email) return;
        const exists = await checkContactExists(null, email);
        if (id("email_error")) {
            id("email_error").innerHTML = exists ? `YOU HAVE ALREADY REGISTERED ON THE PLATFORM` : ``;
        }
    }, 500);
}

id('email')?.addEventListener('keyup', checkPersonalEmail);