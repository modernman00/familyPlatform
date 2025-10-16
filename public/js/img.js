"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["img"],{

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
/* harmony export */   postMultipleApiData: () => (/* binding */ postMultipleApiData),
/* harmony export */   setCookie: () => (/* binding */ setCookie)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios-retry */ "./node_modules/axios-retry/lib/esm/index.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }



// import Cookies from 'js-cookie'

(0,axios_retry__WEBPACK_IMPORTED_MODULE_2__["default"])(axios__WEBPACK_IMPORTED_MODULE_1__["default"], {
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
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(url, formId) {
    var redirect,
      css,
      notificationForm,
      notificationId,
      form,
      formEntries,
      options,
      response,
      _response$data,
      successClass,
      idSetFromHttp,
      famCodeSetFromHttp,
      dbHttpResult,
      redirectDelay,
      _error$response,
      errorClass,
      errorMessage,
      _args = arguments,
      _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          redirect = _args.length > 2 && _args[2] !== undefined ? _args[2] : null;
          css = _args.length > 3 && _args[3] !== undefined ? _args[3] : null;
          notificationForm = "".concat(formId, "_notification");
          notificationId = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(notificationForm);
          if (notificationId) {
            _context.n = 1;
            break;
          }
          throw new Error('Notification element not found');
        case 1:
          // Cleanup previous notification styles
          notificationId.style.display = 'none';
          ['is-danger', 'is-success', 'w3-red', 'w3-green', 'bg-danger', 'bg-success'].forEach(function (cls) {
            return notificationId.classList.remove(cls);
          });

          // extract the form entriesËËË
          form = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(formId);
          if (form) {
            _context.n = 2;
            break;
          }
          throw new Error('Form element not found');
        case 2:
          formEntries = new FormData(form);
          formEntries["delete"]('submit');
          formEntries["delete"]('checkbox_id');
          options = {
            baseURL: '/',
            // Adjust to your API base URL
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN',
            withCredentials: true // Ensure cookies (e.g., XSRF token) are sent
          }; // AXIOS POST FUNCTIONALITY
          _context.p = 3;
          _context.n = 4;
          return axios__WEBPACK_IMPORTED_MODULE_1__["default"].post(url, formEntries, options);
        case 4:
          response = _context.v;
          if (!(response.status < 200 || response.status >= 300)) {
            _context.n = 5;
            break;
          }
          throw new Error(((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'Request failed');
        case 5:
          successClass = getNotificationClassByCSS(css || 'bulma', 'green'); // check if response.data.message is an array
          idSetFromHttp = null;
          famCodeSetFromHttp = null;
          dbHttpResult = null;
          if (!(response.data && _typeof(response.data.message) === 'object')) {
            _context.n = 9;
            break;
          }
          idSetFromHttp = response.data.message.id || null;
          famCodeSetFromHttp = response.data.message.famCode || null;
          dbHttpResult = response.data.message.outcome || null;
          if (idSetFromHttp) {
            _context.n = 6;
            break;
          }
          throw new Error('idSetFromHttp is missing');
        case 6:
          if (dbHttpResult) {
            _context.n = 7;
            break;
          }
          throw new Error('dbHttpResult is missing');
        case 7:
          if (famCodeSetFromHttp) {
            _context.n = 8;
            break;
          }
          throw new Error('famCodeSetFromHttp is missing');
        case 8:
          _context.n = 10;
          break;
        case 9:
          dbHttpResult = response.data.message;
        case 10:
          // Set sessionStorage items if not already set
          if (!sessionStorage.getItem('idSetFromHttp')) sessionStorage.setItem('idSetFromHttp', idSetFromHttp);
          if (!sessionStorage.getItem('famCodeSetFromHttp')) sessionStorage.setItem('famCodeSetFromHttp', famCodeSetFromHttp);
          processFormDataAction(successClass, dbHttpResult, notificationId);
          if (redirect) {
            redirectDelay = 2000; // Configurable delay in ms
            setTimeout(function () {
              window.location.assign(redirect);
            }, redirectDelay);
          }
          _context.n = 12;
          break;
        case 11:
          _context.p = 11;
          _t = _context.v;
          errorClass = getNotificationClassByCSS(css || 'bulma', 'red');
          errorMessage = ((_error$response = _t.response) === null || _error$response === void 0 || (_error$response = _error$response.data) === null || _error$response === void 0 ? void 0 : _error$response.error) || _t.request || 'An unknown error occurred';
          processFormDataAction(errorClass, errorMessage, notificationId);
        case 12:
          return _context.a(2);
      }
    }, _callee, null, [[3, 11]]);
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
    var errorElement = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error');
    if (errorElement) {
      errorElement.scrollIntoView({
        behavior: 'smooth'
      });
      errorElement.innerHTML = message;
    }
    var loader = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('setLoader');
    if (loader) loader.classList.remove('loader');
  } else {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)('Notification element not found');
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
  var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(URL) {
    var token,
      config,
      fetch,
      _args2 = arguments,
      _t2;
    return _regenerator().w(function (_context2) {
      while (1) switch (_context2.p = _context2.n) {
        case 0:
          token = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : null;
          _context2.p = 1;
          config = {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          };
          _context2.n = 2;
          return axios__WEBPACK_IMPORTED_MODULE_1__["default"].get(URL, config);
        case 2:
          fetch = _context2.v;
          return _context2.a(2, fetch.data);
        case 3:
          _context2.p = 3;
          _t2 = _context2.v;
          return _context2.a(2, _t2);
      }
    }, _callee2, null, [[1, 3]]);
  }));
  return function getApiData(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
var getMultipleApiData = /*#__PURE__*/function () {
  var _ref3 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(url1, url2) {
    var token,
      config,
      fetch,
      _args3 = arguments,
      _t3;
    return _regenerator().w(function (_context3) {
      while (1) switch (_context3.p = _context3.n) {
        case 0:
          token = _args3.length > 2 && _args3[2] !== undefined ? _args3[2] : null;
          _context3.p = 1;
          config = {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          };
          _context3.n = 2;
          return axios__WEBPACK_IMPORTED_MODULE_1__["default"].all([axios__WEBPACK_IMPORTED_MODULE_1__["default"].get(url1, config), axios__WEBPACK_IMPORTED_MODULE_1__["default"].get(url2, config)]);
        case 2:
          fetch = _context3.v;
          return _context3.a(2, fetch);
        case 3:
          _context3.p = 3;
          _t3 = _context3.v;
          return _context3.a(2, _t3);
      }
    }, _callee3, null, [[1, 3]]);
  }));
  return function getMultipleApiData(_x4, _x5) {
    return _ref3.apply(this, arguments);
  };
}();

// build a function to post multiple api form data

var postMultipleApiData = /*#__PURE__*/function () {
  var _ref4 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4(url1, url2, formData) {
    var token,
      config,
      fetch,
      _args4 = arguments,
      _t4;
    return _regenerator().w(function (_context4) {
      while (1) switch (_context4.p = _context4.n) {
        case 0:
          token = _args4.length > 3 && _args4[3] !== undefined ? _args4[3] : null;
          _context4.p = 1;
          config = {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer ' + token
            }
          };
          _context4.n = 2;
          return axios__WEBPACK_IMPORTED_MODULE_1__["default"].all([axios__WEBPACK_IMPORTED_MODULE_1__["default"].post(url1, formData, config), axios__WEBPACK_IMPORTED_MODULE_1__["default"].post(url2, formData, config)]);
        case 2:
          fetch = _context4.v;
          return _context4.a(2, fetch);
        case 3:
          _context4.p = 3;
          _t4 = _context4.v;
          return _context4.a(2, _t4);
      }
    }, _callee4, null, [[1, 3]]);
  }));
  return function postMultipleApiData(_x6, _x7, _x8) {
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

/***/ "./resources/asset/js/components/profilePage/comment.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/comment.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   appendNewComment: () => (/* binding */ appendNewComment),
/* harmony export */   commentHTML: () => (/* binding */ commentHTML),
/* harmony export */   showComment: () => (/* binding */ showComment)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");


var reqId = localStorage.getItem('requesterId');
var commentHTML = function commentHTML(data, postId) {
  var profileImg = data.profileImg,
    fullName = data.fullName,
    date_created = data.date_created,
    img = data.img,
    comment = data.comment,
    comment_no = data.comment_no,
    id = data.id;
  var imgURL = profileImg || img;
  var image = imgURL ? "/resources/images/profile/".concat(imgURL) : "/public/avatar/avatarF.png";
  return "<div class=\"d-flex mb-3 commentDiv align-items-start\" data-commentDiv-no=\"".concat(comment_no, "\" id=\"commentDiv").concat(comment_no, "\" name=\"commentDiv\">\n\n  <img src=\"").concat(image, "\" alt=\"Avatar\" class=\"rounded-circle me-2 commentImg\" width=\"32\" height=\"32\">\n\n  <div class=\"flex-grow-1\">\n    <div class=\"d-flex justify-content-between align-items-center\">\n      <strong>").concat((0,_shared__WEBPACK_IMPORTED_MODULE_1__.toSentenceCase)(fullName), "</strong>\n      <small class=\"text-muted commentTiming\" datetime=\"").concat(date_created, "\" title=\"").concat(date_created, "\">\n        ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), "\n      </small>\n    </div>\n\n    <div class=\"comment-text mb-2\">\n      ").concat(comment, "\n    </div>\n\n     <div class=\"reaction-preview\" id=\"reaction-preview-").concat(comment_no, "\"></div>\n\n      <div class=\"comment-actions d-flex gap-3\">\n    \n                \n                <div class=\"reaction-bar\" id=\"reaction-bar-").concat(comment_no, "\">\n                    <div class=\"reaction-option\" data-reaction=\"like\" data-label=\"Like\">\n                        <div class=\"reaction-emoji\">\uD83D\uDC4D</div>\n                    </div>\n                    <div class=\"reaction-option\" data-reaction=\"love\" data-label=\"Love\">\n                        <div class=\"reaction-emoji\">\u2764\uFE0F</div>\n                    </div>\n                    <div class=\"reaction-option\" data-reaction=\"haha\" data-label=\"Haha\">\n                        <div class=\"reaction-emoji\">\uD83D\uDE04</div>\n                    </div>\n                    <div class=\"reaction-option\" data-reaction=\"wow\" data-label=\"Wow\">\n                        <div class=\"reaction-emoji\">\uD83D\uDE2E</div>\n                    </div>\n                    <div class=\"reaction-option\" data-reaction=\"sad\" data-label=\"Sad\">\n                        <div class=\"reaction-emoji\">\uD83D\uDE22</div>\n                    </div>\n                    <div class=\"reaction-option\" data-reaction=\"angry\" data-label=\"Angry\">\n                        <div class=\"reaction-emoji\">\uD83D\uDE20</div>\n                    </div>\n                </div>\n\n                <div class=\"reaction-button like-button-").concat(comment_no, "\" id=\"like-button-").concat(comment_no, "\" data-comment-no=\"").concat(comment_no, "\">\n                    <i class=\"bi bi-hand-thumbs-up reaction-icon\"></i>\n                    <span>Like</span>\n                    <div class=\"reaction-count\" id=\"like-count-").concat(comment_no, "\">0</div>\n                </div>\n\n                ").concat(reqId == id || reqId == postId ? "<button class=\"btn btn-sm btn-icon text-danger\" id=\"removeComment(".concat(comment_no, ")\" title=\"Remove\">\n                    <i class=\"bi bi-trash\" id=\"removeCommentIcon").concat(comment_no, "\"></i>\n                </button>") : '', "\n                \n         \n      </div>\n\n\n\n\n  </div>\n</div><hr>");
};

// i need the postid to use to show the delete button 
var showComment = function showComment(comment, postId) {
  if (!comment) {
    return "<div id=\"comment\" name=\"commentDiv\"></div>";
  } // only run if there is comment

  // USED FOR ALL THE COMMENTS WHEN THE PAGE IS LOADING
  var commentHTMLArray = comment.map(function (commentElement) {
    return commentHTML(commentElement, postId);
  });
  return commentHTMLArray.join(''); // Join the array elements into a single string
};
var appendNewComment = function appendNewComment(commentData) {
  // check if commentData is valid
  if (!commentData) {
    throw new Error('No comment update received');
  }
  var idDiv = "showComment".concat(commentData.post_no);
  // check if the div has been created by the DOM 

  var commentContainer = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)(idDiv);
  if (!commentContainer) {
    throw new Error("The comment div id does not exist ");
  }
  var commentHtml = commentHTML(commentData);
  commentContainer.insertAdjacentHTML('beforeend', commentHtml);
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
  var post_no = el.post_no,
    postMessage = el.postMessage,
    id = el.id;
  var commentLength = comment.length;
  return "\n    <div class=\"post card\" id=\"post".concat(post_no, "\">\n     <div class=\"card-body post").concat(post_no, "\" id=\"postIt\">\n    ").concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(el), "\n\n    <div class=\"post-content\">\n    <p class=\"card-text\"> ").concat(postMessage, " </p>\n\n     <div class=\"photo-grid grid-").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.imgCount)(el), "\">\n      ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(el), "\n    </div>\n    </div>\n\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(el, commentLength), "\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(el), "\n    <div id = 'showComment").concat(post_no, "' class=\"comment-section\">\n    ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment, id), "\n\n      \n    </div>\n");
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
var commentForm = function commentForm(_ref) {
  var post_no = _ref.post_no;
  return "\n    <p id=\"formComment".concat(post_no, "_notification\"></p>\n\n    <form \n      action=\"/postCommentProfile\" \n      method=\"post\" \n      id=\"formComment").concat(post_no, "\" \n      style=\"display:none\" \n      enctype=\"multipart/form-data\"\n      class=\"mb-4\"\n    >\n\n      <input \n        type=\"hidden\" \n        name=\"post_no\" \n        value=\"").concat(post_no, "\" \n      />\n\n             <div class=\"mt-3\">\n              <div class=\"input-group\">\n\n      <input \n        type=\"text\" \n        class=\"form-control form-control-sm inputComment\" \n        placeholder=\"Write a comment...\" \n        id=\"inputComment").concat(post_no, "\" \n        name=\"comment\" \n        value=\"\"\n      />\n\n        <button \n          type=\"submit\" \n          id=\"submitComment").concat(post_no, "\" \n          class=\"btn btn-outline-primary btn-sm submitComment\"\n        >\n          Submit\n        </button>\n      \n              </div>\n          </div>\n    </form>\n  ");
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
var likeCommentButton = function likeCommentButton(data, commentLength) {
  return "\n   <div class=\"reaction-buttons d-flex justify-content-between border-top border-bottom py-2 mb-1\">\n    <button \n      type=\"button\" \n      id=\"likeButton".concat(data.post_no, "\" \n      name=\"").concat(data.post_no, "\"\n      <i class=\"bi bi-hand-thumbs-up me-1\"></i> \n      \xA0   Like \n        <b>\n          <span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">\n            ").concat(data.post_likes, "\n          </span>\n        </b>\n    </button>\n\n    <button \n      type=\"button\" \n      id=\"initComment").concat(data.post_no, "\">\n        <i class=\"bi bi-chat me-1\"></i> \n          Comment \n          (<span class=\"commentCounter\" id=\"commentCounter").concat(data.post_no, "\">\n            ").concat(commentLength, "\n          </span>)\n          \n      </button>\n   \n    </div>\n    ");
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
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");


var timeAgo = function timeAgo(x) {
  return (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x);
};

// const fullName = (fullName) => {
//   return `<h6 id="fullName"><b>${fullName}</b> </h6>`
// }

// const postedAt = (date) => {
//   return `<div class="timeago postTimeCal w3-right w3-opacity"  datetime='${date.date_created}' title='${format(date.date_created)}'> ${timeAgo(date.post_time)}</div>`
// }

// export const nameImgTiming2 = (data) => {

//     const img = (data.profileImg) ? `/public/img/profile/${data.profileImg}` : "/public/avatar/avatarF.png"

//     return `<a href="/profilepage/img?dir=img&pics=${data.img}&pID=${data.post_no}&path=profile&id=${data.id}"> 
//       <img src=${img} alt="img" class="w3-left w3-circle w3-margin-right postImg" style="width:60px">
//         </a>
//         ${postedAt(data)} ${fullName(data.fullName)}`
// }

var nameImgTiming = function nameImgTiming(data) {
  var profileImg = data.profileImg,
    fullName = data.fullName,
    date_created = data.date_created,
    post_time = data.post_time;
  var img = profileImg ? "/resources/images/profile/".concat(profileImg) : "/public/avatar/avatarF.png";
  return "<div class=\"d-flex align-items-center mb-3\">\n\n            <img src=\"".concat(img, "\" alt=\"Profile\" class=\"rounded-circle me-3 postImg\" width=\"40\" height=\"40\">\n                        \n            <div>\n                <h6 class=\"mb-0\">").concat((0,_shared__WEBPACK_IMPORTED_MODULE_1__.toSentenceCase)(fullName), " </h6>\n\n                <small class=\"text-muted\">posted </small><small class=\"text-muted timeago postTimeCal\" title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), "' datetime='").concat(date_created, "'> ").concat(timeAgo(post_time), "</small>\n            </div>\n            </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   imgCount: () => (/* binding */ imgCount),
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
    return "\n  \n\n     \n        <img \n          src=\"/resources/images/post/".concat(imgElement, "\" \n          alt=\"images").concat(i, "\" \n          data-postImgId=\"").concat(postNo).concat(imgElement, "\"\n          data-imgIndex=\"").concat(i, "\"\n          data-postNo=\"").concat(postNo, "\"\n          class=\"grid-image zoomable-image").concat(postNo, "\" \n          id=\"postImage").concat(i, "\"\n          >\n  ");
  };
  var imgElements = postImagesWithValues.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }).join('');

  // ✅ Optional: return both HTML and count for contributor-safe rendering
  return imgElements;
};
var imgCount = function imgCount(data) {
  // GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE
  var postImagesWithValues = Object.keys(data).filter(function (key) {
    return key.startsWith('post_img') && data[key] !== null;
  }).map(function (el) {
    return data[el];
  });
  var imageCount = postImagesWithValues.length;

  // ✅ Optional: return both HTML and count for contributor-safe rendering
  return imageCount;
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/imgViewer.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/imgViewer.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");



/***/ })

}]);
//# sourceMappingURL=img.js.map