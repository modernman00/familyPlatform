import { id, log } from '../global'
import axios from 'axios'
import axiosRetry from 'axios-retry';
// import Cookies from 'js-cookie'

axiosRetry(axios, { retries: 3 });

/**
 * 
* Sends form data via POST request.
 * @param {string} url - The URL to post the data to.
 * @param {string} formId - The ID or class of the form.
 * @param {string|null} redirect - The page to redirect to after successful submission.
 * @param {string|null} css - The CSS framework to use for notification styling (e.g., 'W3css', 'bulma').
 NOTICE:::Make sure you set the notification id as the formId_notification
 */
export const postFormData = async(url, formId, redirect = null, css = null) => {

    let notificationForm = `${formId}_notification`
    const notificationId = id(notificationForm)

    // extract the form entries
    const form = id(formId)

    let formEntries = new FormData(form)

    formEntries.delete('submit')
    formEntries.delete('checkbox_id')
        // formEntries.delete('token')

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }


    // AXIOS POST FUNCTIONALITY
    try {
        const response = await axios.post(url, formEntries, options);

        const successClass = getNotificationClassByCSS("bulma", 'green');

        // check if response.data.message is an array

        let idSetFromHttp = null;
        let famCodeSetFromHttp = null;
        let dbHttpResult = null;

        if (typeof response.data.message === 'object') {
            idSetFromHttp = response.data.message.id;
            famCodeSetFromHttp = response.data.message.famCode;
            dbHttpResult = response.data.message.outcome;

            // check if idSetFromHttp is null, then throw error

            if (!idSetFromHttp) {
                throw new Error('idSetFromHttp is null');
            }

            // throw error if famCodeSetFromHttp is null

            if (!famCodeSetFromHttp) {
                throw new Error('famCodeSetFromHttp is null');
            }

        } else {
            dbHttpResult = response.data.message;
        }



        sessionStorage.setItem('idSetFromHttp', idSetFromHttp);
        sessionStorage.setItem('famCodeSetFromHttp', famCodeSetFromHttp);

        processFormDataAction(successClass, dbHttpResult, notificationId);

        if (redirect) {
            setTimeout(() => {
                window.location.assign(redirect);
            }, 2000);
        }

        // formData.clearHtml();
    } catch (error) {

        const errorClass = getNotificationClassByCSS(css, 'red');

        const errorMessage = error ? .response ? .data ? .message ? ? error ? .message ? .message ? ? error ? .message;

        // Process the form data for error
        processFormDataAction(errorClass, errorMessage, notificationId);


        // Handle specific error cases
        // if (error.response.data.message === "We do not recognise what you are doing") {
        //   window.location.assign('/login');
        // }
    }
};

/**
 * Process form data action.
 * @param {string} cssClass - The CSS class for the notification.
 * @param {string} message - The notification message.
 */
const processFormDataAction = (cssClass, message, formNotificationId) => {
    const notificationElement = id(formNotificationId);
    if (notificationElement) {
        notificationElement.style.display = 'block';
        notificationElement.classList.add(cssClass);
        id('error').scrollIntoView({ behavior: 'smooth' });
        id('error').innerHTML = message;
        id('setLoader').classList.remove('loader');
    }
};

/**
 * Get the notification class based on the CSS framework.
 * @param {string|null} css - The CSS framework to use for notification styling.
 * @param {string} status - The status of the notification ('green' or 'red').
 * @returns {string} - The corresponding CSS class.
 */
const getNotificationClassByCSS = (css, status) => {
    switch (css) {
        case 'W3css':
            return status === 'green' ? 'w3-green' : 'w3-red';
        case 'bulma':
            return status === 'green' ? 'is-success' : 'is-danger';
        case 'bootstrap':
            return status === 'green' ? 'bg-success' : 'bg-danger';
        default:
            return status === 'green' ? 'bg-success' : 'bg-danger';
    }
};


/**
 * 
 * @param { the url you want to get} URL 
 * @returns 
 // now we can use that data from the outside!
axiosTest()
    .then(data => {
        response.json({ message: 'Request received!', data })
    })
    .catch(err => console.log(err))
 */

export const getApiData = async(URL, token = null) => {
    try {

        const config = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            },
        }

        const fetch = await axios.get(URL, config)
        return fetch.data


    } catch (error) {

        return error;

    }


}

export const getMultipleApiData = async(url1, url2, token = null) => {
    try {

        const config = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            },
        }



        const fetch = await axios.all([
            axios.get(url1, config),
            axios.get(url2, config)
        ])
        return fetch

    } catch (error) {

        return error;

    }


}

/**
 * 
 * @param { name} cname 
 * @param {* value} cvalue 
 * @param {* no of days 365} exdays 
 */
export const setCookie = (cname, cvalue, exdays) => {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

export const getCookie = (cname) => {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

export const checkCookie = () => {
    var user = getCookie("username");
    if (user != "") {
        alert("Welcome again " + user);
    } else {
        user = prompt("Please enter your name:", "");
        if (user != "" && user != null) {
            setCookie("username", user, 365);
        }
    }
}