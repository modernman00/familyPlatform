"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["changePW"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FormHelper)
/* harmony export */ });
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");


function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var FormHelper = /*#__PURE__*/function () {
  function FormHelper(data) {
    _classCallCheck(this, FormHelper);
    if (!Array.isArray(data)) throwError('data must be an array of form elements');
    this.data = data;
    this.error = [];
    this.result = 0;
  }
  return _createClass(FormHelper, [{
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
    key: "validateField",
    value: function validateField(value) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'general';
      var regexes = {
        email: /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/
        // Add more regexes as needed
      };
      return type === 'email' ? regexes.email.test(value) : value.trim() !== '';
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
            var errMsg = _this.id("".concat(post.name, "_error"));
            var postName = post.name.replace('_', ' ');
            var asterisk = "*";

            // rid it off the submit and token
            if (['submit', 'button', 'showPassword_id', 'g-recaptcha-response', 'cancel', 'token', 'checkbox_id'].includes(post.name) || ['button'].includes(post.id) || ['button'].includes(post.type)) return;
            // check if there is no value

            if (['spouseName', 'spouseMobile', 'spouseEmail', 'fatherMobile', 'fatherEmail', 'motherMobile', 'maidenName', 'motherEmail'].includes(post.name)) {
              // post.value is not prpvided if it is not provided 
              post.value = post.value === "" ? "Not Provided" : post.value;
            }
            if (post.value === '' || post.value === 'select') {
              if (!_this.validateField(post.value)) {
                if (errMsg) {
                  var _post$placeholder;
                  errMsg.innerHTML = "".concat((_post$placeholder = post.placeholder) !== null && _post$placeholder !== void 0 ? _post$placeholder : asterisk, " cannot be left empty");
                  errMsg.style.color = 'red';
                }
                _this.error.push("".concat(postName.toUpperCase(), " cannot be left empty"));
                _this.result = false;
              }
            }
            if (post.name === 'email' && !_this.validateField(post.value, 'email')) {
              _this.error.push('<li style="color: red;">Please enter a valid email</li>');
              if (errMsg) errMsg.innerHTML = '* Please enter a valid email';
              _this.result = false;
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
      this.error = []; // Empty the error array

      // Define a function to clear error messages for a given input element
      var clearErrorForElement = function clearErrorForElement(elementName) {
        var errorElement = _this2.id("".concat(elementName, "_error"));
        if (errorElement) {
          errorElement.innerHTML = '';
        }
      };
      this.data.forEach(function (el) {
        var _iterator2 = _createForOfIteratorHelper(el),
          _step2;
        try {
          var _loop = function _loop() {
            var post = _step2.value;
            var id = post.id,
              name = post.name,
              value = post.value;

            // Skip certain input types
            if (['submit', 'button', 'token', 'checkbox'].includes(id) || ['token', 'submit'].includes(name)) {
              return 1; // continue
            }
            var the_id = _this2.id(id);
            if (the_id) {
              // Add keyup event listener to clear errors for non-select inputs
              the_id.addEventListener('keyup', function () {
                if (value !== 'select') {
                  clearErrorForElement(name);
                }
              });
            } else {
              console.error("Element with ID '".concat(id, "' with post name '").concat(post.name, "' not found."));
            }

            // Add change event listener to clear error message
            the_id.addEventListener('change', function () {
              clearErrorForElement(name);
            });
          };
          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            if (_loop()) continue;
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
          if (theData === null || theData === undefined || theData === "") {
            throw new Error("Element with ID '".concat(input[i], "_id' not found or is empty"));
          }
          var max = maxi[i];
          var error = _this3.id("".concat(input[i], "_error"));
          theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
          theData.addEventListener('keyup', function () {
            error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
            var help = _this3.id("".concat(input[i], "_help"));
            help.style.color = 'red';
            help.style.fontSize = '10px';
            error.style.color = 'red';
            setTimeout(function () {
              help.style.display = 'none';
            }, 5000);
          });
        };
        for (var i = 0; i < input.length; i++) {
          _loop2(i);
        }
      } catch (error) {
        console.error(error.message);
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
}();


/***/ }),

/***/ "./resources/asset/js/components/changePW/index.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/changePW/index.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");





(0,_helper_general__WEBPACK_IMPORTED_MODULE_0__.matchInput)('password_id', 'confirm_password_id', 'changePasswordErr');
var loaderElement = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('setLoader');
var submitChangePW = function submitChangePW(e) {
  try {
    e.preventDefault();
    var password = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('password_id').value;

    // Remove any previous error notifications
    var changePasswordNotificationElement = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('changePassword_notification');
    if (changePasswordNotificationElement.classList.contains('is-danger')) {
      changePasswordNotificationElement.classList.remove('is-danger');
    }
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('error').innerHTML = '';
    if (password) {
      loaderElement.style.display = 'block';
      (0,_helper_http__WEBPACK_IMPORTED_MODULE_2__.postFormData)('/login/changePW', 'changePassword', '/login');
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  } finally {
    // Hide the loader element regardless of success or error
    loaderElement.style.display = 'none';
  }
};

// Add a click event listener to the element with id 'submit'
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('submit').addEventListener('click', submitChangePW);
var currentPs = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("password_id");
// const emailID = id("email_id")
var confirmPs = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("confirm_password_id");
currentPs.setAttribute('autocomplete', 'new-password');
confirmPs.setAttribute('autocomplete', 'new-password');
// emailID.setAttribute('type', 'hidden')
// emailLabel.style.display = "none"

/***/ }),

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autoCompleter: () => (/* binding */ autoCompleter),
/* harmony export */   checkBox: () => (/* binding */ checkBox),
/* harmony export */   checkBox2: () => (/* binding */ checkBox2),
/* harmony export */   convertFormData: () => (/* binding */ convertFormData),
/* harmony export */   createAndAppendElement: () => (/* binding */ createAndAppendElement),
/* harmony export */   distinctValue: () => (/* binding */ distinctValue),
/* harmony export */   isChecked: () => (/* binding */ isChecked),
/* harmony export */   loaderIcon: () => (/* binding */ loaderIcon),
/* harmony export */   loaderIconBootstrap: () => (/* binding */ loaderIconBootstrap),
/* harmony export */   loaderIconBulma: () => (/* binding */ loaderIconBulma),
/* harmony export */   matchInput: () => (/* binding */ matchInput),
/* harmony export */   matchRegex: () => (/* binding */ matchRegex),
/* harmony export */   realTimeCheckLen: () => (/* binding */ realTimeCheckLen),
/* harmony export */   removeDiv: () => (/* binding */ removeDiv),
/* harmony export */   showResponse: () => (/* binding */ showResponse),
/* harmony export */   toSentenceCase: () => (/* binding */ toSentenceCase)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! autocompleter */ "./node_modules/autocompleter/autocomplete.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(autocompleter__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");


function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }



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
      text = text.toLowerCase();
      // you can also use AJAX requests instead of preloaded data
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
  return "<div class=\"control\"> \n        <label class=\"radio\">\n          <input type=\"radio\" name=\"send".concat(subject, "\" value=\"yes\" id=").concat(subject, "Yes > Yes \n        </label>\n        <label class=\"radio\"> \n          <input type=\"radio\" name=\"send").concat(subject, "\" value=\"no\" id=").concat(subject, "No checked> No \n        </label>\n      </div>");
};
var checkBox2 = function checkBox2(subject) {
  return "<div class=\"control\"> \n        <label class=\"checkbox\">\n          <input type=\"checkbox\" name=\"send".concat(subject, "\" value=\"yes\" id=").concat(subject, "Yes> Yes \n        </label>\n        <label class=\"checkbox\"> \n          <input type=\"checkbox\" name=\"send").concat(subject, "\" value=\"no\" id=").concat(subject, "No> No \n        </label>\n      </div>");
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

/**
 * Converts a string to sentence case.
 *
 * Sentence case is a string where the first letter of each word is capitalized, and the rest of the letters are in lowercase.
 *
 * @param {string} str The string to convert to sentence case.
 * @returns {string} A new string in sentence case.
 */
var toSentenceCase = function toSentenceCase(str) {
  return str.toLowerCase() // Convert the string to lowercase
  .split(' ') // Split the string into words
  .map(function (word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
  }) // Capitalize the first letter of each word
  .join(' '); // Join the words back into a string
};
var convertFormData = function convertFormData(formId) {
  var formInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.qSelAll)(formId);
  var formInputArr = Array.from(formInput);
  return new _FormHelper__WEBPACK_IMPORTED_MODULE_2__["default"](formInputArr);
};
var showResponse = function showResponse(theId, message, status) {
  var responseEl = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(theId);
  var col = status ? 'green' : 'red';
  responseEl.innerHTML = message;
  responseEl.style.color = 'green';
  responseEl.style.backgroundColor = col;
  responseEl.style.color = 'white';
  setTimeout(function () {
    responseEl.innerHTML = '';
  }, 5000); // Disappear after 5 seconds
};

/**
   *
   * @param {input is the id of the input/ this is an array [as, it, it]} input
   * @param {* this is the max policy and it must be an integer} maxi
   */

var realTimeCheckLen = function realTimeCheckLen(input, maxi) {
  try {
    var _loop = function _loop(i) {
      var theData = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(input[i], "_id"));
      if (theData === null || theData === undefined || theData === "") {
        throw new Error("Element with ID '".concat(input[i], "_id' not found or is empty"));
      }
      var max = maxi[i];
      var error = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(input[i], "_error"));
      theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
      theData.addEventListener('keyup', function () {
        error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
        var help = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(input[i], "_help"));
        help.style.color = 'red';
        help.style.fontSize = '10px';
        error.style.color = 'red';
        setTimeout(function () {
          help.style.display = 'none';
        }, 5000);
      });
    };
    for (var i = 0; i < input.length; i++) {
      _loop(i);
    }
  } catch (error) {
    console.error(error.message);
  }
};

/***/ }),

/***/ "./resources/asset/js/components/helper/http.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/helper/http.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkCookie: () => (/* binding */ checkCookie),
/* harmony export */   getApiData: () => (/* binding */ getApiData),
/* harmony export */   getCookie: () => (/* binding */ getCookie),
/* harmony export */   getMultipleApiData: () => (/* binding */ getMultipleApiData),
/* harmony export */   postFormData: () => (/* binding */ postFormData),
/* harmony export */   postMultipleApiData: () => (/* binding */ postMultipleApiData),
/* harmony export */   setCookie: () => (/* binding */ setCookie)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios-retry */ "./node_modules/axios-retry/lib/esm/index.js");
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }



// import Cookies from 'js-cookie'

(0,axios_retry__WEBPACK_IMPORTED_MODULE_1__["default"])(axios__WEBPACK_IMPORTED_MODULE_2__["default"], {
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
  var _ref = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(url, formId) {
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
      errorClass,
      errorMessage,
      _args = arguments;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          redirect = _args.length > 2 && _args[2] !== undefined ? _args[2] : null;
          css = _args.length > 3 && _args[3] !== undefined ? _args[3] : null;
          notificationForm = "".concat(formId, "_notification");
          notificationId = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(notificationForm);
          if (notificationId) {
            _context.next = 6;
            break;
          }
          throw new Error('Notification element not found');
        case 6:
          // Cleanup previous notification styles
          notificationId.style.display = 'none';
          ['is-danger', 'is-success', 'w3-red', 'w3-green', 'bg-danger', 'bg-success'].forEach(function (cls) {
            return notificationId.classList.remove(cls);
          });

          // extract the form entriesËËË
          form = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(formId);
          if (form) {
            _context.next = 11;
            break;
          }
          throw new Error('Form element not found');
        case 11:
          formEntries = new FormData(form);
          formEntries["delete"]('submit');
          formEntries["delete"]('checkbox_id');
          options = {
            baseURL: '/',
            // Adjust to your API base URL
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN',
            withCredentials: true // Ensure cookies (e.g., XSRF token) are sent
          }; // AXIOS POST FUNCTIONALITY
          _context.prev = 15;
          _context.next = 18;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url, formEntries, options);
        case 18:
          response = _context.sent;
          if (!(response.status < 200 || response.status >= 300)) {
            _context.next = 21;
            break;
          }
          throw new Error(((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'Request failed');
        case 21:
          successClass = getNotificationClassByCSS(css || 'bulma', 'green'); // check if response.data.message is an array
          idSetFromHttp = null;
          famCodeSetFromHttp = null;
          dbHttpResult = null;
          if (!(response.data && _typeof(response.data.message) === 'object')) {
            _context.next = 37;
            break;
          }
          idSetFromHttp = response.data.message.id || null;
          famCodeSetFromHttp = response.data.message.famCode || null;
          dbHttpResult = response.data.message.outcome || null;
          if (idSetFromHttp) {
            _context.next = 31;
            break;
          }
          throw new Error('idSetFromHttp is missing');
        case 31:
          if (dbHttpResult) {
            _context.next = 33;
            break;
          }
          throw new Error('dbHttpResult is missing');
        case 33:
          if (famCodeSetFromHttp) {
            _context.next = 35;
            break;
          }
          throw new Error('famCodeSetFromHttp is missing');
        case 35:
          _context.next = 38;
          break;
        case 37:
          dbHttpResult = response.data.message;
        case 38:
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
          _context.next = 49;
          break;
        case 44:
          _context.prev = 44;
          _context.t0 = _context["catch"](15);
          errorClass = getNotificationClassByCSS(css || 'bulma', 'red');
          errorMessage = ((_error$response = _context.t0.response) === null || _error$response === void 0 || (_error$response = _error$response.data) === null || _error$response === void 0 ? void 0 : _error$response.error) || _context.t0.request || 'An unknown error occurred';
          processFormDataAction(errorClass, errorMessage, notificationId);
        case 49:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[15, 44]]);
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
    var errorElement = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error');
    if (errorElement) {
      errorElement.scrollIntoView({
        behavior: 'smooth'
      });
      errorElement.innerHTML = message;
    }
    var loader = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('setLoader');
    if (loader) loader.classList.remove('loader');
  } else {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)('Notification element not found');
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
  var _ref2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2(URL) {
    var token,
      config,
      fetch,
      _args2 = arguments;
    return _regeneratorRuntime().wrap(function _callee2$(_context2) {
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
          _context2.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(URL, config);
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
    }, _callee2, null, [[1, 9]]);
  }));
  return function getApiData(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
var getMultipleApiData = /*#__PURE__*/function () {
  var _ref3 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3(url1, url2) {
    var token,
      config,
      fetch,
      _args3 = arguments;
    return _regeneratorRuntime().wrap(function _callee3$(_context3) {
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
          _context3.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].all([axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(url1, config), axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(url2, config)]);
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
    }, _callee3, null, [[1, 9]]);
  }));
  return function getMultipleApiData(_x4, _x5) {
    return _ref3.apply(this, arguments);
  };
}();

// build a function to post multiple api form data

var postMultipleApiData = /*#__PURE__*/function () {
  var _ref4 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4(url1, url2, formData) {
    var token,
      config,
      fetch,
      _args4 = arguments;
    return _regeneratorRuntime().wrap(function _callee4$(_context4) {
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
          _context4.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].all([axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url1, formData, config), axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url2, formData, config)]);
        case 5:
          fetch = _context4.sent;
          return _context4.abrupt("return", fetch);
        case 9:
          _context4.prev = 9;
          _context4.t0 = _context4["catch"](1);
          return _context4.abrupt("return", _context4.t0);
        case 12:
        case "end":
          return _context4.stop();
      }
    }, _callee4, null, [[1, 9]]);
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

/***/ })

}]);