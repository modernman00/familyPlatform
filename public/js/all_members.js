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
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../global.js */ "./resources/asset/js/components/global.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _profilePage_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../profilePage/htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");









// Attach a click event listener to the document
/**
 * Attach a click event listener to the document. When a button with the id `addFamily<userId>` is clicked, send a family request to the user identified by the userId and update the button's HTML and disable it.
 it returns the notification details for the approvers tab
 * 
 * @param {MouseEvent} e - The event object.
 */
document.onclick = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
    var btn, targetId, userId, approverDetails, familyRequestData, result, _userId, url, _result, memberCard, csrfToken, response, container, remaining, countStr, memberCountBadge, memberCountDisplay, _response$data, errText, _err$response, _err$response$data, _errText, _userId2, _userId3, _t, _t2;
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
          _context.next = 11;
          break;
        case 4:
          if (!targetId.includes('removeProfile')) {
            _context.next = 10;
            break;
          }
          // Extract the user ID from the target ID
          _userId = targetId.replace('removeProfile', '');
          url = "/allMembers/removeProfile/".concat(_userId); // Confirm with SweetAlert warning modal
          _context.next = 5;
          return sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
            title: 'Are you sure?',
            text: 'You will no longer see this profile and associated posts. Are you sure you want to remove this connection?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove connection!'
          });
        case 5:
          _result = _context.sent;
          if (!_result.isConfirmed) {
            _context.next = 9;
            break;
          }
          memberCard = (0,_shared__WEBPACK_IMPORTED_MODULE_4__.qSel)(".member_profile_".concat(_userId)) || (0,_shared__WEBPACK_IMPORTED_MODULE_4__.id)(_userId) || (btn ? btn.closest('.member-card') : null);
          _context.prev = 6;
          csrfToken = (0,_global_js__WEBPACK_IMPORTED_MODULE_5__.getCsrfToken)();
          _context.next = 7;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].delete(url, {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-XSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            params: {
              token: csrfToken
            },
            data: {
              token: csrfToken
            }
          });
        case 7:
          response = _context.sent;
          if (response.data && (response.data.status === 'success' || response.data.message === 'success')) {
            if (memberCard) {
              memberCard.remove();
            }

            // Update member count badge
            container = (0,_shared__WEBPACK_IMPORTED_MODULE_4__.id)('allMembers');
            if (container) {
              remaining = container.querySelectorAll('.member-card').length;
              countStr = remaining.toLocaleString();
              memberCountBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_4__.id)('memberCount');
              memberCountDisplay = (0,_shared__WEBPACK_IMPORTED_MODULE_4__.id)('memberCountDisplay');
              if (memberCountBadge) memberCountBadge.textContent = countStr;
              if (memberCountDisplay) memberCountDisplay.textContent = countStr;
              if (remaining === 0) {
                container.innerHTML = "\n                  <div class=\"col-12 text-center py-5 bg-white rounded-4 border w-100\" style=\"grid-column: 1 / -1; border-radius: var(--stitch-radius-lg);\">\n                      <div class=\"mx-auto mb-3 d-flex align-items-center justify-content-center\" style=\"width: 64px; height: 64px; font-size: 1.8rem; background: var(--stitch-primary-container); color: var(--stitch-primary); border-radius: 50%;\">\n                          <i class=\"bi bi-people\"></i>\n                      </div>\n                      <h5 class=\"fw-bold text-dark mb-1\">No Members in View</h5>\n                      <p class=\"text-muted small mb-3\" style=\"max-width: 440px; margin: 0 auto;\">\n                          There are no matching relatives in this directory category yet. Invite or connect with your family members to build your network.\n                      </p>\n                      <a href=\"/familyStudio\" class=\"btn btn-primary btn-sm fw-bold px-4 py-2\" style=\"border-radius: var(--stitch-radius-pill);\">\n                          <i class=\"bi bi-plus-circle-fill me-1\"></i> Open Family Studio\n                      </a>\n                  </div>\n                ";
              }
            }
            sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
              title: 'Removed!',
              text: 'Member connection has been removed successfully.',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            errText = ((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'Error deleting profile';
            (0,_shared__WEBPACK_IMPORTED_MODULE_4__.msgException)(errText);
            sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire('Error', errText, 'error');
          }
          _context.next = 9;
          break;
        case 8:
          _context.prev = 8;
          _t = _context["catch"](6);
          (0,_shared__WEBPACK_IMPORTED_MODULE_4__.showError)(_t);
          _errText = ((_err$response = _t.response) === null || _err$response === void 0 ? void 0 : (_err$response$data = _err$response.data) === null || _err$response$data === void 0 ? void 0 : _err$response$data.message) || _t.message || 'Error deleting profile';
          sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire('Error', _errText, 'error');
        case 9:
          _context.next = 11;
          break;
        case 10:
          if (targetId.includes('seeProfile')) {
            // Extract the user ID from the target ID
            _userId2 = targetId.replace('seeProfile', ''); // redirect to 'allMembers/setProfile/'+userId
            window.location.href = "/allMembers/seeProfile/".concat(_userId2);
          } else if (targetId.includes('familyTree')) {
            // Extract the user ID from the target ID
            _userId3 = targetId.replace('familyTree', ''); // redirect to 'allMembers/setProfile/'+userId
            window.location.href = "/organogram/".concat(_userId3);
          }
        case 11:
          _context.next = 13;
          break;
        case 12:
          _context.prev = 12;
          _t2 = _context["catch"](0);
          // Handle any errors that occur during execution
          (0,_shared__WEBPACK_IMPORTED_MODULE_4__.showError)(_t2);
        case 13:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 12], [6, 8]]);
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
    var result, approverDetails, _t3;
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
          _t3 = _context2["catch"](0);
          console.error(_t3);
          throw _t3;
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
    ;(0,_shared__WEBPACK_IMPORTED_MODULE_4__.showError)(error);
  }
}

// Function to update the button's HTML and disable it
function updateButton(targetId, newHTML) {
  var theTargetId = (0,_shared__WEBPACK_IMPORTED_MODULE_4__.id)(targetId);
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
/* harmony export */   applyFilter: function() { return /* binding */ applyFilter; },
/* harmony export */   renderMembers: function() { return /* binding */ renderMembers; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
/* harmony import */ var _handleInput__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./handleInput */ "./resources/asset/js/components/allMembers/handleInput.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");




function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__["default"])(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
// resources/js/allMembers/api.js
;


var allMembersContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("allMembers");
var memberCountBadge = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("memberCount");
var memberCountDisplay = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("memberCountDisplay");
var searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("searchFamily");
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
  members.forEach(_html__WEBPACK_IMPORTED_MODULE_4__.renderHtml);

  // Update member count badges
  var countStr = members.length.toLocaleString();
  if (memberCountBadge) memberCountBadge.textContent = countStr;
  if (memberCountDisplay) memberCountDisplay.textContent = countStr;
};

// Client-side category pill filtering
function applyFilter() {
  var filterVal = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "all";
  var cards = allMembersContainer.querySelectorAll(".member-card");
  var visibleCount = 0;
  cards.forEach(function (card) {
    var cat = card.getAttribute("data-category");
    if (filterVal === "all") {
      // "all" shows all connected members in network (family + connected kin)
      if (cat === "family" || cat === "connected") {
        card.style.display = "";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    } else if (cat === filterVal) {
      card.style.display = "";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });
  var emptyPlaceholder = allMembersContainer.querySelector(".empty-filter-placeholder");
  if (visibleCount === 0 && cards.length > 0) {
    if (!emptyPlaceholder) {
      var message = filterVal === "explore" ? "No additional members in the public directory." : "No members in this category.";
      allMembersContainer.insertAdjacentHTML("beforeend", "<div class=\"empty-filter-placeholder col-12 text-center py-4 text-muted small\" style=\"grid-column: 1 / -1;\">".concat(message, "</div>"));
    }
  } else if (emptyPlaceholder) {
    emptyPlaceholder.remove();
  }
  return visibleCount;
}
function setupFilterPills() {
  var pillsContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("memberFilterPills");
  if (!pillsContainer) return;
  pillsContainer.addEventListener("click", function (e) {
    var btn = e.target.closest(".filter-pill-btn");
    if (!btn) return;
    pillsContainer.querySelectorAll(".filter-pill-btn").forEach(function (b) {
      return b.classList.remove("active");
    });
    btn.classList.add("active");
    var filterVal = btn.getAttribute("data-filter") || "all";
    var visibleCount = applyFilter(filterVal);
    if (memberCountBadge) {
      memberCountBadge.textContent = visibleCount.toLocaleString();
    }
  });
}
(function () {
  var _bootstrapAllMembers = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
    var url1, url2, _yield$Promise$all, _yield$Promise$all2, famCodeData, allUsersData, familyMembers, directoryMembers, normalizedDirMembers, seenIds, allLoadedMembers, loader, resetViewToCurrentFilter, handleSearch, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          url1 = "/allMembers/processApiData";
          url2 = "/allMembers/allData"; // Fetch caller's network (family/connected) and wider directory in parallel
          _context.next = 1;
          return Promise.all([(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.getApiData)(url1).catch(function () {
            return {
              message: []
            };
          }), (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.getApiData)(url2).catch(function () {
            return {
              message: []
            };
          })]);
        case 1:
          _yield$Promise$all = _context.sent;
          _yield$Promise$all2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__["default"])(_yield$Promise$all, 2);
          famCodeData = _yield$Promise$all2[0];
          allUsersData = _yield$Promise$all2[1];
          familyMembers = [];
          if (Array.isArray(famCodeData)) {
            familyMembers = famCodeData;
          } else if (Array.isArray(famCodeData === null || famCodeData === void 0 ? void 0 : famCodeData.message)) {
            familyMembers = famCodeData.message;
          } else if (Array.isArray(famCodeData === null || famCodeData === void 0 ? void 0 : famCodeData.data)) {
            familyMembers = famCodeData.data;
          }
          directoryMembers = [];
          if (Array.isArray(allUsersData)) {
            directoryMembers = allUsersData;
          } else if (Array.isArray(allUsersData === null || allUsersData === void 0 ? void 0 : allUsersData.message)) {
            directoryMembers = allUsersData.message;
          } else if (Array.isArray(allUsersData === null || allUsersData === void 0 ? void 0 : allUsersData.data)) {
            directoryMembers = allUsersData.data;
          }

          // Tag directory members with relationType 'other' unless already set
          normalizedDirMembers = directoryMembers.map(function (m) {
            return _objectSpread(_objectSpread({}, m), {}, {
              relationType: m.relationType || "other"
            });
          }); // Deduplicate any overlap by ID (prioritizing family/connected)
          seenIds = new Set();
          allLoadedMembers = [];
          familyMembers.forEach(function (m) {
            if (m && m.id && !seenIds.has(m.id)) {
              seenIds.add(m.id);
              allLoadedMembers.push(m);
            }
          });
          normalizedDirMembers.forEach(function (m) {
            if (m && m.id && !seenIds.has(m.id)) {
              seenIds.add(m.id);
              allLoadedMembers.push(m);
            }
          });

          // Pre-render all members (family + connected + directory) into DOM
          renderMembers(allLoadedMembers);

          // Initial view: show connected network by default
          applyFilter("all");
          if (memberCountBadge) {
            memberCountBadge.textContent = familyMembers.length.toLocaleString();
          }

          // Remove loading spinner
          loader = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("setLoader");
          if (loader) {
            loader.classList.remove("loader");
            loader.classList.add("d-none");
            loader.style.display = "none";
          }

          // Setup filter pills
          setupFilterPills();

          // Wire up search handler across both client-side loaded members & server endpoint
          if (searchInput) {
            resetViewToCurrentFilter = function resetViewToCurrentFilter() {
              var _id;
              renderMembers(allLoadedMembers);
              var activeBtn = (_id = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("memberFilterPills")) === null || _id === void 0 ? void 0 : _id.querySelector(".filter-pill-btn.active");
              var activeFilter = (activeBtn === null || activeBtn === void 0 ? void 0 : activeBtn.getAttribute("data-filter")) || "all";
              applyFilter(activeFilter);
              if (memberCountBadge) {
                var count = activeFilter === "all" ? familyMembers.length : activeFilter === "explore" ? normalizedDirMembers.length : allLoadedMembers.length;
                memberCountBadge.textContent = count.toLocaleString();
              }
            };
            handleSearch = (0,_handleInput__WEBPACK_IMPORTED_MODULE_5__.createSearchHandler)({
              familyMembers,
              directoryMembers: normalizedDirMembers,
              allLoadedMembers,
              renderMembers,
              container: allMembersContainer,
              searchUrl: "/allMembers/search",
              onResetView: resetViewToCurrentFilter
            });
            searchInput.addEventListener("input", handleSearch);
            searchInput.addEventListener("search", handleSearch);
          }
          _context.next = 3;
          break;
        case 2:
          _context.prev = 2;
          _t = _context["catch"](0);
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.showError)(_t);
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
          return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/register/contactNewMember", postObj, {
            headers: {
              "X-CSRF-TOKEN": (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
              "X-XSRF-TOKEN": (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
            }
          });
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
 * Factory that returns a debounced search handler using both client-side loaded members
 * and backend /allMembers/search endpoint for complete directory discovery.
 *
 * @param {object} options
 * @param {Array<object>} options.familyMembers
 * @param {Array<object>} options.directoryMembers
 * @param {Array<object>} options.allLoadedMembers
 * @param {Function} options.renderMembers
 * @param {HTMLElement} options.container
 * @param {string} options.searchUrl
 * @param {Function} [options.onResetView]
 * @returns {() => void}
 */
var createSearchHandler = function createSearchHandler(_ref2) {
  var _ref2$familyMembers = _ref2.familyMembers,
    familyMembers = _ref2$familyMembers === void 0 ? [] : _ref2$familyMembers,
    _ref2$directoryMember = _ref2.directoryMembers,
    directoryMembers = _ref2$directoryMember === void 0 ? [] : _ref2$directoryMember,
    _ref2$allLoadedMember = _ref2.allLoadedMembers,
    allLoadedMembers = _ref2$allLoadedMember === void 0 ? [] : _ref2$allLoadedMember,
    renderMembers = _ref2.renderMembers,
    container = _ref2.container,
    searchUrl = _ref2.searchUrl,
    onResetView = _ref2.onResetView;
  var searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("searchFamily");
  var debounceTimer = null;
  var performLocalFilter = function performLocalFilter(rawQuery) {
    var q = rawQuery.toLowerCase();
    var cleanDigits = q.replace(/[^0-9]/g, "");
    return (allLoadedMembers || []).filter(function (m) {
      var fName = (m.firstName || "").toLowerCase();
      var lName = (m.lastName || "").toLowerCase();
      var fullName = "".concat(fName, " ").concat(lName).trim();
      var fullNameRev = "".concat(lName, " ").concat(fName).trim();
      var email = (m.email || "").toLowerCase();
      var mobile = (m.mobile || "").toLowerCase();
      var mobileDigits = mobile.replace(/[^0-9]/g, "");
      var famCode = (m.famCode || "").toLowerCase();
      var country = (m.country || "").toLowerCase();
      return fName.includes(q) || lName.includes(q) || fullName.includes(q) || fullNameRev.includes(q) || email.includes(q) || mobile.includes(q) || cleanDigits.length >= 3 && mobileDigits.includes(cleanDigits) || famCode.includes(q) || country.includes(q);
    });
  };
  var performSearch = /*#__PURE__*/function () {
    var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2() {
      var rawQuery, pillsContainer, memberCountBadge, memberCountDisplay, resetPillsToAll, localMatches, response, data, matches, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            rawQuery = searchInput.value.trim();
            pillsContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("memberFilterPills");
            memberCountBadge = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("memberCount");
            memberCountDisplay = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("memberCountDisplay");
            resetPillsToAll = function resetPillsToAll() {
              if (pillsContainer) {
                pillsContainer.querySelectorAll(".filter-pill-btn").forEach(function (b) {
                  return b.classList.remove("active");
                });
                var allBtn = pillsContainer.querySelector('[data-filter="all"]');
                if (allBtn) allBtn.classList.add("active");
              }
            }; // empty query = back to original view
            if (rawQuery) {
              _context2.next = 1;
              break;
            }
            if (typeof onResetView === "function") {
              onResetView();
            } else {
              resetPillsToAll();
              renderMembers(familyMembers);
            }
            return _context2.abrupt("return");
          case 1:
            // 1. Instant client-side matches across all loaded directory & family members
            localMatches = performLocalFilter(rawQuery);
            if (localMatches.length > 0) {
              resetPillsToAll();
              renderMembers(localMatches);
            }

            // 2. Server-side debounced search for full platform directory coverage
            _context2.prev = 2;
            _context2.next = 3;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get(searchUrl, {
              params: {
                q: rawQuery
              }
            });
          case 3:
            response = _context2.sent;
            data = response.data || {};
            matches = data.message || [];
            if (!(!matches.length && localMatches.length === 0)) {
              _context2.next = 4;
              break;
            }
            if (memberCountBadge) memberCountBadge.textContent = "0";
            if (memberCountDisplay) memberCountDisplay.textContent = "0";
            renderInviteBlock(container, rawQuery);
            return _context2.abrupt("return");
          case 4:
            if (matches.length > 0) {
              resetPillsToAll();
              renderMembers(matches);
            }
            _context2.next = 6;
            break;
          case 5:
            _context2.prev = 5;
            _t2 = _context2["catch"](2);
            if (localMatches.length === 0) {
              (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.showNotification)("allMembers", "is-danger", "Search failed. Please try again.");
              (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.msgException)(_t2);
            }
          case 6:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[2, 5]]);
    }));
    return function performSearch() {
      return _ref3.apply(this, arguments);
    };
  }();
  return function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(performSearch, 150);
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
    var html = "\n    <div class=\"member-card member_profile_".concat(idSafe, "\" id=\"").concat(idSafe, "\" data-category=\"").concat(categoryKey, "\" data-search=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(fullNameRaw.toLowerCase() + ' ' + (el.email || '')), "\">\n        <div class=\"card-cover\"></div>\n\n        <div class=\"avatar-wrapper\">\n             <img src=\"").concat(imgSafe, "\" alt=\"").concat(fullName, "\" loading=\"lazy\">\n             <span class=\"avatar-online-dot\"></span>\n        </div>\n\n        <div class=\"member-card-body\">\n            ").concat(roleChipHtml, "\n            <h4 class=\"member-name text-truncate\" title=\"").concat(fullName, "\">").concat(fullName, "</h4>\n\n            <span class=\"member-location-chip\">\n                <i class=\"bi bi-geo-alt-fill text-danger\"></i> ").concat(location, "\n            </span>\n\n            <div class=\"member-details\">\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-hash\"></i>\n                    <span class=\"fw-bold\">").concat(famCode, "</span>\n                </div>\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-envelope\"></i>\n                    <span class=\"text-truncate\">").concat(emailSafe, "</span>\n                </div>\n                ").concat(relType !== 'other' ? "\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-calendar-check\"></i> \n                    <span>Connected ".concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(el.created_at), "</span>\n                </div>\n                ") : '', "\n            </div>\n\n            <div class=\"member-interests\">\n                ").concat(relType === 'family' ? "\n                <button class=\"btn-stitch-primary\" id=\"seeProfile".concat(idSafe, "\">\n                    <i class=\"bi bi-person-badge-fill\" style=\"pointer-events: none;\"></i> View Profile\n                </button>\n                <div class=\"d-flex gap-2\">\n                    <button class=\"btn-stitch-tonal flex-grow-1\" id=\"familyTree").concat(idSafe, "\" title=\"View in Family Tree\">\n                        <i class=\"bi bi-diagram-3-fill\" style=\"pointer-events: none;\"></i> Tree\n                    </button>\n                    <button class=\"btn-stitch-danger flex-grow-1\" id=\"removeProfile").concat(idSafe, "\" title=\"Remove from Family\">\n                        <i class=\"bi bi-person-dash-fill\" style=\"pointer-events: none;\"></i> Remove\n                    </button>\n                </div>\n                ") : relType === 'approved_you' || relType === 'you_approved' ? "\n                <button class=\"btn-stitch-primary\" id=\"seeProfile".concat(idSafe, "\">\n                    <i class=\"bi bi-person-badge-fill\" style=\"pointer-events: none;\"></i> View Profile\n                </button>\n                <div class=\"d-flex gap-2\">\n                    <button class=\"btn-stitch-tonal flex-grow-1\" id=\"familyTree").concat(idSafe, "\" title=\"View in Family Tree\">\n                        <i class=\"bi bi-diagram-3-fill\" style=\"pointer-events: none;\"></i> Tree\n                    </button>\n                    <button class=\"btn-stitch-danger flex-grow-1\" id=\"removeProfile").concat(idSafe, "\" title=\"Remove Connection\">\n                        <i class=\"bi bi-person-dash-fill\" style=\"pointer-events: none;\"></i> Remove\n                    </button>\n                </div>\n                ") : "\n                <button class=\"btn-stitch-connect\" \n                        data-user-id=\"addFamily".concat(idSafe, "\" \n                        id=\"addFamily").concat(idSafe, "\"\n                        ").concat(disableButton, ">\n                    <i class=\"bi bi-person-plus-fill\" style=\"pointer-events: none;\"></i> ").concat(statusButtonHTML, "\n                </button>\n                <small class=\"text-muted text-center\" style=\"font-size: 0.75rem; font-weight: 500;\">").concat(tooltip, "</small>\n                "), "\n            </div>\n        </div>\n    </div>\n    ");
    var container = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers');
    if (container) {
      container.insertAdjacentHTML('beforeend', html);
    }
  } catch (error) {
    ;(0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
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