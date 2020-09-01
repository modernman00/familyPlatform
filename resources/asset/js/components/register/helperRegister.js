import FormHelper from '../FormHelper';
import { id, log } from './../../global';
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


	//check spouse
	formData.realTimeServer('spouse', '/search?attribute=spouse&hint', 'spouse_error')

	formData.realTimeServer('fatherName', '/search?attribute=fatherName&hint', 'fatherName_error')

	formData.realTimeServer('motherName', '/search?attribute=motherName&hint', 'motherName_error')


	// check if password matches real time
	formData.matchInput(dataToCheck.password.pwd,
		dataToCheck.password.pwd2,
		dataToCheck.password.err
	);
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

			alert('To continue, you need to agree to Loaneasy Finance handling your information as outlined in our privacy policy')
		}


	} catch (e) {

		console.log(e)

	}
})


