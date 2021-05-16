"use strict";
import FormHelper from './FormHelper';
import { id, log, showError } from '../global';
import { dataToCheckRegister } from '../data/dataToCheck';

const formInput = document.querySelectorAll('.eventModalForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const process = () => {
	// clear error from the form
	formData.clearError()
	// set the maxlength, check the length of the value, raise error
	formData.realTimeCheckLen(
		dataToCheckRegister.maxLength.id,
		dataToCheckRegister.maxLength.max
	);

}

process()

id('submitEventModal').addEventListener('click', () => {
	try {
		alert('inside the submitForm.js')
		
		if (id('email')) {
			formData.emailVal() // sanitise email
		}

		formData.massValidate();  // validate and sanitise data
		//log(formData.error)
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
})

