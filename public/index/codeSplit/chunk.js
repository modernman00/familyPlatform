(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/chunk"],{

/***/ "./resources/asset/js/components/smallinput.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/smallinput.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/global.js");

var maiden = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('motherMaiden_help');
maiden.innerHTML = "Good to identify your family from the mother's side";
var mobile = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('mobile_help');
mobile.innerHTML = "Nigeria: 2348036517179, UK: 447871717809";
var password = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('password_help');
password.innerHTML = 'Must be 8-20 characters long.';
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('spouse').style.display = "none";
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('children2').style.display = "none";

var showSpouse = function showSpouse(e) {
  if (e.target.value === "Yes") {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('spouse').style.display = "block";
  } else {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('spouse').style.display = "none";
  }
};

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus_id').addEventListener('change', showSpouse);

/***/ })

}]);