"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["/js/index"],{

/***/ "./resources/asset/js/index.js":
/*!*************************************!*\
  !*** ./resources/asset/js/index.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");



document.addEventListener('DOMContentLoaded', function () {
  var routeMap = {
    '/register': {
      module: function module() {
        return Promise.all(/*! import() | register */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("register")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/ */ "./resources/asset/js/components/register/index.js"));
      },
      hide: ['.signUp'] // Hide signup navbar
    },
    '/allMembers': {
      module: function module() {
        return Promise.all(/*! import() | all_members */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("all_members")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/allMembers/ */ "./resources/asset/js/components/allMembers/index.js"));
      },
      hide: ['.allMemberNav'] // Hide allMembers navbar
    },
    '/login': {
      module: function module() {
        return __webpack_require__.e(/*! import() | login */ "login").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/login.js */ "./resources/asset/js/components/acctMgt/login.js"));
      },
      hide: ['.login'] // Hide login navbar
    },
    '/lasu': {
      module: function module() {
        return __webpack_require__.e(/*! import() | lasu */ "lasu").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/admin.js */ "./resources/asset/js/components/acctMgt/admin.js"));
      },
      hide: ['.login'] // Same login module as /login
    },
    '/forgot': {
      module: function module() {
        return __webpack_require__.e(/*! import() | forgotPwd */ "forgotPwd").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/forgot.js */ "./resources/asset/js/components/acctMgt/forgot.js"));
      },
      hide: ['.signup_login'] // Hide login/signup navbar
    },
    '/code': {
      module: function module() {
        return __webpack_require__.e(/*! import() | code */ "code").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/code.js */ "./resources/asset/js/components/acctMgt/code.js"));
      },
      hide: ['.signup_login'] // Hide login/signup navbar
    },
    '/profilePage': {
      module: function module() {
        return Promise.all(/*! import() | profilePage */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("profilePage")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/ */ "./resources/asset/js/components/profilePage/index.js"));
      },
      hide: ['.profilePageNav'] // Hide profile page navbar
    },
    '/changePW': {
      module: function module() {
        return __webpack_require__.e(/*! import() | changePW */ "changePW").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/changePW.js */ "./resources/asset/js/components/acctMgt/changePW.js"));
      },
      hide: ['.login', '.signUp'] // Hide login and signup navbars
    },
    '/profilepage/img': {
      module: function module() {
        return Promise.all(/*! import() | img */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("img")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/imgViewer */ "./resources/asset/js/components/profilePage/imgViewer.js"));
      }
    },
    '/createFamilyCode': {
      module: function module() {
        return __webpack_require__.e(/*! import() | familyCode */ "familyCode").then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/familyCode */ "./resources/asset/js/components/register/familyCode.js"));
      }
    },
    '/register/nextStep': {
      hide: ['.login', '.signUp'] // No module, just hide navbars
    },
    '/organogram': {
      module: function module() {
        return Promise.all(/*! import() | organogram */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("organogram")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/familyTree/index.js */ "./resources/asset/js/components/familyTree/index.js"));
      },
      hide: ['.familyTreeNav'] // Hide family tree navbar
    },
    '/allMembers/getProfile': {
      // Module import commented out — placeholder for future logic
      hide: ['.familyTreeNav', '.notification_count'] // Hide navbars
    }
  };

  // Support for dynamic route matching
  if (checkURL('accountSetting')) {
    routeMap['/accountSetting'] = {
      module: function module() {
        return Promise.all(/*! import() | accountSetting */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("accountSetting")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/accountSetting */ "./resources/asset/js/components/accountSetting.js"));
      }
    };
  }
  try {
    var path = window.location.pathname;
    var route = routeMap[path];
    if (!route) {
      throw new Error("Unhandled route: ".concat(path));
    }

    // Hide specified nav elements
    if (route.hide) {
      route.hide.forEach(function (selector) {
        var el = document.querySelector(selector);
        if (el) el.style.display = 'none';
      });
    }

    // Load module if defined
    if (route.module) {
      route.module().then(function (mod) {
        return mod["default"];
      })["catch"](function (err) {
        (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
        throw new Error("Failed to load module for ".concat(path, ": ").concat(err.message));
      });
    }
  } catch (error) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    throw error;
  }
});

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
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
}

/***/ }),

/***/ "./resources/asset/scss/main.scss":
/*!****************************************!*\
  !*** ./resources/asset/scss/main.scss ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["css/main","/js/vendor"], () => (__webpack_exec__("./resources/asset/js/index.js"), __webpack_exec__("./resources/asset/scss/main.scss")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=index.js.map