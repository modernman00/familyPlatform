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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../global.js */ "./resources/asset/js/components/global.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _profilePage_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../profilePage/htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");









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
    var btn, targetId, userId, approverDetails, familyRequestData, result, _userId, url, _result, notificationHTML, response, _userId2, _userId3, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          // Get the target element's ID (defensively checking closest button or element with ID)
          btn = e.target.closest('button') || e.target.closest('[id]');
          targetId = btn ? btn.id : e.target.id || '';
          if (targetId) {
            _context.next = 1;
            break;
          }
          return _context.abrupt("return");
        case 1:
          if (!targetId.includes('addFamily')) {
            _context.next = 4;
            break;
          }
          // Extract the user ID from the target ID
          userId = targetId.replace('addFamily', ''); // Fetch approver details for the user
          _context.next = 2;
          return fetchApproverData(userId);
        case 2:
          approverDetails = _context.sent;
          familyRequestData = {
            approver: approverDetails,
            emailPath: 'msg/request_premium'
          }; // Send the family request data to the server for processing which returns the notification details for the approvers tab
          _context.next = 3;
          return sendFamilyRequest(familyRequestData);
        case 3:
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
          _context.next = 9;
          break;
        case 4:
          if (!targetId.includes('removeProfile')) {
            _context.next = 8;
            break;
          }
          // Extract the user ID from the target ID
          _userId = targetId.replace('removeProfile', '');
          url = "/allMembers/removeProfile/".concat(_userId, "/").concat(reqId); // include a console to confirm if they truly want to delete the profile
          _context.next = 5;
          return sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
            title: 'Are you sure?',
            text: 'You will no longer see the profile and associated posts. Are you sure you want to delete the profile?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
          });
        case 5:
          _result = _context.sent;
          if (!_result.isConfirmed) {
            _context.next = 7;
            break;
          }
          notificationHTML = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.qSel)(".member_profile_".concat(_userId));
          _context.next = 6;
          return axios__WEBPACK_IMPORTED_MODULE_7__["default"]["delete"](url);
        case 6:
          response = _context.sent;
          if (response.data.message === 'success') {
            // remove a html element with call member_profile
            notificationHTML.remove();
          } else {
            (0,_shared__WEBPACK_IMPORTED_MODULE_3__.msgException)("Error deleting profile");
          }
        case 7:
          _context.next = 9;
          break;
        case 8:
          if (targetId.includes('seeProfile')) {
            // Extract the user ID from the target ID
            _userId2 = targetId.replace('seeProfile', ''); // redirect to 'allMembers/setProfile/'+userId
            window.location.href = "/allMembers/seeProfile/".concat(_userId2);
          } else if (targetId.includes('familyTree')) {
            // Extract the user ID from the target ID
            _userId3 = targetId.replace('familyTree', ''); // redirect to 'allMembers/setProfile/'+userId
            window.location.href = "/organogram/".concat(_userId3);
          }
        case 9:
          _context.next = 11;
          break;
        case 10:
          _context.prev = 10;
          _t = _context["catch"](0);
          // Handle any errors that occur during execution
          (0,_shared__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
        case 11:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 10]]);
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
          return axios__WEBPACK_IMPORTED_MODULE_7__["default"].get("/members/familyRequestMgt/getApprover?id=".concat(userId));
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
    return axios__WEBPACK_IMPORTED_MODULE_7__["default"].post('/members/familyRequestMgt', data);
  } catch (error) {
    (0,_shared__WEBPACK_IMPORTED_MODULE_3__.showError)(error);
  }
}

// Function to update the button's HTML and disable it
function updateButton(targetId, newHTML) {
  var theTargetId = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.id)(targetId);
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
/* harmony export */   "renderMembers": function() { return /* binding */ renderMembers; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
/* harmony import */ var _handleInput__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./handleInput */ "./resources/asset/js/components/allMembers/handleInput.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");


// resources/js/allMembers/api.js



var URL = "https://olaogun.test/";
var allMembersContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("allMembers");
var memberCountBadge = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("memberCount");
var memberCountDisplay = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("memberCountDisplay");
var searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("searchFamily");
var NO_MEMBER_HTML = "\n  <div class=\"col-12 text-center py-5 bg-white rounded-4 border w-100\" style=\"grid-column: 1 / -1; border-radius: var(--stitch-radius-lg);\">\n      <div class=\"mx-auto mb-3 d-flex align-items-center justify-content-center\" style=\"width: 64px; height: 64px; font-size: 1.8rem; background: var(--stitch-primary-container); color: var(--stitch-primary); border-radius: 50%;\">\n          <i class=\"bi bi-people\"></i>\n      </div>\n      <h5 class=\"fw-bold text-dark mb-1\">No Members in View</h5>\n      <p class=\"text-muted small mb-3\" style=\"max-width: 440px; margin: 0 auto;\">\n          There are no matching relatives in this directory category yet. Invite or connect with your family members to build your network.\n      </p>\n      <a href=\"/familyStudio\" class=\"btn btn-primary btn-sm fw-bold px-4 py-2\" style=\"border-radius: var(--stitch-radius-pill);\">\n          <i class=\"bi bi-plus-circle-fill me-1\"></i> Open Family Studio\n      </a>\n  </div>\n";

/**
 * Render a list of members into the main container.
 * Also updates the member count badge.
 *
 * @param {Array<object>} members
 */
var renderMembers = function renderMembers() {
  var members = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
  allMembersContainer.innerHTML = "";
  if (!members.length) {
    allMembersContainer.innerHTML = NO_MEMBER_HTML;
    if (memberCountBadge) memberCountBadge.textContent = "0";
    if (memberCountDisplay) memberCountDisplay.textContent = "0";
    return;
  }

  // Render each member card
  members.forEach(_html__WEBPACK_IMPORTED_MODULE_2__.renderHtml);

  // Update member count badges
  var countStr = members.length.toLocaleString();
  if (memberCountBadge) memberCountBadge.textContent = countStr;
  if (memberCountDisplay) memberCountDisplay.textContent = countStr;
};

// Client-side category pill filtering
function setupFilterPills() {
  var pillsContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("memberFilterPills");
  if (!pillsContainer) return;
  pillsContainer.addEventListener("click", function (e) {
    var btn = e.target.closest(".filter-pill-btn");
    if (!btn) return;
    pillsContainer.querySelectorAll(".filter-pill-btn").forEach(function (b) {
      return b.classList.remove("active");
    });
    btn.classList.add("active");
    var filterVal = btn.getAttribute("data-filter");
    var cards = allMembersContainer.querySelectorAll(".member-card");
    var visibleCount = 0;
    cards.forEach(function (card) {
      var cat = card.getAttribute("data-category");
      if (filterVal === "all" || cat === filterVal) {
        card.style.display = "";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });
    if (visibleCount === 0 && cards.length > 0) {
      // Show empty state placeholder if none match category filter
      var emptyPlaceholder = allMembersContainer.querySelector(".empty-filter-placeholder");
      if (!emptyPlaceholder) {
        allMembersContainer.insertAdjacentHTML("beforeend", "<div class=\"empty-filter-placeholder col-12 text-center py-4 text-muted small\" style=\"grid-column: 1 / -1;\">No members in this category.</div>");
      }
    } else {
      var _emptyPlaceholder = allMembersContainer.querySelector(".empty-filter-placeholder");
      if (_emptyPlaceholder) _emptyPlaceholder.remove();
    }
  });
}
(function () {
  var _bootstrapAllMembers = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var url, famCodeData, familyMembers, loader, handleSearch, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          url = "".concat(URL, "allMembers/processApiData");
          _context.next = 1;
          return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.getApiData)(url);
        case 1:
          famCodeData = _context.sent;
          familyMembers = [];
          if (Array.isArray(famCodeData)) {
            familyMembers = famCodeData;
          } else if (Array.isArray(famCodeData === null || famCodeData === void 0 ? void 0 : famCodeData.message)) {
            familyMembers = famCodeData.message;
          } else if (Array.isArray(famCodeData === null || famCodeData === void 0 ? void 0 : famCodeData.data)) {
            familyMembers = famCodeData.data;
          }

          // Pre-render network members
          renderMembers(familyMembers);

          // Remove loading spinner
          loader = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.id)("setLoader");
          if (loader) {
            loader.classList.remove("loader");
            loader.classList.add("d-none");
            loader.style.display = "none";
          }

          // Setup filter pills
          setupFilterPills();

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
/* harmony export */   "createSearchHandler": function() { return /* binding */ createSearchHandler; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
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
  container.innerHTML = "\n    <p>No matching name found \u2013 do you want us to send them a text/email to register to the platform?</p>\n    ".concat((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.checkBox)("newMemberRequest"), " <br>\n\n    <input type=\"hidden\" id=\"newMemberName\" value=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_3__.esc)(rawQuery), "\">\n\n    <input type=\"text\" id=\"newMemberRequestName\" class=\"form-control\"\n           name=\"newMemberRequestName\"\n           placeholder=\"Enter their name\">\n\n    <input type=\"text\" id=\"newMemberRequestEmail\" class=\"form-control\"\n           name=\"newMemberRequestEmail\"\n           placeholder=\"Enter their email address or mobile number\">\n\n    <p id=\"loader\" class=\"loader\" style=\"display:none;\"></p>\n    <small id=\"newMemberRequest_help\" class=\"form-text text-muted\"></small>\n\n    <button class=\"button is-primary\" id=\"newMemberRequestBtn\">Send Request</button>\n  ");
  var nameInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestName");
  var emailInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestEmail");
  var submitBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestBtn");
  var helpMsg = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequest_help");

  // hide fields until checkbox ticked
  nameInput.style.display = "none";
  emailInput.style.display = "none";
  submitBtn.style.display = "none";
  var yesCheckbox = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestYes");
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
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.showNotification)("allMembers", "is-success", response.data.message);
          helpMsg.textContent = "";
          _context.next = 7;
          break;
        case 6:
          _context.prev = 6;
          _t = _context["catch"](4);
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.showNotification)("allMembers", "is-danger", _t.message);
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
  var searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("searchFamily");
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
            (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.showNotification)("allMembers", "is-danger", "Search failed. Please try again.");
            // optional: log or surface more detail in dev builds
            (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.msgException)(_t2);
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
/* harmony export */   "renderHtml": function() { return /* binding */ renderHtml; }
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



var toSentenceCase = function toSentenceCase(str) {
  if (!str || typeof str !== 'string') return '';
  return str.toLowerCase().split(' ').map(function (word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
  }).join(' ');
};
var renderHtml = function renderHtml(el) {
  try {
    var _el$status;
    if (!el) {
      throw new Error('there is no data');
    }
    var theImg = el.img ? "/resources/images/profile/".concat(el.img) : '/resources/images/profile/avatarM.png';
    var status = ((_el$status = el.status) === null || _el$status === void 0 ? void 0 : _el$status.toLowerCase()) || null;
    var statusButtonHTML = 'Connect';
    var tooltip = status === 'rejected' ? 'You can send another request' : 'Send connection request';
    if (status === 'request sent') {
      statusButtonHTML = 'Request Sent';
    }
    var disableButton = status === 'request sent' ? 'disabled' : '';

    // Determine Relation Category & Human-Friendly Status Chip
    var relType = el.relationType || 'other';
    var roleChipHtml = '';
    var categoryKey = 'explore';
    if (relType === 'family') {
      categoryKey = 'family';
      roleChipHtml = "<span class=\"role-chip role-chip-family\"><i class=\"bi bi-house-heart-fill\"></i> Same Family</span>";
    } else if (relType === 'approved_you' || relType === 'you_approved') {
      categoryKey = 'connected';
      roleChipHtml = "<span class=\"role-chip role-chip-connected\"><i class=\"bi bi-check2-circle\"></i> Connected Kin</span>";
    } else {
      categoryKey = 'explore';
      roleChipHtml = "<span class=\"role-chip role-chip-directory\"><i class=\"bi bi-globe2\"></i> Directory</span>";
    }

    // el.* fields are user-authored — escape every one before it hits innerHTML (SEC-2).
    var idSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(el.id);
    var firstName = toSentenceCase(el.firstName || '');
    var lastName = toSentenceCase(el.lastName || '');
    var fullNameRaw = "".concat(firstName, " ").concat(lastName).trim();
    var fullName = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(fullNameRaw);
    var location = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(el.country ? toSentenceCase(el.country) : 'Location not set');
    var famCode = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(el.famCode ? String(el.famCode).toUpperCase() : 'CODE');
    var emailSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(el.email || 'Email not provided');
    var imgSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(theImg);
    var html = "\n    <div class=\"member-card member_profile_".concat(idSafe, "\" id=\"").concat(idSafe, "\" data-category=\"").concat(categoryKey, "\" data-search=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(fullNameRaw.toLowerCase() + ' ' + (el.email || '')), "\">\n        <div class=\"card-cover\"></div>\n\n        <div class=\"avatar-wrapper\">\n             <img src=\"").concat(imgSafe, "\" alt=\"").concat(fullName, "\" loading=\"lazy\">\n             <span class=\"avatar-online-dot\"></span>\n        </div>\n\n        <div class=\"member-card-body\">\n            ").concat(roleChipHtml, "\n            <h4 class=\"member-name text-truncate\" title=\"").concat(fullName, "\">").concat(fullName, "</h4>\n\n            <span class=\"member-location-chip\">\n                <i class=\"bi bi-geo-alt-fill text-danger\"></i> ").concat(location, "\n            </span>\n\n            <div class=\"member-details\">\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-hash\"></i>\n                    <span class=\"fw-bold\">").concat(famCode, "</span>\n                </div>\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-envelope\"></i>\n                    <span class=\"text-truncate\">").concat(emailSafe, "</span>\n                </div>\n                ").concat(relType !== 'other' ? "\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-calendar-check\"></i> \n                    <span>Connected ".concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(el.created_at), "</span>\n                </div>\n                ") : '', "\n            </div>\n\n            <div class=\"member-interests\">\n                ").concat(relType !== 'other' ? "\n                <button class=\"btn-stitch-primary\" id=\"seeProfile".concat(idSafe, "\">\n                    <i class=\"bi bi-person-badge-fill\" style=\"pointer-events: none;\"></i> View Profile\n                </button>\n                <div class=\"d-flex gap-2\">\n                    <button class=\"btn-stitch-tonal flex-grow-1\" id=\"familyTree").concat(idSafe, "\" title=\"View in Family Tree\">\n                        <i class=\"bi bi-diagram-3-fill\" style=\"pointer-events: none;\"></i> Tree\n                    </button>\n                    <button class=\"btn-stitch-danger flex-grow-1\" id=\"removeProfile").concat(idSafe, "\" title=\"Remove Connection\">\n                        <i class=\"bi bi-person-dash-fill\" style=\"pointer-events: none;\"></i> Remove\n                    </button>\n                </div>\n                ") : "\n                <button class=\"btn-stitch-connect\" \n                        data-user-id=\"addFamily".concat(idSafe, "\" \n                        id=\"addFamily").concat(idSafe, "\"\n                        ").concat(disableButton, ">\n                    <i class=\"bi bi-person-plus-fill\" style=\"pointer-events: none;\"></i> ").concat(statusButtonHTML, "\n                </button>\n                <small class=\"text-muted text-center\" style=\"font-size: 0.75rem; font-weight: 500;\">").concat(tooltip, "</small>\n                "), "\n            </div>\n        </div>\n    </div>\n    ");
    var container = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers');
    if (container) {
      container.insertAdjacentHTML('beforeend', html);
    }
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
/* harmony export */   "autoCompleter": function() { return /* binding */ autoCompleter; },
/* harmony export */   "checkBox": function() { return /* binding */ checkBox; },
/* harmony export */   "checkBox2": function() { return /* binding */ checkBox2; },
/* harmony export */   "convertFormData": function() { return /* binding */ convertFormData; },
/* harmony export */   "createAndAppendElement": function() { return /* binding */ createAndAppendElement; },
/* harmony export */   "distinctValue": function() { return /* binding */ distinctValue; },
/* harmony export */   "isChecked": function() { return /* binding */ isChecked; },
/* harmony export */   "loaderIcon": function() { return /* binding */ loaderIcon; },
/* harmony export */   "loaderIconBootstrap": function() { return /* binding */ loaderIconBootstrap; },
/* harmony export */   "loaderIconBulma": function() { return /* binding */ loaderIconBulma; },
/* harmony export */   "matchInput": function() { return /* binding */ matchInput; },
/* harmony export */   "matchRegex": function() { return /* binding */ matchRegex; },
/* harmony export */   "realTimeCheckLen": function() { return /* binding */ realTimeCheckLen; },
/* harmony export */   "removeDiv": function() { return /* binding */ removeDiv; },
/* harmony export */   "showResponse": function() { return /* binding */ showResponse; },
/* harmony export */   "toSentenceCase": function() { return /* binding */ toSentenceCase; }
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
      fn();
    } else if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(noId).checked) {}
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
/* harmony export */   "addToNotificationTab": function() { return /* binding */ addToNotificationTab; },
/* harmony export */   "increaseNotificationCount": function() { return /* binding */ increaseNotificationCount; }
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");





// Update notification badge
function updateNotificationBadge(count) {
  var badge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
  var headerBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('header_notif_count');
  var countNum = parseInt(count || '0');
  var displayStr = countNum > 99 ? '99+' : countNum > 0 ? countNum.toString() : '';
  if (badge) {
    if (countNum <= 0) {
      badge.style.display = 'none';
      badge.textContent = '';
    } else {
      badge.textContent = displayStr;
      badge.style.display = 'flex';
    }
  }
  if (headerBadge) {
    if (countNum <= 0) {
      headerBadge.style.display = 'none';
      headerBadge.textContent = '';
    } else {
      headerBadge.textContent = displayStr;
      headerBadge.style.display = 'flex';
    }
  }
}

// Google Stitch Notification Item Template
var notificationHTML = function notificationHTML(data) {
  // Determine friendly emoji / icon based on type and content
  var getEmoji = function getEmoji(type) {
    var name = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    var content = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
    var text = "".concat(type, " ").concat(name, " ").concat(content).toLowerCase();
    if (text.includes('birthday') || text.includes('cake')) return '🎂';
    if (text.includes('anniversary') || text.includes('celebrate') || text.includes('cypress') || text.includes('party')) return '🥂';
    if (text.includes('crash') || text.includes('chaos') || text.includes('alert') || text.includes('warning')) return '🧧';
    if (text.includes('test') || text.includes('lab')) return '🧪';
    if (text.includes('wedding') || text.includes('marriage')) return '💍';
    if (text.includes('friend') || text.includes('request') || text.includes('connect')) return '👋';
    if (text.includes('post') || text.includes('photo') || text.includes('memory')) return '📸';
    if (text.includes('like') || text.includes('heart')) return '❤️';
    if (text.includes('comment')) return '💬';
    if (text.includes('house')) return '🏡';
    if (text.includes('meeting') || text.includes('event')) return '📅';
    return '🔔';
  };
  var emoji = getEmoji(data.notification_type || '', data.notification_name || '', data.notification_content || '');
  var isUnread = data.notification_status === 'new';
  var sender_id = data.sender_id,
    notification_name = data.notification_name,
    notification_content = data.notification_content,
    created_at = data.created_at,
    no = data.no;
  var randomNumber = Math.floor(100 + Math.random() * 900);
  var bannerId = "notificationBar".concat(sender_id).concat(randomNumber);
  var formattedTitle = (0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(notification_name || 'Notification');
  var timeAgoStr = created_at ? (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(created_at) : 'Just now';
  return "\n    <div id=\"".concat(bannerId, "\" class=\"notif-card-stitch ").concat(isUnread ? 'unread' : '', "\">\n        <div class=\"notif-emoji-icon\">\n            <span>").concat(emoji, "</span>\n        </div>\n        \n        <a href=\"#linkNotification").concat(no, "\" class=\"notif-details-link flex-grow-1 text-decoration-none\">\n            <h6 class=\"notif-item-title\">").concat(formattedTitle, "</h6>\n            <p class=\"notif-item-desc\">").concat(notification_content || '', "</p>\n            <span class=\"notif-item-time\">").concat(timeAgoStr, "</span>\n        </a>\n\n        <button class=\"notif-dismiss-btn\" \n                data-no=\"").concat(no, "\"\n                data-is=\"").concat(sender_id, "\"\n                type=\"button\"\n                id=\"deleteNotification").concat(sender_id).concat(randomNumber, "\"\n                title=\"Dismiss\">\n            <i class=\"bi bi-x-lg\" style=\"pointer-events: none;\"></i>\n        </button>\n    </div>\n    ");
};
var increaseNotificationCount = function increaseNotificationCount() {
  var current = parseInt(sessionStorage.getItem('notificationCount') || '0') + 1;
  sessionStorage.setItem('notificationCount', current);
  updateNotificationBadge(current);
};
var addToNotificationTab = function addToNotificationTab(data) {
  var tab = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab');
  if (tab) {
    tab.insertAdjacentHTML('afterbegin', notificationHTML(data));
  }
};
var yourId = localStorage.getItem('requesterId');
var famCode = localStorage.getItem('requesterFamCode');
var notificationURL = "/member/notifications/id/".concat(yourId, "/").concat(famCode);
if (yourId && famCode && yourId !== 'null' && famCode !== 'null') {
  axios__WEBPACK_IMPORTED_MODULE_3__["default"].get(notificationURL).then(function (res) {
    var data = res.data.message;
    if (data && data.length > 0) {
      sessionStorage.setItem('notificationCount', data.length);
      updateNotificationBadge(data.length);
      data.forEach(function (element) {
        addToNotificationTab(element);
      });
      var updateNotificationTiming = document.querySelectorAll(".notification_timeago");
      if (updateNotificationTiming && updateNotificationTiming.length > 0) {
        (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.render)(updateNotificationTiming);
      }
    } else {
      sessionStorage.setItem('notificationCount', 0);
      updateNotificationBadge(0);
      var tab = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab');
      if (tab && !tab.children.length) {
        tab.innerHTML = "\n                    <div class=\"p-4 text-center text-muted\">\n                        <i class=\"bi bi-bell-slash fs-3 d-block mb-2 text-secondary opacity-50\"></i>\n                        <span class=\"small fw-medium\">All caught up! No notifications</span>\n                    </div>";
      }
    }
  }).catch(function (error) {
    (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  });
}

// Dropdown controls
var notificationBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notificationBtn');
var notificationDropdown = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notificationDropdown');
var markAllReadBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('markAllRead');
if (notificationBtn && notificationDropdown) {
  notificationBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    notificationDropdown.classList.toggle('show');
  });
  document.addEventListener('click', function (e) {
    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
      notificationDropdown.classList.remove('show');
    }
  });
  notificationDropdown.addEventListener('click', function (e) {
    e.stopPropagation();
  });
}
if (markAllReadBtn) {
  markAllReadBtn.addEventListener('click', function () {
    var unreadItems = document.querySelectorAll('.notif-card-stitch.unread');
    unreadItems.forEach(function (item) {
      item.classList.remove('unread');
    });
    sessionStorage.setItem('notificationCount', '0');
    updateNotificationBadge(0);
  });
}

// Dismiss listener delegation
var initDeleteOnce = function initDeleteOnce() {
  var tab = document.getElementById('notification_tab');
  if (!tab) return;
  tab.addEventListener('click', function (e) {
    var btn = e.target.closest('button[id*="deleteNotification"]');
    if (!btn) return;
    e.stopPropagation();
    var bannerId = btn.id.replace('deleteNotification', 'notificationBar');
    var no = btn.getAttribute('data-no');
    var url = "/removeNotification/".concat(no);
    axios__WEBPACK_IMPORTED_MODULE_3__["default"].put(url).then(function (response) {
      if (response.data.message === 'Notification marked as read') {
        var item = document.getElementById(bannerId);
        if (item) {
          item.style.opacity = '0';
          item.style.transform = 'translateX(10px)';
          setTimeout(function () {
            item.remove();
            if (!tab.querySelectorAll('.notif-card-stitch').length) {
              tab.innerHTML = "\n                                <div class=\"p-4 text-center text-muted\">\n                                    <i class=\"bi bi-bell-slash fs-3 d-block mb-2 text-secondary opacity-50\"></i>\n                                    <span class=\"small fw-medium\">All caught up! No notifications</span>\n                                </div>";
            }
          }, 200);
        }
        var currentCount = parseInt(sessionStorage.getItem('notificationCount') || '1') - 1;
        var newValues = currentCount > 0 ? currentCount : 0;
        sessionStorage.setItem('notificationCount', newValues);
        updateNotificationBadge(newValues);
      }
    }).catch(_shared__WEBPACK_IMPORTED_MODULE_1__.showError);
  });
};
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', initDeleteOnce) : initDeleteOnce();

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js ***!
  \***********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "friendRequestCard": function() { return /* binding */ friendRequestCard; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");

var appUrl = "https://olaogun.test/";
var approverId = encodeURIComponent(localStorage.getItem('requesterId'));
var friendRequestCard = function friendRequestCard(data) {
  var _data$img, _data$firstName, _data$lastName, _data$id, _data$famCode;
  var imageUrl = "/resources/images/profile/".concat(encodeURIComponent((_data$img = data.img) !== null && _data$img !== void 0 ? _data$img : data.requesterProfileImg));
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