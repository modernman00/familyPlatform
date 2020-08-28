/**
 * 
 * @param {That is the obj name} objData 
 * @param {* this is the div id} htmlId 
 */
export const Input = (objData, htmlId) => {
  objData.map((element) => {
    if (element.type != 'select' && !element.options) {
      const renderHtml = `
      <div class = form-group col-sm-4>
         <label class=' ' for =${element.attribute}> <strong>${element.label.toUpperCase()}</strong></label>
            <input type="${element.type}" class="form-control" id="${element.attribute}" name="${element.attribute}"  placeholder="${element.placeholder}"/>
            <small id ='${element.attribute}_help' class='small'></small><br>
            <small id =${element.attribute}_error class='error'></small>
      </div>
          `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)

    } else if (element.type === 'select') {

      const renderHtml = `
      <div class = form-group col>
      <label for =${element.attribute}> <strong>${element.label.toUpperCase()}</strong> </label>   
          <select class="form-control" id=${element.attribute} name=${element.attribute}>
                <option value= 'select'>select</option> 
                ${element.options.map(el => {
        return `<option>${el}</option>`
      })
        }             
             
          </select>     
             <small id ='${element.attribute}_help' class='small'></small><br>
            <small id =${element.attribute}_error class='error'></small>
           </div>
          `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    }
    else if (element.type === 'radio') {

      const renderHtml = `
      <div class='form-group col'>
         <label for =${element.attribute}> ${element.label.toUpperCase()}: </label> 

      <div class = 'form-check form-check-inline'>
      ${element.options.map(el => {
        return `
       <input class='form-check-input' type='radio' name=${element.attribute} id=${element.attribute} value=${el}>
        <label class= 'form-check-label' > ${el}
      </label>`
      })
      } 
      </div>  
        <small id ='${element.attribute}_help' class='small'></small><br>
            <small id =${element.attribute}_error class='error'></small>
      </div>
     
      `
         document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    }

  })

}



export const Select = (arg, htmlId) => {
  arg.forEach((element) => {
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



