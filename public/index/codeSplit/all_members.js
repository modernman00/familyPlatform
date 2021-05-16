(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/all_members"],{

/***/ "./resources/asset/js/components/allMembers/api.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/api.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");

 // import { eventInput } from "./event"


var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};

var renderHtml = function renderHtml(el) {
  if (el) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').classList.remove('loader');
    var html = "<div class=\"card col-sm-4\" id=".concat(el.id, ">\n \n        <div class=\"card-body\">\n                    <h4 class='card-title'>").concat(el.firstName, " ").concat(el.lastName, "</h4>\n                    <p class=\"card-text\"><b>Alias:</b> ").concat(el.alias, " \n                    <br> <b>Father:</b>  ").concat(el.fatherName, "\n                    <br> <b>Mother:</b> ").concat(el.motherName, "\n                    <br> <b>Spouse:</b> ").concat(el.spouseName && 'none', "\n                    <br> <b>Contact:</b>  ").concat(el.email, " | ").concat(el.mobile, " \n                    <br> <b>Date joined:</b> ").concat(el.date_created, " ago </p>\n                    <a href=\"/setProfile?id=").concat(el.id, "\" class=\"btn btn-primary stretched-link\">See Profile</a>\n                </div>\n            </div>");
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').insertAdjacentHTML('beforeend', html);
  } else {
    return "<p> Sorry, we could find the data</p>";
  }
};

axios__WEBPACK_IMPORTED_MODULE_0___default().get('http://olaogun.dev.com/allMembers3', config).then(function (response) {
  (0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.loaderIcon)(); // add loader

  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').classList.add('loader');
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').innerHTML = "";
  response.data.map(function (el) {
    renderHtml(el);
  });

  var input = function input() {
    var inputVal = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('searchFamily').value;

    if (inputVal == "") {
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').innerHTML = "";
      response.data.map(function (el) {
        renderHtml(el);
      });
    }

    if (inputVal) {
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').innerHTML = "";
      var filter = response.data.filter(function (el) {
        return el.firstName.toLowerCase().includes(inputVal.toLowerCase());
      });
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(filter);
      filter.map(function (el) {
        renderHtml(el);
      });
    }
  };

  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('searchFamily').addEventListener('keyup', input); // log(searchInput)
  // const filteredData = data.filter(ele => {
  // })
  // response.data.map(el => {
  //     renderHtml(el)
  // })
}) // id('allMembers').classList.remove('loader')
["catch"](function (err) {
  return console.error(err.message);
});

/***/ }),

/***/ "./resources/asset/js/components/allMembers/event.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/event.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
 // export const eventInput = () => {
//     id('searchFamily').addEventListener('keydown', (event) => {
//     let searchValue = event.target.value
//     id('searchHidden').innerHTML = searchValue
// })
// }

/***/ }),

/***/ "./resources/asset/js/components/allMembers/index.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/index.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./api */ "./resources/asset/js/components/allMembers/api.js");
/* harmony import */ var _event__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./event */ "./resources/asset/js/components/allMembers/event.js");



/***/ }),

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "loaderIconBootstrap": () => (/* binding */ loaderIconBootstrap),
/* harmony export */   "loaderIcon": () => (/* binding */ loaderIcon),
/* harmony export */   "loaderIconBulma": () => (/* binding */ loaderIconBulma),
/* harmony export */   "removeDiv": () => (/* binding */ removeDiv),
/* harmony export */   "createAndAppendElement": () => (/* binding */ createAndAppendElement),
/* harmony export */   "autoCompleter": () => (/* binding */ autoCompleter),
/* harmony export */   "distinctValue": () => (/* binding */ distinctValue),
/* harmony export */   "checkBox": () => (/* binding */ checkBox),
/* harmony export */   "isChecked": () => (/* binding */ isChecked),
/* harmony export */   "matchRegex": () => (/* binding */ matchRegex)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! autocompleter */ "./node_modules/autocompleter/autocomplete.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(autocompleter__WEBPACK_IMPORTED_MODULE_1__);


function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }



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
      text = text.toLowerCase(); // you can also use AJAX requests instead of preloaded data

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
  return "<div class=\"control\"> \n        <label class=\"radio\">\n          <input type=\"radio\" name=\"send".concat(subject, "\" value=\"yes\" id=").concat(subject, "Yes> Yes \n        </label>\n        <label class=\"radio\"> \n          <input type=\"radio\" name=\"send").concat(subject, "\" value=\"no\" id=").concat(subject, "No> No \n        </label>\n      </div>");
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
      var regex = /[a-zA-Z0-9.@]+/g;
      var result = data.match(regex);
      if (result.length > 1) return false;
    }
  }
};

/***/ })

}]);