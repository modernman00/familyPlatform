"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/register"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FormHelper)
/* harmony export */ });
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");


function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }

var FormHelper = /*#__PURE__*/function () {
  function FormHelper(data) {
    _classCallCheck(this, FormHelper);
    this.data = data;
    this.error = [];
    this.result = 0;
  }
  _createClass(FormHelper, [{
    key: "id",
    value: function id(x) {
      return document.getElementById(x);
    }

    /**
     * general validation; check empty status, at least a single input, mobile length, white space
     */
  }, {
    key: "getData",
    value: function getData() {
      return this.data;
    }
  }, {
    key: "massValidate",
    value: function massValidate() {
      var _this = this;
      // const reg = /[a-zA-Z0-9./@]/g;
      this.data.forEach(function (et) {
        var _iterator = _createForOfIteratorHelper(et),
          _step;
        try {
          for (_iterator.s(); !(_step = _iterator.n()).done;) {
            var post = _step.value;
            // capture the error to a variable
            var errMsg = _this.id("".concat(post.name, "_error"));
            // console.log(post)
            // rid it off the submit and token
            if (post.name == 'submit' || post.name == 'button' || post.name == 'token' || post.name == 'cancel' || post.name == "checkbox_id") {
              continue;
            }

            // check if there is no value

            var postName = post.name.replace('_', ' ');
            if (postName == "spouseName" || postName == "spouseMobile" || postName == "spouseEmail" || postName == "fatherMobile" || postName == "fatherEmail" || postName == "motherMobile" || postName == "motherEmail") {
              if (post.value === "") {
                post.value = "Not Provided";
              }
            }
            if (post.value === '' || post.value === 'select') {
              errMsg.innerHTML = "* cannot be left empty";
              errMsg.style.color = "red";
              _this.error.push("".concat(postName.toUpperCase(), " cannot be left empty"));
            } else {
              _this.result = 1;
            }
            var checkRegex = (0,_helper_general__WEBPACK_IMPORTED_MODULE_0__.matchRegex)(post.value);
            if (checkRegex === false) {
              _this.error.push("There is a problem with you entry for ".concat(postName.toUpperCase(), "'s question"));
              errMsg.innerHTML = "* There is a problem with you entry for ".concat(postName.toUpperCase(), "'s question");
            }
          }
        } catch (err) {
          _iterator.e(err);
        } finally {
          _iterator.f();
        }
      });
    }
  }, {
    key: "emailVal",
    value: function emailVal() {
      var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
      var msg = "<li style=color:'red';> Please enter a valid email</li>";
      var email = this.id('email_id').value;
      if (email.match(emailExp) === null) {
        this.id('email_error').innerHTML = msg;
        this.id('email_error').style.color = "red";
        this.error.push(msg);
      }
    }
  }, {
    key: "clearError",
    value: function clearError() {
      var _this2 = this;
      this.error = []; // empty the array 
      this.data.forEach(function (el) {
        var _iterator2 = _createForOfIteratorHelper(el),
          _step2;
        try {
          var _loop = function _loop() {
            var post = _step2.value;
            if (post.id == 'submit' || post.id == 'token' || post.name == 'token' || post.name == 'submit' || post.name == 'checkbox') {
              return "continue";
            }
            // console.log(post.name)
            _this2.id(post.id).addEventListener('change', function () {
              if (_this2.id("".concat(post.name, "_error"))) {
                _this2.id("".concat(post.name, "_error")).innerHTML = '';
              }
            });
            if (post.value != 'select') {
              _this2.id(post.id).addEventListener('keyup', function () {
                if (_this2.id("".concat(post.name, "_error"))) {
                  _this2.id("".concat(post.name, "_error")).innerHTML = '';
                }
              });
            } else {
              _this2.id(post.id).addEventListener('change', function () {
                if (_this2.id("".concat(post.name, "_error"))) {
                  _this2.id("".concat(post.name, "_error")).innerHTML = '';
                }
              });
            }
          };
          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            var _ret = _loop();
            if (_ret === "continue") continue;
          }
        } catch (err) {
          _iterator2.e(err);
        } finally {
          _iterator2.f();
        }
      });
    }
  }, {
    key: "clearHtml",
    value: function clearHtml() {
      this.data.forEach(function (el) {
        var _iterator3 = _createForOfIteratorHelper(el),
          _step3;
        try {
          for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
            var post = _step3.value;
            if (post.id == 'submit' || post.name == 'submit' || post.name == 'checkbox') {
              continue;
            }
            post.value = "";
          }
        } catch (err) {
          _iterator3.e(err);
        } finally {
          _iterator3.f();
        }
      });
    }

    /**
     *
     * @param {input is the id of the input/ this is an array [as, it, it]} input
     * @param {* this is the max policy and it must be an integer} maxi
     */
  }, {
    key: "realTimeCheckLen",
    value: function realTimeCheckLen(input, maxi) {
      var _this3 = this;
      try {
        var _loop2 = function _loop2(i) {
          var theData = _this3.id("".concat(input[i], "_id"));
          if (theData == "") throw new Error("empty dataInput");
          var max = maxi[i];
          var error = _this3.id("".concat(input[i], "_error"));
          if (theData) theData.maxLength = parseInt(max + 1);
          theData.addEventListener('keyup', function () {
            error.innerHTML = theData.value.length > max ? "You have reach the maximum limit" : "";
            _this3.id("".concat(input[i], "_help")).style.color = 'red';
            _this3.id("".concat(input[i], "_help")).style.fontSize = '10px';
            error.style.color = 'red';
            setTimeout(function () {
              _this3.id("".concat(input[i], "_help")).style.display = 'none';
            }, 5000);
          });
        };
        for (var i = 0; i < input.length; i++) {
          _loop2(i);
        }
      } catch (error) {
        console.log(error.message);
      }
    }

    /**
     * the id for the password error should be password_help
     * the id for your confirm pasword should confirm_password
     * it will return an error message to the password_help input
     */
  }, {
    key: "matchInput",
    value: function matchInput(first, second) {
      var error, firstInput, secondInput;
      error = this.id("".concat(second, "_error"));
      firstInput = this.id(first + '_id');
      secondInput = this.id(second + '_id');
      secondInput.addEventListener('keyup', function () {
        error.innerHTML = secondInput.value !== firstInput.value ? 'Your passwords do not match' : "";
      });
    }
    /**
     *
     * @param {the id of the input you want to inject to/ this is an array} idArray
     * @param {*the comment or questions you want o inject} html
     */
  }, {
    key: "injectData",
    value: function injectData(idArray, html) {
      var idData;
      for (var i = 0; i < idArray.length; i++) {
        idData = this.id(idArray[i]);
        idData.innerHTML = html[i];
      }
    }

    /**
     *
     * @param {this is an id and its value is for duplication} firstInput
     * @param {* another id that accepts the value of the firstInput} takeFirstInput
     */
  }, {
    key: "duplicate",
    value: function duplicate(giveInput, takeInput) {
      var giver, taker;
      giver = this.id(giveInput);
      taker = this.id(takeInput);
      giver.addEventListener('keyup', function () {
        taker.value = giver.value;
      });
    }

    /**
     *
     * @param {current input that is being type to. the value is what will be checked realtime. the id is needed} input
     * @param {* the url to get the info to . example is /search?hint} url
     * @param {enter the id of the output element} output
     */
  }, {
    key: "realTimeServer",
    value: function realTimeServer(input, url, outputId) {
      var theInput, inputVal, output;
      theInput = this.id(input);
      output = this.id(outputId);
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
    }
  }, {
    key: "isChecked",
    value: function isChecked(yesId, noId, hiddenInput) {
      var _this4 = this;
      var checked = function checked() {
        if (_this4.id(yesId).checked) {
          alert('check');
          _this4.id(hiddenInput).innerHTML = 'checked';
        } else if (_this4.id(noId).checked) {
          _this4.id(hiddenInput).innerHTML = 'checked';
        }
      };
      this.id(yesId).addEventListener('click', checked);
      this.id(noId).addEventListener('click', checked);
    }
  }, {
    key: "previousAddress",
    value: function previousAddress() {
      var _this5 = this;
      var timeAddy = this.id('time_at_address_id');
      var prevAddy = this.id('previous_address_class');
      var showPrev = function showPrev() {
        if (timeAddy.value != '3 years+') {
          prevAddy.style.display = 'block';
          _this5.id('previous_address_help').innerHTML = "Please enter your full address: House No, Street Name, Town/City and Post Code";
        } else {
          prevAddy.style.display = 'none';
        }
      };
      timeAddy.addEventListener('change', showPrev);
    }
  }]);
  return FormHelper;
}();


/***/ }),

/***/ "./resources/asset/js/components/api/index.js":
/*!****************************************************!*\
  !*** ./resources/asset/js/components/api/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getAllData: () => (/* binding */ getAllData),
/* harmony export */   postData: () => (/* binding */ postData)
/* harmony export */ });
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }


var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
var URL = "http://olaogun.test/";
// https: //laravel.com/docs/5.4/mix#environment-variables

var getAllData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var response;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          _context.next = 3;
          return axios__WEBPACK_IMPORTED_MODULE_0___default().get("".concat(URL, "allMembers/processApiData2"), config);
        case 3:
          response = _context.sent;
          return _context.abrupt("return", response.data);
        case 7:
          _context.prev = 7;
          _context.t0 = _context["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_context.t0);
          // You can perform additional error handling actions if needed
          throw _context.t0;
        case 11:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 7]]);
  }));
  return function getAllData() {
    return _ref.apply(this, arguments);
  };
}();
var postData = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2(url, object) {
    var response;
    return _regeneratorRuntime().wrap(function _callee2$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 3;
          return axios__WEBPACK_IMPORTED_MODULE_0___default().post(url, object);
        case 3:
          response = _context2.sent;
          console.log(response);
          _context2.next = 11;
          break;
        case 7:
          _context2.prev = 7;
          _context2.t0 = _context2["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_context2.t0);
          // You can perform additional error handling actions if needed
          throw _context2.t0;
        case 11:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 7]]);
  }));
  return function postData(_x, _x2) {
    return _ref2.apply(this, arguments);
  };
}();

/***/ }),

/***/ "./resources/asset/js/components/dataToCheck.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/dataToCheck.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Login: () => (/* binding */ Login),
/* harmony export */   dataToCheckRegister: () => (/* binding */ dataToCheckRegister)
/* harmony export */ });


var dataToCheckRegister = {
  maxLength: {
    id: ['firstName', 'lastName', 'alias', 'spouseName', 'spouseMobile', 'motherMobile', 'fatherMobile', 'fatherName', 'motherName', 'country', 'mobile', 'email', 'occupation'],
    max: [15, 15, 15, 15, 12, 12, 12, 30, 30, 15, 13, 45, 20]
  },
  password: {
    pwd: 'password',
    pwd2: 'confirm_password'
  },
  familyCheck: {
    father: ["fatherYes", "fatherNo"],
    mother: ["motherYes", "motherNo"],
    spouse: ["spouseYes", "spouseNo"]
  }
};
var Login = {
  maxLength: {
    id: ['email', 'password'],
    max: [35, 35]
  }
};

/***/ }),

/***/ "./resources/asset/js/components/helper/autocomplete.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/helper/autocomplete.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autocomplete: () => (/* binding */ autocomplete)
/* harmony export */ });
var autocomplete = function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function (e) {
    var a,
      b,
      i,
      val = this.value;
    /*close any already open lists of autocompleted values*/
    closeAllLists();
    if (!val) {
      return false;
    }
    currentFocus = -1;
    /*create a DIV element that will contain the items (values):*/
    a = document.createElement("DIV");
    a.setAttribute("id", this.id + "autocomplete-list");
    a.setAttribute("class", "autocomplete-items");
    /*append the DIV element as a child of the autocomplete container:*/
    this.parentNode.appendChild(a);
    /*for each item in the array...*/
    for (i = 0; i < arr.length; i++) {
      /*check if the item starts with the same letters as the text field value:*/
      if (arr[i] && arr[i].substr(0, val.length).toUpperCase() === val.toUpperCase()) {
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
          b.addEventListener("click", function (e) {
            /*insert the value for the autocomplete text field:*/
            inp.value = this.getElementsByTagName("input")[0].value;
            /*close the list of autocompleted values,
            (or any other open lists of autocompleted values:*/
            closeAllLists();
          });
          a.appendChild(b);
        }
      }
    }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function (e) {
    var x = document.getElementById(this.id + "autocomplete-list");
    if (x) x = x.getElementsByTagName("div");
    if (e.keyCode == 40) {
      /*If the arrow DOWN key is pressed,
      increase the currentFocus variable:*/
      currentFocus++;
      /*and and make the current item more visible:*/
      addActive(x);
    } else if (e.keyCode == 38) {
      //up
      /*If the arrow UP key is pressed,
      decrease the currentFocus variable:*/
      currentFocus--;
      /*and and make the current item more visible:*/
      addActive(x);
    } else if (e.keyCode == 13) {
      /*If the ENTER key is pressed, prevent the form from being submitted,*/
      e.preventDefault();
      if (currentFocus > -1) {
        /*and simulate a click on the "active" item:*/
        if (x) x[currentFocus].click();
      }
    }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = x.length - 1;
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }
  /*execute a function when someone clicks in the document:*/
  document.addEventListener("click", function (e) {
    closeAllLists(e.target);
  });
};

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
/* harmony export */   removeDiv: () => (/* binding */ removeDiv)
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

/***/ }),

/***/ "./resources/asset/js/components/register/event.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/event.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");

// import { show } from "./onChange"

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('spouse').style.display = "none";

// id('children2').style.display = "none";

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 

var showSpouse = function showSpouse(e) {
  if (e.target.value === "Yes") {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('spouse').style.display = "block";
  } else {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('spouse').style.display = "none";
  }
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus_id').addEventListener('change', showSpouse);
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('register_notify_div').style.display = "none";

/***/ }),

/***/ "./resources/asset/js/components/register/index.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/index.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _smallinput__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./smallinput */ "./resources/asset/js/components/register/smallinput.js");
/* harmony import */ var _event__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./event */ "./resources/asset/js/components/register/event.js");
/* harmony import */ var _onChange__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./onChange */ "./resources/asset/js/components/register/onChange.js");
/* harmony import */ var _processForm__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./processForm */ "./resources/asset/js/components/register/processForm.js");
/* harmony import */ var _mobileNameCheck__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./mobileNameCheck */ "./resources/asset/js/components/register/mobileNameCheck.js");





var currentPs = document.getElementById("password_id");
var confirmPs = document.getElementById("confirm_password_id");
currentPs.setAttribute('autocomplete', 'new-password');
confirmPs.setAttribute('autocomplete', 'new-password');
// secretWd.setAttribute('autocomplete', 'on')

/***/ }),

/***/ "./resources/asset/js/components/register/mobileNameCheck.js":
/*!*******************************************************************!*\
  !*** ./resources/asset/js/components/register/mobileNameCheck.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../api/index */ "./resources/asset/js/components/api/index.js");
/* harmony import */ var _helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../helper/autocomplete */ "./resources/asset/js/components/helper/autocomplete.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_4__);







var getData = (0,_api_index__WEBPACK_IMPORTED_MODULE_2__.getAllData)();
var firstNameData = [];
var fatherName = [];
var mobile = [];
var motherName = [];
var checkEmail = [];
var fName = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("firstName_id").value;
var lName = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("lastName_id").value;

/**
 *
 * @param {* array} baseArray the array to check against ["Banana", "Orange", "Apple", "Mango"];
 * @param {* string || integer} searchElement  the element to search against ("Mango")
 */

var checkExistence = function checkExistence(baseArray, searchElement) {
  if (!Array.isArray(baseArray)) {
    baseArray = []; // Initialize as an array if it's not already
  }

  if (baseArray.includes(searchElement) === false) {
    baseArray.push(searchElement);
  }
};
// Check if getData is resolved
if (getData instanceof Promise) {
  getData.then(function (el) {
    el.forEach(function (element) {
      checkExistence(firstNameData, element.firstName);
      checkExistence(fatherName, element.fatherName);
      checkExistence(motherName, element.motherName);
      checkExistence(mobile, element.mobile);
      checkExistence(checkEmail, element.email);
    });

    // Code that relies on the data obtained from getAllData() can be placed here
  })["catch"](function (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    // Handle the error appropriately
  });
} else {
  console.log('getData is empty or not resolved');
  // Handle the case when getData is empty or not resolved
}

var firstAutoComplete = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("firstName_id");
var fatherAutoComplete = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("fatherName_id");
var motherAutoComplete = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("motherName_id");
firstAutoComplete.setAttribute("autocomplete", "off");
fatherAutoComplete.setAttribute("autocomplete", "off");
motherAutoComplete.setAttribute("autocomplete", "off");

// AUTOCOMPLETE
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__.autocomplete)(firstAutoComplete, firstNameData);
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__.autocomplete)(fatherAutoComplete, fatherName);
(0,_helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__.autocomplete)(motherAutoComplete, motherName);

// CHECK THE MOBILE OF MOTHER AND FATHER

var setInput = function setInput(name, value) {
  var sex = name === "father" ? "him" : "her";
  var genId = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Mobile_error"));
  genId.style.display = "block";
  genId.innerHTML = mobile.includes(value) ? "Great news that your ".concat(name, " is already on the platform") : "<h4><i>Your ".concat(name, " is not on the platform. Do you want us to send ").concat(sex, " a text/email to register to the platform</i>?</h4>").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.checkBox)(name));
  function processRadio() {
    var postObj = {
      mobile: value,
      viewPath: "msg/contactNewMember",
      data: {
        email: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Email_id")).value,
        mobile: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Mobile_id")).value,
        name: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Name_id")).value,
        yourName: "".concat(fName, " ").concat(lName)
      },
      subject: "".concat(fName, " ").concat(lName, " recommended that you join your family network.")
    };
    axios__WEBPACK_IMPORTED_MODULE_4___default().post("/register/contactNewMember", postObj).then(function (response) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.showNotification)("".concat(name, "Mobile_help"), 'is-success', response.data.message);
      genId.innerHTML = "";
    })["catch"](function (error) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.showNotification)("".concat(name, "Mobile_error"), 'is-danger', error.message);
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    });
  }
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Yes")).addEventListener("click", processRadio);
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "No")).addEventListener("click", function () {
    return genId.style.display = "none";
  });
};

// *
// Handle mobile filtering
// for different individuals.*@param { Object }
// event - The event object.*@param { string }
// name - The name of the individual("father", "mother", "spouse").*/

var mobileFilter = function mobileFilter(event, name) {
  try {
    var value = event.target.value;
    if (!value) {
      throw new Error("Number value is empty");
    }
    if (value.length >= 11) {
      setInput(name, value);
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    // Perform additional error handling or logging if needed
  }
};

var fatherMobile = function fatherMobile(event) {
  var setName = "father";
  mobileFilter(event, setName);
};
var motherMobile = function motherMobile(event) {
  var setName = "mother";
  mobileFilter(event, setName);
};
var spouseMobile = function spouseMobile(event) {
  var setName = "spouse";
  mobileFilter(event, setName);
};

// Add event listeners with error handling
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("fatherMobile_id").addEventListener("keyup", function (event) {
  try {
    fatherMobile(event);
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("motherMobile_id").addEventListener("keyup", function (event) {
  try {
    motherMobile(event);
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("spouseMobile_id").addEventListener("keyup", function (event) {
  try {
    spouseMobile(event);
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
});

// create the data for the function below

var checkEmailObj = {
  kidEmailInput: ["kid_email1", "kid_email2", "kid_email3", "kid_email4", "kid_email5", "kid_email6", "kid_email7", "kid_email8", "kid_email9", "kid_email10"],
  kidNameInput: ["kid_name1", "kid_name2", "kid_name3", "kid_name4", "kid_name5", "kid_name6", "kid_name7", "kid_name8", "kid_name9", "kid_name10"],
  siblingEmail: ["sibling_email1", "sibling_email2", "sibling_email3", "sibling_email4", "sibling_email5", "sibling_email6", "sibling_email7", "sibling_email8", "sibling_email9", "sibling_email10"],
  siblingName: ["sibling_name1", "sibling_name2", "sibling_name3", "sibling_name4", "sibling_name5", "sibling_name6", "sibling_name7", "sibling_name8", "sibling_name9", "sibling_name10"]
};

// check if there is a sibling or kids by email

document.onkeydown = function (e) {
  //. use the onclick to get the id of the element that was clicked
  // 2. use event listener to get the email value (if it is not empty)
  // 3. use the email value to check if it is in the array
  // 4. if it is in the array, show the Yes or No Radio
  // 5. click yes to send email to the kid or sibling

  try {
    // create an object with the data to check
    var elementId = e.target.id; // id of the element that was clicked or press down
    var emailInput = e.target.value;

    // this phase checks the id of what is being typed
    if (!elementId) throw new Error("target id is null and empty");
    var chooseEmail = [];
    var chooseName = [];
    var helpHTML = "";
    var errorHTML = ""; // Show error if applicant's email is registered

    // check if id / event.id is either kid or sibling

    // if the elementId indicate that it is a kid, then choosemail inherits all the kids array from the checkEmailObj and vis a versa

    if (checkEmailObj.kidEmailInput.includes(elementId)) {
      chooseEmail = checkEmailObj.kidEmailInput;
      chooseName = checkEmailObj.kidNameInput;
      helpHTML = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(elementId, "_help"));
    } else if (checkEmailObj.siblingEmail.includes(elementId)) {
      chooseEmail = checkEmailObj.siblingEmail;
      chooseName = checkEmailObj.siblingName;
      helpHTML = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(elementId, "_help"));
    }
    var checkFamilyEmail = function checkFamilyEmail(event) {
      //this checks the value of what is being typed

      var emailInput = event.target.value;
      helpHTML.innerHTML = emailInput.length > 5 && emailInput.length < 7 ? "Email may be too small" : "";

      // use the elementid to find the exact email value and name value
      var index = chooseEmail.indexOf(elementId);
      var email = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(chooseEmail[index]);
      var emailValue = email.value;
      var name = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(chooseName[index]);
      var nameValue = name.value;

      // if (!emailValue)
      //     throw new Error("another round of email is empty");
      // if (!nameValue) throw new Error("name is empty");
      // if (!getData.length) throw new Error("data is faulty");

      // checking family email 
      helpHTML.style.display = "block";
      helpHTML.innerHTML = checkEmail.includes(emailInput) ? "Great news! ".concat(nameValue, " is already registered on the platform") : "".concat(nameValue, " is not on the platform.Do you want us to send ").concat(nameValue, " a email to register to the platform ? ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.checkBox)(elementId));

      // send the email to family membersa

      var processKidRadio = function processKidRadio(ev) {
        var postObj = {
          mobile: "",
          // is this needed?
          viewPath: "msg/contactNewMember",
          data: {
            email: emailValue,
            name: nameValue,
            yourName: "".concat(fName, " ").concat(lName)
          },
          subject: "".concat(fName, " ").concat(lName, " recommended that you join your family network.")
        };
        axios__WEBPACK_IMPORTED_MODULE_4___default().post("/register/contactNewMember", postObj).then(function (response) {
          helpHTML.innerHTML = response.data.message;
          setTimeout(function () {
            helpHTML.style.display = "none";
          }, 5000);
        })["catch"](function (error) {
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
        });
      };
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(elementId, "Yes")).addEventListener("click", processKidRadio);
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(elementId, "No")).addEventListener("click", function () {
        return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(elementId, "No")).style.display = "none";
      });
    };
    if (chooseEmail.includes(elementId)) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(elementId).addEventListener("keyup", checkFamilyEmail);
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};
var checkPersonalEmail = function checkPersonalEmail(e) {
  var email = e.target.value;
  ;
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("email_error").innerHTML = checkEmail.includes(email) ? "Hello! ".concat(fName, " you are already registered on the platform") : "";
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('email_id').addEventListener('keyup', checkPersonalEmail);

/***/ }),

/***/ "./resources/asset/js/components/register/onChange.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/register/onChange.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   show: () => (/* binding */ show)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");


// import { getEnvironmentVariable as env} from 'environment-variable-reader'



// let childrenOnchangeValue = 0;
// let childrenOnchangeValue = 0;

var renderHtmlFamily = function renderHtmlFamily(family, no) {
  if (no) {
    var kids_sib = family == "addChildren" ? "kid" : "sibling";
    return "\n        <div class=\"field-body\">\n\n            <div class=\"field\">\n                <p class=\"control is-expanded has-icons-left\">\n                <input type=\"text\" placeholder = \"Enter ".concat(kids_sib, "'s full name - ").concat(no, "\"  name =").concat(kids_sib, "_name").concat(no, " class=\"input input is-medium is-rounded\" id=\"").concat(kids_sib, "_name").concat(no, "\">\n                <span class=\"icon is-small is-left\">\n                    <i class=\"fas fa-user\"></i>\n                </span>\n                </p>\n               \n            </div>\n\n            <div class=\"field\">\n                <p class=\"control is-expanded has-icons-left\">\n                <input type=\"email\" placeholder = \"Enter ").concat(kids_sib, "'s email - ").concat(no, "\" value = \"Please, provide email address\" name=").concat(kids_sib, "_email").concat(no, " class=\"input input is-medium is-rounded\" id=\"").concat(kids_sib, "_email").concat(no, "\">\n                <span class=\"icon is-small is-left\">\n                <i class=\"fas fa-envelope\"></i>\n                </span>\n                <span class=\"icon is-small is-right\">\n                <i class=\"fas fa-check\"></i>\n                </span>\n                </p>\n                 <p class=\"help is-danger\" id=\"").concat(kids_sib, "_email").concat(no, "_help\"></p>\n           </div>\n\n        </div><br>");
  }
};
var show = function show(kids_or_sib) {
  try {
    // what was picked or selected
    var value = event.target.value;

    // childrenOnchangeValue = value;

    var addDiv = kids_or_sib == "kids" ? "addChildren" : "addSiblings";

    // remove the div 
    (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.removeDiv)(addDiv);
    if (value > 0) {
      // create and append the div element 
      var parent = "".concat(kids_or_sib, "_div");
      (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.createAndAppendElement)('div', addDiv, parent);

      // use the loop to generate the number of input
      for (var i = 0; i < value; i++) {
        var no = i + 1;
        var msg = no > 1 ? "Please, enter their names and emails" : "Please, enter the name and email";
        var getSelectHelp = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help"));
        getSelectHelp.innerHTML = msg;
        getSelectHelp.style.fontSize = '1rem';
        var html = renderHtmlFamily(addDiv, no);
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(addDiv).insertAdjacentHTML('afterBegin', html);
      }
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
var onChangeKidAndSiblings = function onChangeKidAndSiblings() {
  var sibInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("siblings_id");
  var kidInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("kids_id");
  kidInput.addEventListener('change', function () {
    return show('kids');
  });
  sibInput.addEventListener('change', function () {
    return show('siblings');
  });
};
onChangeKidAndSiblings();

// inject the country code once one of the country is picked

var injectCountryCode = function injectCountryCode() {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('country_id').addEventListener('change', function (e) {
    var value = e.target.value;
    switch (value) {
      case 'Nigeria':
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('mobile_id').value = "234";
        break;
      case 'UK':
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('mobile_id').value = "44";
        break;
      case 'Canada':
      case 'USA':
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('mobile_id').value = "1";
        break;
      case 'China':
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('mobile_id').value = "86";
        break;
      default:
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('mobile_id').value = "";
    }
  });
};
injectCountryCode();

/***/ }),

/***/ "./resources/asset/js/components/register/processForm.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/register/processForm.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _dataToCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../dataToCheck */ "./resources/asset/js/components/dataToCheck.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_3__);


function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }




var formInput = document.querySelectorAll('.register');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_0__["default"](formInputArr);
(function () {
  try {
    formData.clearError();

    // set the maxlength, check the length of the value, raise error

    formData.realTimeCheckLen(_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.maxLength.id, _dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.maxLength.max);

    // check if password matches real time
    formData.matchInput(_dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.password.pwd, _dataToCheck__WEBPACK_IMPORTED_MODULE_2__.dataToCheckRegister.password.pwd2);
    formData.duplicate('firstName_id', 'alias_id');
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  }
})();
var processFormDataAction = function processFormDataAction(addClass, serverResponse) {
  // display the success information for 10sec
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('register_notify_div').style.display = "block"; // unblock the notification
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('register_notify_div').classList.add(addClass); // add the success class
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('register_notify_div_msg').innerHTML = serverResponse.message; // error element
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('loader').classList.remove('loader'); // remove loader
};

var processFormData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(url, formElement) {
    var form, formEntries, options;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          form = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(formElement);
          formEntries = new FormData(form);
          formEntries["delete"]('submit');
          formEntries["delete"]('checkbox_id');
          options = {
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN'
          };
          _context.next = 7;
          return axios__WEBPACK_IMPORTED_MODULE_3___default().post(url, formEntries, options).then(function (response) {
            // get the api message and output it to the form
            processFormDataAction('is-success', response.data);
            setTimeout(function () {
              //window.location.replace(redirect)
              window.location.replace('register/nextStep');
            }, 5000);

            // it clears all the contents
            //  formData.clearHtml();
          })["catch"](function (error) {
            processFormDataAction('is-danger', error.response.data);
          });
        case 7:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return function processFormData(_x, _x2) {
    return _ref.apply(this, arguments);
  };
}();
var processForm = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2(e) {
    var errorData;
    return _regeneratorRuntime().wrap(function _callee2$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          e.preventDefault();
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('register_notify_div').classList.remove('is-danger'); // remove the danger class from the notification
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('register_notify_div_msg').innerHTML = ""; // empty the error element
          if (!(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('checkbox').checked) {
            _context2.next = 19;
            break;
          }
          // window.location.hash = '#setLoader';
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("setLoader").focus(); // focus on the loader element

          formData.clearError();
          formData.emailVal();
          formData.massValidate();
          if (!(formData.error.length <= 0)) {
            _context2.next = 16;
            break;
          }
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('loader').classList.add('loader');
          _context2.next = 13;
          return processFormData("/register", 'register');
        case 13:
          return _context2.abrupt("return");
        case 16:
          alert('The form cannot be submitted. Please check the errors');
          // console.log(formData.error)
        case 17:
          _context2.next = 22;
          break;
        case 19:
          alert('To continue, you need to agree to the our privacy policy');
          errorData = "To continue, you need to agree to the our privacy policy";
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showNotification)('checkbox_error', 'is-danger', errorData);
        case 22:
          _context2.next = 27;
          break;
        case 24:
          _context2.prev = 24;
          _context2.t0 = _context2["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_context2.t0);
        case 27:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 24]]);
  }));
  return function processForm(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('submit').addEventListener('click', processForm);

/***/ }),

/***/ "./resources/asset/js/components/register/smallinput.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/smallinput.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


/**
 * 
 * @param {*} parentId - id of the element string
 * @param {*} msg - messages to pass - string
 */
var showMsg = function showMsg(parentId) {
  var msg = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "Please, leave blank if not available";
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(parentId).innerHTML = msg;
};
showMsg('fatherMobile_help');
showMsg('motherMobile_help');
showMsg('motherEmail_help');
showMsg('fatherEmail_help');
showMsg('mobile_help', "Nigeria: 2348036517179, UK: 447871717809");
showMsg('password_help', 'Must be 8-20 characters long.');
var lastName = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('lastName_id').value = "OLAOGUN";

/***/ })

}]);