export const id  = (id) => document.getElementById(id)
export const idValue = (id) => id(id).value
export const idInnerHTML = (id) => id(id).innerHTML

export const qSel = (name) => document.querySelector(name)
export const qSelValue = (name) => qSel(name).value
export const qSelInnerHTML = (name) => qSel(name).innerHTML

export const log = (id) => console.log(id)
export const write = (input) => document.write(input)