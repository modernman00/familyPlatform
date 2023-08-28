"use strict";
// import { getEnvironmentVariable as env} from 'environment-variable-reader'
import { id, showError } from "../global";
import { removeDiv, createAndAppendElement } from '../helper/general'

// let childrenOnchangeValue = 0;
// let childrenOnchangeValue = 0;


const renderHtmlFamily = (family, no) => {

    if (no) {

        const kids_sib = (family == "addChildren") ? "kid" : "sibling"


        let optionsHtml = `
      <option value='Choose'>Choose</option>
      <option value='With Spouse'>With Spouse</option>
      <option value='Not With Spouse'>Not With Spouse</option>
    `;

        if (family === "addSiblings") {
            optionsHtml = `
                <option value='Choose'>Choose</option>
                <option value='Same_Mother_Father'>Same Mother & Father</option>
                <option value='Same_Father'>Only Same Father</option>
                <option value='Same_Mother'>Only Same Mother</option>`;
        }

        return `
            <div class="field-body">
                <div class="field">
                    <p class="control is-expanded">
                        <span class="select is-normal is-success is-fullwidth">
                            <select name="${kids_sib}_option${no}" id="${kids_sib}_option${no}">
                                ${optionsHtml}
                            </select>
                        </span>
                    </p>
                </div>

                <div class="field">
                    <p class="control is-expanded">
                        <input type="text" placeholder = "Enter ${kids_sib}'s full name - ${no}"  name =${kids_sib}_name${no} class="input input is-normal " id="${kids_sib}_name${no}">
                    </p>
                </div>          

                <div class="field">
                    <p class="control is-expanded has-icons-left">
                        <input type="email" placeholder = "Enter ${kids_sib}'s email - ${no}" value = "Please, provide email address" name=${kids_sib}_email${no} class="input input is-normal " id="${kids_sib}_email${no}">

                        <span class="icon is-small is-left">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </p>
                    <p class="help is-danger" id="${kids_sib}_email${no}_help"></p>
                </div>

            </div>`
    }

}

export const show = (kids_or_sib) => {
    try {
        // what was picked or selected
        const value = event.target.value;

        // childrenOnchangeValue = value;
        const addDiv = (kids_or_sib == "kids") ? "addChildren" : "addSiblings"

        // remove the div 
        removeDiv(addDiv)

        if (value == 0) {
            id(`${kids_or_sib}_help`).innerHTML = "";
        }

        if (value > 0) {
            // create and append the div element 
            const parent = `${kids_or_sib}_div`
            createAndAppendElement('div', addDiv, parent)
                // use the loop to generate the number of input
            for (let i = 0; i < value; i++) {
                const no = i + 1
                const msg = (no > 1) ? "Please, enter their names and emails below" : "Please, enter the name and email below"
                const getSelectHelp = id(`${kids_or_sib}_help`)
                getSelectHelp.innerHTML = msg
                getSelectHelp.style.fontSize = '1rem'
                getSelectHelp.style.color = '#fc2003'
                const html = renderHtmlFamily(addDiv, no)
                id(addDiv).insertAdjacentHTML('afterBegin', html)
            }
        }
    } catch (error) {
        showError(error)
    }
}

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
const onChangeKidAndSiblings = () => {

    const sibInput = id("siblings_id");

    const kidInput = id("kids_id")

    kidInput.addEventListener('change', () => show('kids'))
    sibInput.addEventListener('change', () => show('siblings'))
}

onChangeKidAndSiblings()


