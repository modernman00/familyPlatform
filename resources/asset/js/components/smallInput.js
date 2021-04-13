import { id } from "../global"

const maiden = id('motherMaiden_help')
maiden.innerHTML = "Good to identify your family from mum's side"


const mobile = id('mobile_help')
mobile.innerHTML = "Nigeria: 2348036517179, UK: 447871717809"


const password = id('password_help')
password.innerHTML = 'Must be 8-20 characters long.'

id('spouse_div').style.display = "none";

const showSpouse = (e) => {
    if(e.target.value  === "Yes") {
        id('spouse_div').style.display = "block"
    } else {
        id('spouse_div').style.display = "none"
    }

}

id('maritalStatus').addEventListener('change', showSpouse)







