(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/kids"],{

/***/ "./resources/asset/js/components/register/modal/kids.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/modal/kids.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../../../global */ "./resources/asset/js/global.js");




var show = function show(e) {
  try {
    // what was picked or selected
    var kidsNo = e.target.value; // use the loop to generate the number of input

    for (var i = 0; i < kidsNo; i++) {
      var no = i + 1;
      var msg = no > 1 ? "Please, enter their names and emails" : "Please, enter your child name and email";
      var getSelectHelp = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('kids_help');
      getSelectHelp.innerHTML = msg;
      getSelectHelp.style.fontSize = '1rem';
      var addKids = " <div class=\"field is-horizontal\">\n            <div class=\"field \">\n        \n            <div class=\"control is-expanded has-icons-left\">\n            <input type=\"text\" placeholder = \"Enter child's full name - ".concat(no, "\" name =kid_name").concat(no, " class=\"input input is-medium\" id=\"kid_name").concat(no, "\">\n            </div></div>\n            <div class=\"field \">\n            <div class=\"control is-expanded has-icons-left\">\n           <input type=\"email\" placeholder = \"Enter child's email - ").concat(no, "\" name=kid_email").concat(no, " class=\"input input is-medium\" id=\"kid_email").concat(no, "\">\n           </div>\n        </div></div><br>");
      var insertedContent = document.querySelector(".kid".concat(no));

      if (insertedContent) {
        insertedContent.parentNode.removeChild(insertedContent);
      }

      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('addChildren').insertAdjacentHTML('afterend', addKids);
    }
  } catch (error) {
    console.log(error.message);
  }
}; // this is to activate the onchange event


(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('kids_id').addEventListener('change', show);

/***/ })

}]);