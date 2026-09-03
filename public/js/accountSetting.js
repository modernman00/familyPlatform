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
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _accountSettingHelpers_handleFamilyChangeBootstrap__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./accountSettingHelpers/handleFamilyChangeBootstrap */ "./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");
/* harmony import */ var _profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./profilePage/registerPushNotification */ "./resources/asset/js/components/profilePage/registerPushNotification.js");










// Register update handlers for all account & family forms
var formsToRegister = [{
  formId: 'profileForm',
  buttonId: 'profileBtn'
}, {
  formId: 'accountSettingForm',
  buttonId: 'button'
},
// backward compatibility
{
  formId: 'parentsForm',
  buttonId: 'parentsBtn'
}, {
  formId: 'childrenForm',
  buttonId: 'childrenBtn'
}, {
  formId: 'siblingsForm',
  buttonId: 'siblingsBtn'
}, {
  formId: 'maritalForm',
  buttonId: 'maritalBtn'
}, {
  formId: 'passwordForm',
  buttonId: 'passwordBtn'
}, {
  formId: 'preferencesForm',
  buttonId: 'preferencesBtn'
}, {
  formId: 'privacyForm',
  buttonId: 'privacyBtn'
}];
formsToRegister.forEach(function (_ref) {
  var formId = _ref.formId,
    buttonId = _ref.buttonId;
  if ((0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(formId) && (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(buttonId)) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.update)({
      formId,
      route: '/accountSetting',
      buttonId,
      redirect: '/accountSetting'
    });
  }
});

// Function to show/hide spouse information based on marital status
var showSpouse = function showSpouse() {
  var maritalEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('maritalStatus');
  if (!maritalEl) return;
  var maritalStatus = maritalEl.value;
  if (maritalStatus === "Yes - Add Husband") {
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.showElement)('spouse');
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.hideElement)('maiden_name_div');
  } else if (maritalStatus === "Yes - Add Wife") {
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.showElement)('maiden_name_div');
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.showElement)('spouse');
  } else {
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.hideElement)('spouse');
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.hideElement)('maiden_name_div');
  }
};
var maritalSelect = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('maritalStatus');
if (maritalSelect) {
  maritalSelect.addEventListener('change', showSpouse);
  // Initial check on load
  showSpouse();
}

// URL Hash navigation support for deep-linking (e.g., /accountSetting#family-settings or #parents)
var handleHashNavigation = function handleHashNavigation() {
  var hash = window.location.hash;
  if (!hash) return;
  var hashMap = {
    '#profile': 'v-pills-profile-tab',
    '#parents': 'v-pills-parents-tab',
    '#family-settings': 'v-pills-parents-tab',
    '#children': 'v-pills-children-tab',
    '#siblings': 'v-pills-siblings-tab',
    '#marital': 'v-pills-marital-tab',
    '#marital-status': 'v-pills-marital-tab',
    '#password': 'v-pills-password-tab',
    '#preferences': 'v-pills-preferences-tab',
    '#privacy': 'v-pills-privacy-tab'
  };
  var targetTabId = hashMap[hash.toLowerCase()];
  if (targetTabId) {
    var tabBtn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(targetTabId);
    if (tabBtn) {
      var _window$bootstrap;
      if (typeof window !== 'undefined' && (_window$bootstrap = window.bootstrap) !== null && _window$bootstrap !== void 0 && _window$bootstrap.Tab) {
        var tab = new window.bootstrap.Tab(tabBtn);
        tab.show();
      } else {
        tabBtn.click();
      }
    }
  }
};
window.addEventListener('DOMContentLoaded', handleHashNavigation);
window.addEventListener('hashchange', handleHashNavigation);

// Kid / sibling email helper — checks each address against the server on demand
// (no bulk email list is pulled to the browser any more, SEC-4).
var fNameEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('fName');
var fName = fNameEl ? (fNameEl.textContent || '').trim() : '';
var famCode = localStorage.getItem('requesterFamCode');
(0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_5__.processKidsSiblings)(fName, famCode);

// ---- GDPR Art. 15 — "Download my data" ----------------------------------
(function initDataExport() {
  var btn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('dataExportBtn');
  var status = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('dataExportStatus');
  if (!btn) return;
  var say = function say(msg) {
    if (!status) return;
    status.textContent = msg;
    status.style.display = msg ? 'block' : 'none';
  };
  btn.addEventListener('click', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var _exec, res, blob, cd, name, url, a, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          btn.disabled = true;
          say('Preparing your data…');
          _context.prev = 1;
          _context.next = 2;
          return fetch('/account/data-export', {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
              'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
            }
          });
        case 2:
          res = _context.sent;
          if (res.ok) {
            _context.next = 3;
            break;
          }
          throw new Error("Export failed (".concat(res.status, ")"));
        case 3:
          _context.next = 4;
          return res.blob();
        case 4:
          blob = _context.sent;
          cd = res.headers.get('Content-Disposition') || '';
          name = ((_exec = /filename="?([^"]+)"?/.exec(cd)) === null || _exec === void 0 ? void 0 : _exec[1]) || 'familyplatform-data.json';
          url = URL.createObjectURL(blob);
          a = document.createElement('a');
          a.href = url;
          a.download = name;
          document.body.appendChild(a);
          a.click();
          a.remove();
          URL.revokeObjectURL(url);
          say('Download started. Check your downloads folder.');
          _context.next = 6;
          break;
        case 5:
          _context.prev = 5;
          _t = _context["catch"](1);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
          say('Sorry — we could not prepare your data. Please try again.');
        case 6:
          _context.prev = 6;
          btn.disabled = false;
          return _context.finish(6);
        case 7:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[1, 5, 6, 7]]);
  })));

  // ---- GDPR Art. 17 — request account deletion --------------------------
  var delBtn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('dataDeleteBtn');
  if (delBtn) {
    delBtn.addEventListener('click', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2() {
      var confirmed, _window$notify, _window, res, body, _t2, _t3;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            if (!window.Swal) {
              _context2.next = 2;
              break;
            }
            _context2.next = 1;
            return window.Swal.fire({
              icon: 'warning',
              title: 'Request account deletion?',
              html: 'Our team will permanently delete your account and personal data within 30 days. ' + 'Content other family members rely on may be kept in anonymised form. This cannot be undone.',
              showCancelButton: true,
              confirmButtonText: 'Yes, request deletion',
              confirmButtonColor: '#dc2626',
              cancelButtonText: 'Cancel'
            });
          case 1:
            _t2 = _context2.sent.isConfirmed;
            _context2.next = 3;
            break;
          case 2:
            _t2 = window.confirm('Request permanent deletion of your account? Our team will action it within 30 days.');
          case 3:
            confirmed = _t2;
            if (confirmed) {
              _context2.next = 4;
              break;
            }
            return _context2.abrupt("return");
          case 4:
            delBtn.disabled = true;
            say('Sending your request…');
            _context2.prev = 5;
            _context2.next = 6;
            return fetch('/account/request-deletion', {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
                'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
              }
            });
          case 6:
            res = _context2.sent;
            _context2.next = 7;
            return res.json().catch(function () {
              return {};
            });
          case 7:
            body = _context2.sent;
            if (res.ok) {
              _context2.next = 8;
              break;
            }
            throw new Error((body === null || body === void 0 ? void 0 : body.message) || "Request failed (".concat(res.status, ")"));
          case 8:
            say((body === null || body === void 0 ? void 0 : body.message) || 'Your deletion request has been received.');
            (_window$notify = (_window = window).notify) === null || _window$notify === void 0 ? void 0 : _window$notify.call(_window, (body === null || body === void 0 ? void 0 : body.message) || 'Deletion request received', 'success');
            _context2.next = 10;
            break;
          case 9:
            _context2.prev = 9;
            _t3 = _context2["catch"](5);
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t3);
            say('Sorry — we could not send your request. Please try again or email support.');
          case 10:
            _context2.prev = 10;
            delBtn.disabled = false;
            return _context2.finish(10);
          case 11:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[5, 9, 10, 11]]);
    })));
  }
})();

// ---- Browser / mobile push notification toggle (PUSH-1) ------------------
(function initPushToggle() {
  var toggle = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('pushPrefToggle');
  var label = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('pushPrefLabel');
  var hint = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('pushPrefHint');
  if (!toggle) return;
  var DEFAULT_HINT = 'Get instant alerts on this device even when the app is closed.';
  var paint = function paint(on, note) {
    toggle.checked = on;
    if (label) label.textContent = on ? 'ON' : 'OFF';
    if (hint) hint.textContent = note || DEFAULT_HINT;
  };
  var state = (0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.getPushState)();
  if (state === 'unsupported') {
    toggle.disabled = true;
    var pwa = window.pwaManager;
    if (pwa && pwa.isIOS && !pwa.isStandalone) {
      // iOS only delivers Web Push to an installed PWA (iOS 16.4+). Route the
      // user through the existing "Add to Home Screen" walkthrough (PUSH-3).
      if (hint) {
        hint.innerHTML = 'On iPhone, notifications work once the app is on your Home Screen. ' + '<a href="#" id="pushIosInstall" style="font-weight:600;text-decoration:underline;">Show me how</a>.';
        var link = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('pushIosInstall');
        if (link) {
          link.addEventListener('click', function (e) {
            var _pwa$showIOSOverlay;
            e.preventDefault();
            (_pwa$showIOSOverlay = pwa.showIOSOverlay) === null || _pwa$showIOSOverlay === void 0 ? void 0 : _pwa$showIOSOverlay.call(pwa);
          });
        }
      }
    } else {
      paint(false, 'This browser does not support push notifications.');
    }
    return;
  }
  if (state === 'denied') {
    toggle.disabled = true;
    paint(false, 'Notifications are blocked in your browser settings. Re-enable them there, then reload this page.');
    return;
  }
  (0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.isPushSubscribed)().then(function (subbed) {
    return paint(subbed);
  });
  toggle.addEventListener('change', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3() {
    var wantOn, res, _window$notify2, _window2, _window$notify3, _window3, _t4;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          toggle.disabled = true;
          wantOn = toggle.checked;
          _context3.prev = 1;
          if (!wantOn) {
            _context3.next = 3;
            break;
          }
          _context3.next = 2;
          return (0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.enablePushNotifications)();
        case 2:
          res = _context3.sent;
          if (res.ok) {
            paint(true, 'Push notifications are on for this device.');
            (_window$notify2 = (_window2 = window).notify) === null || _window$notify2 === void 0 ? void 0 : _window$notify2.call(_window2, 'Push notifications enabled', 'success');
          } else if (res.reason === 'denied') {
            paint(false, 'You dismissed the browser prompt. Allow notifications to turn this on.');
          } else {
            paint(false, 'Could not enable notifications. Please try again.');
          }
          _context3.next = 5;
          break;
        case 3:
          _context3.next = 4;
          return (0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.disablePushNotifications)();
        case 4:
          paint(false, 'Push notifications are off for this device.');
          (_window$notify3 = (_window3 = window).notify) === null || _window$notify3 === void 0 ? void 0 : _window$notify3.call(_window3, 'Push notifications turned off', 'success');
        case 5:
          _context3.next = 7;
          break;
        case 6:
          _context3.prev = 6;
          _t4 = _context3["catch"](1);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t4);
          paint(!wantOn);
        case 7:
          _context3.prev = 7;
          toggle.disabled = false;
          return _context3.finish(7);
        case 8:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[1, 6, 7, 8]]);
  })));
})();

/***/ }),

/***/ "./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js":
/*!********************************************************************************************!*\
  !*** ./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js ***!
  \********************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "show": function() { return /* binding */ show; }
/* harmony export */ });
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _renderFamilyBootstrap__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./renderFamilyBootstrap */ "./resources/asset/js/components/accountSettingHelpers/renderFamilyBootstrap.js");
/* harmony import */ var _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../data/checkEmailObj */ "./resources/asset/js/data/checkEmailObj.js");
/* harmony import */ var _data_checkEmailFactory__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../data/checkEmailFactory */ "./resources/asset/js/data/checkEmailFactory.js");







/**
 * Adapted for Account Setting Page (Bootstrap 5)
 */

var syncCheckEmailObj = function syncCheckEmailObj() {
  var _id, _id2;
  var kids = Number((_id = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("children")) === null || _id === void 0 ? void 0 : _id.value) || 0;
  var siblings = Number((_id2 = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("sibling")) === null || _id2 === void 0 ? void 0 : _id2.value) || 0;

  // IMPORTANT: mutate the same object reference
  Object.assign(_data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj, (0,_data_checkEmailFactory__WEBPACK_IMPORTED_MODULE_3__.makeCheckEmailObj)(kids, siblings));
};
var show = function show(kids_or_sib, event) {
  try {
    var value = Number(event.target.value) || 0;

    // ✅ unique container IDs (avoid clashing with <select id="children">)
    var containerId = "".concat(kids_or_sib, "_inputs");
    var parentId = "".concat(kids_or_sib, "_div");

    // remove the existing dynamic container
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.removeDiv)(containerId);
    var helpEl = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help"));
    if (helpEl) helpEl.innerHTML = "";
    if (value === 0) {
      syncCheckEmailObj();
      return;
    }
    // create the container under wrapper
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.createAndAppendElement)("div", containerId, parentId);
    if (helpEl) {
      helpEl.innerHTML = value > 1 ? "Please, provide details for each below:" : "Please, provide details below:";
      helpEl.classList.remove("text-danger"); // Use BS5 classes or JS logic? keeping simple
      helpEl.style.color = ""; // Reset custom color if any
    }
    var container = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(containerId);
    if (!container) return;
    for (var i = 0; i < value; i++) {
      var no = i + 1;
      var html = (0,_renderFamilyBootstrap__WEBPACK_IMPORTED_MODULE_1__.renderHtmlFamilyBootstrap)(kids_or_sib, no);
      container.insertAdjacentHTML("beforeEnd", html);
    }

    // 🔥 after DOM changes, regenerate ID lists
    syncCheckEmailObj();
  } catch (error) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
var onChangeKidAndSiblings = function onChangeKidAndSiblings() {
  var kidInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("children");
  var sibInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("sibling");
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
/* harmony export */   "renderHtmlFamilyBootstrap": function() { return /* binding */ renderHtmlFamilyBootstrap; }
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
  return "\n    <div class=\"p-4 rounded-3 mb-4 wrapper-".concat(kids_sib, "-").concat(no, "\" style=\"background-color: #f8fafc; border: 1px solid #e2e8f0;\">\n      \n      <h6 class=\"fw-bold mb-3 text-dark text-uppercase\" style=\"letter-spacing: 0.5px; font-size: 0.9rem;\">\n        #").concat(no, " ").concat(labelText, " Information\n      </h6>\n\n      <div class=\"row g-3\">\n      \n        <!-- Relationship Select -->\n        <div class=\"col-md-4\">\n           <label class=\"form-label\" for=\"").concat(kids_sib, "_option").concat(no, "\">\n               ").concat(optionsHtmlText, "\n           </label>\n           <select class=\"form-select\" name=\"").concat(kids_sib, "_option").concat(no, "\" id=\"").concat(kids_sib, "_option").concat(no, "\">\n              ").concat(optionsHtml, "\n           </select>\n        </div>\n\n        <!-- First Name Input -->\n        <div class=\"col-md-4\">\n           <label class=\"form-label\" for=\"").concat(kids_sib, "_first_name").concat(no, "\">\n               First Name\n           </label>\n           <input \n              type=\"text\" \n              class=\"form-control\" \n              placeholder=\"e.g. John\" \n              name=\"").concat(kids_sib, "_first_name").concat(no, "\" \n              id=\"").concat(kids_sib, "_first_name").concat(no, "\"\n              autocomplete=\"off\"\n           >\n        </div>\n\n        <!-- Last Name Input -->\n        <div class=\"col-md-4\">\n           <label class=\"form-label\" for=\"").concat(kids_sib, "_last_name").concat(no, "\">\n               Last Name\n           </label>\n           <input \n              type=\"text\" \n              class=\"form-control\" \n              placeholder=\"e.g. Doe\" \n              name=\"").concat(kids_sib, "_last_name").concat(no, "\" \n              id=\"").concat(kids_sib, "_last_name").concat(no, "\"\n              autocomplete=\"off\"\n           >\n        </div>\n\n        <!-- Email Input (Full Width) -->\n        <div class=\"col-12\">\n           <label class=\"form-label\" for=\"").concat(kids_sib, "_email").concat(no, "\">\n               Email Address\n           </label>\n           <input \n              type=\"email\" \n              class=\"form-control\" \n              placeholder=\"e.g. john.doe@example.com\" \n              name=\"").concat(kids_sib, "_email").concat(no, "\" \n              id=\"").concat(kids_sib, "_email").concat(no, "\"\n              autocomplete=\"off\"\n           >\n           <!-- Dynamic help text area -->\n           <div class=\"form-text text-danger mt-1\" id=\"").concat(kids_sib, "_email").concat(no, "_help\" style=\"min-height: 20px; font-size: 0.8rem;\"></div>\n        </div>\n\n      </div>\n    </div>\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/api/index.js":
/*!****************************************************!*\
  !*** ./resources/asset/js/components/api/index.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "emailIsRegistered": function() { return /* binding */ emailIsRegistered; },
/* harmony export */   "getAllData": function() { return /* binding */ getAllData; },
/* harmony export */   "postData": function() { return /* binding */ postData; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");




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
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].get("".concat(URL, "allMembers/processApiData2"), config);
        case 1:
          response = _context.sent;
          return _context.abrupt("return", response.data);
        case 2:
          _context.prev = 2;
          _t = _context["catch"](0);
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.showError)(_t);
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
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post(url, object);
        case 1:
          response = _context2.sent;
          console.log(response);
          _context2.next = 3;
          break;
        case 2:
          _context2.prev = 2;
          _t2 = _context2["catch"](0);
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.showError)(_t2);
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

/**
 * Ask the server whether a single email address already belongs to a registered
 * account. Replaces the old bulk fetch of every member email (SEC-4).
 * @param {string} email
 * @returns {Promise<boolean>}
 */
var emailIsRegistered = /*#__PURE__*/function () {
  var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(email) {
    var _response$data, _response$data$messag, response, _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.prev = 0;
          _context3.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].get("".concat(URL, "getEmails"), {
            params: {
              email
            },
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
        case 1:
          response = _context3.sent;
          return _context3.abrupt("return", (response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : (_response$data$messag = _response$data.message) === null || _response$data$messag === void 0 ? void 0 : _response$data$messag.exists) === true);
        case 2:
          _context3.prev = 2;
          _t3 = _context3["catch"](0);
          // Fail closed-ish: treat as "not registered" so the invite path still works.
          console.error('emailIsRegistered check failed:', _t3);
          return _context3.abrupt("return", false);
        case 3:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[0, 2]]);
  }));
  return function emailIsRegistered(_x3) {
    return _ref3.apply(this, arguments);
  };
}();

/***/ }),

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "autoCompleter": function() { return /* binding */ autoCompleter; },
/* harmony export */   "checkBox": function() { return /* binding */ checkBox; },
/* harmony export */   "checkBox2": function() { return /* binding */ checkBox2; },
/* harmony export */   "convertFormData": function() { return /* binding */ convertFormData; },
/* harmony export */   "createAndAppendElement": function() { return /* binding */ createAndAppendElement; },
/* harmony export */   "distinctValue": function() { return /* binding */ distinctValue; },
/* harmony export */   "isChecked": function() { return /* binding */ isChecked; },
/* harmony export */   "loaderIcon": function() { return /* binding */ loaderIcon; },
/* harmony export */   "loaderIconBootstrap": function() { return /* binding */ loaderIconBootstrap; },
/* harmony export */   "loaderIconBulma": function() { return /* binding */ loaderIconBulma; },
/* harmony export */   "matchInput": function() { return /* binding */ matchInput; },
/* harmony export */   "matchRegex": function() { return /* binding */ matchRegex; },
/* harmony export */   "realTimeCheckLen": function() { return /* binding */ realTimeCheckLen; },
/* harmony export */   "removeDiv": function() { return /* binding */ removeDiv; },
/* harmony export */   "showResponse": function() { return /* binding */ showResponse; },
/* harmony export */   "toSentenceCase": function() { return /* binding */ toSentenceCase; }
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
      fn();
    } else if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(noId).checked) {}
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
/* harmony export */   "processKidsSiblings": function() { return /* binding */ processKidsSiblings; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../data/checkEmailObj */ "./resources/asset/js/data/checkEmailObj.js");
/* harmony import */ var _components_global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./api/index */ "./resources/asset/js/components/api/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");







var processKidsSiblings = function processKidsSiblings(firstName) {
  var famCode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  // Per-email existence check (scoped server lookup) with a tiny local cache so
  // repeated keystrokes on the same address don't re-hit the endpoint.
  var existsCache = new Map();
  var checkExists = /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(email) {
      var result;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            if (!existsCache.has(email)) {
              _context.next = 1;
              break;
            }
            return _context.abrupt("return", existsCache.get(email));
          case 1:
            _context.next = 2;
            return (0,_api_index__WEBPACK_IMPORTED_MODULE_5__.emailIsRegistered)(email);
          case 2:
            result = _context.sent;
            existsCache.set(email, result);
            return _context.abrupt("return", result);
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }));
    return function checkExists(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  var getFamCode = function getFamCode() {
    var _ref2, _id$value, _id;
    return (_ref2 = (_id$value = (_id = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)("famCode_id")) === null || _id === void 0 ? void 0 : _id.value) !== null && _id$value !== void 0 ? _id$value : famCode) !== null && _ref2 !== void 0 ? _ref2 : "";
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
  var onInput = debounce(/*#__PURE__*/function () {
    var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(e) {
      var _id$value2, _id2, el, elementId, isKid, isSib, emailInput, helpEl, chooseEmail, chooseName, index, nameId, nameValue, _id$value3, _id3, _id$value4, _id4, prefix, no, fn, ln, exists, _t;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            _context2.prev = 0;
            el = e.target;
            if (!(!el || el.tagName !== "INPUT" || el.type !== "email")) {
              _context2.next = 1;
              break;
            }
            return _context2.abrupt("return");
          case 1:
            elementId = el.id;
            if (elementId) {
              _context2.next = 2;
              break;
            }
            return _context2.abrupt("return");
          case 2:
            // Only handle the ids we generate
            isKid = _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.kidEmailInput.includes(elementId);
            isSib = _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingEmail.includes(elementId);
            if (!(!isKid && !isSib)) {
              _context2.next = 3;
              break;
            }
            return _context2.abrupt("return");
          case 3:
            emailInput = (el.value || "").toLowerCase().trim();
            helpEl = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(elementId, "_help"));
            if (helpEl) {
              _context2.next = 4;
              break;
            }
            return _context2.abrupt("return");
          case 4:
            chooseEmail = isKid ? _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.kidEmailInput : _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingEmail;
            chooseName = isKid ? _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.childrenNameInput : _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingName;
            index = chooseEmail.indexOf(elementId);
            nameId = chooseName[index];
            nameValue = (_id$value2 = (_id2 = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)(nameId)) === null || _id2 === void 0 ? void 0 : _id2.value) !== null && _id$value2 !== void 0 ? _id$value2 : "";
            if (!nameValue) {
              prefix = isKid ? "children" : "sibling";
              no = index + 1;
              fn = (_id$value3 = (_id3 = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(prefix, "_first_name").concat(no))) === null || _id3 === void 0 ? void 0 : _id3.value) !== null && _id$value3 !== void 0 ? _id$value3 : "";
              ln = (_id$value4 = (_id4 = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(prefix, "_last_name").concat(no))) === null || _id4 === void 0 ? void 0 : _id4.value) !== null && _id$value4 !== void 0 ? _id$value4 : "";
              nameValue = "".concat(fn, " ").concat(ln).trim();
            }
            if (emailInput) {
              _context2.next = 5;
              break;
            }
            helpEl.style.display = "none";
            helpEl.innerHTML = "";
            delete helpEl.dataset.email;
            delete helpEl.dataset.name;
            delete helpEl.dataset.familyCode;
            return _context2.abrupt("return");
          case 5:
            if (!(emailInput.length > 0 && emailInput.length < 7)) {
              _context2.next = 6;
              break;
            }
            helpEl.style.display = "block";
            helpEl.innerHTML = "Email may be too short";
            return _context2.abrupt("return");
          case 6:
            _context2.next = 7;
            return checkExists(emailInput);
          case 7:
            exists = _context2.sent;
            helpEl.style.display = "block";
            helpEl.dataset.email = emailInput;
            helpEl.dataset.name = nameValue;
            helpEl.dataset.familyCode = getFamCode();
            helpEl.innerHTML = exists ? "Great news! ".concat(nameValue || "This person", " is already registered on the platform") : "".concat(nameValue || "This person", " is not on the platform. Do you want us to send an email invite? ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_4__.checkBox)(elementId));
            _context2.next = 9;
            break;
          case 8:
            _context2.prev = 8;
            _t = _context2["catch"](0);
            (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
          case 9:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[0, 8]]);
    }));
    return function (_x2) {
      return _ref3.apply(this, arguments);
    };
  }(), 250);
  var onClick = /*#__PURE__*/function () {
    var _ref4 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(e) {
      var target, isYes, isNo, baseId, helpEl, email, name, familyCode, postObj, response, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
        while (1) switch (_context3.prev = _context3.next) {
          case 0:
            _context3.prev = 0;
            target = e.target;
            if (!(!target || !target.id)) {
              _context3.next = 1;
              break;
            }
            return _context3.abrupt("return");
          case 1:
            isYes = target.id.endsWith("Yes");
            isNo = target.id.endsWith("No");
            if (!(!isYes && !isNo)) {
              _context3.next = 2;
              break;
            }
            return _context3.abrupt("return");
          case 2:
            baseId = target.id.replace(/(Yes|No)$/, "");
            helpEl = (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(baseId, "_help"));
            if (helpEl) {
              _context3.next = 3;
              break;
            }
            return _context3.abrupt("return");
          case 3:
            if (!isNo) {
              _context3.next = 4;
              break;
            }
            helpEl.style.display = "none";
            return _context3.abrupt("return");
          case 4:
            if (!(helpEl.dataset.sending === "1")) {
              _context3.next = 5;
              break;
            }
            return _context3.abrupt("return");
          case 5:
            helpEl.dataset.sending = "1";
            email = helpEl.dataset.email;
            name = helpEl.dataset.name;
            familyCode = helpEl.dataset.familyCode;
            if (!(!email || !name)) {
              _context3.next = 6;
              break;
            }
            helpEl.dataset.sending = "";
            return _context3.abrupt("return");
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
            _context3.next = 7;
            return axios__WEBPACK_IMPORTED_MODULE_6__["default"].post("/register/contactNewMember", postObj);
          case 7:
            response = _context3.sent;
            helpEl.innerHTML = response.data.message || "Invite sent";
            setTimeout(function () {
              helpEl.style.display = "none";
            }, 5000);
            helpEl.dataset.sending = "";
            _context3.next = 9;
            break;
          case 8:
            _context3.prev = 8;
            _t2 = _context3["catch"](0);
            (0,_components_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t2);
          case 9:
          case "end":
            return _context3.stop();
        }
      }, _callee3, null, [[0, 8]]);
    }));
    return function onClick(_x3) {
      return _ref4.apply(this, arguments);
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

/***/ "./resources/asset/js/components/profilePage/registerPushNotification.js":
/*!*******************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/registerPushNotification.js ***!
  \*******************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "disablePushNotifications": function() { return /* binding */ disablePushNotifications; },
/* harmony export */   "enablePushNotifications": function() { return /* binding */ enablePushNotifications; },
/* harmony export */   "getPushState": function() { return /* binding */ getPushState; },
/* harmony export */   "isPushSubscribed": function() { return /* binding */ isPushSubscribed; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


/**
 * Web Push — the single client module (PUSH-1 / PUSH-4).
 *
 * Side effect on import: registers the service worker and, if the browser
 * permission is already `granted`, re-syncs the subscription so the server's
 * record stays fresh.
 *
 * Exports for a Settings toggle (must be called from a real user gesture):
 *   enablePushNotifications()  -> Promise<{ok, reason?}>
 *   disablePushNotifications() -> Promise<{ok}>
 *   getPushState()             -> 'unsupported' | 'default' | 'granted' | 'denied'
 *   isPushSubscribed()         -> Promise<boolean>
 */


var VAPID_PUBLIC_KEY = "BAvqqppvGj5V0DqzieyYq5nGu9EW_db01_7jXO1_Nk-8UZzKJpCs1eGYx5d0yuBe7q3xu6oWaFS8etO9lazRMMo" || 0;
var PUSH_PROMPT_DISMISSED_KEY = 'push_prompt_dismissed';
var pushSupported = function pushSupported() {
  return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
};
function urlBase64ToUint8Array(base64String) {
  var padding = '='.repeat((4 - base64String.length % 4) % 4);
  var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  var raw = window.atob(base64);
  var out = new Uint8Array(raw.length);
  for (var i = 0; i < raw.length; ++i) out[i] = raw.charCodeAt(i);
  return out;
}
function keyToBase64(subscription, name) {
  return btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey(name))));
}
function syncToServer(_x) {
  return _syncToServer.apply(this, arguments);
} // ---- import-time bootstrap: keep an existing grant in sync -----------------
function _syncToServer() {
  _syncToServer = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(subscription) {
    var payload;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          payload = {
            endpoint: subscription.endpoint,
            keys: {
              p256dh: keyToBase64(subscription, 'p256dh'),
              auth: keyToBase64(subscription, 'auth')
            }
          };
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/pushNotification/subscription', payload, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)(),
              'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)()
            }
          });
        case 1:
        case "end":
          return _context2.stop();
      }
    }, _callee2);
  }));
  return _syncToServer.apply(this, arguments);
}
if (pushSupported()) {
  navigator.serviceWorker.register('/service-worker.js').then(function (swReg) {
    return swReg.pushManager.getSubscription().then(function (sub) {
      if (sub) return syncToServer(sub).catch(function (e) {
        return console.warn('[push] resync failed', e);
      });
      if (Notification.permission === 'granted' && VAPID_PUBLIC_KEY) {
        return doSubscribe(swReg);
      }
    });
  }).catch(function (err) {
    return console.warn('[push] SW registration failed', err);
  });
}
function doSubscribe(_x2) {
  return _doSubscribe.apply(this, arguments);
}
function _doSubscribe() {
  _doSubscribe = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(swReg) {
    var sub;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.next = 1;
          return swReg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
          });
        case 1:
          sub = _context3.sent;
          _context3.next = 2;
          return syncToServer(sub);
        case 2:
          return _context3.abrupt("return", sub);
        case 3:
        case "end":
          return _context3.stop();
      }
    }, _callee3);
  }));
  return _doSubscribe.apply(this, arguments);
}
function showPushPrompt() {
  var _document$getElementB, _document$getElementB2;
  if (!VAPID_PUBLIC_KEY || Notification.permission !== 'default') return;
  if (localStorage.getItem(PUSH_PROMPT_DISMISSED_KEY)) return;
  if (document.getElementById('push-permission-banner')) return;
  var banner = document.createElement('div');
  banner.id = 'push-permission-banner';
  banner.innerHTML = "\n    <div class=\"pwa-banner-card\">\n      <div class=\"pwa-banner-header\">\n        <div class=\"pwa-banner-text\">\n          <h6>Stay connected with your family</h6>\n          <p>Allow notifications for new friend requests, events, and family updates.</p>\n        </div>\n        <button type=\"button\" id=\"push-prompt-close\" class=\"pwa-btn-close\" aria-label=\"Dismiss\">&times;</button>\n      </div>\n      <div class=\"pwa-banner-actions\">\n        <button type=\"button\" id=\"push-prompt-enable\" class=\"pwa-btn pwa-btn-primary\">Allow notifications</button>\n      </div>\n    </div>\n  ";
  document.body.appendChild(banner);
  setTimeout(function () {
    return banner.classList.add('show');
  }, 100);
  var dismiss = function dismiss() {
    localStorage.setItem(PUSH_PROMPT_DISMISSED_KEY, String(Date.now()));
    banner.remove();
  };
  (_document$getElementB = document.getElementById('push-prompt-close')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.addEventListener('click', dismiss);
  (_document$getElementB2 = document.getElementById('push-prompt-enable')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.addEventListener('click', /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(event) {
      var button, result;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            button = event.currentTarget;
            button.disabled = true;
            button.textContent = 'Enabling...';
            _context.next = 1;
            return enablePushNotifications();
          case 1:
            result = _context.sent;
            if (!result.ok) {
              _context.next = 2;
              break;
            }
            banner.remove();
            return _context.abrupt("return");
          case 2:
            button.disabled = false;
            button.textContent = 'Allow notifications';
            if (result.reason === 'denied') dismiss();
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }));
    return function (_x3) {
      return _ref.apply(this, arguments);
    };
  }());
}
if (pushSupported()) {
  window.addEventListener('load', showPushPrompt, {
    once: true
  });
}

// ---- public API ----------------------------------------------------------
function getPushState() {
  if (!pushSupported()) return 'unsupported';
  return Notification.permission; // 'default' | 'granted' | 'denied'
}
function isPushSubscribed() {
  return _isPushSubscribed.apply(this, arguments);
}
function _isPushSubscribed() {
  _isPushSubscribed = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee4() {
    var swReg, _t, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context4) {
      while (1) switch (_context4.prev = _context4.next) {
        case 0:
          if (pushSupported()) {
            _context4.next = 1;
            break;
          }
          return _context4.abrupt("return", false);
        case 1:
          _context4.prev = 1;
          _context4.next = 2;
          return navigator.serviceWorker.ready;
        case 2:
          swReg = _context4.sent;
          _context4.next = 3;
          return swReg.pushManager.getSubscription();
        case 3:
          _t = _context4.sent;
          return _context4.abrupt("return", _t !== null);
        case 4:
          _context4.prev = 4;
          _t2 = _context4["catch"](1);
          return _context4.abrupt("return", false);
        case 5:
        case "end":
          return _context4.stop();
      }
    }, _callee4, null, [[1, 4]]);
  }));
  return _isPushSubscribed.apply(this, arguments);
}
function enablePushNotifications() {
  return _enablePushNotifications.apply(this, arguments);
}
function _enablePushNotifications() {
  _enablePushNotifications = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee5() {
    var permission, swReg, existing, _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context5) {
      while (1) switch (_context5.prev = _context5.next) {
        case 0:
          if (pushSupported()) {
            _context5.next = 1;
            break;
          }
          return _context5.abrupt("return", {
            ok: false,
            reason: 'unsupported'
          });
        case 1:
          if (VAPID_PUBLIC_KEY) {
            _context5.next = 2;
            break;
          }
          return _context5.abrupt("return", {
            ok: false,
            reason: 'misconfigured'
          });
        case 2:
          permission = Notification.permission;
          if (!(permission === 'default')) {
            _context5.next = 4;
            break;
          }
          _context5.next = 3;
          return Notification.requestPermission();
        case 3:
          permission = _context5.sent;
        case 4:
          if (!(permission !== 'granted')) {
            _context5.next = 5;
            break;
          }
          return _context5.abrupt("return", {
            ok: false,
            reason: permission
          });
        case 5:
          _context5.prev = 5;
          _context5.next = 6;
          return navigator.serviceWorker.ready;
        case 6:
          swReg = _context5.sent;
          _context5.next = 7;
          return swReg.pushManager.getSubscription();
        case 7:
          existing = _context5.sent;
          if (!existing) {
            _context5.next = 9;
            break;
          }
          _context5.next = 8;
          return syncToServer(existing);
        case 8:
          _context5.next = 10;
          break;
        case 9:
          _context5.next = 10;
          return doSubscribe(swReg);
        case 10:
          return _context5.abrupt("return", {
            ok: true
          });
        case 11:
          _context5.prev = 11;
          _t3 = _context5["catch"](5);
          console.error('[push] enable failed', _t3);
          return _context5.abrupt("return", {
            ok: false,
            reason: 'error'
          });
        case 12:
        case "end":
          return _context5.stop();
      }
    }, _callee5, null, [[5, 11]]);
  }));
  return _enablePushNotifications.apply(this, arguments);
}
function disablePushNotifications() {
  return _disablePushNotifications.apply(this, arguments);
}
function _disablePushNotifications() {
  _disablePushNotifications = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee6() {
    var swReg, sub, endpoint, _t4;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context6) {
      while (1) switch (_context6.prev = _context6.next) {
        case 0:
          if (pushSupported()) {
            _context6.next = 1;
            break;
          }
          return _context6.abrupt("return", {
            ok: true
          });
        case 1:
          _context6.prev = 1;
          _context6.next = 2;
          return navigator.serviceWorker.ready;
        case 2:
          swReg = _context6.sent;
          _context6.next = 3;
          return swReg.pushManager.getSubscription();
        case 3:
          sub = _context6.sent;
          if (!sub) {
            _context6.next = 5;
            break;
          }
          endpoint = sub.endpoint;
          _context6.next = 4;
          return sub.unsubscribe();
        case 4:
          _context6.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/pushNotification/unsubscribe', {
            endpoint
          }, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)(),
              'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)()
            }
          }).catch(function (e) {
            return console.warn('[push] server unsubscribe failed', e);
          });
        case 5:
          return _context6.abrupt("return", {
            ok: true
          });
        case 6:
          _context6.prev = 6;
          _t4 = _context6["catch"](1);
          console.error('[push] disable failed', _t4);
          return _context6.abrupt("return", {
            ok: false
          });
        case 7:
        case "end":
          return _context6.stop();
      }
    }, _callee6, null, [[1, 6]]);
  }));
  return _disablePushNotifications.apply(this, arguments);
}

/***/ }),

/***/ "./resources/asset/js/data/checkEmailFactory.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/data/checkEmailFactory.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "makeCheckEmailObj": function() { return /* binding */ makeCheckEmailObj; }
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
/* harmony export */   "checkEmailObj": function() { return /* binding */ checkEmailObj; }
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