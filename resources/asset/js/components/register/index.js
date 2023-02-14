import "./smallinput"
import "./event"
import "./onChange"
import "./processForm"
import "./mobileNameCheck"

const currentPs = document.getElementById("password_id")
const confirmPs = document.getElementById("confirm_password_id")
const secretWd = document.getElementById("secretWord_id")

currentPs.setAttribute('autocomplete', 'on')
confirmPs.setAttribute('autocomplete', 'on')
    // secretWd.setAttribute('autocomplete', 'on')