"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["/js/index"],{

/***/ "./resources/asset/js/index.js":
/*!*************************************!*\
  !*** ./resources/asset/js/index.js ***!
  \*************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var alpinejs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! alpinejs */ "./node_modules/alpinejs/dist/module.esm.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");




window.Alpine = alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"];
var routePromise = Promise.resolve();

/**
 * Tests if the current URL matches the given route.
 *
 * @param {string} url A URL route to test.
 *
 * @returns {boolean} True if the URL matches, otherwise false.
 */
var checkURL = function checkURL(url) {
  return !!window.location.pathname.match(new RegExp("^/".concat(url, "(?:/[^/]+)*$")));
};
try {
  var $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

  // Add a click event on each of them
  $navbarBurgers.forEach(function (el) {
    el.addEventListener('click', function () {
      // Get the target from the "data-target" attribute
      var target = el.dataset.target;
      var $target = document.getElementById(target);

      // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
      el.classList.toggle('is-active');
      $target.classList.toggle('is-active');
    });
  });
} catch (error) {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
}

// Get all "navbar-burger" elements

if (window.location.pathname === '/register') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.registerNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.registerNav').style.display = 'none'; // navbar mgt

  routePromise = Promise.all(/*! import() | register */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("register")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/ */ "./resources/asset/js/components/register/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMemberNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMemberNav').style.display = 'none';
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMembersNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMembersNav').style.display = 'none';
  routePromise = Promise.all(/*! import() | all_members */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("all_members")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/allMembers/ */ "./resources/asset/js/components/allMembers/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | login */ "login").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/login */ "./resources/asset/js/components/acctMgt/login.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/lasu') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | adminLogin */ "adminLogin").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/adminLogin */ "./resources/asset/js/components/acctMgt/adminLogin.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login/forgot') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | forgotPwd */ "forgotPwd").then(__webpack_require__.bind(__webpack_require__, /*! ./components/forgotPwd/ */ "./resources/asset/js/components/forgotPwd/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login/code') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | code */ "code").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/code */ "./resources/asset/js/components/acctMgt/code.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname.toLowerCase().startsWith('/member/profilepage') || window.location.pathname.toLowerCase().startsWith('/profilepage') || checkURL('member/ProfilePage') || checkURL('member/profilePage') || checkURL('profilePage')) {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.profilePageNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.profilePageNav').style.display = 'none'; // navbar mgt

  routePromise = Promise.all(/*! import() | profilePage */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("profilePage")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/ */ "./resources/asset/js/components/profilePage/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login/changePW' || window.location.pathname === '/changePW') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login').style.display = 'none'; // navbar mgt
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp').style.display = 'none'; // navbar mgt
  // qSel('#loader').style.display ="none" // loader
  routePromise = __webpack_require__.e(/*! import() | changePW */ "changePW").then(__webpack_require__.bind(__webpack_require__, /*! ./components/changePW/ */ "./resources/asset/js/components/changePW/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/profilepage/img') {
  // qSel('.login').style.display ="none" // navbar mgt
  routePromise = Promise.all(/*! import() | img */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("img")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/imgViewer */ "./resources/asset/js/components/profilePage/imgViewer.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/createFamilyCode') {
  routePromise = Promise.all(/*! import() | familyCode */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("familyCode")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/familyCode */ "./resources/asset/js/components/register/familyCode.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/register/nextStep') {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login').style.display = 'none'; // navbar mgt
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp').style.display = 'none'; // navbar mgt
} else if (checkURL('accountSetting')) {
  routePromise = Promise.all(/*! import() | accountSetting */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("accountSetting")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/accountSetting */ "./resources/asset/js/components/accountSetting.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (checkURL('organogram') || window.location.pathname.startsWith('/organogram')) {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav').style.display = 'none'; // navbar mgt

  routePromise = Promise.all(/*! import() | organogram */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("organogram")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/familyTree/index.js */ "./resources/asset/js/components/familyTree/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers/getProfile') {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav').style.display = 'none'; // navbar mgt
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_count').style.display = 'none'; // navbar mgt

  // import (
  // import { setCookie } from '../../../node_modules/y/Cookie';
  /* webpackChunkName: 'getProfile' */
  //     /* webpackPrefetch: true */
  //     './components/familyTree/index.js'
  // )
  // .then((module) => module.default)
  //     .catch((err) => showError(err))
}
routePromise.finally(function () {
  alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"].start();
});

/***/ }),

/***/ "./resources/asset/scss/main.scss":
/*!****************************************!*\
  !*** ./resources/asset/scss/main.scss ***!
  \****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/startup prefetch */
/******/ !function() {
/******/ 	__webpack_require__.O(0, ["/js/index"], function() {
/******/ 		["/js/vendor","register","all_members","login","adminLogin","forgotPwd","code","profilePage","changePW","img","familyCode","accountSetting","organogram"].map(__webpack_require__.E);
/******/ 	}, 5);
/******/ }();
/******/ 
/******/ var __webpack_exec__ = function(moduleId) { return __webpack_require__(__webpack_require__.s = moduleId); }
/******/ __webpack_require__.O(0, ["css/main","/js/vendor"], function() { return __webpack_exec__("./resources/asset/js/index.js"), __webpack_exec__("./resources/asset/scss/main.scss"); });
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=index.js.map