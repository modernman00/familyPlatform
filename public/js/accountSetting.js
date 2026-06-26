"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["accountSetting"],{

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
var URL = "https://olaogun.test/";
// https: //laravel.com/docs/5.4/mix#environment-variables

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