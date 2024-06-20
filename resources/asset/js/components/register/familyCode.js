import { id } from "../global"
const button = id("btnFamCode");



btnFamCode.addEventListener("click", function() {

    try {

        if (id('surname_id').value !== "") {
            const uniqueNumber = Date.now();

            const uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber) / 10000000);

            const getSurname = id('surname_id').value

            const firstFourLetters = getSurname.substring(0, 4);

            id('createCode').value = `${firstFourLetters.toUpperCase()}${uniqueNumber1}`;
            btnFamCode.disabled = true;
        }
    } catch (error) {
        id("surname_error").innerHTML = error.messages;

    }

});


// Get references to the HTML output and the copy icon

const copyIcon = id('copyIcon');
const htmlOutputDiv = id('createFamCode');
const htmlOutput = id('createCode');

copyIcon.addEventListener('click', async function(e) {
    copyIcon.innerHTML = "";

    try {
        e.preventDefault();

        // check if the family code has been generated 

        if (htmlOutput.value) {

            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(htmlOutput.value);
            } else {
                // Fallback to the deprecated method
                const range = document.createRange();
                range.selectNode(htmlOutputDiv);
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                document.execCommand('copy');
                selection.removeAllRanges();
            }

            copyIcon.innerHTML = "copied";
            id('famCode_id').value = htmlOutput.value

        } else {
            copyIcon.innerHTML = "copy";
            id('famCode_id').value = ""
            alert('Please generate the family code first')

        }


    } catch (e) {
        console.error('Unable to copy the HTML output: ', e);
    }
});