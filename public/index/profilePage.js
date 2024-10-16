"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["profilePage"],{

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

            // rid it off the submit and token
            if (post.name == 'submit' || post.name == 'button' || post.id == 'button' || post.type == 'button' || post.id == 'showPassword_id' || post.id == 'g-recaptcha-response' || post.name == 'cancel' || post.name == "checkbox_id") {
              continue;
            }

            // check if there is no value

            var postName = post.name.replace('_', ' ');
            if (postName == "spouseName" || postName == "spouseMobile" || postName == "spouseEmail" || postName == "fatherMobile" || postName == "fatherEmail" || postName == "motherMobile" || postName == "maidenName" || postName == "motherEmail") {
              if (post.value === "") {
                post.value = "Not Provided";
              }
            }
            var asterisk = "*";
            if (post.value === '' || post.value === 'select') {
              // CHECK IF ERRMSG EXISTS BEFORE SETTING THE INNERHTML
              if (errMsg) {
                var _post$placeholder;
                errMsg.innerHTML = "".concat((_post$placeholder = post.placeholder) !== null && _post$placeholder !== void 0 ? _post$placeholder : asterisk, " cannot be left empty");
                errMsg.style.color = "red";
              }
              _this.error.push("".concat(postName.toUpperCase(), " cannot be left empty"));
            } else {
              _this.result = 1;
            }
            var checkRegex = (0,_helper_general__WEBPACK_IMPORTED_MODULE_0__.matchRegex)(post.value);
            if (checkRegex === false) {
              _this.error.push("There is a problem with your entry for ".concat(postName.toUpperCase(), "'s question"));
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

/***/ "./resources/asset/js/components/allMembers/api.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/api.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderMembers: () => (/* binding */ renderMembers)
/* harmony export */ });
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
/* harmony import */ var _filterMembersByFamCode__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./filterMembersByFamCode */ "./resources/asset/js/components/allMembers/filterMembersByFamCode.js");
/* harmony import */ var _handleInput__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./handleInput */ "./resources/asset/js/components/allMembers/handleInput.js");





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
var allMembersContainer = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('allMembers');
var noMemberHTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";
var renderMembers = function renderMembers(data, container, noMemberMessage, html) {
  container.innerHTML = "";
  if (data) {
    data.forEach(html);
  } else {
    container.innerHTML = noMemberMessage;
  }
};
axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("".concat(URL, "allMembers/processApiData?id=").concat(reqId), config).then(function (response) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('allMembers').innerHTML = "";
  if (!response.data) {
    throw Error('There is no data');
  }
  if (!famCode) {
    throw Error('There is no famCode');
  }
  var data = response.data;
  var dataWithFamCode = (0,_filterMembersByFamCode__WEBPACK_IMPORTED_MODULE_2__.filterMembersByFamCode)(data, famCode);
  renderMembers(dataWithFamCode, allMembersContainer, noMemberHTML, _html__WEBPACK_IMPORTED_MODULE_1__.renderHtml);

  // Remove the "loader" class after rendering is complete
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('setLoader').classList.remove('loader');
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('searchFamily').addEventListener('input', function () {
    return (0,_handleInput__WEBPACK_IMPORTED_MODULE_3__.handleInput)(data, dataWithFamCode, renderMembers);
  });
})["catch"](function (err) {
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(err.message);
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
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }


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
    var theImg = "/public/img/profile/".concat(el.img);
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
var toSentenceCase = function toSentenceCase(str) {
  return str.toLowerCase() // Convert the string to lowercase
  .split(' ') // Split the string into words
  .map(function (word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
  }) // Capitalize the first letter of each word
  .join(' '); // Join the words back into a string
};

/***/ }),

/***/ "./resources/asset/js/components/helper/http.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/helper/http.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkCookie: () => (/* binding */ checkCookie),
/* harmony export */   getApiData: () => (/* binding */ getApiData),
/* harmony export */   getCookie: () => (/* binding */ getCookie),
/* harmony export */   getMultipleApiData: () => (/* binding */ getMultipleApiData),
/* harmony export */   postFormData: () => (/* binding */ postFormData),
/* harmony export */   setCookie: () => (/* binding */ setCookie)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios-retry */ "./node_modules/axios-retry/lib/esm/index.js");
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }



// import Cookies from 'js-cookie'

(0,axios_retry__WEBPACK_IMPORTED_MODULE_1__["default"])(axios__WEBPACK_IMPORTED_MODULE_2__["default"], {
  retries: 3
});

/**
 * 
* Sends form data via POST request.
 * @param {string} url - The URL to post the data to.
 * @param {string} formId - The ID or class of the form.
 * @param {string|null} redirect - The page to redirect to after successful submission.
 * @param {string|null} css - The CSS framework to use for notification styling (e.g., 'W3css', 'bulma').
 NOTICE:::Make sure you set the notification id as the formId_notification
 */
var postFormData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(url, formId) {
    var redirect,
      css,
      notificationForm,
      notificationId,
      form,
      formEntries,
      options,
      response,
      successClass,
      idSetFromHttp,
      famCodeSetFromHttp,
      dbHttpResult,
      _ref2,
      _error$response$data$,
      _error$response,
      _error$message,
      errorClass,
      errorMessage,
      _args = arguments;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          redirect = _args.length > 2 && _args[2] !== undefined ? _args[2] : null;
          css = _args.length > 3 && _args[3] !== undefined ? _args[3] : null;
          notificationForm = "".concat(formId, "_notification");
          notificationId = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(notificationForm); // extract the form entries
          form = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(formId);
          formEntries = new FormData(form);
          formEntries["delete"]('submit');
          formEntries["delete"]('checkbox_id');
          // formEntries.delete('token')
          options = {
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN'
          }; // AXIOS POST FUNCTIONALITY
          _context.prev = 9;
          _context.next = 12;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url, formEntries, options);
        case 12:
          response = _context.sent;
          successClass = getNotificationClassByCSS("bulma", 'green'); // check if response.data.message is an array
          idSetFromHttp = null;
          famCodeSetFromHttp = null;
          dbHttpResult = null;
          if (!(_typeof(response.data.message) === 'object')) {
            _context.next = 29;
            break;
          }
          idSetFromHttp = response.data.message.id;
          famCodeSetFromHttp = response.data.message.famCode;
          dbHttpResult = response.data.message.outcome;

          // check if idSetFromHttp is null, then throw error
          if (idSetFromHttp) {
            _context.next = 23;
            break;
          }
          throw new Error('idSetFromHttp is null');
        case 23:
          if (dbHttpResult) {
            _context.next = 25;
            break;
          }
          throw new Error('dbHttpResult is null');
        case 25:
          if (famCodeSetFromHttp) {
            _context.next = 27;
            break;
          }
          throw new Error('famCodeSetFromHttp is null');
        case 27:
          _context.next = 30;
          break;
        case 29:
          dbHttpResult = response.data.message;
        case 30:
          // Set sessionStorage items if not already set
          if (!sessionStorage.getItem('idSetFromHttp')) sessionStorage.setItem('idSetFromHttp', idSetFromHttp);
          if (!sessionStorage.getItem('famCodeSetFromHttp')) sessionStorage.setItem('famCodeSetFromHttp', famCodeSetFromHttp);
          processFormDataAction(successClass, dbHttpResult, notificationId);
          if (redirect) {
            setTimeout(function () {
              window.location.assign(redirect);
            }, 2000);
          }
          _context.next = 41;
          break;
        case 36:
          _context.prev = 36;
          _context.t0 = _context["catch"](9);
          errorClass = getNotificationClassByCSS(css, 'red');
          errorMessage = (_ref2 = (_error$response$data$ = _context.t0 === null || _context.t0 === void 0 || (_error$response = _context.t0.response) === null || _error$response === void 0 || (_error$response = _error$response.data) === null || _error$response === void 0 ? void 0 : _error$response.message) !== null && _error$response$data$ !== void 0 ? _error$response$data$ : _context.t0 === null || _context.t0 === void 0 || (_error$message = _context.t0.message) === null || _error$message === void 0 ? void 0 : _error$message.message) !== null && _ref2 !== void 0 ? _ref2 : _context.t0 === null || _context.t0 === void 0 ? void 0 : _context.t0.message; // Process the form data for error
          processFormDataAction(errorClass, errorMessage, notificationId);
        case 41:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[9, 36]]);
  }));
  return function postFormData(_x, _x2) {
    return _ref.apply(this, arguments);
  };
}();

/**
 * Process form data action.
 * @param {string} cssClass - The CSS class for the notification.
 * @param {string} message - The notification message.
 */
var processFormDataAction = function processFormDataAction(cssClass, message, formNotificationId) {
  if (formNotificationId) {
    formNotificationId.style.display = 'block';
    formNotificationId.classList.add(cssClass);
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').scrollIntoView({
      behavior: 'smooth'
    });
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = message;
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('setLoader').classList.remove('loader');
  } else {
    throw new Error('NOTIFICATION NOT FOUND');
  }
};

/**
 * Get the notification class based on the CSS framework.
 * @param {string|null} css - The CSS framework to use for notification styling.
 * @param {string} status - The status of the notification ('green' or 'red').
 * @returns {string} - The corresponding CSS class.
 */
var getNotificationClassByCSS = function getNotificationClassByCSS(css, status) {
  switch (css) {
    case 'W3css':
      return status === 'green' ? 'w3-green' : 'w3-red';
    case 'bulma':
      return status === 'green' ? 'is-success' : 'is-danger';
    case 'bootstrap':
      return status === 'green' ? 'bg-success' : 'bg-danger';
    default:
      return status === 'green' ? 'bg-success' : 'bg-danger';
  }
};

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
  var _ref3 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2(URL) {
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
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(URL, config);
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
    return _ref3.apply(this, arguments);
  };
}();
var getMultipleApiData = /*#__PURE__*/function () {
  var _ref4 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3(url1, url2) {
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
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].all([axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(url1, config), axios__WEBPACK_IMPORTED_MODULE_2__["default"].get(url2, config)]);
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
    return _ref4.apply(this, arguments);
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

/***/ "./resources/asset/js/components/helper/images.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/helper/images.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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

/***/ }),

/***/ "./resources/asset/js/components/profilePage/allEvents.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/allEvents.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");
/* harmony import */ var _post__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./post */ "./resources/asset/js/components/profilePage/post.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_4__);








try {
  // Enable pusher logging - don't include this in production

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
    var commentHtml = (0,_comment__WEBPACK_IMPORTED_MODULE_2__.commentHTML)(commentResponse);
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

      // Make the comment form to appear onclick. initcomment is the id of the comment button 
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
        alert("Please enter a comment before submitting");
      } else {
        // 1.
        axios__WEBPACK_IMPORTED_MODULE_5__["default"].post('/postCommentProfile', formEntries, options).then(function (response) {
          // 2. note. message returns the new post_no from the database

          axios__WEBPACK_IMPORTED_MODULE_5__["default"].get("/member/pp/comment/byNumber?commentNo=".concat(response.data.message)).then(function (res) {
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
      // get the requesterFamCode from the localStorage 
      var requesterFamCodeValue = localStorage.getItem('requesterFamCode');
      // Append the new form entry to the FormData object
      formData.append('postFamCode', requesterFamCodeValue);

      // 3. 
      axios__WEBPACK_IMPORTED_MODULE_5__["default"].post("/member/profilePage/post", formData, options).then(function (response) {
        //  4. 
        axios__WEBPACK_IMPORTED_MODULE_5__["default"].get("/post/getAllPost/byNumber?postNo=".concat(response.data.message)).then(function (res) {
          // 5. 
          (0,_post__WEBPACK_IMPORTED_MODULE_3__.appendNewPost)(res.data.message);

          // Pusher(res.data.message)
        });
        // Enable pusher logging - don't include this in production

        var channel = pusher.subscribe('my-channel');
        channel.bind('updatePost', function (data) {
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)("checking1");
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(data.message);
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)("checking");
        });
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'none';
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("formPostMessageModal").reset();
      });
    }
  };
} catch (e) {
  showError(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/comment.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/comment.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   commentHTML: () => (/* binding */ commentHTML),
/* harmony export */   showComment: () => (/* binding */ showComment)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");

var commentHTML = function commentHTML(data) {
  var imgURL = data.img ? data.img : data.profileImg;
  var img = imgURL ? "/public/img/profile/".concat(imgURL) : "/public/avatar/avatarF.png";
  return "<div class='w3-ul w3-border w3-round' id='comment".concat(data.comment_no, "' name='commentDiv'>\n            <div class='w3-container commentDiv'>\n              <img src='").concat(img, "' alt='Avatar' class='w3-left w3-circle w3-margin-right commentImg' style='width:50px; height:50px'>\n              <p class='w3-right w3-opacity commentTiming' datetime='").concat(data.date_created, "' title='").concat(data.date_created, "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(data.date_created), " </p> \n              <p class='commentFont'> ").concat(data.comment, "</p>\n            </div>\n          </div>");
};
var showComment = function showComment(comment) {
  if (!comment) {
    return "<div class=\"w3-ul w3-border\" id=\"comment\" name=\"commentDiv\"></div>";
  } // only run if there is comment

  var commentHTMLArray = comment.map(function (commentElement) {
    return commentHTML(commentElement);
  });
  return commentHTMLArray.join(''); // Join the array elements into a single string
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/createEvent.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/createEvent.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");







var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_1__["default"](formInputArr);
var displayNone = function displayNone() {
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'none';
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('cancelModal').addEventListener('click', displayNone);
var checkEventAndAdd = function checkEventAndAdd(data) {
  var appendEvent = (0,_eventHTML__WEBPACK_IMPORTED_MODULE_3__.eventHtml)(data);
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventList').insertAdjacentHTML('afterbegin', appendEvent);
};
var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};
var _process = function process(e) {
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

      axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/member/profilePage/event", eventFormEntries, options).then(function (response) {
        // use the event no to get the last event from the database

        axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/member/getEventDataByNo?eventNo=".concat(response.data.message)).then(function (res) {
          if (res.data.message) {
            // add new event real time
            checkEventAndAdd(res.data.message[0]);
          }
        });

        // POST THE EVENT TO NOTIFICATION

        axios__WEBPACK_IMPORTED_MODULE_4__["default"].post('/member/notification/event', eventFormEntries, options).then(function (result) {
          // GET THE POST EVENTS AND ADD THEM TO THE NOTIFICATION

          axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/member/notification/event?notificationNo=".concat(result.data.message)).then(function (result2) {
            (0,_navbar__WEBPACK_IMPORTED_MODULE_2__.addToNotificationTab)(result2.data.message[0]);
            (0,_navbar__WEBPACK_IMPORTED_MODULE_2__.increaseNotificationCount)();
          });
        });

        // now trigger push notifications subscription
        var registration = navigator.serviceWorker.ready;
        var subscription = registration.pushManager.getSubscription();
        if (!subscription) {
          var newSubscription = registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: _process.env.MIX_VAPID_PUBLIC_KEY
          });
          console.log('New Push Subscription:', newSubscription);
        } else {
          console.log('Already subscribed for push notifications:', subscription);
        }
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
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitEventModal').addEventListener('click', _process);

/***/ }),

/***/ "./resources/asset/js/components/profilePage/eventHTML.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/eventHTML.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   eventHtml: () => (/* binding */ eventHtml)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
"use static";


var eventHtml = function eventHtml(data) {
  return "<p class='eventInfo'>\n            <strong>RSVP: </strong> ".concat(data.firstName, " ").concat(data.lastName, "</p>\n            <p class='eventInfo'><strong>Event: </strong>").concat(data.eventName, "</p>\n            <p class='eventInfo'><strong>Date: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.date2String)(data.eventDate), "</p>\n            <p class='eventInfo'><strong>Type: </strong>").concat(data.eventType, "</p>\n            <p class='eventInfo'><strong>Description: </strong> ").concat(data.eventDescription, "</p>\n            <input type='hidden' name='event_no' id='event").concat(data.no, "' value='").concat(data.no, "'>\n            \n           <hr>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/friendRequestCard.js":
/*!************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/friendRequestCard.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


var appUrl = "http://olaogun.test/";
var approver_id = sessionStorage.getItem('idSetFromHttp');
axios__WEBPACK_IMPORTED_MODULE_1__["default"].get("/getFriendRequestById?id=".concat(approver_id)).then(function (results) {
  if (results.data.message) {
    results.data.message.forEach(function (request) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', htmlFriendRequest(request));
    });
  }
});

// const countFriendRequest = (friend) => {
//   return friend.length > 1 ? `<p><b>Friend Requests</b></p><br></br>` : `<p><b>Friend Request</b></p><br>`;
// }

var imgFriendRequest = function imgFriendRequest(data) {
  return "<img src=\"/public/img/profile/".concat(data.img, "\" alt=\"Avatar\" style=\"width:50%\"><br>");
};
var buttonFriendRequest = function buttonFriendRequest(data) {
  return "<div class=\"w3-row w3-opacity\" >\n        <div class=\"w3-half\">\n                <a href=".concat(appUrl, "member/request?req=").concat(data.id, "&appr=").concat(approver_id, "&dec=50&reqCode=").concat(data.famCode, "&src=pp  style=\"text-decoration: none;\"> \n                    <button class=\"w3-button w3-block w3-green w3-section\" title=\"Accept\"><i class=\"fa fa-check\"></i>\n                  </button>\n                </a>\n        </div>\n\n        <div class=\"w3-half\">\n                    <a href=").concat(appUrl, "member/request?req=").concat(data.id, "&appr=").concat(approver_id, "&dec=10>\n                      <button class=\"w3-button w3-block w3-red w3-section\" title=\"Decline\"><i class=\"fa fa-remove\"></i></button>\n                    </a>\n        </div>\n      </div>");
};
var name = function name(data) {
  return "<span>".concat(data.firstName, " ").concat(data.lastName, "</span>");
};
var htmlFriendRequest = function htmlFriendRequest(data) {
  return "\n    <p id=".concat(data.id, "_linkRequestCard></p>\n    ").concat(imgFriendRequest(data), "\n    ").concat(name(data), "\n    ").concat(buttonFriendRequest(data), "\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin\"><br>\n\n      ".concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(el), "\n\n    <hr class=\"w3-clear\">\n\n    <p class=\"postFont\"> ").concat(el.postMessage, " </p>\n\n     ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(el), "\n\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(el), "\n\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(el), "\n\n    <div id = 'showComment").concat(el.post_no, "'>\n\n      ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment), "\n      \n    </div><br>\n  </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js":
/*!*****************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/commentForm.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   commentForm: () => (/* binding */ commentForm)
/* harmony export */ });
var commentForm = function commentForm(data) {
  return " <p id=\"formComment".concat(data.post_no, "_notification\"></p>\n\n  <form \n    action=\"/postCommentProfile\" \n    method=\"post\" id=\"formComment").concat(data.post_no, "\" \n    style=\"display:none\" \n    enctype=\"multipart/form-data\">\n\n    <input \n      name='post_no' \n      type=\"hidden\" \n      name=\"").concat(data.post_no, "\" \n      value=").concat(data.post_no, " />\n\n    <input \n      class=\"w3-input w3-border w3-round-large inputComment\" \n      type=\"text\" \n      placeholder=\"Write a comment\"\n      id=\"inputComment").concat(data.post_no, "\" \n      value = \"\" name='comment'>\n\n    <br>\n\n    <button \n      type='submit' \n      id=\"submitComment").concat(data.post_no, "\" \n      class=\"w3-button w3-green submitComment\">\n        Submit\n    </button>\n    \n    <br><br>\n  </form>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   likeCommentButton: () => (/* binding */ likeCommentButton)
/* harmony export */ });
var likeCommentButton = function likeCommentButton(data) {
  return "\n  <button \n    type=\"button\" \n    id=\"likeButton".concat(data.post_no, "\" \n    name=\"").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-green w3-margin-bottom\">\n    <em class=\"fa fa-thumbs-up\"></em>\n    \xA0   Like \n      <b>\n        <span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">\n          ").concat(data.post_likes, "\n        </span>\n      </b>\n  </button>\n\n   <button \n    type=\"button\" \n    id=\"initComment").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-theme-d2 w3-margin-bottom\">\n      <em class=\"fa fa-comment\"></em> \n        Comment \n    </button>\n    ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js":
/*!*********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   nameImgTiming: () => (/* binding */ nameImgTiming)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");

var timeAgo = function timeAgo(x) {
  return (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x);
};
var fullName = function fullName(_fullName) {
  return "<h6 id=\"fullName\"><b>".concat(_fullName, "</b> </h6>");
};
var postedAt = function postedAt(date) {
  return "<div class=\"timeago postTimeCal w3-right w3-opacity\"  datetime='".concat(date.date_created, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date.date_created), "'> ").concat(timeAgo(date.post_time), "</div>");
};
var nameImgTiming = function nameImgTiming(data) {
  var img = data.img ? "/public/img/profile/".concat(data.img) : "/public/avatar/avatarF.png";
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(data.img, "&pID=").concat(data.post_no, "&path=profile&id=").concat(data.id, "\"> <img src=").concat(img, " alt=\"img\" class=\"w3-left w3-circle w3-margin-right postImg\" style=\"width:60px\">\n        </a>\n        ").concat(postedAt(data), " ").concat(fullName(data.fullName));
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
    return "\n    <a href=\"/profilepage/img?dir=img&pics=".concat(imgElement, "&pID=").concat(postNo, "&path=post\">\n      <div class=\"w3-half\">\n        <img src=\"/public/img/post/").concat(imgElement, "\" style=\"width:100%\" alt=\"images").concat(i, "\" class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat(i, "\">\n      </div>\n    </a>\n  ");
  };
  var imgElements = postImagesWithValues.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }).join('');
  return "\n    <div class=\"w3-row-padding\" style=\"margin:0 -16px\">\n      ".concat(imgElements, "\n      <br>\n    </div>\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/img.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/img.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_images__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/images */ "./resources/asset/js/components/helper/images.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");





(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePics').addEventListener('click', function () {
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formProfilePics').style.display = "block";
});

// FOR PROFILE IMAGE CHANGE
(0,_helper_images__WEBPACK_IMPORTED_MODULE_1__.showImageFileUploadFn)('uploadButtonProfilePics', 'profileImageFile', 'profileImgFileNames');

// FOR POST MODAL IMAGE UPLOAD  

(0,_helper_images__WEBPACK_IMPORTED_MODULE_1__.showImageFileUploadFn)('uploadButton', 'post_img', 'postModalImgFileNames');
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitProfilePics').addEventListener('click', function () {
  // Get the form element
  var form = document.getElementById("formProfilePics");

  // Create a FormData object and append the form data to it
  var formData = new FormData(form);
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };
  // send form data using axios post method

  axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/member/profilePage/profileImg', formData, options).then(function (response) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').innerHTML = response.data;
    if (response.data === "Profile image updated") {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').classList.add('w3-green');
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').innerHTML = response.data;
      // Reload the page
      location.reload();
    }
  })["catch"](function (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').classList.add('w3-red');
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').innerHTML = error.message;
  });
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').innerHTML = "";
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
/* harmony import */ var _friendRequestCard__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./friendRequestCard */ "./resources/asset/js/components/profilePage/friendRequestCard.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
// "use strict";
localStorage.removeItem('redirect');








/***/ }),

/***/ "./resources/asset/js/components/profilePage/loadPost.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/loadPost.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _post__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./post */ "./resources/asset/js/components/profilePage/post.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");





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
      return (0,_post__WEBPACK_IMPORTED_MODULE_1__.allPost)(data, state.comment);
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
      if (e.origin != "http://olaogun.test/") {
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
        return (0,_post__WEBPACK_IMPORTED_MODULE_1__.appendNewPost)(ele);
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
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/post.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/post.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   allPost: () => (/* binding */ allPost),
/* harmony export */   appendNewPost: () => (/* binding */ appendNewPost)
/* harmony export */ });
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _allMembers_api__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../allMembers/api */ "./resources/asset/js/components/allMembers/api.js");
/* harmony import */ var _allMembers_filterMembersByFamCode__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../allMembers/filterMembersByFamCode */ "./resources/asset/js/components/allMembers/filterMembersByFamCode.js");




S;
var allPost = function allPost(el, commentData) {
  if (!el) {
    return false;
  }
  var postNo = parseInt(el.post_no);
  var filterComment = commentData.filter(function (comm) {
    return postNo === parseInt(comm.post_no);
  }); // filter the comment to an array

  var postHtml = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(el, filterComment);
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
    var appendHTML = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(el);
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('afterbegin', appendHTML);
  }
};

/***/ })

}]);