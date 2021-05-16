"use strict";
import FormHelper from '../FormHelper';
import { id, log, showError } from '../global';
import { dataToCheckRegister } from '../dataToCheck';
import axios from "axios";

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

	// check if password matches real time
	formData.matchInput(dataToCheckRegister.password.pwd,
		dataToCheckRegister.password.pwd2,
	);

	formData.duplicate('firstName_id', 'alias_id')

}

process()

const processFormDataAction = (addClass, resource) => {
	// display the success information for 10sec
		id('register_notify').style.display = "block" // unblock the notification
		id('register_notify').classList.add(addClass) // add the success class
		id('error').innerHTML = resource.data // error element
		id('loader').classList.remove('loader') // remove loader
}

const processFormData = async (url, formElement) => {
	const form = id(formElement)
	let formEntries = new FormData(form)
	formEntries.delete('submit')
	formEntries.delete('checkbox_id')

	const options = {
		xsrfCookieName: 'XSRF-TOKEN',
		xsrfHeaderName: 'X-XSRF-TOKEN',
	}

	await axios.post(url, formEntries, options).then(response => {
		// set timer to redirect to the homepage
		setTimeout(() => {
			window.location = "/"
		}, 10000)

		processFormDataAction('is-success', response)
		// it clears all the contents
		formData.clearHtml();
	}
	).catch(error => {

		processFormDataAction('is-danger', error.response)

	})

}

const processForm = (e) => {
	try {
		e.preventDefault();

		id('register_notify').classList.remove('is-danger') // remove the danger class from the notification
		id('error').innerHTML = "" // empty the error element

		if (id('checkbox').checked) {
			// window.location.hash = '#setLoader';
			id("setLoader").focus(); // focus on the loader element

			formData.emailVal()
			formData.massValidate();

			if (formData.error.length <= 0) {

				id('loader').classList.add('loader') // start the loader element
				return processFormData("/register", 'register') // initiate the api
			} else {

				alert('The form cannot be submitted. Please check the errors')
				process()
			}

		} else {
			alert('To continue, you need to agree to the our privacy policy')
		}

	} catch (event) {
		showError(event)
	}
}

id('submit').addEventListener('click', processForm)





