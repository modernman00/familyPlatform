import { id } from "../global"


export const processFormDataAction = (addClass, serverResponse) => {
    // display the success information for 10sec
    notificationDiv.style.display = "block" // unblock the notification
    notificationDiv.classList.add(addClass) // add the success class
    notificationMsg.innerHTML = serverResponse.message // error element
    id('loader').classList.remove('loader') // remove loader
    notificationDiv.scrollIntoView({ behavior: 'smooth', block: 'start' })
}