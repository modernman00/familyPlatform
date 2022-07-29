"use strict";
// import { getEnvironmentVariable as env} from 'environment-variable-reader'
import { id, showError } from "../global";
import { removeDiv, createAndAppendElement } from '../helper/general'

// let childrenOnchangeValue = 0;
// let childrenOnchangeValue = 0;


const renderHtmlFamily = (family, no) => {

    if (no) {

        const kids_sib = (family == "addChildren") ? "kid" : "sibling"

        return `
        <div class="field-body">

            <div class="field">
                <p class="control is-expanded has-icons-left">
                <input type="text" placeholder = "Enter ${kids_sib}'s full name - ${no}"  name =${kids_sib}_name${no} class="input input is-medium is-rounded" id="${kids_sib}_name${no}">
                <span class="icon is-small is-left">
                    <i class="fas fa-user"></i>
                </span>
                </p>
               
            </div>

            <div class="field">
                <p class="control is-expanded has-icons-left">
                <input type="email" placeholder = "Enter ${kids_sib}'s email - ${no}" value = "Please, provide email address" name=${kids_sib}_email${no} class="input input is-medium is-rounded" id="${kids_sib}_email${no}">
                <span class="icon is-small is-left">
                <i class="fas fa-envelope"></i>
                </span>
                <span class="icon is-small is-right">
                <i class="fas fa-check"></i>
                </span>
                </p>
                 <p class="help is-danger" id="${kids_sib}_email${no}_help"></p>
           </div>

        </div><br>`
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

        if (value > 0) {


            // create and append the div element 
            const parent = `${kids_or_sib}_div`
            createAndAppendElement('div', addDiv, parent)


            // use the loop to generate the number of input
            for (let i = 0; i < value; i++) {

                const no = i + 1
                const msg = (no > 1) ? "Please, enter their names and emails" : "Please, enter the name and email"

                const getSelectHelp = id(`${kids_or_sib}_help`)
                getSelectHelp.innerHTML = msg
                getSelectHelp.style.fontSize = '1rem'

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

// inject the country code once one of the country is picked

const injectCountryCode = () => {
    id('country_id').addEventListener('change', (e) => {
        let value = e.target.value;
        switch (value) {
            case 'Nigeria':
                id('mobile_id').value = "234";
                break;
            case 'UK':
                id('mobile_id').value = "44";
                break;
            case 'Canada':
            case 'USA':
                id('mobile_id').value = "1";
                break;
            case 'China':
                id('mobile_id').value = "86";
                break;
            default:
                id('mobile_id').value = "";

        }


    })
}

injectCountryCode()