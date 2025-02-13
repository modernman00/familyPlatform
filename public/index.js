"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["/public/index"],{

/***/ "./resources/asset/js/components/global.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/global.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkElements: () => (/* binding */ checkElements),
/* harmony export */   checkManyElements: () => (/* binding */ checkManyElements),
/* harmony export */   date2String: () => (/* binding */ date2String),
/* harmony export */   hideElement: () => (/* binding */ hideElement),
/* harmony export */   id: () => (/* binding */ id),
/* harmony export */   idInnerHTML: () => (/* binding */ idInnerHTML),
/* harmony export */   idValue: () => (/* binding */ idValue),
/* harmony export */   log: () => (/* binding */ log),
/* harmony export */   manipulateAttribute: () => (/* binding */ manipulateAttribute),
/* harmony export */   msgException: () => (/* binding */ msgException),
/* harmony export */   qSel: () => (/* binding */ qSel),
/* harmony export */   qSelAll: () => (/* binding */ qSelAll),
/* harmony export */   qSelInnerHTML: () => (/* binding */ qSelInnerHTML),
/* harmony export */   qSelValue: () => (/* binding */ qSelValue),
/* harmony export */   showElement: () => (/* binding */ showElement),
/* harmony export */   showError: () => (/* binding */ showError),
/* harmony export */   showNotification: () => (/* binding */ showNotification),
/* harmony export */   warningSign: () => (/* binding */ warningSign),
/* harmony export */   write: () => (/* binding */ write)
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
var warningSign = "\u26A0"; // danger warning sign

var qSel = function qSel(name) {
  return document.querySelector(name);
};
var qSelAll = function qSelAll(name) {
  return document.querySelectorAll(name);
};
var qSelValue = function qSelValue(name) {
  return qSel(name).value;
};
var qSelInnerHTML = function qSelInnerHTML(name) {
  return qSel(name).innerHTML;
};
var log = function log(id) {
  var identifier = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  console.log(' start' + "  " + identifier);
  console.log(id);
  console.log(' end' + "  " + identifier);
};
var write = function write(input) {
  return document.write(input);
};
var hideElement = function hideElement(elementId) {
  id(elementId).style.display = "none";
};
var showElement = function showElement(elementId) {
  id(elementId).style.display = "block";
};
var manipulateAttribute = function manipulateAttribute(idName, removeOrSet, attributeType) {
  var nameValue = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  if (removeOrSet === "remove") {
    id(idName).removeAttribute(attributeType);
  } else {
    id(idName).setAttribute(attributeType, nameValue);
  }
};
var date2String = function date2String(date) {
  return new Date().toDateString(date);
};
var showError = function showError(e) {
  log(e.message, " ERROR MESSAGE"); // "null has no properties"
  log(e.name, " ERROR NAME"); // "TypeError"
  log(e.fileName, " ERROR FILENAME"); // "Scratchpad/1"
  log(e.lineNumber, " ERROR LINENUMBER"); // 2

  log(e.stack);
};
var msgException = function msgException(errorMessage) {
  throw new Error(errorMessage);
};

/**
 * 
 * @param {*} elementId - element id
 * @param {*} addClass either a success or danger class (green or red)
 * @param {*} message - html message to convey success or failure
 * @param {*} timer - timer for the message to disappear- default is 5 secs
 */
var showNotification = function showNotification(elementId, addClass, message) {
  var timer = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 5000;
  // display the success information for 10sec
  id("".concat(elementId)).style.display = "block"; // unblock the notification
  id("".concat(elementId)).classList.add(addClass); // add the success class
  id("".concat(elementId)).innerHTML = message; // error element
  id('loader').classList.remove('loader'); // remove loader

  setTimeout(function () {
    id("".concat(elementId)).style.backgroundColor = "";
    id("".concat(elementId)).style.color = "";
    id("".concat(elementId)).innerHTML = "";
  }, timer);
};

// Function to check for elements and render if they exist
var checkElements = function checkElements(idOrClass, classString) {
  var theFunction = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  var doesElementExist = idOrClass === "id" ? id(classString) : qSel(classString);
  // Check if elements exist before calling render function
  if (doesElementExist.length) {
    theFunction(doesElementExist);
  }
};
var checkManyElements = function checkManyElements(idOrClass, classString) {
  var theFunction = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  var doesElementExist = idOrClass === "id" ? id(classString) : qSelAll(classString);
  // Check if elements exist before calling render function
  if (doesElementExist.length > 0) {
    theFunction(doesElementExist);
  }
};

/***/ }),

/***/ "./resources/asset/js/index.js":
/*!*************************************!*\
  !*** ./resources/asset/js/index.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/global */ "./resources/asset/js/components/global.js");




// to make the bulma navbar menu visible on mobile

var checkURL = function checkURL(url) {
  var regex = new RegExp("^/".concat(url, "(/[^/]+)*$"));
  return regex.test(window.location.pathname);
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
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
}

// Get all "navbar-burger" elements

if (window.location.pathname === '/register') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.signUp').style.display = "none"; // navbar mgt

  Promise.all(/*! import() | register */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("register")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/ */ "./resources/asset/js/components/register/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.allMemberNav').style.display = "none"; //allMemberNav

  Promise.all(/*! import() | all_members */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("all_members")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/allMembers/ */ "./resources/asset/js/components/allMembers/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/login' || window.location.pathname === '/lasu') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.login').style.display = "none"; // navbar mgt

  Promise.all(/*! import() | login */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("login")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/login/ */ "./resources/asset/js/components/login/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/login/forgot') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.signup_login').style.display = "none"; // navbar mgt

  Promise.all(/*! import() | forgotPwd */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("forgotPwd")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/forgotPwd/ */ "./resources/asset/js/components/forgotPwd/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/login/code') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.signup_login').style.display = "none"; // navbar mgt

  Promise.all(/*! import() | code */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("code")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/generateCode/Code */ "./resources/asset/js/components/generateCode/Code.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/member/ProfilePage') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.profilePageNav').style.display = "none"; // navbar mgt

  Promise.all(/*! import() | profilePage */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("profilePage")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/ */ "./resources/asset/js/components/profilePage/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/login/changePW') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.login').style.display = "none"; // navbar mgt
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.signUp').style.display = "none"; // navbar mgt
  // qSel('#loader').style.display ="none" // loader
  Promise.all(/*! import() | changePW */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("changePW")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/changePW/ */ "./resources/asset/js/components/changePW/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/profilepage/img') {
  // qSel('.login').style.display ="none" // navbar mgt
  Promise.all(/*! import() | img */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("img")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/imgViewer */ "./resources/asset/js/components/profilePage/imgViewer.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/createFamilyCode') {
  __webpack_require__.e(/*! import() | familyCode */ "familyCode").then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/familyCode */ "./resources/asset/js/components/register/familyCode.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/register/nextStep') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.login').style.display = "none"; // navbar mgt
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.signUp').style.display = "none"; // navbar mgt
} else if (checkURL('accountSetting')) {
  Promise.all(/*! import() | accountSetting */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("accountSetting")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/accountSetting */ "./resources/asset/js/components/accountSetting.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/organogram') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.familyTreeNav').style.display = "none"; // navbar mgt

  Promise.all(/*! import() | organogram */[__webpack_require__.e("/public/vendor"), __webpack_require__.e("organogram")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/familyTree/index.js */ "./resources/asset/js/components/familyTree/index.js")).then(function (module) {
    return module["default"];
  })["catch"](function (err) {
    return (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers/getProfile') {
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.familyTreeNav').style.display = "none"; // navbar mgt
  (0,_components_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.notification_count').style.display = "none"; // navbar mgt

  // import (
  //     /* webpackChunkName: 'getProfile' */
  //     /* webpackPrefetch: true */
  //     './components/familyTree/index.js'
  // )
  // .then((module) => module.default)
  //     .catch((err) => showError(err))
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
/******/ /* webpack/runtime/startup prefetch */
/******/ (() => {
/******/ 	__webpack_require__.O(0, ["/public/index"], () => {
/******/ 		["/public/vendor","register","all_members","login","forgotPwd","code","profilePage","changePW","img","familyCode","accountSetting","organogram"].map(__webpack_require__.E);
/******/ 	}, 5);
/******/ })();
/******/ 
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["public/style"], () => (__webpack_exec__("./resources/asset/js/index.js"), __webpack_exec__("./resources/asset/scss/main.scss")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);