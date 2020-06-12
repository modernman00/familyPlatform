export const id  = (id) => document.getElementById(id)
export const idValue = (id) => id(id).value
export const idInnerHTML = (id) => id(id).innerHTML

export const name = (name) => document.querySelector(name)
export const nameValue = (name) => name(name).value
export const nameInnerHTML = (name) => name(name).innerHTML

export const log = (id) => console.log(id)
export const write = (input) => document.write(input)