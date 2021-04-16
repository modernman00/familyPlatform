"use strict";
import FormHelper from '../FormHelper';
import { id, log, showError } from '../../global';
import {dataToCheckRegister} from '../../data/dataToCheck';

const formInput = document.querySelectorAll('.register');
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


	//real time check 

	formData.realTimeServer('spouseMobile',
		`/search?attribute=spouseMobile&subject=spouse&hint`,
		'spouseMobile_error')

	formData.realTimeServer('fatherMobile',
		'/search?attribute=fatherMobile&subject=father&hint', 'fatherMobile_error')

	formData.realTimeServer('motherMobile',
		'/search?attribute=motherMobile&subject=mother&hint', 'motherMobile_error')


	// check if password matches real time
	formData.matchInput(dataToCheckRegister.password.pwd,
		dataToCheckRegister.password.pwd2,
		// dataToCheckRegister.password.err
	);

	// check if they have a father yes
	// formData.isChecked(dataToCheckRegister.familyCheck.father[0],
	// 	dataToCheckRegister.familyCheck.father[1],
	// 	'fatherEmail_error'
	// )

	// // check if they have a mother yes
	// formData.isChecked(dataToCheckRegister.familyCheck.mother[0],
	// 	dataToCheckRegister.familyCheck.mother[1],
	// 	'motherEmail_error'
	// )

	// // check if they have a spouse yes
	// formData.isChecked(dataToCheckRegister.familyCheck.spouse[0],
	// 	dataToCheckRegister.familyCheck.spouse[1],
	// 	'spouseEmail_error'
	// )
}

process()

id('submit').addEventListener('click', () => {
	try {
		if (id('checkbox').checked) {
			formData.emailVal() // sanitise email
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

		} else {
			alert('To continue, you need to agree to the Olaoguns handling your information as outlined in our privacy policy')
		}


	} catch (e) {
		showError(e)
	}
})





