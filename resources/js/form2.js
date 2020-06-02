// function to get input value
const idValue = (element) => {return document.getElementById(element).value}
const getId = (element) => {return document.getElementById(element);}
// function to return innerHTML
const getInnerHtml = (element, html) => {return document.getElementById(element).innerHTML = html;};

// function to process our form
const showItem = (element, value) => {
    const voalue = idValue(value)
    if (typeof voalue == 'string') {       
        return document.getElementById(element).innerHTML = voalue.toUpperCase()
    } else {
        return document.getElementById(element).innerHTML = voalue
    }
}

let formInput = []
const processForm = () => {
    const name = idValue('name');
    const age = idValue("age");
    const address = idValue("address");
    const gender = idValue("gender");
    const email = idValue("email");
    let no = Math.round() 
   input = {'id': no, 'name': name, 'age': age, 'address': address, 'gender': gender, 'email': email}
   return formInput.push(input)  
}

    const outcome = document.getElementById('submit');
    outcome.addEventListener('click', () =>{
        processForm()
        console.log(formInput)
        localStorage.setItem('formData', formInput)   
    })

    console.log(localStorage.getItem('formData'))

    const showAlert = (element) => {
        alert(element)
    }

    document.querySelector('.myClick').addEventListener('click', ()=>alert('we are super fine Dude'))







