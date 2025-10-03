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

/***/ "./resources/asset/js/components/accountSetting.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/accountSetting.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _register_onChangeKidSibling__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./register/onChangeKidSibling */ "./resources/asset/js/components/register/onChangeKidSibling.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./api/index */ "./resources/asset/js/components/api/index.js");








var formInput = document.querySelectorAll('.accountSettingForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_2__["default"](formInputArr);
var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};
alert("Account Setting Page");
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
      axios__WEBPACK_IMPORTED_MODULE_3__["default"].post("/accountSetting", eventFormEntries, options).then(function (response) {
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
(0,_api_index__WEBPACK_IMPORTED_MODULE_5__.fetchEmailData)().then(function (data) {
  // Do something with the fetched data
  var emailData = data;

  // SEND EMAIL TO THE KIDS AND processKidsSibling
  (0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_4__.processKidsSiblings)(emailData, fName, lastName, famCode);
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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }


var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
var URL = "https://olaogun.test/";
// https: //laravel.com/docs/5.4/mix#environment-variables

var getAllData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var response, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          _context.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"].get("".concat(URL, "allMembers/processApiData2"), config);
        case 1:
          response = _context.v;
          return _context.a(2, response.data);
        case 2:
          _context.p = 2;
          _t = _context.v;
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_t);
          // You can perform additional error handling actions if needed
          throw _t;
        case 3:
          return _context.a(2);
      }
    }, _callee, null, [[0, 2]]);
  }));
  return function getAllData() {
    return _ref.apply(this, arguments);
  };
}();
var postData = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(url, object) {
    var response, _t2;
    return _regenerator().w(function (_context2) {
      while (1) switch (_context2.p = _context2.n) {
        case 0:
          _context2.p = 0;
          _context2.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"].post(url, object);
        case 1:
          response = _context2.v;
          console.log(response);
          _context2.n = 3;
          break;
        case 2:
          _context2.p = 2;
          _t2 = _context2.v;
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_t2);
          // You can perform additional error handling actions if needed
          throw _t2;
        case 3:
          return _context2.a(2);
      }
    }, _callee2, null, [[0, 2]]);
  }));
  return function postData(_x, _x2) {
    return _ref2.apply(this, arguments);
  };
}();
var fetchEmailData = function fetchEmailData() {
  // Make a GET request and return the promise
  return axios__WEBPACK_IMPORTED_MODULE_0__["default"].get("".concat(URL, "getEmails")).then(function (response) {
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
/* harmony export */   fileUploadSizeValidation: () => (/* binding */ fileUploadSizeValidation),
/* harmony export */   formReset: () => (/* binding */ formReset),
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
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }

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

/**
 * Resets a form by clearing all input fields, validation messages, 
 * image previews and custom inputs.
 * @param {string} formId - The ID of the form to reset.
 */
var formReset = function formReset(formId) {
  var form = id(formId);
  if (!form) return;

  // Reset form fields
  form.reset();

  // Clear validation messages
  form.qSelAll('.is-invalid, .invalid-feedback').forEach(function (el) {
    el.classList.remove('is-invalid');
    if (el.classList.contains('invalid-feedback')) {
      el.textContent = '';
    }
  });

  // Clear image previews
  form.qSelAll('.preview-img').forEach(function (img) {
    img.src = '';
    img.style.display = 'none';
  });

  // Clear custom inputs (e.g., emoji pickers, rich text)
  form.qSelAll('[data-custom-input]').forEach(function (el) {
    el.value = '';
  });
};
var fileUploadSizeValidation = function fileUploadSizeValidation(fileInputId) {
  var maxSizeMB = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 3;
  var fileInput = id(fileInputId);
  if (!fileInput || !fileInput.files) return true; // No files to validate

  var maxSizeBytes = maxSizeMB * 1024 * 1024;
  var _iterator = _createForOfIteratorHelper(fileInput.files),
    _step;
  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var file = _step.value;
      if (file.size > maxSizeBytes) {
        alert("File ".concat(file.name, " exceeds the maximum size of ").concat(maxSizeMB, "MB."));
        fileInput.value = ''; // Clear the input
        return false; // Validation failed
      } else if (file.size === 0) {
        alert("File ".concat(file.name, " is empty and cannot be uploaded."));
        fileInput.value = ''; // Clear the input
        return false; // Validation failed
      } else if (file.type.includes("exe") || file.type.includes("sh") || file.type.includes("bat") || file.type.includes("js")) {
        alert("File ".concat(file.name, " is of an unsupported type and cannot be uploaded."));
        fileInput.value = ''; // Clear the input
        return false; // Validation failed
      } else if (!file.type.startsWith("image/") && !file.type.startsWith("video/") && !file.type.startsWith("audio/") && !file.type === "application/pdf" && !file.type === "application/msword" && !file.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
        alert("File ".concat(file.name, " is of an unsupported type and cannot be uploaded."));
        fileInput.value = ''; // Clear the input
        return false; // Validation failed
      }
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
  return true; // All files are within size limit
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
  // id('loader').classList.remove('loader') // remove loader

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
//# sourceMappingURL=accountSetting.js.map