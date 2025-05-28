"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["shared-lib"],{

/***/ "./node_modules/@modernman00/shared-js-lib/Cookie.js":
/*!***********************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/Cookie.js ***!
  \***********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkCookie: () => (/* binding */ checkCookie),
/* harmony export */   getCookie: () => (/* binding */ getCookie),
/* harmony export */   setCookie: () => (/* binding */ setCookie)
/* harmony export */ });
/**
 * Sets a cookie with the given name, value, and number of days until it expires.
 * By default, the cookie will expire in 365 days.
 * @param {string} cname - The name of the cookie.
 * @param {string} cvalue - The value of the cookie.
 * @param {number} [exdays=365] - The number of days until the cookie expires.
 */
const setCookie = (cname, cvalue, exdays = 365) => {
    const d = new Date(Date.now() + exdays * 864e5);
    document.cookie = `${cname}=${cvalue}; expires=${d.toUTCString()}; path=/`;
}

/**
 * Retrieves the value of a cookie by name.
 * @param {string} cname - The name of the cookie to retrieve.
 * @returns {string} The value of the cookie, or an empty string if the cookie does not exist.
 */
const getCookie = (cname) => {
    const name = cname + "=";
    const cookies = document.cookie.split(';').map(cookie => cookie.trim());
    for (const cookie of cookies) {
        if (cookie.startsWith(name)) {
            return cookie.substring(name.length);
        }
    }
    return "";
}



/**
 * Checks if a cookie with the given name exists. If not, prompts the user
 * for a name and sets the cookie for 365 days. If the cookie does exist,
 * alerts the user with a welcome message.
 * @param {string} name - The name of the cookie to check.
 */
const checkCookie = (name) => {
    let user = getCookie(name);
    if (!user) {
        const askName = prompt("Please enter your name:", "");
        if (askName) {
            setCookie(name, askName, 365);
            user = askName;
        }
    }
    if (user) {
        alert(`Welcome again ${user}`);
    }
}


/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/CountryCode.js":
/*!****************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/CountryCode.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   injectCountryCode: () => (/* binding */ injectCountryCode)
/* harmony export */ });
Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());


// inject the country code once one of the country is picked

// Object to map countries to country codes

const countryCodes = {
  Afghanistan: "93",
  Albania: "355",
  Algeria: "213",
  Andorra: "376",
  Angola: "244",
  "Antigua and Barbuda": "1-268",
  Argentina: "54",
  Armenia: "374",
  Australia: "61",
  Austria: "43",
  Azerbaijan: "994",
  Bahamas: "1-242",
  Bahrain: "973",
  Bangladesh: "880",
  Barbados: "1-246",
  Belarus: "375",
  Belgium: "32",
  Belize: "501",
  Benin: "229",
  Bhutan: "975",
  Bolivia: "591",
  "Bosnia and Herzegovina": "387",
  Botswana: "267",
  Brazil: "55",
  Brunei: "673",
  Bulgaria: "359",
  "Burkina Faso": "226",
  Burundi: "257",
  Cambodia: "855",
  Cameroon: "237",
  Canada: "1",
  "Cape Verde": "238",
  "Central African Republic": "236",
  Chad: "235",
  Chile: "56",
  China: "86",
  Colombia: "57",
  Comoros: "269",
  "Congo (Brazzaville)": "242",
  "Congo (Kinshasa)": "243",
  "Costa Rica": "506",
  "Côte d'Ivoire": "225",
  Croatia: "385",
  Cuba: "53",
  Cyprus: "357",
  "Czech Republic": "420",
  Denmark: "45",
  Djibouti: "253",
  Dominica: "1-767",
  "Dominican Republic": "1-809, 1-829, 1-849",
  "East Timor": "670",
  Ecuador: "593",
  Egypt: "20",
  "El Salvador": "503",
  "Equatorial Guinea": "240",
  Eritrea: "291",
  Estonia: "372",
  Ethiopia: "251",
  Fiji: "679",
  Finland: "358",
  France: "33",
  Gabon: "241",
  Gambia: "220",
  Georgia: "995",
  Germany: "49",
  Ghana: "233",
  Greece: "30",
  Grenada: "1-473",
  Guatemala: "502",
  Guinea: "224",
  "Guinea-Bissau": "245",
  Guyana: "592",
  Haiti: "509",
  Honduras: "504",
  Hungary: "36",
  Iceland: "354",
  India: "91",
  Indonesia: "62",
  Iran: "98",
  Iraq: "964",
  Ireland: "353",
  Israel: "972",
  Italy: "39",
  Jamaica: "1-876",
  Japan: "81",
  Jordan: "962",
  Kazakhstan: "7",
  Kenya: "254",
  Kiribati: "686",
  "North Korea": "850",
  "South Korea": "82",
  Kosovo: "383",
  Kuwait: "965",
  Kyrgyzstan: "996",
  Laos: "856",
  Latvia: "371",
  Lebanon: "961",
  Lesotho: "266",
  Liberia: "231",
  Libya: "218",
  Liechtenstein: "423",
  Lithuania: "370",
  Luxembourg: "352",
  Macedonia: "389",
  Madagascar: "261",
  Malawi: "265",
  Malaysia: "60",
  Maldives: "960",
  Mali: "223",
  Malta: "356",
  "Marshall Islands": "692",
  Mauritania: "222",
  Mauritius: "230",
  Mexico: "52",
  Micronesia: "691",
  Moldova: "373",
  Monaco: "377",
  Mongolia: "976",
  Montenegro: "382",
  Morocco: "212",
  Mozambique: "258",
  Myanmar: "95",
  Namibia: "264",
  Nauru: "674",
  Nepal: "977",
  Netherlands: "31",
  "New Zealand": "64",
  Nicaragua: "505",
  Niger: "227",
  Nigeria: "234",
  Norway: "47",
  Oman: "968",
  Pakistan: "92",
  Palau: "680",
  Panama: "507",
  "Papua New Guinea": "675",
  Paraguay: "595",
  Peru: "51",
  Philippines: "63",
  Poland: "48",
  Portugal: "351",
  Qatar: "974",
  Romania: "40",
  Russia: "7",
  Rwanda: "250",
  "Saint Kitts and Nevis": "1-869",
  "Saint Lucia": "1-758",
  "Saint Vincent and the Grenadines": "1-784",
  Samoa: "685",
  "San Marino": "378",
  "Sao Tome and Principe": "239",
  "Saudi Arabia": "966",
  Senegal: "221",
  Serbia: "381",
  Seychelles: "248",
  "Sierra Leone": "232",
  Singapore: "65",
  Slovakia: "421",
  Slovenia: "386",
  "Solomon Islands": "677",
  Somalia: "252",
  "South Africa": "27",
  "South Sudan": "211",
  Spain: "34",
  "Sri Lanka": "94",
  Sudan: "249",
  Suriname: "597",
  Swaziland: "268",
  Sweden: "46",
  Switzerland: "41",
  Syria: "963",
  Taiwan: "886",
  Tajikistan: "992",
  Tanzania: "255",
  Thailand: "66",
  Togo: "228",
  Tonga: "676",
  "Trinidad and Tobago": "1-868",
  Tunisia: "216",
  Turkey: "90",
  Turkmenistan: "993",
  Tuvalu: "688",
  Uganda: "256",
  Ukraine: "380",
  "United Arab Emirates": "971",
  "United Kingdom": "44",
  "United States": "1",
  Uruguay: "598",
  Uzbekistan: "998",
  Vanuatu: "678",
  "Vatican City": "379",
  Venezuela: "58",
  Vietnam: "84",
  Yemen: "967",
  Zambia: "260",
  Zimbabwe: "263"
}


const injectCountryCode = async (countryId, mobileId) => {
  Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(countryId).addEventListener('change', async (e) => {
    const value = e.target.value;
    Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(mobileId).value = await countryCodes[value] || '';
  });
};

injectCountryCode();

/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/DateTime.js":
/*!*************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/DateTime.js ***!
  \*************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   calTiming: () => (/* binding */ calTiming),
/* harmony export */   date2String: () => (/* binding */ date2String),
/* harmony export */   timeAgo: () => (/* binding */ timeAgo),
/* harmony export */   updateTimeRealTime: () => (/* binding */ updateTimeRealTime)
/* harmony export */ });
/**
 * Returns a string representing the time elapsed since the given date.
 * The string is in the format "[number] [unit]s", where [number] is the number of the unit,
 * and [unit] is the name of the unit. The unit is the largest unit that is at least one,
 * for example "3 days" or "1 year". If the time elapsed is less than one second, returns "0 seconds".
 * @param {Date} date - The date to calculate the time elapsed since.
 * @returns {string} - The time elapsed since the given date.
 */
const calTiming = (date) => {
    const seconds = Math.floor((new Date() - date) / 1000);
    const epochs = [
        { name: "year", seconds: 31536000 },
        { name: "month", seconds: 2592000 },
        { name: "day", seconds: 86400 },
        { name: "hour", seconds: 3600 },
        { name: "minute", seconds: 60 },
        { name: "second", seconds: 1 }
    ];
    
    for (const epoch of epochs) {
        const interval = Math.floor(seconds / epoch.seconds);
        if (interval > 1) {
            return `${interval} ${epoch.name}s`;
        } else if (interval === 1) {
            return `1 ${epoch.name}`;
        }
    }
};
const aDay = 24 * 60 * 60 * 1000;


let element = document.querySelectorAll('time[data-time]')

/**
 * Updates all elements with a 'data-time' attribute with the time that has
 * elapsed since the given time, in real time.
 *
 * The function works by looping through all elements with the 'data-time'
 * attribute and calculating the time elapsed since the given time. It then
 * updates the content of the element with the time that has elapsed.
 *
 * The function then waits 60 seconds before calling itself again.
 *
 * @returns {void}
 */
const updateTimeRealTime = () => {
    for (const entry of element) {
        const seconds = (Date.now() - new Date(entry.dataset.time).getTime()) / 1000;
        entry.textContent = getDuration(seconds);
    }
    setTimeout(updateTimeRealTime, 1000 * 60);
};
setTimeout(updateTimeRealTime, 1000 * 60);


const epochs = [
    ['year', 31536000],
    ['month', 2592000],
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
    ['second', 1]
];

/**
 * Takes a time in seconds and returns an object with the largest unit of time
 * that is at least one, and the number of those units.
 *
 * @param {number} timeAgoInSeconds - The time in seconds to calculate the duration of.
 * @returns {Object} - An object with an interval and epoch, where interval is the number of those units,
 *                    and epoch is the name of the unit.
 */
const getDuration = (timeAgoInSeconds) => {
    for (let [name, seconds] of epochs) {
        const interval = Math.floor(timeAgoInSeconds / seconds);
        if (interval >= 1) {
            return {
                interval: interval,
                epoch: name
            };
        }
    }
};

/**
 * Returns a string representing the time elapsed since the given date.
 * The time elapsed is presented as a string in the form of "X epochs ago",
 * where X is the number of epochs, and epochs is the largest unit of time that
 * is at least one, e.g. "1 hour ago", "2 days ago", etc.
 *
 * @param {Date|string} date - The date from which to calculate the time elapsed.
 * @returns {string} - A string representing the time elapsed since the given date.
 */
const timeAgo = (date) => {
    const timeAgoInSeconds = Math.floor((new Date() - new Date(date)) / 1000);
    const { interval, epoch } = getDuration(timeAgoInSeconds);
    const suffix = interval === 1 ? '' : 's';
    return `${interval} ${epoch}${suffix} ago`;
};

const date2String = (date) => new Date().toDateString(date)

/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/ForgotPassword.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/ForgotPassword.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   forgotPasswordSubmission: () => (/* binding */ forgotPasswordSubmission)
/* harmony export */ });
Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './Http'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './Utility'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());





// block the setLoader div

Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())("setLoader").style.display = "none";

/**
 * @description Handles the forgot password submission process.
 * @param {string} formId - The ID of the form to submit.
 * @param {string} url - The URL to make the POST request to.
 * @param {string} redirectUrl - The URL to redirect the user to after the submission is complete.
 * @returns {function} - A function that handles the submission process.
 */
const forgotPasswordSubmission = (formId, url, redirectUrl) => {
    const emailInput = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())('email_id');

    /**
     * @description Handles the submission of the forgot password form.
     * @param {Event} e - The event object passed by the event listener.
     * @returns {void}
     * @throws {Error} - If there is an error with the submission
     */
    const forgotPasswordSubmissionHelper = (e) => {
        try {
            e.preventDefault();
            const email = emailInput.value.trim();
            if (!Object(function webpackMissingModule() { var e = new Error("Cannot find module './Utility'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(email)) {
                Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())("setLoader").style.display = "block";
                localStorage.setItem('redirect', redirectUrl);
                Object(function webpackMissingModule() { var e = new Error("Cannot find module './Http'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(url, formId, redirectUrl);
            }
        } catch (error) {
            Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(error);
        }
    }
    return forgotPasswordSubmissionHelper
}





/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/FormHelper.js":
/*!***************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/FormHelper.js ***!
  \***************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FormHelper)
/* harmony export */ });

class FormHelper {
    constructor(data) {
         if (!Array.isArray(data)) throwError('data must be an array of form elements');
        this.data = data;
        this.error = [];
        this.result = 0;
    }

    id(x) {
        return document.getElementById(x)
    }

    /**
     * general validation; check empty status, at least a single input, mobile length, white space
     */

    getData() {
        return this.data;
    }


    /**
     * Validate a single form field
     * @param {string} value - the value of the field to validate
     * @param {string} [type='general'] - the type of validation to perform. Currently only 'email' is supported
     * @returns {boolean} - true if the field is valid, false otherwise
     */
    validateField(value, type = 'general') {
        if (type === 'email') {
            const emailRegex = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
            return emailRegex.test(value);
        }
        return value.trim().length > 0;
    }


    /**
     * Loop through all the elements in the form and validate each one.
     * If element is empty, add error message to the error array and set result to false
     * If element is not empty, check if it validates according to its type (email or general)
     * If it does not validate, add error message to the error array and set result to false
     * @returns {void}
     */
    massValidate() {
        const invalidElements = this.data.flatMap(et => et.filter(post => {
            const postName = post.name.replace('_', ' ');
            let errMsg = this.id(`${post.name}_error`);

            // rid it off the submit and token
            if (['submit', 'button', 'showPassword_id', 'g-recaptcha-response', 'cancel', 'token', 'checkbox_id'].includes(post.name) ||
                ['button'].includes(post.id) || ['button'].includes(post.type)) return false;

            // check if there is no value
            if (['spouseName', 'spouseMobile', 'spouseEmail', 'fatherMobile', 'fatherEmail', 'motherMobile', 'maidenName', 'motherEmail'].includes(post.name)) {
                // post.value is not prpvided if it is not provided 
                post.value = post.value === "" ? "Not Provided" : post.value
            }

            if (post.value === '' || post.value === 'select') {
                errMsg.innerHTML = `${post.placeholder ?? "*"} cannot be left empty`;
                errMsg.style.color = 'red';
                this.error.push(`${postName.toUpperCase()} cannot be left empty`);
                this.result = false;
                return true;
            }

            if (post.name === 'email' && !this.validateField(post.value, 'email')) {
                errMsg.innerHTML = '* Please enter a valid email';
                errMsg.style.color = 'red';
                this.error.push('<li style="color: red;">Please enter a valid email</li>');
                this.result = false;
                return true;
            }

            return false;
        }));

        if (invalidElements.length > 0) this.result = false;
    }

    emailVal() {
        const emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
        let msg = `<li style=color:'red';> Please enter a valid email</li>`
        const email = this.id('email_id').value
        if (email.match(emailExp) === null) {
            this.id('email_error').innerHTML = msg
            this.id('email_error').style.color = "red"
            this.error.push(msg)
        }
    }

    /**
     * Clears all error messages and empties the error array.
     * Sets up event listeners to clear error messages for each input element.
     * Keyup event listener: for non-select inputs
     * Change event listener: for all inputs
     * @returns {void}
     */
    clearError() {
        this.error = []; // Empty the error array

        // Function to clear error messages for a given input element
        const clearErrorForElement = (elementName) => {
            const errorElement = this.id(`${elementName}_error`);
            if (errorElement) errorElement.innerHTML = '';
        };

        this.data.flat().forEach(({ id, name, value }) => {
            if (['submit', 'button', 'token', 'checkbox'].includes(id) || ['token', 'submit'].includes(name)) return;

            const element = this.id(id);
            if (!element) {
                console.error(`Element with ID '${id}' and post name '${name}' not found.`);
                return;
            }

            // Add event listeners to clear errors
            const clearErrorHandler = () => clearErrorForElement(name);
            if (value !== 'select') element.addEventListener('keyup', clearErrorHandler);
            element.addEventListener('change', clearErrorHandler);
        });
    }

    /**
     * Clears the values of all input elements in the form, excluding checkboxes and the submit button
     */
    clearHtml() {
        this.data.flat().forEach(post => {
            if (!['submit', 'checkbox'].includes(post.name) && post.type !== 'submit') {
                post.value = "";
            }
        });
    }
    /**
     * Check the length of the input in real time and display an error message if it exceeds the maximum length
     * @param {array of strings} input - the IDs of the input elements to check
     * @param {array of numbers} maxi - the maximum lengths for each input element
     */

    realTimeCheckLen(input, maxi) {
        input.forEach((id, i) => {
            const theData = this.id(`${id}_id`);
            if (!theData) {
                console.error(`Element with ID '${id}_id' not found`);
                return;
            }
            const max = maxi[i];
            theData.maxLength = parseInt(max) + 1;
            theData.addEventListener('input', () => {
                const error = this.id(`${id}_error`);
                error.innerHTML = (theData.value.length > max) ? `You have reached the maximum limit` : "";
                this.id(`${id}_help`).style.display = theData.value.length > max ? '' : 'none';
            });
        });
    }



    /**
     * Match the value of the second input to the value of the first input in real time
     * @param {string} first - the id of the first input
     * @param {string} second - the id of the second input
     */
    matchInput(first, second) {
        const firstInput = this.id(first + '_id');
        const secondInput = this.id(second + '_id');
        const error = this.id(`${second}_error`);

        const checkMatch = () => error.innerHTML = (firstInput.value !== secondInput.value) ? 'Your passwords do not match' : "";

        firstInput.addEventListener('input', checkMatch);
        secondInput.addEventListener('input', checkMatch);
    }
 

    /**
     * Injects the values in the html array to the elements with the IDs in the idArray
     * @param {array of strings} idArray - the IDs of the elements to inject the values to
     * @param {array of strings} html - the values to inject to the elements
     */
    injectData(idArray, html) {
        idArray.forEach((id, i) => this.id(id).innerHTML = html[i]);
    }

    /**
     *
     * @param {this is an id and its value is for duplication} firstInput
     * @param {* another id that accepts the value of the firstInput} takeFirstInput
     */
    duplicate(giveInput, takeInput) {
        let giver, taker;
        giver = this.id(giveInput)
        taker = this.id(takeInput)
        giver.addEventListener('keyup', () => {
            taker.value = giver.value;
        })
    }




    /**
     * Sends a get request to the server as the user types in the specified input element
     * and updates the content of the specified output element with the response from the server
     * @param {string} input - the id of the input element to listen to
     * @param {string} url - the url of the get request to send, the value of the input element will be appended to the end of the url
     * @param {string} outputId - the id of the element to update with the response from the server
     */
    realTimeServer(input, url, outputId) {
        const theInput = this.id(input)
        const output = this.id(outputId)
        theInput.addEventListener('keyup', async () => {
            const inputVal = theInput.value

            if (inputVal === "") {
                output.innerHTML = "";
                return;
            }

            try {
                const response = await axios.get(`${url}=${inputVal}`)
                output.innerHTML = response.data
            } catch (error) {
                console.error(error)
            }
        })
    }

    /**
     * Check if a yes/no radio button is checked and display "checked" in a hidden input field.
     * @param {string} yesId - the id of the yes radio button
     * @param {string} noId - the id of the no radio button
     * @param {string} hiddenInput - the id of the hidden input field to display the result
     */
    isChecked(yesId, noId, hiddenInput) {
        const checked = () => {
            if (this.id(yesId).checked) {
                alert('check')
                this.id(hiddenInput).innerHTML = 'checked';
            } else if (this.id(noId).checked) {
                this.id(hiddenInput).innerHTML = 'checked';
            }
        }

        this.id(yesId).addEventListener('click', checked)
        this.id(noId).addEventListener('click', checked)

    }

    previousAddress() {
        const timeAddy = this.id('time_at_address_id')
        const prevAddy = this.id('previous_address_class')
        const showPrev = () => {
            if (timeAddy.value != '3 years+') {
                prevAddy.style.display = 'block'
                this.id('previous_address_help').innerHTML = "Please enter your full address: House No, Street Name, Town/City and Post Code"
            } else {
                prevAddy.style.display = 'none'
            }

        }
        timeAddy.addEventListener('change', showPrev)

    }


}

/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/FormProcessing.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/FormProcessing.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   convertFormData: () => (/* binding */ convertFormData),
/* harmony export */   showImageFileUploadFn: () => (/* binding */ showImageFileUploadFn)
/* harmony export */ });
Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());


const convertFormData = (formId) => {
    const formInput = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(formId)
    const formInputArr = Array.from(formInput)
    return new FormHelper(formInputArr)

}

/**
 * Sets up an event listener for the given button element that triggers a file upload click event.
 * Also sets up an event listener for the given input element that displays the name of the selected files.
 * @param {string} uploadBtn - the id of a button element that will trigger the file upload dialog box.
 * @param {string} inputId - the id of the input element with the file type.
 * @param {string} fileName - the id of the element where the selected file names will be displayed.
 */
const showImageFileUploadFn = (uploadBtn, inputId, fileName) => {
  const input = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(inputId);
  const fileNamesEl = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(fileName);
  Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(uploadBtn).addEventListener('click', () => input.click());
  input.addEventListener('change', () => fileNamesEl.innerText = Array.from(input.files).map(file => file.name).join(', '));
};


/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/Http.js":
/*!*********************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/Http.js ***!
  \*********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getApiData: () => (/* binding */ getApiData),
/* harmony export */   getMultipleApiData: () => (/* binding */ getMultipleApiData),
/* harmony export */   postFormData: () => (/* binding */ postFormData),
/* harmony export */   postMultipleApiData: () => (/* binding */ postMultipleApiData)
/* harmony export */ });
Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './Loader'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios-retry */ "./node_modules/@modernman00/shared-js-lib/node_modules/axios-retry/dist/esm/index.js");





(0,axios_retry__WEBPACK_IMPORTED_MODULE_1__["default"])(axios__WEBPACK_IMPORTED_MODULE_2__["default"], { retries: 3 });


const postFormData = async (url, formId, redirect = null, css = null) => {
    const notificationForm = `${formId}_notification`;
    const notificationId = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(notificationForm);

    if (!notificationId) {
        throw new Error('Notification element not found');
    }

    // Cleanup previous notification styles
    notificationId.style.display = 'none';
    notificationId.className = notificationId.className.replace(/is-danger|is-success|w3-red|w3-green|bg-danger|bg-success/g, '');

    const form = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(formId);
    if (!form) {
        throw new Error('Form element not found');
    }

    const formEntries = new FormData(form);
    formEntries.delete('submit');
    formEntries.delete('checkbox_id');

    const options = {
        baseURL: '/',
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
        withCredentials: true,
    };

    try {
        const response = await axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url, formEntries, options);
        if (!(response.status >= 200 && response.status < 300)) {
            throw new Error(response.data?.message || 'Request failed');
        }

        const successClass = getNotificationClassByCSS(css || 'bulma', 'green');
        const { message } = response.data || {};
        const { id: idSetFromHttp, famCode: famCodeSetFromHttp, outcome: dbHttpResult } = typeof message === 'object' ? message : { outcome: message };

        if (!idSetFromHttp || !dbHttpResult || !famCodeSetFromHttp) {
            throw new Error('Response data is missing required fields');
        }

        sessionStorage.setItem('idSetFromHttp', sessionStorage.getItem('idSetFromHttp') || idSetFromHttp);
        sessionStorage.setItem('famCodeSetFromHttp', sessionStorage.getItem('famCodeSetFromHttp') || famCodeSetFromHttp);

        processFormDataAction(successClass, dbHttpResult, notificationId);

        if (redirect) {
            setTimeout(() => window.location.assign(redirect), 2000);
        }
    } catch (error) {
        const errorClass = getNotificationClassByCSS(css || 'bulma', 'red');
        const errorMessage = error.response?.data?.error || error.request || 'An unknown error occurred';
        processFormDataAction(errorClass, errorMessage, notificationId);
    }
};


/**
 * Displays a notification message and handles the loading indicator.
 * @param {string} cssClass - The CSS class to add to the notification element.
 * @param {string} message - The message to display in the notification element.
 * @param {HTMLElement} formNotificationId - The notification element to display the message in.
 * @returns {void}
 */
const processFormDataAction = (cssClass, message, formNotificationId) => {
    if (!formNotificationId) {
        return Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())('Notification element not found');
    }

    formNotificationId.style.display = 'block';
    formNotificationId.classList.add(cssClass);

    const errorElement = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())('error');
    if (errorElement) {
        errorElement.innerHTML = message;
        errorElement.scrollIntoView({ behavior: 'smooth' });
    }

    Object(function webpackMissingModule() { var e = new Error("Cannot find module './Loader'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())();
};

const getNotificationClassByCSS = (css, color) => (css === 'bulma' ? `is-${color}` : color === 'green' ? `${css}-${color}` : `${css}-danger`);


/**
 * Fetches data from a specified API endpoint using a GET request.
 * 
 * @param {string} URL - The API endpoint to fetch data from.
 * @param {string|null} [token=null] - Optional authorization token for the request.
 * @returns {Promise<Object>} - A promise that resolves with the response data or rejects with an error.
 */

const getApiData = async (URL, token = null) => {
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    try {
        const response = await axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(URL, { headers });
        return response.data;
    } catch (error) {
        return Promise.reject(error);
    }
}


const getMultipleApiData = async (url1, url2, token = null) => {
    const config = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            Authorization: `Bearer ${token}`,
        },
    };

    return await axios__WEBPACK_IMPORTED_MODULE_2__["default"].all([axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(url1, config), axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(url2, config)]).then(axios__WEBPACK_IMPORTED_MODULE_2__["default"].spread((res1, res2) => [res1.data, res2.data]));
};

const postMultipleApiData = async (url1, url2, formData, token = null) => {
    const config = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
        },
    };

    const [res1, res2] = await Promise.all([
        axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url1, formData, config),
        axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url2, formData, config),
    ]);

    return [res1.data, res2.data];
};


/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/Loader.js":
/*!***********************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/Loader.js ***!
  \***********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   clearLoader: () => (/* binding */ clearLoader),
/* harmony export */   loaderIcon: () => (/* binding */ loaderIcon),
/* harmony export */   loaderIconBootstrap: () => (/* binding */ loaderIconBootstrap),
/* harmony export */   loaderIconBulma: () => (/* binding */ loaderIconBulma),
/* harmony export */   showLoader: () => (/* binding */ showLoader)
/* harmony export */ });
const loaderIconBootstrap = () => {

    return `<div class="spinner-grow text-primary" role="status">
        <span class="sr-only">Loading...</span>
        </div>`
}

const loaderIcon = () => {

    return `<div class="loader"></div>`
}

const loaderIconBulma = () => {

    return `<div class="is-loading"></div>`
}

const clearLoader = (elementId = 'setLoader', loaderClass = "loader") => {
    const loader = id(elementId);
    if (!loader) {
        throw new Error(`Element not found`);
    }

    loader.style.display = "none";
    loader.classList.remove(loaderClass);

}
const showLoader = (elementId ='setLoader', loaderClass = "loader") => {
    const loader = id(elementId);
    if (!loader) {
        throw new Error(`Element  not found`);
    }
    loader.classList.add(loaderClass);
    loader.style.display = "block";
};


/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/Login.js":
/*!**********************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/Login.js ***!
  \**********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   loginSubmission: () => (/* binding */ loginSubmission)
/* harmony export */ });
Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './FormProcessing'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './Http'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './Utility'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './Loader'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());







// block the setLoader div

/**
 * Handles the submission of the login form.
 * @param {string} formId - The ID of the form to submit.
 * @param {string} loginURL - The URL to make the POST request to.
 * @param {string} redirect - The URL to redirect the user to after the submission is complete.
 * @param {string} [css=null] - The CSS class to add to the notification element if the submission is successful.
 * @returns {void}
 * @throws {Error} - If there is an error with the submission
 */
const loginSubmission = async (formId, loginURL, redirect, css = null) => {
    const formData = Object(function webpackMissingModule() { var e = new Error("Cannot find module './FormProcessing'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(formId);
    formData.clearError();

    try {
        formData.emailVal();
        formData.massValidate();

        if (!formData.error.length) {

            Object(function webpackMissingModule() { var e = new Error("Cannot find module './Loader'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())();
            localStorage.setItem('redirect', redirect);

            await Object(function webpackMissingModule() { var e = new Error("Cannot find module './Http'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(loginURL, formId, redirect, css);
        } else {
            alert('The form cannot be submitted. Please check the errors');
        }
    } catch (err) {
        Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(err);
    } finally {
       Object(function webpackMissingModule() { var e = new Error("Cannot find module './Loader'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())();
    }
    
}


// id('button').addEventListener('click', LoginSubmission)

Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())("showPassword_id").addEventListener('click', () => Object(function webpackMissingModule() { var e = new Error("Cannot find module './Utility'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())('password_id'))

/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/ShowResponse.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/ShowResponse.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   deleteNotification: () => (/* binding */ deleteNotification),
/* harmony export */   msgException: () => (/* binding */ msgException),
/* harmony export */   showError: () => (/* binding */ showError),
/* harmony export */   showNotification: () => (/* binding */ showNotification),
/* harmony export */   showResponse: () => (/* binding */ showResponse)
/* harmony export */ });
Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");



const showError = (e) => {
  
    log(e.message, " ERROR MESSAGE") // "null has no properties"
    log(e.name, " ERROR NAME") // "TypeError"
    log(e.fileName,  " ERROR FILENAME") // "Scratchpad/1"
    log(e.lineNumber, " ERROR LINENUMBER") // 2

    log(e.stack)
}

const msgException = (errorMessage) => {
  
    throw new Error(errorMessage)
}


/**
 * 
 * @param {*} elementId - element id
 * @param {*} addClass either a success or danger class (green or red)
 * @param {*} message - html message to convey success or failure
 * @param {*} timer - timer for the message to disappear- default is 5 secs
 */
const showNotification = (elementId, addClass, message, timer = 5000) => {
    // display the success information for 10sec
    Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${elementId}`).style.display = "block" // unblock the notification
    Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${elementId}`).classList.add(addClass) // add the success class
    Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${elementId}`).innerHTML = message // error element
    Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())('loader').classList.remove('loader') // remove loader

    setTimeout(() => {
        Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${elementId}`).style.backgroundColor = ""
        Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${elementId}`).style.color = ""
        Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${elementId}`).innerHTML = ""
    }, timer)
}

  /**
   * 
   * @param {*} elementId - element id 
   * @returns {Promise<void>}
   * @description This is a function that deletes a notification. It takes in the elementId of the delete button
   *              and uses that to get the user ID and the notification ID. It makes a put request to the server to
   *              mark the notification as read. If the request is successful, it removes the notification from the page
   *              and decrements the notification count by 1. If the request is unsuccessful, it logs an error message.
   */
  const deleteNotification = async (elementId) => {

         // Extract the user ID from the target ID
            const senderId = elementId.replace("deleteNotification", "notificationBar");
          
            const elementData = Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(elementId)
            const data = elementData.getAttribute("data-id");

            // change the background of the clicked element 

            const notificationHTML = Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(senderId);

            // Make sure required variables are defined before using them
            if (
                typeof yourId === 'undefined' ||
                typeof famCode === 'undefined'
            ) {
                msgException("Required parameters (yourId or famCode) are not defined");
            }

            const url = `/removeNotification/${yourId}/${famCode}/${data}`


            const response = await axios__WEBPACK_IMPORTED_MODULE_1__["default"].put(url)


            if (response.data.message === "Notification marked as read") {

                // remove a html element with notificationBar after 2 mins 
                notificationHTML.remove()

                // reduce the notification count as you have deleted the notification

                const newValues = parseInt(sessionStorage.getItem('notificationCount') - 1)
                Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())('notification_count').innerHTML = newValues;
            } else {
                msgException("Error removing notification" + " " + response.data.message);
            }
    }

    const showResponse = (theId, message, status) => {
    const responseEl = Object(function webpackMissingModule() { var e = new Error("Cannot find module './utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(theId)
    const col = status ? 'green' : 'red'

    responseEl.innerHTML = message
    responseEl.style.color = 'green'
    responseEl.style.backgroundColor = col
    responseEl.style.color = 'white';
    setTimeout(() => {
        responseEl.innerHTML = '';
    }, 5000); // Disappear after 5 seconds

}


/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/Utility.js":
/*!************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/Utility.js ***!
  \************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autoCompleter: () => (/* binding */ autoCompleter),
/* harmony export */   distinctValue: () => (/* binding */ distinctValue),
/* harmony export */   emailVal: () => (/* binding */ emailVal),
/* harmony export */   msgException: () => (/* binding */ msgException),
/* harmony export */   realTimeCheckLen: () => (/* binding */ realTimeCheckLen),
/* harmony export */   sanitizeInput: () => (/* binding */ sanitizeInput),
/* harmony export */   showError: () => (/* binding */ showError),
/* harmony export */   showPassword: () => (/* binding */ showPassword),
/* harmony export */   toSentenceCase: () => (/* binding */ toSentenceCase)
/* harmony export */ });
Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
/* harmony import */ var Autocompleter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Autocompleter */ "./node_modules/Autocompleter/autocomplete.js");



/**
 * Set maxlength of input fields and display error message and help text in real time
 * @param {string[]} input - array of ids of input fields
 * @param {string[]} maxi - array of maximum lengths of input fields, corresponding to the input array
 * @throws {Error} if any element with id from the input array is not found or is empty
 */
const realTimeCheckLen = (input, maxi) => {
    const data = input.map(el => Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${el}_id`));
    const errors = input.map(el => Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${el}_error`));
    const helps = input.map(el => Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(`${el}_help`));

    maxi.forEach((max, i) => {
        if (!data[i] || !errors[i] || !helps[i]) {
            throw new Error(`Element with ID '${input[i]}_id' not found or is empty`);
        }

        data[i].maxLength = parseInt(max, 10) + 1;
        data[i].addEventListener('keyup', () => {
            errors[i].innerHTML = (data[i].value.length > max) ? `You have reached the maximum limit` : "";
            helps[i].style.display = data[i].value.length > max ? '' : 'none';
            setTimeout(() => {
                helps[i].style.display = 'none';
            }, 5000);
        });
    });
}

/**
 * Converts a string to sentence case.
 *
 * Sentence case is a string where the first letter of each word is capitalized, and the rest of the letters are in lowercase.
 *
 * @param {string} str The string to convert to sentence case.
 * @returns {string} A new string in sentence case.
 */
const toSentenceCase = (str) => {
    return str
        .toLowerCase() // Convert the string to lowercase
        .split(' ')    // Split the string into words
        .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize the first letter of each word
        .join(' ');    // Join the words back into a string
}

/**
 * Sets up an autocomplete functionality for an input element.
 *
 * @param {string} inputId - The ID of the input element to attach the autocomplete to.
 * @param {Array<Object>} data - An array of objects containing data for autocomplete suggestions, 
 *                               where each object should have a 'firstName' property.
 *
 * The function converts all `firstName` properties to lowercase for case-insensitive matching.
 * It initializes the autocomplete with the given input element and data, providing real-time
 * suggestions based on user input. On selecting a suggestion, it populates the input element
 * with the selected suggestion's `firstName`.
 */

const autoCompleter = (inputId, data) => {
    const lowerCaseData = data.map(item => ({...item, firstName: item.firstName.toLowerCase()}));
    Autocompleter__WEBPACK_IMPORTED_MODULE_1__({
        input: inputId,
        fetch: function (text, update) {
            const suggestions = lowerCaseData.filter(n => n.firstName.startsWith(text.toLowerCase()));
            update(suggestions);
        },
        onSelect: function (item) {
            input.value = item.firstName;
        }
    })
}

/**
 * Removes duplicate values from an array.
 *
 * @param {Array} array - The array from which to remove duplicates.
 * @returns {Array} A new array containing only unique values from the original array.
 */

const distinctValue = (array) => {
    return [...new Set(array)]
}

/**
 * Toggles the type of the input element with the given ID between 'password' and 'text'.
 *
 * @param {string} inputId - The ID of the input element to toggle.
 */
const showPassword = (inputId) => {
    const y = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(inputId);
    y.type = y.type === "password" ? "text" : "password";
}


/**
 * Validates an email address and updates the error message if invalid.
 *
 * @param {string} email - The email address to validate.
 * @returns {number} Returns 1 if the email is invalid and sets an error message, otherwise returns 0.
 */

const emailVal = (email) => {
    const emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
    if (!emailExp.test(email)) {
        const errorElement = Object(function webpackMissingModule() { var e = new Error("Cannot find module './UtilityHtml'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())('email_error');
        errorElement.innerHTML = `<li style="color:red;">Please enter a valid email</li>`;
        errorElement.style.color = "red";
        return 1;
    }
    return 0;
}


const showError = (e) => console.error(`${e.name} at ${e.fileName}:${e.lineNumber} - ${e.message}\n${e.stack}`);

const msgException = (errorMessage) => {
  
    throw new Error(errorMessage)
}

/**
 * Sanitizes a string input by escaping special HTML characters to prevent XSS attacks.
 * Converts &, <, >, ", and ' to their respective HTML entities.
 * Trims leading and trailing whitespace from the input.
 *
 * @param {string} input - The input string to sanitize.
 * @returns {string} Sanitized string with HTML special characters escaped.
 */

const sanitizeInput = (input) => {
    if (typeof input !== 'string') return '';
    return input
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .trim();
};


/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/UtilityHtml.js":
/*!****************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/UtilityHtml.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkBox: () => (/* binding */ checkBox),
/* harmony export */   checkBox2: () => (/* binding */ checkBox2),
/* harmony export */   checkElements: () => (/* binding */ checkElements),
/* harmony export */   checkManyElements: () => (/* binding */ checkManyElements),
/* harmony export */   createAndAppendElement: () => (/* binding */ createAndAppendElement),
/* harmony export */   hideElement: () => (/* binding */ hideElement),
/* harmony export */   id: () => (/* binding */ id),
/* harmony export */   idInnerHTML: () => (/* binding */ idInnerHTML),
/* harmony export */   idValue: () => (/* binding */ idValue),
/* harmony export */   isChecked: () => (/* binding */ isChecked),
/* harmony export */   log: () => (/* binding */ log),
/* harmony export */   manipulateAttribute: () => (/* binding */ manipulateAttribute),
/* harmony export */   qSel: () => (/* binding */ qSel),
/* harmony export */   qSelAll: () => (/* binding */ qSelAll),
/* harmony export */   qSelInnerHTML: () => (/* binding */ qSelInnerHTML),
/* harmony export */   qSelValue: () => (/* binding */ qSelValue),
/* harmony export */   removeDiv: () => (/* binding */ removeDiv),
/* harmony export */   showElement: () => (/* binding */ showElement),
/* harmony export */   warningSign: () => (/* binding */ warningSign),
/* harmony export */   write: () => (/* binding */ write)
/* harmony export */ });


const id = (id) => document.getElementById(id)
const idValue = (id) => id(id).value
const idInnerHTML = (id) => id(id).innerHTML
const warningSign = "\u26A0"; // danger warning sign

const qSel = (name) => document.querySelector(name)
const qSelAll = (name) => document.querySelectorAll(name)
const qSelValue = (name) => qSel(name).value
const qSelInnerHTML = (name) => qSel(name).innerHTML

const log = (id, identifier =null) => {
    console.log(' start'+ "  " + identifier)
    console.log(id)
    console.log(' end'+ "  " +identifier)
}
const write = (input) => document.write(input)

const hideElement = (elementId) => {
  id(elementId).style.display = "none";
};

const showElement = (elementId) => {
  id(elementId).style.display = "block";
};

const removeDiv = (div_id) => {
    const div = document.getElementById(div_id)
    if (div) {
        return div.remove()
    }

}


/**
 * @description Manipulates an attribute of a specified HTML element
 * @param {string} idName - The ID of the element to manipulate
 * @param {string} removeOrSet - Specify "remove" to remove the attribute, otherwise set the attribute
 * @param {string} attributeType - The type of attribute to manipulate
 * @param {string|null} [nameValue=null] - The value to set for the attribute if adding it
 * @returns {void}
 */

const manipulateAttribute = (idName, removeOrSet, attributeType, nameValue =null) => {

    if(removeOrSet === "remove") {
           id(idName).removeAttribute(attributeType)
    } else {
        id(idName).setAttribute(attributeType, nameValue)
    }
 
  
}


    // Function to check for elements and render if they exist
    const checkElements = (idOrClass, classString, theFunction = null) => {

        const doesElementExist = (idOrClass === "id") ? id(classString) : qSel(classString)
        // Check if elements exist before calling render function
        if (doesElementExist.length) {
            theFunction(doesElementExist);
        }

    };

    /**
     * @description Check if elements exist before calling render function
     * @param {string} idOrClass - whether to use id or class
     * @param {string} classString - the class or id name
     * @param {function} [theFunction] - the function to call if elements exist
     * @returns {void}
     */
     const checkManyElements = (idOrClass, classString, theFunction = null) => {

        const doesElementExist = (idOrClass === "id") ? id(classString) : qSelAll(classString)
        // Check if elements exist before calling render function
        if (doesElementExist.length > 0) {
            theFunction(doesElementExist);
        }

    };

    /**
     * @description Create a new element and append it to a parent element
     * @param {string} elementType - the type of element to create
     * @param {string} setId - the id of the new element
     * @param {string} parent - the id of the parent element
     * @param {string} [setClass] - an optional class to set on the new element
     * @returns {void}
     */
   const createAndAppendElement = (elementType, setId, parent, setClass = null) => {
    const newDiv = document.createElement(elementType);
    newDiv.setAttribute('id', setId)
    newDiv.setAttribute('class', `field ${setClass}`)
    const parentDiv = id(parent)
    return parentDiv.appendChild(newDiv)
}


const checkBox = (subject) => {
    return `<div class="control"> 
        <label class="radio">
          <input type="radio" name="send${subject}" value="yes" id=${subject}Yes > Yes 
        </label>
        <label class="radio"> 
          <input type="radio" name="send${subject}" value="no" id=${subject}No checked> No 
        </label>
      </div>`;
}

const checkBox2 = (subject) => {
    return `<div class="control"> 
        <label class="checkbox">
          <input type="checkbox" name="send${subject}" value="yes" id=${subject}Yes> Yes 
        </label>
        <label class="checkbox"> 
          <input type="checkbox" name="send${subject}" value="no" id=${subject}No> No 
        </label>
      </div>`

}

const isChecked = (name, fn) => {
    const yesId = (`${name}Yes`)
    const noId = `${name}No`
    const checked = () => {
        if (id(yesId).checked) {
            alert('check')
            fn()
        } else if (id(noId).checked) {
            alert('check No')
        }
    }
    id(yesId).addEventListener('click', checked)
    id(noId).addEventListener('click', checked)
}


  

/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/index.js ***!
  \**********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autoCompleter: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.autoCompleter),
/* harmony export */   calTiming: () => (/* reexport safe */ _DateTime_js__WEBPACK_IMPORTED_MODULE_11__.calTiming),
/* harmony export */   checkBox: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.checkBox),
/* harmony export */   checkBox2: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.checkBox2),
/* harmony export */   checkCookie: () => (/* reexport safe */ _Cookie_js__WEBPACK_IMPORTED_MODULE_7__.checkCookie),
/* harmony export */   checkElements: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.checkElements),
/* harmony export */   checkManyElements: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.checkManyElements),
/* harmony export */   clearLoader: () => (/* reexport safe */ _Loader_js__WEBPACK_IMPORTED_MODULE_4__.clearLoader),
/* harmony export */   convertFormData: () => (/* reexport safe */ _FormProcessing_js__WEBPACK_IMPORTED_MODULE_2__.convertFormData),
/* harmony export */   createAndAppendElement: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.createAndAppendElement),
/* harmony export */   date2String: () => (/* reexport safe */ _DateTime_js__WEBPACK_IMPORTED_MODULE_11__.date2String),
/* harmony export */   deleteNotification: () => (/* reexport safe */ _ShowResponse_js__WEBPACK_IMPORTED_MODULE_10__.deleteNotification),
/* harmony export */   distinctValue: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.distinctValue),
/* harmony export */   emailVal: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.emailVal),
/* harmony export */   forgotPasswordSubmission: () => (/* reexport safe */ _ForgotPassword_js__WEBPACK_IMPORTED_MODULE_5__.forgotPasswordSubmission),
/* harmony export */   getApiData: () => (/* reexport safe */ _Http_js__WEBPACK_IMPORTED_MODULE_0__.getApiData),
/* harmony export */   getCookie: () => (/* reexport safe */ _Cookie_js__WEBPACK_IMPORTED_MODULE_7__.getCookie),
/* harmony export */   getMultipleApiData: () => (/* reexport safe */ _Http_js__WEBPACK_IMPORTED_MODULE_0__.getMultipleApiData),
/* harmony export */   hideElement: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.hideElement),
/* harmony export */   id: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.id),
/* harmony export */   idInnerHTML: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.idInnerHTML),
/* harmony export */   idValue: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.idValue),
/* harmony export */   injectCountryCode: () => (/* reexport safe */ _CountryCode_js__WEBPACK_IMPORTED_MODULE_12__.injectCountryCode),
/* harmony export */   isChecked: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.isChecked),
/* harmony export */   loaderIcon: () => (/* reexport safe */ _Loader_js__WEBPACK_IMPORTED_MODULE_4__.loaderIcon),
/* harmony export */   loaderIconBootstrap: () => (/* reexport safe */ _Loader_js__WEBPACK_IMPORTED_MODULE_4__.loaderIconBootstrap),
/* harmony export */   loaderIconBulma: () => (/* reexport safe */ _Loader_js__WEBPACK_IMPORTED_MODULE_4__.loaderIconBulma),
/* harmony export */   log: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.log),
/* harmony export */   loginSubmission: () => (/* reexport safe */ _Login_js__WEBPACK_IMPORTED_MODULE_6__.loginSubmission),
/* harmony export */   manipulateAttribute: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute),
/* harmony export */   msgException: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.msgException),
/* harmony export */   postFormData: () => (/* reexport safe */ _Http_js__WEBPACK_IMPORTED_MODULE_0__.postFormData),
/* harmony export */   postMultipleApiData: () => (/* reexport safe */ _Http_js__WEBPACK_IMPORTED_MODULE_0__.postMultipleApiData),
/* harmony export */   qSel: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.qSel),
/* harmony export */   qSelAll: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.qSelAll),
/* harmony export */   qSelInnerHTML: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.qSelInnerHTML),
/* harmony export */   qSelValue: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.qSelValue),
/* harmony export */   realTimeCheckLen: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.realTimeCheckLen),
/* harmony export */   removeDiv: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.removeDiv),
/* harmony export */   sanitizeInput: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.sanitizeInput),
/* harmony export */   setCookie: () => (/* reexport safe */ _Cookie_js__WEBPACK_IMPORTED_MODULE_7__.setCookie),
/* harmony export */   showElement: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.showElement),
/* harmony export */   showError: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.showError),
/* harmony export */   showImageFileUploadFn: () => (/* reexport safe */ _FormProcessing_js__WEBPACK_IMPORTED_MODULE_2__.showImageFileUploadFn),
/* harmony export */   showLoader: () => (/* reexport safe */ _Loader_js__WEBPACK_IMPORTED_MODULE_4__.showLoader),
/* harmony export */   showNotification: () => (/* reexport safe */ _ShowResponse_js__WEBPACK_IMPORTED_MODULE_10__.showNotification),
/* harmony export */   showPassword: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.showPassword),
/* harmony export */   showResponse: () => (/* reexport safe */ _ShowResponse_js__WEBPACK_IMPORTED_MODULE_10__.showResponse),
/* harmony export */   timeAgo: () => (/* reexport safe */ _DateTime_js__WEBPACK_IMPORTED_MODULE_11__.timeAgo),
/* harmony export */   toSentenceCase: () => (/* reexport safe */ _Utility_js__WEBPACK_IMPORTED_MODULE_3__.toSentenceCase),
/* harmony export */   updateTimeRealTime: () => (/* reexport safe */ _DateTime_js__WEBPACK_IMPORTED_MODULE_11__.updateTimeRealTime),
/* harmony export */   warningSign: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.warningSign),
/* harmony export */   write: () => (/* reexport safe */ _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__.write)
/* harmony export */ });
/* empty/unused harmony star reexport */
/* harmony import */ var _Http_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Http.js */ "./node_modules/@modernman00/shared-js-lib/Http.js");
/* harmony import */ var _UtilityHtml_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./UtilityHtml.js */ "./node_modules/@modernman00/shared-js-lib/UtilityHtml.js");
/* harmony import */ var _FormProcessing_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FormProcessing.js */ "./node_modules/@modernman00/shared-js-lib/FormProcessing.js");
/* harmony import */ var _Utility_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Utility.js */ "./node_modules/@modernman00/shared-js-lib/Utility.js");
/* harmony import */ var _Loader_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Loader.js */ "./node_modules/@modernman00/shared-js-lib/Loader.js");
/* harmony import */ var _ForgotPassword_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./ForgotPassword.js */ "./node_modules/@modernman00/shared-js-lib/ForgotPassword.js");
/* harmony import */ var _Login_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./Login.js */ "./node_modules/@modernman00/shared-js-lib/Login.js");
/* harmony import */ var _Cookie_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./Cookie.js */ "./node_modules/@modernman00/shared-js-lib/Cookie.js");
/* harmony import */ var _FormHelper_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./FormHelper.js */ "./node_modules/@modernman00/shared-js-lib/FormHelper.js");
Object(function webpackMissingModule() { var e = new Error("Cannot find module '.Loader.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
/* harmony import */ var _ShowResponse_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./ShowResponse.js */ "./node_modules/@modernman00/shared-js-lib/ShowResponse.js");
/* harmony import */ var _DateTime_js__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./DateTime.js */ "./node_modules/@modernman00/shared-js-lib/DateTime.js");
/* harmony import */ var _CountryCode_js__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./CountryCode.js */ "./node_modules/@modernman00/shared-js-lib/CountryCode.js");















/***/ }),

/***/ "./node_modules/@modernman00/shared-js-lib/node_modules/axios-retry/dist/esm/index.js":
/*!********************************************************************************************!*\
  !*** ./node_modules/@modernman00/shared-js-lib/node_modules/axios-retry/dist/esm/index.js ***!
  \********************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DEFAULT_OPTIONS: () => (/* binding */ DEFAULT_OPTIONS),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   exponentialDelay: () => (/* binding */ exponentialDelay),
/* harmony export */   isIdempotentRequestError: () => (/* binding */ isIdempotentRequestError),
/* harmony export */   isNetworkError: () => (/* binding */ isNetworkError),
/* harmony export */   isNetworkOrIdempotentRequestError: () => (/* binding */ isNetworkOrIdempotentRequestError),
/* harmony export */   isRetryableError: () => (/* binding */ isRetryableError),
/* harmony export */   isSafeRequestError: () => (/* binding */ isSafeRequestError),
/* harmony export */   linearDelay: () => (/* binding */ linearDelay),
/* harmony export */   namespace: () => (/* binding */ namespace),
/* harmony export */   retryAfter: () => (/* binding */ retryAfter)
/* harmony export */ });
/* harmony import */ var is_retry_allowed__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! is-retry-allowed */ "./node_modules/is-retry-allowed/index.js");

const namespace = 'axios-retry';
function isNetworkError(error) {
    const CODE_EXCLUDE_LIST = ['ERR_CANCELED', 'ECONNABORTED'];
    if (error.response) {
        return false;
    }
    if (!error.code) {
        return false;
    }
    // Prevents retrying timed out & cancelled requests
    if (CODE_EXCLUDE_LIST.includes(error.code)) {
        return false;
    }
    // Prevents retrying unsafe errors
    return is_retry_allowed__WEBPACK_IMPORTED_MODULE_0__(error);
}
const SAFE_HTTP_METHODS = ['get', 'head', 'options'];
const IDEMPOTENT_HTTP_METHODS = SAFE_HTTP_METHODS.concat(['put', 'delete']);
function isRetryableError(error) {
    return (error.code !== 'ECONNABORTED' &&
        (!error.response ||
            error.response.status === 429 ||
            (error.response.status >= 500 && error.response.status <= 599)));
}
function isSafeRequestError(error) {
    if (!error.config?.method) {
        // Cannot determine if the request can be retried
        return false;
    }
    return isRetryableError(error) && SAFE_HTTP_METHODS.indexOf(error.config.method) !== -1;
}
function isIdempotentRequestError(error) {
    if (!error.config?.method) {
        // Cannot determine if the request can be retried
        return false;
    }
    return isRetryableError(error) && IDEMPOTENT_HTTP_METHODS.indexOf(error.config.method) !== -1;
}
function isNetworkOrIdempotentRequestError(error) {
    return isNetworkError(error) || isIdempotentRequestError(error);
}
function retryAfter(error = undefined) {
    const retryAfterHeader = error?.response?.headers['retry-after'];
    if (!retryAfterHeader) {
        return 0;
    }
    // if the retry after header is a number, convert it to milliseconds
    let retryAfterMs = (Number(retryAfterHeader) || 0) * 1000;
    // If the retry after header is a date, get the number of milliseconds until that date
    if (retryAfterMs === 0) {
        retryAfterMs = (new Date(retryAfterHeader).valueOf() || 0) - Date.now();
    }
    return Math.max(0, retryAfterMs);
}
function noDelay(_retryNumber = 0, error = undefined) {
    return Math.max(0, retryAfter(error));
}
function exponentialDelay(retryNumber = 0, error = undefined, delayFactor = 100) {
    const calculatedDelay = 2 ** retryNumber * delayFactor;
    const delay = Math.max(calculatedDelay, retryAfter(error));
    const randomSum = delay * 0.2 * Math.random(); // 0-20% of the delay
    return delay + randomSum;
}
/**
 * Linear delay
 * @param {number | undefined} delayFactor - delay factor in milliseconds (default: 100)
 * @returns {function} (retryNumber: number, error: AxiosError | undefined) => number
 */
function linearDelay(delayFactor = 100) {
    return (retryNumber = 0, error = undefined) => {
        const delay = retryNumber * delayFactor;
        return Math.max(delay, retryAfter(error));
    };
}
const DEFAULT_OPTIONS = {
    retries: 3,
    retryCondition: isNetworkOrIdempotentRequestError,
    retryDelay: noDelay,
    shouldResetTimeout: false,
    onRetry: () => { },
    onMaxRetryTimesExceeded: () => { },
    validateResponse: null
};
function getRequestOptions(config, defaultOptions) {
    return { ...DEFAULT_OPTIONS, ...defaultOptions, ...config[namespace] };
}
function setCurrentState(config, defaultOptions, resetLastRequestTime = false) {
    const currentState = getRequestOptions(config, defaultOptions || {});
    currentState.retryCount = currentState.retryCount || 0;
    if (!currentState.lastRequestTime || resetLastRequestTime) {
        currentState.lastRequestTime = Date.now();
    }
    config[namespace] = currentState;
    return currentState;
}
function fixConfig(axiosInstance, config) {
    // @ts-ignore
    if (axiosInstance.defaults.agent === config.agent) {
        // @ts-ignore
        delete config.agent;
    }
    if (axiosInstance.defaults.httpAgent === config.httpAgent) {
        delete config.httpAgent;
    }
    if (axiosInstance.defaults.httpsAgent === config.httpsAgent) {
        delete config.httpsAgent;
    }
}
async function shouldRetry(currentState, error) {
    const { retries, retryCondition } = currentState;
    const shouldRetryOrPromise = (currentState.retryCount || 0) < retries && retryCondition(error);
    // This could be a promise
    if (typeof shouldRetryOrPromise === 'object') {
        try {
            const shouldRetryPromiseResult = await shouldRetryOrPromise;
            // keep return true unless shouldRetryPromiseResult return false for compatibility
            return shouldRetryPromiseResult !== false;
        }
        catch (_err) {
            return false;
        }
    }
    return shouldRetryOrPromise;
}
async function handleRetry(axiosInstance, currentState, error, config) {
    currentState.retryCount += 1;
    const { retryDelay, shouldResetTimeout, onRetry } = currentState;
    const delay = retryDelay(currentState.retryCount, error);
    // Axios fails merging this configuration to the default configuration because it has an issue
    // with circular structures: https://github.com/mzabriskie/axios/issues/370
    fixConfig(axiosInstance, config);
    if (!shouldResetTimeout && config.timeout && currentState.lastRequestTime) {
        const lastRequestDuration = Date.now() - currentState.lastRequestTime;
        const timeout = config.timeout - lastRequestDuration - delay;
        if (timeout <= 0) {
            return Promise.reject(error);
        }
        config.timeout = timeout;
    }
    config.transformRequest = [(data) => data];
    await onRetry(currentState.retryCount, error, config);
    if (config.signal?.aborted) {
        return Promise.resolve(axiosInstance(config));
    }
    return new Promise((resolve) => {
        const abortListener = () => {
            clearTimeout(timeout);
            resolve(axiosInstance(config));
        };
        const timeout = setTimeout(() => {
            resolve(axiosInstance(config));
            if (config.signal?.removeEventListener) {
                config.signal.removeEventListener('abort', abortListener);
            }
        }, delay);
        if (config.signal?.addEventListener) {
            config.signal.addEventListener('abort', abortListener, { once: true });
        }
    });
}
async function handleMaxRetryTimesExceeded(currentState, error) {
    if (currentState.retryCount >= currentState.retries)
        await currentState.onMaxRetryTimesExceeded(error, currentState.retryCount);
}
const axiosRetry = (axiosInstance, defaultOptions) => {
    const requestInterceptorId = axiosInstance.interceptors.request.use((config) => {
        setCurrentState(config, defaultOptions, true);
        if (config[namespace]?.validateResponse) {
            // by setting this, all HTTP responses will be go through the error interceptor first
            config.validateStatus = () => false;
        }
        return config;
    });
    const responseInterceptorId = axiosInstance.interceptors.response.use(null, async (error) => {
        const { config } = error;
        // If we have no information to retry the request
        if (!config) {
            return Promise.reject(error);
        }
        const currentState = setCurrentState(config, defaultOptions);
        if (error.response && currentState.validateResponse?.(error.response)) {
            // no issue with response
            return error.response;
        }
        if (await shouldRetry(currentState, error)) {
            return handleRetry(axiosInstance, currentState, error, config);
        }
        await handleMaxRetryTimesExceeded(currentState, error);
        return Promise.reject(error);
    });
    return { requestInterceptorId, responseInterceptorId };
};
// Compatibility with CommonJS
axiosRetry.isNetworkError = isNetworkError;
axiosRetry.isSafeRequestError = isSafeRequestError;
axiosRetry.isIdempotentRequestError = isIdempotentRequestError;
axiosRetry.isNetworkOrIdempotentRequestError = isNetworkOrIdempotentRequestError;
axiosRetry.exponentialDelay = exponentialDelay;
axiosRetry.linearDelay = linearDelay;
axiosRetry.isRetryableError = isRetryableError;
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (axiosRetry);


/***/ })

}]);