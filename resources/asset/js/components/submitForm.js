"use strict";
import FormHelper from './FormHelper';
import Swal from 'sweetalert2';
import { id, log, showError } from '../global';
import { dataToCheckRegister } from '../data/dataToCheck';

const formInput = document.querySelectorAll('.eventModalForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const process = () => {
	// clear error from the form
	formData.clearError()
	// set the maxlength, check the length of the value, raise error

	if (dataToCheckRegister.maxLength.id) {
		formData.realTimeCheckLen(
			dataToCheckRegister.maxLength.id,
			dataToCheckRegister.maxLength.max
		)
	}


}

process()

id('submitEventModal').addEventListener('click', () => {
	try {


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
			Swal.fire({
				icon: 'error',
				title: 'Validation Error',
				text: 'The form cannot be submitted. Please check the errors.',
				timer: 3000,
				showConfirmButton: false
			});

			process()

		}


	} catch (e) {
		showError(e)
	}
})

