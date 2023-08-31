"use strict";
import { id, showError } from "../global"

// import { postFormData } from "../helper/http"

try{

    // NEW MESSAGE MODAL
    const showModal = ()=> id('id01').style.display = 'block'
    
    // CREATE EVENT MODAL
    const showEvent = () => id('id_event_modal').style.display = 'block'

//EVENT ACTION
    id('createEvent').addEventListener('click', showEvent)
    id('postMsg').addEventListener('click', showModal)


// handle post message

} catch (e) {
    showError(e)
}