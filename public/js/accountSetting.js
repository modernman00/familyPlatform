"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["accountSetting"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ FormHelper; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");




function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }

var FormHelper = /*#__PURE__*/function () {
  function FormHelper(data) {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, FormHelper);
    if (!Array.isArray(data)) throwError('data must be an array of form elements');
    this.data = data;
    this.error = [];
    this.result = 0;
  }
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(FormHelper, [{
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

            if (['spouse_name', 'spouse_mobile', 'spouse_email', 'father_mobile', 'father_email', 'mother_mobile', 'maiden_name', 'mother_email'].includes(post.name)) {
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
      var emailEl = this.id('email_id') || this.id('email');
      if (!emailEl) return;
      var email = emailEl.value;
      if (email.match(emailExp) === null) {
        var errEl = this.id('email_error');
        if (errEl) {
          errEl.innerHTML = msg;
          errEl.style.color = "red";
        }
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
              console.log("Adding listeners to element: ".concat(id));
              // Add keyup event listener to clear errors for non-select inputs
              the_id.addEventListener('keyup', function () {
                if (value !== 'select') {
                  clearErrorForElement(name);
                }
              });

              // Add change event listener to clear error message
              the_id.addEventListener('change', function () {
                clearErrorForElement(name);
              });
            } else {
              console.warn("Element with ID '".concat(id, "' with post name '").concat(post.name, "' not found."));
            }
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
          if (!theData) theData = _this3.id(input[i]);
          if (theData === null || theData === undefined || theData === "") {
            console.warn("Element with ID '".concat(input[i], "' not found or is empty"));
            return 1; // continue
          }
          var max = maxi[i];
          var error = _this3.id("".concat(input[i], "_error"));
          theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
          theData.addEventListener('keyup', function () {
            if (error) {
              error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
              error.style.color = 'red';
            }
            var help = _this3.id("".concat(input[i], "_help"));
            if (help) {
              help.style.color = 'red';
              help.style.fontSize = '10px';
              setTimeout(function () {
                help.style.display = 'none';
              }, 5000);
            }
          });
        };
        for (var i = 0; i < input.length; i++) {
          if (_loop2(i)) continue;
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
      firstInput = this.id(first + '_id') || this.id(first);
      secondInput = this.id(second + '_id') || this.id(second);
      if (firstInput && secondInput) {
        secondInput.addEventListener('keyup', function () {
          if (error) error.innerHTML = secondInput.value !== firstInput.value ? 'Your passwords do not match' : "";
        });
      }
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
      giver = this.id(giveInput) || this.id(giveInput.replace('_id', ''));
      taker = this.id(takeInput) || this.id(takeInput.replace('_id', ''));
      if (giver && taker) {
        giver.addEventListener('keyup', function () {
          taker.value = giver.value;
        });
      }
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _accountSettingHelpers_handleFamilyChangeBootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./accountSettingHelpers/handleFamilyChangeBootstrap */ "./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./api/index */ "./resources/asset/js/components/api/index.js");







(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.update)({
  formId: 'accountSettingForm',
  route: '/accountSetting',
  buttonId: 'button',
  redirect: '/accountSetting'
});
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.update)({
  formId: 'passwordForm',
  route: '/accountSetting',
  buttonId: 'passwordBtn',
  redirect: '/accountSetting'
});
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.update)({
  formId: 'preferencesForm',
  route: '/accountSetting',
  buttonId: 'preferencesBtn',
  redirect: '/accountSetting'
});
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.update)({
  formId: 'privacyForm',
  route: '/accountSetting',
  buttonId: 'privacyBtn',
  redirect: '/accountSetting'
});

// Hide spouse and maiden name elements by default
(0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('spouse');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('maiden_name_div');

// Function to show spouse information based on marital status
var showSpouse = function showSpouse() {
  // Get the value of the marital status dropdown
  var maritalStatus = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('maritalStatus').value;

  // Check marital status and show relevant elements
  if (maritalStatus === "Yes - Add Husband") {
    // Display spouse section if adding husband
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('spouse');
  } else if (maritalStatus === "Yes - Add Wife") {
    // Display spouse section if adding wife
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('maiden_name_div');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('spouse');
  } else {
    // Hide spouse section if marital status is not "Yes"
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('spouse');
  }
};

// Add event listener to marital status dropdown to trigger showSpouse function
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('maritalStatus').addEventListener('change', showSpouse);

// GET ALL EMAILS 
// Call the fetchData function to initiate the request
// const emailData = []
var fName = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('fName').textContent;
var famCode = localStorage.getItem('requesterFamCode');
(0,_api_index__WEBPACK_IMPORTED_MODULE_4__.fetchEmailData)().then(function (data) {
  // Do something with the fetched data
  var emailData = data;

  // SEND EMAIL TO THE KIDS AND processKidsSibling
  (0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_3__.processKidsSiblings)(emailData, fName, famCode);
}).catch(function (error) {
  // Handle any errors that occurred during the request or processing
  console.error('Error:', error);
});

// Call the getEmailData function somewhere in your code

/***/ }),

/***/ "./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js":
/*!********************************************************************************************!*\
  !*** ./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js ***!
  \********************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   show: function() { return /* binding */ show; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _renderFamilyBootstrap__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./renderFamilyBootstrap */ "./resources/asset/js/components/accountSettingHelpers/renderFamilyBootstrap.js");
/* harmony import */ var _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../data/checkEmailObj */ "./resources/asset/js/data/checkEmailObj.js");
/* harmony import */ var _data_checkEmailFactory__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../data/checkEmailFactory */ "./resources/asset/js/data/checkEmailFactory.js");








/**
 * Adapted for Account Setting Page (Bootstrap 5)
 */

var syncCheckEmailObj = function syncCheckEmailObj() {
  var _id, _id2;
  var kids = Number((_id = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("children")) === null || _id === void 0 ? void 0 : _id.value) || 0;
  var siblings = Number((_id2 = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("sibling")) === null || _id2 === void 0 ? void 0 : _id2.value) || 0;

  // IMPORTANT: mutate the same object reference
  Object.assign(_data_checkEmailObj__WEBPACK_IMPORTED_MODULE_3__.checkEmailObj, (0,_data_checkEmailFactory__WEBPACK_IMPORTED_MODULE_4__.makeCheckEmailObj)(kids, siblings));
};
var show = function show(kids_or_sib, event) {
  try {
    var value = Number(event.target.value) || 0;

    // ✅ unique container IDs (avoid clashing with <select id="children">)
    var containerId = "".concat(kids_or_sib, "_inputs");
    var parentId = "".concat(kids_or_sib, "_div");

    // remove the existing dynamic container
    (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.removeDiv)(containerId);
    var helpEl = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help"));
    if (helpEl) helpEl.innerHTML = "";
    if (value === 0) {
      syncCheckEmailObj();
      return;
    }
    // create the container under wrapper
    (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.createAndAppendElement)("div", containerId, parentId);
    if (helpEl) {
      helpEl.innerHTML = value > 1 ? "Please, provide details for each below:" : "Please, provide details below:";
      helpEl.classList.remove("text-danger"); // Use BS5 classes or JS logic? keeping simple
      helpEl.style.color = ""; // Reset custom color if any
    }
    var container = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(containerId);
    if (!container) return;
    for (var i = 0; i < value; i++) {
      var no = i + 1;
      var html = (0,_renderFamilyBootstrap__WEBPACK_IMPORTED_MODULE_2__.renderHtmlFamilyBootstrap)(kids_or_sib, no);
      container.insertAdjacentHTML("beforeEnd", html);
    }

    // 🔥 after DOM changes, regenerate ID lists
    syncCheckEmailObj();
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
var onChangeKidAndSiblings = function onChangeKidAndSiblings() {
  var kidInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("children");
  var sibInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("sibling");
  if (kidInput) kidInput.addEventListener('change', function (event) {
    return show('children', event);
  });
  if (sibInput) sibInput.addEventListener('change', function (event) {
    return show('sibling', event);
  });

  // initialise on page load too (if selects already have values)
  syncCheckEmailObj();
};
onChangeKidAndSiblings();

/***/ }),

/***/ "./resources/asset/js/components/accountSettingHelpers/renderFamilyBootstrap.js":
/*!**************************************************************************************!*\
  !*** ./resources/asset/js/components/accountSettingHelpers/renderFamilyBootstrap.js ***!
  \**************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtmlFamilyBootstrap: function() { return /* binding */ renderHtmlFamilyBootstrap; }
/* harmony export */ });
var renderHtmlFamilyBootstrap = function renderHtmlFamilyBootstrap(family, no) {
  if (!no) return "";
  var kids_sib = family === "children" ? "children" : "sibling";
  var labelText = family === "children" ? "Child" : "Sibling";
  var optionsHtmlText = family === "children" ? "With Spouse?" : "Relationship";
  var optionsHtml = "\n    <option value=\"select\" disabled selected>Select status</option>\n    <option value=\"With Spouse\">With Spouse</option>\n    <option value=\"Not With Spouse\">Single / Other</option>\n  ";
  if (family === "sibling") {
    optionsHtml = "\n      <option value=\"select\" disabled selected>Select type</option>\n      <option value=\"Same_Mother_Father\">Same Father & Mother</option>\n      <option value=\"Same_Father\">Same Father Only</option>\n      <option value=\"Same_Mother\">Same Mother Only</option>\n    ";
  }

  // Match the new Account Settings "Spouse Details" container style
  return "\n    <div class=\"p-4 rounded-3 mb-4 wrapper-".concat(kids_sib, "-").concat(no, "\" style=\"background-color: #f8fafc; border: 1px solid #e2e8f0;\">\n      \n      <h6 class=\"fw-bold mb-3 text-dark text-uppercase\" style=\"letter-spacing: 0.5px; font-size: 0.9rem;\">\n        #").concat(no, " ").concat(labelText, " Information\n      </h6>\n\n      <div class=\"row g-3\">\n      \n        <!-- Relationship Select -->\n        <div class=\"col-md-5\">\n           <label class=\"form-label\" for=\"").concat(kids_sib, "_option").concat(no, "\">\n               ").concat(optionsHtmlText, "\n           </label>\n           <select class=\"form-select\" name=\"").concat(kids_sib, "_option").concat(no, "\" id=\"").concat(kids_sib, "_option").concat(no, "\">\n              ").concat(optionsHtml, "\n           </select>\n        </div>\n\n        <!-- Full Name Input -->\n        <div class=\"col-md-7\">\n           <label class=\"form-label\" for=\"").concat(kids_sib, "_name").concat(no, "\">\n               Full Name\n           </label>\n           <input \n              type=\"text\" \n              class=\"form-control\" \n              placeholder=\"e.g. John Doe\" \n              name=\"").concat(kids_sib, "_name").concat(no, "\" \n              id=\"").concat(kids_sib, "_name").concat(no, "\"\n              autocomplete=\"off\"\n           >\n        </div>\n\n        <!-- Email Input (Full Width) -->\n        <div class=\"col-12\">\n           <label class=\"form-label\" for=\"").concat(kids_sib, "_email").concat(no, "\">\n               Email Address\n           </label>\n           <input \n              type=\"email\" \n              class=\"form-control\" \n              placeholder=\"e.g. john.doe@example.com\" \n              name=\"").concat(kids_sib, "_email").concat(no, "\" \n              id=\"").concat(kids_sib, "_email").concat(no, "\"\n              autocomplete=\"off\"\n           >\n           <!-- Dynamic help text area -->\n           <div class=\"form-text text-danger mt-1\" id=\"").concat(kids_sib, "_email").concat(no, "_help\" style=\"min-height: 20px; font-size: 0.8rem;\"></div>\n        </div>\n\n      </div>\n    </div>\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/api/index.js":
/*!****************************************************!*\
  !*** ./resources/asset/js/components/api/index.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   fetchEmailData: function() { return /* binding */ fetchEmailData; },
/* harmony export */   getAllData: function() { return /* binding */ getAllData; },
/* harmony export */   postData: function() { return /* binding */ postData; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");




var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
var URL = '/';
var getAllData = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var response, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          _context.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("".concat(URL, "allMembers/processApiData2"), config);
        case 1:
          response = _context.sent;
          return _context.abrupt("return", response.data);
        case 2:
          _context.prev = 2;
          _t = _context["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
          // You can perform additional error handling actions if needed
          throw _t;
        case 3:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 2]]);
  }));
  return function getAllData() {
    return _ref.apply(this, arguments);
  };
}();
var postData = /*#__PURE__*/function () {
  var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(url, object) {
    var response, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url, object);
        case 1:
          response = _context2.sent;
          console.log(response);
          _context2.next = 3;
          break;
        case 2:
          _context2.prev = 2;
          _t2 = _context2["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t2);
          // You can perform additional error handling actions if needed
          throw _t2;
        case 3:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 2]]);
  }));
  return function postData(_x, _x2) {
    return _ref2.apply(this, arguments);
  };
}();
var fetchEmailData = function fetchEmailData() {
  // Make a GET request and return the promise
  return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("".concat(URL, "getEmails")).then(function (response) {
    var emailArray = response.data.message.map(function (item) {
      return item.email;
    });
    // Return the data or do further processing
    return emailArray;
  }).catch(function (error) {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autoCompleter: function() { return /* binding */ autoCompleter; },
/* harmony export */   checkBox: function() { return /* binding */ checkBox; },
/* harmony export */   checkBox2: function() { return /* binding */ checkBox2; },
/* harmony export */   convertFormData: function() { return /* binding */ convertFormData; },
/* harmony export */   createAndAppendElement: function() { return /* binding */ createAndAppendElement; },
/* harmony export */   distinctValue: function() { return /* binding */ distinctValue; },
/* harmony export */   isChecked: function() { return /* binding */ isChecked; },
/* harmony export */   loaderIcon: function() { return /* binding */ loaderIcon; },
/* harmony export */   loaderIconBootstrap: function() { return /* binding */ loaderIconBootstrap; },
/* harmony export */   loaderIconBulma: function() { return /* binding */ loaderIconBulma; },
/* harmony export */   matchInput: function() { return /* binding */ matchInput; },
/* harmony export */   matchRegex: function() { return /* binding */ matchRegex; },
/* harmony export */   realTimeCheckLen: function() { return /* binding */ realTimeCheckLen; },
/* harmony export */   removeDiv: function() { return /* binding */ removeDiv; },
/* harmony export */   showResponse: function() { return /* binding */ showResponse; },
/* harmony export */   toSentenceCase: function() { return /* binding */ toSentenceCase; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! autocompleter */ "./node_modules/autocompleter/autocomplete.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(autocompleter__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");






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
  if (setClass) newDiv.className = "field ".concat(setClass);else newDiv.className = "field ".concat(setId);
  var parentDiv = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(parent);
  if (!parentDiv) throw new Error("Parent element '".concat(parent, "' not found"));
  return parentDiv.appendChild(newDiv);
};

/**
 * 
 * @param {the id of the input} inputId 
 * @param {the api data or array data} data 
 * @param { filterby is the data.filterby }
 */
var autoCompleter = function autoCompleter(inputId, data) {
  autocompleter__WEBPACK_IMPORTED_MODULE_2___default()({
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
  return (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(new Set(array));
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
    if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(yesId).checked) {
      alert('check');
      fn();
    } else if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(noId).checked) {
      alert('check No');
    }
  };
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(yesId).addEventListener('click', checked);
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(noId).addEventListener('click', checked);
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
  error = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(err);
  firstInput = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(first);
  secondInput = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(second);
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
  var formInput = (0,_global__WEBPACK_IMPORTED_MODULE_1__.qSelAll)(formId);
  var formInputArr = Array.from(formInput);
  return new _FormHelper__WEBPACK_IMPORTED_MODULE_3__["default"](formInputArr);
};
var showResponse = function showResponse(theId, message, status) {
  var responseEl = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(theId);
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
      var theData = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(input[i], "_id"));
      if (theData === null || theData === undefined || theData === "") {
        throw new Error("Element with ID '".concat(input[i], "_id' not found or is empty"));
      }
      var max = maxi[i];
      var error = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(input[i], "_error"));
      theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
      theData.addEventListener('keyup', function () {
        error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
        var help = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(input[i], "_help"));
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   processKidsSiblings: function() { return /* binding */ processKidsSiblings; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../data/checkEmailObj */ "./resources/asset/js/data/checkEmailObj.js");
/* harmony import */ var _components_global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");






var processKidsSiblings = function processKidsSiblings(emailData, firstName) {
  var famCode = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  // normalise email list once
  var emailSet = new Set((emailData || []).filter(Boolean).map(function (e) {
    return String(e).toLowerCase().trim();
  }));
  var getFamCode = function getFamCode() {
    var _ref, _id$value, _id;
    return (_ref = (_id$value = (_id = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)("famCode_id")) === null || _id === void 0 ? void 0 : _id.value) !== null && _id$value !== void 0 ? _id$value : famCode) !== null && _ref !== void 0 ? _ref : "";
  };

  // debounce so it doesn't fire too aggressively
  var t = null;
  var debounce = function debounce(fn) {
    var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 200;
    return function () {
      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }
      clearTimeout(t);
      t = setTimeout(function () {
        return fn.apply(void 0, args);
      }, wait);
    };
  };
  var onInput = debounce(function (e) {
    try {
      var _id$value2, _id2;
      var el = e.target;
      if (!el || el.tagName !== "INPUT" || el.type !== "email") return;
      var elementId = el.id;
      if (!elementId) return;

      // Only handle the ids we generate
      var isKid = _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.kidEmailInput.includes(elementId);
      var isSib = _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingEmail.includes(elementId);
      if (!isKid && !isSib) return;
      var emailInput = (el.value || "").toLowerCase().trim();
      var helpEl = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(elementId, "_help"));
      if (!helpEl) return;
      var chooseEmail = isKid ? _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.kidEmailInput : _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingEmail;
      var chooseName = isKid ? _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.childrenNameInput : _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingName;
      var index = chooseEmail.indexOf(elementId);
      var nameId = chooseName[index];
      var nameValue = (_id$value2 = (_id2 = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)(nameId)) === null || _id2 === void 0 ? void 0 : _id2.value) !== null && _id$value2 !== void 0 ? _id$value2 : "";
      if (emailInput.length > 0 && emailInput.length < 7) {
        helpEl.style.display = "block";
        helpEl.innerHTML = "Email may be too short";
        return;
      }
      var exists = emailInput !== "" && emailSet.has(emailInput);
      helpEl.style.display = "block";
      helpEl.dataset.email = emailInput;
      helpEl.dataset.name = nameValue;
      helpEl.dataset.familyCode = getFamCode();
      helpEl.innerHTML = exists ? "Great news! ".concat(nameValue || "This person", " is already registered on the platform") : "".concat(nameValue || "This person", " is not on the platform. Do you want us to send an email invite? ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_4__.checkBox)(elementId));
    } catch (err) {
      (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.showError)(err);
    }
  }, 250);
  var onClick = /*#__PURE__*/function () {
    var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
      var target, isYes, isNo, baseId, helpEl, email, name, familyCode, postObj, response, _t;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            _context.prev = 0;
            target = e.target;
            if (!(!target || !target.id)) {
              _context.next = 1;
              break;
            }
            return _context.abrupt("return");
          case 1:
            isYes = target.id.endsWith("Yes");
            isNo = target.id.endsWith("No");
            if (!(!isYes && !isNo)) {
              _context.next = 2;
              break;
            }
            return _context.abrupt("return");
          case 2:
            baseId = target.id.replace(/(Yes|No)$/, "");
            helpEl = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(baseId, "_help"));
            if (helpEl) {
              _context.next = 3;
              break;
            }
            return _context.abrupt("return");
          case 3:
            if (!isNo) {
              _context.next = 4;
              break;
            }
            helpEl.style.display = "none";
            return _context.abrupt("return");
          case 4:
            if (!(helpEl.dataset.sending === "1")) {
              _context.next = 5;
              break;
            }
            return _context.abrupt("return");
          case 5:
            helpEl.dataset.sending = "1";
            email = helpEl.dataset.email;
            name = helpEl.dataset.name;
            familyCode = helpEl.dataset.familyCode;
            if (!(!email || !name)) {
              _context.next = 6;
              break;
            }
            helpEl.dataset.sending = "";
            return _context.abrupt("return");
          case 6:
            postObj = {
              mobile: "",
              viewPath: "msg/contactNewMember",
              data: {
                email,
                name,
                yourName: firstName,
                familyCode
              },
              subject: "".concat(firstName, " wants you to join the family network")
            };
            _context.next = 7;
            return axios__WEBPACK_IMPORTED_MODULE_5__["default"].post("/register/contactNewMember", postObj);
          case 7:
            response = _context.sent;
            helpEl.innerHTML = response.data.message || "Invite sent";
            setTimeout(function () {
              helpEl.style.display = "none";
            }, 5000);
            helpEl.dataset.sending = "";
            _context.next = 9;
            break;
          case 8:
            _context.prev = 8;
            _t = _context["catch"](0);
            (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
          case 9:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[0, 8]]);
    }));
    return function onClick(_x) {
      return _ref2.apply(this, arguments);
    };
  }();
  document.addEventListener("input", onInput, true);
  document.addEventListener("click", onClick, true);

  // optional cleanup (if you ever re-init this)
  return function () {
    document.removeEventListener("input", onInput, true);
    document.removeEventListener("click", onClick, true);
  };
};

/***/ }),

/***/ "./resources/asset/js/data/checkEmailFactory.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/data/checkEmailFactory.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   makeCheckEmailObj: function() { return /* binding */ makeCheckEmailObj; }
/* harmony export */ });
// data/checkEmailFactory.js
var makeCheckEmailObj = function makeCheckEmailObj() {
  var kidsCount = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
  var siblingCount = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  var range = function range(n) {
    return Array.from({
      length: n
    }, function (_, i) {
      return i + 1;
    });
  };
  return {
    kidEmailInput: range(kidsCount).map(function (n) {
      return "children_email".concat(n);
    }),
    childrenNameInput: range(kidsCount).map(function (n) {
      return "children_name".concat(n);
    }),
    siblingEmail: range(siblingCount).map(function (n) {
      return "sibling_email".concat(n);
    }),
    siblingName: range(siblingCount).map(function (n) {
      return "sibling_name".concat(n);
    })
  };
};

/***/ }),

/***/ "./resources/asset/js/data/checkEmailObj.js":
/*!**************************************************!*\
  !*** ./resources/asset/js/data/checkEmailObj.js ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkEmailObj: function() { return /* binding */ checkEmailObj; }
/* harmony export */ });
// data/checkEmailObj.js
var checkEmailObj = {
  kidEmailInput: [],
  childrenNameInput: [],
  siblingEmail: [],
  siblingName: []
};

/***/ })

}]);
//# sourceMappingURL=accountSetting.js.map