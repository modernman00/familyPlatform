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
  var insertEmoji = function insertEmoji(e) {
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
  };

  // Prevent input focus loss on desktop browsers
  btn.addEventListener('mousedown', function (e) {
    return e.preventDefault();
  });

  // Prevent focus loss and handle tap on mobile devices
  btn.addEventListener('touchstart', function (e) {
    e.preventDefault();
    insertEmoji(e);
  }, {
    passive: false
  });

  // Normal click handling (fallback for desktop/mouse)
  btn.addEventListener('click', insertEmoji);
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
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");


// use this in conjunction with the file 
var showImageFileUploadFn = function showImageFileUploadFn(uploadBtn, inputId, fileName) {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(uploadBtn).addEventListener('click', function () {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(inputId).click();
  });
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(inputId).addEventListener('change', function () {
    var fileNames = Array.from(this.files).map(function (file) {
      return file.name;
    });
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(fileName).innerText = fileNames.join(', ');
  });
};

/***/ }),

/***/ "./resources/asset/js/components/kinship/kinshipRadar.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/kinship/kinshipRadar.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "initKinshipRadar": function() { return /* binding */ initKinshipRadar; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_2__);




function initKinshipRadar() {
  // The widget markup is included twice (a mobile copy in the feed column and a
  // desktop copy in the right sidebar, toggled via responsive classes) so both
  // exist in the DOM at once. Wire up every copy rather than just the first
  // match - getElementById would silently bind only to whichever one happens to
  // render first, which is often the hidden one for the current viewport.
  var containers = document.querySelectorAll('.kinship-radar-widget');
  containers.forEach(bindKinshipRadarContainer);
}
function getCsrfToken() {
  var _document$querySelect, _document$querySelect2;
  return ((_document$querySelect = document.querySelector('meta[name="csrf-token"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.getAttribute('content')) || ((_document$querySelect2 = document.querySelector('meta[name="csrf_token"]')) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.getAttribute('content')) || (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1] || '';
}
function bindKinshipRadarContainer(container) {
  // 1. Handle Connect Button
  container.addEventListener('click', /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
      var connectBtn, targetUserId, _res$data, csrfToken, res, Toast, dismissBtn, _targetUserId, card, _t, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            connectBtn = e.target.closest('.btn-connect-kin');
            if (!connectBtn) {
              _context.next = 6;
              break;
            }
            targetUserId = connectBtn.getAttribute('data-user-id');
            if (targetUserId) {
              _context.next = 1;
              break;
            }
            return _context.abrupt("return");
          case 1:
            connectBtn.disabled = true;
            connectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Connecting...';
            _context.prev = 2;
            csrfToken = getCsrfToken();
            _context.next = 3;
            return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/members/familyRequestMgt', {
              approverId: targetUserId,
              token: csrfToken
            }, {
              headers: {
                'X-XSRF-TOKEN': csrfToken,
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
              }
            });
          case 3:
            res = _context.sent;
            if (((_res$data = res.data) === null || _res$data === void 0 ? void 0 : _res$data.status) === 'success' || res.status === 200) {
              connectBtn.classList.remove('btn-primary');
              connectBtn.classList.add('btn-success');
              connectBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Request Sent';

              // Track kinship connect event (fire-and-forget)
              axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/api/analytics/track', {
                event_type: 'kinship_connect',
                target_id: targetUserId
              }).catch(function () {});
              if (typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_2___default()) !== 'undefined') {
                Toast = sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().mixin({
                  toast: true,
                  position: 'top-end',
                  showConfirmButton: false,
                  timer: 3000,
                  timerProgressBar: true
                });
                Toast.fire({
                  icon: 'success',
                  title: 'Kinship request sent!'
                });
              }
            }
            _context.next = 5;
            break;
          case 4:
            _context.prev = 4;
            _t = _context["catch"](2);
            console.error('[KinshipRadar] Connect error:', _t);
            connectBtn.disabled = false;
            connectBtn.innerHTML = '<i class="bi bi-person-plus-fill me-1"></i> Connect';
            alert('Could not send kinship request. Please try again.');
          case 5:
            return _context.abrupt("return");
          case 6:
            // 2. Handle Dismiss Button
            dismissBtn = e.target.closest('.btn-dismiss-kin');
            if (!dismissBtn) {
              _context.next = 11;
              break;
            }
            _targetUserId = dismissBtn.getAttribute('data-user-id');
            if (_targetUserId) {
              _context.next = 7;
              break;
            }
            return _context.abrupt("return");
          case 7:
            // Scoped to the button that was actually clicked rather than a
            // document-wide id lookup - the same kin's card id is duplicated
            // across the mobile/desktop widget copies, and getElementById would
            // only ever find (and remove) the first one.
            card = dismissBtn.closest('.kinship-item-card');
            if (card) {
              card.style.opacity = '0';
              card.style.transform = 'scale(0.95)';
              setTimeout(function () {
                return card.remove();
              }, 250);
            }
            _context.prev = 8;
            _context.next = 9;
            return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/api/kinship/dismiss', {
              dismissed_user_id: _targetUserId
            });
          case 9:
            _context.next = 11;
            break;
          case 10:
            _context.prev = 10;
            _t2 = _context["catch"](8);
            console.warn('[KinshipRadar] Dismiss sync error:', _t2);
          case 11:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[2, 4], [8, 10]]);
    }));
    return function (_x) {
      return _ref.apply(this, arguments);
    };
  }());
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initKinshipRadar);
} else {
  initKinshipRadar();
}

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
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");





// Update notification badge
function updateNotificationBadge(count) {
  var badge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count');
  var headerBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('header_notif_count');
  var countNum = parseInt(count || '0');
  var displayStr = countNum > 99 ? '99+' : countNum > 0 ? countNum.toString() : '';
  if (badge) {
    if (countNum <= 0) {
      badge.style.display = 'none';
      badge.textContent = '';
    } else {
      badge.textContent = displayStr;
      badge.style.display = 'flex';
    }
  }
  if (headerBadge) {
    if (countNum <= 0) {
      headerBadge.style.display = 'none';
      headerBadge.textContent = '';
    } else {
      headerBadge.textContent = displayStr;
      headerBadge.style.display = 'flex';
    }
  }
}

// Google Stitch Notification Item Template
var notificationHTML = function notificationHTML(data) {
  // Determine friendly emoji / icon based on type and content
  var getEmoji = function getEmoji(type) {
    var name = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    var content = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
    var text = "".concat(type, " ").concat(name, " ").concat(content).toLowerCase();
    if (text.includes('birthday') || text.includes('cake')) return '🎂';
    if (text.includes('anniversary') || text.includes('celebrate') || text.includes('cypress') || text.includes('party')) return '🥂';
    if (text.includes('crash') || text.includes('chaos') || text.includes('alert') || text.includes('warning')) return '🧧';
    if (text.includes('test') || text.includes('lab')) return '🧪';
    if (text.includes('wedding') || text.includes('marriage')) return '💍';
    if (text.includes('friend') || text.includes('request') || text.includes('connect')) return '👋';
    if (text.includes('post') || text.includes('photo') || text.includes('memory')) return '📸';
    if (text.includes('like') || text.includes('heart')) return '❤️';
    if (text.includes('comment')) return '💬';
    if (text.includes('house')) return '🏡';
    if (text.includes('meeting') || text.includes('event')) return '📅';
    return '🔔';
  };
  var emoji = getEmoji(data.notification_type || '', data.notification_name || '', data.notification_content || '');
  var isUnread = data.notification_status === 'new';
  var sender_id = data.sender_id,
    notification_name = data.notification_name,
    notification_content = data.notification_content,
    created_at = data.created_at,
    no = data.no;
  var randomNumber = Math.floor(100 + Math.random() * 900);
  var bannerId = "notificationBar".concat(sender_id).concat(randomNumber);
  var formattedTitle = (0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(notification_name || 'Notification');
  var timeAgoStr = created_at ? (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(created_at) : 'Just now';
  return "\n    <div id=\"".concat(bannerId, "\" class=\"notif-card-stitch ").concat(isUnread ? 'unread' : '', "\">\n        <div class=\"notif-emoji-icon\">\n            <span>").concat(emoji, "</span>\n        </div>\n        \n        <a href=\"#linkNotification").concat(no, "\" class=\"notif-details-link flex-grow-1 text-decoration-none\">\n            <h6 class=\"notif-item-title\">").concat(formattedTitle, "</h6>\n            <p class=\"notif-item-desc\">").concat(notification_content || '', "</p>\n            <span class=\"notif-item-time\">").concat(timeAgoStr, "</span>\n        </a>\n\n        <button class=\"notif-dismiss-btn\" \n                data-no=\"").concat(no, "\"\n                data-is=\"").concat(sender_id, "\"\n                type=\"button\"\n                id=\"deleteNotification").concat(sender_id).concat(randomNumber, "\"\n                title=\"Dismiss\">\n            <i class=\"bi bi-x-lg\" style=\"pointer-events: none;\"></i>\n        </button>\n    </div>\n    ");
};
var increaseNotificationCount = function increaseNotificationCount() {
  var current = parseInt(sessionStorage.getItem('notificationCount') || '0') + 1;
  sessionStorage.setItem('notificationCount', current);
  updateNotificationBadge(current);
};
var addToNotificationTab = function addToNotificationTab(data) {
  var tab = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab');
  if (tab) {
    tab.insertAdjacentHTML('afterbegin', notificationHTML(data));
  }
};
var yourId = localStorage.getItem('requesterId');
var famCode = localStorage.getItem('requesterFamCode');
var notificationURL = "/member/notifications/id/".concat(yourId, "/").concat(famCode);
if (yourId && famCode && yourId !== 'null' && famCode !== 'null') {
  axios__WEBPACK_IMPORTED_MODULE_3__["default"].get(notificationURL).then(function (res) {
    var data = res.data.message;
    if (data && data.length > 0) {
      sessionStorage.setItem('notificationCount', data.length);
      updateNotificationBadge(data.length);
      data.forEach(function (element) {
        addToNotificationTab(element);
      });
      var updateNotificationTiming = document.querySelectorAll(".notification_timeago");
      if (updateNotificationTiming && updateNotificationTiming.length > 0) {
        (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.render)(updateNotificationTiming);
      }
    } else {
      sessionStorage.setItem('notificationCount', 0);
      updateNotificationBadge(0);
      var tab = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab');
      if (tab && !tab.children.length) {
        tab.innerHTML = "\n                    <div class=\"p-4 text-center text-muted\">\n                        <i class=\"bi bi-bell-slash fs-3 d-block mb-2 text-secondary opacity-50\"></i>\n                        <span class=\"small fw-medium\">All caught up! No notifications</span>\n                    </div>";
      }
    }
  }).catch(function (error) {
    (0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  });
}

// Dropdown controls
var notificationBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notificationBtn');
var notificationDropdown = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('notificationDropdown');
var markAllReadBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('markAllRead');
if (notificationBtn && notificationDropdown) {
  notificationBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    notificationDropdown.classList.toggle('show');
  });
  document.addEventListener('click', function (e) {
    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
      notificationDropdown.classList.remove('show');
    }
  });
  notificationDropdown.addEventListener('click', function (e) {
    e.stopPropagation();
  });
}
if (markAllReadBtn) {
  markAllReadBtn.addEventListener('click', function () {
    var unreadItems = document.querySelectorAll('.notif-card-stitch.unread');
    unreadItems.forEach(function (item) {
      item.classList.remove('unread');
    });
    sessionStorage.setItem('notificationCount', '0');
    updateNotificationBadge(0);
  });
}

// Dismiss listener delegation
var initDeleteOnce = function initDeleteOnce() {
  var tab = document.getElementById('notification_tab');
  if (!tab) return;
  tab.addEventListener('click', function (e) {
    var btn = e.target.closest('button[id*="deleteNotification"]');
    if (!btn) return;
    e.stopPropagation();
    var bannerId = btn.id.replace('deleteNotification', 'notificationBar');
    var no = btn.getAttribute('data-no');
    var url = "/removeNotification/".concat(no);
    axios__WEBPACK_IMPORTED_MODULE_3__["default"].put(url).then(function (response) {
      if (response.data.message === 'Notification marked as read') {
        var item = document.getElementById(bannerId);
        if (item) {
          item.style.opacity = '0';
          item.style.transform = 'translateX(10px)';
          setTimeout(function () {
            item.remove();
            if (!tab.querySelectorAll('.notif-card-stitch').length) {
              tab.innerHTML = "\n                                <div class=\"p-4 text-center text-muted\">\n                                    <i class=\"bi bi-bell-slash fs-3 d-block mb-2 text-secondary opacity-50\"></i>\n                                    <span class=\"small fw-medium\">All caught up! No notifications</span>\n                                </div>";
            }
          }, 200);
        }
        var currentCount = parseInt(sessionStorage.getItem('notificationCount') || '1') - 1;
        var newValues = currentCount > 0 ? currentCount : 0;
        sessionStorage.setItem('notificationCount', newValues);
        updateNotificationBadge(newValues);
      }
    }).catch(_shared__WEBPACK_IMPORTED_MODULE_1__.showError);
  });
};
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', initDeleteOnce) : initDeleteOnce();

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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../global.js */ "./resources/asset/js/components/global.js");
/* harmony import */ var _fileUploadPreview__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../fileUploadPreview */ "./resources/asset/js/components/fileUploadPreview.js");










/**
 * Reliably close the "Create Post" modal. Clicking [data-bs-dismiss] can miss if
 * Bootstrap never instantiated the modal (e.g. opened programmatically), which
 * left the backdrop covering the feed and broke follow-up interactions.
 */
function stripModalChrome() {
  document.body.classList.remove('modal-open');
  document.body.style.removeProperty('overflow');
  document.body.style.removeProperty('padding-right');
  document.querySelectorAll('.modal-backdrop').forEach(function (b) {
    return b.remove();
  });
}
function closePostModal() {
  var _window$bootstrap;
  var modalEl = document.getElementById('postModal');
  if (!modalEl) return;

  // Let Bootstrap run its own teardown (fires hidden.bs.modal, restores focus…).
  var Bs = (_window$bootstrap = window.bootstrap) === null || _window$bootstrap === void 0 ? void 0 : _window$bootstrap.Modal;
  if (Bs) {
    try {
      Bs.getOrCreateInstance(modalEl).hide();
    } catch (_) {/* noop */}
  }

  // …but don't wait on the fade-out animation: hide the modal and clear the
  // backdrop synchronously so the feed underneath is immediately interactive.
  modalEl.classList.remove('show');
  modalEl.style.display = 'none';
  modalEl.setAttribute('aria-hidden', 'true');
  modalEl.removeAttribute('aria-modal');
  modalEl.removeAttribute('role');
  stripModalChrome();

  // A late Bootstrap transition callback can re-add body chrome; sweep once more.
  setTimeout(stripModalChrome, 350);
}
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
      var elementId, _document$getElementB, formExtra, editPostNo, isEditing, pollContainer, _formExtra$querySelec, _formExtra$querySelec2, pollQuestion, pollOptions, formData, requesterFamCodeValue, selectedFiles, fileInput, filesToAppend, submitBtn, originalBtnText, _response$data, response, _response$data2, newPostData, closePreview, addOptBtn, _response$data3, _error$response, _error$response$data, friendRequestSection, _t, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            elementId = e.target.id; // SUBMIT THE NEW POST (from modal)
            if (!(elementId && elementId.includes("submitPost"))) {
              _context.next = 15;
              break;
            }
            e.preventDefault();
            formExtra = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__.id)('formPostMessageModal');
            if (formExtra) {
              _context.next = 1;
              break;
            }
            return _context.abrupt("return");
          case 1:
            // editPost() in feedComponent.js stamps this when reopening the modal
            // to edit an existing post instead of creating a new one.
            editPostNo = (_document$getElementB = document.getElementById('editPostNo')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.value;
            isEditing = !!editPostNo; // Validate poll if it's being created
            pollContainer = document.getElementById('pollCreationContainer');
            if (!(pollContainer && !pollContainer.classList.contains('d-none'))) {
              _context.next = 3;
              break;
            }
            pollQuestion = (_formExtra$querySelec = formExtra.querySelector('input[name="poll_question"]')) === null || _formExtra$querySelec === void 0 ? void 0 : (_formExtra$querySelec2 = _formExtra$querySelec.value) === null || _formExtra$querySelec2 === void 0 ? void 0 : _formExtra$querySelec2.trim();
            pollOptions = Array.from(formExtra.querySelectorAll('input[name="poll_options[]"]')).map(function (input) {
              return input.value.trim();
            }).filter(function (val) {
              return val.length > 0;
            });
            if (pollQuestion) {
              _context.next = 2;
              break;
            }
            sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
              icon: 'error',
              title: 'Poll Incomplete',
              text: 'Please enter a poll question.',
              confirmButtonColor: '#4ade80'
            });
            return _context.abrupt("return");
          case 2:
            if (!(pollOptions.length < 2)) {
              _context.next = 3;
              break;
            }
            sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
              icon: 'error',
              title: 'Poll Incomplete',
              text: 'Please provide at least 2 poll options.',
              confirmButtonColor: '#4ade80'
            });
            return _context.abrupt("return");
          case 3:
            if (!isEditing) {
              formData = new FormData(formExtra);
              requesterFamCodeValue = localStorage.getItem('requesterFamCode');
              formData.append('postFamCode', requesterFamCodeValue);

              // Explicitly ensure all accumulated image files are appended directly to formData
              selectedFiles = (0,_fileUploadPreview__WEBPACK_IMPORTED_MODULE_5__.getSelectedPostFiles)();
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
            submitBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__.id)(elementId);
            originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = "<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> ".concat(isEditing ? 'Saving...' : 'Posting...');
            submitBtn.disabled = true;
            _context.prev = 4;
            if (!isEditing) {
              _context.next = 6;
              break;
            }
            _context.next = 5;
            return axios__WEBPACK_IMPORTED_MODULE_6__["default"].put("/post/".concat(editPostNo), {
              postMessage: (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__.id)('postMessage').value
            }, options);
          case 5:
            _t = _context.sent;
            _context.next = 8;
            break;
          case 6:
            _context.next = 7;
            return axios__WEBPACK_IMPORTED_MODULE_6__["default"].post("/member/profilePage/post", formData, options);
          case 7:
            _t = _context.sent;
          case 8:
            response = _t;
            if (!((response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.status) === 'success' || (response === null || response === void 0 ? void 0 : response.status) === 200)) {
              _context.next = 10;
              break;
            }
            if (!isEditing) {
              _context.next = 9;
              break;
            }
            window.dispatchEvent(new CustomEvent('post-updated', {
              detail: {
                postNo: editPostNo,
                postMessage: (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__.id)('postMessage').value
              }
            }));
            closePostModal();
            formExtra.reset();
            sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Post updated successfully',
              showConfirmButton: false,
              timer: 3000
            });
            return _context.abrupt("return");
          case 9:
            newPostData = response !== null && response !== void 0 && (_response$data2 = response.data) !== null && _response$data2 !== void 0 && _response$data2.message && typeof response.data.message === 'object' ? response.data.message : null; // Close the modal first so the feed underneath is immediately
            // interactive, then hand the new post to the Alpine feed.
            closePostModal();
            window.dispatchEvent(new CustomEvent('post-created', {
              detail: newPostData
            }));
            formExtra.reset();
            (0,_fileUploadPreview__WEBPACK_IMPORTED_MODULE_5__.clearSelectedPostFiles)();
            closePreview = document.getElementById('closeImagePreview');
            if (closePreview) closePreview.click();

            // Reset the poll builder after a successful submission
            if (typeof window.__resetPollBuilder === 'function') {
              window.__resetPollBuilder();
            }
            addOptBtn = document.getElementById('addPollOptionBtn');
            if (addOptBtn) addOptBtn.style.display = '';
            sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Post published successfully',
              showConfirmButton: false,
              timer: 3000
            });
            _context.next = 11;
            break;
          case 10:
            throw new Error((response === null || response === void 0 ? void 0 : (_response$data3 = response.data) === null || _response$data3 === void 0 ? void 0 : _response$data3.message) || 'Failed to publish post');
          case 11:
            _context.next = 13;
            break;
          case 12:
            _context.prev = 12;
            _t2 = _context["catch"](4);
            console.error("An error occurred:", _t2);
            sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
              icon: 'error',
              title: 'Oops...',
              text: (_t2 === null || _t2 === void 0 ? void 0 : (_error$response = _t2.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message) || _t2.message || 'There was an error processing your request. Please try again.'
            });
          case 13:
            _context.prev = 13;
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            return _context.finish(13);
          case 14:
            _context.next = 16;
            break;
          case 15:
            if (elementId && elementId.includes('deleteNotification')) {
              (0,_global_js__WEBPACK_IMPORTED_MODULE_4__.deleteNotification)(elementId);
            }
            // take you to the request card for approval or denial
            else if (e.target.classList.contains('linkRequestCard')) {
              friendRequestSection = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(e.target.getAttribute('data-id'), "_linkRequestCard"));
              if (friendRequestSection) {
                friendRequestSection.scrollIntoView({
                  behavior: "smooth"
                });
              }
            }
          case 16:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[4, 12, 13, 14]]);
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
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
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
  // comment + fullName + image path are user-authored — escape for innerHTML (SEC-2).
  var image = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(imgURL ? "/resources/images/profile/".concat(imgURL) : "/resources/images/profile/avatarM.png");
  var nameSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.toSentenceCase)(fullName));
  var commentSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(comment);
  var counts = (_data$reactions$count = data === null || data === void 0 ? void 0 : (_data$reactions = data.reactions) === null || _data$reactions === void 0 ? void 0 : _data$reactions.counts) !== null && _data$reactions$count !== void 0 ? _data$reactions$count : {};
  var total = (_data$reactions$count2 = data === null || data === void 0 ? void 0 : (_data$reactions2 = data.reactions) === null || _data$reactions2 === void 0 ? void 0 : (_data$reactions2$coun = _data$reactions2.counts) === null || _data$reactions2$coun === void 0 ? void 0 : _data$reactions2$coun.totalReactions) !== null && _data$reactions$count2 !== void 0 ? _data$reactions$count2 : 0;
  return "<div class=\"d-flex mb-3 commentDiv align-items-start\" data-commentDiv-no=\"".concat(comment_no, "\" id=\"commentDiv").concat(comment_no, "\" name=\"commentDiv\">\n\n  <img src=\"").concat(image, "\" alt=\"Avatar\" class=\"rounded-circle me-2 commentImg\" width=\"32\" height=\"32\">\n\n  <div class=\"flex-grow-1\">\n    <div class=\"d-flex justify-content-between align-items-center\">\n      <small><strong>").concat(nameSafe, "</strong></small>\n      <small class=\"text-muted commentTiming\" datetime=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(date_created), "\" title=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(date_created), "\">\n        ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), "\n      </small>\n    </div>\n\n    <div class=\"comment-text mb-2 p-3 shadow-sm\" style=\"background-color: var(--hover-color); border-radius: 18px; border-top-left-radius: 4px; display: inline-block;\">\n      <span style=\"font-size: 0.95rem; color: var(--text-color);\">").concat(commentSafe, "</span>\n    </div>\n\n      <div class=\"d-flex reaction-preview-section align-items-center mb-2 gap-2\"> \n\n        <div class=\"reaction-preview\" id=\"reaction-preview-").concat(comment_no, "\">\n        ").concat((0,_showEmojiOnComment_js__WEBPACK_IMPORTED_MODULE_3__.renderTopReactions)(counts, comment_no), "\n        </div>\n\n         <div class=\"reaction-summary\" data-comment-no=\"").concat(comment_no, "\" role=\"tooltip\" id=\"reaction-summary-").concat(comment_no, "\" style=\"display:none;\">\n        </div>\n\n      </div>\n\n      <div class=\"comment-actions d-flex gap-3\">         \n                <div class=\"reaction-bar\"  id=\"reaction-bar-").concat(comment_no, "\">\n\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Like\" id=\"reaction-option-like-").concat(comment_no, "\" data-reaction=\"like\" data-label=\"likes\"> \uD83D\uDC4D </div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Love\" id=\"reaction-option-love-").concat(comment_no, "\" data-reaction=\"love\" data-label=\"love\">\u2764\uFE0F</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Haha\" id=\"reaction-option-haha-").concat(comment_no, "\" data-reaction=\"haha\" data-label=\"haha\">\uD83D\uDE04</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Wow\" id=\"reaction-option-wow-").concat(comment_no, "\" data-reaction=\"wow\" data-label=\"wow\">\uD83D\uDE2E</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Sad\" id=\"reaction-option-sad-").concat(comment_no, "\" data-reaction=\"sad\" data-label=\"sad\">\uD83D\uDE22</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Angry\" id=\"reaction-option-angry-").concat(comment_no, "\"\n                     data-reaction=\"angry\" data-label=\"angry\">\uD83D\uDE20</div>\n                </div>\n\n                <div class=\"reaction-button like-button-").concat(comment_no, "\" id=\"like-button-").concat(comment_no, "\" data-comment-no=\"").concat(comment_no, "\">\n                    <i class=\"bi bi-hand-thumbs-up reaction-icon\" id=\"like-icon-").concat(comment_no, "\"></i>\n                    <span>Like</span>\n                     <div class=\"reaction-count\" id=\"like-count-").concat(comment_no, "\">").concat(total, "</div>\n                   \n                </div>\n\n                ").concat(reqId == id || reqId == postId ? "<button class=\"btn btn-sm btn-icon text-danger\" id=\"removeComment(".concat(comment_no, ")\" title=\"Remove\">\n                    <i class=\"bi bi-trash\" id=\"removeCommentIcon").concat(comment_no, "\"></i>\n                    </button>") : '', "        \n      </div>\n  </div>\n</div><hr>");
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
  var commentContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.id)(idDiv);
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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_2__);






var FORM_ID = 'editProfileFormModal';
var BTN_ID = 'editProfileBtnModal';
var NOTIFICATION_ID = "".concat(FORM_ID, "_notification");
var el = function el(elId) {
  return document.getElementById(elId);
};

/**
 * Paint the inline notification bar inside the modal.
 * @param {'success'|'danger'} type
 * @param {string} message
 */
var setNotification = function setNotification(type, message) {
  var box = el(NOTIFICATION_ID);
  if (!box) return;
  box.className = "notification alert ".concat(type === 'success' ? 'alert-success bg-success' : 'alert-danger bg-danger', " text-white");
  box.innerHTML = message;
  box.style.display = 'block';
  var body = box.closest('.modal-body');
  if (body) body.scrollTop = 0; // bring the (scrollable) modal body back to the top
};
var clearNotification = function clearNotification() {
  var box = el(NOTIFICATION_ID);
  if (!box) return;
  box.style.display = 'none';
  box.innerHTML = '';
  box.className = '';
};
var setLoader = function setLoader(show) {
  var loader = el('setLoader');
  if (!loader) return;
  loader.innerHTML = show ? "<div class=\"spinner-border spinner-border-sm text-success\" role=\"status\"><span class=\"visually-hidden\">Saving\u2026</span></div> <span class=\"text-white-50 small\">Saving your changes\u2026</span>" : '';
  loader.style.display = show ? 'block' : 'none';
};
var submitting = false;
function handleSave(_x) {
  return _handleSave.apply(this, arguments);
} // Delegated: the "Create Post"/profile chunk can execute before the modal markup
// is parsed, so binding straight to #editProfileBtnModal at load time is racy.
function _handleSave() {
  _handleSave = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(btn) {
    var form, originalBtnText, formData, _response$data, response, message, _error$response, _error$response$data, _error$response2, _error$response2$data, raw, _message, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          if (!submitting) {
            _context.next = 1;
            break;
          }
          return _context.abrupt("return");
        case 1:
          form = el(FORM_ID);
          if (form) {
            _context.next = 2;
            break;
          }
          return _context.abrupt("return");
        case 2:
          submitting = true;
          originalBtnText = btn ? btn.innerHTML : '';
          clearNotification();
          setLoader(true);
          if (btn) {
            btn.disabled = true;
            btn.innerHTML = "<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> Saving\u2026";
          }
          formData = new FormData(form);
          formData.delete('submit');
          _context.prev = 3;
          _context.next = 4;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/member/profilePage/editProfile', formData, {
            withCredentials: true,
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN'
          });
        case 4:
          response = _context.sent;
          message = (response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'Your profile has been updated.';
          setNotification('success', message);
          sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Profile updated successfully',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
          });

          // Reload so the rest of the page picks up the new values.
          setTimeout(function () {
            return window.location.assign('/profilePage');
          }, 800);
          _context.next = 6;
          break;
        case 5:
          _context.prev = 5;
          _t = _context["catch"](3);
          console.error('Edit Profile Submit Error:', _t);
          raw = (_t === null || _t === void 0 ? void 0 : (_error$response = _t.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message) || (_t === null || _t === void 0 ? void 0 : (_error$response2 = _t.response) === null || _error$response2 === void 0 ? void 0 : (_error$response2$data = _error$response2.data) === null || _error$response2$data === void 0 ? void 0 : _error$response2$data.error) || (_t === null || _t === void 0 ? void 0 : _t.message) || 'Failed to update profile. Please try again.';
          _message = Array.isArray(raw) ? raw : [raw];
          setNotification('danger', _message.join('<br>'));

          // Toast (not a blocking modal) so the inline notification stays uncovered.
          sweetalert2__WEBPACK_IMPORTED_MODULE_2___default().fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Update failed',
            text: _message.join(' '),
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
          });
          submitting = false;
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
          }
          setLoader(false);
        case 6:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[3, 5]]);
  }));
  return _handleSave.apply(this, arguments);
}
document.addEventListener('click', function (e) {
  var target = e.target instanceof Element ? e.target.closest("#".concat(BTN_ID)) : null;
  if (!target) return;
  e.preventDefault();
  handleSave(target);
});

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
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");



var handleReaction = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(postNo, reactionType) {
    var token, formData, response, data, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          token = (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)();
          formData = new FormData();
          formData.append('post_no', postNo);
          formData.append('reaction_type', reactionType);
          formData.append('token', token);
          _context.next = 1;
          return fetch('/api/engagement/react', {
            method: 'POST',
            headers: {
              'X-XSRF-TOKEN': token
            },
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
    var postNo,
      token,
      formData,
      response,
      data,
      _args2 = arguments,
      _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          postNo = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : '';
          _context2.prev = 1;
          token = (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)();
          formData = new FormData();
          formData.append('option_id', optionId);
          if (postNo) formData.append('post_no', postNo);
          formData.append('token', token);
          _context2.next = 2;
          return fetch('/api/engagement/vote', {
            method: 'POST',
            headers: {
              'X-XSRF-TOKEN': token
            },
            body: formData
          });
        case 2:
          response = _context2.sent;
          _context2.next = 3;
          return response.json();
        case 3:
          data = _context2.sent;
          return _context2.abrupt("return", data);
        case 4:
          _context2.prev = 4;
          _t2 = _context2["catch"](1);
          console.error('Vote error:', _t2);
          return _context2.abrupt("return", null);
        case 5:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[1, 4]]);
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
              htmlStr += (0,_html__WEBPACK_IMPORTED_MODULE_3__.html)(memory, []);
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



var eventHtml = function eventHtml(data) {
  // sender_name + notification_* are user-authored event fields — escape (SEC-2).
  return "<p class='eventInfo'>\n            <strong>RSVP: </strong> ".concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(data.sender_name), "</p>\n            <p class='eventInfo'><strong>Event: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(data.notification_name), "</p>\n            <p class='eventInfo'><strong>Date: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)((0,_global__WEBPACK_IMPORTED_MODULE_0__.date2String)(data.notification_date)), " </p>\n            <p class='eventInfo'><strong>Type: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(data.notification_type), "</p>\n            <p class='eventInfo'><strong>Description: </strong> ").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(data.notification_content), "</p>\n            <input type='hidden' name='event_no' id='event").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(data.no), "' value='").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(data.no), "'>\n\n\n           <hr>");

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
/* harmony import */ var _videoParser__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./videoParser */ "./resources/asset/js/components/profilePage/videoParser.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");








var renderVideoPlayer = function renderVideoPlayer(postMessage) {
  var video = (0,_videoParser__WEBPACK_IMPORTED_MODULE_6__.extractVideoFromText)(postMessage);
  if (!video) return '';
  if (video.type === 'youtube' || video.type === 'vimeo' || video.type === 'cloudflare') {
    return "\n      <div class=\"video-embed-container mb-3\" style=\"border-radius: 12px; overflow: hidden; max-height: 420px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);\">\n        <div class=\"ratio ratio-16x9\">\n          <iframe src=\"".concat((0,_global__WEBPACK_IMPORTED_MODULE_7__.esc)(video.embedUrl), "\"\n                  title=\"Video Player\"\n                  frameborder=\"0\" \n                  allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" \n                  allowfullscreen \n                  loading=\"lazy\"\n                  style=\"width: 100%; height: 100%; border: none; border-radius: 12px;\"></iframe>\n        </div>\n      </div>\n    ");
  } else if (video.type === 'direct') {
    return "\n      <div class=\"video-embed-container mb-3\" style=\"border-radius: 12px; overflow: hidden; max-height: 420px;\">\n        <div class=\"ratio ratio-16x9\">\n          <video src=\"".concat((0,_global__WEBPACK_IMPORTED_MODULE_7__.esc)(video.embedUrl), "\" controls preload=\"metadata\" style=\"width: 100%; height: 100%; object-fit: contain; background: #000; border-radius: 12px;\"></video>\n        </div>\n      </div>\n    ");
  }
  return '';
};
var html = function html(el) {
  var comment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var post_no = el.post_no,
    postMessage = el.postMessage;
  var video = (0,_videoParser__WEBPACK_IMPORTED_MODULE_6__.extractVideoFromText)(postMessage);
  var displayMsg = (0,_videoParser__WEBPACK_IMPORTED_MODULE_6__.cleanPostMessage)(postMessage, video);
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin post".concat(post_no, "\"><br>\n\n      ").concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(el), "\n\n    <hr class=\"w3-clear\">\n\n    ").concat(displayMsg ? "<p class=\"postFont\"> ".concat((0,_global__WEBPACK_IMPORTED_MODULE_7__.esc)(displayMsg), " </p>") : '', "\n\n     ").concat(renderVideoPlayer(postMessage), "\n\n     ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(el), "\n     \n     ").concat((0,_htmlFolder_engagementHtml__WEBPACK_IMPORTED_MODULE_5__.renderPoll)(el === null || el === void 0 ? void 0 : el.poll), "\n     ").concat((0,_htmlFolder_engagementHtml__WEBPACK_IMPORTED_MODULE_5__.renderReactions)(el), "\n\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(el), "\n\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(el), "\n\n    <div id = 'showComment").concat(post_no, "'>\n\n      ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment), "\n      \n    </div><br>\n  </div>");
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
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");


var renderPoll = function renderPoll(poll) {
  if (!poll || !poll.options || !Array.isArray(poll.options)) return '';

  // Poll question + option text are user-authored — escape for innerHTML (SEC-2).
  var questionText = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(poll.question || '');
  var html = "<div class=\"poll-container mt-3 p-3 bg-light rounded border border-light-subtle\">\n        <h6 class=\"fw-bold mb-3\"><i class=\"fa fa-bar-chart me-2 text-primary\"></i>".concat(questionText, "</h6>\n        <div class=\"poll-options\">");
  poll.options.forEach(function (opt) {
    // Multi-select is supported on backend. user_voted_option_id is an array now
    var isVoted = poll.user_voted_option_id && Array.isArray(poll.user_voted_option_id) && poll.user_voted_option_id.includes(opt.option_id);
    var percentage = (opt === null || opt === void 0 ? void 0 : opt.percentage) || 0;
    var barWidth = percentage + '%';
    var bgClass = isVoted ? 'bg-primary' : 'bg-secondary';
    var optionText = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)((opt === null || opt === void 0 ? void 0 : opt.option_text) || '');
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
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");


var timeAgo = function timeAgo(x) {
  return x ? (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x) : '';
};
var fullName = function fullName(name) {
  return "<h6 id=\"fullName\"><b>".concat((0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(name || 'Unknown User'), "</b> </h6>");
};
var postedAt = function postedAt(date) {
  if (!(date !== null && date !== void 0 && date.date_created) || !(date !== null && date !== void 0 && date.post_time)) return '';
  return "<div class=\"timeago postTimeCal w3-right w3-opacity\" datetime='".concat((0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(date.date_created), "' title='").concat((0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date.date_created)), "'> ").concat(timeAgo(date.post_time), "</div>");
};
var familyBadge = function familyBadge(famCode) {
  return famCode ? "<span class=\"w3-badge w3-small w3-blue w3-margin-left\" style=\"font-weight: normal; padding: 2px 6px;\">Family: ".concat((0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(famCode), "</span>") : '';
};
var nameImgTiming = function nameImgTiming(data) {
  var img = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(data !== null && data !== void 0 && data.profileImg ? "/resources/images/profile/".concat(data.profileImg) : "/public/avatar/avatarF.png");
  var pId = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)((data === null || data === void 0 ? void 0 : data.post_no) || '');
  var uId = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)((data === null || data === void 0 ? void 0 : data.id) || '');
  var imgParam = encodeURIComponent((data === null || data === void 0 ? void 0 : data.img) || '');
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(imgParam, "&pID=").concat(pId, "&path=profile&id=").concat(uId, "\"> <img src=\"").concat(img, "\" alt=\"img\" class=\"w3-left w3-circle w3-margin-right postImg\" style=\"width:60px\">\n        </a>\n        ").concat(postedAt(data), "\n        <div style=\"display: flex; align-items: center;\">\n            ").concat(fullName(data === null || data === void 0 ? void 0 : data.fullName), " ").concat(familyBadge(data === null || data === void 0 ? void 0 : data.postFamCode), "\n        </div>");
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
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");

var showPostImg = function showPostImg(data) {
  // GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE
  var postImagesWithValues = Object.keys(data).filter(function (key) {
    return key.startsWith('post_img') && data[key] !== null;
  }).map(function (el) {
    return data[el];
  });
  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    var nameSafe = (0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(imgElement);
    return "\n    <a href=\"/profilepage/img?dir=img&pics=".concat(encodeURIComponent(imgElement), "&pID=").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(postNo), "&path=post\">\n      <div class=\"w3-half\">\n        <img src=\"/resources/images/post/").concat(nameSafe, "\" style=\"width:100%\" alt=\"images").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(i), "\" class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(i), "\">\n      </div>\n    </a>\n  ");
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
/* harmony import */ var _engagement__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./engagement */ "./resources/asset/js/components/profilePage/engagement.js");
/* harmony import */ var _reels_reelsPlayer__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ../reels/reelsPlayer */ "./resources/asset/js/components/reels/reelsPlayer.js");
/* harmony import */ var _kinship_kinshipRadar__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ../kinship/kinshipRadar */ "./resources/asset/js/components/kinship/kinshipRadar.js");


localStorage.removeItem('redirect');


if (window.Alpine && typeof window.Alpine.data === 'function') {
  window.Alpine.data('profileFeed', _feedComponent__WEBPACK_IMPORTED_MODULE_0__.profileFeed);
  window.Alpine.data('profileSidebar', _sidebarComponents__WEBPACK_IMPORTED_MODULE_1__.profileSidebar);
  window.Alpine.data('upcomingEvents', _sidebarComponents__WEBPACK_IMPORTED_MODULE_1__.upcomingEvents);
}
window.profileFeed = _feedComponent__WEBPACK_IMPORTED_MODULE_0__.profileFeed;
window.profileSidebar = _sidebarComponents__WEBPACK_IMPORTED_MODULE_1__.profileSidebar;
window.upcomingEvents = _sidebarComponents__WEBPACK_IMPORTED_MODULE_1__.upcomingEvents;







// import "./friendRequestCard"  // Disabled in favor of Alpine.js profileSidebar component


// import "./commentEmojiTest"




document.addEventListener('DOMContentLoaded', function () {
  (0,_engagement__WEBPACK_IMPORTED_MODULE_11__.initEngagementListeners)();
  (0,_engagement__WEBPACK_IMPORTED_MODULE_11__.initMemories)();
});

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
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _emojiPicker_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../emojiPicker.js */ "./resources/asset/js/components/emojiPicker.js");
/* harmony import */ var _fileUploadPreview__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../fileUploadPreview */ "./resources/asset/js/components/fileUploadPreview.js");
/* harmony import */ var _videoParser__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./videoParser */ "./resources/asset/js/components/profilePage/videoParser.js");


// Import a helper function to get elements by ID (assumed from your shared utils)



// Get references to DOM elements used in the emoji picker and image preview - SHOW EMOJIs FOR POST

var emojiToggle = document.getElementById('emojiPost'); // Button to show/hide emoji picker
var emojiContainer = document.getElementById('emojiPickerContainer'); // Container for emoji picker
var closeEmojiBtn = document.getElementById('closeEmojiPicker'); // Close button

if (document.getElementById('emojiListPost')) {
  (0,_emojiPicker_js__WEBPACK_IMPORTED_MODULE_2__.showEmojiPicker)('emojiListPost', 'data-emoji-target');
}
if (emojiToggle && emojiContainer) {
  (0,_emojiPicker_js__WEBPACK_IMPORTED_MODULE_2__.initEmojiPickerUX)('emojiPost', 'emojiPickerContainer');
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
  (0,_fileUploadPreview__WEBPACK_IMPORTED_MODULE_3__.imagePreview)('imageUpload', 'imagePreviewList', 'postModalImgFileNames', 'imagePreviewContainer', 'closeImagePreview');
}

// Poll Creation UI Logic
var addPollBtn = document.getElementById('addPollBtn');
var pollContainer = document.getElementById('pollCreationContainer');
var addOptionBtn = document.getElementById('addPollOptionBtn');
var optionsContainer = document.getElementById('pollOptionsContainer');
var removePollBtn = document.getElementById('removePollBtn');
var MAX_POLL_OPTIONS = 6;
var closePoll = function closePoll() {
  if (!pollContainer) return;
  pollContainer.classList.add('d-none');
  if (addPollBtn) addPollBtn.classList.remove('poll-active');
  // Reset to two blank options
  pollContainer.querySelectorAll('input').forEach(function (input) {
    return input.value = '';
  });
  if (optionsContainer) {
    var extras = optionsContainer.querySelectorAll('.poll-builder__option');
    extras.forEach(function (el, i) {
      if (i > 1) el.remove();
    });
  }
};
if (addPollBtn && pollContainer) {
  addPollBtn.addEventListener('click', function () {
    var isHidden = pollContainer.classList.contains('d-none');
    if (isHidden) {
      pollContainer.classList.remove('d-none');
      addPollBtn.classList.add('poll-active');
      var questionInput = pollContainer.querySelector('input[name="poll_question"]');
      if (questionInput) setTimeout(function () {
        return questionInput.focus();
      }, 60);
    } else {
      closePoll();
    }
  });
  if (removePollBtn) {
    removePollBtn.addEventListener('click', closePoll);
  }
  if (addOptionBtn && optionsContainer) {
    addOptionBtn.addEventListener('click', function () {
      var current = optionsContainer.querySelectorAll('.poll-builder__option').length;
      if (current >= MAX_POLL_OPTIONS) return;
      var input = document.createElement('input');
      input.type = 'text';
      input.name = 'poll_options[]';
      input.className = 'poll-builder__option';
      input.placeholder = "Option ".concat(current + 1);
      input.maxLength = 80;
      optionsContainer.appendChild(input);
      input.focus();
      if (current + 1 >= MAX_POLL_OPTIONS) {
        addOptionBtn.style.display = 'none';
      }
    });
  }
}

// Expose so the submit handler can reset the builder after a successful post
window.__resetPollBuilder = closePoll;

// Video Embed UI Logic

var addVideoBtn = document.getElementById('addVideoBtn');
var videoContainer = document.getElementById('videoEmbedContainer');
var videoInput = document.getElementById('postVideoInput');
var videoDirectFileInput = document.getElementById('videoDirectFileInput');
var removeVideoBtn = document.getElementById('removeVideoBtn');
var videoLivePreview = document.getElementById('videoLivePreview');
var videoUploadProgressWrapper = document.getElementById('videoUploadProgressWrapper');
var videoProgressBar = document.getElementById('videoProgressBar');
var videoUploadStatus = document.getElementById('videoUploadStatus');
var videoUploadPercent = document.getElementById('videoUploadPercent');
var postMessageArea = document.getElementById('postMessage');
var closeVideoBuilder = function closeVideoBuilder() {
  if (!videoContainer) return;
  videoContainer.classList.add('d-none');
  if (addVideoBtn) addVideoBtn.classList.remove('video-active');
  if (videoInput) videoInput.value = '';
  if (videoDirectFileInput) videoDirectFileInput.value = '';
  if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
  if (videoLivePreview) {
    videoLivePreview.innerHTML = '';
    videoLivePreview.classList.add('d-none');
  }
};
if (addVideoBtn && videoContainer) {
  addVideoBtn.addEventListener('click', function () {
    var isHidden = videoContainer.classList.contains('d-none');
    if (isHidden) {
      videoContainer.classList.remove('d-none');
      addVideoBtn.classList.add('video-active');
      if (videoInput) setTimeout(function () {
        return videoInput.focus();
      }, 60);
    } else {
      closeVideoBuilder();
    }
  });
  if (removeVideoBtn) {
    removeVideoBtn.addEventListener('click', closeVideoBuilder);
  }

  // Direct Video File Upload to Cloudflare Stream
  if (videoDirectFileInput) {
    videoDirectFileInput.addEventListener('change', /*#__PURE__*/function () {
      var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
        var file, clipDuration, durationLabel, expirySelect, expirySeconds, tokenRes, tokenData, formData, xhr, _t, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              file = e.target.files[0];
              if (file) {
                _context.next = 1;
                break;
              }
              return _context.abrupt("return");
            case 1:
              if (!(file.size > 100 * 1024 * 1024)) {
                _context.next = 2;
                break;
              }
              alert('Video size exceeds 100MB. Please select a shorter video clip.');
              videoDirectFileInput.value = '';
              return _context.abrupt("return");
            case 2:
              // Enforce strict 30-second duration cap via browser metadata check
              clipDuration = null;
              _context.prev = 3;
              _context.next = 4;
              return new Promise(function (resolve) {
                var tempVideo = document.createElement('video');
                tempVideo.preload = 'metadata';
                var blobUrl = URL.createObjectURL(file);
                tempVideo.src = blobUrl;
                tempVideo.onloadedmetadata = function () {
                  URL.revokeObjectURL(blobUrl);
                  resolve(tempVideo.duration);
                };
                tempVideo.onerror = function () {
                  URL.revokeObjectURL(blobUrl);
                  resolve(null);
                };
              });
            case 4:
              clipDuration = _context.sent;
              if (!(clipDuration && clipDuration > 30.5)) {
                _context.next = 5;
                break;
              }
              if (window.Swal) {
                window.Swal.fire({
                  icon: 'warning',
                  title: '30-Second Video Limit',
                  html: "\n                                <div style=\"text-align: left; font-size: 0.95rem;\">\n                                    <p>Direct video uploads on FamilyPlatform are limited to a maximum of <strong>30 seconds</strong> to keep family feeds fast and engaging.</p>\n                                    <div style=\"background: #FFF3CD; border-left: 4px solid #FFC107; padding: 10px 14px; border-radius: 6px; margin: 12px 0;\">\n                                        <span style=\"color: #856404; font-weight: 600;\">Selected Video Duration:</span> \n                                        <span style=\"color: #DC3545; font-weight: 700;\">".concat(Math.round(clipDuration), " seconds</span>\n                                    </div>\n                                    <p class=\"text-muted mb-0\" style=\"font-size: 0.85rem;\">Please trim your clip to 30 seconds or choose a shorter video.</p>\n                                </div>\n                            "),
                  confirmButtonText: 'Understood',
                  confirmButtonColor: '#0056b3'
                });
              } else {
                alert("Video duration (".concat(Math.round(clipDuration), "s) exceeds the maximum limit of 30 seconds. Please select or trim a shorter clip."));
              }
              videoDirectFileInput.value = '';
              return _context.abrupt("return");
            case 5:
              _context.next = 7;
              break;
            case 6:
              _context.prev = 6;
              _t = _context["catch"](3);
              console.warn('Video duration check skipped:', _t);
            case 7:
              if (videoUploadProgressWrapper) {
                videoUploadProgressWrapper.classList.remove('d-none');
                if (videoProgressBar) videoProgressBar.style.width = '10%';
                durationLabel = clipDuration ? "Clip: ".concat(Math.round(clipDuration), "s \u2022 ") : '';
                if (videoUploadStatus) videoUploadStatus.textContent = "".concat(durationLabel, "Requesting secure upload token...");
                if (videoUploadPercent) videoUploadPercent.textContent = '10%';
              }

              // Read selected video expiration
              expirySelect = document.getElementById('videoExpirySelect');
              expirySeconds = expirySelect ? parseInt(expirySelect.value, 10) : 2592000;
              _context.prev = 8;
              _context.next = 9;
              return fetch('/api/video/direct-upload', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                  maxDuration: 30,
                  expirySeconds: expirySeconds
                })
              });
            case 9:
              tokenRes = _context.sent;
              _context.next = 10;
              return tokenRes.json();
            case 10:
              tokenData = _context.sent;
              if (!(!tokenRes.ok || !tokenData.success || !tokenData.uploadUrl)) {
                _context.next = 11;
                break;
              }
              if (tokenRes.status === 403 && Swal) {
                Swal.fire({
                  icon: 'warning',
                  title: '👑 Premium Retention Required',
                  text: tokenData.error || '1 Year and Permanent video retention are available exclusively for Premium members.',
                  confirmButtonText: 'Select 30 Days',
                  confirmButtonColor: '#1e6040'
                });
                if (expirySelect) expirySelect.value = '2592000';
              }
              throw new Error(tokenData.error || 'Failed to obtain direct upload URL.');
            case 11:
              // 2. Upload file directly to Cloudflare Stream via XMLHttpRequest for progress tracking
              if (videoUploadStatus) videoUploadStatus.textContent = 'Uploading directly to Cloudflare edge...';
              formData = new FormData();
              formData.append('file', file);
              xhr = new XMLHttpRequest();
              xhr.open('POST', tokenData.uploadUrl, true);
              xhr.upload.onprogress = function (evt) {
                if (evt.lengthComputable && videoProgressBar && videoUploadPercent) {
                  var percent = Math.round(evt.loaded / evt.total * 100);
                  videoProgressBar.style.width = "".concat(percent, "%");
                  videoUploadPercent.textContent = "".concat(percent, "%");
                }
              };
              xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                  var streamUrl = tokenData.streamUrl || "https://iframe.videodelivery.net/".concat(tokenData.videoId);
                  if (videoUploadStatus) videoUploadStatus.textContent = 'Upload complete! Video ready to stream.';
                  if (videoProgressBar) {
                    videoProgressBar.style.width = '100%';
                    videoProgressBar.classList.remove('progress-bar-animated');
                  }
                  if (videoInput) videoInput.value = streamUrl;

                  // Render live player preview
                  if (videoLivePreview) {
                    videoLivePreview.innerHTML = "<iframe src=\"".concat(streamUrl, "\" style=\"width:100%; height:100%; border:none;\" allowfullscreen></iframe>");
                    videoLivePreview.classList.remove('d-none');
                  }

                  // Auto-append URL to post text with clean line separation
                  if (postMessageArea && !postMessageArea.value.includes(streamUrl)) {
                    postMessageArea.value = postMessageArea.value ? "".concat(postMessageArea.value.trim(), "\n\n").concat(streamUrl) : streamUrl;
                  }
                } else {
                  alert('Cloudflare upload error. Please try again.');
                  if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
                }
              };
              xhr.onerror = function () {
                alert('Network error during video upload. Please check your internet connection.');
                if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
              };
              xhr.send(formData);
              _context.next = 13;
              break;
            case 12:
              _context.prev = 12;
              _t2 = _context["catch"](8);
              console.warn('[VideoUpload] Direct upload failed:', _t2);
              if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
            case 13:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[3, 6], [8, 12]]);
      }));
      return function (_x) {
        return _ref.apply(this, arguments);
      };
    }());
  }

  // Handle Expiration Select change for Premium upgrade hints
  var videoExpirySelectEl = document.getElementById('videoExpirySelect');
  if (videoExpirySelectEl) {
    videoExpirySelectEl.addEventListener('change', function (e) {
      var selectedOpt = e.target.options[e.target.selectedIndex];
      if (selectedOpt && selectedOpt.getAttribute('data-premium') === 'true') {
        if (window.Swal) {
          Swal.fire({
            icon: 'info',
            title: '👑 Premium Video Retention',
            html: '1 Year and Permanent video memories are available for <strong>Premium Members</strong>.<br><small class="text-muted">Free members enjoy up to 30 days retention.</small>',
            confirmButtonText: 'Got It',
            confirmButtonColor: '#1e6040'
          });
        }
      }
    });
  }
  if (videoInput && videoLivePreview) {
    videoInput.addEventListener('input', function (e) {
      var url = e.target.value.trim();
      var parsed = (0,_videoParser__WEBPACK_IMPORTED_MODULE_4__.parseVideoUrl)(url);
      if (parsed) {
        var previewHtml = '';
        if (parsed.type === 'youtube' || parsed.type === 'vimeo' || parsed.type === 'cloudflare') {
          previewHtml = "<iframe src=\"".concat(parsed.embedUrl, "\" style=\"width:100%; height:100%; border:none;\" allowfullscreen></iframe>");
        } else if (parsed.type === 'direct') {
          previewHtml = "<video src=\"".concat(parsed.embedUrl, "\" controls style=\"width:100%; height:100%; object-fit:contain; background:#000;\"></video>");
        }
        videoLivePreview.innerHTML = previewHtml;
        videoLivePreview.classList.remove('d-none');

        // Auto-append URL to post text if not already there with clean spacing
        if (postMessageArea && !postMessageArea.value.includes(url)) {
          postMessageArea.value = postMessageArea.value ? "".concat(postMessageArea.value.trim(), "\n\n").concat(url) : url;
        }
      } else {
        videoLivePreview.innerHTML = '';
        videoLivePreview.classList.add('d-none');
      }
    });
  }
}
window.__resetVideoBuilder = closeVideoBuilder;

/***/ }),

/***/ "./resources/asset/js/components/profilePage/registerPushNotification.js":
/*!*******************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/registerPushNotification.js ***!
  \*******************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "disablePushNotifications": function() { return /* binding */ disablePushNotifications; },
/* harmony export */   "enablePushNotifications": function() { return /* binding */ enablePushNotifications; },
/* harmony export */   "getPushState": function() { return /* binding */ getPushState; },
/* harmony export */   "isPushSubscribed": function() { return /* binding */ isPushSubscribed; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


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
    var payload;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          payload = {
            endpoint: subscription.endpoint,
            keys: {
              p256dh: keyToBase64(subscription, 'p256dh'),
              auth: keyToBase64(subscription, 'auth')
            }
          };
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/pushNotification/subscription', payload, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)(),
              'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)()
            }
          });
        case 1:
        case "end":
          return _context2.stop();
      }
    }, _callee2);
  }));
  return _syncToServer.apply(this, arguments);
}
if (pushSupported()) {
  navigator.serviceWorker.register('/service-worker.js').then(function (swReg) {
    return swReg.pushManager.getSubscription().then(function (sub) {
      if (sub) return syncToServer(sub).catch(function (e) {
        return console.warn('[push] resync failed', e);
      });
      if (Notification.permission === 'granted' && VAPID_PUBLIC_KEY) {
        return doSubscribe(swReg);
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
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/pushNotification/unsubscribe', {
            endpoint
          }, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)(),
              'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)()
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

/***/ "./resources/asset/js/components/reels/reelsPlayer.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/reels/reelsPlayer.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");








var FamilyReelsPlayer = /*#__PURE__*/function () {
  function FamilyReelsPlayer() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, FamilyReelsPlayer);
    this.reels = [];
    this.currentIndex = 0;
    this.isMuted = false;
    this.isLoading = false;
    this.isCommentsOpen = false;
    this.touchStartY = 0;
    this.touchEndY = 0;
    this.viewport = document.getElementById('reelsViewport');
    this.container = document.getElementById('reelsTheaterContainer');
    this.commentsDrawer = document.getElementById('reelCommentsDrawer');
    this.commentsList = document.getElementById('reelCommentsList');
    this.commentForm = document.getElementById('reelCommentForm');
    this.commentInput = document.getElementById('reelCommentInput');
    if (this.viewport || this.container) {
      this.init();
    }
    this.initCreateModal();
  }
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(FamilyReelsPlayer, [{
    key: "init",
    value: function () {
      var _init = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
        var initialDataEl, urlParams, targetId, targetIdx, _t;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.prev = 0;
              // Load initial reels from DOM data attribute or API
              initialDataEl = document.getElementById('reelsInitialData');
              if (initialDataEl) {
                try {
                  this.reels = JSON.parse(initialDataEl.textContent || '[]');
                } catch (e) {
                  this.reels = [];
                }
              }
              if (this.reels.length) {
                _context.next = 1;
                break;
              }
              _context.next = 1;
              return this.fetchReels();
            case 1:
              if (this.reels.length > 0) {
                urlParams = new URLSearchParams(window.location.search);
                targetId = urlParams.get('id');
                if (targetId) {
                  targetIdx = this.reels.findIndex(function (r) {
                    return r.id == targetId;
                  });
                  if (targetIdx !== -1) {
                    this.currentIndex = targetIdx;
                  }
                }
                this.renderCurrentReel();
              } else {
                this.renderEmptyState();
              }
              this.bindEvents();
              _context.next = 3;
              break;
            case 2:
              _context.prev = 2;
              _t = _context["catch"](0);
              console.error('[ReelsPlayer] Init failed:', _t);
            case 3:
            case "end":
              return _context.stop();
          }
        }, _callee, this, [[0, 2]]);
      }));
      function init() {
        return _init.apply(this, arguments);
      }
      return init;
    }()
  }, {
    key: "fetchReels",
    value: function () {
      var _fetchReels = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee2() {
        var _res$data, res, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _context2.prev = 0;
              _context2.next = 1;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].get('/api/reels/feed');
            case 1:
              res = _context2.sent;
              if (((_res$data = res.data) === null || _res$data === void 0 ? void 0 : _res$data.status) === 'success') {
                this.reels = res.data.data || [];
              }
              _context2.next = 3;
              break;
            case 2:
              _context2.prev = 2;
              _t2 = _context2["catch"](0);
              console.error('[ReelsPlayer] Failed to fetch reels:', _t2);
            case 3:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this, [[0, 2]]);
      }));
      function fetchReels() {
        return _fetchReels.apply(this, arguments);
      }
      return fetchReels;
    }()
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;
      // Keyboard Controls
      window.addEventListener('keydown', function (e) {
        var _document$activeEleme, _document$activeEleme2;
        if (['input', 'textarea'].includes((_document$activeEleme = document.activeElement) === null || _document$activeEleme === void 0 ? void 0 : (_document$activeEleme2 = _document$activeEleme.tagName) === null || _document$activeEleme2 === void 0 ? void 0 : _document$activeEleme2.toLowerCase())) return;
        if (e.key === 'ArrowDown' || e.key === 'j') {
          e.preventDefault();
          _this.nextReel();
        } else if (e.key === 'ArrowUp' || e.key === 'k') {
          e.preventDefault();
          _this.prevReel();
        } else if (e.key === ' ' || e.code === 'Space') {
          e.preventDefault();
          _this.togglePlayPause();
        } else if (e.key === 'm' || e.key === 'M') {
          _this.toggleMute();
        }
      });

      // Touch Swipe Gestures
      if (this.viewport) {
        this.viewport.addEventListener('touchstart', function (e) {
          _this.touchStartY = e.changedTouches[0].screenY;
        }, {
          passive: true
        });
        this.viewport.addEventListener('touchend', function (e) {
          _this.touchEndY = e.changedTouches[0].screenY;
          _this.handleSwipe();
        }, {
          passive: true
        });
      }

      // Comment Form Submit
      if (this.commentForm) {
        this.commentForm.addEventListener('submit', function (e) {
          e.preventDefault();
          _this.submitComment();
        });
      }
    }
  }, {
    key: "handleSwipe",
    value: function handleSwipe() {
      var deltaY = this.touchStartY - this.touchEndY;
      if (deltaY > 50) {
        this.nextReel(); // Swiped Up -> Next Reel
      } else if (deltaY < -50) {
        this.prevReel(); // Swiped Down -> Prev Reel
      }
    }
  }, {
    key: "nextReel",
    value: function nextReel() {
      if (this.currentIndex < this.reels.length - 1) {
        this.currentIndex++;
        this.renderCurrentReel();
      } else {
        this.showToast('You have reached the end of family reels!', 'info');
      }
    }
  }, {
    key: "prevReel",
    value: function prevReel() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.renderCurrentReel();
      }
    }
  }, {
    key: "getMediaEmbed",
    value: function getMediaEmbed(videoUrl) {
      if (!videoUrl) {
        return '<div class="d-flex align-items-center justify-content-center h-100 text-white-50 small">No video source</div>';
      }
      var trimmed = videoUrl.trim();

      // YouTube Matcher (watch?v=, youtu.be/, shorts/, embed/)
      var ytRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;
      var ytMatch = trimmed.match(ytRegex);
      if (ytMatch && ytMatch[1]) {
        var videoId = ytMatch[1];
        return "\n            <div class=\"reel-iframe-wrapper\">\n                <iframe \n                    id=\"reelActiveIframe\"\n                    class=\"reel-video-element\"\n                    src=\"https://www.youtube-nocookie.com/embed/".concat(videoId, "?autoplay=1&mute=").concat(this.isMuted ? 1 : 0, "&loop=1&playlist=").concat(videoId, "&controls=1&modestbranding=1&rel=0&playsinline=1&enablejsapi=1\"\n                    frameborder=\"0\"\n                    allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\"\n                    allowfullscreen>\n                </iframe>\n            </div>\n            ");
      }

      // Vimeo Matcher
      var vimeoRegex = /(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)/i;
      var vimeoMatch = trimmed.match(vimeoRegex);
      if (vimeoMatch && (vimeoMatch[3] || vimeoMatch[2])) {
        var vId = vimeoMatch[3] || vimeoMatch[2];
        return "\n            <div class=\"reel-iframe-wrapper\">\n                <iframe \n                    id=\"reelActiveIframe\"\n                    class=\"reel-video-element\"\n                    src=\"https://player.vimeo.com/video/".concat(vId, "?autoplay=1&muted=").concat(this.isMuted ? 1 : 0, "&loop=1&title=0&byline=0&portrait=0\"\n                    frameborder=\"0\"\n                    allow=\"autoplay; fullscreen; picture-in-picture\"\n                    allowfullscreen>\n                </iframe>\n            </div>\n            ");
      }

      // Direct HTML5 Video File (MP4, WebM, MOV, server uploads)
      return "\n        <video class=\"reel-video-element\" id=\"reelActiveVideo\" playsinline webkit-playsinline loop preload=\"auto\" ".concat(this.isMuted ? 'muted' : '', " autoplay>\n            <source src=\"").concat(trimmed, "\" type=\"video/mp4\">\n            <source src=\"").concat(trimmed, "\" type=\"video/webm\">\n            Your browser does not support HTML5 video.\n        </video>\n        ");
    }
  }, {
    key: "renderCurrentReel",
    value: function renderCurrentReel() {
      var _this2 = this,
        _document$getElementB,
        _document$getElementB2,
        _document$getElementB3,
        _document$getElementB4;
      var reel = this.reels[this.currentIndex];
      if (!reel || !this.viewport) return;

      // Close comments drawer when switching
      if (this.isCommentsOpen) this.toggleComments(false);

      // All reel.* string fields are user-authored — escape before innerHTML (SEC-2).
      var fullName = (0,_global__WEBPACK_IMPORTED_MODULE_6__.esc)("".concat(reel.firstName || 'Family', " ").concat(reel.lastName || 'Member').trim());
      var rawAvatar = reel.profilePics ? reel.profilePics.startsWith('/') ? reel.profilePics : "/resources/images/profile/".concat(reel.profilePics) : '/resources/images/profile/avatarM.png';
      var avatarUrl = (0,_global__WEBPACK_IMPORTED_MODULE_6__.esc)(rawAvatar);
      var isLiked = reel.user_reaction === 'like' || !!reel.user_reaction;
      var categoryLabel = (0,_global__WEBPACK_IMPORTED_MODULE_6__.esc)(reel.category ? "#".concat(String(reel.category).toUpperCase()) : '#FAMILY');
      var mediaHtml = this.getMediaEmbed(reel.video_url);
      this.viewport.innerHTML = "\n        <div class=\"reel-card-container\" id=\"activeReelCard\">\n            <!-- Video / Iframe Embed Media -->\n            ".concat(mediaHtml, "\n\n            <!-- Gradient Overlay -->\n            <div class=\"reel-overlay\"></div>\n\n            <!-- Center Feedback Icon -->\n            <div class=\"reel-center-feedback\" id=\"reelCenterFeedback\">\n                <i class=\"bi bi-play-fill\"></i>\n            </div>\n\n            <!-- Top Header Controls -->\n            <div class=\"reel-top-controls\">\n                <a href=\"/profilePage\" class=\"reel-nav-pill-btn\">\n                    <i class=\"bi bi-chevron-left me-1\"></i> Back\n                </a>\n                <div class=\"d-flex align-items-center gap-2\">\n                    <button type=\"button\" class=\"reel-nav-pill-btn\" id=\"btnToggleMute\">\n                        <i class=\"bi ").concat(this.isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill', "\"></i>\n                    </button>\n                    <button type=\"button\" class=\"reel-nav-pill-btn\" data-bs-toggle=\"modal\" data-bs-target=\"#createReelModal\">\n                        <i class=\"bi bi-camera-video-fill me-1 text-warning\"></i> Create\n                    </button>\n                </div>\n            </div>\n\n            <!-- Floating Action Stack (Right) -->\n            <div class=\"reel-action-stack\">\n                <!-- Like Button -->\n                <div class=\"d-flex flex-column align-items-center\">\n                    <button class=\"reel-action-btn ").concat(isLiked ? 'liked' : '', "\" id=\"btnReelLike\" title=\"Like Reel\">\n                        <i class=\"bi ").concat(isLiked ? 'bi-heart-fill' : 'bi-heart', "\"></i>\n                    </button>\n                    <span class=\"reel-action-count\" id=\"reelLikeCount\">").concat(reel.likes_count || 0, "</span>\n                </div>\n\n                <!-- Comment Drawer Button -->\n                <div class=\"d-flex flex-column align-items-center\">\n                    <button class=\"reel-action-btn\" id=\"btnReelComments\" title=\"Family Comments\">\n                        <i class=\"bi bi-chat-dots-fill\"></i>\n                    </button>\n                    <span class=\"reel-action-count\" id=\"reelCommentCount\">").concat(reel.comments_count || 0, "</span>\n                </div>\n\n                <!-- Family Tree Node Jump -->\n                <div class=\"d-flex flex-column align-items-center\">\n                    <a href=\"/organogram/").concat(reel.user_id, "\" class=\"reel-action-btn\" title=\"View on Family Tree\" style=\"text-decoration:none;\">\n                        <i class=\"bi bi-diagram-3-fill text-warning\"></i>\n                    </a>\n                    <span class=\"reel-action-count\">Tree</span>\n                </div>\n\n                <!-- Share Link -->\n                <div class=\"d-flex flex-column align-items-center\">\n                    <button class=\"reel-action-btn\" id=\"btnReelShare\" title=\"Copy Reel Link\">\n                        <i class=\"bi bi-share-fill\"></i>\n                    </button>\n                    <span class=\"reel-action-count\">Share</span>\n                </div>\n            </div>\n\n            <!-- Bottom Metadata Overlay -->\n            <div class=\"reel-bottom-meta\">\n                <div class=\"reel-author-row\">\n                    <img src=\"").concat(avatarUrl, "\" alt=\"").concat(fullName, "\" class=\"reel-author-avatar\">\n                    <div>\n                        <div class=\"reel-author-name\">").concat(fullName, "</div>\n                        <span class=\"reel-tag-pill\"><i class=\"bi bi-stars text-warning\"></i> ").concat(categoryLabel, "</span>\n                    </div>\n                </div>\n\n                <div class=\"reel-caption-text\">").concat((0,_global__WEBPACK_IMPORTED_MODULE_6__.esc)(reel.caption || ''), "</div>\n\n                <div class=\"reel-music-tag\">\n                    <i class=\"bi bi-music-note-beamed text-warning\"></i>\n                    <span>").concat((0,_global__WEBPACK_IMPORTED_MODULE_6__.esc)(reel.music_title || 'Original Family Audio'), " \u2022 ").concat(reel.created_at ? (0,timeago_js__WEBPACK_IMPORTED_MODULE_5__.format)(reel.created_at) : 'Recent', "</span>\n                </div>\n            </div>\n        </div>\n        ");

      // Wire video play / pause & click triggers
      var video = document.getElementById('reelActiveVideo');
      if (video) {
        video.play().catch(function () {
          // Auto-play was prevented (browser policy), prompt click
          _this2.isMuted = true;
          video.muted = true;
          video.play().catch(console.error);
        });

        // Tap on video to toggle play/pause
        video.addEventListener('click', function () {
          return _this2.togglePlayPause();
        });

        // Double click to heart
        var lastTap = 0;
        video.addEventListener('touchend', function (e) {
          var currentTime = new Date().getTime();
          var tapLength = currentTime - lastTap;
          if (tapLength < 300 && tapLength > 0) {
            _this2.toggleLike();
            e.preventDefault();
          }
          lastTap = currentTime;
        });
      }

      // Attach listeners for action buttons
      (_document$getElementB = document.getElementById('btnToggleMute')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.addEventListener('click', function () {
        return _this2.toggleMute();
      });
      (_document$getElementB2 = document.getElementById('btnReelLike')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.addEventListener('click', function () {
        return _this2.toggleLike();
      });
      (_document$getElementB3 = document.getElementById('btnReelComments')) === null || _document$getElementB3 === void 0 ? void 0 : _document$getElementB3.addEventListener('click', function () {
        return _this2.toggleComments(true);
      });
      (_document$getElementB4 = document.getElementById('btnReelShare')) === null || _document$getElementB4 === void 0 ? void 0 : _document$getElementB4.addEventListener('click', function () {
        return _this2.shareReel(reel);
      });
    }
  }, {
    key: "renderEmptyState",
    value: function renderEmptyState() {
      if (!this.viewport) return;
      this.viewport.innerHTML = "\n        <div class=\"d-flex flex-column align-items-center justify-content-center h-100 text-center p-4 text-white\">\n            <div class=\"mb-3 d-flex align-items-center justify-content-center rounded-circle\" style=\"width: 72px; height: 72px; background: rgba(255,255,255,0.1); font-size: 2rem;\">\n                <i class=\"bi bi-camera-reels text-warning\"></i>\n            </div>\n            <h4 class=\"fw-bold mb-2\">No Family Reels Yet</h4>\n            <p class=\"text-white-50 small mb-4\" style=\"max-width: 280px;\">\n                Be the first to record a family milestone, memory, or oral history video!\n            </p>\n            <button type=\"button\" class=\"btn btn-primary fw-bold px-4 py-2\" data-bs-toggle=\"modal\" data-bs-target=\"#createReelModal\" style=\"border-radius: 9999px;\">\n                <i class=\"bi bi-plus-circle-fill me-1\"></i> Record First Reel\n            </button>\n        </div>\n        ";
    }
  }, {
    key: "togglePlayPause",
    value: function togglePlayPause() {
      var video = document.getElementById('reelActiveVideo');
      var feedback = document.getElementById('reelCenterFeedback');
      if (!video) return;
      if (video.paused) {
        video.play();
        if (feedback) {
          feedback.innerHTML = '<i class="bi bi-play-fill"></i>';
          feedback.classList.add('show');
          setTimeout(function () {
            return feedback.classList.remove('show');
          }, 300);
        }
      } else {
        video.pause();
        if (feedback) {
          feedback.innerHTML = '<i class="bi bi-pause-fill"></i>';
          feedback.classList.add('show');
          setTimeout(function () {
            return feedback.classList.remove('show');
          }, 300);
        }
      }
    }
  }, {
    key: "toggleMute",
    value: function toggleMute() {
      this.isMuted = !this.isMuted;
      var video = document.getElementById('reelActiveVideo');
      var muteBtn = document.getElementById('btnToggleMute');
      if (video) video.muted = this.isMuted;
      if (muteBtn) {
        muteBtn.innerHTML = "<i class=\"bi ".concat(this.isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill', "\"></i>");
      }
    }
  }, {
    key: "toggleLike",
    value: function () {
      var _toggleLike = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee3() {
        var reel, likeBtn, likeCount, _res$data2, res, count, _t3;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              reel = this.reels[this.currentIndex];
              if (reel) {
                _context3.next = 1;
                break;
              }
              return _context3.abrupt("return");
            case 1:
              likeBtn = document.getElementById('btnReelLike');
              likeCount = document.getElementById('reelLikeCount');
              _context3.prev = 2;
              _context3.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].post('/api/reels/react', {
                reel_id: reel.id,
                reaction_type: 'like'
              });
            case 3:
              res = _context3.sent;
              if (((_res$data2 = res.data) === null || _res$data2 === void 0 ? void 0 : _res$data2.status) === 'success') {
                count = res.data.count;
                reel.likes_count = count;
                reel.user_reaction = res.data.reaction;
                if (likeCount) likeCount.textContent = count;
                if (likeBtn) {
                  if (res.data.action === 'added' || res.data.action === 'updated') {
                    likeBtn.classList.add('liked');
                    likeBtn.innerHTML = '<i class="bi bi-heart-fill"></i>';
                  } else {
                    likeBtn.classList.remove('liked');
                    likeBtn.innerHTML = '<i class="bi bi-heart"></i>';
                  }
                }
              }
              _context3.next = 5;
              break;
            case 4:
              _context3.prev = 4;
              _t3 = _context3["catch"](2);
              console.error('[ReelsPlayer] Reaction error:', _t3);
            case 5:
            case "end":
              return _context3.stop();
          }
        }, _callee3, this, [[2, 4]]);
      }));
      function toggleLike() {
        return _toggleLike.apply(this, arguments);
      }
      return toggleLike;
    }()
  }, {
    key: "toggleComments",
    value: function () {
      var _toggleComments = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee4() {
        var show,
          _args4 = arguments;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              show = _args4.length > 0 && _args4[0] !== undefined ? _args4[0] : true;
              this.isCommentsOpen = show;
              if (this.commentsDrawer) {
                _context4.next = 1;
                break;
              }
              return _context4.abrupt("return");
            case 1:
              if (!show) {
                _context4.next = 3;
                break;
              }
              this.commentsDrawer.classList.add('open');
              _context4.next = 2;
              return this.loadComments();
            case 2:
              _context4.next = 4;
              break;
            case 3:
              this.commentsDrawer.classList.remove('open');
            case 4:
            case "end":
              return _context4.stop();
          }
        }, _callee4, this);
      }));
      function toggleComments() {
        return _toggleComments.apply(this, arguments);
      }
      return toggleComments;
    }()
  }, {
    key: "loadComments",
    value: function () {
      var _loadComments = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee5() {
        var reel, _res$data3, res, comments, _t4;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context5) {
          while (1) switch (_context5.prev = _context5.next) {
            case 0:
              reel = this.reels[this.currentIndex];
              if (!(!reel || !this.commentsList)) {
                _context5.next = 1;
                break;
              }
              return _context5.abrupt("return");
            case 1:
              this.commentsList.innerHTML = '<div class="text-center py-4 text-white-50"><span class="spinner-border spinner-border-sm me-2"></span>Loading comments...</div>';
              _context5.prev = 2;
              _context5.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].get("/api/reels/comments?reel_id=".concat(reel.id));
            case 3:
              res = _context5.sent;
              comments = ((_res$data3 = res.data) === null || _res$data3 === void 0 ? void 0 : _res$data3.data) || [];
              if (comments.length) {
                _context5.next = 4;
                break;
              }
              this.commentsList.innerHTML = '<div class="text-center py-4 text-white-50 small">No comments yet. Leave the first family note!</div>';
              return _context5.abrupt("return");
            case 4:
              this.commentsList.innerHTML = comments.map(function (c) {
                return "\n            <div class=\"reel-comment-bubble\">\n                <img src=\"".concat((0,_global__WEBPACK_IMPORTED_MODULE_6__.esc)(c.profilePics ? "/resources/images/profile/".concat(c.profilePics) : '/resources/images/profile/avatarM.png'), "\" class=\"reel-comment-avatar\" alt=\"User\">\n                <div class=\"reel-comment-content\">\n                    <div class=\"reel-comment-user\">").concat((0,_global__WEBPACK_IMPORTED_MODULE_6__.esc)("".concat(c.firstName || 'Family', " ").concat(c.lastName || 'Member')), "</div>\n                    <div class=\"reel-comment-text\">").concat((0,_global__WEBPACK_IMPORTED_MODULE_6__.esc)(c.comment || ''), "</div>\n                    <span class=\"reel-comment-time\">").concat(c.created_at ? (0,timeago_js__WEBPACK_IMPORTED_MODULE_5__.format)(c.created_at) : 'Just now', "</span>\n                </div>\n            </div>\n            ");
              }).join('');
              _context5.next = 6;
              break;
            case 5:
              _context5.prev = 5;
              _t4 = _context5["catch"](2);
              this.commentsList.innerHTML = '<div class="text-center py-4 text-danger small">Failed to load comments.</div>';
            case 6:
            case "end":
              return _context5.stop();
          }
        }, _callee5, this, [[2, 5]]);
      }));
      function loadComments() {
        return _loadComments.apply(this, arguments);
      }
      return loadComments;
    }()
  }, {
    key: "submitComment",
    value: function () {
      var _submitComment = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee6() {
        var reel, text, _res$data4, res, countBadge, _t5;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              reel = this.reels[this.currentIndex];
              if (!(!reel || !this.commentInput)) {
                _context6.next = 1;
                break;
              }
              return _context6.abrupt("return");
            case 1:
              text = this.commentInput.value.trim();
              if (text) {
                _context6.next = 2;
                break;
              }
              return _context6.abrupt("return");
            case 2:
              _context6.prev = 2;
              _context6.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].post('/api/reels/comment', {
                reel_id: reel.id,
                comment: text
              });
            case 3:
              res = _context6.sent;
              if (!(((_res$data4 = res.data) === null || _res$data4 === void 0 ? void 0 : _res$data4.status) === 'success')) {
                _context6.next = 4;
                break;
              }
              this.commentInput.value = '';
              reel.comments_count = (reel.comments_count || 0) + 1;
              countBadge = document.getElementById('reelCommentCount');
              if (countBadge) countBadge.textContent = reel.comments_count;
              _context6.next = 4;
              return this.loadComments();
            case 4:
              _context6.next = 6;
              break;
            case 5:
              _context6.prev = 5;
              _t5 = _context6["catch"](2);
              this.showToast('Could not post comment. Please retry.', 'error');
            case 6:
            case "end":
              return _context6.stop();
          }
        }, _callee6, this, [[2, 5]]);
      }));
      function submitComment() {
        return _submitComment.apply(this, arguments);
      }
      return submitComment;
    }()
  }, {
    key: "shareReel",
    value: function shareReel(reel) {
      var _this3 = this;
      var shareUrl = "".concat(window.location.origin, "/reels?id=").concat(reel.id);
      if (navigator.clipboard) {
        navigator.clipboard.writeText(shareUrl).then(function () {
          _this3.showToast('Reel link copied to clipboard!', 'success');
        });
      } else {
        this.showToast(shareUrl, 'info');
      }
    }
  }, {
    key: "showToast",
    value: function showToast(message) {
      var icon = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'info';
      if (typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_4___default()) !== 'undefined') {
        var Toast = sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
        Toast.fire({
          icon,
          title: message
        });
      } else {
        alert(message);
      }
    }
  }, {
    key: "initCreateModal",
    value: function initCreateModal() {
      var fileInput = document.getElementById('reelVideoFileInput');
      var previewContainer = document.getElementById('reelVideoPreviewContainer');
      var previewVideo = document.getElementById('reelPreviewVideo');
      var urlInput = document.getElementById('reelVideoUrlInput');
      var uploadForm = document.getElementById('createReelForm');
      var submitBtn = document.getElementById('btnSubmitReel');
      var dropPrompt = document.getElementById('dropzonePrompt');
      var dropSelected = document.getElementById('dropzoneSelected');
      var dropFileName = document.getElementById('dropFileName');
      if (!uploadForm) return;
      if (fileInput) {
        fileInput.addEventListener('change', function () {
          var _this$files;
          var file = (_this$files = this.files) === null || _this$files === void 0 ? void 0 : _this$files[0];
          if (!file) return;
          if (file.size > 100 * 1024 * 1024) {
            if (typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_4___default()) !== 'undefined') {
              sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
                icon: 'warning',
                title: 'File Too Large',
                text: 'Video file size exceeds the 100MB limit.'
              });
            } else {
              alert('Video file size exceeds 100MB limit.');
            }
            fileInput.value = '';
            return;
          }
          if (dropPrompt && dropSelected && dropFileName) {
            dropPrompt.classList.add('d-none');
            dropSelected.classList.remove('d-none');
            dropFileName.textContent = "".concat(file.name, " (").concat((file.size / (1024 * 1024)).toFixed(1), " MB)");
          }
          var fileObjUrl = URL.createObjectURL(file);
          if (previewContainer && previewVideo) {
            previewVideo.src = fileObjUrl;
            previewContainer.classList.remove('d-none');
            previewVideo.play().catch(console.error);
          }

          // Automatically capture video frame thumbnail using off-screen video and canvas
          var tempVideo = document.createElement('video');
          tempVideo.preload = 'metadata';
          tempVideo.src = fileObjUrl;
          tempVideo.muted = true;
          tempVideo.playsInline = true;
          tempVideo.onloadedmetadata = function () {
            tempVideo.currentTime = Math.min(1.0, (tempVideo.duration || 2) / 2);
          };
          tempVideo.onseeked = function () {
            try {
              var canvas = document.createElement('canvas');
              canvas.width = 360;
              canvas.height = 640;
              var ctx = canvas.getContext('2d');
              if (ctx) {
                ctx.drawImage(tempVideo, 0, 0, canvas.width, canvas.height);
                var thumbData = canvas.toDataURL('image/jpeg', 0.85);
                var thumbInput = document.getElementById('reelThumbnailDataInput');
                if (thumbInput) thumbInput.value = thumbData;
              }
            } catch (e) {
              console.warn('[Reels] Canvas thumbnail extraction skipped:', e);
            }
          };
        });
      }
      uploadForm.addEventListener('submit', /*#__PURE__*/function () {
        var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee7(e) {
          var hasFile, hasUrl, formData, _res$data5, res, _res$data6, _res$data6$data, _error$response, _error$response$data, errMsg, _t6;
          return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context7) {
            while (1) switch (_context7.prev = _context7.next) {
              case 0:
                e.preventDefault();
                hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                hasUrl = urlInput && urlInput.value.trim().length > 0;
                if (!(!hasFile && !hasUrl)) {
                  _context7.next = 1;
                  break;
                }
                if (typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_4___default()) !== 'undefined') {
                  sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
                    icon: 'info',
                    title: 'Select a Video',
                    text: 'Please choose a video file or enter a video URL before publishing.',
                    confirmButtonColor: '#4f46e5'
                  });
                } else {
                  alert('Please choose a video file or enter a video URL before publishing.');
                }
                return _context7.abrupt("return");
              case 1:
                formData = new FormData(uploadForm);
                if (submitBtn) {
                  submitBtn.disabled = true;
                  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publishing Reel...';
                }
                _context7.prev = 2;
                _context7.next = 3;
                return axios__WEBPACK_IMPORTED_MODULE_7__["default"].post('/api/reels/upload', formData, {
                  headers: {
                    'Content-Type': 'multipart/form-data'
                  }
                });
              case 3:
                res = _context7.sent;
                if (!(((_res$data5 = res.data) === null || _res$data5 === void 0 ? void 0 : _res$data5.status) === 'success')) {
                  _context7.next = 5;
                  break;
                }
                if (!(typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_4___default()) !== 'undefined')) {
                  _context7.next = 4;
                  break;
                }
                _context7.next = 4;
                return sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
                  icon: 'success',
                  title: 'Reel Published! 🎉',
                  text: 'Your family reel is now live.',
                  confirmButtonColor: '#4f46e5'
                });
              case 4:
                window.location.href = "/reels?id=".concat(((_res$data6 = res.data) === null || _res$data6 === void 0 ? void 0 : (_res$data6$data = _res$data6.data) === null || _res$data6$data === void 0 ? void 0 : _res$data6$data.id) || '');
              case 5:
                _context7.next = 7;
                break;
              case 6:
                _context7.prev = 6;
                _t6 = _context7["catch"](2);
                errMsg = ((_error$response = _t6.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message) || 'Failed to upload video reel.';
                if (typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_4___default()) !== 'undefined') {
                  sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: errMsg
                  });
                } else {
                  alert(errMsg);
                }
              case 7:
                _context7.prev = 7;
                if (submitBtn) {
                  submitBtn.disabled = false;
                  submitBtn.innerHTML = '<i class="bi bi-camera-video-fill me-1"></i> Publish Reel';
                }
                return _context7.finish(7);
              case 8:
              case "end":
                return _context7.stop();
            }
          }, _callee7, null, [[2, 6, 7, 8]]);
        }));
        return function (_x) {
          return _ref.apply(this, arguments);
        };
      }());
    }
  }]);
}();
function initFamilyReels() {
  new FamilyReelsPlayer();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initFamilyReels);
} else {
  initFamilyReels();
}

/***/ })

}]);
//# sourceMappingURL=profilePage.js.map