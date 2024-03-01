(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/code"],{

/***/ "./resources/asset/js/components/generateCode/Code.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/generateCode/Code.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");





// block the setLoader div

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("setLoader").style.display = "none";
var LoginCode = function LoginCode(e) {
  try {
    e.preventDefault();
    // just in case there was an earlier error notification - remove it
    var codeNotification = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('codeForm_notification');
    if (codeNotification.classList.contains('is-danger')) {
      codeNotification.classList.remove('is-danger');
    }
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('setLoader').style.display = "block";

    // get the direct from the login script (getstorage)
    var location = localStorage.getItem('redirect');
    (0,_helper_http__WEBPACK_IMPORTED_MODULE_1__.postFormData)("/login/code", "codeForm", location);
  } catch (err) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
  }
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submit').addEventListener('click', LoginCode);

/***/ }),

/***/ "./resources/asset/js/components/helper/http.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/helper/http.js ***!
  \******************************************************/
/***/ (() => {

throw new Error("Module build failed (from ./node_modules/babel-loader/lib/index.js):\nSyntaxError: /Users/olawaleolaogun/Sites/familyPlatform/resources/asset/js/components/helper/http.js: Unexpected token, expected \"(\" (59:8)\n\n\u001b[0m \u001b[90m 57 |\u001b[39m         \u001b[36mif\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 58 |\u001b[39m\u001b[0m\n\u001b[0m\u001b[31m\u001b[1m>\u001b[22m\u001b[39m\u001b[90m 59 |\u001b[39m         sessionStorage\u001b[33m.\u001b[39msetItem(\u001b[32m'idSetFromHttp'\u001b[39m\u001b[33m,\u001b[39m idSetFromHttp)\u001b[33m;\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m    |\u001b[39m         \u001b[31m\u001b[1m^\u001b[22m\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 60 |\u001b[39m         sessionStorage\u001b[33m.\u001b[39msetItem(\u001b[32m'famCodeSetFromHttp'\u001b[39m\u001b[33m,\u001b[39m famCodeSetFromHttp)\u001b[33m;\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 61 |\u001b[39m\u001b[0m\n\u001b[0m \u001b[90m 62 |\u001b[39m         processFormDataAction(successClass\u001b[33m,\u001b[39m dbHttpResult\u001b[33m,\u001b[39m notificationId)\u001b[33m;\u001b[39m\u001b[0m\n    at constructor (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:345:19)\n    at Parser.raise (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:3199:19)\n    at Parser.unexpected (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:3229:16)\n    at Parser.expect (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:3566:28)\n    at Parser.parseHeaderExpression (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12823:10)\n    at Parser.parseIfStatement (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12930:22)\n    at Parser.parseStatementContent (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12561:21)\n    at Parser.parseStatementLike (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12530:17)\n    at Parser.parseStatementListItem (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12510:17)\n    at Parser.parseBlockOrModuleBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13131:61)\n    at Parser.parseBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13124:10)\n    at Parser.parseBlock (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13112:10)\n    at Parser.parseTryStatement (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13015:23)\n    at Parser.parseStatementContent (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12569:21)\n    at Parser.parseStatementLike (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12530:17)\n    at Parser.parseStatementListItem (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12510:17)\n    at Parser.parseBlockOrModuleBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13131:61)\n    at Parser.parseBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13124:10)\n    at Parser.parseBlock (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13112:10)\n    at Parser.parseFunctionBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:11893:24)\n    at Parser.parseArrowExpression (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:11868:10)\n    at Parser.parseAsyncArrowFromCallExpression (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:11045:10)\n    at Parser.parseCoverCallAndAsyncArrowHead (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10951:27)\n    at Parser.parseSubscript (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10878:19)\n    at Parser.parseSubscripts (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10849:19)\n    at Parser.parseExprSubscripts (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10840:17)\n    at Parser.parseUpdate (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10819:21)\n    at Parser.parseMaybeUnary (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10795:23)\n    at Parser.parseMaybeUnaryOrPrivate (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10633:61)\n    at Parser.parseExprOps (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10638:23)\n    at Parser.parseMaybeConditional (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10615:23)\n    at Parser.parseMaybeAssign (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10576:21)\n    at /Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10546:39\n    at Parser.allowInAnd (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12221:16)\n    at Parser.parseMaybeAssignAllowIn (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:10546:17)\n    at Parser.parseVar (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13201:91)\n    at Parser.parseVarStatement (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13042:10)\n    at Parser.parseStatementContent (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12625:23)\n    at Parser.parseStatementLike (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12530:17)\n    at Parser.parseStatementListItem (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12510:17)\n    at Parser.parseExportDeclaration (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13777:17)\n    at Parser.maybeParseExportDeclaration (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13732:31)\n    at Parser.parseExport (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13655:29)\n    at Parser.parseStatementContent (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12657:27)\n    at Parser.parseStatementLike (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12530:17)\n    at Parser.parseModuleItem (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12507:17)\n    at Parser.parseBlockOrModuleBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13131:36)\n    at Parser.parseBlockBody (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:13124:10)\n    at Parser.parseProgram (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12406:10)\n    at Parser.parseTopLevel (/Users/olawaleolaogun/Sites/familyPlatform/node_modules/@babel/parser/lib/index.js:12396:25)");

/***/ })

}]);