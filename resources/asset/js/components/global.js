export const id  = (id) => document.getElementById(id)
export const idValue = (id) => id(id).value
export const idInnerHTML = (id) => id(id).innerHTML

export const qSel = (name) => document.querySelector(name)
export const qSelValue = (name) => qSel(name).value
export const qSelInnerHTML = (name) => qSel(name).innerHTML

export const log = (id) => console.log(id)
export const write = (input) => document.write(input)

export const showError = (e) => {
    console.log(e instanceof TypeError)  // true
  console.log(e.message)               // "null has no properties"
  console.log(e.name)                  // "TypeError"
  console.log(e.fileName)              // "Scratchpad/1"
  console.log(e.lineNumber)            // 2
  console.log(e.columnNumber)          // 2
  console.log(e.stack)
}