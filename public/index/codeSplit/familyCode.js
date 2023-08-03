(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/familyCode"],{

/***/ "./resources/asset/js/components/register/familyCode.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/familyCode.js ***!
  \**************************************************************/
/***/ (() => {

var button = document.getElementById("buttonCreateFamilCode");
button.addEventListener("click", function () {
  var uniqueNumber = Date.now();
  var uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber) / 10000000);
  var getSurname = document.getElementById('yourSurname').value;
  var firstFourLetters = getSurname.substring(0, 4);
  document.getElementById('createCode').value = "".concat(firstFourLetters.toUpperCase()).concat(uniqueNumber1);
  button.disabled = true;
});

// Get references to the HTML output and the copy icon

const copyIcon = document.getElementById('copyIcon');

// Add a click event listener to the copy icon
copyIcon.addEventListener('click', async function () {
  const htmlOutput = document.getElementById('createFamCode');
  copyIcon.innerHTML = "";

  try {
    await navigator.clipboard.writeText(htmlOutput.textContent);
    copyIcon.innerHTML = "copied";
    location.replace('/register');
  } catch (e) {
    console.error('Unable to copy the HTML output: ', e);
  }
});



/***/ })

}]);