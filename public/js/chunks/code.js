"use strict";
/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["code"],{

/***/ "./resources/asset/js/components/acctMgt/code.js":
/*!*******************************************************!*\
  !*** ./resources/asset/js/components/acctMgt/code.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ \"./node_modules/@modernman00/shared-js-lib/index.js\");\n\n\n\nvar fromForgot = sessionStorage.getItem('fromForgot');\nvar redirectTo;\n// Determine redirect target based on session flag\n\nif (fromForgot) {\n  redirectTo = '/login/changePW';\n} else {\n  redirectTo = '/member/ProfilePage';\n}\nif (fromForgot) sessionStorage.removeItem('fromForgot');\n(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.createCodeSubmitHandler)({\n  formId: 'codeForm',\n  route: '/login/code',\n  redirect: redirectTo,\n  theme: 'bulma'\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvYXNzZXQvanMvY29tcG9uZW50cy9hY2N0TWd0L2NvZGUuanMiLCJtYXBwaW5ncyI6Ijs7QUFBYTs7QUFDeUQ7QUFFdEUsSUFBTUMsVUFBVSxHQUFHQyxjQUFjLENBQUNDLE9BQU8sQ0FBQyxZQUFZLENBQUM7QUFDdkQsSUFBSUMsVUFBVTtBQUNkOztBQUdBLElBQUdILFVBQVUsRUFBQztFQUNaRyxVQUFVLEdBQUcsaUJBQWlCO0FBQ2hDLENBQUMsTUFBSztFQUNKQSxVQUFVLEdBQUcscUJBQXFCO0FBQ3BDO0FBRUEsSUFBSUgsVUFBVSxFQUFFQyxjQUFjLENBQUNHLFVBQVUsQ0FBQyxZQUFZLENBQUM7QUFFdkRMLG1GQUF1QixDQUFDO0VBQ3RCTSxNQUFNLEVBQUUsVUFBVTtFQUNsQkMsS0FBSyxFQUFFLGFBQWE7RUFDcEJDLFFBQVEsRUFBRUosVUFBVTtFQUNwQkssS0FBSyxFQUFFO0FBQ1QsQ0FBQyxDQUFDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vZmFtaWx5Ly4vcmVzb3VyY2VzL2Fzc2V0L2pzL2NvbXBvbmVudHMvYWNjdE1ndC9jb2RlLmpzP2VjMmEiXSwic291cmNlc0NvbnRlbnQiOlsiXCJ1c2Ugc3RyaWN0XCI7XG5pbXBvcnQgeyAgY3JlYXRlQ29kZVN1Ym1pdEhhbmRsZXIgfSBmcm9tICdAbW9kZXJubWFuMDAvc2hhcmVkLWpzLWxpYic7XG5cbmNvbnN0IGZyb21Gb3Jnb3QgPSBzZXNzaW9uU3RvcmFnZS5nZXRJdGVtKCdmcm9tRm9yZ290Jyk7XG5sZXQgcmVkaXJlY3RUbztcbi8vIERldGVybWluZSByZWRpcmVjdCB0YXJnZXQgYmFzZWQgb24gc2Vzc2lvbiBmbGFnXG5cblxuaWYoZnJvbUZvcmdvdCl7XG4gIHJlZGlyZWN0VG8gPSAnL2xvZ2luL2NoYW5nZVBXJztcbn1lbHNlIHtcbiAgcmVkaXJlY3RUbyA9ICcvbWVtYmVyL1Byb2ZpbGVQYWdlJztcbn1cblxuaWYgKGZyb21Gb3Jnb3QpIHNlc3Npb25TdG9yYWdlLnJlbW92ZUl0ZW0oJ2Zyb21Gb3Jnb3QnKTtcblxuY3JlYXRlQ29kZVN1Ym1pdEhhbmRsZXIoe1xuICBmb3JtSWQ6ICdjb2RlRm9ybScsIFxuICByb3V0ZTogJy9sb2dpbi9jb2RlJywgXG4gIHJlZGlyZWN0OiByZWRpcmVjdFRvLFxuICB0aGVtZTogJ2J1bG1hJ1xufSk7Il0sIm5hbWVzIjpbImNyZWF0ZUNvZGVTdWJtaXRIYW5kbGVyIiwiZnJvbUZvcmdvdCIsInNlc3Npb25TdG9yYWdlIiwiZ2V0SXRlbSIsInJlZGlyZWN0VG8iLCJyZW1vdmVJdGVtIiwiZm9ybUlkIiwicm91dGUiLCJyZWRpcmVjdCIsInRoZW1lIl0sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./resources/asset/js/components/acctMgt/code.js\n\n}");

/***/ })

}]);