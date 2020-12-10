"use strict";
/**
 * 
 * @param {That is the obj name} objData 
 * @param {* this is the div id} htmlId 
 */

export const Input = (objData, htmlId) => {

  try {
    // check errors for the input
    if(!objData) throw (" data object is needed")
    if(htmlId == "") throw ("html id is required")
    
    objData.map((element) => {
    if (element.inputType === 'NORMAL_INPUT') {
      const renderHtml = `
      <label> <strong>${element.form.toUpperCase()}</strong> </label>
      <div class = 'form-group' id=${element.attribute}_div>
         <label class='' for =${element.attribute}> 
         <strong>${element.label.toUpperCase()}</strong>
         </label>
         <input type="${element.type}" class="form-control" 
         id="${element.attribute}"
         name="${element.attribute}"  placeholder="${element.placeholder}"
         />
        <small id ='${element.attribute}_help' class='small text-muted'></small>
            <small id =${element.attribute}_error class='error text-muted'></small>
      </div>`
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)

    } else if (element.inputType === 'SELECT') {
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
             <small id ='${element.attribute}_help' class='small text-muted'></small>
            <small id =${element.attribute}_error class='error text-muted'></small>
           </div>
          `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    } else if (element.inputType === 'FILE') {
      const renderHtml = `
      <div class = 'form-group input-group mb-3' id='${element.attribute}_div'>
       <div class="custom-file">
          <input type="file" name="${element.attribute}" class="custom-file-input" id=${element.attribute}>
          <label class="custom-file-label" for=${element.attribute}>${element.label}</label>
             <small id ='${element.attribute}_help' class='small text-muted'></small>
            <small id =${element.attribute}_error class='error text-muted'></small>
           </div>
          </div>
          `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    } else if (element.type === 'radio') {
      const renderHtml = `
      <label> <strong>${element.form.toUpperCase()}</strong> </label>
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
        <small id ='${element.attribute}_help' class='small text-muted'></small>
            <small id =${element.attribute}_error class='error text-muted'></small>
      </div>     
      `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    } else if (element.form === '3-col') {
      const renderHtml = `
      <label> <strong>${element.label.toUpperCase()}</strong> </label>
       <div class = 'form-row' id='${element.form}_div'>
       
          <div class='form-group col-md-4'>
            <label for='${element.options.attribute[0]}'>
            ${element.options.label[0]}
            </label>
            <input type='${element.options.type[0]}' class='form-control' name='${element.options.attribute[0]}'
            id='${element.options.attribute[0]}' placeholder='${element.options.placeholder[0]
        }'>
         <small id ='${element.options.attribute[0]}_help' class='small text-muted'></small>
            <small id =${element.options.attribute[0]}_error class='error text-muted'></small>
          </div>
          <div class='form-group col-md-4'>
            <label for='${element.options.attribute[1]}'>${element.options.label[1]}
            </label>
            <input type='${element.options.type[1]}' class='form-control'  
            id='${element.options.attribute[1]}' name='${element.options.attribute[1]}'
            placeholder='${element.options.placeholder[1]
            
        }'>
         <small id ='${element.options.attribute[1]}_help' class='small text-muted'></small>
            <small id =${element.options.attribute[1]}_error class='error text-muted'></small>
          </div> 
          <div class='form-group col-md-4'>
            <label for='${element.options.attribute[2]}'>${element.options.label[2]}
            </label>
            <input type='${element.options.type[2]}' class='form-control' 
            id='${element.options.attribute[2]}' name='${element.options.attribute[2]}' 
            placeholder='${element.options.placeholder[2]
        }'>
         <small id ='${element.options.attribute[2]}_help' class='small text-muted'></small>
            <small id =${element.options.attribute[2]}_error class='error text-muted'></small>
          </div>  
      </div>`
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    } else if (element.form === '2-col') {
      const renderHtml = `
      <label> <strong>${element.label.toUpperCase()}</strong> </label>
       <div class = 'form-row' id='${element.form}_div'>
       
          <div class='form-group col-md-6'>
            <label for='${element.options.attribute[0]}'>
            ${element.options.label[0]}
            </label>
            <input type='${element.options.type[0]}' class='form-control' 
            id='${element.options.attribute[0]}' 
            placeholder='${element.options.placeholder[0]}'
            name='${element.options.attribute[0]}'
            >
         <small id ='${element.options.attribute[0]}_help' class='small text-muted'></small>
            <small id =${element.options.attribute[0]}_error class='error text-muted'></small>
          </div>
          <div class='form-group col-md-6'>
            <label for='${element.options.attribute[1]}'>${element.options.label[1]}
            </label>
            <input type='${element.options.type[1]}' class='form-control' 
            id='${element.options.attribute[1]}' 
            placeholder='${element.options.placeholder[1]}'
            name='${element.options.attribute[1]}'
            >
         <small id ='${element.options.attribute[1]}_help' class='small text-muted'></small>
            <small id =${element.options.attribute[1]}_error class='error text-muted'></small>
          </div> 
      </div>`
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    } 
  })

  
  } catch (e) {
    console.log(e)
  }
 
}




