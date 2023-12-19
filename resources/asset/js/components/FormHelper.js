'use strict'

import { matchRegex } from "./helper/general"

export default class FormHelper {
    constructor(data) {
        this.data = data;
        this.error = [];
        this.result = 0;
    }

    id(x) {
        return document.getElementById(x)
    }

    /**
     * general validation; check empty status, at least a single input, mobile length, white space
     */

    getData() {
        return this.data;
    }


    massValidate() {
        // const reg = /[a-zA-Z0-9./@]/g;
        this.data.forEach(et => {

            for (let post of et) {
                // capture the error to a variable
                let errMsg = this.id(`${post.name}_error`)
                // console.log(post)
                // rid it off the submit and token
                if (post.name == 'submit' ||
                    post.name == 'button' ||
                    post.name == 'token' ||
                    post.name == 'cancel' ||
                    post.name == "checkbox_id") {
                    continue;
                }

                // check if there is no value

                let postName = post.name.replace('_', ' ')
                if (postName == "spouseName" ||
                    postName == "spouseMobile" ||
                    postName == "spouseEmail" ||
                    postName == "fatherMobile" ||
                    postName == "fatherEmail" ||
                    postName == "motherMobile" ||
                    postName == "maidenName" ||
                    postName == "motherEmail"
                ) {
                    if (post.value === "") {
                        post.value = "Not Provided"
                    }
                }

                if (post.value === '' || post.value === 'select') {
                    errMsg.innerHTML = `*cannot be left empty`
                    errMsg.style.color = "red"
                    this.error.push(`${postName.toUpperCase()} cannot be left empty`)
                } else {
                    this.result = 1
                }
                const checkRegex = matchRegex(post.value)
                if (checkRegex === false) {
                    this.error.push(`There is a problem with your entry for ${postName.toUpperCase()}'s question`)

                    errMsg.innerHTML = `* There is a problem with you entry for ${postName.toUpperCase()}'s question`
                }
            }
        })
    }

    emailVal() {
        const emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
        let msg = `<li style=color:'red';> Please enter a valid email</li>`
        const email = this.id('email_id').value
        if (email.match(emailExp) === null) {
            this.id('email_error').innerHTML = msg
            this.id('email_error').style.color = "red"
            this.error.push(msg)
        }
    }

    clearError() {
        this.error = []; // Empty the error array

        // Define a function to clear error messages for a given input element
        const clearErrorForElement = (elementName) => {
            const errorElement = this.id(`${elementName}_error`);
            if (errorElement) {
                errorElement.innerHTML = '';
            }
        };

        this.data.forEach(el => {

            for (let post of el) {

                const { id, name, value } = post;

                // Skip certain input types
                if (['submit', 'token', 'checkbox'].includes(id) || ['token', 'submit'].includes(name)) {
                    continue;
                }
                // Add change event listener to clear error message
                this.id(id).addEventListener('change', () => {
                    clearErrorForElement(name);
                });
                // Add keyup event listener for non-select inputs
                if (value !== 'select') {
                    this.id(id).addEventListener('keyup', () => {
                        clearErrorForElement(name);
                    });
                } else {
                    this.id(id).addEventListener('keyup', () => {
                        clearErrorForElement(name);
                    });
                }
            }
        })
    }

    clearHtml() {

        this.data.forEach(el => {
            for (let post of el) {
                if (post.id == 'submit' || post.name == 'submit' || post.name == 'checkbox') {
                    continue
                }
                post.value = ""


            }
        })
    }

    /**
     *
     * @param {input is the id of the input/ this is an array [as, it, it]} input
     * @param {* this is the max policy and it must be an integer} maxi
     */

    realTimeCheckLen(input, maxi) {
        try {

            for (let i = 0; i < input.length; i++) {
                const theData = this.id(`${input[i]}_id`);
                if (theData == "") throw new Error("empty dataInput");
                const max = maxi[i];
                const error = this.id(`${input[i]}_error`);
                if (theData)
                    theData.maxLength = parseInt(max + 1);
                theData.addEventListener('keyup', () => {
                    error.innerHTML = (theData.value.length > max) ? `You have reach the maximum limit` : "";
                    this.id(`${input[i]}_help`).style.color = 'red'
                    this.id(`${input[i]}_help`).style.fontSize = '10px'
                    error.style.color = 'red'

                    setTimeout(() => {
                        this.id(`${input[i]}_help`).style.display = 'none'
                    }, 5000);
                })

            }

        } catch (error) {
            console.log(error.message)
        }
    }

    /**
     * the id for the password error should be password_help
     * the id for your confirm pasword should confirm_password
     * it will return an error message to the password_help input
     */

    matchInput(first, second) {
        let error, firstInput, secondInput
        error = this.id(`${second}_error`)
        firstInput = this.id(first + '_id')
        secondInput = this.id(second + '_id')
        secondInput.addEventListener('keyup', () => {
            error.innerHTML = (secondInput.value !== firstInput.value) ? 'Your passwords do not match' : ""
        })
    }
    /**
     *
     * @param {the id of the input you want to inject to/ this is an array} idArray
     * @param {*the comment or questions you want o inject} html
     */

    injectData(idArray, html) {
        let idData;
        for (let i = 0; i < idArray.length; i++) {
            idData = this.id(idArray[i]);
            idData.innerHTML = html[i];
        }
    }

    /**
     *
     * @param {this is an id and its value is for duplication} firstInput
     * @param {* another id that accepts the value of the firstInput} takeFirstInput
     */
    duplicate(giveInput, takeInput) {
        let giver, taker;
        giver = this.id(giveInput)
        taker = this.id(takeInput)
        giver.addEventListener('keyup', () => {
            taker.value = giver.value;
        })
    }

    /**
     *
     * @param {current input that is being type to. the value is what will be checked realtime. the id is needed} input
     * @param {* the url to get the info to . example is /search?hint} url
     * @param {enter the id of the output element} output
     */


    realTimeServer(input, url, outputId) {
        let theInput, inputVal, output;
        theInput = this.id(input)
        output = this.id(outputId)
        theInput.addEventListener('keyup', () => {
            inputVal = theInput.value

            if (inputVal == 0) {
                output.innerHTML = "";
                return;
            } else {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        output.innerHTML = this.responseText;
                    }
                }
                xmlhttp.open("GET", `${url}=${inputVal}`, true);
                xmlhttp.send();
            }
        })
    }

    isChecked(yesId, noId, hiddenInput) {
        const checked = () => {
            if (this.id(yesId).checked) {
                alert('check')
                this.id(hiddenInput).innerHTML = 'checked';
            } else if (this.id(noId).checked) {
                this.id(hiddenInput).innerHTML = 'checked';
            }
        }

        this.id(yesId).addEventListener('click', checked)
        this.id(noId).addEventListener('click', checked)

    }

    previousAddress() {
        const timeAddy = this.id('time_at_address_id')
        const prevAddy = this.id('previous_address_class')
        const showPrev = () => {
            if (timeAddy.value != '3 years+') {
                prevAddy.style.display = 'block'
                this.id('previous_address_help').innerHTML = "Please enter your full address: House No, Street Name, Town/City and Post Code"
            } else {
                prevAddy.style.display = 'none'
            }

        }
        timeAddy.addEventListener('change', showPrev)

    }


}