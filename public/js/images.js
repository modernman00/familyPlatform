"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["images"],{

/***/ "./resources/asset/js/components/images.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/images.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");





// const modal = id("imageModal");
// const modalImg = id("modalImage");
// const close = qSel(".close");

// qSelAll(".photo-grid img").forEach(img => {
//   img.addEventListener("click", () => {
//     modal.style.display = "block";
//     modalImg.src = img.src;
//   });

//   // Touch support for mobile
//   img.addEventListener("touchstart", () => {
//     modal.style.display = "block";
//     modalImg.src = img.src;
//   });
// });

// close.onclick = () => {
//   modal.style.display = "none";
// };

// modal.onclick = () => {
//   modal.style.display = "none";
// };

// Like functionality

var likeCount = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
    var elementId, newId, likeCounter;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          elementId = e.target.id;
          if (!elementId.startsWith('likeBtn')) {
            _context.next = 1;
            break;
          }
          // remove likeBtn from the id 
          newId = elementId.replace('likeBtn', ''); // remove the love symbol from the idInnerHTML
          // include /\n/g in the replace function 
          likeCounter = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)(elementId).innerHTML.replace('❤️', '').trim(); // make likeCounter a number and add 1 
          likeCounter = parseInt(likeCounter) + 1;

          // update the column like in the database table memory 
          _context.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].put("/memory/like/".concat(newId, "/").concat(likeCounter)).then(function (res) {
            if (res.data.token == 'success') {
              (0,_shared__WEBPACK_IMPORTED_MODULE_2__.log)(res.data.message);
              (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)(elementId).innerHTML = "\u2764\uFE0F ".concat(res.data.message, " ");
              (0,_shared__WEBPACK_IMPORTED_MODULE_2__.log)(res.data.message);
            }
          }).catch(function (err) {
            return console.error('Like error:', err);
          });
        case 1:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return function likeCount(_x) {
    return _ref.apply(this, arguments);
  };
}();
document.addEventListener('click', likeCount);
var processMemory = function processMemory(event) {
  event.preventDefault();
  postFormData('/memory', 'memoryUploadForm', 'memory');
  var grid = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('photoGrid');
};
(0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)('submitMemoryBtn').addEventListener('click', processMemory);

/***/ })

}]);
//# sourceMappingURL=images.js.map