"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["all_members"],{

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

/***/ "./resources/asset/js/components/allMembers/allEvents.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/allEvents.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global.js */ "./resources/asset/js/components/global.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _profilePage_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../profilePage/htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }






// Attach a click event listener to the document
var reqId = localStorage.getItem('requesterId');
/**
 * Attach a click event listener to the document. When a button with the id `addFamily<userId>` is clicked, send a family request to the user identified by the userId and update the button's HTML and disable it.
 it returns the notification details for the approvers tab
 * 
 * @param {MouseEvent} e - The event object.
 */
document.onclick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(e) {
    var targetId, userId, approverDetails, requesterDetails, familyRequestData, response, famCode, _userId, url, notificationHTML, _response, _userId2, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          // Get the target element's ID
          targetId = e.target.id; // Check if the ID includes 'addFamily'
          if (!targetId.includes('addFamily')) {
            _context.n = 3;
            break;
          }
          // Extract the user ID from the target ID
          userId = targetId.replace('addFamily', ''); // Fetch approver details for the user
          _context.n = 1;
          return fetchApproverData(userId);
        case 1:
          approverDetails = _context.v;
          // Prepare family request data
          requesterDetails = getLocalStorageProfile();
          familyRequestData = {
            requester: requesterDetails,
            approver: approverDetails,
            emailPath: 'msg/request',
            subject: "".concat(requesterDetails.requesterFirstName, " ").concat(requesterDetails.requesterLastName, " sent you a family request")
          }; // Send the family request data to the server for processing which returns the notification details for the approvers tab
          _context.n = 2;
          return sendFamilyRequest(familyRequestData);
        case 2:
          response = _context.v;
          // ADD TO THE NOTIFICATION TAB OF THE APPROVER if the famcode on local storage is the same as the approverFamCode
          famCode = localStorage.getItem('requesterFamCode');
          if (famCode === approverDetails.approverCode) {
            (0,_navbar__WEBPACK_IMPORTED_MODULE_3__.addToNotificationTab)(response.data.message);
            (0,_profilePage_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_4__.friendRequestCard)(requesterDetails);
            (0,_navbar__WEBPACK_IMPORTED_MODULE_3__.increaseNotificationCount)();
          }

          // Update the button's HTML and disable it
          updateButton(targetId, 'Request Sent');
          _context.n = 7;
          break;
        case 3:
          if (!targetId.includes('removeProfile')) {
            _context.n = 6;
            break;
          }
          // Extract the user ID from the target ID
          _userId = targetId.replace('removeProfile', '');
          url = "/allMembers/removeProfile/".concat(_userId, "/").concat(reqId);
          alert(url);

          // include a console to confirm if they truly want to delete the profile
          if (!confirm('You will no longer see the profile and associated posts. Are you sure you want to delete the profile?')) {
            _context.n = 5;
            break;
          }
          notificationHTML = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.qSel)(".member_profile_".concat(_userId));
          _context.n = 4;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"]["delete"](url);
        case 4:
          _response = _context.v;
          if (_response.data.message === 'success') {
            // remove a html element with call member_profile
            notificationHTML.remove();
          } else {
            (0,_shared__WEBPACK_IMPORTED_MODULE_1__.msgException)("Error deleting profile");
          }
        case 5:
          _context.n = 7;
          break;
        case 6:
          if (targetId.includes('seeProfile')) {
            // Extract the user ID from the target ID
            _userId2 = targetId.replace('seeProfile', ''); // redirect to 'allMembers/setProfile/'+userId
            window.location.href = "/allMembers/seeProfile/".concat(_userId2);
          }
        case 7:
          _context.n = 9;
          break;
        case 8:
          _context.p = 8;
          _t = _context.v;
          // Handle any errors that occur during execution
          (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(_t);
        case 9:
          return _context.a(2);
      }
    }, _callee, null, [[0, 8]]);
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
  _fetchApproverData = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(userId) {
    var result, approverDetails, _t2;
    return _regenerator().w(function (_context2) {
      while (1) switch (_context2.p = _context2.n) {
        case 0:
          _context2.p = 0;
          _context2.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"].get("/members/familyRequestMgt/getApprover?id=".concat(userId));
        case 1:
          result = _context2.v;
          approverDetails = {
            approverFirstName: result.data.message.firstName,
            approverLastName: result.data.message.lastName,
            approverEmail: result.data.message.email,
            approverId: result.data.message.id,
            approverCode: result.data.message.famCode
          };
          return _context2.a(2, approverDetails);
        case 2:
          _context2.p = 2;
          _t2 = _context2.v;
          console.error(_t2);
          throw _t2;
        case 3:
          return _context2.a(2);
      }
    }, _callee2, null, [[0, 2]]);
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
  _sendFamilyRequest = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(data) {
    var _t3;
    return _regenerator().w(function (_context3) {
      while (1) switch (_context3.p = _context3.n) {
        case 0:
          _context3.p = 0;
          _context3.n = 1;
          return axios__WEBPACK_IMPORTED_MODULE_0__["default"].post('/members/familyRequestMgt', data);
        case 1:
          return _context3.a(2, _context3.v);
        case 2:
          _context3.p = 2;
          _t3 = _context3.v;
          (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(_t3);
        case 3:
          return _context3.a(2);
      }
    }, _callee3, null, [[0, 2]]);
  }));
  return _sendFamilyRequest.apply(this, arguments);
}
function updateButton(targetId, newHTML) {
  var theTargetId = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)(targetId);
  theTargetId.innerHTML = newHTML;
  theTargetId.disabled = true;
}

// ADD THE NEW EVENT TO THE NOTIFICATION TAB

/***/ }),

/***/ "./resources/asset/js/components/allMembers/api.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/api.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderMembers: () => (/* binding */ renderMembers)
/* harmony export */ });
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
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
var reqId = localStorage.getItem('requesterId');
var URL = "https://olaogun.test/";
var allMembersContainer = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers');
var noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";
var renderMembers = function renderMembers(data, container, noMemberMessage, html) {
  // container.innerHTML = "";
  if (data) {
    data.forEach(html);
  } else if (!data) {
    container.innerHTML = noMemberMessage;
  } else {
    data.forEach(html);
  }
};
axios__WEBPACK_IMPORTED_MODULE_0__["default"].get("".concat(URL, "allMembers/processApiData?id=").concat(reqId), config).then(function (response) {
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').innerHTML = "";
  if (!response.data) {
    throw Error('There is no data');
  }
  var data = response.data;
  console.log(data);
  var dataWithFamCode = (0,_filterMembersByFamCode__WEBPACK_IMPORTED_MODULE_3__["default"])(data);
  renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML, _html__WEBPACK_IMPORTED_MODULE_2__.renderHtml);

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
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
var reqId = localStorage.getItem('requesterId');
var famCode = localStorage.getItem('requesterFamCode');
var filterMembersByFamCode = function filterMembersByFamCode(data) {
  // Check if data is an array before calling filter
  if (!Array.isArray(data)) {
    console.error('Error: data is not an array:');
  }
  return data.filter(function (el) {
    return el.famCode === famCode || el.requesterCode === famCode || el.postFamCode === famCode || el.eventCode === famCode;
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (filterMembersByFamCode);

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
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }




var reqId = localStorage.getItem('requesterId');
var famCode = localStorage.getItem('requesterFamCode');
var allMembersContainer = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('allMembers');
var noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";
var handleInput = function handleInput(data, WithFamCode, renderMembers) {
  var searchInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('searchFamily');
  var inputVal = searchInput.value.trim().toLowerCase();
  allMembersContainer.innerHTML = "";
  if (inputVal === "") {
    renderMembers(WithFamCode, allMembersContainer, noMemberHTML, _html__WEBPACK_IMPORTED_MODULE_1__.renderHtml);
  } else {
    var filteredData = data.filter(function (el) {
      return el.firstName.toLowerCase().includes(inputVal) || el.lastName.toLowerCase().includes(inputVal);
    });
    // if no match found, show a message with a checkbox to send a request to the new member to join the platform
    // the checkbox will show a form to enter the new member's name and email or mobile number
    // the form will have a submit button to send the request

    if (filteredData.length === 0) {
      allMembersContainer.innerHTML = "No matching name found-  Do you want us to send them a text/email to register to the platform</i>?</h4>".concat((0,_shared__WEBPACK_IMPORTED_MODULE_2__.checkBox)('newMemberRequest'), " <br> \n            \n            <input type=\"hidden\" id=\"newMemberName\" value=\"").concat(inputVal, "\">\n\n             <input type=\"type\" id=\"newMemberRequestName\" class=\"form-control\" name=\"newMemberRequestName\" data-yourName = \"\" placeholder=\"Enter their name\" >\n\n            <input type=\"type\" id=\"newMemberRequestEmail\" class=\"form-control\" name=\"newMemberRequestEmail\" data-yourName = \"\" placeholder=\"Enter their email address or mobile number\" >\n\n            <p id=\"loader\" class=\"loader\" style=\"display:none;\"><p>\n            <small id=\"newMemberRequest_help\" class=\"form-text text-muted\"></small>\n\n            <button class=\"button is-primary\" id=\"newMemberRequestBtn\">Send Request</button>");
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestEmail').style.display = 'none';
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestName').style.display = 'none';
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestBtn').style.display = 'none';
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestYes').addEventListener('click', function () {
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestName').style.display = 'block';
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestEmail').style.display = 'block';
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestBtn').style.display = 'block';
      });
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestBtn').addEventListener('click', function () {
        var email = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestEmail').value;
        var name = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequestName').value;
        var yourName = localStorage.getItem('yourName');
        var familyCode = localStorage.getItem('requesterFamCode');
        // check if email is an email or mobile number
        var mobileRegex = /^\+?[1-9]\d{1,14}$/; // Simple regex for international phone numbers
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Simple regex for email addresses
        var helpMsg = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('newMemberRequest_help');
        if (!emailRegex.test(email) && !mobileRegex.test(email)) {
          helpMsg.innerHTML = 'Please enter a valid email address or mobile number.';
          return;
        }
        if (!emailRegex.test(email) && mobileRegex.test(email)) {
          // if it is a mobile number, ensure it starts with country code
          if (!email.startsWith('+')) {
            helpMsg.innerHTML = 'Please include the country code. E.g +2348036517179';
            return;
          } else {
            // send a text to the mobile number
          }
        } else if (emailRegex.test(email)) {
          if (name.length < 2) {
            helpMsg.innerHTML = 'Please enter a valid name with at least 2 characters.';
            return;
          }

          // send an email to the email address
          var postObj = {
            mobile: "",
            viewPath: "msg/contactNewMember",
            data: {
              email: email,
              mobile: "",
              name: name,
              familyCode: familyCode,
              yourName: yourName
            },
            subject: "".concat(yourName, " Wants You: Experience the Magic of your Family Network Today!")
          };
          axios__WEBPACK_IMPORTED_MODULE_3__["default"].post("/register/contactNewMember", postObj).then(function (response) {
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.showNotification)("allMembers", 'is-success', response.data.message);
            helpMsg.innerHTML = "";
          })["catch"](function (error) {
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.showNotification)("allMembers", 'is-danger', error.message);
          });
        }
      });
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
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");


var toSentenceCase = function toSentenceCase(str) {
  if (str || typeof str == 'string')
    // {
    //     throw new Error('Invalid sentence for toSentenceCase function')
    // }
    return str.toLowerCase() // Convert the string to lowercase
    .split(' ') // Split the string into words
    .map(function (word) {
      return word.charAt(0).toUpperCase() + word.slice(1);
    }) // Capitalize the first letter of each word
    .join(' '); // Join the words back into a string
};
var renderHtml = function renderHtml(el) {
  var famCode = localStorage.getItem('requesterFamCode');
  var reqId = localStorage.getItem('requesterId');
  try {
    if (!el) {
      // Handle the case where 'el' is falsy, such as when data is not available.
      throw new Error('there is no data');
    }
    var theImg = "/resources/images/profile/".concat(el.img);
    var areTheyLinked = famCode == el.famCode || famCode == el.requesterCode;
    var related = famCode == el.famCode;
    var statusButtonHTML = el.status && el.requester_id === reqId && el.status !== 'Approved' ? el.status : 'Connect';
    var relationshipType = el.relationship ? el.relationship : 'Immediate Family';
    var disableButton = statusButtonHTML === "Request sent" ? "disabled" : "";
    var fatherName = toSentenceCase(el.father_name);
    var motherName = toSentenceCase(el.mother_name);
    var spouseName = toSentenceCase(el.spouseName);
    // const spouse = toSentenceCase(el.spouseName);

    // Create the HTML content based on whether the user is in the same family or not. // LinkedIn-like card design
    var html = "\n    <div class=\"member-card member_profile_".concat(el.id, "\" id=\"").concat(el.id, "\">\n\n       <div class=\"member-card-header\">\n            <img src=\"").concat(el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image', "\"  alt=\"Member-").concat(el.firstName, "\" class=\"member-avatar\">\n        </div>\n\n        <div class=\"member-card-body\">\n            <h3 class=\"member-name\">").concat(toSentenceCase(el.firstName), " ").concat(toSentenceCase(el.lastName), "</h3>\n            <p class=\"member-location\">").concat(el.country, "</p>\n\n  ").concat(areTheyLinked ? "\n    <div class=\"member-details\">\n      <p class=\"member-detail\"><b>Father:</b> ".concat(fatherName || 'Not specified', "</p>\n      <p class=\"member-detail\"><b>Mother:</b> ").concat(motherName || 'Not specified', "</p>\n      <p class=\"member-detail\"><b>Spouse:</b> ").concat(spouseName || 'Not specified', "</p>\n      <p class=\"member-detail\"><b>Mobile:</b> ").concat(el.mobile || 'Not specified', "</p>\n       <p class=\"member-detail\"><b>Family Code:</b> ").concat(el.famCode || 'Not specified', "</p>\n         <p class=\"member-detail\"><b>Relationship Type:</b> ").concat(relationshipType, "</p>\n      <p class=\"member-detail\"><b>Member since:</b> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(el.created_at), "</p>\n    </div>\n\n    <div class=\"member-interests\">\n      <button class=\"btn btn-profile\" id=\"seeProfile").concat(el.id, "\">\n        <i class=\"fa fa-user\"></i> See Profile\n      </button>\n      <span class=\"btn btn-remove\" id=\"removeProfile").concat(el.id, "\">\n        <i class=\"fa fa-times\"></i> Remove\n      </span>\n    </div>\n  ") : "\n    <div class=\"member-actions\">\n      <button class=\"btn btn-primary btn-sm w-100\" \n              data-user-id=\"addFamily".concat(el.id, "\" \n              ").concat(disableButton, "\n              onmouseover=\"pulseButton(this)\" \n              onmouseout=\"resetButton(this)\">\n        <i class=\"fa fa-user-plus\"></i> ").concat(statusButtonHTML, "\n      </button>\n    </div>\n  "), "\n</div>\n\n\n\n    </div>\n");
    (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers').insertAdjacentHTML('beforeend', html);
  } catch (error) {
    (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
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
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/allMembers/allEvents.js");




/***/ }),

/***/ "./resources/asset/js/components/global.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/global.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkElements: () => (/* binding */ checkElements),
/* harmony export */   checkManyElements: () => (/* binding */ checkManyElements),
/* harmony export */   date2String: () => (/* binding */ date2String),
/* harmony export */   fileUploadSizeValidation: () => (/* binding */ fileUploadSizeValidation),
/* harmony export */   formReset: () => (/* binding */ formReset),
/* harmony export */   hideElement: () => (/* binding */ hideElement),
/* harmony export */   id: () => (/* binding */ id),
/* harmony export */   idInnerHTML: () => (/* binding */ idInnerHTML),
/* harmony export */   idValue: () => (/* binding */ idValue),
/* harmony export */   initializeImageModal: () => (/* binding */ initializeImageModal),
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
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }

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

/**
 * Resets a form by clearing all input fields, validation messages, 
 * image previews and custom inputs.
 * @param {string} formId - The ID of the form to reset.
 */
var formReset = function formReset(formId) {
  var form = id(formId);
  if (!form) {
    console.warn("Form with ID \"".concat(formId, "\" not found."));
    return;
  }

  // Reset form fields
  form.reset();

  // Clear validation messages
  form.querySelectorAll('.is-invalid, .invalid-feedback').forEach(function (el) {
    el.classList.remove('is-invalid');
    if (el.classList.contains('invalid-feedback')) {
      el.textContent = '';
    }
  });

  // Clear image previews
  form.querySelectorAll('.preview-img').forEach(function (img) {
    img.src = '';
    img.style.display = 'none';
  });

  // Clear custom inputs (e.g., emoji pickers, rich text)
  form.querySelectorAll('[data-custom-input]').forEach(function (el) {
    el.value = '';
  });
};
var fileUploadSizeValidation = function fileUploadSizeValidation(fileInputId) {
  var maxSizeMB = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 3;
  var fileInput = id(fileInputId);
  if (!fileInput || !fileInput.files) return true; // No files to validate

  var maxSizeBytes = maxSizeMB * 1024 * 1024;
  var _iterator = _createForOfIteratorHelper(fileInput.files),
    _step;
  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var file = _step.value;
      if (file.size > maxSizeBytes) {
        alert("File ".concat(file.name, " exceeds the maximum size of ").concat(maxSizeMB, "MB."));
        fileInput.value = ''; // Clear the input
        return false; // Validation failed
      } else if (file.size === 0) {
        alert("File ".concat(file.name, " is empty and cannot be uploaded."));
        fileInput.value = ''; // Clear the input
        return false; // Validation failed
      } else if (file.type.includes("exe") || file.type.includes("sh") || file.type.includes("bat") || file.type.includes("js")) {
        alert("File ".concat(file.name, " is of an unsupported type and cannot be uploaded."));
        fileInput.value = ''; // Clear the input
        return false; // Validation failed
      } else if (!file.type.startsWith("image/") && !file.type.startsWith("video/") && !file.type.startsWith("audio/") && !file.type === "application/pdf" && !file.type === "application/msword" && !file.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
        alert("File ".concat(file.name, " is of an unsupported type and cannot be uploaded."));
        fileInput.value = ''; // Clear the input
        return false; // Validation failed
      }
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
  return true; // All files are within size limit
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
  // id('loader').classList.remove('loader') // remove loader

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

/**
* ----------------------------------------------------------------
* Reusable Image Modal Function
* ----------------------------------------------------------------
* This function finds all images with the specified selector
* and attaches a click event to show them in a modal.
*
* @param {string} selector - The CSS selector for the images you want to be zoomable (e.g., '.zoomable-image').
* @param {string} modalId - The ID of the modal element (e.g., 'imageModal').
* @param {string} modalImageId - The ID of the image element inside the modal (e.g., 'modalImage').
* @param {string} modalCloseId - The ID of the close button inside the modal (e.g., 'imageModalClose').
* @param {string} imgSrc - The source URL of the image to display in the modal.
* @param {string} imgAlt - The alt text for the image to display in the modal.
* ---------------------------------------------------------------- 
*/
var initializeImageModal = function initializeImageModal(selector, clickedImageIndex, modalId, modalImageId, modalCloseId) {
  // Get references to the modal elements
  // Global variables to manage modal state
  var currentImages = [];
  var currentImageIndex = 0;
  var modal = document.getElementById(modalId);
  var modalImage = document.getElementById(modalImageId);
  var closeModal = document.getElementById(modalCloseId);
  var prevButton = document.getElementById('prevButton');
  var nextButton = document.getElementById('nextButton');

  // Find all images that match the selector
  var images = document.querySelectorAll(selector);
  log(images[images.length - 1].src, " IMAGES");

  // Guard clause: if no modal or images, do nothing.
  if (!modal || !modalImage || !closeModal || images.length === 0) {
    console.warn('Image modal setup failed: Required elements not found.');
    return;
  }

  // Function to hide the modal
  var hideModal = function hideModal() {
    modal.classList.remove('show');
    document.body.style.overflow = ''; // Restore scrolling
  };

  // Function to show the modal with a specific image
  var showModal = function showModal(index) {
    if (!currentImages || currentImages.length === 0) return;
    if (index < 0) {
      currentImageIndex = currentImages.length - 1; // Loop to the last image
    } else if (index >= currentImages.length) {
      currentImageIndex = 0; // Loop to the first image
    } else {
      currentImageIndex = index;
    }
    modalImage.src = currentImages[currentImageIndex].src;
    modalImage.alt = currentImages[currentImageIndex].alt;
    modal.classList.add("show");
    document.body.style.overflow = "hidden"; // Prevent background scrolling
  };

  // Event listeners for modal controls
  closeModal.addEventListener("click", hideModal);
  prevButton.addEventListener("click", function () {
    return showModal(currentImageIndex - 1);
  });
  nextButton.addEventListener("click", function () {
    return showModal(currentImageIndex + 1);
  });
  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      hideModal();
    }
  });

  // Keyboard navigation
  document.addEventListener("keydown", function (e) {
    if (modal.classList.contains("show")) {
      if (e.key === "Escape") {
        hideModal();
      } else if (e.key === "ArrowLeft") {
        showModal(currentImageIndex - 1);
      } else if (e.key === "ArrowRight") {
        showModal(currentImageIndex + 1);
      }
    }
  });
  currentImages = Array.from(document.querySelectorAll(selector));
  if (currentImages.length > 0) {
    showModal(clickedImageIndex);
  } else {
    console.warn("No images found for selector: ".concat(selector));
  }
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
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
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
  // Map notification types to icon classes
  // Map type → { icon, colour }
  var iconMap = {
    friend_request: {
      icon: "fa-solid fa-user-plus",
      color: "text-primary"
    },
    // Blue
    like: {
      icon: "fa-solid fa-thumbs-up",
      color: "text-success"
    },
    // Green
    comment: {
      icon: "fa-solid fa-comment-dots",
      color: "text-info"
    },
    // Cyan
    Anniversary: {
      icon: "fa-solid fa-cake-candles",
      color: "text-warning"
    },
    // Gold
    Birthday: {
      icon: "fa-solid fa-cake-candles",
      color: "text-warning"
    },
    // Gold
    Wedding: {
      icon: "fa-solid fa-heart",
      color: "text-warning"
    },
    // Gold
    new_post: {
      icon: "fa-solid fa-file-lines",
      color: "text-purple"
    },
    // Custom purple
    House_Warming: {
      icon: "fa-solid fa-house",
      color: "text-orange"
    },
    // Orange
    Reunion: {
      icon: "fa-solid fa-people-group",
      color: "text-pink"
    },
    // Pink
    Party: {
      icon: "fa-solid fa-champagne-glasses",
      color: "text-danger"
    },
    // Red
    Meeting: {
      icon: "fa-solid fa-handshake",
      color: "text-teal"
    },
    // Teal
    "default": {
      icon: "fa-solid fa-bell",
      color: "text-secondary"
    } // Grey
  };
  var _ref = iconMap[data.notification_type] || iconMap["default"],
    icon = _ref.icon,
    color = _ref.color;
  var readOrUnread = data.notification_status === 'new' ? 'unread' : 'read';
  var sender_id = data.sender_id,
    notification_name = data.notification_name,
    notification_content = data.notification_content,
    created_at = data.created_at,
    no = data.no;

  // generate random numbers to make the notification unique

  var randomNumber = Math.floor(100 + Math.random() * 900);
  return "<a id = \"notificationBar".concat(sender_id).concat(randomNumber, "\" href=\"#linkNotification").concat(no, "\"  class=\"list-group-item list-group-item-action d-flex align-items-start notification_real_time ").concat(readOrUnread, " notification-item linkRequestCard\">\n\n    \n            <div class=\"notification-icon ").concat(color, "\">\n                <i class=\"").concat(icon, "\"></i></div>\n            <div class=\"notification-text\">\n                <strong>").concat(notification_name, "</strong><br>\n                <small>").concat(notification_content, "</small>\n                <div class=\"notification-time\"> ").concat(postAgoNotification(created_at), " </div>\n            </div>\n            <button class=\"notification-delete btn btn-sm btn-outline-secondary btn-light\" \n                 \" \n                    data-no=\"").concat(no, "\"\n                    data-is=\"").concat(sender_id, "\"\n                    type=\"submit\"\n                    id=\"deleteNotification").concat(sender_id).concat(randomNumber, "\"\n                    aria-label=\"Delete notification\">\n               <i class=\"fa-solid fa-trash\"></i>\n            </button>\n \n\n  </a>\n\n\n  ");
};

// CLICK FUNCTION ON THE NOTIFICATION BAR THAT TAKES ONE TO THE FRIEND REQUEST CARD

var increaseNotificationCount = function increaseNotificationCount() {
  var currentNotificationCount = parseInt(sessionStorage.getItem('notificationCount')) + 1;
  (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = currentNotificationCount;
};
var addToNotificationTab = function addToNotificationTab(data) {
  return (0,_shared__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab').insertAdjacentHTML('afterbegin', notificationHTML(data));
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
      (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = data.length;

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
      (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = 0;
    }
  }
})["catch"](function (error) {
  // Handle any errors that occur during the process
  (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
});

// delete a notification 

// delete notification 

// document.addEventListener('click', async (e) => {
//     const id = e.target.id;
//        log(id)
//     // if (!id.includes('deleteNotification')) return;

//     // const deleteBtn = id(id);
//     // const sender_id = deleteBtn.getAttribute('data-id');

//     // const url = `/removeNotification/${yourId}/${famCode}/${sender_id}`
//     // const response = axios.put(url)

//     // if (response.data.message === "Notification marked as read") {

//     //     // remove a html element with notificationBar after 2 mins 
//     //     qSel(`#${deleteBtn.id}`).closest('.notification_real_time')?.remove();

//     //     // reduce the notification count as you have deleted the notification

//     //     const newValues = parseInt(sessionStorage.getItem('notificationCount') - 1)
//     //     id('notification_count').innerHTML = newValues;
//     // } else {
//     //     msgException("Error removing notification" + " " + response.data.message);
//     // }
// })

var notificationBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notificationBtn');
var notificationDropdown = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notificationDropdown');
var markAllReadBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('markAllRead');
var notificationCount = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');

// Toggle dropdown visibility
notificationBtn.addEventListener('click', function (e) {
  e.stopPropagation();
  notificationDropdown.classList.toggle('show');
});

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
  if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
    notificationDropdown.classList.remove('show');
  }
});

// Prevent dropdown from closing when clicking inside it
notificationDropdown.addEventListener('click', function (e) {
  e.stopPropagation();
});

// Mark all as read functionality
markAllReadBtn.addEventListener('click', function () {
  var unreadItems = document.querySelectorAll('.notification-item.unread');
  unreadItems.forEach(function (item) {
    item.classList.remove('unread');
  });

  // Update notification count
  notificationCount.textContent = '0';
  notificationCount.style.display = 'none';
});

/* run once, after the dropdown HTML is in the page */
var initDeleteOnce = function initDeleteOnce() {
  var tab = document.getElementById('notification_tab'); // static parent
  if (!tab) return;
  tab.addEventListener('click', function (e) {
    var btn = e.target.closest('button[id*="deleteNotification"]');
    if (!btn) return; // not a delete button → ignore

    e.stopPropagation(); // keep dropdown open
    var bannerId = btn.id.replace('deleteNotification', 'notificationBar');
    var no = btn.getAttribute('data-no');
    var url = "/removeNotification/".concat(no);
    axios__WEBPACK_IMPORTED_MODULE_3__["default"].put(url).then(function (response) {
      if (response.data.message === 'Notification marked as read') {
        var _document$getElementB;
        // remove a html element with notificationBar after 2 mins
        (_document$getElementB = document.getElementById(bannerId)) === null || _document$getElementB === void 0 || _document$getElementB.remove();

        // reduce the notification count as you have deleted the notification
        var newValues = parseInt(sessionStorage.getItem('notificationCount') - 1);
        sessionStorage.setItem('notificationCount', newValues);
        (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = newValues;
      } else {
        (0,_shared__WEBPACK_IMPORTED_MODULE_1__.msgException)('Error removing notification' + ' ' + response.data.message);
      }
      // your counter routine
    });
  });
};

/* safe entry point */
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', initDeleteOnce) : initDeleteOnce();

///member/notifications

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   friendRequestCard: () => (/* binding */ friendRequestCard)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");

var appUrl = "https://olaogun.test/";
var approverId = encodeURIComponent(localStorage.getItem('requesterId'));
var friendRequestCard = function friendRequestCard(data) {
  var _data$img, _data$firstName, _data$lastName, _data$id, _data$famCode;
  var imageUrl = "/resources/images/profile/".concat(encodeURIComponent((_data$img = data.img) !== null && _data$img !== void 0 ? _data$img : data.requesterProfileImg));
  var firstName = encodeURIComponent((_data$firstName = data.firstName) !== null && _data$firstName !== void 0 ? _data$firstName : data.requesterFirstName);
  var lastName = encodeURIComponent((_data$lastName = data.lastName) !== null && _data$lastName !== void 0 ? _data$lastName : data.requesterLastName);
  var requestId = encodeURIComponent((_data$id = data.id) !== null && _data$id !== void 0 ? _data$id : data.requesterId);
  var requestCode = encodeURIComponent((_data$famCode = data.famCode) !== null && _data$famCode !== void 0 ? _data$famCode : data.requesterFamCode);
  var mutualFriends = '2 mutual friends';
  var html = "<p id=".concat(requestId, "_linkRequestCard></p>\n\n    <div class=\"d-flex align-items-center mb-3 friend-request-card\">\n      <img src=\"").concat(imageUrl, "\" alt=\"Avatar\" class=\"avatar me-3><br>\n\n        <div class=\"flex-grow-1\">\n          <h6 class=\"mb-0\">").concat(firstName, " ").concat(lastName, "</h6>\n          <small class=\"text-muted\">").concat(mutualFriends, "</small>\n        </div>\n    </div>\n\n    <div class=\"friend-request-actions mb-3\">\n\n              <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/50/").concat(requestCode, "/pp\" class=\"btn btn-sm btn-primary\" title=\"confirm\">Confirm</a>\n\n\n              <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/10\" class=\"btn btn-sm btn-outline-secondary\" title=\"Decline\">Decline</a>\n\n    </div>\n  ");
  (0,_shared__WEBPACK_IMPORTED_MODULE_0__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', html);
};

/***/ })

}]);
//# sourceMappingURL=all_members.js.map