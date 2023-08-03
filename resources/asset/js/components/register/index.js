import "./smallinput"
import "./event"
import "./onChangeKidSibling"
import "./processForm"
import "./mobileNameCheck"
import "./injectCountryCode"
import "./familyCode"

const currentPs = document.getElementById("password_id")
const confirmPs = document.getElementById("confirm_password_id")


currentPs.setAttribute('autocomplete', 'new-password')
confirmPs.setAttribute('autocomplete', 'new-password')
    // secretWd.setAttribute('autocomplete', 'on')