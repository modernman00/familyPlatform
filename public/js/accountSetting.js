"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["accountSetting"],{

/***/ "./resources/asset/js/components/accountSetting.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/accountSetting.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _accountSettingHelpers_handleFamilyChangeBootstrap__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./accountSettingHelpers/handleFamilyChangeBootstrap */ "./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");
/* harmony import */ var _profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./profilePage/registerPushNotification */ "./resources/asset/js/components/profilePage/registerPushNotification.js");










// Register update handlers for all account & family forms
var formsToRegister = [{
  formId: 'profileForm',
  buttonId: 'profileBtn',
  redirect: '/accountSetting#v-pills-profile'
}, {
  formId: 'accountSettingForm',
  buttonId: 'button',
  redirect: '/accountSetting'
},
// backward compatibility
{
  formId: 'parentsForm',
  buttonId: 'parentsBtn',
  redirect: '/accountSetting#v-pills-parents'
}, {
  formId: 'childrenForm',
  buttonId: 'childrenBtn',
  redirect: '/accountSetting#v-pills-children'
}, {
  formId: 'siblingsForm',
  buttonId: 'siblingsBtn',
  redirect: '/accountSetting#v-pills-siblings'
}, {
  formId: 'maritalForm',
  buttonId: 'maritalBtn',
  redirect: '/accountSetting#v-pills-marital'
}, {
  formId: 'passwordForm',
  buttonId: 'passwordBtn',
  redirect: '/accountSetting#v-pills-password'
}, {
  formId: 'preferencesForm',
  buttonId: 'preferencesBtn',
  redirect: '/accountSetting#v-pills-preferences'
}, {
  formId: 'privacyForm',
  buttonId: 'privacyBtn',
  redirect: '/accountSetting#v-pills-privacy'
}];
formsToRegister.forEach(function (_ref) {
  var formId = _ref.formId,
    buttonId = _ref.buttonId,
    redirect = _ref.redirect;
  if ((0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(formId) && (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(buttonId)) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.update)({
      formId,
      route: '/accountSetting',
      buttonId,
      redirect: redirect || '/accountSetting'
    });
  }
});

// Function to show/hide spouse information based on marital status
var showSpouse = function showSpouse() {
  var maritalEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('maritalStatus');
  if (!maritalEl) return;
  var maritalStatus = maritalEl.value;
  if (maritalStatus === "Yes - Add Husband") {
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.showElement)('spouse');
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.hideElement)('maiden_name_div');
  } else if (maritalStatus === "Yes - Add Wife") {
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.showElement)('maiden_name_div');
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.showElement)('spouse');
  } else {
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.hideElement)('spouse');
    (0,_global__WEBPACK_IMPORTED_MODULE_3__.hideElement)('maiden_name_div');
  }
};
var maritalSelect = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('maritalStatus');
if (maritalSelect) {
  maritalSelect.addEventListener('change', showSpouse);
  // Initial check on load
  showSpouse();
}

// URL Hash navigation and direct Family Connections tab scroll support
var scrollToFormPane = function scrollToFormPane(tabId) {
  var formMap = {
    'v-pills-parents-tab': 'v-pills-parents',
    'v-pills-children-tab': 'v-pills-children',
    'v-pills-siblings-tab': 'v-pills-siblings',
    'v-pills-marital-tab': 'v-pills-marital',
    'v-pills-familycode-tab': 'v-pills-familycode'
  };
  var targetPaneId = formMap[tabId];
  if (!targetPaneId) return;
  setTimeout(function () {
    var pane = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(targetPaneId);
    if (pane) {
      pane.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  }, 100);
};
var handleHashNavigation = function handleHashNavigation() {
  var hash = window.location.hash;
  if (!hash) return;
  var hashMap = {
    '#profile': 'v-pills-profile-tab',
    '#v-pills-profile': 'v-pills-profile-tab',
    '#parents': 'v-pills-parents-tab',
    '#v-pills-parents': 'v-pills-parents-tab',
    '#family-settings': 'v-pills-parents-tab',
    '#children': 'v-pills-children-tab',
    '#v-pills-children': 'v-pills-children-tab',
    '#siblings': 'v-pills-siblings-tab',
    '#v-pills-siblings': 'v-pills-siblings-tab',
    '#marital': 'v-pills-marital-tab',
    '#v-pills-marital': 'v-pills-marital-tab',
    '#marital-status': 'v-pills-marital-tab',
    '#password': 'v-pills-password-tab',
    '#v-pills-password': 'v-pills-password-tab',
    '#preferences': 'v-pills-preferences-tab',
    '#v-pills-preferences': 'v-pills-preferences-tab',
    '#privacy': 'v-pills-privacy-tab',
    '#v-pills-privacy': 'v-pills-privacy-tab',
    '#familycode': 'v-pills-familycode-tab',
    '#v-pills-familycode': 'v-pills-familycode-tab',
    '#family-code': 'v-pills-familycode-tab',
    '#family-network': 'v-pills-familycode-tab'
  };
  var targetTabId = hashMap[hash.toLowerCase()];
  if (targetTabId) {
    var tabBtn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(targetTabId);
    if (tabBtn) {
      var _window$bootstrap;
      if (typeof window !== 'undefined' && (_window$bootstrap = window.bootstrap) !== null && _window$bootstrap !== void 0 && _window$bootstrap.Tab) {
        var tab = new window.bootstrap.Tab(tabBtn);
        tab.show();
      } else {
        tabBtn.click();
      }
      scrollToFormPane(targetTabId);
    }
  }
};

// Bind click listeners on Family Connections tabs for instant smooth scrolling directly to form below
['v-pills-parents-tab', 'v-pills-children-tab', 'v-pills-siblings-tab', 'v-pills-marital-tab', 'v-pills-familycode-tab'].forEach(function (btnId) {
  var btn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(btnId);
  if (btn) {
    btn.addEventListener('click', function () {
      return scrollToFormPane(btnId);
    });
  }
});
window.addEventListener('DOMContentLoaded', handleHashNavigation);
window.addEventListener('hashchange', handleHashNavigation);

// Kid / sibling email helper — checks each address against the server on demand
// (no bulk email list is pulled to the browser any more, SEC-4).
var fNameEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('fName');
var fName = fNameEl ? (fNameEl.textContent || '').trim() : '';
var famCode = localStorage.getItem('requesterFamCode');
(0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_5__.processKidsSiblings)(fName, famCode);

// ---- GDPR Art. 15 — "Download my data" ----------------------------------
(function initDataExport() {
  var btn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('dataExportBtn');
  var status = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('dataExportStatus');
  if (!btn) return;
  var say = function say(msg) {
    if (!status) return;
    status.textContent = msg;
    status.style.display = msg ? 'block' : 'none';
  };
  btn.addEventListener('click', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var _exec, res, blob, cd, name, url, a, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          btn.disabled = true;
          say('Preparing your data…');
          _context.prev = 1;
          _context.next = 2;
          return fetch('/account/data-export', {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
              'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
            }
          });
        case 2:
          res = _context.sent;
          if (res.ok) {
            _context.next = 3;
            break;
          }
          throw new Error("Export failed (".concat(res.status, ")"));
        case 3:
          _context.next = 4;
          return res.blob();
        case 4:
          blob = _context.sent;
          cd = res.headers.get('Content-Disposition') || '';
          name = ((_exec = /filename="?([^"]+)"?/.exec(cd)) === null || _exec === void 0 ? void 0 : _exec[1]) || 'familyplatform-data.json';
          url = URL.createObjectURL(blob);
          a = document.createElement('a');
          a.href = url;
          a.download = name;
          document.body.appendChild(a);
          a.click();
          a.remove();
          URL.revokeObjectURL(url);
          say('Download started. Check your downloads folder.');
          _context.next = 6;
          break;
        case 5:
          _context.prev = 5;
          _t = _context["catch"](1);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
          say('Sorry — we could not prepare your data. Please try again.');
        case 6:
          _context.prev = 6;
          btn.disabled = false;
          return _context.finish(6);
        case 7:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[1, 5, 6, 7]]);
  })));

  // ---- GDPR Art. 17 — Account deletion options (Immediate vs 30-Day Grace Period) ----
  var delBtn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('dataDeleteBtn');
  if (delBtn) {
    var executeDeletion = /*#__PURE__*/function () {
      var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(mode) {
        var res, body, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              delBtn.disabled = true;
              say('Processing your deletion request…');
              _context2.prev = 1;
              _context2.next = 2;
              return fetch('/account/request-deletion', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest',
                  'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
                  'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
                },
                body: JSON.stringify({
                  mode
                })
              });
            case 2:
              res = _context2.sent;
              _context2.next = 3;
              return res.json().catch(function () {
                return {};
              });
            case 3:
              body = _context2.sent;
              if (res.ok) {
                _context2.next = 4;
                break;
              }
              throw new Error((body === null || body === void 0 ? void 0 : body.message) || "Request failed (".concat(res.status, ")"));
            case 4:
              say((body === null || body === void 0 ? void 0 : body.message) || 'Your deletion request has been processed.');
              if (!window.Swal) {
                _context2.next = 5;
                break;
              }
              _context2.next = 5;
              return window.Swal.fire({
                icon: 'success',
                title: mode === 'immediate' ? 'Account Permanently Erased' : 'Deletion Scheduled',
                text: (body === null || body === void 0 ? void 0 : body.message) || 'Your account deletion request has been processed.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'Return to Sign In'
              });
            case 5:
              window.location.href = (body === null || body === void 0 ? void 0 : body.redirect) || '/login';
              _context2.next = 7;
              break;
            case 6:
              _context2.prev = 6;
              _t2 = _context2["catch"](1);
              (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t2);
              if (window.Swal) {
                window.Swal.fire({
                  icon: 'error',
                  title: 'Deletion Request Failed',
                  text: _t2.message || 'Sorry — we could not process your deletion request. Please try again or contact support.'
                });
              }
              say('Sorry — we could not process your request. Please try again or email support.');
            case 7:
              _context2.prev = 7;
              delBtn.disabled = false;
              return _context2.finish(7);
            case 8:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[1, 6, 7, 8]]);
      }));
      return function executeDeletion(_x) {
        return _ref3.apply(this, arguments);
      };
    }();
    delBtn.addEventListener('click', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3() {
      var choice, _yield$window$Swal$fi, selectedOption, isImmediate, confirmResult;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
        while (1) switch (_context3.prev = _context3.next) {
          case 0:
            if (window.Swal) {
              _context3.next = 1;
              break;
            }
            choice = window.confirm('Do you want to permanently erase your account immediately (Click OK)? Or cancel (Click Cancel)?');
            if (choice) executeDeletion('immediate');
            return _context3.abrupt("return");
          case 1:
            _context3.next = 2;
            return window.Swal.fire({
              icon: 'warning',
              title: 'Delete Your Account',
              html: "\n          <div class=\"text-start\" style=\"font-size: 0.95rem;\">\n            <p class=\"text-muted mb-3\">Under GDPR Article 17, please select your preferred deletion method:</p>\n            <div class=\"p-3 mb-3 border rounded-3\" style=\"background-color: #f8fafc; cursor: pointer;\">\n              <label class=\"d-flex align-items-start gap-2\" style=\"cursor: pointer;\">\n                <input class=\"form-check-input mt-1\" type=\"radio\" name=\"deletionMode\" id=\"delModeSchedule\" value=\"schedule\" checked style=\"cursor: pointer;\">\n                <div>\n                  <strong class=\"d-block text-dark\">Option B: 30-Day Scheduled Deletion (Grace Period)</strong>\n                  <span class=\"small text-muted d-block mt-1\">\n                    Your account is scheduled for deletion in 30 days and you will be signed out. You can cancel anytime within 30 days simply by signing back in.\n                  </span>\n                </div>\n              </label>\n            </div>\n            <div class=\"p-3 border rounded-3\" style=\"background-color: #fff5f5; border-color: #fecaca !important; cursor: pointer;\">\n              <label class=\"d-flex align-items-start gap-2\" style=\"cursor: pointer;\">\n                <input class=\"form-check-input mt-1\" type=\"radio\" name=\"deletionMode\" id=\"delModeImmediate\" value=\"immediate\" style=\"cursor: pointer;\">\n                <div>\n                  <strong class=\"d-block text-danger\">Option A: Delete Immediately (Permanent Erasure)</strong>\n                  <span class=\"small text-danger-emphasis d-block mt-1\">\n                    Instantly purges and anonymises your profile, credentials, and uploaded media. Shared family trees are preserved in anonymised form. <strong>This cannot be undone.</strong>\n                  </span>\n                </div>\n              </label>\n            </div>\n          </div>\n        ",
              showCancelButton: true,
              confirmButtonText: 'Continue',
              confirmButtonColor: '#dc2626',
              cancelButtonText: 'Cancel',
              preConfirm: function preConfirm() {
                var checked = document.querySelector('input[name="deletionMode"]:checked');
                return checked ? checked.value : 'schedule';
              }
            });
          case 2:
            _yield$window$Swal$fi = _context3.sent;
            selectedOption = _yield$window$Swal$fi.value;
            if (selectedOption) {
              _context3.next = 3;
              break;
            }
            return _context3.abrupt("return");
          case 3:
            isImmediate = selectedOption === 'immediate';
            _context3.next = 4;
            return window.Swal.fire({
              icon: 'warning',
              title: isImmediate ? 'Confirm Immediate Permanent Erasure?' : 'Confirm 30-Day Scheduled Deletion?',
              html: isImmediate ? 'Your personal data, media, and credentials will be permanently erased immediately. You will not be able to recover or sign into this account.' : 'Your account will be deactivated and scheduled for deletion in 30 days. You will be signed out now. You may sign back in at any point within 30 days to cancel this request.',
              showCancelButton: true,
              confirmButtonText: isImmediate ? 'Yes, erase my account now' : 'Yes, schedule deletion',
              confirmButtonColor: '#dc2626',
              cancelButtonText: 'Keep My Account'
            });
          case 4:
            confirmResult = _context3.sent;
            if (confirmResult.isConfirmed) {
              _context3.next = 5;
              break;
            }
            return _context3.abrupt("return");
          case 5:
            executeDeletion(selectedOption);
          case 6:
          case "end":
            return _context3.stop();
        }
      }, _callee3);
    })));
  }
})();

// ---- Browser / mobile push notification toggle (PUSH-1) ------------------
(function initPushToggle() {
  var toggle = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('pushPrefToggle');
  var label = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('pushPrefLabel');
  var hint = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('pushPrefHint');
  if (!toggle) return;
  var DEFAULT_HINT = 'Get instant alerts on this device even when the app is closed.';
  var PUSH_OPT_OUT_KEY = 'fp_push_pref_disabled';
  var paint = function paint(on, note) {
    toggle.checked = on;
    if (label) label.textContent = on ? 'ON' : 'OFF';
    if (hint) hint.textContent = note || DEFAULT_HINT;
  };
  var state = (0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.getPushState)();
  if (state === 'unsupported') {
    toggle.disabled = true;
    var pwa = window.pwaManager;
    if (pwa && pwa.isIOS && !pwa.isStandalone) {
      // iOS only delivers Web Push to an installed PWA (iOS 16.4+). Route the
      // user through the existing "Add to Home Screen" walkthrough (PUSH-3).
      if (hint) {
        hint.innerHTML = 'On iPhone, notifications work once the app is on your Home Screen. ' + '<a href="#" id="pushIosInstall" style="font-weight:600;text-decoration:underline;">Show me how</a>.';
        var link = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('pushIosInstall');
        if (link) {
          link.addEventListener('click', function (e) {
            var _pwa$showIOSOverlay;
            e.preventDefault();
            (_pwa$showIOSOverlay = pwa.showIOSOverlay) === null || _pwa$showIOSOverlay === void 0 ? void 0 : _pwa$showIOSOverlay.call(pwa);
          });
        }
      }
    } else {
      paint(false, 'This browser does not support push notifications.');
    }
    return;
  }
  if (state === 'denied') {
    toggle.disabled = true;
    paint(false, 'Notifications are blocked in your browser settings. Re-enable them there, then reload this page.');
    return;
  }
  ;(0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.isPushSubscribed)().then(function (subbed) {
    if (subbed) {
      localStorage.removeItem(PUSH_OPT_OUT_KEY);
      paint(true);
    } else if (localStorage.getItem(PUSH_OPT_OUT_KEY) === '1') {
      paint(false);
    } else {
      paint(true);
      if (state === 'granted') {
        (0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.enablePushNotifications)().catch(function () {});
      }
    }
  });
  toggle.addEventListener('change', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee4() {
    var wantOn, res, _window$notify, _window, _window$notify2, _window2, _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context4) {
      while (1) switch (_context4.prev = _context4.next) {
        case 0:
          toggle.disabled = true;
          wantOn = toggle.checked;
          _context4.prev = 1;
          if (!wantOn) {
            _context4.next = 3;
            break;
          }
          localStorage.removeItem(PUSH_OPT_OUT_KEY);
          _context4.next = 2;
          return (0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.enablePushNotifications)();
        case 2:
          res = _context4.sent;
          if (res.ok) {
            paint(true, 'Push notifications are on for this device.');
            (_window$notify = (_window = window).notify) === null || _window$notify === void 0 ? void 0 : _window$notify.call(_window, 'Push notifications enabled', 'success');
          } else if (res.reason === 'denied') {
            paint(false, 'You dismissed the browser prompt. Allow notifications to turn this on.');
          } else {
            paint(false, 'Could not enable notifications. Please try again.');
          }
          _context4.next = 5;
          break;
        case 3:
          localStorage.setItem(PUSH_OPT_OUT_KEY, '1');
          _context4.next = 4;
          return (0,_profilePage_registerPushNotification__WEBPACK_IMPORTED_MODULE_6__.disablePushNotifications)();
        case 4:
          paint(false, 'Push notifications are off for this device.');
          (_window$notify2 = (_window2 = window).notify) === null || _window$notify2 === void 0 ? void 0 : _window$notify2.call(_window2, 'Push notifications turned off', 'success');
        case 5:
          _context4.next = 7;
          break;
        case 6:
          _context4.prev = 6;
          _t3 = _context4["catch"](1);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t3);
          paint(!wantOn);
        case 7:
          _context4.prev = 7;
          toggle.disabled = false;
          return _context4.finish(7);
        case 8:
        case "end":
          return _context4.stop();
      }
    }, _callee4, null, [[1, 6, 7, 8]]);
  })));
})();

/***/ }),

/***/ "./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js":
/*!********************************************************************************************!*\
  !*** ./resources/asset/js/components/accountSettingHelpers/handleFamilyChangeBootstrap.js ***!
  \********************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   show: function() { return /* binding */ show; }
/* harmony export */ });
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _renderFamilyBootstrap__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./renderFamilyBootstrap */ "./resources/asset/js/components/accountSettingHelpers/renderFamilyBootstrap.js");
/* harmony import */ var _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../data/checkEmailObj */ "./resources/asset/js/data/checkEmailObj.js");
/* harmony import */ var _data_checkEmailFactory__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../data/checkEmailFactory */ "./resources/asset/js/data/checkEmailFactory.js");







/**
 * Adapted for Account Setting Page (Bootstrap 5)
 */

var syncCheckEmailObj = function syncCheckEmailObj() {
  var _id, _id2;
  var kids = Number((_id = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("children")) === null || _id === void 0 ? void 0 : _id.value) || 0;
  var siblings = Number((_id2 = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("sibling")) === null || _id2 === void 0 ? void 0 : _id2.value) || 0;

  // IMPORTANT: mutate the same object reference
  Object.assign(_data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj, (0,_data_checkEmailFactory__WEBPACK_IMPORTED_MODULE_3__.makeCheckEmailObj)(kids, siblings));
};
var show = function show(kids_or_sib, event) {
  try {
    var value = Number(event.target.value) || 0;

    // ✅ unique container IDs (avoid clashing with <select id="children">)
    var containerId = "".concat(kids_or_sib, "_inputs");
    var parentId = "".concat(kids_or_sib, "_div");

    // remove the existing dynamic container
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.removeDiv)(containerId);
    var helpEl = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help"));
    if (helpEl) helpEl.innerHTML = "";
    if (value === 0) {
      syncCheckEmailObj();
      return;
    }
    // create the container under wrapper
    ;(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.createAndAppendElement)("div", containerId, parentId);
    if (helpEl) {
      helpEl.innerHTML = value > 1 ? "Please, provide details for each below:" : "Please, provide details below:";
      helpEl.classList.remove("text-danger"); // Use BS5 classes or JS logic? keeping simple
      helpEl.style.color = ""; // Reset custom color if any
    }
    var container = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(containerId);
    if (!container) return;
    for (var i = 0; i < value; i++) {
      var no = i + 1;
      var html = (0,_renderFamilyBootstrap__WEBPACK_IMPORTED_MODULE_1__.renderHtmlFamilyBootstrap)(kids_or_sib, no);
      container.insertAdjacentHTML("beforeEnd", html);
    }

    // 🔥 after DOM changes, regenerate ID lists
    syncCheckEmailObj();

    // 🎯 Auto smooth scroll directly to the generated form below
    setTimeout(function () {
      container.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    }, 50);
  } catch (error) {
    ;(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
var onChangeKidAndSiblings = function onChangeKidAndSiblings() {
  var kidInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("children");
  var sibInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)("sibling");
  if (kidInput) kidInput.addEventListener('change', function (event) {
    return show('children', event);
  });
  if (sibInput) sibInput.addEventListener('change', function (event) {
    return show('sibling', event);
  });

  // initialise on page load too (if selects already have values)
  syncCheckEmailObj();
};
onChangeKidAndSiblings();

/***/ }),

/***/ "./resources/asset/js/components/accountSettingHelpers/renderFamilyBootstrap.js":
/*!**************************************************************************************!*\
  !*** ./resources/asset/js/components/accountSettingHelpers/renderFamilyBootstrap.js ***!
  \**************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtmlFamilyBootstrap: function() { return /* binding */ renderHtmlFamilyBootstrap; }
/* harmony export */ });
var renderHtmlFamilyBootstrap = function renderHtmlFamilyBootstrap(family, no) {
  if (!no) return "";
  var kids_sib = family === "children" ? "children" : "sibling";
  var labelText = family === "children" ? "Child" : "Sibling";
  var optionsHtmlText = family === "children" ? "With Spouse?" : "Relationship";
  var optionsHtml = "\n    <option value=\"select\" disabled selected style=\"color: #64748b; background-color: #ffffff;\">Select status</option>\n    <option value=\"With Spouse\" style=\"color: #0f172a; background-color: #ffffff;\">With Spouse</option>\n    <option value=\"Not With Spouse\" style=\"color: #0f172a; background-color: #ffffff;\">Single / Other</option>\n  ";
  if (family === "sibling") {
    optionsHtml = "\n      <option value=\"select\" disabled selected style=\"color: #64748b; background-color: #ffffff;\">Select type</option>\n      <option value=\"Same_Mother_Father\" style=\"color: #0f172a; background-color: #ffffff;\">Same Father & Mother</option>\n      <option value=\"Same_Father\" style=\"color: #0f172a; background-color: #ffffff;\">Same Father Only</option>\n      <option value=\"Same_Mother\" style=\"color: #0f172a; background-color: #ffffff;\">Same Mother Only</option>\n    ";
  }

  // Match the new Account Settings "Spouse Details" container style with high contrast fonts
  return "\n    <div class=\"p-4 rounded-3 mb-4 wrapper-".concat(kids_sib, "-").concat(no, "\" style=\"background-color: #ffffff; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.03);\">\n      \n      <h6 class=\"fw-bold mb-3 text-uppercase\" style=\"color: #0f172a; letter-spacing: 0.5px; font-size: 0.9rem;\">\n        <i class=\"bi bi-").concat(family === 'children' ? 'person-heart text-primary' : 'person-lines-fill text-indigo', " me-1\"></i> #").concat(no, " ").concat(labelText, " Information\n      </h6>\n\n      <div class=\"row g-3\">\n      \n        <!-- Relationship Select -->\n        <div class=\"col-md-4\">\n           <label class=\"form-label text-dark fw-bold\" for=\"").concat(kids_sib, "_option").concat(no, "\">\n               ").concat(optionsHtmlText, "\n           </label>\n           <select class=\"form-select text-dark bg-white border\" name=\"").concat(kids_sib, "_option").concat(no, "\" id=\"").concat(kids_sib, "_option").concat(no, "\">\n              ").concat(optionsHtml, "\n           </select>\n        </div>\n\n        <!-- First Name Input -->\n        <div class=\"col-md-4\">\n           <label class=\"form-label text-dark fw-bold\" for=\"").concat(kids_sib, "_first_name").concat(no, "\">\n               First Name\n           </label>\n           <input \n              type=\"text\" \n              class=\"form-control text-dark bg-white border\" \n              placeholder=\"e.g. John\" \n              name=\"").concat(kids_sib, "_first_name").concat(no, "\" \n              id=\"").concat(kids_sib, "_first_name").concat(no, "\"\n              autocomplete=\"off\"\n           >\n        </div>\n\n        <!-- Last Name Input -->\n        <div class=\"col-md-4\">\n           <label class=\"form-label text-dark fw-bold\" for=\"").concat(kids_sib, "_last_name").concat(no, "\">\n               Last Name\n           </label>\n           <input \n              type=\"text\" \n              class=\"form-control text-dark bg-white border\" \n              placeholder=\"e.g. Doe\" \n              name=\"").concat(kids_sib, "_last_name").concat(no, "\" \n              id=\"").concat(kids_sib, "_last_name").concat(no, "\"\n              autocomplete=\"off\"\n           >\n        </div>\n\n        <!-- Email Input (Full Width) -->\n        <div class=\"col-12\">\n           <label class=\"form-label text-dark fw-bold\" for=\"").concat(kids_sib, "_email").concat(no, "\">\n               Email Address\n           </label>\n           <input \n              type=\"email\" \n              class=\"form-control text-dark bg-white border\" \n              placeholder=\"e.g. john.doe@example.com\" \n              name=\"").concat(kids_sib, "_email").concat(no, "\" \n              id=\"").concat(kids_sib, "_email").concat(no, "\"\n              autocomplete=\"off\"\n           >\n           <!-- Dynamic help text area -->\n           <div class=\"form-text text-danger mt-1 fw-semibold\" id=\"").concat(kids_sib, "_email").concat(no, "_help\" style=\"min-height: 20px; font-size: 0.82rem;\"></div>\n        </div>\n\n      </div>\n    </div>\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/api/index.js":
/*!****************************************************!*\
  !*** ./resources/asset/js/components/api/index.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   emailIsRegistered: function() { return /* binding */ emailIsRegistered; },
/* harmony export */   getAllData: function() { return /* binding */ getAllData; },
/* harmony export */   postData: function() { return /* binding */ postData; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");




var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
var URL = '/';
var getAllData = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var response, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          _context.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("".concat(URL, "allMembers/processApiData2"), config);
        case 1:
          response = _context.sent;
          return _context.abrupt("return", response.data);
        case 2:
          _context.prev = 2;
          _t = _context["catch"](0);
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
          // You can perform additional error handling actions if needed
          throw _t;
        case 3:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 2]]);
  }));
  return function getAllData() {
    return _ref.apply(this, arguments);
  };
}();
var postData = /*#__PURE__*/function () {
  var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(url, object) {
    var response, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url, object);
        case 1:
          response = _context2.sent;
          console.log(response);
          _context2.next = 3;
          break;
        case 2:
          _context2.prev = 2;
          _t2 = _context2["catch"](0);
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__.showError)(_t2);
          // You can perform additional error handling actions if needed
          throw _t2;
        case 3:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 2]]);
  }));
  return function postData(_x, _x2) {
    return _ref2.apply(this, arguments);
  };
}();

/**
 * Ask the server whether a single email address already belongs to a registered
 * account. Replaces the old bulk fetch of every member email (SEC-4).
 * @param {string} email
 * @returns {Promise<boolean>}
 */
var emailIsRegistered = /*#__PURE__*/function () {
  var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(email) {
    var _response$data, _response$data$messag, response, _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.prev = 0;
          _context3.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("".concat(URL, "getEmails"), {
            params: {
              email
            },
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
        case 1:
          response = _context3.sent;
          return _context3.abrupt("return", (response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : (_response$data$messag = _response$data.message) === null || _response$data$messag === void 0 ? void 0 : _response$data$messag.exists) === true);
        case 2:
          _context3.prev = 2;
          _t3 = _context3["catch"](0);
          // Fail closed-ish: treat as "not registered" so the invite path still works.
          console.error('emailIsRegistered check failed:', _t3);
          return _context3.abrupt("return", false);
        case 3:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[0, 2]]);
  }));
  return function emailIsRegistered(_x3) {
    return _ref3.apply(this, arguments);
  };
}();

/***/ }),

/***/ "./resources/asset/js/components/kidsAndSiblings.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/kidsAndSiblings.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   processKidsSiblings: function() { return /* binding */ processKidsSiblings; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../data/checkEmailObj */ "./resources/asset/js/data/checkEmailObj.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./api/index */ "./resources/asset/js/components/api/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");







var processKidsSiblings = function processKidsSiblings(firstName) {
  var famCode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  // Per-email existence check (scoped server lookup) with a tiny local cache so
  // repeated keystrokes on the same address don't re-hit the endpoint.
  var existsCache = new Map();
  var checkExists = /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(email) {
      var result;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            if (!existsCache.has(email)) {
              _context.next = 1;
              break;
            }
            return _context.abrupt("return", existsCache.get(email));
          case 1:
            _context.next = 2;
            return (0,_api_index__WEBPACK_IMPORTED_MODULE_5__.emailIsRegistered)(email);
          case 2:
            result = _context.sent;
            existsCache.set(email, result);
            return _context.abrupt("return", result);
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }));
    return function checkExists(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  var getFamCode = function getFamCode() {
    var _ref2, _id$value, _id;
    return (_ref2 = (_id$value = (_id = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("famCode_id")) === null || _id === void 0 ? void 0 : _id.value) !== null && _id$value !== void 0 ? _id$value : famCode) !== null && _ref2 !== void 0 ? _ref2 : "";
  };

  // debounce so it doesn't fire too aggressively
  var t = null;
  var debounce = function debounce(fn) {
    var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 200;
    return function () {
      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }
      clearTimeout(t);
      t = setTimeout(function () {
        return fn.apply(void 0, args);
      }, wait);
    };
  };
  var onInput = debounce(/*#__PURE__*/function () {
    var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(e) {
      var _id$value2, _id2, el, elementId, isKid, isSib, emailInput, helpEl, chooseEmail, chooseName, index, nameId, nameValue, _id$value3, _id3, _id$value4, _id4, prefix, no, fn, ln, exists, _t;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            _context2.prev = 0;
            el = e.target;
            if (!(!el || el.tagName !== "INPUT" || el.type !== "email")) {
              _context2.next = 1;
              break;
            }
            return _context2.abrupt("return");
          case 1:
            elementId = el.id;
            if (elementId) {
              _context2.next = 2;
              break;
            }
            return _context2.abrupt("return");
          case 2:
            // Only handle the ids we generate
            isKid = _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.kidEmailInput.includes(elementId);
            isSib = _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingEmail.includes(elementId);
            if (!(!isKid && !isSib)) {
              _context2.next = 3;
              break;
            }
            return _context2.abrupt("return");
          case 3:
            emailInput = (el.value || "").toLowerCase().trim();
            helpEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(elementId, "_help"));
            if (helpEl) {
              _context2.next = 4;
              break;
            }
            return _context2.abrupt("return");
          case 4:
            chooseEmail = isKid ? _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.kidEmailInput : _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingEmail;
            chooseName = isKid ? _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.childrenNameInput : _data_checkEmailObj__WEBPACK_IMPORTED_MODULE_2__.checkEmailObj.siblingName;
            index = chooseEmail.indexOf(elementId);
            nameId = chooseName[index];
            nameValue = (_id$value2 = (_id2 = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(nameId)) === null || _id2 === void 0 ? void 0 : _id2.value) !== null && _id$value2 !== void 0 ? _id$value2 : "";
            if (!nameValue) {
              prefix = isKid ? "children" : "sibling";
              no = index + 1;
              fn = (_id$value3 = (_id3 = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(prefix, "_first_name").concat(no))) === null || _id3 === void 0 ? void 0 : _id3.value) !== null && _id$value3 !== void 0 ? _id$value3 : "";
              ln = (_id$value4 = (_id4 = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(prefix, "_last_name").concat(no))) === null || _id4 === void 0 ? void 0 : _id4.value) !== null && _id$value4 !== void 0 ? _id$value4 : "";
              nameValue = "".concat(fn, " ").concat(ln).trim();
            }
            if (emailInput) {
              _context2.next = 5;
              break;
            }
            helpEl.style.display = "none";
            helpEl.innerHTML = "";
            delete helpEl.dataset.email;
            delete helpEl.dataset.name;
            delete helpEl.dataset.familyCode;
            return _context2.abrupt("return");
          case 5:
            if (!(emailInput.length > 0 && emailInput.length < 7)) {
              _context2.next = 6;
              break;
            }
            helpEl.style.display = "block";
            helpEl.innerHTML = "Email may be too short";
            return _context2.abrupt("return");
          case 6:
            _context2.next = 7;
            return checkExists(emailInput);
          case 7:
            exists = _context2.sent;
            helpEl.style.display = "block";
            helpEl.dataset.email = emailInput;
            helpEl.dataset.name = nameValue;
            helpEl.dataset.familyCode = getFamCode();
            helpEl.innerHTML = exists ? "Great news! ".concat(nameValue || "This person", " is already registered on the platform") : "".concat(nameValue || "This person", " is not on the platform. Do you want us to send an email invite? ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_4__.checkBox)(elementId));
            _context2.next = 9;
            break;
          case 8:
            _context2.prev = 8;
            _t = _context2["catch"](0);
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
          case 9:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[0, 8]]);
    }));
    return function (_x2) {
      return _ref3.apply(this, arguments);
    };
  }(), 250);
  var onClick = /*#__PURE__*/function () {
    var _ref4 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(e) {
      var target, isYes, isNo, baseId, helpEl, email, name, familyCode, postObj, response, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
        while (1) switch (_context3.prev = _context3.next) {
          case 0:
            _context3.prev = 0;
            target = e.target;
            if (!(!target || !target.id)) {
              _context3.next = 1;
              break;
            }
            return _context3.abrupt("return");
          case 1:
            isYes = target.id.endsWith("Yes");
            isNo = target.id.endsWith("No");
            if (!(!isYes && !isNo)) {
              _context3.next = 2;
              break;
            }
            return _context3.abrupt("return");
          case 2:
            baseId = target.id.replace(/(Yes|No)$/, "");
            helpEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(baseId, "_help"));
            if (helpEl) {
              _context3.next = 3;
              break;
            }
            return _context3.abrupt("return");
          case 3:
            if (!isNo) {
              _context3.next = 4;
              break;
            }
            helpEl.style.display = "none";
            return _context3.abrupt("return");
          case 4:
            if (!(helpEl.dataset.sending === "1")) {
              _context3.next = 5;
              break;
            }
            return _context3.abrupt("return");
          case 5:
            helpEl.dataset.sending = "1";
            email = helpEl.dataset.email;
            name = helpEl.dataset.name;
            familyCode = helpEl.dataset.familyCode;
            if (!(!email || !name)) {
              _context3.next = 6;
              break;
            }
            helpEl.dataset.sending = "";
            return _context3.abrupt("return");
          case 6:
            postObj = {
              mobile: "",
              viewPath: "msg/contactNewMember",
              data: {
                email,
                name,
                yourName: firstName,
                familyCode
              },
              subject: "".concat(firstName, " wants you to join the family network")
            };
            _context3.next = 7;
            return axios__WEBPACK_IMPORTED_MODULE_6__["default"].post("/register/contactNewMember", postObj, {
              headers: {
                "X-CSRF-TOKEN": (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
                "X-XSRF-TOKEN": (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
              }
            });
          case 7:
            response = _context3.sent;
            helpEl.innerHTML = response.data.message || "Invite sent";
            setTimeout(function () {
              helpEl.style.display = "none";
            }, 5000);
            helpEl.dataset.sending = "";
            _context3.next = 9;
            break;
          case 8:
            _context3.prev = 8;
            _t2 = _context3["catch"](0);
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t2);
          case 9:
          case "end":
            return _context3.stop();
        }
      }, _callee3, null, [[0, 8]]);
    }));
    return function onClick(_x3) {
      return _ref4.apply(this, arguments);
    };
  }();
  document.addEventListener("input", onInput, true);
  document.addEventListener("click", onClick, true);

  // optional cleanup (if you ever re-init this)
  return function () {
    document.removeEventListener("input", onInput, true);
    document.removeEventListener("click", onClick, true);
  };
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/registerPushNotification.js":
/*!*******************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/registerPushNotification.js ***!
  \*******************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   disablePushNotifications: function() { return /* binding */ disablePushNotifications; },
/* harmony export */   enablePushNotifications: function() { return /* binding */ enablePushNotifications; },
/* harmony export */   getPushState: function() { return /* binding */ getPushState; },
/* harmony export */   isPushSubscribed: function() { return /* binding */ isPushSubscribed; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


/**
 * Web Push — the single client module (PUSH-1 / PUSH-4).
 *
 * Side effect on import: registers the service worker and, if the browser
 * permission is already `granted`, re-syncs the subscription so the server's
 * record stays fresh.
 *
 * Exports for a Settings toggle (must be called from a real user gesture):
 *   enablePushNotifications()  -> Promise<{ok, reason?}>
 *   disablePushNotifications() -> Promise<{ok}>
 *   getPushState()             -> 'unsupported' | 'default' | 'granted' | 'denied'
 *   isPushSubscribed()         -> Promise<boolean>
 */


var VAPID_PUBLIC_KEY = "BAvqqppvGj5V0DqzieyYq5nGu9EW_db01_7jXO1_Nk-8UZzKJpCs1eGYx5d0yuBe7q3xu6oWaFS8etO9lazRMMo" || 0;
var PUSH_PROMPT_DISMISSED_KEY = 'push_prompt_dismissed';
var pushSupported = function pushSupported() {
  return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
};
function urlBase64ToUint8Array(base64String) {
  var padding = '='.repeat((4 - base64String.length % 4) % 4);
  var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  var raw = window.atob(base64);
  var out = new Uint8Array(raw.length);
  for (var i = 0; i < raw.length; ++i) out[i] = raw.charCodeAt(i);
  return out;
}
function keyToBase64(subscription, name) {
  return btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey(name))));
}
function syncToServer(_x) {
  return _syncToServer.apply(this, arguments);
} // ---- import-time bootstrap: keep an existing grant in sync -----------------
function _syncToServer() {
  _syncToServer = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(subscription) {
    var token, payload;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          if (!(!subscription || !subscription.endpoint)) {
            _context2.next = 1;
            break;
          }
          return _context2.abrupt("return");
        case 1:
          token = (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)();
          payload = {
            endpoint: subscription.endpoint,
            token: token,
            keys: {
              p256dh: keyToBase64(subscription, 'p256dh'),
              auth: keyToBase64(subscription, 'auth')
            }
          };
          _context2.next = 2;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/pushNotification/subscription', payload, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': token,
              'X-CSRF-TOKEN': token
            }
          });
        case 2:
        case "end":
          return _context2.stop();
      }
    }, _callee2);
  }));
  return _syncToServer.apply(this, arguments);
}
if (pushSupported()) {
  navigator.serviceWorker.register('/sw.js').then(function (swReg) {
    return swReg.pushManager.getSubscription().then(function (sub) {
      if (sub) return syncToServer(sub).catch(function (e) {
        var _e$response, _e$response$data;
        return console.warn('[push] resync failed', (e === null || e === void 0 ? void 0 : (_e$response = e.response) === null || _e$response === void 0 ? void 0 : (_e$response$data = _e$response.data) === null || _e$response$data === void 0 ? void 0 : _e$response$data.message) || e.message);
      });
      if (Notification.permission === 'granted' && VAPID_PUBLIC_KEY) {
        return doSubscribe(swReg).catch(function (e) {
          var _e$response2, _e$response2$data;
          return console.warn('[push] auto-subscribe skipped', (e === null || e === void 0 ? void 0 : (_e$response2 = e.response) === null || _e$response2 === void 0 ? void 0 : (_e$response2$data = _e$response2.data) === null || _e$response2$data === void 0 ? void 0 : _e$response2$data.message) || e.message);
        });
      }
    });
  }).catch(function (err) {
    return console.warn('[push] SW registration failed', err);
  });
}
function doSubscribe(_x2) {
  return _doSubscribe.apply(this, arguments);
}
function _doSubscribe() {
  _doSubscribe = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(swReg) {
    var sub;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.next = 1;
          return swReg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
          });
        case 1:
          sub = _context3.sent;
          _context3.next = 2;
          return syncToServer(sub);
        case 2:
          return _context3.abrupt("return", sub);
        case 3:
        case "end":
          return _context3.stop();
      }
    }, _callee3);
  }));
  return _doSubscribe.apply(this, arguments);
}
function showPushPrompt() {
  var _document$getElementB, _document$getElementB2;
  if (!VAPID_PUBLIC_KEY || Notification.permission !== 'default') return;
  if (localStorage.getItem(PUSH_PROMPT_DISMISSED_KEY)) return;
  if (document.getElementById('push-permission-banner')) return;
  var banner = document.createElement('div');
  banner.id = 'push-permission-banner';
  banner.innerHTML = "\n    <div class=\"pwa-banner-card\">\n      <div class=\"pwa-banner-header\">\n        <div class=\"pwa-banner-text\">\n          <h6>Stay connected with your family</h6>\n          <p>Allow notifications for new friend requests, events, and family updates.</p>\n        </div>\n        <button type=\"button\" id=\"push-prompt-close\" class=\"pwa-btn-close\" aria-label=\"Dismiss\">&times;</button>\n      </div>\n      <div class=\"pwa-banner-actions\">\n        <button type=\"button\" id=\"push-prompt-enable\" class=\"pwa-btn pwa-btn-primary\">Allow notifications</button>\n      </div>\n    </div>\n  ";
  document.body.appendChild(banner);
  setTimeout(function () {
    return banner.classList.add('show');
  }, 100);
  var dismiss = function dismiss() {
    localStorage.setItem(PUSH_PROMPT_DISMISSED_KEY, String(Date.now()));
    banner.remove();
  };
  (_document$getElementB = document.getElementById('push-prompt-close')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.addEventListener('click', dismiss);
  (_document$getElementB2 = document.getElementById('push-prompt-enable')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.addEventListener('click', /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(event) {
      var button, result;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            button = event.currentTarget;
            button.disabled = true;
            button.textContent = 'Enabling...';
            _context.next = 1;
            return enablePushNotifications();
          case 1:
            result = _context.sent;
            if (!result.ok) {
              _context.next = 2;
              break;
            }
            banner.remove();
            return _context.abrupt("return");
          case 2:
            button.disabled = false;
            button.textContent = 'Allow notifications';
            if (result.reason === 'denied') dismiss();
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }));
    return function (_x3) {
      return _ref.apply(this, arguments);
    };
  }());
}
if (pushSupported()) {
  window.addEventListener('load', showPushPrompt, {
    once: true
  });
}

// ---- public API ----------------------------------------------------------
function getPushState() {
  if (!pushSupported()) return 'unsupported';
  return Notification.permission; // 'default' | 'granted' | 'denied'
}
function isPushSubscribed() {
  return _isPushSubscribed.apply(this, arguments);
}
function _isPushSubscribed() {
  _isPushSubscribed = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee4() {
    var swReg, _t, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context4) {
      while (1) switch (_context4.prev = _context4.next) {
        case 0:
          if (pushSupported()) {
            _context4.next = 1;
            break;
          }
          return _context4.abrupt("return", false);
        case 1:
          _context4.prev = 1;
          _context4.next = 2;
          return navigator.serviceWorker.ready;
        case 2:
          swReg = _context4.sent;
          _context4.next = 3;
          return swReg.pushManager.getSubscription();
        case 3:
          _t = _context4.sent;
          return _context4.abrupt("return", _t !== null);
        case 4:
          _context4.prev = 4;
          _t2 = _context4["catch"](1);
          return _context4.abrupt("return", false);
        case 5:
        case "end":
          return _context4.stop();
      }
    }, _callee4, null, [[1, 4]]);
  }));
  return _isPushSubscribed.apply(this, arguments);
}
function enablePushNotifications() {
  return _enablePushNotifications.apply(this, arguments);
}
function _enablePushNotifications() {
  _enablePushNotifications = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee5() {
    var permission, swReg, existing, _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context5) {
      while (1) switch (_context5.prev = _context5.next) {
        case 0:
          if (pushSupported()) {
            _context5.next = 1;
            break;
          }
          return _context5.abrupt("return", {
            ok: false,
            reason: 'unsupported'
          });
        case 1:
          if (VAPID_PUBLIC_KEY) {
            _context5.next = 2;
            break;
          }
          return _context5.abrupt("return", {
            ok: false,
            reason: 'misconfigured'
          });
        case 2:
          permission = Notification.permission;
          if (!(permission === 'default')) {
            _context5.next = 4;
            break;
          }
          _context5.next = 3;
          return Notification.requestPermission();
        case 3:
          permission = _context5.sent;
        case 4:
          if (!(permission !== 'granted')) {
            _context5.next = 5;
            break;
          }
          return _context5.abrupt("return", {
            ok: false,
            reason: permission
          });
        case 5:
          _context5.prev = 5;
          _context5.next = 6;
          return navigator.serviceWorker.ready;
        case 6:
          swReg = _context5.sent;
          _context5.next = 7;
          return swReg.pushManager.getSubscription();
        case 7:
          existing = _context5.sent;
          if (!existing) {
            _context5.next = 9;
            break;
          }
          _context5.next = 8;
          return syncToServer(existing);
        case 8:
          _context5.next = 10;
          break;
        case 9:
          _context5.next = 10;
          return doSubscribe(swReg);
        case 10:
          return _context5.abrupt("return", {
            ok: true
          });
        case 11:
          _context5.prev = 11;
          _t3 = _context5["catch"](5);
          console.error('[push] enable failed', _t3);
          return _context5.abrupt("return", {
            ok: false,
            reason: 'error'
          });
        case 12:
        case "end":
          return _context5.stop();
      }
    }, _callee5, null, [[5, 11]]);
  }));
  return _enablePushNotifications.apply(this, arguments);
}
function disablePushNotifications() {
  return _disablePushNotifications.apply(this, arguments);
}
function _disablePushNotifications() {
  _disablePushNotifications = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee6() {
    var swReg, sub, endpoint, _t4;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context6) {
      while (1) switch (_context6.prev = _context6.next) {
        case 0:
          if (pushSupported()) {
            _context6.next = 1;
            break;
          }
          return _context6.abrupt("return", {
            ok: true
          });
        case 1:
          _context6.prev = 1;
          _context6.next = 2;
          return navigator.serviceWorker.ready;
        case 2:
          swReg = _context6.sent;
          _context6.next = 3;
          return swReg.pushManager.getSubscription();
        case 3:
          sub = _context6.sent;
          if (!sub) {
            _context6.next = 5;
            break;
          }
          endpoint = sub.endpoint;
          _context6.next = 4;
          return sub.unsubscribe();
        case 4:
          _context6.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/pushNotification/unsubscribe', {
            endpoint
          }, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
              'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
            }
          }).catch(function (e) {
            return console.warn('[push] server unsubscribe failed', e);
          });
        case 5:
          return _context6.abrupt("return", {
            ok: true
          });
        case 6:
          _context6.prev = 6;
          _t4 = _context6["catch"](1);
          console.error('[push] disable failed', _t4);
          return _context6.abrupt("return", {
            ok: false
          });
        case 7:
        case "end":
          return _context6.stop();
      }
    }, _callee6, null, [[1, 6]]);
  }));
  return _disablePushNotifications.apply(this, arguments);
}

/***/ }),

/***/ "./resources/asset/js/data/checkEmailFactory.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/data/checkEmailFactory.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   makeCheckEmailObj: function() { return /* binding */ makeCheckEmailObj; }
/* harmony export */ });
// data/checkEmailFactory.js
var makeCheckEmailObj = function makeCheckEmailObj() {
  var kidsCount = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
  var siblingCount = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  var range = function range(n) {
    return Array.from({
      length: n
    }, function (_, i) {
      return i + 1;
    });
  };
  return {
    kidEmailInput: range(kidsCount).map(function (n) {
      return "children_email".concat(n);
    }),
    childrenNameInput: range(kidsCount).map(function (n) {
      return "children_name".concat(n);
    }),
    siblingEmail: range(siblingCount).map(function (n) {
      return "sibling_email".concat(n);
    }),
    siblingName: range(siblingCount).map(function (n) {
      return "sibling_name".concat(n);
    })
  };
};

/***/ }),

/***/ "./resources/asset/js/data/checkEmailObj.js":
/*!**************************************************!*\
  !*** ./resources/asset/js/data/checkEmailObj.js ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkEmailObj: function() { return /* binding */ checkEmailObj; }
/* harmony export */ });
// data/checkEmailObj.js
var checkEmailObj = {
  kidEmailInput: [],
  childrenNameInput: [],
  siblingEmail: [],
  siblingName: []
};

/***/ })

}]);
//# sourceMappingURL=accountSetting.js.map