(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["/index"],{

/***/ "./resources/asset/js/components/global.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/global.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "id": () => (/* binding */ id),
/* harmony export */   "idValue": () => (/* binding */ idValue),
/* harmony export */   "idInnerHTML": () => (/* binding */ idInnerHTML),
/* harmony export */   "qSel": () => (/* binding */ qSel),
/* harmony export */   "qSelValue": () => (/* binding */ qSelValue),
/* harmony export */   "qSelInnerHTML": () => (/* binding */ qSelInnerHTML),
/* harmony export */   "log": () => (/* binding */ log),
/* harmony export */   "write": () => (/* binding */ write),
/* harmony export */   "showError": () => (/* binding */ showError)
/* harmony export */ });
var id = function id(_id) {
  return document.getElementById(_id);
};
var idValue = function idValue(id) {
  return id(id).value;
};
var idInnerHTML = function idInnerHTML(id) {
  return id(id).innerHTML;
};
var qSel = function qSel(name) {
  return document.querySelector(name);
};
var qSelValue = function qSelValue(name) {
  return qSel(name).value;
};
var qSelInnerHTML = function qSelInnerHTML(name) {
  return qSel(name).innerHTML;
};
var log = function log(id) {
  return console.log(id);
};
var write = function write(input) {
  return document.write(input);
};
var showError = function showError(e) {
  console.log(e instanceof TypeError); // true

  console.log(e.message); // "null has no properties"

  console.log(e.name); // "TypeError"

  console.log(e.fileName); // "Scratchpad/1"

  console.log(e.lineNumber); // 2

  console.log(e.columnNumber); // 2

  console.log(e.stack);
};

/***/ }),

/***/ "./resources/asset/js/index.js":
/*!*************************************!*\
  !*** ./resources/asset/js/index.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/global */ "./resources/asset/js/components/global.js");




if (window.location.pathname === '/register') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.signUp').style.display = "none"; // navbar mgt

  Promise.all(/*! import() | codeSplit/register */[__webpack_require__.e("/vendor"), __webpack_require__.e("codeSplit/register")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/ */ "./resources/asset/js/components/register/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers') {
  Promise.all(/*! import() | codeSplit/all_members */[__webpack_require__.e("/vendor"), __webpack_require__.e("codeSplit/all_members")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/allMembers/ */ "./resources/asset/js/components/allMembers/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/login') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.login').style.display = "none"; // navbar mgt

  Promise.all(/*! import() | codeSplit/login */[__webpack_require__.e("/vendor"), __webpack_require__.e("codeSplit/login")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/login/ */ "./resources/asset/js/components/login/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/member/ProfilePage') {
  // qSel('.login').style.display ="none" // navbar mgt
  Promise.all(/*! import() | codeSplit/profilePage */[__webpack_require__.e("/vendor"), __webpack_require__.e("codeSplit/profilePage")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/ */ "./resources/asset/js/components/profilePage/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
}

/***/ }),

/***/ "./resources/asset/scss/main.scss":
/*!****************************************!*\
  !*** ./resources/asset/scss/main.scss ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ "use strict";
/******/ 
/******/ /* webpack/runtime/startup prefetch */
/******/ (() => {
/******/ 	__webpack_require__.O(0, ["/index"], () => (["/vendor","codeSplit/register","codeSplit/all_members","codeSplit/login","codeSplit/profilePage"].map(__webpack_require__.E)), 5);
/******/ })();
/******/ 
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["public/style"], () => (__webpack_exec__("./resources/asset/js/index.js"), __webpack_exec__("./resources/asset/scss/main.scss")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);