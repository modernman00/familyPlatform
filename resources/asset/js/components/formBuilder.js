export const Input = (objData, htmlId) => {
  objData.forEach((element) => {
    if (element.type != 'select') {

      const renderHtml = `
             <label for =${element.attribute}> ${element.label}</label>
            <input type="${element.type}" class="form-control" id="${element.attribute}" name="${element.attribute}" placeholder="${element.placeholder}"/>
            <small id =${element.attribute}-small></small><br>
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



