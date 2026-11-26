"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["changePW"],{

/***/ "./resources/asset/js/components/changePW/index.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/changePW/index.js ***!
  \*********************************************************/
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
;(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.setupPasswordChange)({
  formId: 'changePW',
  route: '/login/changePW',
  redirect: '/login'
});

/***/ })

}]);
//# sourceMappingURL=changePW.js.map?v=7cee8dc8