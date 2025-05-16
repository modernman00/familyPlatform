import axios from "axios"

export const id = (id) => document.getElementById(id)
export const idValue = (id) => id(id).value
export const idInnerHTML = (id) => id(id).innerHTML
export const warningSign = "\u26A0"; // danger warning sign

export const qSel = (name) => document.querySelector(name)
export const qSelAll = (name) => document.querySelectorAll(name)
export const qSelValue = (name) => qSel(name).value
export const qSelInnerHTML = (name) => qSel(name).innerHTML

export const log = (id, identifier =null) => {
    console.log(' start'+ "  " + identifier)
    console.log(id)
    console.log(' end'+ "  " +identifier)
}
export const write = (input) => document.write(input)

export const hideElement = (elementId) => {
  id(elementId).style.display = "none";
};

export const showElement = (elementId) => {
  id(elementId).style.display = "block";
};

export const manipulateAttribute = (idName, removeOrSet, attributeType, nameValue =null) => {

    if(removeOrSet === "remove") {
           id(idName).removeAttribute(attributeType)
    } else {
        id(idName).setAttribute(attributeType, nameValue)
    }
 
  
}

export const date2String = (date) => new Date().toDateString(date)

export const showError = (e) => {
  
    log(e.message, " ERROR MESSAGE") // "null has no properties"
    log(e.name, " ERROR NAME") // "TypeError"
    log(e.fileName,  " ERROR FILENAME") // "Scratchpad/1"
    log(e.lineNumber, " ERROR LINENUMBER") // 2

    log(e.stack)
}

export const msgException = (errorMessage) => {
  
    throw new Error(errorMessage)
}


/**
 * 
 * @param {*} elementId - element id
 * @param {*} addClass either a success or danger class (green or red)
 * @param {*} message - html message to convey success or failure
 * @param {*} timer - timer for the message to disappear- default is 5 secs
 */
export const showNotification = (elementId, addClass, message, timer = 5000) => {
    // display the success information for 10sec
    id(`${elementId}`).style.display = "block" // unblock the notification
    id(`${elementId}`).classList.add(addClass) // add the success class
    id(`${elementId}`).innerHTML = message // error element
    id('loader').classList.remove('loader') // remove loader

    setTimeout(() => {
        id(`${elementId}`).style.backgroundColor = ""
        id(`${elementId}`).style.color = ""
        id(`${elementId}`).innerHTML = ""
    }, timer)
}

    // Function to check for elements and render if they exist
    export const checkElements = (idOrClass, classString, theFunction = null) => {

        const doesElementExist = (idOrClass === "id") ? id(classString) : qSel(classString)
        // Check if elements exist before calling render function
        if (doesElementExist.length) {
            theFunction(doesElementExist);
        }

    };

     export const checkManyElements = (idOrClass, classString, theFunction = null) => {

        const doesElementExist = (idOrClass === "id") ? id(classString) : qSelAll(classString)
        // Check if elements exist before calling render function
        if (doesElementExist.length > 0) {
            theFunction(doesElementExist);
        }

    };

    const yourId = localStorage.getItem('requesterId');
    const famCode = localStorage.getItem('requesterFamCode');

    // delete notification 
    export const deleteNotification = async (elementId) => {

         // Extract the user ID from the target ID
            const senderId = elementId.replace("deleteNotification", "notificationBar");
          
            const elementData = id(elementId)
            const data = elementData.getAttribute("data-id");

            // change the background of the clicked element 

            const notificationHTML = id(senderId);

            // Make sure required variables are defined before using them
            if (
                typeof yourId === 'undefined' ||
                typeof famCode === 'undefined'
            ) {
                msgException("Required parameters (yourId or famCode) are not defined");
            }

            const url = `/removeNotification/${yourId}/${famCode}/${data}`


            const response = await axios.put(url)


            if (response.data.message === "Notification marked as read") {

                // remove a html element with notificationBar after 2 mins 
                notificationHTML.remove()

                // reduce the notification count as you have deleted the notification

                const newValues = parseInt(sessionStorage.getItem('notificationCount') - 1)
                id('notification_count').innerHTML = newValues;
            } else {
                msgException("Error removing notification" + " " + response.data.message);
            }
    }