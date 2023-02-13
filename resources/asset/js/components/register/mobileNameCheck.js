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
 * @param {*} baseArray the array to check against ["Banana", "Orange", "Apple", "Mango"];
 * @param {*} searchElement  the element to search against ("Mango")
 */

const checkExistence = (baseArray, searchElement) => {
    if (baseArray.includes(searchElement) === false) {
        baseArray.push(searchElement);
    }
};

getData.then((el) =>
    el.map((element) => {
        checkExistence(firstNameData, element.firstName);
        checkExistence(fatherName, element.fatherName);
        checkExistence(motherName, element.motherName);
        checkExistence(mobile, element.mobile);
        checkExistence(checkEmail, element.email);
    }),
);

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
                // id(`${name}Mobile_error`).innerHTML = error.message;
                showError(error);
            });
    }
    id(`${name}Yes`).addEventListener("click", processRadio);
    id(`${name}No`).addEventListener(
        "click",
        () => (genId.style.display = "none"),
    );
};

/**
 * @param {the idInput to check} the input id
 * @param {the array to check} data
 * @param {this should either be the mother oor father} who
 */

const mobileFilter = (event, name) => {
    const value = event.target.value;

    if (value === "" || value === undefined || value === null)
        throw new Error("Number value is empty");

    if (value.length >= 11) {
        // mobile value is 10 digits
        // return mobile.find(el => {
        return setInput(name, value);
        // })
    }
};
const fatherMobile = () => {
    const setName = "father";
    mobileFilter(event, setName);
};

const motherMobile = () => {
    const setName = "mother";
    mobileFilter(event, setName);
};

const spouseMobile = () => {
    const setName = "spouse";
    mobileFilter(event, setName);
};

id("fatherMobile_id").addEventListener("keyup", fatherMobile);
id("motherMobile_id").addEventListener("keyup", motherMobile);
id("spouseMobile_id").addEventListener("keyup", spouseMobile);

// create the data for the function below

const checkEmailObj = {
    emailInput: [
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
    nameInput: [
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

        if (elementId === null) throw new Error("target id is null and empty");



        let chooseEmail = [];
        let chooseName = [];
        let helpHTML = "";
        let checkEmail = "";

        // check if id / event.id is either kid or sibling

        if (checkEmailObj.emailInput.includes(elementId)) {
            chooseEmail = checkEmailObj.emailInput;
            chooseName = checkEmailObj.nameInput;
            helpHTML = id(`${elementId}_help`);
        } else if (checkEmailObj.siblingEmail.includes(elementId)) {
            chooseEmail = checkEmailObj.siblingEmail;
            chooseName = checkEmailObj.siblingName;
            helpHTML = id(`${elementId}_help`);
        } else if (elementId == "email_id") {
            checkEmail = id(elementId)
            checkEmail.addEventListener('keyup', checkEmailFn)
        }

        const checkFamilyEmail = (event) => {
            const emailInput = event.target.value;

            if (emailInput === null || emailInput === "")
                throw new Error("email input is empty");

            if (emailInput.length < 6) throw new Error("email input is SHORT");

            if (chooseEmail === null || chooseEmail == "")
                throw new Error("choose email is empty");

            // if (chooseEmail) {
            const index = chooseEmail.indexOf(elementId);
            const email = id(chooseEmail[index]);
            const emailValue = email.value;
            const name = id(chooseName[index]);
            const nameValue = name.value;

            if (emailValue === null || email == "")
                throw new Error("another round of email is empty");
            if (nameValue === "") throw new Error("name is empty");
            if (getData.length === 0) throw new Error("data is faulty");

            // if (emailInput.length > 6) {

            getData.then((el) =>
                el.map((element) => {
                    checkExistence(checkEmail, element.email);
                }),
            );

            helpHTML.style.display = "block";
            helpHTML.innerHTML = checkEmail.includes(emailInput) ?
                `Great news!${nameValue} is already on the platform` :
                `${nameValue} is not on the platform.Do you want us to send ${nameValue} a email to register to the platform ? ${checkBox(elementId)}`;

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

            // }
            // }
        };

        if (chooseEmail.includes(elementId)) {
            // console.log(elementId);
            id(elementId).addEventListener("keyup", checkFamilyEmail);
        }
    } catch (error) {
        showError(error.message);
    }
};