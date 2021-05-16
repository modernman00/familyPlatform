"use strict";
import { id } from '../global';
import autocomplete from 'autocompleter';


export const loaderIconBootstrap = () => {

  return `<div class="spinner-grow text-primary" role="status">
        <span class="sr-only">Loading...</span>
        </div>`
}

export const loaderIcon = () => {

  return `<div class="loader"></div>`
}

export const loaderIconBulma = () => {

  return `<div class="is-loading"></div>`
}

export const removeDiv = (div_id) => {
  const div = document.getElementById(div_id)
  if (div) {
    return div.remove()
  }

}

export const createAndAppendElement = (elementType, setId, parent, setClass = null) => {
  const newDiv = document.createElement(elementType);
  newDiv.setAttribute('id', setId)
  newDiv.setAttribute('class', `field ${setClass}`)
  const parentDiv = id(parent)
  return parentDiv.appendChild(newDiv)
}

/**
 * 
 * @param {the id of the input} inputId 
 * @param {the api data or array data} data 
 * @param { filterby is the data.filterby }
 */
export const autoCompleter = (inputId, data) => {
  autocomplete({
    input: inputId,
    fetch: function (text, update) {
      text = text.toLowerCase();
      // you can also use AJAX requests instead of preloaded data
      const suggestions = data.filter(n => n.firstName.toLowerCase().startsWith(text))
      update(suggestions);
    },
    onSelect: function (item) {
      input.value = item.firstName;
    }

  })
}

export const distinctValue = (array) => {
  return [... new Set(array)]
}

export const checkBox = (subject) => {
  return `<div class="control"> 
        <label class="radio">
          <input type="radio" name="send${subject}" value="yes" id=${subject}Yes> Yes 
        </label>
        <label class="radio"> 
          <input type="radio" name="send${subject}" value="no" id=${subject}No> No 
        </label>
      </div>`;
}

export const isChecked = (name, fn) => {
  const yesId = (`${name}Yes`)
  const noId = `${name}No`
  const checked = () => {
    if (id(yesId).checked) {
      alert('check')
      fn()
    } else if (id(noId).checked) {
      alert('check No')
    }
  }
  id(yesId).addEventListener('click', checked)
  id(noId).addEventListener('click', checked)
}

export const matchRegex = (data) => {
  if (data) {
    if (data != "Not Provided") {
      const regex = /[a-zA-Z0-9.@]+/g
      const result = data.match(regex)
      if (result.length > 1) return false
    }
  }
}
