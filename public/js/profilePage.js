(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["profilePage"],{

/***/ "./resources/asset/js/components/emojiPicker.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/emojiPicker.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showEmojiPicker: function() { return /* binding */ showEmojiPicker; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");
/* harmony import */ var emojibase_data_en_data_json__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! emojibase-data/en/data.json */ "./node_modules/emojibase-data/en/data.json");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");



var EMOJI_CACHE_KEY = 'recentEmojis'; // LocalStorage key for caching recent emojis

// 🟡 Filter emojis to only include smileys (based on Unicode range)
var smileys = emojibase_data_en_data_json__WEBPACK_IMPORTED_MODULE_1__.filter(function (e) {
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
 * Render a list of emoji buttons into the picker
 * @param {Array} emojis - An array of emoji objects from emojibase
 * @param {string} emojiContainerId - The ID of the container element for the emoji buttons
 * @param {string} emojiTargetDataClass - The data class attribute for the emoji target i.e data-emoji-target
 * 
 */

var renderEmojiList = function renderEmojiList(emojis, emojiContainerId, emojiTargetDataClass) {
  var emojiList = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)(emojiContainerId); // Container for emoji buttons

  emojiList.innerHTML = ''; // Clear existing emojis

  // Scope to the closest form
  var form = emojiList.closest('form');
  var emojiTarget = form.querySelector("[".concat(emojiTargetDataClass, "]"));

  // Load and render cached recent emojis first
  var cached = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];
  cached.forEach(function (emoji) {
    return renderEmojiButton(emoji, 'Recent', emojiList, emojiTarget);
  });

  // Render up to 70 emojis from the filtered list
  emojis.slice(0, 70).forEach(function (_ref) {
    var emoji = _ref.emoji,
      label = _ref.label,
      skins = _ref.skins;
    renderEmojiButton(emoji, label, emojiList, emojiTarget); // Main emoji

    // If skin tone variants exist, render them too
    if (skins) {
      skins.forEach(function (_ref2) {
        var skinEmoji = _ref2.emoji;
        renderEmojiButton(skinEmoji, "".concat(label, " (skin tone)"), emojiList, emojiTarget);
      });
    }
  });
};

// 🟡 Create and insert a single emoji button
var renderEmojiButton = function renderEmojiButton(char, label, emojiContainer, emojiTarget) {
  var btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'emoji-btn btn btn-sm btn-light'; // Styling classes
  btn.textContent = char; // Emoji character
  btn.setAttribute('aria-label', label); // Accessibility label

  // When clicked, insert emoji into target and cache it
  btn.addEventListener('click', function () {
    emojiTarget.value += char;
    cacheEmoji(char);
  });
  emojiContainer.appendChild(btn); // Add button to picker
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
/* harmony export */   imagePreview: function() { return /* binding */ imagePreview; }
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

/***/ "./resources/asset/js/components/helper/images.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/helper/images.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showImageFileUploadFn: function() { return /* binding */ showImageFileUploadFn; }
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

/***/ "./resources/asset/js/components/profilePage/allEvents.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/allEvents.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _emojiPicker_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../emojiPicker.js */ "./resources/asset/js/components/emojiPicker.js");
/* harmony import */ var _indexDB_reactions_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./indexDB/reactions.js */ "./resources/asset/js/components/profilePage/indexDB/reactions.js");
/* harmony import */ var _showEmojiOnComment_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./showEmojiOnComment.js */ "./resources/asset/js/components/profilePage/showEmojiOnComment.js");










try {
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };

  // CLICK EVENT get the comment and like button from the document
  document.addEventListener('click', /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee(e) {
      var elementId, postId, postImgId, likeCounterId, likeCounterVal, encodedLikeCounterVal, commentFormId, emojiToggler, emojiListElement, emojiList, idForm, _postId, form, formEntries, inputComment, idInputComment, _id, _id2, _commentFormId, postMessage, formExtra, formData, requesterFamCodeValue, response, friendRequestSection, postNo, imgClass, imagesInGroup, initialIndex, commentNo, commentElement, _response, reactionOptionDiv, reactionOptionDivId, _commentNo, emojiContent, theLabel, _response2, button, countEl, preview, _response2$data$messa, label, reaction, countReaction, emoji, reactionBar, _t;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            //document.onclick = async (e) => {
            elementId = e.target.id;
            postId = e.target.name;
            postImgId = e.target.dataset.postimgid; // Handle Like Button Click
            if (!elementId.includes("likeButton")) {
              _context.next = 3;
              break;
            }
            // replace button with Counter to get the span id 
            likeCounterId = elementId.replace('Button', 'Counter'); // trim removes leading and trailing spaces
            likeCounterVal = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(likeCounterId).innerHTML.trim().replace(/\n/g, ''); // 
            encodedLikeCounterVal = encodeURIComponent(likeCounterVal);
            _context.next = 1;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].put("/profileCard/postLikes?postNo=".concat(postId, "&count=").concat(encodedLikeCounterVal, "&likeCounterId=").concat(likeCounterId));
          case 1:
            _context.next = 2;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/getNewLikesPusher");
          case 2:
            _context.next = 25;
            break;
          case 3:
            if (!elementId.includes("initComment")) {
              _context.next = 4;
              break;
            }
            commentFormId = elementId.replace('init', 'form');
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(commentFormId).classList.toggle('d-none');
            _context.next = 25;
            break;
          case 4:
            if (!elementId.includes("emojiToggle")) {
              _context.next = 5;
              break;
            }
            emojiToggler = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(elementId);
            emojiListElement = elementId.replace('emojiToggle', 'emojiCommentPickerList');
            emojiList = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(emojiListElement); // Container for emoji buttons
            // 🟡 Toggle emoji picker visibility when the toggle button is clicked
            // emojiToggler.addEventListener('click', () => {
            emojiList.classList.toggle('d-none'); // Show/hide the emoji list
            emojiToggler.setAttribute('aria-expanded', emojiList.classList.contains('d-none') ? 'false' : 'true');
            // });

            // comment form emoji picker
            (0,_emojiPicker_js__WEBPACK_IMPORTED_MODULE_5__.showEmojiPicker)(emojiListElement, 'data-commentEmoji-target');
            _context.next = 25;
            break;
          case 5:
            if (!elementId.includes("submitComment")) {
              _context.next = 10;
              break;
            }
            e.preventDefault();

            //idForm == formComment511
            idForm = elementId.replace("submit", "form");
            _postId = elementId.replace("submitComment", ""); // make the comment form disappear
            // id(idForm).style.display = "none"
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(idForm).classList.add('d-none');
            // extract the form entries
            form = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(idForm);
            formEntries = new FormData(form); // if the comment form input is empty. Get the input id and check 
            inputComment = idForm.replace("form", "input");
            idInputComment = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(inputComment);
            if (!(idInputComment.value == null || idInputComment.value == "")) {
              _context.next = 6;
              break;
            }
            alert("Please enter a comment before submitting");
            _context.next = 9;
            break;
          case 6:
            _context.next = 7;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post('/postCommentProfile', formEntries, options);
          case 7:
            _context.next = 8;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/getNewCommentPusher");
          case 8:
            // hide the emoji picker after submission

            (_id = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("emojiCommentPickerList".concat(_postId))) === null || _id === void 0 ? void 0 : _id.classList.toggle('d-none');
            _commentFormId = elementId.replace('submit', 'form');
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.formReset)(idForm);
            // Hide the form explicitly
            (_id2 = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)(_commentFormId)) === null || _id2 === void 0 ? void 0 : _id2.classList.add('d-none');
          case 9:
            _context.next = 25;
            break;
          case 10:
            if (!elementId.includes("submitPost")) {
              _context.next = 17;
              break;
            }
            e.preventDefault();

            // check if the post message is empty
            postMessage = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('postMessage').value.trim();
            if (!(postMessage === "")) {
              _context.next = 11;
              break;
            }
            alert("Post message cannot be empty");
            return _context.abrupt("return");
          case 11:
            // validate the file input if any
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.fileUploadSizeValidation)('post_img', 3);
            formExtra = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('formPostMessageModal');
            formData = new FormData(formExtra); // get the requesterFamCode from the localStorage 
            requesterFamCodeValue = localStorage.getItem('requesterFamCode'); // Append the new form entry to the FormData object
            formData.append('postFamCode', requesterFamCodeValue);
            _context.prev = 12;
            _context.next = 13;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/member/profilePage/post", formData, options);
          case 13:
            response = _context.sent;
            console.log(response.data);

            // Assuming the response contains the new post number in response.data.token
            // You can adjust this based on your actual response structure

            // Cache the new post data if needed
            // await cachePost(response.data.newPost);

            // 2. Notify members of similar famcode about the post by email
            // 3. Update all members of similar famcode on their UIs using Pusher
            _context.next = 14;
            return Promise.all([axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/post/getNewPostAndEmail?newPostNo=" + response.data.token), axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/getNewPostPusher")]);
          case 14:
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.formReset)("formPostMessageModal");
            // redirect to the profile page
            window.location.href = '/profilePage';
            _context.next = 16;
            break;
          case 15:
            _context.prev = 15;
            _t = _context["catch"](12);
            console.error("An error occurred:", _t.response);
            // Optionally, display an error message to the user
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('formPostMessageModal_notification').innerHTML = 'There was an error submitting your post. Please try again later.';
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('formPostMessageModal_notification').classList.add('is-danger');
            (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('formPostMessageModal_notification').style.display = 'block';
          case 16:
            _context.next = 25;
            break;
          case 17:
            if (!e.target.classList.contains('linkRequestCard')) {
              _context.next = 18;
              break;
            }
            // ONCE THE NOTIFICATION BAR IS CLICKED, IT SHOULD TAKE YOU TO BE FRIEND REQUEST CARD
            friendRequestSection = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("".concat(e.target.getAttribute('data-id'), "_linkRequestCard"));
            if (friendRequestSection) {
              friendRequestSection.scrollIntoView({
                behavior: "smooth"
              });
            }
            _context.next = 25;
            break;
          case 18:
            if (!e.target.classList.contains('grid-image')) {
              _context.next = 19;
              break;
            }
            if (e.target.classList.contains('grid-image')) {
              postNo = e.target.dataset.postno;
              imgClass = ".zoomable-image".concat(postNo);
              imagesInGroup = Array.from(document.querySelectorAll(imgClass));
              initialIndex = imagesInGroup.findIndex(function (img) {
                return img.src === e.target.src;
              });
              if (initialIndex !== -1) {
                (0,_global__WEBPACK_IMPORTED_MODULE_3__.initializeImageModal)(imgClass, initialIndex, 'imageModal', 'modalImage', 'imageModalClose');
              }
            }
            _context.next = 25;
            break;
          case 19:
            if (!elementId.includes('removeCommentIcon')) {
              _context.next = 23;
              break;
            }
            // get the comment no
            commentNo = elementId.replace('removeCommentIcon', ''); // Ask user to confirm before deleting (safety check)
            if (!confirm('Are you sure you want to remove this comment?')) {
              _context.next = 22;
              break;
            }
            // Find the comment element and remove it from page
            commentElement = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("commentDiv".concat(commentNo));
            if (!commentElement) {
              _context.next = 21;
              break;
            }
            _context.next = 20;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].delete("/deleteComment/".concat(commentNo));
          case 20:
            _response = _context.sent;
            alert(_response.data.message);
            _context.next = 22;
            break;
          case 21:
            alert('Comment not found');
          case 22:
            _context.next = 25;
            break;
          case 23:
            if (!e.target.closest('[id^="reaction-option-"]')) {
              _context.next = 25;
              break;
            }
            reactionOptionDiv = e.target.closest('[id^="reaction-option-"]');
            reactionOptionDivId = reactionOptionDiv.getAttribute('id');
            _commentNo = reactionOptionDiv.getAttribute('data-option-no');
            emojiContent = reactionOptionDiv.textContent;
            theLabel = reactionOptionDiv.dataset.label; // post to the comment_reactions table
            _context.next = 24;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/commentReaction", {
              comment_no: _commentNo,
              reaction: emojiContent,
              label: theLabel
            });
          case 24:
            _response2 = _context.sent;
            button = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("like-button-".concat(_commentNo));
            countEl = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("like-count-".concat(_commentNo));
            preview = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("reaction-preview-".concat(_commentNo)); // destructure the response to get the reaction summary
            _response2$data$messa = _response2.data.message, label = _response2$data$messa.label, reaction = _response2$data$messa.reaction, countReaction = _response2$data$messa.countReaction;
            if (label) button.querySelector('span').textContent = label;
            emoji = (0,_showEmojiOnComment_js__WEBPACK_IMPORTED_MODULE_7__.renderTopReactions)(countReaction, _commentNo);
            if (reaction) preview.innerHTML = emoji;
            if (countEl) countEl.textContent = countReaction.totalReactions;

            // hide the reaction bar after selection
            reactionBar = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("reaction-bar-".concat(_commentNo));
            if (reactionBar) {
              reactionBar.classList.remove('show');
            }
          case 25:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[12, 15]]);
    }));
    return function (_x) {
      return _ref.apply(this, arguments);
    };
  }());

  // MOUSE ENTER OVER THE LIKE BUTTON TO SHOW REACTION OPTIONS
  document.addEventListener('mouseover', /*#__PURE__*/function () {
    var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee2(e) {
      var reactionDiv, elementId, elementName, commentNo, reactionBar, _commentNo2, getResponse, _getResponse$data$mes, counts, who, whoList, emojiSummary, tooltip;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            reactionDiv = e.target.closest('.reaction-button'); //1 mouseover on the like button to show the reaction-option div
            if (reactionDiv) {
              elementId = reactionDiv.id;
              elementName = reactionDiv.name;
              commentNo = reactionDiv.dataset.commentNo; //2 show the reaction bar - reaction-option class
              reactionBar = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("reaction-bar-".concat(commentNo));
              if (reactionBar) {
                reactionBar.classList.toggle('show');
              } else {
                reactionBar.classList.remove('show');
              }
            }

            // MOUSE ENTER OVER THE REACTION PREVIEW OR COUNT TO SHOW TOOLTIP
            if (!(e.target.classList.contains('reaction-preview') || e.target.classList.contains('reaction-count'))) {
              _context2.next = 3;
              break;
            }
            _commentNo2 = e.target.id.replace('reaction-preview-', '').replace('like-count-', ''); // fetch and show the reaction summary tooltip  
            _context2.next = 1;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get("/commentReactionSummary/".concat(_commentNo2));
          case 1:
            getResponse = _context2.sent;
            _getResponse$data$mes = getResponse.data.message, counts = _getResponse$data$mes.counts, who = _getResponse$data$mes.who;
            whoList = who.map(function (u) {
              return "<div class=\"who-reacted\">".concat(u.reaction, " ").concat(u.firstName, " ").concat(u.lastName, "</div>");
            }).join(''); // Create a summary of reactions that actually occurred
            emojiSummary = Object.entries(counts).filter(function (_ref3) {
              var _ref4 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref3, 2),
                reactionType = _ref4[0],
                count = _ref4[1];
              return Number(count) > 0;
            }) // Keep only reactions with a count greater than 0
            .map(function (_ref5) {
              var _ref6 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref5, 2),
                reactionType = _ref6[0],
                count = _ref6[1];
              return "<span>".concat(reactionType, " ").concat(count, "</span>"); // Convert each to a span element
            }).join(' '); // Combine all spans into one string with spaces in between
            // tooltip display
            tooltip = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("reaction-summary-".concat(_commentNo2));
            if (tooltip) {
              _context2.next = 2;
              break;
            }
            return _context2.abrupt("return");
          case 2:
            if (tooltip) tooltip.style.display = 'block';
            tooltip.innerHTML = "<div class=\"loading-tooltip\">Loading...</div>";
            tooltip.style.display = 'block';
            tooltip.innerHTML = "\n                    <div class=\"tooltip-body\">".concat(whoList, "</div>\n            ");

            // Hide the tooltip after 5 seconds
            setTimeout(function () {
              tooltip.style.display = 'none';
            }, 4000);
            tooltip.classList.remove('show');
          case 3:
          case "end":
            return _context2.stop();
        }
      }, _callee2);
    }));
    return function (_x2) {
      return _ref2.apply(this, arguments);
    };
  }());
  document.addEventListener('mouseover', function (e) {
    var target = e.target.closest('.reaction-button');
    if (!target) return;
    var commentNo = target.dataset.commentNo;
    var tooltip = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("reaction-summary-".concat(commentNo));
    if (tooltip) tooltip.style.display = 'block';
  });
  document.addEventListener('mouseout', function (e) {
    var target = e.target.closest('.reaction-button');
    if (!target) return;
    var commentNo = target.dataset.commentNo;
    var tooltip = (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)("reaction-summary-".concat(commentNo));
    if (tooltip) tooltip.style.display = 'none';
  });

  // MOUSE LEAVE
} catch (e) {
  (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(e);
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
/* harmony export */   appendNewComment: function() { return /* binding */ appendNewComment; },
/* harmony export */   commentHTML: function() { return /* binding */ commentHTML; },
/* harmony export */   showComment: function() { return /* binding */ showComment; }
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
  return "<div class=\"d-flex mb-3 commentDiv align-items-start\" data-commentDiv-no=\"".concat(comment_no, "\" id=\"commentDiv").concat(comment_no, "\" name=\"commentDiv\">\n\n  <img src=\"").concat(image, "\" alt=\"Avatar\" class=\"rounded-circle me-2 commentImg\" width=\"32\" height=\"32\">\n\n  <div class=\"flex-grow-1\">\n    <div class=\"d-flex justify-content-between align-items-center\">\n      <small><strong>").concat((0,_shared__WEBPACK_IMPORTED_MODULE_1__.toSentenceCase)(fullName), "</strong></small>\n      <small class=\"text-muted commentTiming\" datetime=\"").concat(date_created, "\" title=\"").concat(date_created, "\">\n        ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), "\n      </small>\n    </div>\n\n    <div class=\"comment-text mb-2\">\n      <small>").concat(comment, "</small>\n    </div>\n\n      <div class=\"d-flex reaction-preview-section align-items-center mb-2 gap-2\"> \n\n        <div class=\"reaction-preview\" id=\"reaction-preview-").concat(comment_no, "\">\n        ").concat((0,_showEmojiOnComment_js__WEBPACK_IMPORTED_MODULE_3__.renderTopReactions)(counts, comment_no), "\n        </div>\n\n         <div class=\"reaction-summary\" data-comment-no=\"").concat(comment_no, "\" role=\"tooltip\" id=\"reaction-summary-").concat(comment_no, "\" style=\"display:none;\">\n        </div>\n\n      </div>\n\n      <div class=\"comment-actions d-flex gap-3\">         \n                <div class=\"reaction-bar\"  id=\"reaction-bar-").concat(comment_no, "\">\n\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Like\" id=\"reaction-option-like-").concat(comment_no, "\" data-reaction=\"like\" data-label=\"likes\"> \uD83D\uDC4D </div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Love\" id=\"reaction-option-love-").concat(comment_no, "\" data-reaction=\"love\" data-label=\"love\">\u2764\uFE0F</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Haha\" id=\"reaction-option-haha-").concat(comment_no, "\" data-reaction=\"haha\" data-label=\"haha\">\uD83D\uDE04</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Wow\" id=\"reaction-option-wow-").concat(comment_no, "\" data-reaction=\"wow\" data-label=\"wow\">\uD83D\uDE2E</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Sad\" id=\"reaction-option-sad-").concat(comment_no, "\" data-reaction=\"sad\" data-label=\"sad\">\uD83D\uDE22</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Angry\" id=\"reaction-option-angry-").concat(comment_no, "\"\n                     data-reaction=\"angry\" data-label=\"angry\">\uD83D\uDE20</div>\n                </div>\n\n                <div class=\"reaction-button like-button-").concat(comment_no, "\" id=\"like-button-").concat(comment_no, "\" data-comment-no=\"").concat(comment_no, "\">\n                    <i class=\"bi bi-hand-thumbs-up reaction-icon\" id=\"like-icon-").concat(comment_no, "\"></i>\n                    <span>Like</span>\n                     <div class=\"reaction-count\" id=\"like-count-").concat(comment_no, "\">").concat(total, "</div>\n                   \n                </div>\n\n                ").concat(reqId == id || reqId == postId ? "<button class=\"btn btn-sm btn-icon text-danger\" id=\"removeComment(".concat(comment_no, ")\" title=\"Remove\">\n                    <i class=\"bi bi-trash\" id=\"removeCommentIcon").concat(comment_no, "\"></i>\n                    </button>") : '', "        \n      </div>\n  </div>\n</div><hr>");
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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_8__);











var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_4__["default"](formInputArr);
var cancelModal = function cancelModal() {
  var displayNone = function displayNone() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('id_event_modal').style.display = 'none';
  };
  (0,_global__WEBPACK_IMPORTED_MODULE_3__.id)('cancelModal').addEventListener('click', displayNone);
};
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', cancelModal);
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
          return Promise.all([axios__WEBPACK_IMPORTED_MODULE_7__["default"].post("/member/profilePage/event", eventFormEntries, options), axios__WEBPACK_IMPORTED_MODULE_7__["default"].post('/member/notification/event', eventFormEntries, options)]);
        case 2:
          _yield$Promise$all = _context.sent;
          _yield$Promise$all2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_yield$Promise$all, 2);
          eventResponse = _yield$Promise$all2[0];
          notificationResponse = _yield$Promise$all2[1];
          // Extract and get notificationNo from the responses
          notificationNo = notificationResponse.data.message; // update all members of similar famcode on their UIs using Pusher
          axios__WEBPACK_IMPORTED_MODULE_7__["default"].get("/member/notification/event?notificationNo=".concat(notificationNo));

          // redirect to the profile page to view the event
          window.location.href = '/profilePage';
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

/***/ "./resources/asset/js/components/profilePage/eventHTML.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/eventHTML.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   eventHtml: function() { return /* binding */ eventHtml; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
"use static";


var eventHtml = function eventHtml(data) {
  return " <div class=\"event-card card mb-3\">\n                <div class=\"card-body\">\n                    <div class=\"d-flex\">\n                        <div class=\"flex-grow-1\">\n                            <small class='eventInfo'>\n            <strong><strong>RSVP: </strong> ".concat(data.sender_name, "</small><br>\n                            <small class='eventInfo'><strong>Event: </strong>").concat(data.notification_name, "</small><br>\n                            <small class='eventInfo'><strong>Type: </strong>").concat(data.notification_type, "</small><br>\n                            <small class='eventInfo'><strong>Description: </strong> ").concat(data.notification_content, "</small><br>\n                            <small class='eventInfo'><strong>Date: </strong>").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.date2String)(data.notification_date), " </small><br>\n        \n         \n            <input type='hidden' name='event_no' id='event").concat(data.no, "' value='").concat(data.no, "'>\n\n             <div class=\"mt-2 rsvp-buttons d-flex\">\n                                        <button class=\"btn btn-sm btn-outline-primary\">Going</button>\n                                        <button class=\"btn btn-sm btn-outline-secondary\">Maybe</button>\n                                        <button class=\"btn btn-sm btn-outline-danger\">Decline</button>\n                                    </div>\n\n               </div>\n                            </div>\n                        </div>\n                    </div>\n           <hr>");

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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");




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
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("/getFriendRequestById");
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
          (0,_shared__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
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
      (0,_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_4__.friendRequestCard)(data);
      obs.disconnect(); // Stop observing once .requestFriendClass is found
    } else {
      (0,_shared__WEBPACK_IMPORTED_MODULE_3__.log)('there is no requestFriendClass');
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

// Friend Request Actions
var confirmRequestButtons = document.querySelectorAll('.confirm-request');
var deleteRequestButtons = document.querySelectorAll('.delete-request');
confirmRequestButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    var card = this.closest('.card-body');
    var alert = document.createElement('div');
    alert.className = 'alert alert-success';
    alert.textContent = 'Friend request confirmed!';
    card.innerHTML = '';
    card.appendChild(alert);

    // Update notification badge
    updateNotificationBadge(-1);
  });
});
deleteRequestButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    var card = this.closest('.card');
    card.style.opacity = '0';
    setTimeout(function () {
      card.remove();
      // Update notification badge
      updateNotificationBadge(-1);
    }, 300);
  });
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   html: function() { return /* binding */ html; }
/* harmony export */ });
/* harmony import */ var _htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./htmlFolder/nameImageTiming */ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js");
/* harmony import */ var _htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./htmlFolder/commentForm */ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js");
/* harmony import */ var _htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./htmlFolder/likeCommentButton */ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js");
/* harmony import */ var _htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./htmlFolder/showPostImages */ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");





var html = function html(postArray) {
  var comment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
  var post_no = postArray.post_no,
    postMessage = postArray.postMessage,
    id = postArray.id;
  return "\n    <div class=\"post card\" id=\"post".concat(post_no, "\">\n     <div class=\"card-body post").concat(post_no, "\" id=\"postIt\">\n    ").concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(postArray), "\n\n    <div class=\"post-content\">\n    <p class=\"card-text\"> ").concat(postMessage, " </p>\n\n     <div class=\"photo-grid grid-").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.imgCount)(postArray), "\">\n      ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(postArray), "\n    </div>\n    </div>\n\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(postArray, comment.length), "\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(postArray), "\n    <div id = 'showComment").concat(post_no, "' class=\"comment-section\">\n    ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment, id), "\n\n      \n    </div>\n");
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
/* harmony export */   commentForm: function() { return /* binding */ commentForm; }
/* harmony export */ });
var commentForm = function commentForm(_ref) {
  var post_no = _ref.post_no;
  return "\n    <p id=\"formComment".concat(post_no, "_notification\"></p>\n\n    <form action=\"/postCommentProfile\" method=\"post\" id=\"formComment").concat(post_no, "\" enctype=\"multipart/form-data\" class=\"mb-4 d-none formComment").concat(post_no, "\">\n\n      <input type=\"hidden\" name=\"post_no\" value=\"").concat(post_no, "\"/>\n\n       <div id=\"emojiCommentPickerList").concat(post_no, "\" class=\"d-flex flex-wrap gap-2 mt-2 d-none\" role=\"listbox\" aria-hidden=\"true\"> </div>\n\n        <div id=\"gifPickerList\" class=\"d-flex flex-wrap gap-2 mt-2 d-none\" role=\"listbox\" aria-hidden=\"true\"></div>\n\n        <textarea class=\"form-control inputComment mb-3\" data-commentEmoji-target id=\"inputComment").concat(post_no, "\" name=\"comment\" rows=2> </textarea/>\n                \n      <div class=\"mt-3 d-flex justify-content-between align-items-center position-relative\">\n        <div class=\"d-flex gap-2\">\n            <button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" title=\"add emoji\" \n              id=\"emojiToggle").concat(post_no, "\"aria-expanded=\"false\" aria-controls=\"emojiPickerList\">\uD83D\uDE0A\n              </button>\n\n            <button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" id=\"stickerToggle").concat(post_no, "\"  title=\"Stickers\" aria-label=\"Stickers\">\uD83C\uDFF7\uFE0F</button>\n        </div>\n      \n        <button type=\"submit\" id=\"submitComment").concat(post_no, "\" class=\"btn btn-outline-primary btn-sm submitComment\"> Submit </button>\n      \n            \n      </div>\n    </form>\n  ");
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
/* harmony export */   friendRequestCard: function() { return /* binding */ friendRequestCard; }
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");

var appUrl = "https://olaogun.test/";
var approverId = localStorage.getItem('requesterId');
var friendRequestCard = function friendRequestCard(data) {
  var _data$firstName, _data$lastName, _data$id, _data$famCode, _data$gender, _data$occupation, _data$country;
  var imageUrl = data.img ? "/resources/images/profile/".concat(data.profilePics) : data.requesterProfileImg;
  var firstName = encodeURIComponent((_data$firstName = data.firstName) !== null && _data$firstName !== void 0 ? _data$firstName : data.requesterFirstName);
  var lastName = encodeURIComponent((_data$lastName = data.lastName) !== null && _data$lastName !== void 0 ? _data$lastName : data.requesterLastName);
  var requestId = encodeURIComponent((_data$id = data.id) !== null && _data$id !== void 0 ? _data$id : data.requesterId);
  var requestCode = encodeURIComponent((_data$famCode = data.famCode) !== null && _data$famCode !== void 0 ? _data$famCode : data.requesterFamCode);
  var gender = (_data$gender = data.gender) !== null && _data$gender !== void 0 ? _data$gender : "";
  var occupation = (_data$occupation = data.occupation) !== null && _data$occupation !== void 0 ? _data$occupation : "";
  var country = (_data$country = data.country) !== null && _data$country !== void 0 ? _data$country : "";
  var mutualFriends = '';

  /* New modern colorful card design */
  var html = "<p id=".concat(requestId, "_linkRequestCard></p>\n    <div class=\"card border-0 shadow-sm mb-4 friend-request-card\" style=\"border-radius: 12px; overflow: hidden; transition: box-shadow 0.3s ease;\">\n      <div class=\"friend-request-cover\" style=\"height: 70px; background: linear-gradient(135deg, var(--primary-color), #00c6ff);\"></div>\n      <div class=\"card-body p-3 pt-0 position-relative\">\n        <div class=\"d-flex justify-content-between align-items-end mb-2\" style=\"margin-top: -35px;\">\n           <a href=\"/allMembers/seeProfile/").concat(requestId, "\" class=\"position-relative\">\n            <img src=\"").concat(imageUrl, "\" alt=\"").concat(firstName, "\" class=\"avatar rounded-circle border border-4 border-white shadow-sm\" style=\"width: 70px; height: 70px; object-fit: cover; background-color: white;\">\n             <span class=\"position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1\" style=\"width: 12px; height: 12px;\"></span>\n          </a>\n        </div>\n\n        <div class=\"mb-3\">\n           <h6 class=\"mb-0 fw-bold text-truncate\" style=\"font-size: 1.1rem;\">\n              <a href=\"/allMembers/seeProfile/").concat(requestId, "\" class=\"text-dark text-decoration-none\">").concat(firstName, " ").concat(lastName, "</a>\n            </h6>\n            ").concat(occupation ? "<div class=\"small text-muted text-truncate fw-medium\">".concat(occupation, "</div>") : '', "\n             <div class=\"d-flex align-items-center mt-1 text-muted small\">\n                ").concat(country ? "<span class=\"me-2\"><i class=\"bi bi-geo-alt-fill me-1 text-primary\"></i>".concat(country, "</span>") : '', "\n                <span>").concat(mutualFriends ? mutualFriends : 'New to interactions', "</span>\n            </div>\n        </div>\n\n        \n        <div class=\"d-flex gap-2 friend-request-actions\">\n          <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/50/").concat(requestCode, "/pp\" class=\"btn btn-primary btn-sm flex-grow-1 border-0 fw-medium shadow-sm\" style=\"border-radius: 20px; padding: 6px 0; background: linear-gradient(to right, var(--primary-color), #00c6ff);\">\n            Confirm\n          </a>\n          <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/10\" class=\"btn btn-light btn-sm flex-grow-1 border-0 fw-medium text-secondary\" style=\"border-radius: 20px; padding: 6px 0; background-color: #f0f2f5;\">\n            Decline\n          </a>\n        </div>\n      </div>\n    </div>\n  ");
  (0,_shared__WEBPACK_IMPORTED_MODULE_0__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', html);
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
/* harmony export */   likeCommentButton: function() { return /* binding */ likeCommentButton; }
/* harmony export */ });
var likeCommentButton = function likeCommentButton(data, commentLength) {
  return "\n   <div class=\"reaction-buttons d-flex justify-content-between border-top border-bottom py-2 mb-1\">\n    <button \n      type=\"button\" \n      id=\"likeButton".concat(data.post_no, "\" \n      name=\"").concat(data.post_no, "\"\n      <i class=\"bi bi-hand-thumbs-up me-1\"></i> \n      \xA0   Like \n        <b>\n          <span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">\n            ").concat(data.post_likes, "\n          </span>\n        </b>\n    </button>\n\n    <button \n      type=\"button\" \n      id=\"initComment").concat(data.post_no, "\">\n        <i class=\"bi bi-chat me-1\"></i> \n          Comment \n          (<span class=\"commentCounter\" id=\"commentCounter").concat(data.post_no, "\">\n            ").concat(commentLength, "\n          </span>)\n          \n      </button>\n   \n    </div>\n    ");
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
/* harmony export */   nameImgTiming: function() { return /* binding */ nameImgTiming; }
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   imgCount: function() { return /* binding */ imgCount; },
/* harmony export */   showPostImg: function() { return /* binding */ showPostImg; }
/* harmony export */ });
var showPostImg = function showPostImg(data) {
  // GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE
  var postImagesWithValues = Object.keys(data).filter(function (key) {
    return key.startsWith('post_img') && data[key] !== null;
  }).map(function (el) {
    return data[el];
  });
  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    return "\n        <img \n          src=\"/resources/images/post/".concat(imgElement, "\" \n          alt=\"images").concat(i, "\" \n          data-postImgId=\"").concat(postNo).concat(imgElement, "\"\n          data-imgIndex=\"").concat(i, "\"\n          data-postNo=\"").concat(postNo, "\"\n          class=\"grid-image zoomable-image").concat(postNo, "\" \n          id=\"postImage").concat(i, "\"\n          >\n  ");
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

/***/ "./resources/asset/js/components/profilePage/img.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/img.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_images__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/images */ "./resources/asset/js/components/helper/images.js");






// id('profilePics').addEventListener('click', ()=> id('formProfilePics').style.display ="block"

// );

// FOR PROFILE IMAGE CHANGE
// showImageFileUploadFn('uploadButtonProfilePics', 'profileImageFile', 'profileImgFileNames')

// // FOR POST MODAL IMAGE UPLOAD  

// showImageFileUploadFn('uploadButton', 'post_img', 'postModalImgFileNames')

// id('submitProfilePics').addEventListener('click', ()=> {23

//   // Get the form element
//     const form = document.getElementById("formProfilePics");

//     // Create a FormData object and append the form data to it
//     const formData = new FormData(form);

//     const options = {
//         xsrfCookieName: 'XSRF-TOKEN',
//         xsrfHeaderName: 'X-XSRF-TOKEN',
//     }
//     // send form data using axios post method

//     axios.post('/member/profilePage/profileImg', formData, options)
//     .then((response) => {

//       id('profilePicsNotification').innerHTML = response.data
//       log(response.data, "profilePicsNotification")

//       if(response.data.message === "Profile image updated") {
//         id('profilePicsNotification').classList.add('w3-green')
//         id('profilePicsNotification').innerHTML = response.data.message
//         // Reload the page
//         location.reload();

//       }
//     })
//     .catch((error) => {
//        id('profilePicsNotification').classList.add('w3-red')
//       id('profilePicsNotification').innerHTML = error.message
//     });

//   id('profilePicsNotification').innerHTML = ""
// })

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


localStorage.removeItem('redirect');

// Dark Mode Toggle
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











// import "./commentEmojiTest"
 // long press for reaction options

// import "./newPage"

/***/ }),

/***/ "./resources/asset/js/components/profilePage/indexDB/reactions.js":
/*!************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/indexDB/reactions.js ***!
  \************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   cacheReaction: function() { return /* binding */ cacheReaction; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);


/**
 * Creates a local database in the browser
 * Stores reactions using a unique comment number
 * Lets you save and retrieve reactions without needing a server 
 */

/**
 * This opens (or creates) a database called reactions.
 * The 1 is the version number — useful when upgrading the database structure later.
 */
var dbPromise = indexedDB.open('reactions', 1);

/**
 * This runs only when the database is new or its version changes.
 * It creates a store (like a table) called 'reactions'.
 * Each item in this store will use commentNo as its unique ID (called a keyPath).
 */
dbPromise.onupgradeneeded = function (e) {
  var db = e.target.result;
  db.createObjectStore('reactions', {
    keyPath: 'commentNo'
  });
};

/**
 * This function retrieves a reaction from the database using its commentNo
 * It opens a read-only transaction and fetches the matching record.} commentNo 
 */
function cacheReaction(_x, _x2) {
  return _cacheReaction.apply(this, arguments);
}
function _cacheReaction() {
  _cacheReaction = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(commentNo, data) {
    var db, tx;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.next = 1;
          return dbPromise;
        case 1:
          db = _context.sent.result;
          tx = db.transaction('reactions', 'readwrite');
          tx.objectStore('reactions').put({
            commentNo,
            data
          });
        case 2:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return _cacheReaction.apply(this, arguments);
}

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
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");












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
    posts: [],
    // Renamed 'post' to 'posts' for clarity
    pagination: {},
    // Method to fetch initial data and populate state
    initialize() {
      var _arguments = arguments,
        _this = this;
      return (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
        var page, pullData, responseData, _t;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              page = _arguments.length > 0 && _arguments[0] !== undefined ? _arguments[0] : 1;
              _context.prev = 1;
              _context.next = 2;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].get("/post/getAllPostCommentByFamCode?page=".concat(page, "&limit=50"));
            case 2:
              pullData = _context.sent;
              // --- THE FIX IS HERE ---
              // Manually parse the data string before using it
              responseData = pullData.data; // Check if the data is a string and needs parsing (based on your screenshot)
              if (typeof responseData === 'string') {
                responseData = JSON.parse(responseData);
              }

              // 1. Assign the new 'posts' array from the parsed response
              // Handle new response structure with pagination
              if (responseData.message && responseData.message.message) {
                _this.posts = responseData.message.message;
                _this.pagination = responseData.message.pagination;
              } else {
                // Fallback for old structure or direct array
                _this.posts = responseData.message || [];
              }

              // Clear existing posts if it's a new page load (optional, depends on UX)
              // For now, we append, but for pagination usually we clear or replace.
              // Let's clear the container for standard pagination behavior
              (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('postIt').innerHTML = '';
              if (_this.posts.length > 0) {
                // 2. Loop and render.
                _this.posts.forEach(function (post) {
                  return (0,_post__WEBPACK_IMPORTED_MODULE_3__.allPost)(post);
                });
                _this.renderPagination();
              } else {
                (0,_global__WEBPACK_IMPORTED_MODULE_2__.log)("No post");
                (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('postIt').innerHTML = '<p class="text-center text-muted mt-4">No posts found.</p>';
              }
              _context.next = 4;
              break;
            case 3:
              _context.prev = 3;
              _t = _context["catch"](1);
              console.error("Error fetching posts and comments:", _t);
              (0,_global__WEBPACK_IMPORTED_MODULE_2__.showError)(_t);
            case 4:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[1, 3]]);
      }))();
    },
    renderPagination() {
      var _this2 = this;
      var paginationEl = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('feedPagination');
      if (!paginationEl || !this.pagination) return;
      var _this$pagination = this.pagination,
        current_page = _this$pagination.current_page,
        last_page = _this$pagination.last_page;
      var html = '';

      // Previous Button
      html += "\n                <li class=\"page-item ".concat(current_page === 1 ? 'disabled' : '', "\">\n                    <a class=\"page-link\" href=\"#\" data-page=\"").concat(current_page - 1, "\" tabindex=\"-1\">Previous</a>\n                </li>\n            ");

      // Page Numbers (Simplified range for now)
      for (var i = 1; i <= last_page; i++) {
        // Show first, last, current, and neighbors
        if (i === 1 || i === last_page || i >= current_page - 1 && i <= current_page + 1) {
          html += "\n                        <li class=\"page-item ".concat(i === current_page ? 'active' : '', "\">\n                            <a class=\"page-link\" href=\"#\" data-page=\"").concat(i, "\">").concat(i, "</a>\n                        </li>\n                    ");
        } else if (i === current_page - 2 || i === current_page + 2) {
          html += "<li class=\"page-item disabled\"><span class=\"page-link\">...</span></li>";
        }
      }

      // Next Button
      html += "\n                <li class=\"page-item ".concat(current_page === last_page ? 'disabled' : '', "\">\n                    <a class=\"page-link\" href=\"#\" data-page=\"").concat(current_page + 1, "\">Next</a>\n                </li>\n            ");
      paginationEl.innerHTML = html;

      // Add event listeners
      paginationEl.querySelectorAll('.page-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          var newPage = parseInt(e.target.dataset.page);
          if (newPage && newPage !== current_page && newPage > 0 && newPage <= last_page) {
            _this2.initialize(newPage);
            // Scroll to top of feed
            document.querySelector('.feed-column').scrollIntoView({
              behavior: 'smooth'
            });
          }
        });
      });
    }
  };

  // initiate the global object
  state.initialize();
  var addNewPost = /*#__PURE__*/function () {
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
            return axios__WEBPACK_IMPORTED_MODULE_7__["default"].put("/updatePostByStatusAsPublished/".concat(dataForUse.post_no), {
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
    return function addNewPost(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  var addNewComment = /*#__PURE__*/function () {
    var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(e) {
      var dataForUse, commentCounterEl, commentCount, _t3;
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
            commentCounterEl = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("commentCounter".concat(dataForUse.post_no));
            if (commentCounterEl) {
              commentCount = parseInt(commentCounterEl.textContent); // get the current value and convert it to a number 
              commentCounterEl.textContent = commentCount + 1;
            }
            _context3.prev = 1;
            _context3.next = 2;
            return axios__WEBPACK_IMPORTED_MODULE_7__["default"].put("/updateCommentByStatusAsPublished/".concat(dataForUse.comment_no), {
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
    return function addNewComment(_x2) {
      return _ref2.apply(this, arguments);
    };
  }();
  var deleteComment = function deleteComment(data) {
    var no = data.commentNo;
    var postNo = data.postNo;
    var commentEl = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("commentDiv".concat(no));
    if (commentEl) commentEl.remove();
    var commentCounterEl = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("commentCounter".concat(postNo));
    if (commentCounterEl) {
      var commentCount = parseInt(commentCounterEl.textContent);
      // get the current value and convert it to a number 

      if (commentCount > 0) {
        commentCounterEl.textContent = commentCount - 1;
      }
    }
  };
  var updateLike = function updateLike(e) {
    // Parse the incoming data and check if it already exists in state
    var dataForUse = checkOriginAndParsedData(e);
    var likeElement = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)(dataForUse.likeHtmlId);
    if (likeElement) {
      likeElement.innerHTML = parseInt(dataForUse.likeCounter);
    }
  };
  var reactionUpdated = function reactionUpdated(_ref3) {
    var commentNo = _ref3.commentNo,
      reaction = _ref3.reaction,
      countReaction = _ref3.countReaction,
      whoReacted = _ref3.whoReacted;
    var likeCount = (0,_global__WEBPACK_IMPORTED_MODULE_2__.qSel)("#like-count-".concat(commentNo));
    var preview = (0,_global__WEBPACK_IMPORTED_MODULE_2__.qSel)("#reaction-preview-".concat(commentNo));
    if (likeCount) {
      likeCount.textContent = countReaction[reaction] || 0;
    }
    if (preview) {
      preview.innerHTML = "\n            <span title=\"".concat(whoReacted, " reacted with ").concat(reaction, "\">").concat(reaction, "</span>\n        ");
    }
    var tooltip = (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)("reaction-summary-".concat(commentNo));
    if (tooltip) {
      tooltip.innerHTML = "<div><strong>".concat(whoReacted, "</strong> and others reacted</div>");
    }
  };
  var famCode = localStorage.getItem('requesterFamCode');
  var addNewFriendRequest = function addNewFriendRequest(data) {
    // ADD TO THE NOTIFICATION TAB OF THE APPROVER if the famcode on local storage is the same as the approverFamCode

    if (famCode === data.approverDetails.approverCode) {
      (0,_navbar__WEBPACK_IMPORTED_MODULE_9__.addToNotificationTab)(data);
      (0,_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_10__.friendRequestCard)(data.requesterDetails);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_9__.increaseNotificationCount)();
    }
  };

  // Subscribe to the posts channel
  var postsChannel = pusher.subscribe('posts-channel');
  postsChannel.bind('new-post', function (data) {
    data.forEach(function (item) {
      return addNewPost(item);
    });
  });

  // Subscribe to the comments channel
  var commentsChannel = pusher.subscribe('comments-channel');
  commentsChannel.bind('new-comment', function (data) {
    data.forEach(function (item) {
      return addNewComment(item);
    });
  });
  commentsChannel.bind('delete-comment', function (data) {
    deleteComment(data);
  });
  commentsChannel.bind('reacted-updated', function (data) {
    if (!Array.isArray(data)) data = [data];
    data.forEach(function (item) {
      return reactionUpdated(item);
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
    var appendEvent = (0,_eventHTML__WEBPACK_IMPORTED_MODULE_8__.eventHtml)(data);
    return (0,_global__WEBPACK_IMPORTED_MODULE_2__.id)('eventList').insertAdjacentHTML('afterbegin', appendEvent);
  };
  var notificationChannel = pusher.subscribe('notification-channel');
  notificationChannel.bind('new-notification', function (data) {
    if (localStorage.getItem('requesterFamCode') === data.receiver_id) {
      checkEventAndAdd(data);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_9__.addToNotificationTab)(data);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_9__.increaseNotificationCount)();
    }
  });

  // Subscribe to the new friend request channel
  var friendRequestChannel = pusher.subscribe('friend-request-channel');
  friendRequestChannel.bind('new-request', function (data) {
    if (famCode === data.approverDetails.approverCode) {
      addNewFriendRequest(data);
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
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");




// import { postFormData } from "../helper/http"

try {
  // NEW MESSAGE MODAL
  var showModal = function showModal() {
    return (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'block';
  };

  // CREATE EVENT MODAL
  var showEvent = function showEvent() {
    return (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'block';
  };

  //EVENT ACTION

  var action = function action() {
    var createEvent = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('createEvent');
    var postMsg = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('postMsg');
    if (createEvent) {
      createEvent.addEventListener('click', showEvent);
    } else if (postMsg) {
      postMsg.addEventListener('click', showModal);
    }
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', action);
  }

  // handle post message
} catch (e) {
  (0,_shared__WEBPACK_IMPORTED_MODULE_0__.showError)(e);
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
/* harmony export */   allPost: function() { return /* binding */ allPost; },
/* harmony export */   appendNewPost: function() { return /* binding */ appendNewPost; }
/* harmony export */ });
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


var famCode = localStorage.getItem('requesterFamCode');
/**
 * Renders a post and its associated comments in the DOM.
 * * This function now expects a single, complete post object (postData) 
 * which already contains its comments in the 'comments' key, as provided 
 * by the refactored PHP backend.
 *
 * @param {Object} postData - The post object containing post data, and a nested 'comments' array.
 */
var allPost = function allPost(postData) {
  // <-- Removed commentData parameter

  // Check for valid post data
  if (!postData || typeof postData.post_no === 'undefined') {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.msgException)('Invalid post data structure received.');
    return;
  }

  // 1. The comments array is now directly available inside the postData object.
  // We use the logical OR (|| []) to ensure it's always an array, even if the key is missing.
  var postComments = postData.comments || [];

  // 2. Pass the post data and its now-ready-to-use comment array to the HTML function
  var postHtml = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(postData, postComments);

  // 3. Insert the post HTML into the container
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
var appendNewPost = function appendNewPost(postArray) {
  var post_no = postArray.post_no;
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
    var appendHTML = (0,_html__WEBPACK_IMPORTED_MODULE_0__.html)(postArray);

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
/* harmony export */   renderTopReactions: function() { return /* binding */ renderTopReactions; }
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