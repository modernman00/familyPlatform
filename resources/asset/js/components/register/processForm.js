"use strict";
import FormHelper from '../FormHelper';
import { id, log, showError } from '../global';
import { dataToCheckRegister } from './dataToCheck';

const formInput = document.querySelectorAll('.register');
const formInputArr = Array.from(formInput);
// var formData2 = new FormData(id('register'))
// const formData = new FormHelper(formInputArr);
const formData = new FormHelper(formInputArr);



const process = () => {
	// clear error from the form
	formData.clearError()

	// set the maxlength, check the length of the value, raise error
	formData.realTimeCheckLen(
		dataToCheckRegister.maxLength.id,
		dataToCheckRegister.maxLength.max
	);

	// check if password matches real time
	formData.matchInput(dataToCheckRegister.password.pwd,
		dataToCheckRegister.password.pwd2,
	);

}

process()

const processForm = () => {
	try {
	
		if (id('checkbox').checked) {
			formData.emailVal() 
			formData.massValidate();  

			if (formData.error.length <= 0) {
				id('submit').type = 'submit'
				
			} else {
				log(formData.error)
				alert('The form cannot be submitted. Please check the errors')

				process()

			}

		} else {
			alert('To continue, you need to agree to the our privacy policy')
		}


	} catch (e) {
		showError(e)
	}
}

id('submit').addEventListener('click', processForm)





