"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["login"],{

/***/ "./resources/asset/js/components/acctMgt/login.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/acctMgt/login.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");




// Set the redirect URL in localStorage
localStorage.setItem('redirect', '/profilePage');

// Determine the redirect URL based on loginURL
var redirect = '/code';

// alert('redirect ' + redirect)

(0,_shared__WEBPACK_IMPORTED_MODULE_0__.createAdminLoginHandler)({
  formId: 'login',
  route: 'login',
  redirect: redirect,
  theme: 'bulma'
});

/***/ })

}]);
//# sourceMappingURL=login.js.map