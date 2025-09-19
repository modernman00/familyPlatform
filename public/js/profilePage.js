(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["profilePage"],{

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
/* harmony export */   deleteNotification: () => (/* binding */ deleteNotification),
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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }

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
var yourId = localStorage.getItem('requesterId');
var famCode = localStorage.getItem('requesterFamCode');

// delete notification 
var deleteNotification = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(elementId) {
    var senderId, elementData, data, notificationHTML, url, response, newValues;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.n) {
        case 0:
          // Extract the user ID from the target ID
          senderId = elementId.replace("deleteNotification", "notificationBar");
          elementData = id(elementId);
          data = elementData.getAttribute("data-id"); // change the background of the clicked element 
          notificationHTML = id(senderId); // Make sure required variables are defined before using them
          if (typeof yourId === 'undefined' || typeof famCode === 'undefined') {
            msgException("Required parameters (yourId or famCode) are not defined");
          }
          url = "/removeNotification/".concat(yourId, "/").concat(famCode, "/").concat(data);
          _context.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"].put(url);
        case 1:
          response = _context.v;
          if (response.data.message === "Notification marked as read") {
            // remove a html element with notificationBar after 2 mins 
            notificationHTML.remove();

            // reduce the notification count as you have deleted the notification
            newValues = parseInt(sessionStorage.getItem('notificationCount') - 1);
            id('notification_count').innerHTML = newValues;
          } else {
            msgException("Error removing notification" + " " + response.data.message);
          }
        case 2:
          return _context.a(2);
      }
    }, _callee);
  }));
  return function deleteNotification(_x) {
    return _ref.apply(this, arguments);
  };
}();

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

/***/ "./resources/asset/js/components/helper/images.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/helper/images.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showImageFileUploadFn: () => (/* binding */ showImageFileUploadFn)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


// use this in conjunction with the file 
var showImageFileUploadFn = function showImageFileUploadFn(uploadBtn, inputId, fileName) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(uploadBtn).addEventListener('click', function () {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputId).click();
  });
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputId).addEventListener('change', function () {
    var fileNames = Array.from(this.files).map(function (file) {
      return file.name;
    });
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(fileName).innerText = fileNames.join(', ');
  });
};

/***/ }),

/***/ "./resources/asset/js/components/navbar.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/navbar.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToNotificationTab: () => (/* binding */ addToNotificationTab),
/* harmony export */   increaseNotificationCount: () => (/* binding */ increaseNotificationCount)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");




// const timeAgo = (x) => format(x)

// import { html } from './profilePage/html';

// Update notification badge
function updateNotificationBadge(change) {
  var badge = document.querySelector('.notification-badge');
  var count = parseInt(badge.textContent);
  count += change;
  if (count <= 0) {
    badge.style.display = 'none';
  } else {
    badge.textContent = count;
    badge.style.display = 'flex';
  }
}
var postAgoNotification = function postAgoNotification(date) {
  return "\n  <div class=\"notification_timeago w3-left w3-opacity\" datetime='".concat(date, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "\n  </div>");
};
// this is the notification htnl 
var notificationHTML = function notificationHTML(data) {
  // generate random numbers to make the notification unique

  var randomNumber = Math.floor(100 + Math.random() * 900);
  return "<a id = \"notificationBar".concat(data.sender_id).concat(randomNumber, "\" data-id=\"").concat(data.sender_id, "\" class=\"w3-bar-item w3-button notification_real_time linkRequestCard w3-padding-16\">\n\n        ").concat(postAgoNotification(data.created_at), "  - \n        <b> ").concat(data.notification_type, "</b> -\n        ").concat(data.notification_name, " -\n        ").concat(data.notification_content, " -\n        ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(data.sender_name), "\n        <button type = \"submit\" class='w3-button-small w3-round w3-hover-grey w3-border-blue' data-id=\"").concat(data.sender_id, "\" id=\"deleteNotification").concat(data.sender_id).concat(randomNumber, "\"> delete</button>\n  </a>\n\n  ");
};

// CLICK FUNCTION ON THE NOTIFICATION BAR THAT TAKES ONE TO THE FRIEND REQUEST CARD

var increaseNotificationCount = function increaseNotificationCount() {
  var currentNotificationCount = parseInt(sessionStorage.getItem('notificationCount')) + 1;
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = currentNotificationCount;
};
var addToNotificationTab = function addToNotificationTab(data) {
  return (0,_global__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab').insertAdjacentHTML('afterbegin', notificationHTML(data));
};
var yourId = localStorage.getItem('requesterId');
var famCode = localStorage.getItem('requesterFamCode');
var notificationURL = "/member/notifications/id/".concat(yourId, "/").concat(famCode);

// get all the notification and display them 
// they are already filtered by famCode and id 
// for the family request, connection is done by id
// for events -birthday etc, the connection is the famCode 
// so linked notification will be either where id matches or famcode matches

axios__WEBPACK_IMPORTED_MODULE_3__["default"].get(notificationURL).then(function (res) {
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

///member/notifications

/***/ }),

/***/ "./resources/asset/js/components/profilePage/allEvents.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/allEvents.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");


function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }


try {
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };

  // CLICK EVENT get the comment and like button from the document
  document.addEventListener('click', /*#__PURE__*/function () {
    var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(e) {
      var elementId, postId, likeCounterId, likeCounterVal, encodedLikeCounterVal, commentFormId, idForm, form, formEntries, inputComment, idInputComment, formExtra, formData, requesterFamCodeValue, response, friendRequestSection, _t;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.p = _context.n) {
          case 0:
            //document.onclick = async (e) => {
            elementId = e.target.id;
            postId = e.target.name; // Handle Like Button Click
            if (!elementId.includes("likeButton")) {
              _context.n = 3;
              break;
            }
            // replace button with Counter to get the span id 
            likeCounterId = elementId.replace('Button', 'Counter'); // trim removes leading and trailing spaces
            likeCounterVal = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(likeCounterId).innerHTML.trim().replace(/\n/g, ''); // 
            encodedLikeCounterVal = encodeURIComponent(likeCounterVal);
            _context.n = 1;
            return axios__WEBPACK_IMPORTED_MODULE_1__["default"].put("/profileCard/postLikes?postNo=".concat(postId, "&count=").concat(encodedLikeCounterVal, "&likeCounterId=").concat(likeCounterId));
          case 1:
            _context.n = 2;
            return axios__WEBPACK_IMPORTED_MODULE_1__["default"].get("/getNewLikesPusher");
          case 2:
            _context.n = 15;
            break;
          case 3:
            if (!elementId.includes("initComment")) {
              _context.n = 4;
              break;
            }
            commentFormId = elementId.replace('init', 'form');
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(commentFormId).style.display = "block";

            // Handle Comment Submission
            _context.n = 15;
            break;
          case 4:
            if (!elementId.includes("submitComment")) {
              _context.n = 8;
              break;
            }
            e.preventDefault();

            //idForm == formComment511
            idForm = elementId.replace("submit", "form"); // make the comment form disappear
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idForm).style.display = "none";
            // extract the form entries
            form = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idForm);
            formEntries = new FormData(form); // if the comment form input is empty. Get the input id and check 
            inputComment = idForm.replace("form", "input");
            idInputComment = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputComment);
            if (!(idInputComment.value == null || idInputComment.value == "")) {
              _context.n = 5;
              break;
            }
            alert("Please enter a comment before submitting");
            _context.n = 7;
            break;
          case 5:
            _context.n = 6;
            return axios__WEBPACK_IMPORTED_MODULE_1__["default"].post('/postCommentProfile', formEntries, options);
          case 6:
            _context.n = 7;
            return axios__WEBPACK_IMPORTED_MODULE_1__["default"].get("/getNewCommentPusher");
          case 7:
            _context.n = 15;
            break;
          case 8:
            if (!elementId.includes("submitPost")) {
              _context.n = 14;
              break;
            }
            e.preventDefault();
            formExtra = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formPostMessageModal');
            formData = new FormData(formExtra); // get the requesterFamCode from the localStorage 
            requesterFamCodeValue = localStorage.getItem('requesterFamCode'); // Append the new form entry to the FormData object
            formData.append('postFamCode', requesterFamCodeValue);
            _context.p = 9;
            _context.n = 10;
            return axios__WEBPACK_IMPORTED_MODULE_1__["default"].post("/member/profilePage/post", formData, options);
          case 10:
            response = _context.v;
            _context.n = 11;
            return Promise.all([axios__WEBPACK_IMPORTED_MODULE_1__["default"].get("/post/getNewPostAndEmail?newCommentNo=" + response.data.message), axios__WEBPACK_IMPORTED_MODULE_1__["default"].get("/getNewPostPusher")]);
          case 11:
            // Hide the modal and reset the form
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'none';
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("formPostMessageModal").reset();
            _context.n = 13;
            break;
          case 12:
            _context.p = 12;
            _t = _context.v;
            console.error("An error occurred:", _t);
            // Optionally, display an error message to the user
            alert("There was an error processing your request. Please try again.");
          case 13:
            _context.n = 15;
            break;
          case 14:
            if (elementId && elementId.includes('deleteNotification')) {
              (0,_global__WEBPACK_IMPORTED_MODULE_0__.deleteNotification)(elementId);
            } // take you to the request card for approval or denial
            else if (e.target.classList.contains('linkRequestCard')) {
              // ONCE THE NOTIFICATION BAR IS CLICKED, IT SHOULD TAKE YOU TO BE FRIEND REQUEST CARD
              friendRequestSection = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(e.target.getAttribute('data-id'), "_linkRequestCard"));
              if (friendRequestSection) {
                friendRequestSection.scrollIntoView({
                  behavior: "smooth"
                });
              }
            }
          case 15:
            return _context.a(2);
        }
      }, _callee, null, [[9, 12]]);
    }));
    return function (_x) {
      return _ref.apply(this, arguments);
    };
  }());
} catch (e) {
  showError(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/comment.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/comment.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   appendNewComment: () => (/* binding */ appendNewComment),
/* harmony export */   commentHTML: () => (/* binding */ commentHTML),
/* harmony export */   showComment: () => (/* binding */ showComment),
/* harmony export */   writeComment: () => (/* binding */ writeComment)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");


var commentHTML = function commentHTML(data) {
  var profileImg = data.profileImg,
    fullName = data.fullName,
    date_created = data.date_created,
    img = data.img,
    comment = data.comment,
    comment_no = data.comment_no;
  var imgURL = profileImg || img;
  var image = imgURL ? "/public/img/profile/".concat(imgURL) : "/public/avatar/avatarF.png";
  return "<div class='d-flex mb-3 commentDiv' id='comment".concat(comment_no, "' name='commentDiv'>\n         \n      <img src='").concat(image, "' alt='Avatar' class=\"rounded-circle me-2 commentImg\" width=\"32\" height=\"32\">\n\n              <div class=\"flex-grow-1\">\n                <div class=\"comment\">\n                  <strong> ").concat((0,_shared__WEBPACK_IMPORTED_MODULE_1__.toSentenceCase)(fullName), " </strong> \n                  ").concat(comment, "  \n                  <small class='w3-right w3-opacity commentTiming' datetime='").concat(date_created, "' title='").concat(date_created, "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), " \n                  </small> \n                  </div>\n              </div>\n         \n          </div>");
};
var showComment = function showComment(comment) {
  if (!comment) {
    return "<div id=\"comment\" name=\"commentDiv\"></div>";
  } // only run if there is comment

  // USED FOR ALL THE COMMENTS WHEN THE PAGE IS LOADING
  var commentHTMLArray = comment.map(function (commentElement) {
    return commentHTML(commentElement);
  });
  return commentHTMLArray.join(''); // Join the array elements into a single string
};
var appendNewComment = function appendNewComment(commentData) {
  // check if commentData is valid
  if (!commentData) {
    throw new Error('No comment update received');
  }
  var idDiv = "showComment".concat(commentData.post_no);
  // check if the div has been created by the DOM 

  var commentContainer = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)(idDiv);
  if (!commentContainer) {
    throw new Error("The comment div id does not exist ");
  }
  var commentHtml = commentHTML(commentData);
  commentContainer.insertAdjacentHTML('beforeend', commentHtml);
};
var writeComment = function writeComment(postNo) {
  return " \n    <form action=\"/postCommentProfile\" \n      id=\"formComment".concat(postNo, "\" \n    >\n          <div class=\"mt-3\">\n              <div class=\"input-group\">\n                <input type=\"text\" class=\"form-control\" \n                id=\"inputComment").concat(postNo, "\" placeholder=\"Write a comment...\">\n\n                <button class=\"btn btn-outline-primary\" type=\"button\" id=\"inputComment").concat(postNo, "\" name=\"inputComment\">Post</button>\n\n              </div>\n          </div>\n    </form>\n    ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/createEvent.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/createEvent.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_5__);


function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }






var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_1__["default"](formInputArr);
var cancelModal = function cancelModal() {
  var displayNone = function displayNone() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'none';
  };
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('cancelModal').addEventListener('click', displayNone);
};
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', cancelModal);
}

/**
 * Filters events by family code (famCode) to ensure only relevant events are shown
 * @param {Object} event - The event data object
 * @returns {boolean} - Returns true if event is linked to the family code
 */

var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};

/**
 * @function process
 * @description Processes the event modal form data to create a new event
 * @param {Event} e - The event object
 * @example
 * const eventForm = id('eventModalForm');
 * eventForm.addEventListener('submit', process);
 */
var process = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(e) {
    var eventForm, eventFormEntries, _yield$Promise$all, _yield$Promise$all2, eventResponse, notificationResponse, notificationNo, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          e.preventDefault();
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = "";
          formData.massValidate();
          if (!(formData.error.length > 0)) {
            _context.n = 1;
            break;
          }
          alert('The form cannot be submitted. Please check the errors');
          formData.clearError();
          return _context.a(2);
        case 1:
          // get the form data
          eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventModalForm');
          eventFormEntries = new FormData(eventForm); // POST data to create the event and notification in parallel
          _context.n = 2;
          return Promise.all([axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/member/profilePage/event", eventFormEntries, options), axios__WEBPACK_IMPORTED_MODULE_4__["default"].post('/member/notification/event', eventFormEntries, options)]);
        case 2:
          _yield$Promise$all = _context.v;
          _yield$Promise$all2 = _slicedToArray(_yield$Promise$all, 2);
          eventResponse = _yield$Promise$all2[0];
          notificationResponse = _yield$Promise$all2[1];
          // Extract and get notificationNo from the responses
          notificationNo = notificationResponse.data.message; // update all members of similar famcode on their UIs using Pusher
          axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/member/notification/event?notificationNo=".concat(notificationNo));

          // close the modal
          displayNone();
          _context.n = 4;
          break;
        case 3:
          _context.p = 3;
          _t = _context.v;
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(_t);
        case 4:
          return _context.a(2);
      }
    }, _callee, null, [[0, 3]]);
  }));
  return function process(_x) {
    return _ref.apply(this, arguments);
  };
}();
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitEventModal').addEventListener('click', process);
  });
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/emojiPicker.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/emojiPicker.js ***!
  \******************************************************************/
/***/ (() => {

// Emoji Picker
var emojiToggle = document.getElementById('emojiToggle');
var emojiPicker = document.getElementById('emojiPicker');
emojiToggle.addEventListener('click', function (e) {
  e.preventDefault();
  emojiPicker.style.display = emojiPicker.style.display === 'grid' ? 'none' : 'grid';
});

// Add emoji to textarea
var emojiButtons = document.querySelectorAll('.emoji-btn');
var postTextarea = document.querySelector('#postModal textarea');
emojiButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    postTextarea.value += button.textContent;
  });
});

// Close emoji picker when clicking outside
document.addEventListener('click', function (e) {
  if (!emojiToggle.contains(e.target) && !emojiPicker.contains(e.target)) {
    emojiPicker.style.display = 'none';
  }
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/eventHTML.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/eventHTML.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   eventHtml: () => (/* binding */ eventHtml)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
"use static";


var eventHtml = function eventHtml(data) {
  return "<p class='eventInfo'>\n            <strong>RSVP: </strong> ".concat(data.sender_name, "</p>\n            <p class='eventInfo'><strong>Event: </strong>").concat(data.notification_name, "</p>\n            <p class='eventInfo'><strong>Date: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.date2String)(data.notification_date), " </p>\n            <p class='eventInfo'><strong>Type: </strong>").concat(data.notification_type, "</p>\n            <p class='eventInfo'><strong>Description: </strong> ").concat(data.notification_content, "</p>\n            <input type='hidden' name='event_no' id='event").concat(data.no, "' value='").concat(data.no, "'>\n\n            \n           <hr>");

  //                        <button 
  //     type="button" 
  //     id="coming${data.data.no}"
  //     class="w3-button w3-tiny w3-theme-d2 w3-margin-bottom">
  //       <em class="fa fa-comment"></em> 
  //         Coming 
  //     </button>
  // 
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/friendRequestCard.js":
/*!************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/friendRequestCard.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }


var approverId = encodeURIComponent(localStorage.getItem('requesterId')); // means that the user currently working on the UI


//NOTE - this code worked well 25/10/24
/**
 * Fetch friend requests by approver ID and render each request.
 */
var fetchFriendRequests = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var response, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          _context.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"].get("/getFriendRequestById?id=".concat(approverId));
        case 1:
          response = _context.v;
          if (response.data.message) {
            response.data.message.forEach(function (request) {
              return waitForRequestFriendClass(request);
            });
          }
          _context.n = 3;
          break;
        case 2:
          _context.p = 2;
          _t = _context.v;
          (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(_t);
        case 3:
          ;
        case 4:
          return _context.a(2);
      }
    }, _callee, null, [[0, 2]]);
  }));
  return function fetchFriendRequests() {
    return _ref.apply(this, arguments);
  };
}();

// Wait for .requestFriendClass to appear in the DOM
var waitForRequestFriendClass = function waitForRequestFriendClass(data) {
  var observer = new MutationObserver(function (mutations, obs) {
    var requestContainer = document.querySelector('.requestFriendClass');
    if (requestContainer) {
      (0,_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_2__.friendRequestCard)(data);
      obs.disconnect(); // Stop observing once .requestFriendClass is found
    } else {
      (0,_shared__WEBPACK_IMPORTED_MODULE_1__.log)('there is no requestFriendClass');
    }
  });

  // Observe the entire body for changes in child elements
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
};

// TODO: Maybe a future enhancement to show count of friend request 
var countFriendRequest = function countFriendRequest(friend) {
  return friend.length > 1 ? "<p><b>Friend Requests - ".concat(friend.length, "</b></p>\n  <br></br>") : "<p><b>Friend Request</b></p><br>";
};

// Fetch and render friend requests on page load

fetchFriendRequests();

// Friend Request Actions
var confirmRequestButtons = document.querySelectorAll('.confirm-request');
var deleteRequestButtons = document.querySelectorAll('.delete-request');
confirmRequestButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    var card = this.closest('.card-body');
    var alert = document.createElement('div');
    alert.className = 'alert alert-success';
    alert.textContent = 'Friend request confirmed!';
    card.innerHTML = '';
    card.appendChild(alert);

    // Update notification badge
    updateNotificationBadge(-1);
  });
});
deleteRequestButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    var card = this.closest('.card');
    card.style.opacity = '0';
    setTimeout(function () {
      card.remove();
      // Update notification badge
      updateNotificationBadge(-1);
    }, 300);
  });
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   html: () => (/* binding */ html)
/* harmony export */ });
/* harmony import */ var _htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./htmlFolder/nameImageTiming */ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js");
/* harmony import */ var _htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./htmlFolder/commentForm */ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js");
/* harmony import */ var _htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./htmlFolder/likeCommentButton */ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js");
/* harmony import */ var _htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./htmlFolder/showPostImages */ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");





var html = function html(el) {
  var comment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var post_no = el.post_no,
    postMessage = el.postMessage;
  return "\n    <div class=\"post card\">\n     <div class=\"card-body post{{$data['post_no']}}\" id=\"postIt\">\n    ".concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(el), "\n    <p class=\"card-text\"> ").concat(postMessage, " </p>\n    ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(el), "\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(el), "\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(el), "\n    <div id = 'showComment").concat(post_no, "' class=\"comment-section\">\n    ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment), "\n    ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.writeComment)(el), "\n      \n    </div>\n");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js":
/*!*****************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/commentForm.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   commentForm: () => (/* binding */ commentForm)
/* harmony export */ });
var commentForm = function commentForm(_ref) {
  var post_no = _ref.post_no;
  return "\n    <p id=\"formComment".concat(post_no, "_notification\"></p>\n\n    <form \n      action=\"/postCommentProfile\" \n      method=\"post\" \n      id=\"formComment").concat(post_no, "\" \n      style=\"display:none\" \n      enctype=\"multipart/form-data\"\n      class=\"mb-4\"\n    >\n\n      <input \n        type=\"hidden\" \n        name=\"post_no\" \n        value=\"").concat(post_no, "\" \n      />\n\n      <input \n        type=\"text\" \n        class=\"form-control form-control-sm rounded-pill inputComment\" \n        placeholder=\"Write a comment\" \n        id=\"inputComment").concat(post_no, "\" \n        name=\"comment\" \n        value=\"\"\n      />\n\n      <div class=\"mt-3\">\n        <button \n          type=\"submit\" \n          id=\"submitComment").concat(post_no, "\" \n          class=\"btn btn-success btn-sm submitComment\"\n        >\n          Submit\n        </button>\n      </div>\n    </form>\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   friendRequestCard: () => (/* binding */ friendRequestCard)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");

var appUrl = "https://olaogun.test/";
var approverId = encodeURIComponent(localStorage.getItem('requesterId'));
var friendRequestCard = function friendRequestCard(data) {
  var _data$img, _data$firstName, _data$lastName, _data$id, _data$famCode;
  var imageUrl = "/public/img/profile/".concat(encodeURIComponent((_data$img = data.img) !== null && _data$img !== void 0 ? _data$img : data.requesterProfileImg));
  var firstName = encodeURIComponent((_data$firstName = data.firstName) !== null && _data$firstName !== void 0 ? _data$firstName : data.requesterFirstName);
  var lastName = encodeURIComponent((_data$lastName = data.lastName) !== null && _data$lastName !== void 0 ? _data$lastName : data.requesterLastName);
  var requestId = encodeURIComponent((_data$id = data.id) !== null && _data$id !== void 0 ? _data$id : data.requesterId);
  var requestCode = encodeURIComponent((_data$famCode = data.famCode) !== null && _data$famCode !== void 0 ? _data$famCode : data.requesterFamCode);
  var mutualFriends = '2 mutual friends';
  var html = "<p id=".concat(requestId, "_linkRequestCard></p>\n\n    <div class=\"d-flex align-items-center mb-3 friend-request-card\">\n      <img src=\"").concat(imageUrl, "\" alt=\"Avatar\" class=\"avatar me-3><br>\n\n        <div class=\"flex-grow-1\">\n          <h6 class=\"mb-0\">").concat(firstName, " ").concat(lastName, "</h6>\n          <small class=\"text-muted\">").concat(mutualFriends, "</small>\n        </div>\n    </div>\n\n    <div class=\"friend-request-actions mb-3\">\n\n              <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/50/").concat(requestCode, "/pp\" class=\"btn btn-sm btn-primary\" title=\"confirm\">Confirm</a>\n\n\n              <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/10\" class=\"btn btn-sm btn-outline-secondary\" title=\"Decline\">Decline</a>\n\n    </div>\n  ");
  (0,_shared__WEBPACK_IMPORTED_MODULE_0__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', html);
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   likeCommentButton: () => (/* binding */ likeCommentButton)
/* harmony export */ });
var likeCommentButton = function likeCommentButton(data) {
  return "\n   <div class=\"reaction-buttons d-flex justify-content-between border-top border-bottom py-2 mb-3\">\n    <button \n      type=\"button\" \n      id=\"likeButton".concat(data.post_no, "\" \n      name=\"").concat(data.post_no, "\"\n      <i class=\"bi bi-hand-thumbs-up me-1\"></i> \n      \xA0   Like \n        <b>\n          <span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">\n            ").concat(data.post_likes, "\n          </span>\n        </b>\n    </button>\n\n    <button \n      type=\"button\" \n      id=\"initComment").concat(data.post_no, "\">\n        <i class=\"bi bi-chat me-1\"></i> \n          Comment \n      </button>\n        <button><i class=\"bi bi-share me-1\"></i> Share</button>\n    </div>\n    ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js":
/*!*********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   nameImgTiming: () => (/* binding */ nameImgTiming)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");


var timeAgo = function timeAgo(x) {
  return (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x);
};

// const fullName = (fullName) => {
//   return `<h6 id="fullName"><b>${fullName}</b> </h6>`
// }

// const postedAt = (date) => {
//   return `<div class="timeago postTimeCal w3-right w3-opacity"  datetime='${date.date_created}' title='${format(date.date_created)}'> ${timeAgo(date.post_time)}</div>`
// }

// export const nameImgTiming2 = (data) => {

//     const img = (data.profileImg) ? `/public/img/profile/${data.profileImg}` : "/public/avatar/avatarF.png"

//     return `<a href="/profilepage/img?dir=img&pics=${data.img}&pID=${data.post_no}&path=profile&id=${data.id}"> 
//       <img src=${img} alt="img" class="w3-left w3-circle w3-margin-right postImg" style="width:60px">
//         </a>
//         ${postedAt(data)} ${fullName(data.fullName)}`
// }

var nameImgTiming = function nameImgTiming(data) {
  var profileImg = data.profileImg,
    fullName = data.fullName,
    date_created = data.date_created,
    post_time = data.post_time;
  var img = profileImg ? "/public/img/profile/".concat(profileImg) : "/public/avatar/avatarF.png";
  return "<div class=\"d-flex align-items-center mb-3\">\n\n            <img src=\"".concat(img, "\" alt=\"Profile\" class=\"rounded-circle me-3 postImg\" width=\"40\" height=\"40\">\n                        \n            <div>\n                <h6 class=\"mb-0\">").concat((0,_shared__WEBPACK_IMPORTED_MODULE_1__.toSentenceCase)(fullName), " </h6>\n\n                <small class=\"text-muted\">posted </small><small class=\"text-muted timeago postTimeCal\" title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), "' datetime='").concat(date_created, "'> ").concat(timeAgo(post_time), "</small>\n            </div>\n            </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showPostImg: () => (/* binding */ showPostImg)
/* harmony export */ });
var showPostImg = function showPostImg(data) {
  // GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE
  var postImagesWithValues = Object.keys(data).filter(function (key) {
    return key.startsWith('post_img') && data[key] !== null;
  }).map(function (el) {
    return data[el];
  });
  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    return "\n    <a href=\"/profilepage/img?dir=img&pics=".concat(imgElement, "&pID=").concat(postNo, "&path=post\">\n      <div class=\"w3-half\">\n        <img src=\"/public/img/post/").concat(imgElement, "\" style=\"width:100%\" alt=\"images").concat(i, "\" class=\"img-fluid mb-3\" id=\"postImage").concat(i, "\" >\n      </div>\n    </a>\n  ");
  };
  var imgElements = postImagesWithValues.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }).join('');
  return "\n\n      ".concat(imgElements, "\n\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/img.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/img.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_images__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/images */ "./resources/asset/js/components/helper/images.js");




// import  axios from "axios"

// id('profilePics').addEventListener('click', ()=> id('formProfilePics').style.display ="block"

// );

// // FOR PROFILE IMAGE CHANGE
// showImageFileUploadFn('uploadButtonProfilePics', 'profileImageFile', 'profileImgFileNames')

// // FOR POST MODAL IMAGE UPLOAD  

// showImageFileUploadFn('uploadButton', 'post_img', 'postModalImgFileNames')

// id('submitProfilePics').addEventListener('click', ()=> {23

//   // Get the form element
//     const form = document.getElementById("formProfilePics");

//     // Create a FormData object and append the form data to it
//     const formData = new FormData(form);

//     const options = {
//         xsrfCookieName: 'XSRF-TOKEN',
//         xsrfHeaderName: 'X-XSRF-TOKEN',
//     }
//     // send form data using axios post method

//     axios.post('/member/profilePage/profileImg', formData, options)
//     .then((response) => {

//       id('profilePicsNotification').innerHTML = response.data
//       log(response.data, "profilePicsNotification")

//       if(response.data.message === "Profile image updated") {
//         id('profilePicsNotification').classList.add('w3-green')
//         id('profilePicsNotification').innerHTML = response.data.message
//         // Reload the page
//         location.reload();

//       }
//     })
//     .catch((error) => {
//        id('profilePicsNotification').classList.add('w3-red')
//       id('profilePicsNotification').innerHTML = error.message
//     });

//   id('profilePicsNotification').innerHTML = ""
// })

/***/ }),

/***/ "./resources/asset/js/components/profilePage/index.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/index.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _loadPost__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./loadPost */ "./resources/asset/js/components/profilePage/loadPost.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/profilePage/modal.js");
/* harmony import */ var _img__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./img */ "./resources/asset/js/components/profilePage/img.js");
/* harmony import */ var _emojiPicker__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./emojiPicker */ "./resources/asset/js/components/profilePage/emojiPicker.js");
/* harmony import */ var _emojiPicker__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_emojiPicker__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _rsvpBtn__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./rsvpBtn */ "./resources/asset/js/components/profilePage/rsvpBtn.js");
/* harmony import */ var _rsvpBtn__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_rsvpBtn__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/profilePage/allEvents.js");
/* harmony import */ var _registerPushNotification__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./registerPushNotification */ "./resources/asset/js/components/profilePage/registerPushNotification.js");
/* harmony import */ var _periodicSync__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./periodicSync */ "./resources/asset/js/components/profilePage/periodicSync.js");
/* harmony import */ var _periodicSync__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_periodicSync__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _createEvent__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./createEvent */ "./resources/asset/js/components/profilePage/createEvent.js");
/* harmony import */ var _friendRequestCard__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./friendRequestCard */ "./resources/asset/js/components/profilePage/friendRequestCard.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _newPage__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./newPage */ "./resources/asset/js/components/profilePage/newPage.js");
/* harmony import */ var _newPage__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(_newPage__WEBPACK_IMPORTED_MODULE_11__);


localStorage.removeItem('redirect');

// Dark Mode Toggle
var darkModeToggle = document.getElementById('darkModeToggle');
var body = document.body;

// Check for saved dark mode preference
if (localStorage.getItem('darkMode') === 'enabled') {
  body.classList.add('dark-mode');
  darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
}
darkModeToggle.addEventListener('click', function () {
  body.classList.toggle('dark-mode');
  if (body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
    darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
  } else {
    localStorage.setItem('darkMode', null);
    darkModeToggle.innerHTML = '<i class="bi bi-moon-fill"></i>';
  }
});













/***/ }),

/***/ "./resources/asset/js/components/profilePage/loadPost.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/loadPost.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _post__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./post */ "./resources/asset/js/components/profilePage/post.js");
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }









// set an empty array
try {
  var MAX_APPENDED_POSTS = 1000; // Set a maximum limit
  var appendedComments = new Set(); // To track unique comments
  var appendedPosts = new Set(); // To track unique comments

  // Initialize Pusher
  var pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_4___default())("0dc3f141e1632b5aa5db", {
    cluster: "eu",
    encrypted: true
  });

  // Global state object with data-fetching and initialization logic
  var state = {
    post: [],
    comment: [],
    // Method to fetch initial data and populate state
    initialize: function initialize() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var pullData, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              _context.p = 0;
              _context.n = 1;
              return axios__WEBPACK_IMPORTED_MODULE_5__["default"].get("/post/getAllPostCommentByFamCode");
            case 1:
              pullData = _context.v;
              // Assign fetched data to state properties
              _this.post = pullData.data.message.post;
              _this.comment = pullData.data.message.comment;
              _this.comment = _this.comment.flat(); // Flatten the array of arrays into a single array of comment objects

              if (_this.post.length > 0) {
                // Render posts and comments on the page after data is loaded
                _this.post.forEach(function (data) {
                  return (0,_post__WEBPACK_IMPORTED_MODULE_1__.allPost)(data, _this.comment);
                });
              } else {
                (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)("No post");
              }
              _context.n = 3;
              break;
            case 2:
              _context.p = 2;
              _t = _context.v;
              console.error("Error fetching posts and comments:", _t);
            case 3:
              return _context.a(2);
          }
        }, _callee, null, [[0, 2]]);
      }))();
    }
  };

  // initiate the global object
  state.initialize();
  var updatePost = /*#__PURE__*/function () {
    var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(e) {
      var dataForUse, oldestPost, _t2;
      return _regenerator().w(function (_context2) {
        while (1) switch (_context2.p = _context2.n) {
          case 0:
            // Parse the incoming data and check if it already exists in state
            dataForUse = checkOriginAndParsedData(e); // Only append if the comment hasn't been added before
            if (appendedPosts.has(dataForUse.post_no)) {
              _context2.n = 4;
              break;
            }
            appendedPosts.add(dataForUse.post_no);

            // Clean up old entries if the set exceeds the limit
            if (appendedPosts.size > MAX_APPENDED_POSTS) {
              oldestPost = appendedPosts.values().next().value;
              appendedPosts["delete"](oldestPost);
            }
            (0,_post__WEBPACK_IMPORTED_MODULE_1__.appendNewPost)(dataForUse);
            _context2.p = 1;
            _context2.n = 2;
            return axios__WEBPACK_IMPORTED_MODULE_5__["default"].put("/updatePostByStatusAsPublished/".concat(dataForUse.post_no), {
              post_status: 'published'
            });
          case 2:
            _context2.n = 4;
            break;
          case 3:
            _context2.p = 3;
            _t2 = _context2.v;
            console.error("Failed to update comment status: ".concat(_t2.message));
          case 4:
            return _context2.a(2);
        }
      }, _callee2, null, [[1, 3]]);
    }));
    return function updatePost(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  var updateComment = /*#__PURE__*/function () {
    var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(e) {
      var dataForUse, _t3;
      return _regenerator().w(function (_context3) {
        while (1) switch (_context3.p = _context3.n) {
          case 0:
            // Parse the incoming data and check if it already exists in state
            dataForUse = checkOriginAndParsedData(e); // Only append if the comment hasn't been added before
            if (appendedComments.has(dataForUse.comment_no)) {
              _context3.n = 4;
              break;
            }
            appendedComments.add(dataForUse.comment_no);

            // check if dataForUse length is greater than 0 and if yes foreach to lop 

            (0,_comment__WEBPACK_IMPORTED_MODULE_3__.appendNewComment)(dataForUse);
            _context3.p = 1;
            _context3.n = 2;
            return axios__WEBPACK_IMPORTED_MODULE_5__["default"].put("/updateCommentByStatusAsPublished/".concat(dataForUse.comment_no), {
              comment_status: 'published'
            });
          case 2:
            _context3.n = 4;
            break;
          case 3:
            _context3.p = 3;
            _t3 = _context3.v;
            console.error("Failed to update comment status: ".concat(_t3.message));
          case 4:
            return _context3.a(2);
        }
      }, _callee3, null, [[1, 3]]);
    }));
    return function updateComment(_x2) {
      return _ref2.apply(this, arguments);
    };
  }();
  var updateLike = function updateLike(e) {
    // Parse the incoming data and check if it already exists in state
    var dataForUse = checkOriginAndParsedData(e);
    var likeElement = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(dataForUse.likeHtmlId);
    if (likeElement) {
      likeElement.innerHTML = parseInt(dataForUse.likeCounter);
    }
  };

  // Subscribe to the posts channel
  var postsChannel = pusher.subscribe('posts-channel');
  postsChannel.bind('new-post', function (data) {
    data.forEach(function (item) {
      return updatePost(item);
    });
  });

  // Subscribe to the comments channel
  var commentsChannel = pusher.subscribe('comments-channel');
  commentsChannel.bind('new-comment', function (data) {
    data.forEach(function (item) {
      return updateComment(item);
    });
  });

  // Subscribe to the likes channel
  var likesChannel = pusher.subscribe('likes-channel');
  likesChannel.bind('like-event', function (data) {
    data.forEach(function (item) {
      return updateLike(item);
    });
  });

  // Subscribe to the event channel

  var checkEventAndAdd = function checkEventAndAdd(data) {
    var appendEvent = (0,_eventHTML__WEBPACK_IMPORTED_MODULE_6__.eventHtml)(data);
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventList').insertAdjacentHTML('afterbegin', appendEvent);
  };
  var notificationChannel = pusher.subscribe('notification-channel');
  notificationChannel.bind('new-notification', function (data) {
    if (localStorage.getItem('requesterFamCode') === data.receiver_id) {
      checkEventAndAdd(data);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_7__.addToNotificationTab)(data);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_7__.increaseNotificationCount)();
    }
  });

  // AUTOMATICALLY UPDATE TIMESTAMP
  // Function to check for elements and render if they exist every 5 seconds
  setInterval(function () {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.checkManyElements)('class', ".timeago", timeago_js__WEBPACK_IMPORTED_MODULE_2__.render);
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.checkManyElements)('class', ".commentTiming", timeago_js__WEBPACK_IMPORTED_MODULE_2__.render);
  }, 2000); // Adjust interval as needed

  var checkOriginAndParsedData = function checkOriginAndParsedData(data) {
    if (!data) throw new Error('No update received');
    if (data) {
      if (data.origin != appUrl) {
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.msgException)('Invalid Origin');
      }
      return data;
    }

    // check if data is a valid jason object
    // return JSON.parse(data)
  };
} catch (error) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/modal.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/modal.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");




// import { postFormData } from "../helper/http"

try {
  // NEW MESSAGE MODAL
  var showModal = function showModal() {
    return (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'block';
  };

  // CREATE EVENT MODAL
  var showEvent = function showEvent() {
    return (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'block';
  };

  //EVENT ACTION

  var action = function action() {
    var createEvent = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('createEvent');
    var postMsg = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('postMsg');
    if (createEvent) {
      createEvent.addEventListener('click', showEvent);
    } else if (postMsg) {
      postMsg.addEventListener('click', showModal);
    }
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', action);
  }

  // handle post message
} catch (e) {
  (0,_shared__WEBPACK_IMPORTED_MODULE_0__.showError)(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/newPage.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/newPage.js ***!
  \**************************************************************/
/***/ (() => {

// Create Event Functionality
var createEventBtn = document.getElementById('createEventBtn');
createEventBtn.addEventListener('click', function () {
  var eventTitle = document.getElementById('eventTitle').value;
  var eventDate = document.getElementById('eventDate').value;
  var eventTime = document.getElementById('eventTime').value;
  var eventLocation = document.getElementById('eventLocation').value;
  var eventDescription = document.getElementById('eventDescription').value;
  if (!eventTitle || !eventDate || !eventTime || !eventLocation) {
    alert('Please fill in all required fields: Title, Date, Time, and Location');
    return;
  }

  // Format the date for display
  var dateObj = new Date(eventDate);
  var day = dateObj.getDate();
  var month = dateObj.toLocaleString('default', {
    month: 'SHORT'
  }).toUpperCase();

  // Format the time for display
  var timeObj = new Date("1970-01-01T".concat(eventTime));
  var formattedTime = timeObj.toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit'
  });

  // Create the new event card
  var eventsContainer = document.getElementById('eventsContainer');
  var newEventCard = document.createElement('div');
  newEventCard.className = 'event-card card';
  newEventCard.innerHTML = "\n                <div class=\"card-body\">\n                    <div class=\"d-flex\">\n                        <div class=\"event-date me-3\">\n                            <div class=\"fw-bold\">".concat(day, "</div>\n                            <div>").concat(month, "</div>\n                        </div>\n                        <div class=\"flex-grow-1\">\n                            <h6 class=\"mb-0\">").concat(eventTitle, "</h6>\n                            <small class=\"text-muted\">").concat(formattedTime, " \u2022 ").concat(eventLocation, "</small>\n                            <div class=\"mt-2 rsvp-buttons d-flex\">\n                                <button class=\"btn btn-sm btn-outline-primary\">Going</button>\n                                <button class=\"btn btn-sm btn-outline-secondary\">Maybe</button>\n                                <button class=\"btn btn-sm btn-outline-danger\">Decline</button>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            ");

  // Add the new event at the top of the events list
  eventsContainer.insertBefore(newEventCard, eventsContainer.firstChild);

  // Reset the form
  document.getElementById('eventForm').reset();

  // Close the modal
  bootstrap.Modal.getInstance(document.getElementById('createEventModal')).hide();

  // Show success message
  alert('Event created successfully!');

  // Add event listeners to the new RSVP buttons
  var newRsvpButtons = newEventCard.querySelectorAll('.rsvp-buttons .btn');
  newRsvpButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      // Remove active class from all buttons in the same container
      this.parentElement.querySelectorAll('.btn').forEach(function (btn) {
        btn.classList.remove('active');
        if (btn.classList.contains('btn-outline-primary')) {
          btn.classList.replace('btn-primary', 'btn-outline-primary');
        } else if (btn.classList.contains('btn-outline-secondary')) {
          btn.classList.replace('btn-secondary', 'btn-outline-secondary');
        } else if (btn.classList.contains('btn-outline-danger')) {
          btn.classList.replace('btn-danger', 'btn-outline-danger');
        }
      });

      // Add active class to clicked button
      this.classList.add('active');
      if (this.classList.contains('btn-outline-primary')) {
        this.classList.replace('btn-outline-primary', 'btn-primary');
      } else if (this.classList.contains('btn-outline-secondary')) {
        this.classList.replace('btn-outline-secondary', 'btn-secondary');
      } else if (this.classList.contains('btn-outline-danger')) {
        this.classList.replace('btn-outline-danger', 'btn-danger');
      }
    });
  });
});

// Save Profile Changes
var saveProfileBtn = document.getElementById('saveProfileBtn');
saveProfileBtn.addEventListener('click', function () {
  var firstName = document.getElementById('firstName').value;
  var lastName = document.getElementById('lastName').value;
  var bio = document.getElementById('bio').value;

  // Update profile card
  document.querySelector('.profile-card .card-title').textContent = "".concat(firstName, " ").concat(lastName);
  document.querySelector('.profile-card .card-text').textContent = bio;

  // Update navbar
  document.querySelector('#userMenu').innerHTML = "<i class=\"bi bi-person-circle me-1\"></i> ".concat(firstName, " ").concat(lastName);

  // Close modal
  bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();

  // Show success message
  alert('Profile updated successfully!');
});

// Notification Button
var notificationBtn = document.getElementById('notificationBtn');
notificationBtn.addEventListener('click', function () {
  alert('You have 3 new notifications');
  // Reset notification badge
  document.querySelector('.notification-badge').style.display = 'none';
});

// Comment Time Stamps
document.querySelectorAll('.comment-btn').forEach(function (button) {
  button.addEventListener('click', function () {
    var commentSection = this.closest('.card-body').querySelector('.comment-section');
    var commentInput = commentSection.querySelector('input');
    commentInput.focus();
  });
});

// Add comment functionality
document.querySelectorAll('.comment-section .btn').forEach(function (button) {
  button.addEventListener('click', function () {
    var input = this.previousElementSibling;
    var commentText = input.value.trim();
    if (commentText) {
      var commentSection = this.closest('.comment-section');
      var newComment = document.createElement('div');
      newComment.className = 'd-flex mb-3';
      newComment.innerHTML = "\n                        <img src=\"https://randomuser.me/api/portraits/men/1.jpg\" alt=\"Profile\" class=\"rounded-circle me-2\" width=\"32\" height=\"32\">\n                        <div class=\"flex-grow-1\">\n                            <div class=\"comment\">\n                                <strong>John Doe</strong> ".concat(commentText, "\n                                <div class=\"comment-time\">Just now</div>\n                            </div>\n                        </div>\n                    ");
      commentSection.insertBefore(newComment, commentSection.lastElementChild);
      input.value = '';
    }
  });
});

// Pagination
document.querySelectorAll('.pagination .page-link').forEach(function (link) {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    var pageNum = this.getAttribute('data-page');
    if (pageNum) {
      document.querySelectorAll('.post-page').forEach(function (page) {
        page.classList.remove('active');
      });
      document.getElementById("page".concat(pageNum)).classList.add('active');
      document.querySelectorAll('.pagination .page-item').forEach(function (item) {
        item.classList.remove('active');
      });
      this.parentElement.classList.add('active');
    }
  });
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/periodicSync.js":
/*!*******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/periodicSync.js ***!
  \*******************************************************************/
/***/ (() => {

function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
// utility for periodic sync  
if ('periodicSync' in navigator) {
  _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var status, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.n = 1;
          return navigator.permissions.query({
            name: 'periodic-background-sync'
          });
        case 1:
          status = _context.v;
          if (status.state === 'granted') {
            console.log('Background sync permission granted');
          } else {
            console.log('Background sync permission denied');
          }
          _context.p = 2;
          _context.n = 3;
          return navigator.periodicSync.register('content-sync', {
            minInterval: 24 * 60 * 60 * 1000 // Minimum interval in milliseconds (e.g., 1 day)
          });
        case 3:
          console.log('Periodic Sync registered');
          _context.n = 5;
          break;
        case 4:
          _context.p = 4;
          _t = _context.v;
          console.error('Periodic Sync registration failed:', _t);
        case 5:
          return _context.a(2);
      }
    }, _callee, null, [[2, 4]]);
  }))(); // Immediately invoke the async function
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/post.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/post.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   allPost: () => (/* binding */ allPost),
/* harmony export */   appendNewPost: () => (/* binding */ appendNewPost)
/* harmony export */ });
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


var famCode = localStorage.getItem('requesterFamCode');
/**
 * Renders a post and its associated comments in the DOM.
 * 
 * This function takes a post object and an array of comment data,
 * filters the comments to include only those associated with the 
 * given post, generates HTML for the post using the `html` function, 
 * and appends it to the 'postIt' container in the DOM.
 *
 * @param {Object} el - The post object containing post data, including post number.
 * @param {Array} commentData - An array of comment objects associated with posts.
 * @returns {boolean} - Returns false if the post object is invalid.
 */
var allPost = function allPost(postData, commentData) {
  if (!postData || !Array.isArray(commentData)) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.msgException)('Invalid post data');
  }
  var postNo = parseInt(postData.post_no);
  var filterComment = commentData.filter(function (comm) {
    return parseInt(comm.post_no) === postNo;
  }); // filter the comment to an array
  var postHtml = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(postData, filterComment);
  // if(postFamCode === famCode) {
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('beforeend', postHtml);
};

/**
 * Appends a new post to the DOM if it does not already exist.
 * 
 * This function checks for the existence of comment form elements 
 * associated with the provided post object. If any of these elements 
 * are missing, it generates HTML for the post using the `html` function 
 * and inserts it at the beginning of the 'postIt' container.
 *
 * @param {Object} el - The post object containing post data, including post number.
 * @returns {boolean} - Returns false if the post object is invalid.
 */
var appendNewPost = function appendNewPost(el) {
  var post_no = el.post_no;
  // Generate the IDs for the comment form and its components const 
  var commentFormId = "formComment".concat(post_no);
  var inputCommentId = "inputComment".concat(post_no);
  var submitCommentId = "submitComment".concat(post_no);

  // Check if the comment form components exist in the DOM
  var commentForm1 = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(commentFormId);
  var inputComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(inputCommentId);
  var submitComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(submitCommentId);

  // If the comment form components do not exist, create and append the new post
  if (!commentForm1 || !inputComment || !submitComment) {
    var appendHTML = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(el);

    // Ensure the post belongs to the correct family code
    // if (el.postFamCode === famCode) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('afterbegin', appendHTML);
    // } else{
    //   return false
    // }
  }
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/registerPushNotification.js":
/*!*******************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/registerPushNotification.js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");

var VAPID_PUBLIC_KEY = "BAvqqppvGj5V0DqzieyYq5nGu9EW_db01_7jXO1_Nk-8UZzKJpCs1eGYx5d0yuBe7q3xu6oWaFS8etO9lazRMMo";
function urlBase64ToUint8Array(base64String) {
  var padding = '='.repeat((4 - base64String.length % 4) % 4);
  var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  var rawData = window.atob(base64);
  var outputArray = new Uint8Array(rawData.length);
  for (var i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

//// Check if service workers and push notifications are supported by the browser

if ('serviceWorker' in navigator && 'PushManager' in window) {
  navigator.serviceWorker.register('/service-worker.js').then(function (swReg) {
    // Check if the user is already subscribed
    checkSubscription(swReg);
  })["catch"](function (error) {
    console.error('Service Worker registration failed', error);
  });
}
function checkSubscription(swReg) {
  swReg.pushManager.getSubscription().then(function (subscription) {
    if (subscription === null) {
      // Request permission for notifications
      requestPermission(swReg);
    } else {
      console.log('User  is already subscribed:', subscription);
      postSubscriptionToServer(subscription);
    }
  });
}
function requestPermission(swReg) {
  // This call must be made in response to a user action
  Notification.requestPermission().then(function (permission) {
    if (permission === 'granted') {
      subscribeUser(swReg);
    } else {
      console.log('Push notifications permission denied.');
    }
  });
}
function subscribeUser(swReg) {
  var applicationServerKey = urlBase64ToUint8Array(VAPID_PUBLIC_KEY); // Access public VAPID key

  swReg.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: applicationServerKey
  }).then(function (subscription) {
    console.log('User is subscribed:', subscription);
    // Send subscription object to the server to store it
    postSubscriptionToServer(subscription);
  })["catch"](function (error) {
    console.error('Failed to subscribe the user: ', error);
  });
}
function postSubscriptionToServer(subscription) {
  // Prepare the subscription data to be sent to the server

  var subscriptionData = {
    id: localStorage.getItem('requesterId'),
    endpoint: subscription.endpoint,
    keys: {
      p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))),
      auth: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth'))))
    }
  };

  // Send subscription to the server using axios
  axios__WEBPACK_IMPORTED_MODULE_0__["default"].post('/pushNotification/subscription', subscriptionData).then(function (response) {
    console.log('Subscription data successfully sent to the server:', response);
  })["catch"](function (error) {
    console.error('Failed to send subscription data to the server:', error);
  });
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/rsvpBtn.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/rsvpBtn.js ***!
  \**************************************************************/
/***/ (() => {

// RSVP Buttons
var rsvpButtons = document.querySelectorAll('.rsvp-buttons .btn');
rsvpButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    // Remove active class from all buttons in the same container
    this.parentElement.querySelectorAll('.btn').forEach(function (btn) {
      btn.classList.remove('active');
      if (btn.classList.contains('btn-outline-primary')) {
        btn.classList.replace('btn-primary', 'btn-outline-primary');
      } else if (btn.classList.contains('btn-outline-secondary')) {
        btn.classList.replace('btn-secondary', 'btn-outline-secondary');
      } else if (btn.classList.contains('btn-outline-danger')) {
        btn.classList.replace('btn-danger', 'btn-outline-danger');
      }
    });

    // Add active class to clicked button
    this.classList.add('active');
    if (this.classList.contains('btn-outline-primary')) {
      this.classList.replace('btn-outline-primary', 'btn-primary');
    } else if (this.classList.contains('btn-outline-secondary')) {
      this.classList.replace('btn-outline-secondary', 'btn-secondary');
    } else if (this.classList.contains('btn-outline-danger')) {
      this.classList.replace('btn-outline-danger', 'btn-danger');
    }
  });
});

/***/ })

}]);
//# sourceMappingURL=profilePage.js.map