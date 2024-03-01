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

/***/ "./resources/asset/js/components/navbar.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/navbar.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToNotificationTab: () => (/* binding */ addToNotificationTab),
/* harmony export */   increaseNotificationCount: () => (/* binding */ increaseNotificationCount)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_3__);



// const timeAgo = (x) => format(x)

var postAgoNotification = function postAgoNotification(date) {
  return "\n  <div class=\"notification_timeago w3-left w3-opacity\" datetime='".concat(date, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "\n  </div>");
};
// this is the notification htnl 
var notificationHTML = function notificationHTML(data) {
  return "<a data-id=\"".concat(data.sender_id, "\" class=\"w3-bar-item w3-button notification_real_time linkRequestCard\">\n  \n\n  ").concat(postAgoNotification(data.created_at), " -\n  <b> ").concat(data.notification_type, "</b> -\n  ").concat(data.notification_name, " -\n  ").concat(data.notification_content, " -\n  ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(data.sender_name), "\n\n  \n  </a>");
};

// CLICK FUNCTION ON THE NOTIFICATION BAR THAT TAKES ONE TO THE FRIEND REQUEST CARD

var increaseNotificationCount = function increaseNotificationCount() {
  var currentNotificationCount = parseInt(sessionStorage.getItem('notificationCount')) + 1;
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = currentNotificationCount;
};
var addToNotificationTab = function addToNotificationTab(data) {
  return (0,_global__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab').insertAdjacentHTML('afterbegin', notificationHTML(data));
};

// GET THE ID SET FROM THE LOGIN FILE - HTTPS.JS

var idSetFromHttp = sessionStorage.getItem('idSetFromHttp');
var famCodeSetFromHttp = sessionStorage.getItem('famCodeSetFromHttp');
var notificationURL = "/member/notifications/id?notificationId=".concat(idSetFromHttp, "&famCode=").concat(famCodeSetFromHttp);

// const getData = axios.get(notificationURL);

axios__WEBPACK_IMPORTED_MODULE_3___default().get(notificationURL).then(function (res) {
  // Extract the notifications from the response
  var data = res.data.message;
  if (data) {
    if (data.length > 0) {
      // Display the count of notifications
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = data.length;

      // Store the notification count in session storage
      sessionStorage.setItem('notificationCount', data.length);

      // Display each notification
      data.forEach(function (element) {
        addToNotificationTab(element);
      });

      // Update the timing of notifications
      var updateNotificationTiming = document.querySelectorAll(".notification_timeago");
      (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.render)(updateNotificationTiming);
    } else {
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = 0;
    }
  }
})["catch"](function (error) {
  // Handle any errors that occur during the process
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
});

// ONCE THE NOTIFICATION BAR IS CLICKED, IT SHOULD TAKE YOU TO BE FRIEND REQUEST CARD

// Add a click event listener to elements with the "linkRequestCard" class
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('linkRequestCard')) {
    var friendRequestSection = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(e.target.getAttribute('data-id'), "_linkRequestCard"));
    if (friendRequestSection) {
      friendRequestSection.scrollIntoView({
        behavior: "smooth"
      });
    }
  }
});

///member/notifications

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
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");
/* harmony import */ var _post__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./post */ "./resources/asset/js/components/profilePage/post.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_5__);








try {
  (pusher_js__WEBPACK_IMPORTED_MODULE_5___default().logToConsole) = true;
  var pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_5___default())('d1f1e43f3d8afb028a1f', {
    cluster: 'eu'
  });

  // getApiData()

  var newLikeCounterVal = 0;
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };

  // let serverConnection = new EventSource("/post/getAllPost/update") 

  // const Pusher = (pushData) => {

  // // open the server sent event

  //     const update = (e) => {

  //         const data = JSON.parse(e.data)
  //         // log(data)

  //         if( appendNewPost(pushData)) {
  //             serverConnection.close()
  //         }

  //     }

  //     serverConnection.addEventListener("updatePost", update)

  // }

  var showTheComment = function showTheComment(commentResponse) {
    var idDiv = "showComment".concat(commentResponse.post_no);
    var commentHtml = (0,_comment__WEBPACK_IMPORTED_MODULE_2__.commentHTML)(commentResponse);
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idDiv).insertAdjacentHTML('afterbegin', commentHtml);
  };

  // CLICK EVENT get the comment and like button from the document
  document.onclick = function (e) {
    var elementId = e.target.id;
    var postId = e.target.name;
    if (elementId.includes("likeButton")) {
      // replace button with Counter to get the span id 
      var likeCounterId = elementId.replace('Button', 'Counter');
      var likeCounterVal = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(likeCounterId).innerHTML;

      // get the post like using the post id

      (0,_helper_http__WEBPACK_IMPORTED_MODULE_1__.getApiData)("/profileCard/getLikes?postId=".concat(postId, "&count=").concat(likeCounterVal));

      // add one to the result 
      newLikeCounterVal = parseInt(likeCounterVal) + 1;
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(likeCounterId).innerHTML = newLikeCounterVal;

      // Make the comment form to appear onclick. initcomment is the id of the comment button 
    } else if (elementId.includes("initComment")) {
      var commentFormId = elementId.replace('init', 'form');
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(commentFormId).style.display = "block";

      // Submit function for comment using POST API
    } else if (elementId.includes("submitComment")) {
      //elementId == submitComment511

      // 0.5 LISTEN FOR THE SUBMIT EVENT
      // 0.7 GET THE COMMENT FORM ID 
      // 1. POST SENDS BACK THE LAST COMMENT NO POSTED
      // 2.  USE IT TO GET THE NEW COMMENT
      // 3. ADD THE NEW COMMENT TO THE COMMENT DIV 
      // get the specific form id
      e.preventDefault();

      // 0.7
      var idForm = elementId.replace("submit", "form"); //idForm == formComment511

      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idForm).style.display = "none"; // make the comment form disappear

      // extract the form entries
      var form = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idForm);
      var formEntries = new FormData(form);

      // if the comment form input is empty. Get the input id and check 
      var inputComment = idForm.replace("form", "input");
      var idInputComment = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputComment);
      if (idInputComment.value == null || idInputComment.value == "") {
        alert("Please enter a comment before submitting");
      } else {
        // 1.
        axios__WEBPACK_IMPORTED_MODULE_4___default().post('/postCommentProfile', formEntries, options).then(function (response) {
          // 2. note. message returns the new post_no from the database

          axios__WEBPACK_IMPORTED_MODULE_4___default().get("/member/pp/comment/byNumber?commentNo=".concat(response.data.message)).then(function (res) {
            // 3.

            showTheComment(res.data.message);
          });
        })["catch"](function (error) {
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(error);
        });
      }
      // SUBMIT THE POST
    } else if (elementId.includes("submitPost")) {
      // LISTEN TO THE SUBMIT EVENT 
      // 2. GET THE FORM id
      // 3. POST TO THE SERVER USING AXIOS POST
      //4. GET THE POST FROM THE SERVER USING AXIOS GET 
      //5. APPEND THE NEW POST TO THE POSTCARD 

      // 2. 
      var formExtra = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formPostMessageModal');
      var formData = new FormData(formExtra);

      // 3. 
      axios__WEBPACK_IMPORTED_MODULE_4___default().post("/member/profilePage/post", formData, options).then(function (response) {
        //  4. 
        axios__WEBPACK_IMPORTED_MODULE_4___default().get("/post/getAllPost/byNumber?postNo=".concat(response.data.message)).then(function (res) {
          // 5. 

          //  log(res.data)
          (0,_post__WEBPACK_IMPORTED_MODULE_3__.appendNewPost)(res.data.message);

          // Pusher(res.data.message)
        });
        // Enable pusher logging - don't include this in production

        var channel = pusher.subscribe('my-channel');
        channel.bind('updatePost', function (data) {
          // log("checking1")
          // log(data.message);
          // log("checking")
        });
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'none';
      });
    }
  };
} catch (e) {
  showError(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/comment.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/comment.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   commentHTML: () => (/* binding */ commentHTML),
/* harmony export */   showComment: () => (/* binding */ showComment)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");

var commentHTML = function commentHTML(data) {
  var imgURL = data.img ? data.img : data.profileImg;
  var img = imgURL ? "/img/profile/".concat(imgURL) : "/avatar/avatarF.png";
  return "<div class='w3-ul w3-border w3-round' id='comment".concat(data.comment_no, "' name='commentDiv'>\n            <div class='w3-container commentDiv'>\n              <img src='").concat(img, "' alt='Avatar' class='w3-left w3-circle w3-margin-right commentImg' style='width:50px; height:50px'>\n              <p class='w3-right w3-opacity commentTiming' datetime='").concat(data.date_created, "' title='").concat(data.date_created, "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(data.date_created), " </p> \n              <p class='commentFont'> ").concat(data.comment, "</p>\n            </div>\n          </div>");
};
var showComment = function showComment(comment) {
  if (!comment) {
    return "<div class=\"w3-ul w3-border\" id=\"comment\" name=\"commentDiv\"></div>";
  } // only run if there is comment

  var commentHTMLArray = comment.map(function (commentElement) {
    return commentHTML(commentElement);
  });
  return commentHTMLArray.join(''); // Join the array elements into a single string
};

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
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_4__);







var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_1__["default"](formInputArr);
var displayNone = function displayNone() {
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'none';
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('cancelModal').addEventListener('click', displayNone);
var checkEventAndAdd = function checkEventAndAdd(data) {
  var appendEvent = (0,_eventHTML__WEBPACK_IMPORTED_MODULE_3__.eventHtml)(data);
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventList').insertAdjacentHTML('afterbegin', appendEvent);
};
var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};
var process = function process(e) {
  try {
    e.preventDefault();
    // id('eventModalForm_notification').classList.remove('w3-red') // remove the danger class from the notification - may not be needed
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = ""; // may not be needed
    formData.massValidate();
    // log(formData.error)
    if (formData.error.length <= 0) {
      // get the form data
      var eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventModalForm');
      var eventFormEntries = new FormData(eventForm);

      // post the form data to the database and get the last posted event no

      axios__WEBPACK_IMPORTED_MODULE_4___default().post("/member/profilePage/event", eventFormEntries, options).then(function (response) {
        // use the event no to get the last event from the database

        axios__WEBPACK_IMPORTED_MODULE_4___default().get("/member/getEventDataByNo?eventNo=".concat(response.data.message)).then(function (res) {
          if (res.data.message) {
            // add new event real time
            checkEventAndAdd(res.data.message[0]);
          }
        });

        // POST THE EVENT TO NOTIFICATION

        axios__WEBPACK_IMPORTED_MODULE_4___default().post('/member/notification/event', eventFormEntries, options).then(function (result) {
          // GET THE POST EVENTS AND ADD THEM TO THE NOTIFICATION

          axios__WEBPACK_IMPORTED_MODULE_4___default().get("/member/notification/event?notificationNo=".concat(result.data.message)).then(function (result2) {
            (0,_navbar__WEBPACK_IMPORTED_MODULE_2__.addToNotificationTab)(result2.data.message[0]);
            (0,_navbar__WEBPACK_IMPORTED_MODULE_2__.increaseNotificationCount)();
          });
        });
      });
      displayNone();

      // window.location.replace("/member/profilePage")
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

/***/ "./resources/asset/js/components/profilePage/eventHTML.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/eventHTML.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   eventHtml: () => (/* binding */ eventHtml)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
"use static";


var eventHtml = function eventHtml(data) {
  return "<p class='eventInfo'>\n            <strong>RSVP: </strong> ".concat(data.firstName, " ").concat(data.lastName, "</p>\n            <p class='eventInfo'><strong>Event: </strong>").concat(data.eventName, "</p>\n            <p class='eventInfo'><strong>Date: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.date2String)(data.eventDate), "</p>\n            <p class='eventInfo'><strong>Type: </strong>").concat(data.eventType, "</p>\n            <p class='eventInfo'><strong>Description: </strong> ").concat(data.eventDescription, "</p>\n            <input type='hidden' name='event_no' id='event").concat(data.no, "' value='").concat(data.no, "'>\n            \n           <hr>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/friendRequestCard.js":
/*!************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/friendRequestCard.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


var appUrl = "http://olaogun.test/";
var approver_id = sessionStorage.getItem('idSetFromHttp');
axios__WEBPACK_IMPORTED_MODULE_0___default().get("/getFriendRequestById?id=".concat(approver_id)).then(function (results) {
  if (results.data.message) {
    results.data.message.forEach(function (request) {
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', htmlFriendRequest(request));
    });
  }
});

// const countFriendRequest = (friend) => {
//   return friend.length > 1 ? `<p><b>Friend Requests</b></p><br></br>` : `<p><b>Friend Request</b></p><br>`;
// }

var imgFriendRequest = function imgFriendRequest(data) {
  return "<img src=\"/img/profile/".concat(data.img, "\" alt=\"Avatar\" style=\"width:50%\"><br>");
};
var buttonFriendRequest = function buttonFriendRequest(data) {
  return "<div class=\"w3-row w3-opacity\" >\n            <div class=\"w3-half\">\n              <a href=".concat(appUrl, "member/request?req=").concat(data.id, "&appr=").concat(approver_id, "&dec=50&reqCode=").concat(data.famCode, "&src=pp  style=\"text-decoration: none;\"> \n              \n                <button class=\"w3-button w3-block w3-green w3-section\" title=\"Accept\"><i class=\"fa fa-check\"></i>\n              \n                </button>\n              \n              </a>\n\n            </div>\n\n            <div class=\"w3-half\">\n                  <a href=").concat(appUrl, "member/request?req=").concat(data.id, "&appr=").concat(approver_id, "&dec=10>\n                  <button class=\"w3-button w3-block w3-red w3-section\" title=\"Decline\"><i class=\"fa fa-remove\"></i></button>\n              \n                  </a>\n             \n            </div>\n             \n         </div>");
};
var name = function name(data) {
  return "<span>".concat(data.firstName, " ").concat(data.lastName, "</span>");
};
var htmlFriendRequest = function htmlFriendRequest(data) {
  return "\n    <p id=".concat(data.id, "_linkRequestCard></p>\n    ").concat(imgFriendRequest(data), "\n    ").concat(name(data), "\n    ").concat(buttonFriendRequest(data), "\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   html: () => (/* binding */ html)
/* harmony export */ });
/* harmony import */ var _htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./htmlFolder/nameImageTiming */ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js");
/* harmony import */ var _htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./htmlFolder/commentForm */ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js");
/* harmony import */ var _htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./htmlFolder/likeCommentButton */ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js");
/* harmony import */ var _htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./htmlFolder/showPostImages */ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");





var html = function html(el) {
  var comment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin\"><br>\n\n      ".concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(el), "\n\n    <hr class=\"w3-clear\">\n\n    <p class=\"postFont\"> ").concat(el.postMessage, " </p>\n\n     ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(el), "\n\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(el), "\n\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(el), "\n\n    <div id = 'showComment").concat(el.post_no, "'>\n\n      ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment), "\n      \n    </div><br>\n  </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js":
/*!*****************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/commentForm.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   commentForm: () => (/* binding */ commentForm)
/* harmony export */ });
var commentForm = function commentForm(data) {
  return " <p id=\"formComment".concat(data.post_no, "_notification\"></p>\n\n  <form \n    action=\"/postCommentProfile\" \n    method=\"post\" id=\"formComment").concat(data.post_no, "\" \n    style=\"display:none\" \n    enctype=\"multipart/form-data\">\n\n    <input \n      name='post_no' \n      type=\"hidden\" \n      name=\"").concat(data.post_no, "\" \n      value=").concat(data.post_no, " />\n\n    <input \n      class=\"w3-input w3-border w3-round-large inputComment\" \n      type=\"text\" \n      placeholder=\"Write a comment\"\n      id=\"inputComment").concat(data.post_no, "\" \n      value = \"\" name='comment'>\n\n    <br>\n\n    <button \n      type='submit' \n      id=\"submitComment").concat(data.post_no, "\" \n      class=\"w3-button w3-green submitComment\">\n        Submit\n    </button>\n    \n    <br><br>\n  </form>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   likeCommentButton: () => (/* binding */ likeCommentButton)
/* harmony export */ });
var likeCommentButton = function likeCommentButton(data) {
  return "\n  <button \n    type=\"button\" \n    id=\"likeButton".concat(data.post_no, "\" \n    name=\"").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-green w3-margin-bottom\">\n    <em class=\"fa fa-thumbs-up\"></em>\n    \xA0   Like \n      <b>\n        <span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">\n          ").concat(data.post_likes, "\n        </span>\n      </b>\n  </button>\n\n   <button \n    type=\"button\" \n    id=\"initComment").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-theme-d2 w3-margin-bottom\">\n      <em class=\"fa fa-comment\"></em> \n        Comment \n    </button>\n    ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js":
/*!*********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   nameImgTiming: () => (/* binding */ nameImgTiming)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");

var timeAgo = function timeAgo(x) {
  return (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x);
};
var fullName = function fullName(_fullName) {
  return "<h6 id=\"fullName\"><b>".concat(_fullName, "</b> </h6>");
};
var postedAt = function postedAt(date) {
  return "<div class=\"timeago postTimeCal w3-right w3-opacity\"  datetime='".concat(date.date_created, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date.date_created), "'> ").concat(timeAgo(date.post_time), "</div>");
};
var nameImgTiming = function nameImgTiming(data) {
  var img = data.img ? "/img/profile/".concat(data.img) : "/avatar/avatarF.png";
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(data.img, "&pID=").concat(data.post_no, "&path=profile&id=").concat(data.id, "\"> <img src=").concat(img, " alt=\"img\" class=\"w3-left w3-circle w3-margin-right postImg\" style=\"width:60px\">\n        </a>\n        ").concat(postedAt(data), " ").concat(fullName(data.fullName));
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showPostImg: () => (/* binding */ showPostImg)
/* harmony export */ });
var showPostImg = function showPostImg(data) {
  // GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE

  var postImagesWithValues = Object.keys(data).filter(function (key) {
    return key.startsWith('post_img') && data[key] !== null;
  }).map(function (el) {
    return data[el];
  });
  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    return "\n    <a href=\"/profilepage/img?dir=img&pics=".concat(imgElement, "&pID=").concat(postNo, "&path=post\">\n      <div class=\"w3-half\">\n        <img src=\"/img/post/").concat(imgElement, "\" style=\"width:100%\" alt=\"images").concat(i, "\" class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat(i, "\">\n      </div>\n    </a>\n  ");
  };
  var imgElements = postImagesWithValues.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }).join('');
  return "\n    <div class=\"w3-row-padding\" style=\"margin:0 -16px\">\n      ".concat(imgElements, "\n      <br>\n    </div>\n  ");
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
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/profilePage/modal.js");
/* harmony import */ var _img__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./img */ "./resources/asset/js/components/profilePage/img.js");
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/profilePage/allEvents.js");
/* harmony import */ var _createEvent__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./createEvent */ "./resources/asset/js/components/profilePage/createEvent.js");
/* harmony import */ var _friendRequestCard__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./friendRequestCard */ "./resources/asset/js/components/profilePage/friendRequestCard.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
// "use strict";
localStorage.removeItem('redirect');








/***/ }),

/***/ "./resources/asset/js/components/profilePage/loadPost.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/loadPost.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _post__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./post */ "./resources/asset/js/components/profilePage/post.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");





// set an empty array
try {
  var state = {
    post: [],
    comment: []
  };
  var serverConnection = new EventSource("/post/getAllPost/update"); // open the server sent event

  // 2. get the data from the database to set the inital data
  var postData = (0,_helper_http__WEBPACK_IMPORTED_MODULE_2__.getMultipleApiData)("/post/getAllPost", '/member/pp/comment');
  postData.then(function (response) {
    state.post = response[0].data.message;
    state.comment = response[1].data.message;
    state.post.map(function (data) {
      return (0,_post__WEBPACK_IMPORTED_MODULE_1__.allPost)(data, state.comment);
    }); // show all the post and comments

    // // let serverConnection = new EventSource("/post/getAllPost/update") // open the server sent event

    var updateComment = function updateComment(e) {
      var commentData = JSON.parse(e.data);
      var newData = state.comment.some(function (com) {
        return com.comment_no === commentData.comment_no;
      }); // check if the comment no does not already exist

      if (!newData) {
        // if it is not available, add to the data state
        state.comment.push(commentData);
      }
    };
    serverConnection.addEventListener("updateComment", function (e) {
      return updateComment(e);
    });
    var updatePost = function updatePost(e) {
      if (e.origin != "http://olaogun.test/") {
        throw new Error("What is your origin?");
      }
      if (e.data) {
        var newPostData = JSON.parse(e.data);
        var newData = state.post.some(function (el) {
          return el.post_no === newPostData.post_no;
        }); // check if the post no does not already exist

        if (!newData) {
          // if it is not available, add to the data state
          state.post.push(newPostData);
        }
      }
      return state.post.map(function (ele) {
        return (0,_post__WEBPACK_IMPORTED_MODULE_1__.appendNewPost)(ele);
      });
    };
    serverConnection.addEventListener("updatePost", function (e) {
      return updatePost(e);
    });

    // // serverConnection.close()

    // AUTOMATICALLY UPDATE TIMESTAMP

    var updatePostTiming = document.querySelectorAll(".timeago");
    var updateCommentTiming = document.querySelectorAll(".commentTiming");
    (0,timeago_js__WEBPACK_IMPORTED_MODULE_3__.render)(updatePostTiming);
    (0,timeago_js__WEBPACK_IMPORTED_MODULE_3__.render)(updateCommentTiming);
  })["catch"](function (err) {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(err);
  });
} catch (error) {
  //  console.log(error)
}

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
  };

  // CREATE EVENT MODAL
  var showEvent = function showEvent() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'block';
  };

  //EVENT ACTION
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createEvent').addEventListener('click', showEvent);
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('postMsg').addEventListener('click', showModal);

  // handle post message
} catch (e) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/post.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/post.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   allPost: () => (/* binding */ allPost),
/* harmony export */   appendNewPost: () => (/* binding */ appendNewPost)
/* harmony export */ });
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


var allPost = function allPost(el, commentData) {
  if (!el) {
    return false;
  }
  var postNo = parseInt(el.post_no);
  var filterComment = commentData.filter(function (comm) {
    return postNo === parseInt(comm.post_no);
  }); // filter the comment to an array

  var postHtml = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(el, filterComment);
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('beforeend', postHtml);
};
var appendNewPost = function appendNewPost(el) {
  if (!el) {
    return false;
  }
  var commentForm1 = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  var inputComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  var submitComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  if (!commentForm1 || !inputComment || !submitComment) {
    var appendHTML = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(el);
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('afterbegin', appendHTML);
  }
};

/***/ })

}]);