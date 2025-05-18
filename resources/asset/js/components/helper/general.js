"use strict";
import { id, qSelAll } from '../global';
import autocomplete from 'autocompleter';
import FormHelper from '../FormHelper';


export const loaderIconBootstrap = () => {

    return `<div class="spinner-grow text-primary" role="status">
        <span class="sr-only">Loading...</span>
        </div>`
}

export const loaderIcon = () => {

    return `<div class="loader"></div>`
}

export const loaderIconBulma = () => {

    return `<div class="is-loading"></div>`
}

export const removeDiv = (div_id) => {
    const div = document.getElementById(div_id)
    if (div) {
        return div.remove()
    }

}



export const createAndAppendElement = (elementType, setId, parent, setClass = null) => {
    const newDiv = document.createElement(elementType);
    newDiv.setAttribute('id', setId)
    newDiv.setAttribute('class', `field ${setClass}`)
    const parentDiv = id(parent)
    return parentDiv.appendChild(newDiv)
}

/**
 * 
 * @param {the id of the input} inputId 
 * @param {the api data or array data} data 
 * @param { filterby is the data.filterby }
 */
export const autoCompleter = (inputId, data) => {
    autocomplete({
        input: inputId,
        fetch: function (text, update) {
            text = text.toLowerCase();
            // you can also use AJAX requests instead of preloaded data
            const suggestions = data.filter(n => n.firstName.toLowerCase().startsWith(text))
            update(suggestions);
        },
        onSelect: function (item) {
            input.value = item.firstName;
        }

    })
}

export const distinctValue = (array) => {
    return [...new Set(array)]
}

export const checkBox = (subject) => {
    return `<div class="control"> 
        <label class="radio">
          <input type="radio" name="send${subject}" value="yes" id=${subject}Yes > Yes 
        </label>
        <label class="radio"> 
          <input type="radio" name="send${subject}" value="no" id=${subject}No checked> No 
        </label>
      </div>`;
}

export const checkBox2 = (subject) => {
    return `<div class="control"> 
        <label class="checkbox">
          <input type="checkbox" name="send${subject}" value="yes" id=${subject}Yes> Yes 
        </label>
        <label class="checkbox"> 
          <input type="checkbox" name="send${subject}" value="no" id=${subject}No> No 
        </label>
      </div>`

}

export const isChecked = (name, fn) => {
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

export const matchRegex = (data) => {
    if (data) {
        if (data != "Not Provided") {
            const regex = /[<?/>]+/g
            const result = data.match(regex)
            if (result === null) return true
        }
    }
}

/**
 * 
 * @param { id of the first element} first 
 * @param {* id of the second element} second 
 * @param {* error id - if error - where to show it} err 
 */
export const matchInput = (first, second, err) => {
    let error, firstInput, secondInput
    error = id(err)
    firstInput = id(first)
    secondInput = id(second)

    secondInput.addEventListener('keyup', () => {

        if (secondInput.value !== firstInput.value) {
            error.innerHTML = 'Your passwords do not match'
            error.style.color = "red"
        } else {
            error.innerHTML = "The password is matched: <i class='fa fa-check' aria-hidden='true'></i>"
            error.style.color = "green"
        }


    })
}

export const toSentenceCase = (str) => {
    return str
        .toLowerCase() // Convert the string to lowercase
        .split(' ')    // Split the string into words
        .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize the first letter of each word
        .join(' ');    // Join the words back into a string
}

export const convertFormData = (formId) => {
    const formInput = qSelAll(formId)
    const formInputArr = Array.from(formInput)
    return new FormHelper(formInputArr)

}



export const showResponse = (theId, message, status) => {
    const responseEl = id(theId)
    const col = status ? 'green' : 'red'

    responseEl.innerHTML = message
    responseEl.style.color = 'green'
    responseEl.style.backgroundColor = col
    responseEl.style.color = 'white';
    setTimeout(() => {
        responseEl.innerHTML = '';
    }, 5000); // Disappear after 5 seconds

}


/**
   *
   * @param {input is the id of the input/ this is an array [as, it, it]} input
   * @param {* this is the max policy and it must be an integer} maxi
   */

export const realTimeCheckLen = (input, maxi) => {
    try {
        for (let i = 0; i < input.length; i++) {
            const theData = id(`${input[i]}_id`);
            if (theData === null || theData === undefined || theData === "") {
                throw new Error(`Element with ID '${input[i]}_id' not found or is empty`);
            }
            const max = maxi[i];
            const error = id(`${input[i]}_error`);
            theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
            theData.addEventListener('keyup', () => {
                error.innerHTML = (theData.value.length > max) ? `You have reached the maximum limit` : "";
                const help = id(`${input[i]}_help`);
                help.style.color = 'red';
                help.style.fontSize = '10px';
                error.style.color = 'red';
                setTimeout(() => {
                    help.style.display = 'none';
                }, 5000);
            });
        }
    } catch (error) {
        console.error(error.message);
    }
}


