"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["familyCode"],{

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

// Import emoji metadata from emojibase (English locale, full dataset)

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

/***/ "./resources/asset/js/components/register/familyCode.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/familyCode.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }

var button = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("btnFamCode");
btnFamCode.addEventListener("click", function () {
  try {
    if ((0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('surname_id').value !== "") {
      var uniqueNumber = Date.now();
      var uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber) / 10000000);
      var getSurname = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('surname_id').value;
      var firstFourLetters = getSurname.substring(0, 4);
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createCode').value = "".concat(firstFourLetters.toUpperCase()).concat(uniqueNumber1);
      btnFamCode.disabled = true;
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("surname_error").innerHTML = error.messages;
  }
});

// Get references to the HTML output and the copy icon

var copyIcon = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('copyIcon');
var htmlOutputDiv = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createFamCode');
var htmlOutput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createCode');
copyIcon.addEventListener('click', /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(e) {
    var range, selection, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          copyIcon.innerHTML = "";
          _context.p = 1;
          e.preventDefault();

          // check if the family code has been generated 
          if (!htmlOutput.value) {
            _context.n = 5;
            break;
          }
          if (!(navigator.clipboard && navigator.clipboard.writeText)) {
            _context.n = 3;
            break;
          }
          _context.n = 2;
          return navigator.clipboard.writeText(htmlOutput.value);
        case 2:
          _context.n = 4;
          break;
        case 3:
          // Fallback to the deprecated method
          range = document.createRange();
          range.selectNode(htmlOutputDiv);
          selection = window.getSelection();
          selection.removeAllRanges();
          selection.addRange(range);
          document.execCommand('copy');
          selection.removeAllRanges();
        case 4:
          copyIcon.innerHTML = "copied";
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('famCode_id').value = htmlOutput.value;
          _context.n = 6;
          break;
        case 5:
          copyIcon.innerHTML = "copy";
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('famCode_id').value = "";
          alert('Please generate the family code first');
        case 6:
          _context.n = 8;
          break;
        case 7:
          _context.p = 7;
          _t = _context.v;
          console.error('Unable to copy the HTML output: ', _t);
        case 8:
          return _context.a(2);
      }
    }, _callee, null, [[1, 7]]);
  }));
  return function (_x) {
    return _ref.apply(this, arguments);
  };
}());

/***/ })

}]);
//# sourceMappingURL=familyCode.js.map