"use strict";
import { id, showError, showNotification } from "../global";
import { checkBox } from "../helper/general";
import { getAllData } from "../api/index";
import { autocomplete } from "../helper/autocomplete";
import axios from "axios";
import { processKidsSiblings } from "../kidsAndSiblings"


const getData = getAllData();

let firstNameData = [];
let fatherName = [];
let mobile = [];
let motherName = [];
let checkEmail = [];

let fName = id("firstName_id").value 
let lName = id("lastName_id").value;

/**
 *
 * @param {* array} baseArray the array to check against ["Banana", "Orange", "Apple", "Mango"];
 * @param {* string || integer} searchElement  the element to search against ("Mango")
 */

const checkExistence = (baseArray, searchElement) => {
    if (!Array.isArray(baseArray)) {
        baseArray = []; // Initialize as an array if it's not already
    }
    if (baseArray.includes(searchElement) === false) {
        baseArray.push(searchElement);
    }
};
// Check if getData is resolved
if (getData instanceof Promise) {
    getData
        .then((el) => {
            el.forEach((element) => {
                checkExistence(firstNameData, element.firstName);
                checkExistence(fatherName, element.fatherName);
                checkExistence(motherName, element.motherName);
                checkExistence(mobile, element.mobile);
                checkExistence(checkEmail, element.email);
            });

            // Code that relies on the data obtained from getAllData() can be placed here
        })
        .catch((error) => {
            showError(error);
            // Handle the error appropriately
        });
} else {
    console.log('getData is empty or not resolved');
    // Handle the case when getData is empty or not resolved
}

const firstAutoComplete = id("firstName_id");
const fatherAutoComplete = id("fatherName_id");
const motherAutoComplete = id("motherName_id");

firstAutoComplete.setAttribute("autocomplete", "off");
fatherAutoComplete.setAttribute("autocomplete", "off");
motherAutoComplete.setAttribute("autocomplete", "off");

// AUTOCOMPLETE
autocomplete(firstAutoComplete, firstNameData);
autocomplete(fatherAutoComplete, fatherName);
autocomplete(motherAutoComplete, motherName);

// CHECK THE MOBILE OF MOTHER AND FATHER

const setInput = (name, value) => {
    const sex = name === "father" ? "him" : "her";
    const genId = id(`${name}Mobile_error`);
    genId.style.display = "block";


    genId.innerHTML = mobile.includes(value) || email.includes(value) ?
        `Great news that your ${name} is already on the platform` :
        `<h4><i>Your ${name} is not on the platform. Do you want us to send ${sex} a text/email to register to the platform</i>?</h4>${checkBox(name)}`;


    function processRadio() {
        const postObj = {
            mobile: value,
            viewPath: "msg/contactNewMember",

            data: {
                email: id(`${name}Email_id`).value,
                mobile: id(`${name}Mobile_id`).value,
                name: id(`${name}Name_id`).value,
                familyCode: id(`familyCode_id`).value,
                yourName: `${fName} ${lName}`,
            },

            subject: `${fName} ${lName} Wants You: Experience the Magic of your Family Network Today!`,
        };

        axios
            .post("/register/contactNewMember", postObj)
            .then((response) => {

                showNotification(`${name}Mobile_help`, 'is-success', response.data.message);

                genId.innerHTML = "";

            })
            .catch((error) => {
                showNotification(`${name}Mobile_error`, 'is-danger', error.message);
                showError(error);
            });
    }
    id(`${name}Yes`).addEventListener("click", processRadio);
    id(`${name}No`).addEventListener(
        "click",
        () => (genId.style.display = "none"),
    );
};

// *
// Handle mobile filtering
// for different individuals.*@param { Object }
// event - The event object.*@param { string }
// name - The name of the individual("father", "mother", "spouse").*/

const mobileFilter = (event, name) => {
    try {
        const value = event.target.value;

        if (!value) {
            throw new Error("Number value is empty");
        }

        if (value.length >= 11) {
            setInput(name, value);
        }
    } catch (error) {
        showError(error);
        // Perform additional error handling or logging if needed
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
id("fatherMobile_id").addEventListener("keyup", (event) => {
    try {
        fatherMobile(event);
    } catch (error) {
        showError(error)
    }
});

id("motherMobile_id").addEventListener("keyup", (event) => {
    try {
        motherMobile(event);
    } catch (error) {
        showError(error)
    }
});

id("spouseMobile_id").addEventListener("keyup", (event) => {
    try {
        spouseMobile(event);
    } catch (error) {
        showError(error)
    }
});

// create the data for the function below


// check if there is a sibling or kids by email
processKidsSiblings(checkEmail, fName, lName)


const checkPersonalEmail = (e) => {
    const email = e.target.value;
    id("email_error").innerHTML = checkEmail.includes(email) ? `YOU HAVE ALREADY REGISTERED ON THE PLATFORM` : ``;
}


id('email_id').addEventListener('keyup', checkPersonalEmail)