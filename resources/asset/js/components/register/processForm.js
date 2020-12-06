import FormHelper from '../FormHelper';
import { id, log } from '../../global';
import { dataToCheck } from "../../data/dataToCheck";

const formInput = document.querySelectorAll('.register');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const process = () => {

	// clear error from the form
	formData.clearError()

	// set the maxlength, check the length of the value, raise error
	formData.realTimeCheckLen(
		dataToCheck.maxLength.id,
		dataToCheck.maxLength.max
	);


	//real time check 

	formData.realTimeServer('spouseEmail', `/search?attribute=spouseEmail&subject=spouse&hint`, 'spouseEmail_error')

	formData.realTimeServer('fatherEmail', '/search?attribute=fatherEmail&attribute2=email&subject=father&hint', 'fatherEmail_error')

	formData.realTimeServer('motherEmail', '/search?attribute=motherEmail&subject=mother&hint', 'motherEmail_error')


	// check if password matches real time
	formData.matchInput(dataToCheck.password.pwd,
		dataToCheck.password.pwd2,
		dataToCheck.password.err
	);

	// check if they have a father yes
	// formData.isChecked(dataToCheck.familyCheck.father[0],
	// 	dataToCheck.familyCheck.father[1],
	// 	'fatherEmail_error'
	// )

	// // check if they have a mother yes
	// formData.isChecked(dataToCheck.familyCheck.mother[0],
	// 	dataToCheck.familyCheck.mother[1],
	// 	'motherEmail_error'
	// )

	// // check if they have a spouse yes
	// formData.isChecked(dataToCheck.familyCheck.spouse[0],
	// 	dataToCheck.familyCheck.spouse[1],
	// 	'spouseEmail_error'
	// )
}

process()

id('submit').addEventListener('click', () => {
	try {

		if (id('checkbox').checked) {

			formData.emailVal() // sanitise email

			formData.massValidate();  // validate and sanitise data
			log(formData.error)
			if (formData.error.length <= 0) {

				id('submit').type = 'submit'
				console.log('submitted')

			} else {

				console.log(formData.error)
				alert('The form cannot be submitted. Please check the errors')

				process()

			}

		} else {

			alert('To continue, you need to agree to the Olaoguns handling your information as outlined in our privacy policy')
		}


	} catch (e) {

		console.log(e)

	}
})


