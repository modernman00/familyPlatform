"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["/js/index"],{

/***/ "./resources/asset/js/components/darkMode.js":
/*!***************************************************!*\
  !*** ./resources/asset/js/components/darkMode.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "initDarkMode": function() { return /* binding */ initDarkMode; }
/* harmony export */ });


/**
 * Global Dark Mode Manager for FamilyPlatform
 * Persists user preference to localStorage and listens for system preference changes.
 */
function initDarkMode() {
  var toggleBtn = document.getElementById('darkModeToggle');
  var savedTheme = localStorage.getItem('familyPlatform_theme');
  var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  var isDark = savedTheme === 'dark' || savedTheme === null && prefersDark;
  var themeBadge = document.getElementById('themeStatusBadge');
  var applyTheme = function applyTheme(dark) {
    if (dark) {
      document.body.classList.add('dark-mode');
      if (themeBadge) {
        themeBadge.textContent = 'Dark';
        themeBadge.className = 'badge bg-warning text-dark';
      }
    } else {
      document.body.classList.remove('dark-mode');
      if (themeBadge) {
        themeBadge.textContent = 'Light';
        themeBadge.className = 'badge bg-secondary-subtle text-secondary';
      }
    }
  };

  // Apply on initial load
  applyTheme(isDark);
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      var nowDark = !document.body.classList.contains('dark-mode');
      applyTheme(nowDark);
      localStorage.setItem('familyPlatform_theme', nowDark ? 'dark' : 'light');
    });
  }
}

/***/ }),

/***/ "./resources/asset/js/components/global.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/global.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "checkElements": function() { return /* binding */ checkElements; },
/* harmony export */   "checkManyElements": function() { return /* binding */ checkManyElements; },
/* harmony export */   "date2String": function() { return /* binding */ date2String; },
/* harmony export */   "deleteNotification": function() { return /* binding */ deleteNotification; },
/* harmony export */   "esc": function() { return /* binding */ esc; },
/* harmony export */   "extractErrorMessage": function() { return /* binding */ extractErrorMessage; },
/* harmony export */   "getCsrfToken": function() { return /* binding */ getCsrfToken; },
/* harmony export */   "hideElement": function() { return /* binding */ hideElement; },
/* harmony export */   "id": function() { return /* binding */ id; },
/* harmony export */   "idInnerHTML": function() { return /* binding */ idInnerHTML; },
/* harmony export */   "idValue": function() { return /* binding */ idValue; },
/* harmony export */   "log": function() { return /* binding */ log; },
/* harmony export */   "manipulateAttribute": function() { return /* binding */ manipulateAttribute; },
/* harmony export */   "msgException": function() { return /* binding */ msgException; },
/* harmony export */   "qSel": function() { return /* binding */ qSel; },
/* harmony export */   "qSelAll": function() { return /* binding */ qSelAll; },
/* harmony export */   "qSelInnerHTML": function() { return /* binding */ qSelInnerHTML; },
/* harmony export */   "qSelValue": function() { return /* binding */ qSelValue; },
/* harmony export */   "showElement": function() { return /* binding */ showElement; },
/* harmony export */   "showError": function() { return /* binding */ showError; },
/* harmony export */   "showNotification": function() { return /* binding */ showNotification; },
/* harmony export */   "warningSign": function() { return /* binding */ warningSign; },
/* harmony export */   "write": function() { return /* binding */ write; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_2__);




if (typeof window !== "undefined") {
  window.Swal = (sweetalert2__WEBPACK_IMPORTED_MODULE_2___default());
}
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

/**
 * Escape a value for safe interpolation into an HTML template string.
 * Use this on every user-controlled field (names, bios, occupations, free text)
 * that ends up in an `.innerHTML = ` assignment \u2014 SEC-2.
 * @param {*} value
 * @returns {string}
 */
var esc = function esc(value) {
  if (value === null || value === undefined) return '';
  return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
};

/**
 * Current CSRF token. Prefers the `XSRF-TOKEN` cookie (kept in sync with the
 * session by app/config/init.php on every request) over the <meta> tag, which
 * is frozen at page render and goes stale after a session-token regeneration
 * (e.g. straight after 2FA).
 * @returns {string}
 */
var getCsrfToken = function getCsrfToken() {
  var _document$querySelect;
  var fromCookie = (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1];
  if (fromCookie) return decodeURIComponent(fromCookie);
  return ((_document$querySelect = document.querySelector('meta[name="csrf-token"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.getAttribute('content')) || '';
};
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
  if (e.name) log(e.name, " ERROR NAME"); // "TypeError"
  if (e.fileName) log(e.fileName, " ERROR FILENAME"); // "Scratchpad/1"
  if (e.lineNumber) log(e.lineNumber, " ERROR LINENUMBER"); // 2
  if (e.stack) log(e.stack);

  // Extract message if it's an Axios error
  var userMessage = "An unexpected error occurred.";
  if (e.response && e.response.data && e.response.data.message) {
    userMessage = e.response.data.message;
  } else if (e.message) {
    userMessage = e.message;
  }

  // Try to show it in an error div if it exists, otherwise alert
  var errEl = id('error');
  if (errEl) {
    errEl.innerHTML = userMessage;
    errEl.style.display = 'block';
    errEl.style.color = 'red';
  } else {
    sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
      icon: 'error',
      title: 'Error',
      text: userMessage,
      timer: 3000,
      showConfirmButton: false
    });
  }
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
  var Toast = sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: timer,
    timerProgressBar: true,
    didOpen: function didOpen(toast) {
      toast.onmouseenter = (sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().stopTimer);
      toast.onmouseleave = (sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().resumeTimer);
    }
  });
  var iconType = 'info';
  if (addClass.includes('success')) iconType = 'success';
  if (addClass.includes('danger') || addClass.includes('error')) iconType = 'error';
  if (addClass.includes('warning')) iconType = 'warning';
  Toast.fire({
    icon: iconType,
    title: message
  });
  var loader = id('loader');
  if (loader) loader.classList.remove('loader');
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
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(elementId) {
    var senderId, elementData, data, notificationHTML, url, response, newValues;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
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
          _context.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].put(url);
        case 1:
          response = _context.sent;
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
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return function deleteNotification(_x) {
    return _ref.apply(this, arguments);
  };
}();

/**
 * Defensive Error Unwrapping Utility
 *
 * Guarantees that [object Object] is NEVER returned under any error shape.
 * Handles strings, standard Error objects, Axios responses, validation maps.
 *
 * @param {any} err
 * @param {string} defaultMessage
 * @returns {string}
 */
var extractErrorMessage = function extractErrorMessage(err) {
  var defaultMessage = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'An unexpected error occurred.';
  if (err === null || err === undefined) {
    return defaultMessage;
  }
  if (typeof err === 'string') {
    var trimmed = err.trim();
    return trimmed && trimmed !== '[object Object]' ? trimmed : defaultMessage;
  }
  if (err && typeof err === 'object') {
    var resData = err.response ? err.response.data : err.data || null;
    if (resData) {
      if (typeof resData === 'string') {
        var _trimmed = resData.trim();
        if (_trimmed && _trimmed !== '[object Object]') return _trimmed;
      } else if (typeof resData === 'object') {
        if (typeof resData.error === 'string' && resData.error.trim() && resData.error.trim() !== '[object Object]') {
          return resData.error.trim();
        }
        if (typeof resData.message === 'string' && resData.message.trim() && resData.message.trim() !== '[object Object]') {
          return resData.message.trim();
        }
        if (typeof resData.msg === 'string' && resData.msg.trim() && resData.msg.trim() !== '[object Object]') {
          return resData.msg.trim();
        }
        var errorsSource = resData.errors || resData.error;
        if (errorsSource && typeof errorsSource === 'object') {
          if (Array.isArray(errorsSource)) {
            var msgs = errorsSource.map(function (m) {
              return typeof m === 'string' ? m : (m === null || m === void 0 ? void 0 : m.message) || JSON.stringify(m);
            }).filter(Boolean);
            if (msgs.length > 0) return msgs.join(', ');
          } else {
            var values = Object.values(errorsSource).flat().map(function (v) {
              return typeof v === 'string' ? v : (v === null || v === void 0 ? void 0 : v.message) || JSON.stringify(v);
            }).filter(Boolean);
            if (values.length > 0) return values.join(', ');
          }
        }
      }
    }
    if (typeof err.message === 'string' && err.message.trim() && err.message.trim() !== '[object Object]') {
      return err.message.trim();
    }
    if (typeof err.error === 'string' && err.error.trim() && err.error.trim() !== '[object Object]') {
      return err.error.trim();
    }
    if (typeof err.msg === 'string' && err.msg.trim() && err.msg.trim() !== '[object Object]') {
      return err.msg.trim();
    }
    if (err.errors && typeof err.errors === 'object') {
      if (Array.isArray(err.errors)) {
        var _msgs = err.errors.map(function (m) {
          return typeof m === 'string' ? m : (m === null || m === void 0 ? void 0 : m.message) || JSON.stringify(m);
        }).filter(Boolean);
        if (_msgs.length > 0) return _msgs.join(', ');
      } else {
        var _values = Object.values(err.errors).flat().map(function (v) {
          return typeof v === 'string' ? v : (v === null || v === void 0 ? void 0 : v.message) || JSON.stringify(v);
        }).filter(Boolean);
        if (_values.length > 0) return _values.join(', ');
      }
    }
    try {
      if (typeof err.toString === 'function') {
        var str = err.toString();
        if (str && str !== '[object Object]' && typeof str === 'string') {
          return str;
        }
      }
    } catch (_) {}
    try {
      var jsonStr = JSON.stringify(err);
      if (jsonStr && jsonStr !== '{}' && jsonStr.length < 200) {
        return jsonStr;
      }
    } catch (_) {}
  }
  return defaultMessage;
};
if (typeof window !== 'undefined') {
  window.extractErrorMessage = extractErrorMessage;
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/feedComponent.js":
/*!********************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/feedComponent.js ***!
  \********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "profileFeed": function() { return /* binding */ profileFeed; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _videoParser__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./videoParser */ "./resources/asset/js/components/profilePage/videoParser.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");




function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_2__["default"])(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }







function profileFeed() {
  var opts = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  return {
    posts: [],
    isLoading: true,
    errorMessage: '',
    lightboxOpen: false,
    lightboxImages: [],
    lightboxIndex: 0,
    currentUserId: opts.userId || localStorage.getItem('requesterId') || '',
    currentFamCode: opts.famCode || localStorage.getItem('requesterFamCode') || '',
    commentInputs: {},
    activeCommentForms: {},
    activeReactions: {},
    activeReactionBars: {},
    activeCommentReactionBars: {},
    commentEmojiOpen: {},
    pusher: null,
    editingCommentNo: null,
    editCommentText: '',
    csrfOptions: {
      xsrfCookieName: 'XSRF-TOKEN',
      xsrfHeaderName: 'X-XSRF-TOKEN'
    },
    // Emoji map shared across post + comment reactions
    emojiMap: {
      like: {
        icon: '👍',
        label: 'Like'
      },
      love: {
        icon: '❤️',
        label: 'Love'
      },
      haha: {
        icon: '😂',
        label: 'Haha'
      },
      shock: {
        icon: '😮',
        label: 'Wow'
      },
      sad: {
        icon: '😢',
        label: 'Sad'
      }
    },
    commentEmojiMap: {
      like: '👍',
      love: '❤️',
      haha: '😄',
      wow: '😮',
      sad: '😢',
      angry: '😠'
    },
    init() {
      var _this = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee() {
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              // Register listeners before awaiting the initial fetch - a post created (or a
              // Pusher event received) while that first fetch is still in flight must not be
              // silently dropped because nothing was listening for it yet.
              _this.initPusher();
              _this.initEventListeners();
              _context.next = 1;
              return _this.fetchPosts();
            case 1:
              _this.scrollToHashPost();
            case 2:
            case "end":
              return _context.stop();
          }
        }, _callee);
      }))();
    },
    // Jump to (and briefly highlight) the post referenced by a #postNNN link
    // (e.g. from an email/push notification). Posts render asynchronously, so the
    // browser's own hash-scroll-on-load never finds the element in time.
    scrollToHashPost() {
      var hash = window.location.hash;
      if (!hash || !hash.startsWith('#post')) return;
      this.$nextTick(function () {
        var target = document.getElementById(hash.slice(1));
        if (!target) return;
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
        target.classList.add('highlighted-post');
        setTimeout(function () {
          return target.classList.remove('highlighted-post');
        }, 2500);
      });
    },
    fetchPosts() {
      var _this2 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee2() {
        var cachedPosts, _response$data, _response$data$messag, response, rawPosts, _t, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _this2.isLoading = _this2.posts.length === 0;
              _this2.errorMessage = '';

              // 1. Instant offline cache hydration (Facebook standard)
              if (!(window.offlineSync && typeof window.offlineSync.getCachedFeed === 'function')) {
                _context2.next = 4;
                break;
              }
              _context2.prev = 1;
              _context2.next = 2;
              return window.offlineSync.getCachedFeed();
            case 2:
              cachedPosts = _context2.sent;
              if (Array.isArray(cachedPosts) && cachedPosts.length > 0 && _this2.posts.length === 0) {
                _this2.posts = cachedPosts.map(function (p) {
                  return _this2.normalizePost(p);
                });
                _this2.isLoading = false;
              }
              _context2.next = 4;
              break;
            case 3:
              _context2.prev = 3;
              _t = _context2["catch"](1);
              console.warn('[Feed] Offline cache read:', _t);
            case 4:
              _context2.prev = 4;
              _context2.next = 5;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"].get('/post/getAllPostCommentByFamCode');
            case 5:
              response = _context2.sent;
              rawPosts = response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : (_response$data$messag = _response$data.message) === null || _response$data$messag === void 0 ? void 0 : _response$data$messag.message;
              if (Array.isArray(rawPosts)) {
                _this2.posts = rawPosts.map(function (p) {
                  return _this2.normalizePost(p);
                });
                // Update offline store in background
                if (window.offlineSync && typeof window.offlineSync.cacheFeed === 'function') {
                  window.offlineSync.cacheFeed(rawPosts).catch(function () {});
                }
              } else if (_this2.posts.length === 0) {
                _this2.posts = [];
              }
              _context2.next = 7;
              break;
            case 6:
              _context2.prev = 6;
              _t2 = _context2["catch"](4);
              console.error('Failed to load posts from network:', _t2);
              if (_this2.posts.length === 0) {
                _this2.errorMessage = 'Unable to load family posts. Please check your network connection.';
              }
            case 7:
              _context2.prev = 7;
              _this2.isLoading = false;
              return _context2.finish(7);
            case 8:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[1, 3], [4, 6, 7, 8]]);
      }))();
    },
    normalizePost(p) {
      var _this3 = this;
      var profImg = '/public/avatar/avatarF.png';
      var rawImg = (p === null || p === void 0 ? void 0 : p.img) || (p === null || p === void 0 ? void 0 : p.profileImg);
      if (rawImg) {
        profImg = rawImg.startsWith('/') || rawImg.startsWith('http') ? rawImg : "/resources/images/profile/".concat(rawImg);
      }
      return {
        post_no: p === null || p === void 0 ? void 0 : p.post_no,
        id: p === null || p === void 0 ? void 0 : p.id,
        fullName: (p === null || p === void 0 ? void 0 : p.fullName) || 'Family Member',
        profileImg: profImg,
        postFamCode: (p === null || p === void 0 ? void 0 : p.postFamCode) || '',
        date_created: (p === null || p === void 0 ? void 0 : p.date_created) || new Date().toISOString(),
        post_time: (p === null || p === void 0 ? void 0 : p.post_time) || (p === null || p === void 0 ? void 0 : p.date_created) || new Date().toISOString(),
        postMessage: (p === null || p === void 0 ? void 0 : p.postMessage) || '',
        displayMessage: (0,_videoParser__WEBPACK_IMPORTED_MODULE_8__.cleanPostMessage)((p === null || p === void 0 ? void 0 : p.postMessage) || '', (0,_videoParser__WEBPACK_IMPORTED_MODULE_8__.extractVideoFromText)(p === null || p === void 0 ? void 0 : p.postMessage)),
        video: (0,_videoParser__WEBPACK_IMPORTED_MODULE_8__.extractVideoFromText)(p === null || p === void 0 ? void 0 : p.postMessage),
        post_likes: parseInt((p === null || p === void 0 ? void 0 : p.post_likes) || 0, 10),
        images: this.extractImages(p),
        poll: (p === null || p === void 0 ? void 0 : p.poll) || null,
        reactions: Array.isArray(p === null || p === void 0 ? void 0 : p.reactions) ? p.reactions : [],
        user_reaction: (p === null || p === void 0 ? void 0 : p.user_reaction) || null,
        comments: Array.isArray(p === null || p === void 0 ? void 0 : p.comments) ? p.comments.map(function (c) {
          return _this3.normalizeComment(c);
        }) : [],
        isLiked: false
      };
    },
    normalizeComment(c) {
      var _c$reactions$counts, _c$reactions, _c$reactions$counts$t, _c$reactions2, _c$reactions2$counts;
      var img = (c === null || c === void 0 ? void 0 : c.img) || (c === null || c === void 0 ? void 0 : c.profileImg);
      return {
        comment_no: c === null || c === void 0 ? void 0 : c.comment_no,
        post_no: c === null || c === void 0 ? void 0 : c.post_no,
        id: c === null || c === void 0 ? void 0 : c.id,
        fullName: (c === null || c === void 0 ? void 0 : c.fullName) || 'Family Member',
        profileImg: img ? "/resources/images/profile/".concat(img) : '/public/avatar/avatarM.png',
        comment: (c === null || c === void 0 ? void 0 : c.comment) || '',
        date_created: (c === null || c === void 0 ? void 0 : c.date_created) || '',
        comment_time: (c === null || c === void 0 ? void 0 : c.comment_time) || (c === null || c === void 0 ? void 0 : c.date_created) || '',
        reactions: (_c$reactions$counts = c === null || c === void 0 ? void 0 : (_c$reactions = c.reactions) === null || _c$reactions === void 0 ? void 0 : _c$reactions.counts) !== null && _c$reactions$counts !== void 0 ? _c$reactions$counts : {},
        totalReactions: (_c$reactions$counts$t = c === null || c === void 0 ? void 0 : (_c$reactions2 = c.reactions) === null || _c$reactions2 === void 0 ? void 0 : (_c$reactions2$counts = _c$reactions2.counts) === null || _c$reactions2$counts === void 0 ? void 0 : _c$reactions2$counts.totalReactions) !== null && _c$reactions$counts$t !== void 0 ? _c$reactions$counts$t : 0,
        userReaction: (c === null || c === void 0 ? void 0 : c.user_reaction) || null
      };
    },
    extractImages(p) {
      if (!p || typeof p !== 'object') return [];
      return Object.keys(p).filter(function (k) {
        return k.startsWith('post_img') && p[k] !== null && p[k] !== '' && typeof p[k] === 'string';
      }).map(function (k) {
        return p[k].trim();
      }).filter(function (img) {
        return img && img !== 'null' && img !== 'undefined' && img !== 'none' && !img.includes('no_image');
      });
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      try {
        return (0,timeago_js__WEBPACK_IMPORTED_MODULE_6__.format)(dateStr);
      } catch (e) {
        return dateStr;
      }
    },
    toggleCommentForm(postNo) {
      this.activeCommentForms[postNo] = !this.activeCommentForms[postNo];
    },
    submitComment(postNo) {
      var _this4 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee3() {
        var commentText, formData, _response$data2, response, _err$response, _err$response$data, _t3;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              commentText = (_this4.commentInputs[postNo] || '').trim();
              if (commentText) {
                _context3.next = 1;
                break;
              }
              return _context3.abrupt("return");
            case 1:
              formData = new FormData();
              formData.append('post_no', postNo);
              formData.append('comment', commentText);
              _context3.prev = 2;
              _context3.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"].post('/postCommentProfile', formData, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              });
            case 3:
              response = _context3.sent;
              if ((response === null || response === void 0 ? void 0 : (_response$data2 = response.data) === null || _response$data2 === void 0 ? void 0 : _response$data2.status) === 'success' || (response === null || response === void 0 ? void 0 : response.status) === 200) {
                // Don't append an optimistic local copy here: the Pusher
                // 'new-comment' handler (initPusher, below) already adds the
                // real broadcast comment in real time. Since that one carries
                // the real comment_no (this one only has a fake Date.now()
                // placeholder), the dedup check never matches and both stayed
                // on screen — one labeled "You", one with the real name.
                _this4.commentInputs[postNo] = '';
              }
              _context3.next = 5;
              break;
            case 4:
              _context3.prev = 4;
              _t3 = _context3["catch"](2);
              console.error('Failed to submit comment:', _t3);
              sweetalert2__WEBPACK_IMPORTED_MODULE_7___default().fire({
                icon: 'error',
                title: 'Submission Failed',
                text: (_t3 === null || _t3 === void 0 ? void 0 : (_err$response = _t3.response) === null || _err$response === void 0 ? void 0 : (_err$response$data = _err$response.data) === null || _err$response$data === void 0 ? void 0 : _err$response$data.message) || 'Failed to submit comment.',
                confirmButtonColor: '#3085d6'
              });
            case 5:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[2, 4]]);
      }))();
    },
    // ── Post edit/delete (author only) ──────────────────────────────────

    isOwnPost(post) {
      return String(post === null || post === void 0 ? void 0 : post.id) === String(this.currentUserId);
    },
    // Opens the existing "Create Post" modal in edit mode: prefills the text,
    // stamps a hidden post_no the modal's own submit handler (allEvents.js)
    // checks to decide between POST (create) and PUT (update).
    editPost(post) {
      var _window$bootstrap, _window$bootstrap$Mod;
      var postNoInput = document.getElementById('editPostNo');
      var textarea = document.getElementById('postMessage');
      var notice = document.getElementById('editPostNotice');
      if (!postNoInput || !textarea) return;
      postNoInput.value = post.post_no;
      textarea.value = post.postMessage || '';
      if (notice) notice.classList.remove('d-none');
      var modalTitle = document.getElementById('postModalLabel');
      if (modalTitle) modalTitle.textContent = 'Edit Post';
      var modalEl = document.getElementById('postModal');
      var instance = (_window$bootstrap = window.bootstrap) === null || _window$bootstrap === void 0 ? void 0 : (_window$bootstrap$Mod = _window$bootstrap.Modal) === null || _window$bootstrap$Mod === void 0 ? void 0 : _window$bootstrap$Mod.getOrCreateInstance(modalEl);
      instance === null || instance === void 0 ? void 0 : instance.show();
    },
    deletePost(postNo) {
      var _this5 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee4() {
        var result, _err$response2, _err$response2$data, _t4;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              _context4.next = 1;
              return sweetalert2__WEBPACK_IMPORTED_MODULE_7___default().fire({
                title: 'Delete this post?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
              });
            case 1:
              result = _context4.sent;
              if (result.isConfirmed) {
                _context4.next = 2;
                break;
              }
              return _context4.abrupt("return");
            case 2:
              _context4.prev = 2;
              _context4.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"]["delete"]("/post/".concat(postNo), _this5.csrfOptions);
            case 3:
              _this5.posts = _this5.posts.filter(function (p) {
                return String(p.post_no) !== String(postNo);
              });
              _context4.next = 5;
              break;
            case 4:
              _context4.prev = 4;
              _t4 = _context4["catch"](2);
              console.error('Failed to delete post:', _t4);
              sweetalert2__WEBPACK_IMPORTED_MODULE_7___default().fire({
                icon: 'error',
                title: 'Delete Failed',
                text: (_t4 === null || _t4 === void 0 ? void 0 : (_err$response2 = _t4.response) === null || _err$response2 === void 0 ? void 0 : (_err$response2$data = _err$response2.data) === null || _err$response2$data === void 0 ? void 0 : _err$response2$data.message) || 'Failed to delete post.',
                confirmButtonColor: '#3085d6'
              });
            case 5:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[2, 4]]);
      }))();
    },
    // ── Comment edit/delete (comment author or post author) ─────────────

    canEditComment(comment) {
      return String(comment === null || comment === void 0 ? void 0 : comment.id) === String(this.currentUserId);
    },
    canModerateComment(post, comment) {
      return this.canEditComment(comment) || String(post === null || post === void 0 ? void 0 : post.id) === String(this.currentUserId);
    },
    startEditComment(comment) {
      this.editingCommentNo = comment.comment_no;
      this.editCommentText = comment.comment;
    },
    cancelEditComment() {
      this.editingCommentNo = null;
      this.editCommentText = '';
    },
    saveCommentEdit(commentNo) {
      var _this6 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee5() {
        var text, _iterator, _step, post, comment, _err$response3, _err$response3$data, _t5, _t6;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context5) {
          while (1) switch (_context5.prev = _context5.next) {
            case 0:
              text = (_this6.editCommentText || '').trim();
              if (text) {
                _context5.next = 1;
                break;
              }
              return _context5.abrupt("return");
            case 1:
              _context5.prev = 1;
              _context5.next = 2;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"].put("/comment/".concat(commentNo), {
                comment: text
              }, _this6.csrfOptions);
            case 2:
              _iterator = _createForOfIteratorHelper(_this6.posts);
              _context5.prev = 3;
              _iterator.s();
            case 4:
              if ((_step = _iterator.n()).done) {
                _context5.next = 6;
                break;
              }
              post = _step.value;
              comment = post.comments.find(function (c) {
                return String(c.comment_no) === String(commentNo);
              });
              if (!comment) {
                _context5.next = 5;
                break;
              }
              comment.comment = text;
              return _context5.abrupt("continue", 6);
            case 5:
              _context5.next = 4;
              break;
            case 6:
              _context5.next = 8;
              break;
            case 7:
              _context5.prev = 7;
              _t5 = _context5["catch"](3);
              _iterator.e(_t5);
            case 8:
              _context5.prev = 8;
              _iterator.f();
              return _context5.finish(8);
            case 9:
              _this6.cancelEditComment();
              _context5.next = 11;
              break;
            case 10:
              _context5.prev = 10;
              _t6 = _context5["catch"](1);
              console.error('Failed to update comment:', _t6);
              sweetalert2__WEBPACK_IMPORTED_MODULE_7___default().fire({
                icon: 'error',
                title: 'Update Failed',
                text: (_t6 === null || _t6 === void 0 ? void 0 : (_err$response3 = _t6.response) === null || _err$response3 === void 0 ? void 0 : (_err$response3$data = _err$response3.data) === null || _err$response3$data === void 0 ? void 0 : _err$response3$data.message) || 'Failed to update comment.',
                confirmButtonColor: '#3085d6'
              });
            case 11:
            case "end":
              return _context5.stop();
          }
        }, _callee5, null, [[1, 10], [3, 7, 8, 9]]);
      }))();
    },
    deleteComment(postNo, commentNo) {
      var _this7 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee6() {
        var result, post, _err$response4, _err$response4$data, _t7;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              _context6.next = 1;
              return sweetalert2__WEBPACK_IMPORTED_MODULE_7___default().fire({
                title: 'Delete this comment?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
              });
            case 1:
              result = _context6.sent;
              if (result.isConfirmed) {
                _context6.next = 2;
                break;
              }
              return _context6.abrupt("return");
            case 2:
              _context6.prev = 2;
              _context6.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"]["delete"]("/comment/".concat(commentNo), _this7.csrfOptions);
            case 3:
              post = _this7.posts.find(function (p) {
                return String(p.post_no) === String(postNo);
              });
              if (post) {
                post.comments = post.comments.filter(function (c) {
                  return String(c.comment_no) !== String(commentNo);
                });
              }
              _context6.next = 5;
              break;
            case 4:
              _context6.prev = 4;
              _t7 = _context6["catch"](2);
              console.error('Failed to delete comment:', _t7);
              sweetalert2__WEBPACK_IMPORTED_MODULE_7___default().fire({
                icon: 'error',
                title: 'Delete Failed',
                text: (_t7 === null || _t7 === void 0 ? void 0 : (_err$response4 = _t7.response) === null || _err$response4 === void 0 ? void 0 : (_err$response4$data = _err$response4.data) === null || _err$response4$data === void 0 ? void 0 : _err$response4$data.message) || 'Failed to delete comment.',
                confirmButtonColor: '#3085d6'
              });
            case 5:
            case "end":
              return _context6.stop();
          }
        }, _callee6, null, [[2, 4]]);
      }))();
    },
    likePost(postNo) {
      var _this8 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee7() {
        var post, _t8;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context7) {
          while (1) switch (_context7.prev = _context7.next) {
            case 0:
              post = _this8.posts.find(function (p) {
                return p.post_no === postNo;
              });
              if (post) {
                _context7.next = 1;
                break;
              }
              return _context7.abrupt("return");
            case 1:
              post.isLiked = !post.isLiked;
              post.post_likes += post.isLiked ? 1 : -1;
              _context7.prev = 2;
              _context7.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"].put('/profileCard/postLikes?postNo=' + postNo);
            case 3:
              _context7.next = 5;
              break;
            case 4:
              _context7.prev = 4;
              _t8 = _context7["catch"](2);
              console.error('Failed to like post:', _t8);
              // revert on error
              post.isLiked = !post.isLiked;
              post.post_likes += post.isLiked ? 1 : -1;
            case 5:
            case "end":
              return _context7.stop();
          }
        }, _callee7, null, [[2, 4]]);
      }))();
    },
    // ── Post-level: toggle the floating emoji reaction bar ─────────────
    togglePostReactionBar(postNo, show) {
      var _this9 = this;
      // Use a small delay on hide to allow the user to move into the bar
      if (show) {
        clearTimeout(this._postBarTimer);
        this.activeReactionBars[postNo] = true;
      } else {
        this._postBarTimer = setTimeout(function () {
          _this9.activeReactionBars[postNo] = false;
        }, 350);
      }
    },
    keepPostReactionBar(postNo, keep) {
      if (keep) {
        clearTimeout(this._postBarTimer);
        this.activeReactionBars[postNo] = true;
      } else {
        this.activeReactionBars[postNo] = false;
      }
    },
    // ── Comment-level: toggle the floating emoji reaction bar ───────────
    toggleCommentReactionBar(commentNo, show) {
      var _this0 = this;
      if (show) {
        clearTimeout(this._commentBarTimer);
        this.activeCommentReactionBars[commentNo] = true;
      } else {
        this._commentBarTimer = setTimeout(function () {
          _this0.activeCommentReactionBars[commentNo] = false;
        }, 350);
      }
    },
    keepCommentReactionBar(commentNo, keep) {
      if (keep) {
        clearTimeout(this._commentBarTimer);
        this.activeCommentReactionBars[commentNo] = true;
      } else {
        this.activeCommentReactionBars[commentNo] = false;
      }
    },
    // ── Touch support: long-press reveals the reaction bar on mobile,
    // since hover/mouseenter never fires on touch devices ────────────
    startPostLongPress(postNo) {
      var _this1 = this;
      this._postLongPressFired = false;
      clearTimeout(this._postLongPressTimer);
      this._postLongPressTimer = setTimeout(function () {
        _this1._postLongPressFired = true;
        _this1.activeReactionBars[postNo] = true;
      }, 450);
    },
    cancelPostLongPress() {
      clearTimeout(this._postLongPressTimer);
    },
    // Plain tap likes the post; a long-press that already opened the
    // bar suppresses the tap so it doesn't also toggle the like.
    onPostLikeClick(postNo, reactionType) {
      if (this._postLongPressFired) {
        this._postLongPressFired = false;
        return;
      }
      this.reactToPost(postNo, reactionType);
    },
    startCommentLongPress(commentNo) {
      var _this10 = this;
      this._commentLongPressFired = false;
      clearTimeout(this._commentLongPressTimer);
      this._commentLongPressTimer = setTimeout(function () {
        _this10._commentLongPressFired = true;
        _this10.activeCommentReactionBars[commentNo] = true;
      }, 450);
    },
    cancelCommentLongPress() {
      clearTimeout(this._commentLongPressTimer);
    },
    onCommentLikeClick(postNo, commentNo, reactionType) {
      if (this._commentLongPressFired) {
        this._commentLongPressFired = false;
        return;
      }
      this.reactToComment(postNo, commentNo, reactionType);
    },
    // ── Comment emoji picker for text input ─────────────────────────────
    toggleCommentEmoji(postNo) {
      this.commentEmojiOpen[postNo] = !this.commentEmojiOpen[postNo];
    },
    insertEmojiIntoComment(postNo, emoji) {
      var current = this.commentInputs[postNo] || '';
      this.commentInputs[postNo] = current + emoji;
      this.commentEmojiOpen[postNo] = false;
    },
    // ── React to a comment with an emoji ───────────────────────────────
    reactToComment(postNo, commentNo, reactionType) {
      var _this11 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee8() {
        var post, comment, prevReactions, prevTotal, isSame, wasReaction, _res$data, _res$data$message, _res$data$message$cou, formData, res, _serverCounts$totalRe, serverCounts, _t9;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context8) {
          while (1) switch (_context8.prev = _context8.next) {
            case 0:
              post = _this11.posts.find(function (p) {
                return p.post_no === postNo;
              });
              if (post) {
                _context8.next = 1;
                break;
              }
              return _context8.abrupt("return");
            case 1:
              comment = post.comments.find(function (c) {
                return c.comment_no === commentNo;
              });
              if (comment) {
                _context8.next = 2;
                break;
              }
              return _context8.abrupt("return");
            case 2:
              // Optimistic UI — update local state immediately
              prevReactions = _objectSpread({}, comment.reactions);
              prevTotal = comment.totalReactions;
              isSame = comment.userReaction === reactionType;
              wasReaction = comment.userReaction;
              if (isSame) {
                // Toggle off
                comment.reactions[reactionType] = Math.max(0, (comment.reactions[reactionType] || 1) - 1);
                comment.totalReactions = Math.max(0, comment.totalReactions - 1);
                comment.userReaction = null;
              } else {
                // Remove old reaction from counts if switching
                if (wasReaction && comment.reactions[wasReaction]) {
                  comment.reactions[wasReaction] = Math.max(0, comment.reactions[wasReaction] - 1);
                  comment.totalReactions = Math.max(0, comment.totalReactions - 1);
                }
                comment.reactions[reactionType] = (comment.reactions[reactionType] || 0) + 1;
                comment.totalReactions += 1;
                comment.userReaction = reactionType;
              }

              // Hide the reaction bar after clicking
              _this11.activeCommentReactionBars[commentNo] = false;
              _context8.prev = 3;
              formData = new FormData();
              formData.append('comment_no', commentNo);
              formData.append('reaction', reactionType);
              _context8.next = 4;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"].post('/api/reactions/add', formData, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              });
            case 4:
              res = _context8.sent;
              // Sync server counts if returned
              if (res !== null && res !== void 0 && (_res$data = res.data) !== null && _res$data !== void 0 && (_res$data$message = _res$data.message) !== null && _res$data$message !== void 0 && (_res$data$message$cou = _res$data$message.counts) !== null && _res$data$message$cou !== void 0 && _res$data$message$cou.counts) {
                serverCounts = res.data.message.counts.counts;
                comment.reactions = _objectSpread({}, serverCounts);
                comment.totalReactions = (_serverCounts$totalRe = serverCounts === null || serverCounts === void 0 ? void 0 : serverCounts.totalReactions) !== null && _serverCounts$totalRe !== void 0 ? _serverCounts$totalRe : comment.totalReactions;
              }
              _context8.next = 6;
              break;
            case 5:
              _context8.prev = 5;
              _t9 = _context8["catch"](3);
              // Revert optimistic update on failure
              comment.reactions = prevReactions;
              comment.totalReactions = prevTotal;
              comment.userReaction = wasReaction;
              console.error('Comment reaction failed:', _t9);
            case 6:
            case "end":
              return _context8.stop();
          }
        }, _callee8, null, [[3, 5]]);
      }))();
    },
    // ── Get top 3 comment reactions for display ─────────────────────────
    getTopCommentReactions(reactions) {
      if (!reactions || typeof reactions !== 'object') return [];
      var map = this.commentEmojiMap;
      return Object.entries(reactions).filter(function (_ref) {
        var _ref2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__["default"])(_ref, 2),
          k = _ref2[0],
          v = _ref2[1];
        return !['comment_no', 'total', 'totalReactions'].includes(k) && Number(v) > 0;
      }).sort(function (_ref3, _ref4) {
        var _ref5 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__["default"])(_ref3, 2),
          a = _ref5[1];
        var _ref6 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__["default"])(_ref4, 2),
          b = _ref6[1];
        return Number(b) - Number(a);
      }).slice(0, 3).map(function (_ref7) {
        var _map$label;
        var _ref8 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__["default"])(_ref7, 2),
          label = _ref8[0],
          count = _ref8[1];
        return {
          emoji: (_map$label = map[label]) !== null && _map$label !== void 0 ? _map$label : '👍',
          count: Number(count)
        };
      });
    },
    // ── React to a post with an emoji (post-level reactions) ────────────
    reactToPost(postNo, reactionType) {
      var _this12 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee9() {
        var post, previousReaction, formData, _t0;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context9) {
          while (1) switch (_context9.prev = _context9.next) {
            case 0:
              post = _this12.posts.find(function (p) {
                return String(p.post_no) === String(postNo);
              });
              if (post) {
                _context9.next = 1;
                break;
              }
              return _context9.abrupt("return");
            case 1:
              previousReaction = post.user_reaction;
              post.user_reaction = post.user_reaction === reactionType ? null : reactionType;

              // Optimistic like counter bump
              if (previousReaction === null) post.post_likes += 1;else if (post.user_reaction === null) post.post_likes = Math.max(0, post.post_likes - 1);

              // Close the reaction bar
              _this12.activeReactionBars[postNo] = false;
              _context9.prev = 2;
              formData = new FormData();
              formData.append('post_no', postNo);
              formData.append('reaction', reactionType);
              _context9.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"].put('/profileCard/postLikes?postNo=' + postNo);
            case 3:
              _context9.next = 5;
              break;
            case 4:
              _context9.prev = 4;
              _t0 = _context9["catch"](2);
              console.error('Failed to record post reaction:', _t0);
              post.user_reaction = previousReaction;
              // Revert like counter
              if (previousReaction === null) post.post_likes = Math.max(0, post.post_likes - 1);else if (post.user_reaction === null) post.post_likes += 1;
            case 5:
            case "end":
              return _context9.stop();
          }
        }, _callee9, null, [[2, 4]]);
      }))();
    },
    votePoll(postNo, optionId) {
      var _this13 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().mark(function _callee0() {
        var post, snapshot, voted, already, total, _response$data3, _response$data4, formData, response, _response$data5, _t1;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_4___default().wrap(function (_context0) {
          while (1) switch (_context0.prev = _context0.next) {
            case 0:
              post = _this13.posts.find(function (p) {
                return String(p.post_no) === String(postNo);
              });
              if (!(!(post !== null && post !== void 0 && post.poll) || !Array.isArray(post.poll.options))) {
                _context0.next = 1;
                break;
              }
              return _context0.abrupt("return");
            case 1:
              // Snapshot for rollback
              snapshot = JSON.parse(JSON.stringify(post.poll)); // ── Optimistic update ──────────────────────────────────────────
              voted = Array.isArray(post.poll.user_voted_option_id) ? (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(post.poll.user_voted_option_id) : [];
              already = voted.some(function (v) {
                return String(v) === String(optionId);
              });
              post.poll.options = post.poll.options.map(function (opt) {
                if (String(opt.option_id) === String(optionId)) {
                  var count = Number(opt.vote_count || 0) + (already ? -1 : 1);
                  return _objectSpread(_objectSpread({}, opt), {}, {
                    vote_count: Math.max(0, count)
                  });
                }
                return _objectSpread({}, opt);
              });
              post.poll.user_voted_option_id = already ? voted.filter(function (v) {
                return String(v) !== String(optionId);
              }) : [].concat((0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(voted), [optionId]);
              total = post.poll.options.reduce(function (sum, o) {
                return sum + Number(o.vote_count || 0);
              }, 0);
              post.poll.total_votes = total;
              post.poll.options = post.poll.options.map(function (o) {
                return _objectSpread(_objectSpread({}, o), {}, {
                  percentage: total > 0 ? Math.round(Number(o.vote_count || 0) / total * 100) : 0
                });
              });

              // ── Persist ────────────────────────────────────────────────────
              _context0.prev = 2;
              formData = new FormData();
              formData.append('post_no', postNo);
              formData.append('option_id', optionId);
              _context0.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_10__["default"].post('/api/poll/vote', formData, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              });
            case 3:
              response = _context0.sent;
              if (!(response !== null && response !== void 0 && (_response$data3 = response.data) !== null && _response$data3 !== void 0 && _response$data3.poll)) {
                _context0.next = 4;
                break;
              }
              post.poll = response.data.poll;
              _context0.next = 5;
              break;
            case 4:
              if (!((response === null || response === void 0 ? void 0 : (_response$data4 = response.data) === null || _response$data4 === void 0 ? void 0 : _response$data4.status) !== 'success')) {
                _context0.next = 5;
                break;
              }
              throw new Error((response === null || response === void 0 ? void 0 : (_response$data5 = response.data) === null || _response$data5 === void 0 ? void 0 : _response$data5.message) || 'Vote was not recorded');
            case 5:
              _context0.next = 7;
              break;
            case 6:
              _context0.prev = 6;
              _t1 = _context0["catch"](2);
              console.error('Failed to vote on poll:', _t1);
              post.poll = snapshot; // rollback
            case 7:
            case "end":
              return _context0.stop();
          }
        }, _callee0, null, [[2, 6]]);
      }))();
    },
    initEventListeners() {
      var _this14 = this;
      window.addEventListener('post-created', function (event) {
        var newPostData = event === null || event === void 0 ? void 0 : event.detail;
        if (newPostData && typeof newPostData === 'object') {
          var normalized = _this14.normalizePost(newPostData);
          if (!_this14.posts.some(function (p) {
            return String(p.post_no) === String(normalized.post_no);
          })) {
            _this14.posts.unshift(normalized);
          }
        } else {
          _this14.fetchPosts();
        }
      });

      // Dispatched by allEvents.js after a successful post edit (the acting
      // user's own tab — other tabs get it via the update-post Pusher bind).
      window.addEventListener('post-updated', function (event) {
        var _ref9 = (event === null || event === void 0 ? void 0 : event.detail) || {},
          postNo = _ref9.postNo,
          postMessage = _ref9.postMessage;
        var post = _this14.posts.find(function (p) {
          return String(p.post_no) === String(postNo);
        });
        if (post) post.postMessage = postMessage;
      });
    },
    openLightbox(images, index) {
      this.lightboxImages = images;
      this.lightboxIndex = index;
      this.lightboxOpen = true;
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    },
    closeLightbox() {
      this.lightboxOpen = false;
      this.lightboxImages = [];
      this.lightboxIndex = 0;
      document.body.style.overflow = ''; // Restore background scrolling
    },
    nextLightboxImage() {
      if (this.lightboxImages.length > 0) {
        this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxImages.length;
      }
    },
    prevLightboxImage() {
      if (this.lightboxImages.length > 0) {
        this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxImages.length) % this.lightboxImages.length;
      }
    },
    initPusher() {
      var _this15 = this;
      try {
        var key = "0dc3f141e1632b5aa5db";
        var cluster = "eu";
        var famCode = (this.currentFamCode || '').replace(/[^A-Za-z0-9_-]/g, '');
        if (!key || !cluster || !famCode) return;

        // One private, per-family channel. The server (Pusher::authoriseChannel)
        // only signs the subscription if this session belongs to <famCode>,
        // so another family cannot read this feed even with the public key.
        this.pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_5___default())(key, {
          cluster,
          forceTLS: true,
          channelAuthorization: {
            endpoint: '/pusher/auth',
            headersProvider: function headersProvider() {
              return {
                'X-CSRF-Token': (0,_global__WEBPACK_IMPORTED_MODULE_9__.getCsrfToken)(),
                'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_9__.getCsrfToken)(),
                'X-Requested-With': 'XMLHttpRequest'
              };
            }
          }
        });
        var channel = this.pusher.subscribe("private-family-".concat(famCode));
        channel.bind('new-post', function (data) {
          if (Array.isArray(data)) {
            data.forEach(function (item) {
              if (!_this15.posts.some(function (p) {
                return String(p.post_no) === String(item === null || item === void 0 ? void 0 : item.post_no);
              })) {
                _this15.posts.unshift(_this15.normalizePost(item));
              }
            });
          }
        });
        channel.bind('new-comment', function (data) {
          if (Array.isArray(data)) {
            data.forEach(function (item) {
              var post = _this15.posts.find(function (p) {
                return String(p.post_no) === String(item === null || item === void 0 ? void 0 : item.post_no);
              });
              if (post && !post.comments.some(function (c) {
                return String(c.comment_no) === String(item === null || item === void 0 ? void 0 : item.comment_no);
              })) {
                post.comments.push(_this15.normalizeComment(item));
              }
            });
          }
        });

        // Edit/delete broadcasts (PostMessage::deletePost/updatePost/
        // updateComment/deleteComment) send a single flat object, not an
        // array like new-post/new-comment do.
        channel.bind('delete-post', function (data) {
          _this15.posts = _this15.posts.filter(function (p) {
            return String(p.post_no) !== String(data === null || data === void 0 ? void 0 : data.postNo);
          });
        });
        channel.bind('update-post', function (data) {
          var _data$postMessage;
          var post = _this15.posts.find(function (p) {
            return String(p.post_no) === String(data === null || data === void 0 ? void 0 : data.postNo);
          });
          if (post) post.postMessage = (_data$postMessage = data === null || data === void 0 ? void 0 : data.postMessage) !== null && _data$postMessage !== void 0 ? _data$postMessage : post.postMessage;
        });
        channel.bind('delete-comment', function (data) {
          var post = _this15.posts.find(function (p) {
            return String(p.post_no) === String(data === null || data === void 0 ? void 0 : data.postNo);
          });
          if (post) {
            post.comments = post.comments.filter(function (c) {
              return String(c.comment_no) !== String(data === null || data === void 0 ? void 0 : data.commentNo);
            });
          }
        });
        channel.bind('update-comment', function (data) {
          var _data$comment;
          var post = _this15.posts.find(function (p) {
            return String(p.post_no) === String(data === null || data === void 0 ? void 0 : data.postNo);
          });
          var comment = post === null || post === void 0 ? void 0 : post.comments.find(function (c) {
            return String(c.comment_no) === String(data === null || data === void 0 ? void 0 : data.commentNo);
          });
          if (comment) comment.comment = (_data$comment = data === null || data === void 0 ? void 0 : data.comment) !== null && _data$comment !== void 0 ? _data$comment : comment.comment;
        });
        channel.bind('like-event', function (data) {
          if (Array.isArray(data)) {
            data.forEach(function (item) {
              var post = _this15.posts.find(function (p) {
                return String(p.post_no) === String(item === null || item === void 0 ? void 0 : item.post_no);
              });
              if (post && (item === null || item === void 0 ? void 0 : item.likeCounter) !== undefined) {
                post.post_likes = parseInt(item.likeCounter, 10);
              }
            });
          }
        });
      } catch (e) {
        console.warn('Pusher initialization skipped:', e);
      }
    }
  };
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/sidebarComponents.js":
/*!************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/sidebarComponents.js ***!
  \************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "profileSidebar": function() { return /* binding */ profileSidebar; },
/* harmony export */   "upcomingEvents": function() { return /* binding */ upcomingEvents; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");






var csrfOptions = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};
function profileSidebar(initialData) {
  return {
    userData: initialData || {},
    friendRequests: [],
    isLoadingRequests: true,
    requestError: '',
    init() {
      var _this$userData;
      // Defensive typing and fallback check
      if (!((_this$userData = this.userData) !== null && _this$userData !== void 0 && _this$userData.id)) {
        console.warn('profileSidebar: Missing userData.id');
        this.isLoadingRequests = false;
        return;
      }

      // Secure local storage setup
      try {
        localStorage.setItem('requesterFamCode', this.userData.famCode || '');
        localStorage.setItem('requesterId', this.userData.id || '');
        localStorage.setItem('yourName', "".concat(this.userData.firstName || '', " ").concat(this.userData.lastName || '').trim());
      } catch (e) {
        console.error('Failed to set localStorage profile credentials:', e);
      }
      this.fetchRequests();
    },
    fetchRequests() {
      var _this = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
        var _response$data, response, requests, _t;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _this.isLoadingRequests = true;
              _this.requestError = '';
              _context.prev = 1;
              _context.next = 2;
              return axios__WEBPACK_IMPORTED_MODULE_5__["default"].get("/getFriendRequestById?id=".concat(encodeURIComponent(_this.userData.id)), {
                timeout: 8000 // Strict timeout gate
              });
            case 2:
              response = _context.sent;
              requests = response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message;
              if (Array.isArray(requests)) {
                _this.friendRequests = requests.map(function (req) {
                  var _req$id, _ref, _req$firstName, _ref2, _req$lastName, _ref3, _req$img, _ref4, _req$famCode;
                  return {
                    id: (_req$id = req === null || req === void 0 ? void 0 : req.id) !== null && _req$id !== void 0 ? _req$id : req === null || req === void 0 ? void 0 : req.requesterId,
                    firstName: (_ref = (_req$firstName = req === null || req === void 0 ? void 0 : req.firstName) !== null && _req$firstName !== void 0 ? _req$firstName : req === null || req === void 0 ? void 0 : req.requesterFirstName) !== null && _ref !== void 0 ? _ref : 'Unknown',
                    lastName: (_ref2 = (_req$lastName = req === null || req === void 0 ? void 0 : req.lastName) !== null && _req$lastName !== void 0 ? _req$lastName : req === null || req === void 0 ? void 0 : req.requesterLastName) !== null && _ref2 !== void 0 ? _ref2 : '',
                    img: (_ref3 = (_req$img = req === null || req === void 0 ? void 0 : req.img) !== null && _req$img !== void 0 ? _req$img : req === null || req === void 0 ? void 0 : req.requesterProfileImg) !== null && _ref3 !== void 0 ? _ref3 : 'avatarM.png',
                    famCode: (_ref4 = (_req$famCode = req === null || req === void 0 ? void 0 : req.famCode) !== null && _req$famCode !== void 0 ? _req$famCode : req === null || req === void 0 ? void 0 : req.requesterFamCode) !== null && _ref4 !== void 0 ? _ref4 : ''
                  };
                });
              } else {
                _this.friendRequests = [];
              }
              _context.next = 4;
              break;
            case 3:
              _context.prev = 3;
              _t = _context["catch"](1);
              console.error('Failed to fetch friend requests:', _t);
              _this.requestError = 'Could not load requests.';
            case 4:
              _context.prev = 4;
              _this.isLoadingRequests = false;
              return _context.finish(4);
            case 5:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[1, 3, 4, 5]]);
      }))();
    },
    getAcceptUrl(req) {
      var _this$userData2;
      var requestId = encodeURIComponent((req === null || req === void 0 ? void 0 : req.id) || '');
      var approverId = encodeURIComponent(((_this$userData2 = this.userData) === null || _this$userData2 === void 0 ? void 0 : _this$userData2.id) || '');
      var requestCode = encodeURIComponent((req === null || req === void 0 ? void 0 : req.famCode) || '');
      return "/member/request/".concat(requestId, "/").concat(approverId, "/50/").concat(requestCode, "/pp");
    },
    getDeclineUrl(req) {
      var _this$userData3;
      var requestId = encodeURIComponent((req === null || req === void 0 ? void 0 : req.id) || '');
      var approverId = encodeURIComponent(((_this$userData3 = this.userData) === null || _this$userData3 === void 0 ? void 0 : _this$userData3.id) || '');
      return "/member/request/".concat(requestId, "/").concat(approverId, "/10");
    }
  };
}
function upcomingEvents(initialEvents) {
  return {
    events: Array.isArray(initialEvents) ? initialEvents : [],
    currentUserId: localStorage.getItem('requesterId') || '',
    pusher: null,
    init() {
      var _this2 = this;
      // Seeded once from the server on page load — without this listener, an
      // event created via the modal never appeared until a full page reload.
      window.addEventListener('event-created', function (e) {
        var newEvent = e === null || e === void 0 ? void 0 : e.detail;
        if (newEvent && !_this2.events.some(function (ev) {
          return String(ev.no) === String(newEvent.no);
        })) {
          _this2.events.unshift(newEvent);
        }
      });

      // Dispatched by createEvent.js after a successful edit (the acting
      // user's own tab — other tabs get it via the update-event Pusher bind).
      window.addEventListener('event-updated', function (e) {
        _this2.applyEventUpdate(e === null || e === void 0 ? void 0 : e.detail);
      });
      this.initPusher();
    },
    isOwnEvent(event) {
      return String(event === null || event === void 0 ? void 0 : event.id) === String(this.currentUserId);
    },
    // Opens the existing "Create Event" modal in edit mode: prefills every
    // field, stamps a hidden editEventNo the modal's own submit handler
    // (createEvent.js) checks to decide between POST (create) and PUT (update).
    editEvent(event) {
      var _window$bootstrap, _window$bootstrap$Mod;
      var editEventNo = document.getElementById('editEventNo');
      var notice = document.getElementById('editEventNotice');
      var title = document.getElementById('createEventModalLabel');
      var submitBtn = document.getElementById('submitEventModal');
      if (!editEventNo) return;
      editEventNo.value = event.no;
      var setVal = function setVal(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val !== null && val !== void 0 ? val : '';
      };
      setVal('eventName', event.eventName);
      setVal('eventDate', event.eventDateRaw);
      setVal('eventType', event.eventType);
      setVal('eventDescription', event.eventDescription);
      setVal('eventFrequency', event.eventFrequency);
      if (notice) notice.classList.remove('d-none');
      if (title) title.textContent = 'Edit Event';
      if (submitBtn) submitBtn.textContent = 'Save Changes';
      var modalEl = document.getElementById('createEventModal');
      var instance = (_window$bootstrap = window.bootstrap) === null || _window$bootstrap === void 0 ? void 0 : (_window$bootstrap$Mod = _window$bootstrap.Modal) === null || _window$bootstrap$Mod === void 0 ? void 0 : _window$bootstrap$Mod.getOrCreateInstance(modalEl);
      instance === null || instance === void 0 ? void 0 : instance.show();
    },
    deleteEvent(eventNo) {
      var _this3 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2() {
        var result, _err$response, _err$response$data, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _context2.next = 1;
              return sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
                title: 'Delete this event?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
              });
            case 1:
              result = _context2.sent;
              if (result.isConfirmed) {
                _context2.next = 2;
                break;
              }
              return _context2.abrupt("return");
            case 2:
              _context2.prev = 2;
              _context2.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_5__["default"]["delete"]("/member/profilePage/event/".concat(eventNo), csrfOptions);
            case 3:
              _this3.events = _this3.events.filter(function (ev) {
                return String(ev.no) !== String(eventNo);
              });
              _context2.next = 5;
              break;
            case 4:
              _context2.prev = 4;
              _t2 = _context2["catch"](2);
              console.error('Failed to delete event:', _t2);
              sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
                icon: 'error',
                title: 'Delete Failed',
                text: (_t2 === null || _t2 === void 0 ? void 0 : (_err$response = _t2.response) === null || _err$response === void 0 ? void 0 : (_err$response$data = _err$response.data) === null || _err$response$data === void 0 ? void 0 : _err$response$data.message) || 'Failed to delete event.',
                confirmButtonColor: '#3085d6'
              });
            case 5:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[2, 4]]);
      }))();
    },
    applyEventUpdate(updated) {
      if (!(updated !== null && updated !== void 0 && updated.no)) return;
      var event = this.events.find(function (ev) {
        return String(ev.no) === String(updated.no);
      });
      if (event) Object.assign(event, updated);
    },
    initPusher() {
      var _this4 = this;
      try {
        var _this$userData4;
        var key = "0dc3f141e1632b5aa5db";
        var cluster = "eu";
        var famCode = (((_this$userData4 = this.userData) === null || _this$userData4 === void 0 ? void 0 : _this$userData4.famCode) || '').replace(/[^A-Za-z0-9_-]/g, '');
        if (!key || !cluster || !famCode) return;

        // Per-family private channel — server-authorised in Pusher::authoriseChannel.
        this.pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_2___default())(key, {
          cluster,
          forceTLS: true,
          channelAuthorization: {
            endpoint: '/pusher/auth',
            headersProvider: function headersProvider() {
              return {
                'X-CSRF-Token': (0,_global__WEBPACK_IMPORTED_MODULE_4__.getCsrfToken)(),
                'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_4__.getCsrfToken)(),
                'X-Requested-With': 'XMLHttpRequest'
              };
            }
          }
        });
        var channel = this.pusher.subscribe("private-family-".concat(famCode));
        channel.bind('update-event', function (data) {
          return _this4.applyEventUpdate(data);
        });
        channel.bind('delete-event', function (data) {
          _this4.events = _this4.events.filter(function (ev) {
            return String(ev.no) !== String(data === null || data === void 0 ? void 0 : data.no);
          });
        });
      } catch (e) {
        console.warn('Pusher initialization skipped:', e);
      }
    }
  };
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/videoParser.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/videoParser.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "cleanPostMessage": function() { return /* binding */ cleanPostMessage; },
/* harmony export */   "extractVideoFromText": function() { return /* binding */ extractVideoFromText; },
/* harmony export */   "parseVideoUrl": function() { return /* binding */ parseVideoUrl; }
/* harmony export */ });
/**
 * Video URL Parser & Embed Helper for Social Posts
 * Supports:
 * - YouTube (Standard, Shortened youtu.be, Shorts, Live, Unlisted, with or without '=' in query)
 * - Vimeo
 * - Cloudflare Stream
 * - Direct MP4 / WebM video streams
 */



function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function parseVideoUrl(url) {
  if (!url || typeof url !== 'string') return null;
  var trimmed = url.trim();

  // 1. YouTube Matcher
  // Matches: youtube.com/watch?v=ID, youtube.com/watch?vID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID, youtube.com/live/ID
  var ytRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
  var ytMatch = trimmed.match(ytRegex);
  if (ytMatch && ytMatch[1]) {
    return {
      type: 'youtube',
      videoId: ytMatch[1],
      embedUrl: "https://www.youtube-nocookie.com/embed/".concat(ytMatch[1], "?rel=0&modestbranding=1&autoplay=0"),
      originalUrl: trimmed
    };
  }

  // 2. Vimeo Matcher
  // Matches: vimeo.com/ID, player.vimeo.com/video/ID
  var vimeoRegex = /(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
  var vimeoMatch = trimmed.match(vimeoRegex);
  if (vimeoMatch && (vimeoMatch[3] || vimeoMatch[2] || vimeoMatch[1])) {
    var vimeoId = vimeoMatch[3] || vimeoMatch[2] || vimeoMatch[1];
    return {
      type: 'vimeo',
      videoId: vimeoId,
      embedUrl: "https://player.vimeo.com/video/".concat(vimeoId, "?dnt=1&title=0&byline=0&portrait=0"),
      originalUrl: trimmed
    };
  }

  // 3. Cloudflare Stream Matcher
  // Matches: iframe.videodelivery.net/UID, watch.cloudflarestream.com/UID, customer-*.cloudflarestream.com/UID
  var cfStreamRegex = /(?:https?:\/\/)?(?:iframe\.videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)\/([a-zA-Z0-9]{32})/;
  var cfMatch = trimmed.match(cfStreamRegex);
  if (cfMatch && cfMatch[1]) {
    return {
      type: 'cloudflare',
      videoId: cfMatch[1],
      embedUrl: "https://iframe.videodelivery.net/".concat(cfMatch[1]),
      originalUrl: trimmed
    };
  }

  // 4. Direct HTML5 Video (.mp4, .webm, .ogg)
  var directVideoRegex = /^https?:\/\/.+\.(mp4|webm|ogg)(\?.*)?$/i;
  if (directVideoRegex.test(trimmed)) {
    return {
      type: 'direct',
      videoId: null,
      embedUrl: trimmed,
      originalUrl: trimmed
    };
  }
  return null;
}

/**
 * Extract video URL from post message text if present
 */
function extractVideoFromText(text) {
  if (!text || typeof text !== 'string') return null;
  var urlRegex = /(https?:\/\/[^\s]+)/g;
  var matches = text.match(urlRegex);
  if (!matches) return null;
  var _iterator = _createForOfIteratorHelper(matches),
    _step;
  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var url = _step.value;
      var parsed = parseVideoUrl(url);
      if (parsed) return parsed;
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
  return null;
}

/**
 * Strip raw video URL from post text if a player is rendered,
 * leaving only user-written caption/comment text.
 */
function cleanPostMessage(text, video) {
  if (!text || typeof text !== 'string') return '';
  if (!video || !video.originalUrl) return text;
  var cleaned = text.replace(video.originalUrl, '').trim();
  return cleaned;
}

/***/ }),

/***/ "./resources/asset/js/components/pwa/haptics.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/pwa/haptics.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "HAPTIC_PATTERNS": function() { return /* binding */ HAPTIC_PATTERNS; },
/* harmony export */   "triggerHaptic": function() { return /* binding */ triggerHaptic; }
/* harmony export */ });
/**
 * Native Web Haptics Engine
 * Provides subtle tactile feedback for App-Store parity via Web Vibration API
 */



var HAPTIC_PATTERNS = {
  selection: 10,
  // Tab switches, subtle toggles
  impact: 15,
  // Reactions, like clicks, button presses
  success: [10, 40, 15],
  // Saved post, completed action
  warning: [20, 60, 20] // Dismissals, alerts
};

/**
 * Trigger subtle haptic vibration safely
 * Gracefully no-ops on devices without vibration support (e.g. iOS Safari)
 * 
 * @param {'selection' | 'impact' | 'success' | 'warning'} type 
 */
function triggerHaptic() {
  var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'selection';
  if (typeof navigator !== 'undefined' && 'vibrate' in navigator && typeof navigator.vibrate === 'function') {
    try {
      var pattern = HAPTIC_PATTERNS[type] || 10;
      navigator.vibrate(pattern);
    } catch (_unused) {
      // Ignore vibration errors silently
    }
  }
}
/* harmony default export */ __webpack_exports__["default"] = (triggerHaptic);

/***/ }),

/***/ "./resources/asset/js/components/pwa/offlineSync.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/pwa/offlineSync.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_4__);
/**
 * FamilyPlatform Background Sync & Offline Queue Engine
 * - IndexedDB persistent store for offline post submissions & drafts
 * - Background Sync API registration with fallback to window 'online' event
 * - Automatic exponential backoff & retry mechanism
 * - User-facing success feedback upon queue drain
 */






function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }


var DB_NAME = 'familyplatform_offline_db';
var DB_VERSION = 2;
var STORE_NAME = 'offline_queue';
var FEED_STORE_NAME = 'offline_feed';
var OfflineSyncManager = /*#__PURE__*/function () {
  function OfflineSyncManager() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, OfflineSyncManager);
    this.db = null;
    this.isDraining = false;
    this.initDB();
    this.setupListeners();
  }

  /**
   * 1. Initialize IndexedDB
   */
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(OfflineSyncManager, [{
    key: "initDB",
    value: function initDB() {
      var _this = this;
      return new Promise(function (resolve, reject) {
        var request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = function (e) {
          var db = e.target.result;
          if (!db.objectStoreNames.contains(STORE_NAME)) {
            db.createObjectStore(STORE_NAME, {
              keyPath: 'id',
              autoIncrement: true
            });
          }
          if (!db.objectStoreNames.contains(FEED_STORE_NAME)) {
            db.createObjectStore(FEED_STORE_NAME, {
              keyPath: 'key'
            });
          }
        };
        request.onsuccess = function (e) {
          _this.db = e.target.result;
          resolve(_this.db);
        };
        request.onerror = function (e) {
          console.warn('[OfflineSync] IndexedDB failed to open:', e);
          reject(e);
        };
      });
    }

    /**
     * 2. Setup Reconnection & SW Message Listeners
     */
  }, {
    key: "setupListeners",
    value: function setupListeners() {
      var _this2 = this;
      // When browser returns online, trigger queue drain
      window.addEventListener('online', function () {
        console.log('[OfflineSync] Online event detected. Draining queue...');
        _this2.drainQueue();
      });

      // Listen for Service Worker background sync notification
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', function (event) {
          var _event$data;
          if (((_event$data = event.data) === null || _event$data === void 0 ? void 0 : _event$data.type) === 'DRAIN_OFFLINE_QUEUE') {
            console.log('[OfflineSync] SW message: DRAIN_OFFLINE_QUEUE received');
            _this2.drainQueue();
          }
        });
      }
    }

    /**
     * 3. Queue an action when offline
     *
     * @param {string} url Endpoint URL
     * @param {Object|FormData} payload Data payload
     * @param {string} actionDescription User-friendly text (e.g. "Family Post")
     */
  }, {
    key: "enqueue",
    value: (function () {
      var _enqueue = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee2(url, payload) {
        var _this3 = this;
        var actionDescription,
          serializedData,
          item,
          _args2 = arguments;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              actionDescription = _args2.length > 2 && _args2[2] !== undefined ? _args2[2] : 'Action';
              if (this.db) {
                _context2.next = 1;
                break;
              }
              _context2.next = 1;
              return this.initDB();
            case 1:
              // Convert FormData to plain object for IndexedDB serialization if needed
              serializedData = payload;
              if (payload instanceof FormData) {
                serializedData = {};
                payload.forEach(function (value, key) {
                  serializedData[key] = value;
                });
              }
              item = {
                url,
                payload: serializedData,
                description: actionDescription,
                timestamp: Date.now(),
                retries: 0
              };
              return _context2.abrupt("return", new Promise(function (resolve, reject) {
                var tx = _this3.db.transaction(STORE_NAME, 'readwrite');
                var store = tx.objectStore(STORE_NAME);
                var req = store.add(item);
                req.onsuccess = /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
                  var reg, _t;
                  return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
                    while (1) switch (_context.prev = _context.next) {
                      case 0:
                        console.log('[OfflineSync] Item queued successfully:', item);

                        // Register Background Sync if supported
                        if (!('serviceWorker' in navigator && 'SyncManager' in window)) {
                          _context.next = 5;
                          break;
                        }
                        _context.prev = 1;
                        _context.next = 2;
                        return navigator.serviceWorker.ready;
                      case 2:
                        reg = _context.sent;
                        _context.next = 3;
                        return reg.sync.register('sync-family-posts');
                      case 3:
                        _context.next = 5;
                        break;
                      case 4:
                        _context.prev = 4;
                        _t = _context["catch"](1);
                        console.log('[OfflineSync] Sync registration fallback:', _t);
                      case 5:
                        // Show friendly user toast
                        sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
                          icon: 'info',
                          title: 'Saved to Offline Queue',
                          text: "You are currently offline. Your ".concat(actionDescription.toLowerCase(), " has been safely saved and will publish automatically when you reconnect."),
                          timer: 4000,
                          timerProgressBar: true,
                          confirmButtonColor: '#2563eb'
                        });
                        resolve(true);
                      case 6:
                      case "end":
                        return _context.stop();
                    }
                  }, _callee, null, [[1, 4]]);
                }));
                req.onerror = function (err) {
                  console.error('[OfflineSync] Error saving to queue:', err);
                  reject(err);
                };
              }));
            case 2:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this);
      }));
      function enqueue(_x, _x2) {
        return _enqueue.apply(this, arguments);
      }
      return enqueue;
    }()
    /**
     * 4. Drain and replay queued requests
     */
    )
  }, {
    key: "drainQueue",
    value: (function () {
      var _drainQueue = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee3() {
        var _this4 = this;
        var _document$querySelect, items, processedCount, csrfToken, _iterator, _step, _loop, _t3, _t4;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              if (!(this.isDraining || !navigator.onLine)) {
                _context4.next = 1;
                break;
              }
              return _context4.abrupt("return");
            case 1:
              this.isDraining = true;
              if (this.db) {
                _context4.next = 2;
                break;
              }
              _context4.next = 2;
              return this.initDB();
            case 2:
              _context4.prev = 2;
              _context4.next = 3;
              return this.getAllItems();
            case 3:
              items = _context4.sent;
              if (!(items.length === 0)) {
                _context4.next = 4;
                break;
              }
              this.isDraining = false;
              return _context4.abrupt("return");
            case 4:
              console.log("[OfflineSync] Processing ".concat(items.length, " queued action(s)..."));
              processedCount = 0;
              csrfToken = ((_document$querySelect = document.querySelector('meta[name="csrf-token"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.getAttribute('content')) || '';
              _iterator = _createForOfIteratorHelper(items);
              _context4.prev = 5;
              _loop = /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _loop() {
                var item, formData, key, controller, timeoutId, response, _t2;
                return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context3) {
                  while (1) switch (_context3.prev = _context3.next) {
                    case 0:
                      item = _step.value;
                      _context3.prev = 1;
                      formData = new FormData();
                      for (key in item.payload) {
                        formData.append(key, item.payload[key]);
                      }
                      if (csrfToken && !item.payload['token']) {
                        formData.append('token', csrfToken);
                      }

                      // AbortController timeout safeguard (David's Gate 3: 8s limit)
                      controller = new AbortController();
                      timeoutId = setTimeout(function () {
                        return controller.abort();
                      }, 8000);
                      _context3.next = 2;
                      return fetch(item.url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'X-CSRF-TOKEN': csrfToken
                        },
                        signal: controller.signal
                      });
                    case 2:
                      response = _context3.sent;
                      clearTimeout(timeoutId);
                      if (!(response.ok || response.status === 200 || response.status === 302)) {
                        _context3.next = 4;
                        break;
                      }
                      _context3.next = 3;
                      return _this4.deleteItem(item.id);
                    case 3:
                      processedCount++;
                      _context3.next = 7;
                      break;
                    case 4:
                      if (!(response.status >= 400 && response.status < 500)) {
                        _context3.next = 6;
                        break;
                      }
                      // Client error (validation / unauthorized) - discard to avoid infinite loop
                      console.warn("[OfflineSync] Server returned ".concat(response.status, " for item ").concat(item.id, ". Discarding."));
                      _context3.next = 5;
                      return _this4.deleteItem(item.id);
                    case 5:
                      _context3.next = 7;
                      break;
                    case 6:
                      _context3.next = 7;
                      return _this4.incrementRetries(item);
                    case 7:
                      _context3.next = 9;
                      break;
                    case 8:
                      _context3.prev = 8;
                      _t2 = _context3["catch"](1);
                      console.warn('[OfflineSync] Network error during drain:', _t2);
                      return _context3.abrupt("return", 1);
                    case 9:
                    case "end":
                      return _context3.stop();
                  }
                }, _loop, null, [[1, 8]]);
              });
              _iterator.s();
            case 6:
              if ((_step = _iterator.n()).done) {
                _context4.next = 9;
                break;
              }
              return _context4.delegateYield(_loop(), "t0", 7);
            case 7:
              if (!_context4.t0) {
                _context4.next = 8;
                break;
              }
              return _context4.abrupt("continue", 9);
            case 8:
              _context4.next = 6;
              break;
            case 9:
              _context4.next = 11;
              break;
            case 10:
              _context4.prev = 10;
              _t3 = _context4["catch"](5);
              _iterator.e(_t3);
            case 11:
              _context4.prev = 11;
              _iterator.f();
              return _context4.finish(11);
            case 12:
              if (processedCount > 0) {
                sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'success',
                  title: "Synced ".concat(processedCount, " offline update(s) successfully!"),
                  showConfirmButton: false,
                  timer: 3500,
                  timerProgressBar: true
                });

                // If on profile/feed page, refresh view to show new content
                if (window.location.pathname.includes('profilePage')) {
                  setTimeout(function () {
                    return window.location.reload();
                  }, 1500);
                }
              }
              _context4.next = 14;
              break;
            case 13:
              _context4.prev = 13;
              _t4 = _context4["catch"](2);
              console.error('[OfflineSync] Error during drainQueue:', _t4);
            case 14:
              _context4.prev = 14;
              this.isDraining = false;
              return _context4.finish(14);
            case 15:
            case "end":
              return _context4.stop();
          }
        }, _callee3, this, [[2, 13, 14, 15], [5, 10, 11, 12]]);
      }));
      function drainQueue() {
        return _drainQueue.apply(this, arguments);
      }
      return drainQueue;
    }())
  }, {
    key: "getAllItems",
    value: function getAllItems() {
      var _this5 = this;
      return new Promise(function (resolve) {
        var tx = _this5.db.transaction(STORE_NAME, 'readonly');
        var store = tx.objectStore(STORE_NAME);
        var req = store.getAll();
        req.onsuccess = function () {
          return resolve(req.result || []);
        };
        req.onerror = function () {
          return resolve([]);
        };
      });
    }
  }, {
    key: "deleteItem",
    value: function deleteItem(id) {
      var _this6 = this;
      return new Promise(function (resolve) {
        var tx = _this6.db.transaction(STORE_NAME, 'readwrite');
        var store = tx.objectStore(STORE_NAME);
        var req = store.delete(id);
        req.onsuccess = function () {
          return resolve(true);
        };
        req.onerror = function () {
          return resolve(false);
        };
      });
    }
  }, {
    key: "incrementRetries",
    value: function incrementRetries(item) {
      var _this7 = this;
      return new Promise(function (resolve) {
        var tx = _this7.db.transaction(STORE_NAME, 'readwrite');
        var store = tx.objectStore(STORE_NAME);
        item.retries = (item.retries || 0) + 1;
        if (item.retries > 5) {
          // Discard after 5 failed retries
          store.delete(item.id);
        } else {
          store.put(item);
        }
        tx.oncomplete = function () {
          return resolve(true);
        };
      });
    }

    /**
     * 5. Cache Feed Posts for Offline-First Viewing (Facebook Pattern)
     */
  }, {
    key: "cacheFeed",
    value: function cacheFeed(posts) {
      var _this8 = this;
      if (!Array.isArray(posts) || posts.length === 0) return Promise.resolve(false);
      return new Promise(function (resolve) {
        if (!_this8.db) {
          return resolve(false);
        }
        try {
          var tx = _this8.db.transaction(FEED_STORE_NAME, 'readwrite');
          var store = tx.objectStore(FEED_STORE_NAME);
          store.put({
            key: 'latest_feed',
            posts: posts.slice(0, 60),
            cachedAt: Date.now()
          });
          tx.oncomplete = function () {
            return resolve(true);
          };
          tx.onerror = function () {
            return resolve(false);
          };
        } catch (e) {
          console.warn('[OfflineSync] Error writing to feed cache:', e);
          resolve(false);
        }
      });
    }

    /**
     * 6. Retrieve Cached Feed Posts
     */
  }, {
    key: "getCachedFeed",
    value: function getCachedFeed() {
      var _this9 = this;
      return new Promise(function (resolve) {
        if (!_this9.db) {
          _this9.initDB().then(function () {
            _this9.readCachedFeed(resolve);
          }).catch(function () {
            return resolve([]);
          });
          return;
        }
        _this9.readCachedFeed(resolve);
      });
    }
  }, {
    key: "readCachedFeed",
    value: function readCachedFeed(resolve) {
      try {
        var tx = this.db.transaction(FEED_STORE_NAME, 'readonly');
        var store = tx.objectStore(FEED_STORE_NAME);
        var req = store.get('latest_feed');
        req.onsuccess = function () {
          var result = req.result;
          resolve((result === null || result === void 0 ? void 0 : result.posts) || []);
        };
        req.onerror = function () {
          return resolve([]);
        };
      } catch (e) {
        console.warn('[OfflineSync] Error reading feed cache:', e);
        resolve([]);
      }
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (OfflineSyncManager);

/***/ }),

/***/ "./resources/asset/js/components/pwa/pwaManager.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/pwa/pwaManager.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _offlineSync__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./offlineSync */ "./resources/asset/js/components/pwa/offlineSync.js");
/* harmony import */ var _haptics__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./haptics */ "./resources/asset/js/components/pwa/haptics.js");
/**
 * FamilyPlatform PWA Manager
 * World-Class Progressive Web App Engine:
 * - In-app installation banner (Android/Chrome/Edge)
 * - iOS Safari animated 'Add to Home Screen' visual tutorial
 * - Service worker lifecycle & seamless update notification
 * - Real-time Online/Offline connection HUD
 * - Native Badging API integration
 */









var PWAManager = /*#__PURE__*/function () {
  function PWAManager() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, PWAManager);
    this.deferredPrompt = null;
    this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    this.offlineSync = new _offlineSync__WEBPACK_IMPORTED_MODULE_4__["default"]();
    window.offlineSync = this.offlineSync;
    window.triggerHaptic = _haptics__WEBPACK_IMPORTED_MODULE_5__.triggerHaptic;
    // Web Push lives in components/profilePage/registerPushNotification.js — one
    // module, imported by the profile-page bundle and driven by the Settings toggle.
    this.init();
  }
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(PWAManager, [{
    key: "init",
    value: function init() {
      this.registerServiceWorker();
      this.setupInstallPrompt();
      this.setupIOSPrompt();
      this.setupNetworkHUD();
      this.setupLogoutCachePurge();
      this.setupBottomNav();
      this.setupHaptics();
    }

    /**
     * Setup Logout Listener to purge dynamic user cache
     */
  }, {
    key: "setupLogoutCachePurge",
    value: function setupLogoutCachePurge() {
      document.addEventListener('click', function (e) {
        var _navigator$serviceWor;
        var link = e.target.closest('a[href*="signout"], a[href*="logout"]');
        if (link && (_navigator$serviceWor = navigator.serviceWorker) !== null && _navigator$serviceWor !== void 0 && _navigator$serviceWor.controller) {
          navigator.serviceWorker.controller.postMessage({
            action: 'clearUserCache'
          });
        }
      });
    }

    /**
     * 1. Register Service Worker & Handle Updates
     */
  }, {
    key: "registerServiceWorker",
    value: function registerServiceWorker() {
      var _this = this;
      if (!('serviceWorker' in navigator)) return;
      window.addEventListener('load', function () {
        navigator.serviceWorker.register('/service-worker.js', {
          scope: '/'
        }).then(function (registration) {
          // Check for SW updates
          registration.addEventListener('updatefound', function () {
            var newWorker = registration.installing;
            if (!newWorker) return;
            newWorker.addEventListener('statechange', function () {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                _this.showUpdateToast(newWorker);
              }
            });
          });
        }).catch(function (err) {
          console.warn('[PWA] Service Worker registration failed:', err);
        });

        // Handle controllerchange to auto-reload upon skipWaiting.
        // controllerchange also fires the very first time a service worker
        // ever activates for this origin (no prior controller) - reloading
        // then would blow away whatever a first-time visitor is mid-doing
        // (e.g. an in-flight login). Only reload when a controller already
        // existed, i.e. this is an actual update taking over, not an install.
        var hadController = !!navigator.serviceWorker.controller;
        var refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', function () {
          if (!hadController) {
            hadController = true;
            return;
          }
          if (!refreshing) {
            refreshing = true;
            window.location.reload();
          }
        });
      });
    }

    /**
     * Show a non-intrusive Update Notification Toast
     */
  }, {
    key: "showUpdateToast",
    value: function showUpdateToast(newWorker) {
      var _document$getElementB, _document$getElementB2;
      if (document.getElementById('pwa-update-toast')) return;
      var toast = document.createElement('div');
      toast.id = 'pwa-update-toast';
      toast.innerHTML = "\n      <div class=\"pwa-toast-card\">\n        <div class=\"pwa-toast-icon\">\u26A1</div>\n        <div class=\"pwa-toast-content\">\n          <h6>Update Available</h6>\n          <p>A fresh version of FamilyPlatform is ready.</p>\n        </div>\n        <button id=\"pwa-update-btn\" class=\"pwa-btn pwa-btn-primary\">Update</button>\n        <button id=\"pwa-update-close\" class=\"pwa-btn-close\" aria-label=\"Close\">&times;</button>\n      </div>\n    ";
      document.body.appendChild(toast);
      (_document$getElementB = document.getElementById('pwa-update-btn')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.addEventListener('click', function () {
        newWorker.postMessage({
          action: 'skipWaiting'
        });
        toast.remove();
      });
      (_document$getElementB2 = document.getElementById('pwa-update-close')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.addEventListener('click', function () {
        toast.remove();
      });
    }

    /**
     * 2. Android / Chromium Custom Install Banner
     */
  }, {
    key: "setupInstallPrompt",
    value: function setupInstallPrompt() {
      var _this2 = this;
      window.addEventListener('beforeinstallprompt', function (e) {
        // Prevent standard browser mini-infobar
        e.preventDefault();
        _this2.deferredPrompt = e;

        // Don't show if user dismissed within last 7 days or already installed
        var dismissed = localStorage.getItem('pwa_install_dismissed');
        if (dismissed && Date.now() - parseInt(dismissed, 10) < 7 * 86400000) {
          return;
        }
        _this2.showInstallBanner();
      });
      window.addEventListener('appinstalled', function () {
        var _document$getElementB3;
        _this2.deferredPrompt = null;
        (_document$getElementB3 = document.getElementById('pwa-install-banner')) === null || _document$getElementB3 === void 0 ? void 0 : _document$getElementB3.remove();
        console.log('[PWA] FamilyPlatform successfully installed!');
      });
    }
  }, {
    key: "showInstallBanner",
    value: function showInstallBanner() {
      var _document$getElementB4,
        _this3 = this,
        _document$getElementB5;
      if (document.getElementById('pwa-install-banner') || this.isStandalone) return;
      var banner = document.createElement('div');
      banner.id = 'pwa-install-banner';
      banner.innerHTML = "\n      <div class=\"pwa-banner-card\">\n        <div class=\"pwa-banner-header\">\n          <img src=\"/public/img/favicon/android-chrome-192x192.png\" alt=\"FamilyPlatform\" class=\"pwa-banner-logo\" style=\"width: 46px; height: 46px; border-radius: 12px; object-fit: cover;\" onerror=\"this.src='/resources/images/avatarM.png'\">\n          <div class=\"pwa-banner-text\">\n            <h6>Install FamilyPlatform</h6>\n            <p>Get instant alerts, fast family updates & offline access.</p>\n          </div>\n          <button id=\"pwa-banner-close\" class=\"pwa-btn-close\" aria-label=\"Dismiss\">&times;</button>\n        </div>\n        <div class=\"pwa-banner-actions\">\n          <button id=\"pwa-install-btn\" class=\"pwa-btn pwa-btn-primary\">\n            <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"me-1\"><path d=\"M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4\"></path><polyline points=\"7 10 12 15 17 10\"></polyline><line x1=\"12\" y1=\"15\" x2=\"12\" y2=\"3\"></line></svg>\n            Add to Home Screen\n          </button>\n        </div>\n      </div>\n    ";
      document.body.appendChild(banner);

      // Trigger slide-up animation
      setTimeout(function () {
        return banner.classList.add('show');
      }, 100);
      (_document$getElementB4 = document.getElementById('pwa-install-btn')) === null || _document$getElementB4 === void 0 ? void 0 : _document$getElementB4.addEventListener('click', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
        var _yield$_this3$deferre, outcome;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              if (_this3.deferredPrompt) {
                _context.next = 1;
                break;
              }
              return _context.abrupt("return");
            case 1:
              banner.remove();
              _this3.deferredPrompt.prompt();
              _context.next = 2;
              return _this3.deferredPrompt.userChoice;
            case 2:
              _yield$_this3$deferre = _context.sent;
              outcome = _yield$_this3$deferre.outcome;
              console.log("[PWA] Install prompt outcome: ".concat(outcome));
              _this3.deferredPrompt = null;
            case 3:
            case "end":
              return _context.stop();
          }
        }, _callee);
      })));
      (_document$getElementB5 = document.getElementById('pwa-banner-close')) === null || _document$getElementB5 === void 0 ? void 0 : _document$getElementB5.addEventListener('click', function () {
        localStorage.setItem('pwa_install_dismissed', Date.now().toString());
        banner.classList.remove('show');
        setTimeout(function () {
          return banner.remove();
        }, 400);
      });
    }

    /**
     * 3. iOS Safari "Add to Home Screen" Visual Overlay Tutorial
     */
  }, {
    key: "setupIOSPrompt",
    value: function setupIOSPrompt() {
      var _this4 = this;
      if (!this.isIOS || this.isStandalone) return;
      var dismissed = localStorage.getItem('pwa_ios_dismissed');
      if (dismissed && Date.now() - parseInt(dismissed, 10) < 7 * 86400000) {
        return;
      }

      // Delay prompt to not overwhelm user on initial landing
      setTimeout(function () {
        _this4.showIOSOverlay();
      }, 4000);
    }
  }, {
    key: "showIOSOverlay",
    value: function showIOSOverlay() {
      var _document$getElementB6;
      if (document.getElementById('pwa-ios-overlay') || this.isStandalone) return;
      var overlay = document.createElement('div');
      overlay.id = 'pwa-ios-overlay';
      overlay.innerHTML = "\n      <div class=\"pwa-ios-card\">\n        <button id=\"pwa-ios-close\" class=\"pwa-btn-close\" aria-label=\"Close\">&times;</button>\n        <div class=\"pwa-ios-header\">\n          <img src=\"/public/img/favicon/apple-touch-icon.png\" alt=\"FamilyPlatform\" class=\"pwa-ios-logo\" onerror=\"this.src='/resources/images/avatarM.png'\">\n          <div>\n            <h6>Install FamilyPlatform on iOS</h6>\n            <p>Install this app on your iPhone for the full native experience.</p>\n          </div>\n        </div>\n        <div class=\"pwa-ios-steps\">\n          <div class=\"pwa-step\">\n            <span class=\"step-num\">1</span>\n            <span>Tap the <strong>Share</strong> button <svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#007aff\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"vertical-align: middle;\"><path d=\"M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8\"/><polyline points=\"16 6 12 2 8 6\"/><line x1=\"12\" y1=\"2\" x2=\"12\" y2=\"15\"/></svg> at the bottom of Safari.</span>\n          </div>\n          <div class=\"pwa-step\">\n            <span class=\"step-num\">2</span>\n            <span>Scroll down and tap <strong>Add to Home Screen</strong> <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"vertical-align: middle;\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><line x1=\"12\" y1=\"8\" x2=\"12\" y2=\"16\"/><line x1=\"8\" y1=\"12\" x2=\"16\" y2=\"12\"/></svg>.</span>\n          </div>\n        </div>\n        <div class=\"pwa-ios-arrow-pointer\">\n          <svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#007aff\" stroke-width=\"2.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><line x1=\"12\" y1=\"5\" x2=\"12\" y2=\"19\"></line><polyline points=\"19 12 12 19 5 12\"></polyline></svg>\n        </div>\n      </div>\n    ";
      document.body.appendChild(overlay);
      setTimeout(function () {
        return overlay.classList.add('show');
      }, 100);
      (_document$getElementB6 = document.getElementById('pwa-ios-close')) === null || _document$getElementB6 === void 0 ? void 0 : _document$getElementB6.addEventListener('click', function () {
        localStorage.setItem('pwa_ios_dismissed', Date.now().toString());
        overlay.classList.remove('show');
        setTimeout(function () {
          return overlay.remove();
        }, 400);
      });
    }

    /**
     * 4. Network Online/Offline HUD Banner
     */
  }, {
    key: "setupNetworkHUD",
    value: function setupNetworkHUD() {
      var updateNetworkStatus = function updateNetworkStatus(online) {
        var hud = document.getElementById('pwa-network-hud');
        if (!hud) {
          hud = document.createElement('div');
          hud.id = 'pwa-network-hud';
          document.body.appendChild(hud);
        }
        if (online) {
          hud.className = 'pwa-hud online show';
          hud.innerHTML = "<span>\uD83D\uDFE2 Connected \u2014 Real-time family updates active</span>";
          setTimeout(function () {
            hud.classList.remove('show');
          }, 3000);
        } else {
          hud.className = 'pwa-hud offline show';
          hud.innerHTML = "<span>\u26A0\uFE0F Offline Mode \u2014 Changes are queued and will sync automatically</span>";
        }
      };
      window.addEventListener('online', function () {
        return updateNetworkStatus(true);
      });
      window.addEventListener('offline', function () {
        return updateNetworkStatus(false);
      });

      // Initial check
      if (!navigator.onLine) {
        updateNetworkStatus(false);
      }
    }

    /**
     * 5. App Badging API
     */
  }, {
    key: "setupBottomNav",
    value:
    /**
     * 6. Mobile Bottom Navigation Shell & Badge Sync
     */
    function setupBottomNav() {
      var nav = document.getElementById('pwaBottomNav');
      if (!nav) return;

      // Highlight active tab based on current path
      var path = window.location.pathname.toLowerCase();
      var tabs = nav.querySelectorAll('.pwa-tab-item');
      tabs.forEach(function (tab) {
        var href = (tab.getAttribute('href') || '').toLowerCase();
        var tabType = tab.dataset.tab;
        if (tabType === 'feed' && (path === '/profilepage' || path === '/' || path === '')) {
          tab.classList.add('active');
        } else if (tabType === 'tree' && path.includes('/organogram')) {
          tab.classList.add('active');
        } else if (tabType === 'reels' && (path.includes('/reels') || path.includes('/familystudio'))) {
          tab.classList.add('active');
        } else if (tabType === 'members' && path.includes('/allmembers')) {
          tab.classList.add('active');
        } else if (href && href !== 'javascript:void(0)' && path === href) {
          tab.classList.add('active');
        } else {
          tab.classList.remove('active');
        }
        tab.addEventListener('click', function () {
          (0,_haptics__WEBPACK_IMPORTED_MODULE_5__.triggerHaptic)('selection');
        });
      });

      // Wire Alerts Tab click
      var notifTab = document.getElementById('pwaNotifTab');
      if (notifTab) {
        notifTab.addEventListener('click', function (e) {
          e.preventDefault();
          (0,_haptics__WEBPACK_IMPORTED_MODULE_5__.triggerHaptic)('selection');
          var desktopNotifBtn = document.getElementById('notificationBtn');
          if (desktopNotifBtn) {
            desktopNotifBtn.click();
          } else {
            window.location.href = '/notifications';
          }
        });
      }

      // Two-way synchronization of notification badge count
      var syncBadge = function syncBadge() {
        var desktopBadge = document.getElementById('notification_count');
        var mobileBadge = document.getElementById('pwa_bottom_badge');
        if (!desktopBadge || !mobileBadge) return;
        var rawCount = desktopBadge.textContent.trim();
        var count = parseInt(rawCount, 10);
        if (!isNaN(count) && count > 0) {
          mobileBadge.textContent = count > 99 ? '99+' : count;
          mobileBadge.style.display = 'inline-flex';
          PWAManager.setBadge(count);
        } else {
          mobileBadge.textContent = '';
          mobileBadge.style.display = 'none';
          PWAManager.setBadge(0);
        }
      };

      // Initial sync
      syncBadge();

      // Observe changes to desktop badge
      var desktopBadge = document.getElementById('notification_count');
      if (desktopBadge) {
        var observer = new MutationObserver(syncBadge);
        observer.observe(desktopBadge, {
          childList: true,
          characterData: true,
          subtree: true
        });
      }
    }

    /**
     * 7. Native Haptics Delegation
     */
  }, {
    key: "setupHaptics",
    value: function setupHaptics() {
      document.addEventListener('click', function (e) {
        // Like / Reactions
        if (e.target.closest('.likeBtn, .like-btn, .reaction-btn, [data-reaction], .btn-like')) {
          (0,_haptics__WEBPACK_IMPORTED_MODULE_5__.triggerHaptic)('impact');
        }
        // Interactive pill buttons
        else if (e.target.closest('.btn-primary, .btn-user-stitch, .btn-icon-stitch')) {
          (0,_haptics__WEBPACK_IMPORTED_MODULE_5__.triggerHaptic)('selection');
        }
      }, {
        passive: true
      });
    }
  }], [{
    key: "setBadge",
    value: function setBadge() {
      var count = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
      if ('setAppBadge' in navigator) {
        if (count > 0) {
          navigator.setAppBadge(count).catch(function () {});
        } else {
          navigator.clearAppBadge().catch(function () {});
        }
      }
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (PWAManager);

/***/ }),

/***/ "./resources/asset/js/index.js":
/*!*************************************!*\
  !*** ./resources/asset/js/index.js ***!
  \*************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var alpinejs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! alpinejs */ "./node_modules/alpinejs/dist/module.esm.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_pwa_pwaManager__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/pwa/pwaManager */ "./resources/asset/js/components/pwa/pwaManager.js");
/* harmony import */ var _components_darkMode__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/darkMode */ "./resources/asset/js/components/darkMode.js");
/* harmony import */ var _components_profilePage_feedComponent__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/profilePage/feedComponent */ "./resources/asset/js/components/profilePage/feedComponent.js");
/* harmony import */ var _components_profilePage_sidebarComponents__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./components/profilePage/sidebarComponents */ "./resources/asset/js/components/profilePage/sidebarComponents.js");










window.Swal = (sweetalert2__WEBPACK_IMPORTED_MODULE_2___default());
window.Alpine = alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"];
alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"].data('profileFeed', _components_profilePage_feedComponent__WEBPACK_IMPORTED_MODULE_5__.profileFeed);
alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"].data('profileSidebar', _components_profilePage_sidebarComponents__WEBPACK_IMPORTED_MODULE_6__.profileSidebar);
alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"].data('upcomingEvents', _components_profilePage_sidebarComponents__WEBPACK_IMPORTED_MODULE_6__.upcomingEvents);
window.profileFeed = _components_profilePage_feedComponent__WEBPACK_IMPORTED_MODULE_5__.profileFeed;
window.profileSidebar = _components_profilePage_sidebarComponents__WEBPACK_IMPORTED_MODULE_6__.profileSidebar;
window.upcomingEvents = _components_profilePage_sidebarComponents__WEBPACK_IMPORTED_MODULE_6__.upcomingEvents;
window.pwaManager = new _components_pwa_pwaManager__WEBPACK_IMPORTED_MODULE_3__["default"]();
(0,_components_darkMode__WEBPACK_IMPORTED_MODULE_4__.initDarkMode)();
var routePromise = Promise.resolve();

// The server occasionally finds the session's CSRF token missing (e.g. session
// data expired/evicted between page load and this request) and responds 401
// with "We are not familiar with the nature of your activities.". That same
// response always carries a fresh Set-Cookie: XSRF-TOKEN, which the browser
// applies immediately — so a single automatic retry of the exact same request
// picks up the new cookie and succeeds transparently instead of surfacing a
// dead-end error the user has to work around by reloading the page.
axios__WEBPACK_IMPORTED_MODULE_7__["default"].interceptors.response.use(function (response) {
  return response;
}, function (error) {
  var _error$response, _error$response$data, _error$response2;
  var config = error.config;
  var message = (_error$response = error.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message;
  var isStaleCsrfToken = ((_error$response2 = error.response) === null || _error$response2 === void 0 ? void 0 : _error$response2.status) === 401 && typeof message === 'string' && message.includes('not familiar with the nature of your activities') && config && !config._csrfRetried;
  if (isStaleCsrfToken) {
    config._csrfRetried = true;
    if (config.headers) {
      delete config.headers['X-XSRF-TOKEN'];
      delete config.headers['x-xsrf-token'];
      delete config.headers['X-CSRF-TOKEN'];
      delete config.headers['x-csrf-token'];
    }
    return (0,axios__WEBPACK_IMPORTED_MODULE_7__["default"])(config);
  }
  return Promise.reject(error);
});

/**
 * Tests if the current URL matches the given route.
 *
 * @param {string} url A URL route to test.
 *
 * @returns {boolean} True if the URL matches, otherwise false.
 */
var checkURL = function checkURL(url) {
  return !!window.location.pathname.match(new RegExp("^/".concat(url, "(?:/[^/]+)*$")));
};
try {
  var $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

  // Add a click event on each of them
  $navbarBurgers.forEach(function (el) {
    el.addEventListener('click', function () {
      // Get the target from the "data-target" attribute
      var target = el.dataset.target;
      var $target = document.getElementById(target);

      // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
      el.classList.toggle('is-active');
      $target.classList.toggle('is-active');
    });
  });
} catch (error) {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
}

// Get all "navbar-burger" elements

if (window.location.pathname === '/register') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.registerNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.registerNav').style.display = 'none'; // navbar mgt

  routePromise = Promise.all(/*! import() | register */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("register")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/ */ "./resources/asset/js/components/register/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMemberNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMemberNav').style.display = 'none';
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMembersNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMembersNav').style.display = 'none';
  routePromise = Promise.all(/*! import() | all_members */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("all_members")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/allMembers/ */ "./resources/asset/js/components/allMembers/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname.toLowerCase().startsWith('/reels')) {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.reelsNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.reelsNav').style.display = 'none';
  routePromise = __webpack_require__.e(/*! import() | reels */ "reels").then(__webpack_require__.bind(__webpack_require__, /*! ./components/reels/reelsPlayer */ "./resources/asset/js/components/reels/reelsPlayer.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | login */ "login").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/login */ "./resources/asset/js/components/acctMgt/login.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/lasu') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | adminLogin */ "adminLogin").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/adminLogin */ "./resources/asset/js/components/acctMgt/adminLogin.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login/forgot') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | forgotPwd */ "forgotPwd").then(__webpack_require__.bind(__webpack_require__, /*! ./components/forgotPwd/ */ "./resources/asset/js/components/forgotPwd/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (['/login/code', '/verify-email'].includes(window.location.pathname)) {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | code */ "code").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/code */ "./resources/asset/js/components/acctMgt/code.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname.toLowerCase().startsWith('/member/profilepage') || window.location.pathname.toLowerCase().startsWith('/profilepage') || checkURL('member/ProfilePage') || checkURL('member/profilePage') || checkURL('profilePage')) {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.profilePageNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.profilePageNav').style.display = 'none'; // navbar mgt

  routePromise = Promise.all(/*! import() | profilePage */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("profilePage")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/ */ "./resources/asset/js/components/profilePage/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login/changePW' || window.location.pathname === '/changePW') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login').style.display = 'none'; // navbar mgt
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp').style.display = 'none'; // navbar mgt
  // qSel('#loader').style.display ="none" // loader
  routePromise = __webpack_require__.e(/*! import() | changePW */ "changePW").then(__webpack_require__.bind(__webpack_require__, /*! ./components/changePW/ */ "./resources/asset/js/components/changePW/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/profilepage/img') {
  // qSel('.login').style.display ="none" // navbar mgt
  routePromise = Promise.all(/*! import() | img */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("img")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/imgViewer */ "./resources/asset/js/components/profilePage/imgViewer.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/createFamilyCode') {
  routePromise = __webpack_require__.e(/*! import() | familyCode */ "familyCode").then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/familyCode */ "./resources/asset/js/components/register/familyCode.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/register/nextStep') {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login').style.display = 'none'; // navbar mgt
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp').style.display = 'none'; // navbar mgt
} else if (checkURL('accountSetting')) {
  routePromise = Promise.all(/*! import() | accountSetting */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("accountSetting")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/accountSetting */ "./resources/asset/js/components/accountSetting.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (checkURL('organogram') || window.location.pathname.startsWith('/organogram')) {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | organogram */ "organogram").then(__webpack_require__.bind(__webpack_require__, /*! ./components/familyTree/index.js */ "./resources/asset/js/components/familyTree/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers/getProfile') {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav').style.display = 'none'; // navbar mgt
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_count').style.display = 'none'; // navbar mgt

  // import (
  // import { setCookie } from '../../../node_modules/y/Cookie';
  /* webpackChunkName: 'getProfile' */
  //     /* webpackPrefetch: true */
  //     './components/familyTree/index.js'
  // )
  // .then((module) => module.default)
  //     .catch((err) => showError(err))
}
routePromise.finally(function () {
  alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"].start();
});

/***/ }),

/***/ "./resources/asset/scss/main.scss":
/*!****************************************!*\
  !*** ./resources/asset/scss/main.scss ***!
  \****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/startup prefetch */
/******/ !function() {
/******/ 	__webpack_require__.O(0, ["/js/index"], function() {
/******/ 		["/js/vendor","register","all_members","reels","login","adminLogin","forgotPwd","code","profilePage","changePW","img","familyCode","accountSetting","organogram"].map(__webpack_require__.E);
/******/ 	}, 5);
/******/ }();
/******/ 
/******/ var __webpack_exec__ = function(moduleId) { return __webpack_require__(__webpack_require__.s = moduleId); }
/******/ __webpack_require__.O(0, ["css/main","/js/vendor"], function() { return __webpack_exec__("./resources/asset/js/index.js"), __webpack_exec__("./resources/asset/scss/main.scss"); });
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=index.js.map