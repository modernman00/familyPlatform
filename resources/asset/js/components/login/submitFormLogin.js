"use strict";
import FormHelper from '../FormHelper';
import { id, log, showError, qSel } from '../global'
import { dataToCheckLogin } from "../register/dataToCheck";

const formInput = document.querySelectorAll('.login');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const process = () => {
	// clear error from the form
	formData.clearError()
	// set the maxlength, check the length of the value, raise error
	formData.realTimeCheckLen(
		dataToCheckLogin.maxLength.id,
		dataToCheckLogin.maxLength.max
	);
}

	

process()

const processFormSubmission = () => {
	try {
		console.log('it worked')
			formData.emailVal() // sanitise email
			formData.massValidate();  // validate and sanitise data
			log(formData.error)
			log("it worked")
			
			
			if (formData.error.length <= 0) {
				id('submit').type = 'submit'
				//console.log('submitted')
			} else {
				log(formData.error)
				alert('The form cannot be submitted. Please check the errors')

				process()

			}


	} catch (e) {
		showError(e)
	}
}

 const testAlert = () => alert('it worked login')
 
document.querySelector('.button').addEventListener('click', testAlert)




