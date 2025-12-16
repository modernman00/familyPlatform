"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["/js/index"],{

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

            if (['spouseName', 'spouseMobile', 'spouseEmail', 'fatherMobile', 'fatherEmail', 'motherMobile', 'maidenName', 'motherEmail'].includes(post.name)) {
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
      var email = this.id('email_id').value;
      if (email.match(emailExp) === null) {
        this.id('email_error').innerHTML = msg;
        this.id('email_error').style.color = "red";
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
              // Add keyup event listener to clear errors for non-select inputs
              the_id.addEventListener('keyup', function () {
                if (value !== 'select') {
                  clearErrorForElement(name);
                }
              });
            } else {
              console.error("Element with ID '".concat(id, "' with post name '").concat(post.name, "' not found."));
            }

            // Add change event listener to clear error message
            the_id.addEventListener('change', function () {
              clearErrorForElement(name);
            });
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
          if (theData === null || theData === undefined || theData === "") {
            throw new Error("Element with ID '".concat(input[i], "_id' not found or is empty"));
          }
          var max = maxi[i];
          var error = _this3.id("".concat(input[i], "_error"));
          theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
          theData.addEventListener('keyup', function () {
            error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
            var help = _this3.id("".concat(input[i], "_help"));
            help.style.color = 'red';
            help.style.fontSize = '10px';
            error.style.color = 'red';
            setTimeout(function () {
              help.style.display = 'none';
            }, 5000);
          });
        };
        for (var i = 0; i < input.length; i++) {
          _loop2(i);
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
      firstInput = this.id(first + '_id');
      secondInput = this.id(second + '_id');
      secondInput.addEventListener('keyup', function () {
        error.innerHTML = secondInput.value !== firstInput.value ? 'Your passwords do not match' : "";
      });
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
      giver = this.id(giveInput);
      taker = this.id(takeInput);
      giver.addEventListener('keyup', function () {
        taker.value = giver.value;
      });
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
          alert('check');
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

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autoCompleter: function() { return /* binding */ autoCompleter; },
/* harmony export */   checkBox: function() { return /* binding */ checkBox; },
/* harmony export */   checkBox2: function() { return /* binding */ checkBox2; },
/* harmony export */   convertFormData: function() { return /* binding */ convertFormData; },
/* harmony export */   createAndAppendElement: function() { return /* binding */ createAndAppendElement; },
/* harmony export */   distinctValue: function() { return /* binding */ distinctValue; },
/* harmony export */   isChecked: function() { return /* binding */ isChecked; },
/* harmony export */   loaderIcon: function() { return /* binding */ loaderIcon; },
/* harmony export */   loaderIconBootstrap: function() { return /* binding */ loaderIconBootstrap; },
/* harmony export */   loaderIconBulma: function() { return /* binding */ loaderIconBulma; },
/* harmony export */   matchInput: function() { return /* binding */ matchInput; },
/* harmony export */   matchRegex: function() { return /* binding */ matchRegex; },
/* harmony export */   realTimeCheckLen: function() { return /* binding */ realTimeCheckLen; },
/* harmony export */   removeDiv: function() { return /* binding */ removeDiv; },
/* harmony export */   showResponse: function() { return /* binding */ showResponse; },
/* harmony export */   toSentenceCase: function() { return /* binding */ toSentenceCase; }
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
  newDiv.setAttribute('class', "field ".concat(setClass));
  var parentDiv = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(parent);
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
      alert('check');
      fn();
    } else if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(noId).checked) {
      alert('check No');
    }
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
/* harmony export */   addToNotificationTab: function() { return /* binding */ addToNotificationTab; },
/* harmony export */   increaseNotificationCount: function() { return /* binding */ increaseNotificationCount; }
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");




// const timeAgo = (x) => format(x)


// import { html } from './profilePage/html';

// Update notification badge
function updateNotificationBadge(change) {
  var badge = document.querySelector('.notification-badge');
  var count = parseInt(badge.textContent);
  count += change;
  if (count <= 0) {
    badge.style.display = 'none';
  } else {
    badge.textContent = count;
    badge.style.display = 'flex';
  }
}
var postAgoNotification = function postAgoNotification(date) {
  return "\n  <div class=\"notification_timeago w3-left w3-opacity\" datetime='".concat(date, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "\n  </div>");
};
// this is the notification htnl 
var notificationHTML = function notificationHTML(data) {
  // Map notification types to icon classes
  // Map type → { icon, colour }
  var iconMap = {
    friend_request: {
      icon: "fa-solid fa-user-plus",
      color: "text-primary"
    },
    // Blue
    like: {
      icon: "fa-solid fa-thumbs-up",
      color: "text-success"
    },
    // Green
    comment: {
      icon: "fa-solid fa-comment-dots",
      color: "text-info"
    },
    // Cyan
    Anniversary: {
      icon: "fa-solid fa-cake-candles",
      color: "text-warning"
    },
    // Gold
    Birthday: {
      icon: "fa-solid fa-cake-candles",
      color: "text-warning"
    },
    // Gold
    Wedding: {
      icon: "fa-solid fa-heart",
      color: "text-warning"
    },
    // Gold
    new_post: {
      icon: "fa-solid fa-file-lines",
      color: "text-purple"
    },
    // Custom purple
    House_Warming: {
      icon: "fa-solid fa-house",
      color: "text-orange"
    },
    // Orange
    Reunion: {
      icon: "fa-solid fa-people-group",
      color: "text-pink"
    },
    // Pink
    Party: {
      icon: "fa-solid fa-champagne-glasses",
      color: "text-danger"
    },
    // Red
    Meeting: {
      icon: "fa-solid fa-handshake",
      color: "text-teal"
    },
    // Teal
    default: {
      icon: "fa-solid fa-bell",
      color: "text-secondary"
    } // Grey
  };
  var _ref = iconMap[data.notification_type] || iconMap.default,
    icon = _ref.icon,
    color = _ref.color;
  var readOrUnread = data.notification_status === 'new' ? 'unread' : 'read';
  var sender_id = data.sender_id,
    notification_name = data.notification_name,
    notification_content = data.notification_content,
    created_at = data.created_at,
    no = data.no;

  // generate random numbers to make the notification unique

  var randomNumber = Math.floor(100 + Math.random() * 900);
  return "<a id = \"notificationBar".concat(sender_id).concat(randomNumber, "\" href=\"#linkNotification").concat(no, "\"  class=\"list-group-item list-group-item-action d-flex align-items-start notification_real_time ").concat(readOrUnread, " notification-item linkRequestCard\">\n\n    \n            <div class=\"notification-icon ").concat(color, "\">\n                <i class=\"").concat(icon, "\"></i></div>\n            <div class=\"notification-text\">\n                <strong>").concat(notification_name, "</strong><br>\n                <small>").concat(notification_content, "</small>\n                <div class=\"notification-time\"> ").concat(postAgoNotification(created_at), " </div>\n            </div>\n            <button class=\"notification-delete btn btn-sm btn-outline-secondary btn-light\" \n                 \" \n                    data-no=\"").concat(no, "\"\n                    data-is=\"").concat(sender_id, "\"\n                    type=\"submit\"\n                    id=\"deleteNotification").concat(sender_id).concat(randomNumber, "\"\n                    aria-label=\"Delete notification\">\n               <i class=\"fa-solid fa-trash\"></i>\n            </button>\n \n\n  </a>\n\n\n  ");
};

// CLICK FUNCTION ON THE NOTIFICATION BAR THAT TAKES ONE TO THE FRIEND REQUEST CARD

var increaseNotificationCount = function increaseNotificationCount() {
  var currentNotificationCount = parseInt(sessionStorage.getItem('notificationCount')) + 1;
  (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = currentNotificationCount;
};
var addToNotificationTab = function addToNotificationTab(data) {
  return (0,_shared__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab').insertAdjacentHTML('afterbegin', notificationHTML(data));
};
var yourId = localStorage.getItem('requesterId');
var famCode = localStorage.getItem('requesterFamCode');
var notificationURL = "/member/notifications/id/".concat(yourId, "/").concat(famCode);

// get all the notification and display them 
// they are already filtered by famCode and id 
// for the family request, connection is done by id
// for events -birthday etc, the connection is the famCode 
// so linked notification will be either where id matches or famcode matches

axios__WEBPACK_IMPORTED_MODULE_3__["default"].get(notificationURL).then(function (res) {
  // Extract the notifications from the response
  var data = res.data.message;
  if (data) {
    if (data.length > 0) {
      // Display the count of notifications
      var countBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
      countBadge.innerHTML = data.length;
      countBadge.style.display = 'flex';

      // Store the notification count in session storage
      sessionStorage.setItem('notificationCount', data.length);

      // Display each notification
      data.forEach(function (element) {
        addToNotificationTab(element);
      });
      // Update the timing of notifications
      var updateNotificationTiming = document.querySelectorAll(".notification_timeago");
      (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.render)(updateNotificationTiming);
    } else {
      var _countBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
      _countBadge.innerHTML = '0';
      _countBadge.style.display = 'none';
    }
  }
}).catch(function (error) {
  // Handle any errors that occur during the process
  (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
});

// delete a notification 

// delete notification 

// document.addEventListener('click', async (e) => {
//     const id = e.target.id;
//        log(id)
//     // if (!id.includes('deleteNotification')) return;

//     // const deleteBtn = id(id);
//     // const sender_id = deleteBtn.getAttribute('data-id');

//     // const url = `/removeNotification/${yourId}/${famCode}/${sender_id}`
//     // const response = axios.put(url)

//     // if (response.data.message === "Notification marked as read") {

//     //     // remove a html element with notificationBar after 2 mins 
//     //     qSel(`#${deleteBtn.id}`).closest('.notification_real_time')?.remove();

//     //     // reduce the notification count as you have deleted the notification

//     //     const newValues = parseInt(sessionStorage.getItem('notificationCount') - 1)
//     //     id('notification_count').innerHTML = newValues;
//     // } else {
//     //     msgException("Error removing notification" + " " + response.data.message);
//     // }
// })

var notificationBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notificationBtn');
var notificationDropdown = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notificationDropdown');
var markAllReadBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('markAllRead');
var notificationCount = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');

// Only add event listeners if notification elements exist on the page
if (notificationBtn && notificationDropdown) {
  // Toggle dropdown visibility
  notificationBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    notificationDropdown.classList.toggle('show');
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
      notificationDropdown.classList.remove('show');
    }
  });

  // Prevent dropdown from closing when clicking inside it
  notificationDropdown.addEventListener('click', function (e) {
    e.stopPropagation();
  });
}

// Mark all as read functionality (only if button exists)
if (markAllReadBtn && notificationCount) {
  markAllReadBtn.addEventListener('click', function () {
    var unreadItems = document.querySelectorAll('.notification-item.unread');
    unreadItems.forEach(function (item) {
      item.classList.remove('unread');
    });

    // Update notification count
    notificationCount.textContent = '0';
    notificationCount.style.display = 'none';
  });
}

/* run once, after the dropdown HTML is in the page */
var initDeleteOnce = function initDeleteOnce() {
  var tab = document.getElementById('notification_tab'); // static parent
  if (!tab) return;
  tab.addEventListener('click', function (e) {
    var btn = e.target.closest('button[id*="deleteNotification"]');
    if (!btn) return; // not a delete button → ignore

    e.stopPropagation(); // keep dropdown open
    var bannerId = btn.id.replace('deleteNotification', 'notificationBar');
    var no = btn.getAttribute('data-no');
    var url = "/removeNotification/".concat(no);
    axios__WEBPACK_IMPORTED_MODULE_3__["default"].put(url).then(function (response) {
      if (response.data.message === 'Notification marked as read') {
        var _document$getElementB;
        // remove a html element with notificationBar after 2 mins
        (_document$getElementB = document.getElementById(bannerId)) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.remove();

        // reduce the notification count as you have deleted the notification
        var currentCount = parseInt(sessionStorage.getItem('notificationCount')) - 1;
        var newValues = currentCount > 0 ? currentCount : 0;
        sessionStorage.setItem('notificationCount', newValues);
        var countBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
        countBadge.innerHTML = newValues;
        if (newValues <= 0) {
          countBadge.style.display = 'none';
        } else {
          countBadge.style.display = 'flex';
        }
      } else {
        (0,_shared__WEBPACK_IMPORTED_MODULE_1__.msgException)('Error removing notification' + ' ' + response.data.message);
      }
      // your counter routine
    });
  });
};

/* safe entry point */
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', initDeleteOnce) : initDeleteOnce();

///member/notifications

/***/ }),

/***/ "./resources/asset/js/index.js":
/*!*************************************!*\
  !*** ./resources/asset/js/index.js ***!
  \*************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _components_navbar__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/navbar */ "./resources/asset/js/components/navbar.js");




document.addEventListener('DOMContentLoaded', function () {
  var routeMap = {
    '/register': {
      module: function module() {
        return Promise.all(/*! import() | register */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("register")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/ */ "./resources/asset/js/components/register/index.js"));
      },
      hide: ['.signUp'] // Hide signup navbar
    },
    '/allMembers': {
      module: function module() {
        return Promise.all(/*! import() | all_members */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("all_members")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/allMembers/ */ "./resources/asset/js/components/allMembers/index.js"));
      },
      hide: ['.allMembersNav'] // Hide allMembers navbar
    },
    '/login': {
      module: function module() {
        return __webpack_require__.e(/*! import() | login */ "login").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/login.js */ "./resources/asset/js/components/acctMgt/login.js"));
      },
      hide: ['.login'] // Hide login navbar
    },
    '/lasu': {
      module: function module() {
        return __webpack_require__.e(/*! import() | lasu */ "lasu").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/admin.js */ "./resources/asset/js/components/acctMgt/admin.js"));
      },
      hide: ['.login'] // Same login module as /login
    },
    '/forgot': {
      module: function module() {
        return __webpack_require__.e(/*! import() | forgotPwd */ "forgotPwd").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/forgot.js */ "./resources/asset/js/components/acctMgt/forgot.js"));
      },
      hide: ['.signup_login'] // Hide login/signup navbar
    },
    '/code': {
      module: function module() {
        return __webpack_require__.e(/*! import() | code */ "code").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/code.js */ "./resources/asset/js/components/acctMgt/code.js"));
      },
      hide: ['.signup_login'] // Hide login/signup navbar
    },
    '/profilePage': {
      module: function module() {
        return Promise.all(/*! import() | profilePage */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("profilePage")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/ */ "./resources/asset/js/components/profilePage/index.js"));
      },
      hide: ['.profilePageNav'] // Hide profile page navbar
    },
    '/changePW': {
      module: function module() {
        return __webpack_require__.e(/*! import() | changePW */ "changePW").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/changePW.js */ "./resources/asset/js/components/acctMgt/changePW.js"));
      },
      hide: ['.login', '.signUp'] // Hide login and signup navbars
    },
    '/profilepage/img': {
      module: function module() {
        return Promise.all(/*! import() | img */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("img")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/imgViewer */ "./resources/asset/js/components/profilePage/imgViewer.js"));
      }
    },
    '/images': {
      module: function module() {
        return Promise.all(/*! import() | images */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("images")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/images */ "./resources/asset/js/components/images.js"));
      }
    },
    '/createFamilyCode': {
      module: function module() {
        return Promise.all(/*! import() | familyCode */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("familyCode")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/familyCode */ "./resources/asset/js/components/register/familyCode.js"));
      }
    },
    '/register/nextStep': {
      hide: ['.login', '.signUp'] // No module, just hide navbars
    },
    '/organogram': {
      module: function module() {
        return __webpack_require__.e(/*! import() | organogram */ "organogram").then(__webpack_require__.bind(__webpack_require__, /*! ./components/familyTree/index.js */ "./resources/asset/js/components/familyTree/index.js"));
      },
      hide: ['.familyTreeNav', '.profileNav'] // Hide family tree navbar
    },
    '/allMembers/getProfile': {
      // Module import commented out — placeholder for future logic
    },
    '/accountSetting': {
      module: function module() {
        return Promise.all(/*! import() | accountSetting */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("accountSetting")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/accountSetting */ "./resources/asset/js/components/accountSetting.js"));
      }
    }
  };
  try {
    var path = window.location.pathname;
    // Enhanced route matching that handles parameters
    var routeKey = Object.keys(routeMap).find(function (routePath) {
      // Exact match
      if (path === routePath) return true;

      // Match routes with parameters like /organogram/870016OLAWALE
      if (path.startsWith(routePath + '/')) return true;
      return false;
    });
    if (!routeKey) {
      throw new Error("Unhandled route: ".concat(path));
    }
    var route = routeMap[routeKey];
    if (!route) {
      throw new Error("Unhandled route: ".concat(path));
    }

    // Hide specified nav elements
    if (route.hide) {
      route.hide.forEach(function (selector) {
        var el = document.querySelector(selector);
        if (el) el.style.display = 'none';
      });
    }

    // Load module if defined
    if (route.module) {
      route.module().then(function (mod) {
        return mod.default;
      }).catch(function (err) {
        (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(err);
        throw new Error("Failed to load module for ".concat(path, ": ").concat(err.message));
      });
    }
  } catch (error) {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
    throw error;
  }
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
  var darkModeToggle = document.getElementById('darkModeToggle');
  var body = document.body;

  // Only initialize dark mode if the toggle exists on the page
  if (darkModeToggle) {
    // Check for saved dark mode preference
    if (localStorage.getItem('darkMode') === 'enabled') {
      body.classList.add('dark-mode');
      darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
    }
    darkModeToggle.addEventListener('click', function () {
      body.classList.toggle('dark-mode');
      if (body.classList.contains('dark-mode')) {
        localStorage.setItem('darkMode', 'enabled');
        darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
      } else {
        localStorage.setItem('darkMode', null);
        darkModeToggle.innerHTML = '<i class="bi bi-moon-fill"></i>';
      }
    });
  }
} catch (error) {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
}

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
/******/ var __webpack_exec__ = function(moduleId) { return __webpack_require__(__webpack_require__.s = moduleId); }
/******/ __webpack_require__.O(0, ["css/main","/js/vendor"], function() { return __webpack_exec__("./resources/asset/js/index.js"), __webpack_exec__("./resources/asset/scss/main.scss"); });
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=index.js.map