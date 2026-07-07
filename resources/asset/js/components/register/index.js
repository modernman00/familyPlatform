import "./smallinput"
import "./event"
import "./onChangeKidSibling"
import "./processForm"
import "./mobileNameCheck"
import "./injectCountryCode"
import "./familyCode"
import "./modal"

const currentPs = document.getElementById("password")
const confirmPs = document.getElementById("confirm_password")

if (currentPs) currentPs.setAttribute('autocomplete', 'new-password')
if (confirmPs) confirmPs.setAttribute('autocomplete', 'new-password')