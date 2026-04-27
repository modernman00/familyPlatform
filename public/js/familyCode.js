"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["familyCode"],{

/***/ "./resources/asset/js/components/register/familyCode.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/familyCode.js ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");



var btnFamCode = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)("btnFamCode");
btnFamCode.addEventListener("click", function () {
  try {
    if ((0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('surname_id').value !== "") {
      var uniqueNumber = Date.now();
      var uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber) / 10000000);
      var getSurname = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('surname_id').value;
      var firstFourLetters = getSurname.substring(0, 4);
      (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('createCode').value = "".concat(firstFourLetters.toUpperCase()).concat(uniqueNumber1);
      btnFamCode.disabled = true;
    }
  } catch (error) {
    (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)("surname_error").innerHTML = error.messages;
  }
});

// Get references to the HTML output and the copy icon

var copyIcon = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('copyIcon');
var htmlOutputDiv = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('createFamCode');
var htmlOutput = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('createCode');
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
          (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('famCode_id').value = htmlOutput.value;
          _context.next = 6;
          break;
        case 5:
          copyIcon.innerHTML = "copy";
          (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('famCode_id').value = "";
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

/***/ })

}]);
//# sourceMappingURL=familyCode.js.map