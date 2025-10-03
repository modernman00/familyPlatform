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

/**
 * Resets a form by clearing all input fields, validation messages, 
 * image previews and custom inputs.
 * @param {string} formId - The ID of the form to reset.
 */
export const formReset =(formId)=> {
    const form = id(formId);
    if (!form) return;

    // Reset form fields
    form.reset();

    // Clear validation messages
    form.qSelAll('.is-invalid, .invalid-feedback').forEach(el => {
        el.classList.remove('is-invalid');
        if (el.classList.contains('invalid-feedback')) {
            el.textContent = '';
        }
    });


    // Clear image previews
    form.qSelAll('.preview-img').forEach(img => {
        img.src = '';
        img.style.display = 'none';
    });

    // Clear custom inputs (e.g., emoji pickers, rich text)
    form.qSelAll('[data-custom-input]').forEach(el => {
        el.value = '';
    });
}


export const fileUploadSizeValidation = (fileInputId, maxSizeMB = 3) => {
    const fileInput = id(fileInputId);
    if (!fileInput || !fileInput.files) return true; // No files to validate

    const maxSizeBytes = maxSizeMB * 1024 * 1024;
    for (const file of fileInput.files) {
        if (file.size > maxSizeBytes) {
            alert(`File ${file.name} exceeds the maximum size of ${maxSizeMB}MB.`);
            fileInput.value = ''; // Clear the input
            return false; // Validation failed
        } else if (file.size === 0) {
            alert(`File ${file.name} is empty and cannot be uploaded.`);
            fileInput.value = ''; // Clear the input
            return false; // Validation failed
        } else if(file.type.includes("exe") || file.type.includes("sh") || file.type.includes("bat") || file.type.includes("js")) {
            alert(`File ${file.name} is of an unsupported type and cannot be uploaded.`);
            fileInput.value = ''; // Clear the input
            return false; // Validation failed
        } else if(!file.type.startsWith("image/") && !file.type.startsWith("video/") && !file.type.startsWith("audio/") && !file.type === "application/pdf" && !file.type === "application/msword" && !file.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
            alert(`File ${file.name} is of an unsupported type and cannot be uploaded.`);
            fileInput.value = ''; // Clear the input
            return false; // Validation failed
        }
    }
    return true; // All files are within size limit
};


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
    // id('loader').classList.remove('loader') // remove loader

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

