import { id, log } from '../global'
import axios from 'axios'
import axiosRetry from 'axios-retry';

axiosRetry(axios, { retries: 3 });

export const realTimeServer = (input, url, outputId) => {
    let theInput, inputVal, output;
    theInput = id(input)
    output = id(outputId)
    theInput.addEventListener('keyup', () => {
        inputVal = theInput.value

        if (inputVal == 0) {
            output.innerHTML = "";
            return;
        } else {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    output.innerHTML = this.responseText;
                }
            }
            xmlhttp.open("GET", `${url}=${inputVal}`, true);
            xmlhttp.send();
        }
    })
}

/**
 * 
 * @param {the url to post the data to} url 
 * @param {* the id or class of the form} formElement 
 * @param {* the redirect to another page /code or /admin/register} redirect 
 NOTICE:::Make sure you set the notification id as the formId_notification
 */
export const postFormData = async (url, formId, redirect = null, css=null) => {

    let notificationId = `${formId}_notification`
    // the notification function
    const processFormDataAction = (addClass, data) => {
        id(notificationId).style.display = "block" // unblock the notification
        id(notificationId).classList.add(addClass) // add the success class
        id('error').innerHTML = data // error element
        id('loader').classList.remove('loader') // remove loader
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

        var theClass;
         if(css === "W3css") {
             theClass = "w3-green"
        } else if(css  === 'bulma') {
            theClass = "is-success"
        } else {
            theClass = "is-success"
        }
     
        processFormDataAction(theClass, response.data.message)
        // set timer to redirect to the homepage
        if (redirect) {
            setTimeout(() => {
                //window.location.replace(redirect)
                window.location.assign(redirect)
            }, 2000)
        }
        //it clears all the contents
        formData.clearHtml()
        
    }
    ).catch(error => {
        log(error)
           var theClass;
         if(css === "W3css") {
             theClass = "w3-red"
        } else if(css  === 'bulma') {
            theClass = "is-danger"
        }else {
            theClass = "is-danger"
        }
        if (error.response) {
            log(error.response)
            processFormDataAction(theClass, error.response.data.message)
        }
    })

}

/**
 * 
 * @param { the url you want to get} URL 
 * @returns 
 */

export const getApiData = async (URL) => {
    const config = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
    }
    return axios.get(URL, config)
        .then(res => res.data)
        .catch(err => err.response.data)


}