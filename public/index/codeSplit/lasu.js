(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/lasu"],{

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
            // console.log(post)
            // rid it off the submit and token
            if (post.name == 'submit' || post.name == 'button' || post.name == 'token' || post.name == 'cancel' || post.name == "checkbox_id") {
              continue;
            }

            // check if there is no value

            var postName = post.name.replace('_', ' ');
            if (postName == "spouseName" || postName == "spouseMobile" || postName == "spouseEmail" || postName == "fatherMobile" || postName == "fatherEmail" || postName == "motherMobile" || postName == "maidenName" || postName == "motherEmail") {
              if (post.value === "") {
                post.value = "Not Provided";
              }
            }
            if (post.value === '' || post.value === 'select') {
              errMsg.innerHTML = "*cannot be left empty";
              errMsg.style.color = "red";
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
            if (['submit', 'token', 'checkbox'].includes(id) || ['token', 'submit'].includes(name)) {
              return 1; // continue
            }
            // Add change event listener to clear error message
            _this2.id(id).addEventListener('change', function () {
              clearErrorForElement(name);
            });
            // Add keyup event listener for non-select inputs
            if (value !== 'select') {
              _this2.id(id).addEventListener('keyup', function () {
                clearErrorForElement(name);
              });
            } else {
              _this2.id(id).addEventListener('keyup', function () {
                clearErrorForElement(name);
              });
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

/***/ "./resources/asset/js/components/dataToCheck.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/dataToCheck.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Login: () => (/* binding */ Login),
/* harmony export */   dataToCheckRegister: () => (/* binding */ dataToCheckRegister)
/* harmony export */ });


var dataToCheckRegister = {
  maxLength: {
    id: ['firstName', 'lastName', 'alias', 'spouseName', 'spouseMobile', 'motherMobile', 'fatherMobile', 'fatherName', 'motherName', 'country', 'mobile', 'email', 'occupation'],
    max: [15, 15, 15, 15, 12, 12, 12, 30, 30, 15, 13, 45, 20]
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

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
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

/***/ "./resources/asset/js/components/helper/http.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/helper/http.js ***!
  \******************************************************/
/***/ (() => {

throw new Error("Module build failed (from ./node_modules/babel-loader/lib/index.js):\nSyntaxError: /Users/olawaleolaogun/Sites/familyPlatform/resources/asset/js/components/helper/http.js: Unexpected token, expected \"(\" (59:8)\n\n\u001b[0m \u001b[90m 57 |\u001b[39m         \u001b[36mif\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 58 |\u001b[39m\u001b[0m\n\u001b[0m\u001b[31m\u001b[1m>\u001b[22m\u001b[39m\u001b[90m 59 |\u001b[39m         sessionStorage\u001b[33m.\u001b[39msetItem(\u001b[32m'idSetFromHttp'\u001b[39m\u001b[33m,\u001b[39m idSetFromHttp)\u001b[33m;\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m    |\u001b[39m         \u001b[31m\u001b[1m^\u001b[22m\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 60 |\u001b[39m         sessionStorage\u001b[33m.\u001b[39msetItem(\u001b[32m'famCodeSetFromHttp'\u001b[39m\u001b[33m,\u001b[39m famCodeSetFromHttp)\u001b[33m;\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 61 |\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 62 |\u001b[39m         processFormDataAction(successClass\u001b[33m,\u001b[39m dbHttpResult\u001b[33m,\u001b[39m notificationId)\u001b[33m;\u001b[39m\u001b[0m\n    at constructor (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:345:19)\n    at Parser.raise (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:3199:19)\n    at Parser.unexpected (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:3229:16)\n    at Parser.expect (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:3566:28)\n    at Parser.parseHeaderExpression (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12823:10)\n    at Parser.parseIfStatement (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12930:22)\n    at Parser.parseStatementContent (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12561:21)\n    at Parser.parseStatementLike (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12530:17)\n    at Parser.parseStatementListItem (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12510:17)\n    at Parser.parseBlockOrModuleBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13131:61)\n    at Parser.parseBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13124:10)\n    at Parser.parseBlock (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13112:10)\n    at Parser.parseTryStatement (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13015:23)\n    at Parser.parseStatementContent (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12569:21)\n    at Parser.parseStatementLike (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12530:17)\n    at Parser.parseStatementListItem (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12510:17)\n    at Parser.parseBlockOrModuleBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13131:61)\n    at Parser.parseBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13124:10)\n    at Parser.parseBlock (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13112:10)\n    at Parser.parseFunctionBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:11893:24)\n    at Parser.parseArrowExpression (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:11868:10)\n    at Parser.parseAsyncArrowFromCallExpression (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:11045:10)\n    at Parser.parseCoverCallAndAsyncArrowHead (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10951:27)\n    at Parser.parseSubscript (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10878:19)\n    at Parser.parseSubscripts (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10849:19)\n    at Parser.parseExprSubscripts (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10840:17)\n    at Parser.parseUpdate (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10819:21)\n    at Parser.parseMaybeUnary (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10795:23)\n    at Parser.parseMaybeUnaryOrPrivate (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10633:61)\n    at Parser.parseExprOps (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10638:23)\n    at Parser.parseMaybeConditional (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10615:23)\n    at Parser.parseMaybeAssign (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10576:21)\n    at /Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10546:39\n    at Parser.allowInAnd (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12221:16)\n    at Parser.parseMaybeAssignAllowIn (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10546:17)\n    at Parser.parseVar (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13201:91)\n    at Parser.parseVarStatement (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13042:10)\n    at Parser.parseStatementContent (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12625:23)\n    at Parser.parseStatementLike (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12530:17)\n    at Parser.parseStatementListItem (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12510:17)\n    at Parser.parseExportDeclaration (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13777:17)\n    at Parser.maybeParseExportDeclaration (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13732:31)\n    at Parser.parseExport (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13655:29)\n    at Parser.parseStatementContent (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12657:27)\n    at Parser.parseStatementLike (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12530:17)\n    at Parser.parseModuleItem (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12507:17)\n    at Parser.parseBlockOrModuleBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13131:36)\n    at Parser.parseBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13124:10)\n    at Parser.parseProgram (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12406:10)\n    at Parser.parseTopLevel (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12396:25)");

/***/ }),

/***/ "./resources/asset/js/components/helper/security.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/helper/security.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   emailVal: () => (/* binding */ emailVal),
/* harmony export */   showPassword: () => (/* binding */ showPassword)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");

var showPassword = function showPassword(inputId) {
  var y = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputId);
  if (y.type === "password") {
    y.type = "text";
  } else {
    y.type = "password";
  }
};

/**
 * 
 * @param {* } email 
 * @returns 1 if there is an error
 */
var emailVal = function emailVal(email) {
  var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
  var error;
  var msg = "<li style=color:'red';> Please enter a valid email</li>";
  if (email.match(emailExp) === null) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('email_error').innerHTML = msg;
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('email_error').style.color = "red";
    error = 1;
    return error;
  }
};

/***/ }),

/***/ "./resources/asset/js/components/login/Login.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/login/Login.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _dataToCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../dataToCheck */ "./resources/asset/js/components/dataToCheck.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");
/* harmony import */ var _helper_security__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../helper/security */ "./resources/asset/js/components/helper/security.js");








// block the setLoader div

(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("setLoader").style.display = "none";
var formInput = document.querySelectorAll('.loginNow');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_0__["default"](formInputArr);
(function () {
  //clear error from the form
  formData.clearError();

  // set the maxlength, check the length of the value, raise error
  formData.realTimeCheckLen(_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.Login.maxLength.id, _dataToCheck__WEBPACK_IMPORTED_MODULE_2__.Login.maxLength.max);
})();
var LoginSubmission = function LoginSubmission(e) {
  e.preventDefault();

  // Clear any previous error messages
  formData.clearError();
  try {
    // Sanitize email
    formData.emailVal();

    // Validate and sanitize data
    formData.massValidate();
    if (formData.error.length === 0) {
      // the notification div and the content

      (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('error').innerHTML = "";
      var loginNotification = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('loginNow_notification');
      if (loginNotification.classList.contains('is-danger')) {
        loginNotification.classList.remove('is-danger');
      }
      // Display the success information for 10 seconds
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('setLoader').style.display = "block";

      // Set the redirect URL in localStorage
      localStorage.setItem('redirect', '/member/ProfilePage');

      // Get the login URL from sessionStorage
      var loginURL = sessionStorage.getItem('loginURL1');

      // Determine the redirect URL based on loginURL
      var redirect = loginURL === "/lasu" ? "/admin/reviewApps" : "/login/code";

      // Submit the form data
      (0,_helper_http__WEBPACK_IMPORTED_MODULE_3__.postFormData)(loginURL, "loginNow", redirect);
    } else {
      // Display an alert for form errors
      alert('The form cannot be submitted. Please check the errors');
    }
  } catch (err) {
    // Handle any unexpected errors
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  }
};
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('submit').addEventListener('click', LoginSubmission);
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("showPassword_id").addEventListener('click', function () {
  return (0,_helper_security__WEBPACK_IMPORTED_MODULE_4__.showPassword)('password_id');
});

/***/ }),

/***/ "./resources/asset/js/components/login/index.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/login/index.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Login__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Login */ "./resources/asset/js/components/login/Login.js");

var currentPs = document.getElementById("password_id");
var passwordLabel = document.getElementById("showPassword_id");
currentPs.setAttribute('autocomplete', 'current-password');
passwordLabel.setAttribute('aria-label', 'Warning: this will display your password on the screen.');

/***/ })

}]);