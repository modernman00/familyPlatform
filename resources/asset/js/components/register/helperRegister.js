import FormHelper from '../FormHelper';
import { dataToCheck } from "../../data/dataToCheck";

const formInput = document.querySelectorAll('.register');
const formInputArr = Array.from(formInput);
const formData = new FormData(formInputArr);

//const id = x => document.getElementById(x)

try {

const process = () => {
	// inject the help inner html
	formData.injectData(dataToCheck.injectData.id,
		dataToCheck.injectData.comment
	);

	// clear error from the form
	formData.clearError()

	// set the maxlength, check the length of the value, raise error
	formData.realTimeCheckLen(
		dataToCheck.maxLength.id,
		dataToCheck.maxLength.max
	);


	// check email and alert customer
	formData.realTimeServer('email_id', '/search?hint', 'email_error')

	// autofill the username
	formData.duplicate(dataToCheck.duplicate.email,
		dataToCheck.duplicate.username
	);

	// check if password matches real time
	formData.matchInput(dataToCheck.password.pwd,
		dataToCheck.password.pwd2,
		dataToCheck.password.err
	);


	// check if they have a referral code and unhide code
	formData.isChecked(dataToCheck.referral.yes,
		dataToCheck.referral.no,
		dataToCheck.referral.hidden
	)

}

process()


qSel('.submit').addEventListener('click', ()=>{

	if(id('checkbox').checked) {

		formData.emailVal() // sanitise email

		formData.massValidate();

		if (formData.error.length <= 0 ) {

			qSel('.submit').type ='submit'
			console.log('submitted')

		} else {

			console.log(formData.error)
				alert('The form cannot be submitted. Please check the errors')

				process()

		}

	} else {

		alert('To continue, you need to agree to Loaneasy Finance handling your information as outlined in our privacy policy')
	}
})

} catch (e) {

	console.log(e)

}

