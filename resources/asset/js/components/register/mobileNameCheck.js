"use strict";
import { id, showError, showNotification } from "../global";
import { checkBox } from "../helper/general";
import { getAllData } from "../api/index";
import { autocomplete } from "../helper/autocomplete";
import axios from "axios";

const getData = getAllData();

let firstNameData = [];
let fatherName = [];
let mobile = [];
let motherName = [];
let checkEmail = [];

let fName = id("firstName_id").value;
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


    genId.innerHTML = mobile.includes(value) ?
        `Great news that your ${name} is already on the platform` :
        `<h4><i>Your ${name} is not on the platform. Do you want us to send ${sex} a text to register to the platform</i>?</h4>${checkBox(name)}`;


    function processRadio() {
        const postObj = {
            mobile: value,
            viewPath: "msg/contactNewMember",

            data: {
                email: id(`${name}Email_id`).value,
                mobile: id(`${name}Mobile_id`).value,
                name: id(`${name}Name_id`).value,
                yourName: `${fName} ${lName}`,
            },

            subject: `${fName} ${lName} recommended that you join your family network.`,
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

const checkEmailObj = {
    kidEmailInput: [
        "kid_email1",
        "kid_email2",
        "kid_email3",
        "kid_email4",
        "kid_email5",
        "kid_email6",
        "kid_email7",
        "kid_email8",
        "kid_email9",
        "kid_email10",
    ],
    kidNameInput: [
        "kid_name1",
        "kid_name2",
        "kid_name3",
        "kid_name4",
        "kid_name5",
        "kid_name6",
        "kid_name7",
        "kid_name8",
        "kid_name9",
        "kid_name10",
    ],
    siblingEmail: [
        "sibling_email1",
        "sibling_email2",
        "sibling_email3",
        "sibling_email4",
        "sibling_email5",
        "sibling_email6",
        "sibling_email7",
        "sibling_email8",
        "sibling_email9",
        "sibling_email10",
    ],
    siblingName: [
        "sibling_name1",
        "sibling_name2",
        "sibling_name3",
        "sibling_name4",
        "sibling_name5",
        "sibling_name6",
        "sibling_name7",
        "sibling_name8",
        "sibling_name9",
        "sibling_name10",
    ],
};

// check if there is a sibling or kids by email

document.onkeydown = (e) => {
    //. use the onclick to get the id of the element that was clicked
    // 2. use event listener to get the email value (if it is not empty)
    // 3. use the email value to check if it is in the array
    // 4. if it is in the array, show the Yes or No Radio
    // 5. click yes to send email to the kid or sibling

    try {
        // create an object with the data to check
        const elementId = e.target.id; // id of the element that was clicked or press down
        const emailInput = e.target.value;

        // this phase checks the id of what is being typed
        if (!elementId) throw new Error("target id is null and empty");

        let chooseEmail = [];
        let chooseName = [];
        let helpHTML = "";
        let errorHTML = ""; // Show error if applicant's email is registered

        // check if id / event.id is either kid or sibling

        // if the elementId indicate that it is a kid, then choosemail inherits all the kids array from the checkEmailObj and vis a versa

        if (checkEmailObj.kidEmailInput.includes(elementId)) {
            chooseEmail = checkEmailObj.kidEmailInput;
            chooseName = checkEmailObj.kidNameInput;
            helpHTML = id(`${elementId}_help`);
        } else if (checkEmailObj.siblingEmail.includes(elementId)) {
            chooseEmail = checkEmailObj.siblingEmail;
            chooseName = checkEmailObj.siblingName;
            helpHTML = id(`${elementId}_help`);
        }

        const checkFamilyEmail = (event) => {
            //this checks the value of what is being typed

            const emailInput = event.target.value;

            helpHTML.innerHTML = (emailInput.length > 5 && emailInput.length < 7) ? "Email may be too small" : "";


            // use the elementid to find the exact email value and name value
            const index = chooseEmail.indexOf(elementId);
            const email = id(chooseEmail[index]);
            const emailValue = email.value;
            const name = id(chooseName[index]);
            const nameValue = name.value;

            // if (!emailValue)
            //     throw new Error("another round of email is empty");
            // if (!nameValue) throw new Error("name is empty");
            // if (!getData.length) throw new Error("data is faulty");



            // checking family email 
            helpHTML.style.display = "block";
            helpHTML.innerHTML = checkEmail.includes(emailInput) ?
                `Great news! ${nameValue} is already registered on the platform` :
                `${nameValue} is not on the platform.Do you want us to send ${nameValue} a email to register to the platform ? ${checkBox(elementId)}`;



            // send the email to family membersa

            const processKidRadio = (ev) => {
                const postObj = {
                    mobile: "", // is this needed?
                    viewPath: "msg/contactNewMember",

                    data: {
                        email: emailValue,
                        name: nameValue,
                        yourName: `${fName} ${lName}`,
                    },

                    subject: `${fName} ${lName} recommended that you join your family network.`,
                };

                axios
                    .post("/register/contactNewMember", postObj)
                    .then((response) => {
                        helpHTML.innerHTML = response.data.message;

                        setTimeout(() => {
                            helpHTML.style.display = "none";
                        }, 5000);
                    })
                    .catch((error) => {
                        showError(error);
                    });
            };

            id(`${elementId}Yes`).addEventListener("click", processKidRadio);

            id(`${ elementId }No`).addEventListener("click", () => (id(`${ elementId }No`).style.display = "none"));


        };

        if (chooseEmail.includes(elementId)) {

            id(elementId).addEventListener("keyup", checkFamilyEmail);
        }
    } catch (error) {
        showError(error);
    }
};

const checkPersonalEmail = (e) => {
    const email = e.target.value;;
    id("email_error").innerHTML = checkEmail.includes(email) ? `Hello! ${fName} you are already registered on the platform` : ``;



}


id('email_id').addEventListener('keyup', checkPersonalEmail)