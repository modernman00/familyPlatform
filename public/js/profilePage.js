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
/* harmony export */   "imagePreview": function() { return /* binding */ imagePreview; }
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");


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
  var imageInput = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)(fileInputId); // Hidden file input for image uploads
  var previewContainer = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)(previewContainerId); // Wrapper for image previews
  var previewList = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)(previewListId); // Where preview thumbnails are shown
  var fileNamesDisplay = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)(fileNamesDisplayId); // Text display of selected 

  // Helper to update the UI and input files
  var _updatePreviews = function updatePreviews(files) {
    previewList.innerHTML = ''; // Clear previous previews

    if (files.length === 0) {
      previewContainer.classList.add('d-none');
      fileNamesDisplay.textContent = '';
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
      img.style.maxWidth = '100px';
      img.style.maxHeight = '100px';

      // Create remove button
      var removeBtn = document.createElement('button');
      removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 p-0 rounded-circle d-flex align-items-center justify-content-center';
      removeBtn.style.width = '20px';
      removeBtn.style.height = '20px';
      removeBtn.style.transform = 'translate(30%, -30%)';
      removeBtn.innerHTML = '<i class="bi bi-x"></i>';
      removeBtn.onclick = function (e) {
        e.preventDefault(); // Prevent form submission if inside form
        var newFiles = files.filter(function (_, i) {
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
    fileNamesDisplay.textContent = files.map(function (f) {
      return f.name;
    }).join(', ');
    previewContainer.classList.remove('d-none');
  };
  imageInput.addEventListener('change', function () {
    var files = Array.from(imageInput.files);

    // Check for file size limit
    var validFiles = files.filter(function (file) {
      if (file.size > 3 * 1024 * 1024) {
        // 3MB limit
        alert("File ".concat(file.name, " is too large. Max 3MB allowed."));
        return false;
      }
      return true;
    });
    _updatePreviews(validFiles);
  });
  if (closePreviewBtnId) {
    var closePreviewBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)(closePreviewBtnId); // Button to clear image previews
    closePreviewBtn.addEventListener('click', function () {
      _updatePreviews([]); // Clear all
    });
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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");






try {
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };

  // CLICK EVENT get the comment and like button from the document
  document.addEventListener('click', /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(e) {
      var elementId, postId, likeCounterId, likeCounterVal, encodedLikeCounterVal, commentFormId, idForm, form, formEntries, inputComment, idInputComment, formExtra, formData, requesterFamCodeValue, response, friendRequestSection, _t;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            //document.onclick = async (e) => {
            elementId = e.target.id;
            postId = e.target.name; // Handle Like Button Click
            if (!elementId.includes("likeButton")) {
              _context.next = 3;
              break;
            }
            // replace button with Counter to get the span id 
            likeCounterId = elementId.replace('Button', 'Counter'); // trim removes leading and trailing spaces
            likeCounterVal = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(likeCounterId).innerHTML.trim().replace(/\n/g, ''); // 
            encodedLikeCounterVal = encodeURIComponent(likeCounterVal);
            _context.next = 1;
            return axios__WEBPACK_IMPORTED_MODULE_3__["default"].put("/profileCard/postLikes?postNo=".concat(postId, "&count=").concat(encodedLikeCounterVal, "&likeCounterId=").concat(likeCounterId));
          case 1:
            _context.next = 2;
            return axios__WEBPACK_IMPORTED_MODULE_3__["default"].get("/getNewLikesPusher");
          case 2:
            _context.next = 15;
            break;
          case 3:
            if (!elementId.includes("initComment")) {
              _context.next = 4;
              break;
            }
            commentFormId = elementId.replace('init', 'form');
            (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(commentFormId).style.display = "block";

            // Handle Comment Submission
            _context.next = 15;
            break;
          case 4:
            if (!elementId.includes("submitComment")) {
              _context.next = 8;
              break;
            }
            e.preventDefault();

            //idForm == formComment511
            idForm = elementId.replace("submit", "form"); // make the comment form disappear
            (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(idForm).style.display = "none";
            // extract the form entries
            form = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(idForm);
            formEntries = new FormData(form); // if the comment form input is empty. Get the input id and check 
            inputComment = idForm.replace("form", "input");
            idInputComment = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(inputComment);
            if (!(idInputComment.value == null || idInputComment.value == "")) {
              _context.next = 5;
              break;
            }
            alert("Please enter a comment before submitting");
            _context.next = 7;
            break;
          case 5:
            _context.next = 6;
            return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/postCommentProfile', formEntries, options);
          case 6:
            _context.next = 7;
            return axios__WEBPACK_IMPORTED_MODULE_3__["default"].get("/getNewCommentPusher");
          case 7:
            _context.next = 15;
            break;
          case 8:
            if (!elementId.includes("submitPost")) {
              _context.next = 14;
              break;
            }
            e.preventDefault();
            formExtra = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('formPostMessageModal');
            formData = new FormData(formExtra); // get the requesterFamCode from the localStorage 
            requesterFamCodeValue = localStorage.getItem('requesterFamCode'); // Append the new form entry to the FormData object
            formData.append('postFamCode', requesterFamCodeValue);
            _context.prev = 9;
            _context.next = 10;
            return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post("/member/profilePage/post", formData, options);
          case 10:
            response = _context.sent;
            _context.next = 11;
            return Promise.all([axios__WEBPACK_IMPORTED_MODULE_3__["default"].get("/post/getNewPostAndEmail?newCommentNo=" + response.data.message), axios__WEBPACK_IMPORTED_MODULE_3__["default"].get("/getNewPostPusher")]);
          case 11:
            // Hide the modal and reset the form
            (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('id01').style.display = 'none';
            (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("formPostMessageModal").reset();
            _context.next = 13;
            break;
          case 12:
            _context.prev = 12;
            _t = _context["catch"](9);
            console.error("An error occurred:", _t);
            // Optionally, display an error message to the user
            alert("There was an error processing your request. Please try again.");
          case 13:
            _context.next = 15;
            break;
          case 14:
            if (elementId && elementId.includes('deleteNotification')) {
              (0,_global__WEBPACK_IMPORTED_MODULE_2__.deleteNotification)(elementId);
            } // take you to the request card for approval or denial
            else if (e.target.classList.contains('linkRequestCard')) {
              // ONCE THE NOTIFICATION BAR IS CLICKED, IT SHOULD TAKE YOU TO BE FRIEND REQUEST CARD
              friendRequestSection = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("".concat(e.target.getAttribute('data-id'), "_linkRequestCard"));
              if (friendRequestSection) {
                friendRequestSection.scrollIntoView({
                  behavior: "smooth"
                });
              }
            }
          case 15:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[9, 12]]);
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
  var image = imgURL ? "/resources/images/profile/".concat(imgURL) : "/public/avatar/avatarF.png";
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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_7__);











var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_4__["default"](formInputArr);
var displayNone = function displayNone() {
  return (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('id_event_modal').style.display = 'none';
};
(0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('cancelModal').addEventListener('click', displayNone);

/**
 * Filters events by family code (famCode) to ensure only relevant events are shown
 * @param {Object} event - The event data object
 * @returns {boolean} - Returns true if event is linked to the family code
 */

var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
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
    var eventForm, eventFormEntries, _yield$Promise$all, _yield$Promise$all2, eventResponse, notificationResponse, notificationNo, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          e.preventDefault();
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('error').innerHTML = "";
          formData.massValidate();
          if (!(formData.error.length > 0)) {
            _context.next = 1;
            break;
          }
          alert('The form cannot be submitted. Please check the errors');
          formData.clearError();
          return _context.abrupt("return");
        case 1:
          // get the form data
          eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('eventModalForm');
          eventFormEntries = new FormData(eventForm); // POST data to create the event and notification in parallel
          _context.next = 2;
          return Promise.all([axios__WEBPACK_IMPORTED_MODULE_8__["default"].post("/member/profilePage/event", eventFormEntries, options), axios__WEBPACK_IMPORTED_MODULE_8__["default"].post('/member/notification/event', eventFormEntries, options)]);
        case 2:
          _yield$Promise$all = _context.sent;
          _yield$Promise$all2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_yield$Promise$all, 2);
          eventResponse = _yield$Promise$all2[0];
          notificationResponse = _yield$Promise$all2[1];
          // Extract and get notificationNo from the responses
          notificationNo = notificationResponse.data.message; // update all members of similar famcode on their UIs using Pusher
          axios__WEBPACK_IMPORTED_MODULE_8__["default"].get("/member/notification/event?notificationNo=".concat(notificationNo));

          // close the modal
          displayNone();
          _context.next = 4;
          break;
        case 3:
          _context.prev = 3;
          _t = _context["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
        case 4:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 3]]);
  }));
  return function process(_x) {
    return _ref.apply(this, arguments);
  };
}();
(0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('submitEventModal').addEventListener('click', process);

/***/ }),

/***/ "./resources/asset/js/components/profilePage/editProfile.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/editProfile.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
//import axios 


// Submission handler
(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.update)({
  formId: 'editProfileFormModal',
  route: '/member/profilePage/editProfile',
  buttonId: 'editProfileBtnModal',
  redirect: 'profilePage'
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

/***/ "./resources/asset/js/components/profilePage/friendRequestCard.js":
/*!************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/friendRequestCard.js ***!
  \************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");




var approverId = encodeURIComponent(localStorage.getItem('requesterId')); // means that the user currently working on the UI


//NOTE - this code worked well 25/10/24
/**
 * Fetch friend requests by approver ID and render each request.
 */
var fetchFriendRequests = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var response, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          _context.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/getFriendRequestById?id=".concat(approverId));
        case 1:
          response = _context.sent;
          if (response.data.message) {
            response.data.message.forEach(function (request) {
              return waitForRequestFriendClass(request);
            });
          }
          _context.next = 3;
          break;
        case 2:
          _context.prev = 2;
          _t = _context["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_2__.showError)(_t);
        case 3:
          ;
        case 4:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 2]]);
  }));
  return function fetchFriendRequests() {
    return _ref.apply(this, arguments);
  };
}();

// Wait for .requestFriendClass to appear in the DOM
var waitForRequestFriendClass = function waitForRequestFriendClass(data) {
  var observer = new MutationObserver(function (mutations, obs) {
    var requestContainer = document.querySelector('.requestFriendClass');
    if (requestContainer) {
      (0,_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_3__.friendRequestCard)(data);
      obs.disconnect(); // Stop observing once .requestFriendClass is found
    } else {
      (0,_global__WEBPACK_IMPORTED_MODULE_2__.log)('there is no requestFriendClass');
    }
  });

  // Observe the entire body for changes in child elements
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
};

// TODO: Maybe a future enhancement to show count of friend request 
var countFriendRequest = function countFriendRequest(friend) {
  return friend.length > 1 ? "<p><b>Friend Requests - ".concat(friend.length, "</b></p>\n  <br></br>") : "<p><b>Friend Request</b></p><br>";
};

// Fetch and render friend requests on page load

fetchFriendRequests();

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

/***/ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js ***!
  \***********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "friendRequestCard": function() { return /* binding */ friendRequestCard; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");

var appUrl = "https://olaogun.test/";
var approverId = encodeURIComponent(localStorage.getItem('requesterId'));
var friendRequestCard = function friendRequestCard(data) {
  var _data$img, _data$firstName, _data$lastName, _data$id, _data$famCode;
  var imageUrl = "/public/img/profile/".concat(encodeURIComponent((_data$img = data.img) !== null && _data$img !== void 0 ? _data$img : data.requesterProfileImg));
  var firstName = encodeURIComponent((_data$firstName = data.firstName) !== null && _data$firstName !== void 0 ? _data$firstName : data.requesterFirstName);
  var lastName = encodeURIComponent((_data$lastName = data.lastName) !== null && _data$lastName !== void 0 ? _data$lastName : data.requesterLastName);
  var requestId = encodeURIComponent((_data$id = data.id) !== null && _data$id !== void 0 ? _data$id : data.requesterId);
  var requestCode = encodeURIComponent((_data$famCode = data.famCode) !== null && _data$famCode !== void 0 ? _data$famCode : data.requesterFamCode);
  var html = "<p id=".concat(requestId, "_linkRequestCard></p>\n\n\n  <img src=\"").concat(imageUrl, "\" alt=\"Avatar\" style=\"width:50%\"><br>\n\n   <span>").concat(firstName, " ").concat(lastName, "</span>\n\n\n    <div class=\"w3-row w3-opacity\">\n      <div class=\"w3-half\">\n        <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/50/").concat(requestCode, "/pp\" style=\"text-decoration: none;\">\n          <button class=\"w3-button w3-block w3-green w3-section\" title=\"Accept\"><i class=\"fa fa-check\"></i></button>\n        </a>\n      </div>\n      \n      <div class=\"w3-half\">\n        <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/10\" style=\"text-decoration: none;\">\n          <button class=\"w3-button w3-block w3-red w3-section\" title=\"Decline\"><i class=\"fa fa-remove\"></i></button>\n        </a>\n      </div>\n    </div>\n  ");
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', html);
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
  var img = data !== null && data !== void 0 && data.profileImg ? "/public/img/profile/".concat(data.profileImg) : "/public/avatar/avatarF.png";
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
    return "\n    <a href=\"/profilepage/img?dir=img&pics=".concat(imgElement, "&pID=").concat(postNo, "&path=post\">\n      <div class=\"w3-half\">\n        <img src=\"/public/img/post/").concat(imgElement, "\" style=\"width:100%\" alt=\"images").concat(i, "\" class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat(i, "\">\n      </div>\n    </a>\n  ");
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





(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePics').addEventListener('click', function () {
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formProfilePics').style.display = "block";
});

// FOR PROFILE IMAGE CHANGE
(0,_helper_images__WEBPACK_IMPORTED_MODULE_1__.showImageFileUploadFn)('uploadButtonProfilePics', 'profileImageFile', 'profileImgFileNames');

// FOR POST MODAL IMAGE UPLOAD  

(0,_helper_images__WEBPACK_IMPORTED_MODULE_1__.showImageFileUploadFn)('uploadButton', 'post_img', 'postModalImgFileNames');
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitProfilePics').addEventListener('click', function () {
  23;

  // Get the form element
  var form = document.getElementById("formProfilePics");

  // Create a FormData object and append the form data to it
  var formData = new FormData(form);
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };
  // send form data using axios post method

  axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/member/profilePage/profileImg', formData, options).then(function (response) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').innerHTML = response.data;
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(response.data, "profilePicsNotification");
    if (response.data.message === "Profile image updated") {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').classList.add('w3-green');
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').innerHTML = response.data.message;
      // Reload the page
      location.reload();
    }
  }).catch(function (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').classList.add('w3-red');
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').innerHTML = error.message;
  });
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification').innerHTML = "";
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/index.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/index.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _loadPost__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./loadPost */ "./resources/asset/js/components/profilePage/loadPost.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/profilePage/modal.js");
/* harmony import */ var _img__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./img */ "./resources/asset/js/components/profilePage/img.js");
/* harmony import */ var _rsvpBtn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./rsvpBtn */ "./resources/asset/js/components/profilePage/rsvpBtn.js");
/* harmony import */ var _rsvpBtn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_rsvpBtn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/profilePage/allEvents.js");
/* harmony import */ var _registerPushNotification__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./registerPushNotification */ "./resources/asset/js/components/profilePage/registerPushNotification.js");
/* harmony import */ var _periodicSync__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./periodicSync */ "./resources/asset/js/components/profilePage/periodicSync.js");
/* harmony import */ var _createEvent__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./createEvent */ "./resources/asset/js/components/profilePage/createEvent.js");
/* harmony import */ var _friendRequestCard__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./friendRequestCard */ "./resources/asset/js/components/profilePage/friendRequestCard.js");
/* harmony import */ var _editProfile__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./editProfile */ "./resources/asset/js/components/profilePage/editProfile.js");
/* harmony import */ var _postEmojiImgProcess__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./postEmojiImgProcess */ "./resources/asset/js/components/profilePage/postEmojiImgProcess.js");
/* harmony import */ var _longPress__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./longPress */ "./resources/asset/js/components/profilePage/longPress.js");
/* harmony import */ var _longPress__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(_longPress__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var _engagement__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./engagement */ "./resources/asset/js/components/profilePage/engagement.js");


localStorage.removeItem('redirect');

// Dark Mode logic has been moved to main index.js to prevent double-firing












// import "./commentEmojiTest"
 // long press for reaction options


document.addEventListener('DOMContentLoaded', function () {
  (0,_engagement__WEBPACK_IMPORTED_MODULE_12__.initEngagementListeners)();
  (0,_engagement__WEBPACK_IMPORTED_MODULE_12__.initMemories)();
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/loadPost.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/loadPost.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _post__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./post */ "./resources/asset/js/components/profilePage/post.js");
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");











// set an empty array
try {
  var MAX_APPENDED_POSTS = 1000; // Set a maximum limit
  var appendedComments = new Set(); // To track unique comments
  var appendedPosts = new Set(); // To track unique comments

  // Initialize Pusher
  var pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_6___default())("0dc3f141e1632b5aa5db", {
    cluster: "eu",
    encrypted: true
  });

  // Global state object with data-fetching and initialization logic
  var state = {
    post: [],
    comment: [],
    // Method to fetch initial data and populate state
    initialize() {
      var _this = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
        var pullData, _t;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.prev = 0;
              _context.next = 1;
              return axios__WEBPACK_IMPORTED_MODULE_9__["default"].get("/post/getAllPostCommentByFamCode");
            case 1:
              pullData = _context.sent;
              // Assign fetched data to state properties
              _this.post = pullData.data.message.post;
              _this.comment = pullData.data.message.comment;
              _this.comment = _this.comment.flat(); // Flatten the array of arrays into a single array of comment objects

              if (_this.post.length > 0) {
                // Render posts and comments on the page after data is loaded
                _this.post.forEach(function (data) {
                  return (0,_post__WEBPACK_IMPORTED_MODULE_3__.allPost)(data, _this.comment);
                });
              } else {
                (0,_global__WEBPACK_IMPORTED_MODULE_2__.log)("No post");
              }
              _context.next = 3;
              break;
            case 2:
              _context.prev = 2;
              _t = _context["catch"](0);
              console.error("Error fetching posts and comments:", _t);
            case 3:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[0, 2]]);
      }))();
    }
  };

  // initiate the global object
  state.initialize();

  // IntersectionObserver for Infinite Scroll
  var sentinel = document.getElementById('infinite-scroll-sentinel');
  if (sentinel) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          console.log('Sentinel intersected! Fetching next page of posts...');
          // Show skeleton
          sentinel.style.display = 'block';

          // Simulate network delay for the skeleton loader effect, then fetch next page
          setTimeout(function () {
            // In a real implementation, you would pass an offset/page to state.initialize(page)
            // For now, we simulate the end of the list after the first fetch
            sentinel.style.display = 'none';
          }, 1500);
        }
      });
    }, {
      rootMargin: '100px'
    });
    observer.observe(sentinel);
  }
  var updatePost = /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(e) {
      var dataForUse, oldestPost, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            // Parse the incoming data and check if it already exists in state
            dataForUse = checkOriginAndParsedData(e); // Only append if the comment hasn't been added before
            if (appendedPosts.has(dataForUse.post_no)) {
              _context2.next = 4;
              break;
            }
            appendedPosts.add(dataForUse.post_no);

            // Clean up old entries if the set exceeds the limit
            if (appendedPosts.size > MAX_APPENDED_POSTS) {
              oldestPost = appendedPosts.values().next().value;
              appendedPosts.delete(oldestPost);
            }
            (0,_post__WEBPACK_IMPORTED_MODULE_3__.appendNewPost)(dataForUse);
            _context2.prev = 1;
            _context2.next = 2;
            return axios__WEBPACK_IMPORTED_MODULE_9__["default"].put("/updatePostByStatusAsPublished/".concat(dataForUse.post_no), {
              post_status: 'published'
            });
          case 2:
            _context2.next = 4;
            break;
          case 3:
            _context2.prev = 3;
            _t2 = _context2["catch"](1);
            console.error("Failed to update comment status: ".concat(_t2.message));
          case 4:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[1, 3]]);
    }));
    return function updatePost(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  var updateComment = /*#__PURE__*/function () {
    var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(e) {
      var dataForUse, _t3;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
        while (1) switch (_context3.prev = _context3.next) {
          case 0:
            // Parse the incoming data and check if it already exists in state
            dataForUse = checkOriginAndParsedData(e); // Only append if the comment hasn't been added before
            if (appendedComments.has(dataForUse.comment_no)) {
              _context3.next = 4;
              break;
            }
            appendedComments.add(dataForUse.comment_no);

            // check if dataForUse length is greater than 0 and if yes foreach to lop 

            (0,_comment__WEBPACK_IMPORTED_MODULE_5__.appendNewComment)(dataForUse);
            _context3.prev = 1;
            _context3.next = 2;
            return axios__WEBPACK_IMPORTED_MODULE_9__["default"].put("/updateCommentByStatusAsPublished/".concat(dataForUse.comment_no), {
              comment_status: 'published'
            });
          case 2:
            _context3.next = 4;
            break;
          case 3:
            _context3.prev = 3;
            _t3 = _context3["catch"](1);
            console.error("Failed to update comment status: ".concat(_t3.message));
          case 4:
          case "end":
            return _context3.stop();
        }
      }, _callee3, null, [[1, 3]]);
    }));
    return function updateComment(_x2) {
      return _ref2.apply(this, arguments);
    };
  }();
  var updateLike = function updateLike(e) {
    // Parse the incoming data and check if it already exists in state
    var dataForUse = checkOriginAndParsedData(e);
    var likeElement = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(dataForUse.likeHtmlId);
    if (likeElement) {
      likeElement.innerHTML = parseInt(dataForUse.likeCounter);
    }
  };

  // Subscribe to the posts channel
  var postsChannel = pusher.subscribe('posts-channel');
  postsChannel.bind('new-post', function (data) {
    data.forEach(function (item) {
      return updatePost(item);
    });
  });

  // Subscribe to the comments channel
  var commentsChannel = pusher.subscribe('comments-channel');
  commentsChannel.bind('new-comment', function (data) {
    data.forEach(function (item) {
      return updateComment(item);
    });
  });

  // Subscribe to the likes channel
  var likesChannel = pusher.subscribe('likes-channel');
  likesChannel.bind('like-event', function (data) {
    data.forEach(function (item) {
      return updateLike(item);
    });
  });

  // Subscribe to the event channel

  var checkEventAndAdd = function checkEventAndAdd(data) {
    var appendEvent = (0,_eventHTML__WEBPACK_IMPORTED_MODULE_7__.eventHtml)(data);
    return (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('eventList').insertAdjacentHTML('afterbegin', appendEvent);
  };
  var notificationChannel = pusher.subscribe('notification-channel');
  notificationChannel.bind('new-notification', function (data) {
    if (localStorage.getItem('requesterFamCode') === data.receiver_id) {
      checkEventAndAdd(data);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_8__.addToNotificationTab)(data);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_8__.increaseNotificationCount)();
    }
  });

  // AUTOMATICALLY UPDATE TIMESTAMP
  // Function to check for elements and render if they exist every 5 seconds
  setInterval(function () {
    (0,_global__WEBPACK_IMPORTED_MODULE_2__.checkManyElements)('class', ".timeago", timeago_js__WEBPACK_IMPORTED_MODULE_4__.render);
    (0,_global__WEBPACK_IMPORTED_MODULE_2__.checkManyElements)('class', ".commentTiming", timeago_js__WEBPACK_IMPORTED_MODULE_4__.render);
  }, 2000); // Adjust interval as needed

  var checkOriginAndParsedData = function checkOriginAndParsedData(data) {
    if (!data) throw new Error('No update received');
    if (data) {
      if (data.origin != appUrl) {
        (0,_global__WEBPACK_IMPORTED_MODULE_2__.msgException)('Invalid Origin');
      }
      return data;
    }

    // check if data is a valid jason object
    // return JSON.parse(data)
  };
} catch (error) {
  (0,_global__WEBPACK_IMPORTED_MODULE_2__.showError)(error);
}

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
  // NEW MESSAGE MODAL
  var showModal = function showModal() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'block';
  };

  // CREATE EVENT MODAL
  var showEvent = function showEvent() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'block';
  };

  //EVENT ACTION
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createEvent').addEventListener('click', showEvent);
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('postMsg').addEventListener('click', showModal);

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

/***/ "./resources/asset/js/components/profilePage/post.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/post.js ***!
  \***********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "allPost": function() { return /* binding */ allPost; },
/* harmony export */   "appendNewPost": function() { return /* binding */ appendNewPost; }
/* harmony export */ });
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


var famCode = localStorage.getItem('requesterFamCode');
/**
 * Renders a post and its associated comments in the DOM.
 * 
 * This function takes a post object and an array of comment data,
 * filters the comments to include only those associated with the 
 * given post, generates HTML for the post using the `html` function, 
 * and appends it to the 'postIt' container in the DOM.
 *
 * @param {Object} el - The post object containing post data, including post number.
 * @param {Array} commentData - An array of comment objects associated with posts.
 * @returns {boolean} - Returns false if the post object is invalid.
 */
var allPost = function allPost(postData, commentData) {
  if (!postData || !Array.isArray(commentData)) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.msgException)('Invalid post data');
  }
  var postNo = parseInt(postData.post_no);
  var filterComment = commentData.filter(function (comm) {
    return parseInt(comm.post_no) === postNo;
  }); // filter the comment to an array
  var postHtml = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(postData, filterComment);
  // if(postFamCode === famCode) {
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('beforeend', postHtml);
};

/**
 * Appends a new post to the DOM if it does not already exist.
 * 
 * This function checks for the existence of comment form elements 
 * associated with the provided post object. If any of these elements 
 * are missing, it generates HTML for the post using the `html` function 
 * and inserts it at the beginning of the 'postIt' container.
 *
 * @param {Object} el - The post object containing post data, including post number.
 * @returns {boolean} - Returns false if the post object is invalid.
 */
var appendNewPost = function appendNewPost(el) {
  var post_no = el.post_no;
  // Generate the IDs for the comment form and its components const 
  var commentFormId = "formComment".concat(post_no);
  var inputCommentId = "inputComment".concat(post_no);
  var submitCommentId = "submitComment".concat(post_no);

  // Check if the comment form components exist in the DOM
  var commentForm1 = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(commentFormId);
  var inputComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(inputCommentId);
  var submitComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(submitCommentId);

  // If the comment form components do not exist, create and append the new post
  if (!commentForm1 || !inputComment || !submitComment) {
    var appendHTML = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(el);

    // Ensure the post belongs to the correct family code
    // if (el.postFamCode === famCode) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('afterbegin', appendHTML);
    // } else{
    //   return false
    // }
  }
};

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

(0,_emojiPicker_js__WEBPACK_IMPORTED_MODULE_0__.showEmojiPicker)('emojiListPost', 'data-emoji-target');
(0,_emojiPicker_js__WEBPACK_IMPORTED_MODULE_0__.initEmojiPickerUX)('emojiPost', 'emojiPickerContainer');

// 🟡 Toggle emoji picker visibility when the toggle button is clicked
emojiToggle.addEventListener('click', function () {
  emojiContainer.classList.toggle('d-none'); // Show/hide the emoji container
  emojiToggle.setAttribute('aria-expanded', emojiContainer.classList.contains('d-none') ? 'false' : 'true');
});

// Close button handler
if (closeEmojiBtn) {
  closeEmojiBtn.addEventListener('click', function () {
    emojiContainer.classList.add('d-none');
    emojiToggle.setAttribute('aria-expanded', 'false');
  });
}
(0,_fileUploadPreview__WEBPACK_IMPORTED_MODULE_1__.imagePreview)('imageUpload', 'imagePreviewList', 'postModalImgFileNames', 'imagePreviewContainer', 'closeImagePreview');

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
  removePollBtn.addEventListener('click', function () {
    pollContainer.classList.add('d-none');
    // Reset inputs
    var inputs = pollContainer.querySelectorAll('input');
    inputs.forEach(function (input) {
      return input.value = '';
    });
  });
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

/***/ })

}]);
//# sourceMappingURL=profilePage.js.map