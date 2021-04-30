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
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/global.js");
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../helper */ "./resources/asset/js/helper.js");

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
  (0,_helper__WEBPACK_IMPORTED_MODULE_2__.loaderIcon)();
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(response.data); // add loader

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
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/global.js");
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

/***/ "./resources/asset/js/helper.js":
/*!**************************************!*\
  !*** ./resources/asset/js/helper.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "realTimeServer": () => (/* binding */ realTimeServer),
/* harmony export */   "loaderIconBootstrap": () => (/* binding */ loaderIconBootstrap),
/* harmony export */   "loaderIcon": () => (/* binding */ loaderIcon)
/* harmony export */ });
var _this = undefined;

var realTimeServer = function realTimeServer(input, url, outputId) {
  var theInput, inputVal, output;
  theInput = _this.id(input);
  output = _this.id(outputId);
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
};
var loaderIconBootstrap = function loaderIconBootstrap() {
  return "<div class=\"spinner-grow text-primary\" role=\"status\">\n        <span class=\"sr-only\">Loading...</span>\n        </div>";
};
var loaderIcon = function loaderIcon() {
  return "<div class=\"loader\"></div>";
};

/***/ })

}]);