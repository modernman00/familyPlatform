/**
 * 
 * @param {That is the obj name} objData 
 * @param {* this is the div id} htmlId 
 */
export const Input = (objData, htmlId) => {
  objData.map((element) => {
    if (element.type != 'select' && !element.options ) {
      const renderHtml = `
      <div class = 'form-group' id=${element.attribute}_div>
         <label class=' ' for =${element.attribute}> <strong>${element.label.toUpperCase()}</strong></label>
            <input type="${element.type}" class="form-control" id="${element.attribute}" name="${element.attribute}"  placeholder="${element.placeholder}"/>
            <small id ='${element.attribute}_help' class='small'></small>
            <small id =${element.attribute}_error class='error'></small>
      </div>
          `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)

    } else if (element.type === 'select') {
      const renderHtml = `
      <div class = 'form-group' id='${element.attribute}_div'>
      <label for =${element.attribute}> <strong>${element.label.toUpperCase()}</strong> </label>   
          <select class="form-control" id=${element.attribute} name=${element.attribute}>
                <option value= 'select'>select</option> 
                ${element.options.map(el => {
        return `<option value=${el}>${el}</option>`
      })
        }               
          </select>     
             <small id ='${element.attribute}_help' class='small'></small>
            <small id =${element.attribute}_error class='error'></small>
           </div>
          `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    }
    else if (element.type === 'radio') {
      const renderHtml = `
      <div class='form-group col' id='${element.attribute}_div'>
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
        <small id ='${element.attribute}_help' class='small'></small>
            <small id =${element.attribute}_error class='error'></small>
      </div>     
      `
        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    } else if (element.type === 'combined') {
      const renderHtml = 
       `<div class='input-group' id='${element.attribute}_div'>
       <div class="input-group-prepend">
       <span class="input-group-text">${element.attribute.toUpperCase()}:</span>
        </div>

       <input class='form-control' type='${element.inputType[0]}' aria-label="${element.placeholder}"  name='${element.attribute[0]}' id='${element.attribute}' value='${element.value}'> 

        <input class='form-control' type='${element.inputType[1]}' aria-label="${element.placeholder}"  name='${element.attribute[1]}' id='${element.attribute}' value='${element.value}'> 

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



