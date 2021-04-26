"use strict";
import { id} from "./../../../global";

const show = (e) => {
    try {
        // what was picked or selected
        const kidsNo = e.target.value;

        // use the loop to generate the number of input
        for (let i = 0; i < kidsNo; i++) {

            const no = i + 1
            const msg = (no > 1) ? "Please, enter their names and emails" : "Please, enter your child name and email"

            const getSelectHelp = id('kids_help')
            getSelectHelp.innerHTML = msg
            getSelectHelp.style.fontSize = '1rem'

            const addKids = ` <div class="field is-horizontal">
            <div class="field ">
        
            <div class="control is-expanded has-icons-left">
            <input type="text" placeholder = "Enter child's full name - ${no}" name =kid_name${no} class="input input is-medium" id="kid_name${no}">
            </div></div>
            <div class="field ">
            <div class="control is-expanded has-icons-left">
           <input type="email" placeholder = "Enter child's email - ${no}" name=kid_email${no} class="input input is-medium" id="kid_email${no}">
           </div>
        </div></div><br>`

            const insertedContent = document.querySelector(`.kid${no}`);
            if (insertedContent) {
                insertedContent.parentNode.removeChild(insertedContent);
            }

            id('addChildren').insertAdjacentHTML('afterend', addKids)

        }
    } catch (error) {
        console.log(error.message)
    }
}
// this is to activate the onchange event
id('kids_id').addEventListener('change', show)


