"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["code"],{

/***/ "./resources/asset/js/components/acctMgt/code.js":
/*!*******************************************************!*\
  !*** ./resources/asset/js/components/acctMgt/code.js ***!
  \*******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");






// Determine redirect target based on session flag set by the forgot-password flow.
var fromForgot = sessionStorage.getItem('fromForgot');
var redirectTo = fromForgot ? '/changePW' : '/profilePage';
if (fromForgot) sessionStorage.removeItem('fromForgot');

// The shared library binds the click handler on #button and submits form#code,
// posting the hidden #codeValue field that the OTP boxes below keep in sync.
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.createCodeSubmitHandler)({
  formId: 'code',
  route: '/login/code',
  buttonId: 'button',
  redirect: redirectTo,
  theme: 'bootstrap',
  lengthLimitArray: {
    id: ['codeValue'],
    max: [6]
  },
  recaptchaAction: 'LOGIN_CODE'
});

// Normalise Eastern-Arabic / Persian digits to ASCII and strip non-alphanumerics.
var normalizeDigits = function normalizeDigits(str) {
  if (!str) return '';
  return str.replace(/[٠-٩]/g, function (c) {
    return String.fromCharCode(c.charCodeAt(0) - 0x0660 + 48);
  }).replace(/[۰-۹]/g, function (c) {
    return String.fromCharCode(c.charCodeAt(0) - 0x06F0 + 48);
  }).replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
};
var _initOtp = function initOtp() {
  var _id3;
  var otpForm = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('code');
  var otpInputs = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.qSelAll)('.otp-input');
  var hiddenCodeInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('codeValue');
  var pasteBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('pasteBtn');
  var resendBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('resendBtn');

  // Blade may not be parsed yet on first tick — retry briefly.
  if (!otpInputs.length || !hiddenCodeInput) {
    setTimeout(_initOtp, 100);
    return;
  }
  var updateHiddenInput = function updateHiddenInput() {
    var raw = Array.from(otpInputs).map(function (i) {
      return i.value;
    }).join('');
    hiddenCodeInput.value = normalizeDigits(raw);
  };
  var fillFromString = function fillFromString(value) {
    var code = normalizeDigits(value).substring(0, otpInputs.length);
    if (!code.length) return;
    code.split('').forEach(function (char, i) {
      if (otpInputs[i]) otpInputs[i].value = char;
    });
    updateHiddenInput();
    otpInputs[Math.min(code.length - 1, otpInputs.length - 1)].focus();
  };
  otpInputs.forEach(function (input, index) {
    input.addEventListener('input', function (e) {
      var clean = normalizeDigits(e.target.value);
      e.target.value = clean ? clean.slice(-1) : '';
      if (clean && index < otpInputs.length - 1) otpInputs[index + 1].focus();
      updateHiddenInput();
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Backspace' && !e.target.value && index > 0) {
        otpInputs[index - 1].focus();
      }
    });
    input.addEventListener('paste', function (e) {
      e.preventDefault();
      var pasted = (e.clipboardData || window.clipboardData).getData('text');
      fillFromString(pasted);
    });
  });

  // All submission goes through the shared library's click handler on #button.
  // Never let the form submit natively (e.g. Enter key) as a bare GET — route
  // it back through the button once the 6 digits are in.
  if (otpForm) {
    otpForm.addEventListener('submit', function (e) {
      var _id;
      e.preventDefault();
      updateHiddenInput();
      if (hiddenCodeInput.value.length === 6) (_id = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('button')) === null || _id === void 0 ? void 0 : _id.click();
    });
  }
  if (pasteBtn) {
    pasteBtn.addEventListener('click', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
      var text, _t;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            _context.prev = 0;
            _context.next = 1;
            return navigator.clipboard.readText();
          case 1:
            text = _context.sent;
            fillFromString(text);
            _context.next = 3;
            break;
          case 2:
            _context.prev = 2;
            _t = _context["catch"](0);
            if (window.Swal) {
              window.Swal.fire({
                icon: 'info',
                title: 'Clipboard unavailable',
                text: 'Your browser blocked clipboard access. Please type the code manually.',
                confirmButtonColor: '#7b03fc'
              });
            }
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[0, 2]]);
    })));
  }
  if (resendBtn) {
    resendBtn.addEventListener('click', /*#__PURE__*/function () {
      var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(e) {
        var original, _id2, response, result, _window$Swal, _window$Swal2, _window$Swal3, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              e.preventDefault();
              original = resendBtn.innerHTML;
              resendBtn.style.pointerEvents = 'none';
              resendBtn.textContent = 'Sending…';
              _context2.prev = 1;
              _context2.next = 2;
              return fetch('/login/code/resend', {
                method: 'POST',
                headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'X-XSRF-TOKEN': ((_id2 = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('token')) === null || _id2 === void 0 ? void 0 : _id2.value) || ''
                }
              });
            case 2:
              response = _context2.sent;
              _context2.next = 3;
              return response.json().catch(function () {
                return {};
              });
            case 3:
              result = _context2.sent;
              if (response.ok) {
                (_window$Swal = window.Swal) === null || _window$Swal === void 0 ? void 0 : _window$Swal.fire({
                  icon: 'success',
                  title: 'Code sent',
                  text: (result === null || result === void 0 ? void 0 : result.message) || 'A new verification code has been sent to your email.',
                  confirmButtonColor: '#7b03fc'
                });
              } else {
                (_window$Swal2 = window.Swal) === null || _window$Swal2 === void 0 ? void 0 : _window$Swal2.fire({
                  icon: 'error',
                  title: 'Could not resend',
                  text: (result === null || result === void 0 ? void 0 : result.message) || 'Failed to resend the code. Please try again.',
                  confirmButtonColor: '#7b03fc'
                });
              }
              _context2.next = 5;
              break;
            case 4:
              _context2.prev = 4;
              _t2 = _context2["catch"](1);
              (_window$Swal3 = window.Swal) === null || _window$Swal3 === void 0 ? void 0 : _window$Swal3.fire({
                icon: 'error',
                title: 'Network error',
                text: 'A connection error occurred. Please check your network and try again.',
                confirmButtonColor: '#7b03fc'
              });
            case 5:
              _context2.prev = 5;
              resendBtn.innerHTML = original;
              resendBtn.style.pointerEvents = 'auto';
              return _context2.finish(5);
            case 6:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[1, 4, 5, 6]]);
      }));
      return function (_x) {
        return _ref2.apply(this, arguments);
      };
    }());
  }
  otpInputs[0].focus();
  (_id3 = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('button')) === null || _id3 === void 0 ? void 0 : _id3.setAttribute('data-ready', 'true');
};
_initOtp();

/***/ })

}]);
//# sourceMappingURL=code.js.map