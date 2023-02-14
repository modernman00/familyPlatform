import { id } from "../global"

/**
 * 
 * @param {*} parentId - id of the element string
 * @param {*} msg - messages to pass - string
 */
const showMsg = (parentId, msg = "Please, leave blank if not available") => {
    id(parentId).innerHTML = msg
}

showMsg('fatherMobile_help')
showMsg('motherMobile_help')
showMsg('motherEmail_help')
showMsg('fatherEmail_help')
showMsg('mobile_help', "Nigeria: 2348036517179, UK: 447871717809")
showMsg('password_help', 'Must be 8-20 characters long.')

const lastName = id('lastName_id').value = "OLAOGUN"