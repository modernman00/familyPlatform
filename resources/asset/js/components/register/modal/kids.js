import { id, idValue, log } from "./../../../global";
import { Input } from "../../formBuilder";
import { idInnerHTML } from '../../../global';


const show = (e) => {
    const kidsNo = e.target.value;
    // I build a hidden input that will auto click on select change to trigger the modal 

    // id('kids_help').innerHTML = `<input type ='hidden' id='but' class = 'btn btn-primary'  data-toggle="modal" data-target="#kidsModal">`;

    // id('but').click() // it automatically clicks the button

    // use the loop to generate the number of input
    for (let i = 0; i < kidsNo; i++) {



        const no = i + 1
        const kids = [
            {
                label: ["name",  "email"],
                type: 'combined',
                attribute: [`childName-${no}`, `childEmail-${no}`],
                inputType: ['text', 'email'],
                placeholder: ['child\'s name', 'email'],
                options: ''
            }

        ]

        id('kids_help').innerHTML = "Please, enter your child(ren)'s name(s) and email(s)"

        const addKids = `
            <div class="form-group row">
                <label for="childName-${no}" class="col-sm-2 col-form-label">Child Name-${no}</label>
                <div class="col-sm-10">
                <input type="text" placeholder = "Enter child's name - ${no}" name =childName-${no} class="form-control" id="childName-${no}">
                </div>
            </div>
            <div class="form-group row">
                <label for="childEmail-${no}" class="col-sm-2 col-form-label">Child Email-${no}</label>
                <div class="col-sm-10">
                <input type="email" placeholder = "Enter child's email - ${no}" name=childEmail-${no} class="form-control" id="childEmail-${no}">
                </div>
            </div> 
        `

        if (!id(`childName-${no}`) || !id(`childEmail-${no}`) )      
            id('kids_div').insertAdjacentHTML('afterend', addKids)
      
            
    }

}
// this is to activate the onchange event
id('kids').addEventListener('change', show)

const test = () => {
    alert('it worked')
}

id('childName-1').addEventListener('keyup', test)
