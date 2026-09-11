import { id } from "@shared";
import Swal from "sweetalert2";
const btnFamCode = id("btnFamCode");

btnFamCode.addEventListener("click", function() {
    console.log("Generate Family Code clicked");
    try {
        const surnameEl = id('surname');
        if (surnameEl && surnameEl.value !== "") {
            const rawSurname = (surnameEl.value || '').replace(/[^A-Za-z]/g, '');
            let prefix = rawSurname.substring(0, 3).toUpperCase();
            if (prefix.length < 3) {
                prefix = prefix.padEnd(3, 'X');
            }
            const randomThreeDigits = Math.floor(100 + Math.random() * 900);

            id('createCode').value = `${prefix}${randomThreeDigits}`;
            btnFamCode.disabled = true;
            btnFamCode.innerText = "Generated";
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Surname',
                text: 'Please enter a surname first.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    } catch (error) {
        console.error("Family Code Generation Error:", error);
        const errEl = id("surname_error");
        if (errEl) errEl.innerHTML = error.message || "An error occurred";
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
            const targetInput = id('famCode');
            if (targetInput) {
                targetInput.value = htmlOutput.value;
                console.log("Family code copied to form:", htmlOutput.value);
            } else {
                console.error("Target input 'famCode' not found");
            }

        } else {
            copyIcon.innerHTML = "copy";
            const targetInput = id('famCode');
            if (targetInput) targetInput.value = "";
            Swal.fire({
                icon: 'warning',
                title: 'Generate Code First',
                text: 'Please generate the family code first.',
                timer: 3000,
                showConfirmButton: false
            });
        }


    } catch (e) {
        console.error('Unable to copy the HTML output: ', e);
    }
});