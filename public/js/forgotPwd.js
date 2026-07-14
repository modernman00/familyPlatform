"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["forgotPwd"],{

/***/ "./resources/asset/js/components/forgotPwd/index.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/forgotPwd/index.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");
/* harmony import */ var _helper_security__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/security */ "./resources/asset/js/components/helper/security.js");






// block the setLoader div

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("setLoader").style.display = "none";
var forgotPasswordSubmission = function forgotPasswordSubmission(e) {
  try {
    e.preventDefault();
    var email = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('email_id').value;

    // just in case there was an earlier error notification - remove it
    var forgotPasswordNotification = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('forgotPassword_notification');
    if (forgotPasswordNotification.classList.contains('is-danger')) {
      forgotPasswordNotification.classList.remove('is-danger');
    }
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = "";
    if (!(0,_helper_security__WEBPACK_IMPORTED_MODULE_2__.emailVal)(email)) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("setLoader").style.display = "block";
      localStorage.setItem('redirect', '/login/changePW');
      (0,_helper_http__WEBPACK_IMPORTED_MODULE_1__.postFormData)("/login/forgot", "forgotPassword", "/login/code");
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};

// CHECK IF ID(SUBMIT) before submitting form

document.addEventListener('DOMContentLoaded', function () {
  var submitForm = document.getElementById('submit');
  if (submitForm) {
    submitForm.addEventListener('click', forgotPasswordSubmission);
  }
});

/***/ }),

/***/ "./resources/asset/js/components/helper/http.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/helper/http.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkCookie: function() { return /* binding */ checkCookie; },
/* harmony export */   getApiData: function() { return /* binding */ getApiData; },
/* harmony export */   getCookie: function() { return /* binding */ getCookie; },
/* harmony export */   getMultipleApiData: function() { return /* binding */ getMultipleApiData; },
/* harmony export */   postFormData: function() { return /* binding */ postFormData; },
/* harmony export */   postMultipleApiData: function() { return /* binding */ postMultipleApiData; },
/* harmony export */   setCookie: function() { return /* binding */ setCookie; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios-retry */ "./node_modules/axios-retry/lib/esm/index.js");





// import Cookies from 'js-cookie'

(0,axios_retry__WEBPACK_IMPORTED_MODULE_3__["default"])(axios__WEBPACK_IMPORTED_MODULE_4__["default"], {
  retries: 3
});

/**
 * 
* Sends form data via POST request.
 * @param {string} url - The URL to post the data to.
 * @param {string} formId - The ID or class of the form.
 * @param {string|null} redirect - The page to redirect to after successful submission.
 * @param {string|null} css - The CSS framework to use for notification styling (e.g., 'W3css', 'bulma').
 NOTICE:::Make sure you set the notification id as the formId_notification
 */
var postFormData = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(url, formId) {
    var redirect,
      css,
      notificationForm,
      notificationId,
      form,
      formEntries,
      options,
      response,
      _response$data,
      successClass,
      idSetFromHttp,
      famCodeSetFromHttp,
      dbHttpResult,
      redirectDelay,
      _error$response,
      _error$response$data,
      errorClass,
      errorMessage,
      _args = arguments,
      _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          redirect = _args.length > 2 && _args[2] !== undefined ? _args[2] : null;
          css = _args.length > 3 && _args[3] !== undefined ? _args[3] : null;
          notificationForm = "".concat(formId, "_notification");
          notificationId = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(notificationForm);
          if (notificationId) {
            _context.next = 1;
            break;
          }
          throw new Error('Notification element not found');
        case 1:
          // Cleanup previous notification styles
          notificationId.style.display = 'none';
          ['is-danger', 'is-success', 'w3-red', 'w3-green', 'bg-danger', 'bg-success'].forEach(function (cls) {
            return notificationId.classList.remove(cls);
          });

          // extract the form entriesËËË
          form = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(formId);
          if (form) {
            _context.next = 2;
            break;
          }
          throw new Error('Form element not found');
        case 2:
          formEntries = new FormData(form);
          formEntries.delete('submit');
          formEntries.delete('checkbox_id');
          options = {
            baseURL: '/',
            // Adjust to your API base URL
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN',
            withCredentials: true // Ensure cookies (e.g., XSRF token) are sent
          }; // AXIOS POST FUNCTIONALITY
          _context.prev = 3;
          _context.next = 4;
          return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post(url, formEntries, options);
        case 4:
          response = _context.sent;
          if (!(response.status < 200 || response.status >= 300)) {
            _context.next = 5;
            break;
          }
          throw new Error(((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'Request failed');
        case 5:
          successClass = getNotificationClassByCSS(css || 'bulma', 'green'); // check if response.data.message is an array
          idSetFromHttp = null;
          famCodeSetFromHttp = null;
          dbHttpResult = null;
          if (!(response.data && typeof response.data.message === 'object')) {
            _context.next = 9;
            break;
          }
          idSetFromHttp = response.data.message.id || null;
          famCodeSetFromHttp = response.data.message.famCode || null;
          dbHttpResult = response.data.message.outcome || null;
          if (idSetFromHttp) {
            _context.next = 6;
            break;
          }
          throw new Error('idSetFromHttp is missing');
        case 6:
          if (dbHttpResult) {
            _context.next = 7;
            break;
          }
          throw new Error('dbHttpResult is missing');
        case 7:
          if (famCodeSetFromHttp) {
            _context.next = 8;
            break;
          }
          throw new Error('famCodeSetFromHttp is missing');
        case 8:
          _context.next = 10;
          break;
        case 9:
          dbHttpResult = response.data.message;
        case 10:
          // Set sessionStorage items if not already set
          if (!sessionStorage.getItem('idSetFromHttp')) sessionStorage.setItem('idSetFromHttp', idSetFromHttp);
          if (!sessionStorage.getItem('famCodeSetFromHttp')) sessionStorage.setItem('famCodeSetFromHttp', famCodeSetFromHttp);
          processFormDataAction(successClass, dbHttpResult, notificationId);
          if (redirect) {
            redirectDelay = 2000; // Configurable delay in ms
            setTimeout(function () {
              window.location.assign(redirect);
            }, redirectDelay);
          }
          _context.next = 12;
          break;
        case 11:
          _context.prev = 11;
          _t = _context["catch"](3);
          errorClass = getNotificationClassByCSS(css || 'bulma', 'red');
          errorMessage = ((_error$response = _t.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.error) || _t.request || 'An unknown error occurred';
          processFormDataAction(errorClass, errorMessage, notificationId);
        case 12:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[3, 11]]);
  }));
  return function postFormData(_x, _x2) {
    return _ref.apply(this, arguments);
  };
}();

/**
 * Process form data action.
 * @param {string} cssClass - The CSS class for the notification.
 * @param {string} message - The notification message.
 */
var processFormDataAction = function processFormDataAction(cssClass, message, formNotificationId) {
  if (formNotificationId) {
    formNotificationId.style.display = 'block';
    formNotificationId.classList.add(cssClass);
    var errorElement = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('error');
    if (errorElement) {
      errorElement.scrollIntoView({
        behavior: 'smooth'
      });
      errorElement.innerHTML = message;
    }
    var loader = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('setLoader');
    if (loader) loader.classList.remove('loader');
  } else {
    (0,_global__WEBPACK_IMPORTED_MODULE_2__.log)('Notification element not found');
  }
};

/**
 * Get the notification class based on the CSS framework.
 * @param {string|null} css - The CSS framework to use for notification styling.
 * @param {string} status - The status of the notification ('green' or 'red').
 * @returns {string} - The corresponding CSS class.
 */
var getNotificationClassByCSS = function getNotificationClassByCSS(css, status) {
  switch (css) {
    case 'W3css':
      return status === 'green' ? 'w3-green' : 'w3-red';
    case 'bulma':
      return status === 'green' ? 'is-success' : 'is-danger';
    case 'bootstrap':
      return status === 'green' ? 'bg-success' : 'bg-danger';
    default:
      return status === 'green' ? 'bg-success' : 'bg-danger';
  }
};

/**
 * 
 * @param { the url you want to get} URL 
 * @returns 
 // now we can use that data from the outside!
axiosTest()
    .then(data => {
        response.json({ message: 'Request received!', data })
    })
    .catch(err => console.log(err))
 */

var getApiData = /*#__PURE__*/function () {
  var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(URL) {
    var token,
      config,
      fetch,
      _args2 = arguments,
      _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          token = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : null;
          _context2.prev = 1;
          config = {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          };
          _context2.next = 2;
          return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get(URL, config);
        case 2:
          fetch = _context2.sent;
          return _context2.abrupt("return", fetch.data);
        case 3:
          _context2.prev = 3;
          _t2 = _context2["catch"](1);
          return _context2.abrupt("return", _t2);
        case 4:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[1, 3]]);
  }));
  return function getApiData(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
var getMultipleApiData = /*#__PURE__*/function () {
  var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(url1, url2) {
    var token,
      config,
      fetch,
      _args3 = arguments,
      _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          token = _args3.length > 2 && _args3[2] !== undefined ? _args3[2] : null;
          _context3.prev = 1;
          config = {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          };
          _context3.next = 2;
          return axios__WEBPACK_IMPORTED_MODULE_4__["default"].all([axios__WEBPACK_IMPORTED_MODULE_4__["default"].get(url1, config), axios__WEBPACK_IMPORTED_MODULE_4__["default"].get(url2, config)]);
        case 2:
          fetch = _context3.sent;
          return _context3.abrupt("return", fetch);
        case 3:
          _context3.prev = 3;
          _t3 = _context3["catch"](1);
          return _context3.abrupt("return", _t3);
        case 4:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[1, 3]]);
  }));
  return function getMultipleApiData(_x4, _x5) {
    return _ref3.apply(this, arguments);
  };
}();

// build a function to post multiple api form data

var postMultipleApiData = /*#__PURE__*/function () {
  var _ref4 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee4(url1, url2, formData) {
    var token,
      config,
      fetch,
      _args4 = arguments,
      _t4;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context4) {
      while (1) switch (_context4.prev = _context4.next) {
        case 0:
          token = _args4.length > 3 && _args4[3] !== undefined ? _args4[3] : null;
          _context4.prev = 1;
          config = {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          };
          _context4.next = 2;
          return axios__WEBPACK_IMPORTED_MODULE_4__["default"].all([axios__WEBPACK_IMPORTED_MODULE_4__["default"].post(url1, formData, config), axios__WEBPACK_IMPORTED_MODULE_4__["default"].post(url2, formData, config)]);
        case 2:
          fetch = _context4.sent;
          return _context4.abrupt("return", fetch);
        case 3:
          _context4.prev = 3;
          _t4 = _context4["catch"](1);
          return _context4.abrupt("return", _t4);
        case 4:
        case "end":
          return _context4.stop();
      }
    }, _callee4, null, [[1, 3]]);
  }));
  return function postMultipleApiData(_x6, _x7, _x8) {
    return _ref4.apply(this, arguments);
  };
}();
/**
 * 
 * @param { name} cname 
 * @param {* value} cvalue 
 * @param {* no of days 365} exdays 
 */
var setCookie = function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
};
var getCookie = function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
};
var checkCookie = function checkCookie() {
  var user = getCookie("username");
  if (user != "") {
    alert("Welcome again " + user);
  } else {
    user = prompt("Please enter your name:", "");
    if (user != "" && user != null) {
      setCookie("username", user, 365);
    }
  }
};

/***/ }),

/***/ "./resources/asset/js/components/helper/security.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/helper/security.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   emailVal: function() { return /* binding */ emailVal; },
/* harmony export */   showPassword: function() { return /* binding */ showPassword; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");

var showPassword = function showPassword(inputId) {
  var y = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputId);
  if (y.type === "password") {
    y.type = "text";
  } else {
    y.type = "password";
  }
};

/**
 * 
 * @param {* } email 
 * @returns 1 if there is an error
 */
var emailVal = function emailVal(email) {
  var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
  var error;
  var msg = "<li style=color:'red';> Please enter a valid email</li>";
  if (email.match(emailExp) === null) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('email_error').innerHTML = msg;
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('email_error').style.color = "red";
    error = 1;
    return error;
  }
};

/***/ })

}]);
//# sourceMappingURL=forgotPwd.js.map