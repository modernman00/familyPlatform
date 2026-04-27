import { id } from "@modernman00/shared-js-lib"


export const processFormDataAction = (addClass, serverResponse, notificationDiv, notificationMsg) => {
    // display the success information for 10sec
    notificationDiv.style.display = "block" // unblock the notification
    notificationDiv.classList.add(addClass) // add the success class
    notificationMsg.innerHTML = serverResponse.message // error element
    id('loader').classList.remove('loader') // remove loader
    notificationDiv.scrollIntoView({ behavior: 'smooth', block: 'start' })
}