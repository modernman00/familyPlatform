(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["register"],{

/***/ "./resources/asset/js/components/api/index.js":
/*!****************************************************!*\
  !*** ./resources/asset/js/components/api/index.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
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

/***/ "./resources/asset/js/components/dataToCheck.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/dataToCheck.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Login: function() { return /* binding */ Login; },
/* harmony export */   dataToCheckRegister: function() { return /* binding */ dataToCheckRegister; }
/* harmony export */ });


var dataToCheckRegister = {
  maxLength: {
    id: ['firstName', 'lastName', 'spouseName', 'spouseMobile', 'motherMobile', 'fatherMobile', 'fatherName', 'motherName', 'country', 'mobile', 'email', 'occupation'],
    max: [15, 15, 15, 12, 12, 12, 30, 30, 15, 13, 45, 20]
  },
  password: {
    pwd: 'password',
    pwd2: 'confirm_password'
  },
  familyCheck: {
    father: ["fatherYes", "fatherNo"],
    mother: ["motherYes", "motherNo"],
    spouse: ["spouseYes", "spouseNo"]
  }
};
var Login = {
  maxLength: {
    id: ['email', 'password'],
    max: [35, 35]
  }
};

/***/ }),

/***/ "./resources/asset/js/components/helper/autocomplete.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/helper/autocomplete.js ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autocomplete: function() { return /* binding */ autocomplete; }
/* harmony export */ });
var autocomplete = function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function (e) {
    var a,
      b,
      i,
      val = this.value;
    /*close any already open lists of autocompleted values*/
    closeAllLists();
    if (!val) {
      return false;
    }
    currentFocus = -1;
    /*create a DIV element that will contain the items (values):*/
    a = document.createElement("DIV");
    a.setAttribute("id", this.id + "autocomplete-list");
    a.setAttribute("class", "autocomplete-items");
    /*append the DIV element as a child of the autocomplete container:*/
    this.parentNode.appendChild(a);
    /*for each item in the array...*/
    for (i = 0; i < arr.length; i++) {
      /*check if the item starts with the same letters as the text field value:*/
      if (arr[i] && arr[i].substr(0, val.length).toUpperCase() === val.toUpperCase()) {
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
          b.addEventListener("click", function (e) {
            /*insert the value for the autocomplete text field:*/
            inp.value = this.getElementsByTagName("input")[0].value;
            /*close the list of autocompleted values,
            (or any other open lists of autocompleted values:*/
            closeAllLists();
          });
          a.appendChild(b);
        }
      }
    }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function (e) {
    var x = document.getElementById(this.id + "autocomplete-list");
    if (x) x = x.getElementsByTagName("div");
    if (e.keyCode == 40) {
      /*If the arrow DOWN key is pressed,
      increase the currentFocus variable:*/
      currentFocus++;
      /*and and make the current item more visible:*/
      addActive(x);
    } else if (e.keyCode == 38) {
      //up
      /*If the arrow UP key is pressed,
      decrease the currentFocus variable:*/
      currentFocus--;
      /*and and make the current item more visible:*/
      addActive(x);
    } else if (e.keyCode == 13) {
      /*If the ENTER key is pressed, prevent the form from being submitted,*/
      e.preventDefault();
      if (currentFocus > -1) {
        /*and simulate a click on the "active" item:*/
        if (x) x[currentFocus].click();
      }
    }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = x.length - 1;
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }
  /*execute a function when someone clicks in the document:*/
  document.addEventListener("click", function (e) {
    closeAllLists(e.target);
  });
};

/***/ }),

/***/ "./resources/asset/js/components/kidsAndSiblings.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/kidsAndSiblings.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
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

/***/ "./resources/asset/js/components/register/event.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/event.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   hideElement: function() { return /* binding */ hideElement; },
/* harmony export */   showSpouse: function() { return /* binding */ showSpouse; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");

var hideElement = function hideElement(elementId) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(elementId).style.display = "none";
};
var showElement = function showElement(elementId) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(elementId).style.display = "block";
};
var showMaidenName = function showMaidenName() {
  var gender = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('gender_id');
  var maritalStatus = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus_id');
  if (gender.value === "Female" && maritalStatus.value === "Yes") {
    showElement('maidenName_div');
  } else {
    hideElement('maidenName_div');
  }
};
var showSpouse = function showSpouse() {
  var maritalStatus = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus_id').value;
  if (maritalStatus === "Married") {
    showElement('spouse');
  } else {
    hideElement('spouse');
  }
};

// Add event listeners

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus_id').addEventListener('change', showSpouse);
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('gender_id').addEventListener('change', showMaidenName);

// Hide elements by default
hideElement('spouse');
hideElement('maidenName_div');

// Other initializations
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('register_notify_div').style.display = "none";

// inject student after employment status value is student 

var injectStudent = function injectStudent() {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('employmentStatus_id').addEventListener('change', function (e) {
    var value = e.target.value;
    if (value === "Student") {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('occupation_id').value = 'Student';
    }
  });
};
injectStudent();

/***/ }),

/***/ "./resources/asset/js/components/register/familyCode.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/familyCode.js ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



var button = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("btnFamCode");
btnFamCode.addEventListener("click", function () {
  try {
    if ((0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('surname_id').value !== "") {
      var uniqueNumber = Date.now();
      var uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber) / 10000000);
      var getSurname = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('surname_id').value;
      var firstFourLetters = getSurname.substring(0, 4);
      (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('createCode').value = "".concat(firstFourLetters.toUpperCase()).concat(uniqueNumber1);
      btnFamCode.disabled = true;
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("surname_error").innerHTML = error.messages;
  }
});

// Get references to the HTML output and the copy icon

var copyIcon = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('copyIcon');
var htmlOutputDiv = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('createFamCode');
var htmlOutput = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('createCode');
copyIcon.addEventListener('click', /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
    var range, selection, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          copyIcon.innerHTML = "";
          _context.prev = 1;
          e.preventDefault();

          // check if the family code has been generated 
          if (!htmlOutput.value) {
            _context.next = 5;
            break;
          }
          if (!(navigator.clipboard && navigator.clipboard.writeText)) {
            _context.next = 3;
            break;
          }
          _context.next = 2;
          return navigator.clipboard.writeText(htmlOutput.value);
        case 2:
          _context.next = 4;
          break;
        case 3:
          // Fallback to the deprecated method
          range = document.createRange();
          range.selectNode(htmlOutputDiv);
          selection = window.getSelection();
          selection.removeAllRanges();
          selection.addRange(range);
          document.execCommand('copy');
          selection.removeAllRanges();
        case 4:
          copyIcon.innerHTML = "copied";
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('famCode_id').value = htmlOutput.value;
          _context.next = 6;
          break;
        case 5:
          copyIcon.innerHTML = "copy";
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('famCode_id').value = "";
          alert('Please generate the family code first');
        case 6:
          _context.next = 8;
          break;
        case 7:
          _context.prev = 7;
          _t = _context["catch"](1);
          console.error('Unable to copy the HTML output: ', _t);
        case 8:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[1, 7]]);
  }));
  return function (_x) {
    return _ref.apply(this, arguments);
  };
}());

/***/ }),

/***/ "./resources/asset/js/components/register/html/kids_Sibling.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/html/kids_Sibling.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtmlFamily: function() { return /* binding */ renderHtmlFamily; }
/* harmony export */ });
// register/html/kids_Sibling.js
var renderHtmlFamily = function renderHtmlFamily(family, no) {
  if (!no) return "";
  var kids_sib = family === "children" ? "children" : "sibling";
  var optionsHtml = "\n    <option value=\"select\">Choose</option>\n    <option value=\"With Spouse\">With Spouse</option>\n    <option value=\"Not With Spouse\">Not With Spouse</option>\n  ";
  if (family === "sibling") {
    optionsHtml = "\n      <option value=\"select\">Choose</option>\n      <option value=\"Same_Mother_Father\">Same Mother & Father</option>\n      <option value=\"Same_Father\">Only Same Father</option>\n      <option value=\"Same_Mother\">Only Same Mother</option>\n    ";
  }
  return "\n    <div class=\"field-body\">\n      <div class=\"field\">\n        <p class=\"control is-expanded\">\n          <span class=\"select is-normal is-success is-fullwidth\">\n            <select name=\"".concat(kids_sib, "_option").concat(no, "\" id=\"").concat(kids_sib, "_option").concat(no, "\">\n              ").concat(optionsHtml, "\n            </select>\n          </span>\n        </p>\n      </div>\n\n      <div class=\"field\">\n        <p class=\"control is-expanded\">\n          <input\n            type=\"text\"\n            placeholder=\"Enter ").concat(kids_sib, "'s full name - ").concat(no, "\"\n            name=\"").concat(kids_sib, "_name").concat(no, "\"\n            class=\"input is-normal\"\n            id=\"").concat(kids_sib, "_name").concat(no, "\"\n          >\n        </p>\n      </div>\n\n      <div class=\"field\">\n        <p class=\"control is-expanded has-icons-left\">\n          <input\n            type=\"email\"\n            placeholder=\"Enter ").concat(kids_sib, "'s email - ").concat(no, "\"\n            name=\"").concat(kids_sib, "_email").concat(no, "\"\n            class=\"input is-normal\"\n            id=\"").concat(kids_sib, "_email").concat(no, "\"\n          >\n          <span class=\"icon is-small is-left\">\n            <i class=\"fas fa-envelope\"></i>\n          </span>\n        </p>\n        <p class=\"help is-danger\" id=\"").concat(kids_sib, "_email").concat(no, "_help\"></p>\n      </div>\n    </div>\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/register/html/notification.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/html/notification.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   processFormDataAction: function() { return /* binding */ processFormDataAction; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");

var processFormDataAction = function processFormDataAction(addClass, serverResponse) {
  // display the success information for 10sec
  notificationDiv.style.display = "block"; // unblock the notification
  notificationDiv.classList.add(addClass); // add the success class
  notificationMsg.innerHTML = serverResponse.message; // error element
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('loader').classList.remove('loader'); // remove loader
  notificationDiv.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  });
};

/***/ }),

/***/ "./resources/asset/js/components/register/index.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/index.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _smallinput__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./smallinput */ "./resources/asset/js/components/register/smallinput.js");
/* harmony import */ var _event__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./event */ "./resources/asset/js/components/register/event.js");
/* harmony import */ var _onChangeKidSibling__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./onChangeKidSibling */ "./resources/asset/js/components/register/onChangeKidSibling.js");
/* harmony import */ var _processForm__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./processForm */ "./resources/asset/js/components/register/processForm.js");
/* harmony import */ var _mobileNameCheck__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./mobileNameCheck */ "./resources/asset/js/components/register/mobileNameCheck.js");
/* harmony import */ var _injectCountryCode__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./injectCountryCode */ "./resources/asset/js/components/register/injectCountryCode.js");
/* harmony import */ var _familyCode__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./familyCode */ "./resources/asset/js/components/register/familyCode.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/register/modal.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_modal__WEBPACK_IMPORTED_MODULE_7__);








var currentPs = document.getElementById("password_id");
var confirmPs = document.getElementById("confirm_password_id");
currentPs.setAttribute('autocomplete', 'new-password');
confirmPs.setAttribute('autocomplete', 'new-password');
// secretWd.setAttribute('autocomplete', 'on')

/***/ }),

/***/ "./resources/asset/js/components/register/injectCountryCode.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/injectCountryCode.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


// inject the country code once one of the country is picked

// Object to map countries to country codes

var countryCodes = {
  Afghanistan: "93",
  Albania: "355",
  Algeria: "213",
  Andorra: "376",
  Angola: "244",
  "Antigua and Barbuda": "1-268",
  Argentina: "54",
  Armenia: "374",
  Australia: "61",
  Austria: "43",
  Azerbaijan: "994",
  Bahamas: "1-242",
  Bahrain: "973",
  Bangladesh: "880",
  Barbados: "1-246",
  Belarus: "375",
  Belgium: "32",
  Belize: "501",
  Benin: "229",
  Bhutan: "975",
  Bolivia: "591",
  "Bosnia and Herzegovina": "387",
  Botswana: "267",
  Brazil: "55",
  Brunei: "673",
  Bulgaria: "359",
  "Burkina Faso": "226",
  Burundi: "257",
  Cambodia: "855",
  Cameroon: "237",
  Canada: "1",
  "Cape Verde": "238",
  "Central African Republic": "236",
  Chad: "235",
  Chile: "56",
  China: "86",
  Colombia: "57",
  Comoros: "269",
  "Congo (Brazzaville)": "242",
  "Congo (Kinshasa)": "243",
  "Costa Rica": "506",
  "Côte d'Ivoire": "225",
  Croatia: "385",
  Cuba: "53",
  Cyprus: "357",
  "Czech Republic": "420",
  Denmark: "45",
  Djibouti: "253",
  Dominica: "1-767",
  "Dominican Republic": "1-809, 1-829, 1-849",
  "East Timor": "670",
  Ecuador: "593",
  Egypt: "20",
  "El Salvador": "503",
  "Equatorial Guinea": "240",
  Eritrea: "291",
  Estonia: "372",
  Ethiopia: "251",
  Fiji: "679",
  Finland: "358",
  France: "33",
  Gabon: "241",
  Gambia: "220",
  Georgia: "995",
  Germany: "49",
  Ghana: "233",
  Greece: "30",
  Grenada: "1-473",
  Guatemala: "502",
  Guinea: "224",
  "Guinea-Bissau": "245",
  Guyana: "592",
  Haiti: "509",
  Honduras: "504",
  Hungary: "36",
  Iceland: "354",
  India: "91",
  Indonesia: "62",
  Iran: "98",
  Iraq: "964",
  Ireland: "353",
  Israel: "972",
  Italy: "39",
  Jamaica: "1-876",
  Japan: "81",
  Jordan: "962",
  Kazakhstan: "7",
  Kenya: "254",
  Kiribati: "686",
  "North Korea": "850",
  "South Korea": "82",
  Kosovo: "383",
  Kuwait: "965",
  Kyrgyzstan: "996",
  Laos: "856",
  Latvia: "371",
  Lebanon: "961",
  Lesotho: "266",
  Liberia: "231",
  Libya: "218",
  Liechtenstein: "423",
  Lithuania: "370",
  Luxembourg: "352",
  Macedonia: "389",
  Madagascar: "261",
  Malawi: "265",
  Malaysia: "60",
  Maldives: "960",
  Mali: "223",
  Malta: "356",
  "Marshall Islands": "692",
  Mauritania: "222",
  Mauritius: "230",
  Mexico: "52",
  Micronesia: "691",
  Moldova: "373",
  Monaco: "377",
  Mongolia: "976",
  Montenegro: "382",
  Morocco: "212",
  Mozambique: "258",
  Myanmar: "95",
  Namibia: "264",
  Nauru: "674",
  Nepal: "977",
  Netherlands: "31",
  "New Zealand": "64",
  Nicaragua: "505",
  Niger: "227",
  Nigeria: "234",
  Norway: "47",
  Oman: "968",
  Pakistan: "92",
  Palau: "680",
  Panama: "507",
  "Papua New Guinea": "675",
  Paraguay: "595",
  Peru: "51",
  Philippines: "63",
  Poland: "48",
  Portugal: "351",
  Qatar: "974",
  Romania: "40",
  Russia: "7",
  Rwanda: "250",
  "Saint Kitts and Nevis": "1-869",
  "Saint Lucia": "1-758",
  "Saint Vincent and the Grenadines": "1-784",
  Samoa: "685",
  "San Marino": "378",
  "Sao Tome and Principe": "239",
  "Saudi Arabia": "966",
  Senegal: "221",
  Serbia: "381",
  Seychelles: "248",
  "Sierra Leone": "232",
  Singapore: "65",
  Slovakia: "421",
  Slovenia: "386",
  "Solomon Islands": "677",
  Somalia: "252",
  "South Africa": "27",
  "South Sudan": "211",
  Spain: "34",
  "Sri Lanka": "94",
  Sudan: "249",
  Suriname: "597",
  Swaziland: "268",
  Sweden: "46",
  Switzerland: "41",
  Syria: "963",
  Taiwan: "886",
  Tajikistan: "992",
  Tanzania: "255",
  Thailand: "66",
  Togo: "228",
  Tonga: "676",
  "Trinidad and Tobago": "1-868",
  Tunisia: "216",
  Turkey: "90",
  Turkmenistan: "993",
  Tuvalu: "688",
  Uganda: "256",
  Ukraine: "380",
  "United Arab Emirates": "971",
  "United Kingdom": "44",
  "United States": "1",
  Uruguay: "598",
  Uzbekistan: "998",
  Vanuatu: "678",
  "Vatican City": "379",
  Venezuela: "58",
  Vietnam: "84",
  Yemen: "967",
  Zambia: "260",
  Zimbabwe: "263"
};
var injectCountryCode = function injectCountryCode() {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('country_id').addEventListener('change', function (e) {
    var value = e.target.value;
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('mobile_id').value = countryCodes[value] || '';
  });
};
injectCountryCode();

/***/ }),

/***/ "./resources/asset/js/components/register/mobileNameCheck.js":
/*!*******************************************************************!*\
  !*** ./resources/asset/js/components/register/mobileNameCheck.js ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../api/index */ "./resources/asset/js/components/api/index.js");
/* harmony import */ var _helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../helper/autocomplete */ "./resources/asset/js/components/helper/autocomplete.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");








var getData = (0,_api_index__WEBPACK_IMPORTED_MODULE_2__.getAllData)();
var firstNameData = [];
var fatherName = [];
var mobile = [];
var motherName = [];
var checkEmail = [];
var fName = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("firstName").value;
var lName = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("lastName").value;

/**
 *
 * @param {* array} baseArray the array to check against ["Banana", "Orange", "Apple", "Mango"];
 * @param {* string || integer} searchElement  the element to search against ("Mango")
 */

var checkExistence = function checkExistence(baseArray, searchElement) {
  if (!Array.isArray(baseArray)) {
    baseArray = []; // Initialize as an array if it's not already
  }
  if (baseArray.includes(searchElement) === false) {
    baseArray.push(searchElement);
  }
};
// Check if getData is resolved
if (getData instanceof Promise) {
  getData.then(function (el) {
    el.forEach(function (element) {
      checkExistence(firstNameData, element.firstName);
      checkExistence(fatherName, element.fatherName);
      checkExistence(motherName, element.motherName);
      checkExistence(mobile, element.mobile);
      checkExistence(checkEmail, element.email);
    });

    // Code that relies on the data obtained from getAllData() can be placed here
  }).catch(function (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    // Handle the error appropriately
  });
} else {
  console.log('getData is empty or not resolved');
  // Handle the case when getData is empty or not resolved
}
var firstAutoComplete = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("firstName");
var fatherAutoComplete = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("fatherName");
var motherAutoComplete = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("motherName");
firstAutoComplete.setAttribute("autocomplete", "off");
fatherAutoComplete.setAttribute("autocomplete", "off");
motherAutoComplete.setAttribute("autocomplete", "off");

// AUTOCOMPLETE
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__.autocomplete)(firstAutoComplete, firstNameData);
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__.autocomplete)(fatherAutoComplete, fatherName);
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__.autocomplete)(motherAutoComplete, motherName);

// CHECK THE MOBILE OF MOTHER AND FATHER

var setInput = function setInput(name, value) {
  var sex = name === "father" ? "him" : "her";
  var genId = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Mobile_error"));
  genId.style.display = "block";
  genId.innerHTML = mobile.includes(value) || email.includes(value) ? "Great news that your ".concat(name, " is already on the platform") : "<h4><i>Your ".concat(name, " is not on the platform. Do you want us to send ").concat(sex, " a text/email to register to the platform</i>?</h4>").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.checkBox)(name));
  function processRadio() {
    var postObj = {
      mobile: value,
      viewPath: "msg/contactNewMember",
      data: {
        email: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Email")).value,
        mobile: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Mobile")).value,
        name: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Name")).value,
        familyCode: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("familyCode").value,
        yourName: "".concat(fName, " ").concat(lName)
      },
      subject: "".concat(fName, " ").concat(lName, " Wants You: Experience the Magic of your Family Network Today!")
    };
    axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/register/contactNewMember", postObj).then(function (response) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.showNotification)("".concat(name, "Mobile_help"), 'is-success', response.data.message);
      genId.innerHTML = "";
    }).catch(function (error) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.showNotification)("".concat(name, "Mobile_error"), 'is-danger', error.message);
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    });
  }
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Yes")).addEventListener("click", processRadio);
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "No")).addEventListener("click", function () {
    return genId.style.display = "none";
  });
};

// *
// Handle mobile filtering
// for different individuals.*@param { Object }
// event - The event object.*@param { string }
// name - The name of the individual("father", "mother", "spouse").*/

var mobileFilter = function mobileFilter(event, name) {
  try {
    var value = event.target.value;
    if (!value) {
      throw new Error("Number value is empty");
    }
    if (value.length >= 11) {
      setInput(name, value);
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    // Perform additional error handling or logging if needed
  }
};
var fatherMobile = function fatherMobile(event) {
  var setName = "father";
  mobileFilter(event, setName);
};
var motherMobile = function motherMobile(event) {
  var setName = "mother";
  mobileFilter(event, setName);
};
var spouseMobile = function spouseMobile(event) {
  var setName = "spouse";
  mobileFilter(event, setName);
};

// Add event listeners with error handling
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("fatherMobile").addEventListener("keyup", function (event) {
  try {
    fatherMobile(event);
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("motherMobile").addEventListener("keyup", function (event) {
  try {
    motherMobile(event);
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("spouseMobile").addEventListener("keyup", function (event) {
  try {
    spouseMobile(event);
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});

// create the data for the function below

// check if there is a sibling or kids by email
(0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_5__.processKidsSiblings)(checkEmail, fName);
var checkPersonalEmail = function checkPersonalEmail(e) {
  var email = e.target.value;
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("email_error").innerHTML = checkEmail.includes(email) ? "YOU HAVE ALREADY REGISTERED ON THE PLATFORM" : "";
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('email').addEventListener('keyup', checkPersonalEmail);

/***/ }),

/***/ "./resources/asset/js/components/register/modal.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/modal.js ***!
  \*********************************************************/
/***/ (function() {

document.addEventListener('DOMContentLoaded', function () {
  // Functions to open and close a modal
  function openModal($el) {
    $el.classList.add('is-active');
  }
  function closeModal($el) {
    $el.classList.remove('is-active');
  }
  function closeAllModals() {
    (document.querySelectorAll('.modal') || []).forEach(function ($modal) {
      closeModal($modal);
    });
  }

  // Add a click event on buttons to open a specific modal
  (document.querySelectorAll('.js-modal-trigger') || []).forEach(function ($trigger) {
    var modal = $trigger.dataset.target;
    var $target = document.getElementById(modal);
    $trigger.addEventListener('click', function () {
      openModal($target);
    });
  });

  // Add a click event on various child elements to close the parent modal
  (document.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete, .modal-card-foot .button') || []).forEach(function ($close) {
    var $target = $close.closest('.modal');
    $close.addEventListener('click', function () {
      closeModal($target);
    });
  });

  // Add a keyboard event to close all modals
  document.addEventListener('keydown', function (event) {
    if (event.code === 'Escape') {
      closeAllModals();
    }
  });
});

/***/ }),

/***/ "./resources/asset/js/components/register/onChangeKidSibling.js":
/*!**********************************************************************!*\
  !*** ./resources/asset/js/components/register/onChangeKidSibling.js ***!
  \**********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   show: function() { return /* binding */ show; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _html_kids_Sibling__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html/kids_Sibling */ "./resources/asset/js/components/register/html/kids_Sibling.js");
/* harmony import */ var _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../data/checkEmailObj */ "./resources/asset/js/data/checkEmailObj.js");
/* harmony import */ var _data_checkEmailFactory__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../data/checkEmailFactory */ "./resources/asset/js/data/checkEmailFactory.js");


// import { getEnvironmentVariable as env} from 'environment-variable-reader'






/**
 * 
 * @param kids_or_sib Think of it like this:

Dropdown change → decides how many fields exist

onChangeKidSibling.js → owns the counts

checkEmailObj → shared live map of valid IDs

processKidsSiblings → reacts to typing, doesn’t care about counts

That separation is exactly what you want in a growing app.
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
      helpEl.innerHTML = value > 1 ? "Please, enter their names and emails below" : "Please, enter the name and email below";
      helpEl.style.fontSize = "1rem";
      helpEl.style.color = "#fc2003";
    }
    var container = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(containerId);
    if (!container) return;
    for (var i = 0; i < value; i++) {
      var no = i + 1;
      var html = (0,_html_kids_Sibling__WEBPACK_IMPORTED_MODULE_2__.renderHtmlFamily)(kids_or_sib, no);
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

/***/ "./resources/asset/js/components/register/processForm.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/register/processForm.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _dataToCheck__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../dataToCheck */ "./resources/asset/js/components/dataToCheck.js");
/* harmony import */ var _html_notification__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./html/notification */ "./resources/asset/js/components/register/html/notification.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");









var formInput = document.querySelectorAll('.register');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_2__["default"](formInputArr);
(function () {
  try {
    formData.clearError();
    // set the maxlength, check the length of the value, raise error
    formData.realTimeCheckLen(_dataToCheck__WEBPACK_IMPORTED_MODULE_4__.dataToCheckRegister.maxLength.id, _dataToCheck__WEBPACK_IMPORTED_MODULE_4__.dataToCheckRegister.maxLength.max);
    // check if password matches real time
    formData.matchInput(_dataToCheck__WEBPACK_IMPORTED_MODULE_4__.dataToCheckRegister.password.pwd, _dataToCheck__WEBPACK_IMPORTED_MODULE_4__.dataToCheckRegister.password.pwd2);

    // formData.duplicate('firstName_id', 'alias_id')
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(error);
  }
})();
var notificationDiv = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('register_notify_div');
var notificationMsg = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('register_notify_div_msg');
var processFormData = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(url, formElement, token) {
    var form, formEntries, options;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          form = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(formElement);
          formEntries = new FormData(form);
          formEntries.delete('submit');
          formEntries.delete('checkbox_id');
          options = {
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN'
          };
          _context.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_6__["default"].post(url, formEntries, options).then(function (response) {
            // get the api message and output it to the form
            (0,_html_notification__WEBPACK_IMPORTED_MODULE_5__.processFormDataAction)('is-success', response.data);
            setTimeout(function () {
              //window.location.replace(redirect)
              window.location.replace('register/nextStep');
            }, 5000);

            // it clears all the contents
            //  formData.clearHtml();
          }).catch(function (error) {
            (0,_html_notification__WEBPACK_IMPORTED_MODULE_5__.processFormDataAction)('is-danger', error.response.data);
          });
        case 1:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return function processFormData(_x, _x2, _x3) {
    return _ref.apply(this, arguments);
  };
}();
window.processForm = /*#__PURE__*/function () {
  var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(token) {
    var emailError, errorData, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          // e.preventDefault();
          notificationDiv.classList.remove('is-danger'); // remove the danger class from the notification
          notificationMsg.innerHTML = ""; // empty the error element
          if (!(0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('checkbox').checked) {
            _context2.next = 4;
            break;
          }
          // window.location.hash = '#setLoader';
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("setLoader").focus(); // focus on the loader element

          formData.clearError();
          formData.emailVal();
          formData.massValidate();

          // CHECK IF EMAIL HAS NOT BEEN REGISTERED ERROR IS NULL
          emailError = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("email_error").innerHTML;
          if (!(formData.error.length <= 0 && emailError == "")) {
            _context2.next = 2;
            break;
          }
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("setLoader").classList.add('loader');
          _context2.next = 1;
          return processFormData("/register", 'register');
        case 1:
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("setLoader").classList.remove('loader');
          return _context2.abrupt("return");
        case 2:
          alert("The form cannot be submitted. Please check the errors");
          notificationMsg.innerHTML = "".concat(_global__WEBPACK_IMPORTED_MODULE_3__.warningSign, " Check the errors");
          notificationDiv.style.display = "block";
          notificationDiv.style.backgroundColor = "Red";
          notificationDiv.style.color = "White";
          //notificationMsg.scrollIntoView({ behavior: 'smooth', block: 'start' });
          notificationDiv.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
          //By using the js scrollIntoView method on the error element, the browser will automatically scroll to make the error message the focus point after the form is submitted.
        case 3:
          _context2.next = 5;
          break;
        case 4:
          alert('To continue, you need to agree to the our privacy policy');
          errorData = "To continue, you need to agree to the our privacy policy";
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showNotification)('checkbox_error', 'is-danger', errorData);
        case 5:
          _context2.next = 7;
          break;
        case 6:
          _context2.prev = 6;
          _t = _context2["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
        case 7:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 6]]);
  }));
  return function (_x4) {
    return _ref2.apply(this, arguments);
  };
}();

// id('submit').addEventListener('click', processForm)
// id('submit').addEventListener('click', processForm)

/***/ }),

/***/ "./resources/asset/js/components/register/smallinput.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/smallinput.js ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


/**
 * 
 * @param {*} Id - id of the element string
 * @param {*} msg - messages to pass - string
 */
var showMsg = function showMsg(Id) {
  var msg = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "Please, leave blank if not available";
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(Id).innerHTML = msg;
};

// const href = "<a href='/createFamilyCode' target='_blank'>here</a>"
var famCodeButton = "<button type='button' class='js-modal-trigger' data-target='modal-familyCode' id='triggerFamCode'>here</button>";
showMsg('fatherMobile_help');
showMsg('motherMobile_help');
showMsg('motherEmail_help');
showMsg('fatherEmail_help');
showMsg('mobile_help', "Nigeria: 2348036517179, UK: 447871717809");
showMsg('password_help', 'Must be 8-20 characters long.');
showMsg("famCode_help", "Ask a family member who already registered for the code if none then create one for your family ".concat(famCodeButton));

// const lastName = id('lastName_id').value = "OLAOGUN"

// TRIGGER THE MODAL

// Functions to open and close a modal
function openModal($el) {
  $el.classList.add('is-active');
}
function closeModal($el) {
  $el.classList.remove('is-active');
}
var trigger = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('triggerFamCode');
var close = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('modal-close-code');
var modalClass = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('modal-familyCode');
trigger.addEventListener('click', function () {
  openModal(modalClass);
});
close.addEventListener('click', function () {
  closeModal(modalClass);
});

/***/ }),

/***/ "./resources/asset/js/data/checkEmailFactory.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/data/checkEmailFactory.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
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

"use strict";
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
//# sourceMappingURL=register.js.map