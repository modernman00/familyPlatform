/**
 * 
 * @param {That is the obj name} objData 
 * @param {* this is the div id} htmlId 
 */
export const Input = (objData, htmlId) => {
  objData.map((element) => {
    if (element.type != 'select' && !element.options) {
      const renderHtml = `
             <label for =${element.attribute}> ${element.label}</label>
            <input type="${element.type}" class="form-control" id="${element.attribute}" name="${element.attribute}" placeholder="${element.placeholder}"/>
            <small id =${element.attribute}-small></small><br>
          `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    } else if (element.type === 'select') {
      const renderHtml = `<label for =${element.attribute}> ${element.label}</label>   
          <select class="form-control" id=${element.attribute}>
                <option>SELECT</option> 
                ${
                    element.options.map(el => {
                      return `<option>${el}</option>` 
                    })
                }             
             
          </select>     
           <small id =${element.attribute}-small></small><br>
          `
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml)
    }
    else if(element.type === 'radio'){
      const renderHtml = `<label for =${element.attribute}> ${element.label}</label> 
      <input class="form-check-input" type="radio" name="element.attribute" id="element.attribute" value=" gyguuiuuiy  " >
      `
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



