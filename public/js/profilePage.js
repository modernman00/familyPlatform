(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["profilePage"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
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

            if (['spouse_name', 'spouse_mobile', 'spouse_email', 'father_mobile', 'father_email', 'mother_mobile', 'maiden_name', 'mother_email'].includes(post.name)) {
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
      var emailEl = this.id('email_id') || this.id('email');
      if (!emailEl) return;
      var email = emailEl.value;
      if (email.match(emailExp) === null) {
        var errEl = this.id('email_error');
        if (errEl) {
          errEl.innerHTML = msg;
          errEl.style.color = "red";
        }
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
              console.log("Adding listeners to element: ".concat(id));
              // Add keyup event listener to clear errors for non-select inputs
              the_id.addEventListener('keyup', function () {
                if (value !== 'select') {
                  clearErrorForElement(name);
                }
              });

              // Add change event listener to clear error message
              the_id.addEventListener('change', function () {
                clearErrorForElement(name);
              });
            } else {
              console.warn("Element with ID '".concat(id, "' with post name '").concat(post.name, "' not found."));
            }
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
          if (!theData) theData = _this3.id(input[i]);
          if (theData === null || theData === undefined || theData === "") {
            console.warn("Element with ID '".concat(input[i], "' not found or is empty"));
            return 1; // continue
          }
          var max = maxi[i];
          var error = _this3.id("".concat(input[i], "_error"));
          theData.maxLength = parseInt(max) + 1; // Fixed the parsing issue here
          theData.addEventListener('keyup', function () {
            if (error) {
              error.innerHTML = theData.value.length > max ? "You have reached the maximum limit" : "";
              error.style.color = 'red';
            }
            var help = _this3.id("".concat(input[i], "_help"));
            if (help) {
              help.style.color = 'red';
              help.style.fontSize = '10px';
              setTimeout(function () {
                help.style.display = 'none';
              }, 5000);
            }
          });
        };
        for (var i = 0; i < input.length; i++) {
          if (_loop2(i)) continue;
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
      firstInput = this.id(first + '_id') || this.id(first);
      secondInput = this.id(second + '_id') || this.id(second);
      if (firstInput && secondInput) {
        secondInput.addEventListener('keyup', function () {
          if (error) error.innerHTML = secondInput.value !== firstInput.value ? 'Your passwords do not match' : "";
        });
      }
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
      giver = this.id(giveInput) || this.id(giveInput.replace('_id', ''));
      taker = this.id(takeInput) || this.id(takeInput.replace('_id', ''));
      if (giver && taker) {
        giver.addEventListener('keyup', function () {
          taker.value = giver.value;
        });
      }
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

/***/ "./resources/asset/js/components/emojiPicker.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/emojiPicker.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "initEmojiPickerUX": function() { return /* binding */ initEmojiPickerUX; },
/* harmony export */   "showEmojiPicker": function() { return /* binding */ showEmojiPicker; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");
/* harmony import */ var emojibase_data_en_data_json__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! emojibase-data/en/data.json */ "./node_modules/emojibase-data/en/data.json");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");



var EMOJI_CACHE_KEY = 'recentEmojis'; // LocalStorage key for caching recent emojis

// 🟡 Filter emojis to only include smileys (based on Unicode range)
var smileys = emojibase_data_en_data_json__WEBPACK_IMPORTED_MODULE_2__.filter(function (e) {
  var code = parseInt(e.hexcode, 16); // Convert hexcode to decimal
  return code >= 0x1F600 && code <= 0x1F64F; // Emoticons block range
});

/**
 * Renders the filtered smiley emojis into the picker.
 * @param {string} emojiContainerId - The ID of the container element for the emoji buttons.
 * @param {string} emojiTargetDataClass - The data class attribute for the emoji target i.e data-emoji-target.
 */
var showEmojiPicker = function showEmojiPicker(emojiContainerId, emojiTargetDataClass) {
  // Render the filtered smiley emojis into the picker
  renderEmojiList(smileys, emojiContainerId, emojiTargetDataClass);
};

/**
 * Initialize UX behaviors like "click outside to close"
 * @param {string} toggleId - ID of the button that opens the picker
 * @param {string} containerId - ID of the picker container
 */
var initEmojiPickerUX = function initEmojiPickerUX(toggleId, containerId) {
  var toggle = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.id)(toggleId);
  var container = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.id)(containerId);
  if (!toggle || !container) return;
  document.addEventListener('click', function (e) {
    if (!container.classList.contains('d-none')) {
      if (!container.contains(e.target) && !toggle.contains(e.target)) {
        container.classList.add('d-none');
        toggle.setAttribute('aria-expanded', 'false');
      }
    }
  });
};
var renderEmojiList = function renderEmojiList(emojis, emojiContainerId, emojiTargetDataClass) {
  var emojiList = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.id)(emojiContainerId);
  if (!emojiList) return;
  emojiList.innerHTML = '';
  emojiList.classList.add('modern-emoji-picker');
  var form = emojiList.closest('form');
  var emojiTarget = form.querySelector("[".concat(emojiTargetDataClass, "]"));
  var cached = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];
  if (cached.length > 0) {
    var recentHeader = document.createElement('div');
    recentHeader.className = 'emoji-section-header';
    recentHeader.textContent = 'Recently Used';
    emojiList.appendChild(recentHeader);
    var recentGrid = document.createElement('div');
    recentGrid.className = 'emoji-grid';
    cached.forEach(function (emoji) {
      return renderEmojiButton(emoji, 'Recent', recentGrid, emojiTarget, emojiList);
    });
    emojiList.appendChild(recentGrid);
  }
  var allHeader = document.createElement('div');
  allHeader.className = 'emoji-section-header';
  allHeader.textContent = 'All Smileys';
  emojiList.appendChild(allHeader);
  var allGrid = document.createElement('div');
  allGrid.className = 'emoji-grid';
  emojis.slice(0, 100).forEach(function (_ref) {
    var emoji = _ref.emoji,
      label = _ref.label,
      skins = _ref.skins;
    renderEmojiButton(emoji, label, allGrid, emojiTarget, emojiList);
    if (skins) {
      skins.forEach(function (_ref2) {
        var skinEmoji = _ref2.emoji;
        renderEmojiButton(skinEmoji, "".concat(label, " (skin tone)"), allGrid, emojiTarget, emojiList);
      });
    }
  });
  emojiList.appendChild(allGrid);
};

// 🟡 Create and insert a single emoji button
var renderEmojiButton = function renderEmojiButton(char, label, emojiContainer, emojiTarget, pickerList) {
  var btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'modern-emoji-btn';
  btn.textContent = char;
  btn.setAttribute('aria-label', label);
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    if (emojiTarget) {
      var start = emojiTarget.selectionStart;
      var end = emojiTarget.selectionEnd;
      var text = emojiTarget.value;
      emojiTarget.value = text.substring(0, start) + char + text.substring(end);
      var newPos = start + char.length;
      emojiTarget.setSelectionRange(newPos, newPos);
      emojiTarget.focus();
      cacheEmoji(char);

      // Auto-dismiss the picker
      var pickerContainer = pickerList.parentElement;
      if (pickerContainer) {
        pickerContainer.classList.add('d-none');
        // Update aria state on the toggle button if found
        var form = pickerContainer.closest('form');
        if (form) {
          var toggle = form.querySelector('[aria-expanded="true"]');
          if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
      }
    }
  });
  emojiContainer.appendChild(btn);
};

// 🟡 Save emoji to recent cache in localStorage
var cacheEmoji = function cacheEmoji(char) {
  var recent = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];

  // Add emoji to front of list, remove duplicates, keep max 10
  var updated = [char].concat((0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(recent.filter(function (e) {
    return e !== char;
  }))).slice(0, 10);
  localStorage.setItem(EMOJI_CACHE_KEY, JSON.stringify(updated));
};

/***/ }),

/***/ "./resources/asset/js/components/fileUploadPreview.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/fileUploadPreview.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "clearSelectedPostFiles": function() { return /* binding */ clearSelectedPostFiles; },
/* harmony export */   "getSelectedPostFiles": function() { return /* binding */ getSelectedPostFiles; },
/* harmony export */   "imagePreview": function() { return /* binding */ imagePreview; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_2__);



var selectedFilesStore = [];
var getSelectedPostFiles = function getSelectedPostFiles() {
  return selectedFilesStore;
};
var clearSelectedPostFiles = function clearSelectedPostFiles() {
  selectedFilesStore = [];
};

/**
 * Handles image file selection and previews thumbnails
 * 
 * @param {string} fileInputId - The id of the hidden file input element
 * @param {string} previewListId - The id of the container where preview thumbnails are shown
 * @param {string} fileNamesDisplayId - The id of the text element where selected file names are displayed
 * @param {string} previewContainerId - The id of the wrapper element for image previews
 * @param {string} closePreviewBtnId - The id of the button to clear image previews if provided
 */
var imagePreview = function imagePreview(fileInputId, previewListId, fileNamesDisplayId, previewContainerId) {
  var closePreviewBtnId = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;
  var imageInput = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)(fileInputId); // Hidden file input for image uploads
  var previewContainer = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)(previewContainerId); // Wrapper for image previews
  var previewList = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)(previewListId); // Where preview thumbnails are shown
  var fileNamesDisplay = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)(fileNamesDisplayId); // Text display of selected 

  if (!imageInput || !previewContainer || !previewList) return;
  var accumulatedFiles = [];

  // Helper to update the UI and input files
  var _updatePreviews = function updatePreviews(files) {
    accumulatedFiles = files;
    selectedFilesStore = files;
    previewList.innerHTML = ''; // Clear previous previews

    if (files.length === 0) {
      previewContainer.classList.add('d-none');
      if (fileNamesDisplay) fileNamesDisplay.textContent = '';
      imageInput.value = ''; // Clear input if no files
      return;
    }

    // Create a new DataTransfer to update the file input
    var dataTransfer = new DataTransfer();
    files.forEach(function (file, index) {
      dataTransfer.items.add(file);

      // Create wrapper for image and remove button
      var wrapper = document.createElement('div');
      wrapper.className = 'position-relative d-inline-block';
      var img = document.createElement('img');
      var reader = new FileReader();
      reader.onload = function (e) {
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
      img.alt = 'Preview';
      img.className = 'img-thumbnail';
      img.style.width = '80px';
      img.style.height = '80px';
      img.style.objectFit = 'cover';
      img.style.borderRadius = '8px';

      // Create remove button
      var removeBtn = document.createElement('button');
      removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 p-0 rounded-circle d-flex align-items-center justify-content-center';
      removeBtn.style.width = '22px';
      removeBtn.style.height = '22px';
      removeBtn.style.transform = 'translate(30%, -30%)';
      removeBtn.innerHTML = '&times;';
      removeBtn.style.fontSize = '14px';
      removeBtn.style.lineHeight = '1';
      removeBtn.onclick = function (e) {
        e.preventDefault(); // Prevent form submission if inside form
        var newFiles = accumulatedFiles.filter(function (_, i) {
          return i !== index;
        });
        _updatePreviews(newFiles);
      };
      wrapper.appendChild(img);
      wrapper.appendChild(removeBtn);
      previewList.appendChild(wrapper);
    });

    // Update the file input with the new list of files
    imageInput.files = dataTransfer.files;

    // Show file names and reveal preview container
    if (fileNamesDisplay) {
      fileNamesDisplay.textContent = "".concat(files.length, " image").concat(files.length > 1 ? 's' : '', " selected: ") + files.map(function (f) {
        return f.name;
      }).join(', ');
    }
    previewContainer.classList.remove('d-none');
  };
  imageInput.addEventListener('change', function () {
    var selectedFiles = Array.from(imageInput.files || []);
    if (!selectedFiles.length) return;

    // Check for file size limit (10MB matching backend limit in FileUploader)
    var validFiles = selectedFiles.filter(function (file) {
      if (file.size > 10 * 1024 * 1024) {
        // 10MB limit
        sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
          icon: 'error',
          title: 'File Too Large',
          text: "File ".concat(file.name, " is too large. Maximum 10MB allowed per image."),
          timer: 3500,
          showConfirmButton: false
        });
        return false;
      }
      return true;
    });

    // Merge newly selected files with existing accumulated files (deduplicating by name and size)
    var existingIdentifiers = new Set(accumulatedFiles.map(function (f) {
      return "".concat(f.name, "_").concat(f.size);
    }));
    var newUniqueFiles = validFiles.filter(function (f) {
      return !existingIdentifiers.has("".concat(f.name, "_").concat(f.size));
    });
    var combinedFiles = [].concat((0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(accumulatedFiles), (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(newUniqueFiles));
    if (combinedFiles.length > 5) {
      sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
        icon: 'warning',
        title: 'Maximum 5 Images',
        text: 'You can upload up to 5 images per post. Only the first 5 images are kept.',
        timer: 3000,
        showConfirmButton: false
      });
    }
    var finalFiles = combinedFiles.slice(0, 5);
    _updatePreviews(finalFiles);
  });
  if (closePreviewBtnId) {
    var closePreviewBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)(closePreviewBtnId); // Button to clear image previews
    if (closePreviewBtn) {
      closePreviewBtn.addEventListener('click', function () {
        _updatePreviews([]); // Clear all
      });
    }
  }
};

/***/ }),

/***/ "./resources/asset/js/components/global.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/global.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "checkElements": function() { return /* binding */ checkElements; },
/* harmony export */   "checkManyElements": function() { return /* binding */ checkManyElements; },
/* harmony export */   "date2String": function() { return /* binding */ date2String; },
/* harmony export */   "deleteNotification": function() { return /* binding */ deleteNotification; },
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
  // display the success information for 10sec
  id("".concat(elementId)).style.display = "block"; // unblock the notification
  id("".concat(elementId)).classList.add(addClass); // add the success class
  id("".concat(elementId)).innerHTML = message; // error element
  id('loader').classList.remove('loader'); // remove loader

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

/***/ }),

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "autoCompleter": function() { return /* binding */ autoCompleter; },
/* harmony export */   "checkBox": function() { return /* binding */ checkBox; },
/* harmony export */   "checkBox2": function() { return /* binding */ checkBox2; },
/* harmony export */   "convertFormData": function() { return /* binding */ convertFormData; },
/* harmony export */   "createAndAppendElement": function() { return /* binding */ createAndAppendElement; },
/* harmony export */   "distinctValue": function() { return /* binding */ distinctValue; },
/* harmony export */   "isChecked": function() { return /* binding */ isChecked; },
/* harmony export */   "loaderIcon": function() { return /* binding */ loaderIcon; },
/* harmony export */   "loaderIconBootstrap": function() { return /* binding */ loaderIconBootstrap; },
/* harmony export */   "loaderIconBulma": function() { return /* binding */ loaderIconBulma; },
/* harmony export */   "matchInput": function() { return /* binding */ matchInput; },
/* harmony export */   "matchRegex": function() { return /* binding */ matchRegex; },
/* harmony export */   "realTimeCheckLen": function() { return /* binding */ realTimeCheckLen; },
/* harmony export */   "removeDiv": function() { return /* binding */ removeDiv; },
/* harmony export */   "showResponse": function() { return /* binding */ showResponse; },
/* harmony export */   "toSentenceCase": function() { return /* binding */ toSentenceCase; }
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
  if (setClass) newDiv.className = "field ".concat(setClass);else newDiv.className = "field ".concat(setId);
  var parentDiv = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(parent);
  if (!parentDiv) throw new Error("Parent element '".concat(parent, "' not found"));
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
      fn();
    } else if ((0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(noId).checked) {}
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

/***/ "./resources/asset/js/components/helper/images.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/helper/images.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "showImageFileUploadFn": function() { return /* binding */ showImageFileUploadFn; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


// use this in conjunction with the file 
var showImageFileUploadFn = function showImageFileUploadFn(uploadBtn, inputId, fileName) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(uploadBtn).addEventListener('click', function () {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputId).click();
  });
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputId).addEventListener('change', function () {
    var fileNames = Array.from(this.files).map(function (file) {
      return file.name;
    });
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(fileName).innerText = fileNames.join(', ');
  });
};

/***/ }),

/***/ "./resources/asset/js/components/navbar.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/navbar.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "addToNotificationTab": function() { return /* binding */ addToNotificationTab; },
/* harmony export */   "increaseNotificationCount": function() { return /* binding */ increaseNotificationCount; }
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

if (yourId && famCode && yourId !== 'null' && famCode !== 'null') {
  axios__WEBPACK_IMPORTED_MODULE_3__["default"].get(notificationURL).then(function (res) {
    // Extract the notifications from the response
    var data = res.data.message;
    if (data) {
      if (data.length > 0) {
        // Display the count of notifications
        var countBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
        if (countBadge) {
          countBadge.innerHTML = data.length;
          countBadge.style.display = 'flex';
        }

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
        if (_countBadge) {
          _countBadge.innerHTML = '0';
          _countBadge.style.display = 'none';
        }
      }
    }
  }).catch(function (error) {
    // Handle any errors that occur during the process
    (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  });
}

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

/***/ "./resources/asset/js/components/profilePage/allEvents.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/allEvents.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _fileUploadPreview__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../fileUploadPreview */ "./resources/asset/js/components/fileUploadPreview.js");








try {
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };

  // Reset the modal out of "edit" mode however it closes (submit, cancel,
  // backdrop click, ESC) — editPost() in feedComponent.js is what puts it
  // into edit mode by setting these same three things.
  var postModalEl = document.getElementById('postModal');
  if (postModalEl) {
    postModalEl.addEventListener('hidden.bs.modal', function () {
      var editPostNo = document.getElementById('editPostNo');
      var notice = document.getElementById('editPostNotice');
      var title = document.getElementById('postModalLabel');
      if (editPostNo) editPostNo.value = '';
      if (notice) notice.classList.add('d-none');
      if (title) title.textContent = 'Create Post';
    });
  }
  document.addEventListener('click', /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
      var elementId, _document$getElementB, formExtra, editPostNo, isEditing, formData, requesterFamCodeValue, selectedFiles, fileInput, filesToAppend, submitBtn, originalBtnText, _response$data, response, _response$data2, _closeBtn, newPostData, closeBtn, closePreview, _response$data3, _error$response, _error$response$data, friendRequestSection, _t, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            elementId = e.target.id; // SUBMIT THE NEW POST (from modal)
            if (!(elementId && elementId.includes("submitPost"))) {
              _context.next = 13;
              break;
            }
            e.preventDefault();
            formExtra = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('formPostMessageModal');
            if (formExtra) {
              _context.next = 1;
              break;
            }
            return _context.abrupt("return");
          case 1:
            // editPost() in feedComponent.js stamps this when reopening the modal
            // to edit an existing post instead of creating a new one.
            editPostNo = (_document$getElementB = document.getElementById('editPostNo')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.value;
            isEditing = !!editPostNo;
            if (!isEditing) {
              formData = new FormData(formExtra);
              requesterFamCodeValue = localStorage.getItem('requesterFamCode');
              formData.append('postFamCode', requesterFamCodeValue);

              // Explicitly ensure all accumulated image files are appended directly to formData
              selectedFiles = (0,_fileUploadPreview__WEBPACK_IMPORTED_MODULE_4__.getSelectedPostFiles)();
              fileInput = document.getElementById('imageUpload');
              filesToAppend = selectedFiles && selectedFiles.length > 0 ? selectedFiles : fileInput && fileInput.files ? Array.from(fileInput.files) : [];
              if (filesToAppend.length > 0) {
                formData.delete('post_img[]');
                formData.delete('post_img');
                filesToAppend.forEach(function (file) {
                  formData.append('post_img[]', file, file.name);
                });
              }
            }

            // Change button to spinner
            submitBtn = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(elementId);
            originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = "<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> ".concat(isEditing ? 'Saving...' : 'Posting...');
            submitBtn.disabled = true;
            _context.prev = 2;
            if (!isEditing) {
              _context.next = 4;
              break;
            }
            _context.next = 3;
            return axios__WEBPACK_IMPORTED_MODULE_5__["default"].put("/post/".concat(editPostNo), {
              postMessage: (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('postMessage').value
            }, options);
          case 3:
            _t = _context.sent;
            _context.next = 6;
            break;
          case 4:
            _context.next = 5;
            return axios__WEBPACK_IMPORTED_MODULE_5__["default"].post("/member/profilePage/post", formData, options);
          case 5:
            _t = _context.sent;
          case 6:
            response = _t;
            if (!((response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.status) === 'success' || (response === null || response === void 0 ? void 0 : response.status) === 200)) {
              _context.next = 8;
              break;
            }
            if (!isEditing) {
              _context.next = 7;
              break;
            }
            window.dispatchEvent(new CustomEvent('post-updated', {
              detail: {
                postNo: editPostNo,
                postMessage: (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('postMessage').value
              }
            }));
            _closeBtn = document.querySelector('#postModal .btn-close');
            if (_closeBtn) _closeBtn.click();
            formExtra.reset();
            sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Post updated successfully',
              showConfirmButton: false,
              timer: 3000
            });
            return _context.abrupt("return");
          case 7:
            newPostData = response !== null && response !== void 0 && (_response$data2 = response.data) !== null && _response$data2 !== void 0 && _response$data2.message && typeof response.data.message === 'object' ? response.data.message : null;
            window.dispatchEvent(new CustomEvent('post-created', {
              detail: newPostData
            }));
            closeBtn = document.querySelector('#postModal .btn-close');
            if (closeBtn) closeBtn.click();
            formExtra.reset();
            (0,_fileUploadPreview__WEBPACK_IMPORTED_MODULE_4__.clearSelectedPostFiles)();
            closePreview = document.getElementById('closeImagePreview');
            if (closePreview) closePreview.click();
            sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Post published successfully',
              showConfirmButton: false,
              timer: 3000
            });
            _context.next = 9;
            break;
          case 8:
            throw new Error((response === null || response === void 0 ? void 0 : (_response$data3 = response.data) === null || _response$data3 === void 0 ? void 0 : _response$data3.message) || 'Failed to publish post');
          case 9:
            _context.next = 11;
            break;
          case 10:
            _context.prev = 10;
            _t2 = _context["catch"](2);
            console.error("An error occurred:", _t2);
            sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
              icon: 'error',
              title: 'Oops...',
              text: (_t2 === null || _t2 === void 0 ? void 0 : (_error$response = _t2.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message) || _t2.message || 'There was an error processing your request. Please try again.'
            });
          case 11:
            _context.prev = 11;
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            return _context.finish(11);
          case 12:
            _context.next = 14;
            break;
          case 13:
            if (elementId && elementId.includes('deleteNotification')) {
              (0,_global__WEBPACK_IMPORTED_MODULE_2__.deleteNotification)(elementId);
            }
            // take you to the request card for approval or denial
            else if (e.target.classList.contains('linkRequestCard')) {
              friendRequestSection = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("".concat(e.target.getAttribute('data-id'), "_linkRequestCard"));
              if (friendRequestSection) {
                friendRequestSection.scrollIntoView({
                  behavior: "smooth"
                });
              }
            }
          case 14:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[2, 10, 11, 12]]);
    }));
    return function (_x) {
      return _ref.apply(this, arguments);
    };
  }());
} catch (e) {
  showError(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/comment.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/comment.js ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "appendNewComment": function() { return /* binding */ appendNewComment; },
/* harmony export */   "commentHTML": function() { return /* binding */ commentHTML; },
/* harmony export */   "showComment": function() { return /* binding */ showComment; }
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _showEmojiOnComment_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./showEmojiOnComment.js */ "./resources/asset/js/components/profilePage/showEmojiOnComment.js");




var reqId = localStorage.getItem('requesterId');
var commentHTML = function commentHTML(data, postId) {
  var _data$reactions$count, _data$reactions, _data$reactions$count2, _data$reactions2, _data$reactions2$coun;
  var profileImg = data.profileImg,
    fullName = data.fullName,
    date_created = data.date_created,
    img = data.img,
    comment = data.comment,
    comment_no = data.comment_no,
    id = data.id;
  var imgURL = profileImg || img;
  var image = imgURL ? "/resources/images/profile/".concat(imgURL) : "/resources/images/profile/avatarM.png";
  var counts = (_data$reactions$count = data === null || data === void 0 ? void 0 : (_data$reactions = data.reactions) === null || _data$reactions === void 0 ? void 0 : _data$reactions.counts) !== null && _data$reactions$count !== void 0 ? _data$reactions$count : {};
  var total = (_data$reactions$count2 = data === null || data === void 0 ? void 0 : (_data$reactions2 = data.reactions) === null || _data$reactions2 === void 0 ? void 0 : (_data$reactions2$coun = _data$reactions2.counts) === null || _data$reactions2$coun === void 0 ? void 0 : _data$reactions2$coun.totalReactions) !== null && _data$reactions$count2 !== void 0 ? _data$reactions$count2 : 0;
  return "<div class=\"d-flex mb-3 commentDiv align-items-start\" data-commentDiv-no=\"".concat(comment_no, "\" id=\"commentDiv").concat(comment_no, "\" name=\"commentDiv\">\n\n  <img src=\"").concat(image, "\" alt=\"Avatar\" class=\"rounded-circle me-2 commentImg\" width=\"32\" height=\"32\">\n\n  <div class=\"flex-grow-1\">\n    <div class=\"d-flex justify-content-between align-items-center\">\n      <small><strong>").concat((0,_shared__WEBPACK_IMPORTED_MODULE_1__.toSentenceCase)(fullName), "</strong></small>\n      <small class=\"text-muted commentTiming\" datetime=\"").concat(date_created, "\" title=\"").concat(date_created, "\">\n        ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), "\n      </small>\n    </div>\n\n    <div class=\"comment-text mb-2 p-3 shadow-sm\" style=\"background-color: var(--hover-color); border-radius: 18px; border-top-left-radius: 4px; display: inline-block;\">\n      <span style=\"font-size: 0.95rem; color: var(--text-color);\">").concat(comment, "</span>\n    </div>\n\n      <div class=\"d-flex reaction-preview-section align-items-center mb-2 gap-2\"> \n\n        <div class=\"reaction-preview\" id=\"reaction-preview-").concat(comment_no, "\">\n        ").concat((0,_showEmojiOnComment_js__WEBPACK_IMPORTED_MODULE_3__.renderTopReactions)(counts, comment_no), "\n        </div>\n\n         <div class=\"reaction-summary\" data-comment-no=\"").concat(comment_no, "\" role=\"tooltip\" id=\"reaction-summary-").concat(comment_no, "\" style=\"display:none;\">\n        </div>\n\n      </div>\n\n      <div class=\"comment-actions d-flex gap-3\">         \n                <div class=\"reaction-bar\"  id=\"reaction-bar-").concat(comment_no, "\">\n\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Like\" id=\"reaction-option-like-").concat(comment_no, "\" data-reaction=\"like\" data-label=\"likes\"> \uD83D\uDC4D </div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Love\" id=\"reaction-option-love-").concat(comment_no, "\" data-reaction=\"love\" data-label=\"love\">\u2764\uFE0F</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Haha\" id=\"reaction-option-haha-").concat(comment_no, "\" data-reaction=\"haha\" data-label=\"haha\">\uD83D\uDE04</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Wow\" id=\"reaction-option-wow-").concat(comment_no, "\" data-reaction=\"wow\" data-label=\"wow\">\uD83D\uDE2E</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Sad\" id=\"reaction-option-sad-").concat(comment_no, "\" data-reaction=\"sad\" data-label=\"sad\">\uD83D\uDE22</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Angry\" id=\"reaction-option-angry-").concat(comment_no, "\"\n                     data-reaction=\"angry\" data-label=\"angry\">\uD83D\uDE20</div>\n                </div>\n\n                <div class=\"reaction-button like-button-").concat(comment_no, "\" id=\"like-button-").concat(comment_no, "\" data-comment-no=\"").concat(comment_no, "\">\n                    <i class=\"bi bi-hand-thumbs-up reaction-icon\" id=\"like-icon-").concat(comment_no, "\"></i>\n                    <span>Like</span>\n                     <div class=\"reaction-count\" id=\"like-count-").concat(comment_no, "\">").concat(total, "</div>\n                   \n                </div>\n\n                ").concat(reqId == id || reqId == postId ? "<button class=\"btn btn-sm btn-icon text-danger\" id=\"removeComment(".concat(comment_no, ")\" title=\"Remove\">\n                    <i class=\"bi bi-trash\" id=\"removeCommentIcon").concat(comment_no, "\"></i>\n                    </button>") : '', "        \n      </div>\n  </div>\n</div><hr>");
};

// i need the postid to use to show the delete button 
var showComment = function showComment(comment, postId) {
  if (!comment) {
    return "<div id=\"comment\" name=\"commentDiv\"></div>";
  }
  // only run if there is comment
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
    throw new Error('commetContainer div not found in the DOM');
  }
  var commentHtml = commentHTML(commentData);
  commentContainer.insertAdjacentHTML('beforeend', commentHtml);
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/createEvent.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/createEvent.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_8__);












var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_4__["default"](formInputArr);
var displayNone = function displayNone() {
  var _window$bootstrap, _window$bootstrap$Mod;
  var modal = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('id_event_modal') || (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('createEventModal');
  if (!modal) return;

  // This page loads Bootstrap 5, which has no jQuery .modal() plugin — setting
  // style.display='none' directly skips Bootstrap's own hide lifecycle and leaves
  // the .modal-backdrop overlay + body's modal-open/overflow:hidden stuck behind,
  // which is what made the page look frozen after submitting.
  var instance = (_window$bootstrap = window.bootstrap) === null || _window$bootstrap === void 0 ? void 0 : (_window$bootstrap$Mod = _window$bootstrap.Modal) === null || _window$bootstrap$Mod === void 0 ? void 0 : _window$bootstrap$Mod.getInstance(modal);
  if (instance) {
    instance.hide();
  } else {
    var closeBtn = modal.querySelector('.btn-close, [data-bs-dismiss="modal"]');
    if (closeBtn) closeBtn.click();
  }
};
var cancelBtn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('cancelModal');
if (cancelBtn) {
  cancelBtn.addEventListener('click', displayNone);
}

// Reset the modal out of "edit" mode however it closes (submit, cancel,
// backdrop click, ESC) — editEvent() in sidebarComponents.js is what puts it
// into edit mode by setting these same fields.
var createEventModalEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('createEventModal');
if (createEventModalEl) {
  createEventModalEl.addEventListener('hidden.bs.modal', function () {
    var editEventNo = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('editEventNo');
    var notice = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('editEventNotice');
    var title = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('createEventModalLabel');
    var submitBtnEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('submitEventModal');
    if (editEventNo) editEventNo.value = '';
    if (notice) notice.classList.add('d-none');
    if (title) title.textContent = 'Create Event';
    if (submitBtnEl) submitBtnEl.textContent = 'Create Event';
    var eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('eventModalForm');
    if (eventForm) eventForm.reset();
  });
}

/**
 * Filters events by family code (famCode) to ensure only relevant events are shown
 * @param {Object} event - The event data object
 * @returns {boolean} - Returns true if event is linked to the family code
 */

var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};

// Mirrors rightColumn.blade.php's server-side "l jS \of F Y" format (e.g. "Wednesday 25th of August 2026")
var formatEventDate = function formatEventDate(dateStr) {
  if (!dateStr) return '';
  var date = new Date("".concat(dateStr, "T00:00:00"));
  if (isNaN(date)) return dateStr;
  var day = date.getDate();
  var suffix = day % 10 === 1 && day !== 11 ? 'st' : day % 10 === 2 && day !== 12 ? 'nd' : day % 10 === 3 && day !== 13 ? 'rd' : 'th';
  var weekday = date.toLocaleDateString('en-GB', {
    weekday: 'long'
  });
  var month = date.toLocaleDateString('en-GB', {
    month: 'long'
  });
  return "".concat(weekday, " ").concat(day).concat(suffix, " of ").concat(month, " ").concat(date.getFullYear());
};

// Mirrors rightColumn.blade.php's dateDifferenceInt()/number2word() -> "Today"/"Tomorrow"/"in N Days"
var dateDifferenceLabel = function dateDifferenceLabel(dateStr) {
  if (!dateStr) return '';
  var today = new Date();
  today.setHours(0, 0, 0, 0);
  var target = new Date("".concat(dateStr, "T00:00:00"));
  if (isNaN(target)) return '';
  var diffDays = Math.round((target - today) / 86400000);
  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Tomorrow';
  return "in ".concat(diffDays, " Days");
};

/**
 * @function process
 * @description Processes the event modal form data to create a new event
 * @param {Event} e - The event object
 * @example
 * const eventForm = id('eventModalForm');
 * eventForm.addEventListener('submit', process);
 */
var process = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee(e) {
    var _id, _eventResponse$data, errEl, _formInput, _formInputArr, _formData, eventForm, eventFormEntries, editEventNo, payload, _yield$Promise$all, _yield$Promise$all2, eventResponse, notificationResponse, _ref2, notificationNo, newEventNo, _error$response, _error$response$data, userMessage, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          e.preventDefault();
          errEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('error');
          if (errEl) errEl.innerHTML = "";
          _formInput = document.querySelectorAll('.eventModalForm');
          _formInputArr = Array.from(_formInput);
          _formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_4__["default"](_formInputArr);
          _formData.massValidate();
          if (!(_formData.error && _formData.error.length > 0)) {
            _context.next = 1;
            break;
          }
          sweetalert2__WEBPACK_IMPORTED_MODULE_8___default().fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'The form cannot be submitted. Please check the errors',
            confirmButtonColor: '#3085d6'
          });
          _formData.clearError();
          return _context.abrupt("return");
        case 1:
          // get the form data
          eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('eventModalForm');
          eventFormEntries = new FormData(eventForm); // editEvent() in sidebarComponents.js stamps this when reopening the modal
          // to edit an existing event instead of creating a new one.
          editEventNo = (_id = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('editEventNo')) === null || _id === void 0 ? void 0 : _id.value;
          if (!editEventNo) {
            _context.next = 3;
            break;
          }
          payload = {
            eventName: eventFormEntries.get('eventName') || '',
            eventDate: eventFormEntries.get('eventDate') || '',
            eventType: eventFormEntries.get('eventType') || '',
            eventDescription: eventFormEntries.get('eventDescription') || '',
            eventFrequency: eventFormEntries.get('eventFrequency') || ''
          };
          _context.next = 2;
          return axios__WEBPACK_IMPORTED_MODULE_9__["default"].put("/member/profilePage/event/".concat(editEventNo), payload, options);
        case 2:
          window.dispatchEvent(new CustomEvent('event-updated', {
            detail: {
              no: editEventNo,
              eventName: payload.eventName,
              eventDate: formatEventDate(payload.eventDate),
              eventType: payload.eventType,
              dateDifference: dateDifferenceLabel(payload.eventDate)
            }
          }));
          displayNone();
          sweetalert2__WEBPACK_IMPORTED_MODULE_8___default().fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Event updated successfully',
            showConfirmButton: false,
            timer: 3000
          });
          return _context.abrupt("return");
        case 3:
          _context.next = 4;
          return Promise.all([axios__WEBPACK_IMPORTED_MODULE_9__["default"].post("/member/profilePage/event", eventFormEntries, options), axios__WEBPACK_IMPORTED_MODULE_9__["default"].post('/member/notification/event', eventFormEntries, options)]);
        case 4:
          _yield$Promise$all = _context.sent;
          _yield$Promise$all2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_yield$Promise$all, 2);
          eventResponse = _yield$Promise$all2[0];
          notificationResponse = _yield$Promise$all2[1];
          // Extract and get notificationNo from the responses
          _ref2 = notificationResponse.data || {}, notificationNo = _ref2.message; // update all members of similar famcode on their UIs using Pusher
          if (notificationNo) {
            axios__WEBPACK_IMPORTED_MODULE_9__["default"].get("/member/notification/event?notificationNo=".concat(notificationNo));
          }

          // The sidebar's upcomingEvents component only ever gets seeded once, from the
          // page's initial server render (see sidebarComponents.js) — nothing was telling
          // it about events created afterward, so they only showed up on a full reload.
          // Dispatch the new event so it can prepend it immediately, mirroring the shape
          // rightColumn.blade.php builds server-side ({no, eventName, eventDate, eventType, dateDifference}).
          newEventNo = eventResponse === null || eventResponse === void 0 ? void 0 : (_eventResponse$data = eventResponse.data) === null || _eventResponse$data === void 0 ? void 0 : _eventResponse$data.token;
          if (newEventNo) {
            window.dispatchEvent(new CustomEvent('event-created', {
              detail: {
                no: newEventNo,
                eventName: eventFormEntries.get('eventName') || '',
                eventDate: formatEventDate(eventFormEntries.get('eventDate')),
                eventType: eventFormEntries.get('eventType') || '',
                dateDifference: dateDifferenceLabel(eventFormEntries.get('eventDate'))
              }
            }));
          }

          // close the modal
          displayNone();
          sweetalert2__WEBPACK_IMPORTED_MODULE_8___default().fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Event created successfully',
            showConfirmButton: false,
            timer: 3000
          });
          _context.next = 6;
          break;
        case 5:
          _context.prev = 5;
          _t = _context["catch"](0);
          // showError() writes into the modal's own #error <p> instead of firing a Swal
          // when that element exists (it does, in this modal), so real submission
          // failures were going unnoticed. Show a Swal directly instead, same as the
          // validation-error path above.
          userMessage = (_t === null || _t === void 0 ? void 0 : (_error$response = _t.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message) || (_t === null || _t === void 0 ? void 0 : _t.message) || 'There was an error creating your event. Please try again.';
          sweetalert2__WEBPACK_IMPORTED_MODULE_8___default().fire({
            icon: 'error',
            title: 'Oops...',
            text: userMessage,
            confirmButtonColor: '#3085d6'
          });
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.log)(_t);
        case 6:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 5]]);
  }));
  return function process(_x) {
    return _ref.apply(this, arguments);
  };
}();
var submitBtn = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('submitEventModal');
if (submitBtn) {
  submitBtn.addEventListener('click', process);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/editProfile.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/editProfile.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_3__);




var btn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('editProfileBtnModal');
if (btn) {
  btn.addEventListener('click', /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
      var _error$response, _error$response$data, _t;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            e.preventDefault();
            _context.prev = 1;
            _context.next = 2;
            return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.postFormData)('/member/profilePage/editProfile', 'editProfileFormModal', '/profilePage', 'bootstrap');
          case 2:
            _context.next = 4;
            break;
          case 3:
            _context.prev = 3;
            _t = _context["catch"](1);
            console.error('Edit Profile Submit Error:', _t);
            sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
              icon: 'error',
              title: 'Error',
              text: (_t === null || _t === void 0 ? void 0 : (_error$response = _t.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message) || 'Failed to update profile.',
              confirmButtonColor: '#3085d6'
            });
          case 4:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[1, 3]]);
    }));
    return function (_x) {
      return _ref.apply(this, arguments);
    };
  }());
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/engagement.js":
/*!*****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/engagement.js ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "handlePollVote": function() { return /* binding */ handlePollVote; },
/* harmony export */   "handleReaction": function() { return /* binding */ handleReaction; },
/* harmony export */   "initEngagementListeners": function() { return /* binding */ initEngagementListeners; },
/* harmony export */   "initMemories": function() { return /* binding */ initMemories; },
/* harmony export */   "loadMemories": function() { return /* binding */ loadMemories; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");


var handleReaction = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(postNo, reactionType) {
    var formData, response, data, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          formData = new FormData();
          formData.append('post_no', postNo);
          formData.append('reaction_type', reactionType);
          _context.next = 1;
          return fetch('/api/engagement/react', {
            method: 'POST',
            body: formData
          });
        case 1:
          response = _context.sent;
          _context.next = 2;
          return response.json();
        case 2:
          data = _context.sent;
          return _context.abrupt("return", data);
        case 3:
          _context.prev = 3;
          _t = _context["catch"](0);
          console.error('Reaction error:', _t);
          return _context.abrupt("return", null);
        case 4:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 3]]);
  }));
  return function handleReaction(_x, _x2) {
    return _ref.apply(this, arguments);
  };
}();
var handlePollVote = /*#__PURE__*/function () {
  var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(optionId) {
    var formData, response, data, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          formData = new FormData();
          formData.append('option_id', optionId);
          _context2.next = 1;
          return fetch('/api/engagement/vote', {
            method: 'POST',
            body: formData
          });
        case 1:
          response = _context2.sent;
          _context2.next = 2;
          return response.json();
        case 2:
          data = _context2.sent;
          return _context2.abrupt("return", data);
        case 3:
          _context2.prev = 3;
          _t2 = _context2["catch"](0);
          console.error('Vote error:', _t2);
          return _context2.abrupt("return", null);
        case 4:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 3]]);
  }));
  return function handlePollVote(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
var loadMemories = /*#__PURE__*/function () {
  var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3() {
    var response, data, _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.prev = 0;
          _context3.next = 1;
          return fetch('/api/engagement/memories');
        case 1:
          response = _context3.sent;
          _context3.next = 2;
          return response.json();
        case 2:
          data = _context3.sent;
          return _context3.abrupt("return", (data === null || data === void 0 ? void 0 : data.data) || []);
        case 3:
          _context3.prev = 3;
          _t3 = _context3["catch"](0);
          console.error('Memory load error:', _t3);
          return _context3.abrupt("return", []);
        case 4:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[0, 3]]);
  }));
  return function loadMemories() {
    return _ref3.apply(this, arguments);
  };
}();

var initEngagementListeners = function initEngagementListeners() {
  document.addEventListener('click', /*#__PURE__*/function () {
    var _ref4 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee4(e) {
      var btn, optionId, res, _btn, postNo, reaction, _res;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context4) {
        while (1) switch (_context4.prev = _context4.next) {
          case 0:
            if (!e.target.closest('.poll-option-btn')) {
              _context4.next = 2;
              break;
            }
            btn = e.target.closest('.poll-option-btn');
            optionId = btn.getAttribute('data-option-id');
            if (!optionId) {
              _context4.next = 2;
              break;
            }
            btn.disabled = true;
            _context4.next = 1;
            return handlePollVote(optionId);
          case 1:
            res = _context4.sent;
            if ((res === null || res === void 0 ? void 0 : res.status) === 'success') {
              // Trigger a reload of the post or re-fetch to show new bars
              window.dispatchEvent(new Event('refresh-feed'));
              // For immediate visual feedback, just reload page for now or wait for pusher
              window.location.reload();
            }
            btn.disabled = false;
          case 2:
            if (!e.target.closest('.reaction-btn')) {
              _context4.next = 4;
              break;
            }
            _btn = e.target.closest('.reaction-btn');
            postNo = _btn.getAttribute('data-post-no');
            reaction = _btn.getAttribute('data-reaction');
            if (!(postNo && reaction)) {
              _context4.next = 4;
              break;
            }
            _context4.next = 3;
            return handleReaction(postNo, reaction);
          case 3:
            _res = _context4.sent;
            if ((_res === null || _res === void 0 ? void 0 : _res.status) === 'success') {
              // Trigger a reload of the post or re-fetch to show new bars
              window.location.reload();
            }
          case 4:
          case "end":
            return _context4.stop();
        }
      }, _callee4);
    }));
    return function (_x4) {
      return _ref4.apply(this, arguments);
    };
  }());
};
var initMemories = /*#__PURE__*/function () {
  var _ref5 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee5() {
    var container, content, memories, htmlStr, _t4;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context5) {
      while (1) switch (_context5.prev = _context5.next) {
        case 0:
          container = document.getElementById('memories-container');
          content = document.getElementById('memories-content');
          if (!(!container || !content)) {
            _context5.next = 1;
            break;
          }
          return _context5.abrupt("return");
        case 1:
          _context5.prev = 1;
          _context5.next = 2;
          return loadMemories();
        case 2:
          memories = _context5.sent;
          if (memories && memories.length > 0) {
            container.style.display = 'block';
            htmlStr = '';
            memories.forEach(function (memory) {
              // Pass an empty array for comments to the html function since memories don't eager load comments initially
              htmlStr += (0,_html__WEBPACK_IMPORTED_MODULE_2__.html)(memory, []);
            });
            content.innerHTML = htmlStr;
          }
          _context5.next = 4;
          break;
        case 3:
          _context5.prev = 3;
          _t4 = _context5["catch"](1);
          console.error('Failed to load memories:', _t4);
        case 4:
        case "end":
          return _context5.stop();
      }
    }, _callee5, null, [[1, 3]]);
  }));
  return function initMemories() {
    return _ref5.apply(this, arguments);
  };
}();

/***/ }),

/***/ "./resources/asset/js/components/profilePage/eventHTML.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/eventHTML.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "eventHtml": function() { return /* binding */ eventHtml; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
"use static";


var eventHtml = function eventHtml(data) {
  return "<p class='eventInfo'>\n            <strong>RSVP: </strong> ".concat(data.sender_name, "</p>\n            <p class='eventInfo'><strong>Event: </strong>").concat(data.notification_name, "</p>\n            <p class='eventInfo'><strong>Date: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.date2String)(data.notification_date), " </p>\n            <p class='eventInfo'><strong>Type: </strong>").concat(data.notification_type, "</p>\n            <p class='eventInfo'><strong>Description: </strong> ").concat(data.notification_content, "</p>\n            <input type='hidden' name='event_no' id='event").concat(data.no, "' value='").concat(data.no, "'>\n\n            \n           <hr>");

  //                        <button 
  //     type="button" 
  //     id="coming${data.data.no}"
  //     class="w3-button w3-tiny w3-theme-d2 w3-margin-bottom">
  //       <em class="fa fa-comment"></em> 
  //         Coming 
  //     </button>
  // 
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/feedComponent.js":
/*!********************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/feedComponent.js ***!
  \********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "profileFeed": function() { return /* binding */ profileFeed; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_6__);



function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_1__["default"])(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }





function profileFeed() {
  return {
    posts: [],
    isLoading: true,
    errorMessage: '',
    lightboxOpen: false,
    lightboxImages: [],
    lightboxIndex: 0,
    currentUserId: localStorage.getItem('requesterId') || '',
    currentFamCode: localStorage.getItem('requesterFamCode') || '',
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
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
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
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee2() {
        var _response$data, _response$data$messag, response, rawPosts, _t;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _this2.isLoading = true;
              _this2.errorMessage = '';
              _context2.prev = 1;
              _context2.next = 2;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].get('/post/getAllPostCommentByFamCode');
            case 2:
              response = _context2.sent;
              rawPosts = response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : (_response$data$messag = _response$data.message) === null || _response$data$messag === void 0 ? void 0 : _response$data$messag.message;
              if (Array.isArray(rawPosts)) {
                _this2.posts = rawPosts.map(function (p) {
                  return _this2.normalizePost(p);
                });
              } else {
                _this2.posts = [];
              }
              _context2.next = 4;
              break;
            case 3:
              _context2.prev = 3;
              _t = _context2["catch"](1);
              console.error('Failed to load posts:', _t);
              _this2.errorMessage = 'Unable to load family posts. Please refresh or try again later.';
            case 4:
              _context2.prev = 4;
              _this2.isLoading = false;
              return _context2.finish(4);
            case 5:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[1, 3, 4, 5]]);
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
        return k.startsWith('post_img') && p[k] !== null && p[k] !== '';
      }).map(function (k) {
        return p[k];
      });
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      try {
        return (0,timeago_js__WEBPACK_IMPORTED_MODULE_5__.format)(dateStr);
      } catch (e) {
        return dateStr;
      }
    },
    toggleCommentForm(postNo) {
      this.activeCommentForms[postNo] = !this.activeCommentForms[postNo];
    },
    submitComment(postNo) {
      var _this4 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee3() {
        var commentText, formData, _response$data2, response, _err$response, _err$response$data, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context3) {
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
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].post('/postCommentProfile', formData, {
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
              _t2 = _context3["catch"](2);
              console.error('Failed to submit comment:', _t2);
              sweetalert2__WEBPACK_IMPORTED_MODULE_6___default().fire({
                icon: 'error',
                title: 'Submission Failed',
                text: (_t2 === null || _t2 === void 0 ? void 0 : (_err$response = _t2.response) === null || _err$response === void 0 ? void 0 : (_err$response$data = _err$response.data) === null || _err$response$data === void 0 ? void 0 : _err$response$data.message) || 'Failed to submit comment.',
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
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee4() {
        var result, _err$response2, _err$response2$data, _t3;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              _context4.next = 1;
              return sweetalert2__WEBPACK_IMPORTED_MODULE_6___default().fire({
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
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"]["delete"]("/post/".concat(postNo), _this5.csrfOptions);
            case 3:
              _this5.posts = _this5.posts.filter(function (p) {
                return String(p.post_no) !== String(postNo);
              });
              _context4.next = 5;
              break;
            case 4:
              _context4.prev = 4;
              _t3 = _context4["catch"](2);
              console.error('Failed to delete post:', _t3);
              sweetalert2__WEBPACK_IMPORTED_MODULE_6___default().fire({
                icon: 'error',
                title: 'Delete Failed',
                text: (_t3 === null || _t3 === void 0 ? void 0 : (_err$response2 = _t3.response) === null || _err$response2 === void 0 ? void 0 : (_err$response2$data = _err$response2.data) === null || _err$response2$data === void 0 ? void 0 : _err$response2$data.message) || 'Failed to delete post.',
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
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee5() {
        var text, _iterator, _step, post, comment, _err$response3, _err$response3$data, _t4, _t5;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context5) {
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
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].put("/comment/".concat(commentNo), {
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
              _t4 = _context5["catch"](3);
              _iterator.e(_t4);
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
              _t5 = _context5["catch"](1);
              console.error('Failed to update comment:', _t5);
              sweetalert2__WEBPACK_IMPORTED_MODULE_6___default().fire({
                icon: 'error',
                title: 'Update Failed',
                text: (_t5 === null || _t5 === void 0 ? void 0 : (_err$response3 = _t5.response) === null || _err$response3 === void 0 ? void 0 : (_err$response3$data = _err$response3.data) === null || _err$response3$data === void 0 ? void 0 : _err$response3$data.message) || 'Failed to update comment.',
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
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee6() {
        var result, post, _err$response4, _err$response4$data, _t6;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              _context6.next = 1;
              return sweetalert2__WEBPACK_IMPORTED_MODULE_6___default().fire({
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
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"]["delete"]("/comment/".concat(commentNo), _this7.csrfOptions);
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
              _t6 = _context6["catch"](2);
              console.error('Failed to delete comment:', _t6);
              sweetalert2__WEBPACK_IMPORTED_MODULE_6___default().fire({
                icon: 'error',
                title: 'Delete Failed',
                text: (_t6 === null || _t6 === void 0 ? void 0 : (_err$response4 = _t6.response) === null || _err$response4 === void 0 ? void 0 : (_err$response4$data = _err$response4.data) === null || _err$response4$data === void 0 ? void 0 : _err$response4$data.message) || 'Failed to delete comment.',
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
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee7() {
        var post, _t7;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context7) {
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
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].put('/profileCard/postLikes?postNo=' + postNo);
            case 3:
              _context7.next = 5;
              break;
            case 4:
              _context7.prev = 4;
              _t7 = _context7["catch"](2);
              console.error('Failed to like post:', _t7);
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
      var _this1 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee8() {
        var post, comment, prevReactions, prevTotal, isSame, wasReaction, _res$data, _res$data$message, _res$data$message$cou, formData, res, _serverCounts$totalRe, serverCounts, _t8;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context8) {
          while (1) switch (_context8.prev = _context8.next) {
            case 0:
              post = _this1.posts.find(function (p) {
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
              _this1.activeCommentReactionBars[commentNo] = false;
              _context8.prev = 3;
              formData = new FormData();
              formData.append('comment_no', commentNo);
              formData.append('reaction', reactionType);
              _context8.next = 4;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].post('/api/reactions/add', formData, {
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
              _t8 = _context8["catch"](3);
              // Revert optimistic update on failure
              comment.reactions = prevReactions;
              comment.totalReactions = prevTotal;
              comment.userReaction = wasReaction;
              console.error('Comment reaction failed:', _t8);
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
        var _ref2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref, 2),
          k = _ref2[0],
          v = _ref2[1];
        return !['comment_no', 'total', 'totalReactions'].includes(k) && Number(v) > 0;
      }).sort(function (_ref3, _ref4) {
        var _ref5 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref3, 2),
          a = _ref5[1];
        var _ref6 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref4, 2),
          b = _ref6[1];
        return Number(b) - Number(a);
      }).slice(0, 3).map(function (_ref7) {
        var _map$label;
        var _ref8 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref7, 2),
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
      var _this10 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee9() {
        var post, previousReaction, formData, _t9;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context9) {
          while (1) switch (_context9.prev = _context9.next) {
            case 0:
              post = _this10.posts.find(function (p) {
                return p.post_no === postNo;
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
              _this10.activeReactionBars[postNo] = false;
              _context9.prev = 2;
              formData = new FormData();
              formData.append('post_no', postNo);
              formData.append('reaction', reactionType);
              _context9.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].put('/profileCard/postLikes?postNo=' + postNo);
            case 3:
              _context9.next = 5;
              break;
            case 4:
              _context9.prev = 4;
              _t9 = _context9["catch"](2);
              console.error('Failed to record post reaction:', _t9);
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
      var _this11 = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee0() {
        var post, _response$data3, response, _t0;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context0) {
          while (1) switch (_context0.prev = _context0.next) {
            case 0:
              post = _this11.posts.find(function (p) {
                return p.post_no === postNo;
              });
              if (post !== null && post !== void 0 && post.poll) {
                _context0.next = 1;
                break;
              }
              return _context0.abrupt("return");
            case 1:
              _context0.prev = 1;
              _context0.next = 2;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].post('/api/poll/vote', {
                post_no: postNo,
                option_id: optionId
              });
            case 2:
              response = _context0.sent;
              if (response !== null && response !== void 0 && (_response$data3 = response.data) !== null && _response$data3 !== void 0 && _response$data3.poll) {
                post.poll = response.data.poll;
              }
              _context0.next = 4;
              break;
            case 3:
              _context0.prev = 3;
              _t0 = _context0["catch"](1);
              console.error('Failed to vote on poll:', _t0);
            case 4:
            case "end":
              return _context0.stop();
          }
        }, _callee0, null, [[1, 3]]);
      }))();
    },
    initEventListeners() {
      var _this12 = this;
      window.addEventListener('post-created', function (event) {
        var newPostData = event === null || event === void 0 ? void 0 : event.detail;
        if (newPostData && typeof newPostData === 'object') {
          var normalized = _this12.normalizePost(newPostData);
          if (!_this12.posts.some(function (p) {
            return String(p.post_no) === String(normalized.post_no);
          })) {
            _this12.posts.unshift(normalized);
          }
        } else {
          _this12.fetchPosts();
        }
      });

      // Dispatched by allEvents.js after a successful post edit (the acting
      // user's own tab — other tabs get it via the update-post Pusher bind).
      window.addEventListener('post-updated', function (event) {
        var _ref9 = (event === null || event === void 0 ? void 0 : event.detail) || {},
          postNo = _ref9.postNo,
          postMessage = _ref9.postMessage;
        var post = _this12.posts.find(function (p) {
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
      var _this13 = this;
      try {
        var key = "0dc3f141e1632b5aa5db";
        var cluster = "eu";
        if (!key || !cluster) return;
        this.pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_4___default())(key, {
          cluster,
          encrypted: true
        });
        var postsChannel = this.pusher.subscribe('posts-channel');
        postsChannel.bind('new-post', function (data) {
          if (Array.isArray(data)) {
            data.forEach(function (item) {
              if (!_this13.posts.some(function (p) {
                return String(p.post_no) === String(item === null || item === void 0 ? void 0 : item.post_no);
              })) {
                _this13.posts.unshift(_this13.normalizePost(item));
              }
            });
          }
        });
        var commentsChannel = this.pusher.subscribe('comments-channel');
        commentsChannel.bind('new-comment', function (data) {
          if (Array.isArray(data)) {
            data.forEach(function (item) {
              var post = _this13.posts.find(function (p) {
                return String(p.post_no) === String(item === null || item === void 0 ? void 0 : item.post_no);
              });
              if (post && !post.comments.some(function (c) {
                return String(c.comment_no) === String(item === null || item === void 0 ? void 0 : item.comment_no);
              })) {
                post.comments.push(_this13.normalizeComment(item));
              }
            });
          }
        });

        // Edit/delete broadcasts (PostMessage::deletePost/updatePost/
        // updateComment/deleteComment) send a single flat object, not an
        // array like new-post/new-comment do.
        postsChannel.bind('delete-post', function (data) {
          _this13.posts = _this13.posts.filter(function (p) {
            return String(p.post_no) !== String(data === null || data === void 0 ? void 0 : data.postNo);
          });
        });
        postsChannel.bind('update-post', function (data) {
          var _data$postMessage;
          var post = _this13.posts.find(function (p) {
            return String(p.post_no) === String(data === null || data === void 0 ? void 0 : data.postNo);
          });
          if (post) post.postMessage = (_data$postMessage = data === null || data === void 0 ? void 0 : data.postMessage) !== null && _data$postMessage !== void 0 ? _data$postMessage : post.postMessage;
        });
        commentsChannel.bind('delete-comment', function (data) {
          var post = _this13.posts.find(function (p) {
            return String(p.post_no) === String(data === null || data === void 0 ? void 0 : data.postNo);
          });
          if (post) {
            post.comments = post.comments.filter(function (c) {
              return String(c.comment_no) !== String(data === null || data === void 0 ? void 0 : data.commentNo);
            });
          }
        });
        commentsChannel.bind('update-comment', function (data) {
          var _data$comment;
          var post = _this13.posts.find(function (p) {
            return String(p.post_no) === String(data === null || data === void 0 ? void 0 : data.postNo);
          });
          var comment = post === null || post === void 0 ? void 0 : post.comments.find(function (c) {
            return String(c.comment_no) === String(data === null || data === void 0 ? void 0 : data.commentNo);
          });
          if (comment) comment.comment = (_data$comment = data === null || data === void 0 ? void 0 : data.comment) !== null && _data$comment !== void 0 ? _data$comment : comment.comment;
        });
        var likesChannel = this.pusher.subscribe('likes-channel');
        likesChannel.bind('like-event', function (data) {
          if (Array.isArray(data)) {
            data.forEach(function (item) {
              var post = _this13.posts.find(function (p) {
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

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "html": function() { return /* binding */ html; }
/* harmony export */ });
/* harmony import */ var _htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./htmlFolder/nameImageTiming */ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js");
/* harmony import */ var _htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./htmlFolder/commentForm */ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js");
/* harmony import */ var _htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./htmlFolder/likeCommentButton */ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js");
/* harmony import */ var _htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./htmlFolder/showPostImages */ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");
/* harmony import */ var _htmlFolder_engagementHtml__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./htmlFolder/engagementHtml */ "./resources/asset/js/components/profilePage/htmlFolder/engagementHtml.js");






var html = function html(el) {
  var comment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var post_no = el.post_no,
    postMessage = el.postMessage;
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin post".concat(post_no, "\"><br>\n\n      ").concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(el), "\n\n    <hr class=\"w3-clear\">\n\n    <p class=\"postFont\"> ").concat(postMessage, " </p>\n\n     ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(el), "\n     \n     ").concat((0,_htmlFolder_engagementHtml__WEBPACK_IMPORTED_MODULE_5__.renderPoll)(el === null || el === void 0 ? void 0 : el.poll), "\n     ").concat((0,_htmlFolder_engagementHtml__WEBPACK_IMPORTED_MODULE_5__.renderReactions)(el), "\n\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(el), "\n\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(el), "\n\n    <div id = 'showComment").concat(post_no, "'>\n\n      ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment), "\n      \n    </div><br>\n  </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js":
/*!*****************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/commentForm.js ***!
  \*****************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "commentForm": function() { return /* binding */ commentForm; }
/* harmony export */ });
var commentForm = function commentForm(data) {
  var post_no = data.post_no;
  return " <p id=\"formComment".concat(post_no, "_notification\"></p>\n\n  <form \n    action=\"/postCommentProfile\" \n    method=\"post\" id=\"formComment").concat(post_no, "\" \n    style=\"display:none\" \n    enctype=\"multipart/form-data\">\n\n    <input \n      name='post_no' \n      type=\"hidden\" \n      name=\"").concat(post_no, "\" \n      value=").concat(post_no, " />\n\n    <input \n      class=\"w3-input w3-border w3-round-large inputComment\" \n      type=\"text\" \n      placeholder=\"Write a comment\"\n      id=\"inputComment").concat(post_no, "\" \n      value = \"\" name='comment'>\n\n    <br>\n\n    <button \n      type='submit' \n      id=\"submitComment").concat(post_no, "\" \n      class=\"w3-button w3-green submitComment\">\n        Submit\n    </button>\n    \n    <br><br>\n  </form>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/engagementHtml.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/engagementHtml.js ***!
  \********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "renderPoll": function() { return /* binding */ renderPoll; },
/* harmony export */   "renderReactions": function() { return /* binding */ renderReactions; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");

var renderPoll = function renderPoll(poll) {
  if (!poll || !poll.options || !Array.isArray(poll.options)) return '';

  // Safely fallback the question text
  var questionText = poll.question || '';
  var html = "<div class=\"poll-container mt-3 p-3 bg-light rounded border border-light-subtle\">\n        <h6 class=\"fw-bold mb-3\"><i class=\"fa fa-bar-chart me-2 text-primary\"></i>".concat(questionText, "</h6>\n        <div class=\"poll-options\">");
  poll.options.forEach(function (opt) {
    // Multi-select is supported on backend. user_voted_option_id is an array now
    var isVoted = poll.user_voted_option_id && Array.isArray(poll.user_voted_option_id) && poll.user_voted_option_id.includes(opt.option_id);
    var percentage = (opt === null || opt === void 0 ? void 0 : opt.percentage) || 0;
    var barWidth = percentage + '%';
    var bgClass = isVoted ? 'bg-primary' : 'bg-secondary';
    var optionText = (opt === null || opt === void 0 ? void 0 : opt.option_text) || '';
    html += "\n            <div class=\"poll-option mb-2 position-relative\">\n                <button class=\"poll-option-btn w-100 text-start btn btn-outline-".concat(isVoted ? 'primary' : 'secondary', " position-relative overflow-hidden\" \n                        data-option-id=\"").concat((opt === null || opt === void 0 ? void 0 : opt.option_id) || '', "\" style=\"z-index: 1;\">\n                    <span class=\"position-relative\" style=\"z-index: 2; mix-blend-mode: difference; color: white;\">\n                        ").concat(optionText, "\n                    </span>\n                    <span class=\"float-end position-relative fw-bold\" style=\"z-index: 2; mix-blend-mode: difference; color: white;\">\n                        ").concat(percentage, "%\n                    </span>\n                    <div class=\"position-absolute top-0 start-0 h-100 ").concat(bgClass, "\" \n                         style=\"width: ").concat(barWidth, "; opacity: 0.5; transition: width 0.5s ease; z-index: 0;\"></div>\n                </button>\n            </div>\n        ");
  });
  html += "</div>\n        <small class=\"text-muted mt-2 d-block\">".concat((poll === null || poll === void 0 ? void 0 : poll.total_votes) || 0, " votes</small>\n    </div>");
  return html;
};
var renderReactions = function renderReactions(el) {
  // Emojis: Like 👍, Love ❤️, Haha 😂, Shock 😲, Sad 😢
  var emojis = {
    'like': '👍',
    'love': '❤️',
    'haha': '😂',
    'shock': '😲',
    'sad': '😢'
  };
  var reactions = (el === null || el === void 0 ? void 0 : el.reactions) || [];
  var userReaction = (el === null || el === void 0 ? void 0 : el.user_reaction) || null;
  var postNo = (el === null || el === void 0 ? void 0 : el.post_no) || '';
  if (!postNo) return '';
  var summaryHtml = '';
  var totalReactions = 0;
  var counts = {
    like: 0,
    love: 0,
    haha: 0,
    shock: 0,
    sad: 0
  };
  reactions.forEach(function (r) {
    if (r && r.reaction_type && r.count) {
      counts[r.reaction_type] = parseInt(r.count, 10) || 0;
      totalReactions += counts[r.reaction_type];
    }
  });
  if (totalReactions > 0) {
    summaryHtml = "<div class=\"reaction-summary mt-2 mb-2 px-2 py-1 bg-light rounded d-inline-block shadow-sm\">\n            ".concat(counts.like > 0 ? "\uD83D\uDC4D ".concat(counts.like) : '', "\n            ").concat(counts.love > 0 ? "\u2764\uFE0F ".concat(counts.love) : '', "\n            ").concat(counts.haha > 0 ? "\uD83D\uDE02 ".concat(counts.haha) : '', "\n            ").concat(counts.shock > 0 ? "\uD83D\uDE32 ".concat(counts.shock) : '', "\n            ").concat(counts.sad > 0 ? "\uD83D\uDE22 ".concat(counts.sad) : '', "\n        </div>");
  }
  var barHtml = "<div class=\"reaction-bar d-flex gap-2 mt-2 align-items-center\">";
  for (var _i = 0, _Object$entries = Object.entries(emojis); _i < _Object$entries.length; _i++) {
    var _Object$entries$_i = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_Object$entries[_i], 2),
      type = _Object$entries$_i[0],
      emoji = _Object$entries$_i[1];
    var isActive = userReaction === type;
    barHtml += "\n            <button class=\"btn btn-sm reaction-btn ".concat(isActive ? 'btn-primary' : 'btn-light text-dark', " rounded-pill shadow-sm\" \n                    data-post-no=\"").concat(postNo, "\" data-reaction=\"").concat(type, "\"\n                    style=\"transition: transform 0.2s; ").concat(isActive ? 'transform: scale(1.1);' : '', "\">\n                ").concat(emoji, " <span class=\"d-none d-md-inline ms-1 text-capitalize\" style=\"font-size: 0.8rem;\">").concat(type, "</span>\n            </button>\n        ");
  }
  barHtml += "</div>";
  return "<div class=\"engagement-section mt-3\">\n        ".concat(summaryHtml, "\n        ").concat(barHtml, "\n    </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js ***!
  \***********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "likeCommentButton": function() { return /* binding */ likeCommentButton; }
/* harmony export */ });
var likeCommentButton = function likeCommentButton(data, commentLength) {
  return "\n   <div class=\"reaction-buttons d-flex justify-content-around border-top border-bottom py-2 mb-2 mt-3 gap-2\">\n    <button \n      class=\"btn flex-grow-1 fw-semibold rounded-pill d-flex align-items-center justify-content-center\"\n      style=\"background-color: var(--hover-color); color: var(--text-color); border: none; transition: all 0.2s;\"\n      type=\"button\" \n      id=\"likeButton".concat(data.post_no, "\" \n      name=\"").concat(data.post_no, "\">\n      <i class=\"bi bi-hand-thumbs-up me-2\" style=\"font-size: 1.1rem; color: var(--text-muted);\"></i> \n      Like \n      <span class=\"badge ms-2\" style=\"background-color: var(--border-color); color: var(--text-color);\">\n        <span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">").concat(data.post_likes, "</span>\n      </span>\n    </button>\n\n    <button \n      class=\"btn flex-grow-1 fw-semibold rounded-pill d-flex align-items-center justify-content-center\"\n      style=\"background-color: var(--hover-color); color: var(--text-color); border: none; transition: all 0.2s;\"\n      type=\"button\" \n      id=\"initComment").concat(data.post_no, "\">\n        <i class=\"bi bi-chat me-2\" style=\"font-size: 1.1rem; color: var(--text-muted);\"></i> \n        Comment \n        <span class=\"badge ms-2\" style=\"background-color: var(--border-color); color: var(--text-color);\">\n          <span class=\"commentCounter\" id=\"commentCounter").concat(data.post_no, "\">").concat(commentLength, "</span>\n        </span>\n    </button>\n   \n    </div>\n    ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js":
/*!*********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js ***!
  \*********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "nameImgTiming": function() { return /* binding */ nameImgTiming; }
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");

var timeAgo = function timeAgo(x) {
  return x ? (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x) : '';
};
var fullName = function fullName(_fullName) {
  return "<h6 id=\"fullName\"><b>".concat(_fullName || 'Unknown User', "</b> </h6>");
};
var postedAt = function postedAt(date) {
  if (!(date !== null && date !== void 0 && date.date_created) || !(date !== null && date !== void 0 && date.post_time)) return '';
  return "<div class=\"timeago postTimeCal w3-right w3-opacity\"  datetime='".concat(date.date_created, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date.date_created), "'> ").concat(timeAgo(date.post_time), "</div>");
};
var familyBadge = function familyBadge(famCode) {
  return famCode ? "<span class=\"w3-badge w3-small w3-blue w3-margin-left\" style=\"font-weight: normal; padding: 2px 6px;\">Family: ".concat(famCode, "</span>") : '';
};
var nameImgTiming = function nameImgTiming(data) {
  var img = data !== null && data !== void 0 && data.profileImg ? "/resources/images/profile/".concat(data.profileImg) : "/public/avatar/avatarF.png";
  var pId = (data === null || data === void 0 ? void 0 : data.post_no) || '';
  var uId = (data === null || data === void 0 ? void 0 : data.id) || '';
  var imgParam = (data === null || data === void 0 ? void 0 : data.img) || '';
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(imgParam, "&pID=").concat(pId, "&path=profile&id=").concat(uId, "\"> <img src=").concat(img, " alt=\"img\" class=\"w3-left w3-circle w3-margin-right postImg\" style=\"width:60px\">\n        </a>\n        ").concat(postedAt(data), " \n        <div style=\"display: flex; align-items: center;\">\n            ").concat(fullName(data === null || data === void 0 ? void 0 : data.fullName), " ").concat(familyBadge(data === null || data === void 0 ? void 0 : data.postFamCode), "\n        </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js ***!
  \********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "showPostImg": function() { return /* binding */ showPostImg; }
/* harmony export */ });
var showPostImg = function showPostImg(data) {
  // GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE
  var postImagesWithValues = Object.keys(data).filter(function (key) {
    return key.startsWith('post_img') && data[key] !== null;
  }).map(function (el) {
    return data[el];
  });
  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    return "\n    <a href=\"/profilepage/img?dir=img&pics=".concat(imgElement, "&pID=").concat(postNo, "&path=post\">\n      <div class=\"w3-half\">\n        <img src=\"/resources/images/post/").concat(imgElement, "\" style=\"width:100%\" alt=\"images").concat(i, "\" class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat(i, "\">\n      </div>\n    </a>\n  ");
  };
  var imgElements = postImagesWithValues.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }).join('');
  return "\n    <div class=\"w3-row-padding\" style=\"margin:0 -16px\">\n      ".concat(imgElements, "\n      <br>\n    </div>\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/img.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/img.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_images__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/images */ "./resources/asset/js/components/helper/images.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");





var profilePics = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePics');
if (profilePics) {
  profilePics.addEventListener('click', function () {
    var formProfile = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formProfilePics');
    if (formProfile) formProfile.style.display = "block";
  });
}

// FOR PROFILE IMAGE CHANGE
var uploadButtonProfilePics = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('uploadButtonProfilePics');
var profileImageFile = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profileImageFile');
var profileImgFileNames = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profileImgFileNames');
if (uploadButtonProfilePics && profileImageFile && profileImgFileNames) {
  (0,_helper_images__WEBPACK_IMPORTED_MODULE_1__.showImageFileUploadFn)('uploadButtonProfilePics', 'profileImageFile', 'profileImgFileNames');
}

// FOR POST MODAL IMAGE UPLOAD  
var uploadButton = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('uploadButton');
var post_img = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('post_img');
var postModalImgFileNames = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('postModalImgFileNames');
if (uploadButton && post_img && postModalImgFileNames) {
  (0,_helper_images__WEBPACK_IMPORTED_MODULE_1__.showImageFileUploadFn)('uploadButton', 'post_img', 'postModalImgFileNames');
}
var submitProfilePics = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitProfilePics');
if (submitProfilePics) {
  submitProfilePics.addEventListener('click', function () {
    // Get the form element
    var form = document.getElementById("formProfilePics");
    if (!form) return;

    // Create a FormData object and append the form data to it
    var formData = new FormData(form);
    var options = {
      xsrfCookieName: 'XSRF-TOKEN',
      xsrfHeaderName: 'X-XSRF-TOKEN'
    };
    // send form data using axios post method

    axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/member/profilePage/profileImg', formData, options).then(function (response) {
      var profilePicsNotification = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification');
      if (profilePicsNotification) {
        profilePicsNotification.innerHTML = response.data;
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(response.data, "profilePicsNotification");
        if (response.data.message === "Profile image updated") {
          profilePicsNotification.classList.add('w3-green');
          profilePicsNotification.innerHTML = response.data.message;
          // Reload the page
          location.reload();
        }
      }
    }).catch(function (error) {
      var profilePicsNotification = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification');
      if (profilePicsNotification) {
        profilePicsNotification.classList.add('w3-red');
        profilePicsNotification.innerHTML = error.message;
      }
    });
    var profilePicsNotification = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification');
    if (profilePicsNotification) {
      profilePicsNotification.innerHTML = "";
    }
  });
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/index.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/index.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _feedComponent__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./feedComponent */ "./resources/asset/js/components/profilePage/feedComponent.js");
/* harmony import */ var _sidebarComponents__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./sidebarComponents */ "./resources/asset/js/components/profilePage/sidebarComponents.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/profilePage/modal.js");
/* harmony import */ var _img__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./img */ "./resources/asset/js/components/profilePage/img.js");
/* harmony import */ var _rsvpBtn__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./rsvpBtn */ "./resources/asset/js/components/profilePage/rsvpBtn.js");
/* harmony import */ var _rsvpBtn__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_rsvpBtn__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/profilePage/allEvents.js");
/* harmony import */ var _registerPushNotification__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./registerPushNotification */ "./resources/asset/js/components/profilePage/registerPushNotification.js");
/* harmony import */ var _periodicSync__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./periodicSync */ "./resources/asset/js/components/profilePage/periodicSync.js");
/* harmony import */ var _createEvent__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./createEvent */ "./resources/asset/js/components/profilePage/createEvent.js");
/* harmony import */ var _editProfile__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./editProfile */ "./resources/asset/js/components/profilePage/editProfile.js");
/* harmony import */ var _postEmojiImgProcess__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./postEmojiImgProcess */ "./resources/asset/js/components/profilePage/postEmojiImgProcess.js");
/* harmony import */ var _longPress__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./longPress */ "./resources/asset/js/components/profilePage/longPress.js");
/* harmony import */ var _longPress__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(_longPress__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var _engagement__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./engagement */ "./resources/asset/js/components/profilePage/engagement.js");


localStorage.removeItem('redirect');

window.profileFeed = _feedComponent__WEBPACK_IMPORTED_MODULE_0__.profileFeed;

window.profileSidebar = _sidebarComponents__WEBPACK_IMPORTED_MODULE_1__.profileSidebar;
window.upcomingEvents = _sidebarComponents__WEBPACK_IMPORTED_MODULE_1__.upcomingEvents;







// import "./friendRequestCard"  // Disabled in favor of Alpine.js profileSidebar component


// import "./commentEmojiTest"
 // long press for reaction options


document.addEventListener('DOMContentLoaded', function () {
  (0,_engagement__WEBPACK_IMPORTED_MODULE_12__.initEngagementListeners)();
  (0,_engagement__WEBPACK_IMPORTED_MODULE_12__.initMemories)();
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/longPress.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/longPress.js ***!
  \****************************************************************/
/***/ (function() {

/**
* Initializes long-press behavior for all visible reaction buttons.
* This loop ensures onboarding clarity for contributors working with dynamic comment blocks.
*/
document.querySelectorAll('.reaction-button').forEach(function (btn) {
  var commentNo = btn.dataset.commentNo;
  setupLongPressReaction(commentNo);
});

/**
 * Sets up long-press behavior on a reaction button to reveal emoji options.
 * This improves mobile UX and teaches contributors how to scaffold gesture-based interactions.
 *
 * @param {string} commentNo - Unique identifier for the comment block
 */
var setupLongPressReaction = function setupLongPressReaction(commentNo) {
  var button = document.getElementById("like-button-".concat(commentNo));
  var reactionBar = document.getElementById("reaction-bar-".concat(commentNo));
  if (!button || !reactionBar) return; // 🧩 Defensive check for missing DOM elements

  var pressTimer; // ⏱️ Used to track long-press duration

  // 📱 Mobile long-press: triggers on touchstart after delay
  button.addEventListener('touchstart', function (e) {
    e.preventDefault(); // 🧼 Prevent accidental scroll or tap
    pressTimer = setTimeout(function () {
      reactionBar.classList.add('show'); // 🎉 Reveal emoji bar
    }, 400); // ⏱️ Customize delay to match UX expectations
  });

  // 🧼 Cancel long-press if user lifts finger or scrolls
  button.addEventListener('touchend', function () {
    return clearTimeout(pressTimer);
  });
  button.addEventListener('touchmove', function () {
    return clearTimeout(pressTimer);
  });

  // 🖱️ Optional desktop fallback: long-press via mouse
  button.addEventListener('mousedown', function () {
    pressTimer = setTimeout(function () {
      reactionBar.classList.add('show');
    }, 500); // Slightly longer for desktop UX
  });

  // 🧼 Cancel desktop long-press if mouse leaves or releases
  button.addEventListener('mouseup', function () {
    return clearTimeout(pressTimer);
  });
  button.addEventListener('mouseleave', function () {
    return clearTimeout(pressTimer);
  });
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/modal.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/modal.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");




// import { postFormData } from "../helper/http"

try {
  var _id, _id2;
  // NEW MESSAGE MODAL
  var showModal = function showModal() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'block';
  };

  // CREATE EVENT MODAL
  var showEvent = function showEvent() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'block';
  };

  //EVENT ACTION
  (_id = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createEvent')) === null || _id === void 0 ? void 0 : _id.addEventListener('click', showEvent);
  (_id2 = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('postMsg')) === null || _id2 === void 0 ? void 0 : _id2.addEventListener('click', showModal);

  // handle post message
} catch (e) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/periodicSync.js":
/*!*******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/periodicSync.js ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);


// utility for periodic sync  
if ('periodicSync' in navigator) {
  (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var status, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.next = 1;
          return navigator.permissions.query({
            name: 'periodic-background-sync'
          });
        case 1:
          status = _context.sent;
          if (status.state === 'granted') {
            console.log('Background sync permission granted');
          } else {
            console.log('Background sync permission denied');
          }
          _context.prev = 2;
          _context.next = 3;
          return navigator.periodicSync.register('content-sync', {
            minInterval: 24 * 60 * 60 * 1000 // Minimum interval in milliseconds (e.g., 1 day)
          });
        case 3:
          console.log('Periodic Sync registered');
          _context.next = 5;
          break;
        case 4:
          _context.prev = 4;
          _t = _context["catch"](2);
          console.error('Periodic Sync registration failed:', _t);
        case 5:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[2, 4]]);
  }))(); // Immediately invoke the async function
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/postEmojiImgProcess.js":
/*!**************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/postEmojiImgProcess.js ***!
  \**************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _emojiPicker_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../emojiPicker.js */ "./resources/asset/js/components/emojiPicker.js");
/* harmony import */ var _fileUploadPreview__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../fileUploadPreview */ "./resources/asset/js/components/fileUploadPreview.js");
// Import a helper function to get elements by ID (assumed from your shared utils)



// Get references to DOM elements used in the emoji picker and image preview - SHOW EMOJIs FOR POST

var emojiToggle = document.getElementById('emojiPost'); // Button to show/hide emoji picker
var emojiContainer = document.getElementById('emojiPickerContainer'); // Container for emoji picker
var closeEmojiBtn = document.getElementById('closeEmojiPicker'); // Close button

if (document.getElementById('emojiListPost')) {
  (0,_emojiPicker_js__WEBPACK_IMPORTED_MODULE_0__.showEmojiPicker)('emojiListPost', 'data-emoji-target');
}
if (emojiToggle && emojiContainer) {
  (0,_emojiPicker_js__WEBPACK_IMPORTED_MODULE_0__.initEmojiPickerUX)('emojiPost', 'emojiPickerContainer');
}

// 🟡 Toggle emoji picker visibility when the toggle button is clicked
if (emojiToggle && emojiContainer) {
  emojiToggle.addEventListener('click', function () {
    emojiContainer.classList.toggle('d-none'); // Show/hide the emoji container
    emojiToggle.setAttribute('aria-expanded', emojiContainer.classList.contains('d-none') ? 'false' : 'true');
  });
}

// Close button handler
if (closeEmojiBtn && emojiContainer && emojiToggle) {
  closeEmojiBtn.addEventListener('click', function () {
    emojiContainer.classList.add('d-none');
    emojiToggle.setAttribute('aria-expanded', 'false');
  });
}
if (document.getElementById('imageUpload')) {
  (0,_fileUploadPreview__WEBPACK_IMPORTED_MODULE_1__.imagePreview)('imageUpload', 'imagePreviewList', 'postModalImgFileNames', 'imagePreviewContainer', 'closeImagePreview');
}

// Poll Creation UI Logic
var addPollBtn = document.getElementById('addPollBtn');
var pollContainer = document.getElementById('pollCreationContainer');
var addOptionBtn = document.getElementById('addPollOptionBtn');
var optionsContainer = document.getElementById('pollOptionsContainer');
var removePollBtn = document.getElementById('removePollBtn');
if (addPollBtn && pollContainer) {
  addPollBtn.addEventListener('click', function () {
    pollContainer.classList.remove('d-none');
  });
  if (removePollBtn) {
    removePollBtn.addEventListener('click', function () {
      pollContainer.classList.add('d-none');
      // Reset inputs
      var inputs = pollContainer.querySelectorAll('input');
      inputs.forEach(function (input) {
        return input.value = '';
      });
    });
  }
  if (addOptionBtn && optionsContainer) {
    addOptionBtn.addEventListener('click', function () {
      var optionCount = optionsContainer.querySelectorAll('input').length + 1;
      var input = document.createElement('input');
      input.type = 'text';
      input.name = 'poll_options[]';
      input.className = 'form-control mb-2';
      input.placeholder = "Option ".concat(optionCount);
      input.style.borderRadius = '10px';
      optionsContainer.appendChild(input);
    });
  }
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/registerPushNotification.js":
/*!*******************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/registerPushNotification.js ***!
  \*******************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");

var VAPID_PUBLIC_KEY = "BAvqqppvGj5V0DqzieyYq5nGu9EW_db01_7jXO1_Nk-8UZzKJpCs1eGYx5d0yuBe7q3xu6oWaFS8etO9lazRMMo";
function urlBase64ToUint8Array(base64String) {
  var padding = '='.repeat((4 - base64String.length % 4) % 4);
  var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  var rawData = window.atob(base64);
  var outputArray = new Uint8Array(rawData.length);
  for (var i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

//// Check if service workers and push notifications are supported by the browser

if ('serviceWorker' in navigator && 'PushManager' in window) {
  navigator.serviceWorker.register('/service-worker.js').then(function (swReg) {
    // Check if the user is already subscribed
    checkSubscription(swReg);
  }).catch(function (error) {
    console.error('Service Worker registration failed', error);
  });
}
function checkSubscription(swReg) {
  swReg.pushManager.getSubscription().then(function (subscription) {
    if (subscription === null) {
      // Request permission for notifications
      requestPermission(swReg);
    } else {
      console.log('User  is already subscribed:', subscription);
      postSubscriptionToServer(subscription);
    }
  });
}
function requestPermission(swReg) {
  // This call must be made in response to a user action
  Notification.requestPermission().then(function (permission) {
    if (permission === 'granted') {
      subscribeUser(swReg);
    } else {
      console.log('Push notifications permission denied.');
    }
  });
}
function subscribeUser(swReg) {
  var applicationServerKey = urlBase64ToUint8Array(VAPID_PUBLIC_KEY); // Access public VAPID key

  swReg.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: applicationServerKey
  }).then(function (subscription) {
    console.log('User is subscribed:', subscription);
    // Send subscription object to the server to store it
    postSubscriptionToServer(subscription);
  }).catch(function (error) {
    console.error('Failed to subscribe the user: ', error);
  });
}
function postSubscriptionToServer(subscription) {
  // Prepare the subscription data to be sent to the server

  var subscriptionData = {
    id: localStorage.getItem('requesterId'),
    endpoint: subscription.endpoint,
    keys: {
      p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))),
      auth: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth'))))
    }
  };

  // Send subscription to the server using axios
  axios__WEBPACK_IMPORTED_MODULE_0__["default"].post('/pushNotification/subscription', subscriptionData).then(function (response) {
    console.log('Subscription data successfully sent to the server:', response);
  }).catch(function (error) {
    console.error('Failed to send subscription data to the server:', error);
  });
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/rsvpBtn.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/rsvpBtn.js ***!
  \**************************************************************/
/***/ (function() {

// RSVP Buttons
var rsvpButtons = document.querySelectorAll('.rsvp-buttons .btn');
rsvpButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    // Remove active class from all buttons in the same container
    this.parentElement.querySelectorAll('.btn').forEach(function (btn) {
      btn.classList.remove('active');
      if (btn.classList.contains('btn-outline-primary')) {
        btn.classList.replace('btn-primary', 'btn-outline-primary');
      } else if (btn.classList.contains('btn-outline-secondary')) {
        btn.classList.replace('btn-secondary', 'btn-outline-secondary');
      } else if (btn.classList.contains('btn-outline-danger')) {
        btn.classList.replace('btn-danger', 'btn-outline-danger');
      }
    });

    // Add active class to clicked button
    this.classList.add('active');
    if (this.classList.contains('btn-outline-primary')) {
      this.classList.replace('btn-outline-primary', 'btn-primary');
    } else if (this.classList.contains('btn-outline-secondary')) {
      this.classList.replace('btn-outline-secondary', 'btn-secondary');
    } else if (this.classList.contains('btn-outline-danger')) {
      this.classList.replace('btn-outline-danger', 'btn-danger');
    }
  });
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/showEmojiOnComment.js":
/*!*************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/showEmojiOnComment.js ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "renderTopReactions": function() { return /* binding */ renderTopReactions; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



/**
 * 🧠 Extracts the top 3 reaction types by count.
 * Filters out metadata keys like 'comment_no' and 'total', then sorts descending.
 *
 * @param {Object} counts - Reaction summary object including totals and metadata
 * @returns {Array} - Array of top 3 reactions like [ ['love', 5], ['wow', 3], ['likes', 2] ]
 */
var getTopReactions = function getTopReactions() {
  var counts = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  return Object.entries(counts).filter(function (_ref) {
    var _ref2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref, 2),
      label = _ref2[0],
      count = _ref2[1];
    return !['comment_no', 'total', 'totalReactions'].includes(label) && count > 0;
  }) // 🧼 Remove metadata and zero-count reactions
  .sort(function (_ref3, _ref4) {
    var _ref5 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref3, 2),
      a = _ref5[1];
    var _ref6 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref4, 2),
      b = _ref6[1];
    return b - a;
  }) // 🔢 Sort descending by count
  .slice(0, 3);
}; // 🎯 Return top 3 reactions only

/**
 * 🧠 Renders the top 3 reactions into the preview section of a comment.
 * Uses emoji map for visual clarity and teaches contributors how to safely update the DOM.
 *
 * @param {Object} counts - Reaction counts object from the server
 * @param {string|number} commentNo - Unique identifier for the comment block
 */
var renderTopReactions = function renderTopReactions(counts) {
  // 🧩 Map semantic labels to emoji characters
  var emojiMap = {
    likes: '👍',
    love: '❤️',
    haha: '😄',
    wow: '😮',
    sad: '😢',
    angry: '😠'
  };
  var top = getTopReactions(counts); // 🧠 Get top 3 reactions

  var html = top.map(function (_ref7) {
    var _emojiMap$label;
    var _ref8 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref7, 2),
      label = _ref8[0],
      count = _ref8[1];
    var emoji = (_emojiMap$label = emojiMap[label]) !== null && _emojiMap$label !== void 0 ? _emojiMap$label : ''; // 🧼 Fallback if label missing
    return "".concat(emoji, " ").concat(count); // 🖼️ Render emoji + count
  }).join(' '); // 🧵 Combine into single HTML string
  return html;
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/sidebarComponents.js":
/*!************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/sidebarComponents.js ***!
  \************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "profileSidebar": function() { return /* binding */ profileSidebar; },
/* harmony export */   "upcomingEvents": function() { return /* binding */ upcomingEvents; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_3__);





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
              return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/getFriendRequestById?id=".concat(encodeURIComponent(_this.userData.id)), {
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
              return axios__WEBPACK_IMPORTED_MODULE_4__["default"]["delete"]("/member/profilePage/event/".concat(eventNo), csrfOptions);
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
        var key = "0dc3f141e1632b5aa5db";
        var cluster = "eu";
        if (!key || !cluster) return;
        this.pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_2___default())(key, {
          cluster,
          encrypted: true
        });
        var eventsChannel = this.pusher.subscribe('events-channel');
        eventsChannel.bind('update-event', function (data) {
          return _this4.applyEventUpdate(data);
        });
        eventsChannel.bind('delete-event', function (data) {
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

/***/ })

}]);
//# sourceMappingURL=profilePage.js.map