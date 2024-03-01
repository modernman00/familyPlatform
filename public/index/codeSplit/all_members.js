"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/all_members"],{

/***/ "./resources/asset/js/components/allMembers/api.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/api.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
/* harmony import */ var _filterMembersByFamCode__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./filterMembersByFamCode */ "./resources/asset/js/components/allMembers/filterMembersByFamCode.js");
/* harmony import */ var _handleInput__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./handleInput */ "./resources/asset/js/components/allMembers/handleInput.js");





var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
var famCode = localStorage.getItem('requesterFamCode');
var reqId = localStorage.getItem('requesterId');
var URL = "http://olaogun.test/";
var allMembersContainer = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers');
var noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";
var renderMembers = function renderMembers(data, container, noMemberMessage) {
  container.innerHTML = "";
  if (data) {
    data.forEach(_html__WEBPACK_IMPORTED_MODULE_2__.renderHtml);
  } else {
    container.innerHTML = noMemberMessage;
  }
};
axios__WEBPACK_IMPORTED_MODULE_0___default().get("".concat(URL, "allMembers/processApiData?id=").concat(reqId), config).then(function (response) {
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').innerHTML = "";
  if (!response.data) {
    throw Error('There is no data');
  }
  if (!famCode) {
    throw Error('There is no famCode');
  }
  var data = response.data;
  var dataWithFamCode = (0,_filterMembersByFamCode__WEBPACK_IMPORTED_MODULE_3__.filterMembersByFamCode)(data, famCode);
  renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML);

  // Remove the "loader" class after rendering is complete
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('setLoader').classList.remove('loader');
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('searchFamily').addEventListener('input', function () {
    return (0,_handleInput__WEBPACK_IMPORTED_MODULE_4__.handleInput)(data, dataWithFamCode, renderMembers);
  });
})["catch"](function (err) {
  return (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(err.message);
});

/***/ }),

/***/ "./resources/asset/js/components/allMembers/filterMembersByFamCode.js":
/*!****************************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/filterMembersByFamCode.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   filterMembersByFamCode: () => (/* binding */ filterMembersByFamCode)
/* harmony export */ });
var reqId = localStorage.getItem('requesterId');
var filterMembersByFamCode = function filterMembersByFamCode(data, famCode) {
  return data.filter(function (el) {
    return el.id !== reqId && (el.famCode === famCode || el.requesterCode === famCode);
  });
};

/***/ }),

/***/ "./resources/asset/js/components/allMembers/handleInput.js":
/*!*****************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/handleInput.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   handleInput: () => (/* binding */ handleInput)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }


var reqId = localStorage.getItem('requesterId');
var allMembersContainer = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('allMembers');
var noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";
var handleInput = function handleInput(data, WithFamCode, renderMembers) {
  var searchInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('searchFamily');
  var inputVal = searchInput.value.trim().toLowerCase();
  allMembersContainer.innerHTML = "";
  if (inputVal === "") {
    renderMembers(WithFamCode, allMembersContainer, noMemberHTML);
  } else {
    var filteredData = data.filter(function (el) {
      return el.firstName.toLowerCase().includes(inputVal) || el.lastName.toLowerCase().includes(inputVal);
    });
    if (filteredData.length === 0) {
      allMembersContainer.innerHTML = "No matching name found.";
    } else {
      var uniqueItems = {};
      var _iterator = _createForOfIteratorHelper(filteredData),
        _step;
      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var item = _step.value;
          if (!uniqueItems[item.id] || item.requester_id == reqId) {
            uniqueItems[item.id] = item;
          }
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
      var filteredDataByIdAndCurrentUser = Object.values(uniqueItems);
      filteredDataByIdAndCurrentUser.forEach(_html__WEBPACK_IMPORTED_MODULE_1__.renderHtml);
    }
  }
};

/***/ }),

/***/ "./resources/asset/js/components/allMembers/html.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/html.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtml: () => (/* binding */ renderHtml)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");



var renderHtml = function renderHtml(el) {
  var famCode = localStorage.getItem('requesterFamCode');
  var reqId = localStorage.getItem('requesterId');
  try {
    if (!el) {
      // Handle the case where 'el' is falsy, such as when data is not available.
      throw new Error('there is no data');
    }
    var theImg = "/img/profile/".concat(el.img);
    var isUserInSameFamily = famCode == el.famCode || famCode == el.requesterCode;
    var statusButtonHTML = el.status && el.requester_id === reqId && el.status !== 'Approved' ? el.status : 'Add to family';
    var disableButton = statusButtonHTML === "Request sent" ? "disabled" : "";
    var fatherName = (0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(el.fatherName);
    var motherName = (0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(el.motherName);
    // const spouse = toSentenceCase(el.spouseName);

    // Create the HTML content based on whether the user is in the same family or not.
    var html = "\n        <div class=\"w3-col l3 m6 w3-margin-bottom w3-round-xlarge\" id=\"".concat(el.id, "\">\n          \n                <img src=\"").concat(theImg, "\" style=\"width:100%; height:300px;\" alt=\"").concat(el.firstName, "\">\n                      <ul class=\"w3-ul w3-border w3-center w3-hover-shadow\">\n    \n                    <li class=\"w3-black w3-large w3-padding-16\">").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(el.firstName), " ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(el.lastName), "</li>\n\n                    <li class=\"w3-padding-8 allMember_card_content\">\n                         <b>Country:</b> ").concat(el.country, " </li>\n\n                        ").concat(isUserInSameFamily ? "    <li class=\"w3-padding-8\"> <b>Father:</b> ".concat(fatherName, "</li>\n                             <li class=\"w3-padding-8\"> <b>Mother:</b> ").concat(motherName, "</li>\n                             <li class=\"w3-padding-8\"> <b>Spouse:</b> ").concat(el.spouseName || 'none', "</li>\n                             <li class=\"w3-padding-8\"> <b>Mobile:</b> ").concat(el.mobile, " </li>\n                             <li class=\"w3-padding-8\"> <b>Date joined:</b> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(el.created_at), "</li>\n\n                             <li class=\"w3-light-grey w3-padding-16\">\n                             <button class=\"w3-button w3-green w3-padding-small\">\n                                <a href=\"/allMembers/setProfile?id=").concat(el.id, "\">\n                                    See Profile\n                                </a>\n                                </button>\n                                \n                            </li>") : "<li class=\"w3-light-grey w3-padding-16\">\n                            <button type=\"button\" data-user-id=\"addFamily".concat(el.id, "\" class=\"w3-button w3-green w3-padding-large button\" id=\"addFamily").concat(el.id, "\" ").concat(disableButton, ">\n                                ").concat(statusButtonHTML, "\n                            </button>\n                            \n                        </li>"), "\n                    \n     \n          </ul>\n        </div>");

    // Insert the HTML content into the 'allMembers' element.
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').insertAdjacentHTML('beforeend', html);
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  }
};

/***/ }),

/***/ "./resources/asset/js/components/allMembers/index.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/index.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./api */ "./resources/asset/js/components/allMembers/api.js");
/* harmony import */ var _sendRequest__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./sendRequest */ "./resources/asset/js/components/allMembers/sendRequest.js");




/***/ }),

/***/ "./resources/asset/js/components/allMembers/sendRequest.js":
/*!*****************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/sendRequest.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw new Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw new Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }




// Attach a click event listener to the document

document.onclick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(e) {
    var targetId, userId, approverDetails, requesterDetails, familyRequestData, response;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          // Get the target element's ID
          targetId = e.target.id; // Check if the ID includes 'addFamily'
          if (!targetId.includes('addFamily')) {
            _context.next = 15;
            break;
          }
          // Extract the user ID from the target ID
          userId = targetId.replace("addFamily", ""); // Fetch approver details for the user
          _context.next = 6;
          return fetchApproverData(userId);
        case 6:
          approverDetails = _context.sent;
          // Prepare family request data
          requesterDetails = getLocalStorageProfile();
          familyRequestData = {
            requester: requesterDetails,
            approver: approverDetails,
            emailPath: "msg/request",
            subject: "".concat(requesterDetails.requesterFirstName, " ").concat(requesterDetails.requesterLastName, " sent you a family request")
          }; // Send the family request data to the server for processing
          _context.next = 11;
          return sendFamilyRequest(familyRequestData);
        case 11:
          response = _context.sent;
          // ADD TO THE NOTIFICATION TAB
          (0,_navbar__WEBPACK_IMPORTED_MODULE_2__.addToNotificationTab)(response.data.message);
          (0,_navbar__WEBPACK_IMPORTED_MODULE_2__.increaseNotificationCount)();

          // Update the button's HTML and disable it
          updateButton(targetId, 'Sent');
        case 15:
          _context.next = 20;
          break;
        case 17:
          _context.prev = 17;
          _context.t0 = _context["catch"](0);
          // Handle any errors that occur during execution
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_context.t0);
        case 20:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 17]]);
  }));
  return function (_x) {
    return _ref.apply(this, arguments);
  };
}();

// Function to fetch approver data based on user ID
function fetchApproverData(_x2) {
  return _fetchApproverData.apply(this, arguments);
} // Function to retrieve requester details from local storage
function _fetchApproverData() {
  _fetchApproverData = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2(userId) {
    var result, approverDetails;
    return _regeneratorRuntime().wrap(function _callee2$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 3;
          return axios__WEBPACK_IMPORTED_MODULE_0___default().get("/members/familyRequestMgt/getApprover?id=".concat(userId));
        case 3:
          result = _context2.sent;
          approverDetails = {
            approverFirstName: result.data.message.firstName,
            approverLastName: result.data.message.lastName,
            approverEmail: result.data.message.email,
            approverId: result.data.message.id,
            approverCode: result.data.message.famCode
          };
          return _context2.abrupt("return", approverDetails);
        case 8:
          _context2.prev = 8;
          _context2.t0 = _context2["catch"](0);
          console.error(_context2.t0);
          throw _context2.t0;
        case 12:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 8]]);
  }));
  return _fetchApproverData.apply(this, arguments);
}
function getLocalStorageProfile() {
  var getRequesterDetails = localStorage.getItem('profile');
  return JSON.parse(getRequesterDetails);
}

// Function to send family request data to the server
function sendFamilyRequest(_x3) {
  return _sendFamilyRequest.apply(this, arguments);
} // Function to update the button's HTML and disable it
function _sendFamilyRequest() {
  _sendFamilyRequest = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee3(data) {
    return _regeneratorRuntime().wrap(function _callee3$(_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.prev = 0;
          _context3.next = 3;
          return axios__WEBPACK_IMPORTED_MODULE_0___default().post("/members/familyRequestMgt", data);
        case 3:
          return _context3.abrupt("return", _context3.sent);
        case 6:
          _context3.prev = 6;
          _context3.t0 = _context3["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_context3.t0);
        case 9:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[0, 6]]);
  }));
  return _sendFamilyRequest.apply(this, arguments);
}
function updateButton(targetId, newHTML) {
  var theTargetId = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(targetId);
  theTargetId.innerHTML = newHTML;
  theTargetId.disabled = true;
}

// ADD THE NEW EVENT TO THE NOTIFICATION TAB

/***/ }),

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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

/***/ "./resources/asset/js/components/navbar.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/navbar.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToNotificationTab: () => (/* binding */ addToNotificationTab),
/* harmony export */   increaseNotificationCount: () => (/* binding */ increaseNotificationCount)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_3__);



// const timeAgo = (x) => format(x)

var postAgoNotification = function postAgoNotification(date) {
  return "\n  <div class=\"notification_timeago w3-left w3-opacity\" datetime='".concat(date, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "\n  </div>");
};
// this is the notification htnl 
var notificationHTML = function notificationHTML(data) {
  return "<a data-id=\"".concat(data.sender_id, "\" class=\"w3-bar-item w3-button notification_real_time linkRequestCard\">\n  \n\n  ").concat(postAgoNotification(data.created_at), " -\n  <b> ").concat(data.notification_type, "</b> -\n  ").concat(data.notification_name, " -\n  ").concat(data.notification_content, " -\n  ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(data.sender_name), "\n\n  \n  </a>");
};

// CLICK FUNCTION ON THE NOTIFICATION BAR THAT TAKES ONE TO THE FRIEND REQUEST CARD

var increaseNotificationCount = function increaseNotificationCount() {
  var currentNotificationCount = parseInt(sessionStorage.getItem('notificationCount')) + 1;
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = currentNotificationCount;
};
var addToNotificationTab = function addToNotificationTab(data) {
  return (0,_global__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab').insertAdjacentHTML('afterbegin', notificationHTML(data));
};

// GET THE ID SET FROM THE LOGIN FILE - HTTPS.JS

var idSetFromHttp = sessionStorage.getItem('idSetFromHttp');
var famCodeSetFromHttp = sessionStorage.getItem('famCodeSetFromHttp');
var notificationURL = "/member/notifications/id?notificationId=".concat(idSetFromHttp, "&famCode=").concat(famCodeSetFromHttp);

// const getData = axios.get(notificationURL);

try {
  axios__WEBPACK_IMPORTED_MODULE_3___default().get(notificationURL).then(function (res) {
    // Extract the notifications from the response
    var data = res.data.message;
    if (data) {
      if (data.length > 0) {
        // Display the count of notifications
        (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = data.length;

        // Store the notification count in session storage
        sessionStorage.setItem('notificationCount', data.length);

        // Display each notification
        data.forEach(function (element) {
          addToNotificationTab(element);
        });

        // Update the timing of notifications
        var updateNotificationTiming = document.querySelectorAll(".notification_timeago");
        (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.render)(updateNotificationTiming);
      } else {
        (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = 0;
      }
    }
  })["catch"](function (error) {
    // Handle any errors that occur during the process
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  });
} catch (error) {
  // Handle exceptions that occur outside of the promise chain
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
}

// ONCE THE NOTIFICATION BAR IS CLICKED, IT SHOULD TAKE YOU TO BE FRIEND REQUEST CARD

// Add a click event listener to elements with the "linkRequestCard" class
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('linkRequestCard')) {
    var friendRequestSection = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(e.target.getAttribute('data-id'), "_linkRequestCard"));
    if (friendRequestSection) {
      friendRequestSection.scrollIntoView({
        behavior: "smooth"
      });
    }
  }
});

///member/notifications

/***/ })

}]);