"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["all_members"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ FormHelper; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");




function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }

var FormHelper = /*#__PURE__*/function () {
  function FormHelper(data) {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, FormHelper);
    if (!Array.isArray(data)) throwError('data must be an array of form elements');
    this.data = data;
    this.error = [];
    this.result = 0;
  }
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(FormHelper, [{
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

            if (['spouse_name', 'spouse_mobile', 'spouse_email', 'father_mobile', 'father_email', 'mother_mobile', 'maiden_name', 'mother_email'].includes(post.name)) {
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
      var emailEl = this.id('email_id') || this.id('email');
      if (!emailEl) return;
      var email = emailEl.value;
      if (email.match(emailExp) === null) {
        var errEl = this.id('email_error');
        if (errEl) {
          errEl.innerHTML = msg;
          errEl.style.color = "red";
        }
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
              console.log("Adding listeners to element: ".concat(id));
              // Add keyup event listener to clear errors for non-select inputs
              the_id.addEventListener('keyup', function () {
                if (value !== 'select') {
                  clearErrorForElement(name);
                }
              });

              // Add change event listener to clear error message
              the_id.addEventListener('change', function () {
                clearErrorForElement(name);
              });
            } else {
              console.warn("Element with ID '".concat(id, "' with post name '").concat(post.name, "' not found."));
            }
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
          if (!theData) theData = _this3.id(input[i]);
          if (theData === null || theData === undefined || theData === "") {
            console.warn("Element with ID '".concat(input[i], "' not found or is empty"));
            return 1; // continue
          }
          var max = maxi[i];
          var error = _this3.id("".concat(input[i], "_error"));
          theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
          theData.addEventListener('keyup', function () {
            if (error) {
              error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
              error.style.color = 'red';
            }
            var help = _this3.id("".concat(input[i], "_help"));
            if (help) {
              help.style.color = 'red';
              help.style.fontSize = '10px';
              setTimeout(function () {
                help.style.display = 'none';
              }, 5000);
            }
          });
        };
        for (var i = 0; i < input.length; i++) {
          if (_loop2(i)) continue;
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
      firstInput = this.id(first + '_id') || this.id(first);
      secondInput = this.id(second + '_id') || this.id(second);
      if (firstInput && secondInput) {
        secondInput.addEventListener('keyup', function () {
          if (error) error.innerHTML = secondInput.value !== firstInput.value ? 'Your passwords do not match' : "";
        });
      }
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
      giver = this.id(giveInput) || this.id(giveInput.replace('_id', ''));
      taker = this.id(takeInput) || this.id(takeInput.replace('_id', ''));
      if (giver && taker) {
        giver.addEventListener('keyup', function () {
          taker.value = giver.value;
        });
      }
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global.js */ "./resources/asset/js/components/global.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _profilePage_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../profilePage/htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");








// Attach a click event listener to the document
var reqId = localStorage.getItem('requesterId');
/**
 * Attach a click event listener to the document. When a button with the id `addFamily<userId>` is clicked, send a family request to the user identified by the userId and update the button's HTML and disable it.
 it returns the notification details for the approvers tab
 * 
 * @param {MouseEvent} e - The event object.
 */
document.onclick = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
    var targetId, userId, approverDetails, familyRequestData, result, _userId, url, notificationHTML, response, _userId2, _userId3, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          // Get the target element's ID
          targetId = e.target.id; // Check if the ID includes 'addFamily'
          if (!targetId.includes('addFamily')) {
            _context.next = 3;
            break;
          }
          // Extract the user ID from the target ID
          userId = targetId.replace('addFamily', ''); // Fetch approver details for the user
          _context.next = 1;
          return fetchApproverData(userId);
        case 1:
          approverDetails = _context.sent;
          familyRequestData = {
            approver: approverDetails,
            emailPath: 'msg/request_premium'
          }; // Send the family request data to the server for processing which returns the notification details for the approvers tab
          _context.next = 2;
          return sendFamilyRequest(familyRequestData);
        case 2:
          result = _context.sent;
          if (result.data.status === 'success' && result.data.message === 'Request sent') {
            // Update the button's HTML and disable it
            updateButton(targetId, 'Request Sent');
          } else if (result.data.status === 'error' && result.data.message === 'Request already pending') {
            // Update the button's HTML and disable it
            updateButton(targetId, 'Request Pending');
          } else {
            // Update the button's HTML and disable it
            updateButton(targetId, 'Request Failed');
          }
          _context.next = 7;
          break;
        case 3:
          if (!targetId.includes('removeProfile')) {
            _context.next = 6;
            break;
          }
          // Extract the user ID from the target ID
          _userId = targetId.replace('removeProfile', '');
          url = "/allMembers/removeProfile/".concat(_userId, "/").concat(reqId);
          alert(url);

          // include a console to confirm if they truly want to delete the profile
          if (!confirm('You will no longer see the profile and associated posts. Are you sure you want to delete the profile?')) {
            _context.next = 5;
            break;
          }
          notificationHTML = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.qSel)(".member_profile_".concat(_userId));
          _context.next = 4;
          return axios__WEBPACK_IMPORTED_MODULE_6__["default"].delete(url);
        case 4:
          response = _context.sent;
          if (response.data.message === 'success') {
            // remove a html element with call member_profile
            notificationHTML.remove();
          } else {
            (0,_shared__WEBPACK_IMPORTED_MODULE_2__.msgException)("Error deleting profile");
          }
        case 5:
          _context.next = 7;
          break;
        case 6:
          if (targetId.includes('seeProfile')) {
            // Extract the user ID from the target ID
            _userId2 = targetId.replace('seeProfile', ''); // redirect to 'allMembers/setProfile/'+userId
            window.location.href = "/allMembers/seeProfile/".concat(_userId2);
          } else if (targetId.includes('familyTree')) {
            // Extract the user ID from the target ID
            _userId3 = targetId.replace('familyTree', ''); // redirect to 'allMembers/setProfile/'+userId
            window.location.href = "/organogram/".concat(_userId3);
          }
        case 7:
          _context.next = 9;
          break;
        case 8:
          _context.prev = 8;
          _t = _context["catch"](0);
          // Handle any errors that occur during execution
          (0,_shared__WEBPACK_IMPORTED_MODULE_2__.showError)(_t);
        case 9:
        case "end":
          return _context.stop();
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
} // Function to send family request data to the server
function _fetchApproverData() {
  _fetchApproverData = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(userId) {
    var result, approverDetails, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_6__["default"].get("/members/familyRequestMgt/getApprover?id=".concat(userId));
        case 1:
          result = _context2.sent;
          approverDetails = {
            approverFirstName: result.data.message.firstName,
            approverLastName: result.data.message.lastName,
            approverEmail: result.data.message.email,
            approverId: result.data.message.id,
            approverCode: result.data.message.famCode
          };
          return _context2.abrupt("return", approverDetails);
        case 2:
          _context2.prev = 2;
          _t2 = _context2["catch"](0);
          console.error(_t2);
          throw _t2;
        case 3:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 2]]);
  }));
  return _fetchApproverData.apply(this, arguments);
}
function sendFamilyRequest(data) {
  try {
    return axios__WEBPACK_IMPORTED_MODULE_6__["default"].post('/members/familyRequestMgt', data);
  } catch (error) {
    (0,_shared__WEBPACK_IMPORTED_MODULE_2__.showError)(error);
  }
}

// Function to update the button's HTML and disable it
function updateButton(targetId, newHTML) {
  var theTargetId = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.id)(targetId);
  theTargetId.innerHTML = newHTML;
  theTargetId.disabled = true;
}

// ADD THE NEW EVENT TO THE NOTIFICATION TAB

/***/ }),

/***/ "./resources/asset/js/components/allMembers/api.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/api.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderMembers: function() { return /* binding */ renderMembers; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
/* harmony import */ var _handleInput__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./handleInput */ "./resources/asset/js/components/allMembers/handleInput.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");


// resources/js/allMembers/api.js



var URL = "https://olaogun.test/";
var allMembersContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("allMembers"); // main container TO SHOW THE MEMBERS
var memberCountBadge = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("memberCount"); // member count badge
var searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("searchFamily"); // search input

var NO_MEMBER_HTML = "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";

/**
 * Render a list of members into the main container.
 * Also updates the member count badge.
 *
 * @param {Array<object>} members
 */
var renderMembers = function renderMembers() {
  var members = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
  allMembersContainer.innerHTML = "";

  // if no members, show no member html
  if (!members.length) {
    allMembersContainer.innerHTML = NO_MEMBER_HTML;
    memberCountBadge.textContent = "0 Members";
    return;
  }

  // render each member
  members.forEach(_html__WEBPACK_IMPORTED_MODULE_2__.renderHtml);

  // update member count badge
  memberCountBadge.textContent = members.length === 1 ? "1 Member" : "".concat(members.length.toLocaleString(), " Members");
};
(function () {
  var _bootstrapAllMembers = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var _famCodeData$message, url, famCodeData, familyMembers, loader, handleSearch, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          url = "".concat(URL, "allMembers/processApiData"); // network (family + approved)
          _context.next = 1;
          return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.getApiData)(url);
        case 1:
          famCodeData = _context.sent;
          familyMembers = (_famCodeData$message = famCodeData === null || famCodeData === void 0 ? void 0 : famCodeData.message) !== null && _famCodeData$message !== void 0 ? _famCodeData$message : []; // Pre-render: show only the user's network
          renderMembers(familyMembers);

          // remove loader
          loader = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("setLoader");
          if (loader) loader.classList.remove("loader");

          // Wire up debounced search handler
          if (searchInput) {
            handleSearch = (0,_handleInput__WEBPACK_IMPORTED_MODULE_3__.createSearchHandler)({
              familyMembers,
              renderMembers,
              container: allMembersContainer,
              searchUrl: "".concat(URL, "allMembers/search")
            });
            searchInput.addEventListener("input", handleSearch);
          }
          _context.next = 3;
          break;
        case 2:
          _context.prev = 2;
          _t = _context["catch"](0);
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.showError)(_t);
        case 3:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 2]]);
  }));
  function bootstrapAllMembers() {
    return _bootstrapAllMembers.apply(this, arguments);
  }
  return bootstrapAllMembers;
})()();

/***/ }),

/***/ "./resources/asset/js/components/allMembers/handleInput.js":
/*!*****************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/handleInput.js ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createSearchHandler: function() { return /* binding */ createSearchHandler; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");


// resources/js/allMembers/handleInput.js




/**
 * Render the "invite a new member" block when there is no search match.
 *
 * @param {HTMLElement} container
 * @param {string} rawQuery
 */
var renderInviteBlock = function renderInviteBlock(container, rawQuery) {
  var famCode = localStorage.getItem("requesterFamCode") || "";
  var yourName = localStorage.getItem("yourName") || "";
  container.innerHTML = "\n    <p>No matching name found \u2013 do you want us to send them a text/email to register to the platform?</p>\n    ".concat((0,_shared__WEBPACK_IMPORTED_MODULE_3__.checkBox)("newMemberRequest"), " <br>\n\n    <input type=\"hidden\" id=\"newMemberName\" value=\"").concat(rawQuery, "\">\n\n    <input type=\"text\" id=\"newMemberRequestName\" class=\"form-control\"\n           name=\"newMemberRequestName\"\n           placeholder=\"Enter their name\">\n\n    <input type=\"text\" id=\"newMemberRequestEmail\" class=\"form-control\"\n           name=\"newMemberRequestEmail\"\n           placeholder=\"Enter their email address or mobile number\">\n\n    <p id=\"loader\" class=\"loader\" style=\"display:none;\"></p>\n    <small id=\"newMemberRequest_help\" class=\"form-text text-muted\"></small>\n\n    <button class=\"button is-primary\" id=\"newMemberRequestBtn\">Send Request</button>\n  ");
  var nameInput = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestName");
  var emailInput = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestEmail");
  var submitBtn = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestBtn");
  var helpMsg = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequest_help");

  // hide fields until checkbox ticked
  nameInput.style.display = "none";
  emailInput.style.display = "none";
  submitBtn.style.display = "none";
  var yesCheckbox = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestYes");
  if (yesCheckbox) {
    yesCheckbox.addEventListener("click", function () {
      nameInput.style.display = "block";
      emailInput.style.display = "block";
      submitBtn.style.display = "block";
    });
  }
  submitBtn.addEventListener("click", /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var emailOrMobile, name, mobileRegex, emailRegex, postObj, response, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          emailOrMobile = emailInput.value.trim();
          name = nameInput.value.trim();
          mobileRegex = /^\+?[1-9]\d{1,14}$/;
          emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // validation
          if (!(!emailRegex.test(emailOrMobile) && !mobileRegex.test(emailOrMobile))) {
            _context.next = 1;
            break;
          }
          helpMsg.textContent = "Please enter a valid email address or mobile number.";
          return _context.abrupt("return");
        case 1:
          if (!(mobileRegex.test(emailOrMobile) && !emailOrMobile.startsWith("+"))) {
            _context.next = 2;
            break;
          }
          helpMsg.textContent = "Please include the country code. E.g. +2348036517179";
          return _context.abrupt("return");
        case 2:
          if (!(emailRegex.test(emailOrMobile) && name.length < 2)) {
            _context.next = 3;
            break;
          }
          helpMsg.textContent = "Please enter a valid name with at least 2 characters.";
          return _context.abrupt("return");
        case 3:
          if (!emailRegex.test(emailOrMobile)) {
            _context.next = 7;
            break;
          }
          postObj = {
            mobile: "",
            viewPath: "msg/contactNewMember",
            data: {
              email: emailOrMobile,
              mobile: "",
              name,
              familyCode: famCode,
              yourName
            },
            subject: "".concat(yourName, " Wants You: Experience the Magic of your Family Network Today!")
          };
          _context.prev = 4;
          _context.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/register/contactNewMember", postObj);
        case 5:
          response = _context.sent;
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.showNotification)("allMembers", "is-success", response.data.message);
          helpMsg.textContent = "";
          _context.next = 7;
          break;
        case 6:
          _context.prev = 6;
          _t = _context["catch"](4);
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.showNotification)("allMembers", "is-danger", _t.message);
        case 7:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[4, 6]]);
  })));
};

/**
 * Factory that returns a debounced search handler using the backend /allMembers/search endpoint.
 *
 * @param {object} options
 * @param {Array<object>} options.familyMembers
 * @param {Array<object>} options.directoryMembers  // currently unused but handy if you want to fall back
 * @param {Function} options.renderMembers
 * @param {HTMLElement} options.container
 * @param {string} options.searchUrl  // e.g. `${URL}allMembers/search`
 * @returns {(e: InputEvent) => void}
 */
var createSearchHandler = function createSearchHandler(_ref2) {
  var familyMembers = _ref2.familyMembers,
    renderMembers = _ref2.renderMembers,
    container = _ref2.container,
    searchUrl = _ref2.searchUrl;
  var searchInput = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("searchFamily");
  var debounceTimer = null;
  var performSearch = /*#__PURE__*/function () {
    var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2() {
      var rawQuery, response, data, matches, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            rawQuery = searchInput.value.trim(); // THE SEARCH QUERY
            // empty query = back to my network
            if (rawQuery) {
              _context2.next = 1;
              break;
            }
            renderMembers(familyMembers);
            return _context2.abrupt("return");
          case 1:
            _context2.prev = 1;
            _context2.next = 2;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get(searchUrl, {
              params: {
                q: rawQuery
              }
            });
          case 2:
            response = _context2.sent;
            data = response.data || {};
            matches = data.message || [];
            if (matches.length) {
              _context2.next = 3;
              break;
            }
            renderInviteBlock(container, rawQuery);
            return _context2.abrupt("return");
          case 3:
            // backend already orders: family first, then approved, etc.
            container.innerHTML = "";
            renderMembers(matches);
            _context2.next = 5;
            break;
          case 4:
            _context2.prev = 4;
            _t2 = _context2["catch"](1);
            (0,_global__WEBPACK_IMPORTED_MODULE_2__.showNotification)("allMembers", "is-danger", "Search failed. Please try again.");
            // optional: log or surface more detail in dev builds
            (0,_global__WEBPACK_IMPORTED_MODULE_2__.msgException)(_t2);
          case 5:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[1, 4]]);
    }));
    return function performSearch() {
      return _ref3.apply(this, arguments);
    };
  }();
  return function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(performSearch, 200);
  };
};

/***/ }),

/***/ "./resources/asset/js/components/allMembers/html.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/html.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtml: function() { return /* binding */ renderHtml; }
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
  (0,_shared__WEBPACK_IMPORTED_MODULE_1__.log)(el);
  try {
    var _el$status;
    if (!el) {
      // Handle the case where 'el' is falsy, such as when data is not available.
      throw new Error('there is no data');
    }
    var theImg = "/resources/images/profile/".concat(el.img);
    var status = ((_el$status = el.status) === null || _el$status === void 0 ? void 0 : _el$status.toLowerCase()) || null;
    var statusButtonHTML = 'Connect';
    var tooltip = '';
    tooltip = status === 'rejected' ? 'You can send another request' : 'Send request';
    if (status === 'request sent') {
      statusButtonHTML = 'Request sent';
    }
    var disableButton = status === 'request sent' ? 'disabled' : '';

    // Create the HTML content based on whether the user is in the same family or not. // Refined card design
    var html = "\n    <div class=\"member-card member_profile_".concat(el.id, "\" id=\"").concat(el.id, "\">\n        <div class=\"card-cover\"></div>\n        \n        <div class=\"avatar-wrapper\">\n             <img src=\"").concat(el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image', "\"  alt=\"Member-").concat(el.firstName, "\" loading=\"lazy\">\n        </div>\n\n        <div class=\"member-card-body\">\n            <h4 class=\"member-name\">").concat(toSentenceCase(el.firstName), " ").concat(toSentenceCase(el.lastName), "</h4>\n            \n            <p class=\"member-location\"><i class=\"bi bi-geo-alt\"></i> ").concat(el.country, "</p>\n            \n            <div class=\"member-details\">\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-hash\"></i> \n                    <strong>").concat(el.famCode, "</strong>\n                </div>\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-envelope\"></i> \n                    <span class=\"text-truncate\">").concat(el.email, "</span>\n                </div>\n                ").concat(el.relationType !== 'other' ? "\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-link-45deg\"></i> \n                    <span>".concat(el.relationType, "</span>\n                </div>\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-calendar-check\"></i> \n                    <span>Since ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(el.created_at), "</span>\n                </div>\n                ") : '', "\n            </div>\n\n            <div class=\"member-interests\">\n                ").concat(el.relationType !== 'other' ? "\n                <button class=\"btn-profile\" id=\"seeProfile".concat(el.id, "\">\n                    <i class=\"bi bi-person\"></i> View Profile\n                </button>\n                <div class=\"d-flex gap-2\">\n                    <button class=\"btn-remove\" style=\"color: var(--primary-color); border-color: var(--accent-color);\" id=\"familyTree").concat(el.id, "\">\n                        <i class=\"bi bi-diagram-3\"></i> Tree\n                    </button>\n                    <button class=\"btn-remove\" id=\"removeProfile").concat(el.id, "\">\n                        <i class=\"bi bi-person-x\"></i> Remove\n                    </button>\n                </div>\n                ") : "\n                <button class=\"btn-profile\" \n                        data-user-id=\"addFamily".concat(el.id, "\" \n                        id=\"addFamily").concat(el.id, "\"\n                        ").concat(disableButton, ">\n                    <i class=\"bi bi-person-plus\"></i> ").concat(statusButtonHTML, "\n                </button>\n                <small class=\"text-muted\" style=\"font-size: 0.75rem; font-weight: 500;\">").concat(tooltip, "</small>\n                "), "\n            </div>\n        </div>\n    </div>\n");
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./api */ "./resources/asset/js/components/allMembers/api.js");
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/allMembers/allEvents.js");



/***/ }),

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autoCompleter: function() { return /* binding */ autoCompleter; },
/* harmony export */   checkBox: function() { return /* binding */ checkBox; },
/* harmony export */   checkBox2: function() { return /* binding */ checkBox2; },
/* harmony export */   convertFormData: function() { return /* binding */ convertFormData; },
/* harmony export */   createAndAppendElement: function() { return /* binding */ createAndAppendElement; },
/* harmony export */   distinctValue: function() { return /* binding */ distinctValue; },
/* harmony export */   isChecked: function() { return /* binding */ isChecked; },
/* harmony export */   loaderIcon: function() { return /* binding */ loaderIcon; },
/* harmony export */   loaderIconBootstrap: function() { return /* binding */ loaderIconBootstrap; },
/* harmony export */   loaderIconBulma: function() { return /* binding */ loaderIconBulma; },
/* harmony export */   matchInput: function() { return /* binding */ matchInput; },
/* harmony export */   matchRegex: function() { return /* binding */ matchRegex; },
/* harmony export */   realTimeCheckLen: function() { return /* binding */ realTimeCheckLen; },
/* harmony export */   removeDiv: function() { return /* binding */ removeDiv; },
/* harmony export */   showResponse: function() { return /* binding */ showResponse; },
/* harmony export */   toSentenceCase: function() { return /* binding */ toSentenceCase; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! autocompleter */ "./node_modules/autocompleter/autocomplete.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(autocompleter__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");






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
  if (setClass) newDiv.className = "field ".concat(setClass);else newDiv.className = "field ".concat(setId);
  var parentDiv = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(parent);
  if (!parentDiv) throw new Error("Parent element '".concat(parent, "' not found"));
  return parentDiv.appendChild(newDiv);
};

/**
 * 
 * @param {the id of the input} inputId 
 * @param {the api data or array data} data 
 * @param { filterby is the data.filterby }
 */
var autoCompleter = function autoCompleter(inputId, data) {
  autocompleter__WEBPACK_IMPORTED_MODULE_2___default()({
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
  return (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(new Set(array));
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
    if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(yesId).checked) {
      alert('check');
      fn();
    } else if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(noId).checked) {
      alert('check No');
    }
  };
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(yesId).addEventListener('click', checked);
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(noId).addEventListener('click', checked);
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
  error = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(err);
  firstInput = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(first);
  secondInput = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(second);
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
  var formInput = (0,_global__WEBPACK_IMPORTED_MODULE_1__.qSelAll)(formId);
  var formInputArr = Array.from(formInput);
  return new _FormHelper__WEBPACK_IMPORTED_MODULE_3__["default"](formInputArr);
};
var showResponse = function showResponse(theId, message, status) {
  var responseEl = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(theId);
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
      var theData = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(input[i], "_id"));
      if (theData === null || theData === undefined || theData === "") {
        throw new Error("Element with ID '".concat(input[i], "_id' not found or is empty"));
      }
      var max = maxi[i];
      var error = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(input[i], "_error"));
      theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
      theData.addEventListener('keyup', function () {
        error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
        var help = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(input[i], "_help"));
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToNotificationTab: function() { return /* binding */ addToNotificationTab; },
/* harmony export */   increaseNotificationCount: function() { return /* binding */ increaseNotificationCount; }
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
    default: {
      icon: "fa-solid fa-bell",
      color: "text-secondary"
    } // Grey
  };
  var _ref = iconMap[data.notification_type] || iconMap.default,
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

if (yourId && famCode && yourId !== 'null' && famCode !== 'null') {
  axios__WEBPACK_IMPORTED_MODULE_3__["default"].get(notificationURL).then(function (res) {
    // Extract the notifications from the response
    var data = res.data.message;
    if (data) {
      if (data.length > 0) {
        // Display the count of notifications
        var countBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
        if (countBadge) {
          countBadge.innerHTML = data.length;
          countBadge.style.display = 'flex';
        }

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
        var _countBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
        if (_countBadge) {
          _countBadge.innerHTML = '0';
          _countBadge.style.display = 'none';
        }
      }
    }
  }).catch(function (error) {
    // Handle any errors that occur during the process
    (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  });
}

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

// Only add event listeners if notification elements exist on the page
if (notificationBtn && notificationDropdown) {
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
}

// Mark all as read functionality (only if button exists)
if (markAllReadBtn && notificationCount) {
  markAllReadBtn.addEventListener('click', function () {
    var unreadItems = document.querySelectorAll('.notification-item.unread');
    unreadItems.forEach(function (item) {
      item.classList.remove('unread');
    });

    // Update notification count
    notificationCount.textContent = '0';
    notificationCount.style.display = 'none';
  });
}

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
        (_document$getElementB = document.getElementById(bannerId)) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.remove();

        // reduce the notification count as you have deleted the notification
        var currentCount = parseInt(sessionStorage.getItem('notificationCount')) - 1;
        var newValues = currentCount > 0 ? currentCount : 0;
        sessionStorage.setItem('notificationCount', newValues);
        var countBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
        countBadge.innerHTML = newValues;
        if (newValues <= 0) {
          countBadge.style.display = 'none';
        } else {
          countBadge.style.display = 'flex';
        }
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   friendRequestCard: function() { return /* binding */ friendRequestCard; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");

var appUrl = "https://olaogun.test/";
var approverId = encodeURIComponent(localStorage.getItem('requesterId'));
var friendRequestCard = function friendRequestCard(data) {
  var _data$img, _data$firstName, _data$lastName, _data$id, _data$famCode;
  var imageUrl = "/public/img/profile/".concat(encodeURIComponent((_data$img = data.img) !== null && _data$img !== void 0 ? _data$img : data.requesterProfileImg));
  var firstName = encodeURIComponent((_data$firstName = data.firstName) !== null && _data$firstName !== void 0 ? _data$firstName : data.requesterFirstName);
  var lastName = encodeURIComponent((_data$lastName = data.lastName) !== null && _data$lastName !== void 0 ? _data$lastName : data.requesterLastName);
  var requestId = encodeURIComponent((_data$id = data.id) !== null && _data$id !== void 0 ? _data$id : data.requesterId);
  var requestCode = encodeURIComponent((_data$famCode = data.famCode) !== null && _data$famCode !== void 0 ? _data$famCode : data.requesterFamCode);
  var html = "<p id=".concat(requestId, "_linkRequestCard></p>\n\n\n  <img src=\"").concat(imageUrl, "\" alt=\"Avatar\" style=\"width:50%\"><br>\n\n   <span>").concat(firstName, " ").concat(lastName, "</span>\n\n\n    <div class=\"w3-row w3-opacity\">\n      <div class=\"w3-half\">\n        <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/50/").concat(requestCode, "/pp\" style=\"text-decoration: none;\">\n          <button class=\"w3-button w3-block w3-green w3-section\" title=\"Accept\"><i class=\"fa fa-check\"></i></button>\n        </a>\n      </div>\n      \n      <div class=\"w3-half\">\n        <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/10\" style=\"text-decoration: none;\">\n          <button class=\"w3-button w3-block w3-red w3-section\" title=\"Decline\"><i class=\"fa fa-remove\"></i></button>\n        </a>\n      </div>\n    </div>\n  ");
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', html);
};

/***/ })

}]);
//# sourceMappingURL=all_members.js.map