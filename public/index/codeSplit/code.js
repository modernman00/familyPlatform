(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/code"],{

/***/ "./resources/asset/js/components/generateCode/Code.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/generateCode/Code.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");



 // block the setLoader div

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("setLoader").style.display = "none";

var LoginCode = function LoginCode(e) {
  try {
    e.preventDefault();
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('codeForm_notification').classList.remove('is-danger');
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = "";
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('setLoader').style.display = "block";
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('loader').classList.add('loader'); // get the direct from the login script (getstorage)

    var location = localStorage.getItem('redirect');
    (0,_helper_http__WEBPACK_IMPORTED_MODULE_1__.postFormData)("/login/code", "codeForm", location);
    localStorage.removeItem('redirect');
  } catch (err) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  }
};

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submit').addEventListener('click', LoginCode);

/***/ }),

/***/ "./resources/asset/js/components/helper/http.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/helper/http.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "postFormData": () => (/* binding */ postFormData),
/* harmony export */   "getApiData": () => (/* binding */ getApiData),
/* harmony export */   "getMultipleApiData": () => (/* binding */ getMultipleApiData),
/* harmony export */   "setCookie": () => (/* binding */ setCookie),
/* harmony export */   "getCookie": () => (/* binding */ getCookie),
/* harmony export */   "checkCookie": () => (/* binding */ checkCookie)
/* harmony export */ });
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios-retry */ "./node_modules/axios-retry/index.js");
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(axios_retry__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var js_cookie__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! js-cookie */ "./node_modules/js-cookie/src/js.cookie.js");
/* harmony import */ var js_cookie__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(js_cookie__WEBPACK_IMPORTED_MODULE_4__);


function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }





axios_retry__WEBPACK_IMPORTED_MODULE_3___default()((axios__WEBPACK_IMPORTED_MODULE_2___default()), {
  retries: 3
});
/**
 * 
 * @param {the url to post the data to} url 
 * @param {* the id or class of the form} formElement 
 * @param {* the redirect to another page /code or /admin/register} redirect 
 NOTICE:::Make sure you set the notification id as the formId_notification
 */

var postFormData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee(url, formId) {
    var redirect,
        css,
        notificationId,
        processFormDataAction,
        addClassByCSS,
        form,
        formEntries,
        options,
        _args = arguments;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            redirect = _args.length > 2 && _args[2] !== undefined ? _args[2] : null;
            css = _args.length > 3 && _args[3] !== undefined ? _args[3] : null;
            notificationId = "".concat(formId, "_notification"); // the notification function

            processFormDataAction = function processFormDataAction(addClass, data) {
              (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(notificationId).style.display = "block"; // unblock the notification

              (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(notificationId).classList.add(addClass); // add the success class

              (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('error').innerHTML = data; // error element

              (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('loader').classList.remove('loader'); // remove loader
            };

            addClassByCSS = function addClassByCSS(theCss, status) {
              if (theCss === "W3css") {
                return status == 'green' ? "w3-green" : "w3-red";
              } else if (theCss === 'bulma') {
                return status == 'green' ? "is-success" : "is-danger";
              } else {
                return status == 'green' ? "is-success" : "is-danger";
              }
            }; // extract the form entries


            form = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(formId);
            formEntries = new FormData(form);
            formEntries["delete"]('submit');
            formEntries["delete"]('checkbox_id'); // formEntries.delete('token')

            options = {
              xsrfCookieName: 'XSRF-TOKEN',
              xsrfHeaderName: 'X-XSRF-TOKEN'
            }; // AXIOS POST FUNCTIONALITY

            _context.next = 12;
            return axios__WEBPACK_IMPORTED_MODULE_2___default().post(url, formEntries, options).then(function (response) {
              // TO DECIDE ON THE NOTIFICATION
              var theClass = addClassByCSS(css, 'green');
              processFormDataAction(theClass, response.data.message); // set timer to redirect to the homepage

              // set timer to redirect to the homepage
              if (redirect) {
                setTimeout(function () {
                  //window.location.replace(redirect)
                  window.location.assign(redirect);
                }, 2000);
              } //it clears all the contents
              // formData.clearHtml()

            })["catch"](function (error) {
              (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(error);
              var theClass = addClassByCSS(css, 'red');

              if (error.response) {
                (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(error.response);
                processFormDataAction(theClass, error.response.data.message);
              }
            });

          case 12:
          case "end":
            return _context.stop();
        }
      }
    }, _callee);
  }));

  return function postFormData(_x, _x2) {
    return _ref.apply(this, arguments);
  };
}();
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
  var _ref2 = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee2(URL) {
    var token,
        config,
        fetch,
        _args2 = arguments;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee2$(_context2) {
      while (1) {
        switch (_context2.prev = _context2.next) {
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
            _context2.next = 5;
            return axios__WEBPACK_IMPORTED_MODULE_2___default().get(URL, config);

          case 5:
            fetch = _context2.sent;
            return _context2.abrupt("return", fetch.data);

          case 9:
            _context2.prev = 9;
            _context2.t0 = _context2["catch"](1);
            return _context2.abrupt("return", _context2.t0);

          case 12:
          case "end":
            return _context2.stop();
        }
      }
    }, _callee2, null, [[1, 9]]);
  }));

  return function getApiData(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
var getMultipleApiData = /*#__PURE__*/function () {
  var _ref3 = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee3(url1, url2) {
    var token,
        config,
        fetch,
        _args3 = arguments;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee3$(_context3) {
      while (1) {
        switch (_context3.prev = _context3.next) {
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
            _context3.next = 5;
            return axios__WEBPACK_IMPORTED_MODULE_2___default().all([axios__WEBPACK_IMPORTED_MODULE_2___default().get(url1, config), axios__WEBPACK_IMPORTED_MODULE_2___default().get(url2, config)]);

          case 5:
            fetch = _context3.sent;
            return _context3.abrupt("return", fetch);

          case 9:
            _context3.prev = 9;
            _context3.t0 = _context3["catch"](1);
            return _context3.abrupt("return", _context3.t0);

          case 12:
          case "end":
            return _context3.stop();
        }
      }
    }, _callee3, null, [[1, 9]]);
  }));

  return function getMultipleApiData(_x4, _x5) {
    return _ref3.apply(this, arguments);
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

/***/ })

}]);