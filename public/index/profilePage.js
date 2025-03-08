(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["profilePage"],{

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FormHelper)
/* harmony export */ });
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");


function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var FormHelper = /*#__PURE__*/function () {
  function FormHelper(data) {
    _classCallCheck(this, FormHelper);
    this.data = data;
    this.error = [];
    this.result = 0;
  }
  return _createClass(FormHelper, [{
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

            // rid it off the submit and token
            if (post.name == 'submit' || post.name == 'button' || post.id == 'button' || post.type == 'button' || post.id == 'showPassword_id' || post.id == 'g-recaptcha-response' || post.name == 'cancel' || post.name == "checkbox_id") {
              continue;
            }

            // check if there is no value

            var postName = post.name.replace('_', ' ');
            if (postName == "spouseName" || postName == "spouseMobile" || postName == "spouseEmail" || postName == "fatherMobile" || postName == "fatherEmail" || postName == "motherMobile" || postName == "maidenName" || postName == "motherEmail") {
              if (post.value === "") {
                post.value = "Not Provided";
              }
            }
            var asterisk = "*";
            if (post.value === '' || post.value === 'select') {
              // CHECK IF ERRMSG EXISTS BEFORE SETTING THE INNERHTML
              if (errMsg) {
                var _post$placeholder;
                errMsg.innerHTML = "".concat((_post$placeholder = post.placeholder) !== null && _post$placeholder !== void 0 ? _post$placeholder : asterisk, " cannot be left empty");
                errMsg.style.color = "red";
              }
              _this.error.push("".concat(postName.toUpperCase(), " cannot be left empty"));
            } else {
              _this.result = 1;
            }
            var checkRegex = (0,_helper_general__WEBPACK_IMPORTED_MODULE_0__.matchRegex)(post.value);
            if (checkRegex === false) {
              _this.error.push("There is a problem with your entry for ".concat(postName.toUpperCase(), "'s question"));
              errMsg.innerHTML = "* There is a problem with you entry for ".concat(postName.toUpperCase(), "'s question");
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

/***/ "./resources/asset/js/components/helper/general.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/helper/general.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   autoCompleter: () => (/* binding */ autoCompleter),
/* harmony export */   checkBox: () => (/* binding */ checkBox),
/* harmony export */   checkBox2: () => (/* binding */ checkBox2),
/* harmony export */   createAndAppendElement: () => (/* binding */ createAndAppendElement),
/* harmony export */   distinctValue: () => (/* binding */ distinctValue),
/* harmony export */   isChecked: () => (/* binding */ isChecked),
/* harmony export */   loaderIcon: () => (/* binding */ loaderIcon),
/* harmony export */   loaderIconBootstrap: () => (/* binding */ loaderIconBootstrap),
/* harmony export */   loaderIconBulma: () => (/* binding */ loaderIconBulma),
/* harmony export */   matchInput: () => (/* binding */ matchInput),
/* harmony export */   matchRegex: () => (/* binding */ matchRegex),
/* harmony export */   removeDiv: () => (/* binding */ removeDiv),
/* harmony export */   toSentenceCase: () => (/* binding */ toSentenceCase)
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! autocompleter */ "./node_modules/autocompleter/autocomplete.js");
/* harmony import */ var autocompleter__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(autocompleter__WEBPACK_IMPORTED_MODULE_1__);


function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }


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
  var parentDiv = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(parent);
  return parentDiv.appendChild(newDiv);
};

/**
 * 
 * @param {the id of the input} inputId 
 * @param {the api data or array data} data 
 * @param { filterby is the data.filterby }
 */
var autoCompleter = function autoCompleter(inputId, data) {
  autocompleter__WEBPACK_IMPORTED_MODULE_1___default()({
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
  return _toConsumableArray(new Set(array));
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
    if ((0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(yesId).checked) {
      alert('check');
      fn();
    } else if ((0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(noId).checked) {
      alert('check No');
    }
  };
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(yesId).addEventListener('click', checked);
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(noId).addEventListener('click', checked);
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
  error = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(err);
  firstInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(first);
  secondInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(second);
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
var toSentenceCase = function toSentenceCase(str) {
  return str.toLowerCase() // Convert the string to lowercase
  .split(' ') // Split the string into words
  .map(function (word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
  }) // Capitalize the first letter of each word
  .join(' '); // Join the words back into a string
};

/***/ }),

/***/ "./resources/asset/js/components/helper/images.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/helper/images.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showImageFileUploadFn: () => (/* binding */ showImageFileUploadFn)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToNotificationTab: () => (/* binding */ addToNotificationTab),
/* harmony export */   increaseNotificationCount: () => (/* binding */ increaseNotificationCount)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");




// const timeAgo = (x) => format(x)

// import { html } from './profilePage/html';

var postAgoNotification = function postAgoNotification(date) {
  return "\n  <div class=\"notification_timeago w3-left w3-opacity\" datetime='".concat(date, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date), "\n  </div>");
};
// this is the notification htnl 
var notificationHTML = function notificationHTML(data) {
  // generate random numbers to make the notification unique

  var randomNumber = Math.floor(100 + Math.random() * 900);
  return "<a id = \"notificationBar".concat(data.sender_id).concat(randomNumber, "\" data-id=\"").concat(data.sender_id, "\" class=\"w3-bar-item w3-button notification_real_time linkRequestCard\">\n\n        ").concat(postAgoNotification(data.created_at), "  - \n        <b> ").concat(data.notification_type, "</b> -\n        ").concat(data.notification_name, " -\n        ").concat(data.notification_content, " -\n        ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.toSentenceCase)(data.sender_name), "\n        <button class='w3-button-small w3-round w3-hover-grey w3-border-blue' data-id=\"").concat(data.sender_id, "\" id=\"deleteNotification").concat(data.sender_id).concat(randomNumber, "\"> delete</button>\n  </a>\n\n  ");
};

// CLICK FUNCTION ON THE NOTIFICATION BAR THAT TAKES ONE TO THE FRIEND REQUEST CARD

var increaseNotificationCount = function increaseNotificationCount() {
  var currentNotificationCount = parseInt(sessionStorage.getItem('notificationCount')) + 1;
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = currentNotificationCount;
};
var addToNotificationTab = function addToNotificationTab(data) {
  return (0,_global__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_tab').insertAdjacentHTML('afterbegin', notificationHTML(data));
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
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = data.length;

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
      (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('notification_count').innerHTML = 0;
    }
  }
})["catch"](function (error) {
  // Handle any errors that occur during the process
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
});

///member/notifications

/***/ }),

/***/ "./resources/asset/js/components/profilePage/allEvents.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/allEvents.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _post__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./post */ "./resources/asset/js/components/profilePage/post.js");


function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }



try {
  var options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
  };
  var yourId = localStorage.getItem('requesterId');
  var famCode = localStorage.getItem('requesterFamCode');

  // CLICK EVENT get the comment and like button from the document
  document.addEventListener('click', /*#__PURE__*/function () {
    var _ref = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(e) {
      var elementId, postId, likeCounterId, likeCounterVal, encodedLikeCounterVal, commentFormId, idForm, form, formEntries, inputComment, idInputComment, formExtra, formData, requesterFamCodeValue, response, senderId, elementData, data, notificationHTML, url, _response, newValues, friendRequestSection;
      return _regeneratorRuntime().wrap(function _callee$(_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            //document.onclick = async (e) => {
            elementId = e.target.id;
            postId = e.target.name; // Handle Like Button Click
            if (!elementId.includes("likeButton")) {
              _context.next = 12;
              break;
            }
            // replace button with Counter to get the span id 
            likeCounterId = elementId.replace('Button', 'Counter'); // trim removes leading and trailing spaces
            likeCounterVal = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(likeCounterId).innerHTML.trim().replace(/\n/g, ''); // 
            encodedLikeCounterVal = encodeURIComponent(likeCounterVal);
            _context.next = 8;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].put("/profileCard/postLikes?postNo=".concat(postId, "&count=").concat(encodedLikeCounterVal, "&likeCounterId=").concat(likeCounterId));
          case 8:
            _context.next = 10;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("/getNewLikesPusher");
          case 10:
            _context.next = 66;
            break;
          case 12:
            if (!elementId.includes("initComment")) {
              _context.next = 17;
              break;
            }
            commentFormId = elementId.replace('init', 'form');
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(commentFormId).style.display = "block";

            // Handle Comment Submission
            _context.next = 66;
            break;
          case 17:
            if (!elementId.includes("submitComment")) {
              _context.next = 35;
              break;
            }
            e.preventDefault();

            //idForm == formComment511
            idForm = elementId.replace("submit", "form"); // make the comment form disappear
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idForm).style.display = "none";
            // extract the form entries
            form = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(idForm);
            formEntries = new FormData(form); // if the comment form input is empty. Get the input id and check 
            inputComment = idForm.replace("form", "input");
            idInputComment = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(inputComment);
            if (!(idInputComment.value == null || idInputComment.value == "")) {
              _context.next = 29;
              break;
            }
            alert("Please enter a comment before submitting");
            _context.next = 33;
            break;
          case 29:
            _context.next = 31;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/postCommentProfile', formEntries, options);
          case 31:
            _context.next = 33;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("/getNewCommentPusher");
          case 33:
            _context.next = 66;
            break;
          case 35:
            if (!elementId.includes("submitPost")) {
              _context.next = 52;
              break;
            }
            e.preventDefault();
            formExtra = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formPostMessageModal');
            formData = new FormData(formExtra); // get the requesterFamCode from the localStorage 
            requesterFamCodeValue = localStorage.getItem('requesterFamCode'); // Append the new form entry to the FormData object
            formData.append('postFamCode', requesterFamCodeValue);

            // 3. 
            _context.next = 43;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post("/member/profilePage/post", formData, options);
          case 43:
            response = _context.sent;
            _context.next = 46;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("/post/getNewPostAndEmail?newCommentNo=" + response.data.message);
          case 46:
            _context.next = 48;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("/getNewPostPusher");
          case 48:
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'none';
            (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("formPostMessageModal").reset();
            _context.next = 66;
            break;
          case 52:
            if (!(elementId && elementId.includes('deleteNotification'))) {
              _context.next = 65;
              break;
            }
            // Extract the user ID from the target ID
            senderId = elementId.replace("deleteNotification", "notificationBar");
            elementData = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(elementId);
            data = elementData.getAttribute("data-id"); // change the background of the clicked element 
            notificationHTML = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(senderId); // Make sure required variables are defined before using them
            if (typeof yourId === 'undefined' || typeof famCode === 'undefined') {
              (0,_global__WEBPACK_IMPORTED_MODULE_0__.msgException)("Required parameters (yourId or famCode) are not defined");
            }
            url = "/removeNotification/".concat(yourId, "/").concat(famCode, "/").concat(data);
            _context.next = 61;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].put(url);
          case 61:
            _response = _context.sent;
            if (_response.data.message === "success") {
              // remove a html element with notificationBar after 2 mins 
              notificationHTML.remove();

              // reduce the notification count as you have deleted the notification
              newValues = parseInt(sessionStorage.getItem('notificationCount') - 1);
              (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('notification_count').innerHTML = newValues;
            } else {
              (0,_global__WEBPACK_IMPORTED_MODULE_0__.msgException)("Error removing notification");
            }
            _context.next = 66;
            break;
          case 65:
            if (e.target.classList.contains('linkRequestCard')) {
              // ONCE THE NOTIFICATION BAR IS CLICKED, IT SHOULD TAKE YOU TO BE FRIEND REQUEST CARD
              friendRequestSection = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(e.target.getAttribute('data-id'), "_linkRequestCard"));
              if (friendRequestSection) {
                friendRequestSection.scrollIntoView({
                  behavior: "smooth"
                });
              }
            }
          case 66:
          case "end":
            return _context.stop();
        }
      }, _callee);
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   appendNewComment: () => (/* binding */ appendNewComment),
/* harmony export */   commentHTML: () => (/* binding */ commentHTML),
/* harmony export */   showComment: () => (/* binding */ showComment)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");


var commentHTML = function commentHTML(data) {
  var profileImg = data.profileImg,
    date_created = data.date_created,
    img = data.img,
    comment = data.comment,
    comment_no = data.comment_no;
  var imgURL = profileImg || img;
  var image = imgURL ? "/public/img/profile/".concat(imgURL) : "/public/avatar/avatarF.png";
  return "<div class='w3-ul w3-border w3-round' id='comment".concat(comment_no, "' name='commentDiv'>\n            <div class='w3-container commentDiv'>\n              <img src='").concat(image, "' alt='Avatar' class='w3-left w3-circle w3-margin-right commentImg' style='width:50px; height:50px'>\n              <p class='w3-right w3-opacity commentTiming' datetime='").concat(date_created, "' title='").concat(date_created, "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), " </p> \n              <p class='commentFont'> ").concat(comment, "</p>\n            </div>\n          </div>");
};
var showComment = function showComment(comment) {
  if (!comment) {
    return "<div class=\"w3-ul w3-border\" id=\"comment\" name=\"commentDiv\"></div>";
  } // only run if there is comment

  // USED FOR ALL THE COMMENTS WHEN THE PAGE IS LOADING
  var commentHTMLArray = comment.map(function (commentElement) {
    return commentHTML(commentElement);
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

  var commentContainer = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(idDiv);
  if (!commentContainer) {
    throw new Error("The comment div id does not exist ");
  }
  var commentHtml = commentHTML(commentData);
  commentContainer.insertAdjacentHTML('beforeend', commentHtml);
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/createEvent.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/createEvent.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_4__);


function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }






var formInput = document.querySelectorAll('.eventModalForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_1__["default"](formInputArr);
var displayNone = function displayNone() {
  return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'none';
};
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('cancelModal').addEventListener('click', displayNone);

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
  var _ref = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(e) {
    var eventForm, eventFormEntries, _yield$Promise$all, _yield$Promise$all2, eventResponse, notificationResponse, notificationNo;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          e.preventDefault();
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('error').innerHTML = "";
          formData.massValidate();
          if (!(formData.error.length > 0)) {
            _context.next = 8;
            break;
          }
          alert('The form cannot be submitted. Please check the errors');
          formData.clearError();
          return _context.abrupt("return");
        case 8:
          // get the form data
          eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventModalForm');
          eventFormEntries = new FormData(eventForm); // POST data to create the event and notification in parallel
          _context.next = 12;
          return Promise.all([axios__WEBPACK_IMPORTED_MODULE_5__["default"].post("/member/profilePage/event", eventFormEntries, options), axios__WEBPACK_IMPORTED_MODULE_5__["default"].post('/member/notification/event', eventFormEntries, options)]);
        case 12:
          _yield$Promise$all = _context.sent;
          _yield$Promise$all2 = _slicedToArray(_yield$Promise$all, 2);
          eventResponse = _yield$Promise$all2[0];
          notificationResponse = _yield$Promise$all2[1];
          // Extract and get notificationNo from the responses
          notificationNo = notificationResponse.data.message; // update all members of similar famcode on their UIs using Pusher
          axios__WEBPACK_IMPORTED_MODULE_5__["default"].get("/member/notification/event?notificationNo=".concat(notificationNo));

          // close the modal
          displayNone();
          _context.next = 24;
          break;
        case 21:
          _context.prev = 21;
          _context.t0 = _context["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(_context.t0);
        case 24:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 21]]);
  }));
  return function process(_x) {
    return _ref.apply(this, arguments);
  };
}();
(0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitEventModal').addEventListener('click', process);

/***/ }),

/***/ "./resources/asset/js/components/profilePage/eventHTML.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/eventHTML.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   eventHtml: () => (/* binding */ eventHtml)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }


var approverId = encodeURIComponent(localStorage.getItem('requesterId')); // means that the user currently working on the UI


//NOTE - this code worked well 25/10/24
/**
 * Fetch friend requests by approver ID and render each request.
 */
var fetchFriendRequests = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var response;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          _context.next = 3;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("/getFriendRequestById?id=".concat(approverId));
        case 3:
          response = _context.sent;
          if (response.data.message) {
            response.data.message.forEach(function (request) {
              return waitForRequestFriendClass(request);
            });
          }
          _context.next = 10;
          break;
        case 7:
          _context.prev = 7;
          _context.t0 = _context["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(_context.t0);
        case 10:
          ;
        case 11:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 7]]);
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
      (0,_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_1__.friendRequestCard)(data);
      obs.disconnect(); // Stop observing once .requestFriendClass is found
    } else {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)('there is no requestFriendClass');
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
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
    postMessage = el.postMessage;
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin post".concat(post_no, "\"><br>\n\n      ").concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(el), "\n\n    <hr class=\"w3-clear\">\n\n    <p class=\"postFont\"> ").concat(postMessage, " </p>\n\n     ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(el), "\n\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(el), "\n\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(el), "\n\n    <div id = 'showComment").concat(post_no, "'>\n\n      ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment), "\n      \n    </div><br>\n  </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js":
/*!*****************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/commentForm.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   commentForm: () => (/* binding */ commentForm)
/* harmony export */ });
var commentForm = function commentForm(data) {
  var post_no = data.post_no;
  return " <p id=\"formComment".concat(post_no, "_notification\"></p>\n\n  <form \n    action=\"/postCommentProfile\" \n    method=\"post\" id=\"formComment").concat(post_no, "\" \n    style=\"display:none\" \n    enctype=\"multipart/form-data\">\n\n    <input \n      name='post_no' \n      type=\"hidden\" \n      name=\"").concat(post_no, "\" \n      value=").concat(post_no, " />\n\n    <input \n      class=\"w3-input w3-border w3-round-large inputComment\" \n      type=\"text\" \n      placeholder=\"Write a comment\"\n      id=\"inputComment").concat(post_no, "\" \n      value = \"\" name='comment'>\n\n    <br>\n\n    <button \n      type='submit' \n      id=\"submitComment").concat(post_no, "\" \n      class=\"w3-button w3-green submitComment\">\n        Submit\n    </button>\n    \n    <br><br>\n  </form>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   friendRequestCard: () => (/* binding */ friendRequestCard)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   likeCommentButton: () => (/* binding */ likeCommentButton)
/* harmony export */ });
var likeCommentButton = function likeCommentButton(data) {
  return "\n  <button \n    type=\"button\" \n    id=\"likeButton".concat(data.post_no, "\" \n    name=\"").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-green w3-margin-bottom\">\n    <em class=\"fa fa-thumbs-up\"></em>\n    \xA0   Like \n      <b>\n        <span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">\n          ").concat(data.post_likes, "\n        </span>\n      </b>\n  </button>\n\n   <button \n    type=\"button\" \n    id=\"initComment").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-theme-d2 w3-margin-bottom\">\n      <em class=\"fa fa-comment\"></em> \n        Comment \n    </button>\n    ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js":
/*!*********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   nameImgTiming: () => (/* binding */ nameImgTiming)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");

var timeAgo = function timeAgo(x) {
  return (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x);
};
var fullName = function fullName(_fullName) {
  return "<h6 id=\"fullName\"><b>".concat(_fullName, "</b> </h6>");
};
var postedAt = function postedAt(date) {
  return "<div class=\"timeago postTimeCal w3-right w3-opacity\"  datetime='".concat(date.date_created, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date.date_created), "'> ").concat(timeAgo(date.post_time), "</div>");
};
var nameImgTiming = function nameImgTiming(data) {
  var img = data.profileImg ? "/public/img/profile/".concat(data.profileImg) : "/public/avatar/avatarF.png";
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(data.img, "&pID=").concat(data.post_no, "&path=profile&id=").concat(data.id, "\"> <img src=").concat(img, " alt=\"img\" class=\"w3-left w3-circle w3-margin-right postImg\" style=\"width:60px\">\n        </a>\n        ").concat(postedAt(data), " ").concat(fullName(data.fullName));
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
  })["catch"](function (error) {
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _loadPost__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./loadPost */ "./resources/asset/js/components/profilePage/loadPost.js");
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/profilePage/modal.js");
/* harmony import */ var _img__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./img */ "./resources/asset/js/components/profilePage/img.js");
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/profilePage/allEvents.js");
/* harmony import */ var _registerPushNotification__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./registerPushNotification */ "./resources/asset/js/components/profilePage/registerPushNotification.js");
/* harmony import */ var _periodicSync__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./periodicSync */ "./resources/asset/js/components/profilePage/periodicSync.js");
/* harmony import */ var _periodicSync__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_periodicSync__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _createEvent__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./createEvent */ "./resources/asset/js/components/profilePage/createEvent.js");
/* harmony import */ var _friendRequestCard__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./friendRequestCard */ "./resources/asset/js/components/profilePage/friendRequestCard.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");


localStorage.removeItem('redirect');










/***/ }),

/***/ "./resources/asset/js/components/profilePage/loadPost.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/loadPost.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _post__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./post */ "./resources/asset/js/components/profilePage/post.js");
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! pusher-js */ "./node_modules/pusher-js/dist/web/pusher.js");
/* harmony import */ var pusher_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(pusher_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _eventHTML__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./eventHTML */ "./resources/asset/js/components/profilePage/eventHTML.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }









// set an empty array
try {
  var MAX_APPENDED_POSTS = 1000; // Set a maximum limit
  var appendedComments = new Set(); // To track unique comments
  var appendedPosts = new Set(); // To track unique comments

  // Initialize Pusher
  var pusher = new (pusher_js__WEBPACK_IMPORTED_MODULE_4___default())("0dc3f141e1632b5aa5db", {
    cluster: "eu",
    encrypted: true
  });

  // Global state object with data-fetching and initialization logic
  var state = {
    post: [],
    comment: [],
    // Method to fetch initial data and populate state
    initialize: function initialize() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var pullData;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.prev = 0;
              _context.next = 3;
              return axios__WEBPACK_IMPORTED_MODULE_7__["default"].get("/post/getAllPostCommentByFamCode");
            case 3:
              pullData = _context.sent;
              // Assign fetched data to state properties
              _this.post = pullData.data.message.post;
              _this.comment = pullData.data.message.comment;
              _this.comment = _this.comment.flat(); // Flatten the array of arrays into a single array of comment objects

              if (_this.post.length > 0) {
                // Render posts and comments on the page after data is loaded
                _this.post.forEach(function (data) {
                  return (0,_post__WEBPACK_IMPORTED_MODULE_1__.allPost)(data, _this.comment);
                });
              } else {
                (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)("No post");
              }
              _context.next = 13;
              break;
            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](0);
              console.error("Error fetching posts and comments:", _context.t0);
            case 13:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[0, 10]]);
      }))();
    }
  };

  // initiate the global object
  state.initialize();
  var updatePost = /*#__PURE__*/function () {
    var _ref = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2(e) {
      var dataForUse, oldestPost;
      return _regeneratorRuntime().wrap(function _callee2$(_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            // Parse the incoming data and check if it already exists in state
            dataForUse = checkOriginAndParsedData(e); // Only append if the comment hasn't been added before
            if (appendedPosts.has(dataForUse.post_no)) {
              _context2.next = 13;
              break;
            }
            appendedPosts.add(dataForUse.post_no);

            // Clean up old entries if the set exceeds the limit
            if (appendedPosts.size > MAX_APPENDED_POSTS) {
              oldestPost = appendedPosts.values().next().value;
              appendedPosts["delete"](oldestPost);
            }
            (0,_post__WEBPACK_IMPORTED_MODULE_1__.appendNewPost)(dataForUse);
            _context2.prev = 5;
            _context2.next = 8;
            return axios__WEBPACK_IMPORTED_MODULE_7__["default"].put("/updatePostByStatusAsPublished/".concat(dataForUse.post_no), {
              post_status: 'published'
            });
          case 8:
            _context2.next = 13;
            break;
          case 10:
            _context2.prev = 10;
            _context2.t0 = _context2["catch"](5);
            console.error("Failed to update comment status: ".concat(_context2.t0.message));
          case 13:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[5, 10]]);
    }));
    return function updatePost(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  var updateComment = /*#__PURE__*/function () {
    var _ref2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3(e) {
      var dataForUse;
      return _regeneratorRuntime().wrap(function _callee3$(_context3) {
        while (1) switch (_context3.prev = _context3.next) {
          case 0:
            // Parse the incoming data and check if it already exists in state
            dataForUse = checkOriginAndParsedData(e); // Only append if the comment hasn't been added before
            if (appendedComments.has(dataForUse.comment_no)) {
              _context3.next = 12;
              break;
            }
            appendedComments.add(dataForUse.comment_no);

            // check if dataForUse length is greater than 0 and if yes foreach to lop 

            (0,_comment__WEBPACK_IMPORTED_MODULE_3__.appendNewComment)(dataForUse);
            _context3.prev = 4;
            _context3.next = 7;
            return axios__WEBPACK_IMPORTED_MODULE_7__["default"].put("/updateCommentByStatusAsPublished/".concat(dataForUse.comment_no), {
              comment_status: 'published'
            });
          case 7:
            _context3.next = 12;
            break;
          case 9:
            _context3.prev = 9;
            _context3.t0 = _context3["catch"](4);
            console.error("Failed to update comment status: ".concat(_context3.t0.message));
          case 12:
          case "end":
            return _context3.stop();
        }
      }, _callee3, null, [[4, 9]]);
    }));
    return function updateComment(_x2) {
      return _ref2.apply(this, arguments);
    };
  }();
  var updateLike = function updateLike(e) {
    // Parse the incoming data and check if it already exists in state
    var dataForUse = checkOriginAndParsedData(e);
    var likeElement = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(dataForUse.likeHtmlId);
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
    var appendEvent = (0,_eventHTML__WEBPACK_IMPORTED_MODULE_5__.eventHtml)(data);
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('eventList').insertAdjacentHTML('afterbegin', appendEvent);
  };
  var notificationChannel = pusher.subscribe('notification-channel');
  notificationChannel.bind('new-notification', function (data) {
    if (localStorage.getItem('requesterFamCode') === data.receiver_id) {
      checkEventAndAdd(data);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_6__.addToNotificationTab)(data);
      (0,_navbar__WEBPACK_IMPORTED_MODULE_6__.increaseNotificationCount)();
    }
  });

  // AUTOMATICALLY UPDATE TIMESTAMP
  // Function to check for elements and render if they exist every 5 seconds
  setInterval(function () {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.checkManyElements)('class', ".timeago", timeago_js__WEBPACK_IMPORTED_MODULE_2__.render);
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.checkManyElements)('class', ".commentTiming", timeago_js__WEBPACK_IMPORTED_MODULE_2__.render);
  }, 2000); // Adjust interval as needed

  var checkOriginAndParsedData = function checkOriginAndParsedData(data) {
    if (!data) throw new Error('No update received');
    if (data) {
      if (data.origin != appUrl) {
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.msgException)('Invalid Origin');
      }
      return data;
    }

    // check if data is a valid jason object
    // return JSON.parse(data)
  };
} catch (error) {
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/modal.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/modal.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
// utility for periodic sync  
if ('periodicSync' in navigator) {
  _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var status;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.next = 2;
          return navigator.permissions.query({
            name: 'periodic-background-sync'
          });
        case 2:
          status = _context.sent;
          if (status.state === 'granted') {
            console.log('Background sync permission granted');
          } else {
            console.log('Background sync permission denied');
          }
          _context.prev = 4;
          _context.next = 7;
          return navigator.periodicSync.register('content-sync', {
            minInterval: 24 * 60 * 60 * 1000 // Minimum interval in milliseconds (e.g., 1 day)
          });
        case 7:
          console.log('Periodic Sync registered');
          _context.next = 13;
          break;
        case 10:
          _context.prev = 10;
          _context.t0 = _context["catch"](4);
          console.error('Periodic Sync registration failed:', _context.t0);
        case 13:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[4, 10]]);
  }))(); // Immediately invoke the async function
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/post.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/post.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   allPost: () => (/* binding */ allPost),
/* harmony export */   appendNewPost: () => (/* binding */ appendNewPost)
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

/***/ "./resources/asset/js/components/profilePage/registerPushNotification.js":
/*!*******************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/registerPushNotification.js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
  })["catch"](function (error) {
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
  })["catch"](function (error) {
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
  })["catch"](function (error) {
    console.error('Failed to send subscription data to the server:', error);
  });
}

/***/ })

}]);