"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/profilePage"],{

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
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");


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
            if (post.id == 'submit' || post.name == 'token' || post.name == 'submit' || post.name == 'checkbox') {
              return "continue";
            }
            _this2.id(post.id).addEventListener('change', function () {
              _this2.id("".concat(post.name, "_error")).innerHTML = '';
            });
            if (post.value != 'select') {
              _this2.id(post.id).addEventListener('keyup', function () {
                _this2.id("".concat(post.name, "_error")).innerHTML = '';
              });
            } else {
              _this2.id(post.id).addEventListener('change', function () {
                _this2.id("".concat(post.name, "_error")).innerHTML = '';
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

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "autoCompleter": () => (/* binding */ autoCompleter),
/* harmony export */   "checkBox": () => (/* binding */ checkBox),
/* harmony export */   "checkBox2": () => (/* binding */ checkBox2),
/* harmony export */   "createAndAppendElement": () => (/* binding */ createAndAppendElement),
/* harmony export */   "distinctValue": () => (/* binding */ distinctValue),
/* harmony export */   "isChecked": () => (/* binding */ isChecked),
/* harmony export */   "loaderIcon": () => (/* binding */ loaderIcon),
/* harmony export */   "loaderIconBootstrap": () => (/* binding */ loaderIconBootstrap),
/* harmony export */   "loaderIconBulma": () => (/* binding */ loaderIconBulma),
/* harmony export */   "matchInput": () => (/* binding */ matchInput),
/* harmony export */   "matchRegex": () => (/* binding */ matchRegex),
/* harmony export */   "removeDiv": () => (/* binding */ removeDiv)
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

/***/ "./resources/asset/js/components/helper/http.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/helper/http.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "checkCookie": () => (/* binding */ checkCookie),
/* harmony export */   "getApiData": () => (/* binding */ getApiData),
/* harmony export */   "getCookie": () => (/* binding */ getCookie),
/* harmony export */   "getMultipleApiData": () => (/* binding */ getMultipleApiData),
/* harmony export */   "postFormData": () => (/* binding */ postFormData),
/* harmony export */   "setCookie": () => (/* binding */ setCookie)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios-retry */ "./node_modules/axios-retry/lib/esm/index.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }



// import Cookies from 'js-cookie'

(0,axios_retry__WEBPACK_IMPORTED_MODULE_2__["default"])((axios__WEBPACK_IMPORTED_MODULE_1___default()), {
  retries: 3
});

/**
 * 
 * @param {the url to post the data to} url 
 * @param {* the id or class of the form} formElement 
 * @param {* the redirect to another page /code or /admin/register} redirect 
 NOTICE:::Make sure you set the notification id as the formId_notification
 */
var postFormData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(url, formId) {
    var redirect,
      css,
      notificationId,
      processFormDataAction,
      addClassByCSS,
      form,
      formEntries,
      options,
      _args = arguments;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          redirect = _args.length > 2 && _args[2] !== undefined ? _args[2] : null;
          css = _args.length > 3 && _args[3] !== undefined ? _args[3] : null;
          notificationId = "".concat(formId, "_notification"); // the notification function
          processFormDataAction = function processFormDataAction(addClass, data) {
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(notificationId).style.display = "block"; // unblock the notification

            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(notificationId).classList.add(addClass); // add the success class

            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = data; // error element

            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('loader').classList.remove('loader'); // remove loader
          };
          addClassByCSS = function addClassByCSS(theCss, status) {
            if (theCss === "W3css") {
              return status == 'green' ? "w3-green" : "w3-red";
            } else if (theCss === 'bulma') {
              return status == 'green' ? "is-success" : "is-danger";
            } else {
              return status == 'green' ? "is-success" : "is-danger";
            }
          }; // extract the form entries
          form = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(formId);
          formEntries = new FormData(form);
          formEntries["delete"]('submit');
          formEntries["delete"]('checkbox_id');
          // formEntries.delete('token')
          options = {
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN'
          }; // AXIOS POST FUNCTIONALITY
          _context.next = 12;
          return axios__WEBPACK_IMPORTED_MODULE_1___default().post(url, formEntries, options).then(function (response) {
            // TO DECIDE ON THE NOTIFICATION
            var theClass = addClassByCSS(css, 'green');
            processFormDataAction(theClass, response.data.message);

            //set timer to redirect to the homepage
            if (redirect) {
              setTimeout(function () {
                window.location.assign(redirect);
              }, 2000);
            }
            // it clears all the contents
            formData.clearHtml();
          })["catch"](function (error) {
            var theClass = addClassByCSS(css, 'red');

            // if (error.response.data.message === "We do not recognise what you are doing") {
            //     window.location.assign('/login')
            // }

            processFormDataAction(theClass, error.response.data.message);
          });
        case 12:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return function postFormData(_x, _x2) {
    return _ref.apply(this, arguments);
  };
}();

/**
 * 
 * @param { the url you want to get} URL 
 * @returns 
 // now we can use that data from the outside!
axiosTest()
    .then(data => {
        response.json({ message: 'Request received!', data })
    })
    .catch(err => console.log(err))
 */

var getApiData = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2(URL) {
    var token,
      config,
      fetch,
      _args2 = arguments;
    return _regeneratorRuntime().wrap(function _callee2$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          token = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : null;
          _context2.prev = 1;
          config = {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          };
          _context2.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_1___default().get(URL, config);
        case 5:
          fetch = _context2.sent;
          return _context2.abrupt("return", fetch.data);
        case 9:
          _context2.prev = 9;
          _context2.t0 = _context2["catch"](1);
          return _context2.abrupt("return", _context2.t0);
        case 12:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[1, 9]]);
  }));
  return function getApiData(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
var getMultipleApiData = /*#__PURE__*/function () {
  var _ref3 = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee3(url1, url2) {
    var token,
      config,
      fetch,
      _args3 = arguments;
    return _regeneratorRuntime().wrap(function _callee3$(_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          token = _args3.length > 2 && _args3[2] !== undefined ? _args3[2] : null;
          _context3.prev = 1;
          config = {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          };
          _context3.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_1___default().all([axios__WEBPACK_IMPORTED_MODULE_1___default().get(url1, config), axios__WEBPACK_IMPORTED_MODULE_1___default().get(url2, config)]);
        case 5:
          fetch = _context3.sent;
          return _context3.abrupt("return", fetch);
        case 9:
          _context3.prev = 9;
          _context3.t0 = _context3["catch"](1);
          return _context3.abrupt("return", _context3.t0);
        case 12:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[1, 9]]);
  }));
  return function getMultipleApiData(_x4, _x5) {
    return _ref3.apply(this, arguments);
  };
}();

/**
 * 
 * @param { name} cname 
 * @param {* value} cvalue 
 * @param {* no of days 365} exdays 
 */
var setCookie = function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
};
var getCookie = function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
};
var checkCookie = function checkCookie() {
  var user = getCookie("username");
  if (user != "") {
    alert("Welcome again " + user);
  } else {
    user = prompt("Please enter your name:", "");
    if (user != "" && user != null) {
      setCookie("username", user, 365);
    }
  }
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/allEvents.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/allEvents.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");
/* harmony import */ var _profilePage_html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../profilePage/html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_4__);







try {
  (pusher_js__WEBPACK_IMPORTED_MODULE_4___default().logToConsole) = true;
  var pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_4___default())('d1f1e43f3d8afb028a1f', {
    cluster: 'eu'
  });

  // getApiData()

  var newLikeCounterVal = 0;
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };

  // let serverConnection = new EventSource("/post/getAllPost/update") 

  // const Pusher = (pushData) => {

  // // open the server sent event

  //     const update = (e) => {

  //         const data = JSON.parse(e.data)
  //         // log(data)

  //         if( appendNewPost(pushData)) {
  //             serverConnection.close()
  //         }

  //     }

  //     serverConnection.addEventListener("updatePost", update)

  // }

  var showTheComment = function showTheComment(commentResponse) {
    var idDiv = "showComment".concat(commentResponse.post_no);
    var commentHtml = (0,_profilePage_html__WEBPACK_IMPORTED_MODULE_2__.commentHTML)(commentResponse);
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idDiv).insertAdjacentHTML('afterbegin', commentHtml);
  };

  // CLICK EVENT get the comment and like button from the document
  document.onclick = function (e) {
    var elementId = e.target.id;
    var postId = e.target.name;
    if (elementId.includes("likeButton")) {
      // replace button with Counter to get the span id 
      var likeCounterId = elementId.replace('Button', 'Counter');
      var likeCounterVal = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(likeCounterId).innerHTML;

      // get the post like using the post id

      (0,_helper_http__WEBPACK_IMPORTED_MODULE_1__.getApiData)("/profileCard/getLikes?postId=".concat(postId, "&count=").concat(likeCounterVal));

      // add one to the result 
      newLikeCounterVal = parseInt(likeCounterVal) + 1;
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(likeCounterId).innerHTML = newLikeCounterVal;

      // Make the comment form to appear onclick
    } else if (elementId.includes("initComment")) {
      var commentFormId = elementId.replace('init', 'form');
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(commentFormId).style.display = "block";

      // Submit function for comment using POST API
    } else if (elementId.includes("submitComment")) {
      //elementId == submitComment511

      // 0.5 LISTEN FOR THE SUBMIT EVENT
      // 0.7 GET THE COMMENT FORM ID 
      // 1. POST SENDS BACK THE LAST COMMENT NO POSTED
      // 2.  USE IT TO GET THE NEW COMMENT
      // 3. ADD THE NEW COMMENT TO THE COMMENT DIV 
      // get the specific form id
      e.preventDefault();

      // 0.7
      var idForm = elementId.replace("submit", "form"); //idForm == formComment511

      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idForm).style.display = "none"; // make the comment form disappear

      // extract the form entries
      var form = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idForm);
      var formEntries = new FormData(form);

      // if the comment form input is empty. Get the input id and check 
      var inputComment = idForm.replace("form", "input");
      var idInputComment = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputComment);
      if (idInputComment.value == null || idInputComment.value == "") {
        alert("Please enter a comment");
      } else {
        // 1.
        axios__WEBPACK_IMPORTED_MODULE_3___default().post('/postCommentProfile', formEntries, options).then(function (response) {
          // 2. note. message returns the new post_no from the database

          axios__WEBPACK_IMPORTED_MODULE_3___default().get("/member/pp/comment/byNumber?commentNo=".concat(response.data.message)).then(function (res) {
            // 3.

            showTheComment(res.data.message);
          });
        })["catch"](function (error) {
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(error);
        });
      }
      // SUBMIT THE POST
    } else if (elementId.includes("submitPost")) {
      // LISTEN TO THE SUBMIT EVENT 
      // 2. GET THE FORM id
      // 3. POST TO THE SERVER USING AXIOS POST
      //4. GET THE POST FROM THE SERVER USING AXIOS GET 
      //5. APPEND THE NEW POST TO THE POSTCARD 

      // 2. 
      var formExtra = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formPostMessageModal');
      var formData = new FormData(formExtra);

      // 3. 
      axios__WEBPACK_IMPORTED_MODULE_3___default().post("/member/profilePage/post", formData, options).then(function (response) {
        //  4. 
        axios__WEBPACK_IMPORTED_MODULE_3___default().get("/post/getAllPost/byNumber?postNo=".concat(response.data.message)).then(function (res) {
          // 5. 

          //  log(res.data)
          (0,_profilePage_html__WEBPACK_IMPORTED_MODULE_2__.appendNewPost)(res.data.message);

          // Pusher(res.data.message)
        });
        // Enable pusher logging - don't include this in production

        var channel = pusher.subscribe('my-channel');
        channel.bind('updatePost', function (data) {
          // log("checking1")
          // log(data.message);
          // log("checking")
        });
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'none';
      });
    }
  };
} catch (e) {
  showError(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/createEvent.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/createEvent.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_2__);





var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_1__["default"](formInputArr);
var displayNone = function displayNone() {
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'none';
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('cancelModal').addEventListener('click', displayNone);
var eventHtml = function eventHtml(data) {
  return "<p class='eventInfo'><strong>RSVP: </strong> ".concat(data.firstName, " ").concat(data.lastName, "</p>\n            <p class='eventInfo'><strong>Event: </strong>").concat(data.eventName, "</p>\n            <p class='eventInfo'><strong>Date: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.date2String)(data.eventDate), "</p>\n            <p class='eventInfo'><strong>Type: </strong>").concat(data.eventType, "</p>\n            <p class='eventInfo'><strong>Description: </strong> ").concat(data.eventDescription, "</p>\n            <input type='hidden' name='event_no' id='event").concat(data.no, "' value='").concat(data.no, "'>\n            \n           <hr>");
};
var checkEventAndAdd = function checkEventAndAdd(data) {
  var appendEvent = eventHtml(data);
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventList').insertAdjacentHTML('afterbegin', appendEvent);
};
var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};
var process = function process(e) {
  try {
    e.preventDefault();
    // id('eventModalForm_notification').classList.remove('w3-red') // remove the danger class from the notification - may not be needed
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = ""; // may not be needed
    formData.massValidate();
    // log(formData.error)
    if (formData.error.length <= 0) {
      // get the form data
      var eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventModalForm');
      var eventFormEntries = new FormData(eventForm);
      // post the form data to the database and get the last posted event no
      axios__WEBPACK_IMPORTED_MODULE_2___default().post("/member/profilePage/event", eventFormEntries, options).then(function (response) {
        // use the event no to get the last event from the database
        axios__WEBPACK_IMPORTED_MODULE_2___default().get("/member/getEventByNo?eventNo=".concat(response.data.message)).then(function (res) {
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(res.data);
          // add new event real time
          checkEventAndAdd(res.data.message);
        });
      });
      displayNone();

      // window.location.replace("/member/profilePage")
    } else {
      alert('The form cannot be submitted. Please check the errors');
      formData.clearError();
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitEventModal').addEventListener('click', process);

/***/ }),

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "allPost": () => (/* binding */ allPost),
/* harmony export */   "appendNewPost": () => (/* binding */ appendNewPost),
/* harmony export */   "commentHTML": () => (/* binding */ commentHTML),
/* harmony export */   "html": () => (/* binding */ html)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


var timeAgo = function timeAgo(x) {
  return (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x);
};
var name = function name(fullName) {
  return "<h6 id=\"fullName\"><b>".concat(fullName, "</b> </h6>");
};
var postedAt = function postedAt(date) {
  return "<div class=\"timeago postTimeCal w3-right w3-opacity\"  datetime='".concat(date.date_created, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date.date_created), "'> ").concat(timeAgo(date.post_time), "</div>");
};
var nameImgTiming = function nameImgTiming(data) {
  var img = data.img ? "/img/profile/".concat(data.img) : "/avatar/avatarF.png";
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(data.img, "&pID=").concat(data.post_no, "&path=profile&id=").concat(data.id, "\"> <img src=").concat(img, " alt=\"img\" class=\"w3-left w3-circle w3-margin-right postImg\" style=\"width:60px\">\n        </a>\n        ").concat(postedAt(data), " ").concat(name(data.fullName));
};
var commentForm = function commentForm(data) {
  return " <p id=\"formComment".concat(data.post_no, "_notification\"></p>\n\n  <form action=\"/postCommentProfile\" method=\"post\" id=\"formComment").concat(data.post_no, "\" style=\"display:none\" enctype=\"multipart/form-data\">\n\n    <input name='post_no' type=\"hidden\" name=\"").concat(data.post_no, "\" value=").concat(data.post_no, " />\n\n    <input class=\"w3-input w3-border w3-round-large inputComment\" type=\"text\" placeholder=\"Write a comment\"\n      id=\"inputComment").concat(data.post_no, "\" value = \"\" name='comment'>\n\n    <br>\n\n    <button type='submit' id=\"submitComment").concat(data.post_no, "\" class=\"w3-button w3-green submitComment\">Submit</button><br><br>\n  </form>");
};
var button = function button(data) {
  return "<button type=\"button\" id=\"likeButton".concat(data.post_no, "\" name=\"").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-green w3-margin-bottom\">\n    <em class=\"fa fa-thumbs-up\"></em>\n    \xA0Like <b><span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">").concat(data.post_likes, "</span></b>\n  </button>\n\n   <button type=\"button\" id=\"initComment").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-theme-d2 w3-margin-bottom\"><em class=\"fa fa-comment\"></em> Comment </button>\n    ");
};
var showPostImg = function showPostImg(data) {
  var postImg = [];
  for (var i = 0; i < 6; i++) {
    var images = 'post_img' + i;
    if (data[images]) {
      postImg.push(data[images]);
    }
  }
  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    return "<a href=\"/profilepage/img?dir=img&pics=".concat(imgElement, "&pID=").concat(postNo, "&path=post\"> <div class=\"w3-half\">\n            <img src=\"/img/post/").concat(imgElement, "\" style=\"width:100%\" alt=\"images").concat(i, "\"\n              class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat(i, "\">\n          </div>\n        </a>");
  };
  return "<div class=\"w3-row-padding\" style=\"margin:0 -16px\">\n\n      ".concat(postImg.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }), "\n        <br>\n      </div>");
};
var html = function html(el) {
  var comment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin\"><br>\n\n      ".concat(nameImgTiming(el), "\n\n    <hr class=\"w3-clear\">\n\n    <p class=\"postFont\"> ").concat(el.postMessage, " </p>\n\n     ").concat(showPostImg(el), "\n\n    ").concat(button(el), "\n\n    ").concat(commentForm(el), "\n\n    <div id = 'showComment").concat(el.post_no, "'>\n\n      ").concat(showComment(comment), "\n      \n    </div><br>\n  </div>");
};
var allPost = function allPost(el, commentData) {
  if (!el) {
    return false;
  }
  var postNo = parseInt(el.post_no);
  var filterComment = commentData.filter(function (comm) {
    return postNo === parseInt(comm.post_no);
  }); // filter the comment to an array

  var postHtml = html(el, filterComment);
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('beforeend', postHtml);
};
var appendNewPost = function appendNewPost(el) {
  if (!el) {
    return false;
  }
  var commentForm1 = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  var inputComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  var submitComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  if (!commentForm1 || !inputComment || !submitComment) {
    var appendHTML = html(el);
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('afterbegin', appendHTML);
  }
};
var commentHTML = function commentHTML(data) {
  var imgURL = data.img ? data.img : data.profileImg;
  var img = imgURL ? "/img/profile/".concat(imgURL) : "/avatar/avatarF.png";
  return "<div class=\"w3-ul w3-border\" id=\"comment".concat(data.comment_no, "\" name='commentDiv'>\n      <div class=\"w3-container commentDiv\">\n      <img src=\"").concat(img, "\" alt=\"Avatar\" class=\"w3-left w3-circle w3-margin-right commentImg\" style=\"width:60px; height:60px\">\n       <p class=\"w3-right w3-opacity commentTiming\" datetime='").concat(data.date_created, "' title='").concat(data.date_created, "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(data.date_created), " </p> \n         <p class=\"commentFont\"> ").concat(data.comment, "</p>\n    </div>\n</div>");
};
var showComment = function showComment(comment) {
  if (!comment) {
    return "<div class=\"w3-ul w3-border\" id=\"comment\" name=\"commentDiv\"></div>";
  } // only run if there is comment

  return comment.map(function (commentElement) {
    return commentHTML(commentElement);
  });
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/img.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/img.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePics').addEventListener('click', function () {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formProfilePics').style.display = "block";
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/index.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/index.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _loadPost__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./loadPost */ "./resources/asset/js/components/profilePage/loadPost.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/profilePage/modal.js");
/* harmony import */ var _img__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./img */ "./resources/asset/js/components/profilePage/img.js");
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/profilePage/allEvents.js");
/* harmony import */ var _createEvent__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./createEvent */ "./resources/asset/js/components/profilePage/createEvent.js");








/***/ }),

/***/ "./resources/asset/js/components/profilePage/loadPost.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/loadPost.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _profilePage_html__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../profilePage/html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_4__);






// set an empty array
try {
  var state = {
    post: [],
    comment: []
  };
  var serverConnection = new EventSource("/post/getAllPost/update"); // open the server sent event

  // 2. get the data from the database to set the inital data
  var postData = (0,_helper_http__WEBPACK_IMPORTED_MODULE_2__.getMultipleApiData)("/post/getAllPost", '/member/pp/comment');
  postData.then(function (response) {
    state.post = response[0].data.message;
    state.comment = response[1].data.message;
    state.post.map(function (data) {
      return (0,_profilePage_html__WEBPACK_IMPORTED_MODULE_1__.allPost)(data, state.comment);
    }); // show all the post and comments

    // // let serverConnection = new EventSource("/post/getAllPost/update") // open the server sent event

    var updateComment = function updateComment(e) {
      var commentData = JSON.parse(e.data);
      var newData = state.comment.some(function (com) {
        return com.comment_no === commentData.comment_no;
      }); // check if the comment no does not already exist

      if (!newData) {
        // if it is not available, add to the data state
        state.comment.push(commentData);
      }
    };
    serverConnection.addEventListener("updateComment", function (e) {
      return updateComment(e);
    });
    var updatePost = function updatePost(e) {
      if (e.origin != "http://olaogun.dev.com") {
        throw new Error("What is your origin?");
      }
      if (e.data) {
        var newPostData = JSON.parse(e.data);
        var newData = state.post.some(function (el) {
          return el.post_no === newPostData.post_no;
        }); // check if the post no does not already exist

        if (!newData) {
          // if it is not available, add to the data state
          state.post.push(newPostData);
        }
      }
      return state.post.map(function (ele) {
        return (0,_profilePage_html__WEBPACK_IMPORTED_MODULE_1__.appendNewPost)(ele);
      });
    };
    serverConnection.addEventListener("updatePost", function (e) {
      return updatePost(e);
    });

    // // serverConnection.close()

    // AUTOMATICALLY UPDATE TIMESTAMP

    var updatePostTiming = document.querySelectorAll(".timeago");
    var updateCommentTiming = document.querySelectorAll(".commentTiming");
    (0,timeago_js__WEBPACK_IMPORTED_MODULE_3__.render)(updatePostTiming);
    (0,timeago_js__WEBPACK_IMPORTED_MODULE_3__.render)(updateCommentTiming);
  })["catch"](function (err) {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(err);
  });
} catch (error) {
  //  console.log(error)
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/modal.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/modal.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");




// import { postFormData } from "../helper/http"

try {
  // NEW MESSAGE MODAL
  var showModal = function showModal() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'block';
  };

  // CREATE EVENT MODAL
  var showEvent = function showEvent() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'block';
  };

  //EVENT ACTION
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createEvent').addEventListener('click', showEvent);
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('postMsg').addEventListener('click', showModal);

  // handle post message
} catch (e) {
  console.log(e.message);
}

/***/ })

}]);