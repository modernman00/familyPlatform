"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["code"],{

/***/ "./resources/asset/js/components/acctMgt/code.js":
/*!*******************************************************!*\
  !*** ./resources/asset/js/components/acctMgt/code.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");



var fromForgot = sessionStorage.getItem('fromForgot');
var redirectTo;
// Determine redirect target based on session flag

if (fromForgot) {
  redirectTo = '/changePW';
} else {
  redirectTo = '/profilePage';
}
if (fromForgot) sessionStorage.removeItem('fromForgot');
console.log(redirectTo);
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.createCodeSubmitHandler)({
  formId: 'code',
  route: '/code',
  redirect: redirectTo,
  theme: 'bulma'
});

/***/ })

}]);
//# sourceMappingURL=code.js.map