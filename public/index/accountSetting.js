"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["accountSetting"],{

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
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }

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
            var errMsg = _this.id("".concat(post.name, "_error"));

            // rid it off the submit and token
            if (post.name == 'submit' || post.name == 'button' || post.id == 'button' || post.type == 'button' || post.id == 'showPassword_id' || post.id == 'g-recaptcha-response' || post.name == 'cancel' || post.name == "checkbox_id") {
              continue;
            }

            // check if there is no value

            var postName = post.name.replace('_', ' ');
            if (postName == "spouseName" || postName == "spouseMobile" || postName == "spouseEmail" || postName == "fatherMobile" || postName == "fatherEmail" || postName == "motherMobile" || postName == "maidenName" || postName == "motherEmail") {
              if (post.value === "") {
                post.value = "Not Provided";
              }
            }
            var asterisk = "*";
            if (post.value === '' || post.value === 'select') {
              // CHECK IF ERRMSG EXISTS BEFORE SETTING THE INNERHTML
              if (errMsg) {
                var _post$placeholder;
                errMsg.innerHTML = "".concat((_post$placeholder = post.placeholder) !== null && _post$placeholder !== void 0 ? _post$placeholder : asterisk, " cannot be left empty");
                errMsg.style.color = "red";
              }
              _this.error.push("".concat(postName.toUpperCase(), " cannot be left empty"));
            } else {
              _this.result = 1;
            }
            var checkRegex = (0,_helper_general__WEBPACK_IMPORTED_MODULE_0__.matchRegex)(post.value);
            if (checkRegex === false) {
              _this.error.push("There is a problem with your entry for ".concat(postName.toUpperCase(), "'s question"));
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
  return FormHelper;
}();


/***/ }),

/***/ "./resources/asset/js/components/accountSetting.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/accountSetting.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _register_onChangeKidSibling__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./register/onChangeKidSibling */ "./resources/asset/js/components/register/onChangeKidSibling.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./api/index */ "./resources/asset/js/components/api/index.js");








var formInput = document.querySelectorAll('.accountSettingForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_2__["default"](formInputArr);
var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};
var process = function process(e) {
  try {
    e.preventDefault();
    var notificationDiv = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('accountSettingForm_notification');
    var notificationMsg = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('accountSettingForm_notification_error');
    notificationMsg.innerHTML = ""; // may not be needed
    formData.massValidate();
    // log(formData.error)
    if (formData.error.length <= 0) {
      // get the form data
      var eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('accountSettingForm');
      var eventFormEntries = new FormData(eventForm);
      // post the form data to the database and get the last posted event no
      axios__WEBPACK_IMPORTED_MODULE_5__["default"].post("/accountSetting", eventFormEntries, options).then(function (response) {
        notificationDiv.style.display = "block"; // unblock the notification
        notificationDiv.classList.add('is-success'); // add the success class
        notificationMsg.innerHTML = response.data.message;
      });
      // window.location.replace("/member/profilePage")
    } else {
      alert('The form cannot be submitted. Please check the errors');
      formData.clearError();
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  }
};

// show spouse once select is Yes
// Add event listeners
// Function to show spouse information based on marital status
var showSpouse = function showSpouse() {
  // Get the value of the marital status dropdown
  var maritalStatus = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('maritalStatus_id').value;

  // Check marital status and show relevant elements
  if (maritalStatus === "Yes - Add Husband") {
    // Display spouse section if adding husband
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('spouse');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseName_id', 'set', 'name', 'spouseName');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseEmail_id', 'set', 'name', 'spouseEmail');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMobile_id', 'set', 'name', 'spouseMobile');
  } else if (maritalStatus === "Yes - Add Wife") {
    // Display spouse section if adding wife
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('spouseMaidenName_div');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('spouse');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseName_id', 'set', 'name', 'spouseName');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseEmail_id', 'set', 'name', 'spouseEmail');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMobile_id', 'set', 'name', 'spouseMobile');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMaidenName_id', 'set', 'name', 'spouseMaidenName');
    // Display maiden name and spouse sections if adding wife
  } else {
    // Hide spouse section if marital status is not "Yes"
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('spouse');
  }
};

// Add event listener to marital status dropdown to trigger showSpouse function
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('maritalStatus_id').addEventListener('change', showSpouse);

// Hide spouse and maiden name elements by default
(0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('spouse');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('spouseMaidenName_div');

// remove input name attritube by default
(0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseName_id', 'remove', 'name');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMaidenName_id', 'remove', 'name');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseEmail_id', 'remove', 'name');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMobile_id', 'remove', 'name');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('submit').addEventListener('click', process);
// GET ALL EMAILS 

// Call the fetchData function to initiate the request

// const emailData = []
var fName = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('fName').textContent;
var lastName = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('lName').textContent;
var famCode = localStorage.getItem('requesterFamCode');
(0,_api_index__WEBPACK_IMPORTED_MODULE_4__.fetchEmailData)().then(function (data) {
  // Do something with the fetched data
  var emailData = data;

  // SEND EMAIL TO THE KIDS AND processKidsSibling
  (0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_3__.processKidsSiblings)(emailData, fName, lastName, famCode);
})["catch"](function (error) {
  // Handle any errors that occurred during the request or processing
  console.error('Error:', error);
});

// Call the getEmailData function somewhere in your code

/***/ }),

/***/ "./resources/asset/js/components/api/index.js":
/*!****************************************************!*\
  !*** ./resources/asset/js/components/api/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   fetchEmailData: () => (/* binding */ fetchEmailData),
/* harmony export */   getAllData: () => (/* binding */ getAllData),
/* harmony export */   postData: () => (/* binding */ postData)
/* harmony export */ });
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw new Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw new Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }


var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
var URL = "http://olaogun.test/";
// https: //laravel.com/docs/5.4/mix#environment-variables

var getAllData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var response;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          _context.next = 3;
          return axios__WEBPACK_IMPORTED_MODULE_1__["default"].get("".concat(URL, "allMembers/processApiData2"), config);
        case 3:
          response = _context.sent;
          return _context.abrupt("return", response.data);
        case 7:
          _context.prev = 7;
          _context.t0 = _context["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(_context.t0);
          // You can perform additional error handling actions if needed
          throw _context.t0;
        case 11:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 7]]);
  }));
  return function getAllData() {
    return _ref.apply(this, arguments);
  };
}();
var postData = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2(url, object) {
    var response;
    return _regeneratorRuntime().wrap(function _callee2$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 3;
          return axios__WEBPACK_IMPORTED_MODULE_1__["default"].post(url, object);
        case 3:
          response = _context2.sent;
          console.log(response);
          _context2.next = 11;
          break;
        case 7:
          _context2.prev = 7;
          _context2.t0 = _context2["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(_context2.t0);
          // You can perform additional error handling actions if needed
          throw _context2.t0;
        case 11:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 7]]);
  }));
  return function postData(_x, _x2) {
    return _ref2.apply(this, arguments);
  };
}();
var fetchEmailData = function fetchEmailData() {
  // Make a GET request and return the promise
  return axios__WEBPACK_IMPORTED_MODULE_1__["default"].get("".concat(URL, "getEmails")).then(function (response) {
    // Extract the data from the response
    var data = response.data;
    // Return the data or do further processing
    return data;
  })["catch"](function (error) {
    // Handle any errors that occur during the request
    console.error('Error fetching data:', error);
    throw error; // Rethrow the error to be handled by the caller
  });
};

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
/* harmony export */   createAndAppendElement: () => (/* binding */ createAndAppendElement),
/* harmony export */   distinctValue: () => (/* binding */ distinctValue),
/* harmony export */   isChecked: () => (/* binding */ isChecked),
/* harmony export */   loaderIcon: () => (/* binding */ loaderIcon),
/* harmony export */   loaderIconBootstrap: () => (/* binding */ loaderIconBootstrap),
/* harmony export */   loaderIconBulma: () => (/* binding */ loaderIconBulma),
/* harmony export */   matchInput: () => (/* binding */ matchInput),
/* harmony export */   matchRegex: () => (/* binding */ matchRegex),
/* harmony export */   removeDiv: () => (/* binding */ removeDiv),
/* harmony export */   toSentenceCase: () => (/* binding */ toSentenceCase)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! autocompleter */ "./node_modules/autocompleter/autocomplete.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(autocompleter__WEBPACK_IMPORTED_MODULE_1__);


function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }


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
var toSentenceCase = function toSentenceCase(str) {
  return str.toLowerCase() // Convert the string to lowercase
  .split(' ') // Split the string into words
  .map(function (word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
  }) // Capitalize the first letter of each word
  .join(' '); // Join the words back into a string
};

/***/ }),

/***/ "./resources/asset/js/components/kidsAndSiblings.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/kidsAndSiblings.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   processKidsSiblings: () => (/* binding */ processKidsSiblings)
/* harmony export */ });
/* harmony import */ var _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../data/kidsSibling */ "./resources/asset/js/data/kidsSibling.js");
/* harmony import */ var _components_global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../components/global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");




var processKidsSiblings = function processKidsSiblings(checkEmailExists, firstName, lastName) {
  var famCode = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  var chooseEmail = [];
  var chooseName = [];
  var helpHTML = "";
  // let errorHTML = ""; // Show error if applicant's email is registered

  document.onkeydown = function (e) {
    //. use the onclick to get the id of the element that was clicked
    // 2. use event listener to get the email value (if it is not empty)
    // 3. use the email value to check if it is in the array
    // 4. if it is in the array, show the Yes or No Radio
    // 5. click yes to send email to the kid or sibling

    try {
      // create an object with the data to check
      var elementId = e.target.id; // id of the element that was clicked or press down
      var emailInput = e.target.value;
      // this phase checks the id of what is being typed
      if (!elementId) throw new Error("target id is null and empty");

      // if the elementId indicate that it is a kid, then choosemail inherits all the kids array from the checkEmailObj and vis a versa

      if (_data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidEmailInput.includes(elementId)) {
        chooseEmail = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidEmailInput;
        chooseName = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidNameInput;
        helpHTML = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "_help"));
      } else if (_data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingEmail.includes(elementId)) {
        chooseEmail = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingEmail;
        chooseName = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingName;
        helpHTML = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "_help"));
      }
      var checkFamilyEmail = function checkFamilyEmail() {
        //this checks the value of what is being typed

        helpHTML.innerHTML = emailInput.length > 5 && emailInput.length < 7 ? "Email may be too small" : "";

        // use the elementid to find the exact email value and name value
        var index = chooseEmail.indexOf(elementId);
        var email = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)(chooseEmail[index]);
        var emailValue = email.value;
        var name = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)(chooseName[index]);
        var nameValue = name.value;

        // if (!emailValue)
        //     throw new Error("another round of email is empty");
        // if (!nameValue) throw new Error("name is empty");
        // if (!getData.length) throw new Error("data is faulty");
        // checking family email 
        helpHTML.style.display = "block";
        helpHTML.innerHTML = checkEmailExists.includes(emailInput) ? "Great news! ".concat(nameValue, " is already registered on the platform") : "".concat(nameValue, " is not on the platform.Do you want us to send ").concat(nameValue, " a email to register to the platform ? ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.checkBox)(elementId));

        // send the email to family membersa

        var setFamCode;
        var famCodeElement = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)('famCode_id');
        if (famCodeElement) {
          setFamCode = famCodeElement.value;
        } else {
          // Handle the case where the element is not found or not yet loaded
          setFamCode = famCode; // Use a default value (famCode) or handle the situation accordingly
        }
        var processKidRadio = function processKidRadio() {
          var postObj = {
            mobile: "",
            // is this needed?
            viewPath: "msg/contactNewMember",
            data: {
              email: emailValue,
              name: nameValue,
              yourName: "".concat(firstName, " ").concat(lastName),
              familyCode: setFamCode
            },
            subject: "".concat(firstName, " ").concat(lastName, " Wants You: Experience the Magic of your Family Network Today!")
          };
          axios__WEBPACK_IMPORTED_MODULE_3__["default"].post("/register/contactNewMember", postObj).then(function (response) {
            helpHTML.innerHTML = response.data.message;
            setTimeout(function () {
              helpHTML.style.display = "none";
            }, 5000);
          })["catch"](function (error) {
            (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
          });
        };
        (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "Yes")).addEventListener("click", processKidRadio);
        (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "No")).addEventListener("click", function () {
          return (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "No")).style.display = "none";
        });
      };
      if (chooseEmail.includes(elementId)) {
        checkFamilyEmail();

        // id(elementId).addEventListener("keyup", checkFamilyEmail);
      }
    } catch (error) {
      (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
    }
  };
};

/***/ }),

/***/ "./resources/asset/js/components/register/html/kids_Sibling.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/html/kids_Sibling.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtmlFamily: () => (/* binding */ renderHtmlFamily)
/* harmony export */ });
var renderHtmlFamily = function renderHtmlFamily(family, no) {
  if (no) {
    var kids_sib = family == "addChildren" ? "kid" : "sibling";
    var optionsHtml = "\n      <option value='Choose'>Choose</option>\n      <option value='With Spouse'>With Spouse</option>\n      <option value='Not With Spouse'>Not With Spouse</option>\n    ";
    if (family === "addSiblings") {
      optionsHtml = "\n                <option value='Choose'>Choose</option>\n                <option value='Same_Mother_Father'>Same Mother & Father</option>\n                <option value='Same_Father'>Only Same Father</option>\n                <option value='Same_Mother'>Only Same Mother</option>";
    }
    return "\n            <div class=\"field-body\">\n                <div class=\"field\">\n                    <p class=\"control is-expanded\">\n                        <span class=\"select is-normal is-success is-fullwidth\">\n                            <select name=\"".concat(kids_sib, "_option").concat(no, "\" id=\"").concat(kids_sib, "_option").concat(no, "\">\n                                ").concat(optionsHtml, "\n                            </select>\n                        </span>\n                    </p>\n                </div>\n\n                <div class=\"field\">\n                    <p class=\"control is-expanded\">\n                        <input type=\"text\" placeholder = \"Enter ").concat(kids_sib, "'s full name - ").concat(no, "\"  name =").concat(kids_sib, "_name").concat(no, " class=\"input input is-normal \" id=\"").concat(kids_sib, "_name").concat(no, "\">\n                    </p>\n                </div>          \n\n                <div class=\"field\">\n                    <p class=\"control is-expanded has-icons-left\">\n                        <input type=\"email\" placeholder = \"Enter ").concat(kids_sib, "'s email - ").concat(no, "\" value = \"Please, provide email address\" name=").concat(kids_sib, "_email").concat(no, " class=\"input input is-normal \" id=\"").concat(kids_sib, "_email").concat(no, "\">\n\n                        <span class=\"icon is-small is-left\">\n                            <i class=\"fas fa-envelope\"></i>\n                        </span>\n                    </p>\n                    <p class=\"help is-danger\" id=\"").concat(kids_sib, "_email").concat(no, "_help\"></p>\n                </div>\n\n            </div>");
  }
};

/***/ }),

/***/ "./resources/asset/js/components/register/onChangeKidSibling.js":
/*!**********************************************************************!*\
  !*** ./resources/asset/js/components/register/onChangeKidSibling.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   show: () => (/* binding */ show)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _html_kids_Sibling__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html/kids_Sibling */ "./resources/asset/js/components/register/html/kids_Sibling.js");


// import { getEnvironmentVariable as env} from 'environment-variable-reader'




// let childrenOnchangeValue = 0;
// let childrenOnchangeValue = 0;

var show = function show(kids_or_sib, event) {
  try {
    // what was picked or selected
    var value = event.target.value;

    // childrenOnchangeValue = value;
    var addDiv = kids_or_sib == "kids" ? "addChildren" : "addSiblings";

    // remove the div 
    (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.removeDiv)(addDiv);
    if (value == 0) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help")).innerHTML = "";
    }
    if (value > 0) {
      // create and append the div element 
      var parent = "".concat(kids_or_sib, "_div");
      (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.createAndAppendElement)('div', addDiv, parent);
      // use the loop to generate the number of input
      for (var i = 0; i < value; i++) {
        var no = i + 1;
        var msg = no > 1 ? "Please, enter their names and emails below" : "Please, enter the name and email below";
        var getSelectHelp = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help"));
        getSelectHelp.innerHTML = msg;
        getSelectHelp.style.fontSize = '1rem';
        getSelectHelp.style.color = '#fc2003';
        var html = (0,_html_kids_Sibling__WEBPACK_IMPORTED_MODULE_2__.renderHtmlFamily)(addDiv, no);
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(addDiv).insertAdjacentHTML('afterBegin', html);
      }
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
var onChangeKidAndSiblings = function onChangeKidAndSiblings() {
  var sibInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("siblings_id");
  var kidInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("kids_id");
  kidInput.addEventListener('change', function (event) {
    return show('kids', event);
  });
  sibInput.addEventListener('change', function (event) {
    return show('siblings', event);
  });
};
onChangeKidAndSiblings();

/***/ }),

/***/ "./resources/asset/js/data/kidsSibling.js":
/*!************************************************!*\
  !*** ./resources/asset/js/data/kidsSibling.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkEmailObj: () => (/* binding */ checkEmailObj)
/* harmony export */ });
var checkEmailObj = {
  kidEmailInput: ["kid_email1", "kid_email2", "kid_email3", "kid_email4", "kid_email5", "kid_email6", "kid_email7", "kid_email8", "kid_email9", "kid_email10"],
  kidNameInput: ["kid_name1", "kid_name2", "kid_name3", "kid_name4", "kid_name5", "kid_name6", "kid_name7", "kid_name8", "kid_name9", "kid_name10"],
  siblingEmail: ["sibling_email1", "sibling_email2", "sibling_email3", "sibling_email4", "sibling_email5", "sibling_email6", "sibling_email7", "sibling_email8", "sibling_email9", "sibling_email10"],
  siblingName: ["sibling_name1", "sibling_name2", "sibling_name3", "sibling_name4", "sibling_name5", "sibling_name6", "sibling_name7", "sibling_name8", "sibling_name9", "sibling_name10"]
};

/***/ })

}]);