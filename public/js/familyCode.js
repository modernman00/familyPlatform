"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["familyCode"],{

/***/ "./resources/asset/js/components/global.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/global.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkElements: function() { return /* binding */ checkElements; },
/* harmony export */   checkManyElements: function() { return /* binding */ checkManyElements; },
/* harmony export */   date2String: function() { return /* binding */ date2String; },
/* harmony export */   fileUploadSizeValidation: function() { return /* binding */ fileUploadSizeValidation; },
/* harmony export */   formReset: function() { return /* binding */ formReset; },
/* harmony export */   hideElement: function() { return /* binding */ hideElement; },
/* harmony export */   id: function() { return /* binding */ id; },
/* harmony export */   idInnerHTML: function() { return /* binding */ idInnerHTML; },
/* harmony export */   idValue: function() { return /* binding */ idValue; },
/* harmony export */   initializeImageModal: function() { return /* binding */ initializeImageModal; },
/* harmony export */   log: function() { return /* binding */ log; },
/* harmony export */   manipulateAttribute: function() { return /* binding */ manipulateAttribute; },
/* harmony export */   msgException: function() { return /* binding */ msgException; },
/* harmony export */   qSel: function() { return /* binding */ qSel; },
/* harmony export */   qSelAll: function() { return /* binding */ qSelAll; },
/* harmony export */   qSelInnerHTML: function() { return /* binding */ qSelInnerHTML; },
/* harmony export */   qSelValue: function() { return /* binding */ qSelValue; },
/* harmony export */   showElement: function() { return /* binding */ showElement; },
/* harmony export */   showError: function() { return /* binding */ showError; },
/* harmony export */   showNotification: function() { return /* binding */ showNotification; },
/* harmony export */   warningSign: function() { return /* binding */ warningSign; },
/* harmony export */   write: function() { return /* binding */ write; }
/* harmony export */ });
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



var button = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("btnFamCode");
btnFamCode.addEventListener("click", function () {
  try {
    if ((0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('surname_id').value !== "") {
      var uniqueNumber = Date.now();
      var uniqueNumber1 = Math.ceil(Math.floor(Math.random() * uniqueNumber) / 10000000);
      var getSurname = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('surname_id').value;
      var firstFourLetters = getSurname.substring(0, 4);
      (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('createCode').value = "".concat(firstFourLetters.toUpperCase()).concat(uniqueNumber1);
      btnFamCode.disabled = true;
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("surname_error").innerHTML = error.messages;
  }
});

// Get references to the HTML output and the copy icon

var copyIcon = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('copyIcon');
var htmlOutputDiv = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('createFamCode');
var htmlOutput = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('createCode');
copyIcon.addEventListener('click', /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
    var range, selection, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          copyIcon.innerHTML = "";
          _context.prev = 1;
          e.preventDefault();

          // check if the family code has been generated 
          if (!htmlOutput.value) {
            _context.next = 5;
            break;
          }
          if (!(navigator.clipboard && navigator.clipboard.writeText)) {
            _context.next = 3;
            break;
          }
          _context.next = 2;
          return navigator.clipboard.writeText(htmlOutput.value);
        case 2:
          _context.next = 4;
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
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('famCode_id').value = htmlOutput.value;
          _context.next = 6;
          break;
        case 5:
          copyIcon.innerHTML = "copy";
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('famCode_id').value = "";
          alert('Please generate the family code first');
        case 6:
          _context.next = 8;
          break;
        case 7:
          _context.prev = 7;
          _t = _context["catch"](1);
          console.error('Unable to copy the HTML output: ', _t);
        case 8:
        case "end":
          return _context.stop();
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