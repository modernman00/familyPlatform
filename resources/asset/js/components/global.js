export const id = (id) => document.getElementById(id)
export const idValue = (id) => id(id).value
export const idInnerHTML = (id) => id(id).innerHTML

export const qSel = (name) => document.querySelector(name)
export const qSelValue = (name) => qSel(name).value
export const qSelInnerHTML = (name) => qSel(name).innerHTML

export const log = (id) => console.log(id)
export const write = (input) => document.write(input)

export const date2String = (date) => new Date().toDateString(date)

export const showError = (e) => {
    // console.log(e instanceof TypeError) // true
    // console.log(e.message) // "null has no properties"
    // console.log(e.name) // "TypeError"
    // console.log(e.fileName) // "Scratchpad/1"
    // console.log(e.lineNumber) // 2
    // console.log(e.columnNumber) // 2
    // console.log(e.stack)
}

/**
 * 
 * @param {*} elementId - element id
 * @param {*} addClass either a success or danger class (green or red)
 * @param {*} message - html message to convey success or failure
 * @param {*} timer - timer for the message to disappear- default is 5 secs
 */
export const showNotification = (elementId, addClass, message, timer = 5000) => {
    // display the success information for 10sec
    id(`${elementId}`).style.display = "block" // unblock the notification
    id(`${elementId}`).classList.add(addClass) // add the success class
    id(`${elementId}`).innerHTML = message // error element
    id('loader').classList.remove('loader') // remove loader

    setTimeout(() => {
        id(`${elementId}`).style.backgroundColor = ""
        id(`${elementId}`).style.color = ""
        id(`${elementId}`).innerHTML = ""

    }, timer)
}