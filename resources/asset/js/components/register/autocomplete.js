"use strict";
import { id, showError } from '../global'
import { checkBox } from '../helper/general'
import { getAllData } from '../api/index'
import { autocomplete } from '../helper/autocomplete'
import axios from 'axios'


const getData = getAllData()
let firstNameData = []
let fatherName = []
let mobile = []
let motherName = []

getData.then(el =>
    el.map(element => {
        firstNameData.push(element.firstName)
        fatherName.push(element.fatherName)
        motherName.push(element.motherName)
        mobile.push(element.mobile)
    })
)

const lastAutoComplete = id('firstName_id')
const fatherAutoComplete = id('fatherName_id')
const motherAutoComplete = id('motherName_id')

lastAutoComplete.setAttribute('autocomplete', 'off')
fatherAutoComplete.setAttribute('autocomplete', 'off')
motherAutoComplete.setAttribute('autocomplete', 'off')

// AUTOCOMPLETE
autocomplete(lastAutoComplete, firstNameData)
autocomplete(fatherAutoComplete, fatherName)
autocomplete(motherAutoComplete, motherName)

// CHECK THE MOBILE OF MOTHER AND FATHER

const setInput = (element, name, value) => {


    const sex = (name === "father") ? "him" : "her"
    const genId = id(`${name}Mobile_error`)
    genId.style.display = "block"
    if (value === element) {
        genId.innerHTML = `Great news that your ${name} is already on the platform`
    } else {
        genId.innerHTML = `<h4><i>Your ${name} is not on the platform. Do you want us to send ${sex} a text to register to the platform</i>?</h4>` + checkBox(name)

        const processRadio = () => {
            let surname = id('lastName_id').value

            const postObj = {
                mobile: value,
                viewPath: "msg/contactNewMember",
                data: { 
                    email: id(`${name}Email_id`).value, 
                    name: id('fatherName_id').value,
                    surname: surname
                    },
                subject: `Please, register to join the ${surname} family Network`,
            }
            axios.post('/register/contactNewMember', postObj)
                .then((response) => {
                    console.log(response.data);
                    const mobileHelp = id(`${name}Mobile_help`)
                    mobileHelp.innerHTML=response.data.message
                    mobileHelp.style.display = "block"
                    id(`${name}Mobile_error`).innerHTML=""
                })
                .catch((error) => {
                    showError(error);
                });
        }
        id(`${name}Yes`).addEventListener('click', processRadio)
        id(`${name}No`).addEventListener('click', () => genId.style.display = "none")
    }
}

/**
 * @param {the idInput to check} the input id 
 * @param {the array to check} data 
 * @param {this should either be the mother oor father} who 
 */

const mobileFilter = (event, name) => {
    const value = event.target.value;
    return mobile.filter(el => {
        const element = el
        setInput(element, name, value)
    })
}
const fatherMobile = (event) => {
    const setName = "father"
    mobileFilter(event, setName)  
}

const motherMobile = (event) => {
    const setName = "mother"
     mobileFilter(event, setName)  
}

const spouseMobile = (event) => {
    const setName = "spouse"
     mobileFilter(event, setName)  
}


id('fatherMobile_id').addEventListener('keyup', fatherMobile)
id('motherMobile_id').addEventListener('keyup', motherMobile)
id('spouseMobile_id').addEventListener('keyup', spouseMobile)






