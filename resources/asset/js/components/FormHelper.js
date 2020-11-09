'use strict'
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

        const reg = /[a-zA-Z0-9./@]/g;
        this.data.forEach((et) => {
            for (let post of et) {
                // capture the error to a variable
                let errMsg = this.id(`${post.name}_error`)
                // rid it off the submit button
                if (post.type == 'submit' || post.name == 'token') {
                    continue;
                }


                // check if there is no value

                if (post.value === '' || post.value === 'select') {

                    let postName = post.name.replace('_', ' ')
                    errMsg.innerHTML = `<li>Cannot be left empty</li>`
                    this.error.push(`<li>Cannot be left empty</li>`)

                } else if (post.value.match(reg) === null) {

                    errMsg.innerHTML = `<li> only letters and numbers are allowed<li>`
                    this.error.push(`<li> only letters and numbers are allowed<li>`);

                } else {
                    this.result = 1
                }
            }
        })

    }

    emailVal() {
        const emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
        let msg = `<li> Please enter a valid email</li>`
        const email = this.id('email').value
        if (email.match(emailExp) === null) {
            this.id('email_error').innerHTML = msg
            this.error.push(msg)
        }

    }

    clearError() {
        this.error = [] // empty the array 

        this.data.forEach(el => {
            for (let post of el) {
                if (post.id == 'submit' || post.name == 'token' || post.name == 'submit' || post.name == 'checkbox') {
                    continue

                }

                if (post.value != 'select') {
                    this.id(post.id).addEventListener('keyup', () => {
                        this.id(`${post.name}_error`).innerHTML = ''
                    })
                } else {
                    this.id(post.id).addEventListener('change', () => {
                        this.id(`${post.name}_error`).innerHTML = ''
                    })
                }


            }
        })
    }

    /**
     *
     * @param {input is the id of the input/ this is an array [as, it, it]} input
     * @param {* this is the max policy and it must be an integer} maxi
     */

    realTimeCheckLen(input, maxi) {
        for (let i = 0;
            i < input.length;
            i++) {
            const theData = this.id(`${input[i]}`);
            const max = maxi[i];
            const error = this.id(`${input[i]}_error`);
            if (theData)
                theData.maxLength = parseInt(max + 1);
            theData.addEventListener('keyup', () => {
                error.innerHTML = (theData.value.length > max) ? `You have reach the maximum limit` : "";
                this.id(`${input[i]}_help`).style.color = 'red'
                this.id(`${input[i]}_help`).style.fontSize = '20px'

                setTimeout(() => {
                    this.id(`${input[i]}_help`).style.display = 'none'
                }, 5000);
            })

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
        firstInput = this.id(first)
        secondInput = this.id(second)
        secondInput.addEventListener('keyup', () => {
            error.innerHTML = (firstInput.value !== secondInput.value) ? 'Your passwords do not match' : ""
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
