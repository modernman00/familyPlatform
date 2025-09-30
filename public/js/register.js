(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["register"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FormHelper)
/* harmony export */ });
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");


function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var FormHelper = /*#__PURE__*/function () {
  function FormHelper(data) {
    _classCallCheck(this, FormHelper);
    if (!Array.isArray(data)) throwError('data must be an array of form elements');
    this.data = data;
    this.error = [];
    this.result = 0;
  }
  return _createClass(FormHelper, [{
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
    key: "validateField",
    value: function validateField(value) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'general';
      var regexes = {
        email: /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/
        // Add more regexes as needed
      };
      return type === 'email' ? regexes.email.test(value) : value.trim() !== '';
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
            var postName = post.name.replace('_', ' ');
            var asterisk = "*";

            // rid it off the submit and token
            if (['submit', 'button', 'showPassword_id', 'g-recaptcha-response', 'cancel', 'token', 'checkbox_id'].includes(post.name) || ['button'].includes(post.id) || ['button'].includes(post.type)) return;
            // check if there is no value

            if (['spouseName', 'spouseMobile', 'spouseEmail', 'fatherMobile', 'fatherEmail', 'motherMobile', 'maidenName', 'motherEmail'].includes(post.name)) {
              // post.value is not prpvided if it is not provided 
              post.value = post.value === "" ? "Not Provided" : post.value;
            }
            if (post.value === '' || post.value === 'select') {
              if (!_this.validateField(post.value)) {
                if (errMsg) {
                  var _post$placeholder;
                  errMsg.innerHTML = "".concat((_post$placeholder = post.placeholder) !== null && _post$placeholder !== void 0 ? _post$placeholder : asterisk, " cannot be left empty");
                  errMsg.style.color = 'red';
                }
                _this.error.push("".concat(postName.toUpperCase(), " cannot be left empty"));
                _this.result = false;
              }
            }
            if (post.name === 'email' && !_this.validateField(post.value, 'email')) {
              _this.error.push('<li style="color: red;">Please enter a valid email</li>');
              if (errMsg) errMsg.innerHTML = '* Please enter a valid email';
              _this.result = false;
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
      this.error = []; // Empty the error array

      // Define a function to clear error messages for a given input element
      var clearErrorForElement = function clearErrorForElement(elementName) {
        var errorElement = _this2.id("".concat(elementName, "_error"));
        if (errorElement) {
          errorElement.innerHTML = '';
        }
      };
      this.data.forEach(function (el) {
        var _iterator2 = _createForOfIteratorHelper(el),
          _step2;
        try {
          var _loop = function _loop() {
            var post = _step2.value;
            var id = post.id,
              name = post.name,
              value = post.value;

            // Skip certain input types
            if (['submit', 'button', 'token', 'checkbox'].includes(id) || ['token', 'submit'].includes(name)) {
              return 1; // continue
            }
            var the_id = _this2.id(id);
            if (the_id) {
              // Add keyup event listener to clear errors for non-select inputs
              the_id.addEventListener('keyup', function () {
                if (value !== 'select') {
                  clearErrorForElement(name);
                }
              });
            } else {
              console.error("Element with ID '".concat(id, "' with post name '").concat(post.name, "' not found."));
            }

            // Add change event listener to clear error message
            the_id.addEventListener('change', function () {
              clearErrorForElement(name);
            });
          };
          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            if (_loop()) continue;
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
          if (theData === null || theData === undefined || theData === "") {
            throw new Error("Element with ID '".concat(input[i], "_id' not found or is empty"));
          }
          var max = maxi[i];
          var error = _this3.id("".concat(input[i], "_error"));
          theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
          theData.addEventListener('keyup', function () {
            error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
            var help = _this3.id("".concat(input[i], "_help"));
            help.style.color = 'red';
            help.style.fontSize = '10px';
            error.style.color = 'red';
            setTimeout(function () {
              help.style.display = 'none';
            }, 5000);
          });
        };
        for (var i = 0; i < input.length; i++) {
          _loop2(i);
        }
      } catch (error) {
        console.error(error.message);
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
}();


/***/ }),

/***/ "./resources/asset/js/components/api/index.js":
/*!****************************************************!*\
  !*** ./resources/asset/js/components/api/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   fetchEmailData: () => (/* binding */ fetchEmailData),
/* harmony export */   getAllData: () => (/* binding */ getAllData),
/* harmony export */   postData: () => (/* binding */ postData)
/* harmony export */ });
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }


var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
var URL = "https://olaogun.test/";
// https: //laravel.com/docs/5.4/mix#environment-variables

var getAllData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var response, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          _context.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"].get("".concat(URL, "allMembers/processApiData2"), config);
        case 1:
          response = _context.v;
          return _context.a(2, response.data);
        case 2:
          _context.p = 2;
          _t = _context.v;
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_t);
          // You can perform additional error handling actions if needed
          throw _t;
        case 3:
          return _context.a(2);
      }
    }, _callee, null, [[0, 2]]);
  }));
  return function getAllData() {
    return _ref.apply(this, arguments);
  };
}();
var postData = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(url, object) {
    var response, _t2;
    return _regenerator().w(function (_context2) {
      while (1) switch (_context2.p = _context2.n) {
        case 0:
          _context2.p = 0;
          _context2.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"].post(url, object);
        case 1:
          response = _context2.v;
          console.log(response);
          _context2.n = 3;
          break;
        case 2:
          _context2.p = 2;
          _t2 = _context2.v;
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_t2);
          // You can perform additional error handling actions if needed
          throw _t2;
        case 3:
          return _context2.a(2);
      }
    }, _callee2, null, [[0, 2]]);
  }));
  return function postData(_x, _x2) {
    return _ref2.apply(this, arguments);
  };
}();
var fetchEmailData = function fetchEmailData() {
  // Make a GET request and return the promise
  return axios__WEBPACK_IMPORTED_MODULE_0__["default"].get("".concat(URL, "getEmails")).then(function (response) {
    // Extract the data from the response
    var data = response.data;
    // Return the data or do further processing
    return data;
  })["catch"](function (error) {
    // Handle any errors that occur during the request
    console.error('Error fetching data:', error);
    throw error; // Rethrow the error to be handled by the caller
  });
};

/***/ }),

/***/ "./resources/asset/js/components/dataToCheck.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/dataToCheck.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Login: () => (/* binding */ Login),
/* harmony export */   dataToCheckRegister: () => (/* binding */ dataToCheckRegister)
/* harmony export */ });


var dataToCheckRegister = {
  maxLength: {
    id: ['firstName', 'lastName', 'spouseName', 'spouseMobile', 'motherMobile', 'fatherMobile', 'fatherName', 'motherName', 'country', 'mobile', 'email', 'occupation'],
    max: [15, 15, 15, 12, 12, 12, 30, 30, 15, 13, 45, 20]
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

/***/ "./resources/asset/js/components/global.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/global.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkElements: () => (/* binding */ checkElements),
/* harmony export */   checkManyElements: () => (/* binding */ checkManyElements),
/* harmony export */   date2String: () => (/* binding */ date2String),
/* harmony export */   hideElement: () => (/* binding */ hideElement),
/* harmony export */   id: () => (/* binding */ id),
/* harmony export */   idInnerHTML: () => (/* binding */ idInnerHTML),
/* harmony export */   idValue: () => (/* binding */ idValue),
/* harmony export */   log: () => (/* binding */ log),
/* harmony export */   manipulateAttribute: () => (/* binding */ manipulateAttribute),
/* harmony export */   msgException: () => (/* binding */ msgException),
/* harmony export */   qSel: () => (/* binding */ qSel),
/* harmony export */   qSelAll: () => (/* binding */ qSelAll),
/* harmony export */   qSelInnerHTML: () => (/* binding */ qSelInnerHTML),
/* harmony export */   qSelValue: () => (/* binding */ qSelValue),
/* harmony export */   showElement: () => (/* binding */ showElement),
/* harmony export */   showError: () => (/* binding */ showError),
/* harmony export */   showNotification: () => (/* binding */ showNotification),
/* harmony export */   warningSign: () => (/* binding */ warningSign),
/* harmony export */   write: () => (/* binding */ write)
/* harmony export */ });

var id = function id(_id) {
  return document.getElementById(_id);
};
var idValue = function idValue(id) {
  return id(id).value;
};
var idInnerHTML = function idInnerHTML(id) {
  return id(id).innerHTML;
};
var warningSign = "\u26A0"; // danger warning sign

var qSel = function qSel(name) {
  return document.querySelector(name);
};
var qSelAll = function qSelAll(name) {
  return document.querySelectorAll(name);
};
var qSelValue = function qSelValue(name) {
  return qSel(name).value;
};
var qSelInnerHTML = function qSelInnerHTML(name) {
  return qSel(name).innerHTML;
};
var log = function log(id) {
  var identifier = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  console.log(' start' + "  " + identifier);
  console.log(id);
  console.log(' end' + "  " + identifier);
};
var write = function write(input) {
  return document.write(input);
};
var hideElement = function hideElement(elementId) {
  id(elementId).style.display = "none";
};
var showElement = function showElement(elementId) {
  id(elementId).style.display = "block";
};
var manipulateAttribute = function manipulateAttribute(idName, removeOrSet, attributeType) {
  var nameValue = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  if (removeOrSet === "remove") {
    id(idName).removeAttribute(attributeType);
  } else {
    id(idName).setAttribute(attributeType, nameValue);
  }
};
var date2String = function date2String(date) {
  return new Date().toDateString(date);
};
var showError = function showError(e) {
  log(e.message, " ERROR MESSAGE"); // "null has no properties"
  log(e.name, " ERROR NAME"); // "TypeError"
  log(e.fileName, " ERROR FILENAME"); // "Scratchpad/1"
  log(e.lineNumber, " ERROR LINENUMBER"); // 2

  log(e.stack);
};
var msgException = function msgException(errorMessage) {
  throw new Error(errorMessage);
};

/**
 * 
 * @param {*} elementId - element id
 * @param {*} addClass either a success or danger class (green or red)
 * @param {*} message - html message to convey success or failure
 * @param {*} timer - timer for the message to disappear- default is 5 secs
 */
var showNotification = function showNotification(elementId, addClass, message) {
  var timer = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 5000;
  // display the success information for 10sec
  id("".concat(elementId)).style.display = "block"; // unblock the notification
  id("".concat(elementId)).classList.add(addClass); // add the success class
  id("".concat(elementId)).innerHTML = message; // error element
  id('loader').classList.remove('loader'); // remove loader

  setTimeout(function () {
    id("".concat(elementId)).style.backgroundColor = "";
    id("".concat(elementId)).style.color = "";
    id("".concat(elementId)).innerHTML = "";
  }, timer);
};

// Function to check for elements and render if they exist
var checkElements = function checkElements(idOrClass, classString) {
  var theFunction = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  var doesElementExist = idOrClass === "id" ? id(classString) : qSel(classString);
  // Check if elements exist before calling render function
  if (doesElementExist.length) {
    theFunction(doesElementExist);
  }
};
var checkManyElements = function checkManyElements(idOrClass, classString) {
  var theFunction = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  var doesElementExist = idOrClass === "id" ? id(classString) : qSelAll(classString);
  // Check if elements exist before calling render function
  if (doesElementExist.length > 0) {
    theFunction(doesElementExist);
  }
};

/***/ }),

/***/ "./resources/asset/js/components/helper/autocomplete.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/helper/autocomplete.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
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

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autoCompleter: () => (/* binding */ autoCompleter),
/* harmony export */   checkBox: () => (/* binding */ checkBox),
/* harmony export */   checkBox2: () => (/* binding */ checkBox2),
/* harmony export */   convertFormData: () => (/* binding */ convertFormData),
/* harmony export */   createAndAppendElement: () => (/* binding */ createAndAppendElement),
/* harmony export */   distinctValue: () => (/* binding */ distinctValue),
/* harmony export */   isChecked: () => (/* binding */ isChecked),
/* harmony export */   loaderIcon: () => (/* binding */ loaderIcon),
/* harmony export */   loaderIconBootstrap: () => (/* binding */ loaderIconBootstrap),
/* harmony export */   loaderIconBulma: () => (/* binding */ loaderIconBulma),
/* harmony export */   matchInput: () => (/* binding */ matchInput),
/* harmony export */   matchRegex: () => (/* binding */ matchRegex),
/* harmony export */   realTimeCheckLen: () => (/* binding */ realTimeCheckLen),
/* harmony export */   removeDiv: () => (/* binding */ removeDiv),
/* harmony export */   showResponse: () => (/* binding */ showResponse),
/* harmony export */   toSentenceCase: () => (/* binding */ toSentenceCase)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! autocompleter */ "./node_modules/autocompleter/autocomplete.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(autocompleter__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");


function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }



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

/**
 * Converts a string to sentence case.
 *
 * Sentence case is a string where the first letter of each word is capitalized, and the rest of the letters are in lowercase.
 *
 * @param {string} str The string to convert to sentence case.
 * @returns {string} A new string in sentence case.
 */
var toSentenceCase = function toSentenceCase(str) {
  return str.toLowerCase() // Convert the string to lowercase
  .split(' ') // Split the string into words
  .map(function (word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
  }) // Capitalize the first letter of each word
  .join(' '); // Join the words back into a string
};
var convertFormData = function convertFormData(formId) {
  var formInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.qSelAll)(formId);
  var formInputArr = Array.from(formInput);
  return new _FormHelper__WEBPACK_IMPORTED_MODULE_2__["default"](formInputArr);
};
var showResponse = function showResponse(theId, message, status) {
  var responseEl = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(theId);
  var col = status ? 'green' : 'red';
  responseEl.innerHTML = message;
  responseEl.style.color = 'green';
  responseEl.style.backgroundColor = col;
  responseEl.style.color = 'white';
  setTimeout(function () {
    responseEl.innerHTML = '';
  }, 5000); // Disappear after 5 seconds
};

/**
   *
   * @param {input is the id of the input/ this is an array [as, it, it]} input
   * @param {* this is the max policy and it must be an integer} maxi
   */

var realTimeCheckLen = function realTimeCheckLen(input, maxi) {
  try {
    var _loop = function _loop(i) {
      var theData = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(input[i], "_id"));
      if (theData === null || theData === undefined || theData === "") {
        throw new Error("Element with ID '".concat(input[i], "_id' not found or is empty"));
      }
      var max = maxi[i];
      var error = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(input[i], "_error"));
      theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
      theData.addEventListener('keyup', function () {
        error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
        var help = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(input[i], "_help"));
        help.style.color = 'red';
        help.style.fontSize = '10px';
        error.style.color = 'red';
        setTimeout(function () {
          help.style.display = 'none';
        }, 5000);
      });
    };
    for (var i = 0; i < input.length; i++) {
      _loop(i);
    }
  } catch (error) {
    console.error(error.message);
  }
};

/***/ }),

/***/ "./resources/asset/js/components/kidsAndSiblings.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/kidsAndSiblings.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   processKidsSiblings: () => (/* binding */ processKidsSiblings)
/* harmony export */ });
/* harmony import */ var _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../data/kidsSibling */ "./resources/asset/js/data/kidsSibling.js");
/* harmony import */ var _components_global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../components/global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");




var processKidsSiblings = function processKidsSiblings(checkEmailExists, firstName, lastName) {
  var famCode = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  var chooseEmail = [];
  var chooseName = [];
  var helpHTML = "";
  // let errorHTML = ""; // Show error if applicant's email is registered

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

      // if the elementId indicate that it is a kid, then choosemail inherits all the kids array from the checkEmailObj and vis a versa

      if (_data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidEmailInput.includes(elementId)) {
        chooseEmail = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidEmailInput;
        chooseName = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidNameInput;
        helpHTML = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "_help"));
      } else if (_data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingEmail.includes(elementId)) {
        chooseEmail = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingEmail;
        chooseName = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingName;
        helpHTML = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "_help"));
      }
      var checkFamilyEmail = function checkFamilyEmail() {
        //this checks the value of what is being typed

        helpHTML.innerHTML = emailInput.length > 5 && emailInput.length < 7 ? "Email may be too small" : "";

        // use the elementid to find the exact email value and name value
        var index = chooseEmail.indexOf(elementId);
        var email = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)(chooseEmail[index]);
        var emailValue = email.value;
        var name = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)(chooseName[index]);
        var nameValue = name.value;

        // if (!emailValue)
        //     throw new Error("another round of email is empty");
        // if (!nameValue) throw new Error("name is empty");
        // if (!getData.length) throw new Error("data is faulty");
        // checking family email 
        helpHTML.style.display = "block";
        helpHTML.innerHTML = checkEmailExists.includes(emailInput) ? "Great news! ".concat(nameValue, " is already registered on the platform") : "".concat(nameValue, " is not on the platform.Do you want us to send ").concat(nameValue, " a email to register to the platform ? ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.checkBox)(elementId));

        // send the email to family membersa

        var setFamCode;
        var famCodeElement = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)('famCode_id');
        if (famCodeElement) {
          setFamCode = famCodeElement.value;
        } else {
          // Handle the case where the element is not found or not yet loaded
          setFamCode = famCode; // Use a default value (famCode) or handle the situation accordingly
        }
        var processKidRadio = function processKidRadio() {
          var postObj = {
            mobile: "",
            // is this needed?
            viewPath: "msg/contactNewMember",
            data: {
              email: emailValue,
              name: nameValue,
              yourName: "".concat(firstName, " ").concat(lastName),
              familyCode: setFamCode
            },
            subject: "".concat(firstName, " ").concat(lastName, " Wants You: Experience the Magic of your Family Network Today!")
          };
          axios__WEBPACK_IMPORTED_MODULE_3__["default"].post("/register/contactNewMember", postObj).then(function (response) {
            helpHTML.innerHTML = response.data.message;
            setTimeout(function () {
              helpHTML.style.display = "none";
            }, 5000);
          })["catch"](function (error) {
            (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
          });
        };
        (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "Yes")).addEventListener("click", processKidRadio);
        (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "No")).addEventListener("click", function () {
          return (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "No")).style.display = "none";
        });
      };
      if (chooseEmail.includes(elementId)) {
        checkFamilyEmail();

        // id(elementId).addEventListener("keyup", checkFamilyEmail);
      }
    } catch (error) {
      (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
    }
  };
};

/***/ }),

/***/ "./resources/asset/js/components/register/event.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/event.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   hideElement: () => (/* binding */ hideElement),
/* harmony export */   showSpouse: () => (/* binding */ showSpouse)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");

var hideElement = function hideElement(elementId) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(elementId).style.display = "none";
};
var showElement = function showElement(elementId) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(elementId).style.display = "block";
};
var showMaidenName = function showMaidenName() {
  var gender = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('gender_id');
  var maritalStatus = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus_id');
  if (gender.value === "Female" && maritalStatus.value === "Yes") {
    showElement('maidenName_div');
  } else {
    hideElement('maidenName_div');
  }
};
var showSpouse = function showSpouse() {
  var maritalStatus = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus_id').value;
  if (maritalStatus === "Married") {
    showElement('spouse');
  } else {
    hideElement('spouse');
  }
};

// Add event listeners

(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('maritalStatus_id').addEventListener('change', showSpouse);
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('gender_id').addEventListener('change', showMaidenName);

// Hide elements by default
hideElement('spouse');
hideElement('maidenName_div');

// Other initializations
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('register_notify_div').style.display = "none";

// inject student after employment status value is student 

var injectStudent = function injectStudent() {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('employmentStatus_id').addEventListener('change', function (e) {
    var value = e.target.value;
    if (value === "Student") {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('occupation_id').value = 'Student';
    }
  });
};
injectStudent();

/***/ }),

/***/ "./resources/asset/js/components/register/familyCode.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/familyCode.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }

var button = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("btnFamCode");
btnFamCode.addEventListener("click", function () {
  try {
    if ((0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('surname_id').value !== "") {
      var uniqueNumber = Date.now();
      var uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber) / 10000000);
      var getSurname = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('surname_id').value;
      var firstFourLetters = getSurname.substring(0, 4);
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createCode').value = "".concat(firstFourLetters.toUpperCase()).concat(uniqueNumber1);
      btnFamCode.disabled = true;
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("surname_error").innerHTML = error.messages;
  }
});

// Get references to the HTML output and the copy icon

var copyIcon = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('copyIcon');
var htmlOutputDiv = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createFamCode');
var htmlOutput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createCode');
copyIcon.addEventListener('click', /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(e) {
    var range, selection, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          copyIcon.innerHTML = "";
          _context.p = 1;
          e.preventDefault();

          // check if the family code has been generated 
          if (!htmlOutput.value) {
            _context.n = 5;
            break;
          }
          if (!(navigator.clipboard && navigator.clipboard.writeText)) {
            _context.n = 3;
            break;
          }
          _context.n = 2;
          return navigator.clipboard.writeText(htmlOutput.value);
        case 2:
          _context.n = 4;
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
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('famCode_id').value = htmlOutput.value;
          _context.n = 6;
          break;
        case 5:
          copyIcon.innerHTML = "copy";
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('famCode_id').value = "";
          alert('Please generate the family code first');
        case 6:
          _context.n = 8;
          break;
        case 7:
          _context.p = 7;
          _t = _context.v;
          console.error('Unable to copy the HTML output: ', _t);
        case 8:
          return _context.a(2);
      }
    }, _callee, null, [[1, 7]]);
  }));
  return function (_x) {
    return _ref.apply(this, arguments);
  };
}());

/***/ }),

/***/ "./resources/asset/js/components/register/html/kids_Sibling.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/html/kids_Sibling.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtmlFamily: () => (/* binding */ renderHtmlFamily)
/* harmony export */ });
var renderHtmlFamily = function renderHtmlFamily(family, no) {
  if (no) {
    var kids_sib = family == "addChildren" ? "kid" : "sibling";
    var optionsHtml = "\n      <option value='Choose'>Choose</option>\n      <option value='With Spouse'>With Spouse</option>\n      <option value='Not With Spouse'>Not With Spouse</option>\n    ";
    if (family === "addSiblings") {
      optionsHtml = "\n                <option value='Choose'>Choose</option>\n                <option value='Same_Mother_Father'>Same Mother & Father</option>\n                <option value='Same_Father'>Only Same Father</option>\n                <option value='Same_Mother'>Only Same Mother</option>";
    }
    return "\n            <div class=\"field-body\">\n                <div class=\"field\">\n                    <p class=\"control is-expanded\">\n                        <span class=\"select is-normal is-success is-fullwidth\">\n                            <select name=\"".concat(kids_sib, "_option").concat(no, "\" id=\"").concat(kids_sib, "_option").concat(no, "\">\n                                ").concat(optionsHtml, "\n                            </select>\n                        </span>\n                    </p>\n                </div>\n\n                <div class=\"field\">\n                    <p class=\"control is-expanded\">\n                        <input type=\"text\" placeholder = \"Enter ").concat(kids_sib, "'s full name - ").concat(no, "\"  name =").concat(kids_sib, "_name").concat(no, " class=\"input input is-normal \" id=\"").concat(kids_sib, "_name").concat(no, "\">\n                    </p>\n                </div>          \n\n                <div class=\"field\">\n                    <p class=\"control is-expanded has-icons-left\">\n                        <input type=\"email\" placeholder = \"Enter ").concat(kids_sib, "'s email - ").concat(no, "\" value = \"Please, provide email address\" name=").concat(kids_sib, "_email").concat(no, " class=\"input input is-normal \" id=\"").concat(kids_sib, "_email").concat(no, "\">\n\n                        <span class=\"icon is-small is-left\">\n                            <i class=\"fas fa-envelope\"></i>\n                        </span>\n                    </p>\n                    <p class=\"help is-danger\" id=\"").concat(kids_sib, "_email").concat(no, "_help\"></p>\n                </div>\n\n            </div>");
  }
};

/***/ }),

/***/ "./resources/asset/js/components/register/html/notification.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/html/notification.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   processFormDataAction: () => (/* binding */ processFormDataAction)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");

var processFormDataAction = function processFormDataAction(addClass, serverResponse) {
  // display the success information for 10sec
  notificationDiv.style.display = "block"; // unblock the notification
  notificationDiv.classList.add(addClass); // add the success class
  notificationMsg.innerHTML = serverResponse.message; // error element
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('loader').classList.remove('loader'); // remove loader
  notificationDiv.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  });
};

/***/ }),

/***/ "./resources/asset/js/components/register/index.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/index.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _smallinput__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./smallinput */ "./resources/asset/js/components/register/smallinput.js");
/* harmony import */ var _event__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./event */ "./resources/asset/js/components/register/event.js");
/* harmony import */ var _onChangeKidSibling__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./onChangeKidSibling */ "./resources/asset/js/components/register/onChangeKidSibling.js");
/* harmony import */ var _processForm__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./processForm */ "./resources/asset/js/components/register/processForm.js");
/* harmony import */ var _mobileNameCheck__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./mobileNameCheck */ "./resources/asset/js/components/register/mobileNameCheck.js");
/* harmony import */ var _injectCountryCode__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./injectCountryCode */ "./resources/asset/js/components/register/injectCountryCode.js");
/* harmony import */ var _familyCode__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./familyCode */ "./resources/asset/js/components/register/familyCode.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/register/modal.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_modal__WEBPACK_IMPORTED_MODULE_7__);








var currentPs = document.getElementById("password_id");
var confirmPs = document.getElementById("confirm_password_id");
currentPs.setAttribute('autocomplete', 'new-password');
confirmPs.setAttribute('autocomplete', 'new-password');
// secretWd.setAttribute('autocomplete', 'on')

/***/ }),

/***/ "./resources/asset/js/components/register/injectCountryCode.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/injectCountryCode.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


// inject the country code once one of the country is picked

// Object to map countries to country codes

var countryCodes = {
  Afghanistan: "93",
  Albania: "355",
  Algeria: "213",
  Andorra: "376",
  Angola: "244",
  "Antigua and Barbuda": "1-268",
  Argentina: "54",
  Armenia: "374",
  Australia: "61",
  Austria: "43",
  Azerbaijan: "994",
  Bahamas: "1-242",
  Bahrain: "973",
  Bangladesh: "880",
  Barbados: "1-246",
  Belarus: "375",
  Belgium: "32",
  Belize: "501",
  Benin: "229",
  Bhutan: "975",
  Bolivia: "591",
  "Bosnia and Herzegovina": "387",
  Botswana: "267",
  Brazil: "55",
  Brunei: "673",
  Bulgaria: "359",
  "Burkina Faso": "226",
  Burundi: "257",
  Cambodia: "855",
  Cameroon: "237",
  Canada: "1",
  "Cape Verde": "238",
  "Central African Republic": "236",
  Chad: "235",
  Chile: "56",
  China: "86",
  Colombia: "57",
  Comoros: "269",
  "Congo (Brazzaville)": "242",
  "Congo (Kinshasa)": "243",
  "Costa Rica": "506",
  "Côte d'Ivoire": "225",
  Croatia: "385",
  Cuba: "53",
  Cyprus: "357",
  "Czech Republic": "420",
  Denmark: "45",
  Djibouti: "253",
  Dominica: "1-767",
  "Dominican Republic": "1-809, 1-829, 1-849",
  "East Timor": "670",
  Ecuador: "593",
  Egypt: "20",
  "El Salvador": "503",
  "Equatorial Guinea": "240",
  Eritrea: "291",
  Estonia: "372",
  Ethiopia: "251",
  Fiji: "679",
  Finland: "358",
  France: "33",
  Gabon: "241",
  Gambia: "220",
  Georgia: "995",
  Germany: "49",
  Ghana: "233",
  Greece: "30",
  Grenada: "1-473",
  Guatemala: "502",
  Guinea: "224",
  "Guinea-Bissau": "245",
  Guyana: "592",
  Haiti: "509",
  Honduras: "504",
  Hungary: "36",
  Iceland: "354",
  India: "91",
  Indonesia: "62",
  Iran: "98",
  Iraq: "964",
  Ireland: "353",
  Israel: "972",
  Italy: "39",
  Jamaica: "1-876",
  Japan: "81",
  Jordan: "962",
  Kazakhstan: "7",
  Kenya: "254",
  Kiribati: "686",
  "North Korea": "850",
  "South Korea": "82",
  Kosovo: "383",
  Kuwait: "965",
  Kyrgyzstan: "996",
  Laos: "856",
  Latvia: "371",
  Lebanon: "961",
  Lesotho: "266",
  Liberia: "231",
  Libya: "218",
  Liechtenstein: "423",
  Lithuania: "370",
  Luxembourg: "352",
  Macedonia: "389",
  Madagascar: "261",
  Malawi: "265",
  Malaysia: "60",
  Maldives: "960",
  Mali: "223",
  Malta: "356",
  "Marshall Islands": "692",
  Mauritania: "222",
  Mauritius: "230",
  Mexico: "52",
  Micronesia: "691",
  Moldova: "373",
  Monaco: "377",
  Mongolia: "976",
  Montenegro: "382",
  Morocco: "212",
  Mozambique: "258",
  Myanmar: "95",
  Namibia: "264",
  Nauru: "674",
  Nepal: "977",
  Netherlands: "31",
  "New Zealand": "64",
  Nicaragua: "505",
  Niger: "227",
  Nigeria: "234",
  Norway: "47",
  Oman: "968",
  Pakistan: "92",
  Palau: "680",
  Panama: "507",
  "Papua New Guinea": "675",
  Paraguay: "595",
  Peru: "51",
  Philippines: "63",
  Poland: "48",
  Portugal: "351",
  Qatar: "974",
  Romania: "40",
  Russia: "7",
  Rwanda: "250",
  "Saint Kitts and Nevis": "1-869",
  "Saint Lucia": "1-758",
  "Saint Vincent and the Grenadines": "1-784",
  Samoa: "685",
  "San Marino": "378",
  "Sao Tome and Principe": "239",
  "Saudi Arabia": "966",
  Senegal: "221",
  Serbia: "381",
  Seychelles: "248",
  "Sierra Leone": "232",
  Singapore: "65",
  Slovakia: "421",
  Slovenia: "386",
  "Solomon Islands": "677",
  Somalia: "252",
  "South Africa": "27",
  "South Sudan": "211",
  Spain: "34",
  "Sri Lanka": "94",
  Sudan: "249",
  Suriname: "597",
  Swaziland: "268",
  Sweden: "46",
  Switzerland: "41",
  Syria: "963",
  Taiwan: "886",
  Tajikistan: "992",
  Tanzania: "255",
  Thailand: "66",
  Togo: "228",
  Tonga: "676",
  "Trinidad and Tobago": "1-868",
  Tunisia: "216",
  Turkey: "90",
  Turkmenistan: "993",
  Tuvalu: "688",
  Uganda: "256",
  Ukraine: "380",
  "United Arab Emirates": "971",
  "United Kingdom": "44",
  "United States": "1",
  Uruguay: "598",
  Uzbekistan: "998",
  Vanuatu: "678",
  "Vatican City": "379",
  Venezuela: "58",
  Vietnam: "84",
  Yemen: "967",
  Zambia: "260",
  Zimbabwe: "263"
};
var injectCountryCode = function injectCountryCode() {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('country_id').addEventListener('change', function (e) {
    var value = e.target.value;
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('mobile_id').value = countryCodes[value] || '';
  });
};
injectCountryCode();

/***/ }),

/***/ "./resources/asset/js/components/register/mobileNameCheck.js":
/*!*******************************************************************!*\
  !*** ./resources/asset/js/components/register/mobileNameCheck.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../api/index */ "./resources/asset/js/components/api/index.js");
/* harmony import */ var _helper_autocomplete__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../helper/autocomplete */ "./resources/asset/js/components/helper/autocomplete.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");








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
  genId.innerHTML = mobile.includes(value) || email.includes(value) ? "Great news that your ".concat(name, " is already on the platform") : "<h4><i>Your ".concat(name, " is not on the platform. Do you want us to send ").concat(sex, " a text/email to register to the platform</i>?</h4>").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.checkBox)(name));
  function processRadio() {
    var postObj = {
      mobile: value,
      viewPath: "msg/contactNewMember",
      data: {
        email: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Email_id")).value,
        mobile: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Mobile_id")).value,
        name: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(name, "Name_id")).value,
        familyCode: (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("familyCode_id").value,
        yourName: "".concat(fName, " ").concat(lName)
      },
      subject: "".concat(fName, " ").concat(lName, " Wants You: Experience the Magic of your Family Network Today!")
    };
    axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/register/contactNewMember", postObj).then(function (response) {
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

// check if there is a sibling or kids by email
(0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_5__.processKidsSiblings)(checkEmail, fName, lName);
var checkPersonalEmail = function checkPersonalEmail(e) {
  var email = e.target.value;
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("email_error").innerHTML = checkEmail.includes(email) ? "YOU HAVE ALREADY REGISTERED ON THE PLATFORM" : "";
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('email_id').addEventListener('keyup', checkPersonalEmail);

/***/ }),

/***/ "./resources/asset/js/components/register/modal.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/modal.js ***!
  \*********************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  // Functions to open and close a modal
  function openModal($el) {
    $el.classList.add('is-active');
  }
  function closeModal($el) {
    $el.classList.remove('is-active');
  }
  function closeAllModals() {
    (document.querySelectorAll('.modal') || []).forEach(function ($modal) {
      closeModal($modal);
    });
  }

  // Add a click event on buttons to open a specific modal
  (document.querySelectorAll('.js-modal-trigger') || []).forEach(function ($trigger) {
    var modal = $trigger.dataset.target;
    var $target = document.getElementById(modal);
    $trigger.addEventListener('click', function () {
      openModal($target);
    });
  });

  // Add a click event on various child elements to close the parent modal
  (document.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete, .modal-card-foot .button') || []).forEach(function ($close) {
    var $target = $close.closest('.modal');
    $close.addEventListener('click', function () {
      closeModal($target);
    });
  });

  // Add a keyboard event to close all modals
  document.addEventListener('keydown', function (event) {
    if (event.code === 'Escape') {
      closeAllModals();
    }
  });
});

/***/ }),

/***/ "./resources/asset/js/components/register/onChangeKidSibling.js":
/*!**********************************************************************!*\
  !*** ./resources/asset/js/components/register/onChangeKidSibling.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   show: () => (/* binding */ show)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _html_kids_Sibling__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html/kids_Sibling */ "./resources/asset/js/components/register/html/kids_Sibling.js");


// import { getEnvironmentVariable as env} from 'environment-variable-reader'




// let childrenOnchangeValue = 0;
// let childrenOnchangeValue = 0;

var show = function show(kids_or_sib, event) {
  try {
    // what was picked or selected
    var value = event.target.value;

    // childrenOnchangeValue = value;
    var addDiv = kids_or_sib == "kids" ? "addChildren" : "addSiblings";

    // remove the div 
    (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.removeDiv)(addDiv);
    if (value == 0) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help")).innerHTML = "";
    }
    if (value > 0) {
      // create and append the div element 
      var parent = "".concat(kids_or_sib, "_div");
      (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.createAndAppendElement)('div', addDiv, parent);
      // use the loop to generate the number of input
      for (var i = 0; i < value; i++) {
        var no = i + 1;
        var msg = no > 1 ? "Please, enter their names and emails below" : "Please, enter the name and email below";
        var getSelectHelp = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help"));
        getSelectHelp.innerHTML = msg;
        getSelectHelp.style.fontSize = '1rem';
        getSelectHelp.style.color = '#fc2003';
        var html = (0,_html_kids_Sibling__WEBPACK_IMPORTED_MODULE_2__.renderHtmlFamily)(addDiv, no);
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
  kidInput.addEventListener('change', function (event) {
    return show('kids', event);
  });
  sibInput.addEventListener('change', function (event) {
    return show('siblings', event);
  });
};
onChangeKidAndSiblings();

/***/ }),

/***/ "./resources/asset/js/components/register/processForm.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/register/processForm.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _dataToCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../dataToCheck */ "./resources/asset/js/components/dataToCheck.js");
/* harmony import */ var _html_notification__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./html/notification */ "./resources/asset/js/components/register/html/notification.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");


function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }





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

    // formData.duplicate('firstName_id', 'alias_id')
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  }
})();
var notificationDiv = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('register_notify_div');
var notificationMsg = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('register_notify_div_msg');
var processFormData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(url, formElement, token) {
    var form, formEntries, options;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.n) {
        case 0:
          form = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(formElement);
          formEntries = new FormData(form);
          formEntries["delete"]('submit');
          formEntries["delete"]('checkbox_id');
          options = {
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN'
          };
          _context.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post(url, formEntries, options).then(function (response) {
            // get the api message and output it to the form
            (0,_html_notification__WEBPACK_IMPORTED_MODULE_3__.processFormDataAction)('is-success', response.data);
            setTimeout(function () {
              //window.location.replace(redirect)
              window.location.replace('register/nextStep');
            }, 5000);

            // it clears all the contents
            //  formData.clearHtml();
          })["catch"](function (error) {
            (0,_html_notification__WEBPACK_IMPORTED_MODULE_3__.processFormDataAction)('is-danger', error.response.data);
          });
        case 1:
          return _context.a(2);
      }
    }, _callee);
  }));
  return function processFormData(_x, _x2, _x3) {
    return _ref.apply(this, arguments);
  };
}();
window.processForm = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(token) {
    var emailError, errorData, _t;
    return _regenerator().w(function (_context2) {
      while (1) switch (_context2.p = _context2.n) {
        case 0:
          _context2.p = 0;
          // e.preventDefault();
          notificationDiv.classList.remove('is-danger'); // remove the danger class from the notification
          notificationMsg.innerHTML = ""; // empty the error element
          if (!(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('checkbox').checked) {
            _context2.n = 4;
            break;
          }
          // window.location.hash = '#setLoader';
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("setLoader").focus(); // focus on the loader element

          formData.clearError();
          formData.emailVal();
          formData.massValidate();

          // CHECK IF EMAIL HAS NOT BEEN REGISTERED ERROR IS NULL
          emailError = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("email_error").innerHTML;
          if (!(formData.error.length <= 0 && emailError == "")) {
            _context2.n = 2;
            break;
          }
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("setLoader").classList.add('loader');
          _context2.n = 1;
          return processFormData("/register", 'register');
        case 1:
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("setLoader").classList.remove('loader');
          return _context2.a(2);
        case 2:
          alert("The form cannot be submitted. Please check the errors");
          notificationMsg.innerHTML = "".concat(_global__WEBPACK_IMPORTED_MODULE_1__.warningSign, " Check the errors");
          notificationDiv.style.display = "block";
          notificationDiv.style.backgroundColor = "Red";
          notificationDiv.style.color = "White";
          //notificationMsg.scrollIntoView({ behavior: 'smooth', block: 'start' });
          notificationDiv.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
          //By using the js scrollIntoView method on the error element, the browser will automatically scroll to make the error message the focus point after the form is submitted.
        case 3:
          _context2.n = 5;
          break;
        case 4:
          alert('To continue, you need to agree to the our privacy policy');
          errorData = "To continue, you need to agree to the our privacy policy";
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showNotification)('checkbox_error', 'is-danger', errorData);
        case 5:
          _context2.n = 7;
          break;
        case 6:
          _context2.p = 6;
          _t = _context2.v;
          (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(_t);
        case 7:
          return _context2.a(2);
      }
    }, _callee2, null, [[0, 6]]);
  }));
  return function (_x4) {
    return _ref2.apply(this, arguments);
  };
}();

// id('submit').addEventListener('click', processForm)
// id('submit').addEventListener('click', processForm)

/***/ }),

/***/ "./resources/asset/js/components/register/smallinput.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/smallinput.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


/**
 * 
 * @param {*} Id - id of the element string
 * @param {*} msg - messages to pass - string
 */
var showMsg = function showMsg(Id) {
  var msg = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "Please, leave blank if not available";
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(Id).innerHTML = msg;
};

// const href = "<a href='/createFamilyCode' target='_blank'>here</a>"
var famCodeButton = "<button type='button' class='js-modal-trigger' data-target='modal-familyCode' id='triggerFamCode'>here</button>";
showMsg('fatherMobile_help');
showMsg('motherMobile_help');
showMsg('motherEmail_help');
showMsg('fatherEmail_help');
showMsg('mobile_help', "Nigeria: 2348036517179, UK: 447871717809");
showMsg('password_help', 'Must be 8-20 characters long.');
showMsg("famCode_help", "Ask a family member who already registered for the code if none then create one for your family ".concat(famCodeButton));

// const lastName = id('lastName_id').value = "OLAOGUN"

// TRIGGER THE MODAL

// Functions to open and close a modal
function openModal($el) {
  $el.classList.add('is-active');
}
function closeModal($el) {
  $el.classList.remove('is-active');
}
var trigger = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('triggerFamCode');
var close = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('modal-close-code');
var modalClass = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('modal-familyCode');
trigger.addEventListener('click', function () {
  openModal(modalClass);
});
close.addEventListener('click', function () {
  closeModal(modalClass);
});

/***/ }),

/***/ "./resources/asset/js/data/kidsSibling.js":
/*!************************************************!*\
  !*** ./resources/asset/js/data/kidsSibling.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkEmailObj: () => (/* binding */ checkEmailObj)
/* harmony export */ });
var checkEmailObj = {
  kidEmailInput: ["kid_email1", "kid_email2", "kid_email3", "kid_email4", "kid_email5", "kid_email6", "kid_email7", "kid_email8", "kid_email9", "kid_email10"],
  kidNameInput: ["kid_name1", "kid_name2", "kid_name3", "kid_name4", "kid_name5", "kid_name6", "kid_name7", "kid_name8", "kid_name9", "kid_name10"],
  siblingEmail: ["sibling_email1", "sibling_email2", "sibling_email3", "sibling_email4", "sibling_email5", "sibling_email6", "sibling_email7", "sibling_email8", "sibling_email9", "sibling_email10"],
  siblingName: ["sibling_name1", "sibling_name2", "sibling_name3", "sibling_name4", "sibling_name5", "sibling_name6", "sibling_name7", "sibling_name8", "sibling_name9", "sibling_name10"]
};

/***/ })

}]);
//# sourceMappingURL=register.js.map