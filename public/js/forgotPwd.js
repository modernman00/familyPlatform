"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["forgotPwd"],{

/***/ "./resources/asset/js/components/forgotPwd/index.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/forgotPwd/index.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");


// Defensive safeguard: ensure #setLoader exists for legacy shared-js-lib showLoader/clearLoader
if (typeof document !== 'undefined' && !document.getElementById('setLoader')) {
  var dummyLoader = document.createElement('div');
  dummyLoader.id = 'setLoader';
  dummyLoader.style.display = 'none';
  dummyLoader.className = 'd-none';
  dummyLoader.setAttribute('aria-hidden', 'true');
  document.body.appendChild(dummyLoader);
}
localStorage.setItem('redirect', '/login/changePW');
sessionStorage.setItem('fromForgot', 'true');
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.forgotSubmitHandler)({
  formId: 'forgot',
  route: '/login/forgot',
  redirect: '/login/code',
  theme: 'bootstrap',
  recaptchaAction: 'FORGOT'
});

/***/ })

}]);
//# sourceMappingURL=forgotPwd.js.map?v=52a707a4