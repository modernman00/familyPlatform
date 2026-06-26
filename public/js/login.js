"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["login"],{

/***/ "./resources/asset/js/components/acctMgt/login.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/acctMgt/login.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");


// Get the login URL from sessionStorage
var loginURL = sessionStorage.getItem('loginURL1');

// Determine the redirect URL based on loginURL
var redirect = loginURL === '/lasu' ? '/admin/reviewApps' : '/login/code';
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.createAdminLoginHandler)({
  formId: 'login',
  buttonId: 'button',
  // Changed from 'none' to allow manual click handling/validation
  emailId: 'email',
  passwordId: 'password',
  showToggleId: 'showPassword',
  route: 'login',
  redirect: redirect,
  theme: 'bulma',
  recaptchaAction: 'LOGIN'
});

/***/ })

}]);
//# sourceMappingURL=login.js.map