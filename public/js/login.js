"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["login"],{

/***/ "./resources/asset/js/components/acctMgt/login.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/acctMgt/login.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
var _document$getElementB;

var redirect = '/login/code';
localStorage.setItem('redirect', redirect);
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.createAdminLoginHandler)({
  formId: 'login',
  route: '/login',
  redirect: redirect,
  recaptchaAction: 'LOGIN',
  optionalFields: ['rememberMe']
});
(_document$getElementB = document.getElementById('button')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.setAttribute('data-ready', 'true');

/***/ })

}]);
//# sourceMappingURL=login.js.map