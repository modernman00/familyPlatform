import "./Login"

const currentPs = document.getElementById("password_id")
const passwordLabel = document.getElementById("showPassword_id")



currentPs.setAttribute('autocomplete', 'current-password')
passwordLabel.setAttribute('aria-label', 'Warning: this will display your password on the screen.')