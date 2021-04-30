"use strict";
import { id, log } from "../../global";


const renderHtmlFamily = (family, no) => {

    if (no) {

        const kids_sib = (family == "addChildren") ? "kid" : "siblings"

    

        return ` <div class="field is-horizontal">
            <div class="field ">
        
            <div class="control is-expanded has-icons-left">
            <input type="text" placeholder = "Enter child's full name - ${no}" name =${kids_sib}_name${no} class="input input is-medium" id="${kids_sib}_name${no}">
            </div></div>
            <div class="field ">
            <div class="control is-expanded has-icons-left">
           <input type="email" placeholder = "Enter child's email - ${no}" name=${kids_sib}_email${no} class="input input is-medium" id="${kids_sib}_email${no}">
           </div>
        </div></div><br>`
    }

    id('allMembers').insertAdjacentHTML('beforeend', html)

}

export const show = (kids_or_sib) => {
    try {
        // what was picked or selected
        const value = event.target.value;

        alert(value)

         const addDiv = (kids_or_sib == "kids") ? "addChildren" : "addSiblings"

        id(addDiv).classList.remove('.onChangeKidAndSiblings')

        if (value) {

            // use the loop to generate the number of input
            for (let i = 0; i < value; i++) {

                const no = i + 1
                const msg = (no > 1) ? "Please, enter their names and emails" : "Please, enter your child name and email"

                const getSelectHelp = id(`${kids_or_sib}_help`)
                getSelectHelp.innerHTML = msg
                getSelectHelp.style.fontSize = '1rem'

               

                id(addDiv).classList.add("onChangeKidAndSiblings")

                const html = renderHtmlFamily(addDiv, no)

               id(addDiv).insertAdjacentHTML('afterend', html)

            }
        }
    } catch (error) {
        console.log(error.message)
    }
}

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
const onChangeKidAndSiblings = () => {

    const sibInput = id("noSiblings_id");

    const kidInput = id("kids_id")

    kidInput.addEventListener('change', () => show('kids'))
    sibInput.addEventListener('change', () => show('noSiblings'))
}

onChangeKidAndSiblings()


