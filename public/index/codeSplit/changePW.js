(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/changePW"],{

/***/ "./resources/asset/js/components/changePW/index.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/changePW/index.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
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

/***/ })

}]);