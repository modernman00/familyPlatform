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
            emailPath: 'msg/request'
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
          notificationHTML = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.qSel)(".member_profile_".concat(_userId));
          _context.next = 4;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].delete(url);
        case 4:
          response = _context.sent;
          if (response.data.message === 'success') {
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
} // Function to send family request data to the server
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
function sendFamilyRequest(data) {
  try {
    return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/members/familyRequestMgt', data);
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

    // Create the HTML content based on whether the user is in the same family or not. // LinkedIn-like card design
    var html = "\n    <div class=\"member-card member_profile_".concat(el.id, "\" id=\"").concat(el.id, "\">\n\n       <div class=\"card-cover\">\n            <img src=\"").concat(el.img ? theImg : 'https://via.placeholder.com/400x400?text=No+Image', "\"  alt=\"Member-").concat(el.firstName, "\"loading=\"lazy\">\n        </div>\n\n        <div class=\"member-card-body\">\n            <h3 class=\"member-name\">").concat(toSentenceCase(el.firstName), " ").concat(toSentenceCase(el.lastName), "</h3>\n            <p class=\"member-location\"><i class=\"bi bi-geo-alt-fill\"></i> ").concat(el.country, "</p>\n                <p class=\"member-location\"><i class=\"bi bi-geo-alt-fill\"></i> ").concat(el.famCode, "</p>\n                  <p class=\"member-location\"><i class=\"bi bi-geo-alt-fill\"></i> ").concat(el.email, "</p>\n\n  ").concat(el.relationType !== 'other' ? "\n    <div class=\"member-details\">\n\n         <p class=\"member-detail\">  <i class=\"fa fa-link\"></i><span>".concat(el.relationType, "</span></p>\n          <p class=\"member-detail\"> <i class=\"fa fa-calendar-alt\"></i><span>Member since ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(el.created_at), "</span></p>\n    </div>\n\n    <div class=\"member-interests\">\n      <button class=\"btn-profile primary-action\" id=\"seeProfile").concat(el.id, "\" tooltip=\"View Profile\">\n        <i class=\"fa fa-user\"></i> View Profile\n      </button>\n         <button class=\"btn-profile\" id=\"familyTree").concat(el.id, "\" tooltip=\"View Tree\">\n        <i class=\"fa fa-sitemap\"></i> Family Tree\n      </button>\n      <button class=\"btn-remove\" id=\"removeProfile").concat(el.id, "\" tooltip=\"Remove Connection\">\n        <i class=\"fa fa-user-times\"></i> Remove\n      </button>\n    </div> \n  ") : "\n    <div class=\"member-interests\">\n      <button class=\"btn-profile primary-action\" \n              data-user-id=\"addFamily".concat(el.id, "\" \n              id=\"addFamily").concat(el.id, "\"\n              ").concat(disableButton, "\n              tooltip=\"").concat(tooltip, "\">\n        <i class=\"fa fa-user-plus\"></i> ").concat(statusButtonHTML, "\n      </button>\n      <small>").concat(tooltip, "</small>\n    </div>\n  "), "\n</div>\n    </div>\n");
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
var approverId = localStorage.getItem('requesterId');
var friendRequestCard = function friendRequestCard(data) {
  var _data$firstName, _data$lastName, _data$id, _data$famCode, _data$gender, _data$occupation, _data$country;
  var imageUrl = data.img ? "/resources/images/profile/".concat(data.profilePics) : data.requesterProfileImg;
  var firstName = encodeURIComponent((_data$firstName = data.firstName) !== null && _data$firstName !== void 0 ? _data$firstName : data.requesterFirstName);
  var lastName = encodeURIComponent((_data$lastName = data.lastName) !== null && _data$lastName !== void 0 ? _data$lastName : data.requesterLastName);
  var requestId = encodeURIComponent((_data$id = data.id) !== null && _data$id !== void 0 ? _data$id : data.requesterId);
  var requestCode = encodeURIComponent((_data$famCode = data.famCode) !== null && _data$famCode !== void 0 ? _data$famCode : data.requesterFamCode);
  var gender = (_data$gender = data.gender) !== null && _data$gender !== void 0 ? _data$gender : "";
  var occupation = (_data$occupation = data.occupation) !== null && _data$occupation !== void 0 ? _data$occupation : "";
  var country = (_data$country = data.country) !== null && _data$country !== void 0 ? _data$country : "";
  var mutualFriends = '';

  /* New modern colorful card design */
  var html = "<p id=".concat(requestId, "_linkRequestCard></p>\n    <div class=\"card border-0 shadow-sm mb-4 friend-request-card\" style=\"border-radius: 12px; overflow: hidden; transition: box-shadow 0.3s ease;\">\n      <div class=\"friend-request-cover\" style=\"height: 70px; background: linear-gradient(135deg, var(--primary-color), #00c6ff);\"></div>\n      <div class=\"card-body p-3 pt-0 position-relative\">\n        <div class=\"d-flex justify-content-between align-items-end mb-2\" style=\"margin-top: -35px;\">\n           <a href=\"/allMembers/seeProfile/").concat(requestId, "\" class=\"position-relative\">\n            <img src=\"").concat(imageUrl, "\" alt=\"").concat(firstName, "\" class=\"avatar rounded-circle border border-4 border-white shadow-sm\" style=\"width: 70px; height: 70px; object-fit: cover; background-color: white;\">\n             <span class=\"position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1\" style=\"width: 12px; height: 12px;\"></span>\n          </a>\n        </div>\n\n        <div class=\"mb-3\">\n           <h6 class=\"mb-0 fw-bold text-truncate\" style=\"font-size: 1.1rem;\">\n              <a href=\"/allMembers/seeProfile/").concat(requestId, "\" class=\"text-dark text-decoration-none\">").concat(firstName, " ").concat(lastName, "</a>\n            </h6>\n            ").concat(occupation ? "<div class=\"small text-muted text-truncate fw-medium\">".concat(occupation, "</div>") : '', "\n             <div class=\"d-flex align-items-center mt-1 text-muted small\">\n                ").concat(country ? "<span class=\"me-2\"><i class=\"bi bi-geo-alt-fill me-1 text-primary\"></i>".concat(country, "</span>") : '', "\n                <span>").concat(mutualFriends ? mutualFriends : 'New to interactions', "</span>\n            </div>\n        </div>\n\n        \n        <div class=\"d-flex gap-2 friend-request-actions\">\n          <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/50/").concat(requestCode, "/pp\" class=\"btn btn-primary btn-sm flex-grow-1 border-0 fw-medium shadow-sm\" style=\"border-radius: 20px; padding: 6px 0; background: linear-gradient(to right, var(--primary-color), #00c6ff);\">\n            Confirm\n          </a>\n          <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/10\" class=\"btn btn-light btn-sm flex-grow-1 border-0 fw-medium text-secondary\" style=\"border-radius: 20px; padding: 6px 0; background-color: #f0f2f5;\">\n            Decline\n          </a>\n        </div>\n      </div>\n    </div>\n  ");
  (0,_shared__WEBPACK_IMPORTED_MODULE_0__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', html);
};

/***/ })

}]);
//# sourceMappingURL=all_members.js.map