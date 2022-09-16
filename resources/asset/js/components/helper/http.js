import { id, log } from '../global'
import axios from 'axios'
import axiosRetry from 'axios-retry';
// import Cookies from 'js-cookie'

axiosRetry(axios, { retries: 3 });

/**
 * 
 * @param {the url to post the data to} url 
 * @param {* the id or class of the form} formElement 
 * @param {* the redirect to another page /code or /admin/register} redirect 
 NOTICE:::Make sure you set the notification id as the formId_notification
 */
export const postFormData = async(url, formId, redirect = null, css = null) => {

    let notificationId = `${formId}_notification`

    // the notification function
    const processFormDataAction = (addClass, data) => {

        id(notificationId).style.display = "block" // unblock the notification
        id(notificationId).classList.add(addClass) // add the success class
        id('error').innerHTML = data // error element
        id('loader').classList.remove('loader') // remove loader

    }

    const addClassByCSS = (theCss, status) => {

        if (theCss === "W3css") {
            return (status == 'green') ? "w3-green" : "w3-red"
        } else if (theCss === 'bulma') {
            return (status == 'green') ? "is-success" : "is-danger"
        } else {
            return (status == 'green') ? "is-success" : "is-danger"
        }

    }

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
    await axios.post(url, formEntries, options)
        .then(response => {

            // TO DECIDE ON THE NOTIFICATION
            let theClass = addClassByCSS(css, 'green');

            processFormDataAction(theClass, response.data.message)

            // set timer to redirect to the homepage
            if (redirect) {
                setTimeout(() => {
                    //window.location.replace(redirect)
                    window.location.assign(redirect)
                }, 2000)
            }
            //it clears all the contents
            // formData.clearHtml()
        }).catch(error => {
            log(error.response.data.message)
            let theClass = addClassByCSS(css, 'red');

            if (error.response) {
                // log("we love" + error)
                processFormDataAction(theClass, error.response.data.message)
            }
        })
}

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