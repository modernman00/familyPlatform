(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/siblings"],{

/***/ "./resources/asset/js/components/register/modal/siblings.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/register/modal/siblings.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../../../global */ "./resources/asset/js/global.js");




var show = function show(e) {
  var siblingNo = e.target.value; //    const checkAppend = qSel('.appendLabel')
  //         if(checkAppend || id(`noSiblings${no}`) || id(`noSiblingsEmail${no}`)) {
  //             checkAppend.remove()
  //         }
  // use the loop to generate the number of input

  for (var i = 0; i < siblingNo; i++) {
    //    checkAppend && checkAppend.remove()
    var no = i + 1;
    var msg = no > 1 ? "Please, enter their names and emails" : "Please, enter your child name and email";
    var getSelectHelp = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('noSiblings_help');
    getSelectHelp.innerHTML = msg;
    getSelectHelp.style.fontSize = '1rem';
    var addnoSiblings = " <div class=\"row appendLabel\">\n            <div class=\"col\">\n            <input type=\"text\" placeholder = \"Enter sibling's full name - ".concat(no, "\" name =\"sibling_name").concat(no, "\" class=\"form-control\" id=\"sibling_name").concat(no, "\">\n            </div>\n            <div class=\"col\">\n           <input type=\"email\" placeholder = \"Enter sibling's email - ").concat(no, "\" name=\"sibling_email").concat(no, "\" class=\"form-control\" id=\"sibling_email").concat(no, "\">\n           </div>\n        </div><br>");
    if (!(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("noSiblings".concat(no)) || !(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("noSiblingsEmail".concat(no))) (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('noSiblings_div').insertAdjacentHTML('afterend', addnoSiblings);
  }
}; // this is to activate the onchange event


(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('noSiblings').addEventListener('change', show);

/***/ })

}]);