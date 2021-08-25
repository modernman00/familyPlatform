"use strict";
import { id,  showError } from "../global"
import FormHelper from '../FormHelper';
import { postFormData } from "../helper/http"

const formInput = document.querySelectorAll('.eventModalForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);

formData.clearError()

const displayNone = () => id('id_event_modal').style.display = 'none'

id('cancelModal').addEventListener('click', displayNone)



const process = (e) => {
    try {

        e.preventDefault();
        id('eventModalForm_notification').classList.remove('w3-red') // remove the danger class from the notification
        id('error').innerHTML = ""
        formData.massValidate();
        // log(formData.error)
        if (formData.error.length <= 0) {
            // initiate the api
            //  qSel('.submitEventModal').type = 'submit'
            id('loader').classList.add('loader') // start the loader element
            postFormData("/member/profilePage", 'eventModalForm', null, "w3css")
            displayNone();

            // window.location.replace("/member/profilePage")
        } else {
            alert('The form cannot be submitted. Please check the errors')
            formData.clearError()
        }

    } catch (error) {
        showError(error)
    }

}

id('submitEventModal').addEventListener('click', process)





