(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["register"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");




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
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.showError)(_t);
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
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.showError)(_t2);
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
  return axios__WEBPACK_IMPORTED_MODULE_3__["default"].get("".concat(URL, "getEmails")).then(function (response) {
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
    id: ['firstName', 'lastName', 'spouse_name', 'spouse_mobile', 'mother_mobile', 'father_mobile', 'father_name', 'mother_name', 'country', 'mobile', 'email', 'occupation'],
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

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
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
/* harmony export */   showSpouse: function() { return /* binding */ showSpouse; }
/* harmony export */ });
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");

var showMaidenName = function showMaidenName() {
  var gender = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('gender');
  var maritalStatus = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus');
  if (gender && maritalStatus && gender.value === "Female" && maritalStatus.value === "Yes") {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showElement)('maidenName_div');
  } else {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.hideElement)('maidenName_div');
  }
};
var showSpouse = function showSpouse() {
  var maritalStatus = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus');
  if (maritalStatus && maritalStatus.value === "Married") {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showElement)('spouse');
  } else {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.hideElement)('spouse');
  }
};

// Add event listeners
var maritalStatusEl = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus');
if (maritalStatusEl) {
  maritalStatusEl.addEventListener('change', showSpouse);
}
var genderEl = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('gender');
if (genderEl) {
  genderEl.addEventListener('change', showMaidenName);
}

// Hide elements by default
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.hideElement)('spouse');
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.hideElement)('maidenName_div');

// Other initializations
var registerNotifyDiv = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('register_notify_div');
if (registerNotifyDiv) registerNotifyDiv.style.display = "none";

// inject student after employment status value is student 

var injectStudent = function injectStudent() {
  var empStatus = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('employmentStatus');
  if (empStatus) {
    empStatus.addEventListener('change', function (e) {
      var value = e.target.value;
      if (value === "Student") {
        var occ = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('occupation');
        if (occ) occ.value = 'Student';
      }
    });
  }
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
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");



var btnFamCode = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)("btnFamCode");
btnFamCode.addEventListener("click", function () {
  console.log("Generate Family Code clicked");
  try {
    var surnameEl = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('surname');
    if (surnameEl && surnameEl.value !== "") {
      var uniqueNumber = Date.now();
      var uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber) / 10000000);
      var getSurname = surnameEl.value;
      var firstFourLetters = getSurname.substring(0, 4);
      (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('createCode').value = "".concat(firstFourLetters.toUpperCase()).concat(uniqueNumber1);
      btnFamCode.disabled = true;
      btnFamCode.innerText = "Generated";
    } else {
      alert("Please enter a surname first");
    }
  } catch (error) {
    console.error("Family Code Generation Error:", error);
    var errEl = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)("surname_error");
    if (errEl) errEl.innerHTML = error.message || "An error occurred";
  }
});

// Get references to the HTML output and the copy icon

var copyIcon = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('copyIcon');
var htmlOutputDiv = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('createFamCode');
var htmlOutput = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('createCode');
copyIcon.addEventListener('click', /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
    var range, selection, targetInput, _targetInput, _t;
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
          targetInput = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('famCode');
          if (targetInput) {
            targetInput.value = htmlOutput.value;
            console.log("Family code copied to form:", htmlOutput.value);
          } else {
            console.error("Target input 'famCode' not found");
          }
          _context.next = 6;
          break;
        case 5:
          copyIcon.innerHTML = "copy";
          _targetInput = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('famCode');
          if (_targetInput) _targetInput.value = "";
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








var currentPs = document.getElementById("password");
var confirmPs = document.getElementById("confirm_password");
if (currentPs) currentPs.setAttribute('autocomplete', 'new-password');
if (confirmPs) confirmPs.setAttribute('autocomplete', 'new-password');

/***/ }),

/***/ "./resources/asset/js/components/register/injectCountryCode.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/injectCountryCode.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");


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
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('country').addEventListener('change', function (e) {
    var value = e.target.value;
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('mobile').value = countryCodes[value] || '';
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
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../api/index */ "./resources/asset/js/components/api/index.js");
/* harmony import */ var _helper_autocomplete__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/autocomplete */ "./resources/asset/js/components/helper/autocomplete.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");








var getData = (0,_api_index__WEBPACK_IMPORTED_MODULE_1__.getAllData)();
var firstNameData = [];
var fatherName = [];
var mobile = [];
var motherName = [];
var checkEmail = [];
var fName = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("firstName").value;
var lName = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("lastName").value;

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
    // ✅ destructure the array from el.message
    var message = el.message,
      status = el.status;
    if (status !== "success" || !Array.isArray(message)) {
      console.error("Unexpected response shape", el);
      return;
    }
    message.forEach(function (element) {
      checkExistence(firstNameData, element.firstName);
      checkExistence(fatherName, element.fatherName);
      checkExistence(motherName, element.motherName);
      checkExistence(mobile, element.mobile);
      checkExistence(checkEmail, element.email);
    });
  }).catch(function (error) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    // Handle the error appropriately
  });
} else {
  console.log('getData is empty or not resolved');
  // Handle the case when getData is empty or not resolved
}
var firstAutoComplete = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("firstName");
var fatherAutoComplete = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("father_name");
var motherAutoComplete = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("mother_name");
firstAutoComplete.setAttribute("autocomplete", "off");
fatherAutoComplete.setAttribute("autocomplete", "off");
motherAutoComplete.setAttribute("autocomplete", "off");

// AUTOCOMPLETE
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_2__.autocomplete)(firstAutoComplete, firstNameData);
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_2__.autocomplete)(fatherAutoComplete, fatherName);
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_2__.autocomplete)(motherAutoComplete, motherName);

// CHECK THE MOBILE OF MOTHER AND FATHER

var setInput = function setInput(name, value) {
  var sex = name === "father" ? "him" : "her";
  var genId = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Mobile_error"));
  genId.style.display = "block";
  genId.innerHTML = mobile.includes(value) || email.includes(value) ? "Great news that your ".concat(name, " is already on the platform") : "<h4><i>Your ".concat(name, " is not on the platform. Do you want us to send ").concat(sex, " a text/email to register to the platform</i>?</h4>").concat((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.checkBox)(name));
  function processRadio() {
    var postObj = {
      mobile: value,
      viewPath: "msg/contactNewMember",
      data: {
        email: (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Email")).value,
        mobile: (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Mobile")).value,
        name: (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Name")).value,
        familyCode: (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("familyCode").value,
        yourName: "".concat(fName, " ").concat(lName)
      },
      subject: "".concat(fName, " ").concat(lName, " Wants You: Experience the Magic of your Family Network Today!")
    };
    axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/register/contactNewMember", postObj).then(function (response) {
      (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showNotification)("".concat(name, "Mobile_help"), 'is-success', response.data.message);
      genId.innerHTML = "";
    }).catch(function (error) {
      (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showNotification)("".concat(name, "Mobile_error"), 'is-danger', error.message);
      (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    });
  }
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Yes")).addEventListener("click", processRadio);
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "No")).addEventListener("click", function () {
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
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
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
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("father_mobile").addEventListener("keyup", function (event) {
  try {
    fatherMobile(event);
  } catch (error) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("mother_mobile").addEventListener("keyup", function (event) {
  try {
    motherMobile(event);
  } catch (error) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("spouse_mobile").addEventListener("keyup", function (event) {
  try {
    spouseMobile(event);
  } catch (error) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});

// create the data for the function below

// check if there is a sibling or kids by email
(0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_3__.processKidsSiblings)(checkEmail, fName);
var checkPersonalEmail = function checkPersonalEmail(e) {
  var email = e.target.value;
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("email_error").innerHTML = checkEmail.includes(email) ? "YOU HAVE ALREADY REGISTERED ON THE PLATFORM" : "";
};
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('email').addEventListener('keyup', checkPersonalEmail);

/***/ }),

/***/ "./resources/asset/js/components/register/modal.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/modal.js ***!
  \*********************************************************/
/***/ (function() {

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
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _html_kids_Sibling__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./html/kids_Sibling */ "./resources/asset/js/components/register/html/kids_Sibling.js");
/* harmony import */ var _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../data/checkEmailObj */ "./resources/asset/js/data/checkEmailObj.js");
/* harmony import */ var _data_checkEmailFactory__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../data/checkEmailFactory */ "./resources/asset/js/data/checkEmailFactory.js");


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
      helpEl.innerHTML = value > 1 ? "Please, enter their names and emails below" : "Please, enter the name and email below";
      helpEl.style.fontSize = "1rem";
      helpEl.style.color = "#fc2003";
    }
    var container = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(containerId);
    if (!container) return;
    for (var i = 0; i < value; i++) {
      var no = i + 1;
      var html = (0,_html_kids_Sibling__WEBPACK_IMPORTED_MODULE_1__.renderHtmlFamily)(kids_or_sib, no);
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

/***/ "./resources/asset/js/components/register/processForm.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/register/processForm.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib_FormHelper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib/FormHelper */ "./node_modules/@modernman00/shared-js-lib/FormHelper.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _dataToCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../dataToCheck */ "./resources/asset/js/components/dataToCheck.js");





var formInput = document.querySelectorAll('.register');
var formInputArr = Array.from(formInput);
var formData = new _modernman00_shared_js_lib_FormHelper__WEBPACK_IMPORTED_MODULE_0__["default"](formInputArr);
(function () {
  try {
    formData.clearError();
    // set the maxlength, check the length of the value, raise error
    formData.realTimeCheckLen(_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.maxLength.id, _dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.maxLength.max);
    // check if password matches real time
    formData.matchInput(_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.password.pwd, _dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.password.pwd2);

    // formData.duplicate('firstName_id', 'alias_id')
  } catch (error) {
    console.log(error);
  }
})();
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.registerHandler)({
  formId: 'register',
  route: '/register',
  buttonId: 'submit',
  redirect: 'register/nextStep',
  recaptchaAction: 'SUBMIT',
  optionalFields: []
});

/***/ }),

/***/ "./resources/asset/js/components/register/smallinput.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/smallinput.js ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");


/**
 * 
 * @param {*} Id - id of the element string
 * @param {*} msg - messages to pass - string
 */
var showMsg = function showMsg(Id) {
  var msg = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "Please, leave blank if not available";
  var el = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(Id);
  if (el && msg) {
    el.innerHTML = msg;
  }
};

// const href = "<a href='/createFamilyCode' target='_blank'>here</a>"
var famCodeButton = "<button type='button' class='btn btn-sm btn-brand-outline ms-1 py-0' style='font-size: 0.8rem; vertical-align: baseline;' id='triggerFamCode'>here</button>";
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
var trigger = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('triggerFamCode');
var close = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('modal-close-code');
var modalClass = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('modal-familyCode');
if (trigger && modalClass) {
  trigger.addEventListener('click', function () {
    openModal(modalClass);
  });
}
if (close && modalClass) {
  close.addEventListener('click', function () {
    closeModal(modalClass);
  });
}

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