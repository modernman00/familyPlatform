import { id } from '../global'
// import { show } from "./onChange"


id('spouse').style.display = "none";

// id('children2').style.display = "none";

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 

const showSpouse = (e) => {
    if (e.target.value === "Yes") {

        id('spouse').style.display = "block"
    } else {
        id('spouse').style.display = "none"
    }

}

id('maritalStatus_id').addEventListener('change', showSpouse)

id('register_notify_div').style.display = "none"