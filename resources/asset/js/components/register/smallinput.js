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
const famCodeButton = "<button type='button' class='js-modal-trigger' data-target='modal-familyCode' id='triggerFamCode'>here</button>"

showMsg('fatherMobile_help')
showMsg('motherMobile_help')
showMsg('motherEmail_help')
showMsg('fatherEmail_help')
showMsg('mobile_help', "Nigeria: 2348036517179, UK: 447871717809")
showMsg('password_help', 'Must be 8-20 characters long.')
showMsg(`famCode_help`, `Ask a family member who already registered for the code if none then create one for your family ${famCodeButton}`)

// const lastName = id('lastName_id').value = "OLAOGUN"

// TRIGGER THE MODAL

// Functions to open and close a modal
function openModal($el) {
    $el.classList.add('is-active');
}

function closeModal($el) {
    $el.classList.remove('is-active');
}



const trigger = id('triggerFamCode')
const close = id('modal-close-code')
const modalClass = id('modal-familyCode')

trigger.addEventListener('click', () => {
    openModal(modalClass);
});

close.addEventListener('click', () => {
    closeModal(modalClass);
});