"use strict";
import { id, showError } from "@shared"

// import { postFormData } from "../helper/http"

try {

    // NEW MESSAGE MODAL
    const showModal = () => id('id01').style.display = 'block'

    // CREATE EVENT MODAL
    const showEvent = () => id('id_event_modal').style.display = 'block'

    //EVENT ACTION


const action = () => {

    const createEvent = id('createEvent')
    const postMsg = id('postMsg')

     if (createEvent) {
          createEvent.addEventListener('click', showEvent);
        } else if (postMsg) {
          postMsg.addEventListener('click', showModal)
        } 
}
     
    if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', action);
  }

    // handle post message

} catch (e) {
    showError(e)
}