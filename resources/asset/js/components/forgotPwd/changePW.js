import ('../helper/general').then(res => res.matchInput('password_id', 'confirm_password_id', 'changePasswordErr'))

import ('../global').then(response => {
    // Module imported from '../global'

    // Hide the loader element initially
    const loaderElement = response.id('setLoader');
    // loaderElement.style.display = 'none';

    // Function to handle the submission of change password form
    const submitChangePW = async(e) => {
        e.preventDefault();
        try {

            const password = response.id('password_id').value;

            // Remove any previous error notifications
            const changePasswordNotificationElement = response.id('changePassword_notification');
            changePasswordNotificationElement.classList.remove('is-danger');
            response.id('error').innerHTML = '';

            if (password) {
                // Display the loader element
                loaderElement.style.display = 'block';
                response.id('loader').classList.add('loader');

                // Dynamically import the '../helper/http' module
                // Perform the HTTP request to submit the change password form
                const { postFormData } = await import('../helper/http');
                await postFormData('/login/changePW', 'changePassword', '/login');

            }
        } catch (error) {
            // Show error using the 'showError' function on the 'response' object
            response.showError(error)
        } finally {
            // Hide the loader element regardless of success or error
            loaderElement.style.display = 'none';
        }
    }

    // Add a click event listener to the element with id 'submit'
    response.id('submit').addEventListener('click', submitChangePW)

})

const currentPs = document.getElementById("password_id")
const emailID = document.getElementById("email_id")
const confirmPs = document.getElementById("confirm_password_id")


currentPs.setAttribute('autocomplete', 'new-password')
confirmPs.setAttribute('autocomplete', 'new-password')
emailID.setAttribute('type', 'hidden')
// emailLabel.style.display = "none"