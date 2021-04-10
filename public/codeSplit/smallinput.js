(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/codeSplit/smallInput"],{

/***/ "./resources/asset/js/components/smallinput.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/smallinput.js ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/global.js");

/* harmony default export */ __webpack_exports__["default"] = (function () {
  var maiden = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('motherMaiden_help');
  maiden.innerHTML = "Good to identify your family from mum's side";
  var mobile = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('mobile_help');
  mobile.innerHTML = "Nigeria: 2348036517179, UK: 447871717809";
  var password = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('password_help');
  password.innerHTML = 'Must be 8-20 characters long.';
  Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse_div').style.display = "none";

  var showSpouse = function showSpouse(e) {
    if (e.target.value === "Yes") {
      Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse_div').style.display = "block";
    } else {
      Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse_div').style.display = "none";
    }
  };

  Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('maritalStatus').addEventListener('change', showSpouse);
});

/***/ })

}]);