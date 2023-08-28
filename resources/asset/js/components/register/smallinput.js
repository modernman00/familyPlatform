import { id } from "../global"

/**
 * 
 * @param {*} Id - id of the element string
 * @param {*} msg - messages to pass - string
 */
const showMsg = (Id, msg = "Please, leave blank if not available") => {
    id(Id).innerHTML = msg
}

// const href = "<a href='/createFamilyCode' target='_blank'>here</a>"
const href = "<button class='js-modal-trigger' data-target='modal-familyCode'>here</button>"

showMsg('fatherMobile_help')
showMsg('motherMobile_help')
showMsg('motherEmail_help')
showMsg('fatherEmail_help')
showMsg('mobile_help', "Nigeria: 2348036517179, UK: 447871717809")
showMsg('password_help', 'Must be 8-20 characters long.')
showMsg(`familyCode_help`, `Ask a family member who already registered for the code if none then create one for your family ${href}`)

const lastName = id('lastName_id').value = "OLAOGUN"