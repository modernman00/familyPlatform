"use strict";
import FormHelper from '../FormHelper';
import { id, log, showError, qSel } from '../global'
import { Login } from "../dataToCheck";
import { postFormData } from "../helper/http"
import { showPassword } from "../helper/security"

// block the setLoader div

id("setLoader").style.display = "none";

const formInput = document.querySelectorAll('.loginNow');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);

const process = () => {
	//clear error from the form
	formData.clearError()
	// set the maxlength, check the length of the value, raise error
	formData.realTimeCheckLen(
		Login.maxLength.id,
		Login.maxLength.max
	);
}

process()

const LoginSubmission = (e) => {
	try {
		e.preventDefault();
		id('loginNow_notification').classList.remove('is-danger') // remove the danger class from the notification
		id('error').innerHTML = "" // empty the error element

		// if (id('checkbox').checked) {
			id("setLoader").focus(); // focus on the loader element
			formData.emailVal() // sanitise email
			formData.massValidate();  // validate and sanitise data
			if (formData.error.length == 0) {
				// display the success information for 10sec
				id('setLoader').style.display = "block" // unblock the div block at the global.js
				id('loader').classList.add('loader') // start the loader element
				localStorage.setItem('redirect', '/member/ProfilePage')
				postFormData("/login", "loginNow", "/login/code")

			} else {
				alert('The form cannot be submitted. Please check the errors')
				process()
			}
		// } 
		// else {
		// 	alert('To continue, you need to agree to the our privacy policy')
		// }
	} catch (err) {
		showError(err)
	}
}

id('submit').addEventListener('click', LoginSubmission)
id("showPassword_id").addEventListener('click', () => showPassword('password_id'))





