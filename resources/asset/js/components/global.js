import axios from "axios"

export const id = (id) => document.getElementById(id)
export const idValue = (id) => id(id).value
export const idInnerHTML = (id) => id(id).innerHTML
export const warningSign = "\u26A0"; // danger warning sign

export const qSel = (name) => document.querySelector(name)
export const qSelAll = (name) => document.querySelectorAll(name)
export const qSelValue = (name) => qSel(name).value
export const qSelInnerHTML = (name) => qSel(name).innerHTML

export const log = (id, identifier = null) => {
    console.log(' start' + "  " + identifier)
    console.log(id)
    console.log(' end' + "  " + identifier)
}
export const write = (input) => document.write(input)

export const hideElement = (elementId) => {
    id(elementId).style.display = "none";
};

export const showElement = (elementId) => {
    id(elementId).style.display = "block";
};

export const manipulateAttribute = (idName, removeOrSet, attributeType, nameValue = null) => {

    if (removeOrSet === "remove") {
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
export const formReset = (formId) => {
    const form = id(formId);
    if (!form) {
        console.warn(`Form with ID "${formId}" not found.`);
        return;
    }

    // Reset form fields
    form.reset();

    // Clear validation messages
    form.querySelectorAll('.is-invalid, .invalid-feedback').forEach(el => {
        el.classList.remove('is-invalid');
        if (el.classList.contains('invalid-feedback')) {
            el.textContent = '';
        }
    });


    // Clear image previews
    form.querySelectorAll('.preview-img').forEach(img => {
        img.src = '';
        img.style.display = 'none';
    });

    // Clear custom inputs (e.g., emoji pickers, rich text)
    form.querySelectorAll('[data-custom-input]').forEach(el => {
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
        } else if (file.type.includes("exe") || file.type.includes("sh") || file.type.includes("bat") || file.type.includes("js")) {
            alert(`File ${file.name} is of an unsupported type and cannot be uploaded.`);
            fileInput.value = ''; // Clear the input
            return false; // Validation failed
        } else if (!file.type.startsWith("image/") && !file.type.startsWith("video/") && !file.type.startsWith("audio/") && !file.type === "application/pdf" && !file.type === "application/msword" && !file.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
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
    log(e.fileName, " ERROR FILENAME") // "Scratchpad/1"
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

/**
* ----------------------------------------------------------------
* Reusable Image Modal Function
* ----------------------------------------------------------------
* This function finds all images with the specified selector
* and attaches a click event to show them in a modal.
*
* @param {string} selector - The CSS selector for the images you want to be zoomable (e.g., '.zoomable-image').
* @param {string} modalId - The ID of the modal element (e.g., 'imageModal').
* @param {string} modalImageId - The ID of the image element inside the modal (e.g., 'modalImage').
* @param {string} modalCloseId - The ID of the close button inside the modal (e.g., 'imageModalClose').
* @param {string} imgSrc - The source URL of the image to display in the modal.
* @param {string} imgAlt - The alt text for the image to display in the modal.
* ---------------------------------------------------------------- 
*/
export const initializeImageModal = (selector, clickedImageIndex, modalId, modalImageId, modalCloseId) => {
    // Get references to the modal elements
    // Global variables to manage modal state
    let currentImages = [];
    let currentImageIndex = 0;
    const modal = document.getElementById(modalId);
    const modalImage = document.getElementById(modalImageId);
    const closeModal = document.getElementById(modalCloseId);
    const prevButton = document.getElementById('prevButton');
    const nextButton = document.getElementById('nextButton');


    // Find all images that match the selector
    const images = document.querySelectorAll(selector);

    log(images[images.length - 1].src, " IMAGES")


    // Guard clause: if no modal or images, do nothing.
    if (!modal || !modalImage || !closeModal || images.length === 0) {
        console.warn('Image modal setup failed: Required elements not found.');
        return;
    }

    // Function to hide the modal
    const hideModal = () => {
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
    };


    // Function to show the modal with a specific image
    const showModal = (index) => {
        if (!currentImages || currentImages.length === 0) return;

        if (index < 0) {
            currentImageIndex = currentImages.length - 1; // Loop to the last image
        } else if (index >= currentImages.length) {
            currentImageIndex = 0; // Loop to the first image
        } else {
            currentImageIndex = index;
        }
        modalImage.src = currentImages[currentImageIndex].src;
        modalImage.alt = currentImages[currentImageIndex].alt;
        modal.classList.add("show");
        document.body.style.overflow = "hidden"; // Prevent background scrolling
    };

    // Event listeners for modal controls
    closeModal.addEventListener("click", hideModal);
    prevButton.addEventListener("click", () => showModal(currentImageIndex - 1));
    nextButton.addEventListener("click", () => showModal(currentImageIndex + 1));
    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            hideModal();
        }
    });

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
        if (modal.classList.contains("show")) {
            if (e.key === "Escape") {
                hideModal();
            } else if (e.key === "ArrowLeft") {
                showModal(currentImageIndex - 1);
            } else if (e.key === "ArrowRight") {
                showModal(currentImageIndex + 1);
            }
        }
    });

    currentImages = Array.from(document.querySelectorAll(selector));
    if (currentImages.length > 0) {
        showModal(clickedImageIndex);
    } else {
        console.warn(`No images found for selector: ${selector}`);
    }


}

