
const button = document.getElementById("buttonCreateFamilCode");



button.addEventListener("click", function() {

    const uniqueNumber = Date.now();

    const uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber)/ 10000000);

    const getSurname = document.getElementById('yourSurname').value

    const firstFourLetters = getSurname.substring(0, 4);
  
    document.getElementById('createCode').value = `${firstFourLetters.toUpperCase()}${uniqueNumber1}`;
    button.disabled= true;
});


// Get references to the HTML output and the copy icon

const copyIcon = document.getElementById('copyIcon');

// Add a click event listener to the copy icon
copyIcon.addEventListener('click', () => {
  const htmlOutput = document.getElementById('createFamCode');
  copyIcon.innerHTML = ""
    // Create a range object and select the HTML output content
    const range = document.createRange();
    range.selectNode(htmlOutput);

    // Add the range to the clipboard
    try {
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        document.execCommand('copy');
        selection.removeAllRanges();
        copyIcon.innerHTML="copied"

       location.replace('/register')
    } catch (e) {
        console.error('Unable to copy the HTML output: ', e);
    }
});



