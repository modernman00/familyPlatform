import { id, log } from "../../global"

const maiden = id('motherMaiden_help')
maiden.innerHTML = "Good to identify your family from the mother's side"


const mobile = id('mobile_help')
mobile.innerHTML = "Nigeria: 2348036517179, UK: 447871717809"


const password = id('password_help')
password.innerHTML = 'Must be 8-20 characters long.'

id('spouse').style.display = "none";

id('children2').style.display = "none";

const showSpouse = (e) => {
    if(e.target.value  === "Yes") {
        id('spouse').style.display = "block"
    } else {
        id('spouse').style.display = "none"
    }

}

id('maritalStatus_id').addEventListener('change', showSpouse)







