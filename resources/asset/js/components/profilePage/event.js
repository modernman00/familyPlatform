"use strict";
import { id, log } from "../global"
import FormHelper from '../FormHelper';
import { postFormData } from "../helper/http"


try {

    const formInput = document.querySelectorAll('.eventModalForm');
    const formInputArr = Array.from(formInput);
    const formData = new FormHelper(formInputArr);

    formData.clearError()

    const displayNone = () => id('id_event_modal').style.display = 'none'

    id('cancel').addEventListener('click', displayNone)

    const process = () => {

        formData.massValidate();
        if (formData.error.length <= 0) {
            // initiate the api
            id('submitEventModal').type = 'submit'
            return postFormData("/member/createEvent", 'eventModalForm')
        }

    }

    id('submitEventModal').addEventListener('click', process)

} catch (e) {

    log(e.message)

}

