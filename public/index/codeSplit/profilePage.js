(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/profilePage"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FormHelper)
/* harmony export */ });
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");


function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var FormHelper = /*#__PURE__*/function () {
  function FormHelper(data) {
    _classCallCheck(this, FormHelper);

    this.data = data;
    this.error = [];
    this.result = 0;
  }

  _createClass(FormHelper, [{
    key: "id",
    value: function id(x) {
      return document.getElementById(x);
    }
    /**
     * general validation; check empty status, at least a single input, mobile length, white space
     */

  }, {
    key: "getData",
    value: function getData() {
      return this.data;
    }
  }, {
    key: "massValidate",
    value: function massValidate() {
      var _this = this;

      // const reg = /[a-zA-Z0-9./@]/g;
      this.data.forEach(function (et) {
        var _iterator = _createForOfIteratorHelper(et),
            _step;

        try {
          for (_iterator.s(); !(_step = _iterator.n()).done;) {
            var post = _step.value;

            // capture the error to a variable
            var errMsg = _this.id("".concat(post.name, "_error")); // console.log(post)
            // rid it off the submit and token


            if (post.name == 'submit' || post.name == 'button' || post.name == 'token' || post.name == 'cancel' || post.name == "checkbox_id") {
              continue;
            } // check if there is no value


            var postName = post.name.replace('_', ' ');

            if (postName == "spouseName" || postName == "spouseMobile" || postName == "spouseEmail" || postName == "fatherMobile" || postName == "fatherEmail" || postName == "motherMobile" || postName == "motherEmail") {
              if (post.value === "") {
                post.value = "Not Provided";
              }
            }

            if (post.value === '' || post.value === 'select') {
              errMsg.innerHTML = "* cannot be left empty";
              errMsg.style.color = "red";

              _this.error.push("".concat(postName.toUpperCase(), " cannot be left empty"));
            } else {
              _this.result = 1;
            }

            var checkRegex = (0,_helper_general__WEBPACK_IMPORTED_MODULE_0__.matchRegex)(post.value);

            if (checkRegex === false) {
              _this.error.push("There is a problem with you entry for ".concat(postName.toUpperCase(), "'s question"));

              errMsg.innerHTML = "* There is a problem with you entry for ".concat(postName.toUpperCase(), "'s question");
            }
          }
        } catch (err) {
          _iterator.e(err);
        } finally {
          _iterator.f();
        }
      });
    }
  }, {
    key: "emailVal",
    value: function emailVal() {
      var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
      var msg = "<li style=color:'red';> Please enter a valid email</li>";
      var email = this.id('email_id').value;

      if (email.match(emailExp) === null) {
        this.id('email_error').innerHTML = msg;
        this.id('email_error').style.color = "red";
        this.error.push(msg);
      }
    }
  }, {
    key: "clearError",
    value: function clearError() {
      var _this2 = this;

      this.error = []; // empty the array 

      this.data.forEach(function (el) {
        var _iterator2 = _createForOfIteratorHelper(el),
            _step2;

        try {
          var _loop = function _loop() {
            var post = _step2.value;

            if (post.id == 'submit' || post.name == 'token' || post.name == 'submit' || post.name == 'checkbox') {
              return "continue";
            }

            _this2.id(post.id).addEventListener('change', function () {
              _this2.id("".concat(post.name, "_error")).innerHTML = '';
            });

            if (post.value != 'select') {
              _this2.id(post.id).addEventListener('keyup', function () {
                _this2.id("".concat(post.name, "_error")).innerHTML = '';
              });
            } else {
              _this2.id(post.id).addEventListener('change', function () {
                _this2.id("".concat(post.name, "_error")).innerHTML = '';
              });
            }
          };

          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            var _ret = _loop();

            if (_ret === "continue") continue;
          }
        } catch (err) {
          _iterator2.e(err);
        } finally {
          _iterator2.f();
        }
      });
    }
  }, {
    key: "clearHtml",
    value: function clearHtml() {
      this.data.forEach(function (el) {
        var _iterator3 = _createForOfIteratorHelper(el),
            _step3;

        try {
          for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
            var post = _step3.value;

            if (post.id == 'submit' || post.name == 'submit' || post.name == 'checkbox') {
              continue;
            }

            post.value = "";
          }
        } catch (err) {
          _iterator3.e(err);
        } finally {
          _iterator3.f();
        }
      });
    }
    /**
     *
     * @param {input is the id of the input/ this is an array [as, it, it]} input
     * @param {* this is the max policy and it must be an integer} maxi
     */

  }, {
    key: "realTimeCheckLen",
    value: function realTimeCheckLen(input, maxi) {
      var _this3 = this;

      try {
        var _loop2 = function _loop2(i) {
          var theData = _this3.id("".concat(input[i], "_id"));

          if (theData == "") throw new Error("empty dataInput");
          var max = maxi[i];

          var error = _this3.id("".concat(input[i], "_error"));

          if (theData) theData.maxLength = parseInt(max + 1);
          theData.addEventListener('keyup', function () {
            error.innerHTML = theData.value.length > max ? "You have reach the maximum limit" : "";
            _this3.id("".concat(input[i], "_help")).style.color = 'red';
            _this3.id("".concat(input[i], "_help")).style.fontSize = '10px';
            error.style.color = 'red';
            setTimeout(function () {
              _this3.id("".concat(input[i], "_help")).style.display = 'none';
            }, 5000);
          });
        };

        for (var i = 0; i < input.length; i++) {
          _loop2(i);
        }
      } catch (error) {
        console.log(error.message);
      }
    }
    /**
     * the id for the password error should be password_help
     * the id for your confirm pasword should confirm_password
     * it will return an error message to the password_help input
     */

  }, {
    key: "matchInput",
    value: function matchInput(first, second) {
      var error, firstInput, secondInput;
      error = this.id("".concat(second, "_error"));
      firstInput = this.id(first + '_id');
      secondInput = this.id(second + '_id');
      secondInput.addEventListener('keyup', function () {
        error.innerHTML = secondInput.value !== firstInput.value ? 'Your passwords do not match' : "";
      });
    }
    /**
     *
     * @param {the id of the input you want to inject to/ this is an array} idArray
     * @param {*the comment or questions you want o inject} html
     */

  }, {
    key: "injectData",
    value: function injectData(idArray, html) {
      var idData;

      for (var i = 0; i < idArray.length; i++) {
        idData = this.id(idArray[i]);
        idData.innerHTML = html[i];
      }
    }
    /**
     *
     * @param {this is an id and its value is for duplication} firstInput
     * @param {* another id that accepts the value of the firstInput} takeFirstInput
     */

  }, {
    key: "duplicate",
    value: function duplicate(giveInput, takeInput) {
      var giver, taker;
      giver = this.id(giveInput);
      taker = this.id(takeInput);
      giver.addEventListener('keyup', function () {
        taker.value = giver.value;
      });
    }
    /**
     *
     * @param {current input that is being type to. the value is what will be checked realtime. the id is needed} input
     * @param {* the url to get the info to . example is /search?hint} url
     * @param {enter the id of the output element} output
     */

  }, {
    key: "realTimeServer",
    value: function realTimeServer(input, url, outputId) {
      var theInput, inputVal, output;
      theInput = this.id(input);
      output = this.id(outputId);
      theInput.addEventListener('keyup', function () {
        inputVal = theInput.value;

        if (inputVal == 0) {
          output.innerHTML = "";
          return;
        } else {
          var xmlhttp = new XMLHttpRequest();

          xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
              output.innerHTML = this.responseText;
            }
          };

          xmlhttp.open("GET", "".concat(url, "=").concat(inputVal), true);
          xmlhttp.send();
        }
      });
    }
  }, {
    key: "isChecked",
    value: function isChecked(yesId, noId, hiddenInput) {
      var _this4 = this;

      var checked = function checked() {
        if (_this4.id(yesId).checked) {
          alert('check');
          _this4.id(hiddenInput).innerHTML = 'checked';
        } else if (_this4.id(noId).checked) {
          _this4.id(hiddenInput).innerHTML = 'checked';
        }
      };

      this.id(yesId).addEventListener('click', checked);
      this.id(noId).addEventListener('click', checked);
    }
  }, {
    key: "previousAddress",
    value: function previousAddress() {
      var _this5 = this;

      var timeAddy = this.id('time_at_address_id');
      var prevAddy = this.id('previous_address_class');

      var showPrev = function showPrev() {
        if (timeAddy.value != '3 years+') {
          prevAddy.style.display = 'block';
          _this5.id('previous_address_help').innerHTML = "Please enter your full address: House No, Street Name, Town/City and Post Code";
        } else {
          prevAddy.style.display = 'none';
        }
      };

      timeAddy.addEventListener('change', showPrev);
    }
  }]);

  return FormHelper;
}();



/***/ }),

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "loaderIconBootstrap": () => (/* binding */ loaderIconBootstrap),
/* harmony export */   "loaderIcon": () => (/* binding */ loaderIcon),
/* harmony export */   "loaderIconBulma": () => (/* binding */ loaderIconBulma),
/* harmony export */   "removeDiv": () => (/* binding */ removeDiv),
/* harmony export */   "createAndAppendElement": () => (/* binding */ createAndAppendElement),
/* harmony export */   "autoCompleter": () => (/* binding */ autoCompleter),
/* harmony export */   "distinctValue": () => (/* binding */ distinctValue),
/* harmony export */   "checkBox": () => (/* binding */ checkBox),
/* harmony export */   "isChecked": () => (/* binding */ isChecked),
/* harmony export */   "matchRegex": () => (/* binding */ matchRegex),
/* harmony export */   "matchInput": () => (/* binding */ matchInput)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! autocompleter */ "./node_modules/autocompleter/autocomplete.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(autocompleter__WEBPACK_IMPORTED_MODULE_1__);


function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }



var loaderIconBootstrap = function loaderIconBootstrap() {
  return "<div class=\"spinner-grow text-primary\" role=\"status\">\n        <span class=\"sr-only\">Loading...</span>\n        </div>";
};
var loaderIcon = function loaderIcon() {
  return "<div class=\"loader\"></div>";
};
var loaderIconBulma = function loaderIconBulma() {
  return "<div class=\"is-loading\"></div>";
};
var removeDiv = function removeDiv(div_id) {
  var div = document.getElementById(div_id);

  if (div) {
    return div.remove();
  }
};
var createAndAppendElement = function createAndAppendElement(elementType, setId, parent) {
  var setClass = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  var newDiv = document.createElement(elementType);
  newDiv.setAttribute('id', setId);
  newDiv.setAttribute('class', "field ".concat(setClass));
  var parentDiv = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(parent);
  return parentDiv.appendChild(newDiv);
};
/**
 * 
 * @param {the id of the input} inputId 
 * @param {the api data or array data} data 
 * @param { filterby is the data.filterby }
 */

var autoCompleter = function autoCompleter(inputId, data) {
  autocompleter__WEBPACK_IMPORTED_MODULE_1___default()({
    input: inputId,
    fetch: function fetch(text, update) {
      text = text.toLowerCase(); // you can also use AJAX requests instead of preloaded data

      var suggestions = data.filter(function (n) {
        return n.firstName.toLowerCase().startsWith(text);
      });
      update(suggestions);
    },
    onSelect: function onSelect(item) {
      input.value = item.firstName;
    }
  });
};
var distinctValue = function distinctValue(array) {
  return _toConsumableArray(new Set(array));
};
var checkBox = function checkBox(subject) {
  return "<div class=\"control\"> \n        <label class=\"radio\">\n          <input type=\"radio\" name=\"send".concat(subject, "\" value=\"yes\" id=").concat(subject, "Yes> Yes \n        </label>\n        <label class=\"radio\"> \n          <input type=\"radio\" name=\"send").concat(subject, "\" value=\"no\" id=").concat(subject, "No> No \n        </label>\n      </div>");
};
var isChecked = function isChecked(name, fn) {
  var yesId = "".concat(name, "Yes");
  var noId = "".concat(name, "No");

  var checked = function checked() {
    if ((0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(yesId).checked) {
      alert('check');
      fn();
    } else if ((0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(noId).checked) {
      alert('check No');
    }
  };

  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(yesId).addEventListener('click', checked);
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(noId).addEventListener('click', checked);
};
var matchRegex = function matchRegex(data) {
  if (data) {
    if (data != "Not Provided") {
      var regex = /[<?/>]+/g;
      var result = data.match(regex);
      if (result === null) return true;
    }
  }
};
/**
 * 
 * @param { id of the first element} first 
 * @param {* id of the second element} second 
 * @param {* error id - if error - where to show it} err 
 */

var matchInput = function matchInput(first, second, err) {
  var error, firstInput, secondInput;
  error = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(err);
  firstInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(first);
  secondInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(second);
  secondInput.addEventListener('keyup', function () {
    if (secondInput.value !== firstInput.value) {
      error.innerHTML = 'Your passwords do not match';
      error.style.color = "red";
    } else {
      error.innerHTML = "The password is matched: <i class='fa fa-check' aria-hidden='true'></i>";
      error.style.color = "green";
    }
  });
};

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

/***/ "./resources/asset/js/components/profilePage/allEvents.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/allEvents.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");





try {
  // getApiData()
  var newLikeCounterVal = 0; // CLICK EVENT get the comment and like button from the document

  document.onclick = function (e) {
    var elementId = e.target.id;
    var postId = e.target.name; // const eClass = e.target
    // log(eClass)

    if (elementId.includes("likeButton")) {
      // replace button with Counter to get the span id 
      var likeCounterId = elementId.replace('Button', 'Counter');
      var likeCounterVal = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(likeCounterId).innerHTML; // get the post like using the post id

      (0,_helper_http__WEBPACK_IMPORTED_MODULE_1__.getApiData)("/profileCard/getLikes?postId=".concat(postId, "&count=").concat(likeCounterVal)); // add one to the result 

      newLikeCounterVal = parseInt(likeCounterVal) + 1;
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(likeCounterId).innerHTML = newLikeCounterVal; // Make the comment form to appear onclick
    } else if (elementId.includes("initComment")) {
      var commentFormId = elementId.replace('init', 'form');
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(commentFormId).style.display = "block"; // Submit function for comment using POST API
    } else if (elementId.includes("submitComment")) {
      // get the specific form id
      e.preventDefault();
      var idForm = elementId.replace("submit", "form");
      (0,_helper_http__WEBPACK_IMPORTED_MODULE_1__.postFormData)("/postCommentProfile", idForm, "/member/ProfilePage");
      location.reload(); // submit the post 
    } else if (elementId.includes("submitPost")) {
      (0,_helper_http__WEBPACK_IMPORTED_MODULE_1__.postFormData)("/member/profilePage/post", "formPostMessageModal"); // make the post modal display disappear

      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'none'; // location.reload();   
    }
  };
} catch (e) {
  showError(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/createEvent.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/createEvent.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");





var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_1__.default(formInputArr);
formData.clearError();

var displayNone = function displayNone() {
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'none';
};

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('cancelModal').addEventListener('click', displayNone);

var process = function process(e) {
  try {
    e.preventDefault();
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventModalForm_notification').classList.remove('w3-red'); // remove the danger class from the notification

    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = "";
    formData.massValidate(); // log(formData.error)

    if (formData.error.length <= 0) {
      // initiate the api
      //  qSel('.submitEventModal').type = 'submit'
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('loader').classList.add('loader'); // start the loader element

      (0,_helper_http__WEBPACK_IMPORTED_MODULE_2__.postFormData)("/member/profilePage", 'eventModalForm', null, "w3css"); //window.location.replace("/member/profilePage")
    } else {
      alert('The form cannot be submitted. Please check the errors');
      formData.clearError();
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitEventModal').addEventListener('click', process);

/***/ }),

/***/ "./resources/asset/js/components/profilePage/homePage.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/homePage.js ***!
  \***************************************************************/
/***/ (() => {

// import { id, log } from '../global'
// import { getApiData } from '../helper/http'
// import Cookies from 'js-cookie'
// import axios from 'axios'
// const url = "/member/ProfilePage"
// const token = Cookies.get('waletoken') 
// log(token);
// // const result = getApiData(url, token)
// // log(result);
// const res = await axios.get('/member/ProfilePage', { 
//     headers : { 'Authorization' : 'Bearer ' + token}
// })
// log(res)

/***/ }),

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "allPost": () => (/* binding */ allPost),
/* harmony export */   "appendNewPost": () => (/* binding */ appendNewPost)
/* harmony export */ });
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



var name = function name(fullName) {
  return "<h5 id=\"fullName\"> ".concat(fullName, "</h5>");
};

var timing = function timing(postedAt) {
  return "<span class=\"w3-right w3-opacity\"> ".concat(postedAt, " ago</span>");
};

var nameImgTiming = function nameImgTiming(data) {
  var img = data.img ? "/img/profile/".concat(data.img) : "/avatar/avatarF.png";
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(data.img, "&pID=").concat(data.post_no, "&path=profile\">\n        <img src=").concat(img, " alt=\"img\" class=\"w3-left w3-circle w3-margin-right\" style=\"width:60px\">\n        </a>\n        ").concat(timing(data.timing), " ").concat(name(data.fullName));
};

var commentForm = function commentForm(data) {
  return " <p id=\"formComment".concat(data.post_no, "_notification\"></p>\n\n  <form action=\"/postCommentProfile\" method=\"post\" id=\"formComment").concat(data.post_no, "\" style=\"display:none\" enctype=\"multipart/form-data\">\n\n    <input name='post_no' type=\"hidden\" name=\"").concat(data.post_no, "\" value=").concat(data.post_no, " />\n\n    <input class=\"w3-input w3-border w3-round-large inputComment\" type=\"text\" placeholder=\"Write a comment\"\n      id=\"inputComment").concat(data.post_no, " \" name='comment'>\n\n    <br>\n\n    <button type='submit' id=\"submitComment").concat(data.post_no, "\" class=\"w3-button w3-green submitComment\">Submit</button>\n  </form>");
}; // const postImg = () => {
// }


var button = function button(data) {
  return "<button type=\"button\" id=\"likeButton".concat(data.post_no, "\" name=\"").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-green w3-margin-bottom\">\n    <em class=\"fa fa-thumbs-up\"></em>\n    \xA0Like <b><span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">").concat(data.post_likes, "</span></b>\n  </button>\n   <button type=\"button\" id=\"initComment").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-theme-d2 w3-margin-bottom\"><em class=\"fa fa-comment\"></em> Comment </button>\n    ");
};

var showPostImg = function showPostImg(data) {
  var postImg = [];

  for (var i = 0; i < 6; i++) {
    var images = 'post_img' + i;

    if (data[images]) {
      postImg.push(data[images]);
    }
  }

  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    return "<a href=\"/profilepage/img?dir=img&pics=".concat(imgElement, "&pID=").concat(postNo, "&path=post\"> <div class=\"w3-half\">\n        <img src=\"/img/post/").concat(imgElement, "\" style=\"width:100%\" alt=\"images").concat(i, "\"\n          class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat(i, "\">\n      </div>\n    </a>");
  };

  return "<div class=\"w3-row-padding\" style=\"margin:0 -16px\">\n\n  ".concat(postImg.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }), "\n    <br>\n  </div>");
};

var html = function html(el) {
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin\"><br>\n\n      ".concat(nameImgTiming(el), "\n\n    <hr class=\"w3-clear\">\n\n    <p class=\"postFont\"> ").concat(el.postMessage, " </p>\n\n     ").concat(showPostImg(el), "\n\n    ").concat(button(el), "\n\n    ").concat(commentForm(el), "\n  </div><br><br>");
};

var allPost = function allPost(el) {
  if (el) {
    var postHtml = html(el); // the function to be activated

    /**
     * ! Deprecated 
      createAndAppendElement('div', 'postIt', 'messagePost', 'postIt')
     */

    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('beforeend', postHtml); // ! should be reviewed
  } else {
    return "<p> Sorry, we could find the data</p>";
  }
}; // ? is it still needed?

var appendNewPost = function appendNewPost(el) {
  if (!el) {
    return false;
  }

  var commentForm1 = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  var inputComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  var submitComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));

  if (!commentForm1 || !inputComment || !submitComment) {
    var appendHTML = html(el);
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('afterbegin', appendHTML);
  }
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/img.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/img.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePics').addEventListener('click', function () {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formProfilePics').style.display = "block";
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/index.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/index.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _loadPost__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./loadPost */ "./resources/asset/js/components/profilePage/loadPost.js");
/* harmony import */ var _homePage__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./homePage */ "./resources/asset/js/components/profilePage/homePage.js");
/* harmony import */ var _homePage__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_homePage__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/profilePage/modal.js");
/* harmony import */ var _img__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./img */ "./resources/asset/js/components/profilePage/img.js");
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/profilePage/allEvents.js");
/* harmony import */ var _createEvent__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./createEvent */ "./resources/asset/js/components/profilePage/createEvent.js");


 // const postDataI = () => {
//     getApiData(`/post/getAllPost`)
//             .then(data => {
//                 console.log(data.message)
//                 data.message.map(el => { 
//                     allPost(el)
//                 })
//             })
//             .catch(err => {console.log(err)})
// }





 // import { allPost } from "../profilePage/html"
// var source = new EventSource("/post/getAllPost");
//     source.onmessage = function (event) {
//         console.log(event)
//         const data = JSON.parse(event.data)
//         console.log(data)
//         data.map(el => allPost(el))
//     }

/***/ }),

/***/ "./resources/asset/js/components/profilePage/loadPost.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/loadPost.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _profilePage_html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../profilePage/html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");



 // get the postdata which returns a promise

var postData = (0,_helper_http__WEBPACK_IMPORTED_MODULE_3__.getApiData)("/post/getAllPost"); // then get the result of the promise with the then function

postData.then(function (response) {
  return response.message.map(function (el) {
    return (0,_profilePage_html__WEBPACK_IMPORTED_MODULE_2__.allPost)(el);
  });
})["catch"](function (err) {
  return (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(err);
});
var serverConnection = new EventSource("/post/getAllPost/update");

var newPostFn = function newPostFn(e) {
  if (e.origin != "http://olaogun.dev.com") {
    throw new Error("What is your origin?");
  }

  var data = JSON.parse(e.data);
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(e.lastEventId);
  return data.forEach(function (el) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(el.post_no);
    (0,_profilePage_html__WEBPACK_IMPORTED_MODULE_2__.appendNewPost)(el);
  });
};

serverConnection.onmessage = function (e) {
  return newPostFn(e);
}; //serverConnection.addEventListener = ('newPost', newPostFn)

/***/ }),

/***/ "./resources/asset/js/components/profilePage/modal.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/modal.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


 // import { postFormData } from "../helper/http"

try {
  // NEW MESSAGE MODAL
  var showModal = function showModal() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'block';
  }; // CREATE EVENT MODAL


  var showEvent = function showEvent() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'block';
  }; //EVENT ACTION


  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createEvent').addEventListener('click', showEvent);
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('postMsg').addEventListener('click', showModal); // handle post message
} catch (e) {
  console.log(e.message);
}

/***/ })

}]);