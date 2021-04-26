(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/all_members"],{

/***/ "./resources/asset/js/components/allMembers/GetData.js":
/*!*************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/GetData.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./../../global */ "./resources/asset/js/global.js");
/* harmony import */ var _helper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../helper */ "./resources/asset/js/helper.js");



(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('searchFamily').addEventListener('keyup', function (event) {
  var value = event.target.value;
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(value);
}); // const config = {
//     headers: {
//         'Content-Type': 'application/json'
//     },
//     data: {},
//     }
// axios.get('http://olaogun.dev.com/allMembers',config)
//     .then((response) => {
//         log(response.data);
//         log(response.headers);
//         })
//     .catch(err => console.error(err))

/***/ }),

/***/ "./resources/asset/js/components/allMembers/index.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/index.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _GetData__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./GetData */ "./resources/asset/js/components/allMembers/GetData.js");


/***/ }),

/***/ "./resources/asset/js/helper.js":
/*!**************************************!*\
  !*** ./resources/asset/js/helper.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "realTimeServer": () => (/* binding */ realTimeServer)
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

/***/ })

}]);