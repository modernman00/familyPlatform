"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["all_members"],{

/***/ "./resources/asset/js/components/allMembers/allEvents.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/allEvents.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
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
    var targetId, userId, approverDetails, requesterDetails, familyRequestData, response, famCode, _userId, url, notificationHTML, _response, _userId2, _userId3, _t;
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
          // Prepare family request data
          requesterDetails = getLocalStorageProfile();
          familyRequestData = {
            requester: requesterDetails,
            approver: approverDetails,
            emailPath: 'msg/request',
            subject: "".concat(requesterDetails.requesterFirstName, " ").concat(requesterDetails.requesterLastName, " sent you a family request")
          }; // Send the family request data to the server for processing which returns the notification details for the approvers tab
          _context.next = 2;
          return sendFamilyRequest(familyRequestData);
        case 2:
          response = _context.sent;
          // ADD TO THE NOTIFICATION TAB OF THE APPROVER if the famcode on local storage is the same as the approverFamCode
          famCode = localStorage.getItem('requesterFamCode');
          if (famCode === approverDetails.approverCode) {
            (0,_navbar__WEBPACK_IMPORTED_MODULE_5__.addToNotificationTab)(response.data.message);
            (0,_profilePage_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_6__.friendRequestCard)(requesterDetails);
            (0,_navbar__WEBPACK_IMPORTED_MODULE_5__.increaseNotificationCount)();
          }

          // Update the button's HTML and disable it
          updateButton(targetId, 'Request Sent');
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
          notificationHTML = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.qSel)(".member_profile_".concat(_userId));
          _context.next = 4;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].delete(url);
        case 4:
          _response = _context.sent;
          if (_response.data.message === 'success') {
            // remove a html element with call member_profile
            notificationHTML.remove();
          } else {
            (0,_shared__WEBPACK_IMPORTED_MODULE_3__.msgException)("Error deleting profile");
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
          (0,_shared__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
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
} // Function to retrieve requester details from local storage
function _fetchApproverData() {
  _fetchApproverData = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(userId) {
    var result, approverDetails, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("/members/familyRequestMgt/getApprover?id=".concat(userId));
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
function getLocalStorageProfile() {
  var getRequesterDetails = localStorage.getItem('profile');
  return JSON.parse(getRequesterDetails);
}

// Function to send family request data to the server
function sendFamilyRequest(_x3) {
  return _sendFamilyRequest.apply(this, arguments);
} // Function to update the button's HTML and disable it
function _sendFamilyRequest() {
  _sendFamilyRequest = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(data) {
    var _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.prev = 0;
          _context3.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/members/familyRequestMgt', data);
        case 1:
          return _context3.abrupt("return", _context3.sent);
        case 2:
          _context3.prev = 2;
          _t3 = _context3["catch"](0);
          (0,_shared__WEBPACK_IMPORTED_MODULE_3__.showError)(_t3);
        case 3:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[0, 2]]);
  }));
  return _sendFamilyRequest.apply(this, arguments);
}
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
/***/ (function(module, __webpack_exports__, __webpack_require__) {

__webpack_require__.a(module, async function (__webpack_handle_async_dependencies__, __webpack_async_result__) { try {
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderMembers: function() { return /* binding */ renderMembers; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
/* harmony import */ var _handleInput__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./handleInput */ "./resources/asset/js/components/allMembers/handleInput.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");






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
var url1 = "".concat(URL, "allMembers/processApiData"); // data based on famCode and reqMgt accepted and approved
var url2 = "".concat(URL, "allMembers/allData"); // all the users data

var _await$getMultipleApi = await (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.getMultipleApiData)(url1, url2),
  _await$getMultipleApi2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_await$getMultipleApi, 2),
  famCodeData = _await$getMultipleApi2[0],
  allUsers = _await$getMultipleApi2[1];

// const dataWithFamCode = filterMembersByFamCode(data);
renderMembers(famCodeData.message, allMembersContainer, noMemberHTML, _html__WEBPACK_IMPORTED_MODULE_2__.renderHtml);
// Remove the "loader" class after rendering is complete
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('setLoader').classList.remove('loader');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('searchFamily').addEventListener('input', function () {
  return (0,_handleInput__WEBPACK_IMPORTED_MODULE_3__.handleInput)(allUsers.message, famCodeData.message, renderMembers);
});
__webpack_async_result__();
} catch(e) { __webpack_async_result__(e); } }, 1);

/***/ }),

/***/ "./resources/asset/js/components/allMembers/handleInput.js":
/*!*****************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/handleInput.js ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   handleInput: function() { return /* binding */ handleInput; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
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
      return el.firstName.toLowerCase().includes(inputVal) || el.lastName.toLowerCase().includes(inputVal) || el.email.toLowerCase().includes(inputVal) || el.mobile.toLowerCase().includes(inputVal) || el.famCode.toLowerCase().includes(inputVal) || el.country.toLowerCase().includes(inputVal);
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
          }).catch(function (error) {
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
  var famCode = localStorage.getItem('requesterFamCode');
  var reqId = localStorage.getItem('requesterId');
  try {
    if (!el) {
      // Handle the case where 'el' is falsy, such as when data is not available.
      throw new Error('there is no data');
    }
    (0,_shared__WEBPACK_IMPORTED_MODULE_1__.log)(el);
    var theImg = "/resources/images/profile/".concat(el.img);
    var statusButtonHTML = el.status !== null ? el.status : 'Connect';
    var relationshipType = el.relationship ? el.relationship : 'Immediate Family';
    var disableButton = statusButtonHTML === "Request sent" ? "disabled" : "";
    var fatherName = toSentenceCase(el.father_name);
    var motherName = toSentenceCase(el.mother_name);
    var spouseName = toSentenceCase(el.spouse_name);
    // const spouse = toSentenceCase(el.spouseName);

    // Create the HTML content based on whether the user is in the same family or not. // LinkedIn-like card design
    var html = "\n    <div class=\"member-card member_profile_".concat(el.id, "\" id=\"").concat(el.id, "\">\n\n       <div class=\"card-cover\">\n            <img src=\"").concat(el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image', "\"  alt=\"Member-").concat(el.firstName, "\"loading=\"lazy\">\n        </div>\n\n        <div class=\"member-card-body\">\n            <h3 class=\"member-name\">").concat(toSentenceCase(el.firstName), " ").concat(toSentenceCase(el.lastName), "</h3>\n            <p class=\"member-location\"><i class=\"bi bi-geo-alt-fill\"></i> ").concat(el.country, "</p>\n\n  ").concat(el.relationType ? "\n    <div class=\"member-details\">\n\n         <p class=\"member-detail\">  <i class=\"fa fa-link\"></i><span>".concat(el.relationType, "</span></p>\n          <p class=\"member-detail\"> <i class=\"fa fa-calendar-alt\"></i><span>Member since ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(el.created_at), "</span></p>\n    </div>\n\n    <div class=\"member-interests\">\n      <button class=\"btn-profile primary-action\" id=\"seeProfile").concat(el.id, "\" tooltip=\"View Profile\">\n        <i class=\"fa fa-user\"></i> View Profile\n      </button>\n         <button class=\"btn-profile\" id=\"familyTree").concat(el.id, "\" tooltip=\"View Tree\">\n        <i class=\"fa fa-sitemap\"></i> Family Tree\n      </button>\n      <button class=\"btn-remove\" id=\"removeProfile").concat(el.id, "\" tooltip=\"Remove Connection\">\n        <i class=\"fa fa-user-times\"></i> Remove\n      </button>\n    </div>\n  ") : "\n    <div class=\"member-interests\">\n      <button class=\"btn-profile primary-action\" \n              data-user-id=\"addFamily".concat(el.id, "\" \n              ").concat(disableButton, "\n              tooltip=\"Send Request\">\n        <i class=\"fa fa-user-plus\"></i> ").concat(statusButtonHTML, "\n      </button>\n    </div>\n  "), "\n</div>\n    </div>\n");
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
/***/ (function(module, __webpack_exports__, __webpack_require__) {

__webpack_require__.a(module, async function (__webpack_handle_async_dependencies__, __webpack_async_result__) { try {
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./api */ "./resources/asset/js/components/allMembers/api.js");
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/allMembers/allEvents.js");
var __webpack_async_dependencies__ = __webpack_handle_async_dependencies__([_api__WEBPACK_IMPORTED_MODULE_0__]);
var __webpack_async_dependencies_result__ = (__webpack_async_dependencies__.then ? (await __webpack_async_dependencies__)() : __webpack_async_dependencies__);
_api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_async_dependencies_result__[0];


__webpack_async_result__();
} catch(e) { __webpack_async_result__(e); } });

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