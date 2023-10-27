"use strict";
// import { getEnvironmentVariable as env} from 'environment-variable-reader'
import { id, showError } from "../global";
import { removeDiv, createAndAppendElement } from './helper/general'
import { renderHtmlFamily } from './html/kids_Sibling'

// let childrenOnchangeValue = 0;
// let childrenOnchangeValue = 0;

export const show = (kids_or_sib, event) => {
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

    kidInput.addEventListener('change', (event) => show('kids', event));
    sibInput.addEventListener('change', (event) => show('siblings', event));

}

onChangeKidAndSiblings()


