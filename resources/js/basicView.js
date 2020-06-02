export const basicView = (arg, htmlId) => {
   arg.forEach((element)=>{
     if(element.type != 'select'){
        const renderHtml = `<form class="login-form">
             <label> ${element.label}</label>
            <input type="${element.type}" id="${element.attribute}" name="${element.attribute}" placeholder="${element.placeholder}"/>
          </form>`

    document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
  }

    })

}

export const basicViewSelect = (arg, htmlId) => {
   arg.forEach((element)=>{
     const renderHtml = `
     <label>${element.label}</label>
     <select class=form-group>
     <option value>Select</option>
     ${arg.options.forEach(el =>
     `<option value =${el}>${el}</option>`
     )}
     </select>`

    document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)

    })
}



