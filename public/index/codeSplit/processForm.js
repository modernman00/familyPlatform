(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/processForm"],{

/***/ "./resources/asset/js/components/register/processForm.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/register/processForm.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/global.js");
/* harmony import */ var _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../data/dataToCheck */ "./resources/asset/js/data/dataToCheck.js");





var formInput = document.querySelectorAll('.register');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_0__.default(formInputArr);

var process = function process() {
  // clear error from the form
  formData.clearError(); // set the maxlength, check the length of the value, raise error

  formData.realTimeCheckLen(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.maxLength.id, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.maxLength.max); //real time check 

  formData.realTimeServer('spouseMobile_id', "/search?attribute=spouseMobile&subject=spouse&hint", 'spouseMobile_error');
  formData.realTimeServer('fatherMobile_id', '/search?attribute=fatherMobile&subject=father&hint', 'fatherMobile_error');
  formData.realTimeServer('motherMobile_id', '/search?attribute=motherMobile&subject=mother&hint', 'motherMobile_error'); // check if password matches real time

  formData.matchInput(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.password.pwd, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.password.pwd2 // dataToCheckRegister.password.err
  ); // check if they have a father yes
  // formData.isChecked(dataToCheckRegister.familyCheck.father[0],
  // 	dataToCheckRegister.familyCheck.father[1],
  // 	'fatherEmail_error'
  // )
  // // check if they have a mother yes
  // formData.isChecked(dataToCheckRegister.familyCheck.mother[0],
  // 	dataToCheckRegister.familyCheck.mother[1],
  // 	'motherEmail_error'
  // )
  // // check if they have a spouse yes
  // formData.isChecked(dataToCheckRegister.familyCheck.spouse[0],
  // 	dataToCheckRegister.familyCheck.spouse[1],
  // 	'spouseEmail_error'
  // )
};

process();
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('submit').addEventListener('click', function () {
  try {
    if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('checkbox').checked) {
      formData.emailVal(); // sanitise email

      formData.massValidate(); // validate and sanitise data
      //log(formData.error)

      if (formData.error.length <= 0) {
        (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('submit').type = 'submit'; //console.log('submitted')
      } else {
        (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(formData.error);
        alert('The form cannot be submitted. Please check the errors');
        process();
      }
    } else {
      alert('To continue, you need to agree to the Olaoguns handling your information as outlined in our privacy policy');
    }
  } catch (e) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(e);
  }
});

/***/ })

}]);